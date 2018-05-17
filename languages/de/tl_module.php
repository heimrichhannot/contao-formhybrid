<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['formHybridDataContainer']                = ['DataContainer', 'Wählen Sie hier den gewünschten DataContainer aus.'];
$arrLang['formHybridEditable']                     = ['Felder', 'Wählen Sie hier die gewünschten Felder aus.'];
$arrLang['formHybridForcePaletteRelation']         =
    ['Palettenbezug erzwingen', 'Der Bezug der Felder zu einer Palette ist standardmäßig aktiviert. Deaktivieren um Zugriff auf Felder von Paletten und Subpaletten ungeachtet Ihres Palettenbezugs zu erhalten.'];
$arrLang['formHybridAddEditableRequired']          = ['Pflichtfelder überschreiben', 'Legen Sie die Pflichtfelder unabhängig von der DCA-Konfiguration fest.'];
$arrLang['formHybridEditableRequired']             = ['Pflichtfelder', 'Wählen Sie hier die gewünschten Pflichtfelder aus.'];
$arrLang['formHybridAddReadOnly']                  = ['Nurlese-Felder hinzufügen ', 'Legen Sie Felder fest, die nur gelesen werden dürfen.'];
$arrLang['formHybridReadOnly']                     = ['Nurlese-Felder', 'Wählen Sie hier die gewünschten Nurlese-Felder aus.'];
$arrLang['formHybridAddDisplayedSubPaletteFields'] =
    ['Immer anzuzeigende Sub-Paletten-Felder hinzufügen', 'Legen Sie die Felder fest, welche sich in Sub-Paletten befinden, aber immer angezeigt werden sollen.'];
$arrLang['formHybridDisplayedSubPaletteFields']    = ['Immer anzuzeigende Sub-Paletten-Felder', 'Wählen Sie hier die gewünschten Felder aus.'];
$arrLang['formHybridEditableSkip']                 =
    ['Zu überspringende Felder', 'Wählen Sie hier die Felder aus, die vom Modell nicht zur Filterung genutzt werden sollen (abhängig von der Programmlogik).'];
$arrLang['formHybridAddDefaultValues']             = ['Standardwerte hinzufügen', 'Wählen Sie diese Option, um Standardwerte für das Modul hinzuzufügen.'];
$arrLang['formHybridDefaultValues']                = ['Standardwerte', 'Definieren Sie hier Standardwerte für das Modul.'];
$arrLang['formHybridDefaultValues']['field']       = ['Feld', 'Wählen Sie hier das gewünschte Feld aus.'];
$arrLang['formHybridDefaultValues']['value']       = ['Wert', 'Geben Sie hier den gewünschten Standardwert ein. Arrays bitte serialisiert eingeben.'];
$arrLang['formHybridDefaultValues']['label']       = ['Label', 'Geben Sie optional eine alternative Bezeichnung/Label'];

$arrLang['formHybridAsync'] = ['Formular asynchron absenden', 'Wählen Sie diese Option, wenn Sie das Formular asynchron versenden wollen.'];

$arrLang['formHybridCustomSubmit'] = ['Absendefeld anpassen', 'Den Absendefeld des Formulars anpassen.'];
$arrLang['formHybridSubmitLabel']  = ['Absendefeld Bezeichnung', 'Wählen Sie den Text, der auf den Absendefeld stehen soll.'];
$arrLang['formHybridSubmitClass']  = ['Absendefeld CSS-Klasse', 'Vergeben Sie eine individuelle CSS-Klasse für das Absendefeld.'];

$arrLang['formHybridSuccessMessage']                = ['Erfolgsmeldung überschreiben', 'Geben Sie hier eine alternative Erfolgsmeldung an.'];
$arrLang['formHybridSkipScrollingToSuccessMessage'] = ['Nicht zur Erfolsmeldung scrollen', 'Wählen Sie diese Option, damit nicht automatisch zur Erfolgsmeldung gescrollt wird.'];
$arrLang['formHybridSendSubmissionAsNotification']  =
    ['E-Mail über Benachrichtigungscenter versenden', 'Beim erfolgreichen absenden des Formulars wird eine E-Mail über den Benachrichtungscenter ausgelößt.'];
