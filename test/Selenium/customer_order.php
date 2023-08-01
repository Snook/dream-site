<?php
namespace Facebook\WebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Exception;
use CLog;

require_once("../../includes/CApp.inc");
require_once('vendor/autoload.php');
include('includes/config.php');
require_once('test_utils.php');
require_once('../test_support_functions.php');


try {
	
$out_path = APP_BASE  . "/test/Selenium/results/" . "cust_order_results.txt";
$dest_fp = fopen($out_path, 'w');


$host = 'http://localhost:4444/wd/hub'; // this is the default
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

DD_Selenium_Test_Utils::Trace("Customer Order Test Initialized: About to begin ...\r\n", $dest_fp);

DD_Selenium_Test_Utils::signOnCustomer($driver);

$driver->get($baseURL . "/main.php?page=start_new_test_order");

DD_Selenium_Test_Utils::fill_out_customer_credit_card_form($driver);

$driver->findElement(WebDriverBy::id('customers_terms'))->click();
$driver->findElement(WebDriverBy::id('complete_order'))->click();

$title = $driver->getTitle();


if (!DD_Selenium_Test_Utils::Assert($title != 'Thank you',  "Should be on Thank you page", $dest_fp))
{
	DD_Selenium_Test_Utils::Trace("fails!\r\n\r\n", $dest_fp);
	fclose($dest_fp);
	exit;
}


DD_Selenium_Test_Utils::Trace("Passes!\r\n\r\n", $dest_fp);


// normal finish
fclose($dest_fp);

} catch(Exception $e) {

	DD_Selenium_Test_Utils::Trace("test pass failed: exception occurred<br>\n");
	DD_Selenium_Test_Utils::Trace("reason: " . $e->getMessage(), $dest_fp);
	CLog::RecordException($e);
	fclose($dest_fp);



}
