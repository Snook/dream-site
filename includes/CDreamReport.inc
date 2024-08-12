<?php

require_once('DAO/Session.php');
require_once('CRoyaltyReport.inc');
require_once('DAO/BusinessObject/CTimezones.php');
require_once('DAO/BusinessObject/CPayment.php');
require_once('DAO/BusinessObject/CMenuItem.php');

class CDreamReport
{
	const SCRIP = 'SCRIP';
	const DONATED = 'DONATED';
	const STANDARD = 'STANDARD';
	const VOUCHER = 'VOUCHER';

	const SHOWLEGACY = 0;
	const SHOWNEWPLAN = 1;

	static public $SCRIP_PERCENTAGE_SALE = .12; // should be a constant

	static function getOrderInfoByMonth($storeid, $Day, $Month, $Year, $interval, &$rows, $legacy, $partialPeriod = false)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$sqlstr = "Select COUNT(promo_code_id) as promos, SUM(orders.menu_items_total_count) as dinners_sold, Sum(orders.subtotal_all_taxes) as sales_tax, 
			SUM(family_savings_discount) as family_savings_discount, SUM(grand_total) as grand_total, count(distinct booking.user_id) as distinct_users, 
			(sum(orders.session_discount_total) + sum(ifnull(orders.coupon_code_discount_total, 0)) + sum(ifnull(orders.promo_code_discount_total, 0)) + sum(orders.user_preferred_discount_total) +
					sum(orders.direct_order_discount) + sum(orders.dream_rewards_discount) + sum(orders.points_discount_total) + sum(orders.volume_discount_total) + sum(orders.bundle_discount)) as total_discounts, 
					 count(distinct session.id) as total_sessions, " . " count(booking.user_id) as standard_guests, SUM(subtotal_home_store_markup) as markup_total, SUM(fundraiser_value) as fundraising_total, 
					 SUM(ifnull(subtotal_ltd_menu_item_value,0)) as ltd_menu_item_value, 
		SUM(ifnull(ltd_round_up_value,0)) as ltd_round_up_value, SUM(ifnull(subtotal_delivery_fee,0)) as subtotal_delivery_fee, SUM(ifnull(delivery_tip,0)) as delivery_tip, SUM(ifnull(subtotal_bag_fee,0)) as subtotal_bag_fee, ";

		if ($legacy == 0)
		{
			$sqlstr .= "count(IF(orders.menu_items_total_count<=11, 1, NULL))as quicksix,count(IF(orders.menu_items_total_count>11, 1, NULL)) as standard";
		}
		else
		{
			$sqlstr .= "0 as quicksix,count(orders.id) as standard";
		}

		$sqlstr .= " From session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Where " . "session.store_id = '" . $storeid . "' AND " . 'booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and ' . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")";

		if ($partialPeriod)
		{
			$sqlstr .= " and orders.timestamp_created < '$partialPeriod' ";
		}

