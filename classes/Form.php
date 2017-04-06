<?php

namespace HeimrichHannot\FormHybrid;

use Firebase\JWT\JWT;
use HeimrichHannot\Ajax\Ajax;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Exporter\ExporterModel;
use HeimrichHannot\FieldPalette\FieldPaletteModel;
use HeimrichHannot\Haste\Util\FormSubmission;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\Request\Request;
use HeimrichHannot\StatusMessages\StatusMessage;
use MatthiasMullie\Minify\Exception;

abstract class Form extends DC_Hybrid
{
    const FORMHYBRID_NAME = 'formhybrid';

    protected $arrSubmitCallbacks = [];

    protected $strLogFile = 'formhybrid.log';

    public function __construct($varConfig = null, $intId = 0)
    {
        // prevent from caching form, chrome is greedy
        Request::getInstance()->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate'); // HTTP 1.1.
        Request::getInstance()->headers->set('Pragma', 'no-cache'); // HTTP 1.0.
        Request::getInstance()->headers->set('Expires', '0'); // Proxies.

        parent::__construct($this->strTable, $varConfig, $intId);

        if ($this->addOptIn)
        {
            if (!\Database::getInstance()->fieldExists(FormHybrid::OPT_IN_DATABASE_FIELD, $this->table))
            {
                throw new \Exception(
                    'Opt-in requires existing field `' . FormHybrid::OPT_IN_DATABASE_FIELD . '` in database table `' . $this->table
                    . '`. Run `\HeimrichHannot\FormHybrid\FormHybrid::addOptInFieldToTable(' . $this->table . ')` within DCA `' . $this->table . '.php!`'
                );
            }

            if (Request::hasGet(Formhybrid::OPT_IN_REQUEST_ATTRIBUTE))
            {
                $this->activateSubmission();
                // remove parameter from query string and reload current page
                \Controller::redirect(Url::removeQueryString([Formhybrid::OPT_IN_REQUEST_ATTRIBUTE]));
            }
        }
    }

    public function generate()
    {
        return parent::edit();
    }

    protected function activateSubmission()
    {
        $strJWT = Request::getGet(Formhybrid::OPT_IN_REQUEST_ATTRIBUTE);

        try
        {
            $objData = JWT::decode($strJWT, \Config::get('encryptionKey'), ['HS256']);
        } catch (\Exception $e)
        {
            $this->createInvalidOptInTokenMessage();

            return false;
        }

        if (!$objData->table || !$objData->token)
        {
            $this->createInvalidOptInTokenMessage();

            return false;
        }

        if ($objData->table != $this->table)
        {
            $this->createInvalidOptInTokenMessage();

            return false;
        }

        $objResult =
            \Database::getInstance()->prepare('SELECT * FROM ' . $objData->table . ' WHERE ' . FormHybrid::OPT_IN_DATABASE_FIELD . ' = ?')->limit(1)->execute($objData->token);

        if ($objResult->numRows < 1)
        {
            $this->createInvalidOptInTokenMessage();

            return false;
        }

        $this->intId = $objResult->id;

        $strModelClass = \Model::getClassFromTable($objData->table);

        if (!class_exists($strModelClass))
        {
            $this->createInvalidOptInTokenMessage();

            return false;
        }

        /**
         * @var \Model $objModel
         */
        $objModel = new $strModelClass();
        $objModel->setRow($objResult->row());

        \Database::getInstance()->prepare('UPDATE ' . $objData->table . ' SET ' . FormHybrid::OPT_IN_DATABASE_FIELD . ' = "" WHERE id = ?')->execute($objModel->id);

        $arrSubmissionData = FormSubmission::prepareData($objModel, $this->strTable, $this->dca, $this, $this->arrEditable);

        $this->createSuccessNotifications($arrSubmissionData);

        if (!$this->isSilentMode())
        {
            $this->createSuccessMessage($arrSubmissionData, true);
        }

        $this->afterActivationCallback($this);

        return true;
    }

