<?php
/**
 * Table Definition for customer_referral
 */
require_once 'DAO.inc';

class DAO_Customer_referral extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'customer_referral';               // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $referring_user_id;               // int(11)  not_null multiple_key unsigned
    public $referred_user_id;                // int(11)  multiple_key unsigned
    public $referred_user_email;             // string(128)
    public $inviting_user_name;              // string(128)
    public $origination_type_code;           // int(11)  not_null
    public $origination_uid;                 // string(32)
    public $referrer_session_id;             // int(11)
    public $referral_status;                 // int(11)  not_null
    public $amount_credited;                 // real(8)  not_null
    public $first_order_id;					 // int(11)
    public $sequence_timestamp;				 // timestamp(19)
    public $referrers_order_is_sampler;		 // int(1)
    public $store_credit_id;				 // int(11)
    public $plate_points_reward_id;			 // int(11)
    public $session_properties_id;			 // int(11)
    public $referred_user_name;				 // STRING(128)
    public $admin_notes;					 // string(256)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Customer_referral',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
