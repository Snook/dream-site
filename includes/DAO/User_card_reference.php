<?php
/**
 * Table Definition for user_card_reference
 */
require_once 'DAO.inc';

class DAO_User_card_reference extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'user_card_reference';                         // table name
	public $id;                              // int(11)  not_null primary_key unsigned auto_increment
	public $user_id;
	public $store_id;
	public $merchant_account_id;
	public $credit_card_type;
	public $card_number;
	public $card_transaction_number;
	public $last_process_date;
	public $last_process_result;
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(11)  multiple_key unsigned
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_User_card_reference', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}

