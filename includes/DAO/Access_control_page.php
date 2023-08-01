<?php
/**
 * Table Definition for access_control_page
 */
require_once 'DAO.inc';

class DAO_Access_control_page extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'access_control_page';             // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $page_name;                       // string(80)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(10)  not_null multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Access_control_page',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
