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
ClassLoader::addNamespaces(
    [
	'HeimrichHannot',]
);


/**
 * Register the classes
 */
ClassLoader::addClasses(
    [
	// Forms
	'HeimrichHannot\FormHybrid\FormReadonlyField'        => 'system/modules/formhybrid/forms/FormReadonlyField.php',
	'HeimrichHannot\FormHybrid\FormMultiColumnWizard'    => 'system/modules/formhybrid/forms/FormMultiColumnWizard.php',

	// Classes
	'HeimrichHannot\FormHybrid\Submission'               => 'system/modules/formhybrid/classes/Submission.php',
	'HeimrichHannot\FormHybrid\FormAjax'                 => 'system/modules/formhybrid/classes/FormAjax.php',
	'HeimrichHannot\FormHybrid\FormSession'              => 'system/modules/formhybrid/classes/FormSession.php',
	'HeimrichHannot\FormHybrid\FormHybrid'               => 'system/modules/formhybrid/classes/FormHybrid.php',
	'HeimrichHannot\FormHybrid\FormHelper'               => 'system/modules/formhybrid/classes/FormHelper.php',
	'HeimrichHannot\FormHybrid\Backend\ModuleBackend'    => 'system/modules/formhybrid/classes/Backend/ModuleBackend.php',
	'HeimrichHannot\FormHybrid\TagsHelper'               => 'system/modules/formhybrid/classes/TagsHelper.php',
	'HeimrichHannot\FormHybrid\FormConfiguration'        => 'system/modules/formhybrid/classes/FormConfiguration.php',
	'HeimrichHannot\FormHybrid\Form'                     => 'system/modules/formhybrid/classes/Form.php',
	'HeimrichHannot\FormHybrid\FrontendWidget'           => 'system/modules/formhybrid/classes/FrontendWidget.php',
	'HeimrichHannot\FormHybrid\Hooks'                    => 'system/modules/formhybrid/classes/Hooks.php',
	'HeimrichHannot\FormHybrid\Validator'                => 'system/modules/formhybrid/classes/Validator.php',
	'HeimrichHannot\FormHybrid\AvisotaHelper'            => 'system/modules/formhybrid/classes/AvisotaHelper.php',
	'HeimrichHannot\FormHybrid\DatabaseUpdater'          => 'system/modules/formhybrid/classes/DatabaseUpdater.php',
	'HeimrichHannot\FormHybrid\FormSubmissionHelper'     => 'system/modules/formhybrid/classes/FormSubmissionHelper.php',

	// Elements
	'HeimrichHannot\FormHybrid\ContentFormHybridStop'    => 'system/modules/formhybrid/elements/ContentFormHybridStop.php',
	'HeimrichHannot\FormHybrid\ContentFormHybridStart'   => 'system/modules/formhybrid/elements/ContentFormHybridStart.php',
	'HeimrichHannot\FormHybrid\ContentFormHybridElement' => 'system/modules/formhybrid/elements/ContentFormHybridElement.php',

	// Drivers
	'HeimrichHannot\FormHybrid\DC_Hybrid'                => 'system/modules/formhybrid/drivers/DC_Hybrid.php',]
);


/**
 * Register the templates
 */
TemplateLoader::addFiles(
    [
	'form_readonly'                  => 'system/modules/formhybrid/templates/forms',
	'formhybridreadonly_default'     => 'system/modules/formhybrid/templates/readonly',
	'formhybridreadonly_default_sub' => 'system/modules/formhybrid/templates/readonly',
	'formhybrid_default'             => 'system/modules/formhybrid/templates/form',
	'formhybridStart_default'        => 'system/modules/formhybrid/templates/form',
	'formhybridStop_default'         => 'system/modules/formhybrid/templates/form',
	'formhybrid_default_sub'         => 'system/modules/formhybrid/templates/form',
	'ce_formhybrid_start'            => 'system/modules/formhybrid/templates/elements',
	'ce_formhybrid_stop'             => 'system/modules/formhybrid/templates/elements',]
);
