<?php
require_once("config/Config.server.inc");

if(isset($_SERVER['HTTP_USER_AGENT']))
{
	if (preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]))
	{
		define('IS_TOUCH_SCREEN_DEVICE', true);
	}
	else
	{
		define('IS_TOUCH_SCREEN_DEVICE', false);
	}
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

const SLOT_STEALING_CUTOFF_MENU = 124;

$_config_ini = parse_ini_file(__DIR__ .'/config/config.ini');

define("JAVASCRIPT_CSS_VERSION", $_config_ini['version']);

const FADMIN_TIMEOUT = 15; // set minutes to enable timout for everyone but customers, false to disable

const FPDF_FONTPATH = 'fpdf/font/';

define('HTTP_BASE', HTTP_SERVER . WEB_BASE);
define('HTTPS_BASE', HTTPS_SERVER . WEB_BASE);

define('THEME', 'dreamdinners'); // Template theme
define('DEFAULT_PAGE', 'home'); // Default page= when / is accessed

define('TEMPLATE_PATH', APP_BASE . 'templates/' . THEME);
define('HO_TEMPLATE_PATH', APP_BASE . 'ho_templates');

define('ASSETS_PATH', APP_BASE . 'assets');
define('RELATIVE_IMAGES_PATH', 'theme/' . THEME . '/images');
define('IMAGES_PATH', HIGH_CAP_IMAGE_BASE . RELATIVE_IMAGES_PATH);
define('ADMIN_IMAGES_PATH', HIGH_CAP_IMAGE_BASE . 'theme/' . THEME . '/images/admin');
define('EMAIL_IMAGES_PATH', HTTPS_BASE . 'theme/' . THEME . '/images');
define('CSS_PATH', WEB_BASE . 'theme/' . THEME . '/css');
define('SCRIPT_PATH', WEB_BASE . 'theme/' . THEME . '/scripts');
define('MEDIA_PATH', WEB_BASE . 'media');

const USE_BUG_REPORT_PAGE = true;
const ORDER_EDITING_ENABLED = true;

// PATH_SEPARATOR =  ; for windows, : for *nix
$iReturn = ini_set( 'include_path',
	APP_BASE.PATH_SEPARATOR.
	APP_BASE.'phplib/'.PATH_SEPARATOR.
	APP_BASE.'includes/'.PATH_SEPARATOR.
	APP_BASE.'includes/jpgraph/'.PATH_SEPARATOR.
ini_get('include_path'));

// secure PHPSESSID cookie
ini_set('session.name', ((DD_SERVER_NAME != 'LIVE') ? DD_SERVER_NAME . '_PHPSESSID' : 'PHPSESSID'));
ini_set('session.cookie_domain', COOKIE_DOMAIN);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

if (empty($iReturn))
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