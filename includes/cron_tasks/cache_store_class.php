<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

function getClassV2FromAGR($revenue)
{
    if ($revenue > 50000)
    {
        return 1;
    }
    else if ($revenue > 30000)
    {
        return 2;
    }

    return 3;
}

function getClassFromAGR($revenue)
{

    if ($revenue >= 60000)
	{
        return 1;
    }
    else if ($revenue >= 45000)
    {
        return 2;
    }
    else if ($revenue >= 30000)
    {
        return 3;
    }

    return 4;
}



/*
 ALTER TABLE `dreamsite`.`store_class_cache`
ADD COLUMN `class_v2` tinyint(3) NULL AFTER `class`;
 */


try {

    $processed = 0;

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: cache_store_class called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::CACHE_STORE_CLASSES, "cache_store_class called but cron is disabled.");
		exit;
	}

	$curMonth = date("m");
   $curYear = date("Y");
   $lastYear = $curYear - 1;
   $lastMonth = date("Y-m-01", strtotime("$curYear-$curMonth-01"));
   $firstMonth = date("Y-m-01", strtotime("$lastYear-$curMonth-01"));

	$deleter = DAO_CFactory::create('store_class_cache');
   $deleter->query('delete from store_class_cache');

	$queryObj = DAO_CFactory::create('dashboard_metrics_agr');
   $queryObj->query("select dma.store_id, avg(dma.total_agr) as agr from dashboard_metrics_agr dma
                        join store st on st.id = dma.store_id and st.active = 1
                        where dma.date >= '$firstMonth' and dma.date < '$lastMonth' and dma.is_deleted = 0
                        group by dma.store_id");

	while($queryObj->fetch())
   {
       $inserter = DAO_CFactory::create('store_class_cache');

	   $class = getClassFromAGR($queryObj->agr);
       $classV2 = getClassV2FromAGR($queryObj->agr);
       $inserter->query("insert into store_class_cache (store_id, class, class_v2) values ({$queryObj->store_id}, $class, $classV2)");

	   $processed++;
   }

	CLog::RecordCronTask($processed, CLog::SUCCESS, CLog::CACHE_STORE_CLASSES, "Store class caching completed successfully.");
} catch (exception $e) {
	CLog::RecordCronTask($processed, CLog::PARTIAL_FAILURE, CLog::CACHE_STORE_CLASSES, "cache_store_class: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>