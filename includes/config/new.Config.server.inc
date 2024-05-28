<?php
date_default_timezone_set('America/New_York');

// Send users to maintenance page
//define('SITE_DISABLED', true);

// Optional: Message to appear only on maintenance page
//define('MAINT_PAGE_MESSAGE', 'Our website is currently down for scheduled maintenance, site expected to be up at 9:00 PM PDT.');

// List of IPs who won't get redirect to maintenance page
//$g_IP_ExclusionList = array('127.0.0.1','50.34.44.26');

// THIS SERVER'S UNIQUE IDENTITY
const DD_SERVER_NAME = 'LIVE'; // dreamdinners.com
//const DD_SERVER_NAME = 'CHORS'; // chors.dreamdinners.com
//const DD_SERVER_NAME = 'SANDBOX'; // sandbox.dreamdinners.com
//const DD_SERVER_NAME = 'BRANCH'; // branch.dreamdinners.com
//const DD_SERVER_NAME = 'JOHN'; // Local development

// ENVIRONMENT TYPE
const SERVER_ENV = 'LIVE'; // 'LIVE' for production websites, ie: Customer website or reporting servers
//const SERVER_ENV = 'TEST'; // Sandbox/Branch
//const SERVER_ENV = 'DEV'; // Local development

// DNS - IDENTITY
const HTTP_DOMAIN = 'dreamdinners.com'; // Live
//const HTTP_DOMAIN = 'chors.dreamdinners.com'; // Reporting
//const HTTP_DOMAIN = 'sandbox.dreamdinners.com'; // Sandbox
//const HTTP_DOMAIN = 'branch.dreamdinners.com'; // Branch
//const HTTP_DOMAIN = 'dreamsite.dreamdinners.test'; // Local development

const HTTPS_DOMAIN = HTTP_DOMAIN; // HTTPS domain same as HTTP
define('COOKIE_DOMAIN', '.' . HTTP_DOMAIN);

define('HTTP_SERVER', 'http://' . HTTP_DOMAIN);
define('HTTPS_SERVER', 'https://' . HTTPS_DOMAIN);

// CDN image server not used but could be resurrected for a speed bump
const HIGH_CAP_IMAGE_SERVER = 'https://cdn.dreamdinners.com';
const HIGH_CAP_IMAGE_BASE = '/';

// PATHS - Environment specific
switch (SERVER_ENV)
{
	case 'DEV':
		// PATHS
		define("APP_BASE", 'C:/Development/DreamWeb/dream-site/'); // Server path, requires trailing slash
		define("WEB_BASE", '/');
		define("REPORT_OUTPUT_BASE", APP_BASE . '/reports/output');

		define("MAIL_HANDLER", 'sendmail');
		define("MAIL_ENABLED", true);
		define("MAIL_TEST_MODE", true);
		$mail_params = array(
			"sendmail_path" => 'C:\Development\sendmail\sendmail.exe',
			"sendmail_args" => '-t'
		);
		break;
	case 'TEST':
	case 'LIVE':
	default:
		// PATHS
		define("APP_BASE", '/DreamWeb/dream-site/'); // Server path, requires trailing slash
		define("WEB_BASE", '/');
		define("REPORT_OUTPUT_BASE", APP_BASE . '/reports/output');

		define("MAIL_HANDLER", 'sendmail');
		define("MAIL_ENABLED", true);
		define("MAIL_TEST_MODE", false);
		$mail_params = array(
			"sendmail_path" => '/usr/lib/sendmail',
			"sendmail_args" => ''
		);
		break;
}

/**
 *
 * SWITCHING PROVIDED FOR LOCAL DEV TUNNELS
 *
 * LIVE OR TEST SERVERS  ** DO NOT ** PROVIDE PASSWORDS FOR ALTERNATE SERVERS WHEN DEPLOYED
 *
 * */
// SET THE DB VARS
const DATA_SOURCE = 'LIVE';
//const DATA_SOURCE = 'CHORS');
//const DATA_SOURCE = 'SANDBOX');
//const DATA_SOURCE = 'BRANCH');
//const DATA_SOURCE = 'LOCAL';

