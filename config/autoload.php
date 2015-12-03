<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
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
	// Forms
	'HeimrichHannot\FormHybrid\FormMultiColumnWizard'    => 'system/modules/formhybrid/forms/FormMultiColumnWizard.php',

	// Elements
	'HeimrichHannot\FormHybrid\ContentFormHybridStop'    => 'system/modules/formhybrid/elements/ContentFormHybridStop.php',
	'HeimrichHannot\FormHybrid\ContentFormHybridElement' => 'system/modules/formhybrid/elements/ContentFormHybridElement.php',
	'HeimrichHannot\FormHybrid\ContentFormHybridStart'   => 'system/modules/formhybrid/elements/ContentFormHybridStart.php',

	// Drivers
	'HeimrichHannot\FormHybrid\DC_Hybrid'                => 'system/modules/formhybrid/drivers/DC_Hybrid.php',

	// Classes
	'HeimrichHannot\FormHybrid\FrontendWidget'           => 'system/modules/formhybrid/classes/FrontendWidget.php',
	'HeimrichHannot\FormHybrid\Submission'               => 'system/modules/formhybrid/classes/Submission.php',
	'HeimrichHannot\FormHybrid\Form'                     => 'system/modules/formhybrid/classes/Form.php',
	'HeimrichHannot\FormHybrid\Hooks'                    => 'system/modules/formhybrid/classes/Hooks.php',
	'HeimrichHannot\FormHybrid\FormHelper'               => 'system/modules/formhybrid/classes/FormHelper.php',
	'HeimrichHannot\FormHybrid\FormAjax'                 => 'system/modules/formhybrid/classes/FormAjax.php',
	'HeimrichHannot\FormHybrid\TagsHelper'               => 'system/modules/formhybrid/classes/TagsHelper.php',
	'HeimrichHannot\FormHybrid\AvisotaHelper'            => 'system/modules/formhybrid/classes/AvisotaHelper.php',
	'HeimrichHannot\FormHybrid\FormSubmissionHelper'     => 'system/modules/formhybrid/classes/FormSubmissionHelper.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_formhybrid_stop'      => 'system/modules/formhybrid/templates/elements',
	'ce_formhybrid_start'     => 'system/modules/formhybrid/templates/elements',
	'formhybridStart_default' => 'system/modules/formhybrid/templates/form',
	'formhybrid_default_sub'  => 'system/modules/formhybrid/templates/form',
	'formhybrid_default'      => 'system/modules/formhybrid/templates/form',
	'formhybridStop_default'  => 'system/modules/formhybrid/templates/form',
));
