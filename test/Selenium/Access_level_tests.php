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
require_once('phplib/PHPExcel/PHPExcel.php');
include('includes/config.php');
require_once('test_utils.php');

define('GUEST', 0);
define('CUSTOMER', 1);
define('DISHWASHER', 2);
define('NEW_EMPLOYEE', 2);
define('OPS_SUPPORT', 3);
define('GUEST_SERVER', 4);
define('EVENT_COORDINATOR', 5);
define('SALES_LEAD', 6);
define('OPS_LEAD', 7);
define('FRANCHISE_MANAGER', 8);
define('FRANCHISE_OWNER', 9);
define('HOME_OFFICE_STAFF', 10);
define('HOME_OFFICE_MANAGER', 11);
define('SITE_ADMIN', 12);
define('OFFSET', 4);


$accessColumnOffset = OFFSET + NEW_EMPLOYEE;

try {

$out_path = APP_BASE  . "/test/Selenium/results/" . "results.txt";
$dest_fp = fopen($out_path, 'w');


// This would be the url of the host running the server-standalone.jar
$host = 'http://localhost:4444/wd/hub'; // this is the default
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

$filePath = APP_BASE . "/test/Selenium/page_catalogue-admin.xlsx";

$inputFileName = $filePath;
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($filePath);
$objWorksheet = $objPHPExcel->getActiveSheet();

$highestRow = $objWorksheet->getHighestRow();
$highestColumn = $objWorksheet->getHighestColumn();
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);


DD_Selenium_Test_Utils::signOnSiteAdmin($driver);

DD_Selenium_Test_Utils::Trace("Test Initialized: About to begin ...\r\n", $dest_fp);


$count = 0;

$rows = array();
for ($row = 4; $row <= $highestRow; ++$row)
{
	$thispageName = $objWorksheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();
	$thispageName = explode(".", $thispageName)[0];

	$theParms = $objWorksheet->getCellByColumnAndRow(20, $row)->getCalculatedValue();
	if (empty($theParms)) $theParms = false;

	if ($theParms == "skip")
	{
		continue;
	}


	$directives = $objWorksheet->getCellByColumnAndRow(22, $row)->getCalculatedValue();
	if (empty($directives)) $directives = false;

	$shouldHaveAccess = false;
	if ($objWorksheet->getCellByColumnAndRow($accessColumnOffset, $row)->getCalculatedValue() == "x")
	{
		$shouldHaveAccess = true;
	}

	DD_Selenium_Test_Utils::Trace("Testing $thispageName \r\n", $dest_fp);

	$url = $baseURL . "/backoffice/" . $thispageName . $theParms;

	if ($directives == "dismiss_print")
	{
		$url .= "&no_dd_print=true";
	}

	$driver->get($url);

	if ($thispageName == "main")
	{
		sleep(8);
	}

 	$title = $driver->getTitle();

	if ($shouldHaveAccess)
	{
		if (!DD_Selenium_Test_Utils::Assert($title != 'Login to your Dream Dinners account - Dream Dinners',  "Should Have Access", $dest_fp))
		{
			continue;
		}

		if (!DD_Selenium_Test_Utils::Assert($title != 'oops',  "Should Have Access", $dest_fp))
		{
			continue;
		}

		if (!DD_Selenium_Test_Utils::Assert($title != 'Admin Login - Dream Dinners',  "Should Have Access", $dest_fp))
		{
			continue;
		}

		if ($thispageName != "access_error")
		{
			if (!DD_Selenium_Test_Utils::Assert($title != 'Access Denied - Dream Dinners',  "Should Have Access", $dest_fp))
			{
				continue;
			}
		}

		if ($thispageName != "safe_landing")
		{
			if (!DD_Selenium_Test_Utils::Assert($title != 'Waiting Room - Dream Dinners',  "Should Have Access", $dest_fp))
			{
				continue;
			}
		}
	}
	else
	{
		if (!DD_Selenium_Test_Utils::Assert($title == 'Login to your Dream Dinners account - Dream Dinners' || $title == 'oops' ||
			$title == 'Waiting Room - Dream Dinners' || $title == 'Access Denied - Dream Dinners',  "Should Not Have Access", $dest_fp))
		{
			continue;
		}
	}

	DD_Selenium_Test_Utils::Trace("Passes!\r\n\r\n", $dest_fp);


}


// normal finish
fclose($dest_fp);

} catch(Exception $e) {

	DD_Selenium_Test_Utils::Trace("test pass failed: exception occurred<br>\n");
	DD_Selenium_Test_Utils::Trace("reason: " . $e->getMessage(), $dest_fp);
	CLog::RecordException($e);
	fclose($dest_fp);



}