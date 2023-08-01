<?php
/**
 * Table Definition for store_credit
 */
require_once 'DAO.inc';

class DAO_Store_credit extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_credit';                    // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $user_id;                         // int(10)  not_null multiple_key unsigned
    public $amount;                          // real(8)  not_null
    public $credit_card_number;              // string(4)  
    public $payment_transaction_number;      // string(50)  
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $is_redeemed;                     // int(1)  
    public $credit_type;                     // int(3)  not_null unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $date_original_credit;            // datetime(19)  binary
    public $original_credit_id;              // int(11)  unsigned
    public $description;				     // string(255)
    public $ip_address;						 // string(16)
    public $was_sent_60_day_warning;		// int(1)  not_null
    public $date_deleted;                    // datetime(19)  binary
    public $deleted_by;                      // int(11)  unsigned
    public $is_expired;                      // int(1)  not_null
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_credit',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
