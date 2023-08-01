<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once("includes/DAO/BusinessObject/CCustomerReferral.php");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('page/customer/my_events.php');
require_once('DAO/BusinessObject/CDreamTasteEvent.php');
require_once('DAO/BusinessObject/CContactImport.php');
require_once('DAO/BusinessObject/CEmail.php');

class processor_my_events extends CPageProcessor
{
	function runCustomer()
	{
		$this->runMyEvents();
	}

	function runMyEvents()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);
		$User = CUser::getCurrentUser();

		if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'import_contacts')
		{
			$tpl = new CTemplate();

			switch ($_REQUEST['client'])
			{
				case 'google':
					$contacts = CContactImport::getGoogleContactsArray($_POST['contacts']);
					break;
				case 'msgraph':
					$contacts = CContactImport::getMSGraphContactsArray($_POST['contacts']);
					break;
				case 'previous':
					$contacts = CContactImport::getReferredContactsArray($User);
					break;
				default:
					$contacts = false;
					break;
			}

			$imports_html = '';

			if ($contacts)
			{
				foreach ($contacts as $contact_id => $contact)
				{
					$tpl->assign('contact', $contact['name']);

					foreach ($contact['emails'] as $id => $email)
					{
						$tpl->assign('email', $email);
						$tpl->assign('import_id', 'import_id-' . $contact_id . '-' . $id);

						$imports_html .= $tpl->fetch('customer/subtemplate/my_events/my_events_import_row.tpl.php');
					}
				}
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved import form.',
				'html' => $imports_html
			));
		}

		if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'get_template')
		{
			if ($_REQUEST['template'] == 'manage_invites')
			{
				$tpl = new CTemplate();

				$manage_invites = $tpl->fetch('customer/subtemplate/my_events/my_events_manage_invites.tpl.php');

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved invite form.',
					'html' => $manage_invites
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'save_details')
		{
			$sid = $_REQUEST['event_id'];

			$editing_user_id = CUser::getCurrentUser()->id;

			$prop = DAO_CFactory::create('session_properties');
			$prop->session_host = $editing_user_id;
			$prop->session_id = $sid;

			if ($prop->find(true))
			{
				if ($editing_user_id != $prop->session_host)
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'This session belongs to another host.'
					));
				}

				$orgProp = clone($prop);
				$prop->informal_host_name = $_POST['host'];
				$prop->message = $_POST['message'];
				$prop->update($orgProp);

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Your invitation has been saved.'
				));
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'There was a problem saving your invitation.'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'send_invites')
		{
			$sid = $_REQUEST['event_id'];

			$sessionDetails = CSession::getSessionDetail($sid);

			$req_message = CGPC::do_clean((!empty($_REQUEST['message']) ? $_REQUEST['message'] : false), TYPE_NOHTML, true);
			$req_name = CGPC::do_clean((!empty($_REQUEST['name']) ? $_REQUEST['name'] : false), TYPE_NOHTML, true);

			$emailList = json_decode($_REQUEST['emails']);
			$manageInviteOnly = false;

			// no session host or user is not the host, so they are just inviting to an existing session
			if (empty($sessionDetails['session_host']) || $sessionDetails['session_host'] != CUser::getCurrentUser()->id)
			{
				$manageInviteOnly = true;
				$personalMessage = $req_message;

				if (empty($req_name))
				{
					$senderName = CUser::getCurrentUser()->firstname;
				}
				else
				{
					$senderName = $req_name;
				}
			}
			// user is the host, use their session_properties details
			else
			{
				$personalMessage = $req_message;

				if (empty($req_name))
				{
					if (!empty($sessionDetails['session_host_informal_name']))
					{
						$senderName = $sessionDetails['session_host_informal_name'];
					}
					else
					{
						$senderName = CUser::getCurrentUser()->firstname;
					}
				}
				else
				{
					$senderName = $req_name;
				}
			}

			if ($sessionDetails)
			{
				$origination_type_code = 1;

				if ($sessionDetails['session_type'] == CSession::DREAM_TASTE)
				{
					$origination_type_code = 4;
				}

				$referredGuestEmails = array();
				$eventReferrals = array();

				foreach ($emailList as $referral_id => $contact)
				{
					$referredGuestEmails[] = $contact->referred_user_email;

					// check to see if this email has already been invited to this session by this user
					$customer_referral = DAO_CFactory::create('customer_referral');
					$customer_referral->referring_user_id = CUser::getCurrentUser()->id;
					$customer_referral->referred_user_email = $contact->referred_user_email;
					$customer_referral->referrer_session_id = $sessionDetails['id'];

					if (!$customer_referral->find(true))
					{
						$customer_referral->referred_user_name = $contact->referred_user_name;
						$customer_referral->inviting_user_name = $senderName;
						$customer_referral->origination_type_code = $origination_type_code;
						$customer_referral->origination_uid = CCustomerReferral::generateUniqueID();
						$customer_referral->session_properties_id = $sessionDetails['session_properties_id'];
						$customer_referral->referral_status = 0;

						if ($sessionDetails['session_type'] == CSession::DREAM_TASTE)
						{
							$customer_referral->referral_status = 1;
						}

						$customer_referral->insert();

						$eventReferrals[$customer_referral->id] = array(
							'referred_user_name' => $contact->referred_user_name,
							'referred_user_email' => $contact->referred_user_email,
							'referred_user_send_email' => false
						);
					}
					else
					{
						$eventReferrals[$referral_id] = array(
							'referred_user_name' => $contact->referred_user_name,
							'referred_user_email' => $contact->referred_user_email,
							'referred_user_send_email' => false
						);
					}

					if (!empty($contact->referred_user_send_email))
					{
						// set this back to false for front end use
						$emailList->{$referral_id}->referred_user_send_email = false;

						if (!is_numeric($referral_id))
						{
							// cleanup up email list for front end use
							$emailList->{$customer_referral->id} = $emailList->{$referral_id};
							unset($emailList->{$referral_id});
						}

						// email data attributes
						$dataArr = array(
							'message' => (!empty($sessionDetails['session_host_message']) ? $sessionDetails['session_host_message'] : $personalMessage),
							'from_email' => CUser::getCurrentUser()->primary_email,
							'from_name' => $senderName,
							'to_email' => $contact->referred_user_email,
							'to_name' => $contact->referred_user_name,
							'referral_link' => HTTPS_SERVER . '/invite/' . $customer_referral->origination_uid,
							'referral_link_starter' => HTTPS_SERVER . '/invstarter/' . $customer_referral->origination_uid,
							'session' => $sessionDetails
						);

						// send the email
						CEmail::sendInvitations($dataArr);
					}
				}

				$tpl = new CTemplate();

				// load up guests who are attending based on if the hostess can view all
				foreach ($sessionDetails['bookings'] as $booking)
				{
					if ($booking['status'] == CBooking::ACTIVE)
					{
						$attendingGuestEmails[] = $booking['primary_email'];

						if (!$manageInviteOnly || $booking['user_id'] == CUser::getCurrentUser()->id || ($manageInviteOnly && in_array($booking['primary_email'], $referredGuestEmails)))
						{
							$guestsAttending[] = $booking;
						}
					}
				}

				foreach ($sessionDetails['session_rsvp'] as $booking)
				{
					$attendingGuestEmails[] = $booking->user->primary_email;

					if (!$manageInviteOnly || $booking->user->id == CUser::getCurrentUser()->id || ($manageInviteOnly && in_array($booking->user->primary_email, $referredGuestEmails)))
					{
						$guestsAttending[] = $booking->user;
					}
				}

				// bookings and rsvp are mixed between arrays and objects, this forces them all to be arrays
				if (!empty($guestsAttending))
				{
					$guestsAttending = json_decode(json_encode($guestsAttending), true);
				}

				$usersFuturePastEvents = array(
					'remainingTotalAttending' => count($attendingGuestEmails) - count($guestsAttending),
					'guestsAttending' => $guestsAttending,
					'attendingGuestEmails' => $attendingGuestEmails,
					'manageInviteOnly' => $manageInviteOnly,
					'eventReferrals' => $eventReferrals,
					'eventReferralsJS' => ((!empty($eventReferrals)) ? json_encode($eventReferrals) : '{}')
				);

				$tpl->assign('usersFuturePastEvents', $usersFuturePastEvents);

				$manage_invites = $tpl->fetch('customer/subtemplate/my_events/my_events_referrals.tpl.php');

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Invites have been sent.',
					'eventReferralsJS' => json_encode($eventReferrals),
					'html' => $manage_invites,
					'dd_toasts' => array(
						array('message' => 'Invites have been sent.')
					)
				));
			}
			else
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Session not found.'
				));
			}
		}
	}
}

?>