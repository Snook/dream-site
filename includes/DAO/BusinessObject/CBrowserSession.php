<?php
require_once("DAO/Browser_sessions.php");

/* ------------------------------------------------------------------------------------------------
 *	Class: CBrowserSession
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *		Load()
 *		Update()
 *		Expire()
 *
 *  Properties:
 *
 *
 *	Dynamic Properties:
 *		ID
 *		User_ID
 *		etc.
 *
 *
 *	Description:
 *		Placeholder for DB storing session object.  Including this file
 *		creates a global $Session object.  This object should not be confused with
 *		PHP's $_SESSION variable (of which it probably will make use of at some
 *		point).
 *
 *		The CBrowserSession class manages session state by tracking a GUID assigned
 *		to the user (either by cookie or session var).  The GUID is then
 *		stored in the database along with other information such as the user_id
 *		and other items we don't want to be storing is PHP's $_SESSION var.
 *
 *		Session properties other than ID, will be stored as a dynamic property
 *		set accessed by $this->prop['User_ID'].  This will allow fast lookups
 *		by the session ID and a dynamic range of properties that can be added
 *		or modified without schema changes.
 *
 *	Requires:
 *		CLog.inc.php
 *
 * -------------------------------------------------------------------------------------------------- */

class CBrowserSession extends DAO_Browser_sessions
{

	public $isPrevious = false;

	//
	// constructor
	//
	// See if there is a cookie we can pick up
	//

	static private $session = null;

	//if we are using a cookie for storage, then the value won't be available until the next request,
	//so we'll save it here temporarily.
	private $tempStorage = array();
	static $currentFadminStoreObj = null;

	static public function instance()
	{
		if (self::$session == null)
		{
			self::$session = new CBrowserSession();
		}

		return self::$session;
	}

	static public function setValueForThisSessionOnly($key, $value = false, $secure = true, $httponly = true)
	{
		$inst = self::instance();
		$inst->tempStorage[$key] = $value;

		if ($value === false)
		{
			unset($inst->tempStorage[$key]);
		}

		setcookie($key, $value, 0, "/", COOKIE_DOMAIN, $secure, $httponly);

		if ($value === false)
		{
			setcookie($key, false, time() - 60000);
		}
	}

	/**
	 * Wrapper for saving a value into the browser session cache (cookie,session,db,etc)
	 * Passing in no value will delete the variable, use 0 or 1 for booleans instead,
	 * You must pass in a duration in seconds
	 */
	static public function setValueAndDuration($key, $value = false, $seconds = 0, $secure = true, $httponly = true)
	{
		$inst = self::instance();

		$inst->tempStorage[$key] = $value;

		if ($value === false)
		{
			unset($inst->tempStorage[$key]);
		}

		$expire = 0; // at session

		if ($seconds != 0)
		{
			$expire = time() + $seconds;
		}

		setcookie($key, $value, $expire, "/", COOKIE_DOMAIN, $secure, $httponly); //expire in 15 minutes

		if ($value === false)
		{
			setcookie($key, false, time() - 60000);
		}
	}

