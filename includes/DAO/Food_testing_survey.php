<?php
/**
 * Table Definition for food_testing_survey
 */
require_once 'DAO.inc';

class DAO_Food_testing_survey extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'food_testing_survey';					// table name
	public $id;											// int(11)  not_null primary_key unsigned auto_increment
	public $food_testing_id;							// int(11)  not_null unsigned
	public $store_id;									// int(11)  not_null
	public $is_closed;									// int(11)  not_null
	public $schematic_accurate;							// int(11)  not_null
	public $schematic_easy_to_understand;				// int(11)  not_null
	public $schematic_notes;							// text
	public $honeydew_accurate;							// int(11)  not_null
	public $honeydew_easy_to_understand;				// int(11)  not_null
	public $honeydew_notes;								// text
	public $recipe_assembly_card_accurate;				// int(11)  not_null
	public $recipe_assembly_card_easy_to_understand;	// int(11)  not_null
	public $selling_features_notes;						// text
	public $timestamp_paid;								// datetime(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_completed;						// datetime(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_updated;							// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;							// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;									// int(10)  multiple_key unsigned
	public $updated_by;									// int(10)  multiple_key unsigned
	public $is_deleted;									// int(1)  not_null

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Food_testing_survey',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
?>