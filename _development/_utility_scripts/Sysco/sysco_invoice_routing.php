<?php
define('DEV', false);
define('DOWNLOAD', true);
define('SEND', true);
define('BACKUP', true);


//define('TEST_DESTINATION', 'carl.samuelson@dreamdinners.com');
//define('TEST_REPORT_DEST', 'carl.samuelson@dreamdinners.com');
define('TEST_DESTINATION', false);
define('TEST_REPORT_DEST', false);

if (DEV)
{
	require_once("C:\\Development\\Sites\\DreamSite\\includes\\Config.inc");
}
else
{
	require_once("/DreamReports/includes/Config.inc");
}

require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

set_time_limit(100000);
global $fullReport;

if (DEV)
{
	$tempDirectory = "C:\\Development\\Sites\\DreamSite\\Invoices\\";

}
else
{
	$tempDirectory = "/DreamReports/sysco_invoices/";
}

$storeEmailAdditions = array(
	'280' => 'ashley.knight@dreamdinners.com', //SJ
	'244' => 'ashley.knight@dreamdinners.com' //MC
);

$syscoToDDStoreIDMap = array(
	'581934' => 193,
	'614438' => 53,
	'655743' => 62,
	'929760' => 29,
	'838318' => 268,
	'154455' => 102,
	'160682' => 166,
	'554667' => 239,
	'335067' => 208,
	'638620' => 165, // West Chester, PA, store id contains different ship-to ID
	'666417' => 158,

	'075432' => 82,
	'75432' => 82,

	'664693' => 307,
	'831214' => 181,
	'617076' => 91,
	'421230' => 262,
	'417030' => 232,
	'439018' => 288,
	'596437' => 37,

	//'' => 86,   West Chester, OH  number unknown

	'641696' => 200,

	'060266' => 61,
	'60266' => 61,

	'595645' => 136,
	'988774' => 30,

	'061979' => 63,
	'61979' => 63,

	'068770' => 309,
	'68770' => 309,

	'759670' => 159,

	'079335' => 291,
	'79335' => 291,

	'595009' => 264,
	'981340' => 261,
	'574954' => 215,

	'028863' => 308,

	'356683' => 95,
	'414813' => 281,
	'978637' => 99,

	'032748' => 194,
	'32748' => 194,

	'643114' => 138,

	'173400' => 121, // Old Fountain Valley, mapped to San Marcos

	'163376' => 67,
	'169137' => 80,
	'962605' => 229,
	'165238' => 133,
	'970871' => 241,
	'724906' => 190,
	'683391' => 101,
	'825455' => 28,
	'721399' => 119,
	'641985' => 34,
	'284018' => 108,
	'522037' => 286,
	'452276' => 204,
	'631523' => 171,
	'451096' => 103,

	//'' => 121, //San Marcos - see ** above

	'620963' => 125,

	'061960' => 127,
	'61960' => 127,

	'995498' => 227,
	'918771' => 280, //SJ
	'324459' => 244, //MC
	'926361' => 54,
	'928335' => 175,
	'597625' => 105,
	'702159' => 73,
	'320488' => 85,
	'249854' => 76,
	'320489' => 274,
	'770610' => 96);

function logstr($inStr, $prepend = false)
{
	global $fullReport;
	$msg =  date("Y-m-d H:i:s") .  "\t" . $inStr . "\n";

	if ($prepend)
	{
		$fullReport = $msg . $fullReport;
	}
	else
	{
		$fullReport .= $msg;
	}

	echo $msg;
}

function sendReport($data)
{
	if (TEST_REPORT_DEST)
	{
		$sendMail = mail( "ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com", "Nightly Sysco Invoice Routing Report",
			$data, 'From: <do-not-reply@dreamdinners.com>' );
	}
	else
	{
		$sendMail = mail( "ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com,ashley.knight@dreamdinners.com", "Nightly Sysco Invoice Routing Report",
			$data, 'From: <do-not-reply@dreamdinners.com>' );
	}
}

