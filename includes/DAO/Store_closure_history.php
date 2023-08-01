<?php
/**
 * Table Definition for store_closure_history
 */
require_once 'DAO.inc';

class DAO_Store_closure_history extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_closure_history';           // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $store_closure_date;              // datetime(19)  not_null binary
    public $info_recorded_by;                // int(10)  not_null multiple_key unsigned
    public $recorded_grand_opening_date;     // datetime(19)  not_null binary
    public $recorded_home_office_id;         // string(10)  not_null
    public $details;                         // blob(65535)  blob

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_closure_history',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
