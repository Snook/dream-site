<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: referral_rewards called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::REFERRAL_REWARDS, "referral_rewards called but cron is disabled.");
		exit;
	}

	$Referrals = DAO_CFactory::create('customer_referral');

	$Referrals->query("select cr.*, 
		o.bundle_id, 
		b.booking_type, 
		u.home_store_id, 
		u.dream_rewards_version, 
		u.dream_reward_status, 
		st.email_address as store_email,
		s.session_start, 
		s.session_type, 
		CONCAT(u2.firstname, ' ', u2.lastname) as referred_user_name, 
		s2.menu_id as taste_menu 
		from customer_referral cr
		join booking b on cr.first_order_id = b.order_id and b.status = 'ACTIVE'
		join orders o on o.id = b.order_id
		join session s on s.id = b.session_id and DATEDIFF(NOW(), s.session_start ) > 3 
 		join user u on u.id = cr.referring_user_id and u.dream_rewards_version = 3 and (u.dream_reward_status = 1 OR u.dream_reward_status = 3) and u.home_store_id > 0
		join user u2 on u2.id = cr.referred_user_id
 		join store st on st.id = s.store_id
		left join session s2 on cr.referrer_session_id = s2.id
		where cr.referral_status = 3 
		and cr.first_order_id is not null
		and cr.is_deleted = 0");

	$totalCount = 0;
	// Note: exception are handled, logged but not rethrown in the send_reminder_email function
	while ( $Referrals->fetch() )
	{
		$suppressReward = false;
		if ($Referrals->origination_type_code == 4 && !empty($Referrals->taste_menu) && $Referrals->taste_menu > 228) // August 2020
		{
			$suppressReward = true;
			//stop rewarding taste hostesses after August 2020
		}

		$Referrals->process_plate_points_referral_award($suppressReward);
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::REFERRAL_REWARDS, " $totalCount referral awards processed.");

} catch (exception $e) {
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::REFERRAL_REWARDS, "referral_rewards: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>