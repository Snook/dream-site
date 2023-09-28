<?php
namespace Facebook\WebDriver;

require_once("../../includes/CApp.inc");

use Exception;

class DD_Selenium_Test_Utils
{

	public static $throwOnAssertFailure = false;

	static function signOnSiteAdmin($driver)
	{
		include('includes/config.php');

		$driver->get($baseURL . "/signout");
		$driver->get($baseURL . "/backoffice/login");

		$driver->executeScript("$('#password_login').attr('type', 'text');");

		$driver->findElement(WebDriverBy::id("primary_email_login"))->sendKeys($loginUsername);
		$driver->findElement(WebDriverBy::id("password_login"))->sendKeys($loginPassword);
		$driver->findElement(WebDriverBy::id('submit_login'))->click();

	}

	static function signOnCustomer($driver, $loginWithFacebook = false)
	{
		include('includes/config.php');

		$driver->get($baseURL . "/signout");
		$driver->get($baseURL . "/login");

		if ($loginWithFacebook)
		{
			$driver->findElement(WebDriverBy::id('Log in with Facebook[1]'))->click();
		}
		else
		{
			$driver->findElement(WebDriverBy::id("primary_email_login"))->sendKeys($loginUsername);
			$driver->findElement(WebDriverBy::id("password_login"))->sendKeys($loginPassword);
			$driver->findElement(WebDriverBy::id('submit_login'))->click();
		}
	}


	static function fill_out_customer_credit_card_form($driver)
	{

		include('includes/config.php');

		//_setValue(_textbox("ccNameOnCard"), "Wile E Tester");
		$driver->findElement(WebDriverBy::id("ccNameOnCard"))->sendKeys("Wile E Tester");

		//_setSelected(_select("ccType"), $creditCardType);
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('ccType')));
		$select->selectByValue($creditCardType);

		//_setValue(_textbox("ccNumber"), $creditCardNumber);
		$driver->findElement(WebDriverBy::id("ccNumber"))->sendKeys($creditCardNumber);

		//_setSelected(_select("ccMonth"), $creditCardMonth);
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('ccMonth')));
		$select->selectByValue($creditCardMonth);

		//_setSelected(_select("ccYear"), $creditCardYear);
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('ccYear')));
		$select->selectByValue($creditCardYear);

		//_setValue(_textbox("ccSecurityCode"), $creditCardCVV);
		$driver->findElement(WebDriverBy::id("ccSecurityCode"))->sendKeys($creditCardCVV);

	}

	static function fill_out_fadmin_credit_card_form($driver)
	{
		include('includes/config.php');

		//_setSelected(_select("payment1_type"), "Credit Card (new)");
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('payment1_type')));
		$select->selectByValue("CC");

		//_setSelected(_select("payment1_ccType"), $creditCardType);
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('payment1_ccType')));
		$select->selectByValue($creditCardType);


		//_setValue(_textbox("payment1_ccNumber"), $creditCardNumber);
		$driver->findElement(WebDriverBy::id("payment1_ccNumber"))->sendKeys($creditCardNumber);

		//_setSelected(_select("payment1_ccMonth"), $creditCardMonth);
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('payment1_ccMonth')));
		$select->selectByValue($creditCardMonth);

		//_setSelected(_select("payment1_ccYear"), $creditCardYear);
		$select = new WebDriverSelect($driver->findElement(WebDriverBy::id('payment1_ccYear')));
		$select->selectByValue($creditCardYear);

		//_setValue(_textbox("payment1_cc_security_code"), $creditCardCVV);
		$driver->findElement(WebDriverBy::id("payment1_cc_security_code"))->sendKeys($creditCardCVV);


	}

	static function getOrderFactsFromClient($driver)
	{

		$retVal = array();

		$retVal['item_count'] = $driver->findElement(WebDriverBy::id("OEH_item_count"))->getText();
		$retVal['number_servings'] = $driver->findElement(WebDriverBy::id("OEH_number_servings"))->getText();
		$retVal['menu_subtotal'] = $driver->findElement(WebDriverBy::id("OEH_menu_subtotal"))->getText();
		$retVal['food_cost_subtotal'] = $driver->findElement(WebDriverBy::id("OEH_food_cost_subtotal"))->getText();
		$retVal['products_subtotal'] = $driver->findElement(WebDriverBy::id("OEH_products_subtotal"))->getText();
		$retVal['preferred_discount'] = $driver->findElement(WebDriverBy::id("OEH_preferred"))->getText();
		$retVal['direct_discount'] = $driver->findElement(WebDriverBy::id("OEH_direct_order_discount"))->getText();
		$retVal['plate_points_order_discount_food'] = $driver->findElement(WebDriverBy::id("OEH_plate_points_order_discount_food"))->getText();
		$retVal['plate_points_order_discount_fee'] = $driver->findElement(WebDriverBy::id("OEH_plate_points_order_discount_fee"))->getText();
		$retVal['coupon_discount'] = $driver->findElement(WebDriverBy::id("OEH_coupon_discount"))->getText();
		$retVal['session_discount'] = $driver->findElement(WebDriverBy::id("OEH_session_discount"))->getText();
		$retVal['service_fee'] = $driver->findElement(WebDriverBy::id("OEH_subtotal_service_fee"))->getText();
		$retVal['food_tax'] = $driver->findElement(WebDriverBy::id("OEH_food_tax_subtotal"))->getText();
		$retVal['service_tax'] = $driver->findElement(WebDriverBy::id("OEH_service_tax_subtotal"))->getText();
		$retVal['ltd_round_up'] = $driver->findElement(WebDriverBy::id("OEH_ltd_round_up"))->getText();
		$retVal['grand_total'] = $driver->findElement(WebDriverBy::id("OEH_grandtotal"))->getText();
		$retVal['payment_total'] = $driver->findElement(WebDriverBy::id("OEH_paymentsTotal"))->getText();

		//OEH_misc_food_subtotal
		//OEH_misc_nonfood_subtotal

		return $retVal;

	}

	static function selectItems($driver, $minimum_servings = 36, $maximum_servings = 36, $pickLTD = 'sometimes', $addSides = false)
	{
		$finalServings = 0;
		$testServings = $minimum_servings % 3;
		if ($testServings == 1)
		{
			$minimum_servings--;
		}
		else if ($testServings == 2)
		{
			$minimum_servings++;
		}

		$testServings = $maximum_servings % 3;
		if ($testServings == 1)
		{
			$maximum_servings--;
		}
		else if ($testServings == 2)
		{
			$maximum_servings++;
		}

		$servingsRange = $maximum_servings - $minimum_servings;
		if ($servingsRange == 0)
		{
			$finalServings = $minimum_servings;
		}
		else
		{
			$testRange = $servingsRange / 3;
			$testRange = rand(0, $testRange);
			$testRange *= 3;
			$finalServings = $maximum_servings + $testRange;
		}

		$masterArray = array();

		$elements = $driver->findElements(WebDriverBy::cssSelector("[id^=inv_]"));

		foreach($elements as $thisElem)
		{
			$id = $thisElem->getAttribute('id');
			$id_comps = explode("_", $id);
			if (count($id_comps) == 2)
			{
				$id = $id_comps[1];
				$inventory = $thisElem->getText();
				$masterArray[$id] = array('id' => $id, 'inv' => $inventory);
			}
		}

		shuffle($masterArray);

		$numServingsAdded = 0;

		foreach ($masterArray as $thisItem)
		{

			$inventory = $thisItem['inv'];

			$elements = $driver->findElements(WebDriverBy::cssSelector("[data-entreeid='" . $thisItem['id'] . "'][data-is_bundle='false']"));

			if (count($elements) > 1)
			{
				$priceTypeSelector = rand(0, 100);
			}
			else
			{
				$priceTypeSelector = 20;
				// always pick full if only full exists
			}

			if ($priceTypeSelector < 55 && $priceTypeSelector > 43 && $inventory < 10)
			{
				$priceTypeSelector = 80;
				// not enough inventory for both so set to 3 serving
			}

			foreach($elements as $thisElem)
			{
				if (!$thisElem->isDisplayed())
				{
					continue;
				}

				if ($priceTypeSelector > 54)
				{
					// get 3 serving version if exists
					if ($thisElem->getAttribute('data-pricing_type') == 'HALF' && $inventory > 3)
					{
						$thisElem->sendKeys("1");
						$numServingsAdded += 3; // might need to get this as attribute of item if we go back to other than 3 and 6 serving meals
						break;
					}
				}
				else if ($priceTypeSelector < 44)
				{
					// get 6 serving
					if ($thisElem->getAttribute('data-pricing_type') == 'FULL' && $inventory > 6)
					{
						$thisElem->sendKeys("1");
						$numServingsAdded += 6;
						break;
					}
				}
				else
				{
					// get both
					$thisElem->sendKeys("1");
					if ($thisElem->getAttribute('data-pricing_type') == 'HALF')
					{
						$numServingsAdded += 3;
					}
					else
					{
						$numServingsAdded += 6;
					}
				}
			}

			if ($numServingsAdded >= $finalServings)
			{
				break;
			}

		}

	}

	static function selectTasteItems($driver, $pickLTD = 'sometimes', $addSides = false)
	{
		$masterInvArray = array();

		$elements = $driver->findElements(WebDriverBy::cssSelector("[id^=inv_]"));

		foreach($elements as $thisElem)
		{
			$id = $thisElem->getAttribute('id');
			$id_comps = explode("_", $id);
			if (count($id_comps) == 2)
			{
				$id = $id_comps[1];
				$inventory = $thisElem->getText();
				$masterInvArray[$id] = array('id' => $id, 'inv' => $inventory);
			}
		}

	//	shuffle($masterArray);

		$elements = $driver->findElements(WebDriverBy::cssSelector("[id^=bnd_]"));

		shuffle($elements);

		$numServingsAdded = 0;

		foreach ($elements as $thisItem)
		{
			$item_id = $thisItem->getAttribute('data-entreeid');
			$inventory = $masterInvArray[$item_id]['inv'];

			$qty = 1;
			$qtySelector = rand(0, 100);

			if ($qtySelector > 80 && $numServingsAdded < 4 && $inventory > 5)
			{
				$numServingsAdded += 6;
				$qty = 2;
			}
			else if ($inventory > 2)
			{
				$numServingsAdded += 3;
			}
			else
			{
				continue;
			}

			$thisItem->sendKeys($qty);

			if ($numServingsAdded >= 9)
			{
				break;
			}
		}
	}

	static function selectIntroItems($driver, $pickLTD = 'sometimes', $addSides = false)
	{

		$element = $driver->findElement(WebDriverBy::cssSelector("#selectedBundle"));
		$element->click();

		$masterInvArray = array();

		$elements = $driver->findElements(WebDriverBy::cssSelector("[id^=inv_]"));

		foreach($elements as $thisElem)
		{
			$id = $thisElem->getAttribute('id');
			$id_comps = explode("_", $id);
			if (count($id_comps) == 2)
			{
				$id = $id_comps[1];
				$inventory = $thisElem->getText();
				$masterInvArray[$id] = array('id' => $id, 'inv' => $inventory);
			}
		}

		//	shuffle($masterArray);

		$elements = $driver->findElements(WebDriverBy::cssSelector("[id^=bnd_]:not([id^=bnd_div])"));

		shuffle($elements);

		$numServingsAdded = 0;

		foreach ($elements as $thisItem)
		{
			$entree_id = $thisItem->getAttribute('data-entreeid');
			$pricing_type = $thisItem->getAttribute('data-pricing_type');

			$inventory = $masterInvArray[$entree_id]['inv'];

			if ($pricing_type == 'FULL' &&  $inventory > 5 && $numServingsAdded < 13)
			{
				$numServingsAdded += 6;
			}
			else if ($pricing_type == 'HALF'  && $inventory > 2)
			{
				$numServingsAdded += 3;
			}
			else
			{
				continue;
			}

			$driver->executeScript("window.scrollTo(0,0)");
			$elementPosition = $thisItem->getLocation()->getY();
			$driver->executeScript("window.scrollTo(0,$elementPosition)");
			$thisItem->click();
			sleep(1);
			if ($numServingsAdded >= 18)
			{
				break;
			}
		}
		$driver->executeScript("window.scrollTo(0,0)");

	}


	static function Assert($bool_expression, $message, $fp = null)
	{
		if ($bool_expression)
		{
			return true;
		}

 		self::trace("************* Failure **************** : " . $message . "\r\n\r\n", $fp);

 		if (self::$throwOnAssertFailure)
 		{
 			throw new Exception("Test Failure on Assert:  " . $message);
 		}



		return false;
	}


	static function trace($message, $fp = null)
	{
		echo $message;

		if ($fp)
		{
			$length = fputs($fp, $message);
		}
	}


}