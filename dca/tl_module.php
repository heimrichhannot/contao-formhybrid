<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Subpalettes
 */
$arrDca['palettes']['__selector__'][] = 'formHybridAddDefaultValues';
$arrDca['palettes']['__selector__'][] = 'formHybridSendSubmissionAsNotification';
$arrDca['palettes']['__selector__'][] = 'formHybridSendConfirmationAsNotification';
$arrDca['palettes']['__selector__'][] = 'formHybridAddEditableRequired';
$arrDca['palettes']['__selector__'][] = 'formHybridAddReadOnly';
$arrDca['palettes']['__selector__'][] = 'formHybridAddDisplayedSubPaletteFields';
$arrDca['palettes']['__selector__'][] = 'formHybridAddFieldDependentRedirect';
$arrDca['palettes']['__selector__'][] = 'formHybridCustomSubmit';
$arrDca['palettes']['__selector__'][] = 'formHybridAddPermanentFields';
$arrDca['palettes']['__selector__'][] = 'formHybridUseCustomFormId';
$arrDca['palettes']['__selector__'][] = 'formHybridUseCustomFormIdSuffix';
$arrDca['palettes']['__selector__'][] = 'formHybridAllowIdAsGetParameter';
$arrDca['palettes']['__selector__'][] = 'formHybridAddHashToAction';
$arrDca['palettes']['__selector__'][] = 'formHybridExportAfterSubmission';
$arrDca['palettes']['__selector__'][] = 'formHybridAddExportButton';
$arrDca['palettes']['__selector__'][] = 'formHybridAddOptIn';
$arrDca['palettes']['__selector__'][] = 'formHybridAddOptOut';
$arrDca['palettes']['__selector__'][] = 'formHybridAddPrivacyProtocolEntry';
$arrDca['palettes']['__selector__'][] = 'formHybridOptInAddPrivacyProtocolEntry';
$arrDca['palettes']['__selector__'][] = 'formHybridfilterTokenFields';
$arrDca['palettes']['__selector__'][] = 'formHybridAddGetParameter';

array_insert($arrDca['palettes']['__selector__'], 0, ['formHybridViewMode']); // bug??  must be indexed before "type"


/**
 * Subpalettes
 */
$arrDca['subpalettes']['formHybridViewMode_' . FORMHYBRID_VIEW_MODE_DEFAULT]  = 'formHybridTemplate';
$arrDca['subpalettes']['formHybridViewMode_' . FORMHYBRID_VIEW_MODE_READONLY] = 'formHybridReadonlyTemplate';
$arrDca['subpalettes']['formHybridAddDefaultValues']                          = 'formHybridDefaultValues';
$arrDca['subpalettes']['formHybridExportAfterSubmission']                     = 'formHybridExportConfigs';
$arrDca['subpalettes']['formHybridAddPrivacyProtocolEntry']                   = 'formHybridPrivacyProtocolArchive,formHybridPrivacyProtocolEntryType,formHybridPrivacyProtocolDescription,formHybridPrivacyProtocolFieldMapping';
$arrDca['subpalettes']['formHybridSendSubmissionAsNotification']              = 'formHybridSubmissionNotification';
$arrDca['subpalettes']['formHybridSendConfirmationAsNotification']            = 'formHybridConfirmationNotification';
$arrDca['subpalettes']['formHybridAddEditableRequired']                       = 'formHybridEditableRequired';
$arrDca['subpalettes']['formHybridAddReadOnly']                               = 'formHybridReadOnly';
$arrDca['subpalettes']['formHybridAddDisplayedSubPaletteFields']              = 'formHybridDisplayedSubPaletteFields';
$arrDca['subpalettes']['formHybridAddFieldDependentRedirect']                 =
    'formHybridFieldDependentRedirectConditions,formHybridFieldDependentRedirectJumpTo,formHybridFieldDependentRedirectKeepParams';
$arrDca['subpalettes']['formHybridCustomSubmit']                              = 'formHybridSubmitLabel,formHybridSubmitClass';
$arrDca['subpalettes']['formHybridAddPermanentFields']                        = 'formHybridPermanentFields';
$arrDca['subpalettes']['formHybridUseCustomFormId']                           = 'formHybridCustomFormId';
$arrDca['subpalettes']['formHybridUseCustomFormIdSuffix']                     = 'formHybridCustomFormIdSuffix';
$arrDca['subpalettes']['formHybridAllowIdAsGetParameter']                     = 'formHybridIdGetParameter,formHybridAppendIdToUrlOnCreation';
$arrDca['subpalettes']['formHybridAddHashToAction']                           = 'formHybridCustomHash';
$arrDca['subpalettes']['formHybridAddExportButton']                           = 'formHybridExportConfigs';
$arrDca['subpalettes']['formHybridAddOptIn']                                  = 'formHybridOptInExplanation,formHybridOptInSuccessMessage,formHybridOptInNotification,formHybridOptInConfirmedProperty,formHybridOptInModelRetrievalProperty,formHybridOptInJumpTo,formHybridOptInAddPrivacyProtocolEntry,formHybridfilterTokenFields';
$arrDca['subpalettes']['formHybridOptInAddPrivacyProtocolEntry']              = 'formHybridApiApp,formHybridOptInPrivacyProtocolArchive,formHybridOptInPrivacyProtocolEntryType,formHybridOptInPrivacyProtocolDescription,formHybridOptInPrivacyProtocolFieldMapping';
$arrDca['subpalettes']['formHybridAddOptOut']                                 = 'formHybridOptOutSuccessMessage,formHybridOptOutJumpTo';
$arrDca['subpalettes']['formHybridfilterTokenFields']                         = 'formHybridTokenFields';
$arrDca['subpalettes']['formHybridAddGetParameter']                           = 'formHybridGetParameter';

