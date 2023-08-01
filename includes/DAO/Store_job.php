<?php
/**
 * Table Definition for store_closure_history
 */
require_once 'DAO.inc';

class DAO_Store_job extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'store_job';           // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $position;              // enum  not_null binary
    public $description;                // varchar(255)  not_null multiple_key unsigned
    public $available;     // init
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(11)  multiple_key unsigned
	public $updated_by;                      // int(11)  multiple_key unsigned
	public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Store_job',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}