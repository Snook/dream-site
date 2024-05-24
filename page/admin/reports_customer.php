<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once("phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');

function myDateCompare($a, $b)
{
	$aTime = strtotime($a['date']);
	$bTime = strtotime($b['date']);

	if ($aTime == $bTime)
	{
		return 0;
	}

	return ($aTime > $bTime) ? -1 : 1;
}

function myToExcelDataConversion($dateTime)
{
	return PHPExcel_Shared_Date::stringToExcel($dateTime);
}

define("EXCEL_ROW_LIMIT", 300);
define("EXCEL_ORDER_LIMIT", 25);

class page_admin_reports_customer extends CPageAdminOnly
{
	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		//$Owner = DAO_CFactory::create('user_to_franchise');
		//$Owner->user_id = CUser::getCurrentUser()->id;
		//if ( !$Owner->find(true) )
		//	throw new Exception('not a franchise owner, or store not found for current user');
		//$this->_franchise_id = $Owner->franchise_id;
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		ini_set('memory_limit', '-1');
		set_time_limit(3600 * 24);

		$store = null;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;
		$Form->Bootstrap = true;
		$total_count = 0;
		$no_results = false;
		$sessions_attended = 1;
		// $filterToDreamRewards = false;  // NO LONGER NEEDED, drop down
		$filterCustomerType = "all";

		if ($this->currentStore)
		{
			//fadmins
			$store = $this->currentStore;
		}
		else
		{ //site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$Form->DefaultValues['store'] = array_key_exists('store', $_GET) ? $_GET['store'] : null;

			if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING)
			{

				$Form->addElement(array(
					CForm::type => CForm::AdminStoreDropDown,
					CForm::onChangeSubmit => true,
					CForm::allowAllOption => true,
					CForm::showInactiveStores => true,
					CForm::name => 'store'
				));
			}
			else
			{

				$Form->addElement(array(
					CForm::type => CForm::AdminStoreDropDown,
					CForm::onChangeSubmit => true,
					CForm::allowAllOption => false,
					CForm::showInactiveStores => true,
					CForm::name => 'store'
				));
			}

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
			CForm::addOnClick => true,
			CForm::value => 'Run Report'
		));

		// NO LONGER NEEDED, ADDED TO MENU DROP DOWN.  lmh
		//$Form->AddElement(array(CForm::type => CForm::CheckBox, CForm::name => "filterToDreamRewards"));

		//$filterToDreamRewards = $Form->Value('filterToDreamRewards');

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

		$storeObj = DAO_CFactory::create('store');

		if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING && $store == 'all')
		{
			$storeObj->supports_plate_points = 1;
			$storeObj->supports_corporate_crate = 1;
		}
		else
		{
			$storeObj->id = $store;
			$storeObj->find(true);
		}

		$filterOption = array(
			"all" => "Show All Customer Types",
			"reward" => "Loyalty Program Customers"
		);
		if ($storeObj->supports_corporate_crate)
		{
			$filterOption['corp_crate'] = "Corporate Crate Customers";
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $filterOption,
			CForm::default_value => 'all',
			CForm::name => 'customer_type_filter'
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

		if (isset ($_REQUEST["customer_type_filter"]))
		{
			$filterCustomerType = $_REQUEST["customer_type_filter"];
		}

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

		if (isset ($_REQUEST["day"]) && isset ($_REQUEST["month"]) && isset ($_REQUEST["year"]) && isset ($_REQUEST["duration"]))
		{
			$day = $_REQUEST["day"];
			$month = $_REQUEST["month"];
			$year = $_REQUEST["year"];
			$duration = $_REQUEST["duration"];
		}

		if ($Form->value('report_submit'))
		{
			$_REQUEST["export"] = "xlsx";
			$_GET["export"] = "xlsx";
		}

		if (isset ($_REQUEST["export"]))
		{

			//	if (isset($_REQUEST['DRFilter']) and $_REQUEST['DRFilter'] == 'true')
			//		$filterToDreamRewards = TRUE;

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

				$month = $_REQUEST["month_popup"];
				$month++;
				$year = $_REQUEST["year_field_001"];

				if ($Form->value('menu_or_calendar') == 'menu')
				{
					// menu month
					$anchorDay = date("Y-m-01", mktime(0, 0, 0, $month, 1, $year));
					list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
					$start_date = strtotime($menu_start_date);
					$year = date("Y", $start_date);
					$month = date("n", $start_date);
					$day = date("j", $start_date);

					$duration = $interval . " DAY";
				}
				else
				{

					// process for a given month
					$day = "01";
					$duration = '1 MONTH';
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

			$sectionSwitches = array();

			$sectionSwitches['phys_add'] = !empty($_POST['drsw_phys_add']);
			$sectionSwitches['contact_info'] = !empty($_POST['drsw_contact_info']);
			$sectionSwitches['dr_info'] = !empty($_POST['drsw_dr_info']);
			$sectionSwitches['add_user_info'] = !empty($_POST['drsw_add_user_info']);
			$sectionSwitches['serving_count'] = !empty($_POST['drsw_serving_count']);
			$sectionSwitches['total_item_count'] = !empty($_POST['drsw_total_item_count']);
			$sectionSwitches['core_menu_item_count'] = !empty($_POST['drsw_core_menu_item_count']);
			$sectionSwitches['rewards_level'] = !empty($_POST['drsw_rewards_level']);
			$sectionSwitches['order_id'] = !empty($_POST['drsw_order_id']);
			$sectionSwitches['no_show'] = !empty($_POST['drsw_no_show']);
			$sectionSwitches['ticket_amount'] = !empty($_POST['drsw_ticket_amount']);
			$sectionSwitches['coupon_code'] = !empty($_POST['drsw_coupon_code']);
			$sectionSwitches['order_time'] = !empty($_POST['drsw_order_time']);
			$sectionSwitches['order_type'] = !empty($_POST['drsw_order_type']);
			$sectionSwitches['in_store_status'] = !empty($_POST['drsw_in_store_status']);
			$sectionSwitches['platepoints_consumed'] = !empty($_POST['drsw_platepoints_consumed']);

			$defeatRepeats = !empty($_POST['drsw_suppress_repeating_columns']);

			if (isset($_REQUEST['Filter']))
			{
				$filterCustomerType = $_REQUEST['Filter'];
			}

			$rows = $this->findCustomers($store, $sessions_attended, $day, $month, $year, $duration, $filterCustomerType, $sectionSwitches, $storeObj->supports_plate_points, $storeObj->supports_corporate_crate, $defeatRepeats);
			$numRows = count($rows);

			if ($numRows > EXCEL_ROW_LIMIT || $sessions_attended > EXCEL_ORDER_LIMIT)
			{
				$_REQUEST['export'] = 'csv';
				$_GET['export'] = 'csv';
			}

			CLog::RecordReport("Customer (xlsx export)", "Rows:$numRows ~ Store: $store ~ Day: $day ~ Month: $month ~ Year: $year ~ Duration: $duration ~ filter: $filterCustomerType");

			$columnDescs = array();

			if ($numRows > 0)
			{

				if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING && $store == "all")
				{
					$labels = array(
						"Store",
						"HOID",
						"Store State",
						"ID",
						"Last Name",
						"First Name",
						"Primary Email",
						"Active Account"
					);

					$columnDescs['A'] = array('align' => 'left');
					$columnDescs['B'] = array('align' => 'center');
					$columnDescs['C'] = array('align' => 'left');
					$columnDescs['D'] = array('align' => 'center');
					$columnDescs['E'] = array('align' => 'left');
					$columnDescs['F'] = array('align' => 'left');
					$columnDescs['G'] = array(
						'align' => 'left',
						'width' => 'auto'
					);
					$columnDescs['H'] = array('align' => 'center');

					$col = 'I';
				}
				else
				{
					$labels = array(
						"ID",
						"Last Name",
						"First Name",
						"Primary Email",
						"Active Account",
						"Current Home Store"
					);

					$columnDescs['A'] = array('align' => 'center');
					$columnDescs['B'] = array('align' => 'left');
					$columnDescs['C'] = array('align' => 'left');
					$columnDescs['D'] = array(
						'align' => 'left',
						'width' => 'auto'
					);
					$columnDescs['E'] = array('align' => 'center');
					$columnDescs['F'] = array(
						'align' => 'left',
						'width' => 'auto'
					);

					$col = 'G';
				}

				$colSecondChar = '';
				$thirdSecondChar = '';

				if ($sectionSwitches['contact_info'])
				{
					$labels = array_merge($labels, array(
						"Primary Telephone",
						"Primary Telephone Type",
						"Primary Call Time",
						"Secondary Telephone",
						"Secondary Telephone Type",
						"Secondary Call Time",
						"SMS Message Opt-In"
					));

					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
				}

				if ($sectionSwitches['phys_add'])
				{
					$labels = array_merge($labels, array(
						"Address1",
						"Address2",
						"City",
						"State",
						"Postal Code"
					));
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
						'align' => 'center',
						'type' => 'text'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
				}

				if ($sectionSwitches['dr_info'])
				{
					$labels = array_merge($labels, array("Loyalty Status"));

					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
						'align' => 'center',
						'width' => 'auto'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					if ($storeObj->supports_plate_points)
					{
						$labels = array_merge($labels, array("PLATEPOINTs Status"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
				}

				if ($sectionSwitches['add_user_info'])
				{
					$labels = array_merge($labels, array(
						"Lifetime visits",
						"First Session",
						"Account created",
						"Preferred User",
						"User Type"
					));
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
						'align' => 'left',
						'width' => 'auto',
						'type' => 'datetime'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
						'align' => 'left',
						'width' => 'auto',
						'type' => 'datetime'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);
					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					if ($storeObj->supports_corporate_crate)
					{
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'left');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
						$labels = array_merge($labels, array("Corporate Crate"));
					}
				}

				$labels = array_merge($labels, array("Orders Made"));
				$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);

				$colcount = count($labels) + $sessions_attended;

				if ($sectionSwitches['order_type'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['order_time'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['serving_count'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['total_item_count'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['core_menu_item_count'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['rewards_level'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['order_id'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['no_show'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['ticket_amount'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['coupon_code'])
				{
					$colcount += $sessions_attended;
				}
				if ($sectionSwitches['in_store_status'])
				{
					$colcount += $sessions_attended;
				}

				if ($sectionSwitches['platepoints_consumed'])
				{
					$colcount += $sessions_attended;
				}

				$numRepeats = $sessions_attended;

				if ($defeatRepeats)
				{
					$numRepeats = 1;
				}

				for ($i = 0; $i < $numRepeats; $i++)
				{

					$labels = array_merge($labels, array("Session Attended"));

					$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
						'align' => 'left',
						'width' => 'auto',
						'type' => 'datetime',
						'decor' => 'left_border'
					);
					incrementColumn($thirdSecondChar, $colSecondChar, $col);

					if ($sectionSwitches['order_type'])
					{
						$labels = array_merge($labels, array("Order Type"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
							'align' => 'center',
							'width' => 'auto'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['order_time'])
					{
						$labels = array_merge($labels, array("Time Order Placed"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
							'align' => 'left',
							'width' => 'auto',
							'type' => 'datetime'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['serving_count'])
					{
						$labels = array_merge($labels, array("Serving Count"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['total_item_count'])
					{
						$labels = array_merge($labels, array("Total Item Count"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['core_menu_item_count'])
					{
						$labels = array_merge($labels, array("Core Item Count"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['rewards_level'])
					{
						if ($storeObj->supports_plate_points)
						{
							$labels = array_merge($labels, array("Loyalty Info"));
						}
						else
						{
							$labels = array_merge($labels, array("Rewards Level"));
						}

						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['order_id'])
					{
						$labels = array_merge($labels, array("Order ID"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['no_show'])
					{
						$labels = array_merge($labels, array("No Show"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['ticket_amount'])
					{
						$labels = array_merge($labels, array("Ticket Price"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
							'align' => 'center',
							'type' => 'currency'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['coupon_code'])
					{
						$labels = array_merge($labels, array("Coupon Code"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['in_store_status'])
					{
						$labels = array_merge($labels, array("In-Store Sign up"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array('align' => 'center');
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}

					if ($sectionSwitches['platepoints_consumed'])
					{
						$labels = array_merge($labels, array("PLATEPOINTS Dinner Dollars Consumed"));
						$columnDescs[$thirdSecondChar . $colSecondChar . $col] = array(
							'align' => 'center',
							'type' => 'currency'
						);
						incrementColumn($thirdSecondChar, $colSecondChar, $col);
					}
				}

				$RangeStr = $month . "_" . $day . "_" . $year . "_" . $duration;

				if ($store == 'all')
				{
					$tpl->assign('file_name', makeTitle("Order History", "All_Stores", $RangeStr));
				}
				else
				{
					$tpl->assign('file_name', makeTitle("Order History", $storeObj, $RangeStr));
				}

				$tpl->assign('labels', $labels);
				$tpl->assign('rows', $rows);
				$tpl->assign('rowcount', count($rows));
				$tpl->assign('col_descriptions', $columnDescs);
			}
			else
			{
				unset($_REQUEST['export']);
				unset($_GET['export']);
				$no_results = true;
			}
		}

		$formArray = $Form->render();

		$tpl->assign('no_results', $no_results);
		$tpl->assign('report_type_to_run', $report_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'Customer Report');
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

	function getLoyaltystatusString(&$thisRow, $supportsPlatePoints)
	{

		$newStatus = "";

		if ($thisRow['dream_reward_status'] == 1 || $thisRow['dream_reward_status'] == 3 || $thisRow['dream_reward_status'] == 5)
		{

			if ($thisRow['dream_rewards_version'] == 3)
			{

				$lifetimePoints = CPointsUserHistory::getCurrentPointsLevel($thisRow['id']);

				list($curLevel, $nextLevel) = CPointsUserHistory::getLevelDetailsByPoints($lifetimePoints);

				$newStatus = $curLevel['title'];
				if ($thisRow['dream_reward_status'] == 5)
				{
					$newStatus = "On Hold (" . $curLevel['title'] . ")";
				}

				if ($supportsPlatePoints)
				{
					$thisRow['points_status'] = $lifetimePoints;
				}
			}
			else
			{
				$newStatus = "DR " . $thisRow['dream_reward_level'];
				if ($thisRow['dr_downgraded_order_count'] > 0)
				{
					$newStatus . " (downgrade: {$thisRow['dr_downgraded_order_count']} remain)";
				}
			}
		}
		else
		{
			if ($thisRow['dream_reward_status'] == 2 && $thisRow['dream_rewards_version'] == 2)
			{
				$newStatus = "DeAct|Last Level: " . $thisRow['dream_reward_level'];
			}
			else
			{
				$newStatus = "No Program";
			}
		}

		unset($thisRow['dream_reward_level']);
		unset($thisRow['dream_rewards_version']);
		unset($thisRow['dr_downgraded_order_count']);

		$thisRow['dream_reward_status'] = $newStatus;
	}

	function getLoyaltyOrderRewardString(&$thisRow)
	{
		$dateSortedArray = array();

		$currentPointsLevel = false;

		$Orders = DAO_CFactory::create('orders');
		$Orders->query("select o.id, o.user_id, o.subtotal_menu_items, o.subtotal_home_store_markup, o.subtotal_products, o.subtotal_service_fee, o.misc_food_subtotal,
				o.is_in_plate_points_program, o.dream_rewards_level, o.in_store_order, puh.points_allocated from orders o
				join booking b on b.order_id = o.id and b.status = 'ACTIVE'
				join session s on s.id = b.session_id
				left join points_user_history puh on puh.user_id = o.user_id and puh.order_id = o.id and puh.event_type = 'ORDER_CONFIRMED' AND puh.is_deleted = '0'
				where o.id in ({$thisRow['order_ids']})  group by o.id order by s.session_start asc");

		while ($Orders->fetch())
		{
			if ($Orders->is_in_plate_points_program && empty($Orders->points_allocated))
			{
				if (!$currentPointsLevel)
				{
					$currentPointsLevel = CPointsUserHistory::getCurrentPointsLevel($Orders->user_id);
				}

				$thisOrderPoints = CPointsUserHistory::getPointsForOrder($currentPointsLevel, $Orders);
				$currentPointsLevel += $thisOrderPoints;
				$Orders->points_allocated = $thisOrderPoints;
			}

			$dateSortedArray[$Orders->id] = clone($Orders);
		}

		$originalOrderArray = explode(",", $thisRow['order_ids']);
		$originalStatusArray = explode(",", $thisRow['rewards_level']);

		$counter = 0;
		foreach ($originalOrderArray as $thisOrderID)
		{

			$thisOrder = $dateSortedArray[$thisOrderID];

			if ($thisOrder->dream_rewards_level > 0)
			{
				$originalStatusArray[$counter] = "DR " . $thisOrder->dream_rewards_level;
			}
			else if ($thisOrder->is_in_plate_points_program)
			{

				if ($thisOrder->points_allocated > 0)
				{
					// this order was conmfirmed
					$originalStatusArray[$counter] = $thisOrder->points_allocated . " Pts.";
				}
			}

			$counter++;
		}

		$thisRow['rewards_level'] = implode(",", $originalStatusArray);
	}

	function findCustomers($store_id, &$sessions_attended, $Day, $Month, $Year, $Interval, $filterCustomerType, $sectionSwitches, $supportsPlatePoints, $supportsCorporateCrate, $defeatRepeats = false)
	{

		if ($supportsCorporateCrate)
		{
			$clientData = CCorporateCrateClient::getArrayOfAllClients();
		}

		$booking = DAO_CFactory::create("booking");

		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$booking->query('SET group_concat_max_len = 100000;');

		if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING && $store_id == "all")
		{
			$selectStr = "Select st.store_name, st.home_office_id, st.state_id, u.id, u.lastname, u.firstname, u.primary_email, IF(u.is_deleted>0, 'no', 'yes') as deleted ";
			$colcount = 8;
		}
		else
		{
			$selectStr = "Select u.id, u.lastname, u.firstname, u.primary_email, IF(u.is_deleted>0, 'no', 'yes') as deleted, st.store_name ";
			$colcount = 6;
		}

		if ($sectionSwitches['contact_info'])
		{
			$selectStr .= ", u.telephone_1, u.telephone_1_type, u.telephone_1_call_time, u.telephone_2, u.telephone_2_type, u.telephone_2_call_time, user_pref_text.pvalue as text_message_opt_in ";
			$colcount += 6;
		}

		if ($sectionSwitches['phys_add'])
		{
			$selectStr .= ", a.address_line1, a.address_line2, a.city, a.state_id as phy_state_id, a.postal_code ";
			$colcount += 5;
		}

		if ($sectionSwitches['dr_info'])
		{
			$selectStr .= ",  u.dream_reward_status, if(u.dream_reward_level = 0, '', u.dream_reward_level) as dream_reward_level, u.dr_downgraded_order_count, u.dream_rewards_version ";
			$colcount += 1; // note 3 of these columns are removed by getLoyaltystatusString

			if ($supportsPlatePoints)
			{
				$selectStr .= ", '' as points_status ";
				$colcount += 1;
				// placeholder for lifetime points to be retrieved later
			}
		}

		if ($sectionSwitches['add_user_info'])
		{
			$selectStr .= ",  ud.visit_count as life_time_visits, ud.first_session, u.timestamp_created as Customer_since, IF(up.id is null, 'no', 'yes' ) as is_preferred, u.user_type ";
			$colcount += 5;

			if ($supportsCorporateCrate)
			{
				$selectStr .= ", u.secondary_email ";
				$colcount++;
			}
		}

		$concat_ordering_clause = "";
		if ($defeatRepeats)
		{
			$concat_ordering_clause = "order by s.session_start desc";
		}

		$selectStr .= ", Count(o.id) AS orders_made, GROUP_CONCAT(s.session_start $concat_ordering_clause) AS sessions_attended ";
		$colcount += 2;

		if ($sectionSwitches['order_type'])
		{
			$selectStr .= ", GROUP_CONCAT(o.type_of_order $concat_ordering_clause) as order_type ";
			$colcount++;
		}

		if ($sectionSwitches['order_time'])
		{
			$selectStr .= ", GROUP_CONCAT(o.timestamp_created $concat_ordering_clause) as order_time ";
			$colcount++;
		}

		if ($sectionSwitches['serving_count'])
		{
			$selectStr .= ", GROUP_CONCAT(IF (o.servings_total_count is not null, o.servings_total_count, 0) $concat_ordering_clause) as numServings ";
			$colcount++;
		}

		if ($sectionSwitches['total_item_count'])
		{
			$selectStr .= ", GROUP_CONCAT(IF (o.menu_items_total_count is not null, o.menu_items_total_count, 0) $concat_ordering_clause) as numTotalItems ";
			$colcount++;
		}

		if ($sectionSwitches['core_menu_item_count'])
		{
			$selectStr .= ", GROUP_CONCAT(IF (o.menu_items_core_total_count is not null, o.menu_items_core_total_count, 0) $concat_ordering_clause) as numCoreItems ";
			$colcount++;
		}

		if ($sectionSwitches['rewards_level'])
		{
			$selectStr .= ", GROUP_CONCAT(if(o.dream_rewards_level = 0, '', o.dream_rewards_level) $concat_ordering_clause) AS rewards_level ";
			$colcount++;
		}

		if ($sectionSwitches['order_id'])
		{
			$colcount++;
		}

		// Always get the Ids but suppress them later if required, they are needed for determining PLATEPOINTS values
		$selectStr .= ", GROUP_CONCAT(o.id) AS order_ids ";

		if ($sectionSwitches['no_show'])
		{
			$selectStr .= ", GROUP_CONCAT(if(b.no_show = 0, ' ', 'No Show') $concat_ordering_clause) AS no_shows ";
			$colcount++;
		}

		if ($sectionSwitches['ticket_amount'])
		{
			$selectStr .= ", GROUP_CONCAT(o.grand_total $concat_ordering_clause) AS ticket_price ";
			$colcount++;
		}

		if ($sectionSwitches['coupon_code'])
		{
			$selectStr .= ", GROUP_CONCAT(ifnull(cc.coupon_code, ' ') $concat_ordering_clause) AS coupon_code ";
			$colcount++;
		}

		if ($sectionSwitches['in_store_status'])
		{
			$selectStr .= ", GROUP_CONCAT(if(o.in_store_order = 1, 'yes', 'no') $concat_ordering_clause) as in_store_status ";
			$colcount++;
		}

		if ($sectionSwitches['platepoints_consumed'])
		{
			$selectStr .= ", GROUP_CONCAT(o.points_discount_total $concat_ordering_clause) as platepoints_consumed ";
			$colcount++;
		}

		if ($sectionSwitches['order_type'])
		{
			$selectStr .= ", GROUP_CONCAT(s.session_type $concat_ordering_clause) as session_type ";
			$selectStr .= ", GROUP_CONCAT(COALESCE(s.session_type_subtype,'') $concat_ordering_clause) as session_type_subtype ";
		}

		$fromStr = " From booking b
							Inner Join orders o ON b.order_id = o.id
							Inner Join session s ON b.session_id = s.id
							Inner Join user u ON b.user_id = u.id";

		$fromStr .= " left Join store st on st.id = u.home_store_id ";

		if ($sectionSwitches['phys_add'])
		{
			$fromStr .= " Left Join address a ON u.id = a.user_id and a.location_type = 'BILLING' and a.is_deleted = 0 ";
		}

		if ($sectionSwitches['contact_info'])
		{
			$fromStr .= " left join user_preferences as user_pref_text on user_pref_text.user_id = u.id and user_pref_text.pkey = 'TEXT_MESSAGE_OPT_IN' ";
		}

		if ($sectionSwitches['add_user_info'])
		{

			if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING && $store_id == "all")
			{
				$fromStr .= " left join (select user_id, id, is_deleted from user_preferred group by user_id) up on up.user_id = u.id and up.is_deleted = 0
						left Join user_digest ud on ud.user_id = u.id ";
			}
			else
			{
				$fromStr .= " left join (select user_id, store_id, id, is_deleted from user_preferred) up on up.user_id = u.id and up.store_id = $store_id and up.is_deleted = 0
								left Join user_digest ud on ud.user_id = u.id ";
			}
		}

		if ($sectionSwitches['coupon_code'])
		{
			$fromStr .= " left join coupon_code cc on cc.id = o.coupon_code_id ";
		}

		$CrateClause = "";
		if ($filterCustomerType == "corp_crate")
		{
			$CrateClause = " and (not isnull(u.secondary_email) and u.secondary_email <> '') ";
		}

		if (defined('ALLOW_SITE_WIDE_REPORTING') && ALLOW_SITE_WIDE_REPORTING && $store_id == "all")
		{

			$whereStr = " s.session_start >= '$current_date_sql' and s.session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval ) and
							 b.status = 'ACTIVE' and b.is_deleted = 0 AND s.is_deleted = 0 and s.session_publish_state != 'SAVED' $CrateClause group by u.id order by u.lastname, u.firstname";
		}
		else
		{
			$whereStr = " o.store_id = $store_id  and s.session_start >= '$current_date_sql' and s.session_start <  DATE_ADD('$current_date_sql', INTERVAL $Interval ) and
						 b.status = 'ACTIVE' and b.is_deleted = 0 AND s.is_deleted = 0 and s.session_publish_state != 'SAVED' $CrateClause group by u.id order by u.lastname, u.firstname";
		}

		if ($filterCustomerType == "reward")
		{
			$DR_Clause = "(o.dream_rewards_level > 0 or o.is_in_plate_points_program = 1) and ";
		}
		else
		{
			$DR_Clause = "";
		}

		if ($filterCustomerType == "reward")
		{
			$DR_Clause = "(o.dream_rewards_level > 0 or o.is_in_plate_points_program = 1) and ";
		}
		else
		{
			$DR_Clause = "";
		}

		$querystr = $selectStr . $fromStr . " Where " . $DR_Clause . $whereStr;
		$booking->query($querystr);

		$exporting_as_excel = true;
		if ($booking->N > EXCEL_ROW_LIMIT)
		{
			$exporting_as_excel = false;
		}

		$rows = array();
		$master_array = array();
		$count = 0;

		while ($booking->fetch())
		{
			$master_array[] = $booking->toArray();
			if ($booking->orders_made > $sessions_attended)
			{
				$sessions_attended = $booking->orders_made;
			}
		}

		if ($sessions_attended > EXCEL_ORDER_LIMIT)
		{
			$exporting_as_excel = false;
		}

		foreach ($master_array as $tarray)
		{
			if ($supportsCorporateCrate && !empty($tarray['secondary_email']))
			{
				$domain = array_pop(explode("@", $tarray['secondary_email']));

				if (isset($clientData[$domain]))
				{
					$tarray['secondary_email'] = $clientData[$domain]['company_name'];
				}
				else
				{
					$tarray['secondary_email'] = "-";
				}
			}
			else
			{
				$tarray['secondary_email'] = "-";
			}

			if ($sectionSwitches['dr_info'])
			{
				$this->getLoyaltystatusString($tarray, $supportsPlatePoints);
			}

			if ($sectionSwitches['rewards_level'] && $supportsPlatePoints)
			{
				$this->getLoyaltyOrderRewardString($tarray);
			}

			if (empty($sectionSwitches['order_id']))
			{
				unset($tarray['order_ids']);
			}

			// need session_type but just temporaily -- AND SUBTYPES
			// put it in a var and trim it away
			if ($sectionSwitches['order_type'])
			{
				$sessionTypes = $tarray['session_type'];
				$sessionSubTypes = $tarray['session_type_subtype'];
			}

			array_splice($tarray, $colcount, count($tarray));

			$tempDate = $tarray['sessions_attended'];
			$tempDateArray = explode(",", $tarray['sessions_attended']);

			$newSessionStr = "";

			if (!$defeatRepeats)
			{

				if (count($tempDateArray) > 0)
				{

					$sep = explode(",", $tempDate);

					if ($sectionSwitches['order_type'])
					{
						$tempOrderTypes = $tarray['order_type'];
						$sepOrderTypes = explode(",", $tempOrderTypes);
						$sepSessionTypes = explode(",", $sessionTypes);
						$sepSessionSubTypes = explode(",", $sessionSubTypes);
					}

					if ($sectionSwitches['order_time'])
					{
						$tempOrderTimes = $tarray['order_time'];
						$sepOrderTimes = explode(",", $tempOrderTimes);
					}

					if ($sectionSwitches['serving_count'])
					{
						$tempServings = $tarray['numServings'];
						$sepServ = explode(",", $tempServings);
					}

					if ($sectionSwitches['total_item_count'])
					{
						$tempTotalItems = $tarray['numTotalItems'];
						$sepTotalItems = explode(",", $tempTotalItems);
					}

					if ($sectionSwitches['core_menu_item_count'])
					{
						$tempCoreItems = $tarray['numCoreItems'];
						$sepCoreItem = explode(",", $tempCoreItems);
					}

					if ($sectionSwitches['rewards_level'])
					{
						$tempLevel = $tarray['rewards_level'];
						$sepDR = explode(",", $tempLevel);
					}

					if ($sectionSwitches['order_id'])
					{
						$tempOrderIds = $tarray['order_ids'];
						$sepIds = explode(",", $tempOrderIds);
					}

					if ($sectionSwitches['no_show'])
					{
						$tempNoShows = $tarray['no_shows'];
						$sepNoShows = explode(",", $tempNoShows);
					}

					if ($sectionSwitches['ticket_amount'])
					{
						$tempTicketPrice = $tarray['ticket_price'];
						$sepTicketPrice = explode(",", $tempTicketPrice);
					}

					if ($sectionSwitches['coupon_code'])
					{
						$tempCoupons = $tarray['coupon_code'];
						$sepCoupons = explode(",", $tempCoupons);
					}

					if ($sectionSwitches['in_store_status'])
					{
						$tempInStore = $tarray['in_store_status'];
						$sepInStore = explode(",", $tempInStore);
					}

					if ($sectionSwitches['platepoints_consumed'])
					{
						$tempPPConsumed = $tarray['platepoints_consumed'];
						$sepPPConsumed = explode(",", $tempPPConsumed);
					}

					$countarr = count($sep);

					if ($countarr > 1)
					{
						$counter = 0;
						$tempArr = array();
						for ($var = 0; $var < $countarr; $var++)
						{
							$ts = $sep[$var];

							if (isset($ts) && $ts != null && $ts != "")
							{

								//$convertTimeStamp = CSessionReports::reformatTime ($sep[$var]);
								$tempArr[$counter]['date'] = $sep[$var]; //$convertTimeStamp;

								if ($sectionSwitches['order_type'])
								{

									if ($sepSessionSubTypes[$counter] == 'DELIVERY')
									{
										$sepSessionTypes[$counter] = 'DELIVERY';
									}

									if ($sepSessionSubTypes[$counter] == 'WALK_IN')
									{
										$sepSessionTypes[$counter] = 'WALK-IN';
									}

									if ($sepSessionSubTypes[$counter] == 'REMOTE_PICKUP' || $sepSessionSubTypes[$counter] == 'REMOTE_PICKUP_PRIVATE')
									{
										$sepSessionTypes[$counter] = 'REMOTE_PICKUP';
									}

									if ($sepSessionTypes[$counter] == 'SPECIAL_EVENT')
									{
										if ($sepOrderTypes[$counter] == 'INTRO')
										{
											$tempArr[$counter]['order_type'] = 'PICK UP - STARTER PACK';
										}
										else
										{
											$tempArr[$counter]['order_type'] = 'PICK UP';
										}
									}
									else if ($sepSessionTypes[$counter] == 'DELIVERY')
									{
										if ($sepOrderTypes[$counter] == 'INTRO')
										{
											$tempArr[$counter]['order_type'] = 'DELIVERY - STARTER PACK';
										}
										else
										{
											$tempArr[$counter]['order_type'] = 'DELIVERY';
										}
									}
									else if ($sepSessionTypes[$counter] == 'WALK-IN')
									{
										if ($sepOrderTypes[$counter] == 'INTRO')
										{
											$tempArr[$counter]['order_type'] = 'WALK-IN - STARTER PACK';
										}
										else
										{
											$tempArr[$counter]['order_type'] = 'WALK-IN';
										}
									}
									else if ($sepSessionTypes[$counter] == 'REMOTE_PICKUP')
									{
										$tempArr[$counter]['order_type'] = 'PICK UP - COMM PICKUP';
									}
									else
									{
										if ($sepOrderTypes[$counter] == 'INTRO')
										{
											$sepOrderTypes[$counter] = 'STARTER PACK';
										}

										$tempArr[$counter]['order_type'] = $sepOrderTypes[$counter];
									}
								}

								if ($sectionSwitches['order_time'])
								{
									$tempArr[$counter]['order_time'] = $sepOrderTimes[$counter];
								}

								if ($sectionSwitches['serving_count'])
								{
									$tempArr[$counter]['servings'] = $sepServ[$counter];
								}

								if ($sectionSwitches['total_item_count'])
								{
									$tempArr[$counter]['total_items'] = $sepTotalItems[$counter];
								}

								if ($sectionSwitches['core_menu_item_count'])
								{
									$tempArr[$counter]['core_items'] = $sepCoreItem[$counter];
								}

								if ($sectionSwitches['rewards_level'])
								{
									$tempArr[$counter]['dr_level'] = $sepDR[$counter];
								}

								if ($sectionSwitches['order_id'])
								{
									$tempArr[$counter]['order_id'] = $sepIds[$counter];
								}

								if ($sectionSwitches['no_show'])
								{
									$tempArr[$counter]['no_show'] = $sepNoShows[$counter];
								}

								if ($sectionSwitches['ticket_amount'])
								{
									$tempArr[$counter]['ticket_price'] = $sepTicketPrice[$counter];
								}

								if ($sectionSwitches['coupon_code'])
								{
									$tempArr[$counter]['coupon_code'] = $sepCoupons[$counter];
								}

								if ($sectionSwitches['in_store_status'])
								{
									$tempArr[$counter]['in_store_status'] = $sepInStore[$counter];
								}

								if ($sectionSwitches['platepoints_consumed'])
								{
									$tempArr[$counter]['platepoints_consumed'] = $sepPPConsumed[$counter];
								}

								$counter++;
							}
						}

						usort($tempArr, 'myDateCompare');

						unset($tarray['sessions_attended']);

						if ($sectionSwitches['order_type'])
						{
							unset($tarray['order_type']);
						}

						if ($sectionSwitches['order_time'])
						{
							unset($tarray['order_time']);
						}

						if ($sectionSwitches['serving_count'])
						{
							unset($tarray['numServings']);
						}

						if ($sectionSwitches['total_item_count'])
						{
							unset($tarray['numTotalItems']);
						}

						if ($sectionSwitches['core_menu_item_count'])
						{
							unset($tarray['numCoreItems']);
						}

						if ($sectionSwitches['rewards_level'])
						{
							unset($tarray['rewards_level']);
						}

						if ($sectionSwitches['order_id'])
						{
							unset($tarray['order_ids']);
						}

						if ($sectionSwitches['no_show'])
						{
							unset($tarray['no_shows']);
						}

						if ($sectionSwitches['ticket_amount'])
						{
							unset($tarray['ticket_price']);
						}

						if ($sectionSwitches['coupon_code'])
						{
							unset($tarray['coupon_code']);
						}

						if ($sectionSwitches['in_store_status'])
						{
							unset($tarray['in_store_status']);
						}

						if ($sectionSwitches['platepoints_consumed'])
						{
							unset($tarray['platepoints_consumed']);
						}

						foreach ($tempArr as $thisItem)
						{
							/* --------------------------------------
							 *
							 *  note that I can't know if we will switch to CSV reliably
							 *  so until that is addressed I will just output text
							 *
							 */

							if ($exporting_as_excel)
							{
								$tarray[] = myToExcelDataConversion($thisItem['date']);
							}
							else
							{
								$tarray[] = CSessionReports::reformatTime($thisItem['date']);
							}

							if ($sectionSwitches['order_type'])
							{
								if ($thisItem['order_type'] == 'INTRO')
								{
									$thisItem['order_type'] = 'STARTER PACK';
								}
								$tarray[] = $thisItem['order_type'];
							}

							if ($sectionSwitches['order_time'])
							{
								if ($exporting_as_excel)
								{
									$tarray[] = myToExcelDataConversion($thisItem['order_time']);
								}
								else
								{
									$tarray[] = CSessionReports::reformatTime($thisItem['order_time']);
								}
							}

							//-------------------------------------------------------

							if ($sectionSwitches['serving_count'])
							{
								$tarray[] = $thisItem['servings'];
							}

							if ($sectionSwitches['total_item_count'])
							{
								$tarray[] = $thisItem['total_items'];
							}

							if ($sectionSwitches['core_menu_item_count'])
							{
								$tarray[] = $thisItem['core_items'];
							}

							if ($sectionSwitches['rewards_level'])
							{
								$tarray[] = $thisItem['dr_level'];
							}

							if ($sectionSwitches['order_id'])
							{
								$tarray[] = $thisItem['order_id'];
							}

							if ($sectionSwitches['no_show'])
							{
								$tarray[] = $thisItem['no_show'];
							}

							if ($sectionSwitches['ticket_amount'])
							{
								$tarray[] = $thisItem['ticket_price'];
							}

							if ($sectionSwitches['coupon_code'])
							{
								$tarray[] = $thisItem['coupon_code'];
							}

							if ($sectionSwitches['in_store_status'])
							{
								$tarray[] = $thisItem['in_store_status'];
							}

							if ($sectionSwitches['platepoints_consumed'])
							{
								$tarray[] = $thisItem['platepoints_consumed'];
							}
						}
					}
					else
					{

						if ($sectionSwitches['order_type'])
						{

							if ($sessionSubTypes == CSession::DELIVERY)
							{
								$sessionTypes = 'DELIVERY';
							}

							if ($sessionSubTypes == CSession::WALK_IN)
							{
								$sessionTypes = CSession::WALK_IN;
							}

							if ($sessionTypes == 'SPECIAL_EVENT')
							{
								if ($tarray['order_type'] == 'INTRO')
								{
									$tarray['order_type'] = 'PICK UP - STARTER PACK';
								}
								else
								{
									$tarray['order_type'] = 'PICK UP';
								}
							}
							if ($sessionTypes == 'DELIVERY')
							{
								if ($tarray['order_type'] == 'INTRO')
								{
									$tarray['order_type'] = 'DELIVERY - STARTER PACK';
								}
								else
								{
									$tarray['order_type'] = 'DELIVERY';
								}
							}
							if ($sessionTypes == CSession::WALK_IN)
							{
								if ($tarray['order_type'] == COrders::INTRO)
								{
									$tarray['order_type'] = 'WALK-IN - STARTER PACK';
								}
								else
								{
									$tarray['order_type'] = 'WALK-IN';
								}
							}
							else if ($tarray['order_type'] == 'INTRO')
							{
								$tarray['order_type'] = 'STARTER PACK';
							}
						}

						if ($exporting_as_excel)
						{
							$tarray['sessions_attended'] = myToExcelDataConversion($tempDate);

							if ($sectionSwitches['order_time'])
							{
								$tarray['order_time'] = myToExcelDataConversion($tarray['order_time']);
							}
						}
						else
						{
							$tarray['sessions_attended'] = CSessionReports::reformatTime($tempDate);

							if ($sectionSwitches['order_time'])
							{
								$tarray['order_time'] = CSessionReports::reformatTime($tarray['order_time']);
							}
						}
					}
				}

				if ($sectionSwitches['add_user_info'])
				{
					if ($exporting_as_excel)
					{
						$tarray['first_session'] = myToExcelDataConversion($tarray['first_session']);
						$tarray['Customer_since'] = myToExcelDataConversion($tarray['Customer_since']);
					}
					else
					{
						$tarray['first_session'] = CSessionReports::reformatTime($tarray['first_session']);
						$tarray['Customer_since'] = CSessionReports::reformatTime($tarray['Customer_since']);
					}
				}
			}
			else
			{
				if ($sectionSwitches['order_type'])
				{

					if ($sessionSubTypes == CSession::DELIVERY)
					{
						$sessionTypes = 'DELIVERY';
					}

					if ($sessionSubTypes == CSession::WALK_IN)
					{
						$sessionTypes = CSession::WALK_IN;
					}

					if ($sessionTypes == 'SPECIAL_EVENT')
					{
						if ($tarray['order_type'] == 'INTRO')
						{
							$tarray['order_type'] = 'PICK UP - STARTER PACK';
						}
						else
						{
							$tarray['order_type'] = 'PICK UP';
						}
					}
					if ($sessionTypes == 'DELIVERY')
					{
						if ($tarray['order_type'] == 'INTRO')
						{
							$tarray['order_type'] = 'DELIVERY - STARTER PACK';
						}
						else
						{
							$tarray['order_type'] = 'DELIVERY';
						}
					}
					if ($sessionTypes == CSession::WALK_IN)
					{
						if ($tarray['order_type'] == COrders::INTRO)
						{
							$tarray['order_type'] = 'WALK-IN - STARTER PACK';
						}
						else
						{
							$tarray['order_type'] = 'WALK-IN';
						}
					}
					else if ($tarray['order_type'] == 'INTRO')
					{
						$tarray['order_type'] = 'STARTER PACK';
					}
				}
			}

			$rows [$count++] = $tarray;
		}

		return ($rows);
	}

}

?>