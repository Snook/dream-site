<?php
/**
 * Table Definition for orders_address
 */
require_once 'DAO.inc';

class DAO_Orders_address extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'orders_address';                         // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $order_id;                         // int(10)  not_null multiple_key unsigned
	public $firstname;                   // string(255)  not_null
	public $lastname;                   // string(255)  not_null
	public $telephone_1;                   // string(255)  not_null
	public $address_line1;                   // string(255)  not_null
    public $address_line2;                   // string(255)
    public $city;                            // string(64)  not_null
    public $county;                          // string(64)
    public $state_id;                        // string(2)  not_null multiple_key
    public $country_id;                      // string(2)  not_null multiple_key
    public $postal_code;                     // string(10)
    public $usps_adc;                        // string(4)
	public $email_address;                   // string(255)
    public $address_note;                    // string(255)
	public $is_gift;						 // int(1)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(10)  multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null
    public $is_migrated;                     // int(4)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Orders_address',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
