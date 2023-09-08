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

$driver->get($baseURL . "/?page=admin_start_new_test_order&session_id=" . $session->id . "&user_id=400252");
sleep(1);
DD_Selenium_Test_Utils::selectIntroItems($driver);

$driver->executeScript("window.scrollTo(0,0)");

$driver->findElement(WebDriverBy::id('payments_tab_li'))->click();
//$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('payment1_type')));
//$select->selectByValue('CC');

DD_Selenium_Test_Utils::fill_out_fadmin_credit_card_form($driver);

$clientFacts = DD_Selenium_Test_Utils::getOrderFactsFromClient($driver);


$driver->findElement(WebDriverBy::id('addPaymentAndActivateButton'))->click();

$driver->executeScript("window.scrollTo(0,0)");

$driver->executeScript("click_dd_message_button('dd_message', 'Confirm')");

sleep(3); // give a little time to ensure the database is up to date
$title = $driver->getTitle();

if (!DD_Selenium_Test_Utils::Assert($title != 'Order Complete',  "Should be on Thank you page", $dest_fp))
{
	DD_Selenium_Test_Utils::Trace("fails!\r\n\r\n", $dest_fp);
	fclose($dest_fp);
	exit;
}


$landedAt = $driver->getCurrentURL();
$params = explode("?", $landedAt)[1];
$params = explode("&", $params);
$order_id = false;

foreach($params as $thisParam)
{
	if (strpos($thisParam, "order=") === 0)
	{
		$order_id = explode("=", $thisParam)[1];
		break;
	}
}

if (empty($order_id))
{
	CLog::Record("This should not happen");
}

DD_Test_Support::compareOrderFacts($order_id, $clientFacts, $dest_fp);

DD_Selenium_Test_Utils::Trace("Passes!\r\n\r\n", $dest_fp);


// normal finish
fclose($dest_fp);

} catch(Exception $e) {

	DD_Selenium_Test_Utils::Trace("test pass failed: exception occurred<br>\n");
	DD_Selenium_Test_Utils::Trace("reason: " . $e->getMessage(), $dest_fp);
	CLog::RecordException($e);
	fclose($dest_fp);



}