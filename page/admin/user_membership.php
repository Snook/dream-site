<?php
require_once("includes/CPageAdminOnly.inc");
require_once("includes/DAO/BusinessObject/CStatesAndProvinces.php");
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CEnrollmentPackage.php');
require_once('includes/DAO/BusinessObject/CUserData.php');
require_once('includes/DAO/BusinessObject/CStoreCredit.php');
require_once('includes/DAO/BusinessObject/CPointsUserHistory.php');
require_once('includes/DAO/BusinessObject/CPayment.php');
require_once('includes/DAO/BusinessObject/CProduct.php');
require_once('includes/DAO/BusinessObject/CProductOrders.php');
require_once('includes/DAO/BusinessObject/CMembershipHistory.php');

require_once('fpdf/class_multicelltag.php');
require_once('includes/class.inputfilter_clean.php');

class page_admin_user_membership extends CPageAdminOnly
{

	function runManufacturerStaff()
	{
		$this->runMembership();
	}

	function runFranchiseStaff()
	{
		$this->runMembership();
	}

	function runFranchiseLead()
	{
		$this->runMembership();
	}

	function runEventCoordinator()
	{
		$this->runMembership();
	}

	function runOpsLead()
	{
		$this->runMembership();
	}

	function runFranchiseManager()
	{
		$this->runMembership();
	}

	function runFranchiseOwner()
	{
		$this->runMembership();
	}

	function runHomeOfficeStaff()
	{
		$this->runMembership();
	}

	function runHomeOfficeManager()
	{
		$this->runMembership();
	}

	function runSiteAdmin()
	{
		$this->runMembership();
	}

