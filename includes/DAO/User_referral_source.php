<?php
/**
 * Table Definition for user_referral_source
 */
require_once 'DAO.inc';

class DAO_User_referral_source extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_referral_source';            // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(10)  not_null multiple_key unsigned
    public $source;                          // string(13)  not_null enum
    public $meta;                            // string(255)  
    public $is_deleted;                      // int(3)  not_null unsigned
    public $inviting_user_id;                // int(10)  multiple_key unsigned
    public $customer_referral_id;			// int (11)
    public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
    public $created_by;                      // int(11)  unsigned
    public $updated_by;                      // int(11)  unsigned
    


    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_referral_source',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
