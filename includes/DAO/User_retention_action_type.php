<?php
/**
 * Table Definition for user_retention_action_type
 */
require_once 'DAO.inc';

class DAO_User_retention_action_type extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_retention_action_type';      // table name
    public $id;                              // int(3)  not_null primary_key unsigned auto_increment
    public $Action_type;                     // string(80)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $is_active;                       // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_retention_action_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
