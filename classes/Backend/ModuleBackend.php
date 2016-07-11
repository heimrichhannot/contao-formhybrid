<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid\Backend;


use HeimrichHannot\Haste\Util\Arrays;

class ModuleBackend extends \Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	public function getViewModes()
	{
		return array_values(Arrays::filterByPrefixes(get_defined_constants(), array('FORMHYBRID_VIEW_MODE_')));
	}

	public function getSubmitLabels()
	{
		$arrOptions = array();

		$arrTitles = $GLOBALS['TL_LANG']['MSC']['formhybrid']['submitLabels'];

		if(!is_array($arrTitles))
		{
			return $arrOptions;
		}

		foreach($arrTitles as $strKey => $strTitle)
		{
			if(is_array($strTitle))
			{
				$strTitle = $strTitle[0];
			}

			$arrOptions[$strKey] = $strTitle;
		}

		return $arrOptions;
	}

	public function getFormHybridStartTemplates()
	{
		return \Controller::getTemplateGroup('formhybridStart_');
	}

	public function getFormHybridStopTemplates()
	{
		return \Controller::getTemplateGroup('formhybridStop_');
	}

	public function getFormHybridTemplates()
	{
		return \Controller::getTemplateGroup('formhybrid_');
	}

	public function getFormHybridReadonlyTemplates()
	{
		return \Controller::getTemplateGroup('formhybridreadonly_');
	}
}