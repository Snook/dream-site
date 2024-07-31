<?php
require_once 'includes/DAO/BusinessObject/COrders.php';
require_once 'includes/DAO/BusinessObject/CBox.php';
require_once 'includes/DAO/BusinessObject/CBoxInstance.php';
require_once 'includes/DAO/BusinessObject/CBundle.php';

require_once 'includes/api/shipping/shipstation/ShipStationManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationOrderWrapper.php';
require_once 'includes/api/shipping/shipstation/ShipStationRateWrapper.php';

require_once 'includes/CEditOrderPaymentManager.inc';

class COrdersDelivered extends COrders
{
	private $boxes = array();
	public $orderShipping = null;

	protected function calculateBasePrice()
	{
		$this->subtotal_menu_items = 0;
		$this->servings_total_count = 0;
		$this->servings_core_total_count = 0;
		$this->menu_items_total_count = 0;
		$this->subtotal_products = 0;
		$this->product_items_total_count = 0;
		$this->pcal_preassembled_total_count = 0;
		$this->pcal_sidedish_total_count = 0;

		$totalPrice = 0;
		$totalQty = 0;
		$servingsCount = 0;
		$coreServingsCount = 0;
		$accumulatedDiscount = 0;

		if (!empty($this->boxes))
		{
			foreach ($this->boxes as $thisBox)
			{
				if ($thisBox['box_instance']->is_complete)
				{

					$box_price = $thisBox['bundle']->price;
					$box_prediscount_price = 0;
					foreach ($thisBox['items'] as $item)
					{
						list($qty, $mi_obj) = $item;
						$totalPrice += $qty * $mi_obj->price;
						$box_prediscount_price += $qty * $mi_obj->price;
						$totalQty += $qty;

						// if the order is delivered and it's a side, use the servings_per_container_display to calculate total serving average
						if (isset($mi_obj->servings_per_item) && $mi_obj->servings_per_item == 1 && !empty($mi_obj->servings_per_container_display))
						{
							$servingThisItem = $qty * $mi_obj->servings_per_container_display;
						}
						else if (isset($mi_obj->servings_per_item))
						{
							$servingThisItem = $qty * $mi_obj->servings_per_item;
						}
						else
						{
							$servingThisItem = $mi_obj->pricing_type == CMenuItem::HALF ? $qty * 3 : $qty * 6;
						}

						$this->pcal_preassembled_total_count += $qty;
						$servingsCount += $servingThisItem;
						$coreServingsCount += $servingThisItem;
					}

					$accumulatedDiscount += ($box_prediscount_price - $box_price);
				}
			}
		}

		// products
		$product_total = 0;
		$product_qty = 0;
		if ($this->products)
		{
			foreach ($this->products as $item)
			{
				list($qty, $mi_obj) = $item;
				$product_total += $qty * $mi_obj->price;
				$product_qty += $qty;
			}
		}

		$this->pcal_preassembled_total = $totalPrice;
		$this->subtotal_products = $product_total;
		$this->product_items_total_count = $product_qty;
		$this->subtotal_menu_items = $totalPrice;
		$this->menu_items_total_count = $totalQty;
		$this->servings_total_count = $servingsCount;
		$this->servings_core_total_count = $coreServingsCount;

		$this->bundle_discount = $accumulatedDiscount;

		return $this->subtotal_menu_items;
	}

	/**
	 * @throws Exception
	 */
	public function defaultShippingInfo($destZipCode, $SessionObj, $save = false)
	{
		$zipcode = new DAO();
		$zipcode->query("select distribution_center, service_days from zipcodes where distribution_center IS NOT NULL AND zip = '$destZipCode' limit 1");
		$zipcode->fetch();

		if (empty($zipcode->distribution_center))
		{
			return false;
		}

		//TODO: adjust date for blackouts (carrier and store)
		$shippingDate = new DateTime($SessionObj->session_start);
		$shippingDate->modify("- {$zipcode->service_days} day");

		$this->orderShipping();
		$this->orderShipping->order_id = $this->id;
		$this->orderShipping->status = 'NEW';
		$this->orderShipping->shipping_method = 0;
		$this->orderShipping->carrier_code = 'UPS';
		$this->orderShipping->distribution_center = $zipcode->distribution_center;
		$this->orderShipping->service_days = $zipcode->service_days;
		$this->orderShipping->shipping_postal_code = $destZipCode;
		$this->orderShipping->ship_date = $shippingDate->format("Y-m-d");
		$this->orderShipping->requested_delivery_date = $SessionObj->session_start;
		$this->orderShipping->actual_delivery_date = $SessionObj->session_start;

		$this->orderShipping->weight = 0.0;
		$this->orderShipping->shipping_cost = 0.0;
		$this->orderShipping->shipping_tax = 0.0;

		$this->orderShipping->tracking_number = 'null';
		$this->orderShipping->tracking_number_received = '1970-01-01 00:00:01';

		if ($save)
		{
			if (empty($this->orderShipping->id))
			{
				$this->orderShipping->insert();
			}
			else
			{
				$this->orderShipping->update();
			}
		}

		return $this->orderShipping;
	}

	/**
	 * Call this to recalculate all totals and apply adjustments
	 */
	function recalculate($editing = false, $suppressSessionDiscount = false, $rescheduling = false, $userIsOnHold = false)
	{
		$this->PlatePointsRulesVersion = 3;

		$this->type_of_order = COrders::STANDARD;

		$this->subtotal_all_taxes = 0;
		$this->subtotal_all_items = 0;
		$this->volume_discount_total = 0;
		$this->grand_total = 0;
		$this->bundle_discount = 0;

		$this->calculateBasePrice();
		if ($this->bundle_discount < 0)
		{
			// The total cost of the items is less than the bundle costs
			// Set the markup equal to  the difference and the bundle discount to zero
			$this->subtotal_home_store_markup = $this->bundle_discount * -1;
			$this->bundle_discount = 0;
		}

		$this->calculateOtherTotals();

		if (!isset($this->misc_food_subtotal))
		{
			$this->misc_food_subtotal = 0;
		}
		if (!isset($this->misc_nonfood_subtotal))
		{
			$this->misc_nonfood_subtotal = 0;
		}

		$hasServiceFeeCoupon = false;
		$hasDeliveryFeeCoupon = false;

		if (isset($this->coupon) && !empty($this->coupon->coupon_code))
		{
			if ($this->coupon && !empty($this->coupon->limit_to_mfy_fee))
			{
				$hasServiceFeeCoupon = true;
			}

			if ($this->coupon && !empty($this->coupon->limit_to_delivery_fee))
			{
				$hasDeliveryFeeCoupon = true;
			}
		}

		$food_portion_of_points_credit = 0;
		$fee_portion_of_points_credit = 0;

		if ($this->storeAndUserSupportPlatePoints || $this->is_in_plate_points_program || $userIsOnHold)
		{

			if (!empty($this->coupon) && $this->coupon->limit_to_finishing_touch)
			{
				$this->applyCoupon();
			}

			$this->getPointCredits($food_portion_of_points_credit, $fee_portion_of_points_credit, $editing, $rescheduling, $hasServiceFeeCoupon);
		}

		$this->applyDeliveryFee($editing);

		$this->applyCoupon($editing);

		$this->subtotal_food_items_adjusted = $this->subtotal_menu_items + $this->subtotal_home_store_markup - $this->bundle_discount - $this->membership_discount;

		if (!$hasServiceFeeCoupon && !$hasDeliveryFeeCoupon)
		{
			$this->subtotal_food_items_adjusted -= $this->coupon_code_discount_total;
		}

		if ($this->subtotal_food_items_adjusted < 0)
		{
			$this->subtotal_food_items_adjusted = 0;
		}

		//fixed ToddW 1/3/06
		//don't allow a discount amount greater than the order total
		//also need to do this pretax, or tax will be negative
		if (self::isPriceGreaterThan($this->direct_order_discount, $this->subtotal_food_items_adjusted + $this->misc_food_subtotal))
		{
			$this->direct_order_discount = $this->subtotal_food_items_adjusted + $this->misc_food_subtotal;
		}

		$this->subtotal_food_items_adjusted = $this->subtotal_food_items_adjusted - $this->direct_order_discount + $this->misc_food_subtotal - $food_portion_of_points_credit;

		if ($this->subtotal_food_items_adjusted < .005)
		{
			$this->subtotal_food_items_adjusted = 0;
		}

		$this->subtotal_products += $this->misc_nonfood_subtotal;
		$this->subtotal_all_items = $this->subtotal_food_items_adjusted + $this->subtotal_products + $this->subtotal_delivery_fee - $fee_portion_of_points_credit;

		if ($editing)
		{
			$this->setIsInEditOrder(true);
		}
		$this->applyTax(0, $hasServiceFeeCoupon, $hasDeliveryFeeCoupon);

		if ($hasServiceFeeCoupon)
		{
			$this->subtotal_all_items = $this->subtotal_food_items_adjusted + $this->subtotal_products - $fee_portion_of_points_credit + $this->subtotal_delivery_fee;
		}
		else if ($hasDeliveryFeeCoupon)
		{
			$discount = $this->coupon->calculate($this);
			$delFee = $this->subtotal_delivery_fee;
			if (!is_null($discount) && $discount > 0)
			{
				$delFee = $this->subtotal_delivery_fee - $discount;
			}

			$this->subtotal_all_items = $this->subtotal_food_items_adjusted + $this->subtotal_products - $fee_portion_of_points_credit + $this->subtotal_service_fee + $delFee;
		}
		else
		{
			$this->subtotal_all_items = $this->subtotal_food_items_adjusted + $this->subtotal_products + $this->subtotal_service_fee - $fee_portion_of_points_credit + $this->subtotal_delivery_fee;
		}

		$this->grand_total = $this->subtotal_all_items + $this->subtotal_all_taxes;

		return $this->grand_total;
	}

	function getBoxCount()
	{
		$boxCount = 0;

		foreach($this->boxes AS $box_instance_id => $boxArray)
		{
			if (!empty($boxArray['box_instance']->is_complete))
			{
				$boxCount++;
			}
		}

		return $boxCount;
	}

