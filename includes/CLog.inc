<?php
require_once('DB.php');
require_once('DB/common.php');
require_once('includes/DAO/BusinessObject/CUser.php');

/* ------------------------------------------------------------------------
 *	Class: CLog
 *
 *	Data:
 *
 *	Methods:
 *		Normal	($szFile:$szParams:$szDescription)
 *		Error	($szFile, $szParams, $szDescription)
 *		Close	()
 *
 *	Description:
 *		Object for writing to the log database (tbl_Log).
 *
 *	Example:
 *		$Log = new CLog;
 *		$Log -> Error ("File.php:POST=1&DEBUG=1:Something bad happened!");
 *		$Log -> Close();
 *  touch
 * ------------------------------------------------------------------------ */

class CLog
{
	// public

	var $_buffer = '';

	private static $_instance = null;
	private static $_db = null;

	const STRICT = 'STRICT';
	const NOTICE = 'NOTICE';
	const WARNING = 'WARNING';
	const ERROR = 'ERROR';
	const SECURITY = 'SECURITY';
	const LOGIN = 'LOGIN';
	const PHP = 'PHP';
	const DEBUG = 'DEBUG';
	const CCDECLINE = 'CCDECLINE';
	const UNHANDLEDEXCEPTION = 'UNHANDLED';
	const REPORT = 'REPORT';

	//******************************
	// Cron Log Processing
	// $CronLogType
	// Cron tasks
	//******************************
	const USER_RETENTION_NEW = 'USER_RETENTION_NEW';
	const USER_RETENTION_UPDATE = 'USER_RETENTION_UPDATE';
	const USER_RETENTION_REMOVE = 'USER_RETENTION_REMOVE';
	const DASHBOARDCACHING = 'DASHBOARDCACHING';
	const EXPIRE_STORE_CREDIT = 'EXPIRE_STORE_CREDIT';
	const WARN_EXPIRE_STORE_CREDIT = 'WARN_EXPIRE_STORE_CREDIT';
	const CONFIRM_PLATEPOINTS_ORDERS = 'CONFIRM_PLATEPOINTS_ORDERS';
	const EXPIRE_PLATEPOINTS_CREDITS = 'EXPIRE_PLATEPOINTS_CREDITS';
	const WARN_EXPIRE_PLATEPOINTS_CREDITS = 'WARN_EXPIRE_PLATEPOINTS_CREDITS';
	const BIRTHDAY_REWARDS = 'BIRTHDAY_REWARDS';
	const TWO_DAY_TASTE_REMINDERS = 'TWO_DAY_TASTE_REMINDERS';
	const DELAYED_PAYMENTS = 'DELAYED_PAYMENTS';
	const EVITE_REMINDERS = 'EVITE_REMINDERS';
	const SESSION_REMINDERS = 'SESSION_REMINDERS';
	const PUSH_GIFT_CARD_REPORT = 'PUSH_GIFT_CARD_REPORT';
	const REFERRAL_REWARDS = 'REFERRAL_REWARDS';
	const THIRD_ORDER_TASTE_REMINDERS = 'THIRD_ORDER_TASTE_REMINDERS';
	const FOOD_TESTING_REMINDER_EMAIL = 'FOOD_TESTING_REMINDER_EMAIL';
	const CACHE_STORE_CLASSES = 'CACHE_STORE_CLASSES';
	const DELETE_OLD_SAVED_ORDERS = 'DELETE_OLD_SAVED_ORDERS';
	const EMAIL_LATE_COA_FILERS = 'EMAIL_LATE_COA_FILERS';
	const EMAIL_SHIPPING_AGR = 'EMAIL_SHIPPING_AGR';
	const CACHE_WEEKLY_METRICS = 'CACHE_WEEKLY_METRICS';
	const CLEAR_STALE_CARTS = 'CLEAR_STALE_CARTS';
	const MARK_CART_ABANDONED = 'MARK_CART_ABANDONED';
	const CACHE_GLOBAL_RECIPE_RATING = 'CACHE_GLOBAL_RECIPE_RATING';
	const UPDATE_MEMBERSHIP_STATUS = 'UPDATE_MEMBERSHIP_STATUS';
	const ALERT_LOW_INVENTORY = 'ALERT_LOW_INVENTORY';
	const PROCESS_SHIPPING_INFORMATION = 'PROCESS_SHIPPING_INFORMATION';
	const WHAT_TO_EXPECT = 'WHAT_TO_EXPECT';