switch (DATA_SOURCE)
{
	case 'LOCAL':
		// Main site database
		define('DB_SERVER', 'localhost');
		define('DB_DATABASE', 'dreamsite');
		define('DB_SERVER_USERNAME', '');
		define('DB_SERVER_PASSWORD', '');

		// Logging database
		define('DB_LOG_SERVER', 'localhost');
		define('DB_LOG_DATABASE', 'dreamlog');
		define('DB_LOG_SERVER_USERNAME', '');
		define('DB_LOG_SERVER_PASSWORD', '');

		// Cart database
		define('DB_CART_SERVER', 'localhost');
		define('DB_CART_DATABASE', 'dreamcart');
		define('DB_CART_SERVER_USERNAME', '');
		define('DB_CART_SERVER_PASSWORD', '');
		break;
	case 'SANDBOX':
		// Main site database
		define('DB_SERVER', 'localhost');
		define('DB_DATABASE', 'dreamsite');
		define('DB_SERVER_USERNAME', '');
		define('DB_SERVER_PASSWORD', '');

		// Logging database
		define('DB_LOG_SERVER', 'localhost');
		define('DB_LOG_DATABASE', 'dreamlog');
		define('DB_LOG_SERVER_USERNAME', '');
		define('DB_LOG_SERVER_PASSWORD', '');

		// Cart database
		define('DB_CART_SERVER', 'localhost');
		define('DB_CART_DATABASE', 'dreamcart');
		define('DB_CART_SERVER_USERNAME', '');
		define('DB_CART_SERVER_PASSWORD', '');
	case 'BRANCH':
		// Main site database
		define('DB_SERVER', 'localhost');
		define('DB_DATABASE', 'dreamsite');
		define('DB_SERVER_USERNAME', '');
		define('DB_SERVER_PASSWORD', '');

		// Logging database
		define('DB_LOG_SERVER', 'localhost');
		define('DB_LOG_DATABASE', 'dreamlog');
		define('DB_LOG_SERVER_USERNAME', '');
		define('DB_LOG_SERVER_PASSWORD', '');

		// Cart database
		define('DB_CART_SERVER', 'localhost');
		define('DB_CART_DATABASE', 'dreamcart');
		define('DB_CART_SERVER_USERNAME', '');
		define('DB_CART_SERVER_PASSWORD', '');
		break;
	case 'CHORS':
		// Main site database
		define('DB_SERVER', 'localhost');
		define('DB_DATABASE', 'dreamsite');
		define('DB_SERVER_USERNAME', '');
		define('DB_SERVER_PASSWORD', '');

		// Logging database
		define('DB_LOG_SERVER', 'localhost');
		define('DB_LOG_DATABASE', 'dreamlog');
		define('DB_LOG_SERVER_USERNAME', '');
		define('DB_LOG_SERVER_PASSWORD', '');

		// Cart database
		define('DB_CART_SERVER', 'localhost');
		define('DB_CART_DATABASE', 'dreamcart');
		define('DB_CART_SERVER_USERNAME', '');
		define('DB_CART_SERVER_PASSWORD', '');
		break;
	case 'LIVE':
	default:
		// Main site database
		define('DB_SERVER', 'localhost');
		define('DB_DATABASE', 'dreamsite');
		define('DB_SERVER_USERNAME', '');
		define('DB_SERVER_PASSWORD', '');

		// Logging database
		define('DB_LOG_SERVER', 'localhost');
		define('DB_LOG_DATABASE', 'dreamlog');
		define('DB_LOG_SERVER_USERNAME', '');
		define('DB_LOG_SERVER_PASSWORD', '');

		// Cart database
		define('DB_CART_SERVER', 'localhost');
		define('DB_CART_DATABASE', 'dreamcart');
		define('DB_CART_SERVER_USERNAME', '');
		define('DB_CART_SERVER_PASSWORD', '');
		break;
}

