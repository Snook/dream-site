<?php
/**
 * Table Definition for user_to_franchise
 */
require_once 'DAO.inc';

class DAO_User_to_franchise extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_to_franchise';                           // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $franchise_id;                    // int(8)  not_null multiple_key unsigned
    public $user_id;                         // int(11)  not_null multiple_key unsigned
    public $active;                          // int(1)  not_null
    public $owner_description;               // string(255)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_to_franchise',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
