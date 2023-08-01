<?php
/**
 * Table Definition for employee_hours
 */
require_once 'DAO.inc';

class DAO_Employee_hours extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'employee_hours';      // table name
    public $id;                             // mediumint(11)  
    public $employee_id;                     // mediumint(11)  
    public $employee_name;                   // varchar(128)  
    public $shift_date;                      // date(10)  
    public $day;                             // varchar(12)  
    public $reg_hours;                       // decimal(10)  
    public $OT1_hours;                       // decimal(10)  
    public $OT2_hours;                       // decimal(10)  
    public $unpaid_hours;                    // decimal(10)  
    public $time_in;                         // time(8)  
    public $time_out;                        // time(8)  
    public $department;                      // varchar(80)  
    public $jobs;                            // varchar(80)  
    public $reg_pay_rate;                    // decimal(10)  
    public $OT_pay_rate;                     // decimal(10)  
    public $reg_paid;                        // decimal(10)  
    public $OT_paid;                         // decimal(10)  
    public $total_pay_amount;                // decimal(10)  
    public $store_id;                     // mediumint(11)
    public $user_id;                     // mediumint(11)
    
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
