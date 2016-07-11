<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['formHybridDataContainer'] = array('DataContainer', 'Wählen Sie hier den gewünschten DataContainer aus.');
$arrLang['formHybridEditable'] = array('Felder', 'Wählen Sie hier die gewünschten Felder aus.');
$arrLang['formHybridAddEditableRequired'] = array('Pflichtfelder überschreiben', 'Legen Sie die Pflichtfelder unabhängig von der DCA-Konfiguration fest.');
$arrLang['formHybridEditableRequired'] = array('Pflichtfelder', 'Wählen Sie hier die gewünschten Pflichtfelder aus.');
$arrLang['formHybridAddDisplayedSubPaletteFields'] = array('Immer anzuzeigende Sub-Paletten-Felder hinzufügen', 'Legen Sie die Felder fest, welche sich in Sub-Paletten befinden, aber immer angezeigt werden sollen.');
$arrLang['formHybridDisplayedSubPaletteFields'] = array('Immer anzuzeigende Sub-Paletten-Felder', 'Wählen Sie hier die gewünschten Felder aus.');
$arrLang['formHybridEditableSkip'] = array('Zu überspringende Felder', 'Wählen Sie hier die Felder aus, die vom Modell nicht zur Filterung genutzt werden sollen (abhängig von der Programmlogik).');
$arrLang['formHybridAddDefaultValues'] = array('Standardwerte hinzufügen', 'Wählen Sie diese Option, um Standardwerte für das Modul hinzuzufügen.');
$arrLang['formHybridDefaultValues'] = array('Standardwerte', 'Definieren Sie hier Standardwerte für das Modul.');
$arrLang['formHybridDefaultValues']['field'] = array('Feld', 'Wählen Sie hier das gewünschte Feld aus. ACHTUNG: Bitte wählen Sie nur Felder aus, die sich auch tatsächlich im Formular befinden.');
$arrLang['formHybridDefaultValues']['value'] = array('Wert', 'Geben Sie hier den gewünschten Standardwert ein. Arrays bitte serialisiert eingeben.');
$arrLang['formHybridDefaultValues']['label'] = array('Label', 'Geben Sie eine alternative Bezeichnung/Label an?');
$arrLang['formHybridAddSubmitValues'] = array('Werte bei Absenden verändern', 'Wählen Sie diese Option, um Werte beim Absenden des Formulars hinzuzufügen.');
$arrLang['formHybridSubmitValues'] = array('Werte beim Absenden', 'Geben Sie hier die gewünschten Werte ein.');

$arrLang['formHybridAsync'] = array('Formular asynchron absenden', 'Wählen Sie diese Option, wenn Sie das Formular asynchron versenden wollen.');

$arrLang['formHybridCustomSubmit'] = array('Absendefeld anpassen', 'Den Absendefeld des Formulars anpassen.');
$arrLang['formHybridSubmitLabel'] = array('Absendefeld Bezeichnung', 'Wählen Sie den Text, der auf den Absendefeld stehen soll.');
$arrLang['formHybridSubmitClass'] = array('Absendefeld CSS-Klasse', 'Vergeben Sie eine individuelle CSS-Klasse für das Absendefeld.');

$arrLang['formHybridSuccessMessage'] = array('Erfolgsmeldung überschreiben', 'Geben Sie hier eine alternative Erfolgsmeldung an.');
$arrLang['formHybridSkipScrollingToSuccessMessage'] = array('Nicht zur Erfolsmeldung scrollen', 'Wählen Sie diese Option, damit nicht automatisch zur Erfolgsmeldung gescrollt wird.');
$arrLang['formHybridSendSubmissionAsNotification'] = array('E-Mail über Benachrichtigungscenter versenden', 'Beim erfolgreichen absenden des Formulars wird eine E-Mail über den Benachrichtungscenter ausgelößt.');
$arrLang['formHybridSubmissionNotification'] = array('Benachrichtigung nach dem Absenden des Formulars verschicken', 'Wählen Sie hier eine Nachricht aus, die nach dem erfolgreichen Absenden des Formulars verschickt werden soll.');

$arrLang['formHybridSendSubmissionViaEmail'] = array('Per E-Mail versenden', 'Die Formulardaten an eine E-Mail-Adresse versenden.');
$arrLang['formHybridSubmissionMailSender'] = array('Absender', 'Bitte geben Sie hier die Absender-E-Mail-Adresse ein. <strong>Format mit Absender-Name: Name [Email]</strong>');
$arrLang['formHybridSubmissionMailRecipient'] = array('Empfänger-Adresse', 'Mehrere E-Mail-Adressen können mit Komma getrennt werden.');
$arrLang['formHybridSubmissionAvisotaMessage'] = array('Benachrichtigung', 'Wählen Sie hier eine Avisota-Nachricht aus.');
$arrLang['formHybridSubmissionAvisotaSalutationGroup'] = array('Anrede', 'Wählen Sie hier eine Avisota-Anrede aus. Die Anrede generiert sich aus Mitgliedern gleicher E-Mail-Adressen wie die Empfänger.');
$arrLang['formHybridSubmissionMailSubject'] = array('Betreff', 'Bitte geben Sie eine Betreffzeile für die Bestätigungs-E-Mail ein. Wenn Sie keine Betreffzeile erfassen, steigt die Wahrscheinlichkeit, dass die E-Mail als SPAM identifiziert wird.');
$arrLang['formHybridSubmissionMailText'] = array('Text der E-Mail', 'Bitte geben Sie hier den Text der E-Mail ein (##submission## gibt die gesammelten Formulardaten formatiert mit Label: Wert aus). Neben den allgemeinen Insert-Tags werden Tags der Form form::FORMULARFELDNAME unterstützt.');
$arrLang['formHybridSubmissionMailTemplate'] = array('E-Mail-Template', 'Hier können Sie das E-Mail-Template überschreiben.');
$arrLang['formHybridSubmissionMailAttachment'] = array('E-Mail Anhänge', 'Versenden Sie Dateien aus der Dateiverwaltung als Anhang.');