/**
 * Fields
 */
$arrFields = [
    'formHybridDataContainer'                    => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridDataContainer'],
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getDataContainers'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getViewModes'],
        'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
        'sql'              => "varchar(10) NOT NULL default 'default'",
        'reference'        => &$GLOBALS['TL_LANG']['tl_module']['reference'],
    ],
    'formHybridForcePaletteRelation'             => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridForcePaletteRelation'],
        'exclude'   => true,
        'default'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default '1'",
    ],
    'formHybridEditable'                         => [
        'inputType'        => 'checkboxWizard',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'],
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getEditable'],
        'exclude'          => true,
        'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFields'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFields'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getSubPaletteFields'],
        'exclude'          => true,
        'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight',],
        'sql'              => "blob NULL",
    ],
    'formHybridEditableSkip'                     => [
        'inputType'        => 'checkboxWizard',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridEditableSkip'],
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFields'],  // all fields, cause formHybridDefaultValues will suffer from all fields
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
                    'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFields'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFormHybridTemplates'],
        'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true],
        'sql'              => "varchar(64) NOT NULL default 'formhybrid_default'",
    ],
    'formHybridReadonlyTemplate'                 => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridReadonlyTemplate'],
        'default'          => 'formhybridreadonly_default',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFormHybridReadonlyTemplates'],
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
        'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFormHybridStartTemplates'],
        'eval'             => ['tl_class' => 'w50'],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
    'formHybridStopTemplate'                     => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridStopTemplate'],
        'default'          => 'formhybridStop_default',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFormHybridStopTemplates'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getNoficiationMessages'],
        'eval'             => ['chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql'              => "int(10) unsigned NOT NULL default '0'",
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getNoficiationMessages'],
        'eval'             => ['chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true],
        'sql'              => "int(10) unsigned NOT NULL default '0'",
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getSubmitLabels'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getEditable'],
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
    'formHybridUseCustomFormIdSuffix'                  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridUseCustomFormIdSuffix'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridCustomFormIdSuffix'                     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridCustomFormIdSuffix'],
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
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getOptInMessages'],
        'eval'             => ['chosen' => true, 'maxlength' => 255, 'tl_class' => 'w50 clr', 'includeBlankOption' => true, 'mandatory' => true],
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
    'formHybridOptInSuccessMessage'              => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptInSuccessMessage'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'textarea',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => ['allowHtml' => true, 'tl_class' => 'clr', 'class' => 'monospace', 'rte' => 'ace|html', 'helpwizard' => true],
        'sql'         => "text NULL",
    ],
    'formHybridOptInConfirmedProperty'           => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptInConfirmedProperty'],
        'inputType'        => 'select',
        'exclude'          => true,
        'filter'           => false,
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getEditable'],
        'sql'              => "varchar(64) NOT NULL default ''",
        'eval'             => [
            'chosen'             => true,
            'tl_class'           => 'w50',
            'includeBlankOption' => true
        ]
    ],
    'formHybridOptInModelRetrievalProperty'      => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptInModelRetrievalProperty'],
        'inputType'        => 'select',
        'exclude'          => true,
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getEditable'],
        'eval'             => [
            'chosen'             => true,
            'tl_class'           => 'w50',
            'includeBlankOption' => true
        ],
        'sql'              => "varchar(32) NOT NULL default ''"
    ],
    'formHybridOptInJumpTo'                      => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptInJumpTo'],
        'exclude'    => true,
        'inputType'  => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval'       => ['fieldType' => 'radio', 'tl_class' => 'w50'],
        'sql'        => "int(10) unsigned NOT NULL default '0'",
        'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
    ],
    'formHybridAddOptOut'                        => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddOptOut'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridOptOutSuccessMessage'             => [
        'label'       => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptOutSuccessMessage'],
        'exclude'     => true,
        'filter'      => false,
        'inputType'   => 'textarea',
        'explanation' => 'formhybrid_inserttags_text',
        'eval'        => ['allowHtml' => true, 'tl_class' => 'clr', 'class' => 'monospace', 'rte' => 'ace|html', 'helpwizard' => true],
        'sql'         => "text NULL",
    ],
    'formHybridOptOutJumpTo'                     => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['formHybridOptOutJumpTo'],
        'exclude'    => true,
        'inputType'  => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval'       => ['fieldType' => 'radio', 'tl_class' => 'w50'],
        'sql'        => "int(10) unsigned NOT NULL default '0'",
        'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
    ],
    'formHybridfilterTokenFields'                => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridfilterTokenFields'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridTokenFields'                      => [
        'inputType'        => 'checkbox',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridTokenFields'],
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getEditable'],
        'exclude'          => true,
        'eval'             => [
            'multiple' => true,
        ],
        'sql'              => "blob NULL",
    ],
    'formHybridAddGetParameter'                  => [
        'inputType' => 'checkbox',
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['formHybridAddGetParameter'],
        'exclude'   => true,
        'eval'      => [
            'submitOnChange' => true,
            'tl_class'       => 'clr w50'
        ],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'formHybridGetParameter'                     => [
        'inputType'        => 'checkboxWizard',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['formHybridTokenFields'],
        'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getSelectedEditable'],
        'exclude'          => true,
        'eval'             => [
            'multiple'  => true,
            'mandatory' => true,
            'tl_class'  => 'clr w50'
        ],
        'sql'              => "blob NULL",
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
$arrFields['formHybridFieldDependentRedirectJumpTo']['eval']['load']      = 'lazy';


if (class_exists('HeimrichHannot\ContaoExporterBundle\HeimrichHannotContaoExporterBundle') ||
    in_array('exporter', \ModuleLoader::getActive())) {
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
        'relation'     => ['type' => 'hasMany', 'load' => 'lazy'],
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
                    'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getFormHybridExportConfigsAsOptions'],
                    'eval'             => ['tl_class' => 'long clr', 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true],
                    'sql'              => "int(10) unsigned NOT NULL default '0'",
                ],
                'formhybrid_formHybridExportConfigs_entityField' => [
                    'label'            => &$GLOBALS['TL_LANG']['tl_module']['formhybrid_formHybridExportConfigs_entityField'],
                    'exclude'          => true,
                    'inputType'        => 'select',
                    'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getEditableForExport'],
                    'eval'             => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
                    'sql'              => "varchar(64) NOT NULL default ''",
                ],
            ],
        ],
    ];
}

