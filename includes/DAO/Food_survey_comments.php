<?php
/**
 * Table Definition for food_survey_comments
 */
require_once 'DAO.inc';

class DAO_Food_survey_comments extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'food_survey_comments';                     // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $user_id;                         // int(11)  not_null
    public $recipe_id;                       // int(11)  not_null
    public $recipe_version;                  // int(11)  not_null
    public $menu_id;                         // int(11)  null
	public $personal_note;                   // text null
	public $comment;                         // text not_null
    public $comment_moderated;               // text null
    public $comment_status;                  // enum null
    public $timestamp_updated;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $timestamp_created;               // timestamp(19)  not_null unsigned zerofill binary
    public $updated_by;                      // int(10)  multiple_key unsigned
    public $is_active;                       // int(1)  not_null
    public $is_deleted;                      // int(1)  not_null

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Food_survey_comments',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}