<?php
/*
 * Created on Sep 1, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("includes/CPageAdminOnly.inc");
 require_once 'includes/CCalendar.inc';
 require_once 'includes/DAO/BusinessObject/CSession.php';



 class page_admin_reschedule extends CPageAdminOnly {


	function runSiteAdmin(){
		$this->runFranchiseOwner();
	}

 	function runFranchiseStaff() {
		$this->runFranchiseOwner();
	}

 	function runFranchiseLead() {
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}
	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
		$this->runFranchiseOwner();
	}

    function runHomeOfficeManager()
    {
		$this->runFranchiseOwner();
	}

	 function runFranchiseOwner()
	 {


		if (isset($_POST['order_id']) && is_numeric($_POST['order_id']))
		{
			CApp::bounce('/backoffice/order-mgr?order=' . $_POST['order_id'] . '&tabs=mgr.sessionsTab');
		}

		CApp::bounce('/backoffice/main');

	 }

}

?>