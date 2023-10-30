<?php

require_once("../Config.inc");
require_once("CLog.inc");
require_once("includes/api/shipping/shipstation/ShipStationManager.php");
require_once("includes/api/shipping/shipstation/ShipStationOrderBatchWrapper.php");

ShipStationManager::loadOrderShippingInfo();


?>