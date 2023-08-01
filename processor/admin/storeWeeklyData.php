<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/Store_weekly_data.php");

class processor_admin_storeWeeklyData extends CPageProcessor
{

	function runFranchiseLead()
	{
		$this->udpate();
	}

	function runOpsLead()
	{
		$this->udpate();
	}
	
	function runFranchiseManager()
	{
		$this->udpate();
	}

	function runFranchiseOwner()
	{
		$this->udpate();
	}

	function runHomeOfficeStaff()
	{
		echo json_encode(array('processor_success' => false, 'processor_message' => 'No permission'));
	}

	function runHomeOfficeManager()
	{
		$this->udpate();
	}

	function runSiteAdmin()
	{
		$this->udpate();
	}


	function udpate()
	{
		// Process post
		if (isset($_POST['store_id']) && is_numeric($_POST['store_id']) &&
			isset($_POST['week']) && is_numeric($_POST['week']) &&
			isset($_POST['year']) && is_numeric($_POST['year']) &&
			(isset($_POST['op'])))
		{
			switch($_POST['op'])
			{
				case 'updateFoodCosts':
					$this->updateFoodCosts();
					break;
				case 'updateLaborCosts':
					$this->updateLaborCosts();
					break;
				default:

					echo json_encode(array(
							'processor_success' => false,
							'processor_message' => 'Invalid operation parameter'
					));
			}
		}
		else
		{
			echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Invalid parameter'
					));
		}
	}

	function updateFoodCosts()
	{

		if (!isset($_POST['food_costs']) || !is_numeric($_POST['food_costs']))
		{

			if (!isset($_POST['food_costs']))
			{
				CLog::Record("Food cost param was null.");
			}
			else
			{
				CLog::Record("Food cost param was not numeric: " . $_POST['food_costs']);
			}

			echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The food cost parameter is missing or corrupt.'));

			exit;
		}


		$dataObj = DAO_CFactory::create('store_weekly_data');
		$dataObj->store_id = $_POST['store_id'];
		$dataObj->year = $_POST['year'];
		$dataObj->week = $_POST['week'];

		if ($dataObj->find(true))
		{
			$old = clone($dataObj);
			$dataObj->food_costs = $_POST['food_costs'];
			if ($dataObj->update($old) === false)
			{
				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error occurred updating the food costs.'));
			}
			else
			{
				echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The food cost was updated'));
			}

		}
		else
		{
			$dataObj->food_costs = $_POST['food_costs'];
			if ($dataObj->insert())
			{
				echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The food cost was inserted'
				));
			}
			else
			{
				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error occurred inserting the food cost.'));
			}
		}


	}

	function updateLaborCosts()
	{

		if (!isset($_POST['labor_costs']) || !is_numeric($_POST['labor_costs']))
		{
			echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The labor cost parameter is missing or corrupt.'));

			exit;
		}



		$dataObj = DAO_CFactory::create('store_weekly_data');
		$dataObj->store_id = $_POST['store_id'];
		$dataObj->year = $_POST['year'];
		$dataObj->week = $_POST['week'];

		if ($dataObj->find(true))
		{
			$old = clone($dataObj);
			$dataObj->labor_costs = $_POST['labor_costs'];
			if ($dataObj->update($old) === false)
			{
				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error occurred updating the labor costs.'));
			}
			else
			{
				echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The labor cost was updated'));
			}

		}
		else
		{
			$dataObj->food_costs = $_POST['labor_costs'];
			if ($dataObj->insert())
			{
				echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The labor cost was inserted'
				));
			}
			else
			{
				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error occurred inserting the labor cost.'));
			}
		}


	}

}
?>