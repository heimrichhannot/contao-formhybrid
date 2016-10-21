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


$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][10][0] = '{{form::FIELDNAME}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][10][1] =
	'Returns the <strong>formatted value</strong> of the form field. This touches localizations, option values, special fields like date/time, ...';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][11][0] = '{{form_value::FIELDNAME}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][11][1] =
	'Returns the <strong>unformatted value</strong> of the form field. This touches localizations, option values, special fields like date/time, ...';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][12][0] = '{{form_submission::FIELDNAME}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][12][1] =
	'Returns <strong>a combination of field label and formatted value</strong>. (Format: "label: value")';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][13][0] = '{{if \'FOO\' != \'BAR\'}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][14][1] =
	'Allows the usage of an <i>if</i>-statement. Further inserttags can be used in between this statement.<br /><br />Example: {{if \'{{form_submission::FIELDNAME}}\' == \'FOO\'}}.<br /><br /><strong>Attention: Must be closed by {{endif}}.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][15][0] = '{{elseif \'FOO\' != \'BAR\'}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][15][1] =
	'Allows the usage of an <i>elseif</i>-statement. Further inserttags can be used in between this statement.<br /><br />Example: {{elseif \'{{form_submission::FIELDNAME}}\' == \'BAR\'}}.<br /><br /><strong>Attention: Can only be used inside of a {{if-condition}} and the condition must be closed by {{endif}}.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][16][0] = '{{else}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][16][1] =
	'Allows the usage of an <i>else</i>-statement. <strong>Attention: Can only be used inside of a {{if-condition}} and the condition must be closed by {{endif}}.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][17][0] = '{{endif}}';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags'][17][1] =
	'Closes the <i>if</i>-statement. <strong>Attention: Must be used in combination with {{if}}.</strong>';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][0][0] = '##submission##';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][0][1] = 'Returns a combination of label and the formatted value <strong>of all form inputs</strong>. (Format: "Label: value")';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][1][0] = '##submission_all##';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][1][1] = 'Returns a combination of label and the formatted value <strong>all form inputs and default values</strong>. (Format: "Label: value")';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][2][0] = '##domain##';
$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'][2][1] = 'Returns the qualified domain name the form had been sent from.';

$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_text'] = array_merge(
	$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags_tokens'],
	$GLOBALS['TL_LANG']['XPL']['formhybrid_inserttags']
);
