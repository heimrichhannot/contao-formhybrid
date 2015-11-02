<?php

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\HastePlus\Environment;
use HeimrichHannot\StatusMessages\StatusMessage;

abstract class Form extends DC_Hybrid
{
	protected $arrData = array();

	protected $arrSubmitCallbacks = array();

	protected $arrSubPalettes = array();

	protected $arrEditableBoxes = array();

	protected $arrDefaultValues = array();

	protected $arrLegends = array();

	protected $objModel;

	protected $objAjax;

	protected $strClass;

	protected $intId = 0; // id of model entitiy

	private $useModelData = false;

	private $resetAfterSubmission = true;


	public function __construct(\ModuleModel $objModule = null, $intId = 0)
	{
		global $objPage;

		$strInputMethod = strtolower($this->strMethod);

		if ($objModule !== null && $objModule->formHybridDataContainer && $objModule->formHybridPalette) {
			$this->objModule   = $objModule;
			$this->arrData     = $objModule->row();
			$this->strTable    = $objModule->formHybridDataContainer;
			$this->strPalette  = $objModule->formHybridPalette;
			$this->arrEditable = deserialize($objModule->formHybridEditable, true);
			$this->skipScrollingToSuccessMessage = $objModule->formHybridSkipScrollingToSuccessMessage;
			$this->isComplete = $objModule->formHybridIsComplete;

			if ($objModule->formHybridAddEditableRequired) {
				$this->overwriteRequired = true;
				$this->arrRequired = deserialize($objModule->formHybridEditableRequired, true);
			}

			$this->arrSubPalettes        = deserialize($objModule->formHybridSubPalettes, true);
			$this->strTemplate           = $objModule->formHybridTemplate;
			$this->addDefaultValues      = $objModule->formHybridAddDefaultValues;
			$this->arrDefaultValues      =
				FormHelper::getAssocMultiColumnWizardList(deserialize($objModule->formHybridDefaultValues, true), 'field');
			$this->skipValidation        =
				$objModule->formHybridSkipValidation ?: (\Input::$strInputMethod(FORMHYBRID_NAME_SKIP_VALIDATION) ?: false);
			$this->intId            = $intId;
			$this->strTemplateStart      = $this->formHybridStartTemplate ?: $this->strTemplateStart;
			$this->strTemplateStop       = $this->formHybridStopTemplate ?: $this->strTemplateStop;
			$this->async                 = $this->formHybridAsync;
			$this->useCustomSubTemplates = $this->formHybridCustomSubTemplates;
			$this->strClass              = $this->formHybridCssClass;
		}

		$this->strInputMethod   = $strInputMethod = strtolower($this->strMethod);
		$this->strFormId        = $this->strTable . '_' . $this->id;
		$this->strFormName      = 'formhybrid_' . str_replace('tl_', '', $this->strTable);
		// GET is checked for each field separately
		$this->isSubmitted  = (\Input::post('FORM_SUBMIT') == $this->strFormId);
		$this->useModelData = \Database::getInstance()->tableExists($this->strTable);

		parent::__construct($this->strTable, $objModule);
	}

	public function generate()
	{
		return parent::edit();
	}


	protected function processForm()
	{
		if ($this->isSubmitted && !$this->doNotSubmit)
		{
			$this->onSubmitCallback($this);

			if (!$this->skipValidation)
			{
				if (is_array($this->dca['config']['onsubmit_callback'])) {
					foreach ($this->dca['config']['onsubmit_callback'] as $callback) {
						$this->import($callback[0]);
						$this->$callback[0]->$callback[1]($this);

						// reload model from database, maybe something has changed in callback
						$this->objActiveRecord->refresh();
					}
				}
			}

			// reload model from database, maybe something has changed in callback
			$this->objActiveRecord->refresh();

			$arrSubmissionData = $this->prepareSubmissionData();

			if ($this->formHybridSendSubmissionViaEmail) {
				if ($this->formHybridSubmissionAvisotaMessage)
					$this->createSubmissionAvisotaEmail(
						$this->formHybridSubmissionAvisotaMessage,
						$this->formHybridSubmissionAvisotaSalutationGroup,
						$arrSubmissionData
					);
				else
					$this->createSubmissionEmail($arrSubmissionData);
			}

			if ($this->formHybridSendConfirmationViaEmail) {
				if ($this->formHybridConfirmationAvisotaMessage)
					$this->createConfirmationAvisotaEmail(
						$this->formHybridConfirmationAvisotaMessage,
						$this->formHybridConfirmationAvisotaSalutationGroup,
						$arrSubmissionData
					);
				else
					$this->createConfirmationEmail($arrSubmissionData);
			}

			$this->createSuccessMessage($arrSubmissionData);

			$this->afterSubmitCallback($this);

			// reset form is default. disable by $this->setReset(false)
			// Exception: filter forms should never been reset after submit
			if($this->getReset() && !$this->isFilterForm)
			{
				$this->reset();
			}
		}
	}

