<?php
/**
 * Table Definition for user_program_membership
 */
require_once 'DAO.inc';

class DAO_User_program_membership extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_program_membership';         // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  not_null multiple_key unsigned
    public $store_id;                        // int(8)  multiple_key unsigned
    public $menu_program_type_id;            // int(11)  unsigned
    public $membership_status;               // int(11)  not_null unsigned
    public $start_date;                      // timestamp(19)  not_null unsigned zerofill binary
    public $end_date;                        // timestamp(19)  not_null unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null
    public $enrollment_package_id;           // int(10)  unsigned
    public $order_id;                        // int(10)  unsigned
    public $order_count;                     // int(10)  not_null unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_program_membership',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
