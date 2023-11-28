<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CApp.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

ini_set('memory_limit','-1');

restore_error_handler();

try {

	if (defined("DISABLE_CRON") && DISABLE_CRON)
	{
		CLog::Record("CRON: process_delayed payments called but cron is disabled");
		CLog::RecordCronTask(1, CLog::FAILURE, CLog::DELAYED_PAYMENTS, "process_delayed called but cron is disabled.");
		exit;
	}

	// --------------------------------------------------------------------------------------------------------------------------
	// This new method uses the reference number from an initial deposit to pay the remainder. The query should only look
	// for orders placed since the new method was deployed (> 230012 on webdev - must update for live deployment)
	$NewPayment = DAO_CFactory::create('payment');

	if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
	{
		$NewPayment->query("SELECT payment.* FROM payment, booking, session
			WHERE payment.order_id = booking.order_id and booking.session_id = session.id and booking.status = 'ACTIVE'
			and DATEDIFF( now(), session.session_start ) >= -5 and DATEDIFF( now(), session.session_start ) < 14 and payment.is_delayed_payment = 1 and
			payment.delayed_payment_status = 'PENDING' and payment.is_migrated = 0 and  payment.order_id > 295123 limit 4");
	}
	else
	{
		$NewPayment->query("SELECT payment.* FROM payment, booking, session 
			WHERE payment.order_id = booking.order_id and booking.session_id = session.id and booking.status = 'ACTIVE'
			and DATEDIFF( now(), session.session_start ) >= -5 and DATEDIFF( now(), session.session_start ) < 14 and payment.is_delayed_payment = 1 and
			payment.delayed_payment_status = 'PENDING' and payment.is_migrated = 0 and  payment.order_id > 295123 and payment.total_amount > 0");
	}

	$successCount = 0;
	$totalCount = 0;

	while ( $NewPayment->fetch() )
	{

		try
		{
			if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
			{
				CLog::Record('CRON TEST: ' . print_r($NewPayment->toArray(), true));
				$successCount++;
			}
			else
			{
				if ($NewPayment->processDelayedPayment() == 'success') {
					$successCount++;
				}
			}
		}
		catch (exception $e)
		{
			$logMess = "CRON: Exception for payment id " . $NewPayment->id . " - " . $e->getMessage();
			CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::DELAYED_PAYMENTS, $logMess);
			CLog::RecordException($e);
		}

		$totalCount++;
	}

	$logMess = "$totalCount delayed payments (new method) processed of which " . ($totalCount - $successCount) ." failed (declined or erred).";
	CLog::RecordCronTask($totalCount, CLog::SUCCESS, CLog::DELAYED_PAYMENTS, $logMess);

}
catch (exception $e)
{
    CLog::RecordCronTask($totalCount, CLog::PARTIAL_FAILURE, CLog::DELAYED_PAYMENTS, "process_delayed: Exception occurred: " . $e->getMessage());
	CLog::RecordException($e);
}

?>