	protected function prepareSubmissionData()
	{
		return FormSubmissionHelper::prepareData($this->objActiveRecord, $this->dca, $this, $this->arrEditable);
	}

	protected function onSubmitCallback(\DataContainer $dc)
	{
	}

	protected function afterSubmitCallback(\DataContainer $dc)
	{
	}

	protected function createSubmissionEmail($arrSubmissionData)
	{
		$arrRecipient = trimsplit(',', $this->formHybridSubmissionMailRecipient);

		$objEmail           = new \Email();
		$objEmail->from     = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject  =
			\String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->formHybridSubmissionMailSubject, $arrSubmissionData), false),
				$arrSubmissionData
			);

		if($hasText = (strlen($this->formHybridSubmissionMailText) > 0))
		{
			$objEmail->text = \String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->formHybridSubmissionMailText, $arrSubmissionData), false),
				$arrSubmissionData
			);

			// convert <br> to new line and strip tags, except links
			$objEmail->text = strip_tags(preg_replace('/<br(\s+)?\/?>/i', "\n", $objEmail->text), '<a>');
		}


		if($this->formHybridSubmissionTemplate != '')
		{
			$objModel = \FilesModel::findByUuid($this->formHybridSubmissionTemplate);

			if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
			{
				$objFile = new \File($objModel->path, true);

				$objEmail->html = \String::parseSimpleTokens(
					$this->replaceInsertTags(FormHelper::replaceFormDataTags($objFile->getContent(), $arrSubmissionData), false),
					$arrSubmissionData
				);

				// if no text is set, convert html to text
				if(!$hasText)
				{
					$objHtml2Text = new \Html2Text\Html2Text($objEmail->html);
					$objEmail->text = $objHtml2Text->getText();
				}
			}
		}

		// overwrite default from and
		if (!empty($this->formHybridSubmissionMailSender)) {
			list($senderName, $sender) = \String::splitFriendlyEmail($this->formHybridSubmissionMailSender);
			$objEmail->from     = $this->replaceInsertTags(FormHelper::replaceFormDataTags($sender, $arrSubmissionData), false);
			$objEmail->fromName = $this->replaceInsertTags(FormHelper::replaceFormDataTags($senderName, $arrSubmissionData), false);
		}

		if ($this->formHybridSubmissionMailAttachment != '') {
			$this->addAttachmentToEmail($objEmail, deserialize($this->formHybridSubmissionMailAttachment));
		}

		if ($this->sendSubmissionEmail($objEmail, $arrRecipient, $arrSubmissionData)) {
			if (is_array($arrRecipient)) {
				$arrRecipient = array_filter(array_unique($arrRecipient));
				$objEmail->sendTo($this->replaceInsertTags(FormHelper::replaceFormDataTags(implode(',', $arrRecipient), $arrSubmissionData), false));
			}
		}
	}

	protected function createSubmissionAvisotaEmail($intMessageId, $strSalutationGroupId, $arrSubmissionData)
	{
		$arrRecipient = array_filter(array_unique(trimsplit(',', $this->formHybridSubmissionMailRecipient)));

		$objMessage = AvisotaHelper::getAvisotaMessage($intMessageId);

		$objMessage->setSubject(\String::parseSimpleTokens(
			$this->replaceInsertTags(FormHelper::replaceFormDataTags($objMessage->getSubject(), $arrSubmissionData), false),
			$arrSubmissionData
		));

		foreach ($objMessage->getContents() as $objContent)
		{
			$strText = $objContent->getText();

			if (!$strText)
				continue;

			$objContent->setText(str_replace("\n", '<br>', \String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($strText, $arrSubmissionData), false),
				$arrSubmissionData
			)));
		}

		AvisotaHelper::sendAvisotaEMailByMessage(
			$objMessage,
			explode(',', $this->replaceInsertTags(FormHelper::replaceFormDataTags(implode(',', $arrRecipient), $arrSubmissionData), false)),
			array_map(function($arrValue) {
				if (isset($arrValue['value']))
					return $arrValue['value'];
				else
					return $arrValue;
			}, $arrSubmissionData),
			$strSalutationGroupId,
			AvisotaHelper::RECIPIENT_MODE_USE_MEMBER_DATA
		);
	}

	protected function createSuccessMessage($arrSubmissionData)
	{
		$this->formHybridSuccessMessage = \String::parseSimpleTokens(
			$this->replaceInsertTags(
				FormHelper::replaceFormDataTags(
					!empty($this->formHybridSuccessMessage) ? $this->formHybridSuccessMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['success'],
					$arrSubmissionData
				)
			),
			$arrSubmissionData
		);

		StatusMessage::addSuccess($this->formHybridSuccessMessage, $this->objModule->id);
	}

	protected function sendSubmissionEmail($objEmail, $arrRecipient, $arrSubmissionData)
	{
		$this->onSendSubmissionEmailCallback($objEmail, $arrRecipient, $arrSubmissionData);
		return true;
	}
	protected function onSendSubmissionEmailCallback($objEmail, $arrRecipient, $arrSubmissionData) {}

	protected function createConfirmationEmail($arrSubmissionData)
	{
		$arrRecipient = deserialize($arrSubmissionData[$this->formHybridConfirmationMailRecipientField]['value'], true);

		$objEmail           = new \Email();
		$objEmail->from     = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject  = \String::parseSimpleTokens(
			$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->formHybridConfirmationMailSubject, $arrSubmissionData), false),
			$arrSubmissionData
		);

		if($hasText = (strlen($this->formHybridConfirmationMailText) > 0))
		{
			$objEmail->text     = \String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->formHybridConfirmationMailText, $arrSubmissionData), false),
				$arrSubmissionData
			);

			// convert <br> to new line and strip tags, except links
			$objEmail->text = strip_tags(preg_replace('/<br(\s+)?\/?>/i', "\n", $objEmail->text), '<a>');
		}

		if($this->formHybridConfirmationMailTemplate != '')
		{
			$objModel = \FilesModel::findByUuid($this->formHybridConfirmationMailTemplate);

			if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
			{
				$objFile = new \File($objModel->path, true);

				$objEmail->html = \String::parseSimpleTokens(
					$this->replaceInsertTags(FormHelper::replaceFormDataTags($objFile->getContent(), $arrSubmissionData), false),
					$arrSubmissionData
				);

				// if no text is set, convert html to text
				if(!$hasText)
				{
					$objHtml2Text = new \Html2Text\Html2Text($objEmail->html);
					$objEmail->text = $objHtml2Text->getText();
				}
			}
		}

		// overwrite default from and
		if (!empty($this->formHybridConfirmationMailSender)) {
			list($senderName, $sender) = \String::splitFriendlyEmail($this->formHybridConfirmationMailSender);
			$objEmail->from     = $this->replaceInsertTags(FormHelper::replaceFormDataTags($sender, $arrSubmissionData), false);
			$objEmail->fromName = $this->replaceInsertTags(FormHelper::replaceFormDataTags($senderName, $arrSubmissionData), false);
		}

		if ($this->formHybridConfirmationMailAttachment != '') {
			$this->addAttachmentToEmail($objEmail, deserialize($this->formHybridConfirmationMailAttachment));
		}

		if ($this->sendConfirmationEmail($objEmail, $arrRecipient, $arrSubmissionData)) {
			if (is_array($arrRecipient)) {
				$arrRecipient = array_filter(array_unique($arrRecipient));
				$objEmail->sendTo($arrRecipient);
			}
		}
	}

	protected function createConfirmationAvisotaEmail($intMessageId, $strSalutationGroupId, $arrSubmissionData)
	{
		$objMessage = AvisotaHelper::getAvisotaMessage($intMessageId);

		$objMessage->setSubject(\String::parseSimpleTokens(
			$this->replaceInsertTags(FormHelper::replaceFormDataTags($objMessage->getSubject(), $arrSubmissionData), false),
			$arrSubmissionData
		));

		foreach ($objMessage->getContents() as $objContent)
		{
			$strText = $objContent->getText();

			if (!$strText)
				continue;

			$objContent->setText(\String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($strText, $arrSubmissionData), false),
				$arrSubmissionData
			));
		}

		AvisotaHelper::sendAvisotaEMailByMessage(
			$objMessage,
			$arrSubmissionData[$this->formHybridConfirmationMailRecipientField]['value'],
			array_map(function($arrValue) {
				if (isset($arrValue['value']))
					return $arrValue['value'];
				else
					return $arrValue;
			}, $arrSubmissionData),
			$strSalutationGroupId,
			AvisotaHelper::RECIPIENT_MODE_USE_SUBMISSION_DATA
		);
	}

	protected function sendConfirmationEmail($objEmail, $arrRecipient, $arrSubmissionData)
	{
		return true;
	}

	protected function addAttachmentToEmail($objEmail, $arrUuids)
	{
		$objAttachments = \FilesModel::findMultipleByUuids($arrUuids);

		if ($objAttachments !== null) {
			while ($objAttachments->next()) {
				$strMime = 'application/octet-stream';

				if (isset($GLOBALS['TL_MIME'][$objAttachments->extension])) {
					$strMime = $GLOBALS['TL_MIME'][$objAttachments->extension][0];
				}

				$objEmail->attachFile($objAttachments->path, $strMime);
			}
		}
	}

	protected function reset()
	{
		if($this->async)
		{
			$this->isSubmitted = false;
			$this->intId = null;
			$this->initialize();
			$this->generateFields();
			return;
		}

		global $objPage;

		// if no jumpTo is set or jumpTo is equal to current page, reload
		if ($this->jumpTo && $this->jumpTo != $objPage->id &&
			($objTargetPage = \PageModel::findByPk($this->jumpTo)) !== null)
		{
			// unset messages
			unset($_SESSION[FORMHYBRID_MESSAGE_SUCCESS]);
			unset($_SESSION[FORMHYBRID_MESSAGE_ERROR]);
			\Controller::redirect(\Controller::generateFrontendUrl($objTargetPage->row()));
		}
		else
		{
			\Controller::reload();
		}
	}

	abstract protected function compile();

	/**
	 * Return an object property
	 *
	 * @param string
	 *
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey) {
			case 'table':
				return $this->strTable;
			case 'palette':
				return $this->strPalette;
			default:
				if (isset($this->arrData[$strKey])) {
					return $this->arrData[$strKey];
				}
		}


		return parent::__get($strKey);
	}

	/**
	 * Set an object property
	 *
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}

	/**
	 * Check whether a property is set
	 *
	 * @param string
	 *
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}

	public function getSubmission($blnFormatted = true)
	{
		$arrSubmission = $this->arrSubmission;

		if(!$this->isSubmitted())
		{
			$arrSubmission = $this->getDefaults();
		}

		if($this->isFilterForm && is_array($arrSubmission))
		{
			$arrDca = $this->getDca();

			$objSubmission = new Submission();

			foreach($arrSubmission as $strField => $varValue)
			{
				$arrData = $arrDca['fields'][$strField];

				// unset options_callback, as long as we have no valid backend user
				unset($arrData['options_callback'], $arrData['options_callback']);

				if($blnFormatted)
				{
					$objSubmission->{$strField} = FormHelper::getFormatedValueByDca($varValue, $arrData, $this, false);
				}
				else
				{
					$objSubmission->{$strField} = $varValue;
				}
			}
		}

		return $this->isFilterForm ? $objSubmission : $this->objActiveRecord;
	}

	public function isSubmitted()
	{
		return $this->isSubmitted;
	}

	public function doNotSubmit()
	{
		return $this->doNotSubmit;
	}

	public function setSubmitCallbacks(array $callbacks)
	{
		$this->arrSubmitCallbacks = $callbacks;
	}

	/**
	 * Clear inputs, set default values
	 *
	 * @return bool
	 * @deprecated set $this->reset to true/fals on onsubmit_callback
	 */
	public function clearInputs()
	{
		$this->resetAfterSubmission = true;
	}

	public function setReset($varValue)
	{
		$this->resetAfterSubmission = (bool) $varValue;
	}

	public function getReset()
	{
		return $this->resetAfterSubmission;
	}
}
