<?php
/**
 * Table Definition for session_weekly_template_item
 */
require_once 'DAO.inc';

class DAO_Session_weekly_template_item extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'session_weekly_template_item';    // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $session_weekly_template_id;      // int(8)  not_null multiple_key unsigned
    public $start_day;                       // string(3)  enum
    public $start_time;                      // time(8)  binary
	public $session_type;                    // string(9)  enum
	public $session_title;                 // varchar(255)
    public $duration_minutes;                // int(6)  not_null
    public $available_slots;                 // int(6)
    public $introductory_slots;              // int(6)  not_null
    public $session_notes;                   // blob(65535)  blob
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $close_interval_hours;            // int(11)  unsigned
    public $close_interval_type;             // string(12)  enum
	public $meal_customization_close_interval;            // int(11)  unsigned
	public $meal_customization_close_interval_type;             // string(12)  enum
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Session_weekly_template_item',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
