<?php
require_once 'DAO/Box.php';
require_once 'DAO/BusinessObject/CBundle.php';

class CBox extends DAO_Box
{
	const DELIVERED_FIXED = 'DELIVERED_FIXED';
	const DELIVERED_CUSTOM = 'DELIVERED_CUSTOM';

	public $_get = array(
		'box_bundle_1_obj' => false,
		'box_bundle_2_obj' => false,
		'store_obj' => false,
		'menu_obj' => false,
		'orders' => false,
		'orders_n' => false,
		'number_sold_n' => false
	);

	public $menu_item_array = null; // set to true to fetch bundle menu items

	/*
	 * Define array to be used in fetch();
	 */
	protected $_fetchResult = array(
		"total_remaining_servings" => 0,
		"total_in_stock_menu_items" => 0,
		"all_recipes" => array(),
		"boxArray" => array(
			'all' => array(),
			'current' => array(),
			'past' => array()
		)
	);

	public $bundle = array();

	public $info = array(
		"total_remaining_servings" => 0,
		"total_in_stock_menu_items" => 0,
		"all_recipes" => array()
	);

	public $box_bundle_1_obj;
	public $box_bundle_2_obj;
	public $store_obj;
	public $menu_obj;
	public $orders;
	public $orders_n;
	public $number_sold_n;

	function __construct()
	{

	}

	function find($n = false)
	{
		return parent::find($n);
	}

