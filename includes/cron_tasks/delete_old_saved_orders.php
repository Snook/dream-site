<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");

set_time_limit(7200);
ini_set('memory_limit', '1012M');

function deleteSavedOrder($order_id)
{
	$bookingObj = DAO_CFactory::create('booking');
	$bookingObj->order_id = $order_id;
	$bookingObj->status = CBooking::SAVED;

	if ($bookingObj->find(true))
	{
		$bookingObj->delete();
	}
}

try
{

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: delete_old_saved_orders credit called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::DELETE_OLD_SAVED_ORDERS, "delete_old_saved_orders called but cron is disabled.");
		exit;
	}

	$curMonth = date("n");
	$curYear = date('Y');
	$curDay = date("j");

	// this should be called just after the previous month is locked. Currently that would be the 7th.  This task will
	// set up to run on any day of the month though.

	// If the day number is 1 to 6 it will query for saved orders older than the previous month, otherwise it will query for orders older than the
	// current month

	if ($curDay > 6)
	{
		$cutoffdate = date("Y-m-d H:i:s", mktime(0, 0, 0, $curMonth, 1, $curYear));
	}
	else
	{
		$cutoffdate = date("Y-m-d H:i:s", mktime(0, 0, 0, $curMonth - 1, 1, $curYear));
	}

	//override for manual processing
	//if (true)
	//{
	//    $curMonth = 9;
	//    $curYear = 2015;
	//}

	//if (true)
	//{
	//	echo "month: " . $curMonth . "\r\n";
	//	echo "year: " . $curYear . "\r\n";

	//	exit;
	//}

	$bookingObj = DAO_CFactory::create('booking');

	$bookingObj->query("select 	b.order_id, s.session_start from booking b
					join session s on s.id = b.session_id and s.session_start < '$cutoffdate' 
					where b.`status` = 'SAVED' and b.is_deleted = 0");

	$totalCount = 0;

	while ($bookingObj->fetch())
	{
		deleteSavedOrder($bookingObj->order_id);
		//echo "{$bookingObj->order_id} - {$bookingObj->session_start}\r\n";

		$totalCount++;
	}

	//echo "would have deleted $totalCount saved orders - $cutoffdate\r\n";

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::DELETE_OLD_SAVED_ORDERS, "$totalCount saved orders deleted.");
}
catch (exception $e)
{
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::DELETE_OLD_SAVED_ORDERS, "delete_old_saved_orders: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>