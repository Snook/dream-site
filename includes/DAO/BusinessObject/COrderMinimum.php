<?php

require_once 'DAO/Order_minimum.php';

class COrderMinimum extends DAO_Order_minimum
{

	// Minimum TYPES
	const ITEM = 'ITEM';
	const SERVING = 'SERVING';

	// Order TYPES
	const STANDARD_ORDER_TYPE = 'STANDARD';

	//Store Types
	const NON_CORPORATE_STORE = 'NON_CORPORATE_STORE';
	const CORPORATE_STORE = 'CORPORATE_STORE';

	private $instance = null;

	private $isMinimumApplicable = true;
	private $hasFreezerItems = false;

	function __construct($instance = false)
	{
		parent::__construct();

		if ($instance)
		{
			$this->instance = $instance;
		}
	}

	/**
	 *
	 * Create new order_minimum record
	 *
	 * !! Will update only minimum if $orderType, $storeId, $menuId composite key already exists
	 *
	 * @param string $type      - minimum type (required const in[ITEM, SERVING])
	 * @param int    $minimum   - minimum (required)
	 * @param string $orderType - type of order the minimum applies to (const in [STANDARD_ORDER_TYPE], default: STANDARD_ORDER_TYPE)
	 * @param int    $store_id  - menu_id of menu that this minimum applies to (optional)
	 * @param int    $menu_id   - store_id of the store this applies to (optional)
	 *
	 *
	 *  If store, menu or both are null then they serve as defaults:
	 *                        1) Both null: default for all stores, menus
	 *                        2) Store not null, menu null: default for store
	 *                        3) Store null, menu not null: default for menu
	 *
	 *
	 * @throws Exception
	 */
	static function createInstance($type, $minimum, $orderType = COrderMinimum::STANDARD_ORDER_TYPE, $store_id = null, $menu_id = null)
	{

		if (!is_numeric($minimum))
		{
			return 'error';
		}

		$orderMinimum = DAO_CFactory::create("order_minimum");

		$orderMinimum->order_type = $orderType;

		if (is_numeric($store_id))
		{
			$orderMinimum->store_id = $store_id;
		}

		if (is_null($store_id))
		{
			$orderMinimum->whereAdd(" store_id IS NULL");
		}

		if (is_numeric($menu_id))
		{
			$orderMinimum->menu_id = $menu_id;
		}

		if (is_null($menu_id))
		{
			$orderMinimum->whereAdd(" menu_id IS NULL");
		}

		if ($orderMinimum->find(true) > 0)
		{
			$orderMinimum->minimum_type = $type;
			if (is_numeric($minimum))
			{
				$orderMinimum->minimum = $minimum;
			}

			$orderMinimum->update();
		}
		else
		{
			$orderMinimum->minimum_type = $type;
			if (is_numeric($minimum))
			{
				$orderMinimum->minimum = $minimum;
			}
			$orderMinimum->insert();
		}
	}

