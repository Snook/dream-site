<?php
require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationOrderWrapper.php';

$order = new COrdersDelivered();
$order->id = 3650620;
$order->find(true);
$order->reconstruct();
$order->orderAddress();

$orderMap = new ShipStationOrderWrapper($order);

echo $orderMap->toJsonString();