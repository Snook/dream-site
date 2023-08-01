<?php
/**
 * Table Definition for booking
 */
require_once 'DAO.inc';

class DAO_Booking extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'booking';                         // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $session_id;                      // int(11)  not_null multiple_key unsigned
    public $user_id;                         // int(11)  not_null multiple_key unsigned
    public $order_id;                        // int(11)  multiple_key unsigned
    public $status;                          // string(11)  enum
    public $booking_type;                    // string(8)  enum
    public $reason_for_cancellation;        // enum
    public $declined_MFY_option;            // tinyint(1)
    public $declined_to_reschedule;           // tinyint(1)
    public $no_show;						 // int(4)  not_null	
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null
    public $is_migrated;                     // int(4)  

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Booking',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
