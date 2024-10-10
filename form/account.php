<?php
require_once('includes/CForm.inc');
require_once('includes/ValidationRules.inc');
require_once('includes/DAO/BusinessObject/CUser.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CCorporateCrateClient.php');
require_once('includes/DAO/BusinessObject/CUserReferralSource.php');

class form_account
{

	static array $referralSourceArrayOld = array(
		CUserReferralSource::CUSTOMER_REFERRAL => array(
			CForm::placeholder => 'Email of Friend Who Referred You',
			CForm::email => true
		),
		CUserReferralSource::TASTE_EVENT => array(
			CForm::placeholder => 'Taste, In-store Event or Party'
		),
		CUserReferralSource::GROUPON => array(
			CForm::placeholder => 'Groupon, Living Social or Coupon Webite'
		),
		CUserReferralSource::SAW_STORE => array(
			'default_value' => 'Drove By and Was Curious'
		),
		CUserReferralSource::DIRECT_MAIL => array(
			'default_value' => 'Direct Mail'
		),
		CUserReferralSource::WEBSITE => array(
			CForm::placeholder => 'Online'
		),
		CUserReferralSource::RADIO => array(
			CForm::placeholder => 'Radio'
		),
		CUserReferralSource::TELEVISION => array(
			CForm::placeholder => 'Television'
		),
		CUserReferralSource::OTHER => array(
			CForm::placeholder => 'Other'
		)
	);

	public static array $referralSourceArray = array(
		CUserReferralSource::CUSTOMER_REFERRAL => array(
			CForm::placeholder => 'Friend Referral',
			CForm::email => true
		),
		CUserReferralSource::VIRTUAL_PARTY => array(
			CForm::placeholder => 'Virtual Party'
		),
		CUserReferralSource::TASTE_EVENT => array(
			CForm::placeholder => 'In Store Party or Special Event'
		),
		CUserReferralSource::SEARCH_ENGINE => array(
			CForm::placeholder => 'Web Search'
		),
		CUserReferralSource::WEB_AD => array(
			CForm::placeholder => 'Website/Banner Ad'
		),
		CUserReferralSource::BLOG => array(
			CForm::placeholder => 'Blog'
		),
		CUserReferralSource::SAW_STORE => array(
			CForm::placeholder => 'Walk in/Drive by'
		),
		CUserReferralSource::SOCIAL_MEDIA => array(
			CForm::placeholder => 'Social Media'
		),
		CUserReferralSource::OTHER => array(
			CForm::placeholder => 'Other'
		),

	);

	static string $forwardTo = '/my-account'; //switched to auto-confirm
	static public bool $originalCustomerReferral = false;

	/**
	 * Sanitizes the input fields for the account form.
	 *
	 * Sanitizes all the fields in the account form using the CGPC::do_clean() method.
	 */
	static function sanitizeAccountFields(): void
	{
		if (isset($_POST['firstname']))
		{
			$_POST['firstname'] = CGPC::do_clean($_POST['firstname'], TYPE_STR);
		}

		if (isset($_POST['lastname']))
		{
			$_POST['lastname'] = CGPC::do_clean($_POST['lastname'], TYPE_STR);
		}

		if (isset($_POST['primary_email']))
		{
			$_POST['primary_email'] = CGPC::do_clean($_POST['primary_email'], TYPE_EMAIL);
		}

		if (isset($_POST['confirm_email_address']))
		{
			$_POST['confirm_email_address'] = CGPC::do_clean($_POST['confirm_email_address'], TYPE_EMAIL);
		}

		if (isset($_POST['address_line1']))
		{
			$_POST['address_line1'] = CGPC::do_clean($_POST['address_line1'], TYPE_STR);
		}

		if (isset($_POST['address_line2']))
		{
			$_POST['address_line2'] = CGPC::do_clean($_POST['address_line2'], TYPE_STR);
		}

		if (isset($_POST['city']))
		{
			$_POST['city'] = CGPC::do_clean($_POST['city'], TYPE_STR);
		}

		if (isset($_POST['state_id']))
		{
			$_POST['state_id'] = CGPC::do_clean($_POST['state_id'], TYPE_STR);
		}

		if (isset($_POST['postal_code']))
		{
			$_POST['postal_code'] = CGPC::do_clean($_POST['postal_code'], TYPE_STR);
		}

		if (isset($_POST['password']))
		{
			$_POST['password'] = CGPC::do_clean($_POST['password'], TYPE_STR);
		}

		if (isset($_POST['password_confirm']))
		{
			$_POST['password_confirm'] = CGPC::do_clean($_POST['password_confirm'], TYPE_STR);
		}

		if (isset($_POST['telephone_1']))
		{
			$_POST['telephone_1'] = CGPC::do_clean($_POST['telephone_1'], TYPE_STR);
		}

		if (isset($_POST['telephone_2']))
		{
			$_POST['telephone_2'] = CGPC::do_clean($_POST['telephone_2'], TYPE_STR);
		}
	}

