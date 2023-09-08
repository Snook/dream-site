<?php
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');

class page_order_details extends CPage
{

	function runPublic()
	{
		CApp::forceLogin();
	}

	function runCustomer()
	{
		self::runPage();
	}

	static function runPage()
	{
		$tpl = CApp::instance()->template();

		if (defined('OP_TRACING') && OP_TRACING)
		{
			CLog::Record("OP_TRACE: Entering order_thankyou.");
		}

		$order_id = null;

		if (isset($_REQUEST["order"]) && is_numeric($_REQUEST["order"]))
		{
			$order_id = $_REQUEST["order"];
		}

		if (!$order_id)
		{
			//throw new Exception('invalid order id');
			CApp::bounce('?page=my_meals&tab=nav-past_orders');
		}

		// TODO: this is quick way to figure out if this is a Delivered order - we may want to be more explicit
		$OrderTypeFinder = new DAO();
		$isDeliveredOrder = false;
		$OrderTypeFinder->query("select b.id from booking b join session s on b.session_id = s.id and s.session_type = '" . CSession::DELIVERED . "' where b.status = 'ACTIVE' and b.is_deleted = 0 and b.order_id = $order_id");
		if ($OrderTypeFinder->N > 0)
		{
			$isDeliveredOrder = true;
			$Order = new COrdersDelivered();
		}
		else
		{
			$Order = DAO_CFactory::create('orders');
		}

		$Order->id = $order_id;
		$found = $Order->find(true);
		if (!$found)
		{
			//throw new Exception('invalid order');
			$tpl->setErrorMsg('The requested order was not found.');
			CApp::bounce('?page=my_meals&tab=past_orders');
		}

		CGiftCard::addOrderDrivenGiftCardDetailsToTemplate($tpl, CUser::getCurrentUser()->id, $order_id, false);

		$gcOrders = array();
		foreach ($tpl->gift_card_purchase_array as $number => $gc_purchase)
		{
			$gcOrders[$gc_purchase['id']] = $gc_purchase['id'];
		}

		$tpl->assign('orders', implode('-', $gcOrders));

		$ViewingUser = CUser::getCurrentUser();

		if ($Order->user_id !== $ViewingUser->id && $ViewingUser->user_type == CUser::CUSTOMER)
		{
			//throw new Exception('trying to view order from another user');
			$tpl->setErrorMsg('The requested order was not found in your order history.');
			CApp::bounce('?page=my_meals&tab=past_orders');
		}

		$Form = new CForm('customer_order_details');
		$Form->Repost = true;
		$Form->Bootstrap = true;

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

		$formArray = $Form->render();
		$tpl->assign('form_plate_points', $formArray);

		// Get Remaining store credit balance
		$storeCreditBalance = 0;
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->user_id = $Order->user_id;
		$Store_Credit->store_id = $Order->store_id;
		$Store_Credit->is_redeemed = 0;
		$Store_Credit->is_deleted = 0;
		$Store_Credit->is_expired = 0;
		$Store_Credit->find();

		while ($Store_Credit->fetch())
		{
			$storeCreditBalance += $Store_Credit->amount;
		}

		if ($storeCreditBalance > 0)
		{
			$tpl->assign('storeCreditBalance', CTemplate::moneyFormat($storeCreditBalance));
		}

		$User = DAO_CFactory::create('user');
		$User->id = $Order->user_id;
		$User->find(true);

		$User->getUsersLTD_RoundupOrders();
		$User->getPlatePointsSummary();

		$OrderDetailsArray = COrders::buildOrderDetailArrays($User, $Order, null, true, false, false, $isDeliveredOrder);
		$FullyQualifiedOrderType = COrders::getFullyQualifiedOrderTypeFrom($OrderDetailsArray);
		if ($OrderDetailsArray['sessionInfo']['session_type'] == CSession::MADE_FOR_YOU || $OrderDetailsArray['sessionInfo']['session_type'] == CSession::TODD || $OrderDetailsArray['sessionInfo']['session_type'] == CSession::DREAM_TASTE)
		{
			$tpl->assign('can_invite', false);
		}
		else
		{
			$tpl->assign('can_invite', true);
		}

		$tpl->assign('sms_special_case', 'none');


		if (defined('ENABLE_SMS_PREFERENCE_ORDER_DETAILS') && ENABLE_SMS_PREFERENCE_ORDER_DETAILS == true)
		{
			$User->getUserPreferences();
			require_once("processor/account.php");
			$prefsProcessor = new processor_account();
			$results = $prefsProcessor->reconcileSMSOptinStatus($User);

			if ($results['status'] == 'pending')
			{
				$tpl->assign('sms_special_case', 'pending_second_step');
			}
		}
		$tpl->assign('user',$User);

		//Meal Customization
		$orderCustomization = OrdersCustomization::getInstance($Order);
		//these are the settings the store had at the time the order was placed
		$storeCustomizationSettings =  $orderCustomization->storeCustomizationSettingsToObj();
		$storeCustomizationSettings->initFromCurrentStoreCustomizationSettingsIfNull($Order->getStore());
		$str = $orderCustomization->mealCustomizationToStringSelectedOnly(',');
		$tpl->assign('store_allows_meal_customization',$storeCustomizationSettings->allowsMealCustomization());
		$tpl->assign('store_allows_preassembled_customization',$storeCustomizationSettings->allowsPreAssembledCustomization());
		$tpl->assign('meal_customization_string',$str);
		$tpl->assign('order_has_meal_customization',$orderCustomization->hasMealCustomizationPreferencesSet() && $Order->opted_to_customize_recipes);


		$cantRescheduleReason = "";
		$tpl->assign('can_reschedule', $Order->can_customer_reschedule($cantRescheduleReason, $OrderDetailsArray['storeInfo']['timezone_id'], $OrderDetailsArray['sessionInfo']['session_start'], $OrderDetailsArray['sessionInfo']['session_type'], $OrderDetailsArray['bookingStatus'], $OrderDetailsArray['sessionInfo']['session_type_subtype']));
		$tpl->assign('userInfo', $User);
		$tpl->assign('orderDetailsArray', $OrderDetailsArray);
		$tpl->assign('customerName', $OrderDetailsArray['customer_name']);
		$tpl->assign('orderInfo', $OrderDetailsArray['orderInfo']);
		$tpl->assign('menuInfo', $OrderDetailsArray['menuInfo']);
		$tpl->assign('paymentInfo', $OrderDetailsArray['paymentInfo']);
		$tpl->assign('sessionInfo', $OrderDetailsArray['sessionInfo']);
		$tpl->assign('storeInfo', $OrderDetailsArray['storeInfo']);
		$tpl->assign('customerActionString',  COrders::getCustomerActionStringFrom($FullyQualifiedOrderType));


		if (!empty($_GET['status']) && $_GET['status'] == 'ec')
		{
			$tpl->setStatusMsg('Your Delivered order has been updated.');
		}

		// staff viewing another user's order details, can not reschedule
		if ($Order->user_id !== $ViewingUser->id)
		{
			$tpl->assign('can_reschedule', false);
		}

		$Coupon = $Order->getCoupon();

		if ($Coupon)
		{
			$tpl->assign('couponCode', $Coupon->coupon_code);
			$tpl->assign('coupon_title', $Coupon->coupon_code_short_title);
		}
		else
		{
			$tpl->assign('couponCode', false);
		}

		$d = CUser::getCurrentUser()->membershipData;
		$showPlatePointsEnroll = false;
		if (!$User->isUserPreferred() && $User->platePointsData['status'] != 'active' &&
			!$User->platePointsData['userIsOnHold'] &&
			CStore::storeSupportsPlatePoints($OrderDetailsArray['storeInfo']) &&
			CBrowserSession::getValue('dd_thank_you') &&
			CUser::getCurrentUser()->membershipData['status'] != CUser::MEMBERSHIP_STATUS_CURRENT)
		{
			$showPlatePointsEnroll = true;
		}

		$shareMessage = array(
			'title' => '',
			'message' => ''
		);

		switch ($OrderDetailsArray['sessionInfo']['session_type'])
		{
			case CSession::STANDARD:
				$shareMessage = array(
					'title' => 'Dream Dinners',
					'message' => 'Join me at my Dream Dinners session on ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], VERBOSE_DATE_NO_YEAR) . ' at ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], TIME_ONLY) . '. Let’s meet up, chat and prep easy, delicious meals for our families.'
				);
				break;
			case CSession::MADE_FOR_YOU:
				if (!empty($OrderDetailsArray['sessionInfo']['session_type']) && $OrderDetailsArray['sessionInfo']['session_type_subtype'] == CSession::REMOTE_PICKUP)
				{
					$shareMessage = array(
						'title' => 'Community Pick Up Event',
						'message' => 'Join me at a Dream Dinners Community Pick Up on ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], VERBOSE_DATE_NO_YEAR) . ' at ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], TIME_ONLY) . '. Save time each month when you pick up easy, delicious prepped meals your family will love.'
					);
				}
				else
				{
					$shareMessage = array(
						'title' => 'Dream Dinners Pick Up',
						'message' => 'Join me when I pick up my Dream Dinners on ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], VERBOSE_DATE_NO_YEAR) . ' at ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], TIME_ONLY) . '. Save time each month when you pick up easy, delicious prepped meals your family will love.'
					);
				}
				break;
			case CSession::FUNDRAISER:
				$shareMessage = array(
					'title' => $OrderDetailsArray['sessionInfo']['dream_taste_theme_title_public'],
					'message' => 'Join us at our Dream Dinners ' . $OrderDetailsArray['sessionInfo']['dream_taste_theme_title_public'] . ' session on ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], VERBOSE_DATE_NO_YEAR) . ' at ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], TIME_ONLY) . '.  Bring home easy, delicious dinners for your family and give back to a great cause.'
				);
				break;
			case CSession::DREAM_TASTE:
				$shareMessage = array(
					'title' => $OrderDetailsArray['sessionInfo']['dream_taste_theme_title_public'],
					'message' => 'Join me at my ' . $OrderDetailsArray['sessionInfo']['dream_taste_theme_title_public'] . ' session on ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], VERBOSE_DATE_NO_YEAR) . ' at ' . CTemplate::dateTimeFormat($OrderDetailsArray['sessionInfo']['session_start'], TIME_ONLY) . 'to learn about Dream Dinners and take home a few delicious, prepped dinners to try.'
				);
				break;
		}

		$tpl->assign('share_message', $shareMessage);

		$tpl->assign('showPlatePointsEnroll', $showPlatePointsEnroll);
		$tpl->assign('confirmation', $Order->order_confirmation);
		$tpl->assign('order_id', $Order->id);
		$tpl->assign('order_total', $Order->grand_total);
		$tpl->assign('store_VR_code', (empty($OrderDetailsArray['storeInfo']['vertical_response_code']) ? false : $OrderDetailsArray['storeInfo']['vertical_response_code']));

		//logout user after order in storeview
		if (CApp::$isStoreView)
		{
			$session = CBrowserSession::instance()->ExpireSession();
		}
	}
}

?>