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


class FormAjax extends \Controller
{
	/**
	 * Ajax action
	 * @var string
	 */
	protected $strAction;

	/**
	 * Ajax id
	 * @var string
	 */
	protected $strAjaxId;

	/**
	 * Ajax key
	 * @var string
	 */
	protected $strAjaxKey;

	/**
	 * Ajax name
	 * @var string
	 */
	protected $strAjaxName;


	/**
	 * Get the current action
	 * @param string
	 * @throws \Exception
	 */
	public function __construct($strAction)
	{
		if ($strAction == '')
		{
			throw new \Exception('Missing Ajax action');
		}

		$this->strAction = $strAction;
		parent::__construct();
		$this->import('Database');
	}

	/**
	 * Ajax actions that do require a data container object
	 * @param \DataContainer
	 */
	public function executePostActions(\DataContainer &$dc)
	{
		header('Content-Type: text/html; charset=' . \Config::get('characterSet'));

		$dca = $dc->getDca();

		switch ($this->strAction)
		{
			case 'asyncFormSubmit':
				if (\Input::post('load'))
				{
					echo $dc->edit(\Input::post('id'));
				}
			exit; break;
			case 'toggleSubpalette':

				if (!is_array($dca['palettes']['__selector__']) || !in_array($this->Input->post('field'), $dca['palettes']['__selector__']))
				{
					$this->log('Field "' . $this->Input->post('field') . '" is not an allowed selector field (possible SQL injection attempt)', __METHOD__, TL_ERROR);
					header('HTTP/1.1 400 Bad Request');
					die('Bad Request');
				}

				$dc->activeRecord->{\Input::post('field')} = (intval(\Input::post('state') == 1) ? 1 : '');

				if (\Input::post('load'))
				{
					echo $dc->edit(false, \Input::post('id'));
				}

			exit; break;
		}
	}
}
