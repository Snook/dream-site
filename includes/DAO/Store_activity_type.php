<?php
/**
 * Table Definition for store_activity_type
 */
require_once 'DAO.inc';

class DAO_Store_activity_type extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_activity_type';    // table name
    public $id;                              // mediumint(8)  not_null primary_key unsigned auto_increment group_by
    public $type;                            // char(9)  not_null
    public $subtype;                         // varchar(255)  
    public $description;                     // varchar(255)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $created_by;                      // int(11)  unsigned group_by
    public $updated_by;                      // int(11)  unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
