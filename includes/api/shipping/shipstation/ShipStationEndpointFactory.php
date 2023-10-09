<?php
require_once("includes/api/shipping/shipstation/endpoint/EndpointConfig.inc");
class ShipStationEndpointFactory
{
	public static function getEndpoint($storeObj)
	{
		return new EndpointConfig($storeObj);
	}

}

?>