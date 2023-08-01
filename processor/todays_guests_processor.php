<?php
/*
 * Created on August 08, 2011
 * project_name processor_cart_session_processor
 *
 * Copyright 2011 DreamDinners
 * @author CarlS
 */

require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");


class processor_todays_guests_processor extends CPageProcessor
{

	function runPublic()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		$jsonData =  json_encode(array('processor_success' => false,  'result_code' => 1005,  'processor_message' => 'No permission.'));
	}


	function runFranchiseOwner()
	{

		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');


		$returnArray = array();



		if (isset($_REQUEST['store_id']) && is_numeric($_REQUEST['store_id']))
		{
			$store_id = $_REQUEST['store_id'];
			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->id = $store_id;
			$StoreObj->find(true);
			// Get adjusted date for store timezone
			$todays_date = date("Y-m-d", CTimezones::getAdjustedServerTime($StoreObj));

			$fieldlist = "user.id, is_partial_account, user.primary_email, user.firstname, user.lastname, user.telephone_1, user.telephone_2, user.telephone_1_call_time, address.address_line1, address.address_line2,  address.city, address.state_id, address.postal_code";
			$User = DAO_CFactory::create('user');
			$User->query("SELECT
					" . $fieldlist . "
					FROM `session` AS `s`
					INNER JOIN `store` AS `st` ON `s`.`store_id` = `st`.`id`
					INNER JOIN `booking` ON `s`.`id` = `booking`.`session_id`
					INNER JOIN `user` ON `booking`.`user_id` = `user`.`id`
					INNER JOIN `address` ON `address`.`user_id` = `user`.`id`
					WHERE booking.is_deleted = 0
					AND `booking`.status = 'ACTIVE'
					AND s.session_start LIKE '2012-01-12%'
					AND s.store_id = '" . $store_id . "'
					AND user.id != '1'
					ORDER BY user.lastname, user.firstname ASC");

			$rowcount = $User->N;

			if ( $rowcount > 10000 )
			{
				echo json_encode(array('processor_success' => false,  'result_code' => 1002,  'processor_message' => 'Too many results..'));
				exit;
			}

			while ($User->fetch())
			{
				$returnArray[] = array('user_id' => '$User->id', 'name' => $User->firstname . " " . $User->lastname, 'primary_email' => $User->primary_email, 'dream_reward_level' => '1');

			}

		//	$userList = json_encode($returnArray);
			//$jsonData = json_encode(array('data' => $returnArray, ' processor_success' => true,  'result_code' => 1001,  'processor_message' => 'good for you.'));

			$packaging = 1;

			if (isset($_REQUEST['p']))
				$packaging = $_REQUEST['p'];

			$payload = null;
			switch($packaging)
			{
				case 1:
					$payload = array($returnArray);
					break;
				case 2:
					$payload = array('dd_account_list' => $returnArray);
					break;
				case 3:
					$payload = $returnArray;
					break;

			}

			$jsonData = json_encode($payload);
			$error = json_last_error();
			echo $jsonData;
			exit;
		}
		else
		{
			$jsonData =  json_encode(array('processor_success' => false,  'result_code' => 1001,  'processor_message' => 'The store id is invalid.'));
			$error = json_last_error();
			echo $jsonData;
			exit;
		}
	}

}
?>