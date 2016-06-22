<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_version'];

$arrFields = array(
	'memberusername' => array(
		'sql'                     => "varchar(255) NOT NULL default ''"
	),
	'memberid' => array(
		'sql'                     => "int(10) unsigned NOT NULL default '0'"
	)
);

$arrDca['fields'] = array_merge($arrFields,  $arrDca['fields']);