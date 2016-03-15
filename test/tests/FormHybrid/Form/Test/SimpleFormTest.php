<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid\Test\Form\Test;

include_once __DIR__ . '/../../../../../classes/Form.php';
include_once __DIR__ . '/../../../../../../core/library/Contao/Input.php';

use HeimrichHannot\FormHybrid\Form;

class SimpleFormTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \Contao\ModuleModel
	 */
	protected $objModule;

	/**
	 * @var  \HeimrichHannot\FormHybrid\Form
	 */
	protected $objForm;

	public function setUp()
	{
	}

	public function testForm()
	{
	}
}
