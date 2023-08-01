<?php
/**
 * Table Definition for menu_item_inventory
 */
require_once 'DAO.inc';

class DAO_Menu_item_inventory extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu_item_inventory';             // table name
    public $id;                              // int(11)  not_null primary_key unsigned
    public $store_id;                        // int(11)  not_null unsigned
    public $menu_id;                         // int(11)  not_null
    public $recipe_id;                       // int(11)  not_null unsigned
    public $initial_inventory;               // int(11)  not_null
    public $override_inventory;              // int(11)  not_null
    public $number_sold;                     // int(11)  not_null
    public $sales_mix;                      // real
    public $week1_projection;               // int(11)
    public $week2_projection;                // int(11)
    public $week3_projection;               // int(11)
    public $week4_projection;               // int(11)
    public $week5_projection;               // int(11)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu_item_inventory',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