	/**
	 *
	 * Statically determine if the OrderMinimum record for the given store/menu
	 * is set to allow additional ordering.
	 *
	 *  Match results are based off the following rules:
	 *      1) If no specific store and menu match, check store default (i.e record where storeId is not null and menu id is null)
	 *      2) If no store default, check menu default (i.e record where storeId is null and menu id is not null)
	 *      3) If no default, return global default for minimum/order type
	 *
	 **
	 * @param $store_id null|id
	 * @param $menu_id  null|id
	 *
	 *
	 * @return bool true if the store/menu is configured to allow additional ordering
	 * @throws Exception
	 */
	public static function allowsAdditionalOrdering($store_id, $menu_id)
	{

		$orderMinimum = DAO_CFactory::create("order_minimum");
		if (is_numeric($store_id) && is_numeric($menu_id))
		{
			$orderMinimum->store_id = $store_id;
			$orderMinimum->menu_id = $menu_id;
			$orderMinimum->find(true);
		}

		//if store and menu supplied and match found return
		if ($orderMinimum->N > 0)
		{
			return $orderMinimum->allows_additional_ordering;
		}

		$orderMinimum = DAO_CFactory::create("order_minimum");
		$orderMinimum->store_id = null;
		$orderMinimum->menu_id = null;

		//try just by store
		if (is_numeric($store_id))
		{
			$orderMinimum->store_id = $store_id;
			$orderMinimum->whereAdd(" menu_id IS NULL");
			$orderMinimum->find(true);
			if ($orderMinimum->N > 0)
			{
				return $orderMinimum->allows_additional_ordering;
			}
		}

		$orderMinimum = DAO_CFactory::create("order_minimum");
		$orderMinimum->store_id = null;
		$orderMinimum->menu_id = null;

		//try just by menu
		if (is_numeric($menu_id))
		{
			$orderMinimum->store_id = null;
			$orderMinimum->menu_id = $menu_id;
			$orderMinimum->whereAdd(" store_id IS NULL");
			$orderMinimum->find(true);
			if ($orderMinimum->N > 0)
			{
				return $orderMinimum->allows_additional_ordering;
			}
		}

		//global default by minimum_type and order_type
		$orderMinimum = DAO_CFactory::create("order_minimum");
		$orderMinimum->store_id = null;
		$orderMinimum->menu_id = null;
		$orderMinimum->whereAdd(" menu_id IS NULL");
		$orderMinimum->whereAdd(" store_id IS NULL");
		$orderMinimum->find(true);
		if ($orderMinimum->N > 0)
		{
			return $orderMinimum->allows_additional_ordering;
		}
		else
		{
			//log exception -- there should always be a global default in the database
			CLog::RecordNew(CLog::ERROR, "Default Additional Ordering value is not configured for store ({$store_id}), menu ({$menu_id})", "COrderMinimum", "169", true);

			return false;
		}
	}

	/**
	 *
	 * Statically determine if the OrderMinimum record for the given store type/menu
	 * is set to allow additional ordering.
	 *
	 * If the specific store/menu is not found then the global will be used.
	 **
	 *
	 * @param $store_id required
	 * @param $menu_id  required
	 *
	 * @return bool true if the any store/menu of the specified type is configured to allow additional ordering
	 * @throws Exception
	 */
	public static function allowsAdditionalOrderingByStoreType($store_type, $menu_id)
	{

		$stores = DAO_CFactory::create("store");
		$stores->active = 1;

		$hasValidType = false;
		if ($store_type == COrderMinimum::CORPORATE_STORE)
		{
			$hasValidType = true;
			$stores->is_corporate_owned = 1;
		}
		else if ($store_type == COrderMinimum::NON_CORPORATE_STORE)
		{
			$hasValidType = true;
			$stores->is_corporate_owned = 0;
		}

		$stores->find();
		while ($hasValidType && $stores->fetch())
		{
			if (!is_null($stores->id) && !is_null($menu_id))
			{
				$orderMinimum = DAO_CFactory::create("order_minimum");
				$orderMinimum->store_id = $stores->id;
				$orderMinimum->menu_id = $menu_id;

				if ($orderMinimum->find(true))
				{
					return $orderMinimum->allows_additional_ordering;
				}
			}
		}

		return false;
	}