	static public function getSessionCookieName()
	{
		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME != 'LIVE')
		{
			return DD_SERVER_NAME . "_DreamSite";
		}
		else
		{
			return "DreamSite";
		}
	}

	static $siteSpecificCookies = array(
		'cart',
		'default_store_id',
		'credit_key'
	);

	/**
	 * Wrapper for saving a value into the browser session cache (cookie,session,db,etc)
	 * Passing in no value will delete the variable, use 0 or 1 for booleans instead
	 */
	static public function setValue($key, $value = false, $no_expire = false, $secure = true, $httponly = true)
	{
		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME != 'LIVE')
		{
			if (in_array($key, self::$siteSpecificCookies))
			{
				$key = DD_SERVER_NAME . "_" . $key;
			}
		}

		$inst = self::instance();

		$inst->tempStorage[$key] = $value;

		if ($value === false)
		{
			unset($inst->tempStorage[$key]);
			unset($_COOKIE[$key]);
		}

		$DAY = (60 * 60 * 24); //seconds in a day

		if (!$no_expire)
		{
			setcookie($key, $value, time() + 60 * 15, "/", COOKIE_DOMAIN, $secure, $httponly); //expire in 15 minutes
		}
		else if ($no_expire)
		{
			setcookie($key, $value, time() + $DAY * 365, "/", COOKIE_DOMAIN, $secure, $httponly); //don't expire default store if in store view for 1 year
		}
		else
		{
			setcookie($key, $value, time() + $DAY * 60, "/", COOKIE_DOMAIN, $secure, $httponly); //expire in 60 days: cart, firstname etc.
		}

		if ($value === false)
		{
			setcookie($key, false, time() - 60000);
		}
	}

	/**
	 * Wrapper for getting a value from the browser session cache (cookie,session,db,etc)
	 */
	static public function getValue($key, $default_value = null)
	{
		// for testing analytics on order details page
		if (defined('DD_THANK_YOU_DEBUG') && DD_THANK_YOU_DEBUG == true)
		{
			if ($key == 'dd_thank_you')
			{
				return true;
			}
		}

		$inst = self::instance();

		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME != 'LIVE')
		{
			if (in_array($key, self::$siteSpecificCookies))
			{
				$key = DD_SERVER_NAME . "_" . $key;
			}
		}

		if (defined('TEST_COMMAND_LINE_USING_CART') && DD_SERVER_NAME != 'LIVE')
		{
				return DD_SERVER_NAME . "_CLI_CART";
		}

		$val = $default_value;

		if (array_key_exists($key, $inst->tempStorage))
		{
			$val = $inst->tempStorage[$key];
		}
		else if (array_key_exists($key, $_COOKIE))
		{
			$val = $_COOKIE[$key];
		}

		if ($val === 'deleted')
		{
			$val = $default_value;
		}

		return $val;
	}

	static public function getValueOnce($key, $default_value = null)
	{
		// get value
		$val = self::getValue($key, $default_value);

		// clear value
		self::setValue($key, false);

		return $val;
	}

	// set last viewed store id
	static public function setLastViewedStore($id)
	{
		// set cookie timeout to 2 days (172800 seconds)
		self::setValueAndDuration('last_viewed_store', $id, 172800);
	}

	//customer or fadmin home store
	static public function setCurrentStore($id)
	{
		if (self::getCurrentStore() != $id && is_numeric($id))
		{
			if (!empty($id))
			{
				self::setValue('default_store_id', $id, true);
				if (CUser::isLoggedIn())
				{
					$User = CUser::getCurrentUser();
					$User->setHomeStore($id);
				}
				//set the current store name
				$Store = DAO_CFactory::create('store');
				$Store->id = $id;
				$Store->find(true);
			}
		}
	}

	/*
	 * Check if the user has visited before
	 */
	static function isReturning()
	{
		list ($store, $ignore) = self::getCurrentStore();

		if ($store)
		{
			return true;
		}

		return false;
	}

	static public function getCurrentStore()
	{
		$retVal = self::getValue('default_store_id');

		if (isset($retVal) && !is_numeric($retVal))
		    return null;

		return $retVal;
	}

	static public function getCurrentFadminStoreType()
	{
		$id = self::getCurrentFadminStoreID();

		$Store = DAO_CFactory::create('store');
		$Store->id = $id;
		$Store->find(true);

		return $Store->store_type;
	}



	static public function getLastViewedStore()
	{
		return self::getValue('last_viewed_store');
	}

	static public function getCurrentFadminStore()
	{
		return self::instance()->current_store_id;
	}

	static public function getCurrentFadminStoreID()
	{
		if (CUser::getCurrentUser()->isFranchiseAccess())
		{
			return self::getCurrentFadminStore();
		}
		else if (self::getCurrentFadminStore())
		{
			return self::getCurrentFadminStore();
		}
		else
		{
			return self::getCurrentStore();
		}
	}

	static public function getCurrentFadminStoreObj()
	{
		if (self::$currentFadminStoreObj == null)
		{
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->id = self::getCurrentFadminStoreID();
			$DAO_store->find_DAO_store(true);

			self::$currentFadminStoreObj = $DAO_store;
		}

		return self::$currentFadminStoreObj;
	}

	static public function setCurrentFadminStore($store_id)
	{

	    if (is_numeric($store_id))
	    {
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->id = $store_id;
			$DAO_store->find_DAO_store(true);

			if (CUser::getCurrentUser()->isFranchiseAccess())
			{
				CStore::setUpFranchiseStore($DAO_store->id);
			}

			self::setCurrentStore($DAO_store->id);

			$copy = clone(self::instance());
			self::instance()->current_store_id = $DAO_store->id;
			self::instance()->update($copy);
	    }
	}

	static public function setFirstName($name)
	{
		self::setValue('firstname', $name, true);
	}

	static public function getFirstName()
	{
		return self::getValue('firstname');
	}

	static public function setLastName($name)
	{
		self::setValue('lastname', $name, true);
	}

	static public function getLastName()
	{
		return self::getValue('lastname');
	}

	static public function setCartKey($id)
	{
		self::setValue('cart', $id);
	}

	static public function getCartKey()
	{
		return self::getValue('cart');
	}

	static public function isPrevious()
	{
		return self::instance()->isPrevious;
	}

	function __construct()
	{
		parent::__construct();

		$this->isPrevious = false;

		if (!empty($_COOKIE))
		{

			$session_cookie_name = self::getSessionCookieName();

			if (!empty($_COOKIE[$session_cookie_name]))
			{
				$this->browser_session_key = $_COOKIE[$session_cookie_name];
				$this->isPrevious = true;

				//if (DEBUG) echo "<P>Cookie is $szCookie";
				return;
			}
		}
		else
		{
			//setcookie("chkcookie", true, 0, "/", COOKIE_DOMAIN, true, false);  // expire when browser closes
		}
	}

	static function nofollow()
	{
		header('X-Robots-Tag: noindex, follow');
	}

	function cookieDuration($User = null, $keepLoggedIn = false)
	{
		$CookieOneYear = time() + (86400 * 365);
		$CookieFifteenMin = time() + 60 * 15;

		if (!empty($User->user_type) && $User->user_type != CUser::CUSTOMER)
		{
			// Browser session for all types other than customer
			$sessionDuration = time() + 60 * FADMIN_TIMEOUT;

			// If type is site admin they can stay logged in
			if ($User->user_type == CUser::SITE_ADMIN && (defined('ENABLE_SITE_ADMIN_TIMEOUT') && ENABLE_SITE_ADMIN_TIMEOUT != true))
			{
				$sessionDuration = $CookieOneYear;
			}
		}
		else if (!empty($User->user_type) && $User->user_type == CUser::CUSTOMER && $keepLoggedIn)
		{
			// Customers if checked to remember login, set to one year
			$sessionDuration = $CookieOneYear;
		}
		else
		{
			// Customers by default browser session
			$sessionDuration = 0;
		}

		return $sessionDuration;
	}

	/* ------------------------------------------------------------
	*
	* Class: CBrowserSession
	*
	* Function: Create
	*
	* Params:	OUT	$this->ID	- the GUID assigned to the user session
	*
	* This function will create a new user session and create an entry
	* in the database.  It does not assign or associate a user
	* with the session.  If a session already exists, it will be overwritten.
	*
	* -------------------------------------------------------------- */

	function createSessionGUID($User = null, $keepLoggedIn = false)
	{
		// Create a GUID (requires md5)

		$inst = self::instance();

		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float)$sec + ((float)$usec * 100000));
		$this->browser_session_key = md5(uniqid(mt_rand(), true));

		$sessionDuration = $this->cookieDuration($User, $keepLoggedIn);

		// set userid
		setcookie("DDUID", $User->id, $sessionDuration, "/", COOKIE_DOMAIN, true); // expire when browser closes or if checked to remember login

		$session_cookie_name = self::getSessionCookieName();

		$inst->tempStorage[$session_cookie_name] = $this->browser_session_key;

		// httponly needs to be set to false to allow BackOffice timout activity extension
		setcookie($session_cookie_name, $this->browser_session_key, $sessionDuration, "/", COOKIE_DOMAIN, true, false);

		return $this->browser_session_key;
	}

	/* ------------------------------------------------------------
	*
	* Class: CBrowserSession
	*
	* Function: prolongSession
	*
	*
	* This function will prolong a session by FADMIN_TIMEOUT minutes
	*
	* -------------------------------------------------------------- */

	function prolongSession($User = null)
	{
		$sessionDuration = $this->cookieDuration($User);

		$fadminTimeOut = false;

		// store timeout in a cookie
		if (defined('FADMIN_TIMEOUT') && FADMIN_TIMEOUT)
		{
			if ($User->user_type == CUser::SITE_ADMIN && (defined('ENABLE_SITE_ADMIN_TIMEOUT') && ENABLE_SITE_ADMIN_TIMEOUT != true))
			{
				$fadminTimeOut = false;
			}
			else
			{
				$fadminTimeOut = FADMIN_TIMEOUT;
			}
		}

		$session_cookie_name = self::getSessionCookieName();

		setcookie("DreamSite_TO", $fadminTimeOut, 0, "/", COOKIE_DOMAIN, true, false); // expire when browser closes

		// httponly needs to be set to false to allow BackOffice timout activity extension
		setcookie($session_cookie_name, $this->browser_session_key, $sessionDuration, "/", COOKIE_DOMAIN, true, false);
	}

	/* ------------------------------------------------------------
	*
	* Class: CBrowserSession
	*
	* Function: ExpireSession
	*
	* Params:	IN	$this->ID
	*
	* This function expires the user session.  If not logged in,
	* returns an error.
	*
	* -------------------------------------------------------------- */

	function ExpireSession()
	{
		if (CUser::getCurrentUser()->id)
		{
			//delete all browser session records for this user
			unset($this->id);
			//unset($this->browser_session_key);
			unset($this->timestamp_created);
			unset($this->timestamp_updated);
			//$this->user_id = CUser::getCurrentUser()->id;

			$this->delete(false, true);
		}

		self::ClearCookie();

		return true;
	}

	static function ClearCookie()
	{
		$session_cookie_name = self::getSessionCookieName();
		setcookie($session_cookie_name, false);
		setcookie($session_cookie_name, false, 0, "/"); //expire when browser closes

		// clear "login as guest" cookie
		self::setValue('FAUID');
		self::setValue('EDIT_DELIVERED_ORDER');
	}
}

?>