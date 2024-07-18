<?php
class page_login extends CPage {

	function runPublic()
	{
		if (CUser::isLoggedIn())
		{
			// They don't need to be here if logged in except for on mobile
			if (!empty($_GET['back']))
			{
				CApp::bounce($_GET['back']);
			}
			else
			{
				CApp::bounce();
			}
		}

		CApp::forceSecureConnection();
	}
}