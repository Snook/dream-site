<?php
include("includes/CPageAdminOnly.inc");
require_once("includes/CDashboardReportMenuBased.inc");


function timesort($a, $b)
{
	$atime = strtotime($a['date']);
	$btime = strtotime($b['date']);

	if ($atime == $btime)
		return 0;

	return ($atime < $btime) ? -1 : 1;


}

function dollarFormatter($inNumber)
{
	return "$" . number_format($inNumber, 0);

}

function yLabelFormat($inNumber)
{
	return "$" . number_format($inNumber, 0);

}


class page_admin_reports_trending_menu_based extends CPageAdminOnly
{

	private $currentStore = null;
    private $multiStoreOwnerStores = false;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}


	function runEventCoordinator()
	{
	    $this->currentStore = CApp::forceLocationChoice();
		$this->renderChart();
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

		$this->renderChart();
	}

	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();

		$this->renderChart();
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

		$this->renderChart();
	}

	function runHomeOfficeManager()
	{
		$this->renderChart();
	}

	function runSiteAdmin()
	{
		$this->renderChart();
	}


	function grabLastYearGuestCounts($store_id)
	{
		$retVal = array();

		$thisMonth = date("n");
		$thisYear = date("Y");
		$thisMonthTime = mktime(0,0,0, $thisMonth, 1, $thisYear - 1);


		$thisMonthDate = date("Y-m-01", $thisMonthTime);
		$lastYearTime = mktime(0,0,0, $thisMonth-1, 1, $thisYear - 2);
		$lastYear = date("Y-m-01", $lastYearTime);



		$GuestMetrics = DAO_CFactory::create('dashboard_metrics_guests_by_menu');

		$GuestMetrics->query("select dmg.date,
				dmg.store_id,
				dmg.date,
				dmg.guest_count_total
				from dashboard_metrics_guests_by_menu dmg
				where dmg.date > '$lastYear' and dmg.date < '$thisMonthDate' and dmg.store_id = $store_id order by dmg.date asc");

		while ($GuestMetrics->fetch())
		{
			$retVal[] = $GuestMetrics->guest_count_total;
		}

		return $retVal;

	}


	function exportXLSX($tpl)
	{
		$rows = array();

		$_GET['csvfilename'] = str_replace(array(",", "<br />"), array("-", "  "), $tpl->titleString) . " " . CTemplate::dateTimeFormat(date("Y-m-d H:i:s", time()));

		$tpl->assign("labels", array_pad(array("Month", "Adj. Gross Revenue", "Last Year Adj. Gross Revenue", "$ Change", "% Change", "Average Ticket", "Avg. Orders / Session", "Unique Orders", "Unique Guests",
				 "% New to Total Guests", "Ex. Guest Count", "Ex. Guest Sign up %", "New Guest Count", "New Guest Sign up %", "Reacquired Guest Count",
				 "Reacquired Guest Sign up %", "45 Day Lost Guest", "Total Canceled Orders", "Avg. Servings per Guest", "Average Annual Visits"), 18 , ""));


		$colDesc = array("A" => array('width' => 20, 'type' => 'y_axis_labels'),
						"B" => array('type' => 'currency'),
						"C" => array('type' => 'currency'),
						"D" => array('type' => 'currency'),
						"E" => array('type' => 'percent'),
						"F" => array('type' => 'currency'),
						"J" => array('type' => 'percent'),
						"L" => array('type' => 'percent'),
						"N" => array('type' => 'percent'),
						"P" => array('type' => 'percent'));

		$tpl->assign('col_descriptions', $colDesc);



		foreach ($tpl->store_performance_data as $thisRow) {
			$rows[] = array_pad(array(CTemplate::dateTimeFormat($thisRow['date'], VERBOSE_MONTH_YEAR),
									 CTemplate::number_format($thisRow['total_agr'], 2, ""),
					CTemplate::number_format($thisRow['prev_agr'], 2, ""),
					CTemplate::number_format($thisRow['diff'], 2, ""),
					CTemplate::number_format($thisRow['percent_diff'], 4, ""),
					CTemplate::number_format($thisRow['avg_ticket_regular'], 2, ""),
					CTemplate::number_format($thisRow['orders_per_session'], 2, ""),
					$thisRow['orders_count_all'],
					$thisRow['guest_count_total'],
					CTemplate::number_format($thisRow['percent_new'], 4, ""),
					$thisRow['guest_count_existing'],
					(!empty($thisRow['guest_count_existing']) ? CTemplate::number_format(( $thisRow['instore_signup_existing'] / $thisRow['guest_count_existing']), 4, "") : 0),
					$thisRow['guest_count_new'],
					(!empty($thisRow['guest_count_new']) ? CTemplate::number_format(( $thisRow['instore_signup_new'] / $thisRow['guest_count_new']), 4, "") : 0),
					$thisRow['guest_count_reacquired'],
					(!empty($thisRow['guest_count_reacquired']) ? CTemplate::number_format(( $thisRow['instore_signup_reacquired'] / $thisRow['guest_count_reacquired']), 4, "") : 0),
					$thisRow['lost_guests_at_45_days'],
					$thisRow['num_cancelled_orders'],
					CTemplate::number_format($thisRow['avg_servings_per_guest_regular'], 2, ""),
					CTemplate::number_format($thisRow['average_annual_regular_visits'], 2, "")), 17 , "");
		}





		$tpl->assign('rows', $rows);





	}

	function renderChart()
	{

		
		$tpl = CApp::instance()->template();

		$hadError = false;

		$Form = new CForm();
		$Form->Repost = true;

		$AdminUser = CUser::getCurrentUser();
		$userType = $AdminUser->user_type;

		if ($userType == CUser::SITE_ADMIN)
		{
			$tpl->assign('showAllTimeExportLink', true);
		}
		else
		{
			$tpl->assign('showAllTimeExportLink', false);
		}


		$store = null;

		$showStoreSelector = false;
		if ($userType == CUser::HOME_OFFICE_STAFF || $userType == CUser::HOME_OFFICE_MANAGER || $userType == CUser::SITE_ADMIN)
		{
			$showStoreSelector = true;
			$Form->DefaultValues['store'] = array_key_exists('store', $_REQUEST)? $_REQUEST['store'] : '';
			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
					CForm::onChange => 'selectStoreTR',
					CForm::allowAllOption => false,
					CForm::showInactiveStores => false,
					CForm::name => 'store'));
			$store = $Form->value('store');

			$Form->DefaultValues['report_type'] = 'dt_single_store';

			$Form->AddElement(array(CForm::type=> CForm::RadioButton,
					CForm::name => "report_type",
					CForm::value => 'dt_single_store'));

			$Form->AddElement(array(CForm::type=> CForm::RadioButton,
					CForm::name => "report_type",
					CForm::value => 'dt_corp_stores'));

			$Form->AddElement(array(CForm::type=> CForm::RadioButton,
					CForm::name => "report_type",
					CForm::value => 'dt_non_corp_stores'));

			$Form->AddElement(array(CForm::type=> CForm::RadioButton,
					CForm::name => "report_type",
					CForm::value => 'dt_all_stores'));

			$Form->AddElement(array(CForm::type=> CForm::RadioButton,
					CForm::name => "report_type",
					CForm::value => 'dt_stores_by_region'));

			$tradeAreaArr = array(0 => 'Select a Region');
			$tradeAreaObj = DAO_CFactory::create('trade_area');
			$tradeAreaObj->is_active = 1;
			$tradeAreaObj->find();
			while ($tradeAreaObj->fetch())
			{
				$tradeAreaArr[$tradeAreaObj->id] = $tradeAreaObj->region;
			}

			$Form->addElement(array(CForm::type=> CForm::DropDown,
					CForm::allowAllOption => false,
					CForm::name => 'trade_area',
					CForm::options => $tradeAreaArr));



		}
		else if ($this->multiStoreOwnerStores)
        {
            $showStoreSelector = true;

            $Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : '';
            $Form->addElement(array(CForm::type=> CForm::DropDown,
                CForm::onChange => 'selectStore',
                CForm::allowAllOption => false,
                CForm::options => $this->multiStoreOwnerStores,
                CForm::name => 'store'));
            $store = $Form->value('store');

            $Form->DefaultValues['report_type'] = 'dt_single_store';

            $Form->AddElement(array(CForm::type=> CForm::RadioButton,
                CForm::name => "report_type",
                CForm::value => 'dt_single_store'));

            $Form->AddElement(array(CForm::type=> CForm::RadioButton,
                CForm::name => "report_type",
                CForm::value => 'dt_all_stores'));

            $tpl->assign("multiStoreOwnerStores", !empty($this->multiStoreOwnerStores));
        }
		else if ( $this->currentStore )
		{
			$store = $this->currentStore;
		}

		$tpl->assign('showStoreSelector', $showStoreSelector);


		$reportType = $Form->value('report_type');

		$Form->AddElement(array(CForm::type=> CForm::Hidden,
				CForm::name => "store_id",
				CForm::value => $store));


		$is_exporting = false;
		if (isset($_REQUEST['export']) && $_REQUEST['export'] == "xlsx")
		{
			$is_exporting = true;
		}


		if ($reportType == 'dt_single_store' && empty($store))
		{
			$hadError = true;
			$tpl->assign('trending_report_error', 'Please choose a store or change the report type.');
		}

		$titleString = "Menu Month-based Trending Report";
		$tpl->assign('titleString', $titleString);


		if (!$hadError)
		{
			if ($is_exporting)
			{
				CLog::RecordReport("Trending Report Export (export xlsx)", "Store: $store" );
			}
			else
			{
				CLog::RecordReport("Trending Report", "Store: $store" );
			}

			// get current menu
			$Menu = DAO_CFactory::create('menu');
			$Menu->query("SELECT id, menu_start FROM menu WHERE global_menu_end_date >= DATE(now()) AND is_deleted = 0 AND is_active = 1 ORDER BY global_menu_end_date ASC LIMIT 1");
			$Menu->fetch();

			$anchorDate = $Menu->menu_start;
			$anchorDateTS = strtotime($Menu->menu_start);
			$menu_id = $Menu->id;


			if (empty($reportType) || $reportType == 'dt_single_store')
			{
				$currentMonthStr = date("M. Y", $anchorDateTS);

				$storeInfo = DAO_CFactory::create('store');
				$storeInfo->query("select store_name, city, state_id from store where id = $store" );
				$storeInfo->fetch();
				$titleString = "$currentMonthStr Menu Month-based Trending Report for <br />" . $storeInfo->store_name . " " . $storeInfo->city . ", " . $storeInfo->state_id;
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CDashboardMenuBased::getAGRTrendingDataForStore($store, $anchorDate, $is_exporting);
				CDashboardMenuBased::addGuestTrendingDataForStore($store, $StorePerformanceData, $anchorDate, $is_exporting);
				CDashboardMenuBased::addcancelledOrdersForStore($store, $StorePerformanceData, $menu_id, $is_exporting);

				$rollups = CDashboardMenuBased::getAGRTrendingDataRollup($store, $anchorDate);
				CDashboardMenuBased::addGuestTrendingDataRollups($store, $anchorDate, $rollups);
				CDashboardMenuBased::addcancelledOrdersRollups($store, $menu_id, $rollups, $StorePerformanceData);


				$tpl->assign('store_performance_data', $StorePerformanceData);

				$tpl->assign('rollups', $rollups);
				$tpl->assign('curReportType', 'single_store');

				uasort($StorePerformanceData, 'timesort');

			}
			else if ($reportType == 'dt_corp_stores')
			{
				$currentMonthStr = date("M. Y", $anchorDateTS);

				$titleString = "$currentMonthStr Menu Month-based Trending Report for <br /> Corporate Stores";
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CDashboardMenuBased::getAGRTrendingDataHomeOfficeRollup('corp_stores', $anchorDate, 0, $is_exporting );
				CDashboardMenuBased::addGuestTrendingHomeofficeRollups($StorePerformanceData, $anchorDate, 'corp_stores', 0, $is_exporting );
				CDashboardMenuBased::addCancelledOrders($StorePerformanceData, $menu_id, 'corp_stores', 0, $is_exporting);


				$rollups = CDashboardMenuBased::getAGRTrendingDataRollup(false, $anchorDate, 'corp_stores', 0, $is_exporting);
				CDashboardMenuBased::addGuestTrendingDataRollups(false, $anchorDate, $rollups, 'corp_stores', 0, $is_exporting);
				CDashboardMenuBased::addcancelledOrdersRollups($store, $menu_id, $rollups, null, 'corp_stores', $is_exporting);


				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'corp_stores');

				$tpl->assign('rollups', $rollups);

				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'corp_stores';

			}
			else if ($reportType == 'dt_non_corp_stores')
			{
				$currentMonthStr = date("M. Y", $anchorDateTS);

				$titleString = "$currentMonthStr Menu Month-based Trending Report for <br /> Franchise Stores";
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CDashboardMenuBased::getAGRTrendingDataHomeOfficeRollup('non_corp_stores', $anchorDate, 0, $is_exporting);
				CDashboardMenuBased::addGuestTrendingHomeofficeRollups($StorePerformanceData, $anchorDate, 'non_corp_stores', 0, $is_exporting );
				CDashboardMenuBased::addCancelledOrders($StorePerformanceData, $menu_id, 'non_corp_stores', 0, $is_exporting);


				$rollups = CDashboardMenuBased::getAGRTrendingDataRollup(false, $anchorDate, 'non_corp_stores', 0, $is_exporting);
				CDashboardMenuBased::addGuestTrendingDataRollups(false, $anchorDate, $rollups, 'non_corp_stores', 0, $is_exporting);
				CDashboardMenuBased::addcancelledOrdersRollups($store, $menu_id, $rollups, null, 'non_corp_stores', $is_exporting);


				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'non_corp_stores');

				$tpl->assign('rollups', $rollups);

				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'non_corp_stores';


			}
			else if ($reportType == 'dt_all_stores')
			{
				$currentMonthStr = date("M. Y", $anchorDateTS);

				$titleString = "$currentMonthStr Menu Month-based Trending Report for <br /> All Stores";

				if ($this->multiStoreOwnerStores)
                {
                    $titleString = "$currentMonthStr Menu Month-based Trending Report for <br />" . implode(", ", $this->multiStoreOwnerStores);
                    $storeList = implode(",", array_keys($this->multiStoreOwnerStores));

                    $StorePerformanceData = CDashboardMenuBased::getAGRTrendingDataHomeOfficeRollup('custom', $anchorDate, 0, $is_exporting, $storeList);
                    CDashboardMenuBased::addGuestTrendingHomeofficeRollups($StorePerformanceData, $anchorDate, 'custom', 0, $is_exporting, $storeList);
                    CDashboardMenuBased::addCancelledOrders($StorePerformanceData, $menu_id, 'custom', 0, $is_exporting, $storeList);


                    $rollups = CDashboardMenuBased::getAGRTrendingDataRollup(false, $anchorDate, 'custom', 0, $is_exporting, $storeList);
                    CDashboardMenuBased::addGuestTrendingDataRollups(false, $anchorDate, $rollups, 'custom', 0, $is_exporting, $storeList);
                    CDashboardMenuBased::addcancelledOrdersRollups($store, $menu_id, $rollups, null, 'custom', $is_exporting);
                }
				else
                {
                    $StorePerformanceData = CDashboardMenuBased::getAGRTrendingDataHomeOfficeRollup('all_stores', $anchorDate, 0, $is_exporting);
                    CDashboardMenuBased::addGuestTrendingHomeofficeRollups($StorePerformanceData, $anchorDate, 'all_stores', 0, $is_exporting);
                    CDashboardMenuBased::addCancelledOrders($StorePerformanceData, $menu_id, 'all_stores', 0, $is_exporting);


                    $rollups = CDashboardMenuBased::getAGRTrendingDataRollup(false, $anchorDate, 'all_stores', 0, $is_exporting);
                    CDashboardMenuBased::addGuestTrendingDataRollups(false, $anchorDate, $rollups, 'all_stores', 0, $is_exporting);
                    CDashboardMenuBased::addcancelledOrdersRollups($store, $menu_id, $rollups, null, 'all_stores', $is_exporting);
                }
				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'all_stores');

				$tpl->assign('rollups', $rollups);

				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'all_stores';
                $tpl->assign('titleString', $titleString);


			}
			else if ($reportType == 'dt_stores_by_region')
			{

				$trade_area_id = $Form->value('trade_area');


				$regionName = $tradeAreaArr[$trade_area_id];

				$currentMonthStr = date("M. Y", $anchorDateTS);

				$titleString = "$currentMonthStr Menu Month-based Trending Report for <br /> Stores the $regionName Region";
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CDashboardMenuBased::getAGRTrendingDataHomeOfficeRollup('region', $anchorDate, $trade_area_id, $is_exporting);
				CDashboardMenuBased::addGuestTrendingHomeofficeRollups($StorePerformanceData, $anchorDate, 'region', $trade_area_id, $is_exporting);
				CDashboardMenuBased::addCancelledOrders($StorePerformanceData, $menu_id, 'region', $trade_area_id, $is_exporting);



				$rollups = CDashboardMenuBased::getAGRTrendingDataRollup(false, $anchorDate, 'region', $trade_area_id, $is_exporting);
				CDashboardMenuBased::addGuestTrendingDataRollups(false, $anchorDate, $rollups, 'region', $trade_area_id, $is_exporting);
				CDashboardMenuBased::addcancelledOrdersRollups($store, $menu_id, $rollups,  null, 'region', $is_exporting);


				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'region');

				$tpl->assign('rollups', $rollups);

				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'region';

			}




		if ($is_exporting)
		{

			$this->exportXLSX($tpl);
			return;
		}


		foreach($StorePerformanceData as $date => $data)
		{
			$months[] = date("M y", strtotime($date));
			$mags[] = $data['total_agr'];
			$prevmags[] = $data['prev_agr'];
			$guestCounts[] = $data['guest_count_total'];
			$prevGuestCount[] = $data['guest_count_new'];
		}

		//$prevGuestCount = $this->grabLastYearGuestCounts($store);

