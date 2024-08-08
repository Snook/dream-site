<?php

use JetBrains\PhpStorm\NoReturn;

class page_admin_login extends CPage
{
	#[NoReturn] function runPublic(): void
	{
		CApp::bounce(page: '/login', BOUNCE_REQUEST_URI: '/backoffice');
	}

	#[NoReturn] function runCustomer(): void
	{
		CApp::bounce(page: '/backoffice');
	}
}