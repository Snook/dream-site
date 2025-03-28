<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationRateWrapper.php';


$order = new COrdersDelivered();
$order->id = 3776026;
$order->find(true);

$rateRequest = new ShipStationRateWrapper($order,ShipStationRateWrapper::SS_CARRIER_FEDEX);
$rates = ShipStationManager::getInstanceForOrder($order)->getRates( $rateRequest );
//
//
//
echo $rates->getDeliveryFeeByServiceCode(ShipStationRateWrapper::SS_FEDEX_GROUND);
echo $rates->getDeliveryFeeByServiceCode(ShipStationRateWrapper::SS_FEDEX_EXPRESS_SAVER);
echo $rates->getDeliveryFeeByServiceCode(ShipStationRateWrapper::SS_FEDEX_OVERNIGHT_FIRST);

$rates = ShipStationManager::getInstanceForOrder($order)->getRates( $rateRequest );
//
//
//
echo $rates->getDeliveryFeeByServiceCode(ShipStationRateWrapper::SS_FEDEX_GROUND);
echo $rates->getDeliveryFeeByServiceCode(ShipStationRateWrapper::SS_FEDEX_EXPRESS_SAVER);
echo $rates->getDeliveryFeeByServiceCode(ShipStationRateWrapper::SS_FEDEX_OVERNIGHT_FIRST);


//
//$services = ShipStationManager::getInstance()->getServices(ShipStationRateWrapper::SS_CARRIER_FEDEX);
//echo $services;

//$services = ShipStationManager::getInstance()->getWebhooks();
//echo $services;