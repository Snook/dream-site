<?php
require_once("Config.inc");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CBrowserSession.php");
require_once("CLog.inc");
require_once("CPage.inc");
require_once("CCart2.inc");
require_once("CAppUtil.inc");
require_once('form/login.php');

require_once('CGPC.inc');

class CApp
{

	private $_template = null;

	/**
	 * Is it the store view or the web view
	 */
	static public $isStoreView = false;
	static public $adminView = false;

	static private $_instance = null;

	static function instance()
	{
		return self::$_instance;
	}

	function template()
	{
		return $this->_template;
	}

	function fakeTemplate() // for running tests
	{
		require_once 'CTemplate.inc';
		$this->_template = new CTemplate();
	}


	function __construct()
	{

		//if (DEBUG)
		//{
			register_shutdown_function(function () {
				$error = error_get_last();
				if (null !== $error)
				{
					if (!in_array($error['type'], array(2, 8)))
					{
						require_once("CLog.inc");
						CLog::RecordNew('ERROR', "FATAL ERROR: " . print_r($error, true), "", "", true);
					}
				}
			});
		//}

		self::$_instance = $this;
	}

	static function forceSecureConnection()
	{
		if (substr(HTTPS_SERVER, 0, 5) == 'https')
		{
			if (!self::isSecureConnection())
			{
				self::bounce(HTTPS_SERVER . $_SERVER['REQUEST_URI']);
			}
		}
	}

	static function isSecureConnection()
	{
		if (($_SERVER['SERVER_PORT'] == 443) || ($_SERVER['SERVER_PORT'] == 8443) || ($_SERVER['SERVER_PORT'] == 9443))
		{
			return true;
		}

		return false;
	}

	static function approveDirective($locationRequest)
	{
		if (strpos($locationRequest, "../") !== false)
		{
			header("HTTP/1.1 403 Forbidden");
			echo "Forbidden";
			exit(0);
		}

		if (strpos(strtolower($locationRequest), "<script>") !== false)
		{
			header("HTTP/1.1 403 Forbidden");
			echo "Forbidden";
			exit(0);
		}

		// Check for malicious "back" param
		if (isset($_REQUEST['back']))
		{
			$cleanedBackURL = CGPC::do_clean($_REQUEST['back'], TYPE_STR, true);
			if (isset($_GET['back']))
			{
				$_GET['back'] = $cleanedBackURL;
			}

			$_REQUEST['back'] = $cleanedBackURL;
		}

		if (defined('HOSTED_AS_REPORTING_SERVER') && HOSTED_AS_REPORTING_SERVER)
		{

			// If we are hosting as an emergency reporting server then check a white list and return if legal
			if (defined('DESIGNATE_AS_EMERGENCY_REPORTING_SERVER') && DESIGNATE_AS_EMERGENCY_REPORTING_SERVER)
			{
				if (strpos($locationRequest, "admin_emergency_main") !== false || strpos($locationRequest, "admin_fadmin_home") !== false || strpos($locationRequest, "admin_order_details_view_all_multi") !== false || strpos($locationRequest, "admin_order_details_view_all") !== false || strpos($locationRequest, "admin_order_mgr_processor") !== false || strpos($locationRequest, "admin_list_users") !== false || strpos($locationRequest, "admin_user_details") !== false || strpos($locationRequest, "admin_order_history") !== false || strpos($locationRequest, "admin_nutritional_summary") !== false || strpos($locationRequest, "admin_session_tools_printing") !== false || strpos($locationRequest, "admin_order_details_view_all_future_multi") !== false || strpos($locationRequest, "admin_user_plate_points") !== false || strpos($locationRequest, "print") !== false || strpos($locationRequest, "admin_finishing_touch_printable_form") !== false)
				{
					return;
				}
			}

			// otherwise check the standard reporting server list and bail if not found
			if (strpos($locationRequest, "admin_reports") === false && strpos($locationRequest, "admin_dashboard_new") === false && strpos($locationRequest, "admin_home_office") === false && strpos($locationRequest, "admin_dashboard_menu_based") === false && strpos($locationRequest, "admin_food_sales") === false && strpos($locationRequest, "admin_fundraiser") === false)
			{
				if (strpos($locationRequest, "admin_login") === false)
				{
					CApp::bounce('?page=admin_reports');
				}
			}
		}
	}

