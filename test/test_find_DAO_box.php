<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO.inc");


$DAO_box = DAO_CFactory::create('box', true);
$DAO_box->whereAdd("box.store_id IS NOT NULL");
$DAO_box->whereAdd("box.availability_date_start <= NOW()");
$DAO_box->whereAdd("box.availability_date_end >= NOW()");
$DAO_box->find_DAO_box();

$boxArray = array();
while($DAO_box->fetch())
{
	$boxArray[$DAO_box->id] = clone $DAO_box;
}

$debugbreakpoint = true;
?>