<?php
require_once('DB.php');
require_once('includes/CCrypto.inc');
require_once('includes/DAO/BusinessObject/CUser.php');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CGPC.inc');

/* ------------------------------------------------------------------------
 *	Class: CCartStorage
 *
 *	Data:
 *
 *	Methods:
 *
 *	Description:
 *
 *	Example:
 * ------------------------------------------------------------------------ */

class CCartStorage
{
	// public

	private $_db = false;

	function __construct()
	{
	}

	function handleConnectionIssue()
	{
	}

	function init()
	{
		$this->connect();

		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "use dreamcart");
		$this->checkResult($result);

		$result = mysqli_query($this->_db, "INSERT INTO dreamcart.cart_contents (state, timestamp_created) VALUES ('INITIALIZED', now())");
		$this->checkResult($result);

		$result = mysqli_query($this->_db, "select LAST_INSERT_ID() as last_id");

		if ($result)
		{
			if ($row = mysqli_fetch_assoc($result))
			{
				$dataRef = $row['last_id'];
			}
		}

		return $dataRef;
	}

	function checkResult($result, $isFatal = true)
	{
		if (!$result)
		{
			$errStr = mysqli_error($this->_db);

			if ($isFatal)
			{
				throw new Exception($errStr);
			}
		}
	}

	function connect()
	{
		if (!$this->_db)
		{
			$this->_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
			mysqli_select_db($this->_db, DB_CART_DATABASE);
		}

		return $this->_db;
	}

	function testForEmptyArray($data_ref, $arrType)
	{

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "select menu_id, data from dreamcart.cart_content_arrays where cart_contents_id = $data_ref and data_type = '$arrType'");
		$this->checkResult($result);

		$numRows = mysqli_num_rows($result);
		if ($numRows <= 0)
		{
			return true;
		}

		$hasData = false;
		while ($row = mysqli_fetch_assoc($result))
		{
			$data = unserialize(base64_decode($row['data']));
			if (!empty($data))
			{
				$hasData = true;
			}
		}

		return !$hasData;
	}

	// Stale carts havce not been created or  updated in 7 days
	// OR have a menu that is closed
	static function getStaleCartRows($currentMenuId)
	{
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);

		$result = mysqli_query($_db, "select cc.id, cc.timestamp_updated, cc.menu_id from `dreamcart`.cart_contents cc
                                where cc.is_stale = 0 and ((cc.timestamp_updated < DATE_SUB(DATE(now()), INTERVAL 7 DAY) and cc.timestamp_created < DATE_SUB(DATE(now()), INTERVAL 7 DAY))
                                or (NOT ISNULL(cc.menu_id) and cc.menu_id <> 0 and  cc.menu_id < $currentMenuId))");

		$staleList = array();
		while ($row = mysqli_fetch_assoc($result))
		{
			$staleList[] = $row['id'];
		}

		// now check if arrays have been updated
		$result = mysqli_query($_db, "select cca.cart_contents_id, cca.timestamp_created, cca.timestamp_updated from `dreamcart`.cart_content_arrays cca
                            join `dreamcart`.cart_contents cc on cc.id = cca.cart_contents_id and cc.is_stale = 0   
                            where (cca.timestamp_updated < DATE_SUB(DATE(now()), INTERVAL 7 DAY) and cca.timestamp_created < DATE_SUB(DATE(now()), INTERVAL 7 DAY))");
		while ($row = mysqli_fetch_assoc($result))
		{
			if (!in_array($row['cart_contents_id'], $staleList))
			{
				$staleList[] = $row['cart_contents_id'];
			}
		}

		return $staleList;
	}

	static function getStaleEditOrderCartRows()
	{
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);

		$result = mysqli_query($_db, "select cc.id, cc.timestamp_updated, cc.menu_id from `dreamcart`.cart_contents cc
                                where cc.is_stale = 0 and 
                                      cc.edit_order_id is not null and
                                      cc.timestamp_updated < DATE_SUB(DATE(now()), INTERVAL 6 HOUR) and cc.timestamp_created < DATE_SUB(DATE(now()), INTERVAL 6 HOUR)");

		$staleList = array();
		while ($row = mysqli_fetch_assoc($result))
		{
			$staleList[] = $row['id'];
		}

		// now check if arrays have been updated
		$result = mysqli_query($_db, "select cca.cart_contents_id, cca.timestamp_created, cca.timestamp_updated from `dreamcart`.cart_content_arrays cca
                            join `dreamcart`.cart_contents cc on cc.id = cca.cart_contents_id and cc.is_stale = 0   
                            where cc.navigation_type = 'DELIVERED' and cc.edit_order_id is not null and (cca.timestamp_updated < DATE_SUB(DATE(now()), INTERVAL 6 HOUR) and cca.timestamp_created < DATE_SUB(DATE(now()), INTERVAL 6 HOUR))");
		while ($row = mysqli_fetch_assoc($result))
		{
			if (!in_array($row['cart_contents_id'], $staleList))
			{
				$staleList[] = $row['cart_contents_id'];
			}
		}

		return $staleList;
	}

	/**
	 *  Set the list of cart record's abandoned_status to 'ABANDONED'.
	 * This status is monitored by the abandoned cart
	 * cron job.
	 *
	 * This leaves the timestamp_update unaltered.
	 *
	 * @param $cartIdList id or records to mark as abandoned
	 *
	 * @throws Exception
	 */
	static function markAbandonedCartRows($cartIdList)
	{

		if (count($cartIdList) > 0)
		{
			$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
			mysqli_select_db($_db, DB_CART_DATABASE);

			$result = mysqli_query($_db, "update `dreamcart`.cart_contents set abandoned_state = 'ABANDONED', 
    										timestamp_updated = timestamp_updated where id in (" . implode(",", $cartIdList) . ")");

			if (!$result)
			{
				$errStr = mysqli_error($_db);
				throw new Exception($errStr);
			}
		}
	}

	/**
	 * Set the given abandoned cart status on the cart_content row
	 *
	 * @param $cartId
	 * @param $status ('INITIAL','ABANDONED','TRIGGERED_SALESFORCE','RETRY_SALESFORCE','FAILED')
	 *
	 * @throws Exception
	 */
	static function markAbandonedCartStatus($cartId, $status)
	{
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);

		$result = mysqli_query($_db, "update `dreamcart`.cart_contents set abandoned_state = '" . $status . "', 
											timestamp_updated = timestamp_updated where id = " . $cartId);

		if (!$result)
		{
			$errStr = mysqli_error($_db);
			throw new Exception($errStr);
		}
	}

	/**
	 *
	 * Look for any existing cart instances that have passed a time threshold.
	 * A cart is considered abandoned if it was last modified more that
	 * 4 hours in the past and it has a state of 'CURRENT', and it has
	 * an user id and that user does not have any orders created that are
	 * newer than the cart.
	 *
	 * Only marks ones that are not already Abandoned
	 *
	 * @param $limit              integer default is 20, max number to operate on
	 * @param $timeThresholdHours integer default is 4, max number to operate on
	 *
	 * @return array array of record id for all abandoned carts.
	 */
	static function detectAbandonedCartRows($limit = 20, $timeThresholdHours = 4)
	{

		$limitClause = 'limit ' . $limit;
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);

		$result = mysqli_query($_db, "SELECT
												cc.id , cc.timestamp_created, cc.timestamp_updated,
       											(select max(timestamp_created) from dreamsite.orders o where user_id = cc.user_id ) as latest_order
											FROM
												dreamcart.cart_contents cc
											WHERE
												cc.navigation_type IN ('STANDARD','INTRO','DELIVERY','DELIVERED','ALL_STANDARD') 
											    AND cc.abandoned_state IN ('INITIAL','RETRY_SALESFORCE')
												AND cc.is_stale = 0 
												AND cc.state = 'CURRENT' 
												AND cc.user_id IS NOT NULL 
												AND cc.timestamp_updated < DATE_SUB( DATE( now()), INTERVAL " . $timeThresholdHours . " HOUR ) 
												AND cc.timestamp_created < DATE_SUB( DATE( now()), INTERVAL " . $timeThresholdHours . " HOUR ) " . $limitClause);

		$staleList = array();
		while ($row = mysqli_fetch_assoc($result))
		{
			$lastOrderDate = strtotime($row['latest_order']);
			$cartCreateDate = strtotime($row['timestamp_created']);
			if ($lastOrderDate < $cartCreateDate)
			{
				$staleList[] = $row['id'];
			}
		}

		return $staleList;
	}

	/**
	 *
	 * Fetch a set of carts with ABANDONED_STATUS = 'ABANDONED' or 'RETRY_SALESFORCE'
	 *
	 * @param $limit integer default is 20, max number to operate on
	 *
	 * @return array array of record id for all abandoned carts.
	 */
	static function fetchAbandonedCartRows($limit = 20)
	{

		$limitClause = 'limit ' . $limit;
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);

		$result = mysqli_query($_db, "select cc.id, cc.user_id, u.primary_email,u.firstname, c.cart_key 
								from dreamcart.cart_contents cc, dreamsite.user u, dreamsite.cart c
                                where c.cart_contents_id = cc.id and 
                                	  cc.user_id = u.id and 
                                      u.is_deleted = 0 and
                                      cc.is_stale = 0 and 
                                      cc.abandoned_state IN ( 'ABANDONED', 'RETRY_SALESFORCE')  " . $limitClause);
		$data = array();
		while ($row = mysqli_fetch_assoc($result))
		{
			$data[] = array(
				'id' => $row['id'],
				'email' => $row['primary_email'],
				'first_name' => $row['firstname'],
				'user_id' => $row['user_id'],
				'cart_key' => $row['cart_key']
			);
		}

		return $data;
	}

	// Stale carts havce not been created or  updated in 7 days
	// OR have a menu that is closed
	static function markRowsAsStale($staleList)
	{
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);

		$result = mysqli_query($_db, "update `dreamcart`.cart_contents set is_stale = 1, timestamp_updated = timestamp_updated where id in (" . implode(",", $staleList) . ")");

		if (!$result)
		{
			$errStr = mysqli_error($_db);
			throw new Exception($errStr);
		}
	}

	static function clear_old_payments()
	{

		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);

		$result = mysqli_query($_db, "DELETE FROM dreamcart.cart_contents_payment WHERE TIMESTAMPDIFF(HOUR,  timestamp_updated,  now()) > 36");
		if (!$result)
		{
			return 'query failed';
		}

		$rows_affected = mysqli_affected_rows($_db);

		return $rows_affected . " rows affected";
	}


	// the presence of a store means the cart is valid and alive
	// this is a quick test for the store id that has no side effects and fails silently
	static function quietTestForStoreID($data_ref)
	{
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);
		$result = mysqli_query($_db, "select store_id from dreamcart.cart_contents where id = $data_ref");
		if (!$result)
		{
			return false;
		}

		if (mysqli_num_rows($result) <= 0)
		{
			return false;
		}

		$store_id = 0;

		if ($row = mysqli_fetch_assoc($result))
		{
			$store_id = $row['store_id'];
		}

		if ($store_id > 0)
		{
			return true;
		}

		return false;
	}

	// the presence of a store means the cart is valid and alive
	// HOWERVER: there is a situation where the store id is null but the cart has a session
	// For now, get the store from the session
	// until we can isolate the path that results in a null store id
	static function getStoreFromSessionIfExists($data_ref)
	{
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);
		$result = mysqli_query($_db, "select session_id from dreamcart.cart_contents where id = $data_ref");
		if (!$result)
		{
			return false;
		}

		if (mysqli_num_rows($result) <= 0)
		{
			return false;
		}

		$session_id = 0;

		if ($row = mysqli_fetch_assoc($result))
		{
			$session_id = $row['session_id'];
		}

		if (!empty($session_id))
		{
			$sessionObj = DAO_CFactory::create('session');
			$sessionObj->query("select store_id from session where id = {$session_id}");
			$sessionObj->fetch();

			return $sessionObj->store_id;
		}

		return false;
	}

	function getDebugViewOfDatabase($data_ref)
	{
		$uberArray = array();

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "select * from dreamcart.cart_content_arrays where cart_contents_id = $data_ref");
		$this->checkResult($result);

		while ($row = mysqli_fetch_assoc($result))
		{
			$uberArray['arrays'][$row['id']] = array(
				'id' => $row['id'],
				'type' => $row['data_type'],
				'menu_id' => $row['menu_id'],
				'data' => unserialize(base64_decode($row['data']))
			);
		}

		$result = mysqli_query($this->_db, "select * from dreamcart.cart_contents where id = $data_ref");
		$this->checkResult($result, false);

		if ($row = mysqli_fetch_assoc($result))
		{

			$uberArray['menu_id'] = $row["menu_id"];
			$uberArray['user_id'] = $row["user_id"];
			$uberArray['store_id'] = $row["store_id"];
			$uberArray['session_id'] = $row["session_id"];
			$uberArray['navigation_type'] = $row["navigation_type"];
			$uberArray['postal_code'] = $row["postal_code"];
			$uberArray['direct_invite'] = $row["direct_invite"];

			$uberArray['order']['order_user_notes'] = $row["order_user_notes"];
			$uberArray['order']['order_admin_notes'] = $row["order_admin_notes"];
			$uberArray['order']['direct_order_discount'] = $row["direct_order_discount"];
			$uberArray['order']['points_discount_total'] = $row["points_discount_total"];
			$uberArray['order']['misc_food_subtotal'] = $row["misc_food_subtotal"];
			$uberArray['order']['misc_food_subtotal_desc'] = $row["misc_food_subtotal_desc"];
			$uberArray['order']['misc_nonfood_subtotal'] = $row["misc_nonfood_subtotal"];
			$uberArray['order']['misc_nonfood_subtotal_desc'] = $row["misc_nonfood_subtotal_desc"];
			$uberArray['order']['subtotal_service_fee'] = $row["subtotal_service_fee"];
			$uberArray['order']['service_fee_description'] = $row["service_fee_description"];
			$uberArray['order']['ltd_round_up_value'] = $row["ltd_round_up_value"];
			$uberArray['order']['bundle_id'] = $row["bundle_id"];
			$uberArray['order']['coupon_code_id'] = $row["coupon_code_id"];
			$uberArray['order']['promo_code_id'] = $row["promo_code_id"];
			$uberArray['order']['volume_discount_id'] = $row["volume_discount_id"];
			$uberArray['order']['is_sampler'] = $row["is_sampler"];
			$uberArray['order']['is_TODD'] = $row["is_TODD"];
			$uberArray['hasAddedStoreCredit'] = $row["has_added_store_credit"];
		}

		$uberArray['payments'] = $this->retrieveAllPayments($data_ref);

		return $uberArray;
	}

	static function contentRowExists($data_ref)
	{
		$_db = mysqli_connect(DB_CART_SERVER, DB_CART_SERVER_USERNAME, DB_CART_SERVER_PASSWORD);
		mysqli_select_db($_db, DB_CART_DATABASE);
		$result = mysqli_query($_db, "select id from dreamcart.cart_contents where id = $data_ref");
		if (!$result)
		{
			return false;
		}

		if (mysqli_num_rows($result) <= 0)
		{
			return false;
		}

		return true;
	}

	function clearAllArraysOfType($data_ref, $arrType, $alsoDeleteArrays = false)
	{
		$EmptyArray = array();
		$dataStr = base64_encode(serialize($EmptyArray));

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		if ($alsoDeleteArrays)
		{
			$result = mysqli_query($this->_db, "delete from dreamcart.cart_content_arrays where cart_contents_id = $data_ref and data_type = '$arrType'");
			$this->checkResult($result, false);
		}
		else
		{
			$result = mysqli_query($this->_db, "update dreamcart.cart_content_arrays set data = '$dataStr' where cart_contents_id = $data_ref and data_type = '$arrType'");
			$this->checkResult($result);
		}
	}

	function clearAllPayments($data_ref)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "delete from dreamcart.cart_contents_payment  where cart_contents_id = $data_ref");
		$this->checkResult($result);
	}

	/**
	 *
	 * Given a OrderAddress Obj convert to array and store
	 *
	 * @param       $data_ref
	 * @param       $orderShipping
	 * @param false $menu_id
	 */
	public function storeOrderAddress($data_ref, $orderAddress, $menu_id = false)
	{
		$shippingArray = array();

		$shippingArray['shipping_phone_number'] = $orderAddress->telephone_1;
		$shippingArray['shipping_firstname'] = $orderAddress->firstname;
		$shippingArray['shipping_lastname'] = $orderAddress->lastname;
		$shippingArray['shipping_address_line1'] = $orderAddress->address_line1;
		$shippingArray['shipping_address_line2'] = $orderAddress->address_line2;
		$shippingArray['shipping_city'] = $orderAddress->city;
		$shippingArray['shipping_state_id'] = $orderAddress->state_id;
		$shippingArray['shipping_postal_code'] = $orderAddress->postal_code;
		$shippingArray['shipping_address_note'] = $orderAddress->address_note;
		$shippingArray['shipping_is_gift'] = $orderAddress->is_gift;
		$shippingArray['shipping_gift_email_address'] = $orderAddress->email_address;

		$this->storeArray($data_ref, $shippingArray, 'SHIPPING_ADDRESS', $menu_id);
	}

	function storeArray($data_ref, $arr, $arr_type, $menu_id = false)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$dataStr = base64_encode(serialize($arr));

		$menu_test_clause = "";
		if ($menu_id)
		{
			$menu_test_clause = " and menu_id = $menu_id";
		}

		$testQueryResult = mysqli_query($this->_db, "select id from dreamcart.cart_content_arrays where data_type = '$arr_type' and cart_contents_id = $data_ref $menu_test_clause");

		$array_exists = true;
		if (!$testQueryResult)
		{
			$array_exists = false;
		}
		else if (mysqli_num_rows($testQueryResult) <= 0)
		{
			$array_exists = false;
		}

		if ($array_exists)
		{
			$menu_id_clause = "";
			if ($menu_id)
			{
				$menu_id_clause = " and menu_id = $menu_id";
			}

			$result = mysqli_query($this->_db, "update dreamcart.cart_content_arrays set data = '$dataStr' where cart_contents_id = $data_ref and data_type = '$arr_type' $menu_id_clause");
			$this->checkResult($result);
		}
		else
		{
			$menu_id_clause = "null";
			if ($menu_id)
			{
				$menu_id_clause = "$menu_id";
			}

			$result = mysqli_query($this->_db, "insert into dreamcart.cart_content_arrays (data, menu_id, data_type, cart_contents_id, timestamp_created ) values ('$dataStr', $menu_id_clause, '$arr_type', $data_ref, now())");
			$this->checkResult($result);
		}
	}

	/*
	 *  Used by empty cart to maintain Gift Card entries while clearing all food order data
	 */
	function clearOrderData($data_ref)
	{
		CLog::Assert(!empty($data_ref) && is_numeric($data_ref), "Invalid data_ref in storeOrder");

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$szInsert = "update dreamcart.cart_contents set state = 'INITIALIZED', store_id = null, session_id = null, menu_id = null, order_user_notes = null,
		order_admin_notes = null, direct_order_discount = 0, points_discount_total = 0,
		misc_food_subtotal = 0, misc_food_subtotal_desc = null, misc_nonfood_subtotal = 0,
		misc_nonfood_subtotal_desc = null, subtotal_service_fee = 0, service_fee_description = null,
		ltd_round_up_value = null, bundle_id = null, coupon_code_id = null, promo_code_id = 0, volume_discount_id = null, has_added_store_credit = 0,
		is_sampler = 0, is_TODD = 0, order_customization = null where id = $data_ref";

		$result = mysqli_query($this->_db, $szInsert);
		$this->checkResult($result);
	}

	function storeOrder($data_ref, $orderObj, $menu_id)
	{
		CLog::Assert(!empty($data_ref) && is_numeric($data_ref), "Invalid data_ref in storeOrder");

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$sessionClause = "session_id = null";
		if (isset($orderObj->findSession()->id))
		{
			$sessionClause = "session_id = {$orderObj->findSession()->id}";
		}

		$storeClause = "store_id = null";
		if (!empty($orderObj->store_id) && is_numeric($orderObj->store_id))
		{
			$storeClause = "store_id = {$orderObj->store_id} ";
		}

		$menuClause = "";
		if (!empty($menu_id) && is_numeric($menu_id))
		{
			$menuClause = "menu_id = $menu_id, ";
		}

		$round_up_clause = "ltd_round_up_value = null";
		if (isset($orderObj->ltd_round_up_value) && is_numeric($orderObj->ltd_round_up_value))
		{
			$round_up_clause = "ltd_round_up_value = {$orderObj->ltd_round_up_value}";
		}

		$delivery_tip_clause = "delivery_tip = null";
		if (isset($orderObj->delivery_tip) && is_numeric($orderObj->delivery_tip))
		{
			$delivery_tip_clause = "delivery_tip = {$orderObj->delivery_tip}";
		}

		$bundle_clause = "bundle_id = null";
		if (!empty($orderObj->bundle_id) && is_numeric($orderObj->bundle_id))
		{
			$bundle_clause = "bundle_id = {$orderObj->bundle_id}";
		}

		$coupon_clause = "coupon_code_id = null";
		if (!empty($orderObj->coupon_code_id) && is_numeric($orderObj->coupon_code_id))
		{
			$coupon_clause = "coupon_code_id = {$orderObj->coupon_code_id}";
		}

		$points_clause = "points_discount_total = 0";
		if (!empty($orderObj->points_discount_total) && is_numeric($orderObj->points_discount_total))
		{
			$points_clause = "points_discount_total = {$orderObj->points_discount_total}";
		}

		$direct_clause = "direct_order_discount = 0";
		if (!empty($orderObj->direct_order_discount) && is_numeric($orderObj->direct_order_discount))
		{
			$direct_clause = "direct_order_discount = {$orderObj->direct_order_discount}";
		}

		$service_fee_clause = "subtotal_service_fee = 0";
		if (!empty($orderObj->subtotal_service_fee) && is_numeric($orderObj->subtotal_service_fee))
		{
			$service_fee_clause = "subtotal_service_fee = {$orderObj->subtotal_service_fee}";
		}

		$promo_clause = "promo_code_id = null";
		if (!empty($orderObj->promo_code_id) && is_numeric($orderObj->promo_code_id))
		{
			$promo_clause = "promo_code_id = {$orderObj->promo_code_id}";
		}

		$volume_clause = "volume_discount_id = null";
		if (!empty($orderObj->volume_discount_id) && is_numeric($orderObj->volume_discount_id))
		{
			$volume_clause = "volume_discount_id = {$orderObj->volume_discount_id}";
		}

		if (!empty($orderObj->order_user_notes))
		{
			$orderObj->order_user_notes = addslashes($orderObj->order_user_notes);
		}


		$SamplerValue = 0;
		if ($orderObj->is_sampler)
		{
			$SamplerValue = 1;
		}

		$TODD_Value = 0;
		if ($orderObj->is_TODD)
		{
			$SamplerValue = 1;
		}

		$customization = $orderObj->order_customization;

		$szInsert = "update dreamcart.cart_contents set state = 'CURRENT', 
			$storeClause, 
			$sessionClause,
			$menuClause 
			order_user_notes = '{$orderObj->order_user_notes}',
			order_admin_notes = '{$orderObj->order_admin_notes}', 
			$direct_clause, 
			$points_clause,
			$service_fee_clause,
			service_fee_description = '{$orderObj->service_fee_description}',
			$round_up_clause, 
			$delivery_tip_clause,
			$bundle_clause, 
			$coupon_clause, 
			$promo_clause, 
			$volume_clause,
			is_sampler = $SamplerValue,
			is_TODD = $TODD_Value, 
			total_customized_meal_count = {$orderObj->total_customized_meal_count},
			subtotal_meal_customization_fee = {$orderObj->subtotal_meal_customization_fee}, 
			order_customization = ?
			where id = $data_ref";

		//$result = mysqli_query($this->_db, $szInsert);

		//Prepared statement to escape special characters
		$stmt = mysqli_prepare($this->_db, $szInsert);
		mysqli_stmt_bind_param($stmt, "s", $customization);
		$result = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);


		$this->checkResult($result);
	}

	function testEncryption($testData, $id)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$InSerialized = serialize($testData);
		$InBase64 = base64_encode($InSerialized);
		$InCrypto = CCrypto::encode($InBase64);

		$result = mysqli_query($this->_db, "Call dreamcart.store_temp_payment_data('$InCrypto', $id)");

		$result = mysqli_query($this->_db, "SET @DATA = ''");
		$this->checkResult($result);
		$result = mysqli_query($this->_db, "Call dreamcart.get_temp_payment_data($id, @DATA)");
		$this->checkResult($result);
		$result = mysqli_query($this->_db, "SELECT @DATA");
		$this->checkResult($result);

		if ($row = mysqli_fetch_assoc($result))
		{
			$OutInitial = $row['@DATA'];
			$OutBase64 = CCrypto::decode($OutInitial);
			$OutSerial = base64_decode($OutBase64);
			$OutOriginal = unserialize($OutSerial);

			CLog::RecordNew(CLog::DEBUG, "Test of Encryption Out: " . $OutOriginal);
		}
	}

	function storePayment($data_ref, $payment_id, $payment_type, $amount, &$paymentArr, $temp_data)
	{

		CLog::Assert(!empty($data_ref) && is_numeric($data_ref), "Invalid data_ref in storePayment");

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		// figure out if this is new or existing
		$isNew = false;
		if (empty($payment_id))
		{
			$isNew = true;
		}
		else
		{
			$result = mysqli_query($this->_db, "select id from dreamcart.cart_contents_payment where cart_contents_id = $data_ref and id = $payment_id");
			if (!$result || $result->num_rows == 0)
			{
				$isNew = true;
			}
		}

		$dataStr = base64_encode(serialize($paymentArr));

		if ($isNew)
		{

			$result = mysqli_query($this->_db, "insert into dreamcart.cart_contents_payment (cart_contents_id, payment_type, payment_data, amount,
											timestamp_created) values ($data_ref, '$payment_type', '$dataStr', $amount, now())");
			$this->checkResult($result);

			$result = mysqli_query($this->_db, "select LAST_INSERT_ID() as last_id");
			$row = mysqli_fetch_assoc($result);
			$payment_id = $row['last_id'];
		}
		else
		{
			$result = mysqli_query($this->_db, "update dreamcart.cart_contents_payment set payment_data = '$dataStr', amount = $amount where cart_contents_id = $data_ref
											and id = $payment_id");
			$this->checkResult($result);
		}

		if (!empty($temp_data))
		{

			//	$this->testEncryption($temp_data, $payment_id);

			$message = CCrypto::encode(base64_encode(serialize($temp_data)));

			$result = mysqli_query($this->_db, "Call dreamcart.store_temp_payment_data('$message', $payment_id)");
			$this->checkResult($result);
		}
		$paymentArr['payment_record_id'] = $paymentArr['payment_id'];
		$paymentArr['payment_id'] = $payment_id;
	}

	function retrievePayment($data_ref, $payment_id)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "select payment_type, amount, data from dreamcart.cart_contents_payment where cart_contents_id = $data_ref and id = $payment_id");
		$this->checkResult($result);

		$type = false;
		$amount = 0;
		$data = array();
		$tempData = array();

		if ($row = mysqli_fetch_assoc($result))
		{
			$type = $row["payment_type"];
			$amount = $row["amount"];
			$data = unserialize(base64_decode($row["data"]));
		}

		$result = mysqli_query($this->_db, "Call get_temp_payment_data($data_ref, $payment_id, 'tempdata')");
		$this->checkResult($result);

		if ($row = mysqli_fetch_assoc($result))
		{
			$tempData = unserialize(base64_decode(CCrypto::decode($row["tempdata"])));
		}

		return array(
			$type,
			$amount,
			$data,
			$tempData
		);
	}

	function retrieveTempData($id)
	{
		$result = mysqli_query($this->_db, "SET @DATA = ''");
		$this->checkResult($result);
		$result = mysqli_query($this->_db, "Call dreamcart.get_temp_payment_data($id, @DATA)");
		$this->checkResult($result);
		$result = mysqli_query($this->_db, "SELECT @DATA");
		$this->checkResult($result);

		$row = mysqli_fetch_assoc($result);

		return unserialize(base64_decode(CCrypto::decode($row["@DATA"])));
	}

	function retrieveAllPayments($data_ref)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$retVal = array();
		//$_PaymentArray[$Arr->payment_id] = array('paymentType' => $Arr['payment_type'], 'paymentData' => $Arr, "amount" => $Arr['amount'], 'tempData' => $temp_data);
		$result = mysqli_query($this->_db, "select id, payment_type, amount, payment_data, if(isnull(temp_data), 0, 1) as hasTempData from dreamcart.cart_contents_payment where cart_contents_id = $data_ref");
		$this->checkResult($result, false);

		while ($row = mysqli_fetch_assoc($result))
		{
			$ID = $row["id"];
			$paymentype = $row["payment_type"];
			$amount = $row["amount"];
			$data = unserialize(base64_decode($row["payment_data"]));
			$hasTempData = $row["hasTempData"];

			$tempData = array();
			if ($hasTempData)
			{
				$tempData = $this->retrieveTempData($ID);
			}

			$retVal[$ID] = array(
				'payment_type' => $paymentype,
				'amount' => $amount,
				'paymentData' => $data,
				'tempData' => $tempData
			);
		}

		return $retVal;
	}

	function retrieveOrderObject($data_ref)
	{

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$DAO_orders = DAO_CFactory::create('orders', true);

		$result = mysqli_query($this->_db, "select * from dreamcart.cart_contents where id = $data_ref");
		$this->checkResult($result, false);

		$hasAddedStoreCredit = false;

		if ($result && $row = mysqli_fetch_assoc($result))
		{
			$menu_id = $row["menu_id"];
			$session_id = $row["session_id"];
			$navigation_type = $row['navigation_type'];
			$postal_code = $row['postal_code'];
			$direct_invite = $row['direct_invite'];
			$edit_order_id = $row['edit_order_id'];

			if (!empty($navigation_type) && $navigation_type == CTemplate::DELIVERED)
			{
				$DAO_orders = new COrdersDelivered();
			}
			else
			{
				$DAO_orders = DAO_CFactory::create('orders');
			}

			$DAO_orders->setMenuId($row["menu_id"]);
			$DAO_orders->user_id = $row["user_id"];
			if(is_null($DAO_orders->user_id )){
				$DAO_orders->user_id = CUser::getCurrentUser()->id;
			}
			$DAO_orders->store_id = $row["store_id"];
			$DAO_orders->order_user_notes = $row["order_user_notes"];
			$DAO_orders->order_admin_notes = $row["order_admin_notes"];
			$DAO_orders->direct_order_discount = $row["direct_order_discount"];
			$DAO_orders->points_discount_total = $row["points_discount_total"];
			$DAO_orders->misc_food_subtotal = $row["misc_food_subtotal"];
			$DAO_orders->misc_food_subtotal_desc = $row["misc_food_subtotal_desc"];
			$DAO_orders->misc_nonfood_subtotal = $row["misc_nonfood_subtotal"];
			$DAO_orders->misc_nonfood_subtotal_desc = $row["misc_nonfood_subtotal_desc"];
			$DAO_orders->subtotal_service_fee = $row["subtotal_service_fee"];
			$DAO_orders->service_fee_description = $row["service_fee_description"];
			$DAO_orders->ltd_round_up_value = $row["ltd_round_up_value"];
			$DAO_orders->delivery_tip = $row["delivery_tip"];
			$DAO_orders->bundle_id = $row["bundle_id"];
			$DAO_orders->coupon_code_id = $row["coupon_code_id"];
			$DAO_orders->promo_code_id = $row["promo_code_id"];
			$DAO_orders->volume_discount_id = $row["volume_discount_id"];
			$DAO_orders->is_sampler = $row["is_sampler"];
			$DAO_orders->is_TODD = $row["is_TODD"];
			$hasAddedStoreCredit = $row["has_added_store_credit"];
			$DAO_orders->opted_to_bring_bags = $row["opted_to_bring_bags"];
			$DAO_orders->opted_to_customize_recipes = $row["opted_to_customize_recipes"];
			$order_customizations = $row["order_customization"] ;
			if(empty($order_customizations)){
				$order_customizations =  CUser::getCurrentUser()->getOrderCustomizationPreferencesAsJson();
			}
			$DAO_orders->order_customization = $order_customizations;
			$DAO_orders->subtotal_meal_customization_fee = $row["subtotal_meal_customization_fee"];
			$DAO_orders->total_customized_meal_count = $row["total_customized_meal_count"];
//			if($tempOrder->opted_to_customize_recipes){
//				$tempOrder->subtotal_meal_customization_fee = 0.00;
//				$tempOrder->total_customized_meal_count = 0;
//			}
		}

		// occurs when constructing card programmatically for testing
		if (!isset($menu_id))
		{
			$menu_id = false;
		}
		if (!isset($session_id))
		{
			$session_id = false;
		}
		if (!isset($session_id))
		{
			$session_id = false;
		}
		if (!isset($edit_order_id))
		{
			$edit_order_id = false;
		}

		return array(
			$DAO_orders,
			$menu_id,
			$session_id,
			$navigation_type,
			$direct_invite,
			$hasAddedStoreCredit,
			$postal_code,
			$edit_order_id
		);
	}

	function retrieveArray($data_ref, $arrType, $menu_id = false)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$menu_id_clause = "";
		if ($menu_id)
		{
			$menu_id_clause = " and menu_id = $menu_id";
		}
		$result = mysqli_query($this->_db, "select menu_id, data from dreamcart.cart_content_arrays where cart_contents_id = $data_ref and data_type = '$arrType'
								 $menu_id_clause");
		$this->checkResult($result);

		if (mysqli_num_rows($result) <= 0)
		{
			return null;
		}

		$row = mysqli_fetch_assoc($result);

		$menu_id = $row["menu_id"];
		$data = unserialize(base64_decode($row["data"]));

		return $data;
	}

	function storeOrderValue($data_ref, $value_name, $value, $sub_add = false)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		if ($value_name == "order_user_notes")
		{
			$value = addslashes($value);
		}

		if (is_bool($value) && !$value)
		{
			return;
		}

		if (is_null($value))
		{
			$result = mysqli_query($this->_db, "update dreamcart.cart_contents set $value_name = null where id = $data_ref");
		}
		else if (!empty($sub_add))
		{
			if ($sub_add == 'add')
			{
				$result = mysqli_query($this->_db, "update dreamcart.cart_contents set $value_name = $value_name + $value where id = $data_ref");
			}
			else // if 'sub'
			{
				$result = mysqli_query($this->_db, "update dreamcart.cart_contents set $value_name = $value_name - $value where id = $data_ref");
			}
		}
		else
		{
			//$result = mysqli_query($this->_db, "update dreamcart.cart_contents set $value_name = '$value' where id = $data_ref");

			$query = "update dreamcart.cart_contents set $value_name = ? where id = ?";
			$stmt = mysqli_prepare($this->_db, $query);
			mysqli_stmt_bind_param($stmt, "si", $value, $data_ref);
			$result = mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		}
		$this->checkResult($result);
	}

	function clear($data_ref)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "delete from dreamcart.cart_content_arrays where cart_contents_id = $data_ref");
		$this->checkResult($result, false);

		$result = mysqli_query($this->_db, "delete from dreamcart.cart_contents_payment where cart_contents_id = $data_ref");
		$this->checkResult($result, false);

		$result = mysqli_query($this->_db, "delete from dreamcart.cart_contents where id = $data_ref");
		$this->checkResult($result, false);
	}

	function deleteStoreCredits($data_ref)
	{
		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "delete from dreamcart.cart_contents_payment where cart_contents_id = $data_ref and payment_type = 'store_credit'");
		$this->checkResult($result, false);
	}

	function removePayment($paymentID)
	{

		$this->connect();
		if (!$this->_db)
		{
			$this->handleConnectionIssue();
		}

		$result = mysqli_query($this->_db, "delete from dreamcart.cart_contents_payment where id = $paymentID");
		$this->checkResult($result, false);
	}
}

?>