<?php
/**
 * Table Definition for coach
 */
require_once 'DAO.inc';

class DAO_Coach extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'coach';                           // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(10)  not_null multiple_key unsigned
    public $active;                          // int(4)  not_null
    public $administrator;                   // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Coach',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
