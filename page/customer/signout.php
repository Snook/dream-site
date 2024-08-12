<?php

use JetBrains\PhpStorm\NoReturn;

include_once('includes/CForm.inc');

class page_signout extends CPage
{

	#[NoReturn] function runPublic(): void
	{
		$sessionKey = CBrowserSession::instance()->browser_session_key;
		$csrf_protection = new CSRF($sessionKey);
		$csrf_protection->logout();

		CBrowserSession::instance()->ExpireSession();

		CApp::bounce('/');
	}

}