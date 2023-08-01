<?php
/**
 * Table Definition for user_retention_data
 */
require_once 'DAO.inc';

class DAO_User_retention_data extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_retention_data';             // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(10)  not_null multiple_key unsigned
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $booking_id;                      // int(10)  not_null multiple_key unsigned
    public $number_days_inactivity;          // int(6)  not_null
    public $booking_count;                   // int(3)  not_null
    public $is_active;                       // int(4)  not_null
    public $updated_order_id;                // int(10)  multiple_key unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $has_placed_order_after_120_days;    // int(4)  not_null
    public $is_reactivated;                  // int(3)  not_null unsigned
    public $is_archived;                     // int(3)  not_null unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_retention_data',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