if (in_array('privacy', \ModuleLoader::getActive()) || class_exists('\HeimrichHannot\PrivacyBundle\HeimrichHannotPrivacyBundle')) {
    $protocolManager = new \HeimrichHannot\Privacy\Manager\ProtocolManager();

    $arrFields['formHybridAddPrivacyProtocolEntry']     = $protocolManager->getSelectorFieldDca();
    $arrFields['formHybridPrivacyProtocolArchive']      = $protocolManager->getArchiveFieldDca();
    $arrFields['formHybridPrivacyProtocolEntryType']    = $protocolManager->getTypeFieldDca();
    $arrFields['formHybridPrivacyProtocolDescription']  = $protocolManager->getDescriptionFieldDca();
    $arrFields['formHybridPrivacyProtocolFieldMapping'] = $protocolManager->getFieldMappingFieldDca('formHybridDataContainer');

    $arrFields['formHybridOptInAddPrivacyProtocolEntry']             = $protocolManager->getSelectorFieldDca();
    isset($arrFields['formHybridOptInAddPrivacyProtocolEntry']['label'][0]) && $arrFields['formHybridOptInAddPrivacyProtocolEntry']['label'][0] .= ' (Opt-in)';

    $arrFields['formHybridOptInPrivacyProtocolArchive']             = $protocolManager->getArchiveFieldDca();
    isset($arrFields['formHybridOptInPrivacyProtocolArchive']['label'][0]) && $arrFields['formHybridOptInPrivacyProtocolArchive']['label'][0] .= ' (Opt-in)';

    $arrFields['formHybridOptInPrivacyProtocolEntryType']             = $protocolManager->getTypeFieldDca();
    isset($arrFields['formHybridOptInPrivacyProtocolEntryType']['label'][0]) && $arrFields['formHybridOptInPrivacyProtocolEntryType']['label'][0] .= ' (Opt-in)';

    $arrFields['formHybridOptInPrivacyProtocolDescription']             = $protocolManager->getDescriptionFieldDca();
    isset($arrFields['formHybridOptInPrivacyProtocolDescription']['label'][0]) && $arrFields['formHybridOptInPrivacyProtocolDescription']['label'][0] .= ' (Opt-in)';

    $arrFields['formHybridOptInPrivacyProtocolFieldMapping']             = $arrFields['formHybridPrivacyProtocolFieldMapping'];
    isset($arrFields['formHybridOptInPrivacyProtocolFieldMapping']['label'][0]) && $arrFields['formHybridOptInPrivacyProtocolFieldMapping']['label'][0] .= ' (Opt-in)';
}

$arrDca['fields'] = array_merge($arrDca['fields'], $arrFields);

