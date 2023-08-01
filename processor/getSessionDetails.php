<?php
/*
 * Created on June 23, 2011
 * project_name getSessionDetails
 *
 * Copyright 2011 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");

class processor_getSessionDetails extends CPageProcessor
{
	function runPublic()
	{
		$this->getSessionDetails();
	}

	function runCustomer()
	{
		$this->getSessionDetails();
	}

	function runFranchiseStaff()
	{
		$this->getSessionDetails();
	}

	function runFranchiseManager()
	{
		$this->getSessionDetails();
	}
	
	
	function runOpsLead()
	{
		$this->getSessionDetails();
	}

	function runFranchiseOwner()
	{
		$this->getSessionDetails();
	}

	function runHomeOfficeStaff()
	{
		$this->getSessionDetails();
	}

	function runHomeOfficeManager()
	{
		$this->getSessionDetails();
	}

	function runSiteAdmin()
	{
		$this->getSessionDetails();
	}

	function getSessionDetails()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (isset($_POST['session_id']) && is_numeric($_POST['session_id']))
		{
			$session = DAO_CFactory::create('session');

			$session->query("SELECT s.id as session_id, s.*, st.address_line1, st.address_line2, st.city, st.state_id, st.postal_code, st.store_name, st.id AS store_id,
				st.telephone_day FROM session s JOIN store st ON s.store_id = st.id WHERE s.id = '" . $_POST['session_id'] . "' AND s.is_deleted = 0");

			if ($session->fetch())
			{
				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->id = $session->store_id;
				$StoreObj->find(true);
				if ($session->isStandardSessionValid($StoreObj))
				{
					$sessionArray = array();

					// Defaults
					$sessionArray['is_private'] = false;
					$sessionArray['password'] = '';

					if($session->session_password)
					{
						$sessionArray['is_private'] = true;

						if (CUser::getCurrentUser()->user_type != 'CUSTOMER')
						{
							$sessionArray['password'] = $session->session_password;
						}
					}

					$sessionArray['processor_success'] = true;
					$sessionArray['processor_message'] = 'Success';
					$sessionArray['session_id'] = $session->session_id;
					$sessionArray['session_type'] = $session->session_type;
					$sessionArray['session_class'] = $session->session_class;
					$sessionArray['session_start'] = $session->session_start;
					$sessionArray['session_start_no_year'] = CTemplate::dateTimeFormat($session->session_start, VERBOSE_DATE_NO_YEAR_W_COMMA);
					$sessionArray['session_start_simple_time'] = CTemplate::dateTimeFormat($session->session_start, SIMPLE_TIME);
					$sessionArray['session_start_date'] = date("m/d/Y", strtotime($session->session_start));
					$sessionArray['session_start_DD'] = date("d", strtotime($session->session_start));
					$sessionArray['session_start_MM'] = date("m", strtotime($session->session_start));
					$sessionArray['session_start_YYYY'] = date("Y", strtotime($session->session_start));
					$sessionArray['session_start_time'] = date("h:i A", strtotime($session->session_start));
					$sessionArray['store_name'] = $session->store_name;
					$sessionArray['store_id'] = $session->store_id;
					$sessionArray['address_line1'] = $session->address_line1;
					$sessionArray['address_line2'] = $session->address_line2;
					$sessionArray['city'] = $session->city;
					$sessionArray['state_id'] = $session->state_id;
					$sessionArray['postal_code'] = $session->postal_code;
					$sessionArray['telephone_day'] = $session->telephone_day;

					echo json_encode($sessionArray);
				}
				else
				{
					echo json_encode(array('processor_success' => false, 'processor_message' => 'Session full or closed'));
				}
			}
			else
			{
				echo json_encode(array('processor_success' => false, 'processor_message' => 'Session not found'));
			}
		}
		else
		{
			echo json_encode(array('processor_success' => false, 'processor_message' => 'No session id'));
		}
	}
};
?>