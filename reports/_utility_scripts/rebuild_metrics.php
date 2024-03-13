<?php
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once("/DreamSite/includes/Config.inc");
require_once("CLog.inc");
require_once('includes/CDashboardReportMenuBased.inc');

restore_error_handler();

			ini_set('memory_limit', '-1');
			set_time_limit(3600 * 24);

	try {

		//$dateSpans = array('2022-11-01');
		$dateSpans = array('2022-08-01','2022-09-01','2022-10-01','2022-11-01','2022-12-01',
						   '2022-01-01','2022-02-01','2022-03-01','2022-04-01','2022-05-01','2022-06-01','2022-07-01');

		foreach($dateSpans as $thisDate)
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
			$storeObj->query("select id from store where active = 1");

			CLog::RecordCronTask(1, CLog::SUCCESS, CLog::DASHBOARDCACHING, "Historical Metrics cached for " . $thisDate);

			$doneList = "";

			while($storeObj->fetch())
			{
				CDashboardMenuBased::updateAGRMetrics($storeObj->id, $thisDate,$start_date, $interval);
				CDashboardMenuBased::updateGuestMetrics($storeObj->id, $thisDate, $start_date, $interval);
				$doneList .= $storeObj->id . ", ";
				//CLog::RecordCronTask(1, CLog::SUCCESS, CLog::DASHBOARDCACHING, "Historical Metrics cached for " . $storeObj->id);
			}
			echo $doneList . " done\r";

			CDashboardMenuBased::rankStores($thisDate);
		}

	} catch (exception $e) {

		CLog::RecordException($e);
	}

?>
