<?php
/**
 * Table Definition for store_credit
 */
require_once 'DAO.inc';

class DAO_Store_class_cache extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_class_cache';                    // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null  unsigned
    public $class;                           // int(3)  not_null  unsigned
    public $class_v2;                        // int(3)  not_null  unsigned
    
    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_credit',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}