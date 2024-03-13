<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
//require_once("c:\wamp\www\DreamPay\includes\Config.inc");
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");

require_once("/DreamSite/includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");
require_once('ExcelExport.inc');


ini_set('memory_limit','-1');
set_time_limit(3600 * 24);

function makeWeekArray(&$retVal)
{

    for ($x = 13; $x < 53; $x++)
    {
        $retVal[$x ." 2020"] = 0;
    }

    for ($x = 1; $x < 18; $x++)
    {
        $retVal[$x ." 2021"] = 0;
    }

    return $retVal;
}

function makeLabels(&$labels)
{


    for ($x = 13; $x < 53; $x++)
    {
        $DAO = new DAO();
       // $DAO->query("select DATE_SUB(DATE_ADD(MAKEDATE(2020, 1), INTERVAL $x WEEK),INTERVAL WEEKDAY(DATE_ADD(MAKEDATE(2020, 1), INTERVAL $x WEEK)) -1 DAY) as a_date");
        $DAO->query("SELECT STR_TO_DATE('2020 $x Monday', '%X %V %W') as a_date");

        $DAO->fetch();
        $labels[] = $DAO->a_date;
    }

    for ($x = 1; $x < 18; $x++)
    {
        $DAO = new DAO();
       // $DAO->query("select DATE_SUB(DATE_ADD(MAKEDATE(2021, 1), INTERVAL $x WEEK),INTERVAL WEEKDAY(DATE_ADD(MAKEDATE(2021, 1), INTERVAL $x WEEK)) -1 DAY) as a_date");
        $DAO->query("SELECT STR_TO_DATE('2021 $x Monday', '%X %V %W') as a_date");
        $DAO->fetch();
        $labels[] = $DAO->a_date;
    }

    return $labels;

}

$storeArray = array();

try {
   $path = "/DreamSite/stores_closed.csv";
   //$path = "C:\\Development\\Sites\\DreamSite\\Recent_Scripts\\stores_closed.csv";
    $fh = fopen($path, 'w');
    
    
    $stores = new DAO();

    $stores->query("select s.store_id, st.home_office_id, st.store_name, st.city, st.state_id,  CONCAT(WEEK(s.session_start), ' ', YEAR(s.session_start)) as da_week, count(distinct b.order_id) as da_count from session s
                                        join booking b on b.session_id = s.id and b.status = 'ACTIVE'
                                        join store st on st.id = s.store_id
                                        where s.session_start > '2020-03-30 00:00:00' and s.session_start < '2021-05-02 00:00:00' 
                                        group by s.store_id, CONCAT(WEEK(s.session_start), ' ', YEAR(s.session_start))
                                        order by s.store_id, s.session_start");


    $labels = array("ID", "Home Office Id", "Store Name", "City", "State", "Count" );

    $labels = makeLabels($labels);


    while($stores->fetch())
    {
        if (!isset($storeArray[$stores->store_id]))
        {
            $storeArray[$stores->store_id][] = $stores->store_id;
            $storeArray[$stores->store_id][] = $stores->home_office_id;
            $storeArray[$stores->store_id][] = $stores->store_name;
            $storeArray[$stores->store_id][] = $stores->city;
            $storeArray[$stores->store_id][] = $stores->state_id;
            $storeArray[$stores->store_id]['count'] = '-';

            $storeArray[$stores->store_id] = makeWeekArray($storeArray[$stores->store_id]);

        }

        if ($stores->da_week == '0 2021')
        {
            $stores->da_week = '52 2020';
        }

        if (isset($storeArray[$stores->store_id][$stores->da_week]))
        {
            $storeArray[$stores->store_id][$stores->da_week] = $stores->da_count;
        }
        else
        {
            echo "ERROR: " . $stores->da_week;
        }
    }

    $length = fputs($fh, implode(",", $labels) . "\r\n");

    foreach($storeArray as  $data)
    {
        $count = 0;
        foreach($data as $col)
        {
            if ($col === 0)
            {
                $count++;
            }
        }

        $data['count'] = $count;

        $length = fputs($fh, implode(",", $data) . "\r\n");
    }

    fclose($fh);
    
}
catch (exception $e)
{
    CLog::RecordException($e);
}

?>