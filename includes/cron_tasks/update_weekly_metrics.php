<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
//require_once("C:\\Users\\Carl.Samuelson\\Zend\workspaces\\DefaultWorkspace\\DreamSite\\includes\\Config.inc");
require_once("/DreamReports/includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CDashboardReportWeekBased.inc");


ini_set('memory_limit','-1');
set_time_limit(3600 * 24);


try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: update_weekly_metrics called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::CACHE_WEEKLY_METRICS, "update_weekly_metrics called but cron is disabled.");
		exit;
	}
	
	
	
	list($startDateTS, $endDateTS) = CDashboardWeekBased::getFullActiveDateRange();

	//$startDateTS = strtotime('2019-05-06 00:00:00');
	
	$totalCount = 0;
	$stores = new DAO();
	$stores->query("select id from store where active = 1");
	
	while($stores->fetch())
	{
	    $store_id = $stores->id;
	    $totalCount++;
	 //   echo "building for store " . $store_id . "\r\n";
	    
	    $weekDate = new DateTime(date("Y-m-d 00:00:00", $startDateTS));
	    while(strtotime($weekDate->format('Y-m-d 00:00:00')) < $endDateTS)
	    {
	        $curWeek = $weekDate->format('W');
	        $curYear = $weekDate->format('o');
	    //    echo "building " . $weekDate->format('Y-m-d') . "\r\n";
	        CDashboardWeekBased::updateGuestMetrics($store_id, $curWeek, $curYear);
	        $weekDate->modify('+7 days');
	    }
	    
	}
	

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::CACHE_WEEKLY_METRICS, " $totalCount stores weekly metrics processed.");
}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::CACHE_WEEKLY_METRICS, "update_weekly_metrics: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>
