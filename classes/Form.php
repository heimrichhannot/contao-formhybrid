<?php

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\XCommonEnvironment;

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

	protected $arrSubPalettes = array();

	protected $arrEditableBoxes = array();

	protected $arrDefaultValues = array();

	protected $arrLegends = array();

	protected $isSubmitted = false;

	protected $doNotSubmit = false;

	protected $objModel;

	protected $objModule;

	protected $strTemplate = 'formhybrid_default';

	protected $strMethod = FORMHYBRID_METHOD_GET;

	protected $strAction = null;

	protected $hasUpload = false;

	protected $hasSubmit = false;

	protected $novalidate = true;

	protected $strClass;

	protected $strFormClass;

	protected $instanceId = 0; // id of model entitiy

	public function __construct(\ModuleModel $objModule=null, $instanceId = 0)
	{
		parent::__construct();

		global $objPage;

		if($objModule !== null && $objModule->formHybridDataContainer && $objModule->formHybridPalette)
		{
			$this->objModule = $objModule;
			$this->arrData = $objModule->row();
			$this->strTable = $objModule->formHybridDataContainer;
			$this->strPalette = $objModule->formHybridPalette;
			$this->arrEditable = deserialize($objModule->formHybridEditable, true);
			$this->arrSubPalettes = deserialize($objModule->formHybridSubPalettes, true);
			$this->strTemplate = $objModule->formHybridTemplate;
			$this->addDefaultValues = $objModule->formHybridAddDefaultValues;
			$this->arrDefaultValues = deserialize($objModule->formHybridDefaultValues, true);
			$this->instanceId = $instanceId;
		}

		$this->strInputMethod = $strInputMethod = strtolower($this->strMethod);
		$this->strActionDefault = ($this->instanceId ?
			XCommonEnvironment::addParameterToUri($this->generateFrontendUrl($objPage->row()), 'id', $this->instanceId) :
			$this->generateFrontendUrl($objPage->row()));
		$this->strAction = is_null($this->strAction) ? $this->strActionDefault : $this->strAction;
		$this->skipValidation = \Input::$strInputMethod(FORMHYBRID_NAME_SKIP_VALIDATION);
		$this->strFormId = $this->strTable;
		$this->strFormName = 'formhybrid_' . str_replace('tl_', '', $this->strTable);
		// GET is checked for each field separately
		$this->isSubmitted = (\Input::post('FORM_SUBMIT') == $this->strFormId);
	}

	public function generate()
	{
		if(!$this->loadDC()) return false;

		if(!$this->getFields()) return false;

		$this->Template = new \FrontendTemplate($this->strTemplate);

		// Load the model
		$strModelClass = \Model::getClassFromTable($this->strTable);
		if ($this->instanceId && is_numeric($this->instanceId)){
			if(($objModel = $strModelClass::findByPk($this->instanceId)) !== null)
			{
				$this->objModel = $objModel;
			}else
			{
				$this->Template->invalid = true;
				$_SESSION[FORMHYBRID_MESSAGE_ERROR] = $GLOBALS['TL_LANG']['formhybrid']['messages']['error']['invalidId'];
			}
		}
		else
		{
			$this->objModel = class_exists($strModelClass) ? new $strModelClass : new Submission();
		}

		$this->generateFields();
		$this->processForm();
		// regenerate fields after onsubmit callbacks...
		if ($this->isSubmitted && !$this->doNotSubmit)
		{
			$this->arrFields = array();
			$this->objModel->refresh();
			// ... and use the model data (but only, if validation succeeded)
			$this->generateFields(true);
		}

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

		if (isset($_SESSION[FORMHYBRID_MESSAGE_SUCCESS]))
		{
			$this->Template->messageType = 'success';
			$this->Template->message = $_SESSION[FORMHYBRID_MESSAGE_SUCCESS];
			unset($_SESSION[FORMHYBRID_MESSAGE_SUCCESS]);
		}

		if (isset($_SESSION[FORMHYBRID_MESSAGE_ERROR]))
		{
			$this->Template->messageType = 'danger';
			$this->Template->message = $_SESSION[FORMHYBRID_MESSAGE_ERROR];
			unset($_SESSION[FORMHYBRID_MESSAGE_ERROR]);
		}

		$this->compile();

		return $this->Template->parse();
	}

	protected function loadDC()
	{
		\Controller::loadDataContainer($this->strTable);
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
			if (is_array($this->dc['subpalettes']) && in_array($strField, array_keys($this->dc['subpalettes'])))
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

		return !empty($this->arrEditable);
	}

	protected function processForm()
	{
		if($this->isSubmitted && !$this->doNotSubmit)
		{
			$this->save();

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

			$arrTokenData = array();

			foreach($this->objModel->row() as $name => $value)
			{
				if(in_array($name, array('pid', 'id', 'tstamp')) || empty($value)) continue;
				$strLabel = isset($GLOBALS['TL_LANG'][$this->strTable][$name][0]) ? $GLOBALS['TL_LANG'][$this->strTable][$name][0] : $name;

				$strValue = deserialize($value);

				// support input arrays
				if(is_array($strValue))
				{
					$strArrayValue =  "\n";
					foreach($strValue as $arrItem)
					{
						if (is_array($arrItem))
						{
							foreach($arrItem as $itemKey => $itemValue)
							{
								$label = isset($GLOBALS['TL_LANG'][$this->strTable][$itemKey][0]) ? $GLOBALS['TL_LANG'][$this->strTable][$itemKey][0] : $itemKey;

								$strArrayValue .= "\t" . $label . ": " . $itemValue . "\n";
							}
						}
						else
						{
							$strArrayValue = "\t" . $label . ": " . $arrItem . "\n";
						}
					}
					
					$arrTokenData['submission'] .= $strLabel . ": "  . "\n" . $strArrayValue . "\n";
				}
				else{
					$arrTokenData['submission'] .= $strLabel . ": "  . $strValue . "\n";
				}
			}

			if($this->formHybridSendSubmissionViaEmail)
			{
				$objEmail = new \Email();
				$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
				$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
				$objEmail->subject = $this->replaceInsertTags($this->formHybridSubmissionMailSubject, false);
				$objEmail->text = \String::parseSimpleTokens($this->replaceInsertTags($this->formHybridSubmissionMailText), $arrTokenData);
				$objEmail->sendTo($this->formHybridSubmissionMailRecipient);
			}

			$_SESSION[FORMHYBRID_MESSAGE_SUCCESS] = !empty($this->formHybridSuccessMessage) ? $this->formHybridSuccessMessage : $GLOBALS['TL_LANG']['formhybrid']['messages']['success'];
		}
	}

	protected function generateFields($useModelData = false)
	{
		// reset the flag if the fields are updated
		if ($useModelData)
			$this->hasSubmit = false;

		foreach($this->arrEditable as $name)
		{
			if(!in_array($name, array_keys($this->dc['fields']))) continue;

			if ($objField = $this->generateField($name, $this->dc['fields'][$name], $useModelData))
				$this->arrFields[$name] = $objField;
		}

		// add default values not already rendered in the palette as hidden fields
		if ($this->addDefaultValues && is_array($this->arrDefaultValues) && !empty($this->arrDefaultValues))
		{
			foreach ($this->arrDefaultValues as $arrDefaults)
			{
				if(!in_array($arrDefaults['field'], $this->arrEditable))
				{
					if ($objField = $this->generateField($arrDefaults['field'], array(
							'inputType' => 'hidden'
						), $useModelData))
					$this->arrFields[$arrDefaults['field']] = $objField;
				}
			}
		}

		// add submit button if not configured in dca
		if (!$this->hasSubmit)
		{
			$this->generateSubmitField();
		}

		// clear fields, set default value (do not clear if GET Method)
		if(!$this->instanceId && $this->strMethod != FORMHYBRID_METHOD_GET)
		{
			foreach($this->arrFields as $name => $arrField)
			{
				$this->arrFields[$name]->value = $this->getDefaultFieldValue($name);
			}
		}
	}

	protected function generateField($strName, $arrData, $useModelData = false)
	{
		$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
		$strInputMethod = $this->strInputMethod;

		// Continue if the class is not defined
		if (!class_exists($strClass)) return false;

		// GET fallback
		if($this->strMethod == FORMHYBRID_METHOD_GET && \Input::get($strName))
		{
			$this->isSubmitted = true;
		}

		// set value from request
		if ($useModelData)
		{
			if(isset($this->objModel->{$strName}))
			{
				$varValue = $this->objModel->{$strName};
			}
		}
		elseif ($this->isSubmitted)
		{
			$varValue = \Input::$strInputMethod($strName);
			$varValue = $this->transformSpecialValues($strName, $varValue, $arrData);
		}
		else
		{
			// contains the load_callback!
			$varValue = $this->getDefaultFieldValue($strName);
		}

		// handle sub palette fields
		if ($arrData['eval']['selector'])
		{
			if (!$this->getDefaultFieldValue($arrData['eval']['selector']) &&
				!call_user_func_array(array('\Input', $this->strInputMethod), array($arrData['eval']['selector'])))
			{
				return false;
			}
		}

		// prevent name for GET and submit widget, otherwise url will have submit name in
		if($this->strMethod == FORMHYBRID_METHOD_GET && $arrData['inputType'] == 'submit')
		{
			$strName = '';
		}

		// required by options_callback
		$dc = new DC_Hybrid($this->strTable, $this->objModel, $this->objModule);

		$arrWidget = \Widget::getAttributesFromDca($arrData, $strName, $varValue, $strName, $this->strTable, $dc);
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
			if(!$this->skipValidation && !$useModelData)
			{
				$objWidget->validate();
			}

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

				$dc = new DC_Hybrid($this->strTable, $this->objModel, $this->objModule);

				$varValue = $this->transformSpecialValues($strName, $varValue, $arrData);

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

	protected function save()
	{
		// TODO: handle Submission
		if(!$this->objModel instanceof \Contao\Model) return;

		$this->objModel->tstamp = time();
		$this->objModel->save();
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

	protected function getDefaultFieldValue($strName)
	{
		// priority 4 -> dca default value
		$varValue = $this->dc['fields'][$strName]['default'];

		// priority 3 -> default values defined in the module
		if ($this->addDefaultValues && is_array($this->arrDefaultValues) && !empty($this->arrDefaultValues))
		{
			foreach ($this->arrDefaultValues as $arrDefaults) {

				if (empty($arrDefaults['field']) || ($arrDefaults['field'] != $strName)) {
					continue;
				}

				$varValue = $arrDefaults['value'];
			}
		}

		// priority 2 -> set value from model entity if instanceId isset (editable form)
		if (isset($this->objModel->{$strName}))
		{
			$varValue = $this->objModel->{$strName};
		}

		// priority 1 -> load_callback
		$dc = new DC_Hybrid($this->strTable, $this->objModel, $this->objModule);

		if (is_array($this->dc['fields'][$strName]['load_callback']))
		{
			foreach ($this->dc['fields'][$strName]['load_callback'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
			}
		}

		return $varValue;
	}

	public function transformSpecialValues($strName, $varValue, $arrData)
	{
		// Convert date formats into timestamps
		if ($varValue != '' && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
		{
			$objDate = new \Date($varValue, \Config::get($arrData['eval']['rgxp'] . 'Format'));
			$varValue = $objDate->tstamp;
		}

		// Make sure unique fields are unique
		if ($arrData['eval']['unique'] && $varValue != '' && !$this->Database->isUniqueValue($this->strTable, $strName, $varValue, $this->instanceId))
		{
			throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $strName));
		}

		if ($arrData['eval']['multiple'] && isset($arrData['eval']['csv']))
		{
			$varValue = implode($arrData['eval']['csv'], deserialize($varValue, true));
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

	abstract protected function compile();

	abstract protected function onSubmitCallback(\DataContainer $dc);
}