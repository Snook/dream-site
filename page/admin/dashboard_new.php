<?php // main.php

require_once("includes/CPageAdminOnly.inc");
require_once("includes/CDashboardReport.inc");

class page_admin_dashboard_new extends CPageAdminOnly
{

	private $currentStore = null;

	function exportOrderList($month, $year, $store, $tpl, $titleString)
	{

		//$tpl->assign('title_rows', array(array($titleString), array("")));

		$chars = array(
			"/",
			"?",
			"<",
			">",
			"\\",
			":",
			"*",
			"|",
			"\"",
			"^"
		);
		$ents = array(
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_"
		);

		$titleString = str_replace($chars, $ents, $titleString);

		$_GET['csvfilename'] = $titleString;

		$digestObj = DAO_CFactory::create('orders_digest');

		$rows = array();

		$digestObj->query("select od.user_id, od.order_id, u.firstname, u.lastname, u.primary_email, u.telephone_1, u.telephone_1_call_time,  u.telephone_2, od.user_state,  od.order_type, o.order_type as Web_or_Direct, od.session_type, od.original_order_time, od.session_time,
			o.servings_total_count, if(isnull(od2.id), 'NO', 'YES') as has_follow_up_order, od2.original_order_time as fu_order_time, od2.order_id as 'follow_up_order_id', o2.servings_total_count as 'follow_up_order_servings',
				CONCAT('=HYPERLINK(\"" . HTTPS_BASE . "?page=admin_order_history&id=', od.user_id, '\", \"History for ', u.primary_email , '\")') as history_link  from orders_digest od
			join orders o on o.id = od.order_id
			join user u on u.id = od.user_id
			left join orders_digest od2 on od.order_id = od2.in_store_trigger_order and od2.is_deleted = 0
			left join orders o2 on o2.id = od2.order_id
			where MONTH(od.session_time) = $month and YEAR(od.session_time) = $year and od.store_id = $store and od.is_deleted = 0
			group by od.order_id
			order by od.session_time");

		$tpl->assign("labels", array_pad(array(
			"Guest ID",
			"Order ID",
			"First Name",
			"Last Name",
			"Email",
			"Phone 1",
			"Phone 1 Call time",
			"Phone 2",
			"User State",
			"Order Type",
			"Web or Direct",
			"Session Type",
			"Order Time",
			"Session Time",
			"Number Servings",
			"Has Follow Up Order",
			"Follow up Order time",
			"Follow up Order ID",
			"Follow up Order Servings",
			"History Link"
		), 21, ""));

		while ($digestObj->fetch())
		{

			$rows[] = array(
				$digestObj->user_id,
				$digestObj->order_id,
				$digestObj->firstname,
				$digestObj->lastname,
				$digestObj->primary_email,
				$digestObj->telephone_1,
				$digestObj->telephone_1_call_time,
				$digestObj->telephone_2,
				$digestObj->user_state,
				$digestObj->order_type,
				$digestObj->Web_or_Direct,
				$digestObj->session_type,
				$digestObj->original_order_time,
				$digestObj->session_time,
				$digestObj->servings_total_count,
				$digestObj->has_follow_up_order,
				$digestObj->fu_order_time,
				$digestObj->follow_up_order_id,
				$digestObj->follow_up_order_servings,
				$digestObj->history_link
			);
		}

		$tpl->assign('rows', $rows);
	}

	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();
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

	function runHomeOfficeManager()
	{
		// is this person a coach?  If they are, then need to create a customized store drop down
		$this->runSiteAdmin();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

		$AdminUser = CUser::getCurrentUser();
		$userType = $AdminUser->user_type;

		$store = null;

		$showReportTypeSelector = false;
		if ($userType == CUser::HOME_OFFICE_STAFF || $userType == CUser::HOME_OFFICE_MANAGER || $userType == CUser::SITE_ADMIN)
		{
			$showReportTypeSelector = true;
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? CGPC::do_clean($_GET['store'], TYPE_INT) : '';
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChange => 'selectStore',
				CForm::allowAllOption => false,
				CForm::showInactiveStores => false,
				CForm::name => 'store'
			));
			$store = $Form->value('store');
		}
		else if ($this->currentStore)
		{
			$store = $this->currentStore;
		}

