<?php
require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
require_once("DAO/BusinessObject/CSession.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CUser.php");

//CSession::generateWalkInSessionsForMenu(257);

CSession::generateDeliveredSessionsForMenu(256);
