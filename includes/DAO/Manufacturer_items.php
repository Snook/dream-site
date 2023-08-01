<?php
/**
 * Table Definition for manufacturer_items
 */
require_once 'DAO.inc';

class DAO_Manufacturer_items extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'manufacturer_items';                          // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $store_id;                       // int(11)  not_null
    public $recipe_id;                       // int(11)  not_null
    public $active;                       // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Manufacturer_items',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
