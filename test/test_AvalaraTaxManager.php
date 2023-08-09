<?php
require_once("../includes/Config.inc");
require_once("DAO/BusinessObject/COrdersDelivered.php");
require_once 'includes/api/tax/avalara/AvalaraTaxManager.php';
require_once 'includes/api/tax/avalara/AvalaraTaxWrapper.php';




echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxZDLZsbHs9'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxZDXZshps9'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxZDhZsVpDN'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxZDtZsVxDA'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxZDrAsVHsu'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDsPADmpsu'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDsgBDmpDw'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDsJAshtsZ'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDsOAshLDA'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDDqBDmVsu'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDDLAsbxDb'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDDXZsVHDA'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDDhAshND7'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDDtAshDDN'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxDDrZshpDN'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxHsPZsbpsu'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxHsgAsVDDw'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxHsJZsVDDb'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxHsOZDmZDN'));
echo PHP_EOL;
echo base64_decode(CCrypto::decode('ADZRDQBDaxsfZD7LDpBDxHDqBDmDDw'));

/*

$order = new COrdersDelivered();
$order->id = 3630184;
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
*/