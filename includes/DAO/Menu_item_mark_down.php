<?php
/**
 * Table Definition for mark_up_multi
 */
require_once 'DAO.inc';

class DAO_Menu_item_mark_down extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu_item_mark_down';                   // table name
    public $id;                              // int(8)  not_null primary_key auto_increment
    public $store_id;                        // int(8)  not_null
    public $menu_item_id;          			 // int(8)  
    public $markdown_value;         		 // real(6)  
    public $markup_value_sides;          	 // real(6)    
    public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
    public $updated_by;                      // int(11)  
    public $created_by;                      // int(11)  
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu_item_mark_down',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
