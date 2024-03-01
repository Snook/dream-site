<?php
/**
 * Table Definition for default_prices
 */
require_once 'DAO.inc';

class DAO_Default_prices extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'default_prices';                  // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(11)  not_null unsigned
    public $recipe_id;                       // int(11)  not_null unsigned
    public $default_price;                   // real(8)  not_null
    public $default_customer_visibility;     // int(4)  not_null
    public $default_hide_completely;         // int(4)  not_null
	public $show_on_order_form;              // int(4)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Default_prices',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}