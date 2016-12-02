<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['formHybridDataContainer'] = array('Data Container', 'Choose the desired data container.');
$arrLang['formHybridEditable'] = array('Fields', 'Choose the editable fields.');
$arrLang['formHybridAddEditableRequired'] = array('Override mandatory fields', 'Define mandatory ignoring the dca configuration.');
$arrLang['formHybridEditableRequired'] = array('Mandatory fields', 'Choose the desired mandatory fields.');
$arrLang['formHybridAddReadOnly'] = array('Add read only fields', 'Choose this if you want to have read only fields.');
$arrLang['formHybridReadOnly'] = array('Read only fields', 'Define the fields that are read only.');
$arrLang['formHybridAddDisplayedSubPaletteFields'] = array('Add permanently displayed subpalette fields', 'Choose this if you want to have sub palette fields always to be displayed ignoring their selector\'s state.');
$arrLang['formHybridDisplayedSubPaletteFields'] = array('Permanently displayed subpalette fields', 'Choose the desired fields.');
$arrLang['formHybridEditableSkip'] = array('Skippable fields', 'Choose the fields not being used by the model to filter (dependend to module logic).');
$arrLang['formHybridAddDefaultValues'] = array('Add default values', 'Choose this option in order to define default values.');
$arrLang['formHybridDefaultValues'] = array('Default values', 'Define the default values here.');
$arrLang['formHybridDefaultValues']['field'] = array('Field', 'Choose a field.');
$arrLang['formHybridDefaultValues']['value'] = array('Value', 'Type in a value here. Type in arrays serialized.');
$arrLang['formHybridDefaultValues']['label'] = array('Label', 'Type in an alternative label optionally');

$arrLang['formHybridAsync'] = array('Submit form asynchronously', 'Choose this option if you want to send the form asynchronously.');

$arrLang['formHybridCustomSubmit'] = array('Customize submit field', 'Customize the submit field.');
$arrLang['formHybridSubmitLabel'] = array('Submit field label', 'Type in a text to be the label of the submit button.');
$arrLang['formHybridSubmitClass'] = array('Submit field CSS class', 'Type in some css class for the submit button.');

$arrLang['formHybridSuccessMessage'] = array('Override success message', 'Type in an alternative success message.');
$arrLang['formHybridSkipScrollingToSuccessMessage'] = array('Skip scrolling to success message', 'Choose this option if you don\'t want the module to scroll to the success message.');
$arrLang['formHybridSendSubmissionAsNotification'] = array('Send email using notification center', 'After successfully submitting the form an email using the notification center is sent.');
$arrLang['formHybridSubmissionNotification'] = array('Send notification after form submission', 'Choose the notification to be sent after successfully submitting the form.');

$arrLang['formHybridSendSubmissionViaEmail'] = array('Send via email', 'Send the form data via email.');
$arrLang['formHybridSubmissionMailSender'] = array('Sender', 'Please type in the email sender address. <strong>Format with sender name: Name [Email]</strong>');
$arrLang['formHybridSubmissionMailRecipient'] = array('Sender address', 'Multiple email addresses an be typed in comma separatedly.');
$arrLang['formHybridSubmissionAvisotaMessage'] = array('Notification', 'Choose an avisota message.');
$arrLang['formHybridSubmissionAvisotaSalutationGroup'] = array('Salutation', 'Choose an avisota salutation.');
$arrLang['formHybridSubmissionMailSubject'] = array('Subject', 'Please type in a subject.');
$arrLang['formHybridSubmissionMailText'] = array('Email text', 'Please type in the email text.');
$arrLang['formHybridSubmissionMailTemplate'] = array('Email template', 'Here you can override the email template.');
$arrLang['formHybridSubmissionMailAttachment'] = array('Email attachment', 'Send files as attachment.');

