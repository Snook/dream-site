<?php
require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/CMenuItem.php';
require_once 'includes/DAO/BusinessObject/CMenu.php';
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/Store_menu_item_exclusion.php';

class page_admin_finishing_touch_printable_form extends CPageAdminOnly
{

	function runFranchiseOwner()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseStaff()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseLead()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
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

	function runSiteAdmin()
	{

		$tpl = CApp::instance()->template();

		$store_id = CGPC::do_clean($_REQUEST['store_id'], TYPE_INT);
		$menu_id = CGPC::do_clean($_REQUEST['menu_id'], TYPE_INT);

		if (empty($store_id))
		{
			$tpl->setErrorMsg("The store id is invalid.");
			throw new Exception('store id invalid when constructing printable Sides and Sweets form.');
		}

		if (empty($menu_id) || $menu_id < 138)
		{
			$tpl->setErrorMsg("The menu id is invalid.");
			throw new Exception('menu id invalid when constructing printable Sides and Sweets form.');
		}

		CLog::RecordReport("Printable Sides and Sweets Order Form", "Menu:$menu_id~Store:$store_id");

		$daoStore = DAO_CFactory::create('store');
		$daoStore->id = $store_id;

		if (!$daoStore->find(true))
		{
			throw new Exception('store not found when constructing printable Sides and Sweets form.');
		}

		$ctsArray = CMenu::buildCTSArray($daoStore, $menu_id);

		$eflArray = CMenu::buildExtendedFastLaneArray($daoStore, $menu_id);

		$tpl->assign('FT_Items', array_merge($ctsArray, $eflArray));
	}

}

?>