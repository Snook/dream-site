<?php
require_once('includes/CCart2.inc');
require_once('includes/class.inputfilter_clean.php');
require_once('form/account.php');
require_once('DAO/BusinessObject/COrders.php');
require_once('DAO/BusinessObject/CPayment.php');
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CUser.php');
require_once('DAO/BusinessObject/CUserData.php');
require_once('DAO/BusinessObject/CCouponCodeProgram.php');
require_once('DAO/BusinessObject/CGiftCard.php');
require_once('DAO/BusinessObject/CPointsUserHistory.php');
require_once('DAO/BusinessObject/CPointsCredits.php');
require_once('payment/PayPalProcess.php');
require_once('payment/PayPalProcess.php');
require_once('includes/CAppUtil.inc');
require_once('page/customer/checkout.php');


class page_checkout_gift_card extends CPage
{

	private $runAsGuest = false;

	static function buildPaymentForm($Form, $User = null, $Session = null, $Store = null)
	{
		require_once('DAO/BusinessObject/CPayment.php');

		//set defaults
		if (!$User)
		{
			$User = CUser::getCurrentUser();
		}

		if (!isset($_POST['address']))
		{
			$Addr = $User->getPrimaryAddress();

			if ($Addr)
			{
				$Form->DefaultValues['billing_address'] = $Addr->address_line1;
				$Form->DefaultValues['billing_postal_code'] = $Addr->postal_code;
			}
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "ccNameOnCard",
			CForm::autocomplete => false,
			CForm::placeholder => "*Name on Credit Card",
			CForm::required => true,
			CForm::required_msg => "Please enter your name as it appears on the credit card.",
			CForm::css_class => "form-control",
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
			CForm::number => false,
			CForm::css_class => "form-control",
			CForm::size => 16,
			CForm::length => 16
		));

