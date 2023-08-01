<?php
/**
 * Table Definition for opco_new
 */
require_once 'DAO.inc';

class DAO_Opco extends DAO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'opco_new';            // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $opco_number;                     // int(11)  
    public $opco_location;                   // string(64)  
    public $active;                          // int(4)  
    public $pricing_tier;                    // int(11)  
    public $address;                         // string(255)  
    public $city;                            // string(64)  
    public $state_id;                        // string(2)  
    public $zip_code;                        // string(10)  
    public $phone;                           // string(16)  
    public $toll_free_phone;                 // string(16)  
    public $fax_number;                      // string(16)  
    public $website;                         // string(128)  
    public $primary_contact;                 // string(128)  
    public $position;                        // string(64)  
    public $office_phone;                    // string(16)  
    public $extension;                       // string(8)  
    public $mobile_phone;                    // string(16)  
    public $contact_email;                   // string(128)  

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