	function runMembership()
	{
		$tpl = CApp::instance()->template();

		$User = DAO_CFactory::create('user');
		$User->id = $_GET['id'];
		$User->find(true);

		$User->getPlatePointsSummary();
		$User->getMembershipsArray(true, true, true, true);

		$Store = CBrowserSession::getCurrentFadminStoreObj();

		if (!$Store->supports_membership && !$Store->supports_new_memberships)
		{

			$toURL = "/backoffice/main";
			if (!empty($_REQUEST['back']))
			{
				$toURL = $_REQUEST['back'];
			}

			$tpl->setErrorMsg("This store does not support Meal Prep+.");
			CApp::bounce($toURL);
		}

		$tpl->assign('supports_new_memberships', $Store->supports_new_memberships);

		$Store->salesTax = $Store->getCurrentSalesTaxObj();

		$PaymentForm = new CForm();
		$PaymentForm->Bootstrap = true;

		$Form = new CForm('enroll_submit');
		$Form->Bootstrap = true;

		if (!empty($_POST['enroll_submit']))
		{
			if (!$Form->validate_CSRF_token())
			{
				$tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support.
				This message can also be caused by a double submission of the same page.<br /><br /><i>If the problem persists try reloading the page.</i>");
				$Form->setCSRFToken();
			}
			else
			{

				$tpl->assign("order_conversion_results", false);

				try
				{

					$uberObj = new DAO();
					$uberObj->query('START TRANSACTION');

					$product = DAO_CFactory::create('product');
					$product->id = $_POST['product'];

					if ($product->find(true))
					{
						$productOrder = DAO_CFactory::create('product_orders');
						$productOrder->user_id = $User->id;
						$productOrder->store_id = $Store->id;

						$productItems = DAO_CFactory::create('product_orders_items');
						$productItems->product_id = $product->id;
						$productItems->product_membership_initial_menu = $_POST['initial_menu'];
						$productItems->product_membership_status = CUser::MEMBERSHIP_STATUS_CURRENT;
						$productItems->quantity = 1;
						$productItems->item_cost = $product->price;

						$discountCoupon = false;
						if (!empty($_POST['discount_coupon']))
						{
							$discountCoupon = $_POST['discount_coupon'];
						}

						$membership_id = $productOrder->processOrder($productItems, $discountCoupon);
						// note: membership Id is the product_orders_items id

						$ProductPayment = DAO_CFactory::create('product_payment');
						$validation_result = $ProductPayment->parseValidatePrepare($_POST, $User->id, $Store->id, $productOrder->grand_total);
					}

					if (!$validation_result['success'])
					{
						$tpl->setErrorMsg("Validation Error: " . $validation_result['human_readable_error']);
						$tpl->assign("error_occurred", true);
						$Form->setCSRFToken();
					}
					else
					{
						$paymentResult = $ProductPayment->process($productOrder);

						if ($paymentResult['success'])
						{
							$uberObj->query('COMMIT');

							$conversion_results = $this->convertExistingOrdersToMembership($Store->id, $_POST['initial_menu'], $User->id, $membership_id);
							$tpl->assign("order_conversion_results", $conversion_results);

							$meta_arr = array('store_id' => $Store->id, 'product_id' => $product->id);
							CMembershipHistory::recordEvent($User->id, $membership_id,CMembershipHistory::MEMBERSHIP_PURCHASED, $meta_arr);

							$currentMenu = CMenu::getMenuInfo(CMenu::getCurrentMenuId());
							$revenueEvent = DAO_CFactory::create('revenue_event');
							$revenueEvent->event_type = 'MEMBERSHIP_PURCHASE';
							$revenueEvent->event_time = date("Y-m-d H:i:s");
							$revenueEvent->store_id = $Store->id;
							$revenueEvent->menu_id = $currentMenu['id'];
							$revenueEvent->amount =  $product->price;
							$revenueEvent->session_amount =  $product->price;
							$revenueEvent->session_id = 'null';
							$revenueEvent->final_session_id =  'null';
							$revenueEvent->order_id =   'null';
							$revenueEvent->membership_id = $membership_id;
							$revenueEvent->positive_affected_month = $currentMenu['menu_start'];
							$revenueEvent->negative_affected_month = 'null';
							$revenueEvent->insert();

							// get membership status for this order to send in email
							$User->getMembershipStatus(false, true, $membership_id);

							CEmail::membershipOrderConfirmation($User, $productOrder);

							// update user object for current display on management page
							$User->getMembershipsArray(true, true, true, true);
						}
						else
						{
							$uberObj->query('ROLLBACK');
							$tpl->setErrorMsg("Payment Error: " . $paymentResult['human_readable_error']);
							$tpl->assign("error_occurred", true);
							$Form->setCSRFToken();

						}

					}
				}
				catch (InvalidCouponException $e)
				{
					$errorString = "The coupon is not valid.<br />";

					foreach($e->errorArray as $thisError)
					{
						$errorString .= CCouponCode::getCouponErrorUserText($thisError) . "<br />";
					}

					$tpl->setErrorMsg($errorString);
					$tpl->assign("error_occurred", true);
					$uberObj->query('ROLLBACK');
					$Form->setCSRFToken();


				}
				catch (exception $e)
				{
					$Form->setCSRFToken();
					$uberObj->query('ROLLBACK');
					CLog::RecordException($e);
				}
			}
		}
		else
		{
			$Form->setCSRFToken();
		}

		$this->buildPaymentForm($PaymentForm, $User, $Store);

		$toTransactionOptions = array(
			'' => 'Payment',
			'cash' => 'Cash',
			'check' => 'Check',
			'newcc' => 'New Credit Card'
		);

		$refPaymentTypeDataArray = CPayment::getPaymentsStoredForReference($User->id, $Store->id);

		if (!empty($refPaymentTypeDataArray))
		{
			foreach ($refPaymentTypeDataArray as $thisRef)
			{
				$paymentTypeArray['REF_' . $thisRef['payment_id']] = "Reference " . $thisRef['card_type'] . " " . $thisRef['cc_number'] . " " . date("(m-d-y)", $thisRef['date']);
			}
		}

		if (!empty($paymentTypeArray))
		{
			$toTransactionOptions = array_merge($toTransactionOptions, $paymentTypeArray);
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'payment_type',
			CForm::required => true,
			CForm::options => $toTransactionOptions
		));

		$membershipProducts = CProduct::getProductMembership();
		$membershipOptions = array('' => 'Select Membership');

