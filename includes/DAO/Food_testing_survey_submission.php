<?php
/**
 * Table Definition for food_testing_survey_submission
 */
require_once 'DAO.inc';

class DAO_Food_testing_survey_submission extends DAO
{
	###START_AUTOCODE
	/* the code below is auto generated do not remove the above tag */

	public $__table = 'food_testing_survey_submission';                     // table name
	public $id;                              // int(11)  not_null primary_key unsigned auto_increment
	public $food_testing_survey_id;                   // int(11)  not_null unsigned
	public $user_id;                       // int(11)  not_null
	public $serving_size;                  // int(11)  not_null
	public $session_id;                  // int(11)  not_null
	public $menu_id;                  // int(11)  not_null
	public $ease_of_prep;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $look_appealing;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $i_liked_taste;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $family_liked_taste;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $salty_taste;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $spicy_taste;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $kid_friendly;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $would_like_on_menu;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $order_as_is;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $order_as_is_detail;               // text
	public $overall_satisfaction;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $liked_best;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $suggest_improvements;               // text
	public $sent_1st_reminder;               // datetime(19)  not_null unsigned zerofill binary timestamp
	public $sent_2nd_reminder;               // datetime(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_received;               // datetime(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_completed;               // datetime(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
	public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
	public $created_by;                      // int(10)  multiple_key unsigned
	public $updated_by;                      // int(10)  multiple_key unsigned
	public $is_deleted;                      // int(1)  not_null

	/* Static get */
	function staticGet($class, $k, $v = null)
	{
		return DB_DataObject::staticGet('DAO_Food_testing_survey_submission', $k, $v);
	}

	/* the code above is auto generated do not remove the tag below */
	###END_AUTOCODE
}

?>