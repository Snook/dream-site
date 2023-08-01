<?php

namespace Facebook\WebDriver;

//require_once("../../includes/CLog.inc");
require_once("../../includes/DAO/BusinessObject/CMenu.php");
require_once("../../includes/DAO/BusinessObject/COrders.php");

require_once('test_utils.php');

use CLog;
use DAO_CFactory;
use DAO;
use Exception;
use COrders;

class DD_Test_Support
{


	static function setupTestingTables()
	{
		ob_start();
		require_once('testing_tables.sql');
		$DDL = ob_get_clean();

		$queries = explode("\r\n", $DDL);

		$DO = new DAO();

		foreach ($queries as $thisQuery)
		{
			$DO->query($thisQuery);
		}
	}

	static function tearDownTestingTables()
	{
		ob_start();
		require_once('testing_tables_teardown.sql');
		$DDL = ob_get_clean();

		$queries = explode("\r\n", $DDL);

		$DO = new DAO();

		foreach ($queries as $thisQuery)
		{
			$DO->query($thisQuery);
		}
	}

	static function getRandomAccountDetails($partial = false)
	{
		$retVal = array();

		$DO = new DAO();
		$DO->query("select val from test_firstnames order by rand() limit 1");
		$DO->fetch();
		$retVal['firstname'] = $DO->val;
		$DO->query("select val from test_lastnames order by rand() limit 1");
		$DO->fetch();
		$retVal['lastname'] = $DO->val;
		$retVal['primary_email'] = $retVal['firstname'] . "_" . $retVal['lastname'] . "@example.com";
		$retVal['password'] = "tester";

		if (!$partial)
		{
			$DO->query("select CONCAT((select val from test_street_nums order by rand() limit 1), ' ', 
											(select val from test_street_names order by rand() limit 1), ' ',
											(select val from test_street_types order by rand() limit 1), ' ',
											(select val from test_street_dirs order by rand() limit 1));");
			$DO->fetch();
			$retVal['address_line1'] = $DO->val;


		}


		return $retVal;
	}

	static function getStoreWithProperties(
		$hasFoodTax,
		$hasServiceTax)
	{





	}

	static function getPlatePointsRangeForLevel($level)
	{
		switch($level)
		{
			case 'not_enrolled':
				return array(0, 0);
			case 'enrolled':
				return array(0, 1499);
			case 'chef':
				return array(1500, 4999);
			case 'station_chef':
				return array(5000, 9999);
			case 'sous_chef':
				return array(10000, 14999);
			case 'head_chef':
				return array(15000, 19999);
			case 'executive_chef':
				return array(20000, 1000000000);
		}

		return array(0, 1000000000);
	}



	// TODO: unfinished
	static function getUserWithProperties(
		$home_store = 'any', // any or a store_id matched to home_Store
		$plate_points_level = 'any',
		$hasOrders = true,
		$hasOrderForMenu = false, // menu_id
		$would_be_reacquired = false

		)
	{
		$home_store_clause = "";
		if ($home_store != 'any')
		{
			$home_store_clause = " and u.homestore_id = $home_store ";
		}

		$IQ = "select GROUP_CONCAT(s.menu_id) as menus, COUNT(distinct b.id) num_orders, MAX(puh.total_points)as points, u.* from user u 
					left join booking b on b.user_id = u.id and b.status = 'ACTIVE'
					left join session s on s.id = b.session_id
					left join points_user_history puh on puh.user_id = u.id and puh.is_deleted = 0
					where $home_store_clause
					group by u.id";

		$plate_points_clause = "";
		if ($plate_points_level != 'any')
		{
			list ($points_min, $points_max) = self::getPlatePointsRangeForLevel($plate_points_level);
			$plate_points_clause = " and iq.points > $points_min and iq.points < $points_max ";
		}

		$hasOrdersClause = "";
		if ($hasOrders)
		{
			$hasOrdersClause = " and iq.num_orders > 0 ";
		}


		// do it




	}




