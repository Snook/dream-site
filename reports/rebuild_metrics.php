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
		'2024-07-01',
		'2024-08-01',
		'2024-09-01'
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

		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->active = 1;
		$DAO_store->store_type = CStore::FRANCHISE;
		$DAO_store->find();

		CLog::RecordCronTask(1, CLog::SUCCESS, CLog::DASHBOARDCACHING, "Historical Metrics cached for " . $thisDate);

		$doneList = "";

		while ($DAO_store->fetch())
		{
			CDashboardMenuBased::updateAGRMetrics($DAO_store->id, $thisDate, $start_date, $interval);
			CDashboardMenuBased::updateGuestMetrics($DAO_store->id, $thisDate, $start_date, $interval);
			//CDashboardMenuBased::updateAGRMetrics(119, $thisDate, $start_date, $interval);
			//CDashboardMenuBased::updateGuestMetrics(119, $thisDate, $start_date, $interval);

			$doneList .= $DAO_store->id . ", ";
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