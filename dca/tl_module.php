<?php

$dc = &$GLOBALS['TL_DCA']['tl_module'];

$dc['palettes']['__selector__'][]                      = 'formHybridAddDefaultValues';
$dc['palettes']['__selector__'][]                      = 'formHybridSendSubmissionViaEmail';
$dc['palettes']['__selector__'][]                      = 'formHybridSendConfirmationViaEmail';
$dc['palettes']['__selector__'][]                      = 'formHybridAddEditableRequired';
$dc['subpalettes']['formHybridAddDefaultValues']       = 'formHybridDefaultValues';
$dc['subpalettes']['formHybridSendSubmissionViaEmail'] =
	'formHybridSubmissionMailSender,formHybridSubmissionMailRecipient,formHybridSubmissionMailSubject,formHybridSubmissionMailText,formHybridSubmissionMailTemplate,formHybridSubmissionMailAttachment';

$dc['subpalettes']['formHybridSendConfirmationViaEmail'] =
	'formHybridConfirmationMailRecipientField,formHybridConfirmationMailSender,formHybridConfirmationMailSubject,formHybridConfirmationMailText,formHybridConfirmationMailTemplate,formHybridConfirmationMailAttachment';

$dc['subpalettes']['formHybridAddEditableRequired'] = 'formHybridEditableRequired';

