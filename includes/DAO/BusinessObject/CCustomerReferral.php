<?php

require_once 'DAO/Customer_referral.php';
require_once 'DAO/BusinessObject/CBooking.php';

class CCustomerReferral extends DAO_Customer_referral
{

	static $CRStatusMap = array(
		0 => 'no_activity',
		1 => 'visited_site',
		2 => 'has_registered',
		3 => 'has_ordered',
		4 => 'award_is_complete',
		5 => 'determined_as_ineligible',
		6 => 'Overridden',
		7 => 'Overridden',
		8 => 'reward_cancelled',
		9 => 'dream_taste_referral_complete'
	);

	static $ShortStatusDescription = array(
		0 => 'No Activity',
		1 => 'Visited Site',
		2 => 'Registered',
		3 => 'Ordered',
		4 => 'Award Complete',
		5 => 'Ineligible',
		6 => 'Overridden',
		7 => 'Overridden',
		8 => 'reward_cancelled',
		9 => 'dream_taste_referral_complete'
	);

	static $ShortOriginationDescription = array(
		0 => 'Unknown Origin',
		1 => 'Invite a Friend',
		2 => 'Evite Private Party',
		3 => 'Customer Referral',
		4 => 'Taste of Dream Dinners',
		5 => 'Override Referral',
		6 => "Shared Link Referral",
		7 => "User Only Referral"
	);

	static $EvenShorterOriginationDescription = array(
		0 => 'Unknown Origin',
		1 => 'IAF',
		2 => 'Evite Private Party',
		3 => 'Customer Referral',
		4 => 'Taste',
		5 => 'Override Referral',
		6 => "Shared Link Referral",
		7 => "User Only Referral"
	);

	const UNKNOWN_ORIGIN = 0;
	const INVITE_A_FRIEND = 1;
	const EVITE_PRIVATE_PARTY = 2;
	const DIRECT_REFERRAL = 3;
	const TODD_REFERRAL = 4;
	const OVERRIDE_REFERRAL = 5;
	const SHARED_LINK_REFERRAL = 6;
	const USER_ONLY_REFERRAL = 7;

	static function generateUniqueID()
	{
		$length = 8;
		$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

		// set pool of possible char
		if ($pool == "")
		{
			$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$pool .= "abcdefghijklmnopqrstuvwxyz";
			$pool .= "0123456789";
		}// end if
		mt_srand((double)microtime() * 1000000);
		$unique_id = "";
		for ($index = 0; $index < $length; $index++)
		{
			$unique_id .= substr($pool, (mt_rand() % (strlen($pool))), 1);
		}// end for

		return (dechex(time()) . $unique_id);
	}

	static function getTODDReferralRow($email, $session_id)
	{
		$referral = DAO_CFactory::create('customer_referral');
		$referral->query("select * from customer_referral where referred_user_email = '$email' and " . " referrer_session_id = $session_id and is_deleted = 0 and origination_type_code = 4");

		if ($referral->N == 0)
		{
			return false;
		}
		else
		{
			$referral->fetch();
		}

		return $referral;
	}

	static function getIAFReferralRow($email, $inviting_user_id)
	{
		$referral = DAO_CFactory::create('customer_referral');
		$referral->query("select * from customer_referral where referred_user_email = '$email' and " . " referring_user_id = $inviting_user_id and is_deleted = 0 and origination_type_code = 1");

		if ($referral->N == 0)
		{
			return false;
		}
		else
		{
			$referral->fetch();
		}

		return $referral;
	}

