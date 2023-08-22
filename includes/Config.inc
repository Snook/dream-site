<?php
// Send users to maintenance page
//define('SITE_DISABLED', true);

// Optional: Message to appear only on maintenance page
//define('MAINT_PAGE_MESSAGE', 'Our website is currently down for scheduled maintenance, site expected to be up at 9:00 PM PDT.');

// List of IP's who won't get redirect to maintenance page
//$g_IP_ExclusionList = array('127.0.0.1','50.34.44.26');


// detect mobile
global $is_touch_screen_device;
$is_touch_screen_device = false;

if(isset($_SERVER['HTTP_USER_AGENT']))
{
	$useragent = $_SERVER['HTTP_USER_AGENT'];

	if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
	{
		$is_touch_screen_device = true;
	}
}



if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] == "161.58.88.173")
{
	include ("config/Config.prod1.inc");
	define('SERVER_ENV', 'LIVE');
}
else
{
	$machineName = php_uname("n");
	//echo "|" . $machineName . "|";
	switch ($machineName)
	{
		case 'DREA02VMW01':
			include ("config/Config.DREA02VMW01.inc");
			define('SERVER_ENV', 'LIVE');
			break;
		case 'chors.dreamdinners.com':
			include ("config/Config.chors.inc");
			define('SERVER_ENV', 'LIVE');
			break;
		case 'Prod7VMDev1.dreamdinners.local':
			include ("config/Config.prod1.inc");
			define('SERVER_ENV', 'LIVE');
			break;
		case 'Carls-MVP':
		case 'Carls-MVP.local':
		case 'PF1VHAWY-CS10':
			include ("config/Config.carl.inc");
			define('SERVER_ENV', 'DEV');
		break;
		case 'PF115FU6-RS10':
		case 'PF1VHAWY-RS10':
			include ("config/Config.ryans.inc");
			define('SERVER_ENV', 'DEV');
		break;
		case 'PF2LTCZ6-EL10':
			include ("config/Config.evan.inc");
			define('SERVER_ENV', 'DEV');
			break;
		case 'C-DB1-Reports-2':
			include ("config/Config.C-DB1-Reports-2.inc");
			efine('SERVER_ENV', 'TEST');
			break;
		case 'sandbox.dreamdinners.com':
			include ("config/Config.sandbox.inc");
			define('SERVER_ENV', 'TEST');
			break;
		case 'branch.dreamdinners.com':
			include ("config/Config.prod1.inc");
			define('SERVER_ENV', 'TEST');
			break;
		default:
			// DEFAULT Uses the prod settings
			include ("config/Config.DREA02VMW01.inc");
			define('SERVER_ENV', 'LIVE');
	}
}


// if host not listed, dreamdinners.com is used
$domainToTheme = array(
	'dreamdinners.com' => array(
		'theme' => 'dreamdinners',
		'default_page' => 'home',
	)
);

// setup domain parts
global $httpHostInfo;
$httpHostInfo = array();
$domainConfig = $domainToTheme['dreamdinners.com'];

if (!empty($_SERVER['HTTP_HOST']))
{
	// check for m., they are requesting mobile site
	$httpHostInfo['host'] = preg_replace('/^m\./', '', strtolower($_SERVER['HTTP_HOST']), -1, $httpHostInfo['request_mobile']);

	// replace .test with .com for local development for $domainToTheme matching
	$httpHostInfo['host'] = preg_replace('/\.test$/', '.com', $httpHostInfo['host']);

	// replace test site domains with dreamdinners.com for $domainToTheme matching
	$httpHostInfo['host'] = preg_replace('/(prod6vm|training)\.dreamvm\.com$/', 'dreamdinners.com', $httpHostInfo['host']);

	// reverse host name, work from top level to subdomains
	$http_host = array_reverse(explode('.', $httpHostInfo['host']));

	$httpHostInfo['top_level'] = $http_host[0];

	$httpHostInfo['domain'] = '';
	if (!empty($http_host[1]))
	{

		$httpHostInfo['domain'] = $http_host[1];
	}

	$httpHostInfo['subdomain'] = '';
	if (!empty($http_host[2]))
	{
		$httpHostInfo['subdomain'] = $http_host[2];
	}

	$httpHostInfo['sub_subdomain'] = '';
	if (!empty($http_host[3]))
	{
		$httpHostInfo['sub_subdomain'] = $http_host[3];
	}

	$httpHostInfo['sub_sub_subdomain'] = '';
	if (!empty($http_host[4]))
	{
		$httpHostInfo['sub_sub_subdomain'] = $http_host[4];
	}

	if (!empty($domainToTheme[$httpHostInfo['host']]) || (defined('SITE_DISABLED') && SITE_DISABLED != true) )
	{
		$domainConfig = $domainToTheme[$httpHostInfo['host']];
	}
}

