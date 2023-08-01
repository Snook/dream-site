<?php
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once("/DreamSite/includes/Config.inc");
require_once("CLog.inc");
require_once("CDashboardReport.inc");
require_once("CDashboardReportMenuBased.inc");

ini_set('memory_limit', '768M');
set_time_limit(3600);

restore_error_handler();
$StoreCount = 0;

try
{
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: process_dashboard_cache_new called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::DASHBOARDCACHING, "process_dashboard_cache_new called but cron is disabled.");
		exit;
	}

	$storeObj = DAO_CFactory::create('store');
	$storeObj->query("select id from store where active = 1");

	while ($storeObj->fetch())
	{// now processes both calendar and menu based months
		CDashboardNew::updateMetricsForStoreIfNeeded($storeObj->id, true);
		$StoreCount++;
	}

	CLog::RecordCronTask($StoreCount, CLog::SUCCESS, CLog::DASHBOARDCACHING, "process_dashboard_cache_new: Metrics cached for $StoreCount stores");

	CDashboardNew::rankStores();

	$previousMenuAnchor = CDashboardMenuBased::rankStores();
	CLog::RecordCronTask(1, CLog::SUCCESS, CLog::DASHBOARDCACHING, "process_dashboard_cache_new: Ranking stores for current month.");

	$day = date('j');
	if ($day < 8)
	{
		$month = date('n');
		$year = date('Y');
		$prevMonth = mktime(0, 0, 0, $month - 1, 1, $year);
		$monthStr = date("Y-m-d", $prevMonth);
		CDashboardNew::rankStores($monthStr);

		CLog::RecordCronTask(1, CLog::SUCCESS, CLog::DASHBOARDCACHING, "process_dashboard_cache_new: Ranking stores for previous month.");
	}

	if ($previousMenuAnchor)
	{
		CDashboardMenuBased::rankStores($previousMenuAnchor);
	}
}
catch (exception $e)
{
	CLog::RecordCronTask($StoreCount, CLog::PARTIAL_FAILURE, CLog::DASHBOARDCACHING, "process_dashboard_cache_new: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>