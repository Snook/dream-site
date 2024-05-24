<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

$_DAYS_BEFORE = 1;

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: what_to_expect called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::SESSION_REMINDERS, "what_to_expect called but cron is disabled.");
		exit;
	}

	$DAO_booking = DAO_CFactory::create('booking', true);
	$DAO_booking->status = CBooking::ACTIVE;
	$DAO_booking->whereAdd("DATEDIFF(NOW(), session.session_start ) = -" . $_DAYS_BEFORE);
	$DAO_booking->whereAdd("session.session_type_subtype <> '" . CSession::WALK_IN . "' OR session.session_type_subtype IS NULL");
	$DAO_booking->whereAdd("store.store_type = '" . CStore::FRANCHISE . "'");
	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$DAO_booking->limit(10);
	}
	$DAO_booking->find_DAO_booking();

	$totalCount = 0;

	// Note: exception are handled, logged but not rethrown in the send_reminder_email function
	while ($DAO_booking->fetch())
	{
		$DAO_booking->send_reminder_email();
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::WHAT_TO_EXPECT, " $totalCount what to expect emails processed.");
}
catch (exception $e)
{
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::WHAT_TO_EXPECT, "what_to_expect: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>