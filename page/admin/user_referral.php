<?php // page_user_details.php

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('processor/admin/guestSearch.php');
require_once('includes/class.inputfilter_clean.php');

class page_admin_user_referral extends CPageAdminOnly {


	static private function determineActiveReferral($ref_Array)
	{
		if (count($ref_Array) == 0)
			return false;

		if (count($ref_Array) == 1)
		{
			return $ref_Array[0]->id;
		}

		$selectedIndex = false;
		// is newest one an override
		if ($ref_Array[0]->origination_type_code == 5)
		{
			$selectedIndex = 0;
		}
		else
		{
			// if is_awarded then it's the one
			 foreach($ref_Array as $k => $val)
			 {
			 	if ($val->referral_status == 4 && !empty($val->first_order_id))
			 	{
			 		$selectedIndex = $k;
			 		break;
			 	}
			 }

			 // if ordered then it's the one
			 if ($selectedIndex === false)
			 {
				 foreach($ref_Array as $k => $val)
				 {
		 			if ($val->referral_status == 3 && !empty($val->first_order_id))
				 	{
				 		$selectedIndex = $k;
				 		break;
				 	}
				 }
			 }

			 // So there is more than 1 referral and none have resulted in an order
			 // just pick the first one I guess
			 if ($selectedIndex === false)
			 {
				return $ref_Array[0]->id;
			 }
		}
	}
	static function markOtherReferralsAsSuperseded($user_id, $blessedReferral)
	{

		$RefObj = DAO_CFactory::create('customer_referral');

		$RefObj->query("update customer_referral set referral_status = 7 where referred_user_id = $user_id and is_deleted = 0 and id <> $blessedReferral");

	}


	static function createUpdateReferralSource($referredUser, $referringUser, $referralID = 'null' )
	{

		$TestUser = DAO_CFactory::create('user');
		$TestUser->primary_email = $referringUser->primary_email;
		if (!$TestUser->find(true))
		{
			$tpl->setErrorMsg('The email address does not appear to be a valid Dream Dinners account.');
			return true; //true = error
		}

		$DAO_urs = DAO_CFactory::create('user_referral_source');
		$DAO_urs->source = 'CUSTOMER_REFERRAL';
		$DAO_urs->user_id = $referredUser->id;
		if ($DAO_urs->find(true))
		{
			$DAO_urs->meta = $referringUser->primary_email;
			$DAO_urs->customer_referral_id = $referralID;
			$DAO_urs->update();
		}
		else
		{
			$DAO_urs->meta = $referringUser->primary_email;
			$DAO_urs->customer_referral_id = $referralID;
			$DAO_urs->insert();
		}

		return $DAO_urs; // no error
	}


	function queueNewReferral($user, $referringUser, $referralSource)
	{
		$RefObj = CCustomerReferral::addOverrideReferral($user, $referringUser->id, $referringUser->firstname . ' ' . $referringUser->lastname, $referringUser->primary_email);


		self::markOtherReferralsAsSuperseded($user->id, $RefObj->id);

		$referralSourceCopy = clone($referralSource);
		$referralSource->customer_referral_id = $RefObj->id;
		$referralSource->update($referralSourceCopy);
	}

	function queueExistingReferral($user, $referringUser, $referralSource, $referralID, $firstOrderID)
	{
		$RefObj = DAO_CFactory::create('customer_referral');
		$RefObj->id = $referralID;
		if ($RefObj->find(true))
		{
			$refObjCopy = clone($RefObj);

			if (!empty($firstOrderID))
			{
				$RefObj->referral_status = 3;
				$RefObj->first_order_id = $firstOrderID;

			}
			else
			{
				$RefObj->referral_status = 3;
				$RefObj->first_order_id = $firstOrderID;
			}


		}

		self::markOtherReferralsAsSuperseded($user->id, $RefObj->id);

		$referralSourceCopy = clone($referralSource);
		$referralSource->customer_referral_id = $RefObj->id;
		$referralSource->update($referralSourceCopy);
	}


