<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package formhybrid
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FormHybrid;


class FormMultiColumnWizard extends \MultiColumnWizard
{

	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		// load the callback data if there's any (do not do this in __set() already because then we don't have access to currentRecord)
		if (is_array($this->arrCallback))
		{
			$this->import($this->arrCallback[0]);
			$this->columnFields = $this->{$this->arrCallback[0]}->{$this->arrCallback[1]}($this);
		}

		$this->strCommand = 'cmd_' . $this->strField;

		// TODO: Actions
		if ($this->Input->get($this->strCommand) && is_numeric($this->Input->get('cid')) && $this->Input->get('id') == $this->currentRecord)
		{

			switch ($this->Input->get($this->strCommand))
			{
				case 'copy':
					$this->varValue = array_duplicate($this->varValue, $this->Input->get('cid'));
					break;

				case 'up':
					$this->varValue = array_move_up($this->varValue, $this->Input->get('cid'));
					break;

				case 'down':
					$this->varValue = array_move_down($this->varValue, $this->Input->get('cid'));
					break;

				case 'delete':
					$this->varValue = array_delete($this->varValue, $this->Input->get('cid'));
					break;
			}

			// Save in File
			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'] == 'File')
			{
				$this->Config->update(sprintf("\$GLOBALS['TL_CONFIG']['%s']", $this->strField), serialize($this->varValue));

				// Reload the page
				$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($this->strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
			}
			// Save in table
			else if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'] == 'Table')
			{
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['save_callback']))
				{
					$dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'];
					// If less than 3.X, we must load the class by hand.
					if (version_compare(VERSION, '3.0', '<'))
					{
						require_once(sprintf('%s/system/drivers/%s.php', TL_ROOT, $dataContainer));
					}

					$dc            = new $dataContainer($this->strTable);
					$dc->field     = $objWidget->id;
					$dc->inputName = $objWidget->id;

					foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['save_callback'] AS $callback)
					{
                        if (is_array($callback)) {
                            $this->import($callback[0]);
                            $this->{$callback[0]}->{$callback[1]}(serialize($this->varValue), $dc);
                        } elseif (is_callable($callback)) {
                            $callback(serialize($this->varValue), $dc);
                        }

						$this->import($callback[0]);
						$this->{$callback[0]}->{$callback[1]}(serialize($this->varValue), $dc);
					}
				}
				else
				{
					$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						->execute(serialize($this->varValue), $this->currentRecord);
				}

