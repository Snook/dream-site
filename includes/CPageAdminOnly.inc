<?php

/**
 * CPage member functions are called by CApp based on the user's access level.
 *
 * Each page needs to override the run methods based on the functionality that is
 * available for each access group. Access is by default hierarchical.
 */
abstract class CPageAdminOnly
{
	protected $CurrentStore = null;
	protected $CurrentUser = null;
	protected $CurrentFutureMenus = null;
	protected $CurrentFutureMenusDelivered = null;

	protected $Template = null;

	public $use_GPC = false;

	function cleanPageName($inName)
	{
		$isAdmin = substr($inName, 0, 6) == 'admin_';
		if ($isAdmin)
		{
			$inName = substr($inName, 6);
		}

		$inName = str_replace("_", " ", $inName);
		$inName = ucwords($inName);

		return urlencode($inName);
	}

	//CES: ACCESS_CHANGE
	function __construct()
	{
		$this->CurrentStore = CBrowserSession::getCurrentFadminStoreObj();
		$this->CurrentUser = CUser::getCurrentUser();
		$this->CurrentFutureMenus = CMenu::getCurrentAndFutureMenuObj();
		$this->CurrentFutureMenusDelivered = CMenu::getCurrentAndFutureDeliveredMenuObj();

		$this->Template = CApp::instance()->template();
		if (isset($this->Template))
		{
			$this->Template->assign('UserCurrent', $this->CurrentUser);
			$this->Template->assign('StoreCurrent', $this->CurrentStore);
		}

		if (CApp::instance()->template()->page != 'admin_location_switch' && CApp::instance()->template()->page != 'admin_access_agreement')
		{
			//make sure current store is set as one of the fadmin's stores
			if ($this->CurrentUser->isFranchiseAccess())
			{
				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->query('SELECT store.* FROM store,user_to_store WHERE user_to_store.store_id = store.id AND user_to_store.user_id=' . $this->CurrentUser->id . ' and user_to_store.is_deleted = 0');

				$found = false;

				while ($StoreObj->fetch())
				{
					if (CBrowserSession::getCurrentFadminStore() == $StoreObj->id)
					{
						$found = true;
					}
				}

				if (!$found)
				{
					CApp::bounce('/backoffice/location_switch?back=/backoffice/main');
				}

				if (empty($this->CurrentUser->fadmin_nda_agree))
				{
					CApp::bounce('/backoffice/access-agreement?back=' . urlencode('?' . $_SERVER['QUERY_STRING']));
				}
			}
		}
	}

	function runPublic()
	{
		//bump back to the main menu
		$back = "?" . $_SERVER['QUERY_STRING'];
		$url = "/backoffice/login?back=" . urlEncode($back);
		CApp::bounce($url);
		exit;
	}

	function runCustomer()
	{
		//bump back to the main menu
		CApp::instance()->template()->setErrorMsg('You do not have permission to access the requested page.');
		CApp::bounce('/');
		exit;
	}

	function runNewEmployee()
	{
		unset(CApp::instance()->template()->back);
		CApp::bounce('/backoffice/safe_landing');
		exit;
	}

	function runFranchiseStaff()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runFranchiseOwner()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runHomeOfficeManager()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runHomeOfficeStaff()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runFranchiseManager()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runFranchiseLead()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runManufacturerStaff()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runEventCoordinator()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runOpsLead()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runOpsSupport()
	{
		CApp::bounce('/backoffice/access-error?pagename=' . $this->cleanPageName($_GET['page']));
		exit;
	}

	function runDishwasher()
	{
		unset(CApp::instance()->template()->back);
		CApp::bounce('/backoffice/safe_landing');
		exit;
	}

	abstract function runSiteAdmin();


	private function handleSpecialParamType($k, $v)
	{
		if ($k == 'store' || $k == 'store_id')
		{
			if (is_numeric($v))
			{
				return CGPC::do_clean($v, TYPE_INT);
			}
			else
			{
				return CGPC::do_clean($v, TYPE_STR);
			}
		}

		return CGPC::do_clean($v, TYPE_STR);

	}

