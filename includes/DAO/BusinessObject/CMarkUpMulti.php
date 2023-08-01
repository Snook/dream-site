<?php

require_once 'DAO/Mark_up_multi.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CMarkUpMulti
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *
 *  	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class CMarkUpMulti extends DAO_Mark_up_multi
{

	const FLAT = 'FLAT';
	const PERCENTAGE = 'PERCENT';

	function __construct()
	{
		parent::__construct();
	}

	function setZeroDollarAssembly()
	{
		$this->assembly_fee = '0.00';
		$this->delivery_assembly_fee = '0.00';
	}

	/**
	 * This function returns active rows for the store.
	 *
	 * @param menu_id, the menu of the current order. If false, it will only return a mark up that has no
	 * menu_id_start value
	 **/
	function findActive($menu_id = false)
	{
		if (!$this->store_id)
		{
			throw new Exception('need to set store id');
		}

		if ($menu_id)
		{
			if ($menu_id >= 79) // new method for March 2008 and later
			{
				// first check for direct association
				$this->whereAdd("menu_id_start = $menu_id");

				$this->orderBy("id desc");
				$retVal = $this->find();
				if ($retVal)
				{
					return $retVal; // found direct ass
				}
				else // look for default
				{
					$this->whereAdd();
					$this->whereAdd("is_default = 1");

					return $this->find();
				}
			}
			else
			{
				$this->whereAdd("(menu_id_start <= $menu_id or menu_id_start IS NULL)");
				$this->orderBy(' id DESC ');
			}
		}
		else
		{
			$this->whereAdd("(menu_id_start IS NULL)");
			$this->orderBy(' id DESC ');
		}

		return $this->find();
	}
}
?>