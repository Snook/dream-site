<?php
/*
 * Created on July 26, 2011
 * project_name editCashCheckAmount
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");

class processor_admin_editCashCheckAmount extends CPageProcessor
{
	function __construct()
	{
		$this->inputTypeMap['new_total'] = TYPE_NUM;
		$this->inputTypeMap['new_number'] = TYPE_STR;
		$this->inputTypeMap['id'] = TYPE_INT;
		$this->inputTypeMap['type'] = TYPE_STR;
		$this->inputTypeMap['order_id'] = TYPE_INT;

	}

	function runFranchiseStaff()
	{
		$this->editCashCheck();
	}

	function runFranchiseLead()
	{
		$this->editCashCheck();
	}
	function runEventCoordinator()
	{
		$this->editCashCheck();
	}
	function runOpsLead()
	{
		$this->editCashCheck();
	}
	function runFranchiseManager()
	{
		$this->editCashCheck();
	}

	function runFranchiseOwner()
	{
		$this->editCashCheck();
	}

	function runHomeOfficeStaff()
	{
		$this->editCashCheck();
	}

	function runHomeOfficeManager()
	{
		$this->editCashCheck();
	}

	function runSiteAdmin()
	{
		$this->editCashCheck();
	}

	function editCashCheck()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (isset($_POST['new_total']) && isset($_POST['new_number']) && isset($_POST['id']) && is_numeric($_POST['id']) && ($_POST['type'] == 'check' || $_POST['type'] == 'cash'))
		{
			$CashCheckUpdate = DAO_CFactory::create('payment');
			$CashCheckUpdate->id = $_POST['id'];
			$CashCheckUpdate->find(true);
			$OrginalPayment = clone($CashCheckUpdate);
			$CashCheckUpdate->payment_type = strtoupper($_POST['type']);
			$updateLog = '';
			if($OrginalPayment->total_amount != $_POST['new_total'])
			{
				$CashCheckUpdate->total_amount = $_POST['new_total'];
				$updateLog .= ' total_amount from ' . $OrginalPayment->total_amount . ' to ' . $CashCheckUpdate->total_amount;
			}
			if($OrginalPayment->payment_number != $_POST['new_number'])
			{
				$CashCheckUpdate->payment_number = $_POST['new_number'];
				$updateLog .= ' payment_number from ' . $OrginalPayment->payment_number . ' to ' . $CashCheckUpdate->payment_number;
			}
			$CashCheckUpdate->update($OrginalPayment);


			if (isset($_POST['order_id']) && is_numeric($_POST['order_id']))
			{
				$orderObj = DAO_CFactory::create('orders');
				$orderObj->query("select grand_total from orders where id = {$_POST['order_id']}");
				$orderObj->fetch();
				$balanceDue = COrdersDigest::calculateAndAddBalanceDue($_POST['order_id'], $orderObj->grand_total);

				$ordersDigestObj = DAO_CFactory::create('orders_digest');
				$ordersDigestObj->query("update orders_digest set balance_due = $balanceDue where order_id = {$_POST['order_id']}");

			}

			CUserHistory::recordUserEvent(CUser::getCurrentUser()->id, $OrginalPayment->store_id, $OrginalPayment->order_id, '502', 'null', 'null', 'Changed edited_check_payment_details' . $updateLog);

			echo json_encode(array('processor_success' => true, 'processor_message' => '' . ucfirst($_POST['type']) . ' updated'));
		}
		else
		{
			echo json_encode(array('processor_success' => false, 'processor_message' => 'No session id'));
		}
	}
}

?>