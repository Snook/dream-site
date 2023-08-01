<?php
/**
 * Table Definition for product_membership
 */
require_once 'DAO.inc';

class DAO_Product_membership extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'product_membership';                         // table name
	public $id;                              // int(8)  not_null primary_key unsigned auto_increment
	public $product_id;
	public $term_months;
	public $number_skips_allowed;
	public $discount_type;
	public $discount_var;
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(10)  multiple_key unsigned
	public $updated_by;                      // int(10)  multiple_key unsigned
	public $is_deleted;                      // int(4)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Product_membership', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
