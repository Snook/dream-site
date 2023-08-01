<?php 
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CBrowserSession.php");

class page_admin_safe_landing extends CPageAdminOnly {



	function runFranchiseStaff()
	{
        $this->runPage();
	}

	function runFranchiseOwner()
	{
        $this->runPage();
	}

	function runHomeOfficeManager()
	{
        $this->runPage();
	}

	function runHomeOfficeStaff()
	{
        $this->runPage();
	}

	function runFranchiseManager()
	{
        $this->runPage();
	}

	function runFranchiseLead()
	{
        $this->runPage();
	}

	function runManufacturerStaff()
	{
        $this->runPage();
	}

	function runEventCoordinator()
	{
        $this->runPage();
	}

	function runOpsLead()
	{
        $this->runPage();
	}

	function runOpsSupport()
	{
        $this->runPage();
	}

	function runDishwasher()
	{
        $this->runPage();
	}

	function runNewEmployee()
	{
	    $this->runPage();
	}


	function runSiteAdmin()
	{
	    $this->runPage();
	}

	function runPage()
	{

	}
}

?>
