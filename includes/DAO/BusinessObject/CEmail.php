<?php
require_once('CMail.inc');

class CEmail extends CMail
{

	/*
	 * My Events email invitations
	 *
	 * email data attributes
	 *
	 * $invite_array = array(
			'message',
			'from_email',
			'from_name',
			'to_email',
			'to_name',
			'referral_link',
			'session' => $sessionDetails
		)
	 */
	static function sendInvitations($invite_array)
	{
		$contentsHtml = false;
		$contentsText = false;

		if ($invite_array['session']['session_type'] == CSession::DREAM_TASTE)
		{
			$contentsText = CMail::mailMergeIfElese('event_theme/invite/' . $invite_array['session']['dream_taste_theme_string'] . '/evite_dream_taste.txt.php', 'event_theme/invite/' . $invite_array['session']['dream_taste_theme_string_default'] . '/evite_dream_taste.txt.php', $invite_array);
			$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/' . $invite_array['session']['dream_taste_theme_string'] . '/evite_dream_taste.html.php', 'event_theme/invite/' . $invite_array['session']['dream_taste_theme_string_default'] . '/evite_dream_taste.html.php', $invite_array);
			$_email_subject = $invite_array['from_name'] . " invites you to a Meal Prep Workshop";
			$_email_template_name = 'evite_' . $invite_array['session']['dream_taste_theme_string_default'];
		}

		if ($invite_array['session']['session_type'] == CSession::FUNDRAISER)
		{
			$contentsText = CMail::mailMergeIfElese('event_theme/invite/' . $invite_array['session']['dream_taste_theme_string'] . '/evite_fundraiser_ten.txt.php', 'event_theme/invite/' . $invite_array['session']['dream_taste_theme_string_default'] . '/evite_fundraiser_ten.txt.php', $invite_array);
			$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/' . $invite_array['session']['dream_taste_theme_string'] . '/evite_fundraiser_ten.html.php', 'event_theme/invite/' . $invite_array['session']['dream_taste_theme_string_default'] . '/evite_fundraiser_ten.html.php', $invite_array);
			$_email_subject = "Join " . $invite_array['from_name'] . " at a Dream Dinners Fundraising Event";
			$_email_template_name = 'evite_' . $invite_array['session']['dream_taste_theme_string_default'];
		}

		if ($invite_array['session']['session_type_true'] == CSession::STANDARD)
		{
			$contentsText = CMail::mailMergeIfElese('event_theme/invite/standard/standard/standard/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_standard_session.txt.php', 'event_theme/invite/standard/standard/standard/default/evite_standard_session.txt.php', $invite_array);
			$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/standard/standard/standard/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_standard_session.html.php', 'event_theme/invite/standard/standard/standard/default/evite_standard_session.html.php', $invite_array);
			$_email_subject = "Join " . $invite_array['from_name'] . " at a Dream Dinners Session";
			$_email_template_name = 'evite_standard_session';
		}

		if ($invite_array['session']['session_type_true'] == CSession::PRIVATE_SESSION)
		{
			$contentsText = CMail::mailMergeIfElese('event_theme/invite/standard/private_party/standard/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_private_session.txt.php', 'event_theme/invite/standard/private_party/standard/default/evite_private_session.txt.php', $invite_array);
			$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/standard/private_party/standard/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_private_session.html.php', 'event_theme/invite/standard/private_party/standard/default/evite_private_session.html.php', $invite_array);
			$_email_subject = "Join " . $invite_array['from_name'] . " at a Private Party";
			$_email_template_name = 'evite_standard_private';
		}

		if ($invite_array['session']['session_type'] == CSession::MADE_FOR_YOU)
		{
			if ($invite_array['session']['session_type_subtype'] == CSession::DELIVERY || $invite_array['session']['session_type_subtype'] == CSession::DELIVERY_PRIVATE)
			{
				$contentsText = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/delivery/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.txt.php', 'event_theme/invite/standard/made_for_you/delivery/default/evite_made_for_you.txt.php', $invite_array);
				$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/delivery/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.html.php', 'event_theme/invite/standard/made_for_you/delivery/default/evite_made_for_you.html.php', $invite_array);
				$_email_subject = $invite_array['from_name'] . " invites you to try our Home Delivery";
				$_email_template_name = 'evite_made_for_you';
			}
			else if ($invite_array['session']['session_type_subtype'] == CSession::REMOTE_PICKUP)
			{
				$contentsText = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/remote_pickup/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.txt.php', 'event_theme/invite/standard/made_for_you/remote_pickup/default/evite_made_for_you.txt.php', $invite_array);
				$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/remote_pickup/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.html.php', 'event_theme/invite/standard/made_for_you/remote_pickup/default/evite_made_for_you.html.php', $invite_array);
				$_email_subject = $invite_array['from_name'] . " invites you to a Community Pick Up Event";
				$_email_template_name = 'evite_made_for_you';
			}
			else if ($invite_array['session']['session_type_subtype'] == CSession::REMOTE_PICKUP_PRIVATE)
			{
				$contentsText = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/remote_pickup_private/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.txt.php', 'event_theme/invite/standard/made_for_you/remote_pickup_private/default/evite_made_for_you.txt.php', $invite_array);
				$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/remote_pickup_private/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.html.php', 'event_theme/invite/standard/made_for_you/remote_pickup_private/default/evite_made_for_you.html.php', $invite_array);
				$_email_subject = $invite_array['from_name'] . " invites you to a Community Pick Up Event";
				$_email_template_name = 'evite_made_for_you';
			}
			else
			{
				$contentsText = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/standard/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.txt.php', 'event_theme/invite/standard/made_for_you/standard/default/evite_made_for_you.txt.php', $invite_array);
				$contentsHtml = CMail::mailMergeIfElese('event_theme/invite/standard/made_for_you/standard/' . CTemplate::dateTimeFormat($invite_array['session']['menu_name'], YEAR_UNDERSCORE_MONTH) . '/evite_made_for_you.html.php', 'event_theme/invite/standard/made_for_you/standard/default/evite_made_for_you.html.php', $invite_array);
				$_email_subject = $invite_array['from_name'] . " invites you to try our Pick Up service";
				$_email_template_name = 'evite_made_for_you';
			}
		}

		// couldn't find an exact match, load standard template
		if (!$contentsHtml && !$contentsText)
		{
			$contentsText = CMail::mailMerge('event_theme/invite/standard/standard/standard/default/evite_standard_session.txt.php', $invite_array);
			$contentsHtml = CMail::mailMerge('event_theme/invite/standard/standard/standard/default/evite_standard_session.html.php', $invite_array);
			$_email_subject = $invite_array['from_name'] . " invites you to join them at Dream Dinners";
			$_email_template_name = 'evite_standard_session';
		}

		$Mail = new CMail();

		$Mail->from_name = $invite_array['from_name'];
		$Mail->to_name = $invite_array['to_name'];
		$Mail->to_email = $invite_array['to_email'];
		$Mail->subject = $_email_subject;
		$Mail->body_html = $contentsHtml;
		$Mail->body_text = $contentsText;
		$Mail->template_name = $_email_template_name;

		$Mail->sendEmail();
	}

