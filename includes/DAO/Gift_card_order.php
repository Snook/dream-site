<?php
/**
 * Table Definition for booking
 */
require_once 'DAO.inc';

class DAO_Gift_card_order extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'gift_card_order';                 // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $media_type;						 // string not null enum
    public $first_name;                      // string(15)  
    public $last_name;                       // string(20)  
    public $shipping_address_1;              // string(30)  
    public $shipping_address_2;              // string(30)  
    public $shipping_state;                  // string(2)  
    public $shipping_zip;                    // int(11)  
    public $purchase_date;                   // datetime(19)  binary
    public $initial_amount;                  // real(9)  
    public $transaction_ui;                  // string(17)  not_null enum
    public $s_and_h_amount;                  // real(9)  not_null
    public $email;                           // string(30)  
    public $payment_card_number;             // string(20)  
    public $payment_card_type;               // string(20)  
    public $cc_ref_number;                   // string(20)  
    public $design_type_id;                  // int(3)  not_null
    public $from_name;                       // string(80)  
    public $to_name;                       // string(80)      
    public $message_text;                    // blob(65535)  blob
    public $recipient_email_address;         // string(80)  
    public $shipping_city;                   // string(20)  
    public $processed;                       // int(2)  
    public $paid;                            // int(4)  
    public $is_deleted;                      // int(4)  
    public $store_id;                        // int(11)  unsigned
    public $order_id;                        // int(11)  unsigned
    public $user_id;                         // int(11)  unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $gift_card_account_number;        // string(128)  binary
    public $clear_card_number;               // string(20) 
    public $billing_name;                    // string(80)  
    public $billing_address;                 // string(128)  
    public $billing_zip;                     // string(5)  
    public $ip_address;                      // string(20) 
    public $order_confirm_id;				 // string(20)
    public $last_resend_time;                // datetime(19)  binary
    
    
    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Gift_card_order',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