	public static function getMenuIDBasedOnBundle($order_id)
	{
		$menuIdRetriever = new DAO();
		$menuIdRetriever->query("select b.menu_id from order_item oi
									join bundle b on b.id = oi.bundle_id
									where oi.order_id = $order_id and oi.is_deleted = 0 and not isnull(oi.bundle_id) limit 1");
		if ($menuIdRetriever->fetch())
		{
			return $menuIdRetriever->menu_id;
		}

		return false;
	}

	/*
	 *   Check that current boxes in cart can be fulfilled at $store_id
	 * 	 $store_id may be different than the store and session in the cart
	 *  since the customer nmay have changed the zip code
	 */
	public static function cartInventoryCheck($CartObj, $store_id)
	{

		$parent_store_id = CStore::getParentStoreID($store_id);
		// form unique list of recipes
		$inventoryArray = array(); // recipe_id -> remaining_inventory
		$usageArray = array();

		$hasInventory = true;

		$OrderObj = $CartObj->getOrder();
		$Boxes = $OrderObj->getBoxes();
		$menu_id = false;
		//add order items
		if (!empty($Boxes))
		{
			foreach ($Boxes as $box_instance_id => $thisBox)
			{
				if (!$menu_id)
				{
					$menu_id = $thisBox['bundle']->menu_id;
				}

				foreach ($thisBox['items'] as $item)
				{

					list($qty, $menu_item) = $item;

					if ($qty && $menu_item->id)
					{
						$inventoryArray[$menu_item->recipe_id] = 0;

						if (!isset($usageArray[$menu_item->recipe_id]))
						{
							$usageArray[$menu_item->recipe_id] = 0;
						}

						$usageArray[$menu_item->recipe_id] += ($qty * $menu_item->servings_per_item);
					}
				}
			}
		}

		$recipeList = implode(",", array_keys($inventoryArray));
		$ItemInvObj = new DAO();
		$ItemInvObj->query("select recipe_id, override_inventory, number_sold from menu_item_inventory where store_id = $parent_store_id and menu_id = $menu_id and recipe_id in (" . $recipeList . ") and is_deleted = 0");
		while ($ItemInvObj->fetch())
		{
			$inventoryArray[$ItemInvObj->recipe_id] = $ItemInvObj->override_inventory - $ItemInvObj->number_sold;
		}

		if (!empty($Boxes))
		{
			foreach ($Boxes as $box_instance_id => $thisBox)
			{

				foreach ($thisBox['items'] as $item)
				{

					list($qty, $menu_item) = $item;

					if ($qty && $menu_item->id)
					{
						if ($usageArray[$menu_item->recipe_id] > $inventoryArray[$menu_item->recipe_id])
						{
							$hasInventory = false;
							// TODO: // collect array of understocked items and ???
							// remove from cart - but send where?
							// Note currently only called when DC is switched on Checkout page so this can be pass/fail
							// Will need better handling when used a last second validation
							break;
						}
					}
				}
			}
		}

		return $hasInventory;
	}

	public static function buildOrderItemsArray($Order, $promo = null, $freeMeal = null, $flatList = false, $orderBy = 'FeaturedFirst')
	{

		$menu_id = self::getMenuIDBasedOnBundle($Order->id);

		$menuInfo = CBox::getBoxArray($Order->store_id, false, false, false, false, $menu_id);
		$menuInfo['bundle_items'] = array();

		if (array_key_exists("box", $menuInfo))
		{
			foreach ($menuInfo['box'] as &$thisBox)
			{
				if (!isset($thisBox->bundle))
				{
					$thisBox->bundle = array();
				}

				if (!empty($thisBox->box_bundle_1))
				{
					$bundle = DAO_CFactory::create('bundle');
					$bundle->id = $thisBox->box_bundle_1;
					$bundle->find(true);
					$thisBox->bundle[$thisBox->box_bundle_1] = $bundle;
					$items = CBundle::getDeliveredBundleByID($thisBox->box_bundle_1);
					$menuInfo['bundle_items'][$thisBox->box_bundle_1] = $items['bundle'];
				}

				if (!empty($thisBox->box_bundle_2))
				{
					$bundle = DAO_CFactory::create('bundle');
					$bundle->id = $thisBox->box_bundle_2;
					$bundle->find(true);
					$thisBox->bundle[$thisBox->box_bundle_2] = $bundle;
					$items = CBundle::getDeliveredBundleByID($thisBox->box_bundle_2);
					$menuInfo['bundle_items'][$thisBox->box_bundle_2] = $items['bundle'];
				}
			}
		}

		$Order->reconstruct();
		$currentBoxes = array();
		$Boxes = $Order->getBoxes();

		if (!empty($Boxes))
		{
			foreach ($Boxes as $box_inst_id => $box_data)
			{
				$box_instance = DAO_CFactory::create('box_instance');
				$box_instance->id = $box_inst_id;
				$box_instance->find(true);

				$currentBoxes[$box_inst_id] = array();
				$currentBoxes[$box_inst_id]['box_instance'] = clone($box_instance);

				if (is_object($menuInfo['box'][$box_instance->box_id]))
				{
					$currentBoxes[$box_inst_id]['box_info'] = clone($menuInfo['box'][$box_instance->box_id]);
				}

				$currentBoxes[$box_inst_id]['box_id'] = $box_instance->box_id;
				$currentBoxes[$box_inst_id]['box_type'] = $menuInfo['box'][$box_instance->box_id]->box_type;
				$currentBoxes[$box_inst_id]['box_label'] = $menuInfo['box'][$box_instance->box_id]->title;
				$currentBoxes[$box_inst_id]['bundle_data'] = $menuInfo['box'][$box_instance->box_id]->bundle[$box_instance->bundle_id];
				$currentBoxes[$box_inst_id]['bundle_items'] = array();

				foreach ($menuInfo['bundle_items'][$box_instance->bundle_id] as $id => $itemData)
				{
					$currentBoxes[$box_inst_id]['bundle_items'][$id] = array('id' => $id);
					$currentBoxes[$box_inst_id]['bundle_items'][$id]['display_title'] = $itemData['display_title'];
					$currentBoxes[$box_inst_id]['bundle_items'][$id]['pricing_type'] = $itemData['pricing_type'];
					$currentBoxes[$box_inst_id]['bundle_items'][$id]['qty'] = (!empty($box_data['items'][$id][0]) ? $box_data['items'][$id][0] : 0);

					if (empty($menuInfo['itemList'][$id]))
					{
						$menuInfo['itemList'][$id] = $itemData;
						$menuInfo['itemList'][$id]['qty'] = $currentBoxes[$box_inst_id]['bundle_items'][$id]['qty'];
					}
					else
					{
						$menuInfo['itemList'][$id]['qty'] += $currentBoxes[$box_inst_id]['bundle_items'][$id]['qty'];
					}
				}
			}
		}

		$menuInfo['current_boxes'] = $currentBoxes;

		return $menuInfo;
	}

	function refreshForEditing($menu_id = false)
	{
		//get coupon
		if (!empty($this->coupon_code_id) && is_numeric($this->coupon_code_id))
		{
			$couponCode = DAO_CFactory::create('coupon_code');
			$couponCode->id = $this->coupon_code_id;
			$found = $couponCode->find(true);
			if ($found)
			{
				$this->addCoupon($couponCode);
			}
			else
			{
				throw new Exception("Coupon not found in refreshForEditing()");
			}
		}

		$Store = $this->getStore();

		//get tax
		$this->addSalesTax($Store->getCurrentSalesTaxObj());
	}

	function clearBoxesUnsafe()
	{
		$this->boxes = null;
	}

	function insertEditedItems($sessionIsInPast = true, $removeItemsFromInventory = true)
	{
		$boxes = $this->getBoxes();
		//	$menu_id = $this->session->menu_id;
		$menu_id = current($boxes)['bundle']->menu_id;

		$parentStoreId = CStore::getParentStoreID($this->session->store_id);

		//add order items
		if ($boxes)
		{
			foreach ($boxes as $box_inst_id => $boxItems)
			{
				// So the box instances that were present when the order was loaded were all marked deleted in phase 1
				// deleted boxes will stay that way
				// new boxes will have a new box_instance id and will not be found by this query, they can be ignored
				// but boxes originally present and still present must be recreated
				$boxInstanceUpdate = DAO_CFactory::create('box_instance');
				$boxInstanceUpdate->query("select * from box_instance where id = $box_inst_id and is_deleted = 1");
				if ($boxInstanceUpdate->fetch())
				{
					$newBoxInstance = clone($boxInstanceUpdate);
					$newBoxInstance->id = null;
					$newBoxInstance->is_deleted = 0;
					$newBoxInstance->insert();
					$box_inst_id = $newBoxInstance->id;
				}

				$bundleID = $boxItems['bundle']->id;

				foreach ($boxItems['items'] as $item)
				{
					list($qty, $menu_item) = $item;

					if ($qty && $menu_item->id)
					{

						$orderItem = DAO_CFactory::create('order_item');
						$orderItem->menu_item_id = $menu_item->id;
						$orderItem->order_id = $this->id;
						$orderItem->item_count = $qty;
						$orderItem->box_instance_id = $box_inst_id;
						$orderItem->sub_total = $menu_item->price * $qty;
						$orderItem->pre_mark_up_sub_total = $menu_item->price * $qty;
						$orderItem->bundle_id = $bundleID;

						if (!$orderItem->insert())
						{
							throw new Exception ('could not insert order item');
						}

						try
						{
							// Deplete Inventory
							$servingQty = $menu_item->servings_per_item;

							if ($servingQty == 0)
							{
								$servingQty = 6;
							}

							if ($menu_item->is_chef_touched)
							{
								$servingQty = 1;
							}

							$servingQty *= $qty;
							// INVENTORY TOUCH POINT 3

							if ($removeItemsFromInventory)
							{
								//subtract from inventory
								$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory');
								$DAO_menu_item_inventory->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold +  $servingQty where mii.recipe_id = {$menu_item->recipe_id}
								    and mii.store_id = $parentStoreId and mii.menu_id = $menu_id and mii.is_deleted = 0");
							}
						}
						catch (exception $exc)
						{
							// don't allow a problem here to fail the order
							//log the problem
							CLog::RecordException($exc);

							$debugStr = "INV_CONTROL ISSUE- Order: " . $this->id . " | Item: " . $orderItem->menu_item_id . " | Store: " . $this->store_id;
							CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
						}
					}
				}
			}
		}
	}

	public function addItemToBox($boxInstanceObj, $itemObj, $qty)
	{
		if (!isset($this->boxes) || !is_array($this->boxes))
		{
			$this->boxes = array();
		}

		if (!isset($this->boxes[$boxInstanceObj->id]) || !is_array($this->boxes[$boxInstanceObj->id]))
		{
			$this->boxes[$boxInstanceObj->id] = array();
		}

		if (empty($this->boxes[$boxInstanceObj->id]['box_instance']))
		{
			$this->boxes[$boxInstanceObj->id]['box_instance'] = $boxInstanceObj;
		}

		if (empty($this->boxes[$boxInstanceObj->id]['bundle']))
		{
			$this->boxes[$boxInstanceObj->id]['bundle'] = CBundle::getBundleByID($boxInstanceObj->bundle_id);
		}

		if (!isset($this->boxes[$boxInstanceObj->id]['items']) || !is_array($this->boxes[$boxInstanceObj->id]['items']))
		{
			$this->boxes[$boxInstanceObj->id]['items'] = array();
		}

		if (!empty($this->boxes[$boxInstanceObj->id]['items'][$itemObj->id]))
		{
			// update
			$this->boxes[$boxInstanceObj->id]['items'][$itemObj->id][0] += $qty;
		}
		else
		{
			// new
			$this->boxes[$boxInstanceObj->id]['items'][$itemObj->id] = array();
			$this->boxes[$boxInstanceObj->id]['items'][$itemObj->id][0] = $qty;
			$this->boxes[$boxInstanceObj->id]['items'][$itemObj->id][1] = $itemObj;
		}
	}

	function refresh($DAO_user, $menu_id = false)
	{

		if ($DAO_user && $DAO_user->id)
		{
			//if the user_id is set, make sure it matches the customer passed in
			if ($this->user_id && ($this->user_id !== $DAO_user->id))
			{
				CCart2::instance()->emptyCart();
				CApp::instance()->template()->setStatusMsg('The current cart held items for another user. The cart has been emptied. Please start your order again.');
				//CApp::bounce('/session-menu');
				// TODO: where to bounce to?
				throw new Exception("need to specify bounce location (CDeliveredOrder->Refresh() - new customer)");
			}
		}

		// must have store
		$Store = $this->getStore();

		//get tax
		$this->addSalesTax($Store->getCurrentSalesTaxObj());

		//get coupon
		if (isset($this->coupon_code_id) && $this->coupon_code_id)
		{
			$DAO_coupon_code = DAO_CFactory::Create('coupon_code');
			$DAO_coupon_code->id = $this->coupon_code_id;
			$found = $DAO_coupon_code->find(true);
			if ($found)
			{
				$DAO_coupon_code->calculate($this);

				$this->addCoupon($DAO_coupon_code);
			}
		}

		return true;
	}

	function getCondensedMenuItemArray(&$tempBoxesArr, &$parentBundleItems)
	{

		if ($this->boxes)
		{
			foreach ($this->boxes as $box_id => $thisBox)
			{
				$tempBoxesArr[$box_id] = array();
				foreach ($thisBox as $id => $itemInfo)
				{
					$tempBoxesArr[$box_id][$id] = $itemInfo[0];
				}
			}
		}
	}

	function processEditOrder($originalOrder, $Cart = null, $order_revisions = null, $paymentsFromCart = null, $newPaymentType = null, $creditCardArray = null, $storeCreditArray = false, $giftCardArray = false, $useTransaction = true)
	{
		ini_set('memory_limit', '64M');
		set_time_limit(1800);
		$tpl = CApp::instance()->template();

		//force reload if Back button is pressed
		CTemplate::noCache();

		if (empty($originalOrder))
		{
			return array(
				'result' => 'edit_order_failed',
				'msg' => 'Unable to continue. The order was not found.'
			);
		}

		$booking = DAO_CFactory::create('booking');
		$booking->query("SELECT id, status FROM booking WHERE order_id = " . $originalOrder->id . " AND (status = 'ACTIVE' OR status = 'SAVED') AND is_deleted = 0"); // CES: status should never be SAVED - currently
		$booking->fetch();
		$originalOrderState = $booking->status;

		$User = DAO_CFactory::create('user');
		$User->id = $originalOrder->user_id;
		if (!$User->find(true)) // what if user was deleted?  CES: could use direct query and ignore is_deleted
		{
			return array(
				'result' => 'edit_order_failed',
				'msg' => 'Unable to continue. The user on this order was not found.'
			);
		}
		// Is this order editable by the current user?
		if ($originalOrder->user_id != CUser::getCurrentUser()->id)
		{
			return array(
				'result' => 'edit_order_failed',
				'msg' => 'You do not have access privileges for this order.'
			);
		}

		$originalOrder->orderAddress();

		$daoStore = $originalOrder->getStore();
		if (empty($daoStore) && $originalOrderState != 'NEW')
		{
			return array(
				'result' => 'edit_order_failed',
				'msg' => 'Unable to process this order.'
			);
		}

		$originalOrder->reconstruct();
		if ($originalOrderState == 'CANCELLED')
		{
			$OrigSession = $originalOrder->findSession(false, true);
		}
		else
		{
			$OrigSession = $originalOrder->findSession(true);
		}

		if (!$OrigSession)
		{
			return array(
				'result' => 'edit_order_failed',
				'msg' => 'Selected Delivered time no longer available.'
			);
		}

		// original quantities to be shown in parentheses next to input boxes CES - maybe should error if in past?  Should never happen though
		$sessionIsInPast = $OrigSession->isInThePast($daoStore);

		// ------------------------------------ Payments
		if ($originalOrderState != 'NEW')
		{
			////$refTransArray = $this->getReferenceTransactionArray($originalOrder);
			$paymentArr = COrders::buildPaymentInfoArray($originalOrder->id, CUser::getCurrentUser()->user_type, $OrigSession->session_start, false, true);
			$paymentArr['new_cc_payment'] = $creditCardArray;
			$paymentArr['new_gc_payment'] = $giftCardArray;
			////$this->assignRefundsToPayments($paymentArr, $refTransArray);
		}

		// ------------------------- Process Edits

		CLog::RecordDebugTrace('COrdersDelivered submission handling called for edit order: ' . $originalOrder->id, "TR_TRACING");

		$originalGrandTotalMinusTaxes = $originalOrder->grand_total - $originalOrder->subtotal_all_taxes;
		$originalGrandTotal = $originalOrder->grand_total;

		// record original version in edited orders log
		$order_record = DAO_CFactory::create('edited_orders');
		$order_record->setFrom($originalOrder->toArray());
		$order_record->original_order_id = $originalOrder->id;

		$orderRevisionString = '';
		$orderRevisionHtml = '';
		if (is_array($order_revisions))
		{
			foreach ($order_revisions as $id => $item)
			{
				$orderRevisionString .= $item->description;
				$orderRevisionHtml .= $item->description_html;
			}
		}
		$order_record->order_revisions = $orderRevisionString;
		$order_record->order_revision_notes = $orderRevisionHtml;

		// remember state so that we can restore some critical values if an exception occurs
		$originalOrderPriorToUpdate = clone($originalOrder);

		//Delivery fee
		$originalOrder->subtotal_delivery_fee = $this->subtotal_delivery_fee;
		$originalOrder->subtotal_delivery_tax = $this->subtotal_delivery_tax;

		//Direct Order Discount
		$originalOrder->direct_order_discount = $this->direct_order_discount;

		// Coupon
		$originalOrder->coupon_code_id = $this->coupon_code_id;
		$originalOrder->coupon_free_menu_item = $this->coupon_free_menu_item;

		$originalOrder->coupon_code_discount_total = $this->coupon_code_discount_total;

		if ($this->coupon_code_id === '0' || $this->coupon_code_id === 0 || $this->coupon_code_id === "")
		{
			$originalOrder->coupon_code_id = 'null';
			$originalOrder->coupon_code_discount_total = 0;

			$originalOrder->addCoupon(null);
		}

		//update session if needed
		$sessionObj = $originalOrder->findSession();
		$TargetSession = $this->findSession(true);
		if ($sessionObj->id != $TargetSession->id)
		{
			$originalOrder->addSession($TargetSession);
			$rescheduleResult = $originalOrder->reschedule($sessionObj->id, false, true);
			if ($rescheduleResult != 'success')
			{
				CApp::instance()->template()->setErrorMsg('Unable to reschedule to selected delivery date.');
				throw new Exception('Unable to reschedule to selected delivery date.');
			}
		}

		// Ready to commit everything
		$originalOrder->query('START TRANSACTION;');
		$parentStoreId = $daoStore->parent_store_id;

		try
		{
			$this->cleanUpForeignKeys($order_record);

			$order_record->insert();

			$tempStoreID = $originalOrder->store_id;

			// delete original order items after moving them to edited_order_items
			$order_item = DAO_CFactory::create('order_item');
			$order_item->query("SELECT oi.*, mi.servings_per_item, mi.is_chef_touched, mi.recipe_id FROM order_item oi  JOIN menu_item mi ON mi.id = oi.menu_item_id WHERE oi.order_id = " . $originalOrder->id . " AND oi.is_deleted = 0");

			$boxInstanceArray = array();
			while ($order_item->fetch())
			{
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
					$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold -  $servingQty where mii.recipe_id = {$order_item->recipe_id} and mii.store_id = $parentStoreId and mii.menu_id = {$this->getMenuId()} and mii.is_deleted = 0");
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
			$box_instances = DAO_CFactory::create('box_instance');
			$adminUser = CUser::getCurrentUser()->id;
			foreach ($boxInstanceArray as $box_inst_id)
			{
				$box_instances->query("update box_instance set is_deleted = 1, updated_by = $adminUser where id = $box_inst_id");
			}

			// record changes to the master
			$originalOrder = $this->addMenuItemsToOrder($originalOrder);

			if (!$originalOrder->verifyAdequateInventory())
			{
				$itemsOversold = $$originalOrder->getInvExceptionItemsString();

				return array(
					'result' => 'edit_order_failed',
					'msg' => 'Inventory has changed since the order was started and an item has run out of stock. Please review the order and try again. Items adjusted are:<br />' . $itemsOversold
				);
			}

			$originalOrder->refreshForEditing($OrigSession->menu_id);
			$originalOrder->insertEditedItems($sessionIsInPast);

			$originalOrder->setIsInEditOrder(true);
			$originalOrder->recalculate(true);

			if (!isset($originalOrder->coupon_code_id) || $originalOrder->coupon_code_id === '' || $originalOrder->coupon_code_id === '0' || $originalOrder->coupon_code_id === 0)
			{
				$originalOrder->coupon_code_id = 'null';
			}

			$originalOrder->update();

			$paymentResult = array('result' => 'edit_success');
			if (isset($newPaymentType) && $newPaymentType != "" && count($paymentsFromCart) > 0)
			{
				$paymentArr['paymentsFromCart'] = $paymentsFromCart;
				try
				{
					CEditOrderPaymentManager::processPayment($this, $Cart, $paymentArr, $newPaymentType, $originalOrder, $originalGrandTotal, $User, $daoStore);
				}
				catch (Exception $e)
				{
					$paymentResult = array(
						'result' => 'edit_order_failed',
						'msg' => $e->getMessage()
					);
				}
			}

			if ($paymentResult['result'] == 'edit_success')
			{
				//Send Updates to SS
				ShipStationManager::getInstanceForOrder($originalOrder)->addUpdateOrder(new ShipStationOrderWrapper($originalOrder));
				$originalOrder->query('COMMIT;');
				COrdersDigest::recordEditedOrder($originalOrder, $originalGrandTotalMinusTaxes);
			}

			return $paymentResult;
		}
		catch (Exception $e)
		{
			$originalOrder->query('ROLLBACK;');

			return array(
				'result' => 'edit_order_failed',
				'msg' => 'A problem occurred while updating the order.'
			);

			CLog::RecordException($e);
		}
	}

	private function addMenuItemsToOrder(&$originalOrder)
	{
		$items = $this->getBoxes();
		//get menu from session
		$menu_id = $originalOrder->findSession()->menu_id;

		//clear existing menu items
		$originalOrder->clearBoxesUnsafe();

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

			foreach ($boxData['items'] as $id => $item_data)
			{
				$qty = $item_data[0];
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
						//throw new Exception("Menu item not found: " . $id);
						return array(
							'result' => 'edit_order_failed',
							'msg' => "Menu item not found: " . $id
						);
					}
					else
					{
						$totalItemQty += $qty;
						$originalOrder->addItemToBox($boxInstanceObj, $MenuItem, $qty);
					}
				}
			}
		}

