<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CStoreCredit.php');
 require_once ('includes/CSessionReports.inc');
 require_once ('includes/CDashboardReportWeekBased.inc');
 require_once('phplib/PHPExcel/PHPExcel.php');
 require_once('ExcelExport.inc');


 function growthScorecardSummaryRowsCallback($sheet, &$data, $row, $bottomRightExtent)
 {

     if (strpos($data[0], "Average") !== false)
     {
         $sheet->getStyle("A$row:$bottomRightExtent")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_0);

     }
 }


 function growthScorecardSummaryCellCallback($sheet, $colName, $datum, $col, $row)
{
}

 function growthScorecardRowsCallback($sheet, &$data, $row, $bottomRightExtent)
 {


     if (strpos($data[0], "Week") === 0 || strpos($data[0], "Monthly") === 0 || empty($data[0]))
     {
         $styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ),
             'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK ), 'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )),
             'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => 'FFA8B355', ), 'endcolor' => array( 'argb' => 'FFA8B355')));

         $sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
     }

 }

 function growthScorecardCellCallback($sheet, $colName, $datum, $col, $row)
 {
     $styleArray = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ),
         'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));
     $sheet->getStyle("$col$row")->applyFromArray($styleArray);

     if ($col == "A" || $col == "B" || $col == 'E' )
     {
         $styleArray = array( 'font' => array( 'bold' => true, 'size' => 10 ));
         $sheet->getStyle("$col$row")->applyFromArray($styleArray);

     }

 }

 function growthScorecardFinalRenderCallback($sheet, &$data)
 {



     if (page_admin_reports_growth_scorecard::$reportType == 'week')
     {
         $netGainLossFormula = "=SUM(G4:G" . page_admin_reports_growth_scorecard::$lastRow . ")";
         $netGainLossAvgFormula = "=AVERAGE(G4:G" . page_admin_reports_growth_scorecard::$lastRow . ")";
         $netGainLossText = "Year To Date";
     }
     else if (page_admin_reports_growth_scorecard::$reportType == 'month')
     {
         $netGainLossFormula = "=SUM(F4:F" . (page_admin_reports_growth_scorecard::$lastRow - 1) . ")";
         $netGainLossAvgFormula = "=AVERAGE(F4:F" . (page_admin_reports_growth_scorecard::$lastRow - 1) . ")";
         $netGainLossText = "Year To Date";
     }
     else
     {
         $netGainLossFormula = page_admin_reports_growth_scorecard::getTotalFormula("G", page_admin_reports_growth_scorecard::$weekTotalRows);
         $netGainLossText = "Net Gain/Loss Month To Date";
         $netGainLossAvgFormula = page_admin_reports_growth_scorecard::getAverageFormula("G", page_admin_reports_growth_scorecard::$weekTotalRows);

     }




     $boldText = array( 'font' => array( 'bold' => true, 'size' => 11 ));

     $headerBG = array('fill' => array( 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR, 'rotation' => 90, 'startcolor' => array( 'argb' => 'FFFDFBE5', ), 'endcolor' => array( 'argb' => 'FFF1DB3C')));
     $hilightBG = array('fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID,'startcolor' => array( 'argb' => 'FFFFFF00', )));

     $ThickCompleteBorder = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK ), 'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK ),
         'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )));

     $ThinCompleteBorder = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ),
         'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));

     $MediumCompleteBorder = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM ), 'top' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM ),
         'left' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM )));


     $MediumCompleteBorderThickRight = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM ), 'top' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM ),
         'left' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )));

     $ThinCompleteBorderThickRight = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'top' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ),
         'left' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ), 'right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )));

     $BorderThickRight = array('borders' => array('right' => array( 'style' => PHPExcel_Style_Border::BORDER_THICK )));
     $BorderMediumBottom = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM )));

     $WordWrap = array('alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ));
     $WordWrapRight = array('alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP, 'wrap' => true ));


     $sheet->getColumnDimension("I")->setWidth(32);
     $sheet->getColumnDimension("J")->setWidth(12);

     $sheet->getStyle("I4:J4")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
     $sheet->getStyle("I4")->applyFromArray($ThinCompleteBorderThickRight);
     $sheet->getStyle("I4")->applyFromArray($headerBG);
     $sheet->getStyle("I4")->applyFromArray($boldText);
     $sheet->mergeCells("I4:J4");
     $sheet->setCellValue("I4", "Running Total of Guest Count Growth");


     $sheet->getStyle("I5")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
     $sheet->setCellValue("I5", $netGainLossText);
     $sheet->getStyle("I5")->applyFromArray($ThinCompleteBorder);

     $sheet->getStyle("J5")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
     $sheet->setCellValue("J5", $netGainLossFormula);
     $sheet->getStyle("J5")->applyFromArray($ThinCompleteBorder);

     $sheet->getStyle("I4:J5")->applyFromArray($MediumCompleteBorderThickRight);




     $sheet->getStyle("I7:J7")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
     $sheet->mergeCells("I7:J7");
     $sheet->setCellValue("I7", "Revenue Example of Consistent Growth");
     $sheet->getStyle("I7")->applyFromArray($headerBG);
     $sheet->getStyle("I7")->applyFromArray($BorderThickRight);
     $sheet->getStyle("I7")->applyFromArray($boldText);


     $sheet->getStyle("I8")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
     $sheet->setCellValue("I8", "Average Ticket");
     $sheet->getStyle("I8")->applyFromArray($ThinCompleteBorder);
     $sheet->getStyle("I8")->applyFromArray($WordWrap);

     $sheet->getStyle("J8")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
     $sheet->setCellValue("J8", 250);
     $sheet->getStyle("J8")->applyFromArray($ThinCompleteBorder);
     $sheet->getStyle("J8")->applyFromArray($WordWrapRight);

     $sheet->getStyle("I9")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
     $sheet->setCellValue("I9", "Avg. Guest Count Net gain/loss per week");
     $sheet->getStyle("I9")->applyFromArray($WordWrap);

     $sheet->getStyle("J8")->applyFromArray($ThinCompleteBorder);

     $sheet->getStyle("J9")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
     $sheet->setCellValue("J9", $netGainLossAvgFormula);
     $sheet->getStyle("J9")->applyFromArray($ThinCompleteBorder);
     $sheet->getStyle("J9")->applyFromArray($WordWrapRight);


     $sheet->getStyle("I10:I12")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
     $sheet->mergeCells("I10:I12");
     $sheet->setCellValue("I10", "After 52 weeks, you'll see an approximate monthly revenue increase of:");
     $sheet->getStyle("I10:I12")->applyFromArray($ThinCompleteBorder);
     $sheet->getStyle("I10:I12")->applyFromArray($WordWrap);


     $sheet->getStyle("J10")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
     $sheet->mergeCells("J10:J12");
     $sheet->setCellValue("J10", "=J8*J9*52");
     $sheet->getStyle("J10:J12")->applyFromArray($ThinCompleteBorder);
     $sheet->getStyle("J10:J12")->applyFromArray($WordWrapRight);
     $sheet->getStyle("J10")->applyFromArray($BorderMediumBottom);
     $sheet->getStyle("I10")->applyFromArray($BorderMediumBottom);

     $sheet->getStyle("I7:J12")->applyFromArray($MediumCompleteBorder);


     $MediumLowerBorder = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM )));
     if (page_admin_reports_growth_scorecard::$reportType == 'month')
     {
         $sheet->getStyle("A2:F2")->applyFromArray($MediumLowerBorder);
     }
     else
     {
        $sheet->getStyle("A2:G2")->applyFromArray($MediumLowerBorder);
     }

     $ThinRightBorder = array('borders' => array('right' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN )));

     $sheet->getStyle("A2:B2")->applyFromArray($ThinRightBorder);
     $sheet->getStyle("C2")->applyFromArray($ThinRightBorder);
     $sheet->getStyle("D2")->applyFromArray($ThinRightBorder);



     $lastRow = page_admin_reports_growth_scorecard::$lastRow;

     if ($lastRow < 12)
     {
         $lastRow = 12;
     }

     if (page_admin_reports_growth_scorecard::$reportType == 'month' && $lastRow == 16)
     {
         $sheet->getStyle("A15:F15")->applyFromArray($MediumCompleteBorder);
         $sheet->getStyle("A15:F15")->applyFromArray($boldText);
     }


     $sheet->getStyle("A2:J$lastRow")->applyFromArray($ThickCompleteBorder);

     /*
     for($x = 4; $x < $lastRow - 2; $x++)
     {
         if (!in_array($x, page_admin_reports_growth_scorecard::$weekTotalRows))
         {

             $sheet->getStyle("C$x")->applyFromArray($ThinCompleteBorder);
             $sheet->getStyle("C$x")->applyFromArray($hilightBG);

             $sheet->getStyle("D$x")->applyFromArray($ThinCompleteBorder);
             $sheet->getStyle("D$x")->applyFromArray($hilightBG);

             $sheet->getStyle("F$x")->applyFromArray($ThinCompleteBorder);
             $sheet->getStyle("F$x")->applyFromArray($hilightBG);
         }
     }

     $sheet->getStyle("J8")->applyFromArray($hilightBG);
     $sheet->getStyle("J9")->applyFromArray($hilightBG);
     */

 }




 class page_admin_reports_growth_scorecard extends CPageAdminOnly {


     static  $weekTotalRows = array();
     static $lastRow = 0;
     static $reportType;
     static $access_summaries  = false;

     private $show_store_selectors = false;

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }

     function runHomeOfficeManager()
     {
         self::$access_summaries = true;
         $this->show_store_selectors = true;
         $this->runReport();
     }

     function runSiteAdmin()
     {
         self::$access_summaries = true;
         $this->show_store_selectors = true;
         $this->runReport();
     }

     function runFranchiseOwner()
     {

         $this->runReport();
     }

     function runFranchiseLead()
     {

         $this->runReport();
     }

     function runFranchiseManager()
     {

         $this->runReport();
     }

     function runEventCoordinator()
     {

         $this->runReport();
     }

     function runOpsLead()
     {
         $this->runReport();
     }

    function runOpsSupport()
    {
        $this->runReport();
    }

    function runFranchiseStaff()
    {
        $this->runReport();
    }


 	function runReport()
 	{
		$tpl = CApp::instance()->template();


		$year = date("Y");

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

		$Form = new CForm();
		$Form->Repost = TRUE;


		$menuSetter = CMenu::getMenuByDate(date("Y-m-d"));
		$defaultMonth = date("n", strtotime($menuSetter['menu_start']));
		$defaultYear = date("Y", strtotime($menuSetter['menu_start']));

		$tpl->assign("showStoreSelector",$this->show_store_selectors);

		if ($this->show_store_selectors)
		{
    		$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
    		    CForm::onChange => 'selectStoreTR',
    		    CForm::allowAllOption => false,
    		    CForm::showInactiveStores => false,
    		    CForm::name => 'store'));

    		$store_id = $Form->value('store');
		}
		else
		{
		    $store_id = CApp::forceLocationChoice();
		}

		$Form->DefaultValues['year'] = $defaultYear;
		$Form->DefaultValues['month'] = $defaultMonth - 1;

		$Form->AddElement(array(
		    CForm::type => CForm::Text,
		    CForm::name => "year",
		    CForm::required => true,
		    CForm::default_value => $year,
		    CForm::length => 6
		));

		$year = $Form->value('year');

		$Form->AddElement(array(
		    CForm::type => CForm::DropDown,
		    CForm::onChangeSubmit => false,
		    CForm::allowAllOption => false,
		    CForm::options => $month_array,
		    CForm::name => 'month'
		));

		$month = $Form->value('month');
		$month++;

		$Form->AddElement(array(
		    CForm::type => CForm::Submit,
		    CForm::css_class => 'button',
		    CForm::name => 'report_submit',
		    CForm::value => 'Run Web Report'
		));


		if (self::$access_summaries)
		{
    		$Form->AddElement(array(
    		    CForm::type => CForm::Submit,
    		    CForm::css_class => 'button',
    		    CForm::name => 'weekly_summary_report_submit',
    		    CForm::value => 'Export Weekly Rollup Report'
    		));

    		$Form->AddElement(array(
    		    CForm::type => CForm::Submit,
    		    CForm::css_class => 'button',
    		    CForm::name => 'monthly_summary_report_submit',
    		    CForm::value => 'Export Monthly Rollup Report'
    		));
		}

		// Date Selection Type
		$Form->DefaultValues['report_type'] = 'session';

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
		    CForm::name => "report_type",
		    CForm::required => true,
		    CForm::value => 'session'));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
		    CForm::name => "report_type",
		    CForm::required => true,
		    CForm::value => 'week'));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
		    CForm::name => "report_type",
		    CForm::required => true,
		    CForm::value => 'month'));


		$Form->AddElement(array(CForm::type => CForm::Hidden, CForm::name => 'export', CForm::value => 'none'));

		$export = false;

		if (isset($_POST['export']) && $_POST['export'] == 'xlsx')
		    $export = true;


		if ($Form->value('weekly_summary_report_submit'))
		{
		    // TODO
		}

		if ($Form->value('monthly_summary_report_submit'))
		{
		    $repArr = array();

            $monthsArr = array();
            $curYear = date("Y");
            $curMonth = date("m");
            $curMonth = $curYear . "-" . $curMonth . "-01 00:00:00";
            $curDateDT = new DateTime($curMonth);

            for ($x = 0; $x < 18; $x++)
            {
                $curDateDT->modify("- 1 month");
                array_unshift($monthsArr, $curDateDT->format("Y-m-d"));

            }



		    $storeObj = DAO_CFactory::create('store');
		    $storeObj->query("select id, home_office_id, store_name, city, state_id, is_corporate_owned from store where active = 1 order by is_corporate_owned desc, id asc");

		    $labels = array("HOID", "Store Name", "City", "State");
		    $labels = array_merge($labels, $monthsArr);
		    $labels[] = "Total";
		    $labels[] = "Average";

		    $rowNum = 2;
		    $corpCount = 0;
		    while($storeObj->fetch())
		    {

		        if ($storeObj->is_corporate_owned)
		        {
		            $storeObj->store_name .= " (corp)";
		            $corpCount++;
		        }

		        $repArr[$storeObj->id] = array("ID" => $storeObj->home_office_id, "Name" => $storeObj->store_name, "City" => $storeObj->city, "State" => $storeObj->state_id);

		        foreach($monthsArr as $thisMonth)
		        {
		            $monthObj = new DAO();
		            $monthObj->query("select * from dashboard_metrics_guests_by_menu dmg
		                          where dmg.store_id = {$storeObj->id} and date = '{$thisMonth}'");
		            if ($monthObj->fetch())
		            {
		                $repArr[$storeObj->id][$thisMonth] = $monthObj->growth_scorecard_value;
		            }
		            else
		            {
		                $repArr[$storeObj->id][$thisMonth] = "-";
		            }
		        }

		        $repArr[$storeObj->id]["Total"] = "=SUM(E$rowNum:V$rowNum)";
		        $repArr[$storeObj->id]["Average"] = "=AVERAGE(E$rowNum:V$rowNum)";

		        $rowNum++;

		    }

		    $repArr["total_all"] = array("Total For All Stores|->4");
		    $repArr["avg_all"] = array("Average For All Stores|->4");
		    $repArr["total_corp"] = array("Total For Corporate Stores|->4");
		    $repArr["avg_corp"] = array("Average For Corporate Stores|->4");
		    $repArr["total_fran"] = array("Total For Franchise Stores|->4");
		    $repArr["avg_fran"] = array("Average For Franchise Stores|->4");

		    $rowNum--;

		    $col = 'E';
		    $colSecondChar = '';
		    $thirdSecondChar = '';

		    $lastCorpRow = 1 + $corpCount;
		    $firstFranRow = 1 + $lastCorpRow;

		    foreach($monthsArr as $thisMonth)
		    {
		        $repArr["total_all"][$thisMonth] = "=SUM($col" . 2 . ":$col$rowNum)";
		        $repArr["avg_all"][$thisMonth] = "=AVERAGE($col" . 2 . ":$col$rowNum)";

		        $repArr["total_corp"][$thisMonth] = "=SUM($col" . 2 . ":" . "$col$lastCorpRow)";
		        $repArr["avg_corp"][$thisMonth] = "=AVERAGE($col" . 2 . ":" . "$col$lastCorpRow)";

		        $repArr["total_fran"][$thisMonth] = "=SUM($col$firstFranRow:$col$rowNum)";
		        $repArr["avg_fran"][$thisMonth] = "=AVERAGE($col$firstFranRow:$col$rowNum)";

		        incrementColumn($thirdSecondChar, $colSecondChar, $col);

		    }

		    $repArr["total_all"][] = "=SUM(W2:W$rowNum)";
		    $repArr["avg_all"][] = " ";

            $repArr["total_all"][] = " ";
            $repArr["avg_all"][] = "=AVERAGE(X2:X$rowNum)";

            $repArr["total_corp"][] = "=SUM(W2:W$lastCorpRow)";
            $repArr["avg_corp"][] = " ";

            $repArr["total_corp"][] = " ";
		    $repArr["avg_corp"][] = "=AVERAGE(X2:X$lastCorpRow)";

		    $repArr["total_fran"][] = "=SUM(W$firstFranRow:W$rowNum)";
		    $repArr["avg_fran"][] = " ";

		    $repArr["total_fran"][] = " ";
		    $repArr["avg_fran"][] = "=AVERAGE(X$firstFranRow:X$rowNum)";



		    $columnDescs = array();
		    $columnDescs['A'] = array(
		        'align' => 'center',
		        'width' => 12
		    );
		    $columnDescs['B'] = array(
		        'align' => 'left',
		        'width' => 24
		    );
		    $columnDescs['C'] = array(
		        'align' => 'left',
		        'width' => 18
		    );
		    $columnDescs['D'] = array(
		        'align' => 'right',
		        'width' => 10
		    );
		    $columnDescs['E'] = array(
		        'align' => 'center',
		        'width' => 12
		    );
		    $columnDescs['F'] = array(
		        'align' => 'center',
		        'width' => 12
		    );
		    $columnDescs['G'] = array(
		        'align' => 'center',
		        'width' => 12
		    );
		    $columnDescs['H'] = array(
		        'align' => 'center',
		        'width' => 12
		    );
		    $columnDescs['I'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['J'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['K'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['L'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['M'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['N'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['O'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['P'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['Q'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['R'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['S'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );
		    $columnDescs['T'] = array(
		        'align' => 'right',
		        'width' => 12,
		    );

            $columnDescs['U'] = array(
                'align' => 'right',
                'width' => 12,
            );
            $columnDescs['V'] = array(
                'align' => 'right',
                'width' => 12,
            );
            $columnDescs['W'] = array(
                'align' => 'right',
                'width' => 12,
            );
            $columnDescs['X'] = array(
                'type' => 'number_x',
                'align' => 'right',
                'width' => 12,
            );

		    $callbacks = array('row_callback' => 'growthScorecardSummaryRowsCallback', 'cell_callback' => 'growthScorecardSummaryCellCallback');


		    $_GET['export'] = 'xlsx';

		    $numRows = count($repArr);
		    $tpl->assign('labels', $labels);
		    $tpl->assign('rows', $repArr);
		    $tpl->assign('rowcount', $numRows);
		    $tpl->assign('col_descriptions', $columnDescs);
		    $tpl->assign('excel_callbacks', $callbacks);


		    return;

		}


		if ( $Form->value('report_submit') || $export)
		{
		    $madeForYouOnly = false;

		    if (isset($_POST['MFY_only']))
		    {
		        $madeForYouOnly = true;
		    }

		    $cutoffTime = date("Y-m-d H:i:s");
		    $cutoffTime = date("Y-m-d H:i:s", strtotime($cutoffTime) + 86400);

		    self::$reportType = $Form->value('report_type');
		    if (self::$reportType == 'session')
		    {


    		    $anchorDate = $year ."/" . $month . "/1";
    		    $anchorDate = date("Y-m-d", strtotime($anchorDate));

    		    $menu_id = CMenu::getMenuIDByAnchorDate($anchorDate);
    		    $menuObj = DAO_CFactory::create('menu');
    		    $menuObj->id = $menu_id;
    		    $menuObj->find(true);

    		    $storeObj = DAO_CFactory::create('store');
    		    $storeObj->id = $store_id;
    		    $storeObj->find(true);


    		    $storeName = $storeObj->store_name;
    		    $MenuName = $menuObj->menu_name;
        		$sessionArray = array();
        		$sessionObj = new DAO();

        		$title = "$MenuName Growth Scorecard for " . $storeName;
        		$titleRows = array();
        		$titleRows[] = array(
        		    "",
        		    $title
        		);

				$minimum_sql_clause = self::createMinimumClause($store_id,$menu_id);
        		$MFYClause = "";
        		if ($madeForYouOnly)
        		{
        		    $MFYClause = " and s.session_type = 'SPECIAL_EVENT' ";
        		}

				//alter to minimum type instead of hard coded to servings
        		$sessionObj->query("select s.id, s.session_start, count(distinct b.id) as guest_count, GROUP_CONCAT(distinct b.user_id) as guest_list from session s
                                        join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
                                        join orders o on o.id = b.order_id $minimum_sql_clause
                                        where s.menu_id = $menu_id and s.store_id = $store_id $MFYClause
                                        group by s.id
                                        order by s.session_start");

        		while($sessionObj->fetch())
        		{
        		    $sessionArray[$sessionObj->id] = array("id" => $sessionObj->id, "start" => $sessionObj->session_start,
        		        "full_order_guest_count" =>  $sessionObj->guest_count);

            		$last_month_menu_id = $menu_id - 1;
					$minimum_sql_clause = self::createMinimumClause($store_id,$last_month_menu_id);
            		$lastMonth = new DAO();
            		$lastMonth->query("select distinct b.user_id from session s
                                		join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$sessionObj->guest_list})
                                		join orders o on o.id = b.order_id $minimum_sql_clause
            		                      where s.menu_id = $last_month_menu_id and s.store_id = $store_id");

            		$sessionArray[$sessionObj->id]['guests_last_month'] =  $lastMonth->N;

            		$next_month_menu_id = $menu_id + 1;
					$minimum_sql_clause = self::createMinimumClause($store_id,$next_month_menu_id);
            		$nextMonth = new DAO();
            		$nextMonth->query("select distinct b.user_id from session s
            		    join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$sessionObj->guest_list})
            		    join orders o on o.id = b.order_id $minimum_sql_clause
            		    where s.menu_id = $next_month_menu_id and s.store_id = $store_id");

            		$sessionArray[$sessionObj->id]['guests_next_month'] =  $nextMonth->N;

        		}

                $rows = array();
                $headers = array($storeName => 2, "Pre-Session Data Input" => 2, "LEAD GOAL" => 1, "Post-Session" => 1, "Results" => 1 );
                $labels = array($MenuName, "Session Date/Times", "Total number of Guests in that session with full orders",
                                            "Of those, how many had full session orders LAST MONTH?", "That means there are this many GROWTH OPPORTUNITY guests:",
                                            "How many people signed up?", "GROWTH net gain/loss (Don't break the chain)");

                $menuStartMonth = DAO_CFactory::create('menu');
                $menuStartMonth->id = $menu_id;
                $menuStartMonth->find(true);
                $firstWeekData = CDashboardWeekBased::getWeekTimeData($menuStartMonth->global_menu_start_date);

                $lastWeek = $firstWeekData['week_number'];

                $sheetRowNumber = 4;
                $week = 1;
                $weekRangeStart = $sheetRowNumber;

                foreach($sessionArray as $sessionData)
                {
                    $weekData = CDashboardWeekBased::getWeekTimeData($sessionData['start']);

                    if ($weekData['week_number'] != $lastWeek)
                    {

                        $lastWeek = $weekData['week_number'];

                        if ($weekRangeStart >= $sheetRowNumber)
                        {
                            $rows[] = array( "Week " . $week, "TOTALS",
                                "0", "0", "0",
                                "0", "0" );

                        }
                        else
                        {
                            $thisTotalRowNumber = $sheetRowNumber - 1;
                            $rows[] = array( "Week " . $week, "TOTALS",
                                "=SUM(C$weekRangeStart:C$thisTotalRowNumber)", "=SUM(D$weekRangeStart:D$thisTotalRowNumber)", "=SUM(E$weekRangeStart:E$thisTotalRowNumber)",
                                "=SUM(F$weekRangeStart:F$thisTotalRowNumber)", "=SUM(G$weekRangeStart:G$thisTotalRowNumber)");
                        }

                        self::$weekTotalRows[] = $sheetRowNumber;

                        $week++;
                        $sheetRowNumber++;
                        $weekRangeStart  = $sheetRowNumber;
                    }

                    $sessionTime = date("l g:i A", strtotime($sessionData['start']));
                    $sesionTimeAndLink = "=HYPERLINK(\"" . HTTPS_SERVER . WEB_BASE . "?page=admin_main&session=" . $sessionData['id'] . "\", \"$sessionTime\")";


                    $rows[] = array( date("n/j/Y", strtotime($sessionData['start'])),
                        $sesionTimeAndLink,
                        $sessionData['full_order_guest_count'], $sessionData['guests_last_month'], "=C$sheetRowNumber-D$sheetRowNumber",
                        $sessionData['guests_next_month'], "=F$sheetRowNumber-D$sheetRowNumber"
                    );


                    $sheetRowNumber++;

                }

                if ($weekRangeStart >= $sheetRowNumber)
                {
                    $rows[] = array( "Week " . $week, "TOTALS",
                        "0", "0", "0",
                        "0", "0" );
                }
                else
                {
                    $thisTotalRowNumber = $sheetRowNumber - 1;
                    $rows[] = array( "Week " . $week, "TOTALS",
                        "=SUM(C$weekRangeStart:C$thisTotalRowNumber)", "=SUM(D$weekRangeStart:D$thisTotalRowNumber)", "=SUM(E$weekRangeStart:E$thisTotalRowNumber)",
                        "=SUM(F$weekRangeStart:F$thisTotalRowNumber)", "=SUM(G$weekRangeStart:G$thisTotalRowNumber)");
                    self::$weekTotalRows[] = $sheetRowNumber;

                }

                $rows[] = array( "", "",
                    "", "", "",
                    "", "" );


                $rows[] = array( "Monthly", "TOTALS",
                    self::getTotalFormula("C", self::$weekTotalRows),
                    self::getTotalFormula("D", self::$weekTotalRows),
                    self::getTotalFormula("E", self::$weekTotalRows),
                    self::getTotalFormula("F", self::$weekTotalRows),
                    self::getTotalFormula("G", self::$weekTotalRows));

                self::$lastRow = $sheetRowNumber + 2;

                $columnDescs = array();
                $columnDescs['A'] = array(
                    'align' => 'center',
                    'width' => 12
                );
                $columnDescs['B'] = array(
                    'align' => 'center',
                    'width' => 24
                );
                $columnDescs['C'] = array(
                    'align' => 'right',
                    'width' => 12
                );
                $columnDescs['D'] = array(
                    'align' => 'right',
                    'width' => 12
                );
                $columnDescs['E'] = array(
                    'align' => 'right',
                    'width' => 12
                );
                $columnDescs['F'] = array(
                    'align' => 'right',
                    'width' => 12
                );
                $columnDescs['G'] = array(
                    'align' => 'right',
                    'width' => 12
                );

                $callbacks = array('row_callback' => 'growthScorecardRowsCallback', 'cell_callback' => 'growthScorecardCellCallback', 'final_render' => 'growthScorecardFinalRenderCallback');

                if ($export)
                {
                    $overrideValues = array('main_header_height' => 75, 'header_gradient_start' => 'FFA8B355', 'header_gradient_end' => 'FFA8B355', 'titleRowFinalColumn' => "J");

					$_GET['export'] = 'xlsx';

                    $numRows = count($rows);
                    $tpl->assign('labels', $labels);
                    $tpl->assign('rows', $rows);
                    $tpl->assign('rowcount', $numRows);
                    $tpl->assign('sectionHeader', $headers);
                    $tpl->assign('title_rows', $titleRows);
                    $tpl->assign('col_descriptions', $columnDescs);
                    $tpl->assign('excel_callbacks', $callbacks);
                    $tpl->assign('override_values', $overrideValues);



                }
                else
                {
                    PHPExcel_Shared_String::setThousandsSeparator(",");

                    $overrideValues = array('links_target_new_tab' => true, 'header_gradient_start' => 'FFA8B355', 'header_gradient_end' => 'FFA8B355');

                    list($css, $html) =  writeExcelFile("Test", $labels, $rows, true, $titleRows, $columnDescs, false, $callbacks, $headers, true, false, $overrideValues);

                    echo "<html><head>";
                    echo $css;
                    echo "</head><body>";

                    echo $html;
                    echo "</body></html>";
                    exit;

                }

		}
		else if (self::$reportType == 'week')
		{


		    $anchorDate = $year ."-01-01";

		    $menu_id = CMenu::getMenuIDByAnchorDate($anchorDate);
		    $menuObj = DAO_CFactory::create('menu');
		    $menuObj->id = $menu_id;
		    $menuObj->find(true);

		    $lastMenuID = $menu_id + 11;

		    $storeObj = DAO_CFactory::create('store');
		    $storeObj->id = $store_id;
		    $storeObj->find(true);


		    $storeName = $storeObj->store_name;
		    $MenuName = $menuObj->menu_name;
		    $weekArray = array();
		    $weekObj = new DAO();

		    $title = "$year Weekly Summary Growth Scorecard for " . $storeName;
		    $titleRows = array();
		    $titleRows[] = array(
		        "",
		        $title
		    );

		    $MFYClause = "";
		    if ($madeForYouOnly)
		    {
		        $MFYClause = " and s.session_type = 'SPECIAL_EVENT' ";
		    }

			$minimum_sql_clause =   self::createMinimumClause($store_id,$menu_id);

		    $weekObj->query("select  m.menu_name, m.id as this_menu_id, WEEK(s.session_start, 3) as week_num, count(distinct b.user_id) as guest_count, GROUP_CONCAT(distinct b.user_id) as guest_list from session s
		        join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
		        join orders o on o.id = b.order_id $minimum_sql_clause
			     join menu m on m.id = s.menu_id
		        where s.menu_id >= $menu_id and s.menu_id <= $lastMenuID
		        and s.store_id = $store_id and s.session_start < '$cutoffTime' $MFYClause
		        group by WEEK(s.session_start, 3)
		        order by s.session_start");

		    while($weekObj->fetch())
		    {
		        $weekArray[$weekObj->week_num] = array("week_num" => $weekObj->week_num, "menu_month" => $weekObj->menu_name,
		            "full_order_guest_count" =>  $weekObj->guest_count);



		        $last_month_menu_id = $weekObj->this_menu_id - 1;
				$minimum_sql_clause =   self::createMinimumClause($store_id,$last_month_menu_id);
		        $lastMonth = new DAO();
		        $lastMonth->query("select distinct b.user_id from session s
		            join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$weekObj->guest_list})
		            join orders o on o.id = b.order_id $minimum_sql_clause
		            where s.menu_id = $last_month_menu_id and s.store_id = $store_id");

		        $weekArray[$weekObj->week_num]['guests_last_month'] =  $lastMonth->N;

		        $next_month_menu_id = $weekObj->this_menu_id + 1;
				$minimum_sql_clause =   self::createMinimumClause($store_id,$next_month_menu_id);
				$nextMonth = new DAO();
		        $nextMonth->query("select distinct b.user_id from session s
		            join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$weekObj->guest_list})
		            join orders o on o.id = b.order_id $minimum_sql_clause
		            where s.menu_id = $next_month_menu_id and s.store_id = $store_id");

		        $weekArray[$weekObj->week_num]['guests_next_month'] =  $nextMonth->N;

		    }

		    $rows = array();
		    $headers = array($storeName => 2, "Pre-Session Data Input" => 2, "LEAD GOAL" => 1, "Post-Session" => 1, "Results" => 1 );
		    $labels = array("Menu Month", "Week", "Total number of Guests in that session with full orders",
		        "Of those, how many had full session  orders LAST MONTH?", "That means there are this many GROWTH OPPORTUNITY guests:",
		        "How many people signed up?", "GROWTH net gain/loss (Don't break the chain)");

		    /*
		    $menuStartMonth = DAO_CFactory::create('menu');
		    $menuStartMonth->id = $menu_id;
		    $menuStartMonth->find(true);
		    $firstWeekData = CDashboardWeekBased::getWeekTimeData($menuStartMonth->global_menu_start_date);

		    $lastWeek = $firstWeekData['week_number'];
		    */

		    $sheetRowNumber = 4;
		    $week = 1;
		    $weekRangeStart = $sheetRowNumber;



		    foreach($weekArray as $weekData)
		    {


                $rows[] = array(
                    $weekData['menu_month'],
                    $weekData['week_num'],
                    $weekData['full_order_guest_count'],
                    $weekData['guests_last_month'],
                    "=C$sheetRowNumber-D$sheetRowNumber",
                    $weekData['guests_next_month'],
                    "=F$sheetRowNumber-D$sheetRowNumber"
                );

		        $sheetRowNumber++;
		    }


		    self::$lastRow = $sheetRowNumber - 1;

		    $columnDescs = array();
		    $columnDescs['A'] = array(
		        'align' => 'center',
		        'width' => 24
		    );
		    $columnDescs['B'] = array(
		        'align' => 'center',
		        'width' => 10
		    );
		    $columnDescs['C'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['D'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['E'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['F'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['G'] = array(
		        'align' => 'right',
		        'width' => 12
		    );

		    $callbacks = array('cell_callback' => 'growthScorecardCellCallback', 'final_render' => 'growthScorecardFinalRenderCallback');

		    if ($export)
		    {
		        $overrideValues = array('main_header_height' => 75, 'header_gradient_start' => 'FFA8B355', 'header_gradient_end' => 'FFA8B355');

		        $_GET['export'] = 'xlsx';

		        $numRows = count($rows);
		        $tpl->assign('labels', $labels);
		        $tpl->assign('rows', $rows);
		        $tpl->assign('rowcount', $numRows);
		        $tpl->assign('sectionHeader', $headers);
		        $tpl->assign('title_rows', $titleRows);
		        $tpl->assign('col_descriptions', $columnDescs);
		        $tpl->assign('excel_callbacks', $callbacks);
		        $tpl->assign('override_values', $overrideValues);



		    }
		    else
		    {
		        PHPExcel_Shared_String::setThousandsSeparator(",");

		        $overrideValues = array('links_target_new_tab' => true, 'header_gradient_start' => 'FFA8B355', 'header_gradient_end' => 'FFA8B355');

		        list($css, $html) =  writeExcelFile("Test", $labels, $rows, true, $titleRows, $columnDescs, false, $callbacks, $headers, true, false, $overrideValues);

		        echo "<html><head>";
		        echo $css;
		        echo "</head><body>";

		        echo $html;
		        echo "</body></html>";
		        exit;

		    }

		}
		else if (self::$reportType == 'month')
		{
		    $anchorDate = $year ."-01-01";

		    $menu_id = CMenu::getMenuIDByAnchorDate($anchorDate);
		    $menuObj = DAO_CFactory::create('menu');
		    $menuObj->id = $menu_id;
		    $menuObj->find(true);

		    $lastMenuID = $menu_id + 12;

		    $storeObj = DAO_CFactory::create('store');
		    $storeObj->id = $store_id;
		    $storeObj->find(true);


		    $storeName = $storeObj->store_name;
		    $MenuName = $menuObj->menu_name;
		    $monthArray = array();
		    $monthObj = new DAO();

		    $title = "$year Monthly Summary Growth Scorecard for " . $storeName;
		    $titleRows = array();
		    $titleRows[] = array(
		        "",
		        $title
		    );

		    $MFYClause = "";
		    if ($madeForYouOnly)
		    {
		        $MFYClause = " and s.session_type = 'SPECIAL_EVENT' ";
		    }

			$minimum_sql_clause = self::createMinimumClause($store_id,$menu_id);

		    $monthObj->query("select iq.*, dm.total_agr from (
                                select m.id as menu_id, m.menu_name, count(distinct b.user_id) as guest_count, GROUP_CONCAT(distinct b.user_id) as guest_list, m.menu_start from session s
                                		        join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
                                		        join orders o on o.id = b.order_id $minimum_sql_clause 
                                			    join menu m on m.id = s.menu_id
                                		        where s.menu_id >= $menu_id and s.menu_id <= $lastMenuID
		                                          and s.store_id = $store_id and s.session_start < '$cutoffTime' $MFYClause
                                		        group by s.menu_id
                                		        order by s.session_start) as iq
                                 join dashboard_metrics_agr_by_menu dm on dm.date = iq.menu_start and dm.store_id = $store_id and dm.is_deleted = 0
                                order by iq.menu_id");

		    while($monthObj->fetch())
		    {
		        $monthArray[$monthObj->menu_id] = array("menu_month" => $monthObj->menu_name,
		            "full_order_guest_count" =>  $monthObj->guest_count, 'revenue' => $monthObj->total_agr);



		        $last_month_menu_id = $monthObj->menu_id - 1;
				$minimum_sql_clause = self::createMinimumClause($store_id,$last_month_menu_id);
		        $lastMonth = new DAO();
		        $lastMonth->query("select distinct b.user_id from session s
		            join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$monthObj->guest_list})
		            join orders o on o.id = b.order_id $minimum_sql_clause 
		            where s.menu_id = $last_month_menu_id and s.store_id = $store_id");

		        $monthArray[$monthObj->menu_id]['guests_last_month'] =  $lastMonth->N;


		        $next_month_menu_id = $monthObj->menu_id + 1;
				$minimum_sql_clause = self::createMinimumClause($store_id,$next_month_menu_id);
		        $nextMonth = new DAO();
		        $nextMonth->query("select distinct b.user_id from session s
		            join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0  and b.user_id in ({$monthObj->guest_list})
		            join orders o on o.id = b.order_id $minimum_sql_clause 
		            where s.menu_id = $next_month_menu_id and s.store_id = $store_id");

		        $monthArray[$monthObj->menu_id]['guests_next_month'] =  $nextMonth->N;

		    }

		    $rows = array();
		    $labels = array(
		        "Month",
                "Total number of Guests in that session with full orders",
		        "Of those, how many had full session  orders LAST MONTH?",
		        "That means there are this many GROWTH OPPORTUNITY guests:",
		        "How many people signed up?",
                "'Don't break the Chain' Net gain/loss",
                "Revenue");

            $headers = array("$year " . $storeName . " " . $storeObj->state_id => 1, "Pre-Session Data Input" => 2, "LEAD GOAL" => 1, "Post-Session" => 1, "Results" => 1, "Revenue" => 1 );


            $sheetRowNumber = 4;
		    $week = 1;
		    $weekRangeStart = $sheetRowNumber;



		    foreach($monthArray as $monthData)
		    {
		        $literalNum = $monthData['guests_next_month'];
		        $rows[] = array( $monthData['menu_month'],
                    $monthData['full_order_guest_count'], // total
                    $monthData['guests_last_month'], // last month
                    "=B$sheetRowNumber-C$sheetRowNumber", //growth
                    $monthData['guests_next_month'], // how many
                    "=$literalNum - C$sheetRowNumber", // DBC
                    $monthData['revenue']
                );

		        $sheetRowNumber++;
		    }


		    if (isset($rows[12]))
		    {
		        $rows[12][3] = "";
		    }


		    self::$lastRow = $sheetRowNumber;

		    $columnDescs = array();
		    $columnDescs['A'] = array(
		        'align' => 'center',
		        'width' => 24
		    );
		    $columnDescs['B'] = array(
		        'align' => 'center',
		        'width' => 10
		    );
		    $columnDescs['C'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['D'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
		    $columnDescs['E'] = array(
		        'align' => 'right',
		        'width' => 12
		    );
            $columnDescs['F'] = array(
                'align' => 'right',
                'width' => 12
            );
            $columnDescs['G'] = array(
		        'align' => 'right',
		        'width' => 12,
		        'type' => 'currency'
		    );


		    $callbacks = array('cell_callback' => 'growthScorecardCellCallback', 'final_render' => 'growthScorecardFinalRenderCallback');

		    if ($export)
		    {
		        $overrideValues = array('main_header_height' => 75, 'header_gradient_start' => 'FFA8B355', 'header_gradient_end' => 'FFA8B355');

		        $_GET['export'] = 'xlsx';

		        $numRows = count($rows);
		        $tpl->assign('labels', $labels);
		        $tpl->assign('rows', $rows);
                $tpl->assign('rowcount', $numRows);
                $tpl->assign('sectionHeader', $headers);
		        $tpl->assign('title_rows', $titleRows);
		        $tpl->assign('col_descriptions', $columnDescs);
		        $tpl->assign('excel_callbacks', $callbacks);
		        $tpl->assign('override_values', $overrideValues);



		    }
		    else
		    {
		        PHPExcel_Shared_String::setThousandsSeparator(",");

		        $overrideValues = array('links_target_new_tab' => true, 'header_gradient_start' => 'FFA8B355', 'header_gradient_end' => 'FFA8B355');

		        list($css, $html) =  writeExcelFile("Test", $labels, $rows, true, $titleRows, $columnDescs, false, $callbacks, $headers, true, false, $overrideValues);

		        echo "<html><head>";
		        echo $css;
		        echo "</head><body>";

		        echo $html;
		        echo "</body></html>";
		        exit;

		    }}

	}

	$tpl->assign('query_form', $Form->render());

 }

 static function createMinimumClause($store_id,$menu_id){
	 $minimum_sql_clause = ' and o.servings_core_total_count > 35';
	 $minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $store_id,$menu_id);

	 $is_qualifying_clause = ' and o.is_qualifying = 1';

	 if(!is_null($minimum)){
		 if($minimum->getMinimumType() == COrderMinimum::SERVING){
			 $minimum_sql_clause = $is_qualifying_clause .' and o.servings_core_total_count >= ' .$minimum->getMinimum();
		 }

		 if($minimum->getMinimumType() == COrderMinimum::ITEM){
			 $minimum_sql_clause = $is_qualifying_clause.' and o.menu_items_core_total_count >= ' .$minimum->getMinimum();
		 }
	 }

	 return $minimum_sql_clause;
 }

    static function getTotalFormula($column, $totalRows)
    {
        $retVal = "=";
        foreach($totalRows as $row)
        {
            $retVal .= $column . $row . "+";
        }

        $retVal = trim($retVal,"+");
        return $retVal;
    }

     static function getAverageFormula($column, $totalRows)
     {
         $count = 0;
         $retVal = "=(";
         foreach($totalRows as $row)
         {
             $retVal .= $column . $row . "+";
             $count++;
         }

         $retVal = trim($retVal,"+");
         $retVal .= ") / " . $count;
         return $retVal;
     }

 }



?>