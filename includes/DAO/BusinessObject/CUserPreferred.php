<?php

require_once 'DAO/User_preferred.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CUserPreferred
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

class CUserPreferred extends DAO_User_preferred
{

	const FLAT = 'FLAT';
	const PERCENTAGE = 'PERCENT';
	const PREFERRED_CAP_SERVINGS = 'SERVINGS';
	const PREFERRED_CAP_ITEMS = 'ITEMS';
	const PREFERRED_CAP_NONE = 'NONE';

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * This function returns active preferred customer rows for the store and global discounts.
	 * Set user_id first if searching of a single customer
	 **/
	function findActive($storeFilterId = false)
	{
		$query = "SELECT
				user_preferred.*,
				cuser.user_type AS 'added_by_type',
				cuser.firstname AS 'added_by_firstname',
				cuser.lastname AS 'added_by_lastname',
				u.firstname,
				u.lastname,
				store.store_name
				FROM user_preferred JOIN user as u ON user_preferred.user_id = u.id
				LEFT JOIN user AS cuser ON user_preferred.created_by = cuser.id
				LEFT JOIN store on store.id = user_preferred.store_id
				WHERE ";

		if ($this->user_id)
		{
			$query .= "user_preferred.user_id = '" . $this->user_id . "' AND ";
		}

		$query .= "(user_preferred_start <= now() or user_preferred_start IS NULL) AND (user_preferred_expiration > now() or user_preferred_expiration IS NULL) AND ";

		if ($storeFilterId)
		{
			$query .= " (store_id = '" . $storeFilterId . "' or all_stores = 1) AND user_preferred.is_deleted = 0";
		}
		else
		{
			$query .= " user_preferred.is_deleted = '0'";
		}

		$this->query($query);

		return $this->N;
	}

