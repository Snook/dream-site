<?php
/**
 * Table Definition for door_dash_orders_and_payouts
 */
require_once 'DAO.inc';

class DAO_Door_dash_orders_and_payouts extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'door_dash_orders_and_payouts';    // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment group_by
    public $store_id;                        // mediumint(11)  unsigned group_by
    public $timestamp_UTC_time;              // time(10)  
    public $timestamp_UTC_date;              // date(10)  
    public $timestamp_local_time;            // time(10)  
    public $timestamp_local_date;            // date(10)  
    public $payout_time;                     // varchar(32)  
    public $payout_date;                     // date(10)  
    public $door_dash_store_id;             // varchar(12)
    public $business_id;                     // varchar(12)  
    public $store_name;                      // varchar(128)  
    public $merchant_store_id;               // varchar(12)  
    public $transaction_type;                // varchar(32)  
    public $transaction_id;                  // varchar(128)  
    public $doordash_order_id;               // varchar(32)  
    public $merchant_delivery_id;            // varchar(32)  
    public $external_id;                     // varchar(32)  
    public $description;                     // blob(65535)  blob
    public $final_order_status;              // varchar(64)  
    public $currency;                        // varchar(6)  
    public $subtotal;                        // decimal(10)  
    public $tax_subtotal;                    // decimal(10)  
    public $commission;                      // decimal(10)  
    public $commission_tax_amount;           // decimal(10)  
    public $marketing_fees;                  // decimal(10)  
    public $credit;                          // decimal(10)  
    public $debit;                           // decimal(10)  
    public $doordash_transaction_id;         // varchar(32)  
    public $payout_id;                       // varchar(32)  
    public $drive_charge;                    // decimal(10)  
    public $tax_remitted_by_doordash_to_state;    // decimal(10)  
    public $subtotal_for_tax;                // decimal(10)  
    public $doordash_funded_subtotal_discount_amount;    // decimal(10)  
    public $merchant_funded_subtotal_discount_amount;    // decimal(10)  

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
