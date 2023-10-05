<?php

namespace Facebook\WebDriver;

require_once("../../includes/CApp.inc");



class DD_Selenium_Test_Order_Utils
{
	// Note: current store must match session store
	static function placeStandardFadminOrder($driver, $session_id, $user_id)
	{

		include('includes/config.php');

		$driver->get($baseURL . "/backoffice/start_new_test_order?session_id=" . $session_id . "&user_id=" . $user_id);
		DD_Selenium_Test_Utils::selectItems($driver, 36, 48, false);
		$driver->executeScript("window.scrollTo(0,0)");
		$driver->findElement(WebDriverBy::id('payments_tab_li'))->click();
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
	}

	static function loadGiftCard($driver)
	{

		include('includes/config.php');

		$details = DD_Test_Support::getRandomAccountDetails();

		$driver->get($baseURL . "/backoffice/gift-card-load");

		$driver->findElement(WebDriverBy::id("gift_card_number"))->sendKeys("71000210014526");
		$driver->findElement(WebDriverBy::id("amount"))->sendKeys("25");
		$driver->findElement(WebDriverBy::id("primary_email"))->sendKeys($details["primary_email"]);
		$driver->findElement(WebDriverBy::id("confirm_email_address"))->sendKeys($details["primary_email"]);
		$driver->findElement(WebDriverBy::id("billing_name"))->sendKeys($details["firstname"] . " " . $details["lastname"]);
		$driver->findElement(WebDriverBy::id("billing_address"))->sendKeys($details["address_line1"] );
		$driver->findElement(WebDriverBy::id("billing_zip"))->sendKeys("11111");
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('credit_card_type')));
		$select->selectByValue('Visa');
		$driver->findElement(WebDriverBy::id("credit_card_number"))->sendKeys("4111111111111111");

		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('credit_card_exp_month')));
		$select->selectByValue('08');

		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('credit_card_exp_year')));
		$select->selectByValue('19');

		$driver->findElement(WebDriverBy::id("credit_card_cvv"))->sendKeys("111");
		$driver->findElement(WebDriverBy::id("procCardLoadBtn"))->click();



	}








}