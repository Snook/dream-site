<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once("includes/CUserRetention.inc");

 class page_admin_reports_user_retention extends CPageAdminOnly {
 	private $currentStore = null;
    private $isStoreOwner = false;

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }

	function runPublic()
	{
		$tpl = CApp::instance()->template();
		$tpl->assign('isPublic', 1);
	}

	function runFranchiseLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runFranchiseOwner();
	}
	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runFranchiseOwner();
	}
	
	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runFranchiseOwner();
	}
	
	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

 	function runFranchiseOwner()
 	{
	 	$this->currentStore = CApp::forceLocationChoice();
	 	$this->runSiteAdmin();
	 }

 	function runSiteAdmin() {
		$store = NULL;
		$this->isStoreOwner=true;

		set_time_limit(1200);

		$tpl = CApp::instance()->template();

		$tpl->assign('isPublic', 0);

		$Form = new CForm();
		$Form->Repost = FALSE;

		$re_sort_string="session.session_start, user_retention_data.booking_count, user.lastname" ;
		$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 0;
		$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 1;
		$rowCount = 10;

		$lowbandwidthversion = isset($_REQUEST['bandwidth']) ? 1 : 0;

		$pagenum = isset($_REQUEST['pagenum']) ? $_REQUEST['pagenum'] : 0;

		if ($lowbandwidthversion == 1 && $pagenum == 0) $pagenum = 1;
		$export = isset($_REQUEST['csv']) ? true : false;

		if ($sort > 1) {
			if ($sort == 2) $re_sort_string="user.lastname, session.session_start, user_retention_data.booking_count";
		    else if ($sort == 3) $re_sort_string="days_inactive, session.session_start, user_retention_data.booking_count, user.lastname";
		}

		if ( $this->currentStore ) { //fadmins
				$store = $this->currentStore;
		} else { //site admin

			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;
			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
								CForm::onChangeSubmit => true,
								CForm::allowAllOption => false,
								CForm::showInactiveStores => true,
								CForm::name => 'store'));

			$store = $Form->value('store');
		}


		$Store = DAO_CFactory::create('store');
		$Store->id = $store;
		$Store->find(true);
		$tpl->assign('storeName', $Store->store_name);
		$show_detailed_report = isset($_REQUEST['report_type']) ? true : false  ;

		$data=array();
		$follow_up_array=array();
		$label=null;
		$templateLabel=array();
		$follow_up_choices=null;

		switch($step){
			case 0:
				$dataArr = array();

				$data1 = CUserRetention::LocateInactivityRecordsSummation (1, 60, 89, $Store->id);
				$dataArr['InactiveReport6080'] = $data1;


				$data2 = CUserRetention::LocateInactivityRecordsSummation (2, 90, 119, $Store->id);
				$dataArr['InactiveReport90119'] = $data2;

				$data3 = CUserRetention::LocateInactivityToActivityRecordsSummation (1, 60, 89, $Store->id);
				$dataArr['ActiveReport6080'] = $data3;

				$data4 = CUserRetention::LocateInactivityToActivityRecordsSummation (2, 90, 119, $Store->id);
				$dataArr['ActiveReport90119'] = $data4;

				$tpl->assign('rows', $dataArr);

				break;
			case 1:  // 60-89 days Inactive report
				$follow_up_choices=CUserRetention::getFollowUpTypes($step);
				$data = CUserRetention::LocateInactivityRecords(1, 60, 89, $Store->id, $follow_up_array, $re_sort_string);
				$label = "Guest ID,Last Name,First Name,Street Name,City,State,Postal Code,email,Day Phone," .
					"Evening Phone,Call Time,Last Session,Last Session Type,Sessions Attended,Total Days Inactive, Dream Rewards Status, Dream Rewards Level," .
					"Referral Credit Available,Direct Credit Available,Gift Card Used, Retention ID,".
					"60-89 Day Follow-up Date,60-89 Day Follow-up Action,60-89 Day Results Date,60-89 Day Results Comment";
				$templateLabel = explode(",",$label);

				break;
			case 2:   // 90 to 119 Inactive Report
				$follow_up_choices=CUserRetention::getFollowUpTypes($step);
				$data = CUserRetention::LocateInactivityRecords(2, 90, 119, $Store->id, $follow_up_array, $re_sort_string);
				$label = "Guest ID,Last Name,First Name,Street Name,City,State,Postal Code,email,Day Phone,Evening Phone," .
				"Call Time,Last Session,Last Session Type,Sessions Attended,Total Days Inactive, Dream Rewards Status, Dream Rewards Level,Referral Credit Expired, Direct Credit Expired, Gift Card Used,Retention ID," .
				"60-89 Day Follow-up Date,60-89 Day Follow-up Action,60-89 Day Results Date,60-89 Day Results Comment," .
				"90-119 Day Follow-up Date,90-119 Day Follow-up Action,90-119 Day Results Date,90-119 Day Results Comment";
				$templateLabel = explode(",",$label);

				break;
			case 3:  //  60-89 days Inactive to Active Report

					$data = CUserRetention::LocateInactivityToActivityRecords(1, 60, 89, $Store->id,  $follow_up_array,$re_sort_string);
					$label = "Guest ID,Last Name,First Name,Street Name,City,State,Postal Code,email,Day Phone,Evening Phone," .
					"Call Time,Last Session,Last Session Type,Sessions Attended,Order Amount,Total Days Inactive, Dream Rewards Status, Dream Rewards Level,  Gift Card Used,Retention ID," .
						"60-89 Day Follow-up Date,60-89 Day Follow-up Action,60-89 Day Results Date,60-89 Day Results Comment," .
					"90-119 Day Follow-up Date,90-119 Day Follow-up Action,90-119 Day Results Date,90-119 Day Results Comment";
					$templateLabel = explode(",",$label);
				break;
			case 4:   // 90-119 days Inactive to Active Report
					$data = CUserRetention::LocateInactivityToActivityRecords(2, 90, 119, $Store->id,  $follow_up_array,$re_sort_string);
					$label = "Guest ID,Last Name,First Name,Street Name,City,State,Postal Code,email,Day Phone,Evening Phone," .
					"Call Time,Last Session,Last Session Type,Sessions Attended,Order Amount,Total Days Inactive, Dream Rewards Status, Dream Rewards Level,  Gift Card Used,Retention ID," .
					"60-89 Day Follow-up Date,60-89 Day Follow-up Action,60-89 Day Results Date,60-89 Day Results Comment," .
					"90-119 Day Follow-up Date,90-119 Day Follow-up Action,90-119 Day Results Date,90-119 Day Results Comment";
					$templateLabel = explode(",",$label);
				break;
		} // switch



		if ($step > 0 && !empty($data)) {

			if (!empty($follow_up_array)) {
				foreach($follow_up_array as $key => $element ){
					foreach($element as $subkey => $subelement ){
						$userid = $data[$subkey]['user_id'];

						if ( $userid != null) {

							if (!empty($subelement[1][0]['follow_dates'])) $data[$subkey][]  = $subelement[1][0]['follow_dates'];
							if (!empty($subelement[1][0]['follow_types'])) $data[$subkey][]  = $subelement[1][0]['follow_types'];
							if (!empty($subelement[1][0]['result_dates'])) $data[$subkey][]  = $subelement[1][0]['result_dates'];
							if (!empty($subelement[1][0]['comment'])) $data[$subkey][]  = $subelement[1][0]['comment'];

							if (!empty($subelement[2][0]['follow_dates'])) $data[$subkey][]  = $subelement[2][0]['follow_dates'];
							if (!empty($subelement[2][0]['follow_types'])) $data[$subkey][]  = $subelement[2][0]['follow_types'];
							if (!empty($subelement[2][0]['result_dates'])) $data[$subkey][]  = $subelement[2][0]['result_dates'];
							if (!empty($subelement[2][0]['comment'])) $data[$subkey][]  = $subelement[2][0]['comment'];
						}
				    }

			    }
			}

			$numRows = count($data);
			CLog::RecordReport("Inactive Guests", "Rows:$numRows ~ Store: $store ~ Step: $step ~ isLowBandwidth: $lowbandwidthversion" );

		    $tpl->assign('rows', $data);
		    $tpl->assign('report_count', count($data));
			$tpl->assign('export', $export);
			$label = explode(',', $label);
			$tpl->assign('labels', $label);
			$tpl->assign('field_labels', $templateLabel);
			if ($lowbandwidthversion == 1) {
				$tpl->assign('pagenum', $pagenum);
				$rowNum = ($pagenum-1)*$rowCount;
				$tpl->assign('rowcount', $rowCount);
				$tpl->assign('rownum', $rowNum);

			}
		}
		else
		{
			CLog::RecordReport("Inactive Guests (summary)", "Rows:NA ~ Store: $store ~ isLowBandwidth: $lowbandwidthversion");
		}

		if ($lowbandwidthversion == 1) $tpl->assign('bandwidth', $lowbandwidthversion);  // low bandwidth choice

		if ($follow_up_choices != null) $tpl->assign('follow_up_choices', $follow_up_choices);
		$formArray = $Form->render();
		$tpl->assign('form_session_list', $formArray);
		if (!empty($follow_up_array) && !empty($data))  $tpl->assign('follow_up_array', $follow_up_array);
		$tpl->assign('show_detailed_report', $show_detailed_report);
		$tpl->assign('isStoreOwner', $this->isStoreOwner);
		$tpl->assign('sort', $sort);
		$tpl->assign('step', $step);
		$tpl->assign('store',$store);

	}


}

?>