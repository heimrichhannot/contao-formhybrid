<?php 

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['formHybridDataContainer'] = array('DataContainer', 'Wählen Sie hier den gewünschten DataContainer aus.');
$GLOBALS['TL_LANG']['tl_module']['formHybridPalette'] = array('Palette', 'Wählen Sie hier die gewünschte Palette aus.');
$GLOBALS['TL_LANG']['tl_module']['formHybridEditable'] = array('Felder', 'Wählen Sie hier die gewünschten Felder aus.');
$GLOBALS['TL_LANG']['tl_module']['formHybridAddEditableRequired'] = array('Pflichtfelder überschreiben', 'Legen Sie die Pflichtfelder unabhängig von der DCA-Konfiguration fest.');
$GLOBALS['TL_LANG']['tl_module']['formHybridEditableRequired'] = array('Pflichtfelder', 'Wählen Sie hier die gewünschten Pflichtfelder aus.');
$GLOBALS['TL_LANG']['tl_module']['formHybridEditableSkip'] = array('Zu überspringende Felder', 'Wählen Sie hier die Felder aus, die vom Modell nicht zur Filterung genutzt werden sollen (abhängig von der Programmlogik).');
$GLOBALS['TL_LANG']['tl_module']['formHybridAddDefaultValues'] = array('Standardwerte hinzufügen', 'Wählen Sie diese Option, um Standardwerte für das Modul hinzuzufügen.');
$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues'] = array('Standardwerte', 'Definieren Sie hier Standardwerte für das Modul.');
$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['field'] = array('Feld', 'Wählen Sie hier das gewünschte Feld aus. ACHTUNG: Bitte wählen Sie nur Felder aus, die sich auch tatsächlich im Formular befinden.');
$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['value'] = array('Wert', 'Geben Sie hier den gewünschten Standardwert ein. Arrays bitte serialisiert eingeben.');
$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['label'] = array('Label', 'Geben Sie eine alternative Bezeichnung/Label an?');
$GLOBALS['TL_LANG']['tl_module']['formHybridDefaultValues']['hidden'] = array('Feld verstecken', 'Soll das Feld versteckt werden?');
$GLOBALS['TL_LANG']['tl_module']['formHybridSubPalettes'] = array('Subpaletten', 'Wählen Sie hier die gewünschten Subpaletten aus.');
$GLOBALS['TL_LANG']['tl_module']['formHybridSubPalettes']['subpalette'] = array('Subpalette', 'Wählen Sie hier die gewünschten Subpaletten aus.');
$GLOBALS['TL_LANG']['tl_module']['formHybridSubPalettes']['fields'] = array('Felder', 'Wählen Sie hier die gewünschten Felder aus.');

$GLOBALS['TL_LANG']['tl_module']['formHybridAsync'][0] = 'Formular asynchron absenden';
$GLOBALS['TL_LANG']['tl_module']['formHybridAsync'][1] = 'Wählen Sie diese Option, wenn Sie das Formular asynchron versenden wollen.';

