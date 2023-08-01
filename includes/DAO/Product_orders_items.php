<?php
/**
 * Table Definition for product_orders_items
 */
require_once 'DAO.inc';

class DAO_Product_orders_items extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'product_orders_items';                          // table name
	public $id;                              // int(10)  not_null primary_key unsigned auto_increment
	public $product_orders_id;
	public $product_id;
	public $quantity;
	public $item_cost;
	public $product_membership_initial_menu;
	public $product_membership_hard_skip_menu;
	public $ejection_menu_id;
	public $product_membership_status;
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $created_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(4)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_product_orders_items', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
