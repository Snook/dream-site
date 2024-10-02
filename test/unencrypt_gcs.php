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
	ini_set('memory_limit','-1');

	$DAO_gift_card_transaction = DAO_CFactory::create('gift_card_transaction');

	$DAO_gift_card_transaction->query("select id, gift_card_account_number from gift_card_transaction WHERE NOT ISNULL(gift_card_account_number) AND (ISNULL(clear_card_number) or clear_card_number = '') order by id desc");

	$totalCount = 0;

	while ($DAO_gift_card_transaction->fetch())
	{
		if (!empty($DAO_gift_card_transaction->gift_card_account_number))
		{
			$totalCount++;

			$acct_retreive = DAO_CFactory::create('gift_card_transaction');
			$acct_retreive->query("SET @GCAN = ''");
			$acct_retreive->query("Call get_gcan_trans({$DAO_gift_card_transaction->id}, @GCAN)");
			$acct_retreive->query("SELECT @GCAN");
			$acct_retreive->fetch();
			$account = base64_decode(CCrypto::decode($acct_retreive->_GCAN));

			$acct_store = DAO_CFactory::create('gift_card_transaction');

			$acct_store->query("update gift_card_transaction set clear_card_number = '$account' where id = {$DAO_gift_card_transaction->id} ");
		}
	}

	echo $totalCount . "\r\n";

	CLog::Record("CRON: $totalCount cards processed.");
}
catch (exception $e)
{
	CLog::RecordException($e);
}