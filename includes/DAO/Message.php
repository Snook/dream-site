<?php
/**
 * Table Definition for store_coach
 */
require_once 'DAO.inc';

class DAO_Message extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'message';                     // table name
    public $id;                              // int(5)  not_null primary_key multiple_key unsigned auto_increment
    public $type;							 // enum
    public $content;						 // text
    public $store_id;                        // int(11)  multiple_key unsigned
    public $user_id;                         // int(11)  multiple_key unsigned
    public $order_id;                        // int(11)  multiple_key unsigned
    public $is_read	;                        // int(1)  not_null
    public $is_deleted;                       // int(1)  not_null multiple_key unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Message',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
