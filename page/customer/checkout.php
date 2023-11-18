<?php // menus.php
require_once('includes/CCart2.inc');
require_once('includes/class.inputfilter_clean.php');
require_once('includes/CEditOrderPaymentManager.inc');
require_once('includes/OrdersHelper.php');
require_once('form/account.php');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('includes/DAO/BusinessObject/COrderMinimum.php');
require_once('includes/DAO/BusinessObject/CPayment.php');
require_once('includes/DAO/BusinessObject/CStore.php');
require_once('includes/DAO/BusinessObject/CUser.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CPointsCredits.php');
require_once('includes/DAO/BusinessObject/CDreamTasteEvent.php');

/**
 * Class box_comparator
 */
class box_comparator
{
	/**
	 * compare the boxes on two different versions of an order, collect information about differences
	 *
	 * @param       $originalBox
	 * @param       $updatedBox
	 * @param false $reverseKey
	 *
	 * @return array
	 */
	static function compareDeliveredBoxes($originalBox, $updatedBox, $reverseKey = false)
	{
		$difference = array();

		if (box_comparator::areBoxesEquivalent($originalBox, $updatedBox))
		{
			//no changes
			return $difference;
		}

		//collect changes
		box_comparator::determineBoxDifferences($originalBox, $updatedBox, $difference);

		box_comparator::determineBundleDifferences($originalBox, $updatedBox, $difference);

		box_comparator::determineItemDifferences($originalBox, $updatedBox, $difference);

		return $difference;
	}

	//Determines if a box was added or removed from the new version of the order
	private static function determineBoxDifferences($originalBox, $updatedBox, &$difference)
	{
		//box added or removed
		$boxChanges = array();

		//compare boxes if same id then no more work
		foreach ($originalBox as $boxInstanceId => $boxData)
		{
			if (!array_key_exists($boxInstanceId, $updatedBox))
			{
				$delta = new stdClass();
				$delta->type = 'BOX_INSTANCE';
				$delta->ref = $boxInstanceId;
				$delta->action = 'REMOVED';
				$delta->description = 'Box Instance ' . $boxInstanceId . ' was removed.';
				$delta->description_html = '<li>Removed ' . $boxData['box_instance']->box->title . ' (' . $boxData['bundle']->bundle_name . ')</li>';

				$boxChanges[] = $delta;
			}
		}

		foreach ($updatedBox as $boxInstanceId => $boxData)
		{
			if (!array_key_exists($boxInstanceId, $originalBox))
			{
				$delta = new stdClass();
				$delta->type = 'BOX_INSTANCE';
				$delta->ref = $boxInstanceId;
				$delta->action = 'ADDED';
				$delta->description = 'Box Instance  ' . $boxInstanceId . ' was added.';
				$delta->description_html = '<li>Added ' . $boxData['box_instance']->title . ' (' . $boxData['bundle']->bundle_name . ')</li>';
				$boxChanges[] = $delta;
			}
		}

		if (count($boxChanges) > 0)
		{
			$delta = new stdClass();
			$delta->type = 'EDIT_ORDER';
			$delta->ref = null;
			$delta->action = 'UPDATED';
			$delta->description = 'Order was updated.';
			$delta->description_html = "<br><h3>Update Boxes</h3><ul style='margin: 0px; padding: 0px;'>";
			$difference[] = $delta;

			$difference = array_merge($difference, $boxChanges);
			$difference[] = "</ul>";
		}
	}

	//Determines if a bundle was added or removed from the new version of the order
	//Since we get a new box to get a new bundle this will alway mirror determineBoxDifferences()
	private static function determineBundleDifferences($originalBox, $updatedBox, &$difference)
	{
		$restructuredOrginal = array();
		foreach ($originalBox as $boxInstance => $boxInstanceData)
		{
			$bundleId = $boxInstanceData['bundle']->id;
			$restructuredOrginal[$bundleId] = $boxInstanceData['items'];
		}

		$restructuredNew = array();
		foreach ($updatedBox as $boxInstance => $boxInstanceData)
		{
			$bundleId = $boxInstanceData['bundle']->id;
			$restructuredNew[$bundleId] = $boxInstanceData['items'];
		}

		foreach ($restructuredOrginal as $bundleId => $items)
		{
			if (!array_key_exists($bundleId, $restructuredNew))
			{
				$delta = new stdClass();
				$delta->type = 'BUNDLE';
				$delta->ref = $bundleId;
				$delta->action = 'REMOVED';
				$delta->description = 'Bundle ' . $bundleId . ' was removed.';
				//$delta->description_html = '<br>Bundle ' . $bundleId . ' was removed.';
				$difference[] = $delta;
			}
		}

		foreach ($restructuredNew as $bundleId => $items)
		{
			if (!array_key_exists($bundleId, $restructuredOrginal))
			{
				$delta = new stdClass();
				$delta->type = 'BUNDLE';
				$delta->ref = $bundleId;
				$delta->action = 'ADDED';
				$delta->description = 'Bundle ' . $bundleId . ' was added.';
				//$delta->description_html = '<br>Bundle '.$bundleId.' was added.';
				$difference[] = $delta;
			}
		}
	}

	//Determine total item and item qty changes across all boxes, so at the order level. Not possible to
	//determine per box because to change an item or an item qty a box must be removed and added.
	//It looks like a different box in the code so we cant really say what 'changed' at the box level..
	private static function determineItemDifferences($originalBox, $updatedBox, &$difference)
	{
		$itemsChanges = array();

		$itemNameMap = array();
		$uniqueOriginalItemQtys = array();
		foreach ($originalBox as $boxInstance => $boxInstanceData)
		{
			$qty = 1;
			foreach ($boxInstanceData['items'] as $itemId => $item)
			{
				$itemNameMap[$itemId] = $item[1]->menu_item_name;
				$newQty = $uniqueOriginalItemQtys[$itemId] + $item[0];
				$uniqueOriginalItemQtys[$itemId] = $newQty;
			}
		}

		$uniqueNewItemQtys = array();
		foreach ($updatedBox as $boxInstance => $boxInstanceData)
		{

			foreach ($boxInstanceData['items'] as $itemId => $item)
			{
				$itemNameMap[$itemId] = $item[1]->menu_item_name;
				$newQty = $uniqueNewItemQtys[$itemId] + $item[0];
				$uniqueNewItemQtys[$itemId] = $newQty;
			}
		}

		foreach ($uniqueOriginalItemQtys as $itemId => $previousQty)
		{
			$newQty = array_key_exists($itemId, $uniqueNewItemQtys) ? $uniqueNewItemQtys[$itemId] : 0;
			if ($previousQty == $newQty)
			{
				continue;
			}

			$delta = new stdClass();
			$delta->type = 'ITEM';
			$delta->ref = $itemId;
			$delta->action = 'QTY INCREASED';
			if ($previousQty > $newQty)
			{
				$delta->action = 'QTY DECREASED';
			}
			$delta->description = 'Total item ' . $itemId . ' qyt changed from ' . $previousQty . ' to ' . $newQty;
			if ($newQty != 0)
			{
				$delta->description_html = '<li>' . $itemNameMap[$itemId] . ' total order quantity changed from ' . $previousQty . ' to ' . $newQty . '</li>';
			}

			$itemsChanges[] = $delta;
		}

		foreach ($uniqueNewItemQtys as $itemId => $newQty)
		{
			if (array_key_exists($itemId, $uniqueOriginalItemQtys))
			{
				//already captured above
				continue;
			}

			$delta = new stdClass();
			$delta->type = 'ITEM';
			$delta->ref = $itemId;
			$delta->action = 'QTY INCREASED';
			$delta->description = 'Total item ' . $itemId . ' qyt changed from 0 to ' . $newQty;
			if ($newQty > 0)
			{
				$delta->description_html = '<li>Add ' . $newQty . ' ' . $itemNameMap[$itemId] . '</li>';
			}

			$itemsChanges[] = $delta;
		}

		if (count($itemsChanges) > 0)
		{
			$delta = new stdClass();
			$delta->type = 'EDIT_ITEMS';
			$delta->ref = null;
			$delta->action = 'UPDATED';
			$delta->description = 'Total items were updated.';
			$delta->description_html = "<br><h3>Updated Items</h3><ul style='margin: 0px; padding: 0px;'>";
			$difference[] = $delta;

			$difference = array_merge($difference, $itemsChanges);
			$difference[] = "</ul>";
		}
	}

