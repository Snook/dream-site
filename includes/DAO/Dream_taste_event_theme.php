<?php
/**
 * Table Definition for dream_taste_event_theme
 */
require_once 'DAO.inc';

class DAO_dream_taste_event_theme extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dream_taste_event_theme';                            // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
	public $title;                        // string(255)  not_null multiple_key
	public $title_public;                        // string(255)  not_null multiple_key
	public $fadmin_acronym;                        // string(255)  not_null multiple_key
	public $session_type;                        // string(255)  not_null multiple_key
	public $sort;                        // int(11)  not_null multiple_key
	public $sub_theme;                        // string(255)  not_null multiple_key
	public $sub_sub_theme;                        // string(255)  not_null multiple_key
	public $theme_string;                        // string(255)  not_null multiple_key

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Dream_taste_event_theme',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
