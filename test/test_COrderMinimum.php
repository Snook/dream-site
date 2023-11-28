<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO/BusinessObject/COrderMinimum.php");
require_once("includes/DAO/BusinessObject/CUser.php");
require_once("includes/DAO/BusinessObject/COrders.php");
require_once("CLog.inc");
require_once("CAppUtil.inc");
require_once("CApp.inc");
require_once("DAO/BusinessObject/CPayment.php");
require_once("DAO/BusinessObject/CBox.php");
require_once("DAO/BusinessObject/CBoxInstance.php");
require_once("DAO/BusinessObject/CSession.php");


COrderMinimum::carryForwardMinimums(247,248);

//$minimum = new COrderMinimum();

//COrderMinimum::createInstance(COrderMinimum::ITEM, 3);
//COrderMinimum::createInstance(COrderMinimum::SERVING, 36);
//COrderMinimum::createInstance(COrderMinimum::ITEM, 3, COrderMinimum::STANDARD_ORDER_TYPE,159,244);
//COrderMinimum::createInstance(COrderMinimum::SERVING,36, COrderMinimum::STANDARD_ORDER_TYPE,159,244);
//COrderMinimum::createInstance(COrderMinimum::SERVING,36, COrderMinimum::STANDARD_ORDER_TYPE,159,244);
//COrderMinimum::createInstance(COrderMinimum::SERVING, 36,COrderMinimum::STANDARD_ORDER_TYPE,null,244);
//COrderMinimum::createInstance(COrderMinimum::SERVING,36,COrderMinimum::STANDARD_ORDER_TYPE,159,null);
//COrderMinimum::createInstance(COrderMinimum::ITEM,3,COrderMinimum::STANDARD_ORDER_TYPE,null,244);
////
//COrderMinimum::createInstance(COrderMinimum::ITEM,3, COrderMinimum::STANDARD_ORDER_TYPE,21,244);
////
//COrderMinimum::createInstance(COrderMinimum::ITEM,3, COrderMinimum::STANDARD_ORDER_TYPE,244,244);
//
//
//
//echo COrderMinimum::inferMinimumType(COrderMinimum::STANDARD_ORDER_TYPE,244,244).PHP_EOL;
//
//
//$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE);
//echo $minimum->getId().PHP_EOL;
//echo $minimum->getMinimumType().PHP_EOL;
//echo $minimum->getMinimum().PHP_EOL;
//$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE,159, null);
//echo $minimum->getId().PHP_EOL;
//$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE,null,244);
//echo $minimum->getId().PHP_EOL;
//$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE,12,12);
//echo $minimum->getId().PHP_EOL;
//$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE,12,244);
//echo $minimum->getId().PHP_EOL;
//$minimum = COrderMinimum::fetchInstance(COrderMinimum::STANDARD_ORDER_TYPE,159,12);
//echo $minimum->getId().PHP_EOL;
//
//$minimum = COrderMinimum::fetchInstance(COrderMinimum::SERVING,COrderMinimum::STANDARD_ORDER_TYPE,159,244);
//
////Instance operations
//echo $minimum->getId().PHP_EOL;
//echo $minimum->getMinimumType().PHP_EOL;
//echo $minimum->getMinimum().PHP_EOL;
//echo $minimum->getStoreId().PHP_EOL;
//echo print_r($minimum->getStore()->toArray(),true).PHP_EOL;
//echo $minimum->getMenuId().PHP_EOL;
//echo print_r($minimum->getMenu()->toArray(),true).PHP_EOL;
//echo $minimum->toJson().PHP_EOL;
//
//echo COrderMinimum::asJson(COrderMinimum::SERVING).PHP_EOL;
//echo COrderMinimum::asJson(COrderMinimum::ITEM).PHP_EOL;
//echo COrderMinimum::asJson(COrderMinimum::ITEM,COrderMinimum::STANDARD_ORDER_TYPE).PHP_EOL;
//echo COrderMinimum::asJson(COrderMinimum::ITEM,COrderMinimum::STANDARD_ORDER_TYPE,159,244).PHP_EOL;
//echo COrderMinimum::asJson(COrderMinimum::SERVING,COrderMinimum::STANDARD_ORDER_TYPE,159,244).PHP_EOL;
//echo COrderMinimum::asJson(COrderMinimum::SERVING,COrderMinimum::STANDARD_ORDER_TYPE,null,244).PHP_EOL;
//echo COrderMinimum::asJson(COrderMinimum::SERVING,COrderMinimum::STANDARD_ORDER_TYPE,159,null).PHP_EOL;

//echo print_r(unserialize(base64_decode(YToxOntpOjA7aToxOTg1NTt9)),true);


//$minimum = COrderMinimum::fetchInstance(COrderMinimum::ITEM);


$order = new COrders();
$order->id = 3690909;
$order->find(true);
$order->reconstruct();


$order->hasStandardCoreServingMinimum(null,244);