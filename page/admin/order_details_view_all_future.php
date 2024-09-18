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
require_once('includes/DAO/BusinessObject/COrders.php');

class page_admin_order_details_view_all_future extends CPageAdminOnly
{

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
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

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

    function runHomeOfficeStaff()
    {
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

 	function runFranchiseOwner()
 	{
		$output_array = NULL;
 		$tpl = CApp::instance()->template();

		if (isset ($_REQUEST["session_id"]))
		{  // pick the type of search query
			$session_id = CGPC::do_clean($_REQUEST["session_id"],TYPE_INT);

			$sessionDAO = DAO_CFactory::create('session');
			$sessionDAO->query("select s.store_id, st.supports_dream_rewards, st.allow_dfl_tool_access from session s join store st on st.id = s.store_id where s.id = $session_id");
			$sessionDAO->fetch();
			$showDreamRewardsStatus = true;
			$supportsDinnersForLife = false;
			if (!$sessionDAO->supports_dream_rewards)
			{
				$showDreamRewardsStatus =  false;
			}
			if ($sessionDAO->allow_dfl_tool_access)
			{
				$supportsDinnersForLife =  true;
			}

			$other_details = array();
			$output_array = self::create_view_all_future_orders($session_id, $other_details, $showDreamRewardsStatus);


			CLog::RecordReport('Session/Future Orders', "Session: $session_id" );


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
 	}

	static function create_view_all_future_orders ( $session_id , &$other_details, $showDreamRewardsStatus )
	{
		$html_array = array();
		$filename = NULL;
		$counter = 0;
		$templateEngine = new CTemplate();

		$order_info = array();

		$BookingObj = DAO_CFactory::create('booking');
		$BookingObj->query(" select outerQ.*, s3.id, b3.order_id from (select innerQ.session_start, min(s2.session_start) as nextSessionTime, b2.user_id, innerQ.store_id from
			(select b.id as booking_id, b.user_id, s.session_start, s.store_id from booking b
			join session s on b.session_id = s.id
			where b.session_id = $session_id and b.status = 'ACTIVE') as innerQ
			left join booking b2 on innerQ.user_id = b2.user_id and b2.id <> innerQ.booking_id and b2.status = 'ACTIVE'
			left join session s2 on s2.id = b2.session_id and s2.session_start > innerQ.session_start and innerQ.store_id = s2.store_id
			group by b2.user_id ) as outerQ
			left join session s3 on s3.store_id = outerQ.store_id and s3.session_start = nextSessionTime and s3.is_deleted = 0
			left join booking b3 on b3.user_id = outerQ.user_id and b3.session_id = s3.id and b3.status = 'ACTIVE'");

		while ($BookingObj->fetch())
		{
			$User = DAO_CFactory::create('user');

			$User->query("SELECT u.*, a.address_line1, a.address_line2, a.city, a.state_id, a.postal_code FROM user u 
			LEFT JOIN address a ON a.user_id = u.id and a.location_type = 'BILLING' 
			WHERE u.id = " . $BookingObj->user_id);


			if (isset($BookingObj->order_id))
			{
				if ($User->fetch())
				{
					$order_info = null;
					$order = DAO_CFactory::create('orders');
		  			$order->id = $BookingObj->order_id;
					$order->find(true);

					$pending = $order->getPaymentsPending();

					$LTD_donation = 0;
					if (!empty($order->ltd_round_up_value) and is_numeric($order->ltd_round_up_value))
					{
					    $LTD_donation = $order->ltd_round_up_value;
					}


					$balanceDue = COrders::std_round(($order->grand_total + $LTD_donation)- $pending);

					$order_info = COrders::buildOrderDetailArrays($User, $order, null, false, false, false, false,'FeaturedFirst');


					if (isset($order_info['orderInfo']['coupon_title']))
					{
						$templateEngine->assign('coupon_title', $order_info['orderInfo']['coupon_title']);
					}
					else
					{
						$templateEngine->assign('coupon_title', "");
					}

		  			if ($showDreamRewardsStatus)
		  			{
						$drData = CDreamRewardsHistory::getCurrentStateForUserShortForm($User);
			 			if($drData)
			 			{
			 				$downgrade = false;
			 				if (isset($User->dr_downgraded_order_count) && $User->dr_downgraded_order_count > 0)
			 					$downgrade = $User->dr_downgraded_order_count;
			 				$drData['order_level'] = CDreamRewardsHistory::shortLevelDesc($order->dream_rewards_level);
			 			 	if (isset($order->is_dr_downgraded_order) && $order->is_dr_downgraded_order)
		 					{
		 						if ($downgrade && $downgrade > 1)
		 							$drData['order_level'] .= " (%5 off this and the next $downgrade orders)";
		 						else if ($downgrade && $downgrade > 0)
		 							$drData['order_level'] .= " (%5 off this and the next order)";
		 						else
		 							$drData['order_level'] .= " (%5 off this order)";
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

					$guestnote = DAO_CFactory::create('user_data');
					$guestnote->user_data_field_id = GUEST_CARRY_OVER_NOTE;
					$guestnote->user_id = $User->id;
					$guestnote->store_id = CBrowserSession::getCurrentFadminStore();
					$guestnote->find(true);
					$order_info['orderInfo']['guest_carryover_notes'] = $guestnote->user_data_value;

					$session_detail = $order_info['sessionInfo'];
					$session_start = $session_detail['session_start'];
					$templateEngine->assign('DAO_session', $order_info["DAO_session"]);
					$templateEngine->assign('session', $order_info['sessionInfo']);
					$templateEngine->assign('sessionInfo', $order_info['sessionInfo']);
					$templateEngine->assign('customer_name', $order_info['customer_name']);
		  			$templateEngine->assign('customerName', $order_info['customer_name']);

					$templateEngine->assign('menuInfo', $order_info['menuInfo']);

					$address = (!empty($User->address_line2) ? $User->address_line1 . "<br />" . $User->address_line2 : $User->address_line1);

					$other_details[$counter] = array(
						'dr_info' => $drData,
						'user' => $order_info['customer_name'],
						'user_id' => $User->id,
						'session' => $session_start,
						'session_id' => $session_id,
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

					$templateEngine->assign('user', $User->toArray());
					$templateEngine->assign('orderInfo', $order_info['orderInfo']);
					$templateEngine->assign('paymentInfo', $order_info['paymentInfo']);
					$templateEngine->assign('showInstructions', TRUE);
					$templateEngine->assign('balanceDue', $balanceDue);
					$templateEngine->assign('showNonEditableAdminNotes', TRUE);
					$templateEngine->assign('isPrintable', true);
					$templateEngine->assign('dr_info', $drData);
					$templateEngine->assign('plate_points', $User->getPlatePointsSummary($order));
					$html_array[$counter++] = $templateEngine->render('/admin/order_details_table.tpl.php');
				}
			}// if found user
		} // while booking


		return $html_array;
	}
}
?>