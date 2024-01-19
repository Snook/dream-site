<?php // page_account.php

require_once('includes/ValidationRules.inc');
require_once('includes/DAO/Address.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CCustomerReferral.php');
require_once('includes/DAO/BusinessObject/CPayment.php');
require_once("includes/CForm.inc");
require_once('includes/DAO/User_referral_source.php');  // not sure if this is needed here
require_once('includes/DAO/BusinessObject/CUserReferralSource.php');
require_once('includes/class.inputfilter_clean.php');
require_once('form/account.php');

class page_account extends CPage
{

	private $userHasAIFReferral = false;

	function runPublic()
	{
		CApp::forceSecureConnection();

		parent::runPublic();

		$tpl = CApp::instance()->template();

		$inviting_user_email = false;

		if (CUserReferralSource::is_invite2_active())
		{
			$tpl->assign('inviting_user_id', $_COOKIE['IAF2_inviting_user_id']);

			$this->userHasAIFReferral = true;

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

		$tpl->assign('userHasAIFReferral', $this->userHasAIFReferral);

		// filter for xss attacks
		if ($_POST && isset($_POST['submit_account']))
		{
			$xssFilter = new InputFilter();
			$_POST = $xssFilter->process($_POST);
		}

		form_account::sanitizeAccountFields();

		$Form = new CForm;
		$Form->Repost = true;
		$Form->Bootstrap = true;
		$Form = form_account::_buildForm($Form, true, true, $inviting_user_email);

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 5,
			CForm::size => 5,
			CForm::number => true,
			CForm::required => true,
			CForm::name => 'zip_for_search',
			CForm::css_class => "small"
		));

