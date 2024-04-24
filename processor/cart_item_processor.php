<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/CCart2.inc");
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CMenuItem.php');

class processor_cart_item_processor extends CPageProcessor
{
	function runPublic()
	{
		header('Content-type: application/json');

		if (is_null($_POST['qty']) || $_POST['qty'] === "" || $_POST['qty'] < 0)
		{
			$_POST['qty'] = 0;
			$_REQUEST['qty'] = 0;
		}

		if (isset($_POST['op']) && $_POST['op'] == 'update')
		{
			if (empty($_POST['item']) || !is_numeric($_POST['item']))
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'result_code' => 2000,
					'processor_message' => 'Invalid menu item id.'
				));
			}

			$sub_items = false;
			if (!empty($_POST['is_bundle']))
			{
				$sub_items = $_POST['is_bundle'];
			}

			$doBundle = false;
			if (!empty($_POST['order_type']) && $_POST['order_type'] == CSession::INTRO)
			{
				$doBundle = true;
			}

			$doDreamTasteEvent = false;
			if (!empty($_POST['order_type']) && ($_POST['order_type'] == CSession::DREAM_TASTE || $_POST['order_type'] == CSession::FUNDRAISER))
			{
				$doDreamTasteEvent = true;
			}

			try
			{
				$CartObj = CCart2::instance(true);
				$cartOrderObj = $CartObj->getOrder();
				$cartMenuID = $CartObj->getMenuId();
				$cartStoreID = $CartObj->getStoreId();
				$cartSessionID = $CartObj->getSessionId();
				$actualSessionType = 'UNKNOWN';
				$servingsInCart = $cartOrderObj->servings_total_count;
				$coreServingsInCart = $cartOrderObj->servings_core_total_count;

				if (!empty($cartSessionID))
				{
					$daoSession = new DAO();
					$daoSession->query("select session_type from session where id = " . $cartSessionID);
					$daoSession->fetch();
					$actualSessionType = $daoSession->session_type;
				}

				// validate that item is correct for session type
				$session_type = $CartObj->getNavigationType();
				$bundleID = false;

				if ($session_type == CSession::INTRO || ($session_type == CSession::EVENT && ($actualSessionType != CSession::STANDARD || $actualSessionType != 'SPECIAL_EVENT')))
				{
					$bundleID = $cartOrderObj->bundle_id; // Is it true that to stick an item in a cart for an intro or event that the bundle id must be set? I believe so.

					// validate intro
					if ($session_type == CSession::INTRO)
					{
						$doBundle = true;

						if ($_POST['qty'] > 1)
						{
							$_POST['qty'] = 1;
						}
					}
				}

				$curItems = $cartOrderObj->getItems();
				$removing = false;

				if (isset($curItems[$_POST['item']]))
				{
					if ($curItems[$_POST['item']][0] > $_POST['qty'])
					{
						// always allow a removal
						$removing = true;
					}
				}

				if ($removing)
				{
					$validated = true;
				}
				else
				{
					list($validated, $message) = CMenuItem::validateItemForOrder($_POST['item'], $_POST['qty'], $cartStoreID, $cartMenuID, $session_type, $bundleID, $actualSessionType, $servingsInCart, $coreServingsInCart, $doBundle);
				}

				if (!$validated)
				{
					CLog::RecordDebugTrace($message . "\r\n" . print_r($_POST, true), "PROCESSOR_CART_ITEM_PROCESSOR_UPDATE");

					$resultCode = 3;
					if (strpos($message, "exceed the number of servings") !== false)
					{
						$resultCode = 5;
					}

					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'result_code' => $resultCode,
						'processor_message' => $message
					));
				}

				$CartObj->updateMenuItem($_POST['item'], $_POST['qty'], $cartMenuID, true, $doBundle, false, $doDreamTasteEvent);

				if (!empty($_POST['qty']) && !empty($sub_items))
				{
					foreach ($sub_items AS $mid => $qty)
					{
						$CartObj->updateMenuItem($mid, $qty, $cartMenuID, true, $doBundle, $_POST['item'], $doDreamTasteEvent, false, true);
					}
				}

				$CartObj = CCart2::instance(true, false, true);

				$tpl = new CTemplate();

				$cartArrays = $CartObj->getCartArrays();
				$menuItem = (!empty($cartArrays['item_info'][$_POST['item']]) ? $cartArrays['item_info'][$_POST['item']] : false);

				$orderSessionType = $cartOrderObj->getSessionType();

				if (!empty($menuItem))
				{
					if ($cartOrderObj->getBundleObj())
					{
						$number_servings_required = $cartOrderObj->getBundleObj()->number_servings_required;
					}
					else
					{
						$number_servings_required = 0;
					}

					$tpl->assign('processor_cart_info_item_info', $menuItem);
					$tpl->assign('cart_info', CUser::getCartIfExists());

					$cart_update = $tpl->fetch('customer/subtemplate/session_menu/session_menu_menu_cart_item.tpl.php');

					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Update menu item.',
						'number_servings_required' => $number_servings_required,
						'order_type' => $orderSessionType,
						'cart_update' => $cart_update,
						'coupon_code_discount_total' => $cartArrays['order_info']['coupon_code_discount_total'],
						'subtotal_meal_customization_fee' => $cartArrays['order_info']['subtotal_meal_customization_fee'],
						'total_items_price' => CTemplate::number_format($cartArrays['cart_info_array']['total_items_price']),
						'grand_total' => CTemplate::number_format($cartArrays["orderObj"]->grand_total)
					));
				}
				else if (empty($_POST['qty']))
				{
					// menu item was set to zero, removed item from cart
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Removed menu item.',
						'order_type' => $orderSessionType,
						'coupon_code_discount_total' => $cartArrays['order_info']['coupon_code_discount_total'],
						'subtotal_meal_customization_fee' => $cartArrays['order_info']['subtotal_meal_customization_fee'],
						'total_items_price' => CTemplate::number_format($cartArrays['cart_info_array']['total_items_price']),
						'grand_total' => CTemplate::number_format($cartArrays["orderObj"]->grand_total)
					));
				}
			}
			catch (Exception $e)
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'result_code' => 3,
					'processor_message' => 'Unexpected error when updating menu item.'
				));
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'result_code' => 1,
				'coupon_code_discount_total' => $cartArrays['order_info']['coupon_code_discount_total'],
				'subtotal_meal_customization_fee' => $cartArrays['order_info']['subtotal_meal_customization_fee'],
				'total_items_price' => CTemplate::number_format($cartArrays['cart_info_array']['total_items_price']),
				'grand_total' => CTemplate::number_format($cartArrays["orderObj"]->grand_total),
				'processor_message' => 'The item was successfully updated.'
			));
		}
		else if (isset($_POST['op']) && $_POST['op'] == 'store_inst')
		{
			if (!isset($_POST['special_inst']))
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'result_code' => 2100,
					'processor_message' => 'Invalid instructions.'
				));
			}

			$xssFilter = new InputFilter();
			$_POST = $xssFilter->process($_POST);

			$CartObj = CCart2::instance(false);
			$CartObj->addSpecialInstructions($_POST['special_inst']);

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'result_code' => 1,
				'processor_message' => 'The special instructions were successfully updated.'
			));
		}
		else if (isset($_POST['op']) && $_POST['op'] == 'remove_bundle')
		{

			$CartObj = CCart2::instance(false);
			$CartObj->removeBundleId();

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'result_code' => 1,
				'processor_message' => 'The special instructions were successfully updated.'
			));
		}
		else if (isset($_POST['op']) && $_POST['op'] == 'remove_all_items')
		{

			$CartObj = CCart2::instance(false);
			$CartObj->clearMenuItems(false,true,true);

			CAppUtil::processorMessageEcho(array(
				'processor_success' => true,
				'result_code' => 1,
				'processor_message' => 'All items were cleared.'
			));
		}
		else
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'result_code' => 2,
				'processor_message' => 'Invalid operation specified.'
			));
		}
	}

}

?>