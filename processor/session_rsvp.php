<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CSRF.inc");

class processor_session_rsvp extends CPageProcessor
{
	function runPublic()
	{
		$this->sessionRSVP();
	}

	function runCustomer()
	{
		$this->sessionRSVP();
	}

	function sessionRSVP()
	{
		//$session_cookie_name = CBrowserSession::getSessionCookieName();
		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'delete_rsvp'){
			$session_id = $_REQUEST['session_id'];
			$user_id = $_REQUEST['user_id'];

			$User = CUser::getCurrentUser();

			if($User->id != $user_id)
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Delete Session RSVP only allowed by invited user.'
				));
			}
			else
			{
				CSession::deleteSessionRSVP($session_id, $user_id);

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Session RSVP deleted.'
				));
			}

		}
		// handle RSVP only
		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'rsvp_dream_taste')
		{
			$DAO_user = CUser::getCurrentUser();
			$CartObj = CCart2::instance(true);
			$cartSessionID = $CartObj->getSessionId();

			$remember_login = false;

			// option to save login cookie
			if (!empty($_POST['remember_login']))
			{
				$remember_login = true;
			}

			// authenticate the user with supplied credentials
			if (!empty($DAO_user->id))
			{
				$authenticateResult = true;
			}
			else
			{
				$authenticateResult = $DAO_user->Authenticate($_POST['primary_email_login'], $_POST['password_login'], false, false, false, true);
			}

			// get the session details from the submitted session id
			$DAO_session = DAO_CFactory::create('session', true);
			$DAO_session->id = $cartSessionID;
			$DAO_session->find_DAO_session(true);

			// supplied credentials authenticated
			if ($authenticateResult === true)
			{
				// log in the user
				$DAO_user->Login($remember_login);

				if ($DAO_user->isEligibleForSessionRSVP_DreamTaste($DAO_session))
				{
					$slotsWithoutOrders = $DAO_session->getRemainingSlots();
					$numRSVPs = $DAO_session->get_RSVP_count();
					if ($slotsWithoutOrders - $numRSVPs <= 0)
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'The session is full.'
						));
					}

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
						'processor_message' => 'This event is open to guests who are new to Dream Dinners. Please contact the store if you have questions.'
					));
				}
			}

			if ($authenticateResult === false)
			{
				// check the database for existing user that has the submitted email
				$DAO_user = DAO_CFactory::create('user');
				$DAO_user->primary_email = trim($_POST['primary_email_login']);

				// user exists
				if ($DAO_user->find(true))
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'The email address you entered already exists but the password you entered does not match the one we have on record. Please enter your Dream Dinners account password or visit the Sign In page to reset your password.'
					));
				}
				else // create a new user and RSVP
				{
					// get the session details from the submitted session id
					$DAO_session = DAO_CFactory::create('session');
					$DAO_session->id = $cartSessionID;
					$DAO_session->find(true);

					$slotsWithoutOrders = $DAO_session->getRemainingSlots();
					$numRSVPs = $DAO_session->get_RSVP_count();
					if ($slotsWithoutOrders - $numRSVPs <= 0)
					{
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'The session is full. Please contact your host or store.'
						));
					}

					// create a new user with submitted credentials
					if (ValidationRules::validateEmail($_POST['primary_email_login']))
					{
						$DAO_user = new CUser();
						$DAO_user->is_partial_account = 0;
						$DAO_user->primary_email = $_POST['primary_email_login'];
						$DAO_user->firstname = $_POST['firstname'];
						$DAO_user->lastname = $_POST['lastname'];
						$DAO_user->telephone_1 = $_POST['telephone_1'];
						$DAO_user->home_store_id = $DAO_session->store_id;
						$DAO_user->insert($_POST['password_login'], 'CUSTOMER', 'YES', true);

						// authenticate the new user
						$authenticateResult = $DAO_user->Authenticate($_POST['primary_email_login'], $_POST['password_login'], false, false, false, true);

						// log in the new user
						if ($authenticateResult === true)
						{
							$DAO_user->Login($remember_login);
						}

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