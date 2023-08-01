<?php
/**
 * Table Definition for password_history
 */
require_once 'DAO.inc';

class DAO_Password_history extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'password_history';    // table name
    public $id;                              // int(11)  not_null primary_key
    public $user_id;                        // int(11)
    public $password;						// string(72)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Password_history',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
