<?php // page_admin_access_levels.php

require_once('includes/ValidationRules.inc');
require_once('includes/DAO/Address.php');
require_once("includes/CForm.inc");
require_once("includes/CPageAdminOnly.inc");
require_once('page/customer/account.php');
require_once('CMail.inc');
require_once('includes/class.inputfilter_clean.php');


class page_admin_portal extends CPageAdminOnly {

	private $isAdmin = true;

	function runFranchiseOwner()
	{
		$this->isAdmin = false;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runPortal();
	}

	function runFranchiseManager()
	{
		$this->isAdmin = false;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runPortal();
	}

	function runOpsLead()
	{
		$this->isAdmin = false;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runPortal();
	}



	function runHomeOfficeManager()
	{
		$this->isAdmin = false;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runPortal();
	}

	function runSiteAdmin()
	{
		$this->isAdmin = true;
		$this->runPortal();
	}

	function runPortal()
	{
	}
}
?>