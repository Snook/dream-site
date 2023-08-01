<?php
/**
 * Table Definition for user_employee_add_info
 */
require_once 'DAO.inc';

class DAO_User_employee_add_info extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user_employee_add_info';    // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $user_id;                          // int(11)
    public $genius_id;                         // int(11)
    public $target_user_type;                // string(19)  not_null multiple_key enum
    public $onboarding_complete;             // int(4)  not_null
    public $ec_telephone_1;                  // string(32)
    public $ec_telephone_1_type;             // string(9)  enum
    public $ec_telephone_2;                  // string(32)
    public $ec_telephone_2_type;             // string(9)  enum
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(10)  multiple_key unsigned
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
