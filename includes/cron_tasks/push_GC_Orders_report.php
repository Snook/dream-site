<?php

//require_once("c:\wamp\www\DreamSite\includes\Config.inc");
require_once("../Config.inc");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: push_GC_Orders_report called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::PUSH_GIFT_CARD_REPORT, "push_GC_Orders_report called but cron is disabled.");
		exit;
	}

	CGiftCard::sendGiftCardOrderReport();
	CLog::RecordCronTask(1, CLog::SUCCESS, CLog::PUSH_GIFT_CARD_REPORT, "Gift card report sent to 1to1.");



} catch (exception $e) {
    CLog::RecordCronTask(1, CLog::PARTIAL_FAILURE, CLog::PUSH_GIFT_CARD_REPORT, "push_GC_Orders_report: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>