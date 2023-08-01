<?php
/**
 * Table Definition for performance_royalty_override
 */
require_once 'DAO.inc';

class DAO_Performance_royalty_override extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'performance_royalty_override';    // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $performance_start_date;          // datetime(19)  not_null binary
    public $performance_end_date;            // datetime(19)  not_null binary
    public $performance_entered_by;          // int(10)  not_null multiple_key unsigned
    public $performance_date_entered;        // datetime(19)  not_null binary
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Performance_royalty_override',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
