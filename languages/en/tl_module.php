<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['formHybridDataContainer'] = ['Data Container', 'Choose the desired data container.'];
$arrLang['formHybridEditable'] = ['Fields', 'Choose the editable fields.'];
$arrLang['formHybridForcePaletteRelation'] =
    ['Force palette relation', 'Palette relation is active by default. Disable if you want to access fields from all palettes and subpalettes despite their reference.'];
$arrLang['formHybridAddEditableRequired'] = ['Override mandatory fields', 'Define mandatory ignoring the dca configuration.'];
$arrLang['formHybridEditableRequired'] = ['Mandatory fields', 'Choose the desired mandatory fields.'];
$arrLang['formHybridAddReadOnly'] = ['Add read only fields', 'Choose this if you want to have read only fields.'];
$arrLang['formHybridReadOnly'] = ['Read only fields', 'Define the fields that are read only.'];
$arrLang['formHybridAddDisplayedSubPaletteFields'] = ['Add permanently displayed subpalette fields', 'Choose this if you want to have sub palette fields always to be displayed ignoring their selector\'s state.'];
$arrLang['formHybridDisplayedSubPaletteFields'] = ['Permanently displayed subpalette fields', 'Choose the desired fields.'];
$arrLang['formHybridEditableSkip'] = ['Skippable fields', 'Choose the fields not being used by the model to filter (dependend to module logic).'];
$arrLang['formHybridAddDefaultValues'] = ['Add default values', 'Choose this option in order to define default values.'];
$arrLang['formHybridDefaultValues'] = ['Default values', 'Define the default values here.'];
$arrLang['formHybridDefaultValues']['field'] = ['Field', 'Choose a field.'];
$arrLang['formHybridDefaultValues']['value'] = ['Value', 'Type in a value here. Type in arrays serialized.'];
$arrLang['formHybridDefaultValues']['label'] = ['Label', 'Type in an alternative label optionally'];

$arrLang['formHybridAsync'] = ['Submit form asynchronously', 'Choose this option if you want to send the form asynchronously.'];

$arrLang['formHybridCustomSubmit'] = ['Customize submit field', 'Customize the submit field.'];
$arrLang['formHybridSubmitLabel'] = ['Submit field label', 'Type in a text to be the label of the submit button.'];
$arrLang['formHybridSubmitClass'] = ['Submit field CSS class', 'Type in some css class for the submit button.'];

$arrLang['formHybridSuccessMessage'] = ['Override success message', 'Type in an alternative success message.'];
$arrLang['formHybridSkipScrollingToSuccessMessage'] = ['Skip scrolling to success message', 'Choose this option if you don\'t want the module to scroll to the success message.'];
$arrLang['formHybridSendSubmissionAsNotification'] = ['Send email using notification center', 'After successfully submitting the form an email using the notification center is sent.'];
$arrLang['formHybridSubmissionNotification'] = ['Send notification after form submission', 'Choose the notification to be sent after successfully submitting the form.'];

$arrLang['formHybridSendSubmissionViaEmail'] = ['Send via email', 'Send the form data via email.'];
$arrLang['formHybridSubmissionMailSender'] = ['Sender', 'Please type in the email sender address. <strong>Format with sender name: Name [Email]</strong>'];
$arrLang['formHybridSubmissionMailRecipient'] = ['Sender address', 'Multiple email addresses an be typed in comma separatedly.'];
$arrLang['formHybridSubmissionAvisotaMessage'] = ['Notification', 'Choose an avisota message.'];
$arrLang['formHybridSubmissionAvisotaSalutationGroup'] = ['Salutation', 'Choose an avisota salutation.'];
$arrLang['formHybridSubmissionMailSubject'] = ['Subject', 'Please type in a subject.'];
$arrLang['formHybridSubmissionMailText'] = ['Email text', 'Please type in the email text.'];
$arrLang['formHybridSubmissionMailTemplate'] = ['Email template', 'Here you can override the email template.'];
$arrLang['formHybridSubmissionMailAttachment'] = ['Email attachment', 'Send files as attachment.'];

