<?php
/*
 * Created on August 08, 2011
 * project_name guestCarryoverNotes
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CUserData.php");
class processor_admin_guestCarryoverNotes extends CPageProcessor
{

    function __construct()
    {
        $this->doGenericInputCleaning = false;
    }

	function runManufacturerStaff()
	{
		$this->guestNotes();
	}

	function runFranchiseStaff()
	{
		$this->guestNotes();
	}

	function runFranchiseLead()
	{
		$this->guestNotes();
	}

	function runFranchiseManager()
	{
		$this->guestNotes();
	}

	function runFranchiseOwner()
	{
		$this->guestNotes();
	}

	function runHomeOfficeStaff()
	{
		$this->guestNotes();
	}

	function runHomeOfficeManager()
	{
		$this->guestNotes();
	}

	function runEventCoordinator()
	{
		$this->guestNotes();
	}

	function runOpsLead()
	{
		$this->guestNotes();
	}

	function runOpsSupport()
	{
		$this->guestNotes();
	}

	function runSiteAdmin()
	{
		$this->guestNotes();
	}

	function guestNotes()
	{
		// Process post
		if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']))
		{
			$note = CUserData::filterUserCarryoverNote($_POST['note']);
			$user_id = $_POST['user_id'];
			$store_id = CBrowserSession::getCurrentFadminStore();

			$guestnote = CUserData::userCarryoverNote($user_id, $store_id, $note);

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Carryover note updated',
				'note' => $note,
				'dd_toasts' => array(
					array('message' => 'Carryover note updated.')
				)
			));
		}
	}
}
?>