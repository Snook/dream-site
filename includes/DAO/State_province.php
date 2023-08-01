<?php
/**
 * Table Definition for state_province
 */
require_once 'DAO.inc';

class DAO_State_province extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'state_province';                  // table name
    public $id;                              // string(2)  not_null primary_key
    public $state_name;                      // string(128)  not_null
    public $isUSState;                       // string(128)  not_null
    public $trade_area_id;					// int

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_State_province',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