		return $originalOrder;
	}

	private function cleanUpForeignKeys($OrderObj)
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

	// Essentially the Credit Card payment was already completed
	// so this function cannot fail. If it does we then we have a booking in 'SAVED' state
	// and a bare bones order row. Update the order row as much as possible and message the store and guest.
	// This should be a rare occurence

	/*
	 * Called upon callback from PayFlow after successful CC process
	 *
	 *
	 *
	*/

	function postProcessActivatedOrder($Store, $Customer, $emptyCart = false)
	{
		CLog::RecordDebugTrace('COrders::postProcessActivatedOrder called for delivered order: ' . $this->id, "TR_TRACING");

		// On the live server we must eat exceptions here as the order has been committed at thgis point.  If an excpetion occurs here and is alloe
		if ($this->points_discount_total > 0)
		{
			CPointsCredits::processCredits($this->user_id, $this->points_discount_total, $this->id);
		}

		if ($this->boxes)
		{
			foreach ($this->boxes as $box_id => $thisBox)
			{
				CBoxInstance::updateBoxAsOrdered($box_id, $this->id);
			}
		}

		try
		{

			COrdersDigest::recordNewOrder($this, $Store);

			// insert orders_address
			if (isset($this->orderAddress) && is_a($this->orderAddress, 'DAO_Orders_address') && empty($this->orderAddress->id))
			{
				if (empty($this->orderAddress->order_id))
				{
					$this->orderAddress->order_id = $this->id;
				}

				$this->orderAddress->insert();
			}

			// insert orders_shipping
			if (isset($this->orderShipping) && is_a($this->orderShipping, 'DAO_Orders_shipping') && empty($this->orderShipping->id))
			{
				if (empty($this->orderShipping->order_id))
				{
					$this->orderShipping->order_id = $this->id;
				}

				$this->orderShipping->insert();
			}

			ShipStationManager::getInstanceForOrder($this)->addUpdateOrder(new ShipStationOrderWrapper($this));
		}
		catch (exception $exc)
		{
			CLog::RecordException($exc);
			CLog::RecordNew(CLog::ERROR, "Exception occurred in postProcessActivatedOrder; Order ID: " . $this->id, "", "", true);

			if (DEBUG)
			{
				throw $exc;
			}
		}

		if ($emptyCart)
		{
			CCart2::instance()->emptyCart();
		}
	}

	/**
	 * @return 'invalidPayment', 'invalidCC', 'session full', 'closed', 'failed', 'success'
	 * Process order with multiple payments, passing in an array of payment objects
	 * @throws Exception
	 */
	function processOrder($payments, $useTransaction = true)
	{
		CLog::RecordDebugTrace('COrders::processOrder called for user: ' . $this->user_id, "TR_TRACING");

		if ($this->boxes && !$this->session)
		{
			throw new Exception('session not set for order');
		}

		$Customer = DAO_CFactory::create('user');
		$Customer->id = $this->user_id;
		$Customer->find(true);

		if (CPointsUserHistory::userIsActiveInProgram($Customer))
		{
			$this->is_in_plate_points_program = 1;
		}

		$this->recalculate();

		$ccPayment = null; //note: just one cc process for now
		$giftCardCount = 0;
		foreach ($payments as $pay)
		{
			try
			{
				if ($pay->payment_type == CPayment::CC || strpos($pay->payment_type, "REF_") === 0)
				{
					$ccPayment = $pay;
				}

				$pay->validate(true);

				if ($pay->payment_type == CPayment::GIFT_CARD)
				{
					$giftCardCount++;
				}
			}
			catch (exception $e)
			{
				return array(
					'result' => 'invalidPayment',
					'userText' => ''
				);
			}
		}

		//create confirmation number
		//$this->order_confirmation = self::generateConfirmationNum();
		// CES 1/13/15 This might be done by the caller

		if (empty($this->order_confirmation))
		{
			$this->order_confirmation = self::generateConfirmationNum();
		}

		//authorize card?

		//see if order has been saved

		//make sure order has not already been paid for

		$Booking = DAO_CFactory::create('booking');
		$Store = $this->getStore();

		if ($useTransaction)
		{
			$this->query('START TRANSACTION;');
		}

		try
		{
			//add booking
			if (($this->boxes || $this->products) && $this->session && (($this->order_type == self::DIRECT) || ($this->session->isOpen($Store) && $this->session->session_publish_state == CSession::PUBLISHED)))
			{
				$shippingInfo = $this->defaultShippingInfo($this->orderAddress->postal_code, $this->session, false);

				CLog::Assert(($shippingInfo->service_days && $shippingInfo->service_days > 0 && $shippingInfo->service_days < 3), "invalid service_days");

				$shippingDate = new DateTime($this->session->session_start);
				$shippingDate->modify("-{$shippingInfo->service_days} days");
				$shipping_session_id = CSession::getDeliveredSessionIDByDate($shippingDate->format("Y-m-d"), $this->store_id);

				$shippingSession = new DAO();
				$shippingSession->query("select available_slots from session where id = " . $shipping_session_id);
				$shippingSession->fetch();
				$standard_capacity = $shippingSession->available_slots;

				$Booking->session_id = $this->session->id;
				$Booking->user_id = $this->user_id;
				$Booking->status = CBooking::HOLD;
				$Booking->booking_type = 'STANDARD';
				$id = $Booking->insert();
				if (!$id)
				{
					throw new Exception ('booking insert failed : data error ' . $Booking->_lastError->getMessage());
				}

				//lock booking table
				//to prevent this:
				//Thread 1: read available bookings (1 slot left)
				//Thread 2: read available bookings (1 slot left)
				//Thread 1: write booking (0 slot left)
				//Thread 2: write booking (-1 slot left)

				$actualAvailableStdSlots = 0;
				$CapacityExists = false;
				$BookingLocks = DAO_CFactory::create('booking');
				$BookingLocks->query("SELECT status, booking_type FROM booking WHERE session_id = $shipping_session_id AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND status != 'SAVED' AND is_deleted = 0 FOR UPDATE ");

				//count active bookings
				$StdBookCnt = 0;
				while ($BookingLocks->fetch())
				{
					if ($BookingLocks->status === CBooking::ACTIVE)
					{
						$StdBookCnt++;
					}
				}

				$actualAvailableStdSlots = $standard_capacity - $StdBookCnt;
				if ($actualAvailableStdSlots > 0)
				{
					$CapacityExists = true;
				}

				if ($CapacityExists)
				{
					//save order
					$inserted = $this->insert();
					if (!$inserted)
					{
						throw new Exception('data error ' . $this->_lastError->getMessage());
					}

					//save booking
					$Booking->order_id = $this->id;
					$Booking->status = CBooking::ACTIVE;
					$success = $Booking->update();
					if (!$success)
					{
						throw new Exception('data error ' . $Booking->_lastError->getMessage());
					}

					////////////////////////
					// process credit card
					if ($ccPayment)
					{
						//processes cc payment with verisign

						if (strpos($ccPayment->payment_type, "REF_") === 0)
						{
							$rslt = array();
							$ref_number = substr($ccPayment->payment_type, 4);
							$ccPayment->payment_type = CPayment::CC;
							$ccPayment->order_id = $this->id;
							$origPayment = DAO_CFactory::create('payment');
							$origPayment->payment_transaction_number = $ref_number;
							if (!$origPayment->find(true))
							{
								CLog::RecordNew(CLog::ERROR, "Unable to find original payment during processing of reference.", '', '', true);
								$rslt['result'] = 'failed';

								return $rslt;
							}

							$ccPayment->referent_id = $origPayment->id;
							$ccPayment->payment_number = $origPayment->payment_number;
							$ccPayment->credit_card_type = $origPayment->credit_card_type;

							$rslt = $ccPayment->processByReference($Customer, $this, $Store, $ref_number, false);

							if ($rslt['result'] != 'success')
							{
								//verisign failed, rollback order and booking
								if ($useTransaction)
								{
									$this->query('ROLLBACK;');
								}

								if ($rslt['result'] == 'transactionDecline')
								{
									CLog::RecordNew(CLog::CCDECLINE, $ccPayment->verisignExtendedRslt);

									return $rslt;
								}
								else
								{
									CLog::RecordNew(CLog::ERROR, $ccPayment->verisignExtendedRslt, '', '', true);
									$rslt['result'] = 'failed';

									return $rslt;
								}
							}
						}
						else
						{
							$rslt = $ccPayment->processPayment($Customer, $this, $Store);

							if ($rslt['result'] != 'success')
							{
								//verisign failed, rollback order and booking
								if ($useTransaction)
								{
									$this->query('ROLLBACK;');
								}

								if ($rslt['result'] == 'transactionDecline')
								{
									CLog::RecordNew(CLog::CCDECLINE, $ccPayment->verisignExtendedRslt);

									return $rslt;
								}
								else
								{
									CLog::RecordNew(CLog::ERROR, $ccPayment->verisignExtendedRslt, '', '', true);
									$rslt['result'] = 'failed';

									return $rslt;
								}
							}
						}
					}

					//end process credit card
					////////////////////////
					try
					{

						$successfulGiftCardCount = 0;
						foreach ($payments as $pay)
						{

							$paymentIsClearedForInsert = true;

							if ($pay->payment_type == CPayment::GIFT_CARD)
							{
								// if there was a CC payment then do not allow as Gift CArd transaction failure to rollback the order, so handle any errors here and notify the
								// the authorities. HOWEVER, if there was no CC payment (and there is only 1 GC payment then let any errors rollback the order -
								//

								if ($ccPayment || $successfulGiftCardCount > 0)
								{
									try
									{
										$GCresult = CGiftCard::unloadDebitGiftCardWithRetry($pay->payment_number, $pay->total_amount, false, $this->store_id, $this->id);

										if (!$GCresult)
										{
											@CLog::NotifyFadmin($this->store_id, '!!Dreamdinners Server Alert!!', 'The Dreamdinners website ' . 'tried to process a Debit Gift Card for your store, but the transaction has failed. ' . 'A previous card transaction was successful so the order will be successfully entered into the system ' . "but it may be underfunded by at least the amount of this failed Gift Card payment. Please obtain payment " . "from the customer and enter it using the Order Editor. The customer ID is {$this->user_id} and the order confirmation number is {$this->order_confirmation}.");

											CLog::RecordNew(Clog::ERROR, "Order succeeded but Debit gift card payment failed. The customer " . "ID is {$this->user_id} and the order confirmation number is {$this->order_confirmation}.", "", "", true);

											$paymentIsClearedForInsert = false;
										}
										else
										{
											$pay->payment_transaction_number = $GCresult;
											$pay->payment_number = str_repeat('X', (strlen($pay->payment_number) - 4)) . substr($pay->payment_number, -4);
											$successfulGiftCardCount++;
										}
									}
									catch (exception $exc)
									{ /* eat the exception */
									}
								}
								else
								{
									$GCresult = CGiftCard::unloadDebitGiftCardWithRetry($pay->payment_number, $pay->total_amount, false, $this->store_id, $this->id);

									if (!$GCresult)
									{
										CLog::RecordNew(Clog::ERROR, "Order failed because Debit gift card payment failed. The customer " . "ID is {$this->user_id}.", "", "", true);

										return array(
											'result' => 'failed',
											'userText' => 'The gift card payment has failed. Please try again'
										);
									}
									else
									{
										$pay->payment_transaction_number = $GCresult;
										$pay->payment_number = str_repeat('X', (strlen($pay->payment_number) - 4)) . substr($pay->payment_number, -4);
										$successfulGiftCardCount++;
									}
								}
							}

							if ($paymentIsClearedForInsert)
							{
								//insert payment record
								$pay->order_id = $this->id;
								$rslt = $pay->insert();
								if (!$rslt)
								{
									if (DEBUG)
									{
										echo 'E_ERROR:: payment insertion error ';
									}
									@CLog::Record('E_ERROR:: payment insertion error ' . implode(',', $pay->toArray()));
								}

								$pay->recordRevenueEvent($this->session);
							}

							// Handle Store Credit
							if ($pay->payment_type == CPayment::STORE_CREDIT)
							{
								if (isset($pay->store_credit_DAO))
								{
									$fromCrdit = (int)($pay->store_credit_DAO->amount * 100);
									$fromPay = (int)($pay->total_amount * 100);

									if ($fromCrdit > $fromPay)
									{ //partial usage of a store credit- must create a new store credit for the leftover
										$newStoreCredit = clone($pay->store_credit_DAO);
										$newStoreCredit->amount = ($pay->total_amount - $pay->store_credit_DAO->amount) * -1;
										$pay->store_credit_DAO->amount = $pay->total_amount;
										$newStoreCredit->original_credit_id = $pay->store_credit_DAO->id;
										$newStoreCredit->date_original_credit = $pay->store_credit_DAO->timestamp_created;
										$rslt = $newStoreCredit->insert();
										if (!$rslt)
										{
											if (DEBUG)
											{
												echo 'E_ERROR:: store credit insertion error ';
											}
											@CLog::Record('E_ERROR:: store credit insertion error ' . implode(',', $pay->store_credit_DAO->toArray()));
										}
									}

									// store credit is converted to payment above - both that and the update must occur
									$pay->store_credit_DAO->is_redeemed = 1;
									$rslt = $pay->store_credit_DAO->update();
									if (!$rslt)
									{
										if (DEBUG)
										{
											echo 'E_ERROR:: store credit redemption error ';
										}
										@CLog::Record('E_ERROR:: store credit update error ' . implode(',', $pay->store_credit_DAO->toArray()));
									}
								}
								else
								{
									// can't throw here because a CC transaction may have succeeded
									if (DEBUG)
									{
										echo 'E_ERROR:: No store Credit Obj when payment type is store credit ';
									}
									@CLog::Record('E_ERROR:: no store credit ' . implode(',', $pay->store_credit_DAO->toArray()));
								}
							}

							//Added 1/17/06 ToddW
							//New delayed payment process
							if ($pay->PendingPayment)
							{
								$rslt = $pay->PendingPayment->insert();
								if (!$rslt)
								{
									if (DEBUG)
									{
										echo 'E_ERROR:: pending payment insertion error ';
									}
									@CLog::Record('E_ERROR:: pending payment insertion error ' . implode(',', $pay->PendingPayment->toArray()));
								}
							}
						}
					}
					catch (exception $exc)
					{
						if (DEBUG)
						{
							throw $exc;
						}
						//if anything fails at this point, we're stuck, so just log the error
						@CLog::Record('E_ERROR:: payment insertion error ' . implode(',', $pay->toArray()));
					}
				}
				else
				{ //full; delete HOLD booking //if ( $bookCnt < $capacity )
					//delete the booking
					$Booking->delete();
					if ($useTransaction)
					{
						$this->query('COMMIT;');
					}

					return array(
						'result' => 'session full',
						'userText' => ''
					);
				}
			}
			else
			{ //closed; //no inserts
				if ($useTransaction)
				{
					$this->query('COMMIT;');
				} //end the transaction

				return array(
					'result' => 'closed',
					'userText' => ''
				);
			}
		}
		catch (exception $exc)
		{
			if ($useTransaction)
			{
				$this->query('ROLLBACK;');
			}
			CLog::RecordException($exc);
			if (DEBUG)
			{
				throw $exc;
			}

			return array(
				'result' => 'failed',
				'userText' => ''
			);
		}

		if ($useTransaction)
		{
			$this->query('COMMIT;');
		}

		$this->postProcessActivatedOrder($Store, $Customer, true);

		return array(
			'result' => 'success',
			'userText' => ''
		);
	}

	/**
	 * @return 'invalidPayment', 'invalidCC', 'session full', 'closed', 'failed', 'success'
	 * Process order with multiple payments, passing in an array of payment objects
	 *
	 * In this case the booking and order rows exist. Add the payment and set the booking to
	 * ACTIVE is all goes well
	 *
	 *   Called by Fdmin Order Manager for Initial Payment if non-CC. IF a secondary CC payment exists add PAyment is subsequently called
	 *
	 * Differences between processSavedOrder and process_order:
	 *
	 * 1) Order is up-to-date in DB so no updates are required ... Object is also up-to-date
	 * 2) Function is always run as transaction so switching logic is removed
	 * 3) Though the capacity check is in place it is currently not triggered since all callers are from BackOffice
	 * 4) Generates a warning message if other saved orders exist and no capacity remains
	 *
	 *
	 * @throws Exception
	 */

	function processSavedOrder($payments, $useTransaction = true)
	{

		$additional_info = false;

		CLog::RecordDebugTrace('COrders::processSavedOrder called for order: ' . $this->id, "TR_TRACING");

		if ($this->items && !$this->session)
		{
			throw new Exception('session not set for order');
		}

		$warnOfOutstandingSavedOrdersOnFullSession = false;

		$Customer = DAO_CFactory::create('user');
		$Customer->id = $this->user_id;
		$Customer->find(true);

		if (CPointsUserHistory::userIsActiveInProgram($Customer))
		{
			$this->is_in_plate_points_program = 1;
		}

		$this->recalculate();

		$ccPayment = null; //note: just one cc process for now
		$giftCardCount = 0;
		foreach ($payments as $pay)
		{
			try
			{
				if ($pay->payment_type == CPayment::CC || strpos($pay->payment_type, "REF_") === 0)
				{
					$ccPayment = $pay;
				}

				$pay->validate(true);

				if ($pay->payment_type == CPayment::GIFT_CARD)
				{
					$giftCardCount++;
				}
			}
			catch (exception $e)
			{
				return array(
					'result' => 'invalidPayment',
					'userText' => ''
				);
			}
		}

		//create confirmation number
		$this->order_confirmation = self::generateConfirmationNum();

		$Booking = DAO_CFactory::create('booking');
		$Booking->order_id = $this->id;
		$Booking->status = 'SAVED';
		if (!$Booking->find(true))
		{
			return array(
				'result' => 'booking_not_found',
				'userText' => ''
			);
		}

		$Store = $this->getStore();

		if ($useTransaction)
		{
			$this->query('START TRANSACTION;');
		}

		try
		{

			//add booking

			if (!empty($this->boxes) && !empty($this->session) && (($this->order_type == self::DIRECT) || ($this->session->isOpen($Store) && $this->session->session_publish_state == CSession::PUBLISHED)))
			{

				$shippingInfo = $this->orderShipping();
				CLog::Assert(($shippingInfo->service_days && $shippingInfo->service_days > 0 && $shippingInfo->service_days < 3), "invalid service_days");

				$shippingDate = new DateTime($this->session->session_start);
				$shippingDate->modify("-{$shippingInfo->service_days} days");
				$shipping_session_id = CSession::getDeliveredSessionIDByDate($shippingDate->format("Y-m-d"), $this->store_id);

				$shippingSession = new DAO();
				$shippingSession->query("select available_slots from session where id = " . $shipping_session_id);
				$shippingSession->fetch();
				$standard_capacity = $shippingSession->available_slots;

				$Booking->booking_type = 'STANDARD';
				$Booking->status = CBooking::HOLD;

				$this->updateTypeOfOrder();

				//lock booking table
				//to prevent this:
				//Thread 1: read available bookings (1 slot left)
				//Thread 2: read available bookings (1 slot left)
				//Thread 1: write booking (0 slot left)
				//Thread 2: write booking (-1 slot left)

				$CapacityExists = false;
				$BookingLocks = DAO_CFactory::create('booking');

				$BookingLocks->query("SELECT status, booking_type FROM booking WHERE session_id = $shipping_session_id AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND is_deleted = 0 FOR UPDATE ");

				//count active bookings
				$StdBookCnt = 0;
				$savedCount = 0;
				while ($BookingLocks->fetch())
				{
					if ($BookingLocks->status === CBooking::ACTIVE)
					{
						$StdBookCnt++;
					}
					else if ($BookingLocks->status === CBooking::SAVED)
					{
						$savedCount++;
					}
				}

				$CapacityExistsAfterThisOrder = false;

				$stdCapRemaining = $standard_capacity - $StdBookCnt;

				if ($stdCapRemaining > 0)
				{
					$CapacityExists = true;
				}

				if ($stdCapRemaining > 1)
				{
					$CapacityExistsAfterThisOrder = true;
				}

				if ($savedCount > 1 && !$CapacityExistsAfterThisOrder)
				{
					$warnOfOutstandingSavedOrdersOnFullSession = true;
				}

				if (($this->order_type == self::DIRECT) || $CapacityExists)
				{

					//save booking
					$Booking->order_id = $this->id;
					$Booking->status = CBooking::ACTIVE;
					$success = $Booking->update();
					if (!$success)
					{
						throw new Exception('data error ' . $Booking->_lastError->getMessage());
					}

					////////////////////////
					// process credit card

					if ($ccPayment)
					{
						//processes cc payment with verisign

						if (strpos($ccPayment->payment_type, "REF_") === 0)
						{
							$rslt = array();
							$ref_number = substr($ccPayment->payment_type, 4);
							$ccPayment->payment_type = CPayment::CC;
							$ccPayment->order_id = $this->id;
							$origPayment = DAO_CFactory::create('payment');
							$origPayment->payment_transaction_number = $ref_number;
							if (!$origPayment->find(true))
							{
								CLog::RecordNew(CLog::ERROR, "Unable to find original payment during processing of reference.", '', '', true);
								$rslt['result'] = 'failed';

								return $rslt;
							}

							$ccPayment->referent_id = $origPayment->id;
							$ccPayment->payment_number = $origPayment->payment_number;
							$ccPayment->credit_card_type = $origPayment->credit_card_type;

							$rslt = $ccPayment->processByReference($Customer, $this, $Store, $ref_number, false);

							if ($rslt['result'] != 'success')
							{
								//verisign failed, rollback order and booking
								if ($useTransaction)
								{
									$this->query('ROLLBACK;');
								}

								if ($rslt['result'] == 'transactionDecline')
								{
									CLog::RecordNew(CLog::CCDECLINE, $ccPayment->verisignExtendedRslt);

									return $rslt;
								}
								else
								{
									CLog::RecordNew(CLog::ERROR, $ccPayment->verisignExtendedRslt, '', '', true);
									$rslt['result'] = 'failed';

									return $rslt;
								}
							}
						}
						else
						{
							$rslt = $ccPayment->processPayment($Customer, $this, $Store);

							if ($rslt['result'] != 'success')
							{
								//verisign failed, rollback order and booking
								if ($useTransaction)
								{
									$this->query('ROLLBACK;');
								}

								if ($rslt['result'] == 'transactionDecline')
								{
									CLog::RecordNew(CLog::CCDECLINE, $ccPayment->verisignExtendedRslt);

									return $rslt;
								}
								else
								{
									CLog::RecordNew(CLog::ERROR, $ccPayment->verisignExtendedRslt, '', '', true);
									$rslt['result'] = 'failed';

									return $rslt;
								}
							}
						}
					}

					//end process credit card
					////////////////////////

					try
					{

						$successfulGiftCardCount = 0;
						foreach ($payments as $pay)
						{

							$paymentIsClearedForInsert = true;

							if ($pay->payment_type == CPayment::GIFT_CARD)
							{
								// if there was a CC payment then do not allow as Gift CArd transaction failure to rollback the order, so handle any errors here and notify the
								// the authorities. HOWEVER, if there was no CC payment (and there is only 1 GC payment then let any errors rollback the order -
								//

								if ($ccPayment || $successfulGiftCardCount > 0)
								{
									try
									{
										$GCresult = CGiftCard::unloadDebitGiftCardWithRetry($pay->payment_number, $pay->total_amount, false, $this->store_id, $this->id);

										if (!$GCresult)
										{
											@CLog::NotifyFadmin($this->store_id, '!!Dreamdinners Server Alert!!', 'The Dreamdinners website ' . 'tried to process a Debit Gift Card for your store, but the transaction has failed. ' . 'A previous card transaction was successful so the order will be successfully entered into the system ' . "but it may be underfunded by at least the amount of this failed Gift Card payment. Please obtain payment " . "from the customer and enter it using the Order Editor. The customer ID is {$this->user_id} and the order confirmation number is {$this->order_confirmation}.");
											CLog::RecordNew(Clog::ERROR, "Order succeeded but gift card payment failed. The customer " . "ID is {$this->user_id} and the order confirmation number is {$this->order_confirmation}.", "", "", true);
											$additional_info = "Please Note: We tried to process a Debit Gift Card for your store, but the transaction has failed. A previous card transaction was
																	successful so the order was processed but it may be underfunded by at least the amount of this failed Gift Card payment. Please
																	obtain payment from the customer and enter it using the Order Editor. <span style='color:red'>This likely occurred due to a
																	 temporary outage of our gift card processor. If you try the Gift Card number again it will likely succeed.</span>";

											$paymentIsClearedForInsert = false;
										}
										else
										{
											$pay->payment_transaction_number = $GCresult;
											$pay->payment_number = str_repeat('X', (strlen($pay->payment_number) - 4)) . substr($pay->payment_number, -4);
											$successfulGiftCardCount++;
										}
									}
									catch (exception $exc)
									{ /* eat the exception */
									}
								}
								else
								{
									$GCresult = CGiftCard::unloadDebitGiftCardWithRetry($pay->payment_number, $pay->total_amount, false, $this->store_id, $this->id);

									if (!$GCresult)
									{
										CLog::RecordNew(Clog::ERROR, "Order failed because Debit gift card payment failed. The customer " . "ID is {$this->user_id}.", "", "", true);

										return array(
											'result' => 'failed',
											'userText' => 'The gift card payment has failed. Please try again'
										);
									}
									else
									{
										$pay->payment_transaction_number = $GCresult;
										$pay->payment_number = str_repeat('X', (strlen($pay->payment_number) - 4)) . substr($pay->payment_number, -4);
										$successfulGiftCardCount++;
									}
								}
							}

							if ($paymentIsClearedForInsert)
							{
								//insert payment record
								$pay->order_id = $this->id;
								$rslt = $pay->insert();
								if (!$rslt)
								{
									if (DEBUG)
									{
										echo 'E_ERROR:: payment insertion error ';
									}
									@CLog::Record('E_ERROR:: payment insertion error ' . implode(',', $pay->toArray()));
								}

								$pay->recordRevenueEvent($this->session);
							}

							// Handle Store Credit
							if ($pay->payment_type == CPayment::STORE_CREDIT)
							{
								if (isset($pay->store_credit_DAO))
								{
									$fromCrdit = (int)($pay->store_credit_DAO->amount * 100);
									$fromPay = (int)($pay->total_amount * 100);

									if ($fromCrdit > $fromPay)
									{ //partial usage of a store credit- must create a new store credit for the leftover
										$newStoreCredit = clone($pay->store_credit_DAO);
										$newStoreCredit->amount = ($pay->total_amount - $pay->store_credit_DAO->amount) * -1;
										$pay->store_credit_DAO->amount = $pay->total_amount;
										$newStoreCredit->original_credit_id = $pay->store_credit_DAO->id;
										$newStoreCredit->date_original_credit = $pay->store_credit_DAO->timestamp_created;
										$rslt = $newStoreCredit->insert();
										if (!$rslt)
										{
											if (DEBUG)
											{
												echo 'E_ERROR:: store credit insertion error ';
											}
											@CLog::Record('E_ERROR:: store credit insertion error ' . implode(',', $pay->store_credit_DAO->toArray()));
										}
									}

									// store credit is converted to payment above - both that and the update must occur
									$pay->store_credit_DAO->is_redeemed = 1;
									$rslt = $pay->store_credit_DAO->update();
									if (!$rslt)
									{
										if (DEBUG)
										{
											echo 'E_ERROR:: store credit redemption error ';
										}
										@CLog::Record('E_ERROR:: store credit update error ' . implode(',', $pay->store_credit_DAO->toArray()));
									}
								}
								else
								{
									// can't throw here because a CC transaction may have succeeded
									if (DEBUG)
									{
										echo 'E_ERROR:: No store Credit Obj when payment type is store credit ';
									}
									@CLog::Record('E_ERROR:: no store credit ' . implode(',', $pay->store_credit_DAO->toArray()));
								}
							}

							//Added 1/17/06 ToddW
							//New delayed payment process
							if ($pay->PendingPayment)
							{
								$rslt = $pay->PendingPayment->insert();
								if (!$rslt)
								{
									if (DEBUG)
									{
										echo 'E_ERROR:: pending payment insertion error ';
									}
									@CLog::Record('E_ERROR:: pending payment insertion error ' . implode(',', $pay->PendingPayment->toArray()));
								}
							}
						}
					}
					catch (exception $exc)
					{
						if (DEBUG)
						{
							throw $exc;
						}
						//if anything fails at this point, we're stuck, so just log the error
						@CLog::Record('E_ERROR:: payment insertion error ' . implode(',', $pay->toArray()));
					}
				}
				else
				{ //full; delete HOLD booking //if ( $bookCnt < $capacity )
					//delete the booking
					$Booking->delete();
					if ($useTransaction)
					{
						$this->query('COMMIT;');
					}

					return array(
						'result' => 'session full',
						'userText' => ''
					);
				}
			}
			else
			{ //closed; //no inserts
				if ($useTransaction)
				{
					$this->query('COMMIT;');
				} //end the transaction

				return array(
					'result' => 'closed',
					'userText' => ''
				);
			}
		}
		catch (exception $exc)
		{
			if ($useTransaction)
			{
				$this->query('ROLLBACK;');
			}
			CLog::RecordException($exc);
			if (DEBUG)
			{
				throw $exc;
			}

			return array(
				'result' => 'failed',
				'userText' => ''
			);
		}

		if ($useTransaction)
		{
			$this->query('COMMIT;');
		}

		$this->postProcessActivatedOrder($Store, $Customer);

		return array(
			'result' => 'success',
			'userText' => '',
			'additional_info' => $additional_info,
			'warnOfOutstandingSavedOrdersOnFullSession' => $warnOfOutstandingSavedOrdersOnFullSession
		);
	}

	function handleItemsInserts()
	{
		$menu_id = $this->getMenuId();

		$parentStoreId = CStore::getParentStoreID($this->session->store_id);

		//add order items
		if ($this->boxes)
		{
			foreach ($this->boxes as $box_instance_id => $thisBox)
			{
				if (!empty($thisBox["box_instance"]->is_complete) && empty($thisBox["box_instance"]->is_in_edit_mode))
				{
					$bundleID = $thisBox['bundle']->id;

					foreach ($thisBox['items'] as $item)
					{

						list($qty, $menu_item) = $item;

						if ($qty && $menu_item->id)
						{

							$orderItem = DAO_CFactory::create('order_item');
							$orderItem->box_instance_id = $box_instance_id;
							$orderItem->menu_item_id = $menu_item->id;
							$orderItem->order_id = $this->id;
							$orderItem->item_count = $qty;
							$orderItem->bundle_id = $bundleID;

							$thisPrice = $menu_item->price;
							$preMarkupPrice = $menu_item->price;

							$orderItem->sub_total = $thisPrice * $qty;
							$orderItem->pre_mark_up_sub_total = $preMarkupPrice * $qty;

							if (!$orderItem->insert())
							{
								throw new Exception ('could not insert order item');
							}

							try
							{
								if (!empty($menu_item->recipe_id))
								{
									$servingQty = $menu_item->servings_per_item;

									if ($servingQty == 0)
									{
										$servingQty = 6;
									}

									if ($menu_item->is_chef_touched)
									{
										$servingQty = 1;
									}

									$servingQty *= $qty;

									// INVENTORY TOUCH POINT 5

									//subtract from inventory
									$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory');
									$DAO_menu_item_inventory->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold + $servingQty where mii.recipe_id = {$menu_item->recipe_id} and
								                mii.store_id = $parentStoreId and mii.menu_id = $menu_id and mii.is_deleted = 0");
								}
							}
							catch (exception $exc)
							{
								// don't allow a problem here to fail the order
								//log the problem
								CLog::RecordException($exc);
								$debugStr = "INV_CONTROL ISSUE- Order: " . $this->id . " | Item: " . $orderItem->menu_item_id . " | Store: " . $this->store_id;
								CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
							}
						}
					}
				}
			}
		}

		//add products
		if ($this->products)
		{
			foreach ($this->products as $item)
			{

				list($qty, $product) = $item;

				if ($qty && $product->id)
				{

					$orderItem = DAO_CFactory::create('order_item');
					$orderItem->product_id = $product->id;
					$orderItem->order_id = $this->id;
					$orderItem->item_count = $qty;

					//TODO: getItemMarkupMultiSubtotal really is not designed for prodcuts
					// consider a new function once markup rules for products are established
					if ($product->item_type == 'ENROLLMENT')
					{
						$orderItem->sub_total = $product->price * $qty;
					}
					else
					{
						$orderItem->sub_total = self::getItemMarkupMultiSubtotal($this->mark_up, $product, $qty);
					}

					$orderItem->pre_mark_up_sub_total = $product->price * $qty;

					if (!$orderItem->insert())
					{
						throw new Exception ('could not insert order item');
					}

					if ($product->item_type == 'ENROLLMENT')
					{
						CUser::registerSubscription($this, $product);
					}
				}
			}
		}
	}

	function getCartDisplayArrays()
	{

		$retVal = array();

		$totalItemsCost = 0;

		if (!empty($this->boxes))
		{
			foreach ($this->boxes as $boxInstanceID => $thisBox)
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

				$retVal[$boxInstanceID] = array(
					'box_instance' => $boxInstanceObj,
					'bundle' => $thisBox['bundle'],
					'items' => array(),
					'box_info' => array(
						'number_items' => 0,
						'number_servings' => 0
					)
				);

				foreach ($thisBox['items'] as $id => $itemInfo)
				{
					$retVal[$boxInstanceID]['items'][$id] = array(
						'menu_item_description' => $itemInfo[1]->menu_item_description,
						'display_description' => $itemInfo[1]->menu_item_description,
						'is_freezer_menu' => (($itemInfo[1]->menu_item_category_id > 4 || $itemInfo[1]->is_store_special) ? true : false),
						'recipe_id' => $itemInfo[1]->recipe_id,
						'menu_item_id' => $itemInfo[1]->id,
						'entree_id' => $itemInfo[1]->entree_id,
						'display_title' => $itemInfo[1]->menu_item_name,
						'menu_item_name' => $itemInfo[1]->menu_item_name,
						'price' => $itemInfo[1]->price,
						'servings_per_item' => $itemInfo[1]->servings_per_item,
						'qty' => $itemInfo[0],
						'subtotal' => $itemInfo[1]->price * $itemInfo[0],
						'is_store_special' => $itemInfo[1]->is_store_special,
						'menu_item_category_id' => $itemInfo[1]->menu_item_category_id
					);

					$retVal[$boxInstanceID]['box_info']['number_items'] += 1;
					$retVal[$boxInstanceID]['box_info']['number_servings'] += $itemInfo[1]->servings_per_item;
				}

				if (!empty($boxInstanceObj->is_complete))
				{
					$totalItemsCost += $thisBox['bundle']->price;
				}
			}
		}

		return array(
			$retVal,
			$totalItemsCost
		);
	}

	function getAvgCostPerServingData()
	{

		// Per Brandy, 7/23/2021 avg cost is grand total / servings
		$EntreeServings = 0;
		$EntreeCost = $this->grand_total;

		if ($this->boxes)
		{
			foreach ($this->boxes as $box_id => $thisBox)
			{
				$retVal[$box_id] = array();
				foreach ($thisBox['items'] as $id => $itemInfo)
				{
					// if the order is delivered and it's a side, use the servings_per_container_display to calculate total serving average
					if ($itemInfo[1]->servings_per_item == 1 && !empty($itemInfo[1]->servings_per_container_display) && is_numeric($itemInfo[1]->servings_per_container_display))
					{
						$EntreeServings += ($itemInfo[1]->servings_per_container_display * $itemInfo[0]);
					}
					else
					{
						$EntreeServings += ($itemInfo[1]->servings_per_item * $itemInfo[0]);
					}
				}
			}
		}

		return array(
			$EntreeServings,
			$EntreeCost
		);
	}

	function getPointsDiscountableAmount()
	{
		$maxDeductible = parent::getPointsDiscountableAmount();

		/*
		if ($maxDeductible > 5.00)
		{
			$maxDeductible = 5.00;
		}
		*/

		return $maxDeductible;
	}

	function getBoxes()
	{
		return $this->boxes;
	}

	function reconstruct($useOriginalPricing = false)
	{
		if (empty($this->id))
		{
			return; // only for orders already committed to the database
		}

		$this->boxes = array();

		$totalItemQty = 0;

		$OrderItem = DAO_CFactory::create('order_item');

		$OrderItem->query("select
				oi.*, bi.*
				from order_item oi
				join box_instance bi on bi.id = oi.box_instance_id and bi.is_deleted = 0
				where oi.order_id = {$this->id}
				and oi.is_deleted = 0");

		while ($OrderItem->fetch())
		{
			$MenuItem = DAO_CFactory::create('menu_item');

			$MenuItem->query("SELECT
				mi.*
				FROM
				menu_item AS mi
				WHERE mi.id = {$OrderItem->menu_item_id}
				AND mi.is_deleted = 0");

			if (!$MenuItem->fetch())
			{
				throw new Exception("Menu item not found: " . $OrderItem->menu_item_id);
			}
			else
			{
				if (!isset($this->boxes[$OrderItem->box_instance_id]))
				{
					$this->boxes[$OrderItem->box_instance_id] = array();
					$this->boxes[$OrderItem->box_instance_id]['bundle'] = CBundle::getBundleByID($OrderItem->bundle_id);
					$this->boxes[$OrderItem->box_instance_id]['box_instance'] = CBoxInstance::getBoxInstanceByID($OrderItem->box_instance_id);
					$this->boxes[$OrderItem->box_instance_id]['box_instance']->box = CBox::getBoxByID($OrderItem->box_id);
				}

				if (!isset($this->boxes[$OrderItem->box_instance_id]['items']))
				{
					$this->boxes[$OrderItem->box_instance_id]['items'] = array();
				}

				$this->boxes[$OrderItem->box_instance_id]['items'][$OrderItem->menu_item_id] = array(
					$OrderItem->item_count,
					$MenuItem
				);
				$totalItemQty += $OrderItem->item_count;
			}
		}

		return $totalItemQty;
	}

	function getNumberServings()
	{
		if (empty($this->boxes))
		{
			return 0;
		}

		$numServings = 0;

		foreach ($this->boxes as $thisBox)
		{
			foreach ($thisBox['items'] as $itemObj)
			{

				if (isset($itemObj[1]->servings_per_item))
				{
					$numServings += ($itemObj[1]->servings_per_item * $itemObj[0]);
				}
				else
				{
					$numServings += (CMenuItem::translatePricingTypeToNumeric($itemObj[1]->pricing_type) * $itemObj[0]);
				}
			}
		}

		return $numServings;
	}

	function getOrderFoodState($CartObj = null, $orderMinimum = null)
	{
		if (empty($this->boxes))
		{
			return "noFood";
		}
		else
		{
			return 'adequateFood';
		}

		return 'inadequateFoodDelivered';
	}

	function getValidSessionsForShipping($service_days, $dist_ctr_id, $deliveryDaysLimit = 10)
	{

		$retVal = array();
		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $dist_ctr_id;
		$storeObj->find(true);

		$orgServiceDays = $service_days;
		$deliveryDayFilter = $service_days - 1;
		$service_days++;  // TODO: Is there a threshhold prior to which today can be considered the first day?
		$todayTS = CTimezones::getAdjustedTime($storeObj, time());
		$today = new DateTime(date("Y-m-d H:i:s", $todayTS));
		$today->modify("+$service_days days");
		$earliestDeliveryDate = $today->format("Y-m-d");
		$sessionFinder = DAO_CFactory::create('session');

		//$this->query("select * from session where store_id = {$storeObj->id} and DATE(session_start) >= '$earliestDeliveryDate' and delivered_supports_delivery > $deliveryDayFilter and is_deleted = 0 order by session_start limit $max_returned");
		$sessionFinder->query("select iq.id from (
								select * from session where store_id = {$storeObj->id}  and DATE(session_start) >= '$earliestDeliveryDate' and delivered_supports_delivery > $deliveryDayFilter and is_deleted = 0 order by session_start limit 20) as iq
								join session s2 on s2.session_start = DATE_SUB(iq.session_start, INTERVAL $orgServiceDays DAY) and s2.store_id = iq.store_id and s2.is_deleted = 0 and s2.delivered_supports_shipping > 0
								order by iq.session_start limit $deliveryDaysLimit");

		while ($sessionFinder->fetch())
		{
			$retVal[] = $sessionFinder->id;
		}

		return $retVal;
	}

	function can_reschedule($shippingInfoObj = false, $target_session_id = false, $deliveryDaysLimit = 10)
	{

		// Rules for Delivered Orders
		// 1) Cannot reschedule if current delivery date is > delivery session date - service_days
		// example: delivery date = Friday 6/18 and service days = 2 then cannot reschedule beginning 12:00am 6/16
		// 2) Can not reschedule more than 10 delivery days from first viable day
		$validSessions = $this->getValidSessionsForShipping($shippingInfoObj->service_days, $shippingInfoObj->distribution_center, $deliveryDaysLimit);
		// TODO:  Check order status?
		// TODO:  What other constraints?

		if (in_array($target_session_id, $validSessions))
		{
			return true;
		}

		return false;
	}

	/**
	 * @return 'session full', 'closed', 'failed', 'success'
	 *
	 * This functions differently than reschedule() since a Saved order does not occupy a slot.
	 * We don't need to do the fancy locking etc. until the order is Activated
	 * @throws Exception
	 */
	function rescheduleSavedOrder($target_session_id, $Booking, $Order = false)
	{

		$this->query('START TRANSACTION;');

		try
		{
			if ($this->session)
			{
				CLog::Assert($target_session_id == $this->session->id, "Target session ID should matched attached session object");

				$CapacityExists = false;
				$Bookings = DAO_CFactory::create('booking');
				// Capacity for a Delivered Order is based on the shipping date session, the booking holds the deliveredDateSession
				$shippingInfo = $this->orderShipping();
				CLog::Assert(($shippingInfo->service_days && $shippingInfo->service_days > 0 && $shippingInfo->service_days < 3), "invalid service_days");

				$shippingDate = new DateTime($this->session->session_start);
				$shippingDate->modify("-{$shippingInfo->service_days} days");
				$shipping_session_id = CSession::getDeliveredSessionIDByDate($shippingDate->format("Y-m-d"), $this->session->store_id);

				$Bookings->query("SELECT status, booking_type FROM booking WHERE session_id= $shipping_session_id AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND is_deleted = 0");

				//count active bookings
				$StdBookCnt = 0;
				while ($Bookings->fetch())
				{
					if ($Bookings->status === CBooking::ACTIVE)
					{
						$StdBookCnt++;
					}
				}

				$shippingSession = new DAO();
				$shippingSession->query("select available_slots from session where id = " . $shipping_session_id);
				$shippingSession->fetch();
				$standard_capacity = $shippingSession->available_slots;

				if (!empty($standard_capacity) && ($standard_capacity - $StdBookCnt > 0))
				{
					$CapacityExists = true;
				}

				if (!$CapacityExists)
				{
					// TODO: Warn store that target session is full
				}

				$Booking->session_id = $target_session_id;
				$success = $Booking->update();

				$this->defaultShippingInfo($this->orderShipping->shipping_postal_code, $this->session, true);

				if ($success === false)
				{
					throw new Exception('data error ' . $Booking->_lastError->getMessage());
				}
				else if ($success === 0)
				{
					throw new Exception('nothing updated');
				}
			}
			else
			{ //closed; //no inserts
				$this->query('COMMIT;'); //end the transaction

				return 'failed';
			}
		}
		catch (exception $exc)
		{
			$this->query('ROLLBACK;');
			CLog::RecordException($exc);
			if (DEBUG)
			{
				throw $exc;
			}

			return 'failed';
		}

		$this->query('COMMIT;');

		return 'success';
	}

	/**
	 * @return 'session full', 'closed', 'failed', 'success'
	 * @throws Exception
	 */
	function reschedule($orignal_schedule_id, $fadmin_rules = true, $suppress_email = false)
	{

		$Booking = DAO_CFactory::create('booking');
		$Store = $this->getStore();

		$this->query('START TRANSACTION;');

		try
		{
			// CES 3/17/2006 Allow Fadmin to reschedule retroactively
			//add booking
			if ($this->session && ($fadmin_rules || (!$fadmin_rules && $this->session->isOpenForRescheduling($Store))))
			{

				$Booking->session_id = $this->session->id;
				$Booking->order_id = $this->id;
				$Booking->user_id = $this->user_id;
				$Booking->status = CBooking::HOLD;

				$shippingInfo = $this->orderShipping();
				CLog::Assert(($shippingInfo->service_days && $shippingInfo->service_days > 0 && $shippingInfo->service_days < 3), "invalid service_days");

				$shippingDate = new DateTime($this->session->session_start);
				$shippingDate->modify("-{$shippingInfo->service_days} days");
				$shipping_session_id = CSession::getDeliveredSessionIDByDate($shippingDate->format("Y-m-d"), $this->store_id);

				$id = $Booking->insert();
				if (!$id)
				{
					throw new Exception ('booking insert failed : data error ' . $Booking->_lastError->getMessage());
				}

				//lock booking table
				//to prevent this:
				//Thread 1: read available bookings (1 slot left)
				//Thread 2: read available bookings (1 slot left)
				//Thread 1: write booking (0 slot left)
				//Thread 2: write booking (-1 slot left)

				$CapacityExists = true;

				if ($this->order_type != self::DIRECT)
				{

					$CapacityExists = false;
					$BookingLocks = DAO_CFactory::create('booking');

					//find session capacity
					$shippingSession = new DAO();
					$shippingSession->query("select available_slots from session where id = " . $shipping_session_id);
					$shippingSession->fetch();
					$standard_capacity = $shippingSession->available_slots;
					$BookingLocks->query("SELECT * FROM booking WHERE session_id= $shipping_session_id AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND status != 'SAVED' AND booking_type <> 'INTRO' AND is_deleted = 0 FOR UPDATE");

					//count active bookings
					$bookCnt = 0;
					while ($BookingLocks->fetch())
					{
						if ($BookingLocks->status === CBooking::ACTIVE)
						{
							$bookCnt++;
						}
					}

					if ($bookCnt < $standard_capacity)
					{
						$CapacityExists = true;
					}
				}

				// CES 3/17/2006 Allow Fadmin to overbook
				if ($fadmin_rules || $CapacityExists)
				{

					//save booking
					$Booking->order_id = $this->id;
					$Booking->status = CBooking::ACTIVE;
					$success = $Booking->update();
					if (!$success)
					{
						throw new Exception('data error ' . $Booking->_lastError->getMessage());
					}

					$OrginalBooking = DAO_CFactory::create('booking');
					$OrginalBooking->order_id = $this->id;
					$OrginalBooking->whereAdd("status != 'CANCELLED' and status != 'RESCHEDULED' and status != 'SAVED'");

					if (!$OrginalBooking->find(true))
					{
						throw new Exception('data error ' . $Booking->_lastError->getMessage());
					}

					$OrginalBooking->status = CBooking::RESCHEDULED;
					$OrginalBooking->update();

					$shippingInfo->ship_date = $shippingDate->format("Y-m-d");

					$shippingInfo->requested_delivery_date = $this->findSession()->session_start;
					$shippingInfo->update();
				}
				else
				{ //full; delete HOLD booking //if ( $bookCnt < $capacity )
					//delete the booking
					$Booking->delete();
					$this->query('COMMIT;');

					return 'session full';
				}
			}
			else
			{ //closed; //no inserts
				$this->query('COMMIT;'); //end the transaction

				return 'closed';
			}
		}
		catch (exception $exc)
		{
			$this->query('ROLLBACK;');
			CLog::RecordException($exc);
			if (DEBUG)
			{
				throw $exc;
			}

			return 'failed';
		}

		$this->query('COMMIT;');
		$new_time = $this->findSession()->session_start;
		$new_type = $this->findSession()->session_type;
		$newID = $this->findSession()->id;
		$menuID = $this->findSession()->menu_id;

		COrdersDigest::recordRescheduledOrder($this, $new_time, $this->store_id, $newID, $menuID, $new_type);

		// Send email
		$User = DAO_CFactory::create('user');
		$User->id = $this->user_id;
		if (!$User->find(true))
		{
			throw new Exception("User not found in COrders::reschedule().");
		}

		$OrigSession = DAO_CFactory::create('session');
		$OrigSession->id = $orignal_schedule_id;
		if (!$OrigSession->find(true))
		{
			throw new Exception("Session not found in COrders::reschedule().");
		}

		if (!$suppress_email)
		{
			self::sendRescheduleEmail($User, $this, $OrigSession->session_start);
		}

		return 'success';
	}

	protected function calculateOtherTotals()
	{
		$this->pcal_sidedish_total = 0;
	}

	function isIntroOrder()
	{
		return false;
	}

	function isOrderEmpty()
	{

		if ($this->boxes && count($this->boxes))
		{
			return false;
		}

		if ($this->products || count($this->products))
		{
			return false;
		}

		return true;
	}

	function isObserveOnly()
	{
		return false;
	}

	static function getMenuItemsForOrder($ordersArray, $foodSearch = false, $since = false)
	{
		throw new Exception("Not implemented for Delivered");
		// this method should be restructured to call another function that both COrders and CDeliveredOrders should implement.

	}

	function verifyAdequateInventory()
	{
		$this->itemsAdjustedByInventory = null;

		if (!$this->boxes)
		{
			return true;
		}

		if (empty($this->boxes))
		{
			return true;
		}

		if (empty($this->store_id))
		{
			return true;
		}

		$menu_id = $this->getMenuId();
		if (empty($menu_id))
		{
			return true;
		}

		// every menu prior to April 2010 ignores inventory
		if ($menu_id < 104)
		{
			return true;
		}

		$serving_totals = array();

		$tempArray = array();

		foreach ($this->boxes as $box_instance_id => $boxInstanceArray)
		{
			if (!empty($boxInstanceArray["box_instance"]->is_complete))
			{
				foreach ($boxInstanceArray["items"] AS $itemArray)
				{
					list($qty, $DAO_menu_item) = $itemArray;

					$tempArray[] = $DAO_menu_item->id;

					if (!isset($serving_totals[$DAO_menu_item->recipe_id]))
					{
						$serving_totals[$DAO_menu_item->recipe_id] = 0;
					}

					$serving_totals[$DAO_menu_item->recipe_id] += ($qty * $DAO_menu_item->servings_per_item);
				}
			}
		}

		$inv_array = array();

		// INVENTORY TOUCH POINT 9
		$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory', true);
		$DAO_menu_item_inventory->menu_id = $menu_id;
		$DAO_menu_item_inventory->store_id = $this->store->parent_store_id;
		$DAO_menu_item_inventory->getMenuItemInventory($tempArray);
		$DAO_menu_item_inventory->find();

		while ($DAO_menu_item_inventory->fetch())
		{
			$inv_array[$DAO_menu_item_inventory->recipe_id] = $DAO_menu_item_inventory->remaining_servings;
		}

		$retVal = true;

		foreach ($serving_totals as $recipeID => $itemServingsTotal)
		{
			if ($itemServingsTotal > $inv_array[$recipeID])
			{
				$retVal = false;

				if (!isset($this->itemsAdjustedByInventory))
				{
					$this->itemsAdjustedByInventory = "";
				}

				$this->itemsAdjustedByInventory .= $recipeID . ",";

				if (!isset($this->itemsAdjustedByInventoryArray))
				{
					$this->itemsAdjustedByInventoryArray = array();
				}

				$this->itemsAdjustedByInventoryArray[$recipeID] = $recipeID;
			}
		}

		return $retVal;
	}

	function getUnderStockedItems()
	{
		if (!$this->boxes)
		{
			return false;
		}

		if (empty($this->boxes))
		{
			return false;
		}

		if (empty($this->store_id))
		{
			return false;
		}

		$menu_id = $this->getMenuId();
		if (empty($menu_id))
		{
			return false;
		}

		$removalList = array();

		if (empty($this->itemsAdjustedByInventoryArray))
		{
			return false;
		}

		foreach ($this->boxes as $box_instance_id => $boxInstanceArray)
		{
			if (!empty($boxInstanceArray["box_instance"]->is_complete))
			{
				foreach ($boxInstanceArray["items"] as $itemArray)
				{
					list($qty, $DAO_menu_item) = $itemArray;

					if (array_key_exists($DAO_menu_item->recipe_id, $this->itemsAdjustedByInventoryArray))
					{
						$removalList[$box_instance_id] = $box_instance_id;
					}
				}
			}
		}

		return $removalList;
	}

	function removeInitialInventory($menu_id = false)
	{
		$boxes = $this->getBoxes();
		$parentStoreId = CStore::getParentStoreID($this->session->store_id);

		//add order items
		if ($boxes)
		{

			$menu_id = current($boxes)['bundle']->menu_id;

			foreach ($boxes as $box_inst_id => $boxItems)
			{
				$bundleID = $boxItems['bundle']->id;

				foreach ($boxItems['items'] as $item)
				{
					list($qty, $menu_item) = $item;

					if ($qty && $menu_item->id)
					{
						try
						{
							// Deplete Inventory
							$servingQty = $menu_item->servings_per_item;

							if ($servingQty == 0)
							{
								$servingQty = 6;
							}

							if ($menu_item->is_chef_touched)
							{
								$servingQty = 1;
							}

							$servingQty *= $qty;
							// INVENTORY TOUCH POINT 3

							//subtract from inventory
							$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory');
							$DAO_menu_item_inventory->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold + $servingQty where mii.recipe_id = {$menu_item->recipe_id}
								and mii.store_id = $parentStoreId and mii.menu_id = $menu_id and mii.is_deleted = 0");
						}
						catch (exception $exc)
						{
							// don't allow a problem here to fail the order
							//log the problem
							CLog::RecordException($exc);

							$debugStr = "INV_CONTROL ISSUE- Order: " . $this->id . " | Item: " . $menu_item->id . " | Store: " . $this->store_id;
							CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
						}
					}
				}
			}
		}
	}

	static public function sendRescheduleEmail($user, $order, $origSessionTime): void
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order, null, false, false, false, true);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['origSessionInfo'] = array('session_start' => $origSessionTime);
		$orderInfo['details_page'] = 'order-details';
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		$contentsText = CMail::mailMerge('shipping/shipping_session_rescheduled.txt.php', $orderInfo);
		$contentsHtml = CMail::mailMerge('shipping/shipping_session_rescheduled.html.php', $orderInfo);

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, 'Order Rescheduled', $contentsHtml, $contentsText, '', '', $user->id, 'shipping_session_rescheduled');
	}

	function cancel($creditArray, $reason = false, $declinedMFY = false, $declinedReschedule = false, $suppressCancellationEmail = false, $cancelArray = false)
	{
		require_once("CTemplate.inc");

		$resultArray = array();
		if (!$this->can_cancel())
		{
			return false;
		}

		// ---------------------------------- get the boooking and mark it CANCELLED
		$booking = DAO_CFactory::create('booking');
		$booking->order_id = $this->id;
		$booking->whereAdd("status = 'ACTIVE'");
		if (!$booking->find(true))
		{
			throw new Exception("Booking not found in COrders::cancel().");
		}

		$User = DAO_CFactory::create('user');
		$User->id = $booking->user_id;
		$found = $User->find(true);
		if ((!$found) || ($found > 1))
		{
			throw new Exception("User not found in COrders::cancel().");
		}

		$sessionObj = $this->findSession();

		$this->query('START TRANSACTION;');

		try
		{

			$booking->status = CBooking::CANCELLED;

			if ($reason)
			{
				$booking->reason_for_cancellation = $reason;
			}
			if ($declinedMFY)
			{
				$booking->declined_MFY_option = 1;
			}
			if ($declinedReschedule)
			{
				$booking->declined_to_reschedule = 1;
			}

			$booking->update();

			// get the payments for this order, iterate over them and handle according to type

			if (!empty($creditArray))
			{
				foreach ($creditArray as $id => $v)
				{
					$payment = DAO_CFactory::create('payment');
					$payment->id = $id;
					if (!$payment->find(true))
					{
						throw new Exception("Payment not found in COrders::cancel().");
					}

					if ($payment->payment_type == CPayment::STORE_CREDIT)
					{
						$rsltMsg = $payment->processStoreCreditCancellation($sessionObj);
					}
					else if ($payment->payment_type == CPayment::GIFT_CARD)
					{
						// TODO: return gift card funds to card with Load
						//		$rsltMsg = $payment->convertDebitGiftCardPaymentToStoreCredit($this->id);

						$GC_trans = new DAO();
						$query = "select id, billing_email, cc_number, cc_type, cc_ref_number from gift_card_transaction where order_id = {$this->id} and transaction_id = '{$payment->payment_transaction_number}'";

						$GC_trans->query($query);
						$GC_trans->fetch();

						$acct_retreive = DAO_CFactory::create('gift_card_transaction');
						$acct_retreive->query("SET @GCAN = ''");
						$acct_retreive->query("Call get_gcan_trans({$GC_trans->id}, @GCAN)");
						$acct_retreive->query("SELECT @GCAN");
						$acct_retreive->fetch();
						$account = base64_decode(CCrypto::decode($acct_retreive->_GCAN));

						list($success, $message, $DDTransactionRowID) = CGiftCard::loadDebitGiftCardWithRetry($account, $payment->total_amount, $GC_trans->cc_ref_number, $GC_trans->cc_number, $GC_trans->cc_type, $GC_trans->billing_email, 'M');

						if ($success)
						{
							// also add a payment that reflects this refund
							$refundLine = DAO_CFactory::create('payment');

							$refundLine->user_id = $this->user_id;
							$refundLine->store_id = $this->store_id;
							$refundLine->order_id = $this->id;
							$refundLine->is_delayed_payment = 0;
							$refundLine->is_escrip_customer = 0;
							$refundLine->total_amount = $payment->total_amount;
							$refundLine->payment_number = substr($this->payment_number, strlen($this->payment_number) - 4);
							$refundLine->payment_type = CPayment::REFUND_GIFT_CARD;
							$refundLine->insert();
						}
						else
						{
							CLog::RecordNew(CLog::ERROR, "Error reloading Gift Card while cancelling Delivered Order: {$this->id} | Card # $account | Trans # {$payment->transaction_id} ", false, false, true);
						}
					}
					else
					{
						if ($v == 0)
						{
							$rsltMsg = 'Amount specified as 0.00 - crediting skipped';
						}
						else
						{
							$tempArray = $payment->processCredit($v);
							$rsltMsg = $tempArray['result'];
							$userText = $tempArray['userText'];
						}
					}

					$resultArray[$id] = "Crediting " . CPayment::getPaymentDescription($payment->payment_type) . " payment for $" . CTemplate::moneyFormat($v) . ".";

					if (!empty($userText))
					{
						$resultArray[$id] .= " Result : " . $userText;
					}
				}
			}
			else
			{
				$resultArray[0] = "Order successfully cancelled. No payments were credited.";
			}

			$session = DAO_CFactory::create('session');
			$session->query("select menu_id, store_id, session_start from session where id = {$booking->session_id} ");
			$session->fetch();

			$menu_id = $this->getMenuId();
			// add items back into inventory - (reduce number sold)

			$parentStoreID = CStore::getParentStoreID($this->store_id);

			$sessionIsInPast = $session->isInThePast();

			$order_item = DAO_CFactory::create('order_item');
			$order_item->query("SELECT oi.*, mi.servings_per_item, mi.is_chef_touched, mi.recipe_id FROM order_item oi JOIN menu_item mi ON mi.id = oi.menu_item_id WHERE oi.is_deleted = 0 AND oi.order_id = " . $this->id);

			while ($order_item->fetch() && $menu_id)
			{

				try
				{

					if (!empty($order_item->recipe_id))
					{
						//subtract from inventory - that is, increment number sold
						$servingQty = $order_item->servings_per_item;

						if ($servingQty == 0)
						{
							$servingQty = 6;
						}

						$servingQty *= $order_item->item_count;

						// INVENTORY TOUCH POINT 30

						//subtract from inventory
						$invItem = DAO_CFactory::create('menu_item_inventory');
						$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold - $servingQty where mii.recipe_id = {$order_item->recipe_id} and mii.store_id = $parentStoreID and mii.menu_id = $menu_id and mii.is_deleted = 0");
					}
				}
				catch (exception $exc)
				{
					// don't allow a problem here to fail the order
					//log the problem
					CLog::RecordException($exc);

					$debugStr = "INV_CONTROL ISSUE- Cancelled Order: " . $order_item->order_id . " | Item: " . $order_item->menu_item_id . " | Store: " . $this->store_id;
					CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
				}
			}

			//Unset minimum qualification on this order
			if ($this->is_qualifying == 1)
			{
				$this->is_qualifying = 0;
				$this->update();
			}
		}
		catch (exception $exc)
		{
			$this->query('ROLLBACK;');
			CLog::RecordException($exc);
			if (DEBUG)
			{
				throw $exc;
			}

			return false;
		}

		$this->query('COMMIT;');
		COrdersDigest::recordCanceledOrder($this->id, $this->store_id, $menu_id, $this->grand_total, $this->fundraiser_value, $this->subtotal_ltd_menu_item_value);

		// record event and un-consume credit
		CPointsCredits::handleOrderCancelled($User, $this);

		//Look to set qualification status on another order if one exists
		$User->establishMonthlyMinimumQualifyingOrder($sessionObj->menu_id, $this->store_id);

		//Try to Cancel Order in shipstation
		ShipStationManager::getInstanceForOrder($this)->addUpdateOrder(new ShipStationOrderWrapper($this));

		self::sendCancelEmail($User, $this);

		return $resultArray;
	}

	static public function sendEditedOrderConfirmationEmail($user, $order)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);

		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order-details';
		$orderInfo['customer_primary_email'] = $user->primary_email;
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		$subject = "Confirmation of your order changes";

		$contentsText = CMail::mailMerge('shipping/shipping_order_edited.txt.php', $orderInfo);
		$contentsHtml = CMail::mailMerge('shipping/shipping_order_edited.html.php', $orderInfo);

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, $subject, $contentsHtml, $contentsText, '', '', $user->id, 'order');

		// Send the store an email if there are special instructions...
		CEmail::alertStoreInstructions($orderInfo);
	}

	static public function sendCancelEmail($user, $order): void
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order-details';
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		$subject = "Order Canceled";

		$contentsText = CMail::mailMerge('shipping/shipping_order_cancelled.txt.php', $orderInfo);
		$contentsHtml = CMail::mailMerge('shipping/shipping_order_cancelled.html.php', $orderInfo);

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, $subject, $contentsHtml, $contentsText, '', '', $user->id, 'order_cancelled');
	}

	/**
	 * - Customers can edit and cancel an order 6+ days from the Delivery Date UNLESS the 6 days
	 *   before the Delivery Date is after a box is no longer available for sale.
	 *
	 * - Once a delivered box has “expired”, the order is “locked” for the guest and they cannot edit.
	 *
	 * - Once an order has a tracking code or new flag (TBD) checked, the order is locked for editing
	 *   no matter the previous rules
	 *
	 * - If an order has a direct order discount applied, it is locked for editing
	 *
	 *
	 * @param $order mixed either COrdersDelivered or order ID (to fetch COrdersDelivered)
	 *
	 * @return bool true if rules are met, false if rules
	 *              are not met or if the passed order is not of type COrdersDelivered
	 * @throws Exception if in debug mode
	 */
	public static function canEditDeliveredOrder($order)
	{

		if (is_string($order))
		{
			$orderId = $order;
		}
		else if (is_a($order, 'COrdersDelivered'))
		{
			$orderId = $order->id;
		}
		else
		{
			return false;
		}

		try
		{
			$orderInfo = DAO_CFactory::create('orders');
			$orderInfo->query("select o.direct_order_discount, o.type_of_order , 
				os.status as shipping_status, os.requested_delivery_date, os.tracking_number,
				b.booking_type, b.status as booking_status,
				s.store_id, s.menu_id, s.session_type, s.session_start,
				bx.availability_date_end
				from orders o, orders_shipping os, booking b, session s , box_instance bi, box bx
				where o.id = {$orderId}
				and o.id = os.order_id
				and o.id = b.order_id
				and o.id = bi.order_id
				and b.session_id = s.id
				and bx.id = bi.box_id
				and os.status = 'NEW'
				and o.is_deleted = 0");

			$timeZone = new DateTimeZone('America/New_York');
			$now = new DateTime('NOW', $timeZone);

			while ($orderInfo->fetch())
			{
				$selectedDeliveryDate = new DateTime($orderInfo->session_start, $timeZone);
				$daysBeforeDeliveryDate = $now->diff($selectedDeliveryDate);
				$boxAvailabilityEndDate = new DateTime($orderInfo->availability_date_end, $timeZone);
				$daysBeforeAvailabilityEndDate = $now->diff($boxAvailabilityEndDate);

				if ($daysBeforeDeliveryDate->days > 5 && $daysBeforeAvailabilityEndDate->days >= 0 && $orderInfo->direct_order_discount == 0)
				{
					return true;
				}
			}
		}
		catch (exception $exc)
		{
			CLog::RecordException($exc);
			if (DEBUG)
			{
				throw $exc;
			}
		}

		return false;
	}

	function isShipping()
	{
		return true;
	}

	function isDelivered()
	{
		return $this->isShipping();
	}

	public function isInEditOrder()
	{
		return $this->isInEditOrder;
	}

	public function setIsInEditOrder($val)
	{
		$this->isInEditOrder = $val;
	}
}

?>