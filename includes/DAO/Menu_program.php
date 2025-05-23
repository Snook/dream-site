<?php
/**
 * Table Definition for menu program
 */
require_once 'DAO.inc';

class DAO_Menu_program extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu_program';                    // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $title;                           // string(48)  not_null
    public $description;                     // string(256)  

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu_program',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
