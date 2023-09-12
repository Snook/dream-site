<?php // menus.php
require_once('DAO/BusinessObject/COrders.php');
require_once('DAO/BusinessObject/CBundle.php');

//require_once('includes/COrderEditorCart.inc');
require_once('page/admin/order_details_view_all.php');
require_once("includes/CPageAdminOnly.inc");
require_once("DAO/BusinessObject/CStoreHistory.php");
require_once("DAO/BusinessObject/CDreamTasteEvent.php");
require_once("DAO/BusinessObject/CGiftCard.php");
require_once("DAO/BusinessObject/CFundraiser.php");
require_once('DAO/BusinessObject/CMenuItemInventoryHistory.php');
require_once("ValidationRules.inc");

class page_admin_order_mgr_test extends CPageAdminOnly
{

	const QUANTITY_PREFIX = 'qty_'; //entrees

	private $originalOrder = null;
	private $daoStore = null;
	private $User = null;
	private $Store_Credits_Total = 0;
	private $Store_Credit_Array = array();
	private $userIsPlatePointsGuest = false;
	private $storeSupportsPlatePoints = false;
	private $orderState = false;

	function runHomeOfficeStaff()
	{
		$this->runOrderManager();
	}

	function runSiteAdmin()
	{
		$this->runOrderManager();
	}

	function runFranchiseStaff()
	{
		$this->runOrderManager();
	}

	function runFranchiseLead()
	{
		$this->runOrderManager();
	}

	function runHomeOfficeManager()
	{
		$this->runOrderManager();
	}

	function runFranchiseOwner()
	{
		$this->runOrderManager();
	}

	function runFranchiseManager()
	{
		$this->runOrderManager();
	}

	function runEventCoordinator()
	{
		$this->runOrderManager();
	}

	function runOpsLead()
	{
		$this->runOrderManager();
	}

	function runOrderManager()
	{

		ini_set('memory_limit', '64M');
		set_time_limit(1800);
		$tpl = CApp::instance()->template();

		//force reload if Back button is pressed
		CTemplate::noCache();

		if (isset($_GET['session_full']) && $_GET['session_full'] == "true")
		{
			$tpl->setStatusMsg("Warning: Saved orders do not occupy a session slot however the session to which this order is saved is currently full.");
		}

		if (isset($_REQUEST['back']))
		{
			$tpl->assign('back', $_REQUEST['back']);
		}

		$allowLimitedAccess = false;

		if (empty($_REQUEST['order']) || !is_numeric($_REQUEST['order']))
		{
			// New orders must be provided a store id and a user id
			if (empty($_REQUEST['user']) || !is_numeric($_REQUEST['user']))
			{
				$tpl->setErrorMsg("There was a problem with the user ID specified.");
				CApp::bounce("/?page=admin_main");
			}

			$this->originalOrder = DAO_CFactory::create('orders');
			$this->orderState = 'NEW';
			$this->originalOrder->user_id = $_REQUEST['user'];
			$this->originalOrder->store_id = CBrowserSession::getCurrentFadminStoreID();
		}
		else
		{

			// Get the original order
			$this->originalOrder = DAO_CFactory::create('orders');
			$this->originalOrder->id = $_REQUEST['order'];
			if (!$this->originalOrder->find(true))
			{
				throw new Exception('Order not found');
			}

			$booking = DAO_CFactory::create('booking');
			$booking->query("SELECT id, status FROM booking WHERE order_id = " . $this->originalOrder->id . " AND (status = 'ACTIVE' OR status = 'SAVED') AND is_deleted = 0");

			if ($booking->N == 0)
			{

				// TODO: Check for Cancelled and allow payment and notes access
				$cancelledBooking = DAO_CFactory::create('booking');

				$cancelledBooking->query("SELECT id, status FROM booking WHERE order_id = " . $this->originalOrder->id . " AND status = 'CANCELLED' AND is_deleted = 0");
				if ($cancelledBooking->N > 0)
				{

					$cancelledBooking->fetch();
					$this->orderState = 'CANCELLED';// $cancelledBooking->status;
					$allowLimitedAccess = true;
				}
				else
				{
					$tpl->setErrorMsg("There was a problem with the order ID specified.");
					CApp::bounce("/?page=admin_main");
					// TODO: or we could leave them here with a NEW order
				}
			}
			else
			{
				CLog::Assert($booking->N == 1, "There should only be 1 ACTIVE or SAVED status per order");
				$booking->fetch();

				$this->orderState = $booking->status;
			}
		}

		$tpl->assign("store_id", $this->originalOrder->store_id);
		$tpl->assign("orderState", $this->orderState);

		if ($this->orderState == 'SAVED')
		{
			$tpl->assign('saved_booking_id', $booking->id);
		}
		else if ($this->orderState == 'NEW')
		{
			$tpl->assign('saved_booking_id', 'false');
		}
		else  // active, locked or cancelled
		{
			$tpl->assign('saved_booking_id', 'false');
		}

		$tpl->assign("user_id", $this->originalOrder->user_id);

		if ($this->orderState != 'NEW')
		{
			$tpl->assign('org_order_time', strtotime($this->originalOrder->timestamp_created));
		}
		else
		{
			$tpl->assign('org_order_time', 'new order');
		}

		// Is this order editable by the current user?
		if (!CStore::userHasAccessToStore($this->originalOrder->store_id))
		{
			$tpl->setErrorMsg('You do not have access privileges for this order.');

			if (isset($_REQUEST['back']))
			{
				CApp::bounce($_REQUEST['back']);
			}
			else
			{
				CApp::bounce('/?page=admin_main');
			}
		}

		$this->daoStore = $this->originalOrder->getStore();
		if (empty($this->daoStore))
		{
			throw new Exception('Store not found');
		}

		if (!CStore::userHasAccessToStore($this->daoStore->id))
		{
			CApp::bounce('/?page=admin_main');
		}

		if (CUser::getCurrentUser()->isFranchiseAccess() && $this->orderState != 'NEW')
		{
			$currentFadminStore = CBrowserSession::getCurrentFadminStore();
			if ($this->originalOrder->store_id != $currentFadminStore)
			{

				$storeName = $this->daoStore->store_name;
				$tpl->setErrorMsg("This order (#{$this->originalOrder->id}) was placed at a different store. Please change to the $storeName store to edit it.");
				CApp::bounce('/?page=admin_main');
			}
		}

		$Form = new CForm('order_mgr');
		$Form->Repost = true;

		$storeHasTransitioned = CStore::hasPlatePointsTransitionPeriodExpired($this->originalOrder->store_id);
		$tpl->assign('storeHasTransitioned', $storeHasTransitioned);

		if ($this->orderState != 'NEW')
		{
			// ask order to rebuild itself
			// TODO: reconstruct should one day also reconstruct any products from the original order.
			// as of 7/13/09 the only products are the dfl subscriptions. We will handle those specifically for now.
			$this->originalOrder->reconstruct();

			if ($this->orderState == 'CANCELLED')
			{
				$Session = $this->originalOrder->findSession(false, true);
			}
			else
			{
				$Session = $this->originalOrder->findSession(true);
			}

			if (!$Session)
			{
				throw new Exception('Session not found');
			}

			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::default_value => $Session->id,
				CForm::name => 'session'
			));
		}
		else
		{
			$Form->AddElement(array(
				CForm::type => CForm::Hidden,
				CForm::default_value => 'none',
				CForm::name => 'session'
			));
		}

		// original quantities to be shown in parentheses next to input boxes
		$orgQuantities = array();
		$items = $this->originalOrder->getItems();

		$tpl->assign('store_specific_deposit', $this->daoStore->default_delayed_payment_deposit);

		$isDreamTaste = false;
		$sessionIsInPast = false;

		if ($this->daoStore->supports_ltd_roundup)
		{
			$tpl->assign('ltd_roundup_show_input', false);
		}
		else
		{
			$tpl->assign('ltd_roundup_show_input', false);
		}

		if (!empty($this->daoStore->supports_fundraiser))
		{
			$Form->DefaultValues['fundraiser_id'] = $this->originalOrder->fundraiser_id;
			$Form->DefaultValues['fundraiser_value'] = ((!empty($this->originalOrder->fundraiser_value)) ? $this->originalOrder->fundraiser_value : '0.00');

			$fundraiserArray = CFundraiser::storeFundraiserArray($this->daoStore);

			$fundraiserOptions = array('' => 'Select Fundraiser');

			foreach ($fundraiserArray AS $fund_id => $fundraiser)
			{
				if (!empty($fundraiser->active) || $fund_id == $this->originalOrder->fundraiser_id)
				{
					$fundraiserOptions[$fund_id] = array(
						'title' => $fundraiser->fundraiser_name . (empty($fundraiser->active) ? ' (Inactive)' : ''),
						'data' => array(
							'data-description' => htmlentities($fundraiser->fundraiser_description),
							'data-value' => $fundraiser->donation_value,
							'data-active' => $fundraiser->active
						)
					);
				}
			}

			if (!empty($this->originalOrder->fundraiser_id))
			{
				$tpl->assign('fundraiser_description', $fundraiserArray[$this->originalOrder->fundraiser_id]->fundraiser_description);
			}
			else
			{
				$tpl->assign('fundraiser_description', false);
			}

			if (!empty($fundraiserOptions) && $this->daoStore->supports_fundraiser)
			{
				$tpl->assign('fundraiser_show_input', true);
			}
			else
			{
				$tpl->assign('fundraiser_show_input', false);
			}

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'fundraiser_id',
				CForm::attribute => array('data-org_value' => ((!empty($this->originalOrder->fundraiser_id)) ? $this->originalOrder->fundraiser_id : '')),
				CForm::options => $fundraiserOptions,
				CForm::style => 'width: 98%;'
			));

			$Form->addElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'fundraiser_value',
				CForm::attribute => array('data-org_value' => ((!empty($this->originalOrder->fundraiser_value)) ? $this->originalOrder->fundraiser_value : '0.00')),
				CForm::disabled => ((empty($this->originalOrder->fundraiser_id) ? true : false) || (!empty($this->originalOrder->fundraiser_id) && empty($fundraiserArray[$this->originalOrder->fundraiser_id]->active)))
			));
		}

		if ($this->orderState != 'NEW')
		{
			$sessionIsInPast = $Session->isInThePast($this->daoStore);
			$tpl->assign('session_id', $Session->id);

			if ($Session->session_type == CSession::DREAM_TASTE)
			{
				$isDreamTaste = true;
				$dreamTasteProperties = CDreamTasteEvent::sessionProperties($Session->id);
				if (empty($dreamTasteProperties->number_servings_required))
				{
					$dreamTasteProperties->number_servings_required = 9;
				}
				if (empty($dreamTasteProperties->price))
				{
					$dreamTasteProperties->price = 24.99;
				}

				$bundleItemsData = CBundle::getBundleMenuInfo($dreamTasteProperties->bundle_id, $Session->menu_id, $this->daoStore->id);
				$tpl->assign('tasteBundleMenuData', $bundleItemsData);
				$tpl->assign('dreamTasteProperties', $dreamTasteProperties);
			}
			$tpl->assign('isDreamTaste', $isDreamTaste);
		}
		else
		{
			$tpl->assign('isDreamTaste', false);
		}

		$orderHasBundle = false;
		$orderedBItems = array();

		/// ------------------------------------ Setup Bundle - for saved or active orders only
		if ($this->orderState != 'NEW' && $this->daoStore->supports_infomercial && !$isDreamTaste)
		{
			$activeBundle = CBundle::getActiveBundleForMenu($Session->menu_id, $this->daoStore);
			if ($activeBundle)
			{
				$tpl->assign('hasBundle', true);

				$bundleItems = CBundle::getBundleMenuInfo($activeBundle->id, $Session->menu_id, $this->daoStore->id);

				$tpl->assign('bundleInfo', $activeBundle->toArray());

				if (!empty($this->originalOrder->bundle_id))
				{
					$orderHasBundle = true;
				}

				$Form->AddElement(array(
					CForm::type => CForm::CheckBox,
					CForm::name => 'selectedBundle',
					CForm::onClick => 'bundleClick',
					CForm::org_value => ($orderHasBundle ? "1" : "0"),
					CForm::checked => $orderHasBundle,
					CForm::dd_required => false,
					CForm::disabled => false
				));

				$bundlePriceTypeArray = array();

				// brute force: retrieve order items directly and look for the bundle id
				$BOrderItem = DAO_CFactory::create('order_item');
				$BOrderItem->order_id = $this->originalOrder->id;
				$BOrderItem->find();

				while ($BOrderItem->fetch())
				{
					if (isset($BOrderItem->bundle_id) && $BOrderItem->bundle_id > 0)
					{
						$orderedBItems[$BOrderItem->menu_item_id] = 1;
					}
				}

				foreach ($bundleItems['bundle'] as $BIid => $bitem)
				{
					$checked = false;
					if (array_key_exists($BIid, $orderedBItems))
					{
						$bundleItems['bundle'][$BIid]['chosen'] = true;
						$checked = true;
					}
					else
					{
						$bundleItems['bundle'][$BIid]['chosen'] = false;
					}

					$bidname = 'bnd_' . $BIid;

					if (isset($_POST['submit_changes']) && !isset($_POST[$bidname]))
					{
						$checked = false;
					}

					$Form->AddElement(array(
						CForm::type => CForm::CheckBox,
						CForm::name => $bidname,
						CForm::onClick => 'bundleItemClick',
						CForm::checked => $checked,
						CForm::org_value => ($checked ? "1" : "0"),
						CForm::entreeID => $bitem['entree_id'],
						CForm::item_title => $bitem['display_title'],
						CForm::pricing_type => $bitem['pricing_type'],
						CForm::servings => $bitem['servings_per_item'],
						CForm::dd_required => false,
						CForm::disabled => true
					));

					$bundlePriceTypeArray[$bitem['entree_id']][$bitem['pricing_type']] = $BIid;
				}

				$tpl->assign('bundleItems', $bundleItems);

				$tpl->assign('bundlePriceTypeArray', $bundlePriceTypeArray);
			}
			else
			{
				$tpl->assign('hasBundle', false);
			}
		}
		else
		{
			$tpl->assign('hasBundle', false);
		}

		// remove bundle items - from here on they are considered children of the bundle object
		if ($orderHasBundle && !empty($items))
		{
			foreach ($items as $menu_item)
			{

				if (array_key_exists($menu_item[1]->id, $bundleItems['bundle']) && $bundleItems['bundle'][$menu_item[1]->id]['chosen'])
				{
					$items[$menu_item[1]->id][0]--;
					if ($items[$menu_item[1]->id][0] <= 0)
					{
						unset($items[$menu_item[1]->id]);
					}
				}
			}

			$tpl->assign("originallyHadBundle", true);
		}
		else
		{
			$tpl->assign("originallyHadBundle", false);
		}

		if (!empty($items))
		{
			foreach ($items as $menu_item)
			{

				$orgQuantities[$menu_item[1]->id] = $menu_item[0];
			}
		}

		$tpl->assign('CreditBasis', $this->originalOrder->grand_total - $this->originalOrder->subtotal_all_taxes);

		$orgPrices = array();

		// original prices to be used if menu > 78
		if ($this->orderState != 'NEW')
		{
			$orgPrices = $this->getOriginalPrices($this->originalOrder->id);
		}

		if (isset($_GET["force_new_pricing"]))
		{
			$orgPrices = array();
		}

		// cache store and user objects

		$session_is_in_past = false;

		$order_is_locked = false;
		/// check to see if editing period has expired
		if ($this->orderState == 'ACTIVE')
		{
			$now = CTimezones::getAdjustedServerTime($this->daoStore);

			$sessionTS = strtotime($Session->session_start);

			if ($now > $sessionTS)
			{
				$session_is_in_past = true;
			}

			// check for orders in previous month if current day is greater than 6
			$day = date("j", $now);
			$month = date("n", $now);
			$year = date("Y", $now);

			if ($day > 6)
			{
				$cutOff = mktime(0, 0, 0, $month, 1, $year);
				if ($sessionTS < $cutOff)
				{
					$order_is_locked = true;
					$allowLimitedAccess = true;
				}
			}
			else
			{
				$cutOff = mktime(0, 0, 0, $month - 1, 1, $year);
				if ($sessionTS < $cutOff)
				{
					$order_is_locked = true;
					$allowLimitedAccess = true;
				}
			}
		}

		if ($this->originalOrder->is_in_plate_points_program && !$this->originalOrder->points_are_actualized && $session_is_in_past)
		{
			$tpl->assign('order_is_complete_but_unconfirmed', true);
		}

		if (CUser::getCurrentUser()->id == 662598)
		{
			$order_is_locked = false;
			$allowLimitedAccess = false;
		}

		$tpl->assign('order_is_locked', $order_is_locked);

		$tpl->assign('allowLimitedAccess', $allowLimitedAccess);

		$this->User = DAO_CFactory::create('user');
		$this->User->id = $this->originalOrder->user_id;
		if (!$this->User->find(true)) // what if user was deleted?
		{
			throw new Exception('No user associated with this order');
		}

		$tpl->assign('userIsNewToBundle', $this->User->isNewBundleCustomer() || $orderHasBundle);
		$tpl->assign("user_email", $this->User->primary_email);

		$this->User->getUserPreferences();
		$this->User->userData = CUserData::getSFIDataForDisplayNew($this->User->id, array(
			1,
			2,
			10,
			15,
			16,
			17,
			18,
			19,
			20,
			21,
			22,
			23,
			24
		), $this->daoStore);
		$tpl->assign('user_obj', $this->User);

		$tpl->assign('is_partial_account', ($this->User->is_partial_account ? true : false));

		// get original markup if any exists
		$markup = null;
		if (!empty($this->originalOrder->markup_id))
		{
			$markup = DAO_CFactory::create('mark_up');
			$markup->id = $this->originalOrder->markup_id;
			// could have been deleted so remove from where clause
			if (!$markup->find_includeDeleted(true))
			{
				throw new Exception('markup not found when setting up order editor for order: ' . $this->originalOrder->id);
			}
		}
		else if (!empty($this->originalOrder->mark_up_multi_id))
		{
			$markup = DAO_CFactory::create('mark_up_multi');
			$markup->id = $this->originalOrder->mark_up_multi_id;
			// could have been deleted so remove from where clause
			if (!$markup->find_includeDeleted(true))
			{
				throw new Exception('markup not found when setting up order editor for order: ' . $this->originalOrder->id);
			}
		}
		else
		{
			// No markup attached to order -- however this is possible for an Observe only order which has a grand total  of 0
			// in this case no markup id was associated with the order and since no costs were incurred it is legit to use the current markup if it exists
			if ($this->originalOrder->grand_total === 0 || $this->originalOrder->grand_total === "0" || $this->originalOrder->grand_total === "0.00")
			{
				$markup = $this->daoStore->getMarkUpMultiObj($Session->menu_id);
				$this->originalOrder->mark_up_multi_id = $markup->id;
			}
		}

		$introPrice = 12.50;
		if (is_null($markup))
		{
			$tpl->assign('introPrice', 12.50);
		}
		else
		{
			$introPrice = $markup->sampler_item_price;
			$tpl->assign('introPrice', $markup->sampler_item_price);
		}

		// ---------------------------------------------Build Store Credits array and checkbox
		$Store_Credit = DAO_CFactory::create('store_credit');
		$Store_Credit->user_id = $this->originalOrder->user_id;
		$Store_Credit->store_id = $this->originalOrder->store_id;
		$Store_Credit->is_redeemed = 0;
		$Store_Credit->is_deleted = 0;
		$Store_Credit->is_expired = 0;

		$Store_Credit->find();

		while ($Store_Credit->fetch())
		{

			$this->Store_Credit_Array[$Store_Credit->id] = array(
				'Source' => $Store_Credit->credit_card_number,
				'Amount' => $Store_Credit->amount,
				'Date_Redeemed' => $Store_Credit->timestamp_created,
				'DAO_Obj' => clone($Store_Credit)
			);

			$this->Store_Credits_Total += $Store_Credit->amount;
		}

		$pendingCredits = CStoreCredit::getPendingReferralCreditPerUser($this->originalOrder->store_id, $this->originalOrder->user_id);
		$tpl->assign('pendingCredits', $pendingCredits);

		if (!empty($this->Store_Credit_Array) || !empty($pendingCredits))
		{
			$tpl->assign('Store_Credits', $this->Store_Credit_Array);
			$tpl->assign('total_store_credit', $this->Store_Credits_Total);

			$Form->addElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => "use_store_credits",
				CForm::onClick => "storeCreditClick",
				CForm::org_value => "0",
				CForm::checked => false
			));

			$Form->addElement(array(
				CForm::type => CForm::Text,
				CForm::name => "store_credits_amount",
				CForm::length => 8,
				CForm::disabled => true,
				CForm::onKeyUp => "storeCreditAmountChange",
				CForm::default_value => CTemplate::moneyFormat($this->Store_Credits_Total)
			));
		}

		// -------------------------------------Set up menu
		if ($this->orderState != 'NEW')
		{
			$menuInfo = COrders::buildOrderEditMenuPlanArrays($Session->menu_id, $markup, true, $this->daoStore);

			$ctsArray = CMenu::buildCTSArray($this->daoStore, $Session->menu_id);

			$this->removePromoFromDisplayItems($items, $orgQuantities);
			$this->removeCouponFreeMealFromDisplayItems($this->originalOrder, $items, $orgQuantities);

			$needs3servingIntroItems = false;

			//build array sorted by entree and type
			$planArray = array();

			$isTODD = false;
			if ($Session->session_type == CSession::TODD)
			{
				$isTODD = true;
				$TODDMenuArray = CMenu::getTODDMenuMenbuItemIDsForSession($Session->id);
				$tpl->assign('TODDMenuArray', $TODDMenuArray);
			}

			$tpl->assign('isTODD', $isTODD);

			$useCurrent = true;
			$pricing_method = 'USE_CURRENT';
			$full_price = 0;
			$half_price = 0;
			if (isset($Session) && $Session->session_type == 'TODD')
			{
				$todd_props = DAO_CFactory::create('session_properties');
				$todd_props->session_id = $Session->id;
				$todd_props->find(true);
				if ($todd_props->menu_pricing_method == 'FREE')
				{
					$useCurrent = false;
					$pricing_method = 'FREE';
				}
				else if ($todd_props->menu_pricing_method == 'OVERRIDE')
				{
					$useCurrent = false;
					$pricing_method = 'OVERRIDE';
					$full_price = $todd_props->FULL_PRICE;
					$half_price = $todd_props->HALF_PRICE;
				}
			}

			$EntreeToInventoryMap = array();
			$sideStationBundleInfo = array();

			$tabindex = 1;
			$half_tabindex = 0;
			$full_tabindex = 500;

			foreach ($menuInfo as $categoryName => $subArray)
			{

				if (is_array($subArray))
				{
					foreach ($subArray as $item => $menu_item)
					{

						if (is_numeric($item) && !($menu_item['is_chef_touched']))
						{

							$qtyName = self::QUANTITY_PREFIX . $menu_item['id'];

							$keyUpHandlerName = 'qtyUpdate';
							if ($isDreamTaste && in_array($menu_item['id'], explode(',', $dreamTasteProperties->menu_items)))
							{
								$qtyName = 'bnd_' . $menu_item['id'];
								$keyUpHandlerName = 'DTQtyUpdate';
							}

							if (!$Form->value($qtyName))
							{
								$Form->DefaultValues[$qtyName] = 0;
							}

							$remaining = ($menu_item['override_inventory'] - $menu_item['number_sold']);

							// if it doesn't exist create entreeID to inventory entry
							// set amount remaining to current level
							if (!array_key_exists($menu_item['entree_id'], $EntreeToInventoryMap))
							{
								$EntreeToInventoryMap[$menu_item['entree_id']] = array(
									'override_inventory' => $menu_item['override_inventory'],
									'org_remaining' => $remaining,
									'remaining' => $remaining
								);
							}

							if (isset($items[$menu_item['id']]))
							{
								$addRemaining = ($items[$menu_item['id']][0] * $menu_item['servings_per_item']);

								if (isset($items[$menu_item['id']][1]->bundleItemCount) && $items[$menu_item['id']][1]->bundleItemCount > 0)
								{
									$Form->DefaultValues[$qtyName] = $items[$menu_item['id']][0] - $items[$menu_item['id']][1]->bundleItemCount;
								}
								else
								{
									$Form->DefaultValues[$qtyName] = $items[$menu_item['id']][0];
								}

								$EntreeToInventoryMap[$menu_item['entree_id']]['org_remaining'] += $addRemaining;
								$EntreeToInventoryMap[$menu_item['entree_id']]['remaining'] += $addRemaining;
							}

							$disabled = false;
							if ($isDreamTaste && !in_array($menu_item['id'], explode(',', $dreamTasteProperties->menu_items)) && !($menu_item['is_chef_touched']))
							{
								$disabled = true;
							}

							if ($this->daoStore->serving_tabindex_vertical)
							{
								if ($menu_item['pricing_type'] == 'HALF')
								{
									$tabindex = ++$half_tabindex;
								}
								else
								{
									$tabindex = ++$full_tabindex;
								}
							}

							$itemPriceValue = $menu_item['price'];

							if (isset($orgPrices[$item]))
							{
								$itemPriceValue = CTemplate::MoneyFormat($orgPrices[$item]);
							}

							$Form->AddElement(array(
								CForm::type => CForm::Text,
								CForm::name => $qtyName,
								CForm::dd_required => false,
								CForm::length => 2,
								CForm::tabindex => $tabindex,
								CForm::org_value => (isset($items[$menu_item['id']]) ? $items[$menu_item['id']][0] : '0'),
								CForm::dd_type => 'std',
								CForm::attribute => array(
									'data-menu_item_id' => $menu_item['id'],
									'data-intro_item' => (!empty($bundleItems['bundle'][$menu_item['id']]) ? 'true' : 'false'),
									'data-menu_class' => $categoryName
								),
								CForm::disabled => $disabled,
								CForm::entreeID => $menu_item['entree_id'],
								CForm::servings => $menu_item['servings_per_item'],
								CForm::item_title => $menu_item['display_title'],
								CForm::pricing_type => $menu_item['pricing_type'],
								CForm::is_bundle => $menu_item['is_bundle'],
								CForm::price => $itemPriceValue,
								CForm::lastQty => $Form->value($qtyName),
								CForm::onItemQtyKeyUp => $keyUpHandlerName
							));

							if ($menu_item['is_bundle'])
							{
								$subItems = CBundle::getBundleMenuInfoForMenuItem($menu_item['id'], $Session->menu_id, $this->daoStore->id);
								$menuInfo[$categoryName][$menu_item['id']]['sub_items'] = array();

								$sideStationBundleInfo[$menu_item['id']] = $subItems;

								foreach ($subItems['bundle'] as $sid => $subItemInfo)
								{
									$SBQty = 0;
									$sbiQtyName = 'sbi_' . $sid;
									if (!$Form->value($sbiQtyName))
									{
										$Form->DefaultValues[$sbiQtyName] = 0;
									}

									if (isset($items[$sid]))
									{
										$SBQty = $items[$sid][1]->bundleItemCount;

										$Form->DefaultValues[$sbiQtyName] = $SBQty;
									}

									$Form->AddElement(array(
										CForm::type => CForm::Text,
										CForm::name => 'sbi_' . $sid,
										CForm::dd_required => false,
										//CForm::disabled => ($primaryType != $menu_item['pricing_type']?true:false),
										CForm::length => 2,
										CForm::entreeID => $subItemInfo['entree_id'],
										CForm::servings => $subItemInfo['servings_per_item'],
										CForm::lastQty => $SBQty,
										CForm::onItemQtyKeyUp => 'qtyUpdate'
									));

									$menuInfo[$categoryName][$menu_item['id']]['sub_items'][$sid] = $subItemInfo;
								}
							}

							$isTODDItem = false;
							if ($isTODD && array_key_exists($menu_item['id'], $TODDMenuArray))
							{
								$isTODDItem = true;
							}

							if (!$useCurrent && $isTODDItem)
							{
								if ($pricing_method == 'OVERRIDE')
								{
									$menuInfo[$categoryName][$menu_item['id']]['price'] = ($menu_item['pricing_type'] == 'HALF' ? $half_price : $full_price);
								}
								else // FREE
								{
									$menuInfo[$categoryName][$menu_item['id']]['price'] = 0;
								}
							}

							if (isset($orgPrices[$item]))
							{
								$menuInfo[$categoryName][$item]['price'] = CTemplate::MoneyFormat($orgPrices[$item]);
							}
							else if ($menu_item['pricing_type'] == CMenuItem::INTRO)
							{
								$menuInfo[$categoryName][$item]['price'] = CTemplate::MoneyFormat($introPrice);
							}

							//add quantity and price to menu info
							if (isset($menu_item['entree_id']))
							{
								$menuInfo[$categoryName][$item]['qty'] = $Form->value($qtyName);
								$entreeId = $menu_item['entree_id'];
								$planType = $menu_item['pricing_type'];
								if (!isset($planArray[$categoryName]) || !is_array($planArray[$categoryName]) || !array_key_exists($entreeId, $planArray[$categoryName]))
								{
									$planArray[$categoryName][$entreeId] = array();
								}

								$repress = false;

								if ($needs3servingIntroItems && $menuInfo[$categoryName][$item]['pricing_type'] == CMenuItem::INTRO && $menuInfo[$categoryName][$item]['servings_per_item'] != 3)
								{
									$repress = true;
								}

								if (!$repress)
								{
									$planArray[$categoryName][$entreeId][$planType] = $menu_item['id'];
								}
							}
						}
					}
				}
			}

			$tpl->assign('sideStationBundleInfo', $sideStationBundleInfo);

			$hasExistingAddons = false;

			$tpl->assign('hasExistingAddons', $hasExistingAddons);

			$matchedFtItems = page_admin_order_details_view_all::buildFinishingTouchSuggestions($Session->menu_id, $Session->store_id, $menuInfo);
			$tpl->assign('matched_FT_items', $matchedFtItems);

			$hasExistingCTSs = false;
			foreach ($ctsArray as $id => &$data)
			{
				$qtyName = self::QUANTITY_PREFIX . $id;

				if (!isset($orgQuantities[$id]))
				{
					$orgQuantities[$id] = 0;
				}

				$remaining = ($data['override_inventory'] - $data['number_sold']) + $orgQuantities[$id];

				if (!array_key_exists($data['entree_id'], $EntreeToInventoryMap))
				{
					$EntreeToInventoryMap[$data['entree_id']] = array(
						'override_inventory' => $data['override_inventory'],
						'org_remaining' => $remaining,
					);
				}

				if (isset($items[$id][1]->bundleItemCount) && $items[$id][1]->bundleItemCount > 0)
				{
					$Form->DefaultValues[$qtyName] = $orgQuantities[$id] - $items[$id][1]->bundleItemCount;
				}
				else
				{
					$Form->DefaultValues[$qtyName] = $orgQuantities[$id];
				}

				if (isset($orgPrices[$id]))
				{
					$data['price'] = CTemplate::MoneyFormat($orgPrices[$id]);
					$menuInfo['Chef Touched Selections'][$id]['price'] = $data['price'];
				}

				$hasExistingCTSs = true;
				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::name => $qtyName,
					CForm::dd_required => false,
					CForm::length => 2,
					CForm::org_value => (isset($orgQuantities[$id]) ? $orgQuantities[$id] : '0'),
					CForm::dd_type => 'cts',
					CForm::attribute => array(
						'data-intro_item' => (!empty($bundleItems['bundle'][$id]) ? 'true' : 'false'),
						'data-menu_category_id' => 9,
						'data-menu_class' => 'Sides & Sweets'
					),
					CForm::price => $data['price'],
					CForm::pricing_type => $data['pricing_type'],
					CForm::entreeID => $data['entree_id'],
					CForm::servings => $data['servings_per_item'],
					CForm::item_title => $data['display_title'],
					CForm::lastQty => $Form->value($qtyName),
					CForm::onItemQtyKeyUp => 'qtyUpdate'
				));

				//add quantity and price to menu info
				if (isset($data['entree_id']))
				{
					//$data['qty'] = (!empty($currentQty) ? $currentQty : 0);
					$data['qty'] = 0;
					//CES 11/12/14 : currentQty is never set. Must be harmless.
				}
			}

			$tpl->assign('FinishingTouchesArray', $ctsArray);
			$tpl->assign('hasExistingCTSs', $hasExistingCTSs);
			$tpl->assign('entreeToInventoryMap', $EntreeToInventoryMap);
		}

		// ------------------------ set up Discounts

		// ------Direct Discount
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::org_value => $this->originalOrder->direct_order_discount,
			CForm::onKeyUp => 'calculateTotal',
			CForm::default_value => $this->originalOrder->direct_order_discount,
			CForm::name => 'direct_order_discount',
			CForm::length => 16,
			CForm::number => true
		));

		// ------Coupon Discount
		$coupon = $this->originalOrder->getCoupon();
		if ($coupon)
		{
			$tpl->assign('couponVal', $this->originalOrder->coupon_code_discount_total);
			$tpl->assign('couponDiscountMethod', $coupon->discount_method);
			$tpl->assign('couponDiscountVar', $coupon->discount_var);
			$tpl->assign('couponlimitedToFT', ($coupon->limit_to_finishing_touch ? true : false));
			$tpl->assign('couponIsValidWithReferralCredit', ($coupon->valid_with_customer_referral_credit ? true : false));
			$tpl->assign('couponIsValidWithPlatePoints', ($coupon->valid_with_plate_points_credits ? true : false));

			$tpl->assign('couponFreeMenuItem', (!empty($this->originalOrder->coupon_free_menu_item) ? $this->originalOrder->coupon_free_menu_item : false));

			$Form->DefaultValues['coupon_code'] = $coupon->coupon_code;

			$UISideArray = $coupon->toArray();

			if ($coupon->discount_method == CCouponCode::FREE_MEAL && !empty($coupon->menu_item_id))
			{
				$tpl->assign('couponDiscountVar', $coupon->menu_item_id);

				$coupon_menu_item = DAO_CFactory::create('menu_item');
				$coupon_menu_item->id = $coupon->menu_item_id;
				$coupon_menu_item->selectAdd();
				$coupon_menu_item->selectAdd("menu_item_name, pricing_type");
				if ($coupon_menu_item->find(true))
				{
					$UISideArray['free_entree_title'] = $coupon_menu_item->menu_item_name;

					if (isset($coupon_menu_item->servings_per_item))
					{
						$UISideArray['free_entree_servings'] = $coupon_menu_item->servings_per_item;
					}
					else
					{
						$UISideArray['free_entree_servings'] = $coupon_menu_item->pricing_type == CMenuItem::FULL ? '6' : '3';
					}
				}
			}

			$tpl->assign('coupon', $UISideArray);
		}
		else
		{
			$tpl->assign('couponDiscountMethod', 'NONE');
			$tpl->assign('couponDiscountVar', 0);
			$tpl->assign('couponlimitedToFT', false);
			$tpl->assign('couponIsValidWithPlatePoints', true);
			$tpl->assign('couponFreeMenuItem', false);
		}

		// Coupon Code
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::size => 18,
			CForm::maxlength => 18,
			CForm::name => 'coupon_code'
		));

		// ---------------------------------------------- set up misc cost fields

		$Form->DefaultValues['misc_food_subtotal'] = $this->originalOrder->misc_food_subtotal;
		$Form->DefaultValues['misc_food_subtotal_desc'] = $this->originalOrder->misc_food_subtotal_desc;
		$Form->DefaultValues['misc_nonfood_subtotal'] = $this->originalOrder->misc_nonfood_subtotal;
		$Form->DefaultValues['misc_nonfood_subtotal_desc'] = $this->originalOrder->misc_nonfood_subtotal_desc;
		$Form->DefaultValues['subtotal_service_fee'] = $this->originalOrder->subtotal_service_fee;
		$Form->DefaultValues['service_fee_description'] = $this->originalOrder->service_fee_description;

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'misc_food_subtotal',
			CForm::dd_required => false,
			CForm::org_value => $this->originalOrder->misc_food_subtotal,
			CForm::dd_type => 'item_field',
			CForm::length => 7,
			CForm::number => true,
			CForm::onKeyUp => 'costInput'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'misc_nonfood_subtotal',
			CForm::dd_required => false,
			CForm::org_value => $this->originalOrder->misc_nonfood_subtotal,
			CForm::dd_type => 'item_field',
			CForm::length => 7,
			CForm::number => true,
			CForm::onKeyUp => 'costInput'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'misc_food_subtotal_desc',
			CForm::dd_required => false,
			CForm::org_value => CTemplate::cleanDisplayStringForJavascript($this->originalOrder->misc_food_subtotal_desc),
			CForm::dd_type => 'item_field',
			CForm::maxlength => 64,
			CForm::onKeyUp => 'updateChangeList',
			CForm::size => 60
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'misc_nonfood_subtotal_desc',
			CForm::dd_required => false,
			CForm::org_value => CTemplate::cleanDisplayStringForJavascript($this->originalOrder->misc_nonfood_subtotal_desc),
			CForm::dd_type => 'item_field',
			CForm::maxlength => 64,
			CForm::onKeyUp => 'updateChangeList',
			CForm::size => 60
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'service_fee_description',
			CForm::dd_required => false,
			CForm::org_value => CTemplate::cleanDisplayStringForJavascript($this->originalOrder->service_fee_description),
			CForm::dd_type => 'item_field',
			CForm::maxlength => 64,
			CForm::onKeyUp => 'updateChangeList',
			CForm::size => 60
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'subtotal_service_fee',
			CForm::dd_required => false,
			CForm::org_value => $this->originalOrder->subtotal_service_fee,
			CForm::dd_type => 'item_field',
			CForm::number => true,
			CForm::length => 7,
			CForm::onKeyUp => 'costInput'
		));

		// --------------------------------------- set up Taxes
		list($tax_id, $curfoodTax, $curNonFoodTax, $curServiceTax, $curEnrollmentTax, $curDeliveryTax, $curBagFeeTax) = $this->daoStore->getCurrentSalesTax();

		$taxRelation = 'equal';

		if ($this->originalOrder->sales_tax_id)
		{
			$origSalesTaxObj = DAO_CFactory::create('sales_tax');
			$origSalesTaxObj->id = $this->originalOrder->sales_tax_id;
			$origSalesTaxObj->find(true);
			$origFoodTax = $origSalesTaxObj->food_tax;
			$origNonFoodTax = $origSalesTaxObj->total_tax;
			$origServiceTax = $origSalesTaxObj->other1_tax;
			$origEnrollmentTax = $origSalesTaxObj->other2_tax;
			$origDeliveryTax = $origSalesTaxObj->other3_tax;

			if ($origServiceTax == $origFoodTax)
			{
				$taxRelation = 'equal';
			}

			if ($origServiceTax > $origFoodTax)
			{
				$taxRelation = 'svc_tax_greater';
			}
			else
			{
				$taxRelation = 'food_tax_greater';
			}
		}
		else
		{
			$origFoodTax = 0;
			$origNonFoodTax = 0;
			$origServiceTax = 0;
			$origEnrollmentTax = 0;
			$origDeliveryTax = 0;
		}

		if ($taxRelation == 'svc_tax_greater')
		{
			$this->originalOrder->pp_discount_mfy_fee_first = 1;
		}
		else
		{
			$this->originalOrder->pp_discount_mfy_fee_first = 0;
		}

		$tpl->assign('pp_discount_mfy_fee_first', $this->originalOrder->pp_discount_mfy_fee_first);
		$tpl->assign('origNonFoodTax', $origNonFoodTax);
		$tpl->assign('curNonFoodTax', $curNonFoodTax);
		$tpl->assign('origFoodTax', $origFoodTax);
		$tpl->assign('curFoodTax', $curfoodTax);
		$tpl->assign('origServiceTax', $origServiceTax);
		$tpl->assign('curServiceTax', $curServiceTax);
		$tpl->assign('origEnrollmentTax', $origEnrollmentTax);
		$tpl->assign('curEnrollmentTax', $curEnrollmentTax);
		$tpl->assign('origDeliveryTax', $origDeliveryTax);
		$tpl->assign('curDeliveryTax', $curDeliveryTax);

		// -------------------------- preferred user discount
		list ($activeUP, $originalUP) = self::buildUserPreferredArray($this->originalOrder, $this->User, $this->daoStore->id);

		$tpl->assign('activeUP', $activeUP);
		$tpl->assign('originalUP', $originalUP);

		if ($this->orderState != 'NEW')
		{
			// -------------------------- Session discount
			list ($activeSD, $originalSD) = self::buildSessionDiscountArray($this->originalOrder, $Session);

			if ($this->orderState == 'SAVED')
			{
				// if the order is still saved always force the use of the active discount
				if (!$originalSD && !empty($activeSD))
				{
					$originalSD = $activeSD;
					$activeSD = false;
				}
			}

			$tpl->assign('activeSD', $activeSD);
			$tpl->assign('originalSD', $originalSD);
		}
		else
		{
			$tpl->assign('activeSD', false);
			$tpl->assign('originalSD', false);
		}

		$this->storeSupportsPlatePoints = CStore::storeSupportsPlatePoints($this->daoStore->id);

		if ($this->storeSupportsPlatePoints)
		{
			$UserPlatePointsSummary = $this->User->getPlatePointsSummary();
			$tpl->assign('plate_points', $UserPlatePointsSummary);
			$this->userIsPlatePointsGuest = ($UserPlatePointsSummary['status'] == 'active');
		}

		$tpl->assign('userIsPlatePointsGuest', $this->userIsPlatePointsGuest);
		$tpl->assign('storeSupportsPlatePoints', $this->storeSupportsPlatePoints);

		$maxAvailablePPCredit = 0;
		if ($this->userIsPlatePointsGuest && $this->storeSupportsPlatePoints && $this->originalOrder->is_in_plate_points_program)
		{
			$tpl->assign('isPlatePointsOrder', true);

			$this->originalOrder->storeAndUserSupportPlatePoints = true;

			$maxAvailablePPCredit = CPointsCredits::getAvailableCreditForUserAndOrder($this->originalOrder->user_id, $this->originalOrder->id);
			$maxDeduction = 0;

			$tpl->assign('maxPPCredit', $maxAvailablePPCredit);
			$tpl->assign('maxPPDeduction', $maxDeduction);

			if ($maxAvailablePPCredit > 0 && empty($activeUP) && empty($originalUP))
			{

				$Form->DefaultValues['plate_points_discount'] = $this->originalOrder->points_discount_total;

				$Form->AddElement(array(
					CForm::type => CForm::Text,
					CForm::name => 'plate_points_discount',
					CForm::org_value => $this->originalOrder->points_discount_total,
					CForm::number => true,
					CForm::length => 16,
					CForm::onKeyUp => 'handlePlatePointsDiscount',
					CForm::autocomplete => false
				));
			}
			else
			{
				if ($maxAvailablePPCredit <= 0)
				{
					$tpl->assign('noPPReason', "The guest does not currently have any Dinner Dollars.");
				}
				else if (!empty($activeUP) || !empty($originalUP))
				{
					$tpl->assign('noPPReason', "The guest is a preferred user and so cannot use Dinners Dollars.");
				}
				else
				{
					$tpl->assign('noPPReason', "Dinners Dollars are not available.");
				}
			}
		}
		else
		{
			$tpl->assign('isPlatePointsOrder', false);
		}

		// ------------------------------------ Payments
		if ($this->orderState != 'NEW')
		{
			$refTransArray = $this->getReferenceTransactionArray();

			// This refers to referencing payments made against this order , not the new global reference payment type
			$addNewRefType = true;
			if (isset($refTransArray[0]) && $refTransArray[0] == 'No Transactions available')
			{
				$addNewRefType = false;
			}

			$paymentArr = COrders::buildPaymentInfoArray($this->originalOrder->id, CUser::getCurrentUser()->user_type, $Session->session_start);

			$this->assignRefundsToPayments($paymentArr, $refTransArray);

			$tpl->assign('refTransArray', $refTransArray);

			$PendingDPOriginalCardNumber = false;
			$hasDelayedPayment = false;
			$canAdjustDelayedPayment = false;
			// look for a pending delayed payment

			if (!empty($paymentArr))
			{
				foreach ($paymentArr as $arrItem)
				{
					if (is_array($arrItem))
					{
						if (isset($arrItem['is_delayed_payment']))
						{
							$tpl->assign("PendingDP", $arrItem['id']);
							$tpl->assign("PendingDPAmount", $arrItem['total']['other']);
							$tpl->assign("PendingDPOriginalTransActionID", $arrItem['payment_transaction_number']['other']);
							$tpl->assign("PendingDPOriginalStatus", $arrItem['delayed_payment_status']);
							$PendingDPOriginalCardNumber = $arrItem['payment_number']['other'];
							$hasDelayedPayment = true;
							if ($arrItem['delayed_payment_status'] == 'PENDING')
							{
								$canAdjustDelayedPayment = true;
							}
							if ($arrItem['delayed_payment_status'] == 'SUCCESS')
							{
								$hasDelayedPayment = false;
							}
						}
					}
				}
			}
			$refPaymentTypeData = false;
			$refPaymentTypeDataArray = CPayment::getPaymentEligibleForReference($this->originalOrder->user_id, $this->daoStore->id);

			// Always allow this payment type, it's especially useful when the merchant account has changed.
			//	if (!$addNewRefType)
			//	{
			$refPaymentTypeData = $refPaymentTypeDataArray;
			//	}

			if ($refPaymentTypeData)
			{
				$tpl->assign('refPaymentTypeData', $refPaymentTypeData);
			}

			// Create forms to change delayed payment options if not SUCCESS
			if (isset($tpl->PendingDPOriginalStatus) && $tpl->PendingDPOriginalStatus != CPayment::SUCCESS)
			{
				// Create form to point delayed payment to another transaction
				$RefPayentTypeCounter = 0;

				$toTransactionOptions = array();

				if ($refPaymentTypeDataArray)
				{
					foreach ($refPaymentTypeDataArray as $thisRef)
					{
						if ($PendingDPOriginalCardNumber == 'XXXXXXXXXXXX' . $thisRef['cc_number'])
						{
							$toTransactionOptions[$tpl->PendingDPOriginalTransActionID] = $thisRef['card_type'] . " " . $thisRef['cc_number'];
						}
						else
						{
							$toTransactionOptions[$thisRef['payment_id']] = $thisRef['card_type'] . " " . $thisRef['cc_number'];
						}
						$RefPayentTypeCounter++;
					}
				}

				if ($RefPayentTypeCounter > 1)
				{
					$Form->DefaultValues['point_to_transaction'] = $tpl->PendingDPOriginalTransActionID;
					$Form->AddElement(array(
						CForm::type => CForm::DropDown,
						CForm::name => 'point_to_transaction',
						CForm::options => $toTransactionOptions,
						CForm::onChange => 'pointToTransaction'
					));
				}

				$setDelayedPaymentStatusTo = array();
				// Create form to change status of payment
				if ($tpl->PendingDPOriginalStatus == CPayment::FAIL)
				{
					$setDelayedPaymentStatusTo[CPayment::FAIL] = ucfirst(strtolower(CPayment::FAIL));
				}
				$setDelayedPaymentStatusTo[CPayment::PENDING] = ucfirst(strtolower(CPayment::PENDING));
				$setDelayedPaymentStatusTo[CPayment::CANCELLED] = ucfirst(strtolower(CPayment::CANCELLED));

				$Form->DefaultValues['change_delayed_payment_status'] = $tpl->PendingDPOriginalStatus;
				$Form->AddElement(array(
					CForm::type => CForm::DropDown,
					CForm::name => 'change_delayed_payment_status',
					CForm::options => $setDelayedPaymentStatusTo,
					CForm::onChange => 'setDelayedPaymentStatusTo'
				));
			}

			$tpl->assign("canAdjustDelayedPayment", $canAdjustDelayedPayment);
			$tpl->assign("hasDelayedPayment", $hasDelayedPayment);

			// TODO: make this dynamic with a setting in store_details
			$forceManualAutoAdjust = false;
			if ($this->daoStore->id == 34 || $this->daoStore->id == 159)
			{
				$forceManualAutoAdjust = true;
			}
			$tpl->assign("forceManualAutoAdjust", $forceManualAutoAdjust);

			$addrObj = DAO_CFactory::create('address');
			$addrObj->user_id = $this->originalOrder->user_id;

			$addr = "";
			$zip = "";
			if ($addrObj->find(true))
			{
				$addr = $addrObj->address_line1;
				$zip = $addrObj->postal_code;
			}

			$Form->DefaultValues['billing_postal_code_1'] = $zip;
			$Form->DefaultValues['billing_address_1'] = $addr;
			$Form->DefaultValues['payment1_ccNameOnCard'] = $this->User->getName();
			$Form->DefaultValues['billing_postal_code_2'] = $zip;
			$Form->DefaultValues['billing_address_2'] = $addr;
			$Form->DefaultValues['payment2_ccNameOnCard'] = $this->User->getName();

			self::buildPaymentForm($tpl, $Form, 2, $this->daoStore, $this->orderState, $Session, $addNewRefType, $hasDelayedPayment, $refPaymentTypeData);

			$tpl->assign('store_specific_deposit', $this->daoStore->default_delayed_payment_deposit);

			if ($this->orderState != 'NEW')
			{
				$StoreCreditPaymentTotal = $this->getCreditableStoreCreditPaymentTotal();
				$GiftCardPaymentTotal = $this->getCreditableGiftCardPaymentTotal();
			}
			else
			{
				$StoreCreditPaymentTotal = 0;
				$GiftCardPaymentTotal = 0;
			}

			if ($StoreCreditPaymentTotal)
			{
				$tpl->assign('storeCreditPaymentTotal', $StoreCreditPaymentTotal);
			}

			if ($GiftCardPaymentTotal)
			{
				$tpl->assign('debitGiftCardPaymentTotal', $GiftCardPaymentTotal);
			}
		}

		// ------------------------- Process Edits
		if (isset($_POST['submit_changes']) && $_POST['submit_changes'] == "true")
		{

			CLog::RecordDebugTrace('order_mgr submission handling called for order: ' . $this->originalOrder->id, "TR_TRACING");

			if (!$Form->validate_CSRF_token())
			{
				$tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support. This message can also be caused by a double submission of the same page.");
				goto finish_processing;
			}

			$originalGrandTotal = $this->originalOrder->grand_total;

			if ($allowLimitedAccess)
			{

				$this->processStoreCredits();

				if (isset($_POST['payment1_type']) && $_POST['payment1_type'] != "")
				{
					$this->processPayment1($paymentArr);
				}

				if (isset($_POST['payment2_type']) && $_POST['payment2_type'] != "")
				{
					$this->processPayment2();
				}

				// funds are being returned to the customer
				$this->processCredits();

				COrdersDigest::recordEditedOrder($this->originalOrder, $originalGrandTotal, $this->orderState == CBooking::CANCELLED);

				if (!isset($_POST['suppressEmail']))
				{
					COrders::sendEditedOrderConfirmationEmail($this->User, $this->originalOrder);
				}

				$tpl->assign('back', "/?page=admin_order_mgr_thankyou&order=" . $this->originalOrder->id);

				CApp::bounce("/?page=admin_order_mgr_thankyou&order=" . $this->originalOrder->id);
			}
			else
			{
				// record original version in edited orders log
				$order_record = DAO_CFactory::create('edited_orders');
				$order_record->setFrom($this->originalOrder->toArray());
				$order_record->original_order_id = $this->originalOrder->id;

				$order_record->order_revisions = $_POST['changeList'];
				$order_record->order_revision_notes = $_POST['changeListStr'];

				//Sales Tax will be recalculated at the current sales tax rate.
				// This could be expanded to allow the fadmin to choose the sales tax: current or original
				$tax = $this->daoStore->getCurrentSalesTaxObj();
				$this->originalOrder->sales_tax_id = $tax == null ? 'null' : $tax->id;

				// Notes
				//$this->originalOrder->order_user_notes = strip_tags($_POST['order_user_notes']);
				// CES 2-9-15 ... special instructions are no longer posted but use ajax

				//Direct Order Discount
				$this->originalOrder->direct_order_discount = $_POST['direct_order_discount'];

				// fundraiser
				if (!empty($_POST['fundraiser_id']) && !empty($_POST['fundraiser_value']))
				{
					$this->originalOrder->fundraiser_id = $_POST['fundraiser_id'];
					$this->originalOrder->fundraiser_value = number_format($_POST['fundraiser_value'], 2);
				}
				else if (empty($_POST['fundraiser_id']))
				{
					$this->originalOrder->fundraiser_id = null;
					$this->originalOrder->fundraiser_value = null;
				}

				// Coupon
				$this->originalOrder->coupon_code_id = $_POST['coupon_id'];

				$coupon_free_menu_item = $_POST['free_menu_item_coupon'];

				if (!empty($coupon_free_menu_item) && is_numeric($coupon_free_menu_item))
				{
					$this->originalOrder->coupon_free_menu_item = $coupon_free_menu_item;
				}

				if ($_POST['coupon_type'] == 'BONUS_CREDIT')
				{
					$this->originalOrder->coupon_code_discount_total = COrders::std_round($_POST['couponValue']);
					$tpl->assign('couponVal', $this->originalOrder->coupon_code_discount_total);
				}

				if ($this->originalOrder->coupon_code_id === '0' || $this->originalOrder->coupon_code_id === 0 || $this->originalOrder->coupon_code_id === "")
				{
					$this->originalOrder->coupon_code_id = 'null';
					$this->originalOrder->coupon_code_discount_total = 0;
					$tpl->assign('couponDiscountMethod', 'NONE');
					$tpl->assign('couponDiscountVar', 0);
					$tpl->assign('couponlimitedToFT', false);

					$this->originalOrder->addCoupon(null);
					$tpl->assign('couponVal', '');
				}

				// Markup
				// We leave markup alone for now, The markup stored with the original order is used for now.

				// Preferred User
				if (isset($_POST['PUD']))
				{
					switch ($_POST['PUD'])
					{
						case "originalUP":
							// nothing to do
							break;
						case "activeUP":
							$UP = DAO_CFactory::create('user_preferred');
							$UP->user_id = $this->originalOrder->user_id;
							$UP->findActive($this->originalOrder->store_id); // filter by store?
							$UP->fetch();
							$this->originalOrder->user_preferred_id = $UP->id;
							break;
						default:
							$this->originalOrder->user_preferred_id = 'null';
					}
				}
				else
				{
					$this->originalOrder->user_preferred_id = 'null';
				}

				// Session Discount
				$suppressSessionDiscount = false;
				if (isset($_POST['SessDisc']))
				{
					switch ($_POST['SessDisc'])
					{
						case "originalSD":
							// nothing to do
							break;
						case "activeSD":
							$this->originalOrder->session_discount_id = $Session->session_discount_id;
							break;
						default:
							$this->originalOrder->session_discount_id = 'null';
							$suppressSessionDiscount = true;
					}
				}
				else
				{
					$this->originalOrder->session_discount_id = 'null';
					$suppressSessionDiscount = true;
				}

				// -------------------------------------------------------------------- process misc Subtotals
				if (isset($_POST['misc_food_subtotal']))
				{

					if (empty($_POST['misc_food_subtotal']) || !is_numeric($_POST['misc_food_subtotal']))
					{
						$this->originalOrder->misc_food_subtotal = 0;
						$this->originalOrder->misc_food_subtotal_desc = "";
					}
					else
					{
						$this->originalOrder->misc_food_subtotal = $_POST['misc_food_subtotal'];
						$this->originalOrder->misc_food_subtotal_desc = $_POST['misc_food_subtotal_desc'];
					}
				}

				if (isset($_POST['misc_nonfood_subtotal']))
				{
					if (empty($_POST['misc_nonfood_subtotal']) || !is_numeric($_POST['misc_nonfood_subtotal']))
					{
						$this->originalOrder->misc_nonfood_subtotal = 0;
						$this->originalOrder->misc_nonfood_subtotal_desc = "";
					}
					else
					{
						$this->originalOrder->misc_nonfood_subtotal = $_POST['misc_nonfood_subtotal'];
						$this->originalOrder->misc_nonfood_subtotal_desc = $_POST['misc_nonfood_subtotal_desc'];
					}
				}

				if (isset($_POST['subtotal_service_fee']))
				{
					if (empty($_POST['subtotal_service_fee']) || !is_numeric($_POST['subtotal_service_fee']))
					{
						$this->originalOrder->subtotal_service_fee = 0;
						$this->originalOrder->service_fee_description = "";
					}
					else
					{
						$this->originalOrder->subtotal_service_fee = $_POST['subtotal_service_fee'];
						$this->originalOrder->service_fee_description = $_POST['service_fee_description'];
					}
				}

				// remember state so that we can restore some critical values if an exception occurs
				$originalOrderPriorToUpdate = clone($this->originalOrder);

				// Ready to commit everything
				$this->originalOrder->query('START TRANSACTION;');

				try
				{

					// PlatePoints Discount
					if (isset($_POST['plate_points_discount']))
					{

						if (empty($_POST['plate_points_discount']))
						{
							$_POST['plate_points_discount'] = 0;
						}

						if ($_POST['plate_points_discount'] > $maxAvailablePPCredit)
						{
							$_POST['plate_points_discount'] = $maxAvailablePPCredit;
						}

						if ($_POST['plate_points_discount'] < 0)
						{
							$_POST['plate_points_discount'] = 0;
						}

						$pp_credit_adjust_summary = CPointsCredits::AdjustPointsForOrderEdit($this->originalOrder, $_POST['plate_points_discount']);

						$this->originalOrder->points_discount_total = $_POST['plate_points_discount'];
					}
					else
					{
						$pp_credit_adjust_summary = CPointsCredits::AdjustPointsForOrderEdit($this->originalOrder, 0);
						$this->originalOrder->points_discount_total = 0;
					}

					self::cleanUpForeignKeys($order_record);

					if ($order_record->promo_code_id === '0' || $order_record->promo_code_id === 0)
					{
						$order_record->promo_code_id = null;
					}

					$order_record->insert();

					$tempStoreID = $this->originalOrder->store_id;

					// delete original order items after moving them to edited_order_items
					$order_item = DAO_CFactory::create('order_item');
					$order_item->query("SELECT oi.*, mi.servings_per_item, mi.is_chef_touched, mi.recipe_id FROM order_item oi " . " JOIN menu_item mi ON mi.id = oi.menu_item_id WHERE oi.order_id = " . $this->originalOrder->id . " AND oi.is_deleted = 0");
					while ($order_item->fetch())
					{
						$edited_item = DAO_CFactory::create('edited_order_item');
						$edited_item->setFrom($order_item->toArray());
						$edited_item->insert();

						try
						{
							$servingQty = $order_item->servings_per_item;

							if ($servingQty == 0)
							{
								$servingQty = 6;
							}

							$servingQty *= $order_item->item_count;
							//subtract from inventory
							$invItem = DAO_CFactory::create('menu_item_inventory');

							if ($order_item->is_chef_touched)
							{

								if ($sessionIsInPast)
								{
									$invItem->query("update menu_item_inventory mii set mii.override_inventory = mii.override_inventory + $servingQty
    										where mii.recipe_id = {$order_item->recipe_id} and mii.store_id = $tempStoreID and mii.menu_id is null and mii.is_deleted = 0");

									CMenuItemInventoryHistory::RecordEvent($tempStoreID, $order_item->recipe_id, $order_item->order_id, CMenuItemInventoryHistory::EDIT_PHASE_1, $servingQty);
								}
							}
							else
							{
								$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold -  " . " $servingQty where mii.recipe_id = {$order_item->recipe_id} and mii.store_id = $tempStoreID and mii.menu_id = {$Session->menu_id} and mii.is_deleted = 0");
							}
						}
						catch (exception $exc)
						{
							// don't allow a problem here to fail the order
							//log the problem
							CLog::RecordException($exc);

							$debugStr = "INV_CONTROL ISSUE- Edited Order subtract deleted items: " . $order_item->order_id . " | Item: " . $order_item->menu_item_id . " | Store: " . $tempStoreID;
							CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
						}

						$adminUser = CUser::getCurrentUser()->id;
						$order_item->query("update order_item set is_deleted = 1, edit_sequence_id = {$order_record->id}, updated_by = $adminUser where id = {$order_item->id}");
					}

					// addons are processed separately and must be added into the subtotals when displaying the confirmation
					$newAddonQty = 0;
					$newAddonAmount = 0;

					// record changes to the master
					self::addMenuItemsToOrder($this->originalOrder, $orgPrices, $newAddonQty, $newAddonAmount, $introPrice);

					if (!$this->originalOrder->verifyAdequateInventory())
					{

						$itemsOversold = $this->originalOrder->getInvExceptionItemsString();

						$tpl->setErrorMsg('Inventory has changed since the order was started and an item has run out of stock. Please review the order and try again. Items adjusted are:<br />' . $itemsOversold);
						//	header('Location: '.$_SERVER['REQUEST_URI']);
						throw new Exception("INV_EXC");
					}

					// IF the original order was observe only the observe only item is still attached ..if we have 1 or more menu items then delete this
					if ($isTODD && $this->originalOrder->countItems() > 0)
					{
						$order_id = $this->originalOrder->id;
						$product_id = COrders::TODD_FREE_ATTENDANCE_PRODUCT_ID;
						$products = DAO_CFactory::create('order_item');
						$adminUser = CUser::getCurrentUser()->id;
						$products->query("update order_item set is_deleted = 1, edit_sequence_id = {$order_record->id}, updated_by = $adminUser  where order_id = $order_id and product_id = $product_id and is_deleted = 0");
						$this->originalOrder->clearObserveOnlyProduct();
					}

					$this->originalOrder->refreshForEditing();
					$this->originalOrder->insertEditedItems($sessionIsInPast);

					$this->originalOrder->recalculate(true, $suppressSessionDiscount); // true = editing

					if (!isset($this->originalOrder->coupon_code_id) || $this->originalOrder->coupon_code_id === '' || $this->originalOrder->coupon_code_id === '0' || $this->originalOrder->coupon_code_id === 0)
					{
						$this->originalOrder->coupon_code_id = 'null';
						$tpl->assign('couponIsValidWithPlatePoints', true);
					}
					else
					{
						$coupon = $this->originalOrder->getCoupon();
						if ($coupon)
						{
							$tpl->assign('couponVal', $this->originalOrder->coupon_code_discount_total);
							$tpl->assign('couponDiscountMethod', $coupon->discount_method);
							$tpl->assign('couponDiscountVar', $coupon->discount_var);
							$tpl->assign('couponlimitedToFT', ($coupon->limit_to_finishing_touch ? true : false));
							$tpl->assign('couponIsValidWithReferralCredit', ($coupon->valid_with_customer_referral_credit ? true : false));
							$tpl->assign('couponIsValidWithPlatePoints', ($coupon->valid_with_plate_points_credits ? true : false));
						}
						else
						{
							$tpl->assign('couponIsValidWithPlatePoints', true);
						}
					}

					if (!isset($this->originalOrder->bundle_id) || $this->originalOrder->bundle_id === '' || $this->originalOrder->bundle_id === '0' || $this->originalOrder->bundle_id === 0)
					{
						$this->originalOrder->bundle_id = 'null';
					}

					$this->originalOrder->update();

					if (empty($pp_credit_adjust_summary))
					{
						$pp_credit_adjust_summary = false;
					}
					CPointsUserHistory::handleEvent($this->originalOrder->user_id, CPointsUserHistory::ORDER_EDITED, $pp_credit_adjust_summary, $this->originalOrder);

					// handle payments

					$this->processDelayedPaymentAdjustment();

					$this->processStoreCredits();

					if (isset($_POST['payment1_type']) && $_POST['payment1_type'] != "")
					{
						$this->processPayment1($paymentArr);
					}

					if (isset($_POST['payment2_type']) && $_POST['payment2_type'] != "")
					{
						$this->processPayment2();
					}

					// funds are being returned to the customer
					$this->processCredits();

					$this->originalOrder->query('COMMIT;');
					COrdersDigest::recordEditedOrder($this->originalOrder, $originalGrandTotal);

					$tpl->assign('orderEditSuccess', true);
					$tpl->assign('newAddonQty', $newAddonQty);
					$tpl->assign('newAddonAmount', $newAddonAmount);

					$this->originalOrder->timestamp_updated = date("Y-m-d H:i:s");

					if (!isset($_POST['suppressEmail']))
					{
						COrders::sendEditedOrderConfirmationEmail($this->User, $this->originalOrder);
					}

					$tpl->assign('back', "/?page=admin_order_mgr_thankyou&order=" . $this->originalOrder->id);

					CApp::bounce("/?page=admin_order_mgr_thankyou&order=" . $this->originalOrder->id);
				}
				catch (Exception $e)
				{

					$this->originalOrder->points_discount_total = $originalOrderPriorToUpdate->points_discount_total;

					$this->originalOrder->query('ROLLBACK;');

					$Form->setCSRFToken();

					$tpl->setErrorMsg("A problem occurred during order processing. <br />" . $e->getMessage());
					CLog::RecordException($e);
				}
			}
		} // end processing
		else
		{
			$Form->setCSRFToken();
		}

		finish_processing:

		// bundle was removed so update form
		if ($orderHasBundle && (empty($this->originalOrder->bundle_id) || $this->originalOrder->bundle_id == 'null'))
		{
			$Form->AddElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => 'selectedBundle',
				CForm::onClick => 'bundleClick',
				CForm::checked => false,
				CForm::org_value => 0,
				CForm::dd_required => false,
				CForm::disabled => false
			));
		}

		// Build template arrays for display
		if ($this->orderState != 'NEW')
		{
			$this->originalOrder->refreshForEditing();

			if (!empty($this->originalOrder->bundle_id) && $this->originalOrder->bundle_id != 'null')
			{

				if ($Session->session_type == CSession::DREAM_TASTE)
				{

					$dreamEventProperties = CDreamTasteEvent::sessionProperties($Session->id);

					$theBundle = DAO_CFactory::create('bundle');
					$theBundle->id = $dreamEventProperties->bundle_id;
					if (!$theBundle->find(true))
					{
						throw new Exception('Bundle not found for the selected session.');
					}

					$Tarray = $this->originalOrder->getItems();
					$selectedBIs = array();
					foreach ($Tarray as $id => $v)
					{
						$selectedBIs[$id] = $v[0];
					}

					$this->originalOrder->addTasteBundle($theBundle, $selectedBIs, true);
				}
				else
				{
					$activeBundle = CBundle::getActiveBundleForMenu($Session->menu_id, $this->daoStore);
					$selectedBIs = array();

					foreach ($orderedBItems as $id => $qty)
					{
						$selectedBIs[] = $id;
					}

					$this->originalOrder->addBundle($activeBundle, $selectedBIs, true);
				}
			}

			// Adjust override price to equal the last committed price
			$this->originalOrder->setOverridePricingToSavedValues($orgPrices);

			$this->originalOrder->recalculate(true);

			$OrderDetailsArray = COrders::buildOrderDetailArrays($this->User, $this->originalOrder, CUser::getCurrentUser()->user_type);
		}
		else
		{
			$OrderDetailsArray = array(
				'orderInfo' => array('grand_total' => 0),
				'paymentInfo' => array(),
				'sessionInfo' => array(),
				'storeInfo' => array('store_name' => $this->daoStore->store_name)
			);
		}

		$this->assignRefundsToPayments($OrderDetailsArray['paymentInfo']);

		$tpl->assign('customerName', $this->User->getName());
		$tpl->assign('user', $this->User->toArray());
		$tpl->assign('orderInfo', $OrderDetailsArray['orderInfo']);
		$tpl->assign('paymentInfo', $OrderDetailsArray['paymentInfo']);
		$tpl->assign('sessionInfo', $OrderDetailsArray['sessionInfo']);
		$tpl->assign('storeInfo', $OrderDetailsArray['storeInfo']);

		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::onChange => 'handleAutoAdjust',
			CForm::name => 'autoAdjust'
		));

		if ($this->orderState != 'NEW')
		{
			$tpl->assign('planArray', $planArray);
			$tpl->assign('menuInfo', $menuInfo);
		}

		$tpl->assign('form_direct_order', $Form->render());
		$tpl->assign('orgQuantities', $orgQuantities);
	}

	private function getOriginalPrices($order_id)
	{
		$retVal = array();
		$order_items = DAO_CFactory::create('order_item');
		$order_items->order_id = $order_id;

		$order_items->find();

		while ($order_items->fetch())
		{
			$retVal[$order_items->menu_item_id] = $order_items->sub_total / $order_items->item_count;
		}

		return $retVal;
	}

	private function removePromoFromDisplayItems(&$menuInfo, &$orgQuantities)
	{
		if (!empty($this->originalOrder->promo_code_id))
		{
			$Promo = DAO_CFactory::create('promo_code');
			$Promo->id = $this->originalOrder->promo_code_id;
			$foundIt = $Promo->findActive();
			if ($foundIt)
			{
				$Promo->fetch();
				if (!empty($Promo->promo_menu_item_id))
				{
					if (isset($menuInfo[$Promo->promo_menu_item_id]))
					{
						$menuInfo[$Promo->promo_menu_item_id][0]--;
					}
					$orgQuantities[$Promo->promo_menu_item_id]--;
				}
			}
		}
	}

	private function removeCouponFreeMealFromDisplayItems(&$Order, &$menuInfo, &$orgQuantities)
	{

		$coupon = $Order->getCoupon();
		if ($coupon && $coupon->discount_method == 'FREE_MEAL')
		{
			if (!empty($coupon->menu_item_id))
			{
				if (isset($menuInfo[$coupon->menu_item_id]))
				{
					$menuInfo[$coupon->menu_item_id][0]--;
				}
				$orgQuantities[$coupon->menu_item_id]--;
			}
		}
	}

	static function buildUserPreferredArray($order, $user, $store_id)
	{

		$activePreferred = DAO_CFactory::create('user_preferred');
		$activePreferred->user_id = $user->id;
		if ($activePreferred->findActive($store_id))
		{
			$activePreferred->fetch();
			$active = array(
				'id' => $activePreferred->id,
				'type' => $activePreferred->preferred_type,
				'value' => $activePreferred->preferred_value
			);
		}
		else
		{
			$active = false;
		}

		$original = false;

		if (isset($order->user_preferred_id) && $order->user_preferred_id)
		{
			$originalPreferred = DAO_CFactory::create('user_preferred');
			$originalPreferred->id = $order->user_preferred_id;
			if ($originalPreferred->find_includeDeleted(true))
			{
				$original = array(
					'id' => $originalPreferred->id,
					'type' => $originalPreferred->preferred_type,
					'value' => $originalPreferred->preferred_value
				);
			}
		}

		// if active and original are the same or have the same type and value - no need to show active
		if ($original && $active && $original['id'] === $active['id'])
		{
			$active = false;
		}

		if ($original && $active && $original['type'] === $active['type'] && $original['value'] === $active['value'])
		{
			$active = false;
		}

		return array(
			$active,
			$original
		);
	}

	static function buildSessionDiscountArray($order, $session)
	{
		$active = false;

		if (isset($session->session_discount_id) && $session->session_discount_id)
		{
			$activeSessionDiscount = DAO_CFactory::create('session_discount');
			$activeSessionDiscount->id = $session->session_discount_id;
			if ($activeSessionDiscount->find(true))
			{
				$active = array(
					'id' => $activeSessionDiscount->id,
					'type' => $activeSessionDiscount->discount_type,
					'value' => $activeSessionDiscount->discount_var
				);
			}
		}

		$original = false;

		if (isset($order->session_discount_id) && $order->session_discount_id)
		{
			$originalSessionDiscount = DAO_CFactory::create('session_discount');
			$originalSessionDiscount->id = $order->session_discount_id;
			if ($originalSessionDiscount->find(true))
			{
				$original = array(
					'id' => $originalSessionDiscount->id,
					'type' => $originalSessionDiscount->discount_type,
					'value' => $originalSessionDiscount->discount_var
				);
			}
		}

		// if active and original are the same or have the same type and value - no need to show active
		if ($original && $active && $original['id'] === $active['id'])
		{
			$active = false;
		}

		if ($original && $active && $original['type'] === $active['type'] && $original['value'] === $active['value'])
		{
			$active = false;
		}

		return array(
			$active,
			$original
		);
	}

	function getReferenceTransactionArray()
	{
		$origPayment = DAO_CFactory::create('payment');
		$origPayment->order_id = $this->originalOrder->id;
		$origPayment->payment_type = CPayment::CC;

		$refPayArray = array();
		if ($origPayment->find())
		{

			while ($origPayment->fetch())
			{
				if ($origPayment->is_delayed_payment)
				{
					if ($origPayment->delayed_payment_status == 'SUCCESS')
					{
						$refPayArray[$origPayment->delayed_payment_transaction_number] = array(
							'cc_type' => $origPayment->credit_card_type,
							'amount' => $origPayment->total_amount,
							'card_number' => $origPayment->payment_number
						);
					}
				}
				else
				{
					$refPayArray[$origPayment->payment_transaction_number] = array(
						'cc_type' => $origPayment->credit_card_type,
						'amount' => $origPayment->total_amount,
						'card_number' => $origPayment->payment_number
					);
				}
			}
		}
		else
		{
			$refPayArray[0] = "No Transactions available";
		}

		return $refPayArray;
	}

	function assignRefundsToPayments(&$paymentInfo, &$refTransArray = false)
	{

		$sensiblePaymentArray = array();
		foreach ($paymentInfo as $k => $arrItem)
		{
			if (is_array($arrItem))
			{
				$payment_type = $arrItem['payment_type'];

				if ($payment_type == CPayment::CC or $payment_type == CPayment::REFUND)
				{
					$payment_is_delayed_payment = (isset($arrItem['is_delayed_payment']) && $arrItem['is_delayed_payment']);
					$payment_delayed_payment_status = (isset($arrItem['delayed_payment_status']) && $arrItem['delayed_payment_status']);

					$sensiblePaymentArray[$arrItem['id']] = array(
						'is_delayed_payment' => $payment_is_delayed_payment,
						'payment_delayed_payment_status' => $payment_delayed_payment_status,
						'total' => $arrItem['total']['other'],
						'type' => $payment_type,
						'trans_id' => $arrItem['payment_transaction_id']['other'],
						'payment_ord_num' => $k
					);

					if ($payment_type == CPayment::REFUND)
					{
						$sensiblePaymentArray[$arrItem['id']]['original_payment_id'] = $arrItem['original_payment']['other'];
					}
					else
					{
						$sensiblePaymentArray[$arrItem['id']]['current_refunded_amount'] = 0;
					}
				}
			}
		}

		foreach ($sensiblePaymentArray as $id => $payment)
		{
			if ($payment['type'] == CPayment::REFUND)
			{
				if (isset($sensiblePaymentArray[$payment['original_payment_id']]))
				{
					$sensiblePaymentArray[$payment['original_payment_id']]['current_refunded_amount'] += $payment['total'];
				}
			}
		}

		foreach ($sensiblePaymentArray as $id => $payment)
		{
			if ($payment['type'] == CPayment::CC)
			{
				if (isset($payment['current_refunded_amount']) && $payment['current_refunded_amount'] > 0)
				{

					$amountStr = "<span style='color:red'>$" . CTemplate::moneyFormat($payment['current_refunded_amount']) . " ($" . CTemplate::moneyFormat($payment['total'] - $payment['current_refunded_amount']) . " remaining)</span>";
					$paymentInfo[$payment['payment_ord_num']]['current_refunded_amount'] = array(
						'title' => 'Currently Refunded',
						'other' => $amountStr
					);

					$paymentTransID = $paymentInfo[$payment['payment_ord_num']]['payment_transaction_number']['other'];

					if ($refTransArray)
					{
						$refTransArray[$paymentTransID]['current_refunded_amount'] = CTemplate::moneyFormat($payment['current_refunded_amount']);
					}
				}
			}
		}
	}

	public static function buildPaymentForm($tpl, $Form, $numPayments, $Store, $order_state, $sessionObj = false, $addRefType = true, $addDPAdjustType = false, $refPaymentTypeData = false)
	{

		$cardOptions = array('null' => 'Card Type');
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

		$initialYear = date('y');
		$yearOptions = array(
			'null' => 'Year',
			$initialYear => $initialYear
		);
		for ($i = 0; $i < 12; $i++)
		{
			$yearOptions[++$initialYear] = $initialYear;
		}

		for ($i = 1; $i <= $numPayments; $i++)
		{
			$Form->DefaultValues['payment' . $i . '_gift_cert_type'] = 0;

			// amount
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::onKeyUp => 'payAmountChange',
				CForm::name => 'payment' . $i . '_gc_total_amount',
				CForm::length => 16,
				CForm::dd_required => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment' . $i . '_gc_payment_number',
				CForm::length => 16,
				CForm::autocomplete => false,
				CForm::dd_required => true
			));

			$Form->DefaultValues['payment' . $i . '_gift_cert_type'] = 0;

			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'payment' . $i . '_gift_cert_type',
				CForm::options => array(
					CPayment::GC_TYPE_DONATED => 'Donated Voucher',
					CPayment::GC_TYPE_SCRIP => 'Scrip'
				),
				CForm::dd_required => true
			));

			// amount
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::onKeyUp => 'payAmountChange',
				CForm::name => 'payment' . $i . '_cash_total_amount',
				CForm::length => 16,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment' . $i . '_cash_payment_number',
				CForm::length => 16
			));

			// amount
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::onKeyUp => 'payAmountChange',
				CForm::name => 'payment' . $i . '_refund_cash_total_amount',
				CForm::length => 16,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment' . $i . '_refund_cash_payment_number',
				CForm::length => 16
			));

			// amount
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::onKeyUp => 'payAmountChange',
				CForm::name => 'payment' . $i . '_check_total_amount',
				CForm::length => 16,
				CForm::dd_required => true,
				CForm::money => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment' . $i . '_check_payment_number',
				CForm::length => 16
			));

			// amount
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::onKeyUp => 'payAmountChange',
				CForm::name => 'payment' . $i . '_cc_total_amount',
				CForm::length => 16,
				CForm::money => true,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment' . $i . '_ccNameOnCard',
				CForm::length => 50,
				CForm::autocomplete => false,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment' . $i . '_ccNumber',
				CForm::number => true,
				CForm::autocomplete => false,
				CForm::size => 16,
				CForm::length => 16,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'payment' . $i . '_ccMonth',
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
				),
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'payment' . $i . '_ccType',
				CForm::options => $cardOptions,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'payment' . $i . '_ccYear',
				CForm::options => $yearOptions,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment' . $i . '_cc_security_code',
				CForm::length => 6,
				CForm::size => 7,
				CForm::number => true
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "billing_address_" . $i,
				CForm::size => 30,
				CForm::length => 50
			));

			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => "billing_postal_code_" . $i,
				CForm::size => 16,
				CForm::length => 16
			));
			// amount


			if ($sessionObj && $order_state != 'ACTIVE')
			{

				$sessionTS = strtotime($sessionObj->session_start) - 518400; // allow delayed payment 6 days prior
				if (strtotime("now") < $sessionTS)
				{
					$tpl->assign('canDelayPayment', true);
				}
				else
				{
					$tpl->assign('canDelayPayment', false);
				}

				if (!isset($Form->DefaultValues['payment' . $i . '_is_store_specific_flat_rate_deposit_delayed_payment']))
				{
					$Form->DefaultValues['payment' . $i . '_is_store_specific_flat_rate_deposit_delayed_payment'] = '0';
				}

				// always create and render form elements but hide them if illegal, they could become legal through rescheduling of a saved order
				$Form->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => 'payment' . $i . '_is_store_specific_flat_rate_deposit_delayed_payment',
					CForm::dd_required => false,
					CForm::value => '0'
				));
				$Form->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => 'payment' . $i . '_is_store_specific_flat_rate_deposit_delayed_payment',
					CForm::dd_required => false,
					CForm::value => '1'
				));
				$Form->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => 'payment' . $i . '_is_store_specific_flat_rate_deposit_delayed_payment',
					CForm::dd_required => false,
					CForm::value => '2'
				));

				// ref payments can be delayed also
				if ($refPaymentTypeData)
				{
					if (!isset($Form->DefaultValues['ref_payment' . $i . '_is_store_specific_flat_rate_deposit_delayed']))
					{
						$Form->DefaultValues['ref_payment' . $i . '_is_store_specific_flat_rate_deposit_delayed'] = '0';
					}
					$Form->AddElement(array(
						CForm::type => CForm::RadioButton,
						CForm::name => 'ref_payment' . $i . '_is_store_specific_flat_rate_deposit_delayed',
						CForm::dd_required => false,
						CForm::value => '0'
					));
					$Form->AddElement(array(
						CForm::type => CForm::RadioButton,
						CForm::name => 'ref_payment' . $i . '_is_store_specific_flat_rate_deposit_delayed',
						CForm::dd_required => false,
						CForm::value => '1'
					));
					$Form->AddElement(array(
						CForm::type => CForm::RadioButton,
						CForm::name => 'ref_payment' . $i . '_is_store_specific_flat_rate_deposit_delayed',
						CForm::dd_required => false,
						CForm::value => '2'
					));
				}
			}
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::onKeyUp => 'payAmountChange',
			CForm::name => 'payment1_reference_total_amount',
			CForm::length => 16,
			CForm::money => true,
			CForm::dd_required => true
		));

		$paymentTypeArray = array('null' => '---choose payment---');

		if ($addRefType)
		{
			$paymentTypeArray['REFERENCE'] = 'Credit Card (use original card)';
			$paymentTypeArray['CC_REFUND'] = 'Refund Credit Card';
		}

		if ($addDPAdjustType)
		{
			$paymentTypeArray['DPADJUST'] = 'Adjust Delayed Payment';
		}

		$paymentTypeArray[CPayment::CC] = 'Credit Card (new)';
		$paymentTypeArray[CPayment::GIFT_CERT] = 'Certificate';
		$paymentTypeArray[CPayment::CHECK] = 'Check';
		$paymentTypeArray[CPayment::CASH] = 'Cash';
		$paymentTypeArray[CPayment::GIFT_CARD] = 'Gift Card';

		if ($refPaymentTypeData)
		{
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment1_ref_total_amount',
				CForm::length => 16,
				CForm::money => true,
				CForm::dd_required => true
			));
			$Form->AddElement(array(
				CForm::type => CForm::Text,
				CForm::name => 'payment2_ref_total_amount',
				CForm::length => 16,
				CForm::money => true,
				CForm::dd_required => true
			));
			foreach ($refPaymentTypeData as $thisRef)
			{
				$paymentTypeArray['REF_' . $thisRef['payment_id']] = "Reference " . $thisRef['card_type'] . " " . $thisRef['cc_number'] . " " . date("(m-d-y)", $thisRef['date']);
			}
		}

		$paymentTypeArray[CPayment::CREDIT] = 'No Charge';
		$paymentTypeArray[CPayment::REFUND_CASH] = 'Refund Cash';

		// Gift Cards widgets
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_number',
			CForm::autocomplete => false,
			CForm::length => 16,
			CForm::maxlength => 16,
			CForm::dd_required => false
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_amount',
			CForm::length => 6,
			CForm::maxlength => 6,
			CForm::money => true,
			CForm::dd_required => false
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_security_code',
			CForm::autocomplete => false,
			CForm::length => 3,
			CForm::maxlength => 3,
			CForm::dd_required => false
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "gift_card_ccMonth",
			CForm::dd_required => true,
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

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "gift_card_ccYear",
			CForm::dd_required => true,
			CForm::options => $yearOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'payment1_type',
			CForm::options => $paymentTypeArray,
			CForm::onChange => 'changePayment1'
		));

		$options = array(
			'null' => '---choose payment---',
			CPayment::CC => 'Credit Card',
			CPayment::CHECK => 'Check',
			CPayment::CASH => 'Cash'
		);

		if ($refPaymentTypeData)
		{
			foreach ($refPaymentTypeData as $thisRef)
			{
				$options['REF_' . $thisRef['payment_id']] = "Reference " . $thisRef['card_type'] . " " . $thisRef['cc_number'] . " " . date("(m-d-y)", $thisRef['date']);
			}
		}

		if ($numPayments == 2)
		{
			$Form->AddElement(array(
				CForm::type => CForm::DropDown,
				CForm::name => 'payment2_type',
				CForm::options => $options,
				CForm::onChange => 'changePayment2'
			));
		}
	}

	function getCreditableStoreCreditPaymentTotal()
	{

		// first get all payments
		$scPayment = DAO_CFactory::create('payment');
		$scPayment->order_id = $this->originalOrder->id;
		$scPayment->payment_type = CPayment::STORE_CREDIT;

		$scPayment->find();

		$total_SC_Payments = 0;

		while ($scPayment->fetch())
		{
			$total_SC_Payments += $scPayment->total_amount;
		}

		// then get all GC refunds and subtract
		$scRefunds = DAO_CFactory::create('payment');
		$scRefunds->order_id = $this->originalOrder->id;
		$scRefunds->payment_type = CPayment::REFUND_STORE_CREDIT;

		$scRefunds->find();

		while ($scRefunds->fetch())
		{
			$total_SC_Payments -= $scRefunds->total_amount;
		}

		return $total_SC_Payments;
	}

	function getCreditableGiftCardPaymentTotal()
	{

		// first get all payments
		$gcPayment = DAO_CFactory::create('payment');
		$gcPayment->order_id = $this->originalOrder->id;
		$gcPayment->payment_type = CPayment::GIFT_CARD;

		$gcPayment->find();

		$total_GC_Payments = 0;

		while ($gcPayment->fetch())
		{
			$total_GC_Payments += $gcPayment->total_amount;
		}

		// then get all GC refunds and subtract
		$gcRefunds = DAO_CFactory::create('payment');
		$gcRefunds->order_id = $this->originalOrder->id;
		$gcRefunds->payment_type = CPayment::REFUND_GIFT_CARD;

		$gcRefunds->find();

		while ($gcRefunds->fetch())
		{
			$total_GC_Payments -= $gcRefunds->total_amount;
		}

		return $total_GC_Payments;
	}

	function processStoreCredits()
	{
		$sc_payment_array = array();
		$amount_paid = 0;

		if (isset($_POST['use_store_credits']))
		{
			$amountToUse = $_POST['store_credits_amount'];

			if ($amountToUse > 0 && $amountToUse < 500)// sanity check
			{
				foreach ($this->Store_Credit_Array as $id => $vals)
				{
					$amount_paid += $vals['Amount'];

					$newPayment = DAO_CFactory::create('payment');
					$newPayment->user_id = $this->originalOrder->user_id;
					$newPayment->store_id = $this->originalOrder->store_id;
					$newPayment->order_id = $this->originalOrder->id;
					$newPayment->is_delayed_payment = 0;
					$newPayment->payment_type = CPayment::STORE_CREDIT;
					$newPayment->store_credit_id = $id;
					$newPayment->payment_number = $vals['Source'];
					$newPayment->is_escrip_customer = 0;
					$newPayment->store_credit_DAO = $vals['DAO_Obj'];

					if ($amount_paid >= $amountToUse)
					{
						$newPayment->total_amount = CTemplate::moneyFormat($amountToUse - ($amount_paid - $vals['Amount']));
						array_push($sc_payment_array, $newPayment);
						break;
					}
					else
					{
						$newPayment->total_amount = CTemplate::moneyFormat($vals['Amount']);
						array_push($sc_payment_array, $newPayment);
					}
				}

				if (!empty($sc_payment_array))
				{
					$this->originalOrder->processStoreCreditPayments($sc_payment_array);
				}
			}
		}
	}

	function processPayment1($paymentInfo)
	{
		$newPayment = DAO_CFactory::create('payment');
		$newPayment->user_id = $this->originalOrder->user_id;
		$newPayment->store_id = $this->originalOrder->store_id;
		$newPayment->order_id = $this->originalOrder->id;
		$newPayment->is_delayed_payment = 0;
		$newPayment->is_escrip_customer = 0;

		$success = true;

		// --------------------------------------------------------------- Validation
		//check for reference transaction request
		if ($_POST['payment1_type'] == 'REFERENCE')
		{
			if (!$paymentInfo['canAutoAdjust'])
			{
				CApp::instance()->template()->setErrorMsg('Cannot process reference transaction - transaction not found.');
				throw new Exception("Cannot process reference transaction - transaction not found.");
			}
			else
			{
				$origPayments = $this->getReferencedTransactions();
				$this->processReferences($origPayments, $newPayment);
				$this->removePayAtSessionPayment();

				return;
			}
		}

		//check cc#
		if ($_POST['payment1_type'] == CPayment::CC)
		{
			list($success, $msg) = COrders::validateCC($_POST['payment1_ccNumber'], $_POST['payment1_ccType'], $_POST['payment1_ccMonth'], $_POST['payment1_ccYear']);
			if (!$success)
			{
				CApp::instance()->template()->setErrorMsg($msg);
				throw new Exception("Credit Card did not validate.");
			}
			else
			{
				self::processCC(1, $newPayment);
				$this->removePayAtSessionPayment();

				return;
			}
		}

		if ($_POST['payment1_type'] == CPayment::GIFT_CERT)
		{
			// check gift certificate here?
			if (!$success)
			{
				CApp::instance()->template()->setErrorMsg("Gift Certificate did not validate.");
				throw new Exception("Gift Certificate did not validate.");
			}
			else
			{
				self::processGiftCert($newPayment);
				$this->removePayAtSessionPayment();

				return;
			}
		}

		if ($_POST['payment1_type'] == CPayment::CASH)
		{
			self::processCash(1, $newPayment);
			$this->removePayAtSessionPayment();

			return;
		}

		if ($_POST['payment1_type'] == CPayment::REFUND_CASH)
		{
			self::processRefundCash($newPayment);
			$this->removePayAtSessionPayment();

			return;
		}

		if ($_POST['payment1_type'] == CPayment::CHECK)
		{
			self::processCheck(1, $newPayment);
			$this->removePayAtSessionPayment();

			return;
		}

		if ($_POST['payment1_type'] == CPayment::CREDIT)
		{
			self::processCredit($newPayment);
			$this->removePayAtSessionPayment();

			return;
		}

		if ($_POST['payment1_type'] == CPayment::GIFT_CARD)
		{
			self::processDebitGiftCard($newPayment);
			$this->removePayAtSessionPayment();

			return;
		}

		if ($_POST['payment1_type'] == "CC_REFUND")
		{
			$this->processDirectCCRefundRequest($newPayment);

			return;
		}

		if (strpos($_POST['payment1_type'], "REF_") === 0)
		{
			$ref_number = substr($_POST['payment1_type'], 4);
			$this->processExternalReference($newPayment, $ref_number, 1);
			$this->removePayAtSessionPayment();

			return;
		}
	}

	function getReferencedTransactions($isCreditToCustomer = false, $override_prefix = false)
	{

		if ($override_prefix)
		{
			$prefix = $override_prefix;
		}
		else
		{
			$prefix = $isCreditToCustomer ? "Cr_RT_" : "RT_";
		}

		$payments = array();

		foreach ($_POST as $k => $v)
		{
			if (strpos($k, $prefix) === 0)
			{
				if ($v != 0 && $v != "")
				{
					if ($isCreditToCustomer)
					{
						$paymentId = substr($k, strlen($prefix));
					}
					else
					{
						$paymentId = substr($k, 3);
					}

					$origPayment = DAO_CFactory::create('payment');
					$origPayment->payment_transaction_number = $paymentId;
					// TODO: are these guys really unique. Might want to qualify search with more data

					if (!$origPayment->find(true))
					{
						// might a delayed payment so check that field
						$origPayment->payment_transaction_number = null;
						$origPayment->delayed_payment_transaction_number = $paymentId;
						if (!$origPayment->find(true))
						{
							throw new Exception("Original credit card payment not found.");
						}

						$origPayment->payment_transaction_number = $paymentId;
					}

					$origPayment->new_amount = $isCreditToCustomer ? ($v * -1) : $v;

					$payments[$paymentId] = $origPayment;
				}
			}
		}

		return $payments;
	}

	function processReferences($origPayments, $newPayment)
	{

		$counter = 0;

		foreach ($origPayments as $id => $origPayment)
		{
			$counter++;

			if ($counter > 1)
			{
				// first new payment was provided to us, but for others we must create and init them
				$newPayment = DAO_CFactory::create('payment');
				$newPayment->user_id = $this->originalOrder->user_id;
				$newPayment->store_id = $this->originalOrder->store_id;
				$newPayment->order_id = $this->originalOrder->id;
				$newPayment->is_delayed_payment = 0;
				$newPayment->is_escrip_customer = 0;
			}

			$newPayment->total_amount = $origPayment->new_amount;
			$newPayment->credit_card_type = $origPayment->credit_card_type;
			$newPayment->payment_number = $origPayment->payment_number;
			$newPayment->payment_type = CPayment::CC;
			$newPayment->referent_id = $origPayment->id;

			$rslt = $newPayment->processByReference($this->User, $this->originalOrder, $this->daoStore, $origPayment->payment_transaction_number); // will credit or debit

			if ($rslt['result'] != 'success')
			{
				//verisign failed, rollback order and booking

				if ($rslt['result'] == 'transactionDecline')
				{
					CLog::RecordNew(CLog::CCDECLINE, $newPayment->verisignExtendedRslt);
					CApp::instance()->template()->setErrorMsg($rslt['userText']);
					throw new Exception("Reference transaction was declined.");
				}
				else if ($rslt['result'] == 'overCreditingOriginalTransactionError')
				{
					CLog::RecordNew(CLog::ERROR, $newPayment->verisignExtendedRslt, '', '', true);
					CApp::instance()->template()->setErrorMsg('Cannot process reference transaction. PayPal returned error number 117 which usually means that the credit limit of the referenced (original) transaction has been exceeded. (You cannot credit more than the original amount of the sales transaction referenced minus any subsequent credits.) ');
					throw new Exception("Cannot process reference transaction.");
				}
				else
				{
					CLog::RecordNew(CLog::ERROR, $newPayment->verisignExtendedRslt, '', '', true);
					CApp::instance()->template()->setErrorMsg('Cannot process reference transaction.');
					throw new Exception("Cannot process reference transaction.");
				}
			}
			else
			{
				//$newPayment->insert();		is inserted by processByReference
			}
		}
	}

	function removePayAtSessionPayment()
	{
		$tmpPaymentObj = DAO_CFactory::create('payment');
		$tmpPaymentObj->query("update payment set is_deleted = 1 where order_id = {$this->originalOrder->id} and payment_type = 'PAY_AT_SESSION'");

		// also get rid of any zero amount No Charge (credit) payments
		$tmpPaymentObj2 = DAO_CFactory::create('payment');
		$tmpPaymentObj2->query("update payment set is_deleted = 1 where order_id = {$this->originalOrder->id} and payment_type = 'CREDIT' and total_amount = 0");
	}

	function processCC($paymentNumber, $newPayment)
	{

		$newPayment->payment_type = CPayment::CC;

		if ($paymentNumber == 1)
		{
			$newPayment->total_amount = $_POST['payment1_cc_total_amount'];
		}
		else
		{
			$newPayment->total_amount = $_POST['payment2_cc_total_amount'];
		}

		$addrObj = DAO_CFactory::create('address');
		$addrObj->user_id = $this->originalOrder->user_id;

		$addr = null;
		$zip = null;
		if ($addrObj->find(true))
		{
			$addr = $addrObj->address_line1;
			$zip = $addrObj->postal_code;
		}
		else if ($this->User->is_partial_account)
		{
			// look for address from payment form ...only used is user account is partial
			if ($paymentNumber == 1)
			{
				if (isset($_POST['billing_address_1']) && !empty($_POST['billing_address_1']))
				{
					$addr = $_POST['billing_address_1'];
				}

				if (isset($_POST['billing_postal_code_1']) && !empty($_POST['billing_postal_code_1']))
				{
					$zip = $_POST['billing_postal_code_1'];
				}
			}
			else
			{
				if (isset($_POST['billing_address_2']) && !empty($_POST['billing_address_2']))
				{
					$addr = $_POST['billing_address_2'];
				}

				if (isset($_POST['billing_postal_code_2']) && !empty($_POST['billing_postal_code_2']))
				{
					$zip = $_POST['billing_postal_code_2'];
				}
			}
		}

		if ($paymentNumber == 1)
		{
			$newPayment->setCCInfo($_POST['payment1_ccNumber'], $_POST['payment1_ccMonth'], $_POST['payment1_ccYear'], $_POST['payment1_ccNameOnCard'], $addr, null, null, $zip, $_POST['payment1_ccType'], (empty($_POST['payment1_cc_security_code']) ? false : $_POST['payment1_cc_security_code']));
		}
		else
		{
			$newPayment->setCCInfo($_POST['payment2_ccNumber'], $_POST['payment2_ccMonth'], $_POST['payment2_ccYear'], $_POST['payment2_ccNameOnCard'], $addr, null, null, $zip, $_POST['payment2_ccType'], (empty($_POST['payment2_cc_security_code']) ? false : $_POST['payment2_cc_security_code']));
		}

		$rslt = $newPayment->processPayment($this->User, $this->originalOrder, $this->daoStore);

		if ($rslt['result'] != 'success')
		{
			//verisign failed, rollback order and booking

			if ($rslt['result'] == 'transactionDecline')
			{
				CLog::RecordNew(CLog::CCDECLINE, $newPayment->verisignExtendedRslt);
				throw new Exception("Credit Card transaction was declined.");
			}
			else
			{
				CLog::RecordNew(CLog::ERROR, $newPayment->verisignExtendedRslt, '', '', true);
				CApp::instance()->template()->setErrorMsg('Cannot process credit card transaction.');
				throw new Exception("Cannot process credit card transaction.");
			}
		}
		else
		{
			$newPayment->insert();
		}
	}

	function processGiftCert($newPayment)
	{
		$newPayment->payment_type = CPayment::GIFT_CERT;
		$newPayment->total_amount = $_POST['payment1_gc_total_amount'];
		$newPayment->gift_certificate_number = $_POST['payment1_gc_payment_number'];
		$newPayment->gift_cert_type = $_POST['payment1_gift_cert_type'];
		$newPayment->insert();
		$newPayment->recordRevenueEvent($this->originalOrder->session);
	}

	function processCash($paymentNumber, $newPayment)
	{
		$newPayment->payment_type = CPayment::CASH;

		if ($paymentNumber == 1)
		{
			$newPayment->total_amount = $_POST['payment1_cash_total_amount'];
			$newPayment->payment_number = $_POST['payment1_cash_payment_number'];
		}
		else
		{
			$newPayment->total_amount = $_POST['payment2_cash_total_amount'];
			$newPayment->payment_number = $_POST['payment2_cash_payment_number'];
		}

		$newPayment->insert();
	}


	// This was for the old style Gift Card that was converted to store credit when redeemed
	// BUT NOW - handles all returns of Store Credit

	function processRefundCash($newPayment)
	{
		$newPayment->payment_type = CPayment::REFUND_CASH;

		$newPayment->total_amount = $_POST['payment1_refund_cash_total_amount'];
		$newPayment->payment_number = $_POST['payment1_refund_cash_payment_number'];

		$newPayment->insert();
	}

	function processCheck($paymentNumber, $newPayment)
	{
		$newPayment->payment_type = CPayment::CHECK;
		if ($paymentNumber == 1)
		{
			$newPayment->total_amount = $_POST['payment1_check_total_amount'];
			$newPayment->payment_number = $_POST['payment1_check_payment_number'];
		}
		else
		{
			$newPayment->total_amount = $_POST['payment2_check_total_amount'];
			$newPayment->payment_number = $_POST['payment2_check_payment_number'];
		}

		$newPayment->insert();
	}

	function processCredit($newPayment)
	{
		$newPayment->payment_type = CPayment::CREDIT;
		$newPayment->total_amount = $this->originalOrder->grand_total;
		$newPayment->insert();
	}

	function processDebitGiftCard($newPayment)
	{

		if (!ValidationRules::isValidPositiveDecimal($_POST['debit_gift_card_amount']))
		{
			throw new Exception('Debit Gift Card Transaction failed. The amount was invalid.');
		}

		$newPayment->total_amount = $_POST['debit_gift_card_amount'];
		$newPayment->payment_number = $_POST['debit_gift_card_number'];
		$newPayment->payment_type = CPayment::GIFT_CARD;

		$GCresult = CGiftCard::unloadDebitGiftCardWithRetry($newPayment->payment_number, $newPayment->total_amount, false, $this->originalOrder->store_id, $this->originalOrder->id);

		if ($GCresult)
		{
			$newPayment->payment_transaction_number = $GCresult;
			$newPayment->payment_number = str_repeat('X', (strlen($newPayment->payment_number) - 4)) . substr($newPayment->payment_number, -4);
			$newPayment->insert();
		}
		else
		{
			throw new Exception('Debit Gift Card Transaction failed. The number was invalid or there was insufficient funds.');
		}
	}

	function processDirectCCRefundRequest($newPayment)
	{
		$origPayments = $this->getReferencedTransactions(true, "Dir_Cr_RT_");        // true means we are crediting customer
		if (!empty($origPayments))  // if not empty then processReferences used our payment object
		{
			$this->processReferences($origPayments, $newPayment);
		}
	}

	function processExternalReference($newPayment, $ref_number, $paymentNumber)
	{
		$newPayment->payment_type = CPayment::CC;

		if ($paymentNumber == 1)
		{
			$newPayment->total_amount = $_POST['payment1_ref_total_amount'];
		}
		else
		{
			$newPayment->total_amount = $_POST['payment2_ref_total_amount'];
		}

		if ($newPayment->total_amount <= 0)
		{
			throw new Exception("Unable to find original payment during processing of reference.");
		}

		$origPayment = DAO_CFactory::create('payment');
		$origPayment->payment_transaction_number = $ref_number;
		if (!$origPayment->find(true))
		{
			CLog::RecordNew(CLog::ERROR, "Unable to find original payment during processing of reference.", '', '', true);
			throw new Exception("Unable to find original payment during processing of reference.");
		}

		$newPayment->referent_id = $origPayment->id;
		$newPayment->payment_number = $origPayment->payment_number;
		$newPayment->credit_card_type = $origPayment->credit_card_type;

		$rslt = $newPayment->processByReference($this->User, $this->originalOrder, $this->daoStore, $ref_number, false);

		if ($rslt['result'] != 'success')
		{
			if ($rslt['result'] == 'transactionDecline')
			{
				CLog::RecordNew(CLog::CCDECLINE, $newPayment->verisignExtendedRslt);
				throw new Exception("Credit Card transaction was declined.");
			}
			else
			{
				CLog::RecordNew(CLog::ERROR, $newPayment->verisignExtendedRslt, '', '', true);
				CApp::instance()->template()->setErrorMsg('Cannot process credit card transaction.');
				throw new Exception("Cannot process credit card transaction.");
			}
		}
		else
		{
			$newPayment->insert();
		}
	}

	function processPayment2()
	{
		$success = true;

		$newPayment = DAO_CFactory::create('payment');
		$newPayment->user_id = $this->originalOrder->user_id;
		$newPayment->store_id = $this->originalOrder->store_id;
		$newPayment->total_amount = payment1_cc_total_amount;
		$newPayment->order_id = $this->originalOrder->id;
		$newPayment->is_delayed_payment = 0;
		$newPayment->is_escrip_customer = 0;
		// --------------------------------------------------------------- Validation

		//check cc#
		if ($_POST['payment2_type'] == CPayment::CC)
		{
			list($success, $msg) = COrders::validateCC($_POST['payment2_ccNumber'], $_POST['payment2_ccType'], $_POST['payment2_ccMonth'], $_POST['payment2_ccYear']);
			if (!$success)
			{
				CApp::instance()->template()->setErrorMsg($msg);
				throw new Exception("Credit Card did not validate.");
			}
			else
			{
				self::processCC(2, $newPayment);
				$this->removePayAtSessionPayment();

				return;
			}
		}

		if ($_POST['payment2_type'] == CPayment::CASH)
		{
			self::processCash(2, $newPayment);
			$this->removePayAtSessionPayment();

			return;
		}

		if ($_POST['payment2_type'] == CPayment::CHECK)
		{
			self::processCheck(2, $newPayment);
			$this->removePayAtSessionPayment();

			return;
		}
	}

	function processCredits()
	{
		if (isset($_POST['credit_to_customer_refund_cash_amount']) && $_POST['credit_to_customer_refund_cash_amount'] > 0)
		{
			$this->refundCash($_POST['credit_to_customer_refund_cash_amount'], $_POST['credit_to_customer_refund_cash_number']);
		}

		$origPayments = $this->getReferencedTransactions(true);        // true means we are crediting customer
		if (!empty($origPayments))  // if not empty then processReferences used our payment object
		{

			$newPayment = DAO_CFactory::create('payment');
			$newPayment->user_id = $this->originalOrder->user_id;
			$newPayment->store_id = $this->originalOrder->store_id;
			$newPayment->order_id = $this->originalOrder->id;
			$newPayment->is_delayed_payment = 0;
			$newPayment->is_escrip_customer = 0;

			$this->processReferences($origPayments, $newPayment);
		}

		if (isset($_POST['storeCreditRefund']) && $_POST['storeCreditRefund'] > 0)
		{
			$this->CreditStoreCreditPayments($_POST['storeCreditRefund']);
		}

		if (isset($_POST['giftCardRefund']) && $_POST['giftCardRefund'] > 0)
		{
			$this->CreditGiftCardPayments($_POST['giftCardRefund']);
		}
	}

	function refundCash($amount, $number)
	{
		$newPayment = DAO_CFactory::create('payment');
		$newPayment->user_id = $this->originalOrder->user_id;
		$newPayment->store_id = $this->originalOrder->store_id;
		$newPayment->order_id = $this->originalOrder->id;
		$newPayment->is_delayed_payment = 0;
		$newPayment->total_amount = $amount;
		$newPayment->payment_number = $number;
		$newPayment->payment_type = CPayment::REFUND_CASH;

		$newPayment->insert();
	}

	function CreditStoreCreditPayments($totalToCredit)
	{
		// get all original GC payments for this order
		$orgGCPayments = $this->getStoreCreditArray();

		$amountToCreditForThisPayment = $totalToCredit;
		$totalAmountCredited = 0;

		foreach ($orgGCPayments as $id => $obj)
		{

			// TODO: This is really a lot more complex:
			// the available funds that can be credited to store credit are the total of STORE_CREDIT payments minus the REFUND_STORE_CREDIT payments
			// but the amount available for a particular STORE_CREDIT payment must be calculated for that particular payment.

			if ($obj->total_amount <= $amountToCreditForThisPayment)
			{
				$amountToCreditForThisPayment = $obj->total_amount;
			}

			// add store_credit_refund payment up to amount of current payment
			$newPayment = DAO_CFactory::create('payment');
			$newPayment->user_id = $this->originalOrder->user_id;
			$newPayment->store_id = $this->originalOrder->store_id;
			$newPayment->order_id = $this->originalOrder->id;
			$newPayment->payment_number = $obj->payment_number;
			$newPayment->payment_transaction_number = $obj->payment_transaction_number;
			$newPayment->is_delayed_payment = 0;
			$newPayment->is_escrip_customer = 0;
			$newPayment->payment_type = CPayment::REFUND_STORE_CREDIT;
			$newPayment->total_amount = $amountToCreditForThisPayment;

			$totalAmountCredited += $amountToCreditForThisPayment;

			$rslt = $newPayment->insert();
			if (!$rslt)
			{
				CLog::RecordNew(CLog::ERROR, "OrderEdit: Insert of REFUND_STORE_CREDIT failed for customer: " . $this->originalOrder->user_id, "", "", true);
			}
			else
			{
				$newPayment->recordRevenueEvent($this->originalOrder->session, true);
			}

			// add row to store credit table
			$storeCredit = DAO_CFactory::create('store_credit');
			$storeCredit->amount = $amountToCreditForThisPayment;
			$storeCredit->store_id = $this->originalOrder->store_id;
			$storeCredit->user_id = $this->originalOrder->user_id;
			$storeCredit->credit_card_number = $obj->payment_number;
			$storeCredit->payment_transaction_number = $obj->payment_transaction_number;

			$rslt = $storeCredit->insert();
			if (!$rslt)
			{
				CLog::RecordNew(CLog::ERROR, "OrderEdit: Insert of store credit failed for customer: " . $this->originalOrder->user_id, "", "", true);
			}

			$amountToCreditForThisPayment = $totalToCredit - $totalAmountCredited;
		}
	}

	function getStoreCreditArray()
	{

		$retVal = array();

		$storeCredit = DAO_CFactory::create('payment');
		$storeCredit->order_id = $this->originalOrder->id;
		$storeCredit->payment_type = CPayment::STORE_CREDIT;

		$storeCredit->find();

		while ($storeCredit->fetch())
		{
			$retVal[$storeCredit->id] = $storeCredit;
		}

		return $retVal;
	}

	function CreditGiftCardPayments($totalToCredit)
	{
		// get all original GC payments for this order
		$orgGCPayments = $this->getDebitGiftCardArray();

		$amountToCreditForThisPayment = $totalToCredit;
		$totalAmountCredited = 0;

		foreach ($orgGCPayments as $id => $obj)
		{

			if ($obj->total_amount <= $amountToCreditForThisPayment)
			{
				$amountToCreditForThisPayment = $obj->total_amount;
			}

			// add store_credit_refund payment up to amount of current payment
			$newPayment = DAO_CFactory::create('payment');
			$newPayment->user_id = $this->originalOrder->user_id;
			$newPayment->store_id = $this->originalOrder->store_id;
			$newPayment->order_id = $this->originalOrder->id;
			$newPayment->payment_number = $obj->payment_number;
			$newPayment->payment_transaction_number = $obj->payment_transaction_number;
			$newPayment->is_delayed_payment = 0;
			$newPayment->is_escrip_customer = 0;
			$newPayment->payment_type = CPayment::REFUND_GIFT_CARD;
			$newPayment->total_amount = $amountToCreditForThisPayment;

			$totalAmountCredited += $amountToCreditForThisPayment;

			$rslt = $newPayment->insert();
			if (!$rslt)
			{
				CLog::RecordNew(CLog::ERROR, "OrderEdit: Insert of REFUND_GIFT_CARD failed for customer: " . $this->originalOrder->user_id, "", "", true);
			}

			// add row to store credit table
			$storeCredit = DAO_CFactory::create('store_credit');
			$storeCredit->amount = $amountToCreditForThisPayment;
			$storeCredit->store_id = $this->originalOrder->store_id;
			$storeCredit->user_id = $this->originalOrder->user_id;
			$storeCredit->credit_card_number = $obj->payment_number;
			$storeCredit->payment_transaction_number = $obj->payment_transaction_number;

			$rslt = $storeCredit->insert();
			if (!$rslt)
			{
				CLog::RecordNew(CLog::ERROR, "OrderEdit: Insert of store credit failed for customer: " . $this->originalOrder->user_id, "", "", true);
			}

			$amountToCreditForThisPayment = $totalToCredit - $totalAmountCredited;
		}
	}

	function getDebitGiftCardArray()
	{

		$retVal = array();

		$giftCard = DAO_CFactory::create('payment');
		$giftCard->order_id = $this->originalOrder->id;
		$giftCard->payment_type = CPayment::GIFT_CARD;

		$giftCard->find();

		while ($giftCard->fetch())
		{
			$retVal[$giftCard->id] = $giftCard;
		}

		return $retVal;
	}

	static function cleanUpForeignKeys($OrderObj)
	{
		if (isset($OrderObj->premium_id) && $OrderObj->premium_id == "")
		{
			$OrderObj->premium_id = null;
		}

		if (isset($OrderObj->user_preferred_id) && $OrderObj->user_preferred_id == "")
		{
			$OrderObj->user_preferred_id = null;
		}

		if (isset($OrderObj->markup_id) && $OrderObj->markup_id == "")
		{
			$OrderObj->markup_id = null;
		}

		if (isset($OrderObj->session_discount_id) && $OrderObj->session_discount_id == "")
		{
			$OrderObj->session_discount_id = null;
		}

		if (isset($OrderObj->promo_code_id) && $OrderObj->promo_code_id == "")
		{
			$OrderObj->promo_code_id = null;
		}

		if (isset($OrderObj->sales_tax_id) && $OrderObj->sales_tax_id == "")
		{
			$OrderObj->sales_tax_id = null;
		}
	}

	/**
	 * @return ($order, $success, $msg)
	 */
	public static function addMenuItemsToOrder($OrderObj, $orgPrices, &$newAddonQty, &$newAddonAmount, $introPricing)
	{

		$array = $_REQUEST;

		//get menu from session
		$menu_id = $OrderObj->findSession()->menu_id;

		//clear existing menu items
		$OrderObj->clearItemsUnsafe();

		//add menu items
		$prelen = strlen(COrders::QUANTITY_PREFIX);
		$qty_keys = array_keys($array);
		$menu_item_ids = array();
		foreach ($qty_keys as $itemKey)
		{
			if (strstr($itemKey, COrders::QUANTITY_PREFIX) && is_numeric(substr($itemKey, $prelen)))
			{
				$menu_item_ids [] = substr($itemKey, $prelen);
			}
		}

		if (isset($array['selectedBundle']))
		{
			$activeBundle = CBundle::getActiveBundleForMenu($menu_id, $OrderObj->store_id);

			$selectedBIs = array();
			foreach ($array as $k => $v)
			{
				if (strpos($k, 'bnd_') === 0)
				{
					$thisBID = substr($k, 4);
					if ($v)
					{
						$selectedBIs[] = $thisBID;
					}
				}
			}

			$OrderObj->addBundle($activeBundle, $selectedBIs);
		}
		else
		{
			$OrderObj->addBundle(null);
		}

		$Session = $OrderObj->findSession();
		if ($Session->session_type == CSession::DREAM_TASTE)
		{

			$dreamEventProperties = CDreamTasteEvent::sessionProperties($Session->id);

			$theBundle = DAO_CFactory::create('bundle');
			$theBundle->id = $dreamEventProperties->bundle_id;
			if (!$theBundle->find(true))
			{
				throw new Exception('Bundle not found for the selected session.');
			}

			$selectedBIs = array();
			foreach ($array as $k => $v)
			{
				if (strpos($k, 'bnd_') === 0)
				{
					$thisBID = substr($k, 4);
					if ($v)
					{
						$selectedBIs[$thisBID] = $v;
					}
				}
			}

			$OrderObj->addTasteBundle($theBundle, $selectedBIs);
		}

		$getStoreMenu = CMenu::storeSpecificMenuExists($menu_id, $OrderObj->store_id);

		if ($OrderObj->family_savings_discount_version == 2)
		{
			//look for promo code
			if (isset($_POST['promotion_code']) && !empty($_POST['promotion_code']))
			{
				$Promo = DAO_CFactory::create('promo_code');
				$Promo->id = $_POST['promotion_code'];
				$foundIt = $Promo->findActive();
				if ($foundIt)
				{
					$Promo->fetch();
					// add the promo item, mark it as as promo by passing true in addMenuItem
					if (!empty($Promo->promo_menu_item_id))
					{
						$menuItemInfo = DAO_CFactory::create('menu_item');

						if ($getStoreMenu)
						{
							$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = " . $OrderObj->store_id . " AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id = " . $Promo->promo_menu_item_id . " AND mi.is_deleted = 0";
						}
						else
						{
							$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id IS NULL AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id = " . $Promo->promo_menu_item_id . " AND mi.is_deleted = 0";
						}
						$menuItemInfo->query($query);
						if ($menuItemInfo->fetch())
						{

							$OrderObj->addMenuItem(clone($menuItemInfo), 1, true);
						}
					}
				}
			}

			//look for coupon code
			if (isset($_POST['coupon_id']) && !empty($_POST['coupon_id']))
			{
				$Coupon = DAO_CFactory::create('coupon_code');
				$Coupon->id = $_POST['coupon_id'];

				if ($Coupon->find(true))
				{
					// add the promo item, mark it as as promo by passing true in addMenuItem

					if ($Coupon->discount_method == CCouponCode::FREE_MEAL && !empty($Coupon->menu_item_id))
					{
						$menuItemInfo = DAO_CFactory::create('menu_item');
						if ($getStoreMenu)
						{
							$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = " . $OrderObj->store_id . " AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id = " . $Coupon->menu_item_id . " AND mi.is_deleted = 0";
						}
						else
						{
							$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id IS NULL AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id = " . $Coupon->menu_item_id . " AND mi.is_deleted = 0";
						}
						$menuItemInfo->query($query);
						if ($menuItemInfo->fetch())
						{
							$OrderObj->addMenuItem(clone($menuItemInfo), 1, false, true);
						}
					}
				}
			}
		}

		// look through $_POST for new addon items
		foreach ($_POST as $k => $v)
		{
			if (strstr($k, 'qna_') && is_numeric(substr($k, 4)))
			{
				$menu_item_ids [] = substr($k, 4);
			}
		}

		if ($menu_item_ids)
		{
			$menuItemInfo = DAO_CFactory::create('menu_item');

			if ($getStoreMenu)
			{
				$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = " . $OrderObj->store_id . " AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ") AND mi.is_deleted = 0";
			}
			else
			{
				$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id IS NULL AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ") AND mi.is_deleted = 0";
			}

			$menuItemInfo->query($query);
			while ($menuItemInfo->fetch())
			{

				$qty = null;

				if (isset($array[COrders::QUANTITY_PREFIX . $menuItemInfo->id]))
				{
					$qty = $array[COrders::QUANTITY_PREFIX . $menuItemInfo->id];
				}
				else if (isset($_POST['qna_' . $menuItemInfo->id]))
				{
					$qty = $_POST['qna_' . $menuItemInfo->id];
					$newAddonQty += $qty;
					$newAddonAmount += COrders::getStorePrice($OrderObj->findMarkUp(), $menuItemInfo, $qty);
				}

				if (isset($orgPrices[$menuItemInfo->id]))
				{
					$menuItemInfo->override_price = $orgPrices[$menuItemInfo->id];
				}

				if ($menuItemInfo->pricing_type == CMenuItem::INTRO)
				{
					$menuItemInfo->override_price = $introPricing;
				}

				if ($menuItemInfo->is_bundle && $qty > 0)
				{
					$subItems = CBundle::getBundleMenuInfoForMenuItem($menuItemInfo->id, $menu_id, $OrderObj->store_id);
					$subItemKeys = array();
					foreach ($qty_keys as $itemKey)
					{
						$thisID = substr($itemKey, 4);

						if (strstr($itemKey, 'sbi_') && is_numeric($thisID) && array_key_exists($thisID, $subItems['bundle']))
						{
							$subItemKeys[] = substr($itemKey, $prelen);
						}
					}

					$subItemInfo = DAO_CFactory::create('menu_item');
					$select = "SELECT menu_item_category.category_type AS 'category', menu_item.*, menu_to_menu_item.override_price, menu_to_menu_item.menu_order_value FROM menu_item ";
					if ($getStoreMenu)
					{
						$joins = "INNER JOIN  menu_to_menu_item ON menu_to_menu_item.menu_item_id=menu_item.id and menu_to_menu_item.store_id = " . $OrderObj->store_id . " LEFT JOIN menu_item_category on menu_item.menu_item_category_id = menu_item_category.id ";
					}
					else
					{
						$joins = "INNER JOIN  menu_to_menu_item ON menu_to_menu_item.menu_item_id=menu_item.id and menu_to_menu_item.store_id is null LEFT JOIN menu_item_category on menu_item.menu_item_category_id = menu_item_category.id ";
					}

					$where = "where menu_item.id IN (" . implode(",", $subItemKeys) . ") AND menu_to_menu_item.is_deleted = 0 AND  menu_item.is_deleted = 0 ";
					$orderBy = "group by menu_item.id order by menu_to_menu_item.menu_order_value ASC ";

					$subItemInfo->query($select . $joins . $where . $orderBy);

					while ($subItemInfo->fetch())
					{
						$subqty = $array['sbi_' . $subItemInfo->id];
						if ($subqty)
						{
							$subItemInfo->parentItemId = $menuItemInfo->id;
							$subItemInfo->bundleItemCount = $subqty;
							$OrderObj->addMenuItem(clone($subItemInfo), $subqty);
						}
					}
				}

				if ($qty)
				{
					$OrderObj->addMenuItem(clone($menuItemInfo), $qty);
				}
			}
		}

		return $OrderObj;
	}

	function processDelayedPaymentAdjustment()
	{
		if (isset($_POST['PendingDP']) && is_numeric($_POST['PendingDP']) && ($_POST['PendingDPAmount'] < $_POST['OriginalPendingDPAmount'] || $_POST['payment1_type'] == 'DPADJUST'))
		{

			$orgAmount = $_POST['OriginalPendingDPAmount'];
			$adjAmount = $_POST['PendingDPAmount'];

			if (!empty($orgAmount) && !empty($adjAmount) && is_numeric($orgAmount) && is_numeric($adjAmount) && $orgAmount != $adjAmount && $adjAmount >= 0)
			{
				$paymentDAO = DAO_CFactory::create('payment');
				$paymentDAO->id = $_POST['PendingDP'];
				if (!$paymentDAO->find(true))
				{
					throw new Exception('Unexpected missing delayed payment while adjusting Delayed Payment in Order Editor');
				}

				$orgPayment = clone($paymentDAO);
				$paymentDAO->total_amount = $adjAmount;
				$paymentDAO->update($orgPayment);

				$description = "Pending Delayed Payment Adjustment- Org amount: $orgAmount New amount: $adjAmount";

				CStoreHistory::recordStoreEvent($this->originalOrder->user_id, $this->originalOrder->store_id, $this->originalOrder->id, 200, $orgPayment->id, 'null', 'null', $description);
			}
		}
	}
}

?>