	/**
	 * @param string $type      - minimum type (required const in[ITEM, SERVING])
	 * @param string $orderType - type of order the minimum applies to (const in [STANDARD_ORDER_TYPE], default: STANDARD_ORDER_TYPE)
	 * @param int    $store_id  - menu_id of menu that this minimum applies to (optional)
	 * @param int    $menu_id   - store_id of the store this applies to (optional)
	 *
	 *
	 *
	 *  Match results are based off the following rules:
	 *      1) If no store and menu match, check store default (i.e record where storeId is not null and menu id is null)
	 *      2) If no store default, check menu default (i.e record where storeId is null and menu id is not null)
	 *      3) If no default, return global default for minimum/order type
	 *
	 * @return COrderMinimum - a hydrated instance if a matching record is found. If id is null then a matching record was not
	 *           found and hardcoded values where returned ( ITEM == 3, SERVING = 36)
	 * @throws Exception
	 */
	static function fetchInstanceByMinimumType($type, $orderType = COrderMinimum::STANDARD_ORDER_TYPE, $store_id = null, $menu_id = null)
	{

		$orderMinimum = DAO_CFactory::create("order_minimum");

		if (is_null($type))
		{
			//if minimum_type is not passed then infer the type from the store/menu
			$orderMinimum->minimum_type = self::inferMinimumType($orderType, $store_id, $menu_id);
		}

		$orderMinimum->order_type = $orderType;

		if (is_numeric($store_id) && is_numeric($menu_id))
		{
			$orderMinimum->store_id = $store_id;
			$orderMinimum->menu_id = $menu_id;
			$orderMinimum->find(true);
		}

		//if store and menu supplied and match found return
		if ($orderMinimum->N > 0)
		{
			return new COrderMinimum($orderMinimum);
		}
		$orderMinimum = DAO_CFactory::create("order_minimum");
		$orderMinimum->store_id = null;
		$orderMinimum->menu_id = null;

		//try just by store
		if (is_numeric($store_id))
		{
			$orderMinimum->store_id = $store_id;
			$orderMinimum->whereAdd(" menu_id IS NULL");
			$orderMinimum->find(true);
			if ($orderMinimum->N > 0)
			{
				return new COrderMinimum($orderMinimum);
			}
		}

		$orderMinimum = DAO_CFactory::create("order_minimum");
		$orderMinimum->store_id = null;
		$orderMinimum->menu_id = null;

		//try just by menu
		if (is_numeric($menu_id))
		{
			$orderMinimum->store_id = null;
			$orderMinimum->menu_id = $menu_id;
			$orderMinimum->whereAdd(" store_id IS NULL");
			$orderMinimum->find(true);
			if ($orderMinimum->N > 0)
			{
				return new COrderMinimum($orderMinimum);
			}
		}

		$orderMinimum = DAO_CFactory::create("order_minimum");
		$orderMinimum->store_id = null;
		$orderMinimum->menu_id = null;

		//global default by minimum_type and order_type
		$orderMinimum->store_id = null;
		$orderMinimum->menu_id = null;
		$orderMinimum->whereAdd(" menu_id IS NULL");
		$orderMinimum->whereAdd(" store_id IS NULL");
		$orderMinimum->find(true);
		if ($orderMinimum->N > 0)
		{
			return new COrderMinimum($orderMinimum);
		}
		else
		{
			//log exception -- there should always be a global default in the database
			CLog::RecordNew(CLog::ERROR, "Default minimums are not configured for type ({$type}), orderType({$orderType}), store ({$store_id}), menu ({$menu_id})", "COrderMinimum", "149", true);

			//for now return original system default in this error case
			//TODO: evanl - this should go away if the database is setup correctly
			if ($type == COrderMinimum::ITEM)
			{
				$orderMinimum->minimum = 3;
				$orderMinimum->minimum_type = COrderMinimum::ITEM;
			}
			else
			{
				$orderMinimum->minimum = 36;
				$orderMinimum->minimum_type = COrderMinimum::SERVING;
			}

			return new COrderMinimum($orderMinimum);
		}
	}

	/**
	 * @param string $orderType - type of order the minimum applies to (const in [STANDARD_ORDER_TYPE], default: STANDARD_ORDER_TYPE)
	 * @param int    $store_id  - menu_id of menu that this minimum applies to (optional)
	 * @param int    $menu_id   - store_id of the store this applies to (optional)
	 *
	 *
	 *
	 *  Match results are based off the following rules:
	 *      1) If no store and menu match, check store default (i.e record where storeId is not null and menu id is null)
	 *      2) If no store default, check menu default (i.e record where storeId is null and menu id is not null)
	 *      3) If no default, return global default for minimum/order type
	 *
	 * @return COrderMinimum - a hydrated instance if a matching record is found. If id is null then a matching record was not
	 *           found and hardcoded values where returned ( ITEM == 3, SERVING = 36)
	 * @throws Exception
	 */
	static function fetchInstance($orderType = COrderMinimum::STANDARD_ORDER_TYPE, $store_id = null, $menu_id = null)
	{

		return self::fetchInstanceByMinimumType(null, $orderType, $store_id, $menu_id);
	}

	/**
	 * @param string $orderType - type of order the minimum applies to (const in [STANDARD])
	 * @param int    $store_id  - menu_id of menu that this minimum applies to (optional)
	 * @param int    $menu_id   - store_id of the store this applies to (optional)
	 *
	 */
	static function inferMinimumType($orderType, $store_id = null, $menu_id = null)
	{
		$orderMinimum = DAO_CFactory::create("order_minimum");
		$orderMinimum->order_type = $orderType;

		if (is_numeric($store_id))
		{
			$orderMinimum->store_id = $store_id;
		}

		if (is_null($store_id))
		{
			$orderMinimum->whereAdd(" store_id IS NULL");
		}

		if (is_numeric($menu_id))
		{
			$orderMinimum->menu_id = $menu_id;
		}

		if (is_null($menu_id))
		{
			$orderMinimum->whereAdd(" menu_id IS NULL");
		}

		$orderMinimum->find(true);

		$type = $orderMinimum->minimum_type;
		if(is_null($type)){
			$type = COrderMinimum::SERVING;
		}

		return $type;
	}

