<?php
/*
 * Created on Oct 20, 2005
 *
 * Stores an order temporarily along with the payment info
 */
require_once('includes/DAO/BusinessObject/CBrowserSession.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');
require_once('includes/DAO/BusinessObject/CMenu.php');
require_once('includes/DAO/BusinessObject/COrdersDelivered.php');
require_once('includes/CCartStorage.inc');
require_once('includes/CLog.inc');

class CCart2
{

	static private $_instance = null;

	static public function instance($full_restore = true, $external_key = false, $force_new = false)
	{
		if (!self::$_instance || $force_new == true)
		{
			self::$_instance = new CCart2($full_restore, $external_key);
		}

		return self::$_instance;
	}

	static public function instanceFromOrder($orderId, $full_restore = true, $external_key = false, $force_new = false)
	{

		if (empty($orderId))
		{
			//load per usual if not edit order
			return CCart2::instance($full_restore, $external_key, $force_new);
		}
		else
		{
			//Always create a new instance if we are restoring order because only
			//the restored order will be allowed in the cart
			self::$_instance = new CCart2($full_restore, $external_key, $orderId);
		}

		return self::$_instance;
	}

	static public function tempAccess()
	{
		return new CCart2(false);
	}

	private $_cart_contents_id = null;

	private $_PaymentArray = null;
	private $_registration_data = null;
	private $_gift_card_orders = null;
	private $_menu_items = null;
	private $_delivered_boxes = null;
	private $_products = null;
	private $_OrderObj = null;
	private $_menu_id = null;
	private $_session_id = null;
	private $_navigation_type = null; // think session path
	private $_postal_code = null;
	private $_direct_invite = null;
	private $_edit_order_id = null;

	private $_storage = null;
	private $_isFullyConstructed = false;
	private $_hasHadStoreCredit = false; // this is set to true if store credit is added to the cart and never set t0 false

	/**
	 *
	 */
	private function __construct($fullRestore = true, $external_key = false, $existingOrderId = null)
	{
		$this->_storage = new CCartStorage();
		$this->_cart_contents_id = $this->connectTemporaryCartToContents($external_key);

		if (!$this->_cart_contents_id)
		{
			$this->establishTemporaryCart();
			if ($fullRestore)
			{
				$this->initData();
				if (!empty($existingOrderId))
				{
					$this->createCartStorageFromOrder($existingOrderId);
					$this->restoreContents();
				}
			}
		}
		else if (!empty($existingOrderId))
		{
			$this->createCartStorageFromOrder($existingOrderId);
			$this->restoreContents();
		}
		else if ($fullRestore)
		{
			$this->restoreContents();
		}
	}

	function rebuildCart()
	{
		$this->initData();
		$this->restoreContents();
	}

	function getCartDAO()
	{
		return $this->_cartDAO;
	}

	function getDebugInfo()
	{
		// returns the current database settings; assumes cart is not fully constructed
		return $this->_storage->getDebugViewOfDatabase($this->_cart_contents_id);
	}

	function validateForCheckout()
	{

		if (!$this->_isFullyConstructed)
		{
			throw new Exception("Cannot validate unConstructed cart.");
		}

		// can always buy only a gift card
		if (!empty($this->_gift_card_orders))
		{
			return true;
		}

		if (empty($this->_OrderObj->store_id))
		{
			return 'No store found in cart';
		}

		if (empty($this->_session_id))
		{
			return 'No session found in cart';
		}

		if (empty($this->_menu_items) && empty($this->_gift_card_orders) && empty($this->_products) && empty($this->_delivered_boxes))
		{
			return 'No items found in cart';
		}

		return true;
	}

	private function initData()
	{

		$this->_PaymentArray = array();
		$this->_registration_data = array();
		$this->_gift_card_orders = array();
		$this->_menu_items = array();
		$this->_delivered_boxes = array();
		$this->_products = array();
		$this->_OrderObj = new COrders();

		$this->_menu_id = null;
		$this->_session_id = null;
		$this->_navigation_type = null;
		$this->_postal_code = null;
		$this->_direct_invite = null;
		$this->_edit_order_id = null;
		$this->_OrderObj->order_customization = null;
		$this->_OrderObj->direct_order_discount = 0;
		$this->_OrderObj->points_discount_total = 0;
		$this->_OrderObj->misc_food_subtotal = 0;
		$this->_OrderObj->misc_nonfood_subtotal = 0;
		$this->_OrderObj->subtotal_service_fee = 0;
		$this->_OrderObj->subtotal_meal_customization_fee = 0;
		$this->_OrderObj->is_sampler = 0;
		$this->_OrderObj->is_TODD = 0;
		$this->_OrderObj->opted_to_customize_recipes = null;

		$this->_OrderObj->ltd_round_up_value = 0;

		$this->_isFullyConstructed = true;
	}

	/*
	 cart array(
			 cart_info array(
					 has_store
					 has_session
					 has_item
					 has_payment
					 has_gift_cards
			 )
			 store_info array (
					 store data
			 )
			 session_info (
					 session_start
			 )
			 payment_info (
					 payment data
			 )
			 order_info (
					 order data
			 )
			 coupon (
					 coupon data
			 )
			 gift_card_info (
				 gift_card	(
					 card_data
					 )
			 )
	 )
	 */

