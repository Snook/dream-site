<?php
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");

class processor_admin_food_testing extends CPageProcessor
{

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}
	
	function runFranchiseManager()
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

	function runHomeOfficeStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'do_entree_receive')
		{

			$update = DAO_CFactory::create('food_testing_survey_submission');
			$update->id = $_REQUEST['survey_id'];

			if ($update->find())
			{
				$timestamp_received = CTemplate::unix_to_mysql_timestamp(time());

				$update->timestamp_received = $timestamp_received;
				$update->serving_size = $_REQUEST['serving_size'];
				$update->update();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Guest has received entree.',
					'survey_id' => $_REQUEST['survey_id'],
					'timestamp_received' => $timestamp_received,
					'dd_toasts' => array(
						array('message' => 'Guest has received entree.')
					)
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Guest not found.',
					'dd_toasts' => array(
						array('message' => 'Guest not found.')
					)
				));
			}

		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'do_close_survey')
		{
			$update = DAO_CFactory::create('food_testing');
			$update->id = $_REQUEST['survey_id'];

			$is_closed = 0;

			if ($_POST['is_closed'] == 'true')
			{
				$is_closed = 1;
			}

			if ($update->find())
			{
				$update->is_closed = $is_closed;
				$update->update();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Survey has been ' . (($is_closed == 1) ? 'closed' : 'opened') . '.',
					'survey_id' => $_REQUEST['survey_id'],
					'is_closed' => $is_closed,
					'dd_toasts' => array(
						array('message' => 'Survey has been ' . (($is_closed == 1) ? 'closed' : 'opened') . '.')
					)
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Store survey not found.',
					'dd_toasts' => array(
						array('message' => 'Store survey not found.')
					)
				));
			}

		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'do_store_paid')
		{

			$update = DAO_CFactory::create('food_testing_survey');
			$update->id = $_REQUEST['survey_id'];

			if ($update->find())
			{
				$timestamp_paid = CTemplate::unix_to_mysql_timestamp(time());

				$update->timestamp_paid = $timestamp_paid;
				$update->update();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Store has been marked as paid.',
					'survey_id' => $_REQUEST['survey_id'],
					'timestamp_paid' => $timestamp_paid,
					'dd_toasts' => array(
						array('message' => 'Store has been marked as paid.')
					)
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Store survey not found.',
					'dd_toasts' => array(
						array('message' => 'Store survey not found.')
					)
				));
			}

		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'do_delete_guest')
		{

			$survey = DAO_CFactory::create('food_testing_survey_submission');
			$survey->id = $_REQUEST['survey_id'];

			if ($survey->find())
			{
				$survey->delete();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Guest survey deleted.',
					'survey_id' => $_REQUEST['survey_id'],
					'store_id' => $_REQUEST['store_id'],
					'dd_toasts' => array(
						array('message' => 'Guest survey deleted.')
					)
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Guest survey not found.',
					'dd_toasts' => array(
						array('message' => 'Guest survey not found.')
					)
				));
			}

		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'do_received_w9')
		{

			$update = DAO_CFactory::create('store');
			$update->id = $_REQUEST['store_id'];

			if ($update->find())
			{
				$update->food_testing_w9 = 1;
				$update->update();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Store has W-9 on file.',
					'survey_id' => $_REQUEST['survey_id'],
					'store_id' => $_REQUEST['store_id'],
					'dd_toasts' => array(
						array('message' => 'Store has W-9 on file.')
					)
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Store not found.',
					'dd_toasts' => array(
						array('message' => 'Store not found.')
					)
				));
			}

		}

	}

}
?>