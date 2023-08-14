<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CCouponCode.php");
require_once("includes/DAO/BusinessObject/CPointsUserHistory.php");
require_once("includes/DAO/BusinessObject/CDreamTasteEvent.php");
require_once("includes/DAO/BusinessObject/CBundle.php");
require_once("CTemplate.inc");

class processor_admin_orderMgrCouponCodeProcessor extends CPageProcessor
{
	private $currentStore = null;

	static $session = null;
	static $session_init = false;

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$nameValuePairs = explode("&", $_POST['params']);
		$values = array();
		foreach ($nameValuePairs as $val)
		{
			$parts = explode("=", $val);
			if (!empty($parts[0]))
			{
				if (isset($parts[1]))
				{
					$values[$parts[0]] = $parts[1];
				}
				else
				{
					$values[$parts[0]] = null;
				}
			}
		}

		$daoStore = DAO_CFactory::create('store');
		$daoStore->id = $values['store_id'];
		$daoStore->find(true);

		$User = null;
		if (isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']))
		{
			$User = DAO_CFactory::create('user');
			$User->id = $_REQUEST['user_id'];
			if (!$User->find(true))
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'The user cannot be found in the Database.'
				));
			}
		}

		list($Order, $SessionObj) = self::buildOrderFromArray($daoStore, $values, null);

		$menu_id = $SessionObj->menu_id;

		$Order->user_id = $User->id;
		$Order->id = $_REQUEST['order_id'];

		$orgOrderObj = null;
		if (isset($_REQUEST['order_state']) && $_REQUEST['order_state'] != 'ACTIVE')
		{
			$orgOrderObj = clone($Order);
		}

		if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'remove')
		{
		}
		else
		{
			$OrgOrderTime = $_REQUEST['org_ts'];
			$OrgOrderID = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : false;

			$couponCode = $_REQUEST['coupon_code'];

			if (empty($couponCode))
			{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'The coupon_code_not_supplied.'
				));
			}
		}

		// TODO: check for valid store id
		if (!$Order)
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'The order could not be found.'
			));
		}

		if (isset($Order->needToReviewCart) && $Order->needToReviewCart)
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'The cart must be reviewed.'
			));
		}

		$Order->refresh($User);
		$Order->recalculate(true);

		if (isset($_REQUEST['op']) && $_REQUEST['op'] != 'remove')
		{
			$couponValidation = CCouponCode::isCodeValid($couponCode, $Order, $menu_id, true, $OrgOrderTime, $OrgOrderID);

			if (gettype($couponValidation) !== "object" || get_class($couponValidation) !== 'CCouponCode')
			{
				$errors = array();
				foreach ($couponValidation as $thisError)
				{
					$errors[] = CCouponCode::getCouponErrorUserText($thisError);
				}

				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'The coupon code could not be used.',
					'validation_errors' => $errors
				));
			}
			else
			{
				$DAO_Coupon_code = $couponValidation;
			}

			if (isset($DAO_Coupon_code) && $DAO_Coupon_code->limit_to_recipe_id)
			{
				$DAO_Coupon_code->calculate($Order, $Order->getMarkUp());

				$DAO_menu_item = DAO_CFactory::create('menu_item');
				$DAO_menu_item->id = $DAO_Coupon_code->menu_item_id;
				$DAO_menu_item->find(true);

				$Order->addCouponMenuItem($DAO_menu_item);
			}

			$Order->addCoupon($DAO_Coupon_code);
		}
		else
		{
			$Order->removeCoupon();

			if (isset($values['coupon_type']) && $values['coupon_type'] == 'FREE_MEAL' && isset($_REQUEST['order_state']) && $_REQUEST['order_state'] != 'ACTIVE')
			{
				// though the item has not been added to the items array it is still in the order_items table
				$OrderItems = DAO_CFactory::create('order_item');
				$OrderItems->query("update order_item set is_deleted = 1, edit_sequence_id = 1, updated_by = " . CUser::getCurrentUser()->id . " where order_id = {$Order->id} and is_deleted = 0");

				$Order->insertEditedItems(false, false);
				$Order->recalculate(true, false);
			}
		}

		$Order->refresh($User, $menu_id);

		$Order->family_savings_discount = 0;
		$Order->menu_program_id = 1;

		if (isset($DAO_Coupon_code) && $DAO_Coupon_code->coupon_code === 'AAA01')
		{
			CUserData::setUserAsAAAReferred($User, 'AAA_referred');
		}

		$Order->recalculate(true);

		if (isset($_REQUEST['order_state']) && $_REQUEST['order_state'] != 'ACTIVE')
		{
			//	$Order->update($orgOrderObj);
		}

		$entreeServings = 0;
		$entreeTitle = "";
		if (isset($DAO_Coupon_code) && $DAO_Coupon_code->discount_method == CCouponCode::FREE_MEAL)
		{
			$menu_item = DAO_CFactory::create('menu_item');
			$menu_item->id = $DAO_Coupon_code->menu_item_id;
			$menu_item->selectAdd();
			$menu_item->selectAdd("menu_item_name");
			$menu_item->find_includeDeleted(true);
			$entreeTitle = $menu_item->menu_item_name;

			$entreeServings = $menu_item->pricing_type == CMenuItem::FULL ? '6' : '3';

			if (isset($_REQUEST['order_state']) && $_REQUEST['order_state'] != 'ACTIVE')
			{
				// item was added to the order by the recalculate call above
				$orgOrderObj = clone($Order);

				$OrderItems = DAO_CFactory::create('order_item');
				$OrderItems->query("update order_item set is_deleted = 1, edit_sequence_id = 1, updated_by = " . CUser::getCurrentUser()->id . " where order_id = {$Order->id} and is_deleted = 0");

				$Order->insertEditedItems(false, false);
				$Order->recalculate(true, false);

				$Order->update($orgOrderObj);
			}
		}

		// The Order Editor will calculate and update the display so we only need to return pertinent info

		CAppUtil::processorMessageEcho(array(
			'processor_success' => true,
			'processor_message' => 'The coupon code was attached.',
			'validation_errors' => false,
			'coupon_code_discount_total' => $Order->coupon_code_discount_total,
			'discount_method' => (isset($DAO_Coupon_code) ? $DAO_Coupon_code->discount_method : ""),
			'coupon' => (isset($DAO_Coupon_code) ? $DAO_Coupon_code : ""),
			'code_id' => (isset($DAO_Coupon_code) ? $DAO_Coupon_code->id : ""),
			'entree_title' => $entreeTitle,
			'entree_servings' => $entreeServings,
			'discount_var' => (isset($DAO_Coupon_code) ? $DAO_Coupon_code->discount_var : ""),
			'limit_to_finishing_touch' => (isset($DAO_Coupon_code) && $DAO_Coupon_code->limit_to_finishing_touch ? true : false),
			'valid_with_customer_referral_credit' => (isset($DAO_Coupon_code) && $DAO_Coupon_code->valid_with_customer_referral_credit ? true : false),
			'valid_with_plate_points_credits' => (isset($DAO_Coupon_code) && $DAO_Coupon_code->valid_with_plate_points_credits ? true : false),
			'coupon_obj' => (isset($DAO_Coupon_code) ? DAO::getCompressedArrayFromDAO($DAO_Coupon_code) : null),
			'order_item_array' => $Order->getItems()
		));
	}

	/*
	 * Cache the session Obj
	 */
	static function buildSession($id, $storeId)
	{
		if (!$id)
		{
			throw new Exception('session not found');
		}

		if (self::$session and (self::$session->id != $id))
		{
			throw new Exception('invalid session cache' . self::$session->id . ':::' . $id);
		}

		if (!self::$session_init)
		{
			$Session = DAO_CFactory::create("session");
			$Session->id = $id;
			$Session->store_id = $storeId;
			$found = $Session->find(true);
			if ($found)
			{
				self::$session = $Session;
			}
			else
			{
				CLog::RecordIntense("Session not found", "ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com");
				$tpl = @CApp::instance()->template()->setErrorMsg("Could not find the session. Please start the order process again. Dream Dinners support has been notified that an issue has occurred.");
				CApp::bounce("main.php?page=admin_list_users");
			}

			self::$session_init = true;
		}

		return self::$session;
	}

	const QUANTITY_PREFIX = 'qty_'; //entrees

	/**
	 * @return $Order
	 */
	public static function buildOrderFromArray($daoStore, $array, $User)
	{
		$Order = DAO_CFactory::create('orders');
		$Order->store_id = $daoStore->id;
		$Order->is_sampler = 0;
		$Order->family_savings_discount_version = 2;
		$Order->order_admin_notes = (!empty($array['order_admin_notes']) ? $array['order_admin_notes'] : null);
		$Order->order_user_notes = (!empty($array['order_user_notes']) ? $array['order_user_notes'] : null);

		$applyPremium = true;
		if (isset($array['use_premium']) and $array['use_premium'] == 'no')
		{
			$applyPremium = false;
		}
		$Order->setOrderType(COrders::DIRECT, $applyPremium);
		//		$Order->plan_type = COrders::PLAN_NO_PLAN;

		if (isset($User) and isset($User->id))
		{
			$Order->user_id = $User->id;
		}

		$Session = self::buildSession($array['session'], $daoStore->id);
		$Order->addSession($Session);
		$Order->setMenuId($Session->menu_id);

		$Store = $Order->getStore();

		$Markup = $Store->getMarkUpMultiObj($Session->menu_id);
		$Order->mark_up_multi_id = $Markup->id;

		$Order->addMarkup($Markup);

		if ($Session->session_type == CSession::TODD)
		{
			$isTODD = true;
			$Order->is_TODD = 1;

			// Look for Observe Only item
			if (isset($array['pqy_' . COrders::TODD_FREE_ATTENDANCE_PRODUCT_ID]) and !empty($array['pqy_' . COrders::TODD_FREE_ATTENDANCE_PRODUCT_ID]))
			{
				$Product = DAO_CFactory::create('product');
				$select = "SELECT product.* FROM product ";
				$where = " where product.id = " . COrders::TODD_FREE_ATTENDANCE_PRODUCT_ID;

				$Product->query($select . $where);

				if ($Product->fetch())
				{
					$Order->addProduct(clone($Product), 1);

					return array(
						$Order,
						$Session
					);
				}
			}
		}

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

			$Order->addTasteBundle($theBundle, $selectedBIs);
		}

		// TV/Intro Order
		if (isset($array['selectedBundle']) and $array['selectedBundle'])
		{
			$activeBundle = CBundle::getActiveBundleForMenu($Session->menu_id, $Order->store_id);

			if ($daoStore->id == 176)
			{
				$activeBundle->price = 89.95;
			}

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

			$Order->addBundle($activeBundle, $selectedBIs);
		}

		$getStoreMenu = CMenu::storeSpecificMenuExists($Session->menu_id, $Order->store_id);

		//look for promo code
		if (isset($array['promotion_code']) and $array['promotion_code'] and strpos($array['promotion_code'], "No Promotion") !== 0)
		{
			$Promo = DAO_CFactory::create('promo_code');
			$Promo->id = $array['promotion_code'];
			$foundIt = $Promo->findActive();
			if ($foundIt)
			{
				$Promo->fetch();
				$Order->addPromo($Promo);

				// add the promo item, mark it as as promo by passing true in addMenuItem
				if (!empty($Promo->promo_menu_item_id))
				{
					$menuItemInfo = DAO_CFactory::create('menu_item');

					if ($getStoreMenu)
					{
						$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = " . $Order->store_id . " AND mmi.is_deleted = 0 WHERE mi.id = " . $Promo->promo_menu_item_id . " AND mi.is_deleted = 0";
					}
					else
					{
						$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id IS NULL AND mmi.is_deleted = 0 WHERE mi.id = " . $Promo->promo_menu_item_id . " AND mi.is_deleted = 0";
					}

					$menuItemInfo->query($query);
					$menuItemInfo->fetch();
					$Order->addMenuItem(clone($menuItemInfo), 1, true);
				}
			}
		}

		//look for coupon code
		if (isset($array['coupon_id']) and $array['coupon_id'])
		{
			$DAO_coupon_code = DAO_CFactory::create('coupon_code');
			$DAO_coupon_code->id = $array['coupon_id'];

			if ($DAO_coupon_code->find(true))
			{
				$Order->addCoupon($DAO_coupon_code);

				// add the promo item, mark it as as promo by passing true in addMenuItem
				if ($DAO_coupon_code->discount_method == CCouponCode::FREE_MEAL && !empty($DAO_coupon_code->menu_item_id))
				{
					$DAO_menu_item = DAO_CFactory::create('menu_item');
					$DAO_menu_item->id = $DAO_coupon_code->menu_item_id;

					$DAO_menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
					if ($getStoreMenu)
					{
						$DAO_menu_to_menu_item->store_id = $Order->store_id;
					}
					else
					{
						$DAO_menu_to_menu_item->store_id = 'NULL';
					}

					$DAO_menu_item->selectAdd();
					$DAO_menu_item->selectAdd("menu_to_menu_item.override_price AS override_price");
					$DAO_menu_item->selectAdd("menu_item.*");

					$DAO_menu_item->joinAddWhereAsOn($DAO_menu_to_menu_item);

					$DAO_menu_item->find(true);

					$Order->addMenuItem(clone($DAO_menu_item), 1, false, ($DAO_coupon_code->discount_method == CCouponCode::FREE_MEAL), !empty($DAO_coupon_code->limit_to_recipe_id));
				}
			}
		}

		//add menu items
		$prelen = strlen(self::QUANTITY_PREFIX);
		$qty_keys = array_keys($array);
		$menu_item_ids = array();
		foreach ($qty_keys as $itemKey)
		{
			if (strstr($itemKey, self::QUANTITY_PREFIX) and is_numeric(substr($itemKey, $prelen)))
			{
				if ($array[$itemKey])
				{
					$menu_item_ids [] = substr($itemKey, $prelen);
				}
			}
		}

		if ($menu_item_ids)
		{
			$menuItemInfo = DAO_CFactory::create('menu_item');

			if ($getStoreMenu)
			{
				$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = " . $Order->store_id . " AND mmi.menu_id = " . $Session->menu_id . " AND mmi.is_deleted = 0 WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ") AND mi.is_deleted = 0";
			}
			else
			{
				$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id IS NULL AND mmi.menu_id = " . $Session->menu_id . " AND mmi.is_deleted = 0 WHERE mi.id IN (" . implode(", ", $menu_item_ids) . ") AND mi.is_deleted = 0";
			}

			$menuItemInfo->query($query);
			while ($menuItemInfo->fetch())
			{

				if ($menuItemInfo->is_bundle)
				{

					$subItems = CBundle::getBundleMenuInfoForMenuItem($menuItemInfo->id, $Session->menu_id, $Order->store_id);

					$subItemKeys = array();
					foreach ($qty_keys as $itemKey)
					{
						$thisID = substr($itemKey, 4);
						if (strstr($itemKey, 'sbi_') and is_numeric($thisID) and array_key_exists($thisID, $subItems['bundle']))
						{
							$subItemKeys[] = substr($itemKey, $prelen);
						}
					}

					$subItemInfo = DAO_CFactory::create('menu_item');
					$select = "SELECT menu_item_category.category_type AS 'category', menu_item.*, menu_to_menu_item.override_price, menu_to_menu_item.menu_order_value FROM menu_item ";
					if ($getStoreMenu)
					{
						$joins = "INNER JOIN  menu_to_menu_item ON menu_to_menu_item.menu_item_id=menu_item.id and menu_to_menu_item.store_id = " . $Order->store_id . " LEFT JOIN menu_item_category on menu_item.menu_item_category_id = menu_item_category.id ";
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
						$qty = $array['sbi_' . $subItemInfo->id];
						if ($qty)
						{
							$subItemInfo->parentItemId = $menuItemInfo->id;
							$subItemInfo->bundleItemCount = $qty;
							$Order->addMenuItem(clone($subItemInfo), $qty);
						}
					}
				}

				$Order->addMenuItem(clone($menuItemInfo), abs($array[self::QUANTITY_PREFIX . $menuItemInfo->id]));
			}
		}

		//look for direct order discount
		if (isset($array['direct_order_discount']))
		{
			$Order->addDiscount($array['direct_order_discount']);
		}

		//look for direct order discount
		if (isset($array['plate_points_discount']))
		{
			$Order->points_discount_total = $array['plate_points_discount'];
		}
		else
		{
			$Order->points_discount_total = 0;
		}

		$Order->setOrderInStoreStatus();
		$Order->setOrderMultiplierEligibility();

		if (!empty($array['misc_food_subtotal']))
		{
			$Order->misc_food_subtotal = $array['misc_food_subtotal'];
			$Order->misc_food_subtotal_desc = $array['misc_food_subtotal_desc'];
		}

		if (!empty($array['misc_nonfood_subtotal']))
		{
			$Order->misc_nonfood_subtotal = $array['misc_nonfood_subtotal'];
			$Order->misc_nonfood_subtotal_desc = $array['misc_nonfood_subtotal_desc'];
		}

		if (!empty($array['subtotal_service_fee']))
		{
			$Order->subtotal_service_fee = $array['subtotal_service_fee'];
			$Order->service_fee_description = $array['service_fee_description'];
		}

		return array(
			$Order,
			$Session
		);
	}
}

?>