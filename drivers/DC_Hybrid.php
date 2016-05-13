<?php
namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Haste\Util\Url;
use HeimrichHannot\StatusMessages\StatusMessage;

class DC_Hybrid extends \DataContainer
{
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

	protected $username = FORMHYBRID_USERNAME;

	protected $saveToBlob = false;

	public $objModule; // public, required by callbacks

	protected $isFilterForm = false;

	public function __construct($strTable, $objModule = null, $intId = 0)
	{
		$this->import('Database');
		$this->strTable = $strTable;
		$this->objModule = $objModule;
		$this->intId = $this->intId ?: $intId;
		$this->loadDC();

		$this->initialize();

		// Ajax request - FORM_SUBMIT must be given ($this->isSubmitted) if post
		if ($_POST && \Environment::get('isAjaxRequest') && ($this->isSubmitted || $this->strMethod == FORMHYBRID_METHOD_GET)) {
			$this->objAjax = new FormAjax(\Input::post('action'));
			$this->objAjax->executePostActions($this);
		}
	}

	protected function initialize()
	{
		// load the model
		// don't load any class if the form's a filter form -> submission should be used instead
		if (!$this->isFilterForm)
		{
			$strModelClass = \Model::getClassFromTable($this->strTable);
		}

		if ($this->intId && is_numeric($this->intId)) {
			if (($objModel = $strModelClass::findByPk($this->intId)) !== null) {
				$this->objActiveRecord = $objModel;

				if ($this->saveToBlob)
				{
					$arrBlob = deserialize($objModel->formHybridBlob, true);

					foreach ($arrBlob as $strField => $varValue)
					{
						$this->objActiveRecord->{$strField} = $varValue;
					}
				}

				// redirect on specific field value
				static::doFieldDependentRedirect($this, $this->objActiveRecord);
			} else {
				$this->Template->invalid = true;
				StatusMessage::addError($GLOBALS['TL_LANG']['formhybrid']['messages']['error']['invalidId'], $this->objModule->id, 'alert alert-danger');
			}
		} else {
			if (class_exists($strModelClass))
			{
				$objReflection = new \ReflectionClass($strModelClass);
			}
			$this->objActiveRecord = class_exists($strModelClass) && !$objReflection->isAbstract() ? new $strModelClass : new Submission();
			$this->setDefaults();
			$this->setSubmission();

			// frontendedit saves the model initially in order to get an id
			if ($this->initiallySaveModel && !$this->intId) {
				$this->objActiveRecord->tstamp = 0;
				$this->objActiveRecord->save();

				// run onsubmit_callback, required for example by HeimrichHannot\FormHybrid\TagsHelper::saveTagsFromDefaults()
				if (is_array($this->dca['config']['onsubmit_callback'])) {
					foreach ($this->dca['config']['onsubmit_callback'] as $callback) {
						$this->import($callback[0]);
						$this->$callback[0]->$callback[1]($this);

						// reload model from database, maybe something has changed in callback
						$this->objActiveRecord->refresh();
					}
				}

				$strUrl = Url::getUrl();

				if (in_array('frontendedit', \ModuleLoader::getActive()))
				{
					// create -> edit
					$strUrl = Url::removeQueryString(array('act'), $strUrl);
					$strUrl = Url::addQueryString('act=' . FRONTENDEDIT_ACT_EDIT, $strUrl);
				}

				\Controller::redirect(Url::addQueryString('id=' . $this->objActiveRecord->id, $strUrl));
			}
		}
	}

