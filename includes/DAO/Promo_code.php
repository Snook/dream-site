<?php
/**
 * Table Definition for promo_code
 */
require_once 'DAO.inc';

class DAO_Promo_code extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'promo_code';                      // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $promo_title;                     // string(255)  
    public $promo_description;               // string(255)  
    public $promo_code;                      // string(64)  unique_key
    public $promo_type;                      // string(7)  not_null enum
    public $promo_menu_item_id;              // int(8)  multiple_key unsigned
    public $promo_product_item_id;           // int(8)  multiple_key unsigned
    public $promo_var;                       // real(8)  
    public $promo_code_active;               // int(1)  
    public $note;                            // string(255)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $promo_code_start;                // datetime(19)  not_null binary
    public $promo_code_expiration;           // datetime(19)  binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Promo_code',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
