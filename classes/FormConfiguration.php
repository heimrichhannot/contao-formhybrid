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

class FormConfiguration
{
	protected $arrData;

	protected $arrConfig;

	public function __construct($objConfig)
	{
		if($objConfig instanceof \Module)
		{
			$this->setData(Arrays::objectToArray($objConfig));
		}
		else if ($objConfig instanceof \Model)
		{
			$this->setData($objConfig->row());
		}
		else if ($objConfig instanceof \Model\Collection)
		{
			$this->setData($objConfig->current());
		}
	}

	private function setData(array $arrData)
	{

		foreach($arrData as $key => $value)
		{
			if(\HeimrichHannot\Haste\Util\StringUtil::startsWith($key, 'formHybrid'))
			{
				$key = lcfirst(str_replace('formHybrid', '', $key));
			}

			$this->{$key} = $value;
		}
	}
}