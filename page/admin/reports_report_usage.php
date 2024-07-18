<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CSession.php');
 require_once ('includes/CSessionReports.inc');
 require_once( "phplib/PHPExcel/PHPExcel.php");
 require_once('ExcelExport.inc');

 class page_admin_reports_report_usage extends CPageAdminOnly {

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }



 static $nameMap = array("Dream Report - Monthly Summary" => "Dream Report - Monthly Summary",
"Session" => "Session Report",
"Entree" => "Entree Report",
"Misc Items" => "Misc Add-Ons Report",
"Session/Labels Generic" => "Generic Labels Report (1 Item)",
"Session/Labels" => "Custom Labels Report (per Session)",
"Session/Customer View" => "Customer Receipts (per Session)",
"Session/Dream Rewards" => "Customer Dream Rewards Report (per Session)",
"Session Goal Sheet" => "Session Goal Sheet",
"Royalty" => "Royalty Report",
"Payment Details" => "Payment Reconciliation Report (obsolete)",
"Customer (csv export)" => "Order History Report (obsolete)",
"Session/Labels Generic All" => "Generic Labels Report (All Items)",
"Dream Report - Weekly Report" => "Dream Report - Weekly Report",
"Payment Details (CSV Export)" => "Payment Reconciliation Report",
"Session/Franchise View" => "Franchise Receipts (per Session)",
"Store Expenses" => "Store Expenses Report (obsolete)",
"Session/Multi Customer View" => "Customer Receipts (per Day)",
"Dream Report - Customer Count" => "Dream Reports - Customer Count (obsolete)",
"Session/Multi Franchise View" => "Franchise Receipts (per Day)",
"Dream Report - Summary Report" => "Dream Reports - Summary Report (obsolete)",
"Royalty Invoice" => "Royalty Report - Print Invoice",
"Dream Report - Summary Report(CSV Export)" => "Dream Reports - Summary Report Export (obsolete)",
"Guest Profiles" => "Guest Details Report (obsolete)",
"Dashboard" => "Dashboard (obsolete)",
"Session/Multi Labels" => "Custom Labels Report (per Day)",
"Non Linked Report" => "???",
"Dashboard Aggregate" => "Dashboard Aggregate Report",
"Dream Report - Summary Report Expenses Only" => "Dream Report - Summary Report Expenses Only  (obsolete)",
"Cancellation" => "Cancellation Report (obsolete)",
"Cancellation (CSV Export)" => "Cancellation Report (new)",
"Financial Statistical" => "Financial Statistical Report (obsolete)",
"Financial Statistical (CSV export)" => "Financial Statistical Report CSV Export (obsolete)",
"Made For You Guest Detail Report (CSV Export)" => "Made For You Guest Detail Report CSV Export  (obsolete)",
"Session/Multi Dream Rewards" => "Customer Dream Rewards Report (per Day)",
"Food Survey" => "Old Food Survey Report",
"Session/SideDish Report" => "Side Dish Report (per Session)",
"Session/Multi Future Orders" => "Future Orders Report (per Day)",
"Session/Future Orders" => "Future Orders Report (per Session)",
"Coupon" => "Coupon Report",
"Coupon (CSV export)" => "Coupon Report CSV Export",
"Customer Referral Revenue" => "Customer Referral Revenue ",
"Customer Referral Revenue (CSV Export)" => "Customer Referral Revenue CSV Export",
"Misc Items (CSV Export)" => "Misc Add-Ons Report CSV Export",
"Inactive Guests (summary)" => "Inactive Guests Summary (PING)",
"Inactive Guests" => " Inactive Guests Detail (PING)",
"Preferred User" => "Preferred Users Report",
"Payment Details - Discover Gift Card" => "Discover Gift Card Payment Report (obsolete)",
"Session/Multi SideDish Report" => "Side Dish Report (per Day)",
"Session/Multi Fast Lane Report" => "Fast Lane Report (per Day)",
"Session/Fast Lane Report" => "Fast Lane Report (per Session)",
"Royalty (CSV Export)" => "Royalty Report CSV Export",
"Made For You Guest Summary Report (CSV Export)" => "Made For You Guest Summary Report CSV Export",
"Menu Push" => "Menu Push Report",
"Store Credit" => "Credit Report",
"Dream Report - Weekly Report (CSV Export)" => "Dream Report - Weekly Report CSV Export (obsolete)",
"Dream Report - Summary Report Expenses Only (CSV Export)" => "Dream Report - Summary Report Expenses Only CSV Export (obsolete) ",
"Menu Mix" => "Menu Mix Report (obsolete)",
"Payment Details - Debit Gift Card" => "Debit Gift Card Payment Report (obsolete)",
"Food Survey Comments" => "Food Survey Comments (obsolete)",
"Made For You Opt-in Summary Report by Store (CSV Export)" => "Made For You Opt-in Summary Report by Store CSV Export (obsolete)",
"Store Credit (CSV Export)" => "Credit Report CSV Export",
"Dream Report - Customer Count (CSV Export)" => "Dream Report - Customer Count CSV Export (obsolete)",
"Preferred User (CSV Export)" => "Preferred Users Report CSV Export",
"Session/Labels Nutritionals" => "Labels Nutritionals - every page load",
"Session/Labels Nutritionals All FT" => "Nutrition Labels - Sides & Sweets All",
"Session/Labels Nutritionals Single FT" => "Nutrition Labels - Sides & Sweets Single Item",
"Payment Details - Discover Gift Card (CSV Export)" => "Discover Gift Card Payment Report CSV Export (obsolete)",
"Session/Labels Nutritionals Single Entree" => "Nutrition Labels - Single Entr�e",
"Session/Labels Nutritionals All Entree" => "Nutrition Labels - All Entrees",
"Customer (xlsx export)" => "Order History Report",
"Payment Details (Excel Export)" => "Payment Reconciliation Report",
"Made For You Guest Summary Report (Excel Export)" => "Made For You Guest Summary Report Excel Export (obsolete)",
"Financial Statistical (Excel export)" => "Financial Statistical Report v2 Excel Export",
"Made For You Guest Detail Report (Excel Export)" => "Made For You Guest Detail Report Excel Export (obsolete)",
"Dream Report - Summary Report(Excel Export)" => "Dream Report - Summary Report Excel Export (obsolete) ",
"Cancellation (Excel Export)" => "Cancellation Report",
"Dream Report - Weekly Report (Excel Export)" => "Weekly Report",
"Dream Report - Customer Count (Excel Export)" => "Dream Report - Customer Count Excel Export (obsolete)",
"Misc Items (Excel Export)" => "Misc Add-Ons Report Excel Export",
"Performance Override" => "Performance Override Report",
"Royalty (XLSX Export)" => "Royalty Report Excel Export",
"Customer Referral Revenue (Excel Export)" => "Customer Referral Revenue Report Excel Export ",
"Coupon (Excel export)" => "Coupon Report Excel Export",
"Store Credit (Excel Export)" => "Credit Report Excel Export",
"Dream Report - Summary Report Expenses Only (Excel Export)" => "Dream Report - Summary Report Expenses Only Excel Export (obsolete)",
"Payment Reconciliation Report (Excel Export)" => "Payment Reconciliation Report",
"CashFlow Report (Excel Export)" => "CashFlow Report",
"Payment Details - Debit Gift Card (Excel Export)" => "Payment Details - Debit Gift Card Excel Export (obsolete)",
"Session/Multi Franchise Receipt" => "Franchise Receipts (per Day)",
"Session/Multi Customer Receipt" => "Customer Receipts (per Day)",
"Session/Franchise Receipt" => "Franchise Receipts (per Session)",
"Session/Customer Receipt" => "Customer Receipts (per Session)",
"Preferred User (Excel Export)" => "Preferred Users Report Excel Export",
"Guest Details Report" => "Guest Details Report ",
"Session Summary and Goal Tracking Report" => "Session Summary and Goal Tracking Report ",
"New Dashboard Report" => "New Dashboard Report ",
"Trending Report" => "Trending Report ",
"New Dashboard Export (Excel Export)" => "New Dashboard Export Excel Export",
"Trending Report Export (export xlsx)" => "Trending Report Export Excel Export",
"Financial Statistical v2 (Excel export)" => "Financial Statistical Report New",
"Dream Report - Weekly Report v2 (Excel Export)" => "Dream Report - Weekly Report New",
"Session/Multi Store Receipt" => "Franchise Receipts (per Session)",
"Session/Store Receipt" => "Franchise Receipts (per Day)",
"Performance Override" => "Performance Override Submission",
"Entry Summary per Day" => "Entry Summary per Day",
"Entry Summary per Session" => "Entry Summary per Session",
"Printable Finishing Touch Order Form" => "Printable Sides and Sweets Order Form",
"Printable Sides and Sweets Order Form" => "Printable Sides and Sweets Order Form",
"PLATEPOINTS Status and Rewards Report per Session" => "PLATEPOINTS Status and Rewards Report per Session",
"PLATEPOINTS Status and Rewards Report per Day" => "PLATEPOINTS Status and Rewards Report per Day",
"Enrollment Forms per Session" => "Enrollment Forms per Session",
"Enrollment Forms per Day" => "Enrollment Forms per Day",
"Enrollment Form per User" => "Enrollment Forms per User",
"Enrollment Form Blank" => "Enrollment Form Blank"
 );

 	function runHomeOfficeManager(){
		$this->runSiteAdmin();
	}


 	function runSiteAdmin() {

		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$report_type_to_run = 1;
		$total_count = 0;
		$SessionReport = new CSessionReports();

		$report_submitted = FALSE;

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
		$monthnum = date("n");
		$monthnum--;
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
					CForm::default_value => $monthnum,
					CForm::name => 'month_popup'));

		$hadError = false;


		if (isset ($_REQUEST["single_date"])) {
			$day_start = $_REQUEST["single_date"];
		}


		if (isset ($_REQUEST["range_day_start"])) {
			  $range_day_start = $_REQUEST["range_day_start"];
		}
		if (isset ($_REQUEST["range_day_end"])) {
			$range_day_end = $_REQUEST["range_day_end"];
		}

		if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"])  && isset ($_REQUEST["duration"])) {
				$day = $_REQUEST["day"];
				$month = $_REQUEST["month"];
				$year = $_REQUEST["year"];
				$duration = $_REQUEST["duration"];
		}

		if (isset ($_REQUEST["report_submit"])) {


			if ($report_type_to_run == 1) {
				$implodedDateArray = explode("-",$day_start) ;
				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = '1 DAY';

			}
			else if ($report_type_to_run == 2) {

				$startArr = explode("-", $range_day_start);
				$endArr = explode("-", $range_day_end);

				if ($startArr[0] != $endArr[0])
				{
					$tpl->setErrorMsg("When reporting on a date range the start and end dates must be within the same year.");
					$hadError = true;
				}


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
			}
			else if ($report_type_to_run == 3) {

				// process for a given month
				$day = "01";
				$month = $_REQUEST["month_popup"];
				$month++;
				$duration = '1 MONTH';
				$year = $_REQUEST["year_field_001"];

			}
			else if ($report_type_to_run == 4) {
				$spansMenu = TRUE;
				$year = $_REQUEST["year_field_002"];
				$month = "01";
				$day = "01";
				$duration = '1 YEAR';

			}

			$greatestNumberCancelled = 0;

			if (!$hadError)
			{
		    	$rows = $this->findReportUsage($day, $month, $year,  $duration, $greatestNumberCancelled);
		    	$numRows = count($rows);

		    	$this->postProcess($rows);

		    	CLog::RecordReport("Report Usage Report", "Rows:$numRows ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration" );

		    	if ($numRows)
		    	{
					$labels = array("Report Name","Number of Runs", "Last Run Time");

					$columnDescs = array();

					$columnDescs['A'] = array('align' => 'left', 'width' => 'auto'); //id
					$columnDescs['B'] = array('align' => 'left', 'width' => 'auto'); //first
					$columnDescs['C'] = array('align' => 'left', 'width' => 'auto', 'type' => 'datetime'); //last

					$tpl->assign('col_descriptions', $columnDescs );

					$tpl->assign('labels', $labels);
					$tpl->assign('rows', $rows);
					$tpl->assign('rowcount', $numRows);

					$_GET['export'] = 'xlsx';
		    	}
		    	else
		    	{

		    		$tpl->assign('empty_result', true);
		    	}

			}

		}

		$formArray = $Form->render();

		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','Report Usage Report');
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);
	}


	function postProcess(&$rows)
	{
		foreach ($rows as $name => &$data)
		{
			$data['lastRun'] = PHPExcel_Shared_Date::stringToExcel($data['lastRun']);
		}


	}

	function findReportUsage($Day, $Month, $Year, $Interval, &$greatestNumberCancelled)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$db = CLog::instance()->connect();

		//$result = mysql_query("use dreamcart");

		$table_name =  "event_log_" . $Year;

		$query = "SELECT LEFT(description, instr(description, '|') - 1) as report_name, count(id) as numRuns, max(timestamp_created) as lastRun, description FROM $table_name WHERE log_type = 'REPORT' and timestamp_created >= '$current_date_sql' and
						timestamp_created < DATE_ADD('$current_date_sql', INTERVAL $Interval)
						GROUP BY LEFT(description, instr(description, '|') - 1) ORDER BY id asc ";

		$res = mysqli_query($db, $query);

		$resultsArray = array();
		while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
		{

			$internalName = trim($row['report_name']);

			if ($internalName == 'Food Survey'  ||  $internalName == 'Food Survey Comments')
				continue;

			if ($internalName == 'Non Linked Report')
			{
				$tempArr = explode("|", $row['description']);
				$displayName ="Non-Linked: " . $tempArr[1];
			}
			else
			{
				$displayName = self::$nameMap[$internalName];
			}

			if (empty($displayName))
				$displayName = $internalName;

			if (isset($resultsArray[$displayName]))
			{
				$resultsArray[$displayName]['numRuns'] += $row['numRuns'];

				if (strtotime($row['lastRun']) > strtotime($resultsArray[$displayName]['lastRun'] ))
					$resultsArray[$displayName]['lastRun'] = $row['lastRun'];
			}
			else
			{
				$resultsArray[$displayName] = array('name' => $displayName, 'numRuns' => $row['numRuns'], 'lastRun' => $row['lastRun']);
			}
		}

		return $resultsArray;

	}
}


?>