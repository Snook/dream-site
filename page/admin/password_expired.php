<?php
/*
 * Created on Jun 7, 2005
 *
 */
class page_admin_password_expired extends CPage {

	function runPublic()
	{


		$tpl = CApp::instance()->template();

		if (CUser::isLoggedIn() && CUser::getCurrentUser()->user_type != CUser::CUSTOMER)
		{
			CApp::bounce('/backoffice');
		}
	}
}