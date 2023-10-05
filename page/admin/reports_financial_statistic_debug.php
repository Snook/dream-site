<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/CDreamReport.inc');
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');

function sessionTimeSort($a, $b)
{

	$aTime = strtotime($a['session_start']);
	$bTime = strtotime($b['session_start']);

	if ($aTime == $bTime)
	{
		return 0;
	}

	return ($bTime > $aTime) ? -1 : 1;
}

function finStatReportRowsCallback($sheet, $data, $row, $bottomRightExtent)
{

	if ($data['session_type'] == 'Adjustment')
	{
		$styleArray = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array('argb' => 'FFFFE79F')
			)
		);
		$sheet->getStyle("A$row:$bottomRightExtent")->applyFromArray($styleArray);
	}
}

/*

function finStatReportCellCallback($sheet, $colName, $datum, $col, $row)
{

	if ($colName == "total_guests" or $colName == "gross_sales" or $colName == "total_discounts" or $colName == "subtotal_all_taxes")
	{
		$styleArray = array( 'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => array('argb' => 'FF9FE7FF')));
		$sheet->getStyle("$col$row")->applyFromArray($styleArray);
	}

}

*/

class page_admin_reports_financial_statistic_debug extends CPageAdminOnly
{
	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}


	private static $sessionTypeNameMap = array(
		"STANDARD" => 'Standard',
		"SPECIAL_EVENT" => 'MFY',
		"DREAM_TASTE" => 'Taste',
		"TODD" => 'Taste',
		"FUNDRAISER" => "Fundraiser"
	);

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$this->runFinancialStatisticV2();
	}

	function runFinancialStatisticV2()
	{
		$store = null;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;
		$total_count = 0;
		$report_submitted = false;

		$allowSiteWideReporting = false;
		if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING)
		{
			$allowSiteWideReporting = true;
			ini_set('memory_limit','-1');
			set_time_limit(3600 * 24);

		}

		if ($this->currentStore)
		{ //fadmins
			$store = $this->currentStore;
		}
		else
		{ //site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => $allowSiteWideReporting,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}

		$day = 0;
		$month = 0;
		$year = 0;
		$duration = "1 DAY";
		$spansMenu = false;

		$report_array = array();

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"])
		{
			$report_type_to_run = $_REQUEST["pickDate"];
		}

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::value => 'Run Report'
		));

		//$month_array = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
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

		if ($Form->value('report_submit'))
		{
			$report_submitted = true;
			$sessionArray = null;
			$menu_array_object = null;

			if ($report_type_to_run == 1)
			{
				$implodedDateArray = explode("-", $day_start);
				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = '1 DAY';
			}
			else if ($report_type_to_run == 2)
			{
				// process for an entire year
				$rangeReversed = false;
				$implodedDateArray = null;
				$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
				$diff++;  // always add one for SQL to work correctly
				if ($rangeReversed == true)
				{
					$implodedDateArray = explode("-", $range_day_end);
				}
				else
				{
					$implodedDateArray = explode("-", $range_day_start);
				}

				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = $diff . ' DAY';
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
			        $theMenu->query("select id, global_menu_start_date, DATEDIFF(global_menu_end_date, global_menu_start_date) + 1 as day_interval from menu where menu_start = '$dateStr'");
			        $theMenu->fetch();

			        $dateArr = explode("-", $theMenu->global_menu_start_date);

			        $day = $dateArr[2];
			        $month = $dateArr[1];
			        $year = $dateArr[0];
			        $duration = $theMenu->day_interval . " DAY";

			    }
			    else
			    {
    				$day = "01";
    				$month = $_REQUEST["month_popup"];
    				$month++;
    				$duration = '1 MONTH';
    				$year = $_REQUEST["year_field_001"];
			    }
			}
			else if ($report_type_to_run == 4)
			{
				$spansMenu = true;
				$year = $_REQUEST["year_field_002"];
				$month = "01";
				$day = "01";
				$duration = '1 YEAR';
			}

			$unsetArray = array();

			$suppressLTDDonationColumn = false;


			if ($store == 'all')
			{
				$programdiscounts = CDreamReport::findProgramTypesBySessionForNation($day, $month, $year, $duration);
				$certsUsed = $this->getCertificateAdjustmentsForNation($day, $month, $year, $duration);
				$subcats = $this->getSubCategoryBreakdownsForNation($day, $month, $year, $duration);
				$sessionData = $this->getSessionData($store, $day, $month, $year, $duration, $unsetArray, $programdiscounts, $subcats, $certsUsed);
			}
			else
			{


				$programdiscounts = CDreamReport::findProgramTypesBySession($store, $day, $month, $year, $duration);
				$certsUsed = $this->getCertificateAdjustments($store, $day, $month, $year, $duration);

				$subcats = $this->getSubCategoryBreakdowns($store, $day, $month, $year, $duration);

				$sessionData = $this->getSessionData($store, $day, $month, $year, $duration, $unsetArray, $programdiscounts, $subcats, $certsUsed, $suppressLTDDonationColumn);
			}

			if (count($sessionData))
			{

				if ($store == 'all')
				{
					$adjustments = $this->getStoreExpenseDataForNation($day, $month, $year, $duration, $unsetArray);
					$rows = $this->mergeDataForNation($programdiscounts, $sessionData, $adjustments, $unsetArray);
					$this->postProcessForNation($rows);
				}
				else
				{
					$adjustments = $this->getStoreExpenseData($store, $day, $month, $year, $duration, $unsetArray);

					$rows = $this->mergeData($programdiscounts, $sessionData, $adjustments, $unsetArray);


					if ($Form->value('menu_or_calendar') == 'menu')
					{
						$this->postProcess($rows, false, $store, $theMenu->id);
					}
					else
					{
						$formattedMonth = date("Y-m-d", mktime(0,0,0,$month, 1, $year));
						$this->postProcess($rows, $formattedMonth, $store);
					}
				}

				$columnDescs = array();

				if ($store == 'all')
				{
					$labels = array(
						"Month/Year",
						"Store",
						"Guests New",
						"Guests Reacquired",
						"Guests Existing",
						"Guests Total",
						"Total Follow ups",
						"Follow ups 2 months out",
						"Servings",
						"# Sides & Sweets Items",
						"# Core Items",
						"# FL & EFL Items",
					    "Entrees",
						"Entree Markup",
						"Sides & Sweets",
						"Sides & Sweets Markup",
						"Misc. Food",
						"Misc. Non-Food",
						"MFY Fee",
						"LTD Meal Donations"
					);

					$sectionHeader = array(" " => 2);
					$sectionHeader["Guests"] = 6;
					$columnDescs['A'] = array(
						'align' => 'center',
						'width' => 8
					);
					$columnDescs['B'] = array(
						'align' => 'left',
						'width' => 25
					);

					$col = 'C';
					$colSecondChar = '';
					$thirdSecondChar = '';

					//session type
					$columnDescs[$col] = array(
						'align' => 'center',
						'width' => 'auto'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					// simple numeric fields

					for ($x = 0; $x < 9; $x++)
					{

						if ($x == 2)
						{
							$columnDescs[$col] = array(
								'align' => 'center',
								'width' => '8',
								'decor' => 'subtotal'
							);
							incrementColumn($thirdSecondChar, $colSecondChar, $col);
						}
						else
						{
							$columnDescs[$col] = array(
								'align' => 'center',
								'width' => '8'
							);
							incrementColumn($thirdSecondChar, $colSecondChar, $col);
						}
					}
				}
				else
				{
					$labels = array(
						"Session Details Link",
						"Session Time",
						"Session Type",
						"Slots Available",
						"Guests New",
						"Guests Reacquired",
						"Guests Existing",
						"Guests Total",
						"Servings",
						"# Sides & Sweets Items",
						"# Core Items",
						"# FL & EFL Items",
					    "Entrees",
						"Entree Markup",
						"Sides & Sweets",
						"Sides & Sweets Markup",
						"Misc. Food",
						"Misc. Non-Food",
						"MFY Fee",
						"LTD Meal Donations"
					);

					$sectionHeader = array("Guests" => 8);
					$columnDescs['A'] = array(
						'align' => 'center',
						'width' => 8,
						'type' => 'URL'
						/*, 'decor' => 'fixed'*/
					);
					$columnDescs['B'] = array(
						'align' => 'center',
						'width' => 'auto',
						'type' => 'datetime'
						/*, 'decor' => 'fixed'*/
					);

					$col = 'C';
					$colSecondChar = '';
					$thirdSecondChar = '';

					//session type
					$columnDescs[$col] = array(
						'align' => 'center',
						'width' => 'auto'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					// simple numeric fields

					for ($x = 0; $x < 9; $x++)
					{

						if ($x == 4)
						{
							$columnDescs[$col] = array(
								'align' => 'center',
								'width' => '8',
								'decor' => 'subtotal'
							);
							incrementColumn($thirdSecondChar, $colSecondChar, $col);
						}
						else
						{
							$columnDescs[$col] = array(
								'align' => 'center',
								'width' => '8'
							);
							incrementColumn($thirdSecondChar, $colSecondChar, $col);
						}
					}
				}

				for ($x = 0; $x < 8; $x++)
				{
					$columnDescs[$col] = array(
						'align' => 'center',
						'width' => 'auto',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
				}

				// "Enrollment Fee" is the first conditional column

				if (!isset($unsetArray['enrollment_fee']))
				{
					$labels = array_merge($labels, array("Enrollment Fee"));
					$columnDescs[$col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					$sectionHeader['Items Sold'] = 14;
				}
				else
				{
					$sectionHeader['Items Sold'] = 13;
				}

				// fixed discounts
				$label2 = array(
					"Gross Sales",
					"Session Discount",
					"Coupon Code",
					"Preferred User",
					"Direct Order"
				);
				$labels = array_merge($labels, $label2);
				for ($x = 0; $x < 5; $x++)
				{
					if ($x == 0)
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'center',
							'width' => '12',
							'type' => 'currency',
							'decor' => 'subtotal'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
					else
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'center',
							'width' => '12',
							'type' => 'currency'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
				}

				$DiscountsCount = 7;

				if (!isset($unsetArray['dream_rewards_discount']))
				{
					$labels = array_merge($labels, array("Dream Rewards"));
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$DiscountsCount++;
				}

				if (!isset($unsetArray['points_discount_total']))
				{
					$labels = array_merge($labels, array("PLATEPOINTS Dinner Dollars"));
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$DiscountsCount++;
				}

				if (!isset($unsetArray['volume_discount_total']))
				{
					$labels = array_merge($labels, array("Volume Reward"));
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$DiscountsCount++;
				}

				if (!isset($unsetArray['family_savings_discount']))
				{
					$labels = array_merge($labels, array("Family Savings Discount"));
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$DiscountsCount++;
				}

				if (!isset($unsetArray['subtotal_menu_item_mark_down']))
				{
					$labels = array_merge($labels, array("Mark Down Discount"));
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$DiscountsCount++;
				}

				if (!isset($unsetArray['promo_code_discount_total']))
				{
					$labels = array_merge($labels, array("Promo Code"));
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$DiscountsCount++;
				}

				$sectionHeader['Discounts'] = $DiscountsCount;

				$label3 = array(
					"Taste Discount",
					"Starter Pack Discount",
					"Non-taxable Discounts Subtotal",
					"HO Sales Adjustment",
					"HO Sales Adjustment Comment",
					"Direct Referral Store Credit",
					"Invite a Friend Referral Store Credit",
				);

				$labels = array_merge($labels, $label3);
				for ($x = 0; $x < 7; $x++)
				{

					if ($x == 2)
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'center',
							'width' => '12',
							'type' => 'currency',
							'decor' => 'subtotal'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
					else if ($x == 4)
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'left',
							'width' => '16',
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
					else
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'center',
							'width' => '12',
							'type' => 'currency'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
				}

				$AdjustmentsColCount = 10;

				if (!isset($unsetArray['referral_reward_taste']))
				{
					$labels = array_merge($labels, array("Family Savings referral_reward_taste"));
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'center',
						'width' => '12',
						'type' => 'currency'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$AdjustmentsColCount++;
				}


				if (!$suppressLTDDonationColumn)
				{

				$labels23 = array(
					"Vouchers Gift Certs",
					"Donated Gift Certs",
					"Scrip Certs",
				    "Fundraising Dollars",
						"LTD Meal Donations",
					"Taxable Discount Total",
					"Adjusted Gross Revenue",
					"Service Taxes",
					"Food Taxes",
					"Sales Taxes",
					"Subtotal Taxes",
					"Post Tax Total",
						"LTD Round-Up Donations",
						"LTD Meal Donations",
						"LTD Total Donations",
				);

					$AdjustmentsColCount++;

					for ($x = 0; $x < 12; $x++)
					{

						if ($x == 5)
						{
							$columnDescs[$colSecondChar . $col] = array(
								'align' => 'center',
								'width' => '12',
								'type' => 'currency',
								'decor' => 'subtotal'
							);
							incrementColumn($thirdSecondChar, $colSecondChar, $col);
						}
						else if ($x == 6 || $x == 11)
						{
							$columnDescs[$colSecondChar . $col] = array(
								'align' => 'left',
								'width' => '16',
								'decor' => 'majortotal',
								'type' => 'currency'
							);
							incrementColumn($thirdSecondChar, $colSecondChar, $col);
						}
						else
				{
							$columnDescs[$colSecondChar . $col] = array(
								'align' => 'center',
								'width' => '12',
								'type' => 'currency'
							);
							incrementColumn($thirdSecondChar, $colSecondChar, $col);
						}
				}


				}
				else
				{
					$labels23 = array(
						"Vouchers Gift Certs",
						"Donated Gift Certs",
						"Scrip Certs",
						"Fundraising Dollars",
						"Taxable Discount Total",
						"Adjusted Gross Revenue",
						"Service Taxes",
						"Food Taxes",
						"Sales Taxes",
						"Subtotal Taxes",
						"Post Tax Total"
					);

					for ($x = 0; $x < 11; $x++)
				{

					if ($x == 4)
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'center',
							'width' => '12',
							'type' => 'currency',
							'decor' => 'subtotal'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
					else if ($x == 5 || $x == 10)
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'left',
							'width' => '16',
							'decor' => 'majortotal',
							'type' => 'currency'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
					else
					{
						$columnDescs[$colSecondChar . $col] = array(
							'align' => 'center',
							'width' => '12',
							'type' => 'currency'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
				}


				}



				$labels = array_merge($labels, $labels23);

				$sectionHeader['Taxable Discounts and Adjustments'] = $AdjustmentsColCount;

				$sectionHeader['Taxes'] = 5;

				if (!$suppressLTDDonationColumn)
				{
					$sectionHeader['Living the Dream'] = 3;
					$columnDescs[$colSecondChar . $col] = array(
						'align' => 'left',
						'width' => '16',
						'type' => 'currency'
					);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				$columnDescs[$colSecondChar . $col] = array(
						'align' => 'left',
						'width' => '16',
					'type' => 'currency'
				);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar . $col] = array(
						'align' => 'left',
						'width' => '16',
						'decor' => 'majortotal',
					'type' => 'currency'
				);

				$labels25 = array(
					"Revenue Event Sum",
					"Delta"
				);

				$labels = array_merge($labels, $labels25);
				}

				$numRows = count($rows);
				$tpl->assign('labels', $labels);
				$tpl->assign('rows', $rows);
				$tpl->assign('rowcount', $numRows);
				$tpl->assign('sectionHeader', $sectionHeader);
				$tpl->assign('col_descriptions', $columnDescs);
				//$callbacks = array('row_callback' => 'finStatReportRowsCallback', 'cell_callback' => 'finStatReportCellCallback');
				$callbacks = array('row_callback' => 'finStatReportRowsCallback');
				$tpl->assign('excel_callbacks', $callbacks);

				$_GET['export'] = 'xlsx';

				CLog::RecordReport("Financial Statistical v2 (Excel export)", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration");
			}
			else
			{
				$tpl->assign('empty_result', true);
			}
		}

		$formArray = $Form->render();

		$tpl->assign('report_submitted', $report_submitted);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'Statistical/Financial Report');
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

	function getCertificateAdjustments($store_id, $Day, $Month, $Year, $interval)
	{
		$giftCertsArr = array();
		$gifttype = CPayment::GIFT_CERT;  // need to locate payment constant for htis
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		//$storeid = 98;
		$varstr = "Select s.id
						,sum(if(p.gift_cert_type = 'DONATED', p.total_amount, 0)) as donated_gift_cert
						,sum(if(p.gift_cert_type = 'VOUCHER', p.total_amount, 0)) as voucher_gift_cert
						,sum(if(p.gift_cert_type = 'SCRIP', p.total_amount, 0)) as scrip_gift_cert
				  from  session s
						inner Join booking b on s.id = b.session_id
						inner Join orders o on  b.order_id = o.id
						inner Join payment p on o.id = p.order_id
						where p.payment_type = 'GIFT_CERT' and s.store_id = $store_id and b.status = 'ACTIVE' and b.is_deleted = 0
						 and s.is_deleted = 0 and s.session_publish_state != 'SAVED'  and s.session_start >= '$current_date_sql'
						 AND  s.session_start <= DATE_ADD('$current_date_sql',INTERVAL $interval)
						  group by s.id, p.gift_cert_type";
		$session = DAO_CFactory::create("session");
		$session->query($varstr);
		$counter = 0;

		while ($session->fetch())
		{

			$thisVal = array(
				'session_id' => $session->id,
				'donated' => $session->donated_gift_cert,
				'voucher' => $session->voucher_gift_cert,
				'scrip' => $session->scrip_gift_cert
			);
			$giftCertsArr[$session->id] = $thisVal;
		}

		return $giftCertsArr;
	}

	function getCertificateAdjustmentsForNation($Day, $Month, $Year, $interval)
	{
		$giftCertsArr = array();
		$gifttype = CPayment::GIFT_CERT;
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$varstr = "Select CONCAT(MONTH(s.session_start), ' ', YEAR(s.session_start)) as month_year
		,s.store_id
		,sum(if(p.gift_cert_type = 'DONATED', p.total_amount, 0)) as donated_gift_cert
		,sum(if(p.gift_cert_type = 'VOUCHER', p.total_amount, 0)) as voucher_gift_cert
		,sum(if(p.gift_cert_type = 'SCRIP', p.total_amount, 0)) as scrip_gift_cert
		from  session s
		inner Join booking b on s.id = b.session_id
		inner Join orders o on  b.order_id = o.id
		inner Join payment p on o.id = p.order_id
		where p.payment_type = 'GIFT_CERT' and b.status = 'ACTIVE' and b.is_deleted = 0
		and s.is_deleted = 0 and s.session_publish_state != 'SAVED'  and s.session_start >= '$current_date_sql'
		AND  s.session_start <= DATE_ADD('$current_date_sql',INTERVAL $interval)
		group by CONCAT(MONTH(s.session_start), ' ', YEAR(s.session_start)), s.store_id, p.gift_cert_type";

		$session = DAO_CFactory::create("session");
		$session->query($varstr);
		$counter = 0;

		while ($session->fetch())
		{

			$thisVal = array(
				'donated' => $session->donated_gift_cert,
				'voucher' => $session->voucher_gift_cert,
				'scrip' => $session->scrip_gift_cert
			);
			$giftCertsArr[$session->month_year][$session->store_id] = $thisVal;
		}

		return $giftCertsArr;
	}

	function getSessionData($store_id, $Day, $Month, $Year, $interval, &$unsetColumns, $programdiscounts, $subcats, $certsUsed, $suppressLTDDonationColumn = false)
	{
		$varStr = "";
		$PUBLISH_SESSIONS_STATE = 'SAVED';
		// to collect accurate financial data.. a session that is SAVED is not recorded...
		// but sessions that are either closed or published should be recorded
		// also.. only grab bookings that are ACTIVE.. do not look for RESCHEUDLED, HOLD or CANCELLED
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		if ($store_id == 'all')
		{
			$query = "select CONCAT(MONTH(s.session_start), ' ', YEAR(s.session_start)) as month_year, s.store_id, count(if (od.user_state = 'NEW', 1, null)) as new_guests, count(if (od.user_state = 'REACQUIRED', 1, null)) as reac_guests,
						 count(if (od.user_state = 'EXISTING', 1, null)) as ex_guests, count(od.id) as total_guests,
						sum(fu.numFU) as follow_ups, count(if((CAST(s2.menu_id AS SIGNED) - cast(s.menu_id AS SIGNED)) > 1, 1, null)) as follow_ups_skips_menu,
						sum(orders.servings_total_count) as servings, 0 as ft_count, 0 as non_fl_count, 0 as fl_count, ";
		}
		else
		{

			$query = "select s.id as session_id, s.session_start, s.session_type, s.available_slots, count(if (od.user_state = 'NEW', 1, null)) as new_guests, count(if (od.user_state = 'REACQUIRED', 1, null)) as reac_guests,
						 count(if (od.user_state = 'EXISTING', 1, null)) as ex_guests, count(od.id) as total_guests, sum(orders.servings_total_count) as servings, 0 as ft_count, 0 as non_fl_count, 0 as fl_count, ";
		}

		$query .= "0 as entree_total, 0 as core_mu_total, 0 as ft_total, 0 as ft_mu_total, ";

		$query .= "sum(orders.misc_food_subtotal) as misc_food_subtotal, sum(orders.misc_nonfood_subtotal) as misc_nonfood_subtotal, sum(orders.subtotal_service_fee) as subtotal_service_fee,
							sum(orders.subtotal_products - orders.misc_nonfood_subtotal) as enrollment_fee, sum(ifnull(orders.subtotal_ltd_menu_item_value,0)) as ltd_meal_item_value_revenue, ";

		$query .= "sum(orders.misc_food_subtotal + orders.misc_nonfood_subtotal + orders.subtotal_menu_items + orders.subtotal_home_store_markup + orders.subtotal_service_fee)
			+ sum(orders.subtotal_products - orders.misc_nonfood_subtotal) as gross_sales, ";

		$query .= "sum(orders.session_discount_total) * -1 as session_discount_total,
					sum(ifnull(orders.coupon_code_discount_total, 0)) * -1  as coupon_code_discount_total,
					sum(ifnull(orders.user_preferred_discount_total, 0)) * -1  as user_preferred_discount_total,
					sum(ifnull(orders.direct_order_discount, 0)) * -1  as direct_order_discount,
					sum(ifnull(orders.dream_rewards_discount, 0)) * -1  as dream_rewards_discount,
					sum(ifnull(orders.points_discount_total, 0)) * -1  as points_discount_total,
					sum(ifnull(orders.volume_discount_total, 0)) * -1 as volume_discount_total,
					sum(ifnull(orders.family_savings_discount, 0)) * -1 as family_savings_discount,
					sum(ifnull(orders.subtotal_menu_item_mark_down, 0)) * -1 as subtotal_menu_item_mark_down,
					sum(ifnull(orders.promo_code_discount_total, 0)) * -1  as promo_code_discount_total,
					sum(if(orders.type_of_order <> 'INTRO', orders.bundle_discount, 0)) * -1  as taste_discount,
					sum(if(orders.type_of_order = 'INTRO', orders.bundle_discount, 0)) * -1  as intro_discount, ";

		$query .= "(sum(ifnull(orders.session_discount_total, 0)) + sum(ifnull(orders.coupon_code_discount_total, 0)) + sum(ifnull(orders.promo_code_discount_total, 0)) + sum(ifnull(orders.user_preferred_discount_total, 0)) +
					sum(ifnull(orders.direct_order_discount, 0)) + sum(ifnull(orders.dream_rewards_discount, 0)) + sum(ifnull(orders.points_discount_total, 0))  + sum(ifnull(orders.volume_discount_total, 0))
											+ sum(ifnull(orders.subtotal_menu_item_mark_down, 0)) + sum(ifnull(orders.bundle_discount, 0))) * -1 as total_discounts, ";


		if (!$suppressLTDDonationColumn)
		{

			$query .= "0.0 as sales_adjustments, '' as adj_comments, 0.0 as referral_reward_direct, 0.0 as referral_reward_iaf, 0.0 as referral_reward_taste, 0.0 as certs_voucher,
						 0.0 as certs_donated, 0.0 as certs_scrip, sum(ifnull(orders.fundraiser_value, 0)) * -1 as fundraiser_value, 
							sum(ifnull(orders.subtotal_ltd_menu_item_value,0)) * -1 as ltd_menu_item_value_2, 0.0 as subtotal_program_discounts, ";

			$query .= "sum(orders.subtotal_all_items) as subtotal_all_items, sum(orders.subtotal_service_tax) as subtotal_service_tax, sum(orders.subtotal_food_sales_taxes) as subtotal_food_sales_taxes,
						sum(orders.subtotal_sales_taxes) as subtotal_sales_taxes, sum(orders.subtotal_all_taxes) as subtotal_all_taxes, sum(orders.grand_total) as grand_total,
						sum(ifnull(orders.ltd_round_up_value,0)) as ltd_round_up_value, sum(ifnull(orders.subtotal_ltd_menu_item_value,0)) as ltd_meal_item_value, sum(ifnull(orders.ltd_round_up_value,0)) + sum(ifnull(orders.subtotal_ltd_menu_item_value,0)) as ltd_total ";
		}
		else
		{

			$query .= "0.0 as sales_adjustments, '' as adj_comments, 0.0 as referral_reward_direct, 0.0 as referral_reward_iaf, 0.0 as referral_reward_taste, 0.0 as certs_voucher,
						 0.0 as certs_donated, 0.0 as certs_scrip, sum(ifnull(orders.fundraiser_value, 0)) * -1 as fundraiser_value, 0.0 as subtotal_program_discounts, ";

			$query .= "sum(orders.subtotal_all_items) as subtotal_all_items, sum(orders.subtotal_service_tax) as subtotal_service_tax, sum(orders.subtotal_food_sales_taxes) as subtotal_food_sales_taxes,
						sum(orders.subtotal_sales_taxes) as subtotal_sales_taxes, sum(orders.subtotal_all_taxes) as subtotal_all_taxes, sum(orders.grand_total) as grand_total ";
		}


		$query .= ", s.id as session_again, 1 as TBD ";

		if ($store_id == 'all')
		{

			$query .= "from booking
						inner join session s on booking.session_id = s.id
						inner join store st on st.id = s.store_id
						inner join orders on booking.order_id = orders.id
						left join bundle on orders.bundle_id = bundle.id
						join orders_digest od on od.order_id = orders.id and od.is_deleted = 0
						left join
							(select count(od2.id) as numFU,
								od2.in_store_trigger_order,
								od2.is_deleted,
								od2.order_id from orders_digest od2
								group by od2.in_store_trigger_order) as fu
										on orders.id = fu.in_store_trigger_order and fu.is_deleted = 0
						left join booking b2 on b2.order_id = fu.order_id and b2.status = 'ACTIVE'
						left join session s2 on b2.session_id = s2.id
					 where st.active = 1 and s.session_start >= '$current_date_sql' and
					s.session_start <=  DATE_ADD('$current_date_sql', INTERVAL $interval) and
					booking.status  = 'ACTIVE' and s.session_publish_state != '$PUBLISH_SESSIONS_STATE'
					group by s.store_id , CONCAT(MONTH(s.session_start), ' ', YEAR(s.session_start))
						order by s.store_id, s.session_start";
		}
		else
		{
			$query .= "from booking
			inner join session s on booking.session_id = s.id
			inner join orders on booking.order_id = orders.id
			left join bundle on orders.bundle_id = bundle.id
			join orders_digest od on od.order_id = orders.id and od.is_deleted = 0
			where s.store_id = $store_id and s.session_start >= '$current_date_sql' and
			s.session_start <=  DATE_ADD('$current_date_sql', INTERVAL $interval) and
			booking.status  = 'ACTIVE' and s.session_publish_state != '$PUBLISH_SESSIONS_STATE'
			group by s.id order by s.session_start";
		}

		$booking = DAO_CFactory::create("booking");
		$booking->query($query);
		$rows = array();
		$count = 0;

		while ($booking->fetch())
		{

			$vartemp = $booking->toArray();

			if ($store_id == 'all')
			{
				if (!empty($programdiscounts[$vartemp['month_year']][$vartemp['store_id']]))
				{
					$temparray = $programdiscounts[$vartemp['month_year']][$vartemp['store_id']];

					if (!empty($temparray['DIRECT']))
					{
						$vartemp['referral_reward_direct'] -= $temparray['DIRECT']['amount_spent'];
					}

					if (!empty($temparray['TODD']))
					{
						$vartemp['referral_reward_taste'] -= $temparray['TODD']['amount_spent'];
					}

					if (!empty($temparray['IAF']))
					{
						$vartemp['referral_reward_iaf'] -= $temparray['IAF']['amount_spent'];
					}
				}

				if (!empty($certsUsed[$vartemp['month_year']][$vartemp['store_id']]))
				{
					$temparray = $certsUsed[$vartemp['month_year']][$vartemp['store_id']];

					if (!empty($temparray['donated']))
					{
						$vartemp['certs_donated'] -= $temparray['donated'];
					}

					if (!empty($temparray['voucher']))
					{
						$vartemp['certs_voucher'] -= $temparray['voucher'];
					}

					if (!empty($temparray['scrip']))
					{
						$vartemp['certs_scrip'] -= $temparray['scrip'];
					}
				}

				if (isset($subcats) && !empty($subcats) && isset($subcats[$vartemp['month_year']][$vartemp['store_id']]))
				{
					$vartemp['ft_count'] = $subcats[$vartemp['month_year']][$vartemp['store_id']]['ft_item_total'];
					$vartemp['ft_total'] = $subcats[$vartemp['month_year']][$vartemp['store_id']]['ft_total'];

					$vartemp['entree_total'] = $subcats[$vartemp['month_year']][$vartemp['store_id']]['core_total'];

					$vartemp['ft_mu_total'] = $subcats[$vartemp['month_year']][$vartemp['store_id']]['post_mu_ft_total'] - $vartemp['ft_total'];
					$vartemp['core_mu_total'] = $subcats[$vartemp['month_year']][$vartemp['store_id']]['post_mu_core_total'] - $vartemp['entree_total'];


					$vartemp['non_fl_count'] = $subcats[$vartemp['month_year']][$vartemp['store_id']]['non_fl_item_count'];
					$vartemp['fl_count'] = $subcats[$vartemp['month_year']][$vartemp['store_id']]['fl_item_count'];

				}

// NOTE:  Adjust this value as columns are added or removed
				$vartemp = array_slice($vartemp, 0, 55);

				if (isset($vartemp['intro_discount']) && ($vartemp['intro_discount'] === "" || is_null($vartemp['intro_discount'])))
				{
					$vartemp['intro_discount'] = "0.00";
				}

				if (isset($vartemp['taste_discount']) && ($vartemp['taste_discount'] === "" || is_null($vartemp['taste_discount'])))
				{
					$vartemp['taste_discount'] = "0.00";
				}
			}
			else
			{

				if (!empty($programdiscounts[$vartemp['session_id']]))
				{
					$temparray = $programdiscounts[$vartemp['session_id']];

					if (!empty($temparray['DIRECT']))
					{
						$vartemp['referral_reward_direct'] -= $temparray['DIRECT']['amount_spent'];
					}

					if (!empty($temparray['TODD']))
					{
						$vartemp['referral_reward_taste'] -= $temparray['TODD']['amount_spent'];
					}

					if (!empty($temparray['IAF']))
					{
						$vartemp['referral_reward_iaf'] -= $temparray['IAF']['amount_spent'];
					}
				}

				if (!empty($certsUsed[$vartemp['session_id']]))
				{
					$temparray = $certsUsed[$vartemp['session_id']];

					if (!empty($temparray['donated']))
					{
						$vartemp['certs_donated'] -= $temparray['donated'];
					}

					if (!empty($temparray['voucher']))
					{
						$vartemp['certs_voucher'] -= $temparray['voucher'];
					}

					if (!empty($temparray['scrip']))
					{
						$vartemp['certs_scrip'] -= $temparray['scrip'];
					}
				}

				if (isset($subcats) && !empty($subcats) && isset($subcats[$vartemp['session_id']]))
				{
					$vartemp['ft_count'] = $subcats[$vartemp['session_id']]['ft_item_total'];
					$vartemp['ft_total'] = $subcats[$vartemp['session_id']]['ft_total'];

					$vartemp['entree_total'] = $subcats[$vartemp['session_id']]['core_total'];

					$vartemp['ft_mu_total'] = $subcats[$vartemp['session_id']]['post_mu_ft_total'] - $vartemp['ft_total'];
					$vartemp['core_mu_total'] = $subcats[$vartemp['session_id']]['post_mu_core_total'] - $vartemp['entree_total'];

					$vartemp['non_fl_count'] = $subcats[$vartemp['session_id']]['non_fl_item_count'];
					$vartemp['fl_count'] = $subcats[$vartemp['session_id']]['fl_item_count'];

				}
				// NOTE:  Adjust this value as columns are added or removed
				if ($suppressLTDDonationColumn)
				{
					$vartemp = array_slice($vartemp, 0, 52);
				}
				else
				{
					$vartemp = array_slice($vartemp, 0, 57);
				}

				$vartemp['session_id'] = "=HYPERLINK(\"" . HTTPS_BASE . "backoffice/main?session=" . $vartemp['session_id'] . "\", \"Details\")";

				if (isset($vartemp['intro_discount']) && ($vartemp['intro_discount'] === "" || is_null($vartemp['intro_discount'])))
				{
					$vartemp['intro_discount'] = "0.00";
				}

				if (isset($vartemp['taste_discount']) && ($vartemp['taste_discount'] === "" || is_null($vartemp['taste_discount'])))
				{
					$vartemp['taste_discount'] = "0.00";
				}

				$vartemp['session_type'] = self::$sessionTypeNameMap[$vartemp['session_type']];
			}

				$rows [$count++] = $vartemp;
			}

		// post process to remove certain columns if all 0
		$totalVolumeDiscount = 0;
		$familySavingsDiscount = 0;
		$enrollmentFee = 0;
		$tasteReferralCredit = 0;
		$dreamRewardsDiscount = 0;
		$freeMealPromoDiscount = 0;
		$platePointsDiscount = 0;
		$markDownDiscount = 0;

		foreach ($rows as $k => $v)
		{
			$totalVolumeDiscount += $v['volume_discount_total'];
			$familySavingsDiscount += $v['family_savings_discount'];
			$markDownDiscount += $v['subtotal_menu_item_mark_down'];
			$enrollmentFee += $v['enrollment_fee'];
			$tasteReferralCredit += $v['referral_reward_taste'];
			$dreamRewardsDiscount += $v['dream_rewards_discount'];
			$freeMealPromoDiscount += $v['promo_code_discount_total'];
			$platePointsDiscount += $v['points_discount_total'];
		}

		if ($totalVolumeDiscount == 0)
		{
			$unsetColumns['volume_discount_total'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['volume_discount_total']);
			}
		}

		if ($familySavingsDiscount == 0)
		{
			$unsetColumns['family_savings_discount'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['family_savings_discount']);
			}
		}

		if ($markDownDiscount == 0)
		{
			$unsetColumns['subtotal_menu_item_mark_down'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['subtotal_menu_item_mark_down']);
			}
		}

		if ($enrollmentFee == 0)
		{
			$unsetColumns['enrollment_fee'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['enrollment_fee']);
			}
		}

		if ($tasteReferralCredit == 0)
		{
			$unsetColumns['referral_reward_taste'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['referral_reward_taste']);
			}
		}

		if ($dreamRewardsDiscount == 0)
		{
			$unsetColumns['dream_rewards_discount'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['dream_rewards_discount']);
			}
		}

		if ($freeMealPromoDiscount == 0)
		{
			$unsetColumns['promo_code_discount_total'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['promo_code_discount_total']);
			}
		}

		if ($platePointsDiscount == 0)
		{
			$unsetColumns['points_discount_total'] = true;
			foreach ($rows as $k => &$v)
			{
				unset($v['points_discount_total']);
			}
		}

		return ($rows);
	}

	function getSubCategoryBreakdowns($store_id, $Day, $Month, $Year, $interval)
	{
		$varStr = "";
		$menucat = "(9)";

		$rows = array();
		$count = 0;

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$fieldlist = "select s.id,
				sum(oi.item_count) as item_count,
				sum(oi.pre_mark_up_sub_total) as item_total,
				sum(if(mi.menu_item_category_id = 9, oi.pre_mark_up_sub_total, 0)) as ft_total,
				sum(if(mi.menu_item_category_id = 9, oi.item_count, 0)) as ft_item_total,
				sum(if(mi.menu_item_category_id <> 9, oi.pre_mark_up_sub_total, 0)) as core_total,
				sum(if(mi.menu_item_category_id <> 9, oi.item_count, 0)) as core_item_total,
				sum(if(mi.menu_item_category_id = 9, oi.sub_total, 0)) as post_mu_ft_total,
				sum(if(mi.menu_item_category_id <> 9, oi.sub_total, 0)) as post_mu_core_total,
        		sum(if(mi.menu_item_category_id = 1, oi.item_count, 0)) as non_fl_item_count,
        		sum(if(mi.menu_item_category_id = 4, oi.item_count, 0)) as fl_item_count
				from booking b
				inner join session s on b.session_id = s.id
				inner join orders o on b.order_id = o.id
				inner join order_item oi on o.id = oi.order_id
				inner join menu_item mi on oi.menu_item_id = mi.id  and oi.is_deleted = 0
				where s.store_id = $store_id and s.session_start >= '$current_date_sql' and  s.session_start <=  DATE_ADD('$current_date_sql', INTERVAL $interval )
				and  s.is_deleted = 0 and b.is_deleted = 0 and b.status = 'ACTIVE' and  s.session_publish_state != 'SAVED'
				group by s.id
				order by s.session_start";

		$booking = DAO_CFactory::create("booking");
		$booking->query($fieldlist);
		$rows = array();
		$count = 0;
		while ($booking->fetch())
		{
			$vartemp = $booking->toArray();

			$rows [$booking->id] = $vartemp;
		}

		return ($rows);
	}

	function getSubCategoryBreakdownsForNation($Day, $Month, $Year, $interval)
	{
		$varStr = "";
		$menucat = "(9)";

		$rows = array();
		$count = 0;

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$fieldlist = "Select CONCAT(MONTH(s.session_start), ' ', YEAR(s.session_start)) as month_year, s.store_id,
		sum(oi.item_count) as item_count,
		sum(oi.pre_mark_up_sub_total) as item_total,
		sum(if(mi.menu_item_category_id = 9, oi.pre_mark_up_sub_total, 0)) as ft_total,
		sum(if(mi.menu_item_category_id = 9, oi.item_count, 0)) as ft_item_total,
		sum(if(mi.menu_item_category_id <> 9, oi.pre_mark_up_sub_total, 0)) as core_total,
		sum(if(mi.menu_item_category_id <> 9, oi.item_count, 0)) as core_item_total,
		sum(if(mi.menu_item_category_id = 9, oi.sub_total, 0)) as post_mu_ft_total,
		sum(if(mi.menu_item_category_id <> 9, oi.sub_total, 0)) as post_mu_core_total,
		sum(if(mi.menu_item_category_id = 1, oi.item_count, 0)) as non_fl_item_count,
		sum(if(mi.menu_item_category_id = 4, oi.item_count, 0)) as fl_item_count
		from booking b
		inner join session s on b.session_id = s.id
		inner join orders o on b.order_id = o.id
		inner join order_item oi on o.id = oi.order_id
		inner join menu_item mi on oi.menu_item_id = mi.id  and oi.is_deleted = 0
		where s.session_start >= '$current_date_sql' and  s.session_start <=  DATE_ADD('$current_date_sql', INTERVAL $interval )
		and  s.is_deleted = 0 and b.is_deleted = 0 and b.status = 'ACTIVE' and  s.session_publish_state != 'SAVED'
		group by CONCAT(MONTH(s.session_start), ' ', YEAR(s.session_start)), s.store_id
		order by s.session_start";

		$booking = DAO_CFactory::create("booking");
		$booking->query($fieldlist);
		$rows = array();
		$count = 0;
		while ($booking->fetch())
		{
			$vartemp = $booking->toArray();

			$rows [$booking->month_year][$booking->store_id] = $vartemp;
		}

		return ($rows);
	}

	function getStoreExpenseData($store_id, $Day, $Month, $Year, $interval, $unsetArray)
	{

		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$arr = null;
		$varstr = "select store_expenses.entry_date, store_expenses.expense_type, store_expenses.notes, store_expenses.units, store_expenses.total_cost
		From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") and store_id = " . $store_id . " and store_expenses.is_deleted = 0 and store_expenses.expense_type in ('FUNDRAISER_DOLLARS', 'ESCRIP_PAYMENTS','SALES_ADJUSTMENTS') order by entry_date, id DESC";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);

		if (isset($unsetArray['referral_reward_taste']))
		{
			$pad_amount = 35 - (count($unsetArray) - 1);
		}
		else
		{
			$pad_amount = 35 - count($unsetArray);
		}

		while ($store_expenses->fetch())
		{
			$arr = $store_expenses->toArray();
			$ts = date("Y-m-d 00:00:00", strtotime($arr['entry_date']));

			$newEntity = array(
				"session_id" => "",
				'session_start' => $ts,
				'session_type' => 'Adjustment'
			);

			$newEntity = array_pad($newEntity, $pad_amount, "");
			if ($arr['expense_type'] != 'SALES_ADJUSTMENTS')
			{
				$arr['total_cost'] = $arr['total_cost'] * -1;
			}
			if (isset($unsetArray['referral_reward_taste']))
			{
				$newEntity = array_merge($newEntity, array(
					'total_cost' => $arr['total_cost'],
					'expense_type' => $arr['expense_type'] . ' - ' . $arr['notes'],
					"",
					"",
					"",
					"",
					"",
				    "",
				    "",
					$arr['total_cost']
				));
			}
			else
			{
				$newEntity = array_merge($newEntity, array(
					'total_cost' => $arr['total_cost'],
					'expense_type' => $arr['expense_type'] . ' - ' . $arr['notes'],
					"",
					"",
					"",
					"",
				    "",
					"",
					"",
					"",
					$arr['total_cost']
				));
			}

			$data[] = $newEntity;
		}

		return $data;
	}

	function getStoreExpenseDataForNation($Day, $Month, $Year, $interval, $unsetArray)
	{

		$data = array();
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);
		$arr = null;
		$varstr = "select CONCAT(MONTH(store_expenses.entry_date), ' ', YEAR(store_expenses.entry_date)) as month_year, store_expenses.store_id, store_expenses.entry_date, store_expenses.expense_type, store_expenses.total_cost
					From store_expenses Where store_expenses.entry_date >= '" . $current_date_sql . "' AND  store_expenses.entry_date < DATE_ADD('" . $current_date_sql . "',INTERVAL " . $interval . ") and store_expenses.is_deleted = 0 and store_expenses.expense_type in ('FUNDRAISER_DOLLARS', 'ESCRIP_PAYMENTS','SALES_ADJUSTMENTS') order by entry_date, id DESC";
		$store_expenses = DAO_CFactory::create("store_expenses");
		$store_expenses->query($varstr);

		while ($store_expenses->fetch())
		{

			$arr = $store_expenses->toArray();

			if ($arr['expense_type'] != 'SALES_ADJUSTMENTS')
			{
				$arr['total_cost'] = $arr['total_cost'] * -1;
			}

			if (isset($data[$store_expenses->month_year][$store_expenses->store_id][$store_expenses->expense_type]))
			{
				$data[$store_expenses->month_year][$store_expenses->store_id][$store_expenses->expense_type] += $store_expenses->total_cost;
			}
			else
			{
				$data[$store_expenses->month_year][$store_expenses->store_id][$store_expenses->expense_type] = $store_expenses->total_cost;
			}
		}

		return $data;
	}

	function mergeData($programdiscounts, $sessionData, $adjustments, $unsetArray)
	{
		$uber_array = array_merge($sessionData, $adjustments);
		uasort($uber_array, 'sessionTimeSort');

		foreach ($uber_array as $k => &$v)
		{
			$v['session_start'] = PHPExcel_Shared_Date::stringToExcel($v['session_start']);
		}

		return $uber_array;
	}

	function mergeDataForNation($programdiscounts, &$sessionData, $adjustments, $unsetArray)
	{
		foreach ($sessionData as &$row)
		{
			if (isset($adjustments[$row['month_year']][$row['store_id']]))
			{
				foreach ($adjustments[$row['month_year']][$row['store_id']] as $thisAdjustment)
				{
					$row['sales_adjustments'] += $thisAdjustment;
				}
			}
		}

		return $sessionData;
	}

	function postProcess(&$rows, $month, $store, $menu_id = false)
	{
		foreach ($rows as &$data)
		{
			if ($data['session_type'] != 'Adjustment')
			{
				$data['subtotal_program_discounts'] = $data['referral_reward_direct'] + $data['referral_reward_iaf'] +
				(isset($data['referral_reward_taste']) ? $data['referral_reward_taste'] : 0) +
				$data['certs_voucher'] + $data['certs_donated'] + COrders::std_round($data['certs_scrip'] * .12) + $data['fundraiser_value']
				+ (isset($data['ltd_menu_item_value_2']) ? $data['ltd_menu_item_value_2'] : 0);


				$data['subtotal_all_items'] = $data['subtotal_all_items'] + $data['subtotal_program_discounts'];
				$data['certs_scrip'] = COrders::std_round($data['certs_scrip'] * .12);
			}
			else
			{
				$data['subtotal_all_items'] = $data['total_cost'];
			}

			$revenueObj = DAO_CFactory::create('revenue_event');

			if ($menu_id)
			{

				$revenueObj->query("select s.id, s.session_start, SUM(re.amount) as totes, re.positive_affected_month from revenue_event re
					join session s on s.id = re.final_session_id and s.id = {$data['session_again']}
					where re.store_id = $store and re.menu_id = $menu_id and re.is_deleted = 0 and re.order_id > 0 and re.event_type <> 'RESCHEDULED'
					group by re.final_session_id
					order by session_start");

			}
			else
			{
				$revenueObj->query("select s.id, s.session_start, SUM(if(re.negative_affected_month = '$month', re.amount * -1,  re.amount)) as totes, re.positive_affected_month from revenue_event re
					join session s on s.id = re.final_session_id and s.id = {$data['session_again']}
					where re.store_id = $store and (re.positive_affected_month = '$month' or re.negative_affected_month = '$month' ) and re.is_deleted = 0 and re.order_id > 0
					group by re.final_session_id
					order by session_start");
			}

			$revenueObj->fetch();

			$data['session_again'] = $revenueObj->totes;
			$data['TBD'] = $revenueObj->totes - $data['subtotal_all_items'];



		}
	}

	function postProcessForNation(&$rows)
	{
		$Stores = $this->getStoreArray();

		foreach ($rows as &$data)
		{
			$monthYear = $data['month_year'];
			$parts = explode(" ", $monthYear);
			//$monthYear = str_replace( " ", "/", $monthYear);
			$monthYear = $parts[1] . "/" . $parts[0];

			$data['month_year'] = $monthYear;

			$data['subtotal_program_discounts'] = $data['sales_adjustments'] + $data['referral_reward_direct'] + $data['referral_reward_iaf'] +
			    (isset($data['referral_reward_taste']) ? $data['referral_reward_taste'] : 0) +
				$data['certs_voucher'] + $data['certs_donated'] + COrders::std_round($data['certs_scrip'] * .12) + $data['fundraiser_value']
				+ (isset($data['ltd_menu_item_value_2']) ? $data['ltd_menu_item_value_2'] : 0);

			$data['subtotal_all_items'] = $data['subtotal_all_items'] + $data['subtotal_program_discounts'];
			$data['certs_scrip'] = COrders::std_round($data['certs_scrip'] * .12);

			$data['store_id'] = $Stores[$data['store_id']]['state'] . "," . $Stores[$data['store_id']]['name'] . " (" . $Stores[$data['store_id']]['hoid'] . ")";
		}
	}

	function getStoreArray()
	{
		$retVal = array();
		$stores = DAO_CFactory::create('store');
		$stores->query("select id, home_office_id, store_name, state_id from store where is_deleted = 0");
		while ($stores->fetch())
		{
			$retVal[$stores->id] = array(
				'name' => $stores->store_name,
				'hoid' => $stores->home_office_id,
				'state' => $stores->state_id
			);
		}

		return $retVal;
	}
}

?>