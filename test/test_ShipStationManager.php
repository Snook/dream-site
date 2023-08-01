<?php
require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationOrderBatchWrapper.php';
require_once 'includes/api/shipping/shipstation/ShipStationShipmentWrapper.php';

//Get Carriers
//$result = ShipStationManager::getInstance()->getCarriers();
//if ($result == false)
//{
//	echo print_r(ShipStationManager::getInstance()->getLastError(), true);
//}
//else
//{
//	echo $result;
//}

//$order = new COrdersDelivered();
//$order->id = 3641674;
//$order->find(true);
////$order->refresh(null);
////$order->recalculate();
////
//$order->orderShipping();
//$order->orderAddress();
//
////$result = ShipStationManager::getInstance()->listAccountTags();
////
////echo $result;
////Store Id Manual Orders
////$result = ShipStationManager::getInstance()->listStores();
////echo $result;
//$orderMap = new ShipStationOrderWrapper($order);
//
//if(true){
//
//	$result = ShipStationManager::getInstance()->addUpdateOrder($orderMap);
//	if ($result == false)
//	{
//		echo print_r(ShipStationManager::getInstance()->getLastError(), true);
//	}
//	else
//	{
//		echo $result;
//	}
//}

//echo ShipStationManager::getInstance()->getOrders(array('orderNumber'=>3662287));
//echo ShipStationManager::getInstance()->getShipments(array('orderNumber'=>3662287));

$order = new COrdersDelivered();
$order->id = 3653728;


$shipmentWrapper = ShipStationManager::getInstance()->getShipments(new ShipStationShipmentWrapper($order));
//echo $shipmentWrapper->getLatestTrackingNumber();
//$shipmentWrapper->getTrackingNumbers();
$result = $shipmentWrapper->storeShippingData();
if($result->isFailure()){
	$result->echoFailureMessages();
}else{
	$result->echoAllMessages();
}


?>