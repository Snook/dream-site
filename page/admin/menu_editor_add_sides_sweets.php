<?php
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/CMenuItem.php';
require_once 'includes/DAO/BusinessObject/CMenu.php';
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Store_menu_item_exclusion.php';
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');

class page_admin_menu_editor_add_sides_sweets extends CPageAdminOnly
{
	function runSiteAdmin()
	{
		$this->menuEditorAddItem();
	}

	function runHomeOfficeManager()
	{
		$this->menuEditorAddItem();
	}

	function runFranchiseLead()
	{
		$this->menuEditorAddItem();
	}

	function runOpsLead()
	{
		$this->menuEditorAddItem();
	}

	function runFranchiseManager()
	{
		$this->menuEditorAddItem();
	}

	function runFranchiseOwner()
	{
		$this->menuEditorAddItem();
	}

	function menuEditorAddItem()
	{
		$tpl = CApp::instance()->template();

		$menu_id = false;

		if (!empty($_REQUEST['menu_id']) && is_numeric($_REQUEST['menu_id']))
		{
			$menu_id = $_REQUEST['menu_id'];
		}

		if ($menu_id)
		{
			$menuItems = CMenuItem::getEFLEligibleSidesSweets($menu_id, CBrowserSession::getCurrentFadminStoreID());
		}

		$tpl->assign('menuItems', $menuItems);
	}
}

?>