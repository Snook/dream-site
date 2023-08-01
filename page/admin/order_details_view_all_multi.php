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
require_once("page/admin/order_details_view_all.php");
require_once 'includes/DAO/Orders.php';
require_once("includes/CSessionReports.inc");

class page_admin_order_details_view_all_multi extends CPageAdminOnly {

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
		$SessionReport = new CSessionReports();
 		if (!empty($sessions))
 		{

 			$sessionCounter = count($sessions);

	 		foreach($sessions as $sid)
	 		{

				$defaultSessionInfo = $SessionReport->findSessionInfo($sid);
				if($defaultSessionInfo[0]['session_type_subtype'] == CSession::WALK_IN){
					continue;
				}
				if ( isset ($_REQUEST["customer_print_view"]))
				{
					$customer_print_view = CGPC::do_clean($_REQUEST["customer_print_view"],TYPE_STR);
				}

				$template = new CTemplate();

				$sessionDAO = DAO_CFactory::create('session');
				$sessionDAO->query("select s.store_id, st.supports_dream_rewards, st.allow_dfl_tool_access, st.supports_plate_points from session s join store st on st.id = s.store_id where s.id = $sid");
				$sessionDAO->fetch();
				$showDreamRewardsStatus = true;
				$supportsDinnersForLife = false;
				$supportsPlatePoints = false;
				if (!$sessionDAO->supports_dream_rewards)
				{
					$showDreamRewardsStatus =  false;
				}
				if ($sessionDAO->allow_dfl_tool_access)
				{
					$supportsDinnersForLife =  true;
				}
                if ($sessionDAO->supports_plate_points)
                {
                    $supportsPlatePoints =  true;
                }

				$other_details = array();
				$output_array = page_admin_order_details_view_all::create_view_all_orders ( $sid , $other_details, $customer_print_view, $showDreamRewardsStatus, $supportsDinnersForLife, false, $supportsPlatePoints);

				if (empty($output_array))
				{
					$template->assign('null_array', true);
				}
				$template->assign('view_all_list', $output_array);
				$template->assign('other_details_list', $other_details);
				$template->assign('customer_view', $customer_print_view);
				$template->assign('suppress_header_footer', true);

				$output .= $template->fetch('admin/order_details_view_all.tpl.php');

				$sessionCounter--;
				if ($sessionCounter > 0 && !empty($output_array))
				{
					$output .= '<div style="page-break-after:always;"></div>';
				}
	 		}
 		}

 		$report_desc = "Session/";

 		if (isset($_REQUEST['issidedish']) && $_REQUEST['issidedish'] == '1')
 		{
 			$report_desc .= "Multi SideDish Report";
  		}
  		else if (isset($_REQUEST['ispreassembled']) && $_REQUEST['ispreassembled'] == '1')
  		{
  			$report_desc .= "Multi Fast Lane Report";
  		}
 		else
 		{
	 		if ($customer_print_view)
	 			$report_desc .= "Multi Customer Receipt";
	 		else
	 			$report_desc .= "Multi Store Receipt";
 		}

         $report_date = CGPC::do_clean($_REQUEST['report_date'],TYPE_STR);
 		CLog::RecordReport($report_desc, "Date: {}" );


 		$tpl->assign('output', $output);
	}
}
?>