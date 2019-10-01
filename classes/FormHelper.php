<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package anwaltverein
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Request\Request;

class FormHelper extends \System
{
    public static function getFormId($strTable, $intModule, $intId = null, $blnAddEntityId = true, $suffix = '')
    {
        $arrValues = [];

        $arrValues[] = $strTable;
        $arrValues[] = $intModule;

        if ($intId > 0 && $blnAddEntityId)
        {
            $arrValues[] = $intId;
        }

        if ($suffix) {
            $arrValues[] = $suffix;
        }

        return implode('_', $arrValues);
    }

    public static function replaceInsertTags($varValue, $blnCache = true)
    {
        if (is_array($varValue))
        {
            foreach ($varValue as $key => $value)
            {
                $varValue[$key] = static::replaceInsertTags($value, $blnCache);
            }

            return $varValue;
        }

        return \Controller::replaceInsertTags($varValue, $blnCache);
    }

    public static function htmlEntityDecode($varValue)
    {
        if (is_array($varValue))
        {
            foreach ($varValue as $key => $value)
            {
                $varValue[$key] = static::htmlEntityDecode($value);
            }

            return $varValue;
        }

        return html_entity_decode($varValue, ENT_QUOTES, \Config::get('characterSet'));
    }

    public static function getFieldOptions($arrData, $objDc = null)
    {
        $arrOptions = [];

        if (is_array($arrData['options']))
        {
            $arrOptions = $arrData['options'];
        }

        if ($objDc !== null && empty($arrOptions)
            && (is_array($arrData['options_callback']) || is_callable($arrData['options_callback']))
        )
        {
            $arrCallback = [];

            if (is_array($arrData['options_callback']))
            {
                $strClass    = $arrData['options_callback'][0];
                $strMethod   = $arrData['options_callback'][1];
                $objInstance = \Controller::importStatic($strClass);

                try
                {
                    $arrCallback = @$objInstance->{$strMethod}($objDc);
                } catch (\Exception $e)
                {
                    \System::log("$strClass::$strMethod raised an Exception: $e->getMessage()", __METHOD__, TL_ERROR);
                }
            }
            elseif (is_callable($arrData['options_callback']))
            {
                try
                {
                    $arrCallback = @$arrData['options_callback']($objDc);
                } catch (\Exception $e)
                {
                    $strCallback = serialize($arrData['options_callback']);
                    \System::log("$strCallback raised an Exception: $e->getMessage()", __METHOD__, TL_ERROR);
                }
            }

            if (is_array($arrCallback))
            {
                $arrOptions = $arrCallback;
            }
        }

        return $arrOptions;
    }

    /**
     * XSS clean values
     * @deprecated since 2.8, use HeimrichHannot\Request\Request::xssClean()
     *
     * @param      $varValue
     * @param bool $tidy If true, close tags without a closing tag
     *
     * @return mixed $varValue xssClean
     */
    public static function xssClean($varValue, $tidy = false)
    {
        return Request::xssClean($varValue, true, $tidy);
    }

    public static function escapeAllEntities($strDca, $strField, $varValue)
    {
        \Controller::loadDataContainer($strDca);

        $arrData = $GLOBALS['TL_DCA'][$strDca]['fields'][$strField];

        $strPreservedTags = isset($arrData['eval']['allowedTags']) ? $arrData['eval']['allowedTags'] : \Config::get('allowedTags');

        if($arrData['eval']['allowHtml'] || strlen($arrData['eval']['rte']) || $arrData['eval']['preserveTags'])
        {
            // always decode entities if HTML is allowed
            $varValue = Request::cleanHtml($varValue, true, true, $strPreservedTags);
        }
        else if(is_array($arrData['options']) || isset($arrData['options_callback']) || isset($arrData['foreignKey']))
        {
            // options should not be strict cleaned, as they might contain html tags like <strong>
            $varValue = Request::cleanHtml($varValue, true, true, $strPreservedTags);
        }
        else
        {
            $varValue = Request::clean($varValue, $arrData['eval']['decodeEntities'], true);
        }

        return $varValue;
    }

    /**
     * Find and return a $_GET variable
     *
     * @deprecated since 2.8, use HeimrichHannot\Request\Request::getGet()
     *
     * @param string  $strKey         The variable name
     * @param boolean $decodeEntities If true, html entities will be decoded
     *
     * @return mixed The variable value2
     */
    public static function getGet($strKey, $decodeEntities = false)
    {
        return Request::getGet($strKey, $decodeEntities);
    }

    /**
     * Find and return a $_POST variable
     *
     * @deprecated since 2.8, use HeimrichHannot\Request\Request::getPost/getPostHtml/getPostRaw methods
     *
     * @param string  $strKey         The variable name
     * @param boolean $decodeEntities If true, html entities will be decoded
     * @param boolean $allowHtml      If true, html will be allowed
     * @param boolean $preserveTags   If true, html tags will be preserved
     *
     * @return mixed The variable value
     */
    public static function getPost($strKey, $decodeEntities = false, $allowHtml = false, $preserveTags = false)
    {
        if ($preserveTags)
        {
            return Request::getPostRaw($strKey);
        }
        else if ($allowHtml)
        {
            return Request::getPostHtml($strKey, $decodeEntities);
        }

        return Request::getPost($strKey, $decodeEntities);
    }


