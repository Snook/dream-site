<?php // page_admin_create_store.php
/**
 * @author Carl Samuelson
 */

require_once("includes/CPageAdminOnly.inc");
require_once("includes/CCalendar.inc");

class page_admin_start_new_test_order extends CPageAdminOnly
{
	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}
	function runFranchiseStaff()
	{
		$this->runSiteAdmin();
	}
	function runFranchiseLead()
	{
		$this->runSiteAdmin();
	}
	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}
	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}
	function runFranchiseOwner()
	{
		$this->runSiteAdmin();
	}
	function runEventCoordinator()
	{
		$this->runSiteAdmin();
	}
	function runOpsLead()
	{
	    $this->runSiteAdmin();
	}
	function runOpsSupport()
	{
		$this->runSiteAdmin();
	}
	function runDishwasher()
	{
		$this->runSiteAdmin();
	}
	function runNewEmployee()
	{
		$this->runSiteAdmin();
	}
	
	function runSiteAdmin()
	{
		
		$tpl = CApp::instance()->template();
		
		$session_id = CGPC::do_clean($_REQUEST['session_id'],TYPE_INT);
		$user_id = CGPC::do_clean($_REQUEST['user_id'],TYPE_INT);
		
		$params = json_encode(array('session_id' => $session_id, 'user_id' => $user_id));
		
		$tpl->assign('params', $params);
	}
}
?>