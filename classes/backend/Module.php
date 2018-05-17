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
use Contao\CoreBundle\Monolog\ContaoContext;

class Module extends \Backend
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
		return array_values(Arrays::filterByPrefixes(get_defined_constants(), ['FORMHYBRID_VIEW_MODE_']));
	}

	public function getSubmitLabels()
	{
		$arrOptions = [];

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

    public static function getSalutationGroupOptions()
    {
        $arrOptions = [];

        $salutationGroupRepository = \Contao\Doctrine\ORM\EntityHelper::getRepository('Avisota\Contao:SalutationGroup');
        /** @var SalutationGroup[] $salutationGroups */
        $salutationGroups = $salutationGroupRepository->findAll();

        foreach ($salutationGroups as $salutationGroup)
        {
            $arrOptions[$salutationGroup->getId()] = $salutationGroup->getTitle();
        }

        return $arrOptions;
    }

    /**
     * Return all possible Email fields  as array
     *
     * @return array
     */
    public function getEmailFormFields(\DataContainer $dc)
    {
        $arrOptions = [];

        $arrDca = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer];

        if ($dc->activeRecord === null || empty($arrDca))
        {
            return $arrOptions;
        }

        foreach ($arrDca['fields'] as $strName => $arrData)
        {
            if ($arrData['eval']['rgxp'] != 'email')
            {
                continue;
            }

            $strLabel = $arrData['label'][0] ? ($arrData['label'][0] . ' [' . $strName . ']') : $strName;

            $arrOptions[$strName] = $strLabel;
        }

        return $arrOptions;
    }

    public static function getEditable($objDc)
    {
        if ($objDc->activeRecord !== null)
        {
            return \HeimrichHannot\FormHybrid\FormHelper::getEditableFields($objDc->activeRecord->formHybridDataContainer);
        }
        else
        {
            if (null === ($module = \Contao\ModuleModel::findByPk($objDc->id)))
            {
                return [];
            }

            return \HeimrichHannot\FormHybrid\FormHelper::getEditableFields($module->formHybridDataContainer);
        }
    }

    public static function getEditableForExport($objDc)
    {
        if (($objModule = \ModuleModel::findByPk($objDc->activeRecord->pid)) === null)
        {
            return [];
        }

        return \HeimrichHannot\FormHybrid\FormHelper::getEditableFields($objModule->formHybridDataContainer);
    }

    public function getDataContainers(\DataContainer $arrDca)
    {
        $arrDCA = [];

        $arrModules = \ModuleLoader::getActive();

        if (!is_array($arrModules))
        {
            return $arrDCA;
        }

        foreach ($arrModules as $strModule)
        {
            $strDir = TL_ROOT . '/system/modules/' . $strModule . '/dca';
            if (!file_exists($strDir) && version_compare(VERSION, 4.0, '>'))
            {
                try
                {
                    $strDir = \System::getContainer()->get('kernel')->locateResource('@'.$strModule);
                    $strDir .= 'Resources/contao/dca';
                }
                catch (\InvalidArgumentException $ex)
                {
                    \System::getContainer()->get('monolog.logger.contao')->addNotice(
                        'Bundle/Extension '.$strModule.' not found.',
                        ['contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)]
                    );
                    $strDir = '';
                }
            }
            if (file_exists($strDir))
            {
                foreach (scandir($strDir) as $strFile)
                {
                    if (substr($strFile, 0, 1) != '.' && file_exists($strDir . '/' . $strFile))
                    {
                        $arrDCA[] = str_replace('.php', '', $strFile);
                    }
                }
            }
        }

        $arrDCA = array_unique($arrDCA);
        sort($arrDCA);

        return $arrDCA;
    }

    public function getPalette(\DataContainer $arrDca)
    {
        $return = [];

        if (!$arrDca->activeRecord->formHybridDataContainer)
        {
            return $return;
        }

        System::loadLanguageFile($arrDca->activeRecord->formHybridDataContainer);
        Controller::loadDataContainer($arrDca->activeRecord->formHybridDataContainer);

        $arrPalettes = $GLOBALS['TL_DCA'][$arrDca->activeRecord->formHybridDataContainer]['palettes'];

        if (!is_array($arrPalettes))
        {
            return $return;
        }

        foreach ($GLOBALS['TL_DCA'][$arrDca->activeRecord->formHybridDataContainer]['palettes'] as $k => $v)
        {
            if ($k != '__selector__')
            {
                $return[$k] = $k;
            }
        }

        return $return;
    }

    // no type because of multicolumnwizard not supporting passing a dc to an options_callback :-(
    public static function getFields($objDc)
    {
        if ($objDc->activeRecord->formHybridDataContainer)
        {
            return \HeimrichHannot\Haste\Dca\General::getFields($objDc->activeRecord->formHybridDataContainer, false);
        }
    }

    public function getSubPaletteFields(\DataContainer $arrDca)
    {
        $strTable            = $arrDca->activeRecord->formHybridDataContainer;
        $arrSubPalettes      = [];
        $arrSubPaletteFields = [];
        $arrFields           = [];

        \Controller::loadDataContainer($strTable);

        $arrSubPalettes = $GLOBALS['TL_DCA'][$strTable]['subpalettes'];
        if (empty($arrSubPalettes))
        {
            return;
        }

        foreach ($arrSubPalettes as $strName => $strPalette)
        {
            $arrSubPaletteFields = \HeimrichHannot\FormHybrid\FormHelper::getPaletteFields($strTable, $arrSubPalettes[$strName]);
            if (empty($arrSubPaletteFields))
            {
                return;
            }

            $arrFields = array_merge($arrFields, $arrSubPaletteFields);
        }

        return $arrFields;
    }

    public function getOptInMessages(\DataContainer $arrDca)
    {
        $arrOptions = [];

        $objNotifications = \NotificationCenter\Model\Notification::findByType(\HeimrichHannot\FormHybrid\FormHybrid::NOTIFICATION_TYPE_FORM_OPT_IN);

        if ($objNotifications === null)
        {
            return $arrOptions;
        }

        $objMessages = \NotificationCenter\Model\Message::findBy(['pid IN (' . implode(',', array_map('intval', $objNotifications->fetchEach('id'))) . ')'], []);

        while ($objMessages->next())
        {
            if (($objNotification = $objMessages->getRelated('pid')) === null)
            {
                continue;
            }

            $arrOptions[$objNotification->title][$objMessages->id] = $objMessages->title;
        }

        return $arrOptions;
    }

    public function getNoficiationMessages(\DataContainer $arrDca)
    {
        $arrOptions = [];

        $objMessages = \NotificationCenter\Model\Message::findAll();

        if ($objMessages === null)
        {
            return $arrOptions;
        }

        while ($objMessages->next())
        {
            if (($objNotification = $objMessages->getRelated('pid')) === null)
            {
                continue;
            }

            $arrOptions[$objNotification->title][$objMessages->id] = $objMessages->title;
        }

        return $arrOptions;
    }

    public static function getFormHybridExportConfigsAsOptions()
    {
        return \HeimrichHannot\Exporter\Backend::getConfigsAsOptions(\HeimrichHannot\FormHybrid\FormHybrid::EXPORT_TYPE_FORMHYBRID);
    }
}
