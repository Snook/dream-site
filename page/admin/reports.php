<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('DAO/BusinessObject/CUser.php');
 require_once ('DAO/BusinessObject/CFile.php');

 class page_admin_reports extends CPageAdminOnly {
 	private $folder_path = "admin/reports_sub_templates/";
	private $file_prefix = "reports_select_";

	private $storeObj = null;

	const LIMITED_P_AND_L_ACCESS_SECTION_ID = 8;
	const LIMITED_WEEKLY_REPORT_ACCESS = 9;
	
	function handleFileRetrieval($tpl)
	{
		if (isset($_REQUEST['file_id']) && is_numeric($_REQUEST['file_id']))
		{
			$File = CFile::downloadFile($_REQUEST['file_id']);

			if (!$File)
			{
				$tpl->setErrorMessage("An error occurred when retrieving the file.");
				CLog::RecordIntense('Missing File or shenanigans', 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
			}
		}
	}

	function runFranchiseStaff() {

		$currentStore =  CApp::forceLocationChoice();
		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select is_corporate_owned, supports_plate_points, supports_membership, store_type from store where id = $currentStore");
		$storeObj->fetch();

		$this->storeObj = $storeObj;

	 	$this->runFranchiseOwner();
	 }

	 function runFranchiseLead() {

	 	$currentStore =  CApp::forceLocationChoice();
	 	$storeObj = DAO_CFactory::create('store');
	 	$storeObj->query("select is_corporate_owned, supports_plate_points, supports_membership, store_type from store where id = $currentStore");
	 	$storeObj->fetch();

	 	$this->storeObj = $storeObj;


	 	$this->runFranchiseOwner();
	 }

	 function runEventCoordinator()
	 {
	 	$currentStore =  CApp::forceLocationChoice();
	 	$storeObj = DAO_CFactory::create('store');
	 	$storeObj->query("select is_corporate_owned, supports_plate_points, supports_delivery, supports_membership, store_type from store where id = $currentStore");
	 	$storeObj->fetch();

	 	$this->storeObj = $storeObj;
	 	
	 	$tpl = CApp::instance()->template();
	 	 
	 	$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);
	 	
	 	$tpl->assign('hasPandLAccess', $hasPandLAccess);
		 $tpl->assign('storeSupportsDelivery', $this->storeObj->supports_delivery);
		 $tpl->assign('storeSupportsMembership', $this->storeObj->supports_membership);

		 $this->runFranchiseOwner();
	 	 


	 	$this->runFranchiseOwner();
	 }

	 function runOpsLead()
	 {
	 	$currentStore =  CApp::forceLocationChoice();
	 	$storeObj = DAO_CFactory::create('store');
	 	$storeObj->query("select is_corporate_owned, supports_plate_points, supports_delivery, supports_membership, store_type from store where id = $currentStore");
	 	$storeObj->fetch();

	 	$this->storeObj = $storeObj;

	 	$tpl = CApp::instance()->template();
	 	
	 	$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);
	 	 
	 	$tpl->assign('hasPandLAccess', $hasPandLAccess);
		 $tpl->assign('storeSupportsDelivery', $this->storeObj->supports_delivery);
		 $tpl->assign('storeSupportsMembership', $this->storeObj->supports_membership);

		 $this->runFranchiseOwner();
	 }

	 function runOpsSupport()
	 {
	 	$currentStore =  CApp::forceLocationChoice();
	 	$storeObj = DAO_CFactory::create('store');
	 	$storeObj->query("select is_corporate_owned, supports_plate_points, supports_membership, store_type from store where id = $currentStore");
	 	$storeObj->fetch();

	 	$this->storeObj = $storeObj;


	 	$this->runFranchiseOwner();
	 }


	 function runFranchiseManager() {
	 	$currentStore =  CApp::forceLocationChoice();

	 	$hasCorporateOverride = false;

	 	$storeObj = DAO_CFactory::create('store');
	 	$storeObj->query("select is_corporate_owned, supports_plate_points, supports_delivery, supports_membership, store_type from store where id = $currentStore");
	 	$storeObj->fetch();

	 	$this->storeObj = $storeObj;

	 	if ($storeObj->is_corporate_owned)
	 		$hasCorporateOverride = true;

	 	$tpl = CApp::instance()->template();
	 	$tpl->assign('hasCorporateOverride', $hasCorporateOverride);
	 	$tpl->assign('storeSupportsDelivery', $this->storeObj->supports_delivery);
		 $tpl->assign('storeSupportsMembership', $this->storeObj->supports_membership);
		 $tpl->assign('storeIsDistributionCenter', $this->storeObj->store_type == 'DISTRIBUTION_CENTER');

		 $hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);

		 $hasWeeklyReportAccess = CApp::directAccessControlTest(self::LIMITED_WEEKLY_REPORT_ACCESS, CUser::getCurrentUser()->id);
	 	 
	 	$tpl->assign('hasPandLAccess', $hasPandLAccess);
	 	$tpl->assign('hasWeeklyReportAccess', $hasWeeklyReportAccess);

	  	$this->runFranchiseOwner();
	 }

	 function runFranchiseOwner() {

	 	$tpl = CApp::instance()->template();
	 	$this->handleFileRetrieval($tpl);

	 	$currentStore =  CApp::forceLocationChoice();

	 	if (!$this->storeObj)
	 	{
	 		$storeObj = DAO_CFactory::create('store');
	 		$storeObj->query("select is_corporate_owned, supports_plate_points, supports_delivery, supports_membership, store_type from store where id = $currentStore");
	 		$storeObj->fetch();

	 		$this->storeObj = $storeObj;
	 	}

		 $tpl->assign('storeSupportsPlatePoints', $this->storeObj->supports_plate_points);
	 	$tpl->assign('storeSupportsDelivery', $this->storeObj->supports_delivery);
		 $tpl->assign('storeSupportsMembership', $this->storeObj->supports_membership);
		 $tpl->assign('storeIsDistributionCenter', $this->storeObj->store_type == 'DISTRIBUTION_CENTER');
		 // Note: code is duplicated in run handlers that call this handler - please eliminate duplication

	 	$User = CUser::getCurrentUser();
		$template_name = "";
		if ($User != null)
		   $template_name = $this->folder_path . $this->file_prefix . strtolower($User->user_type) . ".tpl.php";
		$tpl->assign('template_name', $template_name);
		$tpl->assign('home_office_user', FALSE);

	 }

	 // ******************************************

	 function runHomeOfficeStaff() {
		$this->runSiteAdmin();
	 }

	 function runHomeOfficeManager() {
	  	$this->runSiteAdmin();
	 }

	 function runManufacturerStaff() {
	 	$this->runFranchiseOwner();
	 }

	 function runSiteAdmin() {
		$tpl = CApp::instance()->template();
		 $tpl->assign('storeIsDistributionCenter', false);

		$this->handleFileRetrieval($tpl);
		$User = CUser::getCurrentUser();
		$template_name = "";
		if ($User != null)
		   $template_name = $this->folder_path . $this->file_prefix . strtolower($User->user_type) . ".tpl.php";

		$tpl->assign('template_name', $template_name);
		$tpl->assign('home_office_user', TRUE);
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);

		//if (!defined('HOME_SITE_SERVER') && ($User->id == '323288' || $User->id == '149902')) {
		//	$tpl->assign('HOME_SITE_SERVER', TRUE);
		//}
		// DB1 specific override to let select employees run DB1 reports from live server
		if (!defined('HOME_SITE_SERVER')) {
			$canoverride = CApp::overrideAdminPage();
			if ($canoverride == true) {
				$tpl->assign('HOME_SITE_SERVER', TRUE);
			}

		}

	}

}

?>