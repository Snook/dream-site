<?php
/**
 * Table Definition for dream_taste_event_properties
 */
require_once 'DAO.inc';

class DAO_Dream_taste_event_properties extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'dream_taste_event_properties';                            // table name
	public $id;                              // int(11)  not_null primary_key auto_increment
	public $dream_taste_event_theme;                  // int(11)
	public $default_taste_type;                  // int(11)
	public $menu_id;                          // int(11)
	public $menu_used_with_theme;
	public $bundle_id;                          // int(11)
	public $host_required;                          // int(1)
	public $available_on_customer_site;                          // int(1)
	public $password_required;                          // int(1)
	public $can_rsvp_only;                          // int(1)
	public $can_rsvp_upgrade;                          // int(1)
	public $existing_guests_can_attend;
	public $host_platepoints_eligible;                          // int(1)
	public $customer_coupon_eligible;                          // int(1)
	public $is_deleted;                          // int(11)

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Dream_taste_event_properties', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}