	/*
	 * Create session hostess notification
	*/
	static function sendHostessNotification($to_email, $to_name, $Session)
	{
		$sessionDetailArray = CSession::getSessionDetail($Session->id, false);

		$data['session_info'] = $sessionDetailArray;

		require_once('CMail.inc');

		if ($sessionDetailArray['session_type'] == CSession::DREAM_TASTE)
		{
			if ($sessionDetailArray['session_type_string'] == 'friends_night_out')
			{
				$contentsText = CMail::mailMerge('host_notify/host_notify_dream_taste_friends_night_out.txt.php', $data);
				$contentsHtml = CMail::mailMerge('host_notify/host_notify_dream_taste_friends_night_out.html.php', $data);
				$_email_subject = 'Your Dream Dinners Event is set up';
				$_email_template_name = 'host_notify_dream_taste_friends_night_out';
			}
			else
			{
				$contentsText = CMail::mailMerge('host_notify/host_notify_dream_taste.txt.php', $data);
				$contentsHtml = CMail::mailMerge('host_notify/host_notify_dream_taste.html.php', $data);
				$_email_subject = 'Your Dream Dinners Event is set up';
				$_email_template_name = 'host_notify_dream_taste';
			}
		}

		if ($sessionDetailArray['session_type'] == CSession::FUNDRAISER)
		{
			$contentsText = CMail::mailMerge('host_notify/host_notify_fundraiser.txt.php', $data);
			$contentsHtml = CMail::mailMerge('host_notify/host_notify_fundraiser.html.php', $data);
			$_email_subject = 'Your Dream Dinners Fundraiser is set up';
			$_email_template_name = 'host_notify_fundraiser';
		}

		if ($sessionDetailArray['session_type_true'] == CSession::PRIVATE_SESSION)
		{
			$contentsText = CMail::mailMerge('host_notify/host_notify_private_party.txt.php', $data);
			$contentsHtml = CMail::mailMerge('host_notify/host_notify_private_party.html.php', $data);
			$_email_subject = 'Your Private Party Session is set up';
			$_email_template_name = 'host_notify_private_party';
		}

		if ($sessionDetailArray['session_type_true'] == CSession::REMOTE_PICKUP_PRIVATE)
		{
			$contentsText = CMail::mailMerge('host_notify/host_notify_community_private.txt.php', $data);
			$contentsHtml = CMail::mailMerge('host_notify/host_notify_community_private.html.php', $data);
			$_email_subject = 'Your Community Pick Up Session is set up';
			$_email_template_name = 'host_notify_community_private';
		}

		if ($contentsHtml && $contentsText)
		{
			$Mail = new CMail();

			$Mail->from_name = $sessionDetailArray['store_name'];
			$Mail->from_email = $sessionDetailArray['email_address'];
			$Mail->to_name = $to_name;
			$Mail->to_email = $to_email;
			$Mail->subject = $_email_subject;
			$Mail->body_html = $contentsHtml;
			$Mail->body_text = $contentsText;
			$Mail->template_name = $_email_template_name;

			$Mail->sendEmail();
		}
	}

