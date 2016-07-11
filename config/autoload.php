<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
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
	// Forms
	'HeimrichHannot\FormHybrid\FormMultiColumnWizard'         => 'system/modules/formhybrid/forms/FormMultiColumnWizard.php',
	'HeimrichHannot\FormHybrid\FormReadonlyField'             => 'system/modules/formhybrid/forms/FormReadonlyField.php',

	// Test
	'HeimrichHannot\FormHybrid\Test\Form\Test\SimpleFormTest' => 'system/modules/formhybrid/test/tests/FormHybrid/Form/Test/SimpleFormTest.php',

	// Drivers
	'HeimrichHannot\FormHybrid\DC_Hybrid'                     => 'system/modules/formhybrid/drivers/DC_Hybrid.php',

	// Elements
	'HeimrichHannot\FormHybrid\ContentFormHybridStop'         => 'system/modules/formhybrid/elements/ContentFormHybridStop.php',
	'HeimrichHannot\FormHybrid\ContentFormHybridElement'      => 'system/modules/formhybrid/elements/ContentFormHybridElement.php',
	'HeimrichHannot\FormHybrid\ContentFormHybridStart'        => 'system/modules/formhybrid/elements/ContentFormHybridStart.php',

	// Classes
	'HeimrichHannot\FormHybrid\Submission'                    => 'system/modules/formhybrid/classes/Submission.php',
	'HeimrichHannot\FormHybrid\Form'                          => 'system/modules/formhybrid/classes/Form.php',
	'HeimrichHannot\FormHybrid\FormAjax'                      => 'system/modules/formhybrid/classes/FormAjax.php',
	'HeimrichHannot\FormHybrid\AvisotaHelper'                 => 'system/modules/formhybrid/classes/AvisotaHelper.php',
	'HeimrichHannot\FormHybrid\Hooks'                         => 'system/modules/formhybrid/classes/Hooks.php',
	'HeimrichHannot\FormHybrid\FormConfiguration'             => 'system/modules/formhybrid/classes/FormConfiguration.php',
	'HeimrichHannot\FormHybrid\TagsHelper'                    => 'system/modules/formhybrid/classes/TagsHelper.php',
	'Contao\Versions'                                         => 'system/modules/formhybrid/classes/Versions.php',
	'HeimrichHannot\FormHybrid\Backend\ModuleBackend'         => 'system/modules/formhybrid/classes/Backend/ModuleBackend.php',
	'HeimrichHannot\FormHybrid\Validator'                     => 'system/modules/formhybrid/classes/Validator.php',
	'HeimrichHannot\FormHybrid\FormHelper'                    => 'system/modules/formhybrid/classes/FormHelper.php',
	'HeimrichHannot\FormHybrid\FormSubmissionHelper'          => 'system/modules/formhybrid/classes/FormSubmissionHelper.php',
	'HeimrichHannot\FormHybrid\FrontendWidget'                => 'system/modules/formhybrid/classes/FrontendWidget.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'form_readonly'              => 'system/modules/formhybrid/templates/forms',
	'formhybridreadonly_default' => 'system/modules/formhybrid/templates/readonly',
	'formhybrid_default'         => 'system/modules/formhybrid/templates/form',
	'formhybrid_default_sub'     => 'system/modules/formhybrid/templates/form',
	'formhybridStart_default'    => 'system/modules/formhybrid/templates/form',
	'formhybridStop_default'     => 'system/modules/formhybrid/templates/form',
	'ce_formhybrid_start'        => 'system/modules/formhybrid/templates/elements',
	'ce_formhybrid_stop'         => 'system/modules/formhybrid/templates/elements',
));
