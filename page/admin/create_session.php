<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/OrdersHelper.php");
require_once("includes/DAO/BusinessObject/CSession.php");
require_once("includes/DAO/BusinessObject/CMenu.php");
require_once("includes/DAO/BusinessObject/CFundraiser.php");
require_once("includes/DAO/BusinessObject/CDreamTasteEvent.php");
require_once("includes/DAO/BusinessObject/CBundle.php");
require_once("includes/DAO/BusinessObject/CEmail.php");

class page_admin_create_session extends CPageAdminOnly
{
	private $currentStore = null;

	private $can = array(
		'create_fundraiser' => false
	);

	public function __construct()
	{
		parent::__construct();

		$this->Template->setScript('foot', SCRIPT_PATH . '/admin/create_edit_session.min.js');
		$this->Template->assign('page_title', 'Create Session');
		$this->Template->assign('topnav', 'sessions');
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
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
		$this->can['create_fundraiser'] = true;
		$this->runCreateSession();
	}

	function runHomeOfficeManager()
	{
		$this->can['create_fundraiser'] = true;
		$this->runCreateSession();
	}

	function runSiteAdmin()
	{
		$this->can['create_fundraiser'] = true;
		$this->runCreateSession();
	}

	function runCreateSession()
	{
		$tpl = CApp::instance()->template();

		// Set up create session form
		$SessionForm = new CForm();
		$SessionForm->Repost = true;
		$SessionForm->Bootstrap = true;

		$startTS = time();
		$menu_id = false;

		if (isset($_REQUEST["selectedCell"]) && $_REQUEST["selectedCell"])
		{
			$startTS = strtotime(CGPC::do_clean($_REQUEST["selectedCell"], TYPE_STR));

			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->whereAdd("menu.global_menu_start_date <= '" . CTemplate::dateTimeFormat($startTS, YEAR_MONTH_DAY) . "'");
			$DAO_menu->whereAdd("menu.global_menu_end_date >= '" . CTemplate::dateTimeFormat($startTS, YEAR_MONTH_DAY) . "'");
			$DAO_menu->find(true);

			$menu_id = $DAO_menu->id;
		}
		else if (isset($_REQUEST["menu"]))
		{
			if (isset($_REQUEST["menu"]) && $_REQUEST["menu"])
			{
				$menu_id = CGPC::do_clean($_REQUEST["menu"], TYPE_INT);
			}
		}

		if (!$menu_id)
		{
			$menu_id = CBrowserSession::instance()->getValue('sm_current_menu');
		}

		if (!$menu_id)
		{
			$activeMenus = CMenu::getActiveMenuArray();
			$lastMenu = array_pop($activeMenus);
			$menu_id = $lastMenu['id'];
		}

		// Session dashboard control request
		if (isset($_REQUEST["date"]) && $_REQUEST["date"])
		{
			$startTS = strtotime(CGPC::do_clean($_REQUEST["date"], TYPE_STR));
		}

		if ($menu_id)
		{
			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->id = $menu_id;
			$menu_result = $DAO_menu->find(true);
			if ($menu_result <= 0)
			{
				throw new Exception("Error retrieving menu during Session create: $menu_id");
			}

			if (!$DAO_menu->isEnabled_Backoffice_SessionEditing())
			{
				$tpl->setStatusMsg('This menu is not available for editing yet.');
				CApp::bounce('/backoffice');
			}
		}
		else
		{
			throw new Exception("Create session function requires a menu id.");
		}

		$curMenus = CMenu::getCurrentAndFutureMenuArrayOld();

		if (!array_key_exists($menu_id, $curMenus))
		{
			$keys = array_keys($curMenus);
			$menu_id = current($keys);
		}

		$SessionForm->DefaultValues['menu'] = $menu_id;
		$SessionForm->addElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'menu',
			CForm::options => $curMenus
		));

		CBrowserSession::instance()->setValue('sm_current_menu', $menu_id);

		if ($this->currentStore)
		{
			//fadmins
			$currentStore = $this->currentStore;
		}
		else
		{
			//site admin
			//does the location stuff for the site admin, adds the dropdown, checks the url for a store id first
			//CForm ::storedropdown always sets the default to the last chosen store
			$SessionForm->DefaultValues['store'] = array_key_exists('store', $_GET) ? CGPC::do_clean($_GET['store'], TYPE_INT) : null;

			$SessionForm->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true,
				CForm::name => 'store'
			));

			$currentStore = $SessionForm->value('store');
		}

		// ----------------------------- get store preference for close interval
		$Store = DAO_CFactory::create('store');
		$Store->id = $currentStore;
		$Store->find(true);

		$markup = $Store->getMarkUpMultiObj($menu_id);

		$Store->getStorePickupLocations();

		$tpl->assign('allowsMealCustomization', $Store->supports_meal_customization);

		$SessionForm->DefaultValues["introductory_slots"] = ($Store->storeSupportsIntroOrders($DAO_menu->id)) ? $Store->default_intro_slots : 0;

		if ($Store->close_interval_type == CStore::HOURS)
		{
			$SessionForm->DefaultValues["close_interval_type"] = CStore::HOURS;
		}
		else
		{
			$SessionForm->DefaultValues["close_interval_type"] = CStore::ONE_FULL_DAY;
		}

		if ($Store->meal_customization_close_interval_type == CStore::HOURS)
		{
			$SessionForm->DefaultValues["meal_customization_close_interval_type"] = CStore::HOURS;
		}
		else
		{
			$SessionForm->DefaultValues["meal_customization_close_interval_type"] = CStore::FOUR_FULL_DAYS;
		}

		if (CTemplate::dateTimeFormat(date('Y-m-d', $startTS), YEAR_MONTH_DAY) >= CTemplate::dateTimeFormat($DAO_menu->global_menu_end_date, YEAR_MONTH_DAY))
		{
			$SessionForm->DefaultValues["session_date"] = CTemplate::dateTimeFormat($DAO_menu->global_menu_start_date, YEAR_MONTH_DAY);
		}
		else if (CTemplate::dateTimeFormat(date('Y-m-d', $startTS), YEAR_MONTH_DAY) >= CTemplate::dateTimeFormat($DAO_menu->global_menu_start_date, YEAR_MONTH_DAY))
		{
			$SessionForm->DefaultValues["session_date"] = CTemplate::dateTimeFormat(date('Y-m-d', $startTS), YEAR_MONTH_DAY);
		}
		else
		{
			$SessionForm->DefaultValues["session_date"] = CTemplate::dateTimeFormat($DAO_menu->global_menu_start_date, YEAR_MONTH_DAY);
		}

		$SessionForm->DefaultValues["custom_close_interval"] = $Store->close_session_hours;
		$SessionForm->DefaultValues["meal_customization_close_interval"] = $Store->close_customization_session_hours;
		$SessionForm->DefaultValues["session_publish_state"] = CSession::PUBLISHED;
		$SessionForm->DefaultValues['session_lead'] = 0;
		$SessionForm->DefaultValues["session_type"] = CSession::STANDARD;
		$SessionForm->DefaultValues["available_slots"] = 10;
		$SessionForm->DefaultValues["duration_minutes"] = 60;
		$SessionForm->DefaultValues["discount_value"] = 0;
		$SessionForm->DefaultValues["session_time"] = '12:00';
		$SessionForm->DefaultValues["session_end_time"] = '13:00';

		if ($markup)
		{
			$SessionForm->DefaultValues["session_assembly_fee"] = $markup->assembly_fee;
			if (!OrdersHelper::allow_assembly_fee($menu_id))
			{
				$SessionForm->DefaultValues['session_assembly_fee'] = 0;
				$markup->assembly_fee = '0.00';
			}
		}

		if ($Store->delivery_fee)
		{
			$SessionForm->DefaultValues["session_delivery_fee"] = $Store->delivery_fee;
		}

		$session_types = array(CSession::STANDARD => 'Assembly');

		if ($Store->supports_special_events)
		{
			$session_types = array_merge($session_types, array(CSession::MADE_FOR_YOU => 'Pick Up & Home Delivery'));
		}

		$DreamTasteTypeInfoArray = CDreamTasteEvent::dreamTasteProperties($menu_id, $Store);
		$dream_taste_themes = array('' => 'Select Theme'); // just here to satisfy CForm, overwritten if $DreamTasteTypeInfoArray is true

		if ($DreamTasteTypeInfoArray)
		{
			$session_types = array_merge($session_types, array(CSession::DREAM_TASTE => 'Workshops'));

			$dream_taste_themes = array();

			foreach ($DreamTasteTypeInfoArray as $id => $tasteTypeInfo)
			{
				$dream_taste_themes[$tasteTypeInfo['id']] = array(
					'title' => $tasteTypeInfo['title'],
					'data' => array(
						'data-host_required' => $tasteTypeInfo['host_required'],
						'data-password_required' => $tasteTypeInfo['password_required']
					)
				);
			}
		}

		if ($Store->supports_fundraiser)
		{
			// get list of fundraiser themes available for the month
			$FundraiserTypeInfoArray = CFundraiser::fundraiserProperties($menu_id, $Store);

			if ($FundraiserTypeInfoArray)
			{
				$session_types = array_merge($session_types, array(CSession::FUNDRAISER => 'Fundraiser'));

				$fundraiser_themes = array();

				foreach ($FundraiserTypeInfoArray as $id => $fundraiserTypeInfo)
				{
					$fundraiser_themes[$fundraiserTypeInfo['id']] = array(
						'title' => $fundraiserTypeInfo['title'],
						'data' => array(
							'data-host_required' => $fundraiserTypeInfo['host_required'],
							'data-password_required' => $fundraiserTypeInfo['password_required']
						)
					);
				}

				$SessionForm->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::options => $fundraiser_themes,
					CForm::name => 'fundraiser_theme'
				));

				// list of fundraisers
				$fundraiserArray = CFundraiser::storeFundraiserArray($Store);

				$fundraiserSelectOptions = array('' => 'Select Fundraiser');

				if (!empty($fundraiserArray))
				{
					foreach ($fundraiserArray as $id => $fundraiserArray)
					{
						if (!empty($fundraiserArray->active))
						{
							$fundraiserSelectOptions[$id] = $fundraiserArray->fundraiser_name;
						}
					}

					if ($this->can['create_fundraiser'])
					{
						//$fundraiserSelectArray['new'] = 'Add Fundraiser';
					}
				}
				$SessionForm->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::options => $fundraiserSelectOptions,
					CForm::name => 'fundraiser_recipient'
				));
			}
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::required => true,
			CForm::options => $session_types,
			CForm::name => 'session_type'
		));

		$storePickupLocation = array('' => 'Select Location');

		foreach ($Store->remoteLocations as $id => $location)
		{
			$storePickupLocation[$id] = array(
				'title' => $location->location_title . ' - ' . $location->address_line1 . (!empty($location->address_line2) ? ' ' . $location->address_line2 : '') . ', ' . $location->city . ', ' . $location->state_id . ' ' . $location->postal_code,
				'data' => array(
					'data-location_title' => $location->location_title,
					'data-default_session_override' => (($location->default_session_override === null) ? 'false' : $location->default_session_override)
				)
			);
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::required => false,
			CForm::options => $storePickupLocation,
			CForm::name => 'session_pickup_location'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => $dream_taste_themes,
			CForm::name => 'dream_taste_theme'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Date,
			CForm::required => true,
			CForm::min => CTemplate::dateTimeFormat($DAO_menu->global_menu_start_date, YEAR_MONTH_DAY),
			CForm::max => CTemplate::dateTimeFormat($DAO_menu->global_menu_end_date, YEAR_MONTH_DAY),
			CForm::name => 'session_date'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Time,
			CForm::required => true,
			CForm::name => 'session_time'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Time,
			CForm::required => true,
			CForm::name => 'session_end_time'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::placeholder => 'Email address or user id',
			CForm::autocomplete => false,
			CForm::name => 'session_host'
		));

		$leadOpts = CSession::retreiveSessionLeadArray($currentStore, true);

		$leadOpts = array_reverse($leadOpts, true);
		$leadOpts[0] = "Please Select a Lead";
		$leadOpts = array_reverse($leadOpts, true);

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'session_lead',
			CForm::options => $leadOpts
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "close_interval_type",
			CForm::required => true,
			CForm::label => 'Hours Prior',
			CForm::value => CSession::HOURS
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "close_interval_type",
			CForm::required => true,
			CForm::label => '1 Day Prior',
			CForm::value => CSession::ONE_FULL_DAY
		));

		$hoursCloseOptions = array();
		for ($x = 0; $x < 168; $x++)
		{
			$hoursCloseOptions[] = $x;
		}

		$hoursCloseOptionsCustomization = array();
		for ($x = 0; $x < 241; $x++)
		{
			$hoursCloseOptionsCustomization[] = $x;
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'custom_close_interval',
			CForm::options => $hoursCloseOptions
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "meal_customization_close_interval_type",
			CForm::required => true,
			CForm::label => 'Hours Prior',
			CForm::value => CSession::HOURS
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::RadioButton,
			CForm::name => "meal_customization_close_interval_type",
			CForm::required => true,
			CForm::label => '4 Day Prior',
			CForm::value => CSession::FOUR_FULL_DAYS
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'meal_customization_close_interval',
			CForm::options => $hoursCloseOptionsCustomization
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::min => 0,
			CForm::required => true,
			CForm::number => true,
			CForm::name => 'available_slots'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::min => 0,
			CForm::required => true,
			CForm::number => true,
			CForm::name => 'introductory_slots'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::min => 0,
			CForm::required => true,
			CForm::number => true,
			CForm::name => 'duration_minutes'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '4',
			CForm::cols => '40',
			CForm::placeholder => 'Optional administrative notes are only viewable by franchise personnel.',
			CForm::name => 'admin_notes',
			CForm::css_class => 'dd-strip-tags'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 50,
			CForm::placeholder => "Optional. Shows on the store locations' calendar and in the order process. E.g. Delivery to Seattle addresses only.",
			CForm::name => 'session_title',
			CForm::css_class => 'dd-strip-tags'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => '4',
			CForm::cols => '40',
			CForm::placeholder => 'Optional session details. Viewable by everyone.',
			CForm::name => 'session_details',
			CForm::css_class => 'dd-strip-tags'
		));

		$isPrivate = false;

		$SessionForm->DefaultValues['session_password'] = $isPrivate;

		if (!empty($_POST['session_password']))
		{
			$isPrivate = true;
			$SessionForm->DefaultValues['session_password'] = CGPC::do_clean($_POST['session_password'], TYPE_STR);
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::disabled => !$isPrivate,
			CForm::maxlength => 24,
			CForm::name => 'session_password'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Text,
			CForm::number => true,
			CForm::min => 0,
			CForm::max => 10,
			CForm::name => 'discount_value'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::disabled => !$isPrivate,
			CForm::label => '$',
			CForm::tooltip => 'Enable Override',
			CForm::name => 'session_assembly_fee_enable'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Number,
			CForm::disabled => true,
			CForm::name => 'session_assembly_fee'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::disabled => !$isPrivate,
			CForm::label => '$',
			CForm::tooltip => 'Enable Override',
			CForm::name => 'session_delivery_fee_enable'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Number,
			CForm::disabled => true,
			CForm::name => 'session_delivery_fee',
			CForm::min => '0',
			CForm::step => '0.01'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => array(
				CSession::PUBLISHED => 'Published',
				CSession::CLOSED => 'Closed (Hidden for guests)'
			),
			CForm::name => 'session_publish_state'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'do_send_pp_notification',
			CForm::checked => true,
			CForm::label => 'Send Email'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => 'session_sneak_peak'
		));

		$subtypeArray = array(
			'' => 'Pick Up'
		);

		if ($Store->supports_delivery)
		{
			$subtypeArray[CSession::DELIVERY] = 'Home Delivery';
			$subtypeArray[CSession::DELIVERY_PRIVATE] = 'Home Delivery - Private';
		}

		if (!empty($Store->remoteLocations) && $Store->supports_offsite_pickup)
		{
			$subtypeArray[CSession::REMOTE_PICKUP] = 'Community Pick Up';
			$subtypeArray[CSession::REMOTE_PICKUP_PRIVATE] = 'Community Pick Up - Private';
		}

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => $subtypeArray,
			CForm::name => 'session_type_subtype'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::options => array(
				CSession::STANDARD => 'Standard',
				CSession::PRIVATE_SESSION => 'Private Party'
			),
			CForm::name => 'standard_session_type_subtype'
		));

		$SessionForm->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::disabled => true,
			// disabled removed in javascript, ensures javascript is working properly before being able to create session
			CForm::css_class => 'btn btn-primary btn-spinner btn-onclick-disable',
			CForm::name => 'session_submit',
			CForm::value => 'Create session'
		));

		$SessionFormArray = $SessionForm->render(true);

		$tpl->assign('hasDreamtTasteType', array_key_exists(CSession::DREAM_TASTE, $session_types));
		$tpl->assign('form_create_session', $SessionFormArray);
		$tpl->assign('allow_assembly_fee', OrdersHelper::allow_assembly_fee($menu_id));
		$tpl->assign('Menu', $DAO_menu);

		if ($SessionForm->value('session_submit'))
		{
			//  --------------------------------------------Do the insert

			$DAO_session = DAO_CFactory::create('session');
			$sessionvalues = $SessionForm->values();
			$DAO_session->setFrom($sessionvalues);

			if ($DAO_session->store_id == null)
			{
				$DAO_session->store_id = $currentStore;
			}

			if (!isset($_POST['session_password']))
			{
				$DAO_session->session_password = "";
			}

			if (empty($DAO_session->session_type))
			{
				$DAO_session->session_type = CSession::STANDARD;
			}

			$DAO_session->session_class = $DAO_session->session_type;
			if ($DAO_session->session_type == CSession::DREAM_TASTE)
			{
				$DAO_session->session_class = CSession::TODD;
			}

			// Make sure mfy and standard don't have passwords if they aren't private sub types
			if (($DAO_session->isMadeForYou() && $DAO_session->session_type_subtype != CSession::REMOTE_PICKUP_PRIVATE && $DAO_session->session_type_subtype != CSession::DELIVERY_PRIVATE) || (!empty($_POST['standard_session_type_subtype']) && $_POST['standard_session_type_subtype'] == CSession::STANDARD && $DAO_session->session_type_subtype != CSession::PRIVATE_SESSION))
			{
				$DAO_session->session_password = "";
			}

			if (!empty($_POST['session_assembly_fee_enable']) && OrdersHelper::allow_assembly_fee($menu_id))
			{
				$DAO_session->session_assembly_fee = CGPC::do_clean($_POST['session_assembly_fee'], TYPE_NUM);
			}
			else
			{
				$DAO_session->session_assembly_fee = 'NULL';
			}

			if (!empty($_POST['session_delivery_fee_enable']))
			{
				$DAO_session->session_delivery_fee = CGPC::do_clean($_POST['session_delivery_fee'], TYPE_NUM);
			}
			else
			{
				$DAO_session->session_delivery_fee = 'NULL';
			}

			$eventThemeAcronym = false;  // passed to doesTimeConflict so curbside events are allowed to overlap other sessions

			// if dream taste or fundraiser, validate properties
			if ($DAO_session->session_type == CSession::DREAM_TASTE || $DAO_session->session_type == CSession::FUNDRAISER)
			{
				if ($DAO_session->session_type == CSession::DREAM_TASTE)
				{
					$tasteTheme = CGPC::do_clean($_POST['dream_taste_theme'], TYPE_STR);
					$tasteThemeInfo = $DreamTasteTypeInfoArray[$tasteTheme];
					$eventThemeAcronym = $tasteThemeInfo['fadmin_acronym'];

					if ($tasteThemeInfo['password_required'] == 1 && empty($DAO_session->session_password))
					{
						$tpl->setErrorMsg('Meal Prep Workshop Sessions must have a password.');

						return;
					}

					if (empty($tasteThemeInfo['id']) || $tasteThemeInfo['id'] == 1)
					{
						CLog::RecordIntense("No Meal Prep Workshop ID", "ryan.snook@dreamdinners.com");

						$tpl->setErrorMsg('Error finding Meal Prep Workshop information, if this continues please contact support.');

						return;
					}
				}

				if ($DAO_session->session_type == CSession::FUNDRAISER)
				{
					$fundraiserTheme = CGPC::do_clean($_POST['fundraiser_theme'], TYPE_STR);
					$fundraiserThemeInfo = $FundraiserTypeInfoArray[$fundraiserTheme];
					$eventThemeAcronym = $fundraiserThemeInfo['fadmin_acronym'];

					if (empty($fundraiserThemeInfo['id']) || $fundraiserThemeInfo['id'] == 1)
					{
						CLog::RecordIntense("No Fundraiser ID", "ryan.snook@dreamdinners.com");

						$tpl->setErrorMsg('Error finding Fundraiser information, if this continues please contact support.');

						return;
					}
				}
			}

			$PP_User = false;
			if (!empty($_POST['session_host']))
			{
				$userDAO = DAO_CFactory::create('user');
				if (is_numeric($_POST['session_host']))
				{
					$userDAO->id = $_POST['session_host'];

					if ($userDAO->find(true))
					{
						$PP_User = $userDAO;
					}
				}
				else
				{
					$userDAO->primary_email = trim(CGPC::do_clean($_POST['session_host'], TYPE_STR));

					if ($userDAO->find(true))
					{
						$PP_User = $userDAO;
					}
				}

				if (!$PP_User)
				{
					$tpl->setErrorMsg('We cannot find the specified user in our system. Please try again or clear the session host field');

					return;
				}
			}

			$DAO_session->session_publish_state = $SessionForm->value('session_publish_state');

			$DAO_session->sneak_peak = 0;
			if ($SessionForm->value('session_sneak_peak') == true)
			{
				$DAO_session->sneak_peak = 1;
			}

			if (!isset($DAO_session->store_id))
			{
				$DAO_session->store_id = $SessionForm->DefaultValues["store"];
			}

			$leadVal = $SessionForm->value('session_lead');
			if (!empty($leadVal))
			{
				$DAO_session->session_lead = $SessionForm->value('session_lead');
			}
			else
			{
				$DAO_session->session_lead = 'null';
			}

			$DAO_session->session_start = date("Y-m-d H:i:s", strtotime(CGPC::do_clean($_POST['session_date'], TYPE_STR) . ' ' . CGPC::do_clean($_POST['session_time'], TYPE_STR)));

			if (!$DAO_menu->isTimeStampLegalForMenu(strtotime($DAO_session->session_start)))
			{
				$tpl->setErrorMsg('The session time is outside of the valid range for this menu. <br /> Note: You can change the menu or session time to correct this. <br /><span style="color:red">Please do not use the back button.</span>');
			}
			else
			{
				$DAO_session->setCloseSchedulingTime($SessionForm->value("closes"), $SessionForm->value("custom_close_interval"));

				if ($DAO_session->session_type == CSession::SPECIAL_EVENT)
				{
					$DAO_session->setMealCustomizationCloseSchedulingTime($SessionForm->value("meal_customization_close_interval_type"), $SessionForm->value("meal_customization_close_interval"));
				}
				else
				{
					$DAO_session->setMealCustomizationCloseSchedulingTime($SessionForm->value("meal_customization_close_interval_type"), -1);
				}

				if ($DAO_session->doesTimeConflict($eventThemeAcronym))
				{
					$tpl->setErrorMsg('The session time and duration conflict with another session.');
				}
				else
				{

					if (!empty($_POST['discount_value']))
					{
						$discount = DAO_CFactory::create('session_discount');

						// CES 6/15/06 - allow only percent for now per marketing
						//$discount->discount_type = $SessionForm->value("discount_type");
						$discount->discount_type = 'PERCENT';
						$discount->discount_var = $SessionForm->value("discount_value");

						if ($discount->discount_var <= 0.0 || $discount->discount_var > 10.0)
						{
							$tpl->setErrorMsg('The session discount must be greater than 0 and less than or equal to 10.0');

							return;
						}

						$rslt = $discount->insert();
						if (!$rslt)
						{
							$tpl->setErrorMsg('The session discount could not be created');
							$DAO_session->session_discount_id = null;
						}

						$DAO_session->session_discount_id = $rslt;
					}

					$DAO_session->menu_id = $DAO_menu->id;

					if (!isset($DAO_session->introductory_slots) || !$Store->storeSupportsIntroOrders($DAO_session->menu_id) || (($DAO_session->session_type != CSession::STANDARD && $DAO_session->session_type != CSession::MADE_FOR_YOU)))
					{
						$DAO_session->introductory_slots = 0;
					}

					if ($DAO_session->session_type == CSession::FUNDRAISER)
					{
						if (empty($_POST['fundraiser_theme']) || !is_numeric($_POST['fundraiser_recipient']))
						{
							throw new Exception("Error applying fundraiser to session properties.");
						}
					}

					if (empty($DAO_session->session_type_subtype))
					{
						$DAO_session->session_type_subtype = 'null';
					}

					$rslt = $DAO_session->insert();

					json_encode(array(
						'session_id' => $DAO_session->id,
						'session_start' => $DAO_session->session_start,
						'event_by' => $DAO_session->created_by,
					));

					CStoreActivityLog::addEvent($DAO_session->store_id, false, false, 11, false);


					if ($rslt)
					{
						$tpl->setStatusMsg('<span class="font-weight-bold">The session has been created.</span> <br /><br /><a class="btn btn-primary btn-sm" href="/backoffice?session=' . $DAO_session->id . '">Go to Session in BackOffice Home</a>');

						if ($DAO_session->session_type == CSession::DREAM_TASTE)
						{
							$tasteTheme = CGPC::do_clean($_POST['dream_taste_theme'], TYPE_STR);
							$tasteThemeInfo = $DreamTasteTypeInfoArray[$tasteTheme];

							if ($tasteThemeInfo['password_required'] == 1 && empty($DAO_session->session_password))
							{
								$tpl->setErrorMsg('Meal Prep Workshop Sessions must have a password.');

								return;
							}

							$session_properties = DAO_CFactory::create('session_properties');
							$session_properties->session_id = $DAO_session->id;
							if (empty($tasteThemeInfo['id']) || $tasteThemeInfo['id'] == 1)
							{
								CLog::RecordIntense("No Meal Prep Workshop ID", "ryan.snook@dreamdinners.com");
							}
							$session_properties->dream_taste_event_id = $tasteThemeInfo['id'];
							$session_properties->menu_pricing_method = 'USE_CURRENT';
							$session_properties->session_host = 0;

							if (!empty($PP_User->id))
							{
								$session_properties->session_host = $PP_User->id; // this is validated above

								if (!empty($tasteThemeInfo['can_rsvp_only']))
								{
									CSession::createSessionRSVP($DAO_session, $PP_User, false);

									if (!empty($tasteThemeInfo['can_rsvp_upgrade']))
									{
										$bundleProperties = CBundle::getBundleInfo($tasteThemeInfo['bundle_id'], $tasteThemeInfo['menu_id'], $Store);

										$tpl->setStatusMsg('<br /><input type="btn btn-primary btn-sm" class="btn btn-primary btn-sm" value="Upgrade Host\'s RSVP for $' . $bundleProperties->price . '" onclick="hostessDreamTasteOrder(' . $DAO_session->id . ', \'' . CTemplate::dateTimeFormat($DAO_session->session_start) . '\', ' . $PP_User->id . ')" />');
									}
								}
								else
								{
									$tpl->setStatusMsg('<br /><input type="btn btn-primary btn-sm" class="btn btn-primary btn-sm" value="Continue to place Hostess\' order" onclick="hostessDreamTasteOrder(' . $DAO_session->id . ', \'' . CTemplate::dateTimeFormat($DAO_session->session_start) . '\', ' . $PP_User->id . ')" />');
								}
							}

							$session_properties->insert();
						}

						if ($DAO_session->session_type == CSession::FUNDRAISER)
						{
							$fundraiserTheme = CGPC::do_clean($_POST['fundraiser_theme'], TYPE_STR);
							$fundraiserThemeInfo = $FundraiserTypeInfoArray[$fundraiserTheme];

							$session_properties = DAO_CFactory::create('session_properties');
							$session_properties->session_id = $DAO_session->id;
							if (empty($fundraiserThemeInfo['id']) || $fundraiserThemeInfo['id'] == 1)
							{
								CLog::RecordIntense("No Fundraiser ID", "ryan.snook@dreamdinners.com");
							}
							$session_properties->dream_taste_event_id = $fundraiserThemeInfo['id'];
							$session_properties->menu_pricing_method = 'USE_CURRENT';
							$session_properties->fundraiser_id = CGPC::do_clean($_POST['fundraiser_recipient'], TYPE_INT);
							$session_properties->session_host = 0;

							if (!empty($PP_User->id))
							{
								$session_properties->session_host = $PP_User->id; // this is validated above
							}

							$session_properties->insert();
						}

						if ($DAO_session->isMadeForYou())
						{
							if (!empty($DAO_session->session_type_subtype) && ($DAO_session->session_type_subtype == CSession::REMOTE_PICKUP || $DAO_session->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
							{
								$session_properties = DAO_CFactory::create('session_properties');
								$session_properties->session_id = $DAO_session->id;
								$session_properties->store_pickup_location_id = CGPC::do_clean($_POST['session_pickup_location'], TYPE_INT);
								$session_properties->menu_pricing_method = 'USE_CURRENT';

								if ($PP_User)
								{
									$session_properties->session_id = $DAO_session->id;
									$session_properties->session_host = $PP_User->id; // this is validated above
								}

								$session_properties->insert();
							}
							else
							{
								$session_properties = DAO_CFactory::create('session_properties');
								$session_properties->session_id = $DAO_session->id;
								$session_properties->session_host = $PP_User->id; // this is validated above
								$session_properties->menu_pricing_method = 'USE_CURRENT';
								$session_properties->insert();
							}
						}

						if ($isPrivate && $DAO_session->session_type == CSession::STANDARD)
						{
							if ($PP_User)
							{
								$session_properties = DAO_CFactory::create('session_properties');
								$session_properties->session_id = $DAO_session->id;
								$session_properties->session_host = $PP_User->id; // this is validated above
								$session_properties->menu_pricing_method = 'USE_CURRENT';
								$session_properties->insert();
							}
						}

						if ($PP_User && isset($_POST['do_send_pp_notification']))
						{
							CEmail::sendHostessNotification($PP_User->primary_email, $PP_User->firstname . ' ' . $PP_User->lastname, $DAO_session);
						}

						if (isset($_GET['back']))
						{
							CApp::bounce($_GET['back']);
						}
					}
					else
					{
						$tpl->setErrorMsg('The session could not be created');
					}
				}
			}
		}
	}
}

?>