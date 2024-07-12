<?php

namespace HeimrichHannot\FormHybrid;

use Contao\Dbafs;
use Contao\File;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\Ajax\Ajax;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Ajax\Response\ResponseRedirect;
use HeimrichHannot\Exporter\Exporter;
use HeimrichHannot\Exporter\ModuleExporter;
use HeimrichHannot\FileCredit\FilesModel;
use HeimrichHannot\FormHybrid\Event\FormhybridBeforeCreateWidgetEvent;
use HeimrichHannot\FormHybrid\Event\FormhybridBeforeRenderFormEvent;
use HeimrichHannot\Haste\Util\Arrays;
use HeimrichHannot\Haste\Util\FormSubmission;
use HeimrichHannot\Haste\Util\StringUtil;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\Request\Request;
use HeimrichHannot\StatusMessages\StatusMessage;
use HeimrichHannot\Versions\Version;

class DC_Hybrid extends \DataContainer
{
    protected $arrData = [];

    protected $strFormId;

    protected $strFormName;

    protected $strMethod = FORMHYBRID_METHOD_GET;

    protected $strAction = null;

    protected $hasUpload = false;

    protected $novalidate = true;

    protected $strTemplate = 'formhybrid_default';

    protected $strTemplateStart = 'formhybridStart_default';

    protected $strTemplateStop = 'formhybridStop_default';

    protected $isSubmitted = false;

    protected $doNotSubmit = false;

    protected $dca;

    protected $arrDefaults = [];

    protected $arrFields = [];

    protected $objConfig = null;

    protected $arrConfig = [];

    protected $arrHiddenFields = [];

    protected $arrSubFields = [];

    protected $arrEditable = [];

    protected $arrReadOnly = [];

    protected $arrRequired = [];

    protected $arrPermanentFields = [];

    protected $arrInvalidFields = [];

    protected $overwriteRequired = false;

    protected $arrSubmission = [];

    protected $strSubmit = '';

    protected $async = false;

    protected $arrAttributes = [];

    protected $useCustomSubTemplates = false;

    protected $saveToBlob = false;

    public $objModule; // public, required by callbacks

    protected $isFilterForm = false;

    protected $skipValidation = false;

    protected $mode = FORMHYBRID_MODE_CREATE;

    private $hasDatabaseTable;

    protected $noEntity = false;

    protected $blnSilentMode = false;

    protected $objAjax;

    protected $arrOriginalRow = null;

    protected $relatedAjaxRequest = false;

    protected $activePalette = 'default';

    private $resetAfterSubmission = true;

    private $invalid = false;

    protected $isDuplicateEntityError = false;

    /**
     * Force entity creation, also if ajax scope does not match with formhybrid scope
     *
     * @var bool
     */
    protected $forceCreate = false;

    protected static $arrPermanentFieldClasses = [
        'Contao\FormCaptcha',
    ];

    /**
     * Set true, and skip ajax form request handling.
     * Might be helpful if you want inject Form within your own module and handle ajax by own methods.
     *
     * @var bool
     */
    protected $skipFormAjax = false;

