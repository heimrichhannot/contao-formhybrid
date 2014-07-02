<?php 

$dc = &$GLOBALS['TL_DCA']['tl_module'];

$arrFields = array
(
	'formHybridDataContainer' => array
	(
		'inputType'								=> 'select',
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridDataContainer'],
		'default'								=> 'default',
		'options_callback'						=> array('tl_form_hybrid_module', 'getDataContainers'),
		'eval'									=> array('chosen'=>true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'),
		'exclude'								=> true,
		'sql'									=> "varchar(255) NOT NULL default ''"
	),
	'formHybridPalette' => array
	(
		'inputType'								=> 'select',
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridPalette'],
		'default'								=> 'default',
		'options_callback'						=> array('tl_form_hybrid_module', 'getPalette'),
		'eval'									=> array('chosen'=>true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'),
		'exclude'								=> true,
		'sql'									=> "varchar(255) NOT NULL default ''"
	),
	'formHybridEditable' => array
	(
		'inputType'								=> 'checkboxWizard',
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
		'options_callback'						=> array('tl_form_hybrid_module', 'getEditable'),
		'exclude'								=> true,
		'eval'									=> array('multiple'=>true, 'includeBlankOption' => true, 'tl_class' => 'w50'),
		'sql'									=> "blob NULL"
	)
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_form_hybrid_module extends \Backend
{
	
	public function getDataContainers(\DataContainer $dc)
	{
		$arrDCA = array();
		foreach (\ModuleLoader::getActive() as $strModule)
		{
			$strDir = TL_ROOT . '/system/modules/' . $strModule . '/dca';
			
			if (file_exists($strDir))
				foreach (scandir($strDir) as $strFile) {
					if ($strFile != '.' && $strFile != '..' && file_exists($strDir . '/' . $strFile))
					{
						$arrDCA[] = str_replace('.php', '', $strFile);
					}
				}
		}
		
		$arrDCA = array_unique($arrDCA);
		sort($arrDCA);
		
		return $arrDCA;
	}
	
	public function getPalette(\DataContainer $dc)
	{
		if (!$dc->activeRecord->formHybridDataContainer)
			return array();
		
		$return = array();
		
		System::loadLanguageFile($dc->activeRecord->formHybridDataContainer);
		$this->loadDataContainer($dc->activeRecord->formHybridDataContainer);
		
		foreach ($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['palettes'] as $k=>$v)
		{
			if ($k != '__selector__')
				$return[$k] = $k;
		}
		
		return $return;
	}
	
	public function getEditable(\DataContainer $dc)
	{
		if (!$dc->activeRecord->formHybridDataContainer)
			return array();
		
		$return = array();
		
		System::loadLanguageFile($dc->activeRecord->formHybridDataContainer);
		$this->loadDataContainer($dc->activeRecord->formHybridDataContainer);
		
		$boxes = trimsplit(';', $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['palettes'][$dc->activeRecord->formHybridPalette]);
		
		foreach ($boxes as $k=>$v)
		{
			$eCount = 1;
			$boxes[$k] = trimsplit(',', $v);
		
			foreach ($boxes[$k] as $kk=>$vv)
			{
				if (preg_match('/^\[.*\]$/i', $vv))
				{
					++$eCount;
					continue;
				}
				
				// legends
				if (preg_match('/^\{.*\}$/i', $vv))
				{
					unset($boxes[$k][$kk]);
				}
			}
		
			// Unset a box if it does not contain any fields
			if (count($boxes[$k]) < $eCount)
			{
				unset($boxes[$k]);
			}
		}
		
		$return = array();
		
		// flatten array and set labels
		foreach ($boxes as $k => $box)
		{
			foreach($box as $name)
			{
				$label = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['fields'][$name]['label'][0];
				$return[$name] = $label ? $label : $name;
			}
		}
		
		return $return;
	}
	
}