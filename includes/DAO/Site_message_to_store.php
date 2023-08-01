<?php
/**
 * Table Definition for site_message_to_store
 */
require_once 'DAO.inc';

class DAO_site_message_to_store extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'site_message_to_store';			// table name
	public $id;					//
	public $site_message_id;			//
	public $store_id;//
	public $created_by;			//
	public $updated_by;			//
	public $timestamp_created;	//
	public $timestamp_updated;	//
	public $is_deleted;			//

	/* Static get */
	function staticGet($class, $k,$v=NULL) { return DB_DataObject::staticGet('DAO_site_message_to_store',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
?>