    /**
     * Return the locale string
     *
     * @return string
     */
    public static function getLocaleString()
    {
        return 'var Formhybrid={' . 'lang:{' . 'close:"' . $GLOBALS['TL_LANG']['MSC']['close'] . '",' . 'collapse:"' . $GLOBALS['TL_LANG']['MSC']['collapseNode'] . '",'
               . 'expand:"' . $GLOBALS['TL_LANG']['MSC']['expandNode'] . '",' . 'loading:"' . $GLOBALS['TL_LANG']['MSC']['loadingData'] . '",' . 'apply:"'
               . $GLOBALS['TL_LANG']['MSC']['apply'] . '",' . 'picker:"' . $GLOBALS['TL_LANG']['MSC']['pickerNoSelection'] . '"' . '},' . 'script_url:"' . TL_ASSETS_URL . '",'
               . 'path:"' . TL_PATH . '",' . 'request_token:"' . REQUEST_TOKEN . '",' . 'referer_id:"' . TL_REFERER_ID . '",' . 'scope:"' . FORMHYBRID_ACTION_SCOPE . '"' . '};';
    }


    public static function getAssocMultiColumnWizardList(array $arrValues, $strKey, $strValue = '')
    {
        $arrReturn = [];

        foreach ($arrValues as $arrValue)
        {
            if (!isset($arrValue[$strKey]) && !isset($arrValue[$strValue]))
            {
                continue;
            }

            $varValue = $arrValue[$strValue];

            if (empty($strValue))
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

        $boxes   = trimsplit(';', $strPalette);
        $legends = [];

        if (!empty($boxes))
        {
            foreach ($boxes as $k => $v)
            {
                $eCount    = 1;
                $boxes[$k] = trimsplit(',', $v);

                foreach ($boxes[$k] as $kk => $vv)
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

        $arrFields = [];

        if (!is_array($boxes))
        {
            return $arrFields;
        }

        // flatten
        array_walk_recursive(
            $boxes,
            function ($a) use (&$arrFields)
            {
                $arrFields[] = $a;
            }
        );

        // remove empty values
        return array_filter($arrFields);
    }

    public static function replaceFormDataTags($strBuffer, $arrMailData)
    {
        // Preserve insert tags
        if (\Config::get('disableInsertTags'))
        {
            return \StringUtil::restoreBasicEntities($strBuffer);
        }

        $tags = preg_split('/\{\{(([^\{\}]*|(?R))*)\}\}/', $strBuffer, -1, PREG_SPLIT_DELIM_CAPTURE);

        $strBuffer = '';
        $runEval   = false;

        for ($_rit = 0, $_cnt = count($tags); $_rit < $_cnt; $_rit += 3)
        {
            $strBuffer .= $tags[$_rit];
            $strTag = $tags[$_rit + 1];

            // Skip empty tags
            if ($strTag == '')
            {
                continue;
            }

            $flags    = explode('|', $strTag);
            $tag      = array_shift($flags);
            $elements = explode('::', $tag);

            // Run the replacement again if there are more tags and not if/elseif condition
            if (strpos($strTag, '{{') !== false)
            {
                $strTag = static::replaceFormDataTags($strTag, $arrMailData);
            }

            // Replace the tag
            switch (strtolower($elements[0]))
            {
                case (strrpos($elements[0], 'if', -strlen($elements[0])) !== false):
                    $strTag  = preg_replace('/if (.*)/i', '<?php if ($1): ?>', $strTag);
                    $runEval = true;
                    break;
                case (strrpos($elements[0], 'elseif', -strlen($elements[0])) !== false):
                    $strTag  = preg_replace('/elseif (.*)/i', '<?php elseif ($1): ?>', $strTag);
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
                        continue 2;
                    }

                    $strTag = $arrMailData[$elements[1]]['output'];
                    break;
                case 'form_value':
                    if ($elements[1] == '' || !isset($arrMailData[$elements[1]]['value']))
                    {
                        $strTag = '';
                        continue 2;
                    }

                    $strTag = $arrMailData[$elements[1]]['value'];
                    break;
                case 'form_submission':
                    if ($elements[1] == '' || !isset($arrMailData[$elements[1]]['submission']))
                    {
                        $strTag = '';
                        continue 2;
                    }

                    $strTag = rtrim($arrMailData[$elements[1]]['submission'], "\n");
                    break;
                // restore inserttag for \Controller::replaceInsertTags()
                default:
                    $strTag = '{{' . $tag . '}}';
            }

            $strBuffer .= $strTag;
        }

        if ($runEval)
        {
            $strBuffer = static::evalConditionTags($strBuffer);
        }


        return \StringUtil::restoreBasicEntities($strBuffer);
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
        $blnEval   = eval("?>" . $strReturn);
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

    /**
     * Gets the available subpalettes and subpalettes with options from the palette
     *
     * @param array          $arrSubPalettes
     * @param array          $arrFieldsInPalette
     * @param \DataContainer $dc
     *
     * @return array
     */
    public static function getFilteredSubPalettes(array $arrSubPalettes, array $arrFieldsInPalette, \DataContainer $dc = null)
    {
        $arrFilteredSubPalettes = [];

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
                $strClass    = $arrField['options_callback'][0];
                $strMethod   = $arrField['options_callback'][1];
                $objInstance = \Controller::importStatic($strClass);
                $arrOptions  = $objInstance->{$strMethod}($dc);

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

    public static function getEditableFields($strDataContainer)
    {
        if (!$strDataContainer)
        {
            return [];
        }

        \Controller::loadDataContainer($strDataContainer);

        if (!is_array($GLOBALS['TL_DCA'][$strDataContainer]))
        {
            return [];
        }

        $arrFields = [];

        if (is_array($GLOBALS['TL_DCA'][$strDataContainer]['fields']))
        {
            foreach ($GLOBALS['TL_DCA'][$strDataContainer]['fields'] as $strName => $arrData)
            {
                if (in_array($strName, ['id', 'tstamp']))
                {
                    continue;
                }

                $arrFields[] = $strName;
            }
        }

        return $arrFields;
    }

}
