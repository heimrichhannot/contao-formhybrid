<?php

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Haste\Util\FormSubmission;
use HeimrichHannot\StatusMessages\StatusMessage;
use MatthiasMullie\Minify\Exception;

abstract class Form extends DC_Hybrid
{
	const FORMHYBRID_NAME = 'formhybrid';

	protected $arrSubmitCallbacks = array();

	protected $strLogFile = 'formhybrid.log';

	public function __construct($varConfig = null, $intId = 0)
	{
		// prevent from caching form, chrome is greedy
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
		header("Pragma: no-cache"); // HTTP 1.0.
		header("Expires: 0"); // Proxies.

		parent::__construct($this->strTable, $varConfig, $intId);
	}


	public function generate()
	{
		return parent::edit();
	}


	protected function processForm()
	{
		if ($this->isSubmitted && !$this->doNotSubmit)
		{
			if ($this->addSubmitValues && !empty($this->arrSubmitValues))
			{
				foreach ($this->arrSubmitValues as $arrSubmitValue)
				{
					$this->objActiveRecord->{$arrSubmitValue['field']} = $arrSubmitValue['value'];
				}

				if ($this->saveToBlob)
				{
					$this->saveToBlob();
				} else
				{
					$this->objActiveRecord->save();
				}
			}

			$this->onSubmitCallback($this);

			if (!$this->isSkipValidation())
			{
				if (is_array($this->dca['config']['onsubmit_callback']))
				{
					foreach ($this->dca['config']['onsubmit_callback'] as $callback)
					{
						$this->import($callback[0]);
						$this->$callback[0]->$callback[1]($this);

						// reload model from database, maybe something has changed in callback
						$this->objActiveRecord->refresh();
					}
				}
			}

			// reload model from database, maybe something has changed in callback
			$this->objActiveRecord->refresh();

			// just created?
			$blnJustCreated = false;
			if (!$this->objActiveRecord->tstamp)
			{
				$blnJustCreated                = true;
				$this->objActiveRecord->tstamp = time();
				$this->onCreateCallback($this->objActiveRecord, $this);
			}

			$blnIsModified = false;
			foreach ($this->objActiveRecord->row() as $strField => $varValue)
			{
				if ($this->arrOriginalRow[$strField] != $varValue)
				{
					$blnIsModified = true;
					break;
				}
			}

			// run callback after update after submit_callbacks since these could do important updates
			if ($blnIsModified)
			{
				// update tstamp
				$this->objActiveRecord->tstamp = time();

				if ($this->saveToBlob)
				{
					$this->saveToBlob();
				} else
				{
					$this->objActiveRecord->save();

					// create new version - only if modified
					$this->createVersion();
				}

				$this->onUpdateCallback($this->objActiveRecord, $this, $blnJustCreated, $this->arrOriginalRow);
			}

			$arrSubmissionData = $this->prepareSubmissionData();

			if ($this->sendSubmissionAsNotification || $this->submissionNotification)
			{
				if (($objMessage = \HeimrichHannot\NotificationCenterPlus\MessageModel::findPublishedById($this->submissionNotification)) !== null)
				{
					$arrToken = FormSubmission::tokenizeData($arrSubmissionData);

					if ($this->sendSubmissionNotification($objMessage, $arrSubmissionData, $arrToken))
					{
						$objMessage->send($arrToken, $GLOBALS['TL_LANGUAGE']);
					}
				}
			}

			if ($this->sendSubmissionViaEmail)
			{
				if ($this->submissionAvisotaMessage)
				{
					$this->createSubmissionAvisotaEmail(
						$this->submissionAvisotaMessage,
						$this->submissionAvisotaSalutationGroup,
						$arrSubmissionData
					);
				} else
				{
					$this->createSubmissionEmail($arrSubmissionData);
				}
			}


			if ($this->confirmationAsNotification || $this->confirmationNotification)
			{
				if (($objMessage = \HeimrichHannot\NotificationCenterPlus\MessageModel::findPublishedById($this->confirmationNotification)) !== null)
				{
					$arrToken = FormSubmission::tokenizeData($arrSubmissionData);

					if ($this->sendConfirmationNotification($objMessage, $arrSubmissionData, $arrToken))
					{
						$objMessage->send($arrToken, $GLOBALS['TL_LANGUAGE']);
					}
				}
			}

			if ($this->sendConfirmationViaEmail)
			{
				if ($this->confirmationAvisotaMessage)
				{
					$this->createConfirmationAvisotaEmail(
						$this->confirmationAvisotaMessage,
						$this->confirmationAvisotaSalutationGroup,
						$arrSubmissionData
					);
				} else
				{
					$this->createConfirmationEmail($arrSubmissionData);
				}
			}

			if (!$this->isFilterForm && !$this->blnSilentMode)
			{
				$this->createSuccessMessage($arrSubmissionData);
			}

			$this->afterSubmitCallback($this);
		}
	}

