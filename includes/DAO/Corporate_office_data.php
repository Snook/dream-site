<?php
/**
 * Table Definition for corporate_office_data
 */
require_once 'DAO.inc';

class DAO_Corporate_office_data extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'corporate_office_data';           // table name
    public $id;                              // int(6)  not_null primary_key auto_increment
    public $ca_name;                         // string(50)  not_null
    public $ca_password;                     // string(128)  not_null
    public $partner_id;                      // string(255)  not_null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Corporate_office_data',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