	function get_cart_info_array()
	{
		$retVal = array();

		$retVal['has_navigation_type'] = (!empty($this->_navigation_type));

		if ($this->_OrderObj->isShipping())
		{
			$items_in_cart = $this->_OrderObj->getBoxes();
		}
		else
		{
			$items_in_cart = $this->_OrderObj->getItems();
		}

		$retVal['has_store'] = (!empty($this->_OrderObj->store_id));
		$retVal['has_session'] = (!empty($this->_session_id));
		$retVal['has_item'] = (!empty($this->_products) || !empty($this->_gift_card_orders));

		$validFoodItemFound = false;
		$validItemFound = false;
		if ($this->_OrderObj->isShipping())
		{
			// menu item check is more involved
			if (!empty($this->_delivered_boxes))
			{
				foreach ($this->_delivered_boxes as $thisArray)
				{
					if (!empty($thisArray) && !empty($thisArray['box_instance']->is_complete))
					{
						$validItemFound = true;
						$validFoodItemFound = true;
					}
				}
			}
		}
		else
		{
			// menu item check is more involved
			if (!empty($this->_menu_items))
			{
				foreach ($this->_menu_items as $thisArray)
				{
					if (!empty($thisArray))
					{
						$validItemFound = true;
					}
				}
			}
		}

		if ($validItemFound)
		{
			$retVal['has_item'] = true;
		}

		$retVal['has_payment'] = (!empty($this->_PaymentArray));
		$retVal['servings_total_count'] = $this->_OrderObj->servings_total_count;
		$retVal['servings_core_total_count'] = $this->_OrderObj->servings_core_total_count;
		$retVal['has_gift_cards'] = (!empty($this->_gift_card_orders));

		if ($this->_OrderObj->isShipping())
		{
			$retVal['has_food'] = $validFoodItemFound;
		}
		else
		{
			$retVal['has_food'] = (!empty($this->_menu_items)); // above the test is for a food item ensures that a menu_item element is an array/
			// why don't we do that here?
		}
		$retVal['six_serving_total_count'] = 0;
		$retVal['three_serving_total_count'] = 0;
		$retVal['four_serving_total_count'] = 0;
		$retVal['two_serving_total_count'] = 0;
		$retVal['session_type'] = $this->_navigation_type; // old key
		$retVal['navigation_type'] = $this->_navigation_type;
		$retVal['direct_invite'] = $this->_direct_invite;
		$retVal['num_sides'] = 0;
		$retVal['postal_code'] = $this->_postal_code;

		if (!empty($items_in_cart))
		{
			if ($this->_OrderObj->isShipping())
			{
				foreach ($items_in_cart as $thisBox)
				{
					if ($thisBox['box_instance']->is_complete)
					{
						foreach ($thisBox['items'] as $itemInfo)
						{
							if ($itemInfo[1]->pricing_type == 'FULL')
							{
								$retVal['six_serving_total_count'] = $retVal['six_serving_total_count'] + $itemInfo[0];
							}
							else if ($itemInfo[1]->pricing_type == 'HALF')
							{
								$retVal['three_serving_total_count'] = $retVal['three_serving_total_count'] + $itemInfo[0];
							}
						}
					}
				}
			}
			else
			{
				$retVal['num_efl'] = 0;
				$retVal['num_sides'] = 0;

				foreach ($items_in_cart as $menu_item)
				{
					// don't count a bundle master_item as a dinner
					// only the bundle sub items count as dinners
					if (!empty($menu_item['1']->is_bundle))
					{
						continue;
					}

					if ($menu_item['1']->isMenuItem_Core())
					{
						switch ($menu_item['1']->pricing_type)
						{
							case CMenuItem::FULL:
								$retVal['six_serving_total_count'] = $retVal['six_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
							case CMenuItem::HALF:
								$retVal['three_serving_total_count'] = $retVal['three_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
							case CMenuItem::FOUR:
								$retVal['four_serving_total_count'] = $retVal['four_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
							case CMenuItem::TWO:
								$retVal['two_serving_total_count'] = $retVal['two_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
						}
					}

					if ($menu_item['1']->isMenuItem_EFL())
					{
						$retVal['num_efl'] = $retVal['num_efl'] + $menu_item['0'];

						switch ($menu_item['1']->pricing_type)
						{
							case CMenuItem::FULL:
								$retVal['six_serving_total_count'] = $retVal['six_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
							case CMenuItem::HALF:
								$retVal['three_serving_total_count'] = $retVal['three_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
							case CMenuItem::FOUR:
								$retVal['four_serving_total_count'] = $retVal['four_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
							case CMenuItem::TWO:
								$retVal['two_serving_total_count'] = $retVal['two_serving_total_count'] + ($menu_item['0'] * $menu_item[1]->item_count_per_item);
								break;
						}
					}

					if ($menu_item['1']->isMenuItem_SidesSweets())
					{
						$retVal['num_sides'] = $retVal['num_sides'] + $menu_item['0'];
					}
				}
			}
		}

		$retVal['dinners_total_count'] = $retVal['six_serving_total_count'] + $retVal['three_serving_total_count'] + $retVal['four_serving_total_count'] + $retVal['two_serving_total_count'];

		if (isset($this->_OrderObj->bundle_id) && $this->_OrderObj->bundle_id > 0)
		{
			$Bundle = $this->_OrderObj->getBundleObj();

			$needed = $Bundle->number_servings_required - $retVal['servings_total_count'];
		}
		else if ($this->_OrderObj->isShipping())
		{
			// TODO: need to look at each boxes bundle to establish servings_needed
			$needed = 0;
		}
		else
		{
			if (CURRENT_PLATE_POINTS_VERSION > 1)
			{
				$needed = 36 - $retVal['servings_core_total_count'];
			}
			else
			{
				$needed = 36 - $retVal['servings_total_count'];
			}
		}
		$retVal['servings_needed'] = ($needed < 0) ? 0 : $needed;

		return $retVal;
	}

	function getCartArrays()
	{
		$retVal = array();

		$retVal['cart_info_array'] = $this->get_cart_info_array();

		if ($retVal['cart_info_array']['has_store'])
		{
			$storeObj = DAO_CFactory::create('store');
			$storeObj->id = $this->_OrderObj->store_id;

			if (!$storeObj->find(true))
			{
				CLog::RecordNew(CLog::DEBUG, "Store not found in getCartArrays() : " . $this->_OrderObj->store_id);
			}
			else
			{
				$retVal['store_info'] = COrders::buildStoreInfoArray($storeObj);

				$retVal['storeObj'] = $storeObj;
			}
		}

		$isDeliveredOrder = false;

		$retVal['sessionObj'] = DAO_CFactory::create('session');

		if ((!empty($this->_session_id)))
		{
			$retVal['sessionObj']->id = $this->_session_id;
			$retVal['sessionObj']->find(true);

			$retVal['session_info'] = $retVal['sessionObj']->toArray();
			$retVal['session_info']['session_type_subtype'] = 'NONE';

			if ($retVal['sessionObj']->isDelivery())
			{
				$retVal['session_info']['session_type_subtype'] = CSession::DELIVERY;
			}

			if ($retVal['sessionObj']->isDelivered())
			{
				$isDeliveredOrder = true;
			}
		}
		else
		{
			if ($retVal['cart_info_array']['navigation_type'] == CTemplate::DELIVERED)
			{
				$isDeliveredOrder = true;
			}
		}

		if ($retVal['cart_info_array']['has_payment'])
		{
			$retVal['payment_info'] = $this->_PaymentArray;
		}

		$UserObj = $this->_OrderObj->getUser();

		$this->_OrderObj->refresh($UserObj, $this->_menu_id);
		$this->_OrderObj->recalculate($isDeliveredOrder);

		$totalItemsCost = 0;

		if ($retVal['cart_info_array']['has_item'])
		{
			list ($retVal['item_info'], $totalItemsCost) = $this->_OrderObj->getCartDisplayArrays();

			$retVal['entree_info'] = array();

			if ($isDeliveredOrder)
			{
				foreach ($retVal['item_info'] as $box_id => $box)
				{
					foreach ($box['items'] as $menuItem)
					{
						$retVal['entree_info'][$box_id][$menuItem['entree_id']][$menuItem['menu_item_id']]['qty'] = $menuItem['qty'];
					}
				}
			}
			else
			{
				foreach ($retVal['item_info'] as $menuItem)
				{
					$retVal['entree_info'][$menuItem['entree_id']][$menuItem['menu_item_id']]['qty'] = $menuItem['qty'];
				}
			}
		}

		$retVal['cart_info_array']['total_items_price'] = $totalItemsCost;

		$retVal['order_info'] = $this->_OrderObj->toArray();

		$retVal['orderObj'] = $this->getOrder();

		if (!empty($this->_OrderObj->coupon_code_id))
		{
			$CouponObj = $this->_OrderObj->getCoupon();
			$retVal['coupon'] = array(
				'coupon_code_title' => $CouponObj->coupon_code_title,
				'coupon_code_short_title' => $CouponObj->coupon_code_short_title,
				'coupon_code' => $CouponObj->coupon_code,
				'limit_to_mfy_fee' => $CouponObj->limit_to_mfy_fee,
				'limit_to_delivery_fee' => $CouponObj->limit_to_delivery_fee
			);
		}

		if ($retVal['cart_info_array']['has_gift_cards'])
		{
			$card_designs = CGiftCard::getActiveGCDesignArray();

			$tempArray = array();
			foreach ($this->_gift_card_orders as $gcoid)
			{
				$GCOrderObj = DAO_CFactory::create('gift_card_order');
				$GCOrderObj->id = $gcoid;
				$GCOrderObj->find(true);

				$thisDesign = $card_designs['designs'][$GCOrderObj->design_type_id];

				$imagePath = (($GCOrderObj->media_type == 'PHYSICAL') ? $thisDesign['image_path'] : $thisDesign['image_path_virtual']);
				$imagePath = IMAGES_PATH . "/gift_cards/" . $imagePath;

				$DescString = "To: {$GCOrderObj->to_name}<br />";
				$DescString .= "From: {$GCOrderObj->from_name}<br />";

				if (!empty($GCOrderObj->message_text))
				{
					$DescString .= "Message: {$GCOrderObj->message_text}<br />";
				}

				if ($GCOrderObj->media_type == 'PHYSICAL')
				{
					$DescString .= "Ship To: {$GCOrderObj->first_name} {$GCOrderObj->last_name}<br />";

					$DescString .= "Shipping Address: {$GCOrderObj->shipping_address_1}";
					if (!empty($GCOrderObj->shipping_address_2))
					{
						$DescString .= ", {$GCOrderObj->shipping_address_2}";
					}

					$DescString .= " {$GCOrderObj->shipping_city}, {$GCOrderObj->shipping_state} {$GCOrderObj->shipping_zip}<br />";
				}

				if ($GCOrderObj->media_type == 'VIRTUAL')
				{
					$DescString .= "Recipient Address: {$GCOrderObj->recipient_email_address}";
				}

				$tempArray[$gcoid] = array(
					'order_id' => $GCOrderObj->id,
					'gc_name' => strip_tags($GCOrderObj->first_name . " " . $GCOrderObj->last_name),
					'to_name' => strip_tags($GCOrderObj->to_name),
					'from_name' => strip_tags($GCOrderObj->from_name),
					'message_text' => strip_tags($GCOrderObj->message_text),
					'shipping_address_1' => strip_tags($GCOrderObj->shipping_address_1),
					'shipping_address_2' => strip_tags($GCOrderObj->shipping_address_2),
					'init_cost' => $GCOrderObj->initial_amount,
					'gc_amount' => $GCOrderObj->initial_amount + ($GCOrderObj->media_type == 'VIRTUAL' ? 0 : COrders::GIFT_CARD_SHIPPING),
					'media_type' => ($GCOrderObj->media_type == 'VIRTUAL' ? 'Virtual eGift Card' : 'Traditional Gift Card'),
					'image_path' => $imagePath,
					'address' => ($GCOrderObj->media_type == 'VIRTUAL' ? $GCOrderObj->recipient_email_address : $GCOrderObj->shipping_address_1),
					'desc' => $DescString
				);
			}

			$retVal['gift_card_info'] = $tempArray;
		}

		$retVal['show_price_and_servings'] = ((empty($this->_OrderObj->bundle_id)) ? true : false);
		$retVal['menu_id'] = $this->_menu_id;

		$retVal['menuObj'] = DAO_CFactory::create('menu');
		$retVal['menuObj']->query("SELECT * FROM `menu` WHERE id = '" . $this->_menu_id . "' AND is_deleted = '0'");
		$retVal['menuObj']->fetch();

		$retVal['menu_info'] = $retVal['menuObj']->toArray();

		return $retVal;
	}

	function changeEventPostalCode($new_postal_code)
	{
		if ($this->_postal_code != $new_postal_code)
		{
			$ckzip = DAO_CFactory::create('zipcodes');
			$ckzip->zip = $new_postal_code;
			$ckzip->whereAdd("zipcodes.distribution_center IS NOT NULL");

			if ($ckzip->find(true))
			{
				$this->addPostalCode($ckzip->zip);

				if ($this->_OrderObj->store_id != $ckzip->distribution_center)
				{
					$this->storeChangeEvent($ckzip->distribution_center);
				}
			}
		}
	}

	function storeChangeEvent($newStore, $allowBoxesToSurvive = false)
	{
		if ($this->_OrderObj->store_id != $newStore)
		{
			$this->_OrderObj->store_id = $newStore;

			$this->_OrderObj->clearSession();
			$this->_OrderObj->ltd_round_up_value = null;
			$this->_session_id = null;
			$this->_direct_invite = null;

			if (!$allowBoxesToSurvive)
			{
				$this->_delivered_boxes = null;
			}

			$this->_OrderObj->refresh(null, $this->_menu_id);

			$this->flush();
		}
	}

	function flush()
	{
		$this->_storage->storeOrder($this->_cart_contents_id, $this->_OrderObj, $this->_menu_id);
		$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", ($this->_session_id ? $this->_session_id : 0));
		$this->_storage->storeOrderValue($this->_cart_contents_id, "navigation_type", ($this->_navigation_type ? $this->_navigation_type : 0));
		$this->_storage->storeOrderValue($this->_cart_contents_id, "postal_code", ($this->_postal_code ? $this->_postal_code : 0));
		$this->_storage->storeOrderValue($this->_cart_contents_id, "direct_invite", ($this->_direct_invite ? $this->_direct_invite : 0));
		$this->_storage->storeOrderValue($this->_cart_contents_id, "has_added_store_credit", ($this->_hasHadStoreCredit ? 1 : 0));

		foreach ($this->_PaymentArray as $pid => &$thePayment)
		{
			$this->_storage->storePayment($this->_cart_contents_id, $pid, $thePayment['payment_type'], $thePayment['amount'], $thePayment['paymentData'], $thePayment['tempData']);
		}

		if (!empty($this->_menu_items))
		{
			$this->_storage->storeArray($this->_cart_contents_id, $this->_menu_items, 'MENU_ITEMS', $this->_menu_id);
		}

		if (!empty($this->_registration_data))
		{
			$this->_storage->storeArray($this->_cart_contents_id, $this->_registration_data, 'ACC_DATA');
		}

		if (!empty($this->_gift_card_orders))
		{
			$this->_storage->storeArray($this->_cart_contents_id, $this->_gift_card_orders, 'GIFT_CARDS');
		}

		if (!empty($this->_products))
		{
			$this->_storage->storeArray($this->_cart_contents_id, $this->_products, 'PRODUCTS');
		}
	}

	function expandBoxArrays()
	{

		$outerArray = &$this->_delivered_boxes;
		$Order = $this->_OrderObj;

		$totalItemQty = 0;
		//add menu items
		if (!empty($outerArray))
		{
			foreach ($outerArray as $boxInstanceID => $boxData)
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

				$outerArray[$boxInstanceID]['box_instance'] = $boxInstanceObj;

				foreach ($boxData['items'] as $id => $qty)
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
						if (is_array($qty))
						{
							$qty = $qty[0];
						}
						$totalItemQty += $qty;
						$Order->addItemToBox($boxInstanceObj, $MenuItem, $qty);
					}
				}
			}
		}

		//add products
		if (!empty($this->_products))
		{
			$Product = DAO_CFactory::create('product');
			$select = "SELECT product.* FROM product ";
			$where = "where product.id IN (" . implode(",", array_keys($this->_products)) . ") AND  product.is_deleted = 0";

			$Product->query($select . $where);

			while ($Product->fetch())
			{
				if ($Product->id)
				{
					$Order->addProduct(clone($Product), $this->_products[$Product->id]);
				}
			}
		}
	}

	function expandItemArrays()
	{
		$outerArray = &$this->_menu_items;
		$Order = $this->getOrder();

		//add menu items
		if ($this->_menu_id && !empty($outerArray) && !empty($Order->store_id))
		{
			$promoItem = null;
			$freeMealItem = null;

			//remake array
			$tempArray = array();

			// look for items coded to be promos )(has "p_" in front of the code)
			foreach ($outerArray['mitems'] as $id => $qty)
			{
				if (strpos($id, 'p_') === 0)
				{
					$promoItem = substr($id, 2);
					$qty--;
					if ($qty)
					{
						$tempArray[$promoItem] = $qty;
					}
				}
				else if (strpos($id, 'f_') === 0)
				{
					$freeMealItem = substr($id, 2);
					$qty--;
					if ($qty)
					{
						$tempArray[$freeMealItem] = $qty;
					}
				}
				else
				{
					$tempArray[$id] = $qty;
				}
			}

			$outerArray['mitems'] = $tempArray;

			$DAO_Menu = DAO_CFactory::create('menu');
			$DAO_Menu->id = $this->_menu_id;

			$inItemList = implode(",", array_keys($outerArray['mitems']));

			$getStoreMenu = CMenu::storeSpecificMenuExists($this->_menu_id, $Order->store_id);

			if ($getStoreMenu)
			{
				$DAO_menu_item = $DAO_Menu->getMenuItemDAO('FeaturedFirst', $Order->store_id, false, false, false, false, false, false, $inItemList); //returns the associated menu item query
			}
			else
			{
				$DAO_menu_item = $DAO_Menu->getMenuItemDAO('FeaturedFirst', false, false, false, false, false, false, false, $inItemList); //returns the associated menu item query
			}

			if ($DAO_menu_item->N != count($outerArray['mitems']))
			{
				//CES 1/21/08 -- this can happen now if the store has removed items since the cart was filled.
				//CES 4/25/08 -- but now constrain the panic and don't throw here
				CLog::RecordNew(CLog::DEBUG, "Menu items in cart do not match main array.");
			}

			$storeObj = new DAO();
			$storeObj->query("select supports_ltd_roundup from store where id = {$Order->store_id}");
			$storeObj->fetch();

			$hasMarkup = false;
			$MarkUp = DAO_CFactory::create('mark_up_multi');
			$MarkUp->store_id = $Order->store_id;
			$num = $MarkUp->findActive($this->_menu_id);
			if ($num)
			{
				$MarkUp->fetch();
				$hasMarkup = true;
			}

			while ($DAO_menu_item->fetch())
			{
				if(array_key_exists($DAO_menu_item->id, $outerArray['mitems']))
				{
					$Order->addMenuItem(clone($DAO_menu_item), $outerArray['mitems'][$DAO_menu_item->id]);
				}
			}

			// special case for promo item
			if ($promoItem)
			{
				$PromoMenuItem = DAO_CFactory::create('menu_item');

				if ($getStoreMenu)
				{
					$query = "SELECT 
						mmi.override_price as override_price, 
						mi.* 
						FROM menu_item  mi 
						left join menu_to_menu_item mmi on mi.id = mmi.menu_item_id and mmi.store_id = " . $Order->store_id . " and mmi.is_deleted = 0 and mmi.menu_id = " . $this->_menu_id . " 
						WHERE mi.id = $promoItem and mi.is_deleted = 0";
				}
				else
				{
					$query = "SELECT 
						mmi.override_price as override_price, 
						mi.* 
						FROM menu_item  mi 
						left join menu_to_menu_item mmi on mi.id = mmi.menu_item_id and mmi.store_id is null and mmi.is_deleted = 0 and mmi.menu_id = " . $this->_menu_id . " 
						WHERE mi.id = $promoItem and mi.is_deleted = 0";
				}

				$PromoMenuItem->query($query);

				if ($PromoMenuItem->fetch())
				{
					$Order->addMenuItem(clone($PromoMenuItem), 1, true);
				}
			}

			// special case for coupon coed free meal item
			if ($freeMealItem)
			{
				$FreeMealMenuItem = DAO_CFactory::create('menu_item');

				if ($getStoreMenu)
				{
					$query = "SELECT mmi.override_price as override_price, mi.* 
						FROM menu_item  mi  
						left join menu_to_menu_item mmi on mi.id = mmi.menu_item_id and mmi.store_id = " . $Order->store_id . " and mmi.is_deleted = 0  and mmi.menu_id =" . $this->_menu_id . " 
						WHERE mi.id = $freeMealItem and mi.is_deleted = 0";
				}
				else
				{
					$query = "SELECT mmi.override_price as override_price, mi.* 
						FROM menu_item  mi  left join menu_to_menu_item mmi on mi.id = mmi.menu_item_id and mmi.store_id is null and mmi.is_deleted = 0 and mmi.menu_id =" . $this->_menu_id . " 
						WHERE mi.id = $freeMealItem and mi.is_deleted = 0";
				}

				$FreeMealMenuItem->query($query);

				if ($FreeMealMenuItem->fetch())
				{
					$Order->addMenuItem(clone($FreeMealMenuItem), 1, false, true);
				}
			} // free meal

			// restore bundle properties, i.e., Set parentItemId and Quantity of Children
			if (!empty($outerArray['mBundleMap']))
			{
				foreach ($outerArray['mBundleMap'] as $parentID => $children)
				{
					foreach ($children as $id => $childQty)
					{
						$Order->updateBundleItem($id, $parentID, $childQty);
					}
				}
			}
		}

		//add products
		if (!empty($this->_products))
		{
			$Product = DAO_CFactory::create('product');
			$select = "SELECT product.* FROM product ";
			$where = "where product.id IN (" . implode(",", array_keys($this->_products)) . ") AND  product.is_deleted = 0";

			$Product->query($select . $where);

			while ($Product->fetch())
			{
				if ($Product->id)
				{
					$Order->addProduct(clone($Product), $this->_products[$Product->id]);
				}
			}
		}

		if (!empty($Order->bundle_id))
		{
			$selectedItems = false;
			if (isset($outerArray) && array_key_exists('mbundleitems', $outerArray))
			{
				$selectedItems = $outerArray['mbundleitems'];
			}

			if (!empty($selectedItems))
			{
				$Order->addBundleWithID($Order->bundle_id, $selectedItems, true);
			}
		}
	}

	function hasCreditCardPayment()
	{
		// assumes fully constructed cart

		foreach ($this->_PaymentArray as $payment)
		{
			$type = strtolower($payment['payment_type']);
			if ($type == "credit_card" || $type == "cc")
			{
				return true;
			}
		}

		return false;
	}

	function hasGiftCard()
	{
		// meant to be called when cart is not fully constructed
		if ($this->_isFullyConstructed)
		{
			return !empty($this->_gift_card_orders);
		}

		return !($this->_storage->testForEmptyArray($this->_cart_contents_id, 'GIFT_CARDS'));
	}

	function menuChangeEvent($newID)
	{
	}

	function clearMenuItems($clearBundleID = true, $clearCache = false, $deleteArrays = false)
	{
		//assumes the cart is not fully constructed
		$this->_storage->clearAllArraysOfType($this->_cart_contents_id, 'MENU_ITEMS', $deleteArrays);
		//assumes the cart is not fully constructed
		$this->_storage->clearAllArraysOfType($this->_cart_contents_id, 'BOXES', $deleteArrays);

		if ($clearBundleID)
		{
			$this->_storage->storeOrderValue($this->_cart_contents_id, "bundle_id", 0);
			$this->_storage->storeOrderValue($this->_cart_contents_id, "navigation_type", CTemplate::ALL_STANDARD);
		}

		$this->_storage->storeOrderValue($this->_cart_contents_id, "coupon_code_id", null);

		$isDeliveredOrder = false;
		if (!empty($this->_navigation_type) && $this->_navigation_type == CTemplate::DELIVERED)
		{
			$isDeliveredOrder = true;
		}

		if ($clearCache)
		{
			if ($isDeliveredOrder)
			{
				unset($this->_delivered_boxes);
				$this->_delivered_boxes = array();
			}
			else
			{
				unset($this->_menu_items);
				$this->_menu_items = array();
			}
		}
	}

	function clearAllPayments($clearCache = false)
	{
		if ($clearCache)
		{
			unset($this->_PaymentArray);
			$this->_PaymentArray = array();
		}

		$this->_storage->clearAllPayments($this->_cart_contents_id);
	}

	function clearGiftCards()
	{
		//assumes the cart is not fully constructed
		$this->_storage->clearAllArraysOfType($this->_cart_contents_id, 'GIFT_CARDS');
	}

	function isShippingAddressPostalCodeEmptyOrEqualTo($newZip)
	{
		$this->_OrderObj->OrderAddress();

		$ShippingAddressArray = $this->_storage->retrieveArray($this->_cart_contents_id, 'SHIPPING_ADDRESS');

		if (!empty($ShippingAddressArray))
		{
			if (!empty($ShippingAddressArray['shipping_postal_code']) && $newZip != $ShippingAddressArray['shipping_postal_code'])
			{
				return false;
			}
		}

		return true;
	}

	private function createCartStorageFromOrder($orderId)
	{

		$tempOrder = new COrdersDelivered();

		$tempOrder->id = $orderId;
		$tempOrder->find(true);

		$CustObj = null;
		if (!empty($tempOrder->user_id))
		{
			$CustObj = DAO_CFactory::create('user');
			$CustObj->id = $tempOrder->user_id;
			$CustObj->find(true);
		}

		$tempOrder->refresh($CustObj);
		$addr = $tempOrder->OrderAddress();
		$tempOrder->OrderShipping();
		$tempOrder->reconstruct();

		$this->_storage->storeOrder($this->_cart_contents_id, $tempOrder, '');

		$this->_storage->storeArray($this->_cart_contents_id, $tempOrder->getBoxes(), 'BOXES');

		$this->_storage->storeOrderAddress($this->_cart_contents_id, $addr);

		$this->_storage->storeOrderValue($this->_cart_contents_id, 'navigation_type', CTemplate::DELIVERED);
		$this->_storage->storeOrderValue($this->_cart_contents_id, 'edit_order_id', $orderId);
		$this->_storage->storeOrderValue($this->_cart_contents_id, 'postal_code', $addr->postal_code);

		$PaymentObj = DAO_CFactory::create('payment');
		//$PaymentObj->query("SELECT * from payment where order_id = ".$orderId ." having max(timestamp_created)");

		$PaymentObj->query("SELECT * from payment where order_id = " . $orderId . " and is_deleted = '0'");

		$this->clearAllPayments(true);
		$count = 1;
		while ($PaymentObj->fetch())
		{

			$paymentArr = array(
				'payment_id' => $PaymentObj->id,
				'payment_type' => $PaymentObj->payment_type,
				'payment_reference' => $PaymentObj->payment_transaction_number,
				'cc_number' => $PaymentObj->payment_number,
				'card_type' => $PaymentObj->credit_card_type,
				'maid' => $PaymentObj->merchant_account_id,
				'date' => strtotime($PaymentObj->timestamp_created),
				'total' => $PaymentObj->total_amount,
				'last_result' => 'successful',
				'store_id' => $PaymentObj->store_id,
				'previous_payment_id' => $PaymentObj->referent_id,
				'card_count' => $count++
			);
			$this->_storage->storePayment($this->_cart_contents_id, $PaymentObj->id, strtolower($PaymentObj->payment_type), $PaymentObj->total_amount, $paymentArr, '');
		}
	}

	function restoreContents()
	{

		// TODO: Consider combining queries for better performance
		list($this->_OrderObj, $this->_menu_id, $this->_session_id, $this->_navigation_type, $this->_direct_invite, $this->_hasHadStoreCredit, $this->_postal_code, $this->_edit_order_id) = $this->_storage->retrieveOrderObject($this->_cart_contents_id);

		if ($this->_session_id)
		{
			$sessionObj = DAO_CFactory::create('session');
			$sessionObj->id = $this->_session_id;
			if (!$sessionObj->find(true))
			{
				$this->clearMenuItems();
				$this->addSessionId(0);
			}
			else
			{
				if (isset($_REQUEST['page']) && $_REQUEST['page'] == "checkout")
				{
					if ($this->getNavigationType() != CTemplate::DELIVERED)
					{
						if ($sessionObj->menu_id != $this->_menu_id)
						{
							$this->_menu_id = $sessionObj->menu_id;
							$this->_storage->storeOrderValue($this->_cart_contents_id, "menu_id", $this->_menu_id);
						}
					}
				}

				$this->_OrderObj->addSession($sessionObj);
			}
		}

		$this->_PaymentArray = $this->_storage->retrieveAllPayments($this->_cart_contents_id);

		$this->_products = $this->_storage->retrieveArray($this->_cart_contents_id, 'PRODUCTS');
		if ($this->_navigation_type == CTemplate::DELIVERED)
		{
			$this->_delivered_boxes = $this->_storage->retrieveArray($this->_cart_contents_id, 'BOXES');
			$this->expandBoxArrays();
		}
		else
		{
			$this->_menu_items = $this->_storage->retrieveArray($this->_cart_contents_id, 'MENU_ITEMS', $this->_menu_id);
			$this->_gift_card_orders = $this->_storage->retrieveArray($this->_cart_contents_id, 'GIFT_CARDS');
			$this->_registration_data = $this->_storage->retrieveArray($this->_cart_contents_id, 'ACC_DATA');

			$this->expandItemArrays();
		}

		if ($this->_OrderObj->findSession() && ($this->_OrderObj->findSession()->isDelivery() || $this->_OrderObj->findSession()->isDelivered()))
		{
			// instantiate the object
			$this->_OrderObj->orderAddress();

			$ShippingAddressArray = $this->_storage->retrieveArray($this->_cart_contents_id, 'SHIPPING_ADDRESS');

			// Populate orderAddress using cart data
			if (!empty($ShippingAddressArray))
			{
				$shipping_phone_number = $ShippingAddressArray['shipping_phone_number'];
				$shipping_phone_number_new = $ShippingAddressArray['shipping_phone_number_new'];
				$shipping_address_note = $ShippingAddressArray['shipping_address_note'];

				$this->_OrderObj->orderAddress->firstname = $ShippingAddressArray['shipping_firstname'];
				$this->_OrderObj->orderAddress->lastname = $ShippingAddressArray['shipping_lastname'];
				$this->_OrderObj->orderAddress->address_line1 = $ShippingAddressArray['shipping_address_line1'];
				$this->_OrderObj->orderAddress->address_line2 = $ShippingAddressArray['shipping_address_line2'];
				$this->_OrderObj->orderAddress->city = $ShippingAddressArray['shipping_city'];
				$this->_OrderObj->orderAddress->state_id = $ShippingAddressArray['shipping_state_id'];
				$this->_OrderObj->orderAddress->postal_code = $ShippingAddressArray['shipping_postal_code'];
				$this->_OrderObj->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
				$this->_OrderObj->orderAddress->address_note = trim(strip_tags($shipping_address_note));
				$this->_OrderObj->orderAddress->is_gift = $ShippingAddressArray['shipping_is_gift'];
				$this->_OrderObj->orderAddress->email_address = $ShippingAddressArray['shipping_gift_email_address'];

				if (!empty($this->_OrderObj->orderAddress->is_gift))
				{
					$this->_OrderObj->my_meals_rating_user_id = $this->_OrderObj->user_id;
				}
				else
				{
					$this->_OrderObj->my_meals_rating_user_id = null;
				}
			}
		}
		else
		{
			$this->_OrderObj->my_meals_rating_user_id = $this->_OrderObj->user_id;
		}

		$this->_gift_card_orders = $this->_storage->retrieveArray($this->_cart_contents_id, 'GIFT_CARDS');
		$this->_registration_data = $this->_storage->retrieveArray($this->_cart_contents_id, 'ACC_DATA');

		$CustObj = null;
		if (!empty($this->_OrderObj->user_id))
		{
			$CustObj = DAO_CFactory::create('user');
			$CustObj->id = $this->_OrderObj->user_id;
			$CustObj->find(true);
		}

		$this->_OrderObj->family_savings_discount_version = 2;

		$this->_OrderObj->getCoupon();

		$this->_OrderObj->refresh($CustObj);
		$this->_OrderObj->recalculate();

		$this->_isFullyConstructed = true;
	}

	/**
	 * Check for cart id in cookie
	 */
	function isFull()
	{
		return CBrowserSession::getCartKey();
	}

	/*  ADD FUNCTIONS
	 * 	All add functions will cache their data unless the parameter "directStore" is set to true. In this case the expectation is that the cart has not been fully constructed
	 *  and that the data is meant to be written to the backing store quickly
	 */

	function updateDinnerDollarsDirect($newAmount)
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "points_discount_total", $newAmount);
	}

	function updateLTDAmountDirect($newAmount)
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "ltd_round_up_value", $newAmount);
	}

