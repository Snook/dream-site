<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CLog.inc");
require_once("CDashboardReport.inc");
require_once("CDashboardReportMenuBased.inc");
require_once("CDashboardReportWeekBased.inc");

ini_set('memory_limit','4096M');
set_time_limit(3600);

restore_error_handler();
$StoreCount = 0;

//register_shutdown_function(function(){
 //   $error = error_get_last();
//    if(null !== $error)
  //  {
  //      echo "Caught at shutdown: " . print_r($error, true);
 //   }
//});


try {

    /*
    TODO

    if (defined("DISABLE_CRON") && DISABLE_CRON)
    {
        CLog::Record("CRON: process_dashboard_cache_new called but cron is disabled");
        CLog::RecordCronTask(1, CLog::FAILURE, CLog::DASHBOARDCACHING, "process_dashboard_cache_new called but cron is disabled.");
        exit;
    }
    */

 $Count = 0;

    if (false)
    {

        $cutoffTime = date("Y-m-d H:i:s");
        $cutoffTime = date("Y-m-d H:i:s", strtotime($cutoffTime) + 86400);

		$weekStartDateTS = strtotime('2018-12-31 00:00:00');
        $weekEndDateTS = strtotime('2019-04-22 00:00:00');

		$storeObj = DAO_CFactory::create('store');
        $storeObj->query("select id from store where active = 1");

		while($storeObj->fetch())
        {
			$Count++;
           // $weekDate = new DateTime(date("Y-m-d 00:00:00", $weekStartDateTS));
          //  while(strtotime($weekDate->format('Y-m-d 00:00:00')) < $weekEndDateTS)
         //   {
         //       $curWeek = $weekDate->format('W');
          //      $curYear = $weekDate->format('o');
          //      CDashboardWeekBased::updateGrowthScoreCard($storeObj->id, $curWeek, $curYear, $cutoffTime);
          //      $weekDate->modify('+7 days');
         //       echo "built week " . $weekDate->format("Y-m-d") . "\r\n";

			//   }
         //   echo "weeks complete\r\n";
            // 198

			$curMenuID = 197;

			$menuObj = new DAO();

			while($curMenuID < 213)
            {

                $menuObj->query("select menu_start from menu where id = " . $curMenuID);
                $menuObj->fetch();

                CDashboardMenuBased::updateGrowthScoreCard($storeObj->id, $curMenuID, $menuObj->menu_start, $cutoffTime, false);
                echo "built month " . $curMenuID . "\r\n";

				$curMenuID++;
            }

			echo "----------------------------store " .$storeObj->id . " complete $Count;\r\n";
		}

		echo "All stores complete $Count;\r\n";

		exit;
    }

	$now = date("Y-m-d");
    $menuObj = new CMenu();
    $menuObj->query("SELECT id, menu_start FROM menu WHERE '$now' <= global_menu_end_date ORDER BY id LIMIT 1");
    $menuObj->fetch();
    $curMenuID = $menuObj->id;

	$cutoffTime = date("Y-m-d H:i:s");
    $cutoffTime = date("Y-m-d H:i:s", strtotime($cutoffTime) + 86400);

	$storeObj = DAO_CFactory::create('store');
    $storeObj->query("select id from store where active = 1");

	list($weekStartDateTS, $weekEndDateTS) = CDashboardWeekBased::getFullActiveDateRange();

	while($storeObj->fetch())
    {// now processes both calendar and menu based months

		CDashboardMenuBased::updateGrowthScoreCard($storeObj->id, $curMenuID, $menuObj->menu_start, $cutoffTime);

		/*
		$weekDate = new DateTime(date("Y-m-d 00:00:00", $weekStartDateTS));
		while(strtotime($weekDate->format('Y-m-d 00:00:00')) < $weekEndDateTS)
		{
			$curWeek = $weekDate->format('W');
			$curYear = $weekDate->format('o');
			//     echo "building " . $weekDate->format('Y-m-d') . "\r\n";
			CDashboardWeekBased::updateGrowthScoreCard($storeObj->id, $curWeek, $curYear, $cutoffTime);
			$weekDate->modify('+7 days');
		}
		*/
        $StoreCount++;
    }
	// CLog::RecordCronTask($StoreCount, CLog::SUCCESS, CLog::DASHBOARDCACHING, "process_dashboard_cache_new: Metrics cached for $StoreCount stores");

} catch (exception $e) {
   // CLog::RecordCronTask($StoreCount, CLog::PARTIAL_FAILURE, CLog::DASHBOARDCACHING, "process_dashboard_cache_new: Exception occurred: " . $e->getMessage());
    CLog::RecordException($e);
}

?>