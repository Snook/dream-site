<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CLog.inc");
require_once("DAO/BusinessObject/CDreamTasteEvent.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CTimezones.php");
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');
require_once('DAO/BusinessObject/CStoreExpenses.php');
require_once('includes/CDreamReport.inc');

restore_error_handler();

function getSnapshotForStore($store_id, $day, $month, $year, $duration )
{
	$rows = array();
	$foundentry = false;
	CDreamReport::getOrderInfoByMonth($store_id, $day, $month, $year, $duration, $rows, 1);
	if (isset($rows['grand_total']) && $rows['grand_total'] > 0)
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

		if (empty($rows['subtotal_delivery_fee']))
		{
			$rows['subtotal_delivery_fee'] = 0;
		}

		$storeobj = DAO_CFactory::create("store");
		$storeobj->id = $store_id;
		$storeobj->selectAdd();
		$storeobj->selectAdd('store_name, id AS store_id, home_office_id, city, state_id, grand_opening_date, supports_ltd_roundup');
		$storeobj->find(true);

		$performance = CRoyaltyReport::findPerformanceExceptions($year . "-" . $month . "-" . $day, $duration, $store_id);
		$haspermanceoverride = false;
		if (isset($performance[$store_id]))
		{
			$haspermanceoverride = true;
		}

		$giftCertValues = CDreamReport::giftCertificatesByType($store_id, $day, $month, $year, $duration);
		$programdiscounts = CDreamReport::ProgramDiscounts($store_id, $day, $month, $year, $duration);


		$DoorDashRevenue = CRoyaltyReport::getDoorDashRevenueByTimeSpan($year . "-" . $month . "-" . $day, $duration, $store_id);
		$DoorDashFees = CRoyaltyReport::getDoorDashFeesByTimeSpan($year . "-" . $month . "-" . $day, $duration, $store_id);
		$ProductOrderMemebershipFeeRevenue = CDreamReport::getMembershipFeeRevenue($store_id, $day, $month, $year, $duration);

		$rows['grand_total']  += $ProductOrderMemebershipFeeRevenue;
		$rows['total_sales']  += $ProductOrderMemebershipFeeRevenue;
		$rows['grand_total']  += $DoorDashRevenue;
		$rows['total_sales']  += $DoorDashRevenue;

		$royaltyFee = 0;
		$marketingFee = 0;

		$instance = new CStoreExpenses();
		$expenseData = $instance->findExpenseDataByMonth($store_id, $day, $month, $year, $duration);
		CDreamReport::calculateFees($rows, $store_id, $haspermanceoverride, $expenseData, $giftCertValues, $programdiscounts, $rows['fundraising_total'], $rows['ltd_menu_item_value'],
			 $rows['subtotal_delivery_fee'], $rows['subtotal_bag_fee'], $DoorDashFees, $marketingFee, $royaltyFee, $storeobj->grand_opening_date, $month, $year);
	}

	if ($foundentry == false)
	{
		return null;
	}
	else
	{
		return $rows['total_less_discounts'];
	}
}

	try {

		$StoreCount = 0;

		if (defined("DISABLE_CRON") && DISABLE_CRON)
		{
			CLog::Record("CRON: take_revenue_snapshot called but cron is disabled");
			exit;
		}

		$months = array(date("Y-m-01"));
		$months[] = date("Y-m-01", strtotime(date("Y-m-01") . ' +1 month'));
		$months[] = date("Y-m-01", strtotime(date("Y-m-01") . ' +2 month'));

		if (date('j') < 7)
		{
			//capture last month as well
			array_unshift($months, date("Y-m-01", strtotime(date("Y-m-01") . ' -1 month')));
		}


		foreach($months as $thisMonth)
		{

			list($menuStartDate, $menuInterval) =  CMenu::getMenuStartandInterval(false, $thisMonth);

			$dateParts = explode("-", $menuStartDate);
			$day = $dateParts[2];
			$month = $dateParts[1];
			$year = $dateParts[0];
			$duration = "$menuInterval DAY";

			$dateParts = explode("-", $thisMonth);
			$calMonthDay = 1;
			$calMonthMonth = $dateParts[1];
			$calMonthYear = $dateParts[0];
			$calMonthDuration  = " 1 MONTH";

			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select id from store where active = 1");
			while($storeObj->fetch())
			{
				$curMenuAGR = getSnapshotForStore($storeObj->id, $day, $month, $year, $duration);
				$curMonthAGR = getSnapshotForStore($storeObj->id, $calMonthDay, $calMonthMonth, $calMonthYear, $calMonthDuration);

				if (is_null($curMonthAGR)) $curMonthAGR= 0;
				if (is_null($curMenuAGR)) $curMenuAGR= 0;

				$SnapShotObj = DAO_CFactory::create('dashboard_metrics_agr_snapshots');
				$SnapShotObj->date = date("Y-m-d");
				$SnapShotObj->store_id = $storeObj->id;
				$SnapShotObj->month = $thisMonth;
				$SnapShotObj->agr_cal_month = $curMonthAGR;
				$SnapShotObj->agr_menu_month = $curMenuAGR;
				$SnapShotObj->timestamp_created = "now()";
				$SnapShotObj->insert();

				$StoreCount++;
			}
		}

		CLog::Record("CRON: $StoreCount store revenue snapshots taken.");


	} catch (exception $e) {

		CLog::RecordException($e);
	}

?>