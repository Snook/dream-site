<?php
/**
 * Table Definition for points_credits
 */
require_once 'DAO.inc';

class DAO_Points_credits extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'points_credits';                  // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  not_null unsigned
    public $dollar_value;                    // real(8)  not_null
    public $credit_state;					// enum
    public $order_id;				 		// int(11) unsigned
    public $parent_of_partial;		 		// int(11) unsigned
    public $original_amount;				// real(8)
    public $expiration_date;				// datetime
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Points_credits',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
