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


function array_min_max($array)
{
    $min = 99999999;
    $max = -999999999;


    foreach($array as $datum)
    {
        if ($datum > $max)
        {
            $max = $datum;
        }
        if ($datum < $min)
        {
            $min = $datum;
        }
    }

    return array($min, $max);
}

function getPerformanceColor($rowDef, $value)
{
    $range = $rowDef['max'] - $rowDef['min'];

    if ($range > 0 && is_numeric($value))
    {
        $pos = (($rowDef['max'] - $value) / $range) * 100;
    }
    else
    {
        return array('argb' =>"99FFFFFF");
    }

    if ($pos < 15)
    {
        return array('argb' =>"99F7686A");
    }
    else if ($pos < 29)
    {
        return array('argb' =>"99F99472");
    }
    else if ($pos < 43)
    {
        return array('argb' =>"99FCC87C");
    }
    else if ($pos < 58)
    {
        return array('argb' =>"99FFEA83");
    }
    else if ($pos < 72)
    {
        return array('argb' =>"99E9E482");
    }
    else if ($pos < 86)
    {
        return array('argb' =>"99B3D57F");
    }
    else
    {
        return array('argb' =>"9962BD7A");
    }

}


function ReportCellCallback($sheet, $colName, $datum, $col, $row)
{
       if (isset(page_admin_reports_menu_skipping::$rowStats[$row]) && $colName > 5)
       {
           $styleArray = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
               'startcolor' => getPerformanceColor(page_admin_reports_menu_skipping::$rowStats[$row], $datum)),
               'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FFAAAAAA')),
                   'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FFAAAAAA'))));
       }
        else
        {
            $styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FFAAAAAA')),
                'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FFAAAAAA'))));
        }


    $sheet->getStyle("$col$row")->applyFromArray($styleArray);

}

