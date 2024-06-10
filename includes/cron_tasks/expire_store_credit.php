<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CStoreCredit.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

if (defined("DISABLE_CRON") && DISABLE_CRON)
{
	CLog::Record("CRON: expire_store_credit called but cron is disabled");
	CLog::RecordCronTask(1, CLog::FAILURE, CLog::EXPIRE_STORE_CREDIT, "expire_store_credit called but cron is disabled.");
	exit;
}

$totalCount = 0;
try
{

	$storeCredits = DAO_CFactory::create('store_credit');

	$storeCredits->query("select sub_query.* 
		from (select sc.*, CONCAT(u.firstname, ' ', u.lastname) as daname, u.primary_email, s.email_address as store_email, s.store_name, if(sc.date_original_credit, sc.date_original_credit, sc.timestamp_created) as dadate 
			from store_credit sc 
				join user u on u.id = sc.user_id 
				join store s on s.id = sc.store_id 
			where sc.is_redeemed = 0 and sc.is_expired = 0 and sc.is_deleted = 0 and credit_type = 2) as sub_query 
		where DATEDIFF(NOW(), sub_query.dadate ) >= 90");

	// Note: exception are handled, logged but not rethrown in the send_reminder_email function
	while ($storeCredits->fetch())
	{
		$storeCredits->expire_credit();
		$totalCount++;
	}

	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::EXPIRE_STORE_CREDIT, "$totalCount store credits marked as expired.");
}
catch (exception $e)
{
	CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::EXPIRE_STORE_CREDIT, "expire_store_credit - expirations: Exception occurred: " . $e->getMessage);
	CLog::RecordException($e);
}

?>