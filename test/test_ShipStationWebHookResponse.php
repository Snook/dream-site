<?php
require_once("../includes/Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationOrderBatchWrapper.php';

//$result = ShipStationManager::getInstance()->listWebhooks();
//echo $result;

//$result = ShipStationManager::getInstance()->subscribeWebhook("https://67.171.15.168:8443/ddproc.php?processor=admin_shipping_wh","ORDER_NOTIFY",'125909','Test Order WebHook');

//$result = ShipStationManager::getInstance()->listWebhooks();


//Fetch one unprocessed from transient_data_store: select * from transient_data_store where data_class = SHIPPING_SHIP_NOTIFICATION_NEW
$batchWrapper = ShipStationManager::getInstance()->loadOrderShippingInfo();