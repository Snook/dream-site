<?php
/**
 * Table Definition for transient_data_store
 */
require_once 'DAO.inc';

class DAO_Transient_data_store extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'transient_data_store';                      // table name
	public $id;
	public $data_reference;
	public $data_class;
	public $data;
	public $expires;
	public $created_by;
	public $updated_by;
	public $timestamp_updated;
	public $timestamp_created;

	/* Static get */
	function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Transient_data_store',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}