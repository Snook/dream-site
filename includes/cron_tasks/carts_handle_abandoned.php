<?php
/*
 * @author evanl
 */
require_once("../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("includes/api/marketing/salesforce/SalesForceMarketingManager.php");
require_once("DAO/CFactory.php");
require_once("CCart2.inc");
require_once("CLog.inc");

try {
	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: Handle Abandoned Carts called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::CLEAR_STALE_CARTS, "clear_stale_carts called but cron is disabled.");
		exit;
	}

	$cartRecords = CCartStorage::fetchAbandonedCartRows(10);
	$totalCount = count($cartRecords);
	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::MARK_CART_ABANDONED, $totalCount . " need to be sent to SalesForce.");

	if ($totalCount > 0)
	{
		foreach ($cartRecords as $cartData){
			//Need contact Email
			$result = SalesForceMarketingManager::getInstance()->fetchContactKeyByEmail($cartData['email']);


			if(!$result->isFailure()){
				$contactKey = $result->getPayload();
				if($contactKey != null){
					$result = SalesForceMarketingManager::getInstance()->invokeAbandonedCartJourney($contactKey,$cartData['email'],$cartData['first_name'],$cartData['cart_key']);
					if(!$result->isFailure()){
						//Mark record as sent
						CCartStorage::markAbandonedCartStatus($cartData['id'],'TRIGGERED_SALESFORCE');
					}else{
						CCartStorage::markAbandonedCartStatus($cartData['id'],'FAILED');
					}
				}
			}
			//dont spam
			sleep(15);
		}
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::MARK_CART_ABANDONED, $totalCount . " abandoned carts sent to SalesForce.");
}
catch (exception $e)
{
	CLog::RecordCronTask(0, CLog::PARTIAL_FAILURE, CLog::MARK_CART_ABANDONED, CLog::MARK_CART_ABANDONED. " - Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>