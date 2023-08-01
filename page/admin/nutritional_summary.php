<?php // admin_resources.php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CBrowserSession.php");

class page_admin_nutritional_summary extends CPageAdminOnly {

	private $needsStoreSelector = null;

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
	function runFranchiseStaff()
	{
		$this->runSiteAdmin();
	}
	function runFranchiseLead()
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
		$this->needsStoreSelector = true;
		return $this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$user_type = CUser::getCurrentUser()->user_type;
		$tpl = CApp::instance()->template();
		$tpl->assign('user_type', $user_type);

		$Form = new CForm();
		$Form->Repost = true;

		// ------------------------------ figure out active store and create store widget if necessary
		$store_id = null;
		if ($this->needsStoreSelector)
		{

			$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();

			$Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
									CForm::name => 'store',
									CForm::onChangeSubmit => true,
									CForm::allowAllOption => false,
									CForm::showInactiveStores => true) );

			$store_id = $Form->value('store');

		}
		else
		{
			$store_id =  CBrowserSession::getCurrentFadminStore();
		}

		$tpl->assign('store_id', $store_id);

		// list of menus
		$daoMenu = DAO_CFactory::create('menu');
		$current_date_sql = date("Y-m-01", mktime(0, 0, 0, date("m"), "01",	date("Y")));
		$daoMenu->whereAdd( " menu_start >= DATE_SUB('$current_date_sql',INTERVAL 3 MONTH) and menu_start <= DATE_ADD('$current_date_sql',INTERVAL 3 MONTH)" );
		$daoMenu->OrderBy("menu_start");
		$daoMenu->find();
		$menuArray = array();

		while ($daoMenu->fetch())
		{
			$menuArray[$daoMenu->id] = $daoMenu->menu_name;
		}

		$Form->AddElement(array(CForm::type=> CForm::DropDown,
				CForm::default_value => CMenu::getCurrentMenuId(),
				CForm::name => "menus_dropdown",
				CForm::options => $menuArray));


		$formArray = $Form->render();
		$tpl->assign('form', $formArray);

	}
}

?>