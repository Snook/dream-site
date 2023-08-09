<?php
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");


set_time_limit(10800);
ini_set('memory_limit','1012M');

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: pp_reward_birthday credit called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::BIRTHDAY_REWARDS, "pp_reward_birthday_credit called but cron is disabled.");
		exit;
	}
	$curMonth = date("n");
	$curYear = date('Y');
	$curDay = date("j");

	// Note: the cron job is set to run on the 1st and 24th of each month
	// If it runs on the 1st we are handling the current month
	// otherwise we are running it for the upcoming month
	//On the first we pick up anyone who has registered after the 24th

	//UPDATE:  this task is huge so breaking it up over 3 days

	if ($curDay > 15)
	{

		$curMonth++;
		if ($curMonth > 12)
		{
			$curMonth = 1;
			$curYear++;
		}
	}

	//override for manual processing
	if (false)
	{
	    $curMonth = 9;
	    $curYear = 2015;
	}

	if (false)
	{
		echo "month: " . $curMonth . "\r\n";
		echo "year: " . $curYear . "\r\n";

		exit;
	}


	if ($curMonth > 7 || $curYear > 2023)
	{
		//NO longer awarding Dinner Dollars

		exit;
	}

	$segmentNumber = 5; // segment 5 mean process all
	switch($curDay)
	{
		case 24:
			$segmentNumber = 1; // A-H
			break;
		case 25:
			$segmentNumber = 2; // I - R
			break;
		case 26:
			$segmentNumber = 3; // S- Z
			break;
		case 27:
			$segmentNumber = 4; // Names starting withnon-alpha chars
			break;
		default:
			$segmentNumber = 5; // all
	}

	if (DEV)
	{
		$exec = "\"C:\\Program Files\\Zend\Zend Studio 13.6.1\\plugins\\com.zend.php.executables.windows_7.1.3.201703171134\\resources\\php.exe\"";
	}
	else
	{
		$exec = "/usr/bin/php";
	}

	if (DEV)
	{
		$cmd = "C:\\Users\\Carl.Samuelson\\Zend\\workspaces\\DefaultWorkspace12\\DreamSite\\includes\\cron_tasks\\pp_reward_birthday_credit_subtask.php";
	}
	else
	{
		$cmd = "/DreamSite/includes/cron_tasks/pp_reward_birthday_credit_subtask.php";
	}

	$cmd = $exec . " " . $cmd . " " . $curYear . " " . $curMonth . " " . $segmentNumber;

	$result = system($cmd);

	if ($result)
	{
		if (strpos($result, "Fatal error") !== false)
		{

			CLog::RecordCronTask(0, CLog::PARTIAL_FAILURE, CLog::BIRTHDAY_REWARDS, "pp_reward_birthday_credit received fatal error - trying again: " . $result);

			// most likely ran out of memory so try one more time
			$result = system($cmd);

			if (strpos($result, "Fatal error") !== false)
			{
				CLog::RecordCronTask(0, CLog::FAILURE, CLog::BIRTHDAY_REWARDS, "1 pp_reward_birthday_credit received unknown error: " . $result);
			}
			else if (strpos($result, "Success") !== false)
			{
				$arr = explode(":", $result);
				CLog::RecordCronTask($arr[1], CLog::SUCCESS, CLog::BIRTHDAY_REWARDS, "{$arr[1]} birthday rewards processed.");
			}
			else
			{
				CLog::RecordCronTask(0, CLog::FAILURE, CLog::BIRTHDAY_REWARDS, "2 pp_reward_birthday_credit received unknown error: " . $result);
			}
		}
		else if (strpos($result, "Success") !== false)
		{
			$arr = explode(":", $result);
			CLog::RecordCronTask($arr[1], CLog::SUCCESS, CLog::BIRTHDAY_REWARDS, "{$arr[1]} birthday rewards processed.");
		}
		else
		{
			CLog::RecordCronTask(0, CLog::FAILURE, CLog::BIRTHDAY_REWARDS, "3 pp_reward_birthday_credit received unknown error: " . $result);
		}
	}
	else
	{
		CLog::RecordCronTask(0, CLog::FAILURE, CLog::BIRTHDAY_REWARDS, "4 pp_reward_birthday_credit received unknown error: " . $result);
	}
}
catch (exception $e)
{
	CLog::RecordCronTask(0, CLog::PARTIAL_FAILURE, CLog::BIRTHDAY_REWARDS, "pp_reward_birthday_credit: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>