$arrLang['formHybridSendConfirmationAsNotification'] = ['Send Confirmation email using notification center', 'If you choose this, a confirmation is sent to the sender of the form using the notification center.'];
$arrLang['formHybridSendConfirmationViaEmail'] = ['Send confirmation via email', 'If you choose this, a confirmation is sent to the sender of the form via email.'];
$arrLang['formHybridConfirmationNotification'] = ['Send confirmation notification', 'Choose the confirmation notification message here.'];
$arrLang['formHybridConfirmationMailSender'] = ['Sender', 'Type in the sender email address here. <strong>Format with sender name: Name [Email]</strong>'];
$arrLang['formHybridConfirmationMailRecipientField'] = ['Email recipient form field', 'Choose the form field containing the mail address.'];
$arrLang['formHybridConfirmationAvisotaMessage'] = ['Notification', 'Choose an avisota message here.'];
$arrLang['formHybridConfirmationAvisotaSalutationGroup'] = ['Salutation', 'Choose an avisota salutation here.'];
$arrLang['formHybridConfirmationMailSubject'] = ['Subject', 'Type in a subject here.'];
$arrLang['formHybridConfirmationMailText'] = ['Email text', 'Type in the email text here.'];
$arrLang['formHybridConfirmationMailTemplate'] = ['Email template', 'Here you can override the email template.'];
$arrLang['formHybridConfirmationMailAttachment'] = ['Email attachments', 'Attach files here.'];
$arrLang['formHybridAddFieldDependentRedirect'] = ['Add field dependent redirect', 'Choose this option in order to define a jump to page for certain field values.'];
$arrLang['formHybridFieldDependentRedirectConditions'] = ['Redirect conditions', ''];
$arrLang['formHybridFieldDependentRedirectJumpTo'] = ['Field dependent redirect page', 'Choose the field dependend redirect page.'];
$arrLang['formHybridFieldDependentRedirectKeepParams'] = ['GET-Parameters to be kept', 'Type in a comma separated list of get parameters to be kept after redirect.'];

$arrLang['formHybridTemplate'] = ['Form template', 'Here you can override the form template.'];

$arrLang['formHybridCustomSubTemplates'] = ['Custom sub palette templates', 'Use custom templates for sub palettes by creating a template with the suffix _sub_[SUBPALETTE_KEY].'];

$arrLang['formHybridIsComplete'] = ['Complete', 'This option is set automatically by your module if the form is submitted at least once.'];
$arrLang['formHybridAction'] = ['Form action', 'Choose the target page.'];
$arrLang['formHybridAddHashToAction'] = ['Add hash to form action', 'Choose this option in order to append the form id as a hash to the form action.'];
$arrLang['formHybridAddPermanentFields'] = ['Add permanently displayed fields', 'Choose field to be displayed ignoring their selector.'];
$arrLang['formHybridPermanentFields'] = ['Permanently displayed fields', 'Choose the fields to be displayed permanently.'];

$arrLang['formHybridResetAfterSubmission'] = ['Reset form after submission', 'Deactivate this if the form shouldn\'t be reset after submission.'];
$arrLang['formHybridSingleSubmission'] = ['Form submission only once', 'After the form has been submitted successfully, no new entity is created and only messages are displayed.'];

$arrLang['formHybridJumpToPreserveParams'] = ['Keep parameters after redirect', 'Choose the parameters to be kept after redirect.'];

$arrLang['formHybridUseCustomFormId'] = ['Override FormId', 'Choose this option if the ID of the filter module should be overridden.'];
$arrLang['formHybridCustomFormId'] = ['New FormID', 'Type in the new form id.'];

$arrLang['formHybridAllowIdAsGetParameter']        =
	['Allow retrieval of ID as GET-Parameter (CAUTION!)', 'Choose this option, if the record to be displayed can be retrieved by a GET parameter. ATTENTION: Use only in combination with "Update conditions"!'];
$arrLang['formHybridIdGetParameter']               = ['ID GET-Parameter', 'Select a field containing your ID.'];
$arrLang['formHybridAppendIdToUrlOnCreation']      = ['New instance: Append ID GET-Parameter to url', 'After creation of instances their id is appended to the url.'];
$arrLang['formHybridTransformGetParamsToHiddenFields']      = ['Transform GET-Parameters to hidden fields', 'Makes sense e.g. for filter forms in GET mode.'];

$arrLang['formHybridEnableAutoComplete'] = ['Enable form "autocomplete"', 'Enable autocomplete for this form (not recommended). <strong>Caution: Form submission values will be cached and restored on browser back button.</strong>'];

$arrLang['formHybridOptInConfirmedProperty'] =
    ['Opt-in success property', 'A property (boolean) which is set true, if opt-in was a success.'];

/**
 * References
 */
$arrLang['reference'][FORMHYBRID_VIEW_MODE_DEFAULT] = 'Default (edit)';
$arrLang['reference'][FORMHYBRID_VIEW_MODE_READONLY] = 'Read only';