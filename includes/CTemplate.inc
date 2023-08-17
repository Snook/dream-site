<?php
require_once("Savant2.php");
/* ------------------------------------------------------------------------------------------------
 *	Class: CTemplate
 *
 *	Description:	 		DD override of the Savant templating system.
 *
 *	Requires:
 *		CLog.inc.php
 *		Savant
 *
 * -------------------------------------------------------------------------------------------------- */
// DateTime format types
define("VERBOSE", 1); // Thursday October 20, 2005 - 05:30 PM
define("NORMAL", 2); // Oct 20, 2005 - 05:30 PM
define("COMPACT", 3); // 10/20/05 17:30:00
define("EXACT", 4); //Thursday October 20, 2005 05:30:00 PM
define("VERBOSE_DATE", 5); //Thursday October 20, 2005
define("TIME_ONLY", 6); //5:30 PM
define("VERBOSE_DATE_NO_YEAR", 7); //Thursday October 20
define("DATE_TIME", 8); //20110602T030000
define("MONTH_YEAR", 9); // August 2011
define("NORMAL_NO_YEAR", 10); // Oct 20 - 05:30 PM
define("SIMPLE_TIME", 11); // lower case am/pm - drop minutes if 0: EG. "9 am"
define("VERBOSE_DATE_NO_YEAR_W_COMMA", 12); //Thursday, October 20
define("CONDENSED_MONTH_YEAR", 13); // Dec 12
define("VERBOSE_MONTH_YEAR", 14); // December 2012
define("COMPACT_W_DAY_OF_WEEK", 15); // Thu Jun 13, 2013 - 6:00 PM
define("MONTH_DAY_YEAR", 16); // Dec, 20 2012
define("YEAR_MONTH_DAY", 17); // 2013-07-12
define("FULL_MONTH_DAY_YEAR", 18); // December, 20 2012
define("CONCISE", 19); // Oct 20, 2015 1:22:23pm
define("DATE_TIME_ITEMPROP", 20); // 2011-05-08T19:30
define("DAY_NAME", 21); // Tue
define("FULL_MONTH", 22); // October
define("DAY_ONLY", 23); // SAT
define("FULL_DAY", 24); // SATURDAY
define("YEAR_UNDERSCORE_MONTH", 25); //
define("MONTH_DAY", 26); //
define("HH_MM", 27); // 15:23
define("MYSQL", 28);
define("CONCISE_NO_SECONDS", 29); // Oct 20, 2015 1:22pm
define("MYSQL_TIMESTAMP", 30); // 2011-05-08 19:30:00
define("DATE_TIME_LOCAL", 31); // 2011-05-08T19:30
define("HH_MM_SS", 32); // 19:30:00 PM
define("NORMAL_NO_TIME", 33); // Oct 20, 2005
define("TIME", 34); // Oct 20, 2005

class CTemplate extends Savant2
{
	static private $head_script_array = array();
	static private $foot_script_array = array();
	static private $head_script_var_array = array();
	static private $head_css_array = array();
	static private $head_onload_array = array();
	static private $head_preload_array = array();
	static private $head_analytics_array = array();

	// admin pages built in bootstrap
	static $bootstrapPages = array(
		'admin_manage_site_notice',
		'admin_login',
		'admin_account',
		'admin_create_session',
		'admin_edit_session',
		'admin_order_details_view_all',
		'admin_order_details_view_all_multi',
		'admin_user_membership',
		'admin_manage_box',
		'admin_manage_coupon_codes',
		'admin_reports_map_activity',
		'admin_reports_national_entree_projection',
		'admin_reports_entree'
	);

	protected $debugInfoArray = array();
	protected $emergency_mode = false;

	public $isEditDeliveredOrder = false;

	const IE_lessthan_10 = "IE_lessthan_10";

	const IE_lessthan_9 = "IE_lessthan_9";
	const IE_lessthan_8 = "IE_lessthan_8";

	// navigation types
	const STANDARD = 'STANDARD';
	const INTRO = 'INTRO';
	const MADE_FOR_YOU = 'SPECIAL_EVENT';
	const SPECIAL_EVENT = 'SPECIAL_EVENT';
	const EVENT = 'EVENT'; // encompasses DREAM_TASTE, FUNDRAISER
	const DELIVERY = 'DELIVERY'; // Made For You for Delivery
	const DELIVERED = 'DELIVERED'; // Shipping from a Dist Ctr
	const ALL_STANDARD = 'ALL_STANDARD'; // Everything but Events and Intro

	static function noCache()
	{
		//force reload if Back button is pressed
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past
	}

	function __construct($template_path = false)
	{
		if (defined('DESIGNATE_AS_EMERGENCY_REPORTING_SERVER') && DESIGNATE_AS_EMERGENCY_REPORTING_SERVER)
		{
			$this->emergency_mode = true;
		}

		if (!$template_path)
		{
			$template_path = TEMPLATE_PATH;
		}

		$this->setPath('template', $template_path);

		$this->assign('page_is_bootstrap', false);
		if (!empty($_REQUEST['page']) && in_array($_REQUEST['page'], self::$bootstrapPages))
		{
			$this->assign('page_is_bootstrap', true);
		}

		// Load required external css files
		$this->initSetCSS();

		// Load required external script files
		$this->initSetScript();

		// Load required scripts
		$this->initOnload();

		// Load maintenance message
		$this->initMaintenance();
	}

