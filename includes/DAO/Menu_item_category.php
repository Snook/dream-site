<?php
/**
 * Table Definition for menu_item_category
 */
require_once 'DAO.inc';

class DAO_Menu_item_category extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu_item_category';              // table name
    public $id;                              // int(3)  not_null primary_key unsigned auto_increment
    public $display_title;                   // string(45)  not_null
    public $category_type;                   // string(45)  not_null
    public $global_order_value;              // int(3)  not_null unsigned
    public $category_description;            // blob(65535)  blob
    public $is_active;                       // int(4)  not_null
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu_item_category',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