	static function isAdminView()
	{
		if (self::$adminView)
		{
			return true;
		}

		return false;
	}

	function run($page)
	{
		$page = str_replace('-', '_', $page);

		// Check to see if there is a page class file, otherwise fall to static page
		if (!$this->fileCheck_page($page))
		{
			return self::runStatic($page);
		}

		//if admin, then require https
		$isAdmin = substr($page, 0, 6) == 'admin_';

		// A new mechansim that allows pages to be integrated with fadmin but
		// only deployed to the Home Office reporting server
		// To add a page place it /DreamSite/ho_page and name it starting with "home_office_"
		// and name the class starting "admin_home_office_".
		// Links to the page will begin "admin_home_office_"
		$isHomeOfficeReporting = substr($page, 0, 18) == 'admin_home_office_';

		// Note: all traffic is currently forced to https via .htaccess file
		// However leave this check in case of failure at the htaccess level [CES 4/6/17]
		if ($isAdmin)
		{
			self::forceSecureConnection();
		}

		if (!$this->isFrameCompatibilityRequired($page))
		{
			header("X-Frame-Options: DENY");
		}

		try
		{
			require_once 'CTemplate.inc';

			$this->_template = new CTemplate();

			//handle authentication and get the current user info
			$LoginForm = form_login::BuildAndProcessForm();

			if (RECORD_NAVIGATION)
			{ // need to call after BuildAndProcessForm
				CLog::RecordNavigation($page, "PAGE");
			}

			if (!DEBUG)
			{
				set_error_handler('pageNotFound');
			}

			$this->_template->assign('page', $page);

			if ($isHomeOfficeReporting)
			{
				$adminpage = substr($page, 6);

				$this->_template->addPath('template', HO_TEMPLATE_PATH);

				self::$adminView = true;

				//load the logic for the page
				// if theme override exists, use it
				if (file_exists(APP_BASE . 'ho_page/' . THEME . '/' . $adminpage . '.php'))
				{
					require_once('ho_page/' . THEME . '/' . $adminpage . '.php');
				}
				else
				{
					require_once('ho_page/' . $adminpage . '.php');
				}

				$pageClass = 'page_' . $page;
			}
			else if ($isAdmin)
			{
				$adminpage = substr($page, 6);
				self::$adminView = true;

				//load the logic for the page
				// if theme override exists, use it
				if (file_exists(APP_BASE . 'page/admin/' . THEME . '/' . $adminpage . '.php'))
				{
					require_once('page/admin/' . THEME . '/' . $adminpage . '.php');
				}
				else if (file_exists(APP_BASE . 'page/admin/' . $adminpage . '.php'))
				{
					require_once('page/admin/' . $adminpage . '.php');
				}
				else
				{
					CApp::bounce('?page=not_found');
				}

				$pageClass = 'page_' . $page;
			}
			else
			{
				self::$adminView = false;

				//load the logic for the page
				// if theme override exists, use it
				if (file_exists(APP_BASE . 'page/customer/' . THEME . '/' . $page . '.php'))
				{
					require_once('page/customer/' . THEME . '/' . $page . '.php');
				}
				else if (file_exists(APP_BASE . 'page/customer/' . $page . '.php'))
				{
					require_once('page/customer/' . $page . '.php');
				}
				else
				{
					CApp::bounce('?page=not_found');
				}

				$pageClass = 'page_' . $page;
			}

			$pageLogic = new $pageClass();

			CGPC::gpc_vars($pageLogic->use_GPC);

			if (!DEBUG)
			{
				restore_error_handler();
			}

			switch (strtolower(CUser::getCurrentUser()->user_type))
			{
				case 'customer':
					$pageLogic->runCustomer();
					break;

				case 'franchise_staff':
					$pageLogic->runFranchiseStaff();
					break;

				case 'guest_server':
					$pageLogic->runFranchiseStaff();
					break;

				case 'franchise_lead':
					$pageLogic->runFranchiseLead();
					break;

				case 'franchise_manager':
					$pageLogic->runFranchiseManager();
					break;

				case 'franchise_owner':
					$pageLogic->runFranchiseOwner();
					break;

				case 'home_office_staff':
					$pageLogic->runHomeOfficeStaff();
					break;

				case 'home_office_manager':
					$pageLogic->runHomeOfficeManager();
					break;

				case 'site_admin':
					$pageLogic->runSiteAdmin();
					break;

				case 'manufacturer_staff':
					$pageLogic->runManufacturerStaff();
					break;

				case 'event_coordinator':
					$pageLogic->runEventCoordinator();
					break;

				case 'ops_lead':
					$pageLogic->runOpsLead();
					break;

				case 'ops_support':
					$pageLogic->runOpsSupport();
					break;

				case 'dishwasher':
					$pageLogic->runDishwasher();
					break;

				case 'new_employee':
					$pageLogic->runNewEmployee();
					break;

				default:
					$pageLogic->runPublic();
					break;
			}

			//render the template
			$this->_template->assign('form_login', $LoginForm->Render());

			if ((array_key_exists('export', $_GET) !== false))
			{
				if ($_GET['export'] == 'csv')
				{
					require_once('CSV.inc');
					$csvObj = new CSV();

					$rows = $this->_template->rows;
					$fileName = $this->_template->page;
					$titleRows = isset($this->_template->title_rows) ? $this->_template->title_rows : false;
					$colDescriptions = isset($this->_template->col_descriptions) ? $this->_template->col_descriptions : false;

					if (isset($this->_template->labels))
					{
						$header = $this->_template->labels;
					}
					else
					{
						$header = array_keys(reset($rows));
					}

					if ((array_key_exists('csvfilename', $_GET) !== false))
					{
						$fileName = $_GET['csvfilename'];
					}

					if ((array_key_exists('hideheaders', $_GET) !== false) && ($_GET['hideheaders'] == 'true'))
					{
						$csvObj->writeCSVFile($fileName, $header, $rows, false, $titleRows, $colDescriptions);
					}
					else
					{
						$csvObj->writeCSVFile($fileName, $header, $rows, true, $titleRows, $colDescriptions);
					}
				}

				if ($_GET['export'] == 'xlsx')
				{
					require_once('ExcelExport.inc');

					$showHeader = false;
					if (!empty($this->_template->labels))
					{
						$header = $this->_template->labels;
						$showHeader = true;
					}
					else
					{
						$header = false;
					}

					$headersAreEmbedded = false;
					if (!empty($this->_template->headersAreEmbedded))
					{
						$headersAreEmbedded = true;
					}

					$rows = $this->_template->rows;
					$fileName = isset($this->_template->file_name) ? $this->_template->file_name : $this->_template->page;
					$titleRows = isset($this->_template->title_rows) ? $this->_template->title_rows : false;
					$sectionHeader = isset($this->_template->sectionHeader) ? $this->_template->sectionHeader : false;
					$callbacks = isset($this->_template->excel_callbacks) ? $this->_template->excel_callbacks : false;
					$colDescriptions = isset($this->_template->col_descriptions) ? $this->_template->col_descriptions : false;
					$suppressLabelsDisplay = isset($this->_template->suppressLabelsDisplay);
					$overrideValues = isset($this->_template->override_values) ? $this->_template->override_values : false;
					$useLib1_8 = !empty($this->_template->useLib1_8) ? true : false;


					writeExcelFile($fileName, $header, $rows, $showHeader, $titleRows, $colDescriptions, $headersAreEmbedded, $callbacks, $sectionHeader, false, $suppressLabelsDisplay, $overrideValues, $useLib1_8);
				}
			}
			else
			{
				if ($isHomeOfficeReporting)
				{
					$error = $this->_template->display($adminpage . '.tpl.php');
				}
				else if ($isAdmin)
				{
					$error = $this->_template->display('admin/' . $adminpage . '.tpl.php');
				}
				else
				{
					$error = $this->_template->display('customer/' . $page . '.tpl.php');
				}
			}

			if (!empty($error))
			{
				if (DEBUG)
				{
					echo '<pre>';
					var_dump($error->text);
					echo '</pre>';
				}

				$this->_template->setDebugMsg("<pre>" . $error->text . "</pre>");

				pageNotFound(E_ERROR, 'not found', '', '');
				//throw new Exception('template error');
			}
		}
		catch (exception $e)
		{
			if (!DEBUG)
			{
				exceptionHandler($e);
			}
			else
			{
				throw $e;
			}
		}
	}

