<?php
/**
 * Table Definition for site_message
 */
require_once 'DAO.inc';

class DAO_site_message extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'site_message';			// table name
	public $id;					//
	public $message_type;		//
	public $alert_css;			//
	public $title;				//
	public $message;			//
	public $icon;				//
	public $audience;			//
	public $message_start;		//
	public $disable_site_start;	//
	public $message_end;		//
	public $is_active;			//
	public $home_office_managed;//
	public $created_by;			//
	public $updated_by;			//
	public $timestamp_created;	//
	public $timestamp_updated;	//
	public $is_deleted;			//

	/* Static get */
	function staticGet($class, $k,$v=NULL) { return DB_DataObject::staticGet('DAO_site_message',$k,$v); }

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}
?>