<?php
/**
 * Table Definition for menu_item_inventory
 */
require_once 'DAO.inc';

class DAO_Menu_item_inventory_history extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu_item_inventory_history';             // table name
    public $id;                              // int(11)  not_null primary_key unsigned
    public $store_id;                        // int(11)  not_null unsigned
    public $recipe_id;                       // int(11)  not_null unsigned
    public $order_id;               		 // int(11)  not_null
    public $event_type;						 // enum
    public $delta;							 // int(11)
    public $IS_override_inventory;           // int(11)  not_null
    public $IS_number_sold;                  // int(11)  not_null
    public $IS_future_pickups;               // int(11)  not_null
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu_item_inventory_history',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
