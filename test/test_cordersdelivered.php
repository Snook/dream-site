<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/DAO/BusinessObject/COrdersDelivered.php';
require_once '/processor/calculatetax.php';


$order = new COrdersDelivered();
$order->id = 3619260;
$order->find(true);
$order->applyTax();

//$processor = new calculatetax();
echo $processor->addtax();

?>