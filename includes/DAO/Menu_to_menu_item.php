<?php
/**
 * Table Definition for menu_to_menu_item
 */
require_once 'DAO.inc';

class DAO_Menu_to_menu_item extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu_to_menu_item';               // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $menu_id;                         // int(8)  not_null multiple_key unsigned
    public $menu_item_id;                    // int(8)  not_null multiple_key unsigned
    public $menu_order_value;                // int(4)
    public $featuredItem;                    // int(4)  not_null
    public $override_price;                  // real(8)
    public $is_visible;                      // int(4)
    public $is_hidden_everywhere;            // int(4)
    public $show_on_pick_sheet;
    public $show_on_order_form;		// int(4)
    public $store_id;                        // int(8)  multiple_key unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu_to_menu_item',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
