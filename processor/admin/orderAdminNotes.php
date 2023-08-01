<?php
/*
 * Created on October 26, 2011
 * project_name orderAdminNotes
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");

class processor_admin_orderAdminNotes extends CPageProcessor
{

	function runFranchiseStaff()
	{
		$this->adminNotes();
	}

	function runFranchiseLead()
	{
		$this->adminNotes();
	}

	function runEventCoordinator()
	{
		$this->adminNotes();
	}

	function runOpsLead()
	{
		$this->adminNotes();
	}

	function runOpsSupport()
	{
		$this->adminNotes();
	}

	function runFranchiseManager()
	{
		$this->adminNotes();
	}

	function runFranchiseOwner()
	{
		$this->adminNotes();
	}

	function runHomeOfficeStaff()
	{
		$this->adminNotes();
	}

	function runHomeOfficeManager()
	{
		$this->adminNotes();
	}

	function runSiteAdmin()
	{
		$this->adminNotes();
	}

	function adminNotes()
	{
		// Process post
		if (isset($_POST['order_id']) && is_numeric($_POST['order_id']) && isset($_POST['note']))
		{
			self::updateAdminNote($_POST['order_id'], $_POST['note']);
		}
	}

	static function loadAdminNote($order_id)
	{
		$ordernote = DAO_CFactory::create('orders');
		$ordernote->id = $order_id;
		$ordernote->find(true);
		return str_replace(array("\r", "\r\n", "\n"), ' ', strip_tags($ordernote->order_admin_notes));
	}

	function updateAdminNote($order_id, $note)
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$ordernote = DAO_CFactory::create('orders');
		$ordernote->id = $order_id;

		$note = str_replace(array("\r", "\r\n", "\n"), ' ', strip_tags($note));

		if ($ordernote->find(true))
		{
			$ordernote->order_admin_notes = $note;
			$ordernote->update();

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Admin note updated',
				'note' => $note,
				'dd_toasts' => array(
					array('message' => 'Admin note updated.')
				)
			));
		}
		else
		{
			$ordernote->order_admin_notes = $note;
			$ordernote->insert();

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Admin note created',
				'note' => $note,
				'dd_toasts' => array(
					array('message' => 'Admin note created.')
				)
			));
		}
	}
}
?>