<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package anwaltverein
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;


use HeimrichHannot\HastePlus\Files;

class FormHelper extends \System
{
	/**
	 * Find and return a $_GET variable
	 *
	 * @param string $strKey The variable name
	 * @param boolean $decodeEntities If true, html entities will be decoded
	 *
	 * @return mixed The variable value
	 */
	public static function getGet($strKey, $decodeEntities = false)
	{
		$strMethod = 'get';

		// Support arrays (thanks to Andreas Schempp)
		$arrParts = explode('[', str_replace(']', '', $strKey));

		if (!empty($arrParts))
		{
			$varValue = \Input::$strMethod(array_shift($arrParts), $decodeEntities);

			foreach($arrParts as $part)
			{
				if (!is_array($varValue))
				{
					break;
				}

				$varValue = $varValue[$part];
			}

			return $varValue;
		}

		return \Input::$strMethod($strKey, $decodeEntities);
	}

	/**
	 * Find and return a $_POST variable
	 *
	 * @param string $strKey The variable name
	 * @param boolean $decodeEntities If true, html entities will be decoded
	 * @param boolean $allowHtml If true, html will be allowed
	 * @param boolean $preserveTags If true, html tags will be preserved
	 *
	 * @return mixed The variable value
	 */
	public static function getPost($strKey, $decodeEntities = false, $allowHtml = false, $preserveTags = false)
	{
		$strMethod = $allowHtml ? 'postHtml' : 'post';

		if ($preserveTags)
		{
			$strMethod = 'postRaw';
		}

		// Support arrays (thanks to Andreas Schempp)
		$arrParts = explode('[', str_replace(']', '', $strKey));

		if (!empty($arrParts))
		{
			$varValue = \Input::$strMethod(array_shift($arrParts), $decodeEntities);

			foreach($arrParts as $part)
			{
				if (!is_array($varValue))
				{
					break;
				}

				$varValue = $varValue[$part];
			}

			return $varValue;
		}

		return \Input::$strMethod($strKey, $decodeEntities);
	}


	public static function transformSpecialValues($varValue, $arrData, $objWidget = null)
	{
		// Convert date formats into timestamps
		if ($varValue != '' && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim'))) {
			$objDate  = new \Date($varValue, \Config::get($arrData['eval']['rgxp'] . 'Format'));
			$varValue = $objDate->tstamp;
		}

		if ($arrData['eval']['multiple'] && isset($arrData['eval']['csv'])) {
			$varValue = implode($arrData['eval']['csv'], deserialize($varValue, true));
		}

		return $varValue;
	}

	/**
	 * Return the locale string
	 * @return string
	 */
	public static function getLocaleString()
	{
		return
			'var Formhybrid={'
			. 'lang:{'
			. 'close:"' . $GLOBALS['TL_LANG']['MSC']['close'] . '",'
			. 'collapse:"' . $GLOBALS['TL_LANG']['MSC']['collapseNode'] . '",'
			. 'expand:"' . $GLOBALS['TL_LANG']['MSC']['expandNode'] . '",'
			. 'loading:"' . $GLOBALS['TL_LANG']['MSC']['loadingData'] . '",'
			. 'apply:"' . $GLOBALS['TL_LANG']['MSC']['apply'] . '",'
			. 'picker:"' . $GLOBALS['TL_LANG']['MSC']['pickerNoSelection'] . '"'
			. '},'
			. 'script_url:"' . TL_ASSETS_URL . '",'
			. 'path:"' . TL_PATH . '",'
			. 'request_token:"' . REQUEST_TOKEN . '",'
			. 'referer_id:"' . TL_REFERER_ID . '"'
			. '};';
	}



	public static function getAssocMultiColumnWizardList(array $arrValues, $strKey, $strValue = '')
	{
		$arrReturn = array();

		foreach($arrValues as $arrValue)
		{
			if(!isset($arrValue[$strKey]) && !isset($arrValue[$strValue])) continue;

			$varValue = $arrValue[$strValue];

			if(empty($strValue))
			{
				$varValue = $arrValue;
				unset($varValue[$strKey]);

			}

			$arrReturn[$arrValue[$strKey]] = $varValue;
		}

		return $arrReturn;
	}

