<?php
/**
 * Table Definition for orders_bundle_box
 */
require_once 'DAO.inc';

class DAO_Box_instance extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'box_instance';          // table name
    public $id;                              // mediumint(8)  not_null primary_key unsigned auto_increment group_by
    public $bundle_id;                       // mediumint(11)  not_null group_by
	public $box_id;                       	 // mediumint(11)  not_null group_by
	public $order_id;                        // mediumint(11)  unsigned group_by
	public $cart_contents_id;                // mediumint(11)  unsigned group_by
	public $is_in_edit_mode;				 // tinyint(1)
	public $is_complete;					 // tinyint(1)
	public $edit_sequence_id;				// mediumint(11)
	public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $created_by;                      // int(11)  multiple_key unsigned group_by
    public $updated_by;                      // int(11)  multiple_key unsigned group_by
    public $is_deleted;                      // tinyint(1)  not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
