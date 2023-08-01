<?php
/**
 * Table Definition for fundraiser
 */
require_once 'DAO.inc';

class DAO_Fundraiser extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'fundraiser';                       // table name
	public $id;                              // int(8)  not_null primary_key unsigned auto_increment
	public $fundraiser_name;                  // varchar(255) not_null
	public $fundraiser_description;                          // text
	public $donation_value;           // int(11)
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(10)  multiple_key unsigned
	public $updated_by;                      // int(10)  multiple_key unsigned
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Fundraiser', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
?>