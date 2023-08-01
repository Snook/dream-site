<?php
/**
 * Table Definition for recipe
 */
require_once 'DAO.inc';

class DAO_Recipe extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'recipe';                          // table name
	public $id;                              // int(11)  not_null primary_key unsigned auto_increment
	public $recipe_id;                       // int(8)  not_null
	public $recipe_name;                     // varchar(255) NULL
	public $override_menu_id;                // int(11)  unsigned
	public $version;                         // int(3)
	public $flag_under_thirty;               // int(1)  not_null unsigned
	public $flag_heart_healthy;              // int(1)  not_null unsigned
	public $flag_grill_friendly;             // int(1)  not_null unsigned
	public $flag_cooks_from_frozen;          // int(1)  not_null unsigned
	public $flag_under_400;                  // int(1)  not_null unsigned
	public $flag_no_added_salt;              // int(1)  not_null unsigned
	public $flag_crockpot;                   // int(1)  not_null unsigned
	public $gluten_friendly;                    // int(1) not_null unsigned
	public $air_fryer;                    // int(1) not_null unsigned
	public $high_protein;                    // int(1) not_null unsigned
	public $vegetarian;                    // int(1) not_null unsigned
	public $kid_friendly;                    // int(1) not_null unsigned
	public $everyday_dinner;                 // int(1) not_null unsigned
	public $gourmet;                         // int(1) not_null unsigned
	public $flavor_profile;                  // varchar(255) NULL
	public $packaging;                       // varchar(255) NULL
	public $recipe_expert;                   // text NULL
	public $cooking_instruction_youtube_id;  // varchar(255) not_null
	public $show_nutritionals;               // int(1)  not_null unsigned
	public $show_cooking;                    // int(1)  not_null unsigned
	public $show_recipe_note;                // int(1)  not_null unsigned
	public $ltd_menu_item_value;             // int(8)  not_null unsigned
	public $cooking_method;                  // varchar(255)
	public $recipe_note;                     // blob(65535)  blob
	public $ingredients;                     // blob(65535)  blob
	public $allergens;                       // blob(65535)  blob
	public $menu_item_category_id;           // int
	public $timestamp_updated;				// timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;				// timestamp(19)  not_null unsigned zerofill binary
	public $created_by;						// int(11)  multiple_key unsigned
	public $updated_by;						// int(11)  multiple_key unsigned
	public $is_deleted;						// int(1)  not_null multiple_key

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Recipe', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}