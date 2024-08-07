<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once("includes/DAO/BusinessObject/CCustomerReferral.php");
require_once("CTemplate.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('page/customer/my_events.php');
require_once('DAO/BusinessObject/CPointsUserHistory.php');
require_once('DAO/BusinessObject/CStoreCredit.php');
require_once('CMailHandlers.inc');

class processor_admin_user_plate_points extends CPageProcessor
{

	private $PAGE_SIZE = 15;

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

		// always require a user_id
		if (empty($_POST['user_id']) || !is_numeric($_POST['user_id']))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The user_id is invalid. '
			));

			exit;
		}

		$user_id = $_POST['user_id'];
		$current_page = (int)$_POST['page'] ?: 0;

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
				$next_page = $_POST['next'];
				$this->next($user_id, $current_page);
				break;
			case 'prev':
				$prev_page = $_POST['prev'];
				$this->prev($user_id, $current_page);
				break;
			case 'dd-next':
				$next_page = $_POST['next'];
				$this->dinner_dollar_next($user_id, $current_page);
				break;
			case 'dd-prev':
				$prev_page = $_POST['prev'];
				$this->dinner_dollar_prev($user_id, $current_page);
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

		$nextPageStart = $current_page * $this->PAGE_SIZE;
		$limit = $nextPageStart . ',' . $this->PAGE_SIZE;
		$data = CPointsUserHistory::getHistory($user_id, $limit);
		$tpl = new CTemplate();

		$tpl->assign('rows', $data);
		$tpl->assign('user_id', $user_id);

		$allowPrev = $current_page > 0;
		$tpl->assign('pagination', true);
		$tpl->assign('pagination_prev', $allowPrev);

		$allowNext = ($data && count($data) > 0);
		$tpl->assign('pagination_next', $allowNext);

		if (!$data)
		{
			$tpl->assign('no_more_rows', true);
		}

		$tpl->assign('page_cur', $current_page);
		$html = $tpl->fetch('admin/subtemplate/user_plate_points/user_plate_points_table.tpl.php');

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

		$nextPageStart = $current_page * $this->PAGE_SIZE;
		$limit = $nextPageStart . ',' . $this->PAGE_SIZE;
		$data = CPointsUserHistory::getHistory($user_id, $limit);

		$tpl = new CTemplate();

		$tpl->assign('rows', $data);
		$tpl->assign('user_id', $user_id);

		$allowPrev = $current_page > 0;
		$tpl->assign('pagination', true);
		$tpl->assign('pagination_prev', $allowPrev);

		$allowNext = ($data && count($data) > 0);
		$tpl->assign('pagination_next', $allowNext);

		$tpl->assign('page_cur', $current_page);
		$html = $tpl->fetch('admin/subtemplate/user_plate_points/user_plate_points_table.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'Prev result found.',
			'html' => $html
		));
		exit;
	}

	function dinner_dollar_next($user_id, $current_page)
	{
		$current_page++;

		$nextPageStart = $current_page * $this->PAGE_SIZE;
		$limit = $nextPageStart . ',' . $this->PAGE_SIZE;
		$data = CStoreCredit::fetchDinnerDollarHistoryByUser($user_id, 'rowcount', $limit);

		$tpl = new CTemplate();

		$tpl->assign('dd_history', $data);
		$tpl->assign('user_id', $user_id);

		$allowPrev = $current_page > 0;
		$tpl->assign('dinner_dollar_pagination', true);
		$tpl->assign('dinner_dollar_pagination_prev', $allowPrev);

		$allowNext = ($data && count($data) > 0);
		$tpl->assign('dinner_dollar_pagination_next', $allowNext);

		if (!$data)
		{
			$tpl->assign('dinner_dollar_no_more_rows', true);
		}

		$tpl->assign('dinner_dollar_page_cur', $current_page);
		$html = $tpl->fetch('admin/subtemplate/user_dinner_dollars/user_dinner_dollars_table.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'Next result found.',
			'html' => $html
		));
		exit;
	}

	function dinner_dollar_prev($user_id, $current_page)
	{
		$current_page--;

		$nextPageStart = $current_page * $this->PAGE_SIZE;
		$limit = $nextPageStart . ',' . $this->PAGE_SIZE;
		$data = CStoreCredit::fetchDinnerDollarHistoryByUser($user_id, 'rowcount', $limit);

		$tpl = new CTemplate();

		$tpl->assign('dd_history', $data);
		$tpl->assign('user_id', $user_id);

		$allowPrev = $current_page > 0;
		$tpl->assign('dinner_dollar_pagination', true);
		$tpl->assign('dinner_dollar_pagination_prev', $allowPrev);

		$allowNext = ($data && count($data) > 0);
		$tpl->assign('dinner_dollar_pagination_next', $allowNext);

		$tpl->assign('dinner_dollar_page_cur', $current_page);
		$html = $tpl->fetch('admin/subtemplate/user_dinner_dollars/user_dinner_dollars_table.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'Prev result found.',
			'html' => $html
		));
		exit;
	}
}

?>