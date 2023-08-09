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

	static function getUsersAvailableCredits($user_id)
	{
		$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit');
		$DAO_customer_referral_credit->user_id = $user_id;
		$DAO_customer_referral_credit->credit_state = CCustomerReferralCredit::AVAILABLE;
		$DAO_customer_referral_credit->orderBy("expiration_date ASC");
		$DAO_customer_referral_credit->find();

		$referralCreditArray = array();

		while ($DAO_customer_referral_credit->fetch())
		{
			$referralCreditArray[$DAO_customer_referral_credit->id] = $DAO_customer_referral_credit->cloneObj(false);
		}

		return $referralCreditArray;
	}

	static function processCredits($user_id, $amountToProcess, $order_id)
	{
		$referralCreditArray = CCustomerReferralCredit::getUsersAvailableCredits($user_id);

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
}

?>