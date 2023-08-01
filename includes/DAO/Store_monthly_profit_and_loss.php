<?php
/**
 * Table Definition for store_expenses
 */
require_once 'DAO.inc';

class DAO_Store_monthly_profit_and_loss extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_monthly_profit_and_loss';                  // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $date;                            // date(10)  multiple_key binary
    public $cost_of_goods_and_services;      // real(10)
    public $employee_wages;                  // real(10)
    public $manager_salaries;                // real(10)
    public $owner_salaries;                  // real(10)
    public $employee_hours;                  // real(6)
    public $manager_hours;                   // real(6)
    public $owner_hours;                     // real(6)
    public $payroll_taxes;                   // real(10)
    public $bank_card_merchant_fees;         // real(10)
    public $kitchen_and_office_supplies;     // real(10)
    public $total_marketing_and_advertising_expense;     // real(10)
    public $rent_expense;                    // real(10)
    public $repairs_and_maintenance;         // real(10)
    public $utilities;                       // real(10)
    public $monthly_debt_service;            // real(10)
    public $other_expenses;                  // real(10)
    public $net_income;                      // real(10)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_monthly_profit_and_loss',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