	static function getSessionWithProperties(
		$store = 'any',
		$sessionType = 'STANDARD',
		$menu = 'current',
		$hasStdSlots = true,
		$hasIntroSlots = false,
		$isFuture = false )//greater than 5 days ahead for testing Delayed Payment
	{
		restore_error_handler();

		$storeClause = "";
		if ($store <> 'any')
		{
			CLog::Assert(is_integer($store), "Bad Store ID");
			$storeClause = " and s.store_id = $store ";
		}

		$typeClause = " and s.session_type = '$sessionType' ";

		$menu_id = false;

		$menuObj = DAO_CFactory::create('menu');
		$menuObj->findCurrent();
		$menuObj->fetch();


		if ($menu == 'current')
		{
			$menu_id = $menuObj->id;
		}
		else if ($menu == 'future')
		{
			$menu_id = $menuObj->id + 1;
		}
		else if ($menu == 'distant')
		{
			$menu_id = $menuObj->id + 2;
		}
		else if (is_integer($menu))
		{
			$menu_id = $menu;
		}

		$futureClause = "";
		if ($isFuture)
		{
			$futureClause = "and DATEDIFF(s.session_start,now()) > 5";
		}

		$availabilityClause = "";
		if ($hasStdSlots)
		{
			$availabilityClause = " where iq.numBooked_standard < iq.available_slots ";
		}

		if ($hasIntroSlots)
		{

			if ($hasStdSlots)
			{
				$availabilityClause .= " and iq.numBooked_intro < iq.introductory_slots and iq.numBooked_intro + iq.numBooked_standard < iq.available_slots ";
			}
			else
			{
				$availabilityClause = " where iq.numBooked_intro < iq.introductory_slots and iq.numBooked_intro + iq.numBooked_standard < iq.available_slots ";
			}


		}

		$searchObj = new DAO();
		$searchObj->query("select rand() * 1000 as selector, iq.* FROM
			(select s.id, s.session_start, s.available_slots, s.introductory_slots
			, COUNT(DISTINCT b.id) as numBooked
			, COUNT(DISTINCT if (b.booking_type = 'STANDARD',  b.id, null)) as numBooked_standard
			, COUNT(DISTINCT if (b.booking_type = 'INTRO',  b.id, null)) as numBooked_intro
			 from session s
			left join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
			where 
			s.menu_id = $menu_id 
			$typeClause
			$storeClause		
			$futureClause
			and s.is_deleted = 0 
			group by s.id
			) as iq
			$availabilityClause
			order by selector desc limit 1");

		$searchObj->fetch();

		$retObj = DAO_CFactory::create('session');
		$retObj->id = $searchObj->id;
		$retObj->find(true);

		return $retObj;
	}


	// no dollar signs allowed but often results are empty string, null or zero.
	static function currencyCompare($a, $b, $fp)
	{
		// if both values are empty strings, null, false, zero or 0.00 then they match
		if (empty($a) && empty($b))
		{
			return true;
		}

		$a_i = (int)(COrders::std_round($a * 100));
		$b_i = (int)(COrders::std_round($b * 100));


		if ($a_i == $b_i)
		{
			return true;
		}

		DD_Selenium_Test_Utils::Trace($a . " <-> " . $b ."<br>\n", $fp);

		return false;
	}



	static function compareOrderFacts($orderID, $clientFacts, $fp = null)
	{

		$OrderObj = DAO_CFactory::create('orders');
		$OrderObj->id = $orderID;
		$OrderObj->find(true);


		DD_Selenium_Test_Utils::$throwOnAssertFailure = true;
		try {

		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['ltd_round_up'], $OrderObj->ltd_round_up_value, $fp), "LTD Round Up does not match database.", $fp);

		// client shows ltd as psrt of grand total so subtracy
		$databaseGrandTotal = $clientFacts['grand_total'] - $clientFacts['ltd_round_up'];
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($databaseGrandTotal, $OrderObj->grand_total, $fp), "Grand Total does not match database.", $fp);

		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['item_count'], $OrderObj->menu_items_total_count, $fp), "Menu item Total does not match database.", $fp);
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['number_servings'], $OrderObj->servings_total_count, $fp), "Servings Count does not match database.", $fp);

		if (empty($OrderObj->bundle_discount)) $OrderObj->bundle_discount = 0;
		$clientMenuTotal = $OrderObj->subtotal_menu_items + $OrderObj->subtotal_home_store_markup - $OrderObj->bundle_discount;
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['menu_subtotal'], $clientMenuTotal, $fp), "Menu Subtotal (with Mark Up) does not match database.", $fp);

		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['preferred_discount'], $OrderObj->user_preferred_discount_total, $fp), "User Preferred Discount Up does not match database.", $fp);
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['direct_discount'], $OrderObj->direct_order_discount, $fp), "Direct Order Discount does not match database.", $fp);

		$totalPlatePointsDiscount = $clientFacts['plate_points_order_discount_food'] + $clientFacts['plate_points_order_discount_fee'];
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($totalPlatePointsDiscount, $OrderObj->points_discount_total, $fp), "PLATEPOINTS Discount does not match database.", $fp);

		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['coupon_discount'], $OrderObj->coupon_code_discount_total, $fp), "Coupon Discount does not match database.", $fp);
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['session_discount'], $OrderObj->session_discount_total, $fp), "Session Discount does not match database.", $fp);
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['service_fee'], $OrderObj->subtotal_service_fee, $fp), "Service Fee does not match database.", $fp);

		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['food_tax'], $OrderObj->subtotal_food_sales_taxes, $fp), "Food Sales Tax does not match database.", $fp);
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['service_tax'], $OrderObj->subtotal_service_tax, $fp), "Service Fee Tax does not match database.", $fp);
		DD_Selenium_Test_Utils::Assert(self::currencyCompare($clientFacts['service_fee'], $OrderObj->subtotal_service_fee, $fp), "Service Fee does not match database.", $fp);

		} catch(Exception $e) {
			DD_Selenium_Test_Utils::Trace("test pass failed: exception occurred<br>\n");
			DD_Selenium_Test_Utils::Trace("reason: " . $e->getMessage(), $fp);
			fclose($fp);
			exit;
		}

		DD_Selenium_Test_Utils::$throwOnAssertFailure = false;


		/*
		$retVal['payment_total'] = $driver->findElement(WebDriverBy::id("OEH_paymentsTotal"))->getText();
		*/

	}

	//$session_type is FUNDRAISER or DREAM_TASTE
	static function getEventPropsForMenuAndSessionType($menu_id, $session_type, $canRSVP, $hostRequired)
	{
		$props = DAO_CFactory::create('dream_taste_event_properties');

		if ($session_type == 'DREAM_TASTE' && $hostRequired && $canRSVP)
		{
			//FNO
			$props->query("select * from dream_taste_event_properties dtep where dtep.menu_id = $menu_id and dtep.can_rsvp_only = 1 and dtep.host_required = 1 and dtep.is_deleted = 0");
		}
		else if ($session_type == 'DREAM_TASTE' && $hostRequired && !$canRSVP)
		{
			//DREAM TASTE Standard
			$props->query("select * from dream_taste_event_properties dtep where dtep.menu_id = $menu_id and dtep.can_rsvp_only = 0 and dtep.host_required = 1 and dtep.is_deleted = 0");
		}
		else if ($session_type == 'DREAM_TASTE' && !$hostRequired && !$canRSVP)
		{
			//DREAM TASTE Openhouse
			$props->query("select * from dream_taste_event_properties dtep where dtep.menu_id = $menu_id and dtep.can_rsvp_only = 0 and dtep.host_required = 0 and dtep.fundraiser_value = 0 and dtep.is_deleted = 0");
		}
		else if ($session_type == 'FUNDRAISER')
		{
			//Fundraiser
			$props->query("select * from dream_taste_event_properties dtep where dtep.menu_id = $menu_id and dtep.can_rsvp_only = 0 and dtep.host_required = 0 and dtep.fundraiser_value > 0 and dtep.is_deleted = 0");
		}
	}
}