	public static function doFieldDependentRedirect($objModule, $objModel)
	{
		if ($objModule->formHybridAddFieldDependentRedirect)
		{
			$arrConditions = deserialize($objModule->formHybridFieldDependentRedirectConditions, true);
			$blnRedirect = true;

			if (!empty($arrConditions)) {
				foreach ($arrConditions as $arrCondition)
				{
					if ($objModel->{$arrCondition['field']} != $objModule->replaceInsertTags($arrCondition['value']))
						$blnRedirect = false;
				}

			}

			if ($blnRedirect)
			{
				global $objPage;

				if (($objPageJumpTo = \PageModel::findByPk($objModule->formHybridFieldDependentRedirectJumpTo)) !== null
					|| $objPageJumpTo = $objPage)
				{
					$strRedirect = \Controller::generateFrontendUrl($objPageJumpTo->row());

					if ($objModule->formHybridFieldDependentRedirectKeepParams)
					{
						$arrParamsToKeep = explode(',', $objModule->formHybridFieldDependentRedirectKeepParams);
						if (!empty($arrParamsToKeep))
						{
							foreach (Url::getUriParameters(Url::getUrl()) as $strParam => $strValue)
							{
								if (in_array($strParam, $arrParamsToKeep))
									$strRedirect = Url::addQueryString($strParam . '=' . $strValue, $strRedirect);
							}
						}
					}

					$strRedirect = Url::addQueryString('token=' . \RequestToken::get(), $strRedirect);

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

		if ($this->isSubmitted && $this->doNotSubmit)
			$this->runOnValidationError($this->arrInvalidFields);

		if ($this->isSubmitted && !$this->doNotSubmit)
		{
			// run field callbacks, must be before save(), same as contao
			$this->runCallbacks();

			// save for save_callbacks
			$this->save();

			$this->objActiveRecord->refresh();

			// create new version
//			$this->createVersion();

			// process form
			$this->processForm();
		}

		$this->generateStart();

		$this->Template->fields = $this->arrFields;
		$this->Template->isSubmitted = $this->isSubmitted;
		$this->Template->submission = $this->objActiveRecord;

		if(!StatusMessage::isEmpty($this->objModule->id))
		{
			$this->Template->message = StatusMessage::generate($this->objModule->id);
			StatusMessage::reset($this->objModule->id);
		}

		$this->compile();

		return \Controller::replaceInsertTags($this->Template->parse(), false);
	}

	public function generateFields($ajaxId = null)
	{
		$arrFields = $this->arrEditable;
		$arrSubFields = array();
		$blnAjax = false;

		// subpalettes
		$arrSelectors = $this->dca['palettes']['__selector__'];

		if (is_array($arrSelectors))
		{
			$toggleSubpalette = str_replace('sub_', '', $ajaxId);

			foreach ($arrSelectors as $strName)
			{
				list($blnActive, $strSubPalette, $arrFields) = $this->retrieveSubpaletteWithState($strName, $arrFields);

				if (!isset($this->dca['subpalettes'][$strSubPalette]))
				{
					continue;
				}

				// get all subpalette fields from palette name
				$arrSubpaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strSubPalette]);

				// determine active subpalette fields
				$arrActiveSubpaletteFields = array_intersect($arrFields, $arrSubpaletteFields);

				// if subpalette is requested, set state to active, clear parent fields and break
				if($toggleSubpalette == $strName)
				{
					$blnAjax = true;
					$arrFields = array(); // clear fields array
					$arrSubFields[$strName] = $arrActiveSubpaletteFields;
					break; // this function can only return one subpalette at once
				}

				// active by default
				if($blnActive)
				{
					// selector field is visible
					if(in_array($strName, $this->arrEditable))
					{
						$arrFields = array_diff($arrFields, $arrActiveSubpaletteFields);
						$arrSubFields[$strName] = $arrActiveSubpaletteFields;
					}
					// for example selector is triggered by default value
					else
					{
						// do nothing, fields should remain in $arrFields
					}
				}
				// inactive
				else
				{
					$arrFields = array_diff($arrFields, $arrActiveSubpaletteFields);
				}
			}
		}

		// add palette fields
		foreach ($arrFields as $strName)
		{
			$this->addField($strName);
		}

		// add subpalette fields
		foreach ($arrSubFields as $strParent => $arrFields)
		{
			// check if subpalette has fields
  			if(empty($arrFields))
			{
				continue;
			}

			foreach ($arrFields as $strName)
			{
				$this->addSubField($strName, $strParent, $blnAjax);
			}

			if (!$blnAjax)
			{
				$objSubTemplate = $this->generateSubpalette('sub_' . $strParent);

				// parent field must exist
				if(!$this->arrFields[$strParent])
				{
					$this->addField($strParent);
				}

				// append subfields to parent field
				if ($this->arrFields[$strParent])
				{
					$this->arrFields[$strParent]->sub = \Controller::replaceInsertTags($objSubTemplate->parse(), false);
				}
			}
		}

		// add submit button
		if (!$blnAjax)
		{
			$this->generateSubmitField();
		}

		return $blnAjax;
	}


	/**
	 * Return the value, considering default and submitted values
	 * @param $strName
	 *
	 * @return mixed
	 */
	protected function getFieldValue($strName)
	{
		$inputMethod = strtolower($this->strMethod);

		$varValue = $this->getDefaultFieldValue($strName, $this->dca['fields'][$strName]);

		if($this->isSubmitted)
		{
			$varValue = \Input::$inputMethod($strName);
		}

		return $varValue;
	}

	/**
	 * Retrieve the subpalette by the field selector
	 * @param   String  $strSelector
	 * @param	Array  $arrFields
	 *
	 * @return array Return the state, subpalette name, the filtered fields array and autosubmit state
	 */
	protected function retrieveSubpaletteWithState($strSelector, array $arrFields)
	{
		$blnActive = null;
		$blnAutoSubmit = false;
		$strSubpalette = null;

		$varValue = $this->getFieldValue($strSelector);

		// skip arrays, they cant be array keys
		if(is_array($varValue))
		{
			return array($blnActive, $strSubpalette, $arrFields, $blnAutoSubmit);
		}

		// checkbox: addImage for example
		if ($this->dca['fields'][$strSelector]['inputType'] == 'checkbox' && !$this->dca['fields'][$strSelector]['eval']['multiple'])
		{
			if(strlen($this->dca['subpalettes'][$strSelector]))
			{
				$blnActive = ($varValue == true);
				$strSubpalette = $strSelector;
			}
		}
		// radio: source in tl_news (source_default, source_external)
		else
		{
			// type selector
			if(isset($this->dca['subpalettes'][$varValue]))
			{
				$blnAutoSubmit = true;
				$blnActive = true;
				$strSubpalette = $varValue;
			}
			// concatenated type selector (e.g. source -> source_external)
			elseif (is_array($this->dca['subpalettes']))
			{
				if(isset($this->dca['subpalettes'][$strSelector .'_'. $varValue]))
				{
					$blnActive = true;
					$strSubpalette = $strSelector .'_'. $varValue;
				}

				// filter out non selected type subpalette fields
				foreach(array_keys($this->dca['subpalettes']) as $strKey)
				{
					// skip current active type selector subpalette
					if($strKey == $strSelector .'_'. $varValue)
					{
						continue;
					}

					// remove fields from same selector, but not active
					if(\HeimrichHannot\Haste\Util\StringUtil::startsWith($strKey, $strSelector .'_'))
					{
						// if no concatenated type selector has been selected, yet -> return the first in order
						// to create FormhybridAjaxRequest.toggleSubpalette calls in the following function
						if (!$strSubpalette)
							$strSubpalette = $strKey;

						$arrSiblingSubPaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strKey]);

						if(is_array($arrSiblingSubPaletteFields))
						{
							$arrFields = array_diff($arrFields, $arrSiblingSubPaletteFields);
						}
					}
				}
			}
		}

