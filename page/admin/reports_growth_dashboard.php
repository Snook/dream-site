<?php
include("includes/CPageAdminOnly.inc");
require_once("includes/CDashboardReportMenuBased.inc");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');



class Common_Style_Arrays
{

    static $ThickBlackBorderComplete =
        array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000')),
            'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
            'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
            'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000'))));



}


function ReportCellCallback($sheet, $colName, $datum, $col, $row)
{

    $styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FFAAAAAA')),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FFAAAAAA'))));

    $sheet->getStyle("$col$row")->applyFromArray($styleArray);

}


function ReportFinalRenderCallback($sheet, $rows)
{

    $sheet->getColumnDimension("A")->setWidth(20);
    $sheet->getColumnDimension("B")->setWidth(20);
    $sheet->getColumnDimension("C")->setWidth(20);
    $sheet->getColumnDimension("D")->setWidth(20);
    $sheet->getColumnDimension("E")->setWidth(20);
    $sheet->getColumnDimension("F")->setWidth(20);
    $sheet->getColumnDimension("G")->setWidth(20);

    $sheet->getStyle("A1:G31")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet->getRowDimension(1)->setRowHeight(40);

	$sheet->getStyle('A1:G31')->getAlignment()->setWrapText(true);

    $styleArray = array(
        'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000')),
            'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000') ),
            'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000') ),
            'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000') )));
    $sheet->getStyle("A1:G31")->applyFromArray($styleArray);

}

function ReportRowCallback($sheet, $data, $row, $bottomRightExtent)
{

    if ($row == 1)
    {

        $styleArray = array('font' => array('bold' => true, 'size' => 20),
            'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000'),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000')))));

        $sheet->getStyle("A$row")->applyFromArray($styleArray);
    }
    else if ($row == 2 || $row == 7)
    {
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16 ),
            'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000')),
                'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
                'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
                'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000'))),
            'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => 'FFCCD490', ),
                            'endcolor' => array( 'argb' => 'FFA8B355')));

        $sheet->getStyle("A$row:G$row")->applyFromArray($styleArray);
    }
    else if ($row == 3 || $row == 9 || $row == 13 || $row == 17 || $row == 21 || $row == 25 || $row == 29)
    {

        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 12 ),
            'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'),
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000')))),
            'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => 'FFCCD490', ),
                'endcolor' => array( 'argb' => 'FFA8B355')));

        $sheet->getStyle("A$row:G$row")->applyFromArray($styleArray);
    }
    else if ($row == 4 || $row == 10 || $row == 14 || $row == 18 || $row == 22 || $row == 26 || $row == 30)
    {
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 11 ),
            'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000'),
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000')))),
            'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => 'FFDBE0B1', ),
                'endcolor' => array( 'argb' => 'FFA8B355')));

        $sheet->getStyle("A$row:G$row")->applyFromArray($styleArray);
    }

}


class page_admin_reports_growth_dashboard extends CPageAdminOnly
{

    private $currentStore = null;
    public static $titleSpanString = "";
    private $did_print_label = false;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

    function runFranchiseOwner()
    {
        $this->currentStore = CApp::forceLocationChoice();
        $this->runPage();
    }

    function runFranchiseManager()
    {
        $this->currentStore = CApp::forceLocationChoice();
        $this->runPage();
    }

    function runEventCoordinator()
    {
        $this->currentStore = CApp::forceLocationChoice();
        $this->runPage();
    }

    function runOpsLead()
    {
        $this->currentStore = CApp::forceLocationChoice();
        $this->runPage();
    }


    function runHomeOfficeManager()
    {
        $this->runPage();
    }

    function runSiteAdmin()
    {
        $this->runPage();
    }


    function promoWasHosted($user_id, $orderData)
    {
        $session_id = $orderData['session_id'];

        $testObj = new DAO();
        $testObj->query("select id from session_properties where session_id = $session_id and session_host = $user_id and is_deleted = 0" );
        if ($testObj->N > 0)
        {
            return true;
        }

        return false;
    }

