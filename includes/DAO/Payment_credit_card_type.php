<?php
/**
 * Table Definition for payment_credit_card_type
 */
require_once 'DAO.inc';

class DAO_Payment_credit_card_type extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'payment_credit_card_type';        // table name
    public $id;                              // int(5)  not_null primary_key unsigned auto_increment
    public $store_id;                        // int(8)  not_null multiple_key unsigned
    public $credit_card_type;                // string(16)  enum
    public $is_default_card;                 // int(1)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  unsigned
    public $updated_by;                      // int(11)  unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Payment_credit_card_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
