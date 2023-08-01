<?php
/*
 * Created on June 16, 2011
 * project_name delayedPaymentProcessor.php
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CPayment.php");

class processor_admin_delayedPaymentStatusProcessor extends CPageProcessor
{

	function __construct()
	{
		$this->doGenericInputCleaning = false;
	}

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

	function runHomeOfficeManager()
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


	function runFranchiseOwner()
	{
		header("Pragma: no-cache");
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (isset($_REQUEST['order_number']) && is_numeric($_REQUEST['order_number']) &&
			($_REQUEST['new_status'] == CPayment::PENDING || $_REQUEST['new_status'] == CPayment::FAIL || $_REQUEST['new_status'] == CPayment::CANCELLED))
		{
			try {

				$UpdatePayment = DAO_CFactory::create('payment');

				$orgStatus = CGPC::do_clean($_REQUEST['old_status'], TYPE_STR);
				$newStatus = CGPC::do_clean($_REQUEST['new_status'], TYPE_STR);


				$UpdatePayment->query("SELECT payment.* FROM payment, booking, session
					WHERE payment.order_id = booking.order_id
					AND booking.session_id = session.id
					AND booking.status = 'ACTIVE'
					AND payment.is_delayed_payment = 1
					AND payment.delayed_payment_status = '" . $orgStatus . "'
					AND payment.is_migrated = 0
					AND payment.order_id = '" . $_REQUEST['order_number'] . "'
					AND payment.total_amount > '0'
					LIMIT 1");

				if ($UpdatePayment->fetch())
				{
					$UpdatePayment->delayed_payment_status = $newStatus;
					if($newStatus == 'PENDING')
					{
						$UpdatePayment->delayed_payment_transaction_date = '';
					}

					if($UpdatePayment->update())
					{
						CUserHistory::recordUserEvent(CUser::getCurrentUser()->id, $_REQUEST['store_id'], $_REQUEST['order_number'], 501, 'null', 'null', 'Changed delayed_payment_status from ' . $orgStatus . ' to ' . $newStatus);

						echo json_encode(array(
							'success' => true,
							'message' => 'Success.',
							'status' => $newStatus,
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
				$logMess = 'Exception for payment id ' . $UpdatePayment->id;
				CLog::Record($logMess);
				CLog::RecordException($e);

				echo json_encode(array('success' => false, 'message' => 'Unexpected error.'));
			}
		}
		else
		{
			echo json_encode(array('success' => false, 'message' => 'Unexpected error.'));
		}
	}
}
?>