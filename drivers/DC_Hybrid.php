<?php
namespace HeimrichHannot\FormHybrid;

class DC_Hybrid extends \DataContainer
{
	public function __construct($strTable, $objItem)
	{
		$this->objActiveRecord = $objItem;
		$this->intId = $objItem->id;
		$this->strTable = $strTable;
	}
}
