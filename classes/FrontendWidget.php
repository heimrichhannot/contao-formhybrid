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
    public function __get($key)
    {
        switch ($key)
        {
            case 'value':
                if ($this->arrConfiguration['nullIfEmpty'] && $this->varValue == '')
                {
                    return null;
                }

                return $this->varValue;
                break;
            default:
                return parent::__get($key);
                break;
        }
    }

    public function __set($key, $value)
    {
        switch ($key)
        {
            case 'value':
                $this->varValue = deserialize($value);

                // Decrypt the value if it is encrypted
                if ($this->arrConfiguration['encrypt'])
                {
                    $this->varValue = \Encryption::decrypt($this->varValue);
                }
                break;
            default:
                parent::__set($key, $value);
                break;
        }
    }


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

            if(false === $objWidget->decodeEntities){
                $_POST[$objWidget->name] = static::decodeEntities($_POST[$objWidget->name]);
            }

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
    
        if ($objWidget->allowHtml)
        {
            $varValue = Request::cleanHtml($varValue, $objWidget->decodeEntities, true, $objWidget->allowedTags ?: \Config::get('allowedTags'));
        }

        $objWidget->value = $varValue;

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
	* Recursivly decode value entities
	*
	* @param array|string $varValue The value
	*
	* @return array|string The value with decoded entities
	*/
	protected static function decodeEntities($varValue)
    {
        if (is_array($varValue))
        {
            foreach ($varValue as $i => $childValue)
            {
                $varValue[$i] = static::decodeEntities($childValue);
            }

            return $varValue;
        }

        return is_string($varValue) ? html_entity_decode($varValue) : '';
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