<?php
require_once 'DAO/Customer_referral_credit.php';

class CCustomerReferralCredit extends DAO_Customer_referral_credit
{
	const AVAILABLE = 'AVAILABLE';
	const EXPIRED = 'EXPIRED';
	const CONSUMED = 'CONSUMED';

	function consume()
	{
		$this->credit_state = CCustomerReferralCredit::CONSUMED;
		$this->update();
	}

	function expire()
	{
		$this->credit_state = CCustomerReferralCredit::EXPIRED;
		$this->update();
	}

	static function getUsersAvailableCreditArray($user_id)
	{
		$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit', true);
		$DAO_customer_referral_credit->user_id = $user_id;
		$DAO_customer_referral_credit->credit_state = CCustomerReferralCredit::AVAILABLE;
		$DAO_customer_referral_credit->orderBy("customer_referral_credit.expiration_date ASC");
		$DAO_customer_referral_credit->find();

		$referralCreditArray = array();

		while ($DAO_customer_referral_credit->fetch())
		{
			$referralCreditArray[$DAO_customer_referral_credit->id] = $DAO_customer_referral_credit->cloneObj(false);
		}

		return $referralCreditArray;
	}

	static function getAvailableCreditForUser($user_id)
	{
		$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit', true);
		$DAO_customer_referral_credit->user_id = $user_id;
		$DAO_customer_referral_credit->credit_state = CCustomerReferralCredit::AVAILABLE;
		$DAO_customer_referral_credit->selectAdd("SUM(customer_referral_credit.dollar_value) as total_available");
		$DAO_customer_referral_credit->find(true);

		if ($DAO_customer_referral_credit->N)
		{
			return $DAO_customer_referral_credit->total_available;
		}

		return 0;
	}

	static function getAvailableCreditForUserAndOrder($user_id, $order_id)
	{
		$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit', true);
		$DAO_customer_referral_credit->user_id = $user_id;
		$DAO_customer_referral_credit->selectAdd("SUM(customer_referral_credit.dollar_value) as total_available");
		$DAO_customer_referral_credit->whereAdd("customer_referral_credit.credit_state = '" . CCustomerReferralCredit::AVAILABLE . "' OR (customer_referral_credit.credit_state = '" . CCustomerReferralCredit::CONSUMED . "' and customer_referral_credit.order_id = '" . $order_id . "')");
		$DAO_customer_referral_credit->find(true);

		if ($DAO_customer_referral_credit->N)
		{
			return $DAO_customer_referral_credit->total_available;
		}

		return 0;
	}

	static function AdjustPointsForOrderEdit($OrderObj, $newAmount)
	{
		$retVal = array(
			'original_amount' => CTemplate::moneyFormat($OrderObj->discount_total_customer_referral_credit),
			'new_amount' => CTemplate::moneyFormat($newAmount)
		);

		$originalAmount = $OrderObj->discount_total_customer_referral_credit;

		if ($originalAmount < $newAmount)
		{
			self::processCredits($OrderObj->user_id, $newAmount - $originalAmount, $OrderObj->id);

			$retVal['additional_credit_consumed'] = CTemplate::moneyFormat($newAmount - $originalAmount);
		}
		else if ($originalAmount > $newAmount)
		{
			$retVal['amount_returned_to_user'] = CTemplate::moneyFormat($originalAmount - $newAmount);

			$refundAmount = $originalAmount - $newAmount;

			$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit', true);
			$DAO_customer_referral_credit->user_id = $OrderObj->user_id;
			$DAO_customer_referral_credit->order_id = $OrderObj->id;
			$DAO_customer_referral_credit->orderBy("customer_referral_credit.id ASC");
			$DAO_customer_referral_credit->find();

			while ($DAO_customer_referral_credit->fetch() && $refundAmount > 0)
			{
				if ($DAO_customer_referral_credit->dollar_value <= $refundAmount)
				{
					$org_DAO_customer_referral_credit = $DAO_customer_referral_credit->cloneObj(false);
					$DAO_customer_referral_credit->credit_state = CCustomerReferralCredit::AVAILABLE;
					$DAO_customer_referral_credit->order_id = 'null';
					$DAO_customer_referral_credit->update($org_DAO_customer_referral_credit);

					$refundAmount -= $DAO_customer_referral_credit->dollar_value;
				}
				else
				{
					// refund amount is less than the next credit entry so split it
					$revisedDollars = $DAO_customer_referral_credit->dollar_value - $refundAmount;
					$newRemainder = $refundAmount;
					$newRemainderObj = DAO_CFactory::create('customer_referral_credit', true);
					$newRemainderObj->dollar_value = $newRemainder;
					$newRemainderObj->user_id = $DAO_customer_referral_credit->user_id;

					$newRemainderObj->expiration_date = $DAO_customer_referral_credit->expiration_date;
					$newRemainderObj->credit_state = CPointsCredits::AVAILABLE;
					$newRemainderObj->parent_of_partial = $DAO_customer_referral_credit->id;

					$newRemainderObj->insert();

					$org_DAO_customer_referral_credit = clone($DAO_customer_referral_credit);
					$DAO_customer_referral_credit->original_amount = $DAO_customer_referral_credit->dollar_value;
					$DAO_customer_referral_credit->dollar_value = $revisedDollars;

					$DAO_customer_referral_credit->update($org_DAO_customer_referral_credit);

					break;
				}
			}
		}

		return $retVal;
	}

