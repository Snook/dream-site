<?php
require_once(dirname(__FILE__) . "/../../../includes/Config.inc");
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
	 print_r($voidResult);

	} catch (exception $e) {
		echo "Card Load failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}


?>