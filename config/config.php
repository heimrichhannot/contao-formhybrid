<?php

define('FORMHYBRID_METHOD_GET', 'GET');
define('FORMHYBRID_METHOD_POST', 'POST');
define('FORMHYBRID_NAME_SUBMIT', 'submit');
define('FORMHYBRID_NAME_FORM_SUBMIT', 'FORM_SUBMIT');
define('FORMHYBRID_MESSAGE_SUCCESS', 'FORMHYBRID_SUCCESS');
define('FORMHYBRID_MESSAGE_ERROR', 'FORMHYBRID_ERROR');
define('FORMHYBRID_NAME_SKIP_VALIDATION', 'skipvalidation');
define('FORMHYBRID_USER_EMAIL', 'info@formhybrid.de');
define('FORMHYBRID_USER_NAME', 'FormHybrid');
define('FORMHYBRID_MODE_CREATE', 'formhybrid_mode_create');
define('FORMHYBRID_MODE_EDIT', 'formhybrid_mode_edit');
define('FORMHYBRID_VIEW_MODE_DEFAULT', 'default');
define('FORMHYBRID_VIEW_MODE_READONLY', 'readonly');
define('FORMHYBRID_ACTION_SCOPE', 'formhybrid');

define('FORMHYBRID_PALETTE_DEFAULT', '
{formhybrid_config_legend},formHybridDataContainer,formHybridEditable,formHybridAddEditableRequired,formHybridAddDisplayedSubPaletteFields,formHybridEditableSkip,formHybridAddDefaultValues,formHybridViewMode;
{formhybrid_template_legend},formHybridTemplate,formHybridCustomSubTemplates,formHybridStartTemplate,formHybridStopTemplate,formHybridCustomSubmit;
{formhybrid_action_legend},formHybridResetAfterSubmission,formHybridAction,formHybridAddHashToAction,formHybridAsync,formHybridAddFieldDependentRedirect;
{formhybrid_message_legend},formHybridSuccessMessage,formHybridSkipScrollingToSuccessMessage;
{formhybrid_notification_legend},formHybridSendSubmissionAsNotification,formHybridSendSubmissionViaEmail,formHybridSendConfirmationAsNotification,formHybridSendConfirmationViaEmail');

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['formhybrid'] = array(
	'formhybridStart'   => 'HeimrichHannot\\FormHybrid\\ContentFormHybridStart',
	'formhybridElement' => 'HeimrichHannot\\FormHybrid\\ContentFormHybridElement',
	'formhybridStop'    => 'HeimrichHannot\\FormHybrid\\ContentFormHybridStop',
);


/**
 * Indent elements
 */
$GLOBALS['TL_WRAPPERS']['start'][] = 'formhybridStart';
$GLOBALS['TL_WRAPPERS']['stop'][]  = 'formhybridStop';

/**
 * Javascript
 */
if (TL_MODE == 'FE') {
	$GLOBALS['TL_JAVASCRIPT']['jquery.formhybrid'] = 'system/modules/formhybrid/assets/js/jquery.formhybrid' . (!$GLOBALS['TL_CONFIG']['debugMode'] ? '.min' : '') . '.js|static';
}

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseWidget'][] = array('HeimrichHannot\FormHybrid\Hooks', 'parseWidgetHook');

/**
 * Front end widgets
 */

$GLOBALS['TL_FFL']['multiColumnWizard'] = 'HeimrichHannot\FormHybrid\FormMultiColumnWizard';

/**
 * Notification Center Tokens
 */
if (in_array('notification_center_plus', \ModuleLoader::getActive())) {
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
 * Ajax Actions
 */
$GLOBALS['AJAX'][\HeimrichHannot\FormHybrid\Form::FORMHYBRID_NAME] = array
(
	'actions' => array
	(
		'toggleSubpalette' => array
		(
			'arguments' => array('subId', 'subField', 'subLoad'),
			'optional'   => array('subLoad'),
		),
		'asyncFormSubmit'  => array
		(
			'arguments' => array(),
			'optional'   => array(),
		),
		'reload'  => array
		(
			'arguments' => array(),
			'optional'   => array(),
		),
	),
);