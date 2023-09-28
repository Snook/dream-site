<?php

namespace Facebook\WebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;


require_once("../../includes/CApp.inc");
require_once('vendor/autoload.php');
require_once('test_utils.php');
require_once('../test_support_functions.php');
require_once('./test_session_utils.php');

include('includes/config.php');

restore_error_handler();

DD_Test_Support::setupTestingTables();

$out_path = APP_BASE  . "/test/Selenium/results/" . "fno_sequence_results.txt";
$dest_fp = fopen($out_path, 'w');


// This would be the url of the host running the server-standalone.jar
$host = 'http://localhost:4444/wd/hub'; // this is the default
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

DD_Selenium_Test_Utils::Trace("Customer Order Test Initialized: About to begin ...\r\n", $dest_fp);

DD_Selenium_Test_Utils::signOnSiteAdmin($driver);

$FNO_Session_ID = DD_Selenium_Test_Session_Utils::createFNOSession($driver, $dest_fp, '12/17/2017', 196, 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
$driver->get($baseURL . "/backoffice?session=" . $FNO_Session_ID);
sleep(2);
for ($x = 0; $x < 4; $x++)
{
	DD_Selenium_Test_Utils::Trace("Attempting number $x ...\r\n", $dest_fp);

	$newUserVals = DD_Test_Support::getRandomAccountDetails(true);
	$elementPosition = $driver->findElement(WebDriverBy::id('add_rsvp_button-' . $FNO_Session_ID))->getLocation()->getY();
	$driver->executeScript("window.scrollTo(0,$elementPosition)");
	$driver->findElement(WebDriverBy::id('add_rsvp_button-' . $FNO_Session_ID))->click();
	$driver->executeScript("window.scrollTo(0,0)");
	sleep(3);
	$driver->executeScript("$('#add_guest_password_login').attr('type', 'text');");

	$driver->findElement(WebDriverBy::id('add_guest_firstname'))->sendKeys($newUserVals['firstname']);
	$driver->findElement(WebDriverBy::id('add_guest_lastname'))->sendKeys($newUserVals['lastname']);
	$driver->findElement(WebDriverBy::id('add_guest_primary_email_login'))->sendKeys($newUserVals['primary_email']);
	$driver->findElement(WebDriverBy::id('add_guest_password_login'))->sendKeys($newUserVals['password']);

	$result = $driver->executeScript("click_dd_message_button('add_guest_div', 'Accept');");
	sleep(3);

}

/*

DD_Selenium_Test_Utils::Trace("Attempting 1 more which should also succeed but with an 'overbooked' message.\r\n", $dest_fp);

$newUserVals = DD_Test_Support::getRandomAccountDetails(true);
$elementPosition = $driver->findElement(WebDriverBy::id('add_rsvp_button-' . $FNO_Session_ID))->getLocation()->getY();
$driver->executeScript("window.scrollTo(0,$elementPosition)");
$driver->findElement(WebDriverBy::id('add_rsvp_button-' . $FNO_Session_ID))->click();
$driver->executeScript("window.scrollTo(0,0)");
sleep(1);
$driver->executeScript("$('#add_guest_password_login').attr('type', 'text');");


$driver->findElement(WebDriverBy::id('add_guest_firstname'))->sendKeys($newUserVals['firstname']);
$driver->findElement(WebDriverBy::id('add_guest_lastname'))->sendKeys($newUserVals['lastname']);
$driver->findElement(WebDriverBy::id('add_guest_primary_email_login'))->sendKeys($newUserVals['primary_email']);
$driver->findElement(WebDriverBy::id('add_guest_password_login'))->sendKeys($newUserVals['password']);

$result = $driver->executeScript("click_dd_message_button('add_guest_div', 'Accept');");


*/
DD_Selenium_Test_Utils::Trace("Success ...\r\n", $dest_fp);