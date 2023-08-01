<?php
/**
 * Table Definition for product_payment
 */
require_once 'DAO.inc';

class DAO_Product_payment extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'product_payment';                         // table name
	public $id;                              // int(11)  not_null primary_key unsigned auto_increment
	public $store_id;						// med int (11)
	public $user_id;						// int(11)
	public $product_orders_id;
	public $payment_type;
	public $user_card_reference_id;
	public $credit_card_type;
	public $card_number;
	public $referent_id;
	public $total_amount;
	public $merchant_account_id;
	public $payment_transaction_id;
	public $payment_system;
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(11)  multiple_key unsigned
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Product_payment', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}