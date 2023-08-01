<?php
/**
 * Table Definition for session_discount
 */
require_once 'DAO.inc';

class DAO_Session_discount extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'session_discount';                // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $discount_type;                   // string(7)  not_null enum
    public $menu_item_id;                    // int(8)  multiple_key unsigned
    public $product_item_id;                 // int(8)  multiple_key unsigned
    public $discount_var;                    // real(8)  
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
    public $is_deleted;                      // int(4)  

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Session_discount',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
