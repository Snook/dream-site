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
require_once("DAO/BusinessObject/COrdersDigest.php");
require_once("DAO/CFactory.php");
require_once("CLog.inc");
require_once('processor/admin/order_mgr_processor.php');

set_time_limit(100000);
ini_set('memory_limit', '-1');

try
{

	$affectedOrders = DAO_CFactory::create('orders_digest');
	$affectedOrders->query("select
		s.store_id,
		st.state_id,
		st.city,
		st.store_name,
		st.email_address,
		m.menu_name,
		b.order_id,
		od.in_store_trigger_order,
		o.menu_items_core_total_count,
		o.servings_core_total_count,
		IFNULL(om.minimum_type, 'SERVING') AS minimum_type,
		b.user_id,
		u.firstname,
		u.lastname,
		u.primary_email,
		u.telephone_1,
		CONCAT('https://dreamdinners.com/main.php?page=admin_order_history&id=',u.id,'&order=',o.id) as order_history
		from booking as b
		join orders as o on b.order_id = o.id and o.is_deleted = 0 and o.in_store_order = 0 and (o.menu_items_core_total_count >= 3 OR o.servings_core_total_count >= 36)
		join `session` as s on b.session_id = s.id and s.is_deleted = 0
		join store as st on st.id = s.store_id
		join `user` as u on b.user_id = u.id
		join menu as m on s.menu_id = m.id
		left join order_minimum as om on s.menu_id = om.menu_id and s.store_id = om.store_id
		left join orders_digest as od on od.order_id = o.id and od.is_deleted 
		where b.`status` = 'ACTIVE'
		and ((b.timestamp_updated > '2023-02-26 21:00:00') OR (b.timestamp_created > '2023-02-26 21:00:00'))
		HAVING ((minimum_type = 'ITEM' AND o.menu_items_core_total_count >= 3) OR (minimum_type = 'SERVING' AND o.servings_core_total_count >= 36))
		ORDER BY st.state_id, st.city, st.store_name, s.menu_id ");

	while ($affectedOrders->fetch())
	{
		$processor = new processor_admin_order_mgr_processor();

		$processor->prepareForDirectCall(false, $affectedOrders->store_id, false, $affectedOrders->order_id);

		$processor->updateOrderStatusFlag('in-store_status', 1, false);
	}
}
catch (exception $e)
{
	echo "Error occurred during processing<br>\n";
	echo "reason: " . $e->getMessage();
	CLog::RecordException($e);
}
?>