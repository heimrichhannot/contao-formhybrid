<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['formHybridDataContainer'] = array('DataContainer', 'Wählen Sie hier den gewünschten DataContainer aus.');
$arrLang['formHybridEditable'] = array('Felder', 'Wählen Sie hier die gewünschten Felder aus.');
$arrLang['formHybridAddEditableRequired'] = array('Pflichtfelder überschreiben', 'Legen Sie die Pflichtfelder unabhängig von der DCA-Konfiguration fest.');
$arrLang['formHybridEditableRequired'] = array('Pflichtfelder', 'Wählen Sie hier die gewünschten Pflichtfelder aus.');
$arrLang['formHybridAddReadOnly'] = array('Nurlese-Felder hinzufügen ', 'Legen Sie Felder fest, die nur gelesen werden dürfen.');
$arrLang['formHybridReadOnly'] = array('Nurlese-Felder', 'Wählen Sie hier die gewünschten Nurlese-Felder aus.');
$arrLang['formHybridAddDisplayedSubPaletteFields'] = array('Immer anzuzeigende Sub-Paletten-Felder hinzufügen', 'Legen Sie die Felder fest, welche sich in Sub-Paletten befinden, aber immer angezeigt werden sollen.');
$arrLang['formHybridDisplayedSubPaletteFields'] = array('Immer anzuzeigende Sub-Paletten-Felder', 'Wählen Sie hier die gewünschten Felder aus.');
$arrLang['formHybridEditableSkip'] = array('Zu überspringende Felder', 'Wählen Sie hier die Felder aus, die vom Modell nicht zur Filterung genutzt werden sollen (abhängig von der Programmlogik).');
$arrLang['formHybridAddDefaultValues'] = array('Standardwerte hinzufügen', 'Wählen Sie diese Option, um Standardwerte für das Modul hinzuzufügen.');
$arrLang['formHybridDefaultValues'] = array('Standardwerte', 'Definieren Sie hier Standardwerte für das Modul.');
$arrLang['formHybridDefaultValues']['field'] = array('Feld', 'Wählen Sie hier das gewünschte Feld aus.');
$arrLang['formHybridDefaultValues']['value'] = array('Wert', 'Geben Sie hier den gewünschten Standardwert ein. Arrays bitte serialisiert eingeben.');
$arrLang['formHybridDefaultValues']['label'] = array('Label', 'Geben Sie optional eine alternative Bezeichnung/Label');

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
$arrLang['formHybridCustomHash'] = array('Standard-Hash überschreiben', 'Geben Sie hier bei Bedarf einen eigenen Hash-Wert ein.');
$arrLang['formHybridAddPermanentFields'] = array('Immer auszugebende Felder hinzufügen', 'Wählen Sie diese Option, um dem Formular bestimmte Felder unter allen Umständen hinzuzufügen. Diese Felder sind in aller Regel Subpalettenfelder, die auch dann ausgegeben werden, wenn deren Selektor nicht aktiv ist. Sinnvoll etwa, wenn Felder im Kontext mehrerer Type-Selektoren vorkommen.');
$arrLang['formHybridPermanentFields'] = array('Immer auszugebende Felder', 'Wählen Sie hier Felder aus, die immer ausgegeben werden sollen.');

$arrLang['formHybridResetAfterSubmission'] = array('Formular nach absenden zurücksetzen', 'Deaktivieren um nach Absenden, das Formular mit den Daten erneut zu laden. (Achtung: Nur einmaliges Absenden möglich!)');
$arrLang['formHybridSingleSubmission'] = array('Formular nur einmal erzeugen', 'Nachdem das Formular erfolgreich abgeschickt wurde, wird keine neue Entität erzeugt und nur Meldungen werden ausgegeben.');
$arrLang['formHybridJumpToPreserveParams'] = array('Parameter beibehalten', 'Wählen Sie diese Option, wenn die Weiterleitungsseite (nach dem Speichern) wieder ein Frontendedit-Leser-Modul enthält. Dadurch werden die aktuelle Action sowie die ID beibehalten.');

$arrLang['formHybridUseCustomFormId'] = array('FormId überschreiben', 'Wählen Sie diese Option, wenn sie die Id des Filtermodules bei der Submission überschreiben wollen.');
$arrLang['formHybridCustomFormId'] = array('Neue FormID', 'Geben Sie hier die Id ein, die das Filtermodul bei der Submission-Überprüfung haben soll.');

$arrLang['formHybridAllowIdAsGetParameter']        =
	array('ID-Eingabe als GET-Parameter erlauben (Vorsicht!)', 'Wählen Sie diese Option, wenn über den GET-Parameter "id" der anzuzeigende Datensatz bestimmt werden darf. ACHTUNG: Nur in Verbindung mit "Bedingungen für das Bearbeiten" verwenden!');
$arrLang['formHybridIdGetParameter']               = array('ID GET-Parameter', 'Geben Sie den GET-Parameter an, der zur Bestimmung des aktuellen Datensatzes verwendet werden soll.');
$arrLang['formHybridAppendIdToUrlOnCreation']      = array('Neue Instanz: ID GET-Parameter an URL anhängen', 'Bei der Erstellung von neuen Instanzen, den GET-Parameter an die URL anhängen.');
$arrLang['formHybridTransformGetParamsToHiddenFields']      = array('GET-Parameter in Hidden-Felder umwandeln', 'Sinnvoll bspw. bei Filterformularen im GET-Modus.');

/**
 * References
 */
$arrLang['reference'][FORMHYBRID_VIEW_MODE_DEFAULT] = 'Standard (bearbeiten)';
$arrLang['reference'][FORMHYBRID_VIEW_MODE_READONLY] = 'Nur lesen';