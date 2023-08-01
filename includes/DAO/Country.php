<?php
/**
 * Table Definition for country
 */
require_once 'DAO.inc';

class DAO_Country extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'country';                         // table name
    public $id;                              // string(2)  not_null primary_key
    public $country_name;                    // string(64)  not_null multiple_key
    public $countries_iso_code_3;            // string(3)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Country',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
