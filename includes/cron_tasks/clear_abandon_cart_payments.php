<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CCart2.inc");
require_once("CLog.inc");

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: clear_abandoned_cart_payments called but cron is disabled");
		exit;
	}

	$result = CCartStorage::clear_old_payments();
	CLog::Record("CRON: Deleting old payment data: " . $result);


} catch (exception $e) {
	CLog::RecordException($e);
}

?>