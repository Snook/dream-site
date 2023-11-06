<?php
require_once("../includes/Config.inc");
require_once("DAO.inc");


$DAO_orders = DAO_CFactory::create('orders', true);

$DAO_orders->id = 3861404;
//$DAO_orders->whereAdd('orders.id IN (3851040, 3851043)');
//$DAO_orders->user_id = 662598;

$DAO_orders->orderBy("orders.timestamp_created DESC");
$DAO_orders->find(false, array('join_sub_dao' => true));

$ordersArray = array();
while($DAO_orders->fetch())
{
	$ordersArray[$DAO_orders->id] = clone $DAO_orders;
	$ordersArray[$DAO_orders->id]->fetch_DAO_order_item_Array();
	$ordersArray[$DAO_orders->id]->fetch_DAO_payment_Array();
}

$debugbreakpoint = true;
?>