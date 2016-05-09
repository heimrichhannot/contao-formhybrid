<?php

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\StatusMessages\StatusMessage;
use MatthiasMullie\Minify\Exception;

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

	protected $strLogFile = 'formhybrid.log';

	protected $blnSilentMode = false;


	public function __construct(\ModuleModel $objModule = null, $intId = 0)
	{
		global $objPage;

		$strInputMethod = strtolower($this->strMethod);

		if ($objModule !== null && $objModule->formHybridDataContainer && $objModule->formHybridPalette) {
			$this->objModule   = $objModule;
			$this->arrData     = $objModule->row();
			$this->strTable    = $objModule->formHybridDataContainer;
			$this->strPalette  = $objModule->formHybridPalette;

			if($objModule->formHybridAction && ($objActionPage = \PageModel::findWithDetails($objModule->formHybridAction)) !== null)
			{
				$this->strAction = \Controller::generateFrontendUrl($objActionPage->row(), null, null, true);
			}
			else
			{
				$this->strAction = Url::removeQueryString(array('file'), \Environment::get('uri'));
			}

			$this->strFormId        = $this->getFormId();
			$this->strFormName      = $this->getFormName();

			if ($this->formHybridAddHashToAction)
				$this->strAction .= '#' . $this->strFormId;

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
		// GET is checked for each field separately
		$this->isSubmitted  = (\Input::post('FORM_SUBMIT') == $this->strFormId);
		$this->useModelData = \Database::getInstance()->tableExists($this->strTable);

		// prevent from caching form, chrome is greedy
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		header("Pragma: no-cache"); // HTTP 1.0.
		header("Expires: 0"); // Proxies.

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

			if($this->formHybridSendSubmissionAsNotification)
			{
				if(($objMessage = \HeimrichHannot\NotificationCenterPlus\MessageModel::findPublishedById($this->formHybridSubmissionNotification)) !== null)
				{
					$arrToken = FormSubmissionHelper::tokenizeData($arrSubmissionData);

					if($this->sendSubmissionNotification($objMessage, $arrSubmissionData, $arrToken))
					{
						$objMessage->send($arrToken, $GLOBALS['TL_LANGUAGE']);
					}
				}
			}

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


			if($this->formHybridSendConfirmationAsNotification)
			{
				if(($objMessage = \HeimrichHannot\NotificationCenterPlus\MessageModel::findPublishedById($this->formHybridConfirmationNotification)) !== null)
				{
					$arrToken = FormSubmissionHelper::tokenizeData($arrSubmissionData);

					if($this->sendConfirmationNotification($objMessage, $arrSubmissionData, $arrToken))
					{
						$objMessage->send($arrToken, $GLOBALS['TL_LANGUAGE']);
					}
				}
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

			if (!$this->isFilterForm && !$this->blnSilentMode)
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
				$to = $this->replaceInsertTags(FormHelper::replaceFormDataTags(implode(',', $arrRecipient), $arrSubmissionData), false);

				try
				{
					$objEmail->sendTo($to);
				} catch(Exception $e){
					log_message('Error sending submission email for entity ' . $this->strTable .':' . $this->intId . ' to : ' . $to . ' (' . $e . ')', $this->strLogFile);
				}
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

		StatusMessage::addSuccess($this->formHybridSuccessMessage, $this->objModule->id, 'alert alert-success');
	}

	protected function sendSubmissionNotification(\NotificationCenter\Model\Message $objMessage, $arrSubmissionData, $arrToken)
	{
		return true;
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

				try
				{
					$objEmail->sendTo($arrRecipient);
				} catch(Exception $e){
					log_message('Error sending submission email for entity ' . $this->strTable .':' . $this->intId . ' to : ' . implode(',', $arrRecipient) . ' (' . $e . ')', $this->strLogFile);
				}
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

	protected function sendConfirmationNotification(\NotificationCenter\Model\Message $objMessage, $arrSubmissionData, $arrToken)
	{
		return true;
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
			if(!StatusMessage::isEmpty($this->objModule->id))
			{
				\HeimrichHannot\StatusMessages\StatusMessage::reset($this->objModule->id);
			}

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
		// parent getter must be dominant, otherwise intId will taken from arrData
		// tl_calendar_events::adjustTime callback make usage of $dc->id instead of $dc->activeRecord->id
		if(($strParent = parent::__get($strKey)) != '')
		{
			return $strParent;
		}

		switch ($strKey)
		{
			default:
				if (isset($this->arrData[$strKey]))
				{
					return $this->arrData[$strKey];
				}
		}
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

	/**
	 * Return the Submission of the form, if nothing was submitted, return null
	 *
	 * @param bool $blnFormatted set false, if the real value should be set
	 * @param bool $blnSkipDefaults skip default values, helpful if nothing was submitted
	 *
	 * @return \FilesModel|Submission|\Model|null
	 */
	public function getSubmission($blnFormatted = true, $blnSkipDefaults = false)
	{
		$arrSubmission = $this->arrSubmission;

		if(!$this->isSubmitted())
		{
			$arrSubmission = $blnSkipDefaults ? array() : $this->getDefaults();
		}

		if($this->isFilterForm && is_array($arrSubmission))
		{
			$arrDca = $this->getDca();

			if(empty($arrSubmission))
			{
				return null;
			}

			$objSubmission = new Submission();

			foreach($arrSubmission as $strField => $varValue)
			{
				$arrData = $arrDca['fields'][$strField];

				if(is_array($arrData['options']) && !Validator::isValidOption($varValue, $arrData, $this))
				{
					continue;
				}

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
