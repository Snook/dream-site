<?php
require_once('includes/CCart2.inc');
require_once('includes/DAO/BusinessObject/CSession.php');
require_once('processor/cart_session_processor.php');

class page_session extends CPage
{

	function runPublic()
	{
		$tpl = CApp::instance()->template();

		$this->runSessionPage($tpl);
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$this->runSessionPage($tpl);
	}

	function runSessionPage($tpl)
	{
		$CartObj = CCart2::instance();
		$OrderObj = $CartObj->getOrder();

		$referral_is_intro = false;

		// process invite by link with referral origination id
		// dreamdinners.com/invite/<origination_id>
		if (isset($_REQUEST['oid']))
		{
			$referral = DAO_CFactory::create('customer_referral');
			$referral->origination_uid = $_REQUEST['oid'];

			if ($referral->find(true))
			{
				if (empty($referral->referrer_session_id))
				{
					CLog::RecordNew('ERROR', 'A referral link was generated that points to a referral without a session: ' . $_REQUEST['oid'], "", "", true);
					$tpl->setErrorMsg('The referral is invalid.');
					CApp::bounce("main.php?page=session_menu");
				}
				CBrowserSession::setValueAndDuration('RSV2_Origination_code', $_REQUEST['oid'], 86400 * 7);
				CBrowserSession::setValueAndDuration('Inviting_user_id', $referral->referring_user_id, 86400 * 7);
				CBrowserSession::setValueAndDuration('RSV2_Share_source', 'full_referral', 86400 * 7);

				$referral_is_intro = $referral->referrers_order_is_sampler;

				$_REQUEST['sid'] = $referral->referrer_session_id;
				// setting this will trigger the validation and redirect logic below
			}
		}

		// process invite by link with session id - user id
		// dreamdinners.com/session/<session_id>-<user_id>
		if (!empty($_REQUEST['sid']) && is_numeric($_REQUEST['sid']) && !empty($_REQUEST['jid']) && !is_numeric($_REQUEST['jid']))
		{
			/// TODO: may need to throttle these requests as they represent a potential DOS attack

			$joining_session_id = $_REQUEST['sid'];
			$inviting_user_id = $_REQUEST['jid'];

			// TODO: ?? Use user and session to look for possible previous referrals?

			$newRefCode = CCustomerReferral::newSharedLinkedReferral($inviting_user_id, $joining_session_id);

			if ($newRefCode)
			{
				CBrowserSession::setValueAndDuration('RSV2_Origination_code', $newRefCode, 86400 * 7);
				CBrowserSession::setValueAndDuration('Inviting_user_id', $inviting_user_id, 86400 * 7);
				CBrowserSession::setValueAndDuration('RSV2_Share_source', 'session_referral', 86400 * 7);
			}

			$_REQUEST['sid'] = $joining_session_id;
		}

		$is_starter_pack_link = false;
		// process invite by link with session id only
		// dreamdinners.com/session/<session_id>
		if (!empty($_REQUEST['sid']) && is_numeric($_REQUEST['sid']))
		{
			$sid = $_REQUEST['sid'];

			$session = CSession::getSessionDetail($sid);

			if (!empty($_REQUEST['starter_pack']))
			{
				$referral_is_intro = true;
				$is_starter_pack_link = true;
			}

			if (!empty($session))
			{
				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->id = $session['store_id'];
				if (!$StoreObj->find(true))
				{
					$tpl->setStatusMsg("Store not found.");
					CApp::bounce('main.php?page=session_menu');
				}

				if (strtotime($session['session_close_scheduling']) <= CTimezones::getAdjustedServerTime($StoreObj))
				{
					//updated per brandy 3/7/23 -$tpl->setStatusMsg("We're sorry, the session that you were invited to is closed. The date may have passed or the session may have been closed due to extenuating circumstances. Here you may view other sessions at your friend's Dream Dinners location.");
					$tpl->setStatusMsg("We're sorry, but the event you were invited to is either sold out or closed. Please contact the store for more information!");
					CApp::bounce('main.php?page=session_menu');
				}

				if ($session['session_publish_state'] != CSession::PUBLISHED)
				{
					$tpl->setStatusMsg("We're sorry, the session that you were invited to is currently closed. If you feel you have received this message in error please contact the store at {$StoreObj->telephone_day} or <a href='mailto:{$StoreObj->email_address}'>{$StoreObj->email_address}</a>.");
					CApp::bounce('main.php?page=session_menu');
				}

				if ($session['session_type'] == CSession::STANDARD || $session['session_type'] == CSession::MADE_FOR_YOU)
				{
					if ($referral_is_intro)
					{

						if (CUser::isLoggedIn() && !CUser::getCurrentUser()->isNewBundleCustomer())
						{
							if (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS)
							{
								$tpl->setErrorMsg('This account has previous orders. This order is permitted on this test server but would be prohibited in production.');
							}
							else
							{
								$tpl->setErrorMsg('Our records indicate that you have previous orders with Dream Dinners. You must be new to Dream Dinners to be eligible for this offer. Please Select a different session type.');
								CApp::bounce('main.php?page=session_menu');
							}
						}

						if ($session['remaining_intro_slots'] < 1)
						{
							if ($is_starter_pack_link)
							{
								$tpl->setStatusMsg("We're sorry, the session that you were invited to is full. Please select a different session.");
								CApp::bounce('main.php?page=session');
							}
							else
							{
								$tpl->setStatusMsg("We're sorry, the session that you were invited to is full. Here you may view other sessions at your friend's Dream Dinners location.");
								CApp::bounce('main.php?page=session_menu');
							}
						}

						if (empty($session['session_password']))
						{
							$CartObj->addNavigationType(CTemplate::INTRO);
						}
						else
						{
							if ($session['session_type'] == CSession::MADE_FOR_YOU)
							{
								$CartObj->addNavigationType(CTemplate::INTRO);
							}
							else
							{
								$CartObj->addNavigationType(CTemplate::EVENT);
							}
						}

						$CartObj->addBundleId(CBundle::getBundleIDForMenuAndType($session['menu_id'], 'TV_OFFER'));
					}
					else
					{
						$CartObj->removeBundleId();

						if ($session['remaining_slots'] < 1)
						{
							$tpl->setStatusMsg("We're sorry, the session that you were invited to is full. Here you may view other sessions at your friend's Dream Dinners location.");
							CApp::bounce('main.php?page=session_menu');
						}

						if (!empty($session['session_password']))
						{
							// Navigation Path
							$CartObj->addNavigationType(CTemplate::EVENT);
						}
						else
						{
							if ($session['session_type'] == CSession::MADE_FOR_YOU)
							{
								if (!empty($session['session_type_subtype']) && $session['session_type_subtype'] == CSession::DELIVERY)
								{
									$CartObj->addNavigationType(CTemplate::DELIVERY);
								}
								else
								{
									$CartObj->addNavigationType(CTemplate::MADE_FOR_YOU);
								}
							}
							else
							{
								$CartObj->addNavigationType($session['session_type']);
							}
						}
					}
				}
				else
				{
					$CartObj->clearMenuItems(true,true);
					$CartObj->addNavigationType(CTemplate::EVENT);
					$CartObj->addBundleId($session['bundle_id']);

					if ($session['remaining_slots'] < 1)
					{
						$tpl->setStatusMsg("We're sorry, the session that you were invited to is full. Here you may view other sessions at your friend's Dream Dinners location.");
						CApp::bounce('main.php?page=session_menu');
					}
				}
			}
			else
			{
				$tpl->setStatusMsg("We're sorry, the session that you were invited to can not be found. Here you may view other sessions at your friend's Dream Dinners location.");
				CApp::bounce('main.php?page=session_menu');
			}

			$CartObj->addMenuId($session['menu_id']);

			if (isset($OrderObj) && $OrderObj->store_id != $session['store_id'])
			{
				$CartObj->storeChangeEvent($session['store_id']);
				$OrderObj = $CartObj->getOrder();
			}

			$CartObj->addSessionId($session['id']);

			if (empty($_GET['ref']) || $_GET['ref'] != 'store')
			{
				$CartObj->addDirectInvite($session['id']);
			}

			CApp::bounce('main.php?page=session_menu');
		}
		// end process invite by link

		// Begin reschedule processing
		$isInReschedulingMode = false;
		if (isset($_REQUEST['reschedule']) && is_numeric($_REQUEST['reschedule']))
		{
			if (!empty($_REQUEST['back']))
			{
				$backlink = $_REQUEST['back'];
			}
			else
			{
				$backlink = "main.php?page=my_account";
			}

			$order_id = $_REQUEST['reschedule'];
			$OrderObj = DAO_CFactory::create('orders');
			$OrderObj->id = $order_id;
			if (!$OrderObj->find(true))
			{
				throw new Exception("Order not found in Customer Reschedule.");
			}

			$bookingObj = DAO_CFactory::create('booking');
			$bookingObj->order_id = $order_id;
			$bookingObj->status = 'ACTIVE';
			if (!$bookingObj->find(true))
			{
				throw new Exception("Booking not found in Customer Reschedule.");
			}

			$current_session_id = $bookingObj->session_id;
			$sessionObj = DAO_CFactory::create('session');
			$sessionObj->id = $current_session_id;
			if (!$sessionObj->find(true))
			{
				throw new Exception("Session not found in Customer Reschedule.");
			}

			$store_id = $OrderObj->store_id;
			$storeObj = DAO_CFactory::create('store');
			$storeObj->id = $store_id;
			if (!$storeObj->find(true))
			{
				throw new Exception("Store not found in Customer Reschedule.");
			}

			$isInReschedulingMode = true;

			$cantRescheduleReason = "";
			$approval = $OrderObj->can_customer_reschedule($cantRescheduleReason, $storeObj->timezone_id, $sessionObj->session_start, $sessionObj->session_type, 'ACTIVE', $sessionObj->session_type_subtype);

			if ($approval !== true)
			{
				$tpl->setErrorMsg($cantRescheduleReason);
				CApp::bounce($backlink);
			}

			if (isset($_POST['target']) && is_numeric($_POST['target']))
			{

				// TODO:   validate the crap out of the target
				$TargetSession = DAO_CFactory::create('session');
				$TargetSession->id = $_POST["target"];
				if (!$TargetSession->find(true))
				{
					throw new Exception("Request for target session failed");
				}

				$canReschedule = true;
				$isPrivate = "'0'";

				if (!empty($TargetSession->session_password) && $TargetSession->session_type != CSession::MADE_FOR_YOU)
				{
					$isPrivate = "'1'";

					if (empty($_POST["new_session_password"]) || $_POST["new_session_password"] != $TargetSession->session_password)
					{
						$tpl->setErrorMsg('The password is incorrect.');
						$tpl->assign('initer', "setNewSessionDateTime({$TargetSession->id}, $isPrivate)");
						$canReschedule = false;
					}
				}

				if ($canReschedule)
				{

					$OrderObj->addSession($TargetSession);

					$result = $OrderObj->reschedule($sessionObj->id, false);

					if ($result === 'success')
					{
						// success
						$tpl->setStatusMsg('Your order has been rescheduled.');
						CApp::bounce("main.php?page=order_details&order=" . $OrderObj->id);
					}
					else if ($result === 'closed')
					{
						$tpl->setErrorMsg("The reschedule attempt failed because the target session is expired");
					}
					else if ($result === 'session full')
					{
						$tpl->setErrorMsg("The reschedule attempt failed because the last available slot was just filled.");
					}
					else
					{
						$tpl->setErrorMsg("The reschedule attempt failed for an unknown reason");
					}
				}
			}

			$menuInfo = CMenu::getMenuInfo($sessionObj->menu_id);
			$request_date = strtotime($menuInfo['menu_start']);

			$tempArr = array(
				'cart_info_array' => array('session_type' => $sessionObj->session_type),
				array('session_info' => array('id' => $current_session_id))
			);

			$sessionsArray = CSession::getMonthlySessionInfoArray($storeObj, $request_date, $sessionObj->menu_id, $tempArr, true, true, $sessionObj, false, false, true, true);
			$tpl->assign('menu_info', $menuInfo);
			$tpl->assign('sessions', $sessionsArray);
			$tpl->assign('isInReschedulingMode', $isInReschedulingMode);
			$tpl->assign('current_session_id', $current_session_id);
			$tpl->assign('current_order_id', $order_id);

			return;
		}

		$tpl->assign('isInReschedulingMode', $isInReschedulingMode);

		$OrderStore = $OrderObj->getStore();

		$store_id_in_cart = $CartObj->getStoreId();
		$menu_id_in_cart = $CartObj->getMenuId();
		$session_type_in_cart = $CartObj->getNavigationType();
		$menu_items_in_cart = $CartObj->getMenuItems();

		if (empty($store_id_in_cart))
		{
			$storeByCookie = false;

			if (CBrowserSession::getValue('last_viewed_store'))
			{
				$storeByCookie = CBrowserSession::getValue('last_viewed_store');

				if (is_numeric($storeByCookie))
				{
					$CartObj->storeChangeEvent($storeByCookie);
				}
			}

			if (!$storeByCookie)
			{
				// no store, send them to pick a store
				CApp::bounce('main.php?page=locations');
			}
		}
		else if (empty($session_type_in_cart))
		{
			// no session type, send them to pick a session type
			//CApp::bounce('main.php?page=session_menu');
			//dont bounce here...this is where they pick the session type
		}
		else if (empty($menu_id_in_cart))
		{
			// no menu chosen, send them to pick a menu
			CApp::bounce('main.php?page=session_menu');
		}

		/*
		else if ($session_type_in_cart != CTemplate::EVENT && empty($menu_items_in_cart))
		{
			// standard or intro order, send them to put items in their cart
			CApp::bounce('main.php?page=session_menu');
		}
        */

		$menuInfo = CMenu::getMenuInfo($menu_id_in_cart);
		$cart_info = CUser::getCartIfExists();
		$request_date = strtotime($menuInfo['menu_start']);

		$sessionsArray = CSession::getMonthlySessionInfoArray($OrderStore, $request_date, $menu_id_in_cart, $cart_info, true, true, false, false, false, false, true);

		$session_type_descs = CStore::getStoreSessionTypeDescriptions($OrderStore);

		// sessions before menu information
		$tpl->assign('has_meal_customization_sessions', $sessionsArray['info']['has_meal_customization_sessions']);
		$tpl->assign('session_type_descs', $session_type_descs);
		$tpl->assign('sticky_nav_bottom_disable', true);
		$tpl->assign('menu_info', $menuInfo);
		$tpl->assign('sessions', $sessionsArray);
		$tpl->assign('cart_info', $cart_info);
	}
}

?>