<?php
require_once("../includes/Config.inc");
require_once('includes/CCartStorage.inc');
require_once('includes/CLog.inc');
require_once 'includes/DAO/BusinessObject/CUser.php';
require_once 'includes/DAO/BusinessObject/COrderMinimum.php';

$user= new CUser();
$user->id = 907086;
$user->find(true);

$order = new COrders();
$order->id = 3690917;
$order->find(true);
$order->reconstruct();

echo $user->determineQualifyingOrderId($order);

echo $user->doesOrderMeetMinimum($order) ? 'YES IT MEETS MIN' : 'NO IT DOES NOT MEET MIN';


//$user->establishMonthlyMinimumQualifyingOrder(245, 244);

//echo COrderMinimum::allowsAdditionalOrdering(159,245);
//echo COrderMinimum::allowsAdditionalOrdering(159,246);
//echo COrderMinimum::allowsAdditionalOrdering(200,245);


//$min = COrderMinimum::fetchInstanceByMinimumType(COrderMinimum::ITEM, COrderMinimum::STANDARD_ORDER_TYPE, 159, 245);
//
//echo $min->toJson() . PHP_EOL;
//
//$user = DAO_CFactory::create('user');
//$user->id = 913686;
//$user->find(true);
//
//
//
//
//$min->updateBasedOnUser($user);
//
//
//echo 'Minimum is' .($min->isMinimumApplicable($user) ? '' : ' not').' applicable.' . PHP_EOL;
//echo 'Menu = ' . $min->getMenuId(). PHP_EOL;
//echo 'Store = ' . $min->getStoreId(). PHP_EOL;
//echo 'Store Name= ' . $min->getStore()->store_name. PHP_EOL;
//echo 'Minimum = ' . $min->getMinimum(). PHP_EOL;
//echo 'Minimum Type = ' . $min->getMinimumType(). PHP_EOL;
//echo 'Order Type = ' . $min->getOrderType(). PHP_EOL;
//echo 'Allows Additional Ordering = ' . $min->getAllowsAdditionalOrdering(). PHP_EOL;
//
//
//$min = COrderMinimum::fetchInstanceByMinimumType(COrderMinimum::ITEM, COrderMinimum::STANDARD_ORDER_TYPE, 200, 245);
//
//echo $min->toJson() . PHP_EOL;
//
//$user = DAO_CFactory::create('user');
//$user->id = 913686;
//$user->find(true);
//
//
//
//
//$min->updateBasedOnUser($user);
//
//echo 'Minimum is' .($min->isMinimumApplicable($user) ? '' : ' not').' applicable.' . PHP_EOL;
//echo 'Menu = ' . $min->getMenuId(). PHP_EOL;
//echo 'Store = ' . $min->getStoreId(). PHP_EOL;
//echo 'Store Name= ' . $min->getStore()->store_name. PHP_EOL;
//echo 'Minimum = ' . $min->getMinimum(). PHP_EOL;
//echo 'Minimum Type = ' . $min->getMinimumType(). PHP_EOL;
//echo 'Order Type = ' . $min->getOrderType(). PHP_EOL;
//echo 'Allows Additional Ordering = ' . $min->getAllowsAdditionalOrdering(). PHP_EOL;
//
//
//
//$min = COrderMinimum::fetchInstanceByMinimumType(COrderMinimum::ITEM, COrderMinimum::STANDARD_ORDER_TYPE, 244, 245);
//
//echo $min->toJson() . PHP_EOL;
//
//$user = DAO_CFactory::create('user');
//$user->id = 913686;
//$user->find(true);
//
//
//
//
//$min->updateBasedOnUser($user);
//
//echo 'Minimum is' .($min->isMinimumApplicable($user) ? '' : ' not').' applicable.' . PHP_EOL;
//echo 'Menu = ' . $min->getMenuId(). PHP_EOL;
//echo 'Store = ' . $min->getStoreId(). PHP_EOL;
//echo 'Store Name= ' . $min->getStore()->store_name. PHP_EOL;
//echo 'Minimum = ' . $min->getMinimum(). PHP_EOL;
//echo 'Minimum Type = ' . $min->getMinimumType(). PHP_EOL;
//echo 'Order Type = ' . $min->getOrderType(). PHP_EOL;
//echo 'Allows Additional Ordering = ' . $min->getAllowsAdditionalOrdering(). PHP_EOL;