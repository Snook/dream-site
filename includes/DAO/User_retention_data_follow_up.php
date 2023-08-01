<?php
/**
 * Table Definition for user_retention_data_follow_up
 */
require_once 'DAO.inc';

class DAO_User_retention_data_follow_up extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_retention_data_follow_up';    // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $user_retention_data_id;          // int(10)  not_null multiple_key unsigned
    public $follow_up_date;                  // date(10)  not_null binary
    public $user_retention_action_type_id;    // int(3)  not_null multiple_key unsigned
    public $leading_report_identifier;       // int(4)  not_null
    public $results_date;                    // date(10)  binary
    public $results_comments;                // blob(65535)  blob
    public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_retention_data_follow_up',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
