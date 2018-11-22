<?php

define('FORMHYBRID_METHOD_GET', 'GET');
define('FORMHYBRID_METHOD_POST', 'POST');
define('FORMHYBRID_NAME_SUBMIT', 'submit');
define('FORMHYBRID_NAME_EXPORT', 'export');
define('FORMHYBRID_NAME_FORM_SUBMIT', 'FORM_SUBMIT');
define('FORMHYBRID_MESSAGE_SUCCESS', 'FORMHYBRID_SUCCESS');
define('FORMHYBRID_MESSAGE_ERROR', 'FORMHYBRID_ERROR');
define('FORMHYBRID_NAME_SKIP_VALIDATION', 'skipvalidation');
define('FORMHYBRID_MODE_CREATE', 'formhybrid_mode_create');
define('FORMHYBRID_MODE_EDIT', 'formhybrid_mode_edit');
define('FORMHYBRID_VIEW_MODE_DEFAULT', 'default');
define('FORMHYBRID_VIEW_MODE_READONLY', 'readonly');
define('FORMHYBRID_ACTION_SCOPE', 'formhybrid');

define(
    'FORMHYBRID_PALETTE_DEFAULT',
    '
{formhybrid_config_legend},formHybridDataContainer,formHybridEditable,formHybridForcePaletteRelation,formHybridAddEditableRequired,formHybridAddDisplayedSubPaletteFields,formHybridEditableSkip,formHybridAddDefaultValues,formHybridViewMode;
{formhybrid_template_legend},formHybridTemplate,formHybridCustomSubTemplates,formHybridStartTemplate,formHybridStopTemplate,formHybridCustomSubmit;
{formhybrid_action_legend},formHybridResetAfterSubmission,formHybridSingleSubmission,formHybridAction,formHybridAddHashToAction,removeAutoItemFromAction,formHybridAsync,formHybridEnableAutoComplete,formHybridAddFieldDependentRedirect;
{formhybrid_message_legend},formHybridSuccessMessage,formHybridSkipScrollingToSuccessMessage;
{formhybrid_notification_legend},formHybridSendSubmissionAsNotification,formHybridSendConfirmationAsNotification'
);

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['formhybrid'] = [
    'formhybridStart'   => 'HeimrichHannot\\FormHybrid\\ContentFormHybridStart',
    'formhybridElement' => 'HeimrichHannot\\FormHybrid\\ContentFormHybridElement',
    'formhybridStop'    => 'HeimrichHannot\\FormHybrid\\ContentFormHybridStop',
];


/**
 * Indent elements
 */
$GLOBALS['TL_WRAPPERS']['start'][] = 'formhybridStart';
$GLOBALS['TL_WRAPPERS']['stop'][]  = 'formhybridStop';

/**
 * Javascript
 */
if (TL_MODE == 'FE')
{
    $GLOBALS['TL_JAVASCRIPT']['jquery.formhybrid'] = 'system/modules/formhybrid/assets/js/jquery.formhybrid' . (!$GLOBALS['TL_CONFIG']['debugMode'] ? '.min' : '') . '.js|static';
}

$GLOBALS['TL_COMPONENTS']['formhybrid'] = [
    'js'  => [
        'system/modules/formhybrid/assets/js/jquery.formhybrid.js|static',
        'system/modules/formhybrid/assets/js/jquery.formhybrid.min.js|static',
    ],
];

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseWidget'][] = ['HeimrichHannot\FormHybrid\Hooks', 'parseWidgetHook'];

/**
 * Front end widgets
 */

$GLOBALS['TL_FFL']['multiColumnWizard'] = 'HeimrichHannot\FormHybrid\FormMultiColumnWizard';

/**
 * Notification Center Tokens
 */
if (in_array('notification_center_plus', \ModuleLoader::getActive()))
{
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['recipients'][] = 'form_value_*';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['recipients'][] = 'form_plain_*';

    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'formsubmission';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'formsubmission_all';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'form_submission_*';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'form_value_*';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'form_plain_*';


    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_subject'][] = 'form_submission_*';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_subject'][] = 'form_value_*';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_subject'][] = 'form_plain_*';

    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'formsubmission';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'formsubmission_all';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'form_submission_*';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'form_value_*';
    $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'form_plain_*';
}

/**
 * Notification Center Notification Types
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] = array_merge_recursive(
    (array) $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'],
    [
        \HeimrichHannot\FormHybrid\FormHybrid::NOTIFICATION_TYPE_FORMHYBRID => [
            \HeimrichHannot\FormHybrid\FormHybrid::NOTIFICATION_TYPE_FORM_OPT_IN => [
                'recipients'           => ['form_value_*', 'form_plain_*', 'admin_email'],
                'email_subject'        => ['form_value_*', 'form_plain_*', 'admin_email', 'env_*', 'page_*', 'user_*', 'date', 'last_update', 'opt_in_link','opt_out_token','opt_out_link'],
                'email_text'           => [
                    'formsubmission',
                    'formsubmission_all',
                    'form_submission_*',
                    'form_value_*',
                    'form_plain_*',
                    'salutation_submission',
                    'admin_email',
                    'env_*',
                    'page_*',
                    'user_*',
                    'date',
                    'last_update',
                    'opt_in_link',
                    'opt_in_token',
                    'opt_out_token',
                    'opt_out_link'
                ],
                'email_html'           => [
                    'formsubmission',
                    'formsubmission_all',
                    'form_submission_*',
                    'form_value_*',
                    'form_plain_*',
                    'salutation_submission',
                    'admin_email',
                    'env_*',
                    'page_*',
                    'user_*',
                    'date',
                    'last_update',
                    'opt_in_link',
                    'opt_in_token',
                    'opt_out_token',
                    'opt_out_link'
                ],
                'file_name'            => ['form_value_*', 'form_plain_*', 'admin_email'],
                'file_content'         => ['form_value_*', 'form_plain_*', 'admin_email'],
                'email_sender_name'    => ['form_value_*', 'form_plain_*', 'admin_email'],
                'email_sender_address' => ['form_value_*', 'form_plain_*', 'admin_email'],
                'email_recipient_cc'   => ['form_value_*', 'form_plain_*', 'admin_email'],
                'email_recipient_bcc'  => ['form_value_*', 'form_plain_*', 'admin_email'],
                'email_replyTo'        => ['form_value_*', 'form_plain_*', 'admin_email'],
                'attachment_tokens'    => ['form_value_*', 'form_plain_*'],
            ],
        ],
    ]
);

/**
 * Ajax Actions
 */
$GLOBALS['AJAX'][\HeimrichHannot\FormHybrid\Form::FORMHYBRID_NAME] = [
    'actions' => [
        'toggleSubpalette' => [
            'arguments' => ['subId', 'subField', 'subLoad'],
            'optional'  => ['subLoad'],
        ],
        'asyncFormSubmit'  => [
            'arguments' => [],
            'optional'  => [],
        ],
        'reload'           => [
            'arguments' => [],
            'optional'  => [],
        ],
    ],
];
