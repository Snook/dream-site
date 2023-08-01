<?php
/**
 * Table Definition for tv_offer_search_log
 */
require_once 'DAO.inc';

class DAO_Tv_offer_search_log extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tv_offer_search_log';             // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $zip_code;                        // string(5)  not_null
    public $ip_address;               		 // string(16)  not_null unsigned zerofill binary timestamp
    public $time_of_search;                  // timestamp(19)  not_null unsigned zerofill binary
	public $state_id;							// string(2)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Tv_offer_search_log',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
