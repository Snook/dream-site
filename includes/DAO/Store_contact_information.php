<?php
/**
 * Table Definition for store_contact_information
 */
require_once 'DAO.inc';

class DAO_Store_contact_information extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'store_contact_information';				// table name
	public $id;								// int(11) not_null primary_key unsigned auto_increment
	public $store_id;						// int(8) not_null unsigned auto_increment
	public $pkg_ship_same_as_store;			// int(1)
	public $pkg_ship_address_line1;			// string(255)
	public $pkg_ship_address_line2;			// string(255)
	public $pkg_ship_city;					// string(64)
	public $pkg_ship_state_id;				// string(2)
	public $pkg_ship_postal_code;			// int(1)
	public $pkg_ship_attn;					// string(255)
	public $pkg_ship_telephone_day;			// string(64)
	public $letter_ship_same_as_store;		// string(64)
	public $letter_ship_address_line1;		// string(255)
	public $letter_ship_address_line2;		// string(255)
	public $letter_ship_city;				// string(64)
	public $letter_ship_state_id;			// string(2)
	public $letter_ship_postal_code;		// string(10)
	public $letter_ship_attn;				// string(255)
	public $letter_ship_telephone_day;		// string(64)
	public $owner_1_name;					// string(255)
	public $owner_1_nickname;				// string(255)
	public $owner_1_address_line1;			// string(255)
	public $owner_1_address_line2;			// string(255)
	public $owner_1_city;					// string(255)
	public $owner_1_state_id;				// string(255)
	public $owner_1_postal_code;			// string(255)
	public $owner_1_telephone_primary;		// string(255)
	public $owner_1_telephone_secondary;	// string(255)
	public $owner_1_email_address;			// string(255)
	public $owner_2_name;					// string(255)
	public $owner_2_nickname;				// string(255)
	public $owner_2_address_line1;			// string(255)
	public $owner_2_address_line2;			// string(255)
	public $owner_2_city;					// string(255)
	public $owner_2_state_id;				// string(255)
	public $owner_2_postal_code;			// string(255)
	public $owner_2_telephone_primary;		// string(255)
	public $owner_2_telephone_secondary;	// string(255)
	public $owner_2_email_address;			// string(255)
	public $owner_3_name;					// string(255)
	public $owner_3_nickname;				// string(255)
	public $owner_3_address_line1;			// string(255)
	public $owner_3_address_line2;			// string(255)
	public $owner_3_city;					// string(255)
	public $owner_3_state_id;				// string(255)
	public $owner_3_postal_code;			// string(255)
	public $owner_3_telephone_primary;		// string(255)
	public $owner_3_telephone_secondary;	// string(255)
	public $owner_3_email_address;			// string(255)
	public $owner_4_name;					// string(255)
	public $owner_4_nickname;				// string(255)
	public $owner_4_address_line1;			// string(255)
	public $owner_4_address_line2;			// string(255)
	public $owner_4_city;					// string(255)
	public $owner_4_state_id;				// string(255)
	public $owner_4_postal_code;			// string(255)
	public $owner_4_telephone_primary;		// string(255)
	public $owner_4_telephone_secondary;	// string(255)
	public $owner_4_email_address;			// string(255)
	public $manager_1_name;					// string(255)
	public $manager_1_nickname;				// string(255)
	public $manager_1_telephone_primary;	// string(255)
	public $timestamp_updated;				// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;				// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;						// int(11)  multiple_key unsigned
	public $updated_by;						// int(11)  multiple_key unsigned
	public $is_deleted;						// int(1)  not_null multiple_key

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_contact_information',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
