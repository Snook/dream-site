<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CMail.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CUserData.php");
require_once("includes/DAO/BusinessObject/CFoodTesting.php");
require_once("page/admin/main.php");
require_once("page/admin/create_session.php");
require_once("processor/admin/processMetrics.php");

class processor_admin_fadmin_home_order_details extends CPageProcessor
{

	function runFranchiseStaff()
	{
		$this->mainProcessor();
	}

	function runFranchiseLead()
	{
		$this->mainProcessor();
	}

	function runEventCoordinator()
	{
		$this->mainProcessor();
	}

	function runOpsLead()
	{
		$this->mainProcessor();
	}

	function runOpsSupport()
	{
		$this->mainProcessor();
	}

	function runFranchiseManager()
	{
		$this->mainProcessor();
	}

	function runHomeOfficeManager()
	{
		$this->mainProcessor();
	}

	function runFranchiseOwner()
	{
		$this->mainProcessor();
	}

	function runSiteAdmin()
	{
		$this->mainProcessor();
	}

	function mainProcessor()
	{
		if (!empty($_POST['op']))
		{
			// Get order details
			if ($_POST['op'] == 'order_details' && !empty($_POST['order_id']) && is_numeric($_POST['order_id']))
			{
				$order_id = $_REQUEST['order_id'];
				$booking_id = null;
				if (!empty($_REQUEST['booking_id']))
				{
					$booking_id = $_REQUEST['booking_id'];
				}

				$tpl = new CTemplate();

				$booking = DAO_CFactory::create('booking');
				$booking->query("select b.*, s.session_start, s.session_type, s.session_class, s.store_id, s.menu_id from booking b
						join session s on s.id = b.session_id
				        where b.order_id = $order_id and b.is_deleted = 0
						order by b.timestamp_created desc, b.id desc");

				if (!$booking->fetch())
				{
					throw new Exception('Booking not found');
				}

				$isDeliveredOrder = false;
				if ($booking->session_type == CSession::DELIVERED)
				{
					$isDeliveredOrder = true;
				}

				$tpl->assign('is_delivered_order', $isDeliveredOrder);

				if ($isDeliveredOrder)
				{
					$order = new COrdersDelivered();
				}
				else
				{
					$order = DAO_CFactory::create('orders');
				}
				$order->id = $order_id;
				$order->find(true);

				//Meal Customization
				$orderCustomization = OrdersCustomization::getInstance($order);
				$str = $orderCustomization->mealCustomizationToStringSelectedOnly(',');
				$tpl->assign('meal_customization_string',$str);
				//these are the settings the store had at the time the order was placed
				$storeCustomizationSettings =  $orderCustomization->storeCustomizationSettingsToObj();
				$storeCustomizationSettings->initFromCurrentStoreCustomizationSettingsIfNull($order->getStore());
				$str = $orderCustomization->mealCustomizationToStringSelectedOnly(',');
				$tpl->assign('store_allows_meal_customization',$storeCustomizationSettings->allowsMealCustomization());
				$tpl->assign('store_allows_preassembled_customization',$storeCustomizationSettings->allowsPreAssembledCustomization());
				$tpl->assign('order_has_meal_customization',$orderCustomization->hasMealCustomizationPreferencesSet() && $order->opted_to_customize_recipes);


				$currentBooking = clone($booking);

				while ($booking->fetch())
				{
					if ($booking->status == CBooking::ACTIVE)
					{
						throw new Exception("There are more than 1 active bookings for this order which should never occur.");
					}
					else if ($booking->status == CBooking::CANCELLED)
					{
						CLog::Record("A booking that was cancelled changed state. OrderID = $order->id");
					}
					else if (false && $booking->status == CBooking::RESCHEDULED)
					{
						// add the rescheduled booking to the history and show the orig schedule date.

						$origSession = DAO_CFactory::create('session');
						$origSession->query("select session_start, id from session where id = " . $booking->session_id);
						// have to bypass DAO because cancelled sessions are marked as deleled. I assume it's ok to recover the date
						// for this message
						if ($origSession->fetch())
						{
							$statusHistory[$origSession->id] = array(
								'org_session_time' => $origSession->session_start,
								'rescheduled_time' => CTemplate::dateTimeFormat($booking->timestamp_updated)
							);
						}
					}
				}

				// --------------------------------- get user name
				$User = DAO_CFactory::create('user');
				$User->id = $currentBooking->user_id;
				$User->find(true);

				$User->getMembershipStatus($order->id);
				$address = $User->getPrimaryAddress();

				$tpl->assign('userAddress', $address);

				// ---------------------------------- get payment info.. might be 1 to n records...
				$cancelled = ($currentBooking->status == CBooking::CANCELLED);
				$PaymentInfo = COrders::buildPaymentInfoArray($order->id, CUser::FRANCHISE_OWNER, $currentBooking->session_start, $cancelled, $booking->session_type == CSession::DELIVERED);
				$tpl->assign('paymentInfo', $PaymentInfo);

				$pending = $order->getPaymentsPending($cancelled);

				$LTD_donation = 0;
				if (!empty($order->ltd_round_up_value) and is_numeric($order->ltd_round_up_value))
				{
					$LTD_donation = $order->ltd_round_up_value;
				}

				if ($cancelled)
				{
					$balanceDue = COrders::std_round($pending - $LTD_donation);
				}
				else
				{
					$balanceDue = COrders::std_round(($order->grand_total + $LTD_donation) - $pending);
				}

				$tpl->assign('balanceDue', $balanceDue);
				$tpl->assign('isCancelled', $cancelled);

				// --------------------------------- get store name
				$Store = DAO_CFactory::create('store');
				$Store->id = $currentBooking->store_id;
				$Store->find(true);

				$promo = null;
				//look for promo code
				if ($order->family_savings_discount_version == 2 && !empty($order->promo_code_id))
				{
					$promo = $order->getPromo();
				}

				$coupon = null;
				//look for coupon code
				if ($order->family_savings_discount_version == 2 && !empty($order->coupon_code_id))
				{
					$coupon = $order->getCoupon();
					$tpl->assign('coupon_title', $coupon->coupon_code_short_title);

					if ($coupon->discount_method != CCouponCode::FREE_MEAL)
					{
						$coupon = null;
					}
				}

				$order_info = COrders::buildOrderDetailArrays($User, $order, null, false, true, true, $isDeliveredOrder, 'FeaturedFirst');
				$menuInfo = $order_info['menuInfo'];//COrders::buildOrderItemsArray($order, $promo, $coupon);

				$orderArray = $order->toArray();

				$orderArray['number_servings'] = $order->getNumberServings();
				$tpl->assign('DAO_session', $order_info["DAO_session"]);
				$tpl->assign('sessionInfo', $order_info['sessionInfo']);
				$tpl->assign('menuInfo', $menuInfo);
				$tpl->assign('orderInfo', $order_info['orderInfo']);
				$tpl->assign('customer_view', 0);

				$drData = CDreamRewardsHistory::getCurrentStateForUserShortForm($User, true);

				if ($drData)
				{
					if (isset($drData['program_version']) && $drData['program_version'] > 0)
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
					$drData['status'] = "N/A";
					$drData['level'] = "N/A";
					$drData['order_level'] = "N/A";
				}

				$tpl->assign('consecutive_order_status', COrders::getOrdersSequenceStatus($orderArray['id'], $orderArray['user_id'], $booking->session_type, $booking->session_start, $orderArray['servings_total_count']));

				// get lifetime orders
				$tempObj = new DAO();
				$tempObj->query("select id from booking where status = 'ACTIVE' and is_deleted = 0 and user_id = " . $currentBooking->user_id);
				$tempObj->num_orders = $tempObj->N;

				$tpl->assign('user', $User);
				$tpl->assign('dr_info', $drData);
				$tpl->assign('plate_points', $User->getPlatePointsSummary($order));
				$tpl->assign('store_supports_DFL', $Store->allow_dfl_tool_access);


				if( $order_info['sessionInfo']['session_type'] === CSession::DELIVERED){
					$order_details_table = $tpl->fetch('admin/order_details_table_delivered.tpl.php');
				}else{
					$order_details_table = $tpl->fetch('admin/order_details_table.tpl.php');
				}

				list($historyArray, $bookingObj) = COrders::getOrderHistory($order_id);

				$history_tpl = new CTemplate();
				$history_tpl->assign('orders_history', $historyArray);
				$history_tpl->assign('storeInfo', array('id' => $Store->id));
				$history_html = $history_tpl->fetch('admin/subtemplate/individual_order_history.tpl.php');

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved Order Details.',
					'order_details_table' => $order_details_table,
					'history_table' => $history_html,
					'order_id' => $order_id,
					'booking_id' => $booking_id
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'No order id.'
				));
			}
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'No operation.'
			));
		}
	}
}

?>