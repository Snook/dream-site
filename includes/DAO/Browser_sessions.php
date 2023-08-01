<?php
/**
 * Table Definition for browser_sessions
 */
require_once 'DAO.inc';

class DAO_Browser_sessions extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'browser_sessions';                // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $browser_session_key;             // string(255)  not_null multiple_key
    public $user_id;                         // int(11)  multiple_key unsigned
    public $current_store_id;                // int(9)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Browser_sessions',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
