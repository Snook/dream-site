<?php
require_once 'DAO/Bundle.php';
require_once 'DAO/BusinessObject/COrders.php';

class CBundle extends DAO_Bundle
{
	const MASTER_ITEM = 'MASTER_ITEM';
	const TV_OFFER = 'TV_OFFER';
	const DREAM_TASTE = 'DREAM_TASTE';
	const FUNDRAISER = 'FUNDRAISER';
	const DELIVERED = 'DELIVERED';

	public $items = null;
	static public $bundle_to_menu_item_group = array();

	/*
	 * $menu_item_array
	 *
	 * set to true to fetch an array of the bundle's menu_item objects
	 *
		$bundle = DAO_CFactory::create('bundle');
		$bundle->menu_item_array = true;
		$bundle->find(true);

		// Result
		$bundle->menu_item_array = array(menu_item_obj_1, menu_item_obj_2, ...)
	 *
	 */
	public $menu_item_array = null;

	public $store_id = null;
	public $info;

	function __construct()
	{
		parent::__construct();
	}

	function find($n = false)
	{
		$find = parent::find($n);

		if (!empty($find))
		{
			if (!empty($this->menu_item_array))
			{
				$this->menu_item_array = array();

				$DAO_bundle_to_menu_item = DAO_CFactory::create('bundle_to_menu_item');
				$DAO_bundle_to_menu_item->bundle_id = $this->id;
				$DAO_bundle_to_menu_item->store_id = ((empty($this->store_id)) ? 'NULL' : $this->store_id);
				$DAO_bundle_to_menu_item->autoJoin();
				$DAO_bundle_to_menu_item->orderBy('bundle_to_menu_item.ordering');

				$DAO_bundle_to_menu_item->find();

				while ($DAO_bundle_to_menu_item->fetch())
				{
					$itemInfo = $DAO_bundle_to_menu_item->cloneObj();

					$this->menu_item_array['menu_item'][$itemInfo->menu_item_id] = $itemInfo;
					$this->menu_item_array['all_recipes'][$itemInfo->menu_item_id_recipe_id] = $itemInfo->menu_item_id;
				}
			}
		}

		return $find;
	}

	function delete($useWhere = false, $forceDelete = false)
	{
		if ($this->id)
		{
			// check if bundles have orders against them
			$DAO_orders = DAO_CFactory::create('orders');
			$DAO_orders->bundle_id = $this->id;
			$DAO_booking = DAO_CFactory::create('booking');
			$DAO_booking->status = CBooking::ACTIVE;
			$DAO_orders->joinAddWhereAsOn($DAO_booking);
			$DAO_orders->groupBy("orders.bundle_id");

			if ($DAO_orders->find())
			{
				throw new Exception('Bundle has been ordered.');
			}
			else
			{
				$DAO_bundle_to_menu_item = DAO_CFactory::create('bundle_to_menu_item');
				$DAO_bundle_to_menu_item->bundle_id = $this->id;
				$DAO_bundle_to_menu_item->find();

				while ($DAO_bundle_to_menu_item->fetch())
				{
					$DAO_bundle_to_menu_item->delete();
				}
			}
		}

		return parent::delete($useWhere, $forceDelete);
	}

