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

$trans_amount = 25;
$ccRef = "manual";
$to_name = "Jesse\'s House";
$recipient_email = "shellygates@jesseshouse.org";
$billing_email = "gates.shelly@gmail.com";
$OrderID = 17336;
$obfNum = "XXXXXXXXXXXX1189";
$refNum = "AY0A1E72E0E8";
$cardType = "Visa"; 
$nameOnCard = "Michelle Gates";
$billing_add = "2095 Peachree Road";
$billing_zip = "30041";

echo "Beginning to process\r\n";



 try {	
 	
 	$confirmNumber = COrders::generateConfirmationNum();
 	

 	$newAccountNumber = CGiftCard::obtainAccountNumberAndLoad($trans_amount, 'M', $to_name, $recipient_email);
 	
 	if ($newAccountNumber)
 	{
 		
 		echo "Success gettting number: $newAccountNumber \r\n";
 		
 		
 		$result = CGiftCard::completeNewAccountTransaction($OrderID, $billing_email, $obfNum, $refNum,
 			$cardType, $newAccountNumber, $nameOnCard, $billing_add,
 			$billing_zip, $confirmNumber, 'CUST_CART');
 		
 		if (!$result)
 		{
 			//handle this very nasty problem
 			echo "Error Completing order\r\n";
 			
 			CLog::RecordIntense("completeNewAccountTransaction Failure", 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
  		}
 		else 
 		{
 			echo "Success Completing order\r\n";
 			
 		}
 		
 	}
 	else
 	{
 		//handle this very nasty problem
 		CLog::RecordIntense("obtainAccountNumberAndLoad Failure", 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
 		echo "Error gettting number\r\n";
 		
 	}
 	
	} catch (exception $e) {
		echo "Card Load failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}


?>
