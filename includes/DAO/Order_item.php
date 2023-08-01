<?php
/**
 * Table Definition for order_item
 */
require_once 'DAO.inc';

class DAO_Order_item extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'order_item';                      // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $order_id;                        // int(10)  not_null multiple_key unsigned group_by
    public $product_id;                      // mediumint(8)  multiple_key unsigned group_by
    public $menu_item_id;                    // mediumint(8)  multiple_key unsigned group_by
    public $parent_menu_item_id;             // mediumint(8)  unsigned group_by
    public $bundle_id;                       // int(11)  unsigned group_by
    public $box_instance_id;                 // int(11)  group_by
    public $pre_mark_up_sub_total;           // decimal(8)  
    public $sub_total;                       // decimal(8)  not_null
    public $item_count;                      // tinyint(1)  group_by
    public $bundle_item_count;               // tinyint(1)  group_by
    public $discounted_subtotal;             // decimal(8)  
    public $menu_item_mark_down_id;          // mediumint(8)  group_by
    public $inventory_was_processed;         // tinyint(1)  unsigned group_by
    public $edit_sequence_id;                // varchar(32)  
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  unsigned
    public $updated_by;                      // mediumint(11)  unsigned group_by
    public $created_by;                      // mediumint(11)  unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Order_item',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