	function addIsIntro($isIntro, $directStore = false)
	{
		$isIntro = ($isIntro ? "1" : "0");

		$this->_storage->storeOrderValue($this->_cart_contents_id, "is_sampler", $isIntro);

		if (!$directStore)
		{
			$this->_OrderObj->is_sampler = $isIntro;
		}
	}

	function addOrderAddress($orderAddress)
	{
		$this->_storage->storeOrderAddress($this->_cart_contents_id, $orderAddress);
	}

	function addBundleId($bid, $directStore = false)
	{

		$this->_storage->storeOrderValue($this->_cart_contents_id, "bundle_id", $bid);

		if (!$directStore)
		{
			$this->_OrderObj->bundle_id = $bid;
		}
	}

	function addMenuId($menu_id, $directStore = false)
	{
		// guest is changing their session, they are no longer directly invited
		$old_menu_id = $this->getMenuId();
		if (!empty($old_menu_id) && $old_menu_id != $menu_id)
		{
			$this->clearDirectInvite();
		}

		$this->_storage->storeOrderValue($this->_cart_contents_id, "menu_id", $menu_id);

		if ($menu_id != $this->_menu_id && !$directStore)
		{
			$this->_menu_items = $this->_storage->retrieveArray($this->_cart_contents_id, 'MENU_ITEMS', $menu_id);
		}

		if (!$directStore)
		{
			$this->_menu_id = $menu_id;
		}
	}

