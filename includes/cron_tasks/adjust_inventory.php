<?php

require_once("../Config.inc");
require_once("CLog.inc");
require_once("DAO/BusinessObject/CDreamTasteEvent.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CTimezones.php");
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');


exit;
restore_error_handler();


	try {


		if (defined("DISABLE_CRON") && DISABLE_CRON)
		{
			CLog::Record("CRON: adjust_inventory called but cron is disabled");
			exit;
		}

		$sessionObj = DAO_CFactory::create('session');
		$sessionObj->query("select s.id, s.store_id, st.timezone_id, s.session_start, s.session_publish_state from session s
								join store st on st.id = s.store_id
								where s.session_start < DATE_ADD(now(), INTERVAL 6 HOUR) and
								s.session_start > DATE_SUB(now(), INTERVAL 1 DAY) and s.session_start > '2013-04-01 00:00:00' and
								s.is_deleted = 0 and s.session_publish_state <> 'SAVED' and s.inventory_was_processed = 0
								order by s.store_id");

		$totalSessionCount = 0;
		$totalUpdates = 0;
		while($sessionObj->fetch())
		{

			$store_id = $sessionObj->store_id;
			$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($sessionObj->timezone_id);
			if ($now >= strtotime($sessionObj->session_start))
			{



				$bookingsPerSession = DAO_CFactory::create('booking');
				$bookingsPerSession->query("select oi.id as order_item_id, o.store_id, o.id, oi.menu_item_id, mi.recipe_id, oi.item_count as item_qty, oi.inventory_was_processed, st.timezone_id from booking b
											join orders o on o.id = b.order_id and o.is_deleted = 0
											join order_item oi on oi.order_id = o.id and oi.is_deleted = 0
											join store st on st.id = o.store_id
											join menu_item mi on mi.id = oi.menu_item_id and mi.menu_item_category_id = 9
											where b.session_id = {$sessionObj->id} and b.status = 'ACTIVE'");

				while($bookingsPerSession->fetch())
				{

				    if (!$bookingsPerSession->inventory_was_processed)
				    {
    					$InvObj = DAO_CFactory::create('menu_item_inventory');
    					$InvObj->query("update menu_item_inventory set override_inventory = override_inventory - {$bookingsPerSession->item_qty} where store_id = $store_id and menu_id is null and recipe_id = {$bookingsPerSession->recipe_id}");

    					$bookingsPerSession->query("update order_item set inventory_was_processed = 1  where id = {$bookingsPerSession->order_item_id}");

    					CMenuItemInventoryHistory::RecordEvent($store_id, $bookingsPerSession->recipe_id, $bookingsPerSession->id, CMenuItemInventoryHistory::ATTEND, $bookingsPerSession->item_qty * -1 );

    					$totalUpdates++;
				    }
				}

				$sessionObj->query("update session set inventory_was_processed = 1 where id = {$sessionObj->id}");

				$totalSessionCount++;
			}


		}

		CLog::Record("CRON: $totalSessionCount sessions processed for $totalUpdates items sides and sweets inventory.");


	} catch (exception $e) {

		CLog::RecordException($e);
	}

?>