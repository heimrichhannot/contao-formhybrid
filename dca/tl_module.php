<?php

$dc = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Subpalettes
 */
$dc['palettes']['__selector__'][] = 'formHybridAddDefaultValues';
$dc['palettes']['__selector__'][] = 'formHybridSendSubmissionViaEmail';
$dc['palettes']['__selector__'][] = 'formHybridSendSubmissionAsNotification';
$dc['palettes']['__selector__'][] = 'formHybridSendConfirmationViaEmail';
$dc['palettes']['__selector__'][] = 'formHybridSendConfirmationAsNotification';
$dc['palettes']['__selector__'][] = 'formHybridAddEditableRequired';
$dc['palettes']['__selector__'][] = 'formHybridAddDisplayedSubPaletteFields';
$dc['palettes']['__selector__'][] = 'formHybridAddFieldDependentRedirect';
$dc['palettes']['__selector__'][] = 'formHybridCustomSubmit';
$dc['palettes']['__selector__'][] = 'formHybridAddSubmitValues';
$dc['palettes']['__selector__'][] = 'formHybridAddPermanentFields';
array_insert($dc['palettes']['__selector__'], 0, array('formHybridViewMode')); // bug??  must be indexed before "type"

$dc['subpalettes']['formHybridViewMode_' . FORMHYBRID_VIEW_MODE_DEFAULT]  = 'formHybridTemplate';
$dc['subpalettes']['formHybridViewMode_' . FORMHYBRID_VIEW_MODE_READONLY] = 'formHybridReadonlyTemplate';

$dc['subpalettes']['formHybridAddDefaultValues']       = 'formHybridDefaultValues';
$dc['subpalettes']['formHybridSendSubmissionViaEmail'] =
	'formHybridSubmissionMailRecipient,formHybridSubmissionAvisotaMessage,formHybridSubmissionMailSender,formHybridSubmissionMailSubject,formHybridSubmissionMailText,formHybridSubmissionMailTemplate,formHybridSubmissionMailAttachment';

$dc['subpalettes']['formHybridSendSubmissionAsNotification'] = 'formHybridSubmissionNotification';

$dc['subpalettes']['formHybridSendConfirmationViaEmail'] =
	'formHybridConfirmationMailRecipientField,formHybridConfirmationAvisotaMessage,formHybridConfirmationMailSender,formHybridConfirmationMailSubject,formHybridConfirmationMailText,formHybridConfirmationMailTemplate,formHybridConfirmationMailAttachment';

$dc['subpalettes']['formHybridSendConfirmationAsNotification'] = 'formHybridConfirmationNotification';

$dc['subpalettes']['formHybridAddEditableRequired']          = 'formHybridEditableRequired';
$dc['subpalettes']['formHybridAddDisplayedSubPaletteFields'] = 'formHybridDisplayedSubPaletteFields';

$dc['subpalettes']['formHybridAddFieldDependentRedirect'] = 'formHybridFieldDependentRedirectConditions,formHybridFieldDependentRedirectJumpTo,formHybridFieldDependentRedirectKeepParams';

$dc['subpalettes']['formHybridCustomSubmit'] = 'formHybridSubmitLabel,formHybridSubmitClass';

$dc['subpalettes']['formHybridAddSubmitValues'] = 'formHybridSubmitValues';

$dc['subpalettes']['formHybridAddPermanentFields'] = 'formHybridPermanentFields';

/**
 * Callbacks
 */
$dc['config']['onload_callback'][] = array('tl_form_hybrid_module', 'modifyPalette');

/**
 * Fields
 */
