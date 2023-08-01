<?php
/**
 * Table Definition for sales_tax
 */
require_once 'DAO.inc';

class DAO_Sales_tax extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'sales_tax';                       // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $postal_code;                     // string(10)  multiple_key
    public $usps_adc;                        // string(4)  
    public $state_tax;                       // real(7)  
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $county_tax;                      // real(7)  
    public $city_tax;                        // real(7)  
    public $total_tax;                       // real(7)  
    public $mta_tax;                         // real(7)  
    public $spd_tax;                         // real(7)  
    public $other1_tax;                      // real(7)  
    public $other2_tax;                      // real(7)  
    public $other3_tax;                      // real(7)  
    public $other4_tax;                      // real(7)  
    public $food_tax;                        // real(7)  
    public $fadmin_override;                 // int(4)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $sales_tax_start;                 // datetime(19)  binary
    public $sales_tax_expiration;            // datetime(19)  binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Sales_tax',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
