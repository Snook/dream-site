<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CRoyaltyReport.inc');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/CDreamReport.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');

class page_admin_reports_royalty extends CPageAdminOnly
{
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

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$store = null;
		$SessionReport = new CSessionReports();
		$RoyaltyReport = new CRoyaltyReport();
		$report_type_to_run = 3;
		$tpl = CApp::instance()->template();
		$tpl->assign('print_view', false);
		$Form = new CForm();
		$Form->Repost = false;
		$total_count = 0;
		$month_array = null;

		if ($this->currentStore) //fadmins
		{
			$store = $this->currentStore;
		}
		else //site admin
		{
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}

		$supports_LTD = false;
		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select id, supports_ltd_roundup, store_type from store where id = $store");
		$storeObj->fetch();
		if ($storeObj->supports_ltd_roundup)
		{
			$supports_LTD = true;
		}

		$tpl->assign('isDC', ($storeObj->isDistributionCenter()));

		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"])
		{
			$report_type_to_run = $_REQUEST["pickDate"];
		}

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::value => 'Run Report'
		));
		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit_print_all',
			CForm::value => 'Print All'
		));

		$month_array = array(
			'1' => 'January',
			'2' => 'February',
			'3' => 'March',
			'4' => 'April',
			'5' => 'May',
			'6' => 'June',
			'7' => 'July',
			'8' => 'August',
			'9' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);
		$monthnum = date("n");
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

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::default_value => $monthnum,
			CForm::options => $month_array,
			CForm::name => 'month_popup'
		));

		if (isset ($_REQUEST["single_date"]))
		{
			$day_start = $_REQUEST["single_date"];
			$tpl->assign('day_start_set', $day_start);
		}

		if (isset($_REQUEST["report_type"]) && isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"]) && isset ($_REQUEST["duration"]))
		{
			$day = $_REQUEST["day"];
			$month = $_REQUEST["month"];
			$year = $_REQUEST["year"];
			$duration = $_REQUEST["duration"];
			$report_type_to_run = $_REQUEST["report_type"];
			$master_royalty_array = array();
			$royaltyArr = null;
			$rows = null;

			if ($report_type_to_run == 4)
			{

				if ($supports_LTD)
				{
					$labels = array(
						"Gross Income",
						"Discounts",
						"Adjustments",
						"Fundraising Contributions",
						"LTD Meal Donations",
						"Total Sales after Discounts",
						"National Marketing Fees",
						"Royalties Owed",
						"Total Fees Owed",
						"LTD Round Up Donations",
						"LTD Meal Donations",
						"Total LTD Donations",
						"Summary Date"
					);
				}
				else
				{
					$labels = array(
						"Gross Income",
						"Discounts",
						"Adjustments",
						"Fundraising Contributions",
						"Total Sales after Discounts",
						"National Marketing Fees",
						"Royalties Owed",
						"Total Fees Owed",
						"Summary Date"
					);
				}

				for ($var = 1; $var <= count($month_array); $var++)
				{
					$arr = self::createRoyaltyArray($store, "01", $var, $year, $duration);

					if ($arr != null)
					{
						if ($supports_LTD)
						{
							$rows[$var] = array(
								$arr['grand_total'],
								$arr['discounts'],
								$arr['adjustments'],
								$arr['fundraising_total'],
								$arr['ltd_round_up_value'],
								$arr['total_less_discounts'],
								$arr['marketing_total'],
								$arr['royalty'],
								$arr['total_fees'],
								$arr['ltd_round_up_value'],
								$arr['ltd_menu_item_value'],
								$arr['ltd_round_up_value'] + $arr['ltd_menu_item_value'],
								$month_array[$var] . ' ' . $year
							);
						}
						else
						{
							$rows[$var] = array(
								$arr['grand_total'],
								$arr['discounts'],
								$arr['adjustments'],
								$arr['fundraising_total'],
								$arr['total_less_discounts'],
								$arr['marketing_total'],
								$arr['royalty'],
								$arr['total_fees'],
								$month_array[$var] . ' ' . $year
							);
						}
					}
				}
			}
			else
			{
				$arr = self::createRoyaltyArray($store, $day, $month, $year, $duration);

				if ($supports_LTD)
				{
					$rows[0] = array(
						$arr['grand_total'],
						$arr['discounts'],
						$arr['adjustments'],
						$arr['fundraising_total'],
						$arr['ltd_round_up_value'],
						$arr['total_less_discounts'],
						$arr['marketing_total'],
						$arr['royalty'],
						$arr['total_fees'],
						$arr['ltd_round_up_value'],
						$arr['ltd_menu_item_value'],
						$arr['ltd_round_up_value'] + $arr['ltd_menu_item_value'],
						$month_array[$month] . ' ' . $year
					);
					$labels = array(
						"Gross Income",
						"Discounts",
						"Adjustments",
						"Fundraising Contributions",
						"LTD Meal Donations",
						"Total Sales after Discounts",
						"National Marketing Fees",
						"Royalties Owed",
						"Total Fees Owed",
						"LTD Round Up Donations",
						"LTD Meal Donations",
						"Total LTD Donations",
						"Date"
					);
				}
				else
				{
					$rows[0] = array(
						$arr['grand_total'],
						$arr['discounts'],
						$arr['adjustments'],
						$arr['fundraising_total'],
						$arr['total_less_discounts'],
						$arr['marketing_total'],
						$arr['royalty'],
						$arr['total_fees'],
						$month_array[$month] . ' ' . $year
					);
					$labels = array(
						"Gross Income",
						"Discounts",
						"Adjustments",
						"Fundraising Contributions",
						"Total Sales after Discounts",
						"National Marketing Fees",
						"Royalties Owed",
						"Total Fees Owed",
						"Date"
					);
				}
			}

			$numRows = count($rows);
			CLog::RecordReport("Royalty (XLSX Export)", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run");

			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows);
			$tpl->assign('rowcount', $numRows);
		}

		if ($Form->value('report_submit') || (!empty($_REQUEST["print_all"]) && $_REQUEST["print_all"]) || (!empty($_REQUEST["print"]) && $_REQUEST["print"]))
		{
			$month = $_REQUEST["month_popup"];
			$year = $_REQUEST["year_field_001"];

			$sessionArray = null;
			$menu_array_object = null;
			$row = null;
			$royaltyArr = null;

			if ((!empty($_REQUEST["print_all"]) && $_REQUEST["print_all"]) || (!empty($_REQUEST["print"]) && $_REQUEST["print"]))
			{
				$tpl->assign('print_view', true);
			}

			if ($report_type_to_run == 3)
			{
				$day = "01";
				$duration = '1 MONTH';
				if (isset($_REQUEST["print_all"]))
				{
					$storeCount = 0;
					$activeStoreObj = DAO_CFactory::create("store");
					$activeStoreObj->query("SELECT id, store_type FROM `store` WHERE active = '1' AND is_deleted = '0' ORDER BY home_office_id + 0 ASC");

					while ($activeStoreObj->fetch())
					{
						if ($royaltyArray = self::createRoyaltyArray($activeStoreObj->id, $day, $month, $year, $duration))
						{
							$rows[$storeCount] = $royaltyArray;
							$rows[$storeCount]['Date'] = $month_array[$month] . ' ' . $year;

							$storeCount++;
						}
					}
				}
				else
				{
					$rows[0] = self::createRoyaltyArray($store, $day, $month, $year, $duration);
					$rows[0]['Date'] = $month_array[$month] . ' ' . $year;
				}
			}
			else if ($report_type_to_run == 4)
			{
				$spansMenu = true;
				$year = $_REQUEST["year_field_002"];
				$duration = '1 MONTH';

				for ($var = 1; $var <= count($month_array); $var++)
				{
					$arr = self::createRoyaltyArray($store, "01", $var, $year, $duration);
					$arr['Date'] = $month_array[$var] . ' ' . $year;
					if ($arr != null)
					{
						$rows[$var] = $arr;
					}
				}
			}

			$numRows = count($rows);
			CLog::RecordReport("Royalty", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ Type: $report_type_to_run");

			$tpl->assign('report_type', $report_type_to_run);
			$tpl->assign('report_day', $day);
			$tpl->assign('report_month', $month);
			$tpl->assign('store', $store);
			$tpl->assign('report_year', $year);
			$tpl->assign('report_duration', $duration);
			$tpl->assign('report_data', $rows);
		}

		$formArray = $Form->render();
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'Royalty Report');
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

	static function createRoyaltyArray($store_id, $day, $month, $year, $duration = false)
	{

		$orgMonthRequest = $month;
		$orgYearRequest = $year;

		$rows = array();
		$foundentry = false;
		$isTransitionMonth = false;
		$isMenuMonthBased = false;
		$needsSalesForceFee = false;

		$DAO_store = DAO_CFactory::create("store");
		$DAO_store->id = $store_id;
		$DAO_store->selectAdd();
		$DAO_store->selectAdd('store_name');
		$DAO_store->selectAdd('id AS store_id');
		$DAO_store->selectAdd('home_office_id');
		$DAO_store->selectAdd('store_type');
		$DAO_store->selectAdd('city');
		$DAO_store->selectAdd('state_id');
		$DAO_store->selectAdd('grand_opening_date');
		$DAO_store->selectAdd('supports_ltd_roundup');
		$DAO_store->find(true);

		$storeIsDC = $DAO_store->isDistributionCenter();

		if ((($year == 2018 && $month >= 9) || $year > 2018) && !$storeIsDC)
		{
			$needsSalesForceFee = true;
		}

		if ($year == 2017 && $month == 6)
		{
			//transition month that requires a custom range
			$duration = "32 DAY";
			$isTransitionMonth = true;
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
				$isMenuMonthBased = true;
			}
		}

		CDreamReport::getOrderInfoByMonth($store_id, $day, $month, $year, $duration, $rows, 1);
		$ProductOrderMemebershipFeeRevenue = CDreamReport::getMembershipFeeRevenue($store_id, $day, $month, $year, $duration);
		$DoorDashRevenue = CRoyaltyReport::getDoorDashRevenueByTimeSpan($year . "-" . $month . "-" . $day, $duration, $store_id);
		$DoorDashFees = CRoyaltyReport::getDoorDashFeesByTimeSpan($year . "-" . $month . "-" . $day, $duration, $store_id);

		$rows['membership_fees'] = CDreamReport::getMembershipFeeRevenue($store_id, $day, $month, $year, $duration);
		$rows['grand_total'] += (float)$ProductOrderMemebershipFeeRevenue;
		$rows['total_sales'] += (float)$ProductOrderMemebershipFeeRevenue;
		$rows['grand_total'] += (float)$DoorDashRevenue;
		$rows['total_sales'] += (float)$DoorDashRevenue;

		if ((isset($rows['grand_total']) && $rows['grand_total'] > 0) || $store_id == 57)
		{
			$foundentry = true;

			if (empty($rows['fundraising_total']))
			{
				$rows['fundraising_total'] = 0;
			}
			if (empty($rows['ltd_round_up_value']))
			{
				$rows['ltd_round_up_value'] = 0;
			}
			if (empty($rows['ltd_menu_item_value']))
			{
				$rows['ltd_menu_item_value'] = 0;
			}
			if (empty($rows['subtotal_delivery_fee']))
			{
				$rows['subtotal_delivery_fee'] = 0;
			}
			if (empty($rows['subtotal_bag_fee']))
			{
				$rows['subtotal_bag_fee'] = 0;
			}

			$performance = CRoyaltyReport::findPerformanceExceptions($year . "-" . $month . "-" . $day, $duration, $store_id);
			$haspermanceoverride = false;
			if (isset($performance[$store_id]))
			{
				$haspermanceoverride = true;
			}

			$giftCertValues = CDreamReport::giftCertificatesByType($store_id, $day, $month, $year, $duration);
			$programdiscounts = CDreamReport::ProgramDiscounts($store_id, $day, $month, $year, $duration);

			$royaltyFee = 0;
			$marketingFee = 0;

			// Finance Dept requested delivery fee be reported by calendar month so override here
			//$rows['subtotal_delivery_fee'] = CRoyaltyReport::getDeliveryFeeByCalendarMonth($orgMonthRequest, $orgYearRequest, $store);

			$instance = new CStoreExpenses();
			$expenseData = $instance->findExpenseDataByMonth($store_id, $day, $month, $year, $duration);
			CDreamReport::calculateFees($rows, $store_id, $haspermanceoverride, $expenseData, $giftCertValues, $programdiscounts, $rows['fundraising_total'], $rows['ltd_menu_item_value'], $rows['subtotal_delivery_fee'], $rows['delivery_tip'], $rows['subtotal_bag_fee'], $DoorDashFees, $marketingFee, $royaltyFee, $DAO_store->grand_opening_date, $month, $year);

			if ($storeIsDC)
			{
				$marketingFee = 0;
			}
			$rows['marketing_total'] = $marketingFee;
			$rows['royalty'] = $royaltyFee;

			$salesForceFee = 0;
			if ($needsSalesForceFee)
			{
				$salesForceFee = CRoyaltyReport::$SALESFORCE_MARKETING_FEE;
			}

			$rows['salesforce_fee'] = $salesForceFee;
			$rows['total_fees'] = $royaltyFee + $marketingFee + $salesForceFee;

			$rows['grand_total_less_taxes'] = $rows['grand_total'] - $rows['sales_tax'];
			$rows['store_name'] = $DAO_store->store_name;
			$rows['store_id'] = $DAO_store->store_id;
			$rows['home_office_id'] = $DAO_store->home_office_id;
			$rows['city'] = $DAO_store->city;
			$rows['state_id'] = $DAO_store->state_id;

			if (!$DAO_store->supports_ltd_roundup)
			{
				unset($rows['ltd_round_up_value']);
			}

			if ($haspermanceoverride == true)
			{
				$rows['used_performance_override'] = true;
			}

			if ($isTransitionMonth)
			{
				$rows['is_transition_month'] = true;
			}

			if ($isMenuMonthBased)
			{
				$rows['is_menu_based_month'] = true;
			}
		}

		if ($foundentry == false)
		{
			return null;
		}
		else
		{
			return $rows;
		}
	}
}