function isRoutingInvoice($fileName)
{
	if (strpos($fileName, "SYSCO") === 0)
	{
		return true;
	}
	
	return false;
}

function emptyLocalCache()
{
	$cachePath = "/DreamReports/sysco_invoices/";

	$fileCount = 0;
	$dir = new DirectoryIterator($cachePath);
	foreach ($dir as $fileinfo)
	{
		if ($fileinfo->isFile())
		{
			$fileCount++;
			unlink($fileinfo->getPathname());
		}
	}

	logstr("successfully deleted $fileCount file from cache");

}

function sendInvoice($store_id, &$entry, $storeEmailAdditions)
{

	//Allow to all as of 8/15/2022
//	if (!in_array($store_id, array(85,159,200,244,280,28,291,127,274)))
//	{
//		return;
//	}

	$storeDAO = DAO_CFactory::create('store');
	$storeDAO->id = $store_id;
	$storeDAO->find(true);


	$Mail = new CMail();
	$Mail->from_name = 'SYSCO';
	$Mail->to_id = false;
	$Mail->to_name = $storeDAO->store_name;

	if (TEST_DESTINATION)
	{
		$Mail->to_email = 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com';
	}
	else
	{
		$emailString = $storeDAO->email_address;
		if (array_key_exists($storeDAO->id, $storeEmailAdditions)) {
			$emailString = $storeEmailAdditions[$storeDAO->id]. ','.$emailString;
		}
		$Mail->to_email = $emailString;
	}


	$Mail->subject = $storeDAO->store_name . " SYSCO INVOICES " . date("m-d-Y");
	$Mail->body_html = "<html><head></head><body>1 invoice attached</body></html>";
	$Mail->body_text = "1 invoice attached";
	$Mail->template_name = 'none';

	$attachmentFile = array('name' => $entry['filename'], 'type' => 'text/csv', 'tmp_name' => $entry['path'], error => 0, 'size' => $entry['file_size'] );

	$Mail->attachment = $attachmentFile;
	$Mail->sendEmail();

	$entry['sent'] = true;
	logstr("successfully sent to {$entry['filename']} to " . $storeDAO->store_name);
	return true;
}

define('SRC_DIR', "Sysco_FTP_Folder");

function ftp_backup_folder_exists($conn_id, $folderName)
{
	$dir =  "/Invoices_Backups_RP/";
	$contents = ftp_nlist($conn_id, $dir);

	foreach ($contents as $pos => $thisFile)
	{
		if ($thisFile == $folderName)
		{
			return true;
		}
	}

	return false;
}


