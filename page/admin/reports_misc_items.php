<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CSession.php');
 require_once ('includes/CSessionReports.inc');
 require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');

 class page_admin_reports_misc_items extends CPageAdminOnly {

	private $currentStore = null;

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }


	function runFranchiseManager() {
		$this->runFranchiseOwner();
	}

	function runFranchiseLead(){
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager(){
		$this->runSiteAdmin();
	}

	function runOpsLead() {
	    $this->runFranchiseOwner();
	}
	
	function runHomeOfficeStaff(){
		$this->runSiteAdmin();
	}

 	function runFranchiseOwner() {
	 	$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	 }

 	function runSiteAdmin() {

 	    if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING)
 	    {
     	    ini_set('memory_limit','-1');
     	    set_time_limit(3600 * 24);
 	    }

		$store = NULL;
		$export = FALSE;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$total_count = 0;
		$report_submitted = FALSE;
		if ( $this->currentStore ) { //fadmins
				$store = $this->currentStore;
		} else { //site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
				$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

				if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING)
				{

				    $Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
				        CForm::onChangeSubmit => true,
				        CForm::allowAllOption => true,
				        CForm::showInactiveStores => true,
				        CForm::name => 'store'));

				}
				else
				{

					$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
										CForm::onChangeSubmit => true,
										CForm::allowAllOption => false,
										CForm::showInactiveStores => true,
										CForm::name => 'store'));

				}


				$store = $Form->value('store');
		}


		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";
		$spansMenu = FALSE;

		$report_array = array();

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"]) $report_type_to_run = $_REQUEST["pickDate"];


		$Form->AddElement(array (CForm::type => CForm::Submit,
 		CForm::name => 'report_submit', CForm::value => 'Run Report'));

			//$month_array = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$year = date("Y");

		$Form->AddElement(array(CForm::type=> CForm::Text,
						CForm::name => "year_field_001",
						CForm::required => true,
						CForm::default_value => $year,
						CForm::length => 6));

		$Form->AddElement(array(CForm::type=> CForm::Text,
						CForm::name => "year_field_002",
						CForm::required => true,
						CForm::default_value => $year,
						CForm::length => 6));


		$Form->AddElement(array(CForm::type=> CForm::DropDown,
					CForm::onChangeSubmit => false,
					CForm::allowAllOption => false,
					CForm::options => $month_array,
					CForm::name => 'month_popup'));


		$Form->DefaultValues['menu_or_calendar'] = 'menu';
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "menu_or_calendar",
			CForm::required => true,
			CForm::value => 'cal'
		));
		
		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "menu_or_calendar",
			CForm::required => true,
			CForm::value => 'menu'
		));




	if (isset ($_REQUEST["single_date"])) {
		$day_start = $_REQUEST["single_date"];
		$tpl->assign('day_start_set', $day_start);
	}


	if (isset ($_REQUEST["range_day_start"])) {
		  $range_day_start = $_REQUEST["range_day_start"];
		  $tpl->assign('range_day_start_set', $range_day_start);
	}
	if (isset ($_REQUEST["range_day_end"])) {
		$range_day_end = $_REQUEST["range_day_end"];
		$tpl->assign('range_day_end_set', $range_day_end);
	}



	if (isset($_REQUEST["report_type"]) && isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"])  && isset ($_REQUEST["duration"])) {
		$export = TRUE;
		$report_type_to_run = $_REQUEST["report_type"];
	}


		if ( $Form->value('report_submit') || $export == TRUE) {
			$report_submitted = TRUE;
			$sessionArray = null;
			$menu_array_object = NULL;
			if ($report_type_to_run == 1) {
				if ($export == FALSE) {
					$implodedDateArray = explode("-",$day_start) ;
					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$sessionArray = retrieveMiscCostListForPeriod($store, $day, $month, $year,  '1 DAY');
				}
				else
					$sessionArray = retrieveMiscCostListForPeriod($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"],  '1 DAY');
				// get the single date
			}
			else if ($report_type_to_run == 2) {
				// process for an entire year

				if ($export == FALSE) {
					$rangeReversed = false;
					$implodedDateArray = null;
					$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
					$diff++;  // always add one for SQL to work correctly
					if ($rangeReversed == true)
					    $implodedDateArray = explode("-",$range_day_end);
					else
					 	$implodedDateArray = explode("-",$range_day_start) ;

					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$duration = $diff . ' DAY';


					$diffMonth = $SessionReport->datediff("m", $range_day_start, $range_day_end, $rangeReversed);
					if ($diffMonth > 1) $spansMenu = TRUE;


					$sessionArray = retrieveMiscCostListForPeriod($store, $day, $month, $year,  $duration);
				}
				else {
			 		$sessionArray = retrieveMiscCostListForPeriod($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"],$_REQUEST["duration"]);
				}

			}
			else if ($report_type_to_run == 3) {
				
					$month = $_REQUEST["month_popup"];
					$month++;
					$year = $_REQUEST["year_field_001"];
				
				
					if ($Form->value('menu_or_calendar') == 'menu')
					{
						if ($export == FALSE) 
						{
							
							// menu month
							$anchorDay = date("Y-m-01", mktime(0,0,0,$month,1, $year));
							list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
							$start_date = strtotime($menu_start_date);
							$year = date("Y", $start_date);
							$month = date("n", $start_date);
							$day = date("j", $start_date);
								
							$duration = $interval . " DAY";
							
							$sessionArray = retrieveMiscCostListForPeriod($store, $day, $month, $year, $duration);
							
						}
						else 
						{
							$sessionArray = retrieveMiscCostListForPeriod($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST['duration']);
						}
					}
					else
					{

					// process for a given month
						if ($export == FALSE) 
						{
						$day = "01";
						$duration = '1 MONTH';
						$sessionArray = retrieveMiscCostListForPeriod($store, $day, $month, $year,  '1 MONTH');
						}
					else
					{
						$sessionArray = retrieveMiscCostListForPeriod($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"],  '1 MONTH');
					}
				}
			}
			else if ($report_type_to_run == 4) {

				set_time_limit ( 120 );
				$spansMenu = TRUE;
				if ($export == FALSE) {
					$year = $_REQUEST["year_field_002"];
					$month = "01";
					$day = "01";
					$duration = '1 YEAR';
					$sessionArray = retrieveMiscCostListForPeriod($store, $day, $month, $year,  $duration);
				}
				else
					$sessionArray = retrieveMiscCostListForPeriod($store, $_REQUEST["day"], $_REQUEST["month"], $_REQUEST["year"], $_REQUEST["duration"]);

			}

			$exportStr = "";
			if ($export == TRUE)
			{

			    if ($store == 'all')
			    {
			        $labels = array('Store', 'City', 'State', 'Customer Name', 'Session', 'Type', 'Description', 'Price');
			        $columnDescs = array('H' => array('align' => 'center', 'type' => 'currency'));

			    }
			    else
			    {
					$labels = array('Customer Name', 'Session', 'Type', 'Description', 'Price');
			        $columnDescs = array('E' => array('align' => 'center', 'type' => 'currency'));

			    }


			    $tpl->assign('col_descriptions', $columnDescs );
				$tpl->assign('labels', $labels);
				$tpl->assign('rowcount', count($sessionArray));
				$tpl->assign('rows', $sessionArray);
				$exportStr = "(Excel Export)";
			}
			else {
				if (!empty($sessionArray)) {
					$tpl->assign('table_data', $sessionArray);
				}

			}


			$numRows = count($sessionArray);
			CLog::RecordReport("Misc Items $exportStr", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run" );

		}

		$tpl->assign('report_type', $report_type_to_run);
		$tpl->assign('report_day', $day);
		$tpl->assign('report_month', $month);
		$tpl->assign('report_year', $year);
		$tpl->assign('report_duration', $duration);
		$tpl->assign('store', $store);



		$formArray = $Form->render();
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('spans_menus', $spansMenu);
		$tpl->assign('total_count', $total_count);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','Misc Add-ons Report');
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);
	}






}

?>