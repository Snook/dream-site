<?php
/**
 * Table Definition for user_login_lockout
 */
require_once 'DAO.inc';

class DAO_User_login_lockout extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_login_lockout'; // table name
    public $id;                              // int(11)  not_null primary_key
    public $user_name;                       // string(64)  not_null
    public $time_of_lockout;				 // datetime
    public $ip_address;						 // string(40)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_login_lockout',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
