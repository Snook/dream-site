<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationEndpointFactory.php';
require_once("DAO/BusinessObject/CStore.php");
///Good Config
$storeObj = new CStore();
$storeObj->id = 315;
$config = ShipStationEndpointFactory::getEndpoint($storeObj);

echo $config->getEndpoint();
echo PHP_EOL;
echo $config->getApiKey();
echo PHP_EOL;
echo $config->getApiSecret();
echo PHP_EOL;
echo $config->getAuthorization();

//Bad Config
$storeObj = new CStore();
$storeObj->id = 313;
$config = ShipStationEndpointFactory::getEndpoint($storeObj);

echo $config->getEndpoint();
echo PHP_EOL;
echo $config->getApiKey();
echo PHP_EOL;
echo $config->getApiSecret();
echo PHP_EOL;
echo $config->getAuthorization();
?>