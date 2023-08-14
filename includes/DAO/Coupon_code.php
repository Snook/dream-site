<?php
/**
 * Table Definition for coupon_code
 */
require_once 'DAO.inc';

class DAO_Coupon_code extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'coupon_code';                     // table name
	public $id;                              // int(11)  not_null primary_key unsigned auto_increment
	public $program_id;                      // int(11)  not_null multiple_key unsigned
	public $coupon_code_title;               // string(80)
	public $coupon_code_short_title;         // string(36)  not_null
	public $coupon_code_description;         // blob(65535)  blob
	public $comments;                        // blob(65535)  blob
	public $coupon_code;                     // string(18)  not_null
	public $is_store_specific;                // int(4)
	public $applicable_customer_type;        // string(8)  enum
	public $use_limit_per_account;           // int(4)  not_null
	public $valid_timespan_start;            // datetime(19)  binary
	public $valid_timespan_end;              // datetime(19)  binary
	public $remiss_cutoff_date;               // datetime(19)  binary
	public $remiss_number_of_months;               // int(11)
	public $valid_menuspan_start;            // int(11)
	public $valid_menuspan_end;              // int(11)
    public $valid_session_timespan_start;            // datetime
    public $valid_session_timespan_end;              // datetime
    public $minimum_order_amount;            // real(8)
	public $minimum_servings_count;                // int(10)
	public $minimum_item_count;                // int(10)
	public $valid_for_session_type_standard;    // int(4)  unsigned
	public $valid_for_session_type_private;    // int(4)
	public $valid_for_session_type_discounted;    // int(4)
    public $valid_for_session_type_delivery;    // tinyint(4)
	public $valid_for_order_type_standard;    // int(4)
	public $valid_for_order_type_intro;      // int(4)
	public $valid_for_order_type_sampler;    // int(4)
	public $valid_for_order_type_dream_taste; // int(4)
	public $valid_for_standard_menu;        // int(4)
	public $valid_for_DFL_menu;                // int(4)
	public $valid_DFL_menu;                // int(4)
	public $valid_corporate_crate_client_id;                // int(11)
	public $is_product_coupon;                // tinyint(1)
	public $valid_for_product_type_membership;                // tinyint(1)
	public $is_direct_order_supported;         // int(1)
	public $is_order_editor_supported;         // int(1)
	public $is_customer_order_supported;     // int(1)
	public $is_platepoints_perk;            // int(1)
	public $is_store_coupon;					// int(1)
	public $is_delivered_coupon;				// int(1)
	public $delivered_requires_medium_box;		// int(1)
	public $delivered_requires_large_box;		// int(1)
	public $delivered_requires_custom_box;		// int(1)
	public $delivered_requires_curated_box;	// int(1)
	public $use_TODD_rules;                     // int(1)
	public $limit_to_grand_total;           // tinyint(1)
	public $limit_to_core;           // tinyint(4)
	public $limit_to_finishing_touch;        // int(1)
	public $limit_to_mfy_fee;           // tinyint(4)
	public $limit_to_delivery_fee;           // tinyint(4)
	public $limit_to_recipe_id;           // tinyint(4)
	public $discount_method;                 // string(9)  enum
	public $discount_var;                    // real(12)
	public $menu_item_id;                    // int(8)  multiple_key unsigned
	public $recipe_id;                    // int(8)  multiple_key unsigned
	public $recipe_id_pricing_type;	// enum
	public $valid_with_customer_referral_credit;    //
	public $valid_with_plate_points_credits;	//
	public $updated_by;                      // int(10)  multiple_key unsigned
	public $created_by;                      // int(10)  multiple_key unsigned
	public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Coupon_code', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}