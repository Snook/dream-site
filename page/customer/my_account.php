<?php
require_once('includes/DAO/BusinessObject/CTimezones.php');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CBooking.php');
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/CUserReferralSource.php');
require_once('includes/DAO/BusinessObject/CUserReferralSource.php');
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CDreamTasteEvent.php');
require_once('includes/DAO/BusinessObject/CSession.php');

class page_my_account extends CPage {

	function runPublic() {
		CApp::forceLogin('?page=my_account');
	}

	function runCustomer() {

		CApp::forceSecureConnection();
		$tpl = CApp::instance()->template();

		ini_set('memory_limit','512M');


		$User = CUser::getCurrentUser();
		$User->getUserPreferences();
		$isPreferred = $User->isUserPreferred() ;
		if ($isPreferred && !$User->platePointsData['transition_has_expired'])
		{
			$User->platePointsData['conversion_data'] = CPointsUserHistory::getPreferredUserConversionData($User);
		}
		else if (($User->platePointsData['status'] == 'in_DR2' || $User->platePointsData['isDeactivatedDRUser']) && !$User->platePointsData['transition_has_expired'])
		{
			$User->platePointsData['conversion_data'] = CPointsUserHistory::getDR2ConversionData($User);
		}
		$UserAddy = DAO_CFactory::create('address');
		$UserAddy->user_id = $User->id;
		$UserAddy->find(true);

		$User->addGuestLTDDonationTotals();

		$StoreArray = null;
		if (!empty( $User->home_store_id))
		{
			$Store = DAO_CFactory::create('store');
			$Store->id = $User->home_store_id;
			$Store->find(true);
			$StoreArray = $Store->toArray();
			$StoreArray['PHPTimeZone'] = CTimezones::getPHPTimeZoneFromID($Store->timezone_id);
		}
		else
		{
			$StoreArray['telephone_day']  = "No Home Store";
		}

		//static function getUsersOrders($User, $limitQuery = false, $since = false, $menu_id = false, $since_ordered = false, $type_of_order_array = false, $ordering_direction = 'asc')
		// Show orders since midnight today as upcoming

		$past_future_threshold = date("Y-m-d H:i:s", strtotime(date("Y-m-d")));

		$upcomingOrdersArray = COrders::getUsersOrders($User, false, $past_future_threshold, false, false, false, 'asc', false, true, true);
		$pastOrdersArray = COrders::getUsersOrders($User, 2, false, false, false, false, 'desc', $past_future_threshold, true);

		$upcomingOrdersArray = $this->sortOrdersByType($upcomingOrdersArray);
		$pastOrdersArray = $this->sortOrdersByType($pastOrdersArray);

		$User->getUsersLTD_RoundupOrders();

		$UserCreditsArray = CUser::getUserCreditArray($User);

		$UserTestRecipes = CUser::getUserTestRecipes($User);

		$usersFuturePastEvents = CDreamTasteEvent::getUsersFuturePastEvents($User->id);

		$currentMenuObj = DAO_CFactory::create('menu');
		$currentMenuObj->findCurrent();
		$currentMenuObj->fetch();

		$nextMonthlyDirectory = strtolower(CTemplate::dateTimeFormat($currentMenuObj->menu_name, FULL_MONTH));
		if (time() > strtotime('-5 days', strtotime($currentMenuObj->global_menu_end_date)))
		{
			$nextMonthlyDirectory = strtolower(CTemplate::dateTimeFormat(strtotime('first day of +1 month', strtotime($currentMenuObj->menu_name)) , FULL_MONTH));
		}

		$printMenus = CMenu::getActiveMenuArray();

		$tpl->assign('isPreferred', $isPreferred);
		$tpl->assign('printMenus', $printMenus);
		$tpl->assign('monthlyDirectory', $nextMonthlyDirectory);
		$tpl->assign('userTestRecipes', $UserTestRecipes);
		$tpl->assign('userCredits', $UserCreditsArray);
		$tpl->assign('usersFuturePastEvents', $usersFuturePastEvents);
		$tpl->assign('user', $User);
		$tpl->assign('user_address', $UserAddy->toArray());
		$tpl->assign('store', $StoreArray);
		$tpl->assign('DRState', CDreamRewardsHistory::getCurrentStateForUserShortForm($User));
		$tpl->assign('customerReferral', CUserReferralSource::getCustomerReferral($User->id) );
		$tpl->assign('future_orders', $upcomingOrdersArray);
		$tpl->assign('past_orders', $pastOrdersArray);
		$tpl->assign('platepoints_history', CPointsUserHistory::getHistory($User->id, 10));
		$tpl->assign('is_delivered_only', $User->hasDeliveredOrdersOnly());
	}

	private function sortOrdersByType($allOrders){

		$result = array('In-Store Assembly'=>array(),
						'Walk-In'=> array(),
						'Pick Up'=> array(),
						'Community Pick Up'=>array(),
						'Delivered'=>array(),
						'Home Delivery'=>array(),
						'Other'=>array());

		$hasOrders = false;
		foreach ($allOrders as $order){
			$hasOrders = true;
			if($order['session_type'] === CSession::DELIVERED){
				$result['Delivered'][] = $order;
				continue;
			}

			if($order['session_type_subtype'] === CSession::WALK_IN){
				$result['Walk-In'][] = $order;
				continue;
			}

			if($order['session_type_subtype'] === CSession::DELIVERY){
				$result['Home Delivery'][] = $order;
				continue;
			}

			if($order['session_type_subtype'] === CSession::REMOTE_PICKUP){
				$result['Community Pick Up'][] = $order;
				continue;
			}

			if($order['session_type_subtype'] === CSession::REMOTE_PICKUP_PRIVATE){
				$result['Community Pick Up'][] = $order;
				continue;
			}

			if($order['session_type'] === CSession::SPECIAL_EVENT){
				$result['Pick Up'][] = $order;
				continue;
			}

			if($order['session_type'] === CSession::STANDARD){
				$result['In-Store Assembly'][] = $order;
				continue;
			}

			$result['Other'][] = $order;
		}

		if($hasOrders){
			return $result;
		}else{
			return array();
		}

	}
}
?>