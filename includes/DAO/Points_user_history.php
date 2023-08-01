<?php
/**
 * Table Definition for points_user_history
 */
require_once 'DAO.inc';

class DAO_Points_user_history extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'points_user_history';             // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  not_null unsigned
    public $event_type;                      // string(21)  not_null enum
    public $order_id;                        // int(11)  not_null
    public $points_allocated;                // decimal
    public $points_converted;                // decimal
    public $total_points;                    // decimal
    public $json_meta;                        // blob(65535)  blob
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $created_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Points_user_history',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
