<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 // 1) Create new 3 servings intro items for the June Menu
 
//require_once("C:\wamp\www\DreamSite\includes\Config.inc");
require_once("/DreamSite/includes/Config.inc");

require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("CLog.inc");
 
$OrderID = 33847; 


 try {
        $acct_retreive = DAO_CFactory::create('gift_card_transaction');
        $acct_retreive->query("SET @GCAN = ''");
        $acct_retreive->query("Call get_gcan_trans({$OrderID}, @GCAN)");
        $acct_retreive->query("SELECT @GCAN");
        $acct_retreive->fetch();
        $account = base64_decode(CCrypto::decode($acct_retreive->_GCAN));
        echo $account . "\n";
 	
	} catch (exception $e) {
		echo "Card Retrieval failed: exception occurred<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}	

 
?>