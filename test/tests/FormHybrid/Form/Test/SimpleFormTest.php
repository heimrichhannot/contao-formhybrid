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
		$this->objModule = new \ModuleModel();
		$this->objModule->id = 99999999;
		$this->objModule->formHybridDataContainer = 'tl_news';
		$this->objModule->formHybridPalette = 'default';
		$this->objModule->formHybridEditable = serialize(array('headline'));

		$this->objForm = new SimpleTestForm($this->objModule);
	}

	public function testForm()
	{
		$this->request->setMethod('POST')->setPost(array( 'FORM_SUBMIT' => $this->objForm->getFormId() ));

		$GLOBALS['_POST']['FORM_SUBMIT'] = $this->objForm->getFormId();
//		\Input::setPost('FORM_SUBMIT', $this->objForm->getFormId());
		\Input::setPost('headline', 'FOO');
		$this->objForm->generate();
	}
}

class SimpleTestForm extends \HeimrichHannot\FormHybrid\Form
{
	protected $strMethod = FORMHYBRID_METHOD_POST;

	protected function compile()
	{
		// TODO: Implement compile() method.
	}

}