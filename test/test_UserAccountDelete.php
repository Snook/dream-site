<?php
require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once("DAO/BusinessObject/CUser.php");


$user = new CUser();
$user->id = 430427;
$user->find(true);


CEmail::accountRequestDelete($user);
//$result = $user->handleDeleteAccountRequest();

//$result = $user->hasPendingDataRequest();
//
//echo $result;