	function initMaintenance()
	{
		$time_now = CTemplate::unix_to_mysql_timestamp(time());

		$Maint = DAO_CFactory::create('site_message');
		$Maint->query("SELECT *
				FROM site_message AS dmm
				WHERE dmm.message_start <= '" . $time_now . "'
				AND dmm.message_end >= '" . $time_now . "'
				AND dmm.is_active = '1'
				AND dmm.is_deleted = '0'
				AND dmm.audience != 'STORE'
				and dmm.message_type = 'SITE_MESSAGE'
				ORDER BY dmm.message_start ASC");

		$maintenance_array = array();

		// handle manual config.inc message
		if (defined('MAINT_PAGE_MESSAGE'))
		{
			$maintenance_array['message']['config_inc'] = array(
				'audience' => SITE_WIDE,
				'message' => MAINT_PAGE_MESSAGE
			);
			$maintenance_array['audience']['SITE_WIDE'] = true;
		}

		while ($Maint->fetch())
		{
			if (!empty($Maint->disable_site_start) && $time_now >= $Maint->disable_site_start && $time_now <= $Maint->message_end)
			{
				global $g_IP_ExclusionList;

				if (empty($g_IP_ExclusionList) || (!empty($g_IP_ExclusionList) && !in_array($_SERVER['REMOTE_ADDR'], $g_IP_ExclusionList)))
				{
					CApp::bounce('maint.php');
				}
			}
			else
			{
				$maintenance_array['message'][$Maint->id] = $Maint->toArray();
				$maintenance_array['audience'][$Maint->audience] = true;
			}
		}

		$this->assign('app_maintenance_message', $maintenance_array);
	}

	/*
	 *  Add info to an array that is echoed as a json encoded javascript array.
	 *  This data is sent to support.dreamdinners.com when a ticket is filed from the embedded form
	 */
	function addDebugInfo($name, $value)
	{
		$this->debugInfoArray[$name] = $value;
	}

	function setToastMsg($toastArray)
	{
		$existingMsg = array();

		if (CBrowserSession::getValue('toastMsg'))
		{
			$existingMsg = json_decode(CBrowserSession::getValue('toastMsg'));
		}

		$existingMsg[] = $toastArray;

		CBrowserSession::setValue('toastMsg', json_encode($existingMsg), false, true, false);
	}

	function setStatusMsg($msg)
	{
		$existingMsg = '';
		if (CBrowserSession::getValue('statusMsg'))
		{
			$existingMsg = CBrowserSession::getValue('statusMsg');
		}
		if (!empty($existingMsg))
		{
			CBrowserSession::setValue('statusMsg', $existingMsg . '<br />' . addslashes($msg));
		}
		else
		{
			CBrowserSession::setValue('statusMsg', addslashes($msg));
		}
	}

	function setErrorMsg($msg)
	{
		$existingMsg = '';
		if (CBrowserSession::getValue('errorMsg'))
		{
			$existingMsg = CBrowserSession::getValue('errorMsg');
		}
		if (!empty($existingMsg))
		{
			CBrowserSession::setValue('errorMsg', $existingMsg . '<br />' . addslashes($msg));
		}
		else
		{
			CBrowserSession::setValue('errorMsg', addslashes($msg));
		}
	}

	function setDebugMsg($msg)
	{
		if (DEBUG)
		{
			$existingMsg = '';
			if (CBrowserSession::getValue('debugMsg'))
			{
				$existingMsg = CBrowserSession::getValue('debugMsg');
			}
			if (!empty($existingMsg))
			{
				//CBrowserSession::setValue('debugMsg', $existingMsg . '<br />' . addslashes($msg));
			}
			else
			{
				CBrowserSession::setValue('debugMsg', addslashes($msg));
			}
		}
		else
		{
			if (CBrowserSession::getValue('debugMsg'))
			{
				CBrowserSession::setValue('debugMsg', false);
			}
		}
	}

	static function getUserDetailsLink($user_id, $addBackLink = true)
	{
		$retVal = "main.php?page=admin_user_details&id=" . $user_id;

		if ($addBackLink)
		{
			$retVal .= "&back=" . urlencode($_SERVER['REQUEST_URI']);
		}

		return $retVal;
	}

	/**
	 * Gets the message and unsets it from the cache
	 */
	function getStatusMsg()
	{
		$rtn = '';
		if (CBrowserSession::getValue('statusMsg') !== null)
		{
			$rtn = CBrowserSession::getValue('statusMsg');
			CBrowserSession::setValue('statusMsg', false);
		}

		return $rtn;
	}

	/**
	 * Gets the message and unsets it from the cache
	 */
	function getErrorMsg()
	{
		$rtn = '';

		if (CBrowserSession::getValue('errorMsg') !== null)
		{
			$rtn = CBrowserSession::getValue('errorMsg');
			CBrowserSession::setValue('errorMsg', false);
		}

		return $rtn;
	}

	/**
	 * Gets the message and unsets it from the cache
	 */
	function getDebugMsg()
	{
		$rtn = '';

		if (CBrowserSession::getValue('debugMsg') !== null)
		{
			$rtn = CBrowserSession::getValue('debugMsg');
			if ($rtn)
			{
				CBrowserSession::setValue('debugMsg', false);
			}
		}

		return $rtn;
	}

	/**
	 * Does the same thing as display, but returns the rendered template instead of echoing it.
	 */
	function render($tpl = null)
	{
		$result = $this->fetch($tpl);
		if ($this->isError($result))
		{
			if (isset($result->info['template']))
			{
				if (DEBUG)
				{
					echo $result->text . " " . $result->info['template'];
				}
				else
				{
					throw new Exception($result->text . " " . $result->info['template']);
				}
			}
			else
			{
				if (DEBUG)
				{
					echo $result->text;
				}
				else
				{
					throw new Exception($result->text);
				}
			}

			return null;
		}
		else
		{
			return $result;
		}
	}

	// fallback template if primary template isn't found
	function loadTemplateIfElse($if_Tpl, $else_Tpl, $setScript = false)
	{
		$file = $this->findFile('template', $if_Tpl);

		if ($file)
		{
			return $this->loadTemplate($if_Tpl, $setScript);
		}
		else
		{
			return $this->loadTemplate($else_Tpl, $setScript);
		}
	}

	function loadTemplate($tpl = null, $setScript = false)
	{
		// set to default template if none specified.
		if (is_null($tpl))
		{
			$tpl = $this->_template;
		}

		// find the template source.
		$file = $this->findFile('template', $tpl);
		if (!$file)
		{
			return $this->error(SAVANT2_ERROR_NOTEMPLATE, array('template' => $tpl));
		}

		// are we compiling source into a script?
		if (is_object($this->_compiler))
		{
			// compile the template source and get the path to the
			// compiled script (will be returned instead of the
			// source path)
			$result = $this->_compiler->compile($file);
		}
		else
		{
			// no compiling requested, return the source path
			$result = $file;
		}

		// is there a script from the compiler?
		if (!$result || $this->isError($result))
		{

			if ($setScript)
			{
				$this->_script = null;
			}

			// return an error, along with any error info
			// generated by the compiler.
			return $this->error(SAVANT2_ERROR_NOSCRIPT, array(
				'template' => $tpl,
				'compiler' => $result
			));
		}
		else
		{

			if ($setScript)
			{
				$this->_script = $result;
			}

			return $result;
		}
	}

	function assignBackBtn($defaultUrl = '?page=home')
	{
		if (array_key_exists('back', $_REQUEST) && $_REQUEST['back'])
		{
			$back = $_REQUEST['back'];
		}
		else
		{
			$back = $defaultUrl;
		}

		$this->assign('back', $back);
	}

	static function formatDecimal($number)
	{
		$number = trim(trim($number, '0'), '.');

		if (!empty($number))
		{
			return $number;
		}
		else
		{
			return 0;
		}
	}

	function assignAndFormatMetricPercent($name, $value)
	{

		if ($value < 0)
		{
			$formattedValue = "<span style='color:red'>" . $value . "%</span>";
		}
		else
		{
			$formattedValue = "<span style='color:green'>" . $value . "%</span>";
		}

		$this->assign($name, $formattedValue);
	}

	function assignAndFormatMetricDollars($name, $value)
	{

		$absValue = number_format(abs($value), 2);

		if ($value < 0)
		{
			$formattedValue = "<span style='color:red'>-$" . $absValue . "</span>";
		}
		else
		{
			$formattedValue = "<span style='color:green'>$" . $absValue . "</span>";
		}

		$this->assign($name, $formattedValue);
	}

	static function number_format($value, $decimals = 2, $sep = ",")
	{
		if ($value === null || $value === "")
		{
			$value = 0;
		}

		return number_format(floatval($value), $decimals, '.', $sep);
	}

	static function divide_and_format($dividend, $divisor, $decimals, $sep = ",")
	{
		if ($divisor == 0)
		{
			return self::number_format(0, $decimals, $sep);
		}

		return self::number_format($dividend / $divisor, $decimals, '.', $sep);
	}

	static function moneyFormat($dollarAmount)
	{
		return sprintf("%01.2f", $dollarAmount);
	}

	static function cleanDisplayStringForJavascript($str, $stripTags = false)
	{
		if ($stripTags)
		{
			$str = strip_tags($str);
		}

		return addslashes($str);
	}

	// This function works like moneyFormat except it always rounds fractional parts up
	function moneyFormatRoundUp($dollarAmount)
	{
		//conert to pennies
		$pennies = $dollarAmount * 100;
		//roundUp
		$pennies = ceil($pennies);
		//back to dollarAmount
		$dollarAmount = $pennies / 100;
		//format
		$dollarAmount = sprintf("%01.2f", $dollarAmount);

		return $dollarAmount;
	}

	/**
	 * simple method to encrypt or decrypt a plain text string
	 * initialization vector(IV) has to be the same when encrypting and decrypting
	 *
	 * @param string $action : can be 'encrypt' or 'decrypt'
	 * @param string $string : string to encrypt or decrypt
	 *
	 * @return string
	 */
	static function encrypt_decrypt($action, $string)
	{
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$secret_key = 'a3d06662fba0ef80dea717fa34be7594';
		$secret_iv = 'c86f233bbbeb9d958c1e35ba1bc2ff51';
		// hash
		$key = hash('sha256', $secret_key);

		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		if ($action == 'encrypt')
		{
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		}
		else if ($action == 'decrypt')
		{
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}

	static function recaptcha_mailHide($email_address, $link_text = false)
	{
		$encrypted_string = self::encrypt_decrypt('encrypt', $email_address);

		list($account, $domain) = explode('@', $email_address);

		$masked_email_address = strtolower(substr($account, 0, 4) . '...@' . $domain);

		$mail_reveal_link = "main.php?page=mailhide&c=" . $encrypted_string;

		$mail_reveal_html = '<a href="' . $mail_reveal_link . '" onclick="window.open(\'' . $mail_reveal_link . '\', \'\', \'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=500\'); return false;" data-tooltip="Spam prevention, please click to reveal this e-mail address" title="Spam prevention, please click to reveal this e-mail address">' . ((!empty($link_text)) ? $link_text : $masked_email_address) . '</a>';

		return array(
			$masked_email_address,
			$mail_reveal_link,
			$mail_reveal_html
		);
	}

	static function recaptcha_mailHideHtml($email_address, $link_text = false)
	{
		// temp solution until mcrypt is enabled
		if (CUser::isLoggedIn() || !function_exists("mcrypt_encrypt"))
		{
			return '<a class="text-uppercase" href="mailto:' . $email_address . '">' . (($link_text) ? $link_text : $email_address) . '</a>';
		}

		list($masked_email_address, $mail_reveal_link, $mail_reveal_html) = self::recaptcha_mailHide($email_address, $link_text);

		return $mail_reveal_html;
	}

	static function percentFormat($dollarAmount)
	{
		return sprintf("%d", $dollarAmount);
	}

	static function strftime_win32($format, $ts = null)
	{
		if (!$ts)
		{
			$ts = time();
		}

		$mapping = array(
			'%C' => sprintf("%02d", date("Y", $ts) / 100),
			'%D' => '%m/%d/%y',
			'%e' => sprintf("%' 2d", date("j", $ts)),
			'%h' => '%b',
			'%n' => "\n",
			'%l' => date("g", $ts),
			'%r' => date("h:i:s", $ts) . " %p",
			'%R' => date("H:i", $ts),
			'%t' => "\t",
			'%T' => '%H:%M:%S',
			'%u' => ($w = date("w", $ts)) ? $w : 7,
			'%P' => date("a", $ts),
			'%b' => date("M", $ts),
			'%g' => date("y", $ts),
			'%a' => date("D", $ts)
		);

		$format = str_replace(array_keys($mapping), array_values($mapping), $format);

		return strftime($format, $ts);
	}

	static function unix_to_mysql_timestamp($unix_timestamp = false)
	{
		if ($unix_timestamp)
		{
			return date('Y-m-d H:i:s', $unix_timestamp);
		}

		return date('Y-m-d H:i:s', time());
	}

	/**
	 * @throws Exception
	 */
	static function formatDateTime($format = 'Y-m-d H:i:s', $timeStamp = false, $StoreObj = false, $modify = false)
	{
		try
		{
			$dateTime = new DateTime();

			if ($timeStamp)
			{
				if (!strtotime($timeStamp))
				{
					$dateTime->setTimestamp($timeStamp);
				}
				else
				{
					$dateTime->setTimestamp(strtotime($timeStamp));
				}
			}

			if ($StoreObj)
			{
				$timeZone = DAO_CFactory::create('timezones');
				$timeZone->id = $StoreObj->timezone_id;

				if ($timeZone->find(true))
				{
					$dateTime->setTimezone(new DateTimeZone($timeZone->tz_name));
				}
			}

			if ($modify)
			{
				if ($modify == 'next thursday')
				{
					if ($dateTime->format('l') !== 'Thursday')
					{
						$dateTime->modify($modify);
					}
				}
				else
				{
					$dateTime->modify($modify);
				}
			}

			return $dateTime->format($format);
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	static function dateTimeFormat($dateStr, $format = NORMAL, $store = false, $toolTipFormat = false)
	{

		if (!isset($dateStr) || $dateStr == "" || $dateStr == "0000-00-00 00:00:00")
		{
			return "unavailable";
		}

		if ($store)
		{
			return CTimezones::localizeAndFormatTimeStamp($dateStr, $store, $format, $toolTipFormat);
		}

		// Check to see if date str is a date str else it might be already be a timestamp
		if (!strtotime($dateStr))
		{
			$timeStamp = $dateStr;
		}
		else
		{
			$timeStamp = strtotime($dateStr);
		}

		$formatStr = "";
		switch ($format)
		{
			case NORMAL:
				$formatStr = "%b %e, %Y - %I:%M %p";
				break;
			case NORMAL_NO_TIME:
				$formatStr = "%b %e, %Y";
				break;
			case VERBOSE:
				$formatStr = "%A %B %e, %Y - %I:%M %p";
				break;
			case COMPACT:
				$formatStr = "%c";
				break;
			case EXACT:
				$formatStr = "%b %e, %Y %r";
				break;
			case VERBOSE_DATE:
				$formatStr = "%A %B %e, %Y";
				break;
			case TIME_ONLY:
				$formatStr = "%l:%M %p";
				break;
			case TIME:
				$formatStr = "g:i:s A T";
				break;
			case HH_MM:
				$formatStr = "%H:%M";
				break;
			case HH_MM_SS:
				$formatStr = "%H:%M:%S %p";
				break;
			case SIMPLE_TIME:
				$formatStr = "%l:%M %P";
				break;
			case DAY_ONLY:
				$formatStr = "%a";
				break;
			case FULL_DAY:
				$formatStr = "%A";
				break;
			case VERBOSE_DATE_NO_YEAR:
				$formatStr = "%A %B %e";
				break;
			case DATE_TIME:
				$formatStr = "%Y%m%dT%H%M%S";
				break;
			case DATE_TIME_ITEMPROP:
			case DATE_TIME_LOCAL:
				$formatStr = "%Y-%m-%dT%H:%M:%S";
				break;
			case MYSQL_TIMESTAMP:
				$formatStr = "%Y-%m-%d %H:%M:%S";
				break;
			case MONTH_YEAR:
				$formatStr = "%B %Y";
				break;
			case NORMAL_NO_YEAR:
				$formatStr = "%b %e, %I:%M %p";
				break;
			case VERBOSE_DATE_NO_YEAR_W_COMMA:
				$formatStr = "%A, %B %e";
				break;
			case CONDENSED_MONTH_YEAR:
				$formatStr = "%b %y";
				break;
			case VERBOSE_MONTH_YEAR:
				$formatStr = "%B %Y";
				break;
			case COMPACT_W_DAY_OF_WEEK:
				$formatStr = "%a %b %e, %Y - %l:%M %p";
				break;
			case MONTH_DAY_YEAR:
				$formatStr = "%b %e, %Y";
				break;
			case YEAR_MONTH_DAY:
				$formatStr = "%Y-%m-%d";
				break;
			case FULL_MONTH_DAY_YEAR:
				$formatStr = "%B %e, %Y";
				break;
			case FULL_MONTH:
				$formatStr = "%B";
				break;
			case YEAR_UNDERSCORE_MONTH:
				$formatStr = "%Y_%m";
				break;
			case MONTH_DAY:
				$formatStr = "%B %e";
				break;
			case CONCISE:
				$formatStr = "%b %e, %Y %r";
				break;
			case CONCISE_NO_SECONDS:
				$formatStr = "%b %e, %Y %I:%M %p";
				break;
			default:
				$formatStr = $format;
		}

		if (strpos(php_uname(), "Windows") !== false)
		{
			$rtn = self::strftime_win32($formatStr, $timeStamp);
		}
		else
		{
			$rtn = strftime($formatStr, $timeStamp);
		}

		//the leading 0 on the time was annoying me--ToddW
		if (($format == NORMAL) || ($format == VERBOSE))
		{
			$rtn = str_replace(' - 0', ' - ', $rtn);
		}

		if ($format == SIMPLE_TIME)
		{
			$rtn = str_replace(':00', '', $rtn);
		}

		return $rtn;
	}

	static function sessionTypeDateTimeFormat($dateStr, $sessionType, $overrideDateFormat, $store = false, $toolTipFormat = false, $doAppendAdditional = false)
	{
		switch ($sessionType)
		{
			case CSession::WALK_IN:
				$result = self::dateTimeFormat($dateStr, NORMAL_NO_TIME) . ($doAppendAdditional ? ' - Walk-In' : '');
				break;
			default:
				$result = self::dateTimeFormat($dateStr, $overrideDateFormat, $store = false, $toolTipFormat = false);
		}

		return $result;
	}

	static function telephoneFormat($phoneString)
	{
		//strip all non-numeric
		$badChars = array(
			" ",
			".",
			"(",
			")",
			"-"
		);
		$rtn = str_replace($badChars, "", $phoneString);

		//make sure it is ten digits
		if (strlen($rtn) == 10)
		{
			return substr($rtn, 0, 3) . '-' . substr($rtn, 3, 3) . '-' . substr($rtn, 6, 4);
		}
		else if (strlen($rtn) == 11 && $rtn[0] == "1")
		{
			return substr($rtn, 1, 3) . '-' . substr($rtn, 4, 3) . '-' . substr($rtn, 7, 4);
		}

		//otherwise output the original string

		return $phoneString;
	}

	static function isEmptyFloat($val)
	{
		$val = (float)$val;

		if (!$val)
		{
			return true;
		}
		if ("$val" === '0.00')
		{
			return true;
		}
		if ("$val" === '0')
		{
			return true;
		}

		return false;
	}

	static function maskCC($ccNumStr)
	{
		return str_repeat('X', (strlen($ccNumStr) - 4)) . substr($ccNumStr, -4);
	}

	// Required css for all pages
	function initSetCSS()
	{
		$isAdmin = false;
		if (isset($_REQUEST['page']) && substr($_REQUEST['page'], 0, 6) == 'admin_')
		{
			$isAdmin = true;
		}

		// Following included in front and back end
		self::setCSS('//use.fontawesome.com/releases/v5.9.0/css/all.css');

		// Following included in front end
		if (!$isAdmin)
		{
			self::setCSS(CSS_PATH . '/customer/dreamdinners.min.css');
		}
		else // Following included in back end
		{
			self::setCSS(CSS_PATH . '/admin/admin-styles.min.css');
		}

		if (defined('DD_SERVER_NAME') && (DD_SERVER_NAME != 'LIVE'))
		{
			if (DD_SERVER_NAME == 'HO_REPORTING')
			{
				if ($isAdmin)
				{
					self::setCSS(CSS_PATH . '/admin/ho_reporting_style.css');
				}
				else
				{
					self::setCSS(CSS_PATH . '/customer/ho_reporting_style.css');
				}
			}

			if (DEBUG)
			{
				if ($isAdmin)
				{
					self::setCSS(CSS_PATH . '/admin/dev_style.css');
				}
			}
		}

		$this->assign('head_css', array_unique(self::$head_css_array));
	}

	// Required scripts for all pages
	function initSetScript()
	{
		$isAdmin = false;
		if (isset($_REQUEST['page']) && substr($_REQUEST['page'], 0, 6) == 'admin_')
		{
			$isAdmin = true;
		}

		// Following included in front end
		if (!$isAdmin)
		{
			// vendor scripts
			self::setScript('foot', SCRIPT_PATH . '/customer/vendor.min.js');

			// dd scripts
			self::setScript('foot', SCRIPT_PATH . '/customer/global.min.js'); // common DD functions

			// 3rd party
			if (defined('ENABLE_ANALYTICS') && ENABLE_ANALYTICS == true)
			{
				self::setScript('foot', SCRIPT_PATH . '/customer/analytics.min.js', true, true); // common DD functions

				self::setScript('head', 'https://www.googletagmanager.com/gtag/js?id=' . GOOGLE_ANALYTICS_ID, true, false, false); // google analytics
			}
			self::setScript('foot', 'https://addevent.com/libs/atc/1.6.1/atc.min.js', true, true); // add to calendar function

			if ((defined('DEBUG') && (DEBUG == true)) || CBrowserSession::getValue('FAUID'))
			{
				self::setScript('foot', SCRIPT_PATH . '/customer/debug.min.js', true, true); // common debug functions
			}
		}
		else
		{
			$script_pos = ($this->page_is_bootstrap ? 'foot' : 'head');

			// libraries
			self::setScript($script_pos, SCRIPT_PATH . '/admin/vendor.min.js');
			self::setScript($script_pos, SCRIPT_PATH . '/admin/vendor/jquery-ui/jquery-ui.min.js');

			if (!$this->page_is_bootstrap)
			{
				// shouldn't need these if using bootstrap framework
				self::setScript($script_pos, SCRIPT_PATH . '/admin/vendor/jquery.maskedinput.min.js'); // for force formatting of input fields
				self::setScript($script_pos, SCRIPT_PATH . '/admin/validate.min.js'); // common DD functions
				self::setScript($script_pos, SCRIPT_PATH . '/admin/safari_validate.min.js'); // DD form validation script
				self::setScript($script_pos, SCRIPT_PATH . '/admin/vendor/qtip/jquery.qtip.js'); // nice tooltip popup elements
			}

			self::setScript($script_pos, SCRIPT_PATH . '/admin/vendor/totalstorage/jquery.total-storage.min.js'); // localstorage management
			self::setScript($script_pos, SCRIPT_PATH . '/admin/vendor/jquery.idle.js'); // idle timeout

			// dd scripts
			self::setScript($script_pos, SCRIPT_PATH . '/customer/global.min.js'); // common DD functions

			self::setScript($script_pos, SCRIPT_PATH . '/admin/admin_global.min.js'); // drop down menu
			self::setScript($script_pos, SCRIPT_PATH . '/admin/help-master.min.js'); // admin help popup system
			self::setScript($script_pos, SCRIPT_PATH . '/admin/vendor/make_dropdown.min.js'); // drop down menu

			if ((defined('DEBUG') && (DEBUG == true)) || CBrowserSession::getValue('FAUID'))
			{
				self::setScript($script_pos, SCRIPT_PATH . '/customer/debug.min.js', true, true); // common debug functions
			}
		}
	}

	// Required scripts for all pages
	function initOnload()
	{
		$isAdmin = false;
		if (isset($_REQUEST['page']) && substr($_REQUEST['page'], 0, 6) == 'admin_')
		{
			$isAdmin = true;
		}

		if (DEBUG || (defined('DD_SERVER_NAME') && (DD_SERVER_NAME != 'LIVE')))
		{
			if ($msg = self::getDebugMsg())
			{
				$msg = addslashes(htmlspecialchars($msg));
				self::$head_onload_array[] = 'dd_message( { message: \'' . str_replace("\n", " \\\n", $msg) . '\' } );';
			}
		}

		// Follwing included in front end
		if ($isAdmin)
		{
			self::$head_onload_array[] = 'admin_global_init();';
		}

		$this->assign('head_onload', array_unique(self::$head_onload_array));
	}

	function getAnalytics()
	{
		$orderType = ((!empty($this->orderInfo['type_of_order'])) ? $this->orderInfo['type_of_order'] : false);
		$orderTypeTitle = ((!empty($this->orderInfo['type_of_order'])) ? ucwords(strtolower(str_replace('_', ' ', $this->orderInfo['type_of_order']))) . ' Order' : false);

		if (!empty($this->sessionInfo) && $this->sessionInfo['session_type'] == CSession::DELIVERED)
		{
			$orderType = CSession::DELIVERED;
			$orderTypeTitle = 'Delivered Order';
		}

		$gtag_Purchase = array();

		if (!empty($this->orderDetailsArray) && !empty($this->orderDetailsArray['menuInfo']['itemList']))
		{
			$gtagItemList = array();

			foreach ($this->orderDetailsArray['menuInfo']['itemList'] as $item)
			{
				$gtagItemList[] = array(
					'item_id' => $item['recipe_id'],
					'item_name' => $item['display_title'],
					'currency' => 'USD',
					'quantity' => $item['qty'],
					'price' => CTemplate::moneyFormat($item['price']),
					'item_variant' => CMenuItem::translatePricingType($item['pricing_type'])
				);
			}

			$gtag_Purchase[] = array(
				'currency' => 'USD',
				'transaction_id' => ((!empty($this->orderInfo['id'])) ? $this->orderInfo['id'] : 0),
				'order_type_title' => $orderTypeTitle,
				'store_id' => $this->orderDetailsArray['orderInfo']['store_id'],
				'order_type' => $this->orderDetailsArray['orderInfo']['order_type'],
				'value' => CTemplate::moneyFormat(((!empty($this->gift_card_total)) ? $this->gift_card_total : ((!empty($this->order_total)) ? $this->order_total : 0))),
				'grand_total' => CTemplate::moneyFormat($this->orderDetailsArray['orderInfo']['grand_total']),
				'coupon' => ((!empty($this->couponCode)) ? $this->couponCode : ''),
				'items' => $gtagItemList
			);
		}

		if (!empty($this->gift_card_purchase_array))
		{
			foreach ($this->gift_card_purchase_array as $order)
			{
				$gtag_Purchase[] = array(
					'currency' => 'USD',
					'transaction_id' => 'GC-' . ((!empty($order['id'])) ? $order['id'] : 0),
					'order_type_title' => 'Gift Card Order',
					'value' => CTemplate::moneyFormat($order['gc_amount']),
					'coupon' => ((!empty($this->couponCode)) ? $this->couponCode : ''),
					'items' => array(
						array(
							'item_id' => 'GC-' . $order['media_type'],
							'item_name' => $order['gc_media_type'],
							'currency' => 'USD',
							'quantity' => 1,
							'price' => CTemplate::moneyFormat($order['gc_amount']),
							'item_variant' => $order['design_type_id']
						)
					)
				);
			}
		}

		self::$head_analytics_array = array(
			'analytics' => ENABLE_ANALYTICS,
			'order_id' => ((!empty($this->orderInfo['id'])) ? $this->orderInfo['id'] : 0),
			'order_type' => $orderType,
			'order_type_title' => $orderTypeTitle,
			'session_type_title' => ((!empty($this->sessionInfo)) ? $this->sessionInfo['session_type_title'] : false),
			'session_type_title_string' => ((!empty($this->sessionInfo)) ? str_replace(' ', '_', $this->sessionInfo['session_type_title']) : false),
			'order_total' => ((!empty($this->order_total)) ? $this->order_total : 0),
			'cart_item' => ((!empty($this->cart_info['item_info'])) ? $this->cart_info['item_info'] : false),
			'order_item' => ((!empty($this->menuInfo['itemList'])) ? $this->menuInfo['itemList'] : false),
			'menu_id' => ((!empty($this->menuInfo['menu_id'])) ? $this->menuInfo['menu_id'] : 0),
			'gift_card_total' => ((!empty($this->gift_card_total)) ? $this->gift_card_total : 0),
			'gift_card_orders' => ((!empty($this->orders)) ? $this->orders : false),
			'gift_card_array' => ((!empty($this->gift_card_purchase_array)) ? $this->gift_card_purchase_array : false),
			'total' => ((!empty($this->gift_card_total)) ? $this->gift_card_total : ((!empty($this->order_total)) ? $this->order_total : 0)),
			'email' => CUser::getCurrentUser()->primary_email,
			'email_sha256' => ((!empty(CUser::getCurrentUser()->primary_email)) ? hash('sha256', CUser::getCurrentUser()->primary_email) : null),
			'store_VR_code' => ((!empty($this->store_VR_code)) ? $this->store_VR_code : 0),
			'customer' => CUser::getCurrentUser()->id,
			'intro_customer' => (CUser::isLoggedIn() && CUser::getCurrentUser()->isNewBundleCustomer()),
			'page' => ((!empty($this->page)) ? $this->page : false),
			'page_title' => ((!empty($this->page_title)) ? $this->page_title : 'DreamDinners.com'),
			'smfcArray' => json_decode(CBrowserSession::getValue('smfc')),
			'dd_thank_you' => CBrowserSession::getValue('dd_thank_you'),
			'vendor' => array(
				'google' => array(
					'enabled' => true,
					'analytics_id' => GOOGLE_ANALYTICS_ID,
					'gtag' => array(
						'purchase' => $gtag_Purchase
					)
				),
				'twitter' => array(
					'enabled' => false,
				),
				'nextdoor' => array(
					'enabled' => false
				),
				'wisepop' => array(
					'enabled' => in_array(((!empty($this->page)) ? $this->page : false), array(
						'meal_prep_dinners',
						'meal_prep_delivery',
						'meal_prep_starter_pack',
						'local_meal_prep',
						'meal_prep_company',
						'prepared_meals',
						'session_menu',
						'email_signup',
						'order_details',
						'sp1n2',
						'sp3c1al',
						'spec1al3'
					))
				)
			)
		);

		// salesforce email studio
		if (isset($_REQUEST['sfmc_sub']) && isset($_REQUEST['j']) && isset($_REQUEST['l']) && isset($_REQUEST['u']) && isset($_REQUEST['mid']) && isset($_REQUEST['jb']))
		{
			self::$head_analytics_array['smfcArray'] = array(
				'sfmc_sub' => CGPC::do_clean($_REQUEST['sfmc_sub'], TYPE_INT, true),
				'j' => CGPC::do_clean($_REQUEST['j'], TYPE_INT, true),
				'l' => CGPC::do_clean($_REQUEST['l'], TYPE_NOHTML, true),
				'u' => CGPC::do_clean($_REQUEST['u'], TYPE_INT, true),
				'mid' => CGPC::do_clean($_REQUEST['mid'], TYPE_INT, true),
				'jb' => CGPC::do_clean($_REQUEST['jb'], TYPE_INT, true)
			);
			CBrowserSession::setValue('smfc', json_encode(self::$head_analytics_array['smfcArray']), false, true, false); // can't be httponly so javascript can remove the cookie
		}

		return self::$head_analytics_array;
	}

	static function browserIsTarget($target)
	{
		if ($target == CTemplate::IE_lessthan_10 && self::isIE())
		{
			$retVal = preg_match('/MSIE [1-9]/', $_SERVER['HTTP_USER_AGENT']);

			return ($retVal == 0 ? false : true);
		}
		else if ($target == CTemplate::IE_lessthan_9 && self::isIE())
		{
			$retVal = preg_match('/MSIE [1-8]/', $_SERVER['HTTP_USER_AGENT']);

			return ($retVal == 0 ? false : true);
		}
		else if ($target == CTemplate::IE_lessthan_8 && self::isIE())
		{
			$retVal = preg_match('/MSIE [1-7]/', $_SERVER['HTTP_USER_AGENT']);

			return ($retVal == 0 ? false : true);
		}

		return false;
	}

	function setCSS($stylesheet, $target = false)
	{
		if (!$target || (self::browserIsTarget($target)))
		{
			self::$head_css_array[] = trim($stylesheet) . ((strpos($stylesheet, '?')) ? '&amp;' : '?') . 'ddv=' . JAVASCRIPT_CSS_VERSION;

			$this->assign('head_css', array_unique(self::$head_css_array));
		}
	}

	function setScript($position, $javascript, $async = false, $defer = false, $crossorigin = false, $preconnect = false)
	{
		if ($preconnect)
		{
			self::setPreload($preconnect, false, true);
		}

		if ($position == 'foot')
		{
			$scriptArray = self::$foot_script_array;
		}
		else if ($position == 'head')
		{
			$scriptArray = self::$head_script_array;
		}

		$scriptArray[trim($javascript)] = array(
			'src' => trim($javascript) . ((strpos($javascript, '?')) ? '&amp;' : '?') . 'ddv=' . JAVASCRIPT_CSS_VERSION,
			'async' => $async,
			'defer' => $defer,
			'crossorigin' => $crossorigin
		);

		if ($position == 'foot')
		{
			self::$foot_script_array = $scriptArray;

			$this->assign('foot_script', self::$foot_script_array);
		}
		else if ($position == 'head')
		{
			self::$head_script_array = $scriptArray;

			$this->assign('head_script', self::$head_script_array);
		}
	}

	function setPreload($path, $type, $preconnect = false)
	{
		self::$head_preload_array[] = array(
			'type' => $type,
			'path' => $path,
			'preconnect' => $preconnect
		);

		$this->assign('head_preload', self::$head_preload_array);
	}

	function setScriptVar($javascript)
	{
		self::$head_script_var_array[] = trim($javascript);

		$this->assign('head_script_var', array_unique(self::$head_script_var_array));
	}

	function setOnLoad($javascript)
	{
		self::$head_onload_array[] = trim($javascript);

		$this->assign('head_onload', array_unique(self::$head_onload_array));
	}

	function isStoreView()
	{
		return CApp::$isStoreView;
	}

	function httpServerAddress()
	{
		return HTTP_BASE . ($this->isStoreView() ? 'storeview/' : '');
	}

	function httpsServerAddress()
	{
		return HTTPS_BASE . ($this->isStoreView() ? 'storeview/' : '');
	}

	function isSafari()
	{
		$Agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($Agent, 'Safari') === false)
		{
			return false;
		}

		return true;
	}

	static function isIE()
	{
		$ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
		if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false))
		{
			return true;
		}
	}

	static function isOldIE()
	{
		$ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
		if (preg_match('~MSIE|Internet Explorer~i', $ua))
		{
			return true;
		}
	}

	static function isMobileSafari()
	{
		if (strstr($_SERVER['HTTP_USER_AGENT'], " AppleWebKit/") && strstr($_SERVER['HTTP_USER_AGENT'], " Mobile/"))
		{
			return true;
		}

		return false;
	}

	static function isIE11()
	{
		$ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
		if (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false)
		{
			return true;
		}

		return false;
	}

	static function cartSessionTypeName($session_type_switch)
	{
		switch ($session_type_switch)
		{
			case CTemplate::STANDARD:
				return "Assembly Session";
				break;
			case CTemplate::INTRO:
				return "Meal Prep Starter Pack";
				break;
			case CTemplate::EVENT:
				return "Special Event";
				break;
			case CTemplate::MADE_FOR_YOU:
				return "Made For You";
				break;
		}
	}

	function sessionTypeFormat(&$sessionData)
	{
		if (isset($sessionData['session_type']))
		{
			if ($sessionData['session_publish_state'] == CSession::CLOSED || $sessionData['expired'] == true)
			{
				return ': Closed';
			}
			if ($sessionData['percent_full'] == '100')
			{
				return ': Full';
			}
			else if ($sessionData['session_type'] == CSession::TODD)
			{
				return ': Taste of Dream Dinners';
			}
			else if ($sessionData['session_type'] == CSession::SPECIAL_EVENT)
			{
				return ': Made for You';
			}
			else if ($sessionData['session_type'] == CSession::DREAM_TASTE)
			{
				return ': Meal Prep Workshop';
			}
			else if ($sessionData['session_type'] == CSession::FUNDRAISER)
			{
				return ': Fundraiser Event';
			}
			else if (!empty($sessionData['session_password']))
			{
				return ': Private';
			}
			else
			{
				return '';
			}
		}

		return 'unknown';
	}

	/*
	 *  Attempt to set upper case words including hyphenated words
	*/
	static function ucwords($string)
	{
		// words that should be lower case
		$exceptions = array(
			'and',
			'or'
		);

		$new_string = '';
		$words = explode(' ', $string);

		foreach ($words as $word)
		{
			if (trim($word) != '')
			{
				if (in_array(strtolower($word), $exceptions))
				{
					$word = strtolower($word);
				}
				else
				{
					if (strtoupper($word) == $word)
					{
						// word is all caps, so set to lower then ucwords
						$word = str_replace('- ', '-', ucwords(strtolower(str_replace('-', '- ', $word))));
					}
					else
					{
						// word is not all caps, so just ucwords
						$word = str_replace('- ', '-', ucwords(str_replace('-', '- ', $word)));
					}
				}

				$new_string .= $word . ' ';
			}
		}

		return trim($new_string);
	}

	static function obfuscateCardNumber($card_number)
	{
		if (is_null($card_number))
		{
			return '';
		}

		return str_repeat('X', (strlen($card_number) - 4)) . substr($card_number, -4);
	}

	static function lastFourCardNumber($card_number)
	{
		return substr($card_number, -4);
	}
}

?>