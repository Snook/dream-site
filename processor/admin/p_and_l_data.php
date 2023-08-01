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

class processor_admin_p_and_l_data extends CPageProcessor
{

	private $date;
	
	function runFranchiseManager()
	{
		$this->updateData();
	}
	
	function runOpsLead()
	{
		$this->updateData();
	}

	function runFranchiseOwner()
	{
		$this->updateData();
	}

	function runHomeOfficeStaff()
	{
		echo json_encode(array('processor_success' => false, 'processor_message' => 'No permission'));
	}

	function runHomeOfficeManager()
	{
		$this->updateData();
	}

	function runSiteAdmin()
	{
		$this->updateData();
	}


	function updateData()
	{

        CTemplate::noCache();

		// Process post
		if (isset($_POST['store_id']) && is_numeric($_POST['store_id']) &&
			isset($_POST['month']) && is_numeric($_POST['month']) &&
			isset($_POST['year']) && is_numeric($_POST['year']) && isset($_POST['p_and_l_data']))
		{
			if (empty($_POST['p_and_l_data']) || !is_array($_POST['p_and_l_data']))
			{

				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'There is a problem with the submitted data.'
				));

				exit;
			}


            $financialsObj = DAO_CFactory::create('store_monthly_profit_and_loss');


            $financialsObj->date = date("Y-m-d", mktime(0,0,0,$_POST['month'], 1, $_POST['year']));
            $financialsObj->store_id = $_POST['store_id'];

            $doUdpate =  false;
            $clone = null;
            if ($financialsObj->find(true))
            {
                $doUdpate = true;
                $clone = clone($financialsObj);
            }

            foreach($_POST['p_and_l_data'] as $k => $v)
            {
                if ($v === "" || $v === "not supplied")
                {
                    $v = 'null';
                }
                else if (!is_numeric($v))
                {
                    echo json_encode(array(
                        'processor_success' => false,
                        'processor_message' => 'A submitted value was non-numeric.'
                    ));

                    exit;
                }

                $financialsObj->$k = $v;
            }

            if ($doUdpate)
            {
                $financialsObj->update($clone);
            }
            else
            {
                $financialsObj->insert();
            }

            echo json_encode(array(
                'processor_success' => true,
                'processor_message' => 'The data was updated.'
            ));
            exit;

		}
		else
		{
			echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Invalid parameter'
					));
			exit;
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
				exit;
				
			}
			else
			{
				echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The monthly goal was updated'));
				exit;
				
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
				exit;
				
			}
			else
			{
				echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error occurred updating the monthly goal.'));
				exit;
				
			}
		}


	}


}
?>