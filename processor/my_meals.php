<?php
require_once("includes/CPageProcessor.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('DAO/BusinessObject/CFoodSurvey.php');
require_once('page/customer/my_meals.php');

class processor_my_meals extends CPageProcessor
{
	function runPublic()
	{
		echo json_encode(array(
			'success' => false,
			'message' => 'Not logged in'
		));
	}

	function runCustomer()
	{
		$this->processData();
	}

	function runFranchiseStaff()
	{
		$this->processData();
	}

	function runFranchiseManager()
	{
		$this->processData();
	}

	function runOpsLead()
	{
		$this->processData();
	}

	function runFranchiseOwner()
	{
		$this->processData();
	}

	function runHomeOfficeStaff()
	{
		$this->processData();
	}

	function runHomeOfficeManager()
	{
		$this->processData();
	}

	function runSiteAdmin()
	{
		$this->processData();
	}

	function processData()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		$result = array(
			'processor_success' => false,
			'processor_message' => 'No data specified'
		);

		$User = CUser::getCurrentUser();

		if ($User && !empty($_POST['operation']))
		{
			$req_recipe_id = CGPC::do_clean((!empty($_POST['recipe_id']) ? $_POST['recipe_id'] : false), TYPE_INT, true);
			$req_recipe_version = CGPC::do_clean((!empty($_POST['recipe_version']) ? $_POST['recipe_version'] : false), TYPE_INT, true);
			$req_store_id = CGPC::do_clean((!empty($_POST['store_id']) ? $_POST['store_id'] : false), TYPE_INT, true);

			switch ($_POST['operation'])
			{
				case 'next':
					$current_page = $_POST['page'];
					$this->next($User, $current_page);
					break;
				case 'prev':
					$current_page = $_POST['page'];
					$this->prev($User, $current_page);
					break;
				case 'rate':
					$req_rating = CGPC::do_clean((!empty($_POST['rating']) ? $_POST['rating'] : false), TYPE_INT, true);
					$result = CFoodSurvey::addMyMealsRating($User, $req_recipe_id, $req_recipe_version, $req_rating, $req_store_id);
					CAppUtil::processorMessageEcho($result);
					break;
				case 'favorite':
					$req_set_favorite = CGPC::do_clean((!empty($_POST['set_favorite']) ? $_POST['set_favorite'] : false), TYPE_BOOL, true);
					$result = CFoodSurvey::addMyMealsFavorite($User, $req_recipe_id, $req_recipe_version, $req_set_favorite);
					CAppUtil::processorMessageEcho($result);
					break;
				case 'review':
					$req_comment = CGPC::do_clean((!empty($_POST['comment']) ? $_POST['comment'] : false), TYPE_NOHTML, true);
					$result = CFoodSurvey::addMyMealsReview($User, $req_recipe_id, $req_recipe_version, $req_comment);
					CAppUtil::processorMessageEcho($result);
					break;
				case 'note':
					$req_comment = CGPC::do_clean((!empty($_POST['comment']) ? $_POST['comment'] : false), TYPE_NOHTML, true);
					$result = CFoodSurvey::addMyMealsPersonalNote($User, $req_recipe_id, $req_recipe_version, $req_comment);
					CAppUtil::processorMessageEcho($result);
					break;
				default:
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Unknown operation parameter'
					));
			}
		}
		else
		{
			$result = array(
				'processor_success' => false,
				'processor_message' => 'No operation specified'
			);
		}

		CAppUtil::processorMessageEcho($result);
	}

	function next($User, $current_page)
	{
		$current_page++;

		$nextPageStart = $current_page * page_my_meals::$PAGE_SIZE;
		$limit = $nextPageStart . ',' . page_my_meals::$PAGE_SIZE;

		$ordersArray = COrders::getUsersOrders($User, $limit, false, false, false, false, 'desc',false, false, true);

		$tpl = new CTemplate();

		$tpl->assign('orders', $ordersArray);
		$tpl->assign('user_id', $User->id);

		$allowPrev = $current_page > 0;
		$tpl->assign('pagination', true);
		$tpl->assign('pagination_prev', $allowPrev);

		$allowNext = ($ordersArray && count($ordersArray) > 0);
		$tpl->assign('pagination_next', $allowNext);

		$tpl->assign('no_more_rows', false);
		if (!$ordersArray)
		{
			$tpl->assign('no_more_rows', true);
		}

		$tpl->assign('page_cur', $current_page);
		$html = $tpl->fetch('customer/subtemplate/my_meals/my_meals_orders.tpl.php');

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'Next result found.',
			'html' => $html
		));
	}

	function prev($User, $current_page)
	{
		$current_page--;

		$nextPageStart = $current_page * page_my_meals::$PAGE_SIZE;
		$limit = $nextPageStart . ',' . page_my_meals::$PAGE_SIZE;
		$ordersArray = COrders::getUsersOrders($User, $limit, false, false, false, false, 'desc',false, false, true);

		$tpl = new CTemplate();

		$tpl->assign('orders', $ordersArray);
		$tpl->assign('user_id', $User->id);

		$allowPrev = $current_page > 0;
		$tpl->assign('pagination', true);
		$tpl->assign('pagination_prev', $allowPrev);


		$tpl->assign('no_more_rows', false);
		if (!$ordersArray)
		{
			$tpl->assign('no_more_rows', true);
		}

		$allowNext = ($ordersArray && count($ordersArray) > 0);
		$tpl->assign('pagination_next', $allowNext);

		$tpl->assign('page_cur', $current_page);
		$html = $tpl->fetch('customer/subtemplate/my_meals/my_meals_orders.tpl.php');

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'Prev result found.',
			'html' => $html
		));
	}
}

?>