		return array($blnActive, $strSubpalette, $arrFields, $blnAutoSubmit);
	}



	protected function addField($strName)
	{
		if (!in_array($strName, array_keys($this->dca['fields']))) {
			return false;
		}

		if ($objField = $this->generateField($strName, $this->dca['fields'][$strName]))
		{
			$this->arrFields[$strName] = $objField;

			if($objField->type == 'submit')
			{
				$this->strSubmit = $strName;
			}
		}

		return true;
	}

	protected function addSubField($strName, $strParent, $skipValidation=false)
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
		$strInputMethod = $this->strInputMethod;

		// Continue if the class is not defined
		if (!class_exists($strClass)) {
			return false;
		}

		// GET fallback
		if ($this->strMethod == FORMHYBRID_METHOD_GET && isset($_GET[$strName])) {
			$this->isSubmitted = true;
		}

		$arrWidgetErrors = array();

		// contains the load_callback!
		$varDefault = $this->getDefaultFieldValue($strName, $arrData);
		$varValue = $varDefault;

		if ($this->isSubmitted && !$skipValidation) {
			$varValue = \Input::$strInputMethod($strName);
			$varValue = FormHelper::transformSpecialValues($varValue, $arrData, $this->strTable, $this->intId, $varDefault, $arrWidgetErrors);
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

		if ($this->skipValidation)
		{
			$arrData['eval']['mandatory'] = false;
		}

		$arrWidget = \Widget::getAttributesFromDca(
			$arrData, $strName, is_array($varValue) ? $varValue : \Controller::replaceInsertTags($varValue), $strName,
			$this->strTable, $this
		);

		$this->updateWidget($arrWidget, $arrData);

		list($blnActive, $strSubPalette, $arrFields, $blnAutoSubmit) = $this->retrieveSubpaletteWithState($strName, array_keys($this->arrFields));

		// support submitOnChange as form submission
		if ($arrData['eval']['submitOnChange'] && isset($this->dca['subpalettes'][$strSubPalette]))
		{
			if($blnAutoSubmit)
			{
				$arrWidget['onchange'] = "this.form.submit();";
			}
			else
			{
				$strEvent = 'onclick';

				switch($arrData['inputType'])
				{
					case 'select':
						$strEvent = 'onchange';
					break;
				}

				$arrWidget[$strEvent] = "FormhybridAjaxRequest.toggleSubpalette(this, 'sub_" . $strName . "', '"
						. $strName . "')";
				unset($arrWidget['submitOnChange']);
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

		if ($this->isSubmitted && !($this->skipValidation || $skipValidation)) {
			FrontendWidget::validateGetAndPost($objWidget, $this->strMethod, $this->strTable, $this->objModule->id, $arrData);

			if(is_array($arrWidgetErrors))
			{
				foreach($arrWidgetErrors as $strError)
				{
					$objWidget->addError($strError);
				}
			}

			if ($objWidget->value && $this->strMethod == FORMHYBRID_METHOD_GET)
				$objWidget->class = 'filtered';

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
				$this->doNotSubmit = true;
				$this->arrInvalidFields[] = $strName;
			} elseif ($arrData['inputType'] == 'tag' && in_array('tags_plus', \ModuleLoader::getActive()))
			{
				$varValue = deserialize($objWidget->value);

				if (!is_array($varValue))
					$varValue = array($varValue);

				if ($this->intId)
					\HeimrichHannot\TagsPlus\TagsPlus::saveTags($this->strTable, $this->intId, array_map('urldecode', $varValue));
			}
			elseif ($objWidget->submitInput()) {
				// save non escaped to database
				if (is_array($objWidget->value))
				{
					$this->objActiveRecord->{$strName} = array_map(function($varVal) use ($arrData) {
						$varVal = FormHelper::transformSpecialValues($varVal, $arrData, $this->strTable, $this->intId);

						if(is_array($varVal))
						{
							foreach($varVal as $key => $val)
							{
								$varVal[$key] = html_entity_decode($val);
							}
						}
						else
						{
							$varVal = html_entity_decode($varVal);
						}

						return $varVal;
					}, $objWidget->value);
				}
				else
				{
					$this->objActiveRecord->{$strName} = html_entity_decode(FormHelper::transformSpecialValues(
						$objWidget->value, $arrData, $this->strTable, $this->intId
					));
				}
			} // support file uploads
			elseif ($objWidget instanceof \uploadable && $arrData['inputType'] == 'multifileupload') {
				$strMethod = strtolower($this->strMethod);
				if (\Input::$strMethod($strName))
				{
					$arrValue = json_decode(\Input::$strMethod($strName));

					if (!empty($arrValue))
					{
						$arrValue = array_map(function($val) {
							return \String::uuidToBin($val);
						}, $arrValue);

						$this->objActiveRecord->{$strName} = serialize($arrValue);
					}
					else
						$this->objActiveRecord->{$strName} = serialize($arrValue);
				}

				// delete the files scheduled for deletion
				$objWidget->deleteScheduledFiles(json_decode(\Input::$strMethod('deleted_' . $strName)));
			} elseif($objWidget instanceof \uploadable && isset($_SESSION['FILES'][$strName])
					&& \Validator::isUuid($_SESSION['FILES'][$strName]['uuid']))
			{
				$this->objActiveRecord->{$strName} = $_SESSION['FILES'][$strName]['uuid'];
			}
		}

		return $objWidget;
	}

	protected function updateWidget($arrWidget, $arrData) {}

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
			$varValue = FormHelper::transformSpecialValues($this->objActiveRecord->{$strName}, $arrData, $this->strTable, $this->intId);
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
		$this->Template->formName = $this->strFormName;
		$this->Template->formId = $this->strFormId;
		$this->Template->method = $this->strMethod;
		$this->Template->action = $this->strAction;
		$this->Template->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$this->Template->skipScrollingToSuccessMessage = $this->skipScrollingToSuccessMessage;
		$this->Template->novalidate = $this->novalidate ? ' novalidate' : '';

		$this->Template->class = (strlen($this->strClass) ? $this->strClass . ' ' : '') . $this->strFormName
			. ' formhybrid' . ($this->isFilterForm ? ' filter-form' : '') . ($this->isSubmitted ? ' submitted' : '') .
			($this->intId ? ' has-model' : '');
		$this->Template->formClass = (strlen($this->strFormClass) ? $this->strFormClass : '');

		if ($this->async) {
			$this->arrAttributes['data-async'] = 'true';
		}

		if (is_array($this->arrAttributes)) {
			$arrAttributes = $this->arrAttributes;
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

		if ($this->useCustomSubTemplates) {
			$strSubTemplate = $this->strTemplate . '_' . $ajaxId;
		}

		$strName = str_replace('sub_', '', $ajaxId);
		$objTemplate = new \FrontendTemplate($strSubTemplate);
		$objTemplate->ajaxId = $ajaxId;
		$objTemplate->fields = $this->arrSubFields[$strName];
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
		if ($this->isFilterForm || !$this->objActiveRecord instanceof \Contao\Model) {
			return;
		}

		// Create the initial version (see #7816)
		$objVersion = new \Versions($this->strTable, $this->objActiveRecord->id);
		$objVersion->userid = 0;
		$objVersion->username = $this->username;

		$objVersion = $this->modifyVersion($objVersion);

		$objVersion->initialize();

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
				$this->arrDefaults[$strName] = \Controller::replaceInsertTags($varDefault);
			}

			if ($this->addDefaultValues && ($varDefault = $this->arrDefaultValues[$strName]) !== null) {
				$this->arrDefaults[$strName] = \Controller::replaceInsertTags($varDefault['value']);
			}
		}

		// add more fields, for example from other palettes or fields that have no palette or no sql
		if (is_array($this->arrDefaultValues))
		{
			foreach ($this->arrDefaultValues as $strField => $varDefault) {
				$arrData = $this->dca['fields'][$strField];

				if (!in_array($strField, $this->arrEditable) && isset($arrData['inputType'])) {

					if (strlen($varDefault['label']) > 0)
					{
						$this->dca['fields'][$strField]['label'][0] = $varDefault['label'];
					}

					switch ($arrData['inputType']) {
						case 'tag':
							if(!in_array('tags', \ModuleLoader::getActive())) break;

							if($varDefault['value'] != '')
							{
								$this->dca['config']['onsubmit_callback'][] = array('HeimrichHannot\FormHybrid\TagsHelper', 'saveTagsFromDefaults');
							}

							break;
						default:
							$this->arrDefaults[$strField] = \Controller::replaceInsertTags($varDefault['value']);
					}
				}
			}
		}

		// set active record from defaults
		if (is_array($this->arrDefaults)) {
			foreach ($this->arrDefaults as $strName => $varValue) {
				$this->objActiveRecord->{$strName} = $varValue;
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
				$arrData, $strName, $this->objActiveRecord->{$strName}, $strName, $this->strTable, $this
			);

			switch ($this->strMethod) {
				case FORMHYBRID_METHOD_GET:
					$this->arrSubmission[$strName] = FormHelper::getGet($strName);
					break;
				case FORMHYBRID_METHOD_POST:
					$this->arrSubmission[$strName] = FormHelper::getPost(
						$strName, $arrAttribues['decodeEntities'], $arrAttribues['allowHtml'],
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

		if($this->strSubmit != '' && isset($this->arrFields[$this->strSubmit]))
		{
			return false;
		}

		if($this->objModule->formHybridCustomSubmit)
		{
			if($this->objModule->formHybridSubmitLabel != '')
			{
				$strLabel = $GLOBALS['TL_LANG']['MSC']['formhybrid']['submitLabels'][$this->objModule->formHybridSubmitLabel];
			}

			$strClass = $this->objModule->formHybridSubmitClass;
		}
		
		$arrData = array
		(
			'inputType' => 'submit',
			'label'     => is_array($strLabel) ? $strLabel : array($strLabel),
			'eval'      => array('class' => $strClass),
		);

		$this->arrFields[FORMHYBRID_NAME_SUBMIT] = $this->generateField(FORMHYBRID_NAME_SUBMIT, $arrData);
	}

	protected function loadDC()
	{
		\Controller::loadDataContainer($this->strTable);
		\System::loadLanguageFile($this->strTable);

		if (!isset($GLOBALS['TL_DCA'][$this->strTable])) {
			return false;
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

		if ($this->saveToBlob)
		{
			$this->objActiveRecord->formHybridBlob = null;
			\Database::getInstance()->prepare("UPDATE $this->strTable SET $this->strTable.formHybridBlob = ? WHERE id=?")
				->execute(serialize($this->objActiveRecord->row()), $this->intId);
		}
		else
		{
			$this->objActiveRecord->tstamp = time();
			$this->objActiveRecord->save();
			$this->intId = $this->objActiveRecord->id;
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

	public function addEditableField($strName, array $arrData)
	{
		$this->dca['fields'][$strName] = $arrData;
		$this->arrEditable[] = $strName;
	}

	public function getDca()
	{
		return $this->dca;
	}

	protected function processForm()
	{
	}

	public function getTable()
	{
		return $this->strTable;
	}

	public function getFormId()
	{
		return $this->strTable . '_' . $this->objModule->id;
	}

	public function getFormName()
	{
		return 'formhybrid_' . str_replace('tl_', '', $this->strTable);
	}

	public function runOnValidationError($arrInvalidFields) {}

}
