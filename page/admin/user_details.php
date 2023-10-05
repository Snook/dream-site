<?php // page_user_details.php

require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStatesAndProvinces.php");
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CEnrollmentPackage.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CStoreCredit.php');

class page_admin_user_details extends CPageAdminOnly
{

	private $can = array(
		'email_customer' => false,
		'place_order' => false,
		'set_access_levels' => false,
		'edit_user_details' => false,
		'set_preferred_status' => false,
		'delete_user' => false,
		'unset_home_store' => false,
		'get_all_user_data' => false,
		'store_specific_store_credit_view' => false,
		'modify_referrals' => false,
		'modify_credit_cards' => false,
		'modify_store_credit' => false,
		'modify_dream_rewards' => false,
		'modify_delayed_payment_tc' => false,
		'view_referral_sources' => false,
		'view_user_history' => false,
		'view_event_log' => false,
		'view_platepoints_history' => false,
		'convert_to_platepoints' => false,
		'join_to_platepoints' => false,
		'all_user_types' => false,
		'modify_user_preferences_round_up' => false,
		'display_corporate_crate_email' => false
	);

	private $canEmailCustomer = true;
	private $canEditInfo = true;
	private $canPlaceOrder = true;
	private $canSetAccessLevels = true;
	private $canSetPrefStatus = true;
	private $canViewReferralSources = true;
	private $canModifyReferrals = true;
	private $canModifyCreditCards = true;
	private $canModifyStoreCredit = true;
	private $canModifyDreamRewards = true;

	private $candelete = false;
	private $canUnsetHomeStore = false;
	private $getAllUserData = false;
	private $storeSpecificStoreCreditView = false;
	private $canViewUserHistory = false;
	private $canViewEventLog = false;
	private $canViewPlatePointsHistory = false;
	private $canJoinToPlatePoints = false;

	function runEventCoordinator()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['all_user_types'] = true;

		if (!empty(CBrowserSession::getCurrentFadminStoreObj()->supports_ltd_roundup))
		{
			$this->can['modify_user_preferences_round_up'] = true;
		}

		$this->canEmailCustomer = true;
		$this->canEditInfo = true;

