<?php 

$dc = &$GLOBALS['TL_DCA']['tl_module'];

$dc['palettes']['__selector__'][] = 'formHybridAddDefaultValues';
$dc['subpalettes']['formHybridAddDefaultValues'] = 'formHybridDefaultValues';

$arrFields = array
(
	'formHybridDataContainer' => array
	(
		'inputType'								=> 'select',
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridDataContainer'],
		'default'								=> 'default',
		'options_callback'						=> array('tl_form_hybrid_module', 'getDataContainers'),
		'eval'									=> array('chosen'=>true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr', 'mandatory' => true),
		'exclude'								=> true,
		'sql'									=> "varchar(255) NOT NULL default ''"
	),
	'formHybridPalette' => array
	(
		'inputType'								=> 'select',
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridPalette'],
		'default'								=> 'default',
		'options_callback'						=> array('tl_form_hybrid_module', 'getPalette'),
		'eval'									=> array('chosen'=>true, 'submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true),
		'exclude'								=> true,
		'sql'									=> "varchar(255) NOT NULL default ''"
	),
	'formHybridEditable' => array
	(
		'inputType'								=> 'checkboxWizard',
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
		'options_callback'						=> array('tl_form_hybrid_module', 'getEditable'),
		'exclude'								=> true,
		'eval'									=> array('multiple'=>true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight', 'mandatory' => true),
		'sql'									=> "blob NULL"
	),
	'formHybridEditableSkip' => array
	(
		'inputType'								=> 'checkboxWizard',
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableSkip'],
		'options_callback'						=> array('tl_form_hybrid_module', 'getEditable'),
		'exclude'								=> true,
		'eval'									=> array('multiple'=>true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight'),
		'sql'									=> "blob NULL"
	),
	'formHybridAddDefaultValues' => array(
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridAddDefaultValues'],
		'exclude'								=> true,
		'inputType'								=> 'checkbox',
		'eval'									=> array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
		'sql'									=> "char(1) NOT NULL default ''"
	),
	'formHybridDefaultValues' => array
	(
		'label'									=> &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues'],
		'exclude' 								=> true,
		'inputType' 							=> 'multiColumnWizard',
		'eval' 									=> array(
			'columnFields' => array(
				'field' => array(
					'label'                 => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['field'],
					'exclude'               => true,
					'inputType'             => 'select',
					'options_callback'		=> array('tl_form_hybrid_module', 'getEditable'),
					'eval'					=> array('style'=>'width: 200px')
				),
				'value' => array(
					'label'                 => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['value'],
					'exclude'               => true,
					'inputType'             => 'text',
					'eval'					=> array('style'=>'width: 200px')
				)
			),
			'tl_class' => 'clr long'
		),
		'sql'						=> "blob NULL"
	)
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_form_hybrid_module extends \Backend
{
	
	public function getDataContainers(\DataContainer $dc)
	{
		$arrDCA = array();

        $arrModules = \ModuleLoader::getActive();

        if(!is_array($arrModules)) return $arrDCA;

		foreach ($arrModules as $strModule)
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
        $return = array();

        if (!$dc->activeRecord->formHybridDataContainer) return $return;

        System::loadLanguageFile($dc->activeRecord->formHybridDataContainer);
		$this->loadDataContainer($dc->activeRecord->formHybridDataContainer);

        $arrPaletes = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['palettes'];

        if(!is_array($arrPaletes)) return $return;

		foreach ($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['palettes'] as $k=>$v)
		{
			if ($k != '__selector__')
				$return[$k] = $k;
		}
		
		return $return;
	}
	
	public function getEditable($dc) // no type because of multicolumnwizard not supporting passing a dc to an options_callback :-(
	{
		// get dc for multicolumnwizard...
		if (!$dc)
		{
			$dc = new stdClass();
			$dc->activeRecord = \ModuleModel::findByPk(\Input::get('id'));
		}
		
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