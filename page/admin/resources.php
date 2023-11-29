<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CBrowserSession.php");

class page_admin_resources extends CPageAdminOnly
{
	function runFranchiseManager()
	{
		return $this->runFranchiseOwner();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseLead()
	{
		return $this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		return $this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		return $this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		return $this->runFranchiseOwner();
	}

	function runSiteAdmin()
	{
		return $this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$menus = CMenu::getActiveMenuArray();

		$firstMenu = array_shift($menus);

		if (isset($firstMenu))
		{
			$this->Template->assign('current_menu_id', $firstMenu['id']);
			$this->Template->assign('current_menu_name', $firstMenu['name']);
		}

		$nextMenu = array_shift($menus);

		if (isset($nextMenu))
		{
			$this->Template->assign('next_menu_id', $nextMenu['id']);
			$this->Template->assign('next_menu_name', $nextMenu['name']);
		}
	}
}
?>