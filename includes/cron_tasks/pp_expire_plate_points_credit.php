<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");

if (defined("DISABLE_CRON") && DISABLE_CRON)
{
	CLog::Record("CRON: referral_rewards called but cron is disabled");
	CLog::RecordCronTask(1, CLog::FAILURE, CLog::EXPIRE_PLATEPOINTS_CREDITS, "pp_expire_plate_points_credit called but cron is disabled.");
	exit;
}

//EXPIRE CREDITS
try {

	$Credits = DAO_CFactory::create('points_credits');

	$Credits->query("select pc.user_id, sum(pc.dollar_value) as dollar_value, GROUP_CONCAT(pc.id) as credit_ids from points_credits pc
                            join user u on u.id = pc.user_id and u.is_deleted = 0
                            where pc.expiration_date < now() and pc.credit_state = 'AVAILABLE' and pc.is_deleted = 0 group by pc.user_id");

	$totalCount = 0;

	while ( $Credits->fetch() ) {

		CPointsCredits::expireCredits($Credits);
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::EXPIRE_PLATEPOINTS_CREDITS, "$totalCount PlatePoints credit expirations processed.");

}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::EXPIRE_PLATEPOINTS_CREDITS, "pp_expire_plate_points_credit: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}



// WARN OF EXPIRATION
try {

	$Credits = DAO_CFactory::create('points_credits');

	$Credits->query("select pc.user_id, u.firstname, u.primary_email, GROUP_CONCAT(pc.dollar_value) as dollar_value, GROUP_CONCAT(pc.id) as credit_ids, GROUP_CONCAT(pc.expiration_date) as expiration_date from points_credits pc
					join user u on u.id = pc.user_id where DATEDIFF(now(), pc.expiration_date) = -45 and pc.credit_state = 'AVAILABLE'
							and pc.is_deleted = 0  and u.is_deleted = 0 group by pc.user_id order by pc.id asc");

	$totalCount = 0;

	while ( $Credits->fetch() ) {

		CPointsCredits::sendExpiringCreditWaring($Credits);
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::WARN_EXPIRE_PLATEPOINTS_CREDITS, "$totalCount PlatePoints credit expiration warnings processed.");

}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::WARN_EXPIRE_PLATEPOINTS_CREDITS, "pp_expire_plate_points_credit - warnings: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>