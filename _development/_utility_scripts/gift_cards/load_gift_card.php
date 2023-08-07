<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

//require_once("C:\wamp\www\DreamSite\includes\Config.inc");
require_once("/DreamSite/includes/Config.inc");

require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("DAO/BusinessObject/COrders.php");
require_once("CLog.inc");

$accountNumber = '711389104866435';
$trans_amount = 100;
$ccRef = "manual";

 try {	//static function loadDebitGiftCard($gift_card_number, $trans_amount, $ccRef, $POS_type='M', $store_id = false)
 	

        $result =  CGiftCard::loadDebitGiftCard($accountNumber, $trans_amount, $ccRef);

        if ($result)
			echo "load Successful!! " . $result .  "\n";
		else
			echo "load Failed\n";


	} catch (exception $e) {
		echo "Card Load failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}


?>
