<?php
require_once("../includes/Config.inc");
require_once('includes/CCartStorage.inc');
require_once('includes/CLog.inc');


//$storage = new CCartStorage();
//echo print_r($storage->retrieveArray('13127129', 'MENU_ITEMS', false), true);

//$link = new CSFMCLink();
//$link->attemptLogin();
//$out = SalesForceMarketingManager::getInstance()->invokeAbandonedCartJourney();


//CCartStorage::detectAbandonedCartRows(10);

//CCartStorage::fetchAbandonedCartRows(10);

$contactKeyResult = QrCodeMonkeyManager::getInstance()->fetchContactKeyByEmail('ryan.snook@dreamdinners.com');
//
$contactKey = $contactKeyResult->getPayload();
//
QrCodeMonkeyManager::getInstance()->invokeAbandonedCartJourney($contactKey,'ryan.snook@dreamdinners.com', 'Ryan','1c0d3f4a26ff32cb07baa4a3bb1627b8');




//$result = SalesForceMarketingManager::getInstance()->fetchContactKeyByEmail('laundryagain@gmail.com');