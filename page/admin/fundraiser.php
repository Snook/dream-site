<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/CDashboardReport.inc');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/CFundraiser.php');
require_once('includes/CSessionReports.inc');
require_once("phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');

class page_admin_fundraiser extends CPageAdminOnly
{
	private $current_store_id = null;

	private $show = array(
		'store_selector' => false
	);

	function runFranchiseManager()
	{
		$this->runPageFundraiser();
	}

	function runOpsLead()
	{
		$this->runPageFundraiser();
	}

	function runFranchiseOwner()
	{
		$this->runPageFundraiser();
	}

	function runHomeOfficeStaff()
	{
	    $this->show['store_selector'] = true;

		$this->runPageFundraiser();
	}

	function runFranchiseLead()
	{
		$this->runPageFundraiser();
	}

	function runEventCoordinator()
	{
		$this->runPageFundraiser();
	}

	function runHomeOfficeManager()
	{
		$this->show['store_selector'] = true;

		$this->runPageFundraiser();
	}

	function runSiteAdmin()
	{
		$this->show['store_selector'] = true;

		$this->runPageFundraiser();
	}

	function runPageFundraiser()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;
		$Form->Bootstrap = true;

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::css_class => "button",
			CForm::value => 'Download Report'
		));

		// $month_array = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
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

		if ($this->show['store_selector'])
		{
			if (!empty($_POST['store']) && is_numeric($_POST['store']))
			{
				CBrowserSession::setCurrentFadminStore($_POST['store']);
			}

			$Form->DefaultValues['store'] = CBrowserSession::getCurrentFadminStoreID();

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => false,
				CForm::onChange => 'if (this.options[this.selectedIndex].value != \'\'){form.submit();}',
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));

			$this->current_store_id = $Form->value('store');
		}
		else
		{
			$this->current_store_id = CBrowserSession::getCurrentFadminStoreID();
		}

		$Store = DAO_CFactory::create('store');
		$Store->id = $this->current_store_id;
		$Store->find(true);

		if ($Form->value('report_submit'))
		{

			$report_type_to_run = CGPC::do_clean($_REQUEST["pickDate"],TYPE_INT);

			if ($report_type_to_run == 1)
			{
				// get the single date
				$day_start = CGPC::do_clean($_REQUEST["single_date"],TYPE_STR);
				$implodedDateArray = explode("-", $day_start);
				$day = $implodedDateArray[2];
				$month = $implodedDateArray[1];
				$year = $implodedDateArray[0];
				$duration = '1 DAY';
				$timeTS = mktime(0, 0, 0, $month, $day, $year);
				$timeSpanStr = "Report for day of " . date("l, M jS, Y", $timeTS);
			}
			else if ($report_type_to_run == 2)
			{

				$rangeReversed = false;
				$implodedDateArray = null;
				$SessionReport = new CSessionReports();

				if (isset ($_REQUEST["range_day_start"]))
				{
					$range_day_start = CGPC::do_clean($_REQUEST["range_day_start"],TYPE_STR);
				}

				if (isset ($_REQUEST["range_day_end"]))
				{
					$range_day_end = CGPC::do_clean($_REQUEST["range_day_end"],TYPE_STR);
				}

				$diff = $SessionReport->datediff("d", $range_day_start, $range_day_end, $rangeReversed);
				$diff++; // always add one for SQL to work correctly
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

				$diffMonth = $SessionReport->datediff("m", $range_day_start, $range_day_end, $rangeReversed);
				if ($diffMonth > 1)
				{
					$spansMenu = true;
				}

				$timeTS1 = mktime(0, 0, 0, $month, intval($day), $year);
				$timeTS2 = $timeTS1 + (($diff - 1) * 86400) + 3601;

				$timeSpanStr = "Report for " . date("M jS, Y", $timeTS1) . " through " . date("M jS, Y", $timeTS2);
			}
			else if ($report_type_to_run == 3)
			{

				$month = CGPC::do_clean($_REQUEST["month_popup"],TYPE_INT);
				$year = CGPC::do_clean($_REQUEST["year_field_001"],TYPE_INT);
				$month++;

				if ($Form->value('menu_or_calendar') == 'menu')
				{
					// menu month
					$anchorDay = date("Y-m-01", mktime(0,0,0,$month,1, $year));
					list($menu_start_date, $interval) = CMenu::getMenuStartandInterval(false, $anchorDay);
					$start_date = strtotime($menu_start_date);
					$year = date("Y", $start_date);
					$month = date("n", $start_date);
					$day = date("j", $start_date);

					$duration = $interval . " DAY";
					$timeSpanStr = "Report for menu month of " . date("F Y", strtotime($anchorDay));

				}
				else
				{
					// process for a given month
					$day = "01";
					$duration = '1 MONTH';
					$timeTS = mktime(0, 0, 0, $month, 1, $year);
					$timeSpanStr = "Report for calendar month of " . date("F Y", $timeTS);
				}

			}
			else if ($report_type_to_run == 4)
			{
				set_time_limit(120);
				$spansMenu = true;
				$year = CGPC::do_clean($_REQUEST["year_field_002"],TYPE_INT);
				$month = "01";
				$day = "01";
				$duration = '1 YEAR';
				$timeTS = mktime(0, 0, 0, 1, 1, $year);
				$timeSpanStr = "Report for year of " . date("Y", $timeTS);
			}

			$fundraiser_id = CGPC::do_clean($_POST['fundraiser_chooser'],TYPE_INT);
			$store_id = CGPC::do_clean($_POST['store_id'],TYPE_INT);
			$rows = $this->getFundraiserData($store_id, $month, $day, $year, $duration, $fundraiser_id);

			$_GET['export'] = 'xlsx';

			$labels = array(
				"FundRaiser",
				"Session Date/Time",
				"Next Session",
				"Amount",
				"First Name",
				"Last Name",
				"Email"
			);

			$columnDescs = array();

			$columnDescs['A'] = array(
				'align' => 'left',
				'width' => 'auto'
			);
			$columnDescs['B'] = array(
				'align' => 'left',
				'type' => 'datetime',
				'width' => 25
			);
			$columnDescs['C'] = array(
				'align' => 'left',
				'type' => 'datetime',
				'width' => 25
			);

			$columnDescs['D'] = array(
				'align' => 'center',
				'type' => 'currency'
			);
			$columnDescs['E'] = array(
				'align' => 'left',
				'width' => 'auto'
			);
			$columnDescs['F'] = array(
				'align' => 'left',
				'width' => 'auto'
			);
			$columnDescs['G'] = array(
				'align' => 'left',
				'width' => 'auto'
			);

			$tpl->assign('col_descriptions', $columnDescs);
			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows);

			$rowCount = count($rows);

			CLog::RecordReport("Fundraiser Report", "Rows:$rowCount");

			if ($rowCount == 1)
			{
				$tpl->setStatusMsg("The request returned no results.");
				unset($_GET['export']);
			}

			$tpl->assign('rowcount', $rowCount);
		}

		$FundraiserDropDownArray = array("all" => "All Fundraisers");
		$fundraiserArray = CFundraiser::storeFundraiserArray($Store, false, true);

		foreach ($fundraiserArray as $id => $data)
		{
			$FundraiserDropDownArray[$id] = $data->fundraiser_name;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::options => $FundraiserDropDownArray,
			CForm::name => 'fundraiser_chooser'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'store_id',
			CForm::value => $this->current_store_id
		));

		$tpl->assign('show', $this->show);
		$tpl->assign('fundraiserArray', $fundraiserArray);
		$tpl->assign('store', $Store);
		$tpl->assign('form_session_list', $Form->render());
	}

	function getFundraiserData($store_id, $month, $day, $year, $duration, $fundraiser_id)
	{
		$ordersObj = DAO_CFactory::create('orders');
		$fundraiserClause = "";
		if ($fundraiser_id == 'all')
		{
			$fundraiserClause = " and o.fundraiser_id > 0 ";
		}
		else
		{
			$fundraiserClause = " and o.fundraiser_id = $fundraiser_id ";
		}

		$sqlDate = date("Y-m-d 00:00:00", mktime(0, 0, 0, $month, $day, $year));

		$ordersObj->query("SELECT
			f.fundraiser_name,
			o.id,
			s.session_start,
       		iq.next_session,
			o.fundraiser_value,
			u.firstname,
			u.lastname,
			u.primary_email
			FROM orders o
			JOIN booking b ON b.order_id = o.id AND b.status = 'ACTIVE' AND b.is_deleted = 0
			JOIN session s ON s.id = b.session_id AND s.session_start > '$sqlDate' AND s.session_start < DATE_ADD('$sqlDate', INTERVAL $duration)
			JOIN user u ON u.id = o.user_id
			JOIN fundraiser f ON f.id = o.fundraiser_id
					left join (select b2.user_id, min(s2.session_start) as next_session from booking b2
						join session s2 on s2.id = b2.session_id where s2.session_start > now() and b2.status  = 'ACTIVE' group by b2.user_id) as iq on iq.user_id = b.user_id

			WHERE o.store_id = $store_id
			AND o.is_deleted = '0' " . $fundraiserClause);

		$rows = array();
		$total = 0;
		while ($ordersObj->fetch())
		{
			$rows[] = array(
				'fr_name' => $ordersObj->fundraiser_name,
				'session' => $ordersObj->session_start,
				'next_session' => $ordersObj->next_session,
				'value' => $ordersObj->fundraiser_value,
				'firstname' => $ordersObj->firstname,
				'lastname' => $ordersObj->lastname,
				'email' => $ordersObj->primary_email
			);

			$total += $ordersObj->fundraiser_value;
		}

		$rows[] = array(
			'fr_name' => "",
			'session' => "",
			'next_session' => "Total",
			'value' => $total,
			'firstname' => "",
			'lastname' => "",
			'email' => ""
		);

		return $rows;
	}
}

?>