<?php // page_user_details.php

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CUserHistory.php');

class page_admin_user_history extends CPageAdminOnly {


	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$this->userHistory();
	}

	function userHistory()
	{
		$tpl = CApp::instance()->template();

		if ( !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $_REQUEST['id'];
			$User->find();
			$User->fetch();

			$tpl->assign('user_info', $User);

			$UserHistory = DAO_CFactory::create('user_history');
			$UserHistory->query("SELECT
				uh.*
				FROM user_history AS uh
				WHERE uh.user_id = '" . $_REQUEST['id'] . "'
				ORDER BY uh.id DESC LIMIT 200");

			$tpl->assign('user_history', $UserHistory);
		}
	}
}
?>