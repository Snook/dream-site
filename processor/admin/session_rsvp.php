<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once("CTemplate.inc");

class processor_admin_session_rsvp extends CPageProcessor
{
	function runSiteAdmin()
	{
		$this->sessionRSVP();
	}

	function runFranchiseStaff()
	{
		$this->sessionRSVP();
	}

	function runFranchiseOwner()
	{
		$this->sessionRSVP();
	}

	function runFranchiseLead()
	{
		$this->sessionRSVP();
	}

	function runEventCoordinator()
	{
		$this->sessionRSVP();
	}

	function runOpsLead()
	{
		$this->sessionRSVP();
	}

	function runOpsSupport()
	{
		$this->sessionRSVP();
	}

	function runFranchiseManager()
	{
		$this->sessionRSVP();
	}

	function runHomeOfficeManager()
	{
		$this->sessionRSVP();
	}

	function sessionRSVP()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (empty($_REQUEST['op']))
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'No operation specified.',
				'dd_toasts' => array(
					array('message' => 'No operation specified.')
				)
			));
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_form_add_guest')
		{
			$tpl = new CTemplate();

			$form_add_guest = $tpl->fetch('admin/subtemplate/form_add_guest.tpl.php');

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved form.',
				'form_add_guest' => $form_add_guest
			));
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'add_rsvp')
		{
			$session_id = $_REQUEST['session_id'];
			$user_id = $_REQUEST['user_id'];

			$DAO_session = DAO_CFactory::create('session', true);
			$DAO_session->id = $session_id;
			$DAO_session->find_DAO_session(true);

			$DAO_user = DAO_CFactory::create('user');
			$DAO_user->id = $user_id;
			$DAO_user->find(true);

			if ($DAO_user->isEligibleForSessionRSVP_DreamTaste($DAO_session))
			{
				CSession::createSessionRSVP($DAO_session, $DAO_user);

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Session RSVP added.'
				));
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'This account is not eligible to RSVP due to previous orders or RSVPs. RSVP is only available to new customers.'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'delete_rsvp')
		{
			$session_id = $_REQUEST['session_id'];
			$user_id = $_REQUEST['user_id'];

			CSession::deleteSessionRSVP($session_id, $user_id);

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Session RSVP deleted.'
			));
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'rsvp_dream_taste')
		{
			$User = new CUser();

			$authenticateResult = $User->Authenticate($_POST['primary_email_login'], $_POST['password_login'], false, false, false, true);

			// get the session details from the submitted session id
			$DAO_session = DAO_CFactory::create('session', true);
			$DAO_session->id = $_POST['rsvp_dream_taste'];

			//TODO: evan-l-> ask Ryan why this losses the session id
			$DAO_session->find_DAO_session(true);
			$DAO_session->id = $_POST['rsvp_dream_taste'];
			// supplied credentials authenticated
			if ($authenticateResult === true)
			{

				if ($User->isEligibleForSessionRSVP_DreamTaste($DAO_session))
				{
					// create rsvp
					$SessionRSVP = CSession::createSessionRSVP($DAO_session, $User);

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'RSVP Success.',
						'user' => array(
							'firstname' => $User->firstname
						)
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'This account is not eligible to RSVP due to previous orders or RSVPs. RSVP is only available to new customers.'
					));
				}
			}

			if ($authenticateResult === false)
			{
				// check the database for existing user that has the submitted email
				$User->primary_email = trim($_POST['primary_email_login']);

				// user exists
				if ($User->find(true))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'The email address you entered already exists but the password you entered does not match the one we have on record. Please enter your Dream Dinners account password or visit the Sign In page to reset your password.'
					));
				}
				else // create a new user and RSVP
				{
					// create a new user with submitted credentials
					if (ValidationRules::validateEmail($_POST['primary_email_login']))
					{
						$DAO_user = new CUser();
						$DAO_user->is_partial_account = 1;
						$DAO_user->primary_email = $_POST['primary_email_login'];
						$DAO_user->firstname = $_POST['firstname'];
						$DAO_user->lastname = $_POST['lastname'];
						$DAO_user->telephone_1 = $_POST['telephone_1'];
						$DAO_user->home_store_id = $DAO_session->store_id;
						$DAO_user->insert($_POST['password_login'], 'CUSTOMER', 'YES', true);

						// authenticate the new user
						$authenticateResult = $DAO_user->Authenticate($_POST['primary_email_login'], $_POST['password_login'], false, false, false, true);

						// create rsvp
						$SessionRSVP = CSession::createSessionRSVP($DAO_session, $DAO_user);

						CAppUtil::processorMessageEcho(array(
							'processor_success' => true,
							'processor_message' => 'RSVP Success.',
							'user' => array(
								'firstname' => $DAO_user->firstname
							)
						));
					}
					else
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'You did not enter a properly formatted email address.<br />(Example: user@example.com).'
						));
					}
				}
			}
		}
	}
}

?>