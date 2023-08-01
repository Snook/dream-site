<?php
/**
 * Table Definition for coupon_to_store
 */
require_once 'DAO.inc';

class DAO_Coupon_to_store extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'coupon_to_store';                 // table name
	public $id;                              // int(11)  not_null primary_key unsigned auto_increment
	public $coupon_code_id;                  // int(11)  not_null unsigned
	public $store_id;                        // int(11)  not_null unsigned
	public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
	public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
	public $created_by;                      // int(10)  multiple_key unsigned
	public $updated_by;                      // int(10)  multiple_key unsigned
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Coupon_to_store',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