	function runStatic($page)
	{
		$page = str_replace('-', '_', $page);

		if (RECORD_NAVIGATION)
		{
			CLog::RecordNavigation($page, "STATIC");
		}

		try
		{
			header("x-frame-options: DENY");

			require_once 'CTemplate.inc';
			$this->_template = new CTemplate();

			// Static file doesn't exist
			// Check to see if store shortcut
			if (!$this->fileCheck_static($page))
			{
				$DAO_store = DAO_CFactory::create('store', true);
				$DAO_store->id = str_replace('_', '-', $page); // revert underscore to dash

				if ($DAO_store->find_DAO_store(true))
				{
					CApp::bounce($DAO_store->getPrettyUrl());
				}
			}

			//handle authentication and get the current user info
			$LoginForm = form_login::BuildAndProcessForm();

			$this->_template->assign('page', $page);

			//render the template
			$this->_template->assign('form_login', $LoginForm->Render());

			if (!DEBUG)
			{
				set_error_handler('pageNotFound');
			}

			$error = $this->_template->display('customer/static/' . $page . '.tpl.php');

			if (!DEBUG)
			{
				restore_error_handler();
			}

			if (!empty($error))
			{
				if (DEBUG)
				{
					echo '<PRE>';
					var_dump($error->text);
					echo '</PRE>';
				}

				$this->_template->setDebugMsg("<PRE>" . $error->text . "</PRE>");

				pageNotFound(E_ERROR, 'not found', '', '');
			}
		}
		catch (exception $e)
		{
			if (!DEBUG)
			{
				exceptionHandler($e);
			}
			else
			{
				throw $e;
			}
		}

		return true;
	}