	static function processCredits($user_id, $amountToProcess, $order_id)
	{
		$referralCreditArray = CCustomerReferralCredit::getUsersAvailableCreditArray($user_id);

		$remainingToProcess = $amountToProcess;

		foreach ($referralCreditArray as $DAO_customer_referral_credit)
		{
			if ($remainingToProcess > 0)
			{
				$thisCreditAmount = $DAO_customer_referral_credit->dollar_value;

				if ($thisCreditAmount <= $remainingToProcess)
				{
					// completely consume this credit
					$remainingToProcess -= $thisCreditAmount;
					$DAO_customer_referral_credit->order_id = $order_id;
					$DAO_customer_referral_credit->consume();
				}
				else if ($remainingToProcess < $thisCreditAmount)
				{
					$remainder = $thisCreditAmount - $remainingToProcess;

					$DAO_customer_referral_credit->order_id = $order_id;
					$DAO_customer_referral_credit->original_amount = $thisCreditAmount;
					$DAO_customer_referral_credit->dollar_value = $remainingToProcess;
					$DAO_customer_referral_credit->consume();

					$remainder_inserter = DAO_CFactory::create('customer_referral_credit', true);
					$remainder_inserter->dollar_value = $remainder;
					$remainder_inserter->credit_state = CCustomerReferralCredit::AVAILABLE;
					$remainder_inserter->expiration_date = $DAO_customer_referral_credit->expiration_date;
					$remainder_inserter->parent_of_partial = $DAO_customer_referral_credit->id;
					$remainder_inserter->user_id = $user_id;
					$remainder_inserter->insert();

					$remainingToProcess = 0;
				}
			}
		}
	}

	static function handleOrderCancelled($UserObj, $OrderObj)
	{
		$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit', true);
		$DAO_customer_referral_credit->user_id = $UserObj->id;
		$DAO_customer_referral_credit->credit_state = CCustomerReferralCredit::CONSUMED;
		$DAO_customer_referral_credit->order_id = $OrderObj->id;
		$DAO_customer_referral_credit->orderBy("customer_referral_credit.id asc");
		$DAO_customer_referral_credit->find();

		while ($DAO_customer_referral_credit->fetch())
		{
			$org_DAO_customer_referral_credit = $DAO_customer_referral_credit->cloneObj(false);

			$DAO_customer_referral_credit->order_id = 'null';
			$DAO_customer_referral_credit->credit_state = CPointsCredits::AVAILABLE;
			$DAO_customer_referral_credit->update($org_DAO_customer_referral_credit);
		}
	}
}

?>