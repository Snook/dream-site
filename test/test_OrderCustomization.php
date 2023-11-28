<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once('includes/CCartStorage.inc');
require_once('includes/CLog.inc');
require_once 'includes/DAO/BusinessObject/CUser.php';
require_once 'includes/DAO/BusinessObject/COrderMinimum.php';
require_once 'includes/OrdersCustomization.php';



//$order = new COrders();
//$order->id = 3802930;
//$order->find(true);



$user = new CUser();
$user->id =662598;
	$user->find(true);
	$customizations = $user->getMealCustomizationPreferences();

	echo print_r($customizations,true);

//OrdersCustomization:: createOrderCustomizationObj();

//$orderCustomization = OrdersCustomization::getInstance($order);
//
//$orderCustomization->setStoreAllowsMealCustomization(true);
//$orderCustomization->setStoreAllowsPreassembledCustomization(true);
//
//$storeSettings =  $orderCustomization->storeCustomizationSettingsToObj();
//echo print_r($storeSettings, true);
//echo PHP_EOL;
//echo $storeSettings->allowsMealCustomization()? 'Does allow meal customization':'Does not allow meal customization';
//echo PHP_EOL;
//echo $storeSettings->allowsPreAssembledCustomization()? 'Does allow pre-assembled customization':'Does not allow pre-assembled customization';
//
//
//$orderCustomization->setStoreAllowsMealCustomization(false);
//$orderCustomization->setStoreAllowsPreassembledCustomization(false);
//
//$storeSettings =  $orderCustomization->storeCustomizationSettingsToObj();
//echo PHP_EOL;
//echo $storeSettings->allowsMealCustomization()? 'Does allow meal customization':'Does not allow meal customization';
//echo PHP_EOL;
//echo $storeSettings->allowsPreAssembledCustomization()? 'Does allow pre-assembled customization':'Does not allow pre-assembled customization';