	function cleanReportInputs()
	{
		$reportParamsTypeMap = array(
			'allFTTotalMinusTaste'  => TYPE_NUM,
			'also_show_cancelled_orders' => TYPE_STR,
			'back'  => TYPE_STR,
			'booking_id'  => TYPE_INT,
			'corp_store'  => TYPE_STR,
			'curMonthStr' => TYPE_DATE_YYYYMMDD,
			'date'  => TYPE_DATE_YYYYMMDD,
			'date_type'  => TYPE_STR,
			'day'  => TYPE_INT,
			'df_ACCT_CREATE_DATE'  => TYPE_STR,
			'df_ACTIVE'  => TYPE_STR,
			'df_ADDRESS'  => TYPE_STR,
			'df_DAYS_INACTIVE'  => TYPE_STR,
			'df_DR_LEVEL'  => TYPE_STR,
			'df_DR_STATUS'  => TYPE_STR,
			'df_EMAIL'  => TYPE_STR,
			'df_INSTRUCTIONS'  => TYPE_STR,
			'df_LAST_SESSION_ATTENDED'  => TYPE_STR,
			'df_NEXT_SESSION'  => TYPE_STR,
			'df_PHONE'  => TYPE_STR,
			'df_PROFILE_DATA'  => TYPE_STR,
			'dfa_All'  => TYPE_STR,
			'do'  => TYPE_STR,
			'duration'  => TYPE_STR,
			'export'  => TYPE_STR,
			'fastlane'  => TYPE_BOOL,
			'filter'  => TYPE_STR,
			'format'  => TYPE_STR,
			'ft_labels_to_print'  => TYPE_INT,
			'ft_menu_items'  => TYPE_INT,
			'ft_menus'  => TYPE_INT,
			'ft_report_submit'  => TYPE_STR,
			'guest_type'  => TYPE_STR,
			'guestCountTotalMinusTaste'  => TYPE_INT,
			'hide_inactive'  => TYPE_STR,
			'interface'  => TYPE_INT,
			'isCurrentMonth'  => TYPE_BOOL,
			'items'  => TYPE_INT,
			'label_action'  => TYPE_STR,
			'labels_to_print'  => TYPE_INT,
			'labels_per_sheet' => TYPE_INT,
			'ft_labels_per_sheet' => TYPE_INT,
			'menu_items'  => TYPE_INT,
			'menu_or_calendar'  => TYPE_STR,
			'menu_popup'  => TYPE_INT,
			'menuid'  => TYPE_INT,
			'menus'  => TYPE_INT,
			'month'  => TYPE_INT,
			'monthMode' => TYPE_STR,
			'month_from_month'  => TYPE_INT,
			'month_other_menu'  => TYPE_INT,
			'month_other_month'  => TYPE_INT,
			'month_popup'  => TYPE_INT,
			'month_single_month'  => TYPE_INT,
			'month_to_month'  => TYPE_INT,
			'months_back'  => TYPE_INT,
			'months_skipped'  => TYPE_INT,
			'multi_session'  => TYPE_DATE_YYYYMMDD,
			'nonTasteSessionCount'  => TYPE_INT,
			'omit_menu_id'  => TYPE_INT,
			'only_show_orders_with_balance_due'  => TYPE_STR,
			'order_by'  => TYPE_STR,
			'order_total'  => TYPE_NUM,
			'override_month' => TYPE_DATE_YYYYMMDD,
			'page'  => TYPE_STR,
			'pf_CASH'  => TYPE_STR,
			'pf_CC'  => TYPE_STR,
			'pf_CHECK'  => TYPE_STR,
			'pf_CREDIT'  => TYPE_STR,
			'pf_GIFT_CARD'  => TYPE_STR,
			'pf_GIFT_CERT_DONATED'  => TYPE_STR,
			'pf_GIFT_CERT_SCRIP'  => TYPE_STR,
			'pf_GIFT_CERT_STANDARD'  => TYPE_STR,
			'pf_GIFT_CERT_VOUCHER'  => TYPE_STR,
			'pf_PAY_AT_SESSION'  => TYPE_STR,
			'pf_REFUND'  => TYPE_STR,
			'pf_REFUND_CASH'  => TYPE_STR,
			'pf_REFUND_GIFT_CARD'  => TYPE_STR,
			'pf_REFUND_STORE_CREDIT'  => TYPE_STR,
			'pf_STORE_CREDIT'  => TYPE_STR,
			'pfa_All'  => TYPE_STR,
			'pickDate'  => TYPE_INT,
			'pickSession'  => TYPE_INT,
			'print'  => TYPE_BOOL,
			'printer'  => TYPE_INT,
			'quarter_popup'  => TYPE_INT,
			'query_submit'  => TYPE_INT,
			'range_day_end'  => TYPE_DATE_YYYYMMDD,
			'range_day_start'  => TYPE_DATE_YYYYMMDD,
			'range_start'  => TYPE_STR,
			'regions'  => TYPE_INT,
			'report_date'  => TYPE_DATE_YYYYMMDD,
			'report_depth'  => TYPE_STR,
			'report_export'  => TYPE_STR,
			'report_id'  => TYPE_STR,
			'report_submit'  => TYPE_STR,
			'report_type'  => TYPE_STR,
			'requested_stores'  => TYPE_STR,
			'run_report'  => TYPE_STR,
			'select_key'  => TYPE_STR,
			'session'  => TYPE_INT,
			'session_id'  => TYPE_INT,
			'show_comparisons'  => TYPE_STR,
			'single_date'  => TYPE_DATE_YYYYMMDD,
			'sort'  => TYPE_INT,
			'step'  => TYPE_INT,
			'store'  => 'TYPE_SPECIAL',
			'store_id'  => 'TYPE_SPECIAL',
			'store_array' => TYPE_STR,
			'store_type'  => TYPE_STR,
			'submit_report'  => TYPE_STR,
			'trade_area'  => TYPE_INT,
			'type'  => TYPE_STR,
			'use_cal_month'  => TYPE_STR,
			'week'  => TYPE_INT,
			'year'  => TYPE_INT,
			'year_field_001'  => TYPE_INT,
			'year_field_002'  => TYPE_INT,
			'year_from_month'  => TYPE_INT,
			'year_other_menu'  => TYPE_INT,
			'year_other_month'  => TYPE_INT,
			'year_popup'  => TYPE_INT,
			'year_single_month'  => TYPE_INT,
			'year_to_month'  => TYPE_INT,
			'year_week'  => TYPE_INT,
			'years_back_popup'  => TYPE_INT,
			'session_types'  => TYPE_ARRAY
		);

		foreach($_POST as $k => $v)
		{
			if (isset($reportParamsTypeMap[$k]))
			{
				if ($reportParamsTypeMap[$k] == 'TYPE_SPECIAL')
				{
					$_POST[$k] = $this->handleSpecialParamType($k, $v);
				}
				else
				{
					$_POST[$k] = CGPC::do_clean($_POST[$k], $reportParamsTypeMap[$k]);
				}
			}
			else
			{
				if (strpos($k, 'sl_') === 0)
				{
					$_POST[$k] = CGPC::do_clean($_POST[$k], TYPE_INT);
				}
				else
				{
					$_POST[$k] = CGPC::do_clean($_POST[$k], TYPE_STR);
					CLog::Record("UNHANDLED REPORT PARAM: " . $k . " -> " . $v);
				}
			}
		}
		foreach($_GET as $k => $v)
		{
			if (isset($reportParamsTypeMap[$k]))
			{
				if ($reportParamsTypeMap[$k] == 'TYPE_SPECIAL')
				{
					$_GET[$k] = $this->handleSpecialParamType($k, $v);
				}
				else
				{
					$_GET[$k] = CGPC::do_clean($_GET[$k], $reportParamsTypeMap[$k]);
				}
			}
			else
			{
				$_GET[$k] = CGPC::do_clean($_GET[$k], TYPE_STR);
				CLog::Record("UNHANDLED REPORT PARAM: " . $k . " -> " . $v);
			}
		}
		foreach($_REQUEST as $k => $v)
		{
			if (isset($reportParamsTypeMap[$k]))
			{
				if ($reportParamsTypeMap[$k] == 'TYPE_SPECIAL')
				{
					$_REQUEST[$k] = $this->handleSpecialParamType($k, $v);
				}
				else
				{
					$_REQUEST[$k] = CGPC::do_clean($_REQUEST[$k], $reportParamsTypeMap[$k]);
				}
			}
			else
			{
				if (strpos($k, 'sl_') === 0)
				{
					$_REQUEST[$k] = CGPC::do_clean($_REQUEST[$k], TYPE_INT);
				}
				else
				{
					$_REQUEST[$k] = CGPC::do_clean($_REQUEST[$k], TYPE_STR);
					CLog::Record("UNHANDLED REPORT PARAM: " . $k . " -> " . $v);
				}
			}
		}
	}

}

?>