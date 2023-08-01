<?php
/**
 * Table Definition for revenue_event
 */
require_once 'DAO.inc';

class DAO_Revenue_event extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'revenue_event';       // table name
    public $id;                              // int(8)  not_null primary_key auto_increment
    public $event_type;                      // string(11)  not_null enum
    public $event_time;                      // datetime(19)  not_null binary
    public $store_id;                        // int(8)  not_null
    public $menu_id;                         // int(8)  not_null
    public $amount;                          // real(12)  not_null
    public $session_amount;                  // real(12)  not_null
    public $session_id;                      // int(11)  not_null unsigned
    public $final_session_id;				 // int(11)  not_null unsigned
    public $order_id;                        // int(11)  not_null unsigned
	public $membership_id;						// int
    public $positive_affected_month;         // date(10)  binary
    public $negative_affected_month;         // date(10)  binary
    public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
    public $updated_by;                      // int(11)  
    public $created_by;                      // int(11)  
    public $is_deleted;                      // int(4)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
