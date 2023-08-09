<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: process_reminders called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::SESSION_REMINDERS, "process_reminders called but cron is disabled.");
		exit;
	}

	$Booking = DAO_CFactory::create('booking');
	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$Booking->query("SELECT
			s.id AS session_id,
			s.session_start,
			s.session_type,
			s.duration_minutes,
			s.menu_id,
			m.menu_name,
			u.firstname,
			u.lastname,
			u.primary_email,
			st.store_name,
			st.id AS store_id,
			st.email_address AS store_email,
			b.user_id,
			b.order_id AS order_id,
			b.booking_type,
			o.menu_program_id,
			o.bundle_id
			FROM booking AS b
			LEFT JOIN `session` AS s ON s.id = b.session_id AND s.is_deleted = '0'
			LEFT JOIN `user` AS u ON u.id = b.user_id AND u.is_deleted = '0'
			LEFT JOIN orders AS o ON b.order_id = o.id AND o.is_deleted = '0'
			LEFT JOIN store AS st ON st.id = s.store_id AND st.is_deleted = '0'
			LEFT JOIN menu AS m ON m.id = s.menu_id AND m.is_deleted = '0'
			WHERE DATEDIFF(NOW(), s.session_start ) = -3 AND b.`status` = 'ACTIVE' AND b.is_deleted = '0'
			LIMIT 4");
	}
	else
	{
		$Booking->query("SELECT
			s.id AS session_id,
			s.session_start,
			s.session_type,
			s.duration_minutes,
			s.menu_id,
			m.menu_name,
			u.firstname,
			u.lastname,
			u.primary_email,
			st.store_name,
			st.id AS store_id,
			st.email_address AS store_email,
			b.user_id,
			b.order_id AS order_id,
			b.booking_type,
			o.menu_program_id,
			o.bundle_id
			FROM booking AS b
			LEFT JOIN `session` AS s ON s.id = b.session_id AND s.is_deleted = '0'
			LEFT JOIN `user` AS u ON u.id = b.user_id AND u.is_deleted = '0'
			LEFT JOIN orders AS o ON b.order_id = o.id AND o.is_deleted = '0'
			LEFT JOIN store AS st ON st.id = s.store_id AND st.is_deleted = '0'
			LEFT JOIN menu AS m ON m.id = s.menu_id AND m.is_deleted = '0'
			WHERE DATEDIFF(NOW(), s.session_start ) = -3 AND b.`status` = 'ACTIVE' AND b.is_deleted = '0'");
	}

	$totalCount = 0;
	// Note: exception are handled, logged but not rethrown in the send_reminder_email function
	while ($Booking->fetch())
	{

		// NOTE: SalesForce takes over on 9/24/2018 - send_reminder_email() is still called from the cron job but now only logs what
	    // Fadmin would have sent for comparison purposes

		$Booking->send_reminder_email();
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