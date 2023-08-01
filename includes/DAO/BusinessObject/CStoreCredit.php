<?php

require_once 'DAO/Store_credit.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CStoreCredit
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *
 *  	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class CStoreCredit extends DAO_Store_credit
{

	function __construct()
	{
		parent::__construct();
	}

	static $CreditShortDescMap = array(
		0 => 'unknown',
		1 => 'Gift Card',
		2 => 'Referral',
		3 => 'Direct'
	);

	static function getActiveCreditByStore($store_id, $group_by_user_id = true, $getDatesAsUniuxTime = false)
	{
		$rows = null;
		$credit = DAO_CFactory::create("store_credit");
		$credit->query("Select 
			store_credit.user_id, 
			`user`.lastname,
			`user`.firstname,
			`user`.primary_email,
			`user`.telephone_1,
			`user`.telephone_2,
			store_credit.amount, 
			store_credit.timestamp_created, 
			`store_credit`.`credit_type`, 
			`store_credit`.`description`, 
			CONCAT(u2.firstname,' ', u2.lastname) as referred_guest, 
			u2.id as referred_guest_id
			From store_credit Inner Join `user` ON store_credit.user_id = `user`.id
			LEFT JOIN customer_referral cr on cr.store_credit_id = store_credit.id
			left join user u2 on cr.referred_user_id = u2.id
			where store_credit.store_id = $store_id and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 
			order by user_id, store_credit.timestamp_created");

		$rows = array();

		while ($credit->fetch())
		{
			$tarray = array(
				'user_id' => $credit->user_id,
				'lastname' => $credit->lastname,
				'firstname' => $credit->firstname,
				'primary_email' => $credit->primary_email,
				'telephone' => (!empty($credit->telephone_1) ? $credit->telephone_1 : ((!empty($credit->telephone_2) ? $credit->telephone_2 : 'N/A'))),
				'credit_type' => $credit->credit_type,
				'amount' => $credit->amount,
				'timestamp_created' => $credit->timestamp_created,
				'description' => $credit->description,
				'referred_guest' => $credit->referred_guest,
				'referred_guest_id' => $credit->referred_guest_id
			);

			$tarray['origination_date'] = empty($tarray['date_original_credit']) ? $tarray['timestamp_created'] : $tarray['date_original_credit'];
			if ($credit->credit_type != 1)
			{
				$expirationTS = strtotime($tarray['origination_date']);
				$expirationTS += (86400 * 90);
				if (!$getDatesAsUniuxTime)
				{
					$tarray['expiration_date'] = CTemplate::dateTimeFormat(date("Y-m-d 1:00:00", $expirationTS));
				}
				else
				{
					$tarray['expiration_date'] = $expirationTS;
				}
			}
			else
			{
				$tarray['expiration_date'] = "n/a";
			}

			if (!$getDatesAsUniuxTime)
			{
				$tarray['timestamp_created'] = CTemplate::dateTimeFormat($tarray['timestamp_created']);
			}
			else
			{
				$tarray['timestamp_created'] = strtotime($tarray['timestamp_created']);
			}

			if ($group_by_user_id)
			{
				$rows [$credit->user_id][] = $tarray;
			}
			else
			{
				$rows[] = $tarray;
			}
		}

		return ($rows);
	}

	static function addPendingReferralCreditByStore($store_id, &$rows, $getDatesAsUniuxTime = false)
	{
		$credit = DAO_CFactory::create("customer_referral");
		$credit->query("select 
			cr.id as credit_id, 
			cr.referring_user_id, 
			cr.referred_user_id, 
			u.firstname, 
			u.lastname, 
			u.primary_email, 
			u.telephone_1, 
			u.telephone_2, 
			o.bundle_id, 
			b.booking_type, 
			u.home_store_id, 
			st.email_address as store_email, 
			s.session_start, 
			s.session_type, 
			CONCAT(u2.firstname, ' ', u2.lastname) as referred_user_name 
			from customer_referral cr
			join booking b on cr.first_order_id = b.order_id and b.status = 'ACTIVE'
			join orders o on o.id = b.order_id
			join session s on s.id = b.session_id
			join user u on u.id = cr.referring_user_id
			join user u2 on u2.id = cr.referred_user_id
			join store st on st.id = s.store_id
			where cr.referral_status = 3 and cr.first_order_id is not null and cr.is_deleted = 0 and u.home_store_id = $store_id and u.dream_rewards_version < 3");

		while ($credit->fetch())
		{

			$reward_amount = 10;
			if ($credit->booking_type == 'INTRO' || (!empty($credit->bundle_id) && $credit->bundle_id > 0))
			{
				$reward_amount = 5;
			}

			$awardDate = date('Y-m-d', strtotime($credit->session_start) + (86400 * 3)) . " 1:00am";

			$tarray = array(
				'user_id' => $credit->referring_user_id,
				'lastname' => $credit->lastname,
				'firstname' => $credit->firstname,
				'primary_email' => $credit->primary_email,
				'telephone' => (!empty($credit->telephone_1) ? $credit->telephone_1 : ((!empty($credit->telephone_2) ? $credit->telephone_2 : 'N/A'))),
				'credit_type' => 99,
				'amount' => CTemplate::moneyFormat($reward_amount),
				'timestamp_created' => $awardDate,
				'description' => 'n/a',
				'referred_guest' => $credit->referred_user_name,
				'referred_guest_id' => $credit->referred_user_id
			);

			$tarray['origination_date'] = empty($tarray['date_original_credit']) ? $tarray['timestamp_created'] : $tarray['date_original_credit'];

			$expirationTS = strtotime($tarray['origination_date']);
			$expirationTS += (86400 * 90);

			if (!$getDatesAsUniuxTime)
			{
				$tarray['timestamp_created'] = CTemplate::dateTimeFormat(date($awardDate));
				$tarray['expiration_date'] = CTemplate::dateTimeFormat(date("Y-m-d 1:00:00", $expirationTS));
			}
			else
			{
				$tarray['expiration_date'] = $expirationTS;
				$tarray['timestamp_created'] = strtotime($awardDate);
			}
			$rows[$credit->referring_user_id][] = $tarray;
		}
	}

	static function getPendingReferralCreditPerUser($store_id, $user_id)
	{

		$StoreClause = "";
		if (!empty($store_id))
		{
			$StoreClause = "and o.store_id = $store_id";
		}

		$retVal = array();

		$credit = DAO_CFactory::create("customer_referral");
		$credit->query("select cr.id as credit_id, cr.referring_user_id, cr.referred_user_id, u.firstname, u.lastname, u.primary_email, st.store_name,
			o.bundle_id, b.booking_type, u.home_store_id, st.email_address as store_email, s.session_start, s.session_type,
			CONCAT(u2.firstname, ' ', u2.lastname) as referred_user_name 
			from customer_referral cr
			join booking b on cr.first_order_id = b.order_id and b.status = 'ACTIVE'
			join orders o on o.id = b.order_id
			join session s on s.id = b.session_id
			join user u on u.id = cr.referring_user_id
			join user u2 on u2.id = cr.referred_user_id
			join store st on st.id = s.store_id
			where cr.referral_status = 3 and cr.first_order_id is not null and cr.is_deleted = 0 $StoreClause and cr.referring_user_id = $user_id and u.dream_rewards_version < 3");

		while ($credit->fetch())
		{

			$reward_amount = 10;
			if ($credit->booking_type == 'INTRO' || (!empty($credit->bundle_id) && $credit->bundle_id > 0))
			{
				$reward_amount = 5;
			}

			$awardDate = date('Y-m-d', strtotime($credit->session_start) + (86400 * 3)) . " 1:00am";

			$tarray = array(
				'user_id' => $credit->referring_user_id,
				'lastname' => $credit->lastname,
				'firstname' => $credit->firstname,
				'primary_email' => $credit->primary_email,
				'amount' => $reward_amount,
				'award_date' => $awardDate,
				'description' => 'n/a',
				'referred_guest' => $credit->referred_user_name,
				'referred_guest_id' => $credit->referred_user_id,
				'store_name' => $credit->store_name,
				'credit_id' => $credit->credit_id,
				'store_id' => $store_id
			);

			$expirationTS = strtotime($awardDate);
			$expirationTS += (86400 * 90);
			$tarray['expiration_date'] = CTemplate::dateTimeFormat(date("Y-m-d 1:00:00", $expirationTS));

			$retVal[] = $tarray;
		}

		return $retVal;
	}

	static function getActiveCreditByID($array_id)
	{
		$id_list = implode(",", $array_id);
		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->query("Select 
			store_credit.credit_card_number,
			store_credit.amount
			From store_credit 
			Inner Join `store` ON store_credit.store_id = `store`.id 
			where store_credit.id in ($id_list) and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 
			order by store_credit.timestamp_created");

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$tarray['credit_card_number'] = str_pad($tarray['credit_card_number'], 16, "X", STR_PAD_LEFT);
			$rows [] = $tarray;
		}

		return ($rows);
	}

	/**
	 *
	 * Lookup the dinner dollar history for a given user
	 *
	 * @param  int   $user_id
	 * @param string $limitType [month|rowcount] - default is month
	 * @param mixed  $limit  int for number of month or total rows, or mysql limit range e.g. '10,30'. Used for paging. Default is 3
	 * @param int    $totalCreditValue passed by reference used to return total dollars available, if needed.
	 *
	 * @return array [timestamp,timestamp_updated,expires,amount,state,events,unique_events,orders]
	 * @throws Exception
	 */
	static function fetchDinnerDollarHistoryByUser($user_id, $limitType = 'month', $limit = 3, &$totalCreditValue = 0)
	{

		$limitClauseMonth = '';
		$limitClauseRowCount = '';
		switch ($limitType) {
			case 'month':
				$limitClauseMonth = " and pc.timestamp_created >= DATE_FORMAT(CURDATE(), '%Y-%m-01') - INTERVAL ".$limit." MONTH ";
				break;
			case 'rowcount':
				$limitClauseRowCount = " limit " . $limit;
				break;
		}
		$totalCredit = 0;
		$rows = array();
		$creditsObj = DAO_CFactory::create('points_credits');
		$q = "select pc.id, pc.timestamp_created, pc.timestamp_updated, pc.credit_state, pc.dollar_value, pc.expiration_date, GROUP_CONCAT(puh.event_type) as events, GROUP_CONCAT(pc.order_id) as orders 
									from points_credits pc
									left join points_to_points_credits ppc on ppc.points_credit_id = pc.id
									left join points_user_history puh on puh.id = ppc.points_user_history_id AND puh.is_deleted = '0'
									where pc.user_id = $user_id and pc.is_deleted = 0 and pc.dollar_value > 0
									" .$limitClauseMonth. "
									group by pc.id
									order by pc.timestamp_created desc " .$limitClauseRowCount;
		$creditsObj->query($q);
		// DD EXP DATE DISPLAY 4
		while ($creditsObj->fetch())
		{
			$orders = $creditsObj->orders;
			$orders_arr = explode (",", $orders);
			$orders_arr = array_unique($orders_arr);
			$uniqueOrders = implode(', ', $orders_arr);

			$events = $creditsObj->events;
			$events_arr = explode (",", $events);
			$events_arr = array_unique($events_arr);
			$uniqueEvents= implode(', ', $events_arr);


			$rows[$creditsObj->id] = array(
				"timestamp" => $creditsObj->timestamp_created,
				"timestamp_updated" => $creditsObj->timestamp_updated,
				'expires' => CPointsCredits::formatExpirationDateForGuest($creditsObj->expiration_date),
				'amount' => $creditsObj->dollar_value,
				'state' => $creditsObj->credit_state,
				'events' => $events,
				'unique_events' => $uniqueEvents,
				'orders' => $uniqueOrders
			);

			if ($creditsObj->credit_state == 'AVAILABLE')
			{
				$totalCredit += $creditsObj->dollar_value;
			}
		}

		$totalCreditValue = $totalCredit;

		return $rows;

	}

	static function getActiveCreditByUser($user_id, $filterOutGiftCardCredit = false, $retrieveRedeemedAndExpiredCredits = false)
	{

		$GiftCardClause = "";
		if ($filterOutGiftCardCredit)
		{
			$GiftCardClause = " and store_credit.credit_type <> 1 ";
		}

		$redeemedAndExpiredClause = "";
		if (!$retrieveRedeemedAndExpiredCredits)
		{
			$redeemedAndExpiredClause = " and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 ";
		}

		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');

		$q = "Select 
			store.id as store_id, 
			store.store_name, 
			store_credit.id as sc_id, 
			store_credit.user_id, 
			store_credit.credit_card_number, 
			store_credit.description, 
			store_credit.ip_address, 
			store_credit.date_original_credit, 
			store_credit.amount, 
			store_credit.payment_transaction_number, 
			store_credit.is_redeemed, 
			store_credit.is_expired, 
			store_credit.timestamp_created, 
			store_credit.timestamp_updated, 
			store_credit.credit_type, 
			cr.origination_type_code, 
			cr.referred_user_id, 
			cr.referrer_session_id, 
			CONCAT(u2.firstname, ' ', u2.lastname) as referred_user, 
			u3.primary_email as adder_email 
			From store_credit 
			Inner Join `store` ON store_credit.store_id = `store`.id 
			left join customer_referral cr on cr.store_credit_id = store_credit.id 
			left Join `user` u2 ON cr.referred_user_id = u2.id 
			left join user u3 on u3.id = store_credit.created_by 
			where store_credit.user_id = $user_id $redeemedAndExpiredClause and store_credit.is_deleted = 0 $GiftCardClause order by store_credit.timestamp_created";
		$Store_Credit->query($q);

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$tarray['credit_card_number'] = str_pad($tarray['credit_card_number'], 16, "X", STR_PAD_LEFT);
			$tarray['origination_date'] = empty($tarray['date_original_credit']) ? $tarray['timestamp_created'] : $tarray['date_original_credit'];

			if ($Store_Credit->credit_type != 1)
			{
				$expirationTS = strtotime($tarray['origination_date']);
				$expirationTS += (86400 * 90);
				$tarray['expiration_date'] = date("Y-m-d 1:00:00", $expirationTS);
			}
			else
			{
				$tarray['expiration_date'] = "n/a";
			}

			//$tarray['origination_date'] = CTemplate::dateTimeFormat($tarray['origination_date']);
			$tarray['credit_type_name'] = self::$CreditShortDescMap[$tarray['credit_type']];
			$rows [] = $tarray;
		}

		return ($rows);
	}

	static function getActiveGCCreditByUser($user_id)
	{
		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->query("Select 
			store.store_name, 
			store_credit.user_id, 
			store_credit.credit_card_number,
			store_credit.amount,
			store_credit.payment_transaction_number,  
			store_credit.timestamp_created 
			From store_credit 
			Inner Join `store` ON store_credit.store_id = `store`.id 
			where store_credit.user_id = $user_id and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 and store_credit.credit_type = 1 
			order by store_credit.timestamp_created");

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$tarray['credit_card_number'] = str_pad($tarray['credit_card_number'], 16, "X", STR_PAD_LEFT);
			$rows [] = $tarray;
		}

		return ($rows);
	}

	static function getActiveReferralCreditByUser($user_id)
	{
		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->query("Select 
		store.store_name, 
		store_credit.user_id, 
		store_credit.credit_card_number,
		store_credit.amount,
		store_credit.payment_transaction_number,  
		store_credit.timestamp_created 
		From store_credit 
		Inner Join `store` ON store_credit.store_id = `store`.id 
		where store_credit.user_id = $user_id and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 and store_credit.credit_type = 2 
		order by store_credit.timestamp_created");

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$rows [] = $tarray;
		}

		return ($rows);
	}

	static function getActiveDirectCreditByUser($user_id)
	{
		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->query("Select 
			store.store_name, 
			store_credit.user_id, 
			store_credit.credit_card_number,
			store_credit.amount,
			store_credit.payment_transaction_number,  
			store_credit.timestamp_created 
			From store_credit Inner Join `store` ON store_credit.store_id = `store`.id 
			where store_credit.user_id = $user_id and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 and store_credit.credit_type = 3 
			order by store_credit.timestamp_created");

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$rows [] = $tarray;
		}

		return ($rows);
	}

	static function getActiveIAFReferralCreditByUser($user_id)
	{
		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->query("Select 
			STORE_CREDIT.ID, 
			store.store_name, 
			store_credit.user_id, 
			store_credit.credit_card_number, 
			store_credit.amount,
			store_credit.payment_transaction_number,  
			store_credit.timestamp_created, 
			cr.origination_type_code
			From store_credit 
			Inner Join `store` ON store_credit.store_id = `store`.id 
			left join customer_referral cr on cr.store_credit_id = store_credit.id
			where store_credit.user_id = $user_id and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 and store_credit.credit_type = 2 and (cr.origination_type_code = 1  or cr.origination_type_code is null or cr.origination_type_code = 3 ) 
			order by store_credit.timestamp_created");

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$rows [] = $tarray;
		}

		return ($rows);
	}

	static function getActiveTODDReferralCreditByUser($user_id)
	{
		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->query("Select 
			STORE_CREDIT.ID, 
			store.store_name, 
			store_credit.user_id, 
			store_credit.credit_card_number, 
			store_credit.amount,
			store_credit.payment_transaction_number,  
			store_credit.timestamp_created, 
			cr.origination_type_code 
			From store_credit 
			Inner Join `store` ON store_credit.store_id = `store`.id 
			left join customer_referral cr on cr.store_credit_id = store_credit.id
			where store_credit.user_id = $user_id and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 and store_credit.credit_type = 2 and cr.origination_type_code = 4  
			order by store_credit.timestamp_created");

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$rows [] = $tarray;
		}

		return ($rows);
	}

	static function getProgramTotalforUser($user_id)
	{
		$rows = null;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->query("Select 
			STORE_CREDIT.ID, 
			store.store_name, 
			store_credit.user_id, 
			store_credit.credit_card_number, 
			store_credit.amount,
			store_credit.payment_transaction_number,  
			store_credit.timestamp_created, 
			cr.origination_type_code 
			From store_credit 
			Inner Join `store` ON store_credit.store_id = `store`.id 
			left join customer_referral cr on cr.store_credit_id = store_credit.id 
			where store_credit.user_id = $user_id and store_credit.is_redeemed = 0 and store_credit.is_expired = 0 and store_credit.is_deleted = 0 and store_credit.credit_type = 2 and cr.origination_type_code = 4  
			order by store_credit.timestamp_created");

		$rows = array();

		while ($Store_Credit->fetch())
		{
			$tarray = $Store_Credit->toArray();
			$rows [] = $tarray;
		}

		return ($rows);
	}

	function expire_credit()
	{
		$DAO_credit = DAO_CFactory::create('store_credit');
		$DAO_credit->query("update store_credit set is_expired = 1 where id = {$this->id}");

		CUserHistory::recordUserEvent($this->user_id, $this->store_id, 'null', 400, 'null', 'null', "Store Credit ID {$this->id} expired.");
	}

	function expire_credit_warn()
	{

		try
		{
			$Mail = new CMail();

			$data = array('szName' => $this->fullname);

			if ($this->origination_type_code == 4)
			{
				$contentsText = CMail::mailMerge('credit_expire_todd.txt.php', $data, false);
				$contentsHtml = CMail::mailMerge('credit_expire_todd.html.php', $data, false);
				$subject = "Taste of Dream Dinners Store Credit Expiring Soon";
			}
			else
			{
				$contentsText = CMail::mailMerge('invite_friends_credit_expire.txt.php', $data, false);
				$contentsHtml = CMail::mailMerge('invite_friends_credit_expire.html.php', $data, false);
				$subject = "Dream Dinners Store Credit Expiring Soon";
			}

			$Mail->send(null, $this->store_email, $this->fullname, $this->primary_email, $subject, $contentsHtml, $contentsText, '', '', $this->user_id, 'credit_expiring');

			$DAO_credit = DAO_CFactory::create('store_credit');
			$DAO_credit->query("update store_credit set was_sent_60_day_warning = 1 where id = {$this->id}");
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
		}
	}

}

?>