$arrFields = array
(
	'formHybridDataContainer'                  => array
	(
		'inputType'        => 'select',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridDataContainer'],
		'options_callback' => array('tl_form_hybrid_module', 'getDataContainers'),
		'eval'             => array(
			'chosen'             => true,
			'submitOnChange'     => true,
			'includeBlankOption' => true,
			'tl_class'           => 'w50 clr',
			'mandatory'          => true,
		),
		'exclude'          => true,
		'sql'              => "varchar(255) NOT NULL default ''",
	),
	'formHybridPalette'                        => array
	(
		'inputType'        => 'select',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridPalette'],
		'default'          => 'default',
		'options_callback' => array('tl_form_hybrid_module', 'getPalette'),
		'eval'             => array(
			'chosen'             => true,
			'submitOnChange'     => true,
			'includeBlankOption' => true,
			'tl_class'           => 'w50',
			'mandatory'          => true,
		),
		'exclude'          => true,
		'sql'              => "varchar(255) NOT NULL default ''",
	),
	'formHybridEditable'                       => array
	(
		'inputType'        => 'checkboxWizard',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
		'options_callback' => array('tl_form_hybrid_module', 'getEditable'),
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr', 'mandatory' => true),
		'sql'              => "blob NULL",
	),
	'formHybridAddEditableRequired'            => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddEditableRequired'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridEditableRequired'               => array
	(
		'inputType'        => 'checkboxWizard',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableRequired'],
		'options_callback' => array('tl_form_hybrid_module', 'getEditable'),
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight',),
		'sql'              => "blob NULL",
	),
	'formHybridEditableSkip'                   => array
	(
		'inputType'        => 'checkboxWizard',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableSkip'],
		'options_callback' => array('tl_form_hybrid_module', 'getFields'),
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight'),
		'sql'              => "blob NULL",
	),
	'formHybridSubPalettes'                    => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubPalettes'],
		'inputType' => 'multiColumnWizard',
		'exclude'   => true,
		'eval'      => array(
			'columnFields' => array(
				'subpalette' => array(
					'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubPalettes']['subpalette'],
					'exclude'          => true,
					'inputType'        => 'select',
					'options_callback' => array('tl_form_hybrid_module', 'getSubPalettes'),
					'eval'             => array('chosen' => true, 'submitOnChange' => true, 'style' => 'width: 200px'),
				),
				'fields'     => array(
					'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubPalettes']['fields'],
					'exclude'          => true,
					'inputType'        => 'checkboxWizard',
					'eval'             => array('columnPos' => 1, 'chosen' => true, 'multiple' => true),
					'options_callback' => array('tl_form_hybrid_module', 'getSubPaletteFields'),
				),
			),
			'tl_class'     => 'clr long',
		),
		'sql'       => "blob NULL",
	),
	'formHybridAddDefaultValues'               => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddDefaultValues'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridDefaultValues'                  => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues'],
		'exclude'   => true,
		'inputType' => 'multiColumnWizard',
		'eval'      => array(
			'columnFields' => array(
				'field' => array(
					'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['field'],
					'exclude'          => true,
					'inputType'        => 'select',
					'options_callback' => array(
						'tl_form_hybrid_module',
						'getFields',
					),
					'eval'             => array('style' => 'width: 200px', 'chosen' => true),
				),
				'value' => array(
					'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['value'],
					'exclude'   => true,
					'inputType' => 'text',
					'eval'      => array('style' => 'width: 200px'),
				),
			),
			'tl_class'     => 'clr long',
		),
		'sql'       => "blob NULL",
	),
	'formHybridTemplate'                       => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridTemplate'],
		'default'          => 'formhybrid_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getFormHybridTemplates'),
		'eval'             => array('tl_class' => 'w50 clr'),
		'sql'              => "varchar(64) NOT NULL default ''",
	),
	'formHybridCustomSubTemplates'               => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomSubTemplates'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridCssClass'           => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridCssClass'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'text',
		'eval'        => array('maxlength' => 255, 'tl_class' => 'w50 clr'),
		'sql'         => "varchar(255) NOT NULL default ''",
	),
	'formHybridStartTemplate'                  => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridStartTemplate'],
		'default'          => 'formhybridStart_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getFormHybridStartTemplates'),
		'eval'             => array('tl_class' => 'w50'),
		'sql'              => "varchar(64) NOT NULL default ''",
	),
	'formHybridStopTemplate'                   => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridStopTemplate'],
		'default'          => 'formhybridStop_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getFormHybridStopTemplates'),
		'eval'             => array('tl_class' => 'w50'),
		'sql'              => "varchar(64) NOT NULL default ''",
	),
	'formHybridAsync'                          => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAsync'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridSuccessMessage'                 => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSuccessMessage'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'textarea',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array('allowHtml' => true, 'tl_class' => 'clr', 'class' => 'monospace', 'rte' => 'ace|html', 'helpwizard' => true),
		'sql'         => "text NULL",
	),
	'formHybridSendSubmissionViaEmail'         => array(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendSubmissionViaEmail'],
		'exclude'     => true,
		'inputType'   => 'checkbox',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('submitOnChange' => true, 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "char(1) NOT NULL default ''",
	),
	'formHybridSubmissionMailSender'           => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSender'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "varchar(255) NOT NULL default ''",
	),
	'formHybridSubmissionMailRecipient'        => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailRecipient'],
		'exclude'     => true,
		'search'      => true,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('mandatory' => true, 'maxlength' => 1022, 'rgxp' => 'emails', 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "varchar(1022) NOT NULL default ''",
	),
	'formHybridSubmissionMailSubject'          => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSubject'],
		'exclude'     => true,
		'search'      => true,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array(
			'mandatory'      => true,
			'maxlength'      => 255,
			'decodeEntities' => true,
			'tl_class'       => 'w50',
			'helpwizard'     => true,
		),
		'sql'         => "varchar(255) NOT NULL default ''",
	),
	'formHybridSubmissionMailText'             => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailText'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'textarea',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array('tl_class' => 'clr', 'decodeEntities' => true, 'alwaysSave' => true, 'helpwizard' => true),
		'sql'         => "text NULL",
	),
	'formHybridSubmissionMailTemplate'         => array(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailTemplate'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'fileTree',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array(
			'helpwizard' => true,
			'files'      => true,
			'fieldType'  => 'radio',
			'extensions' => 'htm,html,txt,tpl',
		),
		'sql'         => "binary(16) NULL",
	),
	'formHybridSubmissionMailAttachment'       => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailAttachment'],
		'exclude'   => true,
		'inputType' => 'fileTree',
		'eval'      => array('multiple' => true, 'fieldType' => 'checkbox', 'files' => true),
		'sql'       => "blob NULL",
	),
	'formHybridSendConfirmationViaEmail'       => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendConfirmationViaEmail'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridConfirmationMailRecipientField' => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailRecipientField'],
		'exclude'          => true,
		'search'           => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getEmailFormFields'),
		'eval'             => array('mandatory' => true, 'chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr'),
		'sql'              => "varchar(255) NOT NULL default ''",
	),
	'formHybridConfirmationMailSender'         => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSender'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "varchar(255) NOT NULL default ''",
	),
	'formHybridConfirmationMailSubject'        => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSubject'],
		'exclude'     => true,
		'search'      => true,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array(
			'mandatory'      => true,
			'maxlength'      => 255,
			'decodeEntities' => true,
			'tl_class'       => 'w50',
			'helpwizard'     => true,
		),
		'sql'         => "varchar(255) NOT NULL default ''",
	),
	'formHybridConfirmationMailText'           => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailText'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'textarea',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array('tl_class' => 'clr', 'decodeEntities' => true, 'alwaysSave' => true, 'helpwizard' => true),
		'sql'         => "text NULL",
	),
	'formHybridConfirmationMailTemplate'       => array(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailTemplate'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'fileTree',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array(
			'helpwizard' => true,
			'files'      => true,
			'fieldType'  => 'radio',
			'extensions' => 'htm,html,txt,tpl',
		),
		'sql'         => "binary(16) NULL",
	),
	'formHybridConfirmationMailAttachment'     => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailAttachment'],
		'exclude'   => true,
		'inputType' => 'fileTree',
		'eval'      => array('multiple' => true, 'fieldType' => 'checkbox', 'files' => true),
		'sql'       => "blob NULL",
	),
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_form_hybrid_module extends \Backend
{

	/**
	 * Return all possible Email fields  as array
	 *
	 * @return array
	 */
	public function getEmailFormFields(\DataContainer $dc)
	{
		$arrOptions = array();

		$arrDca = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer];

		if ($dc->activeRecord === null || empty($arrDca)) {
			return $arrOptions;
		}

		foreach ($arrDca['fields'] as $strName => $arrData) {
			if ($arrData['eval']['rgxp'] != 'email') {
				continue;
			}

			$strLabel = $arrData['label'][0] ? ($arrData['label'][0] . ' [' . $strName . ']') : $strName;

			$arrOptions[$strName] = $strLabel;
		}

		return $arrOptions;
	}


	public function getDataContainers(\DataContainer $dc)
	{
		$arrDCA = array();

		$arrModules = \ModuleLoader::getActive();

		if (!is_array($arrModules)) {
			return $arrDCA;
		}

		foreach ($arrModules as $strModule) {
			$strDir = TL_ROOT . '/system/modules/' . $strModule . '/dca';
			
			if (file_exists($strDir)) {
				foreach (scandir($strDir) as $strFile) {
					if (substr($strFile, 0, 1) != '.' && file_exists($strDir . '/' . $strFile)) {
						$arrDCA[] = str_replace('.php', '', $strFile);
					}
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

		if (!$dc->activeRecord->formHybridDataContainer) {
			return $return;
		}

		System::loadLanguageFile($dc->activeRecord->formHybridDataContainer);
		Controller::loadDataContainer($dc->activeRecord->formHybridDataContainer);

		$arrPalettes = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['palettes'];

		if (!is_array($arrPalettes)) {
			return $return;
		}

		foreach ($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['palettes'] as $k => $v) {
			if ($k != '__selector__') {
				$return[$k] = $k;
			}
		}
		
		return $return;
	}

	public function getSubPalettes($dc)
	{
		$return = array();

		if (!$dc->activeRecord->formHybridDataContainer) {
			return $return;
		}

		\System::loadLanguageFile($dc->activeRecord->formHybridDataContainer);
		$this->loadDataContainer($dc->activeRecord->formHybridDataContainer);

		$arrPalettes = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes'];

		if (!is_array($arrPalettes)) {
			return $return;
		}

		foreach ($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes'] as $strSelector => $strSubPalette) {
			$return[$strSelector] = $strSelector;
		}

		sort($return);

		return $return;
	}

	public function getSubPaletteFields($dc)
	{
		$return = array();

		if (!$dc->activeRecord->formHybridDataContainer || !$dc->value[0]['subpalette']) {
			return $return;
		}

		\System::loadLanguageFile($dc->activeRecord->formHybridDataContainer);
		$this->loadDataContainer($dc->activeRecord->formHybridDataContainer);

		foreach (
			explode(',', $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes'][$dc->value[0]['subpalette']])
			as $v
		) {
			$label = $GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['fields'][$v]['label'][0];

			$return[$v] = $label ? $label : $v;
		}

		return $return;
	}

	public function getEditable($dc) // no type because of multicolumnwizard not supporting passing a dc to an options_callback :-(
	{
		// get dc for multicolumnwizard...
		if (!$dc) {
			$objModule = \ModuleModel::findByPk(\Input::get('id'));

			if ($objModule === null) {
				return array();
			}

			$dc = new HeimrichHannot\FormHybrid\DC_Hybrid('tl_module', $objModule);
		}


		if (!$dc->activeRecord->formHybridDataContainer) {
			return array();
		}


		$arrFields = HeimrichHannot\FormHybrid\FormHelper::getPaletteFields(
			$dc->activeRecord->formHybridDataContainer,
			$GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['palettes'][$dc->activeRecord->formHybridPalette]
		);

		$arrSubPalettes = array_keys($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes']);

		// ignore subpalettes not in palette
		$arrSubPalettes = array_intersect($arrSubPalettes, $arrFields);

		foreach ($arrSubPalettes as $strSubPalette) {
			$arrFields = array_merge(
				$arrFields,
				HeimrichHannot\FormHybrid\FormHelper::getPaletteFields(
					$dc->activeRecord->formHybridDataContainer,
					$GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes'][$strSubPalette]
				)
			);
		}

		return $arrFields;
	}

	public function getFields($dc) // no type because of multicolumnwizard not supporting passing a dc to an options_callback :-(
	{
		// get dc for multicolumnwizard...
		if (!$dc) {
			$dc               = new stdClass();
			$dc->activeRecord = \ModuleModel::findByPk(\Input::get('id'));
		}

		if (!$dc->activeRecord->formHybridDataContainer) {
			return array();
		}

		\System::loadLanguageFile($dc->activeRecord->formHybridDataContainer);
		\Controller::loadDataContainer($dc->activeRecord->formHybridDataContainer);

		$arrOptions = array();

		foreach ($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['fields'] as $strField => $arrData) {
			if (is_array($arrData['label'])) {
				$strLabel = $arrData['label'][0] ?: $strField;
			} else {
				$strLabel = $arrData['label'] ?: $strField;
			}

			$arrOptions[$strField] = $strLabel ?: $strField;
		}

		asort($arrOptions);

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
	
}
