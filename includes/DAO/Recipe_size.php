<?php
/**
 * Table Definition for recipe_size
 */
require_once 'DAO.inc';

class DAO_Recipe_size extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'recipe_size';	// table name
    public $id;							// int(11) NOT NULL AUTO_INCREMEN
    public $recipe_id;					// int(11) NOT NULL
    public $recipe_size;				// enum('LARGE','MEDIUM') DEFAULT NULL
    public $menu_id;					// int(11) DEFAULT NULL
    public $serving_size_combined;		// varchar(255) DEFAULT NULL
    public $servings_per_container;		// int(11) DEFAULT NULL
    public $weight;						// decimal(11,0) DEFAULT NULL
    public $upc;						// varchar(24) DEFAULT NULL
    public $cooking_time;				// varchar(48) DEFAULT NUL
    public $cooking_instructions;		// text DEFAULT NUL
    public $timestamp_updated;          // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;          // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                 // int(11)  multiple_key unsigned
    public $updated_by;                 // int(11)  multiple_key unsigned
    public $is_deleted;					// int(1) NOT NULL DEFAULT '0'

    /* Static get*/
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Recipe_size',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