$arrLang['formHybridSubmissionNotification']        = [
    'Benachrichtigung nach dem Absenden des Formulars verschicken',
    'Wählen Sie hier eine Nachricht aus, die nach dem erfolgreichen Absenden des Formulars verschickt werden soll.',
];

$arrLang['formHybridSendConfirmationAsNotification']     = [
    'Bestätigungs-E-Mail über Benachrichtigungscenter versenden',
    'Wenn Sie diese Option wählen, wird eine Bestätigung per E-Mail an den Absender des Formulars über den Benachrichtungscenter ausgelößt.',
];
$arrLang['formHybridConfirmationNotification']           = [
    'Bestätigungsbenachrichtigung verschicken',
    'Wählen Sie hier eine Nachricht aus, die nach dem erfolgreichen Absenden des Formulars an dessen Absender verschickt werden soll.',
];
$arrLang['formHybridAddFieldDependentRedirect']          = [
    'Feldabhängige Weiterleitung hinzufügen',
    'Wählen Sie diese Option, um eine Weiterleitung zu definieren, die durchgeführt wird, wenn ein Feld (oder mehrere) bestimmte Werte ausweist.',
];
$arrLang['formHybridFieldDependentRedirectConditions']   = ['Bedingungen für die Weiterleitung', ''];
$arrLang['formHybridFieldDependentRedirectJumpTo']       =
    ['Feldabhängige Weiterleitungsseite', 'Wählen Sie hier die Seite, zu der unter der zuvor gewählten Bedingung weitergeleitet werden soll.'];
$arrLang['formHybridFieldDependentRedirectKeepParams']   = [
    'GET-Parameter beibehalten',
    'Geben Sie hier eine kommagetrennte Liste der GET-Parameter ein, die beim feldabhängigen Weiterleiten beibehalten werden. Wenn Sie in diesem Feld nichts eingeben, werden alle Parameter beibehalten.',
];

$arrLang['formHybridTemplate'] = ['Formular-Template', 'Hier können Sie das Formular-Template überschreiben.'];

$arrLang['formHybridCustomSubTemplates'] = [
    'Eigene Formular-Template für Subpaletten',
    'Verwenden Sie eigene Templates für Subpaletten, dann erstellen Sie ein hängen Sie dem Formular-Template _sub_[SUBPALETTE_KEY] an.',
];

$arrLang['formHybridIsComplete']         = ['Komplett', 'Diese Option wird automatisch vom Modul gesetzt, wenn das Formular mindestens einmal '];
$arrLang['formHybridAction']             = ['Form action / Zielseite', 'Wählen Sie eine Zielseite aus.'];
$arrLang['formHybridAddHashToAction']    = ['Hash zur Form-Action hinzufügen', 'Wählen Sie diese Option, um der Form-Action die Form-ID als Hash hinzuzufügen.'];
$arrLang['formHybridCustomHash']         = ['Standard-Hash überschreiben', 'Geben Sie hier bei Bedarf einen eigenen Hash-Wert ein.'];
$arrLang['removeAutoItemFromAction']     =
    ['Auto_item aus der Form-Action entfernen', 'Wählen Sie diese Option, wenn ein eventuell existierendes auto_item aus der Form-Action entfernt werden soll.'];
$arrLang['formHybridAddPermanentFields'] = [
    'Immer auszugebende Felder hinzufügen',
    'Wählen Sie diese Option, um dem Formular bestimmte Felder unter allen Umständen hinzuzufügen. Diese Felder sind in aller Regel Subpalettenfelder, die auch dann ausgegeben werden, wenn deren Selektor nicht aktiv ist. Sinnvoll etwa, wenn Felder im Kontext mehrerer Type-Selektoren vorkommen.',
];
$arrLang['formHybridPermanentFields']    = ['Immer auszugebende Felder', 'Wählen Sie hier Felder aus, die immer ausgegeben werden sollen.'];

$arrLang['formHybridResetAfterSubmission'] =
    ['Formular nach dem Abschicken zurücksetzen', 'Deaktivieren um nach Absenden, das Formular mit den Daten erneut zu laden. (Achtung: Nur einmaliges Absenden möglich!)'];
