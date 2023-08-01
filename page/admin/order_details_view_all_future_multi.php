<?php
/*
 * Created on Nov 10, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 * Could not run merge on this page
 */


require_once("includes/CPageAdminOnly.inc");
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once("page/admin/order_details_view_all_future.php");
require_once 'includes/DAO/Orders.php';

class page_admin_order_details_view_all_future_multi extends CPageAdminOnly {

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
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

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

    function runHomeOfficeStaff()
    {
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

 	function runFranchiseOwner()
 	{
		$customer_print_view = 0;
		$output_array = NULL;
 		$tpl = CApp::instance()->template();
		//$Form = new CForm();
		//$Form->Repost = FALSE;
		if (isset($_REQUEST['report_date']))
		{
		    $dateValues = explode("-", CGPC::do_clean($_REQUEST['report_date'],TYPE_STR));
		}

		$Session = DAO_CFactory::create("session");
		$Session->findDailySessions(CGPC::do_clean($_REQUEST['store_id'],TYPE_INT), $dateValues[2], $dateValues[1], $dateValues[0]);
		while ($Session->fetch())
		{
			$sessions[] = $Session->id;
		}

 		$output = "";

 		$sessionCounter = count($sessions);

 		foreach($sessions as $sid)
 		{
			if ( isset ($_REQUEST["customer_print_view"]))
			{
				$customer_print_view = CGPC::do_clean( $_REQUEST["customer_print_view"],TYPE_STR);
			}

			$template = new CTemplate();

			$sessionDAO = DAO_CFactory::create('session');
			$sessionDAO->query("select s.store_id, st.supports_dream_rewards, st.allow_dfl_tool_access from session s join store st on st.id = s.store_id where s.id = $sid");
			$sessionDAO->fetch();
			$showDreamRewardsStatus = true;
			$supportsDinnersForLife = false;
			if (!$sessionDAO->supports_dream_rewards)
			{
				$showDreamRewardsStatus =  false;
			}
			if ($sessionDAO->allow_dfl_tool_access)
			{
				$supportsDinnersForLife =  true;
			}

			$other_details = array();
			$output_array = page_admin_order_details_view_all_future::create_view_all_future_orders ( $sid , $other_details, $showDreamRewardsStatus );

			if (empty($output_array))
			{
				$template->assign('null_array', true);
			}
			$template->assign('view_all_list', $output_array);
			$template->assign('other_details_list', $other_details);
			$template->assign('customer_view', $customer_print_view);
			$template->assign('suppress_header_footer', true);

			$output .= $template->fetch('admin/order_details_view_all_future.tpl.php');

			$sessionCounter--;
			if ($sessionCounter > 0)
			{
				$output .= "\n<p class='breakafter'>\n";
			}
 		}

         $report_date = CGPC::do_clean($_REQUEST['report_date'],TYPE_STR);
 		CLog::RecordReport('Session/Multi Future Orders', "Date: {$report_date}" );

 		$tpl->assign('output', $output);
	}
}
?>