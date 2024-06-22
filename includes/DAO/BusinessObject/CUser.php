<?php
require_once 'DAO/User.php';
require_once 'DAO/User_login.php';
require_once 'CLog.inc';
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CBrowserSession.php');
require_once('includes/DAO/BusinessObject/CUserHistory.php');
require_once('includes/DAO/BusinessObject/CCustomerReferral.php');
require_once('includes/DAO/BusinessObject/CUserReferralSource.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CPointsCredits.php');
require_once('includes/DAO/BusinessObject/CCorporateCrateClient.php');
require_once('includes/DAO/BusinessObject/CTaskRetryQueue.php');
require_once('includes/CCart2.inc');
require_once('includes/CPasswordPolicy.inc');
require_once 'includes/CResultToken.inc';
require_once("DAO/BusinessObject/CUserAccountManagement.php");

/* ------------------------------------------------------------------------------------------------
*	Class: CUser
*
*	Data:
*
*	Methods:
*		Load()
*		Save()
*		Add()
*		Authenticate()
*		ExpireSession()
*
* Properties:
*		LoggedIn;
*
*	Dynamic Properties:
*		Username
*		ID
*		FirstName
*		LastName
*		etc.
*
*
*	Description:
*		Placeholder for DB storing user object. Including this file
*		creates a global $User object.
*
*	Requires:
*
* -------------------------------------------------------------------------------------------------- */

/* Schema

id				int(11)		NOT NULL auto_increment,
org_id				int(11)		NOT NULL default 1,
timestamp_updated		timestamp	NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
timestamp_created		timestamp	NOT NULL,
last_login			datetime	default NULL,
user_type			enum('SITE_ADMIN', 'CUSTOMER') NOT NULL DEFAULT 'CUSTOMER',
visit_count			int(11)		NOT NULL default 0,
primary_email			varchar(255)	NOT NULL,
firstname			varchar(50)	NOT NULL,
lastname			varchar(50)	NOT NULL,
home_store_id		int(11)		default NULL,
gender			enum('M','F','X')	default NULL,
telephone_1		varchar(64)	default NULL,
telephone_2		varchar(64)	default NULL,
fax				varchar(64)	default NULL,
call_time			enum('MORNING','AFTERNOON','EVENING','NEVER', 'ALWAYS'),
*/

class CUser extends DAO_User
{

	static private $_current = null; //the currently logged in user
	static private $_cookieuser = null; // userinfo by cookie

	/**
	 * Loaded by form/login
	 * getCurrentUser() should always return a valid data object
	 */
	static public function getCurrentUser()
	{
		if (self::$_current == null)
		{
			self::$_current = new CUser();

			if (!empty($_COOKIE['number_feeding']))
			{
				self::$_current->number_feeding = $_COOKIE['number_feeding'];
			}

			if (!empty($_COOKIE['desired_homemade_meals_per_week']))
			{
				self::$_current->desired_homemade_meals_per_week = $_COOKIE['desired_homemade_meals_per_week'];
			}
		}

		return self::$_current;
	}

	//user_type constants
	const GUEST = 'GUEST';
	const CUSTOMER = 'CUSTOMER';
	const FRANCHISE_OWNER = 'FRANCHISE_OWNER';
	const SITE_ADMIN = 'SITE_ADMIN';
	const FRANCHISE_STAFF = 'FRANCHISE_STAFF';
	const FRANCHISE_MANAGER = 'FRANCHISE_MANAGER';
	const HOME_OFFICE_STAFF = 'HOME_OFFICE_STAFF';
	const GUEST_SERVER = 'GUEST_SERVER';
	const HOME_OFFICE_MANAGER = 'HOME_OFFICE_MANAGER';
	const FRANCHISE_LEAD = 'FRANCHISE_LEAD';
	const MANUFACTURER_STAFF = 'MANUFACTURER_STAFF';
	const EVENT_COORDINATOR = 'EVENT_COORDINATOR';
	const OPS_SUPPORT = 'OPS_SUPPORT';
	const OPS_LEAD = 'OPS_LEAD';
	const DISHWASHER = 'DISHWASHER';
	const NEW_EMPLOYEE = 'NEW_EMPLOYEE';

	const DFL_STATUS_INITIATED = 0;
	const DFL_STATUS_ACTIVE = 1;
	const DFL_STATUS_CANCELLED = 2;
	const DFL_STATUS_EXPIRED = 3;

	// user_preferences
	const FB_POST_PP_BADGE = 'FB_POST_PP_BADGE';
	const FB_POST_MY_MEALS_RATE = 'FB_POST_MY_MEALS_RATE';
	const FB_POST_MY_MEALS_WOA = 'FB_POST_MY_MEALS_WOA';
	const FB_POST_SESSION_SIGNUP_POST = 'FB_POST_SESSION_SIGNUP_POST';
	const FB_POST_SESSION_SIGNUP_EVENT = 'FB_POST_SESSION_SIGNUP_EVENT';
	const FB_POST_SESSION_SIGNUP_EVENT_PRIVACY = 'FB_POST_SESSION_SIGNUP_EVENT_PRIVACY';
	const FB_POST_HOST_DREAMTASTE_POST = 'FB_POST_HOST_DREAMTASTE_POST';
	const FB_POST_HOST_DREAMTASTE_EVENT = 'FB_POST_HOST_DREAMTASTE_EVENT';
	const FB_POST_HOST_DREAMTASTE_EVENT_PRIVACY = 'FB_POST_HOST_DREAMTASTE_EVENT_PRIVACY';
	const TC_DELAYED_PAYMENT_AGREE = 'TC_DELAYED_PAYMENT_AGREE';
	const TC_DREAM_DINNERS_AGREE = 'TC_DREAM_DINNERS_AGREE';
	const SESSION_PRINT_NEXT_MENU = 'SESSION_PRINT_NEXT_MENU';
	const SESSION_PRINT_FREEZER_SHEET = 'SESSION_PRINT_FREEZER_SHEET';
	const SESSION_PRINT_NUTRITIONALS = 'SESSION_PRINT_NUTRITIONALS';
	const SESSION_MENU_CART_FLY_TO = 'SESSION_MENU_CART_FLY_TO';
	const SESSION_BEFORE_MENU = 'SESSION_BEFORE_MENU';
	const USER_ACCOUNT_NOTE = 'USER_ACCOUNT_NOTE';
	const LTD_AUTO_ROUND_UP = 'LTD_AUTO_ROUND_UP';
	const HAS_SEEN_ELEMENT = 'HAS_SEEN_ELEMENT';

	const TEXT_MESSAGE_OPT_IN = 'TEXT_MESSAGE_OPT_IN';
	const TEXT_MESSAGE_THAW_PRIMARY = 'TEXT_MESSAGE_THAW_PRIMARY';
	const TEXT_MESSAGE_PROMO_PRIMARY = 'TEXT_MESSAGE_PROMO_PRIMARY';
	const TEXT_MESSAGE_REMINDER_SESSION_PRIMARY = 'TEXT_MESSAGE_REMINDER_SESSION_PRIMARY';
	const TEXT_MESSAGE_DD_TEST = 'TEXT_MESSAGE_DD_TEST';

	const TEXT_MESSAGE_TARGET_NUMBER = 'TEXT_MESSAGE_TARGET_NUMBER';

	const EMAIL_REMINDER_SESSION = 'EMAIL_REMINDER_SESSION';
	const EMAIL_PLATE_POINTS = 'EMAIL_PLATE_POINTS';
	const EMAIL_OFFERS_AND_PROMOS = 'EMAIL_OFFERS_AND_PROMOS';
	const EMAIL_SURVEYS = 'EMAIL_SURVEYS';

	const OPTED_OUT = 'OPTED_OUT';
	const OPTED_IN = 'OPTED_IN';
	const UNANSWERED = 'UNANSWERED';

	//MEAL CUSTOMIZATION PREFs - NEEDS to be in User_pref key enum
	const MEAL_EXCLUDE_RAW_ONION = 'MEAL_EXCLUDE_RAW_ONION';
	const MEAL_EXCLUDE_ONION_SPICES = 'MEAL_EXCLUDE_ONION_SPICES';
	const MEAL_EXCLUDE_RAW_GARLIC = 'MEAL_EXCLUDE_RAW_GARLIC';
	const MEAL_EXCLUDE_GARLIC_SPICES = 'MEAL_EXCLUDE_GARLIC_SPICES';
	const MEAL_EXCLUDE_MUSHROOMS = 'MEAL_EXCLUDE_MUSHROOMS';
	const MEAL_EXCLUDE_OLIVES = 'MEAL_EXCLUDE_OLIVES';
	const MEAL_EXCLUDE_BACON = 'MEAL_EXCLUDE_BACON';
	const MEAL_EXCLUDE_CILANTRO = 'MEAL_EXCLUDE_CILANTRO';
	const MEAL_EXCLUDE_SPECIAL_REQUEST = 'MEAL_EXCLUDE_SPECIAL_REQUEST';
	const MEAL_SPECIAL_REQUEST_DETAILS = 'MEAL_SPECIAL_REQUEST_DETAILS';

	//const MEAL_EXCLUDE_CUSTOM = 'MEAL_EXCLUDE_CUSTOM';

	// preference defaults
	static $preferenceDefaults = array(
		self::FB_POST_PP_BADGE => 1,
		self::FB_POST_MY_MEALS_RATE => 1,
		self::FB_POST_MY_MEALS_WOA => 1,
		self::FB_POST_SESSION_SIGNUP_POST => 0,
		self::FB_POST_SESSION_SIGNUP_EVENT => 1,
		self::FB_POST_SESSION_SIGNUP_EVENT_PRIVACY => 'SECRET',
		self::FB_POST_HOST_DREAMTASTE_POST => 0,
		self::FB_POST_HOST_DREAMTASTE_EVENT => 1,
		self::FB_POST_HOST_DREAMTASTE_EVENT_PRIVACY => 'FRIENDS',
		self::TC_DELAYED_PAYMENT_AGREE => 0,
		self::TC_DREAM_DINNERS_AGREE => 1,
		self::SESSION_PRINT_NEXT_MENU => 1,
		self::SESSION_PRINT_FREEZER_SHEET => 1,
		self::SESSION_PRINT_NUTRITIONALS => 1,
		self::SESSION_MENU_CART_FLY_TO => 1,
		self::SESSION_BEFORE_MENU => 'UNANSWERED',
		self::USER_ACCOUNT_NOTE => null,
		self::LTD_AUTO_ROUND_UP => null,
		self::HAS_SEEN_ELEMENT => array(
			'DDU_TAKE_OFF' => 0,
			'WEEKLY_INVENTORY_WARNING' => 0
		),
		self::TEXT_MESSAGE_OPT_IN => 'UNANSWERED',
		// UNANSWERED, PENDING_OPT_IN, PENDING_OPT_OUT, OPTED_IN, OPTED_OUT
		self::TEXT_MESSAGE_THAW_PRIMARY => 'UNANSWERED',
		self::TEXT_MESSAGE_PROMO_PRIMARY => 'UNANSWERED',
		self::TEXT_MESSAGE_REMINDER_SESSION_PRIMARY => 'UNANSWERED',
		self::TEXT_MESSAGE_DD_TEST => 'UNANSWERED',

		// UNANSWERED or a CELL number
		self::TEXT_MESSAGE_TARGET_NUMBER => 'UNANSWERED',

		// UNANSWERED, PENDING_OPT_IN, PENDING_OPT_OUT, OPTED_IN, OPTED_OUT
		self::EMAIL_REMINDER_SESSION => 'UNANSWERED',
		self::EMAIL_PLATE_POINTS => 'UNANSWERED',
		self::EMAIL_OFFERS_AND_PROMOS => 'UNANSWERED',
		self::EMAIL_SURVEYS => 'UNANSWERED',

		// UNANSWERED, MEAL CUSTOMIZATIONS - needs to match obj defined in OrdersCustomization :: MealCustomizationObj
		self::MEAL_EXCLUDE_RAW_ONION => 'UNANSWERED',
		self::MEAL_EXCLUDE_ONION_SPICES => 'UNANSWERED',
		self::MEAL_EXCLUDE_RAW_GARLIC => 'UNANSWERED',
		self::MEAL_EXCLUDE_GARLIC_SPICES => 'UNANSWERED',
		self::MEAL_EXCLUDE_MUSHROOMS => 'UNANSWERED',
		self::MEAL_EXCLUDE_OLIVES => 'UNANSWERED',
		self::MEAL_EXCLUDE_BACON => 'UNANSWERED',
		self::MEAL_EXCLUDE_CILANTRO => 'UNANSWERED',
		self::MEAL_EXCLUDE_SPECIAL_REQUEST => 'UNANSWERED',
		self::MEAL_SPECIAL_REQUEST_DETAILS => ''
		//self::MEAL_EXCLUDE_CUSTOM => '', currently not used

	);

	static $SMSPrefsDefaults = array(
		self::TEXT_MESSAGE_THAW_PRIMARY => 'UNANSWERED',
		self::TEXT_MESSAGE_PROMO_PRIMARY => 'UNANSWERED',
		self::TEXT_MESSAGE_REMINDER_SESSION_PRIMARY => 'UNANSWERED',
		self::TEXT_MESSAGE_DD_TEST => 'UNANSWERED',
		self::TEXT_MESSAGE_TARGET_NUMBER => 'UNANSWERED'
	);

	static $EMailPrefsDefaults = array(
		self::EMAIL_REMINDER_SESSION => 'UNANSWERED',
		self::EMAIL_PLATE_POINTS => 'UNANSWERED',
		self::EMAIL_OFFERS_AND_PROMOS => 'UNANSWERED',
		self::EMAIL_SURVEYS => 'UNANSWERED'
	);

	const MEMBERSHIP_STATUS_CURRENT = 'MEMBERSHIP_STATUS_CURRENT';
	const MEMBERSHIP_STATUS_REFUNDED = 'MEMBERSHIP_STATUS_REFUNDED';
	const MEMBERSHIP_STATUS_TERMINATED = 'MEMBERSHIP_STATUS_TERMINATED';
	const MEMBERSHIP_STATUS_NOT_ENROLLED = 'MEMBERSHIP_STATUS_NOT_ENROLLED';
	const MEMBERSHIP_STATUS_COMPLETED = 'MEMBERSHIP_STATUS_COMPLETED';

	private $_LoggedIn = false;
	private $_AuthenticationAttempt = false;
	private $_original_primary_email = null; //this gets set in fetch() so we know whether or not to update
	//user_login later on

	public $membershipData = null;
	public $membershipsArray = null;
	public $platePointsData = null;
	public $addressBook = array();
	public $nextSession = array();
	public $accessToStore = array();
	public $preferences = array();
	public $meal_customization_preferences = array();
	public $ltd_roundup_orders = array();
	public $number_feeding = null;
	public $desired_homemade_meals_per_week = null;

	function __construct()
	{
		parent::__construct();
	}

	function find_DAO_user($n = false)
	{
		if ($this->_query["data_select"] === "*")
		{
			throw new Exception("When creating this object, second parameter in DAO_CFactory::create() needs to be 'true'");
		}

		$this->joinAddWhereAsOn(DAO_CFactory::create('user_digest', true), 'LEFT');

		return parent::find($n);
	}

	static function isLoggedIn()
	{
		if (!CBrowserSession::isPrevious())
		{
			return false;
		}

		return self::getCurrentUser()->_LoggedIn;
	}

	function isMultiStoreOwner(&$stores)
	{
		if (empty($this->id))
		{
			return false;
		}

		if ($this->user_type != self::FRANCHISE_OWNER && $this->user_type != self::FRANCHISE_MANAGER)
		{
			return false;
		}

		$UTSDAO = new DAO();
		$UTSDAO->query("select uts.store_id, s.store_name from user_to_store uts 
        join store s on s.id = uts.store_id and s.active where uts.user_id = {$this->id} and uts.is_deleted = 0 and uts.user_id in (228531, 400252, 856342, 658891)");

		if ($UTSDAO->N <= 1)
		{
			return false;
		}

		while ($UTSDAO->fetch())
		{
			$stores[$UTSDAO->store_id] = $UTSDAO->store_name;
		}

		return true;
	}

	/*
	* A Customer is considered new until they have placed an order
	*/
	static function isNewCustomer()
	{
		if (self::$_current == null)
		{
			return true;
		}

		$user_id = self::$_current->id;
		$Booking = DAO_CFactory::create('booking');

		$Booking->query("SELECT b.id FROM booking b
					INNER JOIN orders o ON o.id = b.order_id and o.grand_total <> 0 AND o.is_deleted = 0
					WHERE b.is_deleted = 0 AND b.status = 'ACTIVE' AND b.user_id = $user_id");

		if ($Booking->N > 0)
		{
			return false;
		}

		return true;
	}

	static function isUserStaff()
	{
		if (!empty(self::getCurrentUser()->user_type) && (self::getCurrentUser()->user_type != self::CUSTOMER && self::getCurrentUser()->user_type != self::GUEST))
		{
			return true;
		}

		return false;
	}

	static function userTypeText($user_type)
	{
		switch ($user_type)
		{
			case self::GUEST:
				return 'Guest';
			case self::CUSTOMER:
				return 'Customer';
			case self::SITE_ADMIN:
				return 'Site Admin';
			case self::HOME_OFFICE_MANAGER:
				return 'Home Office Manager';
			case self::HOME_OFFICE_STAFF:
				return 'Home Office Staff';
			case self::FRANCHISE_OWNER:
				return 'Franchise Owner';
			case self::FRANCHISE_MANAGER:
				return 'Sales Manager';
			case self::FRANCHISE_LEAD:
				return 'Sales Lead';
			case self::GUEST_SERVER:
				return 'Guest Server';
			case self::FRANCHISE_STAFF:
				return 'Store Staff (obs)';
			case self::MANUFACTURER_STAFF:
				return 'Manufacturer Staff';
			case self::EVENT_COORDINATOR:
				return 'Business Development Coordinator';
			case self::OPS_SUPPORT:
				return 'Operations Support';
			case self::OPS_LEAD:
				return 'Operations Manager';
			case self::DISHWASHER:
				return 'Dishwasher';
			case self::NEW_EMPLOYEE:
				return 'New Employee';
			default:
				return 'Guest';
		}
	}

	static function userAdminDistributionCenterPageAccessArray()
	{
		$navigationArray = array(
			'home' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::MANUFACTURER_STAFF,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Home',
				'link' => '/backoffice',
				'submenu' => array()
			),
			'guests' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::MANUFACTURER_STAFF,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Guests',
				'link' => '/backoffice/list-users',
				'submenu' => array(
					'admin_list_users' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::MANUFACTURER_STAFF,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Guest Search',
						'link' => '/backoffice/list-users',
					),
					'admin_account' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::MANUFACTURER_STAFF,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Add New Guests',
						'link' => '/backoffice/account',
					)
				)
			),

