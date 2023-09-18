<?php
/**
 * Table Definition for orders
 */
require_once 'DAO.inc';

class DAO_Orders extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'orders';                          // table name
	public $id;                              // int(10)  not_null primary_key unsigned auto_increment
	public $user_id;                         // int(10)  not_null multiple_key unsigned
	public $my_meals_rating_user_id;         // int(10)  n
	public $store_id;                        // int(8)  not_null multiple_key unsigned
	public $sales_tax_id;                    // int(8)  multiple_key unsigned
	public $order_user_notes;                // blob(65535)  blob
	public $order_admin_notes;               // blob(65535)  blob
	public $order_confirmation;              // string(20)
	public $direct_order_discount;           // real(8)
	public $family_savings_discount_version;    // int(1)  not_null unsigned
	public $family_savings_discount;         // real(8)  not_null
	public $type_of_order;                     // enum
	public $grand_total;                     // real(8)  not_null
	public $subtotal_all_items;              // real(8)  not_null
	public $subtotal_all_taxes;              // real(8)  not_null
	public $product_items_total_count;       // int(4)  not_null
	public $subtotal_products;               // real(8)
	public $subtotal_menu_items;             // real(8)
	public $menu_items_total_count;          // int(5)  not_null unsigned
	public $menu_items_core_total_count;	 //int(5)
	public $servings_total_count;            // int(4)
	public $servings_core_total_count;        // int(4)
	public $misc_food_subtotal;              // real(8)
	public $misc_food_subtotal_desc;         // string(128)
	public $misc_nonfood_subtotal;           // real(8)
	public $misc_nonfood_subtotal_desc;      // string(128)
	public $subtotal_sales_taxes;            // real(8)
	public $subtotal_food_sales_taxes;       // real(8)
	public $subtotal_service_tax;             // real(8)
	public $subtotal_service_fee;             // real(8)
	public $subtotal_delivery_fee;             // real(8)
    public $subtotal_bag_fee;                   //real(6)
	public $subtotal_delivery_tax;             // real(8)
    public $subtotal_bag_fee_tax;               //decimal(4,2)
	public $subtotal_menu_item_mark_down;    // real(8)
	public $subtotal_ltd_menu_item_value;    // real(8)
	public $subtotal_meal_customization_fee;    // real(8)
	public $service_fee_description;         // string(128)
	public $dream_rewards_discount;          // real(8)  not_null
	public $dream_rewards_level;             // int(4)  not_null unsigned
	public $bundle_id;                       // int(11)  unsigned
	public $bundle_discount;                 // real(8)
	public $is_dr_downgraded_order;             // int(4)  not_null unsigned
	public $promo_code_discount_total;       // real(8)
	public $coupon_code_id;                  // int(8)  multiple_key unsigned
	public $coupon_code_discount_total;      // real(8)
	public $coupon_free_menu_item;             // int(11)
	public $fundraiser_id;         // int(11)
	public $fundraiser_value;         // int(11)
	public $ltd_round_up_value;        // decimal(2)
	public $promo_code_id;                   // int(5)  multiple_key unsigned
	public $session_discount_id;             // int(5)  multiple_key unsigned
	public $session_discount_total;          // real(8)
	public $subtotal_home_store_markup;      // real(8)
	public $markup_id;                       // int(8)  multiple_key unsigned
	public $mark_up_multi_id;                // int(8)  multiple_key
	public $subtotal_premium_markup;         // real(8)
	public $premium_id;                      // int(5)  multiple_key unsigned
	public $user_preferred_discount_total;    // real(8)
	public $user_preferred_discount_cap_type;    // real(8)
	public $user_preferred_discount_cap_applied;    // real(8)
	public $order_type;                      // string(8)  enum
	public $order_revision_notes;            // blob(65535)  blob
	public $in_store_order;                  // int(1)
	public $is_multiplier_eligible;          //int(1)
	public $is_sampler;                      // int(4)  not_null
	public $order_customization;                      // int(4)  not_null
	public $is_TODD;                          // int(4)  not_null
	public $pcal_preassembled_total_count;    // int(6)  not_null
	public $pcal_sidedish_total_count;       // int(6)  not_null
	public $pcal_preassembled_total;         // real(8)  not_null
	public $pcal_sidedish_total;             // real(8)  not_null
	public $pcal_core_total;				// real(8)  not_null
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $total_bag_count;                   //int(255)
	public $opted_to_bring_bags;			 // int(1)
	public $total_customized_meal_count;      //int(255)
	public $opted_to_customize_recipes;			 // int(1)
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $created_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(4)  not_null
	public $user_preferred_id;               // int(10)  multiple_key unsigned
	public $volume_discount_total;           // real(8)
	public $volume_discount_id;              // int(5)  multiple_key unsigned
	public $is_migrated;                     // int(4)
	public $inviting_user_id;                // int(10)  multiple_key unsigned
	public $menu_program_id;                 // int(10)  not_null unsigned
	public $points_discount_total;             // real(8)
	public $points_are_actualized;            // int(1)
	public $pp_discount_mfy_fee_first;        // int(1)
	public $is_in_plate_points_program;        // int(1)
	public $membership_id;        // int(1) Note: this points at a product_orders_items row which will the purchase and the membership itself
	public $membership_discount;
	// public $exclude_from_reports;			// int(4)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Orders', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}