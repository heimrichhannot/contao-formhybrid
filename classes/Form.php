<?php 

namespace HeimrichHannot\FormHybrid;

use Contao\DataContainer;
abstract class Form extends \Controller
{
	protected $strTable;
	
	protected $strFormId;
	
	protected $strFormName;
	
	protected $dc;
	
	protected $arrFields = array();
	
	protected $strPalette = 'default';
	
	protected $arrEditable = array();
	
	protected $arrEditableBoxes = array();
	
	protected $arrLegends = array();
	
	protected $isSubmitted = false;
	
	protected $doNotSubmit = false;
	
	protected $objModel;
	
	protected $strTemplate = 'formhybrid_default';
	
	protected $strMethod = FORMHYBRID_METHOD_GET;
	
	protected $srtAction = null;
	
	protected $hasUpload = false;
	
	protected $hasSubmit = false;
	
	protected $novalidate = true;
	
	protected $strClass;
	
	protected $strFormClass;
	
	public function __construct()
	{
		parent::__construct();
		
		global $objPage;
		
		$this->strMethod = $this->strMethod == FORMHYBRID_METHOD_GET ? FORMHYBRID_METHOD_GET : FORMHYBRID_METHOD_POST;
		$this->strAction = is_null($this->strAction) ? $this->generateFrontendUrl($objPage->row()) : $this->strAction;
	}
	
	public function generate()
	{
		if(!$this->loadDC()) return false;
		
		if(!$this->setArrFields()) return false;
		
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
	
	protected function setArrFields()
	{
		$boxes = trimsplit(';', $this->dc['palettes'][$this->strPalette]);
		$this->legends = array();

		if (!empty($boxes))
		{
			foreach ($boxes as $k=>$v)
			{
				$eCount = 1;
				$boxes[$k] = trimsplit(',', $v);

				foreach ($boxes[$k] as $kk=>$vv)
				{
					if (preg_match('/^\[.*\]$/i', $vv))
					{
						++$eCount;
						continue;
					}

					if (preg_match('/^\{.*\}$/i', $vv))
					{
						$legend = substr($vv, 1, -1);
						list($key, $cls) = explode(':', $legend);
						$this->arrLegends[$k] = $key;
						unset($boxes[$k][$kk]);
					}
					
					// Unset a field, if arrEditable is given but field not present
					if (is_array($this->arrEditable) && !empty($this->arrEditable) && !in_array($vv, $this->arrEditable) && !$this->dc['fields']['inputType'] == 'hidden')
					{
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (count($boxes[$k]) < $eCount)
				{
					unset($boxes[$k]);
				}
				
			}
			
			$arrEditable = array();
			
			// flat array
			foreach($boxes as $box)
			{
				$arrEditable = array_merge($arrEditable, $box);
			}

			$arrHidden = array();
			
			foreach($this->dc['fields'] as $name => $arrData)
			{
				if($arrData['inputType'] != 'hidden') continue;
				$arrHidden[] = $name;
			}
			
			$this->arrEditableBoxes = $boxes;
			//$this->arrEditable = array_merge($arrHidden, $arrEditable);
			
			// take into account sorting of arrEditable
			$arrEditableSorted = array();
			foreach ($this->arrEditable as $strField)
			{
				if (in_array($strField, array_merge($arrHidden, $arrEditable)))
				{
					$arrEditableSorted[] = $strField;
				}
			}
			
			$this->arrEditable = $arrEditableSorted;
			
			return true;
		}
		
		return false;
	}
	
	protected function generateField($name, $arrData)
	{
		$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
		
		// Continue if the class is not defined
		if (!$this->classFileExists($strClass)) return false;
		
		$value = $arrData['default'];
		
		// GET fallback
		if($this->strMethod == FORMHYBRID_METHOD_GET && \Input::get($name))
		{
			$this->isSubmitted = true;
		}
		
		// set value from request
		if($this->isSubmitted)
		{
			switch($this->strMethod)
			{
				case FORMHYBRID_METHOD_GET:
					$value = \Input::get($name);
				break;
				case FORMHYBRID_METHOD_POST:
					$value = \Input::post($name);
				break;
			}
		}
		
		// Trigger the load_callback
		$dc = new DC_Hybrid($this->strTable, $this->objModel);
		
		if (is_array($arrData['load_callback']))
		{
			foreach ($arrData['load_callback'] as $callback)
			{
				$this->import($callback[0]);
				
				$value = $this->$callback[0]->$callback[1]($value, $dc);
			}
		}
		
		// Convert date formats into timestamps
		if ($value != '' && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
		{
			$objDate = new \Date($value, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
			$value = $objDate->tstamp;
		}
		
		// prevent name for GET and submit widget, otherwise url will have submit name in
		if($this->strMethod == FORMHYBRID_METHOD_GET && $arrData['inputType'] == 'submit')
		{
			$name = '';
		}
		
		$arrWidget = \Widget::getAttributesFromDca($arrData, $name, $value, $name);
		$objWidget = new $strClass($arrWidget);
		
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
			$objWidget->validate();
		
			if($objWidget->hasErrors())
			{
				$this->doNotSubmit = true;
			}
			elseif ($objWidget->submitInput())
			{
				if($this->strMethod == FORMHYBRID_METHOD_GET)
				{
					$objWidget->value = $value;
				}
				
				$value = $objWidget->value;
		
				// Sort array by key (fix for JavaScript wizards)
				if (is_array($value))
				{
					sort($value);
					$value = serialize($value);
				}
		
				$dc = new DC_Hybrid($this->strTable, $this->objModel);
		
				// Trigger the save_callback
				if (is_array($arrData['save_callback']))
				{
					foreach ($arrData['save_callback'] as $callback)
					{
						$this->import($callback[0]);
						$value = $this->$callback[0]->$callback[1]($value, $dc);
					}
				}
		
				$this->objModel->{$name} = $value;
			}
		}
		
		return $objWidget;
	}
	
	protected function generateFields()
	{
		$this->isSubmitted = \Input::post('FORM_SUBMIT') == $this->strFormId;
		
		foreach($this->arrEditable as $name)
		{
			if(!in_array($name, array_keys($this->dc['fields']))) continue;
			
			$this->arrFields[$name] = $this->generateField($name, $this->dc['fields'][$name]);
		}
		
		// add submit button if not configured in dca
		if(!$this->hasSubmit)
		{
			$this->generateSubmitField();
		}
		
		// trigger onsubmit callbacks
		if($this->isSubmitted && !$this->doNotSubmit)
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
	
	abstract protected function compile();
	
	abstract protected function onSubmitCallback(\DataContainer $dc);
}