<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;


use HeimrichHannot\Haste\Util\Arrays;

class FormSession
{
	const FORMHYBRID_FORMSESSION_SUBMISSION_KEY = 'formhybrid_formsession_submission_key';

    const FORMHYBRID_FORMSESSION_START_KEY = 'formhybrid_formsession_start_module';
	
	/**
	 * Add submission id into user session
	 *
	 * @param $formId The form identifier
	 * @param $intId  The submission id
	 */
	public static function addSubmissionId($formId, $intId)
	{
		if (!is_array($_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY]))
		{
			$_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY] = [];
		}
		
		$_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY][$formId] = $intId;
	}
	
	/**
	 * Delete submission id from user session
	 *
	 * @param $formId The form identifier
	 *
	 * @return bool True, if submission id was found and deleted
	 */
	public static function freeSubmissionId($formId)
	{
		if (!isset($_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY][$formId])) {
			return false;
		}
		
		unset($_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY][$formId]);
		
		return true;
	}
	
	/**
	 * Determine valid submission id from user session
	 *
	 * @param $formId The form identifier
	 * @param $intId  The submission id
	 *
	 * @return bool True, if submission id was found
	 */
	public static function isValidSubmissionId($formId, $intId)
	{
		$arrIds = $_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY];
		
		if (!is_array($arrIds)) {
			return false;
		}
		
		if (!isset($_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY][$formId])) {
			return false;
		}
		
		if (!is_array($_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY][$formId])) {
			return false;
		}
		
		if (!in_array($intId, $_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY][$formId])) {
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Return valid submission ids from user session
	 *
	 * @param $formId
	 *
	 * @return array|null Array containing the submission ids for a specific formId
	 */
	public static function getSubmissionId($formId)
	{
		return $_SESSION[static::FORMHYBRID_FORMSESSION_SUBMISSION_KEY][$formId];
	}
}