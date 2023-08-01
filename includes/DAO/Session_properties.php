<?php
/**
 * Table Definition for session_properties
 */
require_once 'DAO.inc';

class DAO_Session_properties extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'session_properties';         // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $session_host;                    // int(11)  not_null multiple_key unsigned
    public $session_id;                      // int(11)  not_null unsigned
	public $dream_taste_event_id;                     // int(11)  not_null unsigned
	public $fundraiser_id;                     // int(11)  not_null unsigned
	public $store_pickup_location_id;                     // int(11)  not_null unsigned
    public $message;                         // blob(65535)  blob
    public $informal_host_name;              // string(80)
    public $menu_pricing_method;             // string(11)  not_null enum
    public $FULL_PRICE;                      // real(8)
    public $HALF_PRICE;                      // real(8)
    public $facebook_post_id;                // varchar(255) unsigned null
    public $facebook_event_id;               // bigint(22) unsigned null
    public $points_user_history_id;			// int(11)  not_null unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Session_properties',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
