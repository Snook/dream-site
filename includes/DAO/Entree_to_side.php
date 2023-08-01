<?php
/**
 * Table Definition for enrollment_package
 */
require_once 'DAO.inc';

class DAO_Entree_to_side extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'entree_to_side';              // table name
    public $id;                             // int(11)  not_null primary_key unsigned auto_increment
    public $entree_id;                      // int(11)
    public $entree_recipe_id;               // int(11)
    public $entree_menu_item_id;            // int(11)
    public $side_menu_item_id;              // int(11)
    public $is_deleted;                     // int(4)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Entree_to_side',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
