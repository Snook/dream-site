<?php
/**
 * Table Definition for merchant_accounts
 */
require_once 'DAO.inc';

class DAO_Merchant_accounts extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'merchant_accounts';               // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $franchise_id;                    // int(8)  not_null multiple_key unsigned
    public $store_id;                        // int(8)  not_null unique_key unsigned
    public $ma_username;                     // string(50)  not_null
    public $ma_password;                     // string(128)  not_null
    public $partner_id;                      // string(255)  not_null
	public $ma_login_account;				 // string(255)  nullable
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Merchant_accounts',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
