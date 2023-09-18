<?php
/**
 * Table Definition for zipcodes
 */
require_once 'DAO.inc';

class DAO_Zipcodes extends DAO
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'zipcodes';                        // table name
	public $zip;                             // string(16)  not_null primary_key multiple_key
	public $zip_type;                             // string(16)  not_null primary_key multiple_key
	public $city;                            // string(30)  not_null primary_key
	public $city_type;                            // string(30)  not_null primary_key
	public $county;                         // string(2)  not_null
    public $state;                           // string(30)  not_null
    public $latitude;                        // real(12)  not_null multiple_key
    public $longitude;                       // real(12)  not_null multiple_key
    public $timezone;                        // int(2)  not_null
	public $dst;                             // int(1)  not_null
	public $utc;                             // int(1)  not_null
	public $distribution_center;             // int(11)
	public $service_days;                    // int(5)
	public $zone;                         	 // int(5)

    /* Static get */
    function staticGet($class,$k,$v=NULL) { return DB_DataObject::staticGet('DAO_Zipcodes',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}