<?php
require_once 'DAO/Points_credits.php';
require_once 'CMailHandlers.inc';

class CPointsCredits extends DAO_Points_credits
{
	const AVAILABLE = 'AVAILABLE';
	const EXPIRED = 'EXPIRED';
	const GUEST = 'CONSUMED';


	static function formatExpirationDateForGuest($date)
	{
		$timeComp = date("H:i:s", strtotime($date));
		if ($timeComp == '03:00:00')
		{
			// The convention is to set the actual expirate date to 03:00am server time.  The cron job runs after 3:00.  This way Pacific time zone guest will have the expiration occur after midnight
			// their time.  East Coast guests will have an additional 3 hours.
			// To the guest show the expiration as 11:59pm.  Therefore an expiration date of 2022-05-09 03:00:00 will display as May 8th, 11:59pm.

			$date = date("Y-m-d 23:59:00", strtotime($date) - 86400);
		}


		return $date;

	}

	static function expireCredits($data)
	{

		$user_id = $data->user_id;
		$creditIDs = explode(",", $data->credit_ids);

		$creditInfo = array('amount_expired' => $data->dollar_value, 'credit_ids' => $data->credit_ids);
		CPointsUserHistory::handleEvent($user_id, CPointsUserHistory::CREDIT_EXPIRED, $creditInfo);

		foreach($creditIDs as $creditID)
		{
			$tmpCreditObj = DAO_CFactory::create('points_credits');
			$tmpCreditObj->query("update points_credits set credit_state = 'EXPIRED' where id = $creditID and user_id = $user_id");
		}


	}

	static function sendExpiringCreditWaring($data)
	{

		$creditIDs = explode(",", $data->credit_ids);
		$amounts = explode(",", $data->dollar_value);
		$dates = explode(",", $data->expiration_date);

		$creditInfo = array();

		foreach($creditIDs as $creditID)
		{
			$thisAmount = array_shift($amounts);
			$thisDate =  array_shift($dates);

			$creditInfo[$creditID] = array('credit_id' =>  $creditID, 'amount' => $thisAmount, 'expiration_date' => $thisDate);

		}

		$data->creditArray = $creditInfo;
		
		// Now only logs that an email would have been sent
		plate_points_mail_handlers::sendPlatePointsExpiringCreditWarningEmail($data);



	}

	static function getUsersAvailableCredits($user_id)
	{
		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->user_id = $user_id;
		$creditObj->credit_state = CPointsCredits::AVAILABLE;
		$creditObj->orderBy('expiration_date ASC');
		$creditObj->find();

		$creditArray = array();

		// DD EXP DATE DISPLAY 1
		while ($creditObj->fetch())
		{
			$creditArray[$creditObj->id]['dollar_value'] = $creditObj->dollar_value;
			$creditArray[$creditObj->id]['expiration_date'] = self::formatExpirationDateForGuest($creditObj->expiration_date);
		}

		return $creditArray;
	}

	static function getAvailableCreditForUser($user_id)
	{
		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->query("select sum(dollar_value) as total_available from points_credits where user_id = $user_id and is_deleted = 0 and credit_state = '" . CPointsCredits::AVAILABLE . "' group by user_id");
		$creditObj->fetch();

		if ($creditObj->N)
			return $creditObj->total_available;

		return 0;
	}