		$this->canPlaceOrder = true;
		$this->canSetAccessLevels = false;
		$this->canSetPrefStatus = true;
		$this->canViewReferralSources = true;
		$this->canModifyReferrals = true;
		$this->canModifyCreditCards = true;
		$this->canModifyStoreCredit = true;
		$this->canModifyDreamRewards = false;
		$this->runUserDetails();
	}

	function runOpsLead()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['place_order'] = true;
		$this->can['modify_referrals'] = true;
		$this->can['modify_credit_cards'] = true;
		$this->can['modify_store_credit'] = true;
		$this->can['modify_dream_rewards'] = true;
		$this->can['modify_delayed_payment_tc'] = true;
		$this->can['view_referral_sources'] = true;
		$this->can['set_access_levels'] = true;
		$this->can['set_preferred_status'] = true;
		$this->can['all_user_types'] = true;

		if (!empty(CBrowserSession::getCurrentFadminStoreObj()->supports_ltd_roundup))
		{
			$this->can['modify_user_preferences_round_up'] = true;
		}

		$this->canEmailCustomer = true;
		$this->canSetAccessLevels = true;
		$this->canEditInfo = true;
		$this->canViewReferralSources = true;
		$this->canUnsetHomeStore = true;

		$this->runUserDetails();
	}

	function runManufacturerStaff()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['all_user_types'] = true;

		$this->canEmailCustomer = true;
		$this->canEditInfo = true;

		$this->canPlaceOrder = false;
		$this->canSetAccessLevels = false;
		$this->canSetPrefStatus = false;
		$this->canViewReferralSources = false;
		$this->canModifyReferrals = false;
		$this->canModifyCreditCards = false;
		$this->canModifyStoreCredit = false;
		$this->canModifyDreamRewards = false;
		$this->runUserDetails();
	}

	function runFranchiseStaff()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['place_order'] = true;
		$this->can['modify_credit_cards'] = true;
		$this->can['modify_store_credit'] = true;
		$this->can['modify_dream_rewards'] = true;
		$this->can['modify_delayed_payment_tc'] = true;
		$this->can['all_user_types'] = true;

		if (!empty(CBrowserSession::getCurrentFadminStoreObj()->supports_ltd_roundup))
		{
			$this->can['modify_user_preferences_round_up'] = true;
		}

		$this->canEmailCustomer = true;
		$this->canEditInfo = true;

		$this->canSetAccessLevels = false;
		$this->canSetPrefStatus = false;
		$this->canViewReferralSources = false;
		$this->canModifyReferrals = false;
		$this->runUserDetails();
	}

	function runFranchiseLead()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['place_order'] = true;
		$this->can['modify_referrals'] = true;
		$this->can['modify_credit_cards'] = true;
		$this->can['modify_store_credit'] = true;
		$this->can['modify_dream_rewards'] = true;
		$this->can['modify_delayed_payment_tc'] = true;
		$this->can['view_referral_sources'] = true;
		$this->can['all_user_types'] = true;

		if (!empty(CBrowserSession::getCurrentFadminStoreObj()->supports_ltd_roundup))
		{
			$this->can['modify_user_preferences_round_up'] = true;
		}

		$this->canEmailCustomer = true;
		$this->canEditInfo = true;
		$this->canViewReferralSources = true;

		$this->canSetAccessLevels = false;
		$this->canSetPrefStatus = false;

		$this->runUserDetails();
	}

	function runFranchiseManager()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['place_order'] = true;
		$this->can['modify_referrals'] = true;
		$this->can['modify_credit_cards'] = true;
		$this->can['modify_store_credit'] = true;
		$this->can['modify_dream_rewards'] = true;
		$this->can['modify_delayed_payment_tc'] = true;
		$this->can['view_referral_sources'] = true;
		$this->can['set_access_levels'] = true;
		$this->can['set_preferred_status'] = true;
		$this->can['all_user_types'] = true;

		if (!empty(CBrowserSession::getCurrentFadminStoreObj()->supports_ltd_roundup))
		{
			$this->can['modify_user_preferences_round_up'] = true;
		}

		$this->canEmailCustomer = true;
		$this->canSetAccessLevels = true;
		$this->canEditInfo = true;
		$this->canViewReferralSources = true;
		$this->canUnsetHomeStore = true;

		$this->runUserDetails();
	}

	function runFranchiseOwner()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['place_order'] = true;
		$this->can['modify_referrals'] = true;
		$this->can['modify_credit_cards'] = true;
		$this->can['modify_store_credit'] = true;
		$this->can['modify_dream_rewards'] = true;
		$this->can['modify_delayed_payment_tc'] = true;
		$this->can['view_referral_sources'] = true;
		$this->can['set_access_levels'] = true;
		$this->can['set_preferred_status'] = true;
		$this->can['all_user_types'] = true;

		if (!empty(CBrowserSession::getCurrentFadminStoreObj()->supports_ltd_roundup))
		{
			$this->can['modify_user_preferences_round_up'] = true;
		}

		$this->canEmailCustomer = true;
		$this->canSetAccessLevels = true;
		$this->canEditInfo = true;
		$this->canSetPrefStatus = true;
		$this->canViewReferralSources = true;
		$this->canUnsetHomeStore = true;

		$this->runUserDetails();
	}

	function runHomeOfficeStaff()
	{
		$this->can['get_all_user_data'] = true;
		$this->can['unset_home_store'] = true;
		$this->can['modify_credit_cards'] = true;
		$this->can['modify_store_credit'] = true;
		$this->can['modify_dream_rewards'] = true;
		$this->can['modify_delayed_payment_tc'] = true;
		$this->can['view_user_history'] = true;
		$this->can['view_event_log'] = true;
		$this->can['view_platepoints_history'] = true;
		$this->can['view_referral_sources'] = true;
		$this->can['all_user_types'] = true;
		$this->can['modify_user_preferences_round_up'] = true;

		$this->getAllUserData = true;
		$this->canViewUserHistory = true;
		$this->canUnsetHomeStore = true;

		$this->canEmailCustomer = false;
		$this->canSetAccessLevels = false;
		$this->canEditInfo = false;
		$this->canSetPrefStatus = false;
		$this->canPlaceOrder = false;
		$this->runUserDetails();
	}

	function runHomeOfficeManager()
	{
		$this->can['email_customer'] = true;
		$this->can['edit_user_details'] = true;
		$this->can['place_order'] = true;
		$this->can['modify_referrals'] = true;
		$this->can['modify_credit_cards'] = true;
		$this->can['modify_store_credit'] = true;
		$this->can['modify_dream_rewards'] = true;
		$this->can['modify_delayed_payment_tc'] = true;
		$this->can['get_all_user_data'] = true;
		$this->can['unset_home_store'] = true;
		$this->can['view_user_history'] = true;
		$this->can['view_event_log'] = true;
		$this->can['view_platepoints_history'] = true;
		$this->can['view_referral_sources'] = true;
		$this->can['set_preferred_status'] = true;
		$this->can['all_user_types'] = true;
		$this->can['modify_user_preferences_round_up'] = true;

		$this->canEmailCustomer = true;
		$this->canEditInfo = true;
		$this->canSetPrefStatus = true;
		$this->canPlaceOrder = true;
		$this->getAllUserData = true;
		$this->canViewUserHistory = true;
		$this->canViewEventLog = true;
		$this->canUnsetHomeStore = true;

		$this->canSetAccessLevels = true;

		$this->runUserDetails();  // we don't want the home office to delete ?
	}

	function runSiteAdmin()
	{
		// site admin access to all permissions
		foreach ($this->can as $perm => $value)
		{
			$this->can[$perm] = true;
		}

		$this->candelete = true;
		$this->getAllUserData = true;
		$this->canViewUserHistory = true;
		$this->canViewEventLog = true;
		$this->canUnsetHomeStore = true;

		if (isset($_REQUEST['action']) && isset($_REQUEST['id']))
		{
			if ($_REQUEST['action'] == 'delete')
			{
				$tpl = CApp::instance()->template();

				$user_id = CGPC::do_clean( $_REQUEST['id'],TYPE_INT);

				$User = DAO_CFactory::create('user');
				$User->id = $user_id;

				if (!$User->find(true))
				{
					$tpl->setStatusMsg('User not found.');

					CApp::bounce('/backoffice/list_users');
				}

				$userCopy = clone($User);
				if (!$User->delete())
				{
					$tpl->setStatusMsg('The account has pending orders and cannot be deleted.');

					CApp::bounce('/backoffice/order-history?id=' . $User->id);
				}
				else
				{
					CEmail::accountCloseRequestToStore($userCopy);
					CLog::RecordNew(CLog::DEBUG, 'User deleted, User ID: ' . $User->id . ', Deleted by: ' . CUser::getCurrentUser()->id);

					$tpl->setStatusMsg('The account has been deleted.');

					CApp::bounce('/backoffice/list_users');
				}
			}

			if ($_REQUEST['action'] == 'confirmAccountInfoSent')
			{
				$tpl = CApp::instance()->template();
				$user_id = CGPC::do_clean($_REQUEST['id'],TYPE_INT);

				$task = CUserAccountManagement::fetchTask($user_id,CUserAccountManagement::ACTION_SEND_ACCOUNT_INFORMATION);
				if(is_null($task)){
					$tpl->setStatusMsg('No data request was found for this user.');
				}else{
					CUserAccountManagement::updateTask($task->id,CUserAccountManagement::ACTION_SEND_ACCOUNT_INFORMATION,CUserAccountManagement::STATUS_COMPLETED);

					$tpl->setStatusMsg('The account data request has been marked complete.');
				}

			}
		}

		$this->runUserDetails();
	}

	function runUserDetails()
	{
		$tpl = CApp::instance()->template();
		$currentStore = CBrowserSession::getCurrentFadminStore();

		$isfranchiseowner = false;

		$user_id = false;

		if (!empty($_GET['id']) && is_numeric($_GET['id']))
		{
			$user_id = $_GET['id'];
		}

		if ($user_id)
		{
			$AdminUser = CUser::getCurrentUser();
			$User = DAO_CFactory::create('user');
			$User->id = $user_id;

			if (!$this->can['all_user_types'])
			{
				$User->user_type = CUser::CUSTOMER;
			}

			$Store = DAO_CFactory::create('store');
			$Store->is_deleted = null; //we don't care, don't put it on the WHERE clause or the LEFT join won't work
			$User->joinAdd($Store, 'LEFT');
			$User->selectAdd();
			$User->selectAdd('user.*, address.state_id, address.city, address.postal_code, address.address_line1, address.address_line2, store.store_name, store.supports_corporate_crate');
			$userAddress = DAO_CFactory::create('address');
			$userAddress->whereAdd("address.location_type = '" . CAddress::BILLING . "'");
			$User->joinAdd($userAddress, array(
				'joinType' => 'LEFT',
				'useWhereAsOn' => true
			));
			$isFound = $User->find(true);

			$User->getUserPreferences();
			$ShippingAddress = $User->getShippingAddress();

			$tpl->assign('shipping_address', $ShippingAddress);

			if (!$isFound)
			{
				$tpl->setErrorMsg('Guest not found');
				CApp::bounce('/backoffice/list_users');
			}

			// Login as User
			if ((DD_SERVER_NAME != 'LIVE' && !empty($_GET['login_as_user'])) || (DD_SERVER_NAME == 'LIVE' && CUser::getCurrentUser()->user_type == 'SITE_ADMIN' && $User->user_type != CUser::SITE_ADMIN && !empty($_GET['login_as_user'])))
			{
				CBrowserSession::setValue('DDUID', $User->id, false, true, false);
				CBrowserSession::setValue('FAUID', $AdminUser->id, false, true, false);

				$BrowserSession = DAO_CFactory::create('browser_sessions');
				$BrowserSession->browser_session_key = CBrowserSession::getValue(CBrowserSession::getSessionCookieName());
				$BrowserSession->find(true);
				$BrowserSession->user_id = $User->id;
				$BrowserSession->update();

				$tpl->setStatusMsg('Now logged in as <span class="font-weight-bold">' . $User->firstname . ' ' . $User->lastname . '</span>.');

				CApp::bounce();
			}

			// unset home store
			if ($this->canUnsetHomeStore && !empty($_GET['action']) && $_GET['action'] == 'unsethome')
			{
				$User->home_store_id = "null";
				$User->update();
				$tpl->setErrorMsg('Guest removed from store');
				CApp::bounce('/backoffice/list_users');
			}

			$isObserveOnlyAccount = $User->is_partial_account;

			$PlatePointsStatus = CPointsUserHistory::getPlatePointsStatus($currentStore, $User);
			$MembershipStatus = $User->getMembershipStatus();
			$userIsPreferredSomewhere = $User->isUserPreferred();

			if ($PlatePointsStatus['storeSupportsPlatePoints'])
			{
				$platePointsSummary = $User->getPlatePointsSummary();

				if ($PlatePointsStatus['userIsEnrolled'])
				{
					$this->canModifyDreamRewards = false;
					$this->canViewPlatePointsHistory = true;
					$tpl->assign('available_pp_credit', $platePointsSummary['available_credit']);
				}
				else if ($userIsPreferredSomewhere)
				{

					if (($User->dream_reward_status == 1 || $User->dream_reward_status == 3) && $User->dream_rewards_version == 2)
					{
						$this->canModifyDreamRewards = true;
					}
					else
					{
						$this->canModifyDreamRewards = false;
					}

					$this->canJoinToPlatePoints = true;
				}
				else
				{
					$this->canJoinToPlatePoints = true;
					// if deactivated allow the store to reactivate
					if ($User->dream_reward_status == 2)
					{
						$this->canModifyDreamRewards = true;
					}
					else
					{
						$this->canModifyDreamRewards = false;
					}
				}
			}

			$aProps = $User->toArray();

			$aProps['platePointsData'] = $platePointsSummary;
			$aProps['membershipData'] = $MembershipStatus;

			$Orders = DAO_CFactory::create('orders');
			$Orders->user_id = $user_id;
			if ($NumOrders = $Orders->find())
			{
				$Booked = DAO_CFactory::create('booking');
				$Booked->user_id = $user_id;
				$Booked->status = 'CANCELLED';
				$NumCancelledOrders = $Booked->find();
			}

			if (empty($User->primary_email))
			{
				$login = DAO_CFactory::create('user_login');
				$login->user_id = $User->id;
				$login->find(true);

				$aProps['login_username'] = $login->ul_username;
			}

			$aProps['numorders'] = $NumOrders;
			if (!empty($NumCancelledOrders))
			{
				$aProps['numcancelledorders'] = $NumCancelledOrders;
			}
			else
			{
				$aProps['numcancelledorders'] = 0;
			}
			$aProps['state'] = CStatesAndProvinces::toAbbrev($User->state_id);
			$aProps['telephone_1_call_time'] = $User->getFieldLabel($aProps['telephone_1_call_time']);
			$aProps['telephone_2_call_time'] = $User->getFieldLabel($aProps['telephone_2_call_time']);
			$aProps['gender'] = $User->getFieldLabel($aProps['gender']);
			$aProps['preferences'] = $User->preferences;


			$aProps['rsvp_history'] =  $User->getRSVPHistory();

			if ($User->supports_corporate_crate)
			{
				$this->can['display_corporate_crate_email'] = true;
			}


			if (!empty($User->secondary_email))
			{
				$aProps['corporate_crate_client'] = CCorporateCrateClient::corporateCrateClientDetails($User->secondary_email);
			}
			else
			{
				$aProps['corporate_crate_client'] = false;
			}

			if (!empty($User->user_type) && $User->user_type != CUser::CUSTOMER)
			{
				$aProps['is_dd_employee'] = true;

			}
			else
			{
				$aProps['is_dd_employee'] = false;
			}

			$aProps['is_preferred_somewhere'] = $userIsPreferredSomewhere;



			$aProps['hasPendingDataRequest'] = $User->hasPendingDataRequest();

			$tpl->assign('user', $aProps);
			$tpl->assign('userObj', $User);
			// $tpl->assign('isFranchiseOwner', true);
			$tpl->assign('rows', array($aProps['id'] => $aProps));
			$tpl->assign('date', array(
				"this_M" => date("M", time()),
				"next_M" => date("M", strtotime('first day of next month')),
				"next_M_time" => strtotime('first day of next month')
			));

			// just an extra security precaution
			if ($User->primary_email === "admin@dreamdinners.com")
			{
				$this->canSetAccessLevels = false;
			}

			// just an extra layer of precaution
			if ($AdminUser->user_type != CUser::SITE_ADMIN && $User->primary_email === "admin@dreamdinners.com")
			{
				$this->canEditInfo = false;
			}

			if (isset($_REQUEST['back']))
			{
				$tpl->assign('back', $_REQUEST['back']);
			}
			else
			{
				$tpl->assign('back', '/backoffice/list_users');
			}

			switch ($AdminUser->user_type)
			{
				case CUser::SITE_ADMIN :
					if ($User->primary_email === "admin@dreamdinners.com")  // just in case
					{
						$this->candelete = false;
					}
					break;
				case CUser::FRANCHISE_OWNER :
					if ($User->user_type == CUser::FRANCHISE_OWNER || $User->user_type == CUser::SITE_ADMIN || $User->user_type == CUser::HOME_OFFICE_STAFF || $User->user_type == CUser::HOME_OFFICE_MANAGER)
					{
						$this->canSetAccessLevels = false;
						$this->canEditInfo = false;
					}

					//CES: ACCESS_CHANGE
					$isfranchiseowner = true;
					if ($User->user_type == CUser::FRANCHISE_STAFF || $User->user_type == CUser::GUEST_SERVER  ||
						$User->user_type == CUser::FRANCHISE_MANAGER || $User->user_type == CUser::OPS_LEAD)
					{
						$this->canSetAccessLevels = false;

						// If I as Franchise Owner have access to one store in common with the user then I can access his access levels
						$UTS = DAO_CFactory::create('user_to_store');
						$UTS->user_id = $User->id;
						$storesAr = array();
						$UTS->find();
						while ($UTS->find())
						{
							if ($UTS->fetch())
							{
								$storesAr[$UTS->store_id] = $UTS->id;
							}
						}

						$AdminUTS = DAO_CFactory::create('user_to_store');
						$AdminUTS->user_id = $AdminUser->id;
						$AdminUTS->find();

						while ($AdminUTS->fetch())
						{
							if (array_key_exists($AdminUTS->store_id, $storesAr))
							{
								$this->canSetAccessLevels = true;
								break;
							}
						}
					}
					$this->storeSpecificStoreCreditView = $currentStore;

					break;
				case CUser::FRANCHISE_STAFF :
					$this->canSetAccessLevels = false;
					$this->storeSpecificStoreCreditView = $currentStore;
					break;
				case CUser::GUEST_SERVER :
					$this->canSetAccessLevels = false;
					$this->storeSpecificStoreCreditView = $currentStore;
					break;
				case CUser::FRANCHISE_MANAGER :
					$this->canSetAccessLevels = true;
					$this->storeSpecificStoreCreditView = $currentStore;
					break;
				case CUser::OPS_LEAD :
					$this->canSetAccessLevels = true;
					$this->storeSpecificStoreCreditView = $currentStore;
					break;
				case CUser::HOME_OFFICE_STAFF :
					$this->canSetAccessLevels = false;
					break;
				case CUser::HOME_OFFICE_MANAGER :
					$this->canSetAccessLevels = true;
					break;
				default:
					break;
			}

			// you can always edit your own info
			if ($AdminUser->id == $User->id)
			{
				$this->canEditInfo = true;
			}
			// fetch the referral sources if allowed
			// --------------------------------------------------------------------------
			if ($this->canViewReferralSources)
			{
				$arSources = array();
				$UserRefSource = DAO_CFactory::create('user_referral_source');
				$UserRefSource->user_id = $user_id;
				$UserRefSource->find();
				while ($UserRefSource->fetch())
				{
					$source = strtolower($UserRefSource->source);
					$source = str_replace('_', ' ', $source);

					if ($source == "word of mouth")
					{
						$source = "friend";
					}

					$arSources[$source] = $UserRefSource->meta;
				}

				if (!isset($arSources['customer referral']))
				{
					$arSources['customer referral'] = 'No guest referral set for credit. Virtual Party friends are not set here.';
				}
				else
				{
					$tempUserObj = DAO_CFactory::create('user');
					$tempUserObj->query("select firstname, lastname from user where primary_email = '{$arSources['customer referral']}' and is_deleted = 0 limit 1");
					if ($tempUserObj->fetch())
					{
						$arSources['customer referral'] .= " - " . $tempUserObj->firstname . " " . $tempUserObj->lastname;
					}
				}

				$arSources['Guest Who Referred Them'] = $arSources['customer referral'];
				unset($arSources['customer referral']);

				$tpl->assign('canViewReferralSources', $this->canViewReferralSources);
				$tpl->assign('arReferralSources', $arSources);
			}

			if ($this->getAllUserData)
			{
				$tpl->assign('SFIData', CUSerData::getSFIDataForDisplay($user_id));
			}
			else
			{
				$tpl->assign('SFIData', CUSerData::getSFIDataForDisplay($user_id));
			}

			$StoreDAO = DAO_CFactory::create('store');
			$StoreDAO->id = $currentStore;
			$StoreDAO->find(true);

			$tpl->assign('isAAAReferred', false);
			if (CUserData::isUserAAAReferred($User) && CCouponCodeProgram::isCodeAcceptedByStore($StoreDAO->id, 'AAA01'))
			{
				$tpl->assign('isAAAReferred', true);
			}

			$rows = CStoreCredit::getActiveCreditByUser($user_id);
			$TODDRollup = 0;
			$IAFRollup = 0;
			$DirectRollup = 0;
			$GCRollup = 0;
			$hasDirectCredit = false;
			if ($this->storeSpecificStoreCreditView)
			{
				foreach ($rows as $thisCredit)
				{
					if ($thisCredit['store_id'] != $this->storeSpecificStoreCreditView)
					{
						continue;
					}

					if ($thisCredit['credit_type'] == 1)
					{
						$GCRollup += $thisCredit['amount'];
					}
					else if ($thisCredit['credit_type'] == 2)
					{
						if ($thisCredit['origination_type_code'] == 1 || $thisCredit['origination_type_code'] == 3)
						{
							$IAFRollup += $thisCredit['amount'];
						}
						else
						{
							$TODDRollup += $thisCredit['amount'];
						}
					}
					else if ($thisCredit['credit_type'] == 3)
					{
						$hasDirectCredit = true;
						$DirectRollup += $thisCredit['amount'];
					}
				}

				$tpl->assign('TODDRollup', $TODDRollup);
				$tpl->assign('IAFRollup', $IAFRollup);
				$tpl->assign('DirectRollup', $DirectRollup);
				$tpl->assign('hasDirectCredit', $hasDirectCredit);
				$tpl->assign('GCRollup', $GCRollup);
			}
			else
			{
				foreach ($rows as $thisCredit)
				{
					if ($thisCredit['credit_type'] == 1)
					{
						$GCRollup += $thisCredit['amount'];
					}
					else if ($thisCredit['credit_type'] == 3)
					{
						$hasDirectCredit = true;
						$DirectRollup += $thisCredit['amount'];
					}
				}

				$tpl->assign('hasDirectCredit', $hasDirectCredit);
				$tpl->assign('GCRollup', $GCRollup);
			}

			if ($isObserveOnlyAccount)
			{
				$this->canPlaceOrder = false;
				$this->canSetAccessLevels = false;
				$this->canSetPrefStatus = false;
			}


            if (defined('ENABLE_SMS_PREFERENCE') && ENABLE_SMS_PREFERENCE == true)
            {
                require_once("processor/account.php");
                $prefsProcessor = new processor_account();
                $results = $prefsProcessor->reconcileSMSOptinStatus($User);
            }

                $tpl->assign('DRControl', CDreamRewardsHistory::getCurrentStateForUser($User, $StoreDAO, true, false));
			$tpl->assign('candelete', $this->candelete);
			$tpl->assign('isPartialAccount', $isObserveOnlyAccount);
			$tpl->assign('isFranchiseOwner', $isfranchiseowner);
			$tpl->assign('canEmailCustomer', $this->canEmailCustomer);
			$tpl->assign('canPlaceOrder', $this->canPlaceOrder);
			$tpl->assign('canChangeAccess', $this->canSetAccessLevels);
			$tpl->assign('canEditInfo', $this->canEditInfo);
			$tpl->assign('canSetPrefStatus', $this->canSetPrefStatus);
			$tpl->assign('canModifyCreditCards', $this->canModifyCreditCards);
			$tpl->assign('canModifyStoreCredit', $this->canModifyStoreCredit);
			$tpl->assign('canModifyDreamRewards', $this->canModifyDreamRewards);
			$tpl->assign('canModifyReferrals', $this->canModifyReferrals);
			$tpl->assign('canUnsetHomeStore', $this->canUnsetHomeStore);
			$tpl->assign('canViewUserHistory', $this->canViewUserHistory);
			$tpl->assign('canViewEventLog', $this->canViewEventLog);
			$tpl->assign('canViewPlatePointsHistory', $this->canViewPlatePointsHistory);
			$tpl->assign('canJoinToPlatePoints', $this->canJoinToPlatePoints);
			$tpl->assign('currentStore', $currentStore);

			$tpl->assign('can', $this->can);
		}
		else
		{
			$tpl->setErrorMsg('guest not found');
			CApp::bounce('/backoffice/list_users');
		}
	}
}

?>