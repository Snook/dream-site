<?php
/*
 * Created on June 09, 2011
 * project_name processPointToTransaction.php
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");

class processor_admin_processPointToTransaction extends CPageProcessor
{

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
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

		if (isset($_REQUEST['order_number']) && is_numeric($_REQUEST['order_number']) && isset($_REQUEST['old_ref_number']) && isset($_REQUEST['new_ref_number']))
		{
			list($new_card_type, $new_card_number) = explode(" ", urldecode($_REQUEST['new_card_number']));

			try
			{
				$UpdatePayment = DAO_CFactory::create('payment');

				$UpdatePayment->query("SELECT payment.* FROM payment
					WHERE payment_transaction_number = '" . $_REQUEST['old_ref_number'] . "'
					AND order_id = '" . $_REQUEST['order_number'] . "'
					AND is_delayed_payment = '1' LIMIT 1");

				if ($UpdatePayment->fetch())
				{
					$UpdatePayment->payment_transaction_number = $_REQUEST['new_ref_number'];
					$UpdatePayment->credit_card_type = $new_card_type;
					$UpdatePayment->payment_number = 'XXXXXXXXXXXX' . $new_card_number;

					if($UpdatePayment->update())
					{
						CUserHistory::recordUserEvent(CUser::getCurrentUser()->id, $_REQUEST['store_id'], $_REQUEST['order_number'], '500', 'null', 'null', 'Changed payment_transaction_number from ' . $_REQUEST['old_ref_number'] . ' to ' . $_REQUEST['new_ref_number']);

						echo json_encode(array(
							'success' => true,
							'message' => 'Success.',
							'new_card_type' => $new_card_type,
							'new_card_number' => $new_card_number,
							'transactionid' => $_REQUEST['new_ref_number'],
							'payment_id' => $UpdatePayment->id,
						));
					}
					else
					{
						echo json_encode(array('success' => false, 'message' => 'Unexpected error.'));
					}
				}
				else
				{
					echo json_encode(array('success' => false, 'message' => 'Delayed payment not found.'));
				}
			}
			catch (exception $e)
			{
				$logMess = "Exception pointing " . $_REQUEST['new_ref_number']. " to order id " . $_REQUEST['order_number'];
				CLog::Record($logMess);
				CLog::RecordException($e);

				echo json_encode(array('success' => false, 'message' => 'Unexpected error.'));
			}
		}
	}
};
?>