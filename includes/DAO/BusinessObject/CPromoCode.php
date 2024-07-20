<?php

	require_once 'DAO/Promo_code.php';
	require_once 'DAO/BusinessObject/COrders.php';

	/* ------------------------------------------------------------------------------------------------
	 *	Class: CPromoCode
	 *
	 *	Data:
	 *
	 *	Methods:
	 *		Create()
	 *
	 *  	Properties:
	 *
	 *
	 *	Description:
	 *
	 *
	 *	Requires:
	 *
	 * -------------------------------------------------------------------------------------------------- */


	class CPromoCode extends DAO_Promo_code {

		const ITEM = 'ITEM';
		const FLAT = 'FLAT';
		const PERCENT = 'PERCENT';

		function __construct() {
			parent::__construct();
		}

		/**
		 * This function returns the code only if still active.
		 * Set id first if searching of a single customer
		 **/
		function findActive() {

			$this->promo_code_active = 1;

			$where = ' (promo_code_start <= now() or promo_code_start IS NULL) AND' .
					'		(promo_code_expiration > now() or promo_code_expiration IS NULL)';

			$this->whereAdd( $where );

			return $this->find();
		}

				/**
		 * This function returns the code only if still active.
		 * Set id first if searching of a single customer
		 **/
		function findActiveForMenu($menu_id, $store_id) {

			$this->promo_code_active = 1;

			$where = ' (promo_code_start <= now() or promo_code_start IS NULL) AND' .
					'		(promo_code_expiration > now() or promo_code_expiration IS NULL)';

			$this->whereAdd( $where );

			if (!$this->find(true))
				return false;

			if (!empty($this->promo_menu_item_id))
			{
				$menu_to_item = DAO_CFactory::create('menu_to_menu_item');
				$menu_to_item->menu_id = $menu_id;
				$menu_to_item->menu_item_id = $this->promo_menu_item_id;
				$menu_to_item->store_id = $store_id;
				if ($menu_to_item->find())
					return true;
			}

			return false;

		}

		/**
		 * Calculates the price reduction from an order.
		 * @return the dollar amount of the discount or false if the promo does
		 * not apply to the order.
		 *    CES: 1-30-07 Added $markup override: if supplied use the passed in markup
		 *    otherwise use the current store markup
		 *    ability to non-current markup added for order editing
		 * @throws Exception
		 */
		function calculate($Order, $markup = false) {
			if ( !$Order )
				return false;

			//check start; done in findActive()

			//check expire; done in findActive()

			switch( $this->promo_type ) {
				case self::FLAT:
					return $this->_calculateFlat($Order, $markup);

				default:
					throw new Exception('unrecognized promo type');
					break;
			}

			return false;
		}


		// CES: 1-30-07 Added $markup override: if supplied use the passed in markup
		// otherwise use the current store markup
		// ability to non-current markup added for order editing
		private function _calculateFlat($Order, $markup = false) {

			//check for menu item in order
			if ( !$this->promo_menu_item_id )
				return false;

			//check for item in cart
			// Note: promo is not in the cart but implied by the promo id
			// front end ordering only
			if ( $Order->family_savings_discount_version != 2 && !$Order->getMenuItemQty($this->promo_menu_item_id) )
				return false;


			$isFamilySavings = $Order->isFamilySavingsOrder();

			if ( !$isFamilySavings && $Order->countItems() < 13 && $Order->family_savings_discount_version != 2) {
				if ( $Order->findSession()->session_type == CSession::QUICKSIX && $Order->countItems() > 6 ) {
					//ok
				} else {
					return false;
				}
			}

			if ($isFamilySavings && $Order->countItems() < 7)
				return false;
			// get current markup for store

			$menu_id = null;
			$Session = $Order->findSession();
			$OrdersMenuID = $Order->getMenuId();
			if (isset($Session))
				$menu_id = $Session->menu_id;
			else if (isset($OrdersMenuID))
				$menu_id = $OrdersMenuID;

			$markupObj = null;
			if (!$markup)
			{
				$Store = $Order->getStore();
				if ($Order->family_savings_discount_version == 2)
					$markupObj = $Store->getMarkUpMultiObj($menu_id);
				else
					$markupObj = $Store->getMarkUpObj($menu_id);
			}
			else
			{
				// retrieve the markup used originally

				if (!empty($markup))
				{
					if ($Order->family_savings_discount_version == 2)
						$markupObj = DAO_CFactory::create('mark_up_multi');
					else
						$markupObj = DAO_CFactory::create('mark_up');
					$markupObj->id = $markup;
					if (!$markupObj->find_includeDeleted(true))
						throw new Exception('markup not found in CPromoCode::_calculateFlat' );
				}
			}

			if ($Order->family_savings_discount_version == 2)
				$markedUpVal = COrders::getStorePrice($markupObj, $Order->getMenuItem($this->promo_menu_item_id), 1);
			else
				$markedUpVal = COrders::getItemMarkupSubtotal($markupObj, $Order->getMenuItem($this->promo_menu_item_id), 1);

			if ($isFamilySavings)
			{
			//deduct item price including markup
				$numServings = $Order->getNumberServings();
				$Order->calculateFamilySavings();

				if ($numServings >= 42)
					$perServingDiscount = $Order->family_savings_discount / $numServings;
				else
					$perServingDiscount = 0;

				$MenuItem = DAO_CFactory::create('menu_item');
				$MenuItem->id = $this->promo_menu_item_id;
				if (!$MenuItem->find(true))
					throw new Exception('Menu_item not found in CPromoCode::_calculateFlat()');


				$servingsInPromo = 0;
				if (isset($MenuItem->servings_per_item))
				{
					$servingsInPromo = $MenuItem->servings_per_item;
				}
				else
				{
					if ($MenuItem->pricing_type == CMenuItem::HALF)
						$servingsInPromo = 3;
					else
						$servingsInPromo = 6;
				}


				$markedUpVal -= ($perServingDiscount * $servingsInPromo);

			}


			return $markedUpVal;

		}

		static private function getItemPrice(&$menuInfo, $promo_item_id)
		{
			foreach ($menuInfo as $categoryName => $subArray)
			{
				if (isset($subArray[$promo_item_id]))
					return $subArray[$promo_item_id]['price'];
			}

			return 0;
		}

		/**
		 * Returns an array that can be used for a popup menu
		 * $menuId is mandatory and the array will be all promos fopr the given menu id,
		 * $selected_id can be null, if not null the price and pricing type for the passed in id is returned
		 * $menuInfo is an array with menuitem_id as the key and value that is an array with at least ['price'] as a value
		 */
		static function buildPromoArray($menu_id, $selectedID, $menuInfo)
		{

			$promo = DAO_CFactory::create('promo_code');

			$promo->query("Select promo_code.id, promo_code.promo_code, menu_item.id as item_id, menu_item.menu_item_name, menu_item.pricing_type, store_menu_item_exclusion.id as excluded from promo_code " .
				" join menu_to_menu_item on menu_to_menu_item.menu_id = $menu_id and menu_to_menu_item.store_id is null " .
				" join menu_item on menu_item.id = menu_to_menu_item.menu_item_id " .
				" LEFT join store_menu_item_exclusion on menu_to_menu_item.id = store_menu_item_exclusion.menu_to_menu_item_id " .
				" where promo_menu_item_id =  menu_to_menu_item.menu_item_id and promo_code.is_deleted = 0 and menu_to_menu_item.is_deleted = 0 and store_menu_item_exclusion.id is null");

			$retVal = array(0 => "No Promotion Selected");
			$selectedInfo = array();
			$item_IDs = array();
			// flatten categores out of the array

			$foundSelected = false;


			while ($promo->fetch())
			{
				$price = self::getItemPrice($menuInfo, $promo->item_id);
				$item_IDs[$promo->item_id] = array('id' => $promo->id, 'price' => $price);

				if ($selectedID == $promo->id)
				{
					$foundSelected = true;
					$selectedInfo['pricing_type'] = ($promo->pricing_type == 'HALF' ? '3 SERV' : '6 SERV');
					$selectedInfo['price'] = $price;
				}

				$retVal[$promo->id] = ($promo->pricing_type == 'HALF' ? '3 SERV' : '6 SERV'). " | " . $price. " | " . $promo->menu_item_name;
			}
			// The promo menu item was suppressed but the current order had the promo originally so we must add it back in
			if (!empty($selectedID) && !$foundSelected)
			{


				$selectedPromo = DAO_CFactory::create('promo_code');

				$selectedPromo->query("Select promo_code.id, promo_code.promo_code, menu_item.id as item_id, menu_item.menu_item_name, menu_item.pricing_type from promo_code " .
				" join menu_item on menu_item.id = promo_code.promo_menu_item_id " .
				" where promo_code.id = $selectedID and promo_code.is_deleted = 0");

				if ($selectedPromo->fetch())
				{
					$price = self::getItemPrice($menuInfo, $selectedPromo->item_id);
					$item_IDs[$selectedPromo->item_id] = array('id' => $selectedPromo->id, 'price' => $price);
					$selectedInfo['pricing_type'] = ($selectedPromo->pricing_type == 'HALF' ? '3 SERV' : '6 SERV');
					$selectedInfo['price'] = $price;
					$retVal[$selectedPromo->id] = ($selectedPromo->pricing_type == 'HALF' ? '3 SERV' : '6 SERV'). " | " . $price. " | " . $selectedPromo->menu_item_name;
				}


			}

			return array($retVal, $selectedInfo, $item_IDs);
		}


		static function getPromoToEntreeIDMap($menu_id)
		{

			$idArray = array();

			$promo = DAO_CFactory::create('promo_code');

			$promo->query("Select promo_code.id, menu_item.entree_id as theMenuItemID, menu_item.pricing_type, store_menu_item_exclusion.id as excluded" .
				" From promo_code Inner Join menu_item ON promo_code.promo_menu_item_id = menu_item.id " .
				" Join menu_to_menu_item on menu_to_menu_item.menu_item_id = menu_item.id and menu_to_menu_item.store_id is null " .
				" LEFT join store_menu_item_exclusion on menu_to_menu_item.id = store_menu_item_exclusion.menu_to_menu_item_id " .
				" Where menu_to_menu_item.menu_id = " . $menu_id .  " and promo_code.is_deleted = 0 and menu_item.is_deleted = 0 and store_menu_item_exclusion.id is null");


			while ($promo->fetch())
			{
				$idArray[$promo->id] = array('entree_id' => $promo->theMenuItemID, 'servings' => ($promo->pricing_type == 'FULL' ? 6 : 3));

			}

			return $idArray;
		}


		static function buildAllPromosArray($menu_id, $showItemNames=false)
		{
			$retVal = NULL;

			$idArray = array();

			$promo = DAO_CFactory::create('promo_code');

			$promo->query("Select promo_code.id, promo_code.promo_code, menu_item.entree_id as theMenuItemID, menu_item.price, menu_item.pricing_type, menu_item.menu_item_name, store_menu_item_exclusion.id as excluded" .
				" From promo_code Inner Join menu_item ON promo_code.promo_menu_item_id = menu_item.id " .
				" Join menu_to_menu_item on menu_to_menu_item.menu_item_id = menu_item.id and menu_to_menu_item.store_id is null " .
				" LEFT join store_menu_item_exclusion on menu_to_menu_item.id = store_menu_item_exclusion.menu_to_menu_item_id " .
				" Where menu_to_menu_item.menu_id = " . $menu_id .  " and promo_code.is_deleted = 0 and menu_item.is_deleted = 0 and store_menu_item_exclusion.id is null");

			if ($showItemNames == false)
				$retVal = array(0 => "No Promotion Selected");

			while ($promo->fetch())
			{
				$idArray[$promo->id] = array('entree_id' => $promo->theMenuItemID, 'servings' => ($promo->pricing_type == 'FULL' ? 6 : 3));

				if ($showItemNames == false)
					$retVal[$promo->id] = ($promo->pricing_type == 'HALF' ? '3 SERV' : '6 SERV') . " | " . $promo->menu_item_name;
				else
					$retVal[] =  $promo->promo_code;
			}
			if ($showItemNames == false && count($retVal)== 1) $retVal[0] = "No Promotions Available";

			return array($retVal, $idArray );
		}
	}

?>