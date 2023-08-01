<?php
/**
 * Table Definition for single_use_tokens
 */
require_once 'DAO.inc';

class DAO_Single_use_tokens extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'single_use_tokens';    // table name
    public $id;                               // int(11)  not_null primary_key
    public $token;                        	  // string(64)
    public $email;							  // string (128)
    public $datetime_created;				  // datetime

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Single_use_tokens',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
