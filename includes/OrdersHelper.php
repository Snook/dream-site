<?php
require_once("DAO/BusinessObject/CStoreActivityLog.php");
class OrdersHelper
{
	private static $instance = null;

	// The constructor is private
	// to prevent initiation with outer code.
	private function __construct()
	{
		// The expensive process (e.g.,db connection) goes here.
	}

	// The object is created from within the class itself
	// only if the class has no instance.
	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new OrdersHelper();
		}

		return self::$instance;
	}

	public function fetchOtherMenuMonthOrders($order_id, $hydrate_session = true)
	{
		$result = array();

		$referenceOrderDAO = DAO_CFactory::create('orders');
		$referenceOrderDAO->query("select o.id, o.user_id, o.store_id, b.status, s.menu_id as current_menu_id
									from orders o, booking b, session s
									where o.id = b.order_id
											and b.session_id = s.id
											and o.is_deleted = 0
											and o.id = {$order_id}");
		$referenceOrderDAO->fetch();

		$otherOrdersDAO = DAO_CFactory::create('orders');
		$query = "select o.id, o.user_id, o.store_id, b.status, s.menu_id, m.menu_name, s.session_start, s.session_type, s.id as cur_session_id
									from orders o, booking b, session s, menu m
									where o.id = b.order_id
											and b.session_id = s.id
											and o.is_deleted = 0
									      	and m.id = s.menu_id
									        and s.menu_id = {$referenceOrderDAO->current_menu_id}
											and b.user_id = {$referenceOrderDAO->user_id}
											and o.id != {$order_id}";
		$otherOrdersDAO->query($query);

		while ($otherOrdersDAO->fetch())
		{
			$obj = clone($otherOrdersDAO);
			if ($hydrate_session)
			{
				$sessionDAO = DAO_CFactory::create('session');
				$sessionDAO->id = $otherOrdersDAO->cur_session_id;
				$sessionDAO->find(true);
				$obj->session_obj = $sessionDAO;
			}

			$result[] = $obj;
		}

		return $result;
	}

	public function hasOtherMenuMonthOrders($order_id)
	{
		if(!empty($order_id))
		{
			$others = $this->fetchOtherMenuMonthOrders($order_id, false);

			if (count($others) > 0)
			{
				return true;
			}
		}

		return false;
	}

	public static function fetchStoreActivity($store_id, $startingDate, $daysBack = 1, $filter = null, $filter_sub_orderType = null)
	{
		$bookingData = array();
		$sessionData = array();
		$menuItemData = array();
		$inventoryData = array();
		$sidesFormData = array();


		//Handle Order/Booking
		if (is_null($filter) || (!in_array("SESSION CREATED", $filter) && !in_array("RECIPE_UPDATED", $filter) && !in_array("INVENTORY", $filter)))
		{
			if (!is_null($filter_sub_orderType))
			{
					$bookingData = self::fetchBookingOrderChangeHistory($store_id, $startingDate, $daysBack, $filter_sub_orderType[0]);
			}
			else
			{
				$bookingData = self::fetchBookingOrderChangeHistory($store_id, $startingDate, $daysBack);
			}
		}

		//Handle S&S Order
		if (is_null($filter) || in_array(CStoreActivityLog::SIDES_ORDER, $filter) || in_array("", $filter))
		{
			$actTypeId = CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::SIDES_ORDER,CStoreActivityLog::SUBTYPE_SIDES_FORM);
			$sidesFormData = CStoreActivityLog::fetchSpecificEventsInTimeframe($store_id, $actTypeId, $startingDate, $daysBack);
		}

		//handle Sessions
		if (is_null($filter) || in_array("SESSION CREATED", $filter) || in_array("", $filter))
		{
			$sessionData = self::fetchSessionChangeHistory($store_id, $startingDate, $daysBack);
		}

		//handle Store Activity Events
		if (is_null($filter) || in_array("INVENTORY", $filter) || in_array("", $filter))
		{
			//fetchSpecificEventsInTimeframe($store_id,$store_activity_type_id, $start, $end){
			$activityTypeId = CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::INVENTORY,CStoreActivityLog::SUBTYPE_LOW);
			$inventoryData = CStoreActivityLog::fetchSpecificEventsInTimeframe($store_id, $activityTypeId, $startingDate, $daysBack);
		}

		//handle Recipe changes
		// On hold for stores until what changed can be displayed
		if (false)
		{
			if (is_null($filter) || in_array("RECIPE_UPDATED", $filter) || in_array("", $filter))
			{
				$menuItemData = CMenuItem::getMenuItemChangeHistory($store_id, $startingDate, $daysBack);
			}
		}

		$activityData = array_merge($bookingData, $sessionData, $menuItemData, $inventoryData, $sidesFormData);

		$activityData = self::filterAndSortOrderActivityByDay($activityData, $startingDate, $store_id, $daysBack, $filter);

		return $activityData;
	}

	private static function fetchBookingOrderChangeHistory($store_id, $startingDate, $daysBack = 1, $order_type = '')
	{
		$retVal = array();

		$dateRangeClause = " and (( b.timestamp_updated >= '$startingDate 00:00:01' and b.timestamp_updated <= '$startingDate 23:59:59') or
								  ( o.timestamp_updated >= '$startingDate 00:00:01' and o.timestamp_updated <= '$startingDate 23:59:59') )";
		if ($daysBack > 1)
		{
			$dateRangeClause = " and (( b.timestamp_updated >= DATE_SUB('$startingDate', INTERVAL $daysBack DAY) and b.timestamp_updated <= '$startingDate 23:59:59') or
									  ( o.timestamp_updated >= DATE_SUB('$startingDate', INTERVAL $daysBack DAY) and o.timestamp_updated <= '$startingDate 23:59:59') )";
		}
		$orderTypeClause = '';
		if ($order_type == 'WEB')
		{
			$orderTypeClause = " and o.order_type = 'WEB'";
		}
		else if ($order_type == 'DIRECT')
		{
			$orderTypeClause = " and o.order_type = 'DIRECT'";
		}

		$bookingObj = DAO_CFactory::create('booking');
		$query = "select distinct b.order_id
									from booking b
									left join session s on s.id = b.session_id and s.is_deleted = 0
									left join orders o on o.id = b.order_id and o.is_deleted = 0
									where b.is_deleted = 0 
									and s.store_id = $store_id 
									$dateRangeClause
									$orderTypeClause
									order by b.id asc";
		$bookingObj->query($query);

		while ($bookingObj->fetch())
		{
			$retVal[] = COrders::getOrderHistory($bookingObj->order_id, false, true);
		}

		$retVal = self::flatten($retVal);

		return $retVal;
	}

	private static function fetchSessionChangeHistory($store_id, $startingDate, $daysBack = 1)
	{
		$retVal = array();

		//Handle Order/Booking
		$dateRangeClause = " and ( s.timestamp_created >= '$startingDate 00:00:01' and s.timestamp_created <= '$startingDate 23:59:59')";
		if ($daysBack > 1)
		{
			$dateRangeClause = " and ( s.timestamp_created >= DATE_SUB('" . $startingDate . "', INTERVAL " . $daysBack . " DAY) and s.timestamp_created <= '$startingDate 23:59:59')";
		}

		$sessionObj = DAO_CFactory::create('session');
		$query = "select distinct s.id, s.session_start, s.timestamp_created, CONCAT(u.firstname, ' ', u.lastname) as creator, u.id as creator_id, u.user_type
									from session s 
									left join user u on  s.created_by = u.id
									where s.is_deleted = 0 
									and (s.session_type_subtype != 'WALK_IN' || s.session_type_subtype is null)
									and s.store_id = $store_id 
									$dateRangeClause
									order by s.id asc";
		$sessionObj->query($query);

		while ($sessionObj->fetch())
		{

			$sessionData = CSession::getSessionDetail($sessionObj->id, false);

			$retVal[] = array(
				'time' => $sessionObj->timestamp_created,
				'action' => "Session Created",
				'user' => $sessionObj->creator,
				'user_id' => $sessionObj->creator_id,
				'user_type' => $sessionObj->user_type,
				'type' => 'SESSION CREATED',
				'session_start' => $sessionObj->session_start,
				'session_id' => $sessionObj->id,
				'session_type' => $sessionData['session_type_text'],
				'session_data' => $sessionData
			);
		}

		//$retVal = self::flatten($retVal);

		return $retVal;
	}

	private static function filterAndSortOrderActivityByDay($activityInput, $laterDate, $store_id, $daysBack = 1, $include = null)
	{
		$DAO_store = DAO_CFactory::create('store');
		$DAO_store->id = $store_id;
		$DAO_store->find(true);

		usort($activityInput, 'history_sort_backwards');

		$activity = array();

		$dateLimit = new DateTime($laterDate);

		//Use the sub function to subtract a DateInterval
		$early_dateDT = $dateLimit->sub(new DateInterval('P' . $daysBack . 'D'));

		//Get yesterday's date in a YYYY-MM-DD format.
		$early_date = $early_dateDT->format('Y-m-d');

		foreach ($activityInput as $item)
		{
			$currentTimestamp = $item['time'];
			$adjustedTimestamp = CTimezones::getAdjustedTime($DAO_store, strtotime($currentTimestamp));
			$currentTimestamp = date('Y-m-d H:i:s', $adjustedTimestamp);

			$date = date_create($currentTimestamp);

			$canInclude = false;
			if (is_null($include) || in_array($item['type'], $include))
			{
				$canInclude = true;
			}
			if ($canInclude && self::in_range($early_date, $laterDate, date_format($date, "Y-m-d")))
			{
				$currentDay = date_format($date, "m/d/Y");
				if (!array_key_exists($currentDay, $activity))
				{
					$activity[$currentDay] = [];
				}
				array_push($activity[$currentDay], $item);
			}
		}

		//krsort($activity);

		return $activity;
	}

	static function in_range($start_date, $end_date, $date_from_user)
	{
		// Convert to timestamp
		$start_ts = strtotime($start_date);
		$end_ts = strtotime($end_date);
		$user_ts = strtotime($date_from_user);

		// Check that user date is between start & end
		return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
	}

	static function flatten(array $array)
	{
		$return = array();
		foreach ($array as $elements)
		{
			foreach ($elements as $element)
			{
				$return[] = $element;
			}
		}

		return $return;
	}

	function history_sort_backwards($a, $b)
	{
		$a_time = strtotime($a['time']);
		$b_time = strtotime($b['time']);

		if ($a_time == $b_time)
		{
			return 0;
		}

		if ($a_time < $b_time)
		{
			return 1;
		}

		return -1;
	}

	//Used to hide assembly fee for everyone starting with April 2023 menu
	public static function allow_assembly_fee($menu_id){
		if(is_null($menu_id)){
			return true;
		}

		if( $menu_id < 260){ //less than April 2023
			return true;
		}
		return false;
	}

}