	function setupDefaultItems($setAsChosen = true)
	{
		if (isset($this->items) && count($this->items))
		{
			unset($this->items);
		}

		$menu_items = DAO_CFactory::create('menu_item');
		$menu_items->query("select 
			mi.id, 
			mi.menu_item_name 
			from bundle_to_menu_item bmi join menu_item mi on mi.id = bmi.menu_item_id 
			where bmi.bundle_id = {$this->id} and bmi.is_deleted = 0 
			order by bmi.ordering ");

		while ($menu_items->fetch())
		{
			$this->items[$menu_items->id] = array(
				'name' => $menu_items->menu_item_name,
				'chosen' => $setAsChosen
			);
		}
	}


	// called by CSessionToolsPrinting loadBundleMenuData for TV_OFFER and TASTE
	// note: store is present in most cases
	static function getBundleInfoByMenuAndType($menu_id, $bundle_type)
	{
		$bundle = DAO_CFactory::create('bundle');
		$bundle->query("SELECT *
			FROM bundle AS b
			WHERE b.bundle_type = '" . $bundle_type . "' AND b.menu_id = '" . $menu_id . "' AND b.is_deleted = '0'");
		$bundle->fetch();

		return $bundle;
	}

	/**
	 * @throws exception
	 * returns new bundle object
	 */
	function createBundleFromBundle()
	{
		$newBundle = clone $this;

		$newBundle->insert();

		// copy all the selected menu items to the new bundle
		$bundleToMenuItem = DAO_CFactory::create('bundle_to_menu_item');
		$bundleToMenuItem->bundle_id = $this->id;
		$bundleToMenuItem->find();

		while ($bundleToMenuItem->fetch())
		{
			// only copy menu items that are in the current offering
			if (!empty($bundleToMenuItem->current_offering))
			{
				$bundleToMenuItem->bundle_id = $newBundle->id;
				$bundleToMenuItem->insert();
			}
		}

		return $newBundle;
	}

	//called by session_menu, item, create_session, order_mgr
	static function getBundleInfo($bundle_id, $menu_id, $store_id_or_obj)
	{
		$bundleInfo = DAO_CFactory::create('bundle');
		$bundleInfo->id = $bundle_id;
		$bundleInfo->find(true);

		// Bundle Price Intercept Point

		$isCorporateStore = false;

		if (!is_object($store_id_or_obj))
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select is_corporate_owned from store where id = $store_id_or_obj");
			$storeObj->fetch();

			if ($storeObj->is_corporate_owned)
			{
				$isCorporateStore = true;
			}
		}
		else
		{
			$isCorporateStore = $store_id_or_obj->is_corporate_owned;
		}

		if ($isCorporateStore && $menu_id > 176 && $menu_id <= 184)
		{
			if ($bundleInfo->bundle_type == CBundle::DREAM_TASTE)
			{
				$bundleInfo->price = 34.99;
			}
			else if ($bundleInfo->bundle_type == CBundle::TV_OFFER)
			{
				$bundleInfo->price = 84.95;
			}
		}

		return $bundleInfo;
	}

	function isMasterItem()
	{
		if ($this->bundle_type == CBundle::MASTER_ITEM)
		{
			return true;
		}

		return false;
	}

	function isStarterPack()
	{
		return $this->isTVOffer();
	}

	function isDreamTaste()
	{
		if ($this->bundle_type == CBundle::DREAM_TASTE)
		{
			return true;
		}

		return false;
	}

	function isFundraiser()
	{
		if ($this->bundle_type == CBundle::FUNDRAISER)
		{
			return true;
		}

		return false;
	}

	function isTVOffer()
	{
		if ($this->bundle_type == CBundle::TV_OFFER)
		{
			return true;
		}

		return false;
	}

	//called by session (customer side) but only for TV_OFFER
	static function getBundleIDForMenuAndType($menu_id, $type)
	{

		$theBundle = DAO_CFactory::create('bundle');

		$theBundle->query("select id from bundle where bundle_type = '$type' and  menu_id = $menu_id and is_deleted = 0");

		if ($theBundle->fetch())
		{
			return $theBundle->id;
		}

		return false;
	}

	static function getBundleByID($bundle_id)
	{
		$DAO_bundle = DAO_CFactory::create('bundle');
		$DAO_bundle->id = $bundle_id;

		if ($DAO_bundle->find(true))
		{
			return $DAO_bundle;
		}

		return false;
	}

	function getDeliveredBundleMenuItems($factorCartIntoInventory, $defaultStoreId = null)
	{
		$init_DAO_store = null;
		$CartObj = CCart2::instance();

		if ($factorCartIntoInventory)
		{
			$factorCartIntoInventory = $CartObj->getMenuItems();

			$init_DAO_store = $CartObj->getOrder()->getStore();
		}

		$DAO_store = DAO_CFactory::create('store', true);

		if ((empty($init_DAO_store) || !$init_DAO_store->isDistributionCenter()) && !empty($defaultStoreId))
		{
			$init_DAO_store->id = $defaultStoreId;
			$init_DAO_store->find_DAO_store(true);
		}

		if (!empty($init_DAO_store) && $init_DAO_store->isDistributionCenter())
		{
			$DAO_store->id = $init_DAO_store->parent_store_id;
			$DAO_store->find_DAO_store(true);
		}

		$menuItemArray = CMenuItem::getFullMenuItemsAndMenuInfo($DAO_store, $this->menu_id, $factorCartIntoInventory, $this->id, false, 'INNER');

		$this->info = array(
			'total_remaining_servings' => 0,
			'total_in_stock_menu_items' => 0,
			'out_of_stock' => false
		);

		$this->menu_item = array(
			'items' => $menuItemArray['menuItemInfo']
		);

		foreach ($menuItemArray['menuItemInfo'] as $entree)
		{
			foreach ($entree as $DAO_menu_item)
			{
				$this->info['total_remaining_servings'] += $DAO_menu_item->getRemainingServings();

				if (!$DAO_menu_item->isOutOfStock())
				{
					$this->info['total_in_stock_menu_items'] += 1;
				}

				$this->info['all_recipes'][$DAO_menu_item->recipe_id] = array(
					'recipe_id' => $DAO_menu_item->recipe_id,
					'menu_id' => $menuItemArray['menuInfo']['menu_id'],
					'menu_item_id' => $DAO_menu_item->id,
					'menu_item_name' => $DAO_menu_item->menu_item_name,
					'menu_item_description' => $DAO_menu_item->menu_item_description
				);
			}
		}

		if ((!empty($this->number_items_required) && $this->info['total_in_stock_menu_items'] < $this->number_items_required) || (!empty($this->number_servings_required) && $this->info['total_remaining_servings'] < $this->number_servings_required))
		{
			$this->info['out_of_stock'] = true;
		}

		$this->info['menuInfo'] = $menuItemArray['menuInfo'];
		$this->info['menuItemInfo'] = $menuItemArray['menuItemInfo'];
		$this->info['menuItemInfoByMID'] = $menuItemArray["menuItemInfoByMID"];

		return $menuItemArray;
	}

	// called by inventory manager to mark items as part of the bundle
	static function getBundleItemsArrayByMenuAndBundleType($menu_id, $type)
	{
		$retVal = array();
		$items = new DAO();
		$items->query("select bmi.menu_item_id, mi.pricing_type, mi.recipe_id,mi.entree_id from bundle b
                                    join bundle_to_menu_item bmi on bmi.bundle_id = b.id and bmi.is_deleted = 0
                                    join menu_item mi on mi.id = bmi.menu_item_id
                                    where b.bundle_type = '$type' and  b.menu_id = $menu_id and b.is_deleted = 0");

		while ($items->fetch())
		{
			$retVal[$items->menu_item_id] = array(
				'id' => $items->menu_item_id,
				'pricing_type' => $items->pricing_type,
				'recipe_id' => $items->recipe_id,
				'entree_id' => $items->entree_id
			);
		}

		return $retVal;
	}

	// Heavy Lifter (TV Offer only)
	static function getActiveBundleForMenu($menu_id, $store_id_or_obj = false)
	{

		// Bundle Price Intercept Point

		$isCorporateStore = false;

		if ($store_id_or_obj)
		{
			if (!is_object($store_id_or_obj))
			{
				$storeObj = DAO_CFactory::create('store');
				$storeObj->id = $store_id_or_obj;
				$storeObj->find(true);

				if ($storeObj->is_corporate_owned)
				{
					$isCorporateStore = true;
				}
			}
			else
			{
				$isCorporateStore = $store_id_or_obj->is_corporate_owned;
			}
		}
		else
		{
			CLog::RecordIntense("No store provided to getActiveBundleForMenu", 'ryan.snook@dreamdinners.com');
		}

		$theBundle = DAO_CFactory::create('bundle');
		$theBundle->menu_id = $menu_id;
		$theBundle->bundle_type = CBundle::TV_OFFER;
		$theBundle->find();

		if ($theBundle->N > 0)
		{
			$theBundle->fetch();

			if ($isCorporateStore && $menu_id > 176 && $menu_id <= 184)
			{
				if ($theBundle->bundle_type == CBundle::DREAM_TASTE)
				{
					$theBundle->price = 34.99;
				}
				else if ($theBundle->bundle_type == CBundle::TV_OFFER)
				{
					$theBundle->price = 84.95;
				}
			}

			return $theBundle;
		}

		return false;
	}

	function getBundleItems()
	{
		$bundle_to_menu_item = DAO_CFactory::create('bundle_to_menu_item');
		$bundle_to_menu_item->bundle_id = $this->id;
		$bundle_to_menu_item->current_offering = 1;

		$menu_item = DAO_CFactory::create('menu_item');
		$menu_item->joinAdd($bundle_to_menu_item, array('useWhereAsOn' => true));
		$menu_item->orderBy("bundle_to_menu_item.ordering");
		$menu_item->find();

		$menuItemInfo = array();

		while ($menu_item->fetch())
		{
			$this->menu_item_array[$menu_item->menu_item_id] = clone $menu_item;

			$i = $menu_item->menu_item_id;
			$menuItemInfo['bundle'][$i] = array();
			$menuItemInfo['bundle'][$i]['id'] = $i;
			$menuItemInfo['bundle'][$i]['display_title'] = $menu_item->menu_item_name;
			$menuItemInfo['bundle'][$i]['display_description'] = stripslashes($menu_item->menu_item_description);
			$menuItemInfo['bundle'][$i]['menu_item_name'] = $menu_item->menu_item_name;
			$menuItemInfo['bundle'][$i]['menu_item_description'] = $menu_item->menu_item_description;
			$menuItemInfo['bundle'][$i]['price'] = $menu_item->price;
			$menuItemInfo['bundle'][$i]['pricing_type'] = $menu_item->pricing_type;
			$menuItemInfo['bundle'][$i]['current_offering'] = $menu_item->current_offering;
			$menuItemInfo['bundle'][$i]['servings_per_item'] = $menu_item->servings_per_item;
			$menuItemInfo['bundle'][$i]['servings_per_container_display'] = $menu_item->servings_per_container_display;
			$menuItemInfo['bundle'][$i]['entree_id'] = $menu_item->entree_id;
			$menuItemInfo['bundle'][$i]['recipe_id'] = isset($menu_item->recipe_id) ? $menu_item->recipe_id : 0;
			$menuItemInfo['bundle'][$i]['menu_item_category_id'] = $menu_item->menu_item_category_id;

			$menuItemInfo['bundle'][$i]['is_visible'] = true;
		}

		return $menuItemInfo;
	}

	static function getDeliveredBundleByID($bundle_id, $current_offering = false)
	{
		$daoMenuItem = DAO_CFactory::create('menu_item');

		$select = "SELECT
			menu_item_category.category_type AS 'category',
			menu_item.*, 
			bundle_to_menu_item.current_offering
		FROM bundle_to_menu_item ";

		$joins = "INNER JOIN  menu_item ON menu_item.id = bundle_to_menu_item.menu_item_id
				LEFT JOIN menu_item_category on menu_item.menu_item_category_id = menu_item_category.id ";

		$where = " where bundle_to_menu_item.bundle_id = $bundle_id AND bundle_to_menu_item.is_deleted = 0 AND menu_item.is_deleted = 0 ";

		if ($current_offering)
		{
			$where .= " AND bundle_to_menu_item.current_offering = '1' ";
		}

		$order_by = " order by bundle_to_menu_item.ordering ";

		$daoMenuItem->query($select . $joins . $where . $order_by);

		$menuItemInfo = array();

		while ($daoMenuItem->fetch())
		{
			$i = $daoMenuItem->id;
			$menuItemInfo['bundle'][$i] = array();
			$menuItemInfo['bundle'][$i]['id'] = $i;
			$menuItemInfo['bundle'][$i]['display_title'] = $daoMenuItem->menu_item_name;
			$menuItemInfo['bundle'][$i]['display_description'] = stripslashes($daoMenuItem->menu_item_description);
			$menuItemInfo['bundle'][$i]['menu_item_name'] = $daoMenuItem->menu_item_name;
			$menuItemInfo['bundle'][$i]['menu_item_description'] = $daoMenuItem->menu_item_description;
			$menuItemInfo['bundle'][$i]['price'] = $daoMenuItem->price;
			$menuItemInfo['bundle'][$i]['pricing_type'] = $daoMenuItem->pricing_type;

			if ($current_offering)
			{
				$menuItemInfo['bundle'][$i]['current_offering'] = $daoMenuItem->current_offering;
			}
			else
			{
				$menuItemInfo['bundle'][$i]['current_offering'] = 0;
			}

			$menuItemInfo['bundle'][$i]['servings_per_item'] = $daoMenuItem->servings_per_item;
			$menuItemInfo['bundle'][$i]['servings_per_container_display'] = $daoMenuItem->servings_per_container_display;
			$menuItemInfo['bundle'][$i]['entree_id'] = $daoMenuItem->entree_id;
			$menuItemInfo['bundle'][$i]['recipe_id'] = isset($daoMenuItem->recipe_id) ? $daoMenuItem->recipe_id : 0;
			$menuItemInfo['bundle'][$i]['menu_item_category_id'] = $daoMenuItem->menu_item_category_id;

			$menuItemInfo['bundle'][$i]['is_visible'] = true;
		}

		return $menuItemInfo;
	}

	// Heavy lifter but no price info returned
	// called from Order Manager
	static function getBundleMenuInfo($bundle_id, $menu_id, $store_id)
	{
		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $menu_id;

		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $store_id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
			'join_bundle_to_menu_item_bundle_id' => $bundle_id
		));

		$menuItemInfo = array();

		while ($DAO_menu_item->fetch())
		{
			$i = $DAO_menu_item->id;
			$menuItemInfo['bundle'][$i] = array();
			$menuItemInfo['bundle'][$i]['id'] = $i;
			$menuItemInfo['bundle'][$i]['display_title'] = $DAO_menu_item->menu_item_name;
			$menuItemInfo['bundle'][$i]['display_description'] = stripslashes($DAO_menu_item->menu_item_description);
			$menuItemInfo['bundle'][$i]['menu_item_name'] = $DAO_menu_item->menu_item_name;
			$menuItemInfo['bundle'][$i]['menu_item_description'] = $DAO_menu_item->menu_item_description;
			$menuItemInfo['bundle'][$i]['pricing_type'] = $DAO_menu_item->pricing_type;
			$menuItemInfo['bundle'][$i]['servings_per_item'] = $DAO_menu_item->servings_per_item;
			$menuItemInfo['bundle'][$i]['entree_id'] = $DAO_menu_item->entree_id;
			$menuItemInfo['bundle'][$i]['initial_inventory'] = isset($DAO_menu_item->initial_inventory) ? $DAO_menu_item->initial_inventory : 9999;
			$menuItemInfo['bundle'][$i]['override_inventory'] = isset($DAO_menu_item->override_inventory) ? $DAO_menu_item->override_inventory : 9999;
			$menuItemInfo['bundle'][$i]['number_sold'] = isset($DAO_menu_item->number_sold) ? $DAO_menu_item->number_sold : 0;
			$menuItemInfo['bundle'][$i]['recipe_id'] = isset($DAO_menu_item->recipe_id) ? $DAO_menu_item->recipe_id : 0;
			$menuItemInfo['bundle'][$i]['menu_item_category_id'] = $DAO_menu_item->menu_item_category_id;

			$menuItemInfo['bundle'][$i]['is_visible'] = true;
		}

		$Menu = DAO_CFactory::create('menu');
		$Menu->id = $menu_id;
		$Menu->find(true);
		$menuItemInfo['menu_name'] = $Menu->menu_name . " Menu";
		$menuItemInfo['menu_name_short'] = $Menu->menu_name;

		return $menuItemInfo;
	}

