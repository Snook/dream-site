<?php // page_admin_create_store.php

/**
 * @author Carl Samuelson
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CSession.php');
 require_once ('includes/CSessionReports.inc');
 require_once ('includes/DAO/BusinessObject/CStoreExpenses.php');


 define('RT_SESSION_SUMMARY', 1);
 define('RT_STORE_SUMMARY', 2);
 define('RT_GUEST_SUMMARY', 3);
 define('RT_GUEST_DETAIL', 4);

 class page_admin_reports_made_for_you extends CPageAdminOnly {

	private $currentStore = null;

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }

	function runFranchiseManager() {
		$this->runFranchiseOwner();
	}
	function runOpsLead() {
		$this->runFranchiseOwner();
	}
	function runFranchiseLead(){
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager(){
		$this->runSiteAdmin();
	}

 	function runFranchiseOwner() {
	 	//$Owner = DAO_CFactory::create('user_to_franchise');
	 	//$Owner->user_id = CUser::getCurrentUser()->id;
	 	//if ( !$Owner->find(true) )
	 	//	throw new Exception('not a franchise owner, or store not found for current user');
	 	//$this->_franchise_id = $Owner->franchise_id;
	 	$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	 }

 	function runSiteAdmin() {
		$store = NULL;
		$export = FALSE;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$total_count = 0;
		$report_submitted = FALSE;

		$report_type = RT_STORE_SUMMARY;
		if (isset($_REQUEST['MFY_Report_Type']))
			$report_type = $_REQUEST['MFY_Report_Type'];

		if ($report_type == RT_GUEST_DETAIL)
		{
			$session_type_array = array('STANDARD' => 'STANDARD', 'SPECIAL_EVENT' => 'Made For You', 'ALL' => 'ALL SESSIONS');

			$Form->DefaultValues['session_type_filter'] = 'SPECIAL_EVENT';

			$Form->AddElement(array(CForm::type=> CForm::DropDown,
				CForm::onChangeSubmit => false,
				CForm::allowAllOption => false,
				CForm::options => $session_type_array,
				CForm::name => 'session_type_filter'));

		}


		if ( $this->currentStore ) { //fadmins
				$store = $this->currentStore;
		} else { //site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
				$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

				$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
										CForm::onChangeSubmit => false,
										CForm::allowAllOption => ($report_type != RT_GUEST_DETAIL),
										CForm::showInactiveStores => true,
										CForm::name => 'store'));


				$store = $Form->value('store');
		}

		if ($store === "all")
		{
			$tpl->assign("store_name", "All Stores");
		}
		else if (is_numeric($store))
		{
			$storeDAO = DAO_CFactory::create('store');
			$storeDAO->id = $store;
			$storeDAO->selectAdd();
			$storeDAO->selectAdd('store_name, city');
			$storeDAO->find(true);

			$tpl->assign("store_name", $storeDAO->city . "--" . $storeDAO->store_name);

		}
		else
		{
			$tpl->assign("store_name", "N/A");
		}

		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";
		$spansMenu = FALSE;

		$report_type_to_run = 1;
		if (isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"]) $report_type_to_run = $_REQUEST["pickDate"];

		$report_array = array();

		$Form->AddElement(array (CForm::type => CForm::Submit,
 		CForm::name => 'report_submit', CForm::value => 'Run Report', CForm::addOnClick => true));

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



		$title_range = "";

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


		if ( $Form->value('report_submit') || (isset($_GET['export']) && $_GET['export'] === "xlsx")) {
			$report_submitted = TRUE;
			$sessionArray = null;
			$menu_array_object = NULL;

			if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"])  && isset ($_REQUEST["duration"]))
			{
				// these guys are set if the export link was clicked

				$day = $_REQUEST["day"];
				$month = $_REQUEST["month"];
				$year = $_REQUEST["year"];
				$duration = $_REQUEST["duration"];
			}
			else if ($report_type_to_run == 1 && $report_type != RT_STORE_SUMMARY) {
				$implodedDateArray = explode("-",$day_start) ;
				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = '1 DAY';

				$title_range = date('l F jS, Y', mktime(0,0,0,$month,$day,$year));

			}
			else if ($report_type_to_run == 2) {
				$SessionReport = new CSessionReports();
				$rangeReversed = false;
				$implodedDateArray = null;
				$implodedEndDataArray = null;
				$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
				$diff++;  // always add one for SQL to work correctly

				if ($rangeReversed == true)
				{
				    $implodedDateArray = explode("-",$range_day_end);
				    $implodedEndDataArray = explode("-",$range_day_start);
				}
				else
				{
				 	$implodedDateArray = explode("-",$range_day_start) ;
				    $implodedEndDataArray = explode("-",$range_day_end);
				}

				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = $diff . ' DAY';

				$title_range = " " . date('l F jS, Y', mktime(0,0,0,$month,$day,$year)) . " to " . date('l F jS, Y', mktime(0,0,0,$implodedEndDataArray[1],$implodedEndDataArray[2],$implodedEndDataArray[0]));

			}
			else if ($report_type_to_run == 3) {

				// process for a given month
				$day = "01";
				$month = $_REQUEST["month_popup"];
				$month++;
				$duration = '1 MONTH';
				$year = $_REQUEST["year_field_001"];

				$title_range = "Month of " . date("F", mktime(0,0,0,$month, 1, $year));

			}
			else if ($report_type_to_run == 4) {
				$spansMenu = TRUE;
				$year = $_REQUEST["year_field_002"];
				$month = "01";
				$day = "01";
				$duration = '1 YEAR';

				$title_range = "Year of " . $year;

			}



			$tpl->assign('report_title_range', $title_range);
			$tpl->assign('report_day', $day);
			$tpl->assign('report_month', $month);
			$tpl->assign('report_year', $year);
			$tpl->assign('report_duration', $duration);


			$store_clause = "";
			if ($store !== "all")
				$store_clause = " and s.store_id = $store ";

			if (isset($_REQUEST['export']) && $_REQUEST['export'] == 'xlsx')
			{
				if ($report_type != RT_STORE_SUMMARY)
				{
					$current_detail_date = mktime(0, 0, 0, $month, $day, $year);
					$current_detail_date_sql = date("Y-m-d 00:00:00", $current_detail_date);
				}

				$rows = array();

				if ($report_type == RT_SESSION_SUMMARY)
				{

					$Sessions = DAO_CFactory::create('session');

					$Sessions->query("select  st.store_name, st.home_office_id, st.city, st.state_id, s.session_start from session s " .
					" join store st on s.store_id = st.id " .
					" where s.session_start >= '"  . $current_detail_date_sql . "'" .
					" and s.session_start <  DATE_ADD('" . $current_detail_date_sql . "', INTERVAL " . $duration . ") and s.session_type = 'SPECIAL_EVENT' $store_clause");

					while($Sessions->fetch())
					{

						$rows[] = array($Sessions->store_name, $Sessions->home_office_id, $Sessions->city, $Sessions->state_id, $Sessions->session_start);
					}

					$labels = array("Store Name", "Home Office ID", "City", "State", "MFY Session");

				}
				else if ($report_type == RT_STORE_SUMMARY)
				{

					$menus = CMenu::getActiveMenuArray();

					$firstMenu = array_shift($menus);
					$nextMenu = array_shift($menus);

					if (!isset($firstMenu['id']))
					{
						$firstMenu['id'] = false;
						$firstMenu['name'] = "Current";
					}


					if (!isset($nextMenu['id']))
					{
						$nextMenu['id'] = false;
						$nextMenu['name'] = "Next";
					}

					$Stores = DAO_CFactory::create('store');

					$Stores->query("select st.id, st.store_name, st.home_office_id, st.city, st.state_id, if(st.supports_special_events, 'YES', 'NO') as supports_events " .
									" from store st where st.is_deleted = 0");

					$Counter = 0;

					while ($Stores->fetch())
					{

						$rows[$Counter] = array($Stores->store_name, $Stores->home_office_id, $Stores->city, $Stores->state_id, $Stores->supports_events);

						$tax = $Stores->getCurrentSalesTaxObj();
						if ($tax && $tax->other1_tax)
							$rows[$Counter][] = $tax->other1_tax;
						else
							$rows[$Counter][] = 0;

						$Stores->clearMarkupMultiObj();

						$curMU = $Stores->getMarkUpMultiObj($firstMenu['id']);
						if ($curMU)
							$rows[$Counter][] = $curMU->assembly_fee;
						else
							$rows[$Counter][] = 25;

						$Stores->clearMarkupMultiObj();
						$nextMU = $Stores->getMarkUpMultiObj($nextMenu['id']);
						if ($nextMU)
							$rows[$Counter][] = $nextMU->assembly_fee;
						else
							$rows[$Counter][] = 25;

						$Stores->clearMarkupMultiObj();
						$Counter++;
					}

					$labels = array("Store Name", "Home Office ID", "City", "State", "Opt-in", "Service Tax", "Service Fee (" . $firstMenu['name'] . ")" , "Service Fee (" .$nextMenu['name'] . ")");


				}
				else if ($report_type == RT_GUEST_SUMMARY)
				{
					$Bookings = DAO_CFactory::create('booking');

					$Bookings->query("select st.store_name, st.home_office_id, s.session_type,  count(distinct s.id) as num_sessions, " .
						" count(distinct b.id) as num_attending, GROUP_CONCAT(distinct b.user_id) as boo from booking b " .
						" join session s on b.session_id = s.id " .
						" join store st on s.store_id = st.id " .
						" join user u on b.user_id = u.id " .
						" join orders o on o.id = b.order_id " .
						" where s.session_start >= '"  . $current_detail_date_sql . "' and s.session_start <  DATE_ADD('" . $current_detail_date_sql . "', INTERVAL " . $duration . ") " .
						" and b.status = 'ACTIVE' $store_clause and b.is_deleted = 0 and o.is_deleted = 0 " .
						" group by st.store_name, s.session_type " .
						" order by st.store_name, s.session_type");

					while($Bookings->fetch())
					{

						$rows[] = array($Bookings->store_name, $Bookings->home_office_id, $Bookings->session_type, $Bookings->num_sessions, $Bookings->num_attending);
					}

					$labels = array("Store Name", "Home Office ID", "Session Type", "Number of Sessions (w/ Guests*)", "Guests Attending");
					$rows[] = array("*Only sessions with one or more attendees are included in this total", "", "", "", "");



				}
				else if ($report_type == RT_GUEST_DETAIL)
				{

					$session_type_clause = "";

					if ($Form->value('session_type_filter') == 'STANDARD')
						$session_type_clause = "and s.session_type = 'STANDARD' ";
					else if ($Form->value('session_type_filter') == 'SPECIAL_EVENT')
						$session_type_clause = "and s.session_type = 'SPECIAL_EVENT' ";


					$Bookings = DAO_CFactory::create('booking');
					$Bookings->query("select  st.store_name, st.home_office_id,  s.session_type,CONCAT(u.firstname, ' ', u.lastname) as customer_name, u.primary_email as customer_email, " .
						" s.session_start, b.id,  o.grand_total, o.grand_total - o.subtotal_all_taxes as total_minus_tax  from booking b " .
						" join session s on b.session_id = s.id " .
						" join store st on s.store_id = st.id " .
						" join user u on b.user_id = u.id " .
						" join orders o on o.id = b.order_id " .
						" where s.session_start >= '"  . $current_detail_date_sql . "' and s.session_start <  DATE_ADD('" . $current_detail_date_sql . "', INTERVAL " . $duration . ") " .
						" and b.status = 'ACTIVE' and st.id = $store and b.is_deleted = 0 and o.is_deleted = 0 $session_type_clause" .
						" order by s.session_start, s.session_type");

					while($Bookings->fetch())
					{

						$rows[] = array($Bookings->store_name, $Bookings->home_office_id, $Bookings->session_type, $Bookings->customer_name, $Bookings->customer_email,
											 $Bookings->session_start, $Bookings->grand_total, $Bookings->total_minus_tax);
					}

					$labels = array("Store Name", "Home Office ID", "Session Type", "Customer Name", "Customer Email", "Session Time", "Total Spend (w/ Tax)", "Total Spend (w/out Tax)");
				}

				$tpl->assign('labels', $labels);
				$tpl->assign('rows', $rows);

				$rowCount = count($rows);

				switch ($report_type)
				{
					case RT_SESSION_SUMMARY:
						$reportName =  "Made For You Sessions Summary Report";
						break;
					case RT_STORE_SUMMARY:
						$reportName =  "Made For You Opt-in Summary Report by Store";
						break;
					case RT_GUEST_SUMMARY:
						$reportName =  "Made For You Guest Summary Report";
						break;
					case RT_GUEST_DETAIL:
						$reportName =  "Made For You Guest Detail Report";
						break;

				}

				CLog::RecordReport("$reportName (Excel Export)", "Rows:$rowCount ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run" );


				if ($rowCount == 0)
				{
					$tpl->assign('no_results', true);
					unset($_GET['export']);
				}

				$tpl->assign('rowcount', $rowCount);
			}

		}


		switch ($report_type)
		{
			case RT_SESSION_SUMMARY:
				$tpl->assign('page_title', "Made For You Sessions Summary Report");
				break;
			case RT_STORE_SUMMARY:
				$tpl->assign('page_title', "Made For You Opt-in Summary Report by Store");
				break;
			case RT_GUEST_SUMMARY:
				$tpl->assign('page_title', "Made For You Guest Summary Report");
				break;
			case RT_GUEST_DETAIL:
				$tpl->assign('page_title', "Made For You Guest Detail Report");
				break;

		}



		$formArray = $Form->render();
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('spans_menus', $spansMenu);
		$tpl->assign('report_type', $report_type_to_run);
		$tpl->assign('total_count', $total_count);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('store', $store);
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);
	}


}

?>