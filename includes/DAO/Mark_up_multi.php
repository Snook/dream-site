<?php
/**
 * Table Definition for mark_up_multi
 */
require_once 'DAO.inc';

class DAO_Mark_up_multi extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'mark_up_multi';                   // table name
    public $id;                              // int(8)  not_null primary_key auto_increment
    public $store_id;                        // int(8)  not_null
    public $markup_value_6_serving;          // real(6)
	public $markup_value_4_serving;          // real(6)
	public $markup_value_3_serving;          // real(6)
	public $markup_value_2_serving;          // real(6)
    public $markup_value_sides;          	 // real(6)
    public $timestamp_created;               // timestamp(19)  unsigned zerofill binary
    public $timestamp_updated;               // timestamp(19)  unsigned zerofill binary timestamp
    public $mark_up_start;                   // datetime(19)  binary
    public $mark_up_expiration;              // datetime(19)  binary
    public $menu_id_start;                   // int(8)
    public $is_default;                      // int(4)
    public $sampler_item_price;				 // real (8)
	public $assembly_fee;					 // real (8)
	public $delivery_assembly_fee;			// real (8)
    public $updated_by;                      // int(11)
    public $created_by;                      // int(11)
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Mark_up_multi',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}