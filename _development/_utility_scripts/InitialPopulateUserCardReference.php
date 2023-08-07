<?php
/*
 * Created on Dec 8, 2005
 *
 * Copyright 2013 DreamDinners
 * @author Carls
 */
//require_once("c:\wamp\www\DreamPay\includes\Config.inc");
//require_once("C:\Development\Sites\DreamSite\includes\Config.inc");
require_once("/DreamSite/includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CPayment.php");
require_once("DAO/BusinessObject/CPointsUserHistory.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once("CMailHandlers.inc");


ini_set('memory_limit','-1');
set_time_limit(3600 * 24);




$masterArray = array();
try {
   // $path = "/DreamDigest/Mayo-user monthly revenue.csv";
   // $path = "C:\Development\Sites\DreamSite\card_ref_population_record.csv";
   // $fh = fopen($path, 'w');

	echo "starting ...\r\n";

    $stores = new DAO();
    $stores->query("select id, store_name from store where active = 1");

	echo "{$stores->N} active stores  ...\r\n";

	while($stores->fetch())
	{
		echo "processing {$stores->store_name} ({$stores->id}) ...\r\n";

		$count = 0;
		$payers = new DAO();
		$payers->query("select distinct user_id from payment where timestamp_created > '2020-08-01 00:00:00' and is_deleted = 0 and store_id = {$stores->id}");
		echo "{$payers->N} possible guests  ...\r\n";

		while ($payers->fetch())
		{
			$refs = CPayment::getPaymentEligibleForReference($payers->user_id, $stores->id);

			if (!empty($refs))
			{

				$count++;
				foreach ($refs as $payment)
				{
					$UCF = DAO_CFactory::create('user_card_reference');
					$UCF->user_id = $payers->user_id;
					$UCF->store_id = $stores->id;
					$UCF->merchant_account_id = $payment['maid'];
					$UCF->credit_card_type = $payment['card_type'];
					$UCF->card_number = $payment['cc_number'];
					$UCF->card_transaction_number = $payment['payment_id'];
					$UCF->last_process_date = date("Y-m-d H:i:s",$payment['date']);
					$UCF->last_process_result = 0;

					$UCF->insert();
				}
			}
		}

		echo "Found $count guests with referencable transactions\r\n";
	}
  //  fclose($fh);
    
}
catch (exception $e)
{
    CLog::RecordException($e);
}

?>