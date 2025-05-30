<?php
/**
 * Table Definition for dream_rewards_history
 */
require_once 'DAO.inc';

class DAO_Dream_rewards_history extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dream_rewards_history';           // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  unsigned
    public $store_id;                        // int(11)  
    public $order_id;                        // int(11)  
    public $event_id;                        // int(4)  not_null
    public $previous_program_level;          // int(4)  
    public $current_program_level;          // int(4)  
    public $previous_program_status;         // int(4)  
    public $current_program_status;          // int(4)  
    public $description;                     // string(255)  
    public $ip_address;                      // string(64)  
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $created_by;                      // int(11)  not_null unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Dream_rewards_history',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}


