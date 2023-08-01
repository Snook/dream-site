<?php

/**
 *
 *
 * @version   $Id$
 * @copyright 2007
 */
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");

class processor_admin_calendarProcessorDelivered extends CPageProcessor
{

	static $sessionArray = array();

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

		$storeID = null;
		if (isset($_REQUEST['store_id']))
		{
			$storeID = $_REQUEST['store_id'];
		}

		if (!$storeID || !is_numeric($storeID))
		{
			return "error";
		}
		else
		{
			$daoStore = DAO_CFactory::create('store');
			$daoStore->id = $storeID;
			$daoStore->find(true);
		}

		if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'retrieve_new' || $_REQUEST['action'] == 'retrieve_for_reschedule' ))
		{

			$orgSessionID = false;
			if (!empty($_REQUEST['cur_session_id']) && is_numeric($_REQUEST['service_days']))
			{
				$orgSessionID = $_REQUEST['cur_session_id'];
			}

			$service_days = 2;
			if (!empty($_REQUEST['service_days']) && is_numeric($_REQUEST['service_days']))
			{
				$service_days = $_REQUEST['service_days'];
			}


			$sessionsArray = CSession::getCurrentDeliveredSessionArrayForDistributionCenter($daoStore, $service_days, false);
			$tpl = new CTemplate();

			$tpl->assign('sessions', $sessionsArray);
			$tpl->assign('orgSessionID', $orgSessionID);


			$data = $tpl->fetch('admin/subtemplate/order_mgr/delivery_date_selector.tpl.php');

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Month retrieval Success.',
				'data' => $data
			));


		}

	}

}

?>