				// Reload the page
				$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($this->strCommand, '/') . '=[^&]*/i', '', $this->Environment->request)));
			}
			// Unknow
			else
			{
				// What to do here?
			}
		}

		$arrUnique = [];
		$arrDatepicker = [];
		$arrTinyMCE = [];
        $arrColorpicker = array();
		$arrHeaderItems = [];

		foreach ($this->columnFields as $strKey => $arrField)
		{
			// Store unique fields
			if ($arrField['eval']['unique'])
			{
				$arrUnique[] = $strKey;
			}

			// Store date picker fields
			if ($arrField['eval']['datepicker'])
			{
				$arrDatepicker[] = $strKey;
			}

            // Store color picker fields
            if ($arrField['eval']['colorpicker'])
            {
                $arrColorpicker[] = $strKey;
            }

			// Store tiny mce fields
			if ($arrField['eval']['rte'] && strncmp($arrField['eval']['rte'], 'tiny', 4) === 0)
			{
				foreach ($this->varValue as $row => $value) {
					$tinyId = 'ctrl_' . $this->strField . '_row' . $row . '_' . $strKey;

					$GLOBALS['TL_RTE']['tinyMCE'][$tinyId] = [
						'id'   => $tinyId,
						'file' => 'tinyMCE',
						'type' => null
                    ];
				}

				$arrTinyMCE[] = $strKey;
			}

			if ($arrField['inputType'] == 'hidden')
			{
				continue;
			}
		}

		$intNumberOfRows = max(count($this->varValue), 1);

		// always show the minimum number of rows if set
		if ($this->minCount && ($intNumberOfRows < $this->minCount))
		{
			$intNumberOfRows = $this->minCount;
		}

		$arrHidden = [];
		$arrItems = [];
		$arrHiddenHeader = [];

		// Add input fields
		for ($i = 0; $i < $intNumberOfRows; $i++)
		{
			$this->activeRow = $i;
			$strHidden       = '';

			// Walk every column
			foreach ($this->columnFields as $strKey => $arrField)
			{
				$strWidget     = '';
				$blnHiddenBody = false;

				if ($arrField['eval']['hideHead'] == true)
				{
					$arrHiddenHeader[$strKey] = true;
				}

				// load row specific data (useful for example for default values in different rows)
				if (isset($this->arrRowSpecificData[$i][$strKey]))
				{
					$arrField = array_merge($arrField, $this->arrRowSpecificData[$i][$strKey]);
				}

				// styles not needed in frontend, done per css
				unset($arrField['eval']['style']);

				$objWidget = $this->initializeWidget($arrField, $i, $strKey, $this->varValue[$i][$strKey]);

				// load errors if there are any
				if (!empty($this->arrWidgetErrors[$strKey][$i]))
				{
					foreach ($this->arrWidgetErrors[$strKey][$i] as $strErrorMsg)
					{
						$objWidget->addError($strErrorMsg);
					}
				}

				if ($objWidget === null)
				{
					continue;
				}
				elseif ($arrField['inputType'] == 'hidden')
				{
					$arrHidden[] = $objWidget;
					continue;
				}

				$arrItems[$i][$strKey] = [
					'field'    => $objWidget];
			}
		}

		if ($this->formTemplate != '')
		{
			$strOutput = $this->generateTemplateOutput($arrUnique, $arrDatepicker, $arrColorpicker, $arrHidden, $arrItems, $arrHiddenHeader);
		}
		else
		{
			$strOutput = $this->generateTable($arrUnique, $arrDatepicker, $arrColorpicker, $arrHidden, $arrItems, $arrHiddenHeader);
		}

		return $strOutput;
	}

    protected function generateTemplateOutput($arrUnique, $arrDatepicker, $arrColorpicker, $strHidden, $arrItems)
	{
		$objTemplate        = new \FrontendTemplate($this->formTemplate);
		$objTemplate->items = $arrItems;
		$objTemplate->hidden = $arrHidden;

		$arrButtons = [];
		foreach ($arrItems as $k => $arrValue)
		{
			$arrButtons[$k]       = $this->generateButtonString($k);
		}
		$objTemplate->buttons = $arrButtons;

		return $objTemplate->parse();
	}

	/**
	 * Generates a table formatted MCW
	 * @param array
	 * @param array
	 * @param string
	 * @param array
	 * @return string
	 */
    protected function generateTable($arrUnique, $arrDatepicker, $arrColorpicker, $strHidden, $arrItems, $arrHiddenHeader = array())
	{

		// generate header fields
		foreach ($this->columnFields as $strKey => $arrField)
		{

			if ($arrField['eval']['columnPos'])
			{
				$arrHeaderItems[$arrField['eval']['columnPos']] = '<td></td>';
			}
			else
			{
				$strHeaderItem = '<td>';

				$strHeaderItem .= (array_key_exists($strKey, $arrHiddenHeader)) ? '<div class="invisible">' : '';
				$strHeaderItem .= (is_array($arrField['label'])) ? $arrField['label'][0] : ($arrField['label'] != null ? $arrField['label'] : $strKey);
				$strHeaderItem .= ((is_array($arrField['label']) && $arrField['label'][1] != '') ? '<span title="' . $arrField['label'][1] . '"><sup>(?)</sup></span>' : '');
				$strHeaderItem .= (array_key_exists($strKey, $arrHiddenHeader)) ? '</div>' : '';

				$arrHeaderItems[] = $strHeaderItem . '</td>';
			}
		}


		$return = '
<table cellspacing="0" ' . (($this->style) ? ('style="' . $this->style . '"') : ('')) . 'rel="maxCount[' . ($this->maxCount ? $this->maxCount : '0') . '] minCount[' . ($this->minCount ? $this->minCount : '0') . '] unique[' . implode(',', $arrUnique) . '] datepicker[' . implode(',', $arrDatepicker) . ']" cellpadding="0" id="ctrl_' . $this->strId . '" class="tl_modulewizard multicolumnwizard" summary="MultiColumnWizard">';

		if ($this->columnTemplate == '')
		{
			$return .= '
  <thead>
    <tr>
      ' . implode("\n      ", $arrHeaderItems) . '
      <td></td>
    </tr>
  </thead>';
		}

		$return .='
  <tbody>';

		foreach ($arrItems as $k => $arrValue)
		{
			$return .= '<tr>';
			foreach ($arrValue as $itemKey => $itemValue)
			{
				if ($itemValue['hide'] == true)
				{
					$itemValue['tl_class'] .= ' invisible';
				}

				$return .= '<td' . ($itemValue['valign'] != '' ? ' valign="' . $itemValue['valign'] . '"' : '') . ($itemValue['tl_class'] != '' ? ' class="' . $itemValue['tl_class'] . '"' : '') . '>' . $itemValue['entry'] . '</td>';
			}

			// insert buttons at the very end
			$return .= '<td class="operations col_last"' . (($this->buttonPos != '') ? ' valign="' . $this->buttonPos . '" ' : '') . '>' . $strHidden;
			$return .= $this->generateButtonString($k);
			$return .= '</td>';
			$return .= '</tr>';
		}

		$return .= '</tbody></table>';


		return $return;
	}
}