    protected function processForm()
    {
        if (Request::getPost(FORMHYBRID_NAME_EXPORT))
        {
            $intConfigId = FieldPaletteModel::findById($this->formHybridExportConfigs[0])->formhybrid_formHybridExportConfigs_config;
            $objConfig   = ExporterModel::findById($intConfigId);

            if (!$objConfig->linkedTable)
            {
                $objConfig->linkedTable = $this->strTable;
            }

            $objExporterClass = $objConfig->exporterClass;
            if (class_exists($objExporterClass))
            {
                $objExporter = new $objExporterClass($objConfig);

                $objExporter->export(\FrontendUser::getInstance(), \FrontendUser::getInstance()->getData());
            }
        }

        $this->onSubmitCallback($this);

        unset($_SESSION['FILES']); // clear files cache

        if (!$this->isSkipValidation())
        {
            if (is_array($this->dca['config']['onsubmit_callback']))
            {
                foreach ($this->dca['config']['onsubmit_callback'] as $callback)
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this);

                    // reload model from database, maybe something has changed in callback
                    if (!$this->saveToBlob)
                    {
                        $this->objActiveRecord->refresh();
                    }
                }
            }
        }

        // reload model from database, maybe something has changed in callback
        if (!$this->saveToBlob)
        {
            $this->objActiveRecord->refresh();
        }

        // just created?
        $blnJustCreated = false;
        if (!$this->objActiveRecord->tstamp)
        {
            $blnJustCreated                = true;
            $this->objActiveRecord->tstamp = time();
            $this->onFirstSubmitCallback($this->objActiveRecord, $this);
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
            }
            else
            {
                $this->objActiveRecord->save();

                // create new version - only if modified
                $this->createVersion();
            }

            $this->onUpdateCallback($this->objActiveRecord, $this, $blnJustCreated, $this->arrOriginalRow);
        }

        $arrSubmissionData = $this->prepareSubmissionData();

        if ($this->addOptIn && $this->optInNotification)
        {
            $this->createOptInNotification($arrSubmissionData);
        }

        if (!$this->addOptIn)
        {
            $this->createSuccessNotifications($arrSubmissionData);
        }

        if (!$this->isSilentMode())
        {
            $this->createSuccessMessage($arrSubmissionData);
        }

        $this->afterSubmitCallback($this);
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

    protected function afterActivationCallback(\DataContainer $dc)
    {
    }

    protected function createOptInNotification($arrSubmissionData)
    {
        if (($objMessage = \HeimrichHannot\NotificationCenterPlus\MessageModel::findPublishedById($this->optInNotification)) !== null)
        {
            $arrToken = FormSubmission::tokenizeData($arrSubmissionData);

            $strToken = \StringUtil::binToUuid(\Database::getInstance()->getUuid());

            $this->objActiveRecord->refresh();
            $this->objActiveRecord->{FormHybrid::OPT_IN_DATABASE_FIELD} = $strToken;
            $this->objActiveRecord->save();

            $strJWT = JWT::encode(
                [
                    'table' => $this->table,
                    'token' => $strToken,
                    'date'  => time(),
                ],
                \Config::get('encryptionKey')
            );

            $arrToken['opt_in_token'] = $strJWT;
            $arrToken['opt_in_link']  = Url::addQueryString(
                FormHybrid::OPT_IN_REQUEST_ATTRIBUTE . '=' . $strJWT,
                AjaxAction::removeAjaxParametersFromUrl(\Environment::get('uri'))
            );

            if ($this->sendOptInNotification($objMessage, $arrSubmissionData, $arrToken))
            {
                $objMessage->send($arrToken, $GLOBALS['TL_LANGUAGE']);
            }
        }
    }

    protected function createSuccessNotifications($arrSubmissionData)
    {
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
            }
            else
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
            }
            else
            {
                $this->createConfirmationEmail($arrSubmissionData);
            }
        }
    }

    protected function createSubmissionEmail($arrSubmissionData)
    {
        $arrRecipient = trimsplit(',', $this->submissionMailRecipient);

        $objEmail           = new \Email();
        $objEmail->from     = $GLOBALS['TL_ADMIN_EMAIL'];
        $objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
        $objEmail->subject  = \StringUtil::parseSimpleTokens(
            $this->replaceInsertTags(FormHelper::replaceFormDataTags($this->submissionMailSubject, $arrSubmissionData), false),
            $arrSubmissionData
        );

        if ($hasText = (strlen($this->submissionMailText) > 0))
        {
            $objEmail->text = \StringUtil::parseSimpleTokens(
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

                $objEmail->html = \StringUtil::parseSimpleTokens(
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
            list($senderName, $sender) = \StringUtil::splitFriendlyEmail($this->submissionMailSender);
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
                    log_message(
                        'Error sending submission email for entity ' . $this->strTable . ':' . $this->intId . ' to : ' . $to . ' (' . $e . ')',
                        $this->strLogFile
                    );
                }
            }
        }
    }

    protected function createSubmissionAvisotaEmail($intMessageId, $strSalutationGroupId, $arrSubmissionData)
    {
        $arrRecipient = array_filter(array_unique(trimsplit(',', $this->submissionMailRecipient)));

        $objMessage = AvisotaHelper::getAvisotaMessage($intMessageId);

        $objMessage->setSubject(
            \StringUtil::parseSimpleTokens(
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
                    \StringUtil::parseSimpleTokens(
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
                    }
                    else
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

    protected function createInvalidOptInTokenMessage()
    {
        $this->successMessage = $GLOBALS['TL_LANG']['formhybrid']['messages']['invalidOptInToken'];

        StatusMessage::addError($this->successMessage, $this->objModule->id, 'alert alert-danger');
    }

    protected function createSuccessMessage($arrSubmissionData, $blnForceSuccess = false)
    {
        $strMessage = !empty($this->successMessage) ? $this->successMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['success'];

        if ($this->addOptIn && !$blnForceSuccess)
        {
            $strMessage = !empty($this->optInSuccessMessage) ? $this->optInSuccessMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['optIn'];
        }

        $this->successMessage = \StringUtil::parseSimpleTokens(
            $this->replaceInsertTags(
                FormHelper::replaceFormDataTags(
                    $strMessage,
                    $arrSubmissionData
                )
            ),
            $arrSubmissionData
        );

        StatusMessage::addSuccess($this->successMessage, $this->objModule->id, 'alert alert-success');
    }

    protected function sendOptInNotification(\NotificationCenter\Model\Message $objMessage, $arrSubmissionData, $arrToken)
    {
        return true;
    }

    protected function sendSubmissionNotification(\NotificationCenter\Model\Message $objMessage, &$arrSubmissionData, &$arrToken)
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
        $objEmail->subject  = \StringUtil::parseSimpleTokens(
            $this->replaceInsertTags(FormHelper::replaceFormDataTags($this->confirmationMailSubject, $arrSubmissionData), false),
            $arrSubmissionData
        );

        if ($hasText = (strlen($this->confirmationMailText) > 0))
        {
            $objEmail->text = \StringUtil::parseSimpleTokens(
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

                $objEmail->html = \StringUtil::parseSimpleTokens(
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
            list($senderName, $sender) = \StringUtil::splitFriendlyEmail($this->confirmationMailSender);
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
                    log_message(
                        'Error sending submission email for entity ' . $this->strTable . ':' . $this->intId . ' to : ' . implode(',', $arrRecipient) . ' (' . $e . ')',
                        $this->strLogFile
                    );
                }
            }
        }
    }

    protected function createConfirmationAvisotaEmail($intMessageId, $strSalutationGroupId, $arrSubmissionData)
    {
        $objMessage = AvisotaHelper::getAvisotaMessage($intMessageId);

        $objMessage->setSubject(
            \StringUtil::parseSimpleTokens(
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
                \StringUtil::parseSimpleTokens(
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
                    }
                    else
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

    protected function sendConfirmationNotification(\NotificationCenter\Model\Message $objMessage, &$arrSubmissionData, &$arrToken)
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
            $arrSubmission = $blnSkipDefaults ? [] : $this->getDefaults();
        }

        if (($this->hasNoEntity() || !$this->hasDatabaseTable()) && is_array($arrSubmission))
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
                }
                else
                {
                    $objSubmission->{$strField} = $varValue;
                }
            }
        }

        return (!$this->hasDatabaseTable() || $this->hasNoEntity()) ? $objSubmission : $this->objActiveRecord;
    }

    public function setSubmitCallbacks(array $callbacks)
    {
        $this->arrSubmitCallbacks = $callbacks;
    }
}
