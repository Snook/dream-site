<?php
/**
 * Table Definition for store_to_api
 */
require_once 'DAO.inc';

class DAO_Store_to_api extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'store_to_api';                       // table name
	public $id;                              // int(8)  not_null primary_key unsigned auto_increment
	public $store_id;                  // string(64)  not_null multiple_key
	public $api;
	public $endpoint;
	public $key;
	public $secret;
	public $api_storeId;
	public $timestamp_updated;				// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;				// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;						// int(11)  multiple_key unsigned
	public $updated_by;						// int(11)  multiple_key unsigned
	public $is_deleted;						// int(1)  not_null multiple_key

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Store_to_api', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
?>