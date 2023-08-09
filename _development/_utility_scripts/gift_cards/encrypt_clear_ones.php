<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
require_once("../../../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CUserData.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");


try {
	$path = "C:\\Users\\Carl.Samuelson\\Zend\workspaces\\DefaultWorkspace12\\DreamSite\\virtual_cards.csv";
	//$path = "/DreamSite/new_nums.csv";
	$fp = fopen($path, 'r');
	$totalCount = 0;

	while (!feof($fp))
	{
		$buffer = fgets($fp, 4096);

		if ($buffer === false)
		{
			echo "card encrypting ending: fgets returned no data - all is probabably ok<br>\n";
			break;
		}

		$valArr = explode(",", $buffer);

		$acct_check = DAO_CFactory::create('gift_card_order');
    	$acct_check->id = $valArr[0];
    	$acct_check->find(true);

		if (!empty($acct_check->clear_card_number) || !empty($acct_check->gift_card_account_number))
    	{
    		throw new Exception("Row {} has an existing GC number");
    	}

		$acct_check->query("update gift_card_order set clear_card_number = '{$valArr[1]}' where id = {$valArr[0]};");

		$acct_set = DAO_CFactory::create('gift_card_order');

		$message = CCrypto::encode(base64_encode($valArr[1]));
    	$acct_set->query("Call store_gcan('$message', {$valArr[0]})");
    	$totalCount++;
	}

	CLog::Record("CRON: $totalCount cards processed.");

}
catch (exception $e)
{
	CLog::RecordException($e);
}

?>