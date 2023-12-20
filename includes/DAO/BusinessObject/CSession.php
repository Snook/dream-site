<?php
require_once 'includes/DAO/Session.php';
require_once 'includes/DAO/BusinessObject/CCorporateCrateClient.php';
require_once 'includes/DAO/BusinessObject/CTimezones.php';
require_once 'includes/CCalendar.inc';
require_once 'includes/iCalcreator.class.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CSession
 *
 *	Data:
 *
 *	Methods:
 *
 *  	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class CSession extends DAO_Session
{

	const STANDARD = 'STANDARD';
	const INTRO = 'INTRO';
	const QUICKSIX = 'QUICKSIX';
	const MENU_PLAN = 'MENU_PLAN';
	const SPECIAL_EVENT = 'SPECIAL_EVENT';
	const MADE_FOR_YOU = 'SPECIAL_EVENT';
	const TODD = 'TODD'; // session_type & session_properties enum - pre-dream taste
	const DREAM_TASTE = 'DREAM_TASTE';
	const FUNDRAISER = 'FUNDRAISER';
	const EVENT = 'EVENT'; // encompasses DREAM_TASTE, FUNDRAISER
	const DELIVERY = 'DELIVERY'; // Made For You for Delivery
	const DELIVERY_PRIVATE = 'DELIVERY_PRIVATE'; // Passworded Made For You for Delivery
	const REMOTE_PICKUP = 'REMOTE_PICKUP'; // Made For You remote pickup
	const REMOTE_PICKUP_PRIVATE = 'REMOTE_PICKUP_PRIVATE'; // Passworded Made For You remote pickup
	const ALL_STANDARD = 'ALL_STANDARD'; // Everything but Events and Intro
	const DELIVERED = 'DELIVERED'; // Shipping from a Dist Ctr
	const WALK_IN = 'WALK_IN'; // Standard Walk-in

	const SAVED = 'SAVED';
	const PUBLISHED = 'PUBLISHED';
	const CLOSED = 'CLOSED';
	const PRIVATE_SESSION = 'PRIVATE_SESSION';
	const DISCOUNTED = 'DISCOUNTED';

	const HOURS = 'HOURS';
	const ONE_FULL_DAY = 'ONE_FULL_DAY';
	const FOUR_FULL_DAYS = 'FOUR_FULL_DAYS';

	public $DAO_store;

	function __construct()
	{
		parent::__construct();
	}

	function fetch()
	{
		$res = parent::fetch();

		if ($res)
		{
			$this->sessionTypeToText();
			$this->getSessionTypeProperties();

			if (!empty($this->DAO_menu))
			{
				$this->DAO_menu->expandData();
			}
		}

		return $res;
	}

	function find($n = false)
	{
		return parent::find($n);
	}

	function find_DAO_session($n = false)
	{
		if ($this->_query["data_select"] === "*")
		{
			throw new Exception("When creating this object, second parameter in DAO_CFactory::create() needs to be 'true'");
		}

		$this->joinAddWhereAsOn(DAO_CFactory::create('menu', true));
		$this->joinAddWhereAsOn(DAO_CFactory::create('store', true));

		$DAO_session_discount = DAO_CFactory::create('session_discount', true);
		$DAO_session_discount->unsetProperty('is_deleted'); // make sure to join deleted rows to account for edited sessions
		$this->joinAddWhereAsOn($DAO_session_discount, 'LEFT');

		$DAO_session_properties = DAO_CFactory::create('session_properties', true);

		$DAO_dream_taste_event_properties = DAO_CFactory::create('dream_taste_event_properties', true);
		$DAO_dream_taste_event_properties->joinAddWhereAsOn(DAO_CFactory::create('dream_taste_event_theme', true), 'LEFT');
		$DAO_session_properties->joinAddWhereAsOn($DAO_dream_taste_event_properties, 'LEFT');

		$DAO_store_to_fundraiser = DAO_CFactory::create('store_to_fundraiser', true);
		$DAO_fundraiser = DAO_CFactory::create('fundraiser', true);
		$DAO_store_to_fundraiser->joinAddWhereAsOn($DAO_fundraiser, 'LEFT');
		$DAO_session_properties->joinAddWhereAsOn($DAO_store_to_fundraiser, 'LEFT');

		$DAO_session_properties->joinAddWhereAsOn(DAO_CFactory::create('store_pickup_location', true), 'LEFT');

		$this->joinAddWhereAsOn($DAO_session_properties, 'LEFT');

		return parent::find($n);
	}

	static function isTODDSession($session_id)
	{
		$SessionDAO = DAO_CFactory::create('session');
		$SessionDAO->id = $session_id;
		$SessionDAO->session_type = CSession::TODD;
		$SessionDAO->find();
		if ($SessionDAO->N == 1)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param      $dateStr String 'yyyy-mm-dd'
	 * @param      $menu_id
	 * @param bool $allDistributionCenters Optional -> default is true, will create for all DCs
	 * @param int $specificDistCenterId Optional -> default is null and ignored, will be used if $allDistributionCenters == true and a valid positive int
	 *                                  is passed
	 *
	 * @throws Exception
	 */
	static function generateDeliveredBlackoutSession($dateStr, $menu_id, $allDistributionCenters = true, $specificDistCenterId = null)
	{

		$date = DateTime::createFromFormat('Y-m-d',$dateStr);

		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->active = 1;
		$DAO_store->store_type = CStore::DISTRIBUTION_CENTER;
		if(!$allDistributionCenters && is_numeric($specificDistCenterId))
		{
			$DAO_store->id = $specificDistCenterId;
		}
		$DAO_store->find();

		while ($DAO_store->fetch())
		{
			self::addUpdateDeliveredSession($date, $DAO_store->id, $menu_id, false, false, 0, 'Store Shipping Blackout');
		}
	}

	static function generateDeliveredSessionsForMenu($menu_id)
	{

		$menuInfo = CMenu::getMenuInfo($menu_id);

		$startDate = new DateTime($menuInfo['global_menu_start_date']);
		// menu starts on Monday but first session associated with menu begins on Thursday
		// TODO: will this have ripple effects?  having sessions with a menu whose end date is prior to the session start date?
		// Does it even matter?  Could we just assign the menu according to the same rules as store retail?
		$startDate->modify("+ 3 days");
		$endDate = new DateTime($menuInfo['global_menu_end_date']);
		$endDate->modify("+ 4 days");

		$validDeliveryDayOfWeekArray = array(
			"Tuesday",
			"Wednesday",
			"Thursday",
			"Friday"
		);
		$validShippingDayOfWeekArray = array(
			"Monday",
			"Tuesday",
			"Wednesday"
		);

		$DAO_store = DAO_CFactory::create('store', true);
		$DAO_store->active = 1;
		$DAO_store->store_type = CStore::DISTRIBUTION_CENTER;
		$DAO_store->find();
		$defaultMaximum = 25;//overridden below if value fetched from store/DC > 0

		while ($DAO_store->fetch())
		{
			if (!empty($DAO_store->default_delivered_sessions) && $DAO_store->default_delivered_sessions > 0)
			{
				$defaultMaximum = $DAO_store->default_delivered_sessions;
			}
			$cursorDate = clone($startDate);
			while ($cursorDate != $endDate)
			{
				// Test 1: supported day of the week
				$canDeliverThisDay = true;
				if (!in_array($cursorDate->format("l"), $validDeliveryDayOfWeekArray))
				{
					$canDeliverThisDay = false;
				}

				// Test 1b: supported day of the week
				$canShipThisDay = true;
				if (!in_array($cursorDate->format("l"), $validShippingDayOfWeekArray))
				{
					$canShipThisDay = false;
				}

				// Test 2: Carrier Blackout Date
				$carrier_blackout = new DAO();
				$carrier_blackout->query("select * from `dreamsite`.shipping_blackout where date = '" . $cursorDate->format("Y-m-d H:i:s") . "' and entity_type = 'SHIPPING_CARRIER' and entity_id = 1 and is_deleted = 0");
				if ($carrier_blackout->N > 0)
				{
					//create blackout session
					self::addUpdateDeliveredSession($cursorDate, $DAO_store->id, $menu_id, false, false, 0, 'Carrier Blackout');
					$cursorDate->modify("+ 1 day");
					continue;
				}

				// Test 3:  Store Blackout date
				$carrier_blackout = new DAO();
				$query = "select * from shipping_blackout where date = '" . $cursorDate->format("Y-m-d H:i:s") . "' and entity_type = 'STORE' and entity_id = " . $DAO_store->id . " and is_deleted = 0";
				$carrier_blackout->query($query);
				if ($carrier_blackout->N > 0)
				{
					//create blackout session
					self::addUpdateDeliveredSession($cursorDate, $DAO_store->id, $menu_id, false, false, 0, 'Store Shipping Blackout');
					$cursorDate->modify("+ 1 day");
					continue;
				}

				// Test 4:  Is there already a session
				self::addUpdateDeliveredSession($cursorDate, $DAO_store->id, $menu_id, $canDeliverThisDay, $canShipThisDay, $defaultMaximum);

				$cursorDate->modify("+ 1 day");
			}
		}
	}

	static function generateWalkInSessionsForMenu($menu_id, $addOnly = false)
	{

		$menuInfo = CMenu::getMenuInfo($menu_id);

		$startDate = new DateTime($menuInfo['global_menu_start_date']);
		$endDate = new DateTime($menuInfo['global_menu_end_date']);
		//So that the last day of the month is included
		$endDate->modify("+ 1 days");

		$stores = new DAO();
		$stores->query("select id from store where store_type = 'FRANCHISE' and  ( active = 1 OR ssm_builder = 1 ) and is_deleted = 0");

		while ($stores->fetch())
		{

			$cursorDate = clone($startDate);
			while ($cursorDate != $endDate)
			{
				self::addUpdateWalkInSession($cursorDate, $stores->id, $menu_id, null, $addOnly);

				$cursorDate->modify("+ 1 day");
			}
		}
	}

	public static function getDeliveryServiceDays($date)
	{
		switch ($date->format("l"))
		{
			case "Sunday":
				return 0;
			case "Monday":
				return 0;
			case "Tuesday":
				return 1;
			case "Wednesday":
				return 2;
			case "Thursday":
				return 2;
			case "Friday":
				return 2;
			case "Saturday":
				return 0;
			default:
				return 0;
		}
	}

	public static function getShippingServiceDays($date)
	{
		switch ($date->format("l"))
		{
			case "Sunday":
				return 0;
			case "Monday":
				return 2;
			case "Tuesday":
				return 2;
			case "Wednesday":
				return 2;
			case "Thursday":
				return 1;
			case "Friday":
				return 0;
			case "Saturday":
				return 0;
			default:
				return 0;
		}
	}

	//Creates or Modifies Delivered sessions
	private static function addUpdateDeliveredSession($date, $storeId, $menu_id, $canDeliverThisDay, $canShipThisDay, $defaultMaximum, $details = null)
	{
		$thisSession = DAO_CFactory::create('session');
		$thisSQLDate = $date->format("Y-m-d");
		$thisSession->query("select * from session where store_id = {$storeId} and DATE(session_start) = '$thisSQLDate' and session_type='DELIVERED' and is_deleted = 0");
		if ($thisSession->N > 0)
		{
			$thisSession->fetch();
			$oldSession = clone($thisSession);
			$thisSession->delivered_supports_delivery = ($canDeliverThisDay ? self::getDeliveryServiceDays($date) : "0");
			$thisSession->delivered_supports_shipping = ($canShipThisDay ? self::getShippingServiceDays($date) : "0");
			$thisSession->available_slots = $defaultMaximum;
			$thisSession->introductory_slots = 0;
			$thisSession->sneak_peak = 0;
			$thisSession->duration_minutes = 60 * 12;
			$thisSession->session_type = CSession::DELIVERED;
			$thisSession->session_publish_state = CSession::PUBLISHED;
			$thisSession->session_details = $details;
			$thisSession->update($oldSession);
		}
		else
		{
			$thisSession->store_id = $storeId;
			$thisSession->menu_id = $menu_id;
			$thisSession->available_slots = $defaultMaximum;
			$thisSession->introductory_slots = 0;
			$thisSession->sneak_peak = 0;
			$thisSession->duration_minutes = 60 * 12;
			$thisSession->session_type = CSession::DELIVERED;
			$thisSession->session_publish_state = CSession::PUBLISHED;
			$thisSession->session_start = $date->format("Y-m-d 08:00:00");
			$thisSession->session_close_scheduling = $date->format("Y-m-d 08:00:00");
			$thisSession->session_close_scheduling_meal_customization = null;
			$thisSession->delivered_supports_delivery = ($canDeliverThisDay ? self::getDeliveryServiceDays($date) : "0");
			$thisSession->delivered_supports_shipping = ($canShipThisDay ? self::getShippingServiceDays($date) : "0");
			$thisSession->session_details = $details;
			$thisSession->insert();
		}
	}

	//Creates or Modifies Walk-in sessions
	private static function addUpdateWalkInSession($date, $storeId, $menu_id, $details = null, $addOnly = false)
	{
		$thisSession = DAO_CFactory::create('session');
		$thisSQLDate = $date->format("Y-m-d");
		$thisSession->query("select * from session where store_id = {$storeId} and DATE(session_start) = '$thisSQLDate' and session_type='SPECIAL_EVENT' and session_type_subtype='WALK_IN' and is_deleted = 0");
		if ($thisSession->N > 0)
		{
			if (!$addOnly)
			{
				$thisSession->fetch();
				$oldSession = clone($thisSession);
				$thisSession->delivered_supports_delivery = "0";
				$thisSession->delivered_supports_shipping = "0";
				$thisSession->available_slots = 250;
				$thisSession->introductory_slots = 0;
				$thisSession->sneak_peak = 0;
				$thisSession->duration_minutes = 60 * 23;
				$thisSession->session_type = CSession::SPECIAL_EVENT;
				$thisSession->session_class = CSession::SPECIAL_EVENT;
				$thisSession->session_type_subtype = CSession::WALK_IN;
				$thisSession->session_publish_state = CSession::PUBLISHED;
				$thisSession->session_close_scheduling_meal_customization = null;
				$thisSession->session_details = $details;
				$thisSession->update($oldSession);
			}
		}
		else
		{
			$thisSession->store_id = $storeId;
			$thisSession->menu_id = $menu_id;
			$thisSession->available_slots = 250;
			$thisSession->introductory_slots = 0;
			$thisSession->sneak_peak = 0;
			$thisSession->duration_minutes = 60 * 23;
			$thisSession->session_type = CSession::SPECIAL_EVENT;
			$thisSession->session_class = CSession::SPECIAL_EVENT;
			$thisSession->session_type_subtype = CSession::WALK_IN;
			$thisSession->session_publish_state = CSession::PUBLISHED;
			$thisSession->session_start = $date->format("Y-m-d 00:00:01");
			$thisSession->session_close_scheduling = $date->format("Y-m-d 23:59:59");
			$thisSession->session_close_scheduling_meal_customization = null;
			$thisSession->delivered_supports_delivery = "0";
			$thisSession->delivered_supports_shipping = "0";
			$thisSession->session_title = 'Walk-in on ' . $thisSQLDate;
			$thisSession->session_details = $details;
			$thisSession->insert();
		}
	}

	function getSessionEnd($Store = false)
	{
		if (empty($Store))
		{
			$Store = DAO_CFactory::create('store');
			$Store->id = $this->store_id;
			$Store->find(true);
		}

		$sessionEnd = new DateTime($this->session_start, new DateTimeZone(CTimezones::zone_by_id($Store->timezone_id)));

		return $sessionEnd->date;
	}

	function sessionTypeToText()
	{
		if ($this->session_type == CSession::STANDARD && $this->isPrivate())
		{
			$this->session_type_true = CSession::PRIVATE_SESSION;
		}
		else if ($this->isMadeForYou() && $this->isDelivery())
		{
			$this->session_type_true = CSession::DELIVERY;
		}
		else if ($this->isMadeForYou() && $this->isRemotePickup())
		{
			if ($this->isPrivate())
			{
				$this->session_type_true = CSession::REMOTE_PICKUP_PRIVATE;
			}
			else
			{
				$this->session_type_true = CSession::REMOTE_PICKUP;
			}
		}
		else
		{
			$this->session_type_true = $this->session_type;
		}

		switch ($this->session_type_true)
		{
			case CSession::DELIVERY:
				return $this->session_type_desc = "Delivery";
			case CSession::REMOTE_PICKUP:
				return $this->session_type_desc = "Community Pick Up";
			case CSession::PRIVATE_SESSION:
				return $this->session_type_desc = "Standard - Private";
			case CSession::STANDARD:
				return $this->session_type_desc = "Standard";
			case CSession::SPECIAL_EVENT:
				return $this->session_type_desc = "Pick Up";
			case CSession::TODD:
				return $this->session_type_desc = "Taste of Dream Dinners";
			case CSession::DREAM_TASTE:
				return $this->session_type_desc = "Meal Prep Workshop";
			case CSession::WALK_IN:
				return $this->session_type_desc = "Walk-In";
			case CSession::FUNDRAISER:
				return $this->session_type_desc = "Fundraiser Event";
			default:
				return $this->session_type_desc = "Standard";
		}
	}

	// setup unique session type names for each type of session
	function getSessionTypeProperties()
	{
		if ($this->session_type == CSession::STANDARD && $this->isPrivate())
		{
			$session_type_switch = CSession::PRIVATE_SESSION;
		}
		else if ($this->isMadeForYou() && $this->isWalkIn())
		{
			$session_type_switch = CSession::WALK_IN;
		}
		else if ($this->isMadeForYou() && $this->isDelivery())
		{
			if ($this->isPrivate())
			{
				$session_type_switch = CSession::DELIVERY_PRIVATE;
			}
			else
			{
				$session_type_switch = CSession::DELIVERY;
			}
		}
		else if ($this->isMadeForYou() && ($this->isRemotePickup() || $this->session_type_subtype == CSession::REMOTE_PICKUP || $this->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE))
		{
			if ($this->isPrivate())
			{
				$session_type_switch = CSession::REMOTE_PICKUP_PRIVATE;
			}
			else
			{
				$session_type_switch = CSession::REMOTE_PICKUP;
			}
		}
		else
		{
			$session_type_switch = $this->session_type;
		}

		/* support for dao sub object style */
		if (isset($this->DAO_dream_taste_event_theme))
		{
			$this->dream_taste_theme_title_public = $this->DAO_dream_taste_event_theme->title_public;
			$this->dream_taste_theme_title = $this->DAO_dream_taste_event_theme->title;
			$this->dream_taste_theme_fadmin_acronym = $this->DAO_dream_taste_event_theme->fadmin_acronym;
		}

		if (!empty($this->dream_taste_theme_title_public))
		{
			$acronym = '';
			$words = preg_split("/\s+/", $this->dream_taste_theme_title_public);

			foreach ($words as $k => $v)
			{
				$acronym .= strtoupper(substr($v, 0, 1));
			}

			return array(
				$this->session_type_title = $this->dream_taste_theme_title,
				$this->session_type_title_public = $this->dream_taste_theme_title_public,
				$this->session_type_title_short = $acronym,
				$this->session_type_fadmin_acronym = $this->dream_taste_theme_fadmin_acronym,
				$this->session_type_string = strtolower(str_replace(' ', '_', $this->dream_taste_theme_title_public))
			);
		}

		switch ($session_type_switch)
		{
			case CSession::DELIVERY:
				return array(
					$this->session_type_title = "Home Delivery",
					$this->session_type_title_public = "Home Delivery - Delivery fee may apply",
					$this->session_type_title_short = "HD",
					$this->session_type_fadmin_acronym = "HD",
					$this->session_type_string = "delivery"
				);
				break;
			case CSession::WALK_IN:
				return array(
					$this->session_type_title = "Walk-In",
					$this->session_type_title_public = "Walk-In",
					$this->session_type_title_short = "WI",
					$this->session_type_fadmin_acronym = "WI",
					$this->session_type_string = "walk_in"
				);
				break;
			case CSession::DELIVERY_PRIVATE:
				return array(
					$this->session_type_title = "Home Delivery - Private",
					$this->session_type_title_public = "Home Delivery - Delivery fee may apply",
					$this->session_type_title_short = "HDP",
					$this->session_type_fadmin_acronym = "HDP",
					$this->session_type_string = "delivery_private"
				);
				break;
			case CSession::REMOTE_PICKUP:
				return array(
					$this->session_type_title = "Community Pick Up",
					$this->session_type_title_public = "Pick Up",
					$this->session_type_title_short = "CP",
					$this->session_type_fadmin_acronym = "CP",
					$this->session_type_string = "remote_pickup"
				);
				break;
			case CSession::REMOTE_PICKUP_PRIVATE:
				return array(
					$this->session_type_title = "Community Pick Up - Private",
					$this->session_type_title_public = "Pick Up",
					$this->session_type_title_short = "CPP",
					$this->session_type_fadmin_acronym = "CPP",
					$this->session_type_string = "remote_pickup_private"
				);
				break;
			case CSession::PRIVATE_SESSION:
				return array(
					$this->session_type_title = "Assembly - Private Party",
					$this->session_type_title_public = "Private Party",
					$this->session_type_title_short = "AP",
					$this->session_type_fadmin_acronym = "AP",
					$this->session_type_string = "private_party"
				);
				break;
			case CSession::STANDARD:
				return array(
					$this->session_type_title = "Assembly",
					$this->session_type_title_public = "Assemble at store",
					$this->session_type_title_short = "A",
					$this->session_type_fadmin_acronym = "A",
					$this->session_type_string = "standard"
				);
				break;
			case CSession::SPECIAL_EVENT:
				return array(
					$this->session_type_title = "Pick Up",
					$this->session_type_title_public = "Pick Up at store",
					$this->session_type_title_short = "P",
					$this->session_type_fadmin_acronym = "P",
					$this->session_type_string = "made_for_you"
				);
				break;
			case CSession::TODD:
				return array(
					$this->session_type_title = "Taste of Dream Dinners",
					$this->session_type_title_public = "Taste of Dream Dinners",
					$this->session_type_title_short = "TD",
					$this->session_type_fadmin_acronym = "TD",
					$this->session_type_string = "taste_of_dream_dinners"
				);
				break;
			case CSession::DREAM_TASTE:
				return array(
					$this->session_type_title = "Meal Prep Workshop",
					$this->session_type_title_public = "Meal Prep Workshop",
					$this->session_type_title_short = "MPW",
					$this->session_type_fadmin_acronym = "MPW",
					$this->session_type_string = "dream_taste"
				);
				break;
			case CSession::FUNDRAISER:
				return array(
					$this->session_type_title = "Fundraiser",
					$this->session_type_title_public = "Fundraiser",
					$this->session_type_title_short = "F",
					$this->session_type_fadmin_acronym = "F",
					$this->session_type_string = "fundraiser"
				);
				break;
			default:
				return array(
					$this->session_type_title = "Assembly",
					$this->session_type_title_public = "Assembly",
					$this->session_type_title_short = "A",
					$this->session_type_fadmin_acronym = "A",
					$this->session_type_string = "standard"
				);
				break;
		}
	}

	static function retreiveSessionLeadArray($store_id, $getFirstAndLast = false)
	{
		$userObj = DAO_CFactory::create('user');
		$userObj->query("select u.id, u.firstname, u.lastname, u.user_type from user u
			join user_to_store uts on uts.user_id = u.id and uts.store_id = $store_id and uts.is_deleted = 0
			where u.is_deleted = 0 and u.user_type in ('FRANCHISE_MANAGER', 'FRANCHISE_OWNER', 'FRANCHISE_LEAD', 'EVENT_COORDINATOR', 'OPS_LEAD', 'HOME_OFFICE_MANAGER')");

		$retval = array();

		$dupeDetector = array();

		if ($getFirstAndLast)
		{
			while ($userObj->fetch())
			{
				$retval[$userObj->id] = $userObj->firstname . " " . $userObj->lastname;
			}
		}
		else
		{
			while ($userObj->fetch())
			{

				if ($userObj->firstname != "Operations")
				{

					if (isset($dupeDetector[$userObj->firstname]))
					{
						$userObj->firstname .= ' ' . ucfirst(substr($userObj->lastname, 0, 1));
					}

					$dupeDetector[$userObj->firstname] = true;

					$retval[$userObj->id] = $userObj->firstname;
				}
			}
		}

		return $retval;
	}

	function getTasteEventProperties()
	{
		$this->dream_taste_sub_theme = null;
		$this->dream_taste_sub_sub_theme = null;
		$this->dream_taste_theme_string = null;
		$this->dream_taste_theme_string_default = null;
		$this->dream_taste_theme_title = null;
		$this->dream_taste_theme_title_public = null;
		$this->dream_taste_theme_fadmin_acronym = null;

		if ($this->session_type == CSession::DREAM_TASTE || $this->session_type == CSession::FUNDRAISER)
		{
			$prop = DAO_CFactory::create('session');
			$prop->query("SELECT
					dtp.id AS dream_taste_event_prop_id,
					dtp.host_required AS dream_taste_host_required,
					dtp.available_on_customer_site AS dream_taste_available_on_customer_site,
					dtp.password_required AS dream_taste_password_required,
					dtp.can_rsvp_only AS dream_taste_can_rsvp_only,
					dtp.can_rsvp_upgrade AS dream_taste_can_rsvp_upgrade,
					dtet.title AS dream_taste_theme_title,
					dtet.title_public AS dream_taste_theme_title_public,
					dtet.fadmin_acronym AS dream_taste_theme_fadmin_acronym,
					dtet.sub_theme AS dream_taste_sub_theme,
					dtet.sub_sub_theme AS dream_taste_sub_sub_theme,
					dtet.theme_string AS dream_taste_theme_string,
					CONCAT(SUBSTRING(dtet.theme_string,1,CHAR_LENGTH(dtet.theme_string)-7),'default') AS dream_taste_theme_string_default,
					f.fundraiser_name,
					f.fundraiser_description,
					f.donation_value AS fundraiser_donation_value
					FROM `session` AS s
					INNER JOIN store AS st ON s.store_id = st.id AND st.is_deleted = '0'
					LEFT JOIN `session_properties` AS sp ON sp.session_id = s.id AND sp.is_deleted = '0'
					LEFT JOIN store_to_fundraiser AS stf ON stf.id = sp.fundraiser_id AND stf.is_deleted = '0'
					LEFT JOIN fundraiser AS f ON f.id = stf.fundraiser_id AND f.is_deleted = '0'
					LEFT JOIN dream_taste_event_properties AS dtp ON dtp.menu_id = s.menu_id AND dtp.id = sp.dream_taste_event_id AND dtp.is_deleted = '0'
					LEFT JOIN dream_taste_event_theme AS dtet ON dtp.dream_taste_event_theme = dtet.id
					WHERE s.is_deleted = '0' AND s.id = '" . $this->id . "'");
			$prop->fetch();

			$this->dream_taste_sub_theme = $prop->dream_taste_sub_theme;
			$this->dream_taste_sub_sub_theme = $prop->dream_taste_sub_sub_theme;
			$this->dream_taste_theme_string = $prop->dream_taste_theme_string;
			$this->dream_taste_theme_string_default = $prop->dream_taste_theme_string_default;
			$this->dream_taste_theme_title = $prop->dream_taste_theme_title;
			$this->dream_taste_theme_title_public = $prop->dream_taste_theme_title_public;
			$this->dream_taste_theme_fadmin_acronym = $prop->dream_taste_theme_fadmin_acronym;
		}
	}

	function generateICSFile()
	{
		$config = array('unique_id' => 'dreamdinners.com');
		$vcalendar = new vcalendar($config);
		$vevent = $vcalendar->newComponent('vevent');

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $this->store_id;
		$storeObj->find(true);
		$storeStr = $storeObj->store_name . " @ " . $storeObj->address_line1 . " " . $storeObj->address_line2 . " " . $storeObj->city . " " . $storeObj->state_id . " " . $storeObj->postal_code;

		$startTS = strtotime($this->session_start);
		$vevent->setProperty('summary', 'Attend Dream Dinners Session');  // catagorize
		$vevent->setProperty('categories', 'FAMILY');  // catagorize
		$vevent->setProperty('dtstart', date('Y', $startTS), date('m', $startTS), date('d', $startTS), date('H', $startTS), date('i', $startTS), 00);  // 24 dec 2006 19.30
		$vevent->setProperty('duration', 0, 0, 2);                    // 3 hours
		$vevent->setProperty('description', 'Assemble meals at Dream Dinners');    // describe the event
		$vevent->setProperty('location', $storeStr);                // locate the event

		$filename = $vcalendar->getConfig('filename');
		$output = $vcalendar->createCalendar();
		$filesize = strlen($output);

		if ('xcal' == $vcalendar->format)
		{
			header('Content-Type: application/calendar+xml; charset=utf-8');
		}
		else
		{
			header('Content-Type: text/calendar; charset=utf-8');
		}

		header('Content-Length: ' . $filesize);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=10');
		header('Pragma: public');

		echo trim($output);
		exit;
	}

	function percentFull()
	{
		if ($this->available_slots == 0)
		{
			$percentFull = 1;
		}
		else
		{
			$percentFull = ($this->available_slots - $this->remaining_slots) / $this->available_slots;
		}

		$percentFull = (int)($percentFull * 100);
		if ($percentFull > 100)
		{
			$percentFull = 100;
		}
		if ($percentFull < 0)
		{
			$percentFull = 0;
		}

		return $percentFull;
	}

	function getRemainingSlots()
	{
		if (isset($this->remaining_slots))
		{
			if ($this->remaining_slots === null)
			{
				return $this->available_slots;
			}

			return $this->remaining_slots;
		}
		else
		{
			// remaining slots is a calculated field and is dependent on the query building the object. Bad! it's not apparent.
			// So if this is called on object not built from such a query then we must do the work here.

			$tempSessionObj = DAO_CFactory::create('session');

			$tempSessionObj->query("SELECT (available_slots - count(booking.id)) AS 'remaining_slots' FROM session LEFT JOIN booking ON booking.session_id = session.id  AND booking.status = 'ACTIVE' WHERE session.id = " . $this->id);

			$tempSessionObj->fetch();

			$this->remaining_slots = $tempSessionObj->remaining_slots;

			return $tempSessionObj->remaining_slots;
		}
	}

	function getRemainingIntroSlots()
	{
		if (isset($this->remaining_intro_slots))
		{
			if ($this->remaining_intro_slots === null)
			{
				return $this->introductory_slots;
			}

			return $this->remaining_intro_slots;
		}
		else
		{
			// remaining slots is a calculated field and is dependent on the query building the object. Bad! it's not apparent.
			// So if this is called on object not built from such a query then we must do the work here.

			// first figure out if there any intro slots left
			$tempSessionObj1 = DAO_CFactory::create('session');
			$tempSessionObj1->query("SELECT (introductory_slots - count(booking.id)) AS 'remaining_intro_slots' FROM session " . " LEFT JOIN booking ON  booking.session_id = session.id  AND booking.status = 'ACTIVE' AND booking.booking_type = 'INTRO' " . " WHERE session.id =" . $this->id);
			$tempSessionObj1->fetch();

			// then get the number of overall remaining slots
			$tempSessionObj2 = DAO_CFactory::create('session');
			$tempSessionObj2->query("SELECT introductory_slots, available_slots - count(booking.id) AS 'remaining_slots' FROM session " . " LEFT JOIN booking ON  booking.session_id = session.id  AND booking.status = 'ACTIVE' " . " WHERE session.id =" . $this->id);
			$tempSessionObj2->fetch();

			if ($tempSessionObj2->remaining_slots < $tempSessionObj1->remaining_intro_slots)
			{
				$tempSessionObj1->remaining_intro_slots = $tempSessionObj2->remaining_slots;
			}

			if ($tempSessionObj1->remaining_intro_slots < 0)
			{
				$tempSessionObj1->remaining_intro_slots = 0;
			}

			$this->remaining_intro_slots = $tempSessionObj1->remaining_intro_slots;

			return $this->remaining_intro_slots;
		}
	}

	/**
	 * Returns the session records for this menu
	 */
	function findCalendarRangeForMenu($store_id, $rangeStart, $rangeEnd, $menu_id)
	{
		return $this->query('SELECT session.*, menu_name, menu_start, (available_slots - count(booking.id)) AS "remaining_slots" FROM session ' . ' JOIN menu ON session.menu_id = menu.id ' . " LEFT JOIN booking ON  booking.session_id = session.id  AND booking.status = 'ACTIVE' " . ' WHERE session.is_deleted = 0 AND store_id = ' . $store_id . " AND menu_id = " . $menu_id . " AND  session.session_start >= '" . $rangeStart . "' AND  session.session_start <= '" . $rangeEnd . "' AND session.session_publish_state = 'PUBLISHED' " . ' AND menu.is_active = 1 GROUP BY session.id ORDER BY  session_start ASC');
	}

	/**
	 * Returns the session records for this month - used by new store expenses entry widget
	 */
	function findCalendarRangeForMonth($store_id, $month, $year)
	{
		return $this->query("SELECT session.* from session
						 where session.is_deleted = 0 and store_id = $store_id
						 and MONTH(session.session_start) = $month and YEAR(session.session_start) = $year and session.session_publish_state <> 'SAVED'
						 GROUP BY session.id ORDER BY session_start ASC");
	}

	/**
	 * Returns the session records for this menu, regardless of menu
	 */
	function findIntroCalendarRange($store_id, $rangeStart, $rangeEnd)
	{
		$cutOffMenu = SLOT_STEALING_CUTOFF_MENU;

		return $this->query("SELECT( if (menu_id <= $cutOffMenu,
											 introductory_slots - count(IF(booking.booking_type = 'INTRO', 1, NULL)),
											(introductory_slots -  (count(IF(booking.booking_type = 'INTRO', 1, NULL))
													 + if(count(IF(booking.booking_type = 'STANDARD', 1, NULL))  > available_slots - introductory_slots,
													count(IF(booking.booking_type = 'STANDARD', 1, NULL)) - (available_slots - introductory_slots) ,0)))) ) as 'remaining_slots' ,
									 menu_name, menu_start, session.* FROM session
									JOIN menu ON session.menu_id = menu.id
									LEFT JOIN booking ON  booking.session_id = session.id  and booking.status = 'ACTIVE'
									WHERE session.is_deleted = 0 AND
									session.store_id = $store_id AND session.session_start >= '$rangeStart' AND  session.session_start <= '$rangeEnd' AND
									session.session_publish_state = 'PUBLISHED'
									and menu.is_active = 1 and session.session_type = 'STANDARD'
									GROUP BY session.id ORDER BY  session_start ASC");
	}

	function findDirectOrderCalendarRange($store_id, $rangeStart, $rangeEnd)
	{


		return $this->query("SELECT 
			count(distinct sr.id) as num_rsvps, 
			available_slots - (count(distinct booking.id) + count(distinct sr.id)) AS 'remaining_slots',
			introductory_slots - ( count( IF ( booking.booking_type = 'INTRO', 1, NULL )) + IF ( count( IF ( booking.booking_type = 'STANDARD', 1, NULL )) > available_slots - introductory_slots, count( IF ( booking.booking_type = 'STANDARD', 1, NULL )) - ( available_slots - introductory_slots ), 0 )) AS 'remaining_intro_slots',
			session.*, 
			menu_name, 
			menu_start
			FROM session
			JOIN menu ON session.menu_id = menu.id
			LEFT JOIN booking ON booking.session_id = session .id AND booking. STATUS = 'ACTIVE'
 			LEFT JOIN session_rsvp sr ON sr.session_id = session.id AND sr.upgrade_booking_id IS NULL AND sr.is_deleted = 0
			WHERE session.is_deleted = 0 
			AND session.store_id = $store_id 
			AND session.session_start >= '$rangeStart' 
			AND session.session_start <= '$rangeEnd'
			AND session.session_publish_state <> 'SAVED' 
			GROUP BY session.id 
			ORDER BY session_start ASC");
	}

	/**
	 * Returns the session records for this menu, regardless of menu
	 */
	function findCalendarRangeForSessionMgr($store_id, $rangeStart, $rangeEnd)
	{
		return $this->query("SELECT
			count(distinct sr.id) as num_rsvps,
			available_slots - (count(distinct booking.id) + count(distinct sr.id)) as 'remaining_slots',
			introductory_slots - (count(IF(booking.booking_type = 'INTRO', 1, NULL)) + if(count(IF(booking.booking_type = 'STANDARD', 1, NULL)) > available_slots - introductory_slots, 
			count(IF(booking.booking_type = 'STANDARD', 1, NULL)) - (available_slots - introductory_slots) ,0)) as 'remaining_intro_slots',
			session.*, 
       		menu_name, menu_start,
			sp.store_pickup_location_id
			FROM session
			JOIN menu ON session.menu_id = menu.id
			LEFT JOIN booking ON  booking.session_id = session.id  and booking.status = 'ACTIVE'
			LEFT JOIN session_rsvp sr ON sr.session_id = session.id AND sr.upgrade_booking_id IS NULL AND sr.is_deleted = 0
			LEFT JOIN session_properties AS sp ON session.id = sp.session_id AND sp.is_deleted = 0
			WHERE session.is_deleted = 0 AND store_id = $store_id
			AND session.session_start >= '$rangeStart' 
			AND session.session_start <= '$rangeEnd'
			GROUP BY session.id 
			ORDER BY session_start ASC");
	}

	/**
	 * Returns the session records for this menu, regardless of menu
	 */
	function findCalendarRangeForDeliveredSessionMgr($store_id, $rangeStart, $rangeEnd)
	{
		return $this->query("SELECT
			count(distinct sr.id) as num_rsvps,
			available_slots - (count(distinct booking.id) + count(distinct sr.id)) as 'remaining_slots',
			introductory_slots - (count(IF(booking.booking_type = 'INTRO', 1, NULL)) + if(count(IF(booking.booking_type = 'STANDARD', 1, NULL)) > available_slots - introductory_slots, 
			count(IF(booking.booking_type = 'STANDARD', 1, NULL)) - (available_slots - introductory_slots) ,0)) as 'remaining_intro_slots',
			session.*, 
       		menu_name, menu_start,
			sp.store_pickup_location_id
			FROM session
			JOIN menu ON session.menu_id = menu.id
			LEFT JOIN booking ON  booking.session_id = session.id  and booking.status = 'ACTIVE'
			LEFT JOIN session_rsvp sr ON sr.session_id = session.id AND sr.upgrade_booking_id IS NULL AND sr.is_deleted = 0
			LEFT JOIN session_properties AS sp ON session.id = sp.session_id AND sp.is_deleted = 0
			WHERE session.is_deleted = 0 AND store_id = $store_id
			AND session.session_start >= '$rangeStart' 
			AND session.session_start <= '$rangeEnd'
			GROUP BY session.id 
			ORDER BY session_start ASC");
	}

	/**
	 * using session_start, store_id, and duration minutes determines whether there is an illegal overlap
	 * (Assembly Session overlapping another assembly session)
	 *
	 * @return boolean
	 */
	function doesTimeConflict($eventThemeAcronym = false)
	{
		// Only Assembly Sessions must be tested for over lap so unless the current session is Assnebly we can return false for no conflict.

		if ($this->session_type == CSession::SPECIAL_EVENT)
		{
			//This includes MFY Store Pickup; Remote Pickup and Home Delivery
			return false;
		}

		if ($this->session_type == CSession::DELIVERED)
		{
			//Shouldn't happen but let's cover it
			return false;
		}

		if (!empty($eventThemeAcronym) && in_array($eventThemeAcronym, array(
				'MPWC',
				'OHC',
				'FC'
			)))
		{
			return false;
		}

		// This session is in-store so now check for overlap with Assemble sessions

		$sessionStartTS = strtotime($this->session_start);
		$sessionEndTime = date("Y-m-d H:i:s", ($sessionStartTS + ($this->duration_minutes * 60)));

		$selfSearch = "";
		if ($this->id != null && $this->id != "" && $this->id > 0)
		{
			$selfSearch = 'AND s.id <> ' . $this->id;
		}

		$Session = new DAO();
		$Session->query("SELECT s.id, s.session_type, s.session_type_subtype, dtet.fadmin_acronym FROM session s
									LEFT JOIN session_properties sp on sp.session_id = s.id and sp.is_deleted = 0
									LEFT JOIN dream_taste_event_properties dtep on dtep.id = sp.dream_taste_event_id and dtep.is_deleted = 0
									LEFT JOIN dream_taste_event_theme dtet on dtet.id = dtep.dream_taste_event_theme# and fadmin_acronym not in 
									WHERE 
									(
										( '{$this->session_start}' < s.session_start AND '$sessionEndTime' > s.session_start ) 
											OR ('{$this->session_start}' >= s.session_start AND '{$this->session_start}' < DATE_ADD( s.session_start, INTERVAL s.duration_minutes MINUTE)
										)) 
									$selfSearch
									AND s.store_id = {$this->store_id}
									AND s.is_deleted = 0
									AND s.session_type <> 'SPECIAL_EVENT' AND (isnull(dtet.fadmin_acronym) OR dtet.fadmin_acronym not in ('MPWC', 'OHC', 'FC'))");

		return $Session->N == 0 ? false : true;
	}

	/**
	 * Determines whether a session is open or closed
	 * $storeObj can be just passed in as $storeObj->timezone_id
	 * @return boolean
	 */
	function isOpen($storeObj = false)
	{
		if (!is_object($storeObj))
		{
			//PHP 8 if (is_object($this->DAO_store ?? null))
			if (is_object($this->DAO_store))
			{
				$storeObj = $this->DAO_store;
			}
			else if (is_numeric($this->store_id))
			{
				$storeObj = DAO_CFactory::create('store');
				$storeObj->id = $this->store_id;
				$storeObj->find(true);
			}
			else
			{
				throw new Exception("No store object");
			}
		}

		$CloseoutDate = strtotime($this->session_close_scheduling);

		$adjustedTime = CTimezones::getAdjustedServerTime($storeObj);
		$storeObj->free();

		return $adjustedTime <= $CloseoutDate;
	}

	/**
	 * Determines whether a session is open or closed for Customization
	 * $storeObj can be just passed in as $storeObj->timezone_id
	 * @return boolean
	 */
	function isOpenForCustomization($storeObj = false)
	{
		if (!is_object($storeObj))
		{
			//PHP 8 if (is_object($this->DAO_store ?? null))
			if (is_object($this->DAO_store))
			{
				$storeObj = $this->DAO_store;
			}
			else if (is_numeric($this->store_id))
			{
				$storeObj = DAO_CFactory::create('store');
				$storeObj->id = $this->store_id;
				$storeObj->find(true);
			}
			else
			{
				throw new Exception("No store object");
			}
		}

		if (!$storeObj->supportsMealCustomization())
		{
			$storeObj->free();

			return false;
		}

		if (empty($this->session_close_scheduling_meal_customization) || $this->session_close_scheduling_meal_customization == 0)
		{
			$storeObj->free();

			return false;
		}

		if ($this->session_close_scheduling_meal_customization == $this->session_start)
		{
			$storeObj->free();

			return false;
		}

		$CloseoutDate = strtotime($this->session_close_scheduling_meal_customization);

		//TODO: temp until the bad 1969 dates are fixed
		if ($CloseoutDate < strtotime('2000-01-01 00:00:00'))
		{
			$storeObj->free();

			return false;
		}

		$adjustedTime = CTimezones::getAdjustedServerTime($storeObj);
		$storeObj->free();

		return $adjustedTime <= $CloseoutDate;
	}

	/**
	 * Determines whether a session ever allowed customization,
	 * even if now closed for customization
	 *
	 * @return boolean
	 */
	function allowedCustomization($storeObj)
	{

		if (!is_object($storeObj))
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->id = $this->store_id;
			$storeObj->find(true);
		}

		if (!$storeObj->supports_meal_customization)
		{
			return false;
		}

		//if the close time is set to 0 in the UI by a user, then the time in the database will match the start time
		//meaning the sessin does not allow customization
		if ($this->session_close_scheduling_meal_customization == $this->session_start)
		{
			return false;
		}

		$CloseoutDate = strtotime($this->session_close_scheduling_meal_customization);

		//TODO: temp until the bad 1969 dates are fixed
		if ($CloseoutDate < strtotime('2000-01-01 00:00:00'))
		{
			return false;
		}

		if ($this->session_close_scheduling_meal_customization > 0)
		{
			return true;
		}

		return false;
	}

	static function sendRSVPemail($SessionObj, $UserObj)
	{
		$StoreObj = DAO_CFactory::create('store');
		$StoreObj->id = $SessionObj->store_id;
		$StoreObj->find(true);

		require_once('CMail.inc');
		$Mail = new CMail();

		$HTMLcontents = CMail::mailMerge('session_rsvp.html.php', array(
			'session_info' => $SessionObj,
			'store_info' => $StoreObj,
			'user_info' => $UserObj
		));

		$Txtcontents = CMail::mailMerge('session_rsvp.txt.php', array(
			'session_info' => $SessionObj,
			'store_info' => $StoreObj,
			'user_info' => $UserObj
		));

		$Mail->send(null, null, $UserObj->firstname . ' ' . $UserObj->lastname, $UserObj->primary_email, 'RSVP Confirmation', $HTMLcontents, $Txtcontents);
	}

	static function createSessionRSVP($SessionObj, $UserObj, $sendConfirmationEmail = true)
	{
		if (is_numeric($SessionObj))
		{
			$session_id = $SessionObj;

			$SessionObj = DAO_CFactory::create('session');
			$SessionObj->id = $session_id;
			$SessionObj->find(true);
		}

		if (is_numeric($UserObj))
		{
			$user_id = $UserObj;

			$UserObj = DAO_CFactory::create('user');
			$UserObj->id = $user_id;
			$UserObj->find(true);
		}

		$SessionRSVP = DAO_CFactory::create('session_rsvp');
		$SessionRSVP->user_id = $UserObj->id;
		$SessionRSVP->session_id = $SessionObj->id;
		if (!$SessionRSVP->find(true))
		{
			$SessionRSVP->insert();

			if ($sendConfirmationEmail)
			{
				CSession::sendRSVPemail($SessionObj, $UserObj);
			}
		}

		return $SessionRSVP;
	}

	static function getSessionRSVP($session_id, $user_id)
	{
		$SessionRSVP = DAO_CFactory::create('session_rsvp');
		$SessionRSVP->user_id = $user_id;
		$SessionRSVP->session_id = $session_id;
		$SessionRSVP->upgrade_booking_id = 'NULL';

		if (!$SessionRSVP->find(true))
		{
			return false;
		}

		return $SessionRSVP;
	}

	static function deleteSessionRSVP($session_id, $user_id = false)
	{
		$SessionRSVP = DAO_CFactory::create('session_rsvp');
		if ($user_id)
		{
			$SessionRSVP->user_id = $user_id;
		}
		$SessionRSVP->session_id = $session_id;
		$SessionRSVP->find();

		while ($SessionRSVP->fetch())
		{
			$SessionRSVP->delete();
		}
	}

	static function upgradeSessionRSVP($session_id, $user_id, $booking_id)
	{
		$SessionRSVP = DAO_CFactory::create('session_rsvp');
		$SessionRSVP->user_id = $user_id;
		$SessionRSVP->session_id = $session_id;
		$SessionRSVP->upgrade_booking_id = 'NULL';

		if ($SessionRSVP->find(true))
		{
			$Org_SessionRSVP = clone $SessionRSVP;

			$SessionRSVP->upgrade_booking_id = $booking_id;
			$SessionRSVP->update($Org_SessionRSVP);
		}
	}

	function getSessionRSVPArray()
	{
		$session_rsvp = DAO_CFactory::create('session_rsvp');
		$session_rsvp->session_id = $this->id;
		$session_rsvp->upgrade_booking_id = 'NULL';
		$session_rsvp->find();

		$session_rsvp_array = array();

		while ($session_rsvp->fetch())
		{
			$user = DAO_CFactory::create('user');
			$user->id = $session_rsvp->user_id;

			if ($user->find(true))
			{
				$session_rsvp_array[$session_rsvp->id] = clone($session_rsvp);
				$session_rsvp_array[$session_rsvp->id]->user = clone($user);
			}
		}

		return $session_rsvp_array;
	}

	static function isAvailable(&$sessionData)
	{
		if (!empty($sessionData['percent_full']) && $sessionData['percent_full'] >= 100)
		{
			return false;
		}
		else if (!empty($sessionData['expired']) && $sessionData['expired'])
		{
			return false;
		}
		else if (!empty($sessionData['session_publish_state']) && $sessionData['session_publish_state'] != 'PUBLISHED')
		{
			return false;
		}

		return true;
	}

	function isStandardSessionValid($storeObj)
	{
		$isOpen = $this->isOpen($storeObj);
		$hasSpace = $this->getRemainingSlots() > 0;

		return $isOpen && $hasSpace;
	}

	function isIntroSessionValid($storeObj)
	{
		$isOpen = $this->isOpen($storeObj);
		$hasSpace = $this->getRemainingIntroSlots() > 0;

		return $isOpen && $hasSpace;
	}

	/**
	 * Determines whether a session is open or closed for scheduling (Note: this is for customer initiated rescheduling)
	 * you can reschedule to any session occurring from 5 days from today or later
	 * @return boolean
	 */
	function isOpenForRescheduling($storeObj)
	{

		$today = CTimezones::getAdjustedTime($storeObj, mktime(0, 0, 0, date("n"), date("j"), date("Y")));
		// midnight this morning
		$cutoff = $today + (86400 * 5);

		return strtotime($this->session_start) >= $cutoff;
	}

	/*
	 *   If the session to be rescheduled is in the last month and the current date is greater than the 6th then the session cannot be resceheduled
	 *
	 */
	function isReschedulingLockedOut($storeObj)
	{

		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->id = $this->menu_id;
		$MenuObj->find(true);

		return !$MenuObj->areSessionsOrdersEditable($storeObj);
	}

	function isInThePast($storeObj = false)
	{
		if (!$storeObj)
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select id, timezone_id from store where id = {$this->store_id}");
			$storeObj->fetch();
		}

		$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($storeObj->timezone_id);

		if (strtotime($this->session_start) <= $now)
		{
			return true;
		}

		return false;
	}

	// Assumes the session start time has been set
	function setCloseSchedulingTime($method, $customInterval = 0)
	{
		$asTS = strtotime($this->session_start);
		$newTS = 0;

		if ($method == self::ONE_FULL_DAY)
		{
			$asTS -= 86400; // set to same time  prior day

			// then get needed parts
			$Month = Date("n", $asTS);
			$Day = Date("j", $asTS);
			$Year = Date("Y", $asTS);

			// to contruct time stamp ignoring time
			$newTS = mktime(0, 0, 0, $Month, $Day, $Year);
		}
		else
		{
			$newTS = $asTS - (3600 * $customInterval); // subtract hours
		}

		$this->session_close_scheduling = date("Y-m-d H:i:s", $newTS);
	}

	// from close to start interval, figure out UI selection
	// that was used to build scheduling close time so
	// edit_session can display the original choice.
	function determineSessionCloseEnum()
	{
		$startTS = strtotime($this->session_start);
		$closeTS = strtotime($this->session_close_scheduling);

		$interval = $startTS - $closeTS;

		// is session close at midnight?
		$hour = Date("G", $closeTS);
		$minute = Date("i", $closeTS);

		if ($hour == 0 && $minute == 0 && $interval > 86400 && $interval < 172800)
			// if session close is at midnight of previous day then the "1_day" method was used
		{
			return self::ONE_FULL_DAY;
		}

		return self::HOURS;
	}

	function getScheduleCloseInterval()
	{
		$startTS = strtotime($this->session_start);
		$closeTS = strtotime($this->session_close_scheduling);
		$interval = $startTS - $closeTS;

		return $interval / 3600;
	}

	// Assumes the session start time has been set
	function setMealCustomizationCloseSchedulingTime($method, $customInterval = 0)
	{
		$asTS = strtotime($this->session_start);
		$newTS = 0;

		if ($method == self::ONE_FULL_DAY)
		{
			$asTS -= 86400; // set to same time  prior day

			// then get needed parts
			$Month = Date("n", $asTS);
			$Day = Date("j", $asTS);
			$Year = Date("Y", $asTS);

			// to contruct time stamp ignoring time
			$newTS = mktime(0, 0, 0, $Month, $Day, $Year);
		}
		if ($method == self::FOUR_FULL_DAYS)
		{
			$asTS -= (86400 * 4); // set to same time  prior day

			// then get needed parts
			$Month = Date("n", $asTS);
			$Day = Date("j", $asTS);
			$Year = Date("Y", $asTS);

			// to contruct time stamp ignoring time
			$newTS = mktime(0, 0, 0, $Month, $Day, $Year);
		}
		else
		{
			$newTS = $asTS - (3600 * $customInterval); // subtract hours
		}

		if (empty($newTS) || $newTS < strtotime('2000-01-01 00:00:00') || $customInterval == -1)
		{
			//session close should be null if can't be correctly calculated
			//probably only happen if sessin start is not populated
			$this->session_close_scheduling_meal_customization = null;
		}
		else
		{
			$this->session_close_scheduling_meal_customization = date("Y-m-d H:i:s", $newTS);
		}
	}

	function determineSessionMealCustomizationCloseEnum()
	{
		$startTS = strtotime($this->session_start);
		$closeTS = strtotime($this->session_close_scheduling_meal_customization);

		$interval = $startTS - $closeTS;

		// is session close at midnight?
		$hour = Date("G", $closeTS);
		$minute = Date("i", $closeTS);

		if ($hour == 0 && $minute == 0 && $interval > (86400 * 4) && $interval < (86400 * 5))
		{
			return self::FOUR_FULL_DAYS;
		}

		return self::HOURS;
	}

	function getScheduleMealCustomizationCloseInterval()
	{
		$startTS = strtotime($this->session_start);
		$closeTS = strtotime($this->session_close_scheduling_meal_customization);
		$interval = $startTS - $closeTS;

		return $interval / 3600;
	}

	/**
	 * @return This method will return all sessions from the current date on.
	 * Passin useMonthPrior == false to not include the previous month
	 * Find all sessions whether unpublished or published.. but not SAVED
	 * @author Lynn Hook
	 */
	function findSessions($store_id, $useMonthPrior = true)
	{
		$current_date_sql = date("Y-m-d 00:00:00");
		$this->selectAdd();
		$this->selectAdd('id');
		$this->selectAdd('session_start');
		$this->selectAdd('session_publish_state');
		$this->whereAdd("store_id = " . $store_id, 'AND');
		$this->whereAdd("session_publish_state != 'SAVED'", 'AND');
		if ($useMonthPrior)
		{
			$this->whereAdd("session_start >=  DATE_SUB('" . $current_date_sql . "', INTERVAL 1 MONTH)");
		}
		else
		{
			$this->whereAdd("session_start >= '" . $current_date_sql . "'");
		}

		$this->orderBy('session_start');

		return $this->find();
	}

	static function parseSessionArrayByMenu($sessionArray, $optionsArray = false)
	{
		$defaultOptionsArray = array(
			'filter_closed' => false,
			'filter_walkin' => false,
			'filter_unpublished' => true
		);

		if (!empty($optionsArray))
		{
			$optionsArray = array_merge($defaultOptionsArray, $optionsArray);
		}
		else
		{
			$optionsArray = $defaultOptionsArray;
		}

		$filteredArray = array();

		foreach ($sessionArray as $DAO_Session)
		{
			if ($optionsArray['filter_unpublished'])
			{
				if (!$DAO_Session->isPublished())
				{
					continue;
				}
			}

			if ($optionsArray['filter_closed'])
			{
				if (!$DAO_Session->isOpen())
				{
					continue;
				}
			}

			if ($optionsArray['filter_walkin'])
			{
				if ($DAO_Session->isWalkIn())
				{
					continue;
				}
			}

			$filteredArray['sessions'][$DAO_Session->id] = $DAO_Session->id;
			$filteredArray['menu_to_session'][$DAO_Session->menu_id][$DAO_Session->id] = $DAO_Session->id;

			if (!isset($filteredArray['menu'][$DAO_Session->menu_id]['DAO_menu']))
			{
				$filteredArray['menu'][$DAO_Session->menu_id]['DAO_menu'] = $DAO_Session->DAO_menu;
			}

			// per menu session type counts
			if (!isset($filteredArray['menu'][$DAO_Session->menu_id]['session_type']))
			{
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'] = array(
					CSession::STANDARD => 0,
					CSession::MADE_FOR_YOU => 0,
					CSession::DELIVERY => 0,
					CSession::DELIVERED => 0,
					CSession::REMOTE_PICKUP => 0,
					CSession::INTRO => 0,
					CSession::EVENT => 0,
					CSession::ALL_STANDARD => 0
				);
			}

			// total session type counts
			if (!isset($filteredArray['info']['session_type']))
			{
				$filteredArray['info']['session_type'] = array(
					CSession::STANDARD => 0,
					CSession::MADE_FOR_YOU => 0,
					CSession::DELIVERY => 0,
					CSession::DELIVERED => 0,
					CSession::REMOTE_PICKUP => 0,
					CSession::INTRO => 0,
					CSession::EVENT => 0,
					CSession::ALL_STANDARD => 0
				);
			}

			if ($DAO_Session->isStandard())
			{
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::STANDARD] += 1;
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::ALL_STANDARD] += 1;
				$filteredArray['info']['session_type'][CSession::STANDARD] += 1;
				$filteredArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
			}

			if ($DAO_Session->isMadeForYou() && !$DAO_Session->isDelivery())
			{
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::MADE_FOR_YOU] += 1;
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::ALL_STANDARD] += 1;
				$filteredArray['info']['session_type'][CSession::MADE_FOR_YOU] += 1;
				$filteredArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
			}

			if ($DAO_Session->isMadeForYou() && $DAO_Session->isDelivery())
			{
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::DELIVERY] += 1;
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::ALL_STANDARD] += 1;
				$filteredArray['info']['session_type'][CSession::DELIVERY] += 1;
				$filteredArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
			}

			if ($DAO_Session->isMadeForYou() && $DAO_Session->isRemotePickup())
			{
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::REMOTE_PICKUP] += 1;
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::ALL_STANDARD] += 1;
				$filteredArray['info']['session_type'][CSession::REMOTE_PICKUP] += 1;
				$filteredArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
			}

			if ($DAO_Session->DAO_store->storeSupportsIntroOrders($DAO_Session->menu_id))
			{
				if (!empty($DAO_Session->remaining_intro_slots) && ($DAO_Session->isMadeForYou() || $DAO_Session->isStandard()))
				{
					$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::INTRO] += 1;
					$filteredArray['info']['session_type'][CSession::INTRO] += 1;
				}
			}

			if ($DAO_Session->isDreamTaste() || $DAO_Session->isFundraiser() || $DAO_Session->isStandardPrivate())
			{
				$filteredArray['menu'][$DAO_Session->menu_id]['session_type'][CSession::EVENT] += 1;
				$filteredArray['info']['session_type'][CSession::EVENT] += 1;
			}
		}

		return $filteredArray;
	}

	/**
	 * Find all session for a given menu or array of menus and return it as an array
	 * Contains a sub array containing all sessions and a filtered sub array
	 * filtered by given rules
	 */
	function getSessionArrayByMenu($optionsArray = false)
	{
		$defaultOptionsArray = array(
			'menu_id_array' => false,
			'published_only' => false,
			'exclude_closed' => false,
			// group by 'EntreeID'
			'groupBy' => 'session_id',
			// order by 'FeaturedFirst', 'NameAZ'
			'orderBy' => 'session_start',
			'limit' => false
		);

		if (!empty($optionsArray))
		{
			$optionsArray = array_merge($defaultOptionsArray, $optionsArray);
		}
		else
		{
			$optionsArray = $defaultOptionsArray;
		}

		$this->findSessionsByMenu($optionsArray);

		$sessionFindArray = array();

		while ($this->fetch())
		{
			$sessionFindArray[$this->id] = $this->cloneObj();
		}

		return $sessionFindArray;
	}

	/**
	 * Find all session for a given menu or array of menus, no filtering
	 */
	function findSessionsByMenu($optionsArray = false)
	{
		$defaultOptionsArray = array(
			'menu_id_array' => false,
			'exclude_walk_in' => false,
			'published_only' => false,
			'active_only' => false,
			// group by 'EntreeID'
			'groupBy' => 'session_id',
			// order by 'FeaturedFirst', 'NameAZ'
			'orderBy' => 'session_start',
			'limit' => false
		);

		if (!empty($optionsArray))
		{
			$optionsArray = array_merge($defaultOptionsArray, $optionsArray);
		}
		else
		{
			$optionsArray = $defaultOptionsArray;
		}

		$this->selectAdd();
		$this->selectAdd("session.*");
		$this->selectAdd("COUNT(DISTINCT session_rsvp.id) AS num_rsvps");
		$this->selectAdd("(available_slots - (COUNT(DISTINCT booking.id) + COUNT(DISTINCT session_rsvp.id))) AS remaining_slots");
		$this->selectAdd("introductory_slots - (COUNT(IF(booking.booking_type = 'INTRO', 1, NULL)) + IF(COUNT(IF(booking.booking_type = 'STANDARD', 1, NULL)) > available_slots - introductory_slots, COUNT(IF(booking.booking_type = 'STANDARD', 1, NULL)) - (available_slots - introductory_slots), 0 )) AS 'remaining_intro_slots'");
		$this->selectAdd("menu.menu_name");
		$this->selectAdd("menu.menu_start");
		$this->selectAdd("COUNT(DISTINCT booking.id) + COUNT(DISTINCT session_rsvp.id) AS booked_count");
		$this->selectAdd("GROUP_CONCAT(booking.id) AS ids_booking");
		$this->selectAdd("GROUP_CONCAT(session_rsvp.id) AS ids_session_rsvp");

		if ($optionsArray['menu_id_array'] && is_array($optionsArray['menu_id_array']))
		{
			if (count($optionsArray['menu_id_array']) == count($optionsArray['menu_id_array'], COUNT_RECURSIVE))
			{
				$menu_ids = implode("','", $optionsArray['menu_id_array']);
			}
			else
			{
				$menu_id_array = array_keys($optionsArray['menu_id_array']);

				$menu_ids = implode("','", $menu_id_array);
			}

			$this->whereAdd("session.menu_id IN('" . $menu_ids . "')");
		}

		$DAO_booking = DAO_CFactory::create('booking');
		$DAO_booking->status = CBooking::ACTIVE;

		$DAO_session_rsvp = DAO_CFactory::create('session_rsvp');
		$DAO_session_rsvp->upgrade_booking_id = 'NULL';

		$DAO_dream_taste_event_properties = DAO_CFactory::create('dream_taste_event_properties');
		$DAO_dream_taste_event_properties->whereAdd("dream_taste_event_properties.menu_id = session.menu_id");
		$DAO_dream_taste_event_properties->joinAddWhereAsOn(DAO_CFactory::create('dream_taste_event_theme'), 'LEFT');
		$DAO_dream_taste_event_properties->joinAddWhereAsOn(DAO_CFactory::create('bundle'), 'LEFT');

		$DAO_store_to_fundraiser = DAO_CFactory::create('store_to_fundraiser');
		$DAO_store_to_fundraiser->joinAddWhereAsOn(DAO_CFactory::create('fundraiser'), 'LEFT');

		$DAO_session_properties = DAO_CFactory::create('session_properties');
		$DAO_session_properties->joinAddWhereAsOn($DAO_store_to_fundraiser, 'LEFT');
		$DAO_session_properties->joinAddWhereAsOn($DAO_dream_taste_event_properties, 'LEFT');

		$this->joinAddWhereAsOn(DAO_CFactory::create('menu'));
		$this->joinAddWhereAsOn(DAO_CFactory::create('store'));
		$this->joinAddWhereAsOn(DAO_CFactory::create('session_discount'), 'LEFT');
		$this->joinAddWhereAsOn($DAO_session_properties, 'LEFT');
		$this->joinAddWhereAsOn($DAO_booking, 'LEFT', false, false, false);
		$this->joinAddWhereAsOn($DAO_session_rsvp, 'LEFT', false, false, false);

		if ($optionsArray['exclude_walk_in'])
		{
			$this->whereAdd("session.session_type_subtype != '" . CSession::WALK_IN . "' OR session.session_type_subtype IS NULL");
		}

		if ($optionsArray['published_only'])
		{
			$this->whereAdd("session.session_publish_state = '" . CSession::PUBLISHED . "'");
		}

		if ($optionsArray['active_only'])
		{
			$this->whereAdd("menu.is_active = 1");
		}

		if ($optionsArray['groupBy'] == 'session_id')
		{
			$this->groupBy("session.id");
		}

		if ($optionsArray['orderBy'] == 'session_start')
		{
			$this->orderBy("session.menu_id ASC, session.session_start ASC");
		}

		if ($optionsArray['limit'] !== false)
		{
			$this->limit($optionsArray['limit']);
		}

		return $this->find();
	}

	/**
	 * @return Method will return all sessions for a given store on a day, month and year.
	 * @author Lynn Hook
	 * Find all sessions whether unpublished or published.. but not SAVED
	 */

	function findDailySessions($store_id, $Day, $Month, $Year)
	{
		$current_date = mktime(0, 0, 0, $Month, $Day, $Year);
		$current_date_sql = date("Y-m-d 00:00:00", $current_date);

		$this->selectAdd();
		$this->selectAdd('id');
		$this->selectAdd('session_start');
		$this->selectAdd('session_publish_state');
		$this->whereAdd("store_id = " . $store_id, 'AND');
		$this->whereAdd("session_publish_state != 'SAVED'", 'AND');
		$this->whereAdd("session_start >= '" . $current_date_sql . "'", 'AND');
		$this->whereAdd("session_start <=  DATE_ADD('" . $current_date_sql . "', INTERVAL 1 DAY)");

		$this->orderBy('session_start');

		return $this->find();
	}

	function has_bookings()
	{
		$bookings = DAO_CFactory::create('booking');
		$bookings->session_id = $this->id;
		$bookings->status = CBooking::ACTIVE;

		if ($bookings->find())
		{
			return true;
		}

		return false;
	}

	function get_num_bookings()
	{

		$bookings = DAO_CFactory::create('booking');

		$bookings->whereAdd("session_id = $this->id and status = 'ACTIVE'");

		$bookings->selectAdd();
		$bookings->selectAdd("id");

		return $bookings->find();
	}

	function get_RSVP_count($excludeGuest = false)
	{
		$RSVPs = DAO_CFactory::create('session_rsvp');
		$RSVPs->session_id = $this->id;
		$RSVPs->upgrade_booking_id = 'NULL';

		if ($excludeGuest && is_numeric($excludeGuest))
		{
			$RSVPs->whereAdd("session_rsvp.user_id <> $excludeGuest");
		}

		$RSVPs->joinAdd(DAO_CFactory::create('user'), array('useWhereAsOn' => true));

		$RSVPs->find();

		return $RSVPs->N;
	}

	static function prepareSessionDetailsForDisplay(&$notes, $preview = false)
	{
		$testStr = strtolower($notes);
		$hasNoHTMLBreakOrParagraph = strrpos($testStr, "<br />") === false && strrpos($testStr, "<p>") === false;
		$notes = strip_tags($notes, "<p><br /><i><u><b>");

		if ($hasNoHTMLBreakOrParagraph)
		{
			$notes = str_replace("\r\n", '<br />', $notes);
		}
		else
		{
			$notes = str_replace("\r\n", '', $notes);
		}

		// for preview we need to further escape quotes
		if ($preview)
		{
			$notes = str_replace(array(
				"'",
				"\""
			), array(
				"\\'",
				'\\"'
			), $notes);
		}

		//$notes = htmlentities($notes, ENT_QUOTES);
		$notes = htmlentities($notes, ENT_QUOTES);
	}

	function isMadeForYou()
	{
		if ($this->session_type == CSession::MADE_FOR_YOU)
		{
			return true;
		}

		return false;
	}

	function isDelivery()
	{
		if ($this->isMadeForYou())
		{
			if (!empty($this->session_type_subtype) && ($this->session_type_subtype == CSession::DELIVERY || $this->session_type_subtype == CSession::DELIVERY_PRIVATE))
			{
				return true;
			}
		}

		return false;
	}

	function isDelivered()
	{

		if (!empty($this->session_type) && $this->session_type === CSession::DELIVERED)
		{
			return true;
		}

		return false;
	}

	function isRemotePickup()
	{
		if ($this->isRemotePickupPublic() || $this->isRemotePickupPrivate())
		{
			return true;
		}

		return false;
	}

	function isRemotePickupPublic()
	{
		//I don't see anywhere where store_pickup_location_id get set on the Session object so this is never true
		//		if ($this->isMadeForYou() && !empty($this->store_pickup_location_id) && $this->session_type_subtype == CSession::REMOTE_PICKUP)
		//		{
		//			return true;
		//		}

		if ($this->isMadeForYou() && $this->session_type_subtype == CSession::REMOTE_PICKUP)
		{
			return true;
		}

		return false;
	}

	function isRemotePickupPrivate()
	{
		//I don't see anywhere where store_pickup_location_id get set on the Session object so this is never true

		//		if ($this->isMadeForYou() && $this->isPrivate() && !empty($this->store_pickup_location_id) && $this->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE)
		//		{
		//			return true;
		//		}

		if ($this->isMadeForYou() && $this->isPrivate() && $this->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE)
		{
			return true;
		}

		return false;
	}

	function isOpenHouse()
	{
		$this->getSessionProperties();

		if ($this->session_type == CSession::DREAM_TASTE)
		{
			return true;
		}

		return false;
	}

	function isDreamTaste()
	{
		if ($this->session_type == CSession::DREAM_TASTE)
		{
			return true;
		}

		return false;
	}

	function isFundraiser()
	{
		if ($this->session_type == CSession::FUNDRAISER)
		{
			return true;
		}

		return false;
	}

	function isFriendsNightOut()
	{
		if (empty($this->id))
		{
			return false;
		}

		$tmpObj = new DAO();
		$tmpObj->query("select * from session_properties sp
                    	    join dream_taste_event_properties dtsp on dtsp.id = sp.dream_taste_event_id and can_rsvp_only = 1
                    	    where sp.session_id = {$this->id}");

		return ($tmpObj->N == 1);
	}

	function isStandard()
	{
		return ($this->session_type == CSession::STANDARD && empty($this->session_password));
	}

	function isStandardPrivate()
	{
		return ($this->session_type == CSession::STANDARD && !empty($this->session_password));
	}

	function isStandardMadeForYou()
	{
		if ($this->session_type == CSession::MADE_FOR_YOU && empty($this->session_type_subtype))
		{
			return true;
		}

		return false;
	}

	function isDiscounted()
	{
		return (!empty($this->session_discount_id));
	}

	function isPrivate()
	{
		return (!empty($this->session_password));
	}

	function isPublished()
	{
		return ($this->session_publish_state == CSession::PUBLISHED);
	}

	function isWalkIn()
	{
		$isWalkin = (!empty($this->session_type_subtype) && $this->session_type_subtype == CSession::WALK_IN);

		return ($this->isMadeForYou() && $isWalkin);
	}

	function isQuickSix()
	{
		return $this->session_type == self::QUICKSIX;
	}

	function sessionNoteType()
	{
		if ($this->session_type == CSession::STANDARD && $this->isPrivate())
		{
			return CSession::PRIVATE_SESSION;
		}
		else
		{
			return $this->session_type;
		}
	}

	static function getFCStartEnd($start, $start_anchor, $end, $end_anchor)
	{
		$true_start = $start_anchor;

		if ($start < $start_anchor)
		{
			$true_start = $start;
		}

		$true_end = date('Y-m-d', strtotime('last day of this month', strtotime($end_anchor)));

		if ($end > $true_end)
		{
			$true_end = $end;
		}

		return array(
			'start' => $true_start,
			'end' => $true_end
		);
	}

	// session info array for https://fullcalendar.io
	static function getSessionsForFullCalendarCustomer($StoreObj, $excludeFull = true)
	{
		$activeMenus = CMenu::getActiveMenuArray();
		$activeMenuIDs = array_keys($activeMenus);

		$sessions = CSession::getMonthlySessionInfoArray($StoreObj, false, $activeMenuIDs, false, true, true, false, false, false, $excludeFull, true);

		$startEnd = self::getFCStartEnd($activeMenus[min(array_keys($activeMenus))]['global_menu_start_date'], $activeMenus[min(array_keys($activeMenus))]['menu_start'], $activeMenus[max(array_keys($activeMenus))]['global_menu_end_date'], $activeMenus[max(array_keys($activeMenus))]['menu_start']);

		$sessionArray = array(
			'start' => $startEnd['start'],
			'end' => $startEnd['end'],
			'sessions' => array(),
			'info' => array(
				'session_type' => array(
					CSession::STANDARD => 0,
					CSession::MADE_FOR_YOU => 0,
					CSession::DELIVERY => 0,
					CSession::REMOTE_PICKUP => 0,
					CSession::INTRO => 0,
					CSession::EVENT => 0,
					CSession::ALL_STANDARD => 0
				),
				'has_meal_customization_sessions' => $sessions['info']['has_meal_customization_sessions']
			)
		);

		foreach ($sessions['sessions'] as $day)
		{
			if (!empty($day['sessions']))
			{
				if (!isset($activeMenus[$day['info']['menu_id']]['session_count']))
				{
					$activeMenus[$day['info']['menu_id']]['session_count'] = 0;
				}

				$activeMenus[$day['info']['menu_id']]['session_count'] += $day['info']['session_count'];

				foreach ($day['sessions'] as $session)
				{
					$remote_pickup_address = false;

					if ($session['session_type_subtype'] == CSession::REMOTE_PICKUP || $session['session_type_subtype'] == CSession::REMOTE_PICKUP_PRIVATE)
					{
						$remote_pickup_address = $session['session_remote_location']->address_line1 . (!empty($session['session_remote_location']->address_line2) ? ' ' . $session['session_remote_location']->address_line2 : '') . ', ' . $session['session_remote_location']->city . ', ' . $session['session_remote_location']->state_id . ' ' . $session['session_remote_location']->postal_code;
					}

					if (!$StoreObj->storeSupportsIntroOrders($session['menu_id']))
					{
						$session['introductory_slots'] = 0;
						$session['remaining_intro_slots'] = 0;
					}

					$sessionArray['sessions'][] = array(
						'title' => $session['session_type_title_public'],
						'start' => $session['session_start'],
						'end' => $session['session_end'],
						'className' => 'fc-event-session-' . $session['session_type_string'],
						'extendedProps' => array(
							'eventType' => 'session',
							'id' => $session['id'],
							'menu_id' => $session['menu_id'],
							'session_title' => $session['session_title'],
							'session_remote_pickup' => $remote_pickup_address,
							'session_type_true' => $session['session_type_true'],
							'session_type_title_short' => $session['session_type_title_short'],
							'session_host_informal_name' => (!empty($session['session_host_informal_name']) ? $session['session_host_informal_name'] : false),
							'session_has_password' => (!empty($session['session_password']) ? true : false),
							//'session_password' => $session['session_password'], // publicly visible in store calendar page source
							'fundraiser_name' => (!empty($session['fundraiser_name']) ? $session['fundraiser_name'] : false),
							'remaining_slots' => $session['remaining_slots'],
							'remaining_intro_slots' => $session['remaining_intro_slots'],
							'is_open_for_customization' => $session['is_open_for_customization']
						)
					);

					if ($session['session_type'] == CSession::STANDARD && empty($session['session_password']))
					{
						$sessionArray['info']['session_type'][CSession::STANDARD] += 1;
						$sessionArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($session['session_type'] == CSession::MADE_FOR_YOU && $session['session_type_subtype'] != CSession::DELIVERY)
					{
						$sessionArray['info']['session_type'][CSession::MADE_FOR_YOU] += 1;
						$sessionArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($session['session_type'] == CSession::MADE_FOR_YOU && $session['session_type_subtype'] == CSession::DELIVERY)
					{
						$sessionArray['info']['session_type'][CSession::DELIVERY] += 1;
						$sessionArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($session['session_type'] == CSession::MADE_FOR_YOU && $session['session_type_subtype'] == CSession::DELIVERY_PRIVATE)
					{
						$sessionArray['info']['session_type'][CSession::DELIVERY_PRIVATE] += 1;
						$sessionArray['info']['session_type'][CSession::EVENT] += 1;
					}

					if ($session['session_type'] == CSession::MADE_FOR_YOU && $session['session_type_subtype'] == CSession::REMOTE_PICKUP)
					{
						$sessionArray['info']['session_type'][CSession::REMOTE_PICKUP] += 1;
						$sessionArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($session['session_type'] == CSession::MADE_FOR_YOU && $session['session_type_subtype'] == CSession::REMOTE_PICKUP_PRIVATE)
					{
						$sessionArray['info']['session_type'][CSession::REMOTE_PICKUP_PRIVATE] += 1;
						$sessionArray['info']['session_type'][CSession::EVENT] += 1;
					}

					if ($StoreObj->storeSupportsIntroOrders($session['menu_id']))
					{
						if (!empty($session['remaining_intro_slots']) && ($session['session_type'] == CSession::MADE_FOR_YOU || $session['session_type'] == CSession::STANDARD))
						{
							$sessionArray['info']['session_type'][CSession::INTRO] += 1;
						}
					}

					if ($session['session_type'] == CSession::DREAM_TASTE || $session['session_type'] == CSession::FUNDRAISER || ($session['session_type'] == CSession::STANDARD && !empty($session['session_password'])))
					{
						$sessionArray['info']['session_type'][CSession::EVENT] += 1;
					}
				}
			}
		}

		return $sessionArray;
	}

	function getSessionProperties()
	{
		if (empty($this->session_properties))
		{
			$sessionprop = DAO_CFactory::create('session_properties');
			$sessionprop->session = $this->id;
			$sessionprop->find();

			$this->session_properties = clone($sessionprop);

			if (!empty($sessionprop->session_host))
			{
				$sessionhost = DAO_CFactory::create('user');
				$sessionhost->id = $sessionprop->session_host;
				$sessionhost->find();

				$this->session_properties->session_host_info = clone($sessionhost);
			}
		}
	}

	function findSessionsEligibleForOrdering($storeObj, $serviceDays, $max_returned = 6)
	{
		$orgServiceDays = $serviceDays;
		$deliveryDayFilter = $serviceDays - 1;
		$serviceDays++;  // TODO: Is there a threshhold prior to which today can be considered the first day?
		$todayTS = CTimezones::getAdjustedTime($storeObj, time());
		$today = new DateTime(date("Y-m-d H:i:s", $todayTS));
		$today->modify("+$serviceDays days");
		$earliestDeliveryDate = $today->format("Y-m-d");

		//$this->query("select * from session where store_id = {$storeObj->id} and DATE(session_start) >= '$earliestDeliveryDate' and delivered_supports_delivery > $deliveryDayFilter and is_deleted = 0 order by session_start limit $max_returned");
		$this->query("select 
			`session`.*,
			(`session`.available_slots - count(booking.id)) AS 'remaining_slots'
			from (select * from `session` where `session`.store_id = " . $storeObj->id . " and DATE(`session`.session_start) >= '" . $earliestDeliveryDate . "' and `session`.delivered_supports_delivery > '" . $deliveryDayFilter . "' and `session`.is_deleted = 0 order by `session`.session_start limit 20) as `session`
			join `session` as session_2 on session_2.session_start = DATE_SUB(`session`.session_start, INTERVAL " . $orgServiceDays . " DAY) and session_2.store_id = `session`.store_id and session_2.is_deleted = 0 and session_2.delivered_supports_shipping > 0
			LEFT JOIN booking ON booking.session_id = `session`.id  AND booking.status = 'ACTIVE' and booking.is_deleted = 0
			group by `session`.id
			order by `session`.session_start limit " .$max_returned);
	}

	static function isSessionValidForDeliveredOrder($session_id, $StoreObj, $menu_id, $serviceDays = false, $zip = false)
	{

		if (!empty($zip) && (empty($serviceDays) || !is_numeric($serviceDays)))
		{
			$serviceDaysRetriever = new DAO();
			$serviceDaysRetriever->query("select service_days from zipcodes where zip = '$zip' limit 1");
			$serviceDaysRetriever->fetch();
			$serviceDays = $serviceDaysRetriever->service_days;
			if (empty($serviceDays))
			{
				$serviceDays = false;
			}
		}

		if (!empty($serviceDays) && is_numeric($serviceDays))
		{
			$sessionsArray = CSession::getCurrentDeliveredSessionArrayForCustomer($StoreObj, $serviceDays, false, $menu_id);

			foreach ($sessionsArray['sessions'] as $date => $data)
			{
				foreach ($data['sessions'] as $id => $data)
				{
					if ($id == $session_id)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	static function getCurrentDeliveredSessionArrayForCustomer($Store, $service_days = 0, $date = false, $menu_id = false, $open_only = true, $get_bookings = false, $excludeFull = false)
	{
		return self::getMonthlySessionInfoArrayForDelivered($Store, $date, $menu_id, false, $open_only, $get_bookings, false, $excludeFull, $service_days, 6);
	}

	static function getCurrentDeliveredSessionArrayForDistributionCenter($Store, $service_days = 0, $date = false, $menu_id = false, $open_only = false, $get_bookings = false, $excludeFull = false)
	{
		return self::getMonthlySessionInfoArrayForDelivered($Store, $date, $menu_id, false, $open_only, $get_bookings, false, $excludeFull, $service_days, 20);
	}

	static function getMonthlySessionInfoArrayForDelivered($Store, $date = false, $menu_id = false, $cart_info = false, $open_only = false, $get_bookings = false, $date_is_anchor = false, $excludeFull = false, $customer_view = false, $max_returned = 6)
	{

		$Sessions = DAO_CFactory::create('session', true);
		$Sessions->store_id = $Store->id;

		if (!$date)
		{
			$date = time();
		}

		if ($date_is_anchor)
		{
			$curMenuArray = CMenu::getMenuByAnchorDate(date("Y-m-d", $date));
		}
		else
		{
			$curMenuArray = CMenu::getMenuByDate(date("Y-m-d", $date));
		}

		$menuMonth = $curMenuArray['menu_start'];
		$curMonthStartTS = strtotime($menuMonth);
		$sessionInfoArray = array(
			'sessions' => array(),
			'info' => array(
				'session_type' => array(
					CSession::STANDARD => 0,
					CSession::MADE_FOR_YOU => 0,
					CSession::DELIVERY => 0,
					CSession::REMOTE_PICKUP => 0,
					CSession::INTRO => 0,
					CSession::EVENT => 0,
					CSession::ALL_STANDARD => 0,
					CSession::DELIVERED => 0
				)
			)
		);

		if ($customer_view)
		{
			$sessionInfoArray['sessions'] = array();

			// if not false then customer_view is the the number service days for delivery
			$Sessions->findSessionsEligibleForOrdering($Store, $customer_view, 20);
		}
		else if (!empty($menu_id))
		{
			$sessionInfoArray['sessions'] = array();

			if (is_array($menu_id))
			{
				$optionsArray['menu_id_array'] = $menu_id;
			}
			else
			{
				$optionsArray = false;
				$Sessions->menu_id = $menu_id;
			}

			$Sessions->findSessionsByMenu($optionsArray);
		}
		else
		{
			list($rangeStart, $rangeEnd) = CCalendar::calculateExpandedMonthRange($curMonthStartTS);
			$Sessions->findSessionByCalendarRange($Store->id, $rangeStart, $rangeEnd);
			$sessionInfoArray['sessions'] = self::getDatesFromRange($rangeStart, $rangeEnd);
		}

		$sessionsIDArray = array();
		$count = 0;

		while ($Sessions->fetch())
		{
			// sessions with open slots only
			if ($excludeFull && $Sessions->getRemainingSlots() <= 0)
			{
				continue;
			}

			// open sessions only
			if ($open_only)
			{
				if (!$Sessions->isOpen($Store)) // this strictly checks for whether or not we have passed the lockout time for a store (usually 24 hours prior to session start) ...
				{
					continue;
				}

				// ... so also check for a session_publish_state of PUBLISHED or SAVED
				if ($Sessions->session_publish_state != 'SAVED' && $Sessions->session_publish_state != 'PUBLISHED')
				{
					continue;
				}
			}

			$date = date('Y-m-d', strtotime($Sessions->session_start));
			$sessionInfoArray['sessions'][$date]['sessions'][$Sessions->id] = $Sessions->id;
			$sessionsIDArray[$Sessions->id] = $Sessions->id;

			if($customer_view && ++$count == $max_returned)
			{
				break;
			}
		}

		// query to get each session details
		$sessionsIDs = implode(',', $sessionsIDArray);
		$sessionDetailsArray = self::getDeliveredSessionDetailArray($sessionsIDs, $get_bookings);

		// put session details into $sessionInfoArray['sessions']
		foreach ($sessionInfoArray['sessions'] as $thisDate => $day)
		{
			if (!empty($day['sessions']))
			{
				foreach ($day['sessions'] as $session_id)
				{
					$sessionInfoArray['sessions'][$thisDate]['sessions'][$session_id] = $sessionDetailsArray[$session_id];
				}
			}
		}

		// compile some info about each days sessions
		foreach ($sessionInfoArray['sessions'] as $thisDate => $day)
		{
			$sessionInfoArray['sessions'][$thisDate]['info']['is_past'] = false;
			$sessionInfoArray['sessions'][$thisDate]['info']['session_count'] = (empty($sessionInfoArray['sessions'][$thisDate]['sessions'])) ? 0 : count($sessionInfoArray['sessions'][$thisDate]['sessions']);
			$sessionInfoArray['sessions'][$thisDate]['info']['session_count_open'] = 0;
			$sessionInfoArray['sessions'][$thisDate]['info']['has_available_sessions'] = false;
			$sessionInfoArray['sessions'][$thisDate]['info']['day_has_session_in_cart'] = false;
			$sessionInfoArray['sessions'][$thisDate]['info']['menu_id'] = $menu_id; // $day['info']['menu']['id']; // TODO:  what?
			$sessionInfoArray['sessions'][$thisDate]['info']['session_types'] = array();
			$sessionInfoArray['sessions'][$thisDate]['info']['session_types_comma'] = false;

			if (!empty($sessionInfoArray['sessions'][$thisDate]['sessions']))
			{
				foreach ($sessionInfoArray['sessions'][$thisDate]['sessions'] as $session_id => $sessionInfo)
				{
					$sessionInfoArray['sessions'][$thisDate]['info']['menu_id'] = $sessionInfo['menu_id'];

					if (!empty($sessionInfo['is_open']))
					{
						$sessionInfoArray['sessions'][$thisDate]['info']['session_count_open']++;
					}

					if ($cart_info && isset($cart_info['session_info']['id']) && $cart_info['session_info']['id'] == $session_id)
					{
						$sessionInfoArray['sessions'][$thisDate]['info']['day_has_session_in_cart'] = true;
					}

					$session_types_key = ($sessionInfo['session_type'] . (!empty($sessionInfo['session_type_subtype']) ? '-' . $sessionInfo['session_type_subtype'] : ''));

					if (!isset($sessionInfoArray['sessions'][$thisDate]['info']['session_types'][$session_types_key]))
					{
						$sessionInfoArray['sessions'][$thisDate]['info']['session_types'][$session_types_key] = 1;
					}
					else
					{
						$sessionInfoArray['sessions'][$thisDate]['info']['session_types'][$session_types_key] += 1;
					}

					if ($sessionInfo['session_type'] == CSession::DELIVERED)
					{
						$sessionInfoArray['info']['session_type'][CSession::DELIVERED] += 1;
					}
				}
			}

			if (!empty($sessionInfoArray['sessions'][$thisDate]['info']['session_count_open']))
			{
				$sessionInfoArray['sessions'][$thisDate]['info']['has_available_sessions'] = true;
			}

			// TODO: when is a delivery date oin the past
			if (!empty($Store->id) && $thisDate < date('Y-m-d', CTimezones::getAdjustedServerTime($Store)))
			{
				$sessionInfoArray['sessions'][$thisDate]['info']['is_past'] = true;
			}

			$sessionInfoArray['sessions'][$thisDate]['info']['session_types_comma'] = implode(',', array_keys($sessionInfoArray['sessions'][$thisDate]['info']['session_types']));
		}

		return $sessionInfoArray;
	}

	static function getMonthlySessionInfoArray($Store, $date = false, $menu_id = false, $cart_info = false, $open_only = false, $excludeSavedSessions = false, $rescheduleSessionObj = false, $get_bookings = false, $date_is_anchor = false, $excludeFull = false, $excludeWalkIn = false)
	{
		$session_type_limit = false;

		if (!empty($cart_info))
		{
			if (is_array($cart_info))
			{
				$session_type_limit = $cart_info['cart_info_array']['navigation_type'];
			}
			else
			{
				$session_type_limit = $cart_info;
			}
		}

		$Sessions = DAO_CFactory::create('session');
		$Sessions->store_id = $Store->id;
		if ($excludeWalkIn)
		{
			$Sessions->whereAdd("session.session_type_subtype IS NULL OR session.session_type_subtype != '" . CSession::WALK_IN . "'");
		}
		if ($excludeSavedSessions)
		{
			$Sessions->whereAdd("session.session_publish_state = '" . CSession::PUBLISHED . "'");
		}

		if (!$date)
		{
			$date = time();
		}

		if ($date_is_anchor)
		{
			$curMenuArray = CMenu::getMenuByAnchorDate(date("Y-m-d", $date));
		}
		else
		{
			$curMenuArray = CMenu::getMenuByDate(date("Y-m-d", $date));
		}

		$menuMonth = $curMenuArray['menu_start'];

		$curMonthStartTS = strtotime($menuMonth);

		$sessionInfoArray = array(
			'sessions' => array(),
			'info' => array(
				'session_type' => array(
					CSession::STANDARD => 0,
					CSession::MADE_FOR_YOU => 0,
					CSession::DELIVERY => 0,
					CSession::DELIVERED => 0,
					CSession::REMOTE_PICKUP => 0,
					CSession::INTRO => 0,
					CSession::EVENT => 0,
					CSession::ALL_STANDARD => 0
				),
				'has_meal_customization_sessions' => false
			)
		);

		if (!empty($menu_id))
		{
			$sessionInfoArray['sessions'] = array();

			if (is_array($menu_id))
			{
				$optionsArray['menu_id_array'] = $menu_id;
			}
			else
			{
				$optionsArray = false;
				$Sessions->menu_id = $menu_id;
			}

			$Sessions->findSessionsByMenu($optionsArray);
		}
		else
		{
			list($rangeStart, $rangeEnd) = CCalendar::calculateExpandedMonthRange($curMonthStartTS);

			$Sessions->findSessionByCalendarRange($Store->id, $rangeStart, $rangeEnd);

			$sessionInfoArray['sessions'] = self::getDatesFromRange($rangeStart, $rangeEnd);
		}

		$sessionsIDArray = array();

		while ($Sessions->fetch())
		{
			// sessions with open slots only
			if ($excludeFull && $Sessions->getRemainingSlots() <= 0)
			{
				continue;
			}

			if ($excludeWalkIn && $Sessions->session_type_subtype == CSession::WALK_IN)
			{
				continue;
			}

			if (!$Store->storeSupportsIntroOrders($Sessions->menu_id))
			{
				$Sessions->introductory_slots = 0;
				$Sessions->remaining_intro_slots = 0;
			}

			// open sessions only
			if ($open_only)
			{
				if (!$Sessions->isOpen($Store)) // this strictly checks for whether or not we have passed the lockout time for a store (usually 24 hours prior to session start) ...
				{
					continue;
				}

				// ... so also check for a session_publish_state of PUBLISHED or SAVED
				if ($Sessions->session_publish_state != 'SAVED' && $Sessions->session_publish_state != 'PUBLISHED')
				{
					continue;
				}
			}

			if ($rescheduleSessionObj && !$Sessions->isOpenForRescheduling($Store))
			{
				continue;
			}
			else if ($rescheduleSessionObj)
			{
				$includeSession = true;

				// check current type against display type
				switch ($rescheduleSessionObj->session_type)
				{
					case CSession::STANDARD:
						if ($Sessions->session_type != CSession::STANDARD)
						{
							$includeSession = false;
						}
						break;
					case CSession::MADE_FOR_YOU:
						if ($Sessions->session_type != CSession::MADE_FOR_YOU)
						{
							$includeSession = false;
						}
						else if ($Sessions->session_type_subtype == CSession::DELIVERY || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP || $Sessions->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE)
						{
							// NOTE: FUTURE TODO:  if the source is MFY and is remote pickup and the target is remote AND the location is identical we could allow it.
							$includeSession = false;
						}
						break;
					case CSession::DREAM_TASTE:
						if ($Sessions->session_type != CSession::DREAM_TASTE)
						{
							$includeSession = false;
						}
						break;
					case CSession::FUNDRAISER:
						if ($Sessions->session_type != CSession::FUNDRAISER)
						{
							$includeSession = false;
						}
						break;
				}

				if (!$includeSession)
				{
					continue;
				}
			}

			if ($excludeSavedSessions && $Sessions->session_publish_state == 'SAVED')
			{
				continue;
			}

			// the cart is looking for intro only slots
			if (!empty($session_type_limit) && $session_type_limit == CSession::INTRO)
			{
				if (!$Sessions->isIntroSessionValid($Store))
				{
					continue;
				}

				if ($Sessions->isPrivate())
				{
					continue;
				}

				if (defined('ENABLE_CUSTOMER_INTRO_MFY') && ENABLE_CUSTOMER_INTRO_MFY != true)
				{
					if ($Sessions->isMadeForYou())
					{
						continue;
					}
				}
			}

			// any session not intro or event
			if (!empty($session_type_limit) && $session_type_limit == CSession::ALL_STANDARD)
			{

				if (!$Sessions->isStandardSessionValid($Store))
				{
					continue;
				}

				if ($Sessions->isDreamTaste() || $Sessions->isPrivate() || $Sessions->isFundraiser())
				{
					continue;
				}
			}

			// the cart is looking for standard sessions
			if (!empty($session_type_limit) && $session_type_limit == CSession::STANDARD)
			{
				if (!$Sessions->isStandard())
				{
					continue;
				}

				if (!$Sessions->isStandardSessionValid($Store))
				{
					continue;
				}

				if ($Sessions->isPrivate())
				{
					continue;
				}
			}

			// the cart is looking for made for you sessions
			if (!empty($session_type_limit) && $session_type_limit == CSession::MADE_FOR_YOU)
			{
				if (!$Sessions->isMadeForYou())
				{
					continue;
				}

				if ($Sessions->isDelivery())
				{
					continue;
				}

				if ($Sessions->isPrivate())
				{
					continue;
				}

				if (!$Sessions->isStandardSessionValid($Store))
				{
					continue;
				}
			}

			// the cart is looking for made for you sessions that are delivery only
			if (!empty($session_type_limit) && $session_type_limit == CSession::DELIVERY)
			{
				if (!$Sessions->isMadeForYou())
				{
					continue;
				}

				if (!$Sessions->isDelivery())
				{
					continue;
				}

				if ($Sessions->isPrivate())
				{
					continue;
				}

				if (!$Sessions->isStandardSessionValid($Store))
				{
					continue;
				}
			}

			// the cart is looking for made for you sessions that are delivered only
			if (!empty($session_type_limit) && $session_type_limit == CSession::DELIVERED)
			{
				if (!$Sessions->isMadeForYou())
				{
					continue;
				}

				if (!$Sessions->isDelivered())
				{
					continue;
				}

				if ($Sessions->isPrivate())
				{
					continue;
				}

				if (!$Sessions->isStandardSessionValid($Store))
				{
					continue;
				}
			}

			// the cart is looking for event sessions
			if (!empty($session_type_limit) && $session_type_limit == CSession::EVENT)
			{
				if ((!$Sessions->isDreamTaste() && !$Sessions->isPrivate() && !$Sessions->isFundraiser()))
				{
					continue;
				}

				if ($Sessions->isMadeForYou() && !$Sessions->isPrivate())
				{
					continue;
				}
			}

			$date = date('Y-m-d', strtotime($Sessions->session_start));

			$sessionInfoArray['sessions'][$date]['sessions'][$Sessions->id] = $Sessions->id;

			$sessionsIDArray[$Sessions->id] = $Sessions->id;
		}

		// query to get each session details
		if (!empty($sessionsIDArray))
		{
			$sessionsIDs = implode(',', $sessionsIDArray);
			$sessionDetailsArray = self::getSessionDetailArray($sessionsIDs, $get_bookings);
		}

		// put session details into $sessionInfoArray['sessions']
		foreach ($sessionInfoArray['sessions'] as $date => $day)
		{
			if (!empty($day['sessions']))
			{
				foreach ($day['sessions'] as $session_id)
				{
					$sessionInfoArray['sessions'][$date]['sessions'][$session_id] = $sessionDetailsArray[$session_id];
				}
			}
		}

		// compile some info about each days sessions
		foreach ($sessionInfoArray['sessions'] as $date => $day)
		{
			$sessionInfoArray['sessions'][$date]['info']['is_past'] = false;
			$sessionInfoArray['sessions'][$date]['info']['session_count'] = (empty($sessionInfoArray['sessions'][$date]['sessions'])) ? 0 : count($sessionInfoArray['sessions'][$date]['sessions']);
			$sessionInfoArray['sessions'][$date]['info']['session_count_open'] = 0;
			$sessionInfoArray['sessions'][$date]['info']['has_available_sessions'] = false;
			$sessionInfoArray['sessions'][$date]['info']['day_has_session_in_cart'] = false;
			$sessionInfoArray['sessions'][$date]['info']['menu_id'] = ((!empty($day['info']['menu']['id'])) ? $day['info']['menu']['id'] : false);
			$sessionInfoArray['sessions'][$date]['info']['session_types'] = array();
			$sessionInfoArray['sessions'][$date]['info']['session_types_comma'] = false;
			$sessionInfoArray['sessions'][$date]['info']['has_meal_customization_sessions'] = false;

			if (!empty($sessionInfoArray['sessions'][$date]['sessions']))
			{
				foreach ($sessionInfoArray['sessions'][$date]['sessions'] as $session_id => $sessionInfo)
				{
					$sessionInfoArray['sessions'][$date]['info']['menu_id'] = $sessionInfo['menu_id'];

					if (!empty($sessionInfo['is_open']))
					{
						$sessionInfoArray['sessions'][$date]['info']['session_count_open']++;
					}

					if ($sessionInfo['is_open_for_customization'])
					{
						$sessionInfoArray['sessions'][$date]['info']['has_meal_customization_sessions'] = true;
						$sessionInfoArray['info']['has_meal_customization_sessions'] = true;
					}

					if ($cart_info && isset($cart_info['session_info']['id']) && $cart_info['session_info']['id'] == $session_id)
					{
						$sessionInfoArray['sessions'][$date]['info']['day_has_session_in_cart'] = true;
					}

					$session_types_key = ($sessionInfo['session_type'] . (!empty($sessionInfo['session_type_subtype']) ? '-' . $sessionInfo['session_type_subtype'] : ''));

					if (!isset($sessionInfoArray['sessions'][$date]['info']['session_types'][$session_types_key]))
					{
						$sessionInfoArray['sessions'][$date]['info']['session_types'][$session_types_key] = 1;
					}
					else
					{
						$sessionInfoArray['sessions'][$date]['info']['session_types'][$session_types_key] += 1;
					}

					if ($sessionInfo['session_type'] == CSession::STANDARD && empty($sessionInfo['session_password']))
					{
						$sessionInfoArray['info']['session_type'][CSession::STANDARD] += 1;
						$sessionInfoArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($sessionInfo['session_type'] == CSession::MADE_FOR_YOU && $sessionInfo['session_type_subtype'] != CSession::DELIVERY)
					{
						$sessionInfoArray['info']['session_type'][CSession::MADE_FOR_YOU] += 1;
						$sessionInfoArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($sessionInfo['session_type'] == CSession::MADE_FOR_YOU && $sessionInfo['session_type_subtype'] == CSession::DELIVERY)
					{
						$sessionInfoArray['info']['session_type'][CSession::DELIVERY] += 1;
						$sessionInfoArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($sessionInfo['session_type'] == CSession::MADE_FOR_YOU && $sessionInfo['session_type_subtype'] == CSession::REMOTE_PICKUP)
					{
						$sessionInfoArray['info']['session_type'][CSession::REMOTE_PICKUP] += 1;
						$sessionInfoArray['info']['session_type'][CSession::ALL_STANDARD] += 1;
					}

					if ($Store->storeSupportsIntroOrders($sessionInfo['menu_id']))
					{
						if (!empty($sessionInfo['remaining_intro_slots']) && ($sessionInfo['session_type'] == CSession::MADE_FOR_YOU || $sessionInfo['session_type'] == CSession::STANDARD))
						{
							$sessionInfoArray['info']['session_type'][CSession::INTRO] += 1;
						}
					}

					if ($sessionInfo['session_type'] == CSession::DREAM_TASTE || $sessionInfo['session_type'] == CSession::FUNDRAISER || ($sessionInfo['session_type'] == CSession::STANDARD && !empty($sessionInfo['session_password'])))
					{
						$sessionInfoArray['info']['session_type'][CSession::EVENT] += 1;
					}
				}
			}

			if (!empty($sessionInfoArray['sessions'][$date]['info']['session_count_open']))
			{
				$sessionInfoArray['sessions'][$date]['info']['has_available_sessions'] = true;
			}

			if (!empty($Store->id) && $date < date('Y-m-d', CTimezones::getAdjustedServerTime($Store)))
			{
				$sessionInfoArray['sessions'][$date]['info']['is_past'] = true;
			}

			$sessionInfoArray['sessions'][$date]['info']['session_types_comma'] = implode(',', array_keys($sessionInfoArray['sessions'][$date]['info']['session_types']));
		}

		return $sessionInfoArray;
	}

	static function getDatesFromRange($start, $end)
	{
		$dates = array();

		$start = date('Y-m-d', strtotime($start));

		$end = date('Y-m-d', strtotime($end));

		$Menu = DAO_CFactory::create('menu');
		$Menu->query("SELECT m1.*,
			DATE_ADD(m2.global_menu_end_date, INTERVAL 1 DAY) AS global_menu_start_date,
			DATE_FORMAT(m1.menu_start, '%Y-%m') AS 'year_month'
			FROM menu AS m1
			LEFT JOIN menu AS m2 ON m2.id + 1 = m1.id
			WHERE (m1.global_menu_end_date > '" . $start . "' AND m1.global_menu_end_date < '" . $end . "')
			OR (m1.menu_start < '" . $end . "' AND m1.global_menu_end_date > '" . $end . "')
			AND m1.is_deleted = '0'");

		$menuArray = array();

		while ($Menu->fetch())
		{
			$menuArray[$Menu->id] = $Menu->toArray();
		}

		$date = $start;

		while ($date <= $end)
		{
			foreach ($menuArray as $menu)
			{
				if ($date >= $menu['global_menu_start_date'] && $date <= $menu['global_menu_end_date'])
				{
					$dates[$date] = array();

					$dates[$date]['info']['menu'] = $menu;
				}
			}

			$date = date('Y-m-d', strtotime($date . ' +1 day'));
		}

		return $dates;
	}

	function getRemoteLocation()
	{
		$this->remote_location = null;

		if ($this->isRemotePickup())
		{
			$location = DAO_CFactory::create('store_pickup_location');
			$location->id = $this->store_pickup_location_id;
			$location->find(true);

			$location->contact_user = false;

			if (!empty($location->contact_user_id))
			{
				$location->contact_user = DAO_CFactory::create('user');
				$location->contact_user->id = $location->contact_user_id;
				$location->contact_user->find(true);
			}

			$this->remote_location = $location;
		}

		return $this->remote_location;
	}

	function fetchFadminAcronym()
	{


		$acronymLookup = DAO_CFactory::create('session');
		$query = "SELECT
       			dtet.fadmin_acronym
				FROM session s 
				LEFT JOIN session_properties sp ON s.id = sp.session_id
				LEFT JOIN dream_taste_event_properties dtep ON dtep.id = sp.dream_taste_event_id
				LEFT JOIN dream_taste_event_theme dtet ON dtet.id = dtep.dream_taste_event_theme    
				WHERE s.id = " . $this->id;

		$acronymLookup->query($query);

		$acronymLookup->fetch();

		return $acronymLookup->fadmin_acronym;
	}

	/**
	 * Query for sessions at a store based on date range
	 */
	function findSessionByCalendarRange($store_id, $rangeStart, $rangeEnd)
	{
		return $this->query("SELECT
					s.*,
					COUNT(DISTINCT sr.id) AS num_rsvps,
					(available_slots - (COUNT(DISTINCT b.id) + COUNT(DISTINCT sr.id))) AS remaining_slots,
					(introductory_slots -  (COUNT(IF(b.booking_type = 'INTRO', 1, NULL)))) AS remaining_intro_slots,
					m.menu_name,
					m.menu_start,
					COUNT(DISTINCT b.id) + COUNT(DISTINCT sr.id) AS booked_count
					FROM session AS s
					INNER JOIN menu AS m ON s.menu_id = m.id
					LEFT JOIN booking AS b ON  b.session_id = s.id  AND b.status = 'ACTIVE'
					LEFT JOIN session_rsvp AS sr ON sr.session_id = s.id AND sr.upgrade_booking_id IS NULL AND sr.is_deleted = 0
					WHERE s.is_deleted = 0
					AND s.store_id = '" . $store_id . "'
					AND s.session_start >= '" . $rangeStart . "'
					AND s.session_start <= '" . $rangeEnd . "'
					GROUP BY s.id
					ORDER BY s.session_start ASC");
	}

	static function getSessionDetailArrayByDate($store_id, $yyyy_mm_dd, $get_bookings = true)
	{
		$Session = DAO_CFactory::create('session');

		$Session->query("SELECT s.id
					FROM session AS s
					WHERE s.store_id = '" . $store_id . "'
					AND s.session_start >= '" . $yyyy_mm_dd . " 00:00:00'
					AND s.session_start <= DATE_ADD('" . $yyyy_mm_dd . " 00:00:00', INTERVAL 1 DAY)
					ORDER BY s.session_start ASC");

		$sessionArray = array();

		while ($Session->fetch())
		{
			$sessionArray[] = $Session->id;
		}

		$session_ids = implode(',', $sessionArray);

		$session_info_array = self::getSessionDetailArray($session_ids, $get_bookings);

		// compile some information for the day based on the session info array
		$date_info_array = array();

		// formatted variables useful for javascript display
		$date_info_array['date_dtf_verbose_date'] = CTemplate::dateTimeFormat($yyyy_mm_dd . ' 00:00:00', VERBOSE_DATE);

		if (!empty($session_info_array))
		{
			$date_info_array['session_count'] = count($session_info_array);

			$date_info_array['booked_count'] = 0;
			$date_info_array['available_slots'] = 0;
			$date_info_array['booked_standard_slots'] = 0;
			$date_info_array['booked_intro_slots'] = 0;
			$date_info_array['remaining_slots'] = 0;
			$date_info_array['remaining_intro_slots'] = 0;
			$date_info_array['session_leads'] = array();

			foreach ($session_info_array as $session)
			{
				$date_info_array['menu_id'] = $session['menu_id'];
				$date_info_array['menu_name'] = $session['menu_name'];

				$date_info_array['booked_count'] += ($session['booked_count'] + $session['num_rsvps']);
				$date_info_array['available_slots'] += $session['available_slots'];
				$date_info_array['booked_standard_slots'] += $session['booked_standard_slots'];
				$date_info_array['booked_intro_slots'] += $session['booked_intro_slots'];
				$date_info_array['remaining_slots'] += $session['remaining_slots'];
				$date_info_array['remaining_intro_slots'] += $session['remaining_intro_slots'];
				if (!empty($session['session_lead']))
				{
					$date_info_array['session_leads'][$session['session_lead']] = array(
						'lead_firstname' => $session['lead_firstname'],
						'lead_lastname' => $session['lead_lastname']
					);
				}
			}
		}

		return array(
			$date_info_array,
			$session_info_array
		);
	}

	/*
	 * Function to return details of a single session
	*/
	static function getSessionDetail($session_id, $get_bookings = true)
	{
		$sessionDetails = self::getSessionDetailArray($session_id, $get_bookings);

		return $sessionDetails[$session_id];
	}

	/**
	 * Returns array of session details by comma list of session_ids
	 */
	static function getSessionDetailArray($session_id, $get_bookings = true, $get_order_info = false)
	{
		if (DEBUG && empty($session_id))
		{
			throw new Exception("No session id specified for CSession::getSessionDetailArray()");
		}

		$Session = DAO_CFactory::create('session');

		$Session->query("SELECT iq.*,
					GROUP_CONCAT(btmi.menu_item_id) AS dream_taste_menu_items FROM
					(SELECT
					s.id,
					s.store_id,
					s.menu_id,
					s.available_slots,
					s.introductory_slots,
					(available_slots - (COUNT(IF(b.`status` = 'ACTIVE', 1, NULL)))) AS remaining_slots,
					(introductory_slots - (COUNT(IF(b.`status` = 'ACTIVE' AND b.booking_type = 'INTRO', 1, NULL)))) AS remaining_intro_slots,
					(COUNT(IF(b.`status` = 'ACTIVE' AND b.booking_type = 'STANDARD', 1, NULL))) AS booked_standard_slots,
					(COUNT(IF(b.`status` = 'ACTIVE' AND b.booking_type = 'INTRO', 1, NULL))) AS booked_intro_slots,
					(COUNT(IF(b.`status` = 'ACTIVE', 1, NULL))) AS booked_count,
					(COUNT(IF(b.`status` = 'RESCHEDULED', 1, NULL))) AS booking_rescheduled_count,
					(COUNT(IF(b.`status` = 'CANCELLED', 1, NULL))) AS booking_cancelled_count,
					s.sneak_peak,
					s.duration_minutes,
					s.session_title,
					s.session_details,
					s.session_type,
					s.session_class,
					s.session_type_subtype,
					s.session_publish_state,
					s.admin_notes,
					s.timestamp_updated,
					s.timestamp_created,
					s.session_start,
					s.session_close_scheduling,
					s.session_close_scheduling_meal_customization,
					s.session_discount_id,
					s.close_interval_type,
					s.session_password,
					s.session_lead,
					s.inventory_was_processed,
					s.created_by,
					s.updated_by,
					s.is_deleted,
					s.is_migrated,
					CASE s.session_type
					WHEN 'DREAM_TASTE' THEN 'taste'
					WHEN 'FUNDRAISER' THEN 'fundraiser'
					WHEN 'TODD' THEN 'ref2'
					END AS session_ref,
					CASE s.session_type
					WHEN 'DREAM_TASTE' THEN 'Meal Prep Workshop'
					WHEN 'FUNDRAISER' THEN 'Fundraiser Event'
					WHEN 'TODD' THEN 'Taste of Dream Dinners'
					END AS session_type_text,
					st.address_line1,
					st.address_line2,
					st.city,
					st.state_id,
					st.county,
					st.postal_code,
					st.email_address,
					st.telephone_day,
					st.telephone_evening,
					st.store_name,
					st.store_description,
					st.timezone_id,
					st.store_type,
					m.menu_name,
					m.menu_start,
					u.firstname AS lead_firstname,
					u.lastname AS lead_lastname,
					u2.firstname AS created_by_firstname,
					u2.lastname AS created_by_lastname,
					u3.firstname AS updated_by_firstname,
					u3.lastname AS updated_by_lastname,
					sd.discount_type,
					sd.discount_var,
					sp.id AS session_properties_id,
					sp.message AS session_host_message,
					sp.fundraiser_id,
					sp.store_pickup_location_id,
					u4.id AS session_host,
					u4.primary_email AS session_host_primary_email,
					u4.firstname AS session_host_firstname,
					u4.lastname AS session_host_lastname,
					IF(sp.informal_host_name IS NULL OR sp.informal_host_name = '', u4.firstname, sp.informal_host_name) AS session_host_informal_name,
					dtp.id AS dream_taste_event_prop_id,
					dtp.host_required AS dream_taste_host_required,
					dtp.available_on_customer_site AS dream_taste_available_on_customer_site,
					dtp.password_required AS dream_taste_password_required,
					dtp.can_rsvp_only AS dream_taste_can_rsvp_only,
					dtp.can_rsvp_upgrade AS dream_taste_can_rsvp_upgrade,
					dtp.menu_used_with_theme AS dream_taste_menu_used_with_theme,
					dtp.existing_guests_can_attend AS dream_taste_existing_guests_can_attend,
					dtet.title AS dream_taste_theme_title,
					dtet.title_public AS dream_taste_theme_title_public,
					dtet.fadmin_acronym AS dream_taste_theme_fadmin_acronym,
					dtet.sub_theme AS dream_taste_sub_theme,
					dtet.sub_sub_theme AS dream_taste_sub_sub_theme,
					dtet.theme_string AS dream_taste_theme_string,
					CONCAT(SUBSTRING(dtet.theme_string,1,CHAR_LENGTH(dtet.theme_string)-7),'default') AS dream_taste_theme_string_default,
					f.fundraiser_name,
					f.fundraiser_description,
					f.donation_value AS fundraiser_donation_value,
					dt_bdl.id AS dream_taste_bundle_id,
					dt_bdl.number_items_required AS dream_taste_number_items_required,
					dt_bdl.number_servings_required AS dream_taste_number_servings_required,
					dt_bdl.price AS dream_taste_price,
					dtp.bundle_id
					FROM `session` AS s
					LEFT JOIN booking AS b ON b.session_id = s.id AND b.is_deleted = '0'
					INNER JOIN menu AS m ON m.id = s.menu_id AND m.is_deleted = '0'
					INNER JOIN store AS st ON s.store_id = st.id AND st.is_deleted = '0'
					LEFT JOIN `user` AS u ON u.id = s.session_lead AND u.is_deleted = '0'
					LEFT JOIN `user` AS u2 ON u2.id = s.created_by AND u2.is_deleted = '0'
					LEFT JOIN `user` AS u3 ON u3.id = s.updated_by AND u3.is_deleted = '0'
					LEFT JOIN `session_discount` AS sd ON sd.id = s.session_discount_id AND sd.is_deleted = '0'
					LEFT JOIN `session_properties` AS sp ON sp.session_id = s.id AND sp.is_deleted = '0'
					LEFT JOIN store_to_fundraiser AS stf ON stf.id = sp.fundraiser_id AND stf.is_deleted = '0'
					LEFT JOIN fundraiser AS f ON f.id = stf.fundraiser_id AND f.is_deleted = '0'
					LEFT JOIN `user` AS u4 ON u4.id = sp.session_host AND u4.is_deleted = '0'
					LEFT JOIN dream_taste_event_properties AS dtp ON dtp.menu_id = s.menu_id AND dtp.id = sp.dream_taste_event_id AND dtp.is_deleted = '0'
					LEFT JOIN bundle AS dt_bdl ON dt_bdl.id = dtp.bundle_id AND dt_bdl.is_deleted = '0'
					LEFT JOIN dream_taste_event_theme AS dtet ON dtp.dream_taste_event_theme = dtet.id
					WHERE s.is_deleted = '0' AND s.id IN (" . $session_id . ")
					GROUP BY s.id) AS iq
					LEFT JOIN bundle_to_menu_item AS btmi ON btmi.bundle_id = iq.bundle_id AND btmi.is_deleted = '0'
					GROUP BY iq.id
					ORDER BY iq.session_start ASC");

		$session_info_array = array();

		while ($Session->fetch())
		{
			$session_info_array[$Session->id] = $Session->toArray();
			$numRSVPs = $Session->get_RSVP_count();

			$Session->remaining_slots -= $numRSVPs;

			$session_info_array[$Session->id]['num_rsvps'] = $numRSVPs;
			$session_info_array[$Session->id]['remaining_slots'] -= $numRSVPs;

			if ($Session->remaining_slots < $Session->remaining_intro_slots)
			{
				$session_info_array[$Session->id]['remaining_intro_slots'] = $Session->remaining_slots;
			}

			if ($Session->remaining_intro_slots < 0)
			{
				$session_info_array[$Session->id]['remaining_intro_slots'] = 0;
			}

			$session_info_array[$Session->id]['session_percent_full'] = $Session->percentFull();

			$session_info_array[$Session->id]['is_open'] = ($Session->isOpen($Session->timezone_id)) ? 1 : 0;
			$session_info_array[$Session->id]['is_open_for_customization'] = ($Session->isOpenForCustomization($Session->timezone_id)) ? 1 : 0;

			$session_info_array[$Session->id]['session_remote_location'] = $Session->getRemoteLocation();
			$session_info_array[$Session->id]['session_note'] = $Session->sessionNoteType();
			$session_info_array[$Session->id]['session_type_true'] = $Session->session_type_true;
			$session_info_array[$Session->id]['session_type_desc'] = $Session->session_type_desc;
			list ($session_info_array[$Session->id]['session_type_title'], $session_info_array[$Session->id]['session_type_title_public'], $session_info_array[$Session->id]['session_type_title_short'], $session_info_array[$Session->id]['session_type_fadmin_acronym'], $session_info_array[$Session->id]['session_type_string']) = $Session->getSessionTypeProperties();

			// session end in unix time stamp, useful to use JavaScript to updated the display dynamically when the session ends
			$sessionEnd = new DateTime($Session->session_start, new DateTimeZone(CTimezones::zone_by_id($Session->timezone_id)));
			$session_info_array[$Session->id]['unix_expiry'] = $sessionEnd->getTimestamp() + (60 * $Session->duration_minutes);
			$sessionEnd->modify("+" . $Session->duration_minutes . " minutes");
			$session_info_array[$Session->id]['session_end'] = $sessionEnd->format("Y-m-d H:i:s");

			// for use in image and template paths
			if ($Session->store_type == CStore::DISTRIBUTION_CENTER)
			{
				// may need uniquepath to theme folder
			}
			else
			{
				$session_info_array[$Session->id]['menu_directory'] = CTemplate::dateTimeFormat($Session->menu_name, YEAR_UNDERSCORE_MONTH);
			}

			// check if session is in the past
			$timeNow = CTimezones::getAdjustedServerTime($Session->timezone_id);
			$session_info_array[$Session->id]['is_past'] = false;
			if ($timeNow > $session_info_array[$Session->id]['unix_expiry'])
			{
				$session_info_array[$Session->id]['is_past'] = true;
			}

			// formatted variables useful for JavaScript display
			$session_info_array[$Session->id]['session_start_dtf_verbose_date'] = CTemplate::dateTimeFormat($Session->session_start, VERBOSE_DATE);
			$session_info_array[$Session->id]['session_start_dtf_time_only'] = CTemplate::dateTimeFormat($Session->session_start, TIME_ONLY);
			$session_info_array[$Session->id]['session_start_dtf_ymd'] = CTemplate::dateTimeFormat($Session->session_start, YEAR_MONTH_DAY);

			$session_info_array[$Session->id]['session_end_dtf_verbose_date'] = CTemplate::dateTimeFormat($session_info_array[$Session->id]['session_end'], VERBOSE_DATE);
			$session_info_array[$Session->id]['session_end_dtf_time_only'] = CTemplate::dateTimeFormat($session_info_array[$Session->id]['session_end'], TIME_ONLY);
			$session_info_array[$Session->id]['session_end_dtf_ymd'] = CTemplate::dateTimeFormat($session_info_array[$Session->id]['session_end'], YEAR_MONTH_DAY);

			if ($get_bookings)
			{
				$session_info_array[$Session->id]['bookings'] = $Session->getBookingsForSession();

				$session_info_array[$Session->id]['session_rsvp'] = $Session->getSessionRSVPArray();
			}

			$session_info_array[$Session->id]['additional_orders'] = $Session->getAdditionalOrderCount();
		}

		return $session_info_array;
	}

	function getShippingCount()
	{
		$nextDayDelivery = new DateTime($this->session_start);
		$nextDayDelivery->modify("+1 day");
		$nextDayDeliverySessionID = self::getDeliveredSessionIDByDate($nextDayDelivery->format("Y-m-d"), $this->store_id);

		$secondDayDelivery = clone($nextDayDelivery);
		$secondDayDelivery->modify("+1 day");
		$secondDayDeliverySessionID = self::getDeliveredSessionIDByDate($secondDayDelivery->format("Y-m-d"), $this->store_id);

		$shipCount = new DAO();
		$shipCount->query("SELECT
							b.id
							FROM booking AS b
							INNER JOIN `session` AS s ON s.id = b.session_id
							INNER JOIN orders AS o ON o.id = b.order_id
							INNER JOIN orders_shipping AS sd ON o.id = sd.order_id
							WHERE ((b.session_id = $nextDayDeliverySessionID and sd.service_days = 1) or (b.session_id = $secondDayDeliverySessionID and sd.service_days = 2))
							AND b.is_deleted = '0' and b.status = 'ACTIVE'");

		return $shipCount->N;
	}

	/**
	 * Returns array of session details by comma list of session_ids
	 */
	static function getDeliveredSessionDetailArray($session_id, $get_bookings = true)
	{
		$DAO_session = DAO_CFactory::create('session', true);

		$DAO_session->query("SELECT
					s.id,
					s.store_id,
					s.menu_id,
					s.available_slots,
					s.introductory_slots,
					s.session_close_scheduling,
					s.session_close_scheduling_meal_customization,
					(s.available_slots - count(b.id)) AS remaining_slots,
					0 AS remaining_intro_slots,
					0 AS booked_intro_slots,
					(COUNT(IF(b.`status` = 'ACTIVE', 1, NULL))) AS booked_count,
					(COUNT(IF(b.`status` = 'RESCHEDULED', 1, NULL))) AS booking_rescheduled_count,
					(COUNT(IF(b.`status` = 'CANCELLED', 1, NULL))) AS booking_cancelled_count,
					s.session_type,
					s.session_start,
					s.created_by,
					s.updated_by,
					s.is_deleted,
					s.delivered_supports_shipping,
					s.delivered_supports_delivery,
					st.address_line1,
					st.address_line2,
					st.city,
					st.state_id,
					st.county,
					st.postal_code,
					st.email_address,
					st.telephone_day,
					st.telephone_evening,
					st.store_name,
					st.store_description,
					st.timezone_id,
					st.store_type,
					m.menu_name,
					m.menu_start,
					u.firstname AS lead_firstname,
					u.lastname AS lead_lastname,
					u2.firstname AS created_by_firstname,
					u2.lastname AS created_by_lastname,
					u3.firstname AS updated_by_firstname,
					u3.lastname AS updated_by_lastname
					FROM `session` AS s
					LEFT JOIN booking AS b ON b.session_id = s.id AND b.is_deleted = '0'
					INNER JOIN menu AS m ON m.id = s.menu_id AND m.is_deleted = '0'
					INNER JOIN store AS st ON s.store_id = st.id AND st.is_deleted = '0'
					LEFT JOIN `user` AS u ON u.id = s.session_lead AND u.is_deleted = '0'
					LEFT JOIN `user` AS u2 ON u2.id = s.created_by AND u2.is_deleted = '0'
					LEFT JOIN `user` AS u3 ON u3.id = s.updated_by AND u3.is_deleted = '0'
					WHERE s.is_deleted = '0' AND s.id IN (" . $session_id . ")	
					GROUP BY s.id");

		$session_info_array = array();

		while ($DAO_session->fetch())
		{
			$session_info_array[$DAO_session->id] = $DAO_session->toArray();
			$numRSVPs = 0;

			$session_info_array[$DAO_session->id]['session_percent_full'] = $DAO_session->percentFull();
			$session_info_array[$DAO_session->id]['is_open'] = ($DAO_session->isOpen($DAO_session->timezone_id)) ? 1 : 0;

			$session_info_array[$DAO_session->id]['session_remote_location'] = false;
			$session_info_array[$DAO_session->id]['session_note'] = $DAO_session->sessionNoteType();
			$session_info_array[$DAO_session->id]['session_type_true'] = $DAO_session->session_type_true;
			$session_info_array[$DAO_session->id]['session_type_desc'] = $DAO_session->session_type_desc;
			list ($session_info_array[$DAO_session->id]['session_type_title'], $session_info_array[$DAO_session->id]['session_type_title_public'], $session_info_array[$DAO_session->id]['session_type_title_short'], $session_info_array[$DAO_session->id]['session_type_fadmin_acronym'], $session_info_array[$DAO_session->id]['session_type_string']) = $DAO_session->getSessionTypeProperties();

			// session end in unix time stamp, useful to use JavaScript to updated the display dynamically when the session ends
			$sessionEnd = new DateTime($DAO_session->session_start, new DateTimeZone(CTimezones::zone_by_id($DAO_session->timezone_id)));
			$session_info_array[$DAO_session->id]['unix_expiry'] = $sessionEnd->getTimestamp() + (60 * $DAO_session->duration_minutes);

			// for use in image and template paths
			if ($DAO_session->store_type == CStore::DISTRIBUTION_CENTER)
			{
				// may need uniquepath to theme folder
			}
			else
			{
				$session_info_array[$DAO_session->id]['menu_directory'] = CTemplate::dateTimeFormat($DAO_session->menu_name, YEAR_UNDERSCORE_MONTH);
			}

			// check if session is in the past
			$timeNow = CTimezones::getAdjustedServerTime($DAO_session->timezone_id);
			$session_info_array[$DAO_session->id]['is_past'] = false;
			if ($timeNow > $session_info_array[$DAO_session->id]['unix_expiry'])
			{
				$session_info_array[$DAO_session->id]['is_past'] = true;
			}

			// formatted variables useful for JavaScript display
			$session_info_array[$DAO_session->id]['session_start_dtf_verbose_date'] = CTemplate::dateTimeFormat($DAO_session->session_start, VERBOSE_DATE);
			$session_info_array[$DAO_session->id]['session_start_dtf_time_only'] = CTemplate::dateTimeFormat($DAO_session->session_start, TIME_ONLY);
			$session_info_array[$DAO_session->id]['session_start_dtf_ymd'] = CTemplate::dateTimeFormat($DAO_session->session_start, YEAR_MONTH_DAY);

			if ($get_bookings)
			{
				$session_info_array[$DAO_session->id]['bookings'] = $DAO_session->getDeliveredBookingsForSession();
				$session_info_array[$DAO_session->id]['delivered_bookings_count'] = count($session_info_array[$DAO_session->id]['bookings']);
				$session_info_array[$DAO_session->id]['shipping_bookings'] = $DAO_session->getDeliveredBookingsForSession(true);
				$session_info_array[$DAO_session->id]['shipping_bookings_count'] = count($session_info_array[$DAO_session->id]['shipping_bookings']);
				//	$session_info_array[$Session->id]['bookings_by_ship_date'] = $Session->getDeliveredBookingsForShipDate();
				$session_info_array[$DAO_session->id]['session_rsvp'] = false;
				$DAO_session->remaining_slots = $DAO_session->available_slots - count($session_info_array[$DAO_session->id]['shipping_bookings']);
				$session_info_array[$DAO_session->id]['remaining_slots'] = $DAO_session->remaining_slots;
			}
			else
			{
				$DAO_session->remaining_slots = $DAO_session->available_slots - $DAO_session->getShippingCount();
			}
		}

		return $session_info_array;
	}

	static function getDeliveredSessionIDByDate($date, $store_id)
	{
		$normalDate = new DateTime($date);
		$mySQLDate = $normalDate->format("Y-m-d");
		$sessionFinder = new DAO();
		$sessionFinder->query("select id from session where store_id = $store_id and DATE(session_start) = '$mySQLDate' and session_type = 'DELIVERED' and is_deleted = 0");
		if ($sessionFinder->fetch())
		{
			return $sessionFinder->id;
		}

		return false;
	}

	/*
	 * getShippingBookings means get the bookings who delivery date session (stored in booking) is
	 * satisfied by shipping during the current session
	 */
	function getDeliveredBookingsForSession($getShippingBookings = false)
	{

		if ($getShippingBookings)
		{
			$nextDayDelivery = new DateTime($this->session_start);
			$nextDayDelivery->modify("+1 day");
			$nextDayDeliverySessionID = self::getDeliveredSessionIDByDate($nextDayDelivery->format("Y-m-d"), $this->store_id);

			$secondDayDelivery = clone($nextDayDelivery);
			$secondDayDelivery->modify("+1 day");
			$secondDayDeliverySessionID = self::getDeliveredSessionIDByDate($secondDayDelivery->format("Y-m-d"), $this->store_id);

			// TODO: what if the session is missing? Should be rare.

			$whereClause = " ((b.session_id = $nextDayDeliverySessionID and sd.service_days = 1) or (b.session_id = $secondDayDeliverySessionID and sd.service_days = 2)) and b.status = 'ACTIVE' ";
		}
		else
		{
			$whereClause = " b.session_id = {$this->id} ";
		}

		$bookings = DAO_CFactory::create('booking');
		$query = "SELECT
					GROUP_CONCAT(ud.user_data_field_id SEPARATOR '||') AS user_data_field_ids,
					GROUP_CONCAT(ud.user_data_value SEPARATOR '||') AS user_data_values,
					up.preferred_type,
					up.preferred_value,
					up.user_preferred_start,
					if (up_global.preferred_type IS NULL, 0, 1) AS preferred_somewhere,
					IQ.*
					FROM (SELECT
							b.id,
							b.order_id,
							b.user_id,
							b.session_id,
							b.`status`,
							b.booking_type,
							b.no_show,
					        b.reason_for_cancellation,
							b.declined_MFY_option,
							b.declined_to_reschedule,
							u.firstname,
							u.lastname,
							u.home_store_id,
							u.primary_email,
							u.secondary_email,
							u.is_partial_account,
							u.user_type,
							u.has_opted_out_of_plate_points,
							s.menu_id,
							s.session_start,
							s.session_type,
							s.store_id,
							st.hide_carryover_notes,
							o.order_user_notes,
							o.order_admin_notes,
							o.grand_total,
							o.bundle_id,
							o.in_store_order,
							o.grand_total - o.subtotal_all_taxes AS points_basis,
							o.points_are_actualized,
							o.is_in_plate_points_program,
							o.timestamp_created AS order_time,
					        o.type_of_order,
					        o.servings_total_count,
				            o.ltd_round_up_value,
							u.dream_reward_status,
							u.dream_rewards_version,
					        sd.service_days,
					        sd.tracking_number,
							GROUP_CONCAT(p.payment_type ORDER BY p.id) AS payment_types,
							GROUP_CONCAT(p.total_amount ORDER BY p.id) AS payment_amounts,
							GROUP_CONCAT(p.is_delayed_payment ORDER BY p.id) AS is_delayed,
							GROUP_CONCAT(ifnull(p.delayed_payment_status, 0) ORDER BY p.id) AS delayed_status,
							udi.first_session,
							udi.dream_taste_third_order_invite,
							udi.visit_count AS user_digest_visit_count,
					        udi.total_delivered_boxes AS user_digest_total_delivered_boxes,
					        oa.is_gift as is_gift,
							(select count(*) from edited_orders eo where eo.is_deleted = '0' and eo.original_order_id = o.id) as edit_order_count
							FROM booking AS b
							INNER JOIN `user` AS u ON u.id = b.user_id
							INNER JOIN `session` AS s ON s.id = b.session_id
							INNER JOIN `store` AS st ON st.id = s.store_id
							INNER JOIN orders AS o ON o.id = b.order_id
							INNER JOIN orders_shipping AS sd ON o.id = sd.order_id and sd.is_deleted = '0'
							INNER JOIN orders_address AS oa ON o.id = oa.order_id and oa.is_deleted = '0'
							LEFT JOIN payment AS p ON p.order_id = b.order_id AND p.is_deleted = '0'
							LEFT JOIN user_digest AS udi ON udi.user_id = b.user_id AND udi.is_deleted = '0'
							WHERE $whereClause
							AND b.is_deleted = '0'
							GROUP BY b.id) AS IQ
					LEFT JOIN user_data AS ud ON ud.user_id = IQ.user_id AND ud.is_deleted = '0' AND (ud.store_id = IQ.store_id OR isnull(ud.store_id))
					LEFT JOIN user_preferred AS up ON up.user_id = IQ.user_id AND IQ.store_id = up.store_id AND up.is_deleted = '0'
					LEFT JOIN user_preferred AS up_global ON up_global.user_id = IQ.user_id  AND up_global.is_deleted = '0'
					GROUP BY IQ.id
					ORDER BY IQ.`status` ASC, IQ.lastname ASC";
		$bookings->query($query);

		$expiredForEditing = false;
		$sessionTS = strtotime($this->session_start);

		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select timezone_id from store where id = {$this->store_id}");
		$storeObj->fetch();

		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->id = $this->menu_id;
		$MenuObj->find(true);
		$expiredForEditing = !$MenuObj->areSessionsOrdersEditable($storeObj);

		$adjustedServerTime = CTimezones::getAdjustedServerTimeWithTimeZoneID($storeObj->timezone_id);

		// check for orders in previous month if current day is greater than 6
		$day = date("j", $adjustedServerTime);
		$month = date("n", $adjustedServerTime);
		$year = date("Y", $adjustedServerTime);

		$booking_info_array = array();
		$booked_user_ids = array();

		while ($bookings->fetch())
		{
			$booked_user_ids[] = $bookings->user_id;
			$booking_info_array[$bookings->id] = $bookings->toArray();
			$booking_info_array[$bookings->id]['can_edit'] = false;

			if (defined('ORDER_EDITING_ENABLED') && ORDER_EDITING_ENABLED)
			{
				if ($expiredForEditing)
				{
					$booking_info_array[$bookings->id]['can_edit'] = false;
				}
				else if ($bookings->status == CBooking::CANCELLED)
				{
					$booking_info_array[$bookings->id]['can_edit'] = false;
				}
				else
				{
					$booking_info_array[$bookings->id]['can_edit'] = true;
				}
			}
			else
			{
				$booking_info_array[$bookings->id]['can_edit'] = false;
			}

			if ($bookings->status == CBooking::ACTIVE)
			{
				$sessionTS = strtotime($bookings->session_start);

				// TODO: When is a DELIVERED order complete

				if ($sessionTS > $adjustedServerTime)
				{
					$bookings->status = 'PENDING';
				}
				else
				{
					$bookings->status = 'COMPLETED';
				}
			}

			switch ($bookings->status)
			{
				case CBooking::ACTIVE:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_active';
					break;
				case CBooking::PENDING:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_pending';
					break;
				case CBooking::COMPLETED:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_completed';
					break;
				case CBooking::RESCHEDULED:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_rescheduled';
					break;
				case CBooking::SAVED:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_saved';
					break;
				default:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_active';
					break;
			}

			switch ($bookings->booking_type)
			{
				case CBooking::STANDARD:
					$booking_info_array[$bookings->id]['booking_type_css'] = 'b_type_standard';
					break;
				case CBooking::INTRO:
					$booking_info_array[$bookings->id]['booking_type_css'] = 'b_type_intro';
					break;
				default:
					$booking_info_array[$bookings->id]['booking_type_css'] = 'b_type_standard';
					break;
			}

			if (!empty($bookings->no_show))
			{
				$booking_info_array[$bookings->id]['no_show'] = true;
			}
			else
			{
				$booking_info_array[$bookings->id]['no_show'] = false;
			}

			$bookings->bookingUser($booking_info_array[$bookings->id]);
			$bookings->can_reschedule($booking_info_array[$bookings->id]);
			$bookings->bookingBalanceDue($booking_info_array[$bookings->id]);
			$bookings->bookingUserData($booking_info_array[$bookings->id]);
			$bookings->leveledUpSinceLastSession($booking_info_array[$bookings->id]);

			$tempEvent = DAO_CFactory::create('box_instance');
			$tempEvent->query("select count(*) as total from box_instance where is_deleted = 0 and order_id = {$bookings->order_id}");
			if ($tempEvent->N > 0)
			{
				$tempEvent->fetch();
				$booking_info_array[$bookings->id]['total_boxes'] = $tempEvent->total;
			}

			if ($booking_info_array[$bookings->id]['user']->platePointsData['status'] == 'active')
			{
				// old array assignments from before ['user']->platePointsData was available, RCS 06-16-2014
				$booking_info_array[$bookings->id]['lifetime_points'] = $booking_info_array[$bookings->id]['user']->platePointsData['lifetime_points'];
				$booking_info_array[$bookings->id]['pending_points'] = $booking_info_array[$bookings->id]['user']->platePointsData['pending_points'];
				$booking_info_array[$bookings->id]['platepoints_detail'] = $booking_info_array[$bookings->id]['user']->platePointsData['current_level'];
				$booking_info_array[$bookings->id]['due_reward_for_current_level'] = $booking_info_array[$bookings->id]['user']->platePointsData['due_reward_for_current_level'];
				$booking_info_array[$bookings->id]['gift_display_str'] = $booking_info_array[$bookings->id]['user']->platePointsData['gift_display_str'];

				$confirmCutOffAdjustment = 0;
				if (defined('DD_SERVER_NAME') && DD_SERVER_NAME != 'LIVE')
				{
					$confirmCutOffAdjustment = 86400 * 90;
				}

				if (!$bookings->is_in_plate_points_program)
				{
					$booking_info_array[$bookings->id]['can_confirm_order'] = false;
					$booking_info_array[$bookings->id]['points_this_order'] = 0;
				}
				else if ($adjustedServerTime < $sessionTS - $confirmCutOffAdjustment)
				{
					$booking_info_array[$bookings->id]['can_confirm_order'] = false;
					$booking_info_array[$bookings->id]['points_this_order'] = CPointsUserHistory::getPointsForOrder($booking_info_array[$bookings->id]['user']->platePointsData['lifetime_points'], $bookings->order_id, $bookings->points_basis, $bookings->in_store_order);
				}
				else
				{
					$booking_info_array[$bookings->id]['can_confirm_order'] = !$bookings->points_are_actualized;

					if ($bookings->points_are_actualized)
					{
						$tempEvent = DAO_CFactory::create('points_user_history');
						$tempEvent->query("select id, points_allocated from points_user_history where event_type = 'ORDER_CONFIRMED' and is_deleted = 0 and order_id = {$bookings->order_id}");
						if ($tempEvent->N > 0)
						{
							$tempEvent->fetch();
							$booking_info_array[$bookings->id]['points_this_order'] = $tempEvent->points_allocated;
						}
						else
						{
							$booking_info_array[$bookings->id]['points_this_order'] = 0;
						}
					}
					else
					{
						$booking_info_array[$bookings->id]['points_this_order'] = CPointsUserHistory::getPointsForOrder($booking_info_array[$bookings->id]['user']->platePointsData['lifetime_points'], $bookings->order_id, $bookings->points_basis, $bookings->in_store_order);
					}
				}
			}
			else
			{
				$booking_info_array[$bookings->id]['lifetime_points'] = 0;
				$booking_info_array[$bookings->id]['pending_points'] = 0;
				$booking_info_array[$bookings->id]['platepoints_detail'] = false;
				$booking_info_array[$bookings->id]['points_this_order'] = 0;
				$booking_info_array[$bookings->id]['can_confirm_order'] = false;

				if ($bookings->preferred_somewhere)
				{
					$userObj = DAO_CFactory::create('user');
					$userObj->id = $bookings->user_id;
					$booking_info_array[$bookings->id]['conversion_data'] = CPointsUserHistory::getPreferredUserConversionData($userObj);
				}
				else if ($bookings->dream_reward_status == 1 || $bookings->dream_reward_status == 3)
				{
					$userObj = DAO_CFactory::create('user');
					$userObj->id = $bookings->user_id;
					$booking_info_array[$bookings->id]['conversion_data'] = CPointsUserHistory::getDR2ConversionData($userObj);
				}
			}
		}

		// Get the guests past booking info
		$user_info_array = CBooking::userBookingHistory(implode(',', $booked_user_ids), $this->store_id, $this->session_start);

		// add guests past info to booking array
		foreach ($booking_info_array as $id => $booking)
		{
			if (!empty($user_info_array[$booking['user_id']]))
			{
				$booking_info_array[$id] = array_merge($booking_info_array[$id], $user_info_array[$booking['user_id']]);
				$booking_info_array[$id]['this_is_first_session'] = false;
				$booking_info_array[$id]['future_session'] = $user_info_array[$booking['user_id']]['future_session'];
			}
			else if (empty($user_info_array[$booking['user_id']]['last_session_attended']))
			{
				$booking_info_array[$id]['this_is_first_session'] = true;

				$booking_info_array[$id]['future_session'] = CBooking::hasFutureBooking($booking['user_id'], $this->session_start);
			}

			if (!empty($booking['secondary_email']))
			{
				$booking_info_array[$id]['corporate_crate_client'] = CCorporateCrateClient::corporateCrateClientDetails($booking['secondary_email']);
			}
			else
			{
				$booking_info_array[$id]['corporate_crate_client'] = false;
			}
		}

		return $booking_info_array;
	}

	function getBookingsForSession()
	{
		$bookings = DAO_CFactory::create('booking');

		$q = "SELECT OQ.*,
				ftss.id AS food_testing_survey_submission_id,
				ftss.food_testing_survey_id,
				ft.title AS food_testing_title,
				ftss.serving_size AS food_testing_size
				FROM (SELECT
					GROUP_CONCAT(ud.user_data_field_id SEPARATOR '||') AS user_data_field_ids,
					GROUP_CONCAT(ud.user_data_value SEPARATOR '||') AS user_data_values,
					up.preferred_type,
					up.preferred_value,
					up.user_preferred_start,
					if (up_global.preferred_type IS NULL, 0, 1) AS preferred_somewhere,
					IQ.*
					FROM (SELECT
							b.id,
							b.order_id,
							b.user_id,
							b.session_id,
							b.`status`,
							b.booking_type,
							b.no_show,
					        b.reason_for_cancellation,
							b.declined_MFY_option,
							b.declined_to_reschedule,
							u.firstname,
							u.lastname,
							u.home_store_id,
							u.primary_email,
							u.secondary_email,
							u.is_partial_account,
							u.user_type,
							u.has_opted_out_of_plate_points,
							s.menu_id,
							s.session_start,
							s.session_type,
							s.store_id,
							st.hide_carryover_notes,
							o.order_user_notes,
							o.order_admin_notes,
							o.grand_total,
							o.bundle_id,
							o.in_store_order,
							o.grand_total - (o.subtotal_all_taxes + o.subtotal_service_fee + o.subtotal_delivery_fee + o.subtotal_products) AS points_basis,
							o.points_are_actualized,
							o.is_in_plate_points_program,
							o.timestamp_created AS order_time,
					        o.type_of_order,
					        o.servings_total_count,
					        o.menu_items_total_count,
					       	 o.servings_core_total_count,
					        o.menu_items_core_total_count,
				            o.ltd_round_up_value,
					        o.is_qualifying,
					        (od.qualifying_order_id IS NOT NULL) AS is_additional,
					        o.opted_to_customize_recipes,
					        o.order_type,
							u.dream_reward_status,
							u.dream_rewards_version,
							GROUP_CONCAT(p.payment_type ORDER BY p.id) AS payment_types,
							GROUP_CONCAT(p.total_amount ORDER BY p.id) AS payment_amounts,
							GROUP_CONCAT(p.is_delayed_payment ORDER BY p.id) AS is_delayed,
							GROUP_CONCAT(ifnull(p.delayed_payment_status, 0) ORDER BY p.id) AS delayed_status,
							udi.first_session,
							udi.dream_taste_third_order_invite,
							udi.visit_count AS user_digest_visit_count
							FROM booking AS b
							INNER JOIN `user` AS u ON u.id = b.user_id
							INNER JOIN `session` AS s ON s.id = b.session_id
							INNER JOIN `store` AS st ON st.id = s.store_id
							INNER JOIN orders AS o ON o.id = b.order_id
							LEFT JOIN orders_digest AS od ON od.order_id = o.id
							LEFT JOIN payment AS p ON p.order_id = b.order_id AND p.is_deleted = '0'
							LEFT JOIN user_digest AS udi ON udi.user_id = b.user_id AND udi.is_deleted = '0'
							WHERE b.session_id = '" . $this->id . "'
							AND b.is_deleted = '0'
							GROUP BY b.id) AS IQ
					LEFT JOIN user_data AS ud ON ud.user_id = IQ.user_id AND ud.is_deleted = '0' AND (ud.store_id = IQ.store_id OR isnull(ud.store_id))
					LEFT JOIN user_preferred AS up ON up.user_id = IQ.user_id AND IQ.store_id = up.store_id AND up.is_deleted = '0'
					LEFT JOIN user_preferred AS up_global ON up_global.user_id = IQ.user_id  AND up_global.is_deleted = '0'
					GROUP BY IQ.id
					ORDER BY IQ.`status` ASC, IQ.lastname ASC) AS OQ
				LEFT JOIN food_testing_survey_submission AS ftss ON ftss.user_id = OQ.user_id AND ftss.session_id = OQ.session_id AND ftss.is_deleted = '0'
				LEFT JOIN food_testing_survey AS fts ON fts.id = ftss.food_testing_survey_id AND fts.is_deleted = '0'
				LEFT JOIN food_testing AS ft ON ft.id = fts.food_testing_id AND ft.is_deleted = '0'
				GROUP BY OQ.id
				ORDER BY OQ.`status` ASC, OQ.lastname ASC";

		$bookings->query($q);

		$expiredForEditing = false;
		$sessionTS = strtotime($this->session_start);

		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select timezone_id from store where id = {$this->store_id}");
		$storeObj->fetch();

		$MenuObj = DAO_CFactory::create('menu');
		$MenuObj->id = $this->menu_id;
		$MenuObj->find(true);
		$expiredForEditing = !$MenuObj->areSessionsOrdersEditable($storeObj);

		$adjustedServerTime = CTimezones::getAdjustedServerTimeWithTimeZoneID($storeObj->timezone_id);

		// check for orders in previous month if current day is greater than 6
		$day = date("j", $adjustedServerTime);
		$month = date("n", $adjustedServerTime);
		$year = date("Y", $adjustedServerTime);
		/*
				if ($day > 6)
				{
					$cutOff = mktime(0, 0, 0, $month, 1, $year);

					if ($sessionTS < $cutOff)
					{
						$expiredForEditing = true;
					}
				}
				else
				{
					$cutOff = mktime(0, 0, 0, $month - 1, 1, $year);

					if ($sessionTS < $cutOff)
					{
						$expiredForEditing = true;
					}
				}

				*/

		$booking_info_array = array();
		$booked_user_ids = array();

		while ($bookings->fetch())
		{
			$booked_user_ids[] = $bookings->user_id;

			$booking_info_array[$bookings->id] = $bookings->toArray();

			$booking_info_array[$bookings->id]['can_edit'] = false;

			if (defined('ORDER_EDITING_ENABLED') && ORDER_EDITING_ENABLED)
			{
				if ($expiredForEditing)
				{
					$booking_info_array[$bookings->id]['can_edit'] = false;
				}
				else if ($bookings->status == CBooking::CANCELLED)
				{
					$booking_info_array[$bookings->id]['can_edit'] = false;
				}
				else
				{
					$booking_info_array[$bookings->id]['can_edit'] = true;
				}
			}
			else
			{
				$booking_info_array[$bookings->id]['can_edit'] = false;
			}

			if ($bookings->status == CBooking::ACTIVE)
			{
				$sessionTS = strtotime($bookings->session_start);

				if ($sessionTS > $adjustedServerTime)
				{
					$bookings->status = 'PENDING';
				}
				else
				{
					$bookings->status = 'COMPLETED';
				}
			}

			switch ($bookings->status)
			{
				case CBooking::ACTIVE:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_active';
					break;
				case CBooking::PENDING:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_pending';
					break;
				case CBooking::COMPLETED:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_completed';
					break;
				case CBooking::RESCHEDULED:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_rescheduled';
					break;
				case CBooking::SAVED:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_saved';
					break;
				default:
					$booking_info_array[$bookings->id]['status_css'] = 'b_status_active';
					break;
			}

			switch ($bookings->booking_type)
			{
				case CBooking::STANDARD:
					$booking_info_array[$bookings->id]['booking_type_css'] = 'b_type_standard';
					break;
				case CBooking::INTRO:
					$booking_info_array[$bookings->id]['booking_type_css'] = 'b_type_intro';
					break;
				default:
					$booking_info_array[$bookings->id]['booking_type_css'] = 'b_type_standard';
					break;
			}

			if (!empty($bookings->no_show))
			{
				$booking_info_array[$bookings->id]['no_show'] = true;
			}
			else
			{
				$booking_info_array[$bookings->id]['no_show'] = false;
			}

			$bookings->bookingUser($booking_info_array[$bookings->id]);

			$bookings->can_reschedule($booking_info_array[$bookings->id]);

			$bookings->bookingBalanceDue($booking_info_array[$bookings->id]);

			$bookings->bookingUserData($booking_info_array[$bookings->id]);

			$bookings->leveledUpSinceLastSession($booking_info_array[$bookings->id]);

			if ($booking_info_array[$bookings->id]['user']->platePointsData['status'] == 'active')
			{
				// old array assignments from before ['user']->platePointsData was available, RCS 06-16-2014
				$booking_info_array[$bookings->id]['lifetime_points'] = $booking_info_array[$bookings->id]['user']->platePointsData['lifetime_points'];
				$booking_info_array[$bookings->id]['pending_points'] = $booking_info_array[$bookings->id]['user']->platePointsData['pending_points'];
				$booking_info_array[$bookings->id]['platepoints_detail'] = $booking_info_array[$bookings->id]['user']->platePointsData['current_level'];
				$booking_info_array[$bookings->id]['due_reward_for_current_level'] = $booking_info_array[$bookings->id]['user']->platePointsData['due_reward_for_current_level'];
				$booking_info_array[$bookings->id]['gift_display_str'] = $booking_info_array[$bookings->id]['user']->platePointsData['gift_display_str'];

				$confirmCutOffAdjustment = 0;
				if (defined('DD_SERVER_NAME') && DD_SERVER_NAME != 'LIVE')
				{
					$confirmCutOffAdjustment = 86400 * 90;
				}

				if (!$bookings->is_in_plate_points_program)
				{
					$booking_info_array[$bookings->id]['can_confirm_order'] = false;
					$booking_info_array[$bookings->id]['points_this_order'] = 0;
				}
				else if ($adjustedServerTime < $sessionTS - $confirmCutOffAdjustment)
				{
					$booking_info_array[$bookings->id]['can_confirm_order'] = false;
					$booking_info_array[$bookings->id]['points_this_order'] = CPointsUserHistory::getPointsForOrder($booking_info_array[$bookings->id]['user']->platePointsData['lifetime_points'], $bookings->order_id, $bookings->points_basis, $bookings->in_store_order);
				}
				else
				{
					$booking_info_array[$bookings->id]['can_confirm_order'] = !$bookings->points_are_actualized;

					if ($bookings->points_are_actualized)
					{
						$tempEvent = DAO_CFactory::create('points_user_history');
						$tempEvent->query("select id, points_allocated from points_user_history where event_type = 'ORDER_CONFIRMED' and is_deleted = 0 and order_id = {$bookings->order_id}");
						if ($tempEvent->N > 0)
						{
							$tempEvent->fetch();
							$booking_info_array[$bookings->id]['points_this_order'] = $tempEvent->points_allocated;
						}
						else
						{
							$booking_info_array[$bookings->id]['points_this_order'] = 0;
						}
					}
					else
					{
						$booking_info_array[$bookings->id]['points_this_order'] = CPointsUserHistory::getPointsForOrder($booking_info_array[$bookings->id]['user']->platePointsData['lifetime_points'], $bookings->order_id, $bookings->points_basis, $bookings->in_store_order);
					}

					if ($booking_info_array[$bookings->id]['can_confirm_order'] && $booking_info_array[$bookings->id]['points_this_order'] <= 0)
					{
						$booking_info_array[$bookings->id]['can_confirm_order'] = false;
					}
				}
			}
			else
			{
				$booking_info_array[$bookings->id]['lifetime_points'] = 0;
				$booking_info_array[$bookings->id]['pending_points'] = 0;
				$booking_info_array[$bookings->id]['platepoints_detail'] = false;
				$booking_info_array[$bookings->id]['points_this_order'] = 0;
				$booking_info_array[$bookings->id]['can_confirm_order'] = false;

				if ($bookings->preferred_somewhere)
				{
					$userObj = DAO_CFactory::create('user');
					$userObj->id = $bookings->user_id;
					$booking_info_array[$bookings->id]['conversion_data'] = CPointsUserHistory::getPreferredUserConversionData($userObj);
				}
				else if ($bookings->dream_reward_status == 1 || $bookings->dream_reward_status == 3)
				{
					$userObj = DAO_CFactory::create('user');
					$userObj->id = $bookings->user_id;
					$booking_info_array[$bookings->id]['conversion_data'] = CPointsUserHistory::getDR2ConversionData($userObj);
				}
			}
		}

		// Get the guests past booking info
		$user_info_array = CBooking::userBookingHistory(implode(',', $booked_user_ids), $this->store_id, $this->session_start);

		// add guests past info to booking array
		foreach ($booking_info_array as $id => $booking)
		{
			if (!empty($user_info_array[$booking['user_id']]))
			{
				$booking_info_array[$id] = array_merge($booking_info_array[$id], $user_info_array[$booking['user_id']]);
				$booking_info_array[$id]['this_is_first_session'] = false;
				$booking_info_array[$id]['future_session'] = $user_info_array[$booking['user_id']]['future_session'];
			}
			else if (empty($user_info_array[$booking['user_id']]['last_session_attended']))
			{
				$booking_info_array[$id]['this_is_first_session'] = true;

				$booking_info_array[$id]['future_session'] = CBooking::hasFutureBooking($booking['user_id'], $this->session_start);
			}

			if (!empty($booking['secondary_email']))
			{
				$booking_info_array[$id]['corporate_crate_client'] = CCorporateCrateClient::corporateCrateClientDetails($booking['secondary_email']);
			}
			else
			{
				$booking_info_array[$id]['corporate_crate_client'] = false;
			}
		}

		return $booking_info_array;
	}

	function getAdditionalOrderCount()
	{
		$additionalOrderCount = DAO_CFactory::create('session');
		$additionalOrderCount->query("select count(*) as total from session s
				inner join booking b on b.session_id = s.id  and b.is_deleted = 0
				inner join orders o on o.id = b.order_id and o.is_deleted = 0
				inner join orders_digest od on od.order_id = o.id		
				WHERE s.id = '" . $this->id . "'		
				and od.qualifying_order_id is not null
				and b.status = 'ACTIVE'");

		$total = 0;
		while ($additionalOrderCount->fetch())
		{
			$total = $additionalOrderCount->total;
		}

		return $total;
	}

	/**
	 * Determine the booking/Session type for the customer's view
	 *
	 * @param $bookingType
	 * @param $sessionType
	 * @param $sessionSubType
	 *
	 * @return string
	 */
	public static function translateTypeForCustomerView($bookingType, $sessionType, $sessionSubType)
	{
		if ($sessionType === CSession::DELIVERED)
		{
			return 'Delivered';
		}

		if ($sessionSubType === CSession::DELIVERY)
		{
			return 'Home Delivery';
		}

		if ($sessionType === CSession::SPECIAL_EVENT)
		{
			return 'Store Pickup';
		}

		if ($sessionType === CSession::STANDARD)
		{
			return 'Assemble in Store';
		}

		return '';
	}
}

?>