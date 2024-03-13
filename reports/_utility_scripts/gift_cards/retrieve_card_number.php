<?php
require_once(dirname(__FILE__) . "/../../../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("CLog.inc");

$OrderID = 20465;


 try {
        $acct_retreive = DAO_CFactory::create('gift_card_order');
        $acct_retreive->query("SET @GCAN = ''");
        $acct_retreive->query("Call get_gcan({$OrderID}, @GCAN)");
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