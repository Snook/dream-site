<?php
/**
 * Table Definition for mark_up_multi_to_volume_discount
 */
require_once 'DAO.inc';

class DAO_Mark_up_multi_to_volume_discount extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'mark_up_multi_to_volume_discount';    // table name
    public $mark_up_multi_id;                // int(8)  not_null multiple_key
    public $volume_discount_id;              // int(5)  not_null multiple_key unsigned

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Mark_up_multi_to_volume_discount',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
