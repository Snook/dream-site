<?php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CSession.php');
 require_once ('includes/CSessionReports.inc');
 require_once( "phplib/PHPExcel/PHPExcel.php");
 require_once('ExcelExport.inc');

 class page_admin_reports_delivery_orders extends CPageAdminOnly {

	private $currentStore = null;

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

 	function runFranchiseLead()
 	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
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


 	function runSiteAdmin()
 	{

		$store = NULL;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$total_count = 0;
		$report_submitted = FALSE;

		if ( $this->currentStore ) { //fadmins
				$store = $this->currentStore;
			} else {
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

				$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
										CForm::onChangeSubmit => true,
										CForm::allowAllOption => true,
										CForm::showInactiveStores => true,
										CForm::name => 'store'));

				ini_set('memory_limit','-1');
				set_time_limit(3600 * 24);

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

				$month = $_REQUEST["month_popup"];
				$month++;
				$year = $_REQUEST["year_field_001"];


				if ($Form->value('menu_or_calendar') == 'menu')
				{
					// menu month
					$anchorDay = date("Y-m-01", mktime(0,0,0,$month,1, $year));
					list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
					$start_date = strtotime($menu_start_date);
					$year = date("Y", $start_date);
					$month = date("n", $start_date);
					$day = date("j", $start_date);

					$duration = $interval . " DAY";
				}
				else
				{

					// process for a given month
					$day = "01";
					$duration = '1 MONTH';
				}

			}
			else if ($report_type_to_run == 4) {
				$spansMenu = TRUE;
				$year = $_REQUEST["year_field_002"];
				$month = "01";
				$day = "01";
				$duration = '1 YEAR';

			}



	    	$rows = $this->findCustomers($store, $day, $month, $year,  $duration);
	    	$numRows = count($rows);

	    	CLog::RecordReport("Delivery Orders (Excel Export)", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration" );

	    	if ($numRows)
	    	{
				$columnDescs = array();

				if ($store == all)
				{
					$labels = array("Home Office ID", "Store Name", "User State", "User ID", "Account Last Name", "Account First Name", "Account Email",  "Order ID", "Delivery Fee","Order Total","Session Start", "Contact First Name", "Contact Last Name", "Contact Number",
									"Delivery Address Line 1", "Delivery Address Line 2", "Delivery City", "Delivery State", "Delivery Postal Code", "Delivery Notes");

					$columnDescs['A'] = array('align' => 'left', 'type' => 'text', 'width' => '8'); //HOID
					$columnDescs['B'] = array('align' => 'left', 'type' => 'text', 'width' => 'auto'); //store
					$columnDescs['C'] = array('align' => 'left', 'type' => 'text', 'width' => '15'); //user state
					$columnDescs['D'] = array('align' => 'left', 'type' => 'URL', 'width' => '20'); //id
					$columnDescs['E'] = array('align' => 'left', 'width' => 'auto'); //first
					$columnDescs['F'] = array('align' => 'left', 'width' => '15'); //last
					$columnDescs['G'] = array('align' => 'left', 'width' => 'auto'); //email
					$columnDescs['H'] = array('align' => 'center', 'type' => 'URL', 'width' => 'auto'); // order id and link
					$columnDescs['I'] = array('align' => 'center', 'type' => 'currency', 'width' => '15'); //delivery fee
					$columnDescs['J'] = array('align' => 'center', 'type' => 'currency', 'width' => '15'); //order amount
					$columnDescs['K'] = array('align' => 'left',  'type' => 'URL', 'width' => 'auto'); //session start
					$columnDescs['L'] = array('align' => 'left', 'width' => '15'); //contact first
					$columnDescs['M'] = array('align' => 'left', 'width' => 'auto'); //contact last
					$columnDescs['N'] = array('align' => 'left', 'width' => 'auto'); // contact number
					$columnDescs['O'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['P'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['Q'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['R'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['S'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['T'] = array('align' => 'wrap', 'width' => '100'); // Notes
				}
				else
				{
					$labels = array("User State", "User ID", "Account Last Name", "Account First Name", "Account Email",  "Order ID", "Delivery Fee","Order Total","Session Start", "Contact First Name", "Contact Last Name", "Contact Number",
									"Delivery Address Line 1", "Delivery Address Line 2", "Delivery City", "Delivery State", "Delivery Postal Code", "Delivery Notes");


					$columnDescs['A'] = array('align' => 'left', 'type' => 'text', 'width' => '15'); //user state
					$columnDescs['B'] = array('align' => 'left', 'type' => 'URL', 'width' => '20'); //id
					$columnDescs['C'] = array('align' => 'left', 'width' => 'auto'); //first
					$columnDescs['D'] = array('align' => 'left', 'width' => '15'); //last
					$columnDescs['E'] = array('align' => 'left', 'width' => 'auto'); //email
					$columnDescs['F'] = array('align' => 'center', 'type' => 'URL', 'width' => 'auto'); // order id and link
					$columnDescs['G'] = array('align' => 'center', 'type' => 'currency', 'width' => '15'); //delivery fee
					$columnDescs['H'] = array('align' => 'center', 'type' => 'currency', 'width' => '15'); //order amount
					$columnDescs['I'] = array('align' => 'left',  'type' => 'URL', 'width' => 'auto'); //session start
					$columnDescs['J'] = array('align' => 'left', 'width' => '15'); //contact first
					$columnDescs['K'] = array('align' => 'left', 'width' => 'auto'); //contact last
					$columnDescs['L'] = array('align' => 'left', 'width' => 'auto'); // contact number
					$columnDescs['M'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['N'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['O'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['P'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['Q'] = array('align' => 'left', 'width' => 'auto'); // contact address
					$columnDescs['R'] = array('align' => 'wrap', 'width' => '100'); // Notes
				}




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

		$formArray = $Form->render();


		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','Delivery Orders Report');
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);
	}



	function findCustomers ($store_id, $Day, $Month, $Year, $Interval)
	{
		$booking = DAO_CFactory::create("booking");


		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);



		$storeClause = "";
		$storeClause2 = "";
		if ($store_id != 'all')
		{
			$storeClause = "s.store_id = $store_id AND ";
			$colcount = 18;  // MAKE SURE YOU UPDATE THIS IF YOU ADD MORE SELECT STATEMENT COLS
		}
		else
		{
			$storeClause2 = "st.home_office_id, st.store_name,";
			$colcount = 20;  // MAKE SURE YOU UPDATE THIS IF YOU ADD MORE SELECT STATEMENT COLS
		}

		$querystr = "SELECT
       					$storeClause2
       					od.user_state,
						u.id,
						u.lastname,
						u.firstname,
						u.primary_email,
						o.id as order_id,
       					o.subtotal_delivery_fee,
       					o.grand_total,
						s.session_start,
						a.firstname AS to_first_name,
						a.lastname AS to_last_name,
						a.telephone_1 AS contact_number,
						a.address_line1,
						a.address_line2,
						a.city,
						a.state_id,
						a.postal_code,
						a.address_note,
       					s.id as session_id
					FROM
						booking b
						INNER JOIN session s ON b.session_id = s.id 
						AND s.session_type = 'SPECIAL_EVENT' 
						AND s.session_type_subtype = 'DELIVERY'
						INNER JOIN user u ON b.user_id = u.id
						INNER JOIN orders o ON b.order_id = o.id
						INNER JOIN orders_address a ON o.id = a.order_id 
						INNER JOIN orders_digest od on od.order_id = o.id                
						INNER JOIN store st on st.id = s.store_id                
						AND a.is_deleted = 0 
					WHERE
						$storeClause
					  	s.session_start >= '$current_date_sql' 
						AND s.session_start < DATE_ADD( '$current_date_sql', INTERVAL $Interval ) 
						AND b.STATUS = 'ACTIVE' 
						AND s.session_publish_state != 'SAVED' 
						AND b.is_deleted = 0 
					GROUP BY
						o.id";



		$booking->query($querystr);
		$rows = array();
		$count = 0;

		while ($booking->fetch()) {

		  $tarray = $booking->toArray();
		  $session_id = $tarray['session_id'];

		  array_splice($tarray, $colcount, count($tarray));
			$tarray['id'] = "=HYPERLINK(\"" . HTTPS_BASE . "main.php?page=admin_user_details&id=" . $tarray['id'] ."\", \"" . 'Link to User: ' . $tarray['id'] . "\")";
			$tarray['order_id'] = "=HYPERLINK(\"" . HTTPS_BASE . "main.php?page=admin_order_mgr&order=" . $tarray['order_id'] ."\", \"" . 'Link to Order: ' . $tarray['order_id'] . "\")";
			$tarray['session_start'] = "=HYPERLINK(\"" . HTTPS_BASE . "main.php?page=admin_main&session=" . $session_id."\", \"" .  CTemplate::dateTimeFormat($tarray['session_start']) . "\")";

		  $rows [$count++]=  $tarray;
		}


		return ($rows);

	}


}



?>