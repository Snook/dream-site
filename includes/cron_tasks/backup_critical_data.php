<?php

require_once("../Config.inc");
//require_once("C:\Users\Carl.Samuelson\Zend\workspaces\DefaultWorkspace12\DreamSite\includes\Config.inc");

require_once("CLog.inc");
require_once("DAO/BusinessObject/CDreamTasteEvent.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CTimezones.php");
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');

restore_error_handler();

	try {
		
		$password = DB_SERVER_PASSWORD;
		$folderName = "/DreamSite/backups/";
		$filename = $folderName . "store_menu_data_" . date("Y-m-d-H-i") . ".sql";
		
		echo system("mysqldump --no-create-info=true --replace --single-transaction -uroot -p$password dreamsite menu_to_menu_item --where='menu_id>188' > $filename");
		
		if (file_exists($folderName)) {
			foreach (new DirectoryIterator($folderName) as $fileInfo) {
				if ($fileInfo->isDot()) {
					continue;
				}
				if ($fileInfo->isFile() && time() - $fileInfo->getCTime() >= 2*24*60*60) {
					unlink($fileInfo->getRealPath());
				}
			}
		}

	} catch (exception $e) {

		CLog::RecordException($e);
	}

?>