			'sessions' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Shipping Calendar',
				'link' => '/backoffice/session-mgr-delivered'
			),

			'reports' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD,
					self::OPS_SUPPORT
				),
				'title' => 'Reports',
				'link' => '/backoffice/reports',
				'submenu' => array(
					'admin_reports' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD,
							self::OPS_SUPPORT
						),
						'title' => 'Select Report',
						'link' => '/backoffice/reports',
					),
					'admin_reports_entree' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD,
							self::OPS_SUPPORT
						),
						'title' => 'Shipping Entr&eacute;e Report',
						'link' => '/backoffice/reports-entree-delivered',
					),
					'admin_dashboard_new' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Dashboard',
						'link' => '/backoffice/dashboard-menu-based',
					),
					'admin_reports_trending' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Trending',
						'link' => '/backoffice/reports-trending-menu-based',
					)
				)
			),

			'store' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::OPS_LEAD,
					self::EVENT_COORDINATOR
				),
				'title' => 'Store/Franchise',
				'link' => '/backoffice/dashboard-activity-log',
				'submenu' => array(
					'admin_dashboard_activity_log' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD,
							self::OPS_SUPPORT
						),
						'title' => 'Activity Log',
						'link' => '/backoffice/dashboard-activity-log',
					),
					'admin_store_details' => array(
						'access' => array(
							self::FRANCHISE_OWNER,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Store Information',
						'link' => '/backoffice/store-details',
					),
					'admin_coupons' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::OPS_LEAD,
							self::EVENT_COORDINATOR
						),
						'title' => 'Coupon Codes',
						'link' => '/backoffice/coupons',
					),
					'admin_create_franchise' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Create Franchise',
						'link' => '/backoffice/create-franchise',
					),
					'admin_create_store' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Create Store',
						'link' => '/backoffice/create-store',
					),
					'admin_list_franchisees' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF
						),
						'title' => 'Search Franchisees',
						'link' => '/backoffice/list-franchisees',
					),
					'admin_list_franchise' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF
						),
						'title' => 'Search Franchises',
						'link' => '/backoffice/list-franchise',
					),
					'admin_list_stores' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF
						),
						'title' => 'Search Stores',
						'link' => '/backoffice/list-stores',
					),
					'admin_manage_box' => array(
						'access' => array(
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::OPS_LEAD
						),
						'title' => 'Manage Boxes',
						'link' => '/backoffice/manage-box',
					),
					'admin_resources' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::EVENT_COORDINATOR,
							self::GUEST_SERVER,
							self::OPS_LEAD
						),
						'title' => 'Links &amp; Resources',
						'link' => '/backoffice/resources',
					)
				)
			),

			'giftcards' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Gift Cards',
				'link' => '/backoffice/gift-card-management',
				'submenu' => array(
					'admin_gift_card_management_load' => array(
						'access' => true,
						'title' => 'Load Gift Card',
						'link' => '/backoffice/gift-card-load',
					),
					'admin_gift_card_management_order' => array(
						'access' => true,
						'title' => 'Order New Gift Card',
						'link' => '/backoffice/gift-card-order',
					),
					'admin_gift_card_management_balance' => array(
						'access' => true,
						'title' => 'Gift Card Balance',
						'link' => '/backoffice/gift-card-balance',
					),
					'admin_resend_gift_card_emails' => array(
						'access' => true,
						'title' => 'Resend Gift Card Emails',
						'link' => '/backoffice/resend-gift-card-emails',
					),
				)
			),
			'resources' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Links &amp; Resources',
				'link' => '/backoffice/resources',
			),
			'tools' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF
				),
				'title' => 'Tools',
				'link' => '/backoffice/tools',
				'submenu' => array(
					'admin_errors' => array(
						'access' => true,
						'title' => 'Error Log',
						'link' => '/backoffice/errors',
					),
					'admin_manage_survey' => array(
						'access' => true,
						'title' => 'Manage Survey',
						'link' => '/backoffice/manage-survey',
					),
					'admin_access_page_override' => array(
						'access' => true,
						'title' => 'Page Overrides',
						'link' => '/backoffice/access-page-override',
					),
					'admin_report_access_levels' => array(
						'access' => array(self::HOME_OFFICE_MANAGER),
						'title' => 'Home Office Access Levels',
						'link' => '/backoffice/report-access-levels',
					),
					'admin_manage_bundle' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Manage Bundles',
						'link' => '/backoffice/manage-bundle',
					),
					'admin_manage_dream_event_theme' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Manage Meal Prep Workshop Themes',
						'link' => '/backoffice/manage-dream-event-theme',
					),
					'admin_manage_dream_event_properties' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Manage Meal Prep Workshop Properties',
						'link' => '/backoffice/manage-dream-event-properties',
					),
					'admin_manage_coupon_codes' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Manage Coupon Codes',
						'link' => '/backoffice/manage-coupon-codes',
					),
					'admin_manage_delivered_shipping' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Shipping - ShipStation',
						'link' => 'https://signin.shipstation.com/login',
					)
				)
			),
			'import' => array(
				'access' => array(
					self::SITE_ADMIN,
					self::HOME_OFFICE_MANAGER
				),
				'title' => 'Menu',
				'link' => '/backoffice/tools',
				'submenu' => array(
					'admin_status' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Dream Dinners Status',
						'link' => '/backoffice/status',
					),
					'admin_menus' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Menu Setup',
						'link' => '/backoffice/menus',
					),
					'admin_import_menu' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Import Menu',
						'link' => '/backoffice/import-menu-reciprofity',
					),
					'admin_import_nutritionals' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Import Nutritionals',
						'link' => '/backoffice/import-nutritionals-reciprofity',
					),
					'admin_import_bundles' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Import Bundles',
						'link' => '/backoffice/import-bundles-reciprofity',
					),
					'admin_import_sidesmap' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Import Sides Map',
						'link' => '/backoffice/import-sidesmap-reciprofity',
					),
					'admin_menu_inspector' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Menu Inspector',
						'link' => '/backoffice/menu-inspector',
					),
					'admin_recipe_database' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Recipe Database',
						'link' => '/backoffice/recipe-database',
					)
				)
			)

		);

		return $navigationArray;
	}

	static function userFadminPageAccessArray($store_id = false)
	{
		$navigationArray = array(
			'home' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::MANUFACTURER_STAFF,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Home',
				'link' => '/backoffice',
				'submenu' => array()
			),
			'guests' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::MANUFACTURER_STAFF,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Guests',
				'link' => '/backoffice/list-users',
				'submenu' => array(
					'admin_list_users' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::MANUFACTURER_STAFF,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Guest Search',
						'link' => '/backoffice/list-users',
					),
					'admin_account' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::MANUFACTURER_STAFF,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Add New Guests',
						'link' => '/backoffice/account',
					)
				)
			),

			'sessions' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Calendar',
				'link' => '/backoffice/session-mgr',
				'submenu' => array(
					'admin_session_mgr' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Calendar',
						'link' => '/backoffice/session-mgr',
					),
					'admin_create_session' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Create a Session',
						'link' => 'javascript:onNBCreateSingleSession();',
					),
					'admin_session_template_mgr' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Template Manager',
						'link' => '/backoffice/session-template-mgr',
					),
					'admin_publish_sessions' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Publish Multiple Sessions',
						'link' => '/backoffice/publish-sessions',
					),
				)
			),

			'reports' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD,
					self::OPS_SUPPORT
				),
				'title' => 'Reports',
				'link' => '/backoffice/reports',
				'submenu' => array(
					'admin_reports' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD,
							self::OPS_SUPPORT
						),
						'title' => 'Select Report',
						'link' => '/backoffice/reports',
					),
					'admin_reports_entree' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::GUEST_SERVER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD,
							self::OPS_SUPPORT
						),
						'title' => 'Entr&eacute;e Report',
						'link' => '/backoffice/reports-entree',
					),
					'admin_dashboard_new' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Dashboard',
						'link' => '/backoffice/dashboard-menu-based',
					),
					'admin_reports_trending' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Trending',
						'link' => '/backoffice/reports-trending-menu-based',
					),
					'admin_reports_goal_management_v2' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Goal Management',
						'link' => '/backoffice/reports-goal-management-v2',
					),
					'admin_reports_customer' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Order History',
						'link' => '/backoffice/reports-customer',
					),
					'admin_reports_manufacturer_labels' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::OPS_LEAD
						),
						'title' => 'Manufacturing Labels',
						'link' => '/backoffice/reports-manufacturer-labels',
					)
				)
			),

			'store' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::OPS_LEAD,
					self::EVENT_COORDINATOR
				),
				'title' => 'Store/Franchise',
				'link' => '/backoffice/dashboard-activity-log',
				'submenu' => array(
					'admin_dashboard_activity_log' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::FRANCHISE_STAFF,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD,
							self::OPS_SUPPORT
						),
						'title' => 'Activity Log',
						'link' => '/backoffice/dashboard-activity-log',
					),
					'admin_store_details' => array(
						'access' => array(
							self::FRANCHISE_OWNER,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Store Information',
						'link' => '/backoffice/store-details',
					),
					'admin_coupons' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::OPS_LEAD,
							self::EVENT_COORDINATOR
						),
						'title' => 'Coupon Codes',
						'link' => '/backoffice/coupons',
					),
					'admin_menu_editor' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::OPS_LEAD
						),
						'title' => 'Menu Editor',
						'link' => '/backoffice/menu-editor',
					),
					'admin_menu_inventory_mgr' => array(
						'access' => (CStore::storeSupportsReciProfity($store_id, 100000) ? array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::OPS_LEAD
						) : array()),
						'title' => 'Inventory Manager',
						'link' => '/backoffice/menu-inventory-mgr',
					),
					'admin_create_franchise' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Create Franchise',
						'link' => '/backoffice/create-franchise',
					),
					'admin_create_store' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Create Store',
						'link' => '/backoffice/create-store',
					),
					'admin_list_franchisees' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF
						),
						'title' => 'Search Franchisees',
						'link' => '/backoffice/list-franchisees',
					),
					'admin_list_franchise' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF
						),
						'title' => 'Search Franchises',
						'link' => '/backoffice/list-franchise',
					),
					'admin_list_stores' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF
						),
						'title' => 'Search Stores',
						'link' => '/backoffice/list-stores',
					),
					'admin_manage_site_notice' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER
						),
						'title' => 'Manage Site Promotions',
						'link' => '/backoffice/manage-site-notice',
					),
					'admin_offsitelocations' => array(
						'access' => array(
							self::HOME_OFFICE_MANAGER,
							self::HOME_OFFICE_STAFF,
							self::FRANCHISE_OWNER,
							self::FRANCHISE_MANAGER,
							self::FRANCHISE_LEAD,
							self::EVENT_COORDINATOR,
							self::OPS_LEAD
						),
						'title' => 'Manage Community Pick Up Locations',
						'link' => '/backoffice/offsitelocations'
					)
				)
			),

			'giftcards' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Gift Cards',
				'link' => '/backoffice/gift-card-management',
				'submenu' => array(
					'admin_gift_card_management_load' => array(
						'access' => true,
						'title' => 'Load Gift Card',
						'link' => '/backoffice/gift-card-load',
					),
					'admin_gift_card_management_order' => array(
						'access' => true,
						'title' => 'Order New Gift Card',
						'link' => '/backoffice/gift-card-order',
					),
					'admin_gift_card_management_balance' => array(
						'access' => true,
						'title' => 'Gift Card Balance',
						'link' => '/backoffice/gift-card-balance',
					),
					'admin_resend_gift_card_emails' => array(
						'access' => true,
						'title' => 'Resend Gift Card Emails',
						'link' => '/backoffice/resend-gift-card-emails',
					),
				)
			),
			'resources' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF,
					self::FRANCHISE_OWNER,
					self::FRANCHISE_MANAGER,
					self::FRANCHISE_LEAD,
					self::FRANCHISE_STAFF,
					self::GUEST_SERVER,
					self::EVENT_COORDINATOR,
					self::OPS_LEAD
				),
				'title' => 'Links &amp; Resources',
				'link' => '/backoffice/resources',
			),
			'tools' => array(
				'access' => array(
					self::HOME_OFFICE_MANAGER,
					self::HOME_OFFICE_STAFF
				),
				'title' => 'Tools',
				'link' => '/backoffice/tools',
				'submenu' => array(
					'admin_errors' => array(
						'access' => true,
						'title' => 'Error Log',
						'link' => '/backoffice/errors',
					),
					'admin_manage_survey' => array(
						'access' => true,
						'title' => 'Manage Survey',
						'link' => '/backoffice/manage-survey',
					),
					'admin_access_page_override' => array(
						'access' => true,
						'title' => 'Page Overrides',
						'link' => '/backoffice/access-page-override',
					),
					'admin_report_access_levels' => array(
						'access' => array(self::HOME_OFFICE_MANAGER),
						'title' => 'Home Office Access Levels',
						'link' => '/backoffice/report-access-levels',
					),
					'admin_manage_bundle' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Manage Bundles',
						'link' => '/backoffice/manage-bundle',
					),
					'admin_manage_dream_event_theme' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Manage Meal Prep Workshop Themes',
						'link' => '/backoffice/manage-dream-event-theme',
					),
					'admin_manage_dream_event_properties' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Manage Meal Prep Workshop Properties',
						'link' => '/backoffice/manage-dream-event-properties',
					),
					'admin_manage_coupon_codes' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Manage Coupon Codes',
						'link' => '/backoffice/manage-coupon-codes',
					),
					'admin_manage_box' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Manage Boxes',
						'link' => '/backoffice/manage-box',
					),
					'admin_sanity_check' => array(
						'access' => array(
							self::SITE_ADMIN
						),
						'title' => 'Sanity Check',
						'link' => '/backoffice/sanity-check',
					)
				)
			),
			'import' => array(
				'access' => array(
					self::SITE_ADMIN,
					self::HOME_OFFICE_MANAGER
				),
				'title' => 'Menu',
				'link' => '/backoffice/tools',
				'submenu' => array(
					'admin_status' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Dream Dinners Status',
						'link' => '/backoffice/status',
					),
					'admin_menus' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Menu Setup',
						'link' => '/backoffice/menus',
					),
					'admin_import_menu_reciprofity' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Import Menu',
						'link' => '/backoffice/import-menu-reciprofity',
					),
					'admin_import_nutritionals_reciprofity' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Import Nutritionals',
						'link' => '/backoffice/import-nutritionals-reciprofity',
					),
					'admin_import_bundles_reciprofity' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Import Bundles',
						'link' => '/backoffice/import-bundles-reciprofity',
					),
					'admin_import_sidesmap_reciprofity' => array(
						'access' => array(self::SITE_ADMIN),
						'title' => 'Import Sides Map',
						'link' => '/backoffice/import-sidesmap-reciprofity',
					),
					'admin_menu_inspector' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Menu Inspector',
						'link' => '/backoffice/menu-inspector',
					),
					'admin_recipe_database' => array(
						'access' => array(
							self::SITE_ADMIN,
							self::HOME_OFFICE_MANAGER
						),
						'title' => 'Recipe Database',
						'link' => '/backoffice/recipe-database',
					)
				)
			)
		);

		return $navigationArray;
	}

	function addGuestLTDDonationTotals()
	{
		$this->LTD_Round_UP_Lifetime_total = 0;
		$this->LTD_MOTM_Lifetime_total = 0;

		if (empty($this->id))
		{
			return;
		}

		$LTDRetriever = new DAO();

		$LTDRetriever->query("select o.user_id, sum(o.subtotal_ltd_menu_item_value) as MOTM_total, sum(ltd_round_up_value) as RU_total from orders o
	    join booking b on b.order_id = o.id and b.`status` = 'ACTIVE'
	    where o.user_id = {$this->id}
	    group by o.user_id");

		if ($LTDRetriever->fetch())
		{
			$this->LTD_Round_UP_Lifetime_total = $LTDRetriever->RU_total;
			$this->LTD_MOTM_Lifetime_total = $LTDRetriever->MOTM_total;
		}
	}

	/*
	 * Returns true if the user is a preferred user at ANY store
	*/
	function isUserPreferred()
	{
		if (empty($this->id))
		{
			return false;
		}

		$UserPreferredDAO = DAO_CFactory::create('user_preferred');
		$UserPreferredDAO->user_id = $this->id;

		if ($UserPreferredDAO->find(true))
		{
			return true;
		}

		return false;
	}

	/*
 	* Returns true if the user is_partial_account
	*/
	function isUserPartial()
	{
		if (empty($this->id))
		{
			return false;
		}

		if (!empty($this->is_partial_account))
		{
			return true;
		}

		return false;
	}

	// -----------------------------------------Get the users store -------------------------------
	// The store can be derived from different sources and the search order is:
	// 2) Request variable (POST only)
	// 3) Cart - but the cart is only checked if no store was found in the first 2 steps
	//			if the cart holds a store id different from the store found in the first 2 steps then
	//			the cart is emptied and rebuilt for the new store
	// 4) Check the browser session for a cookie is no store in cart
	//
	//	when getCurrentStoreViewed() is used
	// 5) Check last viewed store cookie
	//

	static function getCurrentStore($OrderObj = false)
	{
		$methodFound = 0;
		$storeId = null;

		if (isset($_POST['store']) && is_numeric($_POST['store']))
		{
			// 2) Check Request (Direct access)
			$storeObj = DAO_CFactory::create('store');
			$storeObj->id = $_POST['store'];

			if ($storeObj->find(true))
			{
				// can't set inactive stores on customer site
				if (CApp::$adminView || !empty($storeObj->active))
				{
					$storeId = $storeObj->id;
					$methodFound = 2;
				}
			}
		}

		// Check Cart
		if (isset($OrderObj) && !empty($OrderObj->store_id) && empty($storeId))
		{
			// 3) Check cart
			$storeId = $OrderObj->store_id;
			$methodFound = 3;
		}

		// 4) Check homestore setting
		if ((!$storeId) && CBrowserSession::getCurrentStore())
		{
			$storeId = CBrowserSession::getCurrentStore();
			$methodFound = 4;
		}

		return array(
			$storeId,
			$methodFound
		);
	}

	static function getUserCreditArray($User)
	{
		$creditArray = array();

		$creditArray['credit'] = array(
			'storeCredits' => CStoreCredit::getActiveGCCreditByUser($User->id),
			'refstoreCredits' => CStoreCredit::getActiveReferralCreditByUser($User->id),
			'directstoreCredits' => CStoreCredit::getActiveDirectCreditByUser($User->id),
			'available_pp_credits' => CPointsCredits::getUsersAvailableCredits($User->id)
		);

		$hasCredits = false;
		foreach ($creditArray['credit'] as $item)
		{
			if (!empty($item))
			{
				$hasCredits = true;
			}
		}

		if (!$hasCredits)
		{
			return false;
		}

		return $creditArray;
	}

	static function getUserTestRecipes($User)
	{
		$recipe = DAO_CFactory::create('food_testing_survey_submission');
		$recipe->query("SELECT
				ft.title,
				ftss.id,
				fts.food_testing_id,
				ftss.food_testing_survey_id,
				ftss.timestamp_received,
				ftss.timestamp_completed,
				ftss.timestamp_updated,
				ftss.timestamp_created
				FROM food_testing_survey_submission AS ftss
				INNER JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id
				INNER JOIN food_testing AS ft ON ft.id = fts.food_testing_id
				WHERE ftss.user_id = '" . $User->id . "'
				AND (ISNULL(ftss.timestamp_completed) OR ftss.timestamp_completed = '1970-01-01 00:00:01')
				AND ftss.timestamp_received IS NOT NULL
				AND ftss.is_deleted = '0'
				AND fts.is_deleted = '0'
				AND ft.is_deleted = '0'");

		$recipes = array();
		while ($recipe->fetch())
		{
			$recipes[$recipe->id] = $recipe->toArray();
			if ($recipes[$recipe->id]['timestamp_created'] == '1970-01-01 00:00:01')
			{
				$recipes[$recipe->id]['timestamp_created'] = $recipes[$recipe->id]['timestamp_updated'];
			}
		}

		return $recipes;
	}

	function getCurrentStoreViewed($OrderObj = false)
	{
		list ($storeId, $methodFound) = self::getCurrentStore($OrderObj);

		// 5) Check last viewed store
		if ((!$storeId) && CBrowserSession::getLastViewedStore())
		{
			$storeId = CBrowserSession::getLastViewedStore();
			$methodFound = 5;
		}

		return array(
			$storeId,
			$methodFound
		);
	}

	/**
	 * Check to see if the user has an existing qualifying minimum order for the specified
	 * menu and store. A 'qualifying' order is significant in the context of additional order
	 * for a given menu.
	 *
	 * This is only looking to see if a minimum order is explicitly marked as such...i.e. that the
	 * 'is_qualifing' attribute of the order is true and the 'qualifying_menu_id' attribute has a value.
	 *
	 * This is not checking to see if there is an order that can be marked as the qualifying
	 *
	 * WIll not find one if the Store/Menu is not configured to allow additional ordering
	 *
	 * @param $store_id
	 * @param $menu_id
	 *
	 * @return bool true if the customer already has a minimum order
	 */
	public function hasMinimumQualifyingOrderDefined($store_id, $menu_id)
	{

		if (!COrderMinimum::allowsAdditionalOrdering($store_id, $menu_id))
		{
			return false;//doesn't allow additional ordering
		}

		if (empty($store_id) || empty($menu_id))
		{
			return false;//must know store and menu
		}

		$bookingObj = DAO_CFactory::create('booking');
		$sql = "SELECT
				b.id
				FROM booking b
				INNER JOIN orders o ON o.id = b.order_id AND o.is_deleted = '0'
				INNER JOIN user u ON u.id = o.user_id
				WHERE o.store_id = '" . $store_id . "'
				AND o.is_qualifying = 1
				AND o.qualifying_menu_id = '" . $menu_id . "'
				AND b.user_id = '" . $this->id . "'
				AND b.status = 'ACTIVE'
				AND b.is_deleted = '0'";
		$bookingObj->query($sql);

		if ($bookingObj->N > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 * Look to see that a monthly minimum qualifying order does not exist. If it does not,
	 * then find an order that does qualify and updated its attributes (is_qualifying,qualifying_menu_id) accordingly
	 *
	 * @param $menu_id
	 * @param $store_id
	 *
	 * @return bool true if qualifying order was found and updated, false if qualifying order
	 * was not found to update, or one already exists
	 */
	public function establishMonthlyMinimumQualifyingOrder($menu_id, $store_id)
	{

		if (!COrderMinimum::allowsAdditionalOrdering($store_id, $menu_id))
		{
			return false;//doesn't allow additional ordering
		}

		$hasExistingMinimumOrder = $this->hasMinimumQualifyingOrderDefined($store_id, $menu_id);
		if (!$hasExistingMinimumOrder)
		{
			$Orders = DAO_CFactory::create('orders');
			$sql = "SELECT
					o.*
					FROM orders o
					LEFT JOIN booking b ON b.order_id = o.id
					LEFT JOIN store st ON st.id = o.store_id
					LEFT JOIN session s ON b.session_id = s.id
					WHERE b.status = 'ACTIVE'
					AND o.user_id = '" . $this->id . "'
					AND o.is_deleted = 0
					AND s.menu_id = '" . $menu_id . "'
					AND s.store_id = '" . $store_id . "'
					ORDER BY o.timestamp_created DESC LIMIT 20";
			$Orders->query($sql);

			while ($Orders->fetch())
			{
				$Orders->reconstruct();
				$isQualified = COrderMinimum::doesOrderQualifiesAsMinimum($Orders);
				if ($isQualified)
				{
					//update order
					$Orders->is_qualifying = 1;
					$Orders->qualifying_menu_id = $menu_id;
					$Orders->update();

					return true;
				}
			}
		}

		return false;//if qualifying order was not found to update, or one already exists
	}

	/**
	 * Return the id of the user's  existing qualifying minimum order
	 *
	 *
	 * This is only looking to see if a minimum order is explicitly set for this user...i.e. that the
	 * is_qualifing is true and the qualifying_menu_id has a value.
	 *
	 * @param $menu_id
	 * @param $store_id
	 *
	 * @return null|int order id of Qualifying order if one exists
	 */
	public function fetchMinimumQualifyingOrderId($menu_id, $store_id)
	{

		if (!COrderMinimum::allowsAdditionalOrdering($store_id, $menu_id))
		{
			return null;//doesn't allow additional ordering
		}

		$orders = DAO_CFactory::create('orders');
		$sql = "SELECT
				o.id
				FROM orders o
				INNER JOIN booking b ON o.id = b.order_id AND b.is_deleted = '0'
				WHERE o.store_id = '" . $store_id . "'
				AND o.is_qualifying = 1
				AND o.qualifying_menu_id = '" . $menu_id . "'
				AND o.is_deleted = '0'
				AND o.user_id = '" . $this->id . "'
				AND b.status = 'ACTIVE' 
				order by o.id asc limit 1";
		$orders->query($sql);
		if ($orders->fetch(true))
		{
			return $orders->id;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Given an order:
	 *        - Determine if there is a 'qualifying' order that proceeded it
	 *        - Return that order id
	 *
	 * Note: If this is less than minimum and no qualifying order exists then it can be assumed
	 * that the order was create via BackOffice and not directly by a customer.
	 *
	 * @param $orderObj A hydrated order object which contains store/menu data.
	 *
	 * @return null|mediumint Order Id of the qualifying order that proceeded this order
	 */
	public function determineQualifyingOrderId($orderObj)
	{
		if (is_null($orderObj) || empty($orderObj->id))
		{
			return null;
		}
		$menu_id = $orderObj->getSessionObj(true)->menu_id;
		$store_id = $orderObj->getStore()->id;

		//1) check less than minimum for menu/store
		$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $store_id, $menu_id);

		if ($minimum->getAllowsAdditionalOrdering())
		{
			//			$count = null;
			//			if ($minimum->getMinimumType() == COrderMinimum::SERVING)
			//			{
			//				$count = $orderObj->countContributingServings();
			//			}
			//
			//			if ($minimum->getMinimumType() == COrderMinimum::ITEM)
			//			{
			//				$count = $orderObj->countItems();
			//			}

			//No longer care if this order is greater than or less that minimum
			//if(is_numeric($count) && $count < $minimum->getMinimum()){
			//2) Fetch the qualifying order
			$id = $this->fetchMinimumQualifyingOrderId($menu_id, $store_id);
			if ($id != $orderObj->id)
			{
				//3) if not this order return id.
				return $id; //will return null if on is not found
			}
			//}

		}

		return null;
	}

	/**
	 * Given an order:
	 *        - see if it is a greater than or equal to minimum order as configured for store/menu
	 *            - if so, return true otherwise false.
	 *
	 *
	 * @param $orderObj A hydrated order object which contains store/menu data.
	 *
	 * @return boolean true if this order is greater than or equal to configured minimum for this
	 *                 user/store/menu combination
	 */
	public function doesOrderMeetMinimum($orderObj)
	{
		if (is_null($orderObj) || empty($orderObj->id))
		{
			return false;
		}
		$menu_id = $orderObj->getSessionObj(true)->menu_id;
		$store_id = $orderObj->getStore()->id;

		//1) check less than minimum for menu/store
		$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $store_id, $menu_id);

		$count = null;
		if ($minimum->getMinimumType() == COrderMinimum::SERVING)
		{
			$count = $orderObj->countContributingServings();
		}

		if ($minimum->getMinimumType() == COrderMinimum::ITEM)
		{
			$count = $orderObj->countItems();
		}

		if (is_numeric($count) && $count >= $minimum->getMinimum())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function hasAccessToStore($store_id)
	{
		if ($this->user_type == self::SITE_ADMIN || $this->user_type == self::HOME_OFFICE_STAFF || $this->user_type == self::HOME_OFFICE_MANAGER)
		{
			return true;
		}

		if (!empty($this->accessToStore[$store_id]))
		{
			return true;
		}

		$UTS = DAO_CFactory::create('user_to_store');
		$UTS->user_id = $this->id;
		$UTS->store_id = $store_id;

		if ($UTS->find(false))
		{
			return $this->accessToStore[$store_id] = true;
		}

		return $this->accessToStore[$store_id] = false;
	}

	function homeStoreAllowsMealCustomization()
	{
		//allow preferences to be set as long a at least one store allow customization

		if (is_null($this->id))
		{
			return false;
		}

		//TODO: evan-l: change when ready to go live
		return $this->anyStoreAllowsMealCustomization();

		//		$home_store = DAO_CFactory::create('store');
		//		$home_store->id = $this->home_store_id;
		//		$home_store->find(true);
		//
		//		return $home_store->supports_meal_customization;

	}

	function anyStoreAllowsMealCustomization()
	{

		if (is_null($this->id))
		{
			return false;
		}

		$store = DAO_CFactory::create('store');
		$store->supports_meal_customization = 1;
		$store->find(true);

		return ($store->N > 0);
	}

	static function updateUserDigest($user_id, $column, $value)
	{
		$userDigestDAO = DAO_CFactory::create('user_digest');
		$userDigestDAO->user_id = $user_id;

		if ($userDigestDAO->find(true))
		{
			$userDigestDAO->$column = $value;
			$userDigestDAO->update();

			return 'updated';
		}
		else
		{
			$userDigestDAO->$column = $value;
			$userDigestDAO->insert();

			return 'inserted';
		}
	}

	static public function getUserByCookie()
	{
		if (!empty(self::$_cookieuser))
		{
			return self::$_cookieuser;
		}

		$uid = CBrowserSession::getValue('DDUID');

		if (!empty($uid) && is_numeric($uid))
		{
			$userObj = DAO_CFactory::create('user');
			$userObj->id = $uid;

			if ($userObj->find(true))
			{
				$userObj->fetch();

				self::$_cookieuser = $userObj;

				return self::$_cookieuser;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	static function getCartIfExists($doNotNeedStore = false)
	{
		require_once('includes/CCartStorage.inc');

		$key = CBrowserSession::getCartKey();

		if (!$key)
		{
			return null;
		}

		$cartDAO = DAO_CFactory::create('cart');
		$cartDAO->cart_key = $key;
		if ($cartDAO->find(true))
		{
			if ($doNotNeedStore)
			{
				if (CCartStorage::contentRowExists($cartDAO->cart_contents_id))
				{
					return CCart2::instance()->getCartArrays();
				}
				else
				{
					return null;
				}
			}
			else
			{
				if (CCartStorage::quietTestForStoreID($cartDAO->cart_contents_id))
				{
					return CCart2::instance()->getCartArrays();
				}
				else
				{

					$storeIDFromSession = CCartStorage::getStoreFromSessionIfExists($cartDAO->cart_contents_id);

					if ($storeIDFromSession)
					{
						return CCart2::instance()->getCartArrays();
					}

					return null;
				}
			}
		}

		return null;
	}

	function hasOrderForMenu($menu_id)
	{

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->query("select b.id from booking b join session s on s.id = b.session_id and s.menu_id = $menu_id where b.user_id = {$this->id} and b.status = 'ACTIVE' ");
		if ($bookingObj->N > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 *    Delivered only if at least 1 delivered order and ...not retail orders OR retails store orders are
	 *  from inactive store
	 * @return bool
	 * @throws Exception
	 */
	static function hasDeliveredOrdersOnly()
	{
		$orderType = null;
		$id = self::getCurrentUser()->id;

		if (!empty($id) && is_numeric($id))
		{
			//get from user digest - which is updated on new orders
			$userDigestDAO = DAO_CFactory::create('user_digest');
			$userDigestDAO->user_id = $id;

			if ($userDigestDAO->find(true))
			{
				$orderType = $userDigestDAO->customer_order_type;
			}
			if ($orderType === 'DELIVERED_ORDERS_ONLY')
			{
				return true;
			}
		}

		return false;
	}

	function nextSession($after_date = false, $storeID = false)
	{
		if ($after_date)
		{
			$nowDate = $after_date;
		}
		else
		{
			$nowTime = time();
			$nowDate = date('Y-m-d H-i-s', $nowTime);
			//First adjust time span to local store time

			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select timezone_id from store where id = $storeID");

			if ($storeObj->N > 0)
			{
				$storeObj->fetch();
				$nowDate = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $nowTime));
			}
		}

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->query("SELECT
				b.id,
				b.session_id,
				b.order_id,
				b.booking_type,
				s.store_id,
				s.menu_id,
				s.session_type,
				s.session_start
				FROM booking b
				INNER JOIN session s ON s.id = b.session_id AND s.is_deleted = '0'
				INNER JOIN orders o ON o.id = b.order_id AND o.is_deleted = '0'
				WHERE s.session_start > '" . $nowDate . "'
				AND b.user_id = '" . $this->id . "'
				AND b.status = 'ACTIVE'
				AND b.is_deleted = '0'
				ORDER BY s.session_start ASC");

		while ($bookingObj->fetch())
		{
			$this->nextSession[] = clone $bookingObj;
		}
	}



	// tolerance - seconds to subtract from current time - this has '
	// the effect of given the more time
	// For example if we pass in 7200 (2 hours) then a session will test for future will return true until 2 hours
	// after the session
	function hasPendingOrderForInStoreCalculation($currentOrderId, $storeID, $menu_id, $tolerance = 0, $exlude_order = false, $useOrderMinimums = true)
	{
		if ($storeID)
		{
			if ($useOrderMinimums)
			{
				$order_minimum = COrderMinimum::fetchInstance(COrders::STANDARD, $storeID, $menu_id);

				if (!is_null($order_minimum))
				{
					if ($order_minimum->getMinimumType() == COrderMinimum::SERVING)
					{
						$minimumClause = " AND o.servings_core_total_count >= " . $order_minimum->getMinimum();
					}
					if ($order_minimum->getMinimumType() == COrderMinimum::ITEM)
					{
						$minimumClause = " AND o.menu_items_core_total_count >= " . $order_minimum->getMinimum();
					}
				}
			}
			else
			{
				$minimumClause = " AND o.servings_total_count > 17 ";
			}

			$nowTime = time() - $tolerance;
			$nowDate = date('Y-m-d H-i-s', $nowTime);
			//First adjust time span to local store time

			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select timezone_id from store where id = $storeID");

			if ($storeObj->N > 0)
			{
				$storeObj->fetch();
				$nowDate = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $nowTime));
			}

			$exlude_order_clause = "";
			if ($exlude_order)
			{
				$exlude_order_clause = " and o.id <> $exlude_order ";
			}

			$bookingObj = DAO_CFactory::create('booking');

			$orderIdClause = '';
			if (!is_null($currentOrderId))
			{
				$orderIdClause = ' AND o.id <> ' . $currentOrderId . ' ';
			}
			$q = "SELECT
						b.id, o.id as order_id
						FROM booking b
						JOIN session s ON s.id = b.session_id
						JOIN orders o ON o.id = b.order_id
						WHERE s.session_start > '$nowDate'
						AND b.user_id = " . $this->id . "
						AND b.status = 'ACTIVE'
						$exlude_order_clause
						$minimumClause
						AND o.is_TODD = 0
						$orderIdClause
						AND o.type_of_order <> 'DREAM_TASTE'
						order by s.session_start desc";
			$bookingObj->query($q);

			if ($bookingObj->N > 0)
			{
				$bookingObj->fetch();

				return $bookingObj->order_id;
			}
		}
		else
		{
			CLog::Assert(false, "Store ID is required by hasPendingOrderForInStoreCalculation");

			return false;
		}
	}


	// tolerance - seconds to subtract from current time - this has '
	// the effect of given the more time
	// For example if we pass in 7200 (2 hours) then a session will test for future will return true until 2 hours
	// after the session
	function hasPendingOrder($storeID = false, $tolerance = 0)
	{
		if ($storeID)
		{
			$nowTime = time() - $tolerance;
			$nowDate = date('Y-m-d H-i-s', $nowTime);
			//First adjust time span to local store time

			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select timezone_id from store where id = $storeID");

			if ($storeObj->N > 0)
			{
				$storeObj->fetch();
				$nowDate = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $nowTime));
			}

			$bookingObj = DAO_CFactory::create('booking');
			$bookingObj->query("SELECT
						b.id
						FROM booking b
						JOIN session s ON s.id = b.session_id
						JOIN orders o ON o.id = b.order_id
						WHERE DATEDIFF('" . $nowDate . "', s.session_start ) <= 0
						AND b.user_id = " . $this->id . "
						AND b.status = 'ACTIVE'
						AND o.servings_total_count > 17
						AND o.is_TODD = 0
						AND o.type_of_order <> 'DREAM_TASTE'");

			if ($bookingObj->N > 0)
			{
				return true;
			}
		}
		else
		{
			// has pending order at any store

			$Orders = DAO_CFactory::create('orders');
			$Orders->query("SELECT
					b.status,
					s.session_start,
					st.timezone_id
					FROM orders o
					LEFT JOIN booking b ON b.order_id = o.id
					LEFT JOIN store st ON st.id = o.store_id
					LEFT JOIN session s ON b.session_id = s.id
					WHERE b.status = 'ACTIVE'
					AND o.user_id = '" . $this->id . "'
					AND o.is_deleted = 0
					ORDER BY s.session_start DESC LIMIT 1");

			while ($Orders->fetch())
			{
				$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($Orders->timezone_id) - $tolerance;

				if ($Orders->session_start && (strtotime($Orders->session_start) > $now) && $Orders->status == CBooking::ACTIVE)
				{
					return true;
				}
			}
		}

		return false;
	}

	function hasPendingDreamRewardsOrder($storeID = false)
	{

		CLog::Assert(is_numeric($storeID), "valid storeID must be passed into hasPendingDreamRewardsOrder");

		$nowTime = time();
		$nowDate = date('Y-m-d H-i-s', $nowTime);
		//First adjust time span to local store time
		if ($storeID)
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select timezone_id from store where id = $storeID");
			if ($storeObj->N > 0)
			{
				$storeObj->fetch();
				$nowDate = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $nowTime));
			}
		}
		else
		{
			return false; // there must be a store id
		}

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->query("select b.id from booking b join session s on s.id = b.session_id join orders o on o.id = b.order_id join store st on st.id = $storeID where DATEDIFF( DATE('$nowDate'), DATE(s.session_start) ) <= 0 and b.user_id = {$this->id} and b.status = 'ACTIVE' and st.supports_dream_rewards;");

		if ($bookingObj->N > 0)
		{
			return true;
		}

		return false;
	}

	function isCCPA_Enabled()
	{
		if (DD_SERVER_NAME != 'LIVE')
		{
			return true;
		}

		// check customer address for eligible state
		$Addr = $this->getPrimaryAddress();

		if ($Addr->state_id == 'CA')
		{
			return true;
		}

		// check customer shipping address for eligible state
		$Addr2 = $this->getShippingAddress();

		if ($Addr2->state_id == 'CA')
		{
			return true;
		}

		// check home store for eligible state
		list($storeInfo, $ownerInfo, $StoreObj) = CStore::getStoreAndOwnerInfo($this->home_store_id);

		if ($StoreObj->state_id == 'CA')
		{
			return true;
		}

		return false;
	}

	function isAccountDeleteEligible()
	{
		return true;//Guest always has option to delete
		if (DD_SERVER_NAME != 'LIVE')
		{
			return true;
		}

		// check customer address for eligible state
		$Addr = $this->getPrimaryAddress();

		if ($Addr->state_id == 'CA')
		{
			return true;
		}

		// check customer shipping address for eligible state
		$Addr2 = $this->getShippingAddress();

		if ($Addr2->state_id == 'CA')
		{
			return true;
		}

		// check home store for eligible state
		/*
		list($storeInfo, $ownerInfo, $StoreObj) = CStore::getStoreAndOwnerInfo($this->home_store_id);

		if ($StoreObj->state_id == 'CA')
		{
			return true;
		}
		*/

		return false;
	}

	function canOrderOnlineWithDreamRewards($storeID = false)
	{
		if (($this->dream_reward_status == 1 || $this->dream_reward_status == 3) && $this->dream_rewards_version == 2 && $this->hasPendingDreamRewardsOrder($storeID) && $this->dream_reward_level > 5)
		{
			return true;
		}

		return false;
	}

	//returns true if user was in store (had a session) on the day of the passed in
	// time or up to 7 days before.
	function wasInStoreNearTime($time, $excludedSession = false)
	{
		// note: time should be a mysql timestamp: YYYY-MM-DD HH:MM:SS

		$excludedSessionClause = "";
		if ($excludedSession)
		{
			$excludedSessionClause = "s.id <> $excludedSession and";
		}

		$bookingObj = DAO_CFactory::create('booking');

		$bookingObj->query("select b.order_id from booking b
				join session s on s.id = b.session_id
				join orders o on o.id = b.order_id
				where DATEDIFF( '$time', s.session_start ) >= 0 and DATEDIFF( '$time', s.session_start ) < 7
				and $excludedSessionClause b.user_id = {$this->id} and b.status = 'ACTIVE' order by s.session_start desc");
		if ($bookingObj->N > 0)
		{
			$bookingObj->fetch();

			return $bookingObj->order_id;
		}

		return false;
	}

	function isEligibleForIntro($StoreObj = false)
	{
		$canOrderIntro = true;

		if ($this->isLoggedIn())
		{
			if (!$this->isNewBundleCustomer())
			{
				$canOrderIntro = false;
			}
		}

		if ($StoreObj && !$StoreObj->storeSupportsIntroOrders())
		{
			$canOrderIntro = false;
		}

		if (!$StoreObj)
		{
			$CartObj = CCart2::instance();
			$StoreObj = $CartObj->getOrder()->getStore();

			if ($StoreObj)
			{
				if (!$StoreObj->storeSupportsIntroOrders())
				{
					$canOrderIntro = false;
				}
			}
		}

		// test environment can place intro
		if (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS)
		{
			$canOrderIntro = true;
		}

		return $canOrderIntro;
	}

	function isEligibleForFundraiser($session_id)
	{
		if (!self::isLoggedIn())
		{
			return true;
		}

		$Session = DAO_CFactory::create('session');
		$Session->id = $session_id;

		if ($Session->find(true))
		{
			$bookings = $Session->getBookingsForSession();
		}

		if (!empty($bookings))
		{
			// user can only place one order per fundraiser
			foreach ($bookings as $booking_id => $booking)
			{
				if ($booking['user_id'] == $this->id && $booking['status'] == CBooking::ACTIVE)
				{
					return false;
				}
			}
		}

		return true;
	}

	function isEligibleForSessionRSVP_DreamTaste($DAO_session = false, $DAO_orders = false, $CartObj = false, $sessionArray = false)
	{
		if ($DAO_session && !empty($DAO_session->DAO_dream_taste_event_properties->existing_guests_can_attend))
		{
			return true;
		}

		if ($sessionArray && !empty($sessionArray['dream_taste_existing_guests_can_attend']))
		{
			return true;
		}

		if (!$this->isEligibleForDreamTaste($DAO_orders, $CartObj))
		{
			// they have had an order in the last year
			return false;
		}

		$DAO_session_rsvp = DAO_CFactory::create('session_rsvp');
		$DAO_session_rsvp->user_id = $this->id;
		$DAO_session_rsvp->whereAdd("session_rsvp.timestamp_created > DATE_SUB(NOW(), INTERVAL 1 YEAR)");

		$DAO_session_rsvp->fetch();

		if ($DAO_session_rsvp->find())
		{
			return false;
		}

		return true;
	}

	function isEligibleForDreamTaste($DAO_orders = false, $CartObj = false)
	{
		if (empty($this->id))
		{
			return true;
		}

		$DAO_booking = DAO_CFactory::create('booking');
		$DAO_booking->user_id = $this->id;
		$DAO_booking->status = CBooking::ACTIVE;

		$DAO_booking->selectAdd();
		$DAO_booking->selectAdd("booking.*");

		$DAO_orders = DAO_CFactory::create('orders');
		$DAO_orders->is_TODD = 0;
		$DAO_orders->whereAdd("orders.timestamp_created > DATE_SUB(NOW(), INTERVAL 1 YEAR)");

		$DAO_booking->joinAddWhereAsOn($DAO_orders);
		$DAO_booking->joinAddWhereAsOn(DAO_CFactory::create('session'));

		if ($DAO_booking->find())
		{
			return false;
		}

		return true;
	}

	function isNewBundleCustomer()
	{
		if (empty($this->id))
		{
			return true;
		}

		$Order = DAO_CFactory::create('orders');
		$Order->query("SELECT
				o.id,
				o.timestamp_created,
				s.session_type
				FROM booking AS b
				JOIN orders AS o ON o.id = b.order_id
				INNER JOIN `session` AS s ON s.id = b.session_id
				WHERE b.user_id = '" . $this->id . "'
				AND b.`status` = 'ACTIVE'
				AND b.is_deleted = 0
				AND o.timestamp_created > DATE_SUB(NOW(), INTERVAL 1 YEAR)");

		if ($Order->N > 0)
		{
			return false;
		}

		return true;
	}

	function isNewReferralCustomer()
	{
		if (empty($this->id))
		{
			return true;
		}

		$Order = DAO_CFactory::create('orders');
		$Order->query("select o.id, o.timestamp_created from booking b join orders o on o.id = b.order_id
					where b.user_id = {$this->id} and b.status = 'ACTIVE' and b.is_deleted = 0 and o.grand_total > 0 and o.is_TODD = 0
					and (o.timestamp_created > DATE_SUB(NOW(), INTERVAL 790 DAY) and o.timestamp_created < DATE_SUB(NOW(), INTERVAL 60 DAY))");

		if ($Order->N > 0)
		{
			return false;
		}

		return true;
	}

	function hasTriggeredReferralReward()
	{
		if (empty($this->id))
		{
			return true;
		}

		$Order = DAO_CFactory::create('orders');
		$Order->query("select cr.id from customer_referral cr
					where (cr.store_credit_id > 0 or cr.plate_points_reward_id > 0) and referred_user_id = {$this->id} and cr.is_deleted = 0");

		if ($Order->N > 0)
		{
			return true;
		}

		return false;
	}

	function getReferralRewardQualifyingOrderId()
	{

		$Order = DAO_CFactory::create('orders');
		$Order->query("select o.id, o.timestamp_created, s.session_start, o.bundle_id from booking b join orders o on o.id = b.order_id
					join session s on s.id = b.session_id
					where b.user_id = {$this->id} and b.status = 'ACTIVE' and b.is_deleted = 0 and o.grand_total > 0 and o.is_TODD = 0 and (o.servings_total_count > 35 or o.bundle_id > 0)
					and o.timestamp_created > DATE_SUB(NOW(), INTERVAL 60 DAY)
					and o.type_of_order not in ('DREAM_TASTE')
		order by o.timestamp_created limit 1");

		if ($Order->N == 0)
		{
			return false;
		}

		$Order->fetch();

		$rewardDate = date("Y-m-d H:i:s", strtotime($Order->session_start) + (86400 * 3));

		$OrderData = array(
			'id' => $Order->id,
			'sessionTime' => $Order->session_start,
			'isIntro' => (empty($Order->bundle_id) ? false : true),
			'reward_date' => $rewardDate
		);

		return array(
			$Order->id,
			$OrderData
		);
	}

	function hasMultiStoreAccess()
	{
		if (!$this->isFranchiseAccess())
		{
			return true;
		}

		$DAO_user_to_store = DAO_CFactory::create('user_to_store', true);
		$DAO_user_to_store->user_id = $this->id;
		$DAO_user_to_store->find();

		return $DAO_user_to_store->N > 1;
	}

	function isFranchiseAccess()
	{

		if (empty($this->user_type))
		{
			return false;
		}

		if ($this->user_type == self::CUSTOMER || $this->user_type == self::GUEST || $this->user_type == self::SITE_ADMIN || $this->user_type == self::HOME_OFFICE_MANAGER || $this->user_type == self::HOME_OFFICE_STAFF)
		{
			return false;
		}

		return true;
	}

	function getInitialFranchiseStore()
	{

		if ($this->user_type == self::SITE_ADMIN)
		{
			return $this->home_store_id;
		}

		$uts = DAO_CFactory::create('user_to_store');
		$uts->user_id = $this->id;
		if ($uts->find(true))
		{
			return $uts->store_id;
		}

		throw new Exception("No stores assigned to this user.");
	}

	function unEnrollInDFL($programID)
	{
		// get Enrollment data
		$enrollment = DAO_CFactory::create('user_program_membership');
		$enrollment->query("select * from user_program_membership where user_id = {$this->id} and is_deleted = 0 and menu_program_type_id = $programID and membership_status <> 2 and membership_status <> 3");

		if ($enrollment->N == 0)
		{
			//somehow a DFL order was placed but the user has no enrollment
			CLog::RecordNew('ERROR', 'DFL cancel attempt was placed for user without active enrollment');
		}
		else
		{
			CLog::Assert($enrollment->N == 1, "should only be one active enrollment per user per program");
			$enrollment->fetch();
			$curState = clone($enrollment);
			$enrollment->membership_status = self::DFL_STATUS_CANCELLED;
			$enrollment->update($curState);
			CUserHistory::recordUserEvent($this->id, 'null', 'null', 201, 'null', 'null', 'DFL Enrollment canceled for ' . $this->firstname . " " . $this->lastname);
		}
	}

	static function registerSubscription($orderDAO, $productDAO)
	{

		// Do they have an existing subscription? How should we handle that.
		if (self::isUserEnrolledInDFL($orderDAO->user_id, 2))
		{
			//TODO: maybe we should throw here , if the subscription cost money the we can't let this go by undetected.
			CLog::RecordIntense('User already has subscription - for order: ' . $orderDAO->id, 'ryan.snook@dreamdinners.com');

			return;
		}

		$epto = false;
		$package = DAO_CFactory::create('enrollment_package');
		$package->product_id = $productDAO->id;
		if ($package->find(true))
		{

			$userMembership = DAO_CFactory::create('user_program_membership');

			$userMembership->user_id = $orderDAO->user_id;
			$userMembership->menu_program_type_id = 2; // DFL Diabetic
			$userMembership->membership_status = self::DFL_STATUS_INITIATED;

			$hasInitiatedMembership = false;

			if ($userMembership->find(true))
			{
				$hasInitiatedMembership = true;
			}

			$userMembership->store_id = $orderDAO->store_id;
			$userMembership->membership_status = self::DFL_STATUS_ACTIVE;
			$userMembership->order_id = $orderDAO->id;
			$userMembership->enrollment_package_id = $package->id;
			//TODO: Need enrollment period, just assume 3 months for now
			$curTimeTS = time();

			if ($package->enrollment_type_id == 1)
			{
				// time based so calculate end date
				switch ($package->enrollment_period_type)
				{

					case 1: // YEAR
						// CES 6/12/10: subscriptions are now essentially perpetual so set their end date to a distant time
						$endTS = mktime(0, 0, 0, date("n", $curTimeTS), date("j", $curTimeTS), date("Y", $curTimeTS) + 10);
						//	$endTS = mktime(0, 0, 0, date("n", $curTimeTS), date("j", $curTimeTS), date("Y", $curTimeTS) + $package->enrollment_period_length);
						break;
					case 2: // Month
						$endTS = mktime(0, 0, 0, date("n", $curTimeTS) + $package->enrollment_period_length, date("j", $curTimeTS), date("Y", $curTimeTS));
						break;
					case 3: // Days
						$endTS = mktime(0, 0, 0, date("n", $curTimeTS), date("j", $curTimeTS) + $package->enrollment_period_length, date("Y", $curTimeTS));
						break;
					default:
						$endTS = mktime(0, 0, 0, date("n", $curTimeTS), date("j", $curTimeTS), date("Y", $curTimeTS) + 1);
						// config issue: notify and record one year subscription
						CLog::RecordIntense('Could not find enrollment period type in registerSubscription for order: ' . $orderDAO->id, 'ryan.snook@dreamdinners.com');
						break;
				}

				$userMembership->start_date = date('Y-m-d 00:00:00', $curTimeTS);
				$userMembership->end_date = date('Y-m-d 23:59:59', $endTS);
			}
			else if ($package->enrollment_type_id == 2)
			{
				// order based so increment count and record order
				$userMembership->order_count = 1;
				if ($package->order_purchase_limit <= $userMembership->order_count)
				{
					$userMembership->membership_status = self::DFL_STATUS_EXPIRED;
				}

				$epto = DAO_CFactory::create('enrollment_package_to_order');
				$epto->order_id = $orderDAO->id;
			}

			if ($hasInitiatedMembership)
			{
				$userMembership->update();
			}
			else if (!$userMembership->insert())
			{
				CLog::RecordIntense('Could not insert enrollment for order: ' . $orderDAO->id, 'ryan.snook@dreamdinners.com');

				return;
			}

			if ($epto)
			{
				$epto->user_program_membership_id = $userMembership->id;
				$epto->insert();
			}
		}
		else
		{
			// don't throw here, no need to lose the entire order because of an enrollment snafu so
			CLog::RecordIntense('Could not find package in registerSubscription for order: ' . $orderDAO->id, 'ryan.snook@dreamdinners.com');
		}
	}

	function handleDFLOrderPlaced($Order_ID, $programID)
	{

		// get Enrollment data
		$enrollment = DAO_CFactory::create('user_program_membership');
		$enrollment->user_id = $this->id;
		$enrollment->menu_program_type_id = $programID;
		$enrollment->membership_status = self::DFL_STATUS_ACTIVE;

		if (!$enrollment->find(true))
		{
			//somehow a DFL order was placed but the user has no enrollment
			//CLog::RecordNew('ERROR', 'DFL Order was placed for user without active enrollment');
			CLog::RecordIntense('DFL Order was placed for user without any enrollment2', 'ryan.snook@dreamdinners.com');
		}
		else
		{
			/*
			* The actual creation of the valid enrollment is now done by self::registerSubscription which is called when inserting the enrollemnt package product

			if ($enrollment->membership_status == self::DFL_STATUS_INITIATED)
			{
				$curState = clone($enrollment);
				$enrollment->membership_status = self::DFL_STATUS_ACTIVE;

				//TODO: Need enrollment period, just asuume 3 months for now
				$curTimeTS = time();
				$endTS = mktime(0, 0, 0, date("n", $curTimeTS) + 3, date("j", $curTimeTS), date("Y", $curTimeTS));

				$enrollment->start_date = date('Y-m-d 00:00:00', $curTimeTS);
				$enrollment->end_date = date('Y-m-d 23:59:59', $endTS);

				$enrollment->update($curState);

				CUserHistory::recordUserEvent($this->id, 'null', 'null', 203, 'null', 'null', $description = 'Enrolled ' . $this->firstname . " " . $this->lastname . " in DFL.");

			}
			else
			{
				// What to do here. Just another DFL order
			}
			*/

			// Now we only need to update the number of orders placed if this is an order count based enrollment

			// First of all if the order_id recorded in enrollment is the same order as the one we are handling then the order count is already uptodate
			if ($enrollment->order_id == $Order_ID)
			{
				return;
			}
			else
			{

				$package = DAO_CFactory::create('enrollment_package');
				$package->id = $enrollment->enrollment_package_id;
				if ($package->find(true))
				{
					if ($package->enrollment_type_id == 2)
					{

						// order based so increment count and record order
						$enrollment->order_count++;
						if ($package->order_purchase_limit <= $enrollment->orderCount)
						{
							$enrollment->membership_status = self::DFL_STATUS_EXPIRED;
						}

						$enrollment->update();

						$epto = DAO_CFactory::create('enrollment_package_to_order');
						$epto->order_id = $Order_ID;
						$epto->user_program_membership_id = $enrollment->id;
						$epto->insert();
					}
				}
			}
		}
	}

	static function isEnrollmentInitiatedForUser($user_id)
	{

		$enrollment = DAO_CFactory::create('user_program_membership');

		$enrollment->user_id = $user_id;
		$enrollment->membership_status = 0;

		if ($enrollment->find(true))
		{
			return $enrollment->enrollment_package_id;
		}

		return false;
	}

	static function isUserEnrolledInDFL($user_id, $programID)
	{

		return false;

		$retVal = false;
		$enrollment = DAO_CFactory::create('user_program_membership');

		$enrollment->query("select upm.*, ep.* from user_program_membership upm left join enrollment_package ep on ep.id = upm.enrollment_package_id where upm.user_id = $user_id and upm.is_deleted = 0 and (upm.membership_status = 0 or upm.membership_status = 1) and menu_program_type_id = $programID");

		while ($enrollment->fetch())
		{
			if ($enrollment->membership_status == self::DFL_STATUS_ACTIVE)
			{

				if ($enrollment->enrollment_type_id == 1)
				{
					// time span based
					$endDateTS = strtotime($enrollment->end_date);
					$startDateTS = strtotime($enrollment->start_date);

					if ($endDateTS > time() && $startDateTS <= time())
					{
						$retVal = true;
						break;
					}
				}
				else if ($enrollment->enrollment_type_id == 2)
				{
					// order count based
					if ($enrollment->order_count < $enrollment->order_purchase_limit)
					{
						$retVal = true;
						break;
					}
				}
			}
		}

		return $retVal;
	}

	function isEnrolledInDFL($programID)
	{
		return self::isUserEnrolledInDFL($this->id, $programID);
	}

	static $programNameMap = array(
		1 => "Standard",
		2 => "Diabetic Menu"
	);

	function getDFLStatusDescription()
	{

		$retVal = array();

		$counter = 0;

		$enrollment = DAO_CFactory::create('user_program_membership');
		$enrollment->query("select * from user_program_membership where user_id = {$this->id} and is_deleted = 0");

		while ($enrollment->fetch())
		{
			if ($enrollment->membership_status == self::DFL_STATUS_INITIATED)
			{
				//$retVal[$counter]['desc'] = "Customer has initiated enrollment for " . self::$programNameMap[$enrollment->menu_program_type_id];
				$retVal[$counter]['desc'] = "Customer has initiated enrollment in Dinners for Life";
				$retVal[$counter]['code'] = 1;
				break;
			}
			else if ($enrollment->membership_status == self::DFL_STATUS_ACTIVE)
			{
				$endDateTS = strtotime($enrollment->end_date);

				if ($endDateTS > time())
				{
					//$retVal[$counter]['desc'] = "Customer has enrolled for " . self::$programNameMap[$enrollment->menu_program_type_id] . ". Enrollment expires on " . date("M j, Y", $endDateTS);
					$retVal[$counter]['desc'] = "Customer has enrolled in Dinners for Life. Enrollment expires on " . date("M j, Y", $endDateTS);
					$retVal[$counter]['code'] = 2;
				}
				else
				{
					//$retVal[$counter]['desc'] = "Customer enrollment for " . self::$programNameMap[$enrollment->menu_program_type_id] . " has expired on " . date("M j, Y", $endDateTS);
					$retVal[$counter]['desc'] = "Customer enrollment in Dinners for Life has expired on " . date("M j, Y", $endDateTS);
					$retVal[$counter]['code'] = 3;
				}
				break;
			}
			$counter++;
		}

		if (empty($retVal))
		{
			$retVal[$counter]['desc'] = "Not enrolled in Dinners For Life";
			$retVal[$counter]['code'] = 0;
		}

		return $retVal;
	}

	function setLogin()
	{
		$this->_LoggedIn = true;

		//set first name, last name, and home store id in the cookie
		$User = self::getCurrentUser();
		if ($User->home_store_id)
		{
			CBrowserSession::setCurrentStore($User->home_store_id);
		}
	}

	function setHomeStore($store_id)
	{
		$DAO_user = DAO_CFactory::create('user', true);
		$DAO_user->id = $this->id;
		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->id = $store_id;
		$DAO_user->joinAddWhereAsOn($DAO_store, array(
			'joinType' => 'INNER',
			'useLinks' => false
		));

		if ($DAO_user->find(true))
		{
			$org_DAO_user = clone $DAO_user;

			// Requested reporting update, update Home Store even if distribution center
			// Leaving following if/else in case this is reverted and to still log the distribution_center_id.
			$DAO_user->home_store_id = $DAO_user->DAO_store->id;

			if ($DAO_user->DAO_store->isFranchise())
			{
				$DAO_user->home_store_id = $DAO_user->DAO_store->id;
			}
			else if ($DAO_user->DAO_store->isDistributionCenter())
			{
				$DAO_user->distribution_center_id = $DAO_user->DAO_store->id;
			}

			$DAO_user->update($org_DAO_user);
		}
	}

	/**
	 * Called by insert and update to validate the object before sending to the db.
	 *
	 * @throws exception
	 * @access public
	 */
	function validate()
	{

		if (isset($this->user_type))
		{
			switch ($this->user_type)
			{
				case self::CUSTOMER:
				case self::HOME_OFFICE_STAFF:
				case self::FRANCHISE_MANAGER:
				case self::FRANCHISE_STAFF:
				case self::GUEST_SERVER:
				case self::FRANCHISE_OWNER:
				case self::HOME_OFFICE_MANAGER:
				case self::SITE_ADMIN:
				case self::FRANCHISE_LEAD:
				case self::MANUFACTURER_STAFF:
				case self::EVENT_COORDINATOR:
				case self::OPS_SUPPORT:
				case self::OPS_LEAD:
				case self::DISHWASHER:
				case self::NEW_EMPLOYEE:
					break;
				default:
					throw new Exception ("invalid user type");
					break;
			}
		}

		return parent::validate();
	}

	static function userInPlatePointsTest()
	{
		$User = self::getCurrentUser();

		if ($User->hasEnrolledInPlatePoints())
		{
			return true;
		}

		if (CStore::storeInPlatePointsTest($User->home_store_id))
		{
			return true;
		}

		if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'checkout')
		{
			return true;
		}

		if (!self::isLoggedIn())
		{
			return false;
		}

		return false;
	}

	function isEnrolledInPlatePoints()
	{
		return CPointsUserHistory::userIsActiveInProgram($this);
	}

	function getUsersLTD_RoundupOrders()
	{
		if (!empty($this->ltd_roundup_orders))
		{
			return;
		}

		$Order = DAO_CFactory::create('orders');
		$Order->query("SELECT
			o.*
			FROM orders o
			INNER JOIN booking b ON b.order_id = o.id AND b.is_deleted = '0'
			WHERE b.status = 'ACTIVE'
			AND o.user_id = '" . $this->id . "'
			AND o.ltd_round_up_value
			AND o.is_deleted = 0");

		$this->ltd_roundup_orders['orders'] = array();
		$this->ltd_roundup_orders['info'] = array(
			'total' => 0,
			'num_meals' => 0
		);

		while ($Order->fetch())
		{
			$this->ltd_roundup_orders['info']['total'] += $Order->ltd_round_up_value;
			$this->ltd_roundup_orders['orders'][$Order->id] = clone($Order);
		}

		$this->ltd_roundup_orders['info']['num_meals'] = floor($this->ltd_roundup_orders['info']['total'] / .25); // $0.25 donated = 1 meal

		return $this->ltd_roundup_orders;
	}

	function hasEnrolledInPlatePoints()
	{
		if ($this->dream_rewards_version == 3)
		{
			return true;
		}

		return false;
	}

	function getRSVPHistory()
	{
		if (isset($this->id))
		{
			$retVal = array();
			$historyObj = new DAO();
			$historyObj->query("select s.id, s.session_start, sr.timestamp_created from session_rsvp sr
					join session s on sr.session_id = s.id
					where sr.user_id = {$this->id} and sr.is_deleted = 0");

			while ($historyObj->fetch())
			{
				$retVal[$historyObj->id] = array(
					"session_id" => $historyObj->id,
					'session_start' => $historyObj->session_start,
					'rsvp_time' => $historyObj->timestamp_created
				);
			}

			return $retVal;
		}

		return false;
	}

	// returns 'active', 'in_DR2', or 'inactive'
	function getPlatePointsStatus()
	{
		if ($this->dream_rewards_version < 3)
		{
			if ($this->dream_reward_status == 1 || $this->dream_reward_status == 3)
			{
				return 'in_DR2';
			}
			else
			{
				return 'inactive';
			}
		}
		else if ($this->dream_rewards_version == 3)
		{
			if ($this->dream_reward_status == 1 || $this->dream_reward_status == 3)
			{
				return 'active';
			}
			else
			{
				return 'inactive';
			}
		}

		return 'inactive';
	}

	// returns 'active', 'in_DR2', or 'inactive'
	function isUserDeactivatedDRGuest()
	{
		if ($this->dream_rewards_version < 3 && $this->dream_reward_status == 2)
		{
			return true;
		}

		return false;
	}

	function hasPlacedFirstStandardCustomerSiteOrder()
	{
		$test = new DAO();
		$test->query("select o.id from booking b 
				join orders o on o.id = b.order_id and o.order_type = 'WEB' and o.type_of_order = 'STANDARD'
				where b.user_id = {$this->id} and b.status = 'ACTIVE' and b.is_deleted = 0
				limit 1");
		if ($test->N > 0)
		{
			return true;
		}

		return false;
	}

	function get_Booking_Last()
	{
		$DAO_booking = DAO_CFactory::create('booking', true);
		$DAO_booking->user_id = $this->id;
		$DAO_booking->status = CBooking::ACTIVE;
		$DAO_orders = DAO_CFactory::create('orders', true);
		$DAO_booking->joinAddWhereAsOn($DAO_orders);
		$DAO_session = DAO_CFactory::create('session', true);
		$DAO_session->whereAdd("session.session_start < NOW()");
		$DAO_booking->joinAddWhereAsOn($DAO_session);
		$DAO_booking->orderBy("session_start");
		$DAO_booking->limit(1);
		if ($DAO_booking->find(true))
		{
			return $DAO_booking;
		}

		return null;
	}

	function get_Booking_Next()
	{
		$DAO_booking = DAO_CFactory::create('booking', true);
		$DAO_booking->user_id = $this->id;
		$DAO_booking->status = CBooking::ACTIVE;
		$DAO_orders = DAO_CFactory::create('orders', true);
		$DAO_booking->joinAddWhereAsOn($DAO_orders);
		$DAO_session = DAO_CFactory::create('session', true);
		$DAO_session->whereAdd("session.session_start > NOW()");
		$DAO_booking->joinAddWhereAsOn($DAO_session);
		$DAO_booking->orderBy("session_start");
		$DAO_booking->limit(1);
		if ($DAO_booking->find(true))
		{
			return $DAO_booking;
		}

		return null;
	}

	function get_JSON_UserDataValue($key)
	{
		if (!empty($this->json_user_data))
		{
			$userData = json_decode($this->json_user_data);

			if (property_exists($userData, $key))
			{
				return $userData->{$key};
			}
		}

		return null;
	}

	function get_JSON_UserPreferenceValue($key)
	{
		if (!empty($this->json_user_preferences))
		{
			$userPref = json_decode($this->json_user_preferences);

			if (property_exists($userPref, $key))
			{
				return $userPref->{$key};
			}
		}

		return null;
	}

	function getPreferenceValue($key)
	{
		$perferenceArray = $this->getPreferenceArray();

		if (array_key_exists($key, $perferenceArray))
		{
			return $perferenceArray[$key]['value'];
		}

		return null;
	}

	function getPreferenceArray()
	{
		if (!empty($this->preferences))
		{
			return $this->preferences;
		}

		$DAO_user_preferences = DAO_CFactory::create('user_preferences', true);
		$DAO_user_preferences->user_id = $this->id;

		if ($DAO_user_preferences->find())
		{
			while ($DAO_user_preferences->fetch())
			{
				$this->preferences[$DAO_user_preferences->pkey] = array(
					'value' => $DAO_user_preferences->pvalue,
					'timestamp_updated' => $DAO_user_preferences->timestamp_updated,
					'timestamp_created' => $DAO_user_preferences->timestamp_created,
					'created_by' => $DAO_user_preferences->created_by,
					'updated_by' => $DAO_user_preferences->updated_by
				);
			}
		}

		return $this->preferences;
	}

	function getMembershipsArray($getCurrentMembership = false, $getPastMemberships = false, $getFutureMembership = false, $setCurrent = false)
	{
		$currentMenuID = CMenu::getCurrentMenuId();
		$retrieveList = array();
		$lastCurrentMembershipMonth = array();
		$this->membershipsArray = array();

		if ($getPastMemberships)
		{
			$memberShipDAO = new DAO();
			$memberShipDAO->query("SELECT 
					poi.id as mid
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status <> 'MEMBERSHIP_STATUS_CURRENT'
					join product_membership pm on poi.product_id = pm.product_id
					join store st on st.id = po.store_id
					where po.user_id = {$this->id} and po.is_deleted = 0 order by poi.product_membership_initial_menu");

			while ($memberShipDAO->fetch())
			{
				$retrieveList[$memberShipDAO->mid] = "PAST";
			}
		}

		if ($getCurrentMembership)
		{
			$memberShipDAO = new DAO();
			$memberShipDAO->query("SELECT 
					poi.id as mid
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
					join product_membership pm on poi.product_id = pm.product_id
					join store st on st.id = po.store_id
					where user_id = {$this->id} and po.is_deleted = 0  
					and $currentMenuID >= poi.product_membership_initial_menu
					and $currentMenuID <= poi.product_membership_initial_menu + pm.term_months - 1
					order by poi.product_membership_initial_menu limit 1");

			if ($memberShipDAO->fetch())
			{
				$retrieveList[$memberShipDAO->mid] = 'CURRENT';
			}
		}

		if ($getFutureMembership)
		{
			$memberShipDAO = new DAO();
			$memberShipDAO->query("SELECT 
					poi.id as mid
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
						and poi.product_membership_initial_menu > $currentMenuID
					join product_membership pm on poi.product_id = pm.product_id
					join store st on st.id = po.store_id
					where po.user_id = {$this->id} and po.is_deleted = 0 order by poi.product_membership_initial_menu");

			while ($memberShipDAO->fetch())
			{
				$retrieveList[$memberShipDAO->mid] = 'FUTURE';
			}
		}

		krsort($retrieveList);

		if (!empty($retrieveList))
		{
			foreach ($retrieveList as $thisMembershipID => $type)
			{
				$doSetCurrent = false;
				if ($setCurrent && $type == 'CURRENT' || ($type == 'FUTURE' && count($retrieveList) == 1))
				{
					$doSetCurrent = true;
				}

				$membershipData = $this->getMembershipStatus(false, true, $thisMembershipID, $type, $doSetCurrent, true);
				$this->membershipsArray[] = $membershipData;
			}
		}
		else // no order history
		{
			$this->getMembershipStatus();
		}

		if (empty($this->membershipData))
		{
			$this->membershipData = $this->membershipsArray[0];
		}
	}

	function getMembershipForMenu($menu_id)
	{

		$memberShipDAO = new DAO();
		$memberShipDAO->query("SELECT 
					poi.id as mid,
       				poi.product_membership_initial_menu,
       				poi.product_membership_hard_skip_menu,
      				pm.term_months,
       				pm.number_skips_allowed
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and (poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT' || poi.product_membership_status = 'MEMBERSHIP_STATUS_COMPLETED')
					join product_membership pm on poi.product_id = pm.product_id
					join store st on st.id = po.store_id
					where user_id = {$this->id} and po.is_deleted = 0  and $menu_id >= poi.product_membership_initial_menu and $menu_id <= poi.product_membership_initial_menu + pm.term_months	- 1 + pm.number_skips_allowed				
					order by poi.product_membership_initial_menu desc limit 2");

		// Note: this will a row even for a seventh month but in order for the seventh month to be valid there must be 1 and only 1 skip (for term of 6 months and 1 skip allowed)
		while ($memberShipDAO->fetch())
		{
			$jsonArr = json_decode($memberShipDAO->product_membership_hard_skip_menu);
			$skipCount = 0;
			if (is_array($jsonArr))
			{
				$skipCount = count($jsonArr);
			}

			if ($skipCount <= $memberShipDAO->number_skips_allowed)
			{
				$finalMonth = $memberShipDAO->product_membership_initial_menu + $memberShipDAO->term_months - 1 + $skipCount;
			}

			if ($menu_id >= $memberShipDAO->product_membership_initial_menu && $menu_id <= $finalMonth)
			{
				return $memberShipDAO->mid;
			}
		}

		return false;
	}

	function isMenuValidForMembershipOrder($menu_id, &$discountRate, &$membership_id, $membershipIsCancelled = false)
	{
		$stateClause = " (poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT' OR poi.product_membership_status = 'MEMBERSHIP_STATUS_COMPLETED' ) ";

		if ($membershipIsCancelled)
		{
			$stateClause = "poi.product_membership_status = 'MEMBERSHIP_STATUS_TERMINATED' ";
		}

		$memberShipDAO = new DAO();
		$memberShipDAO->query("SELECT 
					poi.id as mid,
					pm.discount_var,
       				poi.product_membership_initial_menu,
       				poi.product_membership_hard_skip_menu,
      				pm.term_months,
       				pm.number_skips_allowed
				FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and $stateClause
						join product_membership pm on poi.product_id = pm.product_id
					join store st on st.id = po.store_id
					where user_id = {$this->id} and po.is_deleted = 0  and $menu_id >= poi.product_membership_initial_menu and $menu_id <= poi.product_membership_initial_menu + pm.term_months - 1 + pm.number_skips_allowed			
					order by poi.product_membership_initial_menu desc limit 2");

		while ($memberShipDAO->fetch())
		{

			$jsonArr = json_decode($memberShipDAO->product_membership_hard_skip_menu);
			$skipCount = 0;
			if (is_array($jsonArr))
			{
				$skipCount = count($jsonArr);
			}

			if ($skipCount <= $memberShipDAO->number_skips_allowed)
			{
				$finalMonth = $memberShipDAO->product_membership_initial_menu + $memberShipDAO->term_months - 1 + $skipCount;
			}

			if ($menu_id >= $memberShipDAO->product_membership_initial_menu && $menu_id <= $finalMonth)
			{
				$discountRate = $memberShipDAO->discount_var;
				$membership_id = $memberShipDAO->mid;

				return true;
			}
		}

		return false;
	}

	function hasCurrentMembership()
	{
		$testObj = new DAO();
		$testObj->query("select po.id from product_orders po
							join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = '" . CUser::MEMBERSHIP_STATUS_CURRENT . "' and poi.is_deleted = 0 
							where po.user_id = {$this->id} and po.is_deleted = 0");
		if ($testObj->N > 0)
		{
			return true;
		}

		return false;
	}

	function getMembershipStatus($focusOrder = false, $generateStatusDisplayStrings = true, $getSpecificMembership = false, $history_type = false, $setAsCurrent = true, $addPaymentData = false)
	{
		if (empty($this->id))
		{
			return null;
		}
		// status field values
		// current - has one or more memberships in good stead
		// lapsed - has only a lapsed membership
		// unenrolled

		$currentMenuID = CMenu::getCurrentMenuId();

		if (!$getSpecificMembership && !$focusOrder)
		{

			$memberShipDAO = new DAO();
			$memberShipDAO->query("SELECT 
					poi.id as mid
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
						join product_membership pm on poi.product_id = pm.product_id
					join store st on st.id = po.store_id
					where user_id = {$this->id} and po.is_deleted = 0  and $currentMenuID  >= poi.product_membership_initial_menu and $currentMenuID <= poi.product_membership_initial_menu + pm.term_months - 1					
					order by poi.product_membership_initial_menu limit 1");

			if ($memberShipDAO->fetch())
			{
				$getSpecificMembership = $memberShipDAO->mid;
				$history_type = 'CURRENT';
			}
		}

		if ($focusOrder && !$getSpecificMembership)
		{
			$MIDgetter = new DAO();
			$MIDgetter->query("select membership_id from orders where id = $focusOrder");
			$MIDgetter->fetch();
			$getSpecificMembership = $MIDgetter->membership_id;
		}

		$membershipDataArr = array(
			'history_type' => $history_type,
			'status' => self::MEMBERSHIP_STATUS_NOT_ENROLLED,
			'enrollment_date' => "n/a",
			'membership_id' => 0,
			'product_orders_id' => 0,
			'eligible_menus' => false,
			'term_months' => 6,
			'number_skips_allowed' => 1,
			'discount_type' => 'PERCENT',
			'discount_var' => 10,
			'enrollment_cancel_eligible' => false,
			// refund allowed
			'enrollment_termination_eligible' => false,
			// no refund
			'can_be_reinstated' => false,
			'enrollment_eligible' => true,
			'ejection_menu_id' => false,
			'months_satisfied' => 0,
			'total_revenue' => 0,
			'total_savings' => 0,
			'hard_skip_count' => 0,
			'hard_skip_months' => array(),
			'completion_month' => false,
			'focus_order_position' => 0,
			'total_orders' => 0,
			'remaining_skips_available' => 0,
			'hard_skip_menus' => false,
			'all_orders_count' => 0
		);

		if ($this->isUserPreferred())
		{
			$membershipDataArr['enrollment_eligible'] = false;
		}

		if ($membershipDataArr['enrollment_eligible'] && $this->isEnrolledInPlatePoints())
		{
			$membershipDataArr['enrollment_eligible'] = false;
		}
		// TODO: if she is ineligible do we need to continue. I guess she could have older membership data

		$memberShipDAO = DAO_CFactory::create('product_orders');

		// Default behavior just get the first current membership
		$specificMembershipQuery = "";
		if ($getSpecificMembership)
		{
			$specificMembershipQuery = " and poi.id = " . $getSpecificMembership;
		}

		$memberShipDAO->query("SELECT 
			po.id as product_orders_id, 
			po.user_id, 
			po.store_id, 
			po.timestamp_created,
			pm.term_months,
			pm.number_skips_allowed,
			pm.discount_type, 
			pm.discount_var, 
			poi.product_membership_initial_menu,
       		poi.product_membership_hard_skip_menu,
       		poi.ejection_menu_id,
			poi.id as mid,
       		poi.product_membership_status, 
			st.timezone_id
			FROM product_orders po
			join product_orders_items poi on poi.product_orders_id = po.id " . $specificMembershipQuery . "
			join product_membership pm on poi.product_id = pm.product_id
			join store st on st.id = po.store_id
			where po.user_id = {$this->id} and po.is_deleted = 0");

		if ($memberShipDAO->fetch())
		{
			$initialMenu = $memberShipDAO->product_membership_initial_menu;

			$membershipDataArr['product_orders_id'] = $memberShipDAO->product_orders_id;
			$membershipDataArr['enrollment_date'] = $memberShipDAO->timestamp_created;
			$membershipDataArr['membership_id'] = $memberShipDAO->mid;
			$membershipDataArr['term_months'] = $memberShipDAO->term_months;
			$membershipDataArr['discount_type'] = $memberShipDAO->discount_type;
			$membershipDataArr['discount_var'] = $memberShipDAO->discount_var;
			$membershipDataArr['ejection_menu_id'] = $memberShipDAO->ejection_menu_id;
			$membershipDataArr['number_skips_allowed'] = $memberShipDAO->number_skips_allowed;

			if ($addPaymentData)
			{
				$membershipDataArr['paymentData'] = array();

				$paymentGetter = DAO_CFactory::create('product_payment');
				$paymentGetter->product_orders_id = $memberShipDAO->product_orders_id;
				$paymentGetter->find();
				while ($paymentGetter->fetch())
				{
					$membershipDataArr['paymentData'][$paymentGetter->id] = DAO::getCompressedArrayFromDAO($paymentGetter);

					switch ($paymentGetter->payment_type)
					{
						case 'CASH':
							$membershipDataArr['paymentData'][$paymentGetter->id]['display_strings'] = array(
								'payment_method' => 'Cash'
							);
							break;
						case 'CHECK':
							$membershipDataArr['paymentData'][$paymentGetter->id]['display_strings'] = array(
								'payment_method' => 'Check' . (!empty($paymentGetter->payment_number) ? ' #' . $paymentGetter->payment_number : '')
							);
							break;
						case 'CC':
							$membershipDataArr['paymentData'][$paymentGetter->id]['display_strings'] = array(
								'payment_method' => $paymentGetter->credit_card_type . ' ' . $paymentGetter->card_number
							);
							break;
					}
				}
			}

			$hardSkippedMenuArr = json_decode($memberShipDAO->product_membership_hard_skip_menu);
			if (!is_array($hardSkippedMenuArr))
			{
				$hardSkippedMenuArr = array();
			}

			$membershipDataArr['hard_skip_menus'] = $hardSkippedMenuArr;

			$ordersArr = array();
			$ordersObj = new DAO();
			$ordersObj->query("select o.id, s.session_start, s.menu_id, o.type_of_order, s.session_type, s.session_type_subtype, o.subtotal_all_items, o.servings_total_count, o.membership_id, o.membership_discount from booking b 
									join session s on s.id = b.session_id and s.menu_id >= $initialMenu
									join orders o on o.id = b.order_id
									where b.user_id = {$this->id} and b.status = 'ACTIVE' and b.is_deleted = 0");

			$membershipDataArr['all_orders_count'] = $ordersObj->N;

			while ($ordersObj->fetch())
			{
				if (!isset($ordersArr[$ordersObj->menu_id]))
				{
					$ordersArr[$ordersObj->menu_id] = array();
				}

				$ordersArr[$ordersObj->menu_id][$ordersObj->id] = DAO::getCompressedArrayFromDAO($ordersObj, true, true);
			}

			$currentMenu = $initialMenu;
			$membershipDataArr['eligible_menus'] = array();
			$lastAnchorMonth = false;

			for ($i = 0; $i < $memberShipDAO->term_months; $i++)
			{
				$membershipDataArr['eligible_menus'][$currentMenu] = array(
					'menu_info' => false,
					'orders' => array(),
					'skipped' => false,
					'valid_order' => false,
					'is_hard_skip' => false,
					'hard_skip_date' => false,
					'number_qualifying_orders' => 0
				);

				$menuData = DAO_CFactory::create('menu');
				$menuData->query("select * from menu where id = $currentMenu");

				if ($menuData->fetch())
				{
					$dateParts = explode("-", $menuData->menu_start);
					$lastAnchorMonth = $menuData->menu_start;

					$membershipDataArr['eligible_menus'][$currentMenu]['menu_info'] = clone $menuData;

					$membershipDataArr['eligible_menus'][$currentMenu]['hard_skip_date'] = mktime(0, 0, 0, $dateParts[1] + 1, 7, $dateParts[0]);

					if (!empty($ordersArr[$currentMenu]))
					{
						$membershipDataArr['eligible_menus'][$currentMenu]['orders'] = $ordersArr[$currentMenu];
					}

					$membershipDataArr['eligible_menus'][$currentMenu]['skippable'] = true;
				}
				else
				{
					// must infer some menu information
					$dateParts = explode("-", $lastAnchorMonth);
					$thisMonthTime = mktime(0, 0, 0, $dateParts[1] + 1, 1, $dateParts[0]);

					$membershipDataArr['eligible_menus'][$currentMenu]['menu_info'] = clone $menuData;
					$membershipDataArr['eligible_menus'][$currentMenu]['menu_info']->menu_name = date("F Y", $thisMonthTime);
					$membershipDataArr['eligible_menus'][$currentMenu]['menu_info']->menu_start = date("Y-m-01", $thisMonthTime);

					$membershipDataArr['eligible_menus'][$currentMenu]['skippable'] = false;

					$lastAnchorMonth = $membershipDataArr['eligible_menus'][$currentMenu]['menu_info']->menu_start;
				}

				$currentMenu++;
			}

			// extend eligible months if legal hard skip exists
			if (count($hardSkippedMenuArr) <= $membershipDataArr['number_skips_allowed'] && count($hardSkippedMenuArr) > 0)
			{
				for ($x = 0; $x < count($hardSkippedMenuArr); $x++)
				{
					$membershipDataArr['eligible_menus'][$currentMenu] = array(
						'menu_info' => false,
						'orders' => array(),
						'skipped' => false,
						'valid_order' => false,
						'is_hard_skip' => false,
						'hard_skip_date' => false,
						'number_qualifying_orders' => 0,
						'skippable' => false
					);

					$menuData = DAO_CFactory::create('menu');
					$menuData->query("select * from menu where id = $currentMenu");

					if ($menuData->fetch())
					{
						$dateParts = explode("-", $menuData->menu_start);
						$lastAnchorMonth = $menuData->menu_start;
						$membershipDataArr['eligible_menus'][$currentMenu]['menu_info'] = clone $menuData;
						if (!empty($ordersArr[$currentMenu]))
						{
							$membershipDataArr['eligible_menus'][$currentMenu]['orders'] = $ordersArr[$currentMenu];
						}
						$membershipDataArr['eligible_menus'][$currentMenu]['skippable'] = true;
					}
					else
					{
						// must infer some menu information
						$dateParts = explode("-", $lastAnchorMonth);
						$thisMonthTime = mktime(0, 0, 0, $dateParts[1] + 1, 1, $dateParts[0]);

						$membershipDataArr['eligible_menus'][$currentMenu]['menu_info'] = clone $menuData;
						$membershipDataArr['eligible_menus'][$currentMenu]['menu_info']->menu_name = date("F Y", $thisMonthTime);
						$membershipDataArr['eligible_menus'][$currentMenu]['menu_info']->menu_start = date("Y-m-01", $thisMonthTime);

						$membershipDataArr['eligible_menus'][$currentMenu]['skippable'] = false;

						$lastAnchorMonth = $membershipDataArr['eligible_menus'][$currentMenu]['menu_info']->menu_start;
					}
				}
			}

			$firstSessionTime = strtotime('2028-12-31 00:00:00'); // wait when is the end of linux epoch?

			foreach ($membershipDataArr['eligible_menus'] as $menu_id => &$thisMenu)
			{

				if (!empty($hardSkippedMenuArr) && in_array($menu_id, $hardSkippedMenuArr))
				{
					$thisMenu['skipped'] = true;
					$thisMenu['is_hard_skip'] = true;
				}

				if (empty($thisMenu['orders']))
				{
					$thisMenu['skipped'] = true;

					//NOTE: this code is obsolete - hard skips should only come from store action
					// Also this is currently not needed since with 1 skip available the guest would have been ejected already
					/*
					if ($thisMenu['hard_skip_date'] !== false && time() > $thisMenu['hard_skip_date'])
					{
						$thisMenu['is_hard_skip'] = true;
					}
					*/
				}
				else
				{
					$numQualifyingOrders = 0;

					foreach ($thisMenu['orders'] as $thisOrder)
					{
						if ($thisOrder['servings_total_count'] >= 36 && $thisOrder['type_of_order'] == 'STANDARD')
						{

							if ($focusOrder && $focusOrder = $thisOrder['id'])
							{
								$membershipDataArr['focus_order_position'] = $membershipDataArr['months_satisfied'] + 1;
							}

							if (!$thisMenu['valid_order'])
							{
								if (strtotime($thisOrder['session_start']) < $firstSessionTime)
								{
									$firstSessionTime = strtotime($thisOrder['session_start']);
								}

								$thisMenu['skipped'] = false;
								$thisMenu['valid_order'] = $thisOrder['id'];
								$numQualifyingOrders++;
								if (empty($thisOrder['membership_id']))
								{
									$thisMenu['has_nondiscounted_order'] = true;
								}
							}
						}
						$membershipDataArr['total_revenue'] += $thisOrder['subtotal_all_items'];
						$membershipDataArr['total_savings'] += $thisOrder['membership_discount'];
					}

					$thisMenu['number_qualifying_orders'] = $numQualifyingOrders;
					if ($numQualifyingOrders > 0)
					{
						$membershipDataArr['months_satisfied']++;
					}

					$membershipDataArr['total_orders'] += $numQualifyingOrders;
				}
			}

			// one more time to count skips
			$numHardSkips = 0;
			$numSoftSkips = 0;
			foreach ($membershipDataArr['eligible_menus'] as &$thisMenu)
			{

				if ($memberShipDAO->product_membership_status != self::MEMBERSHIP_STATUS_CURRENT)
				{
					$thisMenu['skippable'] = false;
				}
				else if ($thisMenu['skippable'] && $thisMenu['number_qualifying_orders'] > 0)
				{
					$thisMenu['skippable'] = false;
				}

				if ($thisMenu['skipped'])
				{
					$numSoftSkips++;
					if ($thisMenu['is_hard_skip'])
					{
						$numHardSkips++;
					}
				}
			}

			$membershipDataArr['hard_skip_count'] = $numHardSkips;

			$legalHardSkips = $numHardSkips;
			if ($legalHardSkips > $membershipDataArr['number_skips_allowed'])
			{
				$legalHardSkips = $membershipDataArr['number_skips_allowed'];
			}

			$membershipDataArr['months_satisfied'] += $legalHardSkips;

			$completionDuration = $membershipDataArr['term_months'];
			$finalMenuID = $initialMenu + $completionDuration - 1;
			$membershipDataArr['completion_month'] = $membershipDataArr['eligible_menus'][$finalMenuID]['menu_info']->menu_name;

			$membershipDataArr['status'] = $memberShipDAO->product_membership_status;

			if ($numHardSkips > $membershipDataArr['number_skips_allowed'])
			{
				if ($membershipDataArr['status'] != self::MEMBERSHIP_STATUS_TERMINATED)
				{
					CLog::RecordNew(CLog::DEBUG, "Membership Should be terminated." . print_r($membershipDataArr, true), "", "", true);
				}
			}

			$membershipDataArr['remaining_skips_available'] = max($membershipDataArr['number_skips_allowed'] - $numHardSkips, 0);

			$now = CTimezones::getAdjustedServerTime($memberShipDAO->timezone_id);
			if ($membershipDataArr['status'] == self::MEMBERSHIP_STATUS_CURRENT && $now > $firstSessionTime)
			{
				$membershipDataArr['enrollment_cancel_eligible'] = false;
				$membershipDataArr['enrollment_termination_eligible'] = true;
			}
			else if ($membershipDataArr['status'] == self::MEMBERSHIP_STATUS_CURRENT)
			{
				$membershipDataArr['enrollment_cancel_eligible'] = true;
			}

			if ($membershipDataArr['enrollment_eligible'] && $membershipDataArr['status'] == self::MEMBERSHIP_STATUS_CURRENT)
			{
				if ($currentMenuID < $finalMenuID - 1)
				{
					$membershipDataArr['enrollment_eligible'] = false;
				}
				else if (!CMenu::menuExists($finalMenuID + 1))
				{
					$membershipDataArr['enrollment_eligible'] = false;
				}
			}

			if ($membershipDataArr['enrollment_eligible'])
			{
				// one more test for a future enrollment
				$memberShipTest = new DAO();
				$memberShipTest->query("SELECT 
					poi.id as mid
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
						join product_membership pm on poi.product_id = pm.product_id and poi.product_membership_initial_menu > $finalMenuID
					where user_id = {$this->id} and po.is_deleted = 0");

				if ($memberShipTest->N > 0)
				{
					$membershipDataArr['enrolled'] = false;
				}
			}

			if ($membershipDataArr['status'] == self::MEMBERSHIP_STATUS_TERMINATED)
			{
				// figure out if the membership can be reinstated by a hard skip
				if (empty($hardSkippedMenuArr))
				{
					// if the status is cancelled but no hard skips have been recorded then we can allow the previous month
					// to be hard skipped.
					if ($memberShipDAO->ejection_menu_id + 1 == $currentMenuID) //TODO: and ejection_menu_id is in time span of membership
					{
						$membershipDataArr['can_be_reinstated'] = true;
					}

					if ($memberShipDAO->ejection_menu_id + 2 == $currentMenuID) //TODO: and ejection_menu_id is in time span of membership and prior to locked date of current menuj
					{
						$membershipDataArr['can_be_reinstated'] = true;
					}
				}
			}
		}

		if ($generateStatusDisplayStrings)
		{
			$membershipDataArr['display_strings'] = array();

			// TODO: need to pull the data based on the membership package of the order which may not be the current package
			if ($membershipDataArr['status'] == CUser::MEMBERSHIP_STATUS_CURRENT)
			{
				$membershipDataArr['display_strings']['status'] = self::getMealPrepPlusDisplayString(CUser::MEMBERSHIP_STATUS_CURRENT);
				$membershipDataArr['display_strings']['status_abbr'] = self::getMealPrepPlusDisplayStringAbbreviated(CUser::MEMBERSHIP_STATUS_CURRENT);

				$membershipDataArr['display_strings']['progress'] = $membershipDataArr['months_satisfied'] . " of " . $membershipDataArr['term_months'];
				if (!empty($membershipDataArr['focus_order_position']))
				{
					$membershipDataArr['display_strings']['order_position'] = $membershipDataArr['focus_order_position'] . " of " . $membershipDataArr['term_months'];
				}
				else
				{
					$membershipDataArr['display_strings']['order_position'] = "n/a";
				}
				$membershipDataArr['display_strings']['completion_month'] = $membershipDataArr['completion_month'];
				$membershipDataArr['display_strings']['total_savings'] = $membershipDataArr['total_savings'];
				//$membershipData['end_month'] = ($User->membershipData['hard_skip_count'] > 0 ? );

			}
			else
			{
				$membershipDataArr['display_strings']['status'] = self::getMealPrepPlusDisplayString($membershipDataArr['status']);
				$membershipDataArr['display_strings']['status_abbr'] = self::getMealPrepPlusDisplayStringAbbreviated($membershipDataArr['status']);
			}
		}

		$membershipDataArr['enrolled'] = ($membershipDataArr['status'] == self::MEMBERSHIP_STATUS_CURRENT);
		// one final check for any open enrollment
		if ($membershipDataArr['status'] != self::MEMBERSHIP_STATUS_CURRENT)
		{
			$memberShipTest = new DAO();
			$memberShipTest->query("SELECT 
					poi.id as mid
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
						join product_membership pm on poi.product_id = pm.product_id
					where user_id = {$this->id} and po.is_deleted = 0");

			if ($memberShipTest->N > 0)
			{
				$membershipDataArr['enrolled'] = true;
			}
		}

		if ($setAsCurrent)
		{
			$this->membershipData = $membershipDataArr;
		}

		return $membershipDataArr;
	}

	function membershipStatusCurrent()
	{
		$this->getMembershipStatus();

		if ($this->membershipData['status'] == CUser::MEMBERSHIP_STATUS_CURRENT)
		{
			return true;
		}

		return false;
	}

	static function getMealPrepPlusDisplayString($inStr)
	{
		switch ($inStr)
		{
			case CUser::MEMBERSHIP_STATUS_CURRENT:
				return "Membership is current";
			case CUser::MEMBERSHIP_STATUS_TERMINATED:
				return "Membership was canceled";
			case CUser::MEMBERSHIP_STATUS_NOT_ENROLLED:
				return "Not a member";
			case CUser::MEMBERSHIP_STATUS_COMPLETED:
				return "Membership is ended";
			case CUser::MEMBERSHIP_STATUS_REFUNDED:
				return "Membership was canceled and fee refunded";
			default:
				return $inStr;
		}
	}

	static function getMealPrepPlusDisplayStringAbbreviated($inStr)
	{
		switch ($inStr)
		{
			case CUser::MEMBERSHIP_STATUS_CURRENT:
				return "Status: Active";
			case CUser::MEMBERSHIP_STATUS_TERMINATED:
				return "Status: Canceled";
			case CUser::MEMBERSHIP_STATUS_NOT_ENROLLED:
				return "Status: Not a member";
			case CUser::MEMBERSHIP_STATUS_COMPLETED:
				return "Status: Ended";
			case CUser::MEMBERSHIP_STATUS_REFUNDED:
				return "Status: Refunded";
			default:
				return $inStr;
		}
	}

	function getPlatePointsSummary($orderObj = false, $getStatsIfOnHold = true)
	{
		$this->platePointsData = array(
			'status' => $this->getPlatePointsStatus(),
			'userIsOnHold' => $this->dream_reward_status == 5,
			'isDeactivatedDRUser' => $this->isUserDeactivatedDRGuest(),
			'lifetime_points' => false,
			'pending_points' => false,
			'points_until_next_credit' => false,
			'points_until_next_level' => false,
			'current_level' => 'not_enrolled',
			'social_connect_awarded' => false,
			'points_this_order' => false,
			'available_credit' => false,
			'user_is_preferred' => false,
			'due_reward_for_current_level' => false,
			'gift_display_str' => false,
			'next_expiring_credit_amount' => 0,
			'next_expiring_credit_date' => false
		);

		$this->platePointsData['user_is_preferred'] = $this->isUserPreferred();
		$streakData = false;

		if ($this->platePointsData['status'] == 'active' || ($this->platePointsData['userIsOnHold'] && $getStatsIfOnHold))
		{
			$this->platePointsData['lifetime_points'] = floor(CPointsUserHistory::getCurrentPointsLevel($this->id));
			$this->platePointsData['pending_points'] = floor(CPointsUserHistory::getPendingPoints($this->id, $this->platePointsData['lifetime_points']));
			list($this->platePointsData['current_level'], $this->platePointsData['next_level']) = CPointsUserHistory::getLevelDetailsByPoints($this->platePointsData['lifetime_points']);
			$this->platePointsData['social_connect_awarded'] = CPointsUserHistory::userSocialConnectAwarded($this->id);
			$this->platePointsData['points_until_next_credit'] = CPointsUserHistory::getPointsUntilNextCredit($this->id);

			$this->platePointsData['points_until_next_level'] = $this->platePointsData['next_level']['req_points'] - $this->platePointsData['lifetime_points'];

			if ($this->platePointsData['current_level']['level'] == 'enrolled')
			{
				$orderID = false;
				if (!empty($orderObj->id))
				{
					$orderID = $orderObj->id;
				}

				// when a member (enrolled) the gifts are driven by order
				$streakData = CPointsUserHistory::getOrdersSequenceStatus($this->id, $orderID);

				if ($streakData['focusOrderInOriginalStreak'])
				{
					$this->platePointsData['habitStreakOrderNumberForThisOrder'] = $streakData['focusOrderStreakOrderNumber'];
					$this->platePointsData['habitStreakOrderCount'] = $streakData['InitialStreakOrderCount'];
				}

				if (!empty($orderObj->id))
				{
					$this->platePointsData['orderBasedGiftData'] = CPointsUserHistory::getOrderBasedGiftData($this->id, $orderObj->id, $streakData);
				}

				if ($orderObj)
				{
					$this->platePointsData['current_level']['rewards']['food_testing'] = $this->isEligibleForFoodTesting($orderObj->id);
				}

				$this->platePointsData['is_receiving_dinner_dollars'] = true;
			}
			else
			{
				$this->platePointsData['gift_display_str'] = CPointsUserHistory::getGiftDisplayString($this->platePointsData['current_level']['rewards']['gift_id']);
				$this->platePointsData['is_receiving_dinner_dollars'] = !$this->platePointsData['user_is_preferred'];
			}
		}

		if ($orderObj)
		{
			$this->platePointsData['points_this_order'] = CPointsUserHistory::getPointsForOrder($this->platePointsData['lifetime_points'], $orderObj);
		}

		$this->platePointsData['available_credit'] = CPointsCredits::getAvailableCreditForUser($this->id);
		list($this->platePointsData['next_expiring_credit_date'], $this->platePointsData['next_expiring_credit_amount']) = CPointsCredits::getNextExpiringCredit($this->id);
		$this->platePointsData['all_expiring_credits'] = CPointsCredits::getAllExpiringCredit($this->id);

		list($this->platePointsData['due_reward_for_current_level'], $this->platePointsData['due_reward_for_current_level_received'], $this->platePointsData['due_reward_for_current_level_received_notes']) = (($this->platePointsData['user_is_preferred']) ? false : CPointsUserHistory::userDuePhysicalRewardForLevel($this->platePointsData['current_level'], $this->id, $orderObj, $streakData));

		$this->platePointsData['transition_has_expired'] = false;
		if (!empty($this->home_store_id))
		{
			$this->platePointsData['transition_has_expired'] = CStore::hasPlatePointsTransitionPeriodExpired($this->home_store_id);
		}

		return $this->platePointsData;
	}

	function isEligibleForFoodTesting($orderID)
	{
		//Get the current session
		$sessionTimeDAO = new DAO();
		$sessionTimeDAO->query("select s.menu_id, o.menu_items_core_total_count from orders o
				join booking b on b.order_id = o.id and b.status = 'ACTIVE'
				join session s on s.id = b.session_id
				where o.id = $orderID and o.is_deleted = 0");

		$orderCount = 0;
		if ($sessionTimeDAO->fetch())
		{
			if ($sessionTimeDAO->menu_items_core_total_count > 0)
			{
				$orderCount++;
			}

			$qualifyingOrdersDAO = new DAO();
			$qualifyingOrdersDAO->query("select o.id from booking b
					join orders o on o.id = b.order_id and o.menu_items_core_total_count > 0
					join session s on s.id = b.session_id and s.menu_id <= {$sessionTimeDAO->menu_id} and s.menu_id >= {$sessionTimeDAO->menu_id} - 3
					where b.status = 'ACTIVE' and b.is_deleted = 0 and b.user_id = {$this->id}");

			if ($orderCount + $qualifyingOrdersDAO->N > 3)
			{
				return true;
			}
		}

		return false;
	}

	// limited preference information for public json arrays
	function getPublicPreferences()
	{
		$this->getUserPreferences();

		$public_preferences = array();

		foreach ($this->preferences as $key => $pref)
		{
			$public_preferences[$key] = array();

			$public_preferences[$key]['value'] = $pref['value'];
		}

		return $public_preferences;
	}

	static function getPreferenceDefault($preference)
	{
		return self::$preferenceDefaults[$preference];
	}

	function getUserPreferences()
	{
		if (!empty($this->preferences))
		{
			return;
		}

		$userPref = DAO_CFactory::create('user_preferences');
		$userPref->user_id = $this->id;
		$userPref->find();

		while ($userPref->fetch())
		{
			$decode_value = json_decode($userPref->pvalue);

			if ($decode_value !== null)
			{
				$pref_value = $decode_value;
			}
			else
			{
				$pref_value = $userPref->pvalue;
			}

			$this->preferences[$userPref->pkey] = array(
				'value' => $pref_value,
				'timestamp_updated' => $userPref->timestamp_updated,
				'timestamp_created' => $userPref->timestamp_created,
				'created_by' => $userPref->created_by,
				'updated_by' => $userPref->updated_by
			);
		}

		// get a list of what preferences aren't set in user_preferences table
		$not_in_user_prefs = array_diff_key(self::$preferenceDefaults, $this->preferences);

		// merge defaults with user preferences for a complete set
		$this->preferences = ($this->preferences + self::$preferenceDefaults);

		// update user_preferences table with any missing defaults
		foreach ($not_in_user_prefs as $pref => $value)
		{
			$this->setUserPreference($pref, $value);
		}

		// this works but is not recursive. Do not nest preferences is HAS_SEEN_ELEMENT
		$curValsArray = (array)$this->preferences[self::HAS_SEEN_ELEMENT]['value'];

		// HAS_SEEN_ELEMENT is a special case as it is an open ended array.  Check if any new values were added.
		$not_in_has_seen_element_pref = array_diff_key(self::$preferenceDefaults[self::HAS_SEEN_ELEMENT], $curValsArray);

		if (!empty($not_in_has_seen_element_pref))
		{
			if (!isset($this->preferences[self::HAS_SEEN_ELEMENT]))
			{
				CLog::RecordIntense("HAS_SEEN_ELEMENT not set in this preferences", "ryan.snook@dreamdinners.com");
			}

			foreach ($not_in_has_seen_element_pref as $key => $default)
			{
				$this->preferences[self::HAS_SEEN_ELEMENT]['value']->$key = $default;
			}

			$this->setUserPreference(self::HAS_SEEN_ELEMENT, (array)$this->preferences[self::HAS_SEEN_ELEMENT]['value']);
		}
	}

	function setUserPreference($key, $value, $create_only = false)
	{
		$this->getUserPreferences();

		$key = trim(strtoupper($key));

		require_once('includes/class.inputfilter_clean.php');
		$xssFilter = new InputFilter();
		$value = $xssFilter->process($value);

		if ($value === 'true')
		{
			$value = 1;
		}
		else if ($value === 'false')
		{
			$value = 0;
		}

		if (!constant('self::' . $key))
		{
			CLog::RecordNew(CLog::DEBUG, 'Constant not defined self::' . $key);
		}

		$userPref = DAO_CFactory::create('user_preferences');
		$userPref->user_id = $this->id;
		$userPref->pkey = $key;

		$json_encoded = false;
		if (is_array($value))
		{
			$json_encoded = true;
			$pref_value = json_encode($value);
		}
		else
		{
			$pref_value = $value;
		}

		if ($userPref->find(true))
		{
			if (!$create_only)
			{
				$userPref->pvalue = $pref_value;
				$userPref->update();
			}
		}
		else
		{
			$userPref->pvalue = $pref_value;
			$userPref->insert();
		}

		$this->preferences[$key] = array(
			'value' => $value,
			'timestamp_updated' => $userPref->timestamp_updated,
			'timestamp_created' => $userPref->timestamp_created,
			'created_by' => $userPref->created_by,
			'updated_by' => $userPref->updated_by
		);
	}

	function setMealCustomizationPreference($mealCustomUpdates, $unanswered_only = false)
	{
		$this->getUserPreferences();
		foreach ($mealCustomUpdates as $key => $value)
		{
			if (!empty($value))
			{
				if ($unanswered_only)
				{
					if ($this->preferences[$key]['value'] == CUser::UNANSWERED)
					{
						$this->setUserPreference($key, $value->value);
					}
				}
				else
				{
					$this->setUserPreference($key, $value->value);
				}
			}
		}
	}

	// Note: this data is used to calculate the ideal
	// number of meals to order - used by customer ordering logic
	function setMealAndFamilySize()
	{

		$settingRetriever = new DAO();
		$num_feeding_id = HOW_MANY_PEOPLE_FEEDING_FIELD_ID;
		$settingRetriever->query("select user_data_value, user_data_field_id from user_data where user_id = {$this->id} and user_data_field_id = $num_feeding_id and is_deleted = 0");
		if ($settingRetriever->fetch())
		{
			// found so update object and remove cookie
			// we always used the saved value over a cookie
			$this->number_feeding = $settingRetriever->user_data_value;
		}
		else if (!empty($_COOKIE['number_feeding']) && is_numeric($_COOKIE['number_feeding']))
		{
			// no database value and cookie exists so store and delete

			$userData25 = DAO_CFactory::create('user_data');
			$userData25->user_id = $this->id;
			$userData25->user_data_field_id = HOW_MANY_PEOPLE_FEEDING_FIELD_ID;
			$userData25->user_data_value = $_COOKIE['number_feeding'];
			$userData25->insert();
			$this->number_feeding = $_COOKIE['number_feeding'];
		}

		$settingRetriever2 = new DAO();
		$desired_meals_id = DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID;
		$settingRetriever2->query("select user_data_value, user_data_field_id from user_data where user_id = {$this->id} and user_data_field_id = $desired_meals_id and is_deleted = 0");
		if ($settingRetriever2->fetch())
		{
			// found so update object and remove cookie
			// we always used the saved value over a cookie
			$this->desired_homemade_meals_per_week = $settingRetriever2->user_data_value;
		}
		else if (!empty($_COOKIE['desired_homemade_meals_per_week']) && is_numeric($_COOKIE['desired_homemade_meals_per_week']))
		{
			$userData26 = DAO_CFactory::create('user_data');
			$userData26->user_id = $this->id;
			$userData26->user_data_field_id = DESIRED_HOMEMADE_MEALS_PER_WEEK_FIELD_ID;
			$userData26->user_data_value = $_COOKIE['desired_homemade_meals_per_week'];
			$userData26->insert();

			$this->desired_homemade_meals_per_week = $_COOKIE['desired_homemade_meals_per_week'];
		}

		CBrowserSession::setValueAndDuration("desired_homemade_meals_per_week", false);
		CBrowserSession::setValueAndDuration("number_feeding", false);

		if (empty($this->number_feeding))
		{
			$this->number_feeding = false;
		}

		if (empty($this->desired_homemade_meals_per_week))
		{
			$this->desired_homemade_meals_per_week = false;
		}
	}

	//MEAL/RECIPE Customization Preference Helpers
	function hasMealCustomizationPreferencesSet()
	{
		if (!empty($this->id))
		{
			$selected = $this->getSelectedMealCustomizationPreferences();

			if (count($selected) > 0)
			{
				return true;
			}
		}

		return false;
	}

	function needsToSetMealCustomizationPreferencesSet()
	{
		$neverSet = $this->getNeverSetMealCustomizationPreferences();
		//$allCustomizationPrefs = $this->getMealCustomizationPreferences();

		$countNeverSet = 0;
		//$countBasis = 0;

		foreach ($neverSet as $key => $data)
		{
			$countNeverSet++;
		}
		//		foreach ($allCustomizationPrefs as $key => $data) {
		//			$countBasis++;
		//		}

		if ($countNeverSet > 0)
		{
			return true;
		}

		return false;
	}

	function getOrderCustomizationPreferencesAsJson()
	{
		$this->getMealCustomizationPreferences();

		$obj = OrdersCustomization::initOrderCustomizationObjFromMealCustomizationObj($this->meal_customization_preferences);

		return json_encode($obj);
	}

	function getMealCustomizationPreferencesAsJson()
	{
		$this->getMealCustomizationPreferences();

		return json_encode($this->meal_customization_preferences);
	}

	function getMealCustomizationPreferences()
	{
		if (empty($this->id))
		{
			$this->meal_customization_preferences = null;

			return $this->meal_customization_preferences;
		}

		$this->getUserPreferences();

		if (!empty($this->meal_customization_preferences))
		{
			return $this->meal_customization_preferences;
		}
		$this->meal_customization_preferences = OrdersCustomization::createOrderCustomizationObj($this->preferences)->meal;

		return $this->meal_customization_preferences;
	}

	function getSelectedMealCustomizationPreferences()
	{
		$prefs = $this->getMealCustomizationPreferences();
		$customization_preferences = array();

		foreach ($prefs as $key => $pref)
		{
			if ($pref->type == 'CHECKBOX' && ($pref->value == CUser::OPTED_IN || $pref->value == 'on'))
			{
				$customization_preferences[$key] = $pref;
			}

			if ($pref->type == 'INPUT' && trim($pref->value) != '')
			{
				$customization_preferences[$key] = $pref;
			}
		}

		return $customization_preferences;
	}

	function getNeverSetMealCustomizationPreferences()
	{
		$prefs = $this->getMealCustomizationPreferences();
		$not_set_customization_preferences = array();

		foreach ($prefs as $key => $pref)
		{
			if ($pref->type == 'CHECKBOX' && $pref->value == CUser::UNANSWERED)
			{
				$not_set_customization_preferences[$key] = $pref;
			}

			if ($pref->type == 'INPUT' && trim($pref->value) == '')
			{
				$not_set_customization_preferences[$key] = $pref;
			}
		}

		return $not_set_customization_preferences;
	}

	function getSelectedMealCustomizationPreferencesAsString($separator = ',')
	{
		$prefs = $this->getSelectedMealCustomizationPreferences();
		$customization_preferences = '';

		foreach ($prefs as $key => $pref)
		{
			if ($pref->type == 'CHECKBOX')
			{
				$customization_preferences .= $pref->short . $separator;
			}

			if ($pref->type == 'INPUT')
			{
				$customization_preferences .= $pref->short . $separator;
			}
		}

		return rtrim($customization_preferences, ',');
	}

	/* ------------------------------------------------------------
	*
	* Class: CUser
	*
	* Function: Authenticate
	*
	* Params:	IN	password - STRING
	*
	* @return true for success
	*
	* This function can be passed either the user ID or the username to check
	* login status. It is also passed the Password which it will
	* one way encrypt and attempt to validate the user. On success,
	* calls Load() and sets and returns ::->_LoggedIn (TRUE/FALSE).
	*
	* -------------------------------------------------------------- */

	function Authenticate($username, $password, $id = false, $oauth = false, $defeatLockoutCheck = false, $suppressUIfunction = false)
	{
		$username = trim($username);
		$password = trim($password);

		// use this only after full security deployment
		$username = CGPC::do_clean($username, TYPE_STR);

		// but also do this until full security deployment
		//	$_db = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
		//	mysqli_select_db($_db, DB_DATABASE);
		//	$username = CGPC::safeSQL($username, $_db);

		if ($this->_AuthenticationAttempt)
		{
			throw new Exception('Cannot attempt more than one authentication.');
		}

		$this->_AuthenticationAttempt = true;
		$this->_LoggedIn = false;

		if ($this->_LoggedIn)
		{
			return $this->_LoggedIn;
		}

		if ($oauth)
		{
			$this->_LoggedIn = true;

			CLog::RecordNew(CLog::LOGIN, 'OAuth Login successful, User ID: ' . $this->id . ', Username: ' . $username . ', IP Addr: ' . $_SERVER['REMOTE_ADDR'] . ', Agent: ' . addslashes($_SERVER['HTTP_USER_AGENT']));
			CUserHistory::recordUserEvent($this->id, 'null', 'null', 100, 'null', 'null', 'OAuth Login successful as: ' . $this->primary_email . ', Agent: ' . addslashes($_SERVER['HTTP_USER_AGENT']));
		}
		else
		{
			if (empty($username))
			{
				$this->_LoggedIn = false;

				return $this->_LoggedIn;
			}

			if (empty($password))
			{
				$this->_LoggedIn = false;

				return $this->_LoggedIn;
			}

			if (CPasswordPolicy::isLockedOut($username))
			{
				if (!$defeatLockoutCheck)
				{
					$templateTest = CApp::instance()->template();

					if (!empty($templateTest))
					{
						CApp::instance()->template()->setErrorMsg('<span style="font-weight:bold; color:red;">It appears you have attempted to login more than the allowed number of attempts. Please wait for ' . CPasswordPolicy::LOCKOUT_DURATION . " minutes and try again.</span>");
					}

					return false;
				}
				else
				{
					CPasswordPolicy::clearLockOut($username);
				}
			}

			// to support bcrypt we need can longer load the user object and test the password in one query
			// so load the objects first regardless of password

			$loginObj = DAO_CFactory::create('user_login');
			$loginObj->whereAdd();

			if ($id)
			{
				$loginObj->id = $id;
			}

			$loginObj->ul_username = $username;

			// [CES 6/16/06] PENDING is no longer used
			//	if ( !$this->primary_email )
			$loginObj->ul_verified = 'YES'; //if no email address, then we don't need to verify it
			//	else
			//	$loginObj->ul_verified = 'PENDING';

			$this->joinAdd($loginObj);
			$this->selectAdd();
			$this->selectAdd('user.*, user_login.uses_bcrypt, user_login.ul_password, user_login.ul_password2, user_login.last_password_update');

			$rslt = $this->find(true);

			if (!$rslt)
			{
				$this->_LoggedIn = false;
			}
			else
			{
				if ($this->uses_bcrypt)
				{
					$this->_LoggedIn = CPasswordPolicy::verifyPassword($password, $this->ul_password2);
				}
				else
				{
					//$this->_LoggedIn = md5($password) == $this->ul_password;
					// As of 3/10/2016 if bcrypt is not set we know that the old md5 has was deleted. We need to ask the user to update their passwword

					$this->_LoggedIn = false;

					$result = self::resetPwd($username, false, true);

					$home_store_id = $this->home_store_id;
					if (empty($home_store_id))
					{
						$home_store_id = false;
					}

					CBrowserSession::setValueAndDuration("ul_has_home_store_id", $home_store_id, 300);

					if ($result == 'password_reset_mail_sent')
					{
						CBrowserSession::setValueAndDuration("ul_has_valid_email", true, 300);
						CBrowserSession::setValueAndDuration("ul_email", $username, 300);
						CApp::bounce("/new-password");
						exit;
					}
					else if ($tpl = CApp::instance()->template())
					{
						CBrowserSession::setValueAndDuration("ul_has_valid_email", false, 300);
						CApp::bounce("/new-password");
						exit;
					}
				}
			}

			if (!$this->_LoggedIn)
			{
				CLog::RecordNew(CLog::LOGIN, 'Login failed, User ID: ' . $this->id . ', Username: ' . $username . ', IP Addr: ' . $_SERVER['REMOTE_ADDR'] . ', Agent: ' . addslashes($_SERVER['HTTP_USER_AGENT']));
				CUserHistory::recordUserEvent($this->id, 'null', 'null', 100, 'null', 'null', 'Login failed as: ' . $username . ', Agent: ' . addslashes($_SERVER['HTTP_USER_AGENT']));

				$this->user_type = self::GUEST;
				unset($this->id);

				CPasswordPolicy::handlePasswordFailure($username);
				// user messaging is handled by caller
			}
			else
			{

				if ($this->user_type != self::CUSTOMER)
				{
					// enforce 90 day password cycle
					$days_since_last_update = CPasswordPolicy::getRemainingPasswordLife($this);

					if ($days_since_last_update > 90)
					{

						$this->_LoggedIn = false;

						$result = $this->resetPwd($username, false, $suppressUIfunction);

						if (!$suppressUIfunction)
						{
							CApp::bounce("/backoffice/password_expired");
						}
						else
						{
							return $result;
						}
					}
					else if ($days_since_last_update > 80)
					{
						if ($tpl = CApp::instance()->template())
						{
							$tpl->setErrorMsg("Note: It has been $days_since_last_update days since the last time your password was updated. Please update it soon. You will be required to update it after 90 days.");
						}
					}
				}

				CLog::RecordNew(CLog::LOGIN, 'Login successful, User ID: ' . $this->id . ', Username: ' . $username . ', IP Addr: ' . $_SERVER['REMOTE_ADDR'] . ', Agent: ' . addslashes($_SERVER['HTTP_USER_AGENT']));
				CUserHistory::recordUserEvent($this->id, 'null', 'null', 100, 'null', 'null', 'Login succeeded as: ' . $this->primary_email . ', Agent: ' . addslashes($_SERVER['HTTP_USER_AGENT']));
			}
		}

		if ($this->_LoggedIn)
		{

			if (CBrowserSession::getValue('AAA_landing') == 1)
			{
				CUserData::setUserAsAAAReferred($this, 'AAA_referred');
			}

			$cart = CCart2::instance();
			if (!is_null($cart))
			{
				$cart->addUserId($this->id);
			}

			$this->updateLastLogin();

			if (!CApp::$adminView)
			{
				$this->getPlatePointsSummary();
				$this->getMembershipStatus();
			}

			$this->getUserPreferences();

			$this->setMealAndFamilySize();

			if (CUserReferralSource::is_referral_V2_active() && !CCustomerReferral::hasRewardedReferrer($this))
			{
				$referral_org_id = $_COOKIE['RSV2_Origination_code'];

				$RefObj = DAO_CFactory::create('customer_referral');
				$RefObj->origination_uid = $referral_org_id;

				if ($RefObj->find(true) && $RefObj->referral_status < 3)
				{
					if (!empty($RefObj->referred_user_email) && $this->primary_email !== $RefObj->referred_user_email)
					{
						// guest may have clicked a forwarded link so the origination code points to another guest
						$newRefObj = clone($RefObj);
						$newRefObj->id = null;
						$RefObj->referral_status = 2;
						$RefObj->referred_user_id = $this->id;
						$RefObj->insert();
					}
					else
					{

						//we found the referral
						$sessionObj = DAO_CFactory::create('session');
						$sessionObj->query("select s.id, s.session_type,
    			            IF(s.session_type = 'DREAM_TASTE', IF(dtep.can_rsvp_only = 1, 'FNO', IF(dtep.host_required = 1, 'DREAM_TASTE_STANDARD', 'DREAM_TASTE_OPEN_HOUSE')), s.session_type) as full_session_type
    			            from session s
    			            left join session_properties sp on sp.session_id = s.id
    			            left join dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id
    			            where s.id = {$RefObj->referrer_session_id}");
						$sessionObj->fetch();

						/*
						if ($sessionObj->full_session_type == "DREAM_TASTE_STANDARD" || $sessionObj->full_session_type == "FNO")
						{
						}
						else
						{
						}
						*/

						// TODO: if the org type - 6 (shared link) then we also need to update the email address and name of the invited guest

						$RefObj->referral_status = 2;
						$RefObj->referred_user_id = $this->id;
						$RefObj->update();
						$referral_id = $RefObj->id;
					} // email matches

					CBrowserSession::setValue('RSV2_Origination_code', false);
					CBrowserSession::setValue('Inviting_user_id', false);

					//TODO: convert to DebugTrace
					CLog::RecordNew(CLog::DEBUG, "Referral process initiated - ID: " . $referral_id);
				} // ref obj found
			} // cookie is present

		}

		return $this->_LoggedIn;
	}

	function Login($remember_login = false)
	{
		$redirectCustomer = '/';

		// if customer is logging into BackOffice, send them to home page
		if ($this->user_type == self::CUSTOMER && (!empty($_GET['page']) && $_GET['page'] == 'admin_login'))
		{
			$redirectCustomer = '/';
		}
		else if (!empty($_POST['back']))
		{
			$redirectCustomer = $_POST['back'];
		}

		if ($this->user_type != self::CUSTOMER)
		{
			// if staff is login in from home page, send them to BackOffice
			if (empty($_GET['page']) && empty($_GET['static']))
			{
				$redirectCustomer = '/backoffice';
			}
			else if (!empty($_GET['page']) && in_array($_GET['page'], array(
					'admin_login'
				)))
			{
				$redirectCustomer = '/backoffice';
			}

			if (!empty($_GET['back']))
			{
				$redirectCustomer = $_GET['back'];
			}
		}
		else
		{
			if (!empty($_GET['back']))
			{
				$redirectCustomer = $_GET['back'];
			}
		}

		//set and create session
		$browserSession = CBrowserSession::instance();
		$browserSession->createSessionGUID($this, $remember_login);
		$browserSession->user_id = $this->id;
		$browserSession->current_store_id = $this->home_store_id;
		$browserSession->insert();

		CBrowserSession::setCurrentStore($this->home_store_id);

		return $redirectCustomer;
	}

	function Logout()
	{
		$sessionKey = CBrowserSession::instance()->browser_session_key;
		$csrf_protection = new CSRF($sessionKey);
		$csrf_protection->logout();

		$session = CBrowserSession::instance()->ExpireSession();
	}

	// returns 'password_reset_mail_sent', 'password_reset_bad_email_format', 'password_reset_email_not_found', or 'password_reset_unexpected_error'

	static function resetPwd($primary_email, $newPwd = false, $suppressUIfunction = false)
	{
		$primary_email = trim($primary_email);

		if (ValidationRules::validateEmail($primary_email))
		{
			//send mail to

			$loginObj = new DAO_User_login();
			$loginObj->ul_username = $primary_email;

			if ($loginObj->find(true))
			{

				CLog::RecordNew(CLog::LOGIN, "password reset.$primary_email");

				$cUser = DAO_CFactory::create('user');
				$cUser->id = $loginObj->user_id;
				$found = $cUser->find(true);

				if ($found == 1)
				{

					if (!$newPwd)
					{

						list($usec, $sec) = explode(' ', microtime());
						mt_srand((float)$sec + ((float)$usec * 100000));
						$cid = md5(uniqid(mt_rand(), true));

						$cidObj = DAO_CFactory::create('single_use_tokens');
						$cidObj->token = $cid;
						$cidObj->email = $primary_email;
						$cidObj->datetime_created = date("Y-m-d H:i:s");
						$cidObj->insert();

						$passwordLink = HTTPS_BASE . "new-password?cid=" . $cid;

						require_once('CMail.inc');
						$Mail = new CMail();
						$HTMLcontents = CMail::mailMerge('forgotPwd.html.php', array(
							'password_link' => $passwordLink,
							'firstname' => $cUser->firstname
						));
						$Textcontents = CMail::mailMerge('forgotPwd.txt.php', array(
							'password_link' => $passwordLink,
							'firstname' => $cUser->firstname
						));
						$Mail->send(null, null, $cUser->firstname . ' ' . $cUser->lastname, $cUser->primary_email, 'Dream Dinners Password Assistance', $HTMLcontents, $Textcontents);

						if (!$suppressUIfunction)
						{
							CApp::instance()->template()->setStatusMsg('An email has been sent to the email address you provided. This email contains a link that will allow you to reset your password. The link will expire in one hour. If you do not receive the email, or require additional assistance, please contact Customer Service at (360) 804-2020, or your local store.');
						}

						return 'password_reset_mail_sent';
					}
				}
				else
				{
					CLog::RecordIntense("User obj was deleted but email exists in undeleted row in user login: user_id#" . $loginObj->user_id, "ryan.snook@dreamdinners.com");
					// display the message so bad dudes learn nothing
					if (!$suppressUIfunction)
					{
						CApp::instance()->template()->setStatusMsg('An email has been sent to the email address you provided. This email contains a link that will allow you to reset your password. The link will expire in one hour. If you do not receive the email, or require additional assistance, please contact Customer Service at (360) 804-2020, or your local store.');
					}
				}
			}
			else
			{
				// Note: it is a security issuhehe, e indicate that the email was not found so just emulate success.
				if (!$suppressUIfunction)
				{
					CApp::instance()->template()->setStatusMsg('An email has been sent to the email address you provided. This email contains a link that will allow you to reset your password. The link will expire in one hour. If you do not receive the email, or require additional assistance, please contact Customer Service at (360) 804-2020, or your local store.');
				}

				return 'password_reset_mail_sent';
			}
		}
		else
		{
			if (!$suppressUIfunction)
			{
				CApp::instance()->template()->setErrorMsg('You did not enter a properly formatted email address.<br />(Example: user@server.com).');
			}

			return 'password_reset_bad_email_format';
		}

		return 'password_reset_unexpected_error';
	}

	static function getConfirmationId($emailAddress)
	{

		if ($emailAddress)
		{

			//based on the user_login id
			$loginObj = new DAO_User_login();
			$loginObj->ul_username = $emailAddress;
			$loginObj->ul_verified = 'PENDING';
			$loginObj->selectAdd();
			$loginObj->selectAdd('id');
			if ($loginObj->find(true))
			{
				return crypt($loginObj->id, 'fezmonkey');
			}
		}

		return null;
	}

	static function getRandomPwd()
	{
		$salt = "abchefghjkmnpqrstuvwxyz0123456789";
		srand((double)microtime() * 1000000);
		$i = 0;
		$pass = '';
		while ($i <= 7)
		{
			$num = rand() % 33;

			$tmp = substr($salt, $num, 1);

			$pass = $pass . $tmp;

			$i++;
		}

		return $pass;
	}

	// for users with no email address
	function updatePassword($newPwd)
	{

		CLog::RecordDebugTrace("self::updatePassword called for {$this->id}", "NONE", 1, 'DEBUG', true);

		if ($this->user_type == self::CUSTOMER)
		{
			$newHash = CPasswordPolicy::getHash($newPwd, $this->id, true);
			$loginObj = new DAO_User_login();
			$loginObj->query("update user_login set ul_password2 = '$newHash', uses_bcrypt = 1 where user_id = " . $this->id . " and is_deleted = 0");
		}
		else
		{

			$loginObj = new DAO_User_login();
			$loginObj->user_id = $this->id;
			if (!$loginObj->find(true))
			{
				throw new Exception("user_login not found in self::updatePassword()");
			}

			// Note: All validation must be done upstream
			$newHash = CPasswordPolicy::getHash($newPwd, $this->id);

			$loginObj->query("update user_login set ul_password2 = '$newHash', uses_bcrypt = 1 where user_id = " . $this->id . " and is_deleted = 0");
			CPasswordPolicy::recordPasswordUpdate($this->id, $newHash);
		}
	}

	/**
	 * Override the DB_DataObject insert method to do extra validation.
	 *
	 * Added an extra layer of user_type checking to prevent accidental or unwanted user_type submissions from
	 * hacked form fields using setFrom($_POST).
	 *
	 * changed default verification level to 'YES' 12/27/05 ToddW
	 */
	function insert($password = false, $user_type = 'CUSTOMER', $verified = 'YES', $sendCreateEmail = false)
	{
		if ($password == false)
		{
			$password = self::getRandomPwd();
		}

		$this->user_type = $user_type;

		if (CCorporateCrateClient::isEmailAddressCorporateCrateEligible($this->primary_email))
		{
			$this->secondary_email = $this->primary_email;
		}

		if ($this->gender === "")
		{
			$this->gender = 'null';
		}

		$rtn = parent::insert();

		if ($sendCreateEmail)
		{
			self::sendConfirmationEmail($this);
		}

		$ul_verified = $verified;

		if ($rtn)
		{

			//if no primary_email, then generate a login/pwd
			if (!$this->primary_email)
			{
				if ((!$this->firstname) || (!$this->lastname))
				{
					throw new Exception('first and last name required to create user');
				}

				//count same last names then append next number
				$User = new CUser();
				$User->lastname = $this->lastname;
				$cnt = $User->count();

				$ul_username = $User->lastname . ($cnt + 1);
				$ul_verified = 'YES';
			}
			else
			{
				$ul_username = $this->primary_email;
			}

			// Create the initial row
			$loginObj = new DAO_User_login();
			$loginObj->user_id = $this->id;
			$loginObj->ul_password2 = CPasswordPolicy::getHash($password, $this->id, true);
			$loginObj->ul_password = null;
			$loginObj->uses_bcrypt = 1;
			$loginObj->ul_username = $ul_username;
			$loginObj->ul_verified = $ul_verified;
			$rslt = $loginObj->insert();
			if (!$rslt)
			{ //shit
				$this->delete();
				throw new Exception('could not create user');
			}
		}

		return $rtn;
	}

	function hasBalanceDue()
	{
		$DAO_orders_digest = DAO_CFactory::create('orders_digest', true);
		$DAO_orders_digest->user_id = $this->id;
		$DAO_orders_digest->selectAdd("SUM(orders_digest.balance_due) AS total_balance_due");
		$DAO_orders_digest->whereAdd("orders_digest.session_time > (NOW() - INTERVAL 6 MONTH)");
		$DAO_orders_digest->find(true);

		if (!empty($DAO_orders_digest->total_balance_due) && $DAO_orders_digest->total_balance_due != '0.00')
		{
			return true;
		}

		return false;
	}

	function hasPendingDataRequest()
	{
		$task = CUserAccountManagement::fetchTask($this->id, CUserAccountManagement::ACTION_SEND_ACCOUNT_INFORMATION);
		if (empty($task))
		{
			return false;
		}
		if ($task->status == CUserAccountManagement::STATUS_REQUESTED)
		{
			return true;
		}

		return false;
	}

	function hasPendingActivity()
	{
		// has pending orders

		if ($this->hasPendingOrder(false))
		{
			return true;
		}

		// has balance due

		if ($this->hasBalanceDue())
		{
			return true;
		}

		if ($this->hasPendingDataRequest())
		{
			return true;
		}

		// no pending responsibilities
		return false;
	}

	/**
	 * This handles when a user request that their account be deleted for CCPA, etc.
	 * It deativates all records (set is_deleted to true) and clears or nulify any table
	 * attribute associated to the guest that might contain personal information.
	 *
	 * Not all data is cleared. Non-personal info on orders, shipping info and other
	 * info needed for audit purposes is maintained.
	 *
	 * @param false $useWhere
	 * @param false $forceDelete
	 *
	 * @return CResultToken
	 * @throws Exception
	 */
	function handleDeleteAccountRequest($useWhere = false, $forceDelete = false)
	{
		$result = new CResultToken();
		$deleted = false;

		$taskId = CUserAccountManagement::createTask($this->id, CUserAccountManagement::ACTION_DELETE_ACCOUNT);
		if ($this->id)
		{
			if ($this->hasPendingActivity())
			{
				CUserAccountManagement::updateTask($taskId, CUserAccountManagement::ACTION_DELETE_ACCOUNT, CUserAccountManagement::STATUS_FAILED, 'User has pending activity');

				return $deleted;
			}
			else
			{
				$result->addResult($this->clearDataFromShipStation());
				$result->addResult($this->obfuscateUserData());

				$deleted = self::delete($useWhere);

				if ($deleted)
				{
					$this->sendDeleteConfirmation();
					$deleted = true;

					CUserAccountManagement::updateTask($taskId, CUserAccountManagement::ACTION_DELETE_ACCOUNT, CUserAccountManagement::STATUS_COMPLETED, 'User data has been cleared');
				}
				else
				{
					if ($this->is_deleted)
					{
						CUserAccountManagement::updateTask($taskId, CUserAccountManagement::ACTION_DELETE_ACCOUNT, CUserAccountManagement::STATUS_COMPLETED, 'User data has been removed');
					}
					else
					{
						CUserAccountManagement::updateTask($taskId, CUserAccountManagement::ACTION_DELETE_ACCOUNT, CUserAccountManagement::STATUS_FAILED, 'Unable to mark user as deleted in the database');
					}
				}
			}
		}

		$failureHandle = function ($args, $resultToken) {
			$taskId = $args[0];
			CUserAccountManagement::updateTask($taskId, CUserAccountManagement::ACTION_DELETE_ACCOUNT, CUserAccountManagement::STATUS_FAILED, $resultToken->failureMessagesToString());
		};

		//Only does something if there is a failure
		$result->batchLogAndNotifyFailureMessage($failureHandle, array($taskId));

		return $deleted;
	}

	function delete($useWhere = false, $forceDelete = false)
	{
		$deleted = false;

		if ($this->id)
		{
			if ($this->hasPendingActivity())
			{
				return false;
			}
			else
			{
				$deleted = parent::delete($useWhere, $forceDelete);

				if ($deleted)
				{
					$Login = DAO_CFactory::create('user_login');
					$Login->user_id = $this->id;
					$Login->find();
					while ($Login->fetch())
					{
						$Login->delete($useWhere);
					}

					$Addr = DAO_CFactory::create('address');
					$Addr->user_id = $this->id;
					$Addr->find();
					while ($Addr->fetch())
					{
						$Addr->delete($useWhere);
					}

					$UserPref = DAO_CFactory::create('user_preferred');
					$UserPref->user_id = $this->id;
					$UserPref->find();
					while ($UserPref->fetch())
					{
						$UserPref->delete($useWhere);
					}

					$Owner = DAO_CFactory::create('user_to_franchise');
					$Owner->user_id = $this->id;
					$Owner->find();
					while ($Owner->fetch())
					{
						$Owner->delete($useWhere);
					}

					$UTS = DAO_CFactory::create('user_to_store');
					$UTS->user_id = $this->id;
					$UTS->find();
					while ($UTS->fetch())
					{
						$UTS->delete($useWhere);
					}

					$SessionRSVP = DAO_CFactory::create('session_rsvp');
					$SessionRSVP->user_id = $this->id;
					$SessionRSVP->find();
					while ($SessionRSVP->fetch())
					{
						$SessionRSVP->delete($useWhere);
					}
				}
			}
		}

		return $deleted;
	}

	private function sendDeleteConfirmation()
	{
		$result = new CResultToken();
		//To Support
		CEmail::accountRequestDelete($this);
		$result->addSuccessMessage('Sent account delete to support');
		//To Home Store
		CEmail::accountRequestDeleteToStore($this);
		$result->addSuccessMessage('Sent account delete to home store');

		return $result;
	}

	private function clearDataFromShipStation()
	{
		$result = new CResultToken();
		if (!empty($this->id))
		{
			$dao = DAO_CFactory::create('orders');
			$dao->query("select distinct o.id from booking b, orders o, session s, orders_shipping os where b.session_id = s.id and b.order_id = o.id and o.user_id = " . $this->id . " and os.order_id = o.id  and s.session_type = '" . CSession::DELIVERED . "' " . "and os.status in ('NEW','DELIVERED') ");

			while ($dao->fetch())
			{
				$order = new COrdersDelivered();
				$order->id = $dao->id;
				$result = ShipStationManager::getInstanceForOrder($order)->addUpdateOrder(new ShipStationOrderWrapper($order));
				if ($result == false)
				{
					$error = ShipStationManager::getInstanceForOrder($order)->getLastError();
					if (!is_null($error))
					{
						$result->addFailureMessage($error->message);
					}
					else
					{
						$result->addFailureMessage('Unable to clear data from shipstation on user delete for order id: ' . $dao->id);
					}
				}
			}
		}

		return $result;
	}

	//Clear or obscure a users personal data from relevant tables
	private function obfuscateUserData($markFullDelete = true)
	{
		$result = new CResultToken();
		if (!empty($this->id))
		{
			//User Table
			$User = DAO_CFactory::create('user');

			$User->id = $this->id;
			$User->find(true);
			$User->primary_email = 'is_deleted.' . $this->id . '@example.com';
			$User->secondary_email = '';
			$User->telephone_1 = '###-xxx-xxxx';
			$User->telephone_2 = '#xx-xxx-xxxx';
			$User->fax = 'xxx-xxx-xxxx';
			$User->facebook_id = 0;
			$User->facebook_oauth_token = 0;
			$User->telephone_day = 'xxx-xxx-xxxx';
			$User->telephone_evening = 'xxx-xxx-xxxx';
			$User->admin_note = '';
			$User->visit_count = 0;
			$User->dream_reward_status = 0;
			$User->dream_reward_level = 0;
			$User->dr_downgraded_order_count = 0;
			$User->has_opted_out_of_plate_points = 0;
			$User->gender = 'X';
			$User->facebook_last_login = null;
			$User->last_login = null;

			if ($markFullDelete)
			{
				$User->is_ccpa_deleted = 1;
			}

			if ($User->N > 0)
			{
				$User->update();
			}

			if ($User->_lastError == false)
			{
				$result->addSuccessMessage('Obfuscated User Table for user ' . $this->id);
			}
			else
			{
				$result->addFailureMessage('Unable to Obfuscated User Table for user id ' . $this->id);
			}

			//User Card Reference
			$dao = DAO_CFactory::create('user_card_reference');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->card_transaction_number = 'xxxxxxxxx';
					$dao->card_number = '0000';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//User Data Table
			$dao = DAO_CFactory::create('user_data');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->user_data_value = '';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//User Digest Table
			$dao = DAO_CFactory::create('user_digest');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->first_session = null;
					$dao->visit_count = 0;
					$dao->cutomer_order_type = 'NO_ORDERS';
					$dao->total_delivered_boxes = 0;
					$dao->last_achievement_achieved_id = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//*******Dont clear Employee Data
			//User Employee Add Info Table
			//User Employee History Table
			//User to store Table

			//User History Table
			$dao = DAO_CFactory::create('user_history');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->ip_address = '';
					$dao->description = '';
					$dao->event_id = '';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//User Login Table
			$dao = DAO_CFactory::create('user_login');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->ul_password = null;
					$dao->ul_password2 = null;
					$dao->ul_username = null;
					$dao->ul_verified = null;
					$dao->event_id = '';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//User Referral Source Table
			$dao = DAO_CFactory::create('user_referral_source');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					//$dao->inviting_user_id = null;
					//$dao->customer_referral_id = null;
					$dao->meta = 'removed on CCPA user deletion request';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			$urd_ids = array();
			//User Retention Data
			$dao = DAO_CFactory::create('user_retention_data');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$urd_ids[] = $dao->id;
					$dao->number_days_inactivity = 0;
					$dao->booking_count = 0;
					$dao->updated_order_id = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//User Retention Data Follow up
			if (count($urd_ids) > 0)
			{

				foreach ($urd_ids as $urd_id)
				{
					$dao = DAO_CFactory::create('user_retention_data_follow_up');
					$dao->user_retention_data_id = $urd_id;
					$dao->find(true);
					if ($dao->N > 0)
					{

						while ($dao->fetch())
						{
							$dao->result_comments = '';
							$dao->results_date = null;
							$dao->update();
							if ($dao->_lastError == false)
							{
								$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
							}
							else
							{
								$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
							}
						}
					}
					$dao = null;
				}
			}

			//Store History
			$dao = DAO_CFactory::create('store_history');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->description = '';
					$dao->ip_address = '';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//Payment
			$dao = DAO_CFactory::create('payment');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->payment_transaction_number = '';
					$dao->do_not_reference = 1;
					$dao->card_number = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//Product_Payment
			$dao = DAO_CFactory::create('product_payment');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->payment_transaction_number = '';
					$dao->card_number = null;
					$dao->do_not_reference = 1;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//Session Properties
			$dao = DAO_CFactory::create('session_properties');
			$dao->session_host = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->message = '';
					$dao->informal_host_name = null;
					$dao->facebook_post_id = null;
					$dao->facebook_event_id = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//Password History
			$dao = DAO_CFactory::create('password_history');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->password = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			$order_ids = array();
			//Orders
			$dao = DAO_CFactory::create('orders');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$order_ids[] = $dao->id;
					$dao->order_admin_notes = '';
					$dao->order_user_notes = '';
					$dao->order_revision_notes = '';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			$dao = DAO_CFactory::create('edited_orders');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->order_admin_notes = '';
					$dao->order_user_notes = '';
					$dao->order_revision_notes = '';
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//Orders Address
			if (count($order_ids) > 0)
			{
				foreach ($order_ids as $order_id)
				{
					$dao = DAO_CFactory::create('orders_address');
					$dao->order_id = $order_id;
					$dao->find(true);
					if ($dao->N > 0)
					{

						while ($dao->fetch())
						{
							//Keeping for Delivery and Delivered in case of sales tax audit we need to keep the home delivery or shipped address only.
							//$dao->firstname = '';
							//$dao->lastname = 'is_deleted';
							$dao->telephone_1 = '';
							//$dao->address_line_1 = '';
							//$dao->address_line_2 = '';
							//$dao->city = '';
							//$dao->state_id = null;
							//$dao->postal_code = null;
							$dao->address_notes = null;
							$dao->email_address = null;
							$dao->update();
							if ($dao->_lastError == false)
							{
								$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
							}
							else
							{
								$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
							}
						}
					}
					$dao = null;
				}
			}

			//Gift card order
			$dao = DAO_CFactory::create('gift_card_order');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->first_name = '';
					$dao->last_name = 'is_deleted';
					$dao->shipping_address_1 = '';
					$dao->shipping_address_2 = '';
					$dao->billing_zip = null;
					$dao->shipping_city = null;
					$dao->shipping_state = null;
					$dao->shipping_zip = null;
					$dao->email = null;
					$dao->cc_ref_number = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//customer_referral
			$dao = DAO_CFactory::create('customer_referral');
			$dao->referring_user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->referred_user_email = null;
					$dao->referred_user_name = null;
					$dao->inviting_user_name = null;
					$dao->admin_notes = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//customer_referral
			$dao = DAO_CFactory::create('customer_referral');
			$dao->referred_user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->referred_user_email = null;
					$dao->referred_user_name = null;
					$dao->inviting_user_name = null;
					$dao->admin_notes = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//Address Table
			$dao = DAO_CFactory::create('address');
			$dao->user_id = $this->id;
			$dao->find(true);
			if ($dao->N > 0)
			{

				while ($dao->fetch())
				{
					$dao->firstname = '';
					$dao->lastname = 'is_deleted';
					$dao->telephone_1 = '';
					$dao->address_line_1 = '';
					$dao->address_line_2 = '';
					$dao->city = '';
					$dao->state_id = 'WA';
					$dao->postal_code = null;
					$dao->address_notes = null;
					$dao->email_address = null;
					$dao->update();
					if ($dao->_lastError == false)
					{
						$result->addSuccessMessage('Obfuscated ' . $dao->tableName() . ' Table, record id = ' . $dao->id);
					}
					else
					{
						$result->addFailureMessage('Unable to Obfuscated ' . $dao->tableName() . ' Table for user id ' . $this->id);
					}
				}
			}
			$dao = null;

			//dreamlog cleanup
			$mdb = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
			mysqli_select_db($mdb, DB_DATABASE);

			$sql = "update dreamlog.nav_log set ip_address = '' where user_id = " . $this->id;
			$dbresult = mysqli_query($mdb, $sql);
			if ($dbresult)
			{
				$result->addSuccessMessage('Obfuscated Nav-Log Table');
			}
			else
			{
				$result->addFailureMessage('Unable to Obfuscated Nav-Log Table for user id ' . $this->id);
			}

			$sql = "update dreamlog.debug_log set description = '', ip_address = '' where user_id = " . $this->id;
			$dbresult = mysqli_query($mdb, $sql);
			if ($dbresult)
			{
				$result->addSuccessMessage('Obfuscated Debug-Log Table');
			}
			else
			{
				$result->addFailureMessage('Unable to Obfuscated Debug-Log Table for user id ' . $this->id);
			}

			$sql = "update dreamlog.email_log set sender_email_address = '', recipient_email_address = '' where ( recipient_user_id = " . $this->id . " or sender_user_id = " . $this->id . ")";
			$dbresult = mysqli_query($mdb, $sql);
			if ($dbresult)
			{
				$result->addSuccessMessage('Obfuscated Email-Log Table');
			}
			else
			{
				$result->addFailureMessage('Unable to Obfuscated Email-Log Table for user id ' . $this->id);
			}

			//mysqli_close($mdb);
			return $result;
		}
	}

	function hasPrimaryEmailChanged()
	{
		return ($this->primary_email !== $this->_original_primary_email);
	}

	/*
	*
	* Added an extra layer of user_type checking to prevent accidental or unwanted user_type submissions from
	* hacked form fields using setFrom($_POST).
	*/
	function update($dataObject = false, $user_type = null)
	{

		//don't include user_type in the update unless is is passed in
		$origUserType = $this->user_type;
		unset($this->user_type);
		if ($dataObject)
		{
			unset($dataObject->user_type);
		}

		//only update user_type explicitly
		if ($user_type != null)
		{
			$this->user_type = $user_type;
			$origUserType = $user_type;
		}

		if ($this->call_time === "")
		{
			$this->call_time = 'null';
		}

		if ($this->telephone_1_call_time === "")
		{
			$this->telephone_1_call_time = 'null';
		}

		if ($this->telephone_2_call_time === "")
		{
			$this->telephone_2_call_time = 'null';
		}

		$rtn = parent::update($dataObject);

		$this->user_type = $origUserType;
		if ($dataObject)
		{
			$dataObject->user_type = $origUserType;
		}

		if ($this->primary_email && $this->hasPrimaryEmailChanged())
		{
			//update _login table
			$loginObj = new DAO_User_login();
			$loginObj->user_id = $this->id;

			//don't select password or user_id
			$loginObj->selectAdd();
			$loginObj->selectAdd('id, ul_username');
			$loginObj->find(true);
			$loginObj->ul_username = $this->primary_email;
			//				$loginObj->ul_verified = 'PENDING'; ///change verification status to pending if the email address is updated
			$rslt = $loginObj->update();
			if (!$rslt)
			{
				throw new Exception('could not update login information');
			}
		}

		return $rtn;
	}

	function convert_partial($password)
	{

		/// need to update a few fields as User was not fetched from database
		$this->user_type = self::CUSTOMER;
		$this->is_partial_account = 0;

		$rtn = parent::update();

		if (!empty($password))
		{
			$loginObj = new DAO_User_login();
			$loginObj->user_id = $this->id;

			if (!$loginObj->find(true))
			{
				$loginObj->ul_username = $this->primary_email;
				$loginObj->ul_password2 = CPasswordPolicy::getHash($password, $this->id, true);
				$loginObj->ul_password = null;
				$loginObj->uses_bcrypt = 1;

				$loginObj->ul_verified = 1;
				$rslt = $loginObj->insert();
				if (!$rslt)
				{
					throw new Exception('could not insert login information in convert_partial');
				}
			}
			else
			{
				$oldRow = clone($loginObj);
				$loginObj->ul_username = $this->primary_email;
				$loginObj->ul_password2 = CPasswordPolicy::getHash($password, $this->id, true);
				$loginObj->ul_password = null;
				$loginObj->uses_bcrypt = 1;

				$loginObj->ul_verified = 1;
				$rslt = $loginObj->update($oldRow);
				if (!$rslt)
				{
					throw new Exception('could not update login information in convert_partial');
				}
			}
		}

		return $rtn;
	}

	/**
	 * There's no easy way to use DB_DataObject to update a timestamp field, so
	 * use this method to update the last login field
	 */
	function updateLastLogin()
	{

		//this sucks, but there's not really an easy way to update the last_login using
		//the db to set the timestamp, then update our object.
		if ($this->id)
		{
			$db = $this->getDatabaseConnection();
			$db->query('UPDATE ' . $this->__table . ' SET last_login = Now(), visit_count = ' . ($this->visit_count + 1) . ' WHERE id = ' . $this->id);

			$res = $db->query('SELECT last_login FROM ' . $this->__table . ' WHERE id = ' . $this->id);
			if (DB::isError($res))
			{
				throw new Exception($res->getMessage());
			}
			$row = $res->fetchRow();
			$this->last_login = $row[0];
		}
		else
		{
			throw new Exception('login error');
		}
	}

	/* ------------------------------------------------------------
	*
	* Class: CUser
	*
	* Function: Create
	*
	* Params:	$this->Username
	*
	* Properties can be dyanmic but should include:
	*	password, email, ..
	*
	* -------------------------------------------------------------- */

	//		function Create() {
	//
	//			global $Log;
	//
	//
	//			if (! isset ($this->Username)) return "Username not set.";
	//			if (! isset ($this->ID)) $this->ID = 0;
	//
	//
	//			// If the UserName is set, find the ID
	//
	//			if (strlen($this->Username) > 1){
	//				$szSelect = "SELECT id FROM user WHERE username = '$this->Username'";
	//				$rs = mysql_query($szSelect);
	//				if (mysql_num_rows($rs) != 0){
	//					$Log->Normal ("CUser.inc:Create():$this->Username - User exists.");
	//					return "User exists";
	//				}
	//			}
	//
	//			// Create the initial row
	//			$szInsert = "INSERT INTO user (username, password) VALUES ('$this->Username', '$this->Password')";
	//			$rs = mysql_query($szInsert);
	//			$this->ID = mysql_insert_id();
	//			$Log->Normal("CUser.inc:Create():Added username $this->Username");
	//
	//
	//
	//			foreach (get_object_vars($this) as $key=>$val){
	//				if (substr($key, 0, 1) != "_"){
	//					$szInsertProp = "INSERT INTO user_properties (user_id, pkey, pvalue) VALUES ($this->ID, '$key', '$val')";
	//					$rs = mysql_query($szInsertProp);
	//				}
	//				// else skip property
	//				if (DEBUG) echo "<br />Added property $key = $val";
	//			}
	//
	//			return E_SUCCESS;
	//
	//		}
	//
	/**
	 * Checks to see if this user already exists in the db.
	 */
	public function exists()
	{
		if (empty($this->primary_email))
		{
			return true;
		} //TODO:: throw exception here

		// don't let people use the support email address
		if (strtolower($this->primary_email) === 'support@dreamdinners.com')
		{
			return true;
		}

		// check if an existing user has this address
		$DAO_user = DAO_CFactory::create('user');
		$DAO_user->primary_email = $this->primary_email;
		if ($DAO_user->find())
		{
			return true;
		}

		// don't let people use the store email address
		$DAO_store = DAO_CFactory::create('store');
		$DAO_store->email_address = $this->primary_email;
		if ($DAO_store->find())
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks to see if this user already exists in the db.
	 */
	public function partial_account_exists()
	{
		if (empty($this->primary_email))
		{
			return false;
		}

		$User = DAO_CFactory::create('user');
		$User->primary_email = $this->primary_email;
		$User->is_partial_account = 1;
		$User->selectAdd();
		$User->selectAdd('id');

		$cnt = $User->find(true);
		if ($cnt)
		{
			$this->id = $User->id;

			return true;
		}

		return false;
	}

	/**
	 * Get associated address record and call find
	 * @return DataObject
	 **/

	function getPrimaryAddress()
	{
		if ($this->id)
		{
			$rtn = DAO_CFactory::create('address');
			$rtn->user_id = $this->id;
			$rtn->location_type = CAddress::BILLING;
			$rtn->is_primary = 1;

			$rtn->find(true);

			return $rtn;
		}

		return false;
	}

	function getAddressBookArray($includeBillingIfEmpty = false, $forceRefresh = false)
	{
		if (!$forceRefresh && !empty($this->addressBook))
		{
			return $this->addressBook;
		}

		$addrBook = DAO_CFactory::create('address');
		$addrBook->user_id = $this->id;
		$addrBook->location_type = CAddress::ADDRESS_BOOK;
		$addrBook->orderBy("lastname");
		$addrBook->find();

		while ($addrBook->fetch())
		{
			$this->addressBook[$addrBook->id] = clone($addrBook);
		}

		// if the address book is empty, populate with billing address
		if (empty($this->addressBook) && $includeBillingIfEmpty)
		{
			$billing = $this->getPrimaryBillingAddress();

			$billAddr[$billing->id] = $billing;

			$this->addressBook = $billAddr + $this->addressBook;
		}

		return $this->addressBook;
	}

	function getPrimaryBillingAddress()
	{
		if ($this->id)
		{
			$rtn = DAO_CFactory::create('address');
			$rtn->user_id = $this->id;
			$rtn->location_type = CAddress::BILLING;
			$rtn->is_primary = 1;

			$rtn->find(true);

			return $rtn;
		}

		return false;
	}

	function getShareURL()
	{
		return HTTPS_BASE . 'share/' . $this->id;
	}

	function getShippingAddress()
	{
		if ($this->id)
		{
			$rtn = DAO_CFactory::create('address');
			$rtn->user_id = $this->id;
			$rtn->location_type = CAddress::SHIPPING;
			$rtn->is_primary = 1;

			$rtn->find(true);

			return $rtn;
		}

		return false;
	}

	function getDaysInactive()
	{
		if ($this->get_Booking_Next() !== null)
		{
			return 0;
		}
		else if ($this->get_Booking_Last() !== null)
		{
			$origin = new DateTimeImmutable($this->get_Booking_Last()->get_DAO_session()->session_start);
			$target = new DateTimeImmutable();
			$interval = $origin->diff($target);
			return $interval->format('%a');
		}
		else
		{
			$origin = new DateTimeImmutable($this->timestamp_created);
			$target = new DateTimeImmutable();
			$interval = $origin->diff($target);
			return $interval->format('%a');
		}
	}

	function getDeliveredAddressDefault()
	{
		if ($this->id)
		{
			$this->getAddressBookArray(true);

			foreach ($this->addressBook as $address)
			{
				return $address;

				break;
			}
		}

		return false;
	}

	function getDeliveryAddressDefault()
	{
		$rslt = $this->getShippingAddress();

		if ($rslt->id)
		{
			return $rslt;
		}
		else
		{
			$rslt = $this->getPrimaryAddress();

			return $rslt;
		}
	}

	/**
	 * Override to store original primary_email value. On updates, we want to also update the _login table.
	 */
	public function fetch()
	{
		$rtn = parent::fetch();

		if ($this->primary_email)
		{
			$this->_original_primary_email = $this->primary_email;
		}
		else
		{
			$this->_original_primary_email = null;
		}

		return $rtn;
	}

	static public function sendConfirmationEmail($user)
	{
		//$confirmId = self::getConfirmationId($user->primary_email);
		$confirmId = null;
		require_once('CMail.inc');
		$Mail = new CMail();
		$HTMLcontents = CMail::mailMerge('new_account.html.php', array(
			'confirm_key' => $confirmId,
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname
		));
		$Txtcontents = CMail::mailMerge('new_account.txt.php', array(
			'confirm_key' => $confirmId,
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname
		));

		$Mail = new CMail();
		$Mail->to_id = $user->id;
		$Mail->to_name = $user->firstname . ' ' . $user->lastname;
		$Mail->to_email = $user->primary_email;
		$Mail->subject = 'New Account Confirmation';
		$Mail->body_html = $HTMLcontents;
		$Mail->body_text = $Txtcontents;
		$Mail->sendEmail();
	}

	static public function sendConfirmationRetryEmail($user)
	{
		//$confirmId = self::getConfirmationId($user->primary_email);
		$confirmId = null;
		require_once('CMail.inc');
		$Mail = new CMail();
		$HTMLcontents = CMail::mailMerge('new_account_retry.html.php', array(
			'confirm_key' => $confirmId,
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname
		));
		$Txtcontents = CMail::mailMerge('new_account_retry.txt.php', array(
			'confirm_key' => $confirmId,
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname
		));

		$Mail = new CMail();
		$Mail->to_id = $user->id;
		$Mail->to_name = $user->firstname . ' ' . $user->lastname;
		$Mail->to_email = $user->primary_email;
		$Mail->subject = 'New Account Confirmation';
		$Mail->body_html = $HTMLcontents;
		$Mail->body_text = $Txtcontents;
		$Mail->sendEmail();
	}

	static public function sendEmailIssueEmail($user)
	{
		//$confirmId = self::getConfirmationId($user->primary_email);
		require_once('CMail.inc');
		$Mail = new CMail();
		$HTMLcontents = CMail::mailMerge('email_issue.html.php', array(
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname
		));
		$Txtcontents = CMail::mailMerge('email_issue.txt.php', array(
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname
		));

		$Mail = new CMail();
		$Mail->to_id = $user->id;
		$Mail->to_name = $user->firstname . ' ' . $user->lastname;
		$Mail->to_email = $user->primary_email;
		$Mail->subject = 'New Account Confirmation';
		$Mail->body_html = $HTMLcontents;
		$Mail->body_text = $Txtcontents;
		$Mail->sendEmail();
	}

	static public function sendAccountChangedEmail($user)
	{
		require_once('CMail.inc');
		$Mail = new CMail();
		$HTMLcontents = CMail::mailMerge('edit_account.html.php', array(
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname,
			'store_id' => $user->home_store_id
		));
		$Txtcontents = CMail::mailMerge('edit_account.txt.php', array(
			'primary_email' => $user->primary_email,
			'firstname' => $user->firstname,
			'store_id' => $user->home_store_id
		));

		$Mail = new CMail();
		$Mail->to_id = $user->id;
		$Mail->to_name = $user->firstname . ' ' . $user->lastname;
		$Mail->to_email = $user->primary_email;
		$Mail->subject = 'Account Change Notification';
		$Mail->body_html = $HTMLcontents;
		$Mail->body_text = $Txtcontents;
		$Mail->sendEmail();
	}

	/**
	 * Change the confirmed status of an account to YES
	 */
	static public function confirmUser($primary_email)
	{
		//set user_login's verified flag to 'YES' only if
		//it is currently set to 'PENDING'
		$original = new DAO_User_login();
		$original->ul_username = $primary_email;
		$original->ul_verified = 'PENDING';
		$rslt = $original->find(true);
		if ($rslt)
		{
			$loginRecord = new DAO_User_login();
			$loginRecord->id = $original->id;
			$loginRecord->user_id = $original->user_id;
			$loginRecord->ul_username = $primary_email;
			$loginRecord->ul_verified = 'YES';
			$rslt = $loginRecord->update($original);
		}

		return $rslt;
	}

	/**
	 * @return full name string
	 */
	public function getName()
	{
		return $this->firstname . ' ' . $this->lastname;
	}

	public function hasSessionToday()
	{
		$booking = DAO_CFactory::create('Booking');
		$session = DAO_CFactory::create('Session');
		$booking->user_id = $this->id;
		$booking->joinAdd($session);
		$booking->whereAdd('DATEDIFF(CurDate(), session_start) = 0');
		if ($booking->find())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}

?>