<?php
/**
 * Table Definition for product_orders
 */
require_once 'DAO.inc';

class DAO_Product_orders extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'product_orders';                          // table name
	public $id;                              // int(10)  not_null primary_key unsigned auto_increment
	public $user_id;                         // int(10)  not_null multiple_key unsigned
	public $store_id;                        // int(8)  not_null multiple_key unsigned
	public $grand_total;

	public $subtotal_all_items;              // real(8)  not_null
	public $subtotal_products;               // real(8)
	public $sales_tax_id;                    // int(8)  multiple_key unsigned
	public $subtotal_all_taxes;              // real(8)  not_null
	public $subtotal_sales_taxes;            // real(8)
	public $order_admin_notes;               // blob(65535)  blob
	public $order_confirmation;              // string(20)
	public $coupon_code_id;                  // int(8)  multiple_key unsigned
	public $coupon_code_discount_total;      // real(8)

	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $created_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(4)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_product_orders', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
