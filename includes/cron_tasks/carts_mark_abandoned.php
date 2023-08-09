<?php
/*
 * @author evanl
 */
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");

require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CCart2.inc");
require_once("CLog.inc");


try {
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: Mark Abandoned Carts called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::CLEAR_STALE_CARTS, "clear_stale_carts called but cron is disabled.");
		exit;
	}

	$cartIDs = CCartStorage::detectAbandonedCartRows(10);
	$totalCount = count($cartIDs);

	if ($totalCount > 0)
	{
        CCartStorage::markAbandonedCartRows($cartIDs);
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::MARK_CART_ABANDONED, $totalCount . " carts marked abandoned.");
}
catch (exception $e)
{
	CLog::RecordCronTask(0, CLog::PARTIAL_FAILURE, CLog::MARK_CART_ABANDONED, CLog::MARK_CART_ABANDONED. " - Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>