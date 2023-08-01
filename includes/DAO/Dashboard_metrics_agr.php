<?php
/**
 * Table Definition for dashboard_metrics_agr
 */
require_once 'DAO.inc';

class DAO_Dashboard_metrics_agr extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dashboard_metrics_agr';           // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $date;                            // date(10)  not_null binary
    public $store_id;                        // int(11)  not_null unsigned
    public $is_locked;                       // int(1)  not_null
    public $total_agr;                       // real(12)  not_null
    public $sales_adjustments_total;         // real(12)
    public $agr_by_session_standard;         // real(12)
    public $agr_by_session_taste;            // real(12)
    public $agr_by_session_mfy;              // real(12)
    public $agr_by_session_fundraiser;			// real(12)
    public $agr_by_order_regular;            // real(12)
    public $agr_by_order_intro;              // real(12)
    public $agr_by_order_taste;              // real(12)
    public $agr_by_order_fundraiser;			// real(12)
    public $agr_by_guest_existing_regular;    // real(12)
    public $agr_by_guest_existing_taste;    // real(12)
    public $agr_by_guest_existing_intro;	//real(12)
    public $agr_by_guest_existing_fundraiser;			// real(12)
    public $agr_by_guest_new_regular;        // real(12)
    public $agr_by_guest_new_taste;          // real(12)
    public $agr_by_guest_new_intro;          // real(12)
    public $agr_by_guest_new_fundraiser;			// real(12)
    public $agr_by_guest_reacquired_regular;    // real(12)
    public $agr_by_guest_reacquired_taste;    // real(12)
    public $agr_by_guest_reacquired_intro;    // real(12)
    public $agr_by_guest_reacquired_fundraiser;			// real(12)
    public $revenue_by_session_standard;    // real(12)
    public $revenue_by_session_taste;    // real(12)
    public $revenue_by_session_mfy;    // real(12)
    public $revenue_by_session_fundraiser;			// real(12)
    public $revenue_by_order_regular;    // real(12)
	public $revenue_by_order_additional;    // real(12)
    public $revenue_by_order_intro;    // real(12)
    public $revenue_by_order_taste;    // real(12)
    public $revenue_by_order_fundraiser;			// real(12)
    public $revenue_by_guest_existing_regular;    // real(12)
	public $revenue_by_guest_existing_additional;    // real(12)
    public $revenue_by_guest_existing_taste;    // real(12)
    public $revenue_by_guest_existing_intro;	// real(12)
    public $revenue_by_guest_existing_fundraiser;			// real(12)
    public $revenue_by_guest_new_regular;    // real(12)
	public $revenue_by_guest_new_additional;    // real(12)
    public $revenue_by_guest_new_taste;    // real(12)
    public $revenue_by_guest_new_intro;    // real(12)
    public $revenue_by_guest_new_fundraiser;			// real(12)
    public $revenue_by_guest_reacquired_regular;    // real(12)
	public $revenue_by_guest_reacquired_additional;    // real(12)
    public $revenue_by_guest_reacquired_taste;    // real(12)
    public $revenue_by_guest_reacquired_intro;    // real(12)
    public $revenue_by_guest_reacquired_fundraiser;			// real(12)
    public $avg_ticket_all;                  // real(8)
    public $avg_ticket_regular;				 //real(8)
    public $avg_ticket_by_guest_existing_regular;    // real(8)
    public $avg_ticket_by_guest_existing_taste;    // real(8)
    public $avg_ticket_by_guest_existing_intro;		//real(8)
    public $avg_ticket_by_guest_existing_fundraiser;			// real(8)
    public $avg_ticket_by_guest_new_regular;    // real(8)
    public $avg_ticket_by_guest_new_taste;    // real(8)
    public $avg_ticket_by_guest_new_intro;    // real(8)
    public $avg_ticket_by_guest_new_fundraiser;			// real(8)
    public $avg_ticket_by_guest_reacquired_regular;    // real(8)
    public $avg_ticket_by_guest_reacquired_taste;    // real(8)
    public $avg_ticket_by_guest_reacquired_intro;    // real(8)
    public $avg_ticket_by_guest_reacquired_fundraiser;			// real(8)
    public $addon_sales_total;               // real(12)
    public $addon_sales_by_guest_existing_regular;    // real(8)
    public $addon_sales_by_guest_existing_taste;    // real(8)
    public $addon_sales_by_guest_existing_intro;	// real(12)
    public $addon_sales_by_guest_new_regular;    // real(8)
    public $addon_sales_by_guest_new_taste;    // real(8)
    public $addon_sales_by_guest_new_intro;    // real(8)
    public $addon_sales_by_guest_reacquired_regular;    // real(8)
    public $addon_sales_by_guest_reacquired_taste;    // real(8)
    public $addon_sales_by_guest_reacquired_intro;    // real(8)
    public $addon_sales_by_guest_existing_fundraiser;			// real(12)
    public $addon_sales_by_guest_new_fundraiser;			// real(12)
    public $addon_sales_by_guest_reacquired_fundraiser;			// real(12)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Dashboard_metrics_agr',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
