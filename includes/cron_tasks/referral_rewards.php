<?php
require_once("../Config.inc");
require_once("DAO.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: referral_rewards called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::REFERRAL_REWARDS, "referral_rewards called but cron is disabled.");
		exit;
	}

	$DAO_customer_referral = DAO_CFactory::create('customer_referral', true);

	$DAO_customer_referral->query("select customer_referral.*, 
		orders.bundle_id, 
		booking.booking_type, 
		user.home_store_id, 
		user.dream_rewards_version, 
		user.dream_reward_status, 
		store.email_address as store_email,
		session.session_start, 
		session.session_type, 
		CONCAT(u2.firstname, ' ', u2.lastname) as referred_user_name, 
		s2.menu_id as taste_menu 
		from customer_referral
 		join user on user.id = customer_referral.referring_user_id and user.dream_rewards_version = 3 and (user.dream_reward_status = 1 OR user.dream_reward_status = 3) and user.home_store_id > 0
		join user u2 on u2.id = customer_referral.referred_user_id
		join booking on customer_referral.first_order_id = booking.order_id and booking.status = 'ACTIVE'
		join orders on orders.id = booking.order_id
		join session on session.id = booking.session_id and DATEDIFF(NOW(), session.session_start ) > 3 
 		join store on store.id = session.store_id
		left join session s2 on customer_referral.referrer_session_id = s2.id
		where customer_referral.referral_status = 3 
		and customer_referral.first_order_id is not null
		and customer_referral.is_deleted = 0");

	$totalCount = 0;
	// Note: exception are handled, logged but not rethrown in the send_reminder_email function
	while ($DAO_customer_referral->fetch())
	{
		$DAO_customer_referral->process_customer_referral_credit();
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::REFERRAL_REWARDS, " $totalCount referral awards processed.");
}
catch (exception $e)
{
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::REFERRAL_REWARDS, "referral_rewards: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}
?>