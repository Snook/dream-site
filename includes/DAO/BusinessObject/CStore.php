<?php
require_once 'DAO/Store.php';
require_once 'DAO/Volume_discount_type.php';
require_once 'DAO/BusinessObject/CStoreFee.php';
require_once 'DAO/BusinessObject/CStatesAndProvinces.php';

/* ------------------------------------------------------------------------------------------------
 *	Class => CStore
 *
 *	Data:
 *
 *	Methods:
 *		Create()
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

class CStore extends DAO_Store
{

	const HOURS = 'HOURS';
	const ONE_FULL_DAY = 'ONE_FULL_DAY';
	const FOUR_FULL_DAYS = 'FOUR_FULL_DAYS';

	const NULL = 'NULL';
	const FRANCHISE = 'FRANCHISE';
	const MANUFACTURER = 'MANUFACTURER';
	const DISTRIBUTION_CENTER = 'DISTRIBUTION_CENTER';

	const AVERAGE_12ITEM_PRICE = 17.50;
	const AVERAGE_12HALF_PRICE = 14.00;
	const AVERAGE_6ITEM_PRICE = 23.50;

	// preference defaults
	static $storeJobPositions = array(
		'SALES_MANAGER' => array(
			'title' => 'Sales Manager'
		),
		'STORE_MANAGER' => array(
			'title' => 'Store Manager'
		),
		'SALES_LEAD' => array(
			'title' => 'Sales Lead'
		),
		'ASSISTANT_SALES_MANAGER' => array(
			'title' => 'Assistant Sales Manager'
		),
		'ASSISTANT_STORE_MANAGER' => array(
			'title' => 'Assistant Store Manager'
		),
		'ASSISTANT_OPS_MANAGER' => array(
			'title' => 'Assistant Operations Manager'
		),
		'OPS_MANAGER' => array(
			'title' => 'Operations Manager'
		),
		'BUSINESS_DEVELOPMENT_COORDINATOR' => array(
			'title' => 'Business Development Coordinator'
		),
		'GUEST_SERVER' => array(
			'title' => 'Guest Server'
		),
		'OPS_SUPPORT' => array(
			'title' => 'Operations Support'
		),
		'DISHWASHER' => array(
			'title' => 'Dishwasher'
		)
	);

	private $_markupObj = null; //cache me
	private $_markupFetched = false;
	private $_markupMultiObj = null; // cache me also
	private $_markupMultiFetched = false;

	// This instance of a store object is instantiated early in the page access if the user is a franchise accessor (Owner, Manager or Staff)
	// and can be used by any admin page logic class, it will be set to the store currently selected by the user.
	static private $_currentFadminStore = null;
	static private $_simpleMapsArray = array();

	private $customization_fees = null;

	public $map_link;
	public $address_linear;
	public $address_with_breaks;
	public $address_html;
	/**
	 * @var array|mixed
	 */
	private $PersonnelArray;
	/**
	 * @var array|mixed
	 */
	private $OwnerArray;
	/**
	 * @var array
	 */
	private $ActivePromoArray;

	function __construct()
	{
		parent::__construct();
	}

	function fetch()
	{
		$res = parent::fetch();

		if ($res)
		{
			$this->digestStore();
		}

		return $res;
	}

	/**
	 * This allows you to enter a string as the store ID in order to look up the stores short url
	 *
	 * @param $n
	 *
	 * @return bool|int|mixed
	 * @throws Exception
	 */
	function find_DAO_store($n = false)
	{
		// you can set the ID as a short url string, if it is not just a number
		if (!empty($this->id) && !is_numeric($this->id) && CTemplate::isAlphaNumHyphen($this->id))
		{
			$find_DAO_short_url = DAO_CFactory::create('short_url', true);
			$find_DAO_short_url->page = 'location';
			$find_DAO_short_url->short_url = $this->id;
			// look up past short urls
			$find_DAO_short_url->unsetProperty('is_deleted');
			$this->joinAddWhereAsOn($find_DAO_short_url, 'INNER', 'find_short_url', false, false); // find on this short url

			// this id was a short url string, so unset it
			$this->id = null;
		}

		$this->joinAddWhereAsOn(DAO_CFactory::create('short_url', true), 'LEFT'); // stores current not deleted short url
		$this->joinAddWhereAsOn(DAO_CFactory::create('timezones', true));

		return parent::find($n);
	}

	function digestStore()
	{
		$this->generateMapLink();
		$this->generateAddressLinear();
		$this->generateAddressWithBreaks();
		$this->generateAddressHTML();
	}

	function getPrettyUrl($full_url = false)
	{
		if(!empty($this->DAO_short_url) && !empty($this->DAO_short_url->short_url))
		{
			$store_short_url = $this->DAO_short_url->getPrettyUrl($full_url);
		}
		else
		{
			$store_short_url = ($full_url ? HTTPS_BASE : WEB_BASE) . "location/" . $this->id;
		}

		return $store_short_url;
	}

	static function setUpFranchiseStore($store_id)
	{

		//set the current store name
		self::$_currentFadminStore = DAO_CFactory::create('store');
		self::$_currentFadminStore->id = $store_id;
		if (!self::$_currentFadminStore->find(true))
		{
			throw new Exception('Franchisee store not found.');
		}
	}

	/*
	 * Pass in store ID or fully loaded store object
	 */
	static function getParentStoreID($store)
	{
		// store id required
		$store_id = false;
		// If the passed in variable is not an object, it is the timezone_id
		if (is_object($store))
		{
			$store_id = $store->parent_store_id;
		}
		else
		{
			$storeObj = new DAO();
			$storeObj->query("select parent_store_id from store where id = $store");
			$storeObj->fetch();
			$store_id = $storeObj->parent_store_id;
		}

		return $store_id;
	}

	static function active_Distribution_Centers()
	{
		$DAO_store = DAO_CFactory::create('store');
		$DAO_store->store_type = CStore::DISTRIBUTION_CENTER;
		$DAO_store->active = 1;
		$DAO_store->show_on_customer_site = 1;

		return $DAO_store->find();
	}

	static function getAvailableStoreJobArray($store_id)
	{
		$job_array = self::getStoreJobArray($store_id);

		foreach ($job_array as $job_id => $job)
		{
			if (empty($job['available']))
			{
				unset($job_array[$job_id]);
			}
		}

		return $job_array;
	}

	static function getStoreJobArray($store_id)
	{
		$store_job = DAO_CFactory::create('store_job');
		$store_job->store_id = $store_id;
		$store_job->find();

		$job_array = self::$storeJobPositions;

		while ($store_job->fetch())
		{
			$job = $store_job->toArray();

			$job_array[$store_job->position] = array_merge($job_array[$job['position']], $job);
		}

		return $job_array;
	}

	static function setAvailableJobs($store_id, $active_job_array)
	{
		// current job array settings
		$job_array = self::getStoreJobArray($store_id);

		// no jobs passed in, deactivate all in database
		if (empty($active_job_array))
		{
			$store_job = DAO_CFactory::create('store_job');
			$store_job->store_id = $store_id;
			$store_job->find();

			while ($store_job->fetch())
			{
				$store_job_update = clone($store_job);
				$store_job_update->available = 0;
				$store_job_update->update($store_job);
			}

			return self::getStoreJobArray($store_id);
		}

		// all jobs being passed in are set to available
		foreach ($active_job_array as $job_id => $job)
		{
			$store_job = DAO_CFactory::create('store_job');
			$store_job->store_id = $store_id;
			$store_job->position = $job_id;

			if (!$store_job->find(true))
			{
				$store_job->available = 1;
				$store_job->insert();
			}
			else
			{
				$store_job_update = clone($store_job);
				$store_job_update->available = 1;
				$store_job_update->update($store_job);
			}
		}

		// check through the current job and deactivate any active ones that were not passed in
		if (!empty($job_array))
		{
			foreach ($job_array as $job_id => $job)
			{
				// the job is not in the new array but was previously set to available, so now we de-available it
				if (!key_exists($job_id, $active_job_array))
				{
					$store_job = DAO_CFactory::create('store_job');
					$store_job->store_id = $store_id;
					$store_job->position = $job_id;
					$store_job->available = 1;

					if ($store_job->find(true))
					{
						$store_job->available = 0;
						$store_job->update();
					}
				}
			}
		}

		return self::getStoreJobArray($store_id);
	}

	static function getFranchiseStore()
	{
		$store_id = CBrowserSession::getCurrentFadminStoreID();

		if (!empty($store_id) && is_numeric($store_id))
		{
			self::setUpFranchiseStore($store_id);
		}

		if (isset(self::$_currentFadminStore->id))
		{
			return self::$_currentFadminStore;
		}

		//throw new Exception('Franchisee store not found.');
		return null;
	}

	static function getTestStoresArray($menu_id)
	{
		if ($menu_id < 251)
		{
			return array(
				200,
				159,
				291
			);
		}
		else
		{
			return array();
		}
	}

	//This impacts showing the menu wheel menu instead of
	//the standard menu
	static function isTestMenuStore($menu_id, $store_id)
	{
		$arrayForMenu = self::getTestStoresArray($menu_id);

		if (in_array($store_id, $arrayForMenu))
		{
			return true;
		}

		return false;
	}

	function storeSupportsIntroOrders($menu_id = false)
	{
		if (empty($this->supports_intro_orders))
		{
			return false;
		}

		if ($menu_id)
		{
			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->id = $menu_id;

			if (!$DAO_menu->isEnabled_Starter_Pack($this))
			{
				return false;
			}
		}

		return true;
	}

	static function storeSupportsStoreSpecificDeposit($store_id, $menu_id = 0)
	{
		return true;
	}

	static function storeSupportsReciProfity($store_id, $menu_id = 0)
	{

		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'LIVE')
		{
			//SLC, MC & Corvallis support all menus
			if (in_array($store_id, array(
				244,
				194,
				119,
				308
			)))
			{
				return true;
			}
		}
		else // true for all but LIVE
		{
			return true;
		}

		//other stores support December and beyond
		if ($menu_id > 244)
		{
			return true;
		}

		return false;
	}

	function hasBioPage()
	{
		if ($this->hasBioPrimary() || $this->hasBioSecondary() || $this->hasBioTeam())
		{
			return true;
		}

		return false;
	}

	function hasBioPrimary()
	{
		if (!empty($this->bio_primary_party_name))
		{
			return true;
		}

		return false;
	}

	function hasBioSecondary()
	{
		if (!empty($this->bio_secondary_party_name))
		{
			return true;
		}

		return false;
	}

	function hasBioTeam()
	{
		if (!empty($this->bio_team_description))
		{
			return true;
		}

		return false;
	}

	static function hasPlatePointsTransitionPeriodExpired($store_id)
	{

		switch ($store_id)
		{

			case 119:
				{
					$cutOff = strtotime('2014-03-01 03:00:00');
					if (time() > $cutOff)
					{
						return true;
					}

					return false;
				}
				break;
			case 244:
			case 182:
			case 200:
				{
					$cutOff = strtotime('2014-04-01 03:00:00');
					if (time() > $cutOff)
					{
						return true;
					}

					return false;
				}
				break;
			case 159:
				{
					$cutOff = strtotime('2014-05-01 03:00:00');
					if (time() > $cutOff)
					{
						return true;
					}

					return false;
				}
				break;
			case 279:
			case 300:
			case 193:
				{
					$cutOff = strtotime('2014-06-01 03:00:00');
					if (time() > $cutOff)
					{
						return true;
					}

					return false;
				}
				break;
			default:
			{
				$cutOff = strtotime('2014-07-01 03:00:00');
				if (time() > $cutOff)
				{
					return true;
				}

				return false;
			}
		}
	}

	static function setStoreSessionTypeDescriptions($StoreObj, $DescriptionsArray)
	{
		$existingEntries = array();
		$existingEntry = DAO_CFactory::create('site_message');
		$existingEntry->query("SELECT *
			FROM site_message_to_store AS smts
			INNER JOIN site_message AS sm ON sm.id = smts.site_message_id
				AND sm.audience = 'STORE'
				AND sm.is_deleted = 0
				AND sm.message_type = 'SESSION_TYPE_DESC'
				AND sm.home_office_managed = 0
			WHERE smts.store_id = '" . $StoreObj->id . "'
			AND smts.is_deleted = 0
			GROUP BY sm.id
			ORDER BY sm.message_end ASC");

		while ($existingEntry->fetch())
		{
			$existingEntries[$existingEntry->title] = clone($existingEntry);
		}

		foreach ($DescriptionsArray as $title => $message)
		{
			if (isset($existingEntries[$title]))
			{
				$org = clone($existingEntries[$title]);
				$existingEntries[$title]->message = $message;
				$existingEntries[$title]->update($org);
			}
			else
			{
				$siteMessage = DAO_CFactory::create('site_message');
				$siteMessage->title = $title;
				$siteMessage->message_type = 'SESSION_TYPE_DESC';
				$siteMessage->message = stripslashes($message);
				$siteMessage->message_start = date("Y-m-d H:i:s");
				$siteMessage->message_end = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2035));

				$siteMessage->is_active = 1;
				$siteMessage->home_office_managed = 0;
				$siteMessage->audience = 'STORE';

				$siteMessage->insert();

				$storeMapping = DAO_CFactory::create('site_message_to_store');
				$storeMapping->store_id = $StoreObj->id;
				$storeMapping->site_message_id = $siteMessage->id;

				$storeMapping->insert();
			}
		}
	}

	static function getStoreSessionTypeDescriptions($Store)
	{
		if (!is_object($Store) && is_numeric($Store))
		{
			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->id = $Store;
			$StoreObj->find(true);
		}
		else
		{
			$StoreObj = $Store;
		}

		$Message = DAO_CFactory::create('site_message');
		$Message->query("SELECT
			sm.id,
			sm.title,
			sm.message
			FROM site_message_to_store AS smts
			INNER JOIN site_message AS sm ON sm.id = smts.site_message_id
				AND sm.audience = 'STORE'
				AND sm.is_deleted = 0
				AND sm.message_type = 'SESSION_TYPE_DESC'
				AND sm.home_office_managed = 0
			WHERE smts.store_id = '" . $StoreObj->id . "'
			AND smts.is_deleted = 0
			GROUP BY sm.id
			ORDER BY sm.message_end ASC");

		$message_array = array();

		while ($Message->fetch())
		{
			$message_array[$Message->title] = $Message->message;
		}

		if (!empty($message_array))
		{
			return $message_array;
		}
		else
		{
			return false;
		}
	}

	function getActivePromoArray()
	{
		$this->ActivePromoArray = array();

		$DAO_site_message = DAO_CFactory::create('site_message', true);
		$DAO_site_message->query("(SELECT
			site_message.*
			FROM site_message
			INNER JOIN site_message_to_store ON site_message.id = site_message_to_store.site_message_id AND site_message_to_store.store_id = '" . $this->id . "' AND site_message_to_store.is_deleted = 0 
			WHERE site_message.audience = 'STORE'
			AND site_message.message_start <= NOW()
			AND site_message.message_end >= NOW()
			AND site_message.is_active = 1
			AND site_message.is_deleted = 0
			AND site_message.message_type = 'SITE_MESSAGE'
			AND site_message.home_office_managed = 1
			GROUP BY site_message.id
			ORDER BY site_message.message_end ASC)
		UNION (SELECT
			site_message.*
			FROM site_message
			INNER JOIN site_message_to_store ON site_message.id = site_message_to_store.site_message_id AND site_message_to_store.store_id = '" . $this->id . "' AND site_message_to_store.is_deleted = 0 
			WHERE site_message.audience = 'STORE'
			AND site_message.message_start <= NOW()
			AND site_message.message_end >= NOW()
			AND site_message.is_active = 1
			AND site_message.is_deleted = 0
			AND site_message.message_type = 'SITE_MESSAGE'
			AND site_message.home_office_managed = 0
			GROUP BY site_message.id
			ORDER BY site_message.message_end ASC
			LIMIT 2)");

		while ($DAO_site_message->fetch())
		{
			$this->ActivePromoArray[] = clone $DAO_site_message;
		}

		return $this->ActivePromoArray;
	}

	static function storeInPlatePointsTest($store_id)
	{

		return true;
		/*
		$eligible_stores = array(119, 159, 182, 200, 244, 279, 300, 193);

		if (in_array($store_id, $eligible_stores))
		{
			return true;
		}

		return false;
		*/
	}

	static function storeSupportsPlatePoints($store)
	{
		if (is_array($store) && isset($store['supports_plate_points']))
		{
			return $store['supports_plate_points'];
		}

		if (is_object($store) && isset($store->supports_plate_points))
		{
			return $store->supports_plate_points;
		}

		if (is_numeric($store))
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select supports_plate_points from store where id = $store");
			$storeObj->fetch();

			return $storeObj->supports_plate_points;
		}
	}

	static function storeSupportsMembership($store)
	{
		if (is_array($store) && isset($store['supports_membership']))
		{
			return $store['supports_membership'];
		}

		if (is_object($store) && isset($store->supports_membership))
		{
			return $store->supports_membership;
		}

		if (is_numeric($store))
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select supports_membership from store where id = $store");
			$storeObj->fetch();

			return $storeObj->supports_membership;
		}
	}

	/**
	 * Static method to get the attribute of a store indicating if meal customization is enabled
	 *
	 * @param $store
	 *
	 * @return mixed|void true if the store has meal/recipe customization enabled
	 * @throws Exception
	 */
	static function storeSupportsMealCustomization($store)
	{
		if (is_array($store) && isset($store['supports_meal_customization']))
		{
			return $store['supports_meal_customization'];
		}

		if (is_object($store) && isset($store->supports_meal_customization))
		{
			return $store->supports_meal_customization;
		}

		if (is_numeric($store))
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select supports_meal_customization from store where id = $store");
			$storeObj->fetch();

			return $storeObj->supports_meal_customization;
		}
	}

	static function storeSupportsZeroCoreMinimum($store)
	{
		$applicableStoreIds = array(73);
		$storeId = null;

		if (is_object($store))
		{
			$storeId = $store->id;
		}

		if (is_numeric($store))
		{
			$storeId = $store;
		}

		if( in_array($storeId, $applicableStoreIds))
		{
			return true;
		}

		return false;
	}

	static function getSimpleMapsStoreArray()
	{
		if (!empty(self::$_simpleMapsArray))
		{
			return self::$_simpleMapsArray;
		}

		$simpleMapsArray = array(
			'main_settings' => array(
				//General settings
				'width' => "responsive",
				//'700' or 'responsive'
				'background_color' => "#FFFFFF",
				'background_transparent' => "yes",
				'border_color' => "#ffffff",
				'popups' => "detect",
				'state_description' => "",
				'state_color' => "#5c6670",
				'state_hover_color' => "#5c6670",
				'state_url' => "",
				'border_size' => "1",
				'all_states_inactive' => "no",
				'all_states_zoomable' => "yes",
				'location_description' => "",
				'location_color' => "#e87722",
				'location_opacity' => "1",
				'location_hover_opacity' => 1,
				'location_url' => "",
				'location_size' => "12",
				'location_type' => "circle",
				'location_image_source' => "frog.png",
				'location_border_color' => "#FFFFFF",
				'location_border' => 2,
				'location_hover_border' => 2.5,
				'all_locations_inactive' => "no",
				'all_locations_hidden' => "no",

				//Label defaults
				'label_color' => "#fff",
				'label_hover_color' => "#fff",
				'label_size' => 22,
				'label_font' => "Arial",
				'hide_labels' => "no",
				'hide_eastern_labels' => "no",
				'manual_zoom' => "no",
				'back_image' => "no",
				'initial_back' => "no",
				'initial_zoom' => -1,
				'initial_zoom_solo' => "no",
				'region_opacity' => 1,
				'region_hover_opacity' => 0.6,
				'zoom_out_incrementally' => "yes",
				'zoom_percentage' => 0.99,
				'zoom_time' => 0.5,

				//Popup settings
				'popup_color' => "white",
				'popup_opacity' => 0.9,
				'popup_shadow' => 1,
				'popup_corners' => 5,
				'popup_font' => "12px/1.5 Verdana, Arial, Helvetica, sans-serif",
				'popup_nocss' => "no",

				//Advanced settings
				'div' => "map",
				'auto_load' => "yes",
				'url_new_tab' => "no",
				'images_directory' => IMAGES_PATH . "/vendor/simplemaps/",
				'fade_time' => 0.1,
				'import_labels' => "no",
				'link_text' => "View Website",
				'state_image_url' => "",
				'state_image_position' => "",
				'location_image_url' => ""
			),
			'legend' => array(
				'entries' => array(
					array(
						'name' => "Assembly Kitchens",
						'color' => "#b9bf33",
						'type' => "state"
					),
					array(
						'name' => "Shipped To Your Door",
						'color' => "#45cfd1",
						'type' => "state"
					),
					array(
						'name' => "Assembly Kitchens & Shipped To Your Door",
						'color' => "#959a21",
						'type' => "state"
					)
				),
			),
			'labels' => array(
				'NH' => array(
					'parent_id' => "NH",
					'x' => "932",
					'y' => "183",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'VT' => array(
					'parent_id' => "VT",
					'x' => "883",
					'y' => "243",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'RI' => array(
					'parent_id' => "RI",
					'x' => "932",
					'y' => "273",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'NJ' => array(
					'parent_id' => "NJ",
					'x' => "883",
					'y' => "273",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'DE' => array(
					'parent_id' => "DE",
					'x' => "883",
					'y' => "303",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'MD' => array(
					'parent_id' => "MD",
					'x' => "932",
					'y' => "303",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'DC' => array(
					'parent_id' => "DC",
					'x' => "884",
					'y' => "332",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'MA' => array(
					'parent_id' => "MA",
					'x' => "932",
					'y' => "213",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'CT' => array(
					'parent_id' => "CT",
					'x' => "932",
					'y' => "243",
					'pill' => "yes",
					'width' => 45,
					'display' => "all"
				),
				'HI' => array(
					'parent_id' => "HI",
					'x' => 305,
					'y' => 565,
					'pill' => "yes"
				),
				'AK' => array(
					'parent_id' => "AK",
					'x' => "113",
					'y' => "495"
				),
				'FL' => array(
					'parent_id' => "FL",
					'x' => "773",
					'y' => "510"
				),
				'ME' => array(
					'parent_id' => "ME",
					'x' => "893",
					'y' => "85"
				),
				'NY' => array(
					'parent_id' => "NY",
					'x' => "815",
					'y' => "158"
				),
				'PA' => array(
					'parent_id' => "PA",
					'x' => "786",
					'y' => "210"
				),
				'VA' => array(
					'parent_id' => "VA",
					'x' => "790",
					'y' => "282"
				),
				'WV' => array(
					'parent_id' => "WV",
					'x' => "744",
					'y' => "270"
				),
				'OH' => array(
					'parent_id' => "OH",
					'x' => "700",
					'y' => "240"
				),
				'IN' => array(
					'parent_id' => "IN",
					'x' => "650",
					'y' => "250"
				),
				'IL' => array(
					'parent_id' => "IL",
					'x' => "600",
					'y' => "250"
				),
				'WI' => array(
					'parent_id' => "WI",
					'x' => "575",
					'y' => "155"
				),
				'NC' => array(
					'parent_id' => "NC",
					'x' => "784",
					'y' => "326"
				),
				'TN' => array(
					'parent_id' => "TN",
					'x' => "655",
					'y' => "340"
				),
				'AR' => array(
					'parent_id' => "AR",
					'x' => "548",
					'y' => "368"
				),
				'MO' => array(
					'parent_id' => "MO",
					'x' => "548",
					'y' => "293"
				),
				'GA' => array(
					'parent_id' => "GA",
					'x' => "718",
					'y' => "405"
				),
				'SC' => array(
					'parent_id' => "SC",
					'x' => "760",
					'y' => "371"
				),
				'KY' => array(
					'parent_id' => "KY",
					'x' => "680",
					'y' => "300"
				),
				'AL' => array(
					'parent_id' => "AL",
					'x' => "655",
					'y' => "405"
				),
				'LA' => array(
					'parent_id' => "LA",
					'x' => "550",
					'y' => "435"
				),
				'MS' => array(
					'parent_id' => "MS",
					'x' => "600",
					'y' => "405"
				),
				'IA' => array(
					'parent_id' => "IA",
					'x' => "525",
					'y' => "210"
				),
				'MN' => array(
					'parent_id' => "MN",
					'x' => "506",
					'y' => "124"
				),
				'OK' => array(
					'parent_id' => "OK",
					'x' => "460",
					'y' => "360"
				),
				'TX' => array(
					'parent_id' => "TX",
					'x' => "425",
					'y' => "435"
				),
				'NM' => array(
					'parent_id' => "NM",
					'x' => "305",
					'y' => "365"
				),
				'KS' => array(
					'parent_id' => "KS",
					'x' => "445",
					'y' => "290"
				),
				'NE' => array(
					'parent_id' => "NE",
					'x' => "420",
					'y' => "225"
				),
				'SD' => array(
					'parent_id' => "SD",
					'x' => "413",
					'y' => "160"
				),
				'ND' => array(
					'parent_id' => "ND",
					'x' => "416",
					'y' => "96"
				),
				'WY' => array(
					'parent_id' => "WY",
					'x' => "300",
					'y' => "180"
				),
				'MT' => array(
					'parent_id' => "MT",
					'x' => "280",
					'y' => "95"
				),
				'CO' => array(
					'parent_id' => "CO",
					'x' => "320",
					'y' => "275"
				),
				'UT' => array(
					'parent_id' => "UT",
					'x' => "223",
					'y' => "260"
				),
				'AZ' => array(
					'parent_id' => "AZ",
					'x' => "205",
					'y' => "360"
				),
				'NV' => array(
					'parent_id' => "NV",
					'x' => "140",
					'y' => "235"
				),
				'OR' => array(
					'parent_id' => "OR",
					'x' => "100",
					'y' => "120"
				),
				'WA' => array(
					'parent_id' => "WA",
					'x' => "130",
					'y' => "55"
				),
				'ID' => array(
					'parent_id' => "ID",
					'x' => "200",
					'y' => "150"
				),
				'CA' => array(
					'parent_id' => "CA",
					'x' => "79",
					'y' => "285"
				),
				'MI' => array(
					'parent_id' => "MI",
					'x' => "663",
					'y' => "185"
				),
				'PR' => array(
					'parent_id' => "PR",
					'x' => "620",
					'y' => "545"
				),
				'GU' => array(
					'parent_id' => "GU",
					'x' => "550",
					'y' => "540"
				),
				'VI' => array(
					'parent_id' => "VI",
					'x' => "680",
					'y' => "519"
				),
				'MP' => array(
					'parent_id' => "MP",
					'x' => "570",
					'y' => "575"
				),
				'AS' => array(
					'parent_id' => "AS",
					'x' => "665",
					'y' => "580"
				)
			)
		);

		$Store = DAO_CFactory::create('store');
		$Store->active = 1;
		$Store->find();

		while ($Store->fetch())
		{
			if ($Store->isActive() || $Store->isComingSoon())
			{
				if ($Store->store_type != CStore::DISTRIBUTION_CENTER)
				{
					$simpleMapsArray['locations'][$Store->id] = array(
						'lat' => $Store->address_latitude,
						'lng' => $Store->address_longitude,
						'name' => $Store->store_name,
						'url' => '?page=store&id=' . $Store->id
					);

					if ($Store->isComingSoon())
					{
						$simpleMapsArray['locations'][$Store->id]['name'] .= ' - Coming Soon!';
						$simpleMapsArray['locations'][$Store->id]['color'] = '#5c6670';
					}

					$simpleMapsArray['state_specific'][$Store->state_id] = array(
						'name' => CStatesAndProvinces::GetName($Store->state_id),
						'color' => '#b9bf33',
						'hover_color' => '#959a21'
					);
				}
			}
		}

		// get distribution center eligible states
		$zipCodes = DAO_CFactory::create('zipcodes');
		$zipCodes->query("select distinct zip.state from zipcodes as zip JOIN store AS st ON zip.distribution_center = st.id AND st.active AND st.show_on_customer_site AND st.store_type = '" . CStore::DISTRIBUTION_CENTER . "' AND st.is_deleted = 0;");

		while ($zipCodes->fetch())
		{
			// state has assembly and delivery
			if (!empty($simpleMapsArray['state_specific'][$zipCodes->state]))
			{
				$simpleMapsArray['state_specific'][$zipCodes->state]['color'] = '#959a21';
				$simpleMapsArray['state_specific'][$zipCodes->state]['hover_color'] = '#5b5e18';
			}
			// state has delivery only
			else
			{
				$simpleMapsArray['state_specific'][$zipCodes->state] = array(
					'name' => CStatesAndProvinces::GetName($zipCodes->state),
					'color' => '#45cfd1',
					'hover_color' => '#2db5b7'
				);
			}
		}

		$simpleMapsArray['state_specific']['AK']['hide'] = "yes";
		$simpleMapsArray['state_specific']['HI']['hide'] = "yes";

		return self::$_simpleMapsArray = $simpleMapsArray;
	}

	static function getStoreTreeAsNestedList($user_id = false, $get_inactive_stores = false, $via_menu_id = false)
	{
		$retVal = '<ul><li id="topNode"><a href="">All Stores</a><ul>';

		$lastState = "";

		$stateArray = CStatesAndProvinces::GetStatesArray();

		$storeObj = DAO_CFactory::create('store');

		if ($user_id)
		{
			$storeObj->query("SELECT  store.store_name, store.state_id, store.city, store.id, store.active, store.is_corporate_owned, store.store_type 
								FROM store, user_to_store WHERE user_to_store.store_id = store.id AND user_to_store.user_id = $user_id and user_to_store.is_deleted = 0");
		}
		else if ($get_inactive_stores)
		{

			if ($via_menu_id)
			{
				$storeObj->query("SELECT store_name, state_id, city, id, active, is_corporate_owned, store.store_type FROM store WHERE is_deleted = 0 and not isnull(first_menu_supported) and $via_menu_id >= first_menu_supported
					and (isnull(last_menu_supported) or $via_menu_id <= last_menu_supported )
					ORDER BY state_id, city");
			}
			else
			{
				$storeObj->query("SELECT store_name, state_id, city, id, active, is_corporate_owned, store.store_type FROM store WHERE is_deleted = 0 ORDER BY state_id, city");
			}
		}
		else
		{
			$storeObj->query("SELECT store_name, state_id, city, id, is_corporate_owned, store.store_type FROM store WHERE active = 1 and is_deleted = 0 ORDER BY state_id, city");
		}

		while ($storeObj->fetch())
		{
			if ($lastState != $storeObj->state_id)
			{
				if ($lastState !== "")
				{
					// close last state
					$retVal .= '</ul>';
				}

				$retVal .= "<li data-jstree='{\"type\":\"us_state\"}' id='tree_state_id-" . $storeObj->state_id . "'><a href='javascript:onTitleClick();'>" . $stateArray[$storeObj->state_id] . '</a><ul>';

				$lastState = $storeObj->state_id;
			}

			if ($get_inactive_stores)
			{
				if ($storeObj->store_type == CStore::DISTRIBUTION_CENTER)
				{
					if (!empty($storeObj->active))
					{
						$retVal .= "<li data-jstree='{\"type\":\"active_dist_ctr\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
					}
					else
					{
						$retVal .= "<li data-jstree='{\"type\":\"inactive_dist_ctr\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
					}
				}
				else if ($storeObj->is_corporate_owned)
				{
					if (!empty($storeObj->active))
					{
						$retVal .= "<li data-jstree='{\"type\":\"active_corp\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
					}
					else
					{
						$retVal .= "<li data-jstree='{\"type\":\"inactive_corp\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
					}
				}
				else
				{
					if (!empty($storeObj->active))
					{
						$retVal .= "<li data-jstree='{\"type\":\"active_franchise\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
					}
					else
					{
						$retVal .= "<li data-jstree='{\"type\":\"inactive_franchise\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
					}
				}
			}
			else
			{
				if ($storeObj->store_type == CStore::DISTRIBUTION_CENTER)
				{
					$retVal .= "<li data-jstree='{\"type\":\"active_dist_ctr\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
				}
				else if ($storeObj->is_corporate_owned)
				{
					$retVal .= "<li data-jstree='{\"type\":\"active_corp\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
				}
				else
				{
					$retVal .= "<li data-jstree='{\"type\":\"active_franchise\"}' id='tree_store_id-" . $storeObj->id . "'><a href='javascript:onTitleClick();'>" . $storeObj->city . ' - ' . $storeObj->store_name . '</a></li>';
				}
			}
		}

		$retVal .= '</ul></li></ul>';

		return $retVal;
	}

	/*
	 * Call this if you will be determining markup for more than 1 menu in a given lifetime of CStore
	 * (or getMarkupMultiObj will always return the cached version)
	 * Called by page/admin/prices.php
	 */
	function clearMarkupMultiObj()
	{
		$this->_markupMultiFetched = false;
		unset($this->_markupMultiObj);
	}

	static function isCoreTestStore($store_id, $menu_id)
	{
		if ($menu_id < 247)
		{
			return false;
		}
		else if ($menu_id == 247)
		{
			return in_array($store_id, array(
				159,
				200
			));
		}
		else if ($menu_id < 250)
		{
			return in_array($store_id, array(
				159,
				200,
				291
			));
		}
		else
		{
			return in_array($store_id, array(
				200,
				159,
				312,
				291,
				310,
				194,
				311
			));
		}
	}

	//UI helper to determine if this store/menu should result in the correct Menu message
	static function testStoreMenuDisplayMessage($store_id, $menu_id)
	{
		if ($menu_id < 247)
		{
			return false;
		}
		else if ($menu_id == 247)
		{
			return in_array($store_id, array(
				159,
				200
			));
		}
		else if ($menu_id < 250)
		{
			return in_array($store_id, array(
				159,
				200,
				291
			));
		}
		else if ($menu_id > 256)
		{
			return in_array($store_id, array(
				28,
				29,
				30,
				54,
				62,
				63,
				67,
				80,
				82,
				96,
				99,
				101,
				102,
				103,
				105,
				108,
				119,
				121,
				127,
				133,
				136,
				158,
				159,
				165,
				166,
				171,
				175,
				181,
				194,
				200,
				204,
				208,
				215,
				229,
				239,
				244,
				262,
				274,
				281,
				288,
				291,
				302,
				307,
				308,
				309
			));
		}
		else
		{
			return in_array($store_id, array(
				28,
				54,
				96,
				99,
				101,
				119,
				159,
				181,
				194,
				200,
				244,
				291,
				302
			));
		}
	}

	//will only return true if is test store for all menus in array
	static function isCoreTestStoreAcrossMenus($store_id, $menu_id_array)
	{

		foreach ($menu_id_array as $menu_id)
		{
			if ($menu_id < 247)
			{
				return false;
			}
		}

		$isTest = false;
		foreach ($menu_id_array as $menu_id)
		{
			if ($menu_id == 247)
			{
				$isTest = in_array($store_id, array(
					159,
					200
				));
			}

			if (in_array($menu_id, array(
				248,
				249,
				250,
				251
			)))
			{
				$isTest = in_array($store_id, array(
					159,
					200,
					291
				));
			}
		}

		return $isTest;
	}

	static function userHasAccessToStore($store_id)
	{
		$User = CUser::getCurrentUser();

		if ($User->user_type == CUser::SITE_ADMIN || $User->user_type == CUser::HOME_OFFICE_STAFF || $User->user_type == CUser::HOME_OFFICE_MANAGER)
		{
			return true;
		}

		if (!empty($User->accessToStore[$store_id]))
		{
			return true;
		}

		$UTS = DAO_CFactory::create('user_to_store');
		$UTS->user_id = $User->id;
		$UTS->store_id = $store_id;

		if ($UTS->find(false))
		{
			return $User->accessToStore[$store_id] = true;
		}

		return $User->accessToStore[$store_id] = false;
	}

	/**
	 * For transition to combined meal plan features
	 * (temporarily set to Everett,Raleigh, )
	 */
	static function isNewPricingPlanStore($store_id, $menu_id)
	{
		return $menu_id > 71;
	}

	static function userHasAccessToDistributionCenter($store_id, $orderState = false)
	{
		$User = CUser::getCurrentUser();

		if ($User->user_type == CUser::SITE_ADMIN || $User->user_type == CUser::HOME_OFFICE_STAFF || $User->user_type == CUser::HOME_OFFICE_MANAGER)
		{
			return true;
		}

		if (!empty($User->accessToStore[$store_id]))
		{
			return true;
		}

		$UTS = DAO_CFactory::create('user_to_store');
		$UTS->user_id = $User->id;
		$UTS->store_id = $store_id;

		if ($UTS->find(false))
		{
			return $User->accessToStore[$store_id] = true;
		}

		$User->accessToStore[$store_id] = false;

		// finally, one more test to see if this is a new Delivered order
		if (($orderState == 'NEW' || $orderState == 'SAVED') && ($User->user_type != CUser::CUSTOMER && $User->user_type != CUser::GUEST))
		{
			return true;
		}

		return false;
	}

	function getCustomerCalendarArray($sessionTypesArray = array(
		CSession::INTRO,
		CSession::ALL_STANDARD,
		CSession::EVENT
	),$excludeMenuIds = false, $UserObj = false, $excludeWalking = false)
	{
		if (empty($UserObj))
		{
			$UserObj = CUser::getCurrentUser();
		}

		$menuArray['menus'] = CMenu::getActiveMenuArray();

		$canOrderIntro = true;

		if ($UserObj->isLoggedIn())
		{
			if (!$UserObj->isNewBundleCustomer())
			{
				$canOrderIntro = false;
			}
		}

		if (!$this->storeSupportsIntroOrders())
		{
			$canOrderIntro = false;
		}

		if (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS)
		{
			$canOrderIntro = true;
		}

		if (!$canOrderIntro)
		{
			// no need to make intro query if they can't attend them
			unset($sessionTypesArray[CSession::INTRO]);
		}

		$sessionArray = array(
			'info' => array('session_type' => array()),
			'sessions' => array()
		);
		$totalSessionCount = 0;

		foreach ($menuArray['menus'] as $mid => $menu)
		{
			if (is_array($excludeMenuIds) && in_array($mid, $excludeMenuIds))
			{
				continue;
			}

			foreach ($sessionTypesArray as $sessionType)
			{
				$sessionsArray = CSession::getMonthlySessionInfoArray($this, $menu['start_date'], $mid, $sessionType, true, true, false, false, false,false, $excludeWalking);

				if (!array_key_exists($sessionType, $sessionArray['info']['session_type']))
				{
					$sessionArray['info']['session_type'][$sessionType] = 0;
				}
				$sessionArray['info']['session_type'][$sessionType] += $sessionsArray['info']['session_type'][$sessionType];

				$sessionArray['sessions'][$sessionType][$mid]['session'] = $sessionsArray;
				$sessionArray['sessions'][$sessionType][$mid]['session_info'] = array();
				$sessionArray['sessions'][$sessionType][$mid]['session_info']['session_count'] = 0;
				$sessionArray['sessions'][$sessionType][$mid]['menu_info'] = $menuArray['menus'][$mid];

				if (!empty($sessionsArray['sessions']))
				{
					foreach ($sessionsArray['sessions'] as $date => $session)
					{
						$sessionArray['sessions'][$sessionType][$mid]['session_info']['session_count'] += $session['info']['session_count'];
						$totalSessionCount += $session['info']['session_count'];
					}
				}

				if (!empty($sessionsArray['sessions']))
				{
					foreach ($sessionsArray['sessions'] as $date => $session)
					{
						$sessionArray['sessions'][$sessionType][$mid]['session_info']['session_count'] += $session['info']['session_count'];
					}
				}
			}
		}

		return $sessionArray;
	}

	// Used to build dropdown of states with active stores
	static function getStateListOfActiveStores()
	{
		$store = DAO_CFactory::create('store');

		$store->query("SELECT DISTINCT store.state_id, state_province.state_name FROM store JOIN state_province ON store.state_id = state_province.id  WHERE active = 1 AND is_deleted = 0");

		$retVal = array();

		while ($store->fetch())
		{
			$retVal[$store->state_id] = $store->state_name;
		}

		return $retVal;
	}

	function getStoreImageName()
	{
		$testPath = APP_BASE . "www" . IMAGES_PATH . "/stores/" . $this->id . '.jpg';
		$testPath = str_replace(HIGH_CAP_IMAGE_BASE, '/', $testPath); // always check relative path

		if (file_exists($testPath))
		{
			return $this->id;
		}

		return 'default';
	}

	static function getStorePersonnel($store_id)
	{
		$userArray = array();

		$User = DAO_CFactory::create('user');
		$User->query("SELECT
			u.id,
			u.firstname,
			u.lastname,
			u.primary_email,
			u.user_type,
			u.fadmin_nda_agree,
			u.last_login,
			uts.display_to_public
			FROM `user` AS u
			INNER JOIN user_to_store AS uts ON uts.user_id = u.id AND uts.is_deleted = '0'
			WHERE uts.store_id = '" . $store_id . "'
			AND u.is_deleted = '0'
			ORDER BY u.user_type DESC, u.firstname ASC");

		while ($User->fetch())
		{
			$userArray[$User->id] = $User->toArray();
		}

		return $userArray;
	}

	function getPersonnelArray()
	{
		$this->PersonnelArray = array();

		$DAO_user = DAO_CFactory::create('user');
		$DAO_user_to_store = DAO_CFactory::create('user_to_store');
		$DAO_user_to_store->store_id = $this->id;
		$DAO_user->joinAddWhereAsOn($DAO_user_to_store);
		$DAO_user->oderBy("user.user_type DESC, user.firstname ASC");
		$DAO_user->find();

		while($DAO_user->fetch())
		{
			$this->PersonnelArray[$DAO_user->id] = clone $DAO_user;
		}
	}

	function getOwnerArray()
	{
		$this->OwnerArray = array();

		$DAO_user = DAO_CFactory::create('user');
		$DAO_user->user_type = CUser::FRANCHISE_OWNER;
		$DAO_user_to_store = DAO_CFactory::create('user_to_store');
		$DAO_user_to_store->store_id = $this->id;
		$DAO_user->joinAddWhereAsOn($DAO_user_to_store);
		$DAO_user->oderBy("user.user_type DESC, user.firstname ASC");
		$DAO_user->find();

		while($DAO_user->fetch())
		{
			$this->OwnerArray[$DAO_user->id] = clone $DAO_user;
		}
	}

	static function getStoreAndOwnerInfo($store_id)
	{
		//get store info
		$storeInfo = array();
		$ownerInfo = array();

		if (!empty($store_id) && is_numeric($store_id))
		{
			$store = DAO_CFactory::create('store');
			$store->query("SELECT
					s.*,
					CONCAT(s.address_line1, IF(s.address_line2 IS NULL OR s.address_line2 = '', '', CONCAT(' ', s.address_line2)), ', ', s.city, ', ', s.state_id, ' ', s.postal_code, IF(s.usps_adc IS NULL OR s.usps_adc = '', '', CONCAT('-', s.usps_adc))) AS linear_address
					FROM store AS s
					WHERE s.id = '" . $store_id . "'
					AND s.is_deleted = '0'");

			if ($store->fetch())
			{

				$uts = DAO_CFactory::create('user_to_store');
				$User = DAO_CFactory::create('user');

				$uts->store_id = $store_id;
				$uts->joinAdd($User);
				$uts->whereAdd("user_to_store.display_to_public = '1' AND user.user_type != '" . CUser::CUSTOMER . "'");
				$uts->orderBy("user.user_type DESC, user_to_store.display_text ASC");
				$uts->find();

				$ownerInfo = array();

				while ($uts->fetch())
				{
					$ownerInfo[$uts->id] = $uts->toArray();

					if (!empty($uts->display_text))
					{
						$ownerInfo[$uts->id]['name'] = htmlentities($uts->display_text);
					}
					else
					{
						$ownerInfo[$uts->id]['name'] = $uts->firstname . ' ' . $uts->lastname;
					}
				}

				$storeInfo = $store->toArray();
				$storeInfo['country'] = CStatesAndProvinces::getCountryName($store->country_id);
				$storeInfo['map'] = $store->generateMapLink();
				$storeInfo['image_name'] = $store->getStoreImageName();
				$storeInfo['coming_soon'] = $store->isComingSoon();
				$storeInfo['job_positions_available'] = self::getAvailableStoreJobArray($store_id);

				return array(
					$storeInfo,
					$ownerInfo,
					$store
				);
			}
			else
			{
				return false;
			}
		}
	}

	static function getSiteNotices($notice_id = false, $store_id = false)
	{
		$notice_query = "";
		if ($notice_id)
		{
			$notice_query = " AND sm.id = '" . $notice_id . "'";
		}
		$join_store_query = " LEFT JOIN site_message_to_store AS smts ON smts.site_message_id = sm.id AND smts.is_deleted = '0'";
		if ($store_id)
		{
			$join_store_query = " INNER JOIN site_message_to_store AS smts ON smts.site_message_id = sm.id AND smts.is_deleted = '0' AND smts.store_id = '" . $store_id . "'";
		}

		$Maint = DAO_CFactory::create('site_message');
		$Maint->query("SELECT
			sm.*,
			st.store_name,
			GROUP_CONCAT(smts.store_id) AS store_id
			FROM site_message AS sm
			" . $join_store_query . "
			LEFT JOIN store AS st ON st.id = smts.store_id
			WHERE sm.is_deleted = '0' and sm.message_type <> 'SESSION_TYPE_DESC'
			" . $notice_query . "
			GROUP BY sm.id
			ORDER BY sm.message_end ASC");

		$maintenance_array = array();

		while ($Maint->fetch())
		{
			$maintenance_array[$Maint->id] = $Maint->toArray();
		}

		return $maintenance_array;
	}

	static function getSiteNoticeMenu($active_only = false, $by_state = true, $fields = false)
	{
		$Maint = DAO_CFactory::create('site_message');
		$Maint->query("SELECT
			st.id,
			st.store_name
			FROM site_message AS sm
			LEFT JOIN site_message_to_store AS smts ON smts.site_message_id = sm.id AND smts.is_deleted = '0'
			LEFT JOIN store AS st ON st.id = smts.store_id
			WHERE sm.is_deleted = '0' and sm.message_type <> 'SESSION_TYPE_DESC'
		  	AND sm.home_office_managed = '0'
			GROUP BY st.id
			ORDER BY st.store_name ASC");

		$maintenance_array = array();

		while ($Maint->fetch())
		{
			$maintenance_array[] = $Maint->id;
		}

		return self::getListOfStores($active_only, $by_state, $fields, $maintenance_array);
	}

	static function getListOfStores($active_only = false, $by_state = true, $fields = false, $storeInArray = false)
	{
		$activeQuery = '';
		$storeIdQuery = '';

		if ($storeInArray)
		{
			$storeIdQuery = " AND s.id IN('" . implode("','", $storeInArray) . "')";
		}

		if ($active_only)
		{
			$activeQuery = ' AND s.active = 1';
		}

		if ($fields)
		{
			$selectFields = $fields;
		}
		else
		{
			$selectFields = "s.*";
		}

		$store = DAO_CFactory::create('store');
		$store->query("SELECT 
			" . $selectFields . ",
 			sp.state_name
 			FROM store AS s
 			JOIN state_province AS sp ON s.state_id = sp.id
 			WHERE s.is_deleted = 0
 			" . $activeQuery . "
 			" . $storeIdQuery . "
 			ORDER BY s.state_id, s.city");

		$retVal = array();

		while ($store->fetch())
		{
			$compressedStoreArray = DAO::getCompressedArrayFromDAO($store, true);

			$store->state_name = str_replace(" ", "_", $store->state_name);

			if ($by_state)
			{
				$retVal[$store->state_name]['stores'][$store->id] = $compressedStoreArray;

				if (empty($retVal[$store->state_name]['info']['has_active']) || !array_key_exists('has_active', $retVal[$store->state_name]['info']))
				{
					$retVal[$store->state_name]['info']['has_active'] = false;
				}

				if (empty($retVal[$store->state_name]['info']['has_active']) && !empty($compressedStoreArray['active']))
				{
					$retVal[$store->state_name]['info']['has_active'] = true;
				}
			}
			else
			{
				$retVal[$store->id] = $compressedStoreArray;
			}
		}

		return $retVal;
	}

	/**
	 * Does a find ordered by stateprov
	 */
	function findByStateProv()
	{
		$this->orderBy(' state_id ASC, city ASC, store_name ASC');

		return $this->find();
	}

	/**
	 * Returns the current mark up price,
	 * @return array of (value, type, start) the current markup price for this store
	 */
	function getMarkUp($menuId)
	{
		$MarkUp = $this->getMarkUpObj($menuId);
		if ($MarkUp)
		{
			return array(
				$MarkUp->markup_value,
				$MarkUp->markup_type,
				$MarkUp->mark_up_start
			);
		}

		return array(
			0,
			null,
			null
		);
	}

	function getStorePickupLocations()
	{
		$pickupLocation = DAO_CFactory::create('store_pickup_location');
		$pickupLocation->store_id = $this->id;
		$pickupLocation->find();

		$this->remoteLocations = array();

		while ($pickupLocation->fetch())
		{
			$this->remoteLocations[$pickupLocation->id] = clone($pickupLocation);
		}

		return $this->remoteLocations;
	}

	function generateStoreImagePath()
	{
		$emailParts = explode("@", $this->email_address);
		$basis = array_shift($emailParts);
		$testPath = "/stores/" . strtolower($basis) . "_thm.gif";
		$szFullFileName = APP_BASE . "www/theme/" . THEME . "/images" . $testPath;
		if (file_exists($szFullFileName))
		{
			return $testPath;
		}

		return "/stores/default_store_image.gif";
	}

	function generateStoreZoomImagePath()
	{
		$emailParts = explode("@", $this->email_address);
		$basis = array_shift($emailParts);
		$testPath = "/stores/" . strtolower($basis) . "_zoom.gif";
		$szFullFileName = APP_BASE . "www/theme/" . THEME . "/images" . $testPath;
		if (file_exists($szFullFileName))
		{
			return $testPath;
		}

		return null;
	}

	function getAveragePrice($menuId, $priceConst)
	{
		//	throw new Exception('CStore::getAveragePrice is actually called so better deal with it');

		$MarkUp = $this->getMarkUpObj($menuId);

		if (!$MarkUp)
		{
			return $priceConst;
		}

		switch ($MarkUp->markup_type)
		{
			case CMarkUp::FLAT:
				return $priceConst + $MarkUp->markup_value;

			case CMarkUp::PERCENTAGE:
				return $priceConst + COrders::std_round(($priceConst * $MarkUp->markup_value) / 100, 2);

			default:
				throw new Exception('unknown markup type');
				break;
		}
	}

	/**
	 * Returns the current mark up price,
	 * @return markup data object
	 */
	function getMarkUpObj($menuId)
	{
		if (!$this->id)
		{
			throw new Exception('store id not set for mark up lookup');
		}

		if (!$this->_markupFetched)
		{
			$MarkUp = DAO_CFactory::create('mark_up');
			$MarkUp->store_id = $this->id;
			$num = $MarkUp->findActive($menuId);
			if ($num)
			{
				$MarkUp->fetch();
				$this->_markupObj = $MarkUp;
			}

			$this->_markupFetched = true;
		}

		//we could possibly get more than one if someone places an order simultaneously while the owner
		//sets a new mark up value, so we'll just use the most recent one
		//			if ( $rslt && ($rslt > 1) ) {
		//				throw new exception('store has more than one current mark up');
		//			}

		return $this->_markupObj;
	}

	/**
	 * Returns the current mark up price,
	 * @return markup data object
	 */
	function getMarkUpMultiObj($menuId)
	{
		if (!$this->id)
		{
			throw new Exception('store id not set for mark up lookup');
		}

		if (!$this->_markupMultiFetched)
		{
			$MarkUp = DAO_CFactory::create('mark_up_multi');
			$MarkUp->store_id = $this->id;
			$num = $MarkUp->findActive($menuId);
			if ($num)
			{
				$MarkUp->fetch();

				//				$DAO_order_minimum = DAO_CFactory::create('order_minimum');
				//				$DAO_order_minimum->menu_id = $menuId;
				//				$DAO_order_minimum->store_id = $this->id;
				//				$DAO_order_minimum->find(true);

				$DAO_order_minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE, $this->id, $menuId);

				if ($DAO_order_minimum->isZeroDollarAssembly())
				{
					$MarkUp->setZeroDollarAssembly();
				}

				$this->_markupMultiObj = $MarkUp;
			}

			$this->_markupMultiFetched = true;
		}

		//we could possibly get more than one if someone places an order simultaneously while the owner
		//sets a new mark up value, so we'll just use the most recent one
		//			if ( $rslt && ($rslt > 1) ) {
		//				throw new exception('store has more than one current mark up');
		//			}

		if (isset($this->_markupMultiObj))
		{
			return $this->_markupMultiObj;
		}

		return null;
	}

	/**
	 * Set the current mark up for a store
	 */
	function setMarkUp($value, $type = CMarkUp::FLAT, $menu_start_id = false)
	{
		if (!$this->id)
		{
			throw new Exception('store id not set for mark up lookup');
		}

		$oldRows = array();

		if ($menu_start_id === false)
		{ //update current markup
			$ActiveMarkUp = $this->getMarkUpObj(false);

			$oldvalue = null;
			$oldtype = null;

			//check and see if a change is required and save any old markup ids for later deletion
			if ($ActiveMarkUp)
			{
				$oldvalue = $ActiveMarkUp->markup_value;
				$oldtype = $ActiveMarkUp->markup_type;
			}

			if (($oldvalue == $value) && ($oldtype == $type))
			{
				return;
			}//no change required

			if ($ActiveMarkUp)
			{
				$oldRows [] = $ActiveMarkUp->id;
			}
		}

		//or insert a new row if the row is "updating"
		if (($type == CMarkUp::FLAT && isset($value)) || (($value !== '0.00') && ($value != '0') && $value))
		{
			//insert the new mark up
			$NewMarkUp = DAO_CFactory::create('mark_up');
			$NewMarkUp->store_id = $this->id;
			$NewMarkUp->markup_value = $value;
			$NewMarkUp->markup_type = $type;
			if ($menu_start_id)
			{
				$NewMarkUp->menu_id_start = $menu_start_id;
			}
			else
			{
				$NewMarkUp->mark_up_start = DAO::now();
			}

			if (!$NewMarkUp->insert())
			{
				throw new Exception('mark up insert failed');
			}
		}

		//delete the old ones
		if ($oldRows)
		{
			foreach ($oldRows as $id)
			{
				$OldMarkUp = DAO_CFactory::create('mark_up');
				$OldMarkUp->id = $id;
				$OldMarkUp->delete();
			}
		}
	}

	/**
	 * Set the current mark up for a store
	 */
	function setMarkUpMulti($Six_Normal, $Four_Normal, $Three_Normal, $Two_Normal, $Sides_Normal, $volume_discount, $menu_start_id = false, $is_default = 0, $sampler_item_price = 12.50, $assembly_fee = 0, $delivery_assembly_fee = 0)
	{
		if (!$this->id)
		{
			throw new Exception('store id not set for mark up lookup');
		}

		$oldRows = array();

		//or insert a new row if the row is "updating"

		//insert the new mark up
		$NewMarkUp = DAO_CFactory::create('mark_up_multi');
		$NewMarkUp->store_id = $this->id;
		$NewMarkUp->is_default = $is_default;
		$NewMarkUp->sampler_item_price = $sampler_item_price;
		$NewMarkUp->assembly_fee = $assembly_fee;
		$NewMarkUp->delivery_assembly_fee = $delivery_assembly_fee;

		if (empty($Six_Normal))
		{
			$Six_Normal = 0;
		}

		if (empty($Four_Normal))
		{
			$Four_Normal = 0;
		}

		if (empty($Three_Normal))
		{
			$Three_Normal = 0;
		}

		if (empty($Two_Normal))
		{
			$Two_Normal = 0;
		}

		if (empty($Sides_Normal))
		{
			$Sides_Normal = 0;
		}

		$NewMarkUp->markup_value_6_serving = $Six_Normal;
		$NewMarkUp->markup_value_4_serving = $Four_Normal;
		$NewMarkUp->markup_value_3_serving = $Three_Normal;
		$NewMarkUp->markup_value_2_serving = $Two_Normal;
		$NewMarkUp->markup_value_sides = $Sides_Normal;

		if ($menu_start_id)
		{
			$NewMarkUp->menu_id_start = $menu_start_id;
		}
		else
		{
			$NewMarkUp->mark_up_start = DAO::now();
		}

		if (!$NewMarkUp->insert())
		{
			throw new Exception('mark up insert failed');
		}
		// create volume discount and link to markup
		$volume_discount_obj = DAO_CFactory::create('volume_discount_type');
		$volume_discount_obj->menu_id = 'null';
		$volume_discount_obj->discount_value = $volume_discount;
		$volume_discount_obj->discount_type_id = 3;
		$volume_discount_obj->is_active = 1;

		if (!$volume_discount_obj->insert())
		{
			throw new Exception('volume_discount insert failed');
		}

		$markup_link = DAO_CFactory::create('mark_up_multi_to_volume_discount');
		$markup_link->volume_discount_id = $volume_discount_obj->id;
		$markup_link->mark_up_multi_id = $NewMarkUp->id;

		if (!$markup_link->insert())
		{
			throw new Exception('mark_up_multi_to_volume_discount');
		}

		//delete the old ones
		if ($oldRows)
		{
			foreach ($oldRows as $id)
			{
				$OldMarkUp = DAO_CFactory::create('mark_up_multi');
				$OldMarkUp->id = $id;
				$OldMarkUp->delete();
			}
		}
	}

	/**
	 * Returns the current premium for this store,
	 * @return array of (value, type) the current premium for this store
	 */
	function getPremium()
	{
		$Prem = $this->getPremiumObj();
		if ($Prem)
		{
			return array(
				$Prem->premium_value,
				$Prem->premium_type
			);
		}

		return array(
			0,
			null
		);
	}

	/**
	 * Returns the current premium data object,
	 * @return premium data object
	 */
	function getPremiumObj()
	{
		if (!$this->id)
		{
			throw new Exception('store id not set for premium lookup');
		}

		$Prem = DAO_CFactory::create('premium');
		$Prem->store_id = $this->id;
		$Prem->orderBy(' id DESC ');
		$rslt = $Prem->find();

		//we could possibly get more than one if someone places an order simultaneously while the owner
		//sets a new mark up value, so we'll just use the most recent one
		//			if ( $rslt && ($rslt > 1) ) {
		//				throw new exception('store has more than one current mark up');
		//			}

		if ($rslt)
		{
			$Prem->fetch();

			return $Prem;
		}

		return null;
	}

	/**
	 * Set the current premium for a store
	 */
	function setPremium($value, $type = false)
	{
		if (!$this->id)
		{
			throw new Exception('store id not set for premium lookup');
		}

		$Prem = DAO_CFactory::create('premium');
		$Prem->store_id = $this->id;
		$Prem->orderBy(' id DESC ');
		$rslt = $Prem->find();

		if (!$type)
		{
			$type = CPremium::FLAT;
		}

		//we could possibly get more than one if someone places an order simultaneously while the owner
		//adds a new premium
		if ($rslt > 1)
		{
			CLog::Record('W_WARNING: => ' . 'bad data => more than one premium for store => ' . $Prem->store_id, null, null);
		}

		$oldvalue = null;
		$oldtype = null;
		$oldRows = array();

		//check and see if a change is required and save any old premium ids for later deletion
		while ($Prem->fetch())
		{
			if (!$oldvalue)
			{
				$oldvalue = $Prem->premium_value;
				$oldtype = $Prem->premium_type;
			}

			if (($oldvalue == $value) && ($oldtype == $type))
			{
				return;
			}//no change required

			$oldRows[] = $Prem->id;
		}

		//if the value is 0, then we'll just delete all premiums
		//otherwise, insert a new row
		if (($value !== '0.00') && ($value != '0') && $value)
		{
			//insert the new premium
			$NewPremium = DAO_CFactory::create('premium');
			$NewPremium->store_id = $this->id;
			$NewPremium->premium_value = $value;
			$NewPremium->premium_type = $type;
			if (!$NewPremium->insert())
			{
				throw new Exception('premium insert failed');
			}
		}

		//delete the old ones
		foreach ($oldRows as $id)
		{
			$OldPremium = DAO_CFactory::create('premium');
			$OldPremium->id = $id;
			$OldPremium->delete();
		}
	}

	function delete($useWhere = false, $forceDelete = false)
	{
		if ($this->id)
		{
			$UTS = DAO_CFactory::create('user_to_store');
			$UTS->store_id = $this->id;
			$UTS->find();

			$uts_user_ids = array();

			while ($UTS->fetch())
			{
				$uts_user_ids[$UTS->user_id] = $UTS->user_id;

				$UTS->delete($useWhere);
			}

			// check if any of the deleted users are members of any other stores
			if (!empty($uts_user_ids))
			{
				$UTS = DAO_CFactory::create('user_to_store');
				$UTS->query("SELECT uts.id FROM user_to_store AS uts WHERE uts.user_id IN (" . implode(',', $uts_user_ids) . ") AND uts.is_deleted = '0'");

				while ($UTS->fetch())
				{
					// uset $uts_user_ids who are still in active stores
					unset($uts_user_ids[$UTS->user_id]);

					$UTS->delete($useWhere);
				}

				// return remaining $uts_user_ids to Customer
				$User = DAO_CFactory::create('user');
				$User->query("SELECT u.id FROM user AS u WHERE u.id IN (" . implode(',', $uts_user_ids) . ") AND u.is_deleted = '0'");

				while ($User->fetch())
				{
					if ($User->user_type != CUser::FRANCHISE_OWNER)
					{
						$userUpdated = clone($User);
						$userUpdated->user_type = CUser::CUSTOMER;
						$userUpdated->update($User, CUser::CUSTOMER);
					}
				}
			}

			// remove merch account
			$MerchantInfo = DAO_CFactory::create('merchant_accounts');
			$MerchantInfo->store_id = $this->id;
			while ($MerchantInfo->fetch())
			{
				$MerchantInfo->delete($useWhere);
			}
		}

		return parent::delete($useWhere, $forceDelete);
	}

	function exists()
	{
		$Store = DAO_CFactory::create('store');
		$Store->store_name = $this->store_name;

		if ($Store->find(true))
		{
			return true;
		}

		return false;
	}

	function getCurrentSalesTaxObj()
	{
		$sales_tax = DAO_CFactory::create('sales_tax');
		$sales_tax->store_id = $this->id;
		$rslt = $sales_tax->findActive();
		if ($rslt && $rslt > 1) //this is possible if the owner is changing taxes at the time of an order
		{
			CLog::Record('W_WARNING: => more than one sales tax returned for store:' . $this->id, null, null);
		}

		if ($rslt)
		{
			while ($sales_tax->fetch())
			{
			}

			return $sales_tax;
		}

		return null;
	}

	/**
	 * @returns list($id, $food_tax, $product_tax);
	 */
	function getCurrentSalesTax()
	{
		$sales_tax = $this->getCurrentSalesTaxObj();
		if (!$sales_tax)
		{
			return array(
				null,
				0,
				0,
				0,
				0,
				0,
				0
			);
		}

		return array(
			$sales_tax->id,
			$sales_tax->food_tax,
			$sales_tax->total_tax,
			$sales_tax->other1_tax,
			$sales_tax->other2_tax,
			$sales_tax->other3_tax,
			$sales_tax->other4_tax
		);
	}

	/**
	 * inserts a new customization Fee Records
	 */
	function setCustomizationFee($store_id, $default, $cost)
	{
		$existingEntry = DAO_CFactory::create('store_fee');
		$existingEntry->store_id = $store_id;
		$existingEntry->type = CStoreFee::MEAL_CUSTOMIZATION;
		$existingEntry->name = $default['name'];
		$existingEntry->find();

		if ($existingEntry->N == 0)
		{
			//insert
			$new_fee = DAO_CFactory::create('store_fee');
			$new_fee->store_id = $store_id;
			$new_fee->type = CStoreFee::MEAL_CUSTOMIZATION;
			$new_fee->cost = $cost;

			$new_fee->name = $default['name'];
			$new_fee->sort = $default['sort'];
			$new_fee->units = $default['units'];
			$new_fee->value = $default['value'];
			$new_fee->operator = $default['operator'];
			$new_fee->description = $default['description'];

			$new_fee->insert();
		}
		else if ($existingEntry->N == 1)
		{
			//update
			$existingEntry->fetch();
			$org = clone($existingEntry);
			$existingEntry->cost = $cost;
			$existingEntry->update($org);
		}
		else
		{
			//error should not be more than 1
		}
	}

	function customizationFeeForMealCount($applicableMealCount)
	{

		if (is_null($applicableMealCount) || $applicableMealCount == 0)
		{
			return 0;
		}

		if (is_null($this->customization_fees))
		{
			$this->customization_fees = CStoreFee::fetchCustomizationFees($this);
		}

		$feeAmount = 0;
		foreach ($this->customization_fees as $fee)
		{
			switch ($fee['operator'])
			{
				case 'GREATER':
					if ($applicableMealCount > $fee['value'])
					{
						$feeAmount = $fee['cost'];
					}
					break;
				case 'LESS':
					if ($applicableMealCount < $fee['value'])
					{
						$feeAmount = $fee['cost'];
					}
					break;
				case 'EQUAL_OR_LESS':
					if ($applicableMealCount <= $fee['value'])
					{
						$feeAmount = $fee['cost'];
					}
					break;
				case 'EQUAL_OR_GREATER':
					if ($applicableMealCount >= $fee['value'])
					{
						$feeAmount = $fee['cost'];
					}
					break;
				case 'BETWEEN_INCLUSIVE':
					$range = explode('-', $fee['value']);
					$range_min = $range[0];
					$range_max = $range[1];
					if ($applicableMealCount >= $range_min && $applicableMealCount <= $range_max)
					{
						$feeAmount = $fee['cost'];
					}
					break;
				case 'BETWEEN_EXCLUSIVE':
					$range = explode('-', $fee['value']);
					$range_min = $range[0];
					$range_max = $range[1];
					if ($applicableMealCount > $range_min && $applicableMealCount < $range_max)
					{
						$feeAmount = $fee['cost'];
					}
					break;
			}
		}

		return $feeAmount;
	}

	/**
	 * inserts a new sales tax record
	 */
	function setCurrentSalesTax($current_food_tax, $current_product_tax, $current_service_tax, $current_enrollment_tax, $current_delivery_tax = 0.0, $current_bag_fee_tax = 0.0)
	{

		list($id, $food_tax, $total_tax, $service_tax, $enrollment_tax, $delivery_tax, $bag_fee_tax) = $this->getCurrentSalesTax();
		if (((string)$current_food_tax != (string)$food_tax) || ((string)$current_product_tax != (string)$total_tax) || ((string)$current_service_tax != (string)$service_tax) || ((string)$current_enrollment_tax != (string)$enrollment_tax) || ((string)$current_delivery_tax != (string)$delivery_tax) || ((string)$current_bag_fee_tax != (string)$bag_fee_tax))
		{
			$sales_tax = DAO_CFactory::create('sales_tax');
			$sales_tax->store_id = $this->id;
			$sales_tax->find();

			if ($current_food_tax || $current_product_tax || $current_service_tax || $current_enrollment_tax || $current_delivery_tax || $current_bag_fee_tax)
			{
				$new_tax = DAO_CFactory::create('sales_tax');
				$new_tax->store_id = $this->id;
				$new_tax->food_tax = $current_food_tax;
				$new_tax->total_tax = $current_product_tax;
				$new_tax->other1_tax = $current_service_tax;
				$new_tax->other2_tax = $current_enrollment_tax;
				$new_tax->other3_tax = $current_delivery_tax;
				$new_tax->other4_tax = $current_bag_fee_tax;

				$new_tax->insert();
			}

			while ($sales_tax->fetch())
			{
				$sales_tax->delete();
			}
		}
	}

	function isOpen()
	{
		if ($this->isActive() && $this->isShowToCustomer() && !$this->isComingSoon())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isComingSoon()
	{
		if ($this->isShowToCustomer() && !$this->isActive())
		{
			$this->coming_soon = true;
		}
		else
		{
			$this->coming_soon = false;
		}

		return $this->coming_soon;
	}

	function isActive()
	{
		if (!empty($this->active))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isShowToCustomer()
	{
		if (!empty($this->show_on_customer_site))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isDistributionCenter()
	{
		if ($this->store_type == CStore::DISTRIBUTION_CENTER)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function generateAddressLinear()
	{
		$this->address_linear = $this->address_line1 . (!empty($this->address_line2) ? " " . $this->address_line2 : "") . ", " . $this->city  . ", " . $this->state_id . " " .  $this->postal_code . (!empty($this->usps_adc) ? "-" . $this->usps_adc : "");

		return $this->address_linear;
	}

	function generateAddressWithBreaks()
	{
		$this->address_with_breaks = $this->address_line1 . (!empty($this->address_line2) ? " " . $this->address_line2 : "") . "\n" . $this->city  . ", " . $this->state_id . " " .  $this->postal_code . (!empty($this->usps_adc) ? "-" . $this->usps_adc : "");

		return $this->address_with_breaks;
	}

	function generateAddressHTML()
	{
		$this->address_html = nl2br($this->generateAddressWithBreaks());

		return $this->address_html;
	}

	function generateMapLink()
	{
		$this->map_link = 'https://maps.google.com/maps?q=' . urlencode($this->generateLinearAddress()) . '&iwloc=A&hl=en';

		return $this->map_link;
	}

	/**
	 * Returns an array of supported credit card types for this store
	 */
	function getCreditCardTypes()
	{
		require_once 'CPayment.php';

		$rtn = array();

		if (!$this->id)
		{
			return array(
				CPayment::VISA,
				CPayment::MASTERCARD
			);
		}

		$ccTypeObj = DAO_CFactory::create('payment_credit_card_type');
		$ccTypeObj->store_id = $this->id;
		$found = $ccTypeObj->find();

		if (!$found)
		{
			return array(
				CPayment::VISA,
				CPayment::MASTERCARD
			);
		}

		while ($ccTypeObj->fetch())
		{
			switch ($ccTypeObj->credit_card_type)
			{
				case CPayment::DISCOVERCARD:
					$rtn [] = CPayment::DISCOVERCARD;
					break;

				case CPayment::AMERICANEXPRESS:
					$rtn [] = CPayment::AMERICANEXPRESS;
					break;

				case CPayment::VISA:
					$rtn [] = CPayment::VISA;
					break;

				case CPayment::MASTERCARD:
					$rtn [] = CPayment::MASTERCARD;
					break;

				default:
					throw new Exception('unknown credit card type');
			}
		}

		// CES 8/31/06 - always include VISA and MASTERCARD
		if (!isset($rtn[CPayment::VISA]))
		{
			$rtn [] = CPayment::VISA;
		}

		if (!isset($rtn[CPayment::MASTERCARD]))
		{
			$rtn [] = CPayment::MASTERCARD;
		}

		return $rtn;
	}
}