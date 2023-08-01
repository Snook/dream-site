<?php
/**
 * Table Definition for access_control_page_user
 */
require_once 'DAO.inc';

class DAO_Access_control_page_user extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'access_control_page_user';        // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(8)  not_null multiple_key unsigned
    public $access_control_page_id;          // int(5)  not_null multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Access_control_page_user',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
