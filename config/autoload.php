<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Formhybrid
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'HeimrichHannot\FormHybrid\Form'       => 'system/modules/formhybrid/classes/Form.php',
	'HeimrichHannot\FormHybrid\Submission' => 'system/modules/formhybrid/classes/Submission.php',

	// Drivers
	'HeimrichHannot\FormHybrid\DC_Hybrid'  => 'system/modules/formhybrid/drivers/DC_Hybrid.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'formhybrid_default' => 'system/modules/formhybrid/templates/form',
));
