<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/DAO/BusinessObject/COrdersDelivered.php';

$DAO_orders = new COrdersDelivered(true);
$DAO_orders->id = 3619260;
$DAO_orders->find_DAO_orders(true);
$DAO_orders->applyTax();

?>