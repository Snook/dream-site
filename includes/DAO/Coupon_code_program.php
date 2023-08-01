<?php
/**
 * Table Definition for coupon_code_program
 */
require_once 'DAO.inc';

class DAO_Coupon_code_program extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'coupon_code_program';             // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $program_name;                    // string(128)  not_null
    public $program_description;             // blob(65535)  blob
    public $program_owner;                   // string(12)  not_null enum
    public $other_program_owner;             // string(80)  
    public $comments;                        // blob(65535)  blob
    public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
    public $created_by;                      // int(10)  multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Coupon_code_program',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
