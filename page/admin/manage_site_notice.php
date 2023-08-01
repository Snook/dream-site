<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_manage_site_notice extends CPageAdminOnly {

	private $currentStore = null;
	private $singleStore = false;

	function runHomeOfficeManager()
	{
		$this->runSiteNotice();
	}

	function runSiteAdmin()
	{
		$this->runSiteNotice();
	}

	function runFranchiseOwner() {
		$this->currentStore = CApp::forceLocationChoice();
		$this->singleStore = CBrowserSession::getCurrentFadminStore();
		$this->runSiteNotice();
	}

	function runFranchiseManager() {
		$this->currentStore = CApp::forceLocationChoice();
		$this->singleStore = CBrowserSession::getCurrentFadminStore();
		$this->runSiteNotice();
	}

	function runSiteNotice()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		$time_now = CTemplate::unix_to_mysql_timestamp(time());

		$maintenance_array = CStore::getSiteNotices(false, $this->singleStore);

		$storearray = CStore::getListOfStores(true, true, "s.id, s.store_name, s.franchise_id");

		$noticeJS = (!empty($maintenance_array) ? json_encode(array_values($maintenance_array)) : "{}");
		$storesJS = json_encode($storearray);

		$tpl->assign('manageSingleStore', $this->singleStore);
		$tpl->assign('time_now', $time_now);
		$tpl->assign('maintenance_array', $maintenance_array);
		$tpl->assign('maintenance_js', $noticeJS);
		$tpl->assign('stores_js', $storesJS);
	}
}
?>