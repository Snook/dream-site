<?php
require_once('includes/CPageAdminOnly.inc');
require_once('includes/DAO/BusinessObject/CBundle.php');

class page_admin_reports_map_activity extends CPageAdminOnly
{
	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeManager()
	{
		$this->sseRun();
	}

	function runSiteAdmin()
	{
		$this->sseRun();
	}

	function sseRun()
	{
		$tpl = CApp::instance()->template();
/*
		$orders = DAO_CFactory::create('orders');
		$orders->query("SELECT
			SUM(ltd_round_up_value)+SUM(subtotal_ltd_menu_item_value) AS foundation_total,
			Sum(o.servings_total_count) AS servings_total_count
			FROM (SELECT
				b.id,
				b.user_id,
				b.timestamp_updated,
				b.timestamp_created,
				b.session_id,
				b.order_id
				FROM booking AS b
				INNER JOIN `session` AS s ON s.id = b.session_id AND s.is_deleted = '0'
				WHERE b.is_deleted = '0'
				AND b.`status` = 'ACTIVE'
				AND b.timestamp_updated < DATE_SUB(NOW(), INTERVAL 60 MINUTE)
				ORDER BY b.timestamp_updated DESC) AS b
			INNER JOIN `orders` AS o ON o.id = b.order_id AND o.is_deleted = 0");
		$orders->fetch();

		$tpl->assign('foundation_total', $orders->foundation_total);
		$tpl->assign('servings_total_count', $orders->servings_total_count);
*/

		$tpl->assign('foundation_total', 0);
		$tpl->assign('servings_total_count', 0);
	}
}
?>
