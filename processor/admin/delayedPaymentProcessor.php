<?php
/*
 * Created on June 03, 2011
 * project_name delayedPaymentProcessor.php
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");

class processor_admin_delayedPaymentProcessor extends CPageProcessor
{

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (isset($_REQUEST['order_number']) && is_numeric($_REQUEST['order_number']))
		{
			try {

				$NewPayment = DAO_CFactory::create('payment');

				$NewPayment->query("SELECT payment.* FROM payment, booking, session
					WHERE payment.order_id = booking.order_id
					AND booking.session_id = session.id
					AND booking.status = 'ACTIVE'
					AND payment.is_delayed_payment = 1
					AND payment.delayed_payment_status = 'PENDING'
					AND payment.is_migrated = 0
					AND payment.order_id = '" . $_REQUEST['order_number'] . "'
					AND payment.total_amount > '0'
					LIMIT 1");

				if ($NewPayment->fetch())
				{
					$transaction = $NewPayment->processDelayedPayment($transactionDetails = true);

					if (is_array($transaction) && $transaction['success'] == true)
					{
						echo json_encode(array(
							'success' => true,
							'message' => 'Success.',
							'payment_id' => $transaction['payment_id'],
							'transaction_id' => $transaction['transaction_id'],
							'transaction_date' => CTemplate::dateTimeFormat($transaction['transaction_date'], NORMAL),
							'summary_date' => $transaction['summary_date'],
						));
					}
					else
					{
						echo json_encode(array('success' => false, 'message' => 'Unexpected error, ' . $transaction));
					}
				}
				else
				{
					echo json_encode(array('success' => false, 'message' => 'Delayed payment not found.'));
				}
			}
			catch (exception $e)
			{
				$logMess = "Exception for payment id " . $NewPayment->id;
				CLog::Record($logMess);
				CLog::RecordException($e);

				echo json_encode(array('success' => false, 'message' => 'Unexpected error, exception logged.'));
			}
		}
	}
}
?>