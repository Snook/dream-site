<?php // page_admin_create_store.php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CRoyaltyReport.inc');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once("includes/CDreamReport.inc");
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_site_royalty_national extends CPageAdminOnly
{
	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		CLog::RecordReport("Non Linked Report", $_SERVER['REQUEST_URI']);

		$store = null;
		$SessionReport = new CSessionReports();
		$RoyaltyReport = new CRoyaltyReport();
		$report_type_to_run = 3;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;
		$total_count = 0;
		$month_array = null;
		$report_submitted = false;

		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";

		set_time_limit(18000);

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"])
		{
			$report_type_to_run = $_REQUEST["pickDate"];
		}

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::value => 'Run Web Report'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_export',
			CForm::value => 'Export Report'
		));

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
		$monthnum = date("n");
		$monthnum--;
		$year = date("Y");

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

		$Form->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'requested_stores'
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::default_value => $monthnum,
			CForm::options => $month_array,
			CForm::name => 'month_popup'
		));

		$tpl->assign('store_data', CStore::getStoreTreeAsNestedList(false, true));

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

		$storeList = false;
		if (!empty($_POST['requested_stores']))
		{

			if ($_POST['requested_stores'] != 'all')
			{

				$storeList = array();
				$tempStoreArr = explode(",", $_POST['requested_stores']);
				foreach ($tempStoreArr as $thisStore)
				{
					if (!empty($thisStore) && is_numeric($thisStore))
					{
						$storeList[] = $thisStore;
					}
				}
				$storeList = implode(",", $storeList);
			}
		}

		if ($Form->value('report_export'))
		{
			if ($report_type_to_run == 1)
			{
				$implodedDateArray = explode("-", $day_start);
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
					$implodedDateArray = explode("-", $range_day_end);
				}
				else
				{
					$implodedDateArray = explode("-", $range_day_start);
				}

				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = $diff . ' DAY';
			}

			else if ($report_type_to_run == 3)
			{

				// process for a given month
				$day = "01";
				$month = $_REQUEST["month_popup"];
				$month++;
				$orgMonthNum = $month;
				$duration = '1 MONTH';
				$year = $_REQUEST["year_field_001"];

				if ($year == 2017 && $month == 6)
				{
					//transition month that requires a custom range
					$duration = "32 DAY";
				}
				else
				{
					$menuMonthStart = strtotime("2017-07-01");
					$curMonthTS = mktime(0, 0, 0, $month, 1, $year);
					if ($curMonthTS >= $menuMonthStart)
					{
						// new method using menu month
						$anchorDay = date("Y-m-01", mktime(0, 0, 0, $month, 1, $year));
						list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
						$start_date = strtotime($menu_start_date);
						$year = date("Y", $start_date);
						$month = date("n", $start_date);
						$day = date("j", $start_date);

						$duration = $interval . " DAY";
					}
				}
			}
			else if ($report_type_to_run == 4)
			{

				$day = "01";
				$month = "01";
				$duration = '1 YEAR';
				$year = $_REQUEST["year_field_002"];
			}

			$royaltyArr = null;
			$k = 0;

			// To get the menu month we need to form the start date and pull the anchor date (menu_start) from the DB
			$TestMenuStart = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
			$TestMenuObj = DAO_CFactory::create('menu');
			$TestMenuObj->query("select menu_start from menu where global_menu_start_date = '$TestMenuStart'");
			$TestMenuObj->fetch();
			$displayDate = explode("-", $TestMenuObj->menu_start);
			$displayMonth = $displayDate[1];
			$displayYear = $displayDate[0];

			$row = $RoyaltyReport->calculateAllStoresRoyalty($day, $month, $year, $duration, $storeList);
			$giftCertValues = CDreamReport::giftCertificatesByTypeGroupByStoreID($day, $month, $year, $duration, $storeList);
			$programDiscounts = CDreamReport::ProgramDiscountsGroupByID($day, $month, $year, $duration, $storeList);

			$instance = new CStoreExpenses();
			$expenseData = $instance->findExpenseDataByMonthByStore($day, $month, $year, $duration, $storeList);
			$homeofficeids = CRoyaltyReport::findHomeOfficeIDAlias();

			$performance = CRoyaltyReport::findPerformanceExceptions($year . "-" . $month . "-" . $day, $duration, null, $storeList);

			$needsSalesForceFee = false;

			if (($displayYear == 2018 && $displayMonth >= 9) || $displayYear > 2018)
			{
				$needsSalesForceFee = true;
			}

			if ($row != null && count($row) > 0)
			{
				$temprows = array();

				for ($i = 0; $i < count($row); $i++)
				{

					if (empty($row[$i]['fundraiser_total']))
					{
						$row[$i]['fundraiser_total'] = 0;
					}
					if (empty($row[$i]['ltd_round_up_value']))
					{
						$row[$i]['ltd_round_up_value'] = 0;
					}
					if (empty($row[$i]['subtotal_delivery_fee']))
					{
						$row[$i]['subtotal_delivery_fee'] = 0;
					}
					if (empty($row[$i]['delivery_tip']))
					{
						$row[$i]['delivery_tip'] = 0;
					}
					if (empty($row[$i]['subtotal_bag_fee']))
					{
						$row[$i]['subtotal_bag_fee'] = 0;
					}

					$marketingFee = 0;
					$royaltyFee = 0;
					$storeid = $row[$i]['id'];
					$storeexpenseData = isset($expenseData[$storeid]) ? $expenseData[$storeid] : null;
					$giftCertDataStore = isset($giftCertValues[$storeid]) ? $giftCertValues[$storeid] : null;
					$progDataStore = isset($programDiscounts[$storeid]) ? $programDiscounts[$storeid] : null;
					$DoorDashFees = CRoyaltyReport::getDoorDashFeesByTimeSpan($year . "-" . $month . "-" . $day, $duration, $storeid);

					$haspermanceoverride = false;
					if (isset($performance[$storeid]))
					{
						$haspermanceoverride = true;
					}

					CDreamReport::calculateFees($row[$i], $storeid, $haspermanceoverride, $storeexpenseData, $giftCertDataStore, $progDataStore, $row[$i]['fundraiser_total'], $row[$i]['ltd_menu_item_value'], $row[$i]['subtotal_delivery_fee'], $row[$i]['delivery_tip'],  $row[$i]['subtotal_bag_fee'], $DoorDashFees, $marketingFee, $royaltyFee, $row[$i]['grand_opening_date'], $month, $year);
					$temprows[$i]['home_office'] = $row[$i]['home_office_id'];
					$temprows[$i]['gp_id'] = $row[$i]['gp_account_id'];
					$temprows[$i]['grand_opening_date'] = $row[$i]['grand_opening_date'];

					$temprows[$i]['month'] = $displayMonth;
					$temprows[$i]['year'] = $displayYear;

					$temprows[$i]['store_name'] = $row[$i]['store_name'];
					$temprows[$i]['city'] = $row[$i]['city'];
					$temprows[$i]['state_id'] = $row[$i]['state_id'];
					$temprows[$i]['grand_total_less_taxes'] = $row[$i]['grand_total'] - $row[$i]['sales_tax'];

					$temprows[$i]['discounts'] = strval(-1 * intval($row[$i]['discounts']));

					if ($row[$i]['adjustments'] > 0)
					{
						$temprows[$i]['adjustments'] = $row[$i]['adjustments'];
					}
					else
					{
						$temprows[$i]['adjustments'] = $row[$i]['adjustments'];
					}

					$temprows[$i]['fundraising'] = $row[$i]['fundraiser_total'] * -1;
					$temprows[$i]['ltd_menu_item_value_discount'] = $row[$i]['ltd_menu_item_value'] * -1;
					$temprows[$i]['subtotal_delivery_fee_discount'] = $row[$i]['subtotal_delivery_fee'] * -1;
					$temprows[$i]['delivery_tip_discount'] = $row[$i]['delivery_tip'] * -1;
					$temprows[$i]['door_dash_fees'] = $DoorDashFees * -1;

					$temprows[$i]['total_less_discounts'] = $row[$i]['total_less_discounts'];

					$temprows[$i]['marketing_total'] = $marketingFee;
					$temprows[$i]['royalty_fee'] = $royaltyFee;

					$salesForceFee = 0;
					if ($needsSalesForceFee)
					{
						$salesForceFee = CRoyaltyReport::$SALESFORCE_MARKETING_FEE;
					}

					$temprows[$i]['total_fees'] = $royaltyFee + $marketingFee;

					$temprows[$i]['ltd_round_up_value'] = $row[$i]['ltd_round_up_value'];
					$temprows[$i]['ltd_menu_item_value'] = $row[$i]['ltd_menu_item_value'];
					$temprows[$i]['ltd_total'] = $row[$i]['ltd_round_up_value'] + $row[$i]['ltd_menu_item_value'];
					$temprows[$i]['subtotal_delivery_fee'] = $row[$i]['subtotal_delivery_fee'];
					$temprows[$i]['delivery_tip'] = $row[$i]['delivery_tip'];

					$temprows[$i]['salesforce_fee'] = $salesForceFee;
					$temprows[$i]['ltd_plus_royalties'] = $temprows[$i]['ltd_total'] + $royaltyFee + $row[$i]['subtotal_delivery_fee'];

					$temprows[$i]['marketing_plus_salesforce_fee'] = $marketingFee + $salesForceFee;

					$temprows[$i]['performance_standard'] = 0;

					if ($row[$i]['performance_standard'] == 0)
					{
						$temprows[$i]['performance_standard'] = 'Store Open <= 1 year: Adjusted Income $ X royalty fee %';
					}
					else if ($row[$i]['performance_standard'] == 1)
					{
						$temprows[$i]['performance_standard'] = '1 year: Adjusted Income $ X royalty fee %';
					}
					else if ($row[$i]['performance_standard'] == 2)
					{
						$temprows[$i]['performance_standard'] = 'Store Open > 1 year: Performance $ X royalty fee %';
					}
					else if ($row[$i]['performance_standard'] == 3)
					{
						$temprows[$i]['performance_standard'] = 'Performance Override was issued';
					}

					$temprows[$i]['is_incremental'] = 0;
					$temprows[$i]['batch'] = 0;

					if (isset($homeofficeids[$storeid]))
					{
						foreach ($homeofficeids[$storeid] as $element)
						{
							$temprows[$i]['archived_home_office_id'] = $element['recorded_home_office_id'];
							$temprows[$i]['archived_grand_opening_date'] = $element['recorded_grand_opening_date'];
						}
					}
				}
			}

			$labels = array(
				"Home Office ID",
				"GP Account ID",
				"Grand Opening Date",
				"Month",
				"Year",
				"Store Name",
				"Store City",
				"Store State",
				"Gross Income (Less Taxes)",
				"Discounts",
				"Adjustments",
				"Fundraising",
				"LTD Meal Donations",
				"Delivery Fees",
				"Delivery Tip",
				"Door Dash Fees",
				"Total Sales with Adjust/Disc",
				"National Marketing Fee",
				"Royalties Owed",
				"Total Fees Owed",
				"LTD Round-Up Donations",
				"LTD Meal Donations",
				"LTD Total Donations",
				"Delivery Fees",
				"Driver Tip",
				"Technology Fee",
				"Royalties + LTD Donations + Delivery Fee",
				"Marketing Fees + Technology Fee",
				"Performance Standard Note:",
				"isIncremental",
				"batch",
				"Archived Store Histories"
			);
			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $temprows);
			$tpl->assign('rowcount', count($temprows));

			$_GET['export'] = 'csv';
		}

		if ($Form->value('report_submit'))
		{
			$report_submitted = true;
			$sessionArray = null;
			$menu_array_object = null;
			$row = null;
			$royaltyArr = null;

			if ($report_type_to_run == 1)
			{
				$implodedDateArray = explode("-", $day_start);
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
					$implodedDateArray = explode("-", $range_day_end);
				}
				else
				{
					$implodedDateArray = explode("-", $range_day_start);
				}

				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = $diff . ' DAY';
			}

			else if ($report_type_to_run == 3)
			{
				// process for a given month
				$day = "01";
				$month = $_REQUEST["month_popup"];
				$month++;
				$orgMonthNum = $month;
				$duration = '1 MONTH';
				$year = $_REQUEST["year_field_001"];

				if ($year == 2017 && $month == 6)
				{
					//transition month that requires a custom range
					$duration = "32 DAY";
				}
				else
				{
					$menuMonthStart = strtotime("2017-07-01");
					$curMonthTS = mktime(0, 0, 0, $month, 1, $year);
					if ($curMonthTS >= $menuMonthStart)
					{
						// new method using menu month
						$anchorDay = date("Y-m-01", mktime(0, 0, 0, $month, 1, $year));
						list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
						$start_date = strtotime($menu_start_date);
						$year = date("Y", $start_date);
						$month = date("n", $start_date);
						$day = date("j", $start_date);

						$duration = $interval . " DAY";
					}
				}
			}
			else if ($report_type_to_run == 4)
			{

				$day = "01";
				$month = "01";
				$duration = '1 YEAR';
				$year = $_REQUEST["year_field_002"];
			}

			// this updates the web page
			$row = $RoyaltyReport->calculateAllStoresRoyalty($day, $month, $year, $duration, $storeList);
			// Note: totals include gross DoorDash Revenue

			$giftCertValues = CDreamReport::giftCertificatesByTypeGroupByStoreID($day, $month, $year, $duration, $storeList);
			$programdiscounts = CDreamReport::ProgramDiscountsGroupByID($day, $month, $year, $duration, $storeList);

			$instance = new CStoreExpenses();
			$expenseData = $instance->findExpenseDataByMonthByStore($day, $month, $year, $duration, $storeList);
			$homeofficeids = CRoyaltyReport::findHomeOfficeIDAlias();
			$performance = CRoyaltyReport::findPerformanceExceptions($year . "-" . $month . "-" . $day, $duration, null, $storeList);

			$needsSalesForceFee = false;

			if ($report_type_to_run == 3 && (($year == 2018 && $orgMonthNum >= 9) || $year > 2018))
			{
				$needsSalesForceFee = true;
			}

			if ($row != null && count($row) > 1)
			{
				for ($i = 0; $i < count($row); $i++)
				{
					if (empty($row[$i]['fundraiser_total']))
					{
						$row[$i]['fundraiser_total'] = 0;
					}
					if (empty($row[$i]['subtotal_delivery_fee']))
					{
						$row[$i]['subtotal_delivery_fee'] = 0;
					}

					$marketingFee = 0;
					$royaltyFee = 0;
					$storeid = $row[$i]['id'];

					$DoorDashFees = CRoyaltyReport::getDoorDashFeesByTimeSpan($year . "-" . $month . "-" . $day, $duration, $storeid);

					$storeexpenseData = isset($expenseData[$storeid]) ? $expenseData[$storeid] : null;
					$giftCertDataStore = isset($giftCertValues[$storeid]) ? $giftCertValues[$storeid] : null;
					$programDataStore = isset($programdiscounts[$storeid]) ? $programdiscounts[$storeid] : null;

					$haspermanceoverride = false;
					if (isset($performance[$storeid]))
					{
						$haspermanceoverride = true;
					}

					CDreamReport::calculateFees($row[$i], $storeid, $haspermanceoverride, $storeexpenseData, $giftCertDataStore, $programDataStore, $row[$i]['fundraiser_total'], $row[$i]['ltd_menu_item_value'], $row[$i]['subtotal_delivery_fee'], $row[$i]['delivery_tip'], $row[$i]['subtotal_bag_fee'], $DoorDashFees, $marketingFee, $royaltyFee, $row[$i]['grand_opening_date'], $month, $year);

					$row[$i]['marketing_total'] = $marketingFee;
					$row[$i]['royalty_fee'] = $royaltyFee;

					$salesForceFee = 0;
					if ($needsSalesForceFee)
					{
						$salesForceFee = CRoyaltyReport::$SALESFORCE_MARKETING_FEE;
					}
					$row[$i]['door_dash_fees'] = $DoorDashFees;

					$row[$i]['salesforce_fee'] = $salesForceFee;
					$row[$i]['total_fees'] = $royaltyFee + $marketingFee + $salesForceFee;

					//$row[$i]['grand_total_less_taxes'] = $row[$i]['grand_total'] - $row[$i]['sales_tax'];
					// LMH, 192009 fix for sales tax.
					$row[$i]['grand_total_less_taxes'] = $row[$i]['grand_total'] - $row[$i]['subtotal_all_taxes'];

					if (isset($homeofficeids[$storeid]))
					{
						foreach ($homeofficeids[$storeid] as $element)
						{

							$row[$i]['store_history'][] = array(
								$element['recorded_home_office_id'],
								$element['recorded_grand_opening_date']
							);
						}
					}
				}
			}
			$tpl->assign('report_type', $report_type_to_run);
			$tpl->assign('report_day', $day);
			$tpl->assign('report_month', $month);
			$tpl->assign('report_year', $year);
			$tpl->assign('report_duration', $duration);
			$tpl->assign('report_submitted', $report_submitted);

			$tpl->assign('royalty_data', $row);
			//$tpl->assign('report_data', $row);
		}

		$formArray = $Form->render();

		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'National Royalty Report');
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

}

?>