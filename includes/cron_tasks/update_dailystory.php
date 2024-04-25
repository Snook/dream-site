<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CTaskRetryQueue.php");
require_once("CLog.inc");

if (defined("DISABLE_CRON") && DISABLE_CRON)
{
	CLog::Record("CRON: process retry tasks called but cron is disabled");
	exit;
}

try
{
	$DAO_user = DAO_CFactory::create('user');
	$DAO_user->query("select * from user where is_deleted = 0");

	while ($DAO_user->fetch())
	{
		// Send to DailyStory

	}

	CLog::RecordNew(CLog::DEBUG, "Processed Daily Story sync");
}
catch (exception $e)
{
	CLog::RecordException($e);
}

?>