<?php
/*
 * Created on June 11, 2012
 * project_name guestSearch
 *
 * Copyright 2012 DreamDinners
 * @author Carls
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/CDashboardReport.inc");
require_once("includes/CSessionReports.inc");

class processor_admin_food_sales extends CPageProcessor
{
	private $showStore = false;

	function runFranchiseManager()
	{
		$this->run();
	}

	function runOpsLead()
	{
		$this->run();
	}

	function runFranchiseOwner()
	{
		$this->run();
	}

	function runEventCoordinator()
	{
		$this->run();
	}

	function runFranchiseLead()
	{
		$this->run();
	}

	function runHomeOfficeManager()
	{
		$this->showStore = true;
		$this->run();
	}

	function runSiteAdmin()
	{
		$this->showStore = true;
		$this->run();
	}

	function setShowStore($shouldShow)
	{
		$this->showStore = $shouldShow;
	}

	function getPurchasersInRangeForItems($start, $duration, $store, $items, $omit_guests_ordered_since_menu_id = false)
	{

		$retVal = array();
		$DAO_Retriever = new DAO();

		$itemNames = array();

		if (is_array($store))
		{
			$storeClause = " and st.id in (" . implode(",", $store) . ") ";
		}
		else
		{
			$storeClause = " and st.id = $store ";
		}

		$omitUserList = "";
		if ($omit_guests_ordered_since_menu_id)
		{
			$menu_id = $omit_guests_ordered_since_menu_id;

			// Get the list of guests who have already ordered, they will be filtered out
			$curGuestsOfMenu = array();

			$booking2 = DAO_CFactory::create('booking');
			$booking2->query("SELECT 
				`booking`.`user_id`
				FROM `booking`
				Inner Join `session` ON `booking`.`session_id` = `session`.`id`
				Inner Join `user` ON `booking`.`user_id` = `user`.`id`
				where booking.is_deleted = 0 and `booking`.status = 'ACTIVE'
				and  session_publish_state != 'SAVED' $storeClause
				and menu_id >= $menu_id
				group by user_id");

			while ($booking2->fetch())
			{
				$curGuestsOfMenu[] = $booking2->user_id;
			}

			if (!empty($curGuestsOfMenu))
			{
				$omitUserList = "AND u.id NOT IN(" .  implode(',', $curGuestsOfMenu) . ")";
			}
		}

		$DAO_Retriever->query("select
			st.home_office_id, 
			st.store_name, 
			st.city as store_city, 
			st.state_id as store_state, 
			mi.recipe_id, 
			mi.menu_item_name, 
			GROUP_CONCAT(DISTINCT s.session_start order by s.session_start desc) as sessions, 
			GROUP_CONCAT(DISTINCT o.id order by s.session_start desc) as order_ids, 
			GROUP_CONCAT(DISTINCT s.id order by s.session_start desc) as session_ids, 
			MAX(s.session_start) as last_session,
			o.user_id, 
			#o.timestamp_created as date_ordered,
			CONCAT(u.firstname, ' ' , u.lastname) as name,
			u.firstname, 
			u.lastname, 
			u.primary_email, 
			u.telephone_1, 
			u.telephone_1_type, 
			u.telephone_1_call_time,  
			u.telephone_2, 
			u.telephone_2_type, 
			u.telephone_2_call_time,
			ad.address_line1, 
			ad.address_line2, 
			ad.city, 
			ad.state_id, 
			ad. postal_code,
			sum(if(mi.pricing_type = 'FULL',oi.item_count, null)) as full_num_ordered,
			sum(if(mi.pricing_type = 'HALF',oi.item_count, null)) as half_num_ordered,
		    sum(if(mi.pricing_type = 'FOUR',oi.item_count, null)) as four_num_ordered,
		    sum(if(mi.pricing_type = 'TWO',oi.item_count, null)) as two_num_ordered
			from booking b 
			join session s on b.session_id = s.id and s.session_start >= '$start' and s.session_start < DATE_ADD('$start',INTERVAL $duration)
			join orders o on o.id = b.order_id
			join user u on u.id = b.user_id " . $omitUserList . "
			left join address ad on ad.user_id = u.id and ad.location_type = 'BILLING' AND ad.is_deleted = 0
			join store st on st.id = s.store_id $storeClause
			join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
			join menu_item mi on mi.id = oi.menu_item_id and mi.recipe_id in ("  . implode(",", $items) .  " )
			where st.active = 1 and b.status  = 'ACTIVE' and b.is_deleted = 0 
			group by u.id, mi.recipe_id 
			order by  session_start, st.id");

		while ($DAO_Retriever->fetch())
		{

			if (!isset($itemNames[$DAO_Retriever->menu_item_name]))
			{
				$itemNames[$DAO_Retriever->menu_item_name] = $DAO_Retriever->menu_item_name . " (" . $DAO_Retriever->recipe_id . ")";
			}

			if ($this->showStore)
			{
				$retVal[$DAO_Retriever->user_id] = array(
					"home_office_id" => $DAO_Retriever->home_office_id,
					'store_name' => $DAO_Retriever->store_name,
					'store_city' => $DAO_Retriever->store_city,
					'store_state' => $DAO_Retriever->store_state,
					'recipe_id' => $DAO_Retriever->recipe_id,
					'menu_item_name' => $DAO_Retriever->menu_item_name,
					'sessions' => $DAO_Retriever->sessions,
					'last_session' => $DAO_Retriever->last_session,
					'user_id' => $DAO_Retriever->user_id,
					'firstname' => $DAO_Retriever->firstname,
					'lastname' => $DAO_Retriever->lastname,
					//'guest_name' => $DAO_Retriever->name,
					'email' => $DAO_Retriever->primary_email,
					'telephone_1' => $DAO_Retriever->telephone_1,
					'telephone_1_type' => $DAO_Retriever->telephone_1_type,
					'telephone_1_call_time' => $DAO_Retriever->telephone_1_call_time,
					'telephone_2' => $DAO_Retriever->telephone_2,
					'telephone_2_type' => $DAO_Retriever->telephone_2_type,
					'telephone_2_call_time' => $DAO_Retriever->telephone_2_call_time,
					'address_line1' => $DAO_Retriever->address_line1,
					'address_line2' => $DAO_Retriever->address_line2,
					'city' => $DAO_Retriever->city,
					'state_id' => $DAO_Retriever->state_id,
					'postal_code' => $DAO_Retriever->postal_code,
					'full_num_ordered' => $DAO_Retriever->full_num_ordered,
					'half_num_ordered' => $DAO_Retriever->half_num_ordered,
					'four_num_ordered' => $DAO_Retriever->four_num_ordered,
					'two_num_ordered' => $DAO_Retriever->two_num_ordered,
					'session_ids' => $DAO_Retriever->session_ids,
					'order_ids' => $DAO_Retriever->order_ids
				);
			}
			else
			{
				$retVal[$DAO_Retriever->user_id] = array(
					'recipe_id' => $DAO_Retriever->recipe_id,
					'menu_item_name' => $DAO_Retriever->menu_item_name,
					'sessions' => $DAO_Retriever->sessions,
					'last_session' => $DAO_Retriever->last_session,
					'user_id' => $DAO_Retriever->user_id,
					'firstname' => $DAO_Retriever->firstname,
					'lastname' => $DAO_Retriever->lastname,
					//'guest_name' => $DAO_Retriever->name,
					'email' => $DAO_Retriever->primary_email,
					'telephone_1' => $DAO_Retriever->telephone_1,
					'telephone_1_type' => $DAO_Retriever->telephone_1_type,
					'telephone_1_call_time' => $DAO_Retriever->telephone_1_call_time,
					'telephone_2' => $DAO_Retriever->telephone_2,
					'telephone_2_type' => $DAO_Retriever->telephone_2_type,
					'telephone_2_call_time' => $DAO_Retriever->telephone_2_call_time,
					'address_line1' => $DAO_Retriever->address_line1,
					'address_line2' => $DAO_Retriever->address_line2,
					'city' => $DAO_Retriever->city,
					'state_id' => $DAO_Retriever->state_id,
					'postal_code' => $DAO_Retriever->postal_code,
					'full_num_ordered' => $DAO_Retriever->full_num_ordered,
					'half_num_ordered' => $DAO_Retriever->half_num_ordered,
					'four_num_ordered' => $DAO_Retriever->four_num_ordered,
					'two_num_ordered' => $DAO_Retriever->two_num_ordered,
					'session_ids' => $DAO_Retriever->session_ids,
					'order_ids' => $DAO_Retriever->order_ids
				);
			}
		}

		return array(
			$retVal,
			$itemNames
		);
	}

	function getCategoryName($id, $isEFL)
	{
		switch ($id)
		{
			case 1:
				return 'CORE';
			case 4:
				if ($isEFL)
				{
					return "EFL";
				}
				else
				{
					return "FAST_LANE";
				}
			case 9:
				return "Sides";

			default:
				return "?";
		}
	}

	function getItemsSoldInRange($start, $duration, $store, $searchStr = false)
	{
		$retVal = array();
		$DAO_Retriever = new DAO();

		$storeClause = "";
		if (is_array($store))
		{
			$storeClause1 = " s.store_id in (" . implode(",", $store) . ") ";
		}
		else
		{
			$storeClause1 = " s.store_id = $store ";
		}

		$stringClause = "";
		if ($searchStr)
		{
			$stringClause = " and mi.menu_item_name like '%$searchStr%' ";
		}

		$DAO_Retriever->query("select mi.recipe_id, mi.menu_item_category_id, mi.is_store_special, mi.entree_id, mi.menu_item_name, sum(oi.item_count) as num_sold,
 									sum(if (mi.pricing_type = 'FULL', oi.item_count, null)) as full_count, sum(if (mi.pricing_type = 'HALF', oi.item_count, null)) as half_count, 
								    sum(if (mi.pricing_type = 'FOUR', oi.item_count, null)) as four_count, sum(if (mi.pricing_type = 'TWO', oi.item_count, null)) as two_count,
								    max(mmi.menu_id) as last_menu  from session s 
									join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
									join order_item oi on oi.order_id = b.order_id and oi.is_deleted = 0
									join menu_item mi on mi.id = oi.menu_item_id
									join menu_to_menu_item mmi on mmi.menu_item_id = mi.id and mmi.store_id = s.store_id and mmi.is_deleted = 0
									where $storeClause1 and s.session_start >= '$start' and s.session_start < DATE_ADD('$start',INTERVAL $duration) $stringClause
									group by mi.recipe_id");

		while ($DAO_Retriever->fetch())
		{
			$retVal[$DAO_Retriever->recipe_id] = array(
				'recipe_id' => $DAO_Retriever->recipe_id,
				'name' => $DAO_Retriever->menu_item_name,
				'num_sold' => $DAO_Retriever->num_sold . " (" . $this->convertIfNotSet($DAO_Retriever->full_count) . "/" . $this->convertIfNotSet($DAO_Retriever->half_count) . "/" . $this->convertIfNotSet($DAO_Retriever->four_count). "/" . $this->convertIfNotSet($DAO_Retriever->two_count). ")",
				'category' => $this->getCategoryName($DAO_Retriever->menu_item_category_id, $DAO_Retriever->is_store_special),
				'entree_id' => $DAO_Retriever->entree_id,
				'menu_id' => $DAO_Retriever->last_menu
			);
		}

		return $retVal;
	}

	function getAllItemsForMenu($menu_id, $store, $searchStr = false)
	{
		$retVal = array();
		$DAO_Retriever = new DAO();

		if (is_array($store))
		{
			$storeClause1 = " and o.store_id in (" . implode(",", $store) . ") ";
			$storeClause2 = " and mmi.store_id in (" . implode(",", $store) . ") ";
		}
		else
		{
			$storeClause1 = " and o.store_id = $store ";
			$storeClause2 = " and mmi.store_id = $store ";
		}

		$stringClause = "";
		if ($searchStr)
		{
			$stringClause = " and mi.menu_item_name like '%$searchStr%' ";
		}

		// First get all items
		$DAO_Retriever->query("select mi.recipe_id, mmi.menu_item_id, mi.menu_item_name, mi.menu_item_category_id, mi.pricing_type, mi.is_store_special, mi.entree_id from menu_to_menu_item mmi 
								join menu_item mi on mi.id = mmi.menu_item_id $stringClause
								where mmi.menu_id = $menu_id $storeClause2 and mmi.is_deleted = 0 
								group by mmi.menu_item_id");
		while ($DAO_Retriever->fetch())
		{
			$retVal[$DAO_Retriever->recipe_id] = array(
				'recipe_id' => $DAO_Retriever->recipe_id,
				'name' => $DAO_Retriever->menu_item_name,
				'category' => $this->getCategoryName($DAO_Retriever->menu_item_category_id, $DAO_Retriever->is_store_special),
				'entree_id' => $DAO_Retriever->entree_id,
				'menu_id' => $menu_id
			);
		}

		$DAO_Retriever = new DAO();

		// now get sales data
		$DAO_Retriever->query("select mi.recipe_id, GROUP_CONCAT(distinct mmi.menu_item_id), sum(oi.item_count) as num_sold, sum(if (mi.pricing_type = 'FULL', oi.item_count, null)) as full_count, sum(if (mi.pricing_type = 'HALF', oi.item_count, null)) as half_count
							from menu_to_menu_item mmi
							join menu_item mi on mi.id = mmi.menu_item_id $stringClause
							join order_item oi on oi.menu_item_id = mmi.menu_item_id and oi.is_deleted = 0
							join booking b on b.order_id = oi.order_id and b.status = 'ACTIVE'
							join orders o on o.id = b.order_id and o.store_id = mmi.store_id
							where mmi.menu_id = $menu_id $storeClause2 and mmi.is_deleted = 0
							group by mi.recipe_id");
		while ($DAO_Retriever->fetch())
		{
			$retVal[$DAO_Retriever->recipe_id]['num_sold'] = $DAO_Retriever->num_sold . " (" . $this->convertIfNotSet($DAO_Retriever->full_count) . "/" . $this->convertIfNotSet($DAO_Retriever->half_count ). "/" . $this->convertIfNotSet($DAO_Retriever->four_count) . "/" . $this->convertIfNotSet($DAO_Retriever->two_count) . ")";
		}

		return $retVal;
	}

	function convertIfNotSet($val, $char = '-'){
		if(empty($val)){
			return $char;
		}

		return $val;

	}

	function run()
	{
		if (!empty($_POST['op']))
		{
			if ($_POST['op'] == 'set_date_range')
			{
				$title = "Items";
				$item_op = false;

				$SessionReport = new CSessionReports();
				$store_id = $_POST['store_id'];

				if (is_array($store_id))
				{
					foreach ($store_id as $k => $v)
					{
						if (empty($v))
						{
							unset($store_id[$k]);
						}
					}
				}

				$searchStr = false;
				if (!empty($_POST['search_string']))
				{
					$searchStr = addslashes($_POST['search_string']);
				}

				// return a mySQL formatted datetime and interval
				// also return information for the item selection pane

				$rangeType = $_POST['rangeType'];

				if ($rangeType == 1)
				{
					$mySQLDate = date("Y-m-d H:i:s", strtotime($_POST['dateStart']));
					$duration = '1 DAY';
					$item_op = "list_for_day";
					$item_data = $this->getItemsSoldInRange($mySQLDate, $duration, $store_id, $searchStr);
					$title = "Items Sold on " . CTemplate::dateTimeFormat($mySQLDate, VERBOSE_DATE);
				}
				else if ($rangeType == 2)
				{
					$rangeReversed = false;
					$diff = $SessionReport->datediff("d", $_POST['dateStart'], $_POST['dateEnd'], $rangeReversed);
					$diff++;  // always add one for SQL to work correctly
					if ($rangeReversed == true)
					{
						$mySQLDate = date("Y-m-d H:i:s", strtotime($_POST['dateEnd']));
						$_POST['dateEnd'] = $_POST['dateStart'];
					}
					else
					{
						$mySQLDate = date("Y-m-d H:i:s", strtotime($_POST['dateStart']));
					}

					$duration = $diff . ' DAY';

					$item_op = "list_for_span";
					$item_data = $this->getItemsSoldInRange($mySQLDate, $duration, $store_id, $searchStr);
					$title = "Items Sold on " . CTemplate::dateTimeFormat($mySQLDate, VERBOSE_DATE) . " thru " . CTemplate::dateTimeFormat($_POST['dateEnd'], VERBOSE_DATE);
				}
				else if ($rangeType == 3)
				{
					// process for a given month

					if ($_POST['useMenuMonth'] !== 'false')
					{

						$month = $_POST["month"];
						$month++;
						$year = $_POST["year"];

						$dateStr = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));

						$theMenu = DAO_CFactory::create('menu');
						$theMenu->query("select id, menu_name, global_menu_start_date, DATEDIFF(global_menu_end_date, global_menu_start_date) + 1 as day_interval from menu where menu_start = '$dateStr'");
						$theMenu->fetch();

						$mySQLDate = date("Y-m-d H:i:s", strtotime($theMenu->global_menu_start_date));
						$duration = $theMenu->day_interval . " DAY";

						$item_op = "list_for_menu_month";
						$title = "All Items for " . $theMenu->menu_name;
						$item_data = $this->getAllItemsForMenu($theMenu->id, $store_id, $searchStr);
					}
					else
					{
						$day = "01";
						$month = $_POST["month"];
						$month++;
						$duration = '1 MONTH';
						$year = $_POST["year"];

						$mySQLDate = date("Y-m-d H:i:s", mktime(0, 0, 0, $month, 1, $year));

						$item_op = "list_for_cal_month";

						$title = "Items Sold for Calendar Month " . CTemplate::dateTimeFormat($mySQLDate, VERBOSE_MONTH_YEAR);
						$item_data = $this->getItemsSoldInRange($mySQLDate, $duration, $store_id, $searchStr);
					}
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Invalid range type submitted.'
					));
					exit;
				}

				$tpl = new CTemplate();

				$tpl->assign('itemData', $item_data);
				$renderedItemData = $tpl->fetch('admin/subtemplate/reports_food_sales_items.tpl.php');

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Date range set is successful.',
					'range_start_date' => $mySQLDate,
					'range_duration' => $duration,
					'item_op' => $item_op,
					'title' => $title,
					'item_data' => $renderedItemData
				));
				exit;
			}
			else if ($_POST['op'] == 'show_purchasers')
			{

				$mySQLDate = $_POST['range_start'];
				$duration = $_POST['duration'];
				$items = $_POST['items'];
				$store_id = $_POST['store_id'];
				$omit_menu_id = false;

				if (!empty($_POST['omit_menu_id']))
				{
					$omit_menu_id = $_POST['omit_menu_id'];
				}

				if (is_array($store_id))
				{
					foreach ($store_id as $k => $v)
					{
						if (empty($v))
						{
							unset($store_id[$k]);
						}
					}
				}

				if ($omit_menu_id)
				{
					list ($guest_data, $itemNames) = $this->getPurchasersInRangeForItems($mySQLDate, $duration, $store_id, $items, $omit_menu_id);
				}
				else
				{
					list ($guest_data, $itemNames) = $this->getPurchasersInRangeForItems($mySQLDate, $duration, $store_id, $items);
				}

				$tpl = new CTemplate();
				$tpl->assign('guestData', $guest_data);
				$tpl->assign('showStore', $this->showStore);
				$tpl->assign('title', "Guests who Purchased " . implode(",", $itemNames));
				$renderedGuestData = $tpl->fetch('admin/subtemplate/reports_food_sales_guests.tpl.php');

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Show_purchasers is successful.',
					'guest_data' => $renderedGuestData
				));
				exit;
			}
			else if ($_POST['op'] == 'menu_item_info')
			{
				$req_entree_id = CGPC::do_clean((!empty($_POST['entree_id']) ? $_POST['entree_id'] : false), TYPE_INT);
				$req_menu_id = CGPC::do_clean((!empty($_POST['menu_id']) ? $_POST['menu_id'] : false), TYPE_INT);
				$req_recipe_id = CGPC::do_clean((!empty($_POST['recipe_id']) ? $_POST['recipe_id'] : false), TYPE_INT);

				if (is_array($_POST['store_id']))
				{
					foreach ($_POST['store_id'] as $k => $v)
					{
						if (empty($v))
						{
							unset($_POST['store_id'][$k]);
						}
					}

					$req_store_id = $_POST['store_id'];
				}
				else
				{
					$req_store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : false), TYPE_INT);
				}

				if ($req_entree_id)
				{
					$result = CMenuItem::getInfoForEFLCandidate($req_recipe_id, $req_menu_id, $req_store_id);

					$tpl = new CTemplate();

					$result['item_detail']['cooking_inst']['half'] = mb_convert_encoding($result['item_detail']['cooking_inst']['half'], 'HTML-ENTITIES', 'UTF-8');
					$result['item_detail']['cooking_inst']['full'] = mb_convert_encoding($result['item_detail']['cooking_inst']['full'], 'HTML-ENTITIES', 'UTF-8');
					$result['item_detail']['cooking_inst']['two'] = mb_convert_encoding($result['item_detail']['cooking_inst']['two'], 'HTML-ENTITIES', 'UTF-8');
					$result['item_detail']['cooking_inst']['four'] = mb_convert_encoding($result['item_detail']['cooking_inst']['four'], 'HTML-ENTITIES', 'UTF-8');

					$tpl->assign('curItem', $result['item_detail']);
					$tpl->assign('salesData', $result['sales_data']);
					$tpl->assign('cardData', $result['card_data']);

					$data = $tpl->fetch('admin/menu_editor_item_info.tpl.php');

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Menu item info retrieved.',
						'data' => $data
					));
				}
			}
		}
	}

}

?>