	//Returns true if same number of boxes/bundles with the same items
	//and item quantities across all boxes
	private static function areBoxesEquivalent($originalBox, $updatedBox)
	{

		//if box count same and the box id same, no change
		if (count($originalBox) == count($updatedBox))
		{
			$diff = array_diff_key($originalBox, $updatedBox);
			$diffRev = array_diff_key($updatedBox, $originalBox);
			if (count($diff) == 0 && count($diffRev) == 0)
			{
				//no change
				return true;
			}
		}

		$uniqueOriginalBundleIds = array();
		$uniqueOriginalItemQtys = array();
		$count = 0;
		foreach ($originalBox as $boxInstance => $boxInstanceData)
		{
			$count++;
			$bundleId = $boxInstanceData['bundle']->id;
			$uniqueOriginalBundleIds[$bundleId] = $count;

			foreach ($boxInstanceData['items'] as $itemId => $item)
			{
				$itemQtyKey = $itemId . '_' . $item[0];
				$uniqueOriginalItemQtys[$itemQtyKey] = 1;
			}
		}

		$uniqueNewBundleIds = array();
		$uniqueNewItemQtys = array();
		foreach ($updatedBox as $boxInstance => $boxInstanceData)
		{
			$count++;
			$bundleId = $boxInstanceData['bundle']->id;
			$uniqueNewBundleIds[$bundleId] = $count;

			foreach ($boxInstanceData['items'] as $itemId => $item)
			{
				$itemQtyKey = $itemId . '_' . $item[0];
				$uniqueNewItemQtys[$itemQtyKey] = 1;
			}
		}

		//if box count same but different box ids, then if same bundles and same items qty then no change
		if (count($originalBox) == count($updatedBox))
		{
			//same bundles
			if (count($uniqueOriginalBundleIds) == count($uniqueNewBundleIds))
			{
				$diff = array_diff_key($uniqueOriginalItemQtys, $uniqueNewItemQtys);
				$diffRev = array_diff_key($uniqueNewItemQtys, $uniqueOriginalItemQtys);
				//same total item and quantities (across boxes)
				if (count($diff) == 0 && count($diffRev) == 0)
				{
					//No Change
					return true;
				}
			}
		}

		return false;
	}
}

class delivered_edit_order_mgr
{
	/**
	 * Given an Edited order, find differences and populate template for rendering
	 *
	 * @param $tpl          - template instance to populate variables onto for display
	 * @param $currentOrder - the edited ordered which will be compared to its previous state
	 * @param $Cart         - current cart
	 *
	 * @return COrdersDelivered|null
	 * @throws Exception
	 */
	static function initOriginalOrder(&$tpl, $currentOrder, $Cart)
	{
		$originalOrder = null;
		$tpl->assign('delta_session', false);
		$tpl->assign('delta_boxes', false);
		$tpl->assign('delta_has_new_total', false);

		//fetch original order and reconcile differences
		if ($tpl->isEditDeliveredOrder)
		{

			//move this into an abtract function of COrders implement in COrderDelivered
			$originalOrder = new COrdersDelivered();
			$originalOrder->id = $Cart->getEditOrderId();
			$originalOrder->find(true);

			$CustObj = null;
			if (!empty($originalOrder->user_id))
			{
				$CustObj = DAO_CFactory::create('user');
				$CustObj->id = $originalOrder->user_id;
				$CustObj->find(true);
			}

			$originalOrder->refresh($CustObj);
			$originalOrder->OrderAddress();
			$originalOrder->OrderShipping();
			$originalOrder->reconstruct();

			$originalSession = $originalOrder->findSession(true);

			if ($originalSession->id != $currentOrder->getSessionId())
			{
				$tpl->assign('delta_session', true);
			}

			$tpl->assign('delta_original_order_cost', $originalOrder->grand_total);

			//compare boxes
			$originalBoxes = $originalOrder->getBoxes();
			$boxes = $currentOrder->getBoxes();

			$boxDifferences = box_comparator::compareDeliveredBoxes($originalBoxes, $boxes);
			if (count($boxDifferences) > 0)
			{
				$tpl->assign('delta_boxes', true);
			}
			$tpl->assign('delta_boxes_differences', $boxDifferences);

			//compare totals
			$oTotal = $originalOrder->grand_total * 100;
			$nTotal = ($currentOrder->grand_total - $currentOrder->points_discount_total) * 100;
			if ($oTotal != $nTotal)
			{
				$totalDiff = ($nTotal - $oTotal) / 100;
				$tpl->assign('delta_original_total', $originalOrder->grand_total);
				$tpl->assign('delta_new_total', $currentOrder->grand_total);
				$tpl->assign('delta_total_diff', $totalDiff);
				$tpl->assign('delta_original_tax', $originalOrder->subtotal_all_taxes);
				$tpl->assign('delta_new_tax', $currentOrder->subtotal_all_taxes);
				$tpl->assign('delta_has_new_total', true);
				$tpl->assign('delta_is_refund', ($totalDiff >= 0 ? false : true));
			}
		}

		return $originalOrder;
	}
}

class checkout_validation
{

	static function validateStoredStoreCredit($storeCreditID)
	{
		$storeCreditDAO = new DAO();
		$storeCreditDAO->query("select id from store_credit where id = $storeCreditID and is_redeemed = 0 and is_deleted = 0 and is_expired = 0");
		if ($storeCreditDAO->N == 0)
		{
			return false;
		}

		return true;
	}

