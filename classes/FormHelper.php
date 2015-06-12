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


class FormHelper extends \System
{

	public static function replaceFormDataTags($strBuffer, $arrMailData)
	{
		global $objPage;

		// Preserve insert tags
		if (\Config::get('disableInsertTags'))
		{
			return \String::restoreBasicEntities($strBuffer);
		}

		$tags = preg_split('/\{\{(([^\{\}]*|(?R))*)\}\}/', $strBuffer, -1, PREG_SPLIT_DELIM_CAPTURE);

		$strBuffer = '';

		for ($_rit=0, $_cnt=count($tags); $_rit<$_cnt; $_rit+=3) {
			$strBuffer .= $tags[$_rit];
			$strTag = $tags[$_rit + 1];

			// Skip empty tags
			if ($strTag == '') {
				continue;
			}

			// Run the replacement again if there are more tags
			if (strpos($strTag, '{{') !== false) {
				$strTag = static::replaceFormDataTags($strTag, $arrMailData);
			}

			$flags = explode('|', $strTag);
			$tag = array_shift($flags);
			$elements = explode('::', $tag);

			// Replace the tag
			switch (strtolower($elements[0])) {
				// form
				case 'form':
					if ($elements[1] == '' || !isset($arrMailData[$elements[1]]['output'])) continue;

					$strTag = $arrMailData[$elements[1]]['output'];
				break;
				case 'form_value':
					if ($elements[1] == '' || !isset($arrMailData[$elements[1]]['value'])) continue;

					$strTag = $arrMailData[$elements[1]]['value'];
				break;
				default:
					$strTag = '{{' . $tag . '}}';
			}

			$strBuffer .= $strTag;
		}

		return \String::restoreBasicEntities($strBuffer);
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
			$value = \Date::parse($objPage->dateFormat, $value);
		}
		elseif ($rgxp == 'time')
		{
			$value = \Date::parse($objPage->timeFormat, $value);
		}
		elseif ($rgxp == 'datim')
		{
			$value = \Date::parse($objPage->datimFormat, $value);
		}
		elseif (is_array($value))
		{
			$value = static::flattenArray($value);

			$value = array_filter($value); // remove empty elements

			$value = implode(', ', $value);
		}
		elseif (is_array($opts) && array_is_assoc($opts))
		{
			$value = isset($opts[$value]) ? $opts[$value] : $value;
		}
		elseif (is_array($rfrc))
		{
			$value = isset($rfrc[$value]) ? ((is_array($rfrc[$value])) ? $rfrc[$value][0] : $rfrc[$value]) : $value;
		}
		else
		{
			$value = $value;
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
}