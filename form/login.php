<?php
/*
 * Created on Jun 23, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once('includes/CForm.inc');
require_once('includes/ValidationRules.inc');
require_once('includes/CAppUtil.inc');
require_once('includes/DAO/BusinessObject/CStore.php');

class form_login
{

	/**
	 * Builds a login form
	 * Out: returns a CForm object
	 * @throws exception
	 */
	static function BuildAndProcessForm(): CForm
	{
		$Form = new CForm;
		$Form->Repost = false;
		$Form->Bootstrap = true;

		$suppressBounce = false;

		if (isset($_POST['nobo']))
		{
			$suppressBounce = true;
		}

		//handle forgotten password
		if ($_POST && isset($_POST['forgotPassword']) && is_string($_POST['forgot_primary_email']))
		{
			CUser::resetPwd($_POST['forgot_primary_email']);
		}

		//$Form->DefaultValues['primary_email_login'] = "Email";
		//$Form->DefaultValues['password_login'] = "Password";

		// turn off autocomplete for staff
		$autocomplete_username = true;
		$autocomplete_password = true;

		if (!empty(CUser::getUserByCookie()->user_type) && !in_array(CUser::getUserByCookie()->user_type, array(
				CUser::GUEST,
				CUser::CUSTOMER
			)))
		{
			$autocomplete_username = true;
			$autocomplete_password = false;

			if (CUser::getUserByCookie()->user_type == CUser::SITE_ADMIN && defined('ENABLE_SITE_ADMIN_TIMEOUT') && ENABLE_SITE_ADMIN_TIMEOUT != true)
			{
				$autocomplete_username = true;
				$autocomplete_password = true;
			}
		}

		// Create form elements
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "primary_email_login",
			CForm::required => true,
			CForm::placeholder => '*Email',
			CForm::autocomplete => $autocomplete_username,
			CForm::maxlength => 64,
			CForm::xss_filter => true,
			CForm::size => 25
		));

		$Form->AddElement(array(
			CForm::type => CForm::Password,
			CForm::name => "password_login",
			CForm::required => true,
			CForm::placeholder => '*Password',
			CForm::autocomplete => $autocomplete_password,
			CForm::maxlength => 41,
			CForm::xss_filter => true,
			CForm::size => 25
		));

		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::label => "Stay logged in",
			CForm::name => "remember_login"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Button,
			CForm::name => "submit_login",
			CForm::css_class => "btn btn-primary btn-block btn-spinner",
			CForm::value => 'Log in'
		));

		if (isset($_GET['back']) && $_GET['back'])
		{
			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => "back",
				CForm::value => $_GET['back']
			));
		}
		else
		{
			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => "back",
				CForm::value => $_SERVER['REQUEST_URI']
			));
		}

		$DAO_user = CUser::getCurrentUser();
		if (!$DAO_user)
		{
			throw new Exception('user is null');
		}

		//
		// Check for POST
		//
		if ($_POST && isset($_POST['submit_login']))
		{
			//authenticate user
			if ($Form->value('primary_email_login') && $Form->value('password_login'))
			{
				if ($DAO_user->Authenticate($Form->value('primary_email_login'), $Form->value('password_login')))
				{
					$remember_login = false;

					if (isset($_POST['remember_login']))
					{
						$remember_login = true;
					}

					$redirectCustomer = $DAO_user->Login($remember_login);

					// -------------------------------------------------------------------- handle login attempt at support portal
					$trigger = empty($_REQUEST["host_url"]) ? false : $_REQUEST["host_url"];

					if ($trigger && defined('REMAUTHSTRNEW') && ($_REQUEST["host_url"] == "support.dreamdinners.com" || $_REQUEST["host_url"] == "support.lovingwithfood.com"))
					{
						CApp::signupOrLoginToFreshDeskSupportPortalNew($_REQUEST, false, true, $_REQUEST["host_url"]);
					}
					//---------------------------------------------------------------------

					if (!$suppressBounce)
					{
						if (CBrowserSession::getSessionVariable(key: CBrowserSession::BOUNCE_REQUEST_URI))
						{
							CApp::bounce(CBrowserSession::getSessionVariableOnce(key: CBrowserSession::BOUNCE_REQUEST_URI));
						}
						else
						{
							CApp::bounce($redirectCustomer);
						}
					}
				}
				else
				{
					CBrowserSession::ClearCookie();
					if (!$suppressBounce)
					{
						CApp::instance()->template()->setErrorMsg('The e-mail address and password you entered do not match any accounts on record. Please make sure that you have correctly entered the e-mail address associated with your dreamdinners.com account.');
					}
				}
			}
		}
		else
		{
			// no login credentials submitted
			$browserSession = CBrowserSession::instance();
			$DAO_user = CUser::getCurrentUser();

			if (CBrowserSession::isPrevious())
			{
				$DAO_user->joinAdd($browserSession);
				$DAO_user->selectAdd();
				$DAO_user->selectAdd('user.*');

				if ($browserSession->isPrevious && $DAO_user->find(true) && $DAO_user->id)
				{
					// user is logged in

					// -----------------------------------------------------------------handle access from support portal
					$trigger = empty($_REQUEST["host_url"]) ? false : $_REQUEST["host_url"];

					if ($trigger && defined('REMAUTHSTRNEW') && $_REQUEST["host_url"] == "support.dreamdinners.com")
					{
						if (isset($_GET['page']) && $_GET['page'] != "signout")
						{
							CApp::signupOrLoginToFreshDeskSupportPortalNew($_REQUEST, false, true);
						}
					}
					// ------------------------------------------------------------------
					$DAO_user->getMembershipStatus(); // Note: without params this method looks for memberships current for today's menu/month.
					$DAO_user->getPlatePointsSummary();
					$DAO_user->getUserPreferences();
					$DAO_user->setMealAndFamilySize();

					$DAO_user->setLogin();

					// CES 09/18/07 also load the browser instance fron the Database as we are now using a value stored with the session.
					$browserSession->find(true);

					if ($DAO_user->isFranchiseAccess())
					{
						$initialStore = !empty($browserSession->current_store_id) ? $browserSession->current_store_id : $DAO_user->getInitialFranchiseStore();
						CStore::setUpFranchiseStore($initialStore);
					}

					// site admin has 10 minute time out reset on every access
					if ($DAO_user->user_type != CUser::CUSTOMER)
					{
						$browserSession->prolongSession($DAO_user);
					}

					//remove the where clause from the previous join
					$DAO_user->whereAdd(); /// CES ??? Why? No more finds performed on this object - or are there?
				}
				else
				{
					// user is not logged in
					$DAO_user->firstname = "Guest";
					$DAO_user->id = 0;
				}
			}
		}

		//if we are not in store view, and the user does not have a
		//primary email address, then keep them on the account page
		//until they enter one
		if (!$suppressBounce)
		{
			if ($DAO_user->isLoggedIn() && (!$DAO_user->primary_email) && (@$_GET['page'] != 'account'))
			{
				CApp::bounce('/account');
			}
		}

		//if already signed in, get name
		$Form->DefaultValues['is_logged_in'] = $DAO_user->isLoggedIn();
		$Form->DefaultValues['user_type'] = $DAO_user->user_type;

		if (!empty($DAO_user->id))
		{
			$Form->DefaultValues['id'] = $DAO_user->id;
		}

		$Form->DefaultValues['home_store_id'] = CBrowserSession::getCurrentStore();

		return $Form;
	}
}