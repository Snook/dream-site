<?php
require_once('includes/DAO/BusinessObject/CMenu.php');

class page_mailhide extends CPage
{

	function runPublic()
	{
		CBrowserSession::nofollow();

		$tpl = CApp::instance()->template();

		$tpl->assign('show_string', false);

		if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response']))
		{
			$obj = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LeIGFUUAAAAAGsB3RDMFRmQa4wTIwaQIoblq8EN&response=" . $_POST['g-recaptcha-response'] . "&remoteip=" . $_SERVER['REMOTE_ADDR']));

			// If the Google Recaptcha check was successful
			if ($obj->success == true)
			{
				$tpl->assign('show_string', CTemplate::encrypt_decrypt('decrypt', $_GET['c']));
			}
		}
	}
}

?>