$arrLang['formHybridSingleSubmission']     =
    ['Formular nur einmal erzeugen', 'Nachdem das Formular erfolgreich abgeschickt wurde, wird keine neue Entität erzeugt und nur Meldungen werden ausgegeben.'];
$arrLang['formHybridJumpToPreserveParams'] = [
    'Parameter beibehalten',
    'Wählen Sie diese Option, wenn die Weiterleitungsseite (nach dem Speichern) wieder ein Frontendedit-Leser-Modul enthält. Dadurch werden die aktuelle Action sowie die ID beibehalten.',
];

$arrLang['formHybridUseCustomFormId'] = ['FormId überschreiben', 'Wählen Sie diese Option, wenn sie die Id des Filtermodules bei der Submission überschreiben wollen.'];
$arrLang['formHybridCustomFormId']    = ['Neue FormID', 'Geben Sie hier die Id ein, die das Filtermodul bei der Submission-Überprüfung haben soll.'];

$arrLang['formHybridAllowIdAsGetParameter']            = [
    'ID-Eingabe als GET-Parameter erlauben (Vorsicht!)',
    'Wählen Sie diese Option, wenn über den GET-Parameter "id" der anzuzeigende Datensatz bestimmt werden darf. ACHTUNG: Nur in Verbindung mit "Bedingungen für das Bearbeiten" verwenden!',
];
$arrLang['formHybridIdGetParameter']                   =
    ['ID GET-Parameter', 'Geben Sie den GET-Parameter an, der zur Bestimmung des aktuellen Datensatzes verwendet werden soll.'];
$arrLang['formHybridAppendIdToUrlOnCreation']          =
    ['Neue Instanz: ID GET-Parameter an URL anhängen', 'Bei der Erstellung von neuen Instanzen, den GET-Parameter an die URL anhängen.'];
$arrLang['formHybridTransformGetParamsToHiddenFields'] = ['GET-Parameter in Hidden-Felder umwandeln', 'Sinnvoll bspw. bei Filterformularen im GET-Modus.'];

$arrLang['formHybridExportAfterSubmission']                =
    ['Datensatz nach dem Abschicken exportieren', 'Wählen Sie diese Option, wenn der Datensatz nach validem Abschicken exportiert werden soll.'];
$arrLang['formHybridExportConfigs']                        = ['Konfiguration', 'Fügen Sie hier neue Konfigurationen für den Export hinzu.'];
$arrLang['formhybrid_formHybridExportConfigs_config']      = ['Exporter-Konfiguration', 'Wählen Sie hier die gewünschte Exporter-Konfiguration aus.'];
$arrLang['formhybrid_formHybridExportConfigs_entityField'] =
    ['Exportdatei in Feld speichern', 'Wählen Sie hier das Feld aus, in das eine Referenz zur Exportdatei gespeichert werden soll.'];

$arrLang['formHybridEnableAutoComplete'] = [
    'Formular "autocomplete" aktivieren',
    'Aktivieren Sie autocomplete für dieses Formular (nicht empfohlen). <strong>Achtung: Formularwerte werden im Browser zwischengespeichert und beim Klicken auf "Zurück" im Browser wiederhergestellt.</strong>',
];
$arrLang['formHybridAddExportButton']    = [
    'Export-Button hinzufügen',
    'Wählen Sie diese Option, wenn dem Formular ein Export-Button hinzugefügt werden soll. Achtung: hierfür muss eine passende Exporter-Konfiguration angelegt sein!',
];

$arrLang['formHybridAddOptIn']            = ['Opt-in Verfahren aktivieren', 'Aktivieren Sie das Opt-In Zustimmungsverfahren für Einsendungen.'];
$arrLang['formHybridOptInNotification']   = [
    'Opt-in Benachrichtigung',
    'Wählen Sie hier eine Nachricht aus, die nach dem erfolgreichen Absenden des Formulars an dessen Absender verschickt werden soll um dessen Authentizität zu überprüfen.',
];
$arrLang['formHybridOptInSuccessMessage'] =
    ['Opt-in Erfolgsmeldung überschreiben', 'Geben Sie hier eine alternative Opt-in Erfolgsmeldung nach erfolgreichen Absenden des Formulars an.'];
