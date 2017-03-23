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

class FormHybridRunOnce extends \Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function run()
	{
		if(class_exists('\HeimrichHannot\FormHybrid\DatabaseUpdater'))
		{
			\HeimrichHannot\FormHybrid\DatabaseUpdater::run();
		}
	}
}

$objRunOnce = new \HeimrichHannot\FormHybrid\FormHybridRunOnce();
$objRunOnce->run();