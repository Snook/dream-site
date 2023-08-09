<?php
require_once("../includes/Config.inc");
require_once 'includes/DAO/BusinessObject/CUser.php';
require_once 'includes/DAO/BusinessObject/CStoreActivityLog.php';

echo CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::GENERIC,CStoreActivityLog::SUBTYPE_GENERIC);
echo CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::INVENTORY,CStoreActivityLog::SUBTYPE_LOW);

$activity_type_id = CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::INVENTORY,CStoreActivityLog::SUBTYPE_LOW);

echo CStoreActivityLog::addEvent(244,'Inventory Level is low for item id = 123','2022-11-08',$activity_type_id);

echo print_r(CStoreActivityLog::fetchAllEventsInTimeframe(244,'2022-11-01','2022-11-08'),true);

echo print_r(CStoreActivityLog::fetchSpecificEventsInTimeframe(244,$activity_type_id,'2022-11-01','2022-11-08'),true);

echo print_r(CStoreActivityLog::fetchStoreActivityTypeAndSubTypeById(1),true);
echo print_r(CStoreActivityLog::fetchStoreActivityTypeAndSubTypeById(2),true);
echo print_r(CStoreActivityLog::fetchStoreActivityTypeAndSubTypeById(3),true);
echo print_r(CStoreActivityLog::fetchStoreActivityTypeAndSubTypeById(4),true);
echo print_r(CStoreActivityLog::fetchStoreActivityTypeAndSubTypeById(5),true);
echo print_r(CStoreActivityLog::fetchStoreActivityTypeAndSubTypeById(6),true);