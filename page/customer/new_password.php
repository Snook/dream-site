<?php

class page_new_password extends CPage
{
	/**
	 * @throws exception
	 */
	function runPublic(): void
	{
		$cid = $_GET['cid'] ?? null;

		if (empty($cid))
		{
			$this->Template->assign('errorMessage', "The confirmation link is invalid. Check your email and be sure to copy the entire link and try again.");

			return;
		}

		// make sure the cid is valid on every load
		$cidObj = DAO_CFactory::create('single_use_tokens');
		$cidObj->token = $cid;
		$cidObjValid = $cidObj->find(true);

		$Form = new CForm;
		$Form->Repost = false;

		$Form->AddElement(array(
			CForm::type => CForm::Password,
			CForm::name => "password",
			CForm::required => true,
			CForm::placeholder => '*Password',
			CForm::autocomplete => 'new-password',
			CForm::maxlength => 41,
			CForm::css_class => "form-control",
			CForm::size => 25
		));

		$Form->AddElement(array(
			CForm::type => CForm::Password,
			CForm::name => "password_confirm",
			CForm::required => true,
			CForm::placeholder => 'Confirm Password',
			CForm::autocomplete => 'new-password',
			CForm::maxlength => 41,
			CForm::css_class => "form-control",
			CForm::size => 25
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => "update_password",
			CForm::css_class => "btn btn-primary",
			CForm::value => 'Update Password'
		));

		$this->Template->assign('form', $Form->Render());
		$this->Template->assign('errorMessage', false);

		// make sure the cid is valid on every load
		if (!$cidObjValid)
		{
			$this->Template->assign('errorMessage', "This confirmation link is invalid or expired. You can generate a new email with a valid link by clicking <i>Forgot your Password?</i> on the login form.");

			return;
		}

		if (strtotime($cidObj->datetime_created) < time() - 3600) // 1 hour
		{
			$cidObj->delete();

			$this->Template->assign('errorMessage', "This confirmation link is expired. You can generate a new email with a valid link by clicking <i>Forgot your Password?</i> on the login form.");

			return;
		}

		if (isset($_POST['update_password']))
		{
			$email = trim($cidObj->email);
			$password = trim($Form->value('password'));
			$password_confirm = trim($Form->value('password_confirm'));

			if (empty($password))
			{
				$this->Template->setErrorMsg("The password is missing.");

				return;
			}

			if (empty($password_confirm))
			{
				$this->Template->setErrorMsg("The password confirmation is missing.");

				return;
			}

			if ($password_confirm != $password)
			{
				$this->Template->setErrorMsg("The passwords do not match.");

				return;
			}

			$userObj = DAO_CFactory::create('user');
			$userObj->primary_email = $email;
			$userObj->selectAdd();
			$userObj->selectAdd('id, primary_email, user_type');
			if (!$userObj->find(true))
			{
				//$this->Template->setErrorMsg("The account could not be found.");
				$toPage = $userObj->Login();
				CApp::bounce($toPage);
			}

			// More demanding restrictions on non-customers
			if ($userObj->user_type != CUser::GUEST && $userObj->user_type != CUser::CUSTOMER)
			{
				$error = false;
				$hasNumber = false;
				$hasLetter = false;

				if (preg_match('/[1-9]/', $password))
				{
					$hasNumber = true;
				}
				if (preg_match('/[a-z]/', $password) || preg_match('/[A-Z]/', $password))
				{
					$hasLetter = true;
				}

				if (!$hasNumber)
				{
					$error = 'Passwords for Store and Home Office personnel must contain at least 1 number.<br />';
				}

				if (!$hasLetter)
				{
					$error .= 'Passwords for Store and Home Office personnel must contain at least 1 letter.<br />';
				}

				if (strlen($password) < 10)
				{
					$error .= 'Passwords for Store and Home Office personnel must be at least 10 characters long.<br />';
				}

				if (!CPasswordPolicy::passwordPassesUniquenessRules($userObj->id, $password))
				{
					$error .= 'The password must be different than the last 4 passwords used.<br />';
				}

				if ($error)
				{
					$this->Template->setErrorMsg($error);

					return;
				}
			}

			$cidObj->delete();

			$userObj->updatePassword($password);
			if ($userObj->Authenticate($email, $password, false, false, true))
			{
				$toPage = $userObj->Login();
				CApp::bounce($toPage);
			}
		}
	}
}