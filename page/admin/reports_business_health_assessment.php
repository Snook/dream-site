<?php
include("includes/CPageAdminOnly.inc");
require_once("includes/CDashboardReportMenuBased.inc");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');



function getPerformanceColor($rowDef, $value)
{
    $range = $rowDef['max'] - $rowDef['min'];

    if ($rowDef['positive_is_better'])
    {
        $pos = (($value - $rowDef['min']) / $range) * 100;
    }
    else
    {
        $pos = (($rowDef['max'] - $value) / $range) * 100;
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

function BHADetailReportFinalRenderCallback($sheet, $rows)
{

    $sheet->getRowDimension(2)->setRowHeight(65);


    $styleArray = array('font' => array( 'bold' => true),
        'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000')),
            'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
            'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
            'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') )));
    $sheet->getStyle("A2:D2")->applyFromArray($styleArray);


    $objRichText = new PHPExcel_RichText();

    $objPayable = $objRichText->createTextRun('Business Health Assessment Detail');
    $objPayable->getFont()->setBold(true);
    $objPayable->getFont()->setSize(24);

    $objPayable5 = $objRichText->createTextRun("\r" . page_admin_reports_business_health_assessment::$titleSpanString);
    $objPayable5->getFont()->setBold(true);
    $objPayable5->getFont()->setSize(18);

    $sheet->getStyle('A2')->getAlignment()->setWrapText(true);
    $sheet->getStyle('A2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->getCell('A2')->setValue($objRichText);

    // ----------------------

    $objRichText2 = new PHPExcel_RichText();
    // $objRichText->createText('This invoice is ');

    $objPayable = $objRichText2->createTextRun('Green Cells - Indicate Higher Performance of the Time Span');
    $objPayable->getFont()->setBold(true);
    $objPayable->getFont()->setSize(12);
    $objPayable->getFont()->setColor( new PHPExcel_Style_Color( "9962BD7A") );

    $objPayable2 = $objRichText2->createTextRun("\rYellow/Orange Cells - Indicate Mid-Range Performance");
    $objPayable2->getFont()->setBold(true);
    $objPayable2->getFont()->setSize(12);
    $objPayable2->getFont()->setColor( new PHPExcel_Style_Color( "99FCC87C" ) );

    $objPayable3 = $objRichText2->createTextRun("\rRed Cells - Indicate Lower Performance of the Time Span");
    $objPayable3->getFont()->setBold(true);
    $objPayable3->getFont()->setSize(12);
    $objPayable3->getFont()->setColor( new PHPExcel_Style_Color( "99F7686A") );


    $sheet->getStyle('E2')->getAlignment()->setWrapText(true);
    $sheet->getStyle('E2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->getCell('E2')->setValue($objRichText2);



}

function BHAReportFinalRenderCallback($sheet, $rows)
{

    $sheet->getRowDimension(2)->setRowHeight(80);


    $styleArray = array('font' => array( 'bold' => true),
        'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000')),
            'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
            'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
            'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') )));
    $sheet->getStyle("A2:D2")->applyFromArray($styleArray);


    $objRichText = new PHPExcel_RichText();

    $objPayable = $objRichText->createTextRun('Business Health Assessment');
    $objPayable->getFont()->setBold(true);
    $objPayable->getFont()->setSize(24);

    $objPayable5 = $objRichText->createTextRun("\r" . page_admin_reports_business_health_assessment::$titleSpanString);
    $objPayable5->getFont()->setBold(true);
    $objPayable5->getFont()->setSize(18);

    $objRichText->createText("\rThe information below is reporting your store’s performance and is not compared to any other store.");
    $sheet->getStyle('A2')->getAlignment()->setWrapText(true);
    $sheet->getStyle('A2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->getCell('A2')->setValue($objRichText);

    // ----------------------

    $objRichText2 = new PHPExcel_RichText();
    // $objRichText->createText('This invoice is ');

    $objPayable = $objRichText2->createTextRun('Green Cells - Indicate Higher Performance of the Time Span');
    $objPayable->getFont()->setBold(true);
    $objPayable->getFont()->setSize(12);
    $objPayable->getFont()->setColor( new PHPExcel_Style_Color( "9962BD7A") );

    $objPayable2 = $objRichText2->createTextRun("\rYellow/Orange Cells - Indicate Mid-Range Performance");
    $objPayable2->getFont()->setBold(true);
    $objPayable2->getFont()->setSize(12);
    $objPayable2->getFont()->setColor( new PHPExcel_Style_Color( "99FCC87C" ) );

    $objPayable3 = $objRichText2->createTextRun("\rRed Cells - Indicate Lower Performance of the Time Span");
    $objPayable3->getFont()->setBold(true);
    $objPayable3->getFont()->setSize(12);
    $objPayable3->getFont()->setColor( new PHPExcel_Style_Color( "99F7686A") );


    $sheet->getStyle('E2')->getAlignment()->setWrapText(true);
    $sheet->getStyle('E2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->getCell('E2')->setValue($objRichText2);



}

function BHAReportCellCallback($sheet, $colName, $datum, $col, $row)
{
    if (!empty(page_admin_reports_business_health_assessment::$rowDefs[$row]) and $col > 'D') {

        if ((page_admin_reports_business_health_assessment::$rowDefs[$row]['name'] == 'COGS' || page_admin_reports_business_health_assessment::$rowDefs[$row]['name'] == 'labor_costs') && empty($datum))
        {
            $styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD'))));

            $sheet->getStyle("$col$row")->applyFromArray($styleArray);
        }
        else
        {


            $sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(page_admin_reports_business_health_assessment::$rowDefs[$row]['type']);


            $styleArray = array('font' => array('bold' => true), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => getPerformanceColor(page_admin_reports_business_health_assessment::$rowDefs[$row], $datum)),
                'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD'))));

            $sheet->getStyle("$col$row")->applyFromArray($styleArray);
        }
    }

}

function BHADetailReportCellCallback($sheet, $colName, $datum, $col, $row)
{
    if (!empty(page_admin_reports_business_health_assessment::$rowDefs[$row]) and ($col > 'D' || strlen($col) > 1))
    {
        $sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(page_admin_reports_business_health_assessment::$rowDefs[$row]['type']);

        if ((page_admin_reports_business_health_assessment::$rowDefs[$row]['name'] == 'COGS' || page_admin_reports_business_health_assessment::$rowDefs[$row]['name'] == 'labor_costs') && empty($datum))
        {
            $styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD'))));

            $sheet->getStyle("$col$row")->applyFromArray($styleArray);
        }
        else
        {
            $styleArray = array('font' => array('bold' => true), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => getPerformanceColor(page_admin_reports_business_health_assessment::$rowDefs[$row], $datum)),
                'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
                    'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD'))));

            $sheet->getStyle("$col$row")->applyFromArray($styleArray);
        }
    }

}

function BHAReportRowCallback($sheet, $data, $row, $bottomRightExtent)
{
    if ($row > 4)
    {
        $sheet->getRowDimension($row)->setRowHeight(25.25);
        $sheet->getStyle("A$row:$bottomRightExtent")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle("E$row:$bottomRightExtent")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }


    if ($row == 10 || $row == 14 or $row == 17)
    {

        $styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000'))));
        $sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
    }
}

function BHADetailReportRowCallback($sheet, $data, $row, $bottomRightExtent)
{
    if ($row > 4)
    {
        $sheet->getRowDimension($row)->setRowHeight(22);
        $sheet->getStyle("A$row:$bottomRightExtent")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle("E$row:$bottomRightExtent")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }


    if ($row == 10 || $row == 14 or $row == 17)
    {

        $styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000'))));
        $sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
    }
}

class page_admin_reports_business_health_assessment extends CPageAdminOnly
{

    private $currentStore = null;
    private $multiStoreOwnerStores = false;
    public static $titleSpanString = "";

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}


    public static $rowDefs = array(
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => array('name' => 'average_annual_visits', 'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_0, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        6 => array('name' => 'lifestyle_guest_count', 'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        7 => array('name' => 'guest_count_existing_regular', 'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        8 => array('name' => 'guest_count_new', 'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        9 => array('name' => 'guest_count_new_converted', 'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        10 => array('name' => 'retention_rate', 'type' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        11 => array('name' => 'avg_ticket_by_guest_existing_regular', 'type' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        12 => array('name' => 'addon_sales_by_regular_existing_guest', 'type' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        13 => array('name' => 'dinner_dollars_used', 'type' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => false),
        14 => array('name' => 'percentage_mfy_sales', 'type' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => false),
        15 => array('name' => 'total_agr', 'type' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => true),
        16 => array('name' => 'COGS', 'type' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => false),
        17 => array('name' => 'labor_costs', 'type' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE, 'min' => 1000000000, 'max' => 0, 'positive_is_better' => false)
    );


    function runHomeOfficeManager()
    {
        $this->runPage();
    }

    function runSiteAdmin()
    {
        $this->runPage();
    }

    function runFranchiseOwner()
    {
        $this->currentStore = CApp::forceLocationChoice();
        $this->runPage();
    }


    function CalcMinAndMax($rows)
    {
        $count = 5;

        foreach($rows as $name => $data)
        {
            $ignoreEmptyValues = false;
            if ($name == "COGS" || $name == "labor_costs")
            {
                $ignoreEmptyValues = true;
            }

            $min = 1000000000;
            $max = -1000000000;

            foreach ($data as $year => $value)
            {
                if ($ignoreEmptyValues && empty($value))
                {
                    continue;
                }

                if ($year > 2000)
                {
                    if ($value < $min)
                    {
                        $min = $value;
                    }
                    if ($value > $max)
                    {
                        $max = $value;
                    }
                }
            }


            self::$rowDefs[$count]['min'] = $min;
            self::$rowDefs[$count]['max'] = $max;
            $count++;
        }
    }

    function CalcMinAndMaxDetail($rows)
    {
        $count = 5;

        foreach($rows as $name => $data)
        {

            $ignoreEmptyValues = false;
            if ($name == "COGS" || $name == "labor_costs")
            {
                $ignoreEmptyValues = true;
            }

            $min = 1000000000;
            $max = -1000000000;

            foreach ($data as $id => $value)
            {
                if ($ignoreEmptyValues && empty($value))
                {
                    continue;
                }

                if ($id > 3)
                {
                    if ($value < $min)
                    {
                        $min = $value;
                    }
                    if ($value > $max)
                    {
                        $max = $value;
                    }
                }
            }


            self::$rowDefs[$count]['min'] = $min;
            self::$rowDefs[$count]['max'] = $max;
            $count++;
        }
    }

    function formArrays($startMonth, $endMonth, $startYear, $endYear, $isRollup)
    {
        $labels = array("ID", "Store Name", "ST", "Key Performance Indicator");
        $expandedArray = array();
        $RollupArray = array();

        while ($startYear <= $endYear) {
            $expandedArray[$startYear] = array();
            $RollupArray[] = $startYear;

            if ($isRollup) {
                $labels[] = $startYear . " Monthly Average";
            }

            $curMonth = $startMonth;

            while ($curMonth <= $endMonth) {
                $expandedArray[$startYear][$curMonth] = array();

                if (!$isRollup) {
                    $labels[] = date("M Y", mktime(0, 0, 0, $curMonth, 1, $startYear));
                }


                $curMonth++;
            }

            $startYear++;

        }

        return array($labels, $expandedArray, $RollupArray);
    }


    function getTimeSpanArrays($reportType, $Form)
    {

        $SpanDescription = "";

        $yearsBack = $Form->value('years_back_popup');
        $isRollup = ($Form->value('report_depth') == 'dp_roll_up');

        $curMenuID = CMenu::getCurrentMenuId();
        $curMenu = new DAO();
        $curMenu->query("select menu_start from menu where id = $curMenuID");
        $curMenu->fetch();
        $curMenuMonth = date("n", strtotime($curMenu->menu_start));

        switch ($reportType) {
            case 'dt_year_to_date':
                $curYear = date("Y");
                $curMonth = date("n");
                if ($curMonth == 1) {
                    $startMonth = 1;
                    $endMonth = 12;
                    $startYear = $curYear - (1 + $yearsBack);
                    $endYear = $curYear - 1;
                } else {
                    $startMonth = 1;
                    $endMonth = $curMonth - 1;
                    $startYear = $curYear - $yearsBack;
                    $endYear = $curYear;
                }

                $monthStr = date("F", strtotime("2019-$endMonth-1"));
                $SpanDescription = "Year to Date (January through $monthStr) - $startYear through $endYear";

                break;
            case 'dt_year':
                $startMonth = 1;
                $endMonth  = 12;
                $startYear = $Form->value('year_popup') - $yearsBack;
                $endYear = $Form->value('year_popup');
                if ($startYear < 2012) $startYear = 2012;

                $SpanDescription = "Full Years $startYear through $endYear";
                break;
            case 'dt_quarter':
                $selectedQuarter = $Form->value('quarter_popup');
                $selectedQuarter++;
                $quarterString = "";
                if ($selectedQuarter == 1)
                {
                    $quarterString = "1st";
                    if ($curMenuMonth > 3)
                    {
                        // can use current year
                        $endYear = date("Y");
                    }
                    else
                    {
                        $endYear = date("Y") - 1;
                    }

                    $startMonth = 1;
                    $endMonth = 3;
                    $startYear = $endYear - $yearsBack;
                }
                else if ($selectedQuarter == 2)
                {
                    $quarterString = "2nd";
                    if ($curMenuMonth > 6)
                    {
                        // can use current year
                        $endYear = date("Y");
                    }
                    else
                    {
                        $endYear = date("Y") - 1;
                    }

                    $startMonth = 4;
                    $endMonth = 6;
                    $startYear = $endYear - $yearsBack;
                }
                else if ($selectedQuarter == 3)
                {
                    $quarterString = "3rd";

                    if ($curMenuMonth > 9)
                    {
                        // can use current year
                        $endYear = date("Y");
                    }
                    else
                    {
                        $endYear = date("Y") - 1;
                    }

                    $startMonth = 7;
                    $endMonth = 9;
                    $startYear = $endYear - $yearsBack;
                }
                else if ($selectedQuarter == 4)
                {
                    $quarterString = "4th";

                    $endYear = date("Y") - 1;
                    $startMonth = 7;
                    $endMonth = 9;
                    $startYear = $endYear - $yearsBack;
                }
                $SpanDescription = "$quarterString Quarter - $startYear through $endYear";

                break;
            case 'dt_month':
                $selectedMonth =  $Form->value('month_popup');
                $selectedMonth++;
                if ( $selectedMonth >= $curMenuMonth)
                {
                    $endYear = date("Y") - 1;
                }
                else
                {
                    $endYear = date("Y");
                }
                $startMonth = $selectedMonth;
                $endMonth = $selectedMonth;
                $startYear = $endYear - $yearsBack;

                $monthStr = date("F", strtotime("2019-$endMonth-1"));
                $SpanDescription = "Menu/Month of $monthStr - $startYear through $endYear";


                break;
            case 'dt_semi_annual':
                if ($curMenuMonth > 6)
                {
                    $endYear = date("Y");
                }
                else
                {
                    $endYear = date("Y") - 1;
                }
                $startMonth = 1;
                $endMonth = 6;
                $startYear = $endYear - $yearsBack;

                $monthStr = date("F", strtotime("2019-$endMonth-1"));
                $SpanDescription = "First Half - $startYear through $endYear";

                break;
        }

        if ($startYear < 2012) $startYear = 2012;

        self::$titleSpanString = $SpanDescription;

        return $this->formArrays($startMonth, $endMonth, $startYear, $endYear, $isRollup);
    }

    static $rowTemplate = array("average_annual_visits" => 0, "lifestyle_guest_count" => 0, "guest_count_existing_regular" => 0, "guest_count_new" => 0, "guest_count_new_converted" => 0,
        "retention_rate" => 0, "avg_ticket_by_guest_existing_regular" => 0, "addon_sales_by_regular_existing_guest" => 0, "dinner_dollars_used" => 0, "percentage_mfy_sales" => 0,
        "total_agr" => 0, "COGS" => 0, "labor_costs" => 0);

    static $metricNameMap = array("average_annual_visits" => "Average Annual Visits per Guest", "lifestyle_guest_count" => "Lifestyle Guest Count (Three Consecutive Regular Orders)",
        "guest_count_existing_regular" => "Existing Guest Count (Regular Orders)", "guest_count_new" => "New Guests per Month", "guest_count_new_converted" => "New Guests that Converted to a Full Order",
        "retention_rate" => "Retention Rate", "avg_ticket_by_guest_existing_regular" => "Average Ticket - Existing Guests (Regular Orders)", "addon_sales_by_regular_existing_guest" => "Sides & Sweets Revenue per Guest",
        "dinner_dollars_used" => "Dinner Dollars Used per Guest", "percentage_mfy_sales" => "Made for You as a % of Total Sales (AGR)",
        "total_agr" => "Monthly Sales (AGR)", "COGS" => "COGS - Cost of Goods Sold (Food & Packaging) as a % of Total Sales (AGR)", "labor_costs" => "Labor (Employee & Manager Wages) as a % of Total Sales (AGR)");

    function getHumanReadableMetricName($metric)
    {
        return self::$metricNameMap[$metric];
    }

    function getRollup($sourceData, $yearArray, $RowHeader)
    {
        $destArray = array();

        foreach(self::$rowTemplate as $thisMetric => $data)
        {
            $destArray[$thisMetric] = $RowHeader;
            $destArray[$thisMetric][] = $this->getHumanReadableMetricName($thisMetric);

            $ignoreEmptyValues = false;
            if ($thisMetric == "COGS" || $thisMetric == "labor_costs")
            {
                $ignoreEmptyValues = true;
            }


            foreach ($yearArray as $thisYear)
            {
                $destArray[$thisMetric][$thisYear] = 0;
                $monthCount = count($sourceData[$thisYear]);

                $emptyMonths = 0;

                foreach($sourceData[$thisYear] as $month => $thisMonthSource)
                {
                    if ($ignoreEmptyValues && empty($thisMonthSource[$thisMetric]))
                    {
                        $emptyMonths++;
                    }


                    $destArray[$thisMetric][$thisYear] += $thisMonthSource[$thisMetric];
                }

                if ($monthCount - $emptyMonths == 0)
                {
                    $destArray[$thisMetric][$thisYear] = 0;
                }
                else
                {
                    $destArray[$thisMetric][$thisYear] /= ($monthCount - $emptyMonths);
                }


            }
        }

        return $destArray;
    }

    function formatExpandedData($expandedArray, $RollupArray, $RowHeader)
    {
        $destArray = array();

        foreach(self::$rowTemplate as $thisMetric => $data)
        {
            $destArray[$thisMetric] = $RowHeader;
            $destArray[$thisMetric][] = $this->getHumanReadableMetricName($thisMetric);

            foreach ($expandedArray as $year => $thisYear)
            {
                foreach($expandedArray[$year] as $month => $thisMonthSource)
                {
                    $destArray[$thisMetric][] += $thisMonthSource[$thisMetric];
                }

            }
        }

        return $destArray;
    }


    function getExpandedData(&$yearArray, $store)
    {
        foreach($yearArray as $year => &$thisYear)
        {
            foreach($thisYear as $month => &$thisMonth)
            {
                $anchorDate = date("Y-m-1", mktime(0,0,0, $month, 1, $year));

                // 1) Metrics from dashboard_metrics_guests_by_menu
                $guestMetrics = new DAO();
                $guestMetrics->query("select orders_count_all, average_annual_visits, lifestyle_guest_count, guest_count_existing_regular, dinner_dollars_used, new_guests_with_follow_up, guest_count_total,
                                        guest_count_new_regular + guest_count_new_taste + guest_count_new_intro + guest_count_new_fundraiser as guest_count_new,
                                        retention_count / guest_count_existing_regular as retention_rate from dashboard_metrics_guests_by_menu where store_id = $store and date = '$anchorDate' and is_deleted = 0");
                $guestMetrics->fetch();

                $tempArray = self::$rowTemplate;

                $tempArray['average_annual_visits'] = $guestMetrics->average_annual_visits;
                $tempArray['lifestyle_guest_count'] = $guestMetrics->lifestyle_guest_count;
                $tempArray['guest_count_existing_regular'] = $guestMetrics->guest_count_existing_regular;
                $tempArray['guest_count_new'] = $guestMetrics->guest_count_new;
                $tempArray['retention_rate'] = $guestMetrics->retention_rate;
                $tempArray['guest_count_new_converted'] = $guestMetrics->new_guests_with_follow_up;
                $tempArray['dinner_dollars_used'] = ($guestMetrics->dinner_dollars_used / $guestMetrics->guest_count_total);

                // 2) Metrics from dashboard_metrics_agr_by_menu
                $AgrMetrics = new DAO();
                $AgrMetrics->query("select avg_ticket_by_guest_existing_regular, addon_sales_total as addon_sales, agr_by_session_mfy / total_agr as percentage_mfy_sales,
                                        total_agr from dashboard_metrics_agr_by_menu where store_id = $store and date = '$anchorDate' and is_deleted = 0");
                $AgrMetrics->fetch();

                $tempArray['avg_ticket_by_guest_existing_regular'] = $AgrMetrics->avg_ticket_by_guest_existing_regular;

                if (empty($guestMetrics->orders_count_all))
                {
                    $tempArray['addon_sales_by_regular_existing_guest']  = 0;
                }
                else
                {
                    $tempArray['addon_sales_by_regular_existing_guest'] = $AgrMetrics->addon_sales / $guestMetrics->orders_count_all;
                }

                $tempArray['percentage_mfy_sales'] = $AgrMetrics->percentage_mfy_sales;

                $tempArray['total_agr'] = $AgrMetrics->total_agr;

                // 3) Metrics from p and l input

                $PandLMetrics = new DAO();
                $PandLMetrics->query("select employee_wages, manager_salaries, cost_of_goods_and_services from store_monthly_profit_and_loss where store_id = $store and date = '$anchorDate' and is_deleted = 0");
                $PandLMetrics->fetch();

                $tempArray['COGS'] = ($PandLMetrics->cost_of_goods_and_services / $AgrMetrics->total_agr);
                $tempArray['labor_costs'] = ($PandLMetrics->employee_wages + $PandLMetrics->manager_salaries) / $AgrMetrics->total_agr;


                $yearArray[$year][$month] = $tempArray;

/*
                dashboard_metrics_guests_by_menu
                    average_annual_visits
                    lifestyle_guest_count
                    guest_count_existing_regular
                        guest_count_new_regular
                        guest_count_new_taste
                        guest_count_new_intro
                        guest_count_new_fundraiser
                    // new guest that converts
                     retention_rate =   retention_count / guest_count_existing_regular
                 dashboard_metrics_agr_by_menu
                    avg_ticket_by_guest_existing_regular
                     addon_sales_total / guest_count_existing_regular
                     // dinner dollars used per guest
                     agr_by_session_mfy / total_agr
                     total_agr
                     // COGS - from P and L ??
                     // employee wages + manager wages from P and L
*/



            }
        }

  //      echo "<pre>" . print_r($yearArray, true) . "</pre>";
    }

    function  exportRollupXLSX($tpl, $RollupArray, $labels, $storeObj)
    {

        $columnDescs = array();
        $col = 'A';
        $colSecondChar = '';
        $thirdSecondChar = '';

        //ID
        $columnDescs[$col] = array(
            'align' => 'center',
            'width' => '6'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);
        //Store Name
        $columnDescs[$col] = array(
            'align' => 'center',
            'width' => 'auto'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);
        //State ID
        $columnDescs[$col] = array(
            'align' => 'center',
            'width' => '5'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);
        //Metric
        $columnDescs[$col] = array(
            'align' => 'left',
            'width' => 'auto'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);

        foreach($RollupArray as $year => $data)
        {
            $columnDescs[$col] = array(
                'align' => 'right',
                'width' => '10'
            );
            incrementColumn($thirdSecondChar, $colSecondChar, $col);
        }

        $numRows = count($RollupArray);

        $tpl->assign('title_rows', array("placeholder","",""));
        $tpl->assign('override_values', array('suppress_auto_filter' => true, 'pane_freeze_cell' => "E5"));

        $tpl->assign('file_name', makeTitle("BHA Report", $storeObj->store_name, self::$titleSpanString));


        $_GET['export'] = "xlsx";
        $tpl->assign('labels', $labels);
        $tpl->assign('rows', $RollupArray);
        $tpl->assign('rowcount', $numRows);
        $tpl->assign('col_descriptions', $columnDescs);
        $callbacks = array('cell_callback' => 'BHAReportCellCallback', 'row_callback' => 'BHAReportRowCallback', 'final_render' => 'BHAReportFinalRenderCallback');
        $tpl->assign('excel_callbacks', $callbacks);
        return;

    }

    function  exportDetailXLSX($tpl, $detailArray, $labels)
    {

        $columnDescs = array();
        $col = 'A';
        $colSecondChar = '';
        $thirdSecondChar = '';

        //ID
        $columnDescs[$col] = array(
            'align' => 'center',
            'width' => '6'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);
        //Store Name
        $columnDescs[$col] = array(
            'align' => 'center',
            'width' => 'auto'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);
        //State ID
        $columnDescs[$col] = array(
            'align' => 'center',
            'width' => '5'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);
        //Metric
        $columnDescs[$col] = array(
            'align' => 'left',
            'width' => 'auto'
        );
        incrementColumn($thirdSecondChar, $colSecondChar, $col);

        foreach($detailArray as $year => $data)
        {
            $columnDescs[$col] = array(
                'align' => 'right',
                'width' => '10'
            );
            incrementColumn($thirdSecondChar, $colSecondChar, $col);
        }

        $tpl->assign('file_name', makeTitle("BHA Detail Report", $storeObj->store_name, self::$titleSpanString));

        $numRows = count($detailArray);
        $tpl->assign('title_rows', array("placeholder","",""));
        $tpl->assign('override_values', array('suppress_auto_filter' => true, 'pane_freeze_cell' => "E5"));

        $_GET['export'] = "xlsx";
        $tpl->assign('labels', $labels);
        $tpl->assign('rows', $detailArray);
        $tpl->assign('rowcount', $numRows);
        $tpl->assign('col_descriptions', $columnDescs);
        $callbacks = array('cell_callback' => 'BHADetailReportCellCallback', 'final_render' => 'BHADetailReportFinalRenderCallback', 'row_callback' => 'BHADetailReportRowCallback');
        $tpl->assign('excel_callbacks', $callbacks);

        return;

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


        if ($this->currentStore)
        { // fadmins
            $store = $this->currentStore;
        }
        else
        {
            $Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : '';
            $Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
            CForm::onChange => 'selectStoreTR',
            CForm::allowAllOption => false,
            CForm::showInactiveStores => false,
            CForm::name => 'store'));

            $store = $Form->value('store');
        }

        $Form->DefaultValues['report_type'] = 'dt_year_to_date';

        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "report_type",
            CForm::value => 'dt_year_to_date'));

        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "report_type",
            CForm::value => 'dt_year'));


        // build year list
        $thisYear = date("Y");
        $startYear = 2016;
        $yearOptions = array();
        while($startYear < $thisYear)
        {
            $yearOptions[$startYear] = $startYear;
            $startYear++;
        }

        // build year list
        $numYearsBackPossible = $thisYear - 2012;
        $yearsBack = array();

        for($x= 1; $x <= $numYearsBackPossible; $x++)
        {
            $yearsBack[$x] = $x;
        }

        $Form->DefaultValues['years_back_popup'] = 5;

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::onChangeSubmit => false,
            CForm::allowAllOption => false,
            CForm::options => $yearsBack,
            CForm::name => 'years_back_popup'
        ));


        $Form->DefaultValues['year_popup'] = $thisYear - 1;

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::onChangeSubmit => false,
            CForm::allowAllOption => false,
            CForm::options => $yearOptions,
            CForm::name => 'year_popup'
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

        $Form->DefaultValues['month_popup'] = 0;


        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::onChangeSubmit => false,
            CForm::allowAllOption => false,
            CForm::options => $month_array,
            CForm::name => 'month_popup'
        ));



        $quarter_array = array(
            '1st Quarter',
            '2nd Quarter',
            '3rd Quarter',
            '4th Quarter'
        );

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::onChangeSubmit => false,
            CForm::allowAllOption => false,
            CForm::options => $quarter_array,
            CForm::name => 'quarter_popup'
        ));


        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "report_type",
            CForm::value => 'dt_quarter'));

        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "report_type",
            CForm::value => 'dt_semi_annual'));

        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "report_type",
            CForm::value => 'dt_month'));

        $Form->DefaultValues['report_depth'] = 'dp_roll_up';


        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "report_depth",
            CForm::value => 'dp_month'));

        $Form->AddElement(array(CForm::type=> CForm::RadioButton,
            CForm::name => "report_depth",
            CForm::value => 'dp_roll_up'));


        $isRollup = ($Form->value('report_depth') == 'dp_roll_up');

        $tpl->assign('showStoreSelector', $showStoreSelector);


		$reportType = $Form->value('report_type');

		$Form->AddElement(array(CForm::type=> CForm::Hidden,
				CForm::name => "store_id",
				CForm::value => $store));


		if (isset($_POST['run_report']))
        {
            $storeObj = DAO_CFactory::create('store');
            $storeObj->id = $store;

            if (empty($storeObj->id))
            {
                $storeObj->id = 244;
                $store = 244;
            }
            $storeObj->find(true);

            $RowHeader = array($storeObj->home_office_id, $storeObj->store_name, $storeObj->state_id);

            $reportType = $Form->value('report_type');

            list($labels, $expandedArray, $RollupArray) = $this->getTimeSpanArrays($reportType, $Form);

            $this->getExpandedData($expandedArray, $store);


            if ($isRollup)
            {
                $rows = $this->getRollup($expandedArray, $RollupArray, $RowHeader);

                $this->CalcMinAndMax($rows);
				CLog::RecordReport("Business Health Assessment Rollup", "Store: $store" );

                $this->exportRollupXLSX($tpl, $rows, $labels, $storeObj);

                return;
            }
            else
            {


                $rows = $this->formatExpandedData($expandedArray, $RollupArray, $RowHeader);
                $this->CalcMinAndMaxDetail($rows);
				CLog::RecordReport("Business Health Assessment Detail", "Store: $store" );

                $this->exportDetailXLSX($tpl, $rows, $labels);
                return;
            }

        }


		$titleString = "Business Health Assessment";
		$tpl->assign('titleString', $titleString);


		$formArray = $Form->render();
		$tpl->assign('store', $store);

		$tpl->assign('form_array', $formArray);


	}
}
?>