//------------------------------------------------------------- AGR graph
		$hasGD = false;
		if (function_exists('imagetypes'))
			$hasGD = true;

		$tpl->assign('hasGD',$hasGD);

		if ($hasGD)
		{


			try {
				require_once ('jpgraph/jpgraph.php');
				require_once ('jpgraph/jpgraph_line.php');
				require_once( 'jpgraph/jpgraph_utils.inc.php');


// caclulate trendline
				for ($i = 1; $i <= count($months); $i++)
				{
					$datax[] = $i;
				}

		//		$datax = array(1,2,3,4,5,6,7,8,9,10,11,12);
				// Instantiate the linear regression class
				$linreg = new LinearRegression($datax, $mags);
				// Get the basic statistics
				list( $stderr, $corr ) = $linreg->GetStat();
				// Get a set of estimated y-value for x-values in range [0,20]
				list($xd, $yd) = $linreg->GetY(1,12);

				$graph = new Graph(1000,250);
				$graph->SetScale("textlin");

				$theme_class=new DreamDinnersTheme;

				$graph->SetTheme($theme_class);
				$graph->img->SetAntiAliasing(true);
				$graph->title->Set('Adjusted Gross Revenue');
				$graph->SetBox(false);

				$graph->img->SetAntiAliasing();

				$graph->yaxis->HideZeroLabel();
				$graph->yaxis->HideLine(false);
				$graph->yaxis->HideTicks(false,false);

				$graph->xgrid->Show();
				$graph->xgrid->SetLineStyle("solid");
				$graph->xaxis->SetTickLabels($months);
				$graph->xgrid->SetColor('#E3E3E3');


				$graph->yaxis->SetLabelFormatCallback('yLabelFormat');

			//	$graph->xaxis->title->Set('X Axis');
			//	$graph->yaxis->title->Set('Y Axis');

				//$graph->SetBackgroundGradient('silver','green', 2, GRAD_PLOT);
				/* $graph->SetBackgroundImage("tiger_bkg.png",BGIMG_FILLPLOT); */

				// Create the first line
				$p1 = new LinePlot($mags);
				$graph->Add($p1);
				$p1->SetColor("#008800");
				$p1->SetLegend('Current Year');
				//$p1->SetFillColor("#6495ED");
				$p1->value->Show();
				///$p1->value->SetFormat("%d");
				$p1->value->SetFormatCallback('dollarFormatter');
				$p1->value->SetColor('#008800');


				// Create the second line
				$p2 = new LinePlot($prevmags);
				$graph->Add($p2);
				$p2->SetColor("#B22222");
				$p2->SetLegend('Previous Year');
				//$p2->SetFillColor("#B22222");
				$p2->value->Show();
				$p2->value->SetFormatCallback('dollarFormatter');
				$p2->value->SetColor('#B22222');


				$graph->legend->SetFrameWeight(1);
				$graph->SetMarginColor(array(222,214,203));
				//$graph->SetMarginColor('#DED6CB');

				//add trendline

				// Create the regression line
				$lplot = new LinePlot($yd);

				// Add the pltos to the line
				$graph->Add($lplot);
				$lplot->SetWeight(2);
				$lplot->SetColor("blue");

				// Output line
				$gdImgHandler = $graph->Stroke(_IMG_HANDLER);


				// create file name
				$agrName = "AGR_" . $store;
				$agrName = md5($agrName) . ".png";
				$tpl->assign("agr_image_path", IMAGES_PATH . "/charts/agr/" . $agrName);
				$agrPath = APP_BASE . "www/theme/" . THEME . "/images/charts/agr/" . $agrName;

				$graph->img->Stream($agrPath);

			} catch (Exception $e) {
				CLog::RecordNew(CLog::DEBUG, $e->getMessage(), "", "", true );
			}

		//------------------------------------------------------------------------Guest Count Graph
				try {
					// Setup the graph
					$graph2 = new Graph(1000,250);
					$graph2->SetScale("textlin");

					$theme_class2=new DreamDinnersTheme;

					$graph2->SetTheme($theme_class2);
					$graph2->img->SetAntiAliasing(true);
					$graph2->title->Set('Unique Guests');
					$graph2->SetBox(false);

					$graph2->img->SetAntiAliasing();

					$graph2->yaxis->HideZeroLabel();
					$graph2->yaxis->HideLine(false);
					$graph2->yaxis->HideTicks(false,false);
					$graph2->yaxis->SetColor('#008800');
					$graph2->yaxis->SetTitle("Unique Guests");
					$graph2->yaxis->SetTitleMargin(35);

					$graph2->xgrid->Show();
					$graph2->xgrid->SetLineStyle("solid");
					$graph2->xaxis->SetTickLabels($months);

					$graph2->xgrid->SetColor('#E3E3E3');


					// Create the first line
					$p3 = new LinePlot($guestCounts);
					$graph2->Add($p3);
					$p3->SetColor("#008800");
					$p3->SetLegend('Unique Guest Count');
					//$p1->SetFillColor("#6495ED");
					$p3->value->Show();
					$p3->value->SetFormat('%d');
					$p3->value->SetColor('#008800');

	/*
					$graph2->SetYScale(0,'lin');

					$graph2->ynaxis[0]->HideZeroLabel();
					$graph2->ynaxis[0]->HideLine(false);
					$graph2->ynaxis[0]->HideTicks(false,false);
					$graph2->ynaxis[0]->SetColor('#B22222');
					$graph2->ynaxis[0]->SetTitle("New Guests");
					$graph2->ynaxis[0]->SetTitleMargin(35);
					$graph2->ynaxis[0]->title->SetColor('#B22222');
	*/

					$graph2->SetY2Scale('lin');

					$graph2->y2axis->HideZeroLabel();
					$graph2->y2axis->HideLine(false);
					$graph2->y2axis->HideTicks(false,false);
					$graph2->y2axis->SetColor('#B22222');
					$graph2->y2axis->SetTitle("New Guests");
					$graph2->y2axis->SetTitleMargin(35);
					$graph2->y2axis->title->SetColor('#B22222');



					// Create the second line
					$p4 = new LinePlot($prevGuestCount);
					$graph2->AddY2($p4);
					$p4->SetColor("#B22222");
					$p4->SetLegend('New Guest Count');
					//$p2->SetFillColor("#B22222");
					$p4->value->Show();
					$p4->value->SetFormat('%d');
					$p4->value->SetColor('#B22222');

					$graph2->legend->SetFrameWeight(2);
					$graph2->SetMarginColor(array(222,214,203));
					//$graph2->SetMarginColor('#DED6CB');

					// Output line
					$gdImgHandler = $graph2->Stroke(_IMG_HANDLER);

					// Stroke image to a file and browser

					// create file name
					$guestName = "GUEST_" . $store;
					$guestName = md5($guestName) . ".png";
					$tpl->assign("guest_image_path", IMAGES_PATH . "/charts/guests/" . $guestName);
					$guestPath = APP_BASE . "www/theme/" . THEME . "/images/charts/guests/" . $guestName;

					$graph2->img->Stream($guestPath);

				} catch (Exception $e) {
					CLog::RecordNew(CLog::DEBUG, $e->getMessage(), "", "", true );
				}
			}
			else
			{
				$tpl->assign("guest_image_path", IMAGES_PATH . "/charts/guests/guests_placeholder.png");
				$tpl->assign("agr_image_path", IMAGES_PATH . "/charts/agr/agr_placeholder.png");

			}


		}

		$formArray = $Form->render();
		$tpl->assign('store', $store);

		$tpl->assign('form_array', $formArray);


	}
}
?>