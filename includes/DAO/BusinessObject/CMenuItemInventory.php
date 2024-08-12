<?php
/**
 * subclass of DAO/Menu_item_inventory
 */
require_once 'DAO/Menu_item_inventory.php';

class CMenuItemInventory extends DAO_Menu_item_inventory
{
	public $remaining_servings;

	function __construct()
	{
		parent::__construct();
	}

	function fetch()
	{
		$res = parent::fetch();

		if ($res)
		{
			$this->calculateRemaining();
		}

		return $res;
	}

	function calculateRemaining()
	{
		$this->remaining_servings = $this->getRemainingServings();
	}

	function isOutOfStock($servings): bool
	{
		return ($this->getRemainingServings() < (int)$servings);
	}

	function getRemainingServings(): int
	{
		return (int)$this->override_inventory - (int)$this->number_sold;
	}

	function getMenuItemInventory($menuItemIdArray)
	{
		$DAO_menu_item = DAO_CFactory::create('menu_item');
		$DAO_menu_item->whereAdd("menu_item.id in (" . implode(",", $menuItemIdArray) . ")");

		$this->joinAddWhereAsOn($DAO_menu_item);

		$this->groupBy("menu_item_inventory.recipe_id");

		return $this;
	}

}
?>