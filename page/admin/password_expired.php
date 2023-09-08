<?php
/*
 * Created on Jun 7, 2005
 *
 */
class page_admin_password_expired extends CPage {

	function runPublic()
	{
		CApp::forceSecureConnection();

		$tpl = CApp::instance()->template();

		if (CUser::isLoggedIn() && CUser::getCurrentUser()->user_type != CUser::CUSTOMER)
		{
			CApp::bounce('?page=admin_main');
		}
	}
}
?>