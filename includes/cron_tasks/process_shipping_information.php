<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CLog.inc");
require_once("includes/api/shipping/shipstation/ShipStationManager.php");
require_once("includes/api/shipping/shipstation/ShipStationOrderBatchWrapper.php");

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: process_shipping_information called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::PROCESS_SHIPPING_INFORMATION, "process_shipping_information called but cron is disabled.");
		exit;
	}

	$DAO_transient_data_store = DAO_CFactory::create('transient_data_store', true);
	$DAO_transient_data_store->data_class = TransientDataStore::SHIPPING_SHIP_NOTIFICATION_NEW;
	$DAO_transient_data_store->limit(20);
	$DAO_transient_data_store->orderBy("RAND()");
	$DAO_transient_data_store->find();

	while ($DAO_transient_data_store->fetch())
	{
		ShipStationManager::loadOrderShippingInfo($DAO_transient_data_store);
	}

	CLog::RecordCronTask(1, CLog::SUCCESS, CLog::PROCESS_SHIPPING_INFORMATION, "Processed shipping information.");
}
catch (exception $e)
{
	CLog::RecordCronTask(1, CLog::PARTIAL_FAILURE, CLog::PROCESS_SHIPPING_INFORMATION, "process_shipping_information: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}