try {

$fullReport = "";

$ftp_server="ftp.box.com";
$ftp_user_name="boxsupportagent@dreamdinners.com";
$ftp_user_pass="Fj39chnhgiA1b9$";

// set up basic connection
$conn_id = ftp_connect($ftp_server);

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

// try to change the directory to 
if (ftp_chdir($conn_id, SRC_DIR)) {
	logstr("Current directory is now: " . ftp_pwd($conn_id));
} else {
	logstr("Couldn't change directory");
}


ftp_set_option($conn_id, FTP_USEPASVADDRESS, false); // set ftp option

logstr("about to ftp_pasv");
if(ftp_pasv($conn_id,true))
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


$catalogEntry = array('path' => '', 'filename' => '', 'store_id' => false, 'file_size' => 0, 'downloaded' => false, 'sent' => false);
$sendCatalog = array();

$countFiles = 0;
$countDLFiles = 0;
$countSentFiles = 0;
$countNoStoreFiles = 0;

foreach ($contents as $pos => $thisFile)
{
	if ($thisFile != "." && $thisFile != ".." && isRoutingInvoice($thisFile))
	{
		// path to remote file
		$local_file = $tempDirectory . "downloaded_" . $thisFile;
		$name_parts = explode("_", $thisFile);
		$syscoStoreCode = $name_parts[1];

		if (strlen($syscoStoreCode) != 6)
		{
			logstr("Nonstandard sysco store id: $syscoStoreCode");
		}

		$DDStoreID = $syscoToDDStoreIDMap[$syscoStoreCode];

		if (!empty($DDStoreID) && is_numeric($DDStoreID))
		{
			if (!isset($sendCatalog[$DDStoreID]))
			{
				$sendCatalog[$DDStoreID] = array();
			}
			$thisEntry = $catalogEntry;
			$thisEntry['path'] = $local_file;
			$thisEntry['filename'] = $thisFile;
			$thisEntry['store_id'] = $syscoStoreCode;


			$sendCatalog[$DDStoreID][] = $thisEntry;
			$countFiles++;
		}
		else
		{
			logstr("Dream Dinners store ID not found: $syscoStoreCode");
			$countNoStoreFiles++;
		}
	}
}

if (DOWNLOAD)
{

	foreach ($sendCatalog as $DD_STORE_ID => &$fileList)
	{
		foreach ($fileList as &$thisEntry)
		{
			// open some file to write to
			$handle = fopen($thisEntry['path'], 'w');

			if ($handle)
			{
				// try to download $remote_file and save it to $handle
				if (ftp_fget($conn_id, $handle, $thisEntry['filename'], FTP_ASCII, 0))
				{
					$thisEntry['file_size'] = ftp_size($conn_id, $thisEntry['filename']);
					logstr("successfully written to {$thisEntry['path']}");
					$thisEntry['downloaded'] = true;
					$countDLFiles++;
				}
				else
				{
					// no further processing of file with error so remove from array
					logstr("There was a problem while downloading {$thisEntry['filename']} to {$thisEntry['path']}\n" . print_r(error_get_last(), true));
				}

				fclose($handle);
			}
			else
			{
				// no further processing of file with error so remove from array
				logstr("There was a problem while opening local file: {$thisEntry['path']}\n" . print_r(error_get_last(), true));
			}
		}
	}
}

if (SEND)
{
	foreach ($sendCatalog as $DD_STORE_ID => &$fileList)
	{
		foreach ($fileList as &$thisEntry)
		{
			if (sendInvoice($DD_STORE_ID, $thisEntry, $storeEmailAdditions))
			{
				$countSentFiles++;
			}
		}
	}
}

if (BACKUP)
{

	$subFolder = date("M_Y");
	if (!ftp_backup_folder_exists($conn_id, $subFolder))
	{
		// try to create the directory $dir
		if (ftp_mkdir($conn_id, "/Invoices_Backups_RP/" . $subFolder))
		{
			logstr("successfully created backup folder $subFolder");
		}
		else
		{
			logstr("There was a problem while creating backup folder $subFolder");
		}
	}
	else
	{
		logstr("$subFolder folder exists");
	}


	foreach ($sendCatalog as $DD_STORE_ID => &$fileList)
	{
		foreach ($fileList as &$thisEntry)
		{
			if ($thisEntry['downloaded'] && $thisEntry['sent'])
			{
				$thisFile = $thisEntry['filename'];

				// Copy file to backup dir
				$oldLoc = "/" . SRC_DIR . "/" . $thisFile;
				$newLoc = "/Invoices_Backups_RP/" . $subFolder . "/" . $thisFile;

				if (ftp_rename($conn_id, $oldLoc, $newLoc))
				{
					logstr("successfully renamed $oldLoc to $newLoc");
				}
				else
				{
					logstr("There was a problem while renaming $oldLoc to $newLoc\n" . print_r(error_get_last(), true));
				}
			}
		}
	}
}
// close the connection
ftp_close($conn_id);

logstr("Sending of $countSentFiles files Completed Successfully", true);
logstr("downloading of $countDLFiles files Completed Successfully", true);
logstr("$countNoStoreFiles files encountered that did not map to a DreamDinners store id", true);
logstr("$countFiles encoutered.", true);

emptyLocalCache();

} catch(Exception $e)
{
	logstr("Exception: ". $e->getMessage());
}

sendReport($fullReport);

?>