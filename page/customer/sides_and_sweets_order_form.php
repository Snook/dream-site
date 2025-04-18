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
	function runPublic(): void
	{
		CApp::forceLogin();
	}

	/**
	 * @throws Exception
	 */
	function runCustomer(): void
	{
		$Form = new CForm;
		$Form->Repost = true;
		$Form->Bootstrap = true;

		//get user
		$DAO_user = CUser::getCurrentUser();

		$date = date('Y.m.d', strtotime("-1 days"));

		//get the user's next 3 orders
		$orderInfoArray = COrders::getUsersOrders($DAO_user, 3, $date, false, false, array('STANDARD'), 'asc', false, true);

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
				reset($orderInfoArray);

				$req_order_id = key($orderInfoArray);
			}

			$selectedOrder = $orderInfoArray[$req_order_id];

			foreach ($orderInfoArray as $order)
			{
				$orderOptions[$order['id']] = CTemplate::sessionTypeDateTimeFormat($order['session_start'], $order['session_type_subtype'], VERBOSE) . ' - ' . COrders::getCustomerActionStringFrom($order['fully_qualified_order_type']) . ' - ' . $order['store_name'];
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
			$store = CStore::getStoreAndOwnerInfo($DAO_user->home_store_id);
		}

		//sort menu item
		$this->Template->assign('store_info', $store);
		$this->Template->assign('menu_items', $menuItems);
		$this->Template->assign('form', $Form->Render());
		$this->Template->assign('haveOrders', !empty($orderInfoArray));
		$this->Template->assign('user', $DAO_user);

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
				$this->Template->setStatusMsg('There was an error with your submission, no menu items were selected.');
			}
			else
			{
				$emailVars = array(
					'store' => $store,
					'order_details' => $selectedOrder,
					'user' => $DAO_user,
					'desired_items' => $desiredMenuItems,
					'payment' => ((!empty($_POST['payment']) && $_POST['payment'] == 'card_on_file') ? 'Use credit card on file' : 'Guest will pay at session'),
					'use_dinner_dollars' => ((!empty($_POST['Use_Dinner_Dollars']) && $_POST['Use_Dinner_Dollars'] == 1) ? 'Yes' : 'No')
				);

				$Mail = new CMail();
				$Mail->from_name = null;
				$Mail->from_email = null;
				$Mail->to_name = $store[0]['store_name'];
				$Mail->to_email = $store[0]['email_address'];
				$Mail->subject = "Sides and Sweets Order Request for " . $DAO_user->firstname . ' ' . $DAO_user->lastname;
				$Mail->body_html = CMail::mailMerge('sides_sweets_order_request.html.php', $emailVars);
				$Mail->body_text = CMail::mailMerge('sides_sweets_order_request.txt.php', $emailVars);
				$Mail->reply_email = $DAO_user->primary_email;
				$Mail->template_name = 'sides_sweets_order_request';
				$Mail->sendEmail();

				$Mail = new CMail();
				$Mail->from_name = $store[0]['store_name'];
				$Mail->from_email = $store[0]['email_address'];
				$Mail->to_name = $DAO_user->firstname . ' ' . $DAO_user->lastname;
				$Mail->to_email = $DAO_user->primary_email;
				$Mail->subject = "Sides and Sweets Order Request Confirmation";
				$Mail->body_html = CMail::mailMerge('sides_sweets_order_confirmation.html.php', $emailVars);
				$Mail->body_text = CMail::mailMerge('sides_sweets_order_confirmation.txt.php', $emailVars);
				$Mail->reply_email = $store[0]['email_address'];
				$Mail->template_name = 'sides_sweets_order_confirmation';
				$Mail->sendEmail();

				$eventTime = date("Y-m-d H:i:s");
				$typeId = CStoreActivityLog::determineStoreActivityTypeId(CStoreActivityLog::SIDES_ORDER, CStoreActivityLog::SUBTYPE_SIDES_FORM);
				$data = CStoreActivityLog::renderTemplate('sides_n_sweets_form_alert.tpl.php', $emailVars);
				CStoreActivityLog::addEvent($store[0]['id'], $data, $eventTime, $typeId);

				CApp::bounce_SubmissionComplete(message: "Your Sides and Sweets order request has been sent to the store, Thank you.");
			}
		}
	}

}