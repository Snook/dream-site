<?php
/**
 * Table Definition for enrollment_package_to_order
 */
require_once 'DAO.inc';

class DAO_Enrollment_package_to_order extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'enrollment_package_to_order';     // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_program_membership_id;      // int(11)  not_null unsigned
    public $order_id;                        // int(11)  not_null unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Enrollment_package_to_order',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
