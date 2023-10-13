<?php

require_once 'includes/CLog.inc';

/**
 * Wrapper of Shipstation Order from repsponse to API to Expose properties,
 * render as json.
 *
 */

class ShipStationOrderResponseWrapper
{
	private $ssOrder = null;

	public function __construct($order)
	{

		$this->mapOrder($order);
	}


	private function mapOrder($order)
	{
		$this->ssOrder = $order;

		//Do any special mapping
	}


	public function toJsonString()
	{
		return json_encode($this->ssOrder);
	}

	public function getShipstationOrder()
	{
		return $this->ssOrder;
	}
}