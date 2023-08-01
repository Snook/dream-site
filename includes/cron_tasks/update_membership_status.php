<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/BusinessObject/CMembershipHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");


define("TESTING", false);

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: update_memberhip_status called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::UPDATE_MEMBERSHIP_STATUS, "referral_rewards called but cron is disabled.");
		exit;
	}

	$HardSkipTotalCount = 0;
	$SoftSkipTotalCount = 0;

	// Look For Memberships requiring termination
	if (date("j") == 7)
	{
		$curMonth = date("n");
		$curYear = date("Y");
		$lastMonthTS = mktime(0,0,0,$curMonth-1,1,$curYear);
		$monthAnchorDate = date("Y-m-d", $lastMonthTS);

		$focusMenu = CMenu::getMenuByAnchorDate($monthAnchorDate);
		$memberships = new DAO();

		$memberships->query("SELECT 
				po.id as product_orders_id, 
				po.user_id, 
				po.store_id, 
       			po.timestamp_created,
				pm.term_months, 
       			pm.number_skips_allowed,
				pm.discount_type, 
				pm.discount_var, 
				poi.product_membership_initial_menu,
       			poi.product_membership_hard_skip_menu,
       			poi.id as membership_id,
       			st.timezone_id
				FROM product_orders po
				join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
				join product_membership pm on poi.product_id = pm.product_id
				join store st on st.id = po.store_id
				where po.is_deleted = 0");

		// Note: exception are handled, logged but not rethrown in the send_reminder_email function
		while ($memberships->fetch())
		{

			$lastPossibleMenu = $memberships->product_membership_initial_menu + $memberships->term_months - 1 + $memberships->number_skips_allowed ;

			if ($memberships->product_membership_initial_menu > $focusMenu['id'])
			{
				continue;
			}

			if ($focusMenu['id'] > $lastPossibleMenu)
			{
				// Shouldn't occur but catch a current enrollment that was not yet cancelled if the focus month is beyond the last possible month
				// Eject from program

				if (TESTING)
				{
					echo "Last check Would be terminating " . $memberships->user_id . " POID: " . $memberships->product_orders_id . "\r\n";
				}
				else
				{
					$updatePOI = new DAO();
					$updatePOI->query("update product_orders_items set ejection_menu_id = {$focusMenu['id']}, 
                                product_membership_status = 'MEMBERSHIP_STATUS_TERMINATED' 
                                where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

					$meta_arr = array('store_id' => $memberships->store_id);
					CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, CMembershipHistory::MEMBERSHIP_TERMINATED, $meta_arr);
				}
				continue;
			}

			$HardSkipTotalCount++;

			$ordersObj = new DAO();
			$ordersObj->query("select o.id, s.session_start, s.menu_id, o.type_of_order, s.session_type, s.session_type_subtype, o.subtotal_all_items, o.servings_total_count, o.membership_id, o.membership_discount from booking b 
									join session s on s.id = b.session_id and s.menu_id = {$focusMenu['id']} and s.session_type in ('STANDARD','SPECIAL_EVENT')
									join orders o on o.id = b.order_id and o.servings_total_count >= 36 
									where b.user_id = {$memberships->user_id} and b.status = 'ACTIVE' and b.is_deleted = 0");

			if ($ordersObj->N == 0)
			{

				$hardSkipArr = json_decode($memberships->product_membership_hard_skip_menu);
				$numHardSkips = count($hardSkipArr) + 1;
				if (is_null($hardSkipArr))
				{
					$hardSkipArr = array();
				}

				if (!empty($hardSkipArr))
				{
					if (in_array($focusMenu['id'], $hardSkipArr))
					{
						// last month menu was already hard skipped
						if ($numHardSkips -1 > $memberships->number_skips_allowed)
						{

							if (TESTING)
							{
								echo "Would be terminating (Last month skipped) normally" . $memberships->user_id . " POID: " . $memberships->product_orders_id . "\r\n";
							}
							else
							{
								// Eject from program
								$updatePOI = new DAO();
								$updatePOI->query("update product_orders_items set ejection_menu_id = {$focusMenu['id']}, product_membership_status = 'MEMBERSHIP_STATUS_TERMINATED', product_membership_hard_skip_menu = '" . json_encode($hardSkipArr) ."' where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

								$meta_arr = array('store_id' => $memberships->store_id);
								CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, CMembershipHistory::MEMBERSHIP_TERMINATED, $meta_arr);
							}

						}

					}	// some other menu was already hard skipped. This means a second hard skip has occurred.
					else if ($numHardSkips > $memberships->number_skips_allowed)
					{

						$hardSkipArr[] = $focusMenu['id'];

						// Eject from program

						if (TESTING)
						{
							echo "Would be terminating (2nd Hard skip) normally" . $memberships->user_id . " POID: " . $memberships->product_orders_id . "\r\n";
						}
						else
						{
							$updatePOI = new DAO();
							$updatePOI->query("update product_orders_items set ejection_menu_id = {$focusMenu['id']}, product_membership_status = 'MEMBERSHIP_STATUS_TERMINATED', product_membership_hard_skip_menu = '" . json_encode($hardSkipArr) ."' where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

							$meta_arr = array('store_id' => $memberships->store_id, 'menu_id' => $focusMenu['id']);
							CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, CMembershipHistory::HARD_SKIP, $meta_arr);
							$meta_arr = array('store_id' => $memberships->store_id);
							CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, CMembershipHistory::MEMBERSHIP_TERMINATED, $meta_arr);
						}

					}
					else
					{
						$hardSkipArr[] = $focusMenu['id'];

						if (TESTING)
						{
							echo "Recording hard skip for " . $memberships->user_id . " POID: " . $memberships->product_orders_id . "\r\n";
						}
						else
						{
							$updatePOI = new DAO();
							$updatePOI->query("update product_orders_item product_membership_hard_skip_menu = '" . json_encode($hardSkipArr) ."' where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

							$meta_arr = array('store_id' => $memberships->store_id, 'menu_id' => $focusMenu['id']);
							CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, CMembershipHistory::HARD_SKIP, $meta_arr);
						}
					}
				}
				else
				{
					if ($numHardSkips > $memberships->number_skips_allowed)
					{
						$hardSkipArr[] = $focusMenu['id'];

						// new rule is that passive hard skip means immediate ejection
						if (TESTING)
						{
							echo "Passive hard skip and termination for " . $memberships->user_id . " POID: " . $memberships->product_orders_id . "\r\n";
						}
						else
						{
							$updatePOI = new DAO();
							$updatePOI->query("update product_orders_items set  ejection_menu_id = {$focusMenu['id']}, product_membership_status = 'MEMBERSHIP_STATUS_TERMINATED', product_membership_hard_skip_menu = '" . json_encode($hardSkipArr) ."' where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

							$meta_arr = array('store_id' => $memberships->store_id, 'mid' => $memberships->membership_id,  'menu_id' => $focusMenu['id']);
							CMembershipHistory::recordEvent($memberships->user_id,  $memberships->membership_id, CMembershipHistory::HARD_SKIP, $meta_arr);
							$meta_arr = array('store_id' => $memberships->store_id, 'mid' => $memberships->membership_id);
							CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, CMembershipHistory::MEMBERSHIP_TERMINATED, $meta_arr);
						}

					}
					else
					{
						$hardSkipArr[] = $focusMenu['id'];
						// must be a multiple skip membership

						if (TESTING)
						{
							echo "Add Another Hard Skip skip and termination for " . $memberships->user_id . " POID: " . $memberships->product_orders_id . "\r\n";
						}
						else
						{
							$updatePOI = new DAO();
							$updatePOI->query("update product_orders_items set  product_membership_hard_skip_menu = '" . json_encode($hardSkipArr) ."' where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

							$meta_arr = array('store_id' => $memberships->store_id, 'mid' => $memberships->membership_id, 'menu_id' => $focusMenu['id']);
							CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, CMembershipHistory::HARD_SKIP, $meta_arr);
						}
					}
				}
			}
		}
	}

	// Mark completed Enrollments
	if (date("j") == 8) //8
	{
		$reportArray = array();

		$curMonth = date("n");
		$curYear = date("Y");
		$lastMonthTS = mktime(0, 0, 0, $curMonth - 1, 1, $curYear);
		$monthAnchorDate = date("Y-m-d", $lastMonthTS);

		$focusMenu = CMenu::getMenuByAnchorDate($monthAnchorDate);

		$firstMonthOfPotentialCompletions = $focusMenu['id'] - 5;

		$memberships = new DAO();

		$memberships->query("SELECT 
				po.id as product_orders_id, 
				po.user_id, 
				po.store_id, 
       			po.timestamp_created,
				pm.term_months, 
       			pm.number_skips_allowed,
				pm.discount_type, 
				pm.discount_var, 
				poi.product_membership_initial_menu,
              	poi.product_membership_hard_skip_menu,
       			poi.id as membership_id,
       			st.timezone_id
				FROM product_orders po
				join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT' 	
				    and poi.product_membership_initial_menu <= $firstMonthOfPotentialCompletions
				join product_membership pm on poi.product_id = pm.product_id
				join store st on st.id = po.store_id
				where po.is_deleted = 0");

		// Note: exception are handled, logged but not rethrown in the send_reminder_email function
		while ($memberships->fetch())
		{

			$orderCounter = new DAO();
			$orderCounter->query("select count(distinct o.id) as order_count, count(distinct s.menu_id) as menu_count, max(s.menu_id) as last_menu from orders o 
				join booking b on b.order_id = o.id and b.status = 'ACTIVE' and b.is_deleted = 0
				join session s on s.id = b.session_id
				where o.membership_id = {$memberships->membership_id} and o.servings_total_count > 35
				order by s.menu_id");

			if($orderCounter->fetch())
			{
				if ($orderCounter->menu_count >= ($memberships->term_months))
				{
					// completed
					if (TESTING)
					{
						echo "Completion for " . $memberships->user_id . " POID: " . $memberships->product_orders_id . "\r\n";
					}
					else
					{
						$updatePOI = new DAO();
						$updatePOI->query("update product_orders_items set product_membership_status = 'MEMBERSHIP_STATUS_COMPLETED' where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

						$meta_arr = array('store_id' => $memberships->store_id, 'mid' => $memberships->membership_id,  'menu_id' => $orderCounter->last_menu);
						CMembershipHistory::recordEvent($memberships->user_id,  $memberships->membership_id, CMembershipHistory::MEMBERSHIP_COMPLETED, $meta_arr);
					}
				}
				else
				{
					// need 1 more check for a more current membership and if found mark the this membership complete
					$OverridingMemberships = new DAO();
					$OverridingMemberships->query("SELECT 
							poi.id as membership_id
							FROM product_orders po
							join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT' 	
								and poi.product_membership_initial_menu >= {$memberships->product_membership_initial_menu} and poi.id <> {$memberships->membership_id} and poi.product_membership_initial_menu <= {$focusMenu['id']}
							join product_membership pm on poi.product_id = pm.product_id
							where po.is_deleted = 0");

					if ($OverridingMemberships->N > 0)
					{
						$OverridingMemberships->fetch();
						if (TESTING)
						{
							$reportArray[] = array('user' => $memberships->user_id, 'store' => $memberships->store_id, 'mid' => $memberships->membership_id, 'override_mid' => $OverridingMemberships->membership_id);
						}
						else
						{
							// A more recent membership has begun after the initial menu of the considered membership and it's initial menu
							$updatePOI = new DAO();
							$updatePOI->query("update product_orders_items set product_membership_status = 'MEMBERSHIP_STATUS_COMPLETED' where product_orders_id = {$memberships->product_orders_id} and is_deleted = 0");

							$meta_arr = array('store_id' => $memberships->store_id, 'mid' => $memberships->membership_id,  'menu_id' => $orderCounter->last_menu);
							CMembershipHistory::recordEvent($memberships->user_id,  $memberships->membership_id, CMembershipHistory::MEMBERSHIP_COMPLETED, $meta_arr);

						}
					}
				}
			}
		}

		if (TESTING)
		{
			echo "MEMBERSHIP COMPLETIONS: Would have completed:\r\n" . print_r($reportArray, true);
		}

	}

  // Find Hard Skips
	if (date("j") == 10 || date("j") == 17)
	{
		$focusMenu = CMenu::getMenuByDate(date("Y-m-d"));

		$skipType = 'SOFT_SKIP_17';
		if (date("j") == 10)
		{
			$skipType = 'SOFT_SKIP_10';
		}

		$memberships = new DAO();

		$memberships->query("SELECT 
			po.id as product_orders_id, 
			po.user_id, 
			po.store_id, 
			po.timestamp_created,
			pm.term_months, 
			pm.discount_type, 
			pm.discount_var, 
			poi.product_membership_initial_menu,
			poi.product_membership_hard_skip_menu,
            poi.id as membership_id,
			st.timezone_id
			FROM product_orders po
			join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
			join product_membership pm on poi.product_id = pm.product_id
			join store st on st.id = po.store_id
			where po.is_deleted = 0");

		// Note: exception are handled, logged but not rethrown in the send_reminder_email function
		while ($memberships->fetch())
		{
			$endMenu = $memberships->product_membership_initial_menu + $memberships->term_months - 1;

			if ($memberships->product_membership_initial_menu > $focusMenu['id'] || $endMenu < $focusMenu['id'])
			{
				continue;
			}

			// don't record soft skip if hard skip was enterred by store
			$jsonArr = json_decode($memberships->product_membership_hard_skip_menu);
			if (in_array($focusMenu['id'], $jsonArr))
			{
				continue;
			}


			$SoftSkipTotalCount++;

			$ordersObj = new DAO();
			$ordersObj->query("select o.id, s.session_start, s.menu_id, o.type_of_order, s.session_type, s.session_type_subtype, o.subtotal_all_items, o.servings_total_count, o.membership_id, o.membership_discount from booking b 
								join session s on s.id = b.session_id and s.menu_id = {$focusMenu['id']} and s.session_type in ('STANDARD','SPECIAL_EVENT')
								join orders o on o.id = b.order_id and o.servings_total_count >= 36 
								where b.user_id = {$memberships->user_id} and b.status = 'ACTIVE' and b.is_deleted = 0");

			if ($ordersObj->N == 0)
			{
				$meta_arr = array(
					'store_id' => $memberships->store_id,
					'menu_id' => $ordersObj->menu_id);
				CMembershipHistory::recordEvent($memberships->user_id, $memberships->membership_id, $skipType, $meta_arr);
			}
		}
	}

	CLog::RecordCronTask($SoftSkipTotalCount, CLog::SUCCESS, CLog::UPDATE_MEMBERSHIP_STATUS, " $SoftSkipTotalCount memberships processed for soft skips.");
	CLog::RecordCronTask($HardSkipTotalCount, CLog::SUCCESS, CLog::UPDATE_MEMBERSHIP_STATUS, " $HardSkipTotalCount memberships processed for hard skips.");

} catch (exception $e) {
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::UPDATE_MEMBERSHIP_STATUS, "update_memberhip_status: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>