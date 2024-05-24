<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

$_DAYS_BEFORE = 3;

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: process_reminders called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::SESSION_REMINDERS, "process_reminders called but cron is disabled.");
		exit;
	}

	$DAO_session_rsvp = DAO_CFactory::create('session_rsvp', true);
	$DAO_session_rsvp->whereAdd("DATEDIFF(NOW(), session.session_start ) = -" . $_DAYS_BEFORE);
	$DAO_session_rsvp->whereAdd("store.store_type = '" . CStore::FRANCHISE . "'");
	$DAO_session_rsvp->whereAdd("session_rsvp.upgrade_booking_id IS NULL");
	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$DAO_session_rsvp->limit(10);
	}
	$DAO_session_rsvp->find_DAO_session_rsvp();

	$totalCount = 0;

	while ($DAO_session_rsvp->fetch())
	{
		$DAO_session_rsvp->send_reminder_email();
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