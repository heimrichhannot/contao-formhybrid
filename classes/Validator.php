<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @package FutureSAX
 * @author  Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;


class Validator
{
    /**
     * check if options are available and find current value in list
     * if not found: do not set the value, otherwise potential sql injection vulnerability
     * TODO: try to check against options_callbacks as well!!!
     *
     * @param $varValue
     * @param $arrData
     *
     * @return bool
     */
    public static function isValidOption($varValue, &$arrData, $objDc = null)
    {
        $arrOptions = FormHelper::getFieldOptions($arrData, $objDc);

        if (empty($arrOptions)) {
            $arrOptions = [0, 1];
        }

        $blnIsAssociative = (($arrData['eval']['isAssociative'] ?? false) || array_is_assoc($arrOptions));
        $intFounds        = 0;

        // add empty option if includeBlankOption is active
        if ($arrData['eval']['includeBlankOption']) {
            $arrOptions[] = 0;
        }

        foreach ($arrOptions as $k => $v) {
            if (!is_array($v)) {
                $checkValue = $blnIsAssociative ? $k : $v;

                if (is_array($varValue)) {
                    if (in_array(urldecode($checkValue), array_map('urldecode', $varValue))) {
                        $intFounds++;
                    }
                } elseif (urldecode($checkValue) == urldecode($varValue)) {
                    $intFounds++;
                    break;
                }

                continue;
            }

            $blnIsAssoc = array_is_assoc($v);

            foreach ($v as $kk => $vv) {
                $checkValue = $blnIsAssoc ? $kk : $vv;

                if (urldecode($checkValue) == urldecode($varValue)) {
                    $intFounds++;
                    break;
                }
            }
        }

        if (is_array($varValue) && $intFounds < count($varValue) || !is_array($varValue) && $intFounds < 1) {
            return false;
        }

        return true;
    }

}
