<?php
/**
 * Table Definition for corporate_crate_client
 */
require_once 'DAO.inc';

class DAO_Corporate_crate_client extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'corporate_crate_client';    // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $company_name;                    // string(256)  
    public $triggering_domain;               // string(256)  
    public $icon_path;                       // string(512)  
    public $is_active;                       // int(1)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