	/**
	 * JSON representation of the fetched order_minimum record.
	 *
	 * @param string $orderType - type of order the minimum applies to (const in [STANDARD_ORDER_TYPE], default: STANDARD_ORDER_TYPE)
	 * @param int    $store_id  - menu_id of menu that this minimum applies to (optional)
	 * @param int    $menu_id   - store_id of the store this applies to (optional)
	 *
	 * @return json string with name value pair representation of the persisted values
	 * @throws Exception
	 */
	static function asJson($orderType = COrderMinimum::STANDARD_ORDER_TYPE, $store_id = null, $menu_id = null)
	{

		$ordersMinimum = self::fetchInstance($orderType, $store_id, $menu_id);
		$assocArray = $ordersMinimum->instance->toArray();
		self:: cleanDoaAttributes($assocArray);

		return json_encode($assocArray);
	}

	/**
	 * Fetches the minimum number required , miminum type being infered from store and menu.
	 *
	 * @param string $orderType - type of order the minimum applies to (const in [STANDARD_ORDER_TYPE], default: STANDARD_ORDER_TYPE)
	 * @param int    $store_id  - menu_id of menu that this minimum applies to (optional)
	 * @param int    $menu_id   - store_id of the store this applies to (optional)
	 *
	 * @return int number of ITEMS or SERVINGS required
	 * @throws Exception
	 */
	static function fetchMinimum($orderType = COrderMinimum::STANDARD_ORDER_TYPE, $store_id = null, $menu_id = null)
	{

		$ordersMinimum = self::fetchInstance($orderType, $store_id, $menu_id);

		return $ordersMinimum->getMinimum();
	}

	/**
	 * Fetches the minimum number required , miminum type being infered from store and menu.
	 *
	 * @param string $type      - minimum type (required const in[ITEM, SERVING])
	 * @param string $orderType - type of order the minimum applies to (const in [STANDARD_ORDER_TYPE], default: STANDARD_ORDER_TYPE)
	 * @param int    $store_id  - menu_id of menu that this minimum applies to (optional)
	 * @param int    $menu_id   - store_id of the store this applies to (optional)
	 *
	 * @return int number of ITEMS or SERVINGS required
	 * @throws Exception
	 */
	static function fetchMinimumForMinimumType($type, $orderType = COrderMinimum::STANDARD_ORDER_TYPE, $store_id = null, $menu_id = null)
	{

		$ordersMinimum = self::fetchInstanceByMinimumType($type, $orderType, $store_id, $menu_id);

		return $ordersMinimum->getMinimum();
	}

	/**
	 * This will copy the existing Minimum and Additional Order setting for all configured
	 * store/menu to the next menu month
	 *
	 * @param $lastMenuId
	 * @param $nextMenuId
	 */
	static function carryForwardMinimums($lastMenuId, $nextMenuId)
	{

		if (!is_null($lastMenuId) && !is_null($nextMenuId))
		{

			$orderMinimum = DAO_CFactory::create("order_minimum");
			$orderMinimum->menu_id = $lastMenuId;
			$orderMinimum->find();
			while ($orderMinimum->fetch())
			{
				$clonedOrderMinimum = DAO_CFactory::create("order_minimum");
				$clonedOrderMinimum->menu_id = $nextMenuId;
				$clonedOrderMinimum->store_id = $orderMinimum->store_id;
				$clonedOrderMinimum->order_type = $orderMinimum->order_type;
				$clonedOrderMinimum->minimum_type = $orderMinimum->minimum_type;
				$clonedOrderMinimum->minimum = $orderMinimum->minimum;
				$clonedOrderMinimum->allows_additional_ordering = $orderMinimum->allows_additional_ordering;

				if ($clonedOrderMinimum->find(true))
				{
					$clonedOrderMinimum->update();
				}
				else
				{
					$clonedOrderMinimum->insert();
				}
			}
		}
		else
		{
			CLog::RecordNew(CLog::ERROR, "Attempted to carryForwardMinimums but last menu was empty", "", "", true);
		}
	}

