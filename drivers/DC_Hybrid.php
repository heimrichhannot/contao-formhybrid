<?php
namespace HeimrichHannot\FormHybrid;

class DC_Hybrid extends \DataContainer
{
	public function __construct($strTable, $objItem, $objModule=null)
	{
		$this->objActiveRecord = $objItem;
		$this->intId = $objItem->id;
		$this->strTable = $strTable;
		$this->objModule = $objModule;
	}

	/**
	 * Return the name of the current palette
	 *
	 * @return string
	 */
	public function getPalette()
	{
		// TODO: Implement getPalette() method.
	}

	/**
	 * Save the current value
	 *
	 * @param mixed $varValue
	 *
	 * @throws \Exception
	 */
	protected function save($varValue)
	{
		// TODO: Implement save() method.
	}


}
