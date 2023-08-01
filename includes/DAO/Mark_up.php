<?php
/**
 * Table Definition for mark_up
 */
require_once 'DAO.inc';

class DAO_Mark_up extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'mark_up';                         // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $markup_type;                     // string(7)  not_null enum
    public $markup_value;                    // real(6)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $mark_up_start;                   // datetime(19)  binary
    public $mark_up_expiration;              // datetime(19)  binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null
    public $menu_id_start;                   // int(8)  multiple_key unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Mark_up',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
