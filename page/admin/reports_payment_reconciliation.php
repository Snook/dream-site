<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once ('includes/DAO/BusinessObject/CSession.php');
require_once ('includes/CSessionReports.inc');
require_once( "phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');

 global $lastOrderConfirmNumber;
 global $currentRowHilight;
 global $lastColumn;

 $lastOrderConfirmNumber = false;
 $currentRowHilight = false;
 $lastColumn = 'N';

function paymentReportRowsCallback($sheet, $data, $row, $bottomRightExtent)
{
	global $lastOrderConfirmNumber;
	global $currentRowHilight;
	global $lastColumn;

	#EBF1FD

	if ($data['payment_total'] < 0)
	{
		$styleArray = array( 'font' => array( 'color' => array( 'argb' => 'FFFF0000')));

		$sheet->getStyle("A$row:$lastColumn$row")->applyFromArray($styleArray);

	}

	if ($lastOrderConfirmNumber <> $data['order_confirmation'])
	{
		$currentRowHilight = !$currentRowHilight;
	}


	if ($currentRowHilight)
	{
		$styleArray = array( 'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('argb' => 'FFEBF1FD')));
		$sheet->getStyle("A$row:$lastColumn$row")->applyFromArray($styleArray);
	}

	$lastOrderConfirmNumber = $data['order_confirmation'];

}


class page_admin_reports_payment_reconciliation extends CPageAdminOnly {

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

		$requestDatesFound = false;
		if (isset($_REQUEST["year"]) && isset($_REQUEST["month"])  && isset($_REQUEST["duration"]) && isset($_REQUEST["report_type"]))
		{
			$year = $_REQUEST["year"];
			$month = $_REQUEST["month"];
			$day = "01";
			$duration = $_REQUEST["duration"];
			$report_type_to_run = $_REQUEST["report_type"];
			$requestDatesFound = true;
		}

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
			CForm::default_value => $monthnum,
			CForm::name => 'month_popup'));

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

		$suppressLTDColumn = false;

		if ($isHomeOfficeAccess)
		{
			$tpl->assign('store_data', CStore::getStoreTreeAsNestedList(false, true));
			$lastColumn = 'P';
		}
		else
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select store_name, supports_ltd_roundup from store where id = $store_id");
			$storeObj->fetch();


			$suppressLTDColumn = !$storeObj->supports_ltd_roundup;
			$tpl->assign('store_name', $storeObj->store_name);

			if ($suppressLTDColumn)
				$lastColumn = 'N';
			else
				$lastColumn = 'O';
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

				$storeList = false;



				if ($isHomeOfficeAccess && !empty($_POST['requested_stores']))
				{

					if ($_POST['requested_stores'] != 'all')
					{

						$storeList = array();
						$tempStoreArr = explode(",", $_POST['requested_stores']);
						foreach($tempStoreArr as $thisStore)
						{
							if (!empty($thisStore) && is_numeric($thisStore))
							{
								$storeList[] = $thisStore;
							}
						}


					}
					$storeList = implode(",", $storeList);
				}
				else
				{
					$storeList = CBrowserSession::getCurrentFadminStore();
				}

			 	$findBySession = true;
				if (isset($_POST['select_key']) && $_POST['select_key'] == 'payment_date')
					$findBySession = false;


				$showPaymentTypes = "";
				$giftCertTypes = array();

				if (isset($_POST['pfa_All']))
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

				$header = array ('First Name', 'Last Name', 'Session Date', 'Order Confirm Number', 'Order Status', 'Order Type', 'Order Total');
				if (!$suppressLTDColumn)
				{
					array_push($header, "LTD Donation");
				}
				$header = array_merge($header, array('Payment Total', 'Balance Due', 'Payment Date', 'Delayed Payment Status', 'Payment Type', 'Card(last 4) Check No.', 'Transaction ID'));

				if ($isHomeOfficeAccess)
					$header = array_merge(array('Store'), $header);

				$tpl->assign('labels', $header);

				$columnDescs = array();

				if ($isHomeOfficeAccess)
				{
					$columnDescs['A'] = array('align' => 'left', 'width' => 'auto');
					$columnDescs['B'] = array('align' => 'left', 'width' => 'auto');
					$columnDescs['C'] = array('align' => 'left', 'width' => 'auto');
					$columnDescs['D'] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto');
					$columnDescs['E'] = array('align' => 'left', 'width' => 'auto', 'type' => 'URL');
					$columnDescs['F'] = array('align' => 'center');
					$columnDescs['G'] = array('align' => 'center');
					$columnDescs['H'] = array('align' => 'center', 'type' => 'currency');
					$columnDescs['I'] = array('align' => 'center', 'type' => 'currency');
					$columnDescs['J'] = array('align' => 'center', 'type' => 'currency');
					$columnDescs['K'] = array('align' => 'center', 'type' => 'currency');
					$columnDescs['L'] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto');
					$columnDescs['M'] = array('align' => 'center');
					$columnDescs['N'] = array('align' => 'left', 'width' => 'auto');
					$columnDescs['O'] = array('align' => 'center');
					$columnDescs['P'] = array('align' => 'left', 'width' => 'auto');
				}
				else
				{
					$columnDescs['A'] = array('align' => 'left', 'width' => 'auto');
					$columnDescs['B'] = array('align' => 'left', 'width' => 'auto');
					$columnDescs['C'] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto');
					$columnDescs['D'] = array('align' => 'left', 'width' => 'auto', 'type' => 'URL');
					$columnDescs['E'] = array('align' => 'center');
					$columnDescs['F'] = array('align' => 'center');
					$columnDescs['G'] = array('align' => 'center', 'type' => 'currency');
					$columnDescs['H'] = array('align' => 'center', 'type' => 'currency');
					$columnDescs['I'] = array('align' => 'center', 'type' => 'currency');
					if (!$suppressLTDColumn)
					{
						$columnDescs['J'] = array('align' => 'center', 'type' => 'currency');
						$columnDescs['K'] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto');
						$columnDescs['L'] = array('align' => 'center');
						$columnDescs['M'] = array('align' => 'left', 'width' => 'auto');
						$columnDescs['N'] = array('align' => 'center');
						$columnDescs['O'] = array('align' => 'left', 'width' => 'auto');
					}
					else
					{
						$columnDescs['J'] = array('align' => 'center', 'type' => 'datetime', 'width' => 'auto');
						$columnDescs['K'] = array('align' => 'center');
						$columnDescs['L'] = array('align' => 'left', 'width' => 'auto');
						$columnDescs['M'] = array('align' => 'center');
						$columnDescs['N'] = array('align' => 'left', 'width' => 'auto');
					}
				}

			$excessiveData = false;

			if ($findBySession)
				$rows = $this->findPaymentsBySession($storeList, $day, $month, $year,  $duration, $showPaymentTypes, $giftCertTypes, $isHomeOfficeAccess, $excessiveData, $suppressLTDColumn);
			else
				$rows = $this->findPaymentsByPaymentDate($storeList, $day, $month, $year,  $duration, $showPaymentTypes, $giftCertTypes, $isHomeOfficeAccess, $excessiveData, $suppressLTDColumn);


			$tpl->assign('rows', $rows);
			$numRows = count($rows);
			$tpl->assign('report_count', $numRows);
			$tpl->assign('col_descriptions', $columnDescs );

			$callbacks = array('row_callback' => 'paymentReportRowsCallback');


			$tpl->assign('excel_callbacks', $callbacks );


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



			CLog::RecordReport("Payment Reconciliation Report (Excel Export)", "Rows:$numRows ~ Store: $store" );


		}



		$formArray = $Form->render();

		if ($showPaymentType == 'STORE_CREDIT' || $showPaymentType == 'GIFT_CARD')  $tpl->assign('supress_delayed_payment_info', true);
		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','Payment Reconciliation Report');


		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', TRUE);
		}
	}

	function getHumanPaymentName($inEnum, $giftCertType)
	{

		if ($inEnum == 'STORE_CREDIT')
		{
			return "STORE CREDIT";
		}
		else if ($inEnum == 'CC')
		{
			return "CREDIT CARD";
		}
		else if ($inEnum == 'PAY_AT_SESSION')
		{
			return "PAY AT SESSION";
		}
		else if ($inEnum == 'CREDIT')
		{
			return "NO CHARGE";
		}
		else if ($inEnum == 'GIFT_CERT')
		{
			if ($giftCertType == CPayment::GC_TYPE_DONATED)
			{
				return "GC - DONATED";
			}
			else if ($giftCertType == CPayment::GC_TYPE_VOUCHER)
			{
				return "GC - VOUCHER";
			}
			else if ($giftCertType == CPayment::GC_TYPE_SCRIP)
			{
				return "GC - SCRIP";

			}
			else
			{
				return "GC - STANDARD";
			}
		}
		else
			return $inEnum;

	}

	function findPaymentsBySession ($storeList, $Day, $Month, $Year, $Interval = '1 DAY', $PAYMENT_TYPES=NULL, $giftCertTypes = "", $isHomeOfficeAccess = true, &$excessiveData = false, $suppressLTDColumn = false)
	{

		$filterIfNoBalanceDue = false;
		if (isset($_POST['only_show_orders_with_balance_due']))
			$filterIfNoBalanceDue = true;

		$showCancelledOrders = false;
		if (isset($_POST['also_show_cancelled_orders']))
		    $showCancelledOrders = true;

		$booking = DAO_CFactory::create("booking");

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$storeClause = "";

		$storeSelect = "";
		if ($isHomeOfficeAccess)
			$storeSelect = "st.store_name, ";

		if ($suppressLTDColumn)
		{

			$select = "select $storeSelect u.firstname, u.lastname, s.session_start, o.order_confirmation, b.status, o.order_type,  o.grand_total as order_total,
			p.total_amount as payment_total, od.balance_due,
			p.timestamp_created as payment_creation_date, '' as dp_status, p.payment_type,
			p.payment_number, p.payment_transaction_number,
			p.is_deposit, p.delayed_payment_status, p.delayed_payment_transaction_date, p.delayed_payment_transaction_number, p.is_delayed_payment, p.gift_cert_type, o.id as orders_id, sc.credit_type ";

		}
		else
		{
			$select = "select $storeSelect u.firstname, u.lastname, s.session_start, o.order_confirmation, b.status, o.order_type,  o.grand_total as order_total, o.ltd_round_up_value,
			p.total_amount as payment_total, od.balance_due,
			p.timestamp_created as payment_creation_date, '' as dp_status, p.payment_type,
			p.payment_number, p.payment_transaction_number,
			p.is_deposit, p.delayed_payment_status, p.delayed_payment_transaction_date, p.delayed_payment_transaction_number, p.is_delayed_payment, p.gift_cert_type, o.id as orders_id, sc.credit_type ";

		}


		$from = " from booking b ";

		$joins = "inner join orders o on b.order_id = o.id
		 inner join session s on b.session_id = s.id
		 join orders_digest od on od.order_id = o.id
		 inner join user u on b.user_id = u.id
		 inner join payment p on o.id = p.order_id
		 inner join store st on p.store_id = st.id
		 left Join store_credit sc on p.store_credit_id = sc.id ";

		if ($storeList)
			$storeClause = " and p.store_id in ($storeList) ";

		$where = "where p.is_deleted = 0 $storeClause and " ;



		if ($PAYMENT_TYPES != "all")
		{
			$where .= " p.payment_type in (" . $PAYMENT_TYPES . ") and ";

		}

	  	$where .= " s.session_start >= '"  . $current_date_sql . "' AND "  .
				" s.session_start <  DATE_ADD('" . $current_date_sql . "', INTERVAL " . $Interval . " ) AND "  .
		  		' s.is_deleted = 0 and s.session_publish_state != "SAVED" and b.status <> "RESCHEDULED" and b.status <> "SAVED" order by s.session_start, o.id, payment_creation_date, p.delayed_payment_status ';

		$booking->query($select . $from . $joins . $where);


		if ($booking->N > 2700)
		{
			$excessiveData = true;
		}

		$rows = array();
		$count = 0;


		while ($booking->fetch())
		{

			$tarray = $booking->toArray();

		  	if ($tarray['payment_type']  == 'REFUND' || $tarray['payment_type']  == 'REFUND_CASH' || $tarray['payment_type']  == 'REFUND_STORE_CREDIT' || $tarray['payment_type']  == 'REFUND_GIFT_CARD')
		  	{
		  		$tarray['payment_total'] *= -1;
		  	}



		  	if ($booking->is_deposit)
		  	{
		  		$tarray['dp_status'] = 'DEPOSIT';
		  	}
		  	else if ($booking->is_delayed_payment)
		  	{
		  		$tarray['dp_status'] = $booking->delayed_payment_status;
		  		$tarray['payment_transaction_number'] = $booking->delayed_payment_transaction_number;

	  			if ($tarray['dp_status'] == 'SUCCESS')
	  			{
	  				$tarray['payment_creation_date'] = $tarray['delayed_payment_transaction_date'];
	  			}
	  			else if ($tarray['dp_status'] == 'PENDING')
	  			{

	  				// figure out approx. processing date
	  				$sessionTS = strtotime($tarray['session_start']);
	  				$processTS = mktime(2,0,0,date("n", $sessionTS), date("j",$sessionTS) - 5, date("Y",$sessionTS));
					if ($processTS < time())
					{
						$processTS = mktime(2,0,0,date("n"), date("j") + 1, date("Y"));
					}

	  				$tarray['payment_creation_date'] = date("Y-m-d H:i:s", $processTS);
	  			}
	  			else if ($tarray['dp_status'] == 'FAIL')
	  			{
	  				$tarray['payment_total'] = 0;
	  			}
		  	}

			$tarray['payment_type'] = $this->getHumanPaymentName($tarray['payment_type'] , $tarray['gift_cert_type']);


			if (strpos($tarray['payment_number'], "XXXX") === 0)
			{
				$tarray['payment_number'] = substr($tarray['payment_number'], -4);
			}

			if (!$excessiveData)
				$tarray['session_start'] = PHPExcel_Shared_Date::stringToExcel($tarray['session_start']);

			$tarray['order_confirmation'] = "=HYPERLINK(\"" . HTTPS_BASE . "?page=admin_order_details&order=" . $booking->orders_id ."\", \"" . $tarray['order_confirmation'] . "\")";
			$tarray['firstname'] = addslashes($tarray['firstname']);// escape the last and first name
			$tarray['lastname']  = addslashes($tarray['lastname']);// escape the last and first name


			//$tarray['payment_creation_date'] = CTimezones::localizeAndFormatTimeStamp($tarray['payment_creation_date'], $booking->store_id);
			//TODO: get Excel to show a timezone

			if (!$excessiveData)
				$tarray['payment_creation_date'] =  $tarray['delayed_payment_transaction_date'] != "-" ? PHPExcel_Shared_Date::stringToExcel ($tarray['payment_creation_date']) : "-";


			$rows [$count++] = $tarray;
		}

		if ($suppressLTDColumn)
		{
			$colCount = 14;
			if ($isHomeOfficeAccess) $colCount = 15;
		}
		else
		{
			$colCount = 15;
			if ($isHomeOfficeAccess) $colCount = 16;
		}

		foreach($rows as $k => &$v)
		{
			$order_id = $v['orders_id'];

			if ($v['status'] == CBooking::CANCELLED && !$showCancelledOrders)
			{
				unset($rows[$k]);
				continue;

			}

			if ($filterIfNoBalanceDue)
			{

				if ($v['balance_due'] == 0)
				{
					unset($rows[$k]);
					continue;
				}
			}


			if (strpos($v['payment_type'] , "GC - ") === 0 && count($giftCertTypes) && !in_array($v['gift_cert_type'], $giftCertTypes))
			{
				unset($rows[$k]);
				continue;
			}

			$v = array_slice($v, 0, $colCount);

		}

		return ($rows);
	}


	function findPaymentsByPaymentDate ($storeList, $Day, $Month, $Year, $Interval = '1 DAY', $PAYMENT_TYPES=NULL, $giftCertTypes = "", $isHomeOfficeAccess = true, &$excessiveData = false, $suppressLTDColumn = false)
	{

		$filterIfNoBalanceDue = false;
		if (isset($_POST['only_show_orders_with_balance_due']))
			$filterIfNoBalanceDue = true;

		$showCancelledOrders = false;
		if (isset($_POST['also_show_cancelled_orders']))
		    $showCancelledOrders = true;

		$booking = DAO_CFactory::create("booking");

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$storeClause = "";

		$storeSelect = "";
		if ($isHomeOfficeAccess)
			$storeSelect = "st.store_name, ";

 		if ($suppressLTDColumn)
 		{
 			$select = "select $storeSelect u.firstname, u.lastname, s.session_start, o.order_confirmation, b.status, o.order_type,  o.grand_total as order_total,
 			p.total_amount as payment_total, od.balance_due,
 			p.timestamp_created as payment_creation_date, '' as dp_status, p.payment_type,
 			p.payment_number, p.payment_transaction_number,
 			p.is_deposit, p.delayed_payment_status, p.delayed_payment_transaction_date, p.delayed_payment_transaction_number, p.is_delayed_payment, p.gift_cert_type, o.id as orders_id, sc.credit_type ";
 		}
 		else
 		{
 			$select = "select $storeSelect u.firstname, u.lastname, s.session_start, o.order_confirmation, b.status, o.order_type,  o.grand_total as order_total, o.ltd_round_up_value,
 			p.total_amount as payment_total, od.balance_due,
 			p.timestamp_created as payment_creation_date, '' as dp_status, p.payment_type,
 			p.payment_number, p.payment_transaction_number,
 			p.is_deposit, p.delayed_payment_status, p.delayed_payment_transaction_date, p.delayed_payment_transaction_number, p.is_delayed_payment, p.gift_cert_type, o.id as orders_id, sc.credit_type ";
 		}


		$from = " from payment p ";

		$joins = " inner join orders o on p.order_id = o.id
		inner join booking b on o.id = b.order_id  and b.status <> 'RESCHEDULED' and b.status <> 'SAVED'
		join orders_digest od on od.order_id = o.id
		inner join session s on b.session_id = s.id
		inner join user u on p.user_id = u.id
		inner join store st on p.store_id = st.id
		left Join store_credit sc on p.store_credit_id = sc.id  ";

		if ($storeList)
			$storeClause = " and p.store_id in ($storeList) ";

		$where = " where p.is_deleted = 0 $storeClause and " ;

		if ($PAYMENT_TYPES != "all")
		{

			$types = explode(",", $PAYMENT_TYPES);

			if (in_array("'CC'", $types))
			{
				$where .= "  (
								(
									p.payment_type <> 'CC' and p.payment_type in (" . $PAYMENT_TYPES . ") and  p.timestamp_created >= '$current_date_sql' AND
									                   p.timestamp_created <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
								)
								OR
								(
									p.payment_type = 'CC' and p.timestamp_created >= '$current_date_sql' AND  p.timestamp_created <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
									and ((p.delayed_payment_status <> 'SUCCESS' and p.delayed_payment_status <> 'PENDING') or isnull( p.delayed_payment_status ))
								)
								OR
								(
										p.payment_type = 'CC' and p.delayed_payment_status = 'SUCCESS' and  p.delayed_payment_transaction_date >= '$current_date_sql' AND
													p.delayed_payment_transaction_date <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
								)
								OR
								(
										p.payment_type = 'CC' and p.delayed_payment_status = 'PENDING' and DATE_SUB(s.session_start,INTERVAL 5 DAY) >= '$current_date_sql' AND
													DATE_SUB(s.session_start,INTERVAL 5 DAY) <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
								)
							 )
				 			order by s.session_start, o.id, payment_creation_date, p.delayed_payment_status";
			}
			else
			{

				$where .= " ((p.payment_type in (" . $PAYMENT_TYPES . ")
								and  p.timestamp_created >= '$current_date_sql' AND  p.timestamp_created <  DATE_ADD('$current_date_sql', INTERVAL $Interval )))
								order by s.session_start, o.id, payment_creation_date, p.delayed_payment_status";

			}



		}
		else
		{
				$where .= "  (
								(
									p.timestamp_created >= '$current_date_sql' AND  p.timestamp_created <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
									and ((p.delayed_payment_status <> 'SUCCESS' and p.delayed_payment_status <> 'PENDING') or isnull( p.delayed_payment_status ))
								)
								OR
								(
										p.payment_type = 'CC' and p.delayed_payment_status = 'SUCCESS' and  p.delayed_payment_transaction_date >= '$current_date_sql' AND
													p.delayed_payment_transaction_date <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
								)
								OR
								(
										p.payment_type = 'CC' and p.delayed_payment_status = 'PENDING' and DATE_SUB(s.session_start,INTERVAL 5 DAY) >= '$current_date_sql' AND
													DATE_SUB(s.session_start,INTERVAL 5 DAY) <  DATE_ADD('$current_date_sql', INTERVAL $Interval )
								)
							 )
				 			order by s.session_start, o.id, payment_creation_date, p.delayed_payment_status";
		}


		$booking->query($select . $from . $joins . $where);


		if ($booking->N > 2700)
		{
			$excessiveData = true;
		}

		$rows = array();
		$count = 0;

		while ($booking->fetch())
		{

			$tarray = $booking->toArray();


			if ($tarray['payment_type']  == 'REFUND' || $tarray['payment_type']  == 'REFUND_CASH' || $tarray['payment_type']  == 'REFUND_STORE_CREDIT' || $tarray['payment_type']  == 'REFUND_GIFT_CARD')
			{
				$tarray['payment_total'] *= -1;
			}

			if ($booking->is_deposit)
			{
				$tarray['dp_status'] = 'DEPOSIT';
			}
			else if ($booking->is_delayed_payment)
			{
				$tarray['dp_status'] = $booking->delayed_payment_status;
				$tarray['payment_transaction_number'] = $booking->delayed_payment_transaction_number;

				if ($tarray['dp_status'] == 'SUCCESS')
				{
					$tarray['payment_creation_date'] = $tarray['delayed_payment_transaction_date'];
				}
				else if ($tarray['dp_status'] == 'PENDING')
				{

					// figure out approx. processing date
					$sessionTS = strtotime($tarray['session_start']);
					$processTS = mktime(2,0,0,date("n", $sessionTS), date("j",$sessionTS) - 5, date("Y",$sessionTS));
					if ($processTS < time())
					{
						$processTS = mktime(2,0,0,date("n"), date("j") + 1, date("Y"));
					}

					$tarray['payment_creation_date'] = date("Y-m-d H:i:s", $processTS);
				}
				else if ($tarray['dp_status'] == 'FAIL')
				{
					$tarray['payment_total'] = 0;
				}

			}

			$tarray['payment_type'] = $this->getHumanPaymentName($tarray['payment_type'] , $tarray['gift_cert_type']);

			if (strpos($tarray['payment_number'], "XXXX") === 0)
			{
				$tarray['payment_number'] = substr($tarray['payment_number'], -4);
			}


			$tarray['order_confirmation'] = "=HYPERLINK(\"" . HTTPS_BASE . "?page=admin_order_details&order=" . $booking->orders_id ."\", \"" . $tarray['order_confirmation'] . "\")";
			$tarray['firstname'] = addslashes($tarray['firstname']);// escape the last and first name
			$tarray['lastname']  = addslashes($tarray['lastname']);// escape the last and first name


			//$tarray['payment_creation_date'] = CTimezones::localizeAndFormatTimeStamp($tarray['payment_creation_date'], $booking->store_id);
			//TODO: get Excel to show a timezone


			if (!$excessiveData)
				$tarray['payment_creation_date'] =  $tarray['delayed_payment_transaction_date'] != "-" ? PHPExcel_Shared_Date::stringToExcel ($tarray['payment_creation_date']) : "-";


			$rows [$count++] = $tarray;
		}

 		if ($suppressLTDColumn)
 		{
			$colCount = 14;
			if ($isHomeOfficeAccess) $colCount = 15;
 		}
 		else
 		{
 			$colCount = 15;
 			if ($isHomeOfficeAccess) $colCount = 16;
 		}

		foreach($rows as $k => &$v)
		{
			$order_id = $v['orders_id'];

			if ($v['status'] == CBooking::CANCELLED && !$showCancelledOrders)
			{
				unset($rows[$k]);
				continue;
			}

			if ($filterIfNoBalanceDue)
			{

				if ($v['balance_due'] == 0)
				{
					unset($rows[$k]);
					continue;
				}
			}

		//	if ($v['is_delayed_payment'] and $v['delayed_payment_status'] == 'PENDING' and strtotime($v['session_start']) > time())
		//	{
		//		unset($rows[$k]);
		//		continue;
		//	}


			if (strpos($v['payment_type'] , "GC - ") === 0  && count($giftCertTypes) && !in_array($v['gift_cert_type'], $giftCertTypes))
			{
				unset($rows[$k]);
				continue;
			}


			if (!$excessiveData)
				$tarray['session_start'] = PHPExcel_Shared_Date::stringToExcel($tarray['session_start']);


			$v = array_slice($v, 0, $colCount);

		}

		// Now append non-food order revenue payments



		return ($rows);
	}

}?>