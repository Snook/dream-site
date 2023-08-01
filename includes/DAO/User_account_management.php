<?php
/**
 * Table Definition for user_account_management
 */
require_once 'DAO.inc';

class DAO_User_account_management extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_account_management';    // table name
    public $id;                              // mediumint(8)  not_null primary_key unsigned auto_increment group_by
    public $user_id;                         // mediumint(11)  not_null group_by
    public $action;                          // char(24)  not_null
    public $status;                          // char(10)  not_null
    public $data;                            // blob(65535)  blob
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $created_by;                      // int(11)  unsigned group_by
    public $updated_by;                      // int(11)  unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_account_management',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
