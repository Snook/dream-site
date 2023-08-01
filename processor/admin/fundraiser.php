<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('payment/PayPalProcess.php');
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CStore.php');
require_once('includes/DAO/BusinessObject/CFundraiser.php');
require_once('page/admin/fundraiser.php');

class processor_admin_fundraiser extends CPageProcessor
{

    function __construct()
    {
        $this->doGenericInputCleaning = false;
    }

	function runSiteAdmin()
	{
		$this->runFundraiser();
	}

	function runFranchiseStaff()
	{
		$this->runFundraiser();
	}

	function runFranchiseManager()
	{
		$this->runFundraiser();
	}

	function runOpsLead()
	{
		$this->runFundraiser();
	}

	function runFranchiseLead()
	{
		$this->runFundraiser();
	}

	function runEventCoordinator()
	{
		$this->runFundraiser();
	}

	function runHomeOfficeManager()
	{
		$this->runFundraiser();
	}

	function runFranchiseOwner()
	{
		$this->runFundraiser();
	}

	function runFundraiser()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'add_fund_info')
		{
			if ($_REQUEST['store_id'])
			{
				$Store = DAO_CFactory::create('store');
				$Store->id = $_REQUEST['store_id'];
				$Store->find(true);

				list ($fundraiser_id, $fundraiserArray) = CFundraiser::addFundraiser($Store, $_REQUEST['data']['title'], $_REQUEST['data']['description'], $_REQUEST['data']['value']);

				if (!empty($fundraiserArray))
				{
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Fundraiser info found',
						'fundraiser_id' => $fundraiser_id,
						'data' => json_encode($fundraiserArray)
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Fundraiser info not found'
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Fundraiser id was not supplied'
				));
				exit;
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'toggle_fundraiser')
		{
			if (!empty($_REQUEST['fund_id']))
			{
				$fundraiser = DAO_CFactory::create('store_to_fundraiser');
				$fundraiser->id = $_REQUEST['fund_id'];
				$fundraiser->find(true);

				if (!empty($fundraiser->active))
				{
					$fundraiser->active = 0;
					$fundraiser->update();

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Fundraiser disabled',
						'fundraiser_id' => $fundraiser->id,
						'dd_toasts' => array(
							array('message' => 'Fundraiser disabled.')
						)
					));
					exit;
				}
				else
				{
					$fundraiser->active = 1;
					$fundraiser->update();

					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Fundraiser enabled',
						'fundraiser_id' => $fundraiser->id,
						'dd_toasts' => array(
							array('message' => 'Fundraiser enabled.')
						)
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Fundraiser id was not supplied'
				));
				exit;
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'edit_fund_info')
		{
			if (!empty($_REQUEST['store_id']))
			{
				$Store = DAO_CFactory::create('store');
				$Store->id = $_REQUEST['store_id'];
				$Store->find(true);

				list ($fundraiser_id, $fundraiserArray) = CFundraiser::editFundraiser($Store, $_REQUEST['data']['fund_id'], $_REQUEST['data']['title'], $_REQUEST['data']['description'], $_REQUEST['data']['value']);

				if (!empty($fundraiserArray))
				{
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Fundraiser info edited',
						'fundraiser_id' => $fundraiser_id,
						'data' => json_encode($fundraiserArray)
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Fundraiser info not found'
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

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_fund_info')
		{
			if ($_REQUEST['fund_id'] && $_REQUEST['store_id'])
			{
				$Store = DAO_CFactory::create('store');
				$Store->id = $_REQUEST['store_id'];
				$Store->find(true);

				$fundraiserArray = CFundraiser::storeFundraiserArray($Store, $_REQUEST['fund_id']);

				if (!empty($fundraiserArray))
				{
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'Fundraiser info found',
						'data' => json_encode($fundraiserArray[$_REQUEST['fund_id']])
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Fundraiser info not found'
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Fundraiser id was not supplied'
				));
				exit;
			}
		}
	}
}

?>