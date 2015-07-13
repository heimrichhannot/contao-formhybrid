<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package formhybrid
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][10][0] = '{{form::FELDNAME}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][10][1] =
	'Liefert den <strong>formatierten Eingabewert</strong> des Formularfeldes zurück. Für Select-Felder, Radio-Buttons und Checkboxen wird die Eingabebezeichnung ausgegeben.';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][11][0] = '{{form_value::FELDNAME}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][11][1] =
	'Liefert den <strong>tatsächlichen Eingabewert</strong> des Formularfeldes zurück. Für Select-Felder, Radio-Buttons und Checkboxen wird der Eingabewert ausgegeben';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][12][0] = '{{form_submission::FELDNAME}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][12][1] =
	'Liefert eine <strong>Kombination aus Feldbezeichnung (Label) und dem formatierten Eingabewert</strong> des Formularfeldes zurück. (Format: "Felzbezeichnung : Eingabebezeichnung")';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][13][0] = '{{if \'FOO\' != \'BAR\'}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][14][1] =
	'Ermöglicht Verwendung von einer <i>if</i>-Kontrollstruktur. Weitere Inserttags können innerhalb dieser Bedingung vewendet werden.<br /><br />Beispiel: {{if \'{{form_submission::FELDNAME}}\' == \'FOO\'}}.<br /><br /><strong>Achtung: Muss durch {{endif}} geschlossen werden.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][15][0] = '{{elseif \'FOO\' != \'BAR\'}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][15][1] =
	'Ermöglicht Verwendung von einer <i>elseif</i>-Kontrollstruktur. Weitere Inserttags können innerhalb dieser Bedingung vewendet werden.<br /><br />Beispiel: {{elseif \'{{form_submission::FELDNAME}}\' == \'BAR\'}}.<br /><br /><strong>Achtung: Kann nur innerhalb einer {{if-Bedingung}} verwendet werden und die komplette Bedingung muss durch {{endif}} geschlossen werden.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][16][0] = '{{else}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][16][1] =
	'Ermöglicht Verwendung von einer <i>else</i>-Kontrollstruktur. <strong>Achtung: Kann nur innerhalb einer {{if-Bedingung}} verwendet werden und die komplette Bedingung muss durch {{endif}} geschlossen werden.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][17][0] = '{{endif}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][17][1] =
	'Beendet eine <i>if</i>-Kontrollstruktur. <strong>Achtung: Muss bei Verwendung einer {{if-Bedingung}} verwendet werden.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][0][0] = '##submission##';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][0][1] = 'Liefert eine Kombination aus Feldbezeichnung (Label) und dem formatierten Eingabewert <strong>aller Eingaben des Formulars formatiert</strong> zurück. (Format: "Felzbezeichnung : Eingabebezeichnung")';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][1][0] = '##domain##';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][1][1] = 'Liefert den qualifizierten Domainnamen zurück über die das Formular abgesendet wurde.';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_text'] = array_merge(
	$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'],
	$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags']
);
