<?php
/**
 * Table Definition for store_monthly_goals
 */
require_once 'DAO.inc';

class DAO_Store_monthly_goals extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_monthly_goals';            // table name
    public $id;                              			// int(11)  not_null primary_key unsigned auto_increment
    public $store_id;									// int(11)
    public $date;                       				// date
    public $gross_revenue_goal;                         // decimal(12)
    public $average_ticket_goal;                        // decimal(8)
    public $finishing_touch_revenue_goal;               // decimal(12)
    public $taste_sessions_goal;                        // int(8)
    public $regular_guest_count_goal;                   // int(8)
    public $taste_guest_count_goal;                     // int(8)
    public $intro_guest_count_goal;                     // int(8)
    public $regular_guest_count_goal_for_inventory;     // int(8)
    public $taste_guest_count_goal_for_inventory;      // int(8)
    public $intro_guest_count_goal_for_inventory;      // int(8)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_monthly_goals',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