	function isFrameCompatibilityRequired($processor)
	{

		if (strpos($processor, "payflow") !== false)
		{
			return true;
		}

		if (strpos($processor, "reports_financial_performance") !== false)
		{
			return true;
		}

		if (strpos($processor, "reports_growth_scorecard") !== false)
		{
			return true;
		}

        if (strpos($processor, "reports_session_host") !== false)
        {
            return true;
        }

        if (strpos($processor, "reports_growth_dashboard") !== false)
        {
            return true;
        }

        if (strpos($processor, "reports_menu_skipping") !== false)
        {
            return true;
        }

		if (strpos($processor, "reports_cornerstone_movement_contest") !== false)
		{
			return true;
		}


		return false;
	}

	function fileCheck_page($exeName, $isProcess = false)
	{
		$valid = true;

		$isAdmin = substr($exeName, 0, 6) == 'admin_';

		$isHomeOfficeReporting = substr($exeName, 0, 18) == 'admin_home_office_';

		if ($isProcess)
		{
			if ($isAdmin)
			{
				$exeName = substr($exeName, 6);
				$fullPath = APP_BASE . 'processor/admin/' . $exeName . '.php';
			}
			else
			{
				$fullPath = APP_BASE . 'processor/' . $exeName . '.php';
			}
		}
		else
		{
			if ($isHomeOfficeReporting)
			{
				$exeName = substr($exeName, 6);
				$fullPath = APP_BASE . 'ho_page/' . $exeName . '.php';
			}
			else if ($isAdmin)
			{
				$exeName = substr($exeName, 6);
				$fullPath = APP_BASE . 'page/admin/' . $exeName . '.php';
			}
			else
			{
				$fullPath = APP_BASE . 'page/customer/' . $exeName . '.php';
			}
		}

		// only allow legal file system characters
		if (!preg_match('/^[\/\w\-. ]+$/', $exeName))
		{
			$valid = false;
		}

		if ($valid)
		{
			if (!file_exists($fullPath))
			{
				$valid = false;
			}
		}

		return $valid;
	}

	function fileCheck_static($page)
	{
		if (!$this->_template->findFile('template', 'customer/static/' . $page . '.tpl.php'))
		{
			return false;
		}

		return true;
	}

	function executableFileCheck($exeName, $isProcess = false)
	{
		if (!$this->fileCheck_page($exeName, $isProcess))
		{
			header("HTTP/1.1 403 Forbidden");
			echo "Forbidden";
			exit(0);
		}

	}