	public static function getPaletteFields($strTable, $strPalette)
	{
		\Controller::loadDataContainer($strTable);

		$boxes = trimsplit(';', $strPalette);
		$legends = array();

		if (!empty($boxes))
		{
			foreach ($boxes as $k=>$v)
			{
				$eCount = 1;
				$boxes[$k] = trimsplit(',', $v);

				foreach ($boxes[$k] as $kk=>$vv)
				{
					if (preg_match('/^\[.*\]$/', $vv))
					{
						++$eCount;
						continue;
					}

					if (preg_match('/^\{.*\}$/', $vv))
					{
						$legends[$k] = substr($vv, 1, -1);
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (count($boxes[$k]) < $eCount)
				{
					unset($boxes[$k]);
				}
			}
		}

		$arrFields = array();

		if(!is_array($boxes)) return $arrFields;

		// flatten
		array_walk_recursive($boxes, function($a) use (&$arrFields) { $arrFields[] = $a; });

		// remove empty values
		return array_filter($arrFields);
	}

	public static function replaceFormDataTags($strBuffer, $arrMailData)
	{
		// Preserve insert tags
		if (\Config::get('disableInsertTags'))
		{
			return \String::restoreBasicEntities($strBuffer);
		}

		$tags = preg_split('/\{\{(([^\{\}]*|(?R))*)\}\}/', $strBuffer, -1, PREG_SPLIT_DELIM_CAPTURE);

		$strBuffer = '';
		$runEval = false;

		for ($_rit=0, $_cnt=count($tags); $_rit<$_cnt; $_rit+=3) {
			$strBuffer .= $tags[$_rit];
			$strTag = $tags[$_rit + 1];

			// Skip empty tags
			if ($strTag == '') {
				continue;
			}

			$flags = explode('|', $strTag);
			$tag = array_shift($flags);
			$elements = explode('::', $tag);

			// Run the replacement again if there are more tags and not if/elseif condition
			if (strpos($strTag, '{{') !== false)
			{
				$strTag = static::replaceFormDataTags($strTag, $arrMailData);
			}

			// Replace the tag
			switch (strtolower($elements[0])) {
				case (strrpos($elements[0], 'if', -strlen($elements[0])) !== FALSE):
					$strTag = preg_replace('/if (.*)/i', '<?php if ($1): ?>', $strTag);
					$runEval = true;
				break;
				case (strrpos($elements[0], 'elseif', -strlen($elements[0])) !== FALSE):
					$strTag = preg_replace('/elseif (.*)/i', '<?php elseif ($1): ?>', $strTag);
					$runEval = true;
				break;
				case 'else':
					$strTag = '<?php else: ?>';
				break;
				case 'endif':
					$strTag = '<?php endif; ?>';
				break;
				// form
				case 'form':
					if ($elements[1] == '' || !isset($arrMailData[$elements[1]]['output']))
					{
						$strTag = '';
						continue;
					}

					$strTag = $arrMailData[$elements[1]]['output'];
				break;
				case 'form_value':
					if ($elements[1] == '' || !isset($arrMailData[$elements[1]]['value']))
					{
						$strTag = '';
						continue;
					}

					$strTag = $arrMailData[$elements[1]]['value'];
				break;
				case 'form_submission':
					if ($elements[1] == '' || !isset($arrMailData[$elements[1]]['submission']))
					{
						$strTag = '';
						continue;
					}

					$strTag = rtrim($arrMailData[$elements[1]]['submission'], "\n");
				break;
				// restore inserttag for \Controller::replaceInsertTags()
				default:
					$strTag = '{{' . $tag . '}}';
			}

			$strBuffer .= $strTag;
		}

		if($runEval)
		{
			$strBuffer = static::evalConditionTags($strBuffer);
		}

		
		return \String::restoreBasicEntities($strBuffer);
	}

	public static function evalConditionTags($strBuffer)
	{
		if (!strlen($strBuffer))
		{
			return;
		}

		$strReturn = str_replace('?><br />', '?>', $strBuffer);

		// Eval the code
		ob_start();
		$blnEval = eval("?>" . $strReturn);
		$strReturn = ob_get_contents();
		ob_end_clean();

		// Throw an exception if there is an eval() error
		if ($blnEval === false)
		{
			throw new \Exception("Error eval() in Formhelper::evalConditionTags ($strReturn)");
		}

		// Return the evaled code
		return $strReturn;
	}


	public static function getFormatedValueByDca($value, $arrData, $dc)
	{
		global $objPage;
		
		$value = deserialize($value);
		$rgxp = $arrData['eval']['rgxp'];
		$opts = $arrData['options'];
		$rfrc = $arrData['reference'];

		$rgxp = $arrData['eval']['rgxp'];

		// Call the options_callback to get the formated value
		if ((is_array($arrData['options_callback']) || is_callable($arrData['options_callback'])) && !$arrData['reference'])
		{
			if (is_array($arrData['options_callback']))
			{
				$strClass = $arrData['options_callback'][0];
				$strMethod = $arrData['options_callback'][1];

				$objInstance = \Controller::importStatic($strClass);

				$options_callback = $objInstance->$strMethod($dc);
			}
			elseif (is_callable($arrData['options_callback']))
			{
				$options_callback = $arrData['options_callback']($dc);
			}

			$arrOptions = !is_array($value) ? array($value) : $value;

			$value = array_intersect_key($options_callback, array_flip($arrOptions));
		}

		if ($rgxp == 'date')
		{
			$value = \Date::parse(\Config::get('dateFormat'), $value);
		}
		elseif ($rgxp == 'time')
		{
			$value = \Date::parse(\Config::get('timeFormat'), $value);
		}
		elseif ($rgxp == 'datim')
		{
			$value = \Date::parse(\Config::get('datimFormat'), $value);
		}
		elseif (is_array($value))
		{
			$value = static::flattenArray($value);

			$value = array_filter($value); // remove empty elements

			$value = implode(', ', array_map(function($value) use ($rfrc) {
				if (is_array($rfrc))
				{
					return isset($rfrc[$value]) ? ((is_array($rfrc[$value])) ? $rfrc[$value][0] : $rfrc[$value]) : $value;
				}
				else
					return $value;
			}, $value));
		}
		elseif (is_array($opts) && array_is_assoc($opts))
		{
			$value = isset($opts[$value]) ? $opts[$value] : $value;
		}
		elseif (is_array($rfrc))
		{
			$value = isset($rfrc[$value]) ? ((is_array($rfrc[$value])) ? $rfrc[$value][0] : $rfrc[$value]) : $value;
		}
		elseif ($arrData['inputType'] == 'fileTree')
		{
			if ($arrData['eval']['multiple'] && is_array($value))
			{
				$value = array_map(function($val) {
					$strPath = Files::getPathFromUuid($val);
					return $strPath ?: $val;
				}, $value);
			}
			else
			{
				$strPath = Files::getPathFromUuid($value);
				$value = $strPath ?: $value;
			}
		}
		elseif (\Validator::isBinaryUuid($value))
		{
			$value = \String::binToUuid($value);
		}

		// Convert special characters (see #1890)
		return specialchars($value);
	}

	public static function flattenArray(array $array)
	{
		$return = array();
		array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
		return $return;
	}

	/**
	 * Gets the available subpalettes and subpalettes with options from the palette
	 *
	 * @param array $arrSubPalettes
	 * @param array $arrFieldsInPalette
	 * @param \DataContainer $dc
	 * @return array
	 */
	public static function getFilteredSubPalettes(array $arrSubPalettes, array $arrFieldsInPalette, \DataContainer $dc=null)
	{
		$arrFilteredSubPalettes = array();

		foreach ($arrFieldsInPalette as $strField)
		{
			if (in_array($strField, $arrSubPalettes))
			{
				$arrFilteredSubPalettes[] = $strField;
				continue;
			}

			$arrField = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['fields'][$strField];

			if (is_array($arrField['options']) && !empty($arrField['options']))
			{
				foreach ($arrField['options'] as $strOption)
				{
					$strSubPaletteName = $strField . '_' . $strOption;
					if (in_array($strSubPaletteName, $arrSubPalettes))
					{
						$arrFilteredSubPalettes[] = $strSubPaletteName;
					}
				}
				continue;
			}

			if (is_array($arrField['options_callback']) && !empty($arrField['options_callback']) && is_callable($arrField['options_callback']))
			{
				$strClass = $arrField['options_callback'][0];
				$strMethod = $arrField['options_callback'][1];
				$objInstance = \Controller::importStatic($strClass);
				$arrOptions = $objInstance->$strMethod($dc);

				foreach ($arrOptions as $strOption)
				{
					$strSubPaletteName = $strField . '_' . $strOption;
					if (in_array($strSubPaletteName, $arrSubPalettes))
					{
						$arrFilteredSubPalettes[] = $strSubPaletteName;
					}
				}
				continue;
			}
		}
		return $arrFilteredSubPalettes;
	}

}
