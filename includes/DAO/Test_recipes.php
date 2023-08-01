<?php
/**
 * Table Definition for store_expenses
 */
require_once 'DAO.inc';

class DAO_Test_recipes extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'test_recipes';                  // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $name;                      			// string(255)  enum
    public $link;                   			 // string(255)  enum
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Test_recipes',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
