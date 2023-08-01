<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CSRF.inc");

class processor_login extends CPageProcessor
{
	function runPublic()
	{

		$session_cookie_name = CBrowserSession::getSessionCookieName();

		if (CUser::isLoggedIn())
		{
			echo json_encode(array(
				'processor_success' => true,
				'cookie_value' => CBrowserSession::getValue($session_cookie_name),
				'processor_message' => 'Logged in.',
				'firstname' => CUser::getCurrentUser()->firstname,
				'lastname' => CUser::getCurrentUser()->lastname,
				'user_type' => CUser::getCurrentUser()->user_type,
				'user_type_text' => CUser::userTypeText(CUser::getCurrentUser()->user_type),
				'dd_toasts' => array(
					array('message' => 'Logged in.')
				)
			));
		}
		else if (!empty($_POST['op']) && $_POST['op'] == 'get_login_form')
		{
			$tpl = new CTemplate();

			$tpl->assign('primary_email', ((CUser::getUserByCookie() && !empty(CUser::getUserByCookie()->primary_email)) ? CUser::getUserByCookie()->primary_email : ''));

			$login_form = $tpl->fetch('admin/subtemplate/admin_login.tpl.php');

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Retrieved login form.',
				'login_form' => $login_form
			));
		}
		else if (!empty($_POST['submit_login']))
		{
			$user = CUser::getCurrentUser();

			$remember_login = false;

			if (!empty($_POST['remember_login']))
			{
				$remember_login = true;
			}

			$authenticateResult = $user->Authenticate($_POST['primary_email_login'], $_POST['password_login'], false, false, false, true);

			if ($authenticateResult === true)
			{
				$user->Login($remember_login);

				if (CBrowserSession::getValue($session_cookie_name))
				{
					if (!empty($_POST['last_session_id']))
					{
						CSRF::updateSessionTokens(CBrowserSession::getValue($session_cookie_name), $_POST['last_session_id']);
					}

					echo json_encode(array(
						'processor_success' => true,
						'cookie_value' => CBrowserSession::getValue($session_cookie_name),
						'processor_message' => 'Logged in.',
						'firstname' => CUser::getCurrentUser()->firstname,
						'lastname' => CUser::getCurrentUser()->lastname,
						'user_type' => CUser::getCurrentUser()->user_type,
						'user_type_text' => CUser::userTypeText(CUser::getCurrentUser()->user_type),
						'dd_toasts' => array(
							array('message' => 'Logged in.')
						)
					));
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'cookie_value' => CBrowserSession::getValue($session_cookie_name),
						'processor_message' => 'Cookie did not set, not logged in. Try again.',
						'dd_toasts' => array(
							array('message' => 'Cookie did not set, not logged in. Try again.')
						)
					));
				}
			}
			else if ($authenticateResult === false)
			{
				echo json_encode(array(
					'processor_success' => false,
					'cookie_value' => CBrowserSession::getValue($session_cookie_name),
					'processor_message' => 'Invalid username and password combination.',
					'dd_toasts' => array(
						array('message' => 'Invalid username and password combination.')
					)
				));
			}
			else if ($authenticateResult === 'password_reset_mail_sent')
			{
				echo json_encode(array(
					'processor_success' => false,
					'cookie_value' => CBrowserSession::getValue($session_cookie_name),
					'processor_message' => 'Your password has expired. An email has been sent to you with instructions to reset it.',
					'dd_toasts' => array(
						array('message' => 'Your password has expired. An email has been sent to you with instructions to reset it.')
					)
				));
			}
			else if ($authenticateResult === 'password_reset_email_not_found') // should never occur but ...
			{
				echo json_encode(array(
					'processor_success' => false,
					'cookie_value' => CBrowserSession::getValue($session_cookie_name),
					'processor_message' => 'An internal error occurred when attempting to reset your expired password.',
					'dd_toasts' => array(
						array('message' => 'An internal error occurred when attempting to reset your expired password.')
					)
				));
			}
			else if ($authenticateResult === 'password_reset_bad_email_format') // should never occur but ...
			{
				echo json_encode(array(
					'processor_success' => false,
					'cookie_value' => CBrowserSession::getValue($session_cookie_name),
					'processor_message' => 'An internal error occurred when attempting to reset your expired password.',
					'dd_toasts' => array(
						array('message' => 'An internal error occurred when attempting to reset your expired password.')
					)
				));
			}
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'cookie_value' => CBrowserSession::getValue($session_cookie_name),
				'processor_message' => 'Not Logged in.',
				'dd_toasts' => array(
					array('message' => 'Not Logged in.')
				)
			));
		}
	}
}

?>