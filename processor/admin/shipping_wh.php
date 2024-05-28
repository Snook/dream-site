<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CMail.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CUserData.php");
require_once("includes/DAO/BusinessObject/CFoodTesting.php");
require_once("page/admin/main.php");
require_once("page/admin/create_session.php");
require_once("processor/admin/processMetrics.php");

class processor_admin_shipping_wh extends CPageProcessor
{

	function runPublic()
	{
		$this->mainProcessor();
	}

	function runFranchiseStaff()
	{
		$this->mainProcessor();
	}

	function runFranchiseLead()
	{
		$this->mainProcessor();
	}

	function runEventCoordinator()
	{
		$this->mainProcessor();
	}

	function runOpsLead()
	{
		$this->mainProcessor();
	}

	function runOpsSupport()
	{
		$this->mainProcessor();
	}

	function runFranchiseManager()
	{
		$this->mainProcessor();
	}

	function runHomeOfficeManager()
	{
		$this->mainProcessor();
	}

	function runFranchiseOwner()
	{
		$this->mainProcessor();
	}

	function runSiteAdmin()
	{
		$this->mainProcessor();
	}

	function mainProcessor()
	{
		$rawJson = file_get_contents('php://input');
		CLog::RecordNew(CLog::NOTICE, $rawJson, "", "", true);

		$hashedDataRef = hash('md5', $rawJson, false);

		if (empty($rawJson))
		{
			CLog::RecordNew(CLog::ERROR, "No data was provided by in call to shipping webhook handler", "shipping_wh.php", 73, false);
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'No Data',
				'processor_name' => 'processor_shipping_wh'
			));
		}
		else
		{
			TransientDataStore::storeData(TransientDataStore::SHIPPING_SHIP_NOTIFICATION_NEW, $hashedDataRef, $rawJson, 50, true);

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Captured.',
				'processor_name' => 'processor_shipping_wh'
			));
		}
	}
}

?>