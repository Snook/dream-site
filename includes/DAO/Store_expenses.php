<?php
/**
 * Table Definition for store_expenses
 */
require_once 'DAO.inc';

class DAO_Store_expenses extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_expenses';                  // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $entry_date;                      // date(10)  multiple_key binary
    public $entry_type;                      // string(6)  enum
    public $expense_type;                    // string(23)  enum
    public $notes;                           // blob(65535)  blob
    public $units;                           // real(8)  not_null
    public $total_cost;                      // real(9)  not_null
    public $session_id;						 // int(11)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_expenses',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