	/**
	 *
	 * Converts the hydrated order_item dao to JSON
	 *
	 * @param false $withDaoAttributes will return meta info from the record (e.g. id, created_by, timestamps, etc...)
	 *
	 * @return false|string
	 */
	public function toJson($withDaoAttributes = false)
	{
		$assocArray = $this->instance->toArray();
		self:: cleanDoaAttributes($assocArray);
		$assocArray['minimum'] = $this->instance->minimum;
		$assocArray['is_applicable'] = $this->isMinimumApplicable();
		$assocArray['has_freezer_inventory'] = $this->hasFreezerInventory();

		return json_encode($assocArray);
	}

	/**
	 *    Takes generic minimum for Store/Menu and makes it even more specific to the passed user.
	 *    So for example, if the user already has a qualifying minimum order then this minimum is
	 *    no longer directly applicable
	 *
	 * Supplying the storeId and MenuId is for the case when the default global minimum
	 * needs to be updated for a specific menu and store.
	 *
	 *
	 * @param $UserObj
	 * @param $storeId store ID for which the minimum is configured
	 * @param $menuId  menu ID for which the minimu is configured
	 */
	public function updateBasedOnUserStoreMenu($UserObj, $storeId = null, $menuId = null)
	{
		if (!is_null($UserObj))
		{
			$this->isMinimumApplicable = !$UserObj->hasMinimumQualifyingOrderDefined($storeId, $menuId);
			if (!$this->isMinimumApplicable)
			{
				//TODO: evanl anything useful to set here?
			}
		}
	}

	//********  UI Helper Methods   **************//

	/**       FADMIN */

	public function formulateOrderMgrHeaderLabel($orderDao)
	{

		if ($orderDao->type_of_order == COrders::STANDARD)
		{
			$msg = 'Core %s/Minimum %s';
			if ($this->getMinimumType() == COrderMinimum::SERVING)
			{
				$msg = sprintf($msg, 'Servings', 'Servings');
			}
			else
			{
				$msg = sprintf($msg, 'Items', 'Items');
			}

			return $msg;
		}

		return 'Core Servings/Minimum Servings';
	}

	/**
	 * Check to see if this order qualifies as a minimum order based on the
	 * configured minimus for the order's store and menu.
	 *
	 *
	 * @param $order order with session/menu/store attributes populated.
	 *
	 * @return bool true if the passed order meets the configured or default
	 *              minimum order type for the store/menu of the order.
	 * @throws Exception
	 */
	public static function doesOrderQualifiesAsMinimum($order)
	{

		$menuId = $order->getSessionObj(true)->menu_id;

		$storeId = $order->store_id;

		$orderMinimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $storeId, $menuId);

		if ($orderMinimum->getMinimumType() == COrderMinimum::ITEM)
		{
			$items = $order->countItems();
			if ($items >= $orderMinimum->getMinimum())
			{
				return true;
			}
		}
		else
		{
			$servings = $order->countContributingServings();
			if ($servings >= $orderMinimum->getMinimum())
			{
				return true;
			}
		}

