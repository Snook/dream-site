<?php // page_admin_create_store.php

/**
 * @author LHook
 */

 require_once("includes/CPageAdminOnly.inc");

 class page_admin_access_error extends CPageAdminOnly {

	function runSiteAdmin()
	{
		$tpl = self::processPageDetails();
	}
	function runFranchiseOwner()
	{
		$tpl = self::processPageDetails();
	}
	function runFranchiseManager()
	{
		$tpl = self::processPageDetails();
	}
	function runFranchiseStaff()
	{
		$tpl = self::processPageDetails();
	}
 	function runFranchiseLead()
 	{
		$tpl = self::processPageDetails();
	}
	function runManufacturerStaff()
	{
		$tpl = self::processPageDetails();
	}
	function runHomeOfficeStaff()
	{
		$tpl = self::processPageDetails();
	}
	function runHomeOfficeManager()
	{
		$tpl = self::processPageDetails();
	}
	function runEventCoordinator()
	{
		$tpl = self::processPageDetails();
	}
	function runOpsLead()
	{
		$tpl = self::processPageDetails();
	}
	function runOpsSupport()
	{
		$tpl = self::processPageDetails();
	}
	function runDishwasher()
	{
		$tpl = self::processPageDetails();
	}
	function runNewEmployee()
	{
	    $tpl = self::processPageDetails();
	}

	function processPageDetails ()
	{
		$tpl = CApp::instance()->template();
		$tpl->assign('page_title','Access Denied');
		
		$topNavName = "";
		if (!empty($_REQUEST['topnavname']))
		{
		    $topNavName = CGPC::do_clean($_REQUEST['topnavname'],TYPE_STR);
		}
		
		$tpl->assign('topNavName',$topNavName);

		$pageName = "";
		if (!empty($_REQUEST['pagename']))
		{
		    $pageName = CGPC::do_clean($_REQUEST['pagename'],TYPE_STR);
		}
		
		$tpl->assign('pagename',$pageName);

		return $tpl;
	}




}

?>