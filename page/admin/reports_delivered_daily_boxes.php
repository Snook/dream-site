<?php // page_admin_create_store.php

/**
 * @author Lynn Hook
 */

require_once ('includes/CPageAdminOnly.inc');
require_once ('includes/CSessionReports.inc');
require_once ('includes/DAO/Booking.php');
require_once ('includes/DAO/BusinessObject/CSession.php');

class page_admin_reports_delivered_daily_boxes extends CPageAdminOnly
{
	private $currentStore = null;

	function __construct()
	{
		parent::__construct();
		$this->cleanReportInputs();
	}

	function runHomeOfficeStaff()
	{
		$this->runSiteAdmin();
	}
	function runHomeOfficeManager()
	{
		$this->runSiteAdmin();
	}
	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}
	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}
	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}
	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}
	function runOpsSupport()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}
 	function runFranchiseOwner()
 	{
	 	$this->currentStore = CApp::forceLocationChoice();
		$this->runSiteAdmin();
	}

 	function runSiteAdmin()
 	{

		$tpl = CApp::instance()->template();

		$daoDistCtr = null;
 		if (isset($_REQUEST['store_id']) && is_numeric($_REQUEST['store_id']))
		{
			$daoDistCtr = DAO_CFactory::create('store');
			$daoDistCtr->id = $_REQUEST['store_id'];
			$daoDistCtr->find(true);
		}

		if ($_REQUEST['type'] == 'delivering')
		{
			$tpl->assign('TypeStr', "DELIVERING");
		}
		else
		{
			$tpl->assign('TypeStr', "SHIPPING");
		}

		$sessionClause = "";
 		$sessionJoinClause = "";
 		$shippingJoin = "";
 		$spanDate = null;
		$endSpanDate = null;
		if (isset($_REQUEST['date']))
		{
			$spanDate = date("Y-m-d 00:00:00", strtotime($_REQUEST['date']));
			$endSpanDate = date("Y-m-d 00:00:00", strtotime($spanDate) + 86400);

			if ($_REQUEST['type'] == 'delivering')
			{
				$sessionJoinClause = "join session s on s.id = b.session_id and DATE(s.session_start) = '{$_REQUEST['date']}' ";
			}
			else
			{
				$shippingJoin = " and os.ship_date >= '$spanDate' and  os.ship_date < '$endSpanDate' ";
			}

		}
		else if (isset($_REQUEST['session']) && is_numeric($_REQUEST['session']))
		{
			$sessionDao = DAO_CFactory::create('session');
			$sessionDao->id = $_REQUEST['session'];
			$sessionDao->find(true);
			$spanDate = date("Y-m-d 00:00:00", strtotime($sessionDao->session_start));
			$endSpanDate = date("Y-m-d 00:00:00", strtotime($spanDate) + 86400);

			if ($_REQUEST['type'] == 'delivering')
			{
				$sessionClause = " and b.session_id = " . $_REQUEST['session'] . " ";
			}
			else
			{
				$shippingJoin = " and os.ship_date >= '$spanDate' and  os.ship_date < '$endSpanDate' ";
			}
		}

		// getOrders/Boxes
		$boxData = new DAO();

		// TESTING
		//	$spanDate = '2021-08-01 00:00:00';
		//	$endSpanDate = '2021-08-20 00:00:00';

		$boxData->query("select os.order_id, os.requested_delivery_date, os.service_days, oi.box_instance_id, oi.bundle_id, oi.menu_item_id, mi.menu_item_name,mi.pricing_type,
												  mi.entree_id, mi.servings_per_item, oi.item_count, bu.bundle_name,
										bx.title, m.menu_name, o.user_id, CONCAT(u.firstname, ' ', u.lastname) as name from booking b
								$sessionJoinClause
								join orders_shipping os on b.order_id = os.order_id $shippingJoin
								join orders o on o.id = os.order_id and o.store_id = {$daoDistCtr->id}
								join order_item oi on oi.order_id = os.order_id and oi.is_deleted = 0
								join menu_item mi on mi.id = oi.menu_item_id
								join bundle bu on bu.id = oi.bundle_id
								join box_instance bi on bi.id = oi.box_instance_id
								join box bx on bx.id = bi.box_id
								join menu m on m.id = bu.menu_id
								join user u on u.id = o.user_id
								where b.status = 'ACTIVE' and b.is_deleted = 0 $sessionClause");


		$entreeCounts= array();
		$boxesArray = array();

		$box_number = 1;
		while ($boxData->fetch())
		{
			if (!isset($boxesArray[$boxData->order_id]))
			{
				$boxesArray[$boxData->order_id] = array('info' => array(), 'boxes' => array());

				$boxesArray[$boxData->order_id]['info']['name'] = $boxData->name;
				$boxesArray[$boxData->order_id]['info']['delivery_date'] = $boxData->requested_delivery_date;
				$boxesArray[$boxData->order_id]['info']['menu'] = $boxData->menu_name;
				$box_number = 0;
			}

			if (!isset($boxesArray[$boxData->order_id]['boxes'][$boxData->box_instance_id]))
			{
				$boxesArray[$boxData->order_id]['boxes'][$boxData->box_instance_id] = array();
				$box_number++;
			}


			if (!isset($boxesArray[$boxData->order_id]['boxes'][$boxData->box_instance_id]['box_name']))
			{
				$boxesArray[$boxData->order_id]['boxes'][$boxData->box_instance_id]['box_name'] = "#" . $box_number . " " . $boxData->title . " - " . $boxData->bundle_name;
			}

			if (!isset($boxesArray[$boxData->order_id]['boxes'][$boxData->box_instance_id]['items']))
			{
				$boxesArray[$boxData->order_id]['boxes'][$boxData->box_instance_id]['items'] = array();
			}


			$boxesArray[$boxData->order_id]['boxes'][$boxData->box_instance_id]['items'][$boxData->menu_item_id] =  $boxData->item_count  . " " . $boxData->menu_item_name;


			//$boxesArray[$boxData->box_instance_id]['rawData'] = DAO::getCompressedArrayFromDAO($boxData);

			if (!isset($entreeCounts[$boxData->entree_id]))
			{
				$entreeCounts[$boxData->entree_id] = array('name' =>$boxData->menu_item_name, 'medium_count' => 0, 'large_count' => 0, 'total_servings' => 0);
			}

			if ($boxData->pricing_type == CMenuItem::FULL)
			{
				$entreeCounts[$boxData->entree_id]['large_count'] += $boxData->item_count;
				$entreeCounts[$boxData->entree_id]['total_servings'] += ($boxData->item_count * $boxData->servings_per_item);
			}
			else
			{
				$entreeCounts[$boxData->entree_id]['medium_count'] += $boxData->item_count;
				$entreeCounts[$boxData->entree_id]['total_servings'] += ($boxData->item_count * $boxData->servings_per_item);
			}
		}

		$tpl->assign('orderInfo', $boxesArray);
		$tpl->assign('storeInfo', DAO::getCompressedArrayFromDAO($daoDistCtr));
		$tpl->assign('entreeCounts', $entreeCounts);
		$tpl->assign('Date', $spanDate);


	}

}
?>