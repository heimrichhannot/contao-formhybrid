<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_version'];

$arrFields = array(
	'memberusername' => array(
		'sql' => "varchar(255) NULL"
	),
	'memberid' => array(
		'sql' => "int(10) unsigned NULL"
	)
);

$arrDca['fields'] = array_merge($arrFields, $arrDca['fields']);