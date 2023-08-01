<?php // page_admin_create_store.php
/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once("includes/CCalendar.inc");

class page_admin_help_system extends CPageAdminOnly
{
	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}
	function runFranchiseStaff()
	{
		$this->runSiteAdmin();
	}
	function runFranchiseLead()
	{
		$this->runSiteAdmin();
	}
	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}
	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}
	function runFranchiseOwner()
	{
		$this->runSiteAdmin();
	}
	function runEventCoordinator()
	{
		$this->runSiteAdmin();
	}
	function runOpsLead()
	{
	    $this->runSiteAdmin();
	}
	function runOpsSupport()
	{
		$this->runSiteAdmin();
	}
	function runDishwasher()
	{
		$this->runSiteAdmin();
	}
	function runNewEmployee()
	{
		$this->runSiteAdmin();
	}
	
	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		switch(CGPC::do_clean($_REQUEST['section'],TYPE_STR))
		{
			case 'DD':
				$report_name = 'admin/help/help_direct_order.tpl.php';
				break;
			case 'OE':
				$report_name = 'admin/help/help_order_edit.tpl.php';
				break;
			case 'ME':
				$report_name = 'admin/help/help_menu_editor.tpl.php';
				break;
			case 'CR':
				$report_name = 'admin/help/help_customer_report.tpl.php';
				break;
			case 'DG':
				$report_name = 'admin/help/help_dashboard_guide.tpl.php';
				break;
			case 'SM':
				$report_name = 'admin/help/help_session_mgr.tpl.php';
				break;
			case 'SP':
				$report_name = 'admin/help/help_session_publishing.tpl.php';
				break;
			case 'FS':
				$report_name = 'admin/help/help_reports_financial_statistic.tpl.php';
				break;
			case 'GC_LOAD':
				$report_name = 'admin/help/help_gift_card_management_load.tpl.php';
				break;
			case 'GC_ORDER':
				$report_name = 'admin/help/help_gift_card_management_order.tpl.php';
				break;
			default:
				$report_name = 'admin/help/help_direct_order.tpl.php';
		}

		$tpl->assign('report_template', $report_name);
	}
}
?>