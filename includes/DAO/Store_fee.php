<?php
/**
 * Table Definition for store_fee
 */
require_once 'DAO.inc';

class DAO_Store_fee extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_fee';           // table name
    public $id;                              // mediumint(8)  not_null primary_key unsigned auto_increment group_by
    public $store_id;                        // mediumint(8)  not_null unsigned group_by
    public $type;                            // char(13)  
    public $name;                            // blob(65535)  blob
    public $description;                     // blob(65535)  blob
    public $units;                           // char(8)  
    public $cost;                            // decimal(9)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $created_by;                      // int(10)  unsigned group_by
    public $updated_by;                      // int(10)  unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
