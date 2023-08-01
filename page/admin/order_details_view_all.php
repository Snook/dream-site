<?php
/*
 * Created on Nov 10, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * Could not run merge on this page
 */

require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/Orders.php');
require_once("includes/CSessionReports.inc");

function suggestionPrioritizor($a, $b)
{
	if ($a['has_match'] && !$b['has_match'])
	{
		return -1;
	}

	if (!$a['has_match'] && $b['has_match'])
	{
		return 1;
	}

	if (in_array($a['sideRecipeID'], page_admin_order_details_view_all::$favoredRecommendedSides) && !in_array($b['sideRecipeID'], page_admin_order_details_view_all::$favoredRecommendedSides))
	{
		return -1;
	}

	if (!in_array($a['sideRecipeID'], page_admin_order_details_view_all::$favoredRecommendedSides) && in_array($b['sideRecipeID'], page_admin_order_details_view_all::$favoredRecommendedSides))
	{
		return 1;
	}

	if (in_array($a['sideRecipeID'], page_admin_order_details_view_all::$notRecommendedSides) && !in_array($b['sideRecipeID'], page_admin_order_details_view_all::$notRecommendedSides))
	{
		return 1;
	}

	if (!in_array($a['sideRecipeID'], page_admin_order_details_view_all::$notRecommendedSides) && in_array($b['sideRecipeID'], page_admin_order_details_view_all::$notRecommendedSides))
	{
		return -1;
	}

	if ($a['id'] < $b['id']) // smaller id means greater priority but should move to a dedicated column
	{
		return -1;
	}
	else if ($a['id'] > $b['id'])
	{
		return 1;
	}

	return 0;
}

class page_admin_order_details_view_all extends CPageAdminOnly
{

	public static $favoredRecommendedSides = array(
		716,
		815,
		278,
		306,
		402,
		717,
		719
	);

	public static $notRecommendedSides = array(
		422,
		178,
		179,
		806,
		807,
		805,
		907,
		908
	);

