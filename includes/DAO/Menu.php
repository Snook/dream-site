<?php
/**
 * Table Definition for menu
 */
require_once 'DAO.inc';

class DAO_Menu extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu';                            // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $menu_name;                       // string(255)
    public $menu_description;                // blob(65535)  blob
    public $admin_notes;                     // string(255)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $menu_start;                      // date(10)  not_null multiple_key binary
    public $is_active;                       // int(4)  
    public $global_menu_start_date;            // date(10)  binary
    public $global_menu_end_date;            // date(10)  binary
    public $display_as_coming_soon;          // int(3)  unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
