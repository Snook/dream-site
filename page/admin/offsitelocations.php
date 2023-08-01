<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/CDashboardReport.inc');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/COffsitelocation.php');
require_once('includes/CSessionReports.inc');
require_once("phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');

class page_admin_offsitelocations extends CPageAdminOnly
{
	private $current_store_id = null;

	private $show = array(
		'store_selector' => false
	);

	function runFranchiseManager()
	{
		$this->runPageOffsitelocation();
	}

	function runOpsLead()
	{
		$this->runPageOffsitelocation();
	}

	function runFranchiseOwner()
	{
		$this->runPageOffsitelocation();
	}

	function runHomeOfficeStaff()
	{
		$this->show['store_selector'] = true;

		$this->runPageOffsitelocation();
	}

	function runFranchiseLead()
	{
		$this->runPageOffsitelocation();
	}

	function runEventCoordinator()
	{
		$this->runPageOffsitelocation();
	}

	function runHomeOfficeManager()
	{
		$this->show['store_selector'] = true;

		$this->runPageOffsitelocation();
	}

	function runSiteAdmin()
	{
		$this->show['store_selector'] = true;

		$this->runPageOffsitelocation();
	}

	function runPageOffsitelocation()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm();
		$Form->Repost = true;

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
					$anchorDay = date("Y-m-01", mktime(0, 0, 0, $month, 1, $year));
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

			$Offsitelocation_id = CGPC::do_clean($_POST['offsitelocation_chooser'],TYPE_INT);
			$store_id = CGPC::do_clean($_POST['store_id'],TYPE_INT);
			$rows = $this->getOffsitelocationData($store_id, $month, $day, $year, $duration, $Offsitelocation_id);

			$_GET['export'] = 'xlsx';

			$labels = array(
				"Offsitelocation",
				"Session Date/Time",
				"Amount",
				"First Name",
				"Last Name",
				"Email",
				"Preferred Phone Type",
				"Mobile",
				"Mobile Contact Time",
				"Land Line",
				"Land Line Contact Time"
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
				'align' => 'center',
				'type' => 'currency'
			);
			$columnDescs['D'] = array(
				'align' => 'left',
				'width' => 'auto'
			);
			$columnDescs['E'] = array(
				'align' => 'left',
				'width' => 'auto'
			);
			$columnDescs['F'] = array(
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
			$columnDescs['H'] = array(
				'align' => 'left',
				'width' => 'auto'
			);
			$columnDescs['I'] = array(
				'align' => 'left',
				'width' => 'auto'
			);
			$columnDescs['J'] = array(
				'align' => 'left',
				'width' => 'auto'
			);

			$tpl->assign('col_descriptions', $columnDescs);
			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows);

			$rowCount = count($rows);

			CLog::RecordReport("Offsitelocation Report", "Rows:$rowCount");

			if ($rowCount == 1)
			{
				$tpl->setStatusMsg("The request returned no results.");
				unset($_GET['export']);
			}

			$tpl->assign('rowcount', $rowCount);
		}

		$OffsitelocationDropDownArray = array("all" => "All Community Pick Up Locations");
		$OffsitelocationArray = COffsitelocation::storeOffsitelocationArray($Store, false, true);

		foreach ($OffsitelocationArray as $id => $data)
		{
			$OffsitelocationDropDownArray[$id] = $data->location_title;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => true,
			CForm::options => $OffsitelocationDropDownArray,
			CForm::name => 'offsitelocation_chooser'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Hidden,
			CForm::name => 'store_id',
			CForm::value => $this->current_store_id
		));

		$states = CStatesAndProvinces::GetStatesArray();
		$statelist = "";
		foreach ($states as $statekey => $statevalue)
		{
			$statelist .= '<option value="' . $statekey . '" ' . (($statekey == $Store->state_id) ? 'selected="selected"' : ''). '>' . $statevalue . '</option>';
		}

		$tpl->assign('statelist', $statelist);
		$tpl->assign('show', $this->show);
		$tpl->assign('OffsitelocationArray', $OffsitelocationArray);
		$tpl->assign('store', $Store);
		$tpl->assign('form_session_list', $Form->render());
	}

	function getOffsitelocationData($store_id, $month, $day, $year, $duration, $Offsitelocation_id)
	{
		$ordersObj = DAO_CFactory::create('orders');
		$OffsitelocationClause = "";
		if ($Offsitelocation_id == 'all')
		{
			$OffsitelocationClause = " and sp.store_pickup_location_id > 0 ";
		}
		else
		{
			$OffsitelocationClause = " and sp.store_pickup_location_id = $Offsitelocation_id ";
		}

		$sqlDate = date("Y-m-d 00:00:00", mktime(0, 0, 0, $month, $day, $year));

		$q = "SELECT
			spl.location_title,
			o.id,
			s.session_start,
			o.grand_total,
			u.firstname,
			u.lastname,
			u.primary_email,
			telephone_1_type as preferred_contact_type,
			if(telephone_1_type = 'MOBILE', telephone_1,(if(telephone_2_type = 'MOBILE', telephone_2, ''))) as mobile,
			if(telephone_1_type = 'MOBILE', telephone_1_call_time,(if(telephone_2_type = 'MOBILE', telephone_1_call_time, ''))) as mobile_contact_time,
			if(telephone_1_type = 'LAND_LINE', telephone_1,(if(telephone_2_type = 'LAND_LINE', telephone_2, ''))) as land_line,
			if(telephone_1_type = 'LAND_LINE', telephone_1_call_time,(if(telephone_2_type = 'LAND_LINE', telephone_1_call_time, ''))) as land_line_contact_time
			FROM orders o
			JOIN booking b ON b.order_id = o.id AND b.status = 'ACTIVE' AND b.is_deleted = 0
			JOIN session s ON s.id = b.session_id AND s.session_start > '$sqlDate' AND s.session_start < DATE_ADD('$sqlDate', INTERVAL $duration) and s.session_type_subtype = 'REMOTE_PICKUP'
			JOIN session_properties sp on sp.session_id = s.id $OffsitelocationClause
			JOIN user u ON u.id = o.user_id
			JOIN store_pickup_location spl on spl.id = sp.store_pickup_location_id
			WHERE o.store_id = $store_id
			AND o.is_deleted = 0";
		$ordersObj->query($q);

		$rows = array();
		$total = 0;
		while ($ordersObj->fetch())
		{
			$rows[] = array(
				'fr_name' => $ordersObj->location_title,
				'session' => $ordersObj->session_start,
				'value' => $ordersObj->grand_total,
				'firstname' => $ordersObj->firstname,
				'lastname' => $ordersObj->lastname,
				'email' => $ordersObj->primary_email,
				'preferred_contact_type' => $ordersObj->preferred_contact_type,
				'mobile' => $ordersObj->mobile,
				'mobile_contact_time' => $ordersObj->mobile_contact_time,
				'land_line' => $ordersObj->land_line,
				'land_line_contact_time' => $ordersObj->land_line_contact_time
			);

			$total += $ordersObj->grand_total;
		}

		$rows[] = array(
			'fr_name' => "",
			'session' => "Total",
			'value' => $total,
			'firstname' => "",
			'lastname' => "",
			'email' => "",
			'preferred_contact_type' => "",
			'mobile' => "",
			'mobile_contact_time' => "",
			'land_line' => "",
			'land_line_contact_time' => "",
		);

		return $rows;
	}
}

?>