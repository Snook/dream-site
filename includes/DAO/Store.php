<?php
/**
 * Table Definition for store
 */
require_once 'DAO.inc';

class DAO_Store extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'store';                // table name
	public $id;                                // int(8)  not_null primary_key unsigned auto_increment
	public $home_office_id;                    // string(10)  multiple_key
	public $franchise_id;                    // int(8)  not_null multiple_key unsigned
	public $parent_store_id;                //
	public $opco_id;                    // int(8)  not_null multiple_key unsigned
	public $is_corporate_owned;                // int(1)
	public $active;                            // int(4)  not_null
	public $show_on_customer_site;            // int(4)
	public $ssm_builder;                    // int(1)  not_null
	public $store_type;                        // string(255)
	public $address_line1;                    // string(255)
	public $address_line2;                    // string(255)
	public $city;                            // string(64)
	public $county;                            // string(64)
	public $state_id;                        // string(2)  not_null multiple_key
	public $country_id;                        // string(2)  multiple_key
	public $postal_code;                    // string(10)
	public $usps_adc;                        // string(4)
	public $address_latitude;                // real(12)  not_null multiple_key
	public $address_longitude;                // real(12)  not_null multiple_key
	public $address_directions;                // text
	public $email_address;                    // string(255)
	public $telephone_day;                    // string(64)
	public $telephone_evening;                // string(64)
	public $telephone_sms;                    // string(64)
	public $fax;                            // string(64)
	public $google_place_id;                    // string(255)
	public $social_twitter;                    // string(255)
	public $social_facebook;                    // string(255)
	public $social_instagram;                    // string(255)
	public $pkg_ship_same_as_store;            // int(1)
	public $pkg_ship_is_commercial;            // int(1)
	public $pkg_ship_address_line1;            // string(255)
	public $pkg_ship_address_line2;            // string(255)
	public $pkg_ship_city;                    // string(255)
	public $pkg_ship_state_id;                // string(255)
	public $pkg_ship_postal_code;            // string(255)
	public $pkg_ship_attn;                    // string(255)
	public $pkg_ship_telephone_day;            // string(255)
	public $letter_ship_same_as_store;        // int(1)
	public $letter_ship_is_commercial;        // int(1)
	public $letter_ship_address_line1;        // string(255)
	public $letter_ship_address_line2;        // string(255)
	public $letter_ship_city;                // string(255)
	public $letter_ship_state_id;            // string(255)
	public $letter_ship_postal_code;        // string(255)
	public $letter_ship_attn;                // string(255)
	public $letter_ship_telephone_day;        // string(255)
	public $manager_1_user_id;                // int(11)
	public $timezone_id;                    // int(6)  not_null
	public $observes_DST;                    // int(1)  not_null
	public $default_intro_slots;            // int(6)  not_null
	public $food_testing_w9;                // int(1)  not_null
	public $supports_corporate_crate;        // int(1) not null default 0
	public $supports_fundraiser;            // int(1)  not_null
	public $supports_retention_programs;    // int(1) not null
	public $supports_transparent_redirect;    // int(1) not null
	public $supports_order_manager;            // int(1) not null
	public $supports_intro_orders;            // int(4)  not_null
	public $supports_infomercial;            // int(4)  not null
	public $supports_dinners_for_life;        // int(4)  not_null
	public $supports_dfl_customer_site;        // int(4)  not_null
	public $supports_ltd_roundup;            // int(4)  not_null
	public $supports_meal_customization;    // int(4)  not_null
	public $allow_preassembled_customization;    // int(4)  not_null
	public $allow_dfl_tool_access;            // int(4)  not_null
	public $hide_carryover_notes;            // int(1)  not_null
	public $hide_fadmin_home_dashboard;        // int(1)  not_null
	public $receive_low_inv_alert;        // int(1)  not_null
	public $supports_plate_points;            // int(1)  not_null unsigned
	public $supports_dream_rewards;            // int(1)  not_null unsigned
	public $supports_free_assembly_promotion;// int(1)
	public $supports_delivery;                // int(1)
	public $supports_delivery_tip;                // int(1)
	public $supports_delayed_payment;                // int(1)
	public $supports_membership;            //int(1)
	public $supports_new_memberships;        //int(1)
	public $supports_offsite_pickup;        //int(1)
	public $supports_bag_fee;               //tinyint(1)
	public $default_bag_fee;                  //decimal(4,2)
	public $delivery_fee;                    // int(1)
	public $supports_plate_points_enhancements;// int(1)
	public $supports_special_events;        // int(1)  not_null unsigned
	public $supports_custom_todd_pricing;    // int(4)
	public $serving_tabindex_vertical;        // int(1) not_null
	public $dream_taste_opt_out;            // int(1) not_null
	public $close_session_hours;            // int(11)  not_null
	public $close_customization_session_hours;// int(11)  not_nul
	public $close_interval_type;            // string(12)  enum
	public $meal_customization_close_interval_type;            // string(12)  enum
	public $default_delayed_payment_deposit;// decimal
	public $delayed_payment_order_minimum;// decimal
	public $store_name;                        // string(64)
	public $store_description;                // blob(65535)  blob
	public $grand_opening_date;                // datetime(19)  binary
	public $publish_session_details;        // int(1)
	public $shortcut_name;                    // string(80)
	public $vertical_response_code;            // string(45)
	public $dailystory_tenant_uid;         // string
	public $gp_account_id;                  // string(16)
	public $ddu_id;                        // string(64)
	public $merchant_id;                       // string(25)
	public $terminal_id;                        // string(10)
	public $door_dash_id;                        // string(20)
	public $do_run_dream_rewards_cron_tasks;// int(1) not null
	public $is_in_current_top_5;            // int(1) not null
	public $timestamp_last_activity;        // timestamp(19)
	public $timestamp_last_metrics_update;    // timestamp(19)
	public $first_menu_supported;            // int 11
	public $last_menu_supported;            // int 11
	public $medium_ship_cost;                // decimal(12)
	public $default_delivered_sessions;        // int(6)  not_null
	public $large_ship_cost;                 // decimal(12)
	public $core_pricing_tier;                // enum
	public $bio_store_name;                // string
	public $bio_primary_party_name;    // string
	public $bio_primary_party_title;    // string
	public $bio_primary_party_story;    // text
	public $bio_secondary_party_name;    // string
	public $bio_secondary_party_title;    // string
	public $bio_secondary_party_story;    // text
	public $bio_team_description;        // text
	public $bio_store_hours;            // text
	public $bio_store_holiday_hours;    // text
	public $timestamp_updated;                // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;                // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                        // int(11)  multiple_key unsigned
	public $updated_by;                        // int(11)  multiple_key unsigned
	public $is_deleted;                        // int(1)  not_null multiple_key

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Store', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}