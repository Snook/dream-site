<?php

require_once 'DAO/Address.php';
require_once 'DAO/BusinessObject/CStatesAndProvinces.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CAddress
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *
 *  	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class CAddress extends DAO_Address
{

	//location types
	const BILLING = 'BILLING';
	const SHIPPING = 'SHIPPING';
	const INVITE = 'INVITE';
	const ADDRESS_BOOK = 'ADDRESS_BOOK';
	const OTHER = 'OTHER';

	function __construct()
	{
		parent::__construct();
	}

	function insert($ignoreAffectedRows = false)
	{
		if (empty($this->location_type))
		{
			$this->location_type = self::BILLING;
		}

		if (empty($this->country_id))
		{
			$this->country_id = 'US';
		}

		return parent::insert($ignoreAffectedRows);
	}

	/**
	 * Called by insert and update to validate the object before sending to the db.
	 *
	 * @throws exception
	 * @access public
	 */
	function validate()
	{
		if (!empty($this->state_id) && !CStatesAndProvinces::IsValid($this->state_id))
		{
			throw new Exception ("invalid state id");
		}

		return parent::validate();
	}

	static function addToAddressBook($addressData, $user_id, $locationType = CAddress::ADDRESS_BOOK, $isPrimary = false)
	{
		$newABAddress = DAO_CFactory::create('address');
		$newABAddress->user_id = $user_id;
		$newABAddress->location_type = $locationType;

		// Address book is the only type that allows multiple, otherwise update existing location_type
		$updateOriginal = false;

		if ($locationType !== CAddress::ADDRESS_BOOK)
		{
			if ($newABAddress->find(true))
			{
				$updateOriginal = clone($newABAddress);
			}
		}

		$shipping_phone_number = $addressData['shipping_phone_number'];
		$shipping_phone_number_new = $addressData['shipping_phone_number_new'];
		$shipping_email_address = $addressData['shipping_email_address'];

		$newABAddress->firstname = $addressData['shipping_firstname'];
		$newABAddress->lastname = $addressData['shipping_lastname'];
		$newABAddress->address_line1 = $addressData['shipping_address_line1'];
		$newABAddress->address_line2 = $addressData['shipping_address_line2'];
		$newABAddress->city = $addressData['shipping_city'];
		$newABAddress->state_id = $addressData['shipping_state_id'];
		$newABAddress->postal_code = $addressData['shipping_postal_code'];
		$newABAddress->country_id = 'US';
		$newABAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
		$newABAddress->email_address = $shipping_email_address;
		$newABAddress->is_primary = $isPrimary ? 1 : 0;

		if ($updateOriginal)
		{
			$newABAddress->update($updateOriginal);
		}
		else
		{
			$newABAddress->insert();
		}
	}

	static function updateAddressBook($addressData, $address_id, $user_id)
	{

		if (empty($address_id) || !is_numeric($address_id))
		{
			return;
		}

		if (empty($user_id) || !is_numeric($user_id))
		{
			return;
		}

		$address = DAO_CFactory::create('address');
		$address->query("select * from address where id = $address_id and user_id = $user_id and is_deleted = 0 and location_type = 'ADDRESS_BOOK'");
		if ($address->fetch())
		{
			$exVersion = clone($address);

			$shipping_phone_number = $addressData['shipping_phone_number'];
			$shipping_phone_number_new = $addressData['shipping_phone_number_new'];
			$shipping_email_address = $addressData['shipping_email_address'];

			// update address book with new row
			$address->firstname = $addressData['shipping_firstname'];
			$address->lastname = $addressData['shipping_lastname'];
			$address->address_line1 = $addressData['shipping_address_line1'];
			$address->address_line2 = $addressData['shipping_address_line2'];
			$address->city = $addressData['shipping_city'];
			$address->state_id = $addressData['shipping_state_id'];
			$address->postal_code = $addressData['shipping_postal_code'];
			$address->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
			$address->email_address = $shipping_email_address;
			$address->update($exVersion);
		}
	}


	static function isAddressNew($addressData, $user_id, $locationType = CAddress::ADDRESS_BOOK)
	{
		$addressArray = array();
		$currentAddresses = DAO_CFactory::create('address');
		$currentAddresses->query("select * from address where user_id = $user_id and location_type= $locationType and is_deleted = 0");
		while($currentAddresses->fetch())
		{
			$addressArray[] = DAO::getCompressedArrayFromDAO($currentAddresses);
		}

		$hasMatch = false;
		foreach($addressArray as $thisABAddress)
		{
			if ($addressData['shipping_postal_code'] != $thisABAddress['postal_code'])
			{
				continue;
			}
			if ($addressData['shipping_firstname'] != $thisABAddress['firstname'])
			{
				continue;
			}
			if ($addressData['shipping_lastname'] != $thisABAddress['lastname'])
			{
				continue;
			}
			if ($addressData['shipping_address_line1'] != $thisABAddress['address_line1'])
			{
				continue;
			}
			if ($addressData['shipping_address_line2'] != $thisABAddress['address_line2'])
			{
				continue;
			}
			if ($addressData['shipping_city'] != $thisABAddress['city'])
			{
				continue;
			}
			if ($addressData['shipping_state_id'] != $thisABAddress['state_id'])
			{
				continue;
			}
			if ($addressData['shipping_email_address'] != $thisABAddress['email_address'])
			{
				continue;
			}
			$hasMatch = true;

		}

		return !$hasMatch;
	}


}