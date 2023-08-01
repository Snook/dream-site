<?php
/**
 * Table Definition for user_preferred
 */
require_once 'DAO.inc';

class DAO_User_preferred extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_preferred';                  // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  not_null multiple_key unsigned
    public $store_id;                        // int(8)  multiple_key unsigned
    public $all_stores;                      // int(4)  not_null
    public $preferred_type;                  // string(7)  not_null enum
    public $preferred_value;                 // real(8)  not_null
	public $preferred_cap_type;              // string(7)  not_null enum
	public $preferred_cap_value;             // real(8)  not_null
    public $exclude_from_reports;			// int(4)  not_null	
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $user_preferred_start;            // datetime(19)  binary
    public $user_preferred_expiration;       // datetime(19)  binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_preferred',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