	static function doCreditCardValidation($payment)
	{
		$validation_result = CPayment::validateCC($payment['ccType'], $payment['ccNumber'], $payment['ccMonth'], $payment['ccYear'], $payment['ccSecurityCode']);

		if ($validation_result)
		{
			$errorMessage = "";
			switch ($validation_result)
			{
				case 'invalidtype':
					{
						$errorMessage = "The card type does not seem to match the number you entered. Please check the card number and the selected card type.";
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
				case 'invalidCSC':
					{
						$errorMessage = "The Security code is not valid.";
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

			return $errorMessage;
		}

		return 0;
	}

	static function validateAndPreparePayments($payments, $tpl, $totalFundsNeeded, $store_id)
	{

		$validationPassed = true;
		$totalNonCreditCardPayments = 0;
		$GiftCardPaymentArray = array();
		$StoreCreditArray = array();
		$CC_Data = array();

		foreach ($payments as $id => $paymentInfo)
		{
			switch ($paymentInfo['payment_type'])
			{
				case 'gift_card':
					// TODO: last ditch balance check to prevent failure
					if ($paymentInfo['amount'] > $totalFundsNeeded)
					{
						$paymentInfo['amount'] = $totalFundsNeeded;
					}

					$GiftCardPaymentArray[] = array(
						'gc_amount' => $paymentInfo['amount'],
						'gc_number' => $paymentInfo['tempData']['card_number']
					);
					$totalNonCreditCardPayments += $paymentInfo['amount'];
					break;
				case 'store_credit':
					if (checkout_validation::validateStoredStoreCredit($paymentInfo['paymentData']['store_credit_id']))
					{
						$StoreCreditArray[] = $paymentInfo['paymentData']['store_credit_id'];
						$totalNonCreditCardPayments += $paymentInfo['amount'];
					}
					break;
			}
		}

		if (COrders::isPriceGreaterThan($totalFundsNeeded, $totalNonCreditCardPayments))
		{

			if (isset($_POST['cc_pay_id_edit_order_multi']) && is_array($_POST['cc_pay_id_edit_order_multi']))
			{

				foreach ($_POST['cc_pay_id_edit_order_multi'] as $id)
				{
					$PaymentObj = DAO_CFactory::create('payment');
					$PaymentObj->id = $id;
					$PaymentObj->find(true);

					if ($PaymentObj->N == 0)
					{
						$validationPassed = false;
						$tpl->setErrorMsg("The Card is no longer valid.");
					}
				}
			}
			else if (isset($_POST['gc_pay_id_edit_order']) && is_numeric($_POST['gc_pay_id_edit_order']))
			{
				$PaymentObj = DAO_CFactory::create('payment');
				$PaymentObj->id = $_POST['gc_pay_id_edit_order'];
				$PaymentObj->find(true);

				if ($PaymentObj->N == 0)
				{
					$validationPassed = false;
					$tpl->setErrorMsg("The Gift Card selected is no longer valid.");
				}
			}
			else if (isset($_POST['cc_pay_id_edit_order']) && is_numeric($_POST['cc_pay_id_edit_order']))
			{
				$PaymentObj = DAO_CFactory::create('payment');
				$PaymentObj->id = $_POST['cc_pay_id_edit_order'];
				$PaymentObj->find(true);

				if ($PaymentObj->N == 0)
				{
					$validationPassed = false;
					$tpl->setErrorMsg("The Card selected is no longer valid.");
				}
			}
			else if (isset($_POST['cc_pay_id']) && is_numeric($_POST['cc_pay_id']))
			{
				$selectMerchantAccoutClause = ', ma.id';
				$selectMerchantAccoutJoin = 'join merchant_accounts ma on ma.store_id = ucr.store_id and ma.is_deleted = 0 and ma.id = ucr.merchant_account_id';
				if (defined('USE_CORPORATE_TEST_ACCOUNT') && USE_CORPORATE_TEST_ACCOUNT !== false)
				{
					$selectMerchantAccoutClause = '';
					$selectMerchantAccoutJoin = '';
				}

				$UCR = DAO_CFactory::create('user_card_reference');
				$UCR->query("select ucr.*$selectMerchantAccoutClause from user_card_reference ucr
						$selectMerchantAccoutJoin
						where ucr.id  = {$_POST['cc_pay_id']} and ucr.store_id = $store_id");
				if (!$UCR->fetch(true))
				{
					$validationPassed = false;
					$tpl->setErrorMsg("The Card selected is no longer valid. You may need to enter the card again.");
				}
				else
				{

					$CC_Data['ccType'] = $UCR->credit_card_type;
					$CC_Data['ccNumber'] = false;
					$CC_Data['ccMonth'] = false;
					$CC_Data['ccYear'] = false;
					$CC_Data['ccNameOnCard'] = false;
					$CC_Data['billing_address'] = false;
					$CC_Data['city'] = false;
					$CC_Data['state_id'] = false;
					$CC_Data['billing_postal_code'] = false;
					$CC_Data['ccSecurityCode'] = false;
					$CC_Data['reference'] = $UCR->card_transaction_number;

					if (!empty($_POST['is_delayed_payment']))
					{
						$CC_Data['do_delayed_payment'] = 1;
					}
					else if (!empty($_POST['is_flat_rate_delayed_payment']))
					{
						$CC_Data['do_delayed_payment'] = 3;
					}
					else if (!empty($_POST['is_store_specific_flat_rate_delayed_payment']))
					{
						$CC_Data['do_delayed_payment'] = 4;
					}
					else
					{
						$CC_Data['do_delayed_payment'] = false;
					}
				}
			}
			else
			{
				$CC_Data['ccType'] = isset($_POST['ccType']) ? $_POST['ccType'] : false;
				$CC_Data['ccNumber'] = isset($_POST['ccNumber']) ? $_POST['ccNumber'] : false;
				$CC_Data['ccMonth'] = isset($_POST['ccMonth']) ? $_POST['ccMonth'] : false;
				$CC_Data['ccYear'] = isset($_POST['ccYear']) ? $_POST['ccYear'] : false;
				$CC_Data['ccNameOnCard'] = isset($_POST['ccNameOnCard']) ? $_POST['ccNameOnCard'] : false;
				$CC_Data['billing_address'] = isset($_POST['billing_address']) ? $_POST['billing_address'] : false;
				$CC_Data['city'] = false;
				$CC_Data['state_id'] = false;
				$CC_Data['billing_postal_code'] = isset($_POST['billing_postal_code']) ? $_POST['billing_postal_code'] : false;
				$CC_Data['ccSecurityCode'] = isset($_POST['ccSecurityCode']) ? $_POST['ccSecurityCode'] : false;

				if (!empty($_POST['is_delayed_payment']))
				{
					$CC_Data['do_delayed_payment'] = 1;
				}
				else if (!empty($_POST['is_flat_rate_delayed_payment']))
				{
					$CC_Data['do_delayed_payment'] = 3;
				}
				else if (!empty($_POST['is_store_specific_flat_rate_delayed_payment']))
				{
					$CC_Data['do_delayed_payment'] = 4;
				}
				else
				{
					$CC_Data['do_delayed_payment'] = false;
				}

				if (!empty($_POST['save_cc_as_ref']))
				{
					$CC_Data['save_card_as_referene'] = true;
				}

				$validationResult = self::doCreditCardValidation($CC_Data);
				if ($validationResult)
				{
					$tpl->setErrorMsg($validationResult);

					$validationPassed = false;
					//		CApp::bounce('/checkout');
					//		exit;
				}
			}
		}
		else
		{
			$CC_Data['ccType'] = false;
			$CC_Data['ccNumber'] = false;
			$CC_Data['ccMonth'] = false;
			$CC_Data['ccYear'] = false;
			$CC_Data['ccNameOnCard'] = false;
			$CC_Data['billing_address'] = false;
			$CC_Data['city'] = false;
			$CC_Data['state_id'] = false;
			$CC_Data['billing_postal_code'] = false;
			$CC_Data['ccSecurityCode'] = false;
			$CC_Data['do_delayed_payment'] = false;
		}

		if (empty($GiftCardPaymentArray))
		{
			$GiftCardPaymentArray = false;
		}
		if (empty($StoreCreditArray))
		{
			$StoreCreditArray = false;
		}

		return array(
			$validationPassed,
			$StoreCreditArray,
			$GiftCardPaymentArray,
			$CC_Data
		);
	}

	//Checks to see if the edit order card can still be edited
	private static function checkCartForEditOrder($orderId, &$tpl, $should_load_cart = false)
	{
		$tpl->assign('isEditDeliveredOrder', false);
		if (COrdersDelivered::canEditDeliveredOrder($orderId))
		{
			CBrowserSession::setValueAndDuration('EDIT_DELIVERED_ORDER', $orderId, 21600);//6 hours
			$tpl->assign('isEditDeliveredOrder', true);
			if ($should_load_cart)
			{
				return CCart2::instanceFromOrder($orderId);
			}
		}
		else
		{
			$tpl->setErrorMsg('Sorry, the selected order is not editable at this time.');
			CApp::bounce('/order-details?order=' . $orderId);
		}
	}

	static function getAndValidateCart($tpl)
	{
		$xssFilter = new InputFilter();
		//used to restore a cart from a previous order - for edit
		$restoreOrderId = $xssFilter->process((array_key_exists('restore_order', $_GET) ? $_GET['restore_order'] : null));

		//used to restore an abandoned cart - one without an order
		$restoreCartKey = $xssFilter->process((array_key_exists('restore_cart', $_GET) ? $_GET['restore_cart'] : null));

		//two above are mutually exclusive, should never both be set at same time

		if (!empty($restoreCartKey))
		{
			CBrowserSession::setCartKey($restoreCartKey);
			$Cart = CCart2::instance(true, $restoreCartKey);
		}
		else
		{
			if (empty($restoreOrderId))
			{
				$Cart = CCart2::instance();
				//check if the stored cart is for an edit order
				$editOrderId = $Cart->getEditOrderId();
				if (!empty($editOrderId) && $editOrderId != false)
				{
					//just setting values into state, don't assign cart here
					checkout_validation::checkCartForEditOrder($editOrderId, $tpl);
				}
			}
			else
			{
				$Cart = checkout_validation::checkCartForEditOrder($restoreOrderId, $tpl, true);
			}
		}

		$Order = $Cart->getOrder();

		// handle switch to Transparent Redirect if necessary
		//Check store setting
		$OrderStore = $Order->getStore();

		$result = $Cart->cart_sanity_check();
		// This function can clear the cart or specific fields - should this be silent?

		if ($result['status'] != 'all_good' && DEBUG)
		{
			$tpl->setDebugMsg($result['status'] . "<br />" . print_r($result['problem_list'], true));
		}

		/*
		 * TODO: Responsive Bounce to needed places depending on cart
		 */
		$store_id_in_cart = $Cart->getStoreId();
		$menu_id_in_cart = $Cart->getMenuId();
		$session_type_in_cart = $Cart->getNavigationType();
		$session_id_in_cart = $Cart->getSessionId();

		if (empty($store_id_in_cart))
		{
			// no store, send them to pick a store
			CApp::bounce('/locations');
		}
		else if (empty($session_type_in_cart))
		{
			// no session type, send them to pick a session type
			CApp::bounce('/session');
		}
		else if (empty($menu_id_in_cart))
		{
			// no menu in cart, send them to pick a session type
			if ($Cart->getNavigationType() != CTemplate::DELIVERED)
			{
				CApp::bounce('/session-menu');
			}
		}
		else if (empty($session_id_in_cart))
		{
			// no session chosen, send them to pick a session
			if ($session_type_in_cart == CTemplate::DELIVERED)
			{
				CApp::bounce('/box-delivery-date');
			}
			else
			{
				CApp::bounce('/session');
			}
		}

		return array(
			$Cart,
			$Order,
			$OrderStore
		);
	}

	public static function validateCoupon($Order, $Cart, $tpl)
	{
		$Coupon = $Order->getCoupon();
		$MenuItems = $Order->getItems();

		if (!empty($Coupon->limit_to_recipe_id) && !empty($Coupon->menu_item_id))
		{
			// menu_item coupon was added but the item isn't in the cart
			if (empty($MenuItems[$Coupon->menu_item_id]))
			{
				// clear coupon codes
				$Order->removeCoupon();
				$Order->recalculate();
				$Cart->addOrder($Order);
			}
		}

		if ($Order->isDreamTaste() || $Order->isFundraiser())
		{
			$TasteEventProperties = CDreamTasteEvent::sessionProperties($Order->getSessionId());

			if (empty($TasteEventProperties->customer_coupon_eligible))
			{
				// clear coupon codes
				$Order->removeCoupon();
				$Order->recalculate();
				$Cart->addOrder($Order);

				$tpl->assign('payment_enabled_coupon', false);
			}
			else
			{
				$tpl->assign('payment_enabled_coupon', true);
			}

			$tpl->assign('payment_enabled_store_credit', false);
			$tpl->assign('payment_enabled_gift_card', true);
		}
	}

	public static function validateFood(&$Order, $Cart, $tpl)
	{
		if (!$Order->verifyAdequateInventory())
		{
			$itemsOversold = $Order->getInvExceptionItemsString();

			$itemsToRemove = $Order->getUnderStockedItems();
			foreach ($itemsToRemove as $id)
			{
				$Cart->removeItem($Order->findSession()->menu_id, $id, $Order->isNewIntroOffer(), ($Order->isDreamTaste() || $Order->isFundraiser()));
			}

			$tpl->setErrorMsg("One or more items has become unavailable since you added it to your cart. Please review your order and try again. Items adjusted are:<br />" . $itemsOversold);
			if ($Cart->getNavigationType() == CTemplate::DELIVERED)
			{
				CApp::bounce('/box-select');
			}
			else
			{
				CApp::bounce('/session-menu');
			}
		}

		$removalList = $Order->getHiddenItems();

		$minimum = COrderMinimum::fetchInstance(COrders::STANDARD, $Cart->getStoreId(), $Cart->getMenuId());
		$minimum->updateBasedOnUserStoreMenu(CUser::getCurrentUser(), $Cart->getStoreId(), $Cart->getMenuId());

		if (!empty($removalList))
		{
			$removedItemsList = "<br />";
			foreach ($removalList as $id => $name)
			{
				$Cart->removeItem($Order->findSession()->menu_id, $id, $Order->isNewIntroOffer(), ($Order->isDreamTaste() || $Order->isFundraiser()));
				$removedItemsList .= $name . "<br />";
			}

			$Cart->rebuildCart();

			$Order = $Cart->getOrder();
			$Order->user_id = CUser::getCurrentUser()->id;
			$Order->refresh(CUser::getCurrentUser());
			$Order->recalculate();

			$Order->family_savings_discount_version = 2;

			$tpl->setErrorMsg('An Item(s) in your cart is no longer available. Please review your order. Removed Items: ' . $removedItemsList);

			if ($Order->getOrderFoodState(null, $minimum) != 'adequateFood')
			{
				if ($Cart->getNavigationType() == CTemplate::DELIVERED)
				{
					CApp::bounce('/box-select');
				}
				else
				{
					CApp::bounce('/session-menu');
				}
			}
		}

		// set current state of food order: noFood; adequateFood; inadequateFoodIntro; inadequateFoodStandard; bundleOverError; bundleRulesNotMet
		$foodState = $Order->getOrderFoodState($Cart, $minimum);
		$tpl->assign('foodState', $foodState);

		if ($foodState == 'bundleOverError')
		{
			//First check that the current session is actually a Taste
			if ($Order->isDreamTaste() || $Order->isFundraiser())
			{
				// the first 3 tests look at session type so if we get here then
				// we know there is bundle and there should be so this error is legit
				$tpl->setStatusMsg('There was a problem with your cart (too many items). Please review your selections and continue checkout.');
				CApp::bounce('/session-menu');
			}
			else
			{
				// so if the session is standard or MFY and the order is not an intro order the more than likely a bundle id is lingering in the cart.
				if (!$Order->isNewIntroOffer())
				{
					$Cart->removeBundleId(); // name is obsolete - this removes any bundle
					$Order->bundle_id = null;
					$Order->recalculate();
					// allow normal processing
				}
				else
				{
					$tpl->setStatusMsg('There was a problem with your cart (too many items). Please review your selections and continue checkout.');
					CApp::bounce('/session-menu');
				}
			}
		}
		else if ($foodState == 'bundleRulesNotMet')
		{
			$tpl->setStatusMsg('There was a problem with your cart (the dinner bundle does not have the correct amount of items). Please review your selections and continue checkout.');
			CApp::bounce('/session-menu');
		}

		// not enough food
		if ($foodState == 'inadequateFoodIntro' || $foodState == 'inadequateFoodStandard')
		{
			$tpl->setStatusMsg('Your cart does not have all the items needed to proceed to checkout: Not enough servings.');
			CApp::bounce('/session-menu');
		}

		if ($foodState == 'adequateFood' && ($Order->isNewIntroOffer() || $Order->isDreamTaste()) && CUser::isLoggedIn())
		{
			if ((!CUser::getCurrentUser()->isNewBundleCustomer() || !CUser::getCurrentUser()->isEligibleForDreamTaste($Order, $Cart)) && defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS)
			{
				$tpl->setErrorMsg('This account has previous orders. This order is permitted on this test server but would be prohibited in production.');
			}
			else if ($Order->isNewIntroOffer() && !CUser::getCurrentUser()->isNewBundleCustomer())
			{
				$tpl->setErrorMsg('Our records indicate that you have previous orders with Dream Dinners. You must be new to Dream Dinners to be eligible for this offer. You have been redirected to the standard menu.');
				$Cart->removeBundleId();
				$Cart->addNavigationType(CTemplate::STANDARD, true);
				CApp::bounce('/session-menu');
			}
			else if ($Order->isDreamTaste() && !CUser::getCurrentUser()->isEligibleForDreamTaste($Order, $Cart))
			{
				$tpl->setStatusMsg("We&rsquo;re sorry, Meal Prep Workshop sessions are only available to new customers. Contact your store for details about hosting your own Meal Prep Workshop event.");
				$Cart->removeBundleId();
				$Cart->addSessionId(0, true);
				$Cart->addNavigationType(CTemplate::STANDARD, true);
				CApp::bounce('/session-menu');
			}
		}

		if ($foodState == 'adequateFood' && $Order->isFundraiser() && CUser::isLoggedIn())
		{
			if ($Order->isFundraiser() && !CUser::getCurrentUser()->isEligibleForFundraiser($Order->findSession()->id))
			{
				if (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS)
				{
					$tpl->setStatusMsg("We are sorry, you currently have an active order for this Fundraiser session.<br /><br />This order is permitted on this test server but would be prohibited in production.");
				}
				else
				{
					$tpl->setStatusMsg("We are sorry, you currently have an active order for this Fundraiser session.");
					CCart2::instance()->emptyCart();
					CApp::bounce('/session-menu');
				}
			}
		}
	}

	public static function setupDinnerDollars($platePointsStatus, $Order, $tpl, $Form, $isEditDeliveredOrder = false)
	{
		if (($platePointsStatus['userIsEnrolled'] || $platePointsStatus['userIsOnHold']) && $platePointsStatus['storeSupportsPlatePoints'] && !CUser::getCurrentUser()->isUserPreferred())
		{
			$Order->storeAndUserSupportPlatePoints = true;
			$maxAvailableCredit = CPointsCredits::getAvailableCreditForUser(CUser::getCurrentUser()->id);
			$maxDeduction = $Order->getPointsDiscountableAmount();

			$Coupon = $Order->getCoupon();
			// Adjust MFY Fee Coupon to current fee amount
			if ($Coupon)
			{
				$UCCode = strtoupper($Coupon->coupon_code);
				if (!empty($Coupon->limit_to_mfy_fee))
				{
					$maxDeduction -= $Order->subtotal_service_fee;
					if ($maxDeduction < 0)
					{
						$maxDeduction = 0;
					}
				}

				if (!empty($Coupon->limit_to_delivery_fee))
				{
					$maxDeduction -= $Order->subtotal_delivery_fee;
					if ($maxDeduction < 0)
					{
						$maxDeduction = 0;
					}
				}
			}

			$tpl->assign('maxPPCredit', $maxAvailableCredit);
			$tpl->assign('maxPPDeduction', $maxDeduction);

			if ($maxAvailableCredit > 0 && !$Order->isBundleOrder())
			{

				$maxPointsDiscount = ($maxAvailableCredit > $maxDeduction ? $maxDeduction : $maxAvailableCredit);
				$requestedPointsDiscount = 0;
				$tpl->assign('pp_discount_mfy_fee_first', $Order->pp_discount_mfy_fee_first);

				// the cart may hold a value - if so use it if it's legal
				if ($Order->points_discount_total > 0)
				{
					if ($Order->points_discount_total <= $maxPointsDiscount)
					{
						$requestedPointsDiscount = $Order->points_discount_total;
					}
					else
					{
						$requestedPointsDiscount = $maxPointsDiscount;
					}
				}

				if ($requestedPointsDiscount > 0)
				{
					$Form->DefaultValues['plate_points_discount'] = $requestedPointsDiscount;
				}

				$Form->AddElement(array(
					CForm::type => CForm::Money,
					CForm::name => 'plate_points_discount',
					CForm::length => 16,
					CForm::onKeyUp => 'handlePlatePointsDiscount',
					CForm::placeholder => 'Enter Amount',
					CForm::autocomplete => false,
					CForm::disabled => false
				));

				$tpl->assign('has_PP_widget', true);

				if (!$isEditDeliveredOrder)
				{
					$Order->points_discount_total = $Form->value('plate_points_discount');
				}
				$Order->recalculate($isEditDeliveredOrder);
			}
			else
			{
				$Order->points_discount_total = 0;
			}
		}
		else
		{
			$Order->storeAndUserSupportPlatePoints = false;
			$Order->points_discount_total = 0;
		}
	}

}

class page_checkout extends CPage
{
	private $runAsGuest = false;

	function runPublic()
	{
		CApp::forceSecureConnection();
		CTemplate::noCache();
		$tpl = CApp::instance()->template();

		// USER is not logged in so show the cart and login/register form

		$tpl->assign('hide_store_selector', true); // Hide store selector on create account
		$tpl->assign('isCreate', true);
		$tpl->assign('isAdmin', false);
		$tpl->assign('form_account', form_account::process_account_creation($tpl)->Render());
		$tpl->assign('platePointsEnroll', true);
		$tpl->assign('isLoggedIn', false);

		list($Cart, $Order, $OrderStore) = checkout_validation::getAndValidateCart($tpl);

		$tpl->assign('payment_enabled_store_credit', true);
		$tpl->assign('payment_enabled_gift_card', true);
		$tpl->assign('payment_enabled_coupon', true);

		if (!empty($Cart))
		{
			//these are user specific so remove from cart
			$Cart->removeMealCustomizationOptOut();
			$Cart->removeOrderCustomizationOptions();
		}

		checkout_validation::validateCoupon($Order, $Cart, $tpl);

		// most issues have to do with the menu from here on so bounce all types to the menu
		if ($Cart->getNavigationType() == CTemplate::DELIVERED)
		{
			$tpl->assign('bounce_to', '/box-select');
		}
		else
		{
			$tpl->assign('bounce_to', '/session-menu');
		}

		$Order->clearMealCustomizations();
		$Order->clearPreferred();
		$Order->recalculate();

		$markup = $Order->getMarkUp();

		if ($markup)
		{
			$defaultAssemblyFee = $markup->assembly_fee;
		}
		else
		{
			$defaultAssemblyFee = null;
		}

		$allowAssembly = OrdersHelper::allow_assembly_fee($Order->getMenuId());
		$tpl->assign('allow_assembly_fee', $allowAssembly);
		if (!$allowAssembly)
		{
			$defaultAssemblyFee = 0;
		}

		$tpl->assign('defaultAssemblyFee', $defaultAssemblyFee);

		$validationResult = $Cart->validateForCheckout();
		if ($validationResult !== true)
		{
			// if it's event, skip the error message, likely bounced from entering password on session page.
			if ($Cart->getNavigationType() != CTemplate::EVENT && $Cart->getNavigationType() != CTemplate::DELIVERED)
			{
				$tpl->setStatusMsg('Your cart does not have all the items needed to proceed to checkout: ' . $validationResult);
			}

			if ($Cart->getNavigationType() == CTemplate::DELIVERED)
			{
				if ($validationResult == 'No session found in cart')
				{
					CApp::bounce('/box_delivery_date');
				}
				else
				{
					CApp::bounce('/box-select');
				}
			}
			else
			{
				CApp::bounce('/session-menu');
			}
		}

		checkout_validation::validateFood($Order, $Cart, $tpl);

		$sessionObj = $Order->getSessionObj(false);
		$action = COrders::getFullyQualifiedOrderTypeFromSession($sessionObj);
		$action = empty($action) ? '' : $action . '<br>';
		$tpl->assign('customerActionString', $action);

		$tpl->assign('avgCostPerServingEntreeServings', $Order->servings_core_total_count);
		$tpl->assign('avgCostPerServingEntreeCost', $Order->pcal_core_total);

		$tpl->assign('sessionTime', $Order->findSession()->session_start);
		$tpl->assign('session_id', $Order->findSession()->id);
		$tpl->assign('has_gift_card', false); // gift cards are processed through separate process

		if (isset($_POST['run_as_guest']) && $_POST['run_as_guest'] == "true")
		{
			$this->runAsGuest = true;
			$this->runCustomer();
		}
		else
		{
			$tpl->assign('allowGuest', false);
		}

		$StoreDAO = $Order->getStore();
		$numberBagsRequired = 0;
		$bagFeeItemizationNote = '';
		if ($StoreDAO->supports_bag_fee)
		{
			$numberBagsRequired = COrders::getNumberBagsRequiredFromItems($Order);
			$bagFeeItemizationNote = '(' . $numberBagsRequired . ' bag' . ($numberBagsRequired > 1 ? 's' : '') . ' * $' . $StoreDAO->default_bag_fee . ')';
		}
		$tpl->assign('bagFeeItemizationNote', $bagFeeItemizationNote);
		$tpl->assign('numberBagsRequired', $numberBagsRequired);
		$tpl->assign('supports_bag_fee', $StoreDAO->supports_bag_fee);
		$tpl->assign('default_bag_fee', $StoreDAO->default_bag_fee);
		$tpl->assign('opted_to_bring_bags', $Order->opted_to_bring_bags);

		$tpl->assign('store_id_of_order', !empty($Order->store_id) ? $Order->store_id : 0);

		$tpl->assign('sticky_nav_bottom_disable', true);
		$tpl->assign('cart_info', CUser::getCartIfExists());
		$tpl->assign('isGiftCardOnlyOrder', false);
	}

	function runCustomer()
	{
		// -------------------------------------Setup
		CTemplate::noCache();

		$originalOrder = null;

		CApp::forceSecureConnection();
		ini_set('memory_limit', '96M');
		$tpl = CApp::instance()->template();

		$User = CUser::getCurrentUser();
		$User->getUsersLTD_RoundupOrders();

		$tpl->assign('ltd_roundup_orders', $User->ltd_roundup_orders);

		// Agree to Dream Dinners T&C, should be true here anyhow
		if (!empty($_POST['customers_terms']))
		{
			$User->setUserPreference(CUser::TC_DREAM_DINNERS_AGREE, 1);
		}

		$canProvideNewDepositMechanisms = true;

		list($Cart, $Order, $OrderStore) = checkout_validation::getAndValidateCart($tpl);

		// check for partial account
		if ($User->isUserPartial())
		{
			// if they are not upgrading a current session_rsvp bounce them to account
			if (!CSession::getSessionRSVP($Order->findSession()->id, $User->id))
			{
				$tpl->setErrorMsg('Please complete your profile information prior to checkout.');
				CApp::bounce('/account');
			}
		}

		$originalOrder = delivered_edit_order_mgr::initOriginalOrder($tpl, $Order, $Cart);

		// platepoints enrollment during checkout
		$tpl->assign('can_enroll_in_platepoints', false);
		$tpl->assign('precheck_enroll_in_platepoints', false);

		$tpl->assign('isLoggedIn', true);
		$tpl->assign('user', $User);

		$tpl->assign('payment_enabled_store_credit', true);
		$tpl->assign('payment_enabled_gift_card', true);
		$tpl->assign('payment_enabled_coupon', true);

		//------------meal customization
		$StoreObj = $Order->getStore();
		$sessionObj = $Order->findSession();
		$showCustomization = false;
		$hasCustomizationOptionsSelected = false;
		if ($OrderStore->supports_meal_customization && $sessionObj->isOpenForCustomization($StoreObj))
		{ //Store allows
			$customizableMealCount = COrders::getNumberOfCustomizableMealsFromItems($Order, $OrderStore->allow_preassembled_customization);
			if ($customizableMealCount > 0)
			{
				$orderCustomizationPrefObj = json_decode($Order->order_customization);
				if (empty($orderCustomizationPrefObj) || empty($orderCustomizationPrefObj->meal))
				{
					$mealCustomizationPrefObj = $User->getMealCustomizationPreferences();
				}
				else
				{
					$mealCustomizationPrefObj = $orderCustomizationPrefObj->meal;
				}

				if (!empty($mealCustomizationPrefObj))
				{
					$showCustomization = true;
					$tpl->assign('meal_customization_preferences_json', json_encode($mealCustomizationPrefObj));
					$tpl->assign('meal_customization_preferences', $mealCustomizationPrefObj);
					$hasCustomizationOptionsSelected = OrdersCustomization::determineIfMealCustomizationPreferencesSetOn($mealCustomizationPrefObj);
				}
				else
				{
					$tpl->assign('meal_customization_preferences_json', '{}');
					$tpl->assign('meal_customization_preferences', null);
				}

				$tpl->assign('has_meal_customization_selected', ($Order->opted_to_customize_recipes ? true : false));
				$tpl->assign('default_meal_customization_to_selected', ((is_null($Order->opted_to_customize_recipes) && $hasCustomizationOptionsSelected) ? true : false));
			}
		}

		$tpl->assign('should_allow_meal_customization', $showCustomization);
		$tpl->assign('allow_preassembled_customization', $OrderStore->allow_preassembled_customization);

		// ----------------------------------------------------validation
		checkout_validation::validateCoupon($Order, $Cart, $tpl);

		if ($this->runAsGuest)
		{
			$tpl->assign('allowGuest', true);
		}
		else
		{
			$tpl->assign('allowGuest', false);
		}

		$currentUserId = CUser::getCurrentUser()->id;
		if ($tpl->isEditDeliveredOrder && $originalOrder->user_id != $currentUserId)
		{
			//force new payment methods if a different user has gotten to the order
			$Cart->clearAllPayments(true);

			$Cart->emptyCart();
			CApp::instance()->template()->setStatusMsg('Editing an order for another user is not allowed. The cart has been emptied. Please start your order again.');
			CApp::bounce('/session-menu');
		}
		else if (!$tpl->isEditDeliveredOrder && $Order->user_id != $currentUserId)
		{
			//TODO: is it an error to have a previous and different user_id in the order/cart?
			$Cart->clearAllPayments(true);
			$Cart->addUserId(CUser::getCurrentUser()->id);
		}

		// -------------------------------------------------update order

		$Order->user_id = $currentUserId;
		$Order->refresh(CUser::getCurrentUser());
		$Order->recalculate($tpl->isEditDeliveredOrder);

		$markup = $Order->getMarkup();
		if ($markup)
		{
			$defaultAssemblyFee = $Order->getMarkup()->assembly_fee;
		}
		else
		{
			$defaultAssemblyFee = 0;
		}

		$tpl->assign('defaultAssemblyFee', $defaultAssemblyFee);
		$Order->family_savings_discount_version = 2;

		// --------------------------------------Validation
		$validationResult = $Cart->validateForCheckout();
		if ($validationResult !== true)
		{
			// if it's event, skip the error message, likely bounced from entering password on session page.
			if ($Cart->getNavigationType() != CTemplate::EVENT && $Cart->getNavigationType() != CTemplate::DELIVERED)
			{
				$tpl->setStatusMsg('Your cart does not have all the items needed to proceed to checkout: ' . $validationResult);
			}

			if ($Cart->getNavigationType() == CTemplate::DELIVERED)
			{
				if ($validationResult == 'No session found in cart')
				{
					CApp::bounce('/box-delivery-date');
				}
				else
				{
					CApp::bounce('/box-select');
				}
			}
			else
			{
				CApp::bounce('/session-menu');
			}
		}

		checkout_validation::validateFood($Order, $Cart, $tpl);

		$tpl->assign('avgCostPerServingEntreeServings', $Order->servings_core_total_count);
		$tpl->assign('avgCostPerServingEntreeCost', $Order->pcal_core_total);
		$tpl->assign('has_gift_card', false);

		$tpl->assign('isGiftCardOnlyOrder', false);
		$tpl->assign('store_id_of_order', !empty($Order->store_id) ? $Order->store_id : 0);

		if ($tpl->isEditDeliveredOrder && !$tpl->delta_has_new_total)
		{
			$tpl->assign('hide_remove_gift_card', true);
			$tpl->assign('payment_enabled_gift_card', false);

			$tpl->assign('payment_enabled_coupon', !$Cart->hasOnlyGiftCardPayments());
		}

		$Form = new CForm('customer_food_checkout');
		$Form->Repost = true;
		$Form->Bootstrap = true;

		list ($store_credit_total, $storeCreditsList) = $this->buildStoreCreditsForm($tpl, $Cart, $Order);

		if ($Order->isDreamTaste() || $Order->isFundraiser())
		{
			$Cart->removeAllStoreCredit();
		}
		else
		{ // only add if not DreamTaste
			$Cart->addStoreCredit($storeCreditsList);
		}
		// SETUP PAYMENTS
		$paymentsInCart = $Cart->getAllPayments();
		$countSelectedCredits = 0;
		$totalSelectedCredits = 0;
		foreach ($paymentsInCart as $id => $cartPayment)
		{
			if ($cartPayment['payment_type'] == 'store_credit')
			{
				$countSelectedCredits++;
				$totalSelectedCredits += $cartPayment['amount'];
			}
		}

		if ($tpl->isEditDeliveredOrder)
		{
			//if edit order check cart for coupons and gc and adjust total due

			//compare totals
			$oTotal = $originalOrder->grand_total * 100;
			$nTotal = $Order->grand_total * 100;

			if ($oTotal != $nTotal)
			{
				$totalDiff = ($nTotal - $oTotal);
				$sumNewGiftCardPayment = $Cart->getNewGiftCardPaymentTotal() * 100;
				$totalDiff = $totalDiff - $sumNewGiftCardPayment;
				$totalDiff = $totalDiff / 100;
				$tpl->assign('delta_total_diff', $totalDiff);
				$tpl->assign('delta_has_new_total', true);
				$tpl->assign('delta_is_refund', ($totalDiff >= 0 ? false : true));
			}
		}

		$tpl->assign('countSelectedCredits', $countSelectedCredits);
		$tpl->assign('totalSelectedCredits', $totalSelectedCredits);

		//debit gift card processing
		$sessionObj = $Order->findSession();

		$action = COrders::getFullyQualifiedOrderTypeFromSession($sessionObj);
		$action = empty($action) ? '' : $action . '<br>';
		$tpl->assign('customerActionString', $action);

		$refPaymentTypeDataArray = CPayment::getPaymentsStoredForReference(CUser::getCurrentUser()->id, $Order->store_id);
		$tpl->assign('card_references', $refPaymentTypeDataArray);

		//build payment form
		self::buildPaymentForm($Form, null, $Order, $tpl);

		$platePointsStatus = CPointsUserHistory::getPlatePointsStatus($StoreObj, $User);

		$tpl->assign('canProvideNewDepositMechanisms', $canProvideNewDepositMechanisms);

		checkout_validation::setupDinnerDollars($platePointsStatus, $Order, $tpl, $Form, $tpl->isEditDeliveredOrder);

		$StoreDAO = $Order->getStore();
		$numberBagsRequired = 0;
		$bagFeeItemizationNote = '';
		if ($StoreDAO->supports_bag_fee)
		{
			$numberBagsRequired = COrders::getNumberBagsRequiredFromItems($Order);
			$bagFeeItemizationNote = '(' . $numberBagsRequired . ' bag' . ($numberBagsRequired > 1 ? 's' : '') . ' * $' . $StoreDAO->default_bag_fee . ')';
		}
		$tpl->assign('bagFeeItemizationNote', $bagFeeItemizationNote);
		$tpl->assign('numberBagsRequired', $numberBagsRequired);
		$tpl->assign('supports_bag_fee', $StoreDAO->supports_bag_fee);
		$tpl->assign('default_bag_fee', $StoreDAO->default_bag_fee);
		$tpl->assign('opted_to_bring_bags', $Order->opted_to_bring_bags);

		$Form->AddElement(array(
			CForm::type => CForm::Button,
			CForm::name => "complete_order",
			CForm::value => "Complete Order",
			CForm::disabled => true,
			CForm::css_class => "btn btn-primary btn-block btn-spinner btn-onclick-disable"
		));

		$Form->AddElement(array(
			CForm::type => CForm::Button,
			CForm::name => "to_payment",
			CForm::value => "Continue to Payment",
			CForm::disabled => true,
			CForm::css_class => "btn btn-primary btn-block btn-spinner btn-onclick-disable"
		));

		$Form->DefaultValues['special_insts'] = $Order->order_user_notes;

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::css_class => 'dd-strip-tags',
			CForm::name => "special_insts"
		));

		if (isset($_POST['complete_order']) && isset($_POST['customers_terms']))
		{
			try
			{

				if (!$Form->validate_CSRF_token())
				{
					$tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support. This message can also be caused by a double submission of the same page.");
					CApp::bounce('/checkout', true);
				}

				$xssFilter = new InputFilter();
				$_POST = $xssFilter->process($_POST);

				if ($StoreDAO->supports_bag_fee)
				{
					if (empty($_POST['opted_to_bring_bags']))
					{
						$numberBagsRequired = COrders::getNumberBagsRequiredFromItems($Order);
						$Order->total_bag_count = $numberBagsRequired;
						$Order->opted_to_bring_bags = 0;
					}
					else
					{
						$Order->opted_to_bring_bags = 1;
						$Order->total_bag_count = 0;
					}
				}
				else
				{
					$Order->total_bag_count = 0;
				}

				$instructions = $Form->value('special_insts');
				$Order->order_user_notes = trim(strip_tags($instructions));

				if ($sessionObj->isDelivery() || $sessionObj->isDelivered())
				{
					$Order->orderAddress();

					$shipping_phone_number = $Form->value('shipping_phone_number');
					$shipping_phone_number_new = $Form->value('shipping_phone_number_new');
					$shipping_address_note = $Form->value('shipping_address_note');

					$Order->orderAddress->firstname = $Form->value('shipping_firstname');
					$Order->orderAddress->lastname = $Form->value('shipping_lastname');
					$Order->orderAddress->address_line1 = $Form->value('shipping_address_line1');
					$Order->orderAddress->address_line2 = $Form->value('shipping_address_line2');
					$Order->orderAddress->city = $Form->value('shipping_city');
					$Order->orderAddress->state_id = $Form->value('shipping_state_id');
					$Order->orderAddress->postal_code = $Form->value('shipping_postal_code');
					$Order->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
					$Order->orderAddress->address_note = trim(strip_tags($shipping_address_note));
				}

				if ($tpl->isEditDeliveredOrder && $originalOrder != null)
				{
					$originalOrder->orderAddress();

					$shipping_phone_number = $Form->value('shipping_phone_number');
					$shipping_phone_number_new = $Form->value('shipping_phone_number_new');
					$shipping_address_note = $Form->value('shipping_address_note');

					$originalOrder->orderAddress->firstname = $Form->value('shipping_firstname');
					$originalOrder->orderAddress->lastname = $Form->value('shipping_lastname');
					$originalOrder->orderAddress->address_line1 = $Form->value('shipping_address_line1');
					$originalOrder->orderAddress->address_line2 = $Form->value('shipping_address_line2');
					$originalOrder->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
					$originalOrder->orderAddress->is_gift = $Form->value('shipping_is_gift');
					$originalOrder->orderAddress->address_note = trim(strip_tags($shipping_address_note));
					$originalOrder->orderAddress->email_address = $Form->value('shipping_gift_email_address');

					$originalOrder->orderAddressDeliveryProcessUpdate();
					$Cart->addOrderAddress($originalOrder->orderAddress);

					if ($tpl->delta_session)
					{
						$TargetSession = $Order->findSession(true);
						$originalOrder->addSession($TargetSession);
						$rescheduleResult = $originalOrder->reschedule($sessionObj->id, false, true);
					}
				}

				if ($Cart->getNavigationType() == CTemplate::DELIVERED)
				{
					$sessionIsValid = CSession::isSessionValidForDeliveredOrder($Order->findSession()->id, $Order->getStore(), $Order->findSession()->menu_id, false, $Order->orderAddress->postal_code);
					if (!$sessionIsValid)
					{
						$tpl->setStatusMsg('The delivery date you selected is unavailable. Please choose a new delivery date below.');
						CApp::bounce('/box-delivery-date');
					}
				}

				$Order->setOrderInStoreStatus();
				$Order->setOrderMultiplierEligibility();

				$totalAmount = $Order->grand_total;
				if ($tpl->isEditDeliveredOrder)
				{
					if ($tpl->delta_has_new_total)
					{
						$totalAmount = $tpl->delta_total_diff;
					}
					else
					{
						$totalAmount = 0;
					}
				}
				list($validationPassed, $StoreCreditArray, $giftCardArray, $creditCardArray) = checkout_validation::validateAndPreparePayments($Cart->getAllPayments(), $tpl, $totalAmount, $Order->store_id);

				if ($Order->isDreamTaste() || $Order->isFundraiser())
				{
					// no store credits can be used
					$StoreCreditArray = array();
				}

				if ($validationPassed)
				{
					if ($tpl->isEditDeliveredOrder && $originalOrder != null && $totalAmount == 0 && !$tpl->delta_boxes)
					{
						//Edited only Delivery Time and/or Address

						//Send Updates to SS
						ShipStationManager::getInstanceForOrder($originalOrder)->addUpdateOrder(new ShipStationOrderWrapper($originalOrder));

						//Boxes, Session have not changed so no update necessary
						$Order = $originalOrder;
						if ($rescheduleResult != null)
						{
							if ($rescheduleResult == 'success')
							{
								//so edit confirmation is sent
								$rescheduleResult = 'edit_success';
							}
							$rslt = array('result' => $rescheduleResult);
						}
						else
						{
							$rslt = array('result' => 'edit_success');
						}
					}
					else if ($tpl->isEditDeliveredOrder && $tpl->delta_boxes && $originalOrder != null)
					{
						//Need to process Gift Card payment to complete the order
						$paymentType = CPayment::GIFT_CARD;
						$paymentsFromCart = $Cart->getNewGiftCardPayments();

						$rslt = $Order->processEditOrder($originalOrder, $Cart, $tpl->delta_boxes_differences, $paymentsFromCart, $paymentType, $creditCardArray, $StoreCreditArray, $giftCardArray, true);
					}
					else
					{
						$creditCardArray['ccNumber'] = str_replace("-", "", $creditCardArray['ccNumber']);

						// finally charge the remaining balance to the credit card
						$rslt = $Order->processNewOrderGC(CPayment::CC, $creditCardArray, $StoreCreditArray, $giftCardArray, true);
					}
				}
				else
				{

					$rslt = array('result' => 'validation_error');
				}
			}
			catch (exception $e)
			{

				$tpl->setErrorMsg('An error has occurred in the payment process, please try again later.');
			}

			switch ($rslt['result'])
			{
				case 'validation_error':
					break; // Note: error messsage was set by validateAndPreparePayments
				case 'edit_order_failed':
					$tpl->setErrorMsg($rslt['msg']);
					break;
				case 'invalidCC':
					$tpl->setErrorMsg('The credit card number you entered could not be verified. Please double check your payment information.');
					break;
				case 'transactionDecline':
					$tpl->setErrorMsg('The Credit Card was declined:<br />' . $rslt['userText']);
					break;
				case 'session full':
				case 'closed':
					$Cart->addSessionId(0, true);
					$tpl->setErrorMsg('The session you have chosen is now full or closed. Please choose another session.');
					if ($Cart->getNavigationType() == CTemplate::DELIVERED)
					{
						CApp::bounce('/box-delivery-date');
					}
					else
					{
						CApp::bounce('/session');
					}
					break;
				case 'edit_success':
					try
					{
						$theUser = CUser::getCurrentUser();

						COrdersDelivered::sendEditedOrderConfirmationEmail($User, $originalOrder);
						//send update gift email
						if (!empty($originalOrder->orderAddress->is_gift))
						{
							CEmail::sendDeliveredGiftEmail($originalOrder, true);
						}
						$Cart->emptyCart();
					}
					catch (exception $e)
					{
						CLog::RecordException($e);
					}

					CApp::bounce('/order-details?status=ec&order=' . $originalOrder->id, true);

					break;
				case 'success':
					try
					{
						$theUser = CUser::getCurrentUser();

						$sendConfirmationEmail = true;
						if ($tpl->isEditDeliveredOrder)
						{
							//only want to send if new order
							//and if only Date or Address Changed
							$sendConfirmationEmail = false;
						}
						// if there is a gift card - the email function is informed and delivers the message
						COrders::sendConfirmationEmail($theUser, $Order, $Order->getGiftCards(), $sendConfirmationEmail);
						$theUser->setHomeStore($Order->store_id);
						CCustomerReferral::updateAsOrderedIfEligible($theUser, $Order);
						$Cart->emptyCart();
					}
					catch (exception $e)
					{
						CLog::RecordException($e);
					}

					// set a cookie so that analytics only records viewing the thank you page once
					CBrowserSession::setValue('dd_thank_you', 'checkout', false, true, false);
					CApp::bounce('/order-details?order=' . $Order->id, true);

					break;

				case 'failed':
				default:
					$tpl->setErrorMsg('An error has occurred in the payment process, please try again later.');
					break;
			}
		}

		$Form->setCSRFToken();

		if (isset($_POST['complete_order']) && !isset($_POST['customers_terms']))
		{
			// somehow checkout was invoked without the terms checkbox checked
			$tpl->setErrorMsg('You must agree to the terms and conditions nullby checking the box.');
		}

		// TODO: we must process this here
		if (isset($_POST['payWithStoreCredit']))
		{
			$FinalGCPayments = array();
			if (!empty($_POST['giftCardPayments']))
			{
				$GiftCardPaymentsTempArray = explode("|", $_POST['giftCardPayments']);

				// pop the last item, the string has 1 pipe too many
				array_pop($GiftCardPaymentsTempArray);

				foreach ($GiftCardPaymentsTempArray as $thisGCPayment)
				{
					$thisPayment = explode("^", $thisGCPayment);
					$FinalGCPayments[] = array(
						"gc_number" => $thisPayment[0],
						"gc_amount" => $thisPayment[1]
					);
				}
			}

			// TODO: investigate $Selected_Store_Credit_Array which was being assigned to store_credits here
			$paymentArray = array(
				'store_credits' => null,
				'gift_card' => $FinalGCPayments
			);

			// this adds store credits to the payment array als
			CCart2::instance()->addPayment($paymentArray);
			CApp::instance()->bounce('/order-submit?sc=true');
		}

		if ($Order)
		{
			//build the order
			$Order->refresh(CUser::getCurrentUser());
			$Order->recalculate($tpl->isEditDeliveredOrder);

			$tpl->assign('orderInfo', $Order->toArray());
			$tpl->assign('session_id', $Order->findSession()->id);
		}

		$Coupon = $Order->getCoupon();

		// We must recheck validity of coupon since the ussr may have removed items, etc.. since coupon was added.
		if ($Coupon)
		{
			if ($Order->isDelivered())
			{
				if ($tpl->isEditDeliveredOrder)
				{
					//TODO: evanl validate
				}
				else
				{
					$result = $Coupon->isValidForDelivered($Order, $Cart->getMenuId());
				}
			}
			else
			{
				$result = $Coupon->isValid($Order, $Cart->getMenuId());
			}

			if (!empty($result))
			{
				$Coupon = null;
				$Order->removeCoupon();
				$Order->recalculate();

				$errorMessages = implode("<br />", $result);
				$tpl->setErrorMsg("A promo code was removed for the following reasons:<br /> " . $errorMessages);
				$Cart->addOrder($Order);
			}
		}

		if ($Coupon)
		{
			$tpl->assign('couponCode', $Coupon->coupon_code);
			$tpl->assign('coupon_title', $Coupon->coupon_code_short_title);
		}
		else
		{
			$tpl->assign('couponCode', false);
		}

		$formArray = $Form->render();
		$tpl->assign('form_payment', $formArray);

		if (isset($_GET['err']) && $_GET['err'] == 'true')
		{
			$tpl->assign('CC_error', true);
		}

		$tpl->assign('productCount', $Order->countProducts());
		if ($Order->countItems() > 0)
		{
			$tpl->assign('hasItems', true);
		}

		$couponDetails = new stdClass();

		if (isset($Coupon))
		{
			$couponDetails->coupon_code_short_title = $Coupon->coupon_code_short_title;
			$couponDetails->limit_to_mfy_fee = $Coupon->limit_to_mfy_fee;
			$couponDetails->limit_to_delivery_fee = $Coupon->limit_to_delivery_fee;
		}
		else
		{
			$couponDetails->coupon_code_short_title = "";
			$couponDetails->limit_to_mfy_fee = false;
			$couponDetails->limit_to_delivery_fee = false;
		}
		$tpl->assign('coupon', json_encode($couponDetails));
		$tpl->assign('grand_total', $Order->grand_total);

		$tpl->assign('sticky_nav_bottom_disable', true);
		$tpl->assign('cart_info', CUser::getCartIfExists());
	}

	static function buildPaymentForm($Form, $User = null, $Order = null, &$tpl = null)
	{
		require_once('DAO/BusinessObject/CPayment.php');

		$Session = null;
		if ($Order)
		{
			$Session = $Order->findSession();
		}

		//set defaults
		if (!$User)
		{
			$User = CUser::getCurrentUser();
			$User->getAddressBookArray(true);
		}

		$DefaultContactNumber = false;
		$DisableZipCode = false;

		if (!empty($Order->orderAddress->address_line1))
		{
			$shipping_address_note = $Order->orderAddress->address_note;

			$Form->DefaultValues['shipping_firstname'] = $Order->orderAddress->firstname;
			$Form->DefaultValues['shipping_lastname'] = $Order->orderAddress->lastname;
			$Form->DefaultValues['shipping_phone_number'] = $Order->orderAddress->telephone_1;
			$DefaultContactNumber = $Order->orderAddress->telephone_1;
			$Form->DefaultValues['shipping_address_line1'] = $Order->orderAddress->address_line1;
			$Form->DefaultValues['shipping_address_line2'] = $Order->orderAddress->address_line2;
			$Form->DefaultValues['shipping_city'] = $Order->orderAddress->city;
			$Form->DefaultValues['shipping_state_id'] = $Order->orderAddress->state_id;
			$Form->DefaultValues['shipping_state'] = CStatesAndProvinces::GetName($Order->orderAddress->state_id);
			$Form->DefaultValues['shipping_postal_code'] = $Order->orderAddress->postal_code;
			$Form->DefaultValues['shipping_address_note'] = trim(strip_tags($shipping_address_note));
			$Form->DefaultValues['shipping_is_gift'] = $Order->orderAddress->is_gift;
			$Form->DefaultValues['shipping_gift_email_address'] = $Order->orderAddress->email_address;
		}
		else if ($Order->getSessionType() != CSession::DELIVERED)
		{
			$Addr = $User->getPrimaryAddress();

			if ($Addr)
			{
				$Form->DefaultValues['billing_address'] = $Addr->address_line1;
				$Form->DefaultValues['billing_city'] = $Order->orderAddress->city;
				$Form->DefaultValues['billing_state_id'] = $Order->orderAddress->state_id;
				$Form->DefaultValues['billing_postal_code'] = $Addr->postal_code;
			}

			$dAddr = $User->getDeliveryAddressDefault();

			if (!empty($dAddr->id))
			{
				$Form->DefaultValues['shipping_firstname'] = $User->firstname;
				$Form->DefaultValues['shipping_lastname'] = $User->lastname;
				$Form->DefaultValues['shipping_phone_number'] = '';
				$Form->DefaultValues['shipping_address_line1'] = $dAddr->address_line1;
				$Form->DefaultValues['shipping_address_line2'] = $dAddr->address_line2;
				$Form->DefaultValues['shipping_city'] = $dAddr->city;
				$Form->DefaultValues['shipping_state_id'] = $dAddr->state_id;
				$Form->DefaultValues['shipping_state'] = CStatesAndProvinces::GetName($dAddr->state_id);
				//$Form->DefaultValues['shipping_postal_code'] = $dAddr->postal_code; // disabled to force guest to verify address
				$Form->DefaultValues['shipping_address_note'] = $dAddr->address_note;
			}
		}
		else if ($Order->getSessionType() == CSession::DELIVERED)
		{
			$CartObj = CCart2::instance();

			$ckzip = DAO_CFactory::create('zipcodes');
			$ckzip->zip = $CartObj->getPostalCode();
			$ckzip->whereAdd("zipcodes.distribution_center IS NOT NULL");

			if ($ckzip->find(true))
			{
				$Form->DefaultValues['shipping_state_id'] = $ckzip->state;
				$Form->DefaultValues['shipping_state'] = CStatesAndProvinces::GetName($ckzip->state);
				$Form->DefaultValues['shipping_postal_code'] = $ckzip->zip; // disabled to force guest to verify address
			}
		}

		// Shipping Address
		if ($Order->getSessionType() == CSession::DELIVERED && !empty($User->addressBook))
		{
			$addArray = array(
				'null' => 'Address Book - New Contact'
			);

			foreach ($User->addressBook as $address)
			{
				$addArray[$address->id] = ((!empty($address->firstname) || !empty($address->lastname)) ? $address->firstname . ' ' . $address->lastname . ' - ' : $address->address_line1 . ', ') . $address->city . ', ' . $address->state_id . ' ' . $address->postal_code;
			}

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => "address_book_select",
				CForm::options => $addArray
			));
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_firstname",
			CForm::required => true,
			CForm::placeholder => "*First name",
			CForm::required_msg => "Please enter a first name.",
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_lastname",
			CForm::required => true,
			CForm::placeholder => "*Last name",
			CForm::required_msg => "Please enter a last name.",
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_address_line1",
			CForm::required => true,
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::maxlength => 255,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::placeholder => "Address 2",
			CForm::name => "shipping_address_line2",
			CForm::maxlength => 255,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_city",
			CForm::required => true,
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::maxlength => 64,
			CForm::size => 30,
			CForm::xss_filter => true,
			CForm::readonly => ($tpl->isEditDeliveredOrder)
		));