$arrLang['formHybridOptInConfirmedProperty'] =
    ['Opt-in Erfolg Property', 'Ein Property (Boolean), welches bei erfolgreichem Opt-In auf true gesetzt werden soll.'];
$arrLang['formHybridOptInJumpTo'] =
    ['Opt-in Redirect', 'Diese Seite wird nach erfolgreicher Bestätigung der Anmeldung aufgerufen.'];

$arrLang['formHybridAddOptOut']            = ['Opt-out Verfahren aktivieren', 'Aktivieren Sie die Generierung von Links, um eine erstellte Entität wieder zu löschen.'];
$arrLang['formHybridOptOutSuccessMessage'] = ['Opt-out Erfolgsmeldung überschreiben', 'Geben Sie hier eine alternative Opt-out Erfolgsmeldung an, welche dem Benutzer nach erfolgreichem Opt-out angezeigt wird.'];
$arrLang['formHybridOptOutJumpTo'] = ['Opt-out Redirect', 'Diese Seite wird nach erfolgreicher Abmeldung aufgerufen.'];

$arrLang['formHybridAddPrivacyProtocolEntry'][0] = 'Nach dem Abschicken einen Eintrag im Datenschutzprotokoll anlegen';
$arrLang['formHybridAddPrivacyProtocolEntry'][1] = 'Wählen Sie diese Option, wenn nach validem Abschicken ein Eintrag im Datenschutzprotokoll angelegt werden soll.';
$arrLang['formHybridPrivacyProtocolArchive'][0] = 'Protokoll-Archiv';
$arrLang['formHybridPrivacyProtocolArchive'][1] = 'Wählen hier das Archiv, in dem der Eintrag gespeichert werden soll.';
$arrLang['formHybridPrivacyProtocolEntryType'][0] = 'Typ';
$arrLang['formHybridPrivacyProtocolEntryType'][1] = 'Wählen hier den Typ des Protokolleintrags aus.';
$arrLang['formHybridPrivacyProtocolDescription'][0] = 'Beschreibung';
$arrLang['formHybridPrivacyProtocolDescription'][1] = 'Geben Sie hier bei Bedarf einen Beschreibungstext für den Protokolleintrag ein.';
$arrLang['formHybridPrivacyProtocolFieldMapping'][0] = 'Feldabbildung';
$arrLang['formHybridPrivacyProtocolFieldMapping'][1] = 'Wählen Sie hier bei Bedarf Felder des Datensatzes aus, die in den Protokolleintrag überführt werden sollen.';
$arrLang['formHybridPrivacyProtocolFieldMapping_entityField'][0] = 'Feld im Datensatz';
$arrLang['formHybridPrivacyProtocolFieldMapping_protocolField'][0] = 'Feld im Protokolleintrag';



/**
 * Explanations
 */
$arrLang['formHybridOptInExplanation'] =
    'Beim Opt-in Verfahren wird eine E-Mail an den Nutzer gesendet, in der er seine Authentizität durch den Aufruf des Aktivierungslinks in der E-Mail bestätigt. Erst nachdem die Einsendung durch das Opt-In-Verfahren verifiziert wurde, werden E-Mail Benachrichtigungen & E-Mail Bestätigungen verschickt. <br /> <b>Entwicklerhinweis: In der DCA-Datei der Entität <u>muss</u> am Ende `\HeimrichHannot\FormHybrid\FormHybrid::addOptInFieldToTable([TABELLENNAME]);` aufgerufen werden, damit das Feld `'
    . HeimrichHannot\FormHybrid\FormHybrid::OPT_IN_DATABASE_FIELD . '` der Tabelle hinzugefügt wird!</b>';

/**
 * References
 */
$arrLang['reference'][FORMHYBRID_VIEW_MODE_DEFAULT]  = 'Standard (bearbeiten)';
$arrLang['reference'][FORMHYBRID_VIEW_MODE_READONLY] = 'Nur lesen';
