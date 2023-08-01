<?php
/**
 * Table Definition for store_coupon_code_exclusion
 */
require_once 'DAO.inc';

class DAO_Store_coupon_code_exclusion extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_coupon_code_exclusion';     // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(11)  not_null multiple_key unsigned
    public $coupon_code_id;                  // int(11)  not_null unsigned
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $created_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_coupon_code_exclusion',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