	static function getAvailableCreditForUserAndOrder($user_id, $order_id)
	{
		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->query("select sum(dollar_value) as total_available from points_credits 
                            where user_id = $user_id and is_deleted = 0 and (credit_state = '" . CPointsCredits::AVAILABLE . "' or (credit_state = 'CONSUMED' and order_id = $order_id)) group by user_id");
		$creditObj->fetch();

		if ($creditObj->N)
			return $creditObj->total_available;

		return 0;
	}


	static function AdjustPointsForOrderEdit($OrderObj, $newAmount)
	{

		$retVal = array('original_amount' => CTemplate::moneyFormat($OrderObj->points_discount_total), 'new_amount' => CTemplate::moneyFormat($newAmount));

		$originalAmount = $OrderObj->points_discount_total;

		if ($originalAmount < $newAmount)
		{
			self::processCredits($OrderObj->user_id, $newAmount - $originalAmount, $OrderObj->id);

			$retVal['additional_credit_consumed'] = CTemplate::moneyFormat($newAmount - $originalAmount);
		}
		else if ($originalAmount > $newAmount)
		{

			$retVal['amount_returned_to_user'] = CTemplate::moneyFormat($originalAmount - $newAmount);

			$refundAmount = $originalAmount - $newAmount;

			$creditObj = DAO_CFactory::create('points_credits');
			$creditObj->query("select * from points_credits where user_id = {$OrderObj->user_id} and is_deleted = 0 and order_id = {$OrderObj->id} order by id desc");
			while($creditObj->fetch() && $refundAmount > 0)
			{
				if ($creditObj->dollar_value <= $refundAmount)
				{
					$preChangeObj = clone($creditObj);
					$creditObj->credit_state = CPointsCredits::AVAILABLE;
					$creditObj->order_id = 'null';
					$creditObj->update($preChangeObj);

					$refundAmount -= $creditObj->dollar_value;
				}
				else
				{ // refund amount is less than the next credit entry so split it

					$revisedDollars = $creditObj->dollar_value - $refundAmount;
					$newRemainder = $refundAmount;
					$newRemainderObj =  DAO_CFactory::create('points_credits');
					$newRemainderObj->dollar_value = $newRemainder;
					$newRemainderObj->user_id = $creditObj->user_id;

					$newRemainderObj->expiration_date = $creditObj->expiration_date;
					$newRemainderObj->credit_state = CPointsCredits::AVAILABLE;
					$newRemainderObj->parent_of_partial = $creditObj->id;

					$newRemainderObj->insert();


					$preChangeObj = clone($creditObj);
					$creditObj->original_amount = $creditObj->dollar_value;
					$creditObj->dollar_value = $revisedDollars;

					$creditObj->update($preChangeObj);



					break;
				}
			}
		}

		return $retVal;
	}


	static function handleOrderCancelled($UserObj, $OrderObj)
	{

		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->query("select * from points_credits where user_id = {$UserObj->id} and is_deleted = 0 and credit_state = 'CONSUMED' and order_id = {$OrderObj->id} order by id asc");

		$totalCreditReturned = 0;
		while ($creditObj->fetch())
		{
			$orgCreditObj = clone($creditObj);

			$creditObj->order_id = 'null';
			$creditObj->credit_state = CPointsCredits::AVAILABLE;

			$totalCreditReturned += $creditObj->dollar_value;

			$creditObj->update($orgCreditObj);
		}

		$meta_data = array('comments' => "Order Canceled - Returned $" . CTemplate::moneyFormat($totalCreditReturned) . " in PLATEPOINTS Dinner Dollars");

		CPointsUserHistory::handleEvent($UserObj, CPointsUserHistory::ORDER_CANCELLED, $meta_data, $OrderObj);

	}


	static function processCredits($user_id, $amountToProcess, $order_id)
	{
		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->query("select * from points_credits where user_id = $user_id and is_deleted = 0 and credit_state = '" . CPointsCredits::AVAILABLE . "' order by expiration_date asc");

		$remainingToProcess = $amountToProcess;

		while ($creditObj->fetch() && $remainingToProcess > 0)
		{
			$thisCreditAmount = $creditObj->dollar_value;

			if ($thisCreditAmount <= $remainingToProcess)
			{
				// complelety consume this credit
				$remainingToProcess -= $thisCreditAmount;
				$creditUpdater = DAO_CFactory::create('points_credits');
				$creditUpdater->query("update points_credits set credit_state = 'CONSUMED', order_id = $order_id where id = {$creditObj->id}");
			}
			else if ($remainingToProcess < $thisCreditAmount)
			{
				$remainder = $thisCreditAmount - $remainingToProcess;
				$creditUpdater2 = DAO_CFactory::create('points_credits');
				$creditUpdater2->query("update points_credits set credit_state = 'CONSUMED', order_id = $order_id, original_amount = $thisCreditAmount, dollar_value = $remainingToProcess where id = {$creditObj->id}");

				$remainder_inserter = DAO_CFactory::create('points_credits');
				$remainder_inserter->dollar_value = $remainder;
				$remainder_inserter->credit_state = CPointsCredits::AVAILABLE;
				$remainder_inserter->expiration_date = $creditObj->expiration_date;
				$remainder_inserter->parent_of_partial = $creditObj->id;
				$remainder_inserter->user_id = $user_id;
				$remainder_inserter->insert();

				$remainingToProcess = 0;
			}
		}
	}

	static function getNextExpiringCredit($user_id)
	{
		$retVal = array(0, false);


		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->query("select * from points_credits
								where user_id = $user_id and credit_state = 'AVAILABLE'  and is_deleted = 0
								order by expiration_date asc");

		if ($creditObj->N == 0)
			return $retVal;

		$firstDay = false;

		while($creditObj->fetch())
		{
			if (!$firstDay)
			{
				// DD EXP DATE DISPLAY 2
				$firstDay = self::formatExpirationDateForGuest($creditObj->expiration_date);
				$retVal[0] = CTemplate::dateTimeFormat($firstDay, MONTH_DAY_YEAR);
				$retVal[1] = $creditObj->dollar_value;
			}
			else
			{
				if ($firstDay == date("Y-m-d", strtotime($creditObj->expiration_date)))
				{
					$retVal[1] += $creditObj->dollar_value;
				}
				else
				{
					break;
				}
			}
		}

		return $retVal;
	}

	static function getAllExpiringCredit($user_id)
	{
		$rows = array();


		$creditObj = DAO_CFactory::create('points_credits');
		$creditObj->query("select * from points_credits
								where user_id = $user_id and credit_state = 'AVAILABLE'  and is_deleted = 0
								order by expiration_date asc");

		if ($creditObj->N == 0)
			return $rows;

		while($creditObj->fetch())
		{
			$rows[] = clone($creditObj);
		}

		return $rows;
	}


	static function addPPCreditToStoreCreditReport($store, &$rows, $getDatesAsUnixTimestamp = false)
	{

		$queryObj = DAO_CFactory::create('points_credits');

		$queryObj->query("select pc.user_id, u.firstname, u.lastname, u.primary_email, u.telephone_1, u.telephone_2, pc.credit_state, pc.dollar_value, pc.expiration_date, pc.timestamp_created  from points_credits pc
						join user u on u.id = pc.user_id and u.home_store_id = $store
						where pc.credit_state = 'AVAILABLE' and pc.is_deleted = 0");

		while($queryObj->fetch())
		{
			// DD EXP DATE DISPLAY 3
			$tarray = array('user_id' => $queryObj->user_id,
					'lastname' => $queryObj->lastname,
					'firstname' => $queryObj->firstname,
					'primary_email' => $queryObj->primary_email,
					'telephone' => (!empty($queryObj->telephone_1) ? $queryObj->telephone_1 : ((!empty($queryObj->telephone_2) ? $queryObj->telephone_2 : 'N/A'))),
					'credit_type' => 100,
					'amount' => CTemplate::moneyFormat($queryObj->dollar_value),
					'timestamp_created' => CTemplate::dateTimeFormat(date($queryObj->timestamp_created)),
					'description' => 'n/a',
					'referred_guest' => "",
					'referred_guest_id' => "",
					'expiration_date' =>  CTemplate::dateTimeFormat(date("Y-m-d 1:00:00", strtotime($queryObj->expiration_date))));
			
			if ($getDatesAsUnixTimestamp)
			{
				$tarray['expiration_date'] =  strtotime($queryObj->expiration_date);
				$tarray['timestamp_created'] =  strtotime($queryObj->timestamp_created);
			}

			$rows[$queryObj->user_id][] = $tarray;

		}


	}



}
?>