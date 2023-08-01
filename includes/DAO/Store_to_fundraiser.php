<?php
/**
 * Table Definition for store_to_fundraiser
 */
require_once 'DAO.inc';

class DAO_Store_to_fundraiser extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'store_to_fundraiser';                       // table name
	public $id;                              // int(8)  not_null primary_key unsigned auto_increment
	public $store_id;                  // string(64)  not_null multiple_key
	public $charity_id;                          // int(1)  not_null
	public $active;                      // int(1)  not_null
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Store_to_fundraiser', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
?>