<?php
/*
 * Created on August 08, 2011
 * project_name guestCarryoverNotes
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/Store_monthly_goals.php");

class processor_admin_sessionGoals extends CPageProcessor
{

	private $date;


	function runFranchiseLead()
	{
		$this->udpateGoals();
	}

	function runFranchiseManager()
	{
		$this->udpateGoals();
	}

	function runFranchiseOwner()
	{
		$this->udpateGoals();
	}
	function runOpsLead()
	{
		$this->udpateGoals();
	}
	
	function runHomeOfficeStaff()
	{
		echo json_encode(array('processor_success' => false, 'processor_message' => 'No permission'));
	}

	function runHomeOfficeManager()
	{
		$this->udpateGoals();
	}

	function runSiteAdmin()
	{
		$this->udpateGoals();
	}


	function udpateGoals()
	{
		// Process post
		if (isset($_POST['store_id']) && is_numeric($_POST['store_id']) &&
			isset($_POST['month']) && is_numeric($_POST['month']) &&
			isset($_POST['year']) && is_numeric($_POST['year']) && isset($_POST['goal']))
		{
			if (empty($_POST['value']) || !is_numeric($_POST['value']))
			{

				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'The value is 0 or is non-numeric. '
				));

				exit;
			}

			$this->date = date("Y-m-d", mktime(0,0,0,$_POST['month'], 1, $_POST['year']));


			switch($_POST['goal'])
			{
				case 'gross_revenue_goal':
					$this->updateGoal('gross_revenue_goal');
					break;
				case 'avg_ticket_goal':
					$this->updateGoal('average_ticket_goal');
					break;
				case 'finishing_touch_goal':
					$this->updateGoal('finishing_touch_revenue_goal');
					break;
				case 'taste_sessions_goal':
					$this->updateGoal('taste_sessions_goal');
					break;
			    case 'taste_guest_count_goal':
				    $this->updateGoal('taste_guest_count_goal');
				    break;
				case 'regular_guest_count_goal':
				    $this->updateGoal('regular_guest_count_goal');
				    break;
				case 'intro_guest_count_goal':
				    $this->updateGoal('intro_guest_count_goal');
				    break;
				default:
					echo json_encode(array('processor_success' => false, 'processor_message' => 'Unknown goal parameter'));
					exit;
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

	function updateGoal($columnName)
	{

		$goalObj = DAO_CFactory::create('store_monthly_goals');
		$goalObj->store_id = $_POST['store_id'];
		$goalObj->date = $this->date;

		if ($goalObj->find(true))
		{
			$old = clone($goalObj);
			$goalObj->$columnName = $_POST['value'];
			if ($goalObj->update($old) === false)
			{
				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error occurred updating the monthly goal.'));
			}
			else
			{
				echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The monthly goal was updated'));
			}

		}
		else
		{
			$goalObj->$columnName = $_POST['value'];
			if ($goalObj->insert())
			{
				echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The monthly goal was inserted'
				));
			}
			else
			{
				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error occurred updating the monthly goal.'));
			}
		}


	}


}
?>