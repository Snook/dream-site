<?php
require_once('DAO/Session.php');
require_once('DAO/Menu.php');
require_once('DAO/Menu_item.php');
require_once('DAO/Menu_to_menu_item.php');
require_once 'DAO/BusinessObject/CMenuItem.php';
require_once('DAO/BusinessObject/CSession.php');
require_once('DAO/BusinessObject/CPayment.php');
require_once('DAO/BusinessObject/COrders.php');
require_once('DAO/BusinessObject/CMenu.php');
require_once('DAO/BusinessObject/CBundle.php');

function stationCompare($a, $b)
{

	if ($a['station_sort'] == $b['station_sort'])
	{
		return 0;
	}

	return ($a['station_sort'] < $b['station_sort']) ? -1 : 1;
}

function retrieveMiscCostListForPeriod($store_id, $Day, $Month, $Year, $Interval = '1 DAY')
{

	$storeClause = "o.store_id = $store_id and";
	$storeCols = "";
	if ($store_id == "all")
	{
		$storeClause = "";
	}

	$varStr = "";
	$session = DAO_CFactory::create("session");
	$session->query('SET group_concat_max_len = 100000;');
	$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
	$current_date_sql = date("Y-m-d 00:00:00", $current_date);
	$varStr = "Select st.store_name, st.city, st.state_id, CONCAT(u.firstname, ' ', u.lastname) as customer_name, s.session_start, o.id, o.misc_food_subtotal, o.misc_food_subtotal_desc, o.misc_nonfood_subtotal, o.misc_nonfood_subtotal_desc From orders o " . "Inner Join booking b ON o.id = b.order_id " . "Inner Join session s ON s.id = b.session_id " . "Inner Join store st on st.id = o.store_id " . "Join user u on o.user_id = u.id " . "Where b.status = 'ACTIVE' and b.is_deleted = 0 AND s.is_deleted = 0 and  $storeClause " . "s.session_start >= '$current_date_sql' AND s.session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval)  AND (o.misc_food_subtotal > 0 or o.misc_food_subtotal > 0)";

	$session->query($varStr);
	$rows = array();
	$count = 0;
	while ($session->fetch())
	{

		if ($session->misc_food_subtotal > 0)
		{
			if ($store_id == "all")
			{
				$rows [$count++] = array(
					'store_name' => $session->store_name,
					'city' => $session->city,
					'state' => $session->state_id,
					'customer_name' => $session->customer_name,
					'session_start' => CTemplate::dateTimeFormat($session->session_start),
					'type' => 'Food',
					'description' => $session->misc_food_subtotal_desc,
					'price' => '$' . CTemplate::moneyFormat($session->misc_food_subtotal)
				);
			}
			else
			{
				$rows [$count++] = array(
					'customer_name' => $session->customer_name,
					'session_start' => CTemplate::dateTimeFormat($session->session_start),
					'type' => 'Food',
					'description' => $session->misc_food_subtotal_desc,
					'price' => '$' . CTemplate::moneyFormat($session->misc_food_subtotal)
				);
			}
		}

		if ($session->misc_nonfood_subtotal > 0)
		{
			if ($store_id == "all")
			{
				$rows [$count++] = array(
					'store_name' => $session->store_name,
					'city' => $session->city,
					'state' => $session->state_id,
					'customer_name' => $session->customer_name,
					'session_start' => CTemplate::dateTimeFormat($session->session_start),
					'type' => 'Non-Food',
					'description' => $session->misc_nonfood_subtotal_desc,
					'price' => '$' . CTemplate::moneyFormat($session->misc_nonfood_subtotal)
				);
			}
			else
			{

				$rows [$count++] = array(
					'customer_name' => $session->customer_name,
					'session_start' => CTemplate::dateTimeFormat($session->session_start),
					'type' => 'Non-Food',
					'description' => $session->misc_nonfood_subtotal_desc,
					'price' => '$' . CTemplate::moneyFormat($session->misc_nonfood_subtotal)
				);
			}
		}
	}

	return ($rows);
}

class CMenuEntreeItem
{
	public $menu_id = 0;
	public $menu_name = "";
	public $inventory_count = 0;
	public $servingSize = 0;
	public $variety_count = 0;
	public $value_count = 0;
	public $container = "";
	public $m_intro_count = 0;
	public $m_variety_count = 0;
	public $m_value_count = 0;
	public $two_variety_count = 0;
	public $four_variety_count = 0;
	public $m_quick_count = 0;
	public $plan = "";
	public $m_sysco_count = 0;
	public $item_revenue = 0;

	function addToInventory($newCount, $newRevenue, $servingSize, $container, $plan)
	{
		$this->inventory_count = $newCount + $this->inventory_count;
		$this->item_revenue += $newRevenue;

		if ($servingSize == CMenuItem::HALF_SERVING_SIZE)
		{
			$this->variety_count += $newCount;
			$this->m_sysco_count += ($newCount * .5);
		}
		else if ($servingSize == CMenuItem::TWO_SERVING_SIZE)
		{
			//TODO:evanl ask carl what this is?
			$this->m_sysco_count += $newCount;
		}
		else if ($servingSize == CMenuItem::FOUR_SERVING_SIZE)
		{
			//TODO:evanl ask carl what this is?
			$this->m_sysco_count += $newCount;
		}
		else if ($servingSize == CMenuItem::FULL_SERVING_SIZE)
		{
			$this->value_count += $newCount;
			$this->m_sysco_count += $newCount;
		}
		else if ($servingSize == 9)
		{
			$this->value_count += $newCount;
			$this->m_sysco_count += ($newCount * .5);
		}
		else if ($servingSize == 18)
		{
			$this->value_count += $newCount;
			$this->m_sysco_count += $newCount;
		}

		$this->servingSize += $newCount * $servingSize;
		$this->container = $container;
		$this->plan = $plan;
		switch ($plan)
		{
			//case CMenuItem::QUICK_HEARTY:
			//$this->m_quick_count += $newCount;
			//break;
			case CMenuItem::LEGACY:
			case CMenuItem::FULL:
				$this->m_value_count += $newCount;
				break;
			case CMenuItem::INTRO:
				$this->m_intro_count += $newCount;
				break;
			case CMenuItem::HALF:
				$this->m_variety_count += $newCount;
				break;
			case CMenuItem::TWO:
				$this->two_variety_count += $newCount;
				break;
			case CMenuItem::FOUR:
				$this->four_variety_count += $newCount;
				break;
		} // switch

	}

	function getSyscoCounts()
	{
		return $this->m_sysco_count;
	}

	function getServingSizes()
	{
		return $this->servingSize;
	}

	function getInventoryCount()
	{
		return $this->inventory_count;
	}

	function getVarietyCount()
	{
		return $this->variety_count;
	}

	function getValueCount()
	{
		return $this->value_count;
	}

	function getMenuID()
	{
		return $this->menu_id;
	}

	function getMenuName()
	{
		return $this->menu_name;
	}

	public function __construct($in_menu_id, $in_menu_title)
	{
		$this->menu_id = $in_menu_id;
		$this->menu_name = $in_menu_title;
	}
}

class CEntreeItem
{
	var $menu_entry_title = "";
	var $menu_item_id = 0;
	var $recipe_id = null;
	var $EntreeObjectArray = array();
	var $children_entrees = null;
	var $children_entrees_sizes = null;

	var $is_side_dish = 0;
	var $is_kids_choice = 0;
	var $is_menu_addon = 0;
	var $is_chef_touched = 0;
	var $is_bundle = 0;
	var $is_preassembled = 0;
	var $category_ordering = 0;
	var $menu_item_category_id = null;
	var $category_name = null;
	var $num_purchasers = 0;

	function addToInventory($menu_id, $inventory_count, $item_revenue, $servingSize, $container, $plan)
	{
		$found = false;
		for ($i = 0; $i < count($this->EntreeObjectArray); $i++)
		{
			$obj_temp = $this->EntreeObjectArray[$i];
			$varId = $obj_temp->getMenuID();
			if ($menu_id == $varId)
			{
				$obj_temp->addToInventory($inventory_count, $item_revenue, $servingSize, $container, $plan);
				$found = true;
				break;
			}
		}

		return $found;
	}

	function AddMenu($in_menu_id, $in_menu_name)
	{
		for ($i = 0; $i < count($this->EntreeObjectArray); $i++)
		{
			$obj_temp = $this->EntreeObjectArray[$i];
			if ($obj_temp->getMenuID() != $in_menu_id)
			{
				$obj = new CMenuEntreeItem($in_menu_id, $in_menu_name);
				$this->EntreeObjectArray[count($this->EntreeObjectArray)] = $obj;
				break;
			}
		}
	}

	public function __construct($in_menu_item_id, $in_menu_id, $in_menu_entree_title, $recipe_id, $in_menu_name, $children_entrees, $entree_sizes)
	{
		$this->menu_entry_title = $in_menu_entree_title;
		$this->menu_item_id = $in_menu_item_id;
		$this->children_entrees = $children_entrees;
		$this->children_entrees_sizes = $entree_sizes;
		$this->recipe_id = $recipe_id;

		$obj = new CMenuEntreeItem($in_menu_id, $in_menu_name);
		$this->EntreeObjectArray[0] = $obj;
	}

	function getAllReportData($menu_name)
	{
		$report_array = null;
		$varcount = 0;
		$variety_count = 0;
		$value_count = 0;
		$quickCount = 0;
		$introCount = 0;
		$valueCount = 0;
		$varietyCount = 0;
		$servingTotals = 0;
		$syscoCounts = 0;
		$item_revenue = 0;

		$smallCount = 0;

		$mediumFourCount = 0;

		// should only ever be one instance for a given menu item
		foreach ($this->EntreeObjectArray as $object)
		{
			if ($object->menu_name === $menu_name)
			{
				$varcount = $varcount + $object->getInventoryCount();
				$variety_count += $object->getVarietyCount();
				$value_count += $object->getValueCount();
				$quickCount += $object->m_quick_count;
				$introCount += $object->m_intro_count;
				//halfs
				$valueCount += $object->m_value_count;
				//fulls
				$varietyCount += $object->m_variety_count;

				//2/4
				$smallCount += $object->two_variety_count;

				$mediumFourCount += $object->four_variety_count;

				$servingTotals += $object->getServingSizes();

				$syscoCounts += $object->getSyscoCounts();

				$item_revenue += $object->item_revenue;

				$report_array = array(
					$this->menu_item_id,
					$varcount,
					$this->menu_entry_title,
					$object->getMenuName(),
					$object->getMenuID(),
					$introCount,
					$valueCount,
					$varietyCount,
					$quickCount,
					$value_count,
					$variety_count,
					$servingTotals,
					$syscoCounts,
					$item_revenue,
					$smallCount,
					$mediumFourCount
				);
				//$report_array = array($this->menu_item_id , $varcount, $this->menu_entry_title, $object->getMenuName(), $object->getMenuID(), $introCount, $valueCount,$varietyCount ,
				//$quickCount ,$value_count,$variety_count, $servingTotals);
				break;
			}
		}

		return ($report_array);
	}
}

// TODO: okay this class has grown beyond Session Report / it has functionality that supports entree as well..
//

class CSessionReports
{

	var $entree_total_count = 0;
	var $total_orders = 0;
	var $total_orders_greater_than_72_servings = 0;
	const NEWPLANACTIVE = '2006-09-18 00:00:00';
	const MENUPLANDEPLOYED = '2006-09-01 00:00:00';
	const SHOWLEGACY = 0;
	const SHOWNEWPLAN = 1;
	var $categoryArray = null;

	function getCategories($category_id)
	{
		$category_name = null;

		if (empty($this->categoryArray))
		{

			$this->categoryArray = array();
			$categories = DAO_CFactory::create('menu_item_category');
			$categories->is_active = 1;
			$categories->orderBy('global_order_value');

			$categories->find();
			while ($categories->fetch())
			{
				$this->categoryArray[$categories->id] = array(
					'global_order_value' => $categories->global_order_value,
					'category_type' => $categories->category_type
				);
			}
		}

		if (!empty($category_id))
		{
			$category_name = $this->categoryArray[$category_id];
		}

		return $category_name;
	}