$arrFields = array
(
	'formHybridDataContainer'                    => array
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
		'sql'              => "varchar(64) NOT NULL default ''",
	),
	'formHybridViewMode'                         => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridViewMode'],
		'default'          => FORMHYBRID_VIEW_MODE_DEFAULT,
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getViewModes'),
		'eval'             => array('tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true),
		'sql'              => "varchar(10) NOT NULL default 'default'",
		'reference'        => &$GLOBALS['TL_LANG']['tl_module']['reference'],
	),
	'formHybridEditable'                         => array
	(
		'inputType'        => 'checkboxWizard',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
		'options_callback' => array('tl_form_hybrid_module', 'getEditable'),
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr', 'mandatory' => true),
		'sql'              => "blob NULL",
	),
	'formHybridAddEditableRequired'              => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddEditableRequired'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridEditableRequired'                 => array
	(
		'inputType'        => 'select',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableRequired'],
		'options_callback' => array('tl_form_hybrid_module', 'getFields'),
		'exclude'          => true,
		'eval'             => array(
			'multiple'           => true,
			'chosen'             => true,
			'includeBlankOption' => true,
			'tl_class'           => 'w50 autoheight',
		),
		'sql'              => "blob NULL",
	),
	'formHybridAddDisplayedSubPaletteFields'     => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddDisplayedSubPaletteFields'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridDisplayedSubPaletteFields'        => array
	(
		'inputType'        => 'checkboxWizard',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridDisplayedSubPaletteFields'],
		'options_callback' => array('tl_form_hybrid_module', 'getSubPaletteFields'),
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight',),
		'sql'              => "blob NULL",
	),
	'formHybridEditableSkip'                     => array
	(
		'inputType'        => 'checkboxWizard',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableSkip'],
		'options_callback' => array('tl_form_hybrid_module', 'getFields'),  // all fields, cause formHybridDefaultValues will suffer from all fields
		'exclude'          => true,
		'eval'             => array('multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight'),
		'sql'              => "blob NULL",
	),
	'formHybridAddDefaultValues'                 => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddDefaultValues'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridDefaultValues'                    => array
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
			),
			'tl_class'     => 'clr long',
		),
		'sql'       => "blob NULL",
	),
	'formHybridAddSubmitValues'                  => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddSubmitValues'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridSubmitValues'                     => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmitValues'],
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
			),
			'tl_class'     => 'clr long',
		),
		'sql'       => "blob NULL",
	),
	'formHybridTemplate'                         => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridTemplate'],
		'default'          => 'formhybrid_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridTemplates'),
		'eval'             => array('tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true),
		'sql'              => "varchar(64) NOT NULL default 'formhybrid_default'",
	),
	'formHybridReadonlyTemplate'                 => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridReadonlyTemplate'],
		'default'          => 'formhybridreadonly_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridReadonlyTemplates'),
		'eval'             => array('tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true),
		'sql'              => "varchar(64) NOT NULL default 'formhybridreadonly_default'",
	),
	'formHybridCustomSubTemplates'               => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomSubTemplates'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridAction'                           => array
	(
		'label'      => &$GLOBALS['TL_LANG']['tl_module']['formHybridAction'],
		'exclude'    => true,
		'inputType'  => 'pageTree',
		'foreignKey' => 'tl_page.title',
		'eval'       => array('fieldType' => 'radio', 'tl_class' => 'clr'),
		'sql'        => "int(10) unsigned NOT NULL default '0'",
		'relation'   => array('type' => 'hasOne', 'load' => 'eager'),
	),
	'formHybridAddHashToAction'                  => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddHashToAction'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridCssClass'                         => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCssClass'],
		'exclude'   => true,
		'filter'    => false,
		'inputType' => 'text',
		'eval'      => array('maxlength' => 64, 'tl_class' => 'w50 clr'),
		'sql'       => "varchar(64) NOT NULL default ''",
	),
	'formHybridStartTemplate'                    => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridStartTemplate'],
		'default'          => 'formhybridStart_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridStartTemplates'),
		'eval'             => array('tl_class' => 'w50'),
		'sql'              => "varchar(64) NOT NULL default ''",
	),
	'formHybridStopTemplate'                     => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridStopTemplate'],
		'default'          => 'formhybridStop_default',
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridStopTemplates'),
		'eval'             => array('tl_class' => 'w50'),
		'sql'              => "varchar(64) NOT NULL default ''",
	),
	'formHybridAsync'                            => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAsync'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridSuccessMessage'                   => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSuccessMessage'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'textarea',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array('allowHtml' => true, 'tl_class' => 'clr', 'class' => 'monospace', 'rte' => 'ace|html', 'helpwizard' => true),
		'sql'         => "text NULL",
	),
	'formHybridSkipScrollingToSuccessMessage'    => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSkipScrollingToSuccessMessage'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridSendSubmissionAsNotification'     => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendSubmissionAsNotification'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr', 'helpwizard' => true),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridSubmissionNotification'           => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionNotification'],
		'exclude'          => true,
		'search'           => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getNoficiationMessages'),
		'eval'             => array('chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true),
		'sql'              => "int(10) unsigned NOT NULL default '0'",
	),
	'formHybridSendSubmissionViaEmail'           => array(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendSubmissionViaEmail'],
		'exclude'     => true,
		'inputType'   => 'checkbox',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('submitOnChange' => true, 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "char(1) NOT NULL default ''",
	),
	'formHybridSubmissionMailSender'             => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSender'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('mandatory' => false, 'maxlength' => 128, 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "varchar(128) NOT NULL default ''",
	),
	'formHybridSubmissionMailRecipient'          => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailRecipient'],
		'exclude'     => true,
		'search'      => true,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('mandatory' => true, 'maxlength' => 128, 'rgxp' => 'emails', 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "varchar(128) NOT NULL default ''",
	),
	'formHybridSubmissionMailSubject'            => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSubject'],
		'exclude'     => true,
		'search'      => true,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array(
			'mandatory'      => false,
			'maxlength'      => 128,
			'decodeEntities' => true,
			'tl_class'       => 'w50',
			'helpwizard'     => true,
		),
		'sql'         => "varchar(128) NOT NULL default ''",
	),
	'formHybridSubmissionMailText'               => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailText'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'textarea',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array('tl_class' => 'clr', 'decodeEntities' => true, 'alwaysSave' => true, 'helpwizard' => true),
		'sql'         => "text NULL",
	),
	'formHybridSubmissionMailTemplate'           => array(
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
	'formHybridSubmissionMailAttachment'         => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailAttachment'],
		'exclude'   => true,
		'inputType' => 'fileTree',
		'eval'      => array('multiple' => true, 'fieldType' => 'checkbox', 'files' => true),
		'sql'       => "blob NULL",
	),
	'formHybridSendConfirmationAsNotification'   => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendConfirmationAsNotification'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr', 'helpwizard' => true),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridConfirmationNotification'         => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationNotification'],
		'exclude'          => true,
		'search'           => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getNoficiationMessages'),
		'eval'             => array('chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true),
		'sql'              => "int(10) unsigned NOT NULL default '0'",
	),
	'formHybridSendConfirmationViaEmail'         => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendConfirmationViaEmail'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridConfirmationMailRecipientField'   => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailRecipientField'],
		'exclude'          => true,
		'search'           => true,
		'inputType'        => 'select',
		'options_callback' => array('tl_form_hybrid_module', 'getEmailFormFields'),
		'eval'             => array('mandatory' => true, 'chosen' => true, 'maxlength' => 128, 'tl_class' => 'w50 clr'),
		'sql'              => "varchar(128) NOT NULL default ''",
	),
	'formHybridConfirmationMailSender'           => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSender'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags',
		'eval'        => array('mandatory' => false, 'maxlength' => 128, 'tl_class' => 'w50 clr', 'helpwizard' => true),
		'sql'         => "varchar(128) NOT NULL default ''",
	),
	'formHybridConfirmationMailSubject'          => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSubject'],
		'exclude'     => true,
		'search'      => true,
		'inputType'   => 'text',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array(
			'mandatory'      => false,
			'maxlength'      => 128,
			'decodeEntities' => true,
			'tl_class'       => 'w50',
			'helpwizard'     => true,
		),
		'sql'         => "varchar(128) NOT NULL default ''",
	),
	'formHybridConfirmationMailText'             => array
	(
		'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailText'],
		'exclude'     => true,
		'filter'      => false,
		'inputType'   => 'textarea',
		'explanation' => 'formhybrid_inserttags_text',
		'eval'        => array('tl_class' => 'clr', 'decodeEntities' => true, 'alwaysSave' => true, 'helpwizard' => true),
		'sql'         => "text NULL",
	),
	'formHybridConfirmationMailTemplate'         => array(
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
	'formHybridConfirmationMailAttachment'       => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailAttachment'],
		'exclude'   => true,
		'inputType' => 'fileTree',
		'eval'      => array('multiple' => true, 'fieldType' => 'checkbox', 'files' => true),
		'sql'       => "blob NULL",
	),
	'formHybridAddFieldDependentRedirect'        => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddFieldDependentRedirect'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridFieldDependentRedirectKeepParams' => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectKeepParams'],
		'inputType' => 'text',
		'eval'      => array('tl_class' => 'w50', 'maxlength' => 64),
		'sql'       => "varchar(64) NOT NULL default ''",
	),
	'formHybridCustomSubmit'                     => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomSubmit'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridSubmitLabel'                      => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmitLabel'],
		'exclude'          => true,
		'inputType'        => 'select',
		'options_callback' => array('HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getSubmitLabels'),
		'eval'             => array('tl_class' => 'w50 clr', 'mandatory' => true, 'maxlength' => 64),
		'sql'              => "varchar(64) NOT NULL default ''",
	),
	'formHybridSubmitClass'                      => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmitClass'],
		'exclude'   => true,
		'inputType' => 'text',
		'eval'      => array('maxlength' => 64, 'tl_class' => 'w50'),
		'sql'       => "varchar(64) NOT NULL default ''",
	),
	'formHybridAddPermanentFields'               => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddPermanentFields'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default ''",
	),
	'formHybridPermanentFields'                  => array
	(
		'inputType'        => 'select',
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridPermanentFields'],
		'options_callback' => array('tl_form_hybrid_module', 'getEditable'),
		'exclude'          => true,
		'eval'             => array(
			'multiple'           => true,
			'includeBlankOption' => true,
			'chosen'             => true,
			'tl_class'           => 'w50',
			'mandatory'          => true,
		),
		'sql'              => "blob NULL",
	),
	'formHybridResetAfterSubmission'             => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridResetAfterSubmission'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('tl_class' => 'w50'),
		'sql'       => "char(1) NOT NULL default '1'",
	),
	'formHybridJumpToPreserveParams'      => array(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridJumpToPreserveParams'],
		'exclude'   => true,
		'inputType' => 'text',
		'eval'      => array('tl_class' => 'w50', 'maxlength' => 128),
		'sql'       => "varchar(128) NOT NULL default ''",
	),
);

// conditions for the field depending redirect
$arrFields['formHybridFieldDependentRedirectConditions']          = $arrFields['formHybridDefaultValues'];
$arrFields['formHybridFieldDependentRedirectConditions']['label'] =
	&$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectConditions'];
unset($arrFields['formHybridFieldDependentRedirectConditions']['eval']['columnFields']['label']);
unset($arrFields['formHybridFieldDependentRedirectConditions']['eval']['columnFields']['hidden']);

$arrFields['formHybridFieldDependentRedirectJumpTo']                      = $dc['fields']['jumpTo'];
$arrFields['formHybridFieldDependentRedirectJumpTo']['label']             =
	&$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectJumpTo'];
$arrFields['formHybridFieldDependentRedirectJumpTo']['eval']['mandatory'] = true;
$arrFields['formHybridFieldDependentRedirectJumpTo']['eval']['tl_class']  = 'w50';

if (in_array('avisota-core', \ModuleLoader::getActive())) {
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
			'chosen'             => true,
			'submitOnChange'     => true,
		),
		'sql'              => "char(36) NOT NULL default ''",
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
			'chosen'             => true,
		),
		'sql'              => "char(36) NOT NULL default ''",
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
			'chosen'             => true,
			'submitOnChange'     => true,
		),
		'sql'              => "char(36) NOT NULL default ''",
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
			'chosen'             => true,
		),
		'sql'              => "char(36) NOT NULL default ''",
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
	
	public static function getEditable($objDc)
	{
		return \HeimrichHannot\FormHybrid\FormHelper::getEditableFields($objDc->activeRecord->formHybridDataContainer);
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
	
	
	// no type because of multicolumnwizard not supporting passing a dc to an options_callback :-(
	public static function getFields($objDc)
	{
		if ($objDc->activeRecord->formHybridDataContainer) {
			return \HeimrichHannot\Haste\Dca\General::getFields($objDc->activeRecord->formHybridDataContainer, false);
		}
	}
	
	public function getSubPaletteFields(\DataContainer $dc)
	{
		$strTable            = $dc->activeRecord->formHybridDataContainer;
		$arrSubPalettes      = array();
		$arrSubPaletteFields = array();
		$arrFields           = array();
		
		\Controller::loadDataContainer($strTable);
		
		$arrSubPalettes = $GLOBALS['TL_DCA'][$strTable]['subpalettes'];
		if (empty($arrSubPalettes)) {
			return;
		}
		
		foreach ($arrSubPalettes as $strName => $strPalette) {
			$arrSubPaletteFields = \HeimrichHannot\FormHybrid\FormHelper::getPaletteFields($strTable, $arrSubPalettes[$strName]);
			if (empty($arrSubPaletteFields)) {
				return;
			}
			
			$arrFields = array_merge($arrFields, $arrSubPaletteFields);
		}
		
		return $arrFields;
	}
	
	public function getNoficiationMessages(\DataContainer $dc)
	{
		$arrOptions = array();
		
		$objMessages = NotificationCenter\Model\Message::findAll();
		
		if ($objMessages === null) {
			return $arrOptions;
		}
		
		while ($objMessages->next()) {
			if (($objNotification = $objMessages->getRelated('pid')) === null) {
				continue;
			}
			
			$arrOptions[$objNotification->title][$objMessages->id] = $objMessages->title;
		}
		
		return $arrOptions;
	}
	
	public function modifyPalette()
	{
		if (!in_array('avisota-core', \ModuleLoader::getActive())) {
			return;
		}
		
		$objModule = \ModuleModel::findByPk(\Input::get('id'));
		$arrDc     = &$GLOBALS['TL_DCA']['tl_module'];
		
		// submission
		$arrFieldsToHide = array
		(
			'formHybridSubmissionMailSender',
			'formHybridSubmissionMailSubject',
			'formHybridSubmissionMailText',
			'formHybridSubmissionMailTemplate',
			'formHybridSubmissionMailAttachment',
		);
		
		if ($objModule->formHybridSendSubmissionViaEmail && $objModule->formHybridSubmissionAvisotaMessage) {
			$arrDc['subpalettes']['formHybridSendSubmissionViaEmail'] = str_replace(
				$arrFieldsToHide,
				array_map(
					function () {
						return '';
					},
					$arrFieldsToHide
				),
				$arrDc['subpalettes']['formHybridSendSubmissionViaEmail']
			);
			
			$arrDc['subpalettes']['formHybridSendSubmissionViaEmail'] = str_replace(
				'formHybridSubmissionAvisotaMessage',
				'formHybridSubmissionAvisotaMessage,formHybridSubmissionAvisotaSalutationGroup',
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
			'formHybridConfirmationMailAttachment',
		);
		
		if ($objModule->formHybridSendConfirmationViaEmail && $objModule->formHybridConfirmationAvisotaMessage) {
			$arrDc['subpalettes']['formHybridSendConfirmationViaEmail'] = str_replace(
				$arrFieldsToHide,
				array_map(
					function () {
						return '';
					},
					$arrFieldsToHide
				),
				$arrDc['subpalettes']['formHybridSendConfirmationViaEmail']
			);
			
			$arrDc['subpalettes']['formHybridSendConfirmationViaEmail'] = str_replace(
				'formHybridConfirmationAvisotaMessage',
				'formHybridConfirmationAvisotaMessage,formHybridConfirmationAvisotaSalutationGroup',
				$arrDc['subpalettes']['formHybridSendConfirmationViaEmail']
			);
		}
	}
}
