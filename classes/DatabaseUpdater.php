<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package anwaltverein
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;

class DatabaseUpdater
{

	public static function run()
	{
		$objDatabase = \Database::getInstance();
		\Controller::loadDataContainer('tl_module');

		$arrRenameFields = [
            'tl_module'      => [
                'jumpToSuccess' => [
					'name' => 'jumpTo',
					'syncValue' => true],
                'jumpToSuccessPreserveParams' => [
					'name' => 'formHybridJumpToPreserveParams',
					'syncValue' => false],
                'allowIdAsGetParameter' => [
					'name' => 'formHybridAllowIdAsGetParameter',
					'syncValue' => true],
                'idGetParameter' => [
					'name' => 'formHybridIdGetParameter',
					'syncValue' => true],
                'appendIdToUrlOnCreation' => [
					'name' => 'formHybridAppendIdToUrlOnCreation',
					'syncValue' => true]],];
		
		foreach ($arrRenameFields as $strTable => $arrFields)
		{
			if (!$objDatabase->tableExists($strTable)) continue;
			
			foreach ($arrFields as $strOldName => $arrConfig)
			{
				if(!$objDatabase->fieldExists($strOldName, $strTable))
				{
					continue;
				}
				
				$strNewName = $arrConfig['name'];
				$sql = &$GLOBALS['TL_DCA']['tl_module']['fields'][$strNewName]['sql'];
				
				if(!$objDatabase->fieldExists($arrConfig['name'], $strTable) && $sql)
				{
					$sql = &$GLOBALS['TL_DCA']['tl_module']['fields'][$strNewName]['sql'];
					$objDatabase->query("ALTER TABLE $strTable ADD `$strNewName` $sql");
				}
				
				if(!$arrConfig['syncValue'])
				{
					continue;
				}
				
				$objDatabase->prepare('UPDATE ' . $strTable . ' SET ' . $arrConfig['name'] . ' = ' . $strOldName)->execute();
			}
		}

		return;
	}
} 