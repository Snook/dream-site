<?php
/*
 * Created on Dec 8, 2005
 * project_name process_delayed.php
 *
 * Copyright 2005 DreamDinners
 * @author Carls
 */
require_once("../Config.inc");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

try {

    echo "beginning diagnotics ....\n";


    $pwFailHistory = DAO_CFactory::create('user_login_failures');
    $pwFailHistory->query("select ip_address, count(distinct id) as fail_count from user_login_failures where  TIMESTAMPDIFF(MINUTE, time_of_failure, NOW()) < 15 group by ip_address");

    $problem = false;

    while($pwFailHistory->fetch())
    {
        if ($pwFailHistory->fail_count >= 12)
        {
            CLog::RecordNew(CLog::SECURITY, "More than 12 login failures in 15 minutes from IP: {$pwFailHistory->ip_address}", "", "", true);
            echo "too many password failures from a single IP: {$pwFailHistory->ip_address}.\n";
            $problem = true;
        }
    }


    if(!$problem)
    {
        echo "All is well.\n";
    }

    echo "diagnotics complete.\n";


} catch (exception $e) {
	CLog::RecordException($e);
}

?>