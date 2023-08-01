<?php
/**
 * Table Definition for points_user_history
 */
require_once 'DAO.inc';

class DAO_Session_rsvp extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'session_rsvp';        // table name
	public $id;                              // int(11)  not_null primary_key unsigned auto_increment
	public $session_id;                      // int(11)  not_null unsigned
	public $user_id;                         // int(11)  not_null unsigned
	public $upgrade_booking_id;              // int(11)  null unsigned
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $created_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(4)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Session_rsvp', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}