		$user = DAO_CFactory::create("session");
		$user->query($sqlstr);
		while ($user->fetch())
		{
			$arr = $user->toArray();
			$rows['distinct_users'] = $arr['distinct_users'];
			$rows['order_quicksix'] = $arr['quicksix'];
			$rows['order_standard'] = $arr['standard'];
			$rows['weekid'] = 0;
			$rows['family_savings_discount'] = $arr['family_savings_discount'];
			$rows['grand_total'] = (float)$arr['grand_total'];
			$rows['promos_used'] = $arr['promos'];
			$rows['total_sales'] = (float)$arr['grand_total'];
			$rows['dinners_sold'] = $arr['dinners_sold'];
			$rows['total_sessions'] = $arr['total_sessions'];
			$rows['sales_tax'] = $arr['sales_tax'];
			$rows['standard_guests'] = $arr['standard_guests'];
			$rows['mark_up'] = $arr['markup_total'];
			$rows['total_discounts'] = $arr['total_discounts'];
			$rows['fundraising_total'] = $arr['fundraising_total'];
			$rows['ltd_round_up_value'] = $arr['ltd_round_up_value'];
			$rows['ltd_menu_item_value'] = $arr['ltd_menu_item_value'];
			$rows['subtotal_delivery_fee'] = $arr['subtotal_delivery_fee'];
			$rows['delivery_tip'] = $arr['delivery_tip'];
			$rows['subtotal_bag_fee'] = $arr['subtotal_bag_fee'];
		}
	}

	static function getEntreeTypeCounts($storeid, $Day, $Month, $Year, $interval, &$rows, $groupBy = 'WEEK')
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$sqlstr = "";

		if ($groupBy == 'WEEK')
		{
			$sqlstr = "Select WEEK(session_start) as week_value, ";
		}
		else if ($groupBy == 'DAY')
		{
			$sqlstr = "SELECT Concat(MONTH(session.session_start),'-',DAY(session.session_start),'-',YEAR(session.session_start)) AS datestamp,";
		}

		$sqlstr .= "SUM(order_item.item_count) as sumval, menu_item.pricing_type";

		$sqlstr .= " from session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id 
		 Inner Join order_item ON orders.id = order_item.order_id 
		 Inner Join menu_item ON order_item.menu_item_id = menu_item.id 
		 Where session.store_id = '" . $storeid . "' AND " . 'order_item.is_deleted = 0 and booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and ' . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") and menu_item.is_bundle = 0 and menu_item.is_side_dish = 0 and menu_item.is_menu_addon = 0 and menu_item.is_chef_touched = 0 and menu_item.is_kids_choice = 0";
		if ($groupBy == 'WEEK')
		{
			$sqlstr .= " group by week_value, pricing_type order by week_value";
		}
		else if ($groupBy == 'DAY')
		{
			$sqlstr .= " group by datestamp, pricing_type order by datestamp";
		}

		$user = DAO_CFactory::create("session");
		$user->query($sqlstr);

		while ($user->fetch())
		{
			if ($groupBy == 'WEEK')
			{
				$rows[$user->week_value][$user->pricing_type] = $user->sumval;
			}
			else if ($groupBy == 'DAY')
			{
				$rows[$user->datestamp][$user->pricing_type] = $user->sumval;
			}
		}
	}

	static function getOrderInfoByWeek($storeid, $Day, $Month, $Year, $interval, &$rows, $legacy, $menu_counts = null)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		if ($legacy == CDreamReport::SHOWLEGACY)
		{
			$sqlstr = "Select count(distinct booking.user_id) as distinct_users, count(distinct session.id) as total_sessions, COUNT(promo_code_id) as promos, 
			SUM(orders.menu_items_total_count) as dinners_sold, WEEK(session.session_start, 0) AS `week`,sum(grand_total) as total_sales, 
			 count(booking.user_id)as standard_guests,count(IF(orders.menu_items_total_count<=11, 1, NULL))as quicksix,count(IF(orders.menu_items_total_count>11, 1, NULL)) as standard 
			 From session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Where " . "session.store_id = '" . $storeid . "' AND " . 'booking.status = "ACTIVE" and orders.is_deleted = 0 and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and ' . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" . " GROUP BY week Order BY week";
		}
		else
		{
			$sqlstr = "Select count(distinct booking.user_id) as distinct_users , count(distinct session.id) as total_sessions, COUNT(promo_code_id) as promos, 0 as quicksix, SUM(orders.menu_items_total_count) as dinners_sold, WEEK(session.session_start, 0) AS `week`,sum(grand_total) as total_sales, " . " count(booking.user_id)as standard_guests, count(orders.id) as standard " . "From session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Where " . "session.store_id = '" . $storeid . "' AND " . 'booking.status = "ACTIVE" and orders.is_deleted = 0 and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and ' . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" . " GROUP BY week Order BY week";
		}
		$user = DAO_CFactory::create("session");
		$user->query($sqlstr);

		while ($user->fetch())
		{
			$arr = $user->toArray();
			$week = $arr['week'];
			if (array_key_exists($week, $rows))
			{

				$rows[$week][CMenuItem::HALF] = 0;
				$rows[$week][CMenuItem::FULL] = 0;
				$rows[$week][CMenuItem::INTRO] = 0;

				if (isset($menu_counts[$week][CMenuItem::HALF]))
				{
					$rows[$week][CMenuItem::HALF] += $menu_counts[$week][CMenuItem::HALF];
				}
				if (isset($menu_counts[$week][CMenuItem::FULL]))
				{
					$rows[$week][CMenuItem::FULL] += $menu_counts[$week][CMenuItem::FULL];
				}
				if (isset($menu_counts[$week][CMenuItem::LEGACY]))
				{
					$rows[$week][CMenuItem::FULL] += $menu_counts[$week][CMenuItem::LEGACY];
				}
				if (isset($menu_counts[$week][CMenuItem::INTRO]))
				{
					$rows[$week][CMenuItem::INTRO] += $menu_counts[$week][CMenuItem::INTRO];
				}

				$rows[$week]['total_sessions'] = $arr['total_sessions'];
				$rows[$week]['order_standard'] = $arr['standard'];
				$rows[$week]['promos_used'] = $arr['promos'];

				$rows[$week]['order_quicksix'] = $arr['quicksix'];

				$rows[$week]['distinct_users'] = $arr['distinct_users'];

				$rows[$week]['total_sales'] = $arr['total_sales'];

				$rows[$week]['dinners_sold'] = $arr['dinners_sold'];
				$rows[$week]['standard_guests'] = $arr['standard_guests'];

				$rows[$week]['avg_cust_half'] = 0;
				$rows[$week]['avg_cust_full'] = 0;
			}
		}
	}

	static function getOrderInfoByDayMonthYear($storeid, $Day, $Month, $Year, $interval, &$rows, &$total_sales, &$total_sales_tax)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$sqlstr = "Select count(IF(pricing_type='" . CMenuItem::FULL . "', 1, NULL))as FULL, count(IF(pricing_type='" . CMenuItem::LEGACY . "', 1, NULL))as LEGACY, count(IF(pricing_type='" . CMenuItem::HALF . "', 1, NULL))as HALF,SUM(family_savings_discount) as family_savings_discount, Sum(session_discount_total) as session_discount_total, Count(distinct session.id) as sessions, WEEK(session.session_start, 0) AS `week`,DAYNAME(session.session_start) AS nameofday, " . "MONTH(session.session_start) AS monthgrp,DAY(session.session_start) AS daygrp,YEAR(session.session_start) AS yeargrp,Concat(MONTH(session.session_start),'-',DAY(session.session_start),'-',YEAR(session.session_start)) AS datestamp, " . "Sum(orders.subtotal_menu_items) as menu_items_subtotal,Sum(orders.menu_items_total_count) as menu_items_total_count,Sum(orders.direct_order_discount) as direct_order, Sum(orders.dream_rewards_discount) as dream_rewards_discount," . "Sum(orders.subtotal_all_taxes) as sales_tax,count(orders.promo_code_id) as promos_issued, Sum(orders.volume_discount_total) as volume_discount_total, Sum(orders.promo_code_discount_total) as promo_code,Sum(orders.subtotal_home_store_markup) as home_store,Sum(orders.subtotal_premium_markup) as premium, " . "Sum(grand_total) as grand, Sum(orders.user_preferred_discount_total) as preferred_user, Sum(orders.subtotal_products) as subtotal_products, Sum(orders.misc_food_subtotal) as misc_food_subtotal, Sum(orders.misc_nonfood_subtotal) as misc_nonfood_subtotal, Sum(orders.subtotal_service_fee) as subtotal_service_fee, " . " count(IF(orders.menu_items_total_count<=11, 1, NULL))as quicksix,count(IF(orders.menu_items_total_count>11, 1, NULL)) as standard, count(booking.user_id)as standard_guests " . "From session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Left Join promo_code ON promo_code.id = orders.promo_code_id Left Join menu_item ON menu_item.id = promo_code.promo_menu_item_id " . "Where session.store_id = '" . $storeid . "' AND " . 'booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and ' . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" . " GROUP BY datestamp Order BY yeargrp, monthgrp,daygrp";

		// note: added subtotal_all_taxes and not subtotal_sales_food_taxes -- future when selling products
		$user = DAO_CFactory::create("session");
		$user->query($sqlstr);

		while ($user->fetch())
		{
			$arr = $user->toArray();
			//$ts = strtotime($arr['datestamp']);
			$ts = $arr['datestamp'];
			if (array_key_exists($ts, $rows))
			{

				$premium = isset($arr['premium']) ? $arr['premium'] : 0;
				$directorder = isset($arr['direct_order']) ? $arr['direct_order'] : 0;
				$prefUser = isset($arr['preferred_user']) ? $arr['preferred_user'] : 0;
				$promoDisc = isset($arr['promo_code']) ? $arr['promo_code'] : 0;
				$volumediscounttotal = isset($arr['volume_discount_total']) ? $arr['volume_discount_total'] : 0;
				$sessiondiscounttotal = isset($arr['session_discount_total']) ? $arr['session_discount_total'] : 0;
				$dream_rewards_discount = isset($arr['dream_rewards_discount']) ? $arr['dream_rewards_discount'] : 0;

				$homestore = isset($arr['home_store']) ? $arr['home_store'] : 0;
				$family_savings_discount = isset($arr['family_savings_discount']) ? $arr['family_savings_discount'] : 0;
				$rows[$ts]['half_item_promos'] = $arr[CMenuItem::HALF]; // take half
				$rows[$ts]['full_item_promos'] = $arr[CMenuItem::FULL] + $arr[CMenuItem::LEGACY];

				$rows[$ts]['sales_tax'] = $arr['sales_tax'];

				// TODO
				// $session discounts
				// QUICKSIX will be going away
				// ALSO NEED TO TRACK INTROS

				$rows[$ts]['menu_items_subtotal'] = $arr['menu_items_subtotal'];
				$rows[$ts]['menu_items_total_count'] = $arr['menu_items_total_count'];
				$rows[$ts]['direct_order'] = $directorder;
				$rows[$ts]['sales_tax'] = $arr['sales_tax'];
				$rows[$ts]['promo_code'] = $promoDisc;
				$rows[$ts]['home_store'] = $homestore;
				$rows[$ts]['premium'] = $premium;
				$rows[$ts]['promos_issued'] = $arr['promos_issued'];
				$rows[$ts]['preferred_user'] = $prefUser;
				$rows[$ts]['order_quicksix'] = $arr['quicksix'];
				$rows[$ts]['order_standard'] = $arr['standard'];
				$rows[$ts]['family_savings_discount'] = $family_savings_discount;
				$rows[$ts]['volume_discount_total'] = $volumediscounttotal;
				$rows[$ts]['dream_rewards_discount'] = $dream_rewards_discount;
				$rows[$ts]['session_discount_total'] = $sessiondiscounttotal;
				$rows[$ts]['misc_food_subtotal'] = $arr['misc_food_subtotal'];
				$rows[$ts]['misc_nonfood_subtotal'] = $arr['misc_nonfood_subtotal'];
				$rows[$ts]['subtotal_products'] = $arr['subtotal_products'];
				$rows[$ts]['subtotal_service_fee'] = $arr['subtotal_service_fee'];
				$rows[$ts]['grand_total'] = $arr['grand'];
				$rows[$ts]['session_count'] = $arr['sessions'];
				$rows[$ts]['standard_guests'] = $arr['standard_guests'];
				$rows[$ts]['all_discounts'] = $directorder + $prefUser + $promoDisc + $family_savings_discount + $volumediscounttotal + $sessiondiscounttotal + $dream_rewards_discount;
				$rows[$ts]['all_markups'] = $homestore + $premium;
				$total_sales_tax += $arr['sales_tax'];
				$total_sales += $arr['grand'];
			}
		}
	}

	// *******************************************
	static function generateDayArray($startDateTS, $endDateTS)
	{
		$rows = array();
		$run = true;

		$endDateTS -= 86400;
		$currentDateTS = $startDateTS;

		$daysinmonth = date("t", $currentDateTS);
		$year = date("Y", $currentDateTS);
		$month = date("m", $currentDateTS);

		for ($i = 0; $i < $daysinmonth; $i++)
		{
			$ivar = $i + 1;

			$dayvar = $month . '-' . $ivar . '-' . $year;

			$currentDateTS = mktime(0, 0, 0, $month, $ivar, $year);

			$curMonthDays = date("t", $currentDateTS);
			$yearWeek = date("W", $currentDateTS);

			$nameofday = date("l", $currentDateTS);
			if ($nameofday == "Sunday")
			{
				$yearWeek++;
			}

			$curDateDay = date("j", $currentDateTS);
			$actualDay = date("n-j-Y", $currentDateTS);

			$rows[$actualDay] = array(
				'span' => 'session',
				'dayofmonth' => $curDateDay,
				'nameofday' => $nameofday,
				'weekid' => $yearWeek,
				'date' => $actualDay
			);
		}

		return $rows;
	}

	// *******************************************
	static function nextMonthSignUps($storeid, $Day, $Month, $Year, $interval, &$rows, $byWeek = false)
	{

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		if ($byWeek)
		{

			$sqlstr2 = "Select WEEK(s.session_start) as week,
							o.store_id as store_id,
						 	count(DISTINCT o.id) as total_orders,
							max(s.menu_id) as menu_id,
						 	count( if (boo.order_id, if (boo.order_id <> o.id, 1 , null), null ) ) as in_store_orders
						 From orders o
							Inner Join booking b ON b.order_id = o.id
							Inner Join session s ON b.session_id = s.id
							left JOIN (select o2.id as order_id, o2.user_id as user_id, o2.timestamp_created as order_date from orders o2
										where o2.timestamp_created >= '$current_date_sql' and
										o2.timestamp_created < DATE_ADD(DATE_ADD('$current_date_sql', INTERVAL $interval), INTERVAL 7 DAY) and
										o2.in_store_order = 1 ) as boo
									on boo.user_id = o.user_id and
									DATE(boo.order_date) >= DATE(s.session_start) and
									DATE(boo.order_date) < DATE_ADD(DATE(s.session_start), INTERVAL 7 DAY) and
									boo.order_id <> o.id
						Where
							b.status = 'ACTIVE' and
							b.is_deleted = 0 AND
							s.session_start >= '$current_date_sql' AND
							s.session_start <= DATE_ADD('$current_date_sql', INTERVAL $interval)and
							o.servings_total_count > 35 and
							o.is_TODD = 0 and o.store_id = $storeid
							GROUP BY WEEK(s.session_start), o.store_id";

			$object2 = DAO_CFactory::create("session");
			$object2->query($sqlstr2);

			while ($object2->fetch())
			{

				$arr = $object2->toArray();
				if (array_key_exists($arr['week'], $rows))
				{
					$rows[$arr['week']]['total_forecasted'] = $arr['total_orders'];
					$rows[$arr['week']]['in_store'] = $arr['in_store_orders'];
				}
				else
				{
					// do we need to do anything else here?
					$rows[$arr['week']]['total_forecasted'] = $arr['total_orders'];
					$rows[$arr['week']]['in_store'] = $arr['in_store_orders'];
				}
			}
		}
		else
		{


			$sqlstr = "Select DAY(s.session_start) as day,
							o.store_id as store_id,
						 	count(DISTINCT o.id) as total_orders,
							max(s.menu_id) as menu_id,
						 	count( if (boo.order_id, if (boo.order_id <> o.id, 1 , null), null ) ) as in_store_orders,
						 	Concat(MONTH(s.session_start),'-',DAY(s.session_start),'-',YEAR(s.session_start)) AS datestamp
						 From orders o
							Inner Join booking b ON b.order_id = o.id
							Inner Join session s ON b.session_id = s.id
							left JOIN (select o2.id as order_id, o2.user_id as user_id, o2.timestamp_created as order_date from orders o2
										where o2.timestamp_created >= '$current_date_sql' and
										o2.timestamp_created < DATE_ADD(DATE_ADD('$current_date_sql', INTERVAL $interval), INTERVAL 7 DAY) and
										o2.in_store_order = 1 ) as boo
									on boo.user_id = o.user_id and
									DATE(boo.order_date) >= DATE(s.session_start) and
									DATE(boo.order_date) < DATE_ADD(DATE(s.session_start), INTERVAL 7 DAY) and
									boo.order_id <> o.id
						Where
							b.status = 'ACTIVE' and
							b.is_deleted = 0 AND
							s.session_start >= '$current_date_sql' AND
							s.session_start <= DATE_ADD('$current_date_sql', INTERVAL $interval)and
							o.servings_total_count > 35 and
							o.is_TODD = 0 and o.store_id = $storeid
							GROUP BY DAY(s.session_start), o.store_id";

			$object = DAO_CFactory::create("session");
			$object->query($sqlstr);
			while ($object->fetch())
			{
				$arr = $object->toArray();

				if ($byWeek == false)
				{
					$ts = $arr['datestamp'];
					if (array_key_exists($ts, $rows))
					{
						$rows[$ts]['in_store'] = $arr['in_store'];
					}
					else
					{
						$rows[$ts]['in_store'] = $arr['in_store'];
					}
				}
			}
			/*

		$sqlstr2 = "Select WEEK(session.session_start,0) as week, COUNT(orders.id) as total_orders, MONTH(session.session_start) AS
			monthgrp,DAY(session.session_start) AS daygrp,YEAR(session.session_start) AS
			yeargrp,Concat(MONTH(session.session_start),'-',DAY(session.session_start),'-',YEAR(session.session_start)) AS datestamp " .
			"From session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Where " ;

		$sqlstr2 .= " session.store_id = '" . $storeid . "' AND orders.servings_total_count > 35 AND orders.is_TODD = 0 AND " .
			'booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and ' .
			" session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" ;

			if ($byWeek == false)
				$sqlstr2 .= " GROUP BY datestamp Order BY yeargrp, monthgrp,daygrp" ;
			else
				$sqlstr2 .= " group by week order by week" ;


			$object2 = DAO_CFactory::create("session");
			$object2->query($sqlstr2);

			while ($object2->fetch()) {
				$arr= $object2->toArray();

				if ($byWeek == false) {
					$ts = $arr['datestamp'];
					if (array_key_exists($ts, $rows)) {
						$rows[$ts]['total_forecasted'] = $arr['total_orders'];
					}
					else {
						// do we need to do anything else here?
						$rows[$ts]['total_forecasted'] = $arr['total_orders'];
					}
				}
				else {
					if (array_key_exists($arr['week'], $rows)) {
						$rows[$arr['week']]['total_forecasted'] = $arr['total_orders'];
					}
					else {
						// do we need to do anything else here?
						$rows[$arr['week']]['total_forecasted'] = $arr['total_orders'];
					}
				}

			}
					*/
		}
	}

	static function getMembershipFeeRevenue($store, $day, $month, $year, $duration)
	{

		$current_date = mktime(0, 0, 0, $month, $day, $year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$obj = new DAO();
		$obj->query("select SUM(poi.item_cost) as totes_revs from product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.is_deleted = 0
					where po.timestamp_created >= '$current_date_sql' and po.timestamp_created <= DATE_ADD('$current_date_sql',INTERVAL 
					$duration) and po.store_id = $store and po.is_deleted = 0");
		if ($obj->fetch())
		{
			if (empty($obj->totes_revs))
			{
				$obj->totes_revs = 0;
			}

			return $obj->totes_revs;
		}

		return 0;
	}

	static function getMembershipFeeTaxes($store, $day, $month, $year, $duration)
	{

		$current_date = mktime(0, 0, 0, $month, $day, $year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$obj = new DAO();
		$obj->query("select SUM(po.subtotal_sales_taxes) as totes_revs from product_orders po
					where po.timestamp_created >= '$current_date_sql' and po.timestamp_created <= DATE_ADD('$current_date_sql',INTERVAL 
					$duration) and po.store_id = $store and po.is_deleted = 0");
		if ($obj->fetch())
		{
			if (empty($obj->totes_revs))
			{
				$obj->totes_revs = 0;
			}

			return $obj->totes_revs;
		}

		return 0;
	}

	static function getMembershipFeeRevenueByMenuID($store, $menu_id)
	{

		$menuObj = new DAO();
		$menuObj->query("select global_menu_end_date, global_menu_start_date from menu where id = $menu_id");
		$menuObj->fetch();

		$start_date_sql = $menuObj->global_menu_start_date;
		$end_date_sql = date("Y-m-d 00:00:00", strtotime($menuObj->global_menu_end_date) + 86400);

		$obj = new DAO();
		$obj->query("select SUM(poi.item_cost) as totes_revs from product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.is_deleted = 0
					where po.timestamp_created >= '$start_date_sql' and po.timestamp_created < '$end_date_sql' and po.store_id = $store and po.is_deleted = 0");
		if ($obj->fetch())
		{
			if (empty($obj->totes_revs))
			{
				$obj->totes_revs = 0;
			}

			return $obj->totes_revs;
		}

		return 0;
	}

	static function findDistinctUsersByMonth($storeid, $Day, $Month, $Year, $interval)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$sqlstr = "Select count(distinct booking.user_id ) as booking_count From session ";
		$sqlstr .= 'Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Where session.store_id = ' . $storeid . ' AND booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED"';
		$sqlstr .= " and session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")";
		$object = DAO_CFactory::create("session");
		$object->query($sqlstr);
		$object->fetch();

		return ($object->booking_count);
	}

	static function findActiveMenus($storeid, $Day, $Month, $Year, $interval)
	{
		$menuidarray = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$sqlstr = "Select menu_id, month(session_start) as month_value	From session " . " Where session.store_id = '$storeid' and " . " session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" . " and session.is_deleted = 0 and session_publish_state != 'SAVED' group by month_value, menu_id order by month_value";
		$object = DAO_CFactory::create("session");
		$object->query($sqlstr);
		while ($object->fetch())
		{
			$menuidarray[$object->month_value][] = $object->menu_id;
		}

		return $menuidarray;
	}

	static function nextMonthSignUpsByMonth($storeid, $Day, $Month, $Year, $interval, &$total_signups)
	{

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$intervalClause = "INTERVAL 1 MONTH";

		$monthStartTS = strtotime($current_date);
		$numMonthDays = date("t", $monthStartTS);

		$extendedIntervalClause = "INTERVAL " . ($numMonthDays + 7) . " DAY";

		$endMonthTime = mktime(0, 0, 0, $Month + 1, 1, $Year);
		$now = time();

		if ($now > $monthStartTS && $now <= $endMonthTime)
		{
			// we must shorten the inverval if searching the current month
			$dayNum = date("d", $now);
			$intervalClause = "INTERVAL $dayNum DAY";
			$extendedIntervalClause = "INTERVAL " . ($dayNum + 7) . " DAY";
		}

		$query = "select sum(innerQ.in_store_orders) as in_store_orders, sum(innerQ.total_orders) as total_forecasted
			from
			(Select o.store_id as store_id, count(distinct o.id) as total_orders, max(s.menu_id) as menu_id,
			count( if (boo.order_id, if (boo.order_id <> o.id, 1 , null), null ) ) as in_store_orders From orders o
				Inner Join booking b ON b.order_id = o.id
				Inner Join session s ON b.session_id = s.id
				left JOIN (select o2.id as order_id, o2.user_id as user_id, o2.timestamp_created as order_date from orders o2 where o2.timestamp_created >= '$current_date_sql' and
									o2.timestamp_created < DATE_ADD('$current_date_sql', $extendedIntervalClause) and o2.in_store_order = 1 ) as boo on boo.user_id = o.user_id
									and DATE(boo.order_date) >= DATE(s.session_start) and DATE(boo.order_date) < DATE_ADD(DATE(s.session_start), INTERVAL 7 DAY) and boo.order_id <> o.id
				Where b.status = 'ACTIVE' and b.is_deleted = 0 AND s.session_start >= '$current_date_sql' AND
									s.session_start <= DATE_ADD('$current_date_sql', $intervalClause)and o.servings_total_count > 35 and o.is_TODD = 0 and o.store_id = $storeid
			GROUP BY o.store_id )
 			as innerQ";

		$object = DAO_CFactory::create("session");
		$object->query($query);
		$object->fetch();

		$total_signups = $object->total_forecasted;

		return ($object->in_store_orders);
	}

	// *******************************************
	static function getSessionInfoByMonth($storeid, $Day, $Month, $Year, $interval, &$rows, $legacy)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$sqlstr = "Select count(id) as sessions, ";

		if ($legacy == 0)
		{
			$sqlstr .= "count(session_class = 'STANDARD' OR NULL) as standard,count(session_class = 'QUICKSIX' OR NULL) as quicksix ";
		}
		else
		{
			$sqlstr .= "count(session.id) as standard,0 as quicksix ";
		}

		$sqlstr .= "From session Where session.session_publish_state != 'SAVED' and session.is_deleted = 0 and " . "session.store_id = '" . $storeid . "' AND " . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")";
		$user = DAO_CFactory::create("session");
		$user->query($sqlstr);
		$user->fetch();
		$arr = $user->toArray();
		$rows['session_count'] = $arr['sessions'];
		$rows['session_standard'] = $arr['standard'];
		$rows['session_quicksix'] = $arr['quicksix'];
	}

	static function getSessionInfoByWeek($storeid, $Day, $Month, $Year, $interval, &$rows, $legacy)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		// interval can no longer be greater than a month.
		$daysInMonth = date('t', $current_date);
		$dayEndTime = mktime(0, 0, 0, $Month, $daysInMonth, $Year);
		$endOfMonth = date("Y-m-d 23:59:59", $dayEndTime);

		$sqlGetWeekVals = "select Week('$current_date_sql') as startWeek, Week('$endOfMonth') as endWeek";
		$system = DAO_CFactory::create("_dreamsite_version");
		// TODO create a class that will encapsulate the connection DAO instead of going through this object
		$system->query($sqlGetWeekVals);
		$rslt = $system->fetch();

		if ($rslt == true)
		{
			for ($i = $system->startWeek; $i <= $system->endWeek; $i++)
			{
				$rows[$i]['weekid'] = $i;
			}
		}

		$sqlstr = "Select WEEK(session.session_start, 0) as week, count(id) as sessions, ";
		if ($legacy == 0)
		{
			$sqlstr .= "count(session_class = 'STANDARD' OR NULL) as standard,count(session_class = 'QUICKSIX' OR NULL) as quicksix ";
		}
		else
		{
			$sqlstr .= "count(session.id) as standard, 0 as quicksix ";
		}

		$sqlstr .= "From session WHERE session.session_publish_state != 'SAVED' and session.is_deleted = 0 and " . "session.store_id = '" . $storeid . "' AND " . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" . " group by week order by week";
		$user = DAO_CFactory::create("session");
		$user->query($sqlstr);

		while ($user->fetch())
		{
			$arr = $user->toArray();
			$week = $arr['week'];
			$rows[$week]['weekid'] = $week;
			$rows[$week]['session_count'] = $arr['sessions'];
			$rows[$week]['session_standard'] = $arr['standard'];
			$rows[$week]['session_quicksix'] = $arr['quicksix'];
		}
	}

	static function getSessionInfoByDayMonthYear($storeid, $Day, $Month, $Year, $interval, &$rows)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$sqlstr = "Select DAYOFMONTH(session.session_start) as daymonth,WEEK(session.session_start, 0) as week,DAYNAME(session.session_start) as nameofday,MONTH(session.session_start) AS monthgrp,DAY(session.session_start) AS daygrp,YEAR(session.session_start) AS yeargrp, " . "Concat(MONTH(session.session_start), '-', DAY(session.session_start), '-' ,YEAR(session.session_start) ) as datestamp, " . "count(id) as sessions, " . "count(session_class = 'STANDARD' OR NULL) as standard,count(session_class = 'QUICKSIX' OR NULL) as quicksix " . "From session Where session.session_publish_state != 'SAVED' and session.is_deleted = 0 and " . "session.store_id = '" . $storeid . "' AND " . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" . " group by yeargrp, monthgrp, daygrp";

		$user = DAO_CFactory::create("session");

		$user->query($sqlstr);
		while ($user->fetch())
		{
			$arr = $user->toArray();

			$ts = $arr['datestamp'];
			//$ts = strtotime($arr['datestamp']);
			if (array_key_exists($ts, $rows))
			{

				$rows[$ts]['session_count'] = $arr['sessions'];
				$rows[$ts]['session_standard'] = $arr['standard'];
				$rows[$ts]['session_quicksix'] = $arr['quicksix'];
			}
		}
	}

	//*****************************************************

	static function findDistinctUsers($storeid, $Day, $Month, $Year, $interval, &$total_guests)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$userArray = array();

		$sqlstr = "Select WEEK(session.session_start, 0) as weekid, count(distinct booking.user_id) as user_count " . "From session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Where " . "session.store_id = '" . $storeid . "' AND " . 'booking.status = "ACTIVE" and booking.is_deleted = 0 AND session.is_deleted = 0 and session_publish_state != "SAVED" and ' . "session.session_start >= '" . $current_date_sql . "' AND " . " session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ")" . " group by weekid order by weekid";

		// note: added subtotal_all_taxes and not subtotal_sales_food_taxes -- future when selling products
		$session = DAO_CFactory::create("session");
		$session->query($sqlstr);
		while ($session->fetch())
		{
			$arr = $session->toArray();

			$week = $arr['weekid'];
			$userArray[$week] = $arr['user_count'];
			$total_guests += $arr['user_count'];
		}

		return $userArray;
	}

	static function findGiftCertificatesByWeek($storeid, $Day, $Month, $Year, $interval, &$total_certs_used_array)
	{
		$giftCertsArr = array();
		$total_certs_used_array = array();
		$gifttype = CPayment::GIFT_CERT; // need to locate payment constant for htis
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		//$storeid = 98;
		$varstr = "Select gift_cert_type, count(gift_cert_type) as certs_used,WEEK(session.session_start, 0) AS weekid from ";
		$varstr .= " session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Inner Join payment ON orders.id = payment.order_id ";
		$varstr .= " Where payment_type = '" . $gifttype . "' and session.store_id = '" . $storeid . "' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 ";
		$varstr .= " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") ";
		$varstr .= " group by weekid, gift_cert_type order by weekid,gift_cert_type ";

		$user = DAO_CFactory::create("session");
		$user->query($varstr);

		while ($user->fetch())
		{
			$arr = $user->toArray();
			$week = $arr['weekid'];
			$certtype = $arr['gift_cert_type'];

			$giftCertsArr[$week][$certtype] = $arr['certs_used'];

			if (!isset($total_certs_used_array[$certtype]))
			{
				$total_certs_used_array[$certtype] = $arr['certs_used'];
			}
			else
			{
				$total_certs_used_array[$certtype] += $arr['certs_used'];
			}
		}

		return $giftCertsArr;
	}

	static function giftCertificatesByType($storeid, $Day, $Month, $Year, $interval)
	{
		$giftCertsArr = array();
		$gifttype = CPayment::GIFT_CERT; // need to locate payment constant for htis
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		//$storeid = 98;
		$varstr = "Select gift_cert_type, count(gift_cert_type) ,SUM(payment.total_amount) as gift_cert from ";
		$varstr .= " session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Inner Join payment ON orders.id = payment.order_id and payment.is_deleted = 0 ";
		$varstr .= " Where payment_type = '" . $gifttype . "' and session.store_id = '" . $storeid . "' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 ";
		$varstr .= " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") ";
		$varstr .= " group by gift_cert_type ";
		$user = DAO_CFactory::create("session");
		$user->query($varstr);
		$counter = 0;

		while ($user->fetch())
		{
			$arr = $user->toArray();

			$cert_type = $arr['gift_cert_type'];
			$giftCertsArr[$cert_type]['gift_cert_type'] = $cert_type;
			$giftCertsArr[$cert_type]['gift_cert'] = $arr['gift_cert'];
			if ($cert_type == CPayment::GC_TYPE_SCRIP)
			{
				$giftCertsArr[$cert_type]['remove_sales'] = $arr['gift_cert'] * self::$SCRIP_PERCENTAGE_SALE;
			}
			else if ($cert_type == CPayment::GC_TYPE_DONATED || $cert_type == CPayment::GC_TYPE_VOUCHER)
			{
				$giftCertsArr[$cert_type]['remove_sales'] = $arr['gift_cert'];
			}
		}

		return $giftCertsArr;
	}

	// IAF AND TODD
	static function ProgramDiscounts($storeid, $Day, $Month, $Year, $interval)
	{
		$progArr = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varstr = "SELECT `session`.`id`,`booking`.`id`,`orders`.`id`,`payment`.`id`,`payment`.`payment_type`,`store_credit`.`id`," . "`store_credit`.`store_id`,`payment`.`total_amount` as amount,`store_credit`.`credit_type`,`store_credit`.`is_redeemed`" . " FROM " . "`session` Inner Join `booking` ON `session`.`id` = `booking`.`session_id` " . " Inner Join `orders` ON `booking`.`order_id` = `orders`.`id` " . " Inner Join `payment` ON `orders`.`id` = `payment`.`order_id` and payment.is_deleted = 0 " . " Inner Join `store_credit` ON `payment`.`store_credit_id` = `store_credit`.`id` " . " Where session.store_id = $storeid and payment_type = 'STORE_CREDIT' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 " . " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and ( session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") )" . " and `store_credit`.`is_redeemed` = 1 and (`store_credit`.`credit_type` = 2 OR `store_credit`.`credit_type` = 3)";

		$progArr = array();
		$user = DAO_CFactory::create("session");
		$user->query($varstr);
		$counter = 0;

		$progArr['programdiscounts'] = 0;
		$progArr['programdiscountscount'] = 0;

		while ($user->fetch())
		{
			$arr = $user->toArray();
			$progArr['programdiscounts'] += $arr['amount'];
			$progArr['programdiscountscount'] += 1;
		}

		return $progArr;
	}

	static function ProgramDiscountsGroupByID($Day, $Month, $Year, $interval, $storeList = false)
	{
		$progArr = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$storeFilter = "";
		if ($storeList)
		{
			$storeFilter = " and session.store_id in ($storeList) ";
		}

		$varstr = "SELECT sum(`payment`.`total_amount`) as sumamount, count(store_credit.id) as amountprocessed, orders.store_id " . " FROM " . "`session` Inner Join `booking` ON `session`.`id` = `booking`.`session_id` " . " Inner Join `orders` ON `booking`.`order_id` = `orders`.`id` " . " Inner Join `payment` ON `orders`.`id` = `payment`.`order_id` " . " Inner Join `store_credit` ON `payment`.`store_credit_id` = `store_credit`.`id` " . " Where payment_type = 'STORE_CREDIT' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 " . " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and ( session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") )" . " and `store_credit`.`is_redeemed` = 1 and (`store_credit`.`credit_type` = 2 OR `store_credit`.`credit_type` = 3) " . $storeFilter . " group by orders.store_id ";

		$progArr = array();
		$user = DAO_CFactory::create("session");
		$user->query($varstr);
		$counter = 0;

		while ($user->fetch())
		{
			$arr = $user->toArray();
			$storeid = $arr['store_id'];
			$progArr[$storeid]['programdiscounts'] = $arr['sumamount'];
			$progArr[$storeid]['programdiscountscount'] = $arr['amountprocessed'];
		}

		return $progArr;
	}

	static function giftCertificatesByTypeGroupByStoreID($Day, $Month, $Year, $interval, $storeList = false)
	{
		$storeClause = "";
		if ($storeList)
		{
			$storeClause = " and session.store_id in ($storeList) ";
		}

		$giftCertsArr = array();
		$gifttype = CPayment::GIFT_CERT; // need to locate payment constant for htis
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		//$storeid = 98;
		$varstr = "Select orders.store_id, gift_cert_type, count(gift_cert_type) ,SUM(payment.total_amount) as gift_cert from ";
		$varstr .= " session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Inner Join payment ON orders.id = payment.order_id and payment.is_deleted = 0 ";
		$varstr .= " Where payment_type = '" . $gifttype . "' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 ";
		$varstr .= " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") ";
		$varstr .= $storeClause;
		$varstr .= " group by store_id, gift_cert_type ";
		$user = DAO_CFactory::create("session");
		$user->query($varstr);
		$counter = 0;

		while ($user->fetch())
		{
			$arr = $user->toArray();
			$storeid = $arr['store_id'];
			$cert_type = $arr['gift_cert_type'];
			$giftCertsArr[$storeid][$cert_type]['gift_cert_type'] = $cert_type;
			$giftCertsArr[$storeid][$cert_type]['gift_cert'] = $arr['gift_cert'];
			if ($cert_type == CPayment::GC_TYPE_SCRIP)
			{
				$giftCertsArr[$storeid][$cert_type]['remove_sales'] = $arr['gift_cert'] * self::$SCRIP_PERCENTAGE_SALE;
			}
			else if ($cert_type == CPayment::GC_TYPE_DONATED || $cert_type == CPayment::GC_TYPE_VOUCHER)
			{
				$giftCertsArr[$storeid][$cert_type]['remove_sales'] = $arr['gift_cert'];
			}
		}

		return $giftCertsArr;
	}

	static function findProgramTypesBySession($storeid, $Day, $Month, $Year, $interval)
	{
		$progarr = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		// the other type would be null
		$varstr = "SELECT session.id as session_id, count(`store_credit`.`id`) as amount_used, sum(`payment`.`total_amount`) as amount, " . " CASE when `customer_referral`.`origination_type_code` = 1 THEN 'IAF' " . " WHEN `customer_referral`.`origination_type_code` = 4 then 'TODD' " . " WHEN `customer_referral`.`origination_type_code` = 3 then 'DIRECT' " . " WHEN `customer_referral`.`origination_type_code`= 5 then 'DIRECT' " . " else 'DIRECT' END as ProgramType," .

			" session.session_start " . " FROM " . "`session` Inner Join `booking` ON `session`.`id` = `booking`.`session_id` " . " Inner Join `orders` ON `booking`.`order_id` = `orders`.`id` " . " Inner Join `payment` ON `orders`.`id` = `payment`.`order_id` " . " Inner Join `store_credit` ON `payment`.`store_credit_id` = `store_credit`.`id` " . " left Join `customer_referral` ON `payment`.`store_credit_id` = `customer_referral`.`store_credit_id` " .

			" Where session.store_id = $storeid and payment_type = 'STORE_CREDIT' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 " . " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and ( session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") )" . " and `store_credit`.is_deleted = 0 and `store_credit`.`is_redeemed` = 1 and (`store_credit`.`credit_type` = 2 OR `store_credit`.`credit_type` = 3) group by session.id, ProgramType ";

		$user = DAO_CFactory::create("session");
		$user->query($varstr);

		$counter = 0;
		while ($user->fetch())
		{
			$arr = $user->toArray();

			$sessionid = $arr['session_id'];
			$originationType = $arr['ProgramType'];

			$progarr[$sessionid][$originationType]['amount_used'] = $arr['amount_used'];
			$progarr[$sessionid][$originationType]['amount_spent'] = $arr['amount'];

			$progarr[$sessionid][$originationType]['session_id'] = $arr['session_id'];
		}

		return $progarr;
	}

	static function findProgramTypesBySessionForNation($Day, $Month, $Year, $interval, $isMenuMonth = false, $menuMonthMonthYear = false)
	{
		$progarr = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$groupClause = " group by CONCAT(MONTH(session.session_start), ' ', YEAR(session.session_start)), session.store_id, ProgramType ";

		if ($isMenuMonth)
		{
			$groupClause = " group by session.menu_id, session.store_id, ProgramType ";
		}

		// the other type would be null
		$varstr = "SELECT CONCAT(MONTH(session.session_start), ' ', YEAR(session.session_start)) as month_year, session.store_id, count(`store_credit`.`id`) as amount_used, sum(`payment`.`total_amount`) as amount,
				CASE when `customer_referral`.`origination_type_code` = 1 THEN 'IAF'
				WHEN `customer_referral`.`origination_type_code` = 4 then 'TODD'
				WHEN `customer_referral`.`origination_type_code` = 3 then 'DIRECT'
				WHEN `customer_referral`.`origination_type_code`= 5 then 'DIRECT'
				else 'DIRECT' END as ProgramType
				FROM session
				Inner Join `booking` ON `session`.`id` = `booking`.`session_id`
				Inner Join `orders` ON `booking`.`order_id` = `orders`.`id`
				Inner Join `payment` ON `orders`.`id` = `payment`.`order_id`
				Inner Join `store_credit` ON `payment`.`store_credit_id` = `store_credit`.`id`
				left Join `customer_referral` ON `payment`.`store_credit_id` = `customer_referral`.`store_credit_id`
				Where payment_type = 'STORE_CREDIT' AND booking.status = 'ACTIVE' and booking.is_deleted = 0
				AND session.is_deleted = 0 and session_publish_state != 'SAVED' and ( session.session_start >= '$current_date_sql' AND session.session_start <= DATE_ADD('$current_date_sql', INTERVAL $interval) )
				and `store_credit`.is_deleted = 0 and `store_credit`.`is_redeemed` = 1 and (`store_credit`.`credit_type` = 2 OR `store_credit`.`credit_type` = 3)
				$groupClause ";

		$session = DAO_CFactory::create("session");
		$session->query($varstr);

		$counter = 0;
		while ($session->fetch())
		{
			$arr = $session->toArray();

			if ($isMenuMonth)
			{
				$monthYear = $menuMonthMonthYear;
			}
			else
			{
				$monthYear = $arr['month_year'];
			}

			$originationType = $arr['ProgramType'];

			$progarr[$monthYear][$session->store_id][$originationType]['amount_used'] = $arr['amount_used'];
			$progarr[$monthYear][$session->store_id][$originationType]['amount_spent'] = $arr['amount'];
		}

		return $progarr;
	}

	static function findProgramTypesByDayMonthYear($storeid, $Day, $Month, $Year, $interval)
	{
		$progarr = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varstr = "SELECT session.id as session_id, count(`store_credit`.`id`) as amount_used, sum(`payment`.`total_amount`) as amount, " . " CASE when `customer_referral`.`origination_type_code` = 1 THEN 'IAF' " . " WHEN `customer_referral`.`origination_type_code` = 4 then 'TODD' " . " WHEN `customer_referral`.`origination_type_code` = 3 then 'DIRECT' " . " WHEN `customer_referral`.`origination_type_code` = 5 then 'DIRECT' " . " else 'DIRECT' END as ProgramType," .

			" DAYNAME(session.session_start) as nameofday, MONTH(session.session_start) AS monthgrp,DAY(session.session_start) AS daygrp,YEAR(session.session_start) AS yeargrp, session.session_start, Concat(MONTH(session.session_start),'-',DAY(session.session_start),'-',YEAR(session.session_start)) AS datestamp " . " FROM " . "`session` Inner Join `booking` ON `session`.`id` = `booking`.`session_id` " . " Inner Join `orders` ON `booking`.`order_id` = `orders`.`id` " . " Inner Join `payment` ON `orders`.`id` = `payment`.`order_id` " . " Inner Join `store_credit` ON `payment`.`store_credit_id` = `store_credit`.`id` " . " Left Join `customer_referral` ON `payment`.`store_credit_id` = `customer_referral`.`store_credit_id` " .

			" Where session.store_id = $storeid and payment_type = 'STORE_CREDIT' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 " . " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and ( session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") )" . " and `store_credit`.`is_redeemed` = 1 and (`store_credit`.`credit_type` = 2 OR `store_credit`.`credit_type` = 3) group by yeargrp, monthgrp, daygrp, ProgramType ";

		$user = DAO_CFactory::create("session");
		$user->query($varstr);

		$counter = 0;
		while ($user->fetch())
		{
			$arr = $user->toArray();
			$ts = $arr['datestamp'];

			$originationType = $arr['ProgramType'];

			$progarr[$ts][$originationType]['datestamp'] = $arr['datestamp'];

			$progarr[$ts][$originationType]['amount_used'] = $arr['amount_used'];
			$progarr[$ts][$originationType]['amount_spent'] = $arr['amount'];

			$progarr[$ts][$originationType]['session_id'] = $arr['session_id'];
		}

		return $progarr;
	}

	static function findGiftCertificatesByDayMonthYear($storeid, $Day, $Month, $Year, $interval, &$totalSalesCertficateReduction)
	{
		$giftCertsArr = array();
		$gifttype = CPayment::GIFT_CERT; // need to locate payment constant for htis
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$varstr = "Select gift_cert_type, count(gift_cert_type) as certs_used, DAYNAME(session.session_start) as nameofday,MONTH(session.session_start) AS monthgrp,DAY(session.session_start) AS daygrp,YEAR(session.session_start) AS yeargrp, session.session_start, SUM(payment.total_amount) as gift_cert, Concat(MONTH(session.session_start),'-',DAY(session.session_start),'-',YEAR(session.session_start)) AS datestamp from ";
		$varstr .= " session Inner Join booking ON session.id = booking.session_id Inner Join orders ON booking.order_id = orders.id Inner Join payment ON orders.id = payment.order_id ";
		$varstr .= " Where payment_type = '" . $gifttype . "' and session.store_id = '" . $storeid . "' AND booking.status = 'ACTIVE' and booking.is_deleted = 0 ";
		$varstr .= " AND session.is_deleted = 0 and session_publish_state != 'SAVED' and session.session_start >= '" . $current_date_sql . "' AND session.session_start <= DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") ";
		$varstr .= " group by yeargrp, monthgrp, daygrp, gift_cert_type ";
		$user = DAO_CFactory::create("session");
		$user->query($varstr);

		$counter = 0;
		while ($user->fetch())
		{
			$arr = $user->toArray();
			$ts = $arr['datestamp'];
			$cert_type = $arr['gift_cert_type'];
			$giftCertsArr[$ts][$cert_type]['datestamp'] = $arr['datestamp'];
			$giftCertsArr[$ts][$cert_type]['gift_cert_type'] = $cert_type;
			$giftCertsArr[$ts][$cert_type]['gift_cert'] = $arr['gift_cert'];
			if ($cert_type == CPayment::GC_TYPE_SCRIP)
			{
				$totalSalesCertficateReduction += COrders::std_round($arr['gift_cert'] * self::$SCRIP_PERCENTAGE_SALE);
				$giftCertsArr[$ts][$cert_type]['remove_sales'] = COrders::std_round($arr['gift_cert'] * self::$SCRIP_PERCENTAGE_SALE);
			}
			else if ($cert_type == CPayment::GC_TYPE_DONATED || $cert_type == CPayment::GC_TYPE_VOUCHER)
			{
				$totalSalesCertficateReduction += $arr['gift_cert'];
				$giftCertsArr[$ts][$cert_type]['remove_sales'] = $arr['gift_cert'];
			}

			$giftCertsArr[$ts][$cert_type]['gift_cert'] = $arr['gift_cert'];
			$giftCertsArr[$ts][$cert_type]['certs_used'] = $arr['certs_used'];
		}

		return $giftCertsArr;
	}

	static function addExpenseData($expenseData, &$rows, $promos_type_arr, $proccess_direction, $month, $year)
	{
		$mktimvar = null;
		$mktimvar = mktime(0, 0, 0, $month, 1, $year);
		$employeePrice = CStoreExpenses::getEmployeeMealPrice($mktimvar);

		$element = current($expenseData);
		$numRows = count($expenseData);
		$food_cost = 0;

		$labor_cost = 0;
		for ($i = 0; $i < $numRows; $i++)
		{
			$weekid = $element['week'];
			if (array_key_exists($weekid, $rows))
			{

				if ($element['expense_type'] == CStoreExpenses::SYSCO || $element['expense_type'] == CStoreExpenses::OTHER)
				{

					if (!isset($rows[$weekid]['food_cost']))
					{
						$rows[$weekid]['food_cost'] = $element['total_cost'];
					}
					else
					{
						$rows[$weekid]['food_cost'] += $element['total_cost'];
					}

					// why would this ever be set ? LMH
					//if (!isset($rows[$weekid]['food_units'])) $rows[$weekid]['food_units'] = $element['units'];
					//else $rows[$weekid]['food_units'] += $element['units'];

					//$food_cost = $element['total_cost'];
					//if ($rows[$weekid]['total_sales'] > 0) {
					//$calc = sprintf("%.3f",$element['total_cost']/$rows[$weekid]['total_sales']);
					//$rows[$weekid]['food_avg'] = $calc * 100;
					//}
				}
				else if ($element['expense_type'] == CStoreExpenses::PROMOS || $element['expense_type'] == CStoreExpenses::EMPLOYEE)
				{
					//if (!isset($rows[$weekid]['dinners_sold'])) $rows[$weekid]['dinners_sold'] = $element['units'];
					//else $rows[$weekid]['dinners_sold'] += $element['units'];

					if (!isset($rows[$weekid]['food_cost']))
					{
						$rows[$weekid]['food_cost'] = -$element['total_cost'];
					}
					else
					{
						$rows[$weekid]['food_cost'] -= $element['total_cost'];
					}

					if (!isset($rows[$weekid]['food_units']))
					{
						$rows[$weekid]['food_units'] = $element['units'];
					}
					else
					{
						$rows[$weekid]['food_units'] += $element['units'];
					}
				}
				else if ($element['expense_type'] == CStoreExpenses::LABOR)
				{
					if (!isset($rows[$weekid]['labor_cost']))
					{
						$rows[$weekid]['labor_cost'] = $element['total_cost'];
					}
					else
					{
						$rows[$weekid]['labor_cost'] += $element['total_cost'];
					}

					if (!isset($rows[$weekid]['labor_units']))
					{
						$rows[$weekid]['labor_units'] = $element['units'];
					}
					else
					{
						$rows[$weekid]['labor_units'] += $element['units'];
					}
					//$labor_cost += $element['total_cost'];
					//if ($rows[$weekid]['total_sales'] > 0)
					//$rows[$weekid]['labor_avg'] = sprintf("%.3f", $element['total_cost']/$rows[$weekid]['total_sales']) * 100;
				}

				//if ($rows[$weekid]['total_sales'] > 0) {
				//$rows[$weekid]['food_labor_avg'] = sprintf("%.3f",($food_cost + $labor_cost)/$rows[$weekid]['total_sales']) * 100;
				//}

			}
			$element = next($expenseData);
		}

		$additional_food_cost = 0;
		for ($i = 0; $i < count($promos_type_arr); $i++)
		{
			$id = key($promos_type_arr);
			if ($proccess_direction == 'WEEK')
			{
				if (isset($promos_type_arr[$id][CMenuItem::FULL]))
				{
					if (!isset($rows[$id]['food_cost']))
					{
						$rows[$id]['food_cost'] = 0;
					}

					$rows[$id]['food_cost'] -= $promos_type_arr[$id][CMenuItem::FULL] * $employeePrice;
				}
				if (isset($promos_type_arr[$id][CMenuItem::HALF]))
				{
					if (!isset($rows[$id]['food_cost']))
					{
						$rows[$id]['food_cost'] = 0;
					}

					$rows[$id]['food_cost'] -= ($promos_type_arr[$id][CMenuItem::HALF] * .5) * $employeePrice;
				}
			}
			else if ($proccess_direction == 'MONTH')
			{

				if (isset($promos_type_arr[$id][CMenuItem::FULL]))
				{
					$additional_food_cost += $promos_type_arr[$id][CMenuItem::FULL] * $employeePrice;
				}

				if (isset($promos_type_arr[$id][CMenuItem::HALF]))
				{
					$additional_food_cost += ($promos_type_arr[$id][CMenuItem::HALF] * .5) * $employeePrice;
				}
			}
			$element = next($promos_type_arr);
		}

		if ($proccess_direction == 'MONTH')
		{
			if (empty($rows[0]['food_cost']))
			{
				$rows[0]['food_cost'] = 0;
			}
			$rows[0]['food_cost'] -= $additional_food_cost;
		}

		$element = current($rows);
		$numRows = count($rows);
		for ($i = 0; $i < count($rows); $i++)
		{
			$weekid = $element['weekid'];

			if (isset($rows[$weekid]['total_sales']) && $rows[$weekid]['total_sales'] > 0)
			{
				$laborCost = isset($rows[$weekid]['labor_cost']) ? $rows[$weekid]['labor_cost'] : 0;
				$food_cost = isset($rows[$weekid]['food_cost']) ? $rows[$weekid]['food_cost'] : 0;
				$rows[$weekid]['food_avg'] = sprintf("%.1f", $food_cost / $rows[$weekid]['total_sales'] * 100);
				$rows[$weekid]['labor_avg'] = sprintf("%.1f", $laborCost / $rows[$weekid]['total_sales'] * 100);
				$rows[$weekid]['food_labor_avg'] = sprintf("%.1f", ($food_cost + $laborCost) / $rows[$weekid]['total_sales'] * 100);
			}

			$element = next($rows);
		}
	}

	static function calculateFees(&$rows, $storeid, $hasperformanceOverride, $expenseData, $giftCertValues, $programValues, $fundraising_total, $ltd_meal_total, $deliveryFeeTotal, $deliveryTipTotal, $bagFeeTotal, $DoorDashCommissions, &$marketingFee, &$royaltyFee, $grandopeningdate, $month, $year)
	{
		$grandtotal = isset($rows['grand_total']) ? $rows['grand_total'] : 0;
		$salestax = isset($rows['sales_tax']) ? $rows['sales_tax'] : 0;

		$pretaxAmount = $grandtotal - $salestax;
		$adjustments = 0;
		// TODO.. how do these adjustments affect sales tax?
		if (isset($expenseData[CStoreExpenses::ADJUSTMENTS]))
		{
			$adjustments += $expenseData[CStoreExpenses::ADJUSTMENTS]['total_cost'];
		}

		$pretaxAmount += $adjustments;

		$discounts = 0;

		if (!empty($programValues['programdiscounts']))
		{

			$discounts += $programValues['programdiscounts'];
		}

		if (isset($expenseData[CStoreExpenses::FUNDRAISER]))
		{
			$discounts += $expenseData[CStoreExpenses::FUNDRAISER]['total_cost'];
		}

		// REMOVE ESCRIP FROM ROYALTYIES
		if (isset($expenseData[CStoreExpenses::ESCRIP]))
		{
			$discounts += $expenseData[CStoreExpenses::ESCRIP]['total_cost'];
		}

		if (isset($giftCertValues[CPayment::GC_TYPE_SCRIP]))
		{
			$discounts += $giftCertValues[CPayment::GC_TYPE_SCRIP]['remove_sales'];
		}
		if (isset($giftCertValues[CPayment::GC_TYPE_DONATED]))
		{
			$discounts += $giftCertValues[CPayment::GC_TYPE_DONATED]['remove_sales'];
		}
		if (isset($giftCertValues[CPayment::GC_TYPE_VOUCHER]))
		{
			$discounts += $giftCertValues[CPayment::GC_TYPE_VOUCHER]['remove_sales'];
		}

		$rows['discounts'] = $discounts;

		$pretaxAmount -= $discounts;

		$pretaxAmount -= $fundraising_total;
		$pretaxAmount -= $ltd_meal_total;
		$pretaxAmount -= $deliveryFeeTotal;
		$pretaxAmount -= $deliveryTipTotal;
		//	$pretaxAmount -= $bagFeeTotal;
		//	Note: all though we pass the bag fee subtotal in, it is currently subject to royalties and so not used here
		// we'll leave it if the law or understanding changes.

		$pretaxAmount -= $DoorDashCommissions;

		$rows['total_less_discounts'] = $pretaxAmount;

		$rows['adjustments'] = $adjustments;

		$marketingFee = CRoyaltyReport::calculateMarketingFee($pretaxAmount);
		$standard = 0;

		$reporttimedate = mktime(0, 0, 0, $month, 01, $year);

		$royaltyFee = CRoyaltyReport::calculateRoyaltyFee($rows, $storeid, $hasperformanceOverride, $pretaxAmount, $reporttimedate, $standard, $grandopeningdate);
		$rows['performance_standard'] = $standard;
	}

}

?>