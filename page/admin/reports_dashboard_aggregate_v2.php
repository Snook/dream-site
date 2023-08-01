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



/*
 *
 * Columns:
 *
 * ---------------STORE BASICS - always included
 * ID
 * Home OFfice ID
 * Store Name
 * State
 * City
 * --------------- EXTENDED STORE DATA - switched on and off
 * Manager
 * District
 * Months Open
 * Store Age
 * Trade Area ID
 *
 * ---------------- GOALS - switched on and off
 * Revenue for month
 * Revenue Goal
 * Sides & Sweets $
 * Sides & Sweets Goal $
 * Count Taste Attendees (excludes host)
 * Guests
 *      Existing
 *      New
 *      Reacquired
 * Servings
 * Sides & Sweets units
 * Flag - has set goals
 *
 * ------------- Same Store Sales
 * Revenue for month
 * Revenue this day last month
 * % comparison
 * Revenue this day this month last year
 * % comparison
 *
 * revenue for current menu
 * revenue for this day in menu last menu
 * % comparison
 * revenue for this day in menu last year
 * % comparison
 *
 */


class page_admin_reports_dashboard_aggregate_v2 extends CPageAdminOnly
{
	private $currentStore = null;
	private $show_store_selectors;
	private $use_menu_month = false;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->show_store_selectors = true;
		$this->runDashboardAggregate();
	}

	function runSiteAdmin()
	{
		$this->show_store_selectors = true;
		$this->runDashboardAggregate();
	}

	/*
	function runFranchiseOwner(){
	    $this->show_store_selectors = false;
	    $this->runDashboardAggregate();
	}
	*/

	function runDashboardAggregate()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = TRUE;

		$tpl->assign('show_store_selectors', $this->show_store_selectors);

		$month = 0;
		$year = 0;

		$Form->AddElement(array (CForm::type => CForm::Submit,
								 CForm::name => 'report_submit',
								 CForm::css_class => 'button',
								 CForm::value => 'Run Report'));

		$month_array = array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$year = date("Y");

		// Date Selection Type
		$Form->DefaultValues['date_type'] = 'current_month';

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "date_type",
								CForm::required => true,
								CForm::value => 'current_month'));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "date_type",
								CForm::required => true,
								CForm::value => 'other_month'));

		$Form->AddElement(array(CForm::type=> CForm::Text,
								CForm::name => "year_other_month",
								CForm::required => true,
								CForm::default_value => $year,
								CForm::length => 6));

		$Form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::onChangeSubmit => false,
								CForm::allowAllOption => false,
								CForm::options => $month_array,
								CForm::name => 'month_other_month'));

		$Form->DefaultValues['store_type'] = 'selected_stores';

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "store_type",
								CForm::onChange => 'storeTypeClick',
								CForm::required => true,
								CForm::value => 'corporate_stores'));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "store_type",
								CForm::onChange => 'storeTypeClick',
								CForm::required => true,
								CForm::value => 'franchise_stores'));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "store_type",
								CForm::onChange => 'storeTypeClick',
								CForm::required => true,
								CForm::value => 'soft_launch_stores'));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "store_type",
								CForm::onChange => 'storeTypeClick',
								CForm::required => true,
								CForm::value => 'region'));

		$Form->AddElement(array(CForm::type=> CForm::RegionDropDown,
								CForm::name => "regions"));

		$Form->AddElement(array(CForm::type=> CForm::RadioButton,
								CForm::name => "store_type",
								CForm::onChange => 'storeTypeClick',
								CForm::required => true,
								CForm::value => 'selected_stores'));

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

		$Form->addElement(array(CForm::type => CForm::Hidden, CForm::name => 'requested_stores'));

		$tpl->assign('store_data', CStore::getStoreTreeAsNestedList(false, true));

		$tpl->assign('query_form', $Form->render());

		if ( $Form->value('report_submit'))
		{
			$sectionSwitches = array();

			$isCurrentMonth = false;

			$this->use_menu_month = ($Form->value('menu_or_calendar') == 'menu');


			$sectionSwitches['ext_store_info'] = !empty($_POST['dagg_ext_store_info']);
			$sectionSwitches['goals'] = !empty($_POST['dagg_goals']);
			$sectionSwitches['p_and_l'] = !empty($_POST['dagg_p_and_l']);
			$sectionSwitches['same_store'] = !empty($_POST['dagg_same_store']);

			if ($Form->value('date_type') == 'current_month')
			{
				// current month
				//  $storeClause = $this->getStoreClause($Form);

				$isCurrentMonth = true;

				if ($this->use_menu_month)
				{
					$now = date("Y-m-d");
					$menuObj = new CMenu();
					$menuObj->query("SELECT id, menu_start, global_menu_start_date FROM menu WHERE '$now' <= global_menu_end_date ORDER BY id LIMIT 1");
					$menuObj->fetch();
					$selectMonth = $menuObj->menu_start;


					$reportName = "Dashboard Aggregate Report for " .  date("F Y", strtotime($selectMonth));

					$rows = $this->retrieveStoreDataMultiQuery($sectionSwitches, $Form, $selectMonth, $isCurrentMonth, $menuObj->id);
				}
				else
				{
					$curMonth = date("n");
					$curYear= date("Y");
					$lastYear = $curYear - 1;
					$curDay = date("j");

					//   $curYear--;
					//   $lastYear--;

					$selectMonth = date("Y-m-d", mktime(0,0,0,$curMonth, 1, $curYear));

					$reportName = "Dashboard Aggregate Report for " .  date("F Y", strtotime($selectMonth));

					$rows = $this->retrieveStoreDataMultiQuery($sectionSwitches, $Form, $selectMonth, $isCurrentMonth);

				}


			}
			else
			{
				$isCurrentMonth = false;

				$curMonth  = $Form->value('month_other_month');
				$curMonth++;
				if (empty($curMonth) || !is_numeric($curMonth))
				{
					throw new Exception("The month is invalid");
				}

				$curYear  = $Form->value('year_other_month');

				if (empty($curYear) || !is_numeric($curYear) || $curYear < 2004 || $curYear > 3000)
				{
					throw new Exception("The month is invalid");
				}


				if ($this->use_menu_month)
				{
					//$curMonth +=1;
					$selectMonth = date("Y-m-d", mktime(0,0,0,$curMonth, 1, $curYear));
					$menuObj = new CMenu();
					$menuObj->query("SELECT id, menu_start, global_menu_start_date FROM menu WHERE '$selectMonth' <= global_menu_end_date ORDER BY id LIMIT 1");
					$menuObj->fetch();
					$selectMonth = $menuObj->menu_start;


					$reportName = "Dashboard Aggregate Report for " .  date("F Y", strtotime($selectMonth));

					$rows = $this->retrieveStoreDataMultiQuery($sectionSwitches, $Form, $selectMonth, $isCurrentMonth, $menuObj->id);
				}else{
					$selectMonth = date("Y-m-d", mktime(0,0,0,$curMonth, 1, $curYear));
					$rows = $this->retrieveStoreDataMultiQuery($sectionSwitches, $Form, $selectMonth, $isCurrentMonth);

				}

			}

			// Always available

			$labels = array(
				"Store ID",
				"Home Office ID",
				"Store Name",
				"Store City",
				"Store State"
			);

			$sectionHeader = array("Store Info" => 5);
			$columnDescs['A'] = array('align' => 'left', 'width' => 6);
			$columnDescs['B'] = array('align' => 'left', 'width' => 6);
			$columnDescs['C'] = array('align' => 'left', 'width' => 'auto');
			$columnDescs['D'] = array('align' => 'left', 'width' => 'auto');
			$columnDescs['E'] = array('align' => 'center', 'width' => 6);

			$col = 'F';
			$colSecondChar = '';
			$thirdSecondChar = '';

			if ($sectionSwitches['ext_store_info'])
			{
				$labels = array_merge($labels, array ("Months Open", "Region", "Coach"));
				$sectionHeader["Ext Store Info"] = 3;

				$columnDescs[$col] = array('align' => 'center', 'width' => 7);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'left', 'width' => 'auto');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'left', 'width' => 'auto');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
			}

			if ($sectionSwitches['goals'])
			{
				$labels = array_merge($labels, array (
					"Revenue Actual", "Revenue Goal", "Sides & Sweets Actual", "Sides & Sweets Goal",
					"Existing Guests", "Existing Guests In-store Signup Rate",
					"Reacquired Guests", "Reacquired Guests In-store Signup Rate",
					"New Guests", "New Guests In-store Signup Rate", "Additional Order Guests", "Starter Pack Guests",
					"Starter Pack Guest Goal", "Regular Guests", "Regular Guest Goal",
					"Fundraiser & Taste Guests", "Fundraiser & Taste Guest Goal",
					"Servings Sold", "Items Sold","FT Units Sold", "Has set Goals", "New Executive Chefs"));

				$sectionHeader["Goals and Actuals"] = 22;

				$columnDescs[$col] = array('align' => 'center', 'width' => 'auto', 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 'auto', 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 'auto', 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 'auto', 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10, 'type' => 'percent');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10, 'type' => 'percent');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10, 'type' => 'percent');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$col] = array('align' => 'center', 'width' => 10);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
			}

			if ($sectionSwitches['p_and_l'])
			{

				$labels = array_merge($labels, array ("Food & Packaging", "Employee Wages", "Manager Salaries", "Owner Salaries", "Employee Hours", "Manager Hours", "Owner Hours",
													  "Payroll Taxes", "Bank Card Merchant Fees", "Kitchen and Office Supplies", "Marketing and Advertising Expense", "Rent", "Repairs and Maintenance",
													  "Utilities", "Monthly Debt Service", "Other Expenses", "Net Income"));

				$sectionHeader["Profit and Loss data"] = 17;

				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12);
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
			}

			if ($sectionSwitches['same_store'])
			{

				$sectionHeader["To date Revenue"] = 1;

				$labels = array_merge($labels, array ("Revenue", "Revenue", "Difference" , "% Difference", "Revenue", "Difference" , "% Difference"));
				$sectionHeader["Same Store This Month Last Year"] = 3;
				$sectionHeader["Same Store Last Month"] = 3;
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'percent');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'currency');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
				$columnDescs[$colSecondChar.$col] = array('align' => 'center', 'width' => 12, 'type' => 'percent');
				incrementColumn($thirdSecondChar, $colSecondChar, $col);
			}

			$numRows = count($rows);
			$tpl->assign('labels', $labels);
			$tpl->assign('rows', $rows);
			$tpl->assign('rowcount', $numRows);
			$tpl->assign('sectionHeader', $sectionHeader);
			$tpl->assign('col_descriptions', $columnDescs);

			if (!$this->show_store_selectors)
			{
				$store_id = CBrowserSession::getCurrentFadminStore();
				CLog::RecordReport("Dashboard Aggregate Report V2", "Store: $store_id" );
			}
			else
			{
				CLog::RecordReport("Dashboard Aggregate Report V2", "Store: Multiple" );
			}


			$_GET['export'] = 'xlsx';
		}
	}

	function retrieveStoreDataMultiQuery($sectionSwitches, $Form, $month, $isCurrentMonth, $menu_id = false)
	{


		$storeClause = $this->getStoreClause($Form);

		$queryObj = DAO_CFactory::create('dashboard_metrics_guests');

		// always include
		$select = "select st.id as store_id, st.home_office_id, st.store_name, st.city, st.state_id ";
		$joins = " $storeClause ";
		$joins .= " left join store_trade_area sta on sta.store_id = st.id and sta.is_active = 1 and sta.is_deleted = 0 ";
		$joins .= " left join trade_area ta on sta.trade_area_id = ta.id and ta.is_active = 1 ";

		$where = "where  dmg.date = '$month' and dmg.is_deleted = 0 group by st.id order by ta.region, st.state_id, st.city";
		$select .= " from dashboard_metrics_guests dmg ";

		$q = $select . $joins . $where;
		$queryObj->query($q);

		if (!$this->use_menu_month)
		{
			$agr_table = 'dashboard_metrics_agr';
			$guest_table = 'dashboard_metrics_guests';
		}
		else
		{
			$agr_table = 'dashboard_metrics_agr_by_menu';
			$guest_table = 'dashboard_metrics_guests_by_menu';
		}



		$rows = array();
		while($queryObj->fetch())
		{

			$dieser = $queryObj->toArray();
			$dieser = array_slice($dieser, 0, 5);
			$rows[$queryObj->store_id] = $dieser;
		}

		if ($sectionSwitches['ext_store_info'])
		{

			$select = "select st.id as store_id ";
			$joins = " $storeClause ";
			$where = "where  dmg.date = '$month' and dmg.is_deleted = 0 group by st.id order by ta.region, st.state_id, st.city";
			$select .= ", PERIOD_DIFF(DATE_FORMAT(now(), '%Y%m'), DATE_FORMAT(st.grand_opening_date, '%Y%m')) as months_open, ta.region, CONCAT(coach_user.firstname, ' ', coach_user.lastname) as coachname ";
			$select .= " from dashboard_metrics_guests dmg ";
			$joins .= " left join store_trade_area sta on sta.store_id = st.id and sta.is_active = 1 and sta.is_deleted = 0 ";
			$joins .= " left join trade_area ta on sta.trade_area_id = ta.id and ta.is_active = 1 ";
			$joins .= " left join store_coach sc on sc.store_id = st.id  and sc.is_active = 1 and sc.is_deleted = 0 ";
			$joins .= " left join coach c on c.id = sc.coach_id and c.active = 1 ";
			$joins .= " left join user coach_user on coach_user.id = c.user_id ";

			$q = $select . $joins . $where;

			$queryObj->query($q);

			while($queryObj->fetch())
			{

				$dieser = $queryObj->toArray();
				$dieser = array_slice($dieser, 0, 4);
				if (isset($rows[$queryObj->store_id]))
				{
					$rows[$queryObj->store_id] = array_merge($rows[$queryObj->store_id], $dieser);
				}
				else
				{
					throw new Exception("Bum Store ID");
				}
			}
		}

		if ($sectionSwitches['goals'])
		{

			$select = "select st.id as store_id ";
			$joins = " $storeClause ";
			$where = "where  dmg.date = '$month' and dmg.is_deleted = 0 group by st.id order by ta.region, st.state_id, st.city";

			$select .= ", dma.total_agr, smg.gross_revenue_goal, dma.addon_sales_total, smg.finishing_touch_revenue_goal ";

			//$select .= ", dmg.guest_count_existing_taste + dmg.guest_count_existing_intro + dmg.guest_count_existing_regular as total_existing_guests ";
			$select .= ", dmg.guest_count_existing_taste + dmg.guest_count_existing_intro + dmg.guest_count_existing_regular + dmg.guest_count_existing_additional + dmg.guest_count_existing_fundraiser + dmg.guest_count_existing_delivered AS total_existing_guests ";

			$select .= ", dmg.instore_signup_existing_regular + dmg.instore_signup_existing_taste + dmg.instore_signup_existing_intro + dmg.instore_signup_existing_fundraiser as existing_guests_signup_rate ";

			//$select .= ", dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_regular as total_reacquired_guests ";
			$select .= ", dmg.guest_count_reacquired_regular + dmg.guest_count_reacquired_additional + dmg.guest_count_reacquired_taste + dmg.guest_count_reacquired_intro + dmg.guest_count_reacquired_fundraiser + dmg.guest_count_reacquired_delivered  AS total_reacquired_guests ";
			$select .= ", dmg.instore_signup_reacquired_regular + dmg.instore_signup_reacquired_taste + dmg.instore_signup_reacquired_intro + dmg.instore_signup_reacquired_fundraiser as reacquired_guests_signup_rate ";

			$select .= ", dmg.guest_count_new_taste + dmg.guest_count_new_intro + dmg.guest_count_new_regular + dmg.guest_count_new_additional + dmg.guest_count_new_fundraiser + dmg.guest_count_new_delivered as total_new_guests ";
			$select .= ", dmg.instore_signup_new_regular + dmg.instore_signup_new_taste + dmg.instore_signup_new_intro + dmg.instore_signup_new_fundraiser + dmg.instore_signup_new_additional as new_guests_signup_rate ";

			$select .= ", dmg.guest_count_new_additional + dmg.guest_count_reacquired_additional+ dmg.guest_count_existing_additional as additional_guests ";
			$select .= ", dmg.guest_count_new_intro + dmg.guest_count_reacquired_intro + dmg.guest_count_existing_intro as intro_guests, smg.intro_guest_count_goal ";
			$select .= ", dmg.guest_count_new_regular + dmg.guest_count_reacquired_regular + dmg.guest_count_existing_regular as regular_guests, smg.regular_guest_count_goal ";
			$select .= ", dmg.guest_count_existing_taste + dmg.guest_count_reacquired_taste + dmg.guest_count_new_taste + dmg.guest_count_existing_fundraiser + dmg.guest_count_reacquired_fundraiser + dmg.guest_count_new_fundraiser as taste_guests, smg.taste_guest_count_goal  ";
			$select .= ", dmg.total_servings_sold, dmg.total_items_sold, 0 as total_ft_units, 0 as has_set_goals, 0 as new_executive_chefs ";
			$select .= " from $guest_table dmg ";
			$joins .= " left join store_trade_area sta on sta.store_id = st.id and sta.is_active = 1 and sta.is_deleted = 0 ";
			$joins .= " left join trade_area ta on sta.trade_area_id = ta.id and ta.is_active = 1 ";

			$joins .= " left join $agr_table dma on dma.store_id = st.id and dma.date =  '$month' and dma.is_deleted = 0 ";
			$joins .= " left join store_monthly_goals smg on smg.store_id = st.id and smg.date = '$month' ";

			$toDateOrders = $this->getToDateGuestCounts($storeClause, $month);
			$newExecutiveChefs = $this->getNewExecutiveChefs($storeClause, $month);

			$q = $select . $joins . $where;
			$queryObj->query($q);

			while($queryObj->fetch())
			{

				$dieser = $queryObj->toArray();
				$dieser = array_slice($dieser, 0, 22);
				if (isset($rows[$queryObj->store_id]))
				{
					$rows[$queryObj->store_id] = array_merge($rows[$queryObj->store_id], $dieser);
				}
				else
				{
					throw new Exception("Bum Store ID");
				}
			}

			foreach($rows as $store_id => &$data)
			{

				if (isset($rows[$store_id]) && isset($toDateOrders[$store_id]))
				{
					$rows[$store_id]['existing_guests_signup_rate'] = CTemplate::divide_and_format($rows[$store_id]['existing_guests_signup_rate'], $toDateOrders[$store_id]['existing_to_date_orders'], 4);
					if ($rows[$store_id]['existing_guests_signup_rate'] > 1) $rows[$store_id]['existing_guests_signup_rate'] = 1.0;

					$rows[$store_id]['reacquired_guests_signup_rate'] = CTemplate::divide_and_format($rows[$store_id]['reacquired_guests_signup_rate'], $toDateOrders[$store_id]['reacquired_to_date_orders'], 4);
					if ($rows[$store_id]['reacquired_guests_signup_rate'] > 1) $rows[$store_id]['reacquired_guests_signup_rate'] = 1.0;

					$rows[$store_id]['new_guests_signup_rate'] = CTemplate::divide_and_format($rows[$store_id]['new_guests_signup_rate'], $toDateOrders[$store_id]['new_to_date_orders'], 4);
					if ($rows[$store_id]['new_guests_signup_rate'] > 1) $rows[$store_id]['new_guests_signup_rate'] = 1.0;
				}

				if (isset($rows[$store_id]))
				{
					if (isset($newExecutiveChefs[$store_id]))
					{
						$rows[$store_id]['new_executive_chefs'] = $newExecutiveChefs[$store_id];
					}
					else
					{
						$rows[$store_id]['new_executive_chefs'] = 0;
					}
				}

			}
		}

		if ($sectionSwitches['p_and_l'])
		{
			//17
			$select = "select st.id as store_id ";
			$joins = " $storeClause ";
			$where = "where  dmg.date = '$month' and dmg.is_deleted = 0 group by st.id";

			$select .= ", smpl.cost_of_goods_and_services,
                                smpl.employee_wages,
                                smpl.manager_salaries,
                                smpl.owner_salaries,
                                smpl.employee_hours,
                                smpl.manager_hours,
                                smpl.owner_hours,
                                smpl.payroll_taxes,
                                smpl.bank_card_merchant_fees,
                                smpl.kitchen_and_office_supplies,
                                smpl.total_marketing_and_advertising_expense,
                                smpl.rent_expense,
                                smpl.repairs_and_maintenance,
                                smpl.utilities,
                                smpl.monthly_debt_service,
                                smpl.other_expenses,
                                smpl.net_income ";
			$select .= " from dashboard_metrics_guests dmg ";

			$joins .= " left join store_monthly_profit_and_loss smpl on smpl.store_id = st.id and smpl.date = '$month' and smpl.is_deleted = 0 ";

			$queryObj->query($select . $joins . $where);

			while($queryObj->fetch())
			{

				$dieser = $queryObj->toArray();
				$dieser = array_slice($dieser, 0, 18);
				if (isset($rows[$queryObj->store_id]))
				{
					$rows[$queryObj->store_id] = array_merge($rows[$queryObj->store_id], $dieser);
				}
				else
				{
					throw new Exception("Bum Store ID");
				}
			}
		}

		if ($sectionSwitches['same_store'])
		{
			$queryObj = DAO_CFactory::create('revenue_event');
			$storeClause = str_replace("dmg", "re", $storeClause);

			// --------------------------------------- Current Month

			if ($this->use_menu_month)
			{
				$q = "select st.id as store_id, sum(re.amount) as store_total from revenue_event re
	        		$storeClause
	        		where re.menu_id = $menu_id and re.is_deleted = 0  and re.event_type <> 'RESCHEDULED'
	        		group by st.id  order by re.event_time";
				$queryObj->query($q);
			}
			else
			{
				$queryObj->query("select st.id as store_id, sum(if(re.negative_affected_month = '$month', re.amount * -1, re.amount))  as store_total from revenue_event re
	        		$storeClause
	        		where (re.positive_affected_month = '$month' or re.negative_affected_month = '$month') and re.is_deleted = 0
	        		group by st.id  order by re.event_time");
			}

			while($queryObj->fetch())
			{

				if (isset($rows[$queryObj->store_id]))
				{
					$rows[$queryObj->store_id] = array_merge($rows[$queryObj->store_id], array('to_date_agr' => $queryObj->store_total));
				}
				else
				{
					throw new Exception("Bum Store ID");
				}
			}

			// --------------------------------------- Same Month - Last Year


			$dateArr = explode("-", $month);
			$selectTS = mktime(0, 0, 0, $dateArr[1], 1, $dateArr[0] - 1);
			$ly_month = date("Y-m-d", $selectTS);

			$cutoffTimeClause = "";
			if ($isCurrentMonth)
			{
				$lastYear = $dateArr[0] - 1;
				$cutoffTimeClause = "and re.event_time < '" . date("$lastYear-m-d H:i:s") . "' ";
			}

			if ($this->use_menu_month)
			{

				$ly_menuObj = new CMenu();
				$ly_menuObj->query("SELECT id, menu_start, global_menu_start_date FROM menu WHERE '$ly_month' = menu_start");
				$ly_menuObj->fetch();
				$Ly_menu_id = $ly_menuObj->id;

				$queryObj->query("select st.id as store_id,  sum(re.amount) as store_total from revenue_event re
        		$storeClause
        			where re.menu_id = $Ly_menu_id and re.is_deleted = 0 and re.event_type <> 'RESCHEDULED' $cutoffTimeClause
        		group by st.id  order by re.event_time");
			}
			else
			{
				$queryObj->query("select st.id as store_id,  sum(re.amount) as store_total from revenue_event re
		            $storeClause
		            where (re.positive_affected_month = '$ly_month' or re.negative_affected_month = '$ly_month') and re.is_deleted = 0 $cutoffTimeClause
		            group by st.id  order by re.event_time");
			}

			while($queryObj->fetch())
			{
				if (isset($rows[$queryObj->store_id]))
				{
					$curAgr  =  $rows[$queryObj->store_id]['to_date_agr'];
					$LYDiff = $curAgr - $queryObj->store_total;
					$LYSign = ($LYDiff < 0 ? "-" : "+");
					$LYDiff =  $LYSign . " $" . CTemplate::number_format(abs($LYDiff), 2);
					$LYpercentDiff = (($curAgr - $queryObj->store_total) / $queryObj->store_total) * 100;
					$LYpercentDiff  = CTemplate::number_format($LYpercentDiff, 2) . "%";

					$LastYear = array("ly_year_agr" => $queryObj->store_total,  'ly_diff' => $LYDiff, 'ly_percent_diff' => $LYpercentDiff);

					$rows[$queryObj->store_id] = array_merge($rows[$queryObj->store_id], $LastYear);
				}
				else
				{
					throw new Exception("Bum Store ID");
				}
			}

			// --------------------------------------- Previous Month 

			$selectTS = mktime(0, 0, 0, $dateArr[1] - 1, 1, $dateArr[0]);
			$lm_month = date("Y-m-d", $selectTS);

			$cutoffTimeClause = "";

			if ($isCurrentMonth)
			{
				$lastMonthMonth = date("m", strtotime($lm_month));
				$lastMonthYear = date("Y", strtotime($lm_month));
				$cutoffTimeLM = date("$lastMonthYear-$lastMonthMonth-d H:i:s");
				$cutoffTimeClause = "and re.event_time < '$cutoffTimeLM' ";
			}

			if ($this->use_menu_month)
			{
				$lm_menuObj = new CMenu();
				$lm_menuObj->query("SELECT id, menu_start, global_menu_start_date FROM menu WHERE '$lm_month' = menu_start");
				$lm_menuObj->fetch();
				$Lm_menu_id = $lm_menuObj->id;

				$queryObj->query("select st.id as store_id, sum(re.amount) as store_total from revenue_event re
            		$storeClause
            		where re.menu_id = $Lm_menu_id and re.is_deleted = 0 and re.event_type <> 'RESCHEDULED' $cutoffTimeClause
            		group by st.id  order by re.event_time");
			}
			else
			{
				$queryObj->query("select st.id as store_id, sum(re.amount) as store_total from revenue_event re
            		$storeClause
            		where (re.positive_affected_month = '$lm_month' or re.negative_affected_month = '$lm_month') and re.is_deleted = 0 $cutoffTimeClause
            		group by st.id  order by re.event_time");
			}

			while ($queryObj->fetch())
			{
				if (isset($rows[$queryObj->store_id]))
				{

					$curAgr = $rows[$queryObj->store_id]['to_date_agr'];
					$LMDiff = $curAgr - $queryObj->store_total;
					$LMSign = ($LMDiff < 0 ? "-" : "+");
					$LMDiff = $LYSign . " $" . CTemplate::number_format(abs($LMDiff), 2);
					$LMpercentDiff = (($curAgr - $queryObj->store_total) / $queryObj->store_total) * 100;
					$LMpercentDiff = CTemplate::number_format($LMpercentDiff, 2) . "%";

					$LastMonth = array(
						"lm_month_agr" => $queryObj->store_total,
						'lm_diff' => $LMDiff,
						'lm_percent_diff' => $LMpercentDiff
					);

					$rows[$queryObj->store_id] = array_merge($rows[$queryObj->store_id], $LastMonth);
				} else
				{
					throw new Exception("Bum Store ID");
				}
			}
		}

		return $rows;
	}

	function getStoreClause($Form)
	{
		if (!$this->show_store_selectors)
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
			return "INNER JOIN store st on dmg.store_id = st.id and st.active = 1 and st.id = $store_id ";
		}

		$retVal = false;
		if ($Form->value('store_type') == 'corporate_stores')
		{
			$retVal = "INNER JOIN store st on dmg.store_id = st.id and st.is_corporate_owned = 1 and st.active = 1";
		}
		else if ($Form->value('store_type') == 'franchise_stores')
		{
			$retVal = "INNER JOIN store st on dmg.store_id = st.id and st.is_corporate_owned = 0 and st.active = 1";
		}
		else if ($Form->value('store_type') == 'soft_launch_stores')
		{
			$retVal = "INNER JOIN store st on dmg.store_id = st.id and st.id in (28, 29, 30, 54, 62, 63, 67, 80, 82, 96, 99, 101, 102, 103, 105,
					108, 119, 121, 127, 133, 136, 158, 159, 166, 171, 175,
					204, 208, 215, 229, 239, 244, 262, 274, 281, 288, 302, 307, 308, 309) and st.active = 1";
		}
		else if ($Form->value('store_type') == 'region')
		{
			$region = $Form->value('regions');

			if (empty($region) or !is_numeric($region))
			{
				throw new Exception("Invalid region id");
			}

			$retVal = "
	        INNER JOIN store st on dmg.store_id = st.id and st.is_corporate_owned = 0 and st.active = 1
	        INNER JOIN store_trade_area str on str.store_id = st.id and str.trade_area_id = $region and str.is_deleted = 0 and str.is_active = 1";
		}
		else if ($Form->value('store_type') == 'selected_stores')
		{
			if ($_POST['requested_stores'] != 'all')
			{

				$tarr = explode(",", $_POST['requested_stores']);
				$newTarr = array();
				foreach($tarr as $storeID)
				{
					if (is_numeric($storeID))
						$newTarr[] = $storeID;
				}

				$storeList = implode(",", $newTarr);
			}
			else
			{
				throw new Exception("TODO");
			}

			$retVal = "INNER JOIN store st on dmg.store_id = st.id and st.active = 1 and st.id in ($storeList) ";
		}

		return $retVal;


	}

	function getNewExecutiveChefs($storeClause, $date)
	{

		$retVal = array();


		if ($this->use_menu_month)
		{

			list($menuStartDate, $menuInterval) = CMenu::getMenuStartandInterval($date);

			$storeClause = str_replace("dmg.store_id", "u.home_store_id", $storeClause);

			$userObj = DAO_CFactory::create('user');

			$userObj->query("select u.home_store_id, count(u.id) as num_new_execs from user u
				join points_user_history puh on puh.user_id = u.id and puh.event_type = 'ACHIEVEMENT_AWARD'
				and puh.timestamp_created >= '$menuStartDate' and puh.timestamp_created < DATE_ADD('$menuStartDate', INTERVAL  $menuInterval)
				and puh.total_points >= 20000 and puh.total_points < 30000
				$storeClause
				group by u.home_store_id");
		}
		else
		{

			$dateParts = explode("-", $date);

			$thisYear = $dateParts[0];
			$thisMonthNumber = intval($dateParts[1]);

			$storeClause = str_replace("dmg.store_id", "u.home_store_id", $storeClause);

			$userObj = DAO_CFactory::create('user');

			$userObj->query("select u.home_store_id, count(u.id) as num_new_execs from user u
		    join points_user_history puh on puh.user_id = u.id and puh.event_type = 'ACHIEVEMENT_AWARD'
		    and MONTH(puh.timestamp_created) = $thisMonthNumber and YEAR(puh.timestamp_created) = $thisYear and puh.total_points >= 20000 and puh.total_points < 30000
		    $storeClause
		    group by u.home_store_id");

		}

		while($userObj->fetch())
		{
			$retVal[$userObj->home_store_id] = $userObj->num_new_execs;
		}

		return $retVal;
	}

	function getToDateGuestCounts($storeClause, $date)
	{
		$retVal = array();


		if ($this->use_menu_month)
		{

			list($menuStartDate, $menuInterval) = CMenu::getMenuStartandInterval($date);

			$storeClause = str_replace("dmg", "od", $storeClause);

			$digest = DAO_CFactory::create('orders_digest');
			$digest->query("select od.store_id, count(od.id) as to_date, od.user_state from orders_digest od
	            $storeClause
	            where od.is_deleted = 0 and
				od.session_time >= '$menuStartDate' and od.session_time < DATE_ADD('$menuStartDate', INTERVAL  $menuInterval)
				and od.session_time < now()
	            GROUP BY od.store_id, od.user_state");
		}
		else
		{
			$dateParts = explode("-", $date);

			$thisYear = $dateParts[0];
			$thisMonthNumber = intval($dateParts[1]);

			$storeClause = str_replace("dmg", "od", $storeClause);

			$digest = DAO_CFactory::create('orders_digest');
			$digest->query("select od.store_id, count(od.id) as to_date, od.user_state from orders_digest od
	    		$storeClause
	    		where od.is_deleted = 0 and MONTH(od.session_time) = $thisMonthNumber and YEAR(od.session_time) = $thisYear and od.session_time < now()
	    		GROUP BY od.store_id, od.user_state");
		}


		while($digest->fetch())
		{
			if ($digest->user_state == 'NEW')
			{
				$retVal[$digest->store_id]['new_to_date_orders'] += $digest->to_date;
			}
			else if ($digest->user_state == 'REACQUIRED')
			{
				$retVal[$digest->store_id]['reacquired_to_date_orders'] += $digest->to_date;
			}
			else if ($digest->user_state == 'EXISTING')
			{
				$retVal[$digest->store_id]['existing_to_date_orders'] += $digest->to_date;
			}
		}

		return $retVal;
	}
}

?>