<?php
/**
 * Table Definition for user
 */
require_once 'DAO.inc';

class DAO_User extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user';                // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $user_type;                       // string(19)  not_null multiple_key enum
    public $is_partial_account;				 // int(4)  not_null unsigned
    public $visit_count;                     // int(11)
	public $primary_email;                   // string(255)  multiple_key
	public $secondary_email;                   // string(255)  multiple_key
    public $firstname;                       // string(80)  not_null
    public $lastname;                        // string(80)  not_null multiple_key
	public $home_store_id;                   // int(8)  multiple_key unsigned
	public $distribution_center_id;                   // int(8)  multiple_key unsigned
    public $dream_reward_status;             // int(4)  not_null unsigned
    public $dream_reward_level;              // int(4)  not_null unsigned
    public $dream_rewards_version;  		 // int(4)  not_null unsigned
    public $dr_downgraded_order_count;  	 // int(4)  not_null unsigned
    public $has_opted_out_of_plate_points;	// int (1)
    public $gender;                          // string(1)  enum
    public $telephone_1;                     // string(32)
    public $telephone_1_type;                // string(?)
    public $telephone_1_call_time;           // string(9)  enum
    public $telephone_2;                     // string(32)
    public $telephone_2_type;                // string(?)
    public $telephone_2_call_time;           // string(9)  enum
    public $fax;                             // string(32)
    public $admin_note;                      // string(255)
    public $fadmin_nda_agree;                // int(1)
    public $facebook_id;					// int(11)
    public $facebook_oauth_token;			// text
    public $facebook_last_login;			// datetime(19)  binary
    public $facebook_validate_error;		// int(11)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $last_login;                      // datetime(19)  binary
    public $created_by;                      // int(10)  multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null
	public $is_ccpa_deleted;                 // tinyint(1)  not_null group_by
    public $is_migrated;                     // int(4)
    public $telephone_day;                   // string(32)
    public $telephone_day_type;              // string(?)
    public $telephone_evening;               // string(23)
    public $call_time;                       // string(9)  enum

    /* Static get */
    function staticGet($class, $k,$v=NULL) { return DAO::staticGet('DAO_User',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}