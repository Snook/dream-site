<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CDreamReport.inc");
require_once("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/CSessionReports.inc');
require_once ('includes/CCalendar.inc');
require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_dream_weekly_v2 extends CPageAdminOnly {
	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$canoverride = CApp::overrideAdminPage(true, $this->currentStore);
		if ($canoverride)
		{
			$this->runSiteAdmin();
		}
		else
			CApp::bounce('/backoffice/access-error?topnavname=reports&pagename=reports_dream_weekly_v2');
	}

	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$canoverride = CApp::overrideAdminPage(true, $this->currentStore);
		if ($canoverride)
		{
			$this->runSiteAdmin();
		}
		else
			CApp::bounce('/backoffice/access-error?topnavname=reports&pagename=reports_dream_weekly_v2');
	}

	function runFranchiseOwner() {
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager() {
		$this->runSiteAdmin();
	}

	function runSiteAdmin() {
		$store = NULL;
		$export = FALSE;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 3;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$total_count = 0;
		$report_submitted = FALSE;
		$yearMonthDayArr = null;

		$Form->DefaultValues['store_type'] = 'single_store';

		if ( $this->currentStore ) { //fadmins
			$store = $this->currentStore;
		}
		else
		{
			$Form->DefaultValues['single_store_select'] = array_key_exists('single_store_select', $_GET)? $_GET['single_store_select'] : null;

			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
									CForm::onChangeSubmit => true,
									CForm::allowAllOption => false,
									CForm::showInactiveStores => true,
									CForm::name => 'single_store_select'));
			$store = $Form->value('single_store_select');
		}



		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "store_type",
								CForm::value => 'single_store'));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "store_type",
								CForm::value => 'all_stores'));

		$store_type = $Form->value('store_type');

		if($store_type == 'all_stores'){
			$store = 'ALL';
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
		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$year = date("Y");
		$curMonth = date("n");

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
								CForm::default_value => $curMonth - 1,
								CForm::name => 'month_popup'));

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
		if (isset($_REQUEST["report_type"])) {
			$report_type_to_run = $_REQUEST["report_type"];
		}

		$isMenuRequest = false;


		$exportData = array_key_exists('export', $_REQUEST)? $_REQUEST['export'] : null;

		if ($exportData == 'xlsx' ||  $Form->value('report_submit')) {
			if ($exportData == null) {
				if ($store == "") {
					$tpl->setErrorMsg("Please Assign a Store!");
					$formArray = $Form->render();
					$tpl->assign('report_submitted', FALSE);
					$tpl->assign('page_title','Dream Report - Weekly Report');
					$tpl->assign('form_session_list', $formArray);
					return;
				}

				$report_submitted = TRUE;
				if ($report_type_to_run == 1) {
					$implodedDateArray = explode("-",$day_start) ;
					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$start_date = mktime(0, 0, 0, $month, $day, $year);
					$enddate = strtotime("+1 day", $start_date);
				}
				else if ($report_type_to_run == 2) {
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
					$start_date = mktime(0, 0, 0, $month, $day, $year);
					$vr = "+" . $diff  . " day";
					$enddate = strtotime($vr, $start_date);

					$tpl->assign('file_name', 'admin_report_weekly_summary_'.$range_day_start.'--'.$range_day_end);

				}
				else if ($report_type_to_run == 3) {
					$month = $_REQUEST["month_popup"];
					$year = $_REQUEST["year_field_001"];
					$month++;

					if ($Form->value('menu_or_calendar') == 'menu')
					{
						$anchorDay = date("Y-m-01", mktime(0,0,0,$month,1, $year));
						list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
						$start_date = strtotime($menu_start_date);
						$year = date("Y", $start_date);
						$month = date("n", $start_date);
						$day = date("j", $start_date);

						$duration = $interval . " DAY";
						$enddate = $start_date + ($interval * 86400);

						$isMenuRequest = true;


					}
					else
					{
						$day = "01";
						$duration = '1 MONTH';
						$start_date = mktime(0, 0, 0, $month, $day, $year);
						$enddate = strtotime("+1 month", $start_date);
					}
					$tpl->assign('file_name', 'admin_report_weekly_summary_'.$month.'-'.$year);
				}
				else if ($report_type_to_run == 4) {
					$year = $_REQUEST["year_field_002"];
					$month = "01";
					$day = "01";
					$duration = '1 YEAR';
					$start_date = mktime(0, 0, 0, $month, $day, $year);
					$enddate = strtotime("+1 year", $start_date);
				}
				$rows = array();
			}
			else if ($exportData == 'xlsx') {
				$year 		= $_REQUEST["year"];
				$month 		= $_REQUEST["month"];
				$day 		= $_REQUEST["day"];
				$duration 	= $_REQUEST["duration"];
				$start_date = mktime(0, 0, 0, $month, $day, $year);


				$tpl->assign('headersAreEmbedded', true);
			}



			$rows = $this->getWeeklyData($store, $day, $month, $year,  $duration);

			$programdiscounts = $this->findProgramTypesByWeek($store, $day, $month, $year,  $duration);
			$certsUsed = $this->getWeeklyCertificateAdjustments($store, $day, $month, $year, $duration);

			$adjs = $this->getWeeklySalesAdjustments($store, $day, $month, $year, $duration);

			$COGS = $this->getWeeklyFoodAndLaborCosts($store, $day, $month, $year, $duration);

			$this->mergeData($rows, $programdiscounts, $adjs, $COGS, $certsUsed, $start_date, $enddate, $isMenuRequest);

			$labels = array("Range Start Date",	"Range End Date",	"Week",	"Number Sessions", "Guests / Session", "Guests New", "Guests Reacquired" , "Guests Existing", "Guests Total",
							"Gross Revenue", "Total Discounts", "Sales Adjustments", "Adjusted Gross Revenue", "Total", "Food & Packaging", "Labor", "Net Sales");


			$columnDescs = array();
			$columnDescs['A'] = array('align' => 'center', 'width' => 'auto', 'type' => 'datetime');
			$columnDescs['B'] = array('align' => 'center', 'width' => 'auto', 'type' => 'datetime');
			$columnDescs['C'] = array('align' => 'center', 'width' => 6);
			$columnDescs['D'] = array('align' => 'center', 'width' => 8);
			$columnDescs['E'] = array('align' => 'center', 'width' => 8);
			$columnDescs['F'] = array('align' => 'center', 'width' => 8);
			$columnDescs['G'] = array('align' => 'center', 'width' => 8);
			$columnDescs['H'] = array('align' => 'center', 'width' => 8);
			$columnDescs['I'] = array('align' => 'center', 'decor' => 'subtotal', 'width' => 8);
			$columnDescs['J'] = array('align' => 'right', 'width' => 'auto', 'type' => 'currency');
			$columnDescs['K'] = array('align' => 'right', 'width' => '12', 'type' => 'currency');
			$columnDescs['L'] = array('align' => 'right', 'width' => '12', 'type' => 'currency');
			$columnDescs['M'] = array('align' => 'right', 'width' => '15', 'type' => 'currency', 'decor' => 'majortotal');
			$columnDescs['N'] = array('align' => 'right', 'width' => '12', 'type' => 'currency');
			$columnDescs['O'] = array('align' => 'right', 'width' => '15', 'type' => 'currency');
			$columnDescs['P'] = array('align' => 'right', 'width' => '15', 'type' => 'currency');
			$columnDescs['Q'] = array('align' => 'right', 'width' => 'auto', 'type' => 'currency', 'decor' => 'majortotal');

			$sectionHeader = array();
			$sectionHeader[" "] = 3;
			$sectionHeader["Session Statistics"] = 6;
			$sectionHeader["Adjusted Gross Revenue"] = 4;
			$sectionHeader["Tax"] = 1;
			$sectionHeader["  "] = 2;
			$sectionHeader["\n"] = 1;


			$exportStr = "(Excel Export)";


			$numRows = count($rows);
			CLog::RecordReport("Dream Report - Weekly Report v2 $exportStr", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run" );

			$tpl->assign('rows', $rows);

			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows);
			$tpl->assign('rowcount', $numRows);
			$tpl->assign('sectionHeader', $sectionHeader);
			$tpl->assign('col_descriptions', $columnDescs );
			//$callbacks = array('row_callback' => 'finStatReportRowsCallback', 'cell_callback' => 'finStatReportCellCallback');
			//$callbacks = array('row_callback' => 'finStatReportRowsCallback');
			//$tpl->assign('excel_callbacks', $callbacks );


			$_GET['export'] = 'xlsx';


		}

		$formArray = $Form->render();
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('spans_menus', $spansMenu);
		$tpl->assign('total_count', $total_count);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','Dream Report - Weekly Report');
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);
	}


	function getWeeklyCertificateAdjustments($store_id, $Day, $Month, $Year,  $interval)
	{
		$giftCertsArr = array();
		$gifttype = CPayment::GIFT_CERT;  // need to locate payment constant for htis
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$store_ids_clause = ' s.store_id = '.$store_id;

		if($store_id == 'ALL'){
			$store_ids_clause = ' s.store_id IN (select store.id from store where store.is_deleted = 0 and store.active = 1) ';
		}


		$varstr = "Select WEEK(s.session_start) as sweek
					,sum(if (p.gift_cert_type = 'SCRIP', p.total_amount * .12, p.total_amount )) as gift_cert_usage
					from  session s
					inner Join booking b on s.id = b.session_id
					inner Join orders o on  b.order_id = o.id
					inner Join payment p on o.id = p.order_id
					where p.payment_type = 'GIFT_CERT' and p.gift_cert_type in ('DONATED', 'VOUCHER', 'SCRIP') and
					$store_ids_clause and b.status = 'ACTIVE' and b.is_deleted = 0
					and s.is_deleted = 0 and s.session_publish_state != 'SAVED'  and s.session_start >= '$current_date_sql'
					AND  s.session_start <= DATE_ADD('$current_date_sql',INTERVAL $interval)
					group by WEEK(s.session_start)";
		$session = DAO_CFactory::create("session");
		$session->query($varstr);
		$counter = 0;

		while ($session->fetch())
		{
			$giftCertsArr[$session->sweek] = $session->gift_cert_usage * -1;
		}

		return $giftCertsArr;
	}


	function getWeeklySalesAdjustments($store, $day, $month, $year,  $duration)
	{
		$data = array();
		$current_date = mktime(0, 0, 0, $month, $day, $year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$arr = null;
		$store_ids_clause = ' store_expenses.store_id = '.$store ;

		if($store == 'ALL'){
			$store_ids_clause = ' store_expenses.store_id IN (select store.id from store where store.is_deleted = 0 and store.active = 1) ';
		}

		$varstr = "select WEEK(store_expenses.entry_date, 3) as weekNum, store_expenses.entry_date, store_expenses.expense_type, store_expenses.notes, store_expenses.units, store_expenses.total_cost
		From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql .
			"',INTERVAL " . $duration . ") " . $store_ids_clause. " and store_expenses.is_deleted = 0 and store_expenses.expense_type in ('FUNDRAISER_DOLLARS', 'ESCRIP_PAYMENTS','SALES_ADJUSTMENTS') order by entry_date, id DESC";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);


		while ($store_expenses->fetch())
		{

			if (!isset($data[$store_expenses->weekNum]))
				$data[$store_expenses->weekNum] = 0;

			switch($store_expenses->expense_type)
			{
				case 'FUNDRAISER_DOLLARS':
				case 'ESCRIP_PAYMENTS':
					$data[$store_expenses->weekNum] -= $store_expenses->total_cost;
					break;
				case 'SALES_ADJUSTMENTS':
					$data[$store_expenses->weekNum] += $store_expenses->total_cost;
					break;
				default:
					break;
			}

		}

		return $data;
	}


	function getWeeklyFoodAndLaborCosts($store, $day, $month, $year,  $duration)
	{
		$data = array();
		$current_date = mktime(0, 0, 0, $month, $day, $year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$arr = null;

		$store_ids_clause = ' store_id = '.$store;

		if($store == 'ALL'){
			$store_ids_clause = ' store_id IN (select store.id from store where store.is_deleted = 0 and store.active = 1) ';
		}

		$varstr = "select 
						MAKEDATE(YEAR(store_expenses.entry_date), DAYOFYEAR(store_expenses.entry_date) - (DAYOFWEEK(store_expenses.entry_date) - 2) ) as range_start,
						MAKEDATE(YEAR(store_expenses.entry_date), DAYOFYEAR(store_expenses.entry_date) + ( 8 - DAYOFWEEK(store_expenses.entry_date)) ) as range_end,
						WEEK(store_expenses.entry_date, 3) as weekNum, store_expenses.entry_date, store_expenses.expense_type, store_expenses.notes, store_expenses.units, store_expenses.total_cost
		From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql .
			"',INTERVAL " . $duration . ") " . $store_ids_clause. " and store_expenses.is_deleted = 0 and store_expenses.expense_type in ('SYSCO', 'OTHER_FOOD', 'LABOR') order by entry_date, id DESC";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);


		while ($store_expenses->fetch())
		{

			if (!isset($data[$store_expenses->weekNum]))
				$data[$store_expenses->weekNum] = array('food_costs' => 0, 'labor_costs' => 0, 'range_start' => $store_expenses->range_start,  'range_end' => $store_expenses->range_end);

			switch($store_expenses->expense_type)
			{
				case 'SYSCO':
				case 'OTHER_FOOD':
					$data[$store_expenses->weekNum]['food_costs'] += $store_expenses->total_cost;
					break;
				case 'LABOR':
					$data[$store_expenses->weekNum]['labor_costs'] += $store_expenses->total_cost;
					break;
				default:
					break;
			}

		}

		return $data;
	}


	function findProgramTypesByWeek ($storeid, $Day, $Month, $Year,  $interval)
	{
		$progarr = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$store_ids_clause = ' s.store_id = $store ';

		if($storeid == 'ALL'){
			$store_ids_clause = ' s.store_id IN (select store.id from store where store.is_deleted = 0 and store.active = 1) ';
		}


		$varstr = "SELECT WEEK(s.session_start, 3) as weekNum
				,count(sc.id) as amount_used
				,sum(p.total_amount) as amount
				,CASE WHEN cr.origination_type_code = 1 THEN 'IAF'
							WHEN cr.origination_type_code = 4 then 'TODD'
							WHEN cr.origination_type_code = 3 then 'DIRECT'
							WHEN cr.origination_type_code = 5 then 'DIRECT'
								else 'DIRECT' END as ProgramType
				  FROM session s
				Inner Join booking b ON s.id = b.session_id
				Inner Join orders o ON b.order_id = o.id
				Inner Join payment p ON o.id = p.order_id
				Inner Join store_credit sc ON p.store_credit_id = sc.id
				Left Join customer_referral cr ON p.store_credit_id = cr.store_credit_id
				Where $store_ids_clause and p.payment_type = 'STORE_CREDIT' AND b.status = 'ACTIVE'
				and b.is_deleted = 0 AND s.is_deleted = 0 and s.session_publish_state != 'SAVED'
				and  ( s.session_start >= '$current_date_sql' AND  s.session_start <= DATE_ADD('$current_date_sql',INTERVAL $interval) )
				 and sc.is_redeemed = 1 and (sc.credit_type = 2 OR sc.credit_type = 3)  group by WEEK(s.session_start, 3), ProgramType ";



		$session = DAO_CFactory::create("session");
		$session->query($varstr);

		$counter = 0;
		while ($session->fetch()) {
			$arr = $session->toArray();

			if (isset($progarr[$session->weekNum] ))
				$progarr[$session->weekNum] -= $arr['amount'];
			else
				$progarr[$session->weekNum] = $arr['amount'] * -1;

		}

		return $progarr;
	}


	function createWeekRow()
	{
		return array('range_start' => "",
					 'range_end' => "",
					 'week' => "",
					 'num_sessions' => 0,
					 'avg_guest_per_session' => 0,
					 'new_guests' => 0,
					 'reac_guests' => 0,
					 'ex_guests' => 0,
					 'total_guests' => 0,
					 'gross_sales' => 0,
					 'total_discounts' => 0,
					 'sales_adjustments' => 0,
					 'agr' => 0,
					 'subtotal_all_taxes' => 0,
					 'food_costs' => 0,
					 'labor_costs' => 0,
					 'net_sales' => 0);
	}


	function mergeData(&$sessionData, $programDiscounts, $adjustments, $COGS, $certsUsed, $startdate, $enddate, $isMenuRequest)
	{
		$needSort = false;

		$startdateSQL = date("Y-m-d H:i:s", $startdate);
		$enddateSQL = date("Y-m-d H:i:s", $enddate - 86400);
		// note that end date is 12:00 am the next day - or midnight of the last day.  To calculate week ranges we need to set the day to last day

		// take the start date and end date and figure out the week numbers needed
		// then add any that are missing
		$weeksRange = new DAO();
		$weeksRange->query("select week('$startdateSQL', 3) as start, week('$enddateSQL', 3) as end");
		$weeksRange->fetch();

		for ($x = $weeksRange->start; $x <= $weeksRange->end; $x++)
		{

			if (!isset($sessionData[$x]))
			{
				$sessionData[$x] = $this->createWeekRow();

				$YearWeek = date("Y", $startdate);
				$YearWeek .= $x;

				$DayRange = new DAO();
				$DayRange->query("SELECT STR_TO_DATE('$YearWeek Monday', '%X%V %W') as start,  DATE_ADD(STR_TO_DATE('$YearWeek Monday', '%X%V %W'), INTERVAL 6 DAY) as end");
				$DayRange->fetch();

				$sessionData[$x]['range_start'] = $DayRange->start;
				$sessionData[$x]['range_end'] = $DayRange->end;
				$sessionData[$x]['week'] = $x;
				$needSort = true;
			}
		}

		if ($needSort)
		{
			ksort($sessionData);
		}

		foreach($programDiscounts as $weekNum => $weeksDiscounts)
		{
			if (isset($sessionData[$weekNum]))
				$sessionData[$weekNum]['sales_adjustments'] += $weeksDiscounts;
			else
			{
				$thisRow = $this->createWeekRow();
				$sessionData[$weekNum] = $thisRow;
				$sessionData[$weekNum]['sales_adjustments'] += $weeksDiscounts;
			}
		}

		foreach($certsUsed as $weekNum => $certsDiscounts)
		{
			if (isset($sessionData[$weekNum]))
				$sessionData[$weekNum]['sales_adjustments'] += $certsDiscounts;
			else
			{
				$thisRow = $this->createWeekRow();
				$sessionData[$weekNum] = $thisRow;
				$sessionData[$weekNum]['sales_adjustments'] += $certsDiscounts;
			}
		}

		foreach($adjustments as $weekNum => $weeksAdjustments)
		{
			if (isset($sessionData[$weekNum]))
				$sessionData[$weekNum]['sales_adjustments'] += $weeksAdjustments;
			else
			{
				$thisRow = $this->createWeekRow();
				$sessionData[$weekNum] = $thisRow;
				$sessionData[$weekNum]['sales_adjustments'] += $weeksAdjustments;
			}
		}

		foreach($COGS as $weekNum => $cogs)
		{
			if (isset($sessionData[$weekNum]))
			{
				$sessionData[$weekNum]['food_costs'] += $cogs['food_costs'];
				$sessionData[$weekNum]['labor_costs'] += $cogs['labor_costs'];
			}
			else
			{
				$thisRow = $this->createWeekRow();
				$sessionData[$weekNum] = $thisRow;
				$sessionData[$weekNum]['week'] = $weekNum;
				$sessionData[$weekNum]['food_costs'] += $cogs['food_costs'];
				$sessionData[$weekNum]['labor_costs'] += $cogs['labor_costs'];
			}
		}


		$count = 0;
		$lastRow = count($sessionData) - 1;
		foreach($sessionData as $weekNum => &$data)
		{
			//$data['week']++; // for display purposes - matches PHP version visible in session summary

			$data['agr'] = $data['gross_sales'] + $data['sales_adjustments'] + $data['total_discounts'];
			$data['net_sales'] = ($data['agr'] + $data['subtotal_all_taxes']) - ($data['food_costs'] + $data['labor_costs'] );

			if ($count == 0)
			{
				$data['range_start'] = date("Y-m-d", $startdate);
			}
			else if ($count == $lastRow)
			{
				$data['range_end'] = date("Y-m-d", $enddate - 86400);
			}
			$count++;

			$data = array_slice($data, 0, 21);
		}


		return $sessionData;
	}

	function getWeeklyData($store, $day, $month, $year,  $duration)
	{
		$varStr = "";
		$PUBLISH_SESSIONS_STATE = 'SAVED';
		// to collect accurate financial data.. a session that is SAVED is no recorded...
		// but sessions that are either closed or published should be recorded
		// also.. only grab bookings that are ACTIVE.. do not look for RESCHEUDLED, HOLD or CANCELLED
		$current_date = mktime(0, 0, 0, $month, $day, $year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$store_ids_clause = ' s.store_id = '.$store;

		if($store == 'ALL'){
			$store_ids_clause = ' s.store_id IN (select store.id from store where store.is_deleted = 0 and store.active = 1) ';
		}

		$query = "select
						MAKEDATE(YEAR(s.session_start), DAYOFYEAR(s.session_start) - (DAYOFWEEK(s.session_start) - 2) ) as range_start,
						MAKEDATE(YEAR(s.session_start), DAYOFYEAR(s.session_start) + ( 8 - DAYOFWEEK(s.session_start)) ) as range_end
						,WEEK(s.session_start, 3) as week
						,count(distinct s.id) as num_sessions
						,count(distinct orders.id) / count(distinct s.id) as avg_guest_per_session
						,count(if (od.user_state = 'NEW', 1, null)) as new_guests
						,count(if (od.user_state = 'REACQUIRED', 1, null)) as reac_guests
						,count(if (od.user_state = 'EXISTING', 1, null)) as ex_guests
						,count(od.id) as total_guests
						,sum(orders.misc_food_subtotal + orders.misc_nonfood_subtotal + orders.subtotal_menu_items +
						orders.subtotal_home_store_markup + orders.subtotal_service_fee) + sum(orders.subtotal_products - orders.misc_nonfood_subtotal) as gross_sales
						,(sum(orders.session_discount_total) +
						sum(ifnull(orders.coupon_code_discount_total, 0)) + 
						sum(ifnull(orders.promo_code_discount_total, 0)) + 
						sum(ifnull(orders.user_preferred_discount_total, 0)) +
						sum(ifnull(orders.direct_order_discount, 0)) + 
						sum(ifnull(orders.dream_rewards_discount, 0)) +  sum(ifnull(orders.volume_discount_total, 0)) +  
						sum(ifnull(orders.bundle_discount, 0)) +  sum(ifnull(orders.points_discount_total, 0))) * -1 as total_discounts
						,(sum(ifnull(orders.fundraiser_value, 0)) + sum(ifnull(orders.subtotal_ltd_menu_item_value, 0))) * -1  as sales_adjustments
						,0.0 as agr
						,sum(orders.subtotal_all_taxes) as subtotal_all_taxes
						,0.0 as food_costs
						,0.0 as labor_costs
						,0.0 as net_sales
						from booking
						inner join session s on booking.session_id = s.id
						inner join orders on booking.order_id = orders.id
						join orders_digest od on od.order_id = orders.id and od.is_deleted = 0
						where $store_ids_clause and s.session_start >= '$current_date_sql' and
							s.session_start <=  DATE_ADD('$current_date_sql', INTERVAL $duration) and
							booking.status  = 'ACTIVE' and s.session_publish_state != 'SAVED'
							group by WEEK(s.session_start, 3) order by s.session_start";

		$booking = DAO_CFactory::create("booking");
		$booking->query($query);
		$rows = array();
		$count = 0;

		while ($booking->fetch())
		{
			$vartemp = $booking->toArray();
			$rows[$booking->week] = $vartemp;

		}

		return $rows;
	}



}

?>