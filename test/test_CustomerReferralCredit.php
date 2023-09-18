<?php
require_once("../includes/Config.inc");
require_once("DAO.inc");
require_once("DAO/BusinessObject/CCustomerReferralCredit.php");

$DAO_user = DAO_CFactory::create('user');
$DAO_user->id = 927279;

// Get credits for user
$referralCreditArray = CCustomerReferralCredit::getUsersAvailableCredits($DAO_user->id);

// Expire credits
$DateTime_now = new DateTime();

$DAO_customer_referral_credit = DAO_CFactory::create('customer_referral_credit', true);
$DAO_customer_referral_credit->credit_state = CCustomerReferralCredit::AVAILABLE;
$DAO_customer_referral_credit->whereAdd("customer_referral_credit.expiration_date <= '" . $DateTime_now->format('Y-m-d 03:00:00') . "'");
$DAO_customer_referral_credit->find();

while ($DAO_customer_referral_credit->fetch())
{
	$DAO_customer_referral_credit->expire();
}

// Consume
$amountToProcess = 8.73;
$order_id = 3833646;

CCustomerReferralCredit::processCredits($DAO_user->id, $amountToProcess, $order_id);

?>