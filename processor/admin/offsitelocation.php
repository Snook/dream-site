<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('payment/PayPalProcess.php');
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CStore.php');
require_once('includes/DAO/BusinessObject/COffsitelocation.php');
require_once('page/admin/offsitelocations.php');
require_once 'DAO/BusinessObject/CStatesAndProvinces.php';

class processor_admin_offsitelocation extends CPageProcessor
{

	function __construct()
	{
		$this->doGenericInputCleaning = false;
	}

	function runSiteAdmin()
	{
		$this->runOffsiteLocation();
	}

	function runFranchiseStaff()
	{
		$this->runOffsiteLocation();
	}

	function runFranchiseManager()
	{
		$this->runOffsiteLocation();
	}

	function runOpsLead()
	{
		$this->runOffsiteLocation();
	}

	function runFranchiseLead()
	{
		$this->runOffsiteLocation();
	}

	function runEventCoordinator()
	{
		$this->runOffsiteLocation();
	}

	function runHomeOfficeManager()
	{
		$this->runOffsiteLocation();
	}

	function runFranchiseOwner()
	{
		$this->runOffsiteLocation();
	}

	function runOffsiteLocation()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'add_offsite_info' && $_POST['data']['edit_location'] == 'false')
		{
			if ($_REQUEST['store_id'])
			{
				$DAO_store = DAO_CFactory::create('store');
				$DAO_store->id = $_REQUEST['store_id'];
				$DAO_store->find(true);

				$pickupLocation = COffsitelocation::addUpdatePickupLocation($DAO_store, $_REQUEST['data']);

				if (!empty($pickupLocation))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Offsite Location Added'
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Offsite Location Not Added'
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Offsite Store id was not supplied'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'add_offsite_info' && $_POST['data']['edit_location'] != 'false')
		{
			if (!empty($_REQUEST['store_id']))
			{
				$DAO_store = DAO_CFactory::create('store', true);
				$DAO_store->id = $_REQUEST['store_id'];
				$DAO_store->find(true);

				$pickupLocation = COffsitelocation::addUpdatePickupLocation($DAO_store, $_REQUEST['data'], $_POST['data']['edit_location']);

				if (!empty($pickupLocation))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Offsite info edited',
						'offsite_id' => $pickupLocation->id,
						'data' => json_encode($pickupLocation)
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Offsite info not found'
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Store id was not supplied'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_offsite_info')
		{
			if (!empty($_REQUEST['offsite_id']))
			{
				$DAO_store_pickup_location = DAO_CFactory::create('store_pickup_location', true);
				$DAO_store_pickup_location->id = $_REQUEST['offsite_id'];

				if ($DAO_store_pickup_location->find(true))
				{
					$DAO_store_pickup_location->contact = '';

					if (!empty($DAO_store_pickup_location->contact_user_id))
					{
						$contactUser = DAO_CFactory::create('user');
						$contactUser->id = $DAO_store_pickup_location->contact_user_id;
						$contactUser->find(true);

						$DAO_store_pickup_location->contact = $contactUser->primary_email;
					}

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Community Pick Up Location info edited',
						'data' => json_encode($DAO_store_pickup_location)
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Community Pick Up Location info not found'
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Store id was not supplied'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'toggle_offsitelocation')
		{
			if (!empty($_REQUEST['location_id']))
			{
				$DAO_store_pickup_location = DAO_CFactory::create('store_pickup_location', true);
				$DAO_store_pickup_location->id = $_REQUEST['location_id'];
				$DAO_store_pickup_location->find(true);

				if ($DAO_store_pickup_location->is_Active())
				{
					$DAO_store_pickup_location->active = 0;
					$DAO_store_pickup_location->update();

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Location disabled',
						'fundraiser_id' => $DAO_store_pickup_location->id,
						'dd_toasts' => array(
							array('message' => 'Location disabled.')
						)
					));
				}
				else
				{
					$DAO_store_pickup_location->active = 1;
					$DAO_store_pickup_location->update();

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Location enabled',
						'fundraiser_id' => $DAO_store_pickup_location->id,
						'dd_toasts' => array(
							array('message' => 'Location enabled.')
						)
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Location id was not supplied'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'toggle_customer_visibility')
		{
			if (!empty($_REQUEST['location_id']))
			{
				$DAO_store_pickup_location = DAO_CFactory::create('store_pickup_location', true);
				$DAO_store_pickup_location->id = $_REQUEST['location_id'];
				$DAO_store_pickup_location->find(true);

				if ($DAO_store_pickup_location->is_ShowOnCustomerSite())
				{
					$DAO_store_pickup_location->show_on_customer_site = 0;
					$DAO_store_pickup_location->update();

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Location hidden from customers',
						'fundraiser_id' => $DAO_store_pickup_location->id,
						'dd_toasts' => array(
							array('message' => 'Location hidden from customers.')
						)
					));
				}
				else
				{
					$DAO_store_pickup_location->show_on_customer_site = 1;
					$DAO_store_pickup_location->update();

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Location visible to customers',
						'fundraiser_id' => $DAO_store_pickup_location->id,
						'dd_toasts' => array(
							array('message' => 'Location visible to customers.')
						)
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Location id was not supplied'
				));
			}
		}
	}
}

?>