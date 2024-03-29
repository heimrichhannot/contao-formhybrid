<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package formhybrid
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;


use Contao\ModuleProxy;

class ContentFormHybridStop extends \ContentElement
{
	protected $strTemplate = 'ce_formhybrid_stop';

	public function generate()
	{
		if(TL_MODE == 'BE')
		{
			return '';
		}

		return parent::generate();
	}

	protected function compile()
	{
		$objContentStart = \Database::getInstance()->prepare("SELECT * FROM tl_content WHERE pid=? AND type=? ORDER BY sorting")->limit(1)->execute($this->pid, 'formhybridStart');

		if($objContentStart->numRows === 0) return;

		$objModule = \ModuleModel::findByPk($objContentStart->formhybridModule);

		if($objModule === null) return;

        $objModule->refresh();

		$strClass = \Module::findClass($objModule->type);


		// Return if the class does not exist
		if (!class_exists($strClass))
		{
			static::log('Module class "'.$strClass.'" (module "'.$objModule->type.'") does not exist', __METHOD__, TL_ERROR);
			return '';
		}

		$objArticle = \ArticleModel::findByPk($this->pid);

		if($objArticle === null) return;

        global $objPage;

        if (class_exists(ModuleProxy::class) && $strClass === ModuleProxy::class) {
            $objModule->renderStop = true;
            $objModule->startModule = $_SESSION[FormSession::FORMHYBRID_FORMSESSION_START_KEY][$objPage->id . '_' .  $objModule->formHybridDataContainer];
            $objModule = new $strClass($objModule, $objArticle->inColumn);
        } else {
            $objModule = new $strClass($objModule, $objArticle->inColumn);
            $objModule->renderStop = true;
            $objModule->startModule = $_SESSION[FormSession::FORMHYBRID_FORMSESSION_START_KEY][$objPage->id . '_' .  $objModule->formHybridDataContainer];
        }

		$this->Template->content = $objModule->generate();
	}
}