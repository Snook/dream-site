<?php

namespace Facebook\WebDriver;

require_once("../../includes/CApp.inc");


class DD_Selenium_Test_Session_Utils
{
	// Note: current store must match session store
	static function createFNOSession($driver, $dest_fp, $date, $menu, $host_email, $recursion_depth = 1)
	{
		include('includes/config.php');

		if ($recursion_depth > 12)
		{
			return false;
		}

		$driver->get($baseURL . "/?page=admin_create_session&selectedCell=" . $date . "&menu=" . $menu);

		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('time_hour')));
		$select->selectByValue($recursion_depth);

		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('session_type')));
		$select->selectByValue('DREAM_TASTE');

		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('dream_taste_theme')));
		$select->selectByValue('DTTHEME_117');
		// TODO: don't hard  code


		$driver->findElement(WebDriverBy::id('session_password'))->sendKeys("test");
		$driver->findElement(WebDriverBy::id('session_host'))->sendKeys($host_email);
		$driver->findElement(WebDriverBy::id('createSession'))->click();


		$result = $driver->executeScript("return last_created_session_id;");

		if (!$result)
		{
			$result = $driver->executeScript("return $('#dd_ErrorMessage').html();");

			if (strpos($result, "session time and duration conflict") !== false)
			{
				return DD_Selenium_Test_Session_Utils::createFNOSession($driver, $dest_fp, $date, $menu, $host_email, ++$recursion_depth);
			}
		}


		return $result;
	}










}