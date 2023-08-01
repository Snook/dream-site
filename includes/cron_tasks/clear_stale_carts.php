<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");

require_once("/DreamSite/includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CCart2.inc");
require_once("CLog.inc");

try {
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: referral_rewards called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::CLEAR_STALE_CARTS, "clear_stale_carts called but cron is disabled.");
		exit;
	}

	$currentMenuID = CMenu::getCurrentMenuId();

	$cartIDs = CCartStorage::getStaleCartRows($currentMenuID);
	$totalCount = count($cartIDs);

	if ($totalCount > 0)
	{
		$Deleter = new DAO();
		$Deleter->query("delete from dreamsite.cart where cart_contents_id in (" . implode(",", $cartIDs) .  ")");

        CCartStorage::markRowsAsStale($cartIDs);

	}

	$cartIDs = CCartStorage::getStaleEditOrderCartRows();
	$totalCount = count($cartIDs);

	if ($totalCount > 0)
	{
		$Deleter = new DAO();
		$Deleter->query("delete from dreamsite.cart where cart_contents_id in (" . implode(",", $cartIDs) .  ")");

		CCartStorage::markRowsAsStale($cartIDs);

	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::CLEAR_STALE_CARTS, $totalCount . " stale carts cleared.");
}
catch (exception $e)
{
	CLog::RecordCronTask(0, CLog::PARTIAL_FAILURE, CLog::CLEAR_STALE_CARTS, "clear_stale_carts: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}
/*
 * ALTER TABLE `dreamcart`.`cart_contents`
ADD COLUMN `is_stale` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `timestamp_updated`;
 */

?>
