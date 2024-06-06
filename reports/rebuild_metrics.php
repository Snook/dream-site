<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("CLog.inc");
require_once('includes/CDashboardReportMenuBased.inc');

restore_error_handler();

ini_set('memory_limit', '-1');
set_time_limit(3600 * 24);

try
{
	$dateSpans = array(
		'2023-10-01',
		'2023-11-01',
		'2023-12-01',
		'2024-01-01',
		'2024-02-01',
		'2024-03-01',
		'2024-04-01',
		'2024-05-01',
		'2024-06-01',
	);

	echo "staring rebuild\r\n";

	foreach ($dateSpans as $thisDate)
	{
		echo "\r\nrebuilding $thisDate\r\n";

		$dateArr = explode("-", $thisDate);
		$thisMonth = $dateArr[1];
		$thisYear = $dateArr[0];

		$menuObj = new CMenu();
		$menuObj->query("SELECT id, menu_start FROM menu WHERE '$thisDate' = menu_start");
		$menuObj->fetch();
		$curMenuID = $menuObj->id;

		list($start_date, $interval) = CMenu::getMenuStartandInterval($curMenuID);

		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select id from store where store_type = 'DISTRIBUTION_CENTER' and active = 1");

		CLog::RecordCronTask(1, CLog::SUCCESS, CLog::DASHBOARDCACHING, "Historical Metrics cached for " . $thisDate);

		$doneList = "";

		while ($storeObj->fetch())
		{
			CDashboardMenuBased::updateAGRMetrics($storeObj->id, $thisDate, $start_date, $interval);
			CDashboardMenuBased::updateGuestMetrics($storeObj->id, $thisDate, $start_date, $interval);
			//CDashboardMenuBased::updateAGRMetrics(119, $thisDate, $start_date, $interval);
			//CDashboardMenuBased::updateGuestMetrics(119, $thisDate, $start_date, $interval);

			$doneList .= $storeObj->id . ", ";
			echo $doneList . " done\r";
			//CLog::RecordCronTask(1, CLog::SUCCESS, CLog::DASHBOARDCACHING, "Historical Metrics cached for " . $storeObj->id);
		}

		CDashboardMenuBased::rankStores($thisDate);
	}
}
catch (exception $e)
{

	CLog::RecordException($e);
}

?>