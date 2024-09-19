<?php
require_once("includes/CPageAdminOnly.inc");
require_once('CMail.inc');

class page_admin_email extends CPageAdminOnly
{
	/**
	 * @throws Exception
	 */
	function runManufacturerStaff(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseStaff(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseLead(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseManager(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runFranchiseOwner(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runEventCoordinator(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runOpsLead(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runHomeOfficeManager(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function runSiteAdmin(): void
	{
		$this->sendEmail();
	}

	/**
	 * @throws Exception
	 */
	function sendEmail(): void
	{
		$user_id = CGPC::do_clean((!empty($_REQUEST['id']) ? $_REQUEST['id'] : false), TYPE_INT);
		$session_id = CGPC::do_clean((!empty($_REQUEST['session']) ? $_REQUEST['session'] : false), TYPE_INT);

		$recipientArray = array();

		if (!empty($user_id))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $user_id;

			if ($User->find(true))
			{
				$recipientArray[$User->id] = array(
					'id' => $User->id,
					'primary_email' => $User->primary_email,
					'firstname' => $User->firstname,
					'lastname' => $User->lastname
				);
			}
		}

		if (!empty($session_id))
		{
			$Session = CSession::getSessionDetailArray($session_id);

			foreach ($Session[$session_id]['bookings'] AS $booking)
			{
				if ($booking['status'] == CBooking::ACTIVE)
				{
					$recipientArray[$booking['user']->id] = array(
						'id' => $booking['user']->id,
						'primary_email' => $booking['user']->primary_email,
						'firstname' => $booking['user']->firstname,
						'lastname' => $booking['user']->lastname
					);
				}
			}

			// Also add RSVPers
			$RSVPers = new DAO();
			$RSVPers->query("SELECT
				u.id,
				u.firstname,
				u.lastname,
				u.primary_email
				FROM session_rsvp AS sr
				JOIN `user` AS u ON u.id = sr.user_id AND u.is_deleted = '0'
				WHERE sr.session_id = '" . $session_id . "'
				AND sr.is_deleted = '0'");

			while ($RSVPers->fetch())
			{
				$recipientArray[$RSVPers->id] = array(
					'id' => $RSVPers->id,
					'primary_email' => $RSVPers->primary_email,
					'firstname' => $RSVPers->firstname,
					'lastname' => $RSVPers->lastname
				);
			}
		}

		$this->Template->assign('recipient_list', $recipientArray);
		$this->Template->assign('extensions', '.' . implode(' .', CMAIL::allowedExtensions()));
		$this->Template->assign('sizelimit', floor(CMail::SIZELIMIT / 1024 / 1024) . 'MB');
		$this->Template->assign('js_extensions', "'" . implode("','", CMAIL::allowedExtensions()) . "'");

		$Form = new CForm();
		$Form->htmlspecialcharsOnRepost = true;
		$Form->Repost = true;

		$fromOptionsEmail = array();
		$fromOptions = array();
		$fromAddress = ADMIN_EMAIL;
		$adminUser = CUser::getCurrentUser();
		$storeAddress = false;

		if ($adminUser->user_type == CUser::SITE_ADMIN || $adminUser->user_type == CUser::HOME_OFFICE_MANAGER || $adminUser->user_type == CUser::HOME_OFFICE_STAFF)
		{
			$fromAddress = $adminUser->primary_email;
		}
		else
		{
			$store = DAO_CFactory::create('store');
			$store->id = CBrowserSession::getCurrentFadminStore();

			if ($store->find(true))
			{
				$storeAddress = $store->email_address;

				$fromOptions[$store->email_address] = array(
					'email' => $store->email_address,
					'name' => 'Dream Dinners ' . $store->store_name
				);

				$fromOptionsEmail[$store->email_address] = 'Dream Dinners ' . $store->store_name . ' &lt;' . $store->email_address . '&gt;';
			}

			if (str_contains($adminUser->primary_email, "dreamdinners.com"))
			{
				$fromOptions[$adminUser->primary_email] = array(
					'email' => $adminUser->primary_email,
					'name' => $adminUser->firstname . ' ' . $adminUser->lastname
				);

				$fromOptionsEmail[$adminUser->primary_email] = $adminUser->firstname . ' ' . $adminUser->lastname . ' &lt;' . $adminUser->primary_email . '&gt;';
			}
		}

		if (isset($_POST['email_send']))
		{
			//send the email
			try
			{
				if (!empty($_POST['recipient']))
				{
					if ((isset($_POST['sender_email']) || isset($_POST['sender_email_dropdown'])) && isset($_POST['email_subject']) && isset($_POST['email_body']))
					{
						foreach ($_POST['recipient'] AS $user_id)
						{
							/*
							When you submit the BackOffice email form it loops over each listed recipient and sends a single email,
							it's not a single email with all recipient BBC'd. So for each recipient listed a single email is sent with the following;

							From: sender@dreamdinners.com [no-reply@dreamdinners.com if sender is not an @dreamdinners.com email]
							Reply-To: sender@dreamdinners.com
							To: recipient@example.com
							BCC: sender@dreamdinners.com [crm_address@example.com if provided] [store_email@dreamdinners.com if sender email is not the store email]
							*/

							$Recipient = DAO_CFactory::create('user');
							$Recipient->id = $user_id;

							if ($Recipient->find(true))
							{
								$fromName = null;
								$sendmail = true;

								if (!empty($_POST['sender_name']))
								{
									$fromName = CGPC::do_clean($_POST['sender_name'],TYPE_STR);
								}

								$fromEmail = ADMIN_EMAIL;

								if (!empty($_POST['sender_email']))
								{
									$fromEmail = CGPC::do_clean($_POST['sender_email'],TYPE_EMAIL);
								}
								else if (!empty($_POST['sender_email_dropdown']))
								{
									$fromName = $fromOptions[CGPC::do_clean($_POST['sender_email_dropdown'],TYPE_STR)]['name'];
									$fromEmail = $fromOptions[CGPC::do_clean($_POST['sender_email_dropdown'],TYPE_EMAIL)]['email'];
								}

								$bcc = $fromEmail;

								if (!empty($_POST['bcc_email']))
								{
									$bcc .= ',' . CGPC::do_clean($_POST['bcc_email'],TYPE_STR);
								}

								if ($adminUser->user_type != CUser::SITE_ADMIN && $adminUser->user_type != CUser::HOME_OFFICE_MANAGER && $adminUser->user_type != CUser::HOME_OFFICE_STAFF)
								{
									if ($fromEmail != $storeAddress)
									{
										if (!empty($bcc) && $storeAddress)
										{
											$bcc .= "," . $storeAddress;
										}
										else if ($storeAddress)
										{
											$bcc = $storeAddress;
										}
									}
								}

								$attachment_file = false;
								if (isset($_FILES['email_attachment']) && $_FILES['email_attachment']['name'] != '')
								{
									$array = explode('.', strtolower(basename($_FILES['email_attachment']['name'])));
									$ext = array_pop($array);

									if ($_FILES['email_attachment']['error'])
									{
										$this->Template->setStatusMsg('Attachment error, file size may be too large. Limit ' . CMail::SIZELIMIT / 1024 / 1024 . 'MB');
										$sendmail = false;
									}
									else if (!in_array($ext, CMail::allowedExtensions()))
									{
										$this->Template->setStatusMsg('Invalid file extension. (' . $ext . ')');
										$sendmail = false;
									}
									else
									{
										$attachment_file = $_FILES['email_attachment'];
									}
								}

								if ($sendmail)
								{
									$emailVars = array(
										'email_subject' => stripslashes(CGPC::do_clean($_POST['email_subject'],TYPE_STR)),
										'email_body' => nl2br(CGPC::do_clean($_POST['email_body'],TYPE_STR_SIMPLE))
										//replace linefeeds with <br />
									);

									$Mail = new CMail();
									$Mail->from_name = $fromName;
									$Mail->from_email = $fromEmail;
									$Mail->to_id = $Recipient->id;
									$Mail->to_name = $Recipient->firstname . ' ' . $Recipient->lastname;
									$Mail->to_email = $Recipient->primary_email;
									$Mail->subject = $emailVars['email_subject'];
									$Mail->body_html = CMail::mailMerge('admin_generic.html.php', $emailVars);
									$Mail->body_text = CMail::mailMerge('admin_generic.txt.php', $emailVars);
									$Mail->reply_email = $fromEmail;
									$Mail->template_name = 'admin_generic';
									$Mail->attachment = $attachment_file;

									if (isset($ext) && $ext == "pdf")
									{
										$Mail->attachmentType = $ext;
									}
									$Mail->bcc_email = $bcc;
									$Mail->sendEmail();

									$log_string = 'Sent email';

									if ($attachment_file)
									{
										$log_string .= ' with ' . $ext . ' attachment';
									}

									$log_string .= ' to ' . $Recipient->primary_email . ' from ' . $fromEmail . ' with subject: ' . $emailVars['email_subject'];

									CUserHistory::recordUserEvent(CUser::getCurrentUser()->id, CUser::getCurrentUser()->home_store_id, 'null', 600, 'null', 'null', $log_string);

									$this->Template->setStatusMsg('Your message has been sent to ' . $Recipient->firstname . ' ' . $Recipient->lastname);
								}
								else
								{
									$this->Template->setStatusMsg('Could not send this email. Please try again.');
								}
							}
						}

						CApp::bounce('/backoffice');
					}
				}
				else
				{
					$this->Template->setErrorMsg("Please supply recipients");
				}
			}
			catch (exception)
			{
				throw new exception('email could not be sent');
			}
		}

		if (!empty($Session))
		{
			$Form->DefaultValues['email_subject'] = 'Your Dream Dinners session ' . CTemplate::dateTimeFormat($Session[$session_id]['session_start'], VERBOSE_DATE) . ' at ' . CTemplate::dateTimeFormat($Session[$session_id]['session_start'], TIME_ONLY);
		}

		if ($adminUser->user_type == CUser::SITE_ADMIN || $adminUser->user_type == CUser::HOME_OFFICE_MANAGER || $adminUser->user_type == CUser::HOME_OFFICE_STAFF)
		{
			$Form->DefaultValues['sender_name'] = $adminUser->firstname . ' ' . $adminUser->lastname;

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::dd_required => true,
				CForm::style => "width:300px;",
				CForm::name => 'sender_name'
			));
		}

		if ($adminUser->user_type == CUser::SITE_ADMIN || $adminUser->user_type == CUser::HOME_OFFICE_MANAGER || $adminUser->user_type == CUser::HOME_OFFICE_STAFF)
		{
			$Form->DefaultValues['sender_email'] = $fromAddress;

			$Form->AddElement(array(
				CForm::type => CForm::EMail,
				CForm::style => 'width:300px;',
				CForm::dd_required => true,
				CForm::name => 'sender_email'
			));
		}
		else
		{
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::dd_required => true,
				CForm::name => 'sender_email_dropdown',
				CForm::options => $fromOptionsEmail
			));
		}

		$Form->AddElement(array(
			CForm::type => CForm::EMail,
			CForm::style => 'width:300px;',
			CForm::name => 'bcc_email'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::style => 'width: 80%; padding: 3px;',
			CForm::dd_required => true,
			CForm::name => 'email_subject'
		));

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::style => 'height: 300px; width: 96%; padding: 10px;',
			CForm::dd_required => true,
			CForm::name => 'email_body'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::value => CMail::SIZELIMIT,
			CForm::name => 'MAX_FILE_SIZE'
		));

		if (!empty($user_id))
		{
			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::value => $user_id,
				CForm::name => 'recipient[' . $user_id . ']'
			));
		}

		if (!empty($recipientArray))
		{
			foreach ($recipientArray AS $recipient)
			{
				$Form->AddElement(array(
					CForm::type => CForm::Hidden,
					CForm::value => $recipient['id'],
					CForm::name => 'recipient[' . $recipient['id'] . ']'
				));
			}
		}

		if (!empty($session_id))
		{
			foreach ($Session[$session_id]['bookings'] AS $booking)
			{
				if ($booking['status'] == CBooking::ACTIVE)
				{
					$Form->AddElement(array(
						CForm::type => CForm::Hidden,
						CForm::value => $booking['user']->id,
						CForm::name => 'recipient[' . $booking['user']->id . ']'
					));
				}
			}
		}

		$Form->AddElement(array(
			CForm::type => CForm::File,
			CForm::name => 'email_attachment'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::value => 'Send',
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'email_send'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Button,
			CForm::value => 'Cancel',
			CForm::onClick => 'javascript:emailCancel();',
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'email_cancel'
		));

		//set template vars
		$this->Template->assign('email_form', $Form->Render());
	}
}