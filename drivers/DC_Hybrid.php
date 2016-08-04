<?php
namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Ajax\Ajax;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Ajax\Response\ResponseRedirect;
use HeimrichHannot\Haste\Security\CodeGenerator;
use HeimrichHannot\Haste\Util\FormSubmission;
use HeimrichHannot\Haste\Util\StringUtil;
use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\StatusMessages\StatusMessage;

class DC_Hybrid extends \DataContainer
{
	protected $arrData = array();
	
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
	
	protected $arrDefaults = array();
	
	protected $arrFields = array();
	
	protected $objConfig = null;
	
	protected $arrConfig = array();
	
	protected $arrHiddenFields = array();
	
	protected $arrSubFields = array();
	
	protected $arrEditable = array();
	
	protected $arrRequired = array();
	
	protected $arrInvalidFields = array();
	
	protected $overwriteRequired = false;
	
	protected $arrSubmission = array();
	
	protected $strSubmit = '';
	
	protected $async = false;
	
	protected $arrAttributes = array();
	
	protected $useCustomSubTemplates = false;
	
	protected $saveToBlob = false;
	
	public $objModule; // public, required by callbacks
	
	protected $isFilterForm = false;
	
	protected $skipValidation = false;
	
	protected $mode = FORMHYBRID_MODE_CREATE;
	
	private $useModelData = false;
	
	protected $blnSilentMode = false;
	
	protected $objAjax;
	
	protected $arrOriginalRow = array();
	
	protected $relatedAjaxRequest = false;
	
	protected $activePalette = 'default';
	
	private $resetAfterSubmission = true;
	
	/**
	 * Set true, and skip ajax form request handling.
	 * Might be helpful if you want inject Form within your own module and handle ajax by own methods.
	 *
	 * @var bool
	 */
	protected $skipFormAjax = false;
	