$GLOBALS['TL_LANG']['tl_module']['formHybridSuccessMessage'][0] = 'Erfolgsmeldung überschreiben';
$GLOBALS['TL_LANG']['tl_module']['formHybridSuccessMessage'][1] = 'Geben Sie hier eine alternative Erfolgsmeldung an.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSkipScrollingToSuccessMessage'][0] = 'Nicht zur Erfolsmeldung scrollen';
$GLOBALS['TL_LANG']['tl_module']['formHybridSkipScrollingToSuccessMessage'][1] = 'Wählen Sie diese Option, damit nicht automatisch zur Erfolgsmeldung gescrollt wird.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSendSubmissionViaEmail'][0] = 'Per E-Mail versenden';
$GLOBALS['TL_LANG']['tl_module']['formHybridSendSubmissionViaEmail'][1] = 'Die Formulardaten an eine E-Mail-Adresse versenden.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSender'][0] = 'Absender';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSender'][1] = 'Bitte geben Sie hier die Absender-E-Mail-Adresse ein. <strong>Format mit Absender-Name: Name [Email]</strong>';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailRecipient'][0] = 'Empfänger-Adresse';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailRecipient'][1] = 'Mehrere E-Mail-Adressen können mit Komma getrennt werden.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaMessage'][0] = 'Benachrichtigung';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaMessage'][1] = 'Wählen Sie hier eine Avisota-Nachricht aus.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaSalutationGroup'][0] = 'Anrede';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionAvisotaSalutationGroup'][1] = 'Wählen Sie hier eine Avisota-Anrede aus. Die Anrede generiert sich aus Mitgliedern gleicher E-Mail-Adressen wie die Empfänger.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSubject'][0] = 'Betreff';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailSubject'][1] = 'Bitte geben Sie eine Betreffzeile für die Bestätigungs-E-Mail ein. Wenn Sie keine Betreffzeile erfassen, steigt die Wahrscheinlichkeit, dass die E-Mail als SPAM identifiziert wird.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailText'][0] = 'Text der E-Mail';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailText'][1] = 'Bitte geben Sie hier den Text der E-Mail ein (##submission## gibt die gesammelten Formulardaten formatiert mit Label: Wert aus). Neben den allgemeinen Insert-Tags werden Tags der Form form::FORMULARFELDNAME unterstützt.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailTemplate'][0] = 'E-Mail-Template';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailTemplate'][1] = 'Hier können Sie das E-Mail-Template überschreiben.';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailAttachment'][0] = 'E-Mail Anhänge';
$GLOBALS['TL_LANG']['tl_module']['formHybridSubmissionMailAttachment'][1] = 'Versenden Sie Dateien aus der Dateiverwaltung als Anhang.';

$GLOBALS['TL_LANG']['tl_module']['formHybridSendConfirmationViaEmail'][0] = 'Bestätigung per E-Mail versenden';
$GLOBALS['TL_LANG']['tl_module']['formHybridSendConfirmationViaEmail'][1] = 'Wenn Sie diese Option wählen, wird eine Bestätigung per E-Mail an den Absender des Formulars versendet.';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSender'][0] = 'Absender';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSender'][1] = 'Bitte geben Sie hier die Absender-E-Mail-Adresse ein. <strong>Format mit Absender-Name: Name [Email]</strong>';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailRecipientField'][0] = 'Formularfeld mit E-Mail-Adresse des Empfängers';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailRecipientField'][1] = 'Wählen Sie hier das Formularfeld, in dem der Absender seine E-Mail-Adresse angibt oder ein Formularfeld, das die Empfänger-Adresse als Wert enthält.';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaMessage'][0] = 'Benachrichtigung';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaMessage'][1] = 'Wählen Sie hier eine Avisota-Nachricht aus.';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaSalutationGroup'][0] = 'Anrede';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationAvisotaSalutationGroup'][1] = 'Wählen Sie hier eine Avisota-Anrede aus. Die Anrede generiert sich aus der Form-Submission.';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSubject'][0] = 'Betreff';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailSubject'][1] = 'Bitte geben Sie eine Betreffzeile für die Bestätigungs-E-Mail ein. Wenn Sie keine Betreffzeile erfassen, steigt die Wahrscheinlichkeit, dass die E-Mail als SPAM identifiziert wird.';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailText'][0] = 'Text der E-Mail';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailText'][1] = 'Bitte geben Sie hier den Text der E-Mail ein (##submission## gibt die gesammelten Formulardaten formatiert mit Label: Wert aus). Neben den allgemeinen Insert-Tags werden Tags der Form form::FORMULARFELDNAME unterstützt.';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailTemplate'][0] = 'E-Mail-Template';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailTemplate'][1] = 'Hier können Sie das E-Mail-Template überschreiben.';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailAttachment'][0] = 'E-Mail Anhänge';
$GLOBALS['TL_LANG']['tl_module']['formHybridConfirmationMailAttachment'][1] = 'Versenden Sie Dateien aus der Dateiverwaltung als Anhang.';

$GLOBALS['TL_LANG']['tl_module']['formHybridTemplate'][0] = 'Formular-Template';
$GLOBALS['TL_LANG']['tl_module']['formHybridTemplate'][1] = 'Hier können Sie das Formular-Template überschreiben.';

$GLOBALS['TL_LANG']['tl_module']['formHybridCustomSubTemplates'][0] = 'Eigene Formular-Template für Subpaletten';
$GLOBALS['TL_LANG']['tl_module']['formHybridCustomSubTemplates'][1] = 'Verwenden Sie eigene Templates für Subpaletten, dann erstellen Sie ein hängen Sie dem Formular-Template _sub_[SUBPALETTE_KEY] an.';

$GLOBALS['TL_LANG']['tl_module']['formHybridIsComplete'][0] = 'Komplett';
$GLOBALS['TL_LANG']['tl_module']['formHybridIsComplete'][1] = 'Diese Option wird automatisch vom Modul gesetzt, wenn das Formular mindestens einmal ';
