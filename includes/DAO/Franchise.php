<?php
/**
 * Table Definition for franchise
 */
require_once 'DAO.inc';

class DAO_Franchise extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'franchise';                       // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $franchise_name;                  // string(64)  not_null multiple_key
    public $active;                          // int(1)  not_null
    public $franchise_description;           // blob(65535)  blob
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(10)  multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Franchise',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
