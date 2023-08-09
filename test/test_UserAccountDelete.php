<?php
require_once("../includes/Config.inc");
require_once("DAO/BusinessObject/CUser.php");


$user = new CUser();
$user->id = 430427;
$user->find(true);


CEmail::accountRequestDelete($user);
//$result = $user->handleDeleteAccountRequest();

//$result = $user->hasPendingDataRequest();
//
//echo $result;