switch (SERVER_ENV)
{
	case 'DEV':
	case 'TEST':
		define("DEBUG", true);
		define('DEBUG_TRACE_LEVEL', 2);
		define("DISABLE_CRON", true);
		define("CRON_TEST_MODE", true);
		define("RECORD_NAVIGATION", true);
		define("DD_THANK_YOU_DEBUG", false);

		define('STORE_NOTIFICATION_ALERT_TEST_EMAIL', 'ryan.snook@dreamdinners.com,brandy.latta@dreamdinners.com');

		// ECommerce
		define("PFP_TEST_MODE", true);
		define("USE_CORPORATE_TEST_ACCOUNT", 175); // Kennewick for test credit card

		define('DESIGNATE_AS_EMERGENCY_REPORTING_SERVER', false);

		define("DEVELOPMENT_MODE", true); // In development mode Failed Assertions throw and exception
		define("ALLOW_TV_OFFER_IF_PREVIOUS", true);
		define("DEBIT_GIFT_CARD_TESTMODE", true);

		define('DEV_BASE_NAME', DD_SERVER_NAME);

		// Site Behavior
		define("ENABLE_SITE_ADMIN_TIMEOUT", false);
		define("ENABLE_FRANCHISE_INFO_COLLECTION", true);
		define("ALLOW_SITE_WIDE_REPORTING", true);
		define("HOSTED_AS_REPORTING_SERVER", false);
		define("ENABLE_ANALYTICS", false);
		define("ENABLE_HELP_SEARCH", true);
		define("ENABLE_CUSTOMER_INTRO_MFY", true);
		define("ENABLE_TC_DELAYED_PAYMENT_AGREE", true);
		define("USE_GLOBAL_SIDES_MENU", false);
		define("SHOW_SIDES_CATEGORY_ON_MENU", true);

		// Avalara
		define("AVALARA_API_ENDPOINT_USE_MOCK", true);

		// Salesforce
		define("ENABLE_SMS_PREFERENCE", false);
		define("ENABLE_SMS_PREFERENCE_ORDER_DETAILS", false);
		define("ENABLE_EMAIL_PREFERENCE", false);

		// Shipstation
		//If this value exists then order will not be sent to shipstation at all
		define('SHIPSTATION_DONT_SEND' , 'IF_DEFINED_WONT_SEND');
		//If these const are defined...orders will be marked as tests in ShipStations if above is removed
		define('SHIPSTATION_TEST_ORDER_PREFIX' , 'TEST_IGNORE_');
		define('SHIPSTATION_TEST_ORDER_TAG' , 107045);
		define('SHIPSTATION_TEST_ORDER_WHAREHOUSEID' , 62604);

		$gEnabledTracingCategories = array(
			"CART" => true,
			"TR_TRACING" => true,
			"TR_TRACING_INTENSE" => true,
			"PAGE_REQUEST" => false,
			"CSRF" => true,
			"PROC_REQUEST" => false,
			"MENU_EDITOR" => false,
			"SOAP" => false,
			"NONE" => true
		);
		break;
	case 'LIVE':
	default:
		define("DEBUG", false); // Live = false
		define('DEBUG_TRACE_LEVEL', 0); // Live = 0
		define("CRON_TEST_MODE", false); // Live = false
		define("DISABLE_CRON", false); // Live = false
		define("RECORD_NAVIGATION", true); // Records every GET and POST - filters CHD - VERY useful but has overhead
		define("DD_THANK_YOU_DEBUG", false);

		// ECommerce
		define("PFP_TEST_MODE", false);// Live = false
		define("USE_CORPORATE_TEST_ACCOUNT", false);// Live = false

		define('DESIGNATE_AS_EMERGENCY_REPORTING_SERVER', false);

		define("DEVELOPMENT_MODE", false); // Live = false
		define("ALLOW_TV_OFFER_IF_PREVIOUS", false); // Live = false
		define("DEBIT_GIFT_CARD_TESTMODE", false); // Live = false

		define('DEV_BASE_NAME', false); // Live = false

		// Site Behavior
		define("ENABLE_SITE_ADMIN_TIMEOUT", true); // Live = true
		define("ENABLE_FRANCHISE_INFO_COLLECTION", true);
		define("ALLOW_SITE_WIDE_REPORTING", false); // Live = false
		define("HOSTED_AS_REPORTING_SERVER", false); // Live = false
		define("ENABLE_ANALYTICS", true); // Live = true
		define("ENABLE_HELP_SEARCH", true); // Live = true
		define("ENABLE_CUSTOMER_INTRO_MFY", true); // Live = true, Customers can order Starter Pack in Made For You sessions
		define("ENABLE_TC_DELAYED_PAYMENT_AGREE", true); // Live = true
		define("USE_GLOBAL_SIDES_MENU", false); // Live = false
		define("SHOW_SIDES_CATEGORY_ON_MENU", true);

		// Avalara
		define("AVALARA_API_ENDPOINT_USE_MOCK", false);

		// Salesforce
		define("ENABLE_SMS_PREFERENCE", true);
		define("ENABLE_SMS_PREFERENCE_ORDER_DETAILS", true);
		define("ENABLE_EMAIL_PREFERENCE", true);

		// Shipstation
		//If this value exists then order will not be sent to shipstation at all
		//define('SHIPSTATION_DONT_SEND' , 'IF_DEFINED_WONT_SEND');
		//If these const are defined...orders will be marked as tests in ShipStations if above is removed
		//define('SHIPSTATION_TEST_ORDER_PREFIX' , 'TEST_IGNORE_');
		//define('SHIPSTATION_TEST_ORDER_TAG' , 107045);
		//define('SHIPSTATION_TEST_ORDER_WHAREHOUSEID' , 62604);

		$gEnabledTracingCategories = array(
			"CART" => false,
			"TR_TRACING" => false,
			"TR_TRACING_INTENSE" => false,
			"PAGE_REQUEST" => false,
			"CSRF" => false,
			"PROC_REQUEST" => false,
			"MENU_EDITOR" => false,
			"SOAP" => false,
			"NONE" => false
		);
		break;
}

