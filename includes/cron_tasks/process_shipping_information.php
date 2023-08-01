<?php

require_once("/DreamSite/includes/Config.inc");
//require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");

require_once("CLog.inc");
require_once("includes/api/shipping/shipstation/ShipStationManager.php");
require_once("includes/api/shipping/shipstation/ShipStationOrderBatchWrapper.php");

ShipStationManager::getInstance()->loadOrderShippingInfo();


?>