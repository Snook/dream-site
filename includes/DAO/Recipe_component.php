<?php
/**
 * Table Definition for recipe_component
 */
require_once 'DAO.inc';

class DAO_Recipe_component extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'recipe_component';                // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $recipe_id;                       // int(11)  not_null unsigned
    public $recipe_number;                       // int(11)  not_null unsigned
    public $component_number;                // int(4)  not_null unsigned
    public $serving;                         // string(64)
	public $serving_weight;                // decimal(12,3)
    public $notes;                           // blob(65535)  blob
	public $timestamp_updated;				// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;				// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;						// int(11)  multiple_key unsigned
	public $updated_by;						// int(11)  multiple_key unsigned
	public $is_deleted;						// int(1)  not_null multiple_key

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Recipe_component',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}