<?php
/**
 * Table Definition for edited_order_item
 */
require_once 'DAO.inc';

class DAO_Edited_order_item extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'edited_order_item';               // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $order_id;                        // int(10)  not_null multiple_key unsigned
    public $product_id;                      // int(8)  multiple_key unsigned
    public $menu_item_id;                    // int(8)  multiple_key unsigned
    public $pre_mark_up_sub_total;           // real(8)  
    public $sub_total;                       // real(8)  not_null
    public $item_count;                      // int(1)  
    public $discounted_subtotal;             // real(8)  

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Edited_order_item',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
