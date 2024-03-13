<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

//require_once("C:\wamp\www\DreamSite\includes\Config.inc");
require_once("/DreamSite/includes/Config.inc");

require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/CFactory.php");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("DAO/BusinessObject/COrders.php");
require_once("CLog.inc");


$StoreObj = DAO_CFactory::create('store');
$StoreObj->id = 159;
$StoreObj->find(true);
print_r(CSession::getMonthlySessionInfoArray($StoreObj, '2022-05-01', 249, CSession::ALL_STANDARD, true, true));

?>
