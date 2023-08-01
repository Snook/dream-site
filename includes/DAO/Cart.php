<?php
/**
 * Table Definition for cart
 */
require_once 'DAO.inc';

class DAO_Cart extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'cart';                            // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $cart_key;                        // string(255)  not_null multiple_key
    public $data;                            // blob(65535)  blob binary
	public $cart_contents_id;				 // int(11)
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary

    /* Static get */
 //   function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Cart',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
