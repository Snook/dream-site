<?php
/**
 * Table Definition for gift_card_design
 */
require_once 'DAO.inc';

class DAO_Gift_card_design extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'gift_card_design';                // table name
    public $id;                              // int(4)  not_null primary_key unsigned auto_increment
    public $title;                           // string(128)  not_null
    public $description;                     // blob(65535)  not_null blob
    public $is_active;                       // int(1)  not_null
    public $image_path;                      // string(128)  not_null
    public $image_path_egift;                // string(128)  not_null
    public $supports_physical;               // int(1)  not_null
    public $supports_virtual;                // int(1)  not_null
    public $layout_order;                    // int(3)  
    public $is_deleted;                      // int(1)  not_null
    
    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Gift_card_design',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
