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
	const BOUNCE_REQUEST_URI = 'BOUNCE_REQUEST_URI';
	const SUBMISSION_MESSAGE = 'SUBMISSION_MESSAGE';

	public bool $isPrevious = false;

	//
	// constructor
	//
	// See if there is a cookie we can pick up
	//

	static private $session = null;

	//if we are using a cookie for storage, then the value won't be available until the next request,
	//so we'll save it here temporarily.
	private array $tempStorage = array();
	static mixed $currentFadminStoreObj = null;

	static public function instance(): ?CBrowserSession
	{
		if (self::$session == null)
		{
			self::$session = new CBrowserSession();
		}

		return self::$session;
	}

	static public function setValueForThisSessionOnly($key, $value = false, $secure = true, $httponly = true): void
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
			setcookie($key, false, time() - 60000, "/");
		}
	}

	/**
	 * Wrapper for saving a value into the browser session cache (cookie,session,db,etc)
	 * Passing in no value will delete the variable, use 0 or 1 for booleans instead,
	 * You must pass in a duration in seconds
	 */
	static public function setValueAndDuration($key, $value = false, $seconds = 0, $secure = true, $httponly = true): void
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
			setcookie($key, false, time() - 60000, "/");
		}
	}

	static public function getSessionCookieName(): string
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

	static public function setSessionVariable($key, $value = false): void
	{
		if (!$value)
		{
			unset($_SESSION[$key]);
		}
		else
		{
			$_SESSION[$key] = $value;
		}
	}

	static public function getSessionVariable($key): mixed
	{
		if (key_exists($key, $_SESSION))
		{
			return $_SESSION[$key];
		}

		return null;
	}

	static public function getSessionVariableOnce($key): mixed
	{
		if (key_exists($key, $_SESSION))
		{
			$thisValue = $_SESSION[$key];

			// Delete
			self::setSessionVariable($key);

			return $thisValue;
		}

		return null;
	}

	static array $siteSpecificCookies = array(
		'cart',
		'default_store_id',
		'credit_key'
	);

	/**
	 * Express method for extending the length that a cart cookie will exist
	 */
	static public function refreshCartCookieExpiry(): void
	{
		self::setValue('cart', self::getValue('cart'));
	}

	/**
	 * Wrapper for saving a value into the browser session cache (cookie,session,db,etc)
	 * Passing in no value will delete the variable, use 0 or 1 for booleans instead
	 */
	static public function setValue($key, $value = false, $no_expire = false, $secure = true, $httponly = true): void
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

		if (str_contains($key, 'cart'))//cart cookie
		{
			setcookie($key, $value, time() + $DAY * 5, "/", COOKIE_DOMAIN, $secure, $httponly); //expire in 5 days
		}
		else if ($no_expire)
		{
			setcookie($key, $value, time() + $DAY * 365, "/", COOKIE_DOMAIN, $secure, $httponly); //don't expire default store if in store view for 1 year
		}
		else
		{
			setcookie($key, $value, time() + 60 * 15, "/", COOKIE_DOMAIN, $secure, $httponly); //expire in 15 minutes
		}

		if ($value === false)
		{
			setcookie($key, false, time() - 60000, "/");
		}
	}

	/**
	 * Wrapper for getting a value from the browser session cache (cookie,session,db,etc)
	 */
	static public function getValue($key, $default_value = null)
	{
		// for testing analytics on order details page
		if (defined('DD_THANK_YOU_DEBUG') && DD_THANK_YOU_DEBUG)
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
		self::setValue($key);

		return $val;
	}

	// set last viewed store id
	static public function setLastViewedStore($id): void
	{
		// set cookie timeout to 2 days (172800 seconds)
		self::setValueAndDuration('last_viewed_store', $id, 172800);
	}

	//customer or fadmin home store

	/**
	 * @throws Exception
	 */
	static public function setCurrentStore($id): void
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
	static function isReturning(): bool
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
		{
			return null;
		}

		return $retVal;
	}

	/**
	 * @throws Exception
	 */
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

	/**
	 * @throws Exception
	 */
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

	/**
	 * @throws Exception
	 */
	static public function setCurrentFadminStore($store_id): void
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

			//self::setCurrentStore($DAO_store->id);

			$copy = clone(self::instance());
			self::instance()->current_store_id = $DAO_store->id;
			self::instance()->update($copy);
		}
	}

	static public function setFirstName($name): void
	{
		self::setValue('firstname', $name, true);
	}

	static public function getFirstName()
	{
		return self::getValue('firstname');
	}

	static public function setLastName($name): void
	{
		self::setValue('lastname', $name, true);
	}

	static public function getLastName()
	{
		return self::getValue('lastname');
	}

	static public function setCartKey($id): void
	{
		self::setValue('cart', $id);
	}

	static public function getCartKey()
	{
		return self::getValue('cart');
	}

	static public function isPrevious(): bool
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
			}
		}
	}

	static function nofollow(): void
	{
		header('X-Robots-Tag: noindex, follow');
	}

	function cookieDuration($DAO_user = null, $keepLoggedIn = false)
	{
		$CookieOneYear = time() + (86400 * 365);

		if (!empty($DAO_user->user_type) && $DAO_user->user_type != CUser::CUSTOMER)
		{
			// Browser session for all types other than customer
			$sessionDuration = time() + 60 * FADMIN_TIMEOUT;

			// If type is site admin they can stay logged in
			if ($DAO_user->user_type == CUser::SITE_ADMIN && (defined('ENABLE_SITE_ADMIN_TIMEOUT') && !ENABLE_SITE_ADMIN_TIMEOUT))
			{
				$sessionDuration = $CookieOneYear;
			}
		}
		else if (!empty($DAO_user->user_type) && $DAO_user->user_type == CUser::CUSTOMER && $keepLoggedIn)
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

	function prolongSession($DAO_user = null): void
	{
		$sessionDuration = $this->cookieDuration($DAO_user);

		$fadminTimeOut = false;

		// store timeout in a cookie
		if (defined('FADMIN_TIMEOUT') && FADMIN_TIMEOUT)
		{
			if ($DAO_user->user_type == CUser::SITE_ADMIN && (defined('ENABLE_SITE_ADMIN_TIMEOUT') && !ENABLE_SITE_ADMIN_TIMEOUT))
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

	function ExpireSession(): bool
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

		session_regenerate_id(true);

		return true;
	}

	static function ClearCookie(): void
	{
		$session_cookie_name = self::getSessionCookieName();
		setcookie($session_cookie_name, false);
		setcookie($session_cookie_name, false, 0, "/"); //expire when browser closes

		// clear "login as guest" cookie
		self::setValue('FAUID');
		self::setValue('EDIT_DELIVERED_ORDER');
	}
}