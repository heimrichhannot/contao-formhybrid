<?php
namespace HeimrichHannot\FormHybrid;

use Contao\Widget;

abstract class DC_Hybrid extends \DataContainer
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

	protected $arrFields = array();

	protected $arrSubFields = array();

	protected $arrEditable = array();

	protected $arrRequired = array();

	protected $overwriteRequired = false;

	protected $arrSubmission = array();

	protected $hasSubmit = false;

	protected $async = false;

	protected $arrAttributes = array();

	protected $useCustomSubTemplates = false;

	protected $username = FORMHYBRID_USERNAME;

	public $objModule; // public, required by callbacks

	protected $isFilterForm = false;

	public function __construct($strTable, $objModule = null)
	{
		$this->import('Database');
		$this->strTable  = $strTable;
		$this->objModule = $objModule;
		$this->loadDC();

		$this->initialize();

		// Ajax request
		if ($_POST && \Environment::get('isAjaxRequest')) {
			$this->objAjax = new FormAjax(\Input::post('action'));
			$this->objAjax->executePostActions($this);
		}
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
			} else {
				$this->Template->invalid            = true;
				$_SESSION[FORMHYBRID_MESSAGE_ERROR] = $GLOBALS['TL_LANG']['formhybrid']['messages']['error']['invalidId'];
			}
		} else {
			$this->objActiveRecord = class_exists($strModelClass) ? new $strModelClass : new Submission();
			$this->setDefaults();
			$this->setSubmission();
			// frontendedit saves the model initially in order to get an id
			if ($this->initiallySaveModel) {
				$this->objActiveRecord->tstamp = 0;
				$this->objActiveRecord->save();
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

			return $this->replaceInsertTags($this->Template->parse(), false);
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

		if ($blnAjax)
		{
			$objTemplate = $this->generateSubpalette($ajaxId);

			return $this->replaceInsertTags($objTemplate->parse(), false);
		}

		if ($this->isSubmitted && !$this->doNotSubmit) {

			// save for save_callbacks
			$this->save();

			// run field callbacks
			$this->runCallbacks();

			$this->objActiveRecord->refresh();

			// create new version
			$this->createVersion();

			// process form
			$this->processForm();
		}

		$this->generateStart();

		$this->Template->fields      = $this->arrFields;
		$this->Template->isSubmitted = $this->isSubmitted;
		$this->Template->submission  = $this->objActiveRecord;

		if (isset($_SESSION[FORMHYBRID_MESSAGE_SUCCESS])) {
			$this->Template->messageType = 'success';
			$this->Template->message     = $_SESSION[FORMHYBRID_MESSAGE_SUCCESS];
			unset($_SESSION[FORMHYBRID_MESSAGE_SUCCESS]);
		}

		if (isset($_SESSION[FORMHYBRID_MESSAGE_ERROR])) {
			$this->Template->messageType = 'danger';
			$this->Template->message     = $_SESSION[FORMHYBRID_MESSAGE_ERROR];
			unset($_SESSION[FORMHYBRID_MESSAGE_ERROR]);
		}

		$this->compile();

		return $this->replaceInsertTags($this->Template->parse(), false);
	}

	public function generateFields($ajaxId = null)
	{
		$arrFields    = $this->arrEditable;
		$arrSubFields = array();

		// subpalettes
		$arrSubpalettes = $this->dca['subpalettes'];
		$blnAjax        = false;

		if (is_array($arrSubpalettes)) {
			$toggleSubpalette = str_replace('sub_', '', $ajaxId);

			foreach ($this->dca['subpalettes'] as $strName => $strPalette) {
				$arrSubpaletteFields = FormHelper::getPaletteFields($this->strTable, $this->dca['subpalettes'][$strName]);

				// if subpalette isn't active, remove the fields
				if (!isset($this->arrDefaultValues[$strName]) || !$this->arrDefaultValues[$strName]) {
					$arrFields = array_diff($arrFields, $arrSubpaletteFields);
				}

				// if current subplatte is requested by FormhybridAjaxRequest.toggleSubpalettes() return the palette
				if ($toggleSubpalette == $strName || $this->arrSubmission[$strName]) {
					$arrSubFields[$strName] = array_intersect($this->arrEditable, $arrSubpaletteFields);

					if ($ajaxId !== null) {
						$blnAjax = true;
						break;
					}
				}
			}
		}

		// add palette fields
		foreach ($arrFields as $strName) {
			$this->addField($strName);
		}

		// add subpalette fields
		foreach ($arrSubFields as $strParent => $arrFields) {
			foreach ($arrFields as $strName) {
				$this->addSubField($strName, $strParent);
			}

			if (!$blnAjax) {
				$objSubTemplate                   = $this->generateSubpalette('sub_' . $strParent);
				$this->arrFields[$strParent]->sub = $this->replaceInsertTags($objSubTemplate->parse(), false);
			}
		}


		// add submit button if not configured in dca
		if (!$this->hasSubmit && !$blnAjax) {
			$this->generateSubmitField();
		}

		return $blnAjax;
	}

	protected function addField($strName)
	{
		if (!in_array($strName, array_keys($this->dca['fields']))) {
			return false;
		}

		if ($objField = $this->generateField($strName, $this->dca['fields'][$strName])) {
			$this->arrFields[$strName] = $objField;
		}

		return true;
	}

	protected function addSubField($strName, $strParent)
	{
		if (!in_array($strName, array_keys($this->dca['fields']))) {
			return false;
		}

		if ($objField = $this->generateField($strName, $this->dca['fields'][$strName]))
		{
			$this->arrSubFields[$strParent][$strName] = $objField;
		}

		return true;
	}

	protected function generateField($strName, $arrData)
	{
		$strClass       = $GLOBALS['TL_FFL'][$arrData['inputType']];
		$strInputMethod = $this->strInputMethod;

		// Continue if the class is not defined
		if (!class_exists($strClass)) {
			return false;
		}

		// GET fallback
		if ($this->strMethod == FORMHYBRID_METHOD_GET && \Input::get($strName)) {
			$this->isSubmitted = true;
		}

		if ($this->isSubmitted) {
			$varValue = \Input::$strInputMethod($strName);
			$varValue = FormHelper::transformSpecialValues($varValue, $arrData);
		} else {
			// contains the load_callback!
			$varValue = $this->getDefaultFieldValue($strName);
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

		$arrWidget = \Widget::getAttributesFromDca($arrData, $strName, $varValue, $strName, $this->strTable, $this);

		if (isset($this->dca['subpalettes'][$strName])) {
			$arrWidget['onclick'] = "FormhybridAjaxRequest.toggleSubpalette(this, 'sub_" . $strName . "', '" . $strName . "')";
			unset($arrWidget['submitOnChange']);
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

		if ($this->isSubmitted) {
			FrontendWidget::validateGetAndPost($objWidget, $this->strMethod);

			// Make sure unique fields are unique
			if ($arrData['eval']['unique'] && $varValue != ''
				&& !\Database::getInstance()->isUniqueValue(
					$this->strTable,
					$strName,
					$varValue,
					$this->instanceId > 0 ? $this->instanceId : null
				)
			) {
				$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $strName));
			}

			if ($objWidget->hasErrors()) {
				$this->doNotSubmit = true;
			} elseif ($objWidget->submitInput()) {
				$this->objActiveRecord->{$strName} = FormHelper::transformSpecialValues($objWidget->value, $arrData, $objWidget);
			}
		}

		return $objWidget;
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

	protected function getDefaultFieldValue($strName)
	{
		// priority 2 -> set value from model entity ($this->setDefaults() triggered before)
		if (isset($this->objActiveRecord->{$strName})) {
			$varValue = $this->objActiveRecord->{$strName};
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
		$this->Template->formName   = $this->strFormName;
		$this->Template->formId     = $this->strFormId;
		$this->Template->method     = $this->strMethod;
		$this->Template->action     = $this->strAction;
		$this->Template->enctype    = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$this->Template->novalidate = $this->novalidate ? ' novalidate' : '';

		$this->Template->class     = (strlen($this->strClass) ? $this->strClass . ' ' : '') . $this->strFormName . ' formhybrid';
		$this->Template->formClass = (strlen($this->strFormClass) ? $this->strFormClass : '');

		if ($this->async) {
			$this->arrAttributes['data-async'] = 'true';
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

			$varValue = $objWidget->value;

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
		if($this->isFilterForm)
		{
			return;
		}

		if (!$this->objActiveRecord instanceof \Contao\Model) {
			return;
		}

		// Create the initial version (see #7816)
		$objVersion = new \Versions($this->strTable, $this->objActiveRecord->id);
		$objVersion->setUserId(0);
		$objVersion->setUsername($this->username);

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
			'A new version of record "' . $this->strTable . '.id=' . $this->intId . '" has been created' . $this->getParentEntries(
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
		if(\Database::getInstance()->tableExists($this->strTable))
		{
			$arrFields = \Database::getInstance()->listFields($this->strTable);
		} else
		{
			$arrFields = $this->dca['fields'];
		}

		foreach ($arrFields as $strName => $arrField) {

			// if database field
			if(isset($arrField['name']))
			{
				$strName = $arrField['name'];
			}

			// set from default field value
			if (($varDefault = $this->dca['fields'][$strName]['default']) !== null) {
				$this->objActiveRecord->{$strName} = $varDefault;
			}

			if ($this->addDefaultValues && ($varDefault = $this->arrDefaultValues[$strName]) !== null) {
				$this->objActiveRecord->{$strName} = $varDefault['value'];
			}
		}

		// add more fields, for example from other palettes or fields that have no palette or no sql
		foreach ($this->arrDefaultValues as $strField => $varDefault)
		{
			$arrData = $this->dca['fields'][$strField];

			if (!in_array($strField, $this->arrEditable) && isset($arrData['inputType'])) {

				if ($varDefault['hidden']) {
					$this->dca['fields'][$strField]['inputType'] = 'hidden';
				}

				if (strlen($varDefault['label']) > 0) {
					$this->dca['fields'][$strField]['label'][0] = $varDefault['label'];
				}

				switch ($arrData['inputType']) {
					case 'submit':
						$this->hasSubmit = true;
						break;
					default:
						$this->objActiveRecord->{$strField} = $varDefault['value'];
				}

				// do not render hidden fields yet, just set them as value in $this->objActiveRecord
				// otherwise they will get overwritten by request and checkboxes will not be checked
				if (!$varDefault['hidden'])
				{
					$this->arrEditable[] = $strField;
				}
			}
		}
	}

	/**
	 * Set the submission from request, required to check values before widget validation
	 */
	protected function setSubmission()
	{
		if(\Database::getInstance()->tableExists($this->strTable))
		{
			$arrFields = \Database::getInstance()->listFields($this->strTable);
		} else
		{
			$arrFields = $this->dca['fields'];
		}

		foreach ($arrFields as $strName => $arrField)
		{
			// if database field
			if(isset($arrField['name']))
			{
				$strName = $arrField['name'];
			}

			$arrData = $this->dca['fields'][$strName];

			// unset options_callback, as long as we have no valid backend user
			unset($arrData['options_callback'], $arrData['options_callback']);

			$arrAttribues = \Widget::getAttributesFromDca($arrData, $strName, $this->objActiveRecord->{$strName}, $strName, $this->strTable, $this);

			switch ($this->strMethod) {
				case FORMHYBRID_METHOD_GET:
					$this->arrSubmission[$strName] = FormHelper::getGet($strName);
					break;
				case FORMHYBRID_METHOD_POST:
					$this->arrSubmission[$strName] =
						FormHelper::getPost($strName, $arrAttribues['decodeEntities'], $arrAttribues['allowHtml'], $arrAttribues['preserveTags']);
					break;
			}
		}
	}

	protected function generateSubmitField()
	{
		$arrData = array
		(
			'inputType' => 'submit',
			'label'     => &$GLOBALS['TL_LANG']['formhybrid']['submit'],
			'eval'      => array('class' => 'btn btn-primary'),
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

		$this->modifyDC();

		return true;
	}

	protected function save($varValue = '')
	{
		if($this->isFilterForm)
		{
			return;
		}

		if (!$this->objActiveRecord instanceof \Contao\Model) {
			return;
		}

		$this->objActiveRecord->tstamp = time();
		$this->objActiveRecord->save();
		$this->intId = $this->objActiveRecord->id;
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

	protected function modifyDC()
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

	abstract protected function processForm();
}


