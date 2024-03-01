<?php
/**
 * subclass of DAO/Menu_to_menu_item
 */
require_once 'DAO/Menu_to_menu_item.php';

class CMenuToMenuItem extends DAO_Menu_to_menu_item
{
	public $store_price;

	public $DAO_menu_item;
	public $DAO_store;
	public $DAO_menu;

	function __construct()
	{
		parent::__construct();
	}

	function fetch()
	{
		$res = parent::fetch();

		if ($res)
		{
			$this->getStorePrice();
		}

		return $res;
	}

	function getStorePrice()
	{
		$this->getDAO_menu_item();
		$this->getDAO_store();

		if (isset($this->override_price))
		{
			$this->store_price = $this->override_price;
		}
		else if (!empty($this->DAO_store) && !empty($this->menu_id))
		{
			$this->getDAO_menu();

			$markup = $this->DAO_store->getMarkupMultiObj($this->DAO_menu->id);
			$this->store_price = COrders::getItemMarkupMultiSubtotal($markup, $this->DAO_menu_item);
		}

		return CTemplate::number_format($this->store_price);
	}

	function getDAO_menu_item()
	{
		if (empty($this->DAO_menu_item) && !empty($this->menu_item_id))
		{
			$this->DAO_menu_item = DAO_CFactory::create('menu_item');
			$this->DAO_menu_item->id = $this->menu_item_id;
			$this->DAO_menu_item->find(true);
		}

		return $this->DAO_menu_item;
	}

	function getDAO_store()
	{
		if (empty($this->DAO_store) && !empty($this->store_id))
		{
			$this->DAO_store = DAO_CFactory::create('store');
			$this->DAO_store->id = $this->store_id;
			$this->DAO_store->find(true);
		}

		return $this->DAO_store;
	}

	function getDAO_menu()
	{
		if (empty($this->DAO_menu) && !empty($this->menu_id))
		{
			$this->DAO_menu = DAO_CFactory::create('menu');
			$this->DAO_menu->id = $this->menu_id;
			$this->DAO_menu->find(true);
		}

		return $this->DAO_menu;
	}


	function isHiddenEverywhere()
	{
		if (!empty($this->is_hidden_everywhere))
		{
			return true;
		}

		return false;
	}

	function isShowOnOrderForm()
	{
		if (!empty($this->show_on_order_form))
		{
			return true;
		}

		return false;
	}

	function isShowOnPickSheet()
	{
		if (!empty($this->show_on_pick_sheet))
		{
			return true;
		}

		return false;
	}

	function isVisible()
	{
		if (!empty($this->is_visible))
		{
			return true;
		}

		return false;
	}

}
?>