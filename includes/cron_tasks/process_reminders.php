<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: process_reminders called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::SESSION_REMINDERS, "process_reminders called but cron is disabled.");
		exit;
	}

	$DAO_booking = DAO_CFactory::create('booking', true);
	$DAO_booking->status = CBooking::ACTIVE;
	$DAO_booking->whereAdd("DATEDIFF(NOW(), session.session_start ) = -3");
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

	$DAO_booking = DAO_CFactory::create('session_rsvp', true);
	$DAO_booking->whereAdd("DATEDIFF(NOW(), session.session_start ) = -3");
	$DAO_booking->whereAdd("store.store_type = '" . CStore::FRANCHISE . "'");
	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$DAO_booking->limit(10);
	}
	$DAO_booking->find_DAO_session_rsvp();

	while ($DAO_booking->fetch())
	{
		$DAO_booking->send_reminder_email();
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::SESSION_REMINDERS, " $totalCount session reminder emails processed.");
}
catch (exception $e)
{
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::SESSION_REMINDERS, "process_reminders: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>