		$cardOptions = array('null' => 'Card Type');
		$cardOptions [CPayment::VISA] = 'Visa';
		$cardOptions [CPayment::MASTERCARD] = 'MasterCard';
		$cardOptions [CPayment::DISCOVERCARD] = 'Discover';
		$cardOptions [CPayment::AMERICANEXPRESS] = 'American Express';

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccType",
			CForm::required => true,
			CForm::required_msg => "Please select a card type.",
			CForm::css_class => "custom-select",
			CForm::options => $cardOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccMonth",
			CForm::required => true,
			CForm::css_class => "custom-select",
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
			CForm::css_class => "custom-select",
			CForm::required => true,
			CForm::required_msg => "Please select an expiration year.",
			CForm::options => $yearOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "ccSecurityCode",
			CForm::autocomplete => false,
			CForm::placeholder => "*Security Code",
			CForm::required => true,
			CForm::required_msg => "Please enter the security code.",
			CForm::css_class => "form-control",
			CForm::pattern => '^[0-9]{3}$',
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
			CForm::css_class => "form-control",
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_address2",
			CForm::placeholder => "Address 2",
			CForm::css_class => "form-control",
			CForm::required => false,
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_city",
			CForm::placeholder => "*Billing City",
			CForm::required => true,
			CForm::required_msg => "Please enter the city of your billing address.",
			CForm::css_class => "form-control",
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => "billing_state_id",
			CForm::placeholder => "*Billing State",
			CForm::required => true,
			CForm::required_msg => "Please enter the street address of your billing address.",
			CForm::css_class => "form-control",
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_postal_code",
			CForm::placeholder => "*Billing Zip Code",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::required_msg => "Please enter the zip code of the billing address.",
			CForm::size => 16,
			CForm::length => 16
		));
	}

	function runPublic()
	{

		// -------------------------------------------------initializations/Load Cart and Order
		CApp::forceSecureConnection();

		CTemplate::noCache();

		$tpl = CApp::instance()->template();

		// USER is not logged in so show the cart and login/register form
		$tpl->assign('user_id', 0);
		$tpl->assign('user_email', 'none');

		$tpl->assign('hide_store_selector', true); // Hide store selector on create account
		$tpl->assign('isCreate', true);
		$tpl->assign('isAdmin', false);
		$tpl->assign('form_account', form_account::process_account_creation($tpl)->Render());

		$tpl->assign('isLoggedIn', false);

		$Cart = CCart2::instance();

		$tpl->assign('payment_enabled_store_credit', false);
		$tpl->assign('payment_enabled_gift_card', false);
		$tpl->assign('payment_enabled_coupon', false);

		if (isset($_POST['remove']))
		{
			if ($_POST['remove'] == "gift_card_purchase")
			{
				$Cart->removeGiftCardOrder($_POST['gcoid']);
				$gc_orders = $Cart->getGiftCardOrders();

				if (empty($gc_orders))
				{
					// there is nothing to purchase so bounce
					$tpl->setStatusMsg("There is nothing left in the cart to order.");
					CApp::bounce("/gift-card-order");
				}
			}
		}

		/// ?????
		$validationResult = $Cart->validateForCheckout();
		if ($validationResult !== true)
		{
			$tpl->setErrorMsg("Your cart does not have all the items needed to proceed to checkout: $validationResult");
			CApp::bounce($tpl->bounce_to);
		}

		$gc_orders = $Cart->getGiftCardOrders();
		$isGiftCardOnlyOrder = true;

		$tpl->assign('sessionTime', '0');
		$tpl->assign('has_gift_card', true);

		//------------------------------------------------------------------------------ Final setup

		$tpl->assign('isGiftCardOnlyOrder', $isGiftCardOnlyOrder);

		$this->runAsGuest = true;
		$this->runCustomer();
	}

	function runCustomer()
	{
		// -------------------------------------Setup
		CTemplate::noCache();

		CApp::forceSecureConnection();
		ini_set('memory_limit', '96M');
		$tpl = CApp::instance()->template();

		$User = CUser::getCurrentUser();

		$tpl->assign('user_id', $User->id);
		$tpl->assign('user_email', $User->primary_email);

		///get order from cart
		$Cart = CCart2::instance();

		$tpl->assign('isLoggedIn', true);

		$tpl->assign('payment_enabled_store_credit', false);
		$tpl->assign('payment_enabled_gift_card', false);
		$tpl->assign('payment_enabled_coupon', false);

		if (isset($_POST['remove']) && !$this->runAsGuest)
		{
			if ($_POST['remove'] == "gift_card_purchase" && !$this->runAsGuest)
			{
				$Cart->removeGiftCardOrder($_POST['gcoid']);
                $gc_orders = $Cart->getGiftCardOrders();

				if (empty($gc_orders))
				{
					// there is nothing to purchase so bounce
					$tpl->setStatusMsg("There is nothing left in the cart to order.");
					CApp::bounce("/gift-card-order");
				}
			}
		}

		if ($this->runAsGuest)
		{
			$tpl->assign('allowGuest', true);
		}
		else
		{
			$tpl->assign('allowGuest', false);
		}

		// --------------------------------------Validation
		/// ?????
		$validationResult = $Cart->validateForCheckout();
		if ($validationResult !== true)
		{
			$tpl->setErrorMsg('Your cart does not have all the items needed to proceed to checkout: ' . $validationResult);

			if (empty($tpl->bounce_to))
			{
				$tpl->bounce_to = "/gift-card-cart";
			}

			CApp::bounce($tpl->bounce_to);
		}

		$Form = new CForm('customer_gift_card_checkout');
		$Form->Repost = true;

		$gc_orders = $Cart->getGiftCardOrders();
		$isGiftCardOnlyOrder = true;

		if (isset($_POST['checkout_submit']))
		{
            $captcha_error = false;

            if (isset($_POST['g-recaptcha-response']))
            {
                  if (!CAppUtil::validateGoogleCaptchaResponse($_POST['g-recaptcha-response']))
                  {
                      $captcha_error = "Captcha Invalid";

                  }
            }
            else
            {
                $captcha_error = "No Captcha response provided";

            }

			if (!$Form->validate_CSRF_token())
			{
				$tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support. 
				This message can also be caused by a double submission of the same page.<br /><br /><i>If the problem persists try reloading the page.");
				$Form->setCSRFToken();
			}
			else if (!$captcha_error)
			{

				$guestID = null;
				$GuestEmail = false;

				if (CUser::isLoggedIn())
				{
					$guestID = CUser::getCurrentUser()->id;
					$GuestEmail = CUser::getCurrentUser()->primary_email;
				}

				if (CUser::isLoggedIn() || (!empty($_POST['primary_email']) && !empty($_POST['confirm_email_address']) && $_POST['primary_email'] == $_POST['confirm_email_address']))
				{
					if (!$GuestEmail)
					{
						$GuestEmail = $_POST['primary_email'];
					}

					$hadError = $this->processGiftCardOrder($gc_orders, $tpl, $Cart, $GuestEmail, $guestID);
					if (!$hadError)
					{
						$Cart->clearAllPayments();
						$Cart->clearGiftCards();

						$gcList = DAO_CFactory::create('gift_card_order');
						$gcList->query("SELECT GROUP_CONCAT(order_confirm_id) AS order_confirm_id FROM gift_card_order WHERE id IN (" . implode(',', $gc_orders) . ") AND paid = 1 AND is_deleted = 0");
						$gcList->fetch();

						// set a cookie so that analytics only records viewing the thank you page once
						CBrowserSession::setValue('dd_thank_you', 'checkout_giftcard', false, true, false);
						CApp::bounce('/order-details-gift-card?orders=' . $gcList->order_confirm_id, true);
					}
					else
					{
						// the processGiftCardOrder has already called setErrorMessage, just continue
						$Form->setCSRFToken();
					}
				}
				else
				{
					$tpl->setErrorMsg("There is a problem with the Billing Email Address");
					$Form->setCSRFToken();
					//CApp::bounce('/checkout-gift-card', true);
				}
			}
			else
            {
                $tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support.");
                $Form->setCSRFToken();
            }
		}
		else
		{
            $Form->setCSRFToken();
		}

		$tpl->assign('has_gift_card', !empty($gc_orders));

		$tpl->assign('isGiftCardOnlyOrder', $isGiftCardOnlyOrder);

		$tpl->assign('store_id_of_order', !empty($Order->store_id) ? $Order->store_id : 0);

		$tpl->assign('gc_order_ids', implode("^", $gc_orders));

		if ($isGiftCardOnlyOrder)
		{
			// If they are only buying a gift card remove any gift card or store credit payments from the cart
			/// ?????
			//$Cart->removeNonCreditPayments();
		}

		// ---------------------------------------------------set up form and prepare payment info

		//build payment form
		self::buildPaymentForm($Form, null, null, ($isGiftCardOnlyOrder ? null : $Order->getStore()));

		$canProvideNewDepositMechanisms = false;

		$tpl->assign('canProvideNewDepositMechanisms', $canProvideNewDepositMechanisms);

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => "complete_order",
			CForm::value => "Submit Order",
			CForm::css_class => "btn btn-primary btn-block btn-spinner btn-onclick-disable"
		));

		$billing_name_sc = $User->firstname . " " . $User->lastname;
		$billing_address_sc = $Form->value('billing_address');
		$billing_zip_sc = $Form->value('billing_postal_code');

		$tpl->assign('couponCode', false);

		$formArray = $Form->render();
		$tpl->assign('form_payment', $formArray);

		if (isset($_GET['err']) && $_GET['err'] == 'true')
		{
			$tpl->assign('CC_error', true);
		}

		$this->setupCCIcons(null, $tpl, $isGiftCardOnlyOrder);

		$tpl->assign('hasItems', false);
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

		$arCardIcons[CPayment::VISA]['accepted'] = true;
		$arCardIcons[CPayment::MASTERCARD]['accepted'] = true;
		$arCardIcons[CPayment::DISCOVERCARD]['accepted'] = true;
		$arCardIcons[CPayment::AMERICANEXPRESS]['accepted'] = true;

		$tpl->assign('arCardIcons', $arCardIcons);
	}

	function processGiftCardOrder($GCIDList, $tpl, $retrieveCCData, $guestEmail, $guestID = null, $order_id = null, $creditCardArray = null)
	{
		$giftCardObjArray = array();
		$hadError = false;

		if (CGiftCard::isSmartTransactionsAliveAndWell())
		{
			$error = 'noError';
			$giftcardTotal = 0.00;

			$gcList = DAO_CFactory::create('gift_card_order');
			$gcList->query("SELECT * FROM gift_card_order WHERE id IN (" . implode(',', $GCIDList) . ") AND paid = 0 AND is_deleted = 0");

			while ($gcList->fetch())
			{
				$giftCardObjArray[] = clone($gcList);

				$giftcardTotal += $gcList->initial_amount;

				if ($gcList->media_type == 'PHYSICAL')
				{
					$giftcardTotal += COrders::GIFT_CARD_SHIPPING;
				}
			}

			$validationPassed = true;
			if ($retrieveCCData)
			{
				list($validationPassed, $StoreCreditArray, $giftCardArray, $creditCardArray) = checkout_validation::validateAndPreparePayments(array(), $tpl, $giftcardTotal, false);
			}

			$creditCardArray['city'] =  isset($_POST['billing_city']) ? $_POST['billing_city'] : false;;
			$creditCardArray['state_id'] =  isset($_POST['billing_state_id']) ? $_POST['billing_state_id'] : false;;

			// if called by script - (CSRF mitigation should help prevent that) then the CSC maybe absent which defeats the validation - for now let's check here but consider moving to checkout_validation::doCreditCardValidation
			if (empty($creditCardArray['ccSecurityCode']) || !is_numeric($creditCardArray['ccSecurityCode']))
			{
				$validationPassed = false;
			}

			if ($validationPassed)
			{
				require_once 'includes/payment/PayPalProcess.php';
				$process = new PayPalProcess();
				$result = $process->processGiftCardOrder($guestEmail, $creditCardArray['ccNameOnCard'], $giftcardTotal, $creditCardArray['ccNumber'], $creditCardArray['ccMonth'], $creditCardArray['ccYear'], $creditCardArray['ccSecurityCode'], $creditCardArray['billing_postal_code'], $creditCardArray['billing_address'],'S',$creditCardArray['city'],$creditCardArray['state_id']);

				$payPalResult = $process->getResult();
				$refNum = $payPalResult['PNREF'];

				$obfNum = str_repeat('X', (strlen($creditCardArray['ccNumber']) - 4)) . substr($creditCardArray['ccNumber'], -4);
			}
			else
			{
				$result[0] = 'validationError';
			}

			if ($result[0] == 'success')
			{
				$updatedGiftCardArray = array();
                $GiftCardSystemFailure = false;
                $voidList = array();
                $hasVoidedCCPayment = false;
				foreach ($giftCardObjArray as $thisGCOrderObj)
				{

					$confirmNumber = COrders::generateConfirmationNum();

					if ($thisGCOrderObj->media_type == 'PHYSICAL')
					{
						$result = CGiftCard::completePhysicalCardPurchaseTransaction($thisGCOrderObj->id, $guestEmail, $obfNum, $refNum, $creditCardArray['ccType'], $creditCardArray['ccNameOnCard'], $creditCardArray['billing_address'], $creditCardArray['billing_postal_code'], $confirmNumber, 'CUST_CART', 'null', $guestID);

						if (!$result) {
                            // update failed - should be rare - email and event trace generated in completePhysicalCardPurchaseTransaction
                            // paid and processed flags and other updates should not have occurred but you should probably investigate if it happens
                            $tpl->setErrorMsg("An error occurred when recording your Gift card order. Please contact Dream Dinners customer support at (360) 804-2020.");

                            if (!$hasVoidedCCPayment)
                            {
                                $process = new PayPalProcess();
                                $voidResult = $process->voidGiftCardPayment($guestEmail, $refNum);
                                if ($voidResult != 'success')
                                {
                                    CLog::RecordIntense("Failure in attempting to void CreditCard payment after Gift Card System failure.", 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
                                }

                                $hasVoidedCCPayment = true;
                            }

                            $GiftCardSystemFailure = true;

						}
						else
                        {
                            $updatedGiftCardArray[] = $result;
                        }

					}
					else
					{
                        list($newAccountNumber, $thisTransID, $AuthCode) = CGiftCard::obtainAccountNumberAndLoadWithRetry($thisGCOrderObj->initial_amount, 'M', $thisGCOrderObj->to_name, $thisGCOrderObj->recipient_email_address);

						if ($newAccountNumber)
						{
							$result = CGiftCard::completeNewAccountTransaction($thisGCOrderObj->id, $guestEmail, $obfNum, $refNum, $creditCardArray['ccType'], $newAccountNumber, $creditCardArray['ccNameOnCard'], $creditCardArray['billing_address'], $creditCardArray['billing_postal_code'], $confirmNumber, 'CUST_CART', 'null', $guestID);

							if (!$result)
							{
                                // update failed - should be EXCEEDINGLY rare - email and event trace generated in completeNewAccountTransaction
                                // GC Load wil be rolled back - paid and processed flags and other updates should not have occurred but you should probably investigate if it happens
								$tpl->setErrorMsg("An error occurred when recording your eGift card order. Please try again later or contact Dream Dinners customer support at (360) 804-2020.");
                                $GiftCardSystemFailure = true;

                                if (!$hasVoidedCCPayment)
                                {
                                    $process = new PayPalProcess();
                                    $voidResult = $process->voidGiftCardPayment($guestEmail, $refNum);
                                    if ($voidResult != 'success')
                                    {
                                        CLog::RecordIntense("Failure in attempting to void CreditCard payment after Gift Card System failure.", 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
                                    }

                                    $hasVoidedCCPayment = true;
                                }

                            }
							else
                            {
                                $result->new_account_number = $newAccountNumber;
                                $updatedGiftCardArray[] = $result;
                                $voidList[$thisTransID] = array("transID" => $thisTransID, "authCode" => $AuthCode, "GCO" => $thisGCOrderObj);
                            }
						}
						else
						{
							//handle this very nasty problem
							CLog::RecordIntense("obtainAccountNumberAndLoad Failure", 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
                            if (!$hasVoidedCCPayment)
                            {
                                $process = new PayPalProcess();
                                $voidResult = $process->voidGiftCardPayment($guestEmail, $refNum);
                                if ($voidResult != 'success')
                                {
                                    CLog::RecordIntense("Failure in attempting to void CreditCard payment after Gift Card System failure.", 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
                                }

                                $hasVoidedCCPayment = true;
                            }

                            $GiftCardSystemFailure = true;
                            $tpl->setErrorMsg("An error occurred when recording your Gift card order. Please contact Dream Dinners customer support at (360) 804-2020.");
						}
					}
				} // end GC loop

				if (!$GiftCardSystemFailure)
				{
                    $paymentData = array(
                        'credit_card_number' => $creditCardArray['ccNumber'],
                        'billing_name' => $creditCardArray['ccNameOnCard'],
                        'billing_address' => $creditCardArray['billing_address'],
                        'billing_zip' => $creditCardArray['billing_postal_code'],
                        'primary_email' => $guestEmail,
                        'payment_card_type' => $creditCardArray['ccType'],
                        'purchase_date' => date("Y-m-d H:i:s")
                    );

                    // send a separate confirmation
                    CGiftCard::sendGCOrderReceiptEmail($updatedGiftCardArray, $paymentData, $giftcardTotal);

                    foreach($updatedGiftCardArray as $ThisGCO)
                    {
                        if ($ThisGCO->media_type = 'VIRTUAL')
                        {
                            CGiftCard::sendVirtualGiftCard($ThisGCO, $ThisGCO->new_account_number);
                        }
                    }


                }
				else
                {
                    foreach($voidList as $thisTransID => $TransData)
                    {
                        $voidResult = CGiftCard::voidTransaction($thisTransID, $TransData['authCode']);

                        if (!$voidResult)
                        {
                            CLog::RecordNew(CLog::ERROR, "Error voiding transaction durng failure: " . $thisTransID);
                        }
                        else
                        {
                            // TODO: revert update to gift_card_order and record in event log
                            $reverter = new DAO();
                            $reverter->query("update gift_card_order set paid = 0, processed = 0, gift_card_account_number = null where id = " . $TransData["GCO"]->id);

                        }
                    }

                    $giftCardFailureOnlySoAddCardsBackToTheCart = true;
                    CLog::RecordNew(CLog::ERROR, "GC System failure. voided Credit Card and any suucessful GC loads.");
                }
			} // end CC success
			else if ($result[0] == 'validationError')
			{
				$tpl->setErrorMsg('Validation Error: Please check your Card Credit information.');
				$hadError = true;
			}
			else
			{
				$tpl->setErrorMsg('The payment for the Gift Card was declined. Please try the order again later or contact Dream Dinners customer support at (360) 804-2020.');
				$hadError = true;
			}
		}
		else
		{

			$tpl->setErrorMsg('We apologize but it appears that the Gift Card system is unresponsive. Please try the order again later or contact Dream Dinners customer support at (360) 804-2020');
			$hadError = true;
		}

		return $hadError;
	}
}

?>