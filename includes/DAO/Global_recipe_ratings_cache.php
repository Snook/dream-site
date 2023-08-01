<?php
/**
 * Table Definition for global_ratings_cache
 */
require_once 'DAO.inc';

class DAO_Global_recipe_ratings_cache extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'global_recipe_ratings_cache';    // table name
    public $id;                              // mediumint(11)  not_null primary_key unsigned auto_increment group_by
    public $recipe_id;                       // mediumint(8)  not_null multiple_key unsigned group_by
    public $global_rating;                   // tinyint(3)  not_null unsigned group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
