<?php
/**
 * Table Definition for membership_history
 */
require_once 'DAO.inc';

class DAO_Membership_history extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'membership_history';    // table name
    public $id;                              // mediumint(11)  not_null primary_key unsigned auto_increment group_by
    public $user_id;                         // mediumint(11)  not_null multiple_key unsigned group_by
	public $membership_id;					 // mediumint(11)
    public $event_type;                      // char(20)  not_null multiple_key
    public $json_meta;                       // blob(65535)  blob
    public $timestamp_updated;               // timestamp(19)  not_null unsigned timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned
    public $updated_by;                      // int(11)  unsigned group_by
    public $created_by;                      // int(11)  unsigned group_by
    public $is_deleted;                      // tinyint(4)  not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