const CURRENT_PLATE_POINTS_VERSION = 1.1;
const FIRST_PLATE_POINTS_1_1_ORDER_ID = 3424217;

/**
 *
 * KEYS and APIs
 *
 */
// FreshDesk
const REMAUTHSTR = '';
const USE_FRESHDESK_SUPPORT = true;
const FRESH_DESK_API_KEY = '';
const FRESH_DESK_API_PASSWORD = '';

$privateKey = <<<EOD
-----BEGIN RSA PRIVATE KEY----- 
-----END RSA PRIVATE KEY-----
EOD;
define('REMAUTHSTRNEW', $privateKey);

// Blackboard/GeniusSys
const GENIUS_TOKEN = "";
const GENIUS_KEY = '';

// Box.com Sysco Invoices
const BOX_FTP_USER = '';
const BOX_FTP_PASS = '';

// Power BI
const POWER_BI_CLIENT_ID = '';
const POWER_BI_CLIENT_SECRET = '';
const POWER_BI_TENANT_ID = '';

// Salesforce
const SFMC_AUTH_API_ENDPOINT = '';
const SFMC_REST_API_ENDPOINT = '';
const SFMC_CLIENT_ID = '';
const SFMC_CLIENT_SECRET = '';
const SALESFORCE_INTEGRATION_USER = '';
const SALESFORCE_INTEGRATION_PASSWORD = '';
const SFMC_MAIN_KEYWORD = '';
const SFMC_MAIN_KEYWORD_2_STEP = '';

// Google
const GOOGLE_ANALYTICS_ID = '';
const GOOGLE_CLIENTID = '';
const GOOGLE_APIKEY = '';
const GOOGLE_CAPTCHA_SITE_KEY = '';
const CAPTCHA_SECRET = '';

// microsoft outlook hotmail
const MICROSOFT_CLIENTID = '';
const MICROSOFT_APIKEY = '';

// yahoo
const YAHOO_CLIENTID = '';
const YAHOO_SECRET = '';

// FaceBook
const FACEBOOK_APPID = false;  // Live appid = '', set to false to disable facebook integration
const FACEBOOK_SECRET = '';
const FACEBOOK_NAMESPACE = '';

//Avalara;
const AVALARA_API_ENDPOINT = 'https://rest.avatax.com/api/v2/';
const AVALARA_API_ACCOUNT_ID = '';//sandbox
const AVALARA_API_LICENSE_KEY = '';//sandbox
?>