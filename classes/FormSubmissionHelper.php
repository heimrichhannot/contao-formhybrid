<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package formhybrid
 * @author Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\FormHybrid;


class FormSubmissionHelper extends FormHelper
{

	public static function tokenizeData(array $arrSubmissionData = array())
	{
		$arrTokens = array();

		foreach($arrSubmissionData as $strName => $arrData)
		{
			if(!is_array($arrData))
			{
				continue;
			}

			foreach($arrData as $strType => $varValue)
			{
				$value = $varValue;

				if(!is_array($varValue) && \Validator::isBinaryUuid($varValue))
				{
					$varValue = \String::binToUuid($varValue);
					$value = $varValue;

					$objFile = \FilesModel::findByUuid($varValue);

					if($objFile !== null)
					{
						$value = $objFile->path;
					}
				}

				switch($strType)
				{
					case 'output':
						$arrTokens['form_' . $strName] = $value;
						$arrTokens['form_plain_' . $strName] = \HeimrichHannot\Haste\Util\StringUtil::convertToText(\String::decodeEntities($value), true);
					break;
					case 'value':
						$arrTokens['form_value_' . $strName] = $varValue;
					break;
					case 'submission':
						$arrTokens['form_submission_' . $strName] = rtrim($value, "\n");
					break;
				}
			}
		}

		// token: ##formsubmission_all##
		if(isset($arrSubmissionData['submission_all']))
		{
			$arrTokens['formsubmission_all'] = $arrSubmissionData['submission_all'];
		}

		// token: ##formsubmission##
		if(isset($arrSubmissionData['submission']))
		{
			$arrTokens['formsubmission'] = $arrSubmissionData['submission'];
		}

		return $arrTokens;
	}

	public static function prepareData(\Model $objSubmission, array $arrDca, $objDc, $arrFields=array(), $arrSkipFields=array('id', 'pid', 'tstamp', 'password'))
	{
		$arrSubmissionData = array();
		$arrRow = $objSubmission->row();

		if (empty($arrFields))
			$arrFields = $arrRow;

		foreach ($arrFields as $strName)
		{
			$varValue = $arrRow[$strName];
			if(empty($varValue)) continue;

			$arrData = $arrDca['fields'][$strName];

			$arrFieldData = static::prepareDataField($strName, $varValue, $arrData, $objDc);

			$arrSubmissionData[$strName] = $arrFieldData;
			$strSubmission = $arrFieldData['submission'];

			$varValue = deserialize($varValue);

			// multicolumnwizard support
			if ($arrData['inputType'] == 'multiColumnWizard') {
				foreach ($varValue as $arrSet) {
					if (!is_array($arrSet)) {
						continue;
					}

					// new line
					$strSubmission .= "\n";

					foreach ($arrSet as $strSetName => $strSetValue) {
						$arrSetData   = $arrData['eval']['columnFields'][$strSetName];
						$arrFieldData = static::prepareDataField($strSetName, $strSetValue, $arrSetData, $objDc);
						// intend new line
						$strSubmission .= "\t" . $arrFieldData['submission'];
					}

					// new line
					$strSubmission .= "\n";
				}
			}

			$arrSubmissionData['submission_all'] .= $strSubmission;

			if(in_array($strName, $arrFields) && !in_array($strName, $arrSkipFields))
			{
				$arrSubmissionData['submission'] .= $strSubmission;
			}
		}

		return $arrSubmissionData;
	}

	public static function prepareDataField($strName, $varValue, $arrData, $objDc)
	{
		$strLabel = isset($arrData['label'][0]) ? $arrData['label'][0] : $strName;

		$strOutput = static::getFormatedValueByDca($varValue, $arrData, $objDc);

		$varValue = deserialize($varValue);

		$strSubmission = $strLabel . ": " . $strOutput . "\n";

		return array('value' => $varValue, 'output' => $strOutput, 'submission' => $strSubmission);
	}
}