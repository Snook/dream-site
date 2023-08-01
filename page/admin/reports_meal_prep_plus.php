<?php
require_once("includes/CPageAdminOnly.inc");
require_once('includes/CSessionReports.inc');
require_once('phplib/PHPExcel/PHPExcel.php');
require_once('ExcelExport.inc');

class page_admin_reports_meal_prep_plus extends CPageAdminOnly
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
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$store = null;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;
		$report_submit = false;
		$curMenuID = 92;

		set_time_limit(3600);

		$SessionReport = new CSessionReports();

		if (isset ($_REQUEST["report_submit"]))
		{
			$report_submit = true;
		}

		if ($this->currentStore)
		{ // fadmins
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

		$month_array = array();

		//	$menu = DAO_CFactory::create('menu');
		//	$menu->findCurrent();

		$menuarray = CMenu::getActiveMenuArray();

		if (count($menuarray) > 0)
		{
			$arry = array_pop($menuarray);
			if (!empty($arry))
			{
				$curMenuID = $arry["id"];
			}
		}

		//	$menu->fetch();
		//	$curMenuID = $menu->id;
		$menu = null;

		$monthnum = $curMenuID;

		$tempMenu = DAO_CFactory::create('menu');
		$str = "SELECT`menu`.`id`,`menu`.`global_menu_end_date`,`menu`.`menu_start`,`menu`.`menu_name`
			from menu where menu.id >= 226 and `menu`.`is_deleted`=0 and `menu`.`is_active` = 1 order by menu_start desc";

		$tempMenu->query($str);
		while ($tempMenu->fetch())
		{
			$month_array[$tempMenu->id] = $tempMenu->menu_name;
		}
		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::onChangeSubmit => false,
			CForm::allowAllOption => false,
			CForm::options => $month_array,
			CForm::default_value => $monthnum,
			CForm::name => 'menu_popup'
		));

		$reportMenu = $Form->value('menu_popup');
		$tempMenu = null;
		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'report_submit',
			CForm::value => 'Run Report'
		));
		$filename = "MenuPushReport";
		$tpl->assign('filename', $filename);
		$report_date = "";

		$rowCount = 0;

		if ($report_submit)
		{
			$rows = array();

			$totalCurrent = 0;
			$totalTerminated = 0;
			$totalRefunded = 0;
			$totalCompleted = 0;
			$totalSubs = 0;

			$subscriptions = new DAO();
			$subscriptions->query("select po.user_id, poi.id as mid, poi.product_membership_initial_menu, poi.product_membership_initial_menu + pm.term_months - 1 as final_menu
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id 
					join product_membership pm on poi.product_id = pm.product_id
					where $store = po.store_id and po.is_deleted = 0  and $reportMenu >= poi.product_membership_initial_menu and $reportMenu <= poi.product_membership_initial_menu + pm.term_months - 1					
					order by po.user_id, poi.product_membership_initial_menu");

			$rowTemplate = array("user_id" => "",
								"user_name" => "",
								 "email" => "",
								 "user_state" => "",
								 "orders_placed_prior" => "",
								 "total_orders_placed_during" => "",
								 "mid" => "",
								 "status" => "", //User::getMealPrepPlusDisplayStringAbbreviated(CUser::MEMBERSHIP_STATUS_NOT_ENROLLED),
								 'enrollment_date' => "",
								"initial_month" => "",
								"num_orders_complete" => "",
								"num_orders_required" => "",
								"months_skipped" => "",
								"revenue" => "",
								"mpp_discount" => "",
								"revenue_after_discount" => "",
								"end_month" => "",
								"last_session_attended" => "",
								"months_remaining" => "");

			while($subscriptions->fetch())
			{
				$rows[$subscriptions->mid] = $rowTemplate;
				
				
				$User = DAO_CFactory::create('user');
				$User->id = $subscriptions->user_id;
				$User->find(true);
				$thisSub = $User->getMembershipStatus(false, true, $subscriptions->mid);

				if ($thisSub['status'] == CUser::MEMBERSHIP_STATUS_TERMINATED)
				{
					$totalTerminated++;
				}
				else if ($thisSub['status'] == CUser::MEMBERSHIP_STATUS_CURRENT)
				{
					$totalCurrent++;
				}
				else if ($thisSub['status'] == CUser::MEMBERSHIP_STATUS_COMPLETED)
				{
					$totalCompleted++;
				}
				else if ($thisSub['status'] == CUser::MEMBERSHIP_STATUS_REFUNDED)
				{
					$totalRefunded++;
				}

				$totalSubs++;


				$this->getAdditionalUserStats($User, $thisSub, $rows[$subscriptions->mid]);

				$rows[$subscriptions->mid]["user_id"] = $User->id;
				$rows[$subscriptions->mid]["user_name"] = $User->firstname . " " . $User->lastname;
				$rows[$subscriptions->mid]["email"] = $User->primary_email;
				$rows[$subscriptions->mid]["mid"] = $subscriptions->mid;
				$rows[$subscriptions->mid]["status"] = CUser::getMealPrepPlusDisplayStringAbbreviated($thisSub['status']);
				$rows[$subscriptions->mid]["enrollment_date"] = PHPExcel_Shared_Date::stringToExcel($thisSub['enrollment_date']);
				$rows[$subscriptions->mid]["initial_month"] = $thisSub['eligible_menus'][$subscriptions->product_membership_initial_menu]['menu_info']->menu_name;
				$rows[$subscriptions->mid]["num_orders_complete"] = $thisSub['months_satisfied'];
				$rows[$subscriptions->mid]["num_orders_required"] = $thisSub['term_months'];
				$rows[$subscriptions->mid]["months_skipped"] = $thisSub['hard_skip_count'];
				$rows[$subscriptions->mid]["revenue"] = $thisSub['total_revenue'];
				$rows[$subscriptions->mid]["mpp_discount"] = $thisSub['total_savings'];
				$rows[$subscriptions->mid]["revenue_after_discount"] = $thisSub['total_revenue']- $thisSub['total_savings'];
				$rows[$subscriptions->mid]["end_month"] = $thisSub['completion_month'];
				$rows[$subscriptions->mid]["months_remaining"] = $thisSub['term_months'] - $thisSub['months_satisfied'];

			}

			$lastRow = $totalSubs + 1;


			if (!empty($rows))
			{

				$rows['totals_1'] = $rowTemplate;
				$rows['totals_1']["mid"] = "Total Current";
				$rows['totals_1']["status"] = $totalCurrent;
				$rows['totals_1']["months_skipped"] = "Totals";
				$rows['totals_1']["revenue"] = "=SUM(N2:N$lastRow)";
				$rows['totals_1']["mpp_discount"] = "=SUM(O2:O$lastRow)";
				$rows['totals_1']["revenue_after_discount"] = "=SUM(P2:P$lastRow)";

				$rows['totals_3'] = $rowTemplate;
				$rows['totals_3']["mid"] = "Total Terminated";
				$rows['totals_3']["status"] = $totalTerminated;

				$rows['totals_4'] = $rowTemplate;
				$rows['totals_4']["mid"] = "Total Refunded";
				$rows['totals_4']["status"] = $totalRefunded;

				$rows['totals_5'] = $rowTemplate;
				$rows['totals_5']["mid"] = "Total Completed";
				$rows['totals_5']["status"] = $totalCompleted;

				$labels = array(
					"User Id",
					"User Name",
					"Email",
					"Guest Type at Membership Start Date",
					"Orders Placed Prior to Membership",
					"Total Orders Placed During Membership",
					"Membership ID",
					"Status",
					"Enrollment Date",
					"Initial Month",
					"Num Orders Complete",
					"Num Orders Required",
					"Months Skipped",
					"Revenue",
					"Meal Prep+ Discount",
					"Revenue After Discount",
					"Membership End Month",
					"Last Session Attended",
					"Months Remaining in Membership"
				);

				$columnDescs = array();

				$columnDescs['A'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['B'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['C'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['D'] = array(
					'align' => 'left',
					'width' => '16'
				);
				$columnDescs['E'] = array(
					'align' => 'left',
					'width' => '14'
				);
				$columnDescs['F'] = array(
					'align' => 'left',
					'width' => '14'
				);
				$columnDescs['G'] = array(
					'align' => 'left',
					'width' => '16'
				);
				$columnDescs['H'] = array(
					'align' => 'left',
					'width' => '16'
				);
				$columnDescs['I'] = array(
					'align' => 'center',
					'type' => 'datetime',
					'width' => 18
				);
				$columnDescs['J'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['K'] = array(
					'align' => 'left',
					'width' => '12'
				);
				$columnDescs['L'] = array(
					'align' => 'left',
					'width' => '12'
				);
				$columnDescs['M'] = array(
					'align' => 'left',
					'width' => '12'
				);
				$columnDescs['N'] = array(
					'align' => 'left',
					'type' => 'currency',
					'width' => '12'
				);
				$columnDescs['O'] = array(
					'align' => 'left',
					'type' => 'currency',
					'width' => '12'
				);
				$columnDescs['P'] = array(
					'align' => 'left',
					'type' => 'currency',
					'width' => '12'
				);
				$columnDescs['Q'] = array(
					'align' => 'left',
					'width' => 'auto'
				);
				$columnDescs['R'] = array(
					'align' => 'left',
					'type' => 'datetime',
					'width' => '18'
				);
				$columnDescs['S'] = array(
					'align' => 'left',
					'width' => '12'
				);

				$tpl->assign('col_descriptions', $columnDescs);

				$_GET['export'] = 'xlsx';

				$rowCount = count($rows);
				$tpl->assign('rowcount', $rowCount);
				$tpl->assign('rows', $rows);
				$tpl->assign('labels', $labels);

				CLog::RecordReport("Meal Prep Plus", "Rows:$rowCount ~ Store: $store ~ Menu: $reportMenu");
			}
			else
			{
				$tpl->assign('no_results', true);
			}
		}

		$formArray = $Form->render();

		$tpl->assign('form_session_list', $formArray);

	}

	function getAdditionalUserStats($userObj, $subStatus, &$row)
	{
		//last session attended
		//User State At Enrollment
		//Orders Placed Prior
		//Total Orders Placed During

		$enrollmentDate = date("Y-m-d 00:00:00", strtotime($subStatus['enrollment_date']));

		$userState = false;
		$orderPreHistory = new DAO();
		$orderPreHistory->query("select count(distinct b.order_id) as num_orders, max(s.session_start) as last_session_time from booking b 	
								join session s on s.id = b.session_id and s.session_start < '$enrollmentDate'
								where b.user_id = {$userObj->id} and b.status = 'ACTIVE' and b.is_deleted = 0");

		$orderPreHistory->fetch();

		if ($orderPreHistory->num_orders == 0)
		{
			$userState = 'NEW';
		}
		else if (strtotime($orderPreHistory->last_session_time) < strtotime($enrollmentDate) - (86400 * 365))
		{
			$userState = 'REACQUIRED';
		}
		else
		{
			$userState = 'EXISTING';
		}


		$row["user_state"] = $userState;
		$row["orders_placed_prior"] = $orderPreHistory->num_orders;
		$row["total_orders_placed_during"] = $subStatus['all_orders_count'];

		$now = date("Y-m-d 00:00:00");
		$lastSessionFinder = new DAO();
		$lastSessionFinder->query("select max(s.session_start) as last_session_time from booking b 	
								join session s on s.id = b.session_id and s.session_start < '$now'
								where b.user_id = {$userObj->id} and b.status = 'ACTIVE' and b.is_deleted = 0");

		$lastSessionFinder->fetch();

		$row["last_session_attended"]  = PHPExcel_Shared_Date::stringToExcel($lastSessionFinder->last_session_time);

	}
}

?>