	// $ProcessingStatusType
	const SUCCESS = 'SUCCESS';
	const FAILURE = 'FAILURE';
	const PARTIAL_FAILURE = 'PARTIAL FAILURE';

	//******************************

	public static function instance()
	{
		if (self::$_instance == null)
		{
			self::$_instance = new CLog();
		}

		return self::$_instance;
	}

	public static function getEventLogTable()
	{
		if (date('Y') != '2011')
		{
			return "event_log_" . date('Y');
		}

		return "event_log";
	}

	// Attempt to create a new event_log table for the year
	public static function createEventLogTable()
	{
		return mysqli_query(self::$_db, "CREATE TABLE IF NOT EXISTS `" . CLog::getEventLogTable() . "` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL DEFAULT '0',
			`admin_notify` int(1) NOT NULL DEFAULT '0',
			`event_hint` varchar(64) DEFAULT NULL,
			`description` text,
			`ip_address` varchar(64) DEFAULT NULL,
			`log_type` enum('STRICT','NOTICE','WARNING','ERROR','SECURITY','LOGIN','PHP','CCDECLINE','UNHANDLED','DEBUG','REPORT','ASSERTION_FAILED') DEFAULT 'NOTICE',
			`timestamp_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`is_dfl_access` tinyint(3) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `id_User_id` (`id`,`user_id`),
			KEY `log_type` (`log_type`),
			KEY `idx_user_id` (`user_id`),
			KEY `idx_timestamp_created` (`timestamp_created`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
	}

	private function __construct()
	{
	}

	function connect()
	{
		if (!self::$_db)
		{
			self::$_db = mysqli_connect(DB_LOG_SERVER, DB_LOG_SERVER_USERNAME, DB_LOG_SERVER_PASSWORD);
			mysqli_select_db(self::$_db, DB_LOG_DATABASE);
		}

		return self::$_db;
	}

	/******************************************************************
	 *
	 * Class:    CLog
	 * Method:    Record
	 *
	 * In:        string -    $szDescription
	 *
	 * Desc:    The normal method of logging an event:
	 *        CLog::Record('E_LOGIN::There was an error logging in.');
	 *
	 * Note:    Prefix the $szDescription with D_, N_, W_, E_, or S_followed
	 *        by a category (make em up as we go, dont need to match others)
	 *        and then :: followed by the descripton.  It sucks having to enter
	 *        in 4 parameters everytime you want to log something ;)
	 *
	 *         enum('DEBUG', 'NOTICE', 'WARNING', 'ERROR', 'SECURITY')
	 *
	 ********************************************************************/

	static function Record($szDescription, $szOptionalFile = "", $szOptionalLine = "", $szOptionalFunction = "")
	{

		$log = self::$_instance;
		$Type = "N";
		$szHint = "";
		$szDesc = "";

		// Break it up if it's in the right format

		if ((substr($szDescription, 1, 1) == "_") && substr_count($szDescription, "::"))
		{
			list ($Type, $temp) = preg_split("/_/", $szDescription);
			$szDesc = $szDescription . $szOptionalFile . $szOptionalLine . $szOptionalFunction;
		}
		else
		{
			$szDesc = $szDescription;
		}

		$user = CUser::getCurrentUser();
		$user_id = @$user->id;
		if (!$user_id)
		{
			$user_id = 0;
		}
		$aErrorTypes = array(
			'D' => 'DEBUG',
			'N' => 'NOTICE',
			'W' => 'WARNING',
			'E' => 'ERROR',
			'S' => 'SECURITY'
		);

		$errorType = $aErrorTypes[$Type];

		CLog::instance()->connect();

		$utils = new DB_Common();

		$szInsert = "INSERT into " . DB_LOG_DATABASE . "." . CLog::getEventLogTable() . " (user_id, ip_address, timestamp_created, admin_notify, event_hint, description, log_type, is_dfl_access )
						VALUES ($user_id, '" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . "', now(), " . ($errorType == 'ERROR' ? "1" : "0") . ", " . $utils->quoteSmart($szHint) . ", " . $utils->quoteSmart($szDesc) . ", '{$aErrorTypes[$Type]}', 0)";

		if (!self::$_db)
		{
			echo 'logging not available';
			// exit;
		}

		try
		{
			$res = mysqli_query(self::$_db, $szInsert);
		}
		catch (Exception $e)
		{
			if ($e->getCode() == 1146)
			{
				CLog::createEventLogTable();
				mysqli_query(self::$_db, $szInsert);
			}
		}

		//echo "<P>$szInsert";
		//exit;
	}

	static function RecordCronTask($RecordsProcessed, $ProcessingStatusType, $CronLogType, $Processing_Comments)
	{
		$log = self::$_instance;

		CLog::instance()->connect();

		$utils = new DB_Common();

		$szInsert = "INSERT into " . DB_LOG_DATABASE . ".cron_log (timestamp_created, records_processed, cron_type,  processing_comments, processing_status )";

		$szInsert .= " VALUES (now(), $RecordsProcessed" . "," . $utils->quoteSmart($CronLogType) . "," . $utils->quoteSmart($Processing_Comments) . "," . $utils->quoteSmart($ProcessingStatusType) . ")";

		if (!self::$_db)
		{
			echo 'logging not available';
		}
		mysqli_query(self::$_db, $szInsert);
	}

	static function RecordException($ex)
	{
		self::Record("E_EXCEPTION:: " . $ex->getMessage() . $ex->getFile() . ': ' . $ex->getLine() . ': ' . $ex->getCode(), $ex->getLine(), $ex->getCode());
	}

	/******************************************************************
	 *
	 * Class:    CLog
	 * Method:    RecordNew
	 *
	 * In:        string -    $szDescription
	 *
	 * Desc:    Revised version of the old Record function to do the following:
	 *            Record more specific error types, so we can do better filtering.
	 *            Notify admin via email for serious stuff.
	 *
	 *         enum('DEBUG', 'NOTICE', 'WARNING', 'ERROR', 'SECURITY', 'LOGIN', 'PHP')
	 *
	 ********************************************************************/

	static function RecordNew($type, $szDescription, $szOptionalFile = "", $szOptionalLine = "", $notifyAdmin = false)
	{

		$log = self::$_instance;

		$szDesc = '';
		if ($szDescription)
		{
			$szDesc = $szDescription;
		}
		if ($szOptionalFile)
		{
			$szDesc .= '::' . $szOptionalFile;
		}
		if ($szOptionalLine)
		{
			$szDesc .= '::' . $szOptionalLine;
		}

		$user = CUser::getCurrentUser();
		if (!empty($user->id) && is_numeric($user->id))
		{
			$user_id = $user->id;
		}
		else
		{
			$user_id = 0;
		}

		switch ($type)
		{
			case self::STRICT:
			case self::NOTICE:
			case self::WARNING:
			case self::ERROR:
			case self::SECURITY:
			case self::LOGIN:
			case self::PHP:
			case self::DEBUG:
			case self::CCDECLINE:
			case self::UNHANDLEDEXCEPTION:
				break;
			default:
				$type = self::NOTICE;
				break;
		}

		CLog::instance()->connect();
		$utils = new DB_Common();

		$szInsert = "INSERT into " . DB_LOG_DATABASE . "." . CLog::getEventLogTable() . " (user_id, ip_address, timestamp_created, admin_notify, description, log_type, is_dfl_access )
						VALUES ($user_id, '" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . "', now(), " . ($notifyAdmin ? "1" : "0") . ", " . $utils->quoteSmart($szDesc) . ", '$type', 0);";

		if (!self::$_db)
		{
			echo 'logging not available';
			// exit;
		}

		try
		{
			$res = mysqli_query(self::$_db, $szInsert);
		}
		catch (Exception $e)
		{
			if ($e->getCode() == 1146)
			{
				CLog::createEventLogTable();
				mysqli_query(self::$_db, $szInsert);
			}
		}

		if ($notifyAdmin)
		{
			$time = date("M j Y g:i a");

			if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'LIVE')
			{
				$entry = "!!DREAMDINNERS SERVER ALERT!!\n\n";
			}
			else
			{
				$entry = "TEST SERVER - " . DD_SERVER_NAME . "\n\n!!DREAMDINNERS SERVER ALERT!!\n\n";
			}

			$entry .= "An event was logged under the following conditions:\n\n";
			$entry .= $szDesc . "\n\n";
			$entry .= "time=$time\n\n";
			$meAsArray = $user->toArray();
			$entry .= "USER:\n\n";
			foreach ($meAsArray as $k => $v)
			{
				$entry .= $k . "=" . $v . "\n";
			}

			$entry .= "\n_SERVER:\n";
			foreach ($_SERVER as $k => $v)
			{
				$entry .= $k . "=" . $v . "\n";
			}

			// sensitive values we should suppress
			$exclusionArray = array(
				'ccNumber',
				'ccSecurityCode',
				'gift_card_number',
				'gift_card_security_code',
				'credit_card_number',
				'credit_card_cvv',
				'credit_card_exp_month',
				'credit_card_exp_year',
				'payment1_cc_security_code',
				'payment2_cc_security_code',
				'payment1_ccNumber',
				'payment2_ccNumber',
				'default_payment1_ccNumber',
				'default_payment2_ccNumber',
				'default_payment1_cc_security_code',
				'default_payment2_cc_security_code',
				'password_login'
			);

			$reqCopy = $_REQUEST;

			if (isset($reqCopy['payment_data']['number']))
			{
				$reqCopy['payment_data']['number'] = "redacted";
			}

			$entry .= "\n_REQUEST:\n";
			foreach ($reqCopy as $k => $v)
			{
				if (!in_array($k, $exclusionArray))
				{
					if (is_array($v))
					{
						foreach ($k as $k2 => $v2)
						{
							$entry .= $k . " : " . $k2 . "=" . $v2 . "\n";
						}
					}
					else
					{
						$entry .= $k . "=" . $v . "\n";
					}
				}
			}
			/*
						$stackTraceMsg = "";
						try
						{
							throw new Exception('for stack trace');
						}
						catch (Exception $e)
						{
							$stackTraceMsg = $e->getTraceAsString();
						}

						$entry .= "\nSTACK:\n";
						$entry .= $stackTraceMsg;
			*/
			if (MAIL_ENABLED)
			{
				$from = 'From: <do-not-reply@dreamdinners.com>';
				$sendMail = @mail("ryan.snook@dreamdinners.com, josh.thayer@dreamdinners.com, brandy.latta@dreamdinners.com, mona.mcdonald@dreamdinners.com", "!!!DREAMDINNERS SERVER ALERT!!!", $entry, $from);
			}
		}
	}

	//CES: ACCESS_CHANGE

	static function NotifyFadmin($store_id, $subject, $msg)
	{
		if (MAIL_ENABLED)
		{
			if (DD_SERVER_NAME == 'LIVE')
			{

				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->id = $store_id;
				if ($StoreObj->find(true) && !empty($StoreObj->email_address))
				{
					$from = 'From: <do-not-reply@dreamdinners.com>';
					$sendMail = mail($StoreObj->email_address, $subject, $msg, $from);
				}
			}
			else
			{
				$from = 'From: <do-not-reply@dreamdinners.com>';
				$sendMail = mail('ryan.snook@dreamdinners.com', $subject . " from Test Server", $msg, $from);
			}
		}
	}

	static function RecordEmail($recipient_id, $recipient_email, $sender_id, $sender_email, $subject)
	{
		CLog::instance()->connect();
		$utils = new DB_Common();

		if (empty($recipient_id))
		{
			$recipient_id = '0';
		}

		$szInsert = "INSERT into " . DB_LOG_DATABASE . ".email_log (subject, recipient_user_id, recipient_email_address, sender_user_id, sender_email_address, timestamp_created )
							VALUES (" . $utils->quoteSmart($subject) . ", " . $utils->quoteSmart($recipient_id) . ", '" . $recipient_email . "'," . $utils->quoteSmart($sender_id) . ",'" . $sender_email . "',now())";

		if (!self::$_db)
		{
			echo 'logging not available';
			// exit;
		}
		mysqli_query(self::$_db, $szInsert);
	}

	static function RecordDebugTrace($message, $category = "NONE", $level = 1, $type = "DEBUG", $recordStack = false)
	{
		global $gEnabledTracingCategories;

		if (!empty($gEnabledTracingCategories) && in_array($category, $gEnabledTracingCategories) && $gEnabledTracingCategories[$category])
		{
			CLog::instance()->connect();
			$utils = new DB_Common();

			$user = CUser::getCurrentUser();
			$user_id = @$user->id;
			if (!$user_id)
			{
				$user_id = 0;
			}

			if ($recordStack)
			{
				$stackTraceMsg = "";
				try
				{
					throw new Exception('for stack trace');
				}
				catch (Exception $e)
				{
					$stackTraceMsg = $e->getTraceAsString();
				}
				$message .= "\nSTACK:\n";
				$message .= $stackTraceMsg;
			}

			$szInsert = "INSERT into " . DB_LOG_DATABASE . ".debug_log" . " (user_id, ip_address, timestamp_created, description, category, log_type, request_uri )
    						VALUES ($user_id, '" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . "', now(),
    						    " . $utils->quoteSmart($message) . ", '$category', '$type', '" . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . "')";

			if (!self::$_db)
			{
				echo 'logging not available';

				return;
			}

			mysqli_query(self::$_db, $szInsert);
		}
	}

	// sensitive values we should suppress
	static $exclusionArray = array(
		'ccNumber',
		'ccSecurityCode',
		'gift_card_number',
		'gift_card_security_code',
		'credit_card_number',
		'credit_card_cvv',
		'credit_card_exp_month',
		'credit_card_exp_year',
		'email_body',
		'billing_address',
		'billing_postal_code',
		'ccMonth',
		'ccYear',
		'ccNameOnCard',
		'debit_gift_card_number',
		'payment1_cc_security_code',
		'payment2_cc_security_code',
		'payment1_ccNumber',
		'payment2_ccNumber',
		'default_payment1_ccNumber',
		'payment1_gc_payment_number',
		'payment1_ccNameOnCard',
		'default_payment2_ccNumber',
		'default_payment1_cc_security_code',
		'default_payment2_cc_security_code',
		'login_password',
		'password',
		'password_confirm',
		'password_login',
		'payment1_ccMonth',
		'payment1_ccYear',
		'billing_address_1',
		'billing_postal_code_1',
		'payment2_ccNameOnCard',
		'payment2_ccMonth',
		'payment2_ccYear',
		'billing_address_2',
		'billing_postal_code_2',
		'cc_security_code'
	);

	static function sanitizeArrayForLog(&$parm_array)
	{
		foreach ($parm_array as $k => $v)
		{
			if (is_array($v))
			{
				self::sanitizeArrayForLog($v);
			}
			else if (in_array($k, self::$exclusionArray))
			{
				$parm_array[$k] = "redacted";
			}
		}
	}

	static function RecordNavigation($destination, $route)
	{
		if ($destination == 'debug_cart' || $destination == 'cart_watcher')
		{
			return;
		}

		CLog::instance()->connect();
		$utils = new DB_Common();

		$user_id = false;
		$user = CUser::getCurrentUser();
		if (!empty($user->id))
		{
			$user_id = $user->id;
		}
		if (!$user_id)
		{
			$user_id = 0;
		}

		$destination = mysqli_real_escape_string(self::$_db, $destination);
		$type = $_SERVER['REQUEST_METHOD'];
		$remoteAddr = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
		$cartKey = (isset($_COOKIE[DD_SERVER_NAME . '_cart']) ? $_COOKIE[DD_SERVER_NAME . '_cart'] : '');

		if ($route == "BOUNCE")
		{

			$szInsert = "INSERT into " . DB_LOG_DATABASE . ".nav_log
            (user_id,
            ip_address,
            resource_name,
            type,
            cart_key,
            route,
            request_time)
            VALUES ($user_id,
            '$remoteAddr',
            '$destination',
            '$type',
            '$cartKey',
            '$route',
            now())";
		}
		else if (DD_SERVER_NAME != 'HO_REPORTING')
		{
			$CartObj = CCart2::tempAccess();   // avoid using _instance
			$debugInfo = $CartObj->getDebugInfo();
			$cartContents = (!empty($debugInfo) ? $utils->quoteSmart(print_r($debugInfo, true)) : "");

			$postCopy = $_POST;

			// Note: self::sanitizeArrayForLog does recursively process arrays
			// However "number" is so generic we handle it here instead of include it
			//in the exclusion array
			if (isset($postCopy['payment_data']['number']))
			{
				$postCopy['payment_data']['number'] = "redacted";
			}
			if (isset($postCopy['add_payment_data']['number']))
			{
				$postCopy['add_payment_data']['number'] = "redacted";
			}

			self::sanitizeArrayForLog($postCopy);

			$content = (!empty($_GET) ? "GET\r\n" . print_r($_GET, true) . "\r\n\r\n" : "");
			$content .= (!empty($postCopy) ? "POST\r\n" . print_r($postCopy, true) : "");
			$content = $utils->quoteSmart($content);

			$cookies = (!empty($_COOKIE) ? $utils->quoteSmart(print_r($_COOKIE, true)) : "null");

			$szInsert = "INSERT into " . DB_LOG_DATABASE . ".nav_log 
                (user_id,
                 ip_address,
                 resource_name,
                 type,
                 cart_key,
                 route,
                 request_content,
                 cookies,
                 request_time,
                cart )
            VALUES ($user_id, 
                    '$remoteAddr',
                    '$destination',
                    '$type',
                    '$cartKey',
                    '$route',
                    $content,
                    $cookies,
                    now(),
                    $cartContents)";
		}

		if (!self::$_db)
		{
			echo 'logging not available';

			return;
		}

		mysqli_query(self::$_db, $szInsert);
	}

	static function RecordTemp($szDescription, $email = false)
	{
		if ($email)
		{
			$email = 'ryan.snook@dreamdinners.com';
		}

		if ($_SERVER['REMOTE_ADDR'] == 'xx.xx.xx.xx')
		{
			self::RecordIntense($szDescription, $email);
		}
	}

	/******************************************************************
	 *
	 * Class:    CLog
	 * Method:    RecordIntense
	 *
	 * In:        string -    $szDescription
	 *            string -    &email - a single email address or comma separated list of email addresses
	 *
	 *
	 * Desc:    Revised version of the old Record function to do the following:
	 *            Record an event of type DEBUG and email the passed in address a more detailed
	 *            record of the event. Details that are emailed are the current USER array, the $_SERVER array,
	 *            the $_REQUEST array (cc card # and security code are filtered) and the call stack.
	 *
	 *
	 ********************************************************************/
	static function RecordIntense($szDescription, $email = false)
	{

		$log = self::$_instance;

		$szDesc = '';
		if ($szDescription)
		{
			$szDesc = $szDescription;
		}

		$user = CUser::getCurrentUser();
		$user_id = @$user->id;
		if (!$user_id)
		{
			$user_id = 0;
		}

		CLog::instance()->connect();
		$utils = new DB_Common();

		$szInsert = "INSERT into " . DB_LOG_DATABASE . "." . CLog::getEventLogTable() . " (user_id, ip_address, timestamp_created, admin_notify, description, log_type, is_dfl_access )
						VALUES ($user_id, '" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . "', now(), 0, " . $utils->quoteSmart($szDesc) . ", 'DEBUG', 0);";

		if (!self::$_db)
		{
			echo 'logging not available';
			// exit;
		}

		try
		{
			$res = mysqli_query(self::$_db, $szInsert);
		}
		catch (Exception $e)
		{
			if ($e->getCode() == 1146)
			{
				CLog::createEventLogTable();
				mysqli_query(self::$_db, $szInsert);
			}
		}

		$time = date("M j Y g:i a");

		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'LIVE')
		{
			$entry = "!!INTENSE DEBUG MESSAGE!!\n\n";
		}
		else
		{
			$entry = "TEST SERVER - " . DD_SERVER_NAME . "\n\n !!INTENSE DEBUG MESSAGE!!\n\n";
		}

		$entry .= "An event was logged under the following conditions:\n\n";
		$entry .= $szDesc . "\n\n";
		$entry .= "time=$time\n\n";
		$meAsArray = $user->toArray();
		$entry .= "USER:\n\n";
		foreach ($meAsArray as $k => $v)
		{
			$entry .= $k . "=" . $v . "\n";
		}

		$entry .= "\n_SERVER:\n";
		foreach ($_SERVER as $k => $v)
		{
			$entry .= $k . "=" . $v . "\n";
		}

		// sensitive values we should suppress
		$exclusionArray = array(
			'ccNumber',
			'ccSecurityCode',
			'gift_card_number',
			'gift_card_security_code',
			'credit_card_number',
			'credit_card_cvv',
			'credit_card_exp_month',
			'credit_card_exp_year',
			'payment1_cc_security_code',
			'payment2_cc_security_code',
			'payment1_ccNumber',
			'payment2_ccNumber',
			'default_payment1_ccNumber',
			'default_payment2_ccNumber',
			'default_payment1_cc_security_code',
			'default_payment2_cc_security_code'
		);

		$reqCopy = $_REQUEST;

		if (isset($reqCopy['payment_data']['number']))
		{
			$reqCopy['payment_data']['number'] = "redacted";
		}

		$entry .= "\n_REQUEST:\n";
		foreach ($reqCopy as $k => $v)
		{
			if (!in_array($k, $exclusionArray))
			{
				if (is_array($v))
				{
					$entry .= $k . "\n";

					foreach ($v as $k2 => $v2)
					{
						$entry .= "\t" . $k2 . "=" . $v2 . "\n";
					}
				}
				else
				{
					$entry .= $k . "=" . $v . "\n";
				}
			}
		}

		$entry .= "\n\n_COOKIE:\n";
		foreach ($_COOKIE as $k => $v)
		{
			$entry .= $k . "=" . $v . "\n";
		}

		$stackTraceMsg = "";
		try
		{
			throw new Exception('for stack trace');
		}
		catch (Exception $e)
		{
			$stackTraceMsg = $e->getTraceAsString();
		}
		$entry .= "\nSTACK:\n";
		$entry .= $stackTraceMsg;

		if ($email && MAIL_ENABLED)
		{
			$from = 'From: <do-not-reply@dreamdinners.com>';
			$sendMail = @mail($email, "!!!INTENSE DEBUG MESSAGE!!!", $entry, $from);
		}
	}

	static function Assert($boolExpression, $szDescription)
	{
		if ($boolExpression)
		{
			return; // assert passes so do nothing
		}

		// assertion failed so record the info

		$log = self::$_instance;

		$szDesc = '';
		if ($szDescription)
		{
			$szDesc = $szDescription;
		}

		$user = CUser::getCurrentUser();
		$user_id = @$user->id;
		if (!$user_id)
		{
			$user_id = 0;
		}

		CLog::instance()->connect();
		$utils = new DB_Common();

		$szInsert = "INSERT into " . DB_LOG_DATABASE . "." . CLog::getEventLogTable() . " (user_id, ip_address, timestamp_created, admin_notify, description, log_type, is_dfl_access )
						VALUES ($user_id, '" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . "', now(), 0, " . $utils->quoteSmart($szDesc) . ", 'ASSERTION_FAILED', 0);";

		if (!self::$_db)
		{
			echo 'logging not available';
			// exit;
		}

		try
		{
			$res = mysqli_query(self::$_db, $szInsert);
		}
		catch (Exception $e)
		{
			if ($e->getCode() == 1146)
			{
				CLog::createEventLogTable();
				mysqli_query(self::$_db, $szInsert);
			}
		}

		if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE)
		{
			throw new exception('ASSERTION FAILED: ' . $szDesc);
		}
	}

	static function RecordReport($szReportName, $szReportParams)
	{


		$user = CUser::getCurrentUser();
		$user_id = @$user->id;
		if (!$user_id)
		{
			$user_id = 0;
		}

		$szDesc = $szReportName . " | " . $szReportParams;

		$errorType = self::REPORT;

		CLog::instance()->connect();

		$utils = new DB_Common();

		$szInsert = "INSERT into " . DB_LOG_DATABASE . "." . CLog::getEventLogTable() . " (user_id, ip_address, timestamp_created, admin_notify, event_hint, description, log_type, is_dfl_access )
		VALUES ($user_id, '" . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . "', now(), 0, null, " . $utils->quoteSmart($szDesc) . ", 'REPORT',0)";

		if (!self::$_db)
		{
			echo 'logging not available';
			// exit;
		}

		try
		{
			$res = mysqli_query(self::$_db, $szInsert);
		}
		catch (Exception $e)
		{
			if ($e->getCode() == 1146)
			{
				CLog::createEventLogTable();
				mysqli_query(self::$_db, $szInsert);
			}
		}

		//echo "<P>$szInsert";
		//exit;
	}

}

?>