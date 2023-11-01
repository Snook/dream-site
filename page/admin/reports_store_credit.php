<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once("includes/CPageAdminOnly.inc");
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/CSessionReports.inc');
require_once("phplib/PHPExcel/PHPExcel.php");
require_once('ExcelExport.inc');

class page_admin_reports_store_credit extends CPageAdminOnly
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

	function runFranchiseLead()
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
		$exportStr = "";
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
		$group = true;
		if (isset ($_REQUEST["export"]))
		{
			$group = true;
			$labels = array(
				"User Id",
				"Last Name",
				"First Name",
				"Primary Email",
				"Phone",
				"Credit Type",
				"Amount",
				"Date Card Redeemed or Credit Rcvd",
				"Description",
				"Referred Guest",
				"Will Expire"
			);
			$tpl->assign('labels', $labels);
			$exportStr = "(Excel Export)";
		}

		$rows = CStoreCredit::getActiveCreditByStore($store, $group, isset ($_REQUEST["export"]));
		CStoreCredit::addPendingReferralCreditByStore($store, $rows, isset ($_REQUEST["export"]));
		CPointsCredits::addPPCreditToStoreCreditReport($store, $rows, isset ($_REQUEST["export"]));

		foreach ($rows as $key => $element)
		{
			foreach ($element as $subkey => $subelement)
			{
				if ($subelement['credit_type'] == 1)
				{
					$rows[$key][$subkey]['credit_type'] = "Gift Card Refund";
					$rows[$key][$subkey]['description'] = "N/A";
				}
				else if ($subelement['credit_type'] == 2)
				{
					$rows[$key][$subkey]['credit_type'] = "Referral Credit";
					if (empty($rows[$key][$subkey]['description']))
					{
						$rows[$key][$subkey]['description'] = "N/A";
					}
				}
				else if ($subelement['credit_type'] == 3)
				{
					$rows[$key][$subkey]['credit_type'] = "Direct Credit";
					if (empty($rows[$key][$subkey]['description']))
					{
						$rows[$key][$subkey]['description'] = "N/A";
					}
				}
				else if ($subelement['credit_type'] == 99)
				{
					$rows[$key][$subkey]['credit_type'] = "Pending Referral Credit";

					if (empty($rows[$key][$subkey]['description']))
					{
						$rows[$key][$subkey]['description'] = "N/A";
					}
				}
				else if ($subelement['credit_type'] == 100)
				{
					$rows[$key][$subkey]['credit_type'] = "PLATEPOINTS Dinner Dollars";
					if (empty($rows[$key][$subkey]['description']))
					{
						$rows[$key][$subkey]['description'] = "N/A";
					}
				}
			}
		}

		if ($rows != null && count($rows) > 0 && isset ($_REQUEST["export"]))
		{  // smash the records down :( csv.inc cannot handle nested arrays
			$temparrays = array();
			foreach ($rows as $key => $element)
			{
				foreach ($element as $subkey => $subelement)
				{
					unset($subelement['referred_guest_id']);
					unset($subelement['origination_date']);

					if (strtoupper($subelement['expiration_date']) != 'N/A' && $subelement['expiration_date'] != 0)
					{
						$subelement['expiration_date'] = PHPExcel_Shared_Date::stringToExcel(date("Y-m-d H:i:s", $subelement['expiration_date']));
					}
					else
					{
						$subelement['expiration_date'] = "N/A";
					}

					$subelement['timestamp_created'] = PHPExcel_Shared_Date::stringToExcel(date("Y-m-d H:i:s", $subelement['timestamp_created']));
					$temparrays[] = $subelement;
				}
			}
			$rows = $temparrays;
		}

		if ($rows != null && count($rows) > 0)
		{
			$tpl->assign('rows', $rows);
		}
		$numRows = count($rows);

		$columnDescs = array();

		$columnDescs['H'] = array(
			'align' => 'left',
			'width' => 'auto',
			'type' => 'datetime'
		);
		$columnDescs['K'] = array(
			'align' => 'left',
			'width' => 'auto',
			'type' => 'datetime'
		);

		$tpl->assign('col_descriptions', $columnDescs);

		$tpl->assign('rowcount', $numRows);
		$tpl->assign('report_count', $numRows);

		CLog::RecordReport("Store Credit $exportStr", "Rows:$numRows ~ Store: $store");

		$formArray = $Form->render();
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title', 'Store Credit Report');
		$tpl->assign('store', $store);
		if (defined('HOME_SITE_SERVER'))
		{
			$tpl->assign('HOME_SITE_SERVER', true);
		}
	}
}

?>