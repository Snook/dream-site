<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CStoreCredit.php');
 require_once ('includes/CSessionReports.inc');

 class page_admin_home_office_reports_dashboard_aggregate extends CPageAdminOnly {

	function getStateInformation ($manager_id =null)
	{
		$sql = "SELECT `store`.`state_id`,`state_province`.`state_name` FROM " .
		" `store_coach` Inner Join `store` ON `store_coach`.`store_id` = `store`.`id` Inner Join `state_province` ON `store`.`state_id` = `state_province`.`id` where ";
		if ($manager_id != null)
			$sql .= " `coach_id` = $manager_id and ";
		$sql .= "  `store_coach`.`is_deleted` = 0 and `store_coach`.`is_active` = 1 " .
		" group by `store`.`state_id`";

		$obj = DAO_CFactory::create("store_coach");

		$obj->query($sql);

		$arr = array();

		while($obj->fetch()){
			 $arr[$obj->state_id] = $obj->state_name;

		}

		return $arr;
	}

	function getmanager(){

			$coacharray = array();
			$obj = DAO_CFactory::create("coach");

			$sql = "SELECT user.firstname, user.lastname, `coach`.`id`,`coach`.`administrator` FROM `coach` Inner Join user ON coach.user_id = user.id where administrator = 0 and `coach`.`active` = 1";

			$obj->query($sql);

			while($obj->fetch()){
				$coacharray[$obj->id] = $obj->lastname . ", " . $obj->firstname;


			}

			$coacharray[-1] = "Un-assigned Stores";
			return $coacharray;


	}
	function getStoreInformation ($manager_id =null)
	{
		$sql = "SELECT store.id, store.store_name, store.state_id, store.city, store.home_office_id FROM " .
		" `store_coach` Inner Join `store` ON `store_coach`.`store_id` = `store`.`id` where ";
		if ($manager_id != null)
			$sql .= " `coach_id` = $manager_id and ";
		$sql .= "  `store_coach`.`is_deleted` = 0 and `store_coach`.`is_active` = 1 " .
		"  order by store.state_id, store.city, store.store_name";

		$obj = DAO_CFactory::create("store_coach");

		$obj->query($sql);

		$arr = array();

		while($obj->fetch()){
			 $arr[$obj->id] = $obj->state_id . ' --- ' . $obj->city . ' --- ' . $obj->store_name . ', ' . $obj->home_office_id;

		}

		return $arr;
	}



	function getDistrictInformation ($manager_id =null)
	{
		$sql = "SELECT `trade_area`.`region`,`trade_area`.`id`,`store_coach`.`coach_id` FROM store_trade_area " .
		" Inner Join `trade_area` ON `store_trade_area`.`trade_area_id` = `trade_area`.`id` Inner Join `store_coach` ON `store_trade_area`.`store_id` = `store_coach`.`store_id` where " .
		" `store_trade_area`.`is_active` = 1 and `store_trade_area`.`is_deleted`= 0 and `trade_area`.`is_active` = 1 and `store_coach`.`is_active` = 1 " ;
		if ($manager_id != null)
			$sql .= " and store_coach.`coach_id` = $manager_id  ";
		$sql .= " group by trade_area.region order by `trade_area`.`region`";

		$obj = DAO_CFactory::create("store_trade_area");

		$obj->query($sql);

		$arr = array();


		while($obj->fetch()){
			 $arr[$obj->id] = $obj->region;

		}

		return $arr;
	}



	function runHomeOfficeManager(){
		 $this->runSiteAdmin();
	}


 	function runSiteAdmin() {
		$store = NULL;

		set_time_limit(100000);
		ini_set('memory_limit','-1');


		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$total_count = 0;
		$report_submitted = FALSE;
		$AdminUser = CUser::getCurrentUser();
	    $userType = $AdminUser->user_type;
		$is_franchise_coach = false;
		$manager_id = null;
		$is_franchise_lead = false;

		$currentdatevalue = null;
		$dashboardlogid = array();

		//if ($userType == CUser::HOME_OFFICE_MANAGER) {
		if (false) {

			$store_array = array();
			$obj = DAO_CFactory::create("coach");

			$sql = "SELECT `coach`.`id`,`coach`.`administrator` FROM `coach` where `coach`.`active` = 1 and coach.user_id = $AdminUser->id";



			$obj->query($sql);

			$rslt = $obj->fetch();

			if ($rslt == TRUE) {
				$is_franchise_coach = true;
				if ($obj->administrator == 1) $is_franchise_lead = true;
				$manager_id = $obj->id;
			}


			if (!empty($manager_id)) {


				$tpl->assign('page_title','Dashboard Aggregate (Manager View)');

				// build state array dropdown

				$enable_store = isset($_POST["store_popup"]) ? false : true;

				//$storearray = $this->getStoreInformation ($manager_id);
				$storearray = $this->getStoreInformation (null);

				if (!empty($storearray)) {
					$Form->AddElement(array(CForm::type=> CForm::DropDown,
							CForm::onChangeSubmit => false,
							CForm::allowAllOption => false,
							CForm::disabled => $enable_store,
							CForm::options => $storearray,
							CForm::name => 'store_popup'));

					$Form->AddElement(array (CForm::type => CForm::CheckBox,
					CForm::onChangeShow => 'store',
					CForm::name => 'select_store_chkbox'));


					$enable_state = isset($_POST["state_popup"]) ? false : true;
				}


				$statearray = $this->getStateInformation ($manager_id);

				if (!empty($statearray)) {

					$Form->AddElement(array(CForm::type=> CForm::DropDown,
							CForm::onChangeSubmit => false,
							CForm::allowAllOption => false,
							CForm::disabled => $enable_state,
							CForm::options => $statearray,
							CForm::name => 'state_popup'));


					$Form->AddElement(array (CForm::type => CForm::CheckBox,
					CForm::onChangeShow => 'state',
					CForm::name => 'select_state_chkbox'));

				}

				$districtArray = $this->getDistrictInformation ($manager_id);
				// build district array drop drop down

				if (!empty($districtArray)) {
					$enable_district = isset($_POST["district_popup"]) ? false : true;

					$Form->AddElement(array(CForm::type=> CForm::DropDown,
							CForm::onChangeSubmit => false,
							CForm::allowAllOption => false,
							CForm::disabled => $enable_district,
							CForm::options => $districtArray,
							CForm::name => 'district_popup'));


					$Form->AddElement(array (CForm::type => CForm::CheckBox,
					CForm::onChangeShow => 'district',
					CForm::name => 'select_district_chkbox'));

					// build regional checkbox

				}

				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'regional',
				CForm::name => 'select_regional_chkbox'));

				//no national report selection needed


			}
			else {


				$tpl->assign('page_title','Dashboard Aggregate (Admin View)');

				$enable_store = isset($_POST["store_popup"]) ? false : true;

				$storearray = $this->getStoreInformation ($manager_id);
				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_store,
						CForm::options => $storearray,
						CForm::name => 'store_popup'));

				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'store',
				CForm::name => 'select_store_chkbox'));

				$enable_state = isset($_POST["state_popup"]) ? false : true;

				$statearray = $this->getStateInformation ();

				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_state,
						CForm::options => $statearray,
						CForm::name => 'state_popup'));


				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'state',
				CForm::name => 'select_state_chkbox'));

				$districtArray = $this->getDistrictInformation ();
				// build district array drop drop down

				$enable_district = isset($_POST["district_popup"]) ? false : true;

				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_district,
						CForm::options => $districtArray,
						CForm::name => 'district_popup'));


				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'district',
				CForm::name => 'select_district_chkbox'));

				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'regional',
				CForm::name => 'select_regional_chkbox'));

				$enable_region = isset($_POST["region_popup"]) ? false : true;

				$array = $this->getmanager();

					$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_region,
						CForm::options => $array,
						CForm::name => 'regional_popup'));


				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'national',
				CForm::name => 'select_national_chkbox'));



			}

		}
		//else if ($userType == CUser::SITE_ADMIN) {
		else if (true) {


				$tpl->assign('page_title','Dashboard Aggregate (Admin View)');

				$enable_store = isset($_POST["store_popup"]) ? false : true;

				$storearray = $this->getStoreInformation ($manager_id);
				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_store,
						CForm::options => $storearray,
						CForm::name => 'store_popup'));

				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'store',
				CForm::name => 'select_store_chkbox'));



				$enable_state = isset($_POST["state_popup"]) ? false : true;



				$statearray = $this->getStateInformation ();

				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_state,
						CForm::options => $statearray,
						CForm::name => 'state_popup'));


				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'state',
				CForm::name => 'select_state_chkbox'));

				$districtArray = $this->getDistrictInformation ();
				// build district array drop drop down

				$enable_district = isset($_POST["district_popup"]) ? false : true;

				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_district,
						CForm::options => $districtArray,
						CForm::name => 'district_popup'));


				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'district',
				CForm::name => 'select_district_chkbox'));

				// build regional checkbox



				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'regional',
				CForm::name => 'select_regional_chkbox'));


				$enable_region = isset($_POST["region_popup"]) ? false : true;

				$array = $this->getmanager();

					$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $enable_region,
						CForm::options => $array,
						CForm::name => 'regional_popup'));



				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'national',
				CForm::name => 'select_national_chkbox'));


		}



		$Form->AddElement(array (CForm::type => CForm::Submit,
			 CForm::name => 'report',
			 CForm::value => 'Export Data'));


		$sql = "SELECT `dashboard_report_cache_log`.`id`, `dashboard_report_cache_log`.`cached_date`, DAY(`dashboard_report_cache_log`.`cached_date`) as dayvar, MONTH(`dashboard_report_cache_log`.`cached_date`) as monthvar, YEAR(`dashboard_report_cache_log`.`cached_date`) as yearvar, `dashboard_report_cache_log`.`active` FROM `dashboard_report_cache_log` where cached_date >= '2007-10-01 00:00:00' order by `dashboard_report_cache_log`.`cached_date`";
		$obj = DAO_CFactory::create("dashboard_report_cache_log");
		$obj->query($sql);
		$currentarrlog = array();
		$archivedValues = array();

		$curYear = date("Y");
		$curMonth = date("m");
		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		while($obj->fetch()){



			if ($obj->monthvar == $curMonth && $obj->yearvar == $curYear ) {
				$currentarrlog[$obj->yearvar][$obj->monthvar][$obj->dayvar] = $obj->id;
			}
			else {
				$archivedValues[$obj->yearvar][$obj->monthvar] = array($month_array[$obj->monthvar-1], $obj->id);
			}


		}

		foreach($archivedValues as $daYear => $daMonths)
		{
			foreach($daMonths as $daMonthKey => $daMonthData)
			{

				$daDate = $daMonthData[0] . " 1, " . $daYear;
				$daMonthTS = strtotime($daDate);
				$daTimeStr = date("Y-m-d", $daMonthTS);

				$objTest = DAO_CFactory::create("dashboard_report_cache_log");
				$objTest->query("select id from dashboard_report_cache_log where date_best_for = '$daTimeStr'");
				if ($objTest->N > 0)
				{
					$objTest->fetch();
					$archivedValues[$daYear][$daMonthKey][1] = $objTest->id;
				}
			}
		}



		$inactive_date_cur = true;
		$inactive_date_arc = true;
		if (isset($_POST["current_date_popup"])) {
			$dashboardlogid[] = $_POST["current_date_popup"];
			$inactive_date_cur = false;
		}

		else if (empty($dashboardlogid) && isset($_POST["archive_date_popup"]))  {


			$dashboardlogid[] = $_POST["archive_date_popup"];
			$inactive_date_arc = false;
		}


		if (!empty($currentarrlog)) {

				$temparr = array();

				foreach($currentarrlog as $year => $value ){
					foreach($value as $month => $svalue ){
							foreach($svalue as $day => $ssvalue ){
								$temparr[$ssvalue] = $month . "/" . $day . "/" . $year;
							}
					}
				}



				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $inactive_date_cur,
						CForm::options => $temparr,
						CForm::name => 'current_date_popup'));


				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'current',
				CForm::name => 'select_current_chkbox'));


		}


		$temparr = array();
		if (!empty($archivedValues)) {



				foreach($archivedValues as $year => $value ){
					foreach($value as $month => $svalue ){
							$temparr[$svalue[1]] = $svalue[0] . " " . $year;
					}
				}

				$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => false,
						CForm::disabled => $inactive_date_arc,
						CForm::options => $temparr,
						CForm::name => 'archive_date_popup'));


				$Form->AddElement(array (CForm::type => CForm::CheckBox,
				CForm::onChangeShow => 'archive',
				CForm::name => 'select_archive_chkbox'));



				$checkboxarray = array();
				$tpl->assign('archive_array',$temparr);
				foreach($temparr as $key => $value ){
					$Form->AddElement(array (CForm::type => CForm::CheckBox,
					CForm::name => 'select_archive_chkbox_multi_' . $key));

				}


		}

		if (isset($_POST["report"]) && $_POST["report"])
		{


			if (!empty($temparr)) {
					foreach($temparr as $key => $value){
						if (isset($_POST["select_archive_chkbox_multi_" . $key]) && $_POST["select_archive_chkbox_multi_" . $key] == "on")
							$dashboardlogid[] = $key;
				}
			}


			// what type of report to run
			//$dashboardlogid
			// getAggregateSummary($store_id, $state_id, $district_id, $cachelogid)
			if (isset($_POST["select_state_chkbox"])) {
				$state = $_POST["state_popup"];
				$masterarra = $this->getAggregateSummary(null, $state, null, $dashboardlogid, null, $manager_id);

			}
			else if (isset($_POST["select_store_chkbox"])) {
				$storeid = $_POST["store_popup"];

				$masterarra = $this->getAggregateSummary($storeid, null, null, $dashboardlogid, null, $manager_id);

			}
			else if (isset($_POST["select_district_chkbox"])) {
				$district = $_POST["district_popup"];

				$masterarra = $this->getAggregateSummary(null, null, $district, $dashboardlogid, null, $manager_id);

			}
			else if (isset($_POST["select_regional_chkbox"])) {
				$region_man_id = isset($_POST["regional_popup"]) ? $_POST["regional_popup"]  : null;

				$masterarra = $this->getAggregateSummary(null, null, null, $dashboardlogid, $region_man_id, $manager_id);
			}
			else if (isset($_POST["select_national_chkbox"])) {

				$masterarra = $this->getAggregateSummary(null, null, null, $dashboardlogid, null, $manager_id);

			}


			$numRows = count($masterarra);
			CLog::RecordReport("Dashboard Aggregate", "Rows:$numRows" );

			if (!empty($masterarra) && count($masterarra) > 0) {

				$tpl->assign('headersAreEmbedded', true);
				$tpl->assign('rows', $masterarra);
				$tpl->assign('labels', null);
				$tpl->assign('rowcount', count($masterarra));
				$_GET['export'] = 'xlsx';

			}
			else
			{
				$masterarra[0] = "Sorry, an error occurred in processing your request.  If this continues, please contact the Dream Dinners Help Desk.";
				$tpl->assign('labels', null);
				$tpl->assign('rows', $masterarra);
				$tpl->assign('rowcount', count($masterarra));
				$_GET['export'] = 'xlsx';

			}


			// which dashboard id to use





		}
