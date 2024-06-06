<?php
/**
 * Table Definition for dashboard_metrics_guests_by_week
 */
require_once 'DAO.inc';

class DAO_Dashboard_metrics_guests_by_week extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dashboard_metrics_guests_by_week';        // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $start_date;                            // datetime
    public $end_date;                            // datetime
    public $menu_id;                            // int(4)  not_null binary
    public $store_id;                        // int(11)  not_null unsigned
    public $week_number;                       // int(1)  not_null
    public $year;                            // int(4)
    public $quarter;                            // int(1)
    public $quarter_week;                            // int(1)
    public $total_agr;                      // decimal
    public $average_standard_ticket;            // decimal
    public $lifestyle_guest_count;              // int
    public $total_food_cost;                   // decimal
    public $guest_count_total;               // int(8)  not_null
    public $guest_count_existing_regular;            // int(8)
    public $guest_count_existing_taste;            // int(8)
    public $guest_count_existing_intro;			// int(8)
    public $guest_count_existing_fundraiser;			// int(8)
    public $guest_count_reacquired_regular;    // int(8)
    public $guest_count_reacquired_taste;    // int(8)
    public $guest_count_reacquired_intro;    // int(8)
	public $guest_count_reacquired_fundraiser;			// int(8)
    public $guest_count_new_regular;         // int(8)
    public $guest_count_new_taste;           // int(8)
    public $guest_count_new_intro;           // int(8)
    public $guest_count_new_fundraiser;			// int(8)
    public $instore_signup_total;            // int(8)
    public $instore_signup_existing_regular;         // int(8)
    public $instore_signup_existing_taste;         // int(8)
    public $instore_signup_existing_intro;			// int(8)
    public $instore_signup_existing_fundraiser;			// int(8)
    public $instore_signup_reacquired_regular;    // int(8)
    public $instore_signup_reacquired_taste;    // int(8)
    public $instore_signup_reacquired_intro;    // int(8)
    public $instore_signup_reacquired_fundraiser;			// int(8)
    public $instore_signup_new_regular;      // int(8)
    public $instore_signup_new_taste;        // int(8)
    public $instore_signup_new_intro;        // int(8)
    public $instore_signup_new_fundraiser;			// int(8)
    public $avg_servings_per_guest_all;      // real(8)
    public $avg_servings_per_guest_regular;		// real(8)
    public $avg_servings_per_guest_existing_regular;    // real(8)
    public $avg_servings_per_guest_existing_taste;    // real(8)
    public $avg_servings_per_guest_existing_intro;		// real(8)
    public $avg_servings_per_guest_existing_fundraiser;			// real(8)
    public $avg_servings_per_guest_reacquired_regular;    // real(8)
    public $avg_servings_per_guest_reacquired_taste;    // real(8)
    public $avg_servings_per_guest_reacquired_intro;    // real(8)
    public $avg_servings_per_guest_reacquired_fundraiser;			// real(8)
    public $avg_servings_per_guest_new_regular;    // real(8)
    public $avg_servings_per_guest_new_taste;    // real(8)
    public $avg_servings_per_guest_new_intro;    // real(8)
    public $avg_servings_per_guest_new_fundraiser;			// real(8)
    public $total_servings_sold;                // int(10)
	public $total_items_sold;                // int(10)
	public $total_boxes_sold;                // int(10)
	public $converted_guests;                // int(8)
    public $conversion_rate;			      // real(6)
    public $one_month_drop_off;              // int(8)
    public $two_month_drop_off;              // int(8)
    public $average_annual_visits;           // real(6)
	public $average_annual_regular_visits;	 // real(6)
    public $sessions_count_all;              // int(6)
    public $sessions_count_regular;          // int(6)
    public $sessions_count_mfy;              // int(6)
    public $sessions_count_taste;            // int(6)
    public $sessions_count_fundraiser;			// int(6)
    public $orders_count_all;                // int(6)
    public $orders_count_regular;            // int(6)
    public $orders_count_mfy;                // int(6)
    public $orders_count_taste;              // int(6)
    public $orders_count_fundraiser;			// int(6)
    public $orders_count_regular_existing_guests;	// int(6)
    public $orders_count_regular_new_guests;		// int(6)
    public $orders_count_regular_reacquired_guests;// int(6)
    public $orders_count_intro_existing_guests;		// int(6)
    public $orders_count_intro_new_guests;			// int(6)
    public $orders_count_intro_reacquired_guests;	// int(6)
    public $orders_count_taste_existing_guests;		// int(6)
    public $orders_count_taste_new_guests;			// int(6)
    public $orders_count_taste_reacquired_guests;	// int(6)
    public $orders_count_fundraiser_existing_guests;			// int(6)
    public $orders_count_fundraiser_new_guests;			// int(6)
    public $orders_count_fundraiser_reacquired_guests;			// int(6)
    public $lost_guests_at_45_days;          // int(6)
    public $retention_count;				// int (6)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Dashboard_metrics_guests_by_week',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}