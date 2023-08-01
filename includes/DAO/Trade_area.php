<?php
/**
 * Table Definition for trade_area
 */
require_once 'DAO.inc';

class DAO_Trade_area extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'trade_area';                      // table name
    public $id;                              // int(3)  not_null primary_key unsigned auto_increment
    public $region;                          // string(255)  not_null
    public $is_active;                       // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Trade_area',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
