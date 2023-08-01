<?php
/**
 * Table Definition for order_minimum
 */
require_once 'DAO.inc';

class DAO_Order_minimum extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'order_minimum';       // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $store_id;                        // mediumint(8)  unsigned group_by
    public $menu_id;                         // mediumint(11)  group_by
    public $order_type;                      // char(8)  not_null
    public $minimum_type;                    // char(7)  not_null
    public $minimum;                         // int(5)  group_by
	public $allows_additional_ordering;      // tinyint(1)  not_null group_by
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $created_by;                      // int(11)  unsigned group_by
    public $updated_by;                      // int(11)  unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by


	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Order_minimum',$k,$v); }
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
