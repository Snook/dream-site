<?php
/**
 * Table Definition for payment
 */
require_once 'DAO.inc';

class DAO_Payment extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'payment';                         // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  not_null multiple_key unsigned
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $order_id;                        // int(11)  not_null multiple_key unsigned
    public $payment_type;                    // string(19)  enum
    public $gift_certificate_number;         // string(100)
    public $gift_cert_type;                  // string(8)  enum
    public $credit_card_type;                // string(16)  enum
    public $payment_number;                  // string(255)
    public $referent_id;                     // int(8)
    public $do_not_reference;                // int(1) not null
    public $payment_note;                    // blob(65535)  blob
    public $admin_note;                      // blob(65535)  blob
    public $total_amount;                    // real(8)  not_null
    public $is_delayed_payment;              // int(4)  not_null
    public $payment_transaction_number;      // string(50)
    public $delayed_payment_status;          // string(9)  enum
    public $delayed_payment_transaction_date;    // datetime(19)  binary
    public $delayed_payment_transaction_number;    // string(50)
    public $payment_system;                 // enum
    public $merchant_account_id;            // int(11)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null
    public $is_migrated;                     // int(4)
    public $is_deposit;                      // int(1)
	public $store_credit_id;                 // int(11)  not_null multiple_key unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Payment',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
