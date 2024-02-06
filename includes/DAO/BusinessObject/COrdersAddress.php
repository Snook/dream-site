<?php

require_once 'DAO/Orders_address.php';

class COrdersAddress extends DAO_Orders_address
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

	function isGift()
	{
		if(!empty($this->is_gift))
		{
			return true;
		}

		return false;
	}
}
?>