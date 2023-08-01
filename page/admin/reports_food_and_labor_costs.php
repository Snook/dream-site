<?php // page_admin_create_store.php

/**
 * @author Carl Samuelson
 */


 require_once("includes/CPageAdminOnly.inc");
 require_once ('includes/DAO/BusinessObject/CStoreCredit.php');
 require_once ('includes/CSessionReports.inc');
require_once ('includes/CDashboardReportWeekBased.inc');
require_once('phplib/PHPExcel/PHPExcel.php');

require_once("DAO/BusinessObject/CImport.php");

require_once('ExcelExport.inc');

function sort_master_array_by_date($a, $b)
{
    $atime = strtotime($a);
    $btime = strtotime($b);

    if ($atime == $btime)
        return 0;

    return ($atime < $btime) ? -1 : 1;
}


function CompReportFinalRenderCallback($sheet, $rows)
{
    $mediumBorderArray = array(
        'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000')),
            'top' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000') ),
            'right' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000') ),
            'left' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000') )));

    $bottomThinBorder = array(
        'borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));


    $row = page_admin_reports_food_and_labor_costs::$rowsMap['first_hour_table_row'];
    $end_row = page_admin_reports_food_and_labor_costs::$rowsMap['last_hour_table_row'];
    $end_col = page_admin_reports_food_and_labor_costs::$rowsMap['last_store_column'];

    $sheet->getStyle("A$row:$end_col$end_row")->applyFromArray($mediumBorderArray);
    $end_row--;
    $sheet->getStyle("A$end_row:$end_col$end_row")->applyFromArray($bottomThinBorder);


    $row = page_admin_reports_food_and_labor_costs::$rowsMap['first_metrics_table_row'];
    $end_row = page_admin_reports_food_and_labor_costs::$rowsMap['last_metrics_table_row'];

    $sheet->getStyle("A$row:$end_col$end_row")->applyFromArray($mediumBorderArray);
    $sheet->mergeCells("A$row:$end_col$row");
    $sheet->getStyle("A$row:$end_col$row")->applyFromArray($bottomThinBorder);

    $row = page_admin_reports_food_and_labor_costs::$rowsMap['first_visits_table_row'];
    $end_row = page_admin_reports_food_and_labor_costs::$rowsMap['last_visits_table_row'];

    $sheet->getStyle("A$row:$end_col$end_row")->applyFromArray($mediumBorderArray);
    $sheet->mergeCells("A$row:$end_col$row");
    $sheet->getStyle("A$row:$end_col$row")->applyFromArray($bottomThinBorder);
    $row++;
    $sheet->getStyle("A$row:$end_col$end_row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
    $end_row--;
    $sheet->getStyle("A$end_row:$end_col$end_row")->applyFromArray($bottomThinBorder);


    $row = page_admin_reports_food_and_labor_costs::$rowsMap['first_servings_table_row'];
    $end_row = page_admin_reports_food_and_labor_costs::$rowsMap['last_servings_table_row'];

    $sheet->getStyle("A$row:$end_col$end_row")->applyFromArray($mediumBorderArray);
    $sheet->mergeCells("A$row:$end_col$row");
    $sheet->getStyle("A$row:$end_col$row")->applyFromArray($bottomThinBorder);
    $row++;
    $sheet->getStyle("A$row:$end_col$end_row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
    $end_row--;
    $sheet->getStyle("A$end_row:$end_col$end_row")->applyFromArray($bottomThinBorder);


    $row = page_admin_reports_food_and_labor_costs::$rowsMap['first_sides_table_row'];
    $end_row = page_admin_reports_food_and_labor_costs::$rowsMap['last_sides_table_row'];

    $sheet->getStyle("A$row:$end_col$end_row")->applyFromArray($mediumBorderArray);
    $sheet->mergeCells("A$row:$end_col$row");
    $sheet->getStyle("A$row:$end_col$row")->applyFromArray($bottomThinBorder);
    $row++;
    $sheet->getStyle("A$row:$end_col$end_row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
    $end_row--;
    $sheet->getStyle("A$end_row:$end_col$end_row")->applyFromArray($bottomThinBorder);


}

function ReportFinalRenderCallback($sheet, $rows)
{
    $lastStart = 4;
    $lastLength = 0;

    foreach(page_admin_reports_food_and_labor_costs::$sessionGroupingMap as $startRow => $length)
    {
        if ($length)
        {
            $end = $startRow + $length - 1;
            $sheet->mergeCells("B$startRow:B$end");
            $sheet->getStyle("B$startRow:B$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B$startRow:B$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("C$startRow:C$end");
            $sheet->getStyle("C$startRow:C$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C$startRow:v$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


            $sheet->mergeCells("D$startRow:D$end");
            $sheet->getStyle("D$startRow:D$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D$startRow:D$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("E$startRow:E$end");
            $sheet->getStyle("E$startRow:E$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E$startRow:E$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("F$startRow:F$end");
            $sheet->getStyle("F$startRow:F$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F$startRow:F$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("G$startRow:G$end");
            $sheet->getStyle("G$startRow:G$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G$startRow:G$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("H$startRow:H$end");
            $sheet->getStyle("H$startRow:H$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H$startRow:H$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("I$startRow:I$end");
            $sheet->getStyle("I$startRow:I$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I$startRow:I$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("N$startRow:N$end");
            $sheet->getStyle("N$startRow:N$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N$startRow:N$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("O$startRow:O$end");
            $sheet->getStyle("O$startRow:O$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("O$startRow:O$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue("N$startRow", "=SUM(K$startRow:M$end)");
            $sheet->setCellValue("O$startRow", "=SUM(K$startRow:M$end) / E$startRow");

            $styleArray = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'FF000000'))));
            $sheet->getStyle("B$startRow:O$end")->applyFromArray($styleArray);

        }
    }

    foreach(page_admin_reports_food_and_labor_costs::$dateGroupingMap as $startRow => $length)
    {
        if ($length)
        {
            $end = $startRow + $length - 1;
            $sheet->mergeCells("A$startRow:A$end");
            $sheet->getStyle("A$startRow:A$end")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A$startRow:A$end")->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $styleArray = array('borders' => array('bottom' => array( 'style' => PHPExcel_Style_Border::BORDER_MEDIUM, 'color' => array('argb' => 'FF000000'))));
            $sheet->getStyle("A$startRow:O$end")->applyFromArray($styleArray);

            $styleArray = array('font' => array( 'bold' => true ));
            $sheet->getStyle("A$startRow:A$end")->applyFromArray($styleArray);
        }
    }

}

function ReportCellCallback($sheet, $colName, $datum, $col, $row)
{

    if ($datum == "!")
    {
        $styleArray = array('font' => array('bold' => true), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('argb' =>"99F7686A")));

        $sheet->getStyle("$col$row")->applyFromArray($styleArray);

    }

}


 class page_admin_reports_food_and_labor_costs extends CPageAdminOnly
{

	 function __construct()
	 {
		 parent::__construct();
		 $this->cleanReportInputs();
	 }

    private $shiftsArray = array();
    private $masterArray = array();
    static $dateGroupingMap = array();
    static $sessionGroupingMap = array();
    static $rowsMap = array('first_hour_table_row' => 0, 'last_hour_table_row' => 0,
        'first_visits_table_row' => 0, 'last_visits_table_row' => 0,
        'first_servings_table_row' => 0, 'last_servings_table_row' => 0,
        'first_sides_table_row' => 0, 'last_sides_table_row' => 0,
        'first_metrics_table_row' => 0, 'last_metrics_table_row' => 0,
        'first_store_column' => 'B', 'last_store_column' => 'B');

    public function runSiteAdmin()
    {
        $this->run();
    }

    public function runHomeOfficeManager()
    {
        $this->run();
    }


    function safeDivide($dividend, $divisor)
    {
        if (empty($divisor))
        {
            return 0;
        }

        return $dividend / $divisor;
    }


  static $ExcelColumnToDBColumnMap = array('EE ID' => 'employee_id',
    'Employee Name' => 'employee_name',
    'Shift Date' => 'shift_date',
    'Day' => 'day',
    'Pay Type' => '!',
    'Reg Hours' => 'reg_hours',
    'OT1 Hours' => 'OT1_hours',
    'OT2 Hours' => 'OT2_hours',
    'Unpaid Hours' => 'unpaid_hours',
    'Time In' => 'time_in',
    'Time Out' => 'time_out',
    'Company' => '!',
    'Department' => 'department',
    'Jobs' => 'jobs',
    'Reg Charge Rate' => '!',
    'OT Charge Rate' => '!',
    'Reg Charge Amount' => '!',
    'OT Charge Amount' => '!',
    'Total Charge Amount' => '!',
    'Reg Pay Rate' => 'reg_pay_rate',
    'OT Pay Rate' => 'OT_pay_rate',
    'Reg Paid' => 'reg_paid',
    'OT Paid' => 'OT_paid',
    'Total Pay Amount' => 'total_pay_amount',
	'CC2' => 'department',
    'CC3' => 'jobs'
  );


    private function run()
    {


        ini_set('memory_limit','-1');
        set_time_limit(3600 * 24);

        $tpl = CApp::instance()->template();

        $Form = new CForm();
        $Form->Repost = TRUE;

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

        $year = date("Y");
        $monthnum = date("n");
        $monthnum--;
        $Form->AddElement(array(
            CForm::type => CForm::Text,
            CForm::name => "year_field_001",
            CForm::required => true,
            CForm::default_value => $year,
            CForm::length => 6
        ));

        $Form->AddElement(array(
            CForm::type => CForm::Text,
            CForm::name => "year_field_002",
            CForm::required => true,
            CForm::default_value => $year,
            CForm::length => 6
        ));

        $firstYear = 2019;  // no data before then
        $thisYear = $firstYear;
        $weekYearOptions = array();
        while ($thisYear <= $year)
        {
            $weekYearOptions[$thisYear] = $thisYear;
            $thisYear++;
        }
        $weekYearOptions['2020'] = '2020';

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::name => "year_week",
            CForm::onChangeSubmit => true,
            CForm::options => $weekYearOptions
        ));

        $weekYear = $Form->value('year_week');

        $weeks = array();
        $weekDate = new DateTime("$weekYear-01-01 00:00:00");
        $dayNum = $weekDate->format('w');
        while($dayNum != 1)
        {
            $weekDate->modify('-1 days');
            $dayNum = $weekDate->format('w');
        }

        $nextYear = $weekYear + 1;
        while(strtotime($weekDate->format('Y-m-d 00:00:00')) < strtotime("$nextYear-01-01 00:00:00"))
        {
            $curWeek = $weekDate->format('W');
            $curYear = $weekDate->format('o');
            $weeks[strtotime($weekDate->format('Y-m-d'))] = "Week " .$curWeek . " ("  . $weekDate->format('Y-m-d') . ")";
            $weekDate->modify('+7 days');

        }

        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::name => "week",
            CForm::options => $weeks
        ));


        $Form->AddElement(array(
            CForm::type => CForm::DropDown,
            CForm::onChangeSubmit => false,
            CForm::allowAllOption => false,
            CForm::options => $month_array,
            CForm::default_value => $monthnum,
            CForm::name => 'month_popup'
        ));


        $Form->DefaultValues['menu_or_calendar'] = 'menu';
        $Form->AddElement(array(
            CForm::type => CForm::RadioButton,
            CForm::name => "menu_or_calendar",
            CForm::required => true,
            CForm::value => 'cal'
        ));

        $Form->AddElement(array(
            CForm::type => CForm::RadioButton,
            CForm::name => "menu_or_calendar",
            CForm::required => true,
            CForm::value => 'menu'
        ));

        if (isset ($_REQUEST["single_date"]))
        {
            $day_start = $_REQUEST["single_date"];
            $tpl->assign('day_start_set', $day_start);
        }

        if (isset ($_REQUEST["range_day_start"]))
        {
            $range_day_start = $_REQUEST["range_day_start"];
            $tpl->assign('range_day_start_set', $range_day_start);
        }

        if (isset ($_REQUEST["range_day_end"]))
        {
            $range_day_end = $_REQUEST["range_day_end"];
            $tpl->assign('range_day_end_set', $range_day_end);
        }


        if (isset($_POST['submit_labor_input']) && $_FILES['labor_input_file']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['labor_input_file']['tmp_name']))
        {
            $error = false;

            if (!$fp = file_get_contents($_FILES['labor_input_file']['tmp_name']))
            {
                CLog::Record('Labor Report Input failed: fopen failed');
                $tpl->setErrorMsg('Labor Report Input failed: fopen failed');
                throw new Exception("Could not open Input file");
            }

            $inputFileName = $_FILES['labor_input_file']['tmp_name'];
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($inputFileName);
            $objWorksheet = $objPHPExcel->getActiveSheet();

            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

            $rows = array();

            // distill the object into an array
            for ($row = 1; $row <= $highestRow; ++$row)
            {
                $rows[$row] = array();

                // load excel obj into an array
                for ($col = 0; $col <= $highestColumnIndex; ++$col)
                {
                    $rows[$row][$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                }
            }

            $positionToColumnMap = array();

            foreach($rows as $thisRow)
            {
                // Use Pay type column to find beginning og data rows
                if (empty($thisRow[5]))
                {
                    continue;
                }
                else if ($thisRow[5] == "Pay Type")
                {
                    $position = 0;
                    foreach($thisRow as $thisLabel)
                    {
                        $positionToColumnMap[$position++] = self::$ExcelColumnToDBColumnMap[$thisLabel];
                    }
                    continue;
                }

                $position = 0;
                $entryDAO = DAO_CFactory::create('employee_hours');
                foreach($thisRow as $thisValue) {
                    $thisColumn = $positionToColumnMap[$position++];

                    if ($thisColumn == 'employee_name') {
                        $temparr = explode(",", $thisValue);
                        $lastName = trim($temparr[0]);
                        $firstName = trim($temparr[1]);
                        $entryDAO->user_id = self::lookupUserID($firstName, $lastName);
                        $entryDAO->$thisColumn = $thisValue;


                    } else if ($thisColumn == 'department') {
                        $temparr = explode("-", $thisValue);
                        $storeHOID = trim($temparr[0]);

                        if ($thisValue == '258 - Granada Hills, CA')
						{
							$entryDAO->store_id = 85;
						}
						else
						{
							$entryDAO->store_id = self::lookupStoreID($storeHOID);
						}
                        $entryDAO->$thisColumn = $thisValue;


                    } else if ($thisColumn == 'shift_date') {
                        $thisValue = date("Y-m-d", strtotime($thisValue));
                        $entryDAO->$thisColumn = $thisValue;

                    } else if ($thisColumn == 'time_in' || $thisColumn == 'time_out') {
                        $thisValue = date("H:i:s", strtotime($thisValue));
                        $entryDAO->$thisColumn = $thisValue;

                    } else if ($thisColumn == 'jobs') {
                        if (empty($thisValue) || $thisValue == " - ")
                        {
                            $thisValue = "000 - Unknown";
                        }

                        $entryDAO->$thisColumn = $thisValue;

                    }
                    else if ($thisColumn != '!')
                    {
                        if (empty($thisColumn))
                        {
                            continue; // merged cells around employee name
                        }

                         $entryDAO->$thisColumn = $thisValue;
                    }
                }


                // TODO : need to check that a duplicate is not being added. But how to determine this?
                // If emplayee and hours match exactly?
                // If not an exact match but overlap occurs should we update instead of insert?

                $entryDAO->insert();
            }

            $tpl->setStatusMsg("File successfully imported");
        }


        $Form->AddElement(array (CForm::type => CForm::Submit,
            CForm::name => 'report_submit',
            CForm::css_class => 'button',
            CForm::value => 'Run Report'));

        $Form->AddElement(array (CForm::type => CForm::DropDown,
            CForm::name => 'report_type',
            CForm::options => array('3' => 'Summary by Job code', '1' => 'Hours by Session')));

        $storeOptions = array();
        $selectStoreDAO = new DAO();
        $selectStoreDAO->query("select id, store_name from store where active = 1 and is_deleted = 0 and is_corporate_owned = 1");
        while($selectStoreDAO->fetch())
        {
            $storeOptions[$selectStoreDAO->id] = $selectStoreDAO->store_name;
        }

        $Form->AddElement(array (CForm::type => CForm::DropDown,
            CForm::name => 'corp_store',
            CForm::options => $storeOptions));

        if (isset($_REQUEST["pickDate"]))
        {
            $report_type_to_run = $_REQUEST["pickDate"];
        }

        if (isset($_POST['report_submit']) && $_POST['report_submit'] == 'Run Report')
        {

            if ($report_type_to_run == 1)
            {
                // single date
                $range_day_start = $day_start;
                $range_day_end = date("Y-m-d", strtotime($day_start) + 86400);
            }
            else if ($report_type_to_run == 2)
            {
                // this is the summary report for the time range
                if (strtotime($range_day_start) > strtotime($range_day_end))
                {
                    $temp = $range_day_start;
                    $range_day_start = $range_day_end;
                    $range_day_end = $temp;
                }
            }
            else if ($report_type_to_run == 3)
            {
                // process for a given month

                if ($Form->value('menu_or_calendar') == 'menu')
                {

                    $month = $_REQUEST["month_popup"];
                    $month++;
                    $year = $_REQUEST["year_field_001"];

                    $dateStr = date("Y-m-d", mktime(0,0,0,$month, 1, $year));

                    $theMenu = DAO_CFactory::create('menu');
                    $theMenu->query("select id, menu_start, global_menu_start_date, DATEDIFF(global_menu_end_date, global_menu_start_date) + 1 as day_interval from menu where menu_start = '$dateStr'");
                    $theMenu->fetch();

                    $range_day_start = $theMenu->global_menu_start_date;
                    $range_day_end = date("Y-m-d", strtotime($range_day_start) + $theMenu->day_interval * 86400);

                    // get menu anchor month
                    $anchorDateArr = explode("-", $theMenu->menu_start);
                    $isMenuMonth = $theMenu->id;
                    $menuMonthMonthYear = ltrim($anchorDateArr[1], "0") . " " . $anchorDateArr[0];

                }
                else
                {
                    $day = "01";
                    $month = $_REQUEST["month_popup"];
                    $month++;
                    $year = $_REQUEST["year_field_001"];

                    $range_day_start = "$year-$month-$day";
                    $weekDate = new DateTime($range_day_start);
                    $weekDate->modify("+ 1 month");
                    $range_day_end = $weekDate->format("Y-m-d");
                }
            }
            else if ($report_type_to_run == 4)
            {
                $year = $_REQUEST["year_field_002"];
                $month = "01";
                $day = "01";

                $range_day_start = "$year-$month-$day";
                $date = new DateTime($range_day_start);
                $date->modify("+ 1 year");
                $range_day_end = $date->format("Y-m-d");

            }
            else if  ($report_type_to_run == 5)
            {
                $range_day_start = date("Y-m-d", $Form->value('week'));
                $weekDate = new DateTime($range_day_start);
                $weekDate->modify("+ 1 week");
                $range_day_end = $weekDate->format("Y-m-d");

            }


            $report_type = $Form->value('report_type');

            if ($report_type == 1)
            {
                $store_id = $Form->value('corp_store');
                $storeObj = new DAO();
                $storeObj->query("select id, store_name, city, state_id from store where id = $store_id");
                $storeObj->fetch();

                $title = makeTitle("Corporate Store Labor Hours by Session", $storeObj, $range_day_start . " to " . $range_day_end);

                // header
                $titleRows = array();
                $titleRows[] = array("Dream Dinners - Corporate Store Labor Hours by Session | Store: " . $storeObj->store_name ."," . $storeObj->state_id);
                $titleRows[] = array("For Period: " . $range_day_start . " to " . $range_day_end);

                list($rows, $labels, $colDesc) = $this->retrieveRangeSessionDetail($store_id, $range_day_start, $range_day_end);

                if (empty($rows))
                {
                    $tpl->setErrorMsg('No data for time period and store.');
					$tpl->assign('no_data', true);
					$tpl->assign('pickdate', $report_type_to_run);
				}
                else
                {
                    $tpl->assign('file_name', $title);

                    // spit out excel sheet
                    $tpl->assign('rows', $rows);

                    $callbacks = array('final_render' => 'ReportFinalRenderCallback', 'cell_callback' => 'ReportCellCallback');
                    $tpl->assign('excel_callbacks', $callbacks);

                    $tpl->assign('title_rows', $titleRows);
                    $tpl->assign('col_descriptions', $colDesc);
                    $tpl->assign('labels', $labels);
                    $_GET['export'] = 'xlsx';

                }

            }
            else if ($report_type == 2)
            {
                $title = makeTitle("Corporate Store Labor Hours by Code", $storeObj, $range_day_start . " to " . $range_day_end);

                // header
                $titleRows = array();
                $titleRows[] = array("Dream Dinners - Corporate Store Labor Hours by Code");
                $titleRows[] = array("For Period: " . $range_day_start . " to " . $range_day_end);

                list($rows, $labels, $colDesc) = $this->retrieveRange($range_day_start, $range_day_end);
                if (empty($rows))
                {
                    $tpl->setErrorMsg('No data for time period and store.');
                }
                else
                {

                    $tpl->assign('file_name', $title);

                    // spit out excel sheet
                    $tpl->assign('rows', $rows);

                    //      $callbacks = array('final_render' => 'ReportFinalRenderCallback', 'cell_callback' => 'ReportCellCallback');
                    //       $tpl->assign('excel_callbacks', $callbacks);

                    $tpl->assign('title_rows', $titleRows);
                    $tpl->assign('col_descriptions', $colDesc);
                    $tpl->assign('labels', $labels);
                    $_GET['export'] = 'xlsx';
                }
            }
            else if ($report_type == 3)
            {
                $title = makeTitle("Corporate Store Labor Hours by Code", $storeObj, $range_day_start . " to " . $range_day_end);

                // header
                $titleRows = array();
                $titleRows[] = array("Dream Dinners - Corporate Store Labor Hours by Code");
                $titleRows[] = array("For Period: " . $range_day_start . " to " . $range_day_end);

                list($rows, $labels, $colDesc) = $this->retrieveRangeComparison($range_day_start, $range_day_end);
                if (empty($rows))
                {
                    $tpl->setErrorMsg('No data for time period and store.');
                }
                else
                {

                    $tpl->assign('file_name', $title);

                    // spit out excel sheet
                    $tpl->assign('rows', $rows);

                    $callbacks = array('final_render' => 'CompReportFinalRenderCallback');
                    $tpl->assign('excel_callbacks', $callbacks);

                    $tpl->assign('title_rows', $titleRows);
                    $tpl->assign('col_descriptions', $colDesc);
                    $tpl->assign('labels', $labels);
                    $_GET['export'] = 'xlsx';
                }
            }
        }

        $tpl->assign('form_session_list', $Form->render());

        header_remove('Set-Cookie');
    }

     function retrieveRangeSessionDetail($store, $range_day_start, $range_day_end)
     {
         $rows = array();


         $range_day_start = strtotime($range_day_start);
         $range_day_end = strtotime($range_day_end);

         if ($range_day_end < $range_day_start) {
             $temp = $range_day_start;
             $range_day_start = $range_day_end;
             $range_day_end = $temp;
         }

         $range_day_start = date("Y-m-d H:i:s", $range_day_start);
         $range_day_end = date("Y-m-d H:i:s", $range_day_end);

         $Sessions = new DAO();
         $Sessions->query("select DATE(s.session_start) as theDay, s.id, s.session_start, s.session_type, s.duration_minutes, count(distinct b.order_id) as attendees, count(distinct sr.user_id) as rsvpers from session s
                            left join booking b on b.session_id = s.id and b.status = 'ACTIVE' and b.is_deleted = 0
                            left join session_rsvp sr on sr.session_id = s.id  AND sr.upgrade_booking_id IS NULL and sr.is_deleted = 0
                            where s.session_start >= '$range_day_start' and s.session_start < '$range_day_end' and s.store_id = $store and s.is_deleted = 0 and s.session_publish_state <> 'SAVED'
                            group by s.id order by s.session_start");


         while ($Sessions->fetch()) {
             if (!isset($this->masterArray[$Sessions->theDay])) {
                 $this->masterArray[$Sessions->theDay] = array();
             }

             $this->masterArray[$Sessions->theDay][$Sessions->id] = DAO::getCompressedArrayFromDAO($Sessions);
         }

         // Normalize sessions
         /*
                  foreach($this->masterArray as $date => $sessions)
                  {
                      $dayRowCount = 0;
                      foreach($sessions as $SID => $sessionData)
                      {
                          if (!empty($sessionData['employees']))
                          {
                              $sessionRowCount = 0;
                              foreach ($sessionData['employees'] as $eid => $data)
                              {
                                  $dayRowCount++;
                                  $sessionRowCount++;
                              }

                              self::$sessionGroupingMap[$curSessionPos] = $sessionRowCount;
                              $curSessionPos += $sessionRowCount;
                          }
                      }
                      if ($dayRowCount)
                      {
                          self::$dateGroupingMap[$curDatePos] = $dayRowCount;
                          $curDatePos += $dayRowCount;
                      }
                  }

         */

         $dao = new DAO();
         $dao->query("select * from employee_hours where shift_date >= '$range_day_start' and shift_date <= '$range_day_end' and store_id = $store");

         if ($dao->N == 0) {
             return array(false, false, false);
         }

         while ($dao->fetch()) {
             if (!isset($this->shiftsArray[$dao->shift_date])) {
                 $this->shiftsArray[$dao->shift_date] = array();
             }

             if (!isset($this->shiftsArray[$dao->shift_date][$dao->employee_id])) {
                 $this->shiftsArray[$dao->shift_date][$dao->employee_id] = array();
             }

             if (!isset($this->shiftsArray[$dao->shift_date][$dao->employee_id][$dao->jobs])) {
                 $this->shiftsArray[$dao->shift_date][$dao->employee_id][$dao->jobs] = array();
             }

             $this->shiftsArray[$dao->shift_date][$dao->employee_id][$dao->jobs] = DAO::getCompressedArrayFromDAO($dao);

         }


         foreach ($this->shiftsArray as $date => $employees) {
             foreach ($employees as $thisDude => $jobs) {
                 foreach ($jobs as $jobType => $data) {
                     if ($jobType == "910 - Sessions" || $jobType == "907 - Tastes/Private Events" || $jobType == "922 - Session Lead") {
                         $shiftData = $this->assignHours($date, $thisDude, $jobType, $data);
                         foreach ($shiftData as $sid => $sData) {
                             if (!isset($this->masterArray[$date][$sid]['employees'])) {
                                 $this->masterArray[$date][$sid]['employees'] = array();
                             }

                             if (isset($this->masterArray[$date][$sid]['employees'][$thisDude])) {
                                 if (isset($sData['timeThisSession'])) {
                                     if (!empty($this->masterArray[$date][$sid]['employees'][$thisDude]['timeThisSession'])) {
                                         $this->masterArray[$date][$sid]['employees'][$thisDude]['timeThisSession'] += $sData['timeThisSession'];
                                     } else {
                                         $this->masterArray[$date][$sid]['employees'][$thisDude]['timeThisSession'] = $sData['timeThisSession'];
                                     }
                                 }

                                 if (isset($sData['timeThisEvent'])) {
                                     if (!empty($this->masterArray[$date][$sid]['employees'][$thisDude]['timeThisEvent'])) {
                                         $this->masterArray[$date][$sid]['employees'][$thisDude]['timeThisEvent'] += $sData['timeThisEvent'];
                                     } else {
                                         $this->masterArray[$date][$sid]['employees'][$thisDude]['timeThisEvent'] = $sData['timeThisEvent'];
                                     }
                                 }

                                 if (isset($sData['sessionLeadTime'])) {
                                     if (!empty($this->masterArray[$date][$sid]['employees'][$thisDude]['sessionLeadTime'])) {
                                         $this->masterArray[$date][$sid]['employees'][$thisDude]['sessionLeadTime'] += $sData['sessionLeadTime'];
                                     } else {
                                         $this->masterArray[$date][$sid]['employees'][$thisDude]['sessionLeadTime'] = $sData['sessionLeadTime'];
                                     }
                                 }

                             } else {
                                 $this->masterArray[$date][$sid]['employees'][$thisDude] = $sData;
                             }
                         }
                     } else {

                     }
                 }
             }
         }

         $curDatePos = 4;
         $curSessionPos = 4;

         foreach ($this->masterArray as $date => $sessions) {
             $dayRowCount = 0;
             foreach ($sessions as $SID => $sessionData) {
                 if (!empty($sessionData['employees'])) {
                     $sessionRowCount = 0;
                     foreach ($sessionData['employees'] as $eid => $data) {
                         $dayRowCount++;
                         $sessionRowCount++;
                     }

                     self::$sessionGroupingMap[$curSessionPos] = $sessionRowCount;
                     $curSessionPos += $sessionRowCount;
                 }
             }
             if ($dayRowCount) {
                 self::$dateGroupingMap[$curDatePos] = $dayRowCount;
                 $curDatePos += $dayRowCount;
             }
         }

// validate

         $validate = true;

         if ($store = 194)
         {
             $validate = false;
         }


         if ($validate)
         {
             foreach ($this->masterArray as $date => $sessions)
             {

                 $dateTotal = array();
                 $shiftTotal = array();
                 foreach ($sessions as $SID => $sessionData)
                 {
                     if (!empty($sessionData['employees']))
                     {
                         foreach ($sessionData['employees'] as $eid => $data)
                         {
                             if (!isset($dateTotal[$eid]))
                             {
                                 $dateTotal[$eid] = 0;
                             }

                             if (!empty($data['timeThisSession']))
                             {
                                 $dateTotal[$eid] += $data['timeThisSession'];
                             }

                             if (!empty($data['timeThisEvent']))
                             {
                                 $dateTotal[$eid] += $data['timeThisEvent'];
                             }

                             if (!empty($data['sessionLeadTime']))
                             {
                                  $dateTotal[$eid] += $data['sessionLeadTime'];
                             }
                         }
                     }
                 }

                 foreach ($this->shiftsArray[$date] as $eid => $codes)
                 {
                     foreach ($codes as $code => $data)
                     {
                         if ($code == "910 - Sessions" || $code == "907 - Tastes/Private Events" || $code == "922 - Session Lead" )
                         {
                             if (!isset($shiftTotal[$eid]))
                             {
                                 $shiftTotal[$eid] = 0;
                             }

                             $time_in_TS = strtotime($date . " " . $data['time_in']);
                             $time_out_TS = strtotime($date . " " . $data['time_out']);
                             $dur = $time_out_TS - $time_in_TS;
                             $shiftTotal[$eid] += $dur;
                         }
                     }
                 }

                 foreach ($shiftTotal as $eid => $total)
                 {
                     if ($total <> $dateTotal[$eid])
                     {
                          throw new Exception("date: " . $date . " eid: " . $eid . "\r\n" . print_r($shiftTotal, true). "\r\n" . print_r($dateTotal, true). "\r\n" . print_r($this->masterArray, true));
                     }
                 }

             }
         }


         $rows = array();
         foreach($this->masterArray as $date => $sessions)
         {
             $firstDay = true;
             foreach($sessions as $SID => $sessionData)
             {
             	 if (!empty($sessionData['employees']))
				 {
					 $firstSession = true;
					 foreach ($sessionData['employees'] as $eid => $data)
					 {
						 $thisRow = array();
						 if ($firstDay)
						 {
							 $thisRow[] = CTemplate::dateTimeFormat($date, MONTH_DAY_YEAR);
						 }
						 else
						 {
							 $thisRow[] = "";
						 }

						 if ($firstSession)
						 {
							 if ($SID == 'catch_all')
							 {
								 $thisRow[] = "Catch All";
								 $thisRow[] = "No Session";
								 $thisRow[] = 0;
								 $thisRow[] = 0;
								 $thisRow[] = '-';
								 $thisRow[] = '-';
								 $thisRow[] = '-';
								 $thisRow[] = '-';
							 }
							 else
							 {
								 $thisRow[] = Ctemplate::dateTimeFormat($sessionData['session_start'], TIME_ONLY);
								 $thisRow[] = $sessionData['session_type'];
								 $thisRow[] = $sessionData['duration_minutes'];
								 $thisRow[] = $sessionData['attendees'];
								 $thisRow[] = $sessionData['attendees'] . "/" . count($sessionData['employees']);
								 $thisRow[] = ($sessionData['attendees'] / count($sessionData['employees']) < 3.0 ? "!" : "");
								 $thisRow[] = ($sessionData['attendees'] < 4 ? "!" : "");
								 $thisRow[] = ($sessionData['attendees'] < 6 ? "!" : "");
							 }
						 }
						 else
						 {
							 $thisRow[] = "";
							 $thisRow[] = "";
							 $thisRow[] = "";
							 $thisRow[] = "";
							 $thisRow[] = "";
							 $thisRow[] = "";
							 $thisRow[] = "";
							 $thisRow[] = "";
						 }

						 if (!empty($this->shiftsArray[$date][$eid]['910 - Sessions']['employee_name']))
						 {
							 $thisRow[] = $this->shiftsArray[$date][$eid]['910 - Sessions']['employee_name'];
						 }
						 else if (!empty($this->shiftsArray[$date][$eid]['907 - Tastes/Private Events']['employee_name']))
						 {
							 $thisRow[] = $this->shiftsArray[$date][$eid]['907 - Tastes/Private Events']['employee_name'];
						 }
						 else if (!empty($this->shiftsArray[$date][$eid]['922 - Session Lead']['employee_name']))
						 {
							 $thisRow[] = $this->shiftsArray[$date][$eid]['922 - Session Lead']['employee_name'];
						 }

						 $thisRow[] = CTemplate::number_format($data['timeThisSession'] / 3600, 3);
						 $thisRow[] = CTemplate::number_format($data['timeThisEvent'] / 3600, 3);
						 $thisRow[] = CTemplate::number_format($data['sessionLeadTime'] / 3600, 3);

						 $firstDay = false;
						 $firstSession = false;
						 $rows[] = $thisRow;
					 }
				 }
             }
         }

         $labels = array("Date", "Session", "Session Type", "Session Duration", "Guest Count", "Ratio", "Ratio Exception",
             "4 Guest Minimum Exception", "6 Guest Min Exception", "Employee Name", "910 - Session Time assigned (hours)",
             "907 - Event Time assigned (hours)", "922 - Lead Time assigned (hours)", "Total Hours", "Hours per Guest");

         $columnDescs = array();
         $columnDescs["A"] = array('align' => 'left', 'width' => 15);
         $columnDescs["B"] = array('align' => 'left', 'width' => 15);
         $columnDescs["C"] = array('align' => 'left', 'width' => 15);
         $columnDescs["D"] = array('align' => 'left', 'width' => 10);
         $columnDescs["E"] = array('align' => 'left', 'width' => 8);
         $columnDescs["F"] = array('align' => 'left', 'width' => 8);
         $columnDescs["G"] = array('align' => 'left', 'width' => 6);
         $columnDescs["H"] = array('align' => 'left', 'width' => 6);
         $columnDescs["I"] = array('align' => 'left', 'width' => 6);
         $columnDescs["J"] = array('align' => 'left', 'width' => 20);
         $columnDescs["K"] = array('align' => 'left', 'width' => 15, 'type' => 'number_xxx');
         $columnDescs["L"] = array('align' => 'left', 'width' => 15, 'type' => 'number_xxx');
         $columnDescs["M"] = array('align' => 'left', 'width' => 15, 'type' => 'number_xxx');
         $columnDescs["N"] = array('align' => 'left', 'width' => 10, 'type' => 'number_xxx');
         $columnDescs["O"] = array('align' => 'left', 'width' => 10, 'type' => 'number_xxx');

         return array($rows,$labels,$columnDescs);

     }

     function findClosestSession($date, $time_in_TS, $time_out_TS)
     {
         $candidateList = array();
         foreach($this->masterArray[$date] as $session_id => $data)
         {
             $session_start_TS = strtotime($data['session_start']);
             $duration = $data['duration_minutes'] * 60;
             $session_end_TS = strtotime($data['session_start']) + $duration;
             $delta = 0;

             if ($session_start_TS > $time_out_TS)
             {
                 $delta = $session_start_TS - $time_out_TS;
             }
             else
             {
                 $delta = $time_in_TS - $session_end_TS;
             }

             $candidateList[$session_id] = $delta;
         }

         if (empty($candidateList))
         {
             return false;
         }

         asort($candidateList);
         reset($candidateList);
         $chosen_session = key($candidateList);
         $sessioninfo = $this->masterArray[$date][$chosen_session];

         $session_start_TS = strtotime($sessioninfo['session_start']);
         $duration = $sessioninfo['duration_minutes'] * 60;
         $session_end_TS = strtotime($sessioninfo['session_start']) + $duration;

         $retVal = array('id' => $chosen_session, 'relation' => "outside_shift", 'session_start_TS' => $session_start_TS, 'session_end_TS' => $session_end_TS, 'duration' => $duration);

         return $retVal;
     }

     function retrieveSessions($date, $time_in_TS, $time_out_TS)
     {

         $retVal = array();
         $lastSessionEndTime = 0;
         foreach($this->masterArray[$date] as $session_id => $data)
         {
             // Note: sessions are stored in the order they occur
             $session_start_TS = strtotime($data['session_start']);
             $duration = $data['duration_minutes'] * 60;
             $session_end_TS = strtotime($data['session_start']) + $duration;

             if ($session_start_TS > $time_out_TS)
             {
                 return $retVal;
             }

             if ($session_end_TS <= $time_in_TS)
             {
                 continue;
             }

             if ($session_start_TS < $time_in_TS &&  $session_end_TS > $time_in_TS && $session_end_TS < $time_out_TS)
             {
                 // session begins before shift begins but ends after shift begins and before it ends
                 $retVal[$session_id] = array('id' => $session_id, 'relation' => "ends_in_shift", 'session_start_TS' => $session_start_TS, 'session_end_TS' => $session_end_TS, 'duration' => $duration);
             }
             else if ($session_start_TS >= $time_in_TS && $session_start_TS < $time_out_TS && $session_end_TS <= $time_out_TS)
             {
                // sessions begins after shift begins but also begins before shift ends and ends before shift ends
                 $retVal[$session_id] =  array('id' => $session_id, 'relation' => "within_shift", 'session_start_TS' => $session_start_TS, 'session_end_TS' => $session_end_TS, 'duration' => $duration);
             }
             else if ($session_start_TS > $time_in_TS && $session_start_TS < $time_out_TS && $session_end_TS > $time_out_TS)
             {
                 // sessions begins after shift begins but also begins before shift ends and ends before shift ends
                 $retVal[$session_id] =  array('id' => $session_id, 'relation' => "begins_in_shift", 'session_start_TS' => $session_start_TS, 'session_end_TS' => $session_end_TS, 'duration' => $duration);
             }
             else if ($session_start_TS < $time_in_TS && $session_end_TS > $time_out_TS)
             {
                 // session begins before shift AND ends after shift ends (rare)
                 $retVal[$session_id] =  array('id' => $session_id, 'relation' => "contained", 'session_start_TS' => $session_start_TS, 'session_end_TS' => $session_end_TS, 'duration' => $duration);
             }

             if ($lastSessionEndTime)
             {
                 $Gap = $session_start_TS - $lastSessionEndTime;
                 CLog::Assert($Gap >= 0, "The session $session_id starts before the prior session ended.");

                 if (isset($retVal[$session_id]))
                 {
                     $retVal[$session_id]['gap'] = $Gap;
                 }
             }

             $lastSessionEndTime = $session_end_TS;
         }
         return $retVal;
     }


     function assignHours($date, $thisDude,  $jobType, $data)
     {
        if ($date == '2019-10-23' && $thisDude == 1085)
        {
            $x = 1;
        }

         $jobField = 'timeThisSession';
         if ($jobType == "907 - Tastes/Private Events") {
             $jobField = 'timeThisEvent';
         } else if ($jobType == "922 - Session Lead") {
             $jobField = 'sessionLeadTime';
         }

         $time_in_TS = strtotime($date . " " . $data['time_in']);
         $time_out_TS = strtotime($date . " " . $data['time_out']);

         $secondsInShift = $time_out_TS - $time_in_TS;

         $allSessionForBlock = $this->retrieveSessions($date, $time_in_TS, $time_out_TS);

         if (empty($allSessionForBlock))
         {
             $catchAllSession = $this->findClosestSession($date, $time_in_TS, $time_out_TS);

             if ($catchAllSession)
             {
                 $catchAllSession[$jobField] = $time_out_TS - $time_in_TS;
                 return array($catchAllSession['id'] => $catchAllSession);
             }
             else
             {
                 if (!isset($this->masterArray[$date]))
                 {
                     $this->masterArray[$date] = array();
                 }

                 uksort($this->masterArray, 'sort_master_array_by_date');

                 if (!isset($this->masterArray[$date]['catch_all']))
                   {
                       $catchAll = array('id' => 'catch_all', 'relation' => "no_session", 'session_start_TS' => 0,
                           'session_end_TS' => 0, 'duration' => 0);
                       $this->masterArray[$date]['catch_all'] = $catchAll;


                   }

                   $catchAll[$jobField] += ($time_out_TS - $time_in_TS);
                   return array('catch_all' => $catchAll);
             }
         }

        $isFirst = true;
        $lastSession = false;
        $sessionCounter = 0;
        $numSessions = count($allSessionForBlock);

        foreach($allSessionForBlock as $thisSessionID => &$sessionData)
        {
            $timeThisSession = 0;
            $sessionCounter++;
            if ($numSessions == 1)
            {
                $timeThisSession += $time_out_TS - $time_in_TS;
                $sessionData[$jobField] = $timeThisSession;
                return $allSessionForBlock;
            }
            else if ($isFirst)
            {
                $isFirst = false;
                if ($sessionData['relation'] == 'contained') {
                    $timeThisSession += $secondsInShift;
                } else if ($sessionData['relation'] == 'ends_in_shift') {
                    $timeThisSession += ($sessionData['session_end_TS'] - $time_in_TS);
                } else if ($sessionData['relation'] == 'begins_in_shift') {
                    $timeThisSession += $secondsInShift;
                } else if ($sessionData['relation'] == 'within_shift') {
                    $timeThisSession += $sessionData['session_end_TS'] - $time_in_TS;
                }

                $sessionData[$jobField] = $timeThisSession;
            }
            else if ($sessionCounter == $numSessions)
            {
                //last session
                if ($sessionData['relation'] == 'begins_in_shift') {
                    $prepTime = 0;
                    if ($sessionData['gap'])
                    {
                        $prepTime = $sessionData['gap'] / 2;
                        $allSessionForBlock[$lastSession['id']][$jobField] += $prepTime;
                    }
                    $timeThisSession += (($time_out_TS - $sessionData['session_start_TS']) + $prepTime);
                }
                else if ($sessionData['relation'] == 'within_shift')
                {
                    $prepTime = 0;
                    if ($sessionData['gap'])
                    {
                        $prepTime = $sessionData['gap'] / 2;
                        $allSessionForBlock[$lastSession['id']][$jobField] += $prepTime;
                    }
                    $timeThisSession += (($time_out_TS - $sessionData['session_start_TS']) + $prepTime);
                    $timeThisSession += (($time_out_TS - $sessionData['session_start_TS']) + $prepTime);
                }
                $sessionData[$jobField] = $timeThisSession;
            }
            else
            {
                if ($sessionData['relation'] == 'contained') {
                    throw new Exception("Should not have a second containing session: $thisSessionID");
                } else if ($sessionData['relation'] == 'ends_in_shift') {
                    throw new Exception("Should not have a second ends_in_shift session: $thisSessionID");
                } else if ($sessionData['relation'] == 'begins_in_shift') {
                    throw new Exception("begins_in_shift session should be last: $thisSessionID");
                }
                else if ($sessionData['relation'] == 'within_shift')
                {
                    $prepTime = 0;
                    if ($sessionData['gap'])
                    {
                        $prepTime = $sessionData['gap'] / 2;
                        $allSessionForBlock[$lastSession['id']][$jobField] += $prepTime;
                    }
                    $timeThisSession += ($sessionData['duration'] + $prepTime);
                }
                $sessionData[$jobField] = $timeThisSession;
            }

            $lastSession = $sessionData;
        }

        return $allSessionForBlock;
     }


     function retrieveRange($range_day_start, $range_day_end)
    {
        $rows = array();

        $labels = array("Labor Category");
        $columnDescs = array();
        $columnDescs['A'] = array(
            'align' => 'left',
            'width' => 25,
            'decor' => 'majortotal'
        );
        $thirdSecondChar = "";
        $colSecondChar = "";
        $col = 'B';
        $lastColumn = "";

        $storeArr = array();
        $storesInPlay = new DAO();
        $storesInPlay->query("select distinct department from employee_hours where shift_date >= '$range_day_start' and shift_date <= '$range_day_end' order by department");
        while($storesInPlay->fetch())
        {
            $storeArr[$storesInPlay->department] = 0;
            $labels[] = $storesInPlay->department;
            $columnDescs[$colSecondChar . $col] = array(
                'align' => 'left',
                'width' => 15
            );
            $lastColumn = $thirdSecondChar .$colSecondChar . $col;
            incrementColumn($thirdSecondChar, $colSecondChar, $col);
        }

        $laborCatArr = array();
        $CatsInPlay = new DAO();
        $CatsInPlay->query("select distinct jobs from employee_hours where shift_date >= '$range_day_start' and shift_date <= '$range_day_end' order by jobs");
        while($CatsInPlay->fetch())
        {
            $laborCatArr[] = $CatsInPlay->jobs;
        }


        foreach($laborCatArr as $thisCat)
        {
            $rows[$thisCat] = array_merge(array($thisCat), $storeArr);
        }

        { // add totals row
            $rows['total'] = array("Grand Total");
            $thirdSecondCharTotals = "";
            $colSecondCharTotals = "";
            $colTotals = 'B';

            foreach ($storeArr as $thisStore)
            {
                $rows['total'][] = "=SUM(" .$thirdSecondCharTotals . $colSecondCharTotals . $colTotals . "4:" . $thirdSecondCharTotals . $colSecondCharTotals . $colTotals . (3 + count($laborCatArr)) . ")";
                incrementColumn($thirdSecondCharTotals, $colSecondCharTotals, $colTotals);

            }
        }

        $dao = new DAO();
        $dao->query("select * from employee_hours where shift_date >= '$range_day_start' and shift_date <= '$range_day_end'");


        while($dao->fetch())
        {
              $rows[$dao->jobs][$dao->department] += ($dao->reg_hours + $dao->OT1_hours + $dao->OT2_hours + $dao->unpaid_hours);
        }

        $labels[] = "GrandTotal";
        $columnDescs[$colSecondChar . $col] = array(
            'align' => 'left',
            'width' => 15,
            'decor' => 'majortotal'
        );

        $pos = 4;

        foreach($rows as &$thisRow)
        {
            $thisRow[] = "=SUM(B$pos:$lastColumn$pos)";
            $pos++;
        }


        return array($rows,$labels,$columnDescs);

    }

     function retrieveRangeComparison($range_day_start, $range_day_end, $Form)
     {
         $rows = array();

         $labels = array("Labor Category");
         $columnDescs = array();
         $columnDescs['A'] = array(
             'align' => 'left',
             'width' => 25,
             'decor' => 'majortotal'
         );
         $thirdSecondChar = "";
         $colSecondChar = "";
         $col = 'B';
         $lastColumn = "";

         // ----get stores in period and store metrics
         $storeMetrics = array();
         $storeArr = array();
         $storesInPlay = new DAO();
         $storesInPlay->query("select distinct department from employee_hours where shift_date >= '$range_day_start' and shift_date <= '$range_day_end' order by department");

         if ($storesInPlay->N == 0)
         {
             return array(false, false, false);
         }


         while($storesInPlay->fetch())
         {
             $storeArr[$storesInPlay->department] = 0;
             $labels[] = $storesInPlay->department;
             $columnDescs[$colSecondChar . $col] = array(
                 'align' => 'left',
                 'width' => 15
             );
             $lastColumn = $thirdSecondChar .$colSecondChar . $col;
             incrementColumn($thirdSecondChar, $colSecondChar, $col);

             $storeMetrics[$storesInPlay->department] = $this->getStoreMetrics($storesInPlay->department, $range_day_start, $range_day_end);

         }

         $numStores = count($storesInPlay);

         // get array of job codes in period
         $laborCatArr = array();
         $CatsInPlay = new DAO();
         $CatsInPlay->query("select distinct jobs from employee_hours where shift_date >= '$range_day_start' and shift_date <= '$range_day_end' order by jobs");
         while($CatsInPlay->fetch())
         {
             $laborCatArr[] = $CatsInPlay->jobs;
         }


         // set up defaults for hours tabble
         foreach($laborCatArr as $thisCat)
         {
             $rows[$thisCat] = array_merge(array($thisCat), $storeArr);
         }

      // add totals row
         $rows['total'] = array("Grand Total");
         $thirdSecondCharTotals = "";
         $colSecondCharTotals = "";
         $colTotals = 'B';

         foreach ($storeArr as $thisStore)
         {
             $rows['total'][] = "=SUM(" .$thirdSecondCharTotals . $colSecondCharTotals . $colTotals . "4:" . $thirdSecondCharTotals . $colSecondCharTotals . $colTotals . (3 + count($laborCatArr)) . ")";
             incrementColumn($thirdSecondCharTotals, $colSecondCharTotals, $colTotals);

         }


         // populate rows with hours data
         $dao = new DAO();
         $dao->query("select * from employee_hours where shift_date >= '$range_day_start' and shift_date <= '$range_day_end'");

         while($dao->fetch())
         {
             $rows[$dao->jobs][$dao->department] += ($dao->reg_hours + $dao->OT1_hours + $dao->OT2_hours + $dao->unpaid_hours);
         }


        // add the totals column at far right
         $labels[] = "GrandTotal";
         $columnDescs[$colSecondChar . $col] = array(
             'align' => 'left',
             'width' => 15,
             'decor' => 'majortotal'
         );
         $pos = 4;

         self::$rowsMap['first_hour_table_row'] = 4;

         foreach($rows as &$thisRow)
         {
             $thisRow[] = "=SUM(B$pos:$lastColumn$pos)";
             $pos++;
         }

         self::$rowsMap['last_hour_table_row'] = $pos - 1;

         // $pos now points to next row
         // ---------------------------------------layout the store metrics
        // blankline
         $rows[] = array_fill(0, $numStores + 2, "");
         $rows[] = array_pad(array("Store Metrics"), $numStores + 2, "" );

         $rows['visits'] = array("Guest Visits");
         $rows['servings'] = array("Total Servings");
         $rows['sides_rev'] = array("Sides Revenue");

         self::$rowsMap['first_metrics_table_row'] = $pos + 1;

         foreach($storeMetrics as $thisStore => $metrics)
         {
             $rows['visits'][] = $metrics[0];
             $rows['servings'][] = $metrics[1];
             $rows['sides_rev'][] = $metrics[2] . "|=>currency";
         }

         $tempRowNum = $pos + 2;
         $rows['visits'][] ="=SUM(B$tempRowNum:$lastColumn$tempRowNum)";
         $tempRowNum++;
         $rows['servings'][] = "=SUM(B$tempRowNum:$lastColumn$tempRowNum)";
         $tempRowNum++;
         $rows['sides_rev'][] ="=SUM(B$tempRowNum:$lastColumn$tempRowNum)|=>currency";


         $pos += 4;
         $metricPos = $pos - 2;
         self::$rowsMap['last_metrics_table_row'] = $pos;

         // ----------------------------------------------------- hours per visit
         $pos += 2;
         self::$rowsMap['first_visits_table_row'] = $pos;

         $rows[] = array_fill(0, $numStores + 2, "");
         $rows[] = array_pad(array("Hours per Guest Visit"), $numStores + 2, "" );

         $rowNum = 4;
         $rowLabel = 100;
         foreach($laborCatArr as $thisCode)
         {
             $rows[$rowLabel] = array($thisCode);

             $thisCol = 'B';
             $lastCol = '';
             foreach ($storeArr as $thisStore)
             {
                 $lastCol = $thisCol;
                 $rows[$rowLabel][] = "=$thisCol$rowNum / $thisCol$metricPos";
                 $thisCol++;
             }
             $pos++;
             $rows[$rowLabel][] = "=AVERAGE(B$pos:$lastCol$pos)";
             $rowNum++;
             $rowLabel++;
         }

         // totals Row
         $lastRow = $pos;
         $firstRow = self::$rowsMap['first_visits_table_row'] + 1;
         $pos++;
         $thisCol = 'B';
         $lastCol = '';
         $rows[$rowLabel][] = "Total";
         foreach ($storeArr as $thisStore)
         {
             $lastCol = $thisCol;
             $rows[$rowLabel][] = "=SUM($thisCol$firstRow:$thisCol$lastRow)";
             $thisCol++;
         }
         $lastRow++;
         $rows[$rowLabel][] = "=AVERAGE(B$lastRow:$lastCol$lastRow)";

         self::$rowsMap['last_visits_table_row'] = $pos;


         // ----------------------------------------------------servings per visit
         $pos += 2;
         self::$rowsMap['first_servings_table_row'] = $pos;


         $rows[] = array_fill(0, $numStores + 2, "");
         $rows[] = array_pad(array("Hours per Serving"), $numStores + 2, "" );

         $rowNum = 4;
         $rowLabel = 200;
         $metricPos++;
         foreach($laborCatArr as $thisCode)
         {
             $rows[$rowLabel] = array($thisCode);

             $thisCol = 'B';
             $lastCol = '';

             foreach ($storeArr as $thisStore)
             {
                 $lastCol = $thisCol;
                 $rows[$rowLabel][] = "=$thisCol$rowNum / $thisCol$metricPos";
                 $thisCol++;
             }
             $pos++;
             $rows[$rowLabel][] = "=AVERAGE(B$pos:$lastCol$pos)";
             $rowNum++;
             $rowLabel++;

         }
         $lastRow = $pos;
         $firstRow = self::$rowsMap['first_servings_table_row'] + 1;
         $pos++;
         $thisCol = 'B';
         $lastCol = '';
         $rows[$rowLabel][] = "Total";
         foreach ($storeArr as $thisStore)
         {
             $lastCol = $thisCol;
             $rows[$rowLabel][] = "=SUM($thisCol$firstRow:$thisCol$lastRow)";
             $thisCol++;
         }
         $lastRow++;
         $rows[$rowLabel][] = "=AVERAGE(B$lastRow:$lastCol$lastRow)";


         self::$rowsMap['last_servings_table_row'] = $pos;


         // --------------------------------------- hours per revenue dollar

         $pos += 2;
         self::$rowsMap['first_sides_table_row'] = $pos;


         $rows[] = array_fill(0, $numStores + 2, "");
         $rows[] = array_pad(array("Sides and Sweets Revenue per hour"), $numStores + 2, "" );

         $rowNum = 4;
         $rowLabel = 300;
         $metricPos++;
         foreach($laborCatArr as $thisCode)
         {
             $rows[$rowLabel] = array($thisCode);

             $thisCol = 'B';
             $lastCol = '';

             foreach ($storeArr as $thisStore)
             {
                 $lastCol = $thisCol;
                 $rows[$rowLabel][] = "=$thisCol$metricPos / $thisCol$rowNum";
                 $thisCol++;
             }
             self::$rowsMap['last_store_column'] = $thisCol--;

             $pos++;
             $rows[$rowLabel][] = "=AVERAGE(B$pos:$lastCol$pos)";
             $rowNum++;
             $rowLabel++;

         }

         $lastRow = $pos;
         $firstRow = self::$rowsMap['first_servings_table_row'] + 1;
         $pos++;
         $thisCol = 'B';
         $lastCol = '';
         $rows[$rowLabel][] = "Total";
         foreach ($storeArr as $thisStore)
         {
             $lastCol = $thisCol;
             $rows[$rowLabel][] = "=SUM($thisCol$firstRow:$thisCol$lastRow)";
             $thisCol++;
         }
         $lastRow++;
         $rows[$rowLabel][] = "=AVERAGE(B$lastRow:$lastCol$lastRow)";

         self::$rowsMap['last_sides_table_row'] = $pos;

         return array($rows,$labels,$columnDescs);

     }

     function getStoreMetrics($storeName, $range_day_start, $range_day_end)
     {
         $lookup = new DAO();
         $lookup->query("select store_id from employee_hours where department = '$storeName'");
         $lookup->fetch();

         $storeID = $lookup->store_id;

         // guest visits
         $visits = new DAO();
         $visits->query("select count(distinct b.order_id) as visit_count from booking b
                                join session s on s.id = b.session_id and s.store_id = $storeID and s.session_start > '$range_day_start' and s.session_start < '$range_day_end'
                                where b.status = 'ACTIVE' and b.is_deleted = 0");
         $visits->fetch();

         // servings
         $servings = new DAO();
         $servings->query("select sum(o.servings_total_count) as serving_count from booking b
                                    join session s on s.id = b.session_id and s.store_id = $storeID and s.session_start > '$range_day_start' and s.session_start < '$range_day_end'
                                    join orders o on o.id = b.order_id
                                    where b.status = 'ACTIVE' and b.is_deleted = 0");
         $servings->fetch();

         // Sides revenue
         $sides = new DAO();
         $sides->query("select sum(oi.sub_total) as sides_revenue from booking b
                                join session s on s.id = b.session_id and s.store_id = $storeID and s.session_start > '$range_day_start' and s.session_start < '$range_day_end'
                                join orders o on o.id = b.order_id
                                join order_item oi on oi.order_id = o.id and oi.is_deleted = 0
                                join menu_item mi on mi.id = oi.menu_item_id and mi.menu_item_category_id = 9
                                where b.status = 'ACTIVE' and b.is_deleted = 0");
         $sides->fetch();

         return array($visits->visit_count, $servings->serving_count, $sides->sides_revenue);

     }

     static function lookupStoreID($HOID)
    {
        $dao = new DAO();
        $dao->query("select id from store where home_office_id = '$HOID' and is_deleted = 0");
        $dao->fetch();
        return $dao->id;
    }


    static function lookupUserID($firstName, $lastName)
    {
        $dao = new DAO();
        $dao->query("select id from user where firstname = '$firstName' and lastname = '$lastName' and user_type <> 'CUSTOMER' and is_deleted = 0");
        $dao->fetch();
        return $dao->id;
    }


}
?>