if ($is_touch_screen_device)
{
	define('IS_TOUCH_SCREEN_DEVICE', true);
}
else
{
	define('IS_TOUCH_SCREEN_DEVICE', false);
}


if (!defined('COOKIE_DOMAIN'))
{
	define('COOKIE_DOMAIN', '.dreamdinners.com');
}

// RARELY MODIFIED
define('TIMENOW', time());
define('TIMESTAMPNOW', date('Y-m-d H:i:s', TIMENOW));

define('SLOT_STEALING_CUTOFF_MENU', 124);

define('JAVASCRIPT_CSS_VERSION', 670);

define('FADMIN_TIMEOUT', 15); // set minutes to enable timout for everyone but customers, false to disable

define('FPDF_FONTPATH', 'fpdf/font/');

define('HTTP_BASE', HTTP_SERVER . WEB_BASE);
define('HTTPS_BASE', HTTPS_SERVER . WEB_BASE);

define('MAIN_SCRIPT', 'main.php');

define('THEME', $domainConfig['theme']);

define('TEMPLATE_PATH', APP_BASE . 'templates/' . THEME);
define('HO_TEMPLATE_PATH', APP_BASE . 'ho_templates');

define('ASSETS_PATH', APP_BASE . 'assets');
define('RELATIVE_IMAGES_PATH', 'theme/' . THEME . '/images');
define('IMAGES_PATH', HIGH_CAP_IMAGE_BASE . RELATIVE_IMAGES_PATH);
define('ADMIN_IMAGES_PATH', HIGH_CAP_IMAGE_BASE . 'theme/' . THEME . '/images/admin');
define('EMAIL_IMAGES_PATH', HTTPS_BASE . 'theme/' . THEME . '/images');
define('CSS_PATH', WEB_BASE . 'theme/' . THEME . '/css/' . JAVASCRIPT_CSS_VERSION);
define('SCRIPT_PATH', WEB_BASE . 'theme/' . THEME . '/scripts/' . JAVASCRIPT_CSS_VERSION);
define('MEDIA_PATH', WEB_BASE . 'media');

define('USE_BUG_REPORT_PAGE', true);
define('ORDER_EDITING_ENABLED', true);

// PATH_SEPARATOR =  ; for windows, : for *nix
$iReturn = ini_set( 'include_path',
	APP_BASE.PATH_SEPARATOR.
	APP_BASE.'phplib/'.PATH_SEPARATOR.
	APP_BASE.'includes/'.PATH_SEPARATOR.
	APP_BASE.'includes/jpgraph/'.PATH_SEPARATOR.
ini_get('include_path'));

// secure PHPSESSID cookie
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

if (! $iReturn || empty($iReturn))
{
	die ("No include_path var in php.ini");
}

//DB_DataObject config
require_once('PEAR.php');

$options = &PEAR::getStaticProperty('DB_DataObject','options');
$options = array(
		'database'		=> 'mysqli://'.DB_SERVER_USERNAME.':'.DB_SERVER_PASSWORD.'@'.DB_SERVER.'/'.DB_DATABASE,
		'schema_location' => APP_BASE.'includes/DAO',
		'class_location' => APP_BASE.'includes/DAO',
		'class_prefix'	=> 'DAO_',
		'extends' => 'DAO',
		'extends_location' => 'DAO.inc',
		'debug' => 0
	);


define('ADMIN_EMAIL', 'support@dreamdinners.com');
define('IT_EMAIL', 'itsupport@dreamdinners.com');
define('ADMINISTRATOR_EMAIL', 'administrator@dreamdinners.com');
?>