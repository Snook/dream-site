<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */

define ('DEV', false);

if (DEV)
{
	require_once("C:\Users\Carl.Samuelson\Zend\workspaces\DefaultWorkspace12\DreamSite\includes\Config.inc");
	
}
else
{
	require_once("../Config.inc");
}

require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");

function fatal_handler() {

    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;

    $error = error_get_last();

    if( $error !== NULL  && $error["type"] == 1) 
	{
        $errno   = $error["type"];
        $errstr  = $error["message"];
		

        echo "Fatal error: " . $errstr;
    }
}

register_shutdown_function( "fatal_handler" );


set_time_limit(7200);
ini_set('memory_limit','1012M');

try {
	
	if (isset($argv[2] ))
	{
		$curMonth = $argv[2];
		$monthArray = CUserData::monthArray();
		$monthName = $monthArray[$curMonth];
	}
	else 
	{
		return "bad month number";
	}
	
	if (isset($argv[1] ))
	{
		$curYear = $argv[1];
	}
	else
	{
		return "bad year number";
	}
	
	if (isset($argv[3] ))
	{
		$segmentNumber = $argv[3];
	}
	else 
	{
		$segmentNumber = 5;
	}
	
	$arr = array();
	

	if (false) //set to true to force memory exhaustion or timeout
	{
		ini_set('memory_limit','12M');
	//	set_time_limit(1);
		
		$count = 0;
		
		while($count++ < 100000000)
		{
			$count++;
			$alloc = new COrders();
			$arr[] = $alloc;
			sleep(1);
		}
	}	
	
	
	$segmentClause = "";
	
	if ($segmentNumber == 1)
	{
		$segmentClause = " and LEFT(u.lastname, 1) in ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H') ";
	}
	else if ($segmentNumber == 2)
	{
		$segmentClause = " and LEFT(u.lastname, 1) in ('I', 'J','K', 'L','M', 'N','O', 'P','Q', 'R') ";
	}
	else if ($segmentNumber == 3)
	{
		$segmentClause = " and LEFT(u.lastname, 1) in ('S', 'T','U', 'V','W', 'X', 'Y', 'Z') ";
	}
	else if ($segmentNumber == 4)
	{
		$segmentClause = " and LEFT(u.lastname, 1) not in ('A', 'B', 'C', 'D','E', 'F','G', 'H','I', 'J','K', 'L','M', 'N','O', 'P','Q', 'R','S', 'T','U', 'V','W', 'X', 'Y', 'Z') ";
	}
		
		
	$BirthdayBoys = DAO_CFactory::create('user_data');

	$BirthdayBoys->query("select u.lastname,ud.user_id, ud.user_data_value, puh.id as puh_id from user_data ud
				join user u on u.id = ud.user_id and u.dream_rewards_version = 3 and (u.dream_reward_status = 1 or u.dream_reward_status = 3) and u.is_deleted = 0 $segmentClause
				left join points_user_history puh on puh.user_id = ud.user_id and puh.event_type = 'BIRTHDAY_MONTH' and puh.json_meta like '%$curYear%' and puh.is_deleted = 0
				where ud.user_data_field_id = 1 and (ud.user_data_value = '$curMonth' or ud.user_data_value = '$monthName') and ud.is_deleted = 0");

	$totalCount = 0;

	while ( $BirthdayBoys->fetch() ) {

		if (empty($BirthdayBoys->puh_id))
		{
			if (DEV)
			{
				echo "Would have awarded : " . $BirthdayBoys->lastname . "\r\n";
			}
			else 
			{
				CPointsUserHistory::handleEvent($BirthdayBoys->user_id, CPointsUserHistory::BIRTHDAY_MONTH, array('comments' => 'Earned $' . CPointsUserHistory::$eventMetaData[CPointsUserHistory::BIRTHDAY_MONTH]['credit'] . ' birthday credit!', 'year' => $curYear, 'month' => $curMonth));
			}
			
			$totalCount++;
		}
	}
	
	echo "Success:" . $totalCount;
	
}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::BIRTHDAY_REWARDS, "pp_reward_birthday_credit: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
	
	return $e->getMessage();
}

?>