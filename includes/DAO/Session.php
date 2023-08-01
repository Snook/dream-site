<?php
/**
 * Table Definition for session
 */
require_once 'DAO.inc';

class DAO_Session extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'session';                         // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $menu_id;                         // int(8)  not_null multiple_key unsigned
    public $available_slots;                 // int(6)  not_null
    public $introductory_slots;              // int(6)  not_null
    public $sneak_peak;                      // int(4)  not_null
    public $duration_minutes;                // int(6)  not_null
	public $session_title;                 // varchar(255)
	public $session_details;                 // blob(65535)  blob
    public $session_type;                    // string(13)  enum
    public $session_class;                    // string(13)  enum
	public $session_type_subtype;                    // string(13)  enum
    public $session_publish_state;           // string(9)  enum
    public $admin_notes;                     // blob(65535)  blob
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $session_start;                   // datetime(19)  multiple_key binary
    public $session_close_scheduling;        // datetime(19)  binary
	public $session_close_scheduling_meal_customization;        // datetime(19)  binary
	public $session_discount_id;             // int(5)  multiple_key unsigned
	public $session_assembly_fee;             // decimal(5)  unsigned
	public $session_delivery_fee;             // decimal(5)  unsigned
    public $close_interval_type;             // string(12)  enum
    public $session_password;                // string(41)
    public $session_lead;					// int(11) unsigned
    public $inventory_was_processed;		 // int (1)
	public $delivered_supports_shipping;	//int(1)
	public $delivered_supports_delivery;	// int(1)
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null
    public $is_migrated;                     // int(4)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Session',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}