<?php
require_once("../includes/Config.inc");
require_once("DAO.inc");
require_once('includes/DAO/BusinessObject/COrders.php');

/*
 *  Test order confirmation email
 */
$DAO_orders = DAO_CFactory::create('orders');
$DAO_orders->id = 3839619;
$DAO_orders->find(true);

$DAO_user = DAO_CFactory::create('user');
$DAO_user->id = $DAO_orders->user_id;
$DAO_user->find(true);

COrders::sendConfirmationEmail($DAO_user, $DAO_orders, $DAO_orders->getGiftCards());

?>