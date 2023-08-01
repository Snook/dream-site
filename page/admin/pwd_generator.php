<?php // admin_pwd_generator.php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CBrowserSession.php");

class page_admin_pwd_generator extends CPageAdminOnly {



	function runHomeOfficeStaff() {
		$this->runSiteAdmin();
	}
	
	function runHomeOfficeManager() {
		$this->runSiteAdmin();
	}

	function runSiteAdmin() {
		$this->needsStoreSelector = true;
		return $this->runFranchiseOwner();
	}

	function runFranchiseOwner() {
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


		if (empty($store_id))
			$store_id = 244;

		$tpl->assign('store_id', $store_id);

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $store_id;
		$storeObj->find(true);

		$tpl->assign('storeSupportsDFL', $storeObj->allow_dfl_tool_access);


		$menus = CMenu::getActiveMenuArray();

		$firstMenu = array_shift($menus);

		if (isset($firstMenu)) {
			$tpl->assign('current_menu_id', $firstMenu['id']);
			$tpl->assign('current_menu_name', $firstMenu['name']);
		}

		$nextMenu = array_shift($menus);

		if (isset($nextMenu)) {
			$tpl->assign('next_menu_id', $nextMenu['id']);
			$tpl->assign('next_menu_name', $nextMenu['name']);
		}

		$formArray = $Form->render();
		$tpl->assign('form', $formArray);

	}
}

?>