	static function bindAndRewardPendingReferral($user, $referringUser, $referralSource, $referralID, $storeID)
	{
		$RefObj = DAO_CFactory::create('customer_referral');
		$RefObj->id = $referralID;
		if ($RefObj->find(true))
		{

			if ($RefObj->referral_status == 3 && !empty($RefObj->first_order_id))
			{
				$RefObj->process_override_referral_award($RefObj->first_order_id, $storeID);

				self::markOtherReferralsAsSuperseded($user->id, $RefObj->id);

				$referralSourceCopy = clone($referralSource);
				$referralSource->customer_referral_id = $RefObj->id;
				$referralSource->update($referralSourceCopy);

			}
			else
			{
				//TODO: error
				throw new Exception("non-pending referral given to bindAndRewardPendingReferral");
			}
		}

		return $RefObj;
	}

	static function markReferralAsRewardCancelled($user, $referringUser, $referralSource, $referralID, $storeID)
	{
		$RefObj = DAO_CFactory::create('customer_referral');
		$RefObj->id = $referralID;
		if ($RefObj->find(true))
		{

			if ($RefObj->referral_status != 4)
			{
				$CopyRefObj = clone($RefObj);
				$RefObj->referral_status = 8;
				$RefObj->update($CopyRefObj);
			}
			else
			{
				$tpl->setErrorMsg('This referral has already resulted in a reward and cannot be canceled');
			}
		}

		return $RefObj;
	}


	function bindAndRewardNewReferral($user, $referringUser, $firstOrderID, $referralSource, $storeID, $intro_reward_override = false )
	{
		$RefObj = CCustomerReferral::addOverrideReferral($user, $referringUser->id, $referringUser->firstname . ' ' . $referringUser->lastname, $referringUser->primary_email);

		if ($RefObj)
		{

			self::markOtherReferralsAsSuperseded($user->id, $RefObj->id);

			$referralSourceCopy = clone($referralSource);
			$referralSource->customer_referral_id = $RefObj->id;
			$referralSource->update($referralSourceCopy);

			$RefObj->process_override_referral_award($firstOrderID, $storeID, $intro_reward_override);
		}
	}