function ReportRowCallback($sheet, $data, $row, $bottomRightExtent)
{

    $lastCol = page_admin_reports_menu_skipping::$sLastCol;
    if ($data[5] == 'Std Skip Rate')
    {
        $sheet->getStyle("G$row:$lastCol$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);



    }
    else if ($data[5] == 'MFY Skip Rate')
    {
        $sheet->getStyle("G$row:$lastCol$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
        $styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000'))));
        $sheet->getStyle("A$row:$lastCol$row")->applyFromArray($styleArray);

    }

}


class page_admin_reports_menu_skipping extends CPageAdminOnly
{

    public static $titleSpanString = "";
    public static  $sLastCol = "";
    public static $rowStats = array();

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

    function runHomeOfficeManager()
    {
        $this->runPage();
    }

    function runSiteAdmin()
    {
        $this->runPage();
    }


    function getStdAttendees($store, $month)
    {
        $retVal = array();
        $daoObj = new DAO();
        $daoObj->query("select distinct b.user_id from session s
                            join booking b on b.session_id = s.id and b.status = 'ACTIVE' 
                            join orders_digest od on od.order_id = b.order_id and od.order_type= 'REGULAR' and od.session_type = 'STANDARD'
                            where s.menu_id = $month and s.store_id = $store");

        while($daoObj->fetch())
        {
            $retVal[] = $daoObj->user_id;
        }
        return $retVal;
    }

    function getM4UAttendees($store, $month)
    {
        $retVal = array();
        $daoObj = new DAO();
        $daoObj->query("select distinct b.user_id from session s
                            join booking b on b.session_id = s.id and b.status = 'ACTIVE' 
                            join orders_digest od on od.order_id = b.order_id and od.order_type= 'REGULAR' and od.session_type = 'MADE_FOR_YOU'
                            where s.menu_id = $month and s.store_id = $store");

        while($daoObj->fetch())
        {
            $retVal[] = $daoObj->user_id;
        }
        return $retVal;
    }


    function summarize($focusMenuID, $monthsBack, $monthsSkipped, &$colDesc, &$labels)
    {
        $rows = array();

        $storeArray = array();
        $dataTemplate = array('# Reg Guests Prev Month' => 0, '# Reg Guests skipped' => 0, '# MFY Guests Prev Month' => 0,
            '# MFY Guests skipped' => 0, 'Std Skip Rate' => 0, 'MFY Skip Rate' => 0);


        $stores = new DAO();

        $stores->query("select id, home_office_id, store_name, city, state_id, is_corporate_owned from store
                where (last_menu_supported > $focusMenuID or isnull(last_menu_supported)) and is_deleted = 0 order by id asc");


        $totals = array();
        $corp_totals = array();
        $franch_totals = array();

        $yearsArray = array();

        $first = true;
        $std_row = 6;
        $mfy_row = 7;

        while ($stores->fetch()) {

            $std_min = 999999999;
            $std_max = -999999999;
            $mfy_min = 999999999;
            $mfy_max = -999999999;



            $thisStore = $stores->id;
            $storeArray[$thisStore] = DAO::getCompressedArrayFromDAO($stores);

            $masterArray[$thisStore] = array();

            $lastMonthStdAttendees = array();
            $lastMonthM4UAttendees = array();

            $thisMonthStdAttendees = array();
            $thisMonthM4UAttendees = array();

            $nextMonthStdAttendees = array();
            $nextMonthM4UAttendees = array();

            $startMenu = $focusMenuID - $monthsBack;

            $lastMonthStdAttendees = $this->getStdAttendees($thisStore, $startMenu - 1);
            $lastMonthM4UAttendees = $this->getM4UAttendees($thisStore, $startMenu - 1);

            $thisMonthStdAttendees = $this->getStdAttendees($thisStore, $startMenu);
            $thisMonthM4UAttendees = $this->getM4UAttendees($thisStore, $startMenu);


            $col = 'F';
            $colSecondChar = '';
            $colThirdChar = '';
            $lastYear = false;
            $lastCol = false;
            $lastSecondCol = false;
            $lastThirdCol = false;

            for ($menu_id = $startMenu; $menu_id < $focusMenuID + 1; $menu_id++)
            {

                $lastCol = $col;
                $lastSecondCol = $colSecondChar;
                $lastThirdCol = $colThirdChar;

                incrementColumn($colThirdChar, $colSecondChar, $col);


                if ($first) {
                    $daoMenu = new DAO();
                    $daoMenu->query("select menu_start from menu where id = $menu_id");
                    $daoMenu->fetch();
                    $labels[] = $daoMenu->menu_start;
                    $thisYear = date("Y", strtotime($daoMenu->menu_start));
                    $colDesc[$colSecondChar.$col] = array('width' => 15);

                    if (!isset($yearsArray[$thisYear]))
                    {
                        $yearsArray[$thisYear] = array('startCol' => $colThirdChar.$colSecondChar.$col, 'endCol' => false);

                        if ($lastYear && $lastYear != $thisYear)
                        {
                            $yearsArray[$lastYear]['endCol'] = $lastThirdCol.$lastSecondCol.$lastCol;
                        }

                        $lastYear = $thisYear;
                    }
                }

                // coming into the loop for the first time we have the previous months attendess
                // get this months and next and determine the count of those that appear in last and next bnut not current
                $nextMonthStdAttendees = $this->getStdAttendees($thisStore, $menu_id + 1);
                $nextMonthM4UAttendees = $this->getM4UAttendees($thisStore, $menu_id + 1);


                // Note for multiple month lapse we would continue the search here
                if ($monthsSkipped > 1) {

                    $notInCurrentMonthSTD = $lastMonthStdAttendees;
                    $notInCurrentMonthM4U = $lastMonthM4UAttendees;

                    $thisMonth = $menu_id;
                    for ($x = 0; $x < $monthsSkipped; $x++) {
                        $NthMonthStdAttendees = $this->getStdAttendees($thisStore, $thisMonth);
                        $NthMonthM4UAttendees = $this->getM4UAttendees($thisStore, $thisMonth);

                        // get the arrays of those last month attendees not in the current month
                        $notInCurrentMonthSTD = array_diff($notInCurrentMonthSTD, $NthMonthStdAttendees, $NthMonthM4UAttendees);
                        $notInCurrentMonthM4U = array_diff($notInCurrentMonthM4U, $NthMonthStdAttendees, $NthMonthM4UAttendees);

                        $thisMonth++;

                    }

                    $finalMonthStdAttendees = $this->getStdAttendees($thisStore, $thisMonth);
                    $finalMonthM4UAttendees = $this->getM4UAttendees($thisStore, $thisMonth);

                    $allNextMonthAttendees = array_unique(array_merge($finalMonthStdAttendees, $finalMonthM4UAttendees));

                } else {


                    // get the arrays of those last month attendees not in the current month
                    $notInCurrentMonthSTD = array_diff($lastMonthStdAttendees, $thisMonthStdAttendees, $thisMonthM4UAttendees);
                    $notInCurrentMonthM4U = array_diff($lastMonthM4UAttendees, $thisMonthStdAttendees, $thisMonthM4UAttendees);


                    $allNextMonthAttendees = array_unique(array_merge($nextMonthStdAttendees, $nextMonthM4UAttendees));

                }


                $skippedSTD = array_intersect($notInCurrentMonthSTD, $allNextMonthAttendees);
                $skippedM4U = array_intersect($notInCurrentMonthM4U, $allNextMonthAttendees);

                $masterArray[$thisStore][$menu_id] = $dataTemplate;

                $masterArray[$thisStore][$menu_id]['# Reg Guests Prev Month'] = count($lastMonthStdAttendees);
                $masterArray[$thisStore][$menu_id]['# Reg Guests skipped'] = count($skippedSTD);
                $masterArray[$thisStore][$menu_id]['# MFY Guests Prev Month'] = count($lastMonthM4UAttendees);
                $masterArray[$thisStore][$menu_id]['# MFY Guests skipped'] = count($skippedM4U);

                if (count($lastMonthStdAttendees)) {
                    $masterArray[$thisStore][$menu_id]['Std Skip Rate'] = count($skippedSTD) / count($lastMonthStdAttendees);
                } else {
                    $masterArray[$thisStore][$menu_id]['Std Skip Rate'] = 0;
                }

                if (count($lastMonthM4UAttendees)) {
                    $masterArray[$thisStore][$menu_id]['MFY Skip Rate'] = count($skippedM4U) / count($lastMonthM4UAttendees);
                } else {
                    $masterArray[$thisStore][$menu_id]['MFY Skip Rate'] = 0;
                }

                if ($masterArray[$thisStore][$menu_id]['Std Skip Rate'] > $std_max)
                {
                    $std_max = $masterArray[$thisStore][$menu_id]['Std Skip Rate'];
                }
                if ($masterArray[$thisStore][$menu_id]['Std Skip Rate'] < $std_min)
                {
                    $std_min = $masterArray[$thisStore][$menu_id]['Std Skip Rate'];
                }

                if ($masterArray[$thisStore][$menu_id]['MFY Skip Rate'] > $mfy_max)
                {
                    $mfy_max = $masterArray[$thisStore][$menu_id]['MFY Skip Rate'];
                }
                if ($masterArray[$thisStore][$menu_id]['MFY Skip Rate'] < $mfy_min)
                {
                    $mfy_min = $masterArray[$thisStore][$menu_id]['MFY Skip Rate'];
                }

                if (!isset($totals[$menu_id])) {
                    $totals[$menu_id] = $dataTemplate;
                }
                if (!isset($corp_totals[$menu_id])) {
                    $corp_totals[$menu_id] = $dataTemplate;
                }
                if (!isset($franch_totals[$menu_id])) {
                    $franch_totals[$menu_id] = $dataTemplate;
                }

                $totals[$menu_id]['# Reg Guests Prev Month'] += count($lastMonthStdAttendees);
                $totals[$menu_id]['# Reg Guests skipped'] += count($skippedSTD);
                $totals[$menu_id]['# MFY Guests Prev Month'] += count($lastMonthM4UAttendees);
                $totals[$menu_id]['# MFY Guests skipped'] += count($skippedM4U);


                if ($storeArray[$thisStore]['is_corporate_owned'])
                {

                    $corp_totals[$menu_id]['# Reg Guests Prev Month'] += count($lastMonthStdAttendees);
                    $corp_totals[$menu_id]['# Reg Guests skipped'] += count($skippedSTD);
                    $corp_totals[$menu_id]['# MFY Guests Prev Month'] += count($lastMonthM4UAttendees);
                    $corp_totals[$menu_id]['# MFY Guests skipped'] += count($skippedM4U);

                }
                else
                 {
                    $franch_totals[$menu_id]['# Reg Guests Prev Month'] += count($lastMonthStdAttendees);
                    $franch_totals[$menu_id]['# Reg Guests skipped'] += count($skippedSTD);
                    $franch_totals[$menu_id]['# MFY Guests Prev Month'] += count($lastMonthM4UAttendees);
                    $franch_totals[$menu_id]['# MFY Guests skipped'] += count($skippedM4U);
                }

                $lastMonthStdAttendees = $thisMonthStdAttendees;
                $lastMonthM4UAttendees = $thisMonthM4UAttendees;

                $thisMonthStdAttendees = $nextMonthStdAttendees;
                $thisMonthM4UAttendees = $nextMonthM4UAttendees;


            }


            self::$rowStats[$std_row] = array('min' => $std_min, 'max' => $std_max);
            self::$rowStats[$mfy_row] = array('min' => $mfy_min, 'max' => $mfy_max);
            $std_row += 6;
            $mfy_row +=6;

            $first = false;
        }

        $yearsArray[$thisYear]['endCol'] = $colThirdChar .$colSecondChar . $col;

        $lastMontlyCol = $col;
        $lastMontlySecondCol = $colSecondChar;
        $lastMontlyThirdCol = $colThirdChar;

        incrementColumn($colThirdChar, $colSecondChar, $col);

        $startCF = 2;
        foreach($masterArray as $thisStore => $storeData)
        {
            foreach($dataTemplate as $thisMetric => $junk)
            {
                $thisline = array($thisStore, $storeArray[$thisStore]['home_office_id'], $storeArray[$thisStore]['store_name'], $storeArray[$thisStore]['city'], $storeArray[$thisStore]['state_id'], $thisMetric );

                foreach($storeData as $thisMonth => $data)
                {
                    $thisline[] = $data[$thisMetric];
                }


                foreach ($yearsArray as $theYear => $theRange)
                {


                    $start = $theRange['startCol'];
                    $end =  $theRange['endCol'];
                    $thisline[] = "=AVERAGE($start$startCF:$end$startCF)|=>number_x";
                    if ($startCF == 2)
                    {
                        $labels[] = $theYear . " Average";
                        incrementColumn($colThirdChar, $colSecondChar, $col);
                        self::$sLastCol = $colThirdChar.$colSecondChar.$col;

                    }

                }

                $startCF += 1;

                $rows[] =  $thisline;
            }
        }

    //TOTALS rows
        foreach($totals as $thisMonth => $data)
        {
            $totals[$thisMonth]['Std Skip Rate'] = $totals[$thisMonth]['# Reg Guests skipped'] / $totals[$thisMonth]['# Reg Guests Prev Month'];
            $totals[$thisMonth]['MFY Skip Rate'] = $totals[$thisMonth]['# MFY Guests skipped'] / $totals[$thisMonth]['# MFY Guests Prev Month'];
        }

        foreach($dataTemplate as $thisMetric => $junk)
        {
            $thisline = array("","", "Total", "All Stores", "", $thisMetric );


            $tempArr = array();

            foreach($totals as $thisMonth => $data)
            {
                $thisline[] = $data[$thisMetric];

                if ($thisMetric == 'Std Skip Rate')
                {
                    $tempArr[] = $data[$thisMetric];
                }
                else if ($thisMetric == 'MFY Skip Rate')
                {
                    $tempArr[] = $data[$thisMetric];
                }
            }

            if ($thisMetric == 'Std Skip Rate')
            {
                list($std_min, $std_max) = array_min_max($tempArr);
            }
            if ($thisMetric == 'MFY Skip Rate')
            {
                list($mfy_min, $mfy_max) = array_min_max($tempArr);
            }

            foreach ($yearsArray as $theYear => $theRange)
            {
                $start = $theRange['startCol'];
                $end =  $theRange['endCol'];

                $thisline[] = "=AVERAGE($start$startCF:$end$startCF)|=>number_x";
            }

            $startCF += 1;
            $rows[] =  $thisline;
        }


        self::$rowStats[$std_row] = array('min' => $std_min, 'max' => $std_max);
        self::$rowStats[$mfy_row] = array('min' => $mfy_min, 'max' => $mfy_max);
        $std_row += 6;
        $mfy_row +=6;



        // CORP_Totals
        foreach($corp_totals as $thisMonth => $data)
        {
            $corp_totals[$thisMonth]['Std Skip Rate'] = $corp_totals[$thisMonth]['# Reg Guests skipped'] / $corp_totals[$thisMonth]['# Reg Guests Prev Month'];
            $corp_totals[$thisMonth]['MFY Skip Rate'] = $corp_totals[$thisMonth]['# MFY Guests skipped'] / $corp_totals[$thisMonth]['# MFY Guests Prev Month'];
        }

        foreach($dataTemplate as $thisMetric => $junk)
        {
            $thisline = array("","", "Total", "Corp. Stores", "", $thisMetric );

            $tempArr = array();

            foreach($corp_totals as $thisMonth => $data)
            {
                $thisline[] = $data[$thisMetric];

                if ($thisMetric == 'Std Skip Rate')
                {
                    $tempArr[] = $data[$thisMetric];
                }
                else if ($thisMetric == 'MFY Skip Rate')
                {
                    $tempArr[] = $data[$thisMetric];
                }
            }

            if ($thisMetric == 'Std Skip Rate')
            {
                list($std_min, $std_max) = array_min_max($tempArr);
            }
            if ($thisMetric == 'MFY Skip Rate')
            {
                list($mfy_min, $mfy_max) = array_min_max($tempArr);
            }

            foreach ($yearsArray as $theYear => $theRange)
            {
                $start = $theRange['startCol'];
                $end =  $theRange['endCol'];

                $thisline[] = "=AVERAGE($start$startCF:$end$startCF)|=>number_x";
            }
            $startCF += 1;

            $rows[] =  $thisline;
        }

        self::$rowStats[$std_row] = array('min' => $std_min, 'max' => $std_max);
        self::$rowStats[$mfy_row] = array('min' => $mfy_min, 'max' => $mfy_max);
        $std_row += 6;
        $mfy_row +=6;


        // FRANCHISE Totals
        foreach($franch_totals as $thisMonth => $data)
        {
            $franch_totals[$thisMonth]['Std Skip Rate'] = $franch_totals[$thisMonth]['# Reg Guests skipped'] / $franch_totals[$thisMonth]['# Reg Guests Prev Month'];
            $franch_totals[$thisMonth]['MFY Skip Rate'] = $franch_totals[$thisMonth]['# MFY Guests skipped'] / $franch_totals[$thisMonth]['# MFY Guests Prev Month'];
        }

        foreach($dataTemplate as $thisMetric => $junk)
        {
            $thisline = array("","", "Total", "Franch. Stores", "", $thisMetric );
            $tempArr = array();

            foreach($franch_totals as $thisMonth => $data)
            {
                $thisline[] = $data[$thisMetric];

                if ($thisMetric == 'Std Skip Rate')
                {
                    $tempArr[] = $data[$thisMetric];
                }
                else if ($thisMetric == 'MFY Skip Rate')
                {
                    $tempArr[] = $data[$thisMetric];
                }
            }

            foreach ($yearsArray as $theYear => $theRange)
            {
                $start = $theRange['startCol'];
                $end =  $theRange['endCol'];

                $thisline[] = "=AVERAGE($start$startCF:$end$startCF)|=>number_x";
            }

            if ($thisMetric == 'Std Skip Rate')
            {
                list($std_min, $std_max) = array_min_max($tempArr);
            }
            if ($thisMetric == 'MFY Skip Rate')
            {
                list($mfy_min, $mfy_max) = array_min_max($tempArr);
            }

            $startCF += 1;

            $rows[] =  $thisline;
        }

        self::$rowStats[$std_row] = array('min' => $std_min, 'max' => $std_max);
        self::$rowStats[$mfy_row] = array('min' => $mfy_min, 'max' => $mfy_max);

        return $rows;

    }

    function runPage()
    {
        ini_set('memory_limit','-1');
        set_time_limit(3600 * 24);


        CApp::forceSecureConnection();
        $tpl = CApp::instance()->template();

        $hadError = false;

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


        $currentYear = date("Y");
        $yearStepper = $currentYear;
        $yearArray = array();
        $yearArray[$yearStepper] = $yearStepper;
        for ($x = 0; $x < 5; $x++) {
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

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::name => "months_back",
            CForm::required => true,
            CForm::options => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,30,21,22,23,24),
            CForm::default_value => 11
        ));

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::name => "months_skipped",
            CForm::required => true,
            CForm::options => array(1,2,3,4),
            CForm::default_value => 12
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


        $export = false;
        if (isset($_POST['export']) && $_POST['export'] == 'xlsx') {
            $export = true;
        }

        if (isset($_REQUEST["run_report"]) || $export)
        {

            $monthsBack = $Form->value('months_back') + 1;
            $monthsSkipped = $Form->value('months_skipped') + 1;

            $month = $Form->value('month') + 1;
            $year = $Form->value('year');
            $monthname = $month_array[$month - 1];

            $selectedMonthTS = mktime(0, 0, 0, $month, 1, $year);
            $selectedMenuID = CMenu::getMenuIDByAnchorDate(date("Y-m-d", $selectedMonthTS));

            $columnDescs = array();
            $columnDescs['A'] = array("width" => 5);
            $columnDescs['B'] = array("width" => 15);
            $columnDescs['C'] = array("width" => 20);
            $columnDescs['D'] = array("width" => 20);
            $columnDescs['E'] = array("width" => 8);
            $columnDescs['F'] = array("width" => 30, 'decor' => 'subtotal');

            $labels = array("ID", "Home Office Id", "Store Name", "City", "State", "Metric");



            $rows = $this->summarize($selectedMenuID, $monthsBack, $monthsSkipped, $columnDescs, $labels);

            $this->printSheet($rows, $export, $tpl, $labels, $columnDescs );

        }

            $titleString = "Menu Skipping Report";
            $tpl->assign('titleString', $titleString);


            $formArray = $Form->render();
            $tpl->assign('store', $store);

            $tpl->assign('form_array', $formArray);


        }

        function printSheet($rows, $export, $tpl, $labels ,$columnDescs)
        {
            PHPExcel_Shared_String::setThousandsSeparator(",");

            $callbacks = array('cell_callback' => 'ReportCellCallback' , 'row_callback' => 'ReportRowCallback', );

            if ($export)
            {

                $tpl->assign('col_descriptions', $columnDescs);
                $tpl->assign('file_name', makeTitle("Skipped Menu Report", "", ""));
                $tpl->assign('rows', $rows);
                $tpl->assign('rowcount', count($rows));
                $tpl->assign('excel_callbacks', $callbacks);
                $tpl->assign('labels', $labels);


                $_GET['export'] = 'xlsx';

            } else {
                list($css, $html) = writeExcelFile("Test", $labels, $rows, true, false, $columnDescs, false, $callbacks, false, true);

                echo "<html><head>";
                echo $css;
                echo "</head><body>";

                echo $html;
                echo "</body></html>";
                exit;
            }

        }
}
?>