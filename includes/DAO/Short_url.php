<?php
/**
 * Table Definition for short_url
 */
require_once 'DAO.inc';

class DAO_Short_url extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'short_url';				// table name
	public $id;								// int(8)  not_null primary_key unsigned auto_increment
	public $store_id;						// int
	public $short_url;						// varchar
	public $timestamp_updated;				// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;				// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;						// int(11)  multiple_key unsigned
	public $updated_by;						// int(11)  multiple_key unsigned
	public $is_deleted;						// int(1)  not_null multiple_key

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Short_url',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}