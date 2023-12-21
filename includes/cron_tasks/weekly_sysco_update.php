<?php
require_once(dirname(__FILE__) . "/../Config.inc");
require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

require_once 'includes/CSysco_EDI_Parser.php';

set_time_limit(100000);
global $fullReport;

$tempDirectory = "/DreamReports/temp/";

function logstr($inStr)
{
	global $fullReport;
	$msg = date("Y-m-d H:i:s") . "\t" . $inStr . "\n";
	$fullReport .= $msg;
	echo $msg;
}

function sendReport($data)
{
	$sendMail = mail("ryan.snook@dreamdinners.com, josh.thayer@dreamdinners.com, lorie.wiseman@dreamdinners.com", "Weekly Sysco Input Report", $data, 'From: <do-not-reply@dreamdinners.com>');
}

function isRoutingInvoice($fileName)
{
	if (strpos($fileName, "SYSCO") === 0)
	{
		return true;
	}

	return false;
}

try
{


	$fullReport = "";

	$ftp_server = "ftp.box.com";
	$ftp_user_name = "boxsupportagent@dreamdinners.com";
	$ftp_user_pass = "Fj39chnhgiA1b9$";

	// set up basic connection
	$conn_id = ftp_connect($ftp_server);

	// login with username and password
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

	// try to change the directory to
	if (ftp_chdir($conn_id, "Sysco_FTP_Folder"))
	{
		logstr("Current directory is now: " . ftp_pwd($conn_id));
	}
	else
	{
		logstr("Couldn't change directory");
	}

	ftp_set_option($conn_id, FTP_USEPASVADDRESS, false); // set ftp option

	logstr("about to ftp_pasv");
	if (ftp_pasv($conn_id, true))
	{
		logstr("success with ftp_pasv");
	}
	else
	{
		logstr("failure with ftp_pasv");
		exit;
	}

	// get contents of the current directory
	logstr("about to ftp_nlist");
	$contents = ftp_nlist($conn_id, ".");
	logstr("done with ftp_nlist");

	/*
	foreach ($contents as $pos => $thisFile)
	{
		if ($thisFile != "." && $thisFile != ".." && !isRoutingInvoice($thisFile))
		{
				echo $thisFile ."\r\n";
		}
	}
	*/

	foreach ($contents as $pos => $thisFile)
	{
		if ($thisFile != "." && $thisFile != ".." && !isRoutingInvoice($thisFile))
		{
			// path to remote file
			$local_file = $tempDirectory . "downloaded_" . $thisFile;

			// open some file to write to
			$handle = fopen($local_file, 'w');

			// try to download $remote_file and save it to $handle
			if (ftp_fget($conn_id, $handle, $thisFile, FTP_ASCII, 0))
			{
				logstr("successfully written to $local_file");
			}
			else
			{
				// no further processing of file with error so remove from array
				unset($contents[$pos]);
				logstr("There was a problem while downloading $thisFile to $local_file\n" . print_r(error_get_last(), true));
			}

			fclose($handle);
		}
	}

	$dateStr = date("Y-m-d_H_i_s");

	$output_file = $tempDirectory . "converted_edi_" . $dateStr . ".csv";

	$outputData = "";

	$count = 0;
	foreach ($contents as $pos => $thisFile)
	{

		if ($thisFile != "." && $thisFile != ".." && !isRoutingInvoice($thisFile))
		{
			$resultArr = array();

			$local_file = $tempDirectory . "downloaded_" . $thisFile;

			$result = CSysco_EDI_Parser::parseFile($local_file, false, $resultArr);
			// if output file is set to false (2nd param) then the contents are returned as text

			if ($result)
			{
				$outputData .= $result;
				$count++;
			}
			else
			{
				logstr("There was a problem while converting $local_file");
			}

			foreach ($resultArr as $msg)
			{
				logstr($msg);
			}
		}
	}

	// save the files
	$fpd = fopen($output_file, 'w');
	if ($fpd === false)
	{
		logstr("parseFile script: fopen failed");

		return false;
	}

	$length = fputs($fpd, $outputData);
	fclose($fpd);

	foreach ($contents as $thisFile)
	{

		if ($thisFile != "." && $thisFile != ".." && !isRoutingInvoice($thisFile))
		{

			// Copy file to backup dir
			$oldLoc = "/Sysco_FTP_Folder/" . $thisFile;
			$newLoc = "/Invoice_Backups/" . $thisFile;

			if (ftp_rename($conn_id, $oldLoc, $newLoc))
			{
				logstr("successfully renamed $oldLoc to $newLoc");
			}
			else
			{
				logstr("There was a problem while renaming $oldLoc to $newLoc");
			}
		}
	}

	// copy converted file to finaldest
	$fp = fopen($output_file, 'r');
	if ($fp === false)
	{
		logstr("parseFile script: fopen failed");

		return false;
	}

	// try to upload $file
	$remoteFile = "/Converted Invoices/converted_" . $dateStr . ".csv";

	if (ftp_fput($conn_id, $remoteFile, $fp, FTP_BINARY))
	{
		logstr("Successfully uploaded $remoteFile");
	}
	else
	{
		logstr("There was a problem while uploading $remoteFile");
	}

	// close the connection and the file handler
	fclose($fp);

	// close the connection
	ftp_close($conn_id);

	logstr("Conversion of $count files Completed Successfully");
}
catch (Exception $e)
{
	logstr("Exception: " . $e->getMessage());
}
sendReport($fullReport);

?>