	static function buildFinishingTouchSuggestions($menu_id, $store_id, $order_items, $bundleItems = false)
	{

		// get list of candidates, need ordering, inventory and mapping to entrees

		// sort list in this order
		/*
		1) has inventory, has mapping, has priority
		2) has inventory has priority
		3) has inventory
		 */

		$orderedEntrees = array();

		// build a flat list of menu_item ids
		foreach ($order_items as $k => $v)
		{
			if (is_array($v) && $k != "Chef Touched Selections" && $k != "Meal Prep Workshop")
			{
				foreach ($v as $id => $itemData)
				{
					$orderedEntrees[] = $id;
				}
			}
		}

		if ($bundleItems)
		{
			foreach ($bundleItems as $id => $v)
			{
				$orderedEntrees[] = $id;
			}
		}

		// INVENTORY TOUCH POINT 16

		$FT_Items = DAO_CFactory::create('menu_item');
		$FT_Items->query("select mi.id, mi.menu_item_name, mi.recipe_id,
				GROUP_CONCAT(ets.entree_recipe_id order by ets.entree_menu_item_id) as mappedEntreeRecipeIDs,
				GROUP_CONCAT(ets.entree_menu_item_id  order by ets.entree_menu_item_id)  as mappedEntreeMIIDs,
				GROUP_CONCAT(mi2.menu_item_name  order by ets.entree_menu_item_id)  as mappedEntreeNames,
				 minv.override_inventory, minv.number_sold from menu_item mi
				left join entree_to_side ets on ets.side_menu_item_id = mi.id
				left join menu_item mi2 on mi2.id = ets.entree_menu_item_id
				join menu_to_menu_item mmi on mmi.menu_id = $menu_id and menu_item_id = mi.id and isnull(mmi.store_id)
				left join menu_item_inventory minv on minv.recipe_id = mi.recipe_id and minv.menu_id = $menu_id and minv.store_id = $store_id
				where mi.menu_item_category_id = 9
				group by mi.id");

		$results = array();

		while ($FT_Items->fetch())
		{

			$remainingServings = $FT_Items->override_inventory;

			if ($remainingServings <= 0)
			{
				continue;
			}

			$results[$FT_Items->id] = array(
				'id' => $FT_Items->id,
				'sideRecipeID' => $FT_Items->recipe_id,
				'sideName' => $FT_Items->menu_item_name,
				'remaining_inventory' => $remainingServings
			);

			$potentialEntreeHitsMenuItemIDs = explode(",", $FT_Items->mappedEntreeMIIDs);
			$potentialEntreeHitsMenuItemNames = explode(",", $FT_Items->mappedEntreeNames);

			$results[$FT_Items->id]['has_match'] = false;
			$results[$FT_Items->id]['matches'] = array();

			$counter = 0;
			foreach ($potentialEntreeHitsMenuItemIDs as $miid)
			{
				if (in_array($miid, $orderedEntrees))
				{
					$results[$FT_Items->id]['matches'][] = array(
						'miid' => $miid,
						'name' => $potentialEntreeHitsMenuItemNames[$counter]
					);
					$results[$FT_Items->id]['has_match'] = true;
				}
				$counter++;
			}
		}

		uasort($results, 'suggestionPrioritizor');

		if (false) // for testing
		{
			$resultTest = array();
			foreach ($results as $id => $data)
			{
				$resultTest[$id] = $data['sideRecipeID'] . " | " . $data['sideName'] . " | " . ($data['has_match'] ? "TRUE" : "FALSE");
			}

			CLog::Record(print_r($resultTest, true));
		}

		return $results;
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseLead()
	{
		$this->runSiteAdmin();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$customer_print_view = 0;
		$output_array = null;
		$session_id = 0;
		$tpl = CApp::instance()->template();
		//$Form = new CForm();
		//$Form->Repost = FALSE;

		if (isset ($_REQUEST["customer_print_view"]))
		{
			$customer_print_view = CGPC::do_clean($_REQUEST["customer_print_view"], TYPE_STR);
		}

		if (isset ($_REQUEST["session_id"]))
		{  // pick the type of search query
			$session_id = CGPC::do_clean($_REQUEST["session_id"], TYPE_INT);

			$singleBooking = false;
			if (!empty($_REQUEST['booking_id']) and is_numeric($_REQUEST['booking_id']))
			{
				$singleBooking = CGPC::do_clean($_REQUEST['booking_id'], TYPE_INT);
			}

			$sessionDAO = DAO_CFactory::create('session');
			$sessionDAO->query("select s.store_id, st.supports_dream_rewards, st.allow_dfl_tool_access from session s join store st on st.id = s.store_id where s.id = $session_id");
			$sessionDAO->fetch();
			$showDreamRewardsStatus = true;
			$supportsDinnersForLife = false;
			if (!$sessionDAO->supports_dream_rewards)
			{
				$showDreamRewardsStatus = false;
			}
			if ($sessionDAO->allow_dfl_tool_access)
			{
				$supportsDinnersForLife = true;
			}

			$other_details = array();
			$output_array = self::create_view_all_orders($session_id, $other_details, $customer_print_view, $showDreamRewardsStatus, $supportsDinnersForLife, $singleBooking);

			$report_desc = "Session/";
			if (isset($_REQUEST['issidedish']) && $_REQUEST['issidedish'] == '1')
			{
				$report_desc .= "SideDish Report";
			}
			else if (isset($_REQUEST['ispreassembled']) && $_REQUEST['ispreassembled'] == '1')
			{
				$report_desc .= "Fast Lane Report";
			}
			else
			{
				if ($customer_print_view)
				{
					$report_desc .= "Customer Receipt";
				}
				else
				{
					$report_desc .= "Store Receipt";
				}
			}

			CLog::RecordReport($report_desc, "Session: $session_id");

			if (empty($output_array))
			{
				$tpl->assign('null_array', true);
			}
			$tpl->assign('view_all_list', $output_array);
			$tpl->assign('other_details_list', $other_details);
		}
		else
		{
			throw new Exception('Could not locate the session id.');
		}
		$tpl->assign('customer_view', $customer_print_view);
	}

	static function create_view_all_orders($session_id, &$other_details, $customer_print_view, $showDreamRewardsStatus = true, $supportsDinnersForLife = false, $single_booking_id = false, $supportsPlatePoints = false)
	{
		$html_array = array();
		$filename = null;
		$counter = 0;
		$templateEngine = new CTemplate();

		$sideDishReport = CGPC::do_clean(isset($_REQUEST["issidedish"]) ? $_REQUEST["issidedish"] : null, TYPE_BOOL);
		$preassembledReport = CGPC::do_clean(isset($_REQUEST["ispreassembled"]) ? $_REQUEST["ispreassembled"] : null, TYPE_BOOL);

		$isDeliveredOrder = false;

		if (!empty($sideDishReport) || !empty($preassembledReport))
		{
			$filename = "order_customer_view_table_additional.tpl.php";
		}
		else
		{

			if (CBrowserSession::getCurrentFadminStoreType() === CStore::DISTRIBUTION_CENTER)
			{
				$filename = 'order_details_table_delivered.tpl.php';  // okay, the customer and store receipts have been merged into this file... please edit for both
				$isDeliveredOrder = true;
			}
			else
			{
				$filename = 'order_details_table.tpl.php';  // okay, the customer and store receipts have been merged into this file... please edit for both
			}
		}

		$templateEngine->assign('issidedish', $sideDishReport);
		$templateEngine->assign('ispreassembled', $preassembledReport);
		$templateEngine->assign('customer_print_view', $customer_print_view);
		$templateEngine->assign('is_customer_franchise_report', true);

		$order_info = array();
		$booking = DAO_CFactory::create('booking');
		$user = DAO_CFactory::create('user');
		$booking->session_id = $session_id;
		$booking->joinAdd($user);

		if (!$single_booking_id)
		{
			$whereClause = " status = 'ACTIVE'";
		}
		else
		{
			$whereClause = " booking.id = $single_booking_id";
		}

		$booking->whereAdd($whereClause);
		$booking->orderBy("user.lastname ASC");
		$booking->find();

		$foundSideDishes = false;
		$foundPreassembled = false;
		while ($booking->fetch())
		{
			$User = DAO_CFactory::create('user');

			$User->query("SELECT
				u.*,
				a.address_line1,
				a.address_line2,
				a.city,
				a.state_id,
				a.postal_code,
				count(*) AS num_orders
				FROM
				`user` AS u
				LEFT JOIN address AS a ON a.user_id = u.id AND a.location_type = 'BILLING' AND a.is_deleted = '0'
				INNER JOIN booking AS b ON b.user_id = u.id AND b.`status` = 'ACTIVE' AND b.is_deleted = '0'
				WHERE u.id = '" . $booking->user_id . "'
				GROUP BY u.id ");

			if ($User->fetch())
			{
				$order_info = null;
				if ($isDeliveredOrder)
				{
					$order = new COrdersDelivered();
				}
				else
				{
					$order = DAO_CFactory::create('orders');
				}

				$order->id = $booking->order_id;
				$order->find(true);

				$orderCustomization = OrdersCustomization::getInstance($order);
				//these are the settings the store had at the time the order was placed
				$storeCustomizationSettings =  $orderCustomization->storeCustomizationSettingsToObj();
				$storeCustomizationSettings->initFromCurrentStoreCustomizationSettingsIfNull($order->getStore());
				$templateEngine->assign('store_allows_meal_customization',$storeCustomizationSettings->allowsMealCustomization());
				$templateEngine->assign('store_allows_preassembled_customization',$storeCustomizationSettings->allowsPreAssembledCustomization());
				$templateEngine->assign('order_has_meal_customization',$orderCustomization->hasMealCustomizationPreferencesSet() && $order->opted_to_customize_recipes);

				$pending = $order->getPaymentsPending();

				$LTD_donation = 0;
				if (!empty($order->ltd_round_up_value) and is_numeric($order->ltd_round_up_value))
				{
					$LTD_donation = $order->ltd_round_up_value;
				}

				$balanceDue = COrders::std_round(($order->grand_total + $LTD_donation) - $pending);

				$order_info = COrders::buildOrderDetailArrays($User, $order, null, false, true, false, $isDeliveredOrder,'FeaturedFirst');

				$orderCustomization = OrdersCustomization::getInstance($order);
				$str = $orderCustomization->mealCustomizationToStringSelectedOnly(',');
				$templateEngine->assign('meal_customization_string', $str);

				if ($order_info['menuInfo']['menu_id'] > 137)
				{
					$relocatedItems = false;
					if (!empty($order_info['RelocatedItems']))
					{
						$relocatedItems = $order_info['RelocatedItems'];
					}

					$FinishingTouchSuggestions = self::buildFinishingTouchSuggestions($order_info['menuInfo']['menu_id'], $order_info['orderInfo']['store_id'], $order_info['menuInfo'], $relocatedItems);
					$templateEngine->assign('finishingTouchSuggestions', $FinishingTouchSuggestions);
				}

				if (isset($order_info['orderInfo']['coupon_title']))
				{
					$templateEngine->assign('coupon_title', $order_info['orderInfo']['coupon_title']);
				}
				else
				{
					$templateEngine->assign('coupon_title', "");
				}

				$User->getMembershipStatus($order_info['orderInfo']['id']);

				if ($showDreamRewardsStatus)
				{
					$drData = CDreamRewardsHistory::getCurrentStateForUserShortForm($User);

					if ($drData)
					{
						$downgrade = false;

						if (isset($User->dr_downgraded_order_count) && $User->dr_downgraded_order_count > 0)
						{
							$downgrade = $User->dr_downgraded_order_count;
						}

						$drData['order_level'] = CDreamRewardsHistory::shortLevelDesc($order->dream_rewards_level);

						if (isset($order->is_dr_downgraded_order) && $order->is_dr_downgraded_order)
						{
							if ($downgrade && $downgrade > 1)
							{
								$drData['order_level'] .= " (%5 off this and the next $downgrade orders)";
							}
							else if ($downgrade && $downgrade > 0)
							{
								$drData['order_level'] .= " (%5 off this and the next order)";
							}
							else
							{
								$drData['order_level'] .= " (%5 off this order)";
							}
						}

						$drData['next_reward'] = CDreamRewardsHistory::nextRewardDataByLevel($drData['program_version'], $order->dream_rewards_level, $downgrade);
						$drData['next_reward'] = $drData['next_reward']['display_text'];
					}
					else
					{
						$drData['status'] = "N/A";
						$drData['level'] = "N/A";
						$drData['order_level'] = "N/A";
						$drData['next_reward'] = "N/A";
					}
				}
				else
				{
					$drData = null;
				}

				/*

								$platePointsStatusMessage = null;
								if ($supportsPlatePoints)
								{
									// quick check for member status
									$levelCheck  = new DAO();
									$levelCheck->query("select total_point from points_user_history where user_id = {$User->id} and is_deleted = 0 order by id desc limit 1");
									$levelCheck->fetch();

									if (!empty($levelCheck->total_points) && $levelCheck->total_points < 1500)
									{
										$platePointsStatusMessage = "Order is not in streak";

										$streak = CPointsUserHistory::getOrdersSequenceStatus($User->id, $order->id);
										if ($streak['focusOrderInOriginalStreak'])
										{
											$platePointsStatusMessage = "Consecutive Orders: " . $streak['focusOrderStreakOrderNumber'];
										}


									}
								}
				*/

				$guestnote = DAO_CFactory::create('user_data');
				$guestnote->user_data_field_id = GUEST_CARRY_OVER_NOTE;
				$guestnote->user_id = $User->id;
				$guestnote->store_id = CBrowserSession::getCurrentFadminStore();
				$guestnote->find(true);
				$order_info['orderInfo']['guest_carryover_notes'] = $guestnote->user_data_value;

				$session_detail = $order_info['sessionInfo'];
				$session_start = $session_detail['session_start'];

				$templateEngine->assign('session', $order_info['sessionInfo']);
				$templateEngine->assign('sessionInfo', $order_info['sessionInfo']);
				$templateEngine->assign('customer_name', $order_info['customer_name']);
				$templateEngine->assign('customerName', $order_info['customer_name']);

				if (!empty($User->secondary_email))
				{
					$corporate_crate_client = CCorporateCrateClient::corporateCrateClientDetails($User->secondary_email);
				}
				else
				{
					$corporate_crate_client = false;
				}
				$templateEngine->assign('corporate_crate_client', $corporate_crate_client);

				$sideDishesArray = null;
				$preassembledArray = array();

				if (!empty($sideDishReport) && $sideDishReport == true)
				{
					foreach ($order_info['menuInfo'] as $key => $var)
					{
						if (!empty($var))
						{
							if (($key == "Chef Touched Selections"))
							{
								//unset($order_info['menuInfo'][$key]);
								//unset($order_info['menuInfo']['markup_discount_scalar']);
								$sideDishesArray = $order_info['menuInfo'][$key];
								$foundSideDishes = true;
								break;
							}
						}
					}
				}
				else if (!empty($preassembledReport) && $preassembledReport == true)
				{
					foreach ($order_info['menuInfo'] as $key => $var)
					{
						if (!empty($var) && is_array($var))
						{
							foreach ($var as $subkey => $subvar)
							{
								if (isset($subvar['is_preassembled']) && $subvar['is_preassembled'] == 1)
								{
									$preassembledArray[$subkey] = $subvar;
									$foundPreassembled = true;
								}

								if (!empty($subvar['sub_items']) && !empty($subvar['sub_items']['has_pre_assembled']))
								{
									foreach ($subvar['sub_items']['menu_items'] as $sub_item)
									{
										if (isset($sub_item->is_preassembled) && $sub_item->is_preassembled == 1)
										{
											$preassembledArray[$subkey] = $subvar;
											$foundPreassembled = true;
										}
									}
								}
							}
						}
					}

					if (!empty($relocatedItems))
					{
						foreach ($relocatedItems as $miid => $itemData)
						{
							if (isset($itemData['is_preassembled']) && $itemData['is_preassembled'] == 1)
							{
								if (isset($preassembledArray[$miid]))
								{
									$preassembledArray[$miid]['qty']++;
								}
								else
								{
									$preassembledArray[$miid] = $itemData;
									$foundPreassembled = true;
								}
							}
						}
					}
				}

				if (!empty($sideDishReport) || !empty($preassembledReport))
				{
					if (empty($order_info['menuInfo']))
					{
						continue;
					}
				}

				$templateEngine->assign('menuInfo', $order_info['menuInfo']);

				$address = (!empty($User->address_line2) ? $User->address_line1 . "<br />" . $User->address_line2 : $User->address_line1);

				$user_array[]['customer_id'] = $User->id;

				$SessionReport = new CSessionReports();
				$history_list = $SessionReport->getCustomerHistory($user_array, $session_id, $order->store_id);

				$drData['bookings_made'] = 'N/A';

				if (!empty($history_list[$User->id]['bookings_made']))
				{
					$drData['bookings_made'] = $history_list[$User->id]['bookings_made'];
				}

				if (!empty($history_list[$User->id]['last_session_attended']))
				{
					$templateEngine->assign('last_session_attended', $history_list[$User->id]['last_session_attended']);
					$templateEngine->assign('last_session_attended_type', $history_list[$User->id]['last_session_attended_type']);
					$templateEngine->assign('last_session_attended_subtype', $history_list[$User->id]['last_session_attended_subtype']);
					//$templateEngine->assign('last_session_attended', '2013-01-01 00:00:00');
				}
				else
				{
					$templateEngine->assign('last_session_attended', null);
				}

				$other_details[$counter] = array(
					'dr_info' => $drData,
					'user' => $order_info['customer_name'],
					'user_id' => $User->id,
					'session' => $session_start,
					'session_id' => $session_id,
					'sidereport' => $sideDishReport,
					'assemblereport' => $preassembledReport,
					'address' => $address,
					'city' => $User->city,
					'state_id' => $User->state_id,
					'postal_code' => $User->postal_code,
					'email' => $User->primary_email,
					'day_phone' => $User->telephone_1,
					'eve_phone' => $User->telephone_2,
					'telephone_1_call_time' => $User->telephone_1_call_time,
					'orderInfo' => $order_info['orderInfo'],
					'menuInfo' => $order_info['menuInfo']
				);

				//print_r($other_details);
				$skip = false;
				if ($sideDishReport && $sideDishesArray == null)
				{
					$skip = true;
				}

				if ($preassembledReport && empty($preassembledArray))
				{
					$skip = true;
				}

				$templateEngine->assign('showlongserving', true);

				if (!$skip)
				{
					$User->getUserPreferences();

					$templateEngine->assign('user', clone $User);
					$templateEngine->assign('store_supports_DFL', $supportsDinnersForLife);
					$templateEngine->assign('orderInfo', $order_info['orderInfo']);
					$templateEngine->assign('sidesArray', $sideDishesArray);
					$templateEngine->assign('preassembledArray', $preassembledArray);
					$templateEngine->assign('paymentInfo', $order_info['paymentInfo']);
					$templateEngine->assign('showInstructions', true);
					$templateEngine->assign('balanceDue', $balanceDue);
					$templateEngine->assign('booking_type', $booking->booking_type);
					$templateEngine->assign('showNonEditableAdminNotes', true);
					$templateEngine->assign('customer_view', $customer_print_view);
					$templateEngine->assign('isPrintable', true);
					$templateEngine->assign('dr_info', $drData);
					$templateEngine->assign('plate_points', $User->getPlatePointsSummary($order));
					$templateEngine->assign('consecutive_order_status', COrders::getOrdersSequenceStatus($order_info['orderInfo']['id'], $order_info['orderInfo']['user_id'], $order_info['sessionInfo']['session_type'], $order_info['sessionInfo']['session_start'], $order_info['orderInfo']['servings_total_count']));

					$html_array[$counter++] = $templateEngine->render('/admin/' . $filename);
				}
			} // if found user
		} // while booking

		if (($sideDishReport && !$foundSideDishes) || ($preassembledReport && !$foundPreassembled))
		{
			$templateEngine->assign('store_supports_DFL', $supportsDinnersForLife);
			$templateEngine->assign('orderInfo', $order_info['orderInfo']);
			$templateEngine->assign('sidesArray', null);
			$templateEngine->assign('preassembledArray', null);
			$templateEngine->assign('paymentInfo', $order_info['paymentInfo']);
			$templateEngine->assign('showInstructions', true);
			$templateEngine->assign('balanceDue', $balanceDue);
			$templateEngine->assign('booking_type', $booking->booking_type);
			$templateEngine->assign('showNonEditableAdminNotes', true);
			$templateEngine->assign('customer_view', $customer_print_view);
			$templateEngine->assign('isPrintable', true);

			if (!isset($order_info['sessionInfo']))
			{
				$SessionObj = DAO_CFactory::create('session');
				$SessionObj->id = $session_id;
				$SessionObj->find(true);
				$templateEngine->assign('session', $SessionObj->toArray());
			}

			$html_array[$counter++] = $templateEngine->render('/admin/' . $filename);

			if (empty($other_details))
			{
				$other_details[] = array(
					'sidereport' => $sideDishReport,
					'assemblereport' => $preassembledReport
				);
			}
		}

		return $html_array;
	}
}

?>