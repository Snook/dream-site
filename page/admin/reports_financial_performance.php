<?php

/**
 * @author Carl Samuelson
 */

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/CDreamReport.inc');
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');
require_once('page/admin/reports_goal_management.php');
require_once('page/admin/reports_p_and_l_input.php');

global $gUse_percentage_of_agr;
global $gExpensesArray;

function finPerfReportRowsCallback($sheet, &$data, $row, $bottomRightExtent)
{

	global $gUse_percentage_of_agr;
	global $gExpensesArray;

	if ($data[0] == '- Adjustments & Discounts' || $data[0] == 'AGR' || $data[0] == '   All Other Expenses' || $data[0] == '- Adjustments and Discounts' || $data[0] == 'Adjusted Gross Revenue')
	{
		$styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
		$sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
	}
	else if ($data[0] == 'Net Income')
	{
		$styleArray = array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THICK)));
		$sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
	}
	/*
	$numDateCols = count($data) - 1;
	if ($gUse_percentage_of_agr && in_array(trim($data[0]), $gExpensesArray))
	{
		for($x = 1; $x < $numDateCols; $x++)
		{
			//$sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
			$data[$x] = "og";
		 }

	}
	*/
}

function finPerfReportCellCallback($sheet, $colName, $datum, $col, $row)
{
	global $gUse_percentage_of_agr;

	if ($gUse_percentage_of_agr)
	{
		if ($col != 'A' && $row > 5 && $row < 24)
		{
			if ($row >= 10 && $row <= 12)
			{
				$sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			}
			else
			{
				$sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_0);
			}
		}
	}
	else
	{
		if ($row >= 10 && $row <= 15)
		{
			$sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
		}
	}
}

function finPerfReportComparisonCellCallback($sheet, $colName, $datum, $col, $row)
{
	global $gUse_percentage_of_agr;

	if ($gUse_percentage_of_agr)
	{
		if (($col == 'B' || $col == 'C' || $col == 'E' || $col == 'G' || $col == 'I') && $row > 5 && $row < 24)
		{
			if ($row < 10 || $row > 12)
			{
				if ($row == 7)
				{
					//  $sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
				}
				else
				{
					$sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_0);
				}
			}
			else
			{
				$sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			}
		}
	}
	else
	{
		if (($col == 'B' || $col == 'C' || $col == 'E' || $col == 'G' || $col == 'I') && $row > 5 && $row < 24)
		{
			if ($row >= 10 && $row <= 12)
			{
				$sheet->getStyle("$col$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
			}
		}
	}
}

class page_admin_reports_financial_performance extends CPageAdminOnly
{
	private $currentStore = null;
	private $show_store_selectors = false;
	private $use_percentage_of_agr = false;
	const LIMITED_P_AND_L_ACCESS_SECTION_ID = 8;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	static $rankingFields = array(
		array(
			'name' => 'cost_of_goods_and_services',
			'convert' => true
		),
		array(
			'name' => 'employee_wages',
			'convert' => true
		),
		array(
			'name' => 'employee_hours',
			'convert' => false
		),
		array(
			'name' => 'manager_hours',
			'convert' => false
		),
		array(
			'name' => 'bank_card_merchant_fees',
			'convert' => true
		),
		array(
			'name' => 'kitchen_and_office_supplies',
			'convert' => true
		),
		array(
			'name' => 'total_marketing_and_advertising_expense',
			'convert' => true
		),
		array(
			'name' => 'rent_expense',
			'convert' => true
		),
		array(
			'name' => 'repairs_and_maintenance',
			'convert' => true
		),
		array(
			'name' => 'utilities',
			'convert' => true
		),
		array(
			'name' => 'other_expenses',
			'convert' => true
		),
		array(
			'name' => 'net_income',
			'convert' => false
		)
	);

	static $convertFields = array(
		'cost_of_goods_and_services',
		'owner_salaries',
		'manager_salaries',
		'employee_wages',
		'bank_card_merchant_fees',
		'kitchen_and_office_supplies',
		'national_marketing_fee',
		'salesforce_fee',
		'royalty_fee',
		'total_marketing_and_advertising_expense',
		'rent_expense',
		'repairs_and_maintenance',
		'utilities',
		'other_expenses',
		'monthly_debt_service',
		'payroll_taxes'
	);

	static $hoursFields = array(
		'employee_hours',
		'manager_hours',
		'owner_hours'
	);

	function runHomeOfficeManager()
	{
		$this->show_store_selectors = true;
		$this->runFinPerfReport();
	}

	function runSiteAdmin()
	{
		$this->show_store_selectors = true;
		$this->runFinPerfReport();
	}

	function runFranchiseOwner()
	{
		$this->show_store_selectors = false;
		$this->currentStore = CApp::forceLocationChoice();

		$this->runFinPerfReport();
	}

	function runFranchiseManager()
	{
		$this->show_store_selectors = false;

		$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);

