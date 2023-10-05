<?php // page_admin_create_store.php

/**
 * @author Carl Samuelson
 */

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/CSessionReports.inc');
require_once('includes/CDashboardReportWeekBased.inc');
require_once('phplib/PHPExcel/PHPExcel.php');

require_once("DAO/BusinessObject/CImport.php");

require_once('ExcelExport.inc');

define('TIMESTAMP_UTC_TIME', 0);
define('TIMESTAMP_UTC_DATE', 1);
define('TIMESTAMP_LOCAL_TIME', 2);
define('TIMESTAMP_LOCAL_DATE', 3);
define('PAYOUT_TIME', 4);
define('PAYOUT_DATE', 5);
define('STORE_ID', 6);
define('BUSINESS_ID', 7);
define('STORE_NAME', 8);
define('MERCHANT_STORE_ID', 9);
define('TRANSACTION_TYPE', 10);
define('TRANSACTION_ID', 11);
define('DOORDASH_ORDER_ID', 12);
define('MERCHANT_DELIVERY_ID', 13);
define('EXTERNAL_ID', 14);
define('ROW_DESCRIPTION', 15);
define('FINAL_ORDER_STATUS', 16);
define('CURRENCY', 17);
define('SUBTOTAL', 18);
define('TAX_SUBTOTAL', 19);
define('COMMISSION', 20);
define('COMMISSION_TAX_AMOUNT', 21);
define('MARKETING_FEES', 22);
define('CREDIT', 23);
define('DEBIT', 24);
define('DOORDASH_TRANSACTION_ID', 25);
define('PAYOUT_ID', 26);
define('DRIVE_CHARGE', 27);
define('TAX_REMITTED_BY_DOORDASH_TO_STATE', 28);
define('SUBTOTAL_FOR_TAX', 29);
define('DOORDASH_FUNDED_SUBTOTAL_DISCOUNT_AMOUNT', 30);
define('MERCHANT_FUNDED_SUBTOTAL_DISCOUNT_AMOUNT', 31);

