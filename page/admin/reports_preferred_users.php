<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('includes/CSessionReports.inc');

class page_admin_reports_preferred_users extends CPageAdminOnly
{

	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runFranchiseManager()
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

	function runFranchiseOwner()
	{
		$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

	function runSiteAdmin()
	{
		$store = null;
		$SessionReport = new CSessionReports();
		$report_type_to_run = 1;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;
		$Form->Bootstrap = true;
		$total_count = 0;
		$report_submitted = false;
		if ($this->currentStore)
		{ //fadmins
			$store = $this->currentStore;
		}
		else
		{
			$Form->DefaultValues['store'] = null;  // alway have the first value...

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$store = $Form->value('store');
		}

		$user_type = CUser::getCurrentUser()->user_type;

		$export = "";
		if (isset ($_REQUEST["export"]))
		{

			$export = "(Excel Export)";

			if ($user_type == CUser::SITE_ADMIN || $user_type == CUser::HOME_OFFICE_STAFF || $user_type == CUser::HOME_OFFICE_MANAGER)
			{
				$labels = array(
					"User Id",
					"Last Name",
					"First Name",
					"Primary Email",
					"User Type",
					"All Stores",
					"Pref Type",
					"Pref Value",
					"Start Date"
				);
			}
			else
			{
				$labels = array(
					"User Id",
					"Last Name",
					"First Name",
					"Primary Email",
					"User Type",
					"Pref Type",
					"Pref Value",
					"Start Date"
				);
			}

			$tpl->assign('labels', $labels);
		}

		$rows = $this->findUsers($store, $user_type);
		$numRows = count($rows);

		$tpl->assign('rows', $rows);
		$tpl->assign('rowcount', $numRows);

		CLog::RecordReport("Preferred User $export", "Rows:$numRows ~ Store: $store");

		$tpl->assign('report_count', count($rows));

		$formArray = $Form->render();

		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'Preferred Users Report');
		$tpl->assign('store', $store);
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}

	function findUsers($store_id, $user_type)
	{
		$booking = DAO_CFactory::create("booking");

		$querystr = "Select `user`.id,`user`.lastname,`user`.firstname,`user`.primary_email," . "`user`.user_type,";

		if ($user_type == CUser::SITE_ADMIN || $user_type == CUser::HOME_OFFICE_STAFF || $user_type == CUser::HOME_OFFICE_MANAGER)
		{
			$querystr .= "user_preferred.all_stores,";
		}

		$querystr .= "user_preferred.preferred_type,user_preferred.preferred_value,user_preferred.user_preferred_start";
		$querystr .= " From user_preferred Inner Join `user` ON user_preferred.user_id = `user`.id Where user_preferred.store_id = '$store_id' ";
		$querystr .= "and user_preferred.is_deleted = 0 and user.is_deleted= 0";

		$booking->query($querystr);
		$rows = array();
		while ($booking->fetch())
		{
			$tarray = $booking->toArray();
			//$tarray['timestamp_created'] = CSessionReports::reformatTime ($tarray['timestamp_created']);
			$tarray['user_preferred_start'] = CSessionReports::reformatTime($tarray['user_preferred_start']);
			$tarray['is_deleted'] = "";
			$rows [] = $tarray;
		}

		return ($rows);
	}

}

?>