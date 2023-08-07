<?php
/**
 * Various Tools
 */
require_once("VoxieDataRouter.inc");


//***********************************
//******Misc Tools
//
//Test Connection
$dataRouter = VoxieDataRouter::getInstance('DEV');
$dataRouter->testVoxieSFTP_Connection_withPK(new ResultToken(true));
//
//Resend a file
//$resultToken = new ResultToken(true);
//$resultToken->setPayload('C:\\Development\\output\\voxie\\voxie_report_2023_07_10-12_33_27.csv');

//$dataRouter->resendOnlyToVoxie('command',$resultToken);
//$dataRouter->resendOnlyToVoxie('client',$resultToken);
?>