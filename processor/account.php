<?php
require_once("includes/CPageProcessor.inc");
require_once("DAO/BusinessObject/CTaskRetryQueue.php");

class processor_account extends CPageProcessor
{
	private $can = array(
		'can_post_user_id' => true,
	);

	function runPublic()
	{
		echo json_encode(array(
			'processor_success' => false,
			'processor_message' => 'Not logged in.',
		));
	}

	function runCustomer()
	{
		$this->can['can_post_user_id'] = false;

		$this->runAccount();
	}

	function runFranchiseStaff()
	{
		$this->runAccount();
	}

	function runFranchiseLead()
	{
		$this->runAccount();
	}

	function runFranchiseManager()
	{
		$this->runAccount();
	}

	function runOpsLead()
	{
		$this->runAccount();
	}

	function runEventCoordinator()
	{
		$this->runAccount();
	}

	function runHomeOfficeManager()
	{
		$this->runAccount();
	}

	function runFranchiseOwner()
	{
		$this->runAccount();
	}

	function runSiteAdmin()
	{
		$this->runAccount();
	}

	function duplicateSMSNumberExists($User, $normalizedTargetNumber)
	{
		$pkey = CUser::TEXT_MESSAGE_TARGET_NUMBER;
		$prefDAO = new DAO();
		$prefDAO->query("select id from user_preferences where user_id <> {$User->id} and pkey = $pkey and pvalue = '$normalizedTargetNumber' and is_deleted = 0");

		if ($prefDAO->N > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * @throws Exception
	 */
	function runAccount()
	{
		if (!isset($_POST['op']))
		{
			$_POST['op'] = '';
		}

		if ($_POST['op'] == 'update_pref')
		{
			if (!empty($_POST['key']))
			{
				$update_guest_pref = false;

				if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
				{
					$DAO_user = DAO_CFactory::create('user');
					$DAO_user->id = $_POST['user_id'];
					$DAO_user->find(true);

					$update_guest_pref = true;
				}
				else
				{
					$DAO_user = CUser::getCurrentUser();
				}

				if ($DAO_user)
				{
					$DAO_user->setUserPreference($_POST['key'], $_POST['value']);
				}
			}

			if ($_POST['key'] == CUser::TC_DELAYED_PAYMENT_AGREE)
			{
				if ($_POST['value'] == 'true')
				{
					$_POST['value'] = 1;
				}
				else
				{
					$_POST['value'] = 0;
				}
			}

			// Handle delayed payment decline
			/*
			 * Disabled for now CES 4/1/2020
			if ($_POST['key'] == CUser::TC_DELAYED_PAYMENT_AGREE && $_POST['value'] == 0)
			{
				$Payment = DAO_CFactory::create('payment');
				$Payment->user_id = $User->id;
				$Payment->is_delayed_payment = 1;
				$Payment->delayed_payment_status = CPayment::PENDING;
				$Payment->find();

				while($Payment->fetch())
				{
					$Payment->payment_note = $Payment->payment_note . ' (Canceled due to Delayed Payment Opt-out)';
					$Payment->delayed_payment_status = CPayment::CANCELLED;
					$Payment->update();
				}
			}
			*/

			if (!empty($update_guest_pref))
			{
				$return_prefs = array($DAO_user->id => $DAO_user->preferences);
			}
			else
			{
				$return_prefs = $DAO_user->preferences;
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Preference updated.',
				(!empty($update_guest_pref) ? 'guest_preferences' : 'user_preferences') => $return_prefs,
				'date_updated' => CTemplate::dateTimeFormat($DAO_user->preferences[$_POST['key']]['timestamp_updated'])
			));
		}

		if ($_POST['op'] == 'get_pref')
		{
			$update_guest_pref = false;

			if ($this->can['can_post_user_id'] && !empty($_POST['user_id']) && is_numeric($_POST['user_id']))
			{
				$DAO_user = DAO_CFactory::create('user');
				$DAO_user->id = $_POST['user_id'];
				$DAO_user->find(true);

				$update_guest_pref = true;
			}
			else
			{
				$DAO_user = CUser::getCurrentUser();
			}

			if ($DAO_user)
			{
				$DAO_user->getUserPreferences();

				if (!empty($update_guest_pref))
				{
					$return_prefs = array($DAO_user->id => $DAO_user->preferences);
				}
				else
				{
					$return_prefs = $DAO_user->preferences;
				}

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Preferences retrieved.',
					(!empty($update_guest_pref) ? 'guest_preferences' : 'user_preferences') => $return_prefs
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Could not find user.'
				));
			}
		}

		if ($_POST['op'] == 'request_data')
		{
			// Make sure there isn't already a request
			if (!CUser::getCurrentUser()->hasPendingDataRequest())
			{
				CEmail::accountRequestData(CUser::getCurrentUser());

				CUserAccountManagement::createTask(CUser::getCurrentUser()->id, CUserAccountManagement::ACTION_SEND_ACCOUNT_INFORMATION);
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Data request submitted.'
			));
		}

		if ($_POST['op'] == 'request_delete' && !empty($_POST['challenge']))
		{
			$DAO_user = DAO_CFactory::create('user', true);
			$DAO_user->id = CUser::getCurrentUser()->id;
			$DAO_user->fetch();
			$authenticateResult = $DAO_user->Authenticate(CUser::getCurrentUser()->primary_email, $_POST['challenge'], false, false, false, true);

			if ($authenticateResult === true)
			{
				// delete account information
				CUser::getCurrentUser()->handleDeleteAccountRequest();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Delete request accepted.',
					'delete_success' => true
				));
			}
			else
			{
				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Incorrect account password.',
					'delete_success' => false
				));
			}
		}
	}
}

?>