		$monthOptions = array("0" => 'Select a Month');
		$curMonthOptionNum = date('n');
		$curYearOptionNum = date('Y');

		for ($x = -2; ; $x++)
		{
			$formatter = "";

			if ($x < 0)
			{
				$formatter = "|||";
			}

			$y_m_d = date('Y-m-01', mktime(0, 0, 0, $curMonthOptionNum - $x, 1, $curYearOptionNum));
			$month_year = date('F Y', mktime(0, 0, 0, $curMonthOptionNum - $x, 1, $curYearOptionNum));

			$monthOptions[$y_m_d] = $formatter . $month_year;

			// earliest the dashboard reports go
			if ($y_m_d == '2012-01-01')
			{
				break;
			}
		}

		$futureMonth = date('Y-m-01', mktime(0, 0, 0, $curMonthOptionNum + 1, 1, $curYearOptionNum));
		$distantMonth = date('Y-m-01', mktime(0, 0, 0, $curMonthOptionNum + 2, 1, $curYearOptionNum));
		$todaysMonth = date('Y-m-01', mktime(0, 0, 0, $curMonthOptionNum, 1, $curYearOptionNum));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => true,
			CForm::options => $monthOptions,
			CForm::default_value => "0",
			CForm::name => 'override_month'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "store_id",
			CForm::value => $store
		));

		$Form->DefaultValues['report_type'] = 'dt_single_store';

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::value => 'dt_single_store'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::value => 'dt_corp_stores'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::value => 'dt_non_corp_stores'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::value => 'dt_all_stores'
		));

		$tpl->assign('showReportTypeSelector', $showReportTypeSelector);

		if (empty($_POST['monthMode']))
		{
			$monthMode = "current";
		}
		else
		{
			$monthMode = CGPC::do_clean($_POST['monthMode'], TYPE_STR);
		}

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "monthMode",
			CForm::value => $monthMode
		));
		$isFutureMonth = false;
		$isDistantMonth = false;

		// -------------------------------------------- AGR comparison
		if (!empty($_REQUEST['override_month']) && $_REQUEST['override_month'] != "Select a Month")
		{

			$OM_parts = explode("-", CGPC::do_clean($_REQUEST['override_month'], TYPE_STR));

			$todaysNumber = 1;
			$currentMonth = date('Y-m-01', strtotime(CGPC::do_clean($_REQUEST['override_month'], TYPE_STR)));
			$curMonthTS = strtotime($currentMonth);
			$currentMonthStr = date("F Y", $curMonthTS);
			$curMonth = date("n", $curMonthTS);
			$curYear = date("Y", $curMonthTS);
			if ($futureMonth == $currentMonth)
			{
				$isFutureMonth = true;
			}
			if ($distantMonth == $currentMonth)
			{
				$isDistantMonth = true;
			}

			if ($todaysMonth == $currentMonth)
			{
				$tpl->assign("showCurrentMonth", true);
			}
			else
			{
				$tpl->assign("showCurrentMonth", false);
			}
		}
		else if ($monthMode == "current")
		{
			$todaysNumber = date('d');
			$currentMonth = date("Y-m-01");
			$currentMonthStr = date("F Y");
			$curMonth = date("n");
			$curYear = date("Y");
			$tpl->assign("showCurrentMonth", true);
		}
		else
		{
			$todaysNumber = date('d');
			$curMonth = date("n");
			$curYear = date("Y");

			$lastMonth = $curMonth - 1;
			$tempYear = $curYear;
			if ($lastMonth == 0)
			{
				$lastMonth = 12;
				$tempYear--;
			}

			$numDaysInLastMonth = date("t", mktime(0, 0, 0, $lastMonth, 1, $tempYear));

			if ($todaysNumber > $numDaysInLastMonth)
			{
				$todaysNumber = $numDaysInLastMonth;
			}

			$todayLastMonth = mktime(0, 0, 0, $curMonth - 1, $todaysNumber, $curYear);

			$todaysNumber = date('d', $todayLastMonth);
			$currentMonth = date("Y-m-01", $todayLastMonth);
			$currentMonthStr = date("F Y", $todayLastMonth);
			$curMonth = date("n", $todayLastMonth);
			$curYear = date("Y", $todayLastMonth);
			$tpl->assign("showCurrentMonth", false);
		}

		$previousMonth = mktime(0, 0, 0, $curMonth - 1, 1, $curYear);
		$previousMonthStr = date("F Y", $previousMonth);
		$curMonthLastYear = mktime(0, 0, 0, $curMonth, 1, $curYear - 1);
		$curMonthLastYearStr = date("F Y", $curMonthLastYear);
		$nextMonth = mktime(0, 0, 0, $curMonth + 1, 1, $curYear);
		$nextMonthStr = date("F Y", $nextMonth);
		$nextMonthLastYear = mktime(0, 0, 0, $curMonth + 1, 1, $curYear - 1);
		$nextMonthLastYearStr = date("F Y", $nextMonthLastYear);
		$distantMonth = mktime(0, 0, 0, $curMonth + 2, 1, $curYear);
		$distantMonthStr = date("F Y", $distantMonth);
		$distantMonthLastYear = mktime(0, 0, 0, $curMonth + 2, 1, $curYear - 1);
		$distantMonthLastYearStr = date("F Y", $distantMonthLastYear);

		$tpl->assign('currentMonthStr', $currentMonthStr);
		$tpl->assign('previousMonthStr', $previousMonthStr);
		$tpl->assign('curMonthLastYearStr', $curMonthLastYearStr);
		$tpl->assign('nextMonthStr', $nextMonthStr);
		$tpl->assign('nextMonthLastYearStr', $nextMonthLastYearStr);
		$tpl->assign('distantMonthStr', $distantMonthStr);
		$tpl->assign('distantMonthLastYearStr', $distantMonthLastYearStr);
		$tpl->assign('isFutureMonth', $isFutureMonth);
		$tpl->assign('isDistantMonth', $isDistantMonth);

		$is_exporting = false;
		if (isset($_REQUEST['export']) && $_REQUEST['export'] == "xlsx")
		{

			$storeInfo = DAO_CFactory::create('store');
			$storeInfo->query("select store_name, city, state_id from store where id = $store");
			$storeInfo->fetch();
			$titleString = "Order Details for " . $currentMonthStr . " " . $storeInfo->store_name . " " . $storeInfo->city . ", " . $storeInfo->state_id;
			CLog::RecordReport("New Dashboard Export (Excel Export)", "Store: $store");

			$this->exportOrderList($curMonth, $curYear, $store, $tpl, $titleString);

			return;
		}

		$titleString = "Dashboard Report";

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "curMonthStr",
			CForm::value => $currentMonth
		));

		$reportType = $Form->value('report_type');

		$hadError = false;

		if ($reportType == 'dt_single_store' && empty($store))
		{
			$hadError = true;
			$tpl->assign('dashboard_error', 'Please choose a store or change the report type.');
		}

		if (!$hadError && $reportType == 'dt_single_store' && CDashboardNew::testForUpdateRequired($store))
		{

			if (defined('HOSTED_AS_REPORTING_SERVER') && HOSTED_AS_REPORTING_SERVER)
			{
				$tpl->assign('updateRequired', false);
			}
			else
			{
				$tpl->assign('dashboard_error', 'The metrics are currently updating. Please wait. The page will update automatically when the update is complete.');
				$tpl->assign('updateRequired', true);
				$hadError = true;
			}
		}

		$includes_delivered_revenue = false;

		if (!$hadError && $reportType == 'dt_single_store')
		{

			$storeInfo = DAO_CFactory::create('store');
			$storeInfo->query("select store_name, city, state_id, store_type from store where id = $store");
			$storeInfo->fetch();
			$titleString = "Dashboard Report for<br />" . $currentMonthStr . "<br />" . $storeInfo->store_name . " " . $storeInfo->city . ", " . $storeInfo->state_id;

			if ($storeInfo->store_type == CStore::DISTRIBUTION_CENTER)
			{
				$includes_delivered_revenue = true;
			}

			// current month AGR
			$currentAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$currentAGRMetrics->store_id = $store;
			$currentAGRMetrics->date = $currentMonth;
			$currentAGRMetrics->find(true);
			$tpl->assign('curMonthAGRMetrics', $currentAGRMetrics->toArray());

			// next month AGR
			$nextAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$nextAGRMetrics->store_id = $store;
			$nextAGRMetrics->date = date("Y-m-01", $nextMonth);
			$nextAGRMetrics->find(true);
			$tpl->assign('nextMonthAGRMetrics', $nextAGRMetrics->toArray());

			// distant month AGR
			$distantAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$distantAGRMetrics->store_id = $store;
			$distantAGRMetrics->date = date("Y-m-01", $distantMonth);
			$distantAGRMetrics->find(true);
			$tpl->assign('distMonthAGRMetrics', $distantAGRMetrics->toArray());

			// PARTIAL MONTHES (accumulate AGR placed on or before the current day of the month

			/*
			// distant month AGR
			$previousAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_partials');
			$previousAGRMetrics->store_id = $store;
			$previousAGRMetrics->date = date("Y-m-$todaysNumber", $previousMonth);
			$previousAGRMetrics->find(true);
			$tpl->assign('prevMonthAGRMetrics', $previousAGRMetrics->toArray());

			// thisMonthLastYear
			$thisMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_partials');
			$thisMonthLastYearAGRMetrics->store_id = $store;
			$thisMonthLastYearAGRMetrics->date = date("Y-m-$todaysNumber", $curMonthLastYear);
			$thisMonthLastYearAGRMetrics->find(true);
			$tpl->assign('curMonthLastYearAGRMetrics', $thisMonthLastYearAGRMetrics->toArray());

			// nextMonthLastYear
			$nextMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_partials');
			$nextMonthLastYearAGRMetrics->store_id = $store;
			$nextMonthLastYearAGRMetrics->date = date("Y-m-$todaysNumber", $nextMonthLastYear);
			$nextMonthLastYearAGRMetrics->find(true);
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearAGRMetrics->toArray());

			// distantMonthLastYear
			$distantMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_partials');
			$distantMonthLastYearAGRMetrics->store_id = $store;
			$distantMonthLastYearAGRMetrics->date = date("Y-m-$todaysNumber", $distantMonthLastYear);
			$distantMonthLastYearAGRMetrics->find(true);
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearAGRMetrics->toArray());

		*/

			// distant month AGR
			$previousAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$previousAGRMetrics->store_id = $store;
			$previousAGRMetrics->date = date("Y-m-01", $previousMonth);
			$previousAGRMetrics->find(true);
			$tpl->assign('prevMonthAGRMetrics', $previousAGRMetrics->toArray());

			// thisMonthLastYear
			$thisMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$thisMonthLastYearAGRMetrics->store_id = $store;
			$thisMonthLastYearAGRMetrics->date = date("Y-m-01", $curMonthLastYear);
			$thisMonthLastYearAGRMetrics->find(true);
			$tpl->assign('curMonthLastYearAGRMetrics', $thisMonthLastYearAGRMetrics->toArray());

			// nextMonthLastYear
			$nextMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$nextMonthLastYearAGRMetrics->store_id = $store;
			$nextMonthLastYearAGRMetrics->date = date("Y-m-01", $nextMonthLastYear);
			$nextMonthLastYearAGRMetrics->find(true);
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearAGRMetrics->toArray());

			// distantMonthLastYear
			$distantMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr');
			$distantMonthLastYearAGRMetrics->store_id = $store;
			$distantMonthLastYearAGRMetrics->date = date("Y-m-01", $distantMonthLastYear);
			$distantMonthLastYearAGRMetrics->find(true);
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearAGRMetrics->toArray());

			// current month Guests
			$currentGuestMetrics = DAO_CFactory::create('dashboard_metrics_guests');
			$currentGuestMetrics->store_id = $store;
			$currentGuestMetrics->date = $currentMonth;
			$currentGuestMetrics->find(true);

			$guestMetricsArray = $currentGuestMetrics->toArray();
			CDashboardNew::addToDateGuestCounts($guestMetricsArray, $currentMonth, $store);
			$tpl->assign('curMonthGuestMetrics', $guestMetricsArray);

			$occupiedTasteSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'DREAM_TASTE', $store);
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);

			$occupiedFundraiserSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'FUNDRAISER', $store);
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);
		}
		else if (!$hadError && $reportType == 'dt_corp_stores')
		{

			$includes_delivered_revenue = true;

			$titleString = "Dashboard Report for<br />" . $currentMonthStr . "<br />" . "Corporate Stores";

			// current month AGR
			$curMonthrollup = CDashboardNew::getRollupAGRbyMonth($currentMonth, 'corp_stores');
			$tpl->assign('curMonthAGRMetrics', $curMonthrollup);

			// next month AGR
			$nextMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'corp_stores');
			$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

			// distant month AGR
			$distMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'corp_stores');
			$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

			/*
			// previous month AGR
			$prevMonthrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $previousMonth), 'corp_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $curMonthLastYear), 'corp_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $nextMonthLastYear), 'corp_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $distantMonthLastYear), 'corp_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);
			*/

			$prevMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'corp_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'corp_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'corp_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'corp_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

			// current Month guests
			$currentGuestMetrics = CDashboardNew::getRollupGuestNumberdByMonth($currentMonth, 'corp_stores');
			CDashboardNew::addToDateGuestCounts($currentGuestMetrics, $currentMonth, 'corp_stores');

			$occupiedTasteSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'DREAM_TASTE', 'corp_stores');
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);
			$occupiedFundraiserSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'FUNDRAISER', 'corp_stores');
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);

			$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);
		}
		else if (!$hadError && $reportType == 'dt_non_corp_stores')
		{

			$includes_delivered_revenue = true;

			$titleString = "Dashboard Report for<br />" . $currentMonthStr . "<br />" . "Franchise Stores";

			// current month AGR
			$curMonthrollup = CDashboardNew::getRollupAGRbyMonth($currentMonth, 'non_corp_stores');
			$tpl->assign('curMonthAGRMetrics', $curMonthrollup);

			// next month AGR
			$nextMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'non_corp_stores');
			$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

			// distant month AGR
			$distMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'non_corp_stores');
			$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

			/*
			// previous month AGR
			$prevMonthrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $previousMonth), 'non_corp_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $curMonthLastYear), 'non_corp_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $nextMonthLastYear), 'non_corp_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $distantMonthLastYear), 'non_corp_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);
			*/

			// previous month AGR
			$prevMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'non_corp_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'non_corp_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'non_corp_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'non_corp_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

			// current Month guests
			$currentGuestMetrics = CDashboardNew::getRollupGuestNumberdByMonth($currentMonth, 'non_corp_stores');
			CDashboardNew::addToDateGuestCounts($currentGuestMetrics, $currentMonth, 'non_corp_stores');
			$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);

			$occupiedTasteSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'DREAM_TASTE', 'non_corp_stores');
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);

			$occupiedFundraiserSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'FUNDRAISER', 'non_corp_stores');
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);
		}
		else if (!$hadError && $reportType == 'dt_all_stores')
		{

			$includes_delivered_revenue = true;

			$titleString = "Dashboard Report for<br />" . $currentMonthStr . "<br />" . "All Stores";

			// current month AGR
			$curMonthrollup = CDashboardNew::getRollupAGRbyMonth($currentMonth, 'all_stores');
			$tpl->assign('curMonthAGRMetrics', $curMonthrollup);
			// next month AGR
			$nextMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'all_stores');
			$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

			// distant month AGR
			$distMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'all_stores');
			$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

			/*
			// previous month AGR
			$prevMonthrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $previousMonth), 'all_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $curMonthLastYear), 'all_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $nextMonthLastYear), 'all_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonthPartial(date("Y-m-$todaysNumber", $distantMonthLastYear), 'all_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);
			*/

			// previous month AGR
			$prevMonthrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'all_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'all_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'all_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardNew::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'all_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

			// current Month guests
			$currentGuestMetrics = CDashboardNew::getRollupGuestNumberdByMonth($currentMonth, 'all_stores');
			CDashboardNew::addToDateGuestCounts($currentGuestMetrics, $currentMonth, 'all_stores');
			$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);

			$occupiedTasteSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'DREAM_TASTE', 'all_stores');
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);

			$occupiedFundraiserSessionCount = CDashboardNew::getOccupiedSessionCountForMonth($curMonth, $curYear, 'FUNDRAISER', 'all_stores');
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);
		}
		$tpl->assign("titleString", $titleString);

		$tpl->assign("includes_delivered_revenue", $includes_delivered_revenue);

		/// ------------------------- calculate deltas
		if (!$hadError && $reportType == 'dt_single_store')
		{
			/*
			$currentAGRMetrics->store_id = $store;

			$nextAGRMetrics->store_id = $store;

			$distantAGRMetrics->store_id = $store;
			*/
			$previousAGRDelta = $currentAGRMetrics->total_agr - $previousAGRMetrics->total_agr;
			$previousAGRDeltaPercent = CTemplate::divide_and_format(($currentAGRMetrics->total_agr - $previousAGRMetrics->total_agr) * 100, $previousAGRMetrics->total_agr, 2);
			$tpl->assignAndFormatMetricDollars('previousAGRDelta', $previousAGRDelta);
			$tpl->assignAndFormatMetricPercent('previousAGRDeltaPercent', $previousAGRDeltaPercent);

			$curMonthlastYearAGRDelta = $currentAGRMetrics->total_agr - $thisMonthLastYearAGRMetrics->total_agr;
			$curMonthlastYearAGRDeltaPercent = CTemplate::divide_and_format(($currentAGRMetrics->total_agr - $thisMonthLastYearAGRMetrics->total_agr) * 100, $thisMonthLastYearAGRMetrics->total_agr, 2);
			$tpl->assignAndFormatMetricDollars('curMonthlastYearAGRDelta', $curMonthlastYearAGRDelta);
			$tpl->assignAndFormatMetricPercent('curMonthlastYearAGRDeltaPercent', $curMonthlastYearAGRDeltaPercent);

			$nextMonthlastYearAGRDelta = $nextAGRMetrics->total_agr - $nextMonthLastYearAGRMetrics->total_agr;
			$nextMonthlastYearAGRDeltaPercent = CTemplate::divide_and_format(($nextAGRMetrics->total_agr - $nextMonthLastYearAGRMetrics->total_agr) * 100, $nextMonthLastYearAGRMetrics->total_agr, 2);
			$tpl->assignAndFormatMetricDollars('nextMonthlastYearAGRDelta', $nextMonthlastYearAGRDelta);
			$tpl->assignAndFormatMetricPercent('nextMonthlastYearAGRDeltaPercent', $nextMonthlastYearAGRDeltaPercent);

			$distantMonthlastYearAGRDelta = $distantAGRMetrics->total_agr - $distantMonthLastYearAGRMetrics->total_agr;
			$distantMonthlastYearAGRDeltaPercent = CTemplate::divide_and_format(($distantAGRMetrics->total_agr - $distantMonthLastYearAGRMetrics->total_agr) * 100, $distantMonthLastYearAGRMetrics->total_agr, 2);
			$tpl->assignAndFormatMetricDollars('distantMonthlastYearAGRDelta', $distantMonthlastYearAGRDelta);
			$tpl->assignAndFormatMetricPercent('distantMonthlastYearAGRDeltaPercent', $distantMonthlastYearAGRDeltaPercent);
		}
		else if (!$hadError)
		{

			$previousAGRDelta = $curMonthrollup['total_agr'] - $prevMonthrollup['total_agr'];
			$previousAGRDeltaPercent = CTemplate::divide_and_format(($curMonthrollup['total_agr'] - $prevMonthrollup['total_agr']) * 100, $prevMonthrollup['total_agr'], 2);
			$tpl->assignAndFormatMetricDollars('previousAGRDelta', $previousAGRDelta);
			$tpl->assignAndFormatMetricPercent('previousAGRDeltaPercent', $previousAGRDeltaPercent);

			$curMonthlastYearAGRDelta = $curMonthrollup['total_agr'] - $curMonthLastYearrollup['total_agr'];
			$curMonthlastYearAGRDeltaPercent = CTemplate::divide_and_format(($curMonthrollup['total_agr'] - $curMonthLastYearrollup['total_agr']) * 100, $curMonthLastYearrollup['total_agr'], 2);
			$tpl->assignAndFormatMetricDollars('curMonthlastYearAGRDelta', $curMonthlastYearAGRDelta);
			$tpl->assignAndFormatMetricPercent('curMonthlastYearAGRDeltaPercent', $curMonthlastYearAGRDeltaPercent);

			$nextMonthlastYearAGRDelta = $nextMonthrollup['total_agr'] - $nextMonthLastYearrollup['total_agr'];
			$nextMonthlastYearAGRDeltaPercent = CTemplate::divide_and_format(($nextMonthrollup['total_agr'] - $nextMonthLastYearrollup['total_agr']) * 100, $nextMonthLastYearrollup['total_agr'], 2);
			$tpl->assignAndFormatMetricDollars('nextMonthlastYearAGRDelta', $nextMonthlastYearAGRDelta);
			$tpl->assignAndFormatMetricPercent('nextMonthlastYearAGRDeltaPercent', $nextMonthlastYearAGRDeltaPercent);

			$distantMonthlastYearAGRDelta = $distMonthrollup['total_agr'] - $distantMonthLastYearrollup['total_agr'];
			$distantMonthlastYearAGRDeltaPercent = CTemplate::divide_and_format(($distMonthrollup['total_agr'] - $distantMonthLastYearrollup['total_agr']) * 100, $distantMonthLastYearrollup['total_agr'], 2);
			$tpl->assignAndFormatMetricDollars('distantMonthlastYearAGRDelta', $distantMonthlastYearAGRDelta);
			$tpl->assignAndFormatMetricPercent('distantMonthlastYearAGRDeltaPercent', $distantMonthlastYearAGRDeltaPercent);
		}

		if (!$hadError)
		{
			CLog::RecordReport("New (Calendar-based) Dashboard Report", "Store: $store");

			$myRankingsObj = DAO_CFactory::create('dashboard_metrics_rankings');
			$myRankingsObj->date = $currentMonth;
			$myRankingsObj->store_id = $store;
			$myRankingsObj->find(true);

			$tpl->assign('myRankings', $myRankingsObj->toArray());
		}

		$formArray = $Form->render();
		$tpl->assign('store', $store);

		$tpl->assign('form_array', $formArray);
	}

}

?>