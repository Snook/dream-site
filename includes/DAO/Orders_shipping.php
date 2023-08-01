<?php
/**
 * Table Definition for orders_shipping
 */
require_once 'DAO.inc';

class DAO_Orders_shipping extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'orders_shipping';     // table name
    public $id;                              // mediumint(8)  not_null primary_key unsigned auto_increment group_by
    public $order_id;                        // mediumint(11)  unsigned group_by
    public $status;                          // char(9)  not_null
    public $shipping_method;                 // int(11)  not_null group_by
    public $carrier_code;                    // char(5)  not_null
    public $distribution_center;             // mediumint(11)  unsigned group_by
    public $service_days;                    // tinyint(5)  unsigned group_by
    public $shipping_postal_code;            // varchar(10)  
    public $ship_date;                       // date(10)  
    public $requested_delivery_date;         // datetime(19)  
    public $actual_delivery_date;            // datetime(19)  
    public $weight;                          // decimal(9)  
    public $shipping_cost;                   // decimal(12)  
    public $shipping_tax;                    // decimal(12)  
    public $tracking_number;                 // varchar(35)  
    public $tracking_number_received;        // datetime(19)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $created_by;                      // int(11)  unsigned group_by
    public $updated_by;                      // int(11)  unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
