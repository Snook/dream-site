<?php
/**
 * Table Definition for store_menu_item_exclusion
 */
require_once 'DAO.inc';

class DAO_Store_menu_item_exclusion extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_menu_item_exclusion';       // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $menu_to_menu_item_id;            // int(11)  not_null multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_menu_item_exclusion',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
