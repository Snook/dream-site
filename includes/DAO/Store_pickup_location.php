<?php
/**
 * Table Definition for store_pickup_location
 */
require_once 'DAO.inc';

class DAO_Store_pickup_location extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'store_pickup_location';				// table name
	public $id;								// int(8)  not_null primary_key unsigned auto_increment
	public $store_id;								// int(8)  not_null primary_key unsigned auto_increment
	public $active;
	public $location_title;
	public $address_line1;
	public $address_line2;
	public $city;
	public $state_id;
	public $postal_code;
	public $default_session_override;
	public $contact_user_id;
	public $timestamp_updated;				// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;				// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;						// int(11)  multiple_key unsigned
	public $updated_by;						// int(11)  multiple_key unsigned
	public $is_deleted;						// int(1)  not_null multiple_key

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_pickup_location',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
