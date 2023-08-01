<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
//require_once("c:\wamp\www\DreamSite2\includes\Config.inc");
require_once("/DreamSite/includes/Config.inc");
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
