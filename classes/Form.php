<?php

namespace HeimrichHannot\FormHybrid;

abstract class Form extends \Controller
{
	protected $arrData = array();

	protected $strTable;

	protected $strFormId;

	protected $strFormName;

	protected $dc;

	protected $arrFields = array();

	protected $strPalette = 'default';

	protected $arrEditable = array();

	protected $arrEditableBoxes = array();

	protected $arrDefaultValues = array();

	protected $arrLegends = array();

	protected $isSubmitted = false;

	protected $doNotSubmit = false;

	protected $objModel;

	protected $objModule;

	protected $strTemplate = 'formhybrid_default';

	protected $strMethod = FORMHYBRID_METHOD_POST;

	protected $strInputMethod = 'post';

	protected $srtAction = null;

	protected $hasUpload = false;

	protected $hasSubmit = false;

	protected $novalidate = true;

	protected $strClass;

	protected $strFormClass;

	public function __construct(\ModuleModel $objModule=null)
	{
		parent::__construct();

		global $objPage;

		if($objModule !== null && $objModule->formHybridPalette && $objModule->formHybridDataContainer)
		{
			$this->objModule = $objModule;
			$this->strTable = $objModule->formHybridDataContainer;
			$this->strPalette = $objModule->formHybridPalette;
			$this->arrEditable = deserialize($objModule->formHybridEditable, true);
			$this->arrSubPalettes = deserialize($objModule->formHybridSubPalettes, true);
			$this->arrDefaultValues = deserialize($objModule->arrDefaultValues, true);
			$this->instanceId = $objModule->instanceId;
		}

		switch ($this->strMethod)
		{
			case FORMHYBRID_METHOD_GET:
				$this->strInputMethod = 'get';
				break;
			case FORMHYBRID_METHOD_POST:
				$this->strInputMethod = 'post';
				break;
		}

		$this->strAction = is_null($this->strAction) ?
			($this->generateFrontendUrl($objPage->row()) . ($this->instanceId ? '?id=' . $this->instanceId : '')) : $this->strAction;
	}

	public function generate()
	{
		if(!$this->loadDC()) return false;

		if(!$this->getFields()) return false;

		$this->strFormId = $this->strTable;
		$this->strFormName = 'formhybrid_' . str_replace('tl_', '', $this->strTable);

		$this->objModel = new Submission();

		$this->generateFields();

		$this->Template = new \FrontendTemplate($this->strTemplate);
		$this->Template->fields = $this->arrFields;

		$this->Template->formName = $this->strFormName;
		$this->Template->isSubmitted = $this->isSubmitted;
		$this->Template->model = $this->objModel;
		$this->Template->formId = $this->strFormId;
		$this->Template->method = $this->strMethod;
		$this->Template->action = $this->strAction;
		$this->Template->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$this->Template->novalidate = $this->novalidate ? ' novalidate' : '';

		$this->Template->class = (strlen($this->strClass) ? $this->strClass . ' ' : '') . $this->strFormName ;
		$this->Template->formClass = (strlen($this->strFormClass) ? $this->strFormClass : '');
		if (is_array($this->arrAttributes))
		{
			$arrAttributes = $this->arrAttributes;
			$this->Template->attributes = implode(' ', array_map(function($strValue) use ($arrAttributes) {
						return $strValue . '="' . $arrAttributes[$strValue] . '"';
					}, array_keys($this->arrAttributes)));
		}
		$this->Template->cssID = ' id="' . $this->strFormName . '"';

		$this->compile();

		return $this->Template->parse();
	}

	protected function loadDC()
	{
		$this->loadDataContainer($this->strTable);

		\System::loadLanguageFile($this->strTable);

		if(!isset($GLOBALS['TL_DCA'][$this->strTable])) return false;

		$this->dc = $GLOBALS['TL_DCA'][$this->strTable];

		return true;
	}

	protected function getFields()
	{
		$arrEditable = array();
		foreach ($this->arrEditable as $strField)
		{
			// check if field really exists
			if (!$this->dc['fields'][$strField])
				continue;

			$arrEditable[] = $strField;

			// add subpalette fields
			if (in_array($strField, array_keys($this->dc['subpalettes'])))
			{
				foreach ($this->arrSubPalettes as $arrSubPalette)
				{
					if ($arrSubPalette['subpalette'] == $strField)
					{
						foreach ($arrSubPalette['fields'] as $strSubPaletteField)
						{
							if (!$this->dc['fields'][$strSubPaletteField])
								continue;
							else
							{
								$arrEditable[] = $strSubPaletteField;
								$this->dc['fields'][$strSubPaletteField]['eval']['selector'] = $strField;
							}
						}
					}
				}
			}
		}

		$this->arrEditable = $arrEditable;

		return count($this->arrEditable) > 0;
	}

