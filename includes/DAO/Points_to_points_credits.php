<?php
/**
 * Table Definition for points_to_points_credits
 */
require_once 'DAO.inc';

class DAO_Points_to_points_credits extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'points_to_points_credits';        // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $points_user_history_id;           // int(11)  not_null unsigned
    public $points_credit_id;                // int(11)  not_null unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Points_to_points_credits',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
