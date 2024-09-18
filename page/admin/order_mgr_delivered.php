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
require_once('DAO/BusinessObject/CBox.php');
require_once("ValidationRules.inc");
require_once("CAppUtil.inc");

class

page_admin_order_mgr_delivered extends CPageAdminOnly
{

	const QUANTITY_PREFIX = 'qty_'; //entrees

	private $originalOrder = null;
	private $daoStore = null;
	private $daoMenu = null;
	private $User = null;
	private $Store_Credits_Total = 0;
	private $Store_Credit_Array = array();
	private $userIsPlatePointsGuest = false;
	private $storeSupportsPlatePoints = false;
	private $storeSupportsMembership = false;
	private $orderState = false;
	private $PlatePointsRulesVersion = 1.1;

	private $discountEligable = array(
		'limited_access' => false,
		'direct_order' => false,
		'dinner_dollars' => true,
		'coupon_code' => true,
		'preferred' => true,
		'session' => true,
	);

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

		if (empty($_REQUEST['order']) || !is_numeric($_REQUEST['order']))
		{
			// New orders must be provided a store id and a user id
			if (empty($_REQUEST['user']) || !is_numeric($_REQUEST['user']))
			{
				$tpl->setErrorMsg("There was a problem with the user ID specified.");
				CApp::bounce("/backoffice");
			}

			$this->originalOrder = new COrdersDelivered(true);
			$this->orderState = 'NEW';
			$this->originalOrder->user_id = CGPC::do_clean($_REQUEST['user'], TYPE_INT);
			$this->originalOrder->store_id = false; // store_id will be set once shipping address is confirmed
		}
		else
		{
			// Get the original order
			$this->originalOrder = new COrdersDelivered(true);
			$this->originalOrder->id = CGPC::do_clean($_REQUEST['order'], TYPE_INT);
			if (!$this->originalOrder->find_DAO_orders(true))
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
					$this->discountEligable['limited_access'] = true;
				}
				else
				{
					$tpl->setErrorMsg("There was a problem with the order ID specified.");
					CApp::bounce("/backoffice");
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

		//TODO:  Enforce following rule: Any store may place new order, only DC can edit.
		if (!CStore::userHasAccessToDistributionCenter($this->originalOrder->store_id, $this->orderState))
		{
			$tpl->setErrorMsg('You do not have access privileges for this order.');
			CApp::bounce('/backoffice');
		}

		$this->User = DAO_CFactory::create('user');
		$this->User->id = $this->originalOrder->user_id;
		if (!$this->User->find(true)) // what if user was deleted?
		{
			throw new Exception('No user associated with this order');
		}

		if (CPointsUserHistory::userIsActiveInProgram($this->User))
		{
			$this->originalOrder->is_in_plate_points_program = 1;
		}

		$Form = new CForm('order_mgr');
		$Form->Repost = true;
		$Form->Bootstrap = true;

		$this->User->getAddressBookArray(true);

		$addArray = array(
			'null' => 'Address Book'
		);

		foreach ($this->User->addressBook as $address)
		{
			$addArray[$address->id] = ((!empty($address->firstname) || !empty($address->lastname)) ? $address->firstname . ' ' . $address->lastname . ' - ' : $address->address_line1 . ', ') . $address->city . ', ' . $address->state_id . ' ' . $address->postal_code;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "address_book_select",
			CForm::options => $addArray
		));

		if ($this->orderState == 'NEW')
		{

			$dAddr = $this->User->getDeliveredAddressDefault();

			if (!empty($dAddr->id))
			{
				$Form->DefaultValues['shipping_firstname'] = $this->User->firstname;
				$Form->DefaultValues['shipping_lastname'] = $this->User->lastname;
				$Form->DefaultValues['shipping_phone_number'] = '';
				$Form->DefaultValues['shipping_address_line1'] = $dAddr->address_line1;
				$Form->DefaultValues['shipping_address_line2'] = $dAddr->address_line2;
				$Form->DefaultValues['shipping_city'] = $dAddr->city;
				$Form->DefaultValues['shipping_state_id'] = $dAddr->state_id;
				$Form->DefaultValues['shipping_postal_code'] = $dAddr->postal_code;
				$Form->DefaultValues['shipping_email_address'] = $dAddr->email_address;
				$Form->DefaultValues['shipping_is_gift'] = !empty($dAddr->is_gift) ? $dAddr->is_gift : null;
			}

			//set DC/Store
			if (!empty($dAddr->postal_code))
			{
				$zipcode = DAO_CFactory::create('zipcodes');
				$sql = "select distinct distribution_center from zipcodes where distribution_center IS NOT NULL AND zip = '{$dAddr->postal_code}' limit 1";
				$zipcode->query($sql);

				$zipcode->fetch();
				if ($zipcode->distribution_center != null)
				{
					$this->originalOrder->store_id = $zipcode->distribution_center;
				}
				else
				{
					CLog::RecordNew(CLog::ERROR, 'order_mgr_delivered: no DC found for zipcode = ' . $dAddr->postal_code);
				}
			}
		}
		else
		{
			$this->originalOrder->orderAddress();
		}

		$this->daoStore = $this->originalOrder->getStore();
		if (empty($this->daoStore) && $this->orderState != 'NEW')
		{
			throw new Exception('Store not found');
		}

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

		//--------------------START DD
		$orderIsEligibleForMembershipDiscount = false;
		$this->storeSupportsPlatePoints = CStore::storeSupportsPlatePoints($this->daoStore);
		$this->storeSupportsMembership = CStore::storeSupportsMembership($this->daoStore);

		if ($this->storeSupportsPlatePoints)
		{
			$order = false;
			if ($this->orderState == 'ACTIVE')
			{
				$order = $this->originalOrder;
			}

			$UserPlatePointsSummary = $this->User->getPlatePointsSummary($order);

			$tpl->assign('plate_points', $UserPlatePointsSummary);

			$this->userIsPlatePointsGuest = ($UserPlatePointsSummary['status'] == 'active');
		}

		$tpl->assign('userIsPlatePointsGuest', $this->userIsPlatePointsGuest);
		$tpl->assign('storeSupportsPlatePoints', $this->storeSupportsPlatePoints);

		$maxAvailablePPCredit = 0;
		if ($this->orderState != 'NEW' && $this->userIsPlatePointsGuest && $this->storeSupportsPlatePoints && $this->originalOrder->is_in_plate_points_program)
		{
			$tpl->assign('isPlatePointsOrder', true);

			$this->originalOrder->storeAndUserSupportPlatePoints = true;

			$maxAvailablePPCredit = CPointsCredits::getAvailableCreditForUserAndOrder($this->originalOrder->user_id, $this->originalOrder->id);
			$maxDeduction = 0;

			$tpl->assign('maxPPCredit', $maxAvailablePPCredit);
			$tpl->assign('maxPPDeduction', $maxDeduction);

			if ($maxAvailablePPCredit > 0 && empty($activeUP) && empty($originalUP))
			{

				if ($this->originalOrder->points_discount_total == 0)
				{
					$Form->DefaultValues['plate_points_discount'] = "";
				}
				else
				{
					$Form->DefaultValues['plate_points_discount'] = $this->originalOrder->points_discount_total;
				}

				$Form->AddElement(array(
					CForm::type => CForm::Money,
					CForm::name => 'plate_points_discount',
					CForm::org_value => $this->originalOrder->points_discount_total,
					CForm::onKeyUp => 'handlePlatePointsDiscount',
					CForm::onChange => 'handlePlatePointsDiscount',
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
			if (!$this->userIsPlatePointsGuest)
			{
				$tpl->assign('noPPReason', "The guest is not enrolled in Platepoints.");
			}
		}

		//--------------------END DD

		// TODO DELIVERED:   switch to boxes array
		// original quantities to be shown in parentheses next to input boxes
		$orgQuantities = array();
		$items = $this->originalOrder->getItems();
		$sessionIsInPast = false;

		if ($this->orderState != 'NEW')
		{
			$sessionIsInPast = $Session->isInThePast($this->daoStore);
			$tpl->assign('session_id', $Session->id);
			$tpl->assign('session_type', $Session->session_type);
		}

		$tpl->assign('CreditBasis', $this->originalOrder->grand_total - $this->originalOrder->subtotal_all_taxes);

		// cache store and user and menu objects
		$order_is_locked = false;

		/// check to see if editing period has expired
		if ($this->orderState == 'ACTIVE')
		{
			// TODO: new rules for Delivered Order editing cap
			$this->daoMenu = DAO_CFactory::create('menu');
			$this->daoMenu->id = $Session->menu_id;
			$this->daoMenu->find(true);

			if (!$this->daoMenu->areSessionsOrdersEditable($this->daoStore))
			{
				$order_is_locked = true;
				$this->discountEligable['limited_access'] = true;
			}
		}

		if (CUser::getCurrentUser()->id == 662598)
		{
			$order_is_locked = false;
			$this->discountEligable['limited_access'] = false;
		}

		// Only the DC can apply Direct Order discounts
		if ($this->CurrentBackOfficeStore->id == $this->originalOrder->store_id)
		{
			$this->discountEligable['direct_order'] = true;
		}

		$tpl->assign('order_is_locked', $order_is_locked);
		$tpl->assign('discountEligable', $this->discountEligable);

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

		// get original markup if any exists
		$markup = null;

		// -------------------------------------Set up menu
		if ($this->orderState != 'NEW')
		{
			$menu_id_of_existing_boxes = COrdersDelivered::getMenuIDBasedOnBundle($this->originalOrder->id);
			if ($menu_id_of_existing_boxes)
			{
				$menuInfo = CBox::getBoxArray($Session->store_id, false, false, false, false, $menu_id_of_existing_boxes);
			}
			else
			{
				$menuInfo = CBox::getBoxArray($Session->store_id, false, false);
			}

			$menuInfo['bundle_items'] = array();

			if (!empty($menuInfo['box']))
			{
				foreach ($menuInfo['box'] as &$thisBox)
				{
					if (!isset($thisBox->bundle))
					{
						$thisBox->bundle = array();
					}

					if (!empty($thisBox->box_bundle_1))
					{
						$thisBox->bundle[$thisBox->box_bundle_1] = $thisBox->box_bundle_1_obj;
						$items = $thisBox->box_bundle_1_obj->getBundleItems();
						$menuInfo['bundle_items'][$thisBox->box_bundle_1] = $items['bundle'];
					}
					if (!empty($thisBox->box_bundle_2))
					{
						$thisBox->bundle[$thisBox->box_bundle_2] = $thisBox->box_bundle_2_obj;
						$items = $thisBox->box_bundle_2_obj->getBundleItems();
						$menuInfo['bundle_items'][$thisBox->box_bundle_2] = $items['bundle'];
					}
				}
			}

			$inventoryArray = self::addDeliveredInventoryToMenuArray($menuInfo, $this->originalOrder->store_id, $this->orderState == 'ACTIVE');

			$tpl->assign('inventory_items', $inventoryArray);

			$currentBoxIDs = array();
			$currentBoxes = array();
			foreach ($this->originalOrder->getBoxes() as $box_inst_id => $box_data)
			{
				$box_instance = DAO_CFactory::create('box_instance');
				$box_instance->id = $box_inst_id;

				$currentBoxIDs[$box_inst_id] = $box_inst_id;
				$box_instance->find(true);

				$currentBoxes[$box_inst_id] = array();
				$currentBoxes[$box_inst_id]['box_instance'] = clone($box_instance);
				$currentBoxes[$box_inst_id]['box_info'] = clone($thisBox);
				$currentBoxes[$box_inst_id]['box_id'] = $box_instance->box_id;
				$currentBoxes[$box_inst_id]['box_type'] = $menuInfo['box'][$box_instance->box_id]->box_type;
				$currentBoxes[$box_inst_id]['box_label'] = $menuInfo['box'][$box_instance->box_id]->title;
				$currentBoxes[$box_inst_id]['bundle_data'] = $menuInfo['box'][$box_instance->box_id]->bundle[$box_instance->bundle_id];
				$currentBoxes[$box_inst_id]['bundle_items'] = array();
				foreach ($menuInfo['bundle_items'][$box_instance->bundle_id] as $id => $itemData)
				{
					$currentBoxes[$box_inst_id]['bundle_items'][$id] = array('id' => $id);
					$currentBoxes[$box_inst_id]['bundle_items'][$id]['display_title'] = $itemData['display_title'];
					$currentBoxes[$box_inst_id]['bundle_items'][$id]['qty'] = (!empty($box_data['items'][$id][0]) ? $box_data['items'][$id][0] : 0);
					$currentBoxes[$box_inst_id]['bundle_items'][$id]['recipe_id'] = $itemData['recipe_id'];
					$currentBoxes[$box_inst_id]['bundle_items'][$id]['servings_per_item'] = $itemData['servings_per_item'];
				}
			}

			// TODO : adjust inventory down by this order if order_State = SAVED

			// restore saved or current boxes
			$tpl->assign('current_boxes', $currentBoxes);
			$tpl->assign('current_box_ids', $currentBoxIDs);

			$tpl->assign('inventory_map', $menuInfo['inventory_map']);
		}

		// ------------------------ set up Discounts

		// ------Direct Discount
		$Form->AddElement(array(
			CForm::type => CForm::Money,
			CForm::org_value => $this->originalOrder->direct_order_discount,
			CForm::onKeyUp => 'calculateTotal',
			CForm::default_value => $this->originalOrder->direct_order_discount,
			CForm::name => 'direct_order_discount',
		));

		// ------Coupon Discount
		$coupon = $this->originalOrder->getCoupon();
		if ($coupon)
		{
			$tpl->assign('couponVal', $this->originalOrder->coupon_code_discount_total);
			$tpl->assign('couponDiscountMethod', $coupon->discount_method);
			$tpl->assign('couponDiscountVar', $coupon->discount_var);
			$tpl->assign('couponlimitedToFT', ($coupon->limit_to_finishing_touch ? true : false));
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
			CForm::size => 20,
			CForm::maxlength => 36,
			CForm::name => 'coupon_code'
		));

		if (!empty($this->originalOrder->orderAddress->id))
		{
			$Form->DefaultValues['shipping_firstname'] = $this->originalOrder->orderAddress->firstname;
			$Form->DefaultValues['shipping_lastname'] = $this->originalOrder->orderAddress->lastname;
			$Form->DefaultValues['shipping_phone_number'] = $this->originalOrder->orderAddress->telephone_1;
			$Form->DefaultValues['shipping_phone_number_new'] = $this->originalOrder->orderAddress->telephone_1;
			$Form->DefaultValues['shipping_address_line1'] = $this->originalOrder->orderAddress->address_line1;
			$Form->DefaultValues['shipping_address_line2'] = $this->originalOrder->orderAddress->address_line2;
			$Form->DefaultValues['shipping_city'] = $this->originalOrder->orderAddress->city;
			$Form->DefaultValues['shipping_state_id'] = $this->originalOrder->orderAddress->state_id;
			$Form->DefaultValues['shipping_postal_code'] = $this->originalOrder->orderAddress->postal_code;
			$Form->DefaultValues['shipping_email_address'] = $this->originalOrder->orderAddress->email_address;
			$Form->DefaultValues['shipping_is_gift'] = $this->originalOrder->orderAddress->is_gift;
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_firstname",
			CForm::org_value => !empty($this->originalOrder->orderAddress->firstname) ? $this->originalOrder->orderAddress->firstname : "",
			CForm::required => true,
			CForm::placeholder => "*First name",
			CForm::css_class => 'delivery-input',
			CForm::required_msg => "Please enter a first name.",
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_lastname",
			CForm::org_value => !empty($this->originalOrder->orderAddress->lastname) ? $this->originalOrder->orderAddress->lastname : "",
			CForm::required => true,
			CForm::placeholder => "*Last name",
			CForm::css_class => 'delivery-input',
			CForm::required_msg => "Please enter a last name.",
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_address_line1",
			CForm::org_value => !empty($this->originalOrder->orderAddress->address_line1) ? $this->originalOrder->orderAddress->address_line1 : "",
			CForm::required => true,
			CForm::placeholder => "*Street Address",
			CForm::required_msg => "Please enter a street address.",
			CForm::css_class => 'delivery-input',
			CForm::maxlength => 255,
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::placeholder => "Address 2",
			CForm::name => "shipping_address_line2",
			CForm::org_value => !empty($this->originalOrder->orderAddress->address_line2) ? $this->originalOrder->orderAddress->address_line2 : "",
			CForm::maxlength => 255,
			CForm::css_class => 'delivery-input',
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_city",
			CForm::org_value => !empty($this->originalOrder->orderAddress->city) ? $this->originalOrder->orderAddress->city : "",
			CForm::required => true,
			CForm::placeholder => "*City",
			CForm::required_msg => "Please enter a city.",
			CForm::maxlength => 64,
			CForm::css_class => 'delivery-input',
			CForm::size => 30,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'shipping_state_id',
			CForm::org_value => !empty($this->originalOrder->orderAddress->state_id) ? $this->originalOrder->orderAddress->state_id : "",
			CForm::required_msg => "Please select a state.",
			CForm::css_class => 'delivery-input',
			CForm::required => true,
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "shipping_postal_code",
			CForm::org_value => !empty($this->originalOrder->orderAddress->postal_code) ? $this->originalOrder->orderAddress->postal_code : "",
			CForm::required => true,
			CForm::css_class => 'delivery-input',
			CForm::placeholder => "*Postal Code",
			CForm::number => true,
			CForm::gpc_type => TYPE_POSTAL_CODE,
			CForm::xss_filter => true,
			CForm::maxlength => 5,
			CForm::required_msg => "Please enter a zip code."
		));

		$Form->AddElement(array(
			CForm::type => CForm::EMail,
			CForm::name => "shipping_email_address",
			CForm::placeholder => "Email",
			CForm::required => false,
			CForm::maxlength => 60,
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::name => "shipping_is_gift",
			CForm::required => false,
			CForm::label => '<span> This Order is a Gift</span>'
		));

		$userPhoneArray = array(
			'' => 'Contact Number',
			$this->User->telephone_1 => $this->User->telephone_1
		);

		if (!empty($this->User->telephone_2))
		{
			$userPhoneArray[$this->User->telephone_2] = $this->User->telephone_2;
		}

		$userPhoneArray['new'] = 'New phone number';

		if (!empty($this->originalOrder->orderAddress->telephone_1) && !array_key_exists($this->originalOrder->orderAddress->telephone_1, $userPhoneArray))
		{
			$shipping_phone_number_org_value = 'new';
			$Form->DefaultValues['shipping_phone_number'] = 'new';
		}
		else
		{
			$shipping_phone_number_org_value = !empty($this->originalOrder->orderAddress->telephone_1) ? $this->originalOrder->orderAddress->telephone_1 : "";
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'shipping_phone_number',
			CForm::org_value => $shipping_phone_number_org_value,
			CForm::options => $userPhoneArray,
			CForm::required_msg => "Please select a contact number.",
			CForm::css_class => 'delivery-input',
			//CForm::required => (($Session->isDelivery()) ? true : false),
			//CForm::dd_required => (($Session->isDelivery()) ? true : false),
			CForm::xss_filter => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Tel,
			CForm::name => 'shipping_phone_number_new',
			CForm::org_value => !empty($this->originalOrder->orderAddress->telephone_1) ? $this->originalOrder->orderAddress->telephone_1 : "",
			CForm::required_msg => "Please enter a contact number.",
			CForm::pattern => '([0-9\-]+){12}',
			CForm::placeholder => "*Contact number",
			CForm::css_class => 'delivery-input',
			CForm::required => false,
			CForm::xss_filter => true
		));

		$Form->DefaultValues['subtotal_delivery_fee'] = $this->originalOrder->subtotal_delivery_fee;

		// Delivery Fee
		$tpl->assign('orderOrStoreSupportDelivery', true);
		$Form->AddElement(array(
			CForm::type => CForm::Money,
			CForm::name => 'subtotal_delivery_fee',
			CForm::dd_required => false,
			CForm::org_value => $this->originalOrder->subtotal_delivery_fee,
			CForm::dd_type => 'item_field',
			CForm::onKeyUp => 'costInput',
			CForm::onChange => 'costInput'

		));

		// --------------------------------------- set up Taxes
		$tax_id = null;
		$curfoodTax = null;
		$curNonFoodTax = null;
		$curServiceTax = null;
		$curEnrollmentTax = null;
		$curDeliveryTax = null;
		$curBagFeeTax = null;
		if ($this->orderState != 'NEW')
		{
			list($tax_id, $curfoodTax, $curNonFoodTax, $curServiceTax, $curEnrollmentTax, $curDeliveryTax, $curBagFeeTax) = $this->daoStore->getCurrentSalesTax();
		}

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
			$origBagFeeTax = $origSalesTaxObj->other4_tax;

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
			$origBagFeeTax = 0;
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

		$tpl->assign('PlatePointsRulesVersion', false);

		$orderIsEligibleForMembershipDiscount = false;
		$this->storeSupportsMembership = CStore::storeSupportsMembership($this->daoStore);

		// TODO: review rules for membership discount
		if ($this->storeSupportsMembership && $this->orderState != 'NEW')
		{
			if ($this->orderState == 'ACTIVE' && !empty($this->originalOrder->membership_id) && is_numeric($this->originalOrder->membership_id))
			{
				$mid = $this->originalOrder->membership_id;
			}
			else
			{
				$mid = $this->User->getMembershipForMenu($this->originalOrder->findSession()->menu_id);
			}

			$tpl->assign('storeSupportsMembership', true);

			if ($mid)
			{
				$sessionOrderIsValid = true;
				if ($this->originalOrder->findSession()->session_type != CSession::STANDARD && $this->originalOrder->findSession()->session_type != CSession::SPECIAL_EVENT)
				{
					$sessionOrderIsValid = false;
				}

				$UserMembershipSummary = $this->User->getMembershipStatus(false, true, $mid);
				$tpl->assign('membership_status', $UserMembershipSummary);

				$menu_id = $this->originalOrder->findSession()->menu_id;
				if (isset($UserMembershipSummary['eligible_menus'][$menu_id]) && $sessionOrderIsValid)
				{
					$this->userIsPlatePointsGuest = true; // needed to allow Dinner Dollars
					$tpl->assign('orderIsEligibleForMembershipDiscount', true);
					$this->discountEligable['session'] = false;
					$orderIsEligibleForMembershipDiscount = true;
				}
				else
				{
					$tpl->assign('orderIsEligibleForMembershipDiscount', false);
				}
			}
			else
			{
				// No current membership but if order retains MID it must be part of a cancelled membership

				$tpl->assign('orderIsEligibleForMembershipDiscount', false);
				if ($this->User->hasCurrentMembership())
				{
					// even though this order was not in a membership check if they have a membership at all and if so
					// allow using Dinner Dollars. If this order was not a Plate Points order the code below will disqualify usage of DDs.
					$this->userIsPlatePointsGuest = true; // needed to allow Dinner Dollars
				}
			}
		}
		else
		{
			$tpl->assign('membership_status', false);
			$tpl->assign('storeSupportsMembership', false);
			$tpl->assign('orderIsEligibleForMembershipDiscount', false);
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

			$paymentArr = COrders::buildPaymentInfoArray($this->originalOrder->id, CUser::getCurrentUser()->user_type, $Session->session_start, false, true);

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
			$refPaymentTypeDataArray = CPayment::getPaymentsStoredForReference($this->originalOrder->user_id, $this->daoStore->id);

			// Always allow this payment type, it's especially useful when the merchant account has changed.
			//	if (!$addNewRefType)
			//	{
			$refPaymentTypeData = $refPaymentTypeDataArray;
			//	}

			if ($refPaymentTypeData)
			{

				$refPaymentTypeDataByRefID = array();

				foreach ($refPaymentTypeData as $cc_id => $pay_data)
				{
					$refPaymentTypeDataByRefID[$pay_data['payment_id']] = $pay_data;
				}

				$tpl->assign('refPaymentTypeData', $refPaymentTypeDataByRefID);
			}

			// Create forms to change delayed payment options if not SUCCESS
			// TODO: rule out Deplayed Payment for Delivered and Remove
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

			self::buildPaymentForm($tpl, $Form, 2, $this->daoStore, $this->orderState, $Session, $addNewRefType, $hasDelayedPayment, $refPaymentTypeData, $order_is_locked);

			$tpl->assign('store_specific_deposit', $this->daoStore->default_delayed_payment_deposit);

			if ($this->orderState != 'NEW')
			{
				$GiftCardPaymentTotal = $this->getCreditableGiftCardPaymentTotal();
			}
			else
			{
				$StoreCreditPaymentTotal = 0;
				$GiftCardPaymentTotal = 0;
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

			$originalGrandTotalMinusTaxes = $this->originalOrder->grand_total - $this->originalOrder->subtotal_all_taxes;

			if ($this->discountEligable['limited_access'])
			{
				try
				{
					//$this->processStoreCredits();

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

					COrdersDigest::recordEditedOrder($this->originalOrder, $originalGrandTotalMinusTaxes, $this->orderState == CBooking::CANCELLED);

					if (!isset($_POST['suppressEmail']))
					{
						COrders::sendEditedOrderConfirmationEmail($this->User, $this->originalOrder);
					}

					CApp::bounce("/backoffice/order-mgr-delivered?order=" . $this->originalOrder->id);
				}
				catch (Exception $e)
				{

					$Form->setCSRFToken();

					$tpl->setErrorMsg("(#e3) A problem occurred during order processing. <br />" . $e->getMessage());
					CLog::RecordException($e);
				}
			}
			else
			{
				// record original version in edited orders log
				$order_record = DAO_CFactory::create('edited_orders');
				$order_record->setFrom($this->originalOrder->toArray());
				$order_record->original_order_id = $this->originalOrder->id;

				$order_record->order_revisions = $_POST['changeList'];
				$order_record->order_revision_notes = CGPC::do_clean($_POST['changeListStr'], TYPE_STR);

				//Direct Order Discount
				$this->originalOrder->direct_order_discount = CGPC::do_clean($_POST['direct_order_discount'], TYPE_NUM);

				// Coupon
				//TODO: Rework Coupon Rules for Delivered
				$this->originalOrder->coupon_code_id = CGPC::do_clean($_POST['coupon_id'], TYPE_INT);

				$coupon_free_menu_item = (isset($_POST['free_menu_item_coupon']) ? $_POST['free_menu_item_coupon'] : null);

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

				if (isset($_POST['subtotal_delivery_fee']))
				{
					if (empty($_POST['subtotal_delivery_fee']) || !is_numeric($_POST['subtotal_delivery_fee']))
					{
						$this->originalOrder->subtotal_delivery_fee = 0;
					}
					else
					{
						$this->originalOrder->subtotal_delivery_fee = $_POST['subtotal_delivery_fee'];
					}
				}

				// remember state so that we can restore some critical values if an exception occurs
				$originalOrderPriorToUpdate = clone($this->originalOrder);

				// Ready to commit everything
				$this->originalOrder->query('START TRANSACTION;');

				$parentStoreId = $this->daoStore->parent_store_id;

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

					$order_record->insert();

					$tempStoreID = $this->originalOrder->store_id;

					// delete original order items after moving them to edited_order_items
					//TODO: Rework item handlling for Delivered
					$order_item = DAO_CFactory::create('order_item');
					$order_item->query("SELECT oi.*, mi.servings_per_item, mi.is_chef_touched, mi.recipe_id FROM order_item oi " . " JOIN menu_item mi ON mi.id = oi.menu_item_id WHERE oi.order_id = " . $this->originalOrder->id . " AND oi.is_deleted = 0");

					$boxInstanceArray = array();
					while ($order_item->fetch())
					{
						/*
						$edited_item = DAO_CFactory::create('edited_order_item');
						$edited_item->setFrom($order_item->toArray());
						$edited_item->insert();
						*/

						try
						{
							$servingQty = $order_item->servings_per_item;

							if ($servingQty == 0)
							{
								$servingQty = 6;
							}

							if ($order_item->is_chef_touched)
							{
								$servingQty = 1;
							}

							$servingQty *= $order_item->item_count;

							if (!in_array($order_item->box_instance_id, $boxInstanceArray))
							{
								$boxInstanceArray[] = $order_item->box_instance_id;
							}

							// INVENTORY TOUCH POINT 17
							//subtract from inventory
							$invItem = DAO_CFactory::create('menu_item_inventory');
							$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold - $servingQty where mii.recipe_id = {$order_item->recipe_id} and mii.store_id = $parentStoreId and mii.menu_id = {$this->originalOrder->getMenuId()} and mii.is_deleted = 0");
						}
						catch (exception $exc)
						{
							// don't allow a problem here to fail the order
							//log the problem
							CLog::RecordException($exc);

							$debugStr = "INV_CONTROL ISSUE- Edited Order subtract deleted items: " . $order_item->order_id . " | Item: " . $order_item->menu_item_id . " | Store: " . $tempStoreID . " | Parent Store: " . $parentStoreId;
							CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
						}

						$adminUser = CUser::getCurrentUser()->id;
						$order_item->query("update order_item set is_deleted = 1, edit_sequence_id = {$order_record->id}, updated_by = $adminUser where id = {$order_item->id}");
					}

					// now delete the original box_instances ... new ones will be created
					foreach ($boxInstanceArray as $box_inst_id)
					{
						$DAO_box_instance = DAO_CFactory::create('box_instance', true);
						$DAO_box_instance->id = $box_inst_id;
						$DAO_box_instance->delete();
					}

					// addons are processed separately and must be added into the subtotals when displaying the confirmation
					$newAddonQty = 0;
					$newAddonAmount = 0;

					// record changes to the master
					self::addMenuItemsToOrder($this->originalOrder);

					if (!$this->originalOrder->verifyAdequateInventory())
					{

						$itemsOversold = $this->originalOrder->getInvExceptionItemsString();

						$tpl->setErrorMsg('Inventory has changed since the order was started and an item has run out of stock. Please review the order and try again. Items adjusted are:<br />' . $itemsOversold);
						throw new Exception("INV_EXC");
					}

					$this->originalOrder->refreshForEditing($Session->menu_id);
					$this->originalOrder->insertEditedItems($sessionIsInPast);

					$this->originalOrder->recalculate(true); // true = editing

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
							$tpl->assign('couponIsValidWithPlatePoints', ($coupon->valid_with_plate_points_credits ? true : false));
						}
						else
						{
							$tpl->assign('couponIsValidWithPlatePoints', true);
						}
					}

					$this->originalOrder->update();

					if (empty($pp_credit_adjust_summary))
					{
						$pp_credit_adjust_summary = false;
					}
					CPointsUserHistory::handleEvent($this->originalOrder->user_id, CPointsUserHistory::ORDER_EDITED, false, $this->originalOrder);

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

					$shipping_phone_number = $Form->value('shipping_phone_number');
					$shipping_phone_number_new = $Form->value('shipping_phone_number_new');

					// TODO: Is front end validation adequate? Otherwise validate this prior to all the payment and database work
					$shipping_email_address = $Form->value('shipping_email_address');

					$this->originalOrder->orderAddress->firstname = $Form->value('shipping_firstname');
					$this->originalOrder->orderAddress->lastname = $Form->value('shipping_lastname');
					$this->originalOrder->orderAddress->address_line1 = $Form->value('shipping_address_line1');
					$this->originalOrder->orderAddress->address_line2 = $Form->value('shipping_address_line2');
					$this->originalOrder->orderAddress->city = $Form->value('shipping_city');
					$this->originalOrder->orderAddress->state_id = $Form->value('shipping_state_id');
					$this->originalOrder->orderAddress->postal_code = $Form->value('shipping_postal_code');
					$this->originalOrder->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
					$this->originalOrder->orderAddress->email_address = trim(strip_tags($shipping_email_address));
					$is_gift = $Form->value('shipping_is_gift');
					$this->originalOrder->orderAddress->is_gift = !empty($is_gift);

					$this->originalOrder->orderAddressDeliveryProcessUpdate();

					$this->originalOrder->query('COMMIT;');
					COrdersDigest::recordEditedOrder($this->originalOrder, $originalGrandTotalMinusTaxes);

					$tpl->assign('orderEditSuccess', true);
					$tpl->assign('newAddonQty', $newAddonQty);
					$tpl->assign('newAddonAmount', $newAddonAmount);

					$this->originalOrder->timestamp_updated = date("Y-m-d H:i:s");

					if (!isset($_POST['suppressEmail']))
					{
						COrdersDelivered::sendEditedOrderConfirmationEmail($this->User, $this->originalOrder);
					}

					CApp::bounce("/backoffice/order-mgr-thankyou?order=" . $this->originalOrder->id);
				}
				catch (Exception $e)
				{
					$this->originalOrder->query('ROLLBACK;');
					$Form->setCSRFToken();

					$tpl->setErrorMsg("(#e4) A problem occurred during order processing. <br />" . $e->getMessage());
					CLog::RecordException($e);
				}
			}
		} // end processing
		else
		{
			$Form->setCSRFToken();
		}

		finish_processing:

		// Build template arrays for display
		if ($this->orderState != 'NEW')
		{
			$this->getShippingDetails($Session, $this->originalOrder->orderAddress->postal_code);

			$this->originalOrder->refreshForEditing($Session->menu_id);

			$this->originalOrder->recalculate(true);
			$OrderDetailsArray = COrders::buildOrderDetailArrays($this->User, $this->originalOrder, CUser::getCurrentUser()->user_type, false, false, false, true);
		}
		else
		{
			$OrderDetailsArray = array(
				'orderInfo' => array('grand_total' => 0),
				'paymentInfo' => array(),
				'sessionInfo' => array()
			);
		}

		$this->assignRefundsToPayments($OrderDetailsArray['paymentInfo']);

		$tpl->assign('customerName', $this->User->getName());
		$tpl->assign('user', $this->User->toArray());
		$tpl->assign('orderInfo', $OrderDetailsArray['orderInfo']);
		$tpl->assign('paymentInfo', $OrderDetailsArray['paymentInfo']);
		$tpl->assign('sessionInfo', $OrderDetailsArray['sessionInfo']);
		$tpl->assign('DAO_session', $OrderDetailsArray['DAO_session']);

		if ($this->orderState != 'NEW')
		{
			$Store = DAO_CFactory::create('store');
			$Store->id = $Session->store_id;
			$Store->find(true);
			$storeInfo = $Store->toArray();
			$storeInfo['PHPTimeZone'] = CTimezones::getPHPTimeZoneFromID($Store->timezone_id);

			$tpl->assign('storeInfo', $storeInfo);
		}

		$Form->AddElement(array(
			CForm::type => CForm::CheckBox,
			CForm::onChange => 'handleAutoAdjust',
			CForm::name => 'autoAdjust',
			CForm::label => '<span id="OEH_auto_pay_msg"></span>'
		));

		if ($this->orderState != 'NEW')
		{
			$tpl->assign('menuInfo', $menuInfo);
		}

		$tpl->assign('form_direct_order', $Form->render());
		$tpl->assign('orgQuantities', $orgQuantities);
	}

	private function getShippingDetails($SessionObj, $destZipCode)
	{

		if (empty($this->originalOrder->orderShipping()->shipping_postal_code))
		{
			return $this->originalOrder->defaultShippingInfo($destZipCode, $SessionObj, true);
		}

		return DAO::getCompressedArrayFromDAO($this->originalOrder->orderShipping(), true, true);
	}

	private function getOriginalPrices($order_id)
	{
		$retVal = array();
		$order_items = DAO_CFactory::create('order_item');
		$order_items->order_id = $order_id;

		$order_items->find();

		while ($order_items->fetch())
		{
			if (!empty($order_items->discounted_subtotal))
			{
				$retVal[$order_items->menu_item_id] = $order_items->discounted_subtotal / $order_items->item_count;
			}
			else
			{
				$retVal[$order_items->menu_item_id] = $order_items->sub_total / $order_items->item_count;
			}
		}

		return $retVal;
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
						'trans_id' => ((!empty($arrItem['payment_transaction_id'])) ? $arrItem['payment_transaction_id']['other'] : null),
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

					if ($payment['is_delayed_payment'])
					{
						$paymentTransID = $paymentInfo[$payment['payment_ord_num']]['delayed_tran_num']['other'];
					}
					else
					{
						$paymentTransID = $paymentInfo[$payment['payment_ord_num']]['payment_transaction_number']['other'];
					}

					if ($refTransArray)
					{
						$refTransArray[$paymentTransID]['current_refunded_amount'] = CTemplate::moneyFormat($payment['current_refunded_amount']);
					}
				}
			}
		}
	}

	public static function buildPaymentForm($tpl, $Form, $numPayments, $Store, $order_state, $sessionObj = false, $addRefType = true, $addDPAdjustType = false, $refPaymentTypeData = false, $order_is_locked = false)
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

			$Form->addElement(array(
				CForm::type => CForm::CheckBox,
				CForm::name => "save_cc_as_ref_" . $i,
				CForm::label => 'Save this card for faster checkout',
				CForm::checked => false
			));

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
			$ref_number = CGPC::do_clean($ref_number, TYPE_STR);
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

	function processCash($paymentNumber, $newPayment)
	{
		$newPayment->payment_type = CPayment::CASH;

		if ($paymentNumber == 1)
		{
			$newPayment->total_amount = CGPC::do_clean($_POST['payment1_cash_total_amount'], TYPE_NUM);
		}
		else
		{
			$newPayment->total_amount = CGPC::do_clean($_POST['payment2_cash_total_amount'], TYPE_NUM);
		}

		$newPayment->insert();
	}


	// This was for the old style Gift Card that was converted to store credit when redeemed
	// BUT NOW - handles all returns of Store Credit

	function processRefundCash($newPayment)
	{
		$newPayment->payment_type = CPayment::REFUND_CASH;

		$newPayment->total_amount = CGPC::do_clean($_POST['payment1_refund_cash_total_amount'], TYPE_NUM);
		$newPayment->payment_number = CGPC::do_clean($_POST['payment1_refund_cash_payment_number'], TYPE_STR);

		$newPayment->insert();
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
			$newPayment->total_amount = CGPC::do_clean($_POST['payment1_cc_total_amount'], TYPE_NUM);
		}
		else
		{
			$newPayment->total_amount = CGPC::do_clean($_POST['payment2_cc_total_amount'], TYPE_NUM);
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
					$addr = CGPC::do_clean($_POST['billing_address_1'], TYPE_STR);
				}

				if (isset($_POST['billing_postal_code_1']) && !empty($_POST['billing_postal_code_1']))
				{
					$zip = CGPC::do_clean($_POST['billing_postal_code_1'], TYPE_STR);
				}
			}
			else
			{
				if (isset($_POST['billing_address_2']) && !empty($_POST['billing_address_2']))
				{
					$addr = CGPC::do_clean($_POST['billing_address_2'], TYPE_STR);
				}

				if (isset($_POST['billing_postal_code_2']) && !empty($_POST['billing_postal_code_2']))
				{
					$zip = CGPC::do_clean($_POST['billing_postal_code_2'], TYPE_STR);
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

		if ($paymentNumber == 1)
		{
			if (isset($_POST['save_cc_as_ref_1']))
			{
				$newPayment->save_card_on_completion = true;
			}
		}
		else
		{
			if (isset($_POST['save_cc_as_ref_2']))
			{
				$newPayment->save_card_on_completion = true;
			}
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

	function processCheck($paymentNumber, $newPayment)
	{
		$newPayment->payment_type = CPayment::CHECK;
		if ($paymentNumber == 1)
		{
			$newPayment->total_amount = CGPC::do_clean($_POST['payment1_check_total_amount'], TYPE_NUM);
			$newPayment->payment_number = CGPC::do_clean($_POST['payment1_check_payment_number'], TYPE_STR);
		}
		else
		{
			$newPayment->total_amount = CGPC::do_clean($_POST['payment2_check_total_amount'], TYPE_NUM);
			$newPayment->payment_number = CGPC::do_clean($_POST['payment2_check_payment_number'], TYPE_STR);
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

		$newPayment->total_amount = CGPC::do_clean($_POST['debit_gift_card_amount'], TYPE_NUM);
		$newPayment->payment_number = CGPC::do_clean($_POST['debit_gift_card_number'], TYPE_NUM);
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
			$newPayment->total_amount = CGPC::do_clean($_POST['payment1_ref_total_amount'], TYPE_NUM);
		}
		else
		{
			$newPayment->total_amount = CGPC::do_clean($_POST['payment2_ref_total_amount'], TYPE_NUM);
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
			$this->refundCash(CGPC::do_clean($_POST['credit_to_customer_refund_cash_amount'], TYPE_NUM), CGPC::do_clean($_POST['credit_to_customer_refund_cash_number'], TYPE_STR));
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
			$this->CreditStoreCreditPayments(CGPC::do_clean($_POST['storeCreditRefund'], TYPE_NUM));
		}

		if (isset($_POST['giftCardRefund']) && $_POST['giftCardRefund'] > 0)
		{
			$this->CreditGiftCardPayments(CGPC::do_clean($_POST['giftCardRefund'], TYPE_NUM));
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
				$newPayment->recordRevenueEvent($this->originalOrder->findSession(), true);
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
			$retVal[$storeCredit->id] = clone($storeCredit);
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

	function addMenuItemsToOrder($OrderObj)
	{

		$items = array();

		foreach ($_POST as $k => $v)
		{
			if (strpos($k, "qty_") === 0)
			{
				list ($pfx, $biid, $miid) = explode("_", $k);
				if (!isset($items[$biid]))
				{
					$items[$biid] = array();
				}

				$items[$biid][$miid] = $v;
			}
		}

		//get menu from session
		$menu_id = $OrderObj->findSession()->menu_id;

		//clear existing menu items
		$OrderObj->clearBoxesUnsafe();

		$totalItemQty = 0;
		//add menu items
		foreach ($items as $boxInstanceID => $boxData)
		{
			$boxInstanceObj = DAO_CFactory::create('box_instance');
			$boxInstanceObj->query("SELECT
					bi.*,
					b.title,
					b.description, 
					b.css_icon,
					b.box_type
					FROM box_instance AS bi
					JOIN box AS b ON b.id = bi.box_id
					WHERE bi.id = '" . $boxInstanceID . "'");
			$boxInstanceObj->fetch();

			foreach ($boxData as $id => $qty)
			{
				if (is_numeric($qty) && $qty > 0 && is_numeric($id))
				{
					$MenuItem = DAO_CFactory::create('menu_item');
					$MenuItem->query("SELECT
										mi.*
										FROM
										menu_item AS mi
										WHERE mi.id = $id
										AND mi.is_deleted = 0");

					if (!$MenuItem->fetch())
					{
						throw new Exception("Menu item not found: " . $id);
					}
					else
					{
						$totalItemQty += $qty;
						$OrderObj->addItemToBox($boxInstanceObj, $MenuItem, $qty);
					}
				}
			}
		}

		return $OrderObj;
	}

	function processDelayedPaymentAdjustment()
	{
		if (isset($_POST['PendingDP']) && is_numeric($_POST['PendingDP']) && ($_POST['PendingDPAmount'] < $_POST['OriginalPendingDPAmount'] || $_POST['payment1_type'] == 'DPADJUST'))
		{

			$orgAmount = CGPC::do_clean($_POST['OriginalPendingDPAmount'], TYPE_NUM);
			$adjAmount = CGPC::do_clean($_POST['PendingDPAmount'], TYPE_NUM);

			if (!empty($orgAmount) && !empty($adjAmount) && is_numeric($orgAmount) && is_numeric($adjAmount) && $orgAmount != $adjAmount && $adjAmount >= 0)
			{
				$paymentDAO = DAO_CFactory::create('payment');
				$paymentDAO->id = CGPC::do_clean($_POST['PendingDP'], TYPE_INT);
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

	static function addDeliveredInventoryToMenuArray(&$inArray, $store_id, $orderIsACTIVE)
	{
		// note: unless order is active the intial inventory has not been reduced by the current items

		$parent_store_id = CStore::getParentStoreID($store_id);
		// form unique list of recipes
		$inventoryArray = array(); // recipe_id -> remaining_inventory
		foreach ($inArray['bundle_items'] as $items)
		{
			if(!is_null($items))
			{
				foreach ($items as $item)
				{
					$inventoryArray[$item['recipe_id']] = 0;
				}
			}

		}

		$menu_id = false;
		if (!empty($inArray['info']['menu_id']))
		{
			$menu_id = $inArray['info']['menu_id'];
		}
		else
		{
			if (array_key_exists("box", $inArray) && is_array($inArray["box"]))
			{
				reset($inArray['box']);
				$anyBox = current($inArray['box']);
				$anyBundle = current($anyBox->bundle);
				$menu_id = $anyBundle->menu_id;
			}
		}

		$recipeList = implode(",", array_keys($inventoryArray));

		$ItemInvObj = new DAO();
		if (!empty($recipeList))
		{
			$ItemInvObj->query("select recipe_id, override_inventory, number_sold from menu_item_inventory where store_id = $parent_store_id and menu_id = $menu_id and recipe_id in (" . $recipeList . ") and is_deleted = 0");
		}

		while ($ItemInvObj->fetch())
		{
			if ($orderIsACTIVE)
			{
				$inventoryArray[$ItemInvObj->recipe_id] = $ItemInvObj->override_inventory - $ItemInvObj->number_sold;
			}
			else
			{
				//if order is not active then it's items are not yet permanentyl removed from  - subtract that as well
				$thisOrdersUsage = 0;
				$inventoryArray[$ItemInvObj->recipe_id] = $ItemInvObj->override_inventory - ($ItemInvObj->number_sold + 0);
			}
		}

		$inArray['inventory_map'] = $inventoryArray;
		if (array_key_exists("box", $inArray))
		{
			foreach ($inArray['box'] as $box_id => $box_data)
			{
				if ($box_data->box_type == CBox::DELIVERED_FIXED)
				{
					if (!empty($box_data->box_bundle_1))
					{
						foreach ($inArray['bundle_items'][$box_data->box_bundle_1] as $item)
						{
							if ($inventoryArray[$item['recipe_id']] < $item['servings_per_item'])
							{
								$inArray['box'][$box_id]->bundle1_out_of_stock = true;
								break;  // 1 item out of stock makes the entire fixed box out of stock
							}
						}
					}

					if (!empty($box_data->box_bundle_2))
					{
						foreach ($inArray['bundle_items'][$box_data->box_bundle_2] as $item)
						{
							if ($inventoryArray[$item['recipe_id']] < $item['servings_per_item'])
							{
								$inArray['box'][$box_id]->bundle2_out_of_stock = true;
								break;  // 1 item out of stock makes the entire fixed box out of stock
							}
						}
					}
				}
				else
				{

					$count1itemORMoreEntrees = 0;
					$count2itemORMoreEntrees = 0;

					// For a custom bundle to not be out of stock there must be 2 items with at least 2 items left in inv or
					// 4 items with at least 1 item inventory or 1 item with at least 2 inv and 2  other items with at least 1 item inv

					if (!empty($box_data->box_bundle_1))
					{
						foreach ($inArray['bundle_items'][$box_data->box_bundle_1] as $miid => $item)
						{
							if ($item['servings_per_item'] > $inventoryArray[$item['recipe_id']])
							{
								$inArray['bundle_items'][$box_data->box_bundle_1][$miid]["out_of_stock"] = true;
							}
							else
							{
								if ($inventoryArray[$item['recipe_id']] >= $item['servings_per_item'])
								{
									$count1itemORMoreEntrees++;
								}

								if ($inventoryArray[$item['recipe_id']] >= ($item['servings_per_item'] * 2))
								{
									$count2itemORMoreEntrees++;
								}
							}
						}
					}

					// note: $count1itemORMoreEntrees includes thoses with more than 2 - that's why "$count1itemORMoreEntrees > 3"
					if (!($count2itemORMoreEntrees >= 2 || $count1itemORMoreEntrees >= 4 || ($count2itemORMoreEntrees == 1 && $count1itemORMoreEntrees > 3)))
					{
						$inArray['box'][$box_id]->bundle1_out_of_stock = true;
					}

					$count1itemORMoreEntrees = 0;
					$count2itemORMoreEntrees = 0;

					if (!empty($box_data->box_bundle_2))
					{
						foreach ($inArray['bundle_items'][$box_data->box_bundle_2] as $miid => $item)
						{
							if ($item['servings_per_item'] > $inventoryArray[$item['recipe_id']])
							{
								$inArray['bundle_items'][$box_data->box_bundle_2][$miid]["out_of_stock"] = true;
							}
							else
							{
								if ($inventoryArray[$item['recipe_id']] >= $item['servings_per_item'])
								{
									$count1itemORMoreEntrees++;
								}

								if ($inventoryArray[$item['recipe_id']] >= ($item['servings_per_item'] * 2))
								{
									$count2itemORMoreEntrees++;
								}
							}
						}
					}

					if (!($count2itemORMoreEntrees >= 2 || $count1itemORMoreEntrees >= 4 || ($count2itemORMoreEntrees == 1 && $count1itemORMoreEntrees > 3)))
					{
						$inArray['box'][$box_id]->bundle2_out_of_stock = true;
					}
				}
			}
		}

		return $inventoryArray;
	}

}