		if ($hasPandLAccess)
		{
			$this->currentStore = CApp::forceLocationChoice();
			$this->runFinPerfReport();
		}
		else
		{
			CApp::bounce('/backoffice/access-error?pagename=Financial Performance Report');
		}
	}

	function runOpsLead()
	{
		$this->show_store_selectors = false;

		$hasPandLAccess = CApp::directAccessControlTest(self::LIMITED_P_AND_L_ACCESS_SECTION_ID, CUser::getCurrentUser()->id);

		if ($hasPandLAccess)
		{
			$this->currentStore = CApp::forceLocationChoice();
			$this->runFinPerfReport();
		}
		else
		{
			CApp::bounce('/backoffice/access-error?pagename=Financial Performance Report');
		}
	}

	function runFinPerfReport()
	{

		global $gUse_percentage_of_agr;
		global $gExpensesArray;

		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		$tpl->assign('show_store_selectors', $this->show_store_selectors);

		if ($this->currentStore)
		{ //fadmins
			$store = $this->currentStore;
		}
		else
		{
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => false,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));
			$store = $Form->value('store');
		}

		$month = 0;
		$year = 0;

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::value => 'Run Report'
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

		$non_expense_arr = array(
			"gross_sales",
			"mark_up",
			"adjustments_and_discounts",
			"adjusted_gross_revenue",
			"net_income",
			"discounts_total",
			"agr_total"
		);

		$hours_field = array(
			"employee_hours",
			"owner_hours",
			"manager_hours"
		);

		$year = date("Y");

		// Date Selection Type
		$Form->DefaultValues['date_type'] = 'single_month';

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "date_type",
			CForm::required => true,
			CForm::value => 'single_month'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "date_type",
			CForm::required => true,
			CForm::value => 'month_range'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_single_month",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::name => 'month_single_month'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_from_month",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::name => 'month_from_month'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "year_to_month",
			CForm::required => true,
			CForm::default_value => $year,
			CForm::length => 6
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::name => 'month_to_month'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'export',
			CForm::value => 'none'
		));

		$tpl->assign('query_form', $Form->render());

		$export = false;

		if (isset($_POST['export']) && $_POST['export'] == 'xlsx')
		{
			$export = true;
		}

		if (isset($_POST['use_percent_of_agr']))
		{
			$this->use_percentage_of_agr = true;
		}
		else
		{
			$this->use_percentage_of_agr = false;
		}

		$gUse_percentage_of_agr = $this->use_percentage_of_agr;

		if ($Form->value('report_submit') || $export)
		{
			$hadLessThan3Error = false;

			$labels = array(
				"COGS (Food + Pkg - Discounts)",
				"Employee Wages",
				"Manager Salaries & Bonuses",
				"Owner Salaries",
				"Employee Hours",
				"Manager Hours",
				"Owner Hours",
				"Payroll Taxes",
				"Bank Card Merchant Fees",
				"Kitchen and Office Supplies",
				"National Marketing Fee",
				"Technology Fee",
				"Royalty Fee",
				"Local Marketing and Advertising",
				"Rent Expense",
				"Repairs and Maintenance",
				"Utilities",
				"All Other Expenses",
				"Net Income",
				"Loan Payments (Principal Only)"
			);

			$gExpensesArray = array(
				"COGS (Food + Pkg - Discounts)",
				"Employee Wages",
				"Manager Salaries & Bonuses",
				"Owner Salaries",
				"Employee Hours",
				"Manager Hours",
				"Owner Hours",
				"Payroll Taxes",
				"Bank Card Merchant Fees",
				"Kitchen and Office Supplies",
				"National Marketing Fee",
				"Technology Fee",
				"Royalty Fee",
				"Local Marketing and Advertising",
				"Rent Expense",
				"Repairs and Maintenance",
				"Utilities",
				"All Other Expenses",
				"Loan Payments (Principal Only)"
			);

			if ($Form->value('date_type') == 'single_month')
			{
				$curMonth = $Form->value('month_single_month');
				$curMonth++;
				if (empty($curMonth) || !is_numeric($curMonth))
				{
					throw new Exception("The month is invalid");
				}

				$curYear = $Form->value('year_single_month');

				if (empty($curYear) || !is_numeric($curYear) || $curYear < 2004 || $curYear > 3000)
				{
					throw new Exception("The year is invalid");
				}

				$storeInfo = page_admin_reports_p_and_l_input::getStoreInfo($store, $curMonth, $curYear);

				$rows = array(
					"gross_sales" => array(
						"Gross Sales",
						$storeInfo['gross_sales']
					),
					"mark_up" => array(
						"+ Mark Up",
						$storeInfo['mark_up']
					),
					"discounts_total" => array(
						"- Adjustments & Discounts",
						$storeInfo['adjustments_and_discounts']
					),
					"agr_total" => array(
						"Adjusted Gross Revenue",
						$storeInfo['adjusted_gross_revenue']
					)
				);

				$selectMonth = date("Y-m-d", mktime(0, 0, 0, $curMonth, 1, $curYear));
				$rows = $this->retrieveStoreData($store, $Form, $selectMonth, $labels, $rows);

				if (!isset($rows['national_marketing_fee']))
				{
					$rows['national_marketing_fee'] = array(
						"National Marketing Fee",
						$storeInfo['marketing_total']
					);
				}
				else
				{
					$rows['national_marketing_fee'][1] = $storeInfo['marketing_total'];
				}

				$salesForceFee = 0;
				if (($curYear == 2018 && $curMonth >= 9) || $curYear > 2018)
				{
					$salesForceFee = 250;
				}

				if (!isset($rows['salesforce_fee']))
				{
					$rows['salesforce_fee'] = array(
						"Technology Fee",
						$salesForceFee
					);
				}
				else
				{
					$rows['salesforce_fee'][1] = $salesForceFee;
				}

				if (!isset($rows['royalty_fee']))
				{
					$rows['royalty_fee'] = array(
						"Royalty Fee",
						$storeInfo['royalty']
					);
				}
				else
				{
					$rows['royalty_fee'][1] = $storeInfo['royalty'];
				}

				$rows['royalty_fee'][1] = $storeInfo['royalty'];

				if ($this->use_percentage_of_agr)
				{
					foreach ($rows as $name => &$dater)
					{
						if (in_array($name, self::$convertFields))
						{
							$dater[1] = $dater[1] / $rows['agr_total'][1];
						}
					}
				}

				if (isset($_POST['show_comparisons']))
				{
					$hadLessThan3Error = $this->retrieveComparisonData($store, $rows, $selectMonth, $curMonth, $curYear, $storeInfo["trade_area_id"], $storeInfo["store_class"], $storeInfo['opco_id']);
				}

				foreach ($rows as $name => &$data)
				{
					if (!in_array($name, $non_expense_arr))
					{
						$data[0] = "   " . $data[0];
					}
				}

				$col = 'A';
				$colSecondChar = '';
				$thirdSecondChar = '';

				$columnDescs[$colSecondChar . $col] = array(
					'align' => 'left',
					'width' => 36,
					'type' => 'text'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar . $col] = array(
					'align' => 'right',
					'width' => 12,
					'type' => 'currency_no_cents'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				$monthLabeling = date("M-Y", strtotime($selectMonth));

				if (isset($_POST['show_comparisons']))
				{

					$headerNames = array(
						"Financial Performance Report",
						$monthLabeling,
						"Nat'l Avg",
						"Nat'l Rank",
						"Region Avg",
						"Region Rank",
						"Revenue Tier Avg",
						"Revenue Tier Rank"
					);

					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'right',
						'width' => 12,
						'type' => 'currency_no_cents'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => 12,
						'type' => 'text'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'right',
						'width' => 12,
						'type' => 'currency_no_cents'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => 12,
						'type' => 'text'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'right',
						'width' => 12,
						'type' => 'currency_no_cents'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => 12,
						'type' => 'text'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER)
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'right',
							'width' => 12,
							'type' => 'currency_no_cents'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'center',
							'width' => 12,
							'type' => 'text'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);

						$headerNames = array_merge($headerNames, array(
							"Sysco Opco Avg",
							"Sysco Opco Rank"
						));
					}
				}
				else
				{
					$headerNames = array(
						"Financial Performance Report",
						$monthLabeling
					);
				}

				$numRows = count($rows);
				$callbacks = array(
					'row_callback' => 'finPerfReportRowsCallback',
					'cell_callback' => 'finPerfReportComparisonCellCallback'
				);

				if ($export)
				{
					$tpl->assign('labels', $headerNames);
					$tpl->assign('rows', $rows);
					$tpl->assign('rowcount', $numRows);
					$tpl->assign('col_descriptions', $columnDescs);
					$tpl->assign('excel_callbacks', $callbacks);
					CLog::RecordReport("Financial Performance Report (Export)", "Store: $store ~ Type: Month");

					$_GET['export'] = 'xlsx';
				}
				else
				{
					PHPExcel_Shared_String::setThousandsSeparator(",");

					list($css, $html) = writeExcelFile("Test", $headerNames, $rows, true, false, $columnDescs, false, $callbacks, false, true);

					echo "<html><head>";
					echo $css;
					echo "</head><body>";

					if ($hadLessThan3Error)
					{
						echo "<div style='color:red;'>One or more comparison categories had only 2 stores so that category will not be shown.</div>";
					}

					echo $html;
					echo "</body></html>";

					CLog::RecordReport("Financial Performance Report", "Store: $store ~ Type: Month");

					exit;
				}
			}
			else // month range
			{


				$labels = array(
					"gross_sales" => "Gross Sales",
					"mark_up" => "+ Mark Up",
					"adjustments_and_discounts" => "- Adjustments & Discounts",
					"adjusted_gross_revenue" => "Adjusted Gross Revenue",
					"cost_of_goods_and_services" => "COGS (Food + Pkg - Discounts)",
					"employee_wages" => "Employee Wages",
					"manager_salaries" => "Manager Salaries & Bonuses",
					"owner_salaries" => "Owner Salaries",
					"employee_hours" => "Employee Hours",
					"manager_hours" => "Manager Hours",
					"owner_hours" => "Owner Hours",
					"payroll_taxes" => "Payroll Taxes",
					"bank_card_merchant_fees" => "Bank Card Merchant Fees",
					"kitchen_and_office_supplies" => "Kitchen and Office Supplies",
					"national_marketing_fee" => "National Marketing Fee",
					"salesforce_fee" => "Technology Fee",
					"royalty_fee" => "Royalty Fee",
					"total_marketing_and_advertising_expense" => "Local Marketing and Advertising",
					"rent_expense" => "Rent Expense",
					"repairs_and_maintenance" => "Repairs and Maintenance",
					"utilities" => "Utilities",
					"other_expenses" => "All Other Expenses",
					"net_income" => "Net Income",
					"monthly_debt_service" => "Loan Payments (Principal Only)"
				);

				$fromMonth = $Form->value('month_from_month');
				$fromMonth++;
				if (empty($fromMonth) || !is_numeric($fromMonth))
				{
					throw new Exception("The from month is invalid");
				}

				$fromYear = $Form->value('year_from_month');
				if (empty($fromYear) || !is_numeric($fromYear) || $fromYear < 2004 || $fromYear > 3000)
				{
					throw new Exception("The from year is invalid");
				}

				$toMonth = $Form->value('month_to_month');
				$toMonth++;
				if (empty($toMonth) || !is_numeric($toMonth))
				{
					throw new Exception("The to month is invalid");
				}

				$toYear = $Form->value('year_to_month');
				if (empty($toYear) || !is_numeric($toYear) || $toYear < 2004 || $toYear > 3000)
				{
					throw new Exception("The to year is invalid");
				}

				$fromDate = date("Y-m-d", mktime(0, 0, 0, $fromMonth, 1, $fromYear));
				$toDate = date("Y-m-d", mktime(0, 0, 0, $toMonth, 1, $toYear));

				$rows = $this->retrieveStoreDataRange($store, $Form, $fromDate, $toDate, $labels);

				$numDateCols = count($rows[0]);

				foreach ($rows as $name => &$data)
				{
					if (!in_array($name, $non_expense_arr))
					{
						$data[0] = "   " . $data[0];

						if ($this->use_percentage_of_agr && !in_array($name, $hours_field))
						{
							for ($x = 1; $x < $numDateCols; $x++)
							{
								$agr = $rows['adjusted_gross_revenue'][$x];
								$thisVal = $data[$x];
								$data[$x] = $thisVal / $agr;
							}
						}
					}
				}

				if ($rows)
				{

					$headerNames = array_shift($rows);

					$col = 'A';
					$colSecondChar = '';
					$thirdSecondChar = '';

					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'left',
						'width' => 50,
						'type' => 'text'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					$numDataCols = count($headerNames) - 1;
					for ($x = 0; $x < $numDataCols; $x++)
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'right',
							'width' => 15,
							'type' => 'currency_no_cents',
							'decor' => 'bottom_border'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					$callbacks = array(
						'row_callback' => 'finPerfReportRowsCallback',
						"cell_callback" => 'finPerfReportCellCallback'
					);

					$numRows = count($rows);

					if ($export)
					{
						$tpl->assign('excel_callbacks', $callbacks);
						$tpl->assign('labels', $headerNames);
						$tpl->assign('rows', $rows);
						$tpl->assign('rowcount', $numRows);
						$tpl->assign('col_descriptions', $columnDescs);

						CLog::RecordReport("Financial Performance Report (Export)", "Store: $store ~ Type: Month Range");

						$_GET['export'] = 'xlsx';
					}
					else
					{

						PHPExcel_Shared_String::setThousandsSeparator(",");

						list($css, $html) = writeExcelFile("Test", $headerNames, $rows, true, false, $columnDescs, false, $callbacks, false, true);

						echo "<html><head>";
						echo $css;
						echo "</head><body>";

						echo $html;
						echo "</body></html>";

						CLog::RecordReport("Financial Performance Report", "Store: $store ~ Type: Month Range");

						exit;
					}
				}
			}
		}
	}

	function retrieveStoreDataRange($store_id, $Form, $fromDate, $toDate, $labels)
	{

		$retVal = array();
		$tempRows = array();

		$headerRow = array("Financial Performance Report");

		$this->getOrderInfoByMonthRange($fromDate, $toDate, $tempRows, $store_id, $headerRow);

		$query = "select smpl.cost_of_goods_and_services,
	    smpl.employee_wages,
	    smpl.manager_salaries,
	    smpl.owner_salaries,
	    smpl.employee_hours,
	    smpl.manager_hours,
	    smpl.owner_hours,
	    smpl.payroll_taxes,
	    smpl.bank_card_merchant_fees,
	    smpl.kitchen_and_office_supplies,
	    1 as national_marketing_fee,
	    1 as royalty_fee,
	    smpl.total_marketing_and_advertising_expense,
	    smpl.rent_expense,
	    smpl.repairs_and_maintenance,
	    smpl.utilities,
	    smpl.other_expenses,
	    smpl.net_income,
	    smpl.monthly_debt_service,
	    smpl.date
	    from store_monthly_profit_and_loss smpl where smpl.store_id = $store_id and 
	           smpl.date >= '$fromDate' and smpl.date <= '$toDate' and smpl.is_deleted = 0 order by smpl.date";

		$queryObj = DAO_CFactory::create('store_monthly_profit_and_loss');
		$queryObj->query($query);

		while ($queryObj->fetch())
		{
			$date = $queryObj->date;

			$deiser = $queryObj->toArray();
			$deiser['national_marketing_fee'] = $tempRows[$date]['national_marketing_fee'];
			$deiser['royalty_fee'] = $tempRows[$date]['royalty_fee'];
			unset($tempRows[$date]['national_marketing_fee']);
			unset($tempRows[$date]['royalty_fee']);

			$tempRows[$date] = array_merge($tempRows[$date], array_slice($deiser, 0, 19, true));
		}

		foreach ($labels as $id => $thisField)
		{
			$retVal[$id] = array();
			$retVal[$id][] = $thisField;

			foreach ($tempRows as $date => $values)
			{
				$retVal[$id][] = $values[$id];
			}
		}

		array_unshift($retVal, $headerRow);

		return $retVal;
	}

	function retrieveStoreData($store_id, $Form, $selectMonth, $labels, $rows)
	{

		$query = "select smpl.cost_of_goods_and_services,
                            smpl.employee_wages,
                            smpl.manager_salaries,
                            smpl.owner_salaries,
                            smpl.employee_hours,
                            smpl.manager_hours,
                            smpl.owner_hours,
                            smpl.payroll_taxes,
                            smpl.bank_card_merchant_fees,
                            smpl.kitchen_and_office_supplies,
                            1 as national_marketing_fee,
                            0 as salesforce_fee,
                            1 as royalty_fee,
                            smpl.total_marketing_and_advertising_expense,
                            smpl.rent_expense,
                            smpl.repairs_and_maintenance,
                            smpl.utilities,
                            smpl.other_expenses,
                            smpl.net_income,
                            smpl.monthly_debt_service
                            
        from store_monthly_profit_and_loss smpl where smpl.store_id = $store_id and smpl.date = '$selectMonth' and smpl.is_deleted = 0";

		$queryObj = DAO_CFactory::create('store_monthly_profit_and_loss');
		$queryObj->query($query);

		if ($queryObj->fetch())
		{

			$dieser = $queryObj->toArray();
			foreach ($dieser as $dbName => $thisField)
			{
				$accountName = array_shift($labels);
				if (!$accountName)
				{
					break;
				}

				$rows[$dbName] = array(
					$accountName,
					$thisField
				);
			}
		}

		return $rows;
	}

	function retrieveComparisonData($store_id, &$rows, $selectMonth, $curMonth, $curYear, $store_trade_area, $store_class, $opco_id)
	{
		$retVal = false;

		$nonPLArr = array(
			"gross_sales",
			"mark_up",
			"discounts_total",
			"agr_total"
		);
		$percentageBasedArr = array(
			'cost_of_goods_and_services',
			'employee_wages',
			'bank_card_merchant_fees',
			'kitchen_and_office_supplies',
			'utilities',
			'other_expenses',
			'total_marketing_and_advertising_expense',
			'rent_expense',
			'repairs_and_maintenance'
		);
		$sensitiveFieldArr = array(
			"manager_salaries",
			"owner_salaries",
			"monthly_debt_service",
			"owner_hours",
			"payroll_taxes"
		);

		$data = array();

		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->findForMonthAndYear($curMonth, $curYear);
		$MenuObj->fetch();

		list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $MenuObj->menu_start);
		$start_date = strtotime($menu_start_date);
		$menu_year = date("Y", $start_date);
		$menu_month = date("n", $start_date);
		$menu_day = date("j", $start_date);
		$duration = $interval . " DAY";

		// National
		$ranks_and_averages = $this->getOrderInfoByMonthAllStores($menu_day, $menu_month, $menu_year, $duration, $data, $store_id);
		list($p_and_l_ranks, $p_and_l_averages) = $this->getPandLByMonthAllStores($curMonth, $curYear, $data, $store_id, false, false, false, true);

		if (!$ranks_and_averages || !$p_and_l_ranks)
		{
			$retVal = true;
		}

		foreach ($rows as $id => &$columns)
		{

			if (in_array($id, $nonPLArr))
			{
				$columns[] = $ranks_and_averages[$id . "_avg"];
				$columns[] = $ranks_and_averages[$id . "_rank"];
			}
			else
			{
				$suffix = "";
				if (in_array($id, $percentageBasedArr) && $this->use_percentage_of_agr)
				{
					$suffix = " %";
				}

				if (!in_array($id, $sensitiveFieldArr))
				{
					$val = $p_and_l_averages[$id . $suffix]['value'];

					if ($this->use_percentage_of_agr && !in_array($id, self::$hoursFields))
					{
						$val /= 100;
					}

					$columns[] = $val;
					$columns[] = $p_and_l_ranks[$id . $suffix];
				}
				else
				{
					$columns[] = "-";
					$columns[] = "-";
				}
			}
		}

		$data2 = array();

		// Regional
		$ranks_and_averages = $this->getOrderInfoByMonthAllStores($menu_day, $menu_month, $menu_year, $duration, $data2, $store_id, $store_trade_area);
		list($p_and_l_ranks, $p_and_l_averages) = $this->getPandLByMonthAllStores($curMonth, $curYear, $data2, $store_id, $store_trade_area, false, false, false);

		$regionalRanksAreHidden = false;
		if (!$ranks_and_averages || !$p_and_l_ranks)
		{
			$retVal = true;
			$regionalRanksAreHidden = true;
		}

		foreach ($rows as $id => &$columns)
		{

			if (in_array($id, $nonPLArr))
			{
				$columns[] = $ranks_and_averages[$id . "_avg"];
				$columns[] = $ranks_and_averages[$id . "_rank"];
			}
			else
			{
				$suffix = "";
				if (in_array($id, $percentageBasedArr) && $this->use_percentage_of_agr)
				{
					$suffix = " %";
				}

				if (!in_array($id, $sensitiveFieldArr))
				{
					if (!$regionalRanksAreHidden)
					{
						$val = $p_and_l_averages[$id . $suffix]['value'];
						if ($this->use_percentage_of_agr && !in_array($id, self::$hoursFields))
						{
							$val /= 100;
						}
						$columns[] = $val;
						$columns[] = $p_and_l_ranks[$id . $suffix];
					}
					else
					{
						$columns[] = "-";
						$columns[] = "-";
					}
				}
				else
				{
					$columns[] = "-";
					$columns[] = "-";
				}
			}
		}

		$data3 = array();

		// By Class
		$ranks_and_averages = $this->getOrderInfoByMonthAllStores($menu_day, $menu_month, $menu_year, $duration, $data3, $store_id, false, $store_class);
		list($p_and_l_ranks, $p_and_l_averages) = $this->getPandLByMonthAllStores($curMonth, $curYear, $data3, $store_id, false, $store_class, false, true);

		$classRanksAreHidden = false;
		if (!$ranks_and_averages || !$p_and_l_ranks)
		{
			$retVal = true;
			$classRanksAreHidden = true;
		}

		foreach ($rows as $id => &$columns)
		{

			if (in_array($id, $nonPLArr))
			{
				$columns[] = $ranks_and_averages[$id . "_avg"];
				$columns[] = $ranks_and_averages[$id . "_rank"];
			}
			else
			{
				$suffix = "";
				if (in_array($id, $percentageBasedArr) && $this->use_percentage_of_agr)
				{
					$suffix = " %";
				}

				if (!in_array($id, $sensitiveFieldArr))
				{
					if (!$classRanksAreHidden)
					{
						$val = $p_and_l_averages[$id . $suffix]['value'];
						if ($this->use_percentage_of_agr && !in_array($id, self::$hoursFields))
						{
							$val /= 100;
						}

						$columns[] = $val;
						$columns[] = $p_and_l_ranks[$id . $suffix];
					}
					else
					{
						$columns[] = "-";
						$columns[] = "-";
					}
				}
				else
				{
					$columns[] = "-";
					$columns[] = "-";
				}
			}
		}

		if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN || CUser::getCurrentUser()->user_type == CUser::HOME_OFFICE_MANAGER)
		{
			// OPCO

			$data4 = array();
			$ranks_and_averages = $this->getOrderInfoByMonthAllStores($menu_day, $menu_month, $menu_year, $duration, $data4, $store_id, false, false, $opco_id);
			list($p_and_l_ranks, $p_and_l_averages) = $this->getPandLByMonthAllStores($curMonth, $curYear, $data4, $store_id, false, false, $opco_id, true);

			// con always data since only home office can see opco comparison
			$retVal = false;

			foreach ($rows as $id => &$columns)
			{

				if (in_array($id, $nonPLArr))
				{
					$columns[] = $ranks_and_averages[$id . "_avg"];
					$columns[] = $ranks_and_averages[$id . "_rank"];
				}
				else
				{
					$suffix = "";
					if (in_array($id, $percentageBasedArr) && $this->use_percentage_of_agr)
					{
						$suffix = " %";
					}

					if (!in_array($id, $sensitiveFieldArr))
					{
						$val = $p_and_l_averages[$id . $suffix]['value'];
						if ($this->use_percentage_of_agr && !in_array($id, self::$hoursFields))
						{
							$val /= 100;
						}

						$columns[] = $val;
						$columns[] = $p_and_l_ranks[$id . $suffix];
					}
					else
					{
						$columns[] = "-";
						$columns[] = "-";
					}
				}
			}
		}

		return $retVal;
	}

	function getPandLByMonthAllStores($curMonth, $curYear, &$data, $store_id, $store_trade_area = false, $store_class = false, $opco_id = false, $logData = false)
	{

		$current_date = mktime(0, 0, 0, $curMonth, 1, $curYear);
		$current_date_sql = date("Y-m-d", $current_date);

		$storeClause = "inner join store st on st.id = smpl.store_id and st.active = 1 and store_type <> 'DISTRIBUTION_CENTER' ";
		if ($store_trade_area)
		{
			$storeClause .= "inner join store_trade_area str on str.store_id = st.id and str.trade_area_id = $store_trade_area and str.is_deleted = 0 and str.is_active = 1";
		}
		else if ($store_class)
		{
			$storeClause .= "inner join store_class_cache scc on scc.store_id = st.id and scc.class = $store_class ";
		}
		else if ($opco_id)
		{
			$storeClause .= " and opco_id = $opco_id";
		}

		$sqlstr = "select smpl.*  from store_monthly_profit_and_loss smpl
        $storeClause
        where smpl.date = '$current_date_sql' and smpl.is_deleted = 0";

		$p_and_l = DAO_CFactory::create("store_monthly_profit_and_loss");
		$p_and_l->query($sqlstr);

		if (!$opco_id && $p_and_l->N < 3 && (CUser::getCurrentUser()->user_type != CUser::SITE_ADMIN && CUser::getCurrentUser()->user_type != CUser::HOME_OFFICE_MANAGER))
		{
			if ($store_class)
			{
				return array(
					false,
					false
				);
			}
		}

		$averages = array();

		while ($p_and_l->fetch())
		{

			if (!isset($data[$p_and_l->store_id]))
			{
				$data[$p_and_l->store_id] = array();
			}

			$data[$p_and_l->store_id] = array_merge($data[$p_and_l->store_id], $p_and_l->toArray());
			$arrValues = $this->addPercentiles($data[$p_and_l->store_id]);

			foreach (self::$rankingFields as $fieldData)
			{

				$thisFieldName = $fieldData['name'];

				if ($fieldData['convert'] && $this->use_percentage_of_agr)
				{
					$thisFieldName .= " %";
				}

				if (isset($data[$p_and_l->store_id][$thisFieldName]))
				{
					if (!is_null($data[$p_and_l->store_id][$thisFieldName]) && $data[$p_and_l->store_id][$thisFieldName] !== "")
					{
						if (isset($averages[$thisFieldName]))
						{
							$averages[$thisFieldName]['store_count'] += 1;
							$averages[$thisFieldName]['value'] += $data[$p_and_l->store_id][$thisFieldName];
						}
						else
						{
							$averages[$thisFieldName] = array(
								'store_count' => 1,
								'value' => $data[$p_and_l->store_id][$thisFieldName]
							);
						}
					}
				}
			}
		}

		$rankings = array();
		foreach (self::$rankingFields as $fieldData)
		{
			$thisFieldName = $fieldData['name'];

			uasort($data, 'sort_by_' . $thisFieldName);

			$tempCount = 0;
			$tempRank = count($data);

			$finalFieldName = $thisFieldName;
			if ($fieldData['convert'] && $this->use_percentage_of_agr)
			{
				$finalFieldName .= " %";
			}

			//   if ($logData)
			//   {
			//      $this->logStoreData($data, $thisFieldName);
			//   }

			foreach ($data as $id => $thisRow)
			{
				if (!empty($thisRow[$finalFieldName]))
				{
					$tempCount++;
				}

				if ($id == $store_id)
				{
					$tempRank = $tempCount;
				}
			}

			$rankings[$finalFieldName] = $tempRank . " of " . $tempCount;
		}

		foreach ($averages as $name => &$thisAvg)
		{
			$thisAvg['value'] = $thisAvg['value'] / $thisAvg['store_count'];
		}

		return array(
			$rankings,
			$averages
		);
	}

	function logStoreData($data, $field)
	{
		$capture = array();
		foreach ($data as $store => $vals)
		{
			if (isset($vals[$field]))
			{
				$capture[$store] = $vals[$field];
			}
		}

		CLog::Record("Data for $field :" . print_r($capture, true));
	}

	function addPercentiles(&$dataArr)
	{


		$dataArr["mark_up %"] = CTemplate::divide_and_format((float)$dataArr["mark_up"] * 100, $dataArr["gross_sales"], 2);
		$dataArr["total_discounts %"] = CTemplate::divide_and_format((float)$dataArr["total_discounts"] * 100, $dataArr["gross_sales"], 2);

		$dataArr["cost_of_goods_and_services %"] = CTemplate::divide_and_format((float)$dataArr["cost_of_goods_and_services"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["employee_wages %"] = CTemplate::divide_and_format((float)$dataArr["employee_wages"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["bank_card_merchant_fees %"] = CTemplate::divide_and_format((float)$dataArr["bank_card_merchant_fees"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["kitchen_and_office_supplies %"] = CTemplate::divide_and_format((float)$dataArr["kitchen_and_office_supplies"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["total_marketing_and_advertising_expense %"] = CTemplate::divide_and_format((float)$dataArr["total_marketing_and_advertising_expense"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["rent_expense %"] = CTemplate::divide_and_format((float)$dataArr["rent_expense"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["repairs_and_maintenance %"] = CTemplate::divide_and_format((float)$dataArr["repairs_and_maintenance"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["utilities %"] = CTemplate::divide_and_format((float)$dataArr["utilities"] * 100, $dataArr["adjusted_gross_revenue"], 2);
		$dataArr["other_expenses %"] = CTemplate::divide_and_format((float)$dataArr["other_expenses"] * 100, $dataArr["adjusted_gross_revenue"], 2);
	}

	function getOrderInfoByMonthRange($startMonth, $endMonth, &$rows, $store_id, &$headerRow)
	{

		$startMonthTS = strtotime($startMonth);
		$month = date("n", $startMonthTS);
		$year = date("Y", $startMonthTS);

		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->findForMonthAndYear($month, $year);
		$MenuObj->fetch();
		$menu_start = date('Y-m-d H:i:s', strtotime($MenuObj->global_menu_start_date));

		$endMonthTS = strtotime($endMonth);
		$month = date("n", $endMonthTS);
		$year = date("Y", $endMonthTS);

		$MenuObj2 = DAO_CFactory::create('menu');
		$MenuObj2->findForMonthAndYear($month, $year);
		$MenuObj2->fetch();
		$menu_end = date('Y-m-d 23:59:59', strtotime($MenuObj2->global_menu_end_date));

		$tmpRows = array();

		$sqlstr = "Select    
		menu.menu_start,
		menu.id as menu_id,
		CONCAT(YEAR(session.session_start),'-',MONTH(session.session_start)), 
		YEAR(session.session_start) as year,
		MONTH(session.session_start) as month,
         sum(orders.subtotal_all_taxes) as sales_tax,
        sum(grand_total) as grand_total,
        (sum(orders.session_discount_total) + sum(ifnull(orders.coupon_code_discount_total, 0)) + sum(ifnull(orders.promo_code_discount_total, 0)) + sum(orders.user_preferred_discount_total) +
        sum(orders.direct_order_discount) + sum(orders.dream_rewards_discount) + sum(orders.points_discount_total)  +
        sum(orders.volume_discount_total) + sum(orders.bundle_discount))  as total_discounts,
        SUM(subtotal_home_store_markup) as markup_total,
        IFNULL(SUM(fundraiser_value), 0) as fundraising_total,
        IFNULL(SUM(subtotal_ltd_menu_item_value), 0) as ltd_item_donation_total,
        IFNULL(SUM(subtotal_delivery_fee), 0) as subtotal_delivery_fee

        From session
        Inner Join booking ON session.id = booking.session_id
        Inner Join orders ON  booking.order_id = orders.id
		Inner Join menu on menu.id = session.menu_id
        Where booking.status = 'ACTIVE' and booking.is_deleted = 0
        AND session.is_deleted = 0 and session_publish_state != 'SAVED'  and session.session_start >= '$menu_start' AND  session.session_start < '$menu_end'
		AND session.store_id = $store_id
        group by session.menu_id 
		order by session.session_start";

		$session = DAO_CFactory::create("session");
		$session->query($sqlstr);

		$markup_total = 0;

		while ($session->fetch())
		{

			$monthLabel = $session->menu_start;

			$nominalMonthParts = explode("-", $monthLabel);
			$nominalYear = $nominalMonthParts[0];
			$nominalMonth = $nominalMonthParts[1];

			$headerRow[] = date("M-Y", strtotime($monthLabel));

			list($menu_start_date, $interval) = CMenu::getMenuStartandInterval($session->menu_id);
			$start_date = strtotime($menu_start_date);
			$year = date("Y", $start_date);
			$month = date("n", $start_date);
			$day = date("j", $start_date);
			$duration = $interval . " DAY";

			$membershipFees = CDreamReport::getMembershipFeeRevenueByMenuID($store_id, $session->menu_id);
			$session->grand_total += $membershipFees;
			$DoorDashRevenue = CRoyaltyReport::getDoorDashRevenueByTimeSpan($start_date, $interval, $store_id);
			$session->grand_total += $DoorDashRevenue;
			$DoorDashFees = CRoyaltyReport::getDoorDashFeesByTimeSpan($start_date, $interval, $store_id);

			$markup_total += $session->markup_total;

			$tmpRows[$monthLabel]['grand_total'] = $session->grand_total;
			$tmpRows[$monthLabel]['sales_tax'] = $session->sales_tax;
			$tmpRows[$monthLabel]['mark_up'] = $session->markup_total;
			$tmpRows[$monthLabel]['total_discounts'] = $session->total_discounts;
			$tmpRows[$monthLabel]['fundraising_total'] = $session->fundraising_total;
			$tmpRows[$monthLabel]['ltd_item_donation_total'] = $session->ltd_item_donation_total;
			$tmpRows[$monthLabel]['subtotal_delivery_fee'] = $session->subtotal_delivery_fee;

			$thisStoreInfo = DAO_CFactory::create('store');
			$thisStoreInfo->query("select home_office_id, store_name, is_corporate_owned, state_id, grand_opening_date from store where id = $store_id");
			$thisStoreInfo->fetch();

			$performance = CRoyaltyReport::findPerformanceExceptions($menu_start_date, $duration, $store_id);
			$haspermanceoverride = false;
			if (isset($performance[$store_id]))
			{
				$haspermanceoverride = true;
			}

			$giftCertValues = CDreamReport::giftCertificatesByType($store_id, $day, $month, $year, $duration);
			$programdiscounts = CDreamReport::ProgramDiscounts($store_id, $day, $month, $year, $duration);
			if (empty($tmpRows[$monthLabel]['fundraising_total']))
			{
				$tmpRows[$monthLabel]['fundraising_total'] = 0;
			}
			if (empty($tmpRows[$monthLabel]['ltd_item_donation_total']))
			{
				$tmpRows[$monthLabel]['ltd_item_donation_total'] = 0;
			}
			if (empty($tmpRows[$monthLabel]['subtotal_delivery_fee']))
			{
				$tmpRows[$monthLabel]['subtotal_delivery_fee'] = 0;
			}
			if (empty($tmpRows[$monthLabel]['subtotal_bag_fee']))
			{
				$tmpRows[$monthLabel]['subtotal_bag_fee'] = 0;
			}

			$royaltyFee = 0;
			$marketingFee = 0;

			$instance = new CStoreExpenses();
			$expenseData = $instance->findExpenseDataByMonth($store_id, $day, $month, $year, $duration);

			CDreamReport::calculateFees($tmpRows[$monthLabel], $store_id, $haspermanceoverride, $expenseData, $giftCertValues, $programdiscounts, $tmpRows[$monthLabel]['fundraising_total'], $tmpRows[$monthLabel]['ltd_item_donation_total'], $tmpRows[$monthLabel]['subtotal_delivery_fee'], $tmpRows[$monthLabel]['delivery_tip'], $tmpRows[$monthLabel]['subtotal_bag_fee'], $DoorDashFees, $marketingFee, $royaltyFee, $thisStoreInfo->grand_opening_date, $month, $year);

			if ($thisStoreInfo->is_corporate_owned)
			{
				$royaltyFee = 0;
			}

			$salesForceFee = 0;
			if (($nominalYear == 2018 && $nominalMonth >= 9) || $nominalYear > 2018)
			{
				$salesForceFee = 250;
			}

			$base_sales = $tmpRows[$monthLabel]['grand_total'] - $tmpRows[$monthLabel]['sales_tax'];
			$gross_sales = $base_sales + $tmpRows[$monthLabel]['total_discounts'] - $tmpRows[$monthLabel]['mark_up'];
			$tmpRows[$monthLabel]['gross_sales'] = $gross_sales;
			$tmpRows[$monthLabel]['adjustments_and_discounts'] = $tmpRows[$monthLabel]['total_discounts'] + $tmpRows[$monthLabel]['discounts'] + $tmpRows[$monthLabel]['fundraising_total'] + $tmpRows[$monthLabel]['ltd_item_donation_total'] + $tmpRows[$monthLabel]['subtotal_delivery_fee'];
			$tmpRows[$monthLabel]['adjustments_and_discounts'] -= $tmpRows[$monthLabel]['adjustments'];

			$tmpRows[$monthLabel]['adjusted_gross_revenue'] = $gross_sales + $tmpRows[$monthLabel]['mark_up'] - $tmpRows[$monthLabel]['adjustments_and_discounts'];

			$rows[$monthLabel] = array(
				'gross_sales' => $gross_sales,
				'mark_up' => $tmpRows[$monthLabel]['mark_up'],
				'adjustments_and_discounts' => $tmpRows[$monthLabel]['adjustments_and_discounts'],
				'adjusted_gross_revenue' => $tmpRows[$monthLabel]['adjusted_gross_revenue'],
				'national_marketing_fee' => $marketingFee,
				'salesforce_fee' => $salesForceFee,
				'royalty_fee' => $royaltyFee
			);
		}
	}

	function getOrderInfoByMonthAllStores($Day, $Month, $Year, $interval, &$rows, $store_id, $store_trade_area = false, $store_class = false, $opco_id = false)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$storeClause = "Inner join store on store.id = orders.store_id and store.active = 1";
		if ($store_trade_area)
		{
			$storeClause .= " Inner join store_trade_area str on str.store_id = store.id and str.trade_area_id = $store_trade_area and str.is_deleted = 0 and str.is_active = 1";
		}
		else if ($store_class)
		{
			$storeClause .= " inner join store_class_cache scc on scc.store_id = store.id and scc.class = $store_class ";
		}
		else if ($opco_id)
		{
			$storeClause .= " and opco_id = $opco_id ";
		}

		$sqlstr = "Select
            session.store_id, sum(orders.subtotal_all_taxes) as sales_tax,
            sum(grand_total) as grand_total,
            (sum(orders.session_discount_total) + sum(ifnull(orders.coupon_code_discount_total, 0)) + sum(ifnull(orders.promo_code_discount_total, 0)) + sum(orders.user_preferred_discount_total) +
            sum(orders.direct_order_discount) + sum(orders.dream_rewards_discount) + sum(orders.points_discount_total)  +
            sum(orders.volume_discount_total) + sum(orders.bundle_discount))  as total_discounts,
            SUM(subtotal_home_store_markup) as markup_total,
            IFNULL(SUM(fundraiser_value), 0) as fundraising_total,
            IFNULL(SUM(subtotal_ltd_menu_item_value), 0) as ltd_menu_item_value,            
       		IFNULL(SUM(subtotal_delivery_fee), 0) as subtotal_delivery_fee,
       		IFNULL(SUM(subtotal_bag_fee), 0) as subtotal_bag_fee
            From session
            Inner Join booking ON session.id = booking.session_id
            Inner Join orders ON  booking.order_id = orders.id
            $storeClause
            Where booking.status = 'ACTIVE' and booking.is_deleted = 0
            AND session.is_deleted = 0 and session_publish_state != 'SAVED'  and session.session_start >= '$current_date_sql' AND  session.session_start <= DATE_ADD('$current_date_sql',INTERVAL $interval)
            group by session.store_id";

		$session = DAO_CFactory::create("session");
		$session->query($sqlstr);

		if (!$opco_id && $session->N < 3 && (CUser::getCurrentUser()->user_type != CUser::SITE_ADMIN && CUser::getCurrentUser()->user_type != CUser::HOME_OFFICE_MANAGER))
		{
			return array(
				'gross_sales_avg' => "-",
				'mark_up_avg' => "-",
				'discounts_total_avg' => "-",
				'agr_total_avg' => "-",
				'gross_sales_rank' => "-",
				'mark_up_rank' => "-",
				'discounts_total_rank' => "-",
				'agr_total_rank' => "-"
			);
		}

		$gross_sales_total = 0;
		$markup_total = 0;
		$discounts_and_adj_total = 0;
		$agr_total = 0;

		$countStores = 0;
		while ($session->fetch())
		{
			$markup_total += $session->markup_total;

			$membershipFees = CDreamReport::getMembershipFeeRevenue($session->store_id, $Day, $Month, $Year, $interval);
			$session->grand_total += $membershipFees;
			$DoorDashRevenue = CRoyaltyReport::getDoorDashRevenueByTimeSpan($Year . "-" . $Month . "-" . $Day, $interval, $session->store_id);
			$session->grand_total += $DoorDashRevenue;

			$rows[$session->store_id]['grand_total'] = $session->grand_total;
			$rows[$session->store_id]['sales_tax'] = $session->sales_tax;
			$rows[$session->store_id]['mark_up'] = $session->markup_total;
			$rows[$session->store_id]['total_discounts'] = $session->total_discounts;
			$rows[$session->store_id]['fundraising_total'] = $session->fundraising_total;
			$rows[$session->store_id]['ltd_menu_item_value'] = $session->ltd_menu_item_value;
			$rows[$session->store_id]['subtotal_delivery_fee'] = $session->subtotal_delivery_fee;
			$rows[$session->store_id]['subtotal_bag_fee'] = $session->subtotal_bag_fee;

			$countStores++;

			$stid = $session->store_id;

			$thisStoreInfo = DAO_CFactory::create('store');
			$thisStoreInfo->query("select home_office_id, store_name, state_id, grand_opening_date from store where id = $stid");
			$thisStoreInfo->fetch();

			$performance = CRoyaltyReport::findPerformanceExceptions($Year . "-" . $Month . "-" . 1, "1 MONTH", $stid);
			$haspermanceoverride = false;
			if (isset($performance[$store_id]))
			{
				$haspermanceoverride = true;
			}

			$giftCertValues = CDreamReport::giftCertificatesByType($stid, 1, $Month, $Year, '1 MONTH');
			$programdiscounts = CDreamReport::ProgramDiscounts($stid, 1, $Month, $Year, '1 MONTH');
			if (empty($rows[$session->store_id]['fundraising_total']))
			{
				$rows[$session->store_id]['fundraising_total'] = 0;
			}
			if (empty($rows[$session->store_id]['ltd_menu_item_value']))
			{
				$rows[$session->store_id]['ltd_menu_item_value'] = 0;
			}
			if (empty($rows[$session->store_id]['subtotal_delivery_fee']))
			{
				$rows[$session->store_id]['subtotal_delivery_fee'] = 0;
			}
			if (empty($rows[$session->store_id]['subtotal_bag_fee']))
			{
				$rows[$session->store_id]['subtotal_bag_fee'] = 0;
			}

			$royaltyFee = 0;
			$marketingFee = 0;

			$instance = new CStoreExpenses();
			$expenseData = $instance->findExpenseDataByMonth($stid, 1, $Month, $Year, '1 MONTH');

			CDreamReport::calculateFees($rows[$session->store_id], $stid, $haspermanceoverride, $expenseData, $giftCertValues, $programdiscounts, $rows[$session->store_id]['fundraising_total'], $rows[$session->store_id]['ltd_menu_item_value'], $rows[$session->store_id]['subtotal_delivery_fee'], $rows[$session->store_id]['delivery_tip'], $rows[$session->store_id]['subtotal_bag_fee'], 0, $marketingFee, $royaltyFee, $thisStoreInfo->grand_opening_date, $Month, $Year);

			$base_sales = $rows[$session->store_id]['grand_total'] - $rows[$session->store_id]['sales_tax'];
			$gross_sales = $base_sales + $rows[$session->store_id]['total_discounts'] - $rows[$session->store_id]['mark_up'];
			$rows[$session->store_id]['gross_sales'] = $gross_sales;
			$rows[$session->store_id]['adjustments_and_discounts'] = $rows[$session->store_id]['total_discounts'] + $rows[$session->store_id]['discounts'] + $rows[$session->store_id]['adjustments'] + $rows[$session->store_id]['fundraising_total'] + $rows[$session->store_id]['ltd_menu_item_value'] + $rows[$session->store_id]['subtotal_delivery_fee'];
			$rows[$session->store_id]['adjusted_gross_revenue'] = $gross_sales + $rows[$session->store_id]['mark_up'] - $rows[$session->store_id]['adjustments_and_discounts'];

			$discounts_and_adj_total += $rows[$session->store_id]['adjustments_and_discounts'];
			$gross_sales_total += $gross_sales;
			$agr_total += $rows[$session->store_id]['adjusted_gross_revenue'];
		}

		$gross_sales_rank = "";
		$mark_up_rank = "";
		$discounts_rank = "";
		$agr_total_rank = "";

		uasort($rows, 'sort_by_gross_sales');
		$tempCount = 0;
		$tempRank = $countStores;
		foreach ($rows as $id => $data)
		{
			if (!empty($data['gross_sales']))
			{
				$tempCount++;
			}

			if ($id == $store_id)
			{
				$tempRank = $tempCount;
			}
		}

		$grand_sales_rank = $tempRank . " of " . $tempCount;

		uasort($rows, 'sort_by_mark_up');
		$tempCount = 0;
		$tempRank = $countStores;
		foreach ($rows as $id => $data)
		{
			if (!empty($data['mark_up']))
			{
				$tempCount++;
			}

			if ($id == $store_id)
			{
				$tempRank = $tempCount;
			}
		}

		$mark_up_rank = $tempRank . " of " . $tempCount;

		uasort($rows, 'sort_by_discounts_and_adj');
		$tempCount = 0;
		$tempRank = $countStores;
		foreach ($rows as $id => $data)
		{
			if (!empty($data['total_discounts']))
			{
				$tempCount++;
			}

			if ($id == $store_id)
			{
				$tempRank = $tempCount;
			}
		}

		$discounts_rank = $tempRank . " of " . $tempCount;

		uasort($rows, 'sort_by_agr_total');
		$tempCount = 0;
		$tempRank = $countStores;
		foreach ($rows as $id => $data)
		{
			if (!empty($data['adjusted_gross_revenue']))
			{
				$tempCount++;
			}

			if ($id == $store_id)
			{
				$tempRank = $tempCount;
			}
		}

		$agr_total_rank = $tempRank . " of " . $tempCount;

		return array(
			'gross_sales_avg' => $gross_sales_total / $countStores,
			'mark_up_avg' => $markup_total / $countStores,
			'discounts_total_avg' => $discounts_and_adj_total / $countStores,
			'agr_total_avg' => $agr_total / $countStores,
			'gross_sales_rank' => $grand_sales_rank,
			'mark_up_rank' => $mark_up_rank,
			'discounts_total_rank' => $discounts_rank,
			'agr_total_rank' => $agr_total_rank
		);
	}
}

function sort_by_gross_sales($a, $b)
{
	if ($a['gross_sales'] == $b['gross_sales'])
	{
		return 0;
	}

	return ($a['gross_sales'] < $b['gross_sales'] ? 1 : -1);
}

function sort_by_mark_up($a, $b)
{
	if ($a['mark_up'] == $b['mark_up'])
	{
		return 0;
	}

	return ($a['mark_up'] < $b['mark_up'] ? 1 : -1);
}

function sort_by_discounts_and_adj($a, $b)
{
	if ($a['adjustments_and_discounts'] == $b['adjustments_and_discounts'])
	{
		return 0;
	}

	return ($a['adjustments_and_discounts'] < $b['adjustments_and_discounts'] ? 1 : -1);
}

function sort_by_agr_total($a, $b)
{
	if ($a['adjusted_gross_revenue'] == $b['adjusted_gross_revenue'])
	{
		return 0;
	}

	return ($a['adjusted_gross_revenue'] < $b['adjusted_gross_revenue'] ? 1 : -1);
}

function sort_by_cost_of_goods_and_services($a, $b)
{
	global $gUse_percentage_of_agr;

	$fieldName = "cost_of_goods_and_services";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_employee_wages($a, $b)
{
	global $gUse_percentage_of_agr;

	$fieldName = "employee_wages";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_employee_hours($a, $b)
{

	$fieldName = "employee_hours";

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_manager_hours($a, $b)
{

	$fieldName = "manager_hours";

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_bank_card_merchant_fees($a, $b)
{
	global $gUse_percentage_of_agr;

	$fieldName = "bank_card_merchant_fees";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_kitchen_and_office_supplies($a, $b)
{
	global $gUse_percentage_of_agr;

	$fieldName = "kitchen_and_office_supplies";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_total_marketing_and_advertising_expense($a, $b)
{
	global $gUse_percentage_of_agr;

	$fieldName = "total_marketing_and_advertising_expense";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_rent_expense($a, $b)
{

	global $gUse_percentage_of_agr;

	$fieldName = "rent_expense";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_repairs_and_maintenance($a, $b)
{

	global $gUse_percentage_of_agr;

	$fieldName = "repairs_and_maintenance";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_utilities($a, $b)
{

	global $gUse_percentage_of_agr;

	$fieldName = "utilities";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_other_expenses($a, $b)
{
	global $gUse_percentage_of_agr;

	$fieldName = "other_expenses";
	if ($gUse_percentage_of_agr)
	{
		$fieldName .= " %";
	}

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a[$fieldName] > $b[$fieldName] ? 1 : -1);
}

function sort_by_net_income($a, $b)
{
	$fieldName = "net_income";

	if (empty($a[$fieldName]) && empty($b[$fieldName]))
	{
		return 0;
	}

	if (empty($a[$fieldName]))
	{
		$a[$fieldName] = 0;
	}
	if (empty($b[$fieldName]))
	{
		$b[$fieldName] = 0;
	}

	if ($a[$fieldName] == $b[$fieldName])
	{
		return 0;
	}

	return ($a['net_income'] < $b['net_income'] ? 1 : -1);
}

?>