	protected function generateFields()
	{
		$this->isSubmitted = \Input::post('FORM_SUBMIT') == $this->strFormId;
		$this->skipValidation = call_user_func_array(array('\Input', $this->strInputMethod), array(FORMHYBRID_NAME_SKIP_VALIDATION));

		foreach ($this->arrEditable as $strName)
		{
			if(!in_array($strName, array_keys($this->dc['fields'])))
				continue;

			if ($strField = $this->generateField($strName, $this->dc['fields'][$strName]))
				$this->arrFields[$strName] = $strField;
		}

		// add default values not already rendered as hidden fields
		foreach ($this->arrDefaultValues as $strName => $varValue)
		{
			if (!in_array($strName, array_keys($this->dc['fields'])))
				continue;

			if (!in_array($strName, $this->arrEditable))
				if ($strField = $this->generateField($strName, array('inputType' => 'hidden')))
					$this->arrFields[$strName] = $strField;
		}

		// add submit button if not configured in dca
		if (!$this->hasSubmit)
		{
			$this->generateSubmitField();
		}

		// trigger onsubmit callbacks
		if ($this->isSubmitted && !$this->doNotSubmit && !$this->skipValidation)
		{
			$dc = new DC_Hybrid($this->strTable, $this->objModel);

			$this->onSubmitCallback($dc);

			if(is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($dc);
				}
			}
		}

	}

	protected function generateField($strName, $arrData)
	{
		$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

		// Continue if the class is not defined
		if (!class_exists($strClass)) return false;

		// GET fallback
		if($this->strMethod == FORMHYBRID_METHOD_GET && \Input::get($strName))
		{
			$this->isSubmitted = true;
		}

		// contains the load_callback!
		$varValue = $this->getFieldValue($strName);

		// handle sub palette fields
		if ($arrData['eval']['selector'])
		{
			if (!$this->getFieldValue($arrData['eval']['selector']))
				return false;
		}

		// prevent name for GET and submit widget, otherwise url will have submit name in
		if($this->strMethod == FORMHYBRID_METHOD_GET && $arrData['inputType'] == 'submit')
		{
			$strName = '';
		}

		$arrWidget = \Widget::getAttributesFromDca($arrData, $strName, $varValue, $strName, $this->strTable);
		$objWidget = new $strClass($arrWidget);

		if (isset($arrData['formHybridOptions']))
		{
			$arrFormHybridOptions = $arrData['formHybridOptions'];

			$this->import($arrFormHybridOptions[0]);
			$objWidget->options = $this->$arrFormHybridOptions[0]->$arrFormHybridOptions[1]();
		}

		if ($objWidget instanceof \uploadable)
		{
			$this->hasUpload = true;
		}

		if ($objWidget->type == 'submit')
		{
			$this->hasSubmit = true;
		}

		if ($this->isSubmitted)
		{
			if (!$this->skipValidation)
				$objWidget->validate();

			if($objWidget->hasErrors())
			{
				$this->doNotSubmit = true;
			}
			elseif ($objWidget->submitInput())
			{
				if($this->strMethod == FORMHYBRID_METHOD_GET)
				{
					$objWidget->value = $varValue;
				}

				$varValue = $objWidget->value;

				// Sort array by key (fix for JavaScript wizards)
				if (is_array($varValue))
				{
					sort($varValue);
					$varValue = serialize($varValue);
				}

				$dc = new DC_Hybrid($this->strTable, $this->objModel, $this->objModule);

				// Convert date formats into timestamps
				if ($varValue != '' && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}

				// Trigger the save_callback
				if (is_array($arrData['save_callback']))
				{
					foreach ($arrData['save_callback'] as $callback)
					{
						$this->import($callback[0]);
						$varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
					}
				}

				$this->objModel->{$strName} = $varValue;
			}
		}

		return $objWidget;
	}

	protected function generateSubmitField()
	{
		$arrData = array
		(
			'inputType' => 'submit',
			'label'		=> &$GLOBALS['TL_LANG']['formhybrid']['submit'],
			'eval'		=> array('class' => 'btn btn-primary')
		);

		$this->arrFields[FORMHYBRID_NAME_SUBMIT] = $this->generateField(FORMHYBRID_NAME_SUBMIT, $arrData);
	}

	protected function getFieldValue($strName)
	{
		// priority 4 -> dca default value
		$varValue = $this->dc['fields'][$strName]['default'];

		// priority 3 -> default values defined in the module
		if (count($this->arrDefaultValues) > 0)
		{
			if (isset($this->arrDefaultValues[$strName]))
				$varValue = $this->arrDefaultValues[$strName];
		}

		// priority 2 -> load_callback
		$dc = new DC_Hybrid($this->strTable, $this->objModel, $this->objModule);

		if (is_array($this->dc['fields'][$strName]['load_callback']))
		{
			foreach ($this->dc['fields'][$strName]['load_callback'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
			}
		}

		// priority 1 -> set value from request if form's been submitted
		if($this->isSubmitted)
		{
			// e.g. \Input::get()
			$varValue = call_user_func_array(array('\Input', $this->strInputMethod), array($strName));
		}

		return $varValue;
	}

	/**
	 * Set an object property
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}

	/**
	 * Return an object property
	 * @param string
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
	 * Check whether a property is set
	 * @param string
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}

	public function getSubmission()
	{
		return $this->objModel;
	}

	public function isSubmitted()
	{
		return $this->isSubmitted;
	}

	public function doNotSubmit()
	{
		return $this->doNotSubmit;
	}

	public static function flattenMultiColumnWizardArray($arrMultiColumnWizard) {
		$arrResult = array();
		foreach ($arrMultiColumnWizard as $arrField)
		{
			$arrResult[$arrField['field']] = (@unserialize($arrField['value']) === false ? $arrField['value'] : deserialize($arrField['value'], true));
		}
		return $arrResult;
	}

	abstract protected function compile();

	abstract protected function onSubmitCallback(\DataContainer $dc);
}