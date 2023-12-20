<?php
ini_set('memory_limit', '-1');
set_time_limit(3600 * 24);

define('DEV', false);

if (DEV)
{
	require_once(dirname(__FILE__) . "/../includes/Config.inc");
	require_once("DAO/BusinessObject/CUser.php");
	require_once("DAO/CFactory.php");
	require_once("CLog.inc");

	define('EXEC', "\"C:\\Development\\Xampp\\php\\php.exe\"");
	define('SCRIPT_CMD', "\"C:\\Development\\Sites\\DreamSite\\Recent_Scripts\\Update_core_item_count.php\"");
	define('BASE_PATH', "C:\\Development\\Sites\\DreamSite\\Recent_Scripts\\");
}
else
{
	require_once(dirname(__FILE__) . "/../includes/Config.inc");
	require_once("DAO/BusinessObject/CUser.php");
	require_once("DAO/CFactory.php");
	require_once("CLog.inc");

	define('EXEC', "/usr/bin/php");
	define('SCRIPT_CMD', "/DreamReports/Update_core_item_count.php");
	define('BASE_PATH', "/DreamReports/");
}

// ----------------------------------------------------------  Begin Processing ---------------------------------------------------
try
{

	$startingID = 3403920;
	$running = true;
	while ($running)
	{
		$cmd = EXEC . " " . SCRIPT_CMD . " " . "\"$startingID\"";
		$result = system($cmd);

		if (is_numeric($result) && $result > 0)
		{
			$startingID = $result;
		}
		else
		{
			$running = false;
		}
	}
}
catch (exception $e)
{
	CLog::RecordException($e);
	logstr($e->getMessage());
}

?>