<?php
/**
 * Table Definition for user_login
 */
require_once 'DAO.inc';

class DAO_User_login extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_login';                      // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  not_null multiple_key unsigned
    public $ul_password;                     // string(41)  not_null
    public $ul_password2;                     // string(72)  not_null
    public $ul_username;                     // string(64)  not_null multiple_key
    public $ul_verified;                     // string(7)  not_null enum
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $uses_bcrypt;		 			// int(1)  not_null
    public $last_password_update;			// datetime
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_login',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
