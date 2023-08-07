<?php

/**
 * relies on vaiables set in /includes/config/envi.config.file
 *
 * These are:
 * VOXIE_FTP_SERVER - Voxie's sftp server
 * VOXIE_USER - sftp user to connect with
 * VOXIE_PRIVATE_KEY - Used if using the PHP SFTP Library for sending file
 * VOXIE_PRIVATE_KEY_PATH - used using command line method for sending file
 * to Voxie
 *
 * VOXIE_SEND_ATTACHMENT - indicate if a copy of the report should be attached
 * to the email notice
 */
require_once("VoxieDataRouter.inc");

//**********************************************************************
//***MAIN entry point for daily CRONJOB on CHORS which runs at 4:OOAM Eastern Time
//**********************************************************************

$dataRouter = VoxieDataRouter::getInstance('LIVE');
$dataRouter->sendUserSessionDataToVoxie('-1 months');
?>