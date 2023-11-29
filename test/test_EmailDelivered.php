<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO.inc");
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/COrdersDelivered.php');

/*
 *  Test order confirmation email
 */
$DAO_orders = new COrdersDelivered();
$DAO_orders->id = 3816265;
$DAO_orders->find(true);

$DAO_user = DAO_CFactory::create('user', true);
$DAO_user->id = $DAO_orders->user_id;
$DAO_user->find(true);

COrdersDelivered::sendConfirmationEmail($DAO_user, $DAO_orders);

?>