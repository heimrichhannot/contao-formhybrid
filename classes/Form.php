<?php

namespace HeimrichHannot\FormHybrid;

use Contao\Controller;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Firebase\JWT\JWT;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Haste\Util\FormSubmission;
use HeimrichHannot\Haste\Util\Salutations;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\NotificationCenterPlus\MessageModel;
use HeimrichHannot\Request\Request;
use HeimrichHannot\StatusMessages\StatusMessage;

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

        $this->checkOptIn();
        $this->checkOptOut();
    }

    public function generate()
    {
        return parent::edit();
    }

    protected function checkOptIn()
    {
        if (!$this->addOptIn)
        {
            return;
        }

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
            Controller::redirect(Url::removeQueryString([Formhybrid::OPT_IN_REQUEST_ATTRIBUTE]));
        }
    }

    protected function checkOptOut()
    {
        if (!$this->addOptOut)
        {
            return;
        }
        if (!\Database::getInstance()->fieldExists(FormHybrid::OPT_OUT_DATABASE_FIELD, $this->table))
        {
            throw new \Exception(
                'Opt-out requires existing field `' . FormHybrid::OPT_OUT_DATABASE_FIELD . '` in database table `' . $this->table
                . '`. Run `\HeimrichHannot\FormHybrid\FormHybrid::addOptOutFieldToTable(' . $this->table . ')` within DCA `' . $this->table
                . '.php!`'
            );
        }

        if (Request::hasGet(Formhybrid::OPT_OUT_REQUEST_ATTRIBUTE))
        {
            $this->isOptOut = "1";
            $this->removeSubscription();
            // remove parameter from query string and reload current page
            \Controller::redirect(Url::removeQueryString([Formhybrid::OPT_OUT_REQUEST_ATTRIBUTE]));
        }

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
        $objModel = $strModelClass::findByPk($this->intId);

        if (empty($objModel))
        {
            $objModel = new $strModelClass();
            $objModel->setRow($objResult->row());
        }

        $strRow = FormHybrid::OPT_IN_DATABASE_FIELD;
        $objModel->$strRow = "";

        // Always add opt-out token, if Database field added to dca:
        $modelData = $objModel->row();
        $strOptOutRow = FormHybrid::OPT_OUT_DATABASE_FIELD;

        if (isset($modelData[$strOptOutRow]))
        {
            $strToken = static::generateUniqueToken();
            $objModel->$strOptOutRow = $strToken;
            $objModel->tstamp = time();
        }

        if ($this->optInConfirmedProperty)
        {
            $strConfirmationProperty = $this->optInConfirmedProperty;
            $objModel->$strConfirmationProperty = true;
			$objModel->tstamp = time();
        }

        $objModel->save();
        $this->objActiveRecord = $objModel;

        $arrSubmissionData = FormSubmission::prepareData($objModel, $this->strTable, $this->dca, $this, $this->arrEditable);

        $this->createSuccessNotifications($arrSubmissionData);

        $optInJumpTo = null;
        if ($this->optInJumpTo)
        {
            $optInJumpTo = PageModel::findByPk($this->optInJumpTo);
        }

        if (!$this->isSilentMode() && !$optInJumpTo)
        {
            $this->createSuccessMessage($arrSubmissionData, true);
        }

        $this->addOptInPrivacyProtocolEntry($objData->submission);

        $this->afterActivationCallback($this, $objModel, $objData);

        if ($optInJumpTo)
        {
            $strUrl = Controller::generateFrontendUrl($optInJumpTo->row(), null, null, true);
            Controller::redirect($strUrl);
        }

        return true;
    }

    protected function removeSubscription()
    {
        $strJWT = Request::getGet(Formhybrid::OPT_OUT_REQUEST_ATTRIBUTE);

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
            \Database::getInstance()->prepare('SELECT * FROM ' . $objData->table . ' WHERE ' . FormHybrid::OPT_OUT_DATABASE_FIELD . ' = ?')->limit(1)->execute($objData->token);

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
        $objModel = $strModelClass::findById($this->intId);
        if (!$objModel)
        {
            $this->createInvalidOptInTokenMessage();

            return false;
        }

        $arrData = $objModel->row();

        /*
         * Delete entry
         */
        $objModel->delete();

        $this->objActiveRecord = $objModel;

        $arrSubmissionData = FormSubmission::tokenizeData($arrData, '');

        if (!$this->isSilentMode())
        {
            $this->createSuccessMessage($arrSubmissionData);
        }
        $this->afterUnsubscribeCallback($this, $objModel);

        if ($this->optOutJumpTo && $objTarget = \PageModel::findByPk($this->optOutJumpTo))
        {
            $strUrl = \Controller::generateFrontendUrl($objTarget->row(), null, null, true);
            \Controller::redirect($strUrl);
        }

        return true;
    }

    protected function processForm()
    {
        if (Request::getPost(FORMHYBRID_NAME_EXPORT))
        {
            if (class_exists('HeimrichHannot\FieldpaletteBundle\HeimrichHannotContaoFieldpaletteBundle')) {
                $fieldpaletteModel = new \HeimrichHannot\FieldpaletteBundle\Model\FieldPaletteModel();
                $intConfigId = $fieldpaletteModel->findById($this->formHybridExportConfigs[0])->formhybrid_formHybridExportConfigs_config;
            } else {
                $fieldpaletteModel = 'HeimrichHannot\FieldPalette\FieldPaletteModel';
                $intConfigId = $fieldpaletteModel::findById($this->formHybridExportConfigs[0])->formhybrid_formHybridExportConfigs_config;
            }

            if (class_exists('HeimrichHannot\ContaoExporterBundle\HeimrichHannotContaoExporterBundle')) {
                $exporterClass = 'HeimrichHannot\ContaoExporterBundle\Model\ExporterModel';
            } else {
                $exporterClass = 'HeimrichHannot\Exporter\ExporterModel';
            }

            $objConfig   = $exporterClass::findById($intConfigId);

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

        if ($this->isDuplicateEntityError)
        {
            $this->createDublicateEntryMessage();
            return;
        }

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

        // HOOK: custom logic before sending notifications
        if (isset($GLOBALS['TL_HOOKS']['formhybridBeforeCreateNotifications']) && \is_array($GLOBALS['TL_HOOKS']['formhybridBeforeCreateNotifications']))
        {
            foreach ($GLOBALS['TL_HOOKS']['formhybridBeforeCreateNotifications'] as $callback)
            {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($arrSubmissionData, $this);
            }
        }

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

    protected function afterActivationCallback(\DataContainer $dc, $objModel, $jwtData = null)
    {
    }

    protected function afterUnsubscribeCallback(\DataContainer $dc)
    {
    }

    protected function createOptInNotification($arrSubmissionData)
    {
        if (($objMessage = MessageModel::findPublishedById($this->optInNotification)) !== null)
        {
            $arrToken = FormSubmission::tokenizeData($arrSubmissionData);

            $strToken = static::generateUniqueToken();

            if ($this->hasNoEntity())
            {
                $instance = $this->findOptInModelInstance();
            }
            else
            {
                $this->objActiveRecord->refresh();

                $instance = $this->objActiveRecord;
            }

            if ($instance instanceof Model)
            {
                $instance->{FormHybrid::OPT_IN_DATABASE_FIELD} = $strToken;
                $instance->save();
            }

            $data = [
                'table' => $this->table,
                'token' => $strToken,
                'date'  => time(),
            ];

            if ($instance instanceof Model)
            {
				$submission = $this->objActiveRecord->row();
            	if ($this->formHybridfilterTokenFields)
				{
					$tokenFields = deserialize($this->formHybridTokenFields, true);
					foreach ($submission as $fieldName => $fieldValue)
					{
						if (in_array($fieldName, $tokenFields))
						{
							$data['submission'][$fieldName] = $fieldValue;

						}
					}
				}
				else {
					$data['submission'] = $submission;
				}
            }

            if (in_array('privacy', \ModuleLoader::getActive()) && $privacyToken = Request::getGet(\HeimrichHannot\Privacy\Privacy::OPT_IN_OUT_TOKEN_PARAM))
            {
                try {
                    $decoded         = \Firebase\JWT\JWT::decode($privacyToken, \Contao\Config::get('encryptionKey'), ['HS256']);
                    $decoded         = (array)$decoded;
                    $data['privacy_prefill_data'] = (array)$decoded['data'];
                } catch (\Exception $e) {}
            }

            $strJWT = JWT::encode(
                $data,
                \Config::get('encryptionKey')
            );

            $arrToken['opt_in_token'] = $strJWT;
            $arrToken['opt_in_link']  = Url::addQueryString(
                FormHybrid::OPT_IN_REQUEST_ATTRIBUTE . '=' . $strJWT,
                AjaxAction::removeAjaxParametersFromUrl(\Environment::get('uri'))
            );

            $arrToken['salutation_submission'] = Salutations::createSalutation($GLOBALS['TL_LANGUAGE'], $this->objActiveRecord);

            if ($this->sendOptInNotification($objMessage, $arrSubmissionData, $arrToken))
            {
                $objMessage->send($arrToken, $GLOBALS['TL_LANGUAGE']);
            }
        }
    }

    protected function findOptInModelInstance() {
        $table = $this->strTable;
        $modelClass = Model::getClassFromTable($table);

        if (!class_exists($modelClass))
        {
            return false;
        }
        if (!$this->objModule->formHybridOptInModelRetrievalProperty) {
            return false;
        }

        return  $modelClass::findOneBy([$table . '.' . $this->objModule->formHybridOptInModelRetrievalProperty . '=?'], [$this->objActiveRecord->{$this->objModule->formHybridOptInModelRetrievalProperty}]);
    }

    protected function addOptInPrivacyProtocolEntry($submissionData = null)
    {
        if (!in_array('privacy', \ModuleLoader::getActive()) || !$this->formHybridOptInAddPrivacyProtocolEntry)
        {
            return;
        }

        $protocolUtil = new \HeimrichHannot\Privacy\Util\ProtocolUtil();

        $data = $protocolUtil->getMappedPrivacyProtocolFieldValues($submissionData, deserialize($this->objModule->formHybridOptInPrivacyProtocolFieldMapping, true));
        $data['description'] = $this->objModule->formHybridOptInPrivacyProtocolDescription;
        $data['table'] = $this->objModule->formHybridDataContainer;

        $protocolManager = new \HeimrichHannot\Privacy\Manager\ProtocolManager();
        $protocolManager->addEntryFromModule(
            $this->objModule->formHybridOptInPrivacyProtocolEntryType,
            $this->objModule->formHybridOptInPrivacyProtocolArchive,
            $data,
            $this->objModule,
            'heimrichhannot/contao-formhybrid'
        );
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

    protected function createInvalidOptInTokenMessage()
    {
        $this->successMessage = $GLOBALS['TL_LANG']['formhybrid']['messages']['invalidOptInToken'];

        StatusMessage::addError($this->successMessage, $this->objModule->id, 'alert alert-danger');
    }

    protected function createDublicateEntryMessage()
    {
        $this->successMessage = $GLOBALS['TL_LANG']['formhybrid']['messages']['dublicateEntry'];

        StatusMessage::addError($this->successMessage, $this->objModule->id, 'alert alert-danger');
    }

    protected function createSuccessMessage($arrSubmissionData, $blnForceSuccess = false)
    {
        $strMessage = !empty($this->successMessage) ? $this->successMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['success'];

        if ($this->isOptOut && !$blnForceSuccess)
        {
            $strMessage = !empty($this->optOutSuccessMessage) ? $this->optOutSuccessMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['optOut'];
        }
        elseif ($this->addOptIn && !$blnForceSuccess)
        {
            $strMessage = !empty($this->optInSuccessMessage) ? $this->optInSuccessMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['optIn'];
        }

        $this->successMessage = StringUtil::parseSimpleTokens(
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

    protected function sendOptInNotification(\NotificationCenter\Model\Message $objMessage, $arrSubmissionData, &$arrToken)
    {
        return true;
    }

    protected function sendSubmissionNotification(\NotificationCenter\Model\Message $objMessage, &$arrSubmissionData, &$arrToken)
    {
        return true;
    }

    protected function sendConfirmationNotification(\NotificationCenter\Model\Message $objMessage, &$arrSubmissionData, &$arrToken)
    {
        return true;
    }

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

    /**
     * Generate unique token
     * Used for opt-in and opt-out tokens.
     *
     * @return string
     */
    public static function generateUniqueToken ()
    {
        $strToken = StringUtil::binToUuid(\Database::getInstance()->getUuid());
        return $strToken;
    }
}
