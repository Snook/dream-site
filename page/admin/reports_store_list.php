<?php
require_once("includes/CPageAdminOnly.inc");
include_once("includes/CForm.inc");
include_once("processor/admin/top30.php");

class page_admin_reports_store_list extends CPageAdminOnly
{
	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeStaff()
	{
		CApp::bounce('/backoffice/access-error?topnavname=store&pagename=' . urlencode('Browse/Edit Stores'));
	}

	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$tpl = CApp::instance()->template();

		//we actually want to always use a GET instead of a post for reports
		//not sure if we'll use much of the CForm functionality
		$Form = new CForm();
		$Form->Repost = false;

		//check out these
		$tpl->assign('labels', null);
		$tpl->assign('rows', null);
		$tpl->assign('rowcount', null);

		//build store drop down

		$metric_type = "agr";
		$month = date("Y-m-01");

		if (isset($_POST['submit_top_5']))
		{
			$top5 = array();
			foreach ($_POST as $k => $v)
			{
				if (strpos($k, "sid_") === 0)
				{
					$store_id = substr($k, 4);
					$top5[] = $store_id;
				}
			}

			if (count($top5) != 5)
			{
				$tpl->setErrorMsg("You must check exactly 5 stores.");
			}
			else
			{

				$StoreObj1 = DAO_CFactory::create('store');
				$StoreObj1->query("update store set is_in_current_top_5 = 0");

				$StoreObj2 = DAO_CFactory::create('store');
				$StoreObj2->query("update store set is_in_current_top_5 = 1 where id in (" . implode(',', $top5) . ")");

				$tpl->setStatusMsg("The top 5 stores have been updated.");
			}
		}

		$printArray = array();

		$rankingsObj = DAO_CFactory::create('dashboard_metrics_rankings');

		$rankingsObj->query("select s.home_office_id, s.store_name, s.city, s.state_id, s.is_in_current_top_5, dmr.store_id, dmr.$metric_type as value, dmr.{$metric_type}_rank as rank from dashboard_metrics_rankings  dmr
		join store s on s.id = dmr.store_id	where dmr.date = '$month' order by dmr.{$metric_type}_rank asc");

		$lastRank = 0;
		while ($rankingsObj->fetch())
		{

			$printArray[$rankingsObj->store_id] = array(
				'value' => $rankingsObj->value,
				'rank' => $rankingsObj->rank,
				'store_name' => $rankingsObj->store_name,
				'city' => $rankingsObj->city,
				'state' => $rankingsObj->state_id,
				'hoid' => $rankingsObj->home_office_id,
				'store_id' => $rankingsObj->store_id,
				'in_Top_5' => $rankingsObj->is_in_current_top_5
			);

			$lastRank = $rankingsObj->rank;

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => "sid_" . $rankingsObj->store_id,
				CForm::checked => !empty($rankingsObj->is_in_current_top_5)
			));
		}

		$tpl->assign('printArray', $printArray);
		$tpl->assign('metric_display_name', processor_admin_top30::getMetricDisplayName($metric_type));
		$tpl->assign('preVal', processor_admin_top30::getMetricPrefix($metric_type));
		$tpl->assign('postVal', processor_admin_top30::getMetricPostfix($metric_type));

		$formArray = $Form->render();

		$tpl->assign('form_list_stores', $formArray);
	}

}

?>