<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Subpalettes
 */
$arrDca['palettes']['__selector__'][] = 'formHybridAddDefaultValues';
$arrDca['palettes']['__selector__'][] = 'formHybridSendSubmissionViaEmail';
$arrDca['palettes']['__selector__'][] = 'formHybridSendSubmissionAsNotification';
$arrDca['palettes']['__selector__'][] = 'formHybridSendConfirmationViaEmail';
$arrDca['palettes']['__selector__'][] = 'formHybridSendConfirmationAsNotification';
$arrDca['palettes']['__selector__'][] = 'formHybridAddEditableRequired';
$arrDca['palettes']['__selector__'][] = 'formHybridAddReadOnly';
$arrDca['palettes']['__selector__'][] = 'formHybridAddDisplayedSubPaletteFields';
$arrDca['palettes']['__selector__'][] = 'formHybridAddFieldDependentRedirect';
$arrDca['palettes']['__selector__'][] = 'formHybridCustomSubmit';
$arrDca['palettes']['__selector__'][] = 'formHybridAddPermanentFields';
$arrDca['palettes']['__selector__'][] = 'formHybridUseCustomFormId';
$arrDca['palettes']['__selector__'][] = 'formHybridAllowIdAsGetParameter';
$arrDca['palettes']['__selector__'][] = 'formHybridAddHashToAction';
$arrDca['palettes']['__selector__'][] = 'formHybridExportAfterSubmission';
$arrDca['palettes']['__selector__'][] = 'formHybridAddExportButton';
$arrDca['palettes']['__selector__'][] = 'formHybridAddOptIn';

array_insert($arrDca['palettes']['__selector__'], 0, ['formHybridViewMode']); // bug??  must be indexed before "type"


/**
 * Subpalettes
 */
$arrDca['subpalettes']['formHybridViewMode_' . FORMHYBRID_VIEW_MODE_DEFAULT]  = 'formHybridTemplate';
$arrDca['subpalettes']['formHybridViewMode_' . FORMHYBRID_VIEW_MODE_READONLY] = 'formHybridReadonlyTemplate';
$arrDca['subpalettes']['formHybridAddDefaultValues']                          = 'formHybridDefaultValues';
$arrDca['subpalettes']['formHybridExportAfterSubmission']                     = 'formHybridExportConfigs';
$arrDca['subpalettes']['formHybridSendSubmissionViaEmail']                    =
    'formHybridSubmissionMailRecipient,formHybridSubmissionAvisotaMessage,formHybridSubmissionMailSender,formHybridSubmissionMailSubject,formHybridSubmissionMailText,formHybridSubmissionMailTemplate,formHybridSubmissionMailAttachment';
$arrDca['subpalettes']['formHybridSendSubmissionAsNotification']              = 'formHybridSubmissionNotification';
$arrDca['subpalettes']['formHybridSendConfirmationViaEmail']                  =
    'formHybridConfirmationMailRecipientField,formHybridConfirmationAvisotaMessage,formHybridConfirmationMailSender,formHybridConfirmationMailSubject,formHybridConfirmationMailText,formHybridConfirmationMailTemplate,formHybridConfirmationMailAttachment';
$arrDca['subpalettes']['formHybridSendConfirmationAsNotification']            = 'formHybridConfirmationNotification';
$arrDca['subpalettes']['formHybridAddEditableRequired']                       = 'formHybridEditableRequired';
$arrDca['subpalettes']['formHybridAddReadOnly']                               = 'formHybridReadOnly';
$arrDca['subpalettes']['formHybridAddDisplayedSubPaletteFields']              = 'formHybridDisplayedSubPaletteFields';
$arrDca['subpalettes']['formHybridAddFieldDependentRedirect']                 =
    'formHybridFieldDependentRedirectConditions,formHybridFieldDependentRedirectJumpTo,formHybridFieldDependentRedirectKeepParams';
