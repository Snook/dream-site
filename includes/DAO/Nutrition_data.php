<?php
/**
 * Table Definition for nutrition_data
 */
require_once 'DAO.inc';

class DAO_Nutrition_data extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'nutrition_data';                  // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $component_id;                    // int(11)  not_null unsigned
    public $component_number;                // int(11)  not_null unsigned
    public $nutrition_element;               // int(8)  not_null unsigned
    public $value;                           // real(14)  not_null
    public $note_indicator;					 // string(6)
	public $prefix;							// string(6)
	public $timestamp_updated;				// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;				// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;						// int(11)  multiple_key unsigned
	public $updated_by;						// int(11)  multiple_key unsigned
	public $is_deleted;						// int(1)  not_null multiple_key

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Nutrition_data',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
