<?php
/**
 * Table Definition for enrollment_package
 */
require_once 'DAO.inc';

class DAO_User_digest extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_digest';              // table name
    public $id;                             	// int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                      	// int(11)
    public $first_session;              	 // date
	public $dream_taste_third_order_invite;    	 // date
    public $visit_count;					// int
	public $last_achievement_achieved_id;    	 // int
	public $is_deleted;                     // int(4)  not_null
	public $customer_order_type;                       // string(19)  not_null enum

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_User_digest',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