	function find_DAO_box($n = false)
	{
		if ($this->_query["data_select"] === "*")
		{
			throw new Exception("When creating this object, second parameter in DAO_CFactory::create() needs to be 'true'");
		}

		$this->joinAddWhereAsOn(DAO_CFactory::create('store', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('menu', true));

		// join bundle master_item to menu_item
		$DAO_bundle_1 = DAO_CFactory::create('bundle');
		$DAO_bundle_1->whereAdd("box_bundle_1.id=box.box_bundle_1");
		$this->joinAddWhereAsOn($DAO_bundle_1, array(
			'joinType' => 'LEFT',
			'useLinks' => false
		), 'box_bundle_1');

		$DAO_bundle_2 = DAO_CFactory::create('bundle');
		$DAO_bundle_2->whereAdd("box_bundle_2.id=box.box_bundle_2");
		$this->joinAddWhereAsOn($DAO_bundle_2, array(
			'joinType' => 'LEFT',
			'useLinks' => false
		), 'box_bundle_2');

		return parent::find($n);
	}

	/**
	 * @throws Exception
	 */
	function fetch()
	{
		$this->box_bundle_1_obj = null;
		$this->box_bundle_2_obj = null;
		$this->store_obj = null;
		$this->menu_obj = null;
		$this->orders = null;
		$this->orders_n = null;
		$this->number_sold_n = null;

		$parent_fetch = parent::fetch();

		if ($parent_fetch)
		{
			/* populate $_fetchResult */
			$boxObj = clone $this;

			if ($this->_get['box_bundle_1_obj'])
			{
				$this->box_bundle_1_obj = $boxObj->getBundle1Obj();
			}

			if ($this->_get['box_bundle_2_obj'])
			{
				$this->box_bundle_2_obj = $boxObj->getBundle2Obj();
			}

			if ($this->_get['store_obj'])
			{
				$this->store_obj = $boxObj->getStoreObj();
			}

			if ($this->_get['menu_obj'])
			{
				$this->menu_obj = $boxObj->getMenuObj();
			}

			if ($this->_get['orders'] || $this->_get['number_sold_n'])
			{
				list ($this->number_sold_n, $this->orders_n, $this->orders) = $boxObj->numberSold($this->_get['orders']);
			}

			$this->_fetchResult['boxArray']['all'][$boxObj->id] = $boxObj;

			// store the box ids of current and past boxes, reference 'all' by id
			if ($boxObj->availability_date_end > TIMESTAMPNOW)
			{
				$this->_fetchResult['boxArray']['current'][$boxObj->id] = $boxObj->id;
			}
			else
			{
				$this->_fetchResult['boxArray']['past'][$boxObj->id] = $boxObj->id;
			}
		}

		return $parent_fetch;
	}

	function getStoreObj()
	{
		if (!empty($this->store_id))
		{
			$this->store_obj = DAO_CFactory::create('store');
			$this->store_obj->id = $this->store_id;
			$this->store_obj->find(true);
		}

		return $this->store_obj;
	}

	function numberSold($n = false)
	{
		$session = DAO_CFactory::create('session');
		$session->session_type = CSession::DELIVERED;

		$booking = DAO_CFactory::create('booking');
		$booking->status = CBooking::ACTIVE;
		$booking->joinAdd($session, array('useWhereAsOn' => true));

		$orders = DAO_CFactory::create('orders');
		$orders->joinAdd($booking, array('useWhereAsOn' => true));

		$box_instance = DAO_CFactory::create('box_instance');
		$box_instance->selectAdd('GROUP_CONCAT(booking.order_id) AS orders');
		$box_instance->box_id = $this->id;
		$box_instance->is_complete = 1;
		$box_instance->whereAdd("box_instance.order_id <> ''");
		$box_instance->joinAdd($orders, array('useWhereAsOn' => true));
		$box_instance->groupBy("box_instance.id");
		$box_instance->orderBy("box_instance.order_id");

		$this->number_sold_n = $box_instance->find();

		$this->orders_n = 0;

		if ($this->number_sold_n && $n)
		{
			while ($box_instance->fetch())
			{
				$this->orders[$box_instance->order_id] = $box_instance->order_id;
			}

			$this->orders_n = count($this->orders);
		}

		return array(
			$this->number_sold_n,
			$this->orders_n,
			$this->orders
		);
	}

	/**
	 * @throws exception
	 */
	function copyBoxToStore($store_id)
	{
		if (empty($this->box_bundle_1_obj))
		{
			$this->getBundle1Obj();
		}

		if (empty($this->box_bundle_2_obj))
		{
			$this->getBundle2Obj();
		}

		$newBox = clone $this;

		if ($store_id)
		{
			$newBox->store_id = $store_id;
			$newBox->insert();

			$boxUpdated = clone $newBox;

			// create new bundle_1 from existing bundle_1
			if (!empty($newBox->box_bundle_1))
			{
				$newBundle = $newBox->box_bundle_1_obj->createBundleFromBundle();

				$boxUpdated->box_bundle_1 = $newBundle->id;
			}

			// create new bundle_2 from existing bundle_2
			if (!empty($newBox->box_bundle_2))
			{
				$newBundle = $newBox->box_bundle_2_obj->createBundleFromBundle();

				$boxUpdated->box_bundle_2 = $newBundle->id;
			}

			$boxUpdated->update($newBox);
		}

		return $newBox;
	}

	function getMenuObj()
	{
		if (!empty($this->menu_id))
		{
			$this->menu_obj = DAO_CFactory::create('menu');
			$this->menu_obj->id = $this->menu_id;
			$this->menu_obj->find(true);
		}

		return $this->menu_obj;
	}

	function getBundle1Obj()
	{
		if (!empty($this->box_bundle_1))
		{
			$this->box_bundle_1_obj = DAO_CFactory::create('bundle');
			$this->box_bundle_1_obj->id = $this->box_bundle_1;
			$this->box_bundle_1_obj->store_id = ((empty($this->store_id)) ? 'NULL' : $this->store_id);
			$this->box_bundle_1_obj->menu_item_array = $this->menu_item_array;
			$this->box_bundle_1_obj->find(true);
		}

		return $this->box_bundle_1_obj;
	}

	function getBundle2Obj()
	{
		if (!empty($this->box_bundle_2))
		{
			$this->box_bundle_2_obj = DAO_CFactory::create('bundle');
			$this->box_bundle_2_obj->id = $this->box_bundle_2;
			$this->box_bundle_2_obj->store_id = ((empty($this->store_id)) ? 'NULL' : $this->store_id);
			$this->box_bundle_2_obj->menu_item_array = $this->menu_item_array;
			$this->box_bundle_2_obj->find(true);
		}

		return $this->box_bundle_2_obj;
	}

	/**
	 * @throws Exception
	 */
	static function getBoxByID($box_id)
	{

		$theObj = DAO_CFactory::create('box');
		$theObj->id = $box_id;

		return $theObj->find(true);
	}

	private function expandBundles($getBundleItems = true, $factorCartIntoInventory = false)
	{
		if (!empty($this->box_bundle_1))
		{
			$this->expandBundle('box_bundle_1', $getBundleItems, $factorCartIntoInventory);
		}

		if (!empty($this->box_bundle_2))
		{
			$this->expandBundle('box_bundle_2', $getBundleItems, $factorCartIntoInventory);
		}
	}

	private function expandBundle($bundlePropertyName, $getBundleItems, $factorCartIntoInventory)
	{
		$bundlePropObj = $bundlePropertyName . "_obj";

		$this->{$bundlePropObj} = CBundle::getBundleByID($this->{$bundlePropertyName});

		if ($getBundleItems)
		{
			$this->{$bundlePropObj}->getDeliveredBundleMenuItems($factorCartIntoInventory, $this->store_id);
		}

		$this->bundle[$this->{$bundlePropObj}->id] = $this->{$bundlePropObj};

		if ($getBundleItems)
		{
			$this->info['total_remaining_servings'] += $this->{$bundlePropObj}->info['total_remaining_servings'];
			$this->info['total_in_stock_menu_items'] += $this->{$bundlePropObj}->info['total_in_stock_menu_items'];
			if (is_array($this->info['all_recipes']) && is_array($this->{$bundlePropObj}->info['all_recipes']))
			{
				$this->info['all_recipes'] = array_replace($this->info['all_recipes'], $this->{$bundlePropObj}->info['all_recipes']);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	static function getBoxArray($storeID = false, $getByID = false, $getBundleItems = true, $factorCartIntoInventory = false, $boxTypeArray = false, $getByMenuID = false, $is_visible_to_customer = false)
	{
		$DAO_box = DAO_CFactory::create('box', true);
		$DAO_box->store_id = 'NULL';

		if ($is_visible_to_customer)
		{
			$DAO_box->is_visible_to_customer = 1;
		}

		if (!empty($storeID))
		{
			$DAO_box->store_id = $storeID;
		}

		if (!empty($getByID))
		{
			$DAO_box->id = $getByID;
		}

		if (!empty($getByMenuID))
		{
			$DAO_box->menu_id = $getByMenuID;
		}
		else
		{
			$today = date('Y-m-d H:i:s');
			$DAO_box->whereAdd("box.availability_date_start < '" . $today . "' AND box.availability_date_end >= '" . $today . "'");
		}

		if (!empty($boxTypeArray))
		{
			$DAO_box->whereAdd("box.box_type IN ('" . implode("','", $boxTypeArray) . "')");
		}

		$DAO_box->orderBy("box.sort DESC, box.availability_date_end, box.id");
		$DAO_box->find();

		$boxArray = array(
			'info' => array(
				'menu_id' => null,
				'all_recipes' => array()
			),
			'box' => array()
		);

		while ($DAO_box->fetch())
		{
			$boxArray['box'][$DAO_box->id] = $DAO_box->cloneObj(true);

			$boxArray['box'][$DAO_box->id]->expandBundles($getBundleItems, $factorCartIntoInventory);

			$boxArray['info']['menu_id'] = $DAO_box->menu_id;

			// roll up info
			if (is_array($boxArray['info']['all_recipes']) && is_array($boxArray['box'][$DAO_box->id]->info['all_recipes']))
			{
				$boxArray['info']['all_recipes'] = array_replace($boxArray['info']['all_recipes'], $boxArray['box'][$DAO_box->id]->info['all_recipes']);
			}
		}

		if (!empty($boxArray['box']))
		{
			return $boxArray;
		}

		return false;
	}

	static function addDeliveredInventoryToMenuArray(&$inArray, $store_id, $orderIsACTIVE)
	{
		// note: unless order is active the initial inventory has not been reduced by the current items

		$parent_store_id = CStore::getParentStoreID($store_id);
		// form unique list of recipes
		$inventoryArray = array(); // recipe_id -> remaining_inventory
		foreach ($inArray['box'] as $box_id => $box_data)
		{
			foreach ($box_data->bundle as $bundle_id => $bundleObj)
			{
				foreach ($bundleObj->menu_item['items'] as $entree_id => $entrees)
				{
					foreach ($entrees as $id => $DAO_menu_item)
					{
						$inventoryArray[$DAO_menu_item->recipe_id] = 0;
					}
				}
			}
		}

		$recipeList = implode(",", array_keys($inventoryArray));

		$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory', true);
		$DAO_menu_item_inventory->menu_id = $inArray['info']['menu_id'];
		$DAO_menu_item_inventory->store_id = $parent_store_id;
		$DAO_menu_item_inventory->whereAdd("menu_item_inventory.recipe_id in (" . $recipeList . ")");
		$DAO_menu_item_inventory->find();

		while ($DAO_menu_item_inventory->fetch())
		{
			if ($orderIsACTIVE)
			{
				$inventoryArray[$DAO_menu_item_inventory->recipe_id] = $DAO_menu_item_inventory->override_inventory - $DAO_menu_item_inventory->number_sold;
			}
			else
			{
				//if order is not active then it's items are not yet permanently removed from  - subtract that as well
				$thisOrdersUsage = 0;
				$inventoryArray[$DAO_menu_item_inventory->recipe_id] = $DAO_menu_item_inventory->override_inventory - ($DAO_menu_item_inventory->number_sold + 0);
			}
		}

		$inArray['inventory_map'] = $inventoryArray;
		foreach ($inArray['box'] as $box_id => $box_data)
		{
			if ($box_data->box_type == CBox::DELIVERED_FIXED)
			{
				if (!empty($inArray['bundle_items'][$box_data->box_bundle_1]))
				{
					foreach ($inArray['bundle_items'][$box_data->box_bundle_1] as $item)
					{
						if ($inventoryArray[$item['recipe_id']] < $item['servings_per_item'])
						{
							$inArray['box'][$box_id]->bundle1_out_of_stock = true;
							break;  // 1 item out of stock makes the entire fixed box out of stock
						}
					}
				}

				if (!empty($inArray['bundle_items'][$box_data->box_bundle_2]))
				{
					foreach ($inArray['bundle_items'][$box_data->box_bundle_2] as $item)
					{
						if ($inventoryArray[$item['recipe_id']] < $item['servings_per_item'])
						{
							$inArray['box'][$box_id]->bundle2_out_of_stock = true;
							break;  // 1 item out of stock makes the entire fixed box out of stock
						}
					}
				}
			}
			else
			{
				$count1itemORMoreEntrees = 0;
				$count2itemORMoreEntrees = 0;

				// TODO: if there are fewer than total servings required across all items
				// in the bundle then the entire box is out of stock

				if (!empty($inArray['bundle_items'][$box_data->box_bundle_1]))
				{
					foreach ($inArray['bundle_items'][$box_data->box_bundle_1] as $miid => $item)
					{
						if ($item['servings_per_item'] > $inventoryArray[$item['recipe_id']])
						{
							$inArray['bundle_items'][$box_data->box_bundle_2][$miid]["out_of_stock"] = true;
						}
					}
				}

				if (!empty($inArray['bundle_items'][$box_data->box_bundle_2]))
				{
					foreach ($inArray['bundle_items'][$box_data->box_bundle_2] as $miid => $item)
					{
						if ($item['servings_per_item'] > $inventoryArray[$item['recipe_id']])
						{
							$inArray['bundle_items'][$box_data->box_bundle_2][$miid]["out_of_stock"] = true;
						}
					}
				}
			}
		}

		return $inventoryArray;
	}

	/**
	 * @throws Exception
	 */
	static function quickCheckForBoxAvailableInState($state_id)
	{
		$ckzip = DAO_CFactory::create('zipcodes');
		$ckzip->state = $state_id;
		$ckzip->whereAdd("zipcodes.distribution_center IS NOT NULL");

		if ($ckzip->find(true))
		{
			return CBox::quickCheckForBoxAvailable($ckzip->distribution_center);
		}

		return false;
	}

	/**
	 * @throws Exception
	 */
	static function quickCheckForBoxAvailable($storeID, $getByID = false, $factorCartObjIntoInventory = false)
	{
		// get by id else get all active boxes
		if ($getByID)
		{
			$by_id_or_active_or_menu_id = "AND b.id = " . $getByID;
		}

		$boxesAvailable = 0;

		$box = DAO_CFactory::create('box');
		$box->query("SELECT
			b.id,
			b.box_type,
			b.box_bundle_1,
			b.box_bundle_2,
			IF(ISNULL(b1.menu_id), b2.menu_id, b1.menu_id) AS menu_id,
			b1.number_servings_required as number_servings_required1,
			b2.number_servings_required as number_servings_required2,
			b1.number_items_required as number_items_required1,
			b2.number_items_required as number_items_required2,
			st.parent_store_id
			FROM
			box AS b
			LEFT JOIN bundle AS b1 ON b1.id = b.box_bundle_1 AND b1.is_deleted = 0
			LEFT JOIN bundle AS b2 ON b2.id = b.box_bundle_2 AND b2.is_deleted = 0
			join store st on st.id = $storeID
			WHERE b.is_deleted = 0
			AND b.availability_date_start < '" . TIMESTAMPNOW . "' AND b.availability_date_end >= '" . TIMESTAMPNOW . "'
			AND b.store_id = $storeID
			ORDER BY b.sort ASC, b.availability_date_end ASC");

		while ($box->fetch())
		{
			if (!empty($box->box_bundle_1))
			{
				$bundle1Inv = self::getBundleInventoryArray($box->box_bundle_1, $box->menu_id, $box->parent_store_id, $factorCartObjIntoInventory);
			}

			if (!empty($box->box_bundle_2))
			{
				$bundle2Inv = self::getBundleInventoryArray($box->box_bundle_2, $box->menu_id, $box->parent_store_id, $factorCartObjIntoInventory);
			}

			if ($box->box_type == CBox::DELIVERED_CUSTOM)
			{
				if (!empty($box->box_bundle_1) && self::hasInventoryForCustomBox($bundle1Inv, $box->number_servings_required1, $box->number_items_required1))
				{
					$boxesAvailable++;
				}

				if (!empty($box->box_bundle_2) && self::hasInventoryForCustomBox($bundle2Inv, $box->number_servings_required2, $box->number_items_required2))
				{
					$boxesAvailable++;
				}
			}
			else
			{
				if (!empty($box->box_bundle_1) && self::hasInventoryForFixedBox($bundle1Inv, $box->number_servings_required1 / $box->number_items_required1))
				{
					$boxesAvailable++;
				}
				if (!empty($box->box_bundle_2) && self::hasInventoryForFixedBox($bundle2Inv, $box->number_servings_required2 / $box->number_items_required2))
				{
					$boxesAvailable++;
				}
			}
		}

		if ($boxesAvailable > 0)
		{
			return true;
		}

		return false;
	}

	static private function hasInventoryForCustomBox($inArray, $servingsReqd, $itemsReqd)
	{
		$servingsPerItem = $servingsReqd / $itemsReqd;
		$servingsAvailable = 0;

		foreach ($inArray as $id => $remainingServings)
		{

			if ($remainingServings - ($servingsPerItem * 2) >= 0)
			{
				$servingsAvailable += ($servingsPerItem * 2);
			}
			else if ($remainingServings - $servingsPerItem >= 0)
			{
				$servingsAvailable += $servingsPerItem;
			}
		}

		if ($servingsAvailable > $servingsReqd)
		{
			return true;
		}

		return false;
	}

	static private function hasInventoryForFixedBox($inArray, $servingsPerItem)
	{
		foreach ($inArray as $id => $remainingServings)
		{
			if ($remainingServings - $servingsPerItem < 0)
			{
				return false;
			}
		}

		return true;
	}

	static private function getBundleInventoryArray($bundle_id, $menu_id, $store_id, $factorCartObjIntoInventory = false)
	{
		$flattenListOfBoxItems = array();

		if (!empty($factorCartObjIntoInventory))
		{
			$boxes = $factorCartObjIntoInventory->getOrder()->getBoxes();

			if (!empty($boxes))
			{
				foreach ($boxes as $thisBox)
				{
					if (!empty($thisBox['box_instance']->is_complete))
					{
						foreach ($thisBox['items'] as $item)
						{
							if (!empty($flattenListOfBoxItems[$item[1]->recipe_id]))
							{
								$flattenListOfBoxItems[$item[1]->recipe_id] += ($item[1]->servings_per_item * $item[0]);
							}
							else
							{
								$flattenListOfBoxItems[$item[1]->recipe_id] = $item[1]->servings_per_item * $item[0];
							}
						}
					}
				}
			}
		}

		$retVal = array();

		$InvRetriever = new DAO();
		$InvRetriever->query("SELECT
			bmi.bundle_id, 
			mi.recipe_id, 
			mii.override_inventory, 
			mii.number_sold
			FROM bundle_to_menu_item AS bmi
			JOIN menu_item AS mi ON bmi.menu_item_id = mi.id
			JOIN menu_item_inventory AS mii ON mi.recipe_id = mii.recipe_id AND mii.store_id = $store_id AND mii.menu_id = $menu_id AND mii.is_deleted = 0
			WHERE bmi.bundle_id = $bundle_id");

		while ($InvRetriever->fetch())
		{
			$retVal[$InvRetriever->recipe_id] = $InvRetriever->override_inventory - $InvRetriever->number_sold;

			// subtract cart inventory
			if (!empty($flattenListOfBoxItems))
			{
				$retVal[$InvRetriever->recipe_id] -= array_key_exists($InvRetriever->recipe_id, $flattenListOfBoxItems) ? $flattenListOfBoxItems[$InvRetriever->recipe_id] : 0;
			}
		}

		return $retVal;
	}

}

?>