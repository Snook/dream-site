<?php
/**
 * Table Definition for premium
 */
require_once 'DAO.inc';

class DAO_Premium extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'premium';                         // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  multiple_key unsigned
    public $is_global;                       // int(4)  
    public $premium_type;                    // string(10)  enum
    public $premium_value;                   // real(6)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Premium',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
