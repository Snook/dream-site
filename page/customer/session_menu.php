<?php // locations.php
require_once('includes/DAO/BusinessObject/CStatesAndProvinces.php');
require_once('includes/miscMath.inc');
require_once('includes/CCart2.inc');
require_once('DAO/BusinessObject/CMenu.php');
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CSession.php');
require_once('DAO/BusinessObject/CBundle.php');
require_once('DAO/BusinessObject/COrderMinimum.php');

class page_session_menu extends CPage
{
	function runPublic()
	{
		$tpl = CApp::instance()->template();

		$this->runSessionMenuPage($tpl);
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$this->runSessionMenuPage($tpl);
	}

	function runSessionMenuPage($tpl)
	{
		CTemplate::noCache();

		$CartObj = CCart2::instance();

		$UserObj = CUser::getCurrentUser();

		// setup store object
		$DAO_store = $CartObj->getOrder()->getStore();

		// no store, see if one can be determined
		if (empty($DAO_store))
		{
			list($storeId, $methodStoreFound) = CUser::getCurrentUser()->getCurrentStoreViewed($CartObj->getOrder());

			if (!empty($storeId))
			{
				// check if the store is active
				$DAO_store = DAO_CFactory::create('store');
				$DAO_store->id = $storeId;
				$DAO_store->active = 1;

				if ($DAO_store->find())
				{
					$CartObj->storeChangeEvent($storeId);

					$CartObj->restoreContents();

					$DAO_store = $CartObj->getOrder()->getStore();
				}
			}

			// no store, send them to pick a store
			if (empty($DAO_store))
			{
				CApp::bounce('?page=locations');
			}
		}

		$result = $CartObj->cart_sanity_check('session_menu');
		// This function can clear the cart or specific fields - should this be silent?

		if ($result['status'] != 'all_good' && DEBUG)
		{
			$tpl->setDebugMsg($result . "<br />" . print_r($result['problem_list'], true));
		}

		$menu_view = 'session_menu';
		if (!empty($_GET['view']) && $_GET['view'] == 'freezer')
		{
			$menu_view = 'session_menu_freezer';
		}

		// $activeMenuArray = CMenu::getActiveMenuArray();
		// AS of July 29, 2019 allow guest to be invited to not yet active customer menu
		$activeMenuArray = CMenu::getCurrentAndFutureMenuArray();

		$navigationType = $CartObj->getNavigationType();

		if ($navigationType == CTemplate::DELIVERED)
		{
			CApp::bounce('?page=box_select');
		}

		// Get the customer calendar array, so we can check if there is any valid sessions for the menu that is in the cart
		$DAO_session_calendar = DAO_CFactory::create('session');
		$DAO_session_calendar->store_id = $DAO_store->id;
		$sessionCalendarArray = $DAO_session_calendar->getSessionArrayByMenu(array(
			'menu_id_array' => CMenu::getCurrentAndFutureMenuArray(),
			'exclude_walk_in' => true,
			'active_only' => true
		));

		$parsedSessionCalendarArray = array(
			'all' => CSession::parseSessionArrayByMenu($sessionCalendarArray),
			'no_closed_walkin' => CSession::parseSessionArrayByMenu($sessionCalendarArray, array(
				'filter_closed' => true,
				'filter_walkin' => true
			))
		);

		// setup menu id
		$cartMenuID = $CartObj->getMenuId();

		$oldestMenuWithOpenSession = min(array_keys($parsedSessionCalendarArray['no_closed_walkin']['menu']));

		// no menu in cart, set the menu to the oldest available menu
		//If oldest is not having available then set to oldest with available sessions
		if (empty($cartMenuID) || $oldestMenuWithOpenSession > $cartMenuID)
		{
			// if menu hasn't been set, choose the oldest menu in the $parsedSessionCalendarArray
			// this should be the earliest someone can attend that has available sessions
			//$oldestMenuWithOpenSession = min(array_keys($parsedSessionCalendarArray['no_closed_walkin']['menu']));

			// double check that the found menu is an active menu
			if (array_key_exists($oldestMenuWithOpenSession, $activeMenuArray))
			{
				$CartObj->addMenuId($oldestMenuWithOpenSession);

				$CartObj->restoreContents();

				$cartMenuID = $CartObj->getMenuId();
			}
			else
			{
				// shouldn't happen, but couldn't find a valid menu so send them to locations
				CApp::bounce('?page=locations');
			}
		}

		$cartSessionId = $CartObj->getSessionId();

		// start with assuming standard, override later with actual if set
		$actualSessionType = CSession::STANDARD;
		$orderType = COrders::STANDARD;

		if (!empty($cartSessionId))
		{
			$sessionArray = CSession::getSessionDetail($cartSessionId);

			$actualSessionType = $sessionArray['session_type'];
		}

		if ($CartObj->getOrder()->isMadeForYou())
		{
			if ($navigationType == CTemplate::INTRO)
			{
				$orderType = COrders::INTRO;
			}
			else
			{
				$orderType = COrders::MADE_FOR_YOU;
			}
		}
		else if ($CartObj->getOrder()->isDreamTaste())
		{
			if (empty($cartSessionId))
			{
				CApp::bounce('?page=session');
			}

			$orderType = COrders::DREAM_TASTE;
		}
		else if ($CartObj->getOrder()->isFundraiser())
		{
			if (empty($cartSessionId))
			{
				CApp::bounce('?page=session');
			}

			$orderType = COrders::FUNDRAISER;
		}
		else if ($CartObj->getOrder()->isNewIntroOffer())
		{
			$orderType = COrders::INTRO;
		}

		// Validate for the freezer menu
		if ($menu_view == 'session_menu_freezer')
		{
			if ($orderType != COrders::STANDARD && $orderType != COrders::MADE_FOR_YOU)
			{
				CApp::bounce("?page=checkout");
			}
			else
			{
				$minimum = COrderMinimum::fetchInstance(COrders::STANDARD, $CartObj->getStoreId(), $cartMenuID);
				$minimum->updateBasedOnUserStoreMenu($UserObj, $DAO_store->id, $cartMenuID);

				if (!$CartObj->getOrder()->hasStandardCoreServingMinimum($CartObj, $minimum))
				{
					CApp::bounce("?page=session_menu");
				}
			}
		}

		//Will be populated where applicable below, default is not to have a configured minimum
		$tpl->assign('order_minimum_json', '{}');

		//does store allow customization
		//does user have selections
		//is session type correct (pickup/delivery)
		//is session open for customization
		$sessionObj = $CartObj->getOrder()->getSessionObj();
		$isOpenForCustomization = false;
		if ($sessionObj)
		{
			$isOpenForCustomization = $sessionObj->isOpenForCustomization($DAO_store);
		}

		$tpl->assign('has_meal_customization_configured', ($DAO_store->supports_meal_customization && $isOpenForCustomization && $UserObj->hasMealCustomizationPreferencesSet() && $actualSessionType == CTemplate::SPECIAL_EVENT));

		$DAO_orders = $CartObj->getOrder();

		if ($orderType == COrders::INTRO)
		{
			$DAO_bundle = CBundle::getActiveBundleForMenu($cartMenuID, $DAO_store);
			$tpl->assign('DAO_bundle', $DAO_bundle);

			$cartMenuItems = $CartObj->getMenuItems($cartMenuID);

			if ($DAO_orders->bundle_id != $DAO_bundle->id)
			{
				$DAO_orders->clearItems();
				$cartMenuItems = array();
				// also need to store the cleared item array in the cart
				$CartObj->clearMenuItems();
			}

			$DAO_orders->bundle_id = $DAO_bundle->id;

			// Note: we could be converting to bundle on menu page so we must update the cart
			$CartObj->addBundleId($DAO_bundle->id);

			$tpl->assign('number_servings_required', $DAO_bundle->number_servings_required);

			if ($DAO_bundle)
			{
				$menuItemArray = CMenuItem::getFullMenuItemsAndMenuInfo($DAO_store, $cartMenuID, $cartMenuItems, $DAO_bundle->id);
			}
			else
			{
				$tpl->setErrorMsg("The Meal Prep Starter Pack is not currently available for the selected menu. Select a different menu or check back again soon.");
			}
		}
		else if ($orderType == COrders::DREAM_TASTE || $orderType == COrders::FUNDRAISER)
		{
			if ($cartSessionId)
			{
				if (strtotime($sessionArray['session_close_scheduling']) <= CTimezones::getAdjustedServerTime($DAO_store))
				{
					$tpl->setStatusMsg("We&rsquo;re sorry, the session that you were invited to is closed. The date may have passed or the session may have been closed due to extenuating circumstances. Here you may view other sessions at your friend&rsquo;s Dream Dinners location.");
					CApp::bounce('?page=locations');
				}

				if ($sessionArray['available_slots'] - $sessionArray['booked_standard_slots'] <= 0)
				{
					$tpl->setStatusMsg("We&rsquo;re sorry, the session that you were invited to is full. Here you may view other sessions at your friend&rsquo;s Dream Dinners location.");
					CApp::bounce('?page=locations');
				}

				if ($sessionArray['session_publish_state'] != CSession::PUBLISHED)
				{
					$tpl->setStatusMsg("We&rsquo;re sorry, the session that you were invited to is currently closed. If you feel you have received this message in error please contact the store at {$DAO_store->telephone_day} or <a href='mailto:{$DAO_store->email_address}'>{$DAO_store->email_address}</a>.");
					CApp::bounce('?page=locations');
				}

				$DAO_bundle = CBundle::getBundleInfo($sessionArray['bundle_id'], $cartMenuID, $DAO_store);
				$CartObj->addBundleId($DAO_bundle->id);
				$tpl->assign('DAO_bundle', $DAO_bundle);

				$menuItemArray = CMenuItem::getFullMenuItemsAndMenuInfo($DAO_store, $cartMenuID, $CartObj->getMenuItems($cartMenuID), $DAO_bundle->id);

				$has_rsvp = false;
				if (CUser::isLoggedIn() && $sessionArray['dream_taste_can_rsvp_only'])
				{
					$SessionRSVP = DAO_CFactory::create('session_rsvp');
					$SessionRSVP->user_id = $UserObj->id;
					$SessionRSVP->session_id = $sessionArray['id'];

					if ($SessionRSVP->find(true))
					{
						$has_rsvp = true;
					}
					else
					{
						// user is logged in but does not have an RSVP for the session, therefore check if any Orders or RSVPs exist and if so bounce and warn
						$canAccessRSVP = CUser::getCurrentUser()->isEligibleForSessionRSVP_DreamTaste(false,false, false, $sessionArray);

						if (!$canAccessRSVP)
						{
							$tpl->setErrorMsg("Sorry but only guests new to Dream Dinners can RSVP for this event. Please contact the store if you have questions.");
							CApp::bounce('?page=locations');
						}
					}
				}

				$tpl->assign('has_rsvp', $has_rsvp);
				$tpl->assign('number_servings_required', $DAO_bundle->number_servings_required);
				$tpl->assign('bundle_cost', $DAO_bundle->price);
			}
		}
		else // standard // made for you
		{
			if (empty($cartSessionId) && $navigationType == CTemplate::EVENT)
			{
				CApp::bounce('?page=session');
			}

			$menuItemArray = CMenuItem::getFullMenuItemsAndMenuInfo($DAO_store, $cartMenuID, $CartObj->getMenuItems($cartMenuID));

			if ($menu_view == "session_menu_freezer" && empty($menuItemArray['menuItemCategoryInfo']['num_visible'][2]) && empty($menuItemArray['menuItemCategoryInfo']['num_visible'][3]))
			{
				CApp::bounce('?page=checkout');
			}

			$DAO_bundle = CBundle::getActiveBundleForMenu($cartMenuID, $DAO_store);
			$tpl->assign('starter_bundle', $DAO_bundle);

			$minimum = COrderMinimum::fetchInstance(COrders::STANDARD, $DAO_store->id, $cartMenuID);

			$sizesAvailable = CMenuItem::availableSizes($menuItemArray['menuItemInfo']);

			$minimum->updateBasedOnUserStoreMenu($UserObj, $DAO_store->id, $cartMenuID);
			$hasFreezerItems = CMenuItem::hasFreezerInventory($DAO_store, $cartMenuID);
			$minimum->setHasFreezerInventory($hasFreezerItems);

			$tpl->assign('order_minimum', $minimum);
			$tpl->assign('minimum_type', $minimum->getMinimumType());
			$tpl->assign('standard_minimum_message', $this->composeStandardMinimumMessage($minimum, $sizesAvailable, $tpl, false));
			$tpl->assign('additional_ordering_message', $this->composeAdditionalOrderingMessage($minimum, $menu_view, $tpl));
			$tpl->assign('order_minimum_json', $minimum->toJson());

			if ($DAO_orders->reconcileOrderToCurrentInventory($menuItemArray['menuItemInfo'])) // TODO: must handle Intro and Taste types above
			{
				// items adjusted in order obj  - must update the cart as well
				$DAO_orders->rebuildCartItemsToMatchOrder($CartObj, $cartMenuID, $orderType);

				// Cart has changed so we need to rerun this
				$menuItemArray = CMenuItem::getFullMenuItemsAndMenuInfo($DAO_store, $cartMenuID, $CartObj->getMenuItems($cartMenuID));

				$DAO_orders->recalculate();
			}

			$tpl->assign('menu_view', $menu_view);
		}

		$bundleServingsNeeded = $DAO_orders->getBundleServingsNeededArray();

		if ($bundleServingsNeeded)
		{
			$bundleRequirementsString = "";

			foreach ($bundleServingsNeeded as $masterItem => $requiredNumItems)
			{
				$bundleRequirementsString .= $masterItem . ":" . $requiredNumItems . ", ";
			}

			$tpl->assign('bundleRequirementsString', substr($bundleRequirementsString, 0, strlen($bundleRequirementsString) - 2));
		}
		else
		{
			$tpl->assign('bundleRequirementsString', false);
		}

		$suportsLTD = false;
		if (!empty($DAO_store->supports_ltd_roundup))
		{
			$suportsLTD = ($DAO_store->supports_ltd_roundup && $orderType == COrders::STANDARD);
		}

		$DAO_orders->recalculate();
		$DAO_coupon_code = $DAO_orders->getCoupon();

		$couponDetails = false;

		if (!empty($DAO_coupon_code))
		{
			$couponDetails = new stdClass();
			$couponDetails->coupon_code_short_title = $DAO_coupon_code->coupon_code_short_title;
			$couponDetails->limit_to_mfy_fee = $DAO_coupon_code->limit_to_mfy_fee;
			$couponDetails->limit_to_delivery_fee = $DAO_coupon_code->limit_to_delivery_fee;
			$couponDetails->coupon_code_discount_total = $DAO_orders->coupon_code_discount_total;

			$couponDetails = json_encode($couponDetails);
		}

		$cartInfo = CUser::getCartIfExists();

		$customizationDetails = new stdClass();
		$customizationDetails->cost = $cartInfo['order_info']['subtotal_meal_customization_fee'];
		$customizationDetails = json_encode($customizationDetails);

		$tpl->assign('menu_view', $menu_view);
		$tpl->assign('suportsLTD', $suportsLTD);
		$tpl->assign('jsMenuItemInfo', json_encode($menuItemArray['menuItemInfoByMID']));
		$tpl->assign('coupon', $couponDetails);
		$tpl->assign('customerCalendarArray', $parsedSessionCalendarArray);
		$tpl->assign('menu_items', $menuItemArray['menuItemInfo']);
		$tpl->assign('cart_info', $cartInfo);
		$tpl->assign('menu_info', $menuItemArray['menuInfo']);
		$tpl->assign('menu_id', $cartMenuID);
		$tpl->assign('order_type', $orderType);
		$tpl->assign('sticky_nav_bottom_disable', true);
		$tpl->assign('allow_customizations', $DAO_orders->opted_to_customize_recipes);
		$tpl->assign('customization', $customizationDetails);
		$tpl->assign('initialCartSubtotal', ((!empty($DAO_bundle->price) && $CartObj->getOrder()->isBundleOrder()) ? $DAO_bundle->price : $DAO_orders->grand_total));

		if (!empty($sessionArray))
		{
			// 4/25/2019:  session may not exist yet
			$tpl->assign('session', $sessionArray);
		}
	}

