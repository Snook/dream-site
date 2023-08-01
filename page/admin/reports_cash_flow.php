<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/CSessionReports.inc');
require_once( "phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');


function myDateCompare($a, $b)
{
	$aTime = strtotime($a['date']);
	$bTime = strtotime($b['date']);

	if ($aTime == $bTime) {
		return 0;
	}
	return ($bTime > $aTime) ? -1 : 1;

}

class page_admin_reports_cash_flow extends CPageAdminOnly {

	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}
	
	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}
	
	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}
	
 	function runFranchiseOwner()
 	{
	 	$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

 	function runSiteAdmin()
 	{

 		ini_set('memory_limit','-1');
 		set_time_limit(3600);


		$store = NULL;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = TRUE;
		$total_count = 0;
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

		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$year = date("Y");
		$monthnum = date("n");
		$monthnum--;

		$defaultMonthValue = 0;
		$requestDatesFound = false;
		if (isset($_REQUEST["year"]) && isset($_REQUEST["month"])  && isset($_REQUEST["duration"]) && isset($_REQUEST["report_type"]))
		{
			$year = $_REQUEST["year"];
			$month = $_REQUEST["month"];
			$day = "01";
			$duration = $_REQUEST["duration"];
			$report_type_to_run = $_REQUEST["report_type"];
			$start_date = mktime(0, 0, 0, $month, $day, $year);
			$enddate = strtotime("+1 month", $start_date);
			$defaultMonthValue = $month-1;
			$requestDatesFound = true;
		}

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

		$Form->addElement(array(CForm::type => CForm::Hidden, CForm::name => 'requested_stores'));


		if (isset ($_REQUEST["single_date"]))
		{
			$day_start = $_REQUEST["single_date"];
			$tpl->assign('day_start_set', $day_start);
		}

		if (isset ($_REQUEST["range_day_start"]))
		{
			$range_day_start = $_REQUEST["range_day_start"];
			$tpl->assign('range_day_start_set', $range_day_start);
		}

		if (isset ($_REQUEST["range_day_end"]))
		{
			$range_day_end = $_REQUEST["range_day_end"];
			$tpl->assign('range_day_end_set', $range_day_end);
		}

		$isHomeOfficeAccess = !CUser::getCurrentUser()->isFranchiseAccess();

		global $lastColumn;


 		if ( $this->currentStore )
		{
			//fadmins
			$currentStore = $this->currentStore;
		}
		else
		{
			//site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
									CForm::onChangeSubmit => false,
									CForm::allowAllOption => false,
									CForm::showInactiveStores => false,
									CForm::name => 'store'));

			$currentStore = $Form->value('store');
		}

		$report_submit = isset($_REQUEST['submit_report'])? $_REQUEST['submit_report'] : NULL;
		$exportData = isset($_REQUEST['datainput'])? $_REQUEST['datainput'] : NULL;
		if ($exportData == "null" )
		{
			$exportData = NULL;
		}
		if ($report_submit != NULL)
		{
			$exportData = NULL;
		}
		$showPaymentType = isset($_REQUEST['payment_type']) ? $_REQUEST['payment_type'] : NULL;

		if ( $report_submit != NULL || $exportData != NULL  || $requestDatesFound == true)
		{
			$report_submitted = TRUE;
			$sessionArray = null;
			$menu_array_object = NULL;

				if ($report_type_to_run == 1)
				{
					$implodedDateArray = explode("-",$day_start) ;
					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$duration = '1 DAY';
				}
				else if ($report_type_to_run == 2)
				{
					// process for an entire year
					$rangeReversed = false;
					$implodedDateArray = null;
					$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
					$diff++;  // always add one for SQL to work correctly
					if ($rangeReversed == true)
					{
						$implodedDateArray = explode("-",$range_day_end);
					}
					else
					{
					 	$implodedDateArray = explode("-",$range_day_start) ;
					}

					$day = $implodedDateArray[2];
					$month = $implodedDateArray[1];
					$year = $implodedDateArray[0];
					$duration = $diff . ' DAY';
				}
				else if ($report_type_to_run == 3)
				{
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
				else if ($report_type_to_run == 4)
				{
					$spansMenu = TRUE;
					$year = $_REQUEST["year_field_002"];
					$month = "01";
					$day = "01";
					$duration = '1 YEAR';
				}


			 	$findBySession = true;
				if (isset($_POST['select_key']) && $_POST['select_key'] == 'payment_date')
					$findBySession = false;


				$showPaymentTypes = "";
				$giftCertTypes = array();

				if (isset($_POST['pfa_ALL']))
					$showPaymentTypes = "all";
				else
				{

					$tarr = array();
					$tCertsArr = array();
					$hasGiftCerts = false;
					foreach ($_POST as $k => $v)
					{
						if (strpos($k, "pf_") === 0)
						{
							$thisType = substr($k, 3);


							if (strpos($thisType, "GIFT_CERT_") === 0)
							{
								if (!$hasGiftCerts)
								{
									$tarr[] =  "'GIFT_CERT'";
								}

								$hasGiftCerts = true;
								$giftCertTypes[] = substr($thisType, 10);


							}
							else
							{
								$tarr[] =  "'" . $thisType . "'";
							}

						}
					}


					$showPaymentTypes = implode(",", $tarr);

				}


				$start_date = mktime(0, 0, 0, $month, $day, $year);
				$enddate = strtotime("+" . $duration, $start_date);

				if ($isHomeOfficeAccess)
					$header = array ('Store', 'Date Payment Received', 'Collected Revenue', 'Scheduled Payments', 'Due at Session', 'Total Revenue', 'Failed CC Payments Due');
				else
					$header = array ('Date Payment Received', 'Collected Revenue', 'Scheduled Payments', 'Due at Session', 'Total Revenue', 'Failed CC Payments Due');


				$tpl->assign('labels', $header);

				$columnDescs = array();

			if ($isHomeOfficeAccess)
			{
				$columnDescs['A'] = array('align' => 'center', 'width' => 30);
				$columnDescs['B'] = array('align' => 'center');
				$columnDescs['C'] = array('align' => 'center', 'type' => 'currency');
				$columnDescs['D'] = array('align' => 'center', 'type' => 'currency');
				$columnDescs['E'] = array('align' => 'center', 'type' => 'currency');
				$columnDescs['G'] = array('align' => 'center', 'type' => 'currency');
			}
			else
			{
				$columnDescs['A'] = array('align' => 'center');
				$columnDescs['B'] = array('align' => 'center', 'type' => 'currency');
				$columnDescs['C'] = array('align' => 'center', 'type' => 'currency');
				$columnDescs['D'] = array('align' => 'center', 'type' => 'currency');
				$columnDescs['F'] = array('align' => 'center', 'type' => 'currency');
			}

			$excessiveData = false;

			$rows = $this->findDailyCashFlow($currentStore, $day, $month, $year,  $duration, $showPaymentTypes, $giftCertTypes, $isHomeOfficeAccess, $excessiveData);

			$this->joinBalanceDueAmounts($rows, $currentStore, $day, $month, $year,  $duration, $showPaymentTypes, $giftCertTypes, $isHomeOfficeAccess, $excessiveData);

			$tpl->assign('rows', $rows);
			$numRows = count($rows);
			$tpl->assign('report_count', $numRows);
			$tpl->assign('col_descriptions', $columnDescs );


			//$tpl->assign('excel_callbacks', $callbacks );


			if ($numRows)
			{
				if ($excessiveData)
					$_GET['export'] = 'csv';
				else
					$_GET['export'] = 'xlsx';
			}
			else
			{
				$tpl->setErrorMsg('There are no results for this query.');
			}

			CLog::RecordReport("CashFlow Report (Excel Export)", "Rows:$numRows ~ Store: $store" );
		}

		$formArray = $Form->render();

		if ($showPaymentType == 'STORE_CREDIT' || $showPaymentType == 'GIFT_CARD')  $tpl->assign('supress_delayed_payment_info', true);
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form', $formArray);

		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', TRUE);
		}
	}


	function joinBalanceDueAmounts(&$rows, $storeList, $Day, $Month, $Year, $Interval = '1 DAY', $PAYMENT_TYPES=NULL, $giftCertTypes = "", $isHomeOfficeAccess = true, &$excessiveData = false)
	{

		$booking = DAO_CFactory::create("booking");

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$storeClause = "";

		$storeSelect = "";
		if ($isHomeOfficeAccess)
			$storeSelect = "iq.store_name, ";

		if ($storeList)
			$storeClause = " and p.store_id in ($storeList) ";



		$query = "select $storeSelect DATE(iq.session_start) as session_start, sum(iq.balance_due) as balance_due_total from
				(select st.store_name, s.session_start, o.id, od.balance_due, GROUP_CONCAT(p.payment_type), GROUP_CONCAT(p.total_amount) from booking b
				join orders o on o.id = b.order_id
				join orders_digest od on od.order_id = o.id
				join session s on s.id = b.session_id
				join store st on st.id = o.store_id
				join payment p on p.order_id = o.id and p.is_deleted = 0
						and p.payment_type <> 'PAY_AT_SESSION'
						and (p.is_delayed_payment <> 1 or (p.is_delayed_payment = 1 and p.delayed_payment_status <> 'PENDING' and p.delayed_payment_status <> 'SUCCESS'))
						and p.is_deposit <> 1
				where b.`status` = 'ACTIVE' and  s.session_start >= '$current_date_sql'
					AND s.session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval) $storeClause and
					od.balance_due > 0 and b.is_deleted = 0
				group by o.id ) as iq
				group by DATE(iq.session_start)";

		$booking->query($query);


		while ($booking->fetch())
		{

			if (array_key_exists($booking->session_start, $rows))
			{
				$rows[$booking->session_start]['balance_due_revenue'] += $booking->balance_due_total;
			}
			else
			{
				if ($isHomeOfficeAccess)
					$rows[$booking->session_start] = array('store' => $booking->store_name, 'date' => $booking->session_start, 'firm_revenue' => 0, 'dp_revenue' => 0, 'balance_due_revenue' => $booking->balance_due_total, 'total_revenue' => 0, 'failed_dp_revenue' => 0);
				else
					$rows[$booking->session_start] = array('date' => $booking->session_start, 'firm_revenue' => 0, 'dp_revenue' => 0, 'balance_due_revenue' => $booking->balance_due_total, 'total_revenue' => 0, 'failed_dp_revenue' => 0);
			}

			$rows[$booking->session_start]['total_revenue'] =  $rows[$booking->session_start]['firm_revenue']  + $rows[$booking->session_start]['dp_revenue']  +
																			+ $rows[$booking->session_start]['balance_due_revenue'];

		}

		uasort($rows, 'myDateCompare');
	}

	function findDailyCashFlow($storeList, $Day, $Month, $Year, $Interval = '1 DAY', $PAYMENT_TYPES=NULL, $giftCertTypes = "", $isHomeOfficeAccess = true, &$excessiveData = false)
	{

		$booking = DAO_CFactory::create("booking");

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$storeClause = "";

		$storeSelect = "";
		if ($isHomeOfficeAccess)
			$storeSelect = "st.store_name, ";

		if ($storeList)
			$storeClause = " and p.store_id in ($storeList) ";



		$query = "select p.id, $storeSelect DATE(s.session_start) as session_date, p.store_id, p.total_amount as payment_total, DATE(p.timestamp_created) as payment_creation_date, p.payment_type,
		p.payment_number, p.is_deposit, p.delayed_payment_status, DATE(p.delayed_payment_transaction_date) as dp_date,  p.is_delayed_payment, od.balance_due, b.status,
		o.id as orders_id, sc.credit_type
		from payment p
		inner join orders o on p.order_id = o.id
		inner join orders_digest od on od.order_id = o.id
		inner join booking b on o.id = b.order_id and b.status <> 'RESCHEDULED' and b.status <> 'SAVED'
		inner join session s on b.session_id = s.id
		inner join store st on p.store_id = st.id
		left Join store_credit sc on p.store_credit_id = sc.id
		where
			(p.is_deleted = 0 $storeClause and p.timestamp_created >= '$current_date_sql' AND  p.timestamp_created <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
			and p.is_delayed_payment = 0 and p.payment_type not in ('PAY_AT_SESSION', 'CREDIT')
		)
		OR
			(p.is_deleted = 0 $storeClause and p.is_delayed_payment = 1 and p.delayed_payment_status = 'PENDING'
			and DATE_SUB(s.session_start, INTERVAL 5 DAY) >=  DATE('$current_date_sql') AND  DATE_SUB(s.session_start, INTERVAL 5 DAY)  <  DATE_ADD('$current_date_sql', INTERVAL $Interval)
			and DATE_SUB(s.session_start, INTERVAL 5 DAY) >= DATE(now()))
		OR
			(p.is_deleted = 0 $storeClause and p.is_delayed_payment = 1 and p.delayed_payment_status = 'SUCCESS'  and p.delayed_payment_transaction_date >= '$current_date_sql'
									AND  p.delayed_payment_transaction_date <  DATE_ADD('$current_date_sql', INTERVAL $Interval )  )
		OR
			(p.is_deleted = 0 $storeClause and p.is_delayed_payment = 1 and p.delayed_payment_status = 'FAIL'  and p.delayed_payment_transaction_date >= '$current_date_sql'
									AND  p.delayed_payment_transaction_date <  DATE_ADD('$current_date_sql', INTERVAL $Interval )  )
		OR
			(p.is_deleted = 0 $storeClause and p.payment_type = 'PAY_AT_SESSION'  and s.session_start >= '$current_date_sql'
									AND s.session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval )  )
		group by p.id
		order by payment_creation_date";

		$booking->query($query);


		$rows = array();

		while ($booking->fetch())
		{

			if ($booking->payment_type  == 'REFUND' || $booking->payment_type == 'REFUND_CASH' || $booking->payment_type  == 'REFUND_STORE_CREDIT' || $booking->payment_type  == 'REFUND_GIFT_CARD')
			{
				$booking->payment_total *= -1;
			}


			if ($booking->status == CBooking::ACTIVE)
			{

				$date = $booking->payment_creation_date;

				if ($booking->payment_type  == 'PAY_AT_SESSION')
				{
					$booking->payment_total = $booking->balance_due;
					$date = $booking->session_date;
				}
				else if ($booking->is_delayed_payment && $booking->delayed_payment_status == 'PENDING')
				{
					$date = $booking->session_date;
					$sessionTS = strtotime($date);
					$date = date("Y-m-d", $sessionTS - (86400 * 4) );
				}
				else if ($booking->is_delayed_payment && ($booking->delayed_payment_status == 'SUCCESS' || $booking->delayed_payment_status == 'FAIL'))
				{
					$date = $booking->dp_date;
				}


				if (!isset($rows[$date]))
				{
					if ($isHomeOfficeAccess)
						$rows[$date] = array('store' => $booking->store_name, 'date' => $date, 'firm_revenue' => 0, 'dp_revenue' => 0, 'balance_due_revenue' => 0, 'total_revenue' => 0, 'failed_dp_revenue' => 0);
					else
						$rows[$date] = array('date' => $date, 'firm_revenue' => 0, 'dp_revenue' => 0, 'balance_due_revenue' => 0, 'total_revenue' => 0, 'failed_dp_revenue' => 0);


				}

				if ($booking->is_delayed_payment && $booking->delayed_payment_status == 'PENDING')
					$rows[$date]['dp_revenue'] += $booking->payment_total;
				else if ($booking->is_delayed_payment && $booking->delayed_payment_status == 'FAIL')
					$rows[$date]['failed_dp_revenue'] += $booking->payment_total;
				else if ($booking->payment_type  == 'PAY_AT_SESSION')
					$rows[$date]['balance_due_revenue'] += $booking->payment_total;
				else
				{
					$rows[$date]['firm_revenue'] += $booking->payment_total;
				}
			}
			else
			{
				// TODO: how to handle payments associated with cancelled orders
				if ($booking->is_deposit)
				{
					$rows[$date]['firm_revenue'] += $booking->payment_total;
				}
			}

		}



		uasort($rows, 'myDateCompare');


		foreach($rows as $date => &$data)
		{
			$data['total_revenue'] = $data['firm_revenue']  + $data['dp_revenue']  + $data['balance_due_revenue'];
		}



		if (count($rows) > 3000)
		{
			$excessiveData = true;
		}


		return ($rows);
	}

}?>