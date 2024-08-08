<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/CDashboardReportMenuBased.inc");
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');

function sortByPointValueTotal($a, $b)
{
	if ($a['total_points'] == $b['total_points'])
	{
		return 0;
	}

	return ($b['total_points'] >  $a['total_points']  ? 1 : -1);

}
function sortByPointValue_guest_count_new($a, $b)
{
	if ($a['guest_count_new_points'] == $b['guest_count_new_points'])
	{
		return 0;
	}

	return ($b['guest_count_new_points'] >  $a['guest_count_new_points']  ? 1 : -1);
}

function sortByPointValue_guest_count_new_converted($a, $b)
{
	if ($a['guest_count_new_converted_points'] == $b['guest_count_new_converted_points'])
	{
		return 0;
	}

	return ($b['guest_count_new_converted_points'] >  $a['guest_count_new_converted_points']  ? 1 : -1);
}

function sortByPointValue_retained_guest_count($a, $b)
{
	if ($a['retained_guest_count_points'] == $b['retained_guest_count_points'])
	{
		return 0;
	}

	return ($b['retained_guest_count_points'] >  $a['retained_guest_count_points']  ? 1 : -1);
}

function sortByPointValue_total_agr($a, $b)
{
	if ($a['total_agr_points'] == $b['total_agr_points'])
	{
		return 0;
	}

	return ($b['total_agr_points'] >  $a['total_agr_points']  ? 1 : -1);
}

function sortByPointValue_agr_growth($a, $b)
{
	if ($a['agr_growth_points'] == $b['agr_growth_points'])
	{
		return 0;
	}

	return ($b['agr_growth_points'] >  $a['agr_growth_points']  ? 1 : -1);
}


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

function BHAReportFinalRenderCallbackAllStores($sheet, $rows)
{
	$sheet->getRowDimension(2)->setRowHeight(80);


	$styleArray = array('font' => array( 'bold' => true),
						'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000')),
										   'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
										   'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') ),
										   'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK, 'color' => array('argb' => 'FF000000') )));
	$sheet->getStyle("A2:D2")->applyFromArray($styleArray);

	$objRichText = new PHPExcel_RichText();

	$objPayable = $objRichText->createTextRun('Cornerstone Movement 2021 Report');
	$objPayable->getFont()->setBold(true);
	$objPayable->getFont()->setSize(24);

	$objPayable5 = $objRichText->createTextRun("\r" . page_admin_reports_cornerstone_movement_contest::$titleSpanString);
	$objPayable5->getFont()->setBold(true);
	$objPayable5->getFont()->setSize(18);

	$sheet->getStyle('A2')->getAlignment()->setWrapText(true);
	$sheet->getStyle('A2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet->getCell('A2')->setValue($objRichText);





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
	$sheet->getStyle("A4:I9")->applyFromArray($styleArray);


    $objRichText = new PHPExcel_RichText();

    $objPayable = $objRichText->createTextRun('Cornerstone Movement 2021 Report');
    $objPayable->getFont()->setBold(true);
    $objPayable->getFont()->setSize(24);

    $objPayable5 = $objRichText->createTextRun("\r" . page_admin_reports_cornerstone_movement_contest::$titleSpanString);
    $objPayable5->getFont()->setBold(true);
    $objPayable5->getFont()->setSize(18);

    $sheet->getStyle('A2')->getAlignment()->setWrapText(true);
    $sheet->getStyle('A2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->getCell('A2')->setValue($objRichText);

    // ----------------------

    $objRichText2 = new PHPExcel_RichText();
    // $objRichText->createText('This invoice is ');

    $objPayable = $objRichText2->createTextRun('');
    $objPayable->getFont()->setBold(true);
    $objPayable->getFont()->setSize(12);
    $objPayable->getFont()->setColor( new PHPExcel_Style_Color( "9962BD7A") );

    $objPayable2 = $objRichText2->createTextRun("");
    $objPayable2->getFont()->setBold(true);
    $objPayable2->getFont()->setSize(12);
    $objPayable2->getFont()->setColor( new PHPExcel_Style_Color( "99FCC87C" ) );

    $objPayable3 = $objRichText2->createTextRun("");
    $objPayable3->getFont()->setBold(true);
    $objPayable3->getFont()->setSize(12);
    $objPayable3->getFont()->setColor( new PHPExcel_Style_Color( "99F7686A") );


    $sheet->getStyle('E2')->getAlignment()->setWrapText(true);
    $sheet->getStyle('E2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet->getCell('E2')->setValue($objRichText2);



}


function setMetricText($sheet, $row, $title)
{

	$objRichText = new PHPExcel_RichText();

	$objPayable = $objRichText->createTextRun($title);
	$objPayable->getFont()->setBold(true);
	$objPayable->getFont()->setSize(10);

	$objPayable5 = $objRichText->createTextRun(" " . page_admin_reports_cornerstone_movement_contest::$rowDefs[$row]['explanation']);
	$objPayable5->getFont()->setSize(10);

//	$sheet->getStyle("D$row")->getAlignment()->setWrapText(true);
	$sheet->getStyle("D$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle("D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	$sheet->getCell("D$row")->setValue($objRichText);


}


function BHAReportCellCallback($sheet, $colName, $datum, $col, $row)
{
	if (!empty(page_admin_reports_cornerstone_movement_contest::$rowDefs[$row]) and $col > 'D' and $col < 'H')
	{
		$sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(page_admin_reports_cornerstone_movement_contest::$rowDefs[$row]['type']);

		$styleArray = array('font' => array('bold' => true), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
																			 'startcolor' =>  array('argb' => 'FFFFFFFF')),
							'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
											   'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
											   'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
											   'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD'))));

		$sheet->getStyle("$col$row")->applyFromArray($styleArray);
	}

	if (!empty(page_admin_reports_cornerstone_movement_contest::$rowDefs[$row]) and $col > 'G' and $col < 'J')
	{

		$styleArray = array('font' => array('bold' => true), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
																			 'startcolor' =>  array('argb' => 'FFFFFFFF')),
							'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
											   'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
											   'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD')),
											   'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FFDDDDDD'))));

		$sheet->getStyle("$col$row")->applyFromArray($styleArray);
	}


	if ($col == 'D' && $row > 4 && $row  < 10)
	{
		setMetricText($sheet, $row, $datum);
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


}