	public function __construct($strTable = '', $varConfig = null, $intId = 0)
	{
		$this->objConfig = new FormConfiguration($varConfig);
		$this->arrConfig = $this->objConfig->getData();
		$this->setData($this->objConfig->getData());
		$this->objModule = $this->objConfig->getModule();
		
		// backwards compatibility for direct DC_Hybrid calls
		if ($strTable) {
			$this->strTable = $strTable;
		}
		
		$this->intId       = $intId;
		$this->strFormId   = $this->getFormId();
		$this->strFormName = $this->getFormName();
		
		if ($this->addEditableRequired) {
			$this->overwriteRequired = true;
		}
		
		$this->strInputMethod = $strInputMethod = strtolower($this->strMethod);
		
		if (\Input::$strInputMethod(FORMHYBRID_NAME_SKIP_VALIDATION)) {
			$this->setSkipValidation(\Input::$strInputMethod(FORMHYBRID_NAME_SKIP_VALIDATION));
		}
		
		// GET is checked for each field separately
		$this->isSubmitted  = (\Input::$strInputMethod('FORM_SUBMIT') == $this->getFormId());
		$this->useModelData = \Database::getInstance()->tableExists($this->strTable);
		
		$this->import('Database');
		$this->objModule = $this->objConfig->getModule();
		$this->intId     = $this->intId ?: $intId;
		$this->loadDC();
		
		$this->initialize();
		
		Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'toggleSubpalette', new FormAjax($this));
		Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'reload', new FormAjax($this));
		
	}
	
	protected function initialize()
	{
		// load the model
		// don't load any class if the form's a filter form -> submission should be used instead
		if (!$this->isFilterForm) {
			$strModelClass = \Model::getClassFromTable($this->strTable);
		}
		
		if ($this->intId && is_numeric($this->intId)) {
			if (($objModel = $strModelClass::findByPk($this->intId)) !== null) {
				$this->objActiveRecord = $objModel;
				$this->setMode(FORMHYBRID_MODE_EDIT);
				
				if ($this->saveToBlob) {
					$this->loadFromBlob(deserialize($objModel->formHybridBlob, true));
				}
				
				// redirect on specific field value
				static::doFieldDependentRedirect($this, $this->objActiveRecord);
			} else {
				$this->Template->invalid = true;
				StatusMessage::addError($GLOBALS['TL_LANG']['formhybrid']['messages']['error']['invalidId'], $this->objModule->id, 'alert alert-danger');
			}
		} // we require a module context for entity creation
		else {
			if ($this->objModule !== null) {
				$this->objActiveRecord = $this->createSubmission($strModelClass);
				$this->setDefaults();
				$this->setSubmission();
				$this->save(); // initially try to save record, as ajax requests for example require entity model
			}
		}
		
		// register form id in session if we got a new one from save()
		if ($this->intId && is_numeric($this->intId)) {
			FormSession::addSubmissionId($this->getFormId(false), $this->getId());
		}
		
		if ($this->viewMode == FORMHYBRID_VIEW_MODE_READONLY) {
			$this->strTemplate = $this->readonlyTemplate;
			$this->setDoNotSubmit(true);
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
					|| $objPageJumpTo = $objPage
				) {
					$strRedirect = \Controller::generateFrontendUrl($objPageJumpTo->row());
					
					if ($this->fieldDependentRedirectKeepParams) {
						$arrParamsToKeep = explode(',', $this->fieldDependentRedirectKeepParams);
						if (!empty($arrParamsToKeep)) {
							foreach (Url::getUriParameters(Url::getUrl()) as $strParam => $strValue) {
								if (in_array($strParam, $arrParamsToKeep)) {
									$strRedirect = Url::addQueryString($strParam . '=' . $strValue, $strRedirect);
								}
							}
						}
					}
					
					if (!$this->deactivateTokens) {
						$strRedirect = Url::addQueryString('token=' . \RequestToken::get(), $strRedirect);
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
		
		if (!$this->getFields()) {
			return false;
		}
		
		$blnAjax = $this->generateFields($ajaxId);
		
		if ($blnAjax) {
			$objTemplate = $this->generateSubpalette($ajaxId);
			
			return \Controller::replaceInsertTags($objTemplate->parse(), false);
		}
		
		if ($this->isSubmitted && $this->isDoNotSubmit()) {
			$this->runOnValidationError($this->arrInvalidFields);
		}
		
		if ($this->isSubmitted && !$this->isDoNotSubmit()) {
			// run field callbacks, must be before save(), same as contao
			$this->runCallbacks();
			
			// save for save_callbacks
			$this->save();
			
			// process form
			$this->processForm();
			
			$this->Template->message = StatusMessage::generate($this->objModule->id);
			
			// reset form is default. disable by $this->setReset(false)
			// Exception: filter forms should never been reset after submit
			if ($this->getReset() && !$this->isFilterForm) {
				$this->reset();
			}
			
			
		}
		
		$this->generateStart();
		
		$this->Template->fields      = $this->arrFields;
		$this->Template->isSubmitted = $this->isSubmitted;
		$this->Template->submission  = $this->objActiveRecord;
		$this->Template->hidden      = $this->generateHiddenFields();
		
		$this->compile();
		
		$strBuffer = \Controller::replaceInsertTags($this->Template->parse(), false);
		
		Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'asyncFormSubmit', new FormAjax($this, $strBuffer));
		Ajax::runActiveAction(Form::FORMHYBRID_NAME, 'reload', new FormAjax($this, $strBuffer));
		
		return $strBuffer;
	}
	
	public function generateFields($ajaxId = null)
	{
		$arrFields    = $this->arrEditable;
		$arrSubFields = array();
		$blnAjax      = false;
		
		// subpalettes
		$arrSelectors = $this->dca['palettes']['__selector__'];
		
		if (is_array($arrSelectors)) {
			$toggleSubpalette = str_replace('sub_', '', $ajaxId);
			
			foreach ($arrSelectors as $strName) {
				list($blnActive, $strSubPalette, $arrFields, $arrActiveSubPaletteFields) = $this->retrieveSubpaletteWithState($strName, $arrFields);
				
				if (!isset($this->dca['subpalettes'][$strSubPalette])) {
					continue;
				}
				
				// if subpalette is requested, set state to active, clear parent fields and break
				if ($toggleSubpalette == $strName) {
					$blnAjax      = true;
					$arrSubFields = array($strName => $arrActiveSubPaletteFields);
					break; // this function can only return one subpalette at once
				}
				
				// active by default
				if ($blnActive) {
					// selector field is visible
					if (in_array($strName, $this->arrEditable)) {
						$arrFields              = array_diff($arrFields, $arrActiveSubPaletteFields);
						$arrSubFields[$strName] = $arrActiveSubPaletteFields;
					} // for example selector is triggered by default value
					else {
						// do nothing, fields should remain in $arrFields
					}
				} // inactive
				else {
					if ($this->addPermanentFields) {
						$arrActiveSubPaletteFields = array_diff($arrActiveSubPaletteFields, $this->arrPermanentFields);
					}
					
					$arrFields = array_diff($arrFields, $arrActiveSubPaletteFields);
				}
			}
			
			// check for active palette from typeselector
			foreach ($arrSelectors as $strSelector) {
				$varValue   = $this->getFieldValue($strSelector);
				$strPalette = $this->dca['palettes'][$varValue];
				$arrOptions = deserialize($this->dca['fields'][$strSelector]['options'], true); // TODO options_callback
			
				if ($varValue && isset($this->dca['palettes'][$varValue]) && $strPalette && in_array($varValue, $arrOptions)) {
					// no messages
					$this->blnSilentMode                                                 = $this->isSkipValidation();
					$this->dca['fields'][$strSelector]['eval']['skipValidationOnSubmit'] = true;
				
					// remove fields not existing in the current palette
					$arrFields = array_intersect($arrFields, FormHelper::getPaletteFields($this->strTable, $strPalette));
				
					// only one palette can be active at a time
					$this->activePalette = $varValue;
					break;
				}
			}
		}
		
		
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
				$objSubTemplate = $this->generateSubpalette('sub_' . $strParent);
				
				// parent field must exist
				if (!$this->arrFields[$strParent]) {
					$this->addField($strParent);
				}
				
				// append subfields to parent field
				if ($this->arrFields[$strParent]) {
					$this->arrFields[$strParent]->sub = \Controller::replaceInsertTags($objSubTemplate->parse(), false);
				}
			}
		}
		
		// add submit button
		if (!$blnAjax) {
			$this->generateSubmitField();
		}
		
		if (!$this->async) {
			$this->generateFormIdentifierField();
		}
		
		return $blnAjax;
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
		$inputMethod = strtolower($this->strMethod);
		
		$varValue = $this->getDefaultFieldValue($strName, $this->dca['fields'][$strName]);
		
		if ($this->isSubmitted && in_array($strName, $this->arrEditable) && ($requestValue = \Input::$inputMethod($strName)) !== null) {
			$varValue = $requestValue;
		}
		
		return $varValue;
	}
	
	/**
	 * Retrieve the subpalette by the field selector
	 *
	 * @param   String $strSelector
	 * @param    Array $arrFields
	 *
	 * @return array Return the state, subpalette name, the filtered fields array and autosubmit state
	 */
	protected function retrieveSubpaletteWithState($strSelector, array $arrFields)
	{
		$blnActive                 = null;
		$blnAutoSubmit             = false;
		$strSubpalette             = null;
		$arrActiveSubPaletteFields = array();
		
		$varValue = $this->getFieldValue($strSelector);
		
		// skip arrays, they cant be array keys
		if (is_array($varValue)) {
			return array($blnActive, $strSubpalette, $arrFields, $blnAutoSubmit);
		}
		
		// checkbox: addImage for example
		if ($this->dca['fields'][$strSelector]['inputType'] == 'checkbox' && !$this->dca['fields'][$strSelector]['eval']['multiple']) {
			if (strlen($this->dca['subpalettes'][$strSelector])) {
				$blnActive     = ($varValue == true);
				$strSubpalette = $strSelector;
				
				if ($blnActive) {
					$arrActiveSubPaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strSubpalette]);
				}
			}
		} // radio: source in tl_news (source_default, source_external)
		else {
			// type selector
			if (isset($this->dca['subpalettes'][$varValue])) {
				$blnAutoSubmit = true;
				$blnActive     = true;
				$strSubpalette = $varValue;
			} // concatenated type selector (e.g. source -> source_external)
			elseif (is_array($this->dca['subpalettes'])) {
				if (isset($this->dca['subpalettes'][$strSelector . '_' . $varValue])) {
					$blnActive                 = true;
					$strSubpalette             = $strSelector . '_' . $varValue;
					$arrActiveSubPaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strSubpalette]);
				}
				
				// filter out non selected type subpalette fields
				foreach (array_keys($this->dca['subpalettes']) as $strKey) {
					// skip current active type selector subpalette
					if ($strKey == $strSelector . '_' . $varValue) {
						continue;
					}
					
					// remove fields from same selector, but not active
					if (\HeimrichHannot\Haste\Util\StringUtil::startsWith($strKey, $strSelector . '_')) {
						// if no concatenated type selector has been selected, yet -> return the first in order
						// to create FormhybridAjaxRequest.toggleSubpalette calls in the following function
						if (!$strSubpalette) {
							$strSubpalette = $strKey;
						}
						
						$arrSiblingSubPaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strKey]);
						
						if (is_array($arrSiblingSubPaletteFields)) {
							foreach ($arrSiblingSubPaletteFields as $strField) {
								// leave active subpalette fields in arrFields
								if (in_array($strField, $arrActiveSubPaletteFields)) {
									continue;
								}
								
								if (!$this->addPermanentFields || !in_array($strField, $this->arrPermanentFields)) {
									$arrFields = array_diff($arrFields, array($strField));
								}
							}
						}
					}
				}
			}
		}
		
		return array($blnActive, $strSubpalette, $arrFields, $arrActiveSubPaletteFields, $blnAutoSubmit);
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
		if ($this->viewMode == FORMHYBRID_VIEW_MODE_READONLY) {
			$strClass = 'HeimrichHannot\FormHybrid\FormReadonlyField';
		}
		
		$strInputMethod = $this->strInputMethod;
		
		// Continue if the class is not defined
		if (!class_exists($strClass)) {
			return false;
		}
		
		$reflection = new \ReflectionClass($strClass);
		
		// skip ajax request independent fields like captcha
		if (($reflection->getName() == 'Contao\FormCaptcha' || $reflection->isSubclassOf('Contao\FormCaptcha')) && $this->isSubmitted && !$this->relatedAjaxRequest
			&& \Environment::get(
				'isAjaxRequest'
			)
		) {
			return false;
		}
		
		$arrWidgetErrors = array();
		
		// contains the load_callback!
		$varDefault = $this->getDefaultFieldValue($strName, $arrData);
		$varValue   = $varDefault;
		
		if ($this->isSubmitted && !$skipValidation) {
			$varValue = \Input::$strInputMethod($strName) !== null ? \Input::$strInputMethod($strName) : $varValue;
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
		
		// prevent name for GET and submit widget, otherwise url will have submit name in
		if ($this->strMethod == FORMHYBRID_METHOD_GET && $arrData['inputType'] == 'submit') {
			$strName = '';
		}
		
		$arrData['eval']['tagTable'] = $this->strTable;
		
		// always disable validation for filter form
		if ($this->isFilterForm) {
			$arrData['eval']['mandatory'] = false;
		}
		
		// to make captcha form related, add the form id without entity id
		if ($arrData['inputType'] == 'captcha') {
			$strName .= '_' . $this->getFormId(false);
		}
		
		$arrWidget = \Widget::getAttributesFromDca(
			$arrData,
			$strName,
			is_array($varValue) ? $varValue : \Controller::replaceInsertTags($varValue),
			$strName,
			$this->strTable,
			$this
		);
		
		$this->updateWidget($arrWidget, $arrData);
		
		list($blnActive, $strSubPalette, $arrFields, $arrActiveSubPaletteFields, $blnAutoSubmit) = $this->retrieveSubpaletteWithState($strName, array_keys($this->arrFields));
		
		// support submitOnChange as form submission
		if ($arrData['eval']['submitOnChange'] && isset($this->dca['subpalettes'][$strSubPalette])) {
			if ($blnAutoSubmit) {
				$arrWidget['onchange'] = $this->async ? 'FormhybridAjaxRequest.asyncSubmit(this.form);' : "this.form.submit();";
			} else {
				$strEvent = 'onclick';
				
				switch ($arrData['inputType']) {
					case 'select':
						$strEvent = 'onchange';
						break;
				}
				
				$arrWidget[$strEvent] = "FormhybridAjaxRequest.toggleSubpalette(this, 'sub_" . $strName . "', '"
										. $strName . "', '" . AjaxAction::generateUrl(Form::FORMHYBRID_NAME, 'toggleSubpalette') . "')";
				unset($arrWidget['submitOnChange']);
			}
		} // the field does trigger a form reload without validation
		else {
			if ($arrWidget['submitOnChange']) {
				$strEvent = null;
				
				if ($arrWidget['onchange']) {
					$strEvent = 'onchange';
				} else {
					if ($arrWidget['onclick']) {
						$strEvent = 'onclick';
					}
				}
				
				if ($strEvent !== null) {
					$arrWidget[$strEvent] = "FormhybridAjaxRequest.reload('" . $this->getFormId() . "', '" . AjaxAction::generateUrl(Form::FORMHYBRID_NAME, 'reload') . "')";
					
					unset($arrWidget['submitOnChange']);
				}
			}
		}
		
		$objWidget = new $strClass($arrWidget);
		
		if (isset($arrData['formHybridOptions'])) {
			$arrFormHybridOptions = $arrData['formHybridOptions'];
			
			$this->import($arrFormHybridOptions[0]);
			$objWidget->options = $this->$arrFormHybridOptions[0]->$arrFormHybridOptions[1]();
		}
		
		if ($objWidget instanceof \uploadable) {
			$this->hasUpload = true;
		}
		
		// always xss clean the user input (also if filter, non-model submission, ...) -> done another time
		// FrontendWidget::validateGetAndPost() in
		$objWidget->value = FormHelper::xssClean($objWidget->value, $arrData['eval']['allowHtml']);
		
		if ($this->isSubmitted && !($this->isSkipValidation() || $skipValidation)) {
			FrontendWidget::validateGetAndPost($objWidget, $this->strMethod, $this->getFormId(), $arrData);
			
			if (is_array($arrWidgetErrors)) {
				foreach ($arrWidgetErrors as $strError) {
					$objWidget->addError($strError);
				}
			}
			
			if ($objWidget->value && $this->strMethod == FORMHYBRID_METHOD_GET) {
				$objWidget->class = 'filtered';
			}
			
			// Make sure unique fields are unique
			if ($arrData['eval']['unique'] && $varValue != ''
				&& !\Database::getInstance()->isUniqueValue(
					$this->strTable,
					$strName,
					$varValue,
					$this->intId > 0 ? $this->intId : null
				)
			) {
				$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $strName));
			}
			
			if ($objWidget->hasErrors()) {
				$this->doNotSubmit        = true;
				$this->arrInvalidFields[] = $strName;
			} elseif ($arrData['inputType'] == 'tag' && in_array('tags_plus', \ModuleLoader::getActive())) {
				$varValue = deserialize($objWidget->value);
				
				if (!is_array($varValue)) {
					$varValue = array($varValue);
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
							
							if (is_array($varVal)) {
								foreach ($varVal as $key => $val) {
									$varVal[$key] = html_entity_decode($val);
								}
							} else {
								$varVal = html_entity_decode($varVal);
							}
							
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
						)
					);
				}
			} // support file uploads
			elseif ($objWidget instanceof \uploadable && $arrData['inputType'] == 'multifileupload') {
				$strMethod = strtolower($this->strMethod);
				if (\Input::$strMethod($strName)) {
					$arrValue = json_decode(\Input::$strMethod($strName));
					
					if (!empty($arrValue)) {
						$arrValue = array_map(
							function ($val) {
								return \String::uuidToBin($val);
							},
							$arrValue
						);
						
						$this->objActiveRecord->{$strName} = serialize($arrValue);
					} else {
						$this->objActiveRecord->{$strName} = serialize($arrValue);
					}
				}
				
				// delete the files scheduled for deletion
				$objWidget->deleteScheduledFiles(json_decode(\Input::$strMethod('deleted_' . $strName)));
			} elseif ($objWidget instanceof \uploadable && isset($_SESSION['FILES'][$strName])
					  && \Validator::isUuid($_SESSION['FILES'][$strName]['uuid'])
			) {
				$this->objActiveRecord->{$strName} = $_SESSION['FILES'][$strName]['uuid'];
			}
		}
		
		return $objWidget;
	}
	
	protected function updateWidget($arrWidget, $arrData)
	{
	}
	
	protected function stripInsertTags($varValue, $arrResult = array())
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
		if (is_array($this->dca['fields'][$strName]['load_callback'])) {
			foreach ($this->dca['fields'][$strName]['load_callback'] as $callback) {
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($varValue, $this);
			}
		}
		
		return $varValue;
	}
	
	public function generateStart()
	{
		$this->Template->formName                      = $this->strFormName;
		$this->Template->formId                        = $this->strFormId;
		$this->Template->method                        = $this->strMethod;
		$this->Template->action                        = $this->strAction;
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
		
		if (is_array($this->arrAttributes)) {
			$arrAttributes              = $this->arrAttributes;
			$this->Template->attributes = implode(
				' ',
				array_map(
					function ($strValue) use ($arrAttributes) {
						return $strValue . '="' . $arrAttributes[$strValue] . '"';
					},
					array_keys($this->arrAttributes)
				)
			);
		}
		
		$this->Template->cssID = ' id="' . $this->strFormName . '"';
	}
	
	protected function generateSubpalette($ajaxId)
	{
		$strSubTemplate = 'formhybrid_default_sub';
		
		if ($this->viewMode == FORMHYBRID_VIEW_MODE_READONLY) {
			$strSubTemplate = 'formhybridreadonly_default_sub';
		}
		
		if ($this->useCustomSubTemplates) {
			$strSubTemplate = $this->strTemplate . '_' . $ajaxId;
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
			$arrData = $this->dca['fields'][$strName];
			
			$varValue = $this->objActiveRecord->{$strName};
			
			// Trigger the save_callback
			if (is_array($arrData['save_callback'])) {
				foreach ($arrData['save_callback'] as $callback) {
					if (is_array($callback)) {
						$this->import($callback[0]);
						$varValue = $this->$callback[0]->$callback[1]($varValue, $this);
					} elseif (is_callable($callback)) {
						$varValue = $callback($varValue, $this);
					}
				}
			}
			
			// Set the correct empty value (see #6284, #6373)
			if ($varValue === '') {
				$varValue = \Widget::getEmptyValueByFieldType($arrData['sql']);
			}
			
			// Save the value if there was no error
			if (($varValue != '' || !$arrData['eval']['doNotSaveEmpty'])
				&& ($this->objActiveRecord->{$strName} !== $varValue
					|| $arrData['eval']['alwaysSave'])
			) {
				$this->objActiveRecord->{$strName} = $varValue;
			}
		}
	}
	
	protected function createVersion()
	{
		if ($this->isFilterForm || !$this->objActiveRecord instanceof \Contao\Model || !$GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning']) {
			return;
		}
		
		// Create the initial version (see #7816)
		$objVersion = new \Contao\Versions($this->strTable, $this->objActiveRecord->id);
		
		if (($objUser = \UserModel::findByUsername(FORMHYBRID_USER_EMAIL)) === null) {
			$objUser           = new \UserModel();
			$objUser->username = $objUser->email = FORMHYBRID_USER_EMAIL;
			$objUser->name     = FORMHYBRID_USER_NAME;
			// at least something must be in there
			$objUser->password  = CodeGenerator::generate();
			$objUser->disable   = true;
			$objUser->dateAdded = $objUser->tstamp = time();
			$objUser->save();
		}
		
		$objVersion->setUserId($objUser->id);
		$objVersion->setUsername($objUser->email);
		
		foreach ($GLOBALS['BE_MOD'] as $strGroup => $arrGroup) {
			foreach ($arrGroup as $strModule => $arrModule) {
				if (!isset($arrModule['tables']) || !is_array($arrModule['tables'])) {
					continue;
				}
				
				if (in_array($this->strTable, $arrModule['tables'])) {
					$objVersion->formhybrid_backend_url = sprintf(
						'contao/main.php?do=%s&table=%s&act=edit&id=%s&rt=%s',
						$strModule,
						$this->strTable,
						$this->objActiveRecord->id,
						\RequestToken::get()
					);
					break 2;
				}
			}
		}
		
		if (FE_USER_LOGGED_IN && ($objMember = \FrontendUser::getInstance()) !== null) {
			$objVersion->memberusername = $objMember->username;
			$objVersion->memberid       = $objMember->id;
		}
		
		$objVersion = $this->modifyVersion($objVersion);
		
		$objVersionCheck = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_version WHERE fromTable=? AND pid=?")
			->limit(1)->execute($this->strTable, $this->objActiveRecord->id);
		
		if ($objVersionCheck->count > 0) {
			$objVersion->create();
		} else {
			$objVersion->initialize();
		}
		
		// Call the onversion_callback
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'])) {
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback) {
				if (is_array($callback)) {
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($this->strTable, $this->intId, $this);
				} elseif (is_callable($callback)) {
					$callback($this->strTable, $this->intId, $this);
				}
			}
		}
		
		$this->log(
			'A new version of record "' . $this->strTable . '.id=' . $this->intId . '" has been created'
			. $this->getParentEntries(
				$this->strTable,
				$this->intId
			),
			__METHOD__,
			TL_GENERAL
		);
	}
	
	protected function modifyVersion($objVersion)
	{
		return $objVersion;
	}
	
	protected function getFields()
	{
		$arrEditable = array();
		
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
	 */
	protected function setDefaults()
	{
		if (\Database::getInstance()->tableExists($this->strTable)) {
			$arrFields = \Database::getInstance()->listFields($this->strTable);
		} else {
			$arrFields = $this->dca['fields'];
		}
		
		foreach ($arrFields as $strName => $arrField) {
			
			// if database field
			if (isset($arrField['name'])) {
				$strName = $arrField['name'];
			}
			
			// set from default field value
			if (($varDefault = $this->dca['fields'][$strName]['default']) !== null) {
				$this->arrDefaults[$strName] = $varDefault;
			}
			
			if ($this->addDefaultValues && ($varDefault = $this->arrDefaultValues[$strName]) !== null) {
				$this->arrDefaults[$strName] = $varDefault['value'];
			}
		}
		
		// add more fields, for example from other palettes or fields that have no palette or no sql
		if (is_array($this->arrDefaultValues)) {
			foreach ($this->arrDefaultValues as $strField => $varDefault) {
				$arrData = $this->dca['fields'][$strField];
				
				if (!in_array($strField, $this->arrEditable) && isset($arrData['inputType'])) {
					
					if (strlen($varDefault['label']) > 0) {
						$this->dca['fields'][$strField]['label'][0] = $varDefault['label'];
					}
					
					switch ($arrData['inputType']) {
						case 'tag':
							if (!in_array('tags', \ModuleLoader::getActive())) {
								break;
							}
							
							if ($varDefault['value'] != '') {
								$this->dca['config']['onsubmit_callback'][] = array('HeimrichHannot\FormHybrid\TagsHelper', 'saveTagsFromDefaults');
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
				// don't cache here!
				$this->objActiveRecord->{$strName} = FormHelper::replaceInsertTags($varValue, false);
			}
		}
	}
	
	public function getDefaults()
	{
		return is_array($this->arrDefaults) ? $this->arrDefaults : array();
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
					$this->arrSubmission[$strName] = FormHelper::getGet($strName);
					break;
				case FORMHYBRID_METHOD_POST:
					$this->arrSubmission[$strName] = FormHelper::getPost(
						$strName,
						$arrAttribues['decodeEntities'],
						$arrAttribues['allowHtml'],
						$arrAttribues['preserveTags']
					);
					break;
			}
		}
	}
	
	protected function generateSubmitField()
	{
		$strLabel = &$GLOBALS['TL_LANG']['MSC']['formhybrid']['submitLabels']['default'];
		$strClass = 'btn btn-primary';
		
		if ($this->strSubmit != '' && isset($this->arrFields[$this->strSubmit])) {
			return false;
		}
		
		if ($this->customSubmit) {
			if ($this->submitLabel != '') {
				$strLabel = $GLOBALS['TL_LANG']['MSC']['formhybrid']['submitLabels'][$this->submitLabel];
			}
			
			$strClass = $this->submitClass;
		}
		
		$arrData = array
		(
			'inputType' => 'submit',
			'label'     => is_array($strLabel) ? $strLabel : array($strLabel),
			'eval'      => array('class' => $strClass),
		);
		
		$this->arrFields[FORMHYBRID_NAME_SUBMIT] = $this->generateField(FORMHYBRID_NAME_SUBMIT, $arrData);
	}
	
	protected function generateFormIdentifierField()
	{
		$arrData = array
		(
			'inputType' => 'hidden',
			'value'     => $this->getFormId(),
		);
		
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
		
		foreach ($this->arrHiddenFields as $strName => $objWidget) {
			$strBuffer .= $objWidget->parse();
		}
		
		return $strBuffer;
	}
	
	protected function loadDC()
	{
		\Controller::loadDataContainer($this->strTable);
		\System::loadLanguageFile($this->strTable);
		
		if (!isset($GLOBALS['TL_DCA'][$this->strTable])) {
			return false;
		}
		
		// Call onload_callback, but only if 3rd callback parameter is set to true, otherwise contao backend related callbacks
		// where a BackendUser is required might get called
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'])) {
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] as $callback) {
				if ($callback[2] !== true) {
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
		
		$this->dca = $GLOBALS['TL_DCA'][$this->strTable];
		
		$this->modifyDC($this->dca);
		
		// store modified dca, otherwise for example widgets wont contain modified callbacks
		$GLOBALS['TL_DCA'][$this->strTable] = $this->dca;
		
		return true;
	}
	
	protected function save($varValue = '')
	{
		if ($this->isFilterForm) {
			return;
		}
		
		if (!$this->objActiveRecord instanceof \Contao\Model) {
			return;
		}
		
		$this->arrOriginalRow = $this->objActiveRecord->originalRow();
		
		if ($this->saveToBlob) {
			$this->saveToBlob();
		} else {
			$this->objActiveRecord->save();
		}
		
		$this->intId     = $this->objActiveRecord->id;
		$this->strFormId = $this->getFormId(); // update form id with new entity id
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
		
		$arrClasses = array_filter($arrClasses);
		
		return implode(' ', $arrClasses);
	}
	
	protected function reset()
	{
		if ($this->async && $this->isRelatedAjaxRequest()) {
			// on reset, reset submission id within session
			FormSession::freeSubmissionId($this->getFormId(false));
			
			$this->isSubmitted       = false;
			$this->intId             = null;
			$this->arrFields         = array();
			$this->arrSubFields      = array();
			$this->arrSubmission     = array();
			$this->arrHiddenFields   = array();
			$this->arrInvalidFields  = array();
			$this->setSkipValidation(false);
			$this->setDoNotSubmit(false);
			$this->initialize();
			$this->generateFields();
		}
		
		$this->redirectAfterSubmission();
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
			$varValue = \Input::get($strParam);
			
			if ($varValue === null) {
				continue;
			}
			
			switch ($strParam) {
				case 'token':
					if ($this->deactivateTokens) {
						break;
					}
					$strUrl = Url::addQueryString($strParam . '=' . \RequestToken::get(), $strUrl);
					break;
				default:
					$strUrl = Url::addQueryString($strParam . '=' . $varValue, $strUrl);
			}
		}
		
		if ($blnRedirect) {
			\HeimrichHannot\StatusMessages\StatusMessage::reset($this->objModule->id);
		}
		
		if ($this->async) {
			if ($blnRedirect) {
				$objResponse = new ResponseRedirect();
				$objResponse->setUrl($strUrl);
				die(json_encode($objResponse));
			}
			
			return;
		}
		
		\Controller::redirect($strUrl);
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
	
	public function addEditableField($strName, array $arrData)
	{
		$this->dca['fields'][$strName] = $arrData;
		$this->arrEditable[]           = $strName;
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
		return \Environment::get('isAjaxRequest') && \Input::post('scope') == FORMHYBRID_ACTION_SCOPE;
	}
	
	public function onCreateCallback($objItem, \DataContainer $objDc)
	{
	}
	
	public function onUpdateCallback($objItem, \DataContainer $objDc, $blnJustCreated, array $arrOriginalRow = array())
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
		return FormHelper::getFormId($this->strTable, $this->objModule->id, $this->intId, $blnAddEntityId);
	}
	
	public function getFormName()
	{
		return 'formhybrid_' . str_replace('tl_', '', $this->strTable);
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
		$this->relatedAjaxRequest = (boolean) $isRelatedAjaxRequest;
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
	
	protected function setData(array $arrData = array())
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
	
}