	function cancelRewardForReferral()
	{
   		// TODO
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

	function runFranchiseManager() {
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff() {
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager() {
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner() {

		$tpl = CApp::instance()->template();

		$user_id = false;
		if (isset($_REQUEST['user']))
			$user_id = CGPC::do_clean($_REQUEST['user'],TYPE_INT);

		if (!$user_id)
		{
			$tpl->setErrorMsg('User not found');
			CApp::bounce('?page=admin_list_users');
		}
		else
		{
		    $tpl->assign('customer_id', $user_id);
		}

		$Form = new CForm();
		$Form->Repost = true;

		$adminUser = CUser::getCurrentUser();

		if ($adminUser->user_type == CUser::SITE_ADMIN || $adminUser->user_type == CUser::HOME_OFFICE_MANAGER || $adminUser->user_type == CUser::HOME_OFFICE_STAFF)
		{
			$Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
					CForm::name => 'store',
					CForm::onChangeSubmit => true,
					CForm::allowAllOption => false,
					CForm::showInactiveStores => true) );
		}
		else
		{
			$store = CBrowserSession::getCurrentFadminStore();

			$Form->addElement(array(CForm::type => CForm::Hidden,
					CForm::name => 'store',
					CForm::default_value => $store));
		}

		if ( empty($store) )
			$store = $Form->value('store');

		$tpl->assign('store_id', $store);

		processor_admin_guestSearch::initSeachPanel($tpl, $store);

		if (isset($_REQUEST['back']))
		{
			$tpl->assign('back', $_REQUEST['back'] );

		}
		else
		{
			$tpl->assign('back', '?page=admin_user_details&amp;id=' . $user_id);
		}

		$doubleReferralRewardInEffect = false;
		$now = time();
		if ($now > strtotime('2020-04-02 00:00:00') && $now < (strtotime('2020-06-04 03:00:00')))
		{
			$tpl->assign('promo_in_effect', true);
			$doubleReferralRewardInEffect = true;
		}

		$user = DAO_CFactory::create('user');
		$user->id = $user_id;
		$user->find(true);


		if (isset($_POST['submit_notes']))
		{
			$xssFilter = new InputFilter();
			$_POST['notes'] = $xssFilter->process($_POST['notes']);


			CUserData::setReferralSourceNotes($user, $_POST['notes']);
		}

		if (isset($_POST['submit_referral']))
		{
			$error = false;
			$refObj = null;
			$refSourceObj = null;
			if (!isset($_POST['overrideReferral']) || empty($_POST['overrideReferral']))
			{
				$tpl->setErrorMsg('You must provide a valid customer email or id for a customer referral.');
				$error = true;
			}
			$referringUser = DAO_CFactory::create('user');
			$referringUser->primary_email = CGPC::do_clean($_POST['overrideReferral'],TYPE_EMAIL);
			if (!$referringUser->find(true))
			{
				$tpl->setErrorMsg('The email address does not appear to be a valid Dream Dinners account.');
				return true; //true = error
			}
			// 1) handle the referral source
			if (!$error && isset($_POST['referral_option']) && $_POST['referral_option'] == 'update_insert')
			{
				$referralID = CGPC::do_clean($_POST['customer_referral_id'],TYPE_INT);
				$referralID = (!empty($referralID) ? $referralID: 'null');
				$refSourceObj = self::createUpdateReferralSource($user, $referringUser, $referralID);

				if (!$refSourceObj)
					$error = true;
			}


			if (!$error)
			{
				// 2) handle the customer_referral tabl;e
				if (isset($_POST['reward_option']) && $_POST['reward_option'] == 'queue_new')
				{
					$this->queueNewReferral($user, $referringUser, $refSourceObj);
				}
				else if  (isset($_POST['reward_option']) && $_POST['reward_option'] == 'queue_existing')
				{
					if (!empty($_POST['customer_referral_id']))
					{
						$this->queueExistingReferral($user, $referringUser, $refSourceObj, CGPC::do_clean($_POST['customer_referral_id'],TYPE_INT), CGPC::do_clean($_POST['first_order_id'],TYPE_INT));
					}
					else
					{
						$tpl->setErrorMsg('The existing referral is missing.');
						return true; //true = error
					}
				}
				else if  (isset($_POST['reward_option']) && $_POST['reward_option'] == 'reward_new')
				{
					$this->bindAndRewardNewReferral($user, $referringUser, CGPC::do_clean($_POST['first_order_id'],TYPE_INT), $refSourceObj, $store );
				}
				else if  (isset($_POST['reward_option']) && $_POST['reward_option'] == 'reward_new_5')
				{
					$this->bindAndRewardNewReferral($user, $referringUser, CGPC::do_clean($_POST['first_order_id'],TYPE_INT), $refSourceObj, $store, true );
				}
				else if  (isset($_POST['reward_option']) && $_POST['reward_option'] == 'reward_pending')
				{
					self::bindAndRewardPendingReferral($user, $referringUser, $refSourceObj, CGPC::do_clean($_POST['customer_referral_id'],TYPE_INT), $store);
				}
				else if  (isset($_POST['reward_option']) && $_POST['reward_option'] == 'never')
				{
					self::markReferralAsRewardCancelled($user, $referringUser, $refSourceObj, CGPC::do_clean( $_POST['customer_referral_id'],TYPE_INT), $store);
				}
				unset($_POST['overrideReferral']);
			}


		}

		$tpl->assign('customerName', $user->firstname . ' ' . $user->lastname);

		$emailClause = "";
		if (!empty($user->primary_email))
			$emailClause = "or cr.referred_user_email = '{$user->primary_email}' ";

		$referrals = DAO_CFactory::create('customer_referral');

		$referrals->query("select cr.*, CONCAT(u.firstname, ' ', u.lastname) as referringUserName, u.primary_email as referring_user_email, if(u.dream_rewards_version = 3 and (u.dream_reward_status = 1 or u.dream_reward_status = 3), 1, 0) as inPP from customer_referral cr " .
						 " left join user u on cr.referring_user_id = u.id " .
						 " where (cr.referred_user_id = $user_id $emailClause) and cr.is_deleted = 0 order by sequence_timestamp desc, id desc");
		$referralStatus = array();


		$reward_complete = false;
		$hasPendingReferral = false;
		$refArray = array();
		$objArray = array();
		while($referrals->fetch())
		{
			$objArray[] = $referrals;
			$refArray[$referrals->id] = array(
			'name' => (!empty($referrals->referringUserName) ? $referrals->referringUserName : "unknown"),
			'email' => (!empty($referrals->referring_user_email) ? $referrals->referring_user_email : "unknown"),
			'user_id' => (!empty($referrals->referring_user_id) ? $referrals->referring_user_id : "0"),
			'type' => CCustomerReferral::$EvenShorterOriginationDescription[$referrals->origination_type_code],
			'date' => ($referrals->sequence_timestamp != '1970-01-01 00:00:01' ? $referrals->sequence_timestamp : $referrals->timestamp_created),
			'status' => CCustomerReferral::$ShortStatusDescription[$referrals->referral_status],
			'ordered' => (!empty($referrals->first_order_id) ? $referrals->first_order_id : "-"),
			'is_pending' => ($referrals->referral_status == 3),
			'amount' => $referrals->amount_credited,
			'active' => false,
			'inPP' => ($referrals->inPP == 1 ? true: false)
			);

			if ($referrals->referral_status == 4 && !empty($referrals->store_credit_id))
				$reward_complete = true;
			if ($referrals->referral_status == 3)
				$hasPendingReferral = $referrals->id;

			if (!empty($referrals->plate_points_reward_id) && is_numeric($referrals->plate_points_reward_id))
			{
				$rewardEvent = DAO_CFactory::create('points_user_history');
				$rewardEvent->id = $referrals->plate_points_reward_id;

				$rewardEvent->find(true);

				$refArray[$referrals->id]['amount'] = $rewardEvent->points_allocated . " Pts.";

			}


		}
		/*
		 * 	Referral_status
		*
		* array of
		*  bool			userIsNew
		*  bool 		hasReferralSource
		*  bool			hasTriggeredReward
		*  id			eligibleOrder
		*  array		eligibleOrderInfo
		*  bool			hasPendingReferral
		*
		*/

		$referralStatus['hasPendingReferral'] = $hasPendingReferral;
		$referralStatus['userIsNew'] = $user->isNewReferralCustomer();
		$referralStatus['hasTriggeredReward'] = $user->hasTriggeredReferralReward();

		if ($referralStatus['userIsNew'] && !$referralStatus['hasTriggeredReward'])
		{
			list ($referralStatus['eligibleOrder'], $referralStatus['eligibleOrderInfo']) = $user->getReferralRewardQualifyingOrderId();

			if ($doubleReferralRewardInEffect)
			{
				$rewardTime = strtotime( $referralStatus['eligibleOrderInfo']['reward_date']);
				if ($rewardTime < (strtotime('2020-06-04 03:00:00')))
				{
					$tpl->assign('queued_reward_will_double', true);
				}
			}

		}
		else
		{
			$referralStatus['eligibleOrder'] = false;
			$referralStatus['eligibleOrderInfo'] = false;
		}

		$tpl->assign('reward_complete', $reward_complete);

		$activeReferralID = self::determineActiveReferral($objArray);

		if ($activeReferralID)
		{
			$refArray[$activeReferralID]['active'] = true;
		}
		$referralStatus['hasReferralSource'] = false;

		$referralSource = DAO_CFactory::create('user_referral_source');
		$referralSource->source = 'CUSTOMER_REFERRAL';
		$referralSource->user_id = $user_id;

		if ($referralSource->find(true))
		{
			$referralStatus['hasReferralSource'] = true;
			if (!empty($referralSource->customer_referral_id))
			{

				$tpl->assign('activeReferral', $refArray[$referralSource->customer_referral_id]);

			}
			else if (!empty($referralSource->inviting_user_id))
			{
				$inviting_user = DAO_CFactory::create('user');
				$inviting_user->id = $referralSource->inviting_user_id;
				$inviting_user->selectAdd();
				$inviting_user->selectAdd('firstname, lastname, primary_email');
				$inviting_user->find(true);

				$name = $inviting_user->firstname . ' ' . $inviting_user->lastname;
				$email = $inviting_user->primary_email;
				if ($activeReferralID)
					$date = $refArray[$activeReferralID]['date'];
				else
					$date = $referralSource->timestamp_created;
				$tpl->assign('activeReferral', array('name' => $name, 'email' => $email, 'date' => $date));
			}
			else
			{
				$inviting_user = DAO_CFactory::create('user');
				$inviting_user->primary_email = $referralSource->meta;
				$inviting_user->selectAdd();
				$inviting_user->selectAdd('firstname, lastname, primary_email');
				$inviting_user->find(true);

				$name = $inviting_user->firstname . ' ' . $inviting_user->lastname;
				$email = $inviting_user->primary_email;
				if ($activeReferralID)
					$date = $refArray[$activeReferralID]['date'];
				else
					$date = $referralSource->timestamp_created;
				$tpl->assign('activeReferral', array('name' => $name, 'email' => $email, 'date' => $date));

			}

		}
		else if ($activeReferralID)
		{
			$tpl->assign('activeReferral',$refArray[$activeReferralID] );
		}
		else
		{
			$tpl->assign('activeReferral', false);
		}

		$tpl->assign('referral_status', $referralStatus );

		$tpl->assign('referralsArray', $refArray);


		$refNotes = CUserData::getReferralSourceNotes($user);

		if ($refNotes)
			$Form->DefaultValues['notes'] = $refNotes;
		$Opname = "update";


		$SubmitBtnName = "Add Referral";
		if ($referralStatus['hasReferralSource'])
			$SubmitBtnName = "Update Referral";

		if ($referralStatus['hasTriggeredReward'] || !$referralStatus['userIsNew'] )
		{
			$Opname = "ineligible";

			if (!$referralStatus['hasReferralSource'])
			{
				$SubmitBtnName = "Add Referral Source";
			}
		}


		if ($reward_complete)
		{
			$Form->AddElement(array(CForm::type=> CForm::Text,
									CForm::name => "overrideReferral",
									CForm::maxlength => 255,
									CForm::disabled =>true,
									CForm::size => 50));

			$Form->AddElement(array(CForm::type=> CForm::Submit,
									CForm::name => "submit_referral",
									CForm::disabled =>true,
									CForm::value => $SubmitBtnName));
		}
		else
		{
			$Form->AddElement(array(CForm::type=> CForm::Text,
									CForm::name => "overrideReferral",
									CForm::maxlength => 255,
									CForm::size => 50));

			$Form->AddElement(array(CForm::type=> CForm::Submit,
									CForm::name => "submit_referral",
									CForm::disabled =>true,
									CForm::value => $SubmitBtnName));
		}




		$Form->AddElement(array(CForm::type=> CForm::TextArea,
								 CForm::rows => '6',
			 					CForm::cols => '75',
								CForm::name => "notes"));

		$Form->AddElement(array(CForm::type=> CForm::Submit,
								CForm::name => "submit_notes",
								CForm::value => "Save Notes"));
		$Form->AddElement(array(CForm::type=> CForm::Hidden,
				CForm::name => "operation",
				CForm::value => "$Opname"));

		$Form->AddElement(array(CForm::type=> CForm::Hidden,
				CForm::name => "overrideReferral",
				CForm::value => ""));

		$Form->AddElement(array(CForm::type=> CForm::Hidden,
				CForm::name => "customer_referral_id",
				CForm::value => ""));

		$Form->AddElement(array(CForm::type=> CForm::Hidden,
				CForm::name => "first_order_id",
				CForm::value => $referralStatus['eligibleOrder']));

		$Form->AddElement(array(CForm::type=> CForm::Hidden,
				CForm::name => "referral_option",
				CForm::value => 'update_insert'));

		$tpl->assign('form', $Form->Render());


	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

}

?>