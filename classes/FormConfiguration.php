<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @package Tag der Deutschen Einheit
 * @author  Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;


use HeimrichHannot\Haste\Util\Arrays;
use HeimrichHannot\Haste\Util\StringUtil;
use HeimrichHannot\Haste\Util\Url;

class FormConfiguration
{
	/**
	 * The transformed data
	 * @varT
	 */
	protected $arrData = array();

	/**
	 * The raw config data
	 * @var
	 */
	protected $varConfig = array();

	protected $arrDefaults = array
	(
		''
	);

	public function __construct($varConfig)
	{
		if($varConfig instanceof \Module)
		{
			$this->varConfig = Arrays::objectToArray($varConfig);
		}
		else if ($varConfig instanceof \Model)
		{
			$this->varConfig = $varConfig->row();
		}
		else if ($varConfig instanceof \Model\Collection)
		{
			$this->varConfig = $varConfig->current()->row();
		}
		else if (is_array($varConfig))
		{
			$this->varConfig = $varConfig;
		}

		if(is_array($this->varConfig))
		{
			$this->transform();
		}
	}

	protected function transform()
	{
		// leave non-formhybrid data inside config
		$this->arrData = Arrays::filterOutByPrefixes($this->varConfig, array('formHybrid'));

		// transform formhybrid prefixed attributes
		$arrData = Arrays::filterByPrefixes($this->varConfig, array('formHybrid'));
		
		foreach ($arrData as $strKey => $varValue)
		{
			$this->{$strKey} = $varValue;
		}
	}

	/**
	 * Get config value from transformed arrData and add logic to modify the value here
	 * @param $strKey
	 *
	 * @return mixed|string
	 */
	public function __get($strKey)
	{
		$varValue = $this->arrData[$strKey];

		switch ($strKey)
		{
			case 'strAction':
				if($varValue && ($objActionPage = \PageModel::findWithDetails($varValue)) !== null)
				{
					$varValue = \Controller::generateFrontendUrl($objActionPage->row(), null, null, true);
				}
				else
				{
					$varValue = Url::removeQueryString(array('file'), \Environment::get('uri'));
				}

				// add hash
				if($this->addHashToAction)
				{
					$varValue .= '#' . $this->strFormId;
				}
			break;
			case 'arrDefaultValues':
				$varValue = FormHelper::getAssocMultiColumnWizardList($varValue, 'field');
			break;
		}

		return $varValue;
	}


	/**
	 * Set config data and modify keys that needs to be mapped to DC_Hybrid attributes
	 * no logic should be done here
	 *
	 * @param $strKey
	 * @param $varValue
	 */
	public function __set($strKey, $varValue)
	{
		$strKey = static::getKey($strKey);

		switch ($strKey)
		{
			case 'action':
				$strKey = 'strAction';
			break;
			case 'table':
			case 'dataContainer': // TODO : rename formHybridDataContainer to formHybridTable
				$strKey = 'strTable';
			break;
			case 'editable':
				$strKey = 'arrEditable';
				$varValue = deserialize($varValue, true);
			break;
			case 'editableRequired':
				$strKey = 'arrRequired';
				$varValue = deserialize($varValue, true);
			break;
			case 'permanentFields':
				$strKey = 'arrPermanentFields';
				$varValue = deserialize($varValue, true);
				break;
			case 'subPalettes':
				$strKey = 'arrSubPalettes';
				$varValue = deserialize($varValue, true);
			break;
			case 'template':
				$strKey = 'strTemplate';
			break;
			case 'defaultValues':
				$strKey = 'arrDefaultValues';
				$varValue = deserialize($varValue, true);
			break;
			case 'submitValues':
				$strKey = 'arrSubmitValues';
				$varValue = deserialize($varValue, true);
			break;
			case 'startTemplate':
				$strKey = 'strTemplateStart';
			break;
			case 'stopTemplate':
				$strKey = 'strTemplateStop';
			break;
			case 'customSubTemplates':
				$strKey = 'useCustomSubTemplates';
			break;
			case 'cssClass':
				$strKey = 'strClass';
			break;
			case 'fieldDependentRedirectConditions':
				$varValue = deserialize($varValue, true);
			break;
		}

		$this->arrData[$strKey] = deserialize($varValue);
	}

	/**
	 * Return the configuration data and trigger __get magic getter
	 * to add custom logic
	 * @return array
	 */
	public function getData()
	{
		$arrData = array();

		foreach ($this->arrData as $strKey => $varValue)
		{
			$arrData[$strKey] = $this->{$strKey};
		}
		return $arrData;
	}

	/**
	 * Fallback function that returns the module from a given id
	 *
	 * @return \ModuleModel | null if no id
	 */
	public function getModule()
	{
		if(!$this->id || ($objModule = \ModuleModel::findByPk($this->id)) === null)
		{
			return null;
		}

		return $objModule;
	}

	/**
	 * Return the internal attribute key, without formHybrid prefix
	 * @param $strKey
	 *
	 * @return string
	 */
	public static function getKey($strKey)
	{
		return lcfirst(preg_replace('/formHybrid/','',$strKey, 1));
	}

	/**
	 * Check if the key starts with 'formHybrid'
	 * 
	 * @param $strKey
	 *
	 * @return bool
	 */
	public static function isLegacyKey($strKey)
	{
		return StringUtil::startsWith($strKey, 'formHybrid');
	}
}