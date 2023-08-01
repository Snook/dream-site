<?php
/**
 * Table Definition for timezones
 */
require_once 'DAO.inc';

class DAO_Timezones extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'timezones';                       // table name
    public $id;                              // int(6)  not_null primary_key auto_increment
    public $tz_name;                         // string(64)  not_null unique_key

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Timezones',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
