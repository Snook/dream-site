<?php
/**
 * Table Definition for monthly_gross_sales
 */
require_once 'DAO.inc';

class DAO_Monthly_gross_sales extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'monthly_gross_sales';    // table name
    public $id;                              // mediumint(11)  not_null primary_key unsigned auto_increment group_by
    public $UFOC;                            // varchar(12)  not_null
    public $Date;                            // date(10)  not_null
    public $value;                           // decimal(16)  not_null
    public $last_value;                      // decimal(16)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
