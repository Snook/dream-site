<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once("includes/DAO/BusinessObject/CCustomerReferral.php");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('page/customer/my_events.php');
require_once('DAO/BusinessObject/CPointsUserHistory.php');
require_once('DAO/BusinessObject/CStoreCredit.php');
require_once('page/admin/order_history.php');
require_once('CMailHandlers.inc');

class processor_admin_order_history extends CPageProcessor
{
	function runEventCoordinator()
	{
		$this->mainProcessor();
	}

	function runOpsLead()
	{
		$this->mainProcessor();
	}

	function runFranchiseStaff()
	{
		$this->mainProcessor();
	}

	function runFranchiseLead()
	{
		$this->mainProcessor();
	}

	function runOpsSupport()
	{
		$this->mainProcessor();
	}

	function runFranchiseManager()
	{
		$this->mainProcessor();
	}

	function runHomeOfficeManager()
	{
		$this->mainProcessor();
	}

	function runFranchiseOwner()
	{
		$this->mainProcessor();
	}

	function runSiteAdmin()
	{
		$this->mainProcessor();
	}

	function mainProcessor()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		// always require a order_id
		if (empty($_POST['user_id']) || !is_numeric($_POST['user_id']))
		{

			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The user id is invalid. '
			));

			exit;
		}

		$user_id = $_POST['user_id'];
		$current_page = $_POST['page'];

		// always require a user_id
		if (empty($_POST['op']))
		{

			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The operation parameter is invalid. '
			));

			exit;
		}

		switch ($_POST['op'])
		{
			case 'next':
				$this->next($user_id, $current_page);
				break;
			case 'prev':
				$this->prev($user_id, $current_page);
				break;
			default:
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Unknown operation parameter'
				));
				exit;
		}
	}

	function next($user_id, $current_page)
	{
		$current_page++;

		$nextPageStart = $current_page * page_admin_order_history::$PAGE_SIZE;
		$data = page_admin_order_history::fetchOrderHistory($user_id, $nextPageStart, page_admin_order_history::$PAGE_SIZE);
		$tpl = new CTemplate();

		$tpl->assign('orders', $data);
		$tpl->assign('user_id', $user_id);

		$DAO_user = DAO_CFactory::create('user');
		$DAO_user->id = $user_id;
		$DAO_user->find(true);
		$tpl->assign('user', $DAO_user->toArray());

		$allowPrev = $current_page > 0;
		$tpl->assign('pagination', true);
		$tpl->assign('pagination_prev', $allowPrev);

		$allowNext = ($data && count($data) > 0);
		$tpl->assign('pagination_next', $allowNext);

		if (!$data)
		{
			$tpl->assign('no_more_rows', true);
		}

		$tpl->assign('active_menus', CMenu::getActiveMenuArray());
		$tpl->assign('page_cur', $current_page);
		$html = $tpl->fetch('admin/subtemplate/order_history/order_history_table.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'Next result found.',
			'html' => $html
		));
		exit;
	}

	function prev($user_id, $current_page)
	{
		$current_page--;
		$nextPageStart = $current_page * page_admin_order_history::$PAGE_SIZE;
		$data = page_admin_order_history::fetchOrderHistory($user_id, $nextPageStart, page_admin_order_history::$PAGE_SIZE);

		$tpl = new CTemplate();

		$tpl->assign('orders', $data);
		$tpl->assign('user_id', $user_id);

		$allowPrev = $current_page > 0;
		$tpl->assign('pagination', true);
		$tpl->assign('pagination_prev', $allowPrev);

		$allowNext = ($data && count($data) > 0);
		$tpl->assign('pagination_next', $allowNext);

		$tpl->assign('active_menus', CMenu::getActiveMenuArray());
		$tpl->assign('page_cur', $current_page);
		$html = $tpl->fetch('admin/subtemplate/order_history/order_history_table.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'Prev result found.',
			'html' => $html
		));
		exit;
	}
}

?>