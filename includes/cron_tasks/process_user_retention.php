<?php

require_once("../Config.inc");

require_once("CLog.inc");
require_once("CUserRetention.inc");

restore_error_handler();


try {

		if (defined("DISABLE_CRON") && DISABLE_CRON)
		{
			CLog::Record("CRON: process_user_retention called but cron is disabled");
			exit;
		}

		set_time_limit(100000);

		$records_processed = CUserRetention::cron_task_find_cancelled_sessions ();
		CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_REMOVE, "Cancelled Orders Processing");

		// TO BE DEPLOYED TO FIX 120+ day issues where a customer does order
		$records_processed = CUserRetention::cron_task_disable_ping_customers ();
		CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_REMOVE, "Find Customers placing orders after 120 days");

		$records_processed = CUserRetention::cron_task_add_new_inactive_customers();
		if ($records_processed == 0) CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_NEW, "cron_task_add_new_inactive_customers");
		else CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_NEW, "cron_task_add_new_inactive_customers");

		$records_processed = CUserRetention::expire_inactive_customers();
		CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_REMOVE, "Inactive Orders Processing");
		$additionalInfo = null;
		$records_processed = CUserRetention::disable_cancellation_edge_cases ($additionalInfo);
		CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_REMOVE, "disable_cancellation_edge_cases: " . $additionalInfo);

		$records_processed = CUserRetention::cron_task_returning_customers();
		if ($records_processed == 0) CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_NEW, "cron_task_returning_customers");
		else CLog::RecordCronTask($records_processed, CLog::SUCCESS, CLog::USER_RETENTION_NEW, "cron_task_returning_customers");

} catch (exception $e) {
	CLog::RecordException($e);
}

?>