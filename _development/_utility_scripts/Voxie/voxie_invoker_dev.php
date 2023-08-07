<?php
/**
 * DEV - WILL TEST CONNECTION (as specified), WILL NOT TRANSFER, WILL NOT SEND EMAIL
 *
 *
 */
require_once("VoxieDataRouter.inc");

define(DEV_PATH_TO_CONFIG_FILE,"C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
define(DEV_OUPUT_DIRECTORY,"C:\\Development\\output\\voxie\\");

$dataRouter = VoxieDataRouter::getInstance('DEV');
$result = $dataRouter->sendUserSessionDataToVoxie('-1 days', true);

//$dataRouter = VoxieDataRouter::getInstance('DEV');
//$result = $dataRouter->sendUserSessionDataToVoxie('-1 days', true, 'none');

?>