	/* NOTE: This function was added to Support secure AJAX calls in the franchise administration
	side of the code base. AJAX must authenticate just like any other page in our back end system
	*/
	function process($processor)
	{

		// File check: Do this early for efficency and so a malicious processor string can do no harm
		// and send back an error rather than a file not found
		$this->executableFileCheck($processor, true);

		//self::forceSecureConnection();
		CLog::RecordDebugTrace($processor, "PROC_REQUEST");

		if (!$this->isFrameCompatibilityRequired($processor))
		{
			header("X-Frame-Options: DENY");
		}

		try
		{
			require_once 'CTemplate.inc';
			$this->_template = new CTemplate();

			$browserSession = CBrowserSession::instance();
			$user = CUser::getCurrentUser();

			if (CBrowserSession::isPrevious())
			{
				$user->joinAdd($browserSession);
				$user->selectAdd();
				$user->selectAdd('user.*');

				if ($browserSession->isPrevious && $user->find(true) && $user->id)
				{
					$user->setLogin();
					$browserSession->find(true);

					if ($user->isFranchiseAccess())
					{
						$initialStore = !empty($browserSession->current_store_id) ? $browserSession->current_store_id : $user->getInitialFranchiseStore();
						CStore::setUpFranchiseStore($initialStore);
					}
				}
				else
				{
					$user->firstname = "Guest";
					$user->id = 0;
				}
			}

			if (RECORD_NAVIGATION)
			{
				CLog::RecordNavigation($processor, "PROCESS");
			}

			$isAdmin = substr($processor, 0, 6) == 'admin_';

			if ($isAdmin)
			{
				$processorFileName = substr($processor, 6);
				require_once('processor/admin/' . $processorFileName . '.php');
			}
			else
			{
				require_once('processor/' . $processor . '.php');
			}

			$processorClass = 'processor_' . $processor;
			$processorLogic = new $processorClass();

			if ($isAdmin)
			{
				// before calling run but after instantiating the processor class -call cleanInputs
				// the processor constructor can provide a type map for input or defeat the mechanism
				$processorLogic->cleanInput();
			}

			if (!DEBUG)
			{
				restore_error_handler();
			}

			switch (strtolower(CUser::getCurrentUser()->user_type))
			{
				case 'customer':
					$processorLogic->runCustomer();
					break;

				case 'franchise_staff':
					$processorLogic->runFranchiseStaff();
					break;

				case 'guest_server':
					$processorLogic->runFranchiseStaff();
					break;

				case 'franchise_lead':
					$processorLogic->runFranchiseLead();
					break;

				case 'franchise_manager':
					$processorLogic->runFranchiseManager();
					break;

				case 'franchise_owner':
					$processorLogic->runFranchiseOwner();
					break;

				case 'home_office_staff':
					$processorLogic->runHomeOfficeStaff();
					break;

				case 'home_office_manager':
					$processorLogic->runHomeOfficeManager();
					break;

				case 'site_admin':
					$processorLogic->runSiteAdmin();
					break;

				case 'manufacturer_staff':
					$processorLogic->runManufacturerStaff();
					break;

				case 'event_coordinator':
					$processorLogic->runEventCoordinator();
					break;

				case 'ops_lead':
					$processorLogic->runOpsLead();
					break;

				case 'ops_support':
					$processorLogic->runOpsSupport();
					break;

				case 'dishwasher':
					$processorLogic->runDishwasher();
					break;

				case 'new_employee':
					$processorLogic->runNewEmployee();
					break;

				default:
					$processorLogic->runPublic();
					break;
			}
		}
		catch (exception $e)
		{
			if (!DEBUG)
			{
				exceptionHandler($e);
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Forward
	 *
	 * By default, return the browser to the previous page or main menu or something, user must
	 * have gone some place verboten.
	 *
	 * @param $tryInsecure will try to go back to http if currently on a secure page.ie. going from login back to insecure page
	 */
	static function bounce($page = '/', $tryInsecure = false)
	{
		$serverPrefix = '';
		$q_params = '';
		$isAdmin = false;

		//		if ($tryInsecure) {
		//			//if admin, then require https
		//			$isAdmin = strstr($page, 'page=admin_');
		//			if ( !$isAdmin ) {
		//				$serverPrefix = HTTPS_BASE;
		//				if ( self::$isStoreView )
		//					$serverPrefix .= 'storeview/';
		//			}
		//		}
		//

		// maintain email conversion query parameters if being bounced
		$get_params = $_GET; // copy get params
		$parts = parse_url($page); // parse params from bounce target

		// vars to maintain through bounce
		$conversionVars = array ('sfmc_sub', 'j', 'l', 'u', 'mid', 'jb');

		if (isset($parts['query']))
		{
			parse_str($parts['query'], $query); // form array of target query parameters

			$isAdmin = (!empty($query['page']) && substr($query['page'], 0, 6) == 'admin_');

			// remove parameters from get that are in the target so there are no duplicates
			foreach ($query AS $key => $value)
			{
				unset($get_params[$key]);
			}

			// filter all but conversion vars
			foreach ($get_params as $key => $value)
			{
				if (!in_array($key, $conversionVars))
				{
					unset($get_params[$key]);
				}
			}

			// put the query array back into string
			if (!empty($get_params))
			{
				$q_params = http_build_query($get_params);
			}
			// end maintain email conversion
		}

		if (RECORD_NAVIGATION)
		{
			CLog::RecordNavigation($page, "BOUNCE");
		}

		if (CApp::instance() && CApp::instance()->template() && isset(CApp::instance()->template()->back))
		{
			$back = CApp::instance()->template()->back;
			$location = $serverPrefix . $back;
		}
		else
		{
			$location = $serverPrefix . $page;
		}

		// check to see if there are already query parameters in the location, if so use & instead of ?
		if (!empty($get_params) && !empty($q_params))
		{
			if (strpos($location, '?'))
			{
				$q_params = '&' . $q_params;
			}
			else
			{
				$q_params = '?' . $q_params;
			}
		}

		if (!$isAdmin)
		{
			$location = $location . $q_params;
		}

		header('location: ' . $location);

		exit();
	}

	// overridePage
	// this method can only be used to override pages when
	// in franchise admin view.
	// it must be called specifically by the page if you need
	// page specific control for a person by person basis

	static function overrideAdminPage($allowCorporateStoreManagers = false, $currentStore = null)
	{
		if (!CUser::isLoggedIn())
		{
			return false;
		}

		$pagename = ltrim(CApp::instance()->template()->page, 'admin_');

		if (CApp::$adminView == false)
		{
			return false;
		}

		$canOverride = false;
		$user = CUser::getCurrentUser();
		$pageuser = DAO_CFactory::create('access_control_page_user');
		$pageitem = DAO_CFactory::create('access_control_page');
		$pageuser->joinAdd($pageitem);
		$pageuser->whereAdd("access_control_page_user.user_id = $user->id and access_control_page.page_name = '$pagename'");
		$pageuser->selectAdd();
		$pageuser->selectAdd("access_control_page_user.id");
		$rslt = $pageuser->find(true);

		if ($rslt > 0)
		{
			$canOverride = true;
		}

		if (!$canOverride && $allowCorporateStoreManagers)
		{
			if ($user->user_type == 'FRANCHISE_MANAGER' || $user->user_type == 'OPS_LEAD')
			{
				$storeObj = DAO_CFactory::create('store');
				$storeObj->query("select is_corporate_owned from store where id = $currentStore");
				$storeObj->fetch();

				if ($storeObj->is_corporate_owned)
				{
					$canOverride = true;
				}
			}
		}

		return $canOverride;
	}

	static function directAccessControlTest($page_or_section_name_id, $user_id)
	{
		if (!CUser::isLoggedIn())
		{
			return false;
		}

		if (CApp::$adminView == false)
		{
			return false;
		}

		if (!is_numeric($page_or_section_name_id))
		{
			return false;
		}

		if (!is_numeric($user_id))
		{
			return false;
		}

		$pageuser = DAO_CFactory::create('access_control_page_user');
		$pageuser->query("select id from access_control_page_user where user_id = $user_id and access_control_page_id = $page_or_section_name_id and is_deleted = 0");
		if ($pageuser->N > 0)
		{
			return true;
		}

		return false;
	}

	static function forceLogin($rtnPage = false)
	{
		if (CUser::isLoggedIn())
		{
			return;
		}

		if ($rtnPage)
		{
			self::bounce('?page=login&back=' . urlencode($rtnPage));
		}
		else
		{
			self::bounce('?page=login&back=?' . urlencode($_SERVER['QUERY_STRING']));
		}

		exit();
	}

	static function forceLocationChoice($rtnPage = false)
	{
		$store = CBrowserSession::getCurrentFadminStore();

		if ($store)
		{
			return $store;
		}

		if ($rtnPage)
		{
			self::bounce('?page=admin_location_switch&back=' . urlencode($rtnPage));
		}
		else
		{
			self::bounce('?page=admin_location_switch&back=?' . urlencode($_SERVER['QUERY_STRING']));
		}

		exit();
	}

	static function getFreshDeskFirstName()
	{
		$agents = array(
			216654,
			212112,
			825206,
			838889,
			879370
		);
		$referrals = array(
			814157,
			172281,
			775477,
			400252,
			329601,
			5890,
			763913,
			308942,
			653362,
			323288,
			786354,
			794549,
			662598,
			471832,
			374766,
			727111,
			3862,
			814792,
			815122,
			819204,
			831304,
			838427,
			838435,
			739956,
			871600,
			856342,
			878667,
			878670
		);
		$outliers = array(
			759207,
			787790,
			411739,
			772324,
			727502,
			719902,
			186201,
			757908,
			786546,
			801860,
			46688,
			797165,
			578399
		);

		$user_id = CUser::getCurrentUser()->id;

		if (in_array($user_id, $agents))
		{
			return "A_" . CUser::getCurrentUser()->firstname;
		}
		else if (in_array($user_id, $referrals))
		{
			return "R_" . CUser::getCurrentUser()->firstname;
		}
		else if (in_array($user_id, $outliers))
		{
			return "Z_" . CUser::getCurrentUser()->firstname;
		}
		else
		{
			return CUser::getCurrentUser()->firstname;
		}
	}


	static function signupOrLoginToFreshDeskSupportPortalNew($request, $ts = false, $from_login = false, $dest_url = "support.dreamdinners.com")
	{
		if ((CUser::isLoggedIn() || $from_login) && defined('REMAUTHSTRNEW') && CUser::getCurrentUser()->user_type != CUser::CUSTOMER)
		{

			$remoteauthkey = REMAUTHSTRNEW;

			$redirectURL = $_REQUEST['redirect_uri'];
			if( isset($redirectURL) && !empty($redirectURL) ){

				$nonce = $_REQUEST['nonce'];
				$clientID = $_REQUEST['client_id'];
				$response_type = $_REQUEST['response_type'];
				$scope = $_REQUEST['scope'];
				$state = $_REQUEST['state'];

				$header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
				$encoded_header = str_replace('=', '', strtr(base64_encode($header), '+/', '-_'));


				$email = CUser::getCurrentUser()->primary_email;
				$firstName = CApp::getFreshDeskFirstName();

				$payload = json_encode(['sub' => CUser::getCurrentUser()->id,
										"given_name"=> $firstName,
										"family_name"=> CUser::getCurrentUser()->lastname,
										"email"=> $email,
										"iat"=> time(),
										"nonce"=> $nonce]);

				$encoded_payload = str_replace('=', '', strtr(base64_encode($payload), '+/', '-_'));

				$dataToSign = $encoded_header . '.' . $encoded_payload;

				openssl_sign($dataToSign, $signature, $remoteauthkey, OPENSSL_ALGO_SHA256);
				$encoded_signature = str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));

				$data =  'client_id=' . $clientID;
				$data .=  '&state=' . $state;
				$data .=  '&id_token=' . $dataToSign . '.' . $encoded_signature;

				$redirect = $redirectURL . '?' . $data;

				CApp::bounce($redirect);
				exit;
			}
			else
			{
				$tpl = new CTemplate();
				$tpl->assign("There was a problem accessing the Help Desk Portal");
			}

		}
		else
		{
			$tpl = new CTemplate();
			$tpl->assign("There was a problem accessing the Help Desk Portal");
		}
	}

	static function signupOrLoginToFreshDeskSupportPortal($ts = false, $from_login = false, $dest_url = "support.dreamdinners.com")
	{
		if ((CUser::isLoggedIn() || $from_login) && defined('REMAUTHSTR') && CUser::getCurrentUser()->user_type != CUser::CUSTOMER)
		{

			$remoteauthkey = REMAUTHSTR;

			$email = CUser::getCurrentUser()->primary_email;
			$fullName = CApp::getFreshDeskFullName();

			if (!$ts)
			{
				$ts = strtotime(date("Y-m-d H:i:s"));
			}

			$rawString = $fullName . $remoteauthkey . $email . $ts;

			$encodedAPIKey = hash_hmac("md5", $rawString, $remoteauthkey);

			$redirectURL = "https://" . $dest_url . "/login/sso?" . "name=" . urlencode(utf8_encode($fullName)) . "&email=" . urlencode(utf8_encode($email)) . "&timestamp=" . urlencode(utf8_encode($ts)) . "&hash=" . $encodedAPIKey;

			CApp::bounce($redirectURL);
			exit;
		}
		else
		{
			$tpl = new CTemplate();
			$tpl->assign("There was a problem accessing the Help Desk Portal");
		}
	}
} // end CApp

function pageNotFound($errno, $errstr, $errfile, $errline)
{
	if ($errno == E_ERROR || $errno == E_WARNING)
	{
		CLog::RecordNew(CLog::DEBUG, $GLOBALS['domainConfig']['theme'] . ' 404 Not Found: ' . $_SERVER['REQUEST_URI']);
		CApp::bounce('404.php');
	}
}

// set to the user defined error handler
if (true/*!DEBUG*/)
{
	$old_error_handler = set_error_handler("errorHandler");
}

function errorHandler($errno, $errstr, $errfile, $errline)
{

	if ($errno == 0)
	{
		return true;
	}

	try
	{
		$tpl = @CApp::instance()->template();

		switch ($errno)
		{
			case E_USER_ERROR:
			case E_ERROR:
			case E_PARSE:
				CLog::RecordNew(CLog::ERROR, $errstr, $errfile, $errline);
				if ($tpl)
				{
					$tpl->setDebugMsg("<PRE><b>ERROR</b> [$errno] $errstr<br />\n</PRE><PRE> Fatal error in line $errline of file $errfile</PRE>");
					$tpl->display('error.tpl.php');
				}
				break;
			case E_USER_WARNING:
			case E_WARNING:
				CLog::RecordNew(CLog::WARNING, $errstr, $errfile, $errline);
				if ($tpl)
				{
					$tpl->setDebugMsg("<PRE><b>WARNING</b> [$errno] $errstr<br />\n</PRE><PRE> Warning in line $errline of file $errfile</PRE>");
				}
				break;
			case E_USER_NOTICE:
			case E_NOTICE:
				if (defined('DEBUG_TRACE_LEVEL') && DEBUG_TRACE_LEVEL > 1)
				{
					CLog::RecordNew(CLog::NOTICE, $errstr, $errfile, $errline);
				}
				break;
			case E_STRICT:
				//				{
				//					if (strpos($errstr, "should be compatible with that" ) === false)
				//					CLog::RecordNew(CLog::STRICT,$errstr, $errfile, $errline);
				//				}
				break;

			default:
				CLog::RecordNew(CLog::UNHANDLEDEXCEPTION, $errstr, $errfile, $errline);
				//echo($errno. $errstr. $errfile. $errline);
				break;
		}
	}
	catch (exception $f)
	{
		//eat it?
	}

	return true;
}

function exceptionHandler($e)
{
	try
	{
		//forward to error page, log error
		//log this mofo
		//CLog::RecordException($e);
		CLog::RecordNew(CLog::UNHANDLEDEXCEPTION, $e->getMessage() . $e->getFile() . ': ' . $e->getLine() . ': ' . $e->getCode(), '', '', true);
	}
	catch (exception $f)
	{
		//eat it?
	}

	if (DEBUG)
	{
		throw $e;
	}

	$tpl = @CApp::instance()->template();

	if ($tpl)
	{
		$tpl->setDebugMsg("<PRE>" . $e->getMessage() . "</PRE><PRE>" . $e->getCode() . "</PRE><PRE>Line: " . $e->getLine() . "</PRE>");
		$tpl->setDebugMsg("<PRE>File: " . $e->getFile() . "</PRE><PRE>" . $e->getTraceAsString() . "</PRE>");
		if ($_REQUEST && isset($_REQUEST['page']) && (substr($_REQUEST['page'], 0, 6) == 'admin_'))
		{
			$tpl->display('admin/error.tpl.php');
		}
		else
		{
			$tpl->display('error.tpl.php');
		}
	}
}

?>