$arrDca['subpalettes']['formHybridCustomSubmit']                              = 'formHybridSubmitLabel,formHybridSubmitClass';
$arrDca['subpalettes']['formHybridAddPermanentFields']                        = 'formHybridPermanentFields';
$arrDca['subpalettes']['formHybridUseCustomFormId']                           = 'formHybridCustomFormId';
$arrDca['subpalettes']['formHybridAllowIdAsGetParameter']                     = 'formHybridIdGetParameter,formHybridAppendIdToUrlOnCreation';
$arrDca['subpalettes']['formHybridAddHashToAction']                           = 'formHybridCustomHash';
$arrDca['subpalettes']['formHybridAddExportButton']                           = 'formHybridExportConfigs';
$arrDca['subpalettes']['formHybridAddOptIn']                                  = 'formHybridOptInExplanation,formHybridOptInSuccessMessage,formHybridOptInNotification';

/**
 * Callbacks
 */
$arrDca['config']['onload_callback'][] = ['tl_form_hybrid_module', 'modifyPalette'];

/**
 * Fields
 */
$arrFields = [
    'formHybridDataContainer'                    => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridDataContainer'],
        'options_callback' => ['tl_form_hybrid_module', 'getDataContainers'],
        'eval'             => [
            'chosen'             => true,
            'submitOnChange'     => true,
            'includeBlankOption' => true,
            'tl_class'           => 'w50 clr',
            'mandatory'          => true,
        ],
        'exclude'          => true,
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
    'formHybridViewMode'                         => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridViewMode'],
        'default'          => FORMHYBRID_VIEW_MODE_DEFAULT,
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getViewModes'],
        'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
        'sql'              => "varchar(10) NOT NULL default 'default'",
        'reference'        => &$GLOBALS['TL_LANG']['tl_module']['reference'],
    ],
    'formHybridEditable'                         => [
        'inputType'        => 'checkboxWizard',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
        'options_callback' => ['tl_form_hybrid_module', 'getEditable'],
        'exclude'          => true,
        'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr', 'mandatory' => true],
        'sql'              => "blob NULL",
    ],
    'formHybridAddEditableRequired'              => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddEditableRequired'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridEditableRequired'                 => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableRequired'],
        'options_callback' => ['tl_form_hybrid_module', 'getFields'],
        'exclude'          => true,
        'eval'             => [
            'multiple'           => true,
            'chosen'             => true,
            'includeBlankOption' => true,
            'tl_class'           => 'w50 autoheight',
        ],
        'sql'              => "blob NULL",
    ],
    'formHybridAddReadOnly'                      => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddReadOnly'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridReadOnly'                         => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridReadOnly'],
        'options_callback' => ['tl_form_hybrid_module', 'getFields'],
        'exclude'          => true,
        'eval'             => [
            'multiple'           => true,
            'chosen'             => true,
            'includeBlankOption' => true,
            'tl_class'           => 'w50 autoheight',
        ],
        'sql'              => "blob NULL",
    ],
    'formHybridAddDisplayedSubPaletteFields'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddDisplayedSubPaletteFields'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridDisplayedSubPaletteFields'        => [
        'inputType'        => 'checkboxWizard',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridDisplayedSubPaletteFields'],
        'options_callback' => ['tl_form_hybrid_module', 'getSubPaletteFields'],
        'exclude'          => true,
        'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight',],
        'sql'              => "blob NULL",
    ],
    'formHybridEditableSkip'                     => [
        'inputType'        => 'checkboxWizard',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableSkip'],
        'options_callback' => ['tl_form_hybrid_module', 'getFields'],  // all fields, cause formHybridDefaultValues will suffer from all fields
        'exclude'          => true,
        'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight'],
        'sql'              => "blob NULL",
    ],
    'formHybridAddDefaultValues'                 => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddDefaultValues'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridDefaultValues'                    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues'],
        'exclude'   => true,
        'inputType' => 'multiColumnWizard',
        'eval'      => [
            'columnFields' => [
                'field' => [
                    'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['field'],
                    'exclude'          => true,
                    'inputType'        => 'select',
                    'options_callback' => [
                        'tl_form_hybrid_module',
                        'getFields',
                    ],
                    'eval'             => ['style' => 'width: 150px', 'chosen' => true, 'includeBlankOption' => true],
                ],
                'value' => [
                    'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['value'],
                    'exclude'   => true,
                    'inputType' => 'text',
                    'eval'      => ['style' => 'width: 100px'],
                ],
                'label' => [
                    'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['label'],
                    'exclude'   => true,
                    'inputType' => 'text',
                    'eval'      => ['style' => 'width: 350px', 'allowHtml' => true],
                ],
            ],
            'tl_class'     => 'clr long',
        ],
        'sql'       => "blob NULL",
    ],
    'formHybridTemplate'                         => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridTemplate'],
        'default'          => 'formhybrid_default',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridTemplates'],
        'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true],
        'sql'              => "varchar(64) NOT NULL default 'formhybrid_default'",
    ],
    'formHybridReadonlyTemplate'                 => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridReadonlyTemplate'],
        'default'          => 'formhybridreadonly_default',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridReadonlyTemplates'],
        'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true],
        'sql'              => "varchar(64) NOT NULL default 'formhybridreadonly_default'",
    ],
    'formHybridCustomSubTemplates'               => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomSubTemplates'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridAction'                           => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['formHybridAction'],
        'exclude'    => true,
        'inputType'  => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval'       => ['fieldType' => 'radio', 'tl_class' => 'clr'],
        'sql'        => "int(10) unsigned NOT NULL default '0'",
        'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
    ],
    'formHybridAddHashToAction'                  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddHashToAction'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridCustomHash'                       => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomHash'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
        'sql'       => "varchar(255) NOT NULL default ''",
    ],
    'removeAutoItemFromAction'                   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['removeAutoItemFromAction'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridCssClass'                         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCssClass'],
        'exclude'   => true,
        'filter'    => false,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 64, 'tl_class' => 'w50 clr'],
        'sql'       => "varchar(64) NOT NULL default ''",
    ],
    'formHybridStartTemplate'                    => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridStartTemplate'],
        'default'          => 'formhybridStart_default',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridStartTemplates'],
        'eval'             => ['tl_class' => 'w50'],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
    'formHybridStopTemplate'                     => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridStopTemplate'],
        'default'          => 'formhybridStop_default',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getFormHybridStopTemplates'],
        'eval'             => ['tl_class' => 'w50'],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
    'formHybridAsync'                            => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAsync'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridSuccessMessage'                   => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSuccessMessage'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'textarea',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => ['allowHtml' => true, 'tl_class' => 'clr', 'class' => 'monospace', 'rte' => 'ace|html', 'helpwizard' => true],
        'sql'         => "text NULL",
    ],
    'formHybridSkipScrollingToSuccessMessage'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSkipScrollingToSuccessMessage'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridSendSubmissionAsNotification'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendSubmissionAsNotification'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr', 'helpwizard' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridSubmissionNotification'           => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionNotification'],
        'exclude'          => true,
        'search'           => true,
        'inputType'        => 'select',
        'options_callback' => ['tl_form_hybrid_module', 'getNoficiationMessages'],
        'eval'             => ['chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql'              => "int(10) unsigned NOT NULL default '0'",
    ],
    'formHybridSendSubmissionViaEmail'           => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendSubmissionViaEmail'],
        'exclude'     => true,
        'inputType'   => 'checkbox',
        'explanation' => 'formhybrid_inserttags',
        'eval'        => ['submitOnChange' => true, 'tl_class' => 'w50 clr', 'helpwizard' => true],
        'sql'         => "char(1) NOT NULL default ''",
    ],
    'formHybridSubmissionMailSender'             => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSender'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'text',
        'explanation' => 'formhybrid_inserttags',
        'eval'        => ['mandatory' => false, 'maxlength' => 128, 'tl_class' => 'w50 clr', 'helpwizard' => true],
        'sql'         => "varchar(128) NOT NULL default ''",
    ],
    'formHybridSubmissionMailRecipient'          => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailRecipient'],
        'exclude'     => true,
        'search'      => true,
        'inputType'   => 'text',
        'explanation' => 'formhybrid_inserttags',
        'eval'        => ['mandatory' => true, 'maxlength' => 128, 'rgxp' => 'emails', 'tl_class' => 'w50 clr', 'helpwizard' => true],
        'sql'         => "varchar(128) NOT NULL default ''",
    ],
    'formHybridSubmissionMailSubject'            => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSubject'],
        'exclude'     => true,
        'search'      => true,
        'inputType'   => 'text',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => [
            'mandatory'      => false,
            'maxlength'      => 128,
            'decodeEntities' => true,
            'tl_class'       => 'w50',
            'helpwizard'     => true,
        ],
        'sql'         => "varchar(128) NOT NULL default ''",
    ],
    'formHybridSubmissionMailText'               => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailText'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'textarea',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => ['tl_class' => 'clr', 'decodeEntities' => true, 'alwaysSave' => true, 'helpwizard' => true],
        'sql'         => "text NULL",
    ],
    'formHybridSubmissionMailTemplate'           => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailTemplate'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'fileTree',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => [
            'helpwizard' => true,
            'files'      => true,
            'fieldType'  => 'radio',
            'extensions' => 'htm,html,txt,tpl',
        ],
        'sql'         => "binary(16) NULL",
    ],
    'formHybridSubmissionMailAttachment'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailAttachment'],
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => ['multiple' => true, 'fieldType' => 'checkbox', 'files' => true],
        'sql'       => "blob NULL",
    ],
    'formHybridSendConfirmationAsNotification'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendConfirmationAsNotification'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr', 'helpwizard' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridConfirmationNotification'         => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationNotification'],
        'exclude'          => true,
        'search'           => true,
        'inputType'        => 'select',
        'options_callback' => ['tl_form_hybrid_module', 'getNoficiationMessages'],
        'eval'             => ['chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql'              => "int(10) unsigned NOT NULL default '0'",
    ],
    'formHybridSendConfirmationViaEmail'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSendConfirmationViaEmail'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridConfirmationMailRecipientField'   => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailRecipientField'],
        'exclude'          => true,
        'search'           => true,
        'inputType'        => 'select',
        'options_callback' => ['tl_form_hybrid_module', 'getEmailFormFields'],
        'eval'             => ['mandatory' => true, 'chosen' => true, 'maxlength' => 128, 'tl_class' => 'w50 clr'],
        'sql'              => "varchar(128) NOT NULL default ''",
    ],
    'formHybridConfirmationMailSender'           => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSender'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'text',
        'explanation' => 'formhybrid_inserttags',
        'eval'        => ['mandatory' => false, 'maxlength' => 128, 'tl_class' => 'w50 clr', 'helpwizard' => true],
        'sql'         => "varchar(128) NOT NULL default ''",
    ],
    'formHybridConfirmationMailSubject'          => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSubject'],
        'exclude'     => true,
        'search'      => true,
        'inputType'   => 'text',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => [
            'mandatory'      => false,
            'maxlength'      => 128,
            'decodeEntities' => true,
            'tl_class'       => 'w50',
            'helpwizard'     => true,
        ],
        'sql'         => "varchar(128) NOT NULL default ''",
    ],
    'formHybridConfirmationMailText'             => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailText'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'textarea',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => ['tl_class' => 'clr', 'decodeEntities' => true, 'alwaysSave' => true, 'helpwizard' => true],
        'sql'         => "text NULL",
    ],
    'formHybridConfirmationMailTemplate'         => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailTemplate'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'fileTree',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => [
            'helpwizard' => true,
            'files'      => true,
            'fieldType'  => 'radio',
            'extensions' => 'htm,html,txt,tpl',
        ],
        'sql'         => "binary(16) NULL",
    ],
    'formHybridConfirmationMailAttachment'       => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailAttachment'],
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => ['multiple' => true, 'fieldType' => 'checkbox', 'files' => true],
        'sql'       => "blob NULL",
    ],
    'formHybridAddFieldDependentRedirect'        => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddFieldDependentRedirect'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridFieldDependentRedirectKeepParams' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectKeepParams'],
        'inputType' => 'text',
        'eval'      => ['tl_class' => 'w50', 'maxlength' => 64],
        'sql'       => "varchar(64) NOT NULL default ''",
    ],
    'formHybridCustomSubmit'                     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomSubmit'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridSubmitLabel'                      => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmitLabel'],
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\ModuleBackend', 'getSubmitLabels'],
        'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'maxlength' => 64],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
    'formHybridSubmitClass'                      => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmitClass'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['maxlength' => 64, 'tl_class' => 'w50'],
        'sql'       => "varchar(64) NOT NULL default ''",
    ],
    'formHybridAddPermanentFields'               => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddPermanentFields'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridPermanentFields'                  => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridPermanentFields'],
        'options_callback' => ['tl_form_hybrid_module', 'getEditable'],
        'exclude'          => true,
        'eval'             => [
            'multiple'           => true,
            'includeBlankOption' => true,
            'chosen'             => true,
            'tl_class'           => 'w50',
            'mandatory'          => true,
        ],
        'sql'              => "blob NULL",
    ],
    'formHybridSingleSubmission'                 => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridSingleSubmission'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridResetAfterSubmission'             => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridResetAfterSubmission'],
        'exclude'   => true,
        'default'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridJumpToPreserveParams'             => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridJumpToPreserveParams'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['tl_class' => 'w50', 'maxlength' => 128],
        'sql'       => "varchar(128) NOT NULL default ''",
    ],
    'formHybridUseCustomFormId'                  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridUseCustomFormId'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridCustomFormId'                     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomFormId'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['tl_class' => 'w50', 'maxlength' => 50],
        'sql'       => "varchar(50) NOT NULL default ''",
    ],
    'formHybridAllowIdAsGetParameter'            => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAllowIdAsGetParameter'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridIdGetParameter'                   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridIdGetParameter'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['tl_class' => 'clr w50', 'maxlength' => 64, 'mandatory' => true],
        'sql'       => "varchar(64) NOT NULL default 'id'",
    ],
    'formHybridAppendIdToUrlOnCreation'          => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAppendIdToUrlOnCreation'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridTransformGetParamsToHiddenFields' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridTransformGetParamsToHiddenFields'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridCreatePdfAfterSubmission'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddEditableRequired'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridEnableAutoComplete'               => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridEnableAutoComplete'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridAddExportButton'                  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddExportButton'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridAddOptIn'                         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddOptIn'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridOptInNotification'                => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptInNotification'],
        'exclude'          => true,
        'search'           => true,
        'inputType'        => 'select',
        'options_callback' => ['tl_form_hybrid_module', 'getOptInMessages'],
        'eval'             => ['chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql'              => "int(10) unsigned NOT NULL default '0'",
    ],
    'formHybridOptInExplanation'                 => [
        'inputType' => 'explanation',
        'eval'      => [
            'text'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptInExplanation'], // this is a string, not an array
            'class'    => 'tl_info', // all contao message css classes are possible
            'tl_class' => 'long clr',
        ],
    ],
    'formHybridOptInSuccessMessage'                   => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptInSuccessMessage'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'textarea',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => ['allowHtml' => true, 'tl_class' => 'clr', 'class' => 'monospace', 'rte' => 'ace|html', 'helpwizard' => true],
        'sql'         => "text NULL",
    ],
];