    function processGuest($user_id, $thisGuest, $now, $storeObj)
    {
        $traceData = FALSE;

        $datime = date("Y-m-d H:i");

        if ($traceData)
        {
           //$path = "C:\\Users\\Carl.Samuelson\\Zend\\workspaces\\DefaultWorkspace\\Responsive\\GD_Data.csv";
         $path = "/DreamSite/report_debug/{$datime}_GD_Data.csv";


            $fh = fopen($path, 'a');

            if (!$this->did_print_label)
            {
                $labels = array("user id", "session time", "focus order type", "past order count", "past std order count", "last std menu",
                "last prior menu", "is reacquired",
                    "focus order is past", "Growth status",
                    "has future order", "Future Revenue", "next month revenue", "this order_revenue", "this month revenue", "is converted seed", "had std last menu");
                $length = fputs($fh, implode(",", $labels) . "\r\n");
                $this->did_print_label = true;
            }
        }


        $focusOrder = false;
        $retVal = array();

        $isConvertedSeed = false;
        $promoOrder = false;
        $standardOrder = false;
        if(count($thisGuest) > 1)
        {
            $hasStd = false;
            $hasPromo = false;
            foreach($thisGuest as $thisOrder)
            {
                if ($thisOrder['type_of_order'] == 'STANDARD') {
                    $hasStd = true;
                    if (!$standardOrder)
                    {
                        $standardOrder = $thisOrder;
                    }
                }
                else
                {
                    $hasPromo = true;
                    if (!$promoOrder)
                    {
                        $promoOrder = $thisOrder;
                    }
                }
            }

            if ($hasStd && $hasPromo)
            {
                //orders are mixed in type so focus order will be standard if promo was hosted
                if ($this->promoWasHosted($promoOrder['user_id'], $promoOrder))
                {
                    $focusOrder = $standardOrder;
                }
                else
                {
                    $focusOrder = $promoOrder;
                    $isConvertedSeed = true;
                }
            }
            else
            {
                // multiple orders in month so select order to focus on
                // for now pick the earliest one which will be first
                // all orders are of a type so first order is appropriate
                $focusOrder = array_shift($thisGuest);
            }
        }
        else
        {
            $focusOrder = array_pop($thisGuest);
        }

        $sessionTime = $focusOrder['session_time'];
        $storeID = $focusOrder['store_id'];

       $retVal['focusOrderIsPast'] = false;
       if (strtotime($sessionTime) < $now)
       {
           $retVal['focusOrderIsPast'] = true;
       }

        $lastMenu = $focusOrder['menu_id'] - 1;
        $nextMenu = $focusOrder['menu_id'] + 1;
        $thisMenu = $focusOrder['menu_id'];

        $history = new DAO();

		$qualifying_order_clause = $this->createMinimumClause($storeObj->id,$lastMenu);

		$q = "select 
                            count(distinct b.order_id) as past_order_count
                            ,count(distinct if(type_of_order = 'STANDARD', o.id, null)) as count_std_orders 
                            ,count(distinct if(type_of_order = 'STANDARD' and s.menu_id = $lastMenu, o.id, null)) as had_std_last_menu      
	                        ,max(if(type_of_order = 'STANDARD', s.menu_id, null)) as last_std_menu
	                        ,max(if(type_of_order = 'STANDARD', s.menu_id, null)) as last_prior_menu
                            from booking b
                            join session s on s.id = b.session_id and s.session_start < '$sessionTime'
                            join orders o on o.id = b.order_id
                            where b.user_id = {$focusOrder['user_id']} and b.status = 'ACTIVE' and b.is_deleted = 0 $qualifying_order_clause
                            group by b.user_id";
        $history->query($q);

        $history->fetch();

        $growthStatus = false;
        // Seed, Seedling or Plant

        $isReacquired = false;
        if (!empty($history->last_prior_menu) && $history->last_prior_menu < $thisMenu - 12)
        {
            $isReacquired = true;
        }

        // A seed has a first order this month that is a promo
        if (($history->past_order_count == 0 || $isReacquired) && $focusOrder['type_of_order'] != 'STANDARD')
        {
            $growthStatus = 'SEED';
            // Note: isConverted Seed can be true

        }
        else if (($history->count_std_orders == 0 || $isReacquired) && $focusOrder['type_of_order'] == 'STANDARD')
        {
            if ($isConvertedSeed)
            {
                // there are (at least) 2 possibilities here
                // 1) They attended their first Standard order prior to their first Promo of the month
                        // Should be a SEED with $isConvertedSeed = true

                // 2) they hosted a DreamTaste in the same month as their first full order
                        // Should be a seedling

                if (!$this->promoWasHosted($focusOrder['user_id'], $promoOrder))
                {
                    $growthStatus = 'SEED';  // 1)
                }
                else
                {
                    $growthStatus = 'SEEDLING'; // 2)
                    $isConvertedSeed = false;
                }
            }
            else
            {
                $growthStatus = 'SEEDLING';
            }
        }
        else if ($history->count_std_orders > 0 && !$history->had_std_last_menu && $focusOrder['type_of_order'] == 'STANDARD')
        {
            $growthStatus = 'REAC_SEEDLING';
        }
        else if ($focusOrder['type_of_order'] == 'STANDARD' && $history->had_std_last_menu)
        {
            $growthStatus = 'PLANT';
        }
        else
        {
            if ($focusOrder['type_of_order'] != 'STANDARD' && $isConvertedSeed)
            {
                // had both standard and promo - had recent std - was rejected from seed status
                // so must be a Plant or Seedling
                if ($history->had_std_last_menu)
                {
                    $growthStatus = 'PLANT';
                }
                else
                {
                    $growthStatus = 'REAC_SEEDLING';
                }
            }
            else
            {
                $growthStatus = 'ERRANT';
            }
        }

        $futures = new DAO();
		$qualifying_order_clause = $this->createMinimumClause($storeObj->id,$nextMenu);
        $futures->query("select 
					count(distinct if(s.session_start > '$sessionTime', o.id, null)) as future_orders		
                    ,count(distinct if(type_of_order = 'STANDARD', o.id, null)) as count_std_orders 
                    ,count(distinct if(type_of_order = 'STANDARD' and s.menu_id = $nextMenu, o.id, null)) as had_std_future_menu
                    ,sum(if(s.menu_id = $nextMenu, od.agr_total, null)) as next_month_rev
                    ,sum(if(s.menu_id = $thisMenu, od.agr_total, null)) as this_month_rev	
                    ,sum(if(s.session_start = '$sessionTime', od.agr_total, null)) as this_order_rev	
                    ,sum(if(s.session_start > '$sessionTime', od.agr_total, null)) as future_rev	
                    from booking b
                    join session s on s.id = b.session_id and s.session_start >= '$sessionTime'
                    join orders o on o.id = b.order_id
                    join orders_digest od on od.order_id = o.id
                    where b.user_id = {$focusOrder['user_id']} and b.status = 'ACTIVE' and b.is_deleted = 0 $qualifying_order_clause
                    group by b.user_id");

        $futures->fetch();

        $hasFutureOrder = false;
        if ($futures->had_std_future_menu)
        {
            $hasFutureOrder = true;
        }

        $retVal['growth_status'] = $growthStatus;
        $retVal['has_future_order'] = $hasFutureOrder;
        $retVal['future_revenue'] = (empty($futures->future_rev) ? 0 : $futures->future_rev);
        $retVal['next_month_revenue'] = (empty($futures->next_month_rev) ? 0 : $futures->next_month_rev);
        $retVal['this_order_revenue'] = (empty($futures->this_order_rev) ? 0 : $futures->this_order_rev);
        $retVal['this_month_revenue'] = (empty($futures->this_month_rev) ? 0 : $futures->this_month_rev);
        $retVal['is_converted_seed'] = $isConvertedSeed;
        $retVal['had_std_last_menu'] = $history->had_std_last_menu;
        $retVal['seed_conversion_order'] = $standardOrder;



        if ($traceData)
        {

            $debugArray = array();
            $debugArray['user_id'] = $focusOrder['user_id'];
            $debugArray['session_time'] = $sessionTime;

            $debugArray['focus_order_type'] = $focusOrder['type_of_order'];
            $debugArray['past_order_count'] = $history->past_order_count;
            $debugArray['count_std_orders'] = $history->count_std_orders;
            $debugArray['last_std_menu'] = $history->last_std_menu;
            $debugArray['last_prior_menu'] = $history->last_prior_menu;
            $debugArray['is_reacquired'] = $isReacquired;



            $debugArray = array_merge($debugArray, $retVal);

            $length = fputs($fh, implode(",", $debugArray) . "\r\n");
            fclose($fh);
        }

        return $retVal;
    }

    function summarize($rows)
    {
        $retVal = array('seed_count' => 0,
            'seedling_count' => 0,
            'reac_seedling_count' => 0,
            'growth_opportunity' => 0,
            'plant_count' => 0,
            'net_gain_loss' => 0,
            'total_guest_count' => 0);

        $seedGrowth = 0;
        $seedlingGrowth = 0;
        $plantGrowth = 0;
        $MTDPlantCount = 0;
        $MTDSeedlingGrowth = 0;
        $MTDPlantGrowth = 0;


        foreach($rows as $user_id => $data)
        {
            if ($data['growth_status'] == 'SEED')
            {
                $retVal['seed_count']++;

                if ($data['has_future_order'] )
                {
                    $seedGrowth++;
                }

                if ($data['is_converted_seed'])
                {
                    // Note: the Seed also had a standard order in the same month
                    // we count the Seed and the conversion (above)
                    //but we also count the new Seedling and if there is a future (next month) order count
                    //the seedling conversion as well
                    $retVal['seedling_count']++;

                    if ($data['has_future_order'] )
                    {
                        $seedlingGrowth++;
                    }
                }
            }
            else if ($data['growth_status'] == 'SEEDLING')
            {
                $retVal['seedling_count']++;

                if ($data['has_future_order'] )
                {
                    $seedlingGrowth++;

                    if ($data['focusOrderIsPast'])
                    {
                        $MTDSeedlingGrowth++;
                    }

                }

            }
            else if ($data['growth_status'] == 'REAC_SEEDLING')
            {
                $retVal['reac_seedling_count']++;

                if ($data['has_future_order'] )
                {
                    $seedlingGrowth++;

                    if ($data['focusOrderIsPast'])
                    {
                        $MTDSeedlingGrowth++;
                    }

                }

            }
            else if ($data['growth_status'] == 'PLANT')
            {
                $retVal['plant_count']++;

                if ($data['has_future_order'] )
                {
                    $plantGrowth++;
                    if ($data['focusOrderIsPast'])
                    {
                        $MTDPlantGrowth++;
                    }
                }

                if ($data['focusOrderIsPast'])
                {
                    $MTDPlantCount++;
                }

            }

        }

        $retVal['growth_opportunity'] = $retVal['seedling_count'] + $retVal['reac_seedling_count'];
        $totalGrowth = $MTDSeedlingGrowth + $MTDPlantGrowth;
        $retVal['net_gain_loss'] = $totalGrowth - $MTDPlantCount;

        $retVal['total_guest_count'] = count($rows);

        return $retVal;
    }

    function runPage()
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
		}

