<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("../../../includes/Config.inc");

require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("DAO/BusinessObject/COrders.php");
require_once("CLog.inc");


$thisTransID = "61d25fef1O3BSU0T";
$AuthCode = "1";

 try {

        //$result =  CGiftCard::unloadDebitGiftCard($accountNumber, $transID, $trans_amount, $ccRef);
	 $voidResult = CGiftCard::voidTransaction($thisTransID, $AuthCode);
	 if ($voidResult)
	 {
	 	echo "success";
	 }
	 else
	 {
		 echo "failure";
	 }

	} catch (exception $e) {
		echo "Card Load failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}


?>