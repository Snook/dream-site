<?php
namespace Facebook\WebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use Exception;
use CLog;

require_once("../../includes/CApp.inc");
require_once('vendor/autoload.php');
include('includes/config.php');
require_once('test_utils.php');
require_once('test_order_utils.php');

require_once('../test_support_functions.php');


try {
	
$session = DD_Test_Support::getSessionWithProperties(291, 'STANDARD', "current", true, true);

$out_path = APP_BASE  . "/test/Selenium/results/" . "fadmin_taste_order_results.txt";
$dest_fp = fopen($out_path, 'w');


// This would be the url of the host running the server-standalone.jar
$host = 'http://localhost:4444/wd/hub'; // this is the default
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

DD_Selenium_Test_Utils::Trace("Fadmin Order Test Initialized: About to begin ...\r\n", $dest_fp);

DD_Selenium_Test_Utils::signOnSiteAdmin($driver);

DD_Test_Support::setupTestingTables();


DD_Selenium_Test_Order_Utils::loadGiftCard($driver);



DD_Selenium_Test_Utils::Trace("Passes!\r\n\r\n", $dest_fp);


// normal finish
fclose($dest_fp);

} catch(Exception $e) {

	DD_Selenium_Test_Utils::Trace("test pass failed: exception occurred<br>\n");
	DD_Selenium_Test_Utils::Trace("reason: " . $e->getMessage(), $dest_fp);
	CLog::RecordException($e);
	fclose($dest_fp);



}
