<?php // admin_webinar_video_home.php
require_once("includes/CPageAdminOnly.inc");


class page_admin_webinar_video_home extends CPageAdminOnly
{
	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		return $this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$user_type = CUser::getCurrentUser()->user_type;
		$tpl = CApp::instance()->template();
		$tpl->assign('user_type', $user_type);
	}
	
	function runFranchiseManager()
	{
		$user_type = CUser::getCurrentUser()->user_type;
		$tpl = CApp::instance()->template();
		$tpl->assign('user_type', $user_type);
	}
	
	function runOpsLead()
	{
		$user_type = CUser::getCurrentUser()->user_type;
		$tpl = CApp::instance()->template();
		$tpl->assign('user_type', $user_type);
	}
	
}

?>
