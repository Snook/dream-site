<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once ('includes/CPageAdminOnly.inc');
require_once ('includes/CSessionReports.inc');
require_once ('includes/DAO/Booking.php');
require_once ('includes/DAO/BusinessObject/CSession.php');

class page_admin_reports_select_multi_session extends CPageAdminOnly
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
		$store = NULL;
		$SessionReport = new CSessionReports();
		$report_date = NULL;
		$isvalidsession = false;
		$addDescription = false;
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = FALSE;
		$session_id = 0;
		$report_type = 1;
		$report_submit = FALSE;
		$form_submit_str = NULL;
		$printerFriendly=FALSE;
		$printdate = NULL;
		$containerArray = NULL;
		$coupon_users = NULL;

        CTemplate::noCache();

		if ( $this->currentStore )
		{ //fadmins
			$store = $this->currentStore;
		}
		else
		{
		 	$Form->DefaultValues['store'] = array_key_exists('store', $_GET)? $_GET['store'] : null;

			$Form->addElement(array(CForm::type=> CForm::AdminStoreDropDown,
									CForm::onChangeSubmit => true,
									CForm::allowAllOption => false,
									CForm::showInactiveStores => true,
									CForm::name => 'store'));

			$store = $Form->value('store');
		}
		if ( isset ($_REQUEST["printer"]))
		{
			$printerFriendly = $_REQUEST["printer"];
		}
		if ( isset ($_REQUEST["query_submit"]))
		{
			$session_type_to_run = $_REQUEST["query_submit"];
			$report_submit = TRUE;
		}
		if ( isset ($_REQUEST["report_id"])) $report_type = $_REQUEST["report_id"];
		if ( isset ($_REQUEST["session_id"])) $session_id = $_REQUEST["session_id"];
		if ( isset ($_REQUEST["report_date"]))  $report_date = $_REQUEST["report_date"];


		if ($report_date)
		{
			$report_date = CGPC::do_clean($report_date, TYPE_DATE_YYYYMMDD);
		}


		if (empty($printerFriendly) && !empty($session_id))
		{
			CApp::bounce('/?page=admin_main&session=' . $session_id);
		}
		else if (empty($printerFriendly) && !empty($report_date))
		{
			CApp::bounce('/?page=admin_main&day=' . $report_date);
		}

		if ( isset ($_REQUEST["pickSession"]))
		{   // pick the type of search query
			$session_type_to_run = $_REQUEST["pickSession"];
		}
		else
		{
			if (!isset($session_type_to_run)) $session_type_to_run = 1;
		}

		$report_array = array();
		$tab_data = array();

		$Form->AddElement(array (CForm::type => CForm::Submit, CForm::name => 'report_submit', CForm::value => 'Run Report'));

		$session_rows = $SessionReport->createAvailableSessionsArray ($store);

		$optionValue = '';
		if (isset ($_REQUEST["popup"]) )
		{
		   $report_type = 2;
		   $optionValue = $_REQUEST['session_id'];
		}
		$Form->AddElement(array(CForm::type=> CForm::DropDown,
						CForm::onChangeSubmit => false,
						CForm::allowAllOption => true,
						CForm::default_value => $optionValue,
						CForm::options => $session_rows,
						CForm::name => 'sessionpopup'));

		if ( $Form->value('report_submit'))
		{

			unset($report_date);
			if ($session_type_to_run == 1)
			{
				$session_id = 0;
				$report_type = 1;
			}
			else
			{
				$report_type = 2;
				$session_id = $Form->value('sessionpopup');
			}
		}

		$formValue = $Form->value('report_submit');

		if ( $formValue != NULL || $report_submit == TRUE)
		{
			if ($session_type_to_run == 1 && !isset($report_date))
			{
			  	$report_date = $_REQUEST["session_day"];
			}
			if (isset($report_date))
			{
			    $dateValues = explode("-", $report_date);
			}
			if ($session_type_to_run == 1)
			{
				$tab_data = $SessionReport->createSessionTabsArray ($store, $dateValues[2], $dateValues[1], $dateValues[0]);
			}
			$report_submit = true;
			$tpl->assign('report_submit', $report_submit);
			if (isset($report_date))
			{
				$tpl->assign('report_date', $report_date); // 2005-11-12 format
			}
		}
		if (isset($report_submit) && $report_submit == TRUE)
		{
			$sessionArray = null;

			if ((isset($report_date)) && $report_date != NULL)
			{
				$dateValues = explode("-", $report_date);
			}
			$promo_items = array();
			$rows = array();
			$exported_productArray = array();
			$exported_customerArray = array();
			$exported_menuItemArray = array();
			$sum = 0;
			if ($session_type_to_run == 1 && $report_type == 1)
			{
				$promo_items = $SessionReport->getPromoCountsPerItem($sum, $store, $dateValues[2], $dateValues[1], $dateValues[0], '1 DAY');

				$sessionArray = $SessionReport->getEntreeCounts($store, $dateValues[2], $dateValues[1], $dateValues[0]);
				if (isset($sessionArray) && count($sessionArray) > 0)
				{
					$form_submit_str = "/?page=admin_reports_select_multi_session";
				}
			}
			else
			{
			 	$promo_items = $SessionReport->getPromoCountsBySession($session_id, $sum);
				$sessionArray = $SessionReport->findSessionDetails($session_id);

				if (isset($sessionArray) && count($sessionArray) > 0)
				{
					$form_submit_str = "/?page=admin_reports_select_multi_session&amp;query_submit=2&amp;report_id=2&amp;popup=1&amp;session_id=" . $session_id;
				}
			}
			if (isset($sessionArray) && count($sessionArray) > 0)
			{
				$printdate = $sessionArray[0]['session_start'];
			}

			if (count($sessionArray) > 0)
			{
			    $isvalidsession = true;
				$menuid = $sessionArray[0]['menu_id'];

				$menuArray = $SessionReport->generateMenuArray ($menuid, $store);
				$containerArray = $SessionReport->getContainerTypes($menuid);

				$tempSessionArr = $sessionArray;

				$filter = false;

				if (isset($_REQUEST['filter_to']))
                {
                    $filter = explode("|", $_REQUEST['filter_to']);

                    foreach($tempSessionArr as $id => $data)
                    {
                        $thisSID = $data['session_id'];

                        if (!in_array($thisSID,$filter))
                        {
                            unset($tempSessionArr[$id]);
                        }

                    }
                }

				$exported_menuItemArray = $SessionReport->buildMenuItemsArray ($tempSessionArr, $menuArray, false, $containerArray, $promo_items);
				$SessionReport->BuildProductArray ($sessionArray, $exported_productArray);
				$SessionReport->generateAndUpdateProductList ($exported_productArray);

				$session_ids = array();
				foreach($sessionArray as $aSession)
				{
					if (!in_array($aSession['session_id'], $session_ids))
					{
						$session_ids[] = $aSession['session_id'];
					}
				}

			}

			if ($report_type > 1)
			{
				$payment_array = $SessionReport->getPaymentArrays($session_id);

				$payment_failed_balance_due_array =  $SessionReport->isBalanceDueOrPaymentsFailed($payment_array);

				$coupon_users = $SessionReport->LocateCouponCodes($session_id);

				$exported_customerArray = $SessionReport->BuildCustomerArray( $sessionArray,  $coupon_users);
				if (!empty($exported_customerArray) && count($exported_customerArray) > 0)
				{
					$tpl->assign('payment_failed_balance_due_array', $payment_failed_balance_due_array);
				}
				if (count($exported_customerArray) > 0)
				{
					if (empty($report_date) && !empty($_REQUEST["session_day"]))
					{
						$report_date = $_REQUEST["session_day"];
					}

					$history_list = $SessionReport->getCustomerHistory($exported_customerArray, $session_id, $store);

			  		if (isset($history_list) && count($history_list) > 0)
					{
						$tpl->assign('history_list', $history_list);
					}
				}

			}
			if (isset($menuid) && $menuid > 109)
			{
				$SessionReport->setupDisplayByStation($exported_menuItemArray);
			}

			$bundleOfferCount = null;
			if (!empty($session_ids))
			{
				$bundleOfferCount = $SessionReport->getTVOfferCountForSession($session_ids);
			}

			$runReport = true;
			$tpl->assign('customer_array', $exported_customerArray);
			$tpl->assign('menu_array', $exported_menuItemArray);
			$tpl->assign('product_array', $exported_productArray);
			$tpl->assign('bundleOfferCount', $bundleOfferCount);

			if (count($sessionArray) > 0 && $report_type == 1)
            {
                $tpl->assign('sessionArray', $sessionArray);
            }

			if (isset($menuid))
			{
				$tpl->assign('menu_id', $menuid);
				$tpl->assign('show_labels', true);
			}

			$tpl->assign('container_array', $containerArray);

			$tpl->assign('sessionID', $session_id);
			$tpl->assign('run_report', $runReport);
			if (isset($report_date)) $tpl->assign('report_date', $report_date);
			$tpl->assign('report_type', $report_type);

			if ($report_type > 1 )
			{
				CLog::RecordReport("Entry Summary per Session", "Store: $store ~ Date: $report_date ~ Type: $report_type" );
			}
			else
			{
				CLog::RecordReport("Entry Summary per Day", "Store: $store ~ Date: $report_date ~ Type: $report_type" );
			}

		}

		$formArray = $Form->render();
		if (isset($tab_data)) $tpl->assign('report_tab_data', $tab_data);

		$tpl->assign('is_test_store',CStore::isCoreTestStore($store, $menuid));
		$tpl->assign('store_id', $store);
		$tpl->assign('is_valid_session', $isvalidsession);
		$tpl->assign('session_type_to_run', $session_type_to_run);
		$tpl->assign('form_session_list', $formArray);
		$tpl->assign('page_title','Daily and Single Session Reports');
		$tpl->assign('form_submit_string', $form_submit_str);
		$tpl->assign('print_view', $printerFriendly);
		if ($printerFriendly == TRUE)
		{
			if (!empty($_REQUEST['report_date']))
			{
				$tpl->assign('printdate', $_REQUEST['report_date'] . ' 00:00:00');
			}
			else
			{
				$tpl->assign('printdate', $printdate);
			}
		}
		if (defined('HOME_SITE_SERVER')) $tpl->assign('HOME_SITE_SERVER', TRUE);

	}

}
?>