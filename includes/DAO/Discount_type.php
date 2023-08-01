<?php
/**
 * Table Definition for discount_type
 */
require_once 'DAO.inc';

class DAO_Discount_type extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'discount_type';                   // table name
    public $id;                              // int(4)  not_null primary_key unsigned auto_increment
    public $type;                            // string(80)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Discount_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