    public function __construct($strTable = '', $varConfig = null, $intId = 0)
    {
        $this->objConfig = $varConfig;

        if (!($varConfig instanceof FormConfiguration)) {
            $this->objConfig = new FormConfiguration($varConfig);
        }

        $this->arrConfig = $this->objConfig->getData();
        $this->setData($this->objConfig->getData());
        $this->objModule = $this->objConfig->getModule();

        // backwards compatibility for direct DC_Hybrid calls
        if ($strTable) {
            $this->strTable = $strTable;
        }

        if ($this->renderStart && Request::getInstance()->request->count() > 0 && !$intId) {
            // get id from FormSession
            $this->intId = FormSession::getSubmissionId(FormHelper::getFormId($this->strTable, $this->objModule->id));
        } else {
            if ($this->renderStop) {
                if (!$this->startModule) {
                    throw new \Exception('Start module/content element is missing.');
                }

                $this->intId = FormSession::getSubmissionId(FormHelper::getFormId($this->strTable, $this->startModule));
            }
        }

        $this->intId                  = $this->intId ?: $intId;
        $this->strFormId              = $this->getFormId();
        $this->getConfig()->strFormId = $this->getFormId();
        $this->strAction = $this->getConfig()->strAction;
        $this->strFormName = $this->getFormName();

        if ($this->addEditableRequired) {
            $this->overwriteRequired = true;
        }

        $this->strInputMethod = $strInputMethod = strtoupper($this->strMethod);

        if ('dev' === System::getContainer()->getParameter('kernel.environment') && ($blnSkipValidation = $this->getInputValue(FORMHYBRID_NAME_SKIP_VALIDATION)) !== null) {
            $this->setSkipValidation($blnSkipValidation);
        }

        // GET is checked for each field separately
        $this->isSubmitted = ($this->getInputValue('FORM_SUBMIT') == $this->getFormId());

        // in case of ajax request, when request token expired and form name is the same, restore form for new id from old input
        if (!$this->isSubmitted && Ajax::isRequestTokenExpired() && (StringUtil::startsWith($this->getInputValue('FORM_SUBMIT'), $this->getFormId(false)))) {
            $this->isSubmitted = true;
        }

        $this->hasDatabaseTable = \Database::getInstance()->tableExists($this->strTable);

        if ($this->hasDatabaseTable()) {
            $strModelClass = \Model::getClassFromTable($this->strTable);

            if (!class_exists($strModelClass)) {
                throw new \Exception(sprintf('Database table %s exists, but no model found, please create one.', $this->strTable));
            }
        }

        // transform filterForm flag for internal usage
        if ($this->isFilterForm) {
            $this->setSkipValidation(true);
            $this->setNoEntity(true);
            $this->setReset(false);
            $this->setSilentMode(true);
        }

        $this->import('Database');

        \Controller::loadDataContainer($this->strTable);
        \System::loadLanguageFile($this->strTable);

        $this->objModule = $this->objConfig->getModule();

        $blnCreated = $this->create();

        $this->loadDC();

        $this->setPermanentFields();

        $this->initialize($blnCreated);

        Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'toggleSubpalette', new FormAjax($this));
        Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'reload', new FormAjax($this));
    }

    protected function create()
    {
        if ($this->hasDatabaseTable() && !$this->hasNoEntity()) {
            $strModelClass = \Model::getClassFromTable($this->strTable);
        }

        if (!$this->intId || !is_numeric($this->intId)) {
            if ($this->objModule !== null && (Ajax::isRelated(Form::FORMHYBRID_NAME) !== false || $this->isForceCreate())) {
                $this->objActiveRecord = $this->createSubmission($strModelClass ?? null);

                // set tstamp by default to 0
                if ($this->hasDatabaseTable() && !$this->hasNoEntity() && \Database::getInstance()->fieldExists('tstamp', $this->strTable)) {
                    $this->objActiveRecord->tstamp = 0;
                }

                $this->setDefaults($GLOBALS['TL_DCA'][$this->strTable]);
                $this->save(); // initially try to save record, as ajax requests for example require entity model

                // register form id in session if we got a new one from save()
                if ($this->intId && is_numeric($this->intId)) {
                    FormSession::addSubmissionId($this->getFormId(false), $this->getId());
                } else {
                    if ($this->hasDatabaseTable() && !$this->hasNoEntity()) {
                        $this->invalid = true;
                        StatusMessage::addError(
                            $GLOBALS['TL_LANG']['formhybrid']['messages']['error']['invalidId'],
                            $this->objModule->id,
                            'alert alert-danger'
                        );
                    }

                    return false;
                }

                return true;
            }
        }

        return false;
    }

    protected function initialize($blnCreated = false)
    {
        // clear files cache
        if (!$this->isSubmitted()) {
            unset($_SESSION['FILES']);
        }

        // load the model
        // don't load any class if the form's a filter form -> submission should be used instead
        if ($this->hasDatabaseTable() && !$this->hasNoEntity()) {
            $strModelClass = \Model::getClassFromTable($this->strTable);
        }

        if (class_exists($strModelClass ?? '')) {
            if (!$blnCreated && ($objModel = $strModelClass::findByPk($this->intId)) !== null) {
                $this->objActiveRecord = $objModel;
                $this->setMode(FORMHYBRID_MODE_EDIT);

                if ($this->saveToBlob) {
                    $this->loadFromBlob(deserialize($objModel->formHybridBlob, true));
                }

                // redirect on specific field value
                static::doFieldDependentRedirect();
            } // we require a module context for entity creation
            else {
                if ($this->objModule !== null && (Ajax::isRelated(Form::FORMHYBRID_NAME) !== false || $this->isForceCreate())) {

                    // do nothing, if ajax request but not related to formhybrid
                    // otherwise a new submission will be generated and validation will fail
                    if ($this->hasDatabaseTable()) {
                        $this->setDefaults(
                            $GLOBALS['TL_DCA'][$this->strTable]
                        ); // set defaults again, as they might has been overwritten within loadDC/modifyDC
                        $this->setSubmission();

                        // Call the oncreate_callback after modifyDC has been called
                        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncreate_callback'])) {
                            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncreate_callback'] as $callback) {
                                if (is_array($callback)) {
                                    $this->import($callback[0]);
                                    $this->{$callback[0]}->{$callback[1]}($this->strTable, $this->intId, $this->objActiveRecord->row(), $this);
                                } elseif (is_callable($callback)) {
                                    $callback($this->strTable, $this->intId, $this->objActiveRecord->row(), $this);
                                }

                                if (!$this->saveToBlob) {
                                    $this->objActiveRecord->refresh();
                                }
                            }
                        }

                        $this->save(); // initially try to save record, as ajax requests for example require entity model
                        $this->doIdDependentRedirectToEntity();
                    } elseif ($this->hasDatabaseTable() && !$this->hasNoEntity()) {
                        $this->invalid = true;
                        StatusMessage::addError(
                            $GLOBALS['TL_LANG']['formhybrid']['messages']['error']['invalidId'],
                            $this->objModule->id,
                            'alert alert-danger'
                        );
                    }
                }
            }
        } else {
            if (!$this->hasDatabaseTable() || $this->hasNoEntity()) {
                $this->setSubmission();
            }
        }

        if ($this->viewMode == FORMHYBRID_VIEW_MODE_READONLY) {
            $this->strTemplate = $this->readonlyTemplate;
            $this->setDoNotSubmit(true);
        }
    }

    /**
     * Redirect and append idGetParameter to url, depending on current configuration
     *
     * @param null $intId The entity id, if null $this->intId will be used from context
     */
    protected function doIdDependentRedirectToEntity($intId = null)
    {
        if ($intId === null) {
            $intId = $this->intId;
        }

        if (!$intId) {
            return;
        }

        if ($this->allowIdAsGetParameter && $this->appendIdToUrlOnCreation && Request::getGet($this->idGetParameter) != $intId) {
            $strUrl = Url::addQueryString($this->idGetParameter.'='.$this->intId);

            // Use AjaxAction::generateUrl(Form::FORMHYBRID_NAME, null, array(Ajax::AJAX_ATTR_AJAXID => $this->objModule->id)) for formhybrid ajax create links
            if (Ajax::isRelated(Form::FORMHYBRID_NAME) !== null && \HeimrichHannot\Request\Request::getGet(Ajax::AJAX_ATTR_AJAXID)) {
                $objResponse = new ResponseRedirect();
                $objResponse->setUrl($strUrl);
                $objResponse->output();
            }

            \Controller::redirect($strUrl);
        }
    }

    protected function createSubmission($strModelClass = null)
    {
        $objSubmission = new Submission();

        if ($strModelClass !== null && class_exists($strModelClass)) {
            $objReflection = new \ReflectionClass($strModelClass);

            if (!$objReflection->isAbstract()) {
                $objSubmission = new $strModelClass();
            }
        }

        return $objSubmission;
    }

    public function loadFromBlob($arrBlob)
    {
        foreach ($arrBlob as $strField => $varValue) {
            $this->objActiveRecord->{$strField} = $varValue;
        }
    }

    public function saveToBlob()
    {
        $varBlob                               = $this->objActiveRecord->formHybridBlob;
        $this->objActiveRecord->formHybridBlob = null;

        \Database::getInstance()->prepare(
            "UPDATE $this->strTable SET $this->strTable.formHybridBlob = ? WHERE id=?"
        )->execute(serialize($this->objActiveRecord->row()), $this->intId);

        $this->objActiveRecord->formHybridBlob = $varBlob;
    }

    public function doFieldDependentRedirect()
    {
        if ($this->addFieldDependentRedirect) {
            $arrConditions = $this->fieldDependentRedirectConditions;
            $blnRedirect   = true;

            if (!empty($arrConditions)) {
                foreach ($arrConditions as $arrCondition) {
                    if ($this->activeRecord->{$arrCondition['field']} != $this->replaceInsertTags($arrCondition['value'])) {
                        $blnRedirect = false;
                    }
                }

            }

            if ($blnRedirect) {
                global $objPage;

                if (($objPageJumpTo = \PageModel::findByPk($this->fieldDependentRedirectJumpTo)) !== null
                    || $objPageJumpTo = $objPage) {
                    $strRedirect = \Controller::generateFrontendUrl($objPageJumpTo->row());

                    if ($this->fieldDependentRedirectKeepParams) {
                        $arrParamsToKeep = explode(',', $this->fieldDependentRedirectKeepParams);
                        if (!empty($arrParamsToKeep)) {
                            foreach (Url::getUriParameters(Url::getUrl()) as $strParam => $strValue) {
                                if (in_array($strParam, $arrParamsToKeep)) {
                                    $strRedirect = Url::addQueryString($strParam.'='.$strValue, $strRedirect);
                                }
                            }
                        }
                    }

                    if (!$this->deactivateTokens) {
                        $strRedirect = Url::addQueryString('token='.\RequestToken::get(), $strRedirect);
                    }

                    StatusMessage::resetAll();
                    \Controller::redirect($strRedirect);
                }
            }
        }
    }

    /**
     * Auto-generate a form to edit the current database record
     *
     * @param integer
     * @param integer
     *
     * @return string
     */
    public function edit($intID = null, $ajaxId = null)
    {
        // render
        if ($this->renderStart) {
            $this->async = false; // async form submission for start/elements/stop not available yet

            $this->Template = new \FrontendTemplate($this->strTemplateStart);
            $this->generateStart();

            return \Controller::replaceInsertTags($this->Template->parse(), false);
        }

        $this->Template = new \FrontendTemplate($this->strTemplate);

        if ($this->renderStop) {
            $this->Template = new \FrontendTemplate($this->strTemplateStop);
        }

        if (empty($this->dca)) {
            return false;
        }

        // run callbacks on ajax reload
        if (is_array($this->dca['config']['onreload_callback'] ?? null)) {
            foreach ($this->dca['config']['onreload_callback'] as $callback) {

                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this);
                } elseif (is_callable($callback)) {
                    $callback($this);
                }

                // reload model from database, maybe something has changed in callback
                if (!$this->saveToBlob) {
                    $this->objActiveRecord->refresh();
                }
            }
        }

        $blnAjax = $this->generateFields($ajaxId);

        if ($blnAjax) {
            $objTemplate = $this->generateSubpalette($ajaxId);

            return \Controller::replaceInsertTags($objTemplate->parse(), false);
        }

        if ($this->isSubmitted && $this->isDoNotSubmit()) {
            $this->runOnValidationError($this->arrInvalidFields);
        }

        $blnSubmittedBeforeReset = false;

        if ($this->isSubmitted && !$this->isDoNotSubmit()) {
            // run field callbacks, must be before save(), same as contao
            $this->runCallbacks();

            // save for save_callbacks
            $this->save();

            if (in_array('exporter', \ModuleLoader::getActive()) && $this->exportAfterSubmission) {
                $this->exportAfterSubmission();
            }

            if (class_exists('HeimrichHannot\ContaoExporterBundle\HeimrichHannotContaoExporterBundle') && $this->exportAfterSubmission) {
                $this->exportAfterSubmission();
            }

            $this->addPrivacyProtocolEntry();

            // process form
            $this->processForm();

            // generate messages and remove them from the session automatically
            $this->Template->message = StatusMessage::generate($this->objModule->id);

            // single submission will not render form again after submission
            if ($this->singleSubmission) {
                $this->invalid = true;
            }
            // reset form is default. disable by $this->setReset(false)
            // Exception: filter forms should never been reset after submit
            else {
                if ($this->getReset()) {
                    $this->resetForm();
                    $blnSubmittedBeforeReset = true;
                }
            }

            $this->redirectAfterSubmission();
        }

        $this->generateStart();

        $this->Template->fields      = $this->arrFields;
        $this->Template->isSubmitted = $this->isSubmitted();
        $this->Template->doNotSubmit = $this->isDoNotSubmit();
        $this->Template->submission  = $this->objActiveRecord;
        $this->Template->hidden      = $this->generateHiddenFields();
        $this->Template->invalid     = $this->invalid;
        $this->Template->config      = $this->objConfig;
        $this->Template->action      = $this->getConfig()->strAction;

        if (isset($GLOBALS['TL_HOOKS'][FormhybridBeforeRenderFormEvent::NAME]) && \is_array($GLOBALS['TL_HOOKS'][FormhybridBeforeRenderFormEvent::NAME]))
        {
            $event = new FormhybridBeforeRenderFormEvent($this->Template, $this->objModule, $this);
            foreach ($GLOBALS['TL_HOOKS'][FormhybridBeforeRenderFormEvent::NAME] as $callback)
            {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($event);
            }
        }

        $this->compile();

        $strBuffer = \Controller::replaceInsertTags($this->Template->parse(), false);

        @Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'asyncFormSubmit', new FormAjax($this, $strBuffer, $blnSubmittedBeforeReset));
        @Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'reload', new FormAjax($this, $strBuffer));

        return $strBuffer;
    }

    public function generateFields($ajaxId = null)
    {
        $arrFields              = $this->arrEditable;
        $arrSubFields           = [];
        $arrActivePaletteFields = $this->retrieveActivePaletteFields();
        $blnAjax                = false;

        // subpalettes
        $arrSelectors = $this->dca['palettes']['__selector__'] ?? null;

        if (is_array($arrSelectors)) {
            $toggleSubpalette = str_replace('sub_', '', $ajaxId);

            foreach ($arrSelectors as $strName) {
                if (($arrPaletteFields = $this->retrieveActivePaletteFields($strName)) !== false) {
                    $arrActivePaletteFields = $arrPaletteFields;
                    break;
                }
            }

            foreach ($arrSelectors as $strName) {
                [
                    $blnActive, $strSubPalette, $arrFields, $arrSubPaletteFields, $blnAutoSubmit, $blnToggleSubpalette
                ] = $this->retrieveSubpaletteWithState($strName, $arrFields, $arrActivePaletteFields);

                if (!$blnToggleSubpalette) {
                    continue;
                }

                // active by default
                if ($blnActive) {
                    // selector field is visible
                    if (in_array($strName, $this->arrEditable)) {
                        if (is_array($arrSubPaletteFields) && is_array($arrFields)) {
                            $arrFields = array_diff($arrFields, $arrSubPaletteFields);
                        }

                        $arrSubFields[$strName] = $arrSubPaletteFields;
                    } // for example selector is triggered by default value
                    else {
                        // do nothing, fields should remain in $arrFields
                    }
                } // inactive
                else {
                    // remove permanent subpalette fields from $arrSubPaletteFields
                    if (is_array($arrSubPaletteFields) && is_array($this->arrPermanentFields)) {
                        $arrSubPaletteFields = array_diff($arrSubPaletteFields, $this->arrPermanentFields);
                    }

                    // subpalette fields should not remain in arrFields, they belong to parent field
                    if (is_array($arrSubPaletteFields) && is_array($arrFields)) {
                        $arrFields = array_diff($arrFields, $arrSubPaletteFields);
                    }
                }

                // if subpalette is requested, set state to active, clear parent fields and break
                if ($toggleSubpalette == $strName) {
                    $blnAjax      = true;
                    $arrFields    = [$strName];
                    $arrSubFields = [$strName => $arrSubPaletteFields];
                    break; // this function can only return one subpalette at once
                }
            }

            // check for active palette from typeselector
            foreach ($arrSelectors as $strSelector) {
                $varValue   = $this->getFieldValue($strSelector);
                $strPalette = null;
                if (is_string($varValue)) {
                    $strPalette = $this->dca['palettes'][$varValue] ?? null;

                }
                $arrOptions = deserialize($this->dca['fields'][$strSelector]['options'], true);

                $arrCallback = $this->dca['fields'][$strSelector]['options_callback'];

                if (is_array($arrCallback) && class_exists($arrCallback[0])) {
                    $objInstance = \Controller::importStatic($arrCallback[0]);

                    try {
                        $arrOptions  = $objInstance->{$arrCallback[1]}($this);
                    } catch (\Error $e) {
                        $arrOptions = [];
                    }
                }

                // check for existing subpalettes in order to distinguish between type and subpalette selectors
                $blnIsSubPaletteSelector = false;
                if (is_array($this->dca['subpalettes'])) {
                    foreach (array_keys($this->dca['subpalettes']) as $strSubPaletteSelector) {
                        if (StringUtil::startsWith($strSubPaletteSelector, $strSelector.'_')) {
                            $blnIsSubPaletteSelector = true;
                            break;
                        }
                    }
                }

                if ($varValue && is_string($varValue) && isset($this->dca['palettes'][$varValue]) && !$blnIsSubPaletteSelector && $strPalette
                    && in_array($varValue, $arrOptions)) {
                    // no messages
                    $this->setSilentMode($this->isSkipValidation());
                    $this->dca['fields'][$strSelector]['eval']['skipValidationOnSubmit'] = true;

                    // only one palette can be active at a time
                    $this->activePalette = $varValue;
                    break;
                }
            }
        }


        // remove fields not existing in the current palette
        $arrFields = array_intersect(
            $arrFields,
            array_merge($arrActivePaletteFields, $this->arrPermanentFields)
        );

        // add palette fields
        foreach ($arrFields as $strName) {
            $this->addField($strName);
        }

        // add subpalette fields
        foreach ($arrSubFields as $strParent => $arrFields) {
            // check if subpalette has fields
            if (empty($arrFields)) {
                continue;
            }

            // skip field, if parent selector is not active
            if (!is_array($this->arrFields) || !isset($this->arrFields[$strParent])) {
                continue;
            }

            foreach ($arrFields as $strName) {
                $this->addSubField($strName, $strParent, $blnAjax);
            }

            if (!$blnAjax) {
                $objSubTemplate = $this->generateSubpalette('sub_'.$strParent);

                // parent field must exist
                if (!$this->arrFields[$strParent]) {
                    $this->addField($strParent);
                }

                // append subfields to parent field
                if ($this->arrFields[$strParent]) {
                    $this->arrFields[$strParent]->sub     = \Controller::replaceInsertTags($objSubTemplate->parse(), false);
                    $this->arrFields[$strParent]->subName = $strParent;
                }
            }
        }

        // add submit button
        if (!$blnAjax) {
            $this->generateSubmitField();
        }

        if ($this->addExportButton) {
            $this->generateExportSubmitField();
        }

        if (!$this->async) {
            $this->generateFormIdentifierField();
        }

        return $blnAjax;
    }

    /**
     * Return the current input value considering $this->strInputMethod
     *
     * @param string $strKey The requested input key
     *
     * @return mixed The value of the requested input
     */
    protected function getInputValue($strKey)
    {
        if (strtoupper($this->strInputMethod) == strtoupper(FORMHYBRID_METHOD_POST)) {
            return Request::hasPost($strKey) ? Request::getPost($strKey) : null;
        }

        return Request::hasGet($strKey) ? Request::getGet($strKey) : null;
    }


    /**
     * Return the value, considering default and submitted values
     *
     * @param $strName
     *
     * @return mixed
     */
    protected function getFieldValue($strName)
    {
        $varValue = null;
        if (isset($this->dca['fields'][$strName])) {
            $varValue = $this->getDefaultFieldValue($strName, $this->dca['fields'][$strName]);
        }

        if ($this->isSubmitted) {
            $inputValue = (strtoupper($this->strMethod) == 'POST') ? Request::getPost($strName) : Request::getGet($strName);
            if (in_array($strName, $this->arrEditable) && ($inputValue !== null || $this->dca['fields'][$strName]['inputType'] === 'checkbox')) {
                $varValue = $inputValue;
            }
        }

        return $varValue;
    }

    /**
     * Retrieve the active palette fields, for palettes type selector
     *
     * @param $strSelector
     *
     * @return boolean|array Return palette fields array if active, if default palette is active return true, otherwise false
     */
    protected function retrieveActivePaletteFields($strSelector = null)
    {
        if ($strSelector === null) {
            // fallback to default palette if forcePaletteRelation is active (default: true)
            if ($this->forcePaletteRelation) {
                return array_intersect($this->arrEditable, FormHelper::getPaletteFields($this->strTable, $this->dca['palettes']['default']));
            }

            return is_array($this->dca['fields']) ? array_keys($this->dca['fields']) : [];
        }

        $varValue = $this->getFieldValue($strSelector);
        if (!is_string($varValue) || empty($varValue)) {
            return false;
        }

        if (!isset($this->dca['palettes'][$varValue])) {
            return false;
        }

        if (is_array($this->arrDefaultValues) && in_array($strSelector, array_keys($this->arrDefaultValues))) {
            return array_intersect($this->arrEditable, FormHelper::getPaletteFields($this->strTable, $this->dca['palettes'][$varValue]));
        }

        // default palette does not limit field selection, as long the field is not editable
        if (!$this->forcePaletteRelation && $varValue == 'default' && !in_array($strSelector, $this->arrEditable)) {
            return is_array($this->dca['fields']) ? array_keys($this->dca['fields']) : [];
        }

        return array_intersect($this->arrEditable, FormHelper::getPaletteFields($this->strTable, $this->dca['palettes'][$varValue]));
    }

    /**
     * Retrieve the subpalette by the field selector
     *
     * @param string $strSelector
     * @param array  $arrFields
     * @param array  $arrActivePaletteFields
     *
     * @return array Return the state, subpalette name, the filtered fields array and autosubmit state
     */
    protected function retrieveSubpaletteWithState($strSelector, array $arrFields, array $arrActivePaletteFields = [])
    {
        $blnActive           = null;
        $blnAutoSubmit       = false;
        $strSubpalette       = null;
        $arrSubPaletteFields = [];
        $blnToggleSubpalette = false;

        $varValue = $this->getFieldValue($strSelector);

        // skip arrays, they cant be array keys
        if (is_array($varValue)) {
            return [$blnActive, $strSubpalette, $arrFields, $blnAutoSubmit];
        }

        // checkbox: addImage for example
        if (isset($this->dca['fields'][$strSelector]) && $this->dca['fields'][$strSelector]['inputType'] == 'checkbox' && !($this->dca['fields'][$strSelector]['eval']['multiple'] ?? false)) {
            if (!empty($this->dca['subpalettes'][$strSelector])) {
                $blnActive           = ($varValue == true);
                $strSubpalette       = $strSelector;
                $blnToggleSubpalette = true;

                $arrSubPaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strSubpalette]);
            }
        } // radio: source in tl_news (source_default, source_external)
        else {
            // subpalettes type selector (Example???)
            if (isset($this->dca['subpalettes'][$varValue])) {
                $blnAutoSubmit = true;
                $blnActive     = true;
                $strSubpalette = $varValue;
            } // concatenated type selector (e.g. source -> source_external)
            elseif (is_array($this->dca['subpalettes'] ?? null)) {
                $arrSubpalettes = Arrays::filterByPrefixes($this->dca['subpalettes'], [$strSelector.'_']);

                if (!empty($arrSubpalettes)) {
                    $blnToggleSubpalette = true; // also if no active type selector, cause no default value, toggleSubpalette
                    $blnExisting         = isset($arrSubpalettes[$strSelector.'_'.$varValue]);

                    if ($blnExisting) {
                        $blnActive = false;

                        if (in_array($strSelector, array_merge($arrActivePaletteFields, $this->arrPermanentFields))) {
                            $blnActive = true;
                        }

                        $strSubpalette       = $strSelector.'_'.$varValue;
                        $arrSubPaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strSubpalette]);
                    }

                    // remove concatenated type selector sibling subpalette fields that are not active
                    foreach ($arrSubpalettes as $strSubSiblingPalette => $strSubSiblingFields) {
                        $arrSubPaletteSiblingFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strSubSiblingPalette]);

                        foreach ($arrSubPaletteSiblingFields as $strSubPaletteSiblingField) {
                            // do never remove permanent fields
                            if (in_array($strSubPaletteSiblingField, $this->arrPermanentFields)) {
                                continue;
                            }

                            // field is not editable
                            if (!in_array($strSubPaletteSiblingField, array_merge($this->arrEditable, $this->arrPermanentFields))) {
                                Arrays::removeValue($strSubPaletteSiblingField, $arrSubPaletteFields);
                                continue;
                            }

                            // do not remove active concatenated type selector fields but check before if fields are editable or permanent
                            if ($blnActive && $strSubSiblingPalette === $strSubpalette) {
                                continue;
                            }

                            // field is also part of parent palette
                            if (!in_array($strSelector, $this->arrEditable) && in_array($strSubPaletteSiblingField, $this->arrEditable) && in_array($strSubPaletteSiblingField, $arrActivePaletteFields)) {
                                // remove field from subpalette fields as it is now stated as parent field
                                Arrays::removeValue($strSubPaletteSiblingField, $arrSubPaletteFields);
                                continue;
                            }

                            // remove sibling subpalette fields that are not active
                            Arrays::removeValue($strSubPaletteSiblingField, $arrFields);
                        }
                    }
                }
            }
        }

        return [$blnActive, $strSubpalette, $arrFields, $arrSubPaletteFields, $blnAutoSubmit, $blnToggleSubpalette];
    }


    protected function addField($strName)
    {
        if (!in_array($strName, array_keys($this->dca['fields']))) {
            return false;
        }

        if ($objField = $this->generateField($strName, $this->dca['fields'][$strName])) {
            $this->arrFields[$strName] = $objField;

            if ($objField->type == 'submit') {
                $this->strSubmit = $strName;
            }
        }

        return true;
    }

    protected function addSubField($strName, $strParent, $skipValidation = false)
    {
        if (!in_array($strName, array_keys($this->dca['fields']))) {
            return false;
        }

        if ($objField = $this->generateField($strName, $this->dca['fields'][$strName], $skipValidation)) {
            $this->arrSubFields[$strParent][$strName] = $objField;
        }

        return true;
    }

    protected function generateField($strName, $arrData, $skipValidation = false)
    {
        $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

        // overwrite the widget in readonly mode
        if ($this->viewMode == FORMHYBRID_VIEW_MODE_READONLY
            || ($this->viewMode == FORMHYBRID_VIEW_MODE_DEFAULT && $this->addReadOnly
                && in_array(
                    $strName,
                    $this->arrReadOnly
                ))) {
            $strClass       = 'HeimrichHannot\FormHybrid\FormReadonlyField';
            $skipValidation = true;
        }

        $strInputMethod = $this->strInputMethod;

        // Continue if the class is not defined
        if (!class_exists($strClass)) {
            return false;
        }

        $arrWidgetErrors = [];

        // prevent name for GET and submit widget, otherwise url will have submit name in
        if ($this->strMethod == FORMHYBRID_METHOD_GET && $arrData['inputType'] == 'submit') {
            $strName = '';
        }

        // to make captcha form related, add the form id without entity id
        if ($arrData['inputType'] == 'captcha') {
            $strName .= '_'.$this->getFormId(false);
            $varDefault = null;
            $varValue = null;
        }

        $this->strField     = $strName;
        $this->strInputName = $strName;

        if ($arrData['inputType'] != 'captcha') {
            // contains the load_callback!
            $varDefault = $this->getDefaultFieldValue($strName, $arrData);
            $varValue   = $varDefault;
        }

        if ($this->isSubmitted && !$skipValidation) {
            $inputValue = $this->getInputValue($strName);
            if ($inputValue !== null || ($inputValue === null && $arrData['inputType'] === 'checkbox')) {
                $varValue = $inputValue;
            }
            
            $varValue = FormSubmission::prepareSpecialValueForSave(
                $varValue,
                $arrData,
                $this->strTable,
                $this->intId,
                $varDefault,
                $arrWidgetErrors
            );
        }

        // overwrite required fields
        if ($this->overwriteRequired) {
            // set mandatory to false
            $arrData['eval']['mandatory'] = false;

            // overwrite mandatory by config
            if (!$arrData['eval']['mandatory'] && in_array($strName, $this->arrRequired)) {
                $arrData['eval']['mandatory'] = true;
            }
        }

        $arrData['eval']['tagTable'] = $this->strTable;

        // always disable validation for filter form
        if ($this->isFilterForm) {
            $arrData['eval']['mandatory'] = false;
        }

        $this->varValue     = is_array($varValue) ? $varValue : \Controller::replaceInsertTags($varValue);

        $arrWidget = \Widget::getAttributesFromDca(
            $arrData,
            $strName,
            is_array($varValue) ? $varValue : \Controller::replaceInsertTags($varValue),
            $strName,
            $this->strTable,
            $this
        );

        $this->updateWidget($arrWidget, $arrData, []);

        [
            $blnActive, $strSubPalette, $arrFields, $arrSubPaletteFields, $blnAutoSubmit, $blnToggleSubpalette
            ] = $this->retrieveSubpaletteWithState($strName, array_keys($this->arrFields));

        // support submitOnChange as form submission
        if (($arrData['eval']['submitOnChange'] ?? false) && $blnToggleSubpalette) {
            if ($blnAutoSubmit) {
                $arrWidget['onchange'] = $this->async ? 'FormhybridAjaxRequest.asyncSubmit(this.form);' : "this.form.submit();";
            } else {
                $strEvent = 'onclick';

                switch ($arrData['inputType']) {
                    case 'select':
                        $strEvent = 'onchange';
                        break;
                }

                $arrWidget[$strEvent] = "FormhybridAjaxRequest.toggleSubpalette(this, 'sub_".$strName."', '".$strName."', '".AjaxAction::generateUrl(
                        Form::FORMHYBRID_NAME,
                        'toggleSubpalette'
                    )."')";
                unset($arrWidget['submitOnChange']);
            }
        } // the field does trigger a form reload without validation
        else {
            if ($arrWidget['submitOnChange'] ?? false) {
                $strEvent = null;

                if ($arrWidget['onchange']) {
                    $strEvent = 'onchange';
                } else {
                    if ($arrWidget['onclick']) {
                        $strEvent = 'onclick';
                    }
                }

                if ($strEvent !== null) {
                    $arrWidget[$strEvent] = "FormhybridAjaxRequest.reload('".$this->getFormId()."', '".AjaxAction::generateUrl(Form::FORMHYBRID_NAME, 'reload')."')";

                    unset($arrWidget['submitOnChange']);
                }
            }
        }

        if (isset($GLOBALS['TL_HOOKS'][FormhybridBeforeCreateWidgetEvent::NAME]) && \is_array($GLOBALS['TL_HOOKS'][FormhybridBeforeCreateWidgetEvent::NAME]))
        {
            $event = new FormhybridBeforeCreateWidgetEvent($arrWidget, $strClass, $arrData, $this);
            foreach ($GLOBALS['TL_HOOKS'][FormhybridBeforeCreateWidgetEvent::NAME] as $callback)
            {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($event);
            }
            $strClass = $event->getWidgetClass();
            $arrWidget = $event->getWidgetData();
        }

        /** @var Widget $objWidget */
        $objWidget = new $strClass($arrWidget);

        if (isset($arrData['formHybridOptions'])) {
            $arrFormHybridOptions = $arrData['formHybridOptions'];

            $this->import($arrFormHybridOptions[0]);
            $objWidget->options = $this->{$arrFormHybridOptions[0]}->{$arrFormHybridOptions[1]}();
        }

        if ($objWidget instanceof \uploadable) {
            $this->hasUpload = true;
        }

        if ($this->isSubmitted) {
            // add filter class if filter is active
            if ($objWidget->value && $this->isFilterForm) {
                $objWidget->class = 'filtered';
            }

            // do not validate fields if not submitted or skipvalidation issset
            // do not submit if ajax request and group is not formhybrid, for example multifileupload (otherwise captcha fields will be validated does not match visible one)
            // do not validate disabled fields
            if (!($this->isSkipValidation() || $skipValidation) && (Ajax::isRelated(Form::FORMHYBRID_NAME) !== false) && ('disabled' !== $objWidget->disabled)) {
                FrontendWidget::validateGetAndPost($objWidget, $this->strMethod, $this->getFormId(), $arrData);

                if (is_array($arrWidgetErrors)) {
                    foreach ($arrWidgetErrors as $strError) {
                        $objWidget->addError($strError);
                    }
                }

                // Make sure unique fields are unique
                if (($arrData['eval']['unique'] ?? false) && $varValue != ''
                    && !\Database::getInstance()->isUniqueValue(
                        $this->strTable,
                        $strName,
                        $varValue,
                        $this->intId > 0 ? $this->intId : null
                    )) {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $strName));
                }

                // trigger save_callbacks before assertion of the new value to objActiveRecord
                try
                {
                    if (is_array($arrData['save_callback'] ?? null)) {
                        foreach ($arrData['save_callback'] as $callback) {
                            if (is_array($callback)) {
                                $this->import($callback[0]);
                                $objWidget->value = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
                            } elseif (is_callable($callback)) {
                                $objWidget->value = $callback($varValue, $this);
                            }
                        }
                    }
                }
                catch (\Exception $e)
                {
                    $this->noReload = true;
                    $objWidget->addError($e->getMessage());
                }

                if ($objWidget->hasErrors()) {
                    $this->doNotSubmit        = true;
                    $this->arrInvalidFields[] = $strName;
                } elseif ($arrData['inputType'] == 'tag' && in_array('tags_plus', \ModuleLoader::getActive())) {
                    $varValue = deserialize($objWidget->value);

                    if (!is_array($varValue)) {
                        $varValue = [$varValue];
                    }

                    if ($this->intId) {
                        \HeimrichHannot\TagsPlus\TagsPlus::saveTags($this->strTable, $this->intId, array_map('urldecode', $varValue));
                    }
                } elseif ($objWidget->submitInput()) {
                    // save non escaped to database
                    if (is_array($objWidget->value)) {
                        $this->objActiveRecord->{$strName} = array_map(
                            function ($varVal) use ($arrData) {
                                $varVal = FormSubmission::prepareSpecialValueForSave(
                                    $varVal,
                                    $arrData,
                                    $this->strTable,
                                    $this->intId
                                );

                                $varVal = FormHelper::htmlEntityDecode($varVal);

                                return $varVal;
                            },
                            $objWidget->value
                        );
                    } else {
                        $this->objActiveRecord->{$strName} = html_entity_decode(
                            FormSubmission::prepareSpecialValueForSave(
                                $objWidget->value,
                                $arrData,
                                $this->strTable,
                                $this->intId
                            ),
                            ENT_QUOTES,
                            \Config::get('characterSet')
                        );
                    }
                } elseif ($objWidget instanceof \uploadable && isset($_SESSION['FILES'][$strName])
                          && \Validator::isUuid($_SESSION['FILES'][$strName]['uuid'])) {
                    $this->objActiveRecord->{$strName} = $_SESSION['FILES'][$strName]['uuid'];
                }
            }
        }

        return $objWidget;
    }

    protected function updateWidget(&$arrWidget, $arrData, $arrSkipFields = [])
    {

    }

    protected function stripInsertTags($varValue, $arrResult = [])
    {
        if (!is_array($varValue)) {
            return strip_insert_tags($varValue);
        }

        foreach ($varValue as $k => $v) {
            if (is_array($v)) {
                $arrResult = $this->stripInsertTags($v, $arrResult);
                continue;
            }

            $arrResult[$k] = strip_insert_tags($v);
        }

        return $arrResult;
    }

    protected function getDefaultFieldValue($strName, $arrData)
    {
        if ($this->formHybridAddGetParameter && Request::hasGet($strName) && $this->setValueFromGetParameter($strName)) {
            return Request::getGet($strName);
        }

        $varValue = null;

        // priority 2 -> set value from model entity ($this->setDefaults() triggered before)
        if (isset($this->objActiveRecord->{$strName})) {
            $varValue = FormSubmission::prepareSpecialValueForSave(
                $this->objActiveRecord->{$strName},
                $arrData,
                $this->strTable,
                $this->intId
            );
        }

        // priority 1 -> load_callback
        if (is_array($this->dca['fields'][$strName]['load_callback'] ?? null)) {
            foreach ($this->dca['fields'][$strName]['load_callback'] as $callback) {

                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
                } elseif (is_callable($callback)) {
                    $callback($varValue, $this);
                }

            }
        }

        return $varValue;
    }

    public function generateStart()
    {
        $this->Template->formName = $this->strFormName;
        $this->Template->formId   = $this->strFormId;
        $this->Template->method   = $this->strMethod;
        $this->Template->action   = $this->updateAction($this->strAction);

        $this->Template->enctype                       = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $this->Template->skipScrollingToSuccessMessage = $this->skipScrollingToSuccessMessage;
        $this->Template->novalidate                    = $this->novalidate ? ' novalidate' : '';

        $this->Template->class     = $this->generateContainerClass();
        $this->Template->formClass = (strlen($this->strFormClass) ? $this->strFormClass : '');

        if ($this->async) {
            $this->arrAttributes['data-async'] = true;
        }

        if ($this->closeModalAfterSubmit && !$this->doNotSubmit) {
            $this->arrAttributes['data-close-modal-on-submit'] = true;
        }

        if (!$this->enableAutoComplete) {
            $this->arrAttributes['autocomplete'] = 'off';
        }

        if (is_array($this->arrAttributes)) {
            $arrAttributes              = $this->arrAttributes;
            $this->Template->attributes = implode(
                ' ',
                array_map(
                    function ($strValue) use ($arrAttributes) {
                        return $strValue.'="'.$arrAttributes[$strValue].'"';
                    },
                    array_keys($this->arrAttributes)
                )
            );
        }

        $this->Template->cssID = ' id="'.$this->strFormName.'"';
    }

    protected function updateAction($strAction)
    {
        return $strAction;
    }

    protected function generateSubpalette($ajaxId)
    {
        $strSubTemplate = 'formhybrid_default_sub';

        if ($this->viewMode == FORMHYBRID_VIEW_MODE_READONLY) {
            $strSubTemplate = 'formhybridreadonly_default_sub';
        }

        if ($this->useCustomSubTemplates) {
            $strSubTemplate = $this->strTemplate.'_'.$ajaxId;
        }

        $strName                  = str_replace('sub_', '', $ajaxId);
        $objTemplate              = new \FrontendTemplate($strSubTemplate);
        $objTemplate->ajaxId      = $ajaxId;
        $objTemplate->fields      = $this->arrSubFields[$strName];
        $objTemplate->isSubmitted = $this->isSubmitted;

        return $objTemplate;
    }

    protected function runCallbacks()
    {
        foreach ($this->arrFields as $strName => $objWidget) {
            $arrData = $this->dca['fields'][$strName] ?? [];

            $varValue = $this->objActiveRecord->{$strName};

            // Set the correct empty value (see #6284, #6373)
            if ($varValue === '') {
                $varValue = \Widget::getEmptyValueByFieldType($arrData['sql'] ?? '');
            }

            // Save the value if there was no error
            if (($varValue != '' || !($arrData['eval']['doNotSaveEmpty'] ?? false))
                && ($this->objActiveRecord->{$strName} !== $varValue
                    || ($arrData['eval']['alwaysSave'] ?? false))) {
                $this->objActiveRecord->{$strName} = $varValue;
            }
        }
    }

    protected function createVersion()
    {
        if (!$this->hasDatabaseTable() || $this->hasNoEntity()) {
            return;
        }

        if (($objVersion = Version::setFromModel($this->objActiveRecord)) !== null) {
            $objVersion = $this->modifyVersion($objVersion);
            @Version::createVersion($objVersion, $this->objActiveRecord);
        }
    }

    protected function modifyVersion($objVersion)
    {
        return $objVersion;
    }

    protected function getFields()
    {
        $arrEditable = [];

        foreach ($this->arrEditable as $strField) {
            // check if field really exists
            if (!$this->dca['fields'][$strField]) {
                continue;
            }

            $arrEditable[] = $strField;
        }

        $this->arrEditable = $arrEditable;

        return !empty($this->arrEditable);
    }

    /**
     * Set Model defaults from dca field
     *
     * @param array $arrDca The DCA Array
     */
    protected function setDefaults($arrDca = [])
    {
        if ($this->hasDatabaseTable()) {
            $arrFields = \Database::getInstance()->listFields($this->strTable);
        } else {
            $arrFields = $arrDca['fields'];
        }

        foreach ($arrFields as $strName => $arrField) {

            // if database field
            if (isset($arrField['name'])) {
                $strName = $arrField['name'];
            }

            // set from default field value
            if (($varDefault = $arrDca['fields'][$strName]['default'] ?? null) !== null) {
                $this->arrDefaults[$strName] = $varDefault;
            }

            if ($this->addDefaultValues && ($varDefault = $this->arrDefaultValues[$strName] ?? null) !== null) {
                $this->arrDefaults[$strName] = $varDefault['value'];
            }
        }

        // add more fields, for example from other palettes or fields that have no palette or no sql
        if (is_array($this->arrDefaultValues)) {
            foreach ($this->arrDefaultValues as $strField => $varDefault) {
                if (!isset($arrDca['fields'][$strField]) || empty($arrDca['fields'][$strField])) {
                    continue;
                }

                $arrData = $arrDca['fields'][$strField];

                if (!in_array($strField, $this->arrEditable) && isset($arrData['inputType'])) {

                    if (strlen($varDefault['label']) > 0) {
                        $arrDca['fields'][$strField]['label'][0] = $varDefault['label'];
                    }

                    switch ($arrData['inputType']) {
                        case 'tag':
                            if (!in_array('tags', \ModuleLoader::getActive())) {
                                break;
                            }

                            if ($varDefault['value'] != '') {
                                $arrDca['config']['onsubmit_callback'][] = ['HeimrichHannot\FormHybrid\TagsHelper', 'saveTagsFromDefaults'];
                            }

                            break;
                        default:
                            $this->arrDefaults[$strField] = $varDefault['value'];
                    }
                }
            }
        }

        // set active record from defaults
        if (is_array($this->arrDefaults)) {
            foreach ($this->arrDefaults as $strName => $varValue) {
                $this->objActiveRecord->{$strName} = FormHelper::replaceInsertTags($varValue, false);
            }
        }
    }

    public function getDefaults()
    {
        return is_array($this->arrDefaults) ? $this->arrDefaults : [];
    }

    /**
     * Set the submission from request, required to check values before widget validation
     */
    protected function setSubmission()
    {
        foreach ($this->dca['fields'] as $strName => $arrField) {
            $arrData = $this->dca['fields'][$strName];

            // unset options_callback, as long as we have no valid backend user
            unset($arrData['options_callback'], $arrData['options_callback']);

            $arrAttribues = \Widget::getAttributesFromDca(
                $arrData,
                $strName,
                $this->objActiveRecord->{$strName},
                $strName,
                $this->strTable,
                $this
            );

            switch ($this->strMethod) {
                case FORMHYBRID_METHOD_GET:
                    $this->arrSubmission[$strName] = Request::getGet($strName);
                    break;
                case FORMHYBRID_METHOD_POST:

                    if ($arrAttribues['preserveTags'] ?? false) {
                        $this->arrSubmission[$strName] = Request::getPostRaw($strName);
                    } else {
                        if ($arrAttribues['allowHtml'] ?? false) {
                            $this->arrSubmission[$strName] = Request::getPostHtml($strName, $arrAttribues['decodeEntities'] ?? false);
                        } else {
                            $this->arrSubmission[$strName] = Request::getPost($strName, $arrAttribues['decodeEntities'] ?? false);
                        }
                    }
                    break;
            }
        }
    }

    protected function generateExportSubmitField()
    {
        $arrData = [
            'inputType' => 'submit',
            'label'     => &$GLOBALS['TL_LANG']['MSC']['formhybrid']['export'],
        ];

        $this->arrFields[FORMHYBRID_NAME_EXPORT] = $this->generateField(FORMHYBRID_NAME_EXPORT, $arrData);
    }

    protected function generateSubmitField()
    {
        $strLabel = &$GLOBALS['TL_LANG']['MSC']['formhybrid']['submitLabels']['default'];
        $strClass = 'btn btn-primary btn-lg';

        if ($this->strSubmit != '' && isset($this->arrFields[$this->strSubmit])) {
            return false;
        }

        if ($this->customSubmit) {
            if ($this->submitLabel != '') {
                $strLabel = $GLOBALS['TL_LANG']['MSC']['formhybrid']['submitLabels'][$this->submitLabel];
            }

            $strClass = $this->submitClass;
        }

        $arrData = [
            'inputType' => 'submit',
            'label'     => is_array($strLabel) ? $strLabel : [$strLabel],
            'eval'      => ['class' => $strClass],
        ];

        $this->arrFields[FORMHYBRID_NAME_SUBMIT] = $this->generateField(FORMHYBRID_NAME_SUBMIT, $arrData);
    }

    protected function generateFormIdentifierField()
    {
        $arrData = [
            'inputType' => 'hidden',
            'value'     => $this->getFormId(),
        ];

        $objWidget                                          = $this->generateField(FORMHYBRID_NAME_FORM_SUBMIT, $arrData);
        $objWidget->value                                   = $this->getFormId();
        $this->arrHiddenFields[FORMHYBRID_NAME_FORM_SUBMIT] = $objWidget;
    }

    protected function generateHiddenFields()
    {
        if (!is_array($this->arrHiddenFields)) {
            return '';
        }

        $strBuffer = '';

        if ($this->formHybridTransformGetParamsToHiddenFields) {
            foreach ($_GET as $strField => $varValue) {
                if ($strField === 'FORM_SUBMIT' && $this->strMethod === FORMHYBRID_METHOD_POST) {
                    continue;
                }

                $arrData = [
                    'inputType' => 'hidden',
                    'value'     => $varValue,
                ];

                $objWidget                        = $this->generateField($strField, $arrData);
                $objWidget->value                 = $varValue;
                $this->arrHiddenFields[$strField] = $objWidget;
            }
        }

        foreach ($this->arrHiddenFields as $strName => $objWidget) {
            $strBuffer .= $objWidget->parse();
        }

        return $strBuffer;
    }

    protected function loadDC()
    {
        if (!isset($GLOBALS['TL_DCA'][$this->strTable])) {
            return false;
        }

        // Call onload_callback, but only if 3rd callback parameter is set to true, otherwise contao backend related callbacks
        // where a BackendUser is required might get called
        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] as $callback) {
                if (!is_array($callback) || !isset($callback[2]) || $callback[2] !== true) {
                    continue;
                }

                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this, true);
                } elseif (is_callable($callback)) {
                    $callback($this, true);
                }
            }
        }

        $this->dca = &$GLOBALS['TL_DCA'][$this->strTable];

        $this->modifyDC($this->dca);

        return true;
    }

    protected function save($varValue = '')
    {
        if (!$this->hasDatabaseTable() || $this->hasNoEntity()) {
            return;
        }

        if ($this->arrOriginalRow === null) {
            $this->arrOriginalRow = $this->objActiveRecord->originalRow();
        }

        if ($this->saveToBlob) {
            $this->saveToBlob();
        } else {
            $this->objActiveRecord->save();
        }

        $this->intId = $this->objActiveRecord->id;

        // update form id and hash with entity id
        $this->strFormId              = $this->getFormId();
        $this->getConfig()->strFormId = $this->getFormId();
        $this->strAction = $this->getConfig()->strAction;
    }

    protected function generateContainerClass()
    {
        $arrClasses = explode(' ', $this->strClass);

        $arrClasses[] = $this->strFormName;

        if ($this->viewMode == FORMHYBRID_VIEW_MODE_READONLY) {
            $arrClasses[] = 'formhybrid-readonly';
        }

        if ($this->viewMode == FORMHYBRID_VIEW_MODE_DEFAULT) {
            $arrClasses[] = 'formhybrid formhybrid-edit';
        }

        if ($this->isFilterForm) {
            $arrClasses[] = 'filter-form';
        }

        if ($this->isSubmitted) {
            $arrClasses[] = 'submitted';
        }

        if ($this->intId) {
            $arrClasses[] = 'has-model';
        }

        if ($this->skipScrollingToSuccessMessage) {
            $arrClasses[] = 'noscroll';
        }

        $arrClasses = array_filter($arrClasses);

        return implode(' ', $arrClasses);
    }

    protected function resetForm($blnForce = false)
    {
        if (($this->async && $this->isRelatedAjaxRequest()) || $blnForce) {
            // on reset, reset submission id within session
            FormSession::freeSubmissionId($this->getFormId(false));

            $this->isSubmitted      = false;
            $this->intId            = null;
            $this->arrFields        = [];
            $this->arrSubFields     = [];
            $this->arrSubmission    = [];
            $this->arrHiddenFields  = [];
            $this->arrInvalidFields = [];
            $this->setSkipValidation(false);
            $this->setDoNotSubmit(false);

            $blnCreated = $this->create();
            $this->loadDC();
            $this->setPermanentFields();
            $this->initialize($blnCreated);
            $this->generateFields();
        }
    }

    protected function exportAfterSubmission()
    {
        if (class_exists('HeimrichHannot\FieldpaletteBundle\HeimrichHannotContaoFieldpaletteBundle')) {
            $fieldpaletteModel = new \HeimrichHannot\FieldpaletteBundle\Model\FieldPaletteModel();
            $objExportConfigs = $fieldpaletteModel->findPublishedByPidAndTableAndField($this->objModule->id, 'tl_module', 'formHybridExportConfigs');
        } else {
            $fieldpaletteModel = 'HeimrichHannot\FieldPalette\FieldPaletteModel';
            $objExportConfigs = $fieldpaletteModel::findPublishedByPidAndTableAndField($this->objModule->id, 'tl_module', 'formHybridExportConfigs');
        }

        if ($objExportConfigs !== null) {
            while ($objExportConfigs->next()) {
                if (class_exists('HeimrichHannot\ContaoExporterBundle\HeimrichHannotContaoExporterBundle')) {
                    $exporterClass = 'HeimrichHannot\ContaoExporterBundle\Model\ExporterModel';
                } else {
                    $exporterClass = 'HeimrichHannot\Exporter\ExporterModel';
                }

                $objConfig = $exporterClass::findByPk($objExportConfigs->formhybrid_formHybridExportConfigs_config);

                if ($objConfig !== null) {
                    $objConfig->type        = 'item';
                    $objConfig->linkedTable = $this->strTable;

                    // prepare fields for exporter
                    $arrExportFields = [];

                    foreach ($this->arrFields as $objWidget) {
                        $arrData = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$objWidget->name];

                        if ($objWidget->type == 'submit') {
                            continue;
                        }

                        $arrExportFields[$objWidget->name] = [
                            'raw'       => $this->objActiveRecord->{$objWidget->name},
                            'inputType' => $arrData['inputType'],
                            'formatted' => FormSubmission::prepareSpecialValueForPrint(
                                $this->objActiveRecord->{$objWidget->name},
                                $arrData,
                                $this->strTable,
                                $this
                            ),
                        ];

                        if ($arrData['inputType'] != 'explanation') {
                            $arrExportFields[$objWidget->name]['label'] = $this->dca['fields'][$objWidget->name]['label'][0] ?: $objWidget->name;
                        }

                        if ($objWidget->subName) {
                            foreach ($this->arrSubFields[$objWidget->subName] as $objSubWidget) {
                                $arrData = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$objSubWidget->name];

                                $arrExportFields[$objSubWidget->name] = [
                                    'raw'       => $this->objActiveRecord->{$objSubWidget->name},
                                    'inputType' => $arrData['inputType'],
                                    'formatted' => FormSubmission::prepareSpecialValueForPrint(
                                        $this->objActiveRecord->{$objSubWidget->name},
                                        $arrData,
                                        $this->strTable,
                                        $this
                                    ),
                                ];

                                if ($arrData['inputType'] != 'explanation') {
                                    $arrExportFields[$objSubWidget->name]['label'] = $this->dca['fields'][$objSubWidget->name]['label'][0] ?: $objSubWidget->name;
                                }
                            }
                        }
                    }

                    $objExporter = ModuleExporter::export($objConfig, $this->objActiveRecord, $arrExportFields);

                    if ($objExportConfigs->formhybrid_formHybridExportConfigs_entityField) {
                        $filePath = $objExporter->getFileDir().'/'.$objExporter->getFilename();

                        $file = new File($filePath);
                        if (!$objFile = $file->getModel()) {
                            if (!Dbafs::shouldBeSynchronized($filePath)) {
                                Dbafs::addResource($filePath);
                            }
                            Dbafs::syncFiles();
                            $objFile = $file->getModel();
                        }

                        $this->objActiveRecord->{$objExportConfigs->formhybrid_formHybridExportConfigs_entityField} = $objFile->uuid;
                        $this->objActiveRecord->save();
                    }
                }
            }
        }
    }

    protected function addPrivacyProtocolEntry()
    {
        if (!in_array('privacy', \ModuleLoader::getActive()) && !class_exists('\HeimrichHannot\PrivacyBundle\HeimrichHannotPrivacyBundle') ||
            !$this->formHybridAddPrivacyProtocolEntry) {
            return;
        }

        $protocolManager = new \HeimrichHannot\Privacy\Manager\ProtocolManager();
        $protocolUtil    = new \HeimrichHannot\Privacy\Util\ProtocolUtil();

        $data                = $protocolUtil->getMappedPrivacyProtocolFieldValues($this->objActiveRecord->row(), deserialize($this->objModule->formHybridPrivacyProtocolFieldMapping, true));
        $data['description'] = $this->objModule->formHybridPrivacyProtocolDescription;
        $data['table']       = $this->objModule->formHybridDataContainer;

        $protocolManager->addEntryFromModule(
            $this->objModule->formHybridPrivacyProtocolEntryType,
            $this->objModule->formHybridPrivacyProtocolArchive,
            $data,
            $this->objModule,
            'heimrichhannot/contao-formhybrid'
        );
    }

    protected function redirectAfterSubmission()
    {
        global $objPage;

        $blnRedirect = false;
        $strUrl      = \Controller::generateFrontendUrl($objPage->row());

        if (($objTarget = \PageModel::findByPk($this->jumpTo)) !== null) {
            $blnRedirect = true;
            $strUrl      = \Controller::generateFrontendUrl($objTarget->row(), null, null, true);
        }

        $arrPreserveParams = trimsplit(',', $this->jumpToPreserveParams);

        foreach ($arrPreserveParams as $strParam) {
            $varValue = Request::getGet($strParam);

            if ($varValue === null) {
                continue;
            }

            switch ($strParam) {
                case 'token':
                    if ($this->deactivateTokens) {
                        break;
                    }
                    $strUrl = Url::addQueryString($strParam.'='.\RequestToken::get(), $strUrl);
                    break;
                default:
                    $strUrl = Url::addQueryString($strParam.'='.$varValue, $strUrl);
            }
        }

        if ($blnRedirect) {
            \HeimrichHannot\StatusMessages\StatusMessage::reset($this->objModule->id);
        }

        if ($this->async) {
            if ($blnRedirect) {
                $objResponse = new ResponseRedirect();
                $objResponse->setUrl($strUrl);
                $objResponse->output();
            }

            return;
        }

        if (!$blnRedirect || $this->isFilterForm) {
            if ($this->getReset()) {
                $this->resetForm(true);
            }

            return;
        }

        \Controller::redirect($strUrl);
    }

    protected function setPermanentFields()
    {
        // cleanup arrPermanentFields if required
        if (!$this->addPermanentFields || !is_array($this->arrPermanentFields)) {
            $this->arrPermanentFields = [];
        }

        foreach ($this->arrEditable as $strName) {
            $arrData = $this->dca['fields'][$strName];
            // treat field without any inputType as hidden
            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']] ?: 'FormHidden';

            $reflection = new \ReflectionClass($strClass);

            foreach (static::$arrPermanentFieldClasses as $strIndependendClass) {
                if (!($reflection->getName() == $strIndependendClass || $reflection->isSubclassOf($strIndependendClass))) {
                    continue 2;
                }

                $this->arrPermanentFields = array_merge($this->arrPermanentFields, [$strName]);
            }
        }
    }

    /**
     * Return the name of the current palette
     *
     * @return string
     */
    public function getPalette()
    {
        return $this->strPalette;
    }

    public function modifyDC(&$arrDca = null)
    {
    }

    /**
     * @param string $strName  The name of the field
     * @param array $arrData  Field DCA config
     * @param bool  $blnForce Force field addin, regardless of existence in active palette
     */
    public function addEditableField($strName, array $arrData, $blnForce = false)
    {
        $this->dca['fields'][$strName] = $arrData;
        $this->arrEditable[]           = $strName;

        if ($blnForce) {
            $this->addPermanentFields   = true;
            $this->arrPermanentFields[] = $strName;
        }
    }

    public function getDca()
    {
        return $this->dca;
    }

    protected function processForm()
    {
    }

    protected function isValidAjaxRequest()
    {
        return \Environment::get('isAjaxRequest') && Request::getPost('scope') == FORMHYBRID_ACTION_SCOPE;
    }

    /**
     * @param                $objItem
     * @param \DataContainer $objDc
     */
    public function onFirstSubmitCallback($objItem, \DataContainer $objDc)
    {
    }

    public function onUpdateCallback($objItem, \DataContainer $objDc, $blnJustCreated, $arrOriginalRow = null)
    {
    }

    public function getTable()
    {
        return $this->strTable;
    }

    /**
     * @internal Use FormHelper::getFormId() for static calls
     *
     * @return string
     */
    public function getFormId($blnAddEntityId = true)
    {
        if ($this->useCustomFormId) {
            return $this->customFormId;
        }

        return FormHelper::getFormId($this->strTable, $this->objModule->id, $this->intId, $blnAddEntityId, ($this->useCustomFormIdSuffix ? $this->customFormIdSuffix : ''));
    }

    public function getFormName()
    {
        return 'formhybrid_'.str_replace('tl_', '', $this->strTable);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->intId;
    }

    /**
     * @return boolean
     */
    public function isSkipValidation()
    {
        return $this->skipValidation;
    }

    /**
     * @param boolean $skipValidation
     */
    public function setSkipValidation($skipValidation)
    {
        $this->skipValidation = $skipValidation;
    }

    public function isSubmitted()
    {
        return $this->isSubmitted;
    }

    /**
     * @deprecated use $this->isDoNotSubmit()
     * @return bool
     */
    public function doNotSubmit()
    {
        return $this->isDoNotSubmit();
    }

    /**
     * @return boolean
     */
    public function isDoNotSubmit()
    {
        return $this->doNotSubmit;
    }

    /**
     * @param boolean $doNotSubmit
     */
    public function setDoNotSubmit($doNotSubmit)
    {
        $this->doNotSubmit = $doNotSubmit;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return boolean
     */
    public function isRelatedAjaxRequest()
    {
        return $this->relatedAjaxRequest;
    }

    /**
     * @param boolean $relatedAjaxRequest
     */
    public function setRelatedAjaxRequest($isRelatedAjaxRequest)
    {
        $this->relatedAjaxRequest = (boolean)$isRelatedAjaxRequest;
    }

    /**
     * @return array
     */
    public function getActualFields()
    {
        return $this->arrFields;
    }

    /**
     * @return array
     */
    public function getActualHiddenFields()
    {
        return $this->arrHiddenFields;
    }

    /**
     * @return array
     */
    public function getActualSubFields()
    {
        return $this->arrSubFields;
    }


    /**
     * @return array
     */
    public function getEditableFields()
    {
        return $this->arrEditable;
    }

    /**
     * @param array $arrEditable
     */
    public function setEditableFields(array $arrEditable)
    {
        $this->arrEditable = $arrEditable;
    }

    /**
     * @return boolean
     */
    public function hasDatabaseTable()
    {
        return $this->hasDatabaseTable;
    }

    /**
     * Clear inputs, set default values
     *
     * @return bool
     * @deprecated set $this->resetForm() to true/fals on onsubmit_callback
     */
    public function clearInputs()
    {
        $this->resetAfterSubmission = true;
    }

    public function setReset($varValue)
    {
        $this->resetAfterSubmission = (bool)$varValue;
    }

    public function getReset()
    {
        return $this->resetAfterSubmission;
    }

    /**
     * @return boolean
     */
    public function isInvalid()
    {
        return $this->invalid;
    }


    /**
     * Return an object property
     *
     * @param string
     *
     * @return mixed
     */
    public function __get($strKey)
    {
        // legacy: support 'formHybrid' prefix

        if (FormConfiguration::isLegacyKey($strKey)) {
            $strKey = FormConfiguration::getKey($strKey);
        }

        // parent getter must be dominant, otherwise intId will taken from arrData
        // tl_calendar_events::adjustTime callback make usage of $dc->id instead of $dc->activeRecord->id
        if (($strParent = parent::__get($strKey)) != '') {
            return $strParent;
        }

        switch ($strKey) {
            // Fallback for old formHybrid modules
            case 'objModel' :
                return $this->objModule;
                break;
            default:
                if (isset($this->arrData[$strKey])) {
                    return $this->arrData[$strKey];
                }
        }
    }

    protected function setData(array $arrData = [])
    {
        foreach ($arrData as $key => $varValue) {
            $this->{$key} = $varValue;
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
        // legacy: support 'formHybrid' prefix
        if (FormConfiguration::isLegacyKey($strKey)) {
            $strKey = FormConfiguration::getKey($strKey);
        }

        if (property_exists($this, $strKey)) {
            $this->{$strKey} = $varValue;

            return;
        }

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
        // legacy: support 'formHybrid' prefix
        if (FormConfiguration::isLegacyKey($strKey)) {
            $strKey = FormConfiguration::getKey($strKey);
        }

        return isset($this->arrData[$strKey]);
    }


    public function runOnValidationError($arrInvalidFields)
    {
    }

    /**
     * @return boolean
     */
    public function hasNoEntity()
    {
        return $this->noEntity;
    }

    /**
     * @param boolean $noEntity
     */
    public function setNoEntity($noEntity)
    {
        $this->noEntity = $noEntity;
    }


    /**
     * @return boolean
     */
    public function isSilentMode()
    {
        return $this->blnSilentMode;
    }

    /**
     * @param boolean $blnSilentMode
     */
    public function setSilentMode($blnSilentMode)
    {
        $this->blnSilentMode = $blnSilentMode;
    }

    /**
     * @return FormConfiguration|null
     */
    public function getConfig()
    {
        return $this->objConfig;
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        return $this->arrConfig;
    }

    /**
     * @return boolean
     */
    public function isForceCreate()
    {
        return $this->forceCreate;
    }

    /**
     * @param boolean $forceCreate
     */
    public function setForceCreate($forceCreate)
    {
        $this->forceCreate = $forceCreate;
    }

    /**
     * @param string $parameter
     * @return bool
     */
    protected function setValueFromGetParameter($parameter)
    {
	$setValue = false;
	$get      = deserialize($this->formHybridGetParameter, true);

	if (in_array($parameter, $get)) {
	  $setValue = true;
	}

	return $setValue;
    }

    /**
     * Return a list of invalid fields. Is filled when fields are generated.
     *
     * @return array
     */
    public function getInvalidFields()
    {
        return $this->arrInvalidFields;
    }


}