		$Form->addElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 40,
			CForm::size => 40,
			CForm::required => true,
			CForm::placeholder => "Enter your zip code below",
			CForm::readonly => true,
			CForm::name => 'home_store'
		));

		$User = new CUser();

		if (!empty($_POST['store_id']))
		{
			if ($Form->value('home_store') !== "None Selected" && $_POST['store_id'] != "no_store" && isset($_POST['store_id']) && is_numeric($_POST['store_id']))
			{
				$Form->DefaultValues['home_store_id'] = $_POST['store_id'];
			}
		}

		$SFICurrentValues = CUserData::buildSFIFormElementsNew($Form, $User);

		form_account::_saveForm($Form, $User, false, true, false, $SFICurrentValues);

		//
		// Check for POST
		//
		if ($_POST && isset($_POST['submit_account']))
		{
			//authenticate user
			if ($Form->value('primary_email') && $Form->value('password'))
			{
				if ($User->Authenticate($Form->value('primary_email'), $Form->value('password')))
				{
					$User->Login();

					if (isset($_REQUEST['back']) && $_REQUEST['back'])
					{
						$url = $_REQUEST['back'];
					}
					else
					{
						$url = "/";
					}

					CApp::instance()->bounce($url, true);
				}
			}
		}

		//set template vars
		$tpl->assign('isPreferred', false);
		$tpl->assign('hide_second_number', false);
		$tpl->assign('sms_special_case', 'none');
		$tpl->assign('form_account', $Form->Render());
		$tpl->assign('isCreate', true);
		$tpl->assign('isCreate', true);
		$tpl->assign('platePointsEnroll', ((!empty($_GET['pp_enroll']) && CUser::getCurrentUser()->platePointsData['status'] != 'active') ? true : false));
		$tpl->assign('isAdmin', false);
		$tpl->assign('hasReferralSource', false);
	}

	function runCustomer()
	{
		CApp::forceSecureConnection();

		$tpl = CApp::instance()->template();

		$User = CUser::getCurrentUser();

		$isPreferred = $User->isUserPreferred() ;
		$tpl->assign('isPreferred', $isPreferred);

		if ($User->platePointsData['user_is_preferred'] && !$User->platePointsData['transition_has_expired'])
		{
			$User->platePointsData['conversion_data'] = CPointsUserHistory::getPreferredUserConversionData($User);
		}
		else if (($User->platePointsData['status'] == 'in_DR2' || $User->platePointsData['isDeactivatedDRUser']) && !$User->platePointsData['transition_has_expired'])
		{
			$User->platePointsData['conversion_data'] = CPointsUserHistory::getDR2ConversionData($User);
		}

		$Addr = $User->getPrimaryAddress();
		$aProps = $User->toArray();
		if ($Addr)
		{
			$aProps = array_merge($Addr->toArray(), $aProps);
		}

		$Form = new CForm('customer_account_submit');
		$Form->Repost = true;
		$Form->Bootstrap = true;
		$Form->DefaultValues = $aProps;

		$sAddr = $User->getShippingAddress();

		if (!empty($sAddr->id))
		{
			$Form->DefaultValues['shipping_address_line1'] = $sAddr->address_line1;
			$Form->DefaultValues['shipping_address_line2'] = $sAddr->address_line2;
			$Form->DefaultValues['shipping_city'] = $sAddr->city;
			$Form->DefaultValues['shipping_state_id'] = $sAddr->state_id;
			$Form->DefaultValues['shipping_postal_code'] = $sAddr->postal_code;
			$Form->DefaultValues['shipping_address_note'] = $sAddr->address_note;
		}

		$Form->DefaultValues['confirm_email_address'] = $aProps['primary_email'];

		$is_CSRF_valid = true;

		// filter for xss attacks
		if ($_POST && isset($_POST['submit_account']))
		{
			$xssFilter = new InputFilter();
			$_POST = $xssFilter->process($_POST);
			if (!$Form->validate_CSRF_token())
			{
				$is_CSRF_valid = false;
			}
		}

		// Note: Since we no longer bounce when updating we must always update the CSRF token to be prepared for the next submission
		$Form->setCSRFToken();

		form_account::sanitizeAccountFields();

		$tpl->assign('hide_second_number', false);

		$hasReferraSource = false;
		$ReferralSource = false;
		$ReferralSourceMetaData = false;
		$UserRefSource = DAO_CFactory::create('user_referral_source');
		$UserRefSource->user_id = $User->id;
		$UserRefSource->find();

		while ($UserRefSource->fetch())
		{
			$ReferralSource = $UserRefSource->source;
			$ReferralSourceMetaData = $UserRefSource->meta;
			$hasReferraSource = true;
			break;
			// Historically guests have been able to add multiple sources but we now only support one
		}

		$Form->DefaultValues['referral_source'] = $ReferralSource;
		if ($ReferralSource == CUserReferralSource::OTHER)
		{
			$Form->DefaultValues['referral_source_details'] = $ReferralSourceMetaData;
		}
		if ($ReferralSource == CUserReferralSource::VIRTUAL_PARTY)
		{
			$Form->DefaultValues['virtual_party_source_details'] = $ReferralSourceMetaData;
		}
		else if ($ReferralSource== CUserReferralSource::CUSTOMER_REFERRAL)
		{
			$Form->DefaultValues['customer_referral_email'] = $ReferralSourceMetaData;
		}

		$Form = form_account::_buildForm($Form, false);
		$SFICurrentValues = CUserData::buildSFIFormElementsNew($Form, $User);


		$erroroccurred = false;
		if ($is_CSRF_valid)
		{
			$erroroccurred = form_account::_saveForm($Form, $User, false, true, false, $SFICurrentValues);
		}
		else
		{
			$tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support. This message can also be caused by a double submission of the same page.");
		}

		$tpl->assign('birthdayComplete', false);
		if (!empty($Form->DefaultValues['birthday_month']) && !empty($Form->DefaultValues['birthday_year']))
		{
			$tpl->assign('birthdayComplete', true);
		}

		$tpl->assign('sms_special_case', 'none');

		$past_future_threshold = date("Y-m-d H:i:s", strtotime(date("Y-m-d")));
		$upcomingOrdersArray = COrders::getUsersOrders($User, false, $past_future_threshold, false, false, false, 'asc', false, true);

		$refPaymentTypeDataArray = CPayment::getPaymentsStoredForReference(CUser::getCurrentUser()->id, 'all');
		$tpl->assign('card_references', $refPaymentTypeDataArray);

		//set template vars
		$tpl->assign('form_account', $Form->Render());
		$tpl->assign('isCreate', false);
		$tpl->assign('hasUpComingOrders', !empty($upcomingOrdersArray));
		$tpl->assign('platePointsEnroll', ((!empty($_GET['pp_enroll']) && CUser::getCurrentUser()->platePointsData['status'] != 'active') ? true : false));
		$tpl->assign('isAdmin', false);
		$tpl->assign('hasReferralSource', $hasReferraSource);
		$tpl->assign('user', $User);
	}
}

?>