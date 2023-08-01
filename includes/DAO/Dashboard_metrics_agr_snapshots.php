<?php
/**
 * Table Definition for DAO_Dashboard_metrics_agr_snapshots
 */
require_once 'DAO.inc';

class DAO_Dashboard_metrics_agr_snapshots extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dashboard_metrics_agr_snapshots';           // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $date;                            // date(10)  not_null binary
    public $month;							 // date(10)  not_null binary
    public $store_id;                        // int(11)  not_null unsigned
    public $agr_cal_month;                       // real(12)  not_null
    public $agr_menu_month;                       // real(12)  not_null
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Dashboard_metrics_agr_snapshots',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