	static function _buildForm($Form, $isCreate = true, $updateSearchForm = false, $referring_user = false, $User = false, $billingAddrRequired = true)
	{
		$isEMailLess = false;
		if ($User && !$isCreate && $User->primary_email == null)
		{
			$isEMailLess = true;
		}

		if (!isset($Form->DefaultValues['telephone_1_type']))
		{
			$Form->DefaultValues['telephone_1_type'] = 'MOBILE';
		}

		if (!isset($Form->DefaultValues['telephone_2_type']))
		{
			$Form->DefaultValues['telephone_2_type'] = 'LAND_LINE';
		}

		if (isset($Form->DefaultValues['firstname']))
		{
			$Form->DefaultValues['firstname'] = htmlentities($Form->DefaultValues['firstname']);
		}

		if (!$isCreate)
		{
			$Form->DefaultValues['confirm_email_address'] = $Form->value('primary_email');
		}

		// First & Last
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "firstname",
			CForm::required => true,
			CForm::placeholder => "*First Name",
			CForm::required_msg => "Please enter your first name.",
			CForm::maxlength => 40,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "lastname",
			CForm::required => true,
			CForm::placeholder => "*Last Name",
			CForm::required_msg => "Please enter your last name.",
			CForm::maxlength => 80,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		// Primary Email
		$Form->AddElement(array(
			CForm::type => CForm::EMail,
			CForm::name => "primary_email",
			CForm::placeholder => "*Email",
			CForm::required => (($isEMailLess) ? false : true),
			CForm::required_msg => "Please enter your email address.",
			CForm::maxlength => 60,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::EMail,
			CForm::name => "confirm_email_address",
			CForm::confirm => "primary_email",
			CForm::placeholder => "*Confirm Email Address",
			CForm::required_msg => "Please confirm your email address.",
			CForm::required => (($isEMailLess) ? false : true),
			CForm::maxlength => 60,
			CForm::xss_filter => true
		));

		// Secondary Email
		$Form->AddElement(array(
			CForm::type => CForm::EMail,
			CForm::name => "secondary_email",
			CForm::placeholder => "Secondary Email",
			CForm::required => false,
			CForm::required_msg => "Please enter your email address.",
			//	CForm::email => true, // email validation wants a non-empty string - make it a string only for now.
			CForm::maxlength => 60,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Password,
			CForm::name => "password",
			CForm::placeholder => "*Password",
			CForm::required => $isCreate,
			CForm::required_msg => "Please enter a password.",
			CForm::autocomplete => 'new-password',
			CForm::minimum => 6,
			CForm::length => 20,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Password,
			CForm::name => "password_confirm",
			CForm::placeholder => "*Password Confirm",
			CForm::required_msg => "Please confirm your password.",
			CForm::autocomplete => 'new-password',
			CForm::required => $isCreate,
			CForm::length => 20,
			CForm::xss_filter => true
		));

		// Billing Address
		$billingRequired = $billingAddrRequired ? '*' : '';

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "address_line1",
			CForm::required => $billingAddrRequired,
			CForm::placeholder => $billingRequired . "Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::maxlength => 255,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::placeholder => "Address 2",
			CForm::name => "address_line2",
			CForm::maxlength => 255,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "city",
			CForm::required => $billingAddrRequired,
			CForm::placeholder => $billingRequired . "City",
			CForm::required_msg => "Please enter a city.",
			CForm::maxlength => 64,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'state_id',
			CForm::required_msg => "Please select a state.",
			CForm::required => $billingAddrRequired
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "postal_code",
			CForm::required => $billingAddrRequired,
			CForm::placeholder => $billingRequired . "Postal Code",
			CForm::gpc_type => TYPE_POSTAL_CODE,
			CForm::xss_filter => true,
			CForm::maxlength => 5,
			CForm::required_msg => "Please enter a zip code.",
			CForm::length => 5
		));

		// Shipping Address
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_address_line1",
			CForm::required => false,
			CForm::placeholder => "Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::maxlength => 255,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::placeholder => "Address 2",
			CForm::name => "shipping_address_line2",
			CForm::maxlength => 255,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_city",
			CForm::required => false,
			CForm::placeholder => "City",
			CForm::required_msg => "Please enter a city.",
			CForm::maxlength => 64,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'shipping_state_id',
			CForm::required_msg => "Please select a state.",
			CForm::required => false
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_postal_code",
			CForm::required => false,
			CForm::placeholder => "Postal Code",
			CForm::number => true,
			CForm::gpc_type => TYPE_POSTAL_CODE,
			CForm::xss_filter => true,
			CForm::maxlength => 5,
			CForm::required_msg => "Please enter a zip code.",
			CForm::length => 16
		));

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::placeholder => "Optional: Gate code, house description, etc.",
			CForm::maxlength => 100,
			CForm::css_class => 'dd-strip-tags',
			CForm::name => 'shipping_address_note',
			CForm::required => false
		));

		// telephone 1
		$Form->AddElement(array(
			CForm::type => CForm::Tel,
			CForm::name => 'telephone_1',
			CForm::placeholder => "*Primary Telephone",
			CForm::required_msg => "Please enter a telephone number.",
			CForm::required => true,
			CForm::length => 18,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "telephone_1_type",
			CForm::value => 'MOBILE',
			CForm::label => 'Mobile'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "telephone_1_type",
			CForm::value => 'LAND_LINE',
			CForm::label => 'Home'
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "telephone_1_call_time",
			CForm::required => false,
			CForm::required_msg => "Please select best time to call.",
			CForm::options => array(
				'null' => 'Best Time to Call',
				'MORNING' => '9am - Noon',
				'AFTERNOON' => 'Noon - 5pm',
				'EVENING' => '5pm - 7pm',
				'NEVER' => 'Only emails please'
			)
		));

		// telephone 2
		$Form->AddElement(array(
			CForm::type => CForm::Tel,
			CForm::name => 'telephone_2',
			CForm::placeholder => "Secondary Telephone",
			CForm::required_msg => "Please enter a telephone number.",
			CForm::required => false,
			CForm::length => 18,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "telephone_2_type",
			CForm::value => 'MOBILE',
			CForm::label => 'Mobile'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "telephone_2_type",
			CForm::value => 'LAND_LINE',
			CForm::label => 'Home'
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "telephone_2_call_time",
			CForm::required => false,
			CForm::required_msg => "Please select best time to call.",
			CForm::options => array(
				'null' => 'Best Time to Call',
				'MORNING' => '9am - Noon',
				'AFTERNOON' => 'Noon - 5pm',
				'EVENING' => '5pm - 7pm',
				'NEVER' => 'Only emails please'
			)
		));

		// telephone fax
		$Form->AddElement(array(
			CForm::type => CForm::Tel,
			CForm::name => 'telephone_fax',
			CForm::placeholder => "Telephone Fax",
			CForm::length => 18,
			CForm::xss_filter => true
		));

		// enroll in platepoints
		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "enroll_in_plate_points"
		));

		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::label => "Opt-in to receive text messages. Message and data rates may apply.",
			CForm::message => 'To receive text messages, please enter a mobile number above.',
			CForm::label_css_class => "font-size-small",
			CForm::name => "sms_opt_in"
		));

		// gender
		$ddRequired = false;
		if (!empty($Form->DefaultValues['dream_rewards_version']) && $Form->DefaultValues['dream_rewards_version'] == 3)
		{
			$ddRequired = true;
		}

		$Form->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::allowAllOption => false,
			CForm::name => 'gender',
			CForm::required => false,
			CForm::required_msg => "Please select gender.",
			CForm::options => array(
				'' => 'Gender',
				'F' => 'Female',
				'M' => 'Male',
				'X' => 'Prefer Not to Say'
			)
		));

		$readOnlyRS = false;
		if (!empty($Form->DefaultValues['referral_source']) || !empty($_POST['referral_source']))
		{
			$readOnlyRS = true;
		}

		//If error, allow them to edit any mistake
		if (!empty($_POST['submit_account']))
		{
			$readOnlyRS = false;
		}

		if ($readOnlyRS)
		{
			// referral source
			$Form->AddElement(array(
				CForm::type => CForm::ReferralSourceDropDown,
				CForm::name => "referral_source",
				CForm::required => $ddRequired,
				CForm::disabled => true,
				CForm::required_msg => "Please select a source."
			));
		}
		else
		{
			// referral source
			$Form->AddElement(array(
				CForm::type => CForm::ReferralSourceDropDown,
				CForm::name => "referral_source",
				CForm::required => $ddRequired,
				CForm::required_msg => "Please select a source."
			));
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'referral_source_details',
			CForm::placeholder => 'Please enter some specifics.',
			CForm::required_msg => "Please enter a source.",
			CForm::length => 66,
			CForm::xss_filter => true,
			CForm::readonly => $readOnlyRS
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'virtual_party_source_details',
			CForm::placeholder => 'Please enter email address or name.',
			CForm::required_msg => "Please enter a source.",
			CForm::length => 66,
			CForm::xss_filter => true,
			CForm::readonly => $readOnlyRS
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'customer_referral_email',
			CForm::email => true,
			CForm::disabled => $readOnlyRS,
			CForm::placeholder => "Friend Email Address",
			CForm::length => 66,
			CForm::xss_filter => true,
			CForm::readonly => $readOnlyRS
		));

		$Form->AddElement(array(
			CForm::type => CForm::Button,
			CForm::name => "submit_account",
			CForm::css_class => "btn btn-primary btn-block btn-spinner",
			CForm::value => (($isCreate) ? 'Create account' : 'Update account')
		));

		return $Form;
	}

	/**
	 * @throws Exception
	 */
	static function _saveForm($Form, $DAO_user, $adminAdd = false, $suppressBounce = false, $suppressEmail = false, $SFICurrentValues = false, $fadminStoreID = false, $isConvertingFromPartial = false, $billingAddrRequired = true, $noAddress = false): bool
	{
		$error = false;

		if ($_POST && isset($_POST['submit_account']))
		{
			$enrollment_success = false;

			$tpl = CApp::instance()->template();

			//set the fields in the data object from the form
			$original = clone($DAO_user);
			$vals = $Form->values();

			// Try and make proper case
			$vals['firstname'] = ucfirst($vals['firstname']);
			$vals['lastname'] = $vals['lastname'];

			// Don't allow double quotes
			$vals['firstname'] = str_replace('"', "'", $vals['firstname']);
			$vals['lastname'] = str_replace('"', "'", $vals['lastname']);

			//not using this guy
			if ((!$adminAdd) && isset($vals['admin_note']))
			{
				unset($vals['admin_note']);
			}

			// Hack for HILARIOUS bug where Judy Null cannot create account
			// "Null" is converted to NULL by DataObject->setFrom()
			// Don't want to disturb setFrom since other code may rely on this behavior
			// so just catch the name and set it explicitly.
			$lastname = $vals['lastname'];

			$DAO_user->setFrom($vals, '%s', true);

			if ($lastname == "Null")
			{
				$DAO_user->lastname = $lastname;
			}

			// Check for required fields

			if (!$adminAdd && empty($DAO_user->id) && (!$_POST['customers_terms'] || ($_POST['customers_terms'] != 1 && $_POST['customers_terms'] != 'on')))
			{
				$tpl->setErrorMsg('Please read the terms and conditions and indicate that you have read them by checking the checkbox.');
				$error = true;
			}

			if (!$suppressEmail)
			{
				// are both the email address and the confirmation supplied
				if (!$Form->value('primary_email') || $Form->value('primary_email') == "")
				{
					$tpl->setErrorMsg('Please enter an email address.');
					$error = true;
				}
				else if (!$_POST['confirm_email_address'] || $_POST['confirm_email_address'] == "")
				{
					$tpl->setErrorMsg('Please enter a confirmation email address.');
					$error = true;
				}// if so do they match
				else if (strtolower($_POST['confirm_email_address']) != strtolower($Form->value('primary_email')))
				{
					$tpl->setErrorMsg('Email addresses do not match. Please enter your email address again.');
					$error = true;
				}
			}

			if (!$Form->value('firstname') || $Form->value('firstname') == "")
			{
				$tpl->setErrorMsg('Please enter a first name.');
				$error = true;
			}

			if (!$Form->value('lastname') || $Form->value('lastname') == "")
			{
				$tpl->setErrorMsg('Please enter a last name.');
				$error = true;
			}

			if ($billingAddrRequired && (!$Form->value('address_line1') || $Form->value('address_line1') == ""))
			{
				$tpl->setErrorMsg('Please enter a street address or Post Office box.');
				$error = true;
			}

			if ($billingAddrRequired && (!$Form->value('city') || $Form->value('city') == ""))
			{
				$tpl->setErrorMsg('Please enter a city.');
				$error = true;
			}

			if ($billingAddrRequired && (!$Form->value('state_id') || $Form->value('state_id') == ""))
			{
				$tpl->setErrorMsg('Please choose a state.');
				$error = true;
			}

			if ($billingAddrRequired && (!$Form->value('postal_code') || $Form->value('postal_code') == ""))
			{
				$tpl->setErrorMsg('Please enter a ZIP/Postal code.');
				$error = true;
			}

			if (!$Form->value('telephone_1') || $Form->value('telephone_1') == "")
			{
				$tpl->setErrorMsg('Please enter a telephone number.');
				$error = true;
			}
			else if (!ValidationRules::telephoneCheck($Form->value('telephone_1')))
			{
				$tpl->setErrorMsg('Telephone numbers must be in the format: ###-###-####');
				$error = true;
			}

			$temp = $Form->value('telephone_2');
			if (!empty($temp) && !ValidationRules::telephoneCheck($temp))
			{
				$tpl->setErrorMsg('Telephone numbers must be in the format: ###-###-####');
				$error = true;
			}

			if (false && !CApp::$adminView)
			{
				if ($Form->value('birthday_month') == null || $Form->value('birthday_month') == "")
				{
					$tpl->setErrorMsg('Please choose a birthday month.');
					$error = true;
				}

				if ($Form->value('birthday_year') == null || $Form->value('birthday_year') == "")
				{
					$tpl->setErrorMsg('Please choose a birthday year.');
					$error = true;
				}

				if ($Form->value('how_many_at_home') == null || $Form->value('how_many_at_home') == "")
				{
					$tpl->setErrorMsg('Please choose a how many at home.');
					$error = true;
				}

				if ($Form->value('how_many_under_18_at_home') == null || $Form->value('how_many_under_18_at_home') == "")
				{
					$tpl->setErrorMsg('Please choose a how many under 18 at home.');
					$error = true;
				}
			}

			$szUsername = explode('@', $Form->value('primary_email'));
			$szUsername = $szUsername[0];

			//validate password, if it is changing
			if ($Form->value('password'))
			{
				$szPassword = $Form->value('password');
				if (!ValidationRules::isCrackproof($szPassword))
				{
					$tpl->setErrorMsg('Passwords cannot be based on dictionary words.');
					$error = true;
				}
				else if (!empty($szUsername) && ((str_contains(strtolower($szPassword), strtolower($Form->value('firstname')))) || (str_contains(strtolower($szPassword), strtolower($Form->value('lastname')))) || (str_contains(strtolower($szPassword), strtolower($szUsername)))))
				{
					$tpl->setErrorMsg('Passwords cannot be based on your name or email address.');
					$error = true;
				}
				else if (empty($szPassword) || ($szPassword != $Form->value('password_confirm')))
				{
					$tpl->setErrorMsg('Passwords do not match. Please enter your password again.');
					$error = true;
				}
				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && strlen($szPassword) < 10)
				{
					$tpl->setErrorMsg('Passwords for Store and Home Office  personnel must be at least 10 characters long.');
					$error = true;
				}
				$hasNumber = false;
				$hasLetter = false;
				if (preg_match('/[1-9]/', $szPassword))
				{
					$hasNumber = true;
				}
				if (preg_match('/[a-z]/', $szPassword) || preg_match('/[A-Z]/', $szPassword))
				{
					$hasLetter = true;
				}
				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && !$hasNumber)
				{
					$tpl->setErrorMsg('Passwords for Store and Home Office  personnel must contain at least 1 number.');
					$error = true;
				}
				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && !$hasLetter)
				{
					$tpl->setErrorMsg('Passwords for Store and Home Office  personnel must contain at least 1 letter.');
					$error = true;
				}

				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && !CPasswordPolicy::passwordPassesUniquenessRules($DAO_user->id, $szPassword))
				{
					$tpl->setErrorMsg('The password must be different than the last 4 passwords used.');
					$error = true;
				}
			}

			if (!$suppressEmail && !ValidationRules::validateEmail($Form->value('primary_email')))
			{
				// TODO: need to erase the submitted value as it may be a XSS attack

				$tpl->setErrorMsg('That appears to be an invalid email address.');
				$error = true;
			}

			if (!$suppressEmail && empty($DAO_user->id) && !isset($szPassword))
			{
				$tpl->setErrorMsg('You must enter a password.');
				$error = true;
			}

			// Referral Source
			$arSources = array();

			$bind_customer_referral = false;
			$referring_customer_name = false;

			if (!empty($_POST['referral_source']))
			{
				if ($_POST['referral_source'] == CUserReferralSource::OTHER)
				{
					$arSources[CUserReferralSource::OTHER] = $_POST['referral_source_details'];
				}
				if ($_POST['referral_source'] == CUserReferralSource::VIRTUAL_PARTY)
				{
					$arSources[CUserReferralSource::VIRTUAL_PARTY] = $_POST['virtual_party_source_details'];
				}
				else if ($_POST['referral_source'] == CUserReferralSource::CUSTOMER_REFERRAL)
				{
					$incomingEmail = null;

					if (isset($_POST['customer_referral_email']))
					{
						$incomingEmail = $_POST['customer_referral_email'];
					}

					if (empty($incomingEmail))
					{
						$tpl->setErrorMsg('You must provide a valid customer email for a customer referral.');
						$error = true;
					}
					else
					{
						$TestUser = DAO_CFactory::create('user');
						$TestUser->primary_email = $incomingEmail;
						if ($TestUser->find(true))
						{
							$arSources[CUserReferralSource::CUSTOMER_REFERRAL] = $TestUser->primary_email;
							$referring_customer_name = $TestUser->firstname . ' ' . $TestUser->lastname;
							$bind_customer_referral = $TestUser->id;
						}
						else
						{
							$tpl->setErrorMsg('The email address for the customer referral does not appear to be a valid account email.');
							$error = true;
						}
					}
				}
				else
				{
					$arSources[$_POST['referral_source']] = "";
				}
			}

			//insert or update
			if (!$error)
			{
				$isTrulyNewUser = empty($DAO_user->id);
				$updatingPartial = false;

				$platePointsEnrollmentMessage = "";

				// check with primary_email for partial account
				// if it exists then update user and insert user_login and address
				if ($DAO_user->partial_account_exists())
				{
					$isTrulyNewUser = false;
					$updatingPartial = true;
				}

				if ($isTrulyNewUser)
				{
					/// INSERT
					if (!$DAO_user->exists() || $suppressEmail)
					{
						if ($suppressEmail)
						{
							$szPassword = CUser::getRandomPwd();
						}

						$rslt = $DAO_user->insert($szPassword, CUser::CUSTOMER, 'YES');

						$customer_referral_id = false;
						if ($rslt !== false)
						{
							if (!$noAddress)
							{
								$rslt = self::_saveAddresses($Form, $DAO_user);
							}

							//PLATEPOINTS enrollee?
							if (false && isset($_POST['enroll_in_plate_points']))
							{
								$enrollment_success = CPointsUserHistory::handleEvent($DAO_user, CPointsUserHistory::OPT_IN);

								if ($enrollment_success)
								{
									if (!$adminAdd)
									{
										$platePointsEnrollmentMessage = "<br />You have been enrolled in our <i>PLATEPOINTS</i> program.";
									}
									else
									{
										$platePointsEnrollmentMessage = "<br />The Guest has been enrolled in the <i>PLATEPOINTS</i> program.";
									}
								}
								else
								{
									$tpl->setErrorMsg(CPointsUserHistory::getLastOperationResult());
								}
							}

							// agree to delayed payment preference
							if (!empty($_POST['tc_delayed_payment']))
							{
								$DAO_user->setUserPreference(CUser::TC_DELAYED_PAYMENT_AGREE, 1);
							}

							if (!empty($_POST['sms_opt_in']))
							{
								$DAO_user->setUserPreference(CUser::TEXT_MESSAGE_OPT_IN, CUser::OPTED_IN);
							}
							else
							{
								$DAO_user->setUserPreference(CUser::TEXT_MESSAGE_OPT_IN, CUser::OPTED_OUT);
							}

							// Agree to Dream Dinners T&C, should be true here anyhow
							if (!empty($_POST['customers_terms']))
							{
								$DAO_user->setUserPreference(CUser::TC_DREAM_DINNERS_AGREE, 1);
							}

							// -------------------------------------------------referral handling ----------------
							// ---------------- this repeated in update section - consider moving to shared function
							$inviting_user_id = false;
							if ($bind_customer_referral && $rslt !== false)
							{
								// Valid email was passed in

								if (!$adminAdd && CUserReferralSource::is_referral_V2_active())
								{
									// the invite cookie exists and the customer is creating the account

									$referral_org_id = $_COOKIE['RSV2_Origination_code'];
									$inviting_user_id = $_COOKIE['Inviting_user_id'];

									$RefObj = DAO_CFactory::create('customer_referral');
									$RefObj->origination_uid = $referral_org_id;

									if ($RefObj->find(true))
									{
										//we found the referral
										$sessionObj = DAO_CFactory::create('session');
										// Possible TODO:  May need to determine session type and find hostess instead of using referral owner
										$sessionObj->query("select s.id, s.session_type, 
                                                            IF(s.session_type = 'DREAM_TASTE', IF(dtep.can_rsvp_only = 1, 'FNO', IF(dtep.host_required = 1, 'DREAM_TASTE_STANDARD', 'DREAM_TASTE_OPEN_HOUSE')), s.session_type) as full_session_type
                                                            from session s 
                                                            left join session_properties sp on sp.session_id = s.id
                                                            left join dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id
                                                            where s.id = {$RefObj->referrer_session_id}");
										$sessionObj->fetch();

										if ($bind_customer_referral == $inviting_user_id)
										{
											// the passed in user matches the referral
											if ($RefObj->referral_status < 2)
											{
												// TODO: if the org type - 6 (shared link) then we also need to update the email address and name of the invited guest

												$RefObj->referral_status = 2;
												$RefObj->referred_user_id = $DAO_user->id;
												$RefObj->update();
												$customer_referral_id = $RefObj->id;

												// session based referral is set so remove cookies
												CBrowserSession::setValue('RSV2_Origination_code', false);
												CBrowserSession::setValue('Inviting_user_id', false);
											}
											else
											{
												// the passed in user does not match the referral row so just create a direct referral
												list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
												// direct referral has user_id so remove cookies
												CBrowserSession::setValue('RSV2_Origination_code', false);
												CBrowserSession::setValue('Inviting_user_id', false);
											}
										}
									}
									else
									{
										// referral row not found so just create a direct referral
										list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
										// direct referral has user_id so remove cookies
										CBrowserSession::setValue('RSV2_Origination_code', false);
										CBrowserSession::setValue('Inviting_user_id', false);
									} // referraL not found
								} // if referral is active
								else
								{
									// referral cookie not found so add direct referral
									list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
									// direct referral has user_id so remove cookies
									CBrowserSession::setValue('RSV2_Origination_code', false);
									CBrowserSession::setValue('Inviting_user_id', false);
								} // referraL not found
							} // if bind customer exists

							if ($rslt !== false)
							{
								CUserReferralSource::insertSources($DAO_user->id, $arSources, $inviting_user_id, $customer_referral_id);
								// -------------------------------------------------end referral handling ----------------

								CUserData::saveSFIFormElementsNew($Form, $DAO_user, $SFICurrentValues);

								// check profile data for birthday month and if it is current
								// award the guest. All existing guests would have been rewarded 7 days before the month start
								if ($enrollment_success)
								{
									$month = $Form->value('birthday_month');

									if (CPointsUserHistory::isElgibleForBirthdayRewardAtEnrollment($DAO_user->home_store_id, $month, $DAO_user->id))
									{
										$metaData = CPointsUserHistory::getEventMetaData(CPointsUserHistory::BIRTHDAY_MONTH);
										$eventComment = 'Earned $' . $metaData['credit'] . ' birthday Dinner Dollars!';

										$enrollment_success = CPointsUserHistory::handleEvent($DAO_user, CPointsUserHistory::BIRTHDAY_MONTH, array(
											'comments' => $eventComment,
											'year' => date('Y'),
											'month' => $month
										));
									}
								}
							}
						}

						if ($rslt !== false)
						{
							if ($adminAdd)
							{
								if ($suppressEmail)
								{
									$login = DAO_CFactory::create('user_login');
									$login->user_id = $DAO_user->id;
									$login->find(true);
									$msg = 'The account has been created. Account ID ' . $DAO_user->id . '. The generated user name for login is <b>' . $login->ul_username . '</b>. The generated password is <b>' . $szPassword . '</b>.';
									$tpl->setStatusMsg($msg . $platePointsEnrollmentMessage);
								}
								else
								{
									$tpl->setToastMsg(array('message' => 'The account has been created. Account ID ' . $DAO_user->id . '. ' . $platePointsEnrollmentMessage));
								}
							}
							else
							{
								$tpl->setToastMsg(array('message' => 'Welcome to Dream Dinners, your account has been created. ' . $platePointsEnrollmentMessage));
							}

							if (!$suppressEmail && !$enrollment_success)
							{
								//send email
								CUser::sendConfirmationEmail($DAO_user);
							}

							//forward to confirmation page
							if (!$suppressBounce)
							{
								if ($adminAdd)
								{
									CApp::instance()->bounce(self::$forwardTo . '?id=' . $DAO_user->id, true);
								}
								else
								{
									CApp::instance()->bounce(self::$forwardTo, true);
								}
							}
						}
						else
						{
							if ($adminAdd)
							{
								$tpl->setStatusMsg('That account could not be created.');
							}
							else
							{
								$tpl->setErrorMsg('Your account could not be created.');
							}
							$error = true;
						}
					}
					else
					{
						$tpl->setErrorMsg('An account with that email address is already registered.');
						$error = true;
					}
				}
				else
				{
					//// Updating the account

					// CES : don't update - this shouldn't be editable so data can't be massaged after the fact
					// CUserReferralSource::insertSources( $User->id, $arSources );

					$emailAddressChanged = $DAO_user->hasPrimaryEmailChanged();

					if ($emailAddressChanged && !empty($DAO_user->primary_email) && !$updatingPartial)
					{
						if ($DAO_user->exists())
						{
							$tpl->setErrorMsg('An account with that email address is already registered.');
							$error = true;

							return $error;
						}
					}

					if ($emailAddressChanged && CCorporateCrateClient::isEmailAddressCorporateCrateEligible($DAO_user->primary_email))
					{
						$DAO_user->secondary_email = $DAO_user->primary_email;
					}

					if ($updatingPartial)
					{
						$rslt = $DAO_user->convert_partial($Form->value('password'));
					}
					else
					{
						$rslt = $DAO_user->update($original);
					}

					// Customer is changing their own account
					if (!$adminAdd)
					{
						if (($rslt !== false) && array_key_exists('password', $Form->values()) && $Form->value('password'))
						{
							//$User->resetPwd($User->primary_email, $Form->value('password'));
							$DAO_user->updatePassword($Form->value('password'));
						}
					}
					else
					{
						if (($rslt !== false) && array_key_exists('password', $Form->values()) && $Form->value('password'))
						{
							$DAO_user->updatePassword($Form->value('password'));
						}
					}

					if ($rslt !== false)
					{
						$rslt = self::_saveAddresses($Form, $DAO_user);

						// -------------------------------------------------referral handling ----------------
						// ---------------- this repeated in create section - consider moving to shared function
						$inviting_user_id = false;
						$customer_referral_id = false;
						if ($bind_customer_referral && $rslt !== false)
						{
							// Valid email was passed in

							if (!$adminAdd && CUserReferralSource::is_referral_V2_active())
							{
								// the invite cookie exists and the customer is creating the account

								$referral_org_id = $_COOKIE['RSV2_Origination_code'];
								$inviting_user_id = $_COOKIE['Inviting_user_id'];

								$RefObj = DAO_CFactory::create('customer_referral');
								$RefObj->origination_code = $referral_org_id;

								if ($RefObj->find(true))
								{
									//we found the referral
									$sessionObj = DAO_CFactory::create('session');
									// Possible TODO:  May need to determine session type and find hostess instead of using referral owner
									$sessionObj->query("select s.id, s.session_type,
										IF(s.session_type = 'DREAM_TASTE', IF(dtep.can_rsvp_only = 1, 'FNO', IF(dtep.host_required = 1, 'DREAM_TASTE_STANDARD', 'DREAM_TASTE_OPEN_HOUSE')), s.session_type) as full_session_type
										from session s
										left join session_properties sp on sp.session_id = s.id
										left join dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id
										where s.id = {$RefObj->referrer_session_id}");
									$sessionObj->fetch();

									if ($bind_customer_referral == $inviting_user_id)
									{
										// the passed in user matches the referral
										if ($RefObj->referral_status < 2)
										{
											$RefObj->referral_status = 2;
											$RefObj->referred_user_id = $DAO_user->id;
											$RefObj->update();
											$customer_referral_id = $RefObj->id;

											// session based referral is set so remove cookies
											CBrowserSession::setValue('RSV2_Origination_code', false);
											CBrowserSession::setValue('Inviting_user_id', false);
										}
										else
										{
											// the passed in user does not match the referral row so just create a direct referral
											list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
											// direct referral has user_id so remove cookies
											CBrowserSession::setValue('RSV2_Origination_code', false);
											CBrowserSession::setValue('Inviting_user_id', false);
										}
									}
								}
								else
								{
									// referral row not found so just create a direct referral
									list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
									// direct referral has user_id so remove cookies
									CBrowserSession::setValue('RSV2_Origination_code', false);
									CBrowserSession::setValue('Inviting_user_id', false);
								} // referraL not found
							} // if referral is active
							else
							{
								// passed in and email but no cookie so create a new direct referral
								list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
								// direct referral has user_id so remove cookies
								CBrowserSession::setValue('RSV2_Origination_code', false);
								CBrowserSession::setValue('Inviting_user_id', false);
							}
						} // if bind customer exists

						if ($rslt !== false)
						{
							CUserReferralSource::insertSources($DAO_user->id, $arSources, $inviting_user_id, $customer_referral_id);
							// -------------------------------------------------end referral handling ----------------

							CUserData::saveSFIFormElementsNew($Form, $DAO_user, $SFICurrentValues);

							//PLATEPOINTS enrollee?
							if (false && isset($_POST['enroll_in_plate_points']))
							{
								$enrollment_success = CPointsUserHistory::handleEvent($DAO_user, CPointsUserHistory::OPT_IN);

								if ($enrollment_success)
								{
									if (!$adminAdd)
									{
										$platePointsEnrollmentMessage = "<br />You have been enrolled in our <i>PLATEPOINTS</i> program.";
									}
									else
									{
										$platePointsEnrollmentMessage = "<br />The Guest has been enrolled in the <i>PLATEPOINTS</i> program.";
									}
								}
								else
								{
									$opRes = CPointsUserHistory::getLastOperationResult();
									$res1 = array_shift($opRes);
									$tpl->setErrorMsg($res1['message']);
								}

								// check profile data for birthday month and if it is current
								// award the guest. All existing guests would have been rewarded 7 days before the month start
								if ($enrollment_success)
								{
									$month = $Form->value('birthday_month');

									if (CPointsUserHistory::isElgibleForBirthdayRewardAtEnrollment($DAO_user->home_store_id, $month, $DAO_user->id))
									{
										$enrollment_success = CPointsUserHistory::handleEvent($DAO_user, CPointsUserHistory::BIRTHDAY_MONTH);
									}
								}
							}
						}
					}

					if ($rslt !== false)
					{
						if ($updatingPartial)
						{
							if (!$adminAdd)
							{
								$tpl->setToastMsg(array('message' => 'Your account has been updated.' . $platePointsEnrollmentMessage));
							}
							else
							{
								$tpl->setToastMsg(array('message' => 'The partial account guest has been upgraded to a standard guest.'));
							}
						}
						else
						{
							if (!$adminAdd)
							{
								$tpl->setToastMsg(array('message' => 'Your account has been updated.' . $platePointsEnrollmentMessage));
							}
							else
							{
								$tpl->setToastMsg(array('message' => 'The account has been updated.' . $platePointsEnrollmentMessage));
							}
						}

						if (!$adminAdd && !$enrollment_success)
						{
							CUser::sendAccountChangedEmail($DAO_user);
						}

						//forward to confirmation page
						if (!$suppressBounce)
						{
							if ($adminAdd)
							{
								CApp::instance()->bounce(self::$forwardTo . '?id=' . $DAO_user->id, true);
							}
							else
							{
								CApp::instance()->bounce(self::$forwardTo, true);
							}
						}
					}
					else
					{
						if (!$adminAdd)
						{
							$tpl->setStatusMsg('Your account could not be updated.');
						}
						else
						{
							$tpl->setStatusMsg('The account could not be updated.');
						}
						$error = true;
					}
				}
			}
		}

		return $error;
	}

	static function _saveFormSimplified($Form, $DAO_user, $adminAdd = false, $suppressBounce = false, $suppressEmail = false, $SFICurrentValues = false, $fadminStoreID = false, $isConvertingFromPartial = false)
	{
		$error = false;

		if ($_POST && isset($_POST['submit_account']))
		{
			$enrollment_success = false;

			$tpl = CApp::instance()->template();

			//set the fields in the data object from the form
			$original = clone($DAO_user);
			$vals = $Form->values();

			// Try and make proper case
			$vals['firstname'] = ucfirst($vals['firstname']);
			$vals['lastname'] = $vals['lastname'];

			// Don't allow double quotes
			$vals['firstname'] = str_replace('"', "'", $vals['firstname']);
			$vals['lastname'] = str_replace('"', "'", $vals['lastname']);

			//not using this guy
			if ((!$adminAdd) && isset($vals['admin_note']))
			{
				unset($vals['admin_note']);
			}

			// Hack for HILARIOUS bug where Judy Null cannot create account
			// "Null" is converted to NULL by DataObject->setFrom()
			// Don't want to disturb setFrom since other code may rely on this behavior
			// so just catch the name and set it explicitly.
			$lastname = $vals['lastname'];

			$DAO_user->setFrom($vals, '%s', true);

			if ($lastname == "Null")
			{
				$DAO_user->lastname = $lastname;
			}

			// Check for required fields

			if (!$adminAdd && empty($DAO_user->id) && (!$_POST['customers_terms'] || ($_POST['customers_terms'] != 1 && $_POST['customers_terms'] != 'on')))
			{
				$tpl->setErrorMsg('Please read the terms and conditions and indicate that you have read them by checking the checkbox.');
				$error = true;
			}

			if (!$suppressEmail)
			{
				// are both the email address and the confirmation supplied
				if (!$Form->value('primary_email') || $Form->value('primary_email') == "")
				{
					$tpl->setErrorMsg('Please enter an email address.');
					$error = true;
				}
				else if (!$_POST['confirm_email_address'] || $_POST['confirm_email_address'] == "")
				{
					$tpl->setErrorMsg('Please enter a confirmation email address.');
					$error = true;
				}// if so do they match
				else if (strtolower($_POST['confirm_email_address']) != strtolower($Form->value('primary_email')))
				{
					$tpl->setErrorMsg('Email addresses do not match. Please enter your email address again.');
					$error = true;
				}
			}

			if (!$Form->value('firstname') || $Form->value('firstname') == "")
			{
				$tpl->setErrorMsg('Please enter a first name.');
				$error = true;
			}

			if (!$Form->value('lastname') || $Form->value('lastname') == "")
			{
				$tpl->setErrorMsg('Please enter a last name.');
				$error = true;
			}

			if (!$Form->value('telephone_1') || $Form->value('telephone_1') == "")
			{
				$tpl->setErrorMsg('Please enter a telephone number.');
				$error = true;
			}
			else if (!ValidationRules::telephoneCheck($Form->value('telephone_1')))
			{
				$tpl->setErrorMsg('Telephone numbers must be in the format: ###-###-####');
				$error = true;
			}

			$szUsername = explode('@', $Form->value('primary_email'));
			$szUsername = $szUsername[0];

			//validate password, if it is changing
			if ($Form->value('password'))
			{
				$szPassword = $Form->value('password');
				if (!ValidationRules::isCrackproof($szPassword))
				{
					$tpl->setErrorMsg('Passwords cannot be based on dictionary words.');
					$error = true;
				}
				else if (!empty($szUsername) && ((strstr(strtolower($szPassword), strtolower($Form->value('firstname'))) !== false) || (strstr(strtolower($szPassword), strtolower($Form->value('lastname'))) !== false) || (strstr(strtolower($szPassword), strtolower($szUsername)) !== false)))
				{
					$tpl->setErrorMsg('Passwords cannot be based on your name or email address.');
					$error = true;
				}
				else if (empty($szPassword) || ($szPassword != $Form->value('password_confirm')))
				{
					$tpl->setErrorMsg('Passwords do not match. Please enter your password again.');
					$error = true;
				}
				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && strlen($szPassword) < 10)
				{
					$tpl->setErrorMsg('Passwords for Store and Home Office personnel must be at least 10 characters long.');
					$error = true;
				}
				$hasNumber = false;
				$hasLetter = false;
				if (preg_match('/[1-9]/', $szPassword))
				{
					$hasNumber = true;
				}
				if (preg_match('/[a-z]/', $szPassword) || preg_match('/[A-Z]/', $szPassword))
				{
					$hasLetter = true;
				}
				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && !$hasNumber)
				{
					$tpl->setErrorMsg('Passwords for Store and Home Office  personnel must contain at least 1 number.');
					$error = true;
				}
				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && !$hasLetter)
				{
					$tpl->setErrorMsg('Passwords for Store and Home Office  personnel must contain at least 1 letter.');
					$error = true;
				}

				if (!empty($DAO_user->id) && $DAO_user->user_type != CUser::GUEST && $DAO_user->user_type != CUser::CUSTOMER && !CPasswordPolicy::passwordPassesUniquenessRules($DAO_user->id, $szPassword))
				{
					$tpl->setErrorMsg('The password must be different than the last 4 passwords used.');
					$error = true;
				}
			}

			if (!$suppressEmail && !ValidationRules::validateEmail($Form->value('primary_email')))
			{
				// TODO: need to erase the submitted value as it may be a XSS attack

				$tpl->setErrorMsg('That appears to be an invalid email address.');
				$error = true;
			}

			if (!$suppressEmail && empty($DAO_user->id) && !isset($szPassword))
			{
				$tpl->setErrorMsg('You must enter a password.');
				$error = true;
			}

			// Referral Source
			$arSources = array();

			$bind_customer_referral = false;
			$referring_customer_name = false;

			if (!empty($_POST['referral_source']))
			{
				if ($_POST['referral_source'] == CUserReferralSource::OTHER)
				{
					$arSources[CUserReferralSource::OTHER] = $_POST['referral_source_details'];
				}
				if ($_POST['referral_source'] == CUserReferralSource::VIRTUAL_PARTY)
				{
					$arSources[CUserReferralSource::VIRTUAL_PARTY] = $_POST['virtual_party_source_details'];
				}
				else if ($_POST['referral_source'] == CUserReferralSource::CUSTOMER_REFERRAL)
				{
					$incomingEmail = null;

					if (isset($_POST['customer_referral_email']))
					{
						$incomingEmail = $_POST['customer_referral_email'];
					}

					if (empty($incomingEmail))
					{
						$tpl->setErrorMsg('You must provide a valid customer email for a customer referral.');
						$error = true;
					}
					else
					{
						$TestUser = DAO_CFactory::create('user');
						$TestUser->primary_email = $incomingEmail;
						if ($TestUser->find(true))
						{
							$arSources[CUserReferralSource::CUSTOMER_REFERRAL] = $TestUser->primary_email;
							$referring_customer_name = $TestUser->firstname . ' ' . $TestUser->lastname;
							$bind_customer_referral = $TestUser->id;
						}
						else
						{
							$tpl->setErrorMsg('The email address for the customer referral does not appear to be a valid account email.');
							$error = true;
						}
					}
				}
				else
				{
					$arSources[$_POST['referral_source']] = "";
				}
			}

			//insert or update
			if (!$error)
			{
				$isTrulyNewUser = empty($DAO_user->id);
				$updatingPartial = false;

				$platePointsEnrollmentMessage = "";

				// check with pimary_email for partial account
				// if it exists then update user and insert user_login and address
				if ($DAO_user->partial_account_exists())
				{
					$isTrulyNewUser = false;
					$updatingPartial = true;
				}

				if ($isTrulyNewUser)
				{
					/// INSERT
					if (!$DAO_user->exists() || $suppressEmail)
					{
						if ($suppressEmail)
						{
							$szPassword = CUser::getRandomPwd();
						}

						$rslt = $DAO_user->insert($szPassword, CUser::CUSTOMER, 'YES');

						$CartObj = CCart2::instance();
						if (!is_null($CartObj))
						{
							$OrderObj = $CartObj->getOrder();
							if (!is_null($OrderObj))
							{
								$StoreObj = $OrderObj->getStore();
								if (!is_null($StoreObj) && !empty($StoreObj->id))
								{
									$DAO_user->setHomeStore($StoreObj->id);
								}
							}
						}

						$customer_referral_id = false;
						if ($rslt !== false)
						{
							//PLATEPOINTS enrollee?
							if (false && isset($_POST['enroll_in_plate_points']))
							{
								$enrollment_success = CPointsUserHistory::handleEvent($DAO_user, CPointsUserHistory::OPT_IN);

								if ($enrollment_success)
								{
									if (!$adminAdd)
									{
										$platePointsEnrollmentMessage = "<br />You have been enrolled in our <i>PLATEPOINTS</i> program.";
									}
									else
									{
										$platePointsEnrollmentMessage = "<br />The Guest has been enrolled in the <i>PLATEPOINTS</i> program.";
									}
								}
								else
								{
									$tpl->setErrorMsg(CPointsUserHistory::getLastOperationResult());
								}
							}

							// Agree to Dream Dinners T&C, should be true here anyhow
							if (!empty($_POST['customers_terms']))
							{
								$DAO_user->setUserPreference(CUser::TC_DREAM_DINNERS_AGREE, 1);
							}

							// -------------------------------------------------referral handling ----------------
							// ---------------- this repeated in update section - consider moving to shared function
							$inviting_user_id = false;
							if ($bind_customer_referral && $rslt !== false)
							{
								// Valid email was passed in

								if (!$adminAdd && CUserReferralSource::is_referral_V2_active())
								{
									// the invite cookie exists and the customer is creating the account

									$referral_org_id = $_COOKIE['RSV2_Origination_code'];
									$inviting_user_id = $_COOKIE['Inviting_user_id'];

									$RefObj = DAO_CFactory::create('customer_referral');
									$RefObj->origination_uid = $referral_org_id;

									if ($RefObj->find(true))
									{
										//we found the referral
										$sessionObj = DAO_CFactory::create('session');
										// Possible TODO:  May need to determine session type and find hostess instead of using referral owner
										$sessionObj->query("select s.id, s.session_type, 
                                                            IF(s.session_type = 'DREAM_TASTE', IF(dtep.can_rsvp_only = 1, 'FNO', IF(dtep.host_required = 1, 'DREAM_TASTE_STANDARD', 'DREAM_TASTE_OPEN_HOUSE')), s.session_type) as full_session_type
                                                            from session s 
                                                            left join session_properties sp on sp.session_id = s.id
                                                            left join dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id
                                                            where s.id = {$RefObj->referrer_session_id}");
										$sessionObj->fetch();

										if ($bind_customer_referral == $inviting_user_id)
										{
											// the passed in user matches the referral
											if ($RefObj->referral_status < 2)
											{
												// TODO: if the org type - 6 (shared link) then we also need to update the email address and name of the invited guest

												$RefObj->referral_status = 2;
												$RefObj->referred_user_id = $DAO_user->id;
												$RefObj->update();
												$customer_referral_id = $RefObj->id;

												// session based referral is set so remove cookies
												CBrowserSession::setValue('RSV2_Origination_code', false);
												CBrowserSession::setValue('Inviting_user_id', false);
											}
											else
											{
												// the passed in user does not match the referral row so just create a direct referral
												list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
												// direct referral has user_id so remove cookies
												CBrowserSession::setValue('RSV2_Origination_code', false);
												CBrowserSession::setValue('Inviting_user_id', false);
											}
										}
									}
									else
									{
										// referral row not found so just create a direct referral
										list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
										// direct referral has user_id so remove cookies
										CBrowserSession::setValue('RSV2_Origination_code', false);
										CBrowserSession::setValue('Inviting_user_id', false);
									} // referraL not found
								} // if referral is active
								else
								{
									// referral cookie not found so add direct referral
									list($customer_referral_id, $new_origination_id) = CCustomerReferral::newDirectReferralFromRegistrationForm($DAO_user, $bind_customer_referral, $referring_customer_name);
									// direct referral has user_id so remove cookies
									CBrowserSession::setValue('RSV2_Origination_code', false);
									CBrowserSession::setValue('Inviting_user_id', false);
								} // referraL not found
							} // if bind customer exists

							if ($rslt !== false)
							{
								CUserReferralSource::insertSources($DAO_user->id, $arSources, $inviting_user_id, $customer_referral_id);
								// -------------------------------------------------end referral handling ----------------

								CUserData::saveSFIFormElementsNew($Form, $DAO_user, $SFICurrentValues);

								// check profile data for birthday month and if it is current
								// award the guest. All existing guests would have been rewarded 7 days before the month start
								if ($enrollment_success)
								{
									$month = $Form->value('birthday_month');

									if (CPointsUserHistory::isElgibleForBirthdayRewardAtEnrollment($DAO_user->home_store_id, $month, $DAO_user->id))
									{
										$metaData = CPointsUserHistory::getEventMetaData(CPointsUserHistory::BIRTHDAY_MONTH);
										$eventComment = 'Earned $' . $metaData['credit'] . ' birthday Dinner Dollars!';

										$enrollment_success = CPointsUserHistory::handleEvent($DAO_user, CPointsUserHistory::BIRTHDAY_MONTH, array(
											'comments' => $eventComment,
											'year' => date('Y'),
											'month' => $month
										));
									}
								}
							}
						}

						if ($rslt !== false)
						{
							if ($adminAdd)
							{
								if ($suppressEmail)
								{
									$login = DAO_CFactory::create('user_login');
									$login->user_id = $DAO_user->id;
									$login->find(true);
									$msg = 'The account has been created. Account ID ' . $DAO_user->id . '. The generated user name for login is <b>' . $login->ul_username . '</b>. The generated password is <b>' . $szPassword . '</b>.';
									$tpl->setStatusMsg($msg . $platePointsEnrollmentMessage);
								}
								else
								{
									$tpl->setToastMsg(array('message' => 'The account has been created. Account ID ' . $DAO_user->id . '. ' . $platePointsEnrollmentMessage));
								}
							}
							else
							{
								$tpl->setToastMsg(array('message' => 'Welcome to Dream Dinners, your account has been created. ' . $platePointsEnrollmentMessage));
							}

							if (!$suppressEmail && !$enrollment_success)
							{
								//send email
								CUser::sendConfirmationEmail($DAO_user);
							}

							//forward to confirmation page
							if (!$suppressBounce)
							{
								if ($adminAdd)
								{
									CApp::instance()->bounce(self::$forwardTo . '?id=' . $DAO_user->id, true);
								}
								else
								{
									CApp::instance()->bounce(self::$forwardTo, true);
								}
							}
						}
						else
						{
							if ($adminAdd)
							{
								$tpl->setStatusMsg('That account could not be created.');
							}
							else
							{
								$tpl->setErrorMsg('Your account could not be created.');
							}
							$error = true;
						}
					}
					else
					{
						$tpl->setErrorMsg('An account with that email address is already registered.');
						$error = true;
					}
				}
			}
		}

		return $error;
	}

	static function _saveFormPartial($Form, $User, $fadminStoreID = false)
	{
		$error = false;

		if ($_POST && isset($_POST['submit_account']))
		{
			$tpl = CApp::instance()->template();

			//set the fields in the data object from the form
			$original = clone($User);
			$vals = $Form->values();

			// Try and make proper case
			$vals['firstname'] = CTemplate::ucwords($vals['firstname']);
			$vals['lastname'] = CTemplate::ucwords($vals['lastname']);

			// Hack for HILARIOUS bug where Judy Null cannot create account
			// "Null" is converted to NULL by DataObject->setFrom()
			// Don't want to disturb setFrom since other code may rely on this behavior
			// so just catch the name and set it explicitly.
			$lastname = $vals['lastname'];
			$User->setFrom($vals);
			if ($lastname == "Null")
			{
				$User->lastname = $lastname;
			}

			// are both the email address and the confirmation supplied
			if (!$Form->value('primary_email') || $Form->value('primary_email') == "")
			{
				$tpl->setErrorMsg('Please enter an email address.');
				$error = true;
			}
			else if (!$_POST['confirm_email_address'] || $_POST['confirm_email_address'] == "")
			{
				$tpl->setErrorMsg('Please enter a confirmation email address.');
				$error = true;
			}// if so do they match
			else if ($_POST['confirm_email_address'] != $Form->value('primary_email'))
			{
				$tpl->setErrorMsg('Email addresses do not match. Please enter your email address again.');
				$error = true;
			}

			if (!$Form->value('firstname') || $Form->value('firstname') == "")
			{
				$tpl->setErrorMsg('Please enter a first name.');
				$error = true;
			}

			if (!$Form->value('lastname') || $Form->value('lastname') == "")
			{
				$tpl->setErrorMsg('Please enter a last name.');
				$error = true;
			}

			if (!ValidationRules::validateEmail($Form->value('primary_email')))
			{
				$tpl->setErrorMsg('That appears to be an invalid email address.');
				$error = true;
			}

			//insert or update
			if (!$error)
			{
				$emailAddressChanged = $User->hasPrimaryEmailChanged();

				if ($emailAddressChanged && !empty($User->primary_email))
				{
					if ($User->exists())
					{
						$tpl->setErrorMsg('An account with that email address is already registered.');
						$error = true;

						return $error;
					}
				}

				$rslt = $User->update($original);

				if ($rslt !== false)
				{
					$tpl->setToastMsg(array('message' => 'The account has been updated.'));
				}
				else
				{
					$tpl->setStatusMsg('The account could not be updated.');
					$error = true;
				}
			}
		}

		return $error;
	}

	static function setUpReferral($tpl)
	{
		$inviting_user_email = false;
		$userHasAIFReferral = false;

		if (CUserReferralSource::is_invite2_active())
		{
			$tpl->assign('inviting_user_id', $_COOKIE['IAF2_inviting_user_id']);

			$userHasAIFReferral = true;

			$Referring_User = DAO_CFactory::create('user');
			$Referring_User->id = $_COOKIE['IAF2_inviting_user_id'];
			if ($Referring_User->find(true))
			{
				$inviting_user_email = $Referring_User->primary_email;
				$tpl->assign('inviters_email', $Referring_User->primary_email);
				$tpl->assign('inviters_name', $_COOKIE['IAF2_inviting_user']);
			}
		}
		else if (CUserReferralSource::is_invite_active())
		{
			$tpl->assign('inviting_user_id', $_COOKIE['IAF_inviting_user_id']);
		}

		$tpl->assign('userHasAIFReferral', $userHasAIFReferral);

		return $inviting_user_email;
	}

	/**
	 * @throws Exception
	 */
	static function process_account_creation($tpl, $bounceTo = false): CForm
	{
		$Form = new CForm;
		$Form->Repost = false;
		$Form->Bootstrap = true;

		$referring_user_email = self::setUpReferral($tpl);

		self::_buildForm($Form, true, false, $referring_user_email);

		$User = DAO_CFactory::create('user', true);

		//there may be 2 password fields on the page.. if so one is named new_password.
		// if it is set then convert to what the backend expects
		$newPassword = $Form->value('new_password');
		if (isset($newPassword))
		{
			$Form->DefaultValues['password'] = $Form->value('new_password');
		}

		$SFICurrentValues = CUserData::buildSFIFormElementsNew($Form, $User);

		//if($tpl->page == 'payment'){
		//$error = self::_saveForm($Form, $User, false, true, false, $SFICurrentValues);

		//}else{
		$error = self::_saveFormSimplified($Form, $User, false, true, false, $SFICurrentValues);

		//}

		//
		// Check for POST and login and possibly redirect
		//
		if ($_POST && isset($_POST['submit_account']) && !$error)
		{
			//authenticate user
			if ($Form->value('primary_email') && $Form->value('password'))
			{
				if ($User->Authenticate($Form->value('primary_email'), $Form->value('password')))
				{
					$User->Login();

					if ($bounceTo)
					{
						CApp::instance()->bounce($bounceTo, true);
					}
					else
					{
						CApp::instance()->bounce();
					}
				}
			}
		}

		return $Form;
	}

	static function _saveAddresses($Form, $User)
	{
		$bAddr = $User->getPrimaryAddress();

		$obAddr = clone($bAddr);
		$bAddr->setFrom($Form->values());

		if ($bAddr->id)
		{
			$rslt = $bAddr->update($obAddr);
		}
		else
		{
			$rslt = $bAddr->insert();
		}

		$sAddr = $User->getShippingAddress();
		$osAddr = clone($sAddr);

		$newShippingLine1 = $Form->value('shipping_address_line1');
		$newShippingLine2 = $Form->value('shipping_address_line2');
		$newShippingCity = $Form->value('shipping_city');
		$newShippingState = $Form->value('shipping_state_id');
		$newShippingZip = $Form->value('shipping_postal_code');
		$newShippingNote = $Form->value('shipping_address_note');

		$sAddr->address_line1 = (!empty($newShippingLine1) ? $Form->value('shipping_address_line1') : $Form->value('address_line1'));
		$sAddr->address_line2 = (!empty($newShippingLine2) ? $Form->value('shipping_address_line2') : $Form->value('address_line2'));
		$sAddr->city = (!empty($newShippingCity) ? $Form->value('shipping_city') : $Form->value('city'));
		$sAddr->state_id = (!empty($newShippingState) ? $Form->value('shipping_state_id') : $Form->value('state_id'));
		$sAddr->postal_code = (!empty($newShippingZip) ? $Form->value('shipping_postal_code') : $Form->value('postal_code'));
		$sAddr->address_note = (!empty($newShippingNote) ? $Form->value('shipping_address_note') : $Form->value('address_note'));

		$sAddr->country_id = 'US';
		$sAddr->location_type = CAddress::SHIPPING;
		$sAddr->is_primary = 1;

		if ($sAddr->id)
		{
			$rslt = $sAddr->update($osAddr);
		}
		else
		{
			$rslt = $sAddr->insert();
		}

		return $rslt;
	}
}