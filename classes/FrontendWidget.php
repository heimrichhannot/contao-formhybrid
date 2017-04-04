<?php

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Request\Request;


/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package formhybrid
 * @author  Dennis Patzer <d.patzer@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */
abstract class FrontendWidget extends \Widget
{
    /**
     * Validate the user input and set the value
     */
    public static function validateGetAndPost($objWidget, $strMethod, $strFormId, $arrData)
    {
        /** @var $objWidget \Widget */
        if ($strMethod == FORMHYBRID_METHOD_GET)
        {
            $varValue = $objWidget->validator(Request::getGet($objWidget->strName, $objWidget->decodeEntities, $objWidget->allowHtml));
        }
        else
        {
            // \Widget->validate retrieves submission data form post -> xss related stuff needs to be removed beforehands
            if ($objWidget->allowHtml)
            {
                // allowHtml = true if, eval preserveTags or rte or allowHtml is set true
                $_POST[$objWidget->name] = Request::getPostHtml($objWidget->name, $objWidget->decodeEntities, $objWidget->allowedTags, $objWidget->allowHtml);
            }
            else
            {
                $_POST[$objWidget->name] = Request::getPost($objWidget->name, $objWidget->decodeEntities);
            }

            $_POST[$objWidget->name] = html_entity_decode($_POST[$objWidget->name]);

            // Captcha needs no value, just simple validation
            if ($objWidget instanceof \FormCaptcha)
            {
                $varValue = '';
                $objWidget->validate();
            }
            else
            {
                $objWidget->validate();
                $varValue = $objWidget->value;
            }
        }

        $objWidget->varValue = $varValue;

        // HOOK: validate form field callback
        if (isset($GLOBALS['TL_HOOKS']['formHybridValidateFormField']) && is_array($GLOBALS['TL_HOOKS']['formHybridValidateFormField']))
        {
            foreach ($GLOBALS['TL_HOOKS']['formHybridValidateFormField'] as $callback)
            {
                $objClass = \Controller::importStatic($callback[0]);
                $objClass->{$callback[1]}($objWidget, $strFormId, $arrData);
            }
        }

        if ($objWidget->hasErrors())
        {
            $objWidget->class = 'error';
        }
    }

    /**
     * Check whether an option is checked
     *
     * @param array $arrOption The options array
     *
     * @return string The "checked" attribute or an empty string
     */
    protected function isChecked($arrOption)
    {
        if (empty($this->varValue) && empty($_GET) && $arrOption['default'])
        {
            return static::optionChecked(1, 1);
        }

        return static::optionChecked($arrOption['value'], $this->varValue);
    }


    /**
     * Check whether an option is selected
     *
     * @param array $arrOption The options array
     *
     * @return string The "selected" attribute or an empty string
     */
    protected function isSelected($arrOption)
    {
        if (empty($this->varValue) && empty($_GET) && $arrOption['default'])
        {
            return static::optionSelected(1, 1);
        }

        return static::optionSelected($arrOption['value'], $this->varValue);
    }

}
