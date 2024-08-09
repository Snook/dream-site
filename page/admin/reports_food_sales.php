<?php

/**
 * @author Carl Samuelson
 */
require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');
require_once('includes/DAO/BusinessObject/CStoreExpenses.php');
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');

function yLabelFormat($inNumber)
{
	return "$" . number_format($inNumber, 0);
}

function xLabelFormat($inText)
{
	return $inText;
}

function dollarFormatter($inNumber)
{
	return "$" . number_format($inNumber, 0);
}

class page_admin_reports_food_sales extends CPageAdminOnly
{
	private $show_store_selectors = false;
	private $store_name = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->show_store_selectors = true;

		$this->run();
	}

	function runSiteAdmin()
	{
		$this->show_store_selectors = true;
		$this->run();
	}

	function runFranchiseOwner()
	{

		$this->run();
	}

	function runFranchiseLead()
	{

		$this->run();
	}

	function runFranchiseManager()
	{

		$this->run();
	}

	function runEventCoordinator()
	{

		$this->run();
	}

	function runOpsLead()
	{

		$this->run();
	}

	function exportExcel($tpl, $rows, $items, $startDate, $range)
	{
		if (empty($rows))
		{

			return false;
		}

		$labels = array(
			"Recipe ID",
			"Item name",
			"Sessions",
			"Last Session",
			"User ID",
			"First Name",
			"Last Name",
			"Email",
			"Telephone 1",
			"Telephone 1 type",
			"Telephone 1 call time",
			"Telephone 2",
			"Telephone 2 type",
			"Telephone 2 call time",
			"Address line 1",
			"Address line 2",
			"City",
			"State ID",
			"Postal code",
			"# Large Ordered",
			"# Medium (3) Ordered",
			"# Medium (4) Ordered",
			"# Small Ordered",
			"Session IDs",
			"Order IDs"
		);

		$col = 'A';
		$colSecondChar = '';
		$thirdSecondChar = '';

		if ($this->show_store_selectors)
		{
			$labels = array_merge(array(
				"Home Office ID",
				"Store Name",
				"City",
				"State"
			), $labels);

			$columnDescs[$col] = array(
				'align' => 'left',
				'width' => 6
			);
			incrementColumn($thirdSecondChar, $colSecondChar, $col);

			$columnDescs[$col] = array(
				'align' => 'left',
				'width' => 20
			);
			incrementColumn($thirdSecondChar, $colSecondChar, $col);

			$columnDescs[$col] = array(
				'align' => 'left',
				'width' => 20
			);
			incrementColumn($thirdSecondChar, $colSecondChar, $col);

			$columnDescs[$col] = array(
				'align' => 'left',
				'width' => 8
			);
			incrementColumn($thirdSecondChar, $colSecondChar, $col);

		}

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 6,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 30
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 20
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 20
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 10,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 16,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 16,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		// email
		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 16,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'center',
			'width' => 14,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'center',
			'width' => 14,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 14,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 14,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 14,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 14,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 28,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 10,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 15,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 6,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 7,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 6,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 6,
		);

		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 6,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 6,
		);

		incrementColumn($thirdSecondChar, $colSecondChar, $col);
		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 10,
		);
		incrementColumn($thirdSecondChar, $colSecondChar, $col);

		$columnDescs[$col] = array(
			'align' => 'left',
			'width' => 10,
			'decor' => 'subtotal'
		);

		$numRows = count($rows);

		$tpl->assign('file_name', makeTitle("Sales_" . array_pop($items), ""));

		$tpl->assign('labels', $labels);
		$tpl->assign('rows', $rows);
		$tpl->assign('rowcount', $numRows);
		$tpl->assign('col_descriptions', $columnDescs);

		$endDate = "";
		$timeSpanStr = " - from " . CTemplate::dateTimeFormat($startDate) . " to ";
		$startDateTS = strtotime($startDate);

		$rangeParts = explode(" ", $range);
		if ($rangeParts[1] == "DAY")
		{
			$startDateTS += ($rangeParts[0] * 86400);

			$startDateTS -= 1;

			$endDate = CTemplate::dateTimeFormat($startDateTS);
		}
		else if ($rangeParts[1] == "MONTH")
		{
			$month = date("n", $startDateTS);
			$year = date("Y", $startDateTS);
			$day = date("j", $startDateTS);

			$endDateTS = mktime(0, 0, 0, $month + 1, $day, $year);

			$endDateTS -= 1;
			$endDate = CTemplate::dateTimeFormat($endDateTS);
		}
		else
		{
			CLog::Assert(false, "Illegal Range Value");
		}

		$timeSpanStr .= $endDate;

		$storeName = "";
		if (!$this->show_store_selectors)
		{
			$storeName = $this->store_name . " ";
		}

		$titleRows[] = array(
			"",
			$storeName . "Food Sales Report"
		);
		$titleRows[] = array(
			"",
			implode(",", $items) . $timeSpanStr
		);
		$titleRows[] = array(
			"",
			"Report Run On",
			date("F j, Y, g:i:a")
		);

		$tpl->assign('title_rows', $titleRows);

		$_GET['export'] = 'xlsx';
	}

	function run()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		ini_set('memory_limit', '1024M');
		set_time_limit(3600 * 1);

		$tpl->assign('show_store_selectors', $this->show_store_selectors);

		if ($this->show_store_selectors)
		{
			$tpl->assign('store_data', CStore::getStoreTreeAsNestedList(false, true));
		}
		else
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
			$storeObj = new DAO();
			$storeObj->query("select store_name from store where id = $store_id");
			$storeObj->fetch();
			$this->store_name = $storeObj->store_name;
			$tpl->assign('cur_store_id', $store_id);
		}

		if (isset($_REQUEST['export']) && $_REQUEST['export'] == 'xlsx')
		{
			$mySQLDate = $_POST['range_start'];
			$duration = $_POST['duration'];
			$items = $_POST['items'];
			$store_id = $_POST['store_id'];
			$omit_menu_id = false;

			if (!empty($_POST['omit_menu_id']))
			{
				$omit_menu_id = $_POST['omit_menu_id'];
			}

			$store_id = explode(",", $store_id);
			$items = explode(",", $items);

			foreach ($store_id as $k => $v)
			{
				if (empty($v))
				{
					unset($store_id[$k]);
				}
			}

			require_once('processor/admin/food_sales.php');

			$processor = new processor_admin_food_sales();

			if ($this->show_store_selectors)
			{
				$processor->setShowStore(true);
			}

			list($rows, $items) = $processor->getPurchasersInRangeForItems($mySQLDate, $duration, $store_id, $items, $omit_menu_id);

			$this->exportExcel($tpl, $rows, $items, $mySQLDate, $duration);

			return;
		}

		$month = 0;
		$year = 0;

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'btn btn-primary btn-sm',
			CForm::name => 'report_export',
			CForm::value => 'Export Excel Report'
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

		if (isset ($report_type_to_run) && isset($_REQUEST["pickDate"]) && $_REQUEST["pickDate"])
		{
			$report_type_to_run = $_REQUEST["pickDate"];
		}

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

		$Form->DefaultValues['store_type'] = 'corporate_stores';

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'corporate_stores'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'franchise_stores'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'region'
		));

		$Form->AddElement(array(
			CForm::type => CForm::RegionDropDown,
			CForm::name => "regions"
		));

		$Form->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "store_type",
			CForm::onChange => 'storeTypeClick',
			CForm::required => true,
			CForm::value => 'selected_stores'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "search_string",
			CForm::maxlength => 120,
			CForm::length => 40
		));

		$Form->addElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'requested_stores'
		));

		$menus = CMenu::getActiveMenuArray();

		$lastActiveMenuId = null;
		$menuOptions = array('Filter no guests');

		foreach ($menus as $thisMenu)
		{
			$menuOptions[$thisMenu['id']] = $thisMenu['name'];
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => $menuOptions,
			CForm::name => 'order_since_menu_id'
		));


		$tpl->assign('form_session_list', $Form->render());
		$tpl->assign('page_title', 'Menu Item Sales Report');
	}
}

?>