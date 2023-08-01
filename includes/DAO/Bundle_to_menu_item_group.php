<?php
/**
 * Table Definition for bundle_to_menu_item
 */
require_once 'DAO.inc';

class DAO_Bundle_to_menu_item_group extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'bundle_to_menu_item_group';             // table name
    public $id;                              // int(8)  not_null primary_key unsigned auto_increment
	public $group_title;                       // int(11)  not_null unsigned
	public $group_description;                       // int(11)  not_null unsigned
	public $number_items_required;                       // int(11)  not_null unsigned
	public $number_servings_required;                       // int(11)  not_null unsigned
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Bundle_to_menu_item_group',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}