		return false;
	}

	//---------------------   attribute modifiers

	//DAO attributes
	/**
	 * DAO Attribute
	 *
	 *
	 * Matches the constants on this class (STANDARD )
	 *
	 * @return null|string matching constant on this class
	 */
	public function getOrderType()
	{
		if (is_null($this->instance))
		{
			return null;
		}

		return $this->instance->order_type;
	}

	/**
	 * DAO Attribute
	 *
	 * Return the persisted value.
	 *
	 * @return mixed
	 */
	public function getAllowsAdditionalOrdering()
	{
		if (is_null($this->instance))
		{
			return null;
		}

		return $this->instance->allows_additional_ordering;
	}

	/**
	 * DAO Attribute
	 *
	 * Matches the constants on this class (ITEM | SERVING )
	 *
	 * @return mixed string|null return the minimum type (ITEM | SERVING) if the wrapper contains a populated DOA instance
	 */
	public function getMinimumType()
	{
		if (is_null($this->instance))
		{
			return null;
		}

		return $this->instance->minimum_type;
	}

	/**
	 * DAO Attribute
	 *
	 * @return mixed number|null return the minimum if the wrapper contains a populated DOA instance
	 */
	public function getMinimum()
	{
		if (is_null($this->instance))
		{
			return null;
		}

		return $this->instance->minimum;
	}

	/**
	 * Determines if the order minimum qualifies as zero dollar assembly
	 *
	 * @return bool true if the minimum_type/menu_id is within parameters
	 */
	public function isZeroDollarAssembly()
	{
		if ($this->getMenuId() >= 257 && $this->getMinimumType() == COrderMinimum::ITEM)
		{
			return true;
		}

		return false;
	}

	/**
	 * DAO Attribute
	 *
	 * Fetches the associated MenuDAO
	 *
	 * @return mixed|null fetch and return the MenuObj based on the storeId set on this instance
	 * @throws Exception
	 */
	public function getMenu()
	{
		if (!is_null($this->getMenuId()))
		{
			$dao = DAO_CFactory::create("menu");
			$dao->id = $this->getMenuId();
			$dao->find(true);

			return $dao;
		}

		return null;
	}

	/**
	 * DAO Attribute
	 *
	 * @return mixed number|null return the menu id if the wrapper contains a populated DOA instance
	 */
	public function getMenuId()
	{
		if (is_null($this->instance))
		{
			return null;
		}

		return $this->instance->menu_id;
	}

	/**
	 * set DAO Attribute
	 *
	 */
	public function setMenuId($val)
	{
		if (is_null($this->instance))
		{
			return;
		}
		$this->instance->menu_id = $val;
	}

	/**
	 * DAO Attribute
	 *
	 * Fetches the associated StoreDAO
	 *
	 * @return mixed|null fetch and return the StoreObj based on the storeId set on this instand
	 * @throws Exception
	 */
	public function getStore()
	{
		if (!is_null($this->getStoreId()))
		{
			$dao = DAO_CFactory::create("store");
			$dao->id = $this->getStoreId();
			$dao->find(true);

			return $dao;
		}

		return null;
	}

	/**
	 * DAO Attribute
	 *
	 * @return mixed number|null return the store id if the wrapper contains a populated DOA instance
	 */
	public function getStoreId()
	{
		if (is_null($this->instance))
		{
			return null;
		}

		return $this->instance->store_id;
	}

	/**
	 * set DAO Attribute
	 *
	 */
	public function setStoreId($val)
	{
		if (is_null($this->instance))
		{
			return;
		}
		$this->instance->store_id = $val;
	}

	/**
	 * DAO Attribute
	 *
	 *
	 * @return mixed number|null return the record id if the wrapper contains a populated DOA instance
	 */
	public function getId()
	{
		if (is_null($this->instance))
		{
			return null;
		}

		return $this->instance->id;
	}
	//End DAO attributes

	/**
	 * Non-DAO attribute
	 *
	 * !!!!! Answer relies on calling  updateBasedOnUser($UserObj) first to establish
	 * rules for the current user. If this has not been done, then the minimum rules
	 * are assumed to apply
	 *
	 * @return bool true if the user does not already have a minimum order for the month.
	 *              true if updateBasedOnUser($UserObj) has not been called on this instance.
	 *              false if the user already placed a minimum order for the month
	 */
	public function isMinimumApplicable()
	{
		return $this->isMinimumApplicable;
	}

	/**
	 * Non-DAO attribute
	 *
	 * @return bool
	 */
	public function hasFreezerInventory()
	{
		return $this->hasFreezerItems;
	}

	/**
	 * Non-DAO attribute
	 *
	 * Set this to indicate that the store and menu have freezer/non-core items
	 * that can be ordered
	 *
	 * Useful in determining minimum rule validation in the order path.
	 *
	 * @param $value
	 */
	public function setHasFreezerInventory($value)
	{
		$this->hasFreezerItems = $value;
	}

	//End attribute

	private static function cleanDoaAttributes(&$assocArray, $withDaoAttributes = false)
	{

		if (!$withDaoAttributes)
		{
			unset($assocArray['id']);
			unset($assocArray['timestamp_updated']);
			unset($assocArray['timestamp_created']);
			unset($assocArray['updated_by']);
			unset($assocArray['created_by']);
			unset($assocArray['is_deleted']);
		}
	}

}

?>