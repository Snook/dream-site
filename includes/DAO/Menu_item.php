<?php
/**
 * Table Definition for menu_item
 */
require_once 'DAO.inc';

class DAO_Menu_item extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'menu_item';                       // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $menu_item_name;                  // string(255)  not_null
    public $menu_item_description;           // blob(65535)  blob
    public $menu_label;						//string(64)
    public $subcategory_label;				//string(48)
    public $menu_image_override;			// int(4) not null
    public $admin_notes;                     // string(255)
    public $food_cost;						 // real(6)
    public $price;                           // real(6)  not_null
    public $copied_from;						 // int(11)  not_null
    public $entree_id;                       // int(11)  not_null
    public $container_type;                  // string(19)  not_null enum
    public $pricing_type;                    // string(6)  not_null enum
    public $is_preassembled;                 // int(4)  not_null
    public $is_side_dish;                    // int(4)  not_null
    public $is_kids_choice;                  // int(3)  not_null unsigned
    public $is_store_special;                 // int(3)  not_null unsigned
    public $is_chef_touched;                 // int(3)  not_null unsigned
    public $is_menu_addon;                   // int(3)  not_null unsigned
    public $is_bundle;                   	 // int(3)  not_null unsigned
    public $is_optional;                     // int(4)  not_null
    public $sell_in_store_only;				 // int(3)  not_null unsigned
    public $SUPC_number;                     // string(45)
    public $is_visibility_controllable;      // int(4)  not_null
    public $is_price_controllable;           // int(4)  not_null
    public $menu_item_category_id;           // int(3)  multiple_key unsigned
    public $servings_per_item;               // int(3)  not_null unsigned
	public $servings_per_container_display;	 // string(24)
	public $item_count_per_item;               // int(3)  unsigned
	public $instructions;					 //
	public $instructions_air_fryer;					 //
	public $instructions_crock_pot;					 //
	public $instructions_grill;					 //
	public $prep_time;	 					//
	public $best_prepared_by;				//
	public $serving_suggestions;			//
    public $menu_order_priority;			// int(8) unsigned
    public $sales_mix;                      // real
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null
    public $menu_program_id;                 // int(10)  not_null unsigned
    public $master_grouping_id;              // int(10)  not_null unsigned
    public $cross_program_grouping_id;       // int(10)  not_null unsigned
    public $is_key_menu_push_item;           // int(4)  not_null
    public $recipe_id;                       // int(8)   unsigned
    public $station_number;					 // int(4)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Menu_item',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}