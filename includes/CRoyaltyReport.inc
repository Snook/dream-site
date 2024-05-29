<?php
require_once("includes/DAO/BusinessObject/COrders.php");

class CRoyaltyReport
{
	static public $MARKETING_SET_FEE = 800.00; // TO BE QUERRIED IN THE DB SOON

	static public $MARKETING_FEE_PERCENTAGE = .02; // TO BE QUERRIED IN THE DB SOON
	static public $ROYALTY_FEE_PERCENTAGE = .06; // TO BE QUERRIED IN THE DB SOON
	static public $SALESFORCE_MARKETING_FEE = 250; // TO BE QUERRIED IN THE DB SOON

	static public $PERFORMANCE_STANDARD_CURRENT_VALUE = 30000;

	function getMarketingFee()
	{
		return self::$MARKETING_SET_FEE;
	}

	static function findHomeOfficeIDAlias($store_id = null)
	{
		$arr = array();
		$obj = DAO_CFactory::create("store_closure_history");
		if (!empty($store_id))
		{
			$obj->store_id = $store_id;
		}
		$obj->orderBy('store_id, recorded_grand_opening_date asc');
		$obj->find();
		while ($obj->fetch())
		{
			$arr[$obj->store_id][] = $obj->toArray();
		} // while

		return $arr;
	}

