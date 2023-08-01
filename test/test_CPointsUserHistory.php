<?php


require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");

require_once 'includes/DAO/BusinessObject/CPointsUserHistory.php';
require_once 'includes/DAO/BusinessObject/CUser.php';



$user = new CUser();
$user->id = 740939;
$user->find(true);

$infoArray = array();

$history = new CPointsUserHistory();
$history->convertPointsToCredit($user, true, $infoArray);

print_r($infoArray);
?>