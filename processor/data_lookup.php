<?php
/*
 * Created on March 12, 2012
 * project_name getSessionDetails
 *
 * Copyright 2012 DreamDinners
 * @author RyanS
 */

require_once("includes/CPageProcessor.inc");

class processor_data_lookup extends CPageProcessor
{
	function runPublic()
	{
		$this->getData();
	}

	function runCustomer()
	{
		$this->getData();
	}

	function runFranchiseStaff()
	{
		$this->getData();
	}

	function runFranchiseManager()
	{
		$this->getData();
	}

	function runOpsLead()
	{
		$this->getData();
	}

	function runFranchiseOwner()
	{
		$this->getData();
	}

	function runHomeOfficeStaff()
	{
		$this->getData();
	}

	function runHomeOfficeManager()
	{
		$this->getData();
	}

	function runSiteAdmin()
	{
		$this->getData();
	}

	function getData()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (isset($_POST['type']) && $_POST['type'] == 'user')
		{
			if (isset($_POST['check']) && $_POST['check'] == 'email')
			{
				$user = DAO_CFactory::create('user');
				$user->primary_email = $_POST['email'];

				if (!ValidationRules::validateEmail($user->primary_email))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'The email does not appear to be valid.'
					));
				}

				if ($user->find(true))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Email found in database.'
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Email not found in database.'
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Check method not specified'
				));
			}
		}
		else if (isset($_POST['type']) && $_POST['type'] == 'valid_email')
		{
			if (!ValidationRules::validateEmail($_POST['email']))
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'The email does not appear to be valid.'
				));
			}
			else if (!empty($_POST['rules']) && !empty($_POST['rules']['not_self']))
			{
				if(CUser::getCurrentUser()->primary_email == $_POST['email'])
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Email address is not different from account email.'
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Valid email format.'
					));
				}
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Valid email format.'
				));
			}
		}
		else
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'No type specified'
			));
		}
	}
}

?>