<?php
/**
 * Table Definition for store_weekly_data
 */
require_once 'DAO.inc';

class DAO_Store_weekly_data extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_weekly_data';            // table name
    public $id;                              			// int(11)  not_null primary_key unsigned auto_increment
    public $store_id;									// int(11)
    public $week;                       				// int
    public $year;										// int
    public $food_costs;                         		// decimal(12)
    public $labor_costs;                         		// decimal(12)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_weekly_data',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