	static function newTODDReferral($new_user_obj, $session_id, $toddOrderPlaced = false)
	{

		$session = DAO_CFactory::create('session');
		$session->query("select s.*, tsp.session_host, tsp.id as tsp_id, tsp.informal_host_name from session s join session_properties tsp on s.id = tsp.session_id " . " where s.id = $session_id and s.session_class = 'TODD' and s.is_deleted = 0");

		if ($session->fetch())
		{
			$referral = DAO_CFactory::create('customer_referral');
			$referral->referred_user_id = $new_user_obj->id;
			$referral->referring_user_id = $session->session_host;
			$referral->referral_status = 2; // registered
			$referral->referrer_session_id = $session_id;
			$referral->referred_user_email = $new_user_obj->primary_email;
			$referral->referred_user_name = $new_user_obj->firstname . ' ' . $new_user_obj->lastname;
			$referral->session_properties_id = $session->tsp_id;
			$referral->origination_type_code = 4; //TODD
			$referral->inviting_user_name = $session->informal_host_name;
			$referral->origination_uid = self::generateUniqueID();

			if ($toddOrderPlaced)
			{
				$referral->sequence_timestamp = date("Y-m-d H:i:s");
			}

			$referral->insert();

			return $referral->id;
		}

		return false;
	}

	static function newSharedLinkedReferral($inviting_user_id, $session_id = false)
	{

		$referralType = (empty($session_id) ? self::USER_ONLY_REFERRAL : self::SHARED_LINK_REFERRAL);

		$sessionPropertiesID = 'null';
		if ($session_id)
		{
			$session = DAO_CFactory::create('session');
			$session->query("select s.*, tsp.session_host, tsp.id as tsp_id, tsp.informal_host_name from session s 
                        join session_properties tsp on s.id = tsp.session_id  where s.id = $session_id and s.session_class = 'TODD' and s.is_deleted = 0");

			$session->fetch();
			if (!empty($session->tsp_id))
			{
				$sessionPropertiesID = $session->tsp_id;
			}
		}

		// Look for existing referral
		$referral = DAO_CFactory::create('customer_referral');
		$foundExisting = false;

		if ($referralType == self::USER_ONLY_REFERRAL)
		{
			$referral->query("select * from customer_referral where (referred_user_id = 0 or isnull(referred_user_id)) and referring_user_id = $inviting_user_id and referral_status = 1 and (isnull(referrer_session_id) or referrer_session_id = 0) and is_deleted = 0");
			if ($referral->N > 0)
			{
				$referral->fetch();
				$foundExisting = true;
			}
		}
		else
		{
			$referral->query("select * from customer_referral where (referred_user_id = 0 or isnull(referred_user_id)) and referring_user_id = $inviting_user_id and referral_status = 1 and referrer_session_id = $session_id and is_deleted = 0");
			if ($referral->N > 0)
			{
				$referral->fetch();
				$foundExisting = true;
			}
		}

		if ($foundExisting)
		{
			return $referral->origination_uid;
		}

		if ($referralType == self::USER_ONLY_REFERRAL)
		{
			$referral->referrer_session_id = $session_id;
		}

		$referral->referred_user_id = 0;
		$referral->referring_user_id = $inviting_user_id;
		$referral->referral_status = 1;
		$referral->referred_user_email = "";
		$referral->referred_user_name = "";
		$referral->session_properties_id = $sessionPropertiesID;
		$referral->origination_type_code = $referralType;
		$referral->inviting_user_name = "";
		$referral->origination_uid = self::generateUniqueID();

		$referral->insert();

		return $referral->origination_uid;
	}

	static function qualifyTODDReferral($userDAO, $session_id)
	{
		$referral = DAO_CFactory::create('customer_referral');
		$referral->query("select * from customer_referral where referred_user_id = {$userDAO->id} and referrer_session_id = $session_id " . " and is_deleted = 0 and referral_status < 3 and origination_type_code = 4");

		if ($referral->N > 0)
		{
			$referral->fetch();
			$referral->sequence_timestamp = date("Y-m-d H:i:s");
			$referral->referral_status = 2; // registered

			$referral->update();
		}
		else
		{

			$session = DAO_CFactory::create('session');
			$session->query("select s.*, tsp.id as tsp_id, tsp.session_host, tsp.informal_host_name from session s join session_properties tsp on s.id = tsp.session_id " . " where s.id = $session_id and s.session_class = 'TODD' and s.is_deleted = 0");

			if ($session->N > 0)
			{
				$session->fetch();

				$newReferral = DAO_CFactory::create('customer_referral');

				$newReferral->referred_user_id = $userDAO->id;
				$newReferral->referring_user_id = $session->session_host;
				$newReferral->referral_status = 2; // registered
				$newReferral->referrer_session_id = $session_id;
				$newReferral->referred_user_email = $userDAO->primary_email;
				$newReferral->session_properties_id = $session->tsp_id;
				$newReferral->origination_type_code = 4; //TODD
				$newReferral->inviting_user_name = $session->informal_host_name;
				$newReferral->sequence_timestamp = date("Y-m-d H:i:s");

				$newReferral->origination_uid = self::generateUniqueID();

				$newReferral->insert();
			}
		}
	}

	static function newIAFReferral($new_user_obj, $inviting_user_id, $origination_code)
	{
		// The IAF email was potentially responded by someone not originally invited (email was forwarded)
		// so use the origination code to a valid invite .... if the referred_user id is different then create a new row
		$referral = DAO_CFactory::create('customer_referral');
		$referral->origination_uid = $origination_code;

		if ($referral->find(true))
		{

			if ($new_user_obj->id != $referral->referred_user_id)
			{
				$referral->id = null;
				$referral->referred_user_id = $new_user_obj->id;
				$referral->referral_status = 2; // registered
				$referral->referred_user_email = $new_user_obj->primary_email;
				$referral->insert();

				return $referral->id;
			}
		}

		return false;
	}

	static function hasRewardedReferrer($userDAO)
	{

		// there is a customer referral with a state greater than has_landed
		$referral = DAO_CFactory::create('customer_referral');
		$referral->query("select id from customer_referral where referred_user_id = {$userDAO->id} and referral_status = 4 and is_deleted = 0 ");

		if ($referral->N > 0)
		{
			return true;
		}

		return false;
	}

	static function isNewToReferralProgram($userDAO)
	{

		// when logging in with the IAF2 cookie the user will be marked as referred by unless ...
		// there is an existing Customer Referral Source and ...
		$URS = DAO_CFactory::create('user_referral_source');
		$URS->user_id = $userDAO->id;
		$URS->source = CUserReferralSource::CUSTOMER_REFERRAL;
		if ($URS->find())
		{
			return false;
		}

		// there is a customer referral with a state greater than has_landed
		$referral = DAO_CFactory::create('customer_referral');
		$referral->query("select id from customer_referral where referred_user_id = {$userDAO->id} and referral_status >= 2 and is_deleted = 0 ");

		if ($referral->N > 0)
		{
			return false;
		}

		return true;
	}

	static function updateAsRegistered($user_id)
	{

		if (isset($_COOKIE['IAF2_origination_uid']))
		{

			$referral = DAO_CFactory::create('customer_referral');
			$referral->origination_uid = $_COOKIE['IAF2_origination_uid'];

			if (!$referral->find(true))
			{
				return false;
			}

			$referral->referred_user_id = $user_id;
			$referral->referral_status = 2;
			$referral->update();

			if (isset($_COOKIE['IAF2_origination_uid']))
			{
				CBrowserSession::setValue('IAF2_origination_uid', false);
			}
			if (isset($_COOKIE['IAF2_inviting_user_id']))
			{
				CBrowserSession::setValue('IAF2_inviting_user_id', false);
			}
			if (isset($_COOKIE['IAF2_inviting_user']))
			{
				CBrowserSession::setValue('IAF2_inviting_user', false);
			}

			return $referral->id;
		}

		return false;
	}

	static function newDirectReferralFromRegistrationForm($new_user_obj, $referring_user_id, $referring_user_name)
	{
		$referral = DAO_CFactory::create('customer_referral');
		$referral->referred_user_id = $new_user_obj->id;
		$referral->referring_user_id = $referring_user_id;
		$referral->referral_status = 2; // registered
		$referral->referred_user_email = $new_user_obj->primary_email;
		$referral->origination_type_code = 3;
		$referral->inviting_user_name = $referring_user_name;
		$referral->origination_uid = self::generateUniqueID();
		$referral->sequence_timestamp = date("Y-m-d H:i:s");

		$referral->insert();

		return array(
			$referral->id,
			$referral->origination_uid
		);
	}

	static function isNewCustomer($userDAO)
	{

		$Order = DAO_CFactory::create('Orders');

		$Order->query("select o.id from orders o
						join booking b on b.order_id = o.id and b.status = 'ACTIVE' and b.is_deleted = 0
						where o.user_id = {$userDAO->id} and o.type_of_order not in ('TODD', 'DREAM_TASTE') and o.is_deleted = 0");
		// don't count the current order
		if ($Order->N > 1)
		{
			return false;
		}

		return true;
	}

	static function hasTODDOrder($userDAO)
	{
		$Order = DAO_CFactory::create('Orders');
		$Booking = DAO_CFactory::create('booking');
		$Order->user_id = $userDAO->id;
		$Order->joinAdd($Booking);
		$Order->whereAdd("booking.status <> 'CANCELLED' AND booking.status <>  'RESCHEDULED' AND booking.status <> 'SAVED' and (orders.type_of_order = 'DREAM_TASTE')");
		if ($Order->find() > 0)
		{
			return true;
		}

		return false;
	}

	static function isStoreAndHostessInPlatePoints($sessionObj)
	{
		$TSP = DAO_CFactory::create('session_properties');
		$TSP->session_id = $sessionObj->id;
		if ($TSP->find(true))
		{

			$status = CPointsUserHistory::getPlatePointsStatus($sessionObj->store_id, $TSP->session_host);

			if ($status['storeSupportsPlatePoints'] && $status['userIsEnrolled'])
			{
				return true;
			}

			return false;
		}

		return false;
	}

	static function updateAsOrderedIfEligible($userDAO, $Order)
	{
		$Session = $Order->findSession();

		if ($Session->session_type == CSession::DREAM_TASTE)
		{
			self::qualifyTODDReferral($userDAO, $Session->id);
		}
		//Per Brandy - allow referral for any qualifying order
		//		if ($Session->session_type == CSession::TODD || $Session->session_type == CSession::DREAM_TASTE || $Session->session_type == CSession::FUNDRAISER)
		//		{
		//			return;
		//		}

		if (!COrderMinimum::doesOrderQualifiesAsMinimum($Order))
		{
			return;
		}

		if (self::hasRewardedReferrer($userDAO))
		{
			return;
		}

		if (!self::isNewCustomer($userDAO))
		{
			return;
		}

		$referral = DAO_CFactory::create('customer_referral');
		$referral->query("select * from customer_referral where referral_status < 3 and referred_user_id = {$userDAO->id} and is_deleted = 0 order by sequence_timestamp desc");
		$numFound = $referral->N;

		if ($numFound == 0)
		{
			return;
		}

		$ref_Array = array();

		while ($referral->fetch())
		{
			$ref_Array[] = $referral;
		}

		if (count($ref_Array) == 0)
		{
			return;
		}

		$selectedIndex = false;
		// is newest one an override
		if ($ref_Array[0]->origination_type_code == 5)
		{
			$selectedIndex = 0;
		}
		else
		{
			// if an IAF referral for this session exists then use it
			foreach ($ref_Array as $k => $val)
			{
				if ($val->origination_type_code == 1)
				{
					$selectedIndex = $k;
					break;
				}
			}

			// didn't find an IAF so check for TODD
			if ($selectedIndex === false)
			{
				if (self::hasTODDOrder($userDAO))
				{
					foreach ($ref_Array as $k => $val)
					{
						if ($val->origination_type_code == 4)
						{
							$selectedIndex = $k;
							break;
						}
					}
				}
			}

			// didn't find a TODD referral so check for direct input, this can be a Direct input (1),
			// or an IAF referral for a different session if sequence date was set
			if ($selectedIndex === false)
			{
				foreach ($ref_Array as $k => $val)
				{
					$time = strtotime($val->sequence_timestamp);

					if ($val->origination_type_code == self::DIRECT_REFERRAL || (($val->origination_type_code == self::INVITE_A_FRIEND) && $time && ($time > 0)) || $val->origination_type_code == self::OVERRIDE_REFERRAL)
					{
						$selectedIndex = $k;
						break;
					}
				}
			}

			// lastly look for share referral
			if ($selectedIndex === false)
			{
				foreach ($ref_Array as $k => $val)
				{

					if ($val->origination_type_code == 7)
					{
						$selectedIndex = $k;
						break;
					}
				}
			}
		}

		if ($selectedIndex !== false)
		{
			$ref_Array[$selectedIndex]->referral_status = 3;
			$ref_Array[$selectedIndex]->first_order_id = $Order->id;
			$ref_Array[$selectedIndex]->update();

			// update referral source
			$DAO_urs = DAO_CFactory::create('user_referral_source');
			$DAO_urs->source = 'CUSTOMER_REFERRAL';
			$DAO_urs->user_id = $userDAO->id;

			$referring_party = DAO_CFactory::create('user');
			$referring_party->id = $ref_Array[$selectedIndex]->referring_user_id;
			$referring_party->find(true);

			if ($DAO_urs->find(true))
			{
				$DAO_urs->meta = $referring_party->primary_email;
				$DAO_urs->customer_referral_id = $ref_Array[$selectedIndex]->id;
				$DAO_urs->update();
			}
			else
			{
				$DAO_urs->meta = $referring_party->primary_email;
				$DAO_urs->customer_referral_id = $ref_Array[$selectedIndex]->id;
				$DAO_urs->insert();
			}
		}
	}

	function send_award_notice_email($data, $RewardRecipientUserDAO, $store_email)
	{

		try
		{
			$Mail = new CMail();

			$subject = 'Referral Award Notice';
			if ((isset($this->session_type) && $this->session_type == CSession::TODD) || (isset($data['is_TODD']) && $data['is_TODD']))
			{
				$contentsText = CMail::mailMerge('credit_notify_todd.txt.php', $data, false);
				$contentsHtml = CMail::mailMerge('credit_notify_todd.html.php', $data, false);
				$subject .= ' - Taste of Dream Dinners';
			}
			else
			{
				$contentsText = CMail::mailMerge('invite_friends_credit_notify.txt.php', $data, false);
				$contentsHtml = CMail::mailMerge('invite_friends_credit_notify.html.php', $data, false);
			}

			$Mail->send(null, $store_email, $RewardRecipientUserDAO->firstname . ' ' . $RewardRecipientUserDAO->lastname, $RewardRecipientUserDAO->primary_email, $subject, $contentsHtml, $contentsText, '', '', $RewardRecipientUserDAO->id, 'invite_friends_credit_notify');
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
		}
	}

	function process_plate_points_referral_award($suppressReward = false)
	{

		// double check validity
		//There cannot be an existing award

		$oldObj = clone($this);

		$ExReferralTest = DAO_CFactory::create('customer_referral');
		$ExReferralTest->referred_user_id = $this->referred_user_id;
		$ExReferralTest->referral_status = 4;
		if ($ExReferralTest->find())
		{
			$this->referral_status = 5;
			$this->update();

			return;
		}

		//check for preferred customer
		$UP = DAO_CFactory::create('user_preferred');
		$UP->user_id = $this->referring_user_id;
		if ($UP->findActive($this->home_store_id))
		{
			// uh oh, this person has a preferred user discount so is ineligible for referral reward.
			// update referral record
			$this->amount_credited = 0;
			$this->referral_status = 5;
			$this->update($oldObj);

			CUserHistory::recordUserEvent($this->referring_user_id, $this->home_store_id, 'null', 300, 'null', 'null', 'Could not reward referral PLATEPOINTS Dinner Dollars due to Preferred User Discount. Referral ID: ' . $this->id);
		}

		try
		{


			$oldObj = clone($this);
			// update referral record first in case something goes awry after the reward the guest will not be rewarded again.
			$this->referral_status = 4;
			$this->update($oldObj);
			$reward_id = null;

			if (!$suppressReward)
			{
				$metaArray = array(
					'booking_type' => $this->booking_type,
					'referred_name' => $this->referred_user_name
				);

				$reward_id = CPointsUserHistory::handleEvent($this->referring_user_id, CPointsUserHistory::REFERRAL_COMPLETED, $metaArray);
			}
			else
			{
				$reward_id = 0;
			}

			$RewardRecipientUserDAO = DAO_CFactory::create('user');
			$RewardRecipientUserDAO->id = $this->referring_user_id;
			$RewardRecipientUserDAO->find(true);

			if (!is_null($reward_id))
			{
				$oldObj = clone($this);

				// update referral record
				$this->amount_credited = 0;
				$this->plate_points_reward_id = $reward_id;
				$this->update($oldObj);
			}

			// update Referral source table
			$DAO_urs = DAO_CFactory::create('user_referral_source');
			$DAO_urs->source = 'CUSTOMER_REFERRAL';
			$DAO_urs->user_id = $this->referred_user_id;
			if ($DAO_urs->find(true))
			{
				$DAO_urs->meta = $RewardRecipientUserDAO->primary_email;
				$DAO_urs->customer_referral_id = $this->id;
				$DAO_urs->update();
			}
			else
			{
				$DAO_urs->meta = $RewardRecipientUserDAO->primary_email;
				$DAO_urs->customer_referral_id = $this->id;
				$DAO_urs->insert();
			}

			CLog::Record("REFERRAL DEBUG: " . print_r($this->toArray(), true));
		}
		catch (exception $e)
		{
			CLog::RecordException($e);
			// don't let one bad apple spoil the whole bunch
		}
	}

	function process_customer_referral_credit()
	{
		$DAO_customer_referral = DAO_CFactory::create('customer_referral', true);
		$DAO_customer_referral->referred_user_id = $this->referred_user_id;
		$DAO_customer_referral->referral_status = 4;
		if ($DAO_customer_referral->find())
		{
			$this->referral_status = 5;
			$this->update();

			return;
		}

		//check for preferred customer
		$DAO_user_preferred = DAO_CFactory::create('user_preferred', true);
		$DAO_user_preferred->user_id = $this->referring_user_id;
		if ($DAO_user_preferred->findActive($this->home_store_id))
		{
			// uh oh, this person has a preferred user discount so is ineligible for referral reward.
			// update referral record
			$this->amount_credited = 0;
			$this->referral_status = 5;
			$this->update();

			CUserHistory::recordUserEvent($this->referring_user_id, $this->home_store_id, 'null', 300, 'null', 'null', 'Cound not reward referral store credit due to Preferred User Discount. Referral ID: ' . $this->id);

			return;
		}

		try
		{
			// determine payment amount
			$reward_amount = 10;

			// add store credit
			$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit', true);
			$DAO_customer_referral_credit->user_id = $this->referring_user_id;
			$DAO_customer_referral_credit->credit_state = CCustomerReferralCredit::AVAILABLE;
			$DAO_customer_referral_credit->dollar_value = $reward_amount;
			$DAO_customer_referral_credit->expiration_date = CTemplate::formatDateTime('Y-m-d 03:00:00', false, false, '+1 year');
			$DAO_customer_referral_credit->insert();

			$DAO_user = DAO_CFactory::create('user', true);
			$DAO_user->id = $this->referring_user_id;
			$DAO_user->find(true);

			// update referral record
			$this->amount_credited = $reward_amount;
			$this->referral_status = 4;
			$this->store_credit_id = $DAO_customer_referral_credit->id;
			$this->update();

			// update Referral source table
			$DAO_user_referral_source = DAO_CFactory::create('user_referral_source', true);
			$DAO_user_referral_source->source = 'CUSTOMER_REFERRAL';
			$DAO_user_referral_source->user_id = $this->referred_user_id;
			if ($DAO_user_referral_source->find(true))
			{
				$DAO_user_referral_source->meta = $DAO_user->primary_email;
				$DAO_user_referral_source->customer_referral_id = $this->id;
				$DAO_user_referral_source->update();
			}
			else
			{
				$DAO_user_referral_source->meta = $DAO_user->primary_email;
				$DAO_user_referral_source->customer_referral_id = $this->id;
				$DAO_user_referral_source->insert();
			}

			// send email
			$data = array(
				'referrer_name' => $this->inviting_user_name,
				'session_date' => CTemplate::dateTimeFormat($this->session_start),
				'referred_name' => $this->referred_user_name,
				'award_amount' => CTemplate::moneyFormat($reward_amount)
			);

			$this->send_award_notice_email($data, $DAO_user, $this->store_email);

			CLog::Record("REFERRAL DEBUG: " . print_r($this->toArray(), true));
		}
		catch (exception $e)
		{
			CLog::RecordException($e);
			// don't let one bad apple spoil the whole bunch
		}
	}

	function process_override_referral_award($order_id, $store_id, $intro_reward_override = false)
	{
		// determine payment amount
		$isIntro = false;
		if ($order_id)
		{
			$OrderObj = DAO_CFactory::create('orders');
			$OrderObj->query("select 
				o.bundle_id, 
				o.is_TODD, 
				s.session_start, 
				b.booking_type 
				from orders o 
				join booking b on b.order_id = $order_id and b.status = 'ACTIVE' 
				join session s on s.id = b.session_id 
				where o.id = $order_id");
			$OrderObj->fetch();

			if ($OrderObj->booking_type == 'INTRO' || (!empty($OrderObj->bundle_id) && $OrderObj->bundle_id > 0))
			{
				$isIntro = true;
			}
		}

		$reward_amount = 10;
		$reward_point_amount = 400;

		$UserObj = DAO_CFactory::create('user');
		$UserObj->id = $this->referring_user_id;
		$UserObj->find(true);

		$bookingType = 'STANDARD';

		if (CPointsUserHistory::userIsActiveInProgram($UserObj))
		{
			// Note: force the booking type to intro if we detected a bundle above, this will ensure a lame Taste only gets only 250 points if the owner short circuits the system
			if ($isIntro || $intro_reward_override)
			{
				$bookingType = 'INTRO';
			}

			$metaArray = array(
				'booking_type' => $bookingType,
				'referred_name' => $this->referred_user_name,
				'points_earned' => $reward_point_amount
			);

			$reward_id = CPointsUserHistory::handleEvent($this->referring_user_id, CPointsUserHistory::REFERRAL_COMPLETED, $metaArray);

			if ($reward_id)
			{
				// update referral record
				$this->amount_credited = $reward_amount;
				$this->referral_status = 4;
				$this->plate_points_reward_id = $reward_id;
				$this->update();
			}

			CLog::Record("REFERRAL DEBUG: " . print_r($this->toArray(), true));
		}
		else
		{
			// add store credit
			$NewCredit = DAO_CFactory::create('store_credit');
			$NewCredit->credit_type = 2; // 2 = customer_referral credit
			$NewCredit->store_id = $store_id;
			$NewCredit->is_redeemed = 0;
			$NewCredit->user_id = $this->referring_user_id;
			$NewCredit->amount = $reward_amount;
			$NewCredit->insert();

			// update referral record
			$this->amount_credited = $reward_amount;
			$this->referral_status = 4;
			$this->store_credit_id = $NewCredit->id;
			$this->first_order_id = $order_id;

			$this->update();

			$RewardRecipientUserDAO = DAO_CFactory::create('user');
			$RewardRecipientUserDAO->id = $this->referring_user_id;
			$RewardRecipientUserDAO->find(true);

			$is_TODD = false;
			$sessionTime = "previous session";
			if ($order_id)
			{
				$sessionTime = CTemplate::dateTimeFormat($OrderObj->session_start);
				$is_TODD = $OrderObj->is_TODD;
			}

			$data = array(
				'referrer_name' => $this->inviting_user_name,
				'session_date' => $sessionTime,
				'referred_name' => $this->referred_user_name,
				'award_amount' => CTemplate::moneyFormat($reward_amount),
				'is_TODD' => $is_TODD
			);

			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->id = $store_id;
			$StoreObj->find(true);

			$this->send_award_notice_email($data, $RewardRecipientUserDAO, $StoreObj->email_address);

			CLog::Record("REFERRAL DEBUG: " . print_r($this->toArray(), true));
		}
	}

	static function addOverrideReferral($user, $referring_user_id, $referringUserName, $referringUserEmail, $isIneligible = false)
	{

		// Figure out if reward exists
		// do nothing if it does
		if (self::hasRewardedReferrer($user))
		{
			return false;
		}

		// figure out if a referral is locked and loaded for the referral reward
		// if so demote the existing referral
		$loadedReferrals = DAO_CFactory::create('customer_referral');
		$loadedReferrals->query("select * from customer_referral where referral_status = 3 and referred_user_id = {$user->id} and is_deleted = 0 and first_order_id is not null order by id asc");

		$order_id = false;
		while ($loadedReferrals->fetch())
		{
			if (!$order_id && !empty($loadedReferrals->first_order_id))
			{
				$order_id = $loadedReferrals->first_order_id;
			}
			$loadedReferrals->referral_status = 6;
			$loadedReferrals->update();
		}

		// finally - create the referral
		$referral = DAO_CFactory::create('customer_referral');
		$referral->referred_user_id = $user->id;
		$referral->referring_user_id = $referring_user_id;

		$referral->referred_user_name = $user->firstname . " " . $user->lastname;

		if ($isIneligible)
		{
			$referral->referral_status = 5; // ineligible
		}
		else if ($order_id)
		{
			$referral->referral_status = 3; // ordered
			$referral->first_order_id = $order_id;
		}
		else
		{
			$referral->referral_status = 2; // regisitered
		}

		$referral->referred_user_email = $user->primary_email;

		$referral->origination_type_code = 5;
		$referral->inviting_user_name = $referringUserName;
		$referral->origination_uid = self::generateUniqueID();
		$referral->sequence_timestamp = date("Y-m-d H:i:s");

		$referral->insert();

		return $referral;
	}

	static function is_referral_active($user_id = false)
	{

		$cookie_is_set = isset($_COOKIE['IAF2_origination_uid']);

		if ($cookie_is_set)
		{
			return true;
		}

		if ($user_id)
		{
			$referral = DAO_CFactory::create('customer_referral');

			$referral->query("select id from customer_referral where referred_user_id = $user_id and referral_status = 2 and is_deleted = 0 ");

			if ($referral->N > 0)
			{
				return true;
			}
		}

		return false;
	}

	static function get_inviting_username_if_referral_active($user_id = false)
	{

		$cookie_is_set = isset($_COOKIE['IAF2_origination_uid']);

		if ($cookie_is_set)
		{
			return $_COOKIE['IAF2_inviting_user'];
		}

		if ($user_id)
		{
			$referral = DAO_CFactory::create('customer_referral');

			$referral->query("select inviting_user_name from customer_referral where referred_user_id = $user_id and referral_status = 2 and is_deleted = 0 and DATEDIFF(CURRENT_DATE(), DATE(timestamp_created)) < 30");

			if ($referral->N > 0)
			{
				$referral->fetch();

				return $referral->inviting_user_name;
			}
		}

		return false;
	}

	static function deleteReferral($userDAO)
	{
		$referrals = DAO_CFactory::create('customer_referral');
		$referrals->query("select id from customer_referral where referred_user_id = {$userDAO->id} and referral_status < 5");

		while ($referrals->fetch())
		{
			$referrals->delete();
		}
	}

}

?>