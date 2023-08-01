<?php
/**
 * Table Definition for browser_sessions
 */
require_once 'DAO.inc';

class DAO_Csrf_tokens extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'csrf_tokens';                // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $session_id;             		// string(255)  
    public $cart_id;             		// string(255)  
    public $token;                         	// string(32) 
    public $expiration_time;                // int
    public $action;                			// string(64)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Csrf_tokens',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