$arrLang['formHybridSendConfirmationAsNotification'] = array('Bestätigungs-E-Mail über Benachrichtigungscenter versenden', 'Wenn Sie diese Option wählen, wird eine Bestätigung per E-Mail an den Absender des Formulars über den Benachrichtungscenter ausgelößt.');
$arrLang['formHybridSendConfirmationViaEmail'] = array('Bestätigung per E-Mail versenden', 'Wenn Sie diese Option wählen, wird eine Bestätigung per E-Mail an den Absender des Formulars versendet.');
$arrLang['formHybridConfirmationNotification'] = array('Bestätigungsbenachrichtigung verschicken', 'Wählen Sie hier eine Nachricht aus, die nach dem erfolgreichen Absenden des Formulars an dessen Absender verschickt werden soll.');
$arrLang['formHybridConfirmationMailSender'] = array('Absender', 'Bitte geben Sie hier die Absender-E-Mail-Adresse ein. <strong>Format mit Absender-Name: Name [Email]</strong>');
$arrLang['formHybridConfirmationMailRecipientField'] = array('Formularfeld mit E-Mail-Adresse des Empfängers', 'Wählen Sie hier das Formularfeld, in dem der Absender seine E-Mail-Adresse angibt oder ein Formularfeld, das die Empfänger-Adresse als Wert enthält.');
$arrLang['formHybridConfirmationAvisotaMessage'] = array('Benachrichtigung', 'Wählen Sie hier eine Avisota-Nachricht aus.');
$arrLang['formHybridConfirmationAvisotaSalutationGroup'] = array('Anrede', 'Wählen Sie hier eine Avisota-Anrede aus. Die Anrede generiert sich aus der Form-Submission.');
$arrLang['formHybridConfirmationMailSubject'] = array('Betreff', 'Bitte geben Sie eine Betreffzeile für die Bestätigungs-E-Mail ein. Wenn Sie keine Betreffzeile erfassen, steigt die Wahrscheinlichkeit, dass die E-Mail als SPAM identifiziert wird.');
$arrLang['formHybridConfirmationMailText'] = array('Text der E-Mail', 'Bitte geben Sie hier den Text der E-Mail ein (##submission## gibt die gesammelten Formulardaten formatiert mit Label: Wert aus). Neben den allgemeinen Insert-Tags werden Tags der Form form::FORMULARFELDNAME unterstützt.');
$arrLang['formHybridConfirmationMailTemplate'] = array('E-Mail-Template', 'Hier können Sie das E-Mail-Template überschreiben.');
$arrLang['formHybridConfirmationMailAttachment'] = array('E-Mail Anhänge', 'Versenden Sie Dateien aus der Dateiverwaltung als Anhang.');
$arrLang['formHybridAddFieldDependentRedirect'] = array('Feldabhängige Weiterleitung hinzufügen', 'Wählen Sie diese Option, um eine Weiterleitung zu definieren, die durchgeführt wird, wenn ein Feld (oder mehrere) bestimmte Werte ausweist.');
$arrLang['formHybridFieldDependentRedirectConditions'] = array('Bedingungen für die Weiterleitung', '');
$arrLang['formHybridFieldDependentRedirectJumpTo'] = array('Feldabhängige Weiterleitungsseite', 'Wählen Sie hier die Seite, zu der unter der zuvor gewählten Bedingung weitergeleitet werden soll.');
$arrLang['formHybridFieldDependentRedirectKeepParams'] = array('GET-Parameter beibehalten', 'Geben Sie hier eine kommagetrennte Liste der GET-Parameter ein, die beim feldabhängigen Weiterleiten beibehalten werden. Wenn Sie in diesem Feld nichts eingeben, werden alle Parameter beibehalten.');

$arrLang['formHybridTemplate'] = array('Formular-Template', 'Hier können Sie das Formular-Template überschreiben.');

$arrLang['formHybridCustomSubTemplates'] = array('Eigene Formular-Template für Subpaletten', 'Verwenden Sie eigene Templates für Subpaletten, dann erstellen Sie ein hängen Sie dem Formular-Template _sub_[SUBPALETTE_KEY] an.');

$arrLang['formHybridIsComplete'] = array('Komplett', 'Diese Option wird automatisch vom Modul gesetzt, wenn das Formular mindestens einmal ');
$arrLang['formHybridAction'] = array('Form action / Zielseite', 'Wählen Sie eine Zielseite aus.');
$arrLang['formHybridAddHashToAction'] = array('Hash zur Form-Action hinzufügen', 'Wählen Sie diese Option, um der Form-Action die Form-ID als Hash hinzuzufügen.');

/**
 * References
 */
$arrLang['reference'][FORMHYBRID_VIEW_MODE_DEFAULT] = 'Standard (bearbeiten)';
$arrLang['reference'][FORMHYBRID_VIEW_MODE_READ] = 'Nur lesen';