//$currentdatevalue


// site admin, home office managers can see aggregates based on: state, district, region and national
// regional managers can see: state (within their district) and their own district.



		$tpl->assign('is_manager', $is_franchise_coach);


		$formArray = $Form->render();
		$tpl->assign('store', $store);
		$tpl->assign('form_array', $formArray);

		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);
	}

function getAggregateSummary($store_id, $state_id, $district_id, $cachelogidarra, $region_id, $coachid=null){
	$masterarray = array();
	$counter = 0;
		foreach($cachelogidarra as $cachelogid) {




		$sql = "SELECT  DAY(`dashboard_report_cache_log`.`cached_date`) as dayvar, MONTH(`dashboard_report_cache_log`.`cached_date`) as monthvar,
		 YEAR(`dashboard_report_cache_log`.`cached_date`) as yearvar, `dashboard_report_cache_log`.`active`,  `dashboard_report_cache_log`.`date_best_for` FROM `dashboard_report_cache_log`
		  where dashboard_report_cache_log.id = $cachelogid";

		$obj = DAO_CFactory::create("dashboard_report_cache_log");
		$obj->query($sql);
		$rslt =$obj->fetch();

		if ($rslt <= 0) {
			$masterarray[] = "Sorry, please check a timespan and enter a date";
		}
		else if ($rslt > 0) {


			if (!empty($obj->date_best_for))
			{
				$dateArr = explode("-", $obj->date_best_for);

				$monthvar = $dateArr[1];
				$yearvar = $dateArr[0];
				$dayvar = "1";
			}
			else
			{
				$monthvar = $obj->monthvar;
				$yearvar = $obj->yearvar;
				$dayvar = $obj->dayvar;
			}


			$coacharray = array();

			$vars = 24;

			$sql = "SELECT  `store`.`id` as `Store_ID`," .

			"`store`.`home_office_id` as `Home_Office_ID`," .

			"`store`.`store_name` as `Store_Name`, " .
			"`store`.`state_id` as State," .
			"store.city as City, '$monthvar' as Month, '$dayvar' as Day,'$yearvar' as Year, " .

			"Concat(user.lastname, ' ,' ,user.firstname)  as Manager," .
			"trade_area.region as District," .

			"dashboard_report_cache_store_classification.age_months as `Months_Open`," .
			"dashboard_report_age_class.description as `Store_Age`,".


			"`dashboard_report_cache_main`.`transaction_totals` AS `\$_Adjusted_Gross_Revenue`, " .
			"`dashboard_report_cache_retention`.`retention_percentage` * 100  as `%_Retention`, " .
			"`dashboard_report_cache_instore`.`in_store_orders` as `%_In_Store_Signups`, " .
			"`dashboard_report_cache_main`.`distinct_users` as `Guest_Visits`, " .
			"`dashboard_report_cache_main`.`customers_per_session` as `Avg_Guest_Visits_Per_Session`, " .
			"`dashboard_report_cache_main`.`percent_change_transactions` as `%_Change_PriorMonth_Adjusted_Gross_Revenue`, " .
			"`dashboard_report_cache_retention`.`percent_change`as `%_Change_Prior_Month_Retention`, " .
			"`dashboard_report_cache_instore`.`percent_change`as `%_Change_Prior_Month_InStore_SignUps`, " .
			"`dashboard_report_cache_main`.`percent_change_distinct`as `%_Change_Prior_Month_Guest_Visits`, " .
			"`dashboard_report_cache_main`.percent_change_session_avg as `%_Change_Prior_Month_Guest_Visits_Per_Session`, store_trade_area.trade_area_id ,  (`dashboard_report_cache_main`.`transaction_totals`/IF(`dashboard_report_cache_main`.`distinct_users` = 0, 0,`dashboard_report_cache_main`.`distinct_users`)) as '\$_AverageTicket' " .
			"FROM dashboard_report_cache_main " .
			"Left Join `store` ON `dashboard_report_cache_main`.`store_id` = `store`.`id` " .
			"Left Join `dashboard_report_cache_retention` ON `store`.`id` = `dashboard_report_cache_retention`.`store_id` AND `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_retention`.`dashboard_report_cache_log_id` AND `dashboard_report_cache_main`.`monthvalue` = `dashboard_report_cache_retention`.`monthvar` AND `dashboard_report_cache_main`.`yearvalue` = `dashboard_report_cache_retention`.`yearvar` " .
			"Left Join `dashboard_report_cache_instore` ON `store`.`id` = `dashboard_report_cache_instore`.`store_id` AND `dashboard_report_cache_main`.`monthvalue` = `dashboard_report_cache_instore`.`monthvar` AND `dashboard_report_cache_main`.`yearvalue` = `dashboard_report_cache_instore`.`yearvar` AND `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = `dashboard_report_cache_instore`.`dashboard_report_cache_log_id` " .

			"Left Join store_coach ON store_coach.store_id = store.id " .
			"Left Join coach ON store_coach.coach_id = coach.id " .
			"Left Join user On coach.user_id = user.id " .

			"Left Join store_trade_area ON store_trade_area.store_id = store.id " .
			"Left Join trade_area ON trade_area.id = store_trade_area.trade_area_id " .

			"Left Join dashboard_report_cache_store_classification ON `store`.`id` = `dashboard_report_cache_store_classification`.`store_id` " .
			" AND `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = dashboard_report_cache_store_classification.dashboard_report_cache_log_id " .

			"Left Join dashboard_report_age_class ON dashboard_report_cache_store_classification.dashboard_report_age_class_id = dashboard_report_age_class.id " .

			"where `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = $cachelogid  and " .
			" store_coach.is_deleted = 0 and store_coach.is_active = 1 and  store_trade_area.is_active = 1 and store_trade_area.is_deleted = 0 and " ;



			if ($store_id != null) {
				$sql .= " dashboard_report_cache_main.store_id = $store_id  and ";
			}
			else if ($state_id != null) {
				$sql .= " store.state_id = '$state_id' and ";
			}
			else if ($district_id != null) {
				$sql .= " dashboard_report_cache_store_classification.trade_area_id = $district_id and ";
			}

			else if ($region_id != null) {
				$sql .= " store_coach.coach_id = $region_id and ";
			}

			if ($coachid != null) {
			$sql .= " store_coach.coach_id = $coachid and ";
			}


			$sql .= " `dashboard_report_cache_main`.`monthvalue` = $monthvar " .
			"and `dashboard_report_cache_main`.`yearvalue` = $yearvar and `dashboard_report_cache_main`.`is_historical` = 0 " .
			"order by `store`.`state_id`,`store`.`city`, store.store_name";



			$obj = DAO_CFactory::create("store");
			$obj->query($sql);



			while($obj->fetch()){
				$temparray = $obj->toArray();

				array_splice($temparray, $vars, count($temparray));
				$masterarray[$temparray['Store_ID']][$yearvar][$monthvar] = $temparray;

			}

			$prioryear = $yearvar - 1;

			$sql = "SELECT	`dashboard_report_cache_main`.`store_id`,`dashboard_report_cache_main`.`transaction_totals` as ytdcurrent," .
			"subquery1.`transaction_totals` as ytdpast, trade_area_id " .
			"FROM " .
			"`dashboard_report_cache_main` " .
			"Left Join dashboard_report_cache_store_classification ON `dashboard_report_cache_main`.store_id = " .
			"dashboard_report_cache_store_classification.store_id " .
			"and dashboard_report_cache_store_classification.dashboard_report_cache_log_id = `dashboard_report_cache_main`.dashboard_report_cache_log_id " .
			"Left Join " .
			"( " .
			"SELECT " .
			"`dashboard_report_cache_main`.`store_id`, " .
			"`dashboard_report_cache_main`.`transaction_totals` " .
			"FROM " .
			"`dashboard_report_cache_main` " .
			"where `dashboard_report_cache_main`.`is_historical` = 1 and `yearvalue` = $prioryear and `monthvalue`  = $monthvar and `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = $cachelogid" .
			") as subquery1 ON subquery1.`store_id` = `dashboard_report_cache_main`.`store_id` " .
			"where `dashboard_report_cache_main`.`is_historical` = 0 and `yearvalue` = $yearvar and `monthvalue`  = $monthvar and `dashboard_report_cache_main`.`dashboard_report_cache_log_id` = $cachelogid";


			$obj = DAO_CFactory::create("dashboard_report_cache_main");
			$obj->query($sql);
			while($obj->fetch()){
				$temparray = $obj->toArray();
		//		$vararr = $masterarray[$temparray['store_id']];

			$sub = '$_Comp_Adj_Gross_Revenue_PriorYr';

	//		$masterarray[$temparray['store_id']][$yearvar][$monthvar][$sub] = 0;

if (!empty($masterarray[$temparray['store_id']][$yearvar][$monthvar])) {


				$masterarray[$temparray['store_id']][$yearvar][$monthvar][$sub] = $temparray['ytdpast'];
}

			}


			}





		}

		$finalarray = array();

		foreach($masterarray as $key => $value){
			foreach($value as $skey => $svalue){
				foreach($svalue as $sskey => $ssvalue){
				$finalarray[] = $ssvalue;
				}
			}
		}

		return $finalarray;
	}



}




?>