<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Haste\Util\FormSubmission;

class FormReadonlyField extends \Widget
{
	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'form_readonly';

	/**
	 * The CSS class prefix
	 *
	 * @var string
	 */
	protected $strPrefix = 'widget widget-readonly';

	protected static $arrSkipTypes = ['hidden', 'submit'];

	protected $arrAttributesData = [];

	/**
	 * Initialize the object
	 *
	 * @param array $arrAttributes An optional attributes array
	 */
	public function __construct($arrAttributes=null)
	{
		parent::__construct($arrAttributes);

		$this->arrAttributesData = $arrAttributes;

	}

	/**
	 * Return an object property
	 *
	 * @param string $strKey The property name
	 *
	 * @return string The property value
	 */
	public function __get($strKey)
	{
		switch($strKey)
		{
			case 'required':
			case 'mandatory':
				return false;
		}

		return parent::__get($strKey);
	}

	/**
	 * Do not validate
	 */
	public function validate()
	{
		return;
	}

	/**
	 * Should only return the field value
	 * @return string
	 */
	public function generate()
	{
		$arrData = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strName];
        $this->objDca->strField = $this->strName;
        $this->objDca->strTable = $this->strTable;
		$value = FormSubmission::prepareSpecialValueForPrint($this->varValue, $arrData, $this->strTable, $this->objDca, $this->activeRecord);

		switch($this->type)
		{
		    // xss protection for multifileupload within presentation not required, xss protection done within multifileupload
			case 'multifileupload':
				if ($this->fieldType == 'checkbox')
				{
                    return '<ul class="download-list">' . implode('', array_map(function($val) {
						return '<li>{{download::' . str_replace(\Environment::get('url') . '/', '', $val) . '}}</li>';
					}, explode(', ', $value))) . '</ul>';
					break;
				}

                return '{{download::' . str_replace(\Environment::get('url') . '/', '', $value) . '}}';
			break;
		}

		$value = class_exists('Contao\StringUtil') ? \StringUtil::decodeEntities(\Controller::replaceInsertTags($value)) : \StringUtil::decodeEntities(\Controller::replaceInsertTags($value));

		if(!$value)
		{
			$value = '-';
		}

		return FormHelper::escapeAllEntities($this->strTable, $this->strName, $value);
	}

	/**
	 * Parse the template file and return it as string
	 *
	 * @param array $arrAttributes An optional attributes array
	 *
	 * @return string The template markup
	 */
	public function parse($arrAttributes = null)
	{
		if (in_array($this->type, static::$arrSkipTypes))
		{
			return '';
		}


		$this->addAttributes($arrAttributes);

		$this->Template = new \FrontendTemplate($this->strTemplate);
		$this->Template->setData($this->arrAttributesData);

		$this->Template->label = $this->generateLabel();
		$this->Template->value = $this->generate();
		$this->Template->sub = $this->sub;

		return $this->Template->parse();
	}
	
}
