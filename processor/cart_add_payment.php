<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CCouponCode.php");
require_once("includes/DAO/BusinessObject/CPayment.php");

require_once("includes/CCart2.inc");

class processor_cart_add_payment extends CPageProcessor
{
	/**
	 * @throws Exception
	 */
	function runPublic(): void
	{
		header('Content-type: application/json');

		if (isset($_POST['payment_type']))
		{
			if ($_POST['payment_type'] == 'coupon' && isset($_POST['coupon_code']))
			{
				$results_array = self::add_coupon_code($_POST['coupon_code']);

				CAppUtil::processorMessageEcho($results_array);
			}

			if ($_POST['payment_type'] == 'ltd_round_up' && isset($_POST['ltd_round_up_value']))
			{
				$results_array = self::add_ltd_round_up($_POST['ltd_round_up_value']);

				CAppUtil::processorMessageEcho($results_array);
			}

			if ($_POST['payment_type'] == 'delivery_tip' && isset($_POST['value']))
			{
				$results_array = self::add_delivery_tip($_POST['value']);

				CAppUtil::processorMessageEcho($results_array);
			}

			if ($_POST['payment_type'] == 'gift_card')
			{
				$results_array = self::add_gift_card_payment();

				CAppUtil::processorMessageEcho($results_array);
			}

			if ($_POST['payment_type'] == 'credit_card')
			{
				$results_array = self::add_credit_card_payment();

				CAppUtil::processorMessageEcho($results_array);
			}

			if ($_POST['payment_type'] == 'all_store_credit')
			{
				$results_array = self::add_all_store_credit();

				CAppUtil::processorMessageEcho($results_array);
			}

			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'result_code' => 2,
				'processor_message' => 'Payment type not valid.'
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

	/**
	 * @throws Exception
	 */
	static function add_all_store_credit(): array
	{
		$sc_user_id = CUser::getCurrentUser()->id;

		if (!$sc_user_id)
		{
			return array(
				'processor_success' => false,
				'result_code' => 4004,
				'processor_message' => "Invalid user at payment page"
			);
		}

		if (empty($_POST['store_id']) || !is_numeric($_POST['store_id']))
		{
			return array(
				'processor_success' => false,
				'result_code' => 4005,
				'processor_message' => "Invalid store at payment page"
			);
		}

		// Build Store Credits array and checkboxes
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->user_id = $sc_user_id;
		$Store_Credit->store_id = $_POST['store_id'];
		$Store_Credit->is_redeemed = 0;
		$Store_Credit->is_deleted = 0;
		$Store_Credit->is_expired = 0;
		$Store_Credit->find();

		$Store_Credit_Array = array();

		$totalCredit = 0;
		while ($Store_Credit->fetch())
		{
			$Store_Credit_Array[$Store_Credit->id] = array(
				'source' => ($Store_Credit->credit_card_number ?? "none"),
				'total_amount' => $Store_Credit->amount,
				'credit_type' => $Store_Credit->credit_type,
				'store_credit_id' => $Store_Credit->id,
				'date_redeemed' => $Store_Credit->timestamp_created,
				'payment_type' => 'store_credit'
			);

			$totalCredit += $Store_Credit->amount;
		}

		$cart = CCart2::instance(false);
		$cart->addStoreCredit($Store_Credit_Array);

		$html = '<img id="remove-credits" src="' . IMAGES_PATH . '/icon/delete.png" alt="Remove" class="img_valign" data-tooltip="Remove Payment" /><span id="checkout_title-credits"> Store Credits</span>';
		$html .= '<span class="value">(<span id="checkout_total_payment-credits">' . CTemplate::moneyFormat($totalCredit) . '</span>)</span>';

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Payment was successfully stored',
			'total_credit' => $totalCredit,
			'html' => $html
		);
	}

	/**
	 * @throws Exception
	 */
	static function add_credit_card_payment(): array
	{
		$validation_result = CPayment::validateCC($_POST['card_type'], $_POST['card_number'], $_POST['exp_month'], $_POST['exp_year'], $_POST['security_code']);

		if ($validation_result)
		{
			switch ($validation_result)
			{
				case 'invalidtype':
					{
						$errorMessage = "The card type is not valid.";
					}
					break;
				case 'invalidmonth':
					{
						$errorMessage = "The expiration month is not valid.";
					}
					break;
				case 'invalidyear':
					{
						$errorMessage = "The expiration year is not valid.";
					}
					break;
				case 'expired':
					{
						$errorMessage = "The card has expired.";
					}
					break;
				case 'invalidnumber':
					{
						$errorMessage = "The card number is not valid. Did you select the correct card type?";
					}
					break;
				default:
				{
					$errorMessage = "An unknown error occurred when validating the card data.";
				}
			}

			return array(
				'processor_success' => false,
				'result_code' => 2004,
				'processor_message' => $errorMessage
			);
		}

		$cart = CCart2::instance();
		$order = $cart->getOrder();
		$Arr = array(
			'payment_id' => 0,
			'payment_type' => $_POST['payment_type'],
			'total_amount' => 0
		);
		$tempDataArray = array(
			'card_number' => $_POST['card_number'],
			'card_type' => $_POST['card_type'],
			'exp_month' => $_POST['exp_month'],
			'exp_year' => $_POST['exp_year'],
			'security_code' => $_POST['security_code'],
			'name_on_card' => $_POST['name_on_card'],
			'billing_addr' => $_POST['billing_addr'],
			'billing_zip' => $_POST['billing_zip'],
			'do_delayed_payment' => $_POST['do_delayed_payment']
		);

		$tpl = new CTemplate();

		$cart->addPayment($Arr, $tempDataArray);

		$minimum = COrderMinimum::fetchInstance(COrders::STANDARD, $cart->getStoreId(), $cart->getMenuId());
		$minimum->updateBasedOnUserStoreMenu(CUser::getCurrentUser(), $cart->getStoreId(), $cart->getMenuId());

		$tpl->assign('has_gift_card', $cart->hasGiftCard());
		$tpl->assign('foodState', $order->getOrderFoodState(null, $minimum));

		$paymentInfo = array(
			'payment_id' => $Arr['payment_id'],
			'amount' => 0,
			'tempData' => $tempDataArray
		);
		$tpl->assign('paymentInfo', $paymentInfo);
		$tpl->assign('paymentID', $Arr['payment_id']);

		$html = $tpl->fetch('customer/subtemplate/checkout/checkout_total_credit_card_payment_row.tpl.php');

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Payment was successfully stored',
			'payment_id' => $Arr['payment_id'],
			'html' => $html
		);
	}

