<?php
/**
 * Table Definition for enrollment_package
 */
require_once 'DAO.inc';

class DAO_Enrollment_package extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'enrollment_package';              // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $product_id;                      // int(11)  not_null unsigned
    public $enrollment_period_type;          // int(8)  unsigned
    public $enrollment_period_length;        // int(8)  unsigned
    public $order_purchase_limit;            // int(8)  unsigned
    public $enrollment_type_id;              // int(11)  unsigned
    public $membership_status;               // int(11)  not_null unsigned
    public $offering_start_date;             // timestamp(19)  not_null unsigned zerofill binary
    public $offering_end_date;               // timestamp(19)  not_null unsigned zerofill binary
    public $enrollment_end_date;             // timestamp(19)  not_null unsigned zerofill binary
    public $is_new_user_eligible;            // int(4)  not_null
    public $is_existing_user_eligible;       // int(4)  not_null
    public $is_TODD_user_eligible;           // int(4)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Enrollment_package',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