	/*
	 * receives a mysql date string asnd a store object and returns true if the date is lessthan 2 weeks old
	 */
	static function can_edit_order($session_time, $store)
	{
		if (defined('ORDER_EDITING_ENABLED') && ORDER_EDITING_ENABLED)
		{
			$store_obj = null;
			if (is_numeric($store))
			{
				$store_obj = DAO_CFactory::create('store');
				$store_obj->id = $store;
				$store_obj->find(true);
			}

			$sessionTS = strtotime($session_time);
			$adjustedServerTime = CTimezones::getAdjustedServerTime($store_obj);

			//allow rescheduling and canceling 2 weeks past session date
			if ($adjustedServerTime > ($sessionTS + 1209600))
			{
				return false;
			}

			// check for orders in previous month if current day is greater than 6
			$day = date("j", $adjustedServerTime);
			$month = date("n", $adjustedServerTime);
			$year = date("Y", $adjustedServerTime);

			if ($day > 6)
			{
				$cutOff = mktime(0, 0, 0, $month, 1, $year);
				if ($sessionTS < $cutOff)
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	static function isLegacyOrderingSystem($nowts)  // 0 is show only legacy, 1 is show legacy and new plan, 3 is show only new plan data
	{

		$compare = strtotime(self::MENUPLANDEPLOYED);
		if ($nowts < $compare)
		{
			return self::SHOWLEGACY;
		}
		else
		{
			return self::SHOWNEWPLAN;
		}
	}

	static function formatCurrency($currency_value)
	{

		$currency = (float)$currency_value;
		$formatted = sprintf("%.2f", $currency);
		$english_format_number = number_format((float)$currency, 2);
		$newstr = "$" . $english_format_number;

		return $newstr;
	}

	static function generateTimeStamp($sqltime)
	{
		$sep = explode("-", $sqltime);
		$newTime = mktime(0, 0, 0, $sep[0], $sep[1], $sep[2]);

		return $newTime;
	}

	static function convertTimeStamp($sqltime)
	{
		$sep = explode(" ", $sqltime);
		$timesep = explode(":", $sep[1]); // split time into hr, min, sec
		$newTime = mktime($timesep[0], $timesep[1], $timesep[2]);
		//$formatTime = date("h:i:s A", $newTime);
		$formatTime = date("g:i a", $newTime);

		return $formatTime;
	}

	// huh ? why two TODO?
	static function reformatTime($sqltime)
	{
		$sep = explode(" ", $sqltime);
		$timesep = explode(":", $sep[1]); // split time into hr, min, sec
		$newTime = mktime($timesep[0], $timesep[1], $timesep[2]);
		$formatTime = date("h:i:s A", $newTime);
		$finalDate = $sep[0] . ' ' . $formatTime;

		return $finalDate;
	}

	static function newDayFormat($sqltime)
	{
		$sep = explode(" ", $sqltime);
		$daysep = explode("-", $sep[0]);
		$newTime = mktime(0, 0, 0, $daysep[1], $daysep[2], $daysep[0]);

		return date("F j, Y", $newTime);
	}

	static function newDayTimeFormat($sqltime)
	{
		$sep = explode(" ", $sqltime);
		$daysep = explode("-", $sep[0]);
		$timesep = explode(":", $sep[1]); // split time into hr, min, sec
		$newTime = mktime($timesep[0], $timesep[1], $timesep[2]);
		$formatTime = date("h:i:s A", $newTime);
		$formateDate = $daysep[1] . "/" . $daysep[2] . "/" . $daysep[0];

		return $formateDate . " " . $formatTime;
	}

	function getTotalEntreeCount()
	{
		return ($this->entree_total_count);
	}

	function getPromoCountsPerItem(&$total_items_found, $store_id, $Day, $Month, $Year, $Interval = '1 DAY')
	{

		$storeClause = "";
		if (!empty($store_id) && is_numeric($store_id))
		{
			$storeClause = " session.store_id = " . $store_id . " AND ";
		}

		$varStr = "";
		$session = DAO_CFactory::create("session");
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varStr = 'Select  menu_item.menu_item_name, count(promo_code_id) as counter, pricing_type' . ' From booking' . ' Inner Join session ON session.id = booking.session_id  Inner Join orders ON booking.order_id = orders.id ' . ' Inner Join promo_code ON promo_code.id = orders.promo_code_id' . ' Inner Join menu_item ON menu_item.id = promo_code.promo_menu_item_id' .

			" Where promo_code_id is not null and booking.status = 'ACTIVE' and booking.is_deleted = 0 AND session.is_deleted = 0 and " . $storeClause . " session_start >= '" . $current_date_sql . "' AND" . " session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) AND " . //" session_publish_state != '" . 'SAVED' . "' group by pricing_type " ;
			" session_publish_state != '" . 'SAVED' . "' group by promo_code_id, pricing_type ";

		$book = DAO_CFactory::create("booking");
		$book->query($varStr);
		$rows = array();
		$total_items_found = 0;
		while ($book->fetch())
		{
			$rows [$book->menu_item_name][$book->pricing_type] = $book->counter;
			$total_items_found += $book->counter;
		}

		return ($rows);
	}

	function getPromoCountsPerItemByProgram($store_id, $Day, $Month, $Year, $Interval, &$total_items_found)
	{

		$storeClause = "";
		if (!empty($store_id) && is_numeric($store_id))
		{
			$storeClause = " session.store_id = " . $store_id . " AND ";
		}

		$varStr = "";
		$session = DAO_CFactory::create("session");
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varStr = 'Select `menu_item`.`menu_program_id`, menu_item.id as menuitemid,  menu_item.menu_item_name, count(promo_code_id) as counter, pricing_type' . ' From booking' . ' Inner Join session ON session.id = booking.session_id  Inner Join orders ON booking.order_id = orders.id ' . ' Inner Join promo_code ON promo_code.id = orders.promo_code_id' . ' Inner Join menu_item ON menu_item.id = promo_code.promo_menu_item_id' .

			" Where promo_code_id is not null and booking.status = 'ACTIVE' and booking.is_deleted = 0 AND session.is_deleted = 0 and " . $storeClause . " session_start >= '" . $current_date_sql . "' AND" . " session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) AND " . //" session_publish_state != '" . 'SAVED' . "' group by pricing_type " ;
			" session_publish_state != '" . 'SAVED' . "' group by menu_program_id, promo_code_id, pricing_type ";

		$book = DAO_CFactory::create("booking");
		$book->query($varStr);
		$rows = array();
		$total_items_found = 0;
		while ($book->fetch())
		{

			$rows [$book->menu_item_name][$book->menu_program_id][$book->pricing_type] = $book->counter;
			$total_items_found += $book->counter;
		}

		return ($rows);
	}

	function getPromoCountsPerItemGroupBy($store_id, $Day, $Month, $Year, $Interval, &$total_items_found, $groupBy)
	{
		$session = DAO_CFactory::create("session");
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varStr = 'Select ';
		$varStr .= " WEEK(orders.timestamp_created) as week_val, Concat(MONTH(orders.timestamp_created), '-', DAY(orders.timestamp_created), '-' ,YEAR(orders.timestamp_created) ) as datestamp, ";
		$varStr .= 'orders.timestamp_created, count(promo_code_id) as counter, pricing_type' . ' From booking' . ' Inner Join session ON session.id = booking.session_id  Inner Join orders ON booking.order_id = orders.id ' . ' Inner Join promo_code ON promo_code.id = orders.promo_code_id' . ' Inner Join menu_item ON menu_item.id = promo_code.promo_menu_item_id' .

			" Where promo_code_id is not null and booking.status = 'ACTIVE' and booking.is_deleted = 0 AND session.is_deleted = 0 and " . " session.store_id = " . $store_id . " AND" . " orders.timestamp_created >= '" . $current_date_sql . "' AND" . " orders.timestamp_created <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) AND " . " session_publish_state != '" . 'SAVED' . "'";

		if ($groupBy == 'day')
		{
			$varStr .= " group by datestamp, pricing_type ";
		}
		else if ($groupBy == 'week')
		{
			$varStr .= " group by week_val, pricing_type ";
		}

		$book = DAO_CFactory::create("booking");
		$book->query($varStr);
		$rows = array();
		$total_items_found = 0;

		while ($book->fetch())
		{

			if ($groupBy == 'day')
			{
				$rows [$book->datestamp][$book->pricing_type] = $book->counter;
			}
			else if ($groupBy == 'week')
			{
				$rows [$book->week_val][$book->pricing_type] = $book->counter;
			}

			$total_items_found += $book->counter;
		}

		return ($rows);
	}

	function getPromoCountsBySession($session_id, &$total_items_found)
	{
		$varStr = "";
		$session = DAO_CFactory::create("session");
		$varStr = 'Select  menu_item.menu_item_name, count(promo_code_id) as counter, pricing_type' . ' From booking' . ' Inner Join session ON session.id = booking.session_id  Inner Join orders ON booking.order_id = orders.id ' . ' Inner Join promo_code ON promo_code.id = orders.promo_code_id' . ' Inner Join menu_item ON menu_item.id = promo_code.promo_menu_item_id' .

			" Where promo_code_id is not null and booking.status = 'ACTIVE' and booking.is_deleted = 0 AND session.is_deleted = 0 and " . " session.id = " . $session_id . " AND" . " session_publish_state != '" . 'SAVED' . "' group by promo_code_id,pricing_type ";
		//" session_publish_state != '" . 'SAVED' . "' group by pricing_type " ;

		$book = DAO_CFactory::create("booking");
		$book->query($varStr);
		$rows = array();
		$total_items_found = 0;
		while ($book->fetch())
		{

			$rows [$book->menu_item_name][$book->pricing_type] = $book->counter;
			$total_items_found += $book->counter;
		}

		return ($rows);
	}

	function createAvailableSessionsArray($store)
	{
		$rows = array();
		$rows['null'] = '--- Choose a Session ---';
		$Session = DAO_CFactory::create("session");
		$numSessions = $Session->findSessions($store);
		while ($Session->fetch())
		{
			$isClosed = $Session->session_publish_state;
			$sessiondate = $Session->session_start;
			$newtimestamp = $this->reformatTime($sessiondate);
			if ($isClosed == "CLOSED")
			{
				$newtimestamp = $newtimestamp . " CLOSED";
			}  // do we really care about cancelled sessions?
			$rows [$Session->id] = $newtimestamp;
		}

		return $rows;
	}

	//get session info only without joins
	function findSessionInfo($sessionid){
		$session = DAO_CFactory::create("session");

		$varStr = "select *
			FROM session
			WHERE `session`.id = '" . $sessionid . "';";

		$session->query($varStr);
		$rows = array();
		$count = 0;
		while ($session->fetch())
		{
			$rows [$count++] = $session->toArray();
		}

		return ($rows);
	}
	function findSessionDetails($sessionid)
	{
		$session = DAO_CFactory::create("session");
		$session->query('SET group_concat_max_len = 100000;');
		$varStr = "select iq.*, urs.meta, CONCAT( u2.firstname, ' ', u2.lastname ) AS referrers_name, u2.id AS referring_user_id, cr.origination_type_code from (
            SELECT booking.id AS booking_id, booking.no_show AS booking_no_show, booking.booking_type, `session`.session_start, booking.order_id, booking.user_id, `session`.menu_id, `user`.firstname,
			`user`.lastname, orders.menu_program_id, orders.bundle_id, `user`.dream_reward_status, `user`.is_partial_account, `user`.is_deleted AS acount_deleted, booking.status, `session`.session_type, `session`.session_type_subtype, booking.session_id,
			`session`.is_deleted, `session`.available_slots,
			GROUP_CONCAT(menu_item.id, ':', order_item.item_count) AS Menu_Items,
			GROUP_CONCAT(product.id, ':', order_item.item_count) AS Product_Items
			FROM booking
			INNER JOIN user ON booking.user_id = `user`.id
			INNER JOIN session ON `session`.id = booking.session_id
			JOIN orders ON booking.order_id = orders.id
			LEFT JOIN user_referral_source urs ON urs.user_id = `user`.id AND urs.source = 'CUSTOMER_REFERRAL' AND urs.is_deleted = 0
			LEFT JOIN user u2 ON urs.meta = u2.primary_email AND urs.meta <> '' AND urs.meta IS NOT NULL AND u2.is_deleted = 0
			LEFT JOIN customer_referral cr ON cr.id = urs.customer_referral_id
			INNER JOIN order_item ON booking.order_id = order_item.order_id AND order_item.is_deleted = 0
			LEFT JOIN product ON product.id = order_item.product_id
			LEFT JOIN menu_item ON menu_item.id = order_item.menu_item_id
			WHERE `session`.id = '" . $sessionid . "' AND booking.status = 'ACTIVE' AND booking.is_deleted = 0 AND `session`.is_deleted = 0 AND session_publish_state != 'SAVED'
			GROUP BY booking.order_id
			ORDER BY booking.timestamp_created DESC) as iq
            LEFT JOIN user_referral_source urs ON urs.user_id = iq.user_id AND urs.source = 'CUSTOMER_REFERRAL' AND urs.is_deleted = 0
            LEFT JOIN user u2 ON urs.meta = u2.primary_email AND urs.meta <> '' AND urs.meta IS NOT NULL AND u2.is_deleted = 0
            LEFT JOIN customer_referral cr ON cr.id = urs.customer_referral_id
            GROUP BY
            iq.order_id";

		$session->query($varStr);
		$rows = array();
		$count = 0;
		while ($session->fetch())
		{
			$rows [$count++] = $session->toArray();
		}

		return ($rows);
	}

	function joinEntreesWithMenus($session_array, &$menu_array, $containerArray)
	{

		foreach ($session_array as $session)
		{

			$menu_item_list = $session['Menu_Items'];

			$menu_id = $session['menu_id'];

			$this->total_orders += $session['numOrders'];
			$this->total_orders_greater_than_72_servings += $session['numOrdersGreaterThan72servings'];

			$session_id = $session['session_id'];

			if (strlen($menu_item_list) > 0)
			{

				$itemPairs = explode(",", $menu_item_list);

				foreach ($itemPairs as $menuPair)
				{

					$entity = explode(":", $menuPair);
					$menu_item_id = $entity[0];
					$item_count = $entity[1];
					$item_revenue = $entity[2];
					$this->entree_total_count += $item_count;

					$varCounter = -1;

					foreach ($menu_array as $entity)
					{
						$varmenuid = $entity->menu_item_id;
						$varCounter++;
						//PHP 8 $children = $entity->children_entrees ?? array();
						if(is_null($entity->children_entrees)){
							$entity->children_entrees = array();
						}
						if (array_search($menu_item_id, $entity->children_entrees) === false)
						{
							continue;
						}
						else
						{
							$servingSize = 0;
							$container = 0;
							$plan = "";

							if ($containerArray != null && count($containerArray) > 0 && isset($containerArray[$menu_item_id]))
							{
								$servingSize = $containerArray[$menu_item_id]['size'];
								$container = $containerArray[$menu_item_id]['container'];
								$plan = $containerArray[$menu_item_id]['plan'];
							}

							$object = $menu_array[$varmenuid];

							if ($object != null)
							{
								// don't add side dishes for now
								// keep them separate
								$object->addToInventory($menu_id, $item_count, $item_revenue, $servingSize, $container, $plan);
							}

							break;
						}
					}
				}
			}
			else
			{
				//	throw new exception('joinEntreesWithMenus error');
				// this is no longer an exception, but rather can happen with TODD 5/27/2009  LMH
			}
		}
	}

	function joinEntreesWithMenusDelivered($session_array, &$menu_array, $containerArray)
	{

		foreach ($session_array as $session)
		{

			$menu_item_list = $session['Menu_Items'];

			$menu_id = $session['menu_id'];

			$this->total_orders += $session['numOrders'];
			$this->total_orders_greater_than_72_servings += $session['numOrdersGreaterThan72servings'];

			$session_id = $session['session_id'];

			if (strlen($menu_item_list) > 0)
			{

				$itemPairs = explode(",", $menu_item_list);

				foreach ($itemPairs as $menuPair)
				{

					$entity = explode(":", $menuPair);
					$menu_id = $entity[0];
					$menu_item_id = $entity[1];
					$item_count = $entity[2];
					$item_revenue = $entity[3];

					$this->entree_total_count += $item_count;

					$varCounter = -1;

					foreach ($menu_array as $entity)
					{
						$varmenuid = $entity->menu_item_id;
						$varCounter++;
						if (array_search($menu_item_id, $entity->children_entrees) === false)
						{
							continue;
						}
						else
						{
							$servingSize = 0;
							$container = 0;
							$plan = "";

							if ($containerArray != null && count($containerArray) > 0 && isset($containerArray[$menu_item_id]))
							{
								$servingSize = $containerArray[$menu_item_id]['size'];
								$container = $containerArray[$menu_item_id]['container'];
								$plan = $containerArray[$menu_item_id]['plan'];
							}

							$object = $menu_array[$varmenuid];

							if ($object != null)
							{
								// don't add side dishes for now
								// keep them separate
								$object->addToInventory($menu_id, $item_count, $item_revenue, $servingSize, $container, $plan);
							}

							break;
						}
					}
				}
			}
			else
			{
				//	throw new exception('joinEntreesWithMenus error');
				// this is no longer an exception, but rather can happen with TODD 5/27/2009  LMH
			}
		}
	}

	function getCustomerHistory($exported_customerArray, $session_id, $storeid)
	{
		$sessionObj = DAO_CFactory::create('session');
		$sessionObj->query("select session_start from session where id = $session_id");
		$sessionObj->fetch();

		$thisSessionStart = $sessionObj->session_start;

		foreach ($exported_customerArray as $element)
		{
			$user_list[] = $element['customer_id'];
		}

		$user_list = implode(",", $user_list);

		$booking = DAO_CFactory::create("booking");

		$current_date_sql = $now = date("Y-m-d 00:00:00");

		//To have been attended the session must be before now
		$booking->query("SELECT
			b.user_id,
			MAX(s.session_start) AS last_session_attended,
			COUNT(b.id) AS bookings_made,
			GROUP_CONCAT(b.session_id) as session_ids
			FROM booking AS b
			INNER JOIN `session` AS s ON b.session_id = s.id
			WHERE s.store_id = '" . $storeid . "'
			AND s.session_start < '" . $thisSessionStart . "'
			AND b.user_id IN (" . $user_list . ")
			AND b.`status` = 'ACTIVE'
			AND b.is_deleted = 0
			AND s.is_deleted = 0
			AND s.session_publish_state != 'SAVED'
			GROUP BY b.user_id
			ORDER BY b.user_id ASC, s.session_start ASC");

		$rows = array();

		while ($booking->fetch())
		{
			$session = DAO_CFactory::create("session");
			$session->query("select * from session where 
      		id in (" . $booking->session_ids . ")
			and session_start = '".$booking->last_session_attended."'");
			$session->fetch();
			$session_type = '';
			$session_subtype = '';
			if($session->N > 0){
				$session_type = $session->session_type;
				$session_subtype = $session->session_type_subtype;
			}
			$rows[$booking->user_id] = array(
				'bookings_made' => $booking->bookings_made,
				'last_session_attended' => $booking->last_session_attended,
				'last_session_attended_type' => $session_type,
				'last_session_attended_subtype' => $session_subtype
			);
		}

		return ($rows);
	}

	function getCustomerHistoryPointInTime($exported_customerArray, $current_date, $storeid)
	{

		$sep = explode(" ", $current_date);
		$daysep = explode("-", $sep[0]);
		$timesep = explode(":", $sep[1]); // split time into hr, min, sec
		$newTime = mktime($timesep[0] - 3, $timesep[1], $timesep[2]);
		$formatTime = date("H:i:s", $newTime);
		$formateDate = $daysep[0] . "-" . $daysep[1] . "-" . $daysep[2];
		$ts = $formateDate . " " . $formatTime;
		foreach ($exported_customerArray as $element)
		{
			$user_list[] = $element['customer_id'];
		}
		sort($user_list);
		$user_list = implode(",", $user_list);

		$varStr = 'Select booking.user_id, Max(session_start) as last_session_attended, count(booking.id) as bookings_made From' . " booking Inner Join session ON booking.session_id = session.id Where session.store_id = $storeid and  session.session_start <= '$ts' and booking.user_id in ($user_list)" . " and booking.`status` = 'ACTIVE' AND booking.is_deleted = 0 and session.is_deleted = 0 and session_publish_state !=  'SAVED'" . ' group by user_id order by user_id,session_start';
		$booking = DAO_CFactory::create("booking");
		$booking->query($varStr);
		$rows = array();
		while ($booking->fetch())
		{
			$rows[$booking->user_id] = array(
				'bookings_made' => $booking->bookings_made,
				'last_session_attended' => $booking->last_session_attended
			);
		}

		return ($rows);
	}

	function getInventoryDataForMenuArray($menu_array, $store_id, $isDelivered = false)
	{

		if ($isDelivered)
		{
			$parentGetter = new DAO();
			$parentGetter->query("select parent_store_id from store where id = " . $store_id);
			$parentGetter->fetch();
			$store_id = $parentGetter->parent_store_id;
		}

		$menu_list = implode(',',$menu_array);
		$varStr = "";
		$item = DAO_CFactory::create("menu_to_menu_item");

		$rows = array();

		if (empty($store_id))
		{
			return $rows;
		}

		// INVENTORY TOUCH POINT 6

		$varStr = "select mii.recipe_id, mii.override_inventory - mii.number_sold as remaining, mmi.menu_item_id, mii.menu_id, mi.recipe_id, mi.menu_item_category_id
 							 from  menu_to_menu_item  mmi
					join menu_item mi on mi.id = mmi.menu_item_id  and mi.id = mi.entree_id
					left  join menu_item_inventory mii on mi.recipe_id = mii.recipe_id and mii.menu_id = mmi.menu_id and mii.store_id = $store_id and mii.is_deleted = 0
					where mmi.store_id = $store_id and mmi.menu_id in ($menu_list) and mmi.is_deleted = 0 group by mii.recipe_id, mmi.menu_id order by mmi.menu_item_id";

		$item->query($varStr);
		while ($item->fetch())
		{
			$rows[$item->menu_id][$item->menu_item_id] = $item->remaining;
		}

		return ($rows);
	}

	function getGuestCountsForMenuItems(&$menu_items_array, $store_id, $Day, $Month, $Year, $Interval = '1 DAY', $sessionArray = false)
	{

		$BookingDAO = DAO_CFactory::create('booking');

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$store_clause = "";
		if ($store_id)
		{
			$store_clause = " and s.store_id = $store_id ";
		}

		$session_clause = "";
		if (!empty($sessionArray))
		{
			if (count($sessionArray) > 0)
			{
				$ids = '';
				foreach ($sessionArray as $value){
					$ids .= $value['session_id'] . ',';
				}

				$ids = rtrim($ids,',');
				$session_clause = " s.id in (".$ids.") and ";
			}
		}

		$BookingDAO->query("SELECT mi.entree_id, mi.menu_item_name, sum(oi.item_count)as num_purchased, count(distinct o.user_id) as num_purchasers  from booking b
							inner join session s on s.id = b.session_id
							inner join orders o on o.id = b.order_id
							inner join order_item oi on oi.order_id = o.id and oi.is_deleted = 0
							left join menu_item  mi on mi.id = oi.menu_item_id
							where b.status = 'ACTIVE' and b.is_deleted = 0 and s.is_deleted = 0 $store_clause and $session_clause
							s.session_start >= '$current_date_sql' and  s.session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval)
							group by mi.entree_id");

		while ($BookingDAO->fetch())
		{
			if (isset($menu_items_array[$BookingDAO->entree_id]))
			{
				$menu_items_array[$BookingDAO->entree_id]->num_purchasers = $BookingDAO->num_purchasers;
			}
		}
	}

	function getTVOfferCounts($store_id, $Day, $Month, $Year, $Interval = '1 DAY')
	{
		$varStr = "";
		$session = DAO_CFactory::create("session");
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$varStr = "SELECT COUNT(orders.bundle_id) AS numBundles, booking.session_id, menu.menu_name " . " From booking " . " Inner Join session ON session.id = booking.session_id " . " Inner Join menu ON menu.id = session.menu_id " . " Inner Join orders ON booking.order_id = orders.id and orders.bundle_id is not null and orders.bundle_id > 0 and orders.type_of_order = 'INTRO' " . " Where booking.status = 'ACTIVE' and booking.is_deleted = 0 AND session.is_deleted = 0 and session.store_id = $store_id and " . " session_start >= '" . $current_date_sql . "' AND " . " session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) " . " group by session.id";

		$session->query($varStr);
		$rows = array();
		while ($session->fetch())
		{

			if (!isset($rows[$session->menu_name]))
			{
				$rows [$session->menu_name] = $session->numBundles;
			}
			else
			{
				$rows [$session->menu_name] += $session->numBundles;
			}
		}

		return ($rows);
	}

	function getTVOfferCountForSession($session_ids)
	{

		if (empty($session_ids))
		{
			return 0;
		}

		$sessionList = implode(",", $session_ids);

		$varStr = "";
		$session = DAO_CFactory::create("session");
		$varStr = "SELECT COUNT(orders.bundle_id) AS numBundles, booking.session_id, menu.menu_name " . " From booking " . " Inner Join session ON session.id = booking.session_id " . " Inner Join menu ON menu.id = session.menu_id " . " Inner Join orders ON booking.order_id = orders.id and orders.bundle_id is not null and orders.bundle_id > 0 and orders.type_of_order = 'INTRO' " . " Where booking.status = 'ACTIVE' and booking.is_deleted = 0 AND session.is_deleted = 0 and " . " session.id in ( $sessionList ) " . " group by session.id";

		$session->query($varStr);
		$count = 0;
		while ($session->fetch())
		{
			$count += $session->numBundles;
		}

		return $count;
	}

	function getDeliveredEntreeCounts($store_id, $Day, $Month, $Year, $Interval = '1 DAY')
	{
		$storeClause = "";
		if (!empty($store_id) && is_numeric($store_id))
		{
			$storeClause = " o.store_id = " . $store_id . "\r\n";
		}
		else
		{
			$dcGetter = new DAO();
			$dcGetter->query("select GROUP_CONCAT(id) as DCs from store where store_type = 'DISTRIBUTION_CENTER' and is_deleted = 0");
			$dcGetter->fetch();
			$storeClause = " o.store_id in (" . $dcGetter->DCs . ") ";
		}

		$varStr = "";
		$session = DAO_CFactory::create("session");
		$session->query('SET group_concat_max_len = 100000;');
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$varStr = "Select s.menu_id,
						os.ship_date,
						s.is_deleted,
						GROUP_CONCAT(bu.menu_id, ':', menu_item.id,':', order_item.item_count,':', order_item.sub_total) AS Menu_Items, 
						booking.session_id,
						count(distinct order_item.order_id) as numOrders
									 From booking
										Inner Join orders o on o.id = booking.order_id and $storeClause
										Inner Join orders_shipping os on os.order_id = o.id and os.ship_date >= '$current_date_sql' AND os.ship_date <  DATE_ADD('$current_date_sql', INTERVAL $Interval ) 
										inner join session s on booking.session_id = s.id
										Inner Join order_item ON booking.order_id = order_item.order_id and order_item.is_deleted = 0 
									    Inner Join bundle bu on bu.id = order_item.bundle_id
									  	Left Join menu_item ON menu_item.id = order_item.menu_item_id  join orders on orders.id = booking.order_id
						Where booking.status = 'ACTIVE' and booking.is_deleted = 0 
						 group by os.ship_date
						 order by menu_item.id Asc";

		$session->query($varStr);
		$rows = array();
		$count = 0;
		while ($session->fetch())
		{
			$rows [$count++] = $session->toArray();
		}

		return ($rows);
	}

	function getEntreeCounts($store_id, $Day, $Month, $Year, $Interval = '1 DAY', $selectedSessionTypes = array(),$queryDelivered = false)
	{
		$storeClause = "";
		if (!empty($store_id) && is_numeric($store_id))
		{
			$storeClause = "AND session.store_id = " . $store_id . " ";
		}

		$storeTypeClause = " and st.store_type = '" . CStore::FRANCHISE . "' ";
		if ($queryDelivered)
		{
			$storeTypeClause = " and st.store_type = '" . CStore::DISTRIBUTION_CENTER . "' ";
		}

		$sessionTypeClause = self::generateWhereClauseForSessionType($selectedSessionTypes,'session');
		if($sessionTypeClause != ''){
			$sessionTypeClause = ' and ' .$sessionTypeClause;
		}

		$varStr = "";
		$session = DAO_CFactory::create("session");
		$session->query('SET group_concat_max_len = 100000;');
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$varStr = "SELECT 
       		session.id as session_id,
			session.menu_id,
			session.session_start,
			session.is_deleted,
			GROUP_CONCAT(menu_item.id,':', order_item.item_count,':',order_item.sub_total) AS Menu_Items,
			GROUP_CONCAT(product.id,':',order_item.item_count) AS Product_Items,
			booking.session_id,
			count(distinct order_item.order_id) as numOrders,
			count(distinct if (orders.servings_total_count > 71, order_item.order_id, null)) as numOrdersGreaterThan72servings
			From booking
			INNER JOIN session ON session.id = booking.session_id " . $storeClause . " AND session_publish_state != 'SAVED' AND session_start >= '" . $current_date_sql . "' AND session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) AND session.is_deleted = 0
			INNER JOIN store as st on session.store_id = st.id " . $storeTypeClause . "
			INNER JOIN order_item ON booking.order_id = order_item.order_id AND order_item.is_deleted = 0
			LEFT JOIN product ON product.id = order_item.product_id
			LEFT JOIN menu_item ON menu_item.id = order_item.menu_item_id  join orders on orders.id = booking.order_id 
			LEFT JOIN session_properties ON session_properties.session_id = session.id  
			LEFT JOIN dream_taste_event_properties ON dream_taste_event_properties.id = session_properties.dream_taste_event_id  
			LEFT JOIN dream_taste_event_theme ON dream_taste_event_theme.id = dream_taste_event_properties.dream_taste_event_theme 
			where booking.status = 'ACTIVE' 
			AND booking.is_deleted = 0 
			$sessionTypeClause
			GROUP BY session.id 
			ORDER BY menu_item.id ASC";

		$session->query($varStr);
		$rows = array();
		$count = 0;

		while ($session->fetch())
		{
			$rows [$count] = $session->toArray();
			try
			{

				$sessionQbe = DAO_CFactory::create("session");
				$sessionQbe->id = $session->session_id;
				$sessionQbe->find(true);

				list ($rows[$count]['session_type_title'], $rows[$count]['session_type_title_public'], $rows[$count]['session_type_title_short'], $rows[$count]['session_type_fadmin_acronym'], $rows[$count]['session_type_string']) = $sessionQbe->getSessionTypeProperties();
			}catch(Exception $e){
				//not worth killing report if session can't be found, should not happen
			}
			$count++;
		}

		return ($rows);
	}

	// Business Rules imposed here..
	// VALUE menu type == 6 Servings
	// VARIETY menu type == 3 Servings
	// INTRO menu type == 6 Servings
	// QUICK & HEARTY == 6 Servings

	//unused - using servings_per_item now
	function getServingSize($menu_plan_type)
	{
		$servingSize = 0;
		switch ($menu_plan_type)
		{
			//case CMenuItem::QUICK_HEARTY:
			case CMenuItem::LEGACY:
			case CMenuItem::FULL:
			case CMenuItem::INTRO:
				$servingSize = CMenuItem::FULL_SERVING_SIZE;
				break;
			case CMenuItem::HALF:
				$servingSize = CMenuItem::HALF_SERVING_SIZE;
				break;
		} // switch

		return $servingSize;
	}

	function getContainerTypes($menu_id, $store_id = false)
	{

		$menu_item_details = array();
		$varStr = "";
		$menu_item = DAO_CFactory::create("menu_to_menu_item");
		$varStr = "SELECT menu_item.servings_per_item, menu_item.id,menu_item.container_type, pricing_type FROM menu_to_menu_item " . "INNER JOIN menu_item ON menu_to_menu_item.menu_item_id = menu_item.id ";

		$nonSSM_menu_array = array();
		$SSM_menu_array = array();

		// split menu array into store specific and non-store specific menus
		if (!is_array($menu_id))
		{
			if ($menu_id < 79)
			{
				$nonSSM_menu_array[] = $menu_id;
			}
			else
			{
				$hasStoreMenu = ($store_id && CMenu::storeSpecificMenuExists($menu_id, $store_id));

				if ($hasStoreMenu)
				{
					$SSM_menu_array[] = $menu_id;
				}
				else
				{
					$nonSSM_menu_array[] = $menu_id;
				}
			}
		}
		else
		{
			foreach ($menu_id as $thisID)
			{
				if ($thisID < 79)
				{
					$nonSSM_menu_array[] = $thisID;
				}
				else
				{
					$hasStoreMenu = ($store_id && CMenu::storeSpecificMenuExists($thisID, $store_id));

					if ($hasStoreMenu)
					{
						$SSM_menu_array[] = $thisID;
					}
					else
					{
						$nonSSM_menu_array[] = $thisID;
					}
				}
			}
		}

		if (!empty($nonSSM_menu_array))
		{

			$listClause = count($nonSSM_menu_array) == 1 ? $nonSSM_menu_array[0] : implode(",", $nonSSM_menu_array);
			$varStr .= "Where (menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id is null and menu_item.is_optional = 0) ";
		}

		if (!empty($SSM_menu_array))
		{

			if (!empty($nonSSM_menu_array))
			{
				$varStr .= " or ";
			}
			else
			{
				$varStr .= " Where ";
			}

			$listClause = count($SSM_menu_array) == 1 ? $SSM_menu_array[0] : implode(",", $SSM_menu_array);
			$varStr .= "(menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id = $store_id) and menu_to_menu_item.is_deleted = 0";
		}

		//if (!is_array($menu_id)) $varStr .= "Where menu_to_menu_item.menu_id =" . $menu_id;
		//else $varStr .= "Where menu_to_menu_item.menu_id IN (" . implode("," , $menu_id) . ") $storeClause ";

		$menu_item->query($varStr);
		$count = 0;
		while ($menu_item->fetch())
		{
			$arr = $menu_item->toArray();
			$servingSize = $arr['servings_per_item']; //$this->getServingSize($arr['pricing_type']);
			$menu_item_details [$arr['id']] = array(
				'plan' => $arr['pricing_type'],
				'container' => $arr['container_type'],
				'size' => $servingSize
			);
		}

		return ($menu_item_details);
	}

	function getContainerTypesDelivered($menu_id, $store_id = false)
	{

		$menu_item_details = array();
		$varStr = "";
		$menu_item = DAO_CFactory::create("menu_to_menu_item");
		$varStr = "SELECT menu_item.servings_per_item, menu_item.id,menu_item.container_type, pricing_type FROM menu_to_menu_item " . "INNER JOIN menu_item ON menu_to_menu_item.menu_item_id = menu_item.id ";
		$SSM_menu_array = array();

		// split menu array into store specific and non-store specific menus

		foreach ($menu_id as $thisID)
		{
			$SSM_menu_array[] = $thisID;
		}

		$listClause = implode(",", $SSM_menu_array);

		if ($store_id)
		{
			$varStr .= " Where (menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id = $store_id) and menu_to_menu_item.is_deleted = 0";
		}
		else
		{
			$varStr .= "Where (menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id is null and menu_item.is_optional = 0) ";
		}

		$menu_item->query($varStr);
		$count = 0;
		while ($menu_item->fetch())
		{
			$arr = $menu_item->toArray();
			$servingSize = $arr['servings_per_item']; //$this->getServingSize($arr['pricing_type']);
			$menu_item_details [$arr['id']] = array(
				'plan' => $arr['pricing_type'],
				'container' => $arr['container_type'],
				'size' => $servingSize
			);
		}

		return ($menu_item_details);
	}

	function createSessionTabsArray($store_id, $day, $month, $year)
	{
		$tab_data = array();
		$Session = DAO_CFactory::create("session");
		$Session->findDailySessions($store_id, $day, $month, $year);
		$count = 0;
		while ($Session->fetch())
		{
			$tab_data [$count++] = $Session->toArray();
		}

		return $tab_data;
	}

	//  FUNCTION NO LONGER IN USE (ENTREE REPORT FULL YEAR QUERY )
	function generateMenuArrayOnStartDate($day, $month, $year, $duration, &$menu_arr)
	{

		$current_date = mktime(0, 0, 0, $month, $day, $year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$menuitems = DAO_CFactory::create("menu_to_menu_item");
		$menu = DAO_CFactory::create("menu");
		$menu_item = DAO_CFactory::create("menu_item");
		$menuitems->joinAdd($menu);
		$menuitems->joinAdd($menu_item);
		$menuitems->selectAdd();
		$fieldlist = 'menu_item.servings_per_item,menu_item.is_preassembled, menu_item.is_side_dish, menu_item.is_kids_choice, menu_item.is_menu_addon, menu_item.is_chef_touched, menu_item.is_bundle, menu_item.``,entree_id, GROUP_CONCAT(menu_item_id) as entree_ids, menu.id, menu.menu_name, menu_item.menu_item_name,menu_item.menu_item_description,menu_to_menu_item.menu_item_id, menu_start';
		$menuitems->selectAdd($fieldlist);
		$menuitems->whereAdd("menu.is_deleted = 0");
		$menuitems->whereAdd("menu_item.is_deleted = 0");
		$menuitems->whereAdd("menu_to_menu_item.is_deleted = 0");
		$menuitems->whereAdd("menu.menu_start >= '" . $current_date_sql . "'");
		$varstr = "menu_start < DATE_ADD(' " . $current_date_sql . "', INTERVAL " . $duration . " )";

		$menuitems->whereAdd($varstr);     // DATE_ADD('2005-01-01 00:00:00', INTERVAL 1 YEAR )
		//$menuitems->orderBy('menu.id, menu_to_menu_item.menu_order_value');

		$menuitems->orderBy('menu_to_menu_item.featuredItem DESC, menu_to_menu_item.menu_order_value ASC');
		$menuitems->groupBy("entree_id");
		$menuitems->find();
		$rows = array();
		$count = 0;

		while ($menuitems->fetch())
		{

			$menu_item_id = $menuitems->menu_item_id;
			$menu_id = $menuitems->id;
			$menu_item_name = $menuitems->menu_item_name;
			$menu_name = $menuitems->menu_name;
			$entree_array = explode(",", $menuitems->entree_ids);
			if (in_array($menuitems->menu_start, $menu_arr) == false)
			{
				//$menu_arr[$count++] = $menuitems->menu_start;
				$menu_arr[$menuitems->menu_start] = $menu_name;
			}

			if (array_key_exists($menu_item_id, $rows))
			{
				$obj = $rows[$menu_item_id];
				$obj->AddMenu($menu_id, $menu_name);
			}
			else
			{
				$instance = new CEntreeItem($menu_item_id, $menu_id, $menu_item_name, $menu_name, $entree_array);

				if (!empty($menuitems->menu_item_category_id))
				{
					$category = $this->getCategories($menuitems->menu_item_category_id);
					$instance->category_name = $category['category_type'];
					$instance->category_ordering = $category['global_order_value'];
					//$category_ordering
				}
				$instance->is_side_dish = $menuitems->is_side_dish;
				$instance->is_kids_choice = $menuitems->is_kids_choice;
				$instance->is_menu_addon = $menuitems->is_menu_addon;
				$instance->is_chef_touched = $menuitems->is_chef_touched;
				$instance->is_bundle = $menuitems->is_bundle;
				$instance->is_preassembled = $menuitems->is_preassembled;
				$instance->menu_item_category_id = $menuitems->menu_item_category_id;

				$rows [$menuitems->entree_id] = $instance;
			}
		}

		ksort($menu_arr);

		return ($rows);
	}

	function generateMenuArrayNItems($menu_array, &$menu_name_array, $container_type_array, $store_id = false)
	{

		$nonSSM_menu_array = array();
		$SSM_menu_array = array();

		// split menu array into store specific and non-store specific menus
		if (!is_array($menu_array))
		{
			if ($menu_array < 79)
			{
				$nonSSM_menu_array[] = $menu_array;
			}
			else
			{
				$hasStoreMenu = ($store_id && CMenu::storeSpecificMenuExists($menu_array, $store_id));

				if ($hasStoreMenu)
				{
					$SSM_menu_array[] = $menu_array;
				}
				else
				{
					$nonSSM_menu_array[] = $menu_array;
				}
			}
		}
		else
		{
			foreach ($menu_array as $thisID)
			{
				if ($thisID < 79)
				{
					$nonSSM_menu_array[] = $thisID;
				}
				else
				{
					$hasStoreMenu = ($store_id && CMenu::storeSpecificMenuExists($thisID, $store_id));

					if ($hasStoreMenu)
					{
						$SSM_menu_array[] = $thisID;
					}
					else
					{
						$nonSSM_menu_array[] = $thisID;
					}
				}
			}
		}

		$storeClause = "";

		if (!empty($nonSSM_menu_array))
		{

			$listClause = count($nonSSM_menu_array) == 1 ? $nonSSM_menu_array[0] : implode(",", $nonSSM_menu_array);
			$storeClause .= " (menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id is null and menu_item.is_optional = 0) ";
		}

		if (!empty($SSM_menu_array))
		{

			if (!empty($nonSSM_menu_array))
			{
				$storeClause .= " or ";
			}

			$listClause = count($SSM_menu_array) == 1 ? $SSM_menu_array[0] : implode(",", $SSM_menu_array);
			$storeClause .= "(menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id = $store_id) ";
		}

		$storeClause = " AND (" . $storeClause . ")";

		$varCounter = 0;

		$varstr = "SELECT
			menu_item.station_number, menu_item.is_store_special,
			menu_item.is_preassembled, menu_item.is_side_dish, menu_item.is_kids_choice,
			menu_item.is_menu_addon, menu_item.is_chef_touched, menu_item.is_bundle,
			menu_item.`menu_item_category_id`, entree_id, GROUP_CONCAT(menu_item_id) AS entree_ids,
			GROUP_CONCAT(menu_item.servings_per_item) AS serv_sizes, menu_to_menu_item.menu_item_id,
			menu_item.menu_item_name,menu.menu_name,menu_to_menu_item.menu_id, menu_start, menu_item.recipe_id
			FROM menu
			INNER JOIN menu_item
			INNER JOIN menu_to_menu_item ON menu_item.id = menu_to_menu_item.menu_item_id AND menu.id = menu_to_menu_item.menu_id and menu_to_menu_item.is_deleted = 0
			" . $storeClause . "
			WHERE menu.is_deleted = 0 AND menu_item.is_deleted = 0 ";

		$var = 0;
		foreach ($menu_array as $menuvalue)
		{
			if ($var == 0)
			{
				$varstr .= " and ";
			}
			$varstr = $varstr . " menu.id = " . $menuvalue;

			if (count($menu_array) > 1 && $var < count($menu_array) - 1)
			{
				$varstr = $varstr . ' OR ';
			}
			$var++;
		}

		////$varstr = $varstr . " Order By menu_id, menu_item.menu_item_name Asc";
		$varstr = $varstr . " Group by entree_id Order By menu_to_menu_item.featuredItem DESC, menu_to_menu_item.menu_order_value ASC";
		$menuitems = DAO_CFactory::create("menu_to_menu_item");
		$menuitems->query($varstr);
		$rows = array();

		$count = 0;

		while ($menuitems->fetch())
		{
			$obj = null;
			$menu_item_id = $menuitems->menu_item_id;
			$menu_id = $menuitems->menu_id;
			$menu_item_name = $menuitems->menu_item_name;
			$menu_name = $menuitems->menu_name;
			$recipe_id = $menuitems->recipe_id;
			$entree_array = explode(",", $menuitems->entree_ids);
			$entree_id = $menuitems->entree_id;

			$servs = explode(",", $menuitems->serv_sizes);
			$tempservars = array();
			$countserv = 0;
			foreach ($entree_array as $elementserv)
			{
				$tempservars[$elementserv] = $servs[$countserv];
				$countserv++;
			}
			$serving_array = $tempservars;

			if (in_array($menuitems->menu_start, $menu_name_array) == false)
			{
				// $menu_name_array[$count++] = $menuitems->menu_start;
				$menu_name_array[$menuitems->menu_start] = $menu_name;
			}
			//if (array_key_exists($menu_item_id , $rows )) {
			if (array_key_exists($entree_id, $rows))
			{
				$obj = $rows[$entree_id];
			}

			if ($obj != null)
			{
				$obj->AddMenu($menu_id, $menu_name);
			}
			else
			{
				//$instance = new CEntreeItem($menu_item_id, $menu_id, $menu_item_name, $menu_name, $entree_array);
				$instance = new CEntreeItem($entree_id, $menu_id, $menu_item_name, $recipe_id, $menu_name, $entree_array, $serving_array);
				//$rows [$count++]= $menuitems->toArray();
				//$rows [$menu_item_id]= $instance;

				if (!empty($menuitems->menu_item_category_id))
				{
					$category = $this->getCategories($menuitems->menu_item_category_id);
					$instance->category_name = $category['category_type'];
					$instance->category_ordering = $category['global_order_value'];
				}

				$instance->station_number = $menuitems->station_number;
				$instance->is_store_special = $menuitems->is_store_special;
				$instance->is_side_dish = $menuitems->is_side_dish;
				$instance->is_kids_choice = $menuitems->is_kids_choice;
				$instance->is_menu_addon = $menuitems->is_menu_addon;
				$instance->is_chef_touched = $menuitems->is_chef_touched;
				$instance->is_bundle = $menuitems->is_bundle;
				$instance->is_preassembled = $menuitems->is_preassembled;
				$instance->menu_item_category_id = $menuitems->menu_item_category_id;

				$rows [$entree_id] = $instance;
			}
		}

		ksort($menu_name_array);

		return ($rows);
	}

	function generateMenuArrayNItemsDelivered($menu_array, &$menu_name_array, $container_type_array, $store_id = false)
	{

		$SSM_menu_array = array();

		// split menu array into store specific and non-store specific menus

		foreach ($menu_array as $thisID)
		{
			$SSM_menu_array[] = $thisID;
		}

		$listClause = count($SSM_menu_array) == 1 ? $SSM_menu_array[0] : implode(",", $SSM_menu_array);

		$storeClause = "";
		if (!$store_id)
		{

			$dcGetter = new DAO();
			$dcGetter->query("select GROUP_CONCAT(id) as DCs from store where store_type = 'DISTRIBUTION_CENTER' and is_deleted = 0");
			$dcGetter->fetch();

			$storeClause .= " (menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id in ({$dcGetter->DCs}) and menu_item.is_optional = 0) ";
			$storeClause = " AND (" . $storeClause . ")";
		}
		else
		{
			$storeClause .= "(menu_to_menu_item.menu_id IN ($listClause) and menu_to_menu_item.store_id = $store_id) ";
			$storeClause = " AND (" . $storeClause . ")";
		}

		$varCounter = 0;

		$varstr = "SELECT
			menu_item.station_number, menu_item.is_store_special,
			menu_item.is_preassembled, menu_item.is_side_dish, menu_item.is_kids_choice,
			menu_item.is_menu_addon, menu_item.is_chef_touched, menu_item.is_bundle,
			menu_item.`menu_item_category_id`, entree_id, GROUP_CONCAT(menu_item_id) AS entree_ids,
			GROUP_CONCAT(menu_item.servings_per_item) AS serv_sizes, menu_to_menu_item.menu_item_id,
			menu_item.menu_item_name,menu.menu_name,menu_to_menu_item.menu_id, menu_start, menu_item.recipe_id
			FROM menu
			INNER JOIN menu_item
			INNER JOIN menu_to_menu_item ON menu_item.id = menu_to_menu_item.menu_item_id AND menu.id = menu_to_menu_item.menu_id and menu_to_menu_item.is_deleted = 0
			" . $storeClause . "
			WHERE menu.is_deleted = 0 AND menu_item.is_deleted = 0 ";

		$var = 0;
		foreach ($menu_array as $menuvalue)
		{
			if ($var == 0)
			{
				$varstr .= " and ";
			}
			$varstr = $varstr . " menu.id = " . $menuvalue;

			if (count($menu_array) > 1 && $var < count($menu_array) - 1)
			{
				$varstr = $varstr . ' OR ';
			}
			$var++;
		}

		////$varstr = $varstr . " Order By menu_id, menu_item.menu_item_name Asc";
		$varstr = $varstr . " Group by entree_id Order By menu_to_menu_item.featuredItem DESC, menu_to_menu_item.menu_order_value ASC";
		$menuitems = DAO_CFactory::create("menu_to_menu_item");
		$menuitems->query($varstr);
		$rows = array();

		$count = 0;

		while ($menuitems->fetch())
		{
			$obj = null;
			$menu_item_id = $menuitems->menu_item_id;
			$menu_id = $menuitems->menu_id;
			$menu_item_name = $menuitems->menu_item_name;
			$menu_name = $menuitems->menu_name;
			$recipe_id = $menuitems->recipe_id;
			$entree_array = explode(",", $menuitems->entree_ids);
			$entree_id = $menuitems->entree_id;

			$servs = explode(",", $menuitems->serv_sizes);
			$tempservars = array();
			$countserv = 0;
			foreach ($entree_array as $elementserv)
			{
				$tempservars[$elementserv] = $servs[$countserv];
				$countserv++;
			}
			$serving_array = $tempservars;

			if (in_array($menuitems->menu_start, $menu_name_array) == false)
			{
				// $menu_name_array[$count++] = $menuitems->menu_start;
				$menu_name_array[$menuitems->menu_start] = $menu_name;
			}
			//if (array_key_exists($menu_item_id , $rows )) {
			if (array_key_exists($entree_id, $rows))
			{
				$obj = $rows[$entree_id];
			}

			if ($obj != null)
			{
				$obj->AddMenu($menu_id, $menu_name);
			}
			else
			{
				//$instance = new CEntreeItem($menu_item_id, $menu_id, $menu_item_name, $menu_name, $entree_array);
				$instance = new CEntreeItem($entree_id, $menu_id, $menu_item_name, $recipe_id, $menu_name, $entree_array, $serving_array);
				//$rows [$count++]= $menuitems->toArray();
				//$rows [$menu_item_id]= $instance;

				if (!empty($menuitems->menu_item_category_id))
				{
					$category = $this->getCategories($menuitems->menu_item_category_id);
					$instance->category_name = $category['category_type'];
					$instance->category_ordering = $category['global_order_value'];
				}

				$instance->station_number = $menuitems->station_number;
				$instance->is_store_special = $menuitems->is_store_special;
				$instance->is_side_dish = $menuitems->is_side_dish;
				$instance->is_kids_choice = $menuitems->is_kids_choice;
				$instance->is_menu_addon = $menuitems->is_menu_addon;
				$instance->is_chef_touched = $menuitems->is_chef_touched;
				$instance->is_bundle = $menuitems->is_bundle;
				$instance->is_preassembled = $menuitems->is_preassembled;
				$instance->menu_item_category_id = $menuitems->menu_item_category_id;

				$rows [$entree_id] = $instance;
			}
		}

		ksort($menu_name_array);

		return ($rows);
	}

	function generateMenuArray($menuid, $store_id = null)
	{

		$menuitems = DAO_CFactory::create("menu_to_menu_item");
		$menu = DAO_CFactory::create("menu");
		$menu_item = DAO_CFactory::create("menu_item");
		$menuitems->joinAdd($menu);
		$menuitems->joinAdd($menu_item);
		$menuitems->selectAdd();
		$fieldlist = 'menu_item.recipe_id as recipe_id, menu_item.is_preassembled as preassembled,menu_item.is_store_special as storespecial, menu_item.is_side_dish as sidedishes, menu_item.is_bundle as is_bundle, menu_item.is_kids_choice as kidschoices, ' . ' menu_item.is_menu_addon as menuaddons, menu_item.is_chef_touched as is_chef_touched, menu_item.menu_item_category_id as categories, menu_item.station_number, ' . ' GROUP_CONCAT(servings_per_item) as serv_sizes,GROUP_CONCAT(menu_item_id) as m_ids, menu_item.menu_item_name,menu_item.menu_item_description, ' . ' menu_to_menu_item.menu_item_id, menu_item.entree_id';
		$menuitems->selectAdd($fieldlist);
		$menuitems->whereAdd("menu.is_deleted = 0");
		$menuitems->whereAdd("menu_item.is_deleted = 0");
		$menuitems->whereAdd("menu_to_menu_item.is_deleted = 0");
		$menuitems->whereAdd("menu.id  = " . $menuid);

		if (!empty($store_id) && $menuid > 78)
		{
			$hasStoreMenu = ($store_id && CMenu::storeSpecificMenuExists($menuid, $store_id));

			if ($hasStoreMenu)
			{
				// need to access store specific menu
				$menuitems->whereAdd("menu_to_menu_item.store_id = $store_id");
			}
			else
			{
				$menuitems->whereAdd("menu_to_menu_item.store_id is null and menu_item.is_optional = 0");
			}
		}
		else
		{
			$menuitems->whereAdd("menu_to_menu_item.store_id is null");
		}
		$menuitems->groupBy('menu_item.entree_id');
		$menuitems->orderBy('menu_to_menu_item.featuredItem DESC, menu_to_menu_item.menu_order_value ASC');

		$menuitems->find();
		$rows = array();
		$count = 0;

		$categoryids = array();

		while ($menuitems->fetch())
		{
			$arr = $menuitems->toArray();
			$mids = explode(",", $arr['m_ids']);
			$rows [$count]['menu_array_ids'] = $mids;

			$servs = explode(",", $arr['serv_sizes']);
			$tempservars = array();
			$countserv = 0;
			foreach ($mids as $elementserv)
			{
				$tempservars[$elementserv] = $servs[$countserv];
				$countserv++;
			}
			$rows [$count]['servings_sizes'] = $tempservars;

			$rows [$count]['categories'] = null;
			$rows [$count]['preassembled'] = null;
			$rows [$count]['storespecial'] = null;
			$rows [$count]['sidedishes'] = null;
			$rows [$count]['is_bundle'] = false;
			$rows [$count]['is_chef_touched'] = false;
			$rows [$count]['station_number'] = $menuitems->station_number;

			if (!empty($arr['categories']))
			{

				$rows [$count]['categories'] = $arr['categories'];

				$rows [$count]['preassembled'] = $arr['preassembled'];

				$rows [$count]['storespecial'] = $arr['storespecial'];;

				$rows [$count]['sidedishes'] = $arr['sidedishes'];

				$rows [$count]['is_bundle'] = $arr['is_bundle'] == 1 ? true : false;
				$rows [$count]['is_chef_touched'] = $arr['is_chef_touched'] == 1 ? true : false;

				$categoryids[$count] = $arr['categories'];
			}

			$rows [$count]['entree_id'] = $arr['entree_id'];
			$rows [$count]['recipe_id'] = $arr['recipe_id'];
			$rows [$count]['menu_item_name'] = $arr['menu_item_name'];
			$rows [$count]['menu_item_description'] = $arr['menu_item_description'];
			$rows [$count]['menu_item_id'] = $arr['menu_item_id'];
			$count++;
		}

		$categoryArray = array();

		// if for some reason stores are allowed to override categories then we will need to further define this.
		if (!empty($categoryids))
		{
			$vararr = implode(',', $categoryids);
			$categories = DAO_CFactory::create("menu_item_category");
			$sql = "SELECT `menu_item_category`.`id`,`menu_item_category`.`category_type`,`menu_item_category`.`global_order_value` " . " FROM `menu_item_category` where `menu_item_category`.`id` in ($vararr) order by `global_order_value` asc";
			$categories->query($sql);
			while ($categories->fetch())
			{
				$categoryArray[$categories->id] = array(
					$categories->category_type,
					$categories->global_order_value
				);
			}

			foreach ($rows as $key => $elements)
			{
				$id = $elements['categories'];
				$rows[$key]['categories_type'] = $categoryArray[$id][0];
				$rows[$key]['global_order_value'] = $categoryArray[$id][1];
			}
		}

		sort($categoryids);
		if (!empty($categoryids))
		{
			$temprows = array();
			foreach ($rows as $key => $elements)
			{
				$temprows[$elements['global_order_value']][$key] = $elements;
			}

			$rows = null;
			ksort($temprows);

			foreach ($temprows as $key => $elements)
			{
				foreach ($elements as $subkey => $subelements)
				{
					$rows[$subkey] = $subelements;
				}
			}
		}

		return ($rows);
	}

	/**
	 * @return updateMenuCount Menu count comes from concat Group.. this method sums all similar counts
	 */

	function updateMenuCount(&$menu_array, $menuitemid, $menucount, $servingSize, $isIntro, $isLegacy)
	{

		$varCounter = -1;
		foreach ($menu_array as $menuvalue)
		{
			$varmenuid = $menuvalue['id'];
			$varCounter++;

			if (array_search($menuitemid, $menuvalue['sub_menu_ids']) === false)
			{
				continue;
			}
			else
			{

				$tempCount = $menu_array[$varCounter]['count'];
				$menu_array[$varCounter]['count'] = $tempCount + $menucount;

				$menu_array[$varCounter]['servings_sold'] += ($menucount * $servingSize);

				$isShiftSetGo = CBundle::isShiftSetGoBundle($menu_array[$varCounter]['recipe_id'],$menu_array[$varCounter]['menu_name']);
				if($isShiftSetGo){
					if ($servingSize == 18)
					{
						$menu_array[$varCounter]['actual_dinners'] += (1 * $menucount);
					}
					else if ($servingSize == 36)
					{
						$menu_array[$varCounter]['actual_dinners'] += (1 * $menucount);
					}
				}
				else if ($servingSize == 3)
				{
					$menu_array[$varCounter]['actual_dinners'] += (.5 * $menucount);
				}
				else if ($servingSize == 6)
				{
					$menu_array[$varCounter]['actual_dinners'] += (1 * $menucount);
				}
				else if ($servingSize == 9)
				{
					$menu_array[$varCounter]['actual_dinners'] += (.5 * $menucount);
				}
				else if ($servingSize == 18)
				{
					$menu_array[$varCounter]['actual_dinners'] += (1 * $menucount);
				}


				if ($isShiftSetGo == true)
				{
					if ($servingSize == 18)
					{
						$tempCount = $menu_array[$varCounter][CMenuItem::HALF];
						$menu_array[$varCounter][CMenuItem::HALF] = $tempCount + $menucount;
					}
					else if ($servingSize == 36)
					{
						$tempCount = $menu_array[$varCounter][CMenuItem::FULL];
						$menu_array[$varCounter][CMenuItem::FULL] = $tempCount + $menucount;
					}
				}
				else if ($isIntro == true)
				{
					$tempCount = $menu_array[$varCounter][CMenuItem::INTRO];
					$menu_array[$varCounter][CMenuItem::INTRO] = $tempCount + $menucount;
				}
				else if ($servingSize == CMenuItem::HALF_SERVING_SIZE)
				{
					$tempCount = $menu_array[$varCounter][CMenuItem::HALF];
					$menu_array[$varCounter][CMenuItem::HALF] = $tempCount + $menucount;
				}
				else if ($servingSize == CMenuItem::FULL_SERVING_SIZE)
				{
					$tempCount = $menu_array[$varCounter][CMenuItem::FULL];
					$menu_array[$varCounter][CMenuItem::FULL] = $tempCount + $menucount;
				}
				else if ($servingSize == CMenuItem::TWO_SERVING_SIZE)
				{
					$tempCount = $menu_array[$varCounter][CMenuItem::TWO];
					$menu_array[$varCounter][CMenuItem::TWO] = $tempCount + $menucount;
				}
				else if ($servingSize == CMenuItem::FOUR_SERVING_SIZE)
				{
					$tempCount = $menu_array[$varCounter][CMenuItem::FOUR];
					$menu_array[$varCounter][CMenuItem::FOUR] = $tempCount + $menucount;
				}
				else if ($servingSize == 9)
				{
					$tempCount = $menu_array[$varCounter][CMenuItem::HALF];
					$menu_array[$varCounter][CMenuItem::HALF] = $tempCount + $menucount;
				}
				else if ($servingSize == 18)
				{
					$tempCount = $menu_array[$varCounter][CMenuItem::FULL];
					$menu_array[$varCounter][CMenuItem::FULL] = $tempCount + $menucount;
				}

				if (!empty($menuvalue['sidedishes']) && $menuvalue['sidedishes'] == 1)
				{

					if ($servingSize == CMenuItem::FULL_SERVING_SIZE)
					{
						$menu_array[$varCounter]['SIDE'][CMenuItem::FULL] = $tempCount + $menucount;
					}
					else
					{
						$menu_array[$varCounter]['SIDE'][CMenuItem::HALF] = $tempCount + $menucount;
					}
				}

				//$promo_large =$menu_array[$varCounter]['PROMO_FULL'] ;
				//$promo_med =$menu_array[$varCounter]['PROMO_HALF'] ;

				//if ($promo_large > 0 && $menu_array[$varCounter][CMenuItem::LEGACY] > 0) {
				//    $menu_array[$varCounter][CMenuItem::FULL] -= $promo_large;
				//}
				//if ($promo_large > 0 && $menu_array[$varCounter][CMenuItem::FULL] > 0) {
				//    $menu_array[$varCounter][CMenuItem::FULL] -= $promo_large;
				//}
				//if ($promo_med > 0 && $menu_array[$varCounter][CMenuItem::HALF] > 0) {
				//    $menu_array[$varCounter][CMenuItem::HALF] -= $promo_med;
				//}

				//$menu_array[$varCounter][CMenuItem::INTRO] = $isIntro;
				//$menu_array[$varCounter][CMenuItem::LEGACY] = $isLegacy;

				break;
			}
		}
	}

	// this function runs through the arrays and checks to see if there aren more than 1 menu.id (could span menus)
	function findAllMenuIDs($row_data)
	{

		$menuids = array();
		$varCounter = 0;
		foreach ($row_data as $menuvalue)
		{

			$varmenuid = $menuvalue['menu_id'];
			if (!in_array($varmenuid, $menuids))
			{
				$menuids[$varCounter] = $varmenuid;
				$varCounter++;
			}
		}

		return $menuids;
	}

	// this function runs through the arrays and checks to see if there aren more than 1 menu.id (could span menus)
	function findAllMenuIDsDelivered($row_data)
	{

		$menuids = array();
		$varCounter = 0;
		foreach ($row_data as $dayData)
		{
			$items = explode(",", $dayData['Menu_Items']);

			foreach ($items as $thisItem)
			{
				$itemData = explode(":", $thisItem);
				$varmenuid = $itemData[0];
				if (!in_array($varmenuid, $menuids))
				{
					$menuids[$varCounter] = $varmenuid;
					$varCounter++;
				}
			}
		}

		return $menuids;
	}

	function setupDisplayByStation(&$menu_array)
	{
		$normalSortCoreFl = 200000;
		$normalSortEFl = 300000;
		$normalSortMisc = 100000;
		$normalSortSS = 400000;
		$count = 0;

		foreach ($menu_array as &$thisItem)
		{

			if (!empty($thisItem['station_number']) && $thisItem['categories_type'] != "Chef Touched Selections")
			{
				$sortOrder = ($thisItem['station_number'] * 100) + $count++;

				$thisItem['categories_type'] = "Station " . $thisItem['station_number'];
				$thisItem['station_sort'] = $sortOrder;
			}
			else
			{
				if($thisItem['categories_type'] == "Fast Lane" && $thisItem['storespecial'] == "0" && $thisItem['preassembled'] == "1")
				{
					$thisItem['station_sort'] = $normalSortCoreFl++;
				}
				elseif($thisItem['categories_type'] == "Fast Lane" && $thisItem['storespecial'] == "1" && $thisItem['preassembled'] == "1")
				{
					$thisItem['station_sort'] = $normalSortEFl++;
				}
				elseif($thisItem['categories_type'] == "Chef Touched Selections")
				{
					$thisItem['station_sort'] = $normalSortSS++;
				}
				else
				{
					$thisItem['station_sort'] = $normalSortMisc++;
				}
			}
		}

		usort($menu_array, 'stationCompare');
	}

	/**
	 * @return buildMenuItemsArray
	 */

	function buildMenuItemsArray($generalArray, $menu_array, $addDescription, $containerArray, $promo_items)
	{
		$isIntro = false;
		$isLegacy = false;
		$sessionEntreeTotalArray = array();
		$entreeCount = 0;
		foreach ($menu_array as $keymen => $fullMenu)
		{
			$subArray = array();
			$subArray['id'] = $fullMenu['menu_item_id'];
			$subArray['menu_name'] = $fullMenu['menu_item_name'];
			$subArray['count'] = 0;
			$subArray['recipe_id'] = $fullMenu['recipe_id'];

			if (!empty($menu_array[$keymen]['categories_type']))
			{
				$subArray['categories_type'] = $menu_array[$keymen]['categories_type'];
				$subArray['storespecial'] = $menu_array[$keymen]['storespecial'];
				$subArray['preassembled'] = $menu_array[$keymen]['preassembled'];
				$subArray['sidedishes'] = $menu_array[$keymen]['sidedishes'];
				$subArray['is_bundle'] = $menu_array[$keymen]['is_bundle'];
				$subArray['is_chef_touched'] = $menu_array[$keymen]['is_chef_touched'];
			}

			$subArray['servings_sold'] = 0;
			$subArray['actual_dinners'] = 0;
			$subArray[CMenuItem::TWO] = 0;
			$subArray[CMenuItem::FOUR] = 0;
			$subArray[CMenuItem::FULL] = 0;
			$subArray[CMenuItem::HALF] = 0;
			$subArray[CMenuItem::INTRO] = 0;
			$subArray[CMenuItem::INTRO] = 0;
			$subArray[CMenuItem::LEGACY] = 0;

			$subArray['station_number'] = $fullMenu['station_number'];

			$subArray['entree_id'] = $fullMenu['entree_id'];
			$subArray['sub_menu_ids'] = $fullMenu['menu_array_ids'];
			$subArray['servings_sizes'] = $fullMenu['servings_sizes'];

			$subArray['PROMO_FULL'] = 0;
			$subArray['PROMO_HALF'] = 0;

			//if (isset($promo_items[$subArray['menu_name']]) && ($promo_items[$subArray['menu_name']]['type'] == CMenuItem::FULL  || $promo_items[$subArray['menu_name']]['type'] == CMenuItem::LEGACY) )
			//$subArray['PROMO_FULL'] = $promo_items[$subArray['menu_name']]['count'];

			//if (isset($promo_items[$subArray['menu_name']]) && $promo_items[$subArray['menu_name']]['type'] == CMenuItem::HALF ) {
			//$subArray['PROMO_HALF'] = $promo_items[$subArray['menu_name']]['count'];
			//}

			if (isset($promo_items[$subArray['menu_name']][CMenuItem::FULL]))
			{
				$subArray['PROMO_FULL'] = $promo_items[$subArray['menu_name']][CMenuItem::FULL]['count'];
			}
			if (isset($promo_items[$subArray['menu_name']][CMenuItem::LEGACY]))
			{
				$subArray['PROMO_FULL'] = $promo_items[$subArray['menu_name']][CMenuItem::LEGACY]['count'];
			}
			if (isset($promo_items[$subArray['menu_name']][CMenuItem::HALF]))
			{
				$subArray['PROMO_HALF'] = $promo_items[$subArray['menu_name']][CMenuItem::HALF]['count'];
			}

			$subArray['menu_desc'] = null;
			if ($addDescription == true)
			{
				$subArray['menu_desc'] = $fullMenu['menu_item_description'];
			}
			$entreeCount = count($sessionEntreeTotalArray);
			$sessionEntreeTotalArray[$entreeCount] = $subArray;
		}
		$counter = 0;
		foreach ($generalArray as $element)
		{
			$menuItemArray = $element['Menu_Items'];
			$itemPairs = explode(",", $menuItemArray);

			foreach ($itemPairs as $menuPair)
			{
				$servingSize = 0;
				$isIntro = false;
				$isLegacy = false;
				$vararr = explode(':', $menuPair);
				$menuitemid = $vararr[0];
				$menucount = $vararr[1];
				if ($containerArray != null && count($containerArray) > 0 && isset($containerArray[$menuitemid]))
				{
					$servingSize = $containerArray[$menuitemid]['size'];
					if ($containerArray[$menuitemid]['plan'] == CMenuItem::INTRO)
					{
						$isIntro = true;
					}
					else if ($containerArray[$menuitemid]['plan'] == CMenuItem::LEGACY)
					{
						$isLegacy = true;
					}
				}
				$this->updateMenuCount($sessionEntreeTotalArray, $menuitemid, $menucount, $servingSize, $isIntro, $isLegacy);
			}
			$counter++;
		}

		$varcounter = 0;
		foreach ($sessionEntreeTotalArray as $element)
		{
			$promo_large = $element['PROMO_FULL'];
			$promo_med = $element['PROMO_HALF'];
			if ($promo_large > 0)
			{
				$sessionEntreeTotalArray[$varcounter][CMenuItem::FULL] -= $promo_large;
			}
			if ($promo_med > 0)
			{
				$sessionEntreeTotalArray[$varcounter][CMenuItem::HALF] -= $promo_med;
			}
			$varcounter++;
		}

		return $sessionEntreeTotalArray;
	}

	function generateAndUpdateProductList(&$productArray, $addDescription = false)
	{
		$varcnt = 0;
		$count = count($productArray);
		if ($count == 0)
		{
			return;
		}
		$productsitems = DAO_CFactory::create("product");
		$varstr = "SELECT product.id,product.product_title,product.product_description FROM product WHERE ";
		foreach ($productArray as $prodVars)
		{
			$varstr = $varstr . "id  = '" . $prodVars['id'] . "'";
			if ($varcnt < $count - 1)
			{
				$varstr = $varstr . ' OR ';
			}
			$varcnt++;
		}
		$productsitems->query($varstr);
		$rows = array();
		while ($productsitems->fetch())
		{
			$rows = $productsitems->toArray();
			for ($i = 0; $i < $count; $i++)
			{
				if ($productArray[$i]['id'] == $rows['id'])
				{
					if ($addDescription == true)
					{
						$productArray[$i]['desc'] = $rows['product_description'];
					}
					$productArray[$i]['title'] = $rows['product_title'];
				}
			}
		}
	}

	function BuildProductArray($generalArray, &$productTotalArray)
	{
		$counter = 0;
		foreach ($generalArray as $element)
		{
			$productItemArray = $element['Product_Items'];
			if (strlen($productItemArray) > 0)
			{
				$itemPairs = explode(",", $productItemArray);
				foreach ($itemPairs as $prodPair)
				{
					$vararr = explode(':', $prodPair);
					$prodid = $vararr[0];
					$count = $vararr[1];
					$found = -1;
					for ($i = 0; $i < count($productTotalArray); $i++)
					{
						if ($productTotalArray[$i]['id'] == $prodid)
						{
							$found = $i;
							break;
						}
					}
					if ($found >= 0)
					{
						$varcount = $productTotalArray[$i]['count'];
						$productTotalArray[$i]['count'] = $count + $varcount;
					}
					else
					{
						$temp = array();
						$temp['id'] = $prodid;
						$temp['count'] = $count;
						$temp['title'] = null;
						$temp['desc'] = null;
						array_push($productTotalArray, $temp);
					}
				}
			}
		}
	}

	// *******************************************

	function isBalanceDueOrPaymentsFailed($payment_array)
	{
		$rows = array();
		if (!empty($payment_array))
		{
			foreach ($payment_array as $user_id => $order)
			{
				foreach ($order as $order_id => $sub_order)
				{

					$LTD_donation = 0;

					$grand_total = $sub_order['grand_total'];
					$totalPayment = 0;

					foreach ($sub_order as $payment_id => $payment)
					{

						if (is_array($payment))
						{

							if (!empty($sub_order[$payment_id]['ltd_round_up_value']) and is_numeric($sub_order[$payment_id]['ltd_round_up_value']))
							{
								$LTD_donation = $sub_order[$payment_id]['ltd_round_up_value'];
							}

							switch ($payment['payment_type'])
							{
								case CPayment::CHECK:
								case CPayment::CASH:
								case CPayment::GIFT_CERT:
								case CPayment::OTHER:
								case CPayment::CREDIT:
								case CPayment::GIFT_CARD:
								case CPayment::STORE_CREDIT;
									$totalPayment += $payment['total_amount'];
									break;

								case CPayment::CC:
									if ((!$payment['is_delayed_payment']) || ($payment['is_delayed_payment'] && (($payment['delayed_payment_status'] == CPayment::SUCCESS) || ($payment['delayed_payment_status'] == CPayment::PENDING))))
									{
										$totalPayment += $payment['total_amount'];
									}
									if ($payment['is_delayed_payment'] == 1) //and  $payment['delayed_payment_status'] == CPayment::FAIL)
									{
										$rows[$user_id][$order_id]['delayed_payment_failure'] = $payment['delayed_payment_status'];
									}
									break;

								case CPayment::REFUND:
								case CPayment::REFUND_CASH:
								case CPayment::REFUND_STORE_CREDIT:  // LMH... gift cards are always redeemed with STORE CREDIT REFUND
								case CPayment::REFUND_GIFT_CARD:
									$totalPayment -= $payment['total_amount'];
									break;
							}
						}
					}

					$balanceDue = COrders::std_round(($grand_total + $LTD_donation) - $totalPayment);
					if ($balanceDue != 0)
					{
						$rows[$user_id][$order_id]['balance_due'] = $balanceDue;
					}
				}
			}
		}

		return $rows;
	}

	function LocateCouponCodes($session_id)
	{
		$payment_array = null;

		$varStr = "";
		// TODO.. should we add payment.delayed_payment_status != 'FAILED'
		$booking = DAO_CFactory::create("booking");
		$varStr = "Select coupon_code_short_title, booking.user_id" . " From booking Inner Join orders on booking.order_id = orders.id " . " Inner Join session ON booking.session_id = session.id" . " Inner Join coupon_code ON orders.coupon_code_id = coupon_code.id" . " Where booking.`status` = 'ACTIVE' AND booking.is_deleted = 0 and session.is_deleted = 0 and session_publish_state !=  'SAVED'" . " and session.id = $session_id";
		$booking->query($varStr);
		$rows = array();
		$count = 0;
		$arr = array();
		while ($booking->fetch())
		{
			$arr[$booking->user_id] = $booking->coupon_code_short_title;
		}

		return $arr;
	}

	function getPaymentArrays($session_id)
	{
		$payment_array = null;

		$varStr = "";
		// TODO.. should we add payment.delayed_payment_status != 'FAILED'
		$booking = DAO_CFactory::create("booking");
		$varStr = "Select payment.payment_transaction_number,  payment.total_amount, orders.grand_total, orders.ltd_round_up_value, booking.order_id, payment.id as payment_id, booking.user_id,RIGHT(payment.payment_number,4) as payment_number," . "payment.payment_type,payment.credit_card_type, payment.is_deposit, delayed_payment_status, is_delayed_payment" . " From booking Inner Join orders on booking.order_id = orders.id " . " Inner Join session ON booking.session_id = session.id" . " Inner Join payment ON booking.order_id = payment.order_id and payment.is_deleted = 0 " . " Where booking.`status` = 'ACTIVE' AND booking.is_deleted = 0 and session.is_deleted = 0 and session_publish_state !=  'SAVED'" . " and session.id = $session_id";
		$booking->query($varStr);
		$rows = array();
		$count = 0;
		$arr = array();
		while ($booking->fetch())
		{
			$payment_array[$booking->user_id][$booking->order_id][$booking->payment_id] = array(
				'payment_transaction_number' => $booking->payment_transaction_number,
				'total_amount' => $booking->total_amount,
				'payment_number' => $booking->payment_number,
				'payment_type' => $booking->payment_type,
				'cc_type' => $booking->credit_card_type,
				'delayed_payment_status' => $booking->delayed_payment_status,
				'is_deposit' => $booking->is_deposit,
				'is_delayed_payment' => $booking->is_delayed_payment,
				'ltd_round_up_value' => $booking->ltd_round_up_value
			);

			if (!isset($payment_array[$booking->user_id][$booking->order_id]['grand_total']))
			{
				$payment_array[$booking->user_id][$booking->order_id]['grand_total'] = $booking->grand_total;
			}
		}

		return $payment_array;
	}

	function BuildCustomerArray($generalArray, $coupon_code_array)
	{

		$sub_customer_array = array();
		$counter = 0;
		foreach ($generalArray as $element)
		{
			$sub_customer_array[$counter]['firstname'] = $element['firstname'];
			$sub_customer_array[$counter]['lastname'] = $element['lastname'];
			$sub_customer_array[$counter]['is_partial_account'] = $element['is_partial_account'];
			$sub_customer_array[$counter]['menu_program_id'] = $element['menu_program_id'];
			$sub_customer_array[$counter]['is_bundle_offer'] = ((!empty($element['bundle_id']) && $element['bundle_id'] > 0) ? true : false);
			$sub_customer_array[$counter]['acount_deleted'] = $element['acount_deleted'];
			$sub_customer_array[$counter]['customer_id'] = $element['user_id'];
			$sub_customer_array[$counter]['dream_reward_status'] = $element['dream_reward_status'];
			$sub_customer_array[$counter]['order_id'] = $element['order_id'];
			$sub_customer_array[$counter]['referring_user_id'] = $element['referring_user_id'];
			$sub_customer_array[$counter]['referring_user_email'] = $element['meta'];
			$sub_customer_array[$counter]['referring_user_name'] = $element['referrers_name'];
			$sub_customer_array[$counter]['referral_type'] = $element['origination_type_code'];
			$sub_customer_array[$counter]['coupon_code'] = null;
			$sub_customer_array[$counter]['is_escrip_customer'] = 0;

			if (!empty($coupon_code_array[$element['user_id']]))
			{
				$sub_customer_array[$counter]['coupon_code'] = $coupon_code_array[$element['user_id']];
			}

			$sub_customer_array[$counter]['booking_type'] = $element['booking_type'];
			$sub_customer_array[$counter]['booking_id'] = $element['booking_id'];
			$sub_customer_array[$counter]['booking_no_show'] = $element['booking_no_show'];
			$counter++;
		}

		return $sub_customer_array;
	}

	function datediff($interval, $date1, $date2, &$rangeReversed)
	{

		$rangeReversed = false;
		//convert the dates into timestamps

		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		$seconds = $date2 - $date1;

		//if $date1 > $date2
		//change substraction order
		//convert the diff to +ve integer
		if ($seconds < 0)
		{
			$tmp = $date1;
			$date1 = $date2;
			$date2 = $tmp;
			$seconds = 0 - $seconds;
			$rangeReversed = true;
		}

		//reconvert the timestamps into dates
		if ($interval == 'y' || $interval == 'm')
		{
			$date1 = date("Y-m-d h:i:s", $date1);
			$date2 = date("Y-m-d h:i:s", $date2);
		}

		switch ($interval)
		{
			case "y":
				$newDate1 = substr($date1, 0, 10);
				$newDate2 = substr($date2, 0, 10);
				list($year1, $month1, $day1) = split('-', $newDate1);
				list($year2, $month2, $day2) = split('-', $newDate2);

				$time1 = (date('H', $date1) * 3600) + (date('i', $date1) * 60) + (date('s', $date1) * 1);
				$time2 = (date('H', $date2) * 3600) + (date('i', $date2) * 60) + (date('s', $date2) * 1);
				$diff = $year2 - $year1;

				if ($month1 > $month2)
				{
					$diff -= 1;
				}
				else if ($month1 == $month2)
				{
					if ($day1 > $day2)
					{
						$diff -= 1;
					}
					else if ($day1 == $day2)
					{
						if ($time1 > $time2)
						{
							$diff -= 1;
						}
					}
				}
				break;
			case "m":

				list($newDate1, $timeval1) = preg_split('/ /', $date1);
				list($newDate2, $timeval2) = preg_split('/ /', $date2);

				list($year1, $month1, $day1) = preg_split('/-/', $newDate1);
				list($year2, $month2, $day2) = preg_split('/-/', $newDate2);

				//   $time1 = (date('H',$date1)*3600) + (date('i',$date1)*60) + (date('s',$date1)*1 );
				//  $time2 = (date('H',$date2)*3600) + (date('i',$date2)*60) + (date('s',$date2)*1 );

				$diff = ($year2 * 12 + $month2) - ($year1 * 12 + $month1);
				if ($day1 > $day2)
				{
					$diff -= 1;
				}

				//if($day1 > $day2) //{
				//   $diff -= 1;
				//} elseif($day1 == $day2) {
				//if($time1 > $time2) {
				//   $diff -= 1;
				//}
				// }
				break;
			case "w":
				// Only simple seconds calculation needed from here on
				$diff = floor($seconds / 604800);
				break;
			case "d":
				//$diff = floor($seconds / 86400);  // this was causing the reports to lose a day when you spanned more than 2 months.
				$diff = round($seconds / 86400);
				break;
			case "h":
				$diff = floor($seconds / 3600);
				break;
			case "i":
				$diff = floor($seconds / 60);
				break;
			case "s":
				$diff = $seconds;
				break;
		}

		return $diff;
	}

	static function generateWhereClauseForSessionType($selectedSessionTypes, $tableAlias = 'session'){
		//session_type = enum('DREAM_EVENT','DREAM_TASTE','QUICKSIX','SPECIAL_EVENT','STANDARD','FUNDRAISER','TODD','DELIVERED')
		//session_class = ('QUICKSIX','SPECIAL_EVENT','STANDARD','FUNDRAISER','TODD')
		//session_type_subtype = enum('DELIVERY','DELIVERY_PRIVATE','REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE','WALK_IN')
		$resultSql = '';

		if(sizeof($selectedSessionTypes) == 0 || $selectedSessionTypes ==""){
			return $resultSql;
		}

		if(sizeof($selectedSessionTypes) == 3 ){
			//If all, don't filter
			return $resultSql;
		}

		foreach ($selectedSessionTypes as $sessionType){
			if( strlen($resultSql) > 0 ){
				$resultSql .= ' or ';
			}
			switch ($sessionType)
			{
				case CSession::DELIVERY:
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= " and $tableAlias.session_type_subtype IN ('". CSession::DELIVERY."', '". CSession::DELIVERY_PRIVATE."')) ";
					break;//DONE
				case CSession::STANDARD://ASSEMBLY
					$resultSql .= " ( ";
					$resultSql .= " ($tableAlias.session_type = '". CSession::STANDARD."' and dream_taste_event_theme.fadmin_acronym is null)";
					$resultSql .= " or ( $tableAlias.session_type = '". CSession::DREAM_TASTE."' and dream_taste_event_theme.fadmin_acronym  in ('DT','MPW','DDT','MPWD','OH','FNO')) ";
					$resultSql .= " or ( $tableAlias.session_type = '". CSession::FUNDRAISER."' and dream_taste_event_theme.fadmin_acronym  != 'FC') ";
					$resultSql .= " ) ";
					break;//DONE
				case CSession::SPECIAL_EVENT://PICKUP
					$resultSql .= " ( ";
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= " and ( $tableAlias.session_type_subtype is null or $tableAlias.session_type_subtype NOT IN ('". CSession::DELIVERY."', '". CSession::DELIVERY_PRIVATE."'))) ";
					$resultSql .= " or ( $tableAlias.session_type = '". CSession::DREAM_TASTE."' and dream_taste_event_theme.fadmin_acronym  in ('OHC','OHH','MPWC','FNOC')) ";
					$resultSql .= " or ( $tableAlias.session_type = '". CSession::FUNDRAISER."' and dream_taste_event_theme.fadmin_acronym  = 'FC') ";
					$resultSql .= " ) ";
					break;//DONE
				default:
					//$resultSql .= " $tableAlias.session_type = '". $sessionType ."' ";
					$resultSql .= "";
			}
		}

		if( strlen($resultSql) > 0 ){
			$resultSql = '('.$resultSql.')';
		}
		return $resultSql;
	}

	static function generateWhereClauseForSessionTypeSubType($selectedSessionTypes, $tableAlias = 'session'){
		//session_type = enum('DREAM_EVENT','DREAM_TASTE','QUICKSIX','SPECIAL_EVENT','STANDARD','FUNDRAISER','TODD','DELIVERED')
		//session_class = ('QUICKSIX','SPECIAL_EVENT','STANDARD','FUNDRAISER','TODD')
		//session_type_subtype = enum('DELIVERY','DELIVERY_PRIVATE','REMOTE_PICKUP','REMOTE_PICKUP_PRIVATE','WALK_IN')
		$resultSql = '';

		if(sizeof($selectedSessionTypes) == 0 || $selectedSessionTypes ==""){
			return $resultSql;
		}

		foreach ($selectedSessionTypes as $sessionType){
			if( strlen($resultSql) > 0 ){
				$resultSql .= ' or ';
			}
			switch ($sessionType)
			{
				case CSession::DELIVERED:
					$resultSql .= " $tableAlias.session_type = '". CSession::DELIVERED."' ";
					break;//DONE
				case CSession::DELIVERY:
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= " and $tableAlias.session_type_subtype = '". CSession::DELIVERY."') ";
					break;//DONE
				case CSession::DELIVERY_PRIVATE:
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= "and $tableAlias.session_type_subtype = '". CSession::DELIVERY."' ";
					$resultSql .= "and $tableAlias.session_password is not null ";
					$resultSql .= "and $tableAlias.session_password <> '') ";
					break;//DONE
				case CSession::REMOTE_PICKUP:
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= "and  $tableAlias.session_type_subtype = '". CSession::REMOTE_PICKUP."') ";
					break;//DONE
				case CSession::REMOTE_PICKUP_PRIVATE:
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= "and $tableAlias.session_type_subtype = '". CSession::REMOTE_PICKUP_PRIVATE."' ";
					$resultSql .= "and $tableAlias.session_password is not null ";
					$resultSql .= "and $tableAlias.session_password <> '') ";
					break;//DONE
				case CSession::PRIVATE_SESSION:
					$resultSql .= " ($tableAlias.session_type = '". CSession::STANDARD."' ";
					$resultSql .= "and $tableAlias.session_password is not null ";
					$resultSql .= "and $tableAlias.session_password <> '') ";
					break;//DONE
				case CSession::STANDARD:
					$resultSql .= " ($tableAlias.session_type = '". CSession::STANDARD."' ";
					$resultSql .= "and ($tableAlias.session_password is null ";
					$resultSql .= "or $tableAlias.session_password = '')) ";
					break;//DONE
				case CSession::SPECIAL_EVENT://PICKUP
				case CSession::MADE_FOR_YOU:
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= "and $tableAlias.session_type_subtype is null) ";
					break;//DONE
				case CSession::TODD:
					$resultSql .= " $tableAlias.session_type = '". CSession::TODD."' ";
					break;//DONE
				case CSession::DREAM_TASTE:
					$resultSql .= " $tableAlias.session_type = '". CSession::DREAM_TASTE."' ";
					break;//DONE
				case CSession::WALK_IN:
					$resultSql .= " ($tableAlias.session_type = '". CSession::SPECIAL_EVENT."' ";
					$resultSql .= "and  $tableAlias.session_type_subtype = '". CSession::WALK_IN."') ";
					break;//DONE
				case CSession::FUNDRAISER:
					$resultSql .= " $tableAlias.session_type = '". CSession::FUNDRAISER."' ";
					break;//DONE
				default:
					//$resultSql .= " $tableAlias.session_type = '". $sessionType ."' ";
					$resultSql .= "";
			}
		}

		if( strlen($resultSql) > 0 ){
			$resultSql = '('.$resultSql.')';
		}
		return $resultSql;
	}
}

?>