$arrLang['formHybridSendConfirmationAsNotification'] = array('Send Confirmation email using notification center', 'If you choose this, a confirmation is sent to the sender of the form using the notification center.');
$arrLang['formHybridSendConfirmationViaEmail'] = array('Send confirmation via email', 'If you choose this, a confirmation is sent to the sender of the form via email.');
$arrLang['formHybridConfirmationNotification'] = array('Send confirmation notification', 'Choose the confirmation notification message here.');
$arrLang['formHybridConfirmationMailSender'] = array('Sender', 'Type in the sender email address here. <strong>Format with sender name: Name [Email]</strong>');
$arrLang['formHybridConfirmationMailRecipientField'] = array('Email recipient form field', 'Choose the form field containing the mail address.');
$arrLang['formHybridConfirmationAvisotaMessage'] = array('Notification', 'Choose an avisota message here.');
$arrLang['formHybridConfirmationAvisotaSalutationGroup'] = array('Salutation', 'Choose an avisota salutation here.');
$arrLang['formHybridConfirmationMailSubject'] = array('Subject', 'Type in a subject here.');
$arrLang['formHybridConfirmationMailText'] = array('Email text', 'Type in the email text here.');
$arrLang['formHybridConfirmationMailTemplate'] = array('Email template', 'Here you can override the email template.');
$arrLang['formHybridConfirmationMailAttachment'] = array('Email attachments', 'Attach files here.');
$arrLang['formHybridAddFieldDependentRedirect'] = array('Add field dependent redirect', 'Choose this option in order to define a jump to page for certain field values.');
$arrLang['formHybridFieldDependentRedirectConditions'] = array('Redirect conditions', '');
$arrLang['formHybridFieldDependentRedirectJumpTo'] = array('Field dependent redirect page', 'Choose the field dependend redirect page.');
$arrLang['formHybridFieldDependentRedirectKeepParams'] = array('GET-Parameters to be kept', 'Type in a comma separated list of get parameters to be kept after redirect.');

$arrLang['formHybridTemplate'] = array('Form template', 'Here you can override the form template.');

$arrLang['formHybridCustomSubTemplates'] = array('Custom sub palette templates', 'Use custom templates for sub palettes by creating a template with the suffix _sub_[SUBPALETTE_KEY].');

$arrLang['formHybridIsComplete'] = array('Complete', 'This option is set automatically by your module if the form is submitted at least once.');
$arrLang['formHybridAction'] = array('Form action', 'Choose the target page.');
$arrLang['formHybridAddHashToAction'] = array('Add hash to form action', 'Choose this option in order to append the form id as a hash to the form action.');
$arrLang['formHybridAddPermanentFields'] = array('Add permanently displayed fields', 'Choose field to be displayed ignoring their selector.');
$arrLang['formHybridPermanentFields'] = array('Permanently displayed fields', 'Choose the fields to be displayed permanently.');

$arrLang['formHybridResetAfterSubmission'] = array('Reset form after submission', 'Deactivate this if the form shouldn\'t be reset after submission.');
$arrLang['formHybridSingleSubmission'] = array('Form submission only once', 'After the form has been submitted successfully, no new entity is created and only messages are displayed.');

$arrLang['formHybridJumpToPreserveParams'] = array('Keep parameters after redirect', 'Choose the parameters to be kept after redirect.');

$arrLang['formHybridUseCustomFormId'] = array('Override FormId', 'Choose this option if the ID of the filter module should be overridden.');
$arrLang['formHybridCustomFormId'] = array('New FormID', 'Type in the new form id.');

$arrLang['formHybridAllowIdAsGetParameter']        =
	array('Allow retrieval of ID as GET-Parameter (CAUTION!)', 'Choose this option, if the record to be displayed can be retrieved by a GET parameter. ATTENTION: Use only in combination with "Update conditions"!');
$arrLang['formHybridIdGetParameter']               = array('ID GET-Parameter', 'Select a field containing your ID.');
$arrLang['formHybridAppendIdToUrlOnCreation']      = array('New instance: Append ID GET-Parameter to url', 'After creation of instances their id is appended to the url.');
$arrLang['formHybridTransformGetParamsToHiddenFields']      = array('Transform GET-Parameters to hidden fields', 'Makes sense e.g. for filter forms in GET mode.');

$arrLang['formHybridEnableAutoComplete'] = array('Enable form "autocomplete"', 'Enable autocomplete for this form (not recommended). <strong>Caution: Form submission values will be cached and restored on browser back button.</strong>');

/**
 * References
 */
$arrLang['reference'][FORMHYBRID_VIEW_MODE_DEFAULT] = 'Default (edit)';
$arrLang['reference'][FORMHYBRID_VIEW_MODE_READONLY] = 'Read only';