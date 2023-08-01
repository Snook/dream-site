<?php
/**
 * Table Definition for product
 */
require_once 'DAO.inc';

class DAO_Product extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'product';                         // table name
	public $id;                              // int(8)  not_null primary_key unsigned auto_increment
	public $product_sku;                     // string(12)
	public $product_title;                   // string(128)
	public $tax_category;
	public $price;                           // real(6)
	public $item_type;
	public $isNonFoodItem;                   // int(6)  not_null
	public $product_description;             // blob(65535)  blob
	public $product_image;                   // string(64)
	public $admin_notes;                     // string(255)
	public $img_url;                         // string(255)
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(10)  multiple_key unsigned
	public $updated_by;                      // int(10)  multiple_key unsigned
	public $is_deleted;                      // int(4)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Product', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
