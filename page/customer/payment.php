<?php // menus.php
require_once('DAO/BusinessObject/COrders.php');
require_once('DAO/BusinessObject/CPayment.php');
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CUser.php');
require_once('DAO/BusinessObject/CUserData.php');
require_once('includes/CCart2.inc');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('DAO/BusinessObject/CGiftCard.php');
require_once('form/account.php');
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CPointsCredits.php');
require_once('includes/DAO/BusinessObject/CDreamTasteEvent.php');
require_once('includes/DAO/BusinessObject/CAddress.php');
require_once('page/customer/checkout.php');
require_once('includes/CEditOrderPaymentManager.inc');

class page_payment extends CPage
{

	private $runAsGuest = false;
	private $didShowNotEnoughFoodMessage = false;

	static function buildPaymentForm($Form, $User = null, $Order = null, $Store = null, $DR_Ordering = false, $hasCreditCardPayment = false, $allowDelayedPayment = true)
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
		}

		if (!isset($_POST['address']))
		{
			$Addr = $User->getPrimaryAddress();

			if (empty($Addr->id))
			{
				$AddressBook = $User->getAddressBookArray(false, true);
				$Addr = empty($AddressBook) ? null : reset($AddressBook);
			}

			if ($Addr)
			{
				$Form->DefaultValues['billing_address'] = $Addr->address_line1;
				$Form->DefaultValues['billing_address2'] = $Addr->address_line2;
				$Form->DefaultValues['billing_city'] = $Addr->city;
				$Form->DefaultValues['billing_state_id'] = $Addr->state_id;
				$Form->DefaultValues['billing_postal_code'] = $Addr->postal_code;
			}
		}

		$Form->DefaultValues['is_delayed_payment'] = '0';
		$Form->DefaultValues['is_flat_rate_delayed_payment'] = '0';
		$Form->DefaultValues['is_store_specific_flat_rate_delayed_payment'] = '0';

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "ccNameOnCard",
			CForm::autocomplete => false,
			CForm::placeholder => "*Name on Credit Card",
			CForm::required => true,
			CForm::required_msg => "Please enter your name as it appears on the credit card.",
			CForm::disabled => $hasCreditCardPayment,
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "ccNumber",
			CForm::autocomplete => false,
			CForm::placeholder => "*Credit Card Number",
			CForm::required => true,
			CForm::required_msg => "Please enter credit card number.",
			CForm::disabled => $hasCreditCardPayment,
			CForm::css_class => 'no-spin-button',
			CForm::number => true,
			//   maxlength doesn't work for 'number' type, using 'tel' type so mobile users get a number keyboard and the use of maxlength is valid
			CForm::size => 16,
			CForm::length => 16
		));

		$Form->addElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "save_cc_as_ref",
			CForm::label => 'Save this card for faster checkout',
			CForm::checked => false
		));

		$cardOptions = array('null' => 'Card Type');

		if ($Store)
		{
			$creditCardArray = $Store->getCreditCardTypes();
			if ($creditCardArray)
			{
				foreach ($creditCardArray as $card)
				{
					switch ($card)
					{
						case CPayment::VISA:
							$cardOptions [CPayment::VISA] = 'Visa';
							break;

						case CPayment::MASTERCARD:
							$cardOptions [CPayment::MASTERCARD] = 'MasterCard';
							break;

						case CPayment::DISCOVERCARD:
							$cardOptions [CPayment::DISCOVERCARD] = 'Discover';
							break;

						case CPayment::AMERICANEXPRESS:
							$cardOptions [CPayment::AMERICANEXPRESS] = 'American Express';
							break;

						default:
							break;
					}
				}
			}
		}
		else
		{
			$cardOptions [CPayment::VISA] = 'Visa';
			$cardOptions [CPayment::MASTERCARD] = 'MasterCard';
			$cardOptions [CPayment::DISCOVERCARD] = 'Discover';
			$cardOptions [CPayment::AMERICANEXPRESS] = 'American Express';
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccType",
			CForm::required => true,
			CForm::required_msg => "Please select a card type.",
			CForm::disabled => $hasCreditCardPayment,
			CForm::options => $cardOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccMonth",
			CForm::required => true,
			CForm::disabled => $hasCreditCardPayment,
			CForm::required_msg => "Please select an expiration month.",
			CForm::options => array(
				'null' => 'Month',
				'01' => '01',
				'02' => '02',
				'03' => '03',
				'04' => '04',
				'05' => '05',
				'06' => '06',
				'07' => '07',
				'08' => '08',
				'09' => '09',
				'10' => '10',
				'11' => '11',
				'12' => '12'
			)
		));

		$initialYear = date('y');
		$yearOptions = array(
			'null' => 'Year',
			$initialYear => $initialYear
		);
		for ($i = 0; $i < 10; $i++)
		{
			$yearOptions[++$initialYear] = $initialYear;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccYear",
			CForm::disabled => $hasCreditCardPayment,
			CForm::required => true,
			CForm::required_msg => "Please select an expiration year.",
			CForm::options => $yearOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "ccSecurityCode",
			CForm::autocomplete => false,
			CForm::placeholder => "*Code",
			CForm::required => true,
			CForm::required_msg => "Please enter the <span class='btn-link help-cvv'>security code</span>.",
			CForm::disabled => $hasCreditCardPayment,
			CForm::pattern => '^[0-9]{3}$',
			CForm::css_class => 'no-tel',
			CForm::min => 0,
			CForm::max => 999,
			CForm::maxlength => 3
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_address",
			CForm::placeholder => "*Billing Address",
			CForm::required => true,
			CForm::required_msg => "Please enter the street address of your billing address.",
			CForm::disabled => $hasCreditCardPayment,
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_address2",
			CForm::placeholder => "Address 2",
			CForm::required => false,
			CForm::disabled => $hasCreditCardPayment,
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_city",
			CForm::placeholder => "Billing City",
			CForm::required => true,
			CForm::disabled => $hasCreditCardPayment,
			CForm::size => 16,
			CForm::length => 16
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::placeholder => "Billing State",
			CForm::name => 'billing_state_id',
			CForm::required_msg => "Please select a state.",
			CForm::disabled => $hasCreditCardPayment,
			CForm::required => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_postal_code",
			CForm::placeholder => "*Billing Zip Code",
			CForm::required => true,
			CForm::disabled => $hasCreditCardPayment,
			CForm::required_msg => "Please enter the zip code of the billing address.",
			CForm::size => 16,
			CForm::length => 16
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "birthday_month",
			CForm::dd_type => "dd_user_data",
			CForm::dd_required => false,
			CForm::css_class => "custom-select",
			CForm::required_msg => "Please select birth month.",
			CForm::options => array('null' => 'Month') + CUserData::monthArray()
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "birthday_year",
			CForm::dd_type => "dd_user_data",
			CForm::dd_required => false,
			CForm::css_class => "custom-select",
			CForm::required_msg => "Please select birth year.",
			CForm::options => array('null' => 'Year') + CUserData::yearArray()
		));

		$storeObj = $Order->getStore();

		if ($storeObj->delayed_payment_order_minimum > $Order->grand_total)
		{
			$allowDelayedPayment = false;
		}

		if (isset($Session) && isset($Store) && CStore::storeSupportsStoreSpecificDeposit($Store->id, $Session->menu_id) && $allowDelayedPayment)
		{
			$defaultDeposit = $Order->getStore()->default_delayed_payment_deposit;
			if (empty($defaultDeposit))
			{
				$defaultDeposit = 20;
			}

			$sessionTS = strtotime($Session->session_start) - 518400; // allow delayed payment 6 days prior
			if (strtotime("now") < $sessionTS)
			{

				$Form->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => "is_store_specific_flat_rate_delayed_payment",
					CForm::required => false,
					CForm::label => 'Pay full balance now',
					CForm::value => '0'
				));
				$Form->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => "is_store_specific_flat_rate_delayed_payment",
					CForm::required => false,
					CForm::label => 'Pay $' . CTemplate::moneyFormat($defaultDeposit) . ' deposit now with remaining balance automatically processed 5 days prior to visit',
					CForm::value => '1'
				));
			}
		}
	}

	function runPublic()
	{


		CTemplate::noCache();

		$tpl = CApp::instance()->template();

		// USER is not logged in so show the cart and login/register form

		$tpl->assign('hide_store_selector', true); // Hide store selector on create account
		$tpl->assign('isCreate', true);
		$tpl->assign('isAdmin', false);
		$tpl->assign('form_account', form_account::process_account_creation($tpl)->Render());

		$tpl->assign('isLoggedIn', false);

		list($Cart, $Order, $OrderStore) = checkout_validation::getAndValidateCart($tpl);

		$tpl->assign('payment_enabled_store_credit', true);
		$tpl->assign('payment_enabled_gift_card', true);
		$tpl->assign('payment_enabled_coupon', true);

		checkout_validation::validateCoupon($Order, $Cart, $tpl);

		// most issues have to do with the menu from here on so bounce all types to the menu
		$tpl->assign('bounce_to', '/session-menu');

		// Not logged in so no account based discounts
		$Order->clearPreferred();
		$Order->recalculate();

		$defaultAssemblyFee = $Order->getMarkUp()->assembly_fee;
		$tpl->assign('defaultAssemblyFee', $defaultAssemblyFee);

		$validationResult = $Cart->validateForCheckout();
		if ($validationResult !== true)
		{
			$tpl->setStatusMsg("Your cart does not have all the items needed to proceed to checkout: $validationResult");
			CApp::bounce('/session-menu');
		}

		checkout_validation::validateFood($Order, $Cart, $tpl);

		$tpl->assign('avgCostPerServingEntreeServings', $Order->servings_core_total_count);
		$tpl->assign('avgCostPerServingEntreeCost', $Order->pcal_core_total);

		$gc_orders = $Cart->getGiftCardOrders();
		$isGiftCardOnlyOrder = ($Order->isOrderEmpty() && !empty($gc_orders));

		$tpl->assign('sessionTime', ($isGiftCardOnlyOrder ? '0' : $Order->findSession()->session_start));
		$tpl->assign('has_gift_card', !empty($gc_orders));
		$tpl->assign('session_id', $Order->findSession()->id);

		checkout_validation::validateFood($Order, $Cart, $tpl);

		if (isset($_POST['run_as_guest']) && $_POST['run_as_guest'] == "true")
		{
			$this->runAsGuest = true;
			$this->runCustomer();
		}
		else
		{
			$tpl->assign('allowGuest', false);
		}

		$sessionObj = $Order->getSessionObj(false);
		$action = COrders::getFullyQualifiedOrderTypeFromSession($sessionObj);
		$action = empty($action) ? '' : $action . '<br>';
		$tpl->assign('customerActionString', $action);

		$tpl->assign('store_id_of_order', !empty($Order->store_id) ? $Order->store_id : 0);

		$tpl->assign('sticky_nav_bottom_disable', true);
		$tpl->assign('cart_info', CUser::getCartIfExists());
		$tpl->assign('isGiftCardOnlyOrder', false);
	}

	/**
	 * @throws exception
	 */
	function runCustomer()
	{
		// -------------------------------------Setup
		CTemplate::noCache();


		ini_set('memory_limit', '96M');
		$tpl = CApp::instance()->template();

		$User = CUser::getCurrentUser();
		$User->getUsersLTD_RoundupOrders();

		$tpl->assign('ltd_roundup_orders', $User->ltd_roundup_orders);
		$tpl->assign('User', $User);

		// Agree to Dream Dinners T&C, should be true here anyhow
		if (!empty($_POST['customers_terms']))
		{
			$User->setUserPreference(CUser::TC_DREAM_DINNERS_AGREE, 1);
		}

		$canProvideNewDepositMechanisms = true;

		///get order from cart
		list($Cart, $DAO_orders, $OrderStore) = checkout_validation::getAndValidateCart($tpl);
		// -------------------------------------------------Edit Delivered Order
		$originalOrder = delivered_edit_order_mgr::initOriginalOrder($tpl, $DAO_orders, $Cart);

		// Shipping address and Special Instructions are POSTED her from checkout.  Store in the cart
		if (isset($_POST['shipping_address_line1']))
		{
			$shippingAddressArray = array();
			$shippingAddressArray['shipping_firstname'] = $_POST['shipping_firstname'];
			$shippingAddressArray['shipping_lastname'] = $_POST['shipping_lastname'];
			$shippingAddressArray['shipping_address_line1'] = $_POST['shipping_address_line1'];
			$shippingAddressArray['shipping_address_line2'] = $_POST['shipping_address_line2'];
			$shippingAddressArray['shipping_city'] = $_POST['shipping_city'];
			$shippingAddressArray['shipping_state_id'] = $_POST['shipping_state_id'];
			$shippingAddressArray['shipping_postal_code'] = $_POST['shipping_postal_code'];
			$shippingAddressArray['shipping_phone_number'] = $_POST['shipping_phone_number'];
			$shippingAddressArray['shipping_phone_number_new'] = $_POST['shipping_phone_number_new'];
			$shippingAddressArray['shipping_address_note'] = $_POST['shipping_address_note'];
			$shippingAddressArray['shipping_is_gift'] = (!empty($_POST['shipping_is_gift']) ? 1 : 0);
			$shippingAddressArray['shipping_gift_email_address'] = $_POST['shipping_gift_email_address'];

			$Cart->addShippingAddress($shippingAddressArray);

			//on edit order be sure to capture address changes
			if ($tpl->isEditDeliveredOrder && $originalOrder != null)
			{
				$originalOrder->orderAddress();

				$shipping_phone_number = $_POST['shipping_phone_number'];
				$shipping_phone_number_new = $_POST['shipping_phone_number_new'];
				$shipping_address_note = $_POST['shipping_address_note'];

				$originalOrder->orderAddress->firstname = $_POST['shipping_firstname'];
				$originalOrder->orderAddress->lastname = $_POST['shipping_lastname'];
				$originalOrder->orderAddress->address_line1 = $_POST['shipping_address_line1'];
				$originalOrder->orderAddress->address_line2 = $_POST['shipping_address_line2'];
				$originalOrder->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
				$originalOrder->orderAddress->is_gift = (!empty($_POST['shipping_is_gift']) ? 1 : 0);
				$originalOrder->orderAddress->address_note = trim(strip_tags($shipping_address_note));
				$originalOrder->orderAddress->email_address = $_POST['shipping_gift_email_address'];

				$originalOrder->orderAddressDeliveryProcessUpdate();
			}

			if (empty($_POST['address_book_select']))
			{
				// address book is set to new contact
				CAddress::addToAddressBook($shippingAddressArray, $User->id);
			}
			else if (is_numeric($_POST['address_book_select']))
			{
				CAddress::updateAddressBook($shippingAddressArray, $_POST['address_book_select'], $User->id);
			}
			else if ($_POST['address_book_select'] == 'new')
			{
				// address book is set to new contact
				CAddress::addToAddressBook($shippingAddressArray, $User->id);
			}
		}

		//handle adding address during checkout, if matching one doesn't already exist
		if (isset($_POST['billing_address']))
		{
			$addressArray = array();
			$addressArray['shipping_address_line1'] = $_POST['billing_address'];
			$addressArray['shipping_address_line2'] = $_POST['billing_address2'];
			$addressArray['shipping_city'] = $_POST['billing_city'];
			$addressArray['shipping_state_id'] = $_POST['billing_state_id'];
			$addressArray['shipping_postal_code'] = $_POST['billing_postal_code'];
			CAddress::addToAddressBook($addressArray, $User->id, CAddress::BILLING, true);
		}

		if (!empty($_POST['special_insts']))
		{
			$instructions = $_POST['special_insts'];
			$instructions = trim(strip_tags($instructions));
			$Cart->addSpecialInstructions($instructions);
		}
		else if (!isset($_POST['complete_order']))
		{
			$Cart->addSpecialInstructions("");
		}

		// check for partial account
		if ($User->isUserPartial())
		{
			// if they are not upgrading a current session_rsvp bounce them to account
			if (!CSession::getSessionRSVP($DAO_orders->findSession()->id, $User->id))
			{
				$tpl->setErrorMsg('Please complete your profile information prior to checkout.');
				CApp::bounce('/account');
			}
		}

		// platepoints enrollment during checkout
		$tpl->assign('can_enroll_in_platepoints', false);
		$tpl->assign('precheck_enroll_in_platepoints', false);

		//------------------------------------------PlatePoints Enroll Setup

		$tpl->assign('isLoggedIn', true);

		$tpl->assign('payment_enabled_store_credit', true);
		$tpl->assign('payment_enabled_gift_card', true);
		$tpl->assign('payment_enabled_coupon', true);

		// ----------------------------------------------------validation
		if ($DAO_orders->isShipping())
		{
			$sessionIsValid = CSession::isSessionValidForDeliveredOrder($DAO_orders->findSession()->id, $DAO_orders->getStore(), $DAO_orders->findSession()->menu_id, false, $DAO_orders->orderAddress->postal_code, excludeFull: true);
			if (!$sessionIsValid)
			{
				$tpl->setStatusMsg('The delivery date you selected is unavailable. Please choose a new delivery date below.');
				CApp::bounce('/box-delivery-date');
			}
		}

		checkout_validation::validateCoupon($DAO_orders, $Cart, $tpl);

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
			$Cart->addUserId(CUser::getCurrentUser()->id);
		}
		else if (!$tpl->isEditDeliveredOrder && $DAO_orders->user_id != $currentUserId)
		{
			//TODO: is it an error to have a previous and different user_id in the order/cart?
			$Cart->clearAllPayments(true);
			$Cart->addUserId(CUser::getCurrentUser()->id);
		}

		// -------------------------------------------------update order

		$DAO_orders->user_id = CUser::getCurrentUser()->id;
		$DAO_orders->refresh(CUser::getCurrentUser());
		$DAO_orders->recalculate();

		$defaultAssemblyFee = $DAO_orders->getMarkup()->assembly_fee;
		$tpl->assign('defaultAssemblyFee', $defaultAssemblyFee);

		$DAO_orders->family_savings_discount_version = 2;

		$DR_Ordering = false;
		if (CUser::getCurrentUser()->canOrderOnlineWithDreamRewards($DAO_orders->store_id))
		{
			$DAO_orders->dream_rewards_level = CUser::getCurrentUser()->dream_reward_level;
			$tpl->assign('DR_Online_Ordering_level', $DAO_orders->dream_rewards_level);
			$DAO_orders->recalculate();
			$DR_Ordering = true;
		}
		//$DR_Ordering = true;
		$tpl->assign('DR_Online_Ordering', $DR_Ordering);

		// --------------------------------------Validation
		$validationResult = $Cart->validateForCheckout();
		if ($validationResult !== true)
		{
			$tpl->setStatusMsg('Your cart does not have all the items needed to proceed to checkout: ' . $validationResult);
			CApp::bounce('/session-menu');
		}

		checkout_validation::validateFood($DAO_orders, $Cart, $tpl);

		$tpl->assign('avgCostPerServingEntreeServings', $DAO_orders->servings_core_total_count);
		$tpl->assign('avgCostPerServingEntreeCost', $DAO_orders->pcal_core_total);
		$tpl->assign('has_gift_card', false);

		$tpl->assign('isGiftCardOnlyOrder', false);
		$tpl->assign('store_id_of_order', !empty($DAO_orders->store_id) ? $DAO_orders->store_id : 0);

		// TODD Observe Only
		if ($DAO_orders->isTODD() && !$DAO_orders->isDreamTaste() && !$DAO_orders->isFundraiser())
		{
			$tpl->setErrorMsg("This order is for attendance of a Taste of Dream Dinners session. Please select a session here.");
			CApp::bounce('/session-menu');
		}

		$Form = new CForm('customer_food_checkout');
		$Form->Repost = true;
		$Form->Bootstrap = true;

		// SETUP PAYMENTS
		$paymentsInCart = $Cart->getAllPayments();
		$countSelectedCredits = 0;
		$totalSelectedCredits = 0;
		foreach ($paymentsInCart as $id => $cartPayment)
		{
			if ($cartPayment['payment_type'] == 'store_credit')
			{
				if (checkout_validation::validateStoredStoreCredit($cartPayment['paymentData']['store_credit_id']))
				{
					$countSelectedCredits++;
					$totalSelectedCredits += $cartPayment['amount'];
				}
			}
		}

		$tpl->assign('countSelectedCredits', $countSelectedCredits);
		$tpl->assign('totalSelectedCredits', $totalSelectedCredits);
		$tpl->assign('total_store_credit', $totalSelectedCredits);

		//debit gift card processing
		$DAO_session = $DAO_orders->findSession();

		$action = COrders::getFullyQualifiedOrderTypeFromSession($DAO_session);
		$action = empty($action) ? '' : $action . '<br>';
		$tpl->assign('customerActionString', $action);

		$isPreferred = $User->isUserPreferred();
		$tpl->assign('isPreferred', $isPreferred);

		$hasCreditCardPayment = $Cart->hasCreditCardPayment();

		/* All orders (for sessions 6 days or more in the future - enforced in BuildPaymentForm()) are now eligible for delayed payment.
		    per Valerie, Kevin, Laura  [CES 3-24-2020]
		*/ //$hasFutureSession = CUser::getCurrentUser()->hasPendingOrder(false, 10800);
		// passing in 10800 to shift the test by 3 hours.  This will return true for up to 3 hours past a
		// recent session

		$allowDelayedPayment = false;
		if ($DAO_session->delayedPaymentEligible($OrderStore))
		{
			$allowDelayedPayment = true;
		}

		$refPaymentTypeDataArray = array();
		if ($tpl->isEditDeliveredOrder)
		{
			//if edit order check cart for coupons and gc and adjust total due

			//compare totals
			$oTotal = $originalOrder->grand_total * 100;
			$nTotal = $DAO_orders->grand_total * 100;

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

			$tpl->assign('hide_remove_gift_card', true);
			$tpl->assign('hide_remove_coupon', true);
			$is_only_gift_cards = false;
			$refPaymentTypeDataArray = CEditOrderPaymentManager::reconcileAvailablePaymentMethods($paymentsInCart, $tpl->delta_total_diff, $tpl->delta_is_refund, $is_only_gift_cards);
			$tpl->assign('only_gift_cards', $is_only_gift_cards);
		}
		else
		{
			$refPaymentTypeDataArray = CPayment::getPaymentsStoredForReference(CUser::getCurrentUser()->id, $DAO_orders->store_id);
		}

		$tpl->assign('card_references', $refPaymentTypeDataArray);

		//build payment form
		self::buildPaymentForm($Form, null, $DAO_orders, $DAO_orders->getStore(), $DR_Ordering, $hasCreditCardPayment, $allowDelayedPayment);

		$platePointsStatus = CPointsUserHistory::getPlatePointsStatus($OrderStore, CUser::getCurrentUser());

		if (false && isset($_POST['enroll_in_plate_points']))
		{
			$SFICurrentValues = CUserData::buildSFIFormElementsNew($Form, $User, false);
			$enrollment_success = CPointsUserHistory::handleEvent($User, CPointsUserHistory::OPT_IN);
			CUserData::saveSFIFormElementsNew($Form, $User, $SFICurrentValues);
			if ($enrollment_success)
			{
				$month = $Form->value('birthday_month');

				if (CPointsUserHistory::isElgibleForBirthdayRewardAtEnrollment($User->home_store_id, $month, $User->id))
				{

					$metaData = CPointsUserHistory::getEventMetaData(CPointsUserHistory::BIRTHDAY_MONTH);
					$eventComment = 'Earned $' . $metaData['credit'] . ' birthday Dinner Dollars!';

					$enrollment_success = CPointsUserHistory::handleEvent($User, CPointsUserHistory::BIRTHDAY_MONTH, array(
						'comments' => $eventComment,
						'year' => date('Y'),
						'month' => $month
					));
				}
			}
		}

		$tpl->assign('canProvideNewDepositMechanisms', $canProvideNewDepositMechanisms);

		checkout_validation::setupDinnerDollars($platePointsStatus, $DAO_orders, $tpl, $Form);

		//------------meal customization
		$DAO_store = $DAO_orders->getStore();
		$DAO_session = $DAO_orders->findSession();
		$showCustomization = false;
		$hasCustomizationOptionsSelected = false;
		if ($OrderStore->supports_meal_customization && $DAO_session->isOpenForCustomization($DAO_store))
		{ //Store allows
			$customizableMealCount = COrders::getNumberOfCustomizableMealsFromItems($DAO_orders, $OrderStore->allow_preassembled_customization);
			if ($customizableMealCount > 0)
			{
				$orderCustomizationPrefObj = json_decode($DAO_orders->order_customization);
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

				$tpl->assign('has_meal_customization_selected', ($DAO_orders->opted_to_customize_recipes ? true : false));
				$tpl->assign('default_meal_customization_to_selected', ((is_null($DAO_orders->opted_to_customize_recipes) && $hasCustomizationOptionsSelected) ? true : false));

			}
		}

		$tpl->assign('should_allow_meal_customization', $showCustomization);
		$tpl->assign('allow_preassembled_customization', $OrderStore->allow_preassembled_customization);

		$DAO_store = $DAO_orders->getStore();
		$numberBagsRequired = 0;
		$bagFeeItemizationNote = '';
		if ($DAO_store->supports_bag_fee)
		{
			$numberBagsRequired = COrders::getNumberBagsRequiredFromItems($DAO_orders);
			$bagFeeItemizationNote = '(' . $numberBagsRequired . ' bag' . ($numberBagsRequired > 1 ? 's' : '') . ' * $' . $DAO_store->default_bag_fee . ')';
		}
		$tpl->assign('bagFeeItemizationNote', $bagFeeItemizationNote);
		$tpl->assign('numberBagsRequired', $numberBagsRequired);
		$tpl->assign('supports_bag_fee', $DAO_store->supports_bag_fee);
		$tpl->assign('default_bag_fee', $DAO_store->default_bag_fee);

		$Form->AddElement(array(
			CForm::type => CForm::Button,
			CForm::name => "complete_order",
			CForm::value => "Submit Order",
			CForm::disabled => true,
			CForm::css_class => "btn btn-primary btn-block btn-spinner btn-onclick-disable"
		));

		if (isset($_POST['complete_order']) && isset($_POST['customers_terms']))
		{
			try
			{
				if (!$Form->validate_CSRF_token())
				{
					$tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support. This message can also be caused by a double submission of the same page.");
					CApp::bounce('/payment', true);
				}

				$xssFilter = new InputFilter();
				$_POST = $xssFilter->process($_POST);

				$DAO_orders->setOrderInStoreStatus();
				$DAO_orders->setOrderMultiplierEligibility();

				$DAO_orders->setStoreCustomizationOptions();

				list($validationPassed, $StoreCreditArray, $giftCardArray, $creditCardArray) = checkout_validation::validateAndPreparePayments($Cart->getAllPayments(), $tpl, $DAO_orders->grand_total, $DAO_orders->store_id, true);

				if ($DAO_orders->isDreamTaste() || $DAO_orders->isFundraiser())
				{
					// no store credits can be used
					$StoreCreditArray = array();
				}

				if ($validationPassed)
				{
					$creditCardArray['ccNumber'] = str_replace("-", "", $creditCardArray['ccNumber']);
					if ($tpl->isEditDeliveredOrder)
					{
						$paymentsFromCart = $Cart->getAllPayments();
						//Determine Payment or Refund type
						if ($tpl->delta_is_refund)
						{
							if (isset($_POST['cc_pay_id_edit_order']))
							{
								//customer has selected previous cc
								$paymentType = 'CC_REFUND';
								$paymentsFromCart = $Cart->getCreditCardPayment(array($_POST['cc_pay_id_edit_order']));
							}
							else if (isset($_POST['gc_pay_id_edit_order']))
							{
								//customer has selected previous gc
								$paymentType = 'GC_REFUND';
								$paymentsFromCart = $Cart->getGiftCardPayment(array($_POST['gc_pay_id_edit_order']));
							}
							else if ($_POST['cc_pay_id_edit_order_multi'] && empty($_POST['gc_pay_id_edit_order_multi']))
							{
								//Refund multiple CCs only
								$paymentType = 'CC_REFUND';
								$paymentsFromCart = $Cart->getCreditCardPayment($_POST['cc_pay_id_edit_order_multi']);
							}
							else if ($_POST['gc_pay_id_edit_order_multi'] && empty($_POST['cc_pay_id_edit_order_multi']))
							{
								//Refund multiple GCs only
								$paymentType = 'GC_REFUND';
								$paymentsFromCart = $Cart->getGiftCardPayment($_POST['gc_pay_id_edit_order_multi']);
							}
							else if ($_POST['gc_pay_id_edit_order_multi'] && $_POST['cc_pay_id_edit_order_multi'])
							{
								//Refund multiple GCs only
								$paymentType = 'CC_GC_REFUND';
								$paymentsFromCart = $Cart->getGiftCardPayment($_POST['gc_pay_id_edit_order_multi']);
								$paymentsFromCart = array_merge($paymentsFromCart, $Cart->getCreditCardPayment($_POST['cc_pay_id_edit_order_multi']));
							}
							else
							{
								//Try to apply to previous credit cards
								$paymentType = 'CC_REFUND';
								$paymentsFromCart = $Cart->getAllCreditCardPayments();
							}
						}
						else
						{
							//Payment using Reference
							if (isset($_POST['cc_pay_id_edit_order']))
							{
								//customer has selected previous cc
								$paymentsFromCart = $Cart->getCreditCardPayment(array($_POST['cc_pay_id_edit_order']));
								$paymentType = CPayment::REFERENCE;
							}
							else if ($creditCardArray['ccNumber'] != false)
							{
								//this is a new CC
								$paymentType = CPayment::CC;
							}
							else
							{
								$paymentsFromCart = $Cart->getAllCreditCardPayments();
								$paymentType = CPayment::REFERENCE;
							}
						}

						if (is_array($giftCardArray))
						{
							//Add GC payments to exclude from other payment
							$paymentType = ($paymentType . '_' . CPayment::GIFT_CARD);
						}

						$rslt = $DAO_orders->processEditOrder($originalOrder, $Cart, $tpl->delta_boxes_differences, $paymentsFromCart, $paymentType, $creditCardArray, $StoreCreditArray, $giftCardArray, true);
					}
					else
					{
						// finally charge the remaining balance to the credit card
						$rslt = $DAO_orders->processNewOrderGC(CPayment::CC, $creditCardArray, $StoreCreditArray, $giftCardArray, true);
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
					CApp::bounce('/session');
					break;
				case 'edit_success':
					try
					{
						$theUser = CUser::getCurrentUser();

						COrdersDelivered::sendEditedOrderConfirmationEmail($User, $originalOrder);
						$Cart->emptyCart();
					}
					catch (exception $e)
					{
						CLog::RecordException($e);
					}

					CBrowserSession::setValue('dd_thank_you', 'checkout', false, true, false);
					CApp::bounce('/order-details?status=ec&order=' . $originalOrder->id, true);

					break;
				case 'success':
					try
					{

						$theUser = CUser::getCurrentUser();

						// if there is a gift card - the email function is informed and delivers the message
						COrders::sendConfirmationEmail($theUser, $DAO_orders, $DAO_orders->getGiftCards());
						CCustomerReferral::updateAsOrderedIfEligible($theUser, $DAO_orders);
						$Cart->emptyCart();
					}
					catch (exception $e)
					{
						CLog::RecordException($e);
					}

					// set a cookie so that analytics only records viewing the thank you page once
					CBrowserSession::setValue('dd_thank_you', 'checkout', false, true, false);
					CApp::bounce('/order-details?order=' . $DAO_orders->id, true);

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
			$GiftCardPaymentsTempArray = array();
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

		if ($DAO_orders)
		{
			//build the order
			$DAO_orders->refresh(CUser::getCurrentUser());
			$DAO_orders->recalculate();

			$tpl->assign('orderInfo', $DAO_orders->toArray());
			$tpl->assign('session_id', $DAO_orders->findSession()->id);
		}

		$Coupon = $DAO_orders->getCoupon();

		// We must recheck validity of coupon since the ussr may have removed items, etc.. since coupon was added.
		if ($Coupon)
		{
			if ($DAO_orders->isDelivered())
			{
				$result = $Coupon->isValidForDelivered($DAO_orders, $Cart->getMenuId());
			}
			else
			{
				$result = $Coupon->isValid($DAO_orders, $Cart->getMenuId());
			}

			if (!empty($result))
			{
				$Coupon = null;
				$DAO_orders->removeCoupon();
				$DAO_orders->recalculate();

				$errorMessages = implode("<br />", $result);
				$tpl->setErrorMsg("A promo code was removed for the following reasons:<br /> " . $errorMessages);
				$Cart->addOrder($DAO_orders);
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

		$this->setupCCIcons($DAO_orders, $tpl, false);

		$tpl->assign('productCount', $DAO_orders->countProducts());
		if ($DAO_orders->countItems() > 0)
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
		$tpl->assign('grand_total', $DAO_orders->grand_total);

		$tpl->assign('sticky_nav_bottom_disable', true);
		$tpl->assign('cart_info', CUser::getCartIfExists());
	}

	function setupCCIcons($Order, $tpl, $isGiftCardOnlyOrder)
	{
		// 07-10-2007  [DBENSON]
		//
		// Determine icons for credit card types that are accepted by the store
		// Note: The arCardIcons array is used to ensure proper sort order
		// =========================================================================
		$arCardIcons = array(
			CPayment::VISA => array(
				'accepted' => false,
				'filename' => 'visa_icon.gif'
			),
			CPayment::MASTERCARD => array(
				'accepted' => false,
				'filename' => 'mastercard_icon.gif'
			),
			CPayment::DISCOVERCARD => array(
				'accepted' => false,
				'filename' => 'discover_icon.gif'
			),
			CPayment::AMERICANEXPRESS => array(
				'accepted' => false,
				'filename' => 'amex_icon.gif'
			)
		);

		$Store = $Order->getStore();
		if ($Store && !$isGiftCardOnlyOrder)
		{
			$arCreditCardTypes = $Store->getCreditCardTypes();
			foreach ($arCreditCardTypes as $card)
			{
				switch ($card)
				{
					case CPayment::VISA:
						$arCardIcons[CPayment::VISA]['accepted'] = true;
						break;

					case CPayment::MASTERCARD:
						$arCardIcons[CPayment::MASTERCARD]['accepted'] = true;
						break;

					case CPayment::DISCOVERCARD:
						$arCardIcons[CPayment::DISCOVERCARD]['accepted'] = true;
						break;

					case CPayment::AMERICANEXPRESS:
						$arCardIcons[CPayment::AMERICANEXPRESS]['accepted'] = true;
						break;

					default:
						break;
				}
			}
		}
		else
		{
			$arCardIcons[CPayment::VISA]['accepted'] = true;
			$arCardIcons[CPayment::MASTERCARD]['accepted'] = true;
			$arCardIcons[CPayment::DISCOVERCARD]['accepted'] = true;
			$arCardIcons[CPayment::AMERICANEXPRESS]['accepted'] = true;
		}

		$tpl->assign('arCardIcons', $arCardIcons);
	}

}