<?php
/**
 * Table Definition for user_data_field
 */
require_once 'DAO.inc';

class DAO_User_data_field extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_data_field';                 // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $label;                           // string(80)  not_null
    public $discription;                     // string(255)  

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_data_field',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
