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

class page_account_signup extends CPage
{

	private $userHasAIFReferral = false;

	/**
	 * @throws Exception
	 */
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

		$User = new CUser();

		$SFICurrentValues = CUserData::buildSFIFormElementsNew($Form, $User);

		form_account::_saveForm($Form, $User, false, true, false, $SFICurrentValues,false, false,false,true);

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
		$tpl->assign('form_account', $Form->Render());
		$tpl->assign('isCreate', true);
	}

	function runCustomer()
	{
		CApp::forceSecureConnection();
		CApp::instance()->bounce('/account', true);
	}
}