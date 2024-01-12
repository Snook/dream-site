<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/DAO/BusinessObject/CUser.php';
require_once 'includes/DAO/BusinessObject/CStoreFee.php';
require_once 'includes/DAO/BusinessObject/CStore.php';
require_once 'includes/OrdersCustomization.php';

$DAO_store = DAO_CFactory::create('store', true);
$DAO_store->id = 315;
$DAO_store->find_DAO_store(true);

$DAO_menu = DAO_CFactory::create('menu', true);
$DAO_menu->id = CMenu::getCurrentMenuId();
$DAO_menu->find(true);

$service_days = 1;

$sessionsArray = CSession::getCurrentDeliveredSessionArrayForDistributionCenter($DAO_store, $service_days, false);

$sessionsArray = CSession::getCurrentDeliveredSessionArrayForCustomer($DAO_store, $service_days, false, $DAO_menu->id, true, false, true);


?>