        $Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : '';

		if ($showStoreSelector)
        {
            $Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
                CForm::onChange => 'selectStoreTR',
                CForm::allowAllOption => false,
                CForm::showInactiveStores => false,
                CForm::name => 'store'));
            $store = $Form->value('store');
        }
		else
		{
            $store = $this->currentStore;
        }


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


        $currentYear = date("Y");
        $yearStepper = $currentYear;
        $yearArray = array();
        $yearArray[$yearStepper] = $yearStepper;
        for($x = 0; $x < 5; $x++)
        {
            --$yearStepper;
            $yearArray[$yearStepper] = $yearStepper;
        }

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::name => "year",
            CForm::required => true,
            CForm::options => $yearArray,
            CForm::default_value => $currentYear
        ));


        $currentMenuID = CMenu::getCurrentMenuId();
        $menuObj = DAO_CFactory::create('menu');
        $menuObj->id = $currentMenuID;
        $menuObj->find(true);
        $defaultMonth = date("n", strtotime($menuObj->menu_start));
        $defaultMonth--;

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::onChangeSubmit => false,
            CForm::allowAllOption => false,
            CForm::options => $month_array,
            CForm::default_value => $defaultMonth,
            CForm::name => 'month'
        ));

        $Form->AddElement(array(CForm::type => CForm::Hidden, CForm::name => 'export', CForm::value => 'none'));


        $tpl->assign('showStoreSelector', $showStoreSelector);

        $export = false;
        if (isset($_POST['export']) && $_POST['export'] == 'xlsx')
        {
            $export = true;
        }

        if (isset($_REQUEST["run_report"]) || $export)
        {

            $month = $Form->value('month') + 1;
            $year = $Form->value('year');
            $monthname = $month_array[$month - 1];

            $selectedMonthTS = mktime(0,0,0,$month, 1, $year);
            $selectedMenuID = CMenu::getMenuIDByAnchorDate(date("Y-m-d", $selectedMonthTS));



            $storeObj = DAO_CFactory::create('store');
            $storeObj->id = $store;
            $hadError = false;

            if (empty($storeObj->id))
            {
                if ($export)
                {
                    $tpl->setErrorMsg("Please Select a store.");
                }
                else
                {
                    echo "<html><head>";
                    echo "</head><body>";

                    echo "<h3>Please Select a Store.<h3></h3>";
                    echo "</body></html>";
                    exit;
                }

                $hadError = true;
            }
			$qualifying_order_clause = $this->createMinimumClause($storeObj->id,$selectedMenuID);

            if (!$hadError) {
                $storeObj->find(true);

                $MonthsGuests = new DAO();
				$q = "select b.user_id, b.session_id, o.type_of_order, s.menu_id, od.* from booking b
                                        join session s on s.id = b.session_id and s.store_id = $store and s.menu_id = $selectedMenuID
                                        join orders o on o.id = b.order_id
                                        join orders_digest od on od.order_id = o.id
                                        where b.status = 'ACTIVE' and b.is_deleted = 0 $qualifying_order_clause
                                        order by s.session_start";
                $MonthsGuests->query($q);

                $masterList = array();
                $processedList = array();

                while ($MonthsGuests->fetch()) {

                    if (!isset($masterList[$MonthsGuests->user_id])) {
                        $masterList[$MonthsGuests->user_id] = array();
                    }

                    $masterList[$MonthsGuests->user_id][$MonthsGuests->order_id] = DAO::getCompressedArrayFromDAO($MonthsGuests);

                }

                $now =  CTimezones::getAdjustedServerTime($storeObj);

                foreach ($masterList as $user_id => $orders)
                {
                    $processedList[$user_id] = $this->processGuest($user_id, $orders, $now, $storeObj);
                }

                /*
                $numPlants = 0;
                foreach($processedList as $user_id => $data)
				{
					if ($data['growth_status'] == 'PLANT')
					{
						echo $user_id . "\r\n";
						$numPlants++;
					}
				}
*/

                $summary = $this->summarize($processedList);

                $rows = array();
                $headerRows = array();
                $this->storeSummary($summary, $rows, $storeObj, $monthname, $year);
                $rows[] = array(" |->7");
                $rows[] = array("Guest Category Growth Reports|->7");
                $rows[] = array(" |->7");

                $this->storeNewGuestDetail($summary, $rows, $processedList);
                $rows[] = array(" |->7");
                $this->storeSeedlingsDetail($summary, $rows, $processedList);
                $rows[] = array(" |->7");
                $this->storeFormerGuestDetail($summary, $rows, $processedList);
                $rows[] = array(" |->7");
                $this->storeGrowthOppDetail($summary, $rows, $processedList);
                $rows[] = array(" |->7");
                $this->storeMaintenanceOppDetail($summary, $rows, $processedList);
                $rows[] = array(" |->7");
                $this->storeTotalGuestCounts($summary, $rows, $processedList);

                $this->printSheet($rows, $export, $tpl);

            }
        }


		$titleString = "Growth Dashboard";
		$tpl->assign('titleString', $titleString);


		$formArray = $Form->render();
		$tpl->assign('store', $store);

		$tpl->assign('form_array', $formArray);


	}

    function printSheet($rows, $export, $tpl)
    {
        $columnDescs = array();
        $columnDescs['A'] = array("width" => 30);
        $columnDescs['B'] = array("width" => 30);
        $columnDescs['C'] = array("width" => 30);
        $columnDescs['D'] = array("width" => 30);
        $columnDescs['E'] = array("width" => 30);
        $columnDescs['F'] = array("width" => 30);
        $columnDescs['G'] = array("width" => 30);
        PHPExcel_Shared_String::setThousandsSeparator(",");

        /*z
         * function writeExcelFile($fileName, $header, $rows, $showHeader=true, $titleRows = false, $columnDescriptions = false,
    $headersAreEmbedded = false, $callbacks = false, $superHeader = false, $returnHTML = false, $suppressLabelsDisplay = false, $overrideValues = false)

         */

        $callbacks = array('cell_callback' => 'ReportCellCallback','row_callback' => 'ReportRowCallback', 'final_render' => 'ReportFinalRenderCallback');


        if ($export)
        {
            $RangeStr =  $month . "_" . $day . "_" . $year . "_" . $duration;


            $tpl->assign('col_descriptions', $columnDescs);
            $tpl->assign('file_name', makeTitle("GrowthDashboard", "", ""));
            $tpl->assign('suppressLabelsDisplay', true);
            $tpl->assign('rows', $rows);
            $tpl->assign('rowcount', count($rows));
            $tpl->assign('excel_callbacks', $callbacks);

            $_GET['export'] = 'xlsx';

        }
        else
        {
            list($css, $html) =  writeExcelFile("Test", array(), $rows, false, false, $columnDescs, false, $callbacks, false, true);

            echo "<html><head>";
            echo $css;
            echo "</head><body>";

            echo $html;
            echo "</body></html>";
            exit;
        }

    }

    function storeSummary($summaryData, &$rows, $storeObj, $monthname, $year)
    {


        $rows[] = array("Growth Dashboard - {$storeObj->store_name} - $monthname $year|->7");
        $rows[] = array("Month-at-a-Glance|->7");
        $rows[] = array("Seeds" , "Seedlings|->2", "Total Growth Opportunity", "Existing Plants", "Growth Results", "Entire Field");
        $rows[] = array("New Guest Count (Seeds)" , "1st Full Session Guest Count (Seedlings)", "Former Guest Count (Reclaimed and Reacquired)",
            "Growth Opportunity Guest Count (Seedlings + Former Guests)", "Retained Guest Count",
            "MTD Store Growth by Guest Count (Net gain/loss)", "Total Guest Count");

        $rows[] = $summaryData;


    }

    function storeNewGuestDetail($summaryData, &$rows, $masterList)
    {
        $seedRevenue = 0;
        $seedTotalCount = 0;
        $seedMTDCount = 0;
        $convertedSeeds = 0;
        $MTDConvertedSeeds = 0;
        $conversionRate = 0;
        $remainingSeeds = 0;
        $allFutureRevenue = 0;


        foreach($masterList as $userId => $data)
        {

            if ($data['growth_status'] == 'SEED')
            {
                $seedRevenue += $data['this_order_revenue'];

                $allFutureRevenue += $data['future_revenue'];

                $seedTotalCount++;
                if ($data['focusOrderIsPast'])
                {
                    $seedMTDCount++;
                }

                if ($data['has_future_order'] )
                {
                    $convertedSeeds++;
                    if ($data['focusOrderIsPast'])
                    {
                        $MTDConvertedSeeds++;
                    }

                }
            }
        }

        if ($seedMTDCount > 0)
        {
            $conversionRate = $MTDConvertedSeeds / $seedMTDCount;
        }
        else
        {
            $conversionRate = 0;
        }

        $remainingSeeds = $seedTotalCount - $seedMTDCount;


        $rows[] = array("New Guests (Seeds)|->7");
        $rows[] = array("Total Revenue from All Seeds" , "Total Seeds Count", "MTD Seeds Count",
            "MTD Seeds Results", "MTD Seeds Conversion Rate",
            "Remaining Seeds", "MTD Revenue on Subsequent Visits");

        $rows[] = array($seedRevenue ."|=>currency" , $seedTotalCount, $seedMTDCount,
            $MTDConvertedSeeds, $conversionRate ."|=>percent",
            $remainingSeeds, $allFutureRevenue ."|=>currency" );

    }


    function storeSeedlingsDetail($summaryData, &$rows, $masterList)
    {

        $seedRevenue = 0;
        $seedTotalCount = 0;
        $seedMTDCount = 0;
        $convertedSeeds = 0;
        $MTDConvertedSeeds = 0;
        $conversionRate = 0;
        $remainingSeeds = 0;
        $futureRevenue = 0;


        foreach($masterList as $userId => $data)
        {

            if ($data['growth_status'] == 'SEEDLING')
            {
                $seedRevenue += $data['this_order_revenue'];

                $futureRevenue += $data['next_month_revenue'];

                $seedTotalCount++;
                if ($data['focusOrderIsPast'])
                {
                    $seedMTDCount++;
                }

                if ($data['has_future_order'] )
                {
                    $convertedSeeds++;
                    if ($data['focusOrderIsPast'])
                    {
                        $MTDConvertedSeeds++;
                    }

                }
            }
            else if ($data['growth_status'] == 'SEED' and $data['is_converted_seed'] )
            {
                $seedRevenue += $data['seed_conversion_order']['agr_total'];
                $futureRevenue += $data['next_month_revenue'];

                $seedTotalCount++;


                $sessionTime = $data['seed_conversion_order']['session_time'];
                $storeID = $data['seed_conversion_order']['store_id'];

                $now =  CTimezones::getAdjustedServerTime($storeID);

                if (strtotime($sessionTime) < $now)
                {
                    $seedMTDCount++;
                }

                if ($data['has_future_order'] )
                {
                    $convertedSeeds++;
                    if (strtotime($sessionTime) < $now)
                    {
                        $MTDConvertedSeeds++;
                    }
                }
            }
        }

        if ($seedMTDCount > 0)
        {
            $conversionRate = $MTDConvertedSeeds / $seedMTDCount;
        }
        else
        {
            $conversionRate = 0;
        }

        $remainingSeeds = $seedTotalCount - $seedMTDCount;

        $rows[] = array("1st Full Session Guests (Seedlings)|->7");
        $rows[] = array("Total Revenue from All Seedlings" , "Total Seedlings Count", "MTD Seedlings Count",
            "MTD Seedlings Results", "MTD Seedlings Conversion Rate",
            "Remaining Seedlings", "MTD Revenue for Following Month");
        $rows[] = array($seedRevenue ."|=>currency" , $seedTotalCount, $seedMTDCount,
            $MTDConvertedSeeds, $conversionRate ."|=>percent",
            $remainingSeeds, $futureRevenue ."|=>currency" );


    }

    function storeFormerGuestDetail($summaryData, &$rows, $masterList)
    {

        $seedRevenue = 0;
        $seedTotalCount = 0;
        $seedMTDCount = 0;
        $convertedSeeds = 0;
        $MTDConvertedSeeds = 0;
        $conversionRate = 0;
        $remainingSeeds = 0;
        $futureRevenue = 0;


        foreach($masterList as $userId => $data)
        {

            if ($data['growth_status'] == 'REAC_SEEDLING')
            {
                $seedRevenue += $data['this_order_revenue'];

                $futureRevenue += $data['next_month_revenue'];

                $seedTotalCount++;
                if ($data['focusOrderIsPast'])
                {
                    $seedMTDCount++;
                }

                if ($data['has_future_order'] )
                {
                    $convertedSeeds++;
                    if ($data['focusOrderIsPast'])
                    {
                        $MTDConvertedSeeds++;
                    }

                }
            }
        }

        if ($seedMTDCount > 0)
        {
            $conversionRate = $MTDConvertedSeeds / $seedMTDCount;
        }
        else
        {
            $conversionRate = 0;
        }

        $remainingSeeds = $seedTotalCount - $seedMTDCount;

        $rows[] = array("Former Guests (Reclaimed and Reacquired)|->7");
        $rows[] = array("Total Revenue from All Former Guests" , "Total Former Guests", "MTD Former Guests",
            "MTD Former Guests Results", "MTD Former Guests Conversion Rate",
            "MTD Remaining Former Guests", "MTD Revenue following Month");

        $rows[] = array($seedRevenue ."|=>currency" , $seedTotalCount, $seedMTDCount,
            $MTDConvertedSeeds, $conversionRate ."|=>percent",
            $remainingSeeds, $futureRevenue ."|=>currency" );

    }

    function storeGrowthOppDetail($summaryData, &$rows, $masterList)
    {

        $seedRevenue = 0;
        $seedTotalCount = 0;
        $seedMTDCount = 0;
        $convertedSeeds = 0;
        $MTDConvertedSeeds = 0;
        $conversionRate = 0;
        $remainingSeeds = 0;
        $futureRevenue = 0;


        foreach($masterList as $userId => $data)
        {

            if ($data['growth_status'] == 'REAC_SEEDLING' || $data['growth_status'] == 'SEEDLING')
            {
                $seedRevenue += $data['this_order_revenue'];

                $futureRevenue += $data['next_month_revenue'];

                $seedTotalCount++;
                if ($data['focusOrderIsPast'])
                {
                    $seedMTDCount++;
                }

                if ($data['has_future_order'] )
                {
                    $convertedSeeds++;
                    if ($data['focusOrderIsPast'])
                    {
                        $MTDConvertedSeeds++;
                    }

                }
            }
        }

        if ($seedMTDCount > 0)
        {
            $conversionRate = $MTDConvertedSeeds / $seedMTDCount;
        }
        else
        {
            $conversionRate = 0;
        }

        $remainingSeeds = $seedTotalCount - $seedMTDCount;

        $rows[] = array("Growth Opportunities (Sum of Seedlings + Former Guests)|->7");
        $rows[] = array("Total Revenue from All Growth Opportunity Guests" , "Total Growth Opportunities", "MTD Growth Opportunities",
            "MTD Growth Results", "MTD Growth Opportunity Conversion Rate",
            "MTD Remaining Growth Opportunities", "MTD Revenue for Following Month");
        $rows[] = array($seedRevenue ."|=>currency" , $seedTotalCount, $seedMTDCount,
            $MTDConvertedSeeds, $conversionRate ."|=>percent",
            $remainingSeeds, $futureRevenue ."|=>currency" );

    }

    function storeMaintenanceOppDetail($summaryData, &$rows, $masterList)
    {

        $seedRevenue = 0;
        $seedTotalCount = 0;
        $seedMTDCount = 0;
        $convertedSeeds = 0;
        $MTDConvertedSeeds = 0;
        $conversionRate = 0;
        $remainingSeeds = 0;
        $futureRevenue = 0;


        foreach($masterList as $userId => $data)
        {

            if ($data['growth_status'] == 'PLANT')
            {
                $seedRevenue += $data['this_order_revenue'];

                $futureRevenue += $data['next_month_revenue'];

                $seedTotalCount++;
                if ($data['focusOrderIsPast'])
                {
                    $seedMTDCount++;
                }

                if ($data['has_future_order'] )
                {
                    $convertedSeeds++;
                    if ($data['focusOrderIsPast'])
                    {
                        $MTDConvertedSeeds++;
                    }

                }
            }
        }

        if ($seedMTDCount > 0)
        {
            $conversionRate = $MTDConvertedSeeds / $seedMTDCount;
        }
        else
        {
            $conversionRate = 0;
        }

        $remainingSeeds = $seedTotalCount - $seedMTDCount;

        $rows[] = array("Retained Guests (Existing Plants)|->7");
        $rows[] = array("Total Revenue from All Retained Guests" , "Total Retained Guest Count", "MTD Maintenance Opportunities",
            "MTD Maintenance Opportunity Results", "MTD Maintenance Opportunity Conversion Rate",
            "Remaining Maintenance Opportunities", "MTD Revenue for Following Month");
        $rows[] = array($seedRevenue ."|=>currency" , $seedTotalCount, $seedMTDCount,
            $MTDConvertedSeeds, $conversionRate ."|=>percent",
            $remainingSeeds, $futureRevenue ."|=>currency" );

    }

    function storeTotalGuestCounts($summaryData, &$rows, $masterList)
    {
        $seedRevenue = 0;
        $seedTotalCount = 0;
        $seedMTDCount = 0;
        $convertedSeeds = 0;
        $MTDConvertedSeeds = 0;
        $conversionRate = 0;
        $remainingSeeds = 0;
        $futureRevenue = 0;


        foreach($masterList as $userId => $data)
        {

            $seedRevenue += $data['this_order_revenue'];

            $futureRevenue += $data['next_month_revenue'];

            $seedTotalCount++;
            if ($data['focusOrderIsPast'])
            {
                $seedMTDCount++;
            }

            if ($data['has_future_order'] )
            {
                $convertedSeeds++;
                if ($data['focusOrderIsPast'])
                {
                    $MTDConvertedSeeds++;
                }

            }
        }

        if ($seedMTDCount > 0)
        {
            $conversionRate = $MTDConvertedSeeds / $seedMTDCount;
        }
        else
        {
            $conversionRate = 0;
        }

        $remainingSeeds = $seedTotalCount - $seedMTDCount;

        $rows[] = array("Total Guest Count|->7");
        $rows[] = array("Total Revenue from All Guests" , "Total Number of Guests", "MTD Number of Guests",
            "MTD Retention Results", "MTD Retention Rate",
            "MTD Remaining Total Guests ", "MTD Revenue for following month");
        $rows[] = array($seedRevenue ."|=>currency" , $seedTotalCount, $seedMTDCount,
            $MTDConvertedSeeds, $conversionRate ."|=>percent",
            $remainingSeeds, $futureRevenue ."|=>currency" );

    }

	function createMinimumClause($store_id,$menu_id){
		$minimum_sql_clause = ' and o.is_qualifying = 1';
		$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $store_id,$menu_id);


		if(!is_null($minimum)){
			if($minimum->getMinimumType() == COrderMinimum::SERVING){
				$minimum_sql_clause .= ' and o.servings_core_total_count >= ' .$minimum->getMinimum();
			}

			if($minimum->getMinimumType() == COrderMinimum::ITEM){
				$minimum_sql_clause .= ' and o.menu_items_core_total_count >= ' .$minimum->getMinimum();
			}
		}
		//leaving as is for now -- delete if we need to limit to qualifying order
		$minimum_sql_clause = '';
		return $minimum_sql_clause;
	}
}
?>