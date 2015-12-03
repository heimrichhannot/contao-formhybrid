<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package formhybrid
 * @author Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\FormHybrid;

use Contao\TagModel;

class TagsHelper extends \System
{


	public function saveTagsFromDefaults(\DataContainer $dc)
	{
		$arrDefaults = $dc->getDefaults();
		$arrDca = $dc->getDca();
		
		if(empty($arrDefaults)) return false;

		foreach($arrDefaults as $strField => $varDefault)
		{
			$arrData = $arrDca['fields'][$strField];

			if(!is_array($arrData) || $arrData['inputType'] != 'tag') continue;

			$arrValues = trimsplit(',', $varDefault);

			$blnTag = false;

			if(!is_array($arrValues) || empty($arrValues))
			{
				$blnTag = false;
			}
			else
			{
				$objTags = TagModel::findByIdAndTable($dc->activeRecord->id, $dc->table);

				$arrSavedTags = array();

				if($objTags !== null)
				{
					$arrSavedTags = $objTags->fetchEach('tag');
					$blnTag = true;
				}

				$arrNewTags = array_diff($arrValues, $arrSavedTags);
				
				if(is_array($arrNewTags) && !empty($arrNewTags))
				{
					$blnTag = true;

					foreach($arrNewTags as $strTag)
					{
						$objModel = new TagModel();
						$objModel->tag = $strTag;
						$objModel->from_table = $dc->table;
						$objModel->tid = $dc->activeRecord->id;
						$objModel->save();
					}
				}
			}

			$dc->activeRecord->{$strField} = $blnTag;
			$dc->activeRecord->save();
		}
	}
}