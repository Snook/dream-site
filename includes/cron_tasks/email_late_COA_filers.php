<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");


set_time_limit(7200);
ini_set('memory_limit','1012M');

define('TEST_MODE', false);

function sendFirstCOAReminderEmail($storeObj, $previousMonthName, $curMonthName)
{

	$data = array("owners" => $storeObj->firstnames, "prevMonth" => $previousMonthName, "curMonth" => $curMonthName );

	$firstnames = explode(",",$storeObj->firstnames );
	$fullnames = explode(",", $storeObj->fullnames);
	$addresses = explode(",",$storeObj->email_addresses );
	$IDs = explode(",",$storeObj->owner_ids );

	$aUserID = array_pop($IDs);

	$Mail = new CMail();

	$contentsText = CMail::mailMerge('coa_first_reminder.txt.php', $data);
	$contentsHtml = CMail::mailMerge('coa_first_reminder.html.php', $data);

	$Mail->send("Finance Dept",
		"finance@dreamdinners.com",
		$storeObj->fullnames,
		$storeObj->email_addresses,
		"P&L Financial Compliance Reminder",
		$contentsHtml,
		$contentsText,
		'',
		'',
		$aUserID,
		'coa_first_reminder');

}

function sendSecondCOAReminderEmail($storeObj)
{

	$data = array("owners" => $storeObj->firstnames);

	$firstnames = explode(",",$storeObj->firstnames );
	$fullnames = explode(",",$storeObj->fullnames );
	$addresses = explode(",",$storeObj->email_addresses );
	$IDs = explode(",",$storeObj->owner_ids );

	$aUserID = array_pop($IDs);

	$Mail = new CMail();

	$contentsText = CMail::mailMerge('coa_second_reminder.txt.php', $data);
	$contentsHtml = CMail::mailMerge('coa_second_reminder.html.php', $data);

	$Mail->send("Tina Kuna",
		"tina.kuna@dreamdinners.com",
		$storeObj->fullnames,
		$storeObj->email_addresses,
		"Final P & L Compliance Reminder",
		$contentsHtml,
		$contentsText,
		'',
		'',
		$aUserID,
		'coa_second_reminder');
}


try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: email_late_COA_filers credit called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::EMAIL_LATE_COA_FILERS, "email_late_COA_filers called but cron is disabled.");
		exit;
	}

	$curMonth = date("n");
	$curYear = date('Y');
	$curDay = date("j");
	$curMonthName = date("F");

	//The crontab will be set up to call this script on the 21st and the 24th.   Let's be cautious and be sure it is one of those 2 days and do nothing if not

	if (!TEST_MODE && $curDay != 21)
	{
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::EMAIL_LATE_COA_FILERS, "email_late_COA_filers: Called on wrong day: $curDay");
		exit;
	}

	$previousMonthTS = mktime(0,0,0,$curMonth-1,1,$curYear);
	$selectMonth = date("Y-m-d", $previousMonthTS);
	$previousMonthName = date("F", $previousMonthTS);

	// The first reminder is from Finance
	$storeObj = DAO_CFactory::create('store');

	if (TEST_MODE)
	{
		$storeObj->query("select iq.* from
			(select st.id, st.franchise_id, smpl.net_income, 'ryan.snook@dreamdinners.com, lori.pierce@dreamdinners.com' as email_addresses, GROUP_CONCAT(u.user_type), count(DISTINCT u.id) as owner_count, GROUP_CONCAT(u.id) as owner_ids,
			GROUP_CONCAT(u.firstname) as firstnames, GROUP_CONCAT(CONCAT(u.firstname, ' ', u.lastname)) as fullnames  from store st
			left join store_monthly_profit_and_loss smpl on date = '$selectMonth' and smpl.store_id = st.id
			left join user_to_franchise utf on utf.franchise_id = st.franchise_id and utf.is_deleted = 0
			left join user u on u.id = utf.user_id and u.user_type = 'FRANCHISE_OWNER' and u.is_deleted = 0
			where st.active = 1
			group by st.id) as iq
			where ISNULL(iq.net_income) limit 1");

		while($storeObj->fetch())
			{

				sendFirstCOAReminderEmail($storeObj, $previousMonthName, $curMonthName);
			//	sendSecondCOAReminderEmail($storeObj);
			}
	}
	else
	{

		$totalCount = 0;

		$storeObj->query("select iq.* from
				(select st.id, st.franchise_id, smpl.net_income, GROUP_CONCAT(u.primary_email) as email_addresses, GROUP_CONCAT(u.user_type), count(DISTINCT u.id) as owner_count, GROUP_CONCAT(u.id) as owner_ids,
				GROUP_CONCAT(u.firstname) as firstnames, GROUP_CONCAT(CONCAT(u.firstname, ' ', u.lastname)) as fullnames  from store st
				left join store_monthly_profit_and_loss smpl on date = '$selectMonth' and smpl.store_id = st.id
				left join user_to_franchise utf on utf.franchise_id = st.franchise_id and utf.is_deleted = 0
				left join user u on u.id = utf.user_id and u.user_type = 'FRANCHISE_OWNER' and u.is_deleted = 0
				where st.active = 1
				group by st.id) as iq
				where ISNULL(iq.net_income)");

		while($storeObj->fetch())
			{

				$totalCount++;

				if ($curDay == 21)
				{
					sendFirstCOAReminderEmail($storeObj, $previousMonthName, $curMonthName);
				}
				//	else
			//	{
			//		sendSecondCOAReminderEmail($storeObj);
			//	}
			}

		if ($curDay == 21)
			{
				CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::EMAIL_LATE_COA_FILERS, "$totalCount stores were sent the first reminder.");
			}
			//else
			//{
			//	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::EMAIL_LATE_COA_FILERS, "$totalCount stores were sent the final reminder.");
			//}

	}
	//echo "would have deleted $totalCount saved orders - $cutoffdate\r\n";

}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::EMAIL_LATE_COA_FILERS, "email_late_COA_filers: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>