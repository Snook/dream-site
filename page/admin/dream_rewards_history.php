<?php // page_admin_create_store.php

/**
 * @author Carl Sam
 */

 require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');

 class page_admin_dream_rewards_history extends CPageAdminOnly {


 	var $showAllOverride = false;

	function runFranchiseManager() {
		$this->showAllOverride = false;
		$this->runHistoryReport();
	}

	function runOpsLead() {
		$this->showAllOverride = false;
		$this->runHistoryReport();
	}
	
	function runFranchiseLead() {
		$this->showAllOverride = false;
		$this->runHistoryReport();
	}

	function runHomeOfficeStaff() {
		$this->showAllOverride = false;
		$this->runHistoryReport();
	}
	function runHomeOfficeManager() {
		$this->showAllOverride = true;
		$this->runHistoryReport();
	}

 	function runFranchiseOwner() {
 		$this->showAllOverride = false;
		$this->runHistoryReport();
	 }
	 function runSiteAdmin() {
	 	$this->showAllOverride = true;
	 	$this->runHistoryReport();
	 }



 	function runHistoryReport() {
		 $tpl = CApp::instance()->template();

		$user_id = $_REQUEST['user'];
		$store_id = $_REQUEST['store'];

		$error = 'success';
		if (empty ($user_id)) {
			$error = 'invalid_user';
		}

		if (empty ($store_id)) {
			$error = 'invalid_store';
		}



		$UserDAO = DAO_CFactory::create('user');
		$UserDAO->id = $user_id;
		if (!$UserDAO->find(true))
			$error = 'invalid_user';

		$StoreDAO = DAO_CFactory::create('store');
		$StoreDAO->id = $store_id;
		if (!$StoreDAO->find(true))
			$error = 'invalid_store';

		if ($error == 'success' && $StoreDAO->supports_dream_rewards)
		{

			$historyArray = CDreamRewardsHistory::getHistoryArray($user_id, $store_id, $this->showAllOverride);

			$tpl->assign('history', $historyArray	);

			$tpl->assign('user_name', $UserDAO->firstname . " " . $UserDAO->lastname);
		}




	}






}

?>