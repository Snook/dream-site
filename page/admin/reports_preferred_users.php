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
	private $allowAllOption = false;

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
		$this->runPreferredReport();
	}

	function runSiteAdmin()
	{
		$this->allowAllOption = true;
		$this->runPreferredReport();
	}

	function runPreferredReport()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = false;
		$Form->Bootstrap = true;
		if ($this->currentStore)
		{
			$store = $this->currentStore;
		}
		else
		{
			$Form->DefaultValues['store'] = null;  // always have the first value...

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => $this->allowAllOption,
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
					"Store Name",
					"State",
					"City",
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
		$DAO_user_preferred = DAO_CFactory::create('user_preferred', true);

		if (is_numeric($store_id))
		{
			$DAO_user_preferred->store_id = $store_id;
		}

		$DAO_user_preferred->joinAddWhereAsOn(DAO_CFactory::create('user', true));
		$DAO_store = DAO_CFactory::create('store', true);

		if (!is_numeric($store_id))
		{
			$DAO_store->active = 1;
		}

		$DAO_user_preferred->joinAddWhereAsOn($DAO_store);
		$DAO_user_preferred->orderBy("store.state_id, store.store_name, `user`.firstname");
		$DAO_user_preferred->find();

		$rows = array();
		while ($DAO_user_preferred->fetch())
		{
			if ($user_type == CUser::SITE_ADMIN || $user_type == CUser::HOME_OFFICE_STAFF || $user_type == CUser::HOME_OFFICE_MANAGER)
			{
				$rows[] = array(
					'id' => $DAO_user_preferred->user_id,
					'firstname' => $DAO_user_preferred->DAO_user->firstname,
					'lastname' => $DAO_user_preferred->DAO_user->lastname,
					'primary_email' => $DAO_user_preferred->DAO_user->primary_email,
					'user_type' => $DAO_user_preferred->DAO_user->user_type,
					'all_stores' => $DAO_user_preferred->all_stores,
					'store_name' => $DAO_user_preferred->DAO_store->store_name,
					'state' => $DAO_user_preferred->DAO_store->state_id,
					'city' => $DAO_user_preferred->DAO_store->city,
					'preferred_type' => $DAO_user_preferred->preferred_type,
					'preferred_value' => $DAO_user_preferred->preferred_value,
					'user_preferred_start' => CSessionReports::reformatTime($DAO_user_preferred->user_preferred_start)
				);
			}
			else
			{
				$rows[] = array(
					'id' => $DAO_user_preferred->user_id,
					'firstname' => $DAO_user_preferred->DAO_user->firstname,
					'lastname' => $DAO_user_preferred->DAO_user->lastname,
					'primary_email' => $DAO_user_preferred->DAO_user->primary_email,
					'user_type' => $DAO_user_preferred->DAO_user->user_type,
					'preferred_type' => $DAO_user_preferred->preferred_type,
					'preferred_value' => $DAO_user_preferred->preferred_value,
					'user_preferred_start' => CSessionReports::reformatTime($DAO_user_preferred->user_preferred_start)
				);
			}
		}

		return ($rows);
	}

}

?>