// conditions for the field depending redirect
$arrFields['formHybridFieldDependentRedirectConditions']          = $arrFields['formHybridDefaultValues'];
$arrFields['formHybridFieldDependentRedirectConditions']['label'] = &$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectConditions'];
unset($arrFields['formHybridFieldDependentRedirectConditions']['eval']['columnFields']['label']);
unset($arrFields['formHybridFieldDependentRedirectConditions']['eval']['columnFields']['hidden']);

$arrFields['formHybridFieldDependentRedirectJumpTo']                      = $arrDca['fields']['jumpTo'];
$arrFields['formHybridFieldDependentRedirectJumpTo']['label']             =
	&$GLOBALS['TL_LANG']['tl_module']['formHybridFieldDependentRedirectJumpTo'];
$arrFields['formHybridFieldDependentRedirectJumpTo']['eval']['mandatory'] = true;
$arrFields['formHybridFieldDependentRedirectJumpTo']['eval']['tl_class']  = 'w50';

if (in_array('avisota-core', \ModuleLoader::getActive()))
{
    $arrFields['formHybridSubmissionAvisotaMessage'] = [
        'exclude'          => true,
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaMessage'],
        'inputType'        => 'select',
        'options_callback' => \ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEventCallbackFactory::createCallback(
            \Avisota\Contao\Message\Core\MessageEvents::CREATE_BOILERPLATE_MESSAGE_OPTIONS,
            'Avisota\Contao\Core\Event\CreateOptionsEvent'
        ),
        'eval'             => [
            'includeBlankOption' => true,
            'tl_class'           => 'w50 clr',
            'chosen'             => true,
            'submitOnChange'     => true,
        ],
        'sql'              => "char(36) NOT NULL default ''",
    ];

    $arrFields['formHybridSubmissionAvisotaSalutationGroup'] = [
        'exclude'          => true,
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaSalutationGroup'],
        'inputType'        => 'select',
        'options_callback' => ['tl_form_hybrid_module', 'getSalutationGroupOptions'],
        'eval'             => [
            'includeBlankOption' => true,
            'tl_class'           => 'w50',
            'chosen'             => true,
        ],
        'sql'              => "char(36) NOT NULL default ''",
    ];

    $arrFields['formHybridConfirmationAvisotaMessage'] = [
        'exclude'          => true,
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaMessage'],
        'inputType'        => 'select',
        'options_callback' => \ContaoCommunityAlliance\Contao\Events\CreateOptions\CreateOptionsEventCallbackFactory::createCallback(
            \Avisota\Contao\Message\Core\MessageEvents::CREATE_BOILERPLATE_MESSAGE_OPTIONS,
            'Avisota\Contao\Core\Event\CreateOptionsEvent'
        ),
        'eval'             => [
            'includeBlankOption' => true,
            'tl_class'           => 'w50 clr',
            'chosen'             => true,
            'submitOnChange'     => true,
        ],
        'sql'              => "char(36) NOT NULL default ''",
    ];

    $arrFields['formHybridConfirmationAvisotaSalutationGroup'] = [
        'exclude'          => true,
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaSalutationGroup'],
        'inputType'        => 'select',
        'options_callback' => ['tl_form_hybrid_module', 'getSalutationGroupOptions'],
        'eval'             => [
            'includeBlankOption' => true,
            'tl_class'           => 'w50',
            'chosen'             => true,
        ],
        'sql'              => "char(36) NOT NULL default ''",
    ];
}

