<?php

require_once 'DAO/Store_pickup_location.php';

class CStorePickUpLocation extends DAO_Store_pickup_location
{

	function __construct()
	{
		parent::__construct();
	}

	function generateAddressLinear()
	{
		return $this->address_line1 . (!empty($this->address_line2) ? " " . $this->address_line2 : "") . ", " . $this->city . ", " . $this->state_id . " " . $this->postal_code . (!empty($this->usps_adc) ? "-" . $this->usps_adc : "");
	}

	function generateAddressHTML()
	{
		return nl2br($this->generateAddressWithBreaks());
	}

	function generateAddressWithBreaks()
	{
		return $this->address_line1 . (!empty($this->address_line2) ? " " . $this->address_line2 : "") . "\n" . $this->city  . ", " . $this->state_id . " " .  $this->postal_code . (!empty($this->usps_adc) ? "-" . $this->usps_adc : "");
	}

	function is_Active()
	{
		return !empty($this->active);
	}

	function generateAnchor()
	{
		return $this->id . '-' . str_replace(' ', '_', strtolower($this->city)) . '-pick-up';
	}
}
?>