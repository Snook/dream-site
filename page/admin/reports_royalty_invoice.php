<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/CRoyaltyReport.inc");
require_once("includes/CSessionReports.inc");
require_once("includes/DAO/BusinessObject/CStore.php");
require_once('page/admin/reports_royalty.php');

class page_admin_reports_royalty_invoice extends CPageAdminOnly
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
		CLog::RecordReport("Royalty Invoice", $_SERVER['REQUEST_URI']);

		$store = null;
		$grandopeningdate = null;
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
		$tpl = CApp::instance()->template();

		$store = $_REQUEST["store"];

		if (isset($store))
		{
			$storeobj = DAO_CFactory::create('store');
			$storeobj->id = $store;
			$storeobj->find(true);
			$grandopeningdate = $storeobj->grand_opening_date;
			$sales_tax = $storeobj->getCurrentSalesTax();
		}

		if (isset($_REQUEST["year"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["order_total"]))
		{
			$order_total = $_REQUEST["order_total"];

			$rows = page_admin_reports_royalty::createRoyaltyArray($store, 1, $_REQUEST["month"], $_REQUEST["year"], "1 Month");

			/*
			$rndMarketingTotal = CRoyaltyReport::calculateMarketingFee($order_total);
			$standard = 0;

			$performance = CRoyaltyReport::findPerformanceExceptions($_REQUEST["year"] . "-" . $_REQUEST["month"] . "-" . 1 , "1 Month", null);
			$haspermanceoverride = false;
			if (isset($performance[$store]))
			{
					$haspermanceoverride = true;
			}

			$reporttimedate = mktime(0,0,0,$_REQUEST["month"], 01, $_REQUEST["year"]);
			$rndRoyalty = CRoyaltyReport::calculateRoyaltyFee($rows, $store, $haspermanceoverride, $order_total, $reporttimedate, $standard, $grandopeningdate);
			$rows['performance_standard'] = $standard;
			$rows['year'] = $_REQUEST["year"];
			$rows['month'] = ($month_array[$_REQUEST["month"]-1]);
			$rows['orders_total'] = $order_total;
			$rows['marketing_total'] = $rndMarketingTotal;
			$rows['royalty'] = $rndRoyalty;
			$rows['total_fees'] = $rndRoyalty + $rndMarketingTotal;

			*/

			$rows['year'] = $_REQUEST["year"];
			$rows['month'] = $month_array[$_REQUEST["month"] - 1];

			$rows['orders_total'] = $order_total;
			$rows['net_sales'] = $order_total - ($rows['royalty'] + $rows['marketing_total'] + $rows['salesforce_fee']);

			$tpl->assign('rows', $rows);

			if (isset($store))
			{
				$tpl->assign('has_sales_tax', $sales_tax);
				$tpl->assign('store_name', $storeobj->store_name);
				$tpl->assign('address', $storeobj->address_line1);
				$tpl->assign('home_office_id', $storeobj->home_office_id);
				$tpl->assign('city', $storeobj->city);
				$tpl->assign('state', $storeobj->state_id);
				$tpl->assign('postal', $storeobj->postal_code);
			}
		}
	}

}