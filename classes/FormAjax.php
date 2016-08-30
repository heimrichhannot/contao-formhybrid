<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package anwaltverein
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;

use HeimrichHannot\Ajax\Response\ResponseData;
use HeimrichHannot\Ajax\Response\ResponseError;
use HeimrichHannot\Ajax\Response\ResponseSuccess;
use HeimrichHannot\Request\Request;
use HeimrichHannot\StatusMessages\StatusMessage;

class FormAjax
{
	/**
	 * Current DataContainer DC_Hybrid
	 *
	 * @var object
	 */
	protected $dc;
	
	/**
	 * Datacontainer array
	 *
	 * @var array
	 */
	protected $dca;
	
	/**
	 * HTML for the response object
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * Overwrite isSubmitted
	 * @var boolean
	 */
	protected $forceIsSubmitted;
	
	/**
	 * Get the current action
	 *
	 * @param $dc   DC_Hybrid
	 * @param $html string
	 *
	 * @throws \Exception
	 */
	public function __construct(DC_Hybrid $dc, $html = '', $forceIsSubmitted = false)
	{
		$this->dc   = $dc;
		$this->dca  = $dc->getDca();
		$this->html = $html;
		$this->forceIsSubmitted = $forceIsSubmitted;
		$this->dc->setRelatedAjaxRequest(true);
	}

	/**
	 * Reload the form without validation (TypeSelector, Concatenated TypeSelecotor, or fields with submitOnChange, but no Subpalettes/Selector
	 * @return ResponseSuccess|void
	 */
	public function reload()
	{
		if(!$this->dc->isSubmitted())
		{
			return;
		}

		// 1st call, set skipValidation and doNotSubmit, to generate the form without validation
		if (!$this->html)
		{
			$this->dc->setSkipValidation(true);
			$this->dc->setDoNotSubmit(true);
			return;
		}
		
		// 2nd call, we return the generated form without validation
		return $this->asyncFormSubmit();
	}
	
	/**
	 * Async form Submit
	 * @return ResponseSuccess
	 */
	public function asyncFormSubmit()
	{
		if(!$this->dc->isSubmitted() && !$this->forceIsSubmitted)
		{
			return;
		}

		$objResponse = new ResponseSuccess();
		$objResponse->setResult(new ResponseData($this->html, array('id' => $this->dc->getFormId())));
		StatusMessage::reset($this->dc->objModule->id); // reset messages after html has been submitted
		return $objResponse;
	}
	
	/**
	 * Toggle Subpalette
	 * @param      $id
	 * @param      $strField
	 * @param bool $blnLoad
	 *
	 * @return ResponseError|ResponseSuccess
	 */
	function toggleSubpalette($id, $strField, $blnLoad = false)
	{
		if(!$this->dc->isSubmitted())
		{
			return;
		}

		$varValue = Request::getPost($strField) ?: 0;
		
		if (!is_array($this->dca['palettes']['__selector__']) || !in_array($strField, $this->dca['palettes']['__selector__'])) {
			\Controller::log('Field "' . $strField . '" is not an allowed selector field (possible SQL injection attempt)', __METHOD__, TL_ERROR);
			
			return new ResponseError();
		}
		
		$arrData = $this->dca['fields'][$strField];
		
		if (!Validator::isValidOption($varValue, $arrData, $this->dc)) {
			\Controller::log('Field "' . $strField . '" value is not an allowed option (possible SQL injection attempt)', __METHOD__, TL_ERROR);
			
			return new ResponseError();
		}
		
		if (empty(FormHelper::getFieldOptions($arrData, $this->dc))) {
			$varValue = (intval($varValue) ? 1 : '');
		}
		
		$this->dc->setSkipValidation(true); // do not validate fields
		$this->dc->setDoNotSubmit(true);
		$this->dc->activeRecord->{$strField} = $varValue;
		
		$objResponse = new ResponseSuccess();
		
		if ($blnLoad)
		{
			$objResponse->setResult(new ResponseData($this->dc->edit(false, $id)));
		}
		
		return $objResponse;
	}
}
