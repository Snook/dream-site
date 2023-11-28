<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("includes/DAO/BusinessObject/COrdersDigest.php");
require_once("CLog.inc");
require_once("CAppUtil.inc");

echo COrdersDigest::determineCustomerOrderType('123123');
echo PHP_EOL;
echo COrdersDigest::determineCustomerOrderType('529093');
echo PHP_EOL;
echo COrdersDigest::determineCustomerOrderType('639612');