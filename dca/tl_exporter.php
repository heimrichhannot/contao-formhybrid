<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_exporter'];

/**
 * Palettes
 */
$arrDca['palettes'][\HeimrichHannot\FormHybrid\FormHybrid::EXPORT_TYPE_FORMHYBRID] =
    '{title_legend},title,type;' .
    '{export_legend},target,fileType;' .
    '{table_legend},linkedTable,skipFields,skipLabels,pdfTemplate;';

/**
 * Fields
 */
$arrDca['fields']['type']['options'][] = \HeimrichHannot\FormHybrid\FormHybrid::EXPORT_TYPE_FORMHYBRID;