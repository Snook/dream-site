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

class processor_admin_sessionLead extends CPageProcessor
{

	function runFranchiseLead()
	{
		$this->setSessionLead();
	}
	function runOpsLead()
	{
		$this->setSessionLead();
	}
	function runFranchiseManager()
	{
		$this->setSessionLead();
	}

	function runFranchiseOwner()
	{
		$this->setSessionLead();
	}

	function runHomeOfficeStaff()
	{
		echo json_encode(array('processor_success' => false, 'processor_message' => 'No permission'));
	}

	function runHomeOfficeManager()
	{
		$this->setSessionLead();
	}

	function runSiteAdmin()
	{
		$this->setSessionLead();
	}


	function setSessionLead()
	{
		// Process post
		if (isset($_POST['user_id']) && is_numeric($_POST['user_id']) && isset($_POST['session_id']) && is_numeric($_POST['session_id']))
		{
			$this->updateSessionLead($_POST['user_id'], $_POST['session_id']);
		}
		else
		{
			echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Invalid parameter'
					));
		}
	}

	function updateSessionLead($lead_id, $session_id)
	{
		$sessionObj = DAO_CFactory::create('session');
		$sessionObj->id = $session_id;
		if (!$sessionObj->find(true))
		{
			echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Session not found'
			));
		}

		$old = clone($sessionObj);
		$sessionObj->session_lead = ($lead_id != 0 ? $lead_id : 'null');
		if ($sessionObj->update($old) === false)
		{
			echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'An error occurred updating the session lead'
			));
		}
		else
		{
			echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'The session lead was updated'
			));
		}


	}

}
?>