class page_admin_reports_door_dash_import extends CPageAdminOnly
{
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

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->run();
	}

	public function runSiteAdmin()
	{
		$this->run();
	}

	public function runHomeOfficeManager()
	{
		$this->run();
	}

	function safeDivide($dividend, $divisor)
	{
		if (empty($divisor))
		{
			return 0;
		}

		return $dividend / $divisor;
	}

	static $ExcelColumnToDBColumnMap = array(
		"TIMESTAMP_UTC_TIME" => "timestamp_UTC_time",
		"TIMESTAMP_UTC_DATE" => "timestamp_UTC_date",
		"TIMESTAMP_LOCAL_TIME" => "timestamp_local_time",
		"TIMESTAMP_LOCAL_DATE" => "timestamp_local_date",
		"PAYOUT_TIME" => "payout_time",
		"PAYOUT_DATE" => "payout_date",
		"STORE_ID" => "dd_store_id",
		"BUSINESS_ID" => "business_id",
		"STORE_NAME" => "store_name",
		"MERCHANT_STORE_ID" => "merchant_store_id",
		"TRANSACTION_TYPE" => "transaction_type",
		"TRANSACTION_ID" => "transaction_id",
		"DOORDASH_ORDER_ID" => "doordash_order_id",
		"MERCHANT_DELIVERY_ID" => "merchant_delivery_id",
		"EXTERNAL_ID" => "external_id",
		"DESCRIPTION" => "description",
		"FINAL_ORDER_STATUS" => "final_order_status",
		"CURRENCY" => "currency",
		"SUBTOTAL " => "subtotal",
		"TAX_SUBTOTAL" => "tax_subtotal",
		"COMMISSION" => "commission",
		"COMMISSION_TAX_AMOUNT" => "commission_tax_amount",
		"MARKETING_FEES" => "marketing_fees",
		"CREDIT" => "credit",
		"DEBIT" => "debit",
		"DOORDASH_TRANSACTION_ID" => "doordash_transaction_id",
		"PAYOUT_ID" => "payout_id",
		"DRIVE_CHARGE" => "drive_charge",
		"TAX_REMITTED_BY_DOORDASH_TO_STATE" => "tax_remitted_by_doordash_to_state",
		"SUBTOTAL_FOR_TAX" => "subtotal_for_tax",
		"DOORDASH_FUNDED_SUBTOTAL_DISCOUNT_AMOUNT" => "doordash_funded_subtotal_discount_amount",
		"MERCHANT_FUNDED_SUBTOTAL_DISCOUNT_AMOUNT" => "merchant_funded_subtotal_discount_amount"
	);

	private function getDoorDashToDDStoreIDArray()
	{
		$retVal = array();
		$storeObj = new DAO();
		$storeObj->query("select id, door_dash_id from store where door_dash_id <> '' and not isnull(door_dash_id) and is_deleted  = 0");
		while ($storeObj->fetch())
		{
			$retVal[$storeObj->door_dash_id] = $storeObj->id;
		}

		return $retVal;
	}

	private function run()
	{
		ini_set('memory_limit', '-1');
		set_time_limit(3600 * 24);

		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		$month_array = array(
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December'
		);

		$year = date("Y");
		$monthnum = date("n");
		$monthnum--;
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_001",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_field_002",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$firstYear = 2019;  // do data before then
		$thisYear = $firstYear;
		$weekYearOptions = array();
		while ($thisYear <= $year)
		{
			$weekYearOptions[$thisYear] = $thisYear;
			$thisYear++;
		}
		$weekYearOptions['2020'] = '2020';

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "year_week",
			CForm::onChangeSubmit => true,
			CForm::options => $weekYearOptions
		));

		$weekYear = $Form->value('year_week');

		$weeks = array();
		$weekDate = new DateTime("$weekYear-01-01 00:00:00");
		$dayNum = $weekDate->format('w');
		while ($dayNum != 1)
		{
			$weekDate->modify('-1 days');
			$dayNum = $weekDate->format('w');
		}

		$nextYear = $weekYear + 1;
		while (strtotime($weekDate->format('Y-m-d 00:00:00')) < strtotime("$nextYear-01-01 00:00:00"))
		{
			$curWeek = $weekDate->format('W');
			$curYear = $weekDate->format('o');
			$weeks[strtotime($weekDate->format('Y-m-d'))] = "Week " . $curWeek . " (" . $weekDate->format('Y-m-d') . ")";
			$weekDate->modify('+7 days');
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "week",
			CForm::options => $weeks
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::default_value => $monthnum,
			CForm::name => 'month_popup'
		));

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

		if ($this->currentStore)
		{ //fadmins
			$store = $this->currentStore;
			$tpl->assign('storeView', true);
		}
		else
		{
			$tpl->assign('storeView', false);

			$Form->DefaultValues['store'] = 'all';

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => true,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}

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

		if (isset($_POST['submit_door_dash_input']) && $_FILES['door_dash_input_file']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['door_dash_input_file']['tmp_name']))
		{
			$error = false;

			$rows = array();
			$isCSV = false;
			if (true)
			{
				// CSV
				$isCSV = true;
				$row = 1;
				if (($handle = fopen($_FILES['door_dash_input_file']['tmp_name'], "r")) !== false)
				{
					while (($data = fgetcsv($handle, 1000, ",")) !== false)
					{
						$numCols = count($data);
						$rows[$row] = array();

						for ($col = 0; $col < $numCols; $col++)
						{
							$rows[$row][$col] = $data[$col];
						}

						$row++;
					}
					fclose($handle);
				}
				else
				{
					CLog::Record('Door Dash import failed: fopen failed');
					$tpl->setErrorMsg('Door Dash import failed: fopen failed');

					return false;
				}
			}
			else
			{
				if (!$fp = file_get_contents($_FILES['door_dash_input_file']['tmp_name']))
				{
					CLog::Record('Door Dash Report Input failed: fopen failed');
					$tpl->setErrorMsg('Door Dash Input failed: fopen failed');
					throw new Exception("Could not open Input file");
				}

				// Excel
				$inputFileName = $_FILES['door_dash_input_file']['tmp_name'];
				$objReader = PHPExcel_IOFactory::createReader('Excel2007');
				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load($inputFileName);
				$objWorksheet = $objPHPExcel->getActiveSheet();

				$highestRow = $objWorksheet->getHighestRow();
				$highestColumn = $objWorksheet->getHighestColumn();
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

				// distill the object into an array
				for ($row = 1; $row <= $highestRow; ++$row)
				{
					$rows[$row] = array();

					// load excel obj into an array
					for ($col = 0; $col <= $highestColumnIndex; ++$col)
					{
						$rows[$row][$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
					}
				}
			}

			$updatedStores = array();

			$positionToColumnMap = array();

			$storeIDMap = $this->getDoorDashToDDStoreIDArray();

			$excel_labels = array_shift($rows);

			foreach ($rows as $thisRow)
			{
				if (!empty($thisRow[STORE_ID]))
				{
					$insertObj = DAO_CFactory::create('door_dash_orders_and_payouts');
					$isNew = true;
					$clonedObj = null;
					if ($thisRow[TRANSACTION_TYPE] == 'PAYOUT')
					{
						$insertObj->transaction_type = 'PAYOUT';
						$insertObj->transaction_id = $thisRow[TRANSACTION_ID];
						if ($insertObj->find(true))
						{
							$isNew = false;
							$clonedObj = clone($insertObj);
						}
					}
					else
					{
						$insertObj->transaction_type = $thisRow[TRANSACTION_TYPE];
						$insertObj->doordash_order_id = $thisRow[DOORDASH_ORDER_ID];
						if ($insertObj->find(true))
						{
							$isNew = false;
							$clonedObj = clone($insertObj);
						}
					}

					$insertObj->store_id = (isset($storeIDMap[$thisRow[STORE_ID]]) ? $storeIDMap[$thisRow[STORE_ID]] : false);

					if (!in_array($insertObj->store_id, $updatedStores))
					{
						$updatedStores[] = $insertObj->store_id;
					}

					if ($isCSV)
					{
						$insertObj->timestamp_UTC_time = date("H:i:s", strtotime($thisRow[TIMESTAMP_UTC_TIME]));
						$insertObj->timestamp_UTC_date = date("Y-m-d", strtotime($thisRow[TIMESTAMP_UTC_DATE]));
						$insertObj->timestamp_local_time = date("H:i:s", strtotime($thisRow[TIMESTAMP_LOCAL_TIME]));
						$insertObj->timestamp_local_date = date("Y-m-d", strtotime($thisRow[TIMESTAMP_LOCAL_DATE]));
						$insertObj->payout_time = date("H:i:s", strtotime($thisRow[PAYOUT_TIME]));
						$insertObj->payout_date = date("Y-m-d", strtotime($thisRow[PAYOUT_DATE]));
					}
					else
					{
						$insertObj->timestamp_UTC_time = date("H:i:s", PHPExcel_Shared_Date::ExcelToPHP($thisRow[TIMESTAMP_UTC_TIME], true));
						$insertObj->timestamp_UTC_date = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($thisRow[TIMESTAMP_UTC_DATE] + $thisRow[TIMESTAMP_UTC_TIME]));
						$insertObj->timestamp_local_time = date("H:i:s", PHPExcel_Shared_Date::ExcelToPHP($thisRow[TIMESTAMP_LOCAL_TIME]));
						$insertObj->timestamp_local_date = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($thisRow[TIMESTAMP_LOCAL_DATE] + $thisRow[TIMESTAMP_LOCAL_TIME]));
						$insertObj->payout_time = date("H:i:s", PHPExcel_Shared_Date::ExcelToPHP($thisRow[PAYOUT_TIME] - $thisRow[PAYOUT_DATE], true));
						$insertObj->payout_date = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($thisRow[PAYOUT_TIME]));
					}
					$insertObj->door_dash_store_id = $thisRow[STORE_ID];
					$insertObj->business_id = (int)$thisRow[BUSINESS_ID];
					$insertObj->store_name = $thisRow[STORE_NAME];
					$insertObj->merchant_store_id = $thisRow[MERCHANT_STORE_ID];
					$insertObj->transaction_type = $thisRow[TRANSACTION_TYPE];
					$insertObj->transaction_id = $thisRow[TRANSACTION_ID];
					$insertObj->doordash_order_id = $thisRow[DOORDASH_ORDER_ID];
					$insertObj->merchant_delivery_id = $thisRow[MERCHANT_DELIVERY_ID];
					$insertObj->external_id = $thisRow[EXTERNAL_ID];
					$insertObj->final_order_status = $thisRow[FINAL_ORDER_STATUS];
					$insertObj->description = $thisRow[ROW_DESCRIPTION];
					$insertObj->currency = $thisRow[CURRENCY];
					$insertObj->subtotal = $thisRow[SUBTOTAL];
					$insertObj->tax_subtotal = $thisRow[TAX_SUBTOTAL];
					$insertObj->commission = $thisRow[COMMISSION];
					$insertObj->commission_tax_amount = $thisRow[COMMISSION_TAX_AMOUNT];
					$insertObj->marketing_fees = $thisRow[MARKETING_FEES];
					$insertObj->credit = $thisRow[CREDIT];
					$insertObj->debit = $thisRow[DEBIT];
					$insertObj->doordash_transaction_id = (int)$thisRow[DOORDASH_TRANSACTION_ID];
					$insertObj->payout_id = (int)$thisRow[PAYOUT_ID];
					$insertObj->drive_charge = $thisRow[DRIVE_CHARGE];
					$insertObj->tax_remitted_by_doordash_to_state = $thisRow[TAX_REMITTED_BY_DOORDASH_TO_STATE];
					$insertObj->subtotal_for_tax = $thisRow[SUBTOTAL_FOR_TAX];
					$insertObj->doordash_funded_subtotal_discount_amount = $thisRow[DOORDASH_FUNDED_SUBTOTAL_DISCOUNT_AMOUNT];
					$insertObj->merchant_funded_subtotal_discount_amount = $thisRow[MERCHANT_FUNDED_SUBTOTAL_DISCOUNT_AMOUNT];

					if ($isNew)
					{
						$insertObj->insert();
					}
					else
					{
						$insertObj->update($clonedObj);
					}
				}
			}

			$time = date("Y-m-d H:i:s");
			foreach ($updatedStores as $thisStoreID)
			{
				$StoreObj = new DAO();
				$StoreObj->query("update store set timestamp_last_activity = '$time' where id = $thisStoreID");
			}

			$tpl->setStatusMsg("File successfully imported");
		}

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::value => 'Run Report'
		));

		if (isset($_REQUEST["pickDate"]))
		{
			$report_type_to_run = $_REQUEST["pickDate"];
		}

		if (isset($_POST['report_submit']) && $_POST['report_submit'] == 'Run Report')
		{

			if ($report_type_to_run == 1)
			{
				// single date
				$range_day_start = $day_start;
				$range_day_end = date("Y-m-d", strtotime($day_start) + 86400);
			}
			else if ($report_type_to_run == 2)
			{
				// this is the summary report for the time range
				if (strtotime($range_day_start) > strtotime($range_day_end))
				{
					$temp = $range_day_start;
					$range_day_start = $range_day_end;
					$range_day_end = $temp;
				}
			}
			else if ($report_type_to_run == 3)
			{
				// process for a given month

				if ($Form->value('menu_or_calendar') == 'menu')
				{

					$month = $_REQUEST["month_popup"];
					$month++;
					$year = $_REQUEST["year_field_001"];

					$dateStr = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));

					$theMenu = DAO_CFactory::create('menu');
					$theMenu->query("select id, menu_start, global_menu_start_date, DATEDIFF(global_menu_end_date, global_menu_start_date) + 1 as day_interval from menu where menu_start = '$dateStr'");
					$theMenu->fetch();

					$range_day_start = $theMenu->global_menu_start_date;
					$range_day_end = date("Y-m-d", strtotime($range_day_start) + $theMenu->day_interval * 86400);

					// get menu anchor month
					$anchorDateArr = explode("-", $theMenu->menu_start);
					$isMenuMonth = $theMenu->id;
					$menuMonthMonthYear = ltrim($anchorDateArr[1], "0") . " " . $anchorDateArr[0];
				}
				else
				{
					$day = "01";
					$month = $_REQUEST["month_popup"];
					$month++;
					$year = $_REQUEST["year_field_001"];

					$range_day_start = "$year-$month-$day";
					$weekDate = new DateTime($range_day_start);
					$weekDate->modify("+ 1 month");
					$weekDate->modify("- 1 day");
					$range_day_end = $weekDate->format("Y-m-d");
				}
			}
			else if ($report_type_to_run == 4)
			{
				$year = $_REQUEST["year_field_002"];
				$month = "01";
				$day = "01";

				$range_day_start = "$year-$month-$day";
				$date = new DateTime($range_day_start);
				$date->modify("+ 1 year");
				$range_day_end = $date->format("Y-m-d");
			}
			else if ($report_type_to_run == 5)
			{
				$range_day_start = date("Y-m-d", $Form->value('week'));
				$weekDate = new DateTime($range_day_start);
				$weekDate->modify("+ 1 week");
				$range_day_end = $weekDate->format("Y-m-d");
			}

			$title = makeTitle("Door Dash Transactions by Date Range", null, $range_day_start . " to " . $range_day_end);

			// header
			$titleRows = array();
			$titleRows[] = array("Dream Dinners - Door Dash Transactions by Date Range");
			$titleRows[] = array("For Period: " . $range_day_start . " to " . $range_day_end);

			$revenue_only = isset($_POST['revenue_only']);

			list($rows, $labels, $colDesc) = $this->retrieveRange($range_day_start, $range_day_end, $revenue_only, $store);

			if (empty($rows))
			{
				$tpl->setErrorMsg('No data for time period and store.');
				$tpl->assign('no_data', true);
				$tpl->assign('pickdate', $report_type_to_run);
			}
			else
			{
				$tpl->assign('file_name', $title);

				// spit out excel sheet
				$tpl->assign('rows', $rows);

				$tpl->assign('title_rows', $titleRows);
				$tpl->assign('col_descriptions', $colDesc);
				$tpl->assign('labels', $labels);
				$_GET['export'] = 'xlsx';
			}
		}

		$tpl->assign('form_session_list', $Form->render());

		header_remove('Set-Cookie');
	}

	private function retrieveRange($range_day_start, $range_day_end, $revenue_only, $store)
	{
		$rows = array();

		$range_day_startTS = strtotime($range_day_start);
		$range_day_endTS = strtotime($range_day_end);

		if ($range_day_endTS < $range_day_startTS)
		{
			$temp = $range_day_startTS;
			$range_day_startTS = $range_day_endTS;
			$range_day_endTS = $temp;
		}

		$range_day_start = date("Y-m-d", $range_day_startTS);
		$range_day_end = date("Y-m-d", $range_day_endTS);

		$rows = array();

		$revenue_only_clause = "";
		if ($revenue_only)
		{
			$revenue_only_clause = " and final_order_status in ('Delivered','Picked Up') ";
		}

		$storeClause = "";

		if ($store != 'all' && is_numeric($store))
		{
			$storeClause = " and d.store_id = $store ";
		}

		$DoorDashEntries = new DAO();
		$DoorDashEntries->query("SELECT st.store_name, st.city, st.state_id, d.timestamp_local_date, d.transaction_type, d.transaction_id,
       									d.doordash_order_id, d.subtotal, d.subtotal_for_tax, tax_subtotal, commission, d.subtotal- d.commission as agr, final_order_status FROM door_dash_orders_and_payouts d
									join store st on st.id = d.store_id
									 where d.is_deleted = 0 AND d.timestamp_local_date >= '$range_day_start' and d.timestamp_local_date <= '$range_day_end' $revenue_only_clause $storeClause");

		while ($DoorDashEntries->fetch())
		{
			$rows[] = DAO::getCompressedArrayFromDAO($DoorDashEntries, true, true);
		}

		$labels = array(
			"Store Name",
			"City",
			"State",
			"Date",
			"Transaction Type",
			"Transaction ID",
			"Door Dash Order ID",
			"Subtotal",
			"Taxable Subtotal",
			"Tax amount",
			"Commission",
			"Ajusted Gross Revenue (for Royalties)",
			"Final Order Status"
		);

		$columnDescs = array();
		$columnDescs["A"] = array(
			'align' => 'left',
			'width' => 'auto'
		);
		$columnDescs["B"] = array(
			'align' => 'left',
			'width' => 'auto'
		);
		$columnDescs["C"] = array(
			'align' => 'left',
			'width' => 'auto'
		);
		$columnDescs["D"] = array(
			'align' => 'left',
			'width' => 'auto'
		);
		$columnDescs["E"] = array(
			'align' => 'left',
			'width' => 'auto'
		);
		$columnDescs["F"] = array(
			'align' => 'left',
			'width' => 'auto'
		);
		$columnDescs["G"] = array(
			'align' => 'left',
			'width' => 'auto'
		);
		$columnDescs["H"] = array(
			'align' => 'right',
			'width' => 'auto',
			'type' => 'currency'
		);
		$columnDescs["I"] = array(
			'align' => 'right',
			'width' => 'auto',
			'type' => 'currency'
		);
		$columnDescs["J"] = array(
			'align' => 'right',
			'width' => 'auto',
			'type' => 'currency'
		);
		$columnDescs["K"] = array(
			'align' => 'right',
			'width' => 'auto',
			'type' => 'currency'
		);
		$columnDescs["L"] = array(
			'align' => 'right',
			'width' => '13',
			'type' => 'currency'
		);
		$columnDescs["M"] = array(
			'align' => 'left',
			'width' => 'auto'
		);

		return array(
			$rows,
			$labels,
			$columnDescs
		);
	}

}

?>