	static function alertStoreSpecialInstructions($orderInfo)
	{
		if (!empty($orderInfo['orderInfo']['order_user_notes']))
		{
			$Mail = new CMail();

			$Mail->from_name = $orderInfo['customer_name'];
			$Mail->from_email = $orderInfo['customer_primary_email'];
			$Mail->to_name = $orderInfo['storeInfo']['store_name'];
			$Mail->to_email = $orderInfo['storeInfo']['email_address'];
			$Mail->subject = 'Alert - Special Request';
			$Mail->body_html = CMail::mailMerge('order_special_request.html.php', $orderInfo);
			$Mail->body_text = CMail::mailMerge('order_special_request.txt.php', $orderInfo);
			$Mail->template_name = 'order_special_request';

			$Mail->sendEmail();
		}
	}

	static function alertStoreHomeDelivery($orderInfo)
	{
		if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && ($orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY || $orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY_PRIVATE))
		{
			$Mail = new CMail();

			$Mail->from_name = $orderInfo['customer_name'];
			$Mail->from_email = $orderInfo['customer_primary_email'];
			$Mail->to_name = $orderInfo['storeInfo']['store_name'];
			$Mail->to_email = $orderInfo['storeInfo']['email_address'];
			$Mail->subject = 'Alert - Home Delivery Order';
			$Mail->body_html = CMail::mailMerge('order_home_delivery_alert.html.php', $orderInfo);
			$Mail->body_text = CMail::mailMerge('order_home_delivery_alert.txt.php', $orderInfo);
			$Mail->template_name = 'order_home_delivery_alert';

			$Mail->sendEmail();
		}
	}

	static function alertStoreShippingOrder($orderInfo)
	{
		if (!empty($orderInfo['sessionInfo']['session_type']) && ($orderInfo['sessionInfo']['session_type'] == CSession::DELIVERED))
		{
			$Mail = new CMail();

			$Mail->from_name = $orderInfo['customer_name'];
			$Mail->from_email = $orderInfo['customer_primary_email'];
			$Mail->to_name = $orderInfo['storeInfo']['store_name'];
			$Mail->to_email = $orderInfo['storeInfo']['email_address'];
			$Mail->subject = 'New Shipping Order - ' . $orderInfo['orderInfo']['orderAddress']['firstname'] . ' ' . $orderInfo['orderInfo']['orderAddress']['lastname'];
			$Mail->body_html = CMail::mailMerge('order_shipping_alert.html.php', $orderInfo);
			$Mail->body_text = CMail::mailMerge('order_shipping_alert.txt.php', $orderInfo);
			$Mail->template_name = 'order_shipping_alert';

			$Mail->sendEmail();
		}
	}

	//TEMP FUNCTION TO ALERT THAT SHIFT_SET_GO Bundle was ordered
	static function alertStoreShiftSetGoOrdered($userObj, $orderObj)
	{
		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'LIVE')
		{

			$allowedStores = array(244);
			$Mail = new CMail();

			if (in_array($orderObj->store_id, $allowedStores))
			{

				if ($orderObj->hasItemByRecipeId(CBundle::shiftSetGoBundleRecipeIds(true)))
				{
					$storeObj = $orderObj->getStore();

					$sessionObj = $orderObj->getSession();
					$sessionDetails = CSession::getSessionDetail($sessionObj->id, false);
					$email_data = array(
						'guest_name' => $userObj->firstname . ' ' . $userObj->lastname,
						'guestObj' => $userObj,
						'orderObj' => $orderObj,
						'order_id' => $orderObj->id,
						'session_time' => CTemplate::dateTimeFormat($sessionObj->session_start, VERBOSE, $storeObj),
						'sessionObj' => $sessionObj,
						'sessionDetails' => $sessionDetails
					);

					$Mail->to_name = $storeObj->store_name;

					if (MAIL_TEST_MODE)
					{
						$Mail->to_email = 'ryan.snook@dreamdinners.com';
					}
					else
					{
						$Mail->to_email = $storeObj->email_address;
					}

					$Mail->from_email = 'do-not-reply@dreamdinners.com';
					$Mail->subject = 'Alert - ' . $userObj->firstname . ' ' . $userObj->lastname . ' placed an order ' . $orderObj->id . ' containing the ShiftSetGo Bundle.';
					$Mail->body_html = CMail::mailMerge('shift_set_go_order_alert.html.php', $email_data);
					$Mail->body_text = CMail::mailMerge('shift_set_go_order_alert.txt.php', $email_data);
					$Mail->template_name = 'shift_set_go_order_alert';

					$Mail->sendEmail();
				}
			}
		}
	}

	static function alertStoreInstructions($orderInfo)
	{
		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'LIVE')
		{
			self::alertStoreSpecialInstructions($orderInfo);
			self::alertStoreHomeDelivery($orderInfo);
			self::alertStoreShippingOrder($orderInfo);
			self::alertStoreCouponUsed($orderInfo);
			self::alertOrderCustomizations($orderInfo);
		}
		else if (defined('STORE_NOTIFICATION_ALERT_TEST_EMAIL'))
		{
			$orderInfo['storeInfo']['email_address'] = STORE_NOTIFICATION_ALERT_TEST_EMAIL;
			self::alertStoreSpecialInstructions($orderInfo);
			self::alertStoreHomeDelivery($orderInfo);
			self::alertStoreShippingOrder($orderInfo);
			self::alertStoreCouponUsed($orderInfo);
			self::alertOrderCustomizations($orderInfo);
		}
	}

	static public function alertOrderCustomizations($orderInfo)
	{

		if ($orderInfo['orderInfo']['opted_to_customize_recipes'] == 1)
		{
			$Mail = new CMail();

			$Mail->from_name = $orderInfo['customer_name'];
			$Mail->from_email = $orderInfo['customer_primary_email'];
			$Mail->to_name = $orderInfo['storeInfo']['store_name'];
			$Mail->to_email = $orderInfo['storeInfo']['email_address'];
			$Mail->subject = 'Alert - Order Place with Customizations Selected';
			$Mail->body_html = CMail::mailMerge('order_customization_request.html.php', $orderInfo);
			$Mail->body_text = CMail::mailMerge('order_customization_request.txt.php', $orderInfo);
			$Mail->template_name = 'order_customization_request';

			$Mail->sendEmail();
		}
	}

	static public function alertStoreCouponUsed($orderInfo)
	{
		if (!empty($orderInfo['orderInfo']['coupon_code_id']))
		{
			$couponInfo = CCouponCode::getCouponDetails($orderInfo['orderInfo']['coupon_code_id']);

			switch (strtoupper($couponInfo->coupon_code))
			{
				case 'JULYNEW':
					$sendAlert = true;
					break;
				case 'CYBERMONDAY':
					$sendAlert = true;
					break;
				case 'MEALGIFT2020':
					$sendAlert = true;
					break;
				default:
					$sendAlert = false;
			}

			if ($sendAlert)
			{
				$Mail = new CMail();

				$userObj = DAO_CFactory::create('user');
				$userObj->id = $orderInfo['bookingInfo']['user_id'];
				$userObj->find(true);

				$email_data = array(
					'user' => $userObj,
					'coupon_details' => $couponInfo,
					'order_details' => $orderInfo
				);

				$Mail->to_name = $userObj->firstname . ' ' . $userObj->lastname;
				$Mail->to_email = $userObj->primary_email;
				$Mail->to_id = $userObj->id;
				$Mail->from_email = $orderInfo['storeInfo']['email_address'];
				$Mail->subject = 'Alert - ' . $userObj->firstname . ' ' . $userObj->lastname . ' used coupon ' . $couponInfo->coupon_code;
				$Mail->body_html = CMail::mailMerge('order_coupon_used_alert.html.php', $email_data);
				$Mail->body_text = CMail::mailMerge('order_coupon_used_alert.txt.php', $email_data);
				$Mail->template_name = 'order_coupon_used_alert';

				$Mail->sendEmail();
			}
		}
	}

	static public function sendBundleConfirmationEmail($user, $order, $includeGiftCardMessage = false, $delayedTransaction = false)
	{
		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order-details';
		$orderInfo['customer_primary_email'] = $user->primary_email;
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);

		$orderInfo['show_GC_message'] = $includeGiftCardMessage;

		$DRState = CDreamRewardsHistory::getCurrentStateForUserShortForm($user);

		if ($DRState)
		{
			if (isset($user->dr_downgraded_order_count) && $user->dr_downgraded_order_count > 0)
			{
				if ($user->dr_downgraded_order_count > 1)
				{
					$dgText = "(VIP - 5% off next {$user->dr_downgraded_order_count} qualifying orders)";
				}
				else
				{
					$dgText = "(VIP - 5% off next qualifying order)";
				}

				$DRState['level'] = str_replace("(VIP)", $dgText, $DRState['level']);
			}
			$orderInfo['DRState'] = $DRState;
		}

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && ($orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY || $orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY_PRIVATE))
			{
				$contentsHtml = CMail::mailMerge('order_bundle_special_event_delivery.html.php', $orderInfo);
				$contentsText = CMail::mailMerge('order_bundle_special_event_delivery.txt.php', $orderInfo);
				$_email_template_name = 'order_bundle_special_event_delivery';
			}
			else if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && $orderInfo['sessionInfo']['session_type_subtype'] == CSession::REMOTE_PICKUP)
			{
				$contentsHtml = CMail::mailMerge('order_bundle_special_event_remote_pickup.html.php', $orderInfo);
				$contentsText = CMail::mailMerge('order_bundle_special_event_remote_pickup.txt.php', $orderInfo);
				$_email_template_name = 'order_bundle_special_event_remote_pickup';
			}
			else
			{
				$contentsHtml = CMail::mailMerge('order_bundle_special_event.html.php', $orderInfo);
				$contentsText = CMail::mailMerge('order_bundle_special_event.txt.php', $orderInfo);
				$_email_template_name = 'order_bundle_special_event';
			}
		}
		else
		{
			$contentsHtml = CMail::mailMerge('order_bundle.html.php', $orderInfo);
			$contentsText = CMail::mailMerge('order_bundle.txt.php', $orderInfo);
			$_email_template_name = 'order_bundle';
		}

		$Mail = new CMail();

		$Mail->from_email = $orderInfo['storeInfo']['email_address'];
		$Mail->to_name = $user->firstname . ' ' . $user->lastname;
		$Mail->to_email = $user->primary_email;
		$Mail->to_id = $user->id;
		$Mail->subject = 'Order Confirmation';
		$Mail->body_html = $contentsHtml;
		$Mail->body_text = $contentsText;
		$Mail->template_name = $_email_template_name;

		$Mail->sendEmail();

		self::alertStoreInstructions($orderInfo);
	}

	static function platePointsOnHoldSuspend($userObj)
	{
		$Mail = new CMail();

		$Mail->to_name = $userObj->firstname . ' ' . $userObj->lastname;
		$Mail->to_email = $userObj->primary_email;
		$Mail->to_id = $userObj->id;
		$Mail->subject = 'PLATEPOINTS On Hold';
		$Mail->body_html = CMail::mailMerge('platepoints/platepoints_onhold_suspend.html.php', $userObj);
		$Mail->body_text = CMail::mailMerge('platepoints/platepoints_onhold_suspend.txt.php', $userObj);
		$Mail->template_name = 'platepoints_onhold_suspend';

		$Mail->sendEmail();
	}

	static function platePointsOnHoldReactivate($userObj)
	{
		$Mail = new CMail();

		$Mail->to_name = $userObj->firstname . ' ' . $userObj->lastname;
		$Mail->to_email = $userObj->primary_email;
		$Mail->to_id = $userObj->id;
		$Mail->subject = 'PLATEPOINTS Reinstated';
		$Mail->body_html = CMail::mailMerge('platepoints/platepoints_onhold_reactivate.html.php', $userObj);
		$Mail->body_text = CMail::mailMerge('platepoints/platepoints_onhold_reactivate.txt.php', $userObj);
		$Mail->template_name = 'platepoints_onhold_reactivate';

		$Mail->sendEmail();
	}

	static function membershipOrderConfirmation($userObj, $productOrder)
	{
		$Mail = new CMail();

		$userObj->getMembershipStatus(false, true, $productOrder->id);

		$email_data = array(
			'user' => $userObj,
			'order_details' => $productOrder
		);

		$Mail->to_name = $userObj->firstname . ' ' . $userObj->lastname;
		$Mail->to_email = $userObj->primary_email;
		$Mail->to_id = $userObj->id;
		$Mail->from_email = $productOrder->storeObj->email_address;
		$Mail->subject = 'You are now a Meal Prep+ member';
		$Mail->body_html = CMail::mailMerge('membership/membership_order_confirmation.html.php', $email_data);
		$Mail->body_text = CMail::mailMerge('membership/membership_order_confirmation.txt.php', $email_data);
		$Mail->template_name = 'membership_order_confirmation';

		$Mail->sendEmail();
	}

	static function membershipCancelled($userObj)
	{
		$Mail = new CMail();

		$userObj->getMembershipStatus();

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $userObj->home_store_id;
		$storeObj->find(true);

		$email_data = array(
			'user' => $userObj,
			'store' => $storeObj
		);

		$Mail->to_name = $userObj->firstname . ' ' . $userObj->lastname;
		$Mail->to_email = $userObj->primary_email;
		$Mail->to_id = $userObj->id;
		$Mail->from_email = $storeObj->email_address;
		$Mail->subject = 'Your Meal Prep+ Membership is Canceled';
		$Mail->body_html = CMail::mailMerge('membership/membership_cancelled.html.php', $email_data);
		$Mail->body_text = CMail::mailMerge('membership/membership_cancelled.txt.php', $email_data);
		$Mail->template_name = 'membership_cancelled';

		$Mail->sendEmail();
	}

	static function membershipEnded($userObj)
	{
		$Mail = new CMail();

		$userObj->getMembershipStatus();

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $userObj->home_store_id;
		$storeObj->find(true);

		$email_data = array(
			'user' => $userObj,
			'store' => $storeObj
		);

		$Mail->to_name = $userObj->firstname . ' ' . $userObj->lastname;
		$Mail->to_email = $userObj->primary_email;
		$Mail->to_id = $userObj->id;
		$Mail->from_email = $storeObj->email_address;
		$Mail->subject = 'Your Meal Prep+ Membership is Over';
		$Mail->body_html = CMail::mailMerge('membership/membership_ended.html.php', $email_data);
		$Mail->body_text = CMail::mailMerge('membership/membership_ended.txt.php', $email_data);
		$Mail->template_name = 'membership_ended';

		$Mail->sendEmail();
	}

	//CCPA
	static function accountRequestData($userObj)
	{
		$userObj->getMembershipStatus();

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $userObj->home_store_id;
		$storeObj->find(true);

		$email_data = array(
			'user' => $userObj,
			'store' => $storeObj
		);

		// create support ticket
		$Mail = new CMail();
		$Mail->to_name = 'Support';
		$Mail->to_email = ADMIN_EMAIL;
		$Mail->subject = 'CCPA: Request for account data';
		$Mail->body_html = CMail::mailMerge('account/account_request_data_support.html.php', $email_data);
		$Mail->body_text = CMail::mailMerge('account/account_request_data_support.txt.php', $email_data);
		$Mail->template_name = 'account_request_data_support';
		$Mail->sendEmail();

		// send confirmation to customer
		$Mail = new CMail();
		$Mail->to_name = $userObj->firstname . ' ' . $userObj->lastname;
		$Mail->to_email = $userObj->primary_email;
		$Mail->to_id = $userObj->id;
		$Mail->subject = 'Request for account data';
		$Mail->body_html = CMail::mailMerge('account/account_request_data_customer.html.php', $email_data);
		$Mail->body_text = CMail::mailMerge('account/account_request_data_customer.txt.php', $email_data);
		$Mail->template_name = 'account_request_data_customer';
		$Mail->sendEmail();
	}

	//CCPA - full delete
	static function accountRequestDelete($userObj)
	{
		//		try{
		//			$userObj->getMembershipStatus();
		//		}catch (Exception $e){
		//
		//		}

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $userObj->home_store_id;
		$storeObj->find(true);

		$sql = "select distinct store.store_name, active from store, orders where
				store.id = orders.store_id
				and orders.user_id = " . $userObj->id;

		$storeWithOrders = DAO_CFactory::create("store");
		$storeWithOrders->query($sql);

		$osn = '';
		while ($storeWithOrders->fetch())
		{
			$isActive = $storeWithOrders->active == "1" ? 'true' : 'false';
			$osn .= $storeWithOrders->store_name . " (is active = " . $isActive . "),";
		}

		$email_data = array(
			'user' => $userObj,
			'store' => $storeObj,
			'order_store_names' => $osn
		);

		// create support ticket
		$Mail = new CMail();
		$Mail->to_name = 'Support';
		$Mail->to_email = ADMIN_EMAIL;
		$Mail->subject = 'Request for account delete';
		$Mail->body_html = CMail::mailMerge('account/account_deletion_request_support.html.php', $email_data);
		$Mail->body_text = CMail::mailMerge('account/account_deletion_request_support.txt.php', $email_data);
		$Mail->template_name = 'account_deletion_request_support';
		$Mail->sendEmail();
	}

	//Account Close from Fadmin - Records marked as is_deleted
	static function accountCloseRequestToStore($userObj)
	{
		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $userObj->home_store_id;
		$storeObj->find(true);

		$userObj->getUserPreferences();
		$currentSMSPhoneSetting = $userObj->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'];
		$currentSMSPhoneSetting = (empty($currentSMSPhoneSetting) || $currentSMSPhoneSetting == 'UNANSWERED') ? 'Not Provided' : $currentSMSPhoneSetting;

		$email_data = array(
			'user' => $userObj,
			'store' => $storeObj,
			'mobile' => $currentSMSPhoneSetting
		);

		$Mail = new CMail();
		$Mail->to_name = $storeObj->store_name;
		$Mail->to_email = $storeObj->email_address;
		$Mail->subject = 'Request for account to be closed';
		$Mail->body_html = CMail::mailMerge('account/account_close_request_store.html.php', $email_data);
		$Mail->body_text = CMail::mailMerge('account/account_close_request_store.txt.php', $email_data);
		$Mail->template_name = 'account_close_request_store';
		$Mail->sendEmail();
	}

	//CCPA
	static function accountRequestDeleteToStore($userObj)
	{
		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $userObj->home_store_id;
		$storeObj->find(true);

		if ($storeObj->N > 0)
		{

			$userObj->getUserPreferences();
			$currentSMSPhoneSetting = $userObj->preferences[CUser::TEXT_MESSAGE_TARGET_NUMBER]['value'];

			$currentSMSPhoneSetting = (empty($currentSMSPhoneSetting) || $currentSMSPhoneSetting == 'UNANSWERED') ? 'Not Provided' : $currentSMSPhoneSetting;
			$email_data = array(
				'user' => $userObj,
				'store' => $storeObj,
				'mobile' => $currentSMSPhoneSetting
			);

			// create support ticket
			$Mail = new CMail();
			$Mail->to_name = $storeObj->store_name;
			$Mail->to_email = $storeObj->email_address;
			$Mail->subject = 'Action Required: Guest Account Deletion Request';
			$Mail->body_html = CMail::mailMerge('account/account_deletion_request_store.html.php', $email_data);
			$Mail->body_text = CMail::mailMerge('account/account_deletion_request_store.txt.php', $email_data);
			$Mail->template_name = 'account_deletion_request_store';
			$Mail->sendEmail();
		}
	}

	static function sendDeliveredGiftEmail($orderObj, $isEdited = false)
	{
		$Mail = new CMail();

		$Mail->to_email = $orderObj->orderAddress->email_address;
		$Mail->subject = 'You have a gift from Dream Dinners on the way!';
		$Mail->body_html = CMail::mailMerge('shipping/shipping_order_confirmation_gift_recipient.html.php', array('orderObj' => $orderObj));
		$Mail->body_text = CMail::mailMerge('shipping/shipping_order_confirmation_gift_recipient.txt.php', array('orderObj' => $orderObj));
		$Mail->template_name = 'shipping_order_confirmation_gift_recipient';

		$Mail->sendEmail();
	}

	static function sendDeliveredShipmentTrackingEmail($DAO_orders)
	{
		$Mail = new CMail();
		$Mail->bcc_email = 'ryan.snook@dreamdinners.com,brandy.latta@dreamdinners.com';
		$Mail->to_email = $DAO_orders->DAO_user->primary_email;
		if ($DAO_orders->DAO_orders_address->isGift())
		{
			$Mail->subject = 'Your gift from Dream Dinners is on the way!';
		}
		else
		{
			$Mail->subject = 'Your order from Dream Dinners is on the way!';
		}
		$Mail->body_html = CMail::mailMerge('shipping/shipping_tracking_notification.html.php', array('DAO_orders' => $DAO_orders));
		$Mail->body_text = CMail::mailMerge('shipping/shipping_tracking_notification.txt.php', array('DAO_orders' => $DAO_orders));
		$Mail->template_name = 'shipping_tracking_notification';
		$Mail->sendEmail();

		if ($DAO_orders->DAO_orders_address->isGift() && !empty($DAO_orders->DAO_orders_address->email_address))
		{
			$Mail = new CMail();
			$Mail->to_email = $DAO_orders->DAO_orders_address->email_address;
			$Mail->subject = 'You have a gift from Dream Dinners on the way!';
			$Mail->body_html = CMail::mailMerge('shipping/shipping_tracking_notification_gift_recipient.html.php', array('DAO_orders' => $DAO_orders));
			$Mail->body_text = CMail::mailMerge('shipping/shipping_tracking_notification_gift_recipient.txt.php', array('DAO_orders' => $DAO_orders));
			$Mail->template_name = 'shipping_tracking_notification_gift_recipient';
			$Mail->sendEmail();
		}
	}
}
?>