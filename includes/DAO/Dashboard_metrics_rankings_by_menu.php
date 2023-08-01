<?php
/**
 * Table Definition for dashboard_metrics_rankings_by_menu
 */
require_once 'DAO.inc';

class DAO_Dashboard_metrics_rankings_by_menu extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dashboard_metrics_rankings_by_menu';      // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $date;                            // date(10)  not_null binary
    public $store_id;                        // int(11)  not_null unsigned
    public $agr;                             // real(12)  not_null
    public $agr_rank;                        // int(6)  not_null
    public $agr_percent_change;              // real(8)  not_null
    public $agr_percent_change_rank;         // int(6)  not_null
    public $in_store_signup;                 // real(8)  not_null
    public $in_store_signup_rank;            // int(6)  not_null
    public $guest_visits;                    // int(8)  not_null
    public $guest_visits_rank;               // int(6)  not_null
	public $new_guest_visits;                    // int(8)  not_null
	public $new_guest_visits_rank;               // int(6)  not_null
	public $avg_visits_per_session;          // real(8)  not_null
    public $avg_visits_per_session_rank;     // int(6)  not_null
    public $avg_ticket;                      // real(10)  not_null
    public $avg_ticket_rank;                 // int(6)  not_null
    public $addon_sales;                     // real(12)  not_null
    public $addon_sales_rank;                // int(6)  not_null
    public $servings_per_guest;              // real(8)  not_null
    public $servings_per_guest_rank;         // int(6)  not_null
    public $converted_guests;              		// real(8)  not_null
    public $converted_guests_rank;         // int(6)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Dashboard_metrics_rankings_by_menu',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