		if ($tpl->isEditDeliveredOrder)
		{
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'shipping_state',
				CForm::disabled => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::name => 'shipping_state_id'

			));
		}
		else
		{

			$Form->AddElement(array(
				CForm::type => CForm::StatesProvinceDropDown,
				CForm::name => 'shipping_state_id',
				CForm::required_msg => "Please select a state.",
				CForm::required => true
			));
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_postal_code",
			CForm::required => true,
			CForm::placeholder => "*Postal Code",
			CForm::number => true,
			CForm::gpc_type => TYPE_POSTAL_CODE,
			CForm::xss_filter => true,
			CForm::maxlength => 5,
			CForm::required_msg => "Please enter a zip code.",
			CForm::length => 16,
			CForm::readonly => ($Session->session_type == CSession::DELIVERED)
		));

		if ($Order->getSessionType() == CSession::DELIVERED)
		{
			$Form->AddElement(array(
				CForm::type => CForm::Tel,
				CForm::name => 'shipping_phone_number',
				CForm::required_msg => "Please enter a contact number.",
				CForm::pattern => '([0-9\-]+){12}',
				CForm::placeholder => "*Contact number",
				CForm::required => false,
				CForm::xss_filter => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'shipping_is_gift',
				CForm::label => "Is this a gift?",
				CForm::custom_switch => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'shipping_gift_email_address',
				CForm::placeholder => "Optional recipient email address",
				CForm::email => true
			));
		}
		else
		{
			$userPhoneArray = array(
				'' => 'Contact Number',
				$User->telephone_1 => $User->telephone_1
			);

			if (!empty($User->telephone_2))
			{
				$userPhoneArray[$User->telephone_2] = $User->telephone_2;
			}

			if ($DefaultContactNumber && !in_array($DefaultContactNumber, $userPhoneArray))
			{
				$Form->DefaultValues['shipping_phone_number_new'] = $DefaultContactNumber;
				$Form->DefaultValues['shipping_phone_number'] = "new";
				$tpl->assign("hasNewShippingContactNumber", true);
			}

			$userPhoneArray['new'] = 'New phone number';

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'shipping_phone_number',
				CForm::options => $userPhoneArray,
				CForm::required_msg => "Please select a contact number.",
				CForm::required => true,
				CForm::xss_filter => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Tel,
				CForm::name => 'shipping_phone_number_new',
				CForm::required_msg => "Please enter a contact number.",
				CForm::pattern => '([0-9\-]+){12}',
				CForm::placeholder => "*Contact number",
				CForm::required => false,
				CForm::xss_filter => true
			));
		}

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::placeholder => "Optional: Gate code, house description, etc.",
			CForm::maxlength => 100,
			CForm::css_class => 'dd-strip-tags',
			CForm::name => 'shipping_address_note',
			CForm::required => false
		));

		// Gift Cards widgets
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_number',
			CForm::autocomplete => false,
			CForm::length => 16,
			CForm::maxlength => 16,
			CForm::required => true
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_amount',
			CForm::length => 6,
			CForm::maxlength => 6,
			CForm::money => true,
			CForm::required => true
		));
	}

	function buildStoreCreditsForm($tpl, $Cart, $Order)
	{

		$sc_user_id = CUser::getCurrentUser()->id;

		if (empty($sc_user_id))
		{
			return array(
				0,
				array()
			);
		}

		// Build Store Credits array and checkboxes
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->user_id = $sc_user_id;
		$Store_Credit->store_id = $Order->store_id;
		$Store_Credit->is_redeemed = 0;
		$Store_Credit->is_deleted = 0;
		$Store_Credit->is_expired = 0;
		$Store_Credit->find();

		$Store_Credit_Array = array();

		$StoreCreditTotalAllTypes = 0;

		$CreditsForm = new CForm();
		$CreditsForm->Bootstrap = true;
		$type2Count = 0;
		$totalType2Credit = 0;
		$type1Count = 0;
		$totalType1Credit = 0;

		while ($Store_Credit->fetch())
		{
			$label = "Store Credit";

			$disabled = false;
			if ($Store_Credit->credit_type == 2 || $Store_Credit->credit_type == 3)
			{
				$type2Count++;
				$totalType2Credit += $Store_Credit->amount;
				$disabled = true;

				if ($Store_Credit->credit_type == 2)
				{
					$label = "Referral Reward";
				}
				else
				{
					$label = "Direct Store Credit";
				}
			}
			else if ($Store_Credit->credit_type == 1)
			{
				$type1Count++;
				$totalType1Credit += $Store_Credit->amount;
				$disabled = true;
				$label = (isset($Store_Credit->credit_card_number) ? 'Refund for ' . $Store_Credit->credit_card_number : "Gift Card Refund");
			}

			$CreditsForm->addElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => "SC_" . $Store_Credit->id,
				CForm::disabled => $disabled,
				CForm::checked => true,
				CForm::Label => $label
			));

			$Store_Credit_Array[$Store_Credit->id] = array(
				'source' => (isset($Store_Credit->credit_card_number) ? $Store_Credit->credit_card_number : "none"),
				'total_amount' => $Store_Credit->amount,
				'credit_type' => $Store_Credit->credit_type,
				'store_credit_id' => $Store_Credit->id,
				'date_redeemed' => $Store_Credit->timestamp_created,
				'payment_type' => 'store_credit'
			);

			$StoreCreditTotalAllTypes += $Store_Credit->amount;
		}

		$tpl->assign('numType1Credits', $type1Count);
		$tpl->assign('totalType1Credit', $totalType1Credit);

		$tpl->assign('numType2Credits', $type2Count);
		$tpl->assign('totalType2Credit', $totalType2Credit);
		$tpl->assign('total_store_credit', $StoreCreditTotalAllTypes);

		if (!empty($Store_Credit_Array))
		{
			$tpl->assign('Store_Credits', $Store_Credit_Array);
		}

		$tpl->assign('credits_form', $CreditsForm->Render());

		return array(
			$StoreCreditTotalAllTypes,
			$Store_Credit_Array
		);
	}

}

?>