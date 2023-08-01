<?php
/**
 * Table Definition for _dreamsite_version
 */
require_once 'DAO.inc';

class DAO__dreamsite_version extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = '_dreamsite_version';              // table name
    public $version_id;                      // string(4)  not_null primary_key
    public $version_date;                    // timestamp(19)  unsigned zerofill binary timestamp
    public $version_comments;                // string(255)  not_null

    /* Static get */
    function staticGet($class, $k,$v=NULL) { return DB_DataObject::staticGet('DAO__dreamsite_version',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
