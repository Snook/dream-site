<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

set_time_limit(100000);
ini_set('memory_limit', '-1');

define("TO_SQL_FILE", true);

if (isset($argv[1]))
{
	$startingID = $argv[1];
}
else
{
	echo 'failure';

	return;
}

try
{

	if (TO_SQL_FILE)
	{
		$path = "/DreamReports/update_new_totals.sql";
		//$path = "C:\\Development\\Sites\\DreamSite\\Recent_Scripts\\update_new_totals.sql";
		$fh = fopen($path, 'a');
	}

	$Orders = DAO_CFactory::create('orders');
	$Orders->query("SELECT o.* FROM `orders` o
						join booking b on b.order_id = o.id and b.status = 'ACTIVE' and b.is_deleted = 0
						join session s on s.id = b.session_id and s.session_start > '2019-08-01 00:00:00' and s.session_start < '2022-02-28 00:00:00' and s.session_type <> 'DELIVERED'
						where o.id > $startingID
						order by o.id limit 9500");
	echo $Orders->N . " Non-Delivered Rows to process\r\n";

	$lastID = 0;
	$count = 0;
	while ($Orders->fetch())
	{

		$ThisOrder = clone($Orders);
		$ThisOrder->tempReCalc();

		if (TO_SQL_FILE)
		{
			$length = fputs($fh, "update orders set pcal_core_total = {$ThisOrder->pcal_core_total}, menu_items_core_total_count = {$ThisOrder->menu_items_core_total_count} where id = {$ThisOrder->id};\r\n");
		}
		else
		{
			$Saver = new DAO();
			$Saver->query("update orders set pcal_core_total = {$ThisOrder->pcal_core_total}, menu_items_core_total_count = {$ThisOrder->menu_items_core_total_count} where id = {$ThisOrder->id}");
		}

		$count++;
		$lastID = $ThisOrder->id;

		if ($count % 500 == 0)
		{
			echo "$count processed...\r\n";
		}
	}

	if (TO_SQL_FILE)
	{
		fclose($fh);
	}

	echo $lastID;

	return;
}
catch (exception $e)
{
	echo "error occurred at count: $count\r\n";
	echo "Error occurred during processing<br>\n";
	echo "reason: " . $e->getMessage();
	CLog::RecordException($e);
}
?>