	static function add_gift_card_payment(): array
	{
		if (empty($_POST['card_number']) || !is_numeric($_POST['card_number']))
		{
			return array(
				'processor_success' => false,
				'result_code' => 2002,
				'processor_message' => 'Invalid Gift Card Number'
			);
		}

		if (empty($_POST['amount']) || !is_numeric($_POST['amount']) || $_POST['amount'] < 0)
		{
			return array(
				'processor_success' => false,
				'result_code' => 2003,
				'processor_message' => 'Invalid Gift Card Amount'
			);
		}

		if (!isset($_POST['payment_id']) || !is_numeric($_POST['payment_id']))
		{
			return array(
				'processor_success' => false,
				'result_code' => 2002,
				'processor_message' => 'Invalid Payment Data'
			);
		}

		$new_gc_amount = sprintf("%01.2f", $_POST['amount']);

		$order_subtotal = sprintf("%01.2f", $_POST['current_subtotal']);

		$cart = CCart2::instance();
		$Arr = array(
			'payment_id' => $_POST['payment_id'],
			'payment_type' => $_POST['payment_type'],
			'total_amount' => $_POST['amount']
		);
		$tempDataArray = array('card_number' => $_POST['card_number']);
		$cart->addPayment($Arr, $tempDataArray);
		$editOrderId = $cart->getEditOrderId();

		$tpl = new CTemplate();

		$paymentInfo = array(
			'payment_id' => $Arr['payment_id'],
			'amount' => $new_gc_amount,
			'tempData' => array('card_number' => $_POST['card_number'])
		);
		$tpl->assign('paymentInfo', $paymentInfo);
		$tpl->assign('paymentID', $Arr['payment_id']);

		$edit_order_html = null;
		if (!empty($editOrderId))
		{
			$tpl->assign('isEditDeliveredOrder', true);

			$original_order_subtotal = sprintf("%01.2f", $_POST['original_order_total']);

			$oTotal = $original_order_subtotal * 100;
			$nTotal = $order_subtotal * 100;

			$tpl->assign('delta_has_new_total', false);
			if ($oTotal != $nTotal)
			{
				$totalDiff = ($nTotal - $oTotal);
				$sumNewGiftCardPayment = $cart->getNewGiftCardPaymentTotal() * 100;
				//$sumNewGiftCardPayment += $new_gc_amount;
				$totalDiff = $totalDiff - $sumNewGiftCardPayment;
				$totalDiff = $totalDiff / 100;
				$tpl->assign('delta_total_diff', $totalDiff);
				$tpl->assign('delta_has_new_total', true);
				$tpl->assign('delta_is_refund', !($totalDiff >= 0));
			}

			$tpl->assign('delta_original_total', $original_order_subtotal);

			$edit_order_html = $tpl->fetch('customer/subtemplate/checkout/checkout_total_edit_order_row.tpl.php');
		}

		$html = $tpl->fetch('customer/subtemplate/checkout/checkout_total_gift_card_payment_row.tpl.php');

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Payment was successfully stored',
			'payment_id' => $Arr['payment_id'],
			'html' => $html,
			'edit_order_html' => $edit_order_html
		);
	}

	static function add_ltd_round_up($roundUpValue): array
	{
		$CartObj = CCart2::instance();
		$Order = $CartObj->getOrder();

		$Order->refresh(CUser::getCurrentUser());
		$Order->recalculate();

		$Order->addLTDRoundUp($roundUpValue);

		$Order->recalculate();
		$CartObj->addOrder($Order);

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'processor_message' => 'Round Up applied to order.'
		);
	}

	static function add_delivery_tip($value): array
	{
		$CartObj = CCart2::instance();
		$DAO_orders = $CartObj->getOrder();

		$DAO_orders->refresh(CUser::getCurrentUser());
		$DAO_orders->recalculate();

		$DAO_orders->addDeliveryTip($value);

		$DAO_orders->recalculate();
		$CartObj->addOrder($DAO_orders);

		return array(
			'processor_success' => true,
			'result_code' => 1,
			'delivery_tip' => $DAO_orders->delivery_tip,
			'grand_total' => $DAO_orders->grand_total,
			'processor_message' => 'Tip applied to order.'
		);
	}

	/**
	 * @throws Exception
	 */
	static function add_coupon_code($couponCode): array
	{
		$CartObj = CCart2::instance();
		$DAO_orders = $CartObj->getOrder();
		$menu_id = $CartObj->getMenuId();
		$editOrderId = $CartObj->getEditOrderId();

		if (CUser::isloggedIn() && empty($DAO_orders->user_id))
		{
			$DAO_orders->user_id = CUser::getCurrentUser()->id;
		}

		$DAO_orders->refresh(CUser::getCurrentUser());
		$DAO_orders->recalculate();

		if ($DAO_orders->isDelivered())
		{
			$DAO_coupon_code = CCouponCode::isCodeValidForDelivered($couponCode, $DAO_orders, $menu_id);
		}
		else
		{
			$DAO_coupon_code = CCouponCode::isCodeValid($couponCode, $DAO_orders, $menu_id);
		}

		if (gettype($DAO_coupon_code) !== "object" || get_class($DAO_coupon_code) !== 'CCouponCode')
		{

			$isLoggedin = false;
			if (CUser::isLoggedIn())
			{
				$isLoggedin = true;
			}

			$errors = '';
			foreach ($DAO_coupon_code as $thisError)
			{
				if ($thisError == 'user_not_existing' && !$isLoggedin)
				{
					$errors .= CCouponCode::getCouponErrorUserText($thisError) . ' <b>You may need to log into to apply this coupon</b><br />';
				}
				else
				{
					$errors .= CCouponCode::getCouponErrorUserText($thisError) . '<br />';
				}
			}

			$results_array = array(
				'processor_success' => false,
				'result_code' => 2,
				'processor_message' => $errors
			);
		}
		else
		{
			$bounce_to = false;
			$cart_update = false;
			$menu_item_id = false;
			$totalQty = false;
			if ($DAO_coupon_code->limit_to_recipe_id)
			{
				$DAO_coupon_code->calculate($DAO_orders, $DAO_orders->getMarkUp());

				$DAO_menu = DAO_CFactory::create('menu', true);
				$DAO_menu->id = $menu_id;
				$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
					'menu_to_menu_item_store_id' => $DAO_orders->getStoreObj()->id,
					'exclude_menu_item_category_core' => false,
					'exclude_menu_item_category_efl' => false,
					'exclude_menu_item_category_sides_sweets' => false,
					'menu_item_id_list' => $DAO_coupon_code->menu_item_id
				));

				if ($DAO_menu_item->find(true))
				{
					if ($DAO_menu_item->recipe_id == $DAO_coupon_code->recipe_id)
					{
						$totalQty = $DAO_orders->addCouponMenuItem($DAO_menu_item);

						$tpl = new CTemplate();
						$tpl->assign('menu_item', $DAO_menu_item);
						$tpl->assign('cart_info', CUser::getCartIfExists());

						$menu_item_id = $DAO_menu_item->id;
						$cart_update = $tpl->fetch('customer/subtemplate/session_menu/session_menu_menu_cart_item.tpl.php');

						if (!empty($_POST['page']) && $_POST['page'] == 'checkout')
						{
							$bounce_to = '/checkout';
						}
					}
					else
					{
						CLog::RecordDebugTrace("Coupon ID: " . $DAO_coupon_code->id . " Order Id:" . $DAO_orders->id ."\r\n" . print_r($_POST, true), "COUPON_CODE_RECIPE_MISMATCH", 1, CLog::DEBUG, true);
					}
				}
			}

			$DAO_orders->addCoupon($DAO_coupon_code);

			$CartObj->addOrder($DAO_orders);

			$DAO_orders->recalculate();
			$OrderCoupon = $DAO_orders->getCoupon();

			$couponDetails = new stdClass();
			$couponDetails->coupon_code_short_title = $OrderCoupon->coupon_code_short_title;
			$couponDetails->limit_to_mfy_fee = $OrderCoupon->limit_to_mfy_fee;
			$couponDetails->limit_to_delivery_fee = $OrderCoupon->limit_to_delivery_fee;
			$couponDetails->coupon_code_discount_total = $DAO_orders->coupon_code_discount_total;

			$results_array = array(
				'processor_success' => true,
				'result_code' => 1,
				'processor_message' => 'Coupon applied to order.',
				'coupon' => $couponDetails,
				'coupon_title' => $DAO_coupon_code->coupon_code_short_title,
				'cart_update' => $cart_update,
				'menu_item_id' => $menu_item_id,
				'qty_in_cart' => $totalQty,
				'orderInfo' => $DAO_orders->toArray()
			);

			if ($bounce_to)
			{
				$results_array['bounce_to'] = '/checkout';
			}

			if (!empty($editOrderId))
			{
				//get original to fine price updates
				$originalOrder = new COrdersDelivered();
				$originalOrder->id = $editOrderId;
				$originalOrder->find(true);

				$originalOrder->refresh(CUser::getCurrentUser());
				$originalOrder->reconstruct();

				$sumNewGiftCardPayment = $CartObj->getNewGiftCardPaymentTotal();

				$totalPriceDiff = $DAO_orders->grand_total - $sumNewGiftCardPayment - $originalOrder->grand_total;
				$results_array['balance'] = CTemplate::moneyFormat($totalPriceDiff);
				$results_array['originalOrderInfo'] = $originalOrder->toArray();
				$results_array['edit_order_diff'] = true;
			}
		}

		return $results_array;
	}
}