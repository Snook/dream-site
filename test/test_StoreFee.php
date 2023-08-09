<?php
require_once("../includes/Config.inc");
require_once 'includes/DAO/BusinessObject/CUser.php';
require_once 'includes/DAO/BusinessObject/CStoreFee.php';
require_once 'includes/DAO/BusinessObject/CStore.php';
require_once 'includes/OrdersCustomization.php';

//$fee = CStoreFee::fetchCustomizationFees(244);


//$store = new CStore();
//$store->id = 244;
//$store->find(true);
//
//for ($x = 0; $x <= 25; $x++) {
//	echo $x . ' = ' .$store->customizationFeeForMealCount($x).PHP_EOL;
//}


$order = new COrders();
$order->id = 3787686;
$order->find(true);
$inst = OrdersCustomization::getInstance($order);
$meal = $inst->mealCustomizationToObj();
echo $inst->mealCustomizationToJson().PHP_EOL;
echo $meal->no_added_salt.PHP_EOL.PHP_EOL;

$mealCustomUpdates = new MealCustomizationObj();
$mealCustomUpdates->no_added_salt = CUser::OPTED_OUT;
$mealCustomUpdates->no_onions = CUser::OPTED_IN;

$inst->updateMealCustomization($mealCustomUpdates);
echo $inst->mealCustomizationToJson().PHP_EOL;
$meal2 = $inst->mealCustomizationToObj();
echo $meal2->no_added_salt.PHP_EOL.PHP_EOL;


$oc = $inst->orderCustomizationToObj();
//echo $inst->orderCustomizationToJson().PHP_EOL;;