		foreach ($membershipProducts as $id => $product)
		{
			$membershipOptions[$product->id] = array(
				'title' => $product->product_title,
				'data' => array(
					'data-price' => $product->price,
					'data-tax' => ((!empty($Store->salesTax->other2_tax)) ? $Store->salesTax->other2_tax : 0)
				)
			);
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'product',
			CForm::required => true,
			CForm::options => $membershipOptions
		));

		$activeMenus = CMenu::getActiveMenuArray();
		$menuOptions = array('' => 'Starting Menu');

		foreach ($activeMenus as $id => $menu)
		{
			$menuOptions[$menu['id']] = $menu['menu_name'];
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'initial_menu',
			CForm::required => true,
			CForm::options => $menuOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::value => 'Enroll guest in membership',
			CForm::css_class => 'btn btn-primary btn-block',
			CForm::name => 'enroll_submit'
		));

		$tpl->assign('user', $User);
		$tpl->assign('form_payment', $PaymentForm->Render());
		$tpl->assign('form_membership', $Form->Render());
	}

	function convertExistingOrdersToMembership($store_id, $menu_id, $user_id, $membership_id)
	{
		$order_retriever = new DAO();


		$thisMorning = date("Y-m-d 00:00:00");

		$order_retriever->query("select o.id from booking b
				join session s on s.id = b.session_id and s.menu_id >= $menu_id and s.session_start > '$thisMorning'
				join orders o on o.id = b.order_id and o.type_of_order = 'STANDARD' 
				where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0" );

		if ($order_retriever->N > 0)
		{
			require_once('processor/admin/order_mgr_processor.php');
			$proc = new processor_admin_order_mgr_processor();
		}

		$results = array();

		while($order_retriever->fetch())
		{
			$proc->prepareForDirectCall(false, $store_id, $user_id, $order_retriever->id);
			$result = $proc->ConvertOrderToMembership($membership_id, true);

			if ($result['processor_success'])
			{
				$results[$order_retriever->id] = 'success';
			}
			else
			{
				$results[$order_retriever->id] = $result['processor_message'];
			}
		}

		return $results;

	}

	function buildPaymentForm($Form, $User, $Store)
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
				$Form->DefaultValues['shipping_postal_code'] = $dAddr->postal_code;
				$Form->DefaultValues['shipping_address_note'] = $dAddr->address_note;
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
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Tel,
			CForm::name => "ccNumber",
			CForm::autocomplete => false,
			CForm::placeholder => "*Credit Card Number",
			CForm::required => true,
			CForm::required_msg => "Please enter credit card number.",
			CForm::css_class => 'input-credit-card no-tel'
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
			CForm::options => $cardOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccMonth",
			CForm::required => true,
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
			CForm::required_msg => "Please enter the street address of your billing address."
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "billing_postal_code",
			CForm::placeholder => "*Billing Zip Code",
			CForm::required => true,
			CForm::required_msg => "Please enter the zip code of the billing address."
		));

		// cash input
		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "payment_cash",
			CForm::placeholder => "*Cash amount",
			CForm::required => true,
			CForm::required_msg => "Please enter the cash amount."
		));

		// check input
		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "payment_check",
			CForm::placeholder => "*Check amount",
			CForm::required => true,
			CForm::required_msg => "Please enter the check amount."
		));

		// coupon input
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "discount_coupon",
			CForm::placeholder => "Coupon code",
			CForm::required => false,
			CForm::attribute => array (
				'data-discount_var' => 0,
				'data-discount_method' => 0,
				'data-user_id' => $User->id,
				'data-store_id' => $Store->id
			)
		));

		// payment number
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "payment_number",
			CForm::placeholder => "Payment number",
			CForm::required => false,
			CForm::required_msg => "Please enter the payment number."
		));

		// Shipping Address
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
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'shipping_state_id',
			CForm::required_msg => "Please select a state.",
			CForm::required => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "shipping_postal_code",
			CForm::required => true,
			CForm::placeholder => "*Postal Code",
			CForm::gpc_type => TYPE_POSTAL_CODE,
			CForm::xss_filter => true,
			CForm::maxlength => 5,
			CForm::required_msg => "Please enter a zip code.",
			CForm::length => 16
		));

		$userPhoneArray = array(
			'' => 'Contact Number',
			$User->telephone_1 => $User->telephone_1
		);

		if (!empty($User->telephone_2))
		{
			$userPhoneArray[$User->telephone_2] = $User->telephone_2;
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
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_security_code',
			CForm::autocomplete => false,
			CForm::length => 3,
			CForm::maxlength => 3,
			CForm::required => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "gift_card_ccMonth",
			CForm::required => true,
			CForm::options => array(
				'null' => 'month',
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

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "gift_card_ccYear",
			CForm::required => true,
			CForm::options => $yearOptions
		));
	}
}

?>