<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
require_once("../../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");
require_once('ExcelExport.inc');


ini_set('memory_limit','-1');
set_time_limit(3600 * 24);




try {
   $path = "/DreamSite/gifts..csv";
   //$path = "C:\\Development\\Sites\\DreamSite\\Recent_Scripts\\gifts.csv";
   $fh = fopen($path, 'w');

   $monthsArr = array ("7-2020" => 0, "8-2020" => 0,"9-2020" => 0,"10-2020" => 0,"11-2020" => 0,"12-2020" => 0,"1-2021" => 0,"2-2021" => 0,"3-2021" => 0, "3-2021" => 0, "4-2021" => 0);

    $gift_levels = array("member" => $monthsArr, "chef" => $monthsArr, "sous chef" => $monthsArr, "station chef" => $monthsArr, "head chef" => $monthsArr, "executive chef" => $monthsArr );

    $mainarray = array(30 => $gift_levels,61 => $gift_levels,80 => $gift_levels,159 => $gift_levels,200 => $gift_levels,244 => $gift_levels);

    $gifts = new DAO();

    $gifts->query("select u.home_store_id, st.store_name, st.city, st.state_id
            , CONCAT(MONTH(puh.timestamp_created),'-',YEAR(puh.timestamp_created)) as da_month
            , puh.json_meta
            , count(puh.id)  as total
            from user u
            join points_user_history puh on puh.user_id = u.id and puh.event_type in ('ACHIEVEMENT_AWARD') and puh.is_deleted = 0 and puh.timestamp_created > '2020-07-01 00:00:00' and puh.timestamp_created < '2021-05-01 00:00:00'
            and json_meta like '%rank\":1}%'
            join store st on st.id= u.home_store_id and st.active = 1 and st.id in (30,61,80,159,200,244)
            group by u.home_store_id, CONCAT(MONTH(puh.timestamp_created),'-',YEAR(puh.timestamp_created)), puh.json_meta
            order by u.home_store_id desc, puh.timestamp_created desc");


    $labels = array("ID", "Home Office Id", "Store Name", "City", "State", );


    while($gifts->fetch())
    {
        $gift_selector = "";
        if (strpos($gifts->json_meta, ", Chef!") !== false)
        {
            $gift_selector = "chef";
        }
        else if (strpos($gifts->json_meta, ", Station Chef!") !== false)
        {
            $gift_selector = "station chef";
        }
        else if (strpos($gifts->json_meta, ", Sous Chef!") !== false)
        {
            $gift_selector = "sous chef";
        }
        else if (strpos($gifts->json_meta, ", Head Chef!") !== false)
        {
            $gift_selector = "head chef";
        }
        else if (strpos($gifts->json_meta, ", Executive Chef!") !== false)
        {
            $gift_selector = "executive chef";
        }


        $mainarray[$gifts->home_store_id][$gift_selector][$gifts->da_month] = $gifts->total;
    }

    $gift2 = new DAO();
    $gift2->query("select u.home_store_id, st.store_name, st.city, st.state_id
                    , CONCAT(MONTH(puh.timestamp_created),'-',YEAR(puh.timestamp_created)) as da_month
                    , puh.json_meta
                    , count(puh.id)  as total
                    from user u
                    join points_user_history puh on puh.user_id = u.id and puh.event_type in ('OPT_IN') and puh.is_deleted = 0 and puh.timestamp_created > '2020-07-01 00:00:00' and puh.timestamp_created < '2021-05-01 00:00:00'
                    join store st on st.id= u.home_store_id and st.active = 1 and st.id in (30,61,80,159,200,244)
                    group by u.home_store_id, CONCAT(MONTH(puh.timestamp_created),'-',YEAR(puh.timestamp_created)), puh.json_meta
                    order by u.home_store_id desc, puh.timestamp_created desc");

	while($gift2->fetch())
    {
          $mainarray[$gift2->home_store_id]['member'][$gift2->da_month]= $gift2->total;
    }

    foreach($mainarray as $id => $data)
    {
        foreach($data as $level => $counts)
        {
            $length = fputs($fh, "$id,$level," . implode(",", $counts) . "\r\n");
        }
    }


    fclose($fh);
}
catch (exception $e)
{
    CLog::RecordException($e);
}

?>