<?php
/**
 * Table Definition for file
 */
require_once 'DAO.inc';

class DAO_File extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'file';                       // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $file_asset_name;              	 // varchar(255)  null
    public $file_name;               	 	 // varchar(255)  null
    public $file_type;               		 // varchar(255)  null
    public $file_size;               		 // int(11)  null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $created_by;                      // int(11)  multiple_key unsigned
    public $updated_by;                      // int(11)  multiple_key unsigned
    public $is_deleted;                      // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_File',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
