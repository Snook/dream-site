<?php
require_once(dirname(__FILE__) . "/../../../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("DAO/BusinessObject/COrders.php");
require_once("CLog.inc");

$accountNumber = '713891169152430';
$trans_amount = 100;
$transID = "manual";
$ccRef = "manual";

 try {

        $result =  CGiftCard::unloadDebitGiftCard($accountNumber, $transID, $trans_amount, $ccRef);

        if ($result)
			echo "Unload Successful!! " . $result .  "\n";
		else
			echo "Unload Failed\n";


	} catch (exception $e) {
		echo "Card Load failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}


?>