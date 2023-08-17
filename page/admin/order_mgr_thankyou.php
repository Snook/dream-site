<?php // order_mgr_thankyou.php
require_once("includes/CPageAdminOnly.inc");

class page_admin_order_mgr_thankyou extends CPageAdminOnly
{

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}
	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}
	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$tpl = CApp::instance()->template();

		$order_id = null;

		if (isset($_REQUEST["order"]) && $_REQUEST["order"])
		{
			$order_id = CGPC::do_clean($_REQUEST["order"],TYPE_INT);
		}

		if (!$order_id)
		{
			throw new Exception('invalid order id');
		}

		if (!empty($_REQUEST['session_full_with_saved']) and $_REQUEST['session_full_with_saved'] == 'true')
		{
			$tpl->setErrorMsg('There are no more slots available for this session but there are saved orders requesting this session.');
		}

		// TODO: this is quick way to figure out if this is a Delivered order - we may want to be more explicit
		$OrderTypeFinder = new DAO();
		$isDeliveredOrder = false;
		$OrderTypeFinder->query("select b.id from booking b join session s on b.session_id = s.id and s.session_type = '" .CSession::DELIVERED .  "' where b.status = 'ACTIVE' and b.is_deleted = 0 and b.order_id = $order_id");
		if ($OrderTypeFinder->N > 0)
		{
			$isDeliveredOrder = true;
			$Order = new COrdersDelivered();
		}
		else
		{
			$Order = DAO_CFactory::create('orders');
		}

		$tpl->assign("is_delivered_order", $isDeliveredOrder);

		$Order->id = $order_id;
		$found = $Order->find(true);

		if (!$found)
		{
			throw new Exception('invalid order');
		}

		$User = DAO_CFactory::create('user');
		$User->id = $Order->user_id;
		$User->find(true);

		$User->getMembershipStatus($Order->id);

		//update the user's home store if it changed
		if (($User->user_type == CUser::CUSTOMER) and ($User->home_store_id != $Order->store_id))
		{
			$OldUser = clone($User);
			$User->home_store_id = $Order->store_id;
			$User->update($OldUser);
		}

		$orderInfoArray = COrders::buildOrderDetailArrays($User, $Order, false,false,false, false,$isDeliveredOrder	);

		$nextMenuTimeStamp = CMenu::getNextMenuTimestamp($orderInfoArray['menuInfo']['menu_id']);

		if (!empty($nextMenuTimeStamp))
		{
			$tpl->assign('nextMenuParm', "&month=" . $nextMenuTimeStamp);
		}
		else
		{
			$tpl->assign('nextMenuParm', '');
		}


		$LTD_donation = 0;
		if (!empty($Order->ltd_round_up_value) and is_numeric($Order->ltd_round_up_value))
		{
		    $LTD_donation = $Order->ltd_round_up_value;
		}

		if ($orderInfoArray['bookingStatus'] == CBooking::CANCELLED)
		{
			$balanceDue = $Order->getPaymentsPending() - $LTD_donation;
		}
		else
		{
			$balanceDue = COrders::std_round(($Order->grand_total + $LTD_donation)  - $Order->getPaymentsPending());
		}
		$tpl->assign('balanceDue', $balanceDue);

		$coupon = $Order->getCoupon();

		if ($coupon)
		{
			$tpl->assign('coupon_title', $coupon->coupon_code_short_title);
		}

		$orderInfoArray['number_servings'] = $Order->getNumberServings();

		$drData = CDreamRewardsHistory::getCurrentStateForUserShortForm($User);

		$orderCustomization = OrdersCustomization::getInstance($Order);
		$str = $orderCustomization->mealCustomizationToStringSelectedOnly(',');
		$tpl->assign('meal_customization_string',$str);

		if ($drData and $drData['program_version'] < 3)
		{
			$downgrade = false;
			if (isset($User->dr_downgraded_order_count) and $User->dr_downgraded_order_count > 0)
			{
				$downgrade = $User->dr_downgraded_order_count;
			}

			$drData['order_level'] = CDreamRewardsHistory::shortLevelDesc($orderInfoArray['orderInfo']['dream_rewards_level']);
			if (isset($orderInfoArray['orderInfo']['is_dr_downgraded_order']) and $orderInfoArray['orderInfo']['is_dr_downgraded_order'])
			{
				if ($downgrade and $downgrade > 1)
				{
					$drData['order_level'] .= " (%5 off this and the next $downgrade orders)";
				}
				else if ($downgrade and $downgrade > 0)
				{
					$drData['order_level'] .= " (%5 off this and the next order)";
				}
				else
				{
					$drData['order_level'] .= " (%5 off this order)";
				}
			}

			$drData['next_reward'] = CDreamRewardsHistory::nextRewardDataByLevel($drData['program_version'], $orderInfoArray['orderInfo']['dream_rewards_level'], $downgrade);
			$drData['next_reward'] = $drData['next_reward']['display_text'];
		}
		else
		{
			$drData['status'] = "N/A";
			$drData['level'] = "N/A";
			$drData['order_level'] = "N/A";
			$drData['next_reward'] = "N/A";
		}

		if (!empty($User->secondary_email))
		{
			$corporate_crate_client = CCorporateCrateClient::corporateCrateClientDetails($User->secondary_email);
		}
		else
		{
			$corporate_crate_client = false;
		}

		$tpl->assign('user', $User);
		$tpl->assign('corporate_crate_client', $corporate_crate_client);
		$tpl->assign('dr_info', $drData);
		$tpl->assign('plate_points', $User->getPlatePointsSummary($Order));

		//TODO: evanl add referral rewards summary info

		$tpl->assign('bookingInfo', $orderInfoArray['bookingInfo']);
		$tpl->assign('orderInfo', $orderInfoArray['orderInfo']);
		$tpl->assign('bookingStatus', $orderInfoArray['bookingStatus']);
		$tpl->assign('menuInfo', $orderInfoArray['menuInfo']);
		$tpl->assign('paymentInfo', $orderInfoArray['paymentInfo']);
		$tpl->assign('sessionInfo', $orderInfoArray['sessionInfo']);
		$tpl->assign('storeInfo', $orderInfoArray['storeInfo']);
		$tpl->assign('customerName', $orderInfoArray['customer_name']);
		$tpl->assign('confirmation', $Order->order_confirmation);
		$tpl->assign('order_id', $Order->id);
	}
}

?>