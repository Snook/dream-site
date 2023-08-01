<?php
/**
 * Table Definition for volume_discount_type
 */
require_once 'DAO.inc';

class DAO_Volume_discount_type extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'volume_discount_type';            // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $menu_id;                         // int(8)  multiple_key unsigned
    public $discount_value;                  // real(8)  not_null
    public $is_active;                       // int(4)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $discount_type_id;                // int(3)  not_null multiple_key unsigned
    public $created_by;                      // int(10)  multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Volume_discount_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