	static function getBundleGroup($group_id)
	{
		if (empty($group_id))
		{
			return false;
		}

		if (!empty(self::$bundle_to_menu_item_group[$group_id]))
		{
			return self::$bundle_to_menu_item_group[$group_id];
		}
		else
		{
			$bundleGroup = DAO_CFactory::create('bundle_to_menu_item_group');
			$bundleGroup->id = $group_id;

			if ($bundleGroup->find(true))
			{
				self::$bundle_to_menu_item_group[$group_id] = clone $bundleGroup;
				self::$bundle_to_menu_item_group[$group_id]->sub_items = array();

				return self::$bundle_to_menu_item_group[$group_id];
			}
			else
			{
				return false;
			}
		}
	}

	// for Master Item style bundle
	static function getBundleMenuInfoForMenuItem($menu_item_id, $menu_id, $store_id)
	{
		// INVENTORY TOUCH POINT 11

		$daoMenuItem = DAO_CFactory::create('menu_item');
		$daoMenuItem->query("SELECT
			btmig.group_title,
			btmig.group_description,
			btmig.number_items_required AS group_number_items_required,
			menu_item_category.category_type AS 'category', 
			menu_item.*,
			minv.override_inventory,
			minv.number_sold,
			btmi.bundle_to_menu_item_group_id, 
			btmi.fixed_quantity, 
			b.number_items_required,
			b.number_servings_required 
			FROM bundle AS b
			INNER JOIN bundle_to_menu_item AS btmi ON btmi.bundle_id = b.id
			INNER JOIN menu_item ON menu_item.id = btmi.menu_item_id
			INNER JOIN menu_to_menu_item AS mtmi ON mtmi.menu_item_id = menu_item.id AND mtmi.store_id = $store_id
			LEFT JOIN menu_item_category ON menu_item.menu_item_category_id = menu_item_category.id
			LEFT JOIN menu_item_inventory minv ON minv.store_id = $store_id AND minv.menu_id = $menu_id AND minv.recipe_id = menu_item.recipe_id AND minv.is_deleted = 0
			LEFT JOIN bundle_to_menu_item_group AS btmig ON btmig.id = btmi.bundle_to_menu_item_group_id
			WHERE b.master_menu_item = $menu_item_id AND btmi.is_deleted = 0 AND menu_item.is_deleted = 0 
			ORDER BY btmi.bundle_to_menu_item_group_id, btmi.ordering, menu_item.subcategory_label, mtmi.menu_order_value");

		$menuItemInfo = array();
		$number_required = 0;

		while ($daoMenuItem->fetch())
		{
			$i = $daoMenuItem->id;
			$menuItemInfo['bundle'][$i] = $daoMenuItem->toArray('%s', false, true);

			$menuItemInfo['bundle'][$i]['id'] = $i;
			$menuItemInfo['bundle'][$i]['display_title'] = $daoMenuItem->menu_item_name;
			$menuItemInfo['bundle'][$i]['menu_item_name'] = $daoMenuItem->menu_item_name;
			$menuItemInfo['bundle'][$i]['display_description'] = stripslashes($daoMenuItem->menu_item_description);
			$menuItemInfo['bundle'][$i]['menu_item_description'] = $daoMenuItem->menu_item_description;
			$menuItemInfo['bundle'][$i]['recipe_id'] = $daoMenuItem->recipe_id;
			$menuItemInfo['bundle'][$i]['parent_item'] = $menu_item_id;
			$menuItemInfo['bundle'][$i]['number_items_required'] = 0;
			$menuItemInfo['bundle'][$i]['is_bundle'] = 0;
			$menuItemInfo['bundle'][$i]['price'] = 0;
			$menuItemInfo['bundle'][$i]['category_id'] = $daoMenuItem->menu_item_category_id;
			$menuItemInfo['bundle'][$i]['initial_inventory'] = isset($daoMenuItem->initial_inventory) ? $daoMenuItem->initial_inventory : 9999;
			$menuItemInfo['bundle'][$i]['override_inventory'] = isset($daoMenuItem->override_inventory) ? $daoMenuItem->override_inventory : 9999;
			$menuItemInfo['bundle'][$i]['number_sold'] = isset($daoMenuItem->number_sold) ? $daoMenuItem->number_sold : 0;
			$menuItemInfo['bundle'][$i]['bundle_to_menu_item_group'] = self::getBundleGroup($daoMenuItem->bundle_to_menu_item_group_id);

			if (!empty($menuItemInfo['bundle'][$i]['bundle_to_menu_item_group']))
			{
				$menuItemInfo['bundle'][$i]['bundle_to_menu_item_group']->sub_items[$i] = clone $daoMenuItem;
			}

			// Special Inventory handling for Sides & Sweets
			$menuItemInfo['bundle'][$i]['remaining_servings'] = $menuItemInfo['bundle'][$i]['override_inventory'] - $menuItemInfo['bundle'][$i]['number_sold'];

			if ($daoMenuItem->menu_item_category_id == 9)
			{
				if ($menuItemInfo['bundle'][$i]['remaining_servings'] < 1)
				{
					$menuItemInfo['bundle'][$i]['this_type_out_of_stock'] = true;
					$menuItemInfo['bundle'][$i]['out_of_stock'] = true;
				}
				else
				{
					$menuItemInfo['bundle'][$i]['this_type_out_of_stock'] = false;
					$menuItemInfo['bundle'][$i]['out_of_stock'] = false;
				}

				if ($menuItemInfo['bundle'][$i]['remaining_servings'] < 5)
				{
					$menuItemInfo['bundle'][$i]['limited_qtys'] = true;
				}
				else
				{
					$menuItemInfo['bundle'][$i]['limited_qtys'] = false;
				}
			}
			else
			{
				if ($menuItemInfo['bundle'][$i]['remaining_servings'] < $menuItemInfo['bundle'][$i]['servings_per_item'])
				{
					$menuItemInfo['bundle'][$i]['this_type_out_of_stock'] = true;
				}
				else
				{
					$menuItemInfo['bundle'][$i]['this_type_out_of_stock'] = false;
				}

				if ($menuItemInfo['bundle'][$i]['remaining_servings'] < $daoMenuItem->servings_per_item)
				{
					$menuItemInfo['bundle'][$i]['out_of_stock'] = true;
				}
				else
				{
					$menuItemInfo['bundle'][$i]['out_of_stock'] = false;
				}

				if ($daoMenuItem->menu_item_category_id == 9)
				{
					$stocklimit = 5;
				}
				else
				{
					$stocklimit = 18;
				}

				if ($menuItemInfo['bundle'][$i]['remaining_servings'] < $stocklimit)
				{
					$menuItemInfo['bundle'][$i]['limited_qtys'] = true;
				}
				else
				{
					$menuItemInfo['bundle'][$i]['limited_qtys'] = false;
				}
			}

			$menuItemInfo['bundle'][$i]['recipe_id'] = isset($daoMenuItem->recipe_id) ? $daoMenuItem->recipe_id : 0;
			$menuItemInfo['bundle'][$i]['is_visible'] = true;
			$number_required = $daoMenuItem->number_items_required;
		}

		$menuItemInfo['number_items_required'] = $number_required;
		$menuItemInfo['bundle_groups'] = self::$bundle_to_menu_item_group;

		return $menuItemInfo;
	}

	/**
	 * @param        $recipe_id       //  required, but will only be used if the menu item name is not passed or
	 *                                //  if the menu item name does not contain 'shiftsetgo'
	 * @param string $menu_item_name  //  optional menu item name, function will return tru if it contains 'shiftsetgo' case-insensitive
	 *
	 * @return bool
	 */
	public static function isShiftSetGoBundle($recipe_id, $menu_item_name = false)
	{
		if (!empty($menu_item_name))
		{
			if (preg_match('/shiftsetgo|live well bundle/i', $menu_item_name))
			{
				return true;
			}
			else
			{
				//matches so return
				return false;
			}
		}

		return in_array($recipe_id, self::shiftSetGoBundleRecipeIds(true));
	}

	/**
	 * @param        $asArray  // if true will return an array, otherwise will return comma seperated string
	 *
	 * @return mixed array or string depending on requested, string is nothing is passed
	 */
	public static function shiftSetGoBundleRecipeIds($asArray = false)
	{

		$recipe_ids = array(
			1281,
			1282,
			1286,
			1288,
			1290,
			1298
		);
		if ($asArray)
		{
			return $recipe_ids;
		}

		return implode(',', $recipe_ids);
	}
}

?>