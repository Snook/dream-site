<?php
/**
 * Table Definition for booking
 */
require_once 'DAO.inc';

class DAO_Gift_card_transaction extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'gift_card_transaction';           // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $transaction_type;                // int(11)  
    public $transaction_response;            // string(10)  
    public $transaction_date;                // datetime(19)  binary
    public $transaction_amount;              // real(9)  
    public $pos_type;                        // string(3)  
    public $auth_ref_number;                 // int(11)  
    public $gift_card_number;                // string(15)  
    public $clear_card_number;               // string(20)
    public $cc_type;                		 // string(32)
    public $cc_number;		                 // string(16)
    public $billing_email;		             // string(255)
    public $cc_ref_number;                   // string(20)  
    public $store_id;                        // int(11)  unsigned
    public $order_id;                        // int(11)  unsigned
    public $transaction_id;                  // string(50)  
    public $additional_info;				 // text
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Gift_card_transaction',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
