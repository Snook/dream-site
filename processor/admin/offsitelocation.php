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
				$Store = DAO_CFactory::create('store');
				$Store->id = $_REQUEST['store_id'];
				$Store->find(true);

				$pickupLocation = COffsitelocation::addUpdatePickupLocation($Store, $_REQUEST['data']);

				if (!empty($pickupLocation))
				{
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Offsite Location Added'
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Offsite Location Not Added'
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Offsite Store id was not supplied'
				));
				exit;
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'add_offsite_info' && $_POST['data']['edit_location'] != 'false')
		{
			if (!empty($_REQUEST['store_id']))
			{
				$Store = DAO_CFactory::create('store');
				$Store->id = $_REQUEST['store_id'];
				$Store->find(true);

				$pickupLocation = COffsitelocation::addUpdatePickupLocation($Store, $_REQUEST['data'], $_POST['data']['edit_location']);

				if (!empty($pickupLocation))
				{
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Offsite info edited',
						'offsite_id' => $pickupLocation->id,
						'data' => json_encode($pickupLocation)
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Offsite info not found'
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Store id was not supplied'
				));
				exit;
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_offsite_info')
		{
			if (!empty($_REQUEST['offsite_id']))
			{
				$Offsite = DAO_CFactory::create('store_pickup_location');
				$Offsite->id = $_REQUEST['offsite_id'];

				if ($Offsite->find(true))
				{
					$Offsite->contact = '';

					if (!empty($Offsite->contact_user_id))
					{
						$contactUser = DAO_CFactory::create('user');
						$contactUser->id = $Offsite->contact_user_id;
						$contactUser->find(true);

						$Offsite->contact = $contactUser->primary_email;
					}

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Community Pick Up Location info edited',
						'data' => json_encode($Offsite)
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Community Pick Up Location info not found'
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Store id was not supplied'
				));
				exit;
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'toggle_offsitelocation')
		{
			if (!empty($_REQUEST['location_id']))
			{
				$location = DAO_CFactory::create('store_pickup_location');
				$location->id = $_REQUEST['location_id'];
				$location->find(true);

				if (!empty($location->active))
				{
					$location->active = 0;
					$location->update();

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Location disabled',
						'fundraiser_id' => $location->id,
						'dd_toasts' => array(
							array('message' => 'Location disabled.')
						)
					));
					exit;
				}
				else
				{
					$location->active = 1;
					$location->update();

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Location enabled',
						'fundraiser_id' => $location->id,
						'dd_toasts' => array(
							array('message' => 'Location enabled.')
						)
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Location id was not supplied'
				));
				exit;
			}
		}

	}
}

?>