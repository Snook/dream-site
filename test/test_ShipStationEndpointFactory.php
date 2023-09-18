<?php
require_once("../includes/Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationEndpointFactory.php';
require_once("DAO/BusinessObject/CStore.php");
///Good Config
$storeObj = new CStore();
$storeObj->id = 310;
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
$storeObj->id = 311;
$config = ShipStationEndpointFactory::getEndpoint($storeObj);

echo $config->getEndpoint();
echo PHP_EOL;
echo $config->getApiKey();
echo PHP_EOL;
echo $config->getApiSecret();
echo PHP_EOL;
echo $config->getAuthorization();
?>