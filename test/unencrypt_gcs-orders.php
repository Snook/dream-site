<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");

try
{
	$DAO_gift_card_order = DAO_CFactory::create('gift_card_order');

	$DAO_gift_card_order->query("select id, gift_card_account_number from gift_card_order WHERE NOT ISNULL(gift_card_account_number) AND (ISNULL(clear_card_number) or clear_card_number='') order by id desc");

	$totalCount = 0;

	while ($DAO_gift_card_order->fetch())
	{
		if (!empty($DAO_gift_card_order->gift_card_account_number))
		{
			$totalCount++;

			$acct_retrieve = DAO_CFactory::create('gift_card_order');
			$acct_retrieve->query("SET @GCAN = ''");
			$acct_retrieve->query("Call get_gcan({$DAO_gift_card_order->id}, @GCAN)");
			$acct_retrieve->query("SELECT @GCAN");
			$acct_retrieve->fetch();
			$account = base64_decode(CCrypto::decode($acct_retrieve->_GCAN));

			$acct_store = DAO_CFactory::create('gift_card_order');

			$acct_store->query("update gift_card_order set clear_card_number = '$account' where id = {$DAO_gift_card_order->id} ");
		}
	}

	echo $totalCount . "\r\n";

	CLog::Record("CRON: $totalCount cards processed.");
}
catch (exception $e)
{
	CLog::RecordException($e);
}