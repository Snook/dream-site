<?php
include("includes/CPageAdminOnly.inc");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');

require_once("includes/CTrendingReportNew.inc");


function timesort($a, $b)
{
	$atime = strtotime($a['date']);
	$btime = strtotime($b['date']);

	if ($atime == $btime)
		return 0;

	return ($atime < $btime) ? -1 : 1;


}

function trendingRowCallback($sheet, $data, $row, $bottomRightExtent)
{

	if ($row == 18)
	{
		$styleArray = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array('argb' => 'FFFFE79F')
			),
			'font' => array( 'bold' => true)
		);
		
		$sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
	}
}

class page_admin_reports_trending_new extends CPageAdminOnly
{

	private $currentStore = null;
	private $isSingleStoreView = false;
	private $includeInactiveStores = false;
	private $useCalendarMonth = false;
    private $isOwnerView = false;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}


	function runHomeOfficeManager()
	{
		$this->run();
	}

	function runSiteAdmin()
	{
		$this->run();
	}

    function runFranchiseOwner()
    {
        $this->isOwnerView = true;
        $this->currentStore = CApp::forceLocationChoice();
        $this->run();
    }


    function exportXLSX($tpl, $title)
	{
		$rows = array();
		
		
		if ($this->isSingleStoreView)
		{
			$sectionHeader = array(" " => 1,
									 "Sales" => 6,
									"Guest Count" => 2,
									"Orders" => 4,
									Servings => 2,
									"In-Store Sign Ups / Conversion Rate" => 5);
				
			
			$labels = array(// "# of Open Stores",
							"Month",
							"Total Sales (AGR)",
							// "Average Sales/Store",
							"Same Store Sales % Inc/Decr over Last Year",
							"Total Sides & Sweets Sales",
							"S & S as % of Total Sales",
							//	"Avg. S & S Sales/Store",
							"Avg. Ticket (All Orders)",
							"Avg. Ticket (Excludes Tastes/Intros)",
							"Total Unique Guests",
							//"Avg. Guests/Store",
							"% +/- Previous Month",
							"Total Unique Orders",
							"MFY as % of Total Orders",
							"Canceled Orders",
							"Avg. Orders/Session",
							"Total Servings",
							"Average Servings per Guest",
							"New Guests %",
							"Reacquired Guests %",
							"Existing Guests %",
							"All Guests %",
							"Conv. Rate");
		}
		else 
		{
			
			$sectionHeader = array(" " => 2,
				"Sales" => 8,
				"Guest Count" => 3,
				"Orders" => 4,
				Servings => 2,
				"In-Store Sign Ups / Conversion Rate" => 5);
					
			
			$labels = array("# of Open Stores",
						"Month",
						"Total Sales (AGR)",
						"Average Sales/Store",
						"Same Store Sales % Inc/Decr over Last Year",
						"Total Sides & Sweets Sales",
						"S & S as % of Total Sales",
						"Avg. S & S Sales/Store",
						"Avg. Ticket (All Orders)",
						"Avg. Ticket (Excludes Tastes/Intros)",
						"Total Unique Guests",
						"Avg. Guests/Store",
						"% +/- Previous Month",
						"Total Unique Orders",
						"MFY as % of Total Orders",
						"Canceled Orders",
						"Avg. Orders/Session",
						"Total Servings",
						"Average Servings per Guest",
						"New Guests %",
						"Reacquired Guests %",
						"Existing Guests %",
						"All Guests %",
						"Conv. Rate");
				
		}
		

		$tpl->assign("labels", $labels);

		$col = 'A';
		$colSecondChar = '';
		$thirdSecondChar = '';
		$colDesc = array();
		
		if (!$this->isSingleStoreView)
		{
			// number stores
			$columnDescs[$col] = array('align' => 'center', 'width' => '10');
			incrementColumn($thirdSecondChar, $colSecondChar, $col);
		}
		
		// month
		$columnDescs[$col] = array('align' => 'center', 'width' => 'auto', 'type' => 'datetime');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		// total AGR
		$columnDescs[$col] = array('align' => 'center', 'width' => 'auto', 'type' => 'currency', 'decor' => 'left_border');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		if (!$this->isSingleStoreView)
		{
			// average per store AGR
			$columnDescs[$col] = array('align' => 'center', 'width' => '12', 'type' => 'currency');
			incrementColumn($thirdSecondChar, $colSecondChar, $col);
		}
		
		// percent change
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		// sides and sweet sales
		$columnDescs[$col] = array('align' => 'center', 'width' => '12', 'type' => 'currency', 'decor' => 'left_border');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		// percent S & S of total AGR
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		
		if (!$this->isSingleStoreView)
		{
			// sides and sweets sales per store AGR
			$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'currency');
			incrementColumn($thirdSecondChar, $colSecondChar, $col);
		}
		
		// Average ticket
		$columnDescs[$col] = array('align' => 'center', 'width' => '12', 'type' => 'currency', 'decor' => 'left_border');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		// Average ticket excluding taste and intro
		$columnDescs[$col] = array('align' => 'center', 'width' => '12', 'type' => 'currency');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		// Unique guests
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'number_w_parens', 'decor' => 'left_border');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		if (!$this->isSingleStoreView)
		{
			// Average Guest per store
			$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => ' number');
			incrementColumn($thirdSecondChar, $colSecondChar, $col);
		}
		
		// percent change of guest count
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		// Order count
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'number_w_parens', 'decor' => 'left_border');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// percent MFY of orders
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// Cancelled Orders
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'number');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// Average orders per session
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'number_x');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// Total servings
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'number_w_parens', 'decor' => 'left_border');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// Average servings per guest
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'number');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		// new guest in-store rate
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent_as_int', 'decor' => 'left_border');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// Reacquired guest in-store rate
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent_as_int');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// existing guest in-store rate
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent_as_int');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// all guest in-store rate
		$columnDescs[$col] = array('align' => 'center', 'width' => '10', 'type' => 'percent_as_int');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		// Converion Rate
		$columnDescs[$col] = array('align' => 'center', 'width' => 'auto', 'type' => 'percent_as_int');
		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		
		
		$tpl->assign('col_descriptions', $columnDescs);
		$tpl->assign('sectionHeader', $sectionHeader);
		

		foreach ($tpl->store_performance_data as $thisRow) 
		{
			if ($this->isSingleStoreView)
			{

				$rows[] = array(
					CTemplate::dateTimeFormat($thisRow['date'], CONDENSED_MONTH_YEAR),
					CTemplate::number_format($thisRow['total_agr'], 2, ""),
					CTemplate::number_format($thisRow['percent_diff'], 4, ""),
					CTemplate::number_format($thisRow['addon_sales_total'], 2, ""),
					CTemplate::number_format($thisRow['percent_addon_of_total'], 2, ""),
					CTemplate::number_format($thisRow['avg_ticket_all'], 2, ""),
					CTemplate::number_format($thisRow['avg_ticket_regular'], 2, ""),
					$thisRow['guest_count_total'],
					CTemplate::divide_and_format($thisRow['prev_guest_count_total'] - $thisRow['guest_count_total'], $thisRow['prev_guest_count_total'], 2),
					$thisRow['orders_count_all'],
					CTemplate::number_format($thisRow['MFY_percent_of_total'], 4, ""),
					$thisRow['num_cancelled_orders'],
					CTemplate::number_format($thisRow['guests_per_session'], 1, ""),
					CTemplate::number_format($thisRow['total_servings_sold'], 0, ""),
					CTemplate::number_format($thisRow['avg_servings_per_guest'], 1, ""),
					
					CTemplate::divide_and_format($thisRow['instore_signup_new'], $thisRow['guest_count_new'], 2),
					CTemplate::divide_and_format($thisRow['instore_signup_reacquired'], $thisRow['guest_count_reacquired'], 2),
					CTemplate::divide_and_format($thisRow['instore_signup_existing'], $thisRow['guest_count_existing'], 2),
					CTemplate::divide_and_format($thisRow['instore_signup_total'], $thisRow['guest_count_total'], 2),
					CTemplate::number_format($thisRow['conversion_rate'] / 100, 2, ""));
						
					
			}
			else
			{
				$rows[] = array(
					CTemplate::number_format($thisRow['num_stores'], 0, ""),
					CTemplate::dateTimeFormat($thisRow['date'], CONDENSED_MONTH_YEAR),
					CTemplate::number_format($thisRow['total_agr'], 2, ""),
					CTemplate::number_format($thisRow['total_agr'] / $thisRow['num_stores'], 4, ""),
					CTemplate::number_format($thisRow['percent_diff'], 4, ""),
					CTemplate::number_format($thisRow['addon_sales_total'], 2, ""),
					CTemplate::number_format($thisRow['percent_addon_of_total'], 2, ""),
					CTemplate::number_format($thisRow['addon_sales_total'] / $thisRow['num_stores'], 4, ""),
					CTemplate::number_format($thisRow['avg_ticket_all'], 2, ""),
					CTemplate::number_format($thisRow['avg_ticket_regular'], 2, ""),
					$thisRow['guest_count_total'],
					CTemplate::divide_and_format($thisRow['guest_count_total'], $thisRow['num_stores'], 0),
					CTemplate::divide_and_format($thisRow['prev_guest_count_total'] - $thisRow['guest_count_total'], $thisRow['prev_guest_count_total'], 2),
					$thisRow['orders_count_all'],
					CTemplate::number_format($thisRow['MFY_percent_of_total'], 4, ""),
					$thisRow['num_cancelled_orders'],
					CTemplate::number_format($thisRow['guests_per_session'], 1, ""),
					CTemplate::number_format($thisRow['total_servings_sold'], 0, ""),
					CTemplate::number_format($thisRow['avg_servings_per_guest'], 1, ""),
					CTemplate::divide_and_format($thisRow['instore_signup_new'], $thisRow['guest_count_new'], 2),
					CTemplate::divide_and_format($thisRow['instore_signup_reacquired'], $thisRow['guest_count_reacquired'], 2),
					CTemplate::divide_and_format($thisRow['instore_signup_existing'], $thisRow['guest_count_existing'], 2),
					CTemplate::divide_and_format($thisRow['instore_signup_total'], $thisRow['guest_count_total'], 2),
					CTemplate::number_format($thisRow['conversion_rate'] / 100, 2, ""));
				
			}
		}
		
		
		if ($this->isSingleStoreView)
		{
			$avgRow = 4 + count($rows);

			$rows[] = array("13 Mo. Avg",
				"=AVERAGE(B5:B$avgRow)",
				"=AVERAGE(C5:C$avgRow)",
				"=AVERAGE(D5:C$avgRow)",
				"=AVERAGE(E5:E$avgRow)",
				"=AVERAGE(F5:F$avgRow)",
				"=AVERAGE(G5:G$avgRow)",
				"=AVERAGE(H5:H$avgRow)",
				"=AVERAGE(I5:I$avgRow)",
				"=AVERAGE(J5:J$avgRow)",
				"=AVERAGE(K5:K$avgRow)",
				"=AVERAGE(L5:L$avgRow)",
				"=AVERAGE(M5:M$avgRow)",
				"=AVERAGE(N5:N$avgRow)",
				"=AVERAGE(O5:O$avgRow)",
				"=AVERAGE(P5:P$avgRow)",
				"=AVERAGE(Q5:Q$avgRow)",
				"=AVERAGE(R5:R$avgRow)",
				"=AVERAGE(S5:S$avgRow)",
				"=AVERAGE(T5:T$avgRow)");
			}
		else
		{

			$rows[] = array("13 Mo. Avg",
				"",
				"=AVERAGE(C5:C17)",
				"=AVERAGE(D5:D17)",
				"=AVERAGE(E5:E17)",
				"=AVERAGE(F5:F17)",
				"=AVERAGE(G5:G17)",
				"=AVERAGE(H5:H17)",
				"=AVERAGE(I5:I17)",
				"=AVERAGE(J5:J17)",
				"=AVERAGE(K5:K17)",
				"=ROUND(AVERAGE(L5:L17),0)",
				"=AVERAGE(M5:M17)",
				"=AVERAGE(N5:N17)",
				"=AVERAGE(O5:O17)",
				"=AVERAGE(P5:P17)",
				"=AVERAGE(Q5:Q17)",
				"=AVERAGE(R5:R17)",
				"=AVERAGE(S5:S17)",
				"=AVERAGE(T5:T17)",
				"=AVERAGE(U5:U17)",
				"=AVERAGE(V5:V17)",
				"=AVERAGE(W5:W17)",
				"=AVERAGE(X5:X17)");
		}
		
		$callbacks = array('row_callback' => 'trendingRowCallback');
		$tpl->assign('excel_callbacks', $callbacks);
		
		
		$titleRows[] = array("", $title);
		$titleRows[] = array("", "Report Run On", date("F j, Y, g:i:a"));
		
		$tpl->assign('title_rows', $titleRows);
		

		$tpl->assign('rows', $rows);

	}

	function run()
	{

		CApp::forceSecureConnection();
		$tpl = CApp::instance()->template();

		$hadError = false;

		$Form = new CForm();
		$Form->Repost = true;

		$AdminUser = CUser::getCurrentUser();
		$userType = $AdminUser->user_type;

		$store = null;

		$showStoreSelector = false;
		if ($userType == CUser::HOME_OFFICE_STAFF || $userType == CUser::HOME_OFFICE_MANAGER || $userType == CUser::SITE_ADMIN)
		{
			$showStoreSelector = true;
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : '';
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
			
			$Form->AddElement(array(CForm::type=> CForm::CheckBox,
				CForm::name => "select_inactive_stores"));
			
			$Form->AddElement(array(CForm::type=> CForm::CheckBox,
				CForm::name => "use_cal_month"));
				
				
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
		else if ( $this->currentStore )
		{

            $Form->AddElement(array(CForm::type=> CForm::CheckBox,
                CForm::name => "use_cal_month"));

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

		$titleString = "Rolling 13-Month Business Analysis Report";
		$tpl->assign('titleString', $titleString);

		
		if (isset($_POST['select_inactive_stores']))
		{
			$this->includeInactiveStores = true;
		}
		
		if (isset($_POST['use_cal_month']))
		{
			$this->useCalendarMonth = true;
		}
		

		if (!$hadError)
		{
			if ($is_exporting)
				CLog::RecordReport("Rolling 13-Month Business Analysis Report Export (export xlsx)", "Store: $store" );
			else
				CLog::RecordReport("Rolling 13-Month Business Analysis Report", "Store: $store" );

			if ($this->isOwnerView || $reportType == 'dt_single_store')
			{
				
				$this->isSingleStoreView = true;
				
				$storeInfo = DAO_CFactory::create('store');
				$storeInfo->query("select store_name, city, state_id from store where id = $store" );
				$storeInfo->fetch();
				
				if ($this->useCalendarMonth)
				{
					$titleString = "Calendar Month-based Rolling 13-Month Business Analysis Report for <br />" . $storeInfo->store_name . " " . $storeInfo->city . ", " . $storeInfo->state_id;
				}
				else 
				{
					$titleString = "Menu Month-based Rolling 13-Month Business Analysis Report for <br />" . $storeInfo->store_name . " " . $storeInfo->city . ", " . $storeInfo->state_id;
				}
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CTrendingReportNew::getAGRTrendingDataForStore($store, $is_exporting, $this->useCalendarMonth);
				CTrendingReportNew::addGuestTrendingDataForStore($store, $StorePerformanceData, $is_exporting, $this->useCalendarMonth);
				CTrendingReportNew::addcancelledOrdersForStore($store, $StorePerformanceData, $is_exporting, $this->useCalendarMonth);

				$tpl->assign('store_performance_data', $StorePerformanceData);

				//$tpl->assign('rollups', $rollups);
				$tpl->assign('curReportType', 'single_store');

				uasort($StorePerformanceData, 'timesort');

			}
			else if ($reportType == 'dt_corp_stores')
			{
				
				if ($this->useCalendarMonth)
				{
					$titleString = "Calendar Month-based Rolling 13-Month Business Analysis Report for Corporate Stores";
				}
				else
				{
					$titleString = "Menu Month-based Rolling 13-Month Business Analysis Report for Corporate Stores";
				}
				
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CTrendingReportNew::getAGRTrendingDataHomeOfficeRollup('corp_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth );
				CTrendingReportNew::addGuestTrendingHomeofficeRollups($StorePerformanceData, 'corp_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth );
				CTrendingReportNew::addCancelledOrders($StorePerformanceData, 'corp_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth);

				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'corp_stores');

				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'corp_stores';

			}
			else if ($reportType == 'dt_non_corp_stores')
			{
				if ($this->useCalendarMonth)
				{
					$titleString = "Calendar Month-based Rolling 13-Month Business Analysis Report for Franchise Stores";
				}
				else
				{
					$titleString = "Menu Month-based Rolling 13-Month Business Analysis Report for Franchise Stores";
				}
				
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CTrendingReportNew::getAGRTrendingDataHomeOfficeRollup('non_corp_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth);
				CTrendingReportNew::addGuestTrendingHomeofficeRollups($StorePerformanceData, 'non_corp_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth);
				CTrendingReportNew::addCancelledOrders($StorePerformanceData, 'non_corp_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth);

				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'non_corp_stores');

				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'non_corp_stores';


			}
			else if ($reportType == 'dt_all_stores')
			{
				if ($this->useCalendarMonth)
				{
					$titleString = "Calendar Month-based Rolling 13-Month Business Analysis Report for All Stores";
				}
				else
				{
					$titleString = "Menu Month-based Rolling 13-Month Business Analysis Report for All Stores";
				}
				
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CTrendingReportNew::getAGRTrendingDataHomeOfficeRollup('all_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth);
				CTrendingReportNew::addGuestTrendingHomeofficeRollups($StorePerformanceData, 'all_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth);
				CTrendingReportNew::addCancelledOrders($StorePerformanceData, 'all_stores', 0, $this->includeInactiveStores, $this->useCalendarMonth);

				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'all_stores');


				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'all_stores';


			}
			else if ($reportType == 'dt_stores_by_region')
			{

				$trade_area_id = $Form->value('trade_area');


				$regionName = $tradeAreaArr[$trade_area_id];

				if ($this->useCalendarMonth)
				{
					$titleString = "Calendar Month-based Rolling 13-Month Business Analysis Report for Stores the $regionName Region";
				}
				else
				{
					$titleString = "Menu Month-based Rolling 13-Month Business Analysis Report for Stores the $regionName Region";
				}
				
				$tpl->assign('titleString', $titleString);

				$StorePerformanceData =  CTrendingReportNew::getAGRTrendingDataHomeOfficeRollup('region', $trade_area_id, $this->includeInactiveStores, $this->useCalendarMonth);
				CTrendingReportNew::addGuestTrendingHomeofficeRollups($StorePerformanceData, 'region', $trade_area_id, $this->includeInactiveStores, $this->useCalendarMonth);
				CTrendingReportNew::addCancelledOrders($StorePerformanceData, 'region', $trade_area_id, $this->includeInactiveStores, $this->useCalendarMonth);

				$tpl->assign('store_performance_data', $StorePerformanceData);
				$tpl->assign('curReportType', 'region');

				uasort($StorePerformanceData, 'timesort');

				// for hashing the image name
				$store = 'region';

			}


			if ($is_exporting)
			{
				$this->exportXLSX($tpl, $titleString);
				return;
			}
		}

		$tpl->assign('store', $store);
		
		$formArray = $Form->render();
		$tpl->assign('form_array', $formArray);


	}
}
?>