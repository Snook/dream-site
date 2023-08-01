<?php
/**
 * Table Definition for pricing
 */
require_once 'DAO.inc';

class DAO_Pricing extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'pricing';             // table name
    public $id;                              // mediumint(8)  not_null primary_key unsigned auto_increment group_by
    public $menu_id;                         // mediumint(8)  not_null unsigned group_by
    public $recipe_id;                       // mediumint(8)  not_null unsigned group_by
    public $tier;                            // char(1)  not_null
    public $pricing_type;                    // char(6)  
    public $price;                           // decimal(9)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $created_by;                      // int(10)  unsigned group_by
    public $updated_by;                      // int(10)  unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
