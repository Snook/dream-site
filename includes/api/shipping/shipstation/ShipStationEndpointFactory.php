<?php
class ShipStationEndpointFactory
{
	public static function getEndpoint($storeObj)
	{
		$inc = "includes/api/shipping/shipstation/endpoint/EndpointConfig.inc";
		require_once $inc;
		return new EndpointConfig($storeObj);
	}

}

?>