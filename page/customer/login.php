<?php

class page_login extends CPage
{
	function runPublic(): void
	{
		if (CUser::isLoggedIn())
		{
			CApp::bounce(page: '/my-account');
		}
	}
}