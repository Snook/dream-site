<?php
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/CStoreActivityLog.php');

function sortBySize($a, $b)
{
	if ($a['pricing_type'] == CMenuItem::TWO)
	{
		return -1;
	}
	else if ($a['pricing_type'] == CMenuItem::FULL)
	{
		return 1;
	}
	else if ($a['pricing_type'] == CMenuItem::FOUR)
	{
		if ($b['pricing_type'] == CMenuItem::TWO || $b['pricing_type'] == CMenuItem::HALF)
		{
			return 1;
		}
		else
		{
			return -1;
		}
	}
	else if ($a['pricing_type'] == CMenuItem::HALF)
	{
		if ($b['pricing_type'] == CMenuItem::TWO)
		{
			return 1;
		}
		else if ($b['pricing_type'] == CMenuItem::FOUR || $b['pricing_type'] == CMenuItem::FULL)
		{
			return -1;
		}
	}

	return 0;
}

function sortByCategoryLabelAlpha($itemOne, $itemTwo)
{

	if ($itemOne['category_id'] != 9)
	{
		return -1;
	}
	if ($itemTwo['category_id'] != 9)
	{
		return 1;
	}
	$labelOne = strtolower($itemOne['subcategory_label']);
	$labelTwo = strtolower($itemTwo['subcategory_label']);

	if ($labelOne == $labelTwo)
	{
		//prioritize bundles
		if ($itemOne['is_bundle'])
		{
			return -1;
		}
		if ($itemTwo['is_bundle'])
		{
			return 1;
		}

		return sortBySize($itemOne, $itemTwo);
	}
	else
	{
		return strcmp($labelOne, $labelTwo);
	}
}

class page_sides_and_sweets_order_form extends CPage
{
	function runPublic()
	{
		$back = '?' . $_SERVER['QUERY_STRING'];
		CApp::forceLogin($back);
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$Form = new CForm;
		$Form->Repost = true;
		$Form->Bootstrap = true;

		//get user
		$user = CUser::getCurrentUser();

		$date = date('Y.m.d', strtotime("-1 days"));

		//get the user's next 3 orders
		$orderInfoArray = COrders::getUsersOrders($user, 3, $date, false, false, array('STANDARD'), 'asc', false, true);

		$req_order_id = false;
		$orderOptions = array();
		$menuItems = array();
		$selectedOrder = false;

		if (!empty($orderInfoArray))
		{
			if (!empty($_GET['id']) && is_numeric($_GET['id']))
			{
				if (!empty($orderInfoArray[$_GET['id']]))
				{
					$req_order_id = $_GET['id'];
				}
			}
			else if (!empty($_POST['orders']) && is_numeric($_POST['orders']))
			{
				if (!empty($orderInfoArray[$_POST['orders']]))
				{
					$req_order_id = $_POST['orders'];
				}
			}
			else
			{
				if (!empty($orderInfoArray))
				{
					reset($orderInfoArray);

					$req_order_id = key($orderInfoArray);
				}
			}

			$selectedOrder = $orderInfoArray[$req_order_id];

			foreach ($orderInfoArray as $order)
			{
				$orderOptions[$order['id']] = CTemplate::sessionTypeDateTimeFormat($order['session_start'], $order['session_type_subtype'], VERBOSE, false, false, false) . ' - ' . COrders::getCustomerActionStringFrom($order['fully_qualified_order_type']) . ' - ' . $order['store_name'];
			}

			$Form->DefaultValues['orders'] = $req_order_id;

			$Form->addElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'orders',
				CForm::options => $orderOptions
			));
		}

		if ($selectedOrder)
		{
			$store_id = $selectedOrder['store_id'];
			$menu_id = $selectedOrder['menu_id'];

			$store = CStore::getStoreAndOwnerInfo($store_id);

			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->id = $menu_id;
			$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
				'menu_to_menu_item_store_id' => $store_id,
				'exclude_menu_item_category_core' => true,
				'exclude_menu_item_category_efl' => false,
				'exclude_menu_item_category_sides_sweets' => false
			));

			while ($DAO_menu_item->fetch())
			{
				if (!$DAO_menu_item->isBundle() && !$DAO_menu_item->isShiftSetGo() && $DAO_menu_item->hasAvailableInventory() && ($DAO_menu_item->showOnOrderForm() || $DAO_menu_item->showOnPickSheet()))
				{
					$menuItems[$DAO_menu_item->id] = $DAO_menu_item->cloneObj();
				}
			}
		}
		else
		{
			$store = CStore::getStoreAndOwnerInfo($user->home_store_id);
		}

		//sort menu item
		$tpl->assign('store_info', $store);
		$tpl->assign('menu_items', $menuItems);
		$tpl->assign('form', $Form->Render());
		$tpl->assign('haveOrders', !empty($orderInfoArray));
		$tpl->assign('user', $user);

		//items have been ordered, send store an email
		if (!empty($_POST['submit']) && $_POST['submit'] == 'submit')
		{
			$desiredMenuItems = array();

			foreach ($_POST['menu_item'] as $menu_item_id => $quantity)
			{
				if (!empty($quantity) && !empty($menuItems[$menu_item_id]))
				{
					$desiredMenuItems[$menu_item_id] = array(
						'item_detail' => $menuItems[$menu_item_id],
						'quantity_desired' => $quantity
					);
				}
			}

			if (empty($desiredMenuItems))
			{
				$tpl->setStatusMsg('There was an error with your submission, no menu items were selected.');
			}
			else
			{
				$emailVars = array(
					'store' => $store,
					'order_details' => $selectedOrder,
					'user' => $user,
					'desired_items' => $desiredMenuItems,
					'payment' => ((!empty($_POST['payment']) && $_POST['payment'] == 'card_on_file') ? 'Use credit card on file' : 'Guest will pay at session'),
					'use_dinner_dollars' => ((!empty($_POST['Use_Dinner_Dollars']) && $_POST['Use_Dinner_Dollars'] == 1) ? 'Yes' : 'No')
				);

				$Mail = new CMail();
				$Mail->from_name = null;
				$Mail->from_email = null;
				$Mail->to_name = $store[0]['store_name'];
				//$Mail->to_email = 'brandy.latta@dreamdinners.com';
				$Mail->to_email = $store[0]['email_address'];
				$Mail->subject = "Sides and Sweets Order Request for " . $user->firstname . ' ' . $user->lastname;
				$Mail->body_html = CMail::mailMerge('sides_sweets_order_request.html.php', $emailVars);
				$Mail->body_text = CMail::mailMerge('sides_sweets_order_request.txt.php', $emailVars);
				$Mail->reply_email = $user->primary_email;
				$Mail->template_name = 'admin_generic';

				$Mail->sendEmail();

				date_default_timezone_set('America/New_York');
				$eventTime = date("Y-m-d H:i:s");
				$typeId = CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::SIDES_ORDER, CStoreActivityLog::SUBTYPE_SIDES_FORM);
				$data = CStoreActivityLog::renderTemplate('sides_n_sweets_form_alert.tpl.php', $emailVars);
				CStoreActivityLog::addEvent($store[0]['id'], $data, $eventTime, $typeId);

				$tpl->setStatusMsg('Your Sides and Sweets Order Request has been sent to the store, Thank you.');

				CApp::bounce("/my-account");
			}
		}
	}

}
?>