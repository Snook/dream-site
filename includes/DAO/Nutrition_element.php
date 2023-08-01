<?php
/**
 * Table Definition for nutrition_element
 */
require_once 'DAO.inc';

class DAO_Nutrition_element extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'nutrition_element';               // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $label;                           // string(64)  not_null
    public $display_label;					 // string(64)
    public $description;                     // blob(65535)  blob
    public $measure_label;                   // string(32)
    public $daily_value;					 // int(4)
    public $do_display;						 // tiny int
    public $display_order;					 // tiny int
	public $parent_element;					 // tiny int
	public $sprintf;					 // tiny int
	public $indent;					 // tiny int

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Nutrition_element',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
