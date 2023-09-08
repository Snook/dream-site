<?php // main.php

require_once("includes/CPageAdminOnly.inc");
require_once("includes/CDashboardReportMenuBased.inc");

class page_admin_dashboard_menu_based extends CPageAdminOnly
{

	private $currentStore = null;
	private $multiStoreOwnerStores = false;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function exportOrderList($curMonthStartDate, $curMonthInterval, $store, $tpl, $titleString)
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

		$nextOrder = "(select ss.session_start from 
						session ss 
						join booking bb on bb.session_id = ss.id and bb.status != 'CANCELLED' and bb.is_deleted = 0 
						join orders oo on oo.id = bb.order_id and oo.is_deleted = 0 and  oo.is_deleted = 0 
						where ss.session_start > od.session_time 
						and oo.id != od.order_id
						and oo.user_id = o.user_id
						order by ss.session_start asc limit 1) as 'next_order_session_time',";

		$query = "select $nextOrder od.user_id, od.order_id, u.firstname, u.lastname, u.primary_email, u.telephone_1, u.telephone_1_call_time,  u.telephone_2, od.user_state,  od.order_type, o.order_type as Web_or_Direct, od.session_type, od.original_order_time, od.session_time,
			o.servings_total_count, o.menu_items_core_total_count, if(isnull(od2.id), 'NO', 'YES') as has_follow_up_order, od2.original_order_time as fu_order_time, od2.order_id as 'follow_up_order_id', o2.servings_total_count as 'follow_up_order_servings',
				CONCAT('=HYPERLINK(\"" . HTTPS_BASE . "?page=admin_order_history&id=', od.user_id, '\", \"History for ', u.primary_email , '\")') as history_link  
				from orders_digest od
			join orders o on o.id = od.order_id
			join user u on u.id = od.user_id
			left join orders_digest od2 on od.order_id = od2.in_store_trigger_order and od2.is_deleted = 0
			left join orders o2 on o2.id = od2.order_id
			where 
			od.session_time >= '$curMonthStartDate' and od.session_time < DATE_ADD('$curMonthStartDate', INTERVAL $curMonthInterval DAY)
			and od.store_id = $store and od.is_deleted = 0
			group by od.order_id
			order by od.session_time";

		$digestObj->query($query);

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
			"Number Core Items",
			"Next Order Session Time",
			"Has in-store sign up order",
			"In-store sign up order time",
			"In-store sign up order ID",
			"In-store sign up order servings",
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
				$digestObj->menu_items_core_total_count,
				$digestObj->next_order_session_time,
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
		$theStores = array();
		$hasMultipleStores = CUser::getCurrentUser()->isMultiStoreOwner($theStores);

		if ($hasMultipleStores)
		{
			$this->multiStoreOwnerStores = $theStores;
		}
		else
		{
			$this->currentStore = CApp::forceLocationChoice();
		}

		$this->runSiteAdmin();
	}

	function runFranchiseManager()
	{
		$theStores = array();
		$hasMultipleStores = CUser::getCurrentUser()->isMultiStoreOwner($theStores);

		if ($hasMultipleStores)
		{
			$this->multiStoreOwnerStores = $theStores;
		}
		else
		{
			$this->currentStore = CApp::forceLocationChoice();
		}

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

		$titleString = "";
		$store = null;
		$didReturnCustomRollup = false;

		$showReportTypeSelector = false;

		if ($this->multiStoreOwnerStores)
		{
			$showReportTypeSelector = true;

			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : '';
			$Form->addElement(array(
				CForm::type => CForm::DropDown,
				CForm::onChange => 'selectStore',
				CForm::allowAllOption => false,
				CForm::options => $this->multiStoreOwnerStores,
				CForm::name => 'store'
			));
			$store = $Form->value('store');
		}
		else if ($userType == CUser::HOME_OFFICE_STAFF || $userType == CUser::HOME_OFFICE_MANAGER || $userType == CUser::SITE_ADMIN)
		{
			$showReportTypeSelector = true;
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : '';
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChange => 'selectStore',
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));
			$store = $Form->value('store');
		}
		else if ($this->currentStore)
		{
			$store = $this->currentStore;
		}

		$curMenuID = CMenu::getCurrentMenuId();
		$curMenuObj = DAO_CFactory::create('menu');
		$curMenuObj->id = $curMenuID;
		$curMenuObj->find(true);

		$todaysMonth = $curMenuObj->menu_start;
		$todaysMonthTS = strtotime($todaysMonth);

		$monthOptions = array("0" => 'Select a Month');
		$curMonthOptionNum = date('n', $todaysMonthTS);
		$curYearOptionNum = date('Y', $todaysMonthTS);

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

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChange => 'menuChange',
			CForm::options => $monthOptions,
			CForm::default_value => "0",
			CForm::name => 'override_month'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "store_id",
			CForm::value => $store
		));
		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "store_array"
		));

		$Form->DefaultValues['report_type'] = 'dt_single_store';

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::onChange => "report_type_select",
			CForm::value => 'dt_single_store'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::onChange => "report_type_select",
			CForm::value => 'dt_soft_launch'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::onChange => "report_type_select",
			CForm::value => 'dt_non_soft_launch'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "report_type",
			CForm::onChange => "report_type_select",
			CForm::value => 'dt_custom'
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

		$hasMenu = false;

		if (!$showReportTypeSelector)
		{ // For Store personnel

			$hasMenu = true;
			// -------------------------------------------- AGR comparison
			if (!empty($_REQUEST['override_month']) && $_REQUEST['override_month'] != "Select a Month")
			{

				$OM_parts = explode("-", CGPC::do_clean($_REQUEST['override_month'], TYPE_STR));

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

				list($curMonthStartDate, $curMonthInterval) = CMenu::getMenuStartandInterval(false, $currentMonth);

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
				$currentMonth = $todaysMonth;
				$currentMonthStr = date("F Y", $todaysMonthTS);
				$curMonth = date("n", $todaysMonthTS);
				$curYear = date("Y", $todaysMonthTS);

				list($curMonthStartDate, $curMonthInterval) = CMenu::getMenuStartandInterval(false, $todaysMonth);

				$tpl->assign("showCurrentMonth", true);
			}
			else
			{ // previous

				$curMonth = date("n", $todaysMonthTS);
				$curYear = date("Y", $todaysMonthTS);

				$todayLastMonth = mktime(0, 0, 0, $curMonth - 1, 1, $curYear);

				$currentMonth = date("Y-m-01", $todayLastMonth);
				$currentMonthStr = date("F Y", $todayLastMonth);
				$curMonth = date("n", $todayLastMonth);
				$curYear = date("Y", $todayLastMonth);

				list($curMonthStartDate, $curMonthInterval) = CMenu::getMenuStartandInterval(false, $currentMonth);

				$tpl->assign("showCurrentMonth", false);
			}
		}
		else if (!empty($_REQUEST['override_month']) && $_REQUEST['override_month'] != "Select a Month")
		{

			$hasMenu = true;
			$OM_parts = explode("-", CGPC::do_clean($_REQUEST['override_month'], TYPE_STR));

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

			list($curMonthStartDate, $curMonthInterval) = CMenu::getMenuStartandInterval(false, $currentMonth);
			$currentMonthMenuID = CMenu::getMenuIDByAnchorDate($currentMonth);

			$tpl->assign('selected_menu', $currentMonthMenuID);
		}

		$hadError = false;
		$reportType = $Form->value('report_type');

		if (in_array($userType, array(
			"SITE_ADMIN",
			"HOME_OFFICE_MANAGER"
		)))
		{
			$tpl->assign('showDeliveredRows', true);
		}
		else
		{
			$tpl->assign('showDeliveredRows', false);
		}

		if ($hasMenu)
		{
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
		}
		else
		{
			$hadError = true;
			$tpl->assign('dashboard_error', 'Please select a menu and a store or stores.');
		}

		if (!$hadError)
		{

			$is_exporting = false;
			if (isset($_REQUEST['export']) && $_REQUEST['export'] == "xlsx")
			{

				$storeInfo = DAO_CFactory::create('store');
				$storeInfo->query("select store_name, city, state_id from store where id = $store");
				$storeInfo->fetch();
				$titleString = "Order Details for " . $currentMonthStr . " " . $storeInfo->store_name . " " . $storeInfo->city . ", " . $storeInfo->state_id;
				CLog::RecordReport("New Dashboard Export (Excel Export)", "Store: $store");

				$this->exportOrderList($curMonthStartDate, $curMonthInterval, $store, $tpl, $titleString);

				return;
			}

			$titleString = "Dashboard Report";

			$ownerID = false;
			if ($this->multiStoreOwnerStores)
			{
				$ownerID = CUser::getCurrentUser()->id;
			}

			if ($showReportTypeSelector)
			{
				$tpl->assign('store_data', CStore::getStoreTreeAsNestedList($ownerID, true, $currentMonthMenuID));
			}

			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => "curMonthStr",
				CForm::value => $currentMonth
			));
		}

		if ($reportType == 'dt_single_store' && empty($store))
		{
			$hadError = true;
			$tpl->assign('dashboard_error', 'Please select a menu and a store or stores.');
		}

		if ($showReportTypeSelector && empty($_POST['store_array']) && $reportType == 'dt_custom')
		{
			$hadError = true;
			$tpl->assign('dashboard_error', 'Please select a menu and a store or stores.');
		}

		if (!$hadError && $reportType == 'dt_single_store' && CDashboardMenuBased::testForUpdateRequired($store))
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

		if (!$hadError && $reportType == 'dt_single_store')
		{

			$query = "select store_name, city, state_id, store_type from store where id = $store";
			$storeInfo = DAO_CFactory::create('store');
			$storeInfo->query($query);
			$storeInfo->fetch();
			$titleString = "Dashboard Report for " . $currentMonthStr . " " . $storeInfo->store_name . " " . $storeInfo->city . ", " . $storeInfo->state_id;

			if ($storeInfo->store_type == CStore::DISTRIBUTION_CENTER)
			{
				$tpl->assign('showDeliveredRows', true);
			}
			else
			{
				$tpl->assign('showDeliveredRows', false);
			}

			$tpl->assign('showAdditionalOrderRows', COrderMinimum::allowsAdditionalOrdering($store, $currentMonthMenuID));

			// current month AGR
			$currentAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$currentAGRMetrics->store_id = $store;
			$currentAGRMetrics->date = $currentMonth;
			$currentAGRMetrics->find(true);
			$currentAGRMetricsArray = $currentAGRMetrics->toArray();
			CDashboardMenuBased::addMonthStartRevenue($store, $currentMonth, $currentAGRMetricsArray, 'month_start_total_agr');
			$tpl->assign('curMonthAGRMetrics', $currentAGRMetricsArray);

			// next month AGR
			$nextAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$nextAGRMetrics->store_id = $store;
			$nextAGRMetrics->date = date("Y-m-01", $nextMonth);
			$nextAGRMetrics->find(true);
			$nextAGRMetricsArray = $nextAGRMetrics->toArray();
			CDashboardMenuBased::addMonthStartRevenue($store, $nextAGRMetricsArray['date'], $nextAGRMetricsArray, 'month_start_total_agr');
			$tpl->assign('nextMonthAGRMetrics', $nextAGRMetricsArray);

			// distant month AGR
			$distantAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$distantAGRMetrics->store_id = $store;
			$distantAGRMetrics->date = date("Y-m-01", $distantMonth);
			$distantAGRMetrics->find(true);
			$distantAGRMetricsArray = $distantAGRMetrics->toArray();
			CDashboardMenuBased::addMonthStartRevenue($store, $distantAGRMetricsArray['date'], $distantAGRMetricsArray, 'month_start_total_agr');
			$tpl->assign('distMonthAGRMetrics', $distantAGRMetricsArray);

			// previous month AGR
			$previousAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$previousAGRMetrics->store_id = $store;
			$previousAGRMetrics->date = date("Y-m-01", $previousMonth);
			$previousAGRMetrics->find(true);
			$previousAGRMetricsArray = $previousAGRMetrics->toArray();
			CDashboardMenuBased::addMonthStartRevenue($store, $previousAGRMetricsArray['date'], $previousAGRMetricsArray, 'month_start_total_agr');
			$tpl->assign('prevMonthAGRMetrics', $previousAGRMetricsArray);

			// thisMonthLastYear
			$thisMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$thisMonthLastYearAGRMetrics->store_id = $store;
			$thisMonthLastYearAGRMetrics->date = date("Y-m-01", $curMonthLastYear);
			$thisMonthLastYearAGRMetrics->find(true);
			$thisMonthLastYearAGRMetricsArray = $thisMonthLastYearAGRMetrics->toArray();
			CDashboardMenuBased::addMonthStartRevenue($store, $thisMonthLastYearAGRMetricsArray['date'], $thisMonthLastYearAGRMetricsArray, 'month_start_total_agr');
			$tpl->assign('curMonthLastYearAGRMetrics', $thisMonthLastYearAGRMetricsArray);

			// nextMonthLastYear
			$nextMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$nextMonthLastYearAGRMetrics->store_id = $store;
			$nextMonthLastYearAGRMetrics->date = date("Y-m-01", $nextMonthLastYear);
			$nextMonthLastYearAGRMetrics->find(true);
			$nextMonthLastYearAGRMetricsArray = $nextMonthLastYearAGRMetrics->toArray();
			CDashboardMenuBased::addMonthStartRevenue($store, $nextMonthLastYearAGRMetricsArray['date'], $nextMonthLastYearAGRMetricsArray, 'month_start_total_agr');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearAGRMetricsArray);

			// distantMonthLastYear
			$distantMonthLastYearAGRMetrics = DAO_CFactory::create('dashboard_metrics_agr_by_menu');
			$distantMonthLastYearAGRMetrics->store_id = $store;
			$distantMonthLastYearAGRMetrics->date = date("Y-m-01", $distantMonthLastYear);
			$distantMonthLastYearAGRMetrics->find(true);
			$distantMonthLastYearAGRMetricsArray = $distantMonthLastYearAGRMetrics->toArray();
			CDashboardMenuBased::addMonthStartRevenue($store, $distantMonthLastYearAGRMetricsArray['date'], $distantMonthLastYearAGRMetricsArray, 'month_start_total_agr');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearAGRMetricsArray);

			// current month Guests
			$currentGuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');
			$currentGuestMetrics->store_id = $store;
			$currentGuestMetrics->date = $currentMonth;
			$currentGuestMetrics->find(true);

			$guestMetricsArray = $currentGuestMetrics->toArray();
			CDashboardMenuBased::addToDateGuestCounts($guestMetricsArray, $store, $curMonthStartDate, $curMonthInterval);
			$tpl->assign('curMonthGuestMetrics', $guestMetricsArray);

			$occupiedTasteSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'DREAM_TASTE', $store);
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);

			$occupiedFundraiserSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'FUNDRAISER', $store);
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);
		}
		else if (!$hadError && ($reportType == 'dt_soft_launch' || $reportType == 'dt_non_soft_launch'))
		{
			$MARKETING_TEST_STORES = array(30, 54, 61, 63, 91, 136, 215, 232, 244, 261, 264, 288, 308, 309);
			$titleString = "Dashboard Report for Marketing Test Store Set";

			$storeArr = array();
			if ($reportType == 'dt_soft_launch')
			{
				$tempStoreArr = $MARKETING_TEST_STORES;
			}
			else
			{
				$titleString = "Dashboard Report for Non-Marketing Test Store Set";
				$query = "select GROUP_CONCAT(id) as ids from store where id not in (".implode(",",$MARKETING_TEST_STORES).") and active = 1 and is_deleted = 0 order by id";
				$storeInfo = DAO_CFactory::create('store');
				$storeInfo->query($query);
				$storeInfo->fetch();

				$tempStoreArr = explode(',',$storeInfo->ids);
			}


			$allowsAdditionalOrdering = false;
			foreach ($tempStoreArr as $thisStore)
			{
				if (!empty($thisStore) && is_numeric($thisStore))
				{
					$storeArr[] = $thisStore;

					$allowsAdditionalOrdering = ($allowsAdditionalOrdering == false ? COrderMinimum::allowsAdditionalOrdering($thisStore, $currentMonthMenuID) : $allowsAdditionalOrdering);
				}
			}

			$tpl->assign('showAdditionalOrderRows', $allowsAdditionalOrdering);
			if (!empty($storeArr))
			{

				if (count($storeArr) < 50)
				{
					$storeNames = new DAO();
					$q = "select s.store_name from store s where s.id in (" . implode(",", $storeArr) . ",312) order by s.store_name";
					$storeNames->query($q);
					$names = '';
					while ($storeNames->fetch())
					{
						$names .= $storeNames->store_name . ', ';
					}
					$titleString .= ": <p style='font-size: xx-small;'>" . rtrim($names, ', ') . "</p>";
				}

				// current month AGR
				$curMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth($currentMonth, 'custom', $storeArr);
				$tpl->assign('curMonthAGRMetrics', $curMonthrollup);

				// next month AGR
				$nextMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'custom', $storeArr);
				$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

				// distant month AGR
				$distMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'custom', $storeArr);
				$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

				$prevMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'custom', $storeArr);
				$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

				// thisMonthLastYear
				$curMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'custom', $storeArr);
				$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

				// nextMonthLastYear
				$nextMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'custom', $storeArr);
				$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

				// distantMonthLastYear
				$distantMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'custom', $storeArr);
				$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

				// current Month guests
				$currentGuestMetrics = CDashboardMenuBased::getRollupGuestNumberdByMonth($currentMonth, 'custom', $storeArr);
				CDashboardMenuBased::addToDateGuestCounts($currentGuestMetrics, 'custom', $curMonthStartDate, $curMonthInterval, $storeArr);

				$occupiedTasteSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'DREAM_TASTE', 'custom', $storeArr);
				$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);
				$occupiedFundraiserSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'FUNDRAISER', 'custom', $storeArr);
				$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);

				$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);

				$didReturnCustomRollup = false;
			}
			else
			{
				$hadError = true;
			}
		}
		else if (!$hadError && $reportType == 'dt_custom')
		{

			$titleString = "Dashboard Report for Custom Store Set";

			if (isset($_POST['store_array']))
			{

				$storeArr = array();
				$tempStoreArr = explode(",", CGPC::do_clean($_POST['store_array'], TYPE_STR));

				$allowsAdditionalOrdering = false;
				foreach ($tempStoreArr as $thisStore)
				{
					if (!empty($thisStore) && is_numeric($thisStore))
					{
						$storeArr[] = $thisStore;

						$allowsAdditionalOrdering = ($allowsAdditionalOrdering == false ? COrderMinimum::allowsAdditionalOrdering($thisStore, $currentMonthMenuID) : $allowsAdditionalOrdering);
					}
				}

				$tpl->assign('showAdditionalOrderRows', $allowsAdditionalOrdering);
				if (!empty($storeArr))
				{

					if (count($storeArr) < 20)
					{
						$storeNames = new DAO();
						$storeNames->query("select 1 as one, GROUP_CONCAT(store_name SEPARATOR '; ') as names from store where id in (" . implode(",", $storeArr) . ") group by one");
						$storeNames->fetch();
						$titleString = str_replace("Custom Store Set", $storeNames->names, $titleString);
					}

					// current month AGR
					$curMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth($currentMonth, 'custom', $storeArr);
					$tpl->assign('curMonthAGRMetrics', $curMonthrollup);

					// next month AGR
					$nextMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'custom', $storeArr);
					$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

					// distant month AGR
					$distMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'custom', $storeArr);
					$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

					$prevMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'custom', $storeArr);
					$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

					// thisMonthLastYear
					$curMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'custom', $storeArr);
					$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

					// nextMonthLastYear
					$nextMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'custom', $storeArr);
					$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

					// distantMonthLastYear
					$distantMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'custom', $storeArr);
					$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

					// current Month guests
					$currentGuestMetrics = CDashboardMenuBased::getRollupGuestNumberdByMonth($currentMonth, 'custom', $storeArr);
					CDashboardMenuBased::addToDateGuestCounts($currentGuestMetrics, 'custom', $curMonthStartDate, $curMonthInterval, $storeArr);

					$occupiedTasteSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'DREAM_TASTE', 'custom', $storeArr);
					$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);
					$occupiedFundraiserSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'FUNDRAISER', 'custom', $storeArr);
					$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);

					$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);

					$didReturnCustomRollup = true;
				}
				else
				{
					$hadError = true;
				}
			}
			else
			{
				$hadError = true;
			}
		}
		else if (!$hadError && $reportType == 'dt_corp_stores')
		{
			$allowsAdditionalOrdering = COrderMinimum::allowsAdditionalOrderingByStoreType(COrderMinimum::CORPORATE_STORE, $currentMonthMenuID);
			$tpl->assign('showAdditionalOrderRows', $allowsAdditionalOrdering);
			$titleString = "Dashboard Report for<br />" . $currentMonthStr . "<br />" . "Corporate Stores";

			// current month AGR
			$curMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth($currentMonth, 'corp_stores');
			$tpl->assign('curMonthAGRMetrics', $curMonthrollup);

			// next month AGR
			$nextMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'corp_stores');
			$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

			// distant month AGR
			$distMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'corp_stores');
			$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

			$prevMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'corp_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'corp_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'corp_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'corp_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

			// current Month guests
			$currentGuestMetrics = CDashboardMenuBased::getRollupGuestNumberdByMonth($currentMonth, 'corp_stores');
			CDashboardMenuBased::addToDateGuestCounts($currentGuestMetrics, 'corp_stores', $curMonthStartDate, $curMonthInterval);

			$occupiedTasteSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'DREAM_TASTE', 'corp_stores');
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);
			$occupiedFundraiserSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'FUNDRAISER', 'corp_stores');
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);

			$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);
		}
		else if (!$hadError && $reportType == 'dt_non_corp_stores')
		{

			$allowsAdditionalOrdering = COrderMinimum::allowsAdditionalOrderingByStoreType(COrderMinimum::NON_CORPORATE_STORE, $currentMonthMenuID);
			$tpl->assign('showAdditionalOrderRows', $allowsAdditionalOrdering);
			$titleString = "Dashboard Report for<br />" . $currentMonthStr . "<br />" . "Franchise Stores";

			// current month AGR
			$curMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth($currentMonth, 'non_corp_stores');
			$tpl->assign('curMonthAGRMetrics', $curMonthrollup);

			// next month AGR
			$nextMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'non_corp_stores');
			$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

			// distant month AGR
			$distMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'non_corp_stores');
			$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

			// previous month AGR
			$prevMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'non_corp_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'non_corp_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'non_corp_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'non_corp_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

			// current Month guests
			$currentGuestMetrics = CDashboardMenuBased::getRollupGuestNumberdByMonth($currentMonth, 'non_corp_stores');
			CDashboardMenuBased::addToDateGuestCounts($currentGuestMetrics, 'non_corp_stores', $curMonthStartDate, $curMonthInterval);
			$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);

			$occupiedTasteSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'DREAM_TASTE', 'non_corp_stores');
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);

			$occupiedFundraiserSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'FUNDRAISER', 'non_corp_stores');
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);
		}
		else if (!$hadError && $reportType == 'dt_all_stores')
		{

			$titleString = "Dashboard Report for<br />" . $currentMonthStr . "<br />" . "All Stores";

			// current month AGR
			$curMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth($currentMonth, 'all_stores');
			$tpl->assign('curMonthAGRMetrics', $curMonthrollup);
			// next month AGR
			$nextMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonth), 'all_stores');
			$tpl->assign('nextMonthAGRMetrics', $nextMonthrollup);

			// distant month AGR
			$distMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonth), 'all_stores');
			$tpl->assign('distMonthAGRMetrics', $distMonthrollup);

			// previous month AGR
			$prevMonthrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $previousMonth), 'all_stores');
			$tpl->assign('prevMonthAGRMetrics', $prevMonthrollup);

			// thisMonthLastYear
			$curMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $curMonthLastYear), 'all_stores');
			$tpl->assign('curMonthLastYearAGRMetrics', $curMonthLastYearrollup);

			// nextMonthLastYear
			$nextMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $nextMonthLastYear), 'all_stores');
			$tpl->assign('nextMonthLastYearAGRMetrics', $nextMonthLastYearrollup);

			// distantMonthLastYear
			$distantMonthLastYearrollup = CDashboardMenuBased::getRollupAGRbyMonth(date("Y-m-01", $distantMonthLastYear), 'all_stores');
			$tpl->assign('distantMonthLastYearAGRMetrics', $distantMonthLastYearrollup);

			// current Month guests
			$currentGuestMetrics = CDashboardMenuBased::getRollupGuestNumberdByMonth($currentMonth, 'all_stores');
			CDashboardMenuBased::addToDateGuestCounts($currentGuestMetrics, 'all_stores', $curMonthStartDate, $curMonthInterval);
			$tpl->assign('curMonthGuestMetrics', $currentGuestMetrics);

			$occupiedTasteSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'DREAM_TASTE', 'all_stores');
			$tpl->assign('occupiedTasteSessionCount', $occupiedTasteSessionCount);

			$occupiedFundraiserSessionCount = CDashboardMenuBased::getOccupiedSessionCountForMonth($curMonthStartDate, $curMonthInterval, 'FUNDRAISER', 'all_stores');
			$tpl->assign('occupiedFundraiserSessionCount', $occupiedFundraiserSessionCount);
		}

		$tpl->assign("titleString", $titleString);

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
			CLog::RecordReport("New Dashboard Report", "Store: $store");

			$myRankingsObj = DAO_CFactory::create('dashboard_metrics_rankings_by_menu');
			$myRankingsObj->date = $currentMonth;
			$myRankingsObj->store_id = $store;
			$myRankingsObj->find(true);

			$tpl->assign('myRankings', $myRankingsObj->toArray());
		}

		$tpl->assign('didReturnCustomRollup', $didReturnCustomRollup);

		$formArray = $Form->render();
		$tpl->assign('store', $store);

		$tpl->assign('form_array', $formArray);
	}

}

?>