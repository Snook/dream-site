<?php
require_once("../includes/Config.inc");
require_once("DAO/BusinessObject/COrdersDelivered.php");
require_once("DAO/BusinessObject/CUserAccountManagement.php");


echo CUserAccountManagement::createTask(430427,CUserAccountManagement::ACTION_DELETE_ACCOUNT);