class page_admin_reports_cornerstone_movement_contest extends CPageAdminOnly
{

	private $currentStore = null;
	private $multiStoreOwnerStores = false;
	public static $titleSpanString = "";
	public static $currentStoreHOID = null;


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
		5 => array(
			'name' => 'guest_count_new',
			'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
			'min' => 1000000000,
			'max' => 0,
			'positive_is_better' => true,
			'explanation' => "(1 Point for Every New Guest)"
		),
		6 => array(
			'name' => 'guest_count_new_converted',
			'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
			'min' => 1000000000,
			'max' => 0,
			'positive_is_better' => true,
			'explanation' => "(5 Points for Every Converted New Guest)"
		),
		7 => array(
			'name' => 'retained_guest_count',
			'type' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
			'min' => 1000000000,
			'max' => 0,
			'positive_is_better' => true,
			'explanation' => "(2 Points for Every Add’l Retained Guest over Same Month, Previous Year)"
		),
		8 => array(
			'name' => 'total_agr',
			'type' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD,
			'min' => 1000000000,
			'max' => 0,
			'positive_is_better' => true,
			'explanation' => "(3 Points for Every $5,000 in Revenue, starting at $25,000)"
		),
		9 => array(
			'name' => 'agr_growth',
			'type' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00,
			'min' => 1000000000,
			'max' => 0,
			'positive_is_better' => true,
			'explanation' => "(10 Points for Every 5% of YOY Monthly Growth)"
		)
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

