<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_location_switch extends CPageAdminOnly
{
    function runEventCoordinator()
    {
		$this->locationSwitch();
    }
    function runOpsLead()
    {
		$this->locationSwitch();
    }
    function runOpsSupport()
    {
		$this->locationSwitch();
    }
    function runDishwasher()
    {
    	$this->locationSwitch();
    }
    function runNewEmployee()
    {
    	$this->locationSwitch();
    }
    function runManufacturerStaff()
	{
		$this->locationSwitch();
	}

	function runFranchiseStaff()
	{
		$this->locationSwitch();
	}

	function runFranchiseLead()
	{
		$this->locationSwitch();
	}

	function runFranchiseManager()
	{
		$this->locationSwitch();
	}

	function runFranchiseOwner()
	{
		$this->locationSwitch();
	}

	function runHomeOfficeStaff()
	{
		$this->locationSwitch();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		return $this->locationSwitch();
	}

	function locationSwitch()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = false;

		if (!empty($_POST['store']) && is_numeric($_POST['store']))
		{
			CBrowserSession::setCurrentFadminStore($_POST['store']);
			CApp::bounce($_REQUEST['back']);
		}

		$StoreObj = DAO_CFactory::create('store');
		$StoreObj->query("SELECT
			store.id
			FROM store, user_to_store
			WHERE user_to_store.store_id = store.id
			AND user_to_store.user_id = '" . CUser::getCurrentUser()->id . "'
			AND user_to_store.is_deleted = '0'");

		//if there's just one store, then continue
		if ($StoreObj->N == 1)
		{
			$StoreObj->fetch();
			CBrowserSession::setCurrentFadminStore($StoreObj->id);
			CApp::bounce($_REQUEST['back']);
		}

		$Form->DefaultValues['store'] = CBrowserSession::getCurrentFadminStore();

		if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_STAFF)
		{
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));
		}
		else if (!empty($StoreObj->N))
		{
			$Form->addElement(array(
				CForm::type => CForm::StoreDropDown,
				CForm::userAccessFilter => CUser::getCurrentUser()->id,
				CForm::name => 'store',
				CForm::showInactiveStores => true
			));
		}

		$formArray = $Form->render();
		$tpl->assign('form_location_switch', $formArray);
	}
}

?>