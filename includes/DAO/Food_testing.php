<?php
/**
 * Table Definition for food_testing
 */
require_once 'DAO.inc';

class DAO_Food_testing extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'food_testing';                     // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $title;                   // int(11)  not_null unsigned
	public $is_closed;                      // int(1)  not_null
    public $survey_start;                       // datetime(11)  not_null
    public $survey_end;                  // datetime(11)  not_null
    public $file_id;							// int(11)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(10)  multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Food_testing',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>