	/**
	 * @param $orderObj Current order to see if any preferred capacity can still be applied to
	 *
	 *                  Currently the capacity is for all orders in a menu month
	 *
	 *
	 * @return $simpleObj with two properties
	 *            ->hasBeenMet: true | false (also returns false if the cap is set to NONE )
	 *          ->remainingObj: If hasBeenMet == FALSE, then this will return how many item/servings the
	 *                            preferred discount can apply to
	 *                            Obj->type, Obj->countRemaining
	 * @throws Exception
	 */
	static function hasCapacityBeenMet($orderObj)
	{
		$result = new stdClass();

		$remainingInfo = new stdClass();
		$remainingInfo->type = null;
		$remainingInfo->countRemaining = null;

		$preferredObj = $orderObj->getPreferredObj();

		//short circuit case for when cap is set to none
		if ($preferredObj && $preferredObj->preferred_cap_type == CUserPreferred::PREFERRED_CAP_NONE)
		{
			$result->hasBeenMet = false;
			$remainingInfo->type = $preferredObj->preferred_cap_type;
			//virtually unlimited
			$remainingInfo->countRemaining = 5000;
			$result->remainingObj = $remainingInfo;

			return $result;
		}

		//fetch all orders for this menu
		$Orders = DAO_CFactory::create("orders");
		$session = $orderObj->getSessionObj(false);
		if (!empty($session))
		{
			$Orders->query("select o.id, o.user_preferred_discount_total, o.user_preferred_discount_cap_type, o.user_preferred_discount_cap_applied from orders o
							join booking b on b.order_id = o.id and b.is_deleted = 0 and b.status in ('SAVED','RESCHEDULED','ACTIVE')
							join session s on s.id = b.session_id and s.is_deleted = 0 
							where o.user_id = $orderObj->user_id
							and o.is_deleted = 0
							and s.menu_id = $session->menu_id");
		}

		//no other orders in menu so cap can not have been met
		if ($Orders->N == 0)
		{
			$result->hasBeenMet = false;

			if ($preferredObj)
			{
				$remainingInfo->type = $preferredObj->preferred_cap_type;
				$remainingInfo->countRemaining = $preferredObj->preferred_cap_value;
			}

			$result->remainingObj = $remainingInfo;

			return $result;
		}

		//Other orders...loop through to see if they have capacity fulfilled
		$countTowardsItemCap = 0;
		$countTowardsServingsCap = 0;
		$orderAllotment = array();
		while ($Orders->fetch())
		{
			if ($orderObj->id == $Orders->id)
			{
				//we need to calculate based on updated item so don't include in remaining
				continue;
			}

			$orderCountApplied = new stdClass();
			$orderCountApplied->order_id = $Orders->id;
			$orderCountApplied->user_preferred_discount_cap_applied = $Orders->user_preferred_discount_cap_applied;
			$orderAllotment[] = $orderCountApplied;
			if ($Orders->user_preferred_discount_cap_type == CUserPreferred::PREFERRED_CAP_ITEMS)
			{
				$countTowardsItemCap += $Orders->user_preferred_discount_cap_applied;
			}
			else if ($Orders->user_preferred_discount_cap_type == CUserPreferred::PREFERRED_CAP_SERVINGS)
			{
				$countTowardsServingsCap += $Orders->user_preferred_discount_cap_applied;
			}
		}

		//If switch between the two in a single month, and an order is already placed, then the current one will be applied.
		if ($preferredObj)
		{
			if (($preferredObj->preferred_cap_type == CUserPreferred::PREFERRED_CAP_ITEMS && $countTowardsItemCap >= $preferredObj->preferred_cap_value) || ($preferredObj->preferred_cap_type == CUserPreferred::PREFERRED_CAP_SERVINGS && $countTowardsServingsCap >= $preferredObj->preferred_cap_value))
			{
				$result->hasBeenMet = true;
				$result->remainingObj = new stdClass();
				$result->remainingObj->type = null;
				$result->remainingObj->countRemaining = null;

				return $result;
			}
		}

		$result->hasBeenMet = false;

		if ($preferredObj)
		{
			$remainingInfo->type = $preferredObj->preferred_cap_type;
			if ($preferredObj->preferred_cap_type == CUserPreferred::PREFERRED_CAP_ITEMS)
			{
				$remainingInfo->countRemaining = ($preferredObj->preferred_cap_value - $countTowardsItemCap);
			}
			else if ($preferredObj->preferred_cap_type == CUserPreferred::PREFERRED_CAP_SERVINGS)
			{
				$remainingInfo->countRemaining = ($preferredObj->preferred_cap_value - $countTowardsServingsCap);
			}
		}

		$result->remainingObj = $remainingInfo;
		$result->orderAllotment = $orderAllotment;

		return $result;
	}

	/**
	 * @param $orderObj             order containing the items/serving information to determine how much of the preferred discount
	 *                              can be applied to this order, if any
	 * @param $remainingCapacityObj remainin capaity object with three properties: type, countRemaining, wasApplied
	 *
	 * @return null if there was an error otherwise an Obj with two properties sumIncludedCost, sumInclude (either items or servings)
	 */
	static function calculateDiscountTotal($orderObj, $remainingCapacityObj, $exclusionList)
	{
		$result = null;

		if (is_null($orderObj) || is_null($remainingCapacityObj))
		{
			//error: these are mandatory for function to work
			CLog::RecordNew(CLog::ERROR, 'CUserPreferred->calculateDiscountTotal order or remaining capacity is null', "", "", false);

			return null;
		}
		$preferredObj = $orderObj->getPreferredObj();
		$sumIncludedCost = null;

		$items = $orderObj->getItems();
		if ($items)
		{
			$remaining = $remainingCapacityObj->remainingObj->countRemaining;
			$type = $remainingCapacityObj->remainingObj->type;

			$itemSortedByCosts = array();
			foreach ($items as $itemArray)
			{
				$itemObj = $itemArray[1];
				$itemObj->item_count = $itemArray[0];
				$itemSortedByCosts[$itemObj->id] = $itemObj;
			}

			usort($itemSortedByCosts, function ($a, $b) {
				if ($a->override_price < $b->override_price)
				{
					return -1; // Less than
				}
				else if ($a->override_price > $b->override_price)
				{
					return 1;  // Greater than
				}
				else
				{
					return 0;  // Equal
				}
			});

			$markup = null;
			$result = new stdClass();
			$result->countIncluded = null;
			$result->sumIncludedCost = null;
			$applied = false;
			switch ($type)
			{
				case CUserPreferred::PREFERRED_CAP_NONE:
					return null;
				case CUserPreferred::PREFERRED_CAP_ITEMS:
					$countIncluded = 0;
					//loop through highest cost to lowest to build discount cost in that order
					foreach ($itemSortedByCosts as $item)
					{
						if (isset($item->isPromo) && $item->isPromo)
						{
							//dont include in calculation
							continue;
						}

						if (isset($item->isFreeMeal) && $item->isFreeMeal)
						{
							//dont include in calculation
							continue;
						}
						$itemCount = $item->item_count;
						for ($i = 0; $i < $itemCount; $i++)
						{
							//Sides and Sweets
							if ($preferredObj->include_sides && ($item->is_side_dish || $item->is_menu_addon || $item->is_chef_touched) && ($item->menu_item_category_id > 4 || ($item->menu_item_category_id == 4 && $item->is_store_special == 1)))
							{
								if (is_null($item->override_price))
								{
									if (!isset($markup))
									{
										$markup = $orderObj->getStoreObj()->getMarkUpMultiObj($orderObj->getSessionObj()->menu_id);
									}
									$sidePrice = CTemplate::moneyFormat(COrders::getItemMarkupMultiSubtotal($markup, $item, 1));
									$sumIncludedCost += $sidePrice;
								}
								else
								{
									$sumIncludedCost += $item->override_price;
								}
								$applied = true;
							}
							else if ($countIncluded < $remaining && !in_array($item->id, $exclusionList) && ($item->menu_item_category_id < 4 || ($item->menu_item_category_id < 5 && ($item->menu_item_category_id == 4 && $item->is_store_special == 0))))
							{

								$sumIncludedCost += $item->override_price;
								$countIncluded++;

								$applied = true;
							}
						}
					}
					$result->wasApplied = $applied;
					if ($applied)
					{
						$result->countIncluded = $countIncluded;
						$result->sumIncludedCost = $sumIncludedCost;
					}
					break;
				case CUserPreferred::PREFERRED_CAP_SERVINGS:
					$countIncludedServings = 0;
					//loop through highest cost to lowest to build discount cost in that order
					foreach ($itemSortedByCosts as $item)
					{
						if (isset($item->isPromo) && $item->isPromo)
						{
							//dont include in calculation
							continue;
						}

						if (isset($item->isFreeMeal) && $item->isFreeMeal)
						{
							//dont include in calculation
							continue;
						}

						for ($i = 0; $i < $item->item_count; $i++)
						{
							//Sides and Sweets
							if ($preferredObj->include_sides && ($item->menu_item_category_id > 4 || ($item->menu_item_category_id == 4 && $item->is_store_special == 1)))
							{
								if (is_null($item->override_price))
								{
									if (!isset($markup))
									{
										$markup = $orderObj->getStoreObj()->getMarkUpMultiObj($orderObj->getSessionObj()->menu_id);
									}
									$sidePrice = CTemplate::moneyFormat(COrders::getItemMarkupMultiSubtotal($markup, $item, 1));
									$sumIncludedCost += $sidePrice;
								}
								else
								{
									$sumIncludedCost += $item->override_price;
								}
								$applied = true;
							}
							else if ($countIncludedServings < $remaining && !in_array($item->id, $exclusionList) && ($item->menu_item_category_id < 4 || ($item->menu_item_category_id < 5 && ($item->menu_item_category_id == 4 && $item->is_store_special == 0))))
							{
								if ($item->servings_per_item > $remaining)
								{
									$remainder = ($item->override_price / $item->servings_per_item) * $remaining;
									$remainder = CTemplate::moneyFormat($remainder);
									$sumIncludedCost += $remainder;
									$countIncludedServings += $remaining;
								}
								else
								{
									$sumIncludedCost += $item->override_price;
									$countIncludedServings += $item->servings_per_item;
								}

								$applied = true;
							}
						}
					}

					$result->wasApplied = $applied;
					if ($applied)
					{
						$result->countIncluded = $countIncludedServings;
						$result->sumIncludedCost = $sumIncludedCost;
					}

					break;
			}
		}
		else
		{
			//error: can't calculate if there are no items
			//CLog::RecordNew(CLog::WARNING, 'CUserPreferred->calculateDiscountTotal no items on the order so discount not calculated', "", "", false);

			return null;
		}

		return $result;
	}
}

?>