<?php
/*
 * Created on May 24, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("../../includes/Config.inc");

require_once("DAO/BusinessObject/COrders.php");
require_once("DAO/BusinessObject/CUser.php");
require_once("DAO/BusinessObject/CMenuItem.php");
require_once("DAO/BusinessObject/CMenu.php");
require_once("DAO/BusinessObject/CStore.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");

set_time_limit(100000);
ini_set('memory_limit', '-1');


 try {

 	$orderD = new DAO();
	$orderD->query("select id, order_id, session_time, is_deleted from orders_digest where isnull(session_id) order by id");
	 echo $orderD->N . " order Digest Rows to process\r\n";

	$count = 0;
	while($orderD->fetch())
	{
		$IDGetter = new DAO();
		$count++;

		if ($orderD->is_deleted == 0)
		{
			$IDGetter->query("select session_id from booking where order_id = {$orderD->order_id} and status='ACTIVE' and is_deleted = 0");
			if ($IDGetter->N == 1)
			{
				$IDGetter->fetch();
				$IDSetter = new DAO();
				$IDSetter->query("update orders_digest set session_id = {$IDGetter->session_id} where id = {$orderD->id}");
			}
			else if ($IDGetter->N > 1)
			{
				echo "Multiple ACTIVE bookings for order: " . $orderD->order_id .  "\r\n";
			}
			else
			{
				echo "No ACTIVE bookings for order: " . $orderD->order_id .  "\r\n";
			}
		}
		else
		{
			$IDGetter->query("select session_id from booking where order_id = {$orderD->order_id} and status='CANCELLED' and is_deleted = 0");
			if ($IDGetter->N == 1)
			{
				$IDGetter->fetch();
				$IDSetter = new DAO();
				$IDSetter->query("update orders_digest set session_id = {$IDGetter->session_id} where id = {$orderD->id}");
			}
			else if ($IDGetter->N > 1)
			{
				echo "Multiple CANCELLED bookings for order: " . $orderD->order_id .  "\r\n";
			}
			else
			{
				echo "No CANCELLED bookings for order: " . $orderD->order_id .  "\r\n";
			}
		}

		if ($count % 1000 == 0)
		{
			echo $count .  " rows processed\r\n";
		}

	}



	} catch (exception $e) {
		echo "Error occurred during processing<br>\n";
		echo "reason: " . $e->getMessage();
		CLog::RecordException($e);
	}
?>