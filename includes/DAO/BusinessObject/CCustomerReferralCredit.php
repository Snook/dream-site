<?php
require_once 'DAO/Customer_referral_credit.php';

class CCustomerReferralCredit extends DAO_Customer_referral_credit
{
	const AVAILABLE = 'AVAILABLE';
	const EXPIRED = 'EXPIRED';
	const CONSUMED = 'CONSUMED';

	static function getUsersAvailableCredits($user_id)
	{
		$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit');
		$DAO_customer_referral_credit->user_id = $user_id;
		$DAO_customer_referral_credit->credit_state = CPointsCredits::AVAILABLE;
		$DAO_customer_referral_credit->orderBy("expiration_date ASC");
		$DAO_customer_referral_credit->find();

		$referralCreditArray = array();

		while ($DAO_customer_referral_credit->fetch())
		{
			$referralCreditArray[$DAO_customer_referral_credit->id] = $DAO_customer_referral_credit->cloneObj();
		}

		return $referralCreditArray;
	}

}

?>