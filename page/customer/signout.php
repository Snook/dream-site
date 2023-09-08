<?php
include_once('includes/CForm.inc');

/*
 * Created on Jun 7, 2005
 *
 */

class page_signout extends CPage
{

	function runPublic()
	{

		$sessionKey = CBrowserSession::instance()->browser_session_key;
		$csrf_protection = new CSRF($sessionKey);
		$csrf_protection->logout();

		$session = CBrowserSession::instance()->ExpireSession();

		if (isset($_REQUEST['back']) && $_REQUEST['back'])
		{
			CApp::bounce($_REQUEST['back'], true);
		}
		else if (isset($_REQUEST['remo']) && $_REQUEST['remo'] = "true")
		{
			CApp::bounce('?page=admin_login', true);
		}
		else
		{
			CApp::bounce('/', true);
		}
	}

}
?>