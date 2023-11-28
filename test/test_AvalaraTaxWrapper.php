<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO/BusinessObject/COrdersDelivered.php");
require_once 'includes/api/tax/avalara/AvalaraTaxWrapper.php';

$order = new COrdersDelivered();
$order->id = 3619273;
$order->find(true);

$tax = new AvalaraTaxWrapper($order);

echo $tax->getRateRequestJson();