	protected function prepareSubmissionData()
	{
		return FormSubmission::prepareData($this->objActiveRecord, $this->strTable, $this->dca, $this, $this->arrEditable);
	}

	protected function onSubmitCallback(\DataContainer $dc)
	{
	}

	protected function afterSubmitCallback(\DataContainer $dc)
	{
	}

	protected function createSubmissionEmail($arrSubmissionData)
	{
		$arrRecipient = trimsplit(',', $this->submissionMailRecipient);

		$objEmail           = new \Email();
		$objEmail->from     = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject  = \String::parseSimpleTokens(
			$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->submissionMailSubject, $arrSubmissionData), false),
			$arrSubmissionData
		);

		if ($hasText = (strlen($this->submissionMailText) > 0))
		{
			$objEmail->text = \String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->submissionMailText, $arrSubmissionData), false),
				$arrSubmissionData
			);

			// convert <br> to new line and strip tags, except links
			$objEmail->text = strip_tags(preg_replace('/<br(\s+)?\/?>/i', "\n", $objEmail->text), '<a>');
		}


		if ($this->submissionTemplate != '')
		{
			$objModel = \FilesModel::findByUuid($this->submissionTemplate);

			if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
			{
				$objFile = new \File($objModel->path, true);

				$objEmail->html = \String::parseSimpleTokens(
					$this->replaceInsertTags(FormHelper::replaceFormDataTags($objFile->getContent(), $arrSubmissionData), false),
					$arrSubmissionData
				);

				// if no text is set, convert html to text
				if (!$hasText)
				{
					$objHtml2Text   = new \Html2Text\Html2Text($objEmail->html);
					$objEmail->text = $objHtml2Text->getText();
				}
			}
		}

		// overwrite default from and
		if (!empty($this->submissionMailSender))
		{
			list($senderName, $sender) = \String::splitFriendlyEmail($this->submissionMailSender);
			$objEmail->from     = $this->replaceInsertTags(FormHelper::replaceFormDataTags($sender, $arrSubmissionData), false);
			$objEmail->fromName = $this->replaceInsertTags(FormHelper::replaceFormDataTags($senderName, $arrSubmissionData), false);
		}

		if ($this->submissionMailAttachment != '')
		{
			$this->addAttachmentToEmail($objEmail, deserialize($this->submissionMailAttachment));
		}

		if ($this->sendSubmissionEmail($objEmail, $arrRecipient, $arrSubmissionData))
		{
			if (is_array($arrRecipient))
			{
				$arrRecipient = array_filter(array_unique($arrRecipient));
				$to           = $this->replaceInsertTags(FormHelper::replaceFormDataTags(implode(',', $arrRecipient), $arrSubmissionData), false);

				try
				{
					$objEmail->sendTo($to);
				} catch (Exception $e)
				{
					log_message('Error sending submission email for entity ' . $this->strTable . ':' . $this->intId . ' to : ' . $to . ' (' . $e . ')', $this->strLogFile);
				}
			}
		}
	}

	protected function createSubmissionAvisotaEmail($intMessageId, $strSalutationGroupId, $arrSubmissionData)
	{
		$arrRecipient = array_filter(array_unique(trimsplit(',', $this->submissionMailRecipient)));

		$objMessage = AvisotaHelper::getAvisotaMessage($intMessageId);

		$objMessage->setSubject(
			\String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($objMessage->getSubject(), $arrSubmissionData), false),
				$arrSubmissionData
			)
		);

		foreach ($objMessage->getContents() as $objContent)
		{
			$strText = $objContent->getText();

			if (!$strText)
			{
				continue;
			}

			$objContent->setText(
				str_replace(
					"\n",
					'<br>',
					\String::parseSimpleTokens(
						$this->replaceInsertTags(FormHelper::replaceFormDataTags($strText, $arrSubmissionData), false),
						$arrSubmissionData
					)
				)
			);
		}

		AvisotaHelper::sendAvisotaEMailByMessage(
			$objMessage,
			explode(',', $this->replaceInsertTags(FormHelper::replaceFormDataTags(implode(',', $arrRecipient), $arrSubmissionData), false)),
			array_map(
				function ($arrValue)
				{
					if (isset($arrValue['value']))
					{
						return $arrValue['value'];
					} else
					{
						return $arrValue;
					}
				},
				$arrSubmissionData
			),
			$strSalutationGroupId,
			AvisotaHelper::RECIPIENT_MODE_USE_MEMBER_DATA
		);
	}

	protected function createSuccessMessage($arrSubmissionData)
	{
		$this->successMessage = \String::parseSimpleTokens(
			$this->replaceInsertTags(
				FormHelper::replaceFormDataTags(
					!empty($this->successMessage) ? $this->successMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['success'],
					$arrSubmissionData
				)
			),
			$arrSubmissionData
		);

		StatusMessage::addSuccess($this->successMessage, $this->objModule->id, 'alert alert-success');
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

	protected function onSendSubmissionEmailCallback($objEmail, $arrRecipient, $arrSubmissionData)
	{
	}

	protected function createConfirmationEmail($arrSubmissionData)
	{
		$arrRecipient = deserialize($arrSubmissionData[$this->confirmationMailRecipientField]['value'], true);

		$objEmail           = new \Email();
		$objEmail->from     = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject  = \String::parseSimpleTokens(
			$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->confirmationMailSubject, $arrSubmissionData), false),
			$arrSubmissionData
		);

		if ($hasText = (strlen($this->confirmationMailText) > 0))
		{
			$objEmail->text = \String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($this->confirmationMailText, $arrSubmissionData), false),
				$arrSubmissionData
			);

			// convert <br> to new line and strip tags, except links
			$objEmail->text = strip_tags(preg_replace('/<br(\s+)?\/?>/i', "\n", $objEmail->text), '<a>');
		}

		if ($this->confirmationMailTemplate != '')
		{
			$objModel = \FilesModel::findByUuid($this->confirmationMailTemplate);

			if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
			{
				$objFile = new \File($objModel->path, true);

				$objEmail->html = \String::parseSimpleTokens(
					$this->replaceInsertTags(FormHelper::replaceFormDataTags($objFile->getContent(), $arrSubmissionData), false),
					$arrSubmissionData
				);

				// if no text is set, convert html to text
				if (!$hasText)
				{
					$objHtml2Text   = new \Html2Text\Html2Text($objEmail->html);
					$objEmail->text = $objHtml2Text->getText();
				}
			}
		}

		// overwrite default from and
		if (!empty($this->confirmationMailSender))
		{
			list($senderName, $sender) = \String::splitFriendlyEmail($this->confirmationMailSender);
			$objEmail->from     = $this->replaceInsertTags(FormHelper::replaceFormDataTags($sender, $arrSubmissionData), false);
			$objEmail->fromName = $this->replaceInsertTags(FormHelper::replaceFormDataTags($senderName, $arrSubmissionData), false);
		}

		if ($this->confirmationMailAttachment != '')
		{
			$this->addAttachmentToEmail($objEmail, deserialize($this->confirmationMailAttachment));
		}

		if ($this->sendConfirmationEmail($objEmail, $arrRecipient, $arrSubmissionData))
		{
			if (is_array($arrRecipient))
			{
				$arrRecipient = array_filter(array_unique($arrRecipient));

				try
				{
					$objEmail->sendTo($arrRecipient);
				} catch (Exception $e)
				{
					log_message('Error sending submission email for entity ' . $this->strTable . ':' . $this->intId . ' to : ' . implode(',', $arrRecipient) . ' (' . $e . ')', $this->strLogFile);
				}
			}
		}
	}

	protected function createConfirmationAvisotaEmail($intMessageId, $strSalutationGroupId, $arrSubmissionData)
	{
		$objMessage = AvisotaHelper::getAvisotaMessage($intMessageId);

		$objMessage->setSubject(
			\String::parseSimpleTokens(
				$this->replaceInsertTags(FormHelper::replaceFormDataTags($objMessage->getSubject(), $arrSubmissionData), false),
				$arrSubmissionData
			)
		);

		foreach ($objMessage->getContents() as $objContent)
		{
			$strText = $objContent->getText();

			if (!$strText)
			{
				continue;
			}

			$objContent->setText(
				\String::parseSimpleTokens(
					$this->replaceInsertTags(FormHelper::replaceFormDataTags($strText, $arrSubmissionData), false),
					$arrSubmissionData
				)
			);
		}

		AvisotaHelper::sendAvisotaEMailByMessage(
			$objMessage,
			$arrSubmissionData[$this->confirmationMailRecipientField]['value'],
			array_map(
				function ($arrValue)
				{
					if (isset($arrValue['value']))
					{
						return $arrValue['value'];
					} else
					{
						return $arrValue;
					}
				},
				$arrSubmissionData
			),
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

		if ($objAttachments !== null)
		{
			while ($objAttachments->next())
			{
				$strMime = 'application/octet-stream';

				if (isset($GLOBALS['TL_MIME'][$objAttachments->extension]))
				{
					$strMime = $GLOBALS['TL_MIME'][$objAttachments->extension][0];
				}

				$objEmail->attachFile($objAttachments->path, $strMime);
			}
		}
	}

	abstract protected function compile();

	/**
	 * Return the Submission of the form, if nothing was submitted, return null
	 *
	 * @param bool $blnFormatted    set false, if the real value should be set
	 * @param bool $blnSkipDefaults skip default values, helpful if nothing was submitted
	 *
	 * @return \FilesModel|Submission|\Model|null
	 */
	public function getSubmission($blnFormatted = true, $blnSkipDefaults = false)
	{
		$arrSubmission = $this->arrSubmission;

		if (!$this->isSubmitted())
		{
			$arrSubmission = $blnSkipDefaults ? array() : $this->getDefaults();
		}

		if (($this->isFilterForm || !$this->useModelData()) && is_array($arrSubmission))
		{
			$arrDca = $this->getDca();

			if (empty($arrSubmission))
			{
				return null;
			}

			$objSubmission = new Submission();

			foreach ($arrSubmission as $strField => $varValue)
			{
				$arrData = $arrDca['fields'][$strField];

				if (is_array($arrData['options']) && !Validator::isValidOption($varValue, $arrData, $this))
				{
					continue;
				}

				if ($blnFormatted)
				{
					$objSubmission->{$strField} = FormSubmission::prepareSpecialValueForPrint($varValue, $arrData, $this->strTable, $this);
				} else
				{
					$objSubmission->{$strField} = $varValue;
				}
			}
		}

		return ($this->isFilterForm || !$this->useModelData()) ? $objSubmission : $this->objActiveRecord;
	}

	public function setSubmitCallbacks(array $callbacks)
	{
		$this->arrSubmitCallbacks = $callbacks;
	}
}
