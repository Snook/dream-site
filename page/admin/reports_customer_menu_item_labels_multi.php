<?php
/*
 * Created on Nov 10, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once("page/admin/reports_customer_menu_item_labels.php");
require_once 'includes/DAO/Orders.php';
require_once 'includes/CSessionReports.inc';
require_once 'DAO/BusinessObject/CMenu.php';

class page_admin_reports_customer_menu_item_labels_multi extends CPageAdminOnly
{
	/*
	*  Get rid of any known offenses, for starters, html_entities from the Menu Import script
	*/
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

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runOpsSupport()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
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
		ini_set('memory_limit', '32M');
		set_time_limit(7200);

		if (isset($_REQUEST['report_date']))
		{
			$dateValues = explode("-", $_REQUEST['report_date']);
		}

		$Session = DAO_CFactory::create("session");
		$Session->findDailySessions($_REQUEST['store_id'], $dateValues[2], $dateValues[1], $dateValues[0]);
		while ($Session->fetch())
		{
			$sessions[] = $Session->id;
		}

		$output = "";
		$tpl = CApp::instance()->template();
		$suppressFastlane = isset($_REQUEST["suppressFastlane"]) ? $_REQUEST["suppressFastlane"] : 0;
		$menu_id = isset ($_REQUEST["menuid"]) ? $_REQUEST["menuid"] : 0;
		$break = isset ($_REQUEST["break"]) ? $_REQUEST["break"] : 0;
		$store_id = isset ($_REQUEST["store_id"]) ? $_REQUEST["store_id"] : 0;
		if ($store_id == 0)
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}

		$store = DAO_CFactory::create('store');
		$store->id = $store_id;
		$store->find(true);
		$store_name = $store->store_name;
		$store_phone = $store->telephone_day;

		$template = new CTemplate();
		$template->assign('success', true);
		$template->assign('suppressFastlane', $suppressFastlane);
		$template->assign('show_borders', 0);
		$template->assign('interface', false);

		$arrayus_majorus = array();

		if (!empty($sessions))
		{
			foreach ($sessions as $sid)
			{
				$output_array = page_admin_reports_customer_menu_item_labels::create_view_all_orders($sid, $other_details, $menu_id, $store_id, $suppressFastlane);
				$arrayus_majorus = array_merge($arrayus_majorus, $output_array);
			}
		}

		CLog::RecordReport('Session/Multi Labels', "Date: {$_REQUEST['report_date']}");

		if (empty($arrayus_majorus))
		{
			$tpl->setStatusMsg('No results for the selected date or time.');
			$template->assign('success', false);
		}
		else
		{
			$tpl->assign('show_borders', 0);
		}

		$template->assign('store_name', $store_name);
		$template->assign('store_phone', $store_phone);

		$template->assign('break', $break);
		$template->assign('order_details', $arrayus_majorus);
		$template->assign('storeObj', $store);

		$output .= $template->fetch('admin/reports_customer_menu_item_labels_four.tpl.php');

		echo $output;
		exit;
	}
}

?>