<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationOrderWrapper.php';

$order = new COrdersDelivered(true);
$order->id = 3650620;
$order->find_DAO_orders(true);
$order->reconstruct();
$order->orderAddress();

$orderMap = new ShipStationOrderWrapper($order);

echo $orderMap->toJsonString();