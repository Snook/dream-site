<?php
require_once('DAO/BusinessObject/CFoodSurvey.php');
require_once('DAO/BusinessObject/COrders.php');

class page_my_meals extends CPage
{

	public static $PAGE_SIZE = 10;

	function runPublic()
	{
		CApp::forceLogin(returnUrl: CApp::instance()->template()->bounceBackUrl(currentUrl: true));
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		ini_set('memory_limit', '1024M');

		$tpl->assign('no_rate_orders', true);
		$tpl->assign('no_past_orders', true);

		$req_order_id = CGPC::do_clean((!empty($_GET['order']) ? $_GET['order'] : false), TYPE_INT);
		$req_search = CGPC::do_clean((!empty($_GET['search']) ? $_GET['search'] : false), TYPE_NOHTML, true);

		if (!empty($req_search))
		{
			$req_order_id = 'all';
		}

		$User = CUser::getCurrentUser();
		$tpl->assign('user_id', $User->id);

		$ordersArray = COrders::getUsersOrders($User, false, false, false, false, false, 'desc');

		$orderHistoryArray = COrders::getUsersOrders($User, '0,' . self::$PAGE_SIZE, false, false, false, false, 'desc');

		if (!empty($orderHistoryArray))
		{
			$tpl->assign('no_past_orders', false);
			$tpl->assign('orders', $orderHistoryArray);

			//paging control
			$totalFetchedOrder = count($ordersArray);
			$shouldPage = $totalFetchedOrder > (self::$PAGE_SIZE - 3);
			$tpl->assign('pagination', $shouldPage);
		}
		else
		{
			$tpl->assign('pagination', false);
		}

		$tpl->assign('no_more_rows', false);
		if (!$ordersArray)
		{
			$tpl->assign('no_more_rows', true);
		}

		$tpl->assign('pagination_prev', false);
		$tpl->assign('pagination_next', true);
		$tpl->assign('page_cur', 0);

		$showOrders = array();

		foreach ($ordersArray as $order_id => $orderDetails) // catch first one that is active as default to display, should be the latest session attended
		{
			if ($orderDetails['status'] == 'COMPLETED' && $orderDetails['can_rate_my_meals'])
			{
				$showOrders[$order_id] = $orderDetails;

				if (empty($req_order_id))
				{
					$req_order_id = $order_id;
				}
				break;
			}
		}

		if (!empty($req_order_id) && is_numeric($req_order_id) && in_array($req_order_id, array_keys($ordersArray))) // specific order requested
		{
			$order_id = $req_order_id;

			if ($ordersArray[$order_id]['can_rate_my_meals'])
			{
				$showOrders = array($order_id => $ordersArray[$order_id]);
			}
		}
		else if (!empty($req_search) && $req_order_id == 'all')
		{
			$showOrders = $ordersArray;
		}

		if (!empty($showOrders))
		{
			$tpl->assign('no_rate_orders', false);

			if (!empty($req_search))
			{
				require_once('includes/class.inputfilter_clean.php');

				// food search
				$xssFilter = new InputFilter();
				$req_search = $xssFilter->process($req_search);

				list($displayArray, $recipesArray) = COrders::getMenuItemsForOrder($showOrders, $req_search, '2012-01-01 00:00:00');

				$tpl->assign('no_search_results', false);

				if (empty($recipesArray))
				{
					$tpl->assign('no_search_results', true);
				}

				$tpl->assign('food_search', htmlentities($req_search));
			}
			else
			{
				list($displayArray, $recipesArray) = COrders::getMenuItemsForOrder($showOrders, false, '2012-01-01 00:00:00');
			}

			$ratingsArray = CFoodSurvey::getUsersRatedRecipes($User, $recipesArray);

			$displayRecipes = array();

			foreach ($displayArray as $order_id => $orderDetails)
			{
				$displayArray[$order_id] = array_merge($displayArray[$order_id], $ordersArray[$order_id]);

				foreach ($displayArray[$order_id]['recipes'] as $element_id => $itemDetail)
				{
					// $displayRecipes: if a newer order has already listed the recipe, don't show it again
					if (in_array($element_id, $displayRecipes))
					{
						unset($displayArray[$order_id]['recipes'][$element_id]);
					}
					else if (!empty($ratingsArray[$element_id]))
					{
						$displayRecipes[] = $element_id;

						$displayArray[$order_id]['recipes'][$element_id] = array_merge($displayArray[$order_id]['recipes'][$element_id], $ratingsArray[$element_id]);
					}
				}

				// $displayRecipes: if the loop has cleared out all of the recipes from the order, remove the order from display
				if (empty($displayArray[$order_id]['recipes']))
				{
					unset($displayArray[$order_id]);
				}
			}

			$tpl->assign('recipes', $displayArray);
		}
	}
}