	function composeAdditionalOrderingMessage($minimum, $menu_view, $tpl)
	{
		if (!$minimum->isMinimumApplicable())
		{
			if ($minimum->hasFreezerInventory())
			{
				if ($menu_view == 'session_menu_freezer')
				{
					return 'Select an item to continue.';
				}
				else
				{
					$tpl->assign('number_servings_required', 1);
				}
			}
		}

		if ($menu_view == 'session_menu_freezer')
		{
			$cartInfo = CUser::getCartIfExists();
			$msg = '';
			if (!empty($cartInfo['cart_info_array']) && !empty($cartInfo['cart_info_array']['dinners_total_count']))
			{
				$plural = $cartInfo['cart_info_array']['dinners_total_count'] > 1 ? 's' : '';
				$msg = 'You have dinners for <span class="total-meal-nights  font-weight-bold">' . $cartInfo['cart_info_array']['dinners_total_count'] . '</span> night' . $plural;
			}

			return $msg;
		}

		return '';
	}

	//formulate the message displayed to the customer indicating how
	//much they should order. If there is a user then check there order
	//history to see if they have a qualifying order and so can order
	//less than a standard minimum
	function composeStandardMinimumMessage($minimum, $sizesAvailable, $tpl)
	{

		//if qualifying exists then minimum is one, unless
		//there are freezer items available.
		if (!$minimum->isMinimumApplicable())
		{
			if ($minimum->hasFreezerInventory())
			{

				if (empty($tpl->menu_view) || $tpl->menu_view != 'session_menu_freezer')
				{
					return 'Select a meal to get started or click continue.';
				}
				else
				{
					return '';
				}
			}
			else
			{

				$tpl->assign('number_servings_required', 1);

				return 'Select at least 1 meal to continue.';
			}
		}

		$minimumMessage = '';
		if ($minimum->getMinimumType() == COrderMinimum::SERVING)
		{

			$small = null;
			$four = null;
			$medium = null;
			$large = null;
			$smallMessage = '';
			$mediumMessage = '';
			$fourMessage = '';
			$largeMessage = '';
			foreach ($sizesAvailable as $size)
			{
				switch ($size)
				{
					case CMenuItem::TWO:
						$small = floor($minimum->getMinimum() / 2);
						if ($minimum->getMinimum() % 2 > 0)
						{
							$small++;
						}
						$sPlural = $small > 1 ? 's' : '';
						$smallMessage = $small . ' small meal' . $sPlural . ', ';
						break;
					case CMenuItem::FOUR:
						$four = floor($minimum->getMinimum() / 4);
						if ($minimum->getMinimum() % 4 > 0)
						{
							$four++;
						}
						$fPlural = $four > 1 ? 's' : '';
						$fourMessage = $four . ' medium meal' . $fPlural . ', ';
						break;
					case CMenuItem::HALF:
						$medium = floor($minimum->getMinimum() / 3);
						if ($minimum->getMinimum() % 3 > 0)
						{
							$medium++;
						}
						$mPlural = $medium > 1 ? 's' : '';
						$mediumMessage = $medium . ' medium meal' . $mPlural . ', ';
						break;
					case CMenuItem::FULL:
						$large = floor($minimum->getMinimum() / 6);
						if ($minimum->getMinimum() % 6 > 0)
						{
							$large++;
						}
						$lPlural = $large > 1 ? 's' : '';
						$largeMessage = ' or ' . $large . ' large meal' . $lPlural;
						break;
				}
			}
			//need to know the minimum servings

			$messages = $smallMessage . $mediumMessage . $fourMessage . $largeMessage;

			$minimumMessage = 'Choose at least %s (or a combination) to continue.';

			$minimumMessage = sprintf($minimumMessage, $messages);
		}

		if ($minimum->getMinimumType() == COrderMinimum::ITEM)
		{
			$itemQuantity = $minimum->getMinimum();
			$minimumMessage = '<div class="font-weight-bold">Select at least <span class="remaining font-weight-bold">%s</span> meal<span class="plural font-weight-bold">%s</span> to continue.</div>';
			$minimumMessage = sprintf($minimumMessage, $itemQuantity, ($itemQuantity > 1 ? 's' : ''));
		}

		return $minimumMessage;
	}
}

?>