if (in_array('exporter', \ModuleLoader::getActive()))
{
    $arrFields['formHybridExportAfterSubmission'] = [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridExportAfterSubmission'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ];

    $arrFields['formHybridExportConfigs'] = [
        'label'        => &$GLOBALS['TL_LANG']['tl_module']['formHybridExportConfigs'],
        'exclude'      => true,
        'inputType'    => 'fieldpalette',
        'foreignKey'   => 'tl_fieldpalette.id',
        'relation'     => ['type' => 'hasMany', 'load' => 'eager'],
        'sql'          => "blob NULL",
        'eval'         => ['tl_class' => 'clr'],
        'fieldpalette' => [
            'config'   => [
                'hidePublished' => true,
            ],
            'list'     => [
                'label' => [
                    'fields' => ['formhybrid_formHybridExportConfigs_config'],
                    'format' => '%s',
                ],
            ],
            'palettes' => [
                'default' => 'formhybrid_formHybridExportConfigs_config,formhybrid_formHybridExportConfigs_entityField',
            ],
            'fields'   => [
                'formhybrid_formHybridExportConfigs_config'      => [
                    'label'            => &$GLOBALS['TL_LANG']['tl_module']['formhybrid_formHybridExportConfigs_config'],
                    'exclude'          => true,
                    'filter'           => true,
                    'inputType'        => 'select',
                    'options_callback' => ['tl_form_hybrid_module', 'getFormHybridExportConfigsAsOptions'],
                    'eval'             => ['tl_class' => 'long clr', 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true],
                    'sql'              => "int(10) unsigned NOT NULL default '0'",
                ],
                'formhybrid_formHybridExportConfigs_entityField' => [
                    'label'            => &$GLOBALS['TL_LANG']['tl_module']['formhybrid_formHybridExportConfigs_entityField'],
                    'exclude'          => true,
                    'inputType'        => 'select',
                    'options_callback' => ['tl_form_hybrid_module', 'getEditableForExport'],
                    'eval'             => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
                    'sql'              => "varchar(64) NOT NULL default ''",
                ],
            ],
        ],
    ];
}

$arrDca['fields'] = array_merge($arrDca['fields'], $arrFields);

class tl_form_hybrid_module extends \Backend
{

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
        return \HeimrichHannot\FormHybrid\FormHelper::getEditableFields($objDc->activeRecord->formHybridDataContainer);
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

        $objNotifications = NotificationCenter\Model\Notification::findByType(\HeimrichHannot\FormHybrid\FormHybrid::NOTIFICATION_TYPE_FORM_OPT_IN);

        if ($objNotifications === null)
        {
            return $arrOptions;
        }

        $objMessages = NotificationCenter\Model\Message::findBy(['pid IN (' . implode(',', array_map('intval', $objNotifications->fetchEach('id'))) . ')'], []);

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

        $objMessages = NotificationCenter\Model\Message::findAll();

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

    public function modifyPalette()
    {
        if (!in_array('avisota-core', \ModuleLoader::getActive()))
        {
            return;
        }

        $objModule = \ModuleModel::findByPk(\HeimrichHannot\Request\Request::getGet('id'));
        $arrDc     = &$GLOBALS['TL_DCA']['tl_module'];

        // submission
        $arrFieldsToHide = [
            'formHybridSubmissionMailSender',
            'formHybridSubmissionMailSubject',
            'formHybridSubmissionMailText',
            'formHybridSubmissionMailTemplate',
            'formHybridSubmissionMailAttachment',
        ];

        if ($objModule->formHybridSendSubmissionViaEmail && $objModule->formHybridSubmissionAvisotaMessage)
        {
            $arrDc['subpalettes']['formHybridSendSubmissionViaEmail'] = str_replace(
                $arrFieldsToHide,
                array_map(
                    function ()
                    {
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
        $arrFieldsToHide = [
            'formHybridConfirmationMailSender',
            'formHybridConfirmationMailSubject',
            'formHybridConfirmationMailText',
            'formHybridConfirmationMailTemplate',
            'formHybridConfirmationMailAttachment',
        ];

        if ($objModule->formHybridSendConfirmationViaEmail && $objModule->formHybridConfirmationAvisotaMessage)
        {
            $arrDc['subpalettes']['formHybridSendConfirmationViaEmail'] = str_replace(
                $arrFieldsToHide,
                array_map(
                    function ()
                    {
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

    public static function getFormHybridExportConfigsAsOptions()
    {
        return \HeimrichHannot\Exporter\Backend::getConfigsAsOptions(\HeimrichHannot\FormHybrid\FormHybrid::EXPORT_TYPE_FORMHYBRID);
    }
}
