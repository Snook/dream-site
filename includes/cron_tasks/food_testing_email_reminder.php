<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CFoodTesting.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: food_testing_email_reminder called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::FOOD_TESTING_REMINDER_EMAIL, "food_testing_email_reminder called but cron is disabled.");
		exit;
	}

	$emailCount = 0;

	$firstReminder = DAO_CFactory::create('food_testing_survey_submission');

	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$firstReminder->query("SELECT
			ftss.id,
			ftss.user_id,
			u.firstname,
			u.lastname,
			u.primary_email,
			ft.title
			FROM food_testing_survey_submission AS ftss
			INNER JOIN `user` AS u ON u.id = ftss.user_id AND u.has_opted_out_of_plate_points = '0' AND u.is_deleted = '0'
			INNER JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id AND fts.is_closed = '0' AND fts.is_deleted = '0'
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_closed = '0' AND ft.is_deleted = '0'
			WHERE DATEDIFF(NOW(), ftss.timestamp_received) >= 5
			AND NOT ISNULL(ftss.timestamp_received)
			AND ISNULL(ftss.sent_1st_reminder)
			AND ISNULL(ftss.sent_2nd_reminder)
			AND ISNULL(ftss.timestamp_completed)
			AND ftss.is_deleted = '0'
			LIMIT 4");
	}
	else
	{
		$firstReminder->query("SELECT
			ftss.id,
			ftss.user_id,
			u.firstname,
			u.lastname,
			u.primary_email,
			ft.title
			FROM food_testing_survey_submission AS ftss
			INNER JOIN `user` AS u ON u.id = ftss.user_id AND u.has_opted_out_of_plate_points = '0' AND u.is_deleted = '0'
			INNER JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id AND fts.is_closed = '0' AND fts.is_deleted = '0'
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_closed = '0' AND ft.is_deleted = '0'
			WHERE DATEDIFF(NOW(), ftss.timestamp_received) >= 5
			AND NOT ISNULL(ftss.timestamp_received)
			AND ISNULL(ftss.sent_1st_reminder)
			AND ISNULL(ftss.sent_2nd_reminder)
			AND ISNULL(ftss.timestamp_completed)
			AND ftss.is_deleted = '0'");
	}

	while ($firstReminder->fetch())
	{
		try
		{
			if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
			{
				$emailCount++;
				CLog::Record('CRON TEST - Food Testing: ' . print_r($firstReminder->toArray(), true));
			}
			else
			{
				$emailCount++;
				CFoodTesting::sendFirstReminder($firstReminder);

				$emailRec = DAO_CFactory::create('food_testing_survey_submission');
				$emailRec->id = $firstReminder->id;
				$emailRec->sent_1st_reminder = CTemplate::unix_to_mysql_timestamp(time());
				$emailRec->update();
			}
		}
		catch (Exception $e)
		{
			CLog::RecordCronTask($emailCount, CLog::PARTIAL_FAILURE, CLog::FOOD_TESTING_REMINDER_EMAIL, "food_testing_email_reminder_first: Exception occurred: " . $e->getMessage());
			CLog::RecordException($e);
		}
	}

	$secondReminder = DAO_CFactory::create('food_testing_survey_submission');

	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$secondReminder->query("SELECT
			ftss.id,
			ftss.user_id,
			u.firstname,
			u.lastname,
			u.primary_email,
			ft.title
			FROM food_testing_survey_submission AS ftss
			INNER JOIN `user` AS u ON u.id = ftss.user_id AND u.has_opted_out_of_plate_points = '0' AND u.is_deleted = '0'
			INNER JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id AND fts.is_closed = '0' AND fts.is_deleted = '0'
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_closed = '0' AND ft.is_deleted = '0'
			WHERE DATEDIFF(NOW(), ftss.timestamp_received) >= 10
			AND NOT ISNULL(ftss.timestamp_received)
			AND NOT ISNULL(ftss.sent_1st_reminder)
			AND ISNULL(ftss.sent_2nd_reminder)
			AND ISNULL(ftss.timestamp_completed)
			AND ftss.is_deleted = '0'
			LIMIT 4");
	}
	else
	{
		$secondReminder->query("SELECT
			ftss.id,
			ftss.user_id,
			u.firstname,
			u.lastname,
			u.primary_email,
			ft.title
			FROM food_testing_survey_submission AS ftss
			INNER JOIN `user` AS u ON u.id = ftss.user_id AND u.has_opted_out_of_plate_points = '0' AND u.is_deleted = '0'
			INNER JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id AND fts.is_closed = '0' AND fts.is_deleted = '0'
			INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_closed = '0' AND ft.is_deleted = '0'
			WHERE DATEDIFF(NOW(), ftss.timestamp_received) >= 10
			AND NOT ISNULL(ftss.timestamp_received)
			AND NOT ISNULL(ftss.sent_1st_reminder)
			AND ISNULL(ftss.sent_2nd_reminder)
			AND ISNULL(ftss.timestamp_completed)
			AND ftss.is_deleted = '0'");
	}

	while ($secondReminder->fetch())
	{
		try
		{
			if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
			{
				$emailCount++;
				CLog::Record('CRON TEST - Food Testing: ' . print_r($secondReminder->toArray(), true));
			}
			else
			{
				$emailCount++;
				CFoodTesting::sendSecondReminder($secondReminder);

				$emailRec = DAO_CFactory::create('food_testing_survey_submission');
				$emailRec->id = $secondReminder->id;
				$emailRec->sent_2nd_reminder = CTemplate::unix_to_mysql_timestamp(time());
				$emailRec->update();
			}
		}
		catch (Exception $e)
		{
			CLog::RecordCronTask($emailCount, CLog::PARTIAL_FAILURE, CLog::FOOD_TESTING_REMINDER_EMAIL, "food_testing_email_reminder_second: Exception occurred: " . $e->getMessage());
			CLog::RecordException($e);
		}
	}

	CLog::RecordCronTask($emailCount, CLog::SUCCESS, CLog::FOOD_TESTING_REMINDER_EMAIL, " $emailCount food testing reminder emails processed.");
}
catch (exception $e)
{
	CLog::RecordCronTask($emailCount, CLog::PARTIAL_FAILURE, CLog::FOOD_TESTING_REMINDER_EMAIL, "food_testing_email_reminder: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}
?>