	function runEventCoordinator()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runPage();
	}

	function runFranchiseManager()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runPage();
	}

	function runOpsLead()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runPage();
	}

	function formArrays($startMonth, $endMonth, $startYear, $endYear, $isRollup)
	{
		$labels = array(
			"ID",
			"Store Name",
			"ST",
			"Key Performance Indicator"
		);
		$expandedArray = array();
		$RollupArray = array();

		while ($startYear <= $endYear)
		{
			$expandedArray[$startYear] = array();
			$RollupArray[] = $startYear;

			if ($isRollup)
			{
				$labels[] = $startYear;
			}

			$curMonth = $startMonth;

			while ($curMonth <= $endMonth)
			{
				$expandedArray[$startYear][$curMonth] = array();

				if (!$isRollup)
				{
					$labels[] = date("M Y", mktime(0, 0, 0, $curMonth, 1, $startYear));
				}

				$curMonth++;
			}

			$startYear++;
		}

		$labels[] = "Incr/Decr";
		$labels[] = "Contest Points Earned This Month";
		$labels[] = "Points Rank This Month";


		return array(
			$labels,
			$expandedArray,
			$RollupArray,
			$startMonth
		);
	}

	function formAllStoresArrays($startMonth, $endMonth, $startYear, $endYear, $isRollup)
	{
		$MonthStr = date("M", mktime(0, 0, 0, $startMonth, 1, 2020));
		$lastYearStr = $MonthStr . " " . $startYear;
		$thisYearStr = $MonthStr . " " . $endYear;
		$labels = array(
			"ID",
			"Store Name",
			"ST",
			"New Guest Count $lastYearStr",
			"New Guest Count $thisYearStr",
			"New Guest Count Incr/Decr",
			"New Guest Count Points",
			"Converted New Guests Count $lastYearStr",
			"Converted New Guests Count $thisYearStr",
			"Converted New Guests Count Incr/Decr",
			"Converted New Guests Count Points",
			"Retained Guest Count $lastYearStr",
			"Retained Guest Count $thisYearStr",
			"Retained Guest Count Incr/Decr",
			"Retained Guest Count Points",
			"Total AGR $lastYearStr",
			"Total AGR $thisYearStr",
			"Total AGR Count Incr/Decr",
			"Total AGR Points",
			"AGR Growth",
			"AGR Growth Points",
			"Total Points"
		);

		$expandedArray = array();
		$RollupArray = array();

		while ($startYear <= $endYear)
		{
			$expandedArray[$startYear] = array();
			$RollupArray[] = $startYear;

			$curMonth = $startMonth;

			while ($curMonth <= $endMonth)
			{
				$expandedArray[$startYear][$curMonth] = array();
				$curMonth++;
			}

			$startYear++;
		}

		return array(
			$labels,
			$expandedArray,
			$RollupArray,
			$startMonth
		);
	}

	function getTimeSpanArrays($Form, $allStores = false)
	{


		$yearsBack = 1;
		$isRollup = true;

		$selectedMonth = $Form->value('month_popup');
		$selectedMonth++;

		$endYear = $Form->value('year_popup');
		$startYear = $endYear - 1;

		$startMonth = $selectedMonth;
		$endMonth = $selectedMonth;

		$monthStr = date("F", strtotime("2019-$endMonth-1"));
		$SpanDescription = "Menu/Month of $monthStr - $endYear";

		self::$titleSpanString = $SpanDescription;

		if ($allStores)
		{
			return $this->formAllStoresArrays($startMonth, $endMonth, $startYear, $endYear, true);
		}

		return $this->formArrays($startMonth, $endMonth, $startYear, $endYear, true);
	}

	static $rowTemplate = array(
		"guest_count_new" => 0,
		"guest_count_new_converted" => 0,
		"retained_guest_count" => 0,
		"total_agr" => 0,
		"agr_growth" => 0
	);

	static $allStoreRowTemplate = array(
		"guest_count_new_last_year" => 0,
		"guest_count_new_this_year" => 0,
		"guest_count_new_delta" => 0,
		"guest_count_new_points" => 0,

		"guest_count_new_converted_last_year" => 0,
		"guest_count_new_converted_this_year" => 0,
		"guest_count_new_converted_delta" => 0,
		"guest_count_new_converted_points" => 0,

		"retained_guest_count_last_year" => 0,
		"retained_guest_count_this_year" => 0,
		"retained_guest_count_delta" => 0,
		"retained_guest_count_points" => 0,

		"total_agr_last_year" => 0,
		"total_agr_this_year" => 0,
		"total_agr_delta" => 0,
		"total_agr_points" => 0,

		"agr_growth" => 0,
		"agr_growth_points" => 0,
		'total_points' => 0
	);

	static $metricNameMap = array(
		"average_annual_visits" => "Average Annual Visits per Guest",
		"lifestyle_guest_count" => "Lifestyle Guest Count (Three Consecutive Regular Orders)",
		"guest_count_existing_regular" => "Existing Guest Count (Regular Orders)",
		"guest_count_new" => "New Guests per Month",
		"guest_count_new_converted" => "New Guests that Converted to a Full Order",
		"retention_rate" => "Retention Rate",
		"avg_ticket_by_guest_existing_regular" => "Average Ticket - Existing Guests (Regular Orders)",
		"addon_sales_by_regular_existing_guest" => "Sides & Sweets Revenue per Guest",
		"dinner_dollars_used" => "Dinner Dollars Used per Guest",
		"percentage_mfy_sales" => "Made for You as a % of Total Sales (AGR)",
		"total_agr" => "Monthly Sales (AGR)",
		"COGS" => "COGS - Cost of Goods Sold (Food & Packaging) as a % of Total Sales (AGR)",
		"labor_costs" => "Labor (Employee & Manager Wages) as a % of Total Sales (AGR)",
		"retained_guest_count" => "Retained Guest Count",
		"agr_growth" => "Yr. over Yr. Monthly Growth in Revenue(AGR)"
	);

	function getHumanReadableMetricName($metric)
	{
		return self::$metricNameMap[$metric];
	}

	function getRollup($sourceData, $yearArray, $RowHeader)
	{
		$destArray = array();

		foreach (self::$rowTemplate as $thisMetric => $data)
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

				foreach ($sourceData[$thisYear] as $month => $thisMonthSource)
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

			$destArray[$thisMetric]['delta'] = $sourceData['delta'][$thisMetric];
		}

		return $destArray;
	}

	function getExpandedData(&$yearArray, $store)
	{
		$focusMonth = false;
		$focusYear = false;

		foreach ($yearArray as $year => &$thisYear)
		{
			foreach ($thisYear as $month => &$thisMonth)
			{
				$anchorDate = date("Y-m-1", mktime(0, 0, 0, $month, 1, $year));
				$anchorDateLastYear = date("Y-m-1", mktime(0, 0, 0, $month, 1, $year - 1));

				// 1) Metrics from dashboard_metrics_guests_by_menu
				$guestMetrics = new DAO();
				$guestMetrics->query("select  new_guests_with_follow_up, retained_guest_count,
                                        guest_count_new_regular + guest_count_new_taste + guest_count_new_intro + guest_count_new_fundraiser as guest_count_new
                                        from dashboard_metrics_guests_by_menu where store_id = $store and date = '$anchorDate' and is_deleted = 0");
				$guestMetrics->fetch();

				$tempArray = self::$rowTemplate;

				$tempArray['guest_count_new'] = $guestMetrics->guest_count_new;
				$tempArray['guest_count_new_converted'] = $guestMetrics->new_guests_with_follow_up;
				$tempArray['retained_guest_count'] = $guestMetrics->retained_guest_count;

				// 2) Metrics from dashboard_metrics_agr_by_menu
				$AgrMetrics = new DAO();
				$AgrMetrics->query("select total_agr from dashboard_metrics_agr_by_menu where store_id = $store and date = '$anchorDate' and is_deleted = 0");
				$AgrMetrics->fetch();

				$AgrMetricsLastYear = new DAO();
				$AgrMetricsLastYear->query("select total_agr from dashboard_metrics_agr_by_menu where store_id = $store and date = '$anchorDateLastYear' and is_deleted = 0");
				$AgrMetricsLastYear->fetch();

				$tempArray['total_agr'] = $AgrMetrics->total_agr;

				$curMonthlastYearAGRDelta = $AgrMetrics->total_agr - $AgrMetricsLastYear->total_agr;
				$curMonthlastYearAGRDeltaPercent = CTemplate::divide_and_format(($AgrMetrics->total_agr - $AgrMetricsLastYear->total_agr) * 100, $AgrMetricsLastYear->total_agr, 2);
				$tempArray['agr_growth'] = CTemplate::divide_and_format($curMonthlastYearAGRDeltaPercent, 100, 4);

				$yearArray[$year][$month] = $tempArray;

				$focusMonth = $month;
				$focusYear = $year;
			}
		}

		// add incr/decr column
		$yearArray['delta'] = self::$rowTemplate;
		$blob = $yearArray[$focusYear][$focusMonth]['guest_count_new'];
		$dfg = $yearArray[$focusYear - 1][$focusMonth]['guest_count_new'];
		$yearArray['delta']['guest_count_new'] = $yearArray[$focusYear][$focusMonth]['guest_count_new'] - $yearArray[$focusYear - 1][$focusMonth]['guest_count_new'];
		$yearArray['delta']['guest_count_new_converted'] = $yearArray[$focusYear][$focusMonth]['guest_count_new_converted'] - $yearArray[$focusYear - 1][$focusMonth]['guest_count_new_converted'];
		$yearArray['delta']['retained_guest_count'] = $yearArray[$focusYear][$focusMonth]['retained_guest_count'] - $yearArray[$focusYear - 1][$focusMonth]['retained_guest_count'];
		$yearArray['delta']['total_agr'] = $yearArray[$focusYear][$focusMonth]['total_agr'] - $yearArray[$focusYear - 1][$focusMonth]['total_agr'];
		$yearArray['delta']['agr_growth'] = "-";
		//      echo "<pre>" . print_r($yearArray, true) . "</pre>";
	}

	function exportAllStoresXLSX($tpl, $RollupArray, $labels)
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

		foreach (self::$allStoreRowTemplate as $name => $value)
		{

			if ($name == 'guest_count_new_last_year' || $name == 'guest_count_new_converted_last_year' || $name == 'retained_guest_count_last_year')
			{
				$columnDescs[$col] = array(
					'align' => 'right',
					'width' => '10',
					'decor' => 'left_border_heavy'
				);
			}
			else if ($name == 'total_agr_last_year')
			{
				$columnDescs[$col] = array(
					'align' => 'right',
					'width' => '14',
					'type' => 'currency',
					'decor' => 'left_border_heavy'
				);
			}
			else if ($name == 'total_agr_this_year' || $name == 'total_agr_delta')
			{
				$columnDescs[$col] = array(
					'align' => 'right',
					'width' => '14',
					'type' => 'currency',
				);
			}
			else if ($name == 'agr_growth')
			{
				$columnDescs[$col] = array(
					'align' => 'right',
					'width' => '14',
					'type' => 'percent',
					'decor' => 'left_border_heavy'
				);
			}
			else if ($name == 'total_points')
			{
				$columnDescs[$col] = array(
					'align' => 'right',
					'width' => '10',
					'decor' => 'majortotal',
				);
			}
			else
			{
				$columnDescs[$col] = array(
					'align' => 'right',
					'width' => '10'
				);
			}

			incrementColumn($thirdSecondChar, $colSecondChar, $col);
		}

		$tpl->assign('override_values', array(
			'main_header_height' => 70
		));

		$tpl->assign('file_name', makeTitle("Cornerstone Movement Contest Report", "", self::$titleSpanString));

		$numRows = count($RollupArray);

		$tpl->assign('title_rows', array(
			"placeholder",
			"",
			""
		));

		$_GET['export'] = "xlsx";
		$tpl->assign('labels', $labels);
		$tpl->assign('rows', $RollupArray);
		$tpl->assign('rowcount', $numRows);
		$tpl->assign('col_descriptions', $columnDescs);
		$callbacks = array(
			'final_render' => 'BHAReportFinalRenderCallbackAllStores'
		);
		$tpl->assign('excel_callbacks', $callbacks);

		return;
	}

	function exportRollupXLSX($tpl, $RollupArray, $labels, $storeObj, $webReport)
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
			'width' => '85'
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		for ($x = 0; $x < 5; $x++)
		{
			$columnDescs[$col] = array(
				'align' => 'right',
				'width' => '10'
			);
			incrementColumn($thirdSecondChar, $colSecondChar, $col);
		}

		$numRows = count($RollupArray);

		$tpl->assign('title_rows', array(
			"placeholder",
			"",
			""
		));
		$tpl->assign('override_values', array(
			'main_header_height' => 70
		));

		$callbacks = array(
			'row_callback' => 'BHAReportRowCallback',
			'cell_callback' => 'BHAReportCellCallback',
			'final_render' => 'BHAReportFinalRenderCallback'
		);

		if ($webReport)
		{
			list($css, $html) =  writeExcelFile("Test", $labels, $RollupArray, true, array("small placeholder", "", ""), $columnDescs, false, $callbacks, false, true);

			echo "<html><head>";
			echo $css;
			echo "</head><body>";

			echo $html;
			echo "</body></html>";
			exit;
		}
		else
		{
			$tpl->assign('file_name', makeTitle("Cornerstone Movement Contest Report", $storeObj->store_name, self::$titleSpanString));

			$_GET['export'] = "xlsx";
			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $RollupArray);
			$tpl->assign('rowcount', $numRows);
			$tpl->assign('col_descriptions', $columnDescs);
			$tpl->assign('excel_callbacks', $callbacks);
		}

		return;
	}

	function rankStore($Form, $homeOfficeID)
	{


		list($labels, $baseExpandedArray, $RollupArray, $focusMonth) = $this->getTimeSpanArrays($Form, true);
		$uberArray = array();
		$storeObj = new DAO();
		$storeObj->query("select id, store_name, home_office_id, state_id from store where active = 1 and is_deleted = 0");
		while ($storeObj->fetch())
		{
			$RowHeader = array(
				$storeObj->home_office_id,
				$storeObj->store_name,
				$storeObj->state_id
			);
			$expandedArray = $baseExpandedArray;
			$this->getExpandedData($expandedArray, $storeObj->id);
			$uberArray[$storeObj->id] = $this->transposeData($expandedArray, $focusMonth, $RowHeader);
			$this->calculatePointsAllStores($uberArray[$storeObj->id]);
		}

		self::$currentStoreHOID = $homeOfficeID;
		$ranks = array('guest_count_new' => 0, 'guest_count_new_converted' => 0, 'retained_guest_count' => 0, 'total_agr' => 0, 'agr_growth' => 0, 'total_points' => 0);

		usort($uberArray,'sortByPointValueTotal');
		$rank = 0;
		$lastVal = -10000000;
		foreach ($uberArray as $this_store)
		{
			if ($lastVal != $this_store['total_points'])
			{
				$rank++;
			}
			$lastVal = $this_store['total_points'];

			if ($this_store[0] == $homeOfficeID)
			{
				break;
			}
		}
		$ranks['total_points']  = $rank;

		usort($uberArray,'sortByPointValue_guest_count_new');
		$rank = 0;
		$lastVal = -10000000;
		foreach ($uberArray as $this_store)
		{
			if ($lastVal != $this_store['guest_count_new_points'])
			{
				$rank++;
			}

			$lastVal = $this_store['guest_count_new_points'];

			if ($this_store[0] == $homeOfficeID)
			{
				break;
			}
		}
		$ranks['guest_count_new']  = $rank;

		usort($uberArray,'sortByPointValue_guest_count_new_converted');
		$rank = 0;
		$lastVal = -10000000;
		foreach ($uberArray as $this_store)
		{
			if ($lastVal != $this_store['guest_count_new_converted_points'])
			{
				$rank++;
			}

			$lastVal = $this_store['guest_count_new_converted_points'];

			if ($this_store[0] == $homeOfficeID)
			{
				break;
			}
		}
		$ranks['guest_count_new_converted']  = $rank;

		usort($uberArray,'sortByPointValue_retained_guest_count');
		$rank = 0;
		$lastVal = -10000000;
		foreach ($uberArray as $this_store)
		{
			if ($lastVal != $this_store['retained_guest_count_points'])
			{
				$rank++;
			}

			$lastVal = $this_store['retained_guest_count_points'];

			if ($this_store[0] == $homeOfficeID)
			{
				break;
			}
		}
		$ranks['retained_guest_count']  = $rank;

		usort($uberArray,'sortByPointValue_total_agr');
		$rank = 0;
		$lastVal = -10000000;
		foreach ($uberArray as $this_store)
		{
			if ($lastVal != $this_store['total_agr_points'])
			{
				$rank++;
			}

			$lastVal = $this_store['total_agr_points'];

			if ($this_store[0] == $homeOfficeID)
			{
				break;
			}
		}
		$ranks['total_agr']  = $rank;

		usort($uberArray,'sortByPointValue_agr_growth');
		$rank = 0;
		$lastVal = -10000000;
		foreach ($uberArray as $this_store)
		{
			if ($lastVal != $this_store['agr_growth_points'])
			{
				$rank++;
			}

			$lastVal = $this_store['agr_growth_points'];

			if ($this_store[0] == $homeOfficeID)
			{
				break;
			}
		}
		$ranks['agr_growth']  = $rank;

		return $ranks;

	}

	function runPage()
	{
		ini_set('memory_limit', '-1');
		set_time_limit(3600 * 24);

		
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
			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChange => 'selectStoreTR',
				CForm::allowAllOption => true,
				CForm::showInactiveStores => false,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}

		// build year list
		$thisYear = date("Y");
		$startYear = 2018;
		$yearOptions = array();
		while ($startYear <= $thisYear)
		{
			$yearOptions[$startYear] = $startYear;
			$startYear++;
		}

		$Form->DefaultValues['year_popup'] = $thisYear;

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

		$tpl->assign('showStoreSelector', $showStoreSelector);

		$reportType = $Form->value('report_type');

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => "store_id",
			CForm::value => $store
		));

		if (isset($_POST['run_report']) || isset($_POST['run_web']) )
		{

			if ($store == 'all' && !isset($_POST['run_web']))
			{
				list($labels, $baseExpandedArray, $RollupArray, $focusMonth) = $this->getTimeSpanArrays($Form, true);
				$uberArray = array();
				$storeObj = new DAO();
				$storeObj->query("select id, store_name, home_office_id, state_id from store where active = 1 and is_deleted = 0");
				while ($storeObj->fetch())
				{
					$RowHeader = array(
						$storeObj->home_office_id,
						$storeObj->store_name,
						$storeObj->state_id
					);
					$expandedArray = $baseExpandedArray;
					$this->getExpandedData($expandedArray, $storeObj->id);
					$uberArray[$storeObj->id] = $this->transposeData($expandedArray, $focusMonth, $RowHeader);
					$this->calculatePointsAllStores($uberArray[$storeObj->id]);
				}

				$this->exportAllStoresXLSX($tpl, $uberArray, $labels);

				return;
			}
			else
			{
				$storeObj = DAO_CFactory::create('store');
				$storeObj->id = $store;

				if (empty($storeObj->id))
				{
					$storeObj->id = 244;
					$store = 244;
				}
				$storeObj->find(true);

				$RowHeader = array(
					$storeObj->home_office_id,
					$storeObj->store_name,
					$storeObj->state_id
				);

				list($labels, $expandedArray, $RollupArray, $focusMonth) = $this->getTimeSpanArrays($Form);

				$this->getExpandedData($expandedArray, $store);
				$rows = $this->getRollup($expandedArray, $RollupArray, $RowHeader);
				$this->CalcAndAddPoints($rows,$focusMonth, $RollupArray);

				$ranks = $this->rankStore($Form, $storeObj->home_office_id);

				$rows['guest_count_new']['rank'] = $ranks['guest_count_new'];
				$rows['guest_count_new_converted']['rank'] = $ranks['guest_count_new_converted'];
				$rows['retained_guest_count']['rank'] = $ranks['retained_guest_count'];
				$rows['total_agr']['rank'] = $ranks['total_agr'];
				$rows['agr_growth']['rank'] = $ranks['agr_growth'];
				$rows['total_points']['rank'] = $ranks['total_points'];

				$this->exportRollupXLSX($tpl, $rows, $labels, $storeObj, isset($_POST['run_web']));

				return;
			}
		}

		$titleString = "Cornerstone Movement Contest Report";
		$tpl->assign('titleString', $titleString);

		$formArray = $Form->render();
		$tpl->assign('store', $store);

		$tpl->assign('form_array', $formArray);
	}

	// for single store
	function CalcAndAddPoints(&$rows, $focusMonth, $RollupArray)
	{
		$thisYear = $RollupArray[1];



		$rows['guest_count_new']['points'] = $rows['guest_count_new'][$thisYear];
		$rows['guest_count_new_converted']['points'] = $rows['guest_count_new_converted'][$thisYear] * 5;

		if ($rows['retained_guest_count']['delta'] > 0)
		{
			$rows['retained_guest_count']['points'] = $rows['retained_guest_count']['delta'] * 2;
		}
		else
		{
			$rows['retained_guest_count']['points'] = 0;
		}

		$revenue = $rows['total_agr'][$thisYear];
		$rev_points = false;
		if ($revenue < 20000.0)
		{
			$rev_points = 0;
		}
		else if ($revenue < 25000.0)
		{
			$rev_points = 1;
		}
		else
		{
			$rev_points = ((int)(($revenue - 25000.0) / 5000) + 1) * 3;
		}

		$rows['total_agr']['points'] = $rev_points;


		$revenueDelta = $rows['agr_growth'][$thisYear] * 100.0;
		$rev_delta_points = false;
		if ($revenueDelta < 5.0)
		{
			$rev_delta_points = 0;
		}
		else
		{
			$rev_delta_points = ((int)($revenueDelta / 5.0)) * 10;
		}

		$rows['agr_growth']['points'] = $rev_delta_points;

		$totalPoints = $rows['guest_count_new']['points'] + $rows['guest_count_new_converted']['points'] + $rows['retained_guest_count']['points'] + $rows['total_agr']['points'] + $rows['agr_growth']['points'];

		$rows['total_points'] = array("Total Contest Points Earned This Month:|->7", $totalPoints);


	}


	function transposeData($expandedArray, $focusMonth, $RowHeader)
	{
		$row = array();
		$row = array_merge($row, $RowHeader);
		$row = array_merge($row, self::$allStoreRowTemplate);

		$count = 0;
		// 0 = last year
		// 1 = this year
		// 2 = delta
		foreach ($expandedArray as $cat => $data)
		{
			if ($count == 0)
			{
				$row['guest_count_new_last_year'] = $data[$focusMonth]['guest_count_new'];
				$row['guest_count_new_converted_last_year'] = $data[$focusMonth]['guest_count_new_converted'];
				$row['retained_guest_count_last_year'] = $data[$focusMonth]['retained_guest_count'];
				$row['total_agr_last_year'] = $data[$focusMonth]['total_agr'];
				$row['agr_growth'] = $data[$focusMonth]['agr_growth'];
			}
			else if ($count == 1)
			{
				$row['guest_count_new_this_year'] = $data[$focusMonth]['guest_count_new'];
				$row['guest_count_new_converted_this_year'] = $data[$focusMonth]['guest_count_new_converted'];
				$row['retained_guest_count_this_year'] = $data[$focusMonth]['retained_guest_count'];
				$row['total_agr_this_year'] = $data[$focusMonth]['total_agr'];
				$row['agr_growth'] = $data[$focusMonth]['agr_growth'];
			}
			else
			{
				$row['guest_count_new_delta'] = $data['guest_count_new'];
				$row['guest_count_new_converted_delta'] = $data['guest_count_new_converted'];
				$row['retained_guest_count_delta'] = $data['retained_guest_count'];
				$row['total_agr_delta'] = $data['total_agr'];
			}

			$count++;
		}

		return $row;
	}

	function calculatePointsAllStores(&$row)
	{
		$row['guest_count_new_points'] = $row['guest_count_new_this_year'];
		$row['guest_count_new_converted_points'] = $row['guest_count_new_converted_this_year'] * 5;

		if ($row['retained_guest_count_delta'] > 0)
		{
			$row['retained_guest_count_points'] = $row['retained_guest_count_delta'] * 2;
		}
		else
		{
			$row['retained_guest_count_points'] = 0;
		}

		$revenue = $row['total_agr_this_year'];
		$rev_points = false;
		if ($revenue < 20000.0)
		{
			$rev_points = 0;
		}
		else if ($revenue < 25000.0)
		{
			$rev_points = 1;
		}
		else
		{
			$rev_points = ((int)(($revenue - 25000.0) / 5000) + 1) * 3;
		}

		$row['total_agr_points'] = $rev_points;


		$revenueDelta = $row['agr_growth'] * 100.0;
		$rev_delta_points = false;
		if ($revenueDelta < 5.0)
		{
			$rev_delta_points = 0;
		}
		else
		{
			$rev_delta_points = ((int)($revenueDelta / 5.0)) * 10;
		}

		$row['agr_growth_points'] = $rev_delta_points;
		$row['total_points'] = $row['guest_count_new_points'] + $row['guest_count_new_converted_points'] + $row['retained_guest_count_points'] + $row['total_agr_points'] + $row['agr_growth_points'];
	}

}
?>