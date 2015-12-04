<?php

$dc = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Subpalettes
 */
$dc['palettes']['__selector__'][]                      = 'formHybridAddDefaultValues';
$dc['palettes']['__selector__'][]                      = 'formHybridSendSubmissionViaEmail';
$dc['palettes']['__selector__'][]                      = 'formHybridSendConfirmationViaEmail';
$dc['palettes']['__selector__'][]                      = 'formHybridAddEditableRequired';
$dc['palettes']['__selector__'][]                      = 'formHybridAddFieldDependentRedirect';
$dc['subpalettes']['formHybridAddDefaultValues']       = 'formHybridDefaultValues';
$dc['subpalettes']['formHybridSendSubmissionViaEmail'] =
	'formHybridSubmissionMailRecipient,formHybridSubmissionAvisotaMessage,formHybridSubmissionMailSender,formHybridSubmissionMailSubject,formHybridSubmissionMailText,formHybridSubmissionMailTemplate,formHybridSubmissionMailAttachment';

$dc['subpalettes']['formHybridSendConfirmationViaEmail'] =
	'formHybridConfirmationMailRecipientField,formHybridConfirmationAvisotaMessage,formHybridConfirmationMailSender,formHybridConfirmationMailSubject,formHybridConfirmationMailText,formHybridConfirmationMailTemplate,formHybridConfirmationMailAttachment';

$dc['subpalettes']['formHybridAddEditableRequired'] = 'formHybridEditableRequired';

$dc['subpalettes']['formHybridAddFieldDependentRedirect'] = 'formHybridFieldDependentRedirectConditions,formHybridFieldDependentRedirectJumpTo,formHybridFieldDependentRedirectKeepParams';

/**
 * Callbacks
 */
$dc['config']['onload_callback'][] = array('tl_form_hybrid_module', 'modifyPalette');

/**
 * Fields
 */
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
		'options_callback' => array('tl_form_hybrid_module', 'getFields'),
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight',),
		'sql'              => "blob NULL",
	),
	'formHybridEditableSkip'                   => array
	(
		'inputType'        => 'checkboxWizard',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableSkip'],
		'options_callback' => array('tl_form_hybrid_module', 'getFields'),  // all fields, cause formHybridDefaultValues will suffer from all fields
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight'),
		'sql'              => "blob NULL",
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
					'eval'             => array('style' => 'width: 150px', 'chosen' => true),
				),
				'value' => array(
					'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['value'],
					'exclude'   => true,
					'inputType' => 'text',
					'eval'      => array('style' => 'width: 100px'),
				),
				'label' => array(
					'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['label'],
					'exclude'   => true,
					'inputType' => 'text',
					'eval'      => array('style' => 'width: 350px', 'allowHtml' => true),
				),
				'hidden' => array
				(
					'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['hidden'],
					'exclude'   => true,
					'inputType' => 'checkbox',
					'eval'      => array('style' => 'width: 50px'),
				)
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
	'formHybridAction' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['formHybridAction'],
		'exclude'                 => true,
		'inputType'               => 'pageTree',
		'foreignKey'              => 'tl_page.title',
		'eval'                    => array('fieldType'=>'radio', 'tl_class' => 'clr'),
		'sql'                     => "int(10) unsigned NOT NULL default '0'",
		'relation'                => array('type'=>'hasOne', 'load'=>'eager')
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
	'formHybridSkipScrollingToSuccessMessage'   => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSkipScrollingToSuccessMessage'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
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
			'mandatory'      => false,
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
			'mandatory'      => false,
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
	'formHybridAddFieldDependentRedirect'            => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddFieldDependentRedirect'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridFieldDependentRedirectKeepParams'            => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectKeepParams'],
		'inputType'   => 'text',
		'eval'        => array('tl_class' => 'w50'),
		'sql'         => "varchar(255) NOT NULL default ''",
	)
);

// conditions for the field depending redirect
$arrFields['formHybridFieldDependentRedirectConditions'] = $arrFields['formHybridDefaultValues'];
$arrFields['formHybridFieldDependentRedirectConditions']['label'] =
	&$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectConditions'];
unset($arrFields['formHybridFieldDependentRedirectConditions']['label']['eval']['columnFields']['label']);
unset($arrFields['formHybridFieldDependentRedirectConditions']['label']['eval']['columnFields']['hidden']);
$arrFields['formHybridFieldDependentRedirectJumpTo'] = $dc['fields']['jumpTo'];
$arrFields['formHybridFieldDependentRedirectJumpTo']['label'] =
	&$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectJumpTo'];
$arrFields['formHybridFieldDependentRedirectJumpTo']['eval']['mandatory'] = true;
$arrFields['formHybridFieldDependentRedirectJumpTo']['eval']['tl_class'] = 'w50';

if (in_array('avisota-core', \ModuleLoader::getActive()))
{
	$arrFields['formHybridSubmissionAvisotaMessage'] = array
	(
		'exclude'          => true,
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaMessage'],
		'inputType'        => 'select',
		'options_callback' => \ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEventCallbackFactory::createCallback(
			\Avisota\Contao\Message\Core\MessageEvents::CREATE_BOILERPLATE_MESSAGE_OPTIONS,
			'Avisota\Contao\Core\Event\CreateOptionsEvent'
		),
		'eval'             => array(
			'includeBlankOption' => true,
			'tl_class'           => 'w50 clr',
			'chosen' => true,
			'submitOnChange'     => true
		),
		'sql' => "char(36) NOT NULL default ''"
	);

	$arrFields['formHybridSubmissionAvisotaSalutationGroup'] = array
	(
		'exclude'          => true,
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaSalutationGroup'],
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getSalutationGroupOptions'),
		'eval'             => array(
			'includeBlankOption' => true,
			'tl_class'           => 'w50',
			'chosen' => true
		),
		'sql' => "char(36) NOT NULL default ''"
	);

	$arrFields['formHybridConfirmationAvisotaMessage'] = array
	(
		'exclude'          => true,
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaMessage'],
		'inputType'        => 'select',
		'options_callback' => \ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEventCallbackFactory::createCallback(
			\Avisota\Contao\Message\Core\MessageEvents::CREATE_BOILERPLATE_MESSAGE_OPTIONS,
			'Avisota\Contao\Core\Event\CreateOptionsEvent'
		),
		'eval'             => array(
			'includeBlankOption' => true,
			'tl_class'           => 'w50 clr',
			'chosen' => true,
			'submitOnChange'     => true
		),
		'sql' => "char(36) NOT NULL default ''"
	);

	$arrFields['formHybridConfirmationAvisotaSalutationGroup'] = array
	(
		'exclude'          => true,
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaSalutationGroup'],
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getSalutationGroupOptions'),
		'eval'             => array(
			'includeBlankOption' => true,
			'tl_class'           => 'w50',
			'chosen' => true
		),
		'sql' => "char(36) NOT NULL default ''"
	);
}

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_form_hybrid_module extends \Backend
{

	public static function getSalutationGroupOptions()
	{
		$arrOptions = array();

		$salutationGroupRepository = \Contao\Doctrine\ORM\EntityHelper::getRepository('Avisota\Contao:SalutationGroup');
		/** @var SalutationGroup[] $salutationGroups */
		$salutationGroups = $salutationGroupRepository->findAll();

		foreach ($salutationGroups as $salutationGroup) {
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
		
		if(is_array($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes']))
		{
			$arrSubPalettes = array_keys($GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes']);

			// ignore subpalettes not in palette
			$arrSubPalettes = HeimrichHannot\FormHybrid\FormHelper::getFilteredSubPalettes(
				$arrSubPalettes, $arrFields, $dc);

			foreach ($arrSubPalettes as $strSubPalette) {
				$arrFields = array_merge(
					$arrFields,
					HeimrichHannot\FormHybrid\FormHelper::getPaletteFields(
						$dc->activeRecord->formHybridDataContainer,
						$GLOBALS['TL_DCA'][$dc->activeRecord->formHybridDataContainer]['subpalettes'][$strSubPalette]
					)
				);
			}
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

	public function modifyPalette()
	{
		if (!in_array('avisota-core', \ModuleLoader::getActive()))
			return;

		$objModule = \ModuleModel::findByPk(\Input::get('id'));
		$arrDc = &$GLOBALS['TL_DCA']['tl_module'];

		// submission
		$arrFieldsToHide = array
		(
			'formHybridSubmissionMailSender',
			'formHybridSubmissionMailSubject',
			'formHybridSubmissionMailText',
			'formHybridSubmissionMailTemplate',
			'formHybridSubmissionMailAttachment'
		);

		if ($objModule->formHybridSendSubmissionViaEmail && $objModule->formHybridSubmissionAvisotaMessage)
		{
			$arrDc['subpalettes']['formHybridSendSubmissionViaEmail'] = str_replace(
				$arrFieldsToHide, array_map(function() {return '';}, $arrFieldsToHide),
				$arrDc['subpalettes']['formHybridSendSubmissionViaEmail']
			);

			$arrDc['subpalettes']['formHybridSendSubmissionViaEmail'] = str_replace(
				'formHybridSubmissionAvisotaMessage', 'formHybridSubmissionAvisotaMessage,formHybridSubmissionAvisotaSalutationGroup',
				$arrDc['subpalettes']['formHybridSendSubmissionViaEmail']
			);
		}

		// confirmation
		$arrFieldsToHide = array
		(
			'formHybridConfirmationMailSender',
			'formHybridConfirmationMailSubject',
			'formHybridConfirmationMailText',
			'formHybridConfirmationMailTemplate',
			'formHybridConfirmationMailAttachment'
		);

		if ($objModule->formHybridSendConfirmationViaEmail && $objModule->formHybridConfirmationAvisotaMessage)
		{
			$arrDc['subpalettes']['formHybridSendConfirmationViaEmail'] = str_replace(
				$arrFieldsToHide, array_map(function() {return '';}, $arrFieldsToHide),
				$arrDc['subpalettes']['formHybridSendConfirmationViaEmail']
			);

			$arrDc['subpalettes']['formHybridSendConfirmationViaEmail'] = str_replace(
				'formHybridConfirmationAvisotaMessage', 'formHybridConfirmationAvisotaMessage,formHybridConfirmationAvisotaSalutationGroup',
				$arrDc['subpalettes']['formHybridSendConfirmationViaEmail']
			);
		}
	}
}
