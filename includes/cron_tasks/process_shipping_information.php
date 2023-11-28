<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("CLog.inc");
require_once("includes/api/shipping/shipstation/ShipStationManager.php");
require_once("includes/api/shipping/shipstation/ShipStationOrderBatchWrapper.php");

ShipStationManager::loadOrderShippingInfo();


?>