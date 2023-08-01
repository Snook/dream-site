<?php
/**
 * Table Definition for box
 */
require_once 'DAO.inc';

class DAO_Box extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'box';                         // table name
	public $id;
	public $store_id;
	public $title;
	public $description;
	public $css_icon;
	public $sort;
	public $box_type;
	public $menu_id;
	public $is_visible_to_customer;
	public $box_bundle_1;
	public $box_bundle_1_active;
	public $box_bundle_2;
	public $box_bundle_2_active;
	public $availability_date_start;
	public $availability_date_end;
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(11)  multiple_key unsigned
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Box',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}