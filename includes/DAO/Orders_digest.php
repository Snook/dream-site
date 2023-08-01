<?php
/**
 * Table Definition for orders
 */
require_once 'DAO.inc';

class DAO_Orders_digest extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'orders_digest';                   // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $order_id;                        // int(11)  not_null unsigned
    public $user_id;                        // int(11)  not_null unsigned
    public $store_id;                        // int(11)  not_null unsigned
    public $agr_total;                       // real(8)  not_null
    public $addon_total;                     // real(8)  not_null
    public $balance_due;					// real(8)  not_null
    public $in_store_trigger_order;          // int(11)  unsigned
	public $qualifying_order_id;          // int(11)  unsigned
    public $original_order_time;             // datetime(19)  not_null binary
	public $session_id;						// int(11)
    public $session_time;                    // datetime(19)  not_null binary
    public $order_type;                      // string(7)  not_null enum
    public $session_type;                    // string(12)  not_null enum
    public $user_state;                      // string(12)  not_null enum
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Orders_digest',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