	function addBagOptOut($bag_option, $directStore = false)
	{
		//NOTE: null means not supported by store
		// 1 means guest opted to bring bags
		// 0 means opted out
		$this->_storage->storeOrderValue($this->_cart_contents_id, "opted_to_bring_bags", $bag_option);

		if (!$directStore)
		{
			$this->_OrderObj->opted_to_bring_bags = $bag_option;
		}
	}

	function addMealCustomizationOptOut($customization_option, $directStore = false)
	{
		//NOTE: null means not supported by store
		// 1 means guest opted to allow customization
		// 0 means opted out
		$this->_storage->storeOrderValue($this->_cart_contents_id, "opted_to_customize_recipes", $customization_option);

		if (!$directStore)
		{
			$this->_OrderObj->opted_to_customize_recipes = $customization_option;
		}
	}

	function addMealCustomizationOptionToOrder($customization, $directStore = false)
	{
		//NOTE: null means not supported by store
		// 1 means guest opted to allow customization
		// 0 means opted out
		$this->_storage->storeOrderValue($this->_cart_contents_id, "order_customization", $customization);

		if (!$directStore)
		{
			$this->_OrderObj->order_customization = $customization;
		}
	}

	function removeMealCustomizationOptOut()
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "opted_to_customize_recipes", 0);
	}

	function removeOrderCustomizationOptions()
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "order_customization", '');
	}

	function addUserId($user_id, $directStore = false)
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "user_id", $user_id);

		if (!$directStore)
		{
			$this->_OrderObj->user_id = $user_id;
		}
	}

	function removeItem($menu_id, $menu_item_id, $isIntro = false, $isDreamTasteEvent = false)
	{
		$this->updateMenuItem($menu_item_id, 0, $menu_id, true, $isIntro, false, $isDreamTasteEvent);

		$DAO_order = $this->getOrder();
		$DAO_coupon_code = $DAO_order->getCoupon();

		if (!empty($DAO_coupon_code->limit_to_recipe_id) && !empty($DAO_coupon_code->menu_item_id) && $DAO_coupon_code->menu_item_id = $menu_item_id)
		{
			$this->_OrderObj->removeCoupon();
			$this->_OrderObj->refresh($DAO_order->getUser());
			$this->_OrderObj->recalculate();

			$this->addOrder($this->_OrderObj);
		}
	}

	function updateMenuItem($menu_item_id, $qty, $menu_id, $directStore = false, $isIntro = false, $parent_item_id = false, $isDreamTasteEvent = false, $updateCache = false, $addQtyToExisting = false)
	{
		if ($qty < 0)
		{
			$qty = 0;
		}

		// currently, only called with directStore true
		if ($directStore)
		{
			$tempArray = $this->_storage->retrieveArray($this->_cart_contents_id, 'MENU_ITEMS', $menu_id);
		}
		else
		{

			if ($menu_id == $this->_menu_id)
			{
				$tempArray = &$this->_menu_items;
			}
			else
			{
				// TODO
				throw new Exception("updateMenuItem not supported with fully constructed cart");
			}
		}

		if ($isDreamTasteEvent)
		{
			$origBundleQty = (isset($tempArray['mbundleitems'][$menu_item_id]) ? $tempArray['mbundleitems'][$menu_item_id] : 0);
			if ($qty == 0)
			{

				$origQty = (isset($tempArray['mitems'][$menu_item_id]) ? $tempArray['mitems'][$menu_item_id] : 0);

				if (isset($tempArray['mbundleitems'][$menu_item_id]))
				{
					unset($tempArray['mbundleitems'][$menu_item_id]);
				}

				if (isset($tempArray['mitems'][$menu_item_id]))
				{
					$tempArray['mitems'][$menu_item_id] = $origQty - $origBundleQty;
				}
				if (isset($tempArray['mitems'][$menu_item_id]) && $tempArray['mitems'][$menu_item_id] == 0)
				{
					unset($tempArray['mitems'][$menu_item_id]);
				}
			}
			else
			{

				$tempArray['mbundleitems'][$menu_item_id] = $qty;

				if (isset($tempArray['mitems'][$menu_item_id]))
				{
					$origQty = $tempArray['mitems'][$menu_item_id];
					$tempArray['mitems'][$menu_item_id] = ($origQty - $origBundleQty) + $qty;
				}
				else
				{
					$tempArray['mitems'][$menu_item_id] = $qty;
				}
			}
		}
		else if ($isIntro)
		{
			if ($qty == 0)
			{
				if (isset($tempArray['mbundleitems'][$menu_item_id]))
				{
					unset($tempArray['mbundleitems'][$menu_item_id]);
				}
				if (isset($tempArray['mitems'][$menu_item_id]))
				{
					$tempArray['mitems'][$menu_item_id]--;
				}
				if (isset($tempArray['mitems'][$menu_item_id]) && $tempArray['mitems'][$menu_item_id] == 0)
				{
					unset($tempArray['mitems'][$menu_item_id]);
				}
			}
			else
			{
				$tempArray['mbundleitems'][$menu_item_id] = $qty;
				if (isset($tempArray['mitems'][$menu_item_id]))
				{
					$tempArray['mitems'][$menu_item_id]++;
				}
				else
				{
					$tempArray['mitems'][$menu_item_id] = 1;
				}
			}
		}
		else
		{
			if ($qty == 0)
			{
				if ($parent_item_id)
				{
					// if item is a member of a bundle we must reduce both the item in the flat list and in the children array

					// get the current total qty
					$totalQty = $tempArray['mitems'][$menu_item_id];
					// get the current bundled Qty
					$bundleQty = (isset($tempArray['mBundleMap'][$parent_item_id][$menu_item_id]) ? $tempArray['mBundleMap'][$parent_item_id][$menu_item_id] : 0);

					// downward adjustment of the main list
					$totalQty -= $bundleQty;
					if ($totalQty <= 0)
					{
						unset($tempArray['mitems'][$menu_item_id]);
					}
					else
					{
						$tempArray['mitems'][$menu_item_id] = $totalQty;
					}

					// and delete the child
					unset($tempArray['mBundleMap'][$parent_item_id][$menu_item_id]);
				}
				else
				{
					$bundleQty = 0;
					// if the item is ordered as part ofa bundle we need that quantity for the final adjustment
					// we don't have a parent id, so we have to search
					if (!empty($tempArray['mBundleMap']))
					{
						foreach ($tempArray['mBundleMap'] as $children)
						{
							foreach ($children as $chid => $qty)
							{
								if ($menu_item_id == $chid)
								{
									$bundleQty += $qty;
								}
							}
						}
					}

					if (isset($tempArray['mBundleMap'][$menu_item_id]))
					{
						foreach ($tempArray['mBundleMap'][$menu_item_id] as $child_id => $child_qty)
						{
							if (isset($tempArray['mitems'][$child_id]))
							{
								$tempArray['mitems'][$child_id] -= $child_qty;

								if ($tempArray['mitems'][$child_id] <= 0)
								{
									unset($tempArray['mitems'][$child_id]);
								}
							}
						}

						unset($tempArray['mBundleMap'][$menu_item_id]);
					}

					if ($bundleQty == 0)
					{
						if (isset($tempArray['mitems'][$menu_item_id]))
						{
							unset($tempArray['mitems'][$menu_item_id]);
						}
					}
					else
					{
						if (isset($tempArray['mitems'][$menu_item_id]))
						{
							$tempArray['mitems'][$menu_item_id] = $bundleQty;
						}
					}
				}
			}
			else if ($qty > 0)
			{
				if ($parent_item_id)
				{
					// get current children qty
					$bundleQty = (isset($tempArray['mBundleMap'][$parent_item_id][$menu_item_id]) ? $tempArray['mBundleMap'][$parent_item_id][$menu_item_id] : 0);

					if ($addQtyToExisting)
					{
						$qty = $qty + $bundleQty;
					}

					$difference = $qty - $bundleQty;

					$tempArray['mBundleMap'][$parent_item_id][$menu_item_id] = $qty;

					// adjust the current total qty
					$tempArray['mitems'][$menu_item_id] += $difference;
				}
				else
				{

					// if this item is listed as a parent we must wipe out the children if decrementing
					if (isset($tempArray['mBundleMap'][$menu_item_id]))
					{
						$curBundleQuantity = $tempArray['mitems'][$menu_item_id];

						if ($qty < $curBundleQuantity)
						{
							foreach ($tempArray['mBundleMap'][$menu_item_id] as $sid => $sqty)
							{
								$tempArray['mitems'][$sid] -= $sqty;

								if ($tempArray['mitems'][$sid] <= 0)
								{
									unset($tempArray['mitems'][$sid]);
								}

								unset($tempArray['mBundleMap'][$menu_item_id][$sid]);
							}
						}
						$tempArray['mitems'][$menu_item_id] = $qty;
					}
					else
					{

						$bundleQty = 0;
						// if the item is ordered as part of a bundle we need that quantity for the final adjustment
						// we don't have a parent id, so we have to search
						if (!empty($tempArray['mBundleMap']))
						{
							foreach ($tempArray['mBundleMap'] as $children)
							{
								foreach ($children as $chid => $childqty)
								{
									if ($menu_item_id == $chid)
									{
										$bundleQty += $childqty;
									}
								}
							}
						}

						$tempArray['mitems'][$menu_item_id] = $qty + $bundleQty;
					}
				}
			}
		}

		if ($directStore)
		{
			$this->_storage->storeArray($this->_cart_contents_id, $tempArray, "MENU_ITEMS", $menu_id);
		}

		if ($updateCache)
		{
			$this->_menu_items = $tempArray;
		}
	}

	function addDeliveredBox($box_inst_id, $box_data, $directStore = false)
	{
		// box data =
		// array(bundle_id (int) , items (array), ...)
		$tempArray = $this->_storage->retrieveArray($this->_cart_contents_id, 'BOXES');

		if (empty($tempArray))
		{
			$tempArray = array();
			$tempArray[$box_inst_id] = $box_data;
		}
		else if (empty($tempArray[$box_inst_id]))
		{
			$tempArray[$box_inst_id] = $box_data;
		}

		$this->_storage->storeArray($this->_cart_contents_id, $tempArray, "BOXES");

		if (!$directStore)
		{
			$this->_delivered_boxes = $tempArray;
		}
	}

	function removeDeliveredBox($box_instance_id, $box_data = false, $directStore = false)
	{
		// box data =
		// array(bundle_id (int) , items (array), ...)
		$tempArray = $this->_storage->retrieveArray($this->_cart_contents_id, 'BOXES');

		if (empty($tempArray))
		{
			return; // no harm, no foul
		}
		else if (empty($tempArray[$box_instance_id]))
		{
			return; // no harm, no foul
		}

		unset($tempArray[$box_instance_id]);

		$this->_storage->storeArray($this->_cart_contents_id, $tempArray, "BOXES");

		if (!$directStore)
		{
			$this->_delivered_boxes = $tempArray;
		}
	}

	function addItemToDeliveredBox($box_inst_id, $item_id, $qty, $directStore = false)
	{

		$tempArray = $this->_storage->retrieveArray($this->_cart_contents_id, 'BOXES');

		if (empty($tempArray))
		{
			// error
		}
		if (empty($tempArray[$box_inst_id]))
		{
			// error
		}

		if (isset($tempArray[$box_inst_id]['items'][$item_id]) && $qty == 0)
		{
			unset($tempArray[$box_inst_id]['items'][$item_id]);
		}
		else if ($qty != 0)
		{
			$tempArray[$box_inst_id]['items'][$item_id] = $qty;
		}

		$this->_storage->storeArray($this->_cart_contents_id, $tempArray, "BOXES");

		if (!$directStore)
		{
			$this->_delivered_boxes = $tempArray;
		}
	}

	function addSessionId($session_id, $directStore = false)
	{
		// guest is changing their session, they are no longer directly invited
		if ($this->getDirectInvite() != $session_id)
		{
			$this->clearDirectInvite();
		}

		$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", $session_id);

		if (!$directStore)
		{
			$this->_session_id = $session_id;
		}
	}

	function clearSession($directStore = false)
	{
		$this->clearDirectInvite();

		$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);

		if (!$directStore)
		{
			$this->_session_id = null;
		}
	}

	function clearStore($directStore = false)
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "store_id", null);

		if (!$directStore)
		{
			$this->store_id = null;
		}
	}

	function addPostalCode($postal_code, $directStore = false)
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "postal_code", $postal_code);

		if (!$directStore)
		{
			$this->_postal_code = $postal_code;
		}
	}

	function getPostalCode()
	{
		return $this->_postal_code;
	}

	function addNavigationType($navigation_type, $directStore = false, $clearExisting = true)
	{
		if ($clearExisting)
		{
			$this->addSessionId(null);

			// guest is changing their session type, they are no longer directly invited
			$old_navigation_type = $this->getNavigationType();

			// they can switch between standard or intro because they can be the same session id
			switch ($navigation_type)
			{
				case CTemplate::STANDARD:
				case CTemplate::ALL_STANDARD:
				case CTemplate::SPECIAL_EVENT:
				case CTemplate::MADE_FOR_YOU:
				case CTemplate::DELIVERY:
					if ($old_navigation_type != CTemplate::INTRO)
					{
						$this->clearDirectInvite();
					}
					if ($old_navigation_type == CTemplate::DELIVERED)
					{
						$this->clearMenuItems(true, true, true);
					}
					break;
				case CTemplate::INTRO:
					if ($old_navigation_type != CTemplate::STANDARD)
					{
						$this->clearDirectInvite();
					}
					break;
				case CTemplate::DELIVERED:
					$this->clearDirectInvite();
					if ($old_navigation_type != CTemplate::DELIVERED)
					{
						$this->clearMenuItems(true, true, true);
						$this->_storage->storeOrderValue($this->_cart_contents_id, "subtotal_service_fee", 0);
					}
					break;
				default:
					if (!empty($old_navigation_type) && $old_navigation_type != $navigation_type)
					{
						// Note: Added test for a change: Prior to that this was clearing menu items when just selecting a different session of the same type.
						// TODO: I think we can dial this in further as some transitions may not require clearing items. Also some cases above may actually require clearing

						$this->clearMenuItems();
						$this->clearDirectInvite();
					}
					break;
			}
		}

		$this->_storage->storeOrderValue($this->_cart_contents_id, "navigation_type", $navigation_type);

		if (!$directStore)
		{
			$this->_navigation_type = $navigation_type;

			if ($navigation_type == CTemplate::DELIVERED && (empty($this->_OrderObj) || get_class($this->_OrderObj) != 'COrdersDelivered'))
			{
				// need to copy across critical data - store id is one , are there others?
				$tempOrder = new COrdersDelivered();
				if (!empty($this->_OrderObj->store_id))
				{
					$tempOrder->store_id = $this->_OrderObj->store_id;
				}

				$this->_OrderObj = $tempOrder;
			}
		}
	}

	function addDirectInvite($direct_invite, $directStore = false)
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "direct_invite", $direct_invite);

		if (!$directStore)
		{
			$this->_direct_invite = $direct_invite;
		}
	}

	function clearDirectInvite()
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "direct_invite", 0);
	}

	function addSpecialInstructions($instructions, $directStore = false)
	{
		$xssFilter = new InputFilter();
		$instructions = $xssFilter->process($instructions);

		$instructions = trim(strip_tags($instructions));

		if (empty($instructions))
		{
			return;
		}

		$this->_storage->storeOrderValue($this->_cart_contents_id, "order_user_notes", $instructions);

		if (!$directStore)
		{
			$this->_OrderObj->order_user_notes = $instructions;
		}
	}

	function clearShippingAddress($alsoDeleteArrays = false)
	{
		$this->_storage->clearAllArraysOfType($this->_cart_contents_id, 'SHIPPING_ADDRESS', $alsoDeleteArrays);

		if (isset($this->_OrderObj->orderAddress))
		{
			unset($this->_OrderObj->orderAddress);
		}
	}

	function clearDeliveredBoxes($alsoDeleteArrays = false)
	{
		$this->_storage->clearAllArraysOfType($this->_cart_contents_id, 'BOXES', $alsoDeleteArrays);

		if (isset($this->_OrderObj->_delivered_boxes))
		{
			unset($this->_OrderObj->_delivered_boxes);
		}
	}

	function addShippingAddress($ShippingAddressArray, $directStore = false)
	{
		$xssFilter = new InputFilter();
		$ShippingAddressArray = $xssFilter->process($ShippingAddressArray);

		$this->_storage->storeArray($this->_cart_contents_id, $ShippingAddressArray, 'SHIPPING_ADDRESS');

		if (!$directStore)
		{

			$shipping_phone_number = $ShippingAddressArray['shipping_phone_number'];
			$shipping_phone_number_new = $ShippingAddressArray['shipping_phone_number_new'];
			$shipping_address_note = $ShippingAddressArray['shipping_address_note'];

			$this->_OrderObj->orderAddress();
			$this->_OrderObj->orderAddress->firstname = $ShippingAddressArray['shipping_firstname'];
			$this->_OrderObj->orderAddress->lastname = $ShippingAddressArray['shipping_lastname'];
			$this->_OrderObj->orderAddress->address_line1 = $ShippingAddressArray['shipping_address_line1'];
			$this->_OrderObj->orderAddress->address_line2 = $ShippingAddressArray['shipping_address_line2'];
			$this->_OrderObj->orderAddress->city = $ShippingAddressArray['shipping_city'];
			$this->_OrderObj->orderAddress->state_id = $ShippingAddressArray['shipping_state_id'];
			$this->_OrderObj->orderAddress->postal_code = $ShippingAddressArray['shipping_postal_code'];
			$this->_OrderObj->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
			$this->_OrderObj->orderAddress->address_note = trim(strip_tags($shipping_address_note));
			$this->_OrderObj->orderAddress->is_gift = $ShippingAddressArray['shipping_is_gift'];
			$this->_OrderObj->orderAddress->email_address = $ShippingAddressArray['shipping_gift_email_address'];
		}
	}

	function addStoreCredit($Arr, $directStore = false)
	{
		// the payment array is needed to determine whether the store credit already exists in the cart
		$this->_PaymentArray = $this->_storage->retrieveAllPayments($this->_cart_contents_id);

		foreach ($Arr as $id => $SCPayment)
		{
			// check to see if the store credit is in the payment list
			$canAdd = true;
			foreach ($this->_PaymentArray as $data)
			{
				if ($data['payment_type'] == 'store_credit' && $data["paymentData"]["store_credit_id"] == $id)
				{
					$canAdd = false;
					break;
				}
			}

			if ($canAdd)
			{
				$this->_storage->storePayment($this->_cart_contents_id, 0, 'store_credit', $SCPayment['total_amount'], $SCPayment, null);

				if (!$directStore)
				{
					$this->_PaymentArray[$SCPayment['payment_id']] = array(
						'payment_type' => $SCPayment['payment_type'],
						'paymentData' => $SCPayment,
						"amount" => $SCPayment['total_amount'],
						'tempData' => null
					);
				}
			}
		}

		$this->_hasHadStoreCredit = true;
		$this->_storage->storeOrderValue($this->_cart_contents_id, "has_added_store_credit", 1);
	}

	function removeAllStoreCredit()
	{

		$this->_storage->deleteStoreCredits($this->_cart_contents_id);

		// assumes cart is not restored
		if ($this->_isFullyConstructed)
		{
			$newArray = array();
			foreach ($this->_PaymentArray as $id => $thisPayment)
			{
				if ($thisPayment['payment_type'] != 'store_credit')
				{
					$newArray[$id] = $thisPayment;
				}
			}

			$this->_PaymentArray = $newArray;
		}
	}

	/* Array elements must be labeled as follows
	 * $Arr
	 *	payment_id
	 *	payment_type
	 *	total_amount
	 *	--- anything else
	 *
	 *	$tempArr
	 *	--- anything, it will be restored exactly as stored
	 */

	function addPayment(&$Arr, $temp_data = false, $directStore = false)
	{
		$this->_storage->storePayment($this->_cart_contents_id, $Arr['payment_id'], $Arr['payment_type'], $Arr['total_amount'], $Arr, $temp_data);

		if (!$directStore)
		{
			$this->_PaymentArray[$Arr['payment_id']] = array(
				'payment_type' => $Arr['payment_type'],
				//TODO: evanl this was PaymentType camel-case but I'm not sure it was on purpose? Everywhere else in the cart it is payment_type?
				'paymentData' => $Arr,
				"amount" => $Arr['total_amount'],
				'tempData' => $temp_data
			);
		}
	}

	function addRegistrationData($regData, $directStore = false)
	{
		$this->_storage->storeArray($this->_cart_contents_id, $regData, "ACC_DATA");

		if (!$directStore)
		{
			$this->_registration_data = $regData;
		}
	}

	function addGiftCardOrder($order_id, $directStore = false)
	{

		$tempArray = array();
		if (isset($this->_gift_card_orders))
		{
			$tempArray = $this->_gift_card_orders;
		}

		$tempArray[] = $order_id;

		$this->_storage->storeArray($this->_cart_contents_id, $tempArray, "GIFT_CARDS");

		if (!$directStore)
		{
			$this->_gift_card_orders = $tempArray;
		}
	}

	/**
	 * Add an order to the cart, clears out any previous orders
	 */
	function addOrder($OrderObj, $directStore = false)
	{
		$menuID = false;
		if ($OrderObj->findSession())
		{

			$this->_session_id = $OrderObj->findSession()->id;

			$menuID = $OrderObj->findSession()->menu_id;

			if (!$directStore)
			{

				if (empty($this->_menu_id) && $menuID != null)
				{
					$this->_menu_id = $menuID;
				}
			}
		}

		if (!isset($OrderObj->volume_discount_id))
		{
			$OrderObj->volume_discount_id = 'null';
		}

		$this->_OrderObj = $OrderObj;

		$this->_storage->storeOrder($this->_cart_contents_id, $this->_OrderObj, $menuID);

		$this->storeOrdersMenuItems($this->_OrderObj);
	}

	function removeNonCreditPayments()
	{
		// assumes fully contructed cart
		$tempArray = array();
		foreach ($this->_PaymentArray as $id => $data)
		{
			if ($data['payment_type'] == "credit_card")
			{
				$tempArray[$id] = $data;
			}
			else
			{
				$this->_storage->removePayment($id);
			}
		}

		$this->_PaymentArray = $tempArray;
	}

	function removeGiftCardOrder($order_id)
	{
		$newArray = array();
		if ($this->_isFullyConstructed)
		{

			foreach ($this->_gift_card_orders as $orderNum)
			{
				if ($orderNum != $order_id)
				{
					$newArray[] = $orderNum;
				}
			}

			$this->_gift_card_orders = $newArray;
		}
		else
		{
			$tempArray = $this->_storage->retrieveArray($this->_cart_contents_id, 'GIFT_CARDS');
			foreach ($tempArray as $orderNum)
			{
				if ($orderNum != $order_id)
				{
					$newArray[] = $orderNum;
				}
			}
		}

		$this->_storage->storeArray($this->_cart_contents_id, $newArray, "GIFT_CARDS");
	}

	function removeBundleId()
	{
		if ($this->_isFullyConstructed)
		{
			$this->_OrderObj->bundle_id = 0;
			$this->_OrderObj->bundle_discount = 0;
		}

		$this->_storage->storeOrderValue($this->_cart_contents_id, "bundle_id", 0);
	}

	function removePayment($payment_id)
	{

		if ($this->_isFullyConstructed)
		{
			unset($this->_PaymentArray[$payment_id]);
		}

		$this->_storage->removePayment($payment_id);
	}

	function getBundleID()
	{
		if (!empty($this->_OrderObj->bundle_id) && is_numeric($this->_OrderObj->bundle_id))
		{
			return $this->_OrderObj->bundle_id;
		}

		return false;
	}

	function getSessionId()
	{
		return $this->_session_id;
	}

	function getIsIntro()
	{
		return $this->_OrderObj->is_sampler;
	}

	function getMenuId()
	{
		return $this->_menu_id;
	}

	// more accurate name than getSessionType()
	function getNavigationType()
	{
		return $this->_navigation_type;
	}

	function getDirectInvite()
	{
		if (empty($this->_direct_invite))
		{
			return false;
		}

		return $this->_direct_invite;
	}

	function getEditOrderId()
	{
		if (empty($this->_edit_order_id))
		{
			return false;
		}

		return $this->_edit_order_id;
	}

	function clearEditOrderCart()
	{
		$this->clearDeliveredBoxes(true);
		$this->clearShippingAddress(true);
		$this->clearMenuItems();
		$this->clearAllPayments();

		$this->clearEditOrderId();
		CBrowserSession::setValue('EDIT_DELIVERED_ORDER');
	}

	function clearEditOrderId()
	{
		$this->_storage->storeOrderValue($this->_cart_contents_id, "edit_order_id", '');
	}

	function cart_sanity_check($page = 'any')
	{

		$result = array(
			'status' => "all_good",
			'problem_list' => array()
		);

		if (isset($this->_OrderObj->store_id) && is_numeric($this->_OrderObj->store_id))
		{
			$DAO_store = DAO_CFactory::create('store');
			$DAO_store->id = $this->_OrderObj->store_id;
			$DAO_store->active = 1;

			if ($DAO_store->find(true))
			{
				// good store so ....
				if (!empty($this->_navigation_type))
				{
					if (!empty($this->_session_id))
					{
						$sessionTest = new DAO();
						$sessionTest->query("select session_type, store_id, menu_id, session_start, session_password from session where id = {$this->_session_id}");
						$sessionTest->fetch();

						if ($sessionTest->menu_id != $this->_menu_id && $this->_navigation_type != CTemplate::DELIVERED)
						{
							$this->_session_id = null;
							$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);
							$result['status'] = "session_removed";
							$result['problem_list'][] = 'Session menu ID does not match cart so removed session';
							CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
						}

						if ($sessionTest->store_id != $this->_OrderObj->store_id)
						{
							$this->_session_id = null;
							$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);

							$result['status'] = "session_removed";
							$result['problem_list'][] = 'Session store does not match order so removed session';
							CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
						}

						if ($this->_navigation_type == CTemplate::STANDARD && $sessionTest->session_type != CTemplate::STANDARD)
						{
							$this->_session_id = null;
							$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);

							$result['status'] = "session_removed";
							$result['problem_list'][] = 'Session type of STANDARD does not match session so removed session';
							CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
						}
						else if ($this->_navigation_type == CTemplate::MADE_FOR_YOU && $sessionTest->session_type != CSession::MADE_FOR_YOU)
						{
							$this->_session_id = null;
							$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);

							$result['status'] = "session_removed";
							$result['problem_list'][] = 'Session type of MADE_FOR_YOU does not match session so removed session';
							CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
						}
						else if ($this->_navigation_type == CTemplate::INTRO && ($sessionTest->session_type != CSession::SPECIAL_EVENT && $sessionTest->session_type != CSession::STANDARD))
						{
							$this->_session_id = null;
							$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);

							$result['status'] = "session_removed";
							$result['problem_list'][] = 'Session type of INTRO does not match session so removed session';
							CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
						}
						else if ($this->_navigation_type == CTemplate::EVENT && ($sessionTest->session_type == CSession::SPECIAL_EVENT && empty($sessionTest->session_password)))
						{
							$this->_session_id = null;
							$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);

							$result['status'] = "session_removed";
							$result['problem_list'][] = 'Session type of EVENT does not match session so removed session';
							CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
						}
						// TODO: check for open session and available slots

					}
					else
					{
						// nothing to do
					}

					if ($this->_navigation_type == CTemplate::STANDARD && !empty($this->_OrderObj->bundle_id))
					{
						$this->_OrderObj->bundle_id = null;
						$this->_storage->storeOrderValue($this->_cart_contents_id, "bundle_id", null);

						$result['status'] = "bundle_removed";
						$result['problem_list'][] = 'Session type is Standard so removed bundle.';
						CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
					}

					if ($page == 'session_menu' && $this->_navigation_type == CTemplate::INTRO && empty($this->_OrderObj->bundle_id) && !empty($this->_menu_id) && !empty($this->_OrderObj->store_id))
					{
						$this->clearMenuItems(false, true);
						$Bundle = CBundle::getActiveBundleForMenu($this->_menu_id, $this->_OrderObj->store_id);
						$this->addBundleId($Bundle->id, true);
					}
				}
				else
				{
					// empty session type so there should be no session, menu or items
					// hmmmm... or we could check for session and set the type accordingly
					// but for now ...
					if (!$this->_session_id && is_numeric($this->_session_id))
					{
						$this->_session_id = null;
						$this->_storage->storeOrderValue($this->_cart_contents_id, "session_id", null);

						$result['status'] = "session_removed";
						$result['problem_list'][] = 'Session type is not set so removed session';
						CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
					}
				}
			}
			else
			{
				// Store is inactive so purge
				$this->emptyCart(true);
				// TODO: really only need to report an issue if other order data is stored,
				$result['status'] = "cart_emptied";
				$result['problem_list'][] = 'Store is inactive so emptied cart.';
				CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
			}
		}
		else
		{
			// No Store so make sure everything else is empty except gift cards
			$this->emptyCart(true);
			$result['status'] = "cart_emptied"; // TODO: set to all_good until we can further isolate the issue
			// I.E., This is currently triggered by normal correct circumstances
			$result['problem_list'][] = 'No Store so emptied cart.';
			CLog::RecordDebugTrace(print_r($result, true), 'CART', 1, CLog::DEBUG, true);
		}

		// things changed so refresh the cart
		if ($result['status'] != 'all_good')
		{
			$this->restoreContents();
		}

		return $result;
	}

	function getMenuItems($menu_id = false)
	{
		if ($menu_id == $this->_menu_id)
		{
			return $this->_menu_items;
		}
		else
		{
			return $this->_storage->retrieveArray($this->_cart_contents_id, 'MENU_ITEMS', $menu_id);
		}
	}

	function getProducts()
	{
		return $this->_products;
	}

	function getPayment($payment_id)
	{
		return $this->_PaymentArray[$payment_id];
	}

	function getAllPayments()
	{
		return $this->_PaymentArray;
	}

	function getAllCreditCardPayments()
	{
		$result = array();
		foreach ($this->_PaymentArray as $id => $payment)
		{
			$type = strtoupper($payment['payment_type']);
			if ($type == CPayment::CC || $type == 'CREDIT_CARD')
			{
				$result[$id] = $payment;
			}
		}

		return $result;
	}

	//not by cart payment id, but by actual payment record id
	function getNewGiftCardPayments()
	{
		$result = array();
		foreach ($this->_PaymentArray as $id => $payment)
		{
			$type = strtoupper($payment['paymentData']['payment_type']);
			if ($type == CPayment::GIFT_CARD)
			{

				$newCardNumber = null;
				if (array_key_exists('card_number', $payment['tempData']))
				{
					$newCardNumber = $payment['tempData']['card_number'];
				}
				if ($payment['paymentData']['payment_id'] == 0 || !is_null($newCardNumber))
				{
					$result[$id] = $payment;
				}
			}
		}

		return $result;
	}

	//returns all gift card payments that have a
	//card number which indicates they are new. Previous
	//should not have the gift number in the tempData
	function getNewGiftCardPaymentTotal()
	{
		$sumGiftCardPayment = 0;
		foreach ($this->_PaymentArray as $id => $payment)
		{
			$type = strtoupper($payment['payment_type']);
			if ($type == CPayment::GIFT_CARD)
			{

				if (!empty($payment['tempData']) && !empty($payment['tempData']['card_number']))
				{
					$sumGiftCardPayment += $payment['amount'];
				}
			}
		}

		return $sumGiftCardPayment;
	}

	//not by cart payment id, but by actual payment record id
	function getCreditCardPayment($payment_record_ids)
	{
		$result = array();
		foreach ($this->_PaymentArray as $id => $payment)
		{
			if (!array_key_exists('payment_type', $payment))
			{
				echo 'here';
			}
			$type = strtoupper($payment['payment_type']);
			if ($type == CPayment::CC || $type == 'CREDIT_CARD')
			{

				if (in_array($payment['paymentData']['payment_id'], $payment_record_ids))
				{
					$result[$id] = $payment;
				}
			}
		}

		return $result;
	}

	//not by cart payment id, but by actual payment record id
	function getGiftCardPayment($payment_record_ids)
	{
		$result = array();
		foreach ($this->_PaymentArray as $id => $payment)
		{
			$type = strtoupper($payment['payment_type']);
			if ($type == CPayment::GIFT_CARD)
			{

				if (in_array($payment['paymentData']['payment_id'], $payment_record_ids))
				{
					$result[$id] = $payment;
				}
			}
		}

		return $result;
	}

	function hasOnlyGiftCardPayments()
	{
		foreach ($this->_PaymentArray as $id => $payment)
		{
			$type = strtoupper($payment['payment_type']);
			if ($type != CPayment::GIFT_CARD)
			{
				return false;
			}
		}

		return true;
	}

	function getRegistrationData()
	{
		return $this->_registration_data;
	}

	function getGiftCardOrders()
	{
		return $this->_gift_card_orders;
	}

	private function storeOrdersMenuItems($OrderObj)
	{

		if (get_class($OrderObj) == "COrdersDelivered")
		{
		//	CLog::Assert(!empty($_REQUEST["processor"]) && ($_REQUEST["processor"] == 'cart_remove_coupon' || $_REQUEST["processor"] == 'cart_add_coupon'), "unexpected call to storeOrdersMenuItems for COrdersDelivered order");
		}
		else
		{
			$parentBundleItems = array();
			$tempMenuItemsArr = array();
			$introBundle = array();

			$tempProductsArr = array();

			$OrderObj->getCondensedMenuItemArray($tempMenuItemsArr, $parentBundleItems);
			$OrderObj->getCondensedProductArray($tempProductsArr);

			// New store managed bundle
			if (!empty($parentBundleItems))
			{
				$OrderObj->getCondensedBundleArray($parentBundleItems);
			}

			$OrderObj->getCondensedTVOfferBundleArray($introBundle, $tempMenuItemsArr);

			$uberArray = array();

			if (!empty($tempMenuItemsArr))
			{
				$uberArray['mitems'] = $tempMenuItemsArr;
			}

			if (!empty($parentBundleItems))
			{
				$uberArray['mBundleMap'] = $parentBundleItems;
			}

			if (!empty($introBundle))
			{
				$uberArray['mbundleitems'] = $introBundle;
			}

			if ($this->_menu_id)
			{
				$this->_storage->storeArray($this->_cart_contents_id, $uberArray, "MENU_ITEMS", $this->_menu_id);
			}

			//TODO: enable this when we introduce products
			//$this->_storage->storeArray($this->_cart_contents_id, $tempProductsArr, "PRODUCTS");

			$this->_menu_items = $uberArray;
			$this->_products = $tempProductsArr;

			/*
			 * It is unneccesary to write out the bundle items now since all will be selected and unserialize will call addBundle
			 * which will recreate the items.
			 * When we support a bundle with optional items this array will track which are chosen

			if ( !empty($this->bundle_id) and isset($this->bundle) and isset($this->bundle->items) and  count($this->bundle->items)) {
				$rtn['mbundle_items'] = array();
				foreach( $$this->bundle->items as $id => $itemInfo ) {
						$rtn['mproducts'][$id] = 1;
				}
			}

			*/

			// TODO: move this into separate function
			/*
			if ( isset($this->session) ) {
				$rtn['session'] = $this->session->id;
				$rtn['store_id'] = $this->session->store_id;
				if ( !array_key_exists('store_id', $rtn) or !$rtn['store_id']) {
					throw new exception('serialization error: store id not found for session '.$this->session->id);
				}
			}
			*/
		}
	}

	/**
	 * Retrieve an order object from the cart
	 */
	function getOrder()
	{

		if (isset($this->_OrderObj) && isset($this->_OrderObj->store_id) && !empty(CUser::getCurrentUser()->id))
		{
			$DAO_store = DAO_CFactory::create('store', true);
			$DAO_store->id = $this->getStoreId();
			$DAO_store->find(true);

			$DAO_user = CUser::getCurrentUser();

			$platePointsStatus = CPointsUserHistory::getPlatePointsStatus($DAO_store, $DAO_user);
			if ($platePointsStatus['userIsEnrolled'] && $platePointsStatus['storeSupportsPlatePoints'])
			{
				$this->_OrderObj->storeAndUserSupportPlatePoints = true;
			}
			else
			{
				$this->_OrderObj->storeAndUserSupportPlatePoints = false;
			}
		}
		else
		{
			$this->_OrderObj->storeAndUserSupportPlatePoints = false;
		}

		return $this->_OrderObj;
	}

	function getStoreId()
	{

		if (isset($this->_OrderObj) && isset($this->_OrderObj->store_id))
		{
			return $this->_OrderObj->store_id;
		}

		return false;
	}

	function emptyCartCompletely()
	{
		if (!empty($this->_cart_contents_id))
		{
			$this->_storage->clear($this->_cart_contents_id);
		}

		CBrowserSession::setValue('EDIT_DELIVERED_ORDER');

		$this->disconnectTemporaryCart();

		$this->_OrderObj = null;
		$this->_PaymentArray = null;
		$this->_cartDAO = null;
		$this->_menu_id = null;
		$this->_registration_data = null;
		$this->_gift_card_orders = null;
		$this->_cart_contents_id = null;
		$this->_navigation_type = null;
		$this->_session_id = null;
		$this->_delivered_boxes = null;

		self::$_instance = null;
	}

	/**
	 * Remove cart id from cookie, and remove cart row from db
	 */
	function emptyCart($do_not_disconnect = false)
	{

		if (empty($this->_gift_card_orders) && !$do_not_disconnect)
		{
			$this->emptyCartCompletely();
		}
		else
		{
			// don't disconnect cart if gift cards are present

			$this->clearMenuItems();
			$this->clearAllPayments();

			$this->_storage->clearOrderData($this->_cart_contents_id);

			$this->_navigation_type = null;
			$this->_menu_id = null;
			$this->_session_id = null;
		}
	}

	private function disconnectTemporaryCart()
	{
		$key = CBrowserSession::getCartKey();

		if ($key)
		{
			$cartDAO = DAO_CFactory::create('cart');
			$cartDAO->cart_key = $key;
			if ($cartDAO->find(true))
			{
				$cartDAO->delete(false, true);
			}

			CBrowserSession::setCartKey(false);

			$cookieName = 'cart';
			if (defined('DD_SERVER_NAME') && DD_SERVER_NAME != 'LIVE')
			{
				$cookieName = DD_SERVER_NAME . "_cart";
			}

			unset($_COOKIE[$cookieName]);
		}
	}

	private function establishTemporaryCart()
	{

		CLog::Assert($this->_cart_contents_id == null, "establishTemporaryCart with non-null cart contents id");

		//create uuid
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float)$sec + ((float)$usec * 100000));
		$uuid = md5(uniqid(mt_rand(), true));

		//insert cart row
		$cartDAO = DAO_CFactory::create('cart');
		$cartDAO->cart_key = $uuid;
		$cartDAO->cart_contents_id = $this->_storage->init();

		if ($cartDAO->insert())
		{
			CBrowserSession::setCartKey($uuid);
			$this->_cart_contents_id = $cartDAO->cart_contents_id;
		}
		else
		{
			throw new Exception("INSERT table cart failed IN establishTemporaryCart");
		}
	}

	private function connectTemporaryCartToContents($key = false)
	{
		if ($this->_cart_contents_id)
		{
			return $this->_cart_contents_id;
		}

		if (!$key)
		{
			$key = CBrowserSession::getCartKey();
		}

		if ($key)
		{
			$this->_cartDAO = DAO_CFactory::create('cart');
			$this->_cartDAO->cart_key = $key;
			$num = $this->_cartDAO->find();
			if ($num == 1)
			{
				$this->_cartDAO->fetch();
				$contentID = $this->_cartDAO->cart_contents_id;

				if (empty($contentID))
				{
					//TODO: need conversion code
					throw new Exception("TODO: access of old style cart. Need conversion code.");
				}

				if (!$this->_storage->contentRowExists($contentID))
				{

					// somehow the cartent rows were deleted
					$this->_cart_contents_id = $this->_storage->init();
					$this->_cartDAO->cart_contents_id = $this->_cart_contents_id;
					$this->_cartDAO->update();

					$this->initData();
				}

				return $contentID;
			}
			else if ($num > 1)
			{
				$this->emptyCart();
				CLog::RecordIntense('More than 1 cart for cart key: ' . $key, 'ryan.snook@dreamdinners.com');
			}
			else
			{
				CLog::RecordTemp('No db row found in fetchcart for key: ' . $key);
				$this->emptyCart();
			}
		}

		return null;
	}
}

?>