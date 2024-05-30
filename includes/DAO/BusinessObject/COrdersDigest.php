<?php

require_once 'DAO/Orders_digest.php';
require_once 'includes/DAO/BusinessObject/COrderMinimum.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: COrdersDigest
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *
 * 	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class COrdersDigest extends DAO_Orders_digest
{

	public static function updateInStoreStatus($order_id, $newStatus)
	{
		if ($newStatus == 0)
		{
			$digestObj = DAO_CFactory::create('orders_digest');
			$digestObj->query("update orders_digest set in_store_trigger_order = null where order_id = $order_id");

			return true;
		}
		else
		{
			$orderSearch = DAO_CFactory::create('booking');
			$orderSearch->query("select GROUP_CONCAT(s2.session_start order by s2.session_start desc) as sessions, GROUP_CONCAT(b2.order_id order by s2.session_start desc) as orders, iq.session_start
				from (select s.session_start, b.user_id from booking b
				join session s on s.id = b.session_id
				where b.status = 'ACTIVE' and b.order_id = $order_id) as iq
				join booking b2 on b2.user_id = iq.user_id and b2.`status` = 'ACTIVE'
				join session s2 on s2.id = b2.session_id
				where s2.session_start < iq.session_start");
			$orderSearch->fetch();

			$sessions = explode(",", $orderSearch->sessions);
			$orders = explode(",", $orderSearch->orders);

			$last_session = array_shift($sessions);
			$trigger_order = array_shift($orders);
			$cur_session = $orderSearch->session_start;

			if ($last_session && strtotime($cur_session) - strtotime($last_session) < (86400 * 60)) // I hereby decree 60 days is the limit
			{
				$digestObj = DAO_CFactory::create('orders_digest');
				$digestObj->query("update orders_digest set in_store_trigger_order = $trigger_order where order_id = $order_id");

				return true;
			}
			else
			{
				// no recent orders looking back so look forward
				$orderSearch = DAO_CFactory::create('booking');
				$orderSearch->query("select GROUP_CONCAT(s2.session_start order by s2.session_start asc) as sessions, GROUP_CONCAT(b2.order_id order by s2.session_start asc) as orders, iq.session_start
					from (select s.session_start, b.user_id from booking b
					join session s on s.id = b.session_id
					where b.status = 'ACTIVE' and b.order_id = $order_id) as iq
					join booking b2 on b2.user_id = iq.user_id and b2.`status` = 'ACTIVE'
					join session s2 on s2.id = b2.session_id
					where s2.session_start > iq.session_start");
				$orderSearch->fetch();

				$sessions = explode(",", $orderSearch->sessions);
				$orders = explode(",", $orderSearch->orders);

				$first_session = array_shift($sessions);
				$trigger_order = array_shift($orders);
				$cur_session = $orderSearch->session_start;

				if ($first_session && strtotime($first_session) - strtotime($cur_session) < (86400 * 60)) // I hereby decree 60 days is the limit
				{
					$digestObj = DAO_CFactory::create('orders_digest');
					$digestObj->query("update orders_digest set in_store_trigger_order = $trigger_order where order_id = $order_id");

					return true;
				}
			}
		}

		return false;
	}

	public static function handleDelayedPaymentSuccess($order_id, $grand_total)
	{
		$newBalanceDue = self::calculateAndAddBalanceDue($order_id, $grand_total);

		$dasOrder = DAO_CFactory::create('orders_digest');
		$dasOrder->query("update orders_digest set balance_due = '$newBalanceDue' where order_id = $order_id");
	}

	public static function calculateAndAddBalanceDue($order_id, $grand_total, $isCancelled = false)
	{
		$paymentObj = DAO_CFactory::create('payment');

		$paymentObj->query("select 
			GROUP_CONCAT(p.payment_type order by p.id) as payment_types, 
			GROUP_CONCAT(p.total_amount order by p.id) as payment_amounts,
			GROUP_CONCAT(p.is_delayed_payment order by p.id) as is_delayed, 
			GROUP_CONCAT(ifnull(p.delayed_payment_status, 0) order by p.id) as delayed_status,
			o.ltd_round_up_value
			from payment p 
			join orders o on o.id = $order_id
			where p.order_id = $order_id and p.is_deleted = 0");

		$paymentObj->fetch();

		$types = explode(",", $paymentObj->payment_types);
		$amounts = explode(",", $paymentObj->payment_amounts);
		$isDelayed = explode(",", $paymentObj->is_delayed);
		$delayedStatus = explode(",", $paymentObj->delayed_status);

		$grand_total = $grand_total + .000000001;
		$grand_total = (int)($grand_total * 100);
		$payment_total = 0;

		$LTD_donation = 0;
		if (!empty($paymentObj->ltd_round_up_value) and is_numeric($paymentObj->ltd_round_up_value))
		{
			$LTD_donation = $paymentObj->ltd_round_up_value * 100;
		}

		if ($isCancelled)
		{
			foreach ($types as $thisType)
			{
				$this_amount = array_shift($amounts);
				$thisDelayed = array_shift($isDelayed);
				$thisDelayedStatus = array_shift($delayedStatus);
				$this_amount = $this_amount + .000000001;

				$this_amount = intval($this_amount * 100);
				switch ($thisType)
				{
					case CPayment::REFUND:
					case CPayment::REFUND_CASH:
					case CPayment::REFUND_STORE_CREDIT:
					case CPayment::REFUND_GIFT_CARD:
						$payment_total -= $this_amount;
						break;
					case CPayment::CREDIT:
						// NO Charge should have no effect
						break;
					default:
					{
						$validPayment = true;

						if ($thisDelayed && ($thisDelayedStatus == 'FAIL' || $thisDelayedStatus == 'CANCELLED' || $thisDelayedStatus == 'PENDING'))
						{
							$validPayment = false;
						}

						if ($validPayment)
						{
							$payment_total += $this_amount;
						}
					}
				}
			}

			return (($payment_total / 100) * -1);
		}
		else
		{

			foreach ($types as $thisType)
			{
				$this_amount = array_shift($amounts);
				$thisDelayed = array_shift($isDelayed);
				$thisDelayedStatus = array_shift($delayedStatus);
				$this_amount = $this_amount + .000000001;

				$this_amount = intval($this_amount * 100);
				switch ($thisType)
				{
					case CPayment::REFUND:
					case CPayment::REFUND_CASH:
					case CPayment::REFUND_STORE_CREDIT:
					case CPayment::REFUND_GIFT_CARD:

						$payment_total -= $this_amount;
						break;
					default:
					{
						$validPayment = true;

						if ($thisDelayed && ($thisDelayedStatus == 'FAIL' || $thisDelayedStatus == 'CANCELLED' || $thisDelayedStatus == 'PENDING'))
						{
							$validPayment = false;
						}

						if ($validPayment)
						{
							$payment_total += $this_amount;
						}
					}
				}
			}

			$Diff = ($grand_total + $LTD_donation) - $payment_total;

			return ($Diff / 100);
		}
	}

	static function updateUserDigestOrderCancelled($DAO_orders_digest)
	{
		if (!empty($DAO_orders_digest->user_id) && is_numeric($DAO_orders_digest->user_id))
		{
			$DAO_user_digest = DAO_CFactory::create('user_digest', true);
			$DAO_user_digest->user_id = $DAO_orders_digest->user_id;

			if ($DAO_user_digest->find(true))
			{
				if ($DAO_orders_digest->session_time == $DAO_user_digest->first_session)
				{
					$DAO_user_digest->first_session = 'null';
				}

				if ($DAO_orders_digest->order_id == $DAO_user_digest->order_id_first_shipping)
				{
					$DAO_user_digest->order_id_first_shipping = 'null';
				}

				if ($DAO_orders_digest->order_id == $DAO_user_digest->order_id_first_home_delivery)
				{
					$DAO_user_digest->order_id_first_home_delivery = 'null';
				}

				if ($DAO_orders_digest->order_id == $DAO_user_digest->order_id_first_pick_up)
				{
					$DAO_user_digest->order_id_first_pick_up = 'null';
				}

				$DAO_user_digest->visit_count--;
				$DAO_user_digest->update();
			}
		}
	}

	public static function determineTotalBoxesOrdered($user_id)
	{
		if (is_null($user_id))
		{
			return 0;
		}
		$ordersDAO = DAO_CFactory::create('orders', true);
		$ordersDAO->query("SELECT
				count( bi.id ) AS total_boxes 
			FROM
				box_instance bi,
				orders o,
				booking b 
			WHERE
				bi.order_id = o.id 
				AND b.order_id = o.id 
				AND o.is_deleted = 0 
				AND b.STATUS = 'ACTIVE' 
				AND b.is_deleted = 0 
				AND bi.is_deleted = 0 
				AND o.user_id = {$user_id}");

		$total_boxes = 0;
		while ($ordersDAO->fetch())
		{
			$total_boxes = $ordersDAO->total_boxes;
		}

		return $total_boxes;
	}

	/**
	 * @param $user_id customer's user_id for which to determine the type of user.
	 *                 4 possible types, no orders, retail only orders, delivery only orders or all.
	 *
	 * ONLY considers orders from ACTIVE stores
	 *
	 * @return mixed|string|null - will be null if the user_id is not supplied, otherwise
	 * in [NO_ORDERS,RETAIL_ORDERS_ONLY,DELIVERED_ORDERS_ONLY,ALL_ORDER_TYPES]
	 * @throws Exception
	 */
	public static function determineCustomerOrderType($user_id)
	{
		if (is_null($user_id))
		{
			return null;
		}

		$orderDigestDAO = DAO_CFactory::create('orders_digest');
		$orderDigestDAO->query("select distinct od.session_type 
			from orders_digest od, store s
			where od.store_id = s.id and
			s.active = 1 and
			od.user_id = {$user_id}");
		$types = array();
		while ($orderDigestDAO->fetch())
		{
			switch ($orderDigestDAO->session_type)
			{
				case 'DELIVERED':
					!in_array('DELIVERED_ORDERS_ONLY', $types) ? $types[] = 'DELIVERED_ORDERS_ONLY' : '';
					break;
				default:
					!in_array('RETAIL_ORDERS_ONLY', $types) ? $types[] = 'RETAIL_ORDERS_ONLY' : '';
					break;
			}
		}

		if (count($types) === 0)
		{
			$types = array('NO_ORDERS');
		}
		else if (count($types) > 1)
		{
			$types = array('ALL_ORDER_TYPES');
		}

		return $types[0];
	}

	static function updateUserDigest($DAO_orders, $session_time, $originalSessionTime = false)
	{
		$DAO_session = $DAO_orders->getSessionObj();

		if (!empty($DAO_orders->user_id) && is_numeric($DAO_orders->user_id))
		{
			$DAO_user_digest = DAO_CFactory::create('user_digest', true);
			$DAO_user_digest->user_id = $DAO_orders->user_id;

			$customerOrderType = self::determineCustomerOrderType($DAO_orders->user_id);
			$totalDeliveredBoxesOrdered = self::determineTotalBoxesOrdered($DAO_orders->user_id);

			if ($DAO_user_digest->find(true))
			{
				$org_DAO_user_digest = clone $DAO_user_digest;

				$DAO_user_digest->total_delivered_boxes = $totalDeliveredBoxesOrdered;

				if (!is_null($customerOrderType))
				{
					$DAO_user_digest->customer_order_type = $customerOrderType;
				}

				if ($DAO_orders->isShipping())
				{
					if (empty($DAO_user_digest->order_id_first_shipping))
					{
						$DAO_user_digest->order_id_first_shipping = $DAO_orders->id;
						$DAO_user_digest->update($org_DAO_user_digest);
					}
				}
				else if ($DAO_orders->isDelivery())
				{
					if (empty($DAO_user_digest->order_id_first_home_delivery))
					{
						$DAO_user_digest->order_id_first_home_delivery = $DAO_orders->id;
						$DAO_user_digest->update($org_DAO_user_digest);
					}
				}
				else if ($DAO_session->isPickUp())
				{
					if (empty($DAO_user_digest->order_id_first_pick_up))
					{
						$DAO_user_digest->order_id_first_pick_up = $DAO_orders->id;
						$DAO_user_digest->update($org_DAO_user_digest);
					}
				}

				if (empty($DAO_user_digest->first_session))
				{
					$DAO_user_digest->first_session = $session_time;
					$DAO_user_digest->visit_count = 1;
					$DAO_user_digest->update($org_DAO_user_digest);
				}
				else
				{
					if ($originalSessionTime && $originalSessionTime == $DAO_user_digest->first_session)
					{
						// rescheduling the original first order
						$DAO_user_digest->first_session = $session_time;
						$DAO_user_digest->update($org_DAO_user_digest);
					}
					else if (!$originalSessionTime && strtotime($DAO_user_digest->first_session) > strtotime($session_time))
					{
						// otherwise a new session is placed for a time earlier than the original so it becomes the first session
						$DAO_user_digest->first_session = $session_time;
						$DAO_user_digest->update($org_DAO_user_digest);
					}
					else if (!$originalSessionTime)
					{
						$DAO_user_digest->visit_count++;
						$DAO_user_digest->update($org_DAO_user_digest);
					}
				}
			}
			else
			{
				$DAO_user_digest->total_delivered_boxes = $totalDeliveredBoxesOrdered;

				if (!is_null($customerOrderType))
				{
					$DAO_user_digest->customer_order_type = $customerOrderType;
				}

				if ($DAO_orders->isShipping())
				{
					if (empty($DAO_user_digest->order_id_first_shipping))
					{
						$DAO_user_digest->order_id_first_shipping = $DAO_orders->id;
					}
				}
				else if ($DAO_orders->isDelivery())
				{
					if (empty($DAO_user_digest->order_id_first_home_delivery))
					{
						$DAO_user_digest->order_id_first_home_delivery = $DAO_orders->id;
					}
				}
				else if ($DAO_session->isPickUp())
				{
					if (empty($DAO_user_digest->order_id_first_pick_up))
					{
						$DAO_user_digest->order_id_first_pick_up = $DAO_orders->id;
					}
				}

				$DAO_user_digest->first_session = $session_time;
				$DAO_user_digest->visit_count = 1;
				$DAO_user_digest->insert();
			}
		}
	}

	static function hadFutureBooking($curOrderTime, $curOrderID, $user_id, $store_id, $tolerance = 0)
	{

		$unadjustedOrderTime = $curOrderTime;
		$curOrderTimeTS = strtotime($curOrderTime) - $tolerance;

		$storeObj = DAO_CFactory::create('store');
		$storeObj->query("select timezone_id from store where id = $store_id");

		if ($storeObj->N > 0)
		{
			$storeObj->fetch();
			$curOrderTime = date("Y-m-d H:i:s", CTimezones::getAdjustedTime($storeObj, $curOrderTimeTS));
		}

		$tempOrderObj = DAO_CFactory::create('orders');

		$tempOrderObj->query("select o.id from orders o
					join booking b on b.order_id = o.id and b.status = 'ACTIVE'
					join session s on s.id = b.session_id
					where o.user_id = $user_id
						 and DATEDIFF('" . $curOrderTime . "', s.session_start ) <= 0
			 		 and o.timestamp_created < '$unadjustedOrderTime'
			 and o.id <> $curOrderID
			 and o.servings_total_count > 17
			 and o.is_TODD <> 1
			 and o.type_of_order <> 'DREAM_TASTE'
			 order by o.id asc");

		if ($tempOrderObj->N > 0)
		{
			$tempOrderObj->fetch();

			return $tempOrderObj->id;
		}

		return false;
	}

	static function isOrderForNextMenu($triggerOrder, $newOrderMenu)
	{

		$triggerBooking = DAO_CFactory::create('booking');

		$triggerBooking->query("select s.menu_id from booking b
 join session s on s.id = b.session_id
 where b.status = 'ACTIVE' and b.order_id = $triggerOrder and b.is_deleted = 0");

		if ($triggerBooking->fetch())
		{
			if ($newOrderMenu <= $triggerBooking->menu_id + 1)
			{
				return true;
			}
		}

		return false;
	}

	static function calculateAddonSales($order_id)
	{

		$Items = DAO_CFactory::create('order_item');

		$Items->query("select sum( oi.sub_total ) as CTS_total from order_item oi
					join menu_item mi on mi.id = oi.menu_item_id and mi.menu_item_category_id = 9
					where oi.order_id = $order_id and oi.is_deleted = 0 and isnull(oi.parent_menu_item_id)
					group by oi.order_id");

		$CTS_total = 0;

		if ($Items->fetch())
		{
			$CTS_total = $Items->CTS_total;
		}

		return $CTS_total;
	}

	static function calculateAGRTotal($order_id, $grand_total, $taxes, $fundraising_total = 0, $ltd_meal_total = 0, $bag_fee = 0)
	{

		// get propgram discounts
		$Credits = DAO_CFactory::create('payment');

		$Credits->query("select sum(p.total_amount) as sc_total from payment p
			Inner Join store_credit sc ON p.store_credit_id = sc.id
			Where p.payment_type = 'STORE_CREDIT' AND p.order_id = $order_id and p.is_deleted = 0 and sc.is_deleted = 0
			and sc.is_redeemed = 1 and (sc.credit_type = 2 OR sc.credit_type = 3) group by p.order_id");

		$programs_total = 0;

		if ($Credits->fetch())
		{
			$programs_total = $Credits->sc_total;
		}

		$Certs = DAO_CFactory::create('payment');

		$Certs->query("select p.total_amount, p.gift_cert_type from payment p
			Where p.payment_type = 'GIFT_CERT' AND p.order_id = $order_id and p.is_deleted = 0");

		$certs_total = 0;

		while ($Certs->fetch())
		{

			if ($Certs->gift_cert_type == 'VOUCHER' || $Certs->gift_cert_type == 'DONATED')
			{
				$certs_total += $Certs->total_amount;
			}
			else if ($Certs->gift_cert_type == 'SCRIP')
			{
				$certs_total += ($Certs->total_amount * 0.12);
			}
		}

		//$agr = $grand_total - ($taxes + $certs_total + $programs_total + $fundraising_total + $ltd_meal_total + $bag_fee);
		// Note: although the bag fee is pased in it is currently not considered and adjustment and is ignored

		$agr = floatVal($grand_total) - (floatVal($taxes) + floatVal($certs_total) + floatVal($programs_total) + floatVal($fundraising_total) + floatVal($ltd_meal_total));

		return $agr;
	}

	static function getUserStateAtOrderTimeOriginal($user_id, $order_id, $order_time, $session_time)
	{
		// Is there any other additional active sessions, any at all
		// If no, Set NEW and exit
		$testObj = DAO_CFactory::create('booking');
		$testObj->query("select o.id, o.timestamp_created, s.session_start from booking b
					join orders o on o.id = b.order_id
					join session s on s.id = b.session_id
					where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0 and
					o.id <> $order_id order by session_start");
		if ($testObj->N == 0)
		{
			return 'NEW';
		}

		$curSessionTimeTS = strtotime($session_time);

		$testArray = array();
		$count = 0;
		while ($testObj->fetch())
		{
			// opportunity for efficiency here, if the new session occurs before all
			// existing sessions then we can return new after we force all others to Existing
			if ($count == 0 && $curSessionTimeTS < strtotime($testObj->session_start))
			{

				$Updater = DAO_CFactory::create('orders_digest');
				$Updater->query("update orders_digest set user_state = 'EXISTING' where user_id = $user_id and order_id <> $order_id and is_deleted = 0");

				return 'NEW';
			}

			$count++;
			$testArray[$testObj->id] = $testObj->session_start;
		}

		// Is the most recent additional session greater than 1 year ago
		// If yes, set REACQUIRED and exit
		$orderTimeTS = strtotime($order_time);
		$last_session = end($testArray);
		$last_sessionTS = strtotime($last_session);

		$oneYearAgo = mktime(0, 0, 0, date("n", $orderTimeTS), date("j", $orderTimeTS), date("Y", $orderTimeTS) - 1);
		if ($last_sessionTS < $oneYearAgo)
		{
			// we can return REACQUIRED here but this state can be supplanted by a future order for an earlier time
			return 'REACQUIRED';
		}

		// OK, tricky part, we can't assume this is an existing order, we must check for the order of things
		if ($last_sessionTS < $curSessionTimeTS)
		{
			// easy, this order's session is further in the future than all others so it must be Existing
			return 'EXISTING';
		}
		else
		{

			$lastSessionConsideredTS = false;
			// Ugh, it could be REACQUIRED or EXISTING at this point
			foreach ($testArray as $oid => $session_start)
			{
				$session_startTS = strtotime($session_start);

				if ($curSessionTimeTS > $session_startTS)
				{
					$lastSessionConsideredTS = $session_startTS;
					continue;
				}
				// this will be the first time the current session is more recent than ana array item
				// so if it is more than a year it is reacquired, set as such and all remaining as Existing
				// otherwise it is existing

				if ($curSessionTimeTS - $lastSessionConsideredTS > (86400 * 365))
				{
					// so this is reacquired so set all future sessions to existing and return
					$Updater = DAO_CFactory::create('orders_digest');
					$Updater->query("update orders_digest set user_state = 'EXISTING' where user_id = $user_id and order_id <> $order_id and is_deleted = 0 and session_time > '$session_time'");

					return 'REACQUIRED';
				}
				break;
			}
		}

		return 'EXISTING';
		/* OLD METHOD

					$testObj = DAO_CFactory::create('booking');
					$testObj->query("select o.id, o.timestamp_created from booking b
							join orders o on o.id = b.order_id
							join session s on s.id = b.session_id
							where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0 and
							s.session_start > DATE_SUB('$order_time', INTERVAL 1 YEAR) and
							o.id <> $order_id and o.timestamp_created < '$order_time'");
					if ($testObj->N > 0)
					{
						return 'EXISTING';
					}

					$testObj = DAO_CFactory::create('booking');
					$testObj->query("select o.id, o.timestamp_created from booking b
							join orders o on o.id = b.order_id
							join session s on s.id = b.session_id
							where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0 and
							 s.session_start < DATE_SUB('$order_time', INTERVAL 1 YEAR)");

					if ($testObj->N > 0)
					{
						return 'REACQUIRED';
					}

					return 'NEW';
		*/
	}

	/**
	 * New: a) first order ever
	 * b) another order within the same menu as the first order ever
	 *
	 * Existing: a) ordered placed after the first menu month
	 * b) any additional order placed after the first menu month
	 *
	 * Reacquired: a) Any order place one year after the previous order
	 * b) any order in the same month as the Reacquired order
	 *
	 *
	 * @param $user_id
	 * @param $order_id
	 * @param $order_time
	 * @param $menu_id
	 *
	 * @return string [ NEW | EXISTING | REACQUIRED]
	 * @throws Exception
	 */
	static function getUserStateAtOrderTime($user_id, $order_id, $order_time, $menu_id)
	{
		// Is there any other additional active sessions, any at all
		// If no, Set NEW and exit
		$testObj = DAO_CFactory::create('booking');

		$sql = "SELECT
					o.id,
					o.timestamp_created,
					s.menu_id,
					s.session_start,
 					od.user_state,
					od.qualifying_order_id
				FROM
					booking b
					JOIN orders o ON o.id = b.order_id
					JOIN session s ON s.id = b.session_id 
					LEFT JOIN orders_digest od ON od.order_id = o.id
				WHERE
					b.user_id = $user_id 
					AND b.STATUS = 'ACTIVE' 
					AND b.is_deleted = 0 
					AND o.id <> $order_id 
				ORDER BY
					s.menu_id,
					s.session_start";

		$testObj->query($sql);
		if ($testObj->N == 0)
		{
			return 'NEW';
		}

		$sessionTestArray = array();
		$menuTestArray = array();
		$count = 0;
		while ($testObj->fetch())
		{
			// opportunity for efficiency here, if the menu of this new order
			// is earlier than the menu id of the existing orders than mark all as existing
			if ($count == 0 && $menu_id < $testObj->menu_id)
			{

				$Updater = DAO_CFactory::create('orders_digest');
				$sql = "UPDATE orders_digest set USER_STATE = 'EXISTING' where
						WHERE
							user_id = $user_id 
							AND order_id <> $order_id 
							AND order_id NOT IN (
							SELECT
								o.id 
							FROM
								booking b
								JOIN orders o ON o.id = b.order_id
								JOIN session s ON s.id = b.session_id 
							WHERE
								b.user_id = $user_id 
								AND b.STATUS = 'ACTIVE' 
								AND b.is_deleted = 0 
								AND o.id <> $order_id 
								AND s.menu_id <> $menu_id 
							) 
							AND is_deleted = 0";
				$Updater->query($sql);

				return 'NEW';
			}

			//If of the same menu as an existing, then this orders user state
			//should be the same as any previous for that menu
			if ($menu_id == $testObj->menu_id)
			{
				return $testObj->user_state;
			}

			$count++;
			$sessionTestArray[$testObj->id] = $testObj->session_start;
			$menuTestArray[$testObj->id] = $testObj->menu_id;
		}

		// Is the most recent additional session greater than 1 year ago
		// If yes, set REACQUIRED and exit
		$orderTimeTS = strtotime($order_time);
		$last_session = end($sessionTestArray);
		$last_sessionTS = strtotime($last_session);

		$oneYearAgo = mktime(0, 0, 0, date("n", $orderTimeTS), date("j", $orderTimeTS), date("Y", $orderTimeTS) - 1);
		if ($last_sessionTS < $oneYearAgo)
		{
			// we can return REACQUIRED here but this state can be supplanted by a future order for an earlier time
			return 'REACQUIRED';
		}

		$last_menu = end($menuTestArray);
		if ($menu_id > $last_menu)
		{
			//Just to be explicit
			return 'EXISTING';
		}

		return 'EXISTING';
	}

	static function recordNewOrder($DAO_orders, $storeObj)
	{
		$DAO_user = DAO_CFactory::create('user');
		$DAO_user->id = $DAO_orders->user_id;

		if ($DAO_user->find(true))
		{
			// need actual timestamp for accuracy
			$tempOrderObj = DAO_CFactory::create('orders');
			$tempOrderObj->id = $DAO_orders->id;
			$tempOrderObj->find(true);

			$DAO_orders->timestamp_created = $tempOrderObj->timestamp_created;

			// In Store Flag Intercept Point
			// called from all ordering methods - the flag already being set and therefore screened for # serving rules
			// If the flag is true this figures out which order triggered to be true, this is recorded in OrdersDigest
			$in_store_trigger_order = 'null';

			if ($DAO_orders->in_store_order)
			{
				$inStoreStatusArray = $DAO_orders->getOrderInStoreStatus();

				$in_store_trigger_order = $inStoreStatusArray['in_store_trigger_order'];
			}

			$AddonTotal = self::calculateAddonSales($DAO_orders->id);
			$AddonTotal += ($DAO_orders->misc_food_subtotal + $DAO_orders->misc_nonfood_subtotal);
			$AGRTotal = self::calculateAGRTotal($DAO_orders->id, $DAO_orders->grand_total, $DAO_orders->subtotal_all_taxes, $DAO_orders->fundraiser_value, $DAO_orders->subtotal_ltd_menu_item_value, $DAO_orders->subtotal_bag_fee);

			$session_type = 'STANDARD';
			switch ($DAO_orders->findSession()->session_type)
			{
				case 'TODD':
				case 'DREAM_TASTE':
					$session_type = 'TASTE';
					break;
				case 'FUNDRAISER':
					$session_type = 'FUNDRAISER';
					break;
				case 'DELIVERED':
					$session_type = 'DELIVERED';
					break;
				case 'SPECIAL_EVENT':
					$session_type = 'MADE_FOR_YOU';
			}

			$orderType = 'REGULAR';
			if ($DAO_orders->is_TODD || $DAO_orders->isDreamTaste())
			{
				$orderType = 'TASTE';
			}
			else if ($DAO_orders->isNewIntroOffer())
			{
				$orderType = 'INTRO';
			}

			if ($DAO_orders->findSession()->session_type == 'FUNDRAISER')
			{
				$orderType = 'FUNDRAISER';
			}

			$DAO_Order_digest = DAO_CFactory::create('orders_digest');
			$session = $DAO_orders->findSession();
			$sessionTime = $session->session_start;
			$sessionID = $session->id;
			$menuID = $session->menu_id;

			$userState = self::getUserStateAtOrderTime($DAO_orders->user_id, $DAO_orders->id, $DAO_orders->timestamp_created, $menuID);

			$balanceDue = self::calculateAndAddBalanceDue($DAO_orders->id, $DAO_orders->grand_total);

			if ($orderType == 'REGULAR' && ($session_type == 'STANDARD' || $session_type == 'MADE_FOR_YOU'))
			{
				$qualifying_order_id = $DAO_user->determineQualifyingOrderId($DAO_orders);

				if (!empty($qualifying_order_id))
				{
					$DAO_Order_digest->qualifying_order_id = $qualifying_order_id;
				}
			}

			$DAO_Order_digest->in_store_trigger_order = $in_store_trigger_order;

			$DAO_Order_digest->order_id = $DAO_orders->id;
			$DAO_Order_digest->user_id = $DAO_orders->user_id;
			$DAO_Order_digest->store_id = $DAO_orders->store_id;
			$DAO_Order_digest->agr_total = $AGRTotal;
			$DAO_Order_digest->addon_total = $AddonTotal;
			$DAO_Order_digest->balance_due = $balanceDue;
			$DAO_Order_digest->original_order_time = $DAO_orders->timestamp_created;
			$DAO_Order_digest->session_id = $sessionID;
			$DAO_Order_digest->session_time = $sessionTime;
			$DAO_Order_digest->session_type = $session_type;
			$DAO_Order_digest->order_type = $orderType;
			$DAO_Order_digest->user_state = $userState;
			$DAO_Order_digest->insert();

			$revenueEvent = DAO_CFactory::create('revenue_event');
			$revenueEvent->event_type = 'ORDERED';
			$revenueEvent->event_time = $DAO_orders->timestamp_created;
			$revenueEvent->store_id = $DAO_orders->store_id;
			$revenueEvent->menu_id = $DAO_orders->findSession()->menu_id;
			$revenueEvent->amount = $DAO_orders->grand_total - $DAO_orders->subtotal_all_taxes;
			$revenueEvent->session_amount = $DAO_orders->grand_total - $DAO_orders->subtotal_all_taxes;
			// The session amount is for producing a session total - due to multiple possible reschedule events
			// only 1 reschedule event can contain the revenue amount. On the intial order the session_amount is equal to the amount
			// If rescheduled outside of the calendar month this will need to be adjusted
			$revenueEvent->session_id = $DAO_orders->findSession()->id;
			$revenueEvent->final_session_id = $DAO_orders->findSession()->id;
			$revenueEvent->order_id = $DAO_orders->id;
			$revenueEvent->positive_affected_month = date("Y-m-01", strtotime($sessionTime));
			$revenueEvent->negative_affected_month = 'null';
			$revenueEvent->insert();

			if (!empty($DAO_orders->fundraiser_value) and is_numeric($DAO_orders->fundraiser_value))
			{
				$FE_revenueEvent = DAO_CFactory::create('revenue_event');
				$FE_revenueEvent->event_type = 'FUNDRAISER_DOLLARS';
				$FE_revenueEvent->event_time = $DAO_orders->timestamp_created;
				$FE_revenueEvent->store_id = $DAO_orders->store_id;
				$FE_revenueEvent->menu_id = $DAO_orders->findSession()->menu_id;
				$FE_revenueEvent->amount = $DAO_orders->fundraiser_value * -1;
				$FE_revenueEvent->session_amount = $DAO_orders->fundraiser_value * -1;
				// The session amount is for producing a session total - due to multiple possible reschedule events
				// only 1 reschedule event can contain the revenue amount. On the intial order the session_amount is equal to the amount
				// If rescheduled outside of the calendar month this will need to be adjusted
				$FE_revenueEvent->session_id = $DAO_orders->findSession()->id;
				$FE_revenueEvent->final_session_id = $DAO_orders->findSession()->id;
				$FE_revenueEvent->order_id = $DAO_orders->id;
				$FE_revenueEvent->positive_affected_month = date("Y-m-01", strtotime($sessionTime));
				$FE_revenueEvent->negative_affected_month = 'null';
				$FE_revenueEvent->insert();
			}

			if (!empty($DAO_orders->subtotal_ltd_menu_item_value) and is_numeric($DAO_orders->subtotal_ltd_menu_item_value))
			{
				$FE_revenueEvent = DAO_CFactory::create('revenue_event');
				$FE_revenueEvent->event_type = 'LTD_MEAL_DONATION';
				$FE_revenueEvent->event_time = $DAO_orders->timestamp_created;
				$FE_revenueEvent->store_id = $DAO_orders->store_id;
				$FE_revenueEvent->menu_id = $DAO_orders->findSession()->menu_id;
				$FE_revenueEvent->amount = $DAO_orders->subtotal_ltd_menu_item_value * -1;
				$FE_revenueEvent->session_amount = $DAO_orders->subtotal_ltd_menu_item_value * -1;
				$FE_revenueEvent->session_id = $DAO_orders->findSession()->id;
				$FE_revenueEvent->final_session_id = $DAO_orders->findSession()->id;
				$FE_revenueEvent->order_id = $DAO_orders->id;
				$FE_revenueEvent->positive_affected_month = date("Y-m-01", strtotime($sessionTime));
				$FE_revenueEvent->negative_affected_month = 'null';
				$FE_revenueEvent->insert();
			}

			self::updateUserDigest($DAO_orders, $sessionTime);

			self::updateLastActivityDate($DAO_orders->store_id, $DAO_orders->timestamp_created);
		}
	}

	static function updateLastActivityDate($store_id, $time)
	{
		$StoreObj = DAO_CFactory::create('store');
		$StoreObj->query("update store set timestamp_last_activity = '$time' where id = $store_id");
	}

	static function recordRescheduledOrder($DAO_orders, $new_time, $store_id, $newSessionID, $menuID, $new_type = 'STANDARD')
	{
		$orderDigest = DAO_CFactory::create('orders_digest');
		$orderDigest->order_id = $DAO_orders->id;
		if (!$orderDigest->find(true))
		{
			throw new Exception("Error finding original order in COrderDigest::recordRescheduledOrder");
		}

		switch ($new_type)
		{
			case 'STANDARD':
				break;
			case 'TODD':
			case 'DREAM_TASTE':
				$new_type = 'TASTE';
				break;
			case 'SPECIAL_EVENT':
				$new_type = 'MADE_FOR_YOU';
				break;
		}

		self::updateUserDigest($DAO_orders, $new_time, $orderDigest->session_time);

		$currentUserState = $orderDigest->user_state;
		$newUserState = $currentUserState;
		$newTimeTS = strtotime($new_time);
		$curSessionTime = $orderDigest->session_time;
		$curSessionTimeTS = strtotime($curSessionTime);

		//WIth Additional Ordering - Simplified because the user state should be consistent throughout the month
		//and a session cannot move menus
		if ($currentUserState == 'EXISTING')
		{
			$newUserState = 'EXISTING';
		}
		else if ($currentUserState == 'NEW')
		{
			//Sequence of orders no longer matters, since only can reschedule into same menu
			$newUserState = 'NEW';
		}
		else if ($currentUserState == 'REACQUIRED')
		{
			$newUserState = 'REACQUIRED';
		}

		$orgObj = clone($orderDigest);
		$orderDigest->session_time = $new_time;
		$orderDigest->session_type = $new_type;
		$orderDigest->session_id = $newSessionID;
		$orderDigest->user_state = $newUserState;
		$orderDigest->update($orgObj);

		if (date("n", $newTimeTS) != date("n", $curSessionTimeTS))
		{
			// Revenue Event management
			$revEvent = DAO_CFactory::create('revenue_event');
			$revEvent->query("update revenue_event set session_amount = 0 where order_id = $DAO_orders->id and is_deleted = 0");

			$BookingObj = DAO_CFactory::create('booking');
			$BookingObj->query("select s.session_start, s.menu_id, s.id as session_id, o.grand_total, o.subtotal_all_taxes from booking b
			 join session s on s.id = b.session_id
			 join orders o on o.id = b.order_id
			 where b.order_id = $DAO_orders->id and b.status = '" . CBooking::ACTIVE . "'");
			$BookingObj->fetch();

			$revenueEvent = DAO_CFactory::create('revenue_event');
			$revenueEvent->event_type = 'RESCHEDULED';
			$revenueEvent->event_time = date("Y-m-d H:i:s");
			$revenueEvent->store_id = $store_id;
			$revenueEvent->menu_id = $BookingObj->menu_id;
			$revenueEvent->amount = ($BookingObj->grand_total - $BookingObj->subtotal_all_taxes);
			$revenueEvent->session_amount = $BookingObj->grand_total - $BookingObj->subtotal_all_taxes;

			$revenueEvent->session_id = $BookingObj->session_id;
			$revenueEvent->order_id = $DAO_orders->id;
			$revenueEvent->positive_affected_month = date("Y-m-01", $newTimeTS);
			$revenueEvent->negative_affected_month = date("Y-m-01", $curSessionTimeTS);
			$revenueEvent->insert();

			// update final session
			$revEvent2 = DAO_CFactory::create('revenue_event');
			$revEvent2->query("update revenue_event set final_session_id = {$BookingObj->session_id} where order_id = $DAO_orders->id and is_deleted = 0");
		}
		else
		{
			$BookingObj = DAO_CFactory::create('booking');
			$BookingObj->query("select b.session_id from booking b
					where b.order_id = $DAO_orders->id and b.status = '" . CBooking::ACTIVE . "'");
			$BookingObj->fetch();

			// update final session

			$revEvent2 = DAO_CFactory::create('revenue_event');
			$revEvent2->query("update revenue_event set final_session_id = {$BookingObj->session_id} where order_id = $DAO_orders->id and is_deleted = 0");
		}

		self::updateLastActivityDate($store_id, date("Y-m-d H:i:s"));
	}

	static function recordCanceledOrder($order_id, $store_id, $menu_id, $grand_total, $fundraiser_value = 0, $ltd_meal_total = 0)
	{
		$DAO_orders_digest = DAO_CFactory::create('orders_digest');

		// TODO: don't throw here. Just email the details
		$DAO_orders_digest->order_id = $order_id;
		if (!$DAO_orders_digest->find(true))
		{
			throw new Exception("Error finding original order in COrderDigest::recordCanceledOrder");
		}

		//this may impact the user_state of orders placed after this one, if this was
		//new or reacquired
		if ($DAO_orders_digest->user_state == 'NEW' || $DAO_orders_digest->user_state == 'REACQUIRED')
		{
			if ($menu_id == false)
			{
				//Unable to update subsequent order_digest's user_state
				CLog::RecordNew(CLog::WARNING, "Unable to update subsequent order_digest's user_state for cancelled order id = {$order_id}", "", "", true);
			}
			else
			{
				$whichState = $DAO_orders_digest->user_state;
				$nextFutureOrder = DAO_CFactory::create('orders_digest');
				$sql = "SELECT
						od.order_id,
						s.menu_id
					FROM
						orders_digest od
						JOIN booking b ON b.order_id = od.order_id
						JOIN orders o ON o.id = od.order_id
						JOIN session s ON s.id = b.session_id 
					WHERE
						od.user_id = {$DAO_orders_digest->user_id} 
						AND od.order_id <> $order_id 
						AND od.is_deleted = 0 
						AND s.menu_id >= $menu_id
					ORDER BY
						s.menu_id;
					";

				$nextFutureOrder->query($sql);

				$next_menu_id = 0;
				if ($nextFutureOrder->N > 0)
				{
					while ($nextFutureOrder->fetch())
					{
						if ($menu_id == $nextFutureOrder->menu_id)
						{
							//same menu do nothing to others - others should already be okay
							break;
						}

						if ($menu_id < $nextFutureOrder->menu_id)
						{
							if ($next_menu_id > 0 && $next_menu_id != $nextFutureOrder->menu_id)
							{
								//we've hit subsequent month so break
								break;
							}
							$next_menu_id = $nextFutureOrder->menu_id;
							//next month exists so need to update all orders in that month only
							$nextFutureOrder->query("update orders_digest set user_state = '$whichState' where order_id = {$nextFutureOrder->order_id}");
						}
					}
				}
			}
		}

		$balanceDue = self::calculateAndAddBalanceDue($order_id, $grand_total, true);

		$sql = "update orders_digest set is_deleted = 1, balance_due = $balanceDue where order_id = $order_id and is_deleted = 0";
		$DAO_orders_digest->query($sql);

		$BookingObj = DAO_CFactory::create('booking');
		$BookingObj->query("select s.session_start, s.menu_id, s.id as session_id, o.grand_total, o.subtotal_all_taxes from booking b
			join session s on s.id = b.session_id
			join orders o on o.id = b.order_id
			where b.order_id = $order_id and b.status = '" . CBooking::CANCELLED . "'");
		$BookingObj->fetch();

		$revenueEvent = DAO_CFactory::create('revenue_event');
		$revenueEvent->event_type = 'CANCELLED';
		$revenueEvent->event_time = date("Y-m-d H:i:s");
		$revenueEvent->store_id = $store_id;
		$revenueEvent->menu_id = $BookingObj->menu_id;
		$revenueEvent->amount = ($BookingObj->grand_total - $BookingObj->subtotal_all_taxes) * -1;
		$revenueEvent->session_amount = ($BookingObj->grand_total - $BookingObj->subtotal_all_taxes) * -1;
		$revenueEvent->session_id = $BookingObj->session_id;
		$revenueEvent->final_session_id = $BookingObj->session_id;
		$revenueEvent->order_id = $order_id;
		$revenueEvent->positive_affected_month = date("Y-m-01", strtotime($BookingObj->session_start));
		$revenueEvent->negative_affected_month = 'null';
		$revenueEvent->insert();

		$paymentEvents = DAO_CFactory::create('revenue_event');

		$paymentEvents->query("select * from revenue_event where event_type = 'GIFT_CERT_ADJUST' and order_id = $order_id and is_deleted = 0");
		while ($paymentEvents->fetch())
		{
			$newBalancingEvent = clone($paymentEvents);

			// no reversal for gift cert yet so we can use the revenue event as the basis to reverses

			$newBalancingEvent->amount *= -1;
			$newBalancingEvent->session_amount *= -1;
			$newBalancingEvent->event_time = date("Y-m-d H:i:s");
			$newBalancingEvent->id = null;

			$newBalancingEvent->insert();
		}

		$payments = DAO_CFactory::create('payment');
		$payments->query("select payment_type, total_amount from payment where payment_type in ('STORE_CREDIT','REFUND_STORE_CREDIT') and order_id = $order_id and is_deleted = 0 order by id asc");
		$reverseAmount = 0;
		while ($payments->fetch())
		{
			if ($payments->payment_type == 'STORE_CREDIT')
			{
				$reverseAmount += $payments->total_amount;
			}
			else if ($payments->payment_type == 'REFUND_STORE_CREDIT')
			{
				$reverseAmount -= $payments->total_amount;
			}
		}

		CLog::Assert($reverseAmount >= 0, "negative total store credit on order: $order_id This should not happen.");

		if ($reverseAmount > 0)
		{
			$SCrevenueEvent = DAO_CFactory::create('revenue_event');
			$SCrevenueEvent->event_type = 'STORE_CREDIT_ADJUST';
			$SCrevenueEvent->event_time = date("Y-m-d H:i:s");
			$SCrevenueEvent->store_id = $store_id;
			$SCrevenueEvent->menu_id = $BookingObj->menu_id;
			$SCrevenueEvent->amount = $reverseAmount;
			$SCrevenueEvent->session_amount = $reverseAmount;
			$SCrevenueEvent->session_id = $BookingObj->session_id;
			$SCrevenueEvent->final_session_id = $BookingObj->session_id;
			$SCrevenueEvent->order_id = $order_id;
			$SCrevenueEvent->positive_affected_month = date("Y-m-01", strtotime($BookingObj->session_start));
			$SCrevenueEvent->negative_affected_month = 'null';
			$SCrevenueEvent->insert();
		}

		if (!empty($fundraiser_value) && is_numeric($fundraiser_value) && $fundraiser_value > 0)
		{
			$FErevenueEvent = DAO_CFactory::create('revenue_event');
			$FErevenueEvent->event_type = 'FUNDRAISER_DOLLARS';
			$FErevenueEvent->event_time = date("Y-m-d H:i:s");
			$FErevenueEvent->store_id = $store_id;
			$FErevenueEvent->menu_id = $BookingObj->menu_id;
			$FErevenueEvent->amount = $fundraiser_value;
			$FErevenueEvent->session_amount = $fundraiser_value;
			$FErevenueEvent->session_id = $BookingObj->session_id;
			$FErevenueEvent->final_session_id = $BookingObj->session_id;
			$FErevenueEvent->order_id = $order_id;
			$FErevenueEvent->positive_affected_month = date("Y-m-01", strtotime($BookingObj->session_start));
			$FErevenueEvent->negative_affected_month = 'null';
			$FErevenueEvent->insert();
		}

		if (!empty($ltd_meal_total) && is_numeric($ltd_meal_total) && $ltd_meal_total > 0)
		{
			$FErevenueEvent = DAO_CFactory::create('revenue_event');
			$FErevenueEvent->event_type = 'LTD_MEAL_DONATION';
			$FErevenueEvent->event_time = date("Y-m-d H:i:s");
			$FErevenueEvent->store_id = $store_id;
			$FErevenueEvent->menu_id = $BookingObj->menu_id;
			$FErevenueEvent->amount = $ltd_meal_total;
			$FErevenueEvent->session_amount = $ltd_meal_total;
			$FErevenueEvent->session_id = $BookingObj->session_id;
			$FErevenueEvent->final_session_id = $BookingObj->session_id;
			$FErevenueEvent->order_id = $order_id;
			$FErevenueEvent->positive_affected_month = date("Y-m-01", strtotime($BookingObj->session_start));
			$FErevenueEvent->negative_affected_month = 'null';
			$FErevenueEvent->insert();
		}

		self::updateUserDigestOrderCancelled($DAO_orders_digest);

		self::updateLastActivityDate($store_id, date("Y-m-d H:i:s"));
	}

	static function recordEditedOrder($OrderObj, $originalGrandTotalMinusTaxes, $isCancelled = false)
	{

		$AddonTotal = self::calculateAddonSales($OrderObj->id);

		$AddonTotal += ($OrderObj->misc_food_subtotal + $OrderObj->misc_nonfood_subtotal);

		$AGRTotal = self::calculateAGRTotal($OrderObj->id, $OrderObj->grand_total, $OrderObj->subtotal_all_taxes, $OrderObj->fundraiser_value, $OrderObj->subtotal_ltd_menu_item_value, $OrderObj->subtotal_bag_fee);

		$orderType = 'REGULAR';

		if ($OrderObj->is_TODD)
		{
			$orderType = 'TASTE';
		}
		else if (!empty($OrderObj->bundle_id) && $OrderObj->bundle_id > 0)
		{
			$bobject = $OrderObj->getBundleObj();

			switch ($bobject->bundle_type)
			{
				case 'TV_OFFER':
					$orderType = 'INTRO';
					break;
				case 'FUNDRAISER':
					$orderType = 'FUNDRAISER';
					break;
				default:
					$orderType = 'TASTE';
			}
		}

		$balanceDue = self::calculateAndAddBalanceDue($OrderObj->id, $OrderObj->grand_total, $isCancelled);

		$qualifyingOrderSql = '';
		$User = DAO_CFactory::create('user');
		$User->query("select id from user where id = {$OrderObj->user_id}");

		if ($User->fetch())
		{
			//update qualifying order if it has changed
			$qualifying_order_id = $User->determineQualifyingOrderId($OrderObj);
			$qualifyingOrderSql = ",qualifying_order_id = " . ($qualifying_order_id == null ? 'null' : $qualifying_order_id);
		}

		$queryObj = DAO_CFactory::create('orders_digest');

		$sql = "update orders_digest set agr_total = $AGRTotal $qualifyingOrderSql, addon_total = $AddonTotal, order_type = '$orderType', balance_due = $balanceDue where order_id = {$OrderObj->id}";
		$queryObj->query($sql);

		$delta = ($OrderObj->grand_total - $OrderObj->subtotal_all_taxes) - $originalGrandTotalMinusTaxes;

		if ($delta != 0 && !$isCancelled)
		{
			$revenueEvent = DAO_CFactory::create('revenue_event');
			$revenueEvent->event_type = 'EDITED';
			$revenueEvent->event_time = date("Y-m-d H:i:s");
			$revenueEvent->store_id = $OrderObj->store_id;
			$revenueEvent->menu_id = $OrderObj->findSession()->menu_id;
			$revenueEvent->amount = $delta;
			$revenueEvent->session_amount = $delta;
			$revenueEvent->session_id = $OrderObj->findSession()->id;
			$revenueEvent->final_session_id = $OrderObj->findSession()->id;
			$revenueEvent->order_id = $OrderObj->id;
			$revenueEvent->positive_affected_month = date("Y-m-01", strtotime($OrderObj->findSession()->session_start));
			$revenueEvent->negative_affected_month = 'null'; // note: negative month is used for reschedules only
			$revenueEvent->insert();
		}

		/// Adjsut fundrasier dollars if need be
		$testRevenueEvent = DAO_CFactory::create('revenue_event');
		$testRevenueEvent->query("select * from revenue_event where order_id = {$OrderObj->id} and event_type = 'FUNDRAISER_DOLLARS' and is_deleted = 0");
		$totalFEAmount = 0;
		while ($testRevenueEvent->fetch())
		{
			$totalFEAmount += $testRevenueEvent->amount;
		}

		$totalFEAmount *= -1;

		if ($OrderObj->fundraiser_value != $totalFEAmount && !$isCancelled)
		{
			$FEdelta = floatval($OrderObj->fundraiser_value) - floatval($totalFEAmount);

			$FErevenueEvent = DAO_CFactory::create('revenue_event');
			$FErevenueEvent->event_type = 'FUNDRAISER_DOLLARS';
			$FErevenueEvent->event_time = date("Y-m-d H:i:s");
			$FErevenueEvent->store_id = $OrderObj->store_id;
			$FErevenueEvent->menu_id = $OrderObj->findSession()->menu_id;
			$FErevenueEvent->amount = $FEdelta * -1;
			$FErevenueEvent->session_amount = $FEdelta * -1;
			$FErevenueEvent->session_id = $OrderObj->findSession()->id;
			$FErevenueEvent->final_session_id = $OrderObj->findSession()->id;
			$FErevenueEvent->order_id = $OrderObj->id;
			$FErevenueEvent->positive_affected_month = date("Y-m-01", strtotime($OrderObj->findSession()->session_start));
			$FErevenueEvent->negative_affected_month = 'null'; // note: negative month is used for reschedules only
			$FErevenueEvent->insert();
		}

		// Adjust LTD meal donation dollars if need be
		$testRevenueEvent2 = DAO_CFactory::create('revenue_event');
		$testRevenueEvent2->query("select * from revenue_event where order_id = {$OrderObj->id} and event_type = 'LTD_MEAL_DONATION' and is_deleted = 0");
		$totalLTDMEalAmount = 0;
		while ($testRevenueEvent2->fetch())
		{
			$totalLTDMEalAmount += $testRevenueEvent2->amount;
		}

		$totalLTDMEalAmount *= -1;

		if ($OrderObj->subtotal_ltd_menu_item_value != $totalLTDMEalAmount && !$isCancelled)
		{
			$LTDMealdelta = $OrderObj->subtotal_ltd_menu_item_value - $totalLTDMEalAmount;

			$FErevenueEvent = DAO_CFactory::create('revenue_event');
			$FErevenueEvent->event_type = 'LTD_MEAL_DONATION';
			$FErevenueEvent->event_time = date("Y-m-d H:i:s");
			$FErevenueEvent->store_id = $OrderObj->store_id;
			$FErevenueEvent->menu_id = $OrderObj->findSession()->menu_id;
			$FErevenueEvent->amount = $LTDMealdelta * -1;
			$FErevenueEvent->session_amount = $LTDMealdelta * -1;
			$FErevenueEvent->session_id = $OrderObj->findSession()->id;
			$FErevenueEvent->final_session_id = $OrderObj->findSession()->id;
			$FErevenueEvent->order_id = $OrderObj->id;
			$FErevenueEvent->positive_affected_month = date("Y-m-01", strtotime($OrderObj->findSession()->session_start));
			$FErevenueEvent->negative_affected_month = 'null'; // note: negative month is used for reschedules only
			$FErevenueEvent->insert();
		}

		self::updateLastActivityDate($OrderObj->store_id, date("Y-m-d H:i:s"));
	}
}

?>