	static function getDoorDashRevenueByTimeSpan($startDate, $interval, $store_id)
	{
		$DoorDashEntries = new DAO();
		$DoorDashEntries->query("SELECT d.store_id, SUM(d.subtotal) as doordashRev FROM door_dash_orders_and_payouts d
									 where d.store_id = $store_id and d.timestamp_local_date >= '$startDate' and d.timestamp_local_date < DATE_ADD('$startDate', INTERVAL $interval) and d.is_deleted = 0
									 and d.final_order_status in ('Delivered', 'Picked up')
									 GROUP BY d.store_id");
		$DoorDashEntries->fetch();
		if (empty($DoorDashEntries->doordashRev))
		{
			$DoorDashEntries->doordashRev = 0;
		}

		return $DoorDashEntries->doordashRev;
	}

	static function getDoorDashFeesByTimeSpan($startDate, $interval, $store_id)
	{
		$DoorDashEntries = new DAO();
		$DoorDashEntries->query("SELECT d.store_id, SUM(d.commission) as doordashFees FROM door_dash_orders_and_payouts d
									 where d.store_id = $store_id and d.timestamp_local_date >= '$startDate' and d.timestamp_local_date < DATE_ADD('$startDate', INTERVAL $interval) and d.is_deleted = 0
									 and d.final_order_status in ('Delivered', 'Picked up')
									 GROUP BY d.store_id");
		$DoorDashEntries->fetch();
		if (empty($DoorDashEntries->doordashFees))
		{
			$DoorDashEntries->doordashFees = 0;
		}

		return $DoorDashEntries->doordashFees;
	}

	static function getDoorDashAGRByTimeSpan($startDate, $interval, $store_id)
	{
		$DoorDashEntries = new DAO();
		$DoorDashEntries->query("SELECT d.store_id, SUM(d.subtotal) as doordashRev, SUM(d.commision) as doordashFees FROM door_dash_orders_and_payouts d
									 where d.store_id = $store_id and d.timestamp_local_date >= '$startDate' and d.timestamp_local_date < DATE_ADD('$startDate', INTERVAL $interval) and d.is_deleted = 0
									 and d.final_order_status in ('Delivered', 'Picked up')
									 GROUP BY d.store_id");
		$DoorDashEntries->fetch();
		if (empty($DoorDashEntries->doordashRev))
		{
			$DoorDashEntries->doordashRev = 0;
		}

		return $DoorDashEntries->doordashRev - $DoorDashEntries->doordashFees;
	}

	static function getDeliveryFeeByCalendarMonth($month, $year, $store_id)
	{
		$obj = new DAO();
		$obj->query("select SUM(o.subtotal_delivery_fee) as subtotal_delivery_fee from session s
					join booking b on b.session_id = s.id and b.status = 'ACTIVE'
					join orders o on o.id = b.order_id
					where s.is_deleted = 0 and s.store_id = $store_id and YEAR(s.session_start) = $year and MONTH(s.session_start) = $month");
		$obj->fetch();

		return $obj->subtotal_delivery_fee;
	}

	static function findPerformanceExceptions($startDate, $interval, $store_id = null, $storeList = false)
	{
		CLog::Assert(empty($store_id) || empty ($storeList), "Cannot provide both a store and a store list");

		$DAO_performance_royalty_override = DAO_CFactory::create("performance_royalty_override", true);
		if (!empty($store_id))
		{
			$DAO_performance_royalty_override->store_id = $store_id;
		}
		else if (!empty($storeList))
		{
			$DAO_performance_royalty_override->whereAdd("performance_royalty_override.store_id in ($storeList)");
		}
		$DAO_performance_royalty_override->whereAdd("( '$startDate' >= performance_royalty_override.performance_start_date AND DATE_SUB(DATE_ADD('" . $startDate . "', INTERVAL " . $interval . "), INTERVAL 1 SECOND) <= performance_royalty_override.performance_end_date)");
		$DAO_performance_royalty_override->orderBy('performance_royalty_override.performance_start_date');
		$DAO_performance_royalty_override->find();

		$royaltyOverrideArray = array();
		while ($DAO_performance_royalty_override->fetch())
		{
			$royaltyOverrideArray[$DAO_performance_royalty_override->store_id] = $DAO_performance_royalty_override->toArray();
		}

		return $royaltyOverrideArray;
	}

	static function calculateMarketingFee($order_total)
	{
		$varmarketingTotal = $order_total * self::$MARKETING_FEE_PERCENTAGE; // to be changed
		$rndMarketingTotal = COrders::std_round($varmarketingTotal);
		if ($rndMarketingTotal < self::$MARKETING_SET_FEE)
		{
			$rndMarketingTotal = self::$MARKETING_SET_FEE;
		}

		return $rndMarketingTotal;
	}

	static function calculateRoyaltyFee(&$rows, $store_id, $performanceOverride, $order_total, $reporttimedate, &$performanceAppliedValue = 0, $grandopeningdate = null)
	{

		$varroyalty = 0;
		$rndRoyalty = 0;

		/*
		 $performanceAppliedValue:

		 3 = override applied
		 2 = performance std in effect - min 1800 (30,000 * 12%) applied
		 1 = performance std in effect - sales above minimum
		 0 = performance std not in effect - < 1 year from grand opening

		 Yes: If Total Sales after Discounts/Adjustments: > $30,000, then Actual Sales after Discounts/Adjustments X 6% = royalty fee owed
				 If Total Sales after Discounts/Adjustments: =< $30,000, then $30,000 x 6% = $1,800 royalty fee owed

		 No: Total Sales after Discounts/Adjustments X 6% = royalty fee owed
	 */

		//	$grandopeningdate = 0;
		if ($grandopeningdate != null)
		{

			list($yr, $mo, $da) = explode('-', $grandopeningdate);
			$da = explode(' ', $da);
			$da = $da[0];

			$grandts = mktime(0, 0, 0, $mo, $da, $yr);
			$nowts = $reporttimedate;
			$timevar = $nowts - $grandts;
			$secondsinyear = 31556926;

			if ($timevar <= $secondsinyear)
			{
				$grandopeningdate = 0;
			}
		}

		if ($performanceOverride == true)
		{
			$performanceAppliedValue = 3;
			$varroyalty = $order_total * self::$ROYALTY_FEE_PERCENTAGE; // to be changed
			$rndRoyalty = COrders::std_round($varroyalty);
		}
		else
		{
			if ($grandopeningdate > 0)
			{
				if ($order_total > self::$PERFORMANCE_STANDARD_CURRENT_VALUE)
				{
					// Std in effect sales > 30,000
					$performanceAppliedValue = 1;
					$varroyalty = $order_total * self::$ROYALTY_FEE_PERCENTAGE; // to be changed
					$rndRoyalty = COrders::std_round($varroyalty);
				}
				else
				{
					// Std in effect sales < 30k
					$performanceAppliedValue = 2;
					$varroyalty = self::$PERFORMANCE_STANDARD_CURRENT_VALUE * self::$ROYALTY_FEE_PERCENTAGE; // to be changed
					$rndRoyalty = COrders::std_round($varroyalty);
				}
			}
			else
			{
				// no Std - store < 1 year old
				$performanceAppliedValue = 0;
				$varroyalty = $order_total * self::$ROYALTY_FEE_PERCENTAGE; // to be changed
				$rndRoyalty = COrders::std_round($varroyalty);
			}
		}

		return $rndRoyalty;
	}

	function calculateRoyalty($array_entity, $report_type_to_run, $index = 0, $add_store_name = false)
	{
		CLog::Assert(false, "calculateRoyalty() is not expected to be called");

		/*
		$arr = $array_entity[$index];

		if (!$arr || $arr['orders_total'] == "" || !isset($arr['orders_total'])) {
			return NULL;
		}

		if ($add_store_name == TRUE && $arr['store_name'] == NULL) {
		 return NULL;
		}

		$royalty_arr = array();
		$subtotal = $arr['orders_total'];
		$sales_taxes = $arr['sales_taxes'];
		$food_taxes = $arr['food_sales_taxes'];
		$subtotalMinusTaxes = $subtotal - ($sales_taxes+$food_taxes);

		if ($subtotalMinusTaxes == 0 || $subtotalMinusTaxes == "") {
			$royalty_arr[0]['orders_total'] = 0.00;
			$royalty_arr[0]['marketing_total'] = 0.00;

			$royalty_arr[0]['royalty'] = 0.00;
			$royalty_arr[0]['total_fees'] = 0.00;
			if ($add_store_name == TRUE) {
				 $royalty_arr[0]['store_name'] = $arr['store_name'];
			}

			return;
		}
		$standard = 0;
		$rndRoyalty = CRoyaltyReport::calculateRoyaltyFee($rows,$royalty_arr[0]['id'], $subtotalMinusTaxes, $standard, $grand_opening_date);
		$rndMarketingTotal = CRoyaltyReport::calculateMarketingFee($subtotalMinusTaxes);

		$royalty_arr[0]['performance_standard'] = $standard;
		$royalty_arr[0]['orders_total'] = $subtotalMinusTaxes;
		$royalty_arr[0]['marketing_total'] = $rndMarketingTotal;
		$royalty_arr[0]['royalty'] = $rndRoyalty;
		$royalty_arr[0]['total_fees'] = $rndRoyalty + $rndMarketingTotal;
		if ($add_store_name == TRUE) {
				 $royalty_arr[0]['store_name'] = $arr['store_name'];
			}

		return $royalty_arr;

		*/

		return null;
	}

	function calculateTotal($store_id, $Day, $Month, $Year, $interval)
	{
		$PUBLISH_SESSIONS_STATE = 'SAVED';
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$additionalstr = "";
		$finalStr = "";
		$varstr = 'Select Sum(orders.grand_total) as orders_total,
       		Sum(orders.subtotal_sales_taxes) AS sales_taxes, Sum(orders.subtotal_food_sales_taxes) AS food_sales_taxes, 
			Sum(orders.subtotal_all_items) AS subtotal_all_items, Sum(orders.subtotal_all_taxes) AS subtotal_all_taxes, 
			Sum(orders.direct_order_discount) AS subtotal_direct_order_discount, Sum(orders.promo_code_discount_total) AS subtotal_promo_code_discount_total,
			Sum(orders.subtotal_products) AS subtotal_products, Sum(orders.subtotal_menu_items) as subtotal_menu_items 
			From booking 
				Inner Join session ON session.id = booking.session_id 
				Inner Join orders ON booking.order_id = orders.id ' . " 
				where session.session_publish_state != '" . $PUBLISH_SESSIONS_STATE . "' ";

		$additionalstr = " and session.is_deleted = 0 and booking.is_deleted = 0 and booking.STATUS = 'ACTIVE' and session.store_id = " . $store_id . " AND ";
		$finalStr = " session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")";
		$sql = $varstr . $additionalstr . $finalStr;

		$booking = DAO_CFactory::create("booking");
		$booking->query($sql);
		$rows = array();
		$booking->fetch();
		$rows [0] = $booking->toArray(); // only fetch the first row

		return ($rows);
	}

	function calculateAllStoresRoyalty($Day, $Month, $Year, $interval, $storeFilter = false)
	{
		if (!$storeFilter)
		{
			$storeFilter = '';
		}
		else
		{
			$storeFilter = " and store.id in ($storeFilter) ";
		}

		$PUBLISH_SESSIONS_STATE = 'SAVED';
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$additionalstr = "";
		$finalStr = "";
		$varstr = 'Select grand_opening_date, Sum(orders.grand_total) as grand_total, Sum(orders.grand_total) as orders_total, 
			SUM(ifnull(orders.subtotal_ltd_menu_item_value,0)) as ltd_menu_item_value, SUM(orders.fundraiser_value) as fundraiser_total, 
			SUM(ifnull(orders.ltd_round_up_value,0)) as ltd_round_up_value, SUM(ifnull(orders.subtotal_delivery_fee,0)) as subtotal_delivery_fee, 
			SUM(ifnull(orders.delivery_tip,0)) as delivery_tip, SUM(ifnull(orders.subtotal_bag_fee,0)) as subtotal_bag_fee, store.store_name, 
			store.id,store.home_office_id, store.city, store.state_id, store.gp_account_id, Sum(orders.subtotal_all_taxes) AS sales_tax,
			Sum(orders.subtotal_all_items) AS subtotal_all_items, Sum(orders.subtotal_all_taxes) AS subtotal_all_taxes, Sum(orders.direct_order_discount) AS subtotal_direct_order_discount, 
			Sum(orders.promo_code_discount_total) AS subtotal_promo_code_discount_total, Sum(orders.subtotal_products) AS subtotal_products, 
			Sum(orders.subtotal_menu_items) as subtotal_menu_items, grand_opening_date 
			From booking 
				Inner Join session ON session.id = booking.session_id 
				Inner Join orders ON booking.order_id = orders.id 
				Inner Join store ON orders.store_id = store.id ' . $storeFilter . " 
				where session.session_publish_state != '" . $PUBLISH_SESSIONS_STATE . "' ";

		$additionalstr = " and session.is_deleted = 0 and booking.is_deleted = 0 and booking.STATUS = 'ACTIVE' and ";

		$finalStr = " session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")";

		$ordergroupby = " Group By orders.store_id Order By store.state_id, store.city, store.store_name Asc ";

		$sql = $varstr . $additionalstr . $finalStr . $ordergroupby;

		$booking = DAO_CFactory::create("booking");
		$booking->query($sql);
		$rows = array();
		$count = 0;

		while ($booking->fetch())
		{

			$ProductOrderMemebershipFeeRevenue = CDreamReport::getMembershipFeeRevenue($booking->id, $Day, $Month, $Year, $interval);
			$booking->grand_total += $ProductOrderMemebershipFeeRevenue;
			$booking->orders_total += $ProductOrderMemebershipFeeRevenue;
			$DoorDashRevenue = self::getDoorDashRevenueByTimeSpan($Year . "-" . $Month . "-" . $Day, $interval, $booking->id);
			$booking->grand_total += $DoorDashRevenue;
			$booking->orders_total += $DoorDashRevenue;

			$rows [$count++] = $booking->toArray();
		}

		return ($rows);
	}

}

?>