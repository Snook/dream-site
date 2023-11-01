<?php
require_once("../includes/Config.inc");
require_once("DAO/BusinessObject/COrdersDelivered.php");
require_once 'includes/api/tax/avalara/AvalaraTaxManager.php';
require_once 'includes/api/tax/avalara/AvalaraTaxWrapper.php';



$order = new COrdersDelivered();
$order->id = 3860749;
$order->find(true);




$result = AvalaraTaxManager::getInstance()->getTaxRates(new AvalaraTaxWrapper($order));

if ($result == false)
{
	echo print_r(AvalaraTaxManager::getInstance()->getLastError(), true);
}
else
{
	echo print_r($result->getTaxes(),true);

	echo $result->getFoodTax();
}

//echo AvalaraTaxManager::getInstance()->pingService();



//$address = new stdClass();
//$address->line1 = "1109 NE 181st PL";
//$address->city = "Shoreline";
//$address->region = "WA";
//$address->postalCode = "98155";
//$address->country = "US";
//AvalaraTaxManager::getInstance()->resolveAddress($address);
