<?php
/**
 * Table Definition for session_to_menu_item
 */
require_once 'DAO.inc';

class DAO_Session_to_menu_item extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'session_to_menu_item';            // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $session_id;                      // int(11)  not_null unsigned
    public $menu_item_id;                    // int(11)  unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Session_to_menu_item',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
