<?php
/**
 * Table Definition for bundle
 */
require_once 'DAO.inc';

class DAO_Bundle extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'bundle';                          // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $bundle_type;					 // enum
    public $bundle_name;                     // string(255)  not_null
    public $menu_id;                         // int(11)  not_null
    public $master_menu_item;				 // int(11)
    public $number_items_required;			 // tinyint(1)
    public $number_servings_required;		 // tinyint(1)
    public $menu_item_description;           // blob(65535)  blob
    public $price;                           // real(6)  not_null
	public $price_shipping;					// real (6) not_null
    public $start_date;                      // datetime(19)  binary
    public $end_date;                        // datetime(19)  binary
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Bundle',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
