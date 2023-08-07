<?php
/**
 * TEST - WILL TEST CONNECTION (as specified), WILL NOT TRANSFER, WILL SEND EMAIL
 */
require_once("VoxieDataRouter.inc");

//  common setups for TEST on CHORS
//
$dataRouter = VoxieDataRouter::getInstance('TEST');
$dataRouter->sendUserSessionDataToVoxie('-10 days', true);
//
//$dataRouter = VoxieDataRouter::getInstance('TEST');
//$dataRouter->sendUserSessionDataToVoxie('-10 days', true, 'none');
?>