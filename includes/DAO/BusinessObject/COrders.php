<?php

/**
 * subclass of DAO/order
 */
require_once 'includes/DAO/Orders.php';
require_once 'includes/OrdersHelper.php';
require_once 'includes/OrdersCustomization.php';
require_once("includes/DAO/Menu_item_inventory.php");
require_once 'includes/DAO/BusinessObject/CTimezones.php';
require_once 'includes/DAO/BusinessObject/CStore.php';
require_once 'includes/DAO/BusinessObject/CEmail.php';
require_once 'includes/DAO/BusinessObject/CMarkUp.php';
require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');
require_once('includes/DAO/BusinessObject/CMenuItemInventoryHistory.php');
require_once 'includes/DAO/BusinessObject/CUserRetentionData.php';
require_once 'includes/DAO/Mark_up_multi_to_volume_discount.php';
require_once 'includes/DAO/BusinessObject/COrdersDigest.php';
require_once 'includes/DAO/BusinessObject/CPointsUserHistory.php';
require_once 'includes/DAO/BusinessObject/CPointsCredits.php';
require_once 'includes/DAO/BusinessObject/CFundraiser.php';
require_once 'includes/DAO/BusinessObject/CMembershipHistory.php';
require_once 'includes/DAO/BusinessObject/COrderMinimum.php';

class COrders extends DAO_Orders
{
	//Gift Card Consts
	const GIFT_CARD_SHIPPING = 2.00;
	//order types
	const WEB = 'WEB';
	const DIRECT = 'DIRECT';
	const IN_STORE = 'IN_STORE';

	//plan types
	const PLAN_LEGACY = 'LEGACY';
	const PLAN_NO_PLAN = 'NOPLAN';
	const PLAN_QUICK_HEARTY = 'QUICK_HEARTY';
	const PLAN_VARIETY = 'VARIETY';
	const PLAN_VALUE = 'VALUE';
	const PLAN_INTRO = 'INTRO';

	const TODD_FREE_ATTENDANCE_PRODUCT_ID = 9;
	const GIFT_CARD_PRODUCT_ID = 11;

	const INTRO = 'INTRO';
	const DREAM_TASTE = 'DREAM_TASTE';
	const FUNDRAISER = 'FUNDRAISER';
	const STANDARD = 'STANDARD';
	const MADE_FOR_YOU = 'SPECIAL_EVENT';
	const SPECIAL_EVENT = 'SPECIAL_EVENT';
	const TODD = 'TODD';
	const DELIVERED = 'DELIVERED';

	const VERSION = 2;

	//transient fields
	//used prior to saving the order
	protected $items = null; // array of array($qty,$obj)
	protected $products = null; // keep non-food items in separate array
	private $preferred = null;
	protected $mark_up = null;
	private $premium = null;
	private $promo = null;
	protected $coupon = null;
	private $sales_tax = null;
	protected $session = null;
	private $bundle = null;
	protected $store = null;
	protected $menu_id = null;
	protected $user = null;

	protected $isInEditOrder = false;

	public $itemsAdjustedByInventory = null;
	public $itemsAdjustedByInventoryArray = null;

	private $applyPremium = true;

	private $recalculateMealCustomizationFee = true;
	private $allowClosedMealCustomizeSessionRecalculate = false;

	protected $subtotal_food_items_adjusted = 0; //this is what food taxes are applied against

	// client code must set this prior to calculations in order to process plate point credits
	public $storeAndUserSupportPlatePoints = false;
	public $allowOverrideOfServiceFee = false;
	public $allowOverrideOfDeliveryFee = false;

	public $PlatePointsRulesVersion = 1.1;
	public $orderAddress = null;

	public $menu_items_core_total_count;

	//calendar setup vars
	public static $sessionInfo;
	public static $currentMenu;
	public static $startofCurrentMenu = null;
	public static $endofCurrentMenu = null;
	public static $endofPreviousMenuCached = null;
	public static $endofCurrentMenuCached = null;

	public static $currentMonthSuffix = null;
	public static $hasIntroSlots = false;
	public static $foundAtLeastOneOpenSession = false;

	const CALENDAR_NAME = 'sessionCalendar';
	const QUANTITY_PREFIX = 'qty_'; //entrees

	//
	// constructor
	//
	function __construct($dataSelectTable = false)
	{
		parent::__construct($dataSelectTable);
	}

	function find_DAO_orders($n = false)
	{
		// When joining the dao objects, the $this can't have a just wildcard select
		if ($this->_query["data_select"] === "*")
		{
			throw new Exception("When creating this object, second parameter in DAO_CFactory::create() needs to be 'true'");
		}

		$DAO_booking = DAO_CFactory::create('booking', true);
		$DAO_booking->whereAdd("booking.status = 'ACTIVE' OR booking.status = 'SAVED' OR booking.status = 'CANCELLED'");

		$DAO_session = DAO_CFactory::create('session', true);
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('menu', true));
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('store', true));

		$DAO_session_discount = DAO_CFactory::create('session_discount', true);
		$DAO_session_discount->unsetProperty('is_deleted'); // make sure to join deleted rows to account for edited sessions
		$DAO_session->joinAddWhereAsOn($DAO_session_discount, 'LEFT');

		$DAO_session_properties = DAO_CFactory::create('session_properties', true);

		$DAO_dream_taste_event_properties = DAO_CFactory::create('dream_taste_event_properties', true);
		$DAO_dream_taste_event_properties->joinAddWhereAsOn(DAO_CFactory::create('dream_taste_event_theme', true), 'LEFT');
		$DAO_session_properties->joinAddWhereAsOn($DAO_dream_taste_event_properties, 'LEFT');

		$DAO_store_to_fundraiser = DAO_CFactory::create('store_to_fundraiser', true);
		$DAO_fundraiser = DAO_CFactory::create('fundraiser', true);
		$DAO_store_to_fundraiser->joinAddWhereAsOn($DAO_fundraiser, 'LEFT', 'session_fundraiser');
		$DAO_session_properties->joinAddWhereAsOn($DAO_store_to_fundraiser, 'LEFT');

		$DAO_session_properties->joinAddWhereAsOn(DAO_CFactory::create('store_pickup_location', true), 'LEFT');

		$DAO_session->joinAddWhereAsOn($DAO_session_properties, 'LEFT');

		$DAO_booking->joinAddWhereAsOn($DAO_session);

		$this->joinAddWhereAsOn($DAO_booking);

		$DAO_user = DAO_CFactory::create('user', true);
		$DAO_user->unsetProperty('is_deleted'); // ability to look up orders with deleted users
		$this->joinAddWhereAsOn($DAO_user);

		$this->joinAddWhereAsOn(DAO_CFactory::create('bundle', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('coupon_code', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('fundraiser', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('mark_up', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('mark_up_multi', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('user_preferred', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('orders_address', true), 'LEFT');
		$this->joinAddWhereAsOn(DAO_CFactory::create('orders_shipping', true), 'LEFT');

		$DAO_sales_tax = DAO_CFactory::create('sales_tax', true);
		$DAO_sales_tax->unsetProperty('is_deleted'); // ability to reference previous tax record associated with the order
		$this->joinAddWhereAsOn($DAO_sales_tax, 'LEFT');

		return parent::find($n);
	}

	function fetch_DAO_order_item_Array()
	{
		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->id = $this->DAO_menu->id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'join_order_item_order_id' => array($this->id),
			'join_order_item_order' => 'INNER',
			'menu_to_menu_item_store_id' => $this->DAO_store->id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false
		));

		while ($DAO_menu_item->fetch())
		{
			$this->DAO_order_item_Array[$DAO_menu_item->id] = clone $DAO_menu_item;
		}
	}

	function fetch_DAO_payment_Array()
	{
		$DAO_payment = DAO_CFactory::create('payment', true);
		$DAO_payment->order_id = $this->id;
		$DAO_payment->find();

		while ($DAO_payment->fetch())
		{
			$this->DAO_payment_Array[$DAO_payment->id] = clone $DAO_payment;
		}
	}

	// use passed in counts if provided otherwise use the item list of the Order object if provided
	public static function getNumberBagsRequiredFromItems($orderObj, $entreeCount = false, $sidesCount = false)
	{
		// uncomment the code below for a start on supporting sides in the calculation

		//	$sidesCountFinal = 0;
		$entreesCountFinal = 0;
		if (!empty($orderObj) && !empty($orderObj->items))
		{
			//		$sidesCountFinal = $orderObj->countItems(false, 'sides');
			$entreesCountFinal = $orderObj->countItems(false, 'entrees');
		}

		if ($entreeCount > 0)
		{
			$entreesCountFinal = $entreeCount;
		}

		//	if ($sidesCount > 0)
		//	{
		//		$sidesCountFinal = $sidesCount;
		//	}

		$entreesPerBag = 4;
		//	$sidesPerBag = 6;

		$bagsRequired = floor($entreesCountFinal / $entreesPerBag);
		$entreeRemainder = 0;
		if ($entreesCountFinal % $entreesPerBag > 0)
		{
			$entreeRemainder = $entreesPerBag - ($entreesCountFinal % $entreesPerBag);
		}

		if ($entreeRemainder > 0)
		{
			$bagsRequired++;
		}

		/*
			// simple for now since we are assuming 2 sides fit the space of 1 entree. this could change
		$sidesCapacityOfRemainder = $entreeRemainder * 2;
		$sidesCountFinal -= $sidesCapacityOfRemainder;

		if ($sidesCountFinal <= 0)
		{
			return (int)$bagsRequired;
		}

		$sidesBagsRequired = floor($sidesCountFinal / $sidesPerBag);
		$sidesRemainder = 0;
		if ($sidesCountFinal % $sidesPerBag > 0)
		{
			$sidesRemainder = $sidesCountFinal % $sidesPerBag;
		}

		if ($sidesRemainder > 0)
		{
			$sidesBagsRequired++;
		}
		*/

		//return (int)($bagsRequired + $sidesBagsRequired);

		return (int)$bagsRequired;
	}

	// use passed in counts if provided otherwise use the item list of the Order object if provided
	public static function getNumberOfCustomizableMealsFromItems($orderObj, $include_pre_assembled = true)
	{
		$entreesCountFinal = 0;
		if (!empty($orderObj) && !empty($orderObj->items))
		{
			$entreesCountFinal = $orderObj->countItems(false, 'core_entrees', !$include_pre_assembled);
		}

		return (int)$entreesCountFinal;
	}

	public static function getTypeofOrderDisplayString($type)
	{
		if ($type == 'INTRO')
		{
			return 'STARTER PACK';
		}

		return $type;
	}

	function orderAddress()
	{
		$deliveryAddress = DAO_CFactory::create('orders_address');

		if ($this->id)
		{
			$deliveryAddress->order_id = $this->id;
			$deliveryAddress->find(true);
		}

		return $this->orderAddress = $deliveryAddress;
	}

	function orderAddressDeliveryProcessUpdate()
	{
		// delete old address
		$deliveryAddress = DAO_CFactory::create('orders_address');
		$deliveryAddress->order_id = $this->id;
		$deliveryAddress->find(true);
		$deliveryAddress->delete();

		// insert new address
		$this->orderAddress->insert();
	}

	static function getOrdersSequenceStatus($order_id, $user_id, $session_type, $sessionStart, $totalServings)
	{

		if ($totalServings < 36 || ($session_type <> CSession::STANDARD && $session_type <> CSession::SPECIAL_EVENT))
		{
			return false;
		}

		$orderHistory = new DAO();
		$orderHistory->query("select o.id, o.type_of_order, o.is_in_plate_points_program, o.in_store_order, s.menu_id from booking b 
                            join session s on s.id = b.session_id and s.session_start <= '$sessionStart'
                            join orders o on o.id = b.order_id and o.type_of_order = 'STANDARD' and o.servings_total_count > 35
                            where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0 order by s.session_start desc");

		if ($orderHistory->N == 0)
		{
			// no orders
			return 1;
		}

		$lastMenuID = false;
		$counting = false;
		while ($orderHistory->fetch())
		{


			if ($counting)
			{
				if ($orderHistory->menu_id == $lastMenuID)
				{
					// 2 std orders in same menu so ignore the second one
					continue;
				}
				else if ($orderHistory->menu_id == $lastMenuID - 1)
				{
					// contiguous!!
					$counting++;
				}
				else
				{
					//skipped
					break;
				}
			}

			if ($orderHistory->id == $order_id)
			{
				$counting = 1;
			}

			$lastMenuID = $orderHistory->menu_id;
		}

		return $counting;
	}

	public static function std_round($myFloat, $d = 2)
	{
		//.005 does not always round up. if we add a tiny number, it will.
		return round($myFloat + .000001, $d);
	}

	public static function isPriceGreaterThan($firstFloat, $secondFloat)
	{

		if (!is_numeric($firstFloat))
		{
			return false;
		}

		if (!is_numeric($secondFloat))
		{
			return false;
		}

		$firstCmp = (int)(self::std_round($firstFloat * 100));
		$secondCmp = (int)(self::std_round($secondFloat * 100));

		if ($firstCmp > $secondCmp)
		{
			return true;
		}

		return false;
	}

	public static function isPriceLessThan($firstFloat, $secondFloat)
	{

		if (!is_numeric($firstFloat))
		{
			return false;
		}

		if (!is_numeric($secondFloat))
		{
			return false;
		}

		$firstCmp = (int)(self::std_round($firstFloat * 100));
		$secondCmp = (int)(self::std_round($secondFloat * 100));

		if ($firstCmp < $secondCmp)
		{
			return true;
		}

		return false;
	}

	public static function isPriceGreaterThanOrEqualTo($firstFloat, $secondFloat)
	{

		if (!is_numeric($firstFloat))
		{
			return false;
		}

		if (!is_numeric($secondFloat))
		{
			return false;
		}

		$firstCmp = (int)(self::std_round($firstFloat * 100));
		$secondCmp = (int)(self::std_round($secondFloat * 100));

		if ($firstCmp >= $secondCmp)
		{
			return true;
		}

		return false;
	}

	public static function isPriceEqualTo($firstFloat, $secondFloat)
	{

		if (!is_numeric($firstFloat))
		{
			return false;
		}

		if (!is_numeric($secondFloat))
		{
			return false;
		}

		$firstCmp = (int)(self::std_round($firstFloat * 100));
		$secondCmp = (int)(self::std_round($secondFloat * 100));

		if ($firstCmp == $secondCmp)
		{
			return true;
		}

		return false;
	}

	public static function getOrderHistory($order_id, $returnBooking = true, $returnSessionDetails = false)
	{
		$retVal = array();

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->query("select b.*, CONCAT(u1.firstname, ' ', u1.lastname) as creator, u1.user_type as creator_user_type, u2.user_type as updator_user_type,
							CONCAT(u2.firstname, ' ',u2.lastname) as updator, 	u1.id as creator_id, u2.id as updator_id, s.session_start ,
       						s.id as session_id
									from booking b
									left join user u1 on b.created_by = u1.id
									left join user u2 on b.updated_by = u2.id
									left join session s on s.id = b.session_id and s.is_deleted = 0
									where b.order_id = $order_id and b.is_deleted = 0
									order by b.id asc");

		$numFound = $bookingObj->N;

		$orderObj = DAO_CFactory::create('orders');
		$orderObj->query("select 
							o.id, 
							o.store_id, 
							o.grand_total, 
							o.servings_total_count, 
							o.menu_items_total_count,
							o.opted_to_customize_recipes,
							o.total_customized_meal_count,
       						o.subtotal_meal_customization_fee,
       						o.order_customization,
							CONCAT(u1.firstname, ' ', u1.lastname) as creator,
							CONCAT(u2.firstname, ' ',u2.lastname) as updator,		
							u1.id as creator_id, 
							u2.id as updator_id,
							o.timestamp_created, 
							o.timestamp_updated,
							o.order_type as order_type,
							SUM(mi.is_store_special * oi.item_count) total_efl_item_count
							from orders o
							left join user u1 on o.created_by = u1.id
							left join user u2 on o.updated_by = u2.id
							left join order_item as oi on oi.order_id = o.id and oi.is_deleted = 0
							left join menu_item as mi on oi.menu_item_id = mi.id and mi.is_deleted = 0
							where o.id = $order_id
							and o.is_deleted = 0
							group by o.id");

		$orderObj->fetch();

		$hasFoundFirstReschedule = false;
		$lastObject = null;

		while ($bookingObj->fetch())
		{
			$sessionData = null;
			if ($returnSessionDetails)
			{
				$sessionData = CSession::getSessionDetail($bookingObj->session_id, false);
			}

			switch ($bookingObj->status)
			{
				case CBooking::SAVED:

					CLog::ASSERT($numFound == 1, "There should only be 1 saved booking per order");

					$retVal[] = array(
						'time' => $bookingObj->timestamp_created,
						'session' => $bookingObj->session_start,
						'action' => "Order Saved",
						'user' => $bookingObj->creator,
						'user_id' => $bookingObj->creator_id,
						'user_type' => $bookingObj->creator_user_type,
						'type' => 'SAVED',
						'total' => $orderObj->grand_total,
						'item_count' => $orderObj->menu_items_total_count,
						'servings' => $orderObj->servings_total_count,
						'order_id' => $orderObj->id,
						'order_type' => $orderObj->order_type,
						'total_efl_item_count' => $orderObj->total_efl_item_count,
						'session_data' => $sessionData,
						'order_data' => $orderObj
					);

					break;

				case CBooking::RESCHEDULED:

					if (!$hasFoundFirstReschedule)
					{

						$retVal[] = array(
							'time' => $bookingObj->timestamp_created,
							'session' => $bookingObj->session_start,
							'action' => "Order Placed",
							'user' => $bookingObj->creator,
							'user_id' => $bookingObj->creator_id,
							'user_type' => $bookingObj->creator_user_type,
							'type' => 'PLACED',
							'total' => $orderObj->grand_total,
							'item_count' => $orderObj->menu_items_total_count,
							'servings' => $orderObj->servings_total_count,
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);

						// The first reschedule is actually the first booking row and so may have originally been in the SAVED state.  Check for time difference and insert
						// saved event if so.

						if (abs(strtotime($bookingObj->timestamp_created) - strtotime($orderObj->timestamp_created)) > 60 && $numFound > 1)
						{
							$retVal[] = array(
								'time' => $bookingObj->timestamp_created,
								'action' => 'Order saved',
								'user' => $bookingObj->creator,
								'user_id' => $bookingObj->creator_id,
								'user_type' => $bookingObj->creator_user_type,
								'type' => 'SAVED',
								'total' => "-",
								'item_count' => "-",
								'servings' => "-",
								'order_id' => $orderObj->id,
								'order_type' => $orderObj->order_type,
								'total_efl_item_count' => $orderObj->total_efl_item_count,
								'session_data' => $sessionData,
								'order_data' => $orderObj
							);

							$retVal[0]['time'] = $orderObj->timestamp_created;
						}

						$hasFoundFirstReschedule = true;
					}
					else
					{

						$retVal[] = array(
							'time' => $lastObject->timestamp_updated,
							'session' => $bookingObj->session_start,
							'action' => "Rescheduled from " . CTemplate::dateTimeFormat($lastObject->session_start) . " to  " . CTemplate::dateTimeFormat($bookingObj->session_start),
							'date_string' => "Rescheduled from " . CTemplate::dateTimeFormat($lastObject->session_start) . " to  " . CTemplate::dateTimeFormat($bookingObj->session_start),
							'user' => $lastObject->updator,
							'user_id' => $lastObject->updator_id,
							'user_type' => $lastObject->updator_user_type,
							'type' => 'RESCHEDULED',
							'total' => $orderObj->grand_total,
							'item_count' => $orderObj->menu_items_total_count,
							'servings' => $orderObj->servings_total_count,
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);
					}
					break;

				case CBooking::ACTIVE:

					if (!empty($lastObject) && $lastObject->status == CBooking::RESCHEDULED)
					{
						$retVal[] = array(
							'time' => $lastObject->timestamp_updated,
							'session' => $bookingObj->session_start,
							'action' => "Rescheduled from " . CTemplate::dateTimeFormat($lastObject->session_start) . " to  " . CTemplate::dateTimeFormat($bookingObj->session_start),
							'date_string' => "from " . CTemplate::dateTimeFormat($lastObject->session_start) . " to  " . CTemplate::dateTimeFormat($bookingObj->session_start),
							'user' => $lastObject->updator,
							'user_id' => $lastObject->updator_id,
							'user_type' => $lastObject->updator_user_type,
							'type' => 'RESCHEDULED',
							'total' => $orderObj->grand_total,
							'item_count' => $orderObj->menu_items_total_count,
							'servings' => $orderObj->servings_total_count,
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);
					}

					if ($numFound == 1)
					{

						$retVal[] = array(
							'time' => $orderObj->timestamp_created,
							'session' => $bookingObj->session_start,
							'action' => "Order Placed",
							'user' => $bookingObj->creator,
							'user_id' => $bookingObj->creator_id,
							'user_type' => $bookingObj->creator_user_type,
							'type' => 'PLACED',
							'total' => $orderObj->grand_total,
							'item_count' => $orderObj->menu_items_total_count,
							'servings' => $orderObj->servings_total_count,
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);
					}

					$storeObj = DAO_CFactory::create('store');

					$storeObj->query("select timezone_id from store where id = {$orderObj->store_id}");
					$storeObj->fetch();
					$todayTS = CTimezones::getAdjustedServerTime($storeObj);

					if ($todayTS < strtotime($bookingObj->session_start))
					{
						$descStr = "Current Status: Pending";
					}
					else
					{
						$descStr = "Current Status: Complete";
					}

					$retVal[] = array(
						'time' => date("Y-m-d H:i:s"),
						'action' => $descStr,
						'user' => "-",
						'user_id' => "-",
						'user_type' => "",
						'type' => 'CURRENT',
						'total' => $orderObj->grand_total,
						'item_count' => $orderObj->menu_items_total_count,
						'servings' => $orderObj->servings_total_count,
						'order_id' => $orderObj->id,
						'order_type' => $orderObj->order_type,
						'total_efl_item_count' => $orderObj->total_efl_item_count,
						'session_data' => $sessionData,
						'order_data' => $orderObj
					);

					if (abs(strtotime($bookingObj->timestamp_created) - strtotime($orderObj->timestamp_created)) > 60 && $numFound == 1)
					{
						$retVal[] = array(
							'time' => $bookingObj->timestamp_created,
							'action' => 'Order saved',
							'user' => $bookingObj->creator,
							'user_id' => $bookingObj->creator_id,
							'user_type' => $bookingObj->creator_user_type,
							'type' => 'SAVED',
							'total' => "-",
							'item_count' => "-",
							'servings' => "-",
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);
					}

					break;

				case CBooking::CANCELLED:

					if (isset($lastObject) && $lastObject->status == CBooking::RESCHEDULED)
					{
						$retVal[] = array(
							'time' => $lastObject->timestamp_updated,
							'session' => $bookingObj->session_start,
							'action' => "Rescheduled from " . CTemplate::dateTimeFormat($lastObject->session_start) . " to  " . CTemplate::dateTimeFormat($bookingObj->session_start),
							'date_string' => "from " . CTemplate::dateTimeFormat($lastObject->session_start) . " to  " . CTemplate::dateTimeFormat($bookingObj->session_start),
							'user' => $lastObject->updator,
							'user_id' => $lastObject->updator_id,
							'user_type' => $lastObject->updator_user_type,
							'type' => 'RESCHEDULED',
							'total' => $orderObj->grand_total,
							'item_count' => $orderObj->menu_items_total_count,
							'servings' => $orderObj->servings_total_count,
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);
					}

					if ($numFound == 1)
					{

						$retVal[] = array(
							'time' => $orderObj->timestamp_created,
							'session' => $bookingObj->session_start,
							'action' => "Order Placed",
							'user' => $bookingObj->creator,
							'user_id' => $bookingObj->creator_id,
							'user_type' => $bookingObj->creator_user_type,
							'type' => 'PLACED',
							'total' => $orderObj->grand_total,
							'item_count' => $orderObj->menu_items_total_count,
							'servings' => $orderObj->servings_total_count,
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);
					}

					$retVal[] = array(
						'time' => $bookingObj->timestamp_updated,
						'action' => "Order Cancelled",
						'user' => $bookingObj->updator,
						'user_id' => $bookingObj->updator_id,
						'user_type' => $bookingObj->updator_user_type,
						'total' => $orderObj->grand_total,
						'type' => 'CANCELLED',
						'item_count' => $orderObj->menu_items_total_count,
						'servings' => $orderObj->servings_total_count,
						'order_id' => $orderObj->id,
						'order_type' => $orderObj->order_type,
						'total_efl_item_count' => $orderObj->total_efl_item_count,
						'session_data' => $sessionData,
						'order_data' => $orderObj
					);

					if (abs(strtotime($bookingObj->timestamp_created) - strtotime($orderObj->timestamp_created)) > 60 && $numFound == 1)
					{
						$retVal[] = array(
							'time' => $bookingObj->timestamp_created,
							'action' => 'Order saved',
							'user' => $bookingObj->creator,
							'user_id' => $bookingObj->creator_id,
							'user_type' => $bookingObj->creator_user_type,
							'type' => 'SAVED',
							'total' => "-",
							'item_count' => "-",
							'servings' => "-",
							'order_id' => $orderObj->id,
							'order_type' => $orderObj->order_type,
							'total_efl_item_count' => $orderObj->total_efl_item_count,
							'session_data' => $sessionData,
							'order_data' => $orderObj
						);
					}

					break;
			}

			$lastObject = clone($bookingObj);
		}

		$editedOrders = DAO_CFactory::create('edited_orders');
		$editedOrders->query("select u1.id as creator_id,u1.user_type, eo.original_order_id, eo.order_type, eo.grand_total, eo.menu_items_total_count, eo.servings_total_count, CONCAT(u1.firstname, ' ', u1.lastname) as creator, eo.timestamp_created, eo.order_revision_notes from edited_orders eo
									left join user u1 on  eo.created_by = u1.id
									where eo.original_order_id = $order_id
									order by eo.id asc");

		while ($editedOrders->fetch())
		{
			$sessionData = null;
			if ($returnSessionDetails)
			{
				$sessionData = CSession::getSessionDetail($bookingObj->session_id, false);
			}

			$entry = array(
				'time' => $editedOrders->timestamp_created,
				'action' => "Order edited",
				'user' => $editedOrders->creator,
				'user_id' => $editedOrders->creator_id,
				'user_type' => $editedOrders->user_type,
				'total' => $editedOrders->grand_total,
				'type' => 'EDITED',
				'item_count' => $editedOrders->menu_items_total_count,
				'servings' => $editedOrders->servings_total_count,
				'notes' => $editedOrders->order_revision_notes,
				'order_id' => $editedOrders->original_order_id,
				'order_type' => $orderObj->order_type,
				'session_data' => $sessionData,
				'order_data' => $orderObj
			);

			$retVal[] = $entry;
		}

		usort($retVal, 'history_sort_backwards');

		$currentGrandTotal = $orderObj->grand_total;
		$currentItemCount = $orderObj->menu_items_total_count;
		$currentServingsCount = $orderObj->servings_total_count;

		foreach ($retVal as &$thisEntry)
		{
			if ($thisEntry['type'] == 'EDITED')
			{
				$prevGrandTotal = $thisEntry['total'];
				$prevItemCount = $thisEntry['item_count'];
				$prevServingsCount = $thisEntry['servings'];
			}

			$thisEntry['total'] = $currentGrandTotal;
			$thisEntry['item_count'] = $currentItemCount;
			$thisEntry['servings'] = $currentServingsCount;

			if ($thisEntry['type'] == 'EDITED')
			{
				$currentGrandTotal = $prevGrandTotal;
				$currentItemCount = $prevItemCount;
				$currentServingsCount = $prevServingsCount;
			}
		}

		if ($returnBooking)
		{
			return array(
				$retVal,
				$bookingObj
			);
		}
		else
		{
			return $retVal;
		}
	}


	// need by PLATEPOINTS System.
	// If the 2 taxes are equal then PLATEPOINTS discounts can be applied without regard'
	// for the specific costs being discounted. If not then the user must indicate which to
	// discount so tax is applied appropriately
	function getFoodAndServiceRelation()
	{
		if (!$this->sales_tax)
		{
			return 'equal';
		}

		if ($this->sales_tax->other1_tax == $this->sales_tax->food_tax)
		{
			return 'equal';
		}

		if ($this->sales_tax->other1_tax > $this->sales_tax->food_tax)
		{
			return 'svc_tax_greater';
		}
		else
		{
			return 'food_tax_greater';
		}
	}

	/**
	 * For unsaved orders
	 */
	function getItems()
	{
		return $this->items;
	}

	function getBundleServingsNeededArray()
	{
		if (!$this->items)
		{
			return null;
		}

		$retVal = array();

		foreach ($this->items as $id => $itemObj)
		{

			if ($itemObj[1]->is_bundle)
			{
				$bundleObj = DAO_CFactory::create('bundle');
				$bundleObj->query("select number_items_required from bundle where master_menu_item = $id and is_deleted = 0");
				if ($bundleObj->fetch())
				{
					$requiredAmount = $bundleObj->number_items_required * $itemObj[0];
					$amountFound = 0;
					foreach ($this->items as $sid => $subItemObj)
					{
						if (isset($subItemObj[1]->parentItemId) && is_numeric($subItemObj[1]->parentItemId) && $subItemObj[1]->parentItemId == $id)
						{
							$amountFound += $subItemObj[1]->bundleItemCount;
						}
					}

					$retVal[$id] = $requiredAmount - $amountFound;
				}
				else
				{
					throw new Exception("item is marked as bundle but no master item found in bundle table.");
				}
			}
		}

		if (empty($retVal))
		{
			return null;
		}

		return $retVal;
	}

	function areMIBundleRulesMet()
	{
		if (!$this->items)
		{
			return true;
		}

		$retVal = true;

		foreach ($this->items as $id => $itemObj)
		{

			if ($itemObj[1]->is_bundle)
			{
				$bundleObj = DAO_CFactory::create('bundle');
				$bundleObj->query("select number_items_required from bundle where master_menu_item = $id and is_deleted = 0");
				if ($bundleObj->fetch())
				{
					$requiredAmount = $bundleObj->number_items_required * $itemObj[0];
					$amountFound = 0;
					foreach ($this->items as $sid => $subItemObj)
					{
						if (isset($subItemObj[1]->parentItemId) && is_numeric($subItemObj[1]->parentItemId) && $subItemObj[1]->parentItemId == $id)
						{
							$amountFound += $subItemObj[1]->bundleItemCount;
						}
					}

					if ($amountFound != $requiredAmount)
					{
						$retVal = false;
					}
				}
				else
				{
					throw new Exception("item is marked as bundle but no master item found in bundle table.");
				}
			}
		}

		return $retVal;
	}

	function getStoreOrderInStoreStatus()
	{
		$inStoreStatusArray = array(
			'in_store_order' => false,
			'in_store_trigger_order' => 'null'
		);

		// check these because it might be possible to be on the customer site without one of these attached to the order yet
		$userObj = $this->getUser();
		$menu_id = $this->getMenuId();
		$session_id = $this->getSessionId();

		// required info needed to calculate
		if (!is_object($userObj) || empty($userObj->id) || empty($menu_id) || empty($session_id))
		{
			CLog::Assert(false, "user_id, menu_id and session_id are required by getStoreOrderInStoreStatus for order " . $this->id);

			return 0;
		}

		// In Store Flag Intercept Point - fadmin
		if (empty($this->timestamp_created))
		{
			$time = date('Y-m-d H:i:s');
		}
		else
		{
			$time = $this->timestamp_created;
		}

		$currentOrder = $userObj->wasInStoreNearTime($time, $session_id);

		// note: Booking is still in the saved state so this order will not be returned as a pending future order
		// no need to pass the order id
		$pendingOrder = $userObj->hasPendingOrderForInStoreCalculation($this->id, $this->store_id, $menu_id, 86400);

		if ($currentOrder)
		{
			$inStoreStatusArray['in_store_order'] = COrdersDigest::isOrderForNextMenu($currentOrder, $menu_id);

			if ($inStoreStatusArray['in_store_order'])
			{
				$inStoreStatusArray['in_store_trigger_order'] = $currentOrder;
			}
		}

		if (!$inStoreStatusArray['in_store_order'] && $pendingOrder)
		{
			$inStoreStatusArray['in_store_order'] = COrdersDigest::isOrderForNextMenu($pendingOrder, $menu_id);

			if ($inStoreStatusArray['in_store_order'])
			{
				$inStoreStatusArray['in_store_trigger_order'] = $pendingOrder;
			}
		}

		if ($inStoreStatusArray['in_store_order'])
		{
			if ($this->is_TODD || $this->isDreamTaste())
			{
				$inStoreStatusArray['in_store_order'] = false;
			}

			$order_minimum = COrderMinimum::fetchInstance(COrders::STANDARD, $this->store_id, $menu_id);

			if (!is_null($order_minimum))
			{
				if ($order_minimum->getMinimumType() == COrderMinimum::SERVING)
				{
					$min = $order_minimum->getMinimum();
					if (!$this->isBundleOrder() && $this->servings_total_count < $min)
					{
						$inStoreStatusArray['in_store_order'] = false;
					}
				}

				if ($order_minimum->getMinimumType() == COrderMinimum::ITEM)
				{
					$min = $order_minimum->getMinimum();
					if (!$this->isBundleOrder() && $this->menu_items_core_total_count < $min)
					{
						$inStoreStatusArray['in_store_order'] = false;
					}
				}
			}
			else
			{
				if (!$this->isBundleOrder() && $this->servings_total_count < 36)
				{
					$inStoreStatusArray['in_store_order'] = false;
				}
			}
		}

		return $inStoreStatusArray;
	}

	function getOrderInStoreStatus()
	{
		// Dec 2022 multiplier and in_store are the same rules
		$isEligible = $this->getStoreOrderInStoreStatus();

		return $isEligible;
	}

	function getOrderMultiplierEligibility()
	{
		// Dec 2022 multiplier and in_store are the same rules
		$isEligible = $this->getStoreOrderInStoreStatus();

		return $isEligible;
	}

	function setOrderInStoreStatus()
	{
		$inStoreStatusArray = $this->getOrderInStoreStatus();

		$this->in_store_order = ($inStoreStatusArray['in_store_order'] ? 1 : 0);
	}

	function setOrderMultiplierEligibility()
	{
		$inStoreStatusArray = $this->getOrderMultiplierEligibility();

		$this->is_multiplier_eligible = ($inStoreStatusArray['in_store_order'] ? 1 : 0);
	}
	//update the store's setting on the order so that if they change in the future
	//it will not impact this order
	function setStoreCustomizationOptions()
	{
		$StoreObj = $this->getStore();
		$orderCustomization = OrdersCustomization::getInstance($this);
		$orderCustomization->setStoreAllowsMealCustomization($StoreObj->supports_meal_customization);
		$orderCustomization->setStoreAllowsPreassembledCustomization($StoreObj->allow_preassembled_customization);
		$this->order_customization = $orderCustomization->orderCustomizationToJson();
	}

	// set current state of food order: noFood; adequateFood; inadequateFoodIntro; inadequateFoodStandard
	function getOrderFoodState($CartObj = null, $orderMinimum = null)
	{
		if (empty($this->items))
		{
			return "noFood";
		}
		if (!$this->areMIBundleRulesMet())
		{
			return 'bundleRulesNotMet';
		}

		$numServings = $this->getNumberQualifyingServings();
		$totalItemQuantity = $this->getTotalItemQuantity();

		if ($this->isBundleOrder())
		{
			$Bundle = $this->getBundleObj();

			if ($numServings == $Bundle->number_servings_required)
			{
				return 'adequateFood';
			}
			else if ($numServings > $Bundle->number_servings_required)
			{
				return 'bundleOverError';
			}

			return 'inadequateFoodIntro';
		}

		$minimumType = null;
		$minimumQty = null;
		if (!is_null($orderMinimum))
		{
			$minimumType = $orderMinimum->getMinimumType();
			$minimumQty = $orderMinimum->getMinimum();
		}

		if (is_null($orderMinimum))
		{
			if ($numServings >= 36)
			{
				return 'adequateFood';
			}
		}
		else if (!$orderMinimum->isMinimumApplicable())
		{
			if ($totalItemQuantity >= 1)
			{
				return 'adequateFood';
			}
		}
		else if ($minimumType == COrderMinimum::SERVING)
		{
			if ($numServings >= $minimumQty)
			{
				return 'adequateFood';
			}
		}
		else if ($minimumType == COrderMinimum::ITEM)
		{
			if ($totalItemQuantity >= $minimumQty)
			{
				return 'adequateFood';
			}
		}

		return 'inadequateFoodStandard';
	}

	function getMarkUp()
	{
		return $this->mark_up;
	}

	function getProducts()
	{
		return $this->products;
	}

	function getBundleObj()
	{
		if (isset($this->bundle))
		{
			return $this->bundle;
		}

		if ($this->bundle_id)
		{
			$Bundle = DAO_CFactory::create('bundle');
			$Bundle->id = $this->bundle_id;
			$Bundle->find(true);
			$this->bundle = $Bundle;

			return $Bundle;
		}

		return false;
	}

	function getPreferredObj()
	{
		if (isset($this->preferred))
		{
			return $this->preferred;
		}

		if ($this->user_preferred_id)
		{
			$Preferred = DAO_CFactory::create('user_preferred');
			$Preferred->id = $this->user_preferred_id;

			if ($Preferred->find(true))
			{
				$this->preferred = $Preferred;

				return $Preferred;
			}
		}

		return false;
	}

	function getStoreObj()
	{
		if (isset($this->store))
		{
			return $this->store;
		}

		return false;
	}

	function getSessionObj($doLookup = false)
	{
		if (is_object($this->session))
		{
			return $this->session;
		}

		if ($doLookup)
		{
			$booking = DAO_CFactory::create('booking');
			$session = DAO_CFactory::create('session');

			$booking->order_id = $this->id;

			if (!$booking->find(true))
			{
				throw new Exception('Booking not found in getSessionObj(); OrderID = ' . $this->id);
			}

			$session->id = $booking->session_id;

			if (!$session->find(true))
			{
				throw new Exception('Session not found in getSessionObj()');
			}

			//$this->session = $session;

			return $session;
		}

		return false;
	}

	function hasBundleOfferItem($id)
	{
		if (!isset($this->bundle))
		{
			return false;
		}

		if (!isset($this->bundle->items))
		{
			return false;
		}

		if (array_key_exists($id, $this->bundle->items) && $this->bundle->items[$id]['chosen'])
		{
			return true;
		}

		return false;
	}

	function hasItemByRecipeId($recipeIds)
	{
		if (!isset($this->items))
		{
			return false;
		}

		if (!isset($this->items))
		{
			return false;
		}

		foreach ($this->items as $id => $itemInfo)
		{
			$item = $itemInfo[1];

			if (in_array($item->recipe_id, $recipeIds))
			{
				return true;
			}
		}

		return false;
	}

	function addBundleWithID($bundle_id, $selectedItems = false, $newCart = false)
	{
		if (empty($bundle_id))
		{
			throw new Exception("COrders::addBundle called with invalid bundle id");
		}

		$Bundle = DAO_CFactory::create('bundle');
		$Bundle->id = $bundle_id;

		if (!$Bundle->find(true))
		{
			throw new Exception("COrders::addBundle called with invalid bundle id");
		}

		// Bundle Price Intercept Point

		if ($newCart)
		{
			$this->addBundleV2($Bundle, $selectedItems);
		}
		else
		{
			$this->addBundle($Bundle, $selectedItems);
		}
	}

	// given a bundle this adds creates the bundle object
	// Currently we call setupDefaultItems which returns all items as chosen
	// If we support choice (EG, 6 of 8) then an alternate method will be needed
	// that controls which items are chosen.
	function addBundleV2($Bundle, $selectedItems = false)
	{
		if ($Bundle == null)
		{
			$this->bundle = null;
			$this->bundle_id = null;
			$this->bundle_discount = 0;

			return;
		}

		// TODO : for now just clear all items but
		// we need to merge the new bundle items with any non-bundle items while removing old bundle items
		// Note: this is safe since the back end where items other than the bundle can exist the order is always rebuilt from scratch
		// and the bundle is added first
		// In the front end the bundle is the only "item"
		unset($this->items);

		$Bundle->setupDefaultItems();

		$this->bundle = $Bundle;
		$this->bundle_id = $Bundle->id;

		$menu_item_ids = array();
		foreach ($Bundle->items as $id => $bitem)
		{
			if ($selectedItems)
			{
				if (array_key_exists($id, $selectedItems))
				{
					$menu_item_ids[] = $id;
					$Bundle->items[$id]['chosen'] = true;
				}
				else
				{
					$Bundle->items[$id]['chosen'] = false;
				}
			}
			else
			{
				$menu_item_ids[] = $id;
			}
		}

		$getStoreMenu = CMenu::storeSpecificMenuExists($this->menu_id, $this->store_id);

		$inItemList = implode(",", $menu_item_ids);

		$DAO_Menu = DAO_CFactory::create('menu');
		$DAO_Menu->id = $Bundle->menu_id;

		if ($getStoreMenu)
		{
			$DAO_menu_item = $DAO_Menu->getMenuItemDAO('FeaturedFirst', $this->store_id, false, false, false, false, false, false, $inItemList); //returns the associated menu item query
		}
		else
		{
			$DAO_menu_item = $DAO_Menu->getMenuItemDAO('FeaturedFirst', false, false, false, false, false, false, false, $inItemList); //returns the associated menu item query
		}

		while ($DAO_menu_item->fetch())
		{
			$this->addMenuItem(clone($DAO_menu_item), $selectedItems[$DAO_menu_item->id]);
		}
	}

	function addTasteBundle($Bundle, $selectedItems = false, $removeOnlyExistingBundleItems = false)
	{
		if ($Bundle == null)
		{
			$this->bundle = null;
			$this->bundle_id = null;
			$this->bundle_discount = 0;

			return;
		}

		if ($removeOnlyExistingBundleItems)
		{
			if (!empty($this->items))
			{
				foreach ($this->items as $id => $data)
				{
					list($qty, $miObj) = $data;

					if (!empty($miObj->bundle_id) && $miObj->bundle_id == $Bundle->id)
					{
						unset($this->items[$id]);
					}
				}
			}
		}
		else
		{
			unset($this->items);
		}

		$Bundle->setupDefaultItems();

		$this->bundle = $Bundle;
		$this->bundle_id = $Bundle->id;

		$menu_item_ids = array();
		foreach ($Bundle->items as $id => $bitem)
		{
			if ($selectedItems)
			{
				if (array_key_exists($id, $selectedItems))
				{
					$menu_item_ids[] = $id;
					$Bundle->items[$id]['chosen'] = true;
					$Bundle->items[$id]['qty'] = $selectedItems[$id];
				}
				else
				{
					$Bundle->items[$id]['chosen'] = false;
					$Bundle->items[$id]['qty'] = 0;
				}
			}
			else
			{
				$menu_item_ids[] = $id;
			}
		}

		$DAO_menu = DAO_CFactory::create('menu');
		$DAO_menu->id = $this->menu_id;

		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $this->store_id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false,
			'menu_item_id_list' => implode(",", $menu_item_ids)
		));

		while ($DAO_menu_item->fetch())
		{
			$this->addMenuItem(clone($DAO_menu_item), $selectedItems[$DAO_menu_item->id]);
		}
	}

	// given a bundle this adds attaches the bundle object to the Order
	// and adds the chosen items to the items array

	function addBundle($Bundle, $selectedItems = false, $removeOnlyExistingBundleItems = false)
	{
		if ($Bundle == null)
		{
			$this->bundle = null;
			$this->bundle_id = null;
			$this->bundle_discount = 0;

			return;
		}

		// TODO : for now just clear all items but
		// we need to merge the new bundle items with any non-bundle items while removing old bundle items
		// Note: this is safe since the back end where items other than the bundle can exist the order is always rebuilt from scratch
		// and the bundle is added first
		// In the front end the bundle is the only "item"

		if ($removeOnlyExistingBundleItems && !empty($this->items))
		{
			foreach ($this->items as $id => $data)
			{
				list($qty, $miObj) = $data;

				if (!empty($miObj->bundle_id) && $miObj->bundle_id == $Bundle->id)
				{
					if ($qty == 1)
					{
						unset($this->items[$id]);
					}
					else
					{
						$this->items[$id][0] -= 1;
					}
				}
			}
		}
		else
		{
			unset($this->items);
		}

		$Bundle->setupDefaultItems();

		$this->bundle = $Bundle;
		$this->bundle_id = $Bundle->id;

		if (!empty($selectedItems))
		{
			$menu_item_ids = array();

			foreach ($Bundle->items as $id => $bitem)
			{
				if ($selectedItems)
				{
					if (in_array($id, $selectedItems))
					{
						$menu_item_ids[] = $id;
						$Bundle->items[$id]['chosen'] = true;
					}
					else
					{
						$Bundle->items[$id]['chosen'] = false;
					}
				}
				else
				{
					$menu_item_ids[] = $id;
				}
			}

			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->id = $Bundle->menu_id;

			$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
				'menu_to_menu_item_store_id' => $this->store_id,
				'exclude_menu_item_category_core' => false,
				'exclude_menu_item_category_efl' => false,
				'exclude_menu_item_category_sides_sweets' => false,
				'menu_item_id_list' => implode(",", $menu_item_ids)
			));

			while ($DAO_menu_item->fetch())
			{
				$this->addMenuItem(clone($DAO_menu_item), 1);
			}
		}
	}

	function applyBundleDiscount()
	{
		$this->bundle_discount = 0;

		if (empty($this->bundle->items))
		{
			return;
		}

		$sumStorePrice = 0;
		foreach ($this->bundle->items as $id => $bitem)
		{
			if ($bitem['chosen'])
			{
				$thisItem = $this->getMenuItem($id);

				if ($this->bundle->bundle_type == 'TV_OFFER')
				{
					$thisItemQty = 1;
				}
				else
				{
					$thisItemQty = $this->getMenuItemQty($id);
				}

				CLog::Assert(isset($thisItem), "Bundle Item $id not found in COrder::items");

				$sumStorePrice += $thisItem->store_price * $thisItemQty;

				if ($this->bundle->bundle_type == 'TV_OFFER')
				{
					$sumStorePrice -= $thisItem->ltd_menu_item_value;
				}
			}
		}

		$this->bundle_discount = $sumStorePrice - $this->bundle->price;
	}

	function getGiftCards()
	{

		$giftcard = false;
		if (!isset($this->products))
		{
			return false;
		}

		foreach ($this->products as $product)
		{
			//id=10 is gift card product, make a constant

			if ($product[1]->id == self::GIFT_CARD_PRODUCT_ID)
			{
				$giftcard = true;
			}
		}

		return $giftcard;
	}

	function isOrderEmpty()
	{

		if ($this->items && count($this->items))
		{
			return false;
		}

		if ($this->products || count($this->products))
		{
			return false;
		}

		return true;
	}

	function setMenuId($inMenuID)
	{
		$this->menu_id = $inMenuID;
	}

	function getMenuId()
	{
		return $this->menu_id;
	}

	// clears Both menu items (items) and non-food items (products)
	function clearItems()
	{
		if ($this->id)
		{
			throw new Exception('order already saved.');
		}

		$this->items = null;
		$this->products = null;
	}

	// clears Both menu items (items) and non-food items (products)
	function clearMenuItems()
	{
		if ($this->id)
		{
			throw new Exception('order already saved.');
		}

		$this->items = null;
	}

	function clearItemsUnsafe()
	{
		$this->items = null;
		$this->products = null;
	}

	function clearObserveOnlyProduct()
	{

		if (!empty($this->products))
		{
			foreach ($this->products as $id => $thisProduct)
			{
				if ($id == TODD_FREE_ATTENDANCE_PRODUCT_ID)
				{
					unset($this->products[$id]);
				}
			}
		}
	}

	function getMenuItemQty($menu_item_id)
	{
		if (!$this->items)
		{
			return 0;
		}

		if (array_key_exists($menu_item_id, $this->items))
		{
			return $this->items[$menu_item_id][0];
		}

		return 0;
	}

	function updateBundleItem($id, $parentID, $childQty)
	{
		if (isset($this->items[$id]))
		{
			$this->items[$id][1]->parentItemId = $parentID;
			$this->items[$id][1]->bundleItemCount = $childQty;
		}
	}

	function getProductQty($product_id)
	{
		if (!$this->products)
		{
			return 0;
		}

		if (array_key_exists($product_id, $this->products))
		{
			return $this->items[$product_id][0];
		}

		return 0;
	}

	function getMenuItem($menu_item_id)
	{
		if (!$this->items)
		{
			return null;
		}

		if (array_key_exists($menu_item_id, $this->items))
		{
			return $this->items[$menu_item_id][1];
		}

		return null;
	}

	function getProduct($product_id)
	{
		if (!$this->products)
		{
			return null;
		}

		if (array_key_exists($product_id, $this->products))
		{
			return $this->products[$product_id][1];
		}

		return null;
	}

	function hasStandardCoreServingMinimum($CartObj = null, $orderMinimum = null)
	{
		$minimumType = null;
		$minimumQty = null;
		if (!is_null($orderMinimum))
		{
			$minimumType = $orderMinimum->getMinimumType();
			$minimumQty = $orderMinimum->getMinimum();

			if (!$orderMinimum->isMinimumApplicable())
			{
				return true;
			}
		}

		if ($this->items)
		{
			$coreServingsTotal = 0;
			$coreItemsQuantityTotal = 0;
			foreach ($this->items as $id => $itemInfo)
			{
				if ($itemInfo[1]->menu_item_category_id < 4 || ($itemInfo[1]->menu_item_category_id == 4 && $itemInfo[1]->is_store_special == 0))
				{
					$coreItemsQuantityTotal += ($itemInfo[1]->item_count_per_item * $itemInfo[0]);
					$coreServingsTotal += ($itemInfo[1]->servings_per_item * $itemInfo[0]);
				}
			}

			if (is_null($orderMinimum))
			{
				if ($coreServingsTotal >= 36)
				{
					return true;
				}
			}
			else if ($minimumType == COrderMinimum::SERVING && $coreServingsTotal >= $minimumQty)
			{
				return true;
			}
			else if ($minimumType == COrderMinimum::ITEM && $coreItemsQuantityTotal >= $minimumQty)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function rebuildCartItemsToMatchOrder($CartObj, $menu_id, $order_type)
	{

		$CartObj->clearMenuItems(false, true);

		$doIntro = false;
		if ($order_type == CSession::INTRO)
		{
			$doIntro = true;
		}

		$doDreamTasteEvent = false;
		if ($order_type == CSession::FUNDRAISER)
		{
			$doDreamTasteEvent = true;
		}

		foreach ($this->items as $thisItem)
		{
			$CartObj->updateMenuItem($thisItem[1]->id, $thisItem[0], $menu_id, true, $doIntro, false, $doDreamTasteEvent, true);
		}
	}

	function reconcileOrderToCurrentInventory(&$menuItemInfo)
	{
		$didAdjust = false;

		if ($this->items)
		{
			$entreeIDToServingsMap = array();
			$fullSizeEntrees = array();
			$halfSizeEntrees = array();

			// get the totals needed by entree Id
			foreach ($this->items as $id => $itemInfo)
			{
				if (!isset($entreeIDToServingsMap[$itemInfo[1]->entree_id]))
				{
					$entreeIDToServingsMap[$itemInfo[1]->entree_id] = $itemInfo[1]->servings_per_item * $itemInfo[0];
				}
				else
				{
					$entreeIDToServingsMap[$itemInfo[1]->entree_id] += $itemInfo[1]->servings_per_item * $itemInfo[0];
				}

				if ($itemInfo[1]->pricing_type == CMenuItem::FULL)
				{
					$fullSizeEntrees[$itemInfo[1]->entree_id] = $id;
				}
				else
				{
					$halfSizeEntrees[$itemInfo[1]->entree_id] = $id;
				}
			}

			foreach ($entreeIDToServingsMap as $entree_id => $neededServings)
			{
				if (!empty($this->coupon->menu_item_id) && $this->coupon->menu_item_id == $entree_id)
				{
					continue;
				}

				$remianingServings = $menuItemInfo[$entree_id][$entree_id]->remaining_servings;

				if (empty($remianingServings))
				{
					$rs = $menuItemInfo[$entree_id];
					if (count($rs) == 1)
					{
						$remianingServings = array_slice($rs, 0, 1);
						$remianingServings = $remianingServings[0]->remaining_servings;
					}
				}

				if ($remianingServings < $neededServings)
				{
					$didAdjust = true;
					$fullSizeServings = 0;
					$halfSizeServings = 0;

					if (isset($fullSizeEntrees[$entree_id]))
					{
						$fullSizeServings = $this->items[$fullSizeEntrees[$entree_id]][0] * $this->items[$fullSizeEntrees[$entree_id]][1]->servings_per_item;
					}

					if (isset($halfSizeServings[$entree_id]))
					{
						$halfSizeServings = $this->items[$halfSizeEntrees[$entree_id]][0] * $this->items[$halfSizeEntrees[$entree_id]][1]->servings_per_item;
					}

					$gap = $neededServings - $remianingServings;

					if ($fullSizeServings > $halfSizeServings)
					{
						// more full entree servings so remove from them first
						$firstEntreeType = $fullSizeEntrees;
						$secondEntreeType = $halfSizeEntrees;
						$firstServingsCount = $fullSizeServings;
						$secondServingsCount = $halfSizeServings;
					}
					else
					{
						$firstEntreeType = $halfSizeEntrees;
						$secondEntreeType = $fullSizeEntrees;
						$firstServingsCount = $halfSizeServings;
						$secondServingsCount = $fullSizeServings;
					}

					$gapSizeInEntrees = $gap / $this->items[$firstEntreeType[$entree_id]][1]->servings_per_item;
					$partialAmount = $gapSizeInEntrees - floor($gapSizeInEntrees);

					if ($partialAmount > 0)
					{
						$gapSizeInEntrees = floor($gapSizeInEntrees) + 1;
					}

					if ($this->items[$firstEntreeType[$entree_id]][0] >= $gapSizeInEntrees)
					{
						// we can resolve the shortage by removing from the first type only
						$this->items[$firstEntreeType[$entree_id]][0] -= $gapSizeInEntrees; // <------
						if (isset($this->items[$firstEntreeType[$entree_id]][1]->parentItemId))
						{
							if ($this->items[$firstEntreeType[$entree_id]][1]->bundleChildCount > $this->items[$firstEntreeType[$entree_id]][0])
							{
								$this->items[$firstEntreeType[$entree_id]][1]->bundleChildCount = $this->items[$firstEntreeType[$entree_id]][0];
							}
						}

						$menuItemInfo[$entree_id][$firstEntreeType[$entree_id]]->qty_in_cart = $this->items[$firstEntreeType[$entree_id]][0];

						if ($this->items[$firstEntreeType[$entree_id]][0] == 0)
						{
							unset($this->items[$firstEntreeType[$entree_id]]);
						}
					}
					else
					{
						// otherwise remove them all and check for the second type
						$remaining = $gap - $firstServingsCount;
						unset($this->items[$firstEntreeType[$entree_id]]); // <---------
						$menuItemInfo[$entree_id][$firstEntreeType[$entree_id]]->qty_in_cart = 0;

						if ($secondServingsCount > 0)
						{
							$gapSizeInEntrees = $remaining / $this->items[$secondEntreeType[$entree_id]][1]->servings_per_item;
							$partialAmount = $gapSizeInEntrees - floor($gapSizeInEntrees);

							if ($partialAmount > 0)
							{
								$gapSizeInEntrees = floor($gapSizeInEntrees) + 1;
							}

							if ($this->items[$secondEntreeType[$entree_id]][0] >= $gapSizeInEntrees)
							{
								// we can resolve the shortage by removing halfsize entrees
								$this->items[$secondEntreeType[$entree_id]][0] -= $gapSizeInEntrees; // <-----------------

								if (isset($this->items[$secondEntreeType[$entree_id]][1]->parentItemId))
								{
									if ($this->items[$secondEntreeType[$entree_id]][1]->bundleChildCount > $this->items[$secondEntreeType[$entree_id]][0])
									{
										$this->items[$secondEntreeType[$entree_id]][1]->bundleChildCount = $this->items[$secondEntreeType[$entree_id]][0];
									}
								}

								$menuItemInfo[$entree_id][$secondEntreeType[$entree_id]]->qty_in_cart = $this->items[$secondEntreeType[$entree_id]][0];
							}
							else
							{
								unset($this->items[$secondEntreeType[$entree_id]]); // <-----------
								$menuItemInfo[$entree_id][$secondEntreeType[$entree_id]]->qty_in_cart = 0;
							}
						}
					}
				}
			}
		}

		return $didAdjust;
	}

	/**
	 * Get users past orders since cutoff date
	 *
	 * @param object $User
	 * @param string $since      : Cut off date
	 * @param int    $limitQuery : Limit how many resuts to return
	 *
	 * @return array
	 */
	static function getUsersOrders($User, $limitQuery = false, $since = false, $menu_id = false, $since_ordered = false, $type_of_order_array = false, $ordering_direction = 'asc', $timeSpanCap = false, $active_only = false, $verify_freezer_inventory = false)
	{

		if ($since)
		{
			$since = "AND s.session_start > '" . $since . "'";
		}
		else
		{
			$since = "";
		}

		if ($timeSpanCap)
		{
			$since .= "AND s.session_start < '" . $timeSpanCap . "'";
		}

		if ($since_ordered)
		{
			$since = "AND o.timestamp_created > '" . $since_ordered . "'";
		}

		$order_type = '';
		if (!empty($type_of_order_array))
		{
			$order_types = "'" . implode("','", $type_of_order_array) . "'";

			$order_type = " AND o.type_of_order IN(" . $order_types . ")";
		}

		if ($menu_id)
		{
			$menu_id = "AND s.menu_id = '" . $menu_id . "'";
		}

		if ($ordering_direction == 'desc')
		{
			$order_clause = " ORDER BY s.session_start DESC ";
		}
		else
		{
			$order_clause = " ORDER BY s.session_start asc";
		}

		if ($limitQuery)
		{
			$limitQuery = " LIMIT " . $limitQuery;
		}

		$booking_status_query = "b.status = 'ACTIVE' OR b.status = 'CANCELLED'";
		if ($active_only)
		{
			$booking_status_query = "b.status = 'ACTIVE'";
		}

		$Order = DAO_CFactory::create('orders');
		$query = "SELECT
				b.id AS booking_id,
				b.user_id,
				b.booking_type,
				b.status,
				b.order_id,
				o.id,
				o.my_meals_rating_user_id,
				o.order_confirmation,
				o.is_TODD,
				o.servings_total_count,
				o.session_discount_id,
				o.grand_total,
				o.menu_program_id,
				o.timestamp_created,
				o.store_id,
       			oa.is_gift,
				s.id AS 'session_id',
				s.session_start,
				s.duration_minutes,
				s.session_type,
       			s.session_type_subtype,
				s.menu_id AS menuid,
       			dtet.fadmin_acronym,
       			sp.store_pickup_location_id,
				st.store_name,
				st.city,
				st.state_id,
				st.postal_code,
				st.address_line1,
				st.address_line2,
				st.timezone_id,
                st.email_address,
                st.telephone_day,
				o.coupon_code_id,
				cc.coupon_code_title,
				cc.coupon_code,
       			os.tracking_number
				FROM orders o
				INNER JOIN booking b ON b.order_id = o.id AND b.is_deleted = '0'
				INNER JOIN store st ON st.id = o.store_id AND st.is_deleted = '0'
				LEFT JOIN session s ON b.session_id = s.id
				LEFT JOIN session_properties sp ON s.id = sp.session_id
				LEFT JOIN dream_taste_event_properties dtep ON dtep.id = sp.dream_taste_event_id
				LEFT JOIN dream_taste_event_theme dtet ON dtet.id = dtep.dream_taste_event_theme    
				LEFT JOIN orders_address oa ON oa.order_id = o.id AND oa.is_deleted = '0'
				LEFT JOIN orders_shipping os ON os.order_id = o.id AND os.is_deleted = '0'
				LEFT JOIN coupon_code AS cc ON cc.id = o.coupon_code_id AND cc.is_deleted = '0'
				WHERE (" . $booking_status_query . ")
				AND o.user_id = '" . $User->id . "'
				" . $since . "
				" . $menu_id . "
				" . $order_type . "
				AND o.is_deleted = 0 " . $order_clause . $limitQuery;

		$Order->query($query);

		$ordersArray = array();

		while ($Order->fetch())
		{
			$ordersArray[$Order->id] = $Order->toArray();
			$ordersArray[$Order->id]['isDFL'] = ($Order->menu_program_id > 1);
			$ordersArray[$Order->id]['menu_id'] = $Order->menuid;
			$ordersArray[$Order->id]['reschedulable'] = false;
			$ordersArray[$Order->id]['has_freezer_inventory'] = false;
			$ordersArray[$Order->id]['can_rate_my_meals'] = true;
			$ordersArray[$Order->id]['future_session'] = false;
			$ordersArray[$Order->id]['is_special_session'] = false;
			$ordersArray[$Order->id]['is_todd'] = false;
			$ordersArray[$Order->id]['session_end'] = date("Y-m-d H:i:s", strtotime($Order->session_start) + $Order->duration_minutes * 60);
			$ordersArray[$Order->id]['email_address'] = $Order->email_address;
			$ordersArray[$Order->id]['telephone_day'] = $Order->telephone_day;
			$ordersArray[$Order->id]['fully_qualified_order_type'] = COrders::getFullyQualifiedOrderTypeFrom(false, $Order->session_type, $Order->session_type_subtype, $Order->booking_type, $Order->fadmin_acronym);

			if (($Order->session_type_subtype == CSession::REMOTE_PICKUP || $Order->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE) && !empty($Order->store_pickup_location_id))
			{
				$location = DAO_CFactory::create('store_pickup_location');
				$location->id = $Order->store_pickup_location_id;
				$location->find(true);
				$ordersArray[$Order->id]['session_remote_location'] = $location;
			}

			$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($Order->timezone_id);
			$thisMorning = mktime(0, 0, 0, date("n", $now), date("j", $now), date("Y", $now));
			$cutoff = $thisMorning + (86400 * 5);

			if ($Order->session_start && (strtotime($Order->session_start) > $now) && $Order->status == CBooking::ACTIVE)
			{
				$ordersArray[$Order->id]['future_session'] = true;

				if ($verify_freezer_inventory)
				{

					$store_id = $Order->store_id;
					$menu_id = $Order->menuid;

					$DAO_menu = DAO_CFactory::create('menu');
					$DAO_menu->id = $menu_id;
					$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
						'menu_to_menu_item_store_id' => $store_id,
						'exclude_menu_item_category_core' => true,
						'exclude_menu_item_category_efl' => false,
						'exclude_menu_item_is_bundle' => true,
						'exclude_menu_item_category_sides_sweets' => false,
						'join_bundle_to_menu_item_bundle_id' => false,
						'exclude_menu_item_no_inventory' => true,
						'orderBy' => 'Inventory'
					));

					while ($DAO_menu_item->fetch())
					{
						if ($DAO_menu_item->hasAvailableInventory() && ($DAO_menu_item->showOnOrderForm() || $DAO_menu_item->showOnPickSheet()))
						{
							$ordersArray[$Order->id]['has_freezer_inventory'] = true;
							break;
						}
					}
				}

				if (strtotime($Order->session_start) > $cutoff)
				{
					$ordersArray[$Order->id]['reschedulable'] = true;
				}
				else
				{
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Orders that are 5 days away or less cannot be rescheduled.";
				}

				if (empty($Order->my_meals_rating_user_id) || $Order->my_meals_rating_user_id != $Order->user_id)
				{
					$ordersArray[$Order->id]['can_rate_my_meals'] = false;
				}

				if (isset($Order->session_discount_id) && isset($ordersArray[$Order->id]['reschedulable']))
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Orders placed on a discounted session can only be rescheduled by the store manager. Please Contact the store to reschedule this order.";
				}

				if ($Order->session_type == CSession::SPECIAL_EVENT)
				{
					$ordersArray[$Order->id]['is_special_session'] = true;

					if ($Order->isDelivery())
					{
						$ordersArray[$Order->id]['reschedulable'] = false;
						$ordersArray[$Order->id]['cantRescheduleReason'] = "Delivery orders mus be rescheduled by the store.";
					}
				}
				else
				{
					$ordersArray[$Order->id]['is_special_session'] = false;
				}
				if ($ordersArray[$Order->id]['session_type'] == CSession::DREAM_TASTE)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Meal Prep Workshop orders cannot be rescheduled.";
				}

				if ($ordersArray[$Order->id]['session_type'] == CSession::FUNDRAISER)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Fundraiser orders cannot be rescheduled.";
				}

				if ($ordersArray[$Order->id]['session_type'] == CSession::DELIVERED)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Delivered orders cannot be rescheduled.";
				}

				if ($Order->session_type == CSession::TODD)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['is_todd'] = true;
				}
				else
				{
					$ordersArray[$Order->id]['is_todd'] = false;
				}
			}
			else
			{
				if ($Order->status == CBooking::ACTIVE)
				{
					$ordersArray[$Order->id]['status'] = 'COMPLETED';
				}
			}
		}

		return $ordersArray;
	}

	/**
	 * Get stores orders since cutoff date
	 *
	 * @param int    $storeId
	 * @param string $since      : Cut off date
	 * @param int    $limitQuery : Limit how many results to return
	 *
	 * @return array
	 */
	static function fetchOrdersForStore($storeId, $limitQuery = false, $since = false, $lastModified = false, $menu_id = false, $since_ordered = false, $type_of_order_array = false, $ordering_direction = 'desc', $timeSpanCap = false, $active_only = false)
	{

		if ($since)
		{
			$since = "AND s.session_start > '" . $since . "'";
		}
		else
		{
			$since = "";
		}

		if ($lastModified)
		{
			$lastModified = "AND o.timestamp_updated > '" . $lastModified . "'";
		}
		else
		{
			$lastModified = "";
		}

		if ($timeSpanCap)
		{
			$since .= "AND s.session_start < '" . $timeSpanCap . "'";
		}

		if ($since_ordered)
		{
			$since = "AND o.timestamp_created > '" . $since_ordered . "'";
		}

		$order_type = '';
		if (!empty($type_of_order_array))
		{
			$order_types = "'" . implode("','", $type_of_order_array) . "'";

			$order_type = " AND o.type_of_order IN(" . $order_types . ")";
		}

		if ($menu_id)
		{
			$menu_id = "AND s.menu_id = '" . $menu_id . "'";
		}

		if ($ordering_direction == 'desc')
		{
			$order_clause = " ORDER BY o.timestamp_created DESC ";
		}
		else
		{
			$order_clause = " ORDER BY o.timestamp_created asc";
		}

		if ($limitQuery)
		{
			$limitQuery = " LIMIT " . $limitQuery;
		}

		$booking_status_query = "b.status = 'ACTIVE' OR b.status = 'CANCELLED'";
		if ($active_only)
		{
			$booking_status_query = "b.status = 'ACTIVE'";
		}

		$Order = DAO_CFactory::create('orders');
		$query = "SELECT
				b.id AS booking_id,
				b.user_id,
				b.booking_type,
				b.status,
				b.order_id,
				o.id,
				o.my_meals_rating_user_id,
				o.order_confirmation,
				o.is_TODD,
				o.servings_total_count,
				o.session_discount_id,
				o.grand_total,
				o.menu_program_id,
				o.timestamp_created,
       			o.timestamp_updated,
				o.store_id,
       			oa.is_gift,
				s.id AS 'session_id',
				s.session_start,
				s.duration_minutes,
				s.session_type,
       			s.session_type_subtype,
				s.menu_id AS menuid,
       			dtet.fadmin_acronym,
       			sp.store_pickup_location_id,
				st.store_name,
				st.city,
				st.state_id,
				st.postal_code,
				st.address_line1,
				st.address_line2,
				st.timezone_id,
                st.email_address,
                st.telephone_day,
				o.coupon_code_id,
				cc.coupon_code_title,
				cc.coupon_code,
       			os.tracking_number,
       			u.firstname, u.lastname,
				u.primary_email
				FROM orders o
				INNER JOIN booking b ON b.order_id = o.id AND b.is_deleted = '0'
				INNER JOIN store st ON st.id = o.store_id AND st.is_deleted = '0'
				LEFT JOIN session s ON b.session_id = s.id
				LEFT JOIN session_properties sp ON s.id = sp.session_id
				LEFT JOIN user u ON u.id = o.user_id
				LEFT JOIN dream_taste_event_properties dtep ON dtep.id = sp.dream_taste_event_id
				LEFT JOIN dream_taste_event_theme dtet ON dtet.id = dtep.dream_taste_event_theme    
				LEFT JOIN orders_address oa ON oa.order_id = o.id AND oa.is_deleted = '0'
				LEFT JOIN orders_shipping os ON os.order_id = o.id AND os.is_deleted = '0'
				LEFT JOIN coupon_code AS cc ON cc.id = o.coupon_code_id AND cc.is_deleted = '0'
				WHERE (" . $booking_status_query . ")
				AND o.store_id = '" . $storeId . "'
				" . $since . "
				" . $menu_id . "
				" . $order_type . "
				" . $lastModified . "
				AND o.is_deleted = 0 " . $order_clause . $limitQuery;

		$Order->query($query);

		$ordersArray = array();

		while ($Order->fetch())
		{
			$ordersArray[$Order->id] = $Order->toArray();
			$ordersArray[$Order->id]['isDFL'] = ($Order->menu_program_id > 1);
			$ordersArray[$Order->id]['menu_id'] = $Order->menuid;
			$ordersArray[$Order->id]['reschedulable'] = false;
			$ordersArray[$Order->id]['can_rate_my_meals'] = true;
			$ordersArray[$Order->id]['future_session'] = false;
			$ordersArray[$Order->id]['is_special_session'] = false;
			$ordersArray[$Order->id]['is_todd'] = false;
			$ordersArray[$Order->id]['session_end'] = date("Y-m-d H:i:s", strtotime($Order->session_start) + $Order->duration_minutes * 60);
			$ordersArray[$Order->id]['email_address'] = $Order->email_address;
			$ordersArray[$Order->id]['telephone_day'] = $Order->telephone_day;
			$ordersArray[$Order->id]['fully_qualified_order_type'] = COrders::getFullyQualifiedOrderTypeFrom(false, $Order->session_type, $Order->session_type_subtype, $Order->booking_type, $Order->fadmin_acronym);
			$ordersArray[$Order->id]['guest_name'] = $Order->firstname . ' ' . $Order->lastname;
			$ordersArray[$Order->id]['guest_email'] = $Order->primary_email;
			$ordersArray[$Order->id]['timestamp_updated'] = $Order->timestamp_updated;

			if (($Order->session_type_subtype == CSession::REMOTE_PICKUP || $Order->session_type_subtype == CSession::REMOTE_PICKUP_PRIVATE) && !empty($Order->store_pickup_location_id))
			{
				$location = DAO_CFactory::create('store_pickup_location');
				$location->id = $Order->store_pickup_location_id;
				$location->find(true);
				$ordersArray[$Order->id]['session_remote_location'] = $location;
			}

			$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($Order->timezone_id);
			$thisMorning = mktime(0, 0, 0, date("n", $now), date("j", $now), date("Y", $now));
			$cutoff = $thisMorning + (86400 * 5);

			if ($Order->session_start && (strtotime($Order->session_start) > $now) && $Order->status == CBooking::ACTIVE)
			{
				$ordersArray[$Order->id]['future_session'] = true;

				if (strtotime($Order->session_start) > $cutoff)
				{
					$ordersArray[$Order->id]['reschedulable'] = true;
				}
				else
				{
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Orders that are 5 days away or less cannot be rescheduled.";
				}

				if (empty($Order->my_meals_rating_user_id) || $Order->my_meals_rating_user_id != $Order->user_id)
				{
					$ordersArray[$Order->id]['can_rate_my_meals'] = false;
				}

				if (isset($Order->session_discount_id) && isset($ordersArray[$Order->id]['reschedulable']))
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Orders placed on a discounted session can only be rescheduled by the store manager. Please Contact the store to reschedule this order.";
				}

				if ($Order->session_type == CSession::SPECIAL_EVENT)
				{
					$ordersArray[$Order->id]['is_special_session'] = true;

					if (isset($Order->session_type_subtype) && $Order->session_type_subtype == CSession::DELIVERY)
					{
						$ordersArray[$Order->id]['reschedulable'] = false;
						$ordersArray[$Order->id]['cantRescheduleReason'] = "Delivery orders mus be rescheduled by the store.";
					}
				}
				else
				{
					$ordersArray[$Order->id]['is_special_session'] = false;
				}
				if ($ordersArray[$Order->id]['session_type'] == CSession::DREAM_TASTE)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Meal Prep Workshop orders cannot be rescheduled.";
				}

				if ($ordersArray[$Order->id]['session_type'] == CSession::FUNDRAISER)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Fundraiser orders cannot be rescheduled.";
				}

				if ($ordersArray[$Order->id]['session_type'] == CSession::DELIVERED)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['cantRescheduleReason'] = "Delivered orders cannot be rescheduled.";
				}

				if ($Order->session_type == CSession::TODD)
				{
					$ordersArray[$Order->id]['reschedulable'] = false;
					$ordersArray[$Order->id]['is_todd'] = true;
				}
				else
				{
					$ordersArray[$Order->id]['is_todd'] = false;
				}
			}
			else
			{
				if ($Order->status == CBooking::ACTIVE)
				{
					$ordersArray[$Order->id]['status'] = 'COMPLETED';
				}
			}
		}

		return $ordersArray;
	}

	/**
	 * Returns array of menu items for orders in array and an array of recipes
	 *
	 * @param array  $ordersArray : array of orders
	 * @param string $foodSearch  : search term, ie chicken
	 * @param string $since       : limit query by time
	 *
	 * @return array: array ( array, array )
	 */
	static function getMenuItemsForOrder($ordersArray, $foodSearch = false, $since = false)
	{
		$recipesListed = array();
		$orders = array();

		foreach ($ordersArray as $orderDetails)
		{
			if ($since && $orderDetails['session_start'] < $since)
			{
				continue;
			}

			$DAO_menu = DAO_CFactory::create('menu');
			$DAO_menu->id = $orderDetails['menu_id'];
			$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
				'menu_to_menu_item_store_id' => $orderDetails['store_id'],
				'join_order_item_order_id' => array($orderDetails['order_id']),
				'join_order_item_order' => 'INNER',
				'menu_item_word_search' => $foodSearch,
				'exclude_menu_item_category_core' => false,
				'exclude_menu_item_category_efl' => false,
				'exclude_menu_item_category_sides_sweets' => false,
				'groupBy' => 'order_item.id'
			));

			while ($DAO_menu_item->fetch())
			{
				$orders[$DAO_menu_item->DAO_order_item->order_id]['recipes'][$DAO_menu_item->element_id]['element_id'] = $DAO_menu_item->element_id;
				$orders[$DAO_menu_item->DAO_order_item->order_id]['recipes'][$DAO_menu_item->element_id]['recipe_id'] = $DAO_menu_item->recipe_id;
				$orders[$DAO_menu_item->DAO_order_item->order_id]['recipes'][$DAO_menu_item->element_id]['recipe_version'] = $DAO_menu_item->recipe_version;
				$orders[$DAO_menu_item->DAO_order_item->order_id]['recipes'][$DAO_menu_item->element_id]['menu_item_name'] = $DAO_menu_item->menu_item_name;
				$orders[$DAO_menu_item->DAO_order_item->order_id]['recipes'][$DAO_menu_item->element_id]['menu_item_description'] = $DAO_menu_item->menu_item_description;
				$orders[$DAO_menu_item->DAO_order_item->order_id]['recipes'][$DAO_menu_item->element_id]['store_id'] = $DAO_menu_item->store_id;

				if (!in_array($DAO_menu_item->recipe_id, $recipesListed))
				{
					$recipesListed[] = $DAO_menu_item->recipe_id;
				}
			}
		}

		return array(
			$orders,
			$recipesListed
		);
	}

	function getUnderStockedItems()
	{
		if (!$this->items)
		{
			return false;
		}

		if (empty($this->items))
		{
			return false;
		}

		if (empty($this->store_id))
		{
			return false;
		}

		$menu_id = $this->session->menu_id;
		if (empty($menu_id))
		{
			return false;
		}

		$removalList = array();

		if (empty($this->itemsAdjustedByInventoryArray))
		{
			return false;
		}

		foreach ($this->items as $id => $thisItem)
		{
			if (array_key_exists($thisItem[1]->recipe_id, $this->itemsAdjustedByInventoryArray))
			{
				$removalList[$id] = $id;
			}
		}

		return $removalList;
	}

	function getHiddenItems()
	{

		if (!$this->items)
		{
			return false;
		}

		if (empty($this->items))
		{
			return false;
		}

		if (empty($this->store_id))
		{
			return false;
		}

		$menu_id = $this->session->menu_id;
		if (empty($menu_id))
		{
			return false;
		}

		$removalList = array();

		foreach ($this->items as $id => $thisItem)
		{
			// do not remove hidden bundle items or coupon item
			if (empty($thisItem[1]->parentItemId) && (empty($this->coupon->menu_item_id) && $this->coupon->menu_item_id != $id))
			{
				$hiddenTest = DAO_CFactory::create('menu_item');
				$hiddenTest->query("select mi.menu_item_name from menu_to_menu_item mmi
					join menu_item mi on mmi.menu_item_id = mi.id
					where mmi.menu_id = $menu_id
					and mmi.store_id = {$this->store_id} and mmi.menu_item_id = $id and (mmi.is_visible = 0 or mmi.is_deleted = 1)");

				if ($hiddenTest->N != 0)
				{
					$hiddenTest->fetch();
					$removalList[$id] = $hiddenTest->menu_item_name;
				}
			}
		}

		return $removalList;
	}

	function verifyAdequateInventory()
	{
		$this->itemsAdjustedByInventory = null;

		if (!$this->items)
		{
			return true;
		}

		if (empty($this->items))
		{
			return true;
		}

		if (empty($this->store_id))
		{
			return true;
		}

		$menu_id = $this->session->menu_id;
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

		foreach ($this->items as $id => $thisItem)
		{
			$tempArray[] = $id;

			if (!isset($serving_totals[$thisItem[1]->recipe_id]))
			{
				$serving_totals[$thisItem[1]->recipe_id] = 0;
			}
			$serving_totals[$thisItem[1]->recipe_id] += ($thisItem[0] * $thisItem[1]->servings_per_item);
		}

		$inv_array = array();

		// INVENTORY TOUCH POINT 9
		$DAO_menu_item_inventory = DAO_CFactory::create('menu_item_inventory');
		$DAO_menu_item_inventory->menu_id = $menu_id;
		$DAO_menu_item_inventory->store_id = $this->store_id;
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

	function getInvExceptionItemsString()
	{
		if (empty($this->itemsAdjustedByInventory))
		{
			return "";
		}

		$retVal = "";

		$tmpStr = substr($this->itemsAdjustedByInventory, 0, strlen($this->itemsAdjustedByInventory) - 1);

		$Menu_items = DAO_CFactory::create('menu_item', true);

		$Menu_items->query("select mi2.menu_item_name from (
		    SELECT max(mi.id) as most_recent_version FROM menu_item mi WHERE mi.recipe_id IN (" . $tmpStr . ")
		    group by mi.recipe_id) as iq
		    join menu_item mi2 on mi2.id = iq.most_recent_version");

		while ($Menu_items->fetch())
		{
			$retVal .= $Menu_items->menu_item_name . "<br />";
		}

		return $retVal;
	}

	function hasSubscriptionProduct()
	{

		if (!$this->products)
		{
			return false;
		}

		foreach ($this->products as $id => $thisProduct)
		{
			if ($thisProduct[1]->item_type == 'ENROLLMENT')
			{
				return true;
			}
		}

		return false;
	}

	//
	function clearSubscriptionsFromProductsList()
	{
		if ($this->products)
		{
			foreach ($this->products as $id => $thisProduct)
			{

				if ($thisProduct[1]->item_type == 'ENROLLMENT')
				{
					unset($this->products[$id]);
				}
			}
		}
	}

	function isObserveOnly()
	{
		if ($this->items && count($this->items) > 0)
		{
			return false;
		}

		if (!$this->products)
		{
			return false;
		}

		if (!$this->is_TODD)
		{
			return false;
		}

		if (array_key_exists(self::TODD_FREE_ATTENDANCE_PRODUCT_ID, $this->products))
		{
			return true;
		}

		return false;
	}

	function getSessionType()
	{
		$sessionType = CSession::STANDARD;

		if ($this->isNewIntroOffer())
		{
			$sessionType = CSession::INTRO;
		}
		else if ($this->isMadeForYou())
		{
			$sessionType = CSession::MADE_FOR_YOU;
		}
		else if ($this->isDreamTaste())
		{
			$sessionType = CSession::DREAM_TASTE;
		}
		else if ($this->isFundraiser())
		{
			$sessionType = CSession::FUNDRAISER;
		}
		else if ($this->isDelivery())
		{
			$sessionType = CSession::DELIVERY;
		}
		else if ($this->isDelivered())
		{
			$sessionType = CSession::DELIVERED;
		}

		return $sessionType;
	}

	/**
	 * For unsaved orders
	 */
	function getSessionId()
	{
		if (is_object($this->session) && !empty($this->session->id))
		{
			return $this->session->id;
		}

		return false;
	}

	/**
	 * Use this instead of setting order_type directly
	 */
	function setOrderType($type = 'WEB', $applyPremium = true)
	{
		$this->order_type = $type;

		if ($this->order_type == self::DIRECT)
		{
			$this->applyPremium = $applyPremium;
		}
	}

	/**
	 * Use this to indicate if the recalculate function
	 * should update the customization fee.
	 */
	function setShouldRecalculateMealCustomizationFee($val)
	{
		$this->recalculateMealCustomizationFee = $val;
	}

	function shouldRecalculateMealCustomizationFee($val)
	{
		return $this->recalculateMealCustomizationFee;
	}

	function setAllowRecalculateMealCustomizationFeeClosedSession($val)
	{
		$this->allowClosedMealCustomizeSessionRecalculate = $val;
	}

	function allowRecalculateMealCustomizationFeeClosedSession()
	{
		return $this->allowClosedMealCustomizeSessionRecalculate;
	}

	/**
	 * Add a menu item to an order
	 */
	function addMenuItem($menu_item_obj, $quantity, $isPromoItem = false, $isCouponFreeMeal = false, $isDiscountedRecipe = false)
	{

		if (!$menu_item_obj->id)
		{
			return;
		}

		if ($quantity == 0)
		{
			return;
		}

		if (!isset($this->items))
		{
			$this->items = array();
		}

		if ($isPromoItem)
		{
			$menu_item_obj->isPromo = true;
		}

		if ($isCouponFreeMeal)
		{
			$menu_item_obj->isFreeMeal = true;
		}

		//keep em sorted
		if (array_key_exists($menu_item_obj->id, $this->items))
		{
			$currentQty = $this->items[$menu_item_obj->id][0];

			$isCurrentlyPromo = isset($this->items[$menu_item_obj->id][1]->isPromo) && $this->items[$menu_item_obj->id][1]->isPromo;
			$isCurrentlyCouponFreeMeal = isset($this->items[$menu_item_obj->id][1]->isFreeMeal) && $this->items[$menu_item_obj->id][1]->isFreeMeal;

			if (!empty($this->items[$menu_item_obj->id][1]->parentItemId))
			{
				$menu_item_obj->parentItemId = $this->items[$menu_item_obj->id][1]->parentItemId;
			}

			if (!empty($this->items[$menu_item_obj->id][1]->bundleItemCount))
			{
				$menu_item_obj->bundleItemCount = $this->items[$menu_item_obj->id][1]->bundleItemCount;
			}

			$this->items[$menu_item_obj->id] = array(
				$quantity + $currentQty,
				$menu_item_obj
			);

			if ($isCurrentlyPromo)
			{
				$this->items[$menu_item_obj->id][1]->isPromo = true;
			}

			if ($isCurrentlyCouponFreeMeal)
			{
				$this->items[$menu_item_obj->id][1]->isFreeMeal = true;
			}
		}
		else
		{
			$this->items[$menu_item_obj->id] = array(
				$quantity,
				$menu_item_obj
			);
		}
	}

	function addCouponMenuItem($menu_item_obj, $addQtyIfExists = false)
	{
		if (!$menu_item_obj->id)
		{
			return;
		}

		if (array_key_exists($menu_item_obj->id, $this->items))
		{
			$currentQty = $this->items[$menu_item_obj->id][0];

			if ($addQtyIfExists)
			{
				$totalQty = $currentQty + $addQtyIfExists;
			}
			else
			{
				$totalQty = $currentQty;
			}

			$this->items[$menu_item_obj->id] = array(
				$totalQty,
				$menu_item_obj
			);
		}
		else
		{
			$totalQty = 1;

			$this->items[$menu_item_obj->id] = array(
				$totalQty,
				$menu_item_obj
			);
		}

		return $totalQty;
	}

	/**
	 * Add a menu item to an order
	 */
	function addProduct($product_obj, $quantity)
	{

		if (!$product_obj->id)
		{
			return;
		}

		if ($quantity == 0)
		{
			return;
		}

		if (!$this->products)
		{
			$this->products = array();
		}

		//keep em sorted
		if (array_key_exists($product_obj->id, $this->products))
		{

			$currentQty = $this->products[$product_obj->id][0];

			$this->products[$product_obj->id] = array(
				$quantity + $currentQty,
				$product_obj
			);
		}
		else
		{
			$this->products[$product_obj->id] = array(
				$quantity,
				$product_obj
			);
		}
	}

	function addStore($store)
	{
		if (!$store)
		{
			return;
		}

		$this->store_id = $store->id;
		$this->store = $store;
	}

	/**
	 * Added ToddW 4-28-06 count menu items of a specific type
	 * Added CES 6-1-22 param $menu_item_category_type (false, 'entrees' or 'sides') used if $menu_item_pricing_type is false
	 */
	function countItems($menu_item_pricing_type = false, $menu_item_category = false, $exclude_pre_assembled = false)
	{
		$cnt = 0;

		$exclude_pre_assembled = ($exclude_pre_assembled === "0" ? false : $exclude_pre_assembled);
		if (!$this->items)
		{
			return 0;
		}

		if (!$menu_item_pricing_type)
		{
			if ($menu_item_category == 'sides')
			{
				foreach ($this->items as $itemStuff)
				{
					if ($itemStuff[1]->menu_item_category_id == 9) // 9 = sides
					{
						$cnt += $itemStuff[0];
					}
				}
			}
			else if ($menu_item_category == 'entrees')
			{
				foreach ($this->items as $itemStuff)
				{
					if ($itemStuff[1]->menu_item_category_id != 9) // 9 = sides
					{
						if ($exclude_pre_assembled && $itemStuff[1]->is_preassembled)
						{
							continue;
						}
						$cnt += ($itemStuff[0] * $itemStuff[1]->item_count_per_item);
					}
				}
			}
			else if ($menu_item_category == 'core_entrees')
			{
				foreach ($this->items as $itemStuff)
				{
					if ($itemStuff[1]->menu_item_category_id == 1 || ($itemStuff[1]->menu_item_category_id == 4 && $itemStuff[1]->is_store_special == 0)) // 1 = core entree
					{
						if ($exclude_pre_assembled && $itemStuff[1]->is_preassembled)
						{
							continue;
						}
						if ($itemStuff[1]->is_bundle && is_null($itemStuff[1]->parentItemId))
						{
							//if it is a bundle parent item, don't include
							continue;
						}
						$cnt += ($itemStuff[0] * $itemStuff[1]->item_count_per_item);
					}
				}
			}
			else
			{
				foreach ($this->items as $itemStuff)
				{
					if ($exclude_pre_assembled && $itemStuff[1]->is_preassembled)
					{
						continue;
					}
					$cnt += ($itemStuff[0] * $itemStuff[1]->item_count_per_item);
				}
			}

			return $cnt;
		}
		else
		{
			foreach ($this->items as $itemStuff)
			{
				if ($itemStuff[1]->pricing_type == $menu_item_pricing_type)
				{
					if ($exclude_pre_assembled && $itemStuff[1]->is_preassembled)
					{
						continue;
					}
					$cnt += ($itemStuff[0] * $itemStuff[1]->item_count_per_item);
				}
			}

			return $cnt;
		}
	}

	function countProducts()
	{
		$cnt = 0;

		if (!$this->products)
		{
			return 0;
		}

		foreach ($this->products as $itemStuff)
		{
			$cnt += $itemStuff[0];
		}

		return $cnt;
	}

	/**
	 * For Customer menu only - count servings that contribute to the volume reward
	 * and towards the 36 minimum for ordering
	 */

	function countContributingServings()
	{
		$cnt = 0;

		if (!$this->items)
		{
			return 0;
		}

		foreach ($this->items as $itemStuff)
		{
			if (!$itemStuff[1]->is_side_dish)
			{
				if (isset($itemStuff[1]->servings_per_item))
				{
					$cnt += ($itemStuff[1]->servings_per_item * $itemStuff[0]);
				}
				else
				{
					if ($itemStuff[1]->pricing_type == CMenuItem::FULL)
					{
						$cnt += (6 * $itemStuff[0]);
					}
					else
					{
						$cnt += (3 * $itemStuff[0]);
					}
				}
			}
		}

		return $cnt;
	}

	// Caution: This function is called to ensure the pricing used when the order was placed is displayed in
	// the CURRENT section of the comparison view of the Order Manager. Do not call this function unless you are sure you know what
	// you are doing.
	function setOverridePricingToSavedValues($orgPricing)
	{
		if (!empty($this->items) && is_array($this->items))
		{
			foreach ($this->items as $id => &$thisItem)
			{
				if (isset($orgPricing[$id]))
				{

					if (!empty($thisItem[1]->markdown_id) && is_numeric($thisItem[1]->markdown_id) && $thisItem[1]->markdown_id > 0)
					{
					}
					else if (isset($thisItem[1]->ltd_menu_item_value) && $thisItem[1]->ltd_menu_item_value > 0)
					{
						$thisItem[1]->override_price = $orgPricing[$id];
					}
					else
					{
						$thisItem[1]->override_price = $orgPricing[$id];

						// This is needed for cases where the original price is lower than the current base price.
						// This should not be accomodated but it has happened so we alter the base price.  Arggg!!
						if ($thisItem[1]->override_price < $thisItem[1]->price)
						{
							$thisItem[1]->price = $thisItem[1]->override_price;
						}
					}
				}
			}
		}
	}

	/**
	 * After the Edited order is submitted the order must be recalculated. Recalulation requires that
	 * any business objects needed have already been instantiated.
	 * Editing offers more choices such as ignoring the preferred customer discount so we cannot use
	 * Refresh to instantiate the object. Hence this function.
	 */
	function refreshForEditing($menu_id = false)
	{

		//add markup
		$Store = $this->getStore();
		$markup = null;
		if ($this->family_savings_discount_version == 2)
		{
			if (!empty($this->mark_up_multi_id))
			{
				$markup = DAO_CFactory::create('mark_up_multi');
				$markup->id = $this->mark_up_multi_id;
				if (!$markup->find_includeDeleted(true))
				{
					throw new Exception('markup not found when setting up order editor for order:' . $this->originalOrder->id);
				}
			}
		}
		else
		{
			if (!empty($this->markup_id))
			{
				$markup = DAO_CFactory::create('mark_up');
				$markup->id = $this->markup_id;
				if (!$markup->find_includeDeleted(true))
				{
					throw new Exception('markup not found when setting up order editor for order:' . $this->originalOrder->id);
				}
			}
		}
		$this->addMarkup($markup);

		//get tax
		$this->addSalesTax($Store->getCurrentSalesTaxObj());

		//get promo
		if (isset($this->promo_code_id) && $this->promo_code_id != 0)
		{
			$Promo = DAO_CFactory::create('promo_code');
			$Promo->id = $this->promo_code_id;
			$found = $Promo->find();
			if ($found)
			{
				$Promo->fetch();
				$this->addPromo($Promo);
			}
			else
			{
				throw new Exception("Promo not found in refreshForEditing()");
			}
		}

		//get coupon
		if (!empty($this->coupon_code_id) && is_numeric($this->coupon_code_id))
		{
			$couponCode = DAO_CFactory::create('coupon_code');
			$couponCode->id = $this->coupon_code_id;
			$found = $couponCode->find(true);
			if ($found)
			{
				if (!empty($couponCode->limit_to_mfy_fee))
				{
					$couponCode->discount_var = $this->subtotal_service_fee;
				}

				if (!empty($couponCode->limit_to_delivery_fee))
				{
					if ($couponCode->discount_method == "FLAT")
					{
						if ($couponCode->discount_var > $this->subtotal_delivery_fee)
						{
							$couponCode->discount_var = $this->subtotal_delivery_fee;
						}
					}
					//$couponCode->discount_var = $this->subtotal_delivery_fee;
				}

				if ($menu_id && $menu_id < 221)
				{

					if ($couponCode->coupon_code == 'GNOINTRO15')
					{
						$couponCode->discount_var = 84.95;
					}
				}

				// if this is a dream taste, the hostess discount is equal to the cost of the dream taste bundle
				if ($couponCode->coupon_code == 'HOSTESS' && $this->session->session_type == CSession::DREAM_TASTE)
				{
					$couponCode->discount_var = $this->bundle->price;
				}

				$this->addCoupon($couponCode);
			}
			else
			{
				throw new Exception("Coupon not found in refreshForEditing()");
			}
		}

		//get preferred customer
		if (isset($this->user_preferred_id) && $this->user_preferred_id != 0 && $this->user_preferred_id != "null")
		{
			$UP = DAO_CFactory::create('user_preferred');
			$UP->id = $this->user_preferred_id;
			$found = $UP->find_includeDeleted();
			if ($found)
			{
				$UP->fetch();
				$this->preferred = $UP;
			}
			else
			{
				throw new Exception("User Preferred not found in refreshForEditing()");
			}
		}
	}

	function getOriginalPrices()
	{
		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'join_order_item_order_id' => array($this->id),
			'join_order_item_order' => 'INNER',
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$retVal = array();

		while ($DAO_menu_item->fetch())
		{
			if (!empty($DAO_menu_item->DAO_order_item->discounted_subtotal) && empty($DAO_menu_item->DAO_order_item->menu_item_mark_down_id))
			{
				$retVal[$DAO_menu_item->DAO_order_item->menu_item_id] = $DAO_menu_item->DAO_order_item->discounted_subtotal / $DAO_menu_item->DAO_order_item->item_count;
			}
			else
			{
				$retVal[$DAO_menu_item->DAO_order_item->menu_item_id] = $DAO_menu_item->DAO_order_item->sub_total / $DAO_menu_item->DAO_order_item->item_count;
			}
		}

		return $retVal;
	}

	/**
	 * After calling unserialize or building a new order, use this function to (re)apply any price adjustments: tax,preferred,mark_up, etc.
	 * based on the reconstituted fields: $session, $store_id, etc.
	 * @return 'closed' if the session is now closed
	 */
	function refresh($customer, $menu_id = false)
	{

		// must have a menu id

		if (!$menu_id)
		{
			if ($this->session)
			{
				$menu_id = $this->session->menu_id;
				$this->menu_id = $menu_id;
			}
			else if (isset($this->menu_id))
			{
				$menu_id = $this->menu_id;
			}
		}

		if ($customer && $customer->id)
		{

			//if the user_id is set, make sure it matches the customer passed in
			if ($this->user_id && ($this->user_id !== $customer->id))
			{
				CCart2::instance()->emptyCart();
				CApp::instance()->template()->setStatusMsg('The current cart held items for another user. The cart has been emptied. Please start your order again.');
				CApp::bounce('/session-menu');
			}

			//check for preferred customer
			$UP = DAO_CFactory::create('user_preferred');
			$UP->user_id = $customer->id;
			$UP->findActive($this->store_id);

			if ($UP->N == 0)
			{
				$this->clearPreferred();
			}
			else
			{
				$this->addActivePreferred($UP);
			}
		}
		else
		{
			$this->clearPreferred();
		}

		// must have store

		$this->getStore();
		$Store = $this->store;

		if (!$Store || !$menu_id)
		{
			return false;
		}

		// temp fix ...remove all references to family_savings_discount_version for complete fix
		$this->family_savings_discount_version = 2;
		//add markup
		if ($this->family_savings_discount_version == 2)
		{
			$Markup = $Store->getMarkUpMultiObj($menu_id);
		}
		else
		{
			$Markup = $Store->getMarkUpObj($menu_id);
		}
		$this->addMarkup($Markup);

		// CES 03/28/08 may need to refresh the order prior to the existence of a session
		if (!empty($this->session))
		{
			//check for premium
			if (($this->session->isQuickSix() || $this->order_type == self::DIRECT) && $this->applyPremium)
			{
				$this->addPremium($Store->getPremium());
			}
		}

		//get promotion
		if (isset($this->promo_code_id) && $this->promo_code_id)
		{
			$Promo = DAO_CFactory::create('promo_code');
			$Promo->id = $this->promo_code_id;
			$found = $Promo->findActive();
			if ($found)
			{
				$Promo->fetch();
				$this->addPromo($Promo);
			}
		}

		//get coupon
		if (isset($this->coupon_code_id) && $this->coupon_code_id)
		{
			$couponCode = DAO_CFactory::create('coupon_code');
			$couponCode->id = $this->coupon_code_id;
			$found = $couponCode->find(true);

			if ($found)
			{
				$couponCode->calculate($this, $Markup);

				$this->addCoupon($couponCode);
			}
		}

		//get tax
		$this->addSalesTax($Store->getCurrentSalesTaxObj());

		//make sure it is still open ?

		if (!empty($this->session) && !$this->session->isOpen($Store))
		{
			return 'closed';
		}

		return true;
	}

	function applySessionDiscount($suppressSessionDiscount = false, $editing = false)
	{
		if ($suppressSessionDiscount)
		{
			$this->session_discount_total = 0;
			$this->session_discount_id = 'null';

			return;
		}

		if ($this->isIntroOrder())
		{
			return;
		}

		if (!empty($this->membership_id))
		{
			$this->session_discount_total = 0;
			$this->session_discount_id = 'null';

			return;
		}

		$session_discount = DAO_CFactory::create("session_discount");

		if ($editing)
		{
			$session_discount->id = $this->session_discount_id;
		}
		else
		{
			if (!empty($this->session->session_discount_id))
			{
				$session_discount->id = $this->session->session_discount_id;
			}
		}

		if (empty($session_discount->id))
		{
			$this->session_discount_total = 0;
			$this->session_discount_id = 'null';

			return;
		}

		$discount_amount = 0;

		// Don't use find as an edited order may be pointed at a 'deleted' (replaced) version. Find will not pick up a deleted discount
		$session_discount->query("select * from session_discount where id = " . $session_discount->id);
		if ($session_discount->fetch())
		{
			switch ($session_discount->discount_type)
			{
				case 'ITEM':
					//not supoported yet
					return;
				case 'FLAT':

					$discount_amount = $session_discount->discount_var;
					$this->subtotal_food_items_adjusted = $this->subtotal_food_items_adjusted - $discount_amount;

					break;
				case 'PERCENT':
					$discount_amount = self::std_round((($this->subtotal_food_items_adjusted + $this->misc_food_subtotal) * $session_discount->discount_var) / 100, 2);
					$this->subtotal_food_items_adjusted = $this->subtotal_food_items_adjusted - $discount_amount;

					if ($this->subtotal_food_items_adjusted < 0)
					{
						$this->subtotal_food_items_adjusted = 0;
					}
					break;
				default:
					return;
			}

			if ($this->subtotal_food_items_adjusted < 0)
			{
				$this->subtotal_food_items_adjusted = 0;
			}
			$this->session_discount_total = $discount_amount;
			$this->session_discount_id = $session_discount->id;
		}
	}

	function applyVolumeDiscount()
	{

		$NonPromoServings = $this->servings_total_count;

		if ($this->items)
		{
			foreach ($this->items as $itemArray)
			{
				$qty = $itemArray[0];
				$itemObj = $itemArray[1];

				if (isset($itemObj->parentItemId) && isset($this->items[$itemObj->parentItemId]))
				{
					//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
					$qty -= $itemObj->bundleItemCount;

					if ($qty <= 0)
					{
						continue;
					}
				}

				if (isset($itemObj->isPromo) && $itemObj->isPromo)
				{
					if ($itemObj->pricing_type == CMenuItem::HALF)
					{
						$NonPromoServings -= 3;
					}
					else if ($itemObj->pricing_type == CMenuItem::TWO)
					{
						$NonPromoServings -= 2;
					}
					else if ($itemObj->pricing_type == CMenuItem::FOUR)
					{
						$NonPromoServings -= 4;
					}
					else
					{
						$NonPromoServings -= 6;
					}
					break;
				}

				if (isset($itemObj->isFreeMeal) && $itemObj->isFreeMeal)
				{
					if ($itemObj->pricing_type == CMenuItem::HALF)
					{
						$NonPromoServings -= 3;
					}
					else if ($itemObj->pricing_type == CMenuItem::TWO)
					{
						$NonPromoServings -= 2;
					}
					else if ($itemObj->pricing_type == CMenuItem::FOUR)
					{
						$NonPromoServings -= 4;
					}
					else
					{
						$NonPromoServings -= 6;
					}
					break;
				}
			}
		}

		if ($this->family_savings_discount_version == 2 && $NonPromoServings >= 72)
		{

			$mightUseOldMethod = false;
			// if there is no session then the order is being calculated prior to session selection.
			// This only happens in recent menus. so the old method (which needed the menu id for determination)
			// can be ignored

			if (isset($this->findSession()->menu_id))
			{
				$mightUseOldMethod = true;
				$menu_id = $this->findSession()->menu_id;
			}
			else if (isset($this->menu_id))
			{
				$mightUseOldMethod = true;
				$menu_id = $this->menu_id;
			}

			if ($mightUseOldMethod && $menu_id <= 73) // september and earlier use the the discount tied tied to menu
			{
				$volume_discount = DAO_CFactory::create('volume_discount_type');
				$volume_discount->menu_id = $menu_id;

				// use first one found - should only be one
				if ($volume_discount->find(true))
				{
					$this->volume_discount_total = $volume_discount->discount_value;
					$this->volume_discount_id = $volume_discount->id;
				}
			}
			else // the volume reward is tied to markup
				// if there is no markup for the store use a default value of $20
			{
				$discount_amount = false;

				if ($this->mark_up)
				{

					$markup_link = DAO_CFactory::create('mark_up_multi_to_volume_discount');
					$markup_link->mark_up_multi_id = $this->mark_up->id;

					if ($markup_link->find(true))
					{
						$volume_discount = DAO_CFactory::create('volume_discount_type');
						$volume_discount->id = $markup_link->volume_discount_id;

						if ($volume_discount->find(true))
						{
							$discount_amount = $volume_discount->discount_value;
						}
					}
				}

				// we are looking for false here - a 0 value is legal and must be enforced
				if ($discount_amount === false)
				{

					$this->volume_discount_total = 30;
					$this->volume_discount_id = 1;
				}
				else // if there is no vd associated with a markup then use the default value
					// and point the order->volume_discount_id to row 1 of the volume_discount_type table
				{
					$this->volume_discount_total = $discount_amount;
					$this->volume_discount_id = $volume_discount->id;
				}
			}
		}
	}

	function canApplyVolumeDisount($canAllowVolumeRewardForTransition = false)
	{

		// No volume discount if any of the below are true

		if ($this->storeAndUserSupportPlatePoints)
		{
			return false;
		}

		if (CStore::hasPlatePointsTransitionPeriodExpired($this->store_id) && !$canAllowVolumeRewardForTransition)
		{
			return false;
		}

		// 2) there is a session discount
		if (!empty($this->session->session_discount_id))
		{

			$session_discount = DAO_CFactory::create("session_discount");
			$session_discount->id = $this->session->session_discount_id;

			if ($session_discount->find(true))
			{
				if ($session_discount->discount_var > 0)
				{
					return false;
				}
			}
		}

		// 3) this is a preferred user
		if (!empty($this->preferred))
		{
			return false;
		}

		if ($this->isBundleOrder())
		{
			return false;
		}

		return true;
	}

	private function getNonDiscountableItemsTotal()
	{

		$total = 0;
		$markup = $this->mark_up;

		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				list($qty, $mi_obj) = $item;

				if (isset($mi_obj->parentItemId) && isset($this->items[$mi_obj->parentItemId]))
				{

					//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
					$qty -= $mi_obj->bundleItemCount;
					if ($qty <= 0)
					{
						continue;
					}
				}

				if ($mi_obj->is_kids_choice || $mi_obj->is_menu_addon || $mi_obj->is_side_dish || $mi_obj->is_chef_touched)
				{
					if (isset($mi_obj->override_price))
					{
						$total += ($mi_obj->override_price * $qty);
					}
					else
					{
						if ($this->family_savings_discount_version == 2)
						{
							$total += self::getItemMarkupMultiSubtotal($markup, $mi_obj, $qty);
						}
						else
						{
							$total += self::getItemMarkupSubtotal($markup, $mi_obj, $qty);
						}
					}
				}
			}
		}

		return $total;
	}

	function getDreamRewardsDiscountBasis()
	{
		$basis = 0;

		if (isset($this->items))
		{
			usort($this->items, 'sort_by_price');

			$servingCount = 0;
			foreach ($this->items as $item)
			{
				list($qty, $mi_obj) = $item;

				if ($mi_obj->menu_category_Id < 5) // must be core item
				{
					list($qty, $mi_obj) = $item;
					$servingThisItem = $qty * $mi_obj->servings_per_item;

					if ($servingThisItem + $servingCount > 72)
					{
						// take partial amount and add to basis
						continue;
					}
					else
					{
						$basis += ($qty * $mi_obj->price);
						$servingCount += $servingThisItem;
					}
				}
			}
		}

		return $basis;
	}

	function applyDreamRewardsDiscount($editing)
	{
		$this->dream_rewards_discount = 0;

		// hard rule so nothing to do
		if ($this->servings_total_count < 36)
		{
			return;
		}

		// the level is externally set and if set causes the automatic application of the discount appropriate for the level
		// if the discount is to be defeated the level must be set to zero or unset
		if (isset($this->dream_rewards_level) && $this->dream_rewards_level > 0 && !empty($this->user_id))
		{
			require_once('includes/DAO/BusinessObject/CDreamRewardsHistory.php');

			$discountRewardLevel = $this->dream_rewards_level;
			if ($discountRewardLevel > 13)
			{
				$discountRewardLevel = 13;
			}

			$User = DAO_CFactory::create('user');
			$User->id = $this->user_id;
			$User->selectAdd();
			$User->selectAdd(" dream_rewards_version ");

			if (!$User->find(true))
			{
				throw new Exception("User not found in COrders::applyDreamRewardsDiscount().");
			}

			// just in case
			if ($User->dream_rewards_version > 2 && !$editing)
			{
				return;
			}

			// However if we are editing this could be an old DR order so force the version to 2
			if (!$this->order_is_in_plate_points_program)
			{
				$User->dream_rewards_version = 2;
			}

			$rewardData = CDreamRewardsHistory::rewardDataByLevel($User->dream_rewards_version, $discountRewardLevel);

			if ($rewardData['type'] == 'percent')
			{

				$nonDiscountableItemsTotal = $this->getNonDiscountableItemsTotal();

				$base = $this->subtotal_menu_items + $this->subtotal_home_store_markup - $nonDiscountableItemsTotal - $this->volume_discount_total;
				//$base = $this->getDreamRewardsDiscountBasis();

				$percentDiscount = $rewardData['value'];

				if ($this->is_dr_downgraded_order == 1)
				{
					$this->dream_rewards_discount = self::std_round(($base * 5) / 100, 2);
				}
				else
				{
					$this->dream_rewards_discount = self::std_round(($base * $percentDiscount) / 100, 2);
				}
			}
			// if order is succssully placed the client code is responsible for advancing the program level.

		}
	}

	function isFreePromotionInEffectForSession($store, $session)
	{

		$sessionStartTS = strtotime($session->session_start);

		// Range of sessions that will have no service fee
		$promoStartTS = mktime(0, 0, 0, 11, 28, 2016);
		$promoEndTS = mktime(0, 0, 0, 1, 2, 2017);

		// orders must be placed after this date to qualify for the free MFY session
		// Tiffany Herman directed any MFY made for the promo month qualifys - RCS 4-22-2016
		// $triggerStartTS = mktime(0, 0, 0, 5, 31, 2015);

		if ($store->supports_free_assembly_promotion && ($sessionStartTS >= $promoStartTS && $sessionStartTS < $promoEndTS))
		{
			return true;
		}

		return false;
	}

	function applyServiceFee($editing = false)
	{

		if ($editing || $this->allowOverrideOfServiceFee)
		{
			return;
		}

		// leave alone override values from edit or initial Order Manager activation

		$this->subtotal_service_fee = 0;
		$this->service_fee_description = "null";

		if (!isset($this->session))
		{
			return;
		}

		if ($this->session->session_type == CSession::SPECIAL_EVENT)
		{

			if ($this->isNewIntroOffer())
			{
				return;
			}

			if ($this->store_id && isset($this->session->session_start))
			{
				// we have what we need to determine if the  assembly fee should be waved

				if (!isset($this->store))
				{
					$this->store = DAO_CFactory::create('store');
					$this->store->id = $this->store_id;
					$this->store->find(true);
				}

				if ($this->isFreePromotionInEffectForSession($this->store, $this->session))
				{
					$this->service_fee_description = "Free Assembly Promo";

					return;
				}
			}

			$menu_id = $this->menu_id;
			if (!isset($menu_id))
			{
				$menu_id = $this->session->menu_id;
			}

			if (OrdersHelper::allow_assembly_fee($menu_id))
			{
				$this->service_fee_description = "Pick Up Assembly Fee";
				if (isset($this->mark_up))
				{
					if ($this->session->isDelivery())
					{
						$this->service_fee_description = "Delivery Assembly Fee";

						if (!empty($this->session->session_assembly_fee))
						{
							$this->subtotal_service_fee = $this->session->session_assembly_fee;
						}
						else
						{
							$this->subtotal_service_fee = $this->mark_up->delivery_assembly_fee;
						}
					}
					else
					{
						if (!empty($this->session->session_assembly_fee))
						{
							$this->subtotal_service_fee = $this->session->session_assembly_fee;
						}
						else
						{
							$this->subtotal_service_fee = $this->mark_up->assembly_fee;
						}
					}
				}
				else if (!empty($this->session->session_assembly_fee))
				{
					$this->subtotal_service_fee = $this->session->session_assembly_fee;
				}
				else
				{
					if ($this->session->isDelivery())
					{
						$this->subtotal_service_fee = 0;
					}
					else
					{
						$this->subtotal_service_fee = 20;
					}
				}
			}
			else
			{
				$this->service_fee_description = "Restocking Fee";
				$this->subtotal_service_fee = 0;
			}
		}
	}

	function getPointCredits(&$food_portion_of_points_credit, &$fee_portion_of_points_credit, $editing = false, $rescheduling = false, $hasServiceFeeCoupon = false)
	{

		if ($this->points_discount_total == 0)
		{
			$food_portion_of_points_credit = 0;
			$fee_portion_of_points_credit = 0;

			return 0;
		}

		if (empty($this->misc_food_subtotal))
		{
			$this->misc_food_subtotal = 0;
		}

		if ($hasServiceFeeCoupon)
		{
			// special case, the service is cancelled out with a coupon

			$maxDeduction = $this->getPointsDiscountableAmount();
			// now includes all deductible amounts including service fee
			$maxDeduction -= $this->subtotal_service_fee;

			if ($this->points_discount_total > $maxDeduction)
			{
				$this->points_discount_total = $maxDeduction;
			}

			$food_portion_of_points_credit = $this->points_discount_total;
			$fee_portion_of_points_credit = 0;

			return;
		}

		$maxDeduction = $this->getPointsDiscountableAmount();

		if (!empty($this->coupon) && $this->coupon->limit_to_finishing_touch)
		{
			$maxDeduction -= $this->coupon_code_discount_total;
		}

		if ($maxDeduction < 0)
		{
			$maxDeduction = 0;
		}

		if ($editing)
		{
			// if editing just accept the set amount as it has already been vetted and the credits adjusted
			$amountRequested = $this->points_discount_total;
		}
		else if ($rescheduling == 'to_non_mfy')
		{
			// if rescheduling from MFY to Standard there may be PlatePoints that were discounting the service fee.

			// Therefore if the maxDeduction is less than the current assinged dollars we may need to adjust that.
			if ($this->points_discount_total > $maxDeduction)
			{
				$amountRequested = $maxDeduction;
			}
			else
			{
				$amountRequested = $this->points_discount_total;
			}
		}
		else if ($rescheduling == 'to_mfy')
		{

			$amountRequested = $this->points_discount_total;

			// Now that there is a service the the split of what is assigned to service fee vs food could change and thus
			// change the tax amounts. But keep the consumed dinner dollars the same
		}
		else
		{
			$maxAvailableCredit = CPointsCredits::getAvailableCreditForUser($this->user_id);
			$amountRequested = $this->points_discount_total;

			if ($amountRequested > $maxAvailableCredit)
			{
				$amountRequested = $maxAvailableCredit;
			}

			if ($amountRequested > $maxDeduction)
			{
				$amountRequested = $maxDeduction;
			}
		}

		$this->points_discount_total = $amountRequested;

		if ($this->pp_discount_mfy_fee_first)
		{
			if ($this->points_discount_total > $this->subtotal_service_fee)
			{
				$food_portion_of_points_credit = $this->points_discount_total - $this->subtotal_service_fee;
				$fee_portion_of_points_credit = $this->subtotal_service_fee;
			}
			else
			{
				$fee_portion_of_points_credit = $this->points_discount_total;
				$food_portion_of_points_credit = 0;
			}
		}
		else
		{
			$foodPortionOfMaxDeduction = $maxDeduction - $this->subtotal_service_fee;

			if ($this->points_discount_total > $foodPortionOfMaxDeduction)
			{
				$remainderAfterFoodDiscount = $this->points_discount_total - $foodPortionOfMaxDeduction;
				$food_portion_of_points_credit = $foodPortionOfMaxDeduction;
				$fee_portion_of_points_credit = $remainderAfterFoodDiscount;
			}
			else
			{
				$food_portion_of_points_credit = $this->points_discount_total;
				$fee_portion_of_points_credit = 0;
			}
		}
	}

	function summarizeOrderState($food_portion_of_points_credit = false, $fee_portion_of_points_credit = false)
	{

		$retVal = array();

		$retVal['grand_total'] = $this->grand_total;
		$retVal['subtotal_all_taxes'] = $this->subtotal_all_taxes;
		$retVal['subtotal_food_items_adjusted'] = $this->subtotal_food_items_adjusted;
		$retVal['subtotal_service_fee'] = $this->subtotal_service_fee;
		$retVal['subtotal_delivery_fee'] = $this->subtotal_delivery_fee;
		$retVal['direct_order_discount'] = $this->direct_order_discount;
		$retVal['misc_food_subtotal'] = $this->misc_food_subtotal;
		$retVal['subtotal_menu_items'] = $this->subtotal_menu_items;
		$retVal['subtotal_home_store_markup'] = $this->subtotal_home_store_markup;
		$retVal['subtotal_menu_item_mark_down'] = $this->subtotal_menu_item_mark_down;
		$retVal['subtotal_ltd_menu_item_value'] = $this->subtotal_ltd_menu_item_value;
		$retVal['user_preferred_discount_total'] = $this->user_preferred_discount_total;
		$retVal['coupon_code_discount_total'] = $this->coupon_code_discount_total;
		$retVal['bundle_discount'] = $this->bundle_discount;
		$retVal['food_portion_of_points_credit'] = $food_portion_of_points_credit;
		$retVal['fee_portion_of_points_credit'] = $fee_portion_of_points_credit;

		$itemArr = array();
		if (!empty($this->items))
		{

			foreach ($this->items as $id => $data)
			{

				$qty = $data[0];
				$obj = $data[1];

				$entry = array(
					'id' => $id,
					'qty' => $qty,
					'name' => $obj->menu_item_name,
					'price' => $obj->price,
					'store_price' => (isset($obj->store_price) ? $obj->store_price : "-"),
					'override_price' => $obj->override_price,
					'bundle_id' => (isset($obj->bundle_id) ? $obj->bundle_id : "-"),
					'ltd_value' => (isset($obj->ltd_menu_item_value) ? $obj->ltd_menu_item_value : "-"),
				);

				$itemArr[] = $entry;
			}

			$retVal['items'] = $itemArr;
		}

		return $retVal;
	}

	function applyMembershipDiscount($discount, $membership_id, $food_portion_of_points_credit, $fee_portion_of_points_credit)
	{
		if (empty($food_portion_of_points_credit))
		{
			$food_portion_of_points_credit = 0;
		}

		if (empty($fee_portion_of_points_credit))
		{
			$fee_portion_of_points_credit = 0;
		}

		if (empty($this->direct_order_discount))
		{
			$this->direct_order_discount = 0;
		}

		$discountRate = $discount / 100;

		$membershipDiscountBasis = $this->subtotal_menu_items + $this->subtotal_home_store_markup - $this->subtotal_menu_item_mark_down - $food_portion_of_points_credit - $fee_portion_of_points_credit - $this->direct_order_discount;

		$discount_amount = self::std_round($membershipDiscountBasis * $discountRate, 2);

		$this->membership_discount = $discount_amount;
		$this->membership_id = $membership_id;
	}

	function applyDeliveryFee($editing = false)
	{
		if ($editing || $this->allowOverrideOfDeliveryFee)
		{
			return;
		}

		$sessionObj = $this->findSession();
		if (!$editing && $sessionObj)
		{
			if ($this->findSession()->isDelivered())
			{
				$this->subtotal_delivery_fee = 0;
				$Boxes = $this->getBoxes();

				if (!empty($Boxes))
				{
					foreach ($Boxes as $box_instance_id => $thisBox)
					{
						if (!empty($thisBox['bundle']->price_shipping) && !empty($thisBox["box_instance"]->is_complete))
						{
							$this->subtotal_delivery_fee += $thisBox['bundle']->price_shipping;
						}
					}
				}
			}
			else if ($this->findSession()->isDelivery() || $this->findSession()->isRemotePickup())
			{
				$this->subtotal_delivery_fee = 0;

				if (!empty($this->findSession()->session_delivery_fee) || !empty($this->getStore()->delivery_fee))
				{
					if (!empty($this->findSession()->session_delivery_fee))
					{
						$this->subtotal_delivery_fee = $this->findSession()->session_delivery_fee;
					}
					else
					{
						$this->subtotal_delivery_fee = $this->getStore()->delivery_fee;
					}
				}
			}
			else
			{
				$this->subtotal_delivery_fee = 0;
			}
		}
	}

	function orderIsCancelledSubscriptionOrder($guest, &$discountRate, &$membership_id)
	{

		$Session = $this->findSession();

		if (!empty($Session))
		{
			$pastSession = $Session->isInThePast($this->getStore());
			$hasMID = (isset($this->membership_id) && is_numeric($this->membership_id) && $this->membership_id > 0);

			if ($hasMID)
			{
				$membership_id = $this->membership_id;
				$guest->isMenuValidForMembershipOrder($Session->menu_id, $discountRate, $membership_id, true);
			}

			return ($pastSession && $hasMID);
		}

		return false;
	}

	//Check to see if there is not a different existing order that meets the monthly minimum to
	//allow additional ordering. This is used in create.
	public function updateMinimumQualification()
	{
		$user = $this->getUser();

		if (!COrderMinimum::allowsAdditionalOrdering($this->store_id, $this->session->menu_id))
		{
			$this->is_qualifying = 0;
			$this->qualifying_menu_id = null;

			return;
		}

		$hasExistingMinimumOrder = $user->hasMinimumQualifyingOrderDefined($this->store_id, $this->session->menu_id);
		if (!$hasExistingMinimumOrder)
		{
			$this->is_qualifying = COrderMinimum::doesOrderQualifiesAsMinimum($this) ? 1 : 0;
			if ($this->is_qualifying)
			{
				$this->qualifying_menu_id = $this->session->menu_id;
			}
		}
	}

	/**
	 * This will look to see if this order still qualifies as monthly minimum on
	 * edit order, if this was already the minimum.
	 *
	 * @throws Exception
	 */
	public function updateIsQualifyingOrderAttributesOnEdit()
	{
		//if this was the qualifying order
		if ($this->is_qualifying)
		{
			$this->is_qualifying = COrderMinimum::doesOrderQualifiesAsMinimum($this) ? 1 : 0;
			if (!$this->is_qualifying)
			{
				$this->qualifying_menu_id = null;
			}
		}
	}

	/**
	 * Call this to recalculate all totals and apply adjustments
	 */
	function recalculate($editing = false, $suppressSessionDiscount = false, $rescheduling = false, $userIsOnHold = false)
	{
		// Initialize non-financials
		$this->PlatePointsRulesVersion = 3;
		if (!$this->order_type)
		{
			$this->setOrderType();
		}

		$this->applyServiceFee($editing);

		$taxRelation = $this->getFoodAndServiceRelation();
		// test for need for MFY credit box
		if ($this->subtotal_service_fee > 0 && $taxRelation == 'svc_tax_greater')
		{
			$this->pp_discount_mfy_fee_first = 1;
		}
		else
		{
			$this->pp_discount_mfy_fee_first = 0;
		}

		$this->subtotal_all_taxes = 0;
		$this->subtotal_all_items = 0;
		$this->volume_discount_total = 0;
		$this->grand_total = 0;
		$this->bundle_discount = 0;

		$this->calculateBasePrice();
		$this->applyMarkup();

		$this->applyMarkdown();
		// Note: markdown is stored in subtotal_menu_item_mark_down and is subtracted from subtotal_food_items_adjusted

		//$this->subtotal_home_store_markup +=
		$this->applyLTDMenuItemValue($editing);
		// the ltd meal donation is considered food cost and is added to mark up

		$this->calculateOtherTotals();

		if (!empty($this->bundle_id) && isset($this->bundle))
		{

			$this->applyBundleDiscount();
		}
		//$this->applyVolumeDiscount();  no more!

		$this->applyDreamRewardsDiscount($editing);
		$this->applyPremium(); //note: a percent premium will apply to promo items
		// $this->applyPromo($editing); no more!

		//if ($this->isFamilySavingsOrder())
		//	$this->calculateFamilySavings($editing);  no more

		if (!isset($this->misc_food_subtotal))
		{
			$this->misc_food_subtotal = 0;
		}
		if (!isset($this->misc_nonfood_subtotal))
		{
			$this->misc_nonfood_subtotal = 0;
		}

		if ($this->bundle_discount == 0 && empty($this->bundle_id))
		{
			$this->applyPreferred();
		}
		else
		{
			$this->user_preferred_discount_total = 0;
			$this->user_preferred_id = null;
		}

		$food_portion_of_points_credit = 0;
		$fee_portion_of_points_credit = 0;

		$hasServiceFeeCoupon = false;
		$hasDeliveryFeeCoupon = false;

		if (isset($this->coupon) && !empty($this->coupon->coupon_code))
		{

			$UCCode = strtoupper($this->coupon->coupon_code);

			if ($this->coupon && !empty($this->coupon->limit_to_mfy_fee))
			{
				$hasServiceFeeCoupon = true;
			}

			if ($this->coupon && !empty($this->coupon->limit_to_delivery_fee))
			{
				$hasDeliveryFeeCoupon = true;
			}
		}

		if ($this->storeAndUserSupportPlatePoints || $this->is_in_plate_points_program || $userIsOnHold)
		{

			if (!empty($this->coupon) && $this->coupon->limit_to_finishing_touch)
			{
				$this->applyCoupon();
			}

			$this->getPointCredits($food_portion_of_points_credit, $fee_portion_of_points_credit, $editing, $rescheduling, $hasServiceFeeCoupon);
		}

		// Membership Discount
		$this->membership_id = 0;
		$this->membership_discount = 0;
		if (!empty($this->store_id) && $this->getStore()->supports_membership)
		{
			$mpp_menu_id = false;
			$guest = $this->getUser();

			if ($guest)
			{
				$theSession = $this->findSession();

				if (!empty($theSession))
				{
					$mpp_menu_id = $theSession->menu_id;
					$sessionOrderIsValid = true;

					if ($theSession->session_type != CSession::STANDARD && $theSession->session_type != CSession::SPECIAL_EVENT)
					{
						$sessionOrderIsValid = false;
					}
					else if ($this->isNewIntroOffer())
					{
						$sessionOrderIsValid = false;
					}
				}
				else
				{
					$sessionOrderIsValid = false;
				}

				$discountRate = 0;
				$membership_id = false;

				if ($sessionOrderIsValid && ($guest->isMenuValidForMembershipOrder($mpp_menu_id, $discountRate, $membership_id) || $this->orderIsCancelledSubscriptionOrder($guest, $discountRate, $membership_id)))
				{
					$this->applyMembershipDiscount($discountRate, $membership_id, $food_portion_of_points_credit, $fee_portion_of_points_credit);
				}
			}
		}

		$this->applyDeliveryFee($editing);

		// TODO: must check multiple discount rules
		$this->applyCoupon($editing);

		// WARNING:  Any discounts added above here must take into consideration the new subtotal_menu_item_mark_down if the discount is based on food cost

		// Note the LTD Meal Donation is reflected in the markup
		$this->subtotal_food_items_adjusted = $this->subtotal_menu_items - $this->subtotal_menu_item_mark_down + $this->dream_rewards_discount - $this->user_preferred_discount_total + $this->subtotal_home_store_markup - $this->promo_code_discount_total + $this->subtotal_premium_markup - $this->family_savings_discount - $this->volume_discount_total - $this->bundle_discount - $food_portion_of_points_credit - $this->membership_discount;

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

		if (!$editing || (!empty($this->session_discount_id) && is_numeric($this->session_discount_id)))
		{
			$this->applySessionDiscount($suppressSessionDiscount, $editing);
		}

		$this->subtotal_food_items_adjusted = floatval($this->subtotal_food_items_adjusted) - floatval($this->direct_order_discount) + floatval($this->misc_food_subtotal);

		if ($this->subtotal_food_items_adjusted < .005)
		{
			$this->subtotal_food_items_adjusted = 0;
		}

		$this->subtotal_products += $this->misc_nonfood_subtotal;

		$storeObj = $this->getStore();
		if (!empty($storeObj) && $storeObj->supports_bag_fee)
		{
			if (!empty($this->opted_to_bring_bags))
			{
				$this->total_bag_count = 0;
			}
			else if (is_null($this->total_bag_count))
			{
				$this->total_bag_count = self::getNumberBagsRequiredFromItems($this);
			}

			$this->subtotal_bag_fee = $this->total_bag_count * $storeObj->default_bag_fee;
		}
		else
		{
			$this->subtotal_bag_fee = 0;
			$this->total_bag_count = 0;
		}

		if ($this->recalculateMealCustomizationFee)
		{//Can pass in override so it should not be changed
			if (!empty($storeObj) && $storeObj->supports_meal_customization)
			{

				if ($this->opted_to_customize_recipes == 1)
				{
					//Do they have any customizations configured
					$customization = OrdersCustomization::getInstance($this);

					if ($customization->hasMealCustomizationPreferencesSet())
					{
						$sessionObj = $this->getSessionObj();
						if (empty($sessionObj))
						{
							$sessionObj = $this->findSession(true);
						}
						$sessionOpenForCustomization = (empty($sessionObj) ? false : $sessionObj->isOpenForCustomization($storeObj));
						if ($sessionOpenForCustomization || $this->allowRecalculateMealCustomizationFeeClosedSession())
						{
							$this->total_customized_meal_count = self::getNumberOfCustomizableMealsFromItems($this, $storeObj->allow_preassembled_customization);
						}
						else
						{
							$this->total_customized_meal_count = 0;
						}
					}
					else
					{
						$this->total_customized_meal_count = 0;
					}
				}
				else
				{
					$this->total_customized_meal_count = 0;
				}

				$this->subtotal_meal_customization_fee = $storeObj->customizationFeeForMealCount($this->total_customized_meal_count);
			}
			else
			{
				$this->subtotal_meal_customization_fee = 0;
				$this->total_customized_meal_count = 0;
			}
		}

		if (get_class($this) == 'COrdersDelivered')
		{
			$this->subtotal_all_items = $this->subtotal_food_items_adjusted + $this->subtotal_products + $this->subtotal_delivery_fee;
			$this->applyTax($fee_portion_of_points_credit, $hasServiceFeeCoupon, $hasDeliveryFeeCoupon);
		}
		else
		{
			$this->applyTax($fee_portion_of_points_credit, $hasServiceFeeCoupon, $hasDeliveryFeeCoupon);

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
		}

		$this->subtotal_all_items += $this->subtotal_bag_fee;

		$this->subtotal_all_items += $this->subtotal_meal_customization_fee;

		$this->grand_total = $this->subtotal_all_items + $this->subtotal_all_taxes;

		return $this->grand_total;
	}

	/**
	 * Creates a Booking on insert() and determine if premium applies
	 */
	function addSession($session)
	{
		$this->session = $session;
		$this->store_id = $session->store_id;
	}

	/**
	 * Removes session from order
	 */
	function clearSession()
	{
		if (isset($this->session))
		{
			unset($this->session);
		}
	}

	/**
	 * Set discount object
	 */
	function addDiscount($discountAmount)
	{
		//no negative discounts
		if ($discountAmount == '')
		{
			$discountAmount = 0;
		}

		if ($discountAmount < 0)
		{
			$discountAmount = 0 - $discountAmount;
		}

		$this->direct_order_discount = $discountAmount;
	}

	/**
	 * Pass in the results of a UP->findActive() call to apply the best dicount
	 */
	function addActivePreferred($preferred)
	{
		//find lowest preferred
		$lowest = null;
		$lowestUP = null;
		while ($preferred->fetch())
		{
			$Order = clone($this);
			$Order->addPreferred($preferred);
			$Order->recalculate();
			if ((!$lowest) || ($Order->grand_total < $lowest))
			{
				$lowest = $Order->grand_total;
				$lowestUP = clone($preferred);
			}
		}

		if ($lowestUP)
		{
			$this->addPreferred($lowestUP);
		}
	}

	/**
	 * Pass in a single preferred discount row
	 */
	function addPreferred($preferred)
	{
		if (!$preferred)
		{
			return;
		}

		$this->preferred = $preferred;
		$this->user_preferred_id = $preferred->id;
	}

	function clearPreferred()
	{
		unset($this->preferred);
		$this->user_preferred_id = null;
	}

	function clearMealCustomizations($includeMealCustomizationSettings = false)
	{
		$this->opted_to_customize_recipes = null;
		$this->total_customized_meal_count = 0;
		$this->subtotal_meal_customization_fee = 0;
		if ($includeMealCustomizationSettings)
		{
			$this->order_customization = '';
		}
	}

	function addMarkup($mark_up)
	{
		if (!$mark_up)
		{
			$this->markup_id = null;

			return;
		}

		$this->mark_up = $mark_up;
		$this->mark_up_multi_id = $mark_up->id;
	}

	function addSalesTax($sales_tax)
	{
		if (!$sales_tax)
		{
			$this->sales_tax = null;
			$this->sales_tax_id = null;

			return;
		}

		$this->sales_tax = $sales_tax;
		$this->sales_tax_id = $sales_tax->id;
	}

	/**
	 * Set promo obj
	 */
	function addPromo($promo)
	{
		if (!$promo)
		{
			$this->promo = null;
			//$promo->promo_code_id = null;  // LMH remove.. not needed.. $promo doesn't exist
			$this->promo_code_id = null;  // LMH  another bug in the store view... 12/5/06
			$this->promo_code_discount_total = 0;

			return;
		}

		$this->promo = $promo;
		$this->promo_code_id = $promo->id;
	}

	/**
	 * Set coupon obj
	 */
	function addCoupon($coupon)
	{
		if (!$coupon)
		{
			$this->coupon = null;
			$this->coupon_code_id = null;  // LMH  another bug in the store view... 12/5/06
			$this->coupon_code_discount_total = 0;

			return;
		}

		$this->coupon = $coupon;
		$this->coupon_code_id = $coupon->id;
	}

	function addLTDRoundUp($roundUpValue)
	{
		if ($roundUpValue == 0)
		{
			$this->ltd_round_up_value = 0;

			return;
		}
		else if (empty($roundUpValue))
		{
			$this->ltd_round_up_value = null;

			return;
		}

		$this->ltd_round_up_value = $roundUpValue;
	}

	public function pullOutPromoItem()
	{

		if ($this->items)
		{
			foreach ($this->items as $id => &$item)
			{
				if (isset($item[1]->isPromo) && $item[1]->isPromo)
				{
					$item[0]--;
					if ($item[0] <= 0)
					{
						unset($this->items[$id]);
					}

					$thePromoID = false;
					if (!empty($this->promo_code_id))
					{

						$thePromoID = $this->promo_code_id;
						$this->promo_code_id = null;
					}

					// need to remove this item if qty is zero
					$promo = DAO_CFactory::create('promo_code');
					$promo->id = $thePromoID;
					if ($promo->find(true))
					{
						return $promo->promo_code;
					}
					else
					{
						return false;
					}
				}
			}
		}

		return false;
	}

	public function removeCouponDiscountedRecipe()
	{
		if (!empty($this->items) && !empty($this->coupon->limit_to_recipe_id) && !empty($this->coupon->menu_item_id))
		{
			if (isset($this->items[$this->coupon->menu_item_id]))
			{
				$this->items[$this->coupon->menu_item_id][0]--;

				if ($this->items[$this->coupon->menu_item_id][0] <= 0)
				{
					unset($this->items[$this->coupon->menu_item_id]);
				}
			}
		}
	}

	public function removeCouponFreeMealItem()
	{
		if ($this->items)
		{
			foreach ($this->items as $id => &$item)
			{
				if (isset($item[1]->isFreeMeal) && $item[1]->isFreeMeal)
				{
					$item[0]--;
					if ($item[0] <= 0)
					{
						unset($this->items[$id]);
					}
				}
			}
		}
	}

	public function removeCoupon()
	{
		if (isset($this->coupon))
		{
			$this->removeCouponFreeMealItem();
			$this->removeCouponDiscountedRecipe();

			$this->coupon = null;
			$this->coupon_code_id = null;
			$this->coupon_code_discount_total = 0;
		}
	}

	public function getMealCustomizationJson()
	{
		$customizationWrapper = OrdersCustomization::getInstance($this);

		return $customizationWrapper->mealCustomizationToJson();
	}

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
		$totalQtyCore = 0;
		$servingsCount = 0;
		$coreServingsCount = 0;

		$useCurrent = true;
		$pricing_method = 'USE_CURRENT';
		$full_price = 0;
		$half_price = 0;
		$toddItems = array();

		if (!empty($this->session))
		{

			if ($this->session->session_type == 'TODD')
			{

				$toddItems = CMenu::getTODDMenuMenbuItemIDsForSession($this->session->id);

				$todd_props = DAO_CFactory::create('session_properties');
				$todd_props->session_id = $this->session->id;
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
		}

		if ($this->items)
		{
			foreach ($this->items as $item)
			{

				list($qty, $DAO_menu_item) = $item;

				if (isset($DAO_menu_item->parentItemId) && isset($this->items[$DAO_menu_item->parentItemId]))
				{
					//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
					$qty -= $DAO_menu_item->bundleItemCount;

					if ($qty <= 0)
					{
						continue;
					}
				}

				if ($useCurrent || !array_key_exists($DAO_menu_item->id, $toddItems))
				{
					$totalPrice += $qty * $DAO_menu_item->store_price;

					if ($this->type_of_order == 'INTRO' || (!empty($this->bundle) && $this->bundle->bundle_type == 'TV_OFFER'))
					{
						$totalPrice -= $DAO_menu_item->ltd_menu_item_value;
					}
				}
				else
				{
					if ($pricing_method == 'OVERRIDE')
					{
						$thisPrice = ($DAO_menu_item->pricing_type == 'HALF' ? $half_price : $full_price);
						if (self::isPriceGreaterThan($thisPrice, $DAO_menu_item->price))
						{
							$thisPrice = $DAO_menu_item->price;
						}

						$totalPrice += ($thisPrice * $qty);
					}
					else // FREE
					{
						//mothing to do
					}
				}

				$totalQty += $qty * $DAO_menu_item->item_count_per_item;

				if ($DAO_menu_item->menu_item_category_id == 1 || ($DAO_menu_item->menu_item_category_id == 4 && empty($DAO_menu_item->is_store_special)))
				{
					$totalQtyCore += $qty * $DAO_menu_item->item_count_per_item;
				}

				if ($DAO_menu_item->is_chef_touched)
				{
					$servingThisItem = 0;
				}
				else if (isset($DAO_menu_item->servings_per_item))
				{
					$servingThisItem = $qty * $DAO_menu_item->servings_per_item;
				}
				else
				{
					$servingThisItem = $qty * CMenuItem::translatePricingTypeToNumeric($DAO_menu_item->pricing_type);
				}

				if ((isset($DAO_menu_item->is_side_dish) && $DAO_menu_item->is_side_dish) || $DAO_menu_item->menu_item_category_id == 9)
				{
					$this->pcal_sidedish_total_count += $qty;
				}
				else if (isset($DAO_menu_item->is_preassembled) && $DAO_menu_item->is_preassembled)
				{
					$this->pcal_preassembled_total_count += $qty;
					$servingsCount += $servingThisItem;

					if (!$DAO_menu_item->is_store_special)
					{
						$coreServingsCount += $servingThisItem;
					}
				}
				else
				{
					if (!$DAO_menu_item->is_store_special)
					{
						// all store specials should be pre-assembled but ... just in case
						$coreServingsCount += $servingThisItem;
					}

					$servingsCount += $servingThisItem;
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
				list($qty, $DAO_menu_item) = $item;
				$product_total += $qty * $DAO_menu_item->price;
				$product_qty += $qty;
			}
		}

		$this->subtotal_products = $product_total;
		$this->product_items_total_count = $product_qty;
		$this->subtotal_menu_items = $totalPrice;
		$this->menu_items_total_count = $totalQty;
		$this->menu_items_core_total_count = $totalQtyCore;
		$this->servings_total_count = $servingsCount;
		$this->servings_core_total_count = $coreServingsCount;

		return $this->subtotal_menu_items;
	}

	/*
	 *  Calculate the "pcal" totals. These are precalculated to make reports and average per serving cost
	 *  calculations more efficient. These values are used in calculating Coupon value
	 */
	private function calculateOtherTotals()
	{

		$store = $this->getStore();
		$supportsMOTM = !is_null($store) ? $store->supports_ltd_roundup : false;

		$this->pcal_core_total = 0;
		$this->pcal_preassembled_total = 0;
		$this->pcal_sidedish_total = 0;

		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				list($qty, $DAO_menu_item) = $item;

				if ($DAO_menu_item->isMenuItem_Core())
				{
					// Newer dao object method
					if (!empty($DAO_menu_item->DAO_order_item))
					{
						$this->pcal_core_total += $DAO_menu_item->store_price * ($DAO_menu_item->DAO_order_item->item_count - $DAO_menu_item->DAO_order_item->bundle_item_count);
					}
					else
					{
						// old method
						$this->pcal_core_total += $DAO_menu_item->store_price * ($qty - $DAO_menu_item->bundleItemCount);
					}
				}

				if ((isset($DAO_menu_item->is_side_dish) && $DAO_menu_item->is_side_dish) || $DAO_menu_item->isMenuItem_SidesSweets())
				{
					// Newer dao object method
					if (!empty($DAO_menu_item->DAO_order_item))
					{
						$this->pcal_sidedish_total += $DAO_menu_item->store_price * ($DAO_menu_item->DAO_order_item->item_count - $DAO_menu_item->DAO_order_item->bundle_item_count);
					}
					else
					{
						// old method
						$this->pcal_sidedish_total += $DAO_menu_item->store_price * ($qty - $DAO_menu_item->bundleItemCount);
					}
				}

				if (isset($DAO_menu_item->is_preassembled) && $DAO_menu_item->is_preassembled)
				{
					// Newer dao object method
					if (!empty($DAO_menu_item->DAO_order_item))
					{
						$this->pcal_preassembled_total += $DAO_menu_item->store_price * ($DAO_menu_item->DAO_order_item->item_count - $DAO_menu_item->DAO_order_item->bundle_item_count);
					}
					else
					{
						// old method
						$this->pcal_preassembled_total += $DAO_menu_item->store_price * ($qty - $DAO_menu_item->bundleItemCount);
					}
				}
			}
		}

		$this->getAvgCostPerServingData();
	}

	function getPointsDiscountableAmount()
	{
		return $this->subtotal_menu_items + $this->subtotal_home_store_markup + $this->subtotal_service_fee;
	}

	private function applyMarkdown()
	{
		$this->subtotal_menu_item_mark_down = 0;

		if ($this->items)
		{
			foreach ($this->items as &$item)
			{
				list($qty, $mi_obj) = $item;

				if (!empty($mi_obj->markdown_id) && is_numeric($mi_obj->markdown_id) && $mi_obj->markdown_id > 0)
				{
					CLog::Assert(!empty($mi_obj->store_price) && is_numeric($mi_obj->store_price), "store price should have been set in previous step.");

					$this->subtotal_menu_item_mark_down += (self::std_round(($mi_obj->store_price * $mi_obj->markdown_value) / 100, 2)) * $qty;
				}
			}
		}
	}

	public function applyLTDMenuItemValue($editing = false)
	{
		$doApplyValue = false;
		$hasInProgramItem = false;
		$ltdItemExists = false;
		$storeIsInProgram = !empty($this->store->supports_ltd_roundup);

		if ($editing)
		{
			// get current value of subtotal_ltd_menu_item_value for the order
			$current_subtotal_ltd_menu_item_value = $this->subtotal_ltd_menu_item_value;

			// check for a menu item that is in-program
			if (!empty($this->items))
			{
				foreach ($this->items as &$item)
				{
					list($qty, $DAO_menu_item) = $item;

					if (!empty($DAO_menu_item->order_item_ltd_menu_item))
					{
						$hasInProgramItem = true;
					}

					// there is an item with a ltd_menu_item_value and an empty markdown_id so must be a ltd menu item
					if (!empty($DAO_menu_item->ltd_menu_item_value))
					{
						$ltdItemExists = true;
					}
				}
			}

			if (!$ltdItemExists)
			{
				$this->subtotal_ltd_menu_item_value = 0;

				return 0;
			}

			// The order has an item that was placed 'in program' and so the value should always be applied regardless
			// of the current store state
			if ($hasInProgramItem)
			{
				$doApplyValue = true;
			}
			else
			{
				// else the order may be being saved in the editor - check current state of the store and whether item exists
				if ($storeIsInProgram)
				{
					$doApplyValue = true;
				}
			}

			// special check for an item added outside of program that should not contribute in now opted in store
			if ($current_subtotal_ltd_menu_item_value == 0 && !$hasInProgramItem && $storeIsInProgram)
			{
				$doApplyValue = false;
			}
		}
		else
		{
			// new order, check to see if store is in-program
			if ($storeIsInProgram)
			{
				$doApplyValue = true;
			}
		}

		// reset subtotal_ltd_menu_item_value
		$this->subtotal_ltd_menu_item_value = 0;

		// calculate new subtotal_ltd_menu_item_value
		if ($doApplyValue)
		{
			if ($this->items)
			{
				foreach ($this->items as &$item)
				{
					list($qty, $DAO_menu_item) = $item;

					// Remove LTD donation from Starter Pack selections
					if (!empty($DAO_menu_item->ltd_menu_item_value))
					{
						if (isset($this->bundle) && !empty($this->bundle->items) && isset($this->bundle->items[$DAO_menu_item->id]))
						{
							if ($this->bundle->items[$DAO_menu_item->id]['chosen'])
							{
								$qty--;
							}
						}

						// Remove LTD donation from Master_Item bundle selections
						// turned off for now RCS April 4, 2020
						if (isset($DAO_menu_item->parentItemId))
						{
							if (!empty($this->items[$DAO_menu_item->parentItemId][1]->is_bundle))
							{
								$qty = $qty - $this->items[$DAO_menu_item->parentItemId][0];
							}
						}

						$this->subtotal_ltd_menu_item_value += $DAO_menu_item->ltd_menu_item_value * $qty;
					}
				}
			}
		}

		return $this->subtotal_ltd_menu_item_value;
	}

	/**
	 */
	private function applyMarkup()
	{
		$this->subtotal_home_store_markup = 0;
		$markup = $this->mark_up;

		/*
		if (!$markup ) {
			return;
		}
		*/

		$useCurrent = true;
		$pricing_method = 'USE_CURRENT';
		$full_price = 0;
		$half_price = 0;
		$toddItems = array();

		if (!empty($this->session))
		{

			if ($this->session->session_type == 'TODD')
			{

				$toddItems = CMenu::getTODDMenuMenbuItemIDsForSession($this->session->id);
				$todd_props = DAO_CFactory::create('session_properties');
				$todd_props->session_id = $this->session->id;
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
		}

		if ($this->items)
		{
			foreach ($this->items as &$item)
			{
				list($qty, $DAO_menu_item) = $item;

				if (isset($DAO_menu_item->parentItemId) && isset($this->items[$DAO_menu_item->parentItemId]))
				{
					//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
					$qty -= $DAO_menu_item->bundleItemCount;

					if ($qty <= 0)
					{
						continue;
					}
				}

				if ($useCurrent || !array_key_exists($DAO_menu_item->id, $toddItems))
				{
					if (isset($DAO_menu_item->override_price))
					{
						$thisMarkupAmt = ($DAO_menu_item->override_price * $qty) - ($DAO_menu_item->store_price * $qty);
						if ($thisMarkupAmt > 0)
						{
							$this->subtotal_home_store_markup += $thisMarkupAmt;
						}
					}
					else
					{
						if ($this->family_savings_discount_version == 2)
						{
							$DAO_menu_item->store_price = self::std_round(self::getItemMarkupMultiSubtotal($markup, $DAO_menu_item, 1));
							$thisMarkupAmt = self::getItemMarkupMultiSubtotal($markup, $DAO_menu_item, $qty) - ($DAO_menu_item->store_price * $qty);
							if ($thisMarkupAmt > 0)
							{
								$this->subtotal_home_store_markup += $thisMarkupAmt;
							}
						}
						else
						{
							$DAO_menu_item->store_price = self::std_round(self::getItemMarkupMultiSubtotal($markup, $DAO_menu_item, 1));
							$thisMarkupAmt = self::getItemMarkupSubtotal($markup, $DAO_menu_item, $qty) - ($DAO_menu_item->store_price * $qty);
							if ($thisMarkupAmt > 0)
							{
								$this->subtotal_home_store_markup += $thisMarkupAmt;
							}
						}
					}
				}
				else
				{
					if ($pricing_method == 'OVERRIDE')
					{
						$thisPrice = ($DAO_menu_item->pricing_type == 'HALF' ? $half_price : $full_price);

						if (self::isPriceGreaterThan($thisPrice, $DAO_menu_item->price))
						{
							$thisMarkupAmt = ($thisPrice - $DAO_menu_item->price) * $qty;

							$this->subtotal_home_store_markup += $thisMarkupAmt;
						}
					}
					else // FREE
					{
						//mothing to do
					}
				}
			}
		}

		if ($this->subtotal_home_store_markup < 0)
		{
			throw new Exception('markup exception');
		}
	}

	// CES 1-30-07 : editing flag switches how markup is found
	// if editing we must load the markup listed with the order
	// if ordering then current store markup is found
	function calculateFamilySavings($editing = false)
	{
		$totalServings = 0;

		foreach ($this->items as $itemArray)
		{
			$qty = $itemArray[0];
			$itemObj = $itemArray[1];
			if ($itemObj->pricing_type == CMenuItem::HALF)
			{
				$totalServings += $qty * 3;
			}
			else
			{
				$totalServings += $qty * 6;
			}
		}

		if ($totalServings >= 42)
		{

			if (!$this->store && isset($this->store_id))
			{
				$this->store = DAO_CFactory::create('store');
				$this->store->id = $this->store_id;
				$this->store->find(true);
			}

			$menu_id = null;
			if (isset($this->session))
			{
				$menu_id = $this->session->menu_id;
			}
			else if (isset($this->menu_id))
			{
				$menu_id = $this->menu_id;
			}

			$markup = null;
			if ($editing)
			{
				if (!empty($this->markup_id))
				{
					$markup = DAO_CFactory::create('mark_up');
					$markup->id = $this->markup_id;
					if (!$markup->find_includeDeleted(true))
					{
						throw new Exception('markup not found when setting up order editor for order: ' . $this->originalOrder->id);
					}
				}
			}
			else if ($this->store)
			{
				$markup = $this->store->getMarkUpObj($menu_id);
			}

			$discounted_total = 0.0;

			foreach ($this->items as $itemArray)
			{
				$qty = $itemArray[0];
				$itemObj = $itemArray[1];
				$discounted_total += self::getItemDiscountedSubtotal($markup, $itemObj, $qty, $totalServings);
			}

			$this->family_savings_discount = $this->subtotal_menu_items + $this->subtotal_home_store_markup - $discounted_total;
		}
		else
		{
			$this->family_savings_discount = 0;
		}
	}

	/**
	 */
	static function getItemMarkupSubtotal($markup, $itemObj, $qty = 1)
	{
		if (($itemObj->pricing_type == CMenuItem::INTRO) || (!$markup))
		{
			return $itemObj->price * $qty;
		}

		switch ($markup->markup_type)
		{
			case CMarkUp::FLAT:

				if ($itemObj->pricing_type == CMenuItem::HALF)
				{
					return $itemObj->price * $qty + ($markup->markup_value / 2) * $qty;
				}

				return $itemObj->price * $qty + $markup->markup_value * $qty;

			case CMarkUp::PERCENTAGE:
				return ($itemObj->price * $qty) + self::std_round(($itemObj->price * $markup->markup_value) / 100, 2) * $qty;

			default:
				throw new Exception('unknown markup type');
				break;
		}
	}

	static function getStorePrice($markup, $itemObj, $qty = 1, $hasLTDPriceSet = false, $storeSupportsMOTM = true)
	{

		$retVal = 0;

		if (isset($itemObj->override_price))
		{
			$retVal = $itemObj->override_price * $qty;
		}
		else
		{
			$retVal = self::getItemMarkupMultiSubtotal($markup, $itemObj, $qty);
		}

		if (isset($itemObj->markdown_id) && is_numeric($itemObj->markdown_id) && $itemObj->markdown_id > 0)
		{
			$percentage = $itemObj->markdown_value / 100;

			$retVal -= COrders::std_round($retVal * $percentage);
		}

		if (!empty($itemObj->ltd_menu_item_value) && !$hasLTDPriceSet && $storeSupportsMOTM)
		{
			$retVal += COrders::std_round($itemObj->ltd_menu_item_value);
		}

		return CTemplate::number_format($retVal);
	}

	/**
	 */
	static function getItemMarkupMultiSubtotal($markup, $itemObj, $qty = 1)
	{
		// Item Pricing

		if ($itemObj->pricing_type == CMenuItem::INTRO)
		{
			if (!$markup)
			{
				return 12.50 * $qty;
			}

			return $markup->sampler_item_price * $qty;
		}

		if (!$markup)
		{
			return $itemObj->price * $qty;
		}

		if ($itemObj->pricing_type == CMenuItem::HALF)
		{
			if (empty($markup->markup_value_3_serving))
			{
				$markup->markup_value_3_serving = 0.0;
			}

			return ($itemObj->price * $qty) + self::std_round(($itemObj->price * $markup->markup_value_3_serving) / 100, 2) * $qty;
		}
		else if ($itemObj->pricing_type == CMenuItem::FULL)
		{
			if ($itemObj->menu_item_category_id == 9)
			{
				if (empty($markup->markup_value_sides))
				{
					$markup->markup_value_sides = 0.0;
				}

				return ($itemObj->price * $qty) + self::std_round(($itemObj->price * $markup->markup_value_sides) / 100, 2) * $qty;
			}
			else
			{
				if (empty($markup->markup_value_6_serving))
				{
					$markup->markup_value_6_serving = 0.0;
				}

				return ($itemObj->price * $qty) + self::std_round(($itemObj->price * $markup->markup_value_6_serving) / 100, 2) * $qty;
			}
		}
		else if ($itemObj->pricing_type == CMenuItem::TWO)
		{
			if (empty($markup->markup_value_2_serving))
			{
				$markup->markup_value_2_serving = 0.0;
			}

			return ($itemObj->price * $qty) + self::std_round(($itemObj->price * $markup->markup_value_2_serving) / 100, 2) * $qty;
		}
		else if ($itemObj->pricing_type == CMenuItem::FOUR)
		{
			if (empty($markup->markup_value_4_serving))
			{
				$markup->markup_value_4_serving = 0.0;
			}

			return ($itemObj->price * $qty) + self::std_round(($itemObj->price * $markup->markup_value_4_serving) / 100, 2) * $qty;
		}

		return $itemObj->price * $qty;
	}

	/**
	 *    $itemObj is a COrder_item that has a new member added : pricing_type
	 *  $totalNumberServings is the total number of servings per order
	 */
	static function getItemDiscountedSubtotal($markup, $itemObj, $qty, $totalNumberServings)
	{
		if ($itemObj->pricing_type == CMenuItem::INTRO)
		{
			return $itemObj->price * $qty;
		}

		$numServingsPerDinner = 6;
		if ($itemObj->pricing_type == CMenuItem::HALF)
		{
			$numServingsPerDinner = 3;
		}

		$scaleFactor = 1.0;

		if ($markup && $markup->markup_type == CMarkUp::PERCENTAGE)
		{
			$scaleFactor = ($markup->markup_value / 100) + 1.0;
		}

		if (!$markup)
		{
			$markedUpPrice = $itemObj->price;
		}
		else
		{
			$markedUpPrice = self::getItemMarkupSubtotal($markup, $itemObj, 1);
		}

		if ($totalNumberServings >= 72)
		{
			$discountedPrice = $markedUpPrice - round(1.29 * $scaleFactor * $numServingsPerDinner, 2);
		}
		else if ($totalNumberServings >= 66)
		{
			$discountedPrice = $markedUpPrice - round(1.08 * $scaleFactor * $numServingsPerDinner, 2);
		}
		else if ($totalNumberServings >= 60)
		{
			$discountedPrice = $markedUpPrice - round(0.86 * $scaleFactor * $numServingsPerDinner, 2);
		}
		else if ($totalNumberServings >= 54)
		{
			$discountedPrice = $markedUpPrice - round(0.65 * $scaleFactor * $numServingsPerDinner, 2);
		}
		else if ($totalNumberServings >= 48)
		{
			$discountedPrice = $markedUpPrice - round(0.44 * $scaleFactor * $numServingsPerDinner, 2);
		}
		else if ($totalNumberServings >= 42)
		{
			$discountedPrice = $markedUpPrice - round(0.23 * $scaleFactor * $numServingsPerDinner, 2);
		}
		else
		{
			$discountedPrice = $markedUpPrice;
		}

		return $discountedPrice * $qty;
	}

	/*
	*	 If Editing is true pass in the markup id stored with the order
	*/
	private function applyPromo($editing = false)
	{
		if (!$this->promo)
		{
			return;
		}

		CLog::Assert(!$this->storeAndUserSupportPlatePoints, "A promo code was added to a PLATEPOINTS order");

		$markup = false;
		if ($editing)
		{
			$markup = !empty($this->markup_id) ? $this->markup_id : false;
		}

		$value = $this->promo->calculate($this, $markup);

		if ($value !== false)
		{
			$this->promo_code_discount_total = $value;
		}
		else
		{
			$this->promo_code_id = null;
			// this was an error in the store view.. LMH 12/05/06
		}
	}

	/*
	*	 If Editing is true pass in the markup id stored with the order
	*/
	protected function applyCoupon($editing = false)
	{
		if (!$this->coupon)
		{
			return;
		}

		$markup = false;
		if ($editing)
		{
			$markup = !empty($this->markup_id) ? $this->markup_id : false;
		}

		$value = $this->coupon->calculate($this, $markup);

		if ($value !== false)
		{
			$this->coupon_code_discount_total = $value;
		}
		else
		{
			$this->coupon_code_id = null;
		}
	}

	private function applyPremium()
	{
		$this->subtotal_premium_markup = 0;
		$this->premium_id = null;

		return;
		/*
		 *

		 CES 10/8/07 No longer supported

		if ( !$this->session )
			throw new exception('session not set');

		if ( $this->menu_items_total_count > 11 || $this->isIntroOrder())
			return;

		if ( ($this->menu_items_total_count != 6) && !($this->order_type == self::DIRECT))
			throw new exception($this->menu_items_total_count.' items not permitted on a quick 6 day');



		// [CES 1/12/06] legal to apply apply the premium on any order of less than 12 items placed from the direct order UI
		if ( $this->applyPremium && ($this->session->session_type == CSession::QUICKSIX || $this->order_type == self::DIRECT)) {

			//find premium
			$premium = DAO_CFactory::create('premium');
			$premium->store_id = $this->session->store_id;
			$num = $premium->find(true);
			if ( $num && ($num > 1) ) {
				throw new exception('uh oh, more than one premium is set');
			} else if ( !$num ) {
				return;
			}

			$this->premium_id = $premium->id;

			switch( $premium->premium_type ) {
				case CPremium::FLAT:
					$this->subtotal_premium_markup = $premium->premium_value;
					break;

				case CPremium::PERCENTAGE:
					//apply premium after markup
					$this->subtotal_premium_markup = self::std_round((($this->subtotal_home_store_markup + $this->subtotal_menu_items)*$premium->premium_value)/100.00, 2);
					break;

				default:
				case CPremium::DISTRIBUTE:
					throw new exception('not yet implemented');
					break;
			}
		}
		*/
	}

	private function getSidesTotal()
	{

		$sidesTotal = 0;
		$markup = $this->mark_up;

		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				list($qty, $mi_obj) = $item;

				if (isset($mi_obj->parentItemId) && isset($this->items[$mi_obj->parentItemId]))
				{
					//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
					$qty -= $mi_obj->bundleItemCount;

					if ($qty <= 0)
					{
						continue;
					}
				}

				if ($mi_obj->is_side_dish)
				{
					if (isset($mi_obj->override_price))
					{
						$sidesTotal += ($mi_obj->override_price * $qty);
					}
					else
					{
						if ($this->family_savings_discount_version == 2)
						{
							$sidesTotal += self::getItemMarkupMultiSubtotal($markup, $mi_obj, $qty);
						}
						else
						{
							$sidesTotal += self::getItemMarkupSubtotal($markup, $mi_obj, $qty);
						}
					}
				}
			}
		}

		return $sidesTotal;
	}

	private function getMenuAddonsTotal()
	{

		$menuAddonTotal = 0;
		$markup = $this->mark_up;

		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				list($qty, $mi_obj) = $item;

				if (isset($mi_obj->parentItemId) && isset($this->items[$mi_obj->parentItemId]))
				{
					//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
					$qty -= $mi_obj->bundleItemCount;

					if ($qty <= 0)
					{
						continue;
					}
				}

				if ($mi_obj->is_menu_addon || $mi_obj->is_chef_touched)
				{
					if (isset($mi_obj->override_price))
					{
						$menuAddonTotal += ($mi_obj->override_price * $qty);
					}
					else
					{
						if ($this->family_savings_discount_version == 2)
						{
							$menuAddonTotal += self::getItemMarkupMultiSubtotal($markup, $mi_obj, $qty);
						}
						else
						{
							$menuAddonTotal += self::getItemMarkupSubtotal($markup, $mi_obj, $qty);
						}
					}
				}
			}
		}

		return $menuAddonTotal;
	}

	private function getKidsChoiceTotal()
	{

		$kidsChoiceTotal = 0;
		$markup = $this->mark_up;

		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				list($qty, $mi_obj) = $item;

				if (isset($mi_obj->parentItemId) && isset($this->items[$mi_obj->parentItemId]))
				{
					//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
					$qty -= $mi_obj->bundleItemCount;

					if ($qty <= 0)
					{
						continue;
					}
				}

				if ($mi_obj->is_kids_choice)
				{
					if (isset($mi_obj->override_price))
					{
						$kidsChoiceTotal += ($mi_obj->override_price * $qty);
					}
					else
					{
						if ($this->family_savings_discount_version == 2)
						{
							$kidsChoiceTotal += self::getItemMarkupMultiSubtotal($markup, $mi_obj, $qty);
						}
						else
						{
							$kidsChoiceTotal += self::getItemMarkupSubtotal($markup, $mi_obj, $qty);
						}
					}
				}
			}
		}

		return $kidsChoiceTotal;
	}

	private function getBundlesTotal()
	{

		$bundlesTotal = 0;
		$markup = $this->mark_up;

		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				list($qty, $mi_obj) = $item;

				if ($mi_obj->is_bundle)
				{
					if (isset($mi_obj->override_price))
					{
						$bundlesTotal += ($mi_obj->override_price * $qty);
					}
					else
					{
						if ($this->family_savings_discount_version == 2)
						{
							$bundlesTotal += self::getItemMarkupMultiSubtotal($markup, $mi_obj, $qty);
						}
						else
						{
							$bundlesTotal += self::getItemMarkupSubtotal($markup, $mi_obj, $qty);
						}
					}
				}
			}
		}

		return $bundlesTotal;
	}

	/**
	 * Apply to total calculation
	 */
	private function applyPreferred()
	{
		$this->user_preferred_discount_total = 0;
		//	$this->exclude_from_reports = 0;

		if (!isset($this->preferred))
		{
			$this->getPreferredObj();
			if (!isset($this->preferred))
			{
				return;
			}
		}

		if (isset($this->dream_rewards_level) && $this->dream_rewards_level > 0)
		{
			return;
		}

		$preferred = $this->preferred;

		$preferredCapacity = CUserPreferred::hasCapacityBeenMet($this);
		if ($preferredCapacity->hasBeenMet)
		{
			return;
		}

		// 7-9-2007 :: DBENSON :: david.benson@dreamdinners.com
		//
		// modified to deduct the volume reward from the base (needs code review)
		// =============================================================================
		//$base = $this->subtotal_menu_items + $this->subtotal_home_store_markup - $this->family_savings_discount - $this->promo_code_discount_total;
		$base = $this->subtotal_menu_items;
		$base += $this->subtotal_home_store_markup;
		$base -= $this->family_savings_discount;
		$base -= $this->promo_code_discount_total;
		$base -= $this->volume_discount_total;
		$base -= $this->subtotal_menu_item_mark_down;

		$sidesTotal = $this->getSidesTotal();
		$kidsChoiceTotal = $this->getKidsChoiceTotal();
		$menuAddonsTotal = $this->getMenuAddonsTotal(); // includes ChefTouched Total

		$exclusionList = array(
			5004,
			5005,
			5051,
			5052
		);
		$exclusionTotal = 0;

		// CES 9/20/2010: Bundles are now to be subject to the PUD
		$bundlesTotal = 0; //$this->getBundlesTotal();

		$markup = $this->mark_up;

		switch ($preferred->preferred_type)
		{
			case CUserPreferred::FLAT:

				$qtyFull = 0;
				$qtyHalf = 0;
				$countIncludedItems = 0;
				$totalOrderItemCount = 0;

				if ($this->items)
				{
					$items = $this->items;

					// sort items by price high to low
					usort($items, function ($a, $b) {
						return $a[1]->override_price < $b[1]->override_price;
					});

					foreach ($items as $itemArray)
					{
						$qty = $itemArray[0];
						$itemObj = $itemArray[1];
						$totalOrderItemCount += $qty;

						// skip any excluded items
						if (in_array($itemObj->id, $exclusionList))
						{

							if (isset($itemObj->override_price))
							{
								$exclusionTotal += ($itemObj->override_price * $qty);
							}
							else
							{
								$exclusionTotal += self::getItemMarkupMultiSubtotal($markup, $itemObj, $qty);
							}

							continue;
						}

						// if sides aren't allowed, skip
						if (!$preferred->include_sides && ($itemObj->is_side_dish || $itemObj->is_kids_choice || $itemObj->is_menu_addon || $itemObj->is_chef_touched || $itemObj->menu_item_category_id == 9))
						{
							if (isset($itemObj->override_price))
							{
								$exclusionTotal += ($itemObj->override_price * $qty);
							}
							else
							{
								$exclusionTotal += self::getItemMarkupMultiSubtotal($markup, $itemObj, $qty);
							}

							continue;
						}

						// only one item can be promo
						if (isset($itemObj->isPromo) && !empty($itemObj->isPromo))
						{
							$qty--;
						}

						// only one item can be free meal
						if (isset($itemObj->isFreeMeal) && !empty($itemObj->isFreeMeal))
						{
							$qty--;
						}

						if (!empty($qty))
						{
							if ($countIncludedItems < $preferredCapacity->remainingObj->countRemaining || $preferredCapacity->remainingObj->type == CUserPreferred::PREFERRED_CAP_NONE)
							{
								if ($qty < $preferredCapacity->remainingObj->countRemaining)
								{
									if ($itemObj->pricing_type == CMenuItem::HALF)
									{
										$qtyHalf += $qty;
										$countIncludedItems += $qty;
									}
									else
									{
										$qtyFull += $qty;
										$countIncludedItems += $qty;
									}
								}
								else
								{
									if ($itemObj->pricing_type == CMenuItem::HALF)
									{
										$qtyHalf += $preferredCapacity->remainingObj->countRemaining - $countIncludedItems;
										$countIncludedItems += $qtyHalf;
									}
									else
									{
										$qtyFull += $preferredCapacity->remainingObj->countRemaining - $countIncludedItems;
										$countIncludedItems += $qtyFull;
									}
								}
							}
						}
					}
				}

				if ($countIncludedItems < $totalOrderItemCount)
				{
					$countIncludedItems = 0;
					foreach ($items as $itemArray)
					{
						$itemObj = $itemArray[1];
						if (!$preferred->include_sides && ($itemObj->is_side_dish || $itemObj->is_kids_choice || $itemObj->is_menu_addon || $itemObj->is_chef_touched || $itemObj->menu_item_category_id == 9))
						{
							continue;
						}

						$qty = $itemArray[0];
						for ($i = 0; $i < $qty; $i++)
						{
							if ($countIncludedItems < $preferredCapacity->remainingObj->countRemaining)
							{
								//add this item as a flat fee
								if ($itemObj->pricing_type == CMenuItem::HALF)
								{
									$halfFlatAmount = $preferred->preferred_value / 2;
									$preferredTotal += $halfFlatAmount;
								}
								else
								{
									$preferredTotal += $preferred->preferred_value;
								}
								$countIncludedItems++;
							}
							else
							{
								//if capcity is met then add the full item price
								if ($preferred->include_sides && ($itemObj->is_side_dish || $itemObj->is_menu_addon || $itemObj->is_chef_touched) && (($itemObj->menu_item_category_id == 4 && $itemObj->is_store_special > 0) || $itemObj->menu_item_category_id == 9))
								{
									if (is_null($itemObj->override_price))
									{
										if (!isset($markup))
										{
											$markup = $this->getStoreObj()->getMarkUpMultiObj($this->getSessionObj()->menu_id);
										}
										$preferredTotal += CTemplate::moneyFormat(COrders::getItemMarkupMultiSubtotal($markup, $itemObj, 1));
									}
									else
									{
										$preferredTotal += $itemObj->override_price;
									}
								}
								else if ($itemObj->menu_item_category_id < 5)
								{

									$preferredTotal += $itemObj->override_price;
								}
							}
						}
					}
				}
				else
				{
					$preferredTotal = $qtyFull * $preferred->preferred_value;
					$preferredTotal += self::std_round($qtyHalf * ($preferred->preferred_value / 2));
				}

				$preferredTotal = self::std_round($preferredTotal);
				$this->user_preferred_discount_total = $base;
				$this->user_preferred_discount_total -= $preferredTotal;
				if (!$preferred->include_sides)
				{
					$this->user_preferred_discount_total -= $bundlesTotal;
				}

				$this->user_preferred_discount_total -= $exclusionTotal;
				$this->user_preferred_discount_cap_type = CUserPreferred::PREFERRED_CAP_ITEMS;
				$this->user_preferred_discount_cap_applied = $countIncludedItems;

				break;

			case CUserPreferred::PERCENTAGE:

				$discountTotalObj = CUserPreferred::calculateDiscountTotal($this, $preferredCapacity, $exclusionList);

				if (is_null($discountTotalObj))
				{
					$totalAvailableToDiscount = $base;
					if (!$preferred->include_sides)
					{
						$totalAvailableToDiscount -= $sidesTotal;
						$totalAvailableToDiscount -= $kidsChoiceTotal;
						$totalAvailableToDiscount -= $menuAddonsTotal;
						$totalAvailableToDiscount -= $bundlesTotal;
					}

					$this->user_preferred_discount_total = self::std_round(($totalAvailableToDiscount * $preferred->preferred_value) / 100, 2);
				}
				else
				{
					if ($discountTotalObj->wasApplied)
					{
						$totalAvailableToDiscount = $discountTotalObj->sumIncludedCost;
						$this->user_preferred_discount_cap_type = $preferred->preferred_cap_type;
						$this->user_preferred_discount_cap_applied = $discountTotalObj->countIncluded;

						$this->user_preferred_discount_total = self::std_round(($totalAvailableToDiscount * $preferred->preferred_value) / 100, 2);
					}
				}

				break;

			default:
				throw new Exception('unknown preferred discount type');
				break;
		}

		if ($this->user_preferred_discount_total < 0)
		{
			$this->user_preferred_discount_total = 0;
		}
	}

	function getProductSubTotalsByTaxCategory()
	{

		if (!$this->products)
		{
			return array(
				$this->subtotal_products,
				0
			);
		}

		$nonFoodSubtotal = $this->subtotal_products;
		$enrollmentSubtotal = 0;
		foreach ($this->products as $id => $thisProduct)
		{
			if ($thisProduct[1]->tax_category == 'ENROLLMENT')
			{
				$nonFoodSubtotal -= $thisProduct[1]->price;
				$enrollmentSubtotal += $thisProduct[1]->price;
			}
		}

		return array(
			$nonFoodSubtotal,
			$enrollmentSubtotal
		);
	}

	function applyTax($fee_portion_of_points_credit = 0, $hasServiceFeeCoupon = false, $hasDeliveryFeeCoupon = false)
	{
		$this->subtotal_all_taxes = 0;
		$this->subtotal_food_sales_taxes = 0;
		$this->subtotal_sales_taxes = 0;
		$this->subtotal_service_tax = 0;
		$this->subtotal_delivery_tax = 0;
		$this->subtotal_bag_fee_tax = 0;

		if ($this->sales_tax && ($this->subtotal_food_items_adjusted || $this->subtotal_products))
		{
			$foodTax = $this->sales_tax->food_tax;
			$productTax = $this->sales_tax->total_tax;
			$serviceTax = $this->sales_tax->other1_tax;
			$enrollmentTax = $this->sales_tax->other2_tax;
			$deliveryTax = $this->sales_tax->other3_tax;
			$bagFeeTax = $this->sales_tax->other4_tax;

			$this->subtotal_food_sales_taxes = self::std_round($this->subtotal_food_items_adjusted * $foodTax / 100, 2);

			// the subtotal_products can be subject to multiple tax categories so we must break down the subtotal by category
			// current categories are nonfoodsales and enrollment.
			list($nonFoodSubtotal, $enrollmentSubtotal) = $this->getProductSubTotalsByTaxCategory();
			$this->subtotal_sales_taxes = self::std_round($nonFoodSubtotal * $productTax / 100, 2) + self::std_round($enrollmentSubtotal * $enrollmentTax / 100, 2);

			if (!$hasServiceFeeCoupon)
			{
				$serviceTaxAmount = $this->subtotal_service_fee + $this->subtotal_meal_customization_fee;
				$this->subtotal_service_tax = self::std_round(($serviceTaxAmount - $fee_portion_of_points_credit) * $serviceTax / 100, 2);
			}

			if ($deliveryTax > 0 && $this->subtotal_delivery_fee > 0)
			{
				$delFee = $this->subtotal_delivery_fee;
				if ($hasDeliveryFeeCoupon)
				{
					$discount = $this->coupon->calculate($this);
					if (!is_null($discount) && $discount > 0)
					{
						$delFee = $this->subtotal_delivery_fee - $discount;
					}
				}

				$this->subtotal_delivery_tax = self::std_round($delFee * $deliveryTax / 100, 2);
			}

			$this->subtotal_bag_fee_tax = self::std_round($this->subtotal_bag_fee * $bagFeeTax / 100, 2);

			$this->subtotal_all_taxes = $this->subtotal_food_sales_taxes + $this->subtotal_sales_taxes + $this->subtotal_service_tax + $this->subtotal_delivery_tax + $this->subtotal_bag_fee_tax;
		}
		else
		{
			//TODO: throw ?
		}
	}

	function insertEditedItems($sessionIsInPast = true, $removeItemsFromInventory = true)
	{

		if (!isset($this->family_savings_discount))
		{
			$this->family_savings_discount = 0;
		}

		$items = $this->getItems();

		$pricing_method = 'USE_CURRENT';
		$useCurrent = true;
		$full_price = 0;
		$half_price = 0;

		$toddItems = array();
		if ($this->session)
		{

			if ($this->session->session_type == 'TODD')
			{

				$toddItems = CMenu::getTODDMenuMenbuItemIDsForSession($this->session->id);

				$todd_props = DAO_CFactory::create('session_properties');
				$todd_props->session_id = $this->session->id;
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
		}

		$menu_id = $this->session->menu_id;

		//add order items
		if ($items)
		{
			foreach ($items as $item)
			{
				list($qty, $DAO_menu_item) = $item;

				if ($qty && $DAO_menu_item->id)
				{
					$DAO_order_item = DAO_CFactory::create('order_item', true);
					$DAO_order_item->menu_item_id = $DAO_menu_item->id;
					$DAO_order_item->order_id = $this->id;
					$DAO_order_item->item_count = $qty;

					$hasMarkdown = false;
					$hasLTDMenuItem = false;

					if (isset($DAO_menu_item->markdown_id) && is_numeric($DAO_menu_item->markdown_id) && $DAO_menu_item->markdown_id > 0)
					{
						$DAO_order_item->menu_item_mark_down_id = $DAO_menu_item->markdown_id;
						$hasMarkdown = true;
					}

					if (!empty($DAO_menu_item->order_item_ltd_menu_item))
					{
						$hasLTDMenuItem = true;
					}

					if (!empty($DAO_menu_item->parentItemId))
					{
						$DAO_order_item->parent_menu_item_id = $DAO_menu_item->parentItemId;
						$DAO_order_item->bundle_item_count = $DAO_menu_item->bundleItemCount;
					}

					if ($useCurrent || !array_key_exists($DAO_menu_item->id, $toddItems))
					{
						if ($this->family_savings_discount_version == 2)
						{
							if (isset($DAO_menu_item->store_price))
							{
								if ($DAO_menu_item->DAO_store->supportsLTDRoundup())
								{
									$DAO_order_item->discounted_subtotal = (!empty($DAO_menu_item->DAO_recipe->ltd_menu_item_value)) ? $DAO_menu_item->store_price * $qty : null;
								}
								$DAO_order_item->sub_total = $DAO_menu_item->store_price_no_ltd * $qty;
								$DAO_order_item->pre_mark_up_sub_total = $DAO_menu_item->store_price * $qty;

								if (!empty($this->bundle->bundle_type) && $this->bundle->bundle_type == CBundle::TV_OFFER && $DAO_menu_item->DAO_store->supportsLTDRoundup())
								{
									// remove one ltd from order
									$DAO_order_item->sub_total -= $DAO_menu_item->ltd_menu_item_value;
								}
							}
							else if (isset($DAO_menu_item->override_price))
							{
								$orgOverridePrice = $DAO_menu_item->override_price;
								if ($hasLTDMenuItem)
								{
									$DAO_order_item->discounted_subtotal = $DAO_menu_item->override_price * $qty;
									$orgStoreOverridePrice = $DAO_menu_item->override_price - $DAO_menu_item->ltd_menu_item_value;
									$DAO_order_item->sub_total = $orgStoreOverridePrice * $qty;
								}
								else
								{
									$DAO_order_item->sub_total = $DAO_menu_item->override_price * $qty;
								}

								$DAO_order_item->pre_mark_up_sub_total = $DAO_menu_item->store_price * $qty;
							}
							else
							{
								$DAO_order_item->sub_total = self::getItemMarkupMultiSubtotal($this->mark_up, $DAO_menu_item, $qty);
								$DAO_order_item->pre_mark_up_sub_total = $DAO_menu_item->store_price * $qty;
							}

							$normalPrice = $DAO_order_item->sub_total / $qty;

							if ($hasMarkdown)
							{
								$DAO_order_item->discounted_subtotal = ($normalPrice - self::std_round(($normalPrice * $DAO_menu_item->markdown_value) / 100, 2)) * $qty;
							}
						}
					}
					else
					{
						if ($pricing_method == 'OVERRIDE')
						{
							$thisPrice = ($DAO_menu_item->pricing_type == 'HALF' ? $half_price : $full_price);
							if (self::isPriceGreaterThan($thisPrice, $DAO_menu_item->price))
							{
								$preMarkupPrice = $DAO_menu_item->price;
							}
							else
							{
								$preMarkupPrice = $thisPrice;
							}

							$DAO_order_item->sub_total = $thisPrice * $qty;
							$DAO_order_item->pre_mark_up_sub_total = $preMarkupPrice * $qty;
						}
						else // FREE
						{
							$DAO_order_item->sub_total = 0;
							$DAO_order_item->pre_mark_up_sub_total = 0;
						}
					}

					// mark order items that belong to a bundle with a bundle_id
					if (!empty($this->bundle_id) && isset($this->bundle) && array_key_exists($DAO_order_item->menu_item_id, $this->bundle->items) && $this->bundle->items[$DAO_order_item->menu_item_id]['chosen'])
					{
						$DAO_order_item->bundle_id = $this->bundle_id;
					}

					if (!$DAO_order_item->insert())
					{
						throw new Exception ('could not insert order item');
					}

					try
					{

						$servingQty = $DAO_menu_item->servings_per_item;

						if ($servingQty == 0)
						{
							$servingQty = 6;
						}

						if ($DAO_menu_item->is_chef_touched)
						{
							$servingQty = 1;
						}

						$servingQty *= $qty;
						// INVENTORY TOUCH POINT 3

						if ($removeItemsFromInventory)
						{
							//subtract from inventory
							$invItem = DAO_CFactory::create('menu_item_inventory');
							$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold +  " . " $servingQty where mii.recipe_id = {$DAO_menu_item->recipe_id}
								    and mii.store_id = {$this->store_id} and mii.menu_id = $menu_id and mii.is_deleted = 0");
						}
					}
					catch (exception $exc)
					{
						// don't allow a problem here to fail the order
						//log the problem
						CLog::RecordException($exc);

						$debugStr = "INV_CONTROL ISSUE- Order: " . $this->id . " | Item: " . $DAO_order_item->menu_item_id . " | Store: " . $this->store_id;
						CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
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

					$DAO_order_item = DAO_CFactory::create('order_item');
					$DAO_order_item->product_id = $product->id;
					$DAO_order_item->order_id = $this->id;
					$DAO_order_item->item_count = $qty;

					if ($product->item_type == 'ENROLLMENT')
					{
						$DAO_order_item->sub_total = $product->price * $qty;
					}

					/*
					 *
					 *  Only worry about subscriptions for now which do not respect markup
					else
					{
						$orderItem->sub_total = self::getItemMarkupMultiSubtotal($this->mark_up, $product, $qty);
					}
					*/

					$DAO_order_item->pre_mark_up_sub_total = $product->price * $qty;

					if (!$DAO_order_item->insert())
					{
						throw new Exception ('could not insert order item');
					}
				}
			}
		}
	}

	/*
	 *
	 *  The order is being activated but the order_items are already inserted
	 *
	 */
	function removeInitialInventory($menu_id)
	{

		//add order items
		if ($this->items)
		{
			foreach ($this->items as $item)
			{

				list($qty, $menu_item) = $item;

				if ($qty && $menu_item->id)
				{

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

							// INVENTORY TOUCH POINT 4
							//subtract from inventory
							$invItem = DAO_CFactory::create('menu_item_inventory');
							$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold +  " . " $servingQty where mii.recipe_id = {$menu_item->recipe_id} 
							                    and mii.store_id = {$this->store_id} and mii.menu_id = $menu_id and mii.is_deleted = 0");
						}
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

	function handleItemsInserts()
	{
		$useCurrent = true;
		$pricing_method = 'USE_CURRENT';
		$full_price = 0;
		$half_price = 0;

		$toddItems = array();

		if ($this->session)
		{

			if ($this->session->session_type == 'TODD')
			{
				$toddItems = CMenu::getTODDMenuMenbuItemIDsForSession($this->session->id);

				$todd_props = DAO_CFactory::create('session_properties');
				$todd_props->session_id = $this->session->id;
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
		}

		$menu_id = $this->session->menu_id;

		$sessionIsInPast = $this->session->isInThePast();

		//add order items
		if ($this->items)
		{
			foreach ($this->items as $item)
			{
				list($qty, $DAO_menu_item) = $item;

				if ($qty && $DAO_menu_item->id)
				{
					$DAO_order_item = DAO_CFactory::create('order_item', true);
					$DAO_order_item->menu_item_id = $DAO_menu_item->id;
					$DAO_order_item->order_id = $this->id;
					$DAO_order_item->item_count = $qty;

					if (!empty($DAO_menu_item->parentItemId))
					{
						$DAO_order_item->parent_menu_item_id = $DAO_menu_item->parentItemId;
						$DAO_order_item->bundle_item_count = $DAO_menu_item->bundleItemCount;
					}

					$hasMarkdown = false;
					$hasLTDMenuItem = false;

					if (!empty($DAO_menu_item->markdown_id) && is_numeric($DAO_menu_item->markdown_id))
					{
						$DAO_order_item->menu_item_mark_down_id = $DAO_menu_item->markdown_id;
						$hasMarkdown = true;
					}

					if (!empty($this->store->supports_ltd_roundup) && !empty($DAO_menu_item->ltd_menu_item_value) && is_numeric($DAO_menu_item->ltd_menu_item_value))
					{
						$hasLTDMenuItem = true;
					}

					if ($useCurrent || !array_key_exists($DAO_menu_item->id, $toddItems))
					{
						if (isset($DAO_menu_item->store_price))
						{
							if ($DAO_menu_item->DAO_store->supportsLTDRoundup())
							{
								$DAO_order_item->discounted_subtotal = (!empty($DAO_menu_item->DAO_recipe->ltd_menu_item_value)) ? $DAO_menu_item->store_price * $qty : null;
							}
							$DAO_order_item->sub_total = $DAO_menu_item->store_price_no_ltd * $qty;
							$DAO_order_item->pre_mark_up_sub_total = $DAO_menu_item->store_price * $qty;
						}
						else if (isset($DAO_menu_item->override_price))
						{
							$orgOverridePrice = $DAO_menu_item->override_price;
							if ($hasLTDMenuItem)
							{
								$DAO_order_item->discounted_subtotal = $DAO_menu_item->override_price * $qty;
								$orgStoreOverridePrice = $DAO_menu_item->override_price - $DAO_menu_item->ltd_menu_item_value;
								$DAO_order_item->sub_total = $orgStoreOverridePrice * $qty;
							}
							else
							{
								$DAO_order_item->sub_total = $DAO_menu_item->override_price * $qty;
							}

							$DAO_order_item->pre_mark_up_sub_total = $DAO_menu_item->store_price_no_ltd * $qty;
						}
						else
						{
							$DAO_order_item->sub_total = self::getItemMarkupMultiSubtotal($this->mark_up, $DAO_menu_item, $qty);
							$DAO_order_item->pre_mark_up_sub_total = $DAO_menu_item->store_price * $qty;
						}

						$normalPrice = $DAO_order_item->sub_total / $qty;

						if ($hasMarkdown)
						{
							$DAO_order_item->discounted_subtotal = ($normalPrice - self::std_round(($normalPrice * $DAO_menu_item->markdown_value) / 100, 2)) * $qty;
						}
					}
					else
					{
						if ($pricing_method == 'OVERRIDE')
						{
							$thisPrice = ($DAO_menu_item->pricing_type == 'HALF' ? $half_price : $full_price);
							if (self::isPriceGreaterThan($thisPrice, $DAO_menu_item->price))
							{
								$preMarkupPrice = $DAO_menu_item->price;
							}
							else
							{
								$preMarkupPrice = $thisPrice;
							}

							$DAO_order_item->sub_total = $thisPrice * $qty;
							$DAO_order_item->pre_mark_up_sub_total = $preMarkupPrice * $qty;
						}
						else // FREE
						{
							$DAO_order_item->sub_total = 0;
							$DAO_order_item->pre_mark_up_sub_total = 0;
						}
					}

					// mark order items that belong to a bundle with a bundle_id
					if (!empty($this->bundle_id) && isset($this->bundle) && array_key_exists($DAO_order_item->menu_item_id, $this->bundle->items) && $this->bundle->items[$DAO_order_item->menu_item_id]['chosen'])
					{
						$DAO_order_item->bundle_id = $this->bundle_id;
					}

					if (!$DAO_order_item->insert())
					{
						throw new Exception ('could not insert order item');
					}

					try
					{
						if (!empty($DAO_menu_item->recipe_id))
						{
							$servingQty = $DAO_menu_item->servings_per_item;

							if ($servingQty == 0)
							{
								$servingQty = 6;
							}

							if ($DAO_menu_item->is_chef_touched)
							{
								$servingQty = 1;
							}

							$servingQty *= $qty;

							// INVENTORY TOUCH POINT 7

							//subtract from inventory
							$invItem = DAO_CFactory::create('menu_item_inventory');
							$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold +  " . " $servingQty where mii.recipe_id = {$DAO_menu_item->recipe_id} and 
							                mii.store_id = {$this->store_id} and mii.menu_id = $menu_id and mii.is_deleted = 0");
						}
					}
					catch (exception $exc)
					{
						// don't allow a problem here to fail the order
						//log the problem
						CLog::RecordException($exc);
						$debugStr = "INV_CONTROL ISSUE- Order: " . $this->id . " | Item: " . $DAO_order_item->menu_item_id . " | Store: " . $this->store_id;
						CLog::RecordNew(CLog::ERROR, $debugStr, "", "", true);
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

					$DAO_order_item = DAO_CFactory::create('order_item');
					$DAO_order_item->product_id = $product->id;
					$DAO_order_item->order_id = $this->id;
					$DAO_order_item->item_count = $qty;

					//TODO: getItemMarkupMultiSubtotal really is not designed for prodcuts
					// consider a new function once markup rules for products are established
					if ($product->item_type == 'ENROLLMENT')
					{
						$DAO_order_item->sub_total = $product->price * $qty;
					}
					else
					{
						$DAO_order_item->sub_total = self::getItemMarkupMultiSubtotal($this->mark_up, $product, $qty);
					}

					$DAO_order_item->pre_mark_up_sub_total = $product->price * $qty;

					if (!$DAO_order_item->insert())
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

	/**
	 * Overridden to do insert of order_items and booking record
	 */
	function insert($inserting_saved_order = false)
	{

		if (!isset($this->family_savings_discount))
		{
			$this->family_savings_discount = 0;
		}

		if (empty($this->volume_discount_id))
		{
			$this->volume_discount_id = 'null';
		}

		if (empty($this->mark_up_multi_id))
		{
			$this->mark_up_multi_id = 'null';
		}

		if (empty($this->inviting_user_id))
		{
			$this->inviting_user_id = 'null';
		}

		if (is_null($this->dream_rewards_level))
		{
			$this->dream_rewards_level = 0;
		}

		// Force menu_program_id to 1 if it is zero
		if ($this->menu_program_id === 0 || $this->menu_program_id === '0' || empty($this->menu_program_id))
		{
			$this->menu_program_id = 1;
		}

		//add order
		$rtn = parent::insert();

		if (!$rtn)
		{
			throw new Exception('order not inserted');
		}

		if ($inserting_saved_order)
		{
			return true;
		}

		$this->handleItemsInserts();

		return $rtn;
	}

	function can_customer_reschedule(&$cantRescheduleReason, $timezone_id, $session_start, $session_type, $booking_status, $session_type_subtype)
	{
		$retVal = false;

		$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($timezone_id);
		$thisMorning = mktime(0, 0, 0, date("n", $now), date("j", $now), date("Y", $now));
		$cutoff = $thisMorning + (86400 * 5);

		if (strtotime($session_start) > $cutoff)
		{
			$retVal = true;
		}
		else
		{
			$cantRescheduleReason = "Orders that are 5 days away or less cannot be rescheduled.";
			$retVal = false;
		}

		if (!empty($this->session_discount_id) && is_numeric($this->session_discount_id))
		{
			$cantRescheduleReason = "Orders placed on a discounted session can only be rescheduled by the store manager. Please Contact the store to reschedule this order.";
			$retVal = false;
		}

		if ($this->isRemotePickup())
		{
			$cantRescheduleReason = "Community pick up orders must be rescheduled by your store.";
			$retVal = false;
		}

		if ($this->isDelivery())
		{
			$cantRescheduleReason = "Delivery Orders must be rescheduled by your store.";
			$retVal = false;
		}

		if ($booking_status == CBooking::CANCELLED)
		{
			$cantRescheduleReason = "Cancelled orders can not be rescheduled.";
			$retVal = false;
		}

		if ($session_type == CSession::DREAM_TASTE)
		{
			$cantRescheduleReason = "Meal Prep Workshop orders cannot be rescheduled.";
			$retVal = false;
		}
		else if ($session_type == CSession::FUNDRAISER)
		{
			$cantRescheduleReason = "Fundraiser orders cannot be rescheduled.";
			$retVal = false;
		}
		else if ($session_type == CSession::DELIVERED)
		{
			$cantRescheduleReason = "Delivered orders cannot be rescheduled.";
			$retVal = false;
		}
		else if ($session_type == CSession::TODD)
		{
			$cantRescheduleReason = "Taste of Dream Dinners orders cannot be rescheduled.";
			$retVal = false;
		}

		return $retVal;
	}

	function can_reschedule($shippingInfoObj = false, $target_session_id = false)
	{
		$Booking = DAO_CFactory::create('booking');
		$Booking->order_id = $this->id;
		$Booking->whereAdd("status = 'ACTIVE'");

		if (!$Booking->find(true))
		{
			return false;
		}
		$Session = DAO_CFactory::create('session');
		$Session->id = $Booking->session_id;
		if (!$Session->find(true))
		{
			CLog::Record('N_NOTICE:: Session not found in COrders::can_reschedule');

			return false;
		}

		$Store = DAO_CFactory::create('store');
		$Store->id = $Session->store_id;
		if (!$Store->find(true))
		{
			CLog::Record('N_NOTICE:: Store not found in COrders::can_reschedule');

			return false;
		}

		// After the 6th a session in last month cannot be rescheduled
		if ($Session->isReschedulingLockedOut($Store))
		{
			return false;
		}

		// CES 06/06/07 - allow orders from the current and next menu to be rescheduled (as before) and in addition
		// allow orders from the previous menu to be rescheduled (as they may have been inadvertently rescheduled too far back)
		$menu = DAO_CFactory::create('menu');
		$menu->findCurrent();
		$menu->fetch();
		$curMenuID = $menu->id;

		if ($Session->menu_id < $curMenuID - 1)
		{
			return false;
		}

		/*
		$sessionTS = strtotime($Session->session_start) + 1209600; // allow rescheduling and canceling 2 weeks past session date
		if (CTimezones::getAdjustedServerTime($Store) > $sessionTS)
			return false;
*/

		return true;
	}

	function can_cancel()
	{
		$Booking = DAO_CFactory::create('booking');
		$Booking->order_id = $this->id;
		$Booking->whereAdd("status = 'ACTIVE'");

		if (!$Booking->find(true))
		{
			return false;
		}

		$Session = DAO_CFactory::create('session');
		$Session->id = $Booking->session_id;
		if (!$Session->find(true))
		{
			CLog::Record('N_NOTICE:: Session not found in COrders::can_cancel');

			return false;
		}

		$Store = DAO_CFactory::create('store');
		$Store->id = $Session->store_id;
		if (!$Store->find(true))
		{
			CLog::Record('N_NOTICE:: Store not found in COrders::can_cancel');

			return false;
		}

		$todayTS = CTimezones::getAdjustedServerTime($Store);
		$sessionTS = strtotime($Session->session_start);

		if ($todayTS <= $sessionTS)
		{
			//session is future
			return true;
		}

		$menuObj = DAO_CFactory::create('menu');
		$menuObj->id = $Session->menu_id;
		$menuObj->find(true);

		return $menuObj->areSessionsOrdersEditable($Store);
	}

	function can_edit()
	{
		if (defined('ORDER_EDITING_ENABLED') && ORDER_EDITING_ENABLED)
		{
			$Booking = DAO_CFactory::create('booking');
			$Booking->order_id = $this->id;
			$Booking->whereAdd("status = 'ACTIVE'");

			if (!$Booking->find(true))
			{
				return false;
			}

			$Session = DAO_CFactory::create('session');
			$Session->id = $Booking->session_id;
			if (!$Session->find(true))
			{
				CLog::Record('N_NOTICE:: Session not found in COrders::can_edit');

				return false;
			}

			$Store = DAO_CFactory::create('store');
			$Store->id = $Session->store_id;
			if (!$Store->find(true))
			{
				CLog::Record('N_NOTICE:: Store not found in COrders::can_edit');

				return false;
				//	throw new Exception("Store not found in COrders::can_cancel");
			}

			$menuObj = DAO_CFactory::create('menu');
			$menuObj->id = $Session->menu_id;
			$menuObj->find(true);

			return $menuObj->areSessionsOrdersEditable($Store);
		}

		return false;
	}

	/**
	 * Call before cancelling an order, returns an array of payment info so the user can indicate any credits to be made
	 */
	function cancel_preflight()
	{

		$paymentArray = array();

		if (!$this->can_cancel())
		{
			$paymentArray[0] = "The order cannot be cancelled.";

			// TODO: Need to be more specific
			return array(
				$paymentArray,
				false
			);
		}

		// get the payments for this order, iterate over them and handle according to type
		$payment = DAO_CFactory::create('payment');
		$payment->order_id = $this->id;
		$payment->find();
		$refundableAmount = 0;

		$thisPayment = array();

		while ($payment->fetch())
		{

			$thisPayment['id'] = $payment->id;
			$thisPayment['type'] = $payment->payment_type;
			$thisPayment['amt'] = sprintf("%01.2f", $payment->total_amount);
			$thisPayment['can_credit'] = false;
			$thisPayment['is_deposit'] = $payment->is_deposit;

			switch ($payment->payment_type)
			{
				case CPayment::CHECK:
					$thisPayment['num'] = "na";
					$refundableAmount = $refundableAmount + $payment->total_amount;
					break;

				case CPayment::CASH:
					$thisPayment['num'] = "na";
					$refundableAmount = $refundableAmount + $payment->total_amount;
					break;

				case CPayment::GIFT_CERT:
					$thisPayment['num'] = $payment->gift_certificate_number;
					$refundableAmount = $refundableAmount + $payment->total_amount;
					break;

				// LMH FOR NOW.. WE WILL ONLY GIVE CREDIT BACK TO THE STORE_CREDIT TABLE
				// THIS IS FOR GIFT CARDS ONLY
				case CPayment::STORE_CREDIT:
					$thisPayment['can_credit'] = true;
					$refundableAmount = $refundableAmount + $payment->total_amount;

					break;

				case CPayment::CC:
					$thisPayment['num'] = $payment->payment_transaction_number;
					$thisPayment['refunded_amount'] = 0;
					$refundableAmount = $refundableAmount + $payment->total_amount;

					//we can credit completed payments only
					if ($payment->is_delayed_payment)
					{
						if ($payment->delayed_payment_status == CPayment::SUCCESS)
						{
							$thisPayment['can_credit'] = true;
							$thisPayment['num'] = $payment->delayed_payment_transaction_number;
						}
						else
						{
							$thisPayment['num'] = "pending";
						}
					}
					else if ($payment->payment_transaction_number)
					{
						$thisPayment['can_credit'] = true;
					}
					break;

				case CPayment::OTHER:
					$thisPayment['num'] = "na";
					$refundableAmount = $refundableAmount + $payment->total_amount;
					break;

				case CPayment::CREDIT:
					$thisPayment['num'] = "na";
					$refundableAmount = $refundableAmount + $payment->total_amount;
					break;

				case CPayment::REFUND:
					$thisPayment['num'] = "na";
					$refundableAmount = $refundableAmount - $payment->total_amount;
					$thisPayment['refund_target'] = $payment->referent_id;
					break;

				case CPayment::REFUND_STORE_CREDIT:
					$thisPayment['num'] = "na";
					$refundableAmount = $refundableAmount - $payment->total_amount;
					break;
			}

			$paymentArray[$payment->id] = $thisPayment;
		}

		foreach ($paymentArray as $id => $payment)
		{
			if ($payment['type'] == CPayment::REFUND)
			{
				if (isset($paymentArray[$payment['refund_target']]))
				{
					$paymentArray[$payment['refund_target']]['refunded_amount'] += $payment['amt'];
				}
			}
		}

		return array(
			$paymentArray,
			sprintf("%01.2f", $refundableAmount)
		);
	}

	function isBundleOrder()
	{

		if (!empty($this->bundle_id))
		{
			return true;
		}

		return false;
	}

	function isIntroOrder()
	{
		if (!$this->items)
		{
			$this->reconstruct();
		}

		if (!$this->items)
		{
			return false;
		}

		foreach ($this->items as $itemObj)
		{
			if ($itemObj[1]->pricing_type == CMenuItem::INTRO)
			{
				return true;
			}
		}

		return false;
	}

	static function menuInfoHasIntroOrder($menuInfo)
	{
		// None
		$hasintro = 0;

		if (!is_array($menuInfo))
		{
			return 0;
		}

		foreach ($menuInfo as $cats)
		{
			if (is_array($cats))
			{
				$anItem = array_rand($cats);
				if ($cats[$anItem]['pricing_type'] == 'INTRO')
				{
					if ($cats[$anItem]['servings_per_item'] == 6)
					{
						// Meal Prep Starter Pack
						$hasintro = 1;
					}
					else
					{
						// Menu Sampler
						$hasintro = 2;
					}
				}
				break;
			}
		}

		return $hasintro;
	}

	function isStandard()
	{
		return (!$this->isNewIntroOffer() && !$this->isTODD() && !$this->isDreamTaste() && !$this->isFundraiser());
	}

	function isMadeForYou()
	{
		$Session = $this->findSession();

		if (empty($Session))
		{
			return false;
		}

		return ($this->isStandard() && $Session->isMadeForYou());
	}

	function isSampler()
	{
		return $this->is_sampler;
	}

	function isTODD()
	{
		$Session = $this->findSession();

		if (!empty($Session->session_type) && $Session->session_type == CSession::TODD || !empty($this->is_TODD))
		{
			return true;
		}

		return false;
	}

	function isFundraiser()
	{
		$Session = $this->findSession();

		if (empty($Session))
		{
			return false;
		}

		if ($Session->session_type == CSession::FUNDRAISER)
		{
			return true;
		}

		return false;
	}

	function isDreamTaste()
	{
		$Session = $this->findSession();

		if (empty($Session))
		{
			return false;
		}

		if ($Session->session_type == CSession::DREAM_TASTE)
		{
			return true;
		}

		return false;
	}

	function isRemotePickup()
	{
		$Session = $this->findSession();

		if (empty($Session))
		{
			return false;
		}

		if ($Session->isRemotePickup())
		{
			return true;
		}

		return false;
	}

	function isDelivery()
	{
		$Session = $this->findSession();

		if (empty($Session))
		{
			return false;
		}

		if ($Session->isMadeForYou() && $Session->session_type_subtype == CSession::DELIVERY || $Session->session_type_subtype == CSession::DELIVERY_PRIVATE)
		{
			return true;
		}

		return false;
	}

	public function isInEditOrder()
	{
		return $this->isInEditOrder;
	}

	public function setIsInEditOrder($val)
	{
		$this->isInEditOrder = $val;
	}

	function isShipping()
	{
		$Session = $this->findSession();
		$Store = $this->getStore();

		if (!empty($Session) && $Session->session_type == CSession::DELIVERED)
		{
			return true;
		}

		if (!empty($Store) && $Store->store_type == CStore::DISTRIBUTION_CENTER)
		{
			return true;
		}

		return false;
	}

	function isDelivered()
	{
		return $this->isShipping();
	}

	function isNewIntroOffer()
	{
		if ($this->isBundleOrder())
		{
			$bundle = $this->getBundleObj();

			if ($bundle->bundle_type == CBundle::TV_OFFER)
			{
				return true;
			}
		}

		return false;
	}

	function updateTypeOfOrder()
	{
		if ($this->isDreamTaste())
		{
			$this->type_of_order = self::DREAM_TASTE;

			return;
		}
		if ($this->isFundraiser())
		{
			$this->type_of_order = self::FUNDRAISER;

			return;
		}
		if ($this->isNewIntroOffer())
		{
			$this->type_of_order = self::INTRO;

			return;
		}
		if ($this->isTODD())
		{
			$this->type_of_order = self::TODD;

			return;
		}

		$this->type_of_order = self::STANDARD;
	}

	function isFamilySavingsOrder()
	{

		$Session = $this->findSession();

		if (!empty($Session))
		{
			if (!empty($Session->menu_id) && $Session->menu_id >= 72)
			{
				return false;
			}
		}

		if ($this->family_savings_discount_version == 2)
		{
			return false;
		}

		if (!$this->items)
		{
			$this->reconstruct();
		}

		if (!$this->items)
		{
			return false;
		}

		foreach ($this->items as $itemObj)
		{
			if ($itemObj[1]->pricing_type == CMenuItem::FULL || $itemObj[1]->pricing_type == CMenuItem::HALF)
			{
				return true;
			}
		}

		return false;
	}

	function getNumberServings()
	{
		if (!$this->items)
		{
			return 0;
		}

		$numServings = 0;

		foreach ($this->items as $itemObj)
		{

			if (isset($itemObj[1]->parentItemId) && isset($itemObj[1]->items[$itemObj[1]->parentItemId]))
			{
				//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
				$itemObj[0] -= $itemObj[1]->bundleItemCount;

				if ($itemObj[0] <= 0)
				{
					continue;
				}
			}

			if (isset($itemObj[1]->servings_per_item))
			{
				$numServings += ($itemObj[1]->servings_per_item * $itemObj[0]);
			}
			else
			{
				if ($itemObj[1]->pricing_type == CMenuItem::FULL)
				{
					$numServings += (6 * $itemObj[0]);
				}
				else if ($itemObj[1]->pricing_type == CMenuItem::HALF)
				{
					$numServings += (3 * $itemObj[0]);
				}
				else if ($itemObj[1]->pricing_type == CMenuItem::LEGACY)
				{
					$numServings += (6 * $itemObj[0]);
				}
				else if ($itemObj[1]->pricing_type == CMenuItem::INTRO)
				{
					$numServings += (3 * $itemObj[0]);
				}
			}
		}

		return $numServings;
	}

	function getTotalItemQuantity()
	{
		if (!$this->items)
		{
			return 0;
		}

		$totalQuantityAccrossAllItems = 0;

		foreach ($this->items as $item)
		{
			$totalQuantityAccrossAllItems += ($item[0] * $item[1]->item_count_per_item);
		}

		return $totalQuantityAccrossAllItems;
	}

	function getNumberQualifyingServings()
	{
		if (!$this->items)
		{
			return 0;
		}

		$numServings = 0;

		foreach ($this->items as $itemObj)
		{

			if (isset($itemObj[1]->parentItemId) && isset($itemObj[1]->items[$itemObj[1]->parentItemId]))
			{
				//this item has a parent which determines price and servings so decrement quantity and possibly skip this item
				$itemObj[0] -= $itemObj[1]->bundleItemCount;

				if ($itemObj[0] <= 0)
				{
					continue;
				}
			}

			if ($itemObj[1]->menu_item_category_id != 9 && $itemObj[1]->is_store_special == 0)
			{
				if (isset($itemObj[1]->servings_per_item))
				{
					$numServings += ($itemObj[1]->servings_per_item * $itemObj[0]);
				}
				else
				{
					if ($itemObj[1]->pricing_type == CMenuItem::FULL)
					{
						$numServings += (6 * $itemObj[0]);
					}
					else if ($itemObj[1]->pricing_type == CMenuItem::HALF)
					{
						$numServings += (3 * $itemObj[0]);
					}
					else if ($itemObj[1]->pricing_type == CMenuItem::LEGACY)
					{
						$numServings += (6 * $itemObj[0]);
					}
					else if ($itemObj[1]->pricing_type == CMenuItem::INTRO)
					{
						$numServings += (3 * $itemObj[0]);
					}
				}
			}
		}

		return $numServings;
	}

	/**
	 * set booking to cancelled, iterate over the passed in array (payment id => amount to credit)
	 * returns an array of results (payment id => result string) or false
	 *
	 * modified: 3-31-06 ToddW $creditArray was being used as a mutlidimensional array, but it really is only
	 * a simple array of payment ids and amount values.
	 */
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

			$creditWasCancelled = true;

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
						$rsltMsg = $payment->convertDebitGiftCardPaymentToStoreCredit($this->id);
						$userText = $rsltMsg;
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

			if (!empty($cancelArray))
			{
				// CES 4/19/22 - only Pending Delayed Payments supported for cancellation
				foreach ($cancelArray as $id => $v)
				{
					$payment = DAO_CFactory::create('payment');
					$payment->id = $id;
					if (!$payment->find(true))
					{
						throw new Exception("Payment not found in COrders::cancel().");
					}
					$orgPayment = clone($payment);

					if ($payment->payment_type == CPayment::CC && $payment->is_delayed_payment)
					{
						$payment->delayed_payment_status = CPayment::CANCELLED;
						$payment->delayed_payment_transaction_date = date('Y-m-d H:i:s');
						$payment->update($orgPayment);
					}

					$resultArray[$id] = "Cancelled Delayed Payment for $" . CTemplate::moneyFormat($v) . ".";

					if (!empty($userText))
					{
						$resultArray[$id] .= " Result : " . $userText;
					}
				}
			}

			if (empty($resultArray))
			{
				$resultArray[0] = "No payments were refunded or cancelled.";
			}

			$session = DAO_CFactory::create('session');
			$session->query("select menu_id, store_id, session_start from session where id = {$booking->session_id} ");
			$session->fetch();
			$menu_id = false;

			if (!empty($session->menu_id))
			{
				$menu_id = $session->menu_id;
			}
			// add items back into inventory - (reduce number sold)

			$sessionIsInPast = $session->isInThePast();

			$order_item = DAO_CFactory::create('order_item');
			$order_item->query("SELECT oi.*, mi.servings_per_item, mi.is_chef_touched, mi.recipe_id FROM order_item oi " . " JOIN menu_item mi ON mi.id = oi.menu_item_id WHERE oi.is_deleted = 0 AND oi.order_id = " . $this->id);

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
						$invItem->query("update menu_item_inventory mii set mii.number_sold = mii.number_sold - " . " $servingQty where mii.recipe_id = {$order_item->recipe_id} and mii.store_id = {$this->store_id} and mii.menu_id = $menu_id and mii.is_deleted = 0");
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

		if ($this->dream_rewards_level > 0)
		{
			$rewardMsg = "";
			if ($this->isMostRecentOrder())
			{

				$UserOld = clone($User);
				$currentLevel = $User->dream_reward_level;
				$previousLevel = $currentLevel;
				$rewardMsg = "<span style='color:red'><b>Dream Rewards Adjusted: </b></span>This order was placed within the Dream Rewards program. The user&rsquo;s dream reward level was lowered.";
				$currentLevel--;

				$User->dream_reward_level = $currentLevel;

				$User->update($UserOld);

				CDreamRewardsHistory::recordDreamRewardsEvent($User->id, $this->store_id, $this->id, 3, $User->dream_reward_status, $User->dream_reward_level, $User->dream_reward_status, $previousLevel, 'cancelled order decrements level- : ' . CAppUtil::truncate($_SERVER['HTTP_REFERER'], 72));
			}
			else
			{

				$rewardMsg = "<span style='color:red'><b>Dream Rewards <u>NOT</u> adjusted: </b></span>This order was placed within the Dream Rewards program however " . "the user has placed a more recent order. Please review the user&rsquo;s order history and adjust their reward level manually.";

				CDreamRewardsHistory::recordDreamRewardsEvent($User->id, $this->store_id, $this->id, 3, $User->dream_reward_status, $User->dream_reward_level, $User->dream_reward_status, $User->dream_reward_level, 'cancelled order - level not decremented -  : ' . CAppUtil::truncate($_SERVER['HTTP_REFERER'], 72));
			}

			$resultArray[] = $rewardMsg;
		}

		if (!$suppressCancellationEmail)
		{
			self::sendCancelEmail($User, $this);
		}

		return $resultArray;
	}

	function isMostRecentOrder()
	{
		if (empty($this->id) || empty($this->user_id))
		{
			return false;
		}

		$TestOrder = DAO_CFactory::create('orders');
		$TestOrder->query("SELECT o.id FROM orders o JOIN booking b ON o.id = b.order_id WHERE o.id > " . $this->id . " AND b.status = 'ACTIVE' AND o.user_id = " . $this->user_id);

		if ($TestOrder->N > 0)
		{
			return false;
		}

		return true;
	}

	static function generateConfirmationNum()
	{
		$length = 8;
		$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

		// set pool of possible char
		if ($pool == "")
		{
			$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$pool .= "abcdefghijklmnopqrstuvwxyz";
			$pool .= "0123456789";
		}// end if
		mt_srand((double)microtime() * 1000000);
		$unique_id = "";
		for ($index = 0; $index < $length; $index++)
		{
			$unique_id .= substr($pool, (mt_rand() % (strlen($pool))), 1);
		}// end for

		return (dechex(time()) . $unique_id);
	}

	/**
	 * @return 'session full', 'closed', 'failed', 'success'
	 *
	 * This functions differently than reschedule() since a Saved order does not occupy a slot.
	 * We don't need to do the fancy locking etc. until the order is Activated
	 *
	 *
	 */
	function rescheduleSavedOrder($target_session_id, $Booking, $Order = false)
	{

		$this->query('START TRANSACTION;');

		try
		{
			if ($this->session)
			{

				$CapacityExists = false;
				$Bookings = DAO_CFactory::create('booking');

				$Bookings->query('SELECT status, booking_type FROM booking WHERE session_id=' . $this->session->id . " AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND is_deleted = 0");

				//count active bookings
				$StdBookCnt = 0;
				$IntroBookCnt = 0;
				while ($Bookings->fetch())
				{
					if ($Bookings->status === CBooking::ACTIVE)
					{
						if ($Bookings->booking_type == 'INTRO')
						{
							$IntroBookCnt++;
						}
						else
						{
							$StdBookCnt++;
						}
					}
				}

				$intro_capacity = $this->session->introductory_slots;
				$standard_capacity = $this->session->available_slots;

				if ($Booking->booking_type == 'INTRO')
				{
					$introSlotsAvailable = ($intro_capacity - $IntroBookCnt > 0);
					$anySlotAvailable = ($standard_capacity - ($IntroBookCnt + $StdBookCnt) > 0);

					if ($introSlotsAvailable && $anySlotAvailable)
					{
						$CapacityExists = true;
					}
				}
				else
				{
					if (!empty($standard_capacity) && ($standard_capacity - ($StdBookCnt + $IntroBookCnt) > 0))
					{
						$CapacityExists = true;
					}
				}

				if (!$CapacityExists)
				{
					// TODO: Warn store that target session is full
				}

				$Booking->session_id = $target_session_id;
				$success = $Booking->update();

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
	 */
	function reschedule($orginal_schedule_id, $fadmin_rules = true, $suppress_email = false)
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

				// Now both TV Offer and sampler orders occupy intro slots
				if ($this->isNewIntroOffer())
				{
					$Booking->booking_type = 'INTRO';
				}

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

					if ($this->session->menu_id > SLOT_STEALING_CUTOFF_MENU)
					{

						$BookingLocks->query('SELECT status, booking_type FROM booking WHERE session_id=' . $this->session->id . " AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND status != 'SAVED' AND is_deleted = 0 FOR UPDATE ");

						//count active bookings
						$StdBookCnt = 0;
						$IntroBookCnt = 0;
						while ($BookingLocks->fetch())
						{
							if ($BookingLocks->status === CBooking::ACTIVE)
							{
								if ($BookingLocks->booking_type == 'INTRO')
								{
									$IntroBookCnt++;
								}
								else
								{
									$StdBookCnt++;
								}
							}
						}

						$intro_capacity = $this->session->introductory_slots;
						$standard_capacity = $this->session->available_slots;

						if ($Booking->booking_type == 'INTRO')
						{
							$introSlotsAvailable = ($intro_capacity - $IntroBookCnt > 0);
							$anySlotAvailable = ($standard_capacity - ($IntroBookCnt + $StdBookCnt) > 0);

							if ($introSlotsAvailable && $anySlotAvailable)
							{
								$CapacityExists = true;
							}
						}
						else
						{
							if (!empty($standard_capacity) && ($standard_capacity - ($StdBookCnt + $IntroBookCnt) > 0))
							{
								$CapacityExists = true;
							}
						}
					}
					else
					{
						//find session capacity
						if ($this->isIntroOrder())
						{
							$capacity = $this->session->introductory_slots;
						}
						else
						{
							$capacity = $this->session->available_slots;
						}

						if ($this->isIntroOrder())
						{
							$BookingLocks->query('SELECT * FROM booking WHERE session_id=' . $this->session->id . " AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND status != 'SAVED' AND booking_type = 'INTRO' AND is_deleted = 0 FOR UPDATE ");
						}
						else
						{
							$BookingLocks->query('SELECT * FROM booking WHERE session_id=' . $this->session->id . " AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND status != 'SAVED' AND booking_type <> 'INTRO' AND is_deleted = 0 FOR UPDATE ");
						}

						//count active bookings
						$bookCnt = 0;
						while ($BookingLocks->fetch())
						{
							if ($BookingLocks->status === CBooking::ACTIVE)
							{
								$bookCnt++;
							}
						}

						if ($bookCnt < $capacity)
						{
							$CapacityExists = true;
						}
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
		$new_type = $this->findSession()->session_class;
		$newID = $this->findSession()->id;
		$menuID = $this->findSession()->menu_id;

		COrdersDigest::recordRescheduledOrder($this->id, $new_time, $this->store_id, $newID, $menuID, $new_type);

		// Send email
		$User = DAO_CFactory::create('user');
		$User->id = $this->user_id;
		if (!$User->find(true))
		{
			throw new Exception("User not found in COrders::reschedule().");
		}

		$OrigSession = DAO_CFactory::create('session');
		$OrigSession->id = $orginal_schedule_id;
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

	function removeHardSkipIfNeeded()
	{

		//Note: this will need to be called when editing from less than 36 servings to >= 36
		// IF we do not enforce the first order be >= 36 servings
		if ($this->servings_total_count >= 36 && !empty($this->membership_id))
		{
			// Add the skip and update status
			$productOrderItem = DAO_CFactory::create('product_orders_items');
			$productOrderItem->id = $this->membership_id;
			$productOrderItem->find(true);

			$orgOrderItem = clone $productOrderItem;

			$jsonArr = json_decode($productOrderItem->product_membership_hard_skip_menu);
			if (!is_array($jsonArr) || count($jsonArr) == 0)
			{
				return;
			}

			$menu_id = $this->findSession()->menu_id;
			if (in_array($menu_id, $jsonArr))
			{

				$newJsonArr = array();
				foreach ($jsonArr as $thisMenuID)
				{
					if ($thisMenuID != $menu_id)
					{
						$newJsonArr[] = $thisMenuID;
					}
				}

				$productOrderItem->product_membership_hard_skip_menu = json_encode($newJsonArr);

				$productOrderItem->update($orgOrderItem);

				$meta_arr = array(
					'store_id' => $this->store_id,
					'menu_id' => $menu_id,
					'order_id' => $this->id
				);

				CMembershipHistory::recordEvent($this->user_id, $this->membership_id, CMembershipHistory::SKIP_REVOKED, $meta_arr);
			}
		}
	}

	function postProcessActivatedOrder($Store, $Customer, $emptyCart = false)
	{
		CLog::RecordDebugTrace('COrders::postProcessActivatedOrder called for order: ' . $this->id, "TR_TRACING");

		try
		{
			// On the live server we must eat exceptions here as the order has been committed at thgis point.  If an excpetion occurs here and is alloe
			if ($this->points_discount_total > 0)
			{
				CPointsCredits::processCredits($this->user_id, $this->points_discount_total, $this->id);
			}

			// Notes: LMH [ PING PROJECT ADDITION 5/10/2007]
			// if this fails, we will not stop the order no matter what
			// Additionally, if this is too slow, we may need to create a cron job that will look these up nightly :(
			CUserRetentionData::updateUserRetentionInfo($this->user_id, $this->id, $this->store_id);
			COrdersDigest::recordNewOrder($this, $Store);

			// insert orders_address
			if ($this->session->isDelivery())
			{
				if (isset($this->orderAddress) && is_a($this->orderAddress, 'DAO_Orders_address') && empty($this->orderAddress->id))
				{
					if (empty($this->orderAddress->order_id))
					{
						$this->orderAddress->order_id = $this->id;
					}

					if (empty($this->orderAddress->address_line1))
					{
						CLog::RecordNew(CLog::ERROR, "Address Line 1 is empty when inserting into orders_address; Order ID: " . $this->id . "\r\n" . print_r($this->orderAddress, true), "", "", true);
					}

					$this->orderAddress->insert();
				}
			}

			// check to see if the guest has a current membership and if this menu was marked as a hard skip.
			$this->removeHardSkipIfNeeded();
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
	 */
	function processOrder($payments, $useTransaction = true)
	{
		CLog::RecordDebugTrace('COrders::processOrder called for user: ' . $this->user_id, "TR_TRACING");

		if ($this->items && !$this->session)
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

		$this->updateMinimumQualification();

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
			if (($this->items || $this->products) && $this->session && (($this->order_type == self::DIRECT) || ($this->session->isOpen($Store) && $this->session->session_publish_state == CSession::PUBLISHED)))
			{

				$intro_capacity = $this->session->introductory_slots;
				$standard_capacity = $this->session->available_slots;

				$Booking->session_id = $this->session->id;
				//$Booking->order_id = $this->id;
				$Booking->user_id = $this->user_id;
				$Booking->status = CBooking::HOLD;

				$Booking->booking_type = 'STANDARD';
				if ($this->isNewIntroOffer())
				{
					$Booking->booking_type = 'INTRO';
				}

				$this->updateTypeOfOrder();

				$fundraiserSessionPropObj = CFundraiser::fundraiserEventSessionProperties($this->session);
				if (!empty($fundraiserSessionPropObj) && !empty($fundraiserSessionPropObj->fundraiser_id) && !empty($fundraiserSessionPropObj->fundraiser_value))
				{
					$this->fundraiser_id = $fundraiserSessionPropObj->fundraiser_id;
					$this->fundraiser_value = $fundraiserSessionPropObj->fundraiser_value;
				}

				$id = $Booking->insert();
				if (!$id)
				{
					throw new Exception ('booking insert failed : data error ' . $Booking->_lastError->getMessage());
				}

				// user is placing a full order update session_rsvp for the same session if they have one
				CSession::upgradeSessionRSVP($this->session->id, $this->user_id, $Booking->id);

				//lock booking table
				//to prevent this:
				//Thread 1: read available bookings (1 slot left)
				//Thread 2: read available bookings (1 slot left)
				//Thread 1: write booking (0 slot left)
				//Thread 2: write booking (-1 slot left)

				$CapacityExists = true;
				$actualAvailableStdSlots = 0;
				$actualAvailableIntroSlots = 0;

				if ($this->order_type != self::DIRECT)
				{

					$CapacityExists = false;
					$BookingLocks = DAO_CFactory::create('booking');

					$BookingLocks->query('SELECT status, booking_type FROM booking WHERE session_id=' . $this->session->id . " AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND status != 'SAVED' AND is_deleted = 0 FOR UPDATE ");

					//count active bookings
					$StdBookCnt = 0;
					$IntroBookCnt = 0;
					while ($BookingLocks->fetch())
					{
						if ($BookingLocks->status === CBooking::ACTIVE)
						{
							if ($BookingLocks->booking_type == 'INTRO')
							{
								$IntroBookCnt++;
							}
							else
							{
								$StdBookCnt++;
							}
						}
					}

					if ($Booking->booking_type == 'INTRO')
					{

						$actualAvailableStdSlots = $standard_capacity - ($IntroBookCnt + $StdBookCnt);
						$actualAvailableIntroSlots = $intro_capacity - $IntroBookCnt;

						$introSlotsAvailable = ($intro_capacity - $IntroBookCnt > 0);
						$anySlotAvailable = ($standard_capacity - ($IntroBookCnt + $StdBookCnt) > 0);

						if ($introSlotsAvailable && $anySlotAvailable)
						{
							$CapacityExists = true;
						}
					}
					else
					{
						$actualAvailableStdSlots = $standard_capacity - ($IntroBookCnt + $StdBookCnt);

						if ($standard_capacity - ($StdBookCnt + $IntroBookCnt) > 0)
						{
							$CapacityExists = true;
						}
					}

					// capacity for RSVP determination
					if ($CapacityExists)
					{
						// RSVP count against slot capacity but are not yet full fledged bookings
						$activeRSVPs = $this->session->get_RSVP_count($this->user_id);

						if ($actualAvailableStdSlots - $activeRSVPs <= 0)
						{
							$CapacityExists = false;
						}
					}
				}

				if (($this->order_type == self::DIRECT) || $CapacityExists)
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

								$ccPayment->updateUserCardReference($rslt, $ref_number);

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
	 * @return string[], 'invalidCC', 'session full', 'closed', 'failed', 'success'
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

		$this->updateMinimumQualification();

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
			if (($this->items || $this->products) && $this->session && (($this->order_type == self::DIRECT) || ($this->session->isOpen($Store) && $this->session->session_publish_state == CSession::PUBLISHED)))
			{

				$intro_capacity = $this->session->introductory_slots;
				$standard_capacity = $this->session->available_slots;

				$Booking->booking_type = 'STANDARD';
				if ($this->isNewIntroOffer())
				{
					$Booking->booking_type = 'INTRO';
				}

				$Booking->status = CBooking::HOLD;

				$this->updateTypeOfOrder();

				// user is placing a full order update session_rsvp for the same session if they have one
				CSession::upgradeSessionRSVP($this->session->id, $this->user_id, $Booking->id);

				//lock booking table
				//to prevent this:
				//Thread 1: read available bookings (1 slot left)
				//Thread 2: read available bookings (1 slot left)
				//Thread 1: write booking (0 slot left)
				//Thread 2: write booking (-1 slot left)

				$CapacityExists = false;
				$BookingLocks = DAO_CFactory::create('booking');

				$BookingLocks->query('SELECT status, booking_type FROM booking WHERE session_id=' . $this->session->id . " AND status != 'CANCELLED' AND status != 'RESCHEDULED' AND is_deleted = 0 FOR UPDATE ");

				//count active bookings
				$StdBookCnt = 0;
				$IntroBookCnt = 0;
				$savedCount = 0;
				$savedIntroCount = 0;
				while ($BookingLocks->fetch())
				{
					if ($BookingLocks->status === CBooking::ACTIVE)
					{
						if ($BookingLocks->booking_type == 'INTRO')
						{
							$IntroBookCnt++;
						}
						else
						{
							$StdBookCnt++;
						}
					}
					else if ($BookingLocks->status === CBooking::SAVED)
					{
						$savedCount++;

						if ($BookingLocks->booking_type == 'INTRO')
						{
							$savedIntroCount++;
						}
					}
				}

				$CapacityExistsAfterThisOrder = false;

				if ($Booking->booking_type == 'INTRO')
				{
					$introSlotsAvailable = ($intro_capacity - $IntroBookCnt > 0);
					$anySlotAvailable = ($standard_capacity - ($IntroBookCnt + $StdBookCnt) > 0);

					if ($introSlotsAvailable && $anySlotAvailable)
					{
						$CapacityExists = true;
					}

					if ($CapacityExists && ($intro_capacity - $IntroBookCnt) > 1)
					{
						$CapacityExistsAfterThisOrder = true;
					}

					$savedCount = $savedIntroCount;

					// needed by RSVP check below;
					$stdCapRemaining = $intro_capacity - $IntroBookCnt;
				}
				else
				{

					$stdCapRemaining = $standard_capacity - ($StdBookCnt + $IntroBookCnt);

					if ($stdCapRemaining > 0)
					{
						$CapacityExists = true;
					}

					if ($stdCapRemaining > 1)
					{
						$CapacityExistsAfterThisOrder = true;
					}
				}

				// capacity for RSVP determination
				if ($CapacityExists)
				{
					// RSVP count against slot capacity but are not yet full fledged bookings
					$activeRSVPs = $this->session->get_RSVP_count($this->user_id);

					$StdCapacityAfterRSVPs = $stdCapRemaining - $activeRSVPs;

					if ($StdCapacityAfterRSVPs <= 0)
					{
						$CapacityExists = false;
					}

					if ($StdCapacityAfterRSVPs > 1)
					{
						$CapacityExistsAfterThisOrder = true;
					}
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

	/***
	 * Processes the passed in Store Credits to Payments for this order
	 * Does not check for over or under payment
	 *
	 * @return 'failed', 'success'
	 */
	function processStoreCreditPayments($sc_payment_array)
	{

		foreach ($sc_payment_array as $pay)
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

				return 'failed';
			}

			$pay->recordRevenueEvent($this->findSession());

			if (isset($pay->store_credit_DAO))
			{
				$fromCrdit = (int)($pay->store_credit_DAO->amount * 100);
				$fromPay = (int)($pay->total_amount * 100);

				if ($fromCrdit > $fromPay)
				{ //partial usage of a store credit- must create a new store credit for the leftover
					$newStoreCredit = clone($pay->store_credit_DAO);
					$newStoreCredit->amount = ($pay->total_amount - $pay->store_credit_DAO->amount) * -1;
					$rslt = $newStoreCredit->insert();
					if (!$rslt)
					{
						if (DEBUG)
						{
							echo 'E_ERROR:: store credit insertion error ';
						}
						@CLog::Record('E_ERROR:: store credit insertion error ' . implode(',', $pay->store_credit_DAO->toArray()));

						return 'failed';
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

					return 'failed';
				}
			}
		}

		return 'success';
	}

	/***
	 * Given an 'edited order' create all edit order records and apply given Payment Methods (charge or refunds)
	 *
	 * @param       $originalOrder
	 * @param null  $Cart
	 * @param null  $order_revisions
	 * @param null  $paymentsFromCart
	 * @param null  $newPaymentType
	 * @param null  $creditCardArray
	 * @param false $storeCreditArray
	 * @param false $giftCardArray
	 * @param bool  $useTransaction
	 *
	 * @return string[]
	 * @throws Exception
	 */

	function processEditOrder($originalOrder, $Cart = null, $order_revisions = null, $previousPayments = null, $paymentType = null, $creditCardArray = null, $storeCreditArray = false, $giftCardArray = false, $useTransaction = true)
	{
		//ONly implemented in COrdersDelivered right now

	}

	/***
	 * Processes the payment, creates a booking, and saves the order;
	 * Payment type may be null as payment may be through store credit only
	 *
	 * @return 'invalidCC', 'session full', 'closed', 'failed', 'success'
	 */
	function processNewOrderDirectOrder($PaymentArray, $storeCreditArray = false, $giftCardArray = false, $orderIsSaved = false)
	{

		CLog::RecordDebugTrace('COrders::processNewOrderDirectOrder called for user: ' . $this->user_id, "TR_TRACING");

		$this->recalculate();

		$this->updateMinimumQualification();

		$paymentTotal = 0;
		$hasCCPayment = false;
		foreach ($PaymentArray as $pay)
		{
			if ($pay->payment_type != CPayment::STORE_CREDIT)
			{
				$paymentTotal += $pay->total_amount;
			}

			if ($pay->payment_type == CPayment::CC)
			{
				$hasCCPayment = true;
			}
		}

		$credit_total = 0;
		$storeCreditArrayDAO = array();
		// Determine balance after applying Store Credit
		if (is_array($storeCreditArray))
		{
			foreach ($storeCreditArray as $storeCredit)
			{
				$storeCreditDAO = DAO_CFactory::create('store_credit');
				$storeCreditDAO->id = $storeCredit;
				if (!$storeCreditDAO->find(true))
				{
					throw new Exception('Store Credit not found in processNewOrderDirectOrder()');
				}
				$storeCreditArrayDAO[$storeCreditDAO->id] = $storeCreditDAO;

				$credit_total += $storeCreditDAO->amount;
			}
		}

		/*
		if ($paymentTotal < $this->grand_total and $hasCCPayment)
		{

			foreach($PaymentArray as $pay)	{
				if ($pay->payment_type == CPayment::CC) {
					$Payment->total_amount = $this->grand_total - $paymentTotal;
				}
			}

		}
		*/

		if (self::isPriceGreaterThanOrEqualTo($paymentTotal, $this->grand_total))
		{
			// decision point - if CC payment amounts become calculated we should
			// check to see if a CC was entered and if so remove it from the array as it is no longer necessary
		}
		else
		{
			// Now build payment objects from store_credits
			// mark the actual amount to charge - must be equal to $storeCreditDAO->amount
			// except for the last one which may be equal to or less than

			$remainderForSC = $this->grand_total - $paymentTotal;

			foreach ($storeCreditArrayDAO as $id => $credit)
			{
				$paymentTotal += $credit->amount;

				$thisPayment = DAO_CFactory::create('payment');
				$thisPayment->user_id = $this->user_id;
				$thisPayment->store_id = $this->store_id;
				$thisPayment->payment_type = CPayment::STORE_CREDIT;
				$thisPayment->is_delayed_payment = false;
				$thisPayment->payment_number = $credit->credit_card_number;
				$thisPayment->store_credit_id = $id;
				$thisPayment->payment_transaction_number = $credit->payment_transaction_number;

				if (self::isPriceGreaterThan($credit->amount, $remainderForSC))
				{
					$thisPayment->store_credit_DAO = $credit;
					$thisPayment->total_amount = floatval(sprintf("%01.2f", $remainderForSC));
					array_push($PaymentArray, $thisPayment);
					break;
				}
				else
				{
					$thisPayment->store_credit_DAO = $credit;
					$thisPayment->total_amount = $credit->amount;
					array_push($PaymentArray, $thisPayment);
				}

				$remainderForSC -= $thisPayment->total_amount;
			}

			if ($giftCardArray)
			{
				//make gift card payment items
				foreach ($giftCardArray as $thisGiftCard)
				{

					if ($thisGiftCard['gc_amount'] <= 0)
					{
						continue;
					}
					// $totalGiftcardCredit += $thisGiftCard['gc_amount'];

					$thisPayment = DAO_CFactory::create('payment');

					$thisPayment->user_id = $this->user_id;
					$thisPayment->store_id = $this->store_id;
					$thisPayment->payment_type = CPayment::GIFT_CARD;
					$thisPayment->is_delayed_payment = false;
					$thisPayment->payment_number = $thisGiftCard['gc_number'];
					//$thisPayment->store_credit_id = $thisGiftCard['credit_id'];
					//$thisPayment->payment_transaction_number = $thisGiftCard['credit_id'];

					//   $cmpGCC = (int)($totalGiftcardCredit * 100);
					//   $cmpGT = (int)($this->grand_total * 100);

					$thisPayment->total_amount = $thisGiftCard['gc_amount'];
					array_push($PaymentArray, $thisPayment);
				}
				// echo "<br />TOTAL GIFT CARD CREDIT:".$totalGiftcardCredit;
			}
		}

		if ($orderIsSaved)
		{
			return $this->processSavedOrder($PaymentArray);
		}
		else
		{
			return $this->processOrder($PaymentArray);
		}
	}

	/***
	 * Processes the payment, creates a booking, and saves the order;
	 * Payment type may be null as payment may be through store credit only
	 *
	 * @return 'invalidCC', 'session full', 'closed', 'failed', 'success'
	 */
	function processNewOrderGC($paymentType, $creditCardArray, $storeCreditArray = false, $giftCardArray = false, $useTransaction = true)
	{
		$ccType = $creditCardArray['ccType'];
		$paymentNumber = $creditCardArray['ccNumber'];
		$paymentMonth = $creditCardArray['ccMonth'];
		$paymentYear = $creditCardArray['ccYear'];
		$name = $creditCardArray['ccNameOnCard'];
		$securityCode = $creditCardArray['ccSecurityCode'];
		$address = $creditCardArray['billing_address'];
		$city = $creditCardArray['city'];
		$state = $creditCardArray['state_id'];
		$zip = $creditCardArray['billing_postal_code'];
		$delayed = $creditCardArray['do_delayed_payment'];
		$saveCard = false;
		if (!empty($creditCardArray['save_card_as_referene']))
		{
			$saveCard = true;
		}

		CLog::RecordDebugTrace('COrders::processNewOrderGC called for user: ' . $this->user_id, "TR_TRACING");

		$this->recalculate();

		$paymentArray = array();

		$totalStoreCredit = 0;
		$storeCreditArrayDAO = array();
		// Determine balance after applying Store Credit

		if ($storeCreditArray)
		{

			foreach ($storeCreditArray as $storeCredit)
			{
				$storeCreditDAO = DAO_CFactory::create('store_credit');
				$storeCreditDAO->id = $storeCredit;
				if (!$storeCreditDAO->find(true))
				{
					throw new Exception('Store Credit not found in processNewOrderGC()');
				}
				$storeCreditArrayDAO[$storeCreditDAO->id] = $storeCreditDAO;

				$totalStoreCredit += $storeCreditDAO->amount;
			}
			//echo "<br />TOTAL STORE CREDIT:".$totalStoreCredit;
		}

		$totalGiftcardCredit = 0;

		if ($giftCardArray)
		{
			foreach ($giftCardArray as $thisGiftCard)
			{
				$totalGiftcardCredit += $thisGiftCard['gc_amount'];
			}
		}

		$cmpSC = (int)($totalStoreCredit * 100) + (int)($totalGiftcardCredit * 100);
		$cmpGT = (int)($this->grand_total * 100);
		if ($cmpSC < $cmpGT && $paymentType == false)
		{
			// there is not enough store credit and gift card funds and no cc payment was provided
			return 'failed';
		}

		// build payment objects from store_credits
		// mark the actual amount to charge - must be equal to $storeCreditDAO->amount except for the last one which may be equal to or less than
		$totalStoreCredit = 0;
		$isEnoughStoreCreditForTotalCost = false;

		$currentFundingTotal = 0;

		foreach ($storeCreditArrayDAO as $id => $credit)
		{
			$totalStoreCredit += $credit->amount;

			$thisPayment = DAO_CFactory::create('payment');
			$thisPayment->user_id = $this->user_id;
			$thisPayment->store_id = $this->store_id;
			$thisPayment->payment_type = CPayment::STORE_CREDIT;
			$thisPayment->is_delayed_payment = false;
			$thisPayment->payment_number = $credit->credit_card_number;
			$thisPayment->store_credit_id = $id;
			$thisPayment->payment_transaction_number = $credit->payment_transaction_number;

			$cmpSC = (int)($totalStoreCredit * 100);
			$cmpGT = (int)($this->grand_total * 100);

			if ($cmpSC >= $cmpGT)
			{
				$thisPayment->total_amount = $credit->amount - ($totalStoreCredit - $this->grand_total);
				$currentFundingTotal += $thisPayment->total_amount;
				$thisPayment->store_credit_DAO = $credit;
				array_push($paymentArray, $thisPayment);
				$isEnoughStoreCreditForTotalCost = true;
				break;
			}
			else
			{
				$thisPayment->store_credit_DAO = $credit;
				$thisPayment->total_amount = $credit->amount;
				array_push($paymentArray, $thisPayment);
				$currentFundingTotal += $credit->amount;
			}
		}

		if ($giftCardArray && !$isEnoughStoreCreditForTotalCost)
		{
			foreach ($giftCardArray as $thisGiftCard)
			{

				$currentFundingTotal += $thisGiftCard['gc_amount'];

				$thisPayment = DAO_CFactory::create('payment');
				$thisPayment->user_id = $this->user_id;
				$thisPayment->store_id = $this->store_id;
				$thisPayment->payment_type = CPayment::GIFT_CARD;
				$thisPayment->is_delayed_payment = false;
				$thisPayment->payment_number = $thisGiftCard['gc_number'];

				$thisPayment->total_amount = $thisGiftCard['gc_amount'];
				array_push($paymentArray, $thisPayment);
			}
		}

		$cmpSC = (int)($currentFundingTotal * 100);
		$cmpGT = (int)($this->grand_total * 100);
		if ($cmpSC < $cmpGT && (($paymentType == CPayment::CC && $paymentNumber) || isset($creditCardArray['reference'])))
		{
			$Payment = DAO_CFactory::create('payment');
			$Payment->user_id = $this->user_id;
			$Payment->store_id = $this->store_id;

			if (!isset($this->ltd_round_up_value) || !is_numeric($this->ltd_round_up_value))
			{
				$this->ltd_round_up_value = 0;
			}

			$Payment->total_amount = ($this->grand_total + $this->ltd_round_up_value) - $totalStoreCredit - $totalGiftcardCredit;
			$Payment->is_delayed_payment = 0;

			if ($delayed === 'false')
			{
				$delayed = false;
			}

			if ($delayed)
			{
				$Payment->is_delayed_payment = $delayed;
			}

			$Payment->delayed_payment_status = CPayment::PENDING;

			if (isset($creditCardArray['reference']))
			{
				$Payment->setCCInfo($paymentNumber, $paymentMonth, $paymentYear, $name, $address, $city, $state, $zip, $ccType, $securityCode);
				$Payment->payment_type = "REF_" . $creditCardArray['reference'];
			}
			else
			{
				$Payment->setCCInfo($paymentNumber, $paymentMonth, $paymentYear, $name, $address, $city, $state, $zip, $ccType, $securityCode, $saveCard);
			}

			array_push($paymentArray, $Payment);
			// echo "<br />CREDIT CARD AMOUNT: ".$Payment->total_amount;
		}

		if (empty($paymentArray))
		{
			if ($this->grand_total > 0)
			{
				CLog::RecordIntense("The Payment Array is empty: cctype: $ccType: paymentNumber: $paymentNumber", 'ryan.snook@dreamdinners.com');

				return array(
					'result' => 'failed',
					'userText' => 'A problem has occurred in processing the payment.'
				);
			}
			else
			{
				// Added by CES 6/27/2013 - 100% preferred user discount can mean a grand_total of 0 so we need to accept that and post a "No CHARGE" payment

				$NCPayment = DAO_CFactory::create('payment');
				$NCPayment->payment_type = CPayment::CREDIT;
				$NCPayment->user_id = $this->user_id;
				$NCPayment->store_id = $this->store_id;
				$NCPayment->total_amount = 0;
				$NCPayment->is_delayed_payment = 0;
				$paymentArray[] = $NCPayment;
			}
		}

		return $this->processOrder($paymentArray, $useTransaction);
	}

	/**
	 * Add a new payment to an existing order
	 */
	function addPayment($Payment)
	{

		CLog::Assert(false, "COrders::addPayment should not be used");

		$Customer = DAO_CFactory::create('user');
		$Customer->id = $this->user_id;
		$Customer->find(true);

		$Store = $this->getStore();

		try
		{
			$Payment->validate(true);
		}
		catch (exception $e)
		{
			return array(
				'result' => 'invalidPayment',
				'userText' => ''
			);
		}

		try
		{
			////////////////////////
			// process credit card
			if ($Payment->payment_type == CPayment::CC)
			{
				//processes cc payment with verisign
				$rslt = $Payment->processPayment($Customer, $this, $Store);

				if ($rslt['result'] != 'success')
				{
					//verisign failed, rollback order and booking

					if ('transactionDecline' == $rslt['result'])
					{
						CLog::Record('N_NOTICE:: ' . $Payment->verisignExtendedRslt);

						return $rslt;
					}
					else
					{
						CLog::Record('E_ERROR:: ' . $Payment->verisignExtendedRslt);
						$rslt['result'] = 'failed';

						return $rslt;
					}
				}
			}

			//end process credit card
			////////////////////////

			try
			{
				//insert payment record
				$Payment->order_id = $this->id;
				$rslt = $Payment->insert();
				if (!$rslt)
				{
					if (DEBUG)
					{
						echo 'E_ERROR:: payment insertion error ';
					}
					@CLog::Record('E_ERROR:: payment insertion error ' . implode(',', $Payment->toArray()));
				}
			}
			catch (exception $exc)
			{
				if (DEBUG)
				{
					throw $exc;
				}
				//if anything fails at this point, we're stuck, so just log the error
				@CLog::Record('E_ERROR:: payment insertion error ' . implode(',', $Payment->toArray()));
			}
		}
		catch (exception $exc)
		{
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

		return array(
			'result' => 'success',
			'userText' => ''
		);
	}

	/**
	 * Cart function
	 */
	function serializeMe()
	{

		$parentBundleItems = array();

		$rtn = $this->toArray();
		$rtn['version'] = 2;
		if ($this->items)
		{
			$rtn['mitems'] = array();
			foreach ($this->items as $id => $itemInfo)
			{

				if ($this->family_savings_discount_version == 2 && isset($itemInfo[1]->isPromo) && $itemInfo[1]->isPromo)
				{
					$rtn['mitems']['p_' . $id] = $itemInfo[0];
				}
				else if ($this->family_savings_discount_version == 2 && isset($itemInfo[1]->isFreeMeal) && $itemInfo[1]->isFreeMeal)
				{
					$rtn['mitems']['f_' . $id] = $itemInfo[0];
				}
				else
				{
					$rtn['mitems'][$id] = $itemInfo[0];
				}

				if ($itemInfo[1]->is_bundle)
				{
					$parentBundleItems[] = $id;
				}
			}
		}

		if ($this->products)
		{
			$rtn['mproducts'] = array();
			foreach ($this->products as $id => $itemInfo)
			{
				$rtn['mproducts'][$id] = $itemInfo[0];
			}
		}

		// New store managed bundle
		if (!empty($parentBundleItems))
		{
			$rtn['mBundleMap'] = array();
			foreach ($parentBundleItems as $pid)
			{
				// loop through and add children of this bundle
				$rtn['mBundleMap'][$pid] = array();
				foreach ($this->items as $id => $itemInfo)
				{
					if ($itemInfo[1]->parentItemId == $pid)
					{
						$rtn['mBundleMap'][$pid][$id] = $itemInfo[1]->bundleItemCount;
					}
				}
			}
		}

		// TV Offer style bundle
		if (!empty($this->bundle_id) && isset($this->bundle))
		{
			$rtn['mbundleitems'] = array();
			foreach ($this->bundle->items as $id => $info)
			{
				if ($info['chosen'])
				{
					$rtn['mbundleitems'][] = $id;
				}
			}
		}

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

		if (isset($this->session))
		{
			$rtn['session'] = $this->session->id;
			$rtn['store_id'] = $this->session->store_id;
			if (!array_key_exists('store_id', $rtn) || !$rtn['store_id'])
			{
				throw new Exception('serialization error: store id not found for session ' . $this->session->id);
			}
		}

		/* test fails in some legitimate places so remove it
		 *
		if ( !array_key_exists('store_id', $rtn) || !$rtn['store_id']) {
			CLog::RecordIntense("Store serialization issue", "ryan.snook@dreamdinners.com");
			//throw new exception('serialization error: store id not found');
		}
		*/

		//TODO: encrypt
		return serialize($rtn);
	}

	/* The user changed stores we can
	 * remove any items not supported by the store
	 * and leave those that are.
	 */
	function adjustItemsForStore()
	{
	}

	/* The user changed menus :
	 * remember their old selections and restore any previously saved that match the new menu.
	 */
	function adjustItemsForMenu($newMenu, $originalMenu, $previousItems)
	{
		// first create an array of the existing items
		// this will be returned and stored in the cart
		$retval = array();
		if ($this->items && $originalMenu != -1)
		{
			foreach ($this->items as $item)
			{
				list($qty, $mi_obj) = $item;
				$retval[$originalMenu][$mi_obj->id] = $qty;
			}
		}

		$this->clearItems();

		if ($previousItems)
		{
			foreach ($previousItems[$newMenu] as $id => $qty)
			{
				$MenuItem = DAO_CFactory::create('menu_item');
				$MenuItem->id = $id;
				if (!$MenuItem->find(true))
				{
					throw new Exception("Menu item not found");
				}
				else
				{
					$this->addMenuItem($MenuItem, $qty);
				}
			}
		}

		return $retval;
	}

	function reconstruct($useOriginalPricing = false)
	{
		if (empty($this->id))
		{
			return;
		}

		$this->items = null;

		$totalItemQty = 0;

		$store_id = $this->store_id;

		if (empty($this->DAO_store))
		{
			$this->DAO_store = DAO_CFactory::create('store', true);
			$this->DAO_store->id = $store_id;
			$this->DAO_store->find(true);
		}

		if (!empty($this->DAO_store) && ($this->DAO_store->isDistributionCenter()))
		{
			$store_id = $this->DAO_store->parent_store_id;
		}

		$OrderItem = DAO_CFactory::create('order_item', true);
		$OrderItem->query("select
				mmi.menu_id,
				mmi.override_price,
				mimd.markdown_value,
				mimd.id as markdown_id,
				oi.*
				from order_item oi
				left join menu_to_menu_item mmi on mmi.menu_item_id = oi.menu_item_id and mmi.store_id = {$store_id} and mmi.is_deleted = 0
				left join menu_item_mark_down mimd on mimd.id = oi.menu_item_mark_down_id
				where oi.order_id = {$this->id}
				and oi.is_deleted = 0");

		while ($OrderItem->fetch())
		{
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->id = $OrderItem->menu_id;
			$MenuItem = $DAO_menu->findMenuItemDAO(array(
				'menu_item_id_list' => $OrderItem->menu_item_id,
				'join_order_item_order_id' => array($this->id),
				'join_order_item_order' => 'INNER',
				'menu_to_menu_item_store_id' => $store_id,
				'exclude_menu_item_category_core' => false,
				'exclude_menu_item_category_efl' => false,
				'exclude_menu_item_category_sides_sweets' => false
			));

			if (!$MenuItem->fetch())
			{
				throw new Exception("Menu item not found: " . $OrderItem->menu_item_id);
			}
			else
			{

				if (!empty($OrderItem->parent_menu_item_id))
				{
					$MenuItem->parentItemId = $OrderItem->parent_menu_item_id;
					$MenuItem->bundleItemCount = $OrderItem->bundle_item_count;
				}

				if (!empty($OrderItem->bundle_id))
				{
					$MenuItem->bundle_id = $OrderItem->bundle_id;
				}

				if (!empty($OrderItem->markdown_id))
				{
					$MenuItem->markdown_id = $OrderItem->markdown_id;
					$MenuItem->markdown_value = $OrderItem->markdown_value;

					$MenuItem->override_price = $OrderItem->sub_total / $OrderItem->item_count;

					// if not marked down just set the override price to current override price (see below) The price will later be forced to the price in effect at the time
					// of the original order
					// However if a mark down is present we restore the original prices as the value is calculated on the fly in a few places.
					$MenuItem->order_item_ltd_menu_item = false;
				}
				else if (!empty($OrderItem->discounted_subtotal))
				{
					// must be the LTD meal donation if not mark down
					// using round to get to an even dollar amount if the precision has drifted
					$MenuItem->ltd_menu_item_value = round($OrderItem->discounted_subtotal - $OrderItem->sub_total) / $OrderItem->item_count;
					$MenuItem->override_price = $OrderItem->discounted_subtotal / $OrderItem->item_count;
					$MenuItem->order_item_ltd_menu_item = true;
				}
				else
				{
					$MenuItem->markdown_id = false;
					$MenuItem->markdown_value = 0;

					if ($useOriginalPricing)
					{
						$MenuItem->override_price = $OrderItem->sub_total / $OrderItem->item_count;

						// Refresh store_price if using original price
						$MenuItem->getStorePrice();
					}
					else
					{
						$MenuItem->override_price = $OrderItem->override_price;
					}

					$MenuItem->order_item_ltd_menu_item = false;
				}

				$this->addMenuItem($MenuItem, $OrderItem->item_count);
				$totalItemQty += $OrderItem->item_count;
			}
		}

		return $totalItemQty;
	}

	function getAvgCostPerServing()
	{
		return $this->getFoodTotal() / $this->getServingsTotalCount();
	}

	function getAvgCostPerServingCore()
	{
		return $this->getFoodTotal() / $this->getServingsCoreTotalCount();
	}

	function getServingsCoreTotalCount()
	{
		if (!empty($this->servings_core_total_count))
		{
			return $this->servings_core_total_count;
		}

		return 0;
	}

	function getServingsTotalCount()
	{
		if (!empty($this->servings_total_count))
		{
			return $this->servings_total_count;
		}

		return 0;
	}

	function getFoodTotal()
	{
		if (isset($this->bundle_id) && $this->bundle_id > 0)
		{
			return $this->subtotal_menu_items + $this->subtotal_home_store_markup - $this->subtotal_menu_item_mark_down - $this->bundle_discount;
		}
		else
		{
			return $this->subtotal_menu_items + $this->subtotal_home_store_markup - $this->subtotal_menu_item_mark_down;
		}
	}

	function getAvgCostPerServingData()
	{
		$this->average_cost_per_serving_core = null;

		if (!empty($this->pcal_core_total) && !empty($this->servings_core_total_count))
		{
			$this->average_cost_per_serving_core = CTemplate::moneyFormat($this->pcal_core_total / $this->servings_core_total_count);
		}

		return array(
			$this->servings_core_total_count,
			$this->pcal_core_total
		);
	}

	function getCartDisplayArrays()
	{
		$retVal = array();
		$bundleMap = array();

		$totalItemsCost = 0;

		if (!empty($this->bundle->items))
		{
			foreach ($this->bundle->items as $id => $itemInfo)
			{

				if (!empty($itemInfo['chosen']))
				{
					$itemRetreiver = DAO_CFactory::create('menu_item');
					$itemRetreiver->query("select 
						menu_item_description, 
						is_store_special, 
						menu_item_category_id, 
						is_bundle, 
						recipe_id, 
						price, 
						entree_id, 
						servings_per_item, 
						servings_per_container_display, 
						menu_item_category_id, 
						is_store_special,
       					is_preassembled
					from menu_item 
					where id = " . $id);

					$itemRetreiver->fetch();

					$retVal[$id] = array(
						'menu_item_description' => $itemRetreiver->menu_item_description,
						'display_description' => $itemRetreiver->menu_item_description,
						'is_freezer_menu' => (($itemRetreiver->menu_item_category_id > 4 || $itemRetreiver->is_store_special) ? true : false),
						'recipe_id' => $itemRetreiver->recipe_id,
						'menu_item_id' => $id,
						'entree_id' => $itemRetreiver->entree_id,
						'display_title' => $itemInfo["name"],
						'is_visible' => 1,
						'is_preassembled' => $itemRetreiver->is_preassembled,
						'menu_item_name' => $itemInfo["name"],
						'price' => 0,
						'servings_per_item' => $itemRetreiver->servings_per_item,
						'servings_per_container_display' => $itemRetreiver->servings_per_container_display,
						'pricing_type_info' => $itemRetreiver->pricing_type_info,
						'qty' => 1,
						'subtotal' => 0,
						'is_store_special' => $itemRetreiver->is_store_special,
						'is_bundle' => $itemRetreiver->is_bundle,
						'menu_item_category_id' => $itemRetreiver->menu_item_category_id
					);
				}
			}
		}

		if ($this->items)
		{
			foreach ($this->items as $id => $itemInfo)
			{
				if (isset($itemInfo[1]->parentItemId) && is_numeric($itemInfo[1]->parentItemId))
				{
					if (empty($bundleMap[$itemInfo[1]->parentItemId]))
					{
						$bundleMap[$itemInfo[1]->parentItemId] = array();
					}

					$bundleMap[$itemInfo[1]->parentItemId][$id] = $itemInfo[1]->bundleItemCount;
				}

				$retVal[$id] = array(
					'menu_item_description' => $itemInfo[1]->menu_item_description,
					'display_description' => $itemInfo[1]->menu_item_description,
					'is_freezer_menu' => (($itemInfo[1]->menu_item_category_id > 4 || $itemInfo[1]->is_store_special) ? true : false),
					'recipe_id' => $itemInfo[1]->recipe_id,
					'menu_item_id' => $itemInfo[1]->id,
					'entree_id' => $itemInfo[1]->entree_id,
					'display_title' => $itemInfo[1]->menu_item_name,
					'is_visible' => $itemInfo[1]->DAO_menu_to_menu_item->is_visible,
					'is_preassembled' => $itemInfo[1]->is_preassembled,
					'menu_item_name' => $itemInfo[1]->menu_item_name,
					'price' => $this->getStorePrice($this->mark_up, $itemInfo[1], 1, true),
					'servings_per_item' => $itemInfo[1]->servings_per_item,
					'servings_per_container_display' => $itemInfo[1]->servings_per_container_display,
					'pricing_type_info' => $itemInfo[1]->pricing_type_info,
					'qty' => $itemInfo[0],
					'subtotal' => $this->getStorePrice($this->mark_up, $itemInfo[1], $itemInfo[0], true),
					'is_store_special' => $itemInfo[1]->is_store_special,
					'is_bundle' => $itemInfo[1]->is_bundle,
					'menu_item_category_id' => $itemInfo[1]->menu_item_category_id

				);

				if (empty($itemInfo[1]->parentItemId) || empty($this->items[$itemInfo[1]->parentItemId][1]->is_bundle))
				{
					$totalItemsCost += ($retVal[$id]['price'] * $itemInfo[0]);
				}

				if (isset($itemInfo[1]->isPromo) && $itemInfo[1]->isPromo)
				{
					$retVal[$id]['is_promo'] = true;
				}
				else if (isset($itemInfo[1]->isFreeMeal) && $itemInfo[1]->isFreeMeal)
				{
					$retVal[$id]['is_free_meal'] = true;
				}
			}
		}

		// postProcess for bundles
		foreach ($bundleMap as $bid => $bitems)
		{

			if (!isset($retVal[$bid]['subItems']))
			{
				$retVal[$bid]['subItems'] = array();
			}

			foreach ($bitems as $mid => $qty)
			{

				$retVal[$bid]['subItems'][$mid] = $retVal[$mid];

				if ($retVal[$mid]['qty'] > $qty)
				{
					$retVal[$mid]['qty'] -= $qty;
					$retVal[$bid]['subItems'][$mid]['qty'] = $qty;
				}
				else if ($retVal[$mid]['qty'] == $qty)
				{
					unset($retVal[$mid]);
				}
			}
		}

		return array(
			$retVal,
			$totalItemsCost
		);
	}

	function getCondensedMenuItemArray(&$tempMenuItemsArr, &$parentBundleItems)
	{
		if ($this->items)
		{
			foreach ($this->items as $id => $itemInfo)
			{

				if (isset($itemInfo[1]->isPromo) && $itemInfo[1]->isPromo)
				{
					$tempMenuItemsArr['p_' . $id] = $itemInfo[0];
				}
				else if (isset($itemInfo[1]->isFreeMeal) && $itemInfo[1]->isFreeMeal)
				{
					$tempMenuItemsArr['f_' . $id] = $itemInfo[0];
				}
				else
				{
					$tempMenuItemsArr[$id] = $itemInfo[0];
				}

				if ($itemInfo[1]->is_bundle)
				{
					$parentBundleItems[$id] = array();
				}
			}
		}
	}

	function getCondensedProductArray(&$tempProductsArr)
	{
		if ($this->products)
		{
			foreach ($this->products as $id => $itemInfo)
			{
				$tempProductsArr[$id] = $itemInfo[0];
			}
		}
	}

	function getCondensedBundleArray(&$parentBundleItems)
	{
		foreach ($parentBundleItems as $pid => &$childArr)
		{
			// loop through and add children of this bundle
			foreach ($this->items as $id => $itemInfo)
			{
				if ($itemInfo[1]->parentItemId == $pid)
				{
					$childArr[$id] = $itemInfo[1]->bundleItemCount;
				}
			}
		}
	}

	function getCondensedTVOfferBundleArray(&$introBundle, $tempMenuItemsArr = false)
	{
		// TV Offer style bundle
		if (!empty($this->bundle_id) && isset($this->bundle) && !empty($this->bundle->items))
		{
			foreach ($this->bundle->items as $id => $info)
			{
				if ($info['chosen'])
				{
					if (isset($tempMenuItemsArr[$id]))
					{
						$introBundle[$id] = $tempMenuItemsArr[$id];
					}
					else
					{
						$introBundle[$id] = 1;
					}
				}
			}
		}
	}

	static public function sendConfirmationEmail($user, $order, $includeGiftCardMessage = false, $delayedTransaction = false, $shouldSendGiftMessage = true)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$isDeliveredOrder = false;
		if (get_class($order) == "COrdersDelivered")
		{
			$isDeliveredOrder = true;
		}

		$orderInfo = COrders::buildOrderDetailArrays($user, $order, null, true, false, false, $isDeliveredOrder);
		$orderInfo['user'] = $user;
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order_details';
		$orderInfo['customer_primary_email'] = $user->primary_email;
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		$orderCustomization = OrdersCustomization::getInstance($order);
		$orderInfo['meal_customization_string'] = $orderCustomization->mealCustomizationToStringSelectedOnly(',');

		$BookingHistory = CBooking::userBookingHistory($user->id, $orderInfo['storeInfo']['id'], $orderInfo['sessionInfo']['session_start']);

		if (!empty($BookingHistory[$user->id]))
		{
			$orderInfo['booking_history'] = $BookingHistory[$user->id];
		}
		else
		{
			$orderInfo['booking_history'] = array('bookings_made' => 0);
		}

		$orderInfo['show_GC_message'] = $includeGiftCardMessage;

		$DRState = CDreamRewardsHistory::getCurrentStateForUserShortForm($user);

		if ($DRState)
		{
			if (isset($user->dr_downgraded_order_count) && $user->dr_downgraded_order_count > 0)
			{
				if ($user->dr_downgraded_order_count > 1)
				{
					$dgText = "(VIP - 5% off next {$user->dr_downgraded_order_count} qualifying orders)";
				}
				else
				{
					$dgText = "(VIP - 5% off next qualifying order)";
				}

				$DRState['level'] = str_replace("(VIP)", $dgText, $DRState['level']);
			}
			$orderInfo['DRState'] = $DRState;
		}

		$normalSubject = "Order Confirmation";
		$delayedPymentSubject = "Payment Confirmation";

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && ($orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY || $orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY_PRIVATE))
			{
				if ($delayedTransaction)
				{
					$contentsText = CMail::mailMerge('order_delayed_special_event_delivery.txt.php', $orderInfo);
					$contentsHtml = CMail::mailMerge('order_delayed_special_event_delivery.html.php', $orderInfo);
				}
				else
				{
					$contentsText = CMail::mailMerge('order_special_event_delivery.txt.php', $orderInfo);
					$contentsHtml = CMail::mailMerge('order_special_event_delivery.html.php', $orderInfo);
				}
			}
			else if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && $orderInfo['sessionInfo']['session_type_subtype'] == CSession::REMOTE_PICKUP)
			{
				if ($delayedTransaction)
				{
					$contentsText = CMail::mailMerge('order_delayed_special_event_remote_pickup.txt.php', $orderInfo);
					$contentsHtml = CMail::mailMerge('order_delayed_special_event_remote_pickup.html.php', $orderInfo);
				}
				else
				{
					$contentsText = CMail::mailMerge('order_special_event_remote_pickup.txt.php', $orderInfo);
					$contentsHtml = CMail::mailMerge('order_special_event_remote_pickup.html.php', $orderInfo);
				}
			}
			else if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && $orderInfo['sessionInfo']['session_type_subtype'] == CSession::WALK_IN)
			{
				$contentsText = CMail::mailMerge('order_special_event_walk_in.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_special_event_walk_in.html.php', $orderInfo);
			}
			else
			{
				if ($delayedTransaction)
				{
					$contentsText = CMail::mailMerge('order_delayed_special_event.txt.php', $orderInfo);
					$contentsHtml = CMail::mailMerge('order_delayed_special_event.html.php', $orderInfo);
				}
				else
				{
					$contentsText = CMail::mailMerge('order_special_event.txt.php', $orderInfo);
					$contentsHtml = CMail::mailMerge('order_special_event.html.php', $orderInfo);
				}
			}
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::TODD)
		{
			// no longer needed this block for TODD

			$orderInfo['customer_name'] = $user->firstname;

			$invite = DAO_CFactory::create('session_properties');
			$invite->query("select tsp.informal_host_name, CONCAT(u.firstname, ' ', u.lastname) as fullHostName from session_properties tsp " . " join user u on tsp.session_host = u.id where tsp.session_id = {$orderInfo['sessionInfo']['session_id']} ");

			$invite->fetch();
			$orderInfo['hostessName'] = $invite->fullHostName;
			$orderInfo['hostessFirstName'] = $invite->informal_host_name;

			if ($order->isObserveOnly())
			{
				$contentsText = CMail::mailMerge('order_todd_observe.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_todd_observe.html.php', $orderInfo);
			}
			else
			{
				$Items = $order->getItems();
				$dinner = array_shift($Items);
				$orderInfo['dinnerName'] = $dinner[1]->menu_item_name;
				$orderInfo['dinnerPrice'] = COrders::getStorePrice($order->getMarkUp(), $dinner[1]);
				$contentsText = CMail::mailMerge('order_todd.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_todd.html.php', $orderInfo);
			}

			$normalSubject = "Party Confirmation - Taste of Dream Dinners";
			$delayedPymentSubject = "Payment Confirmation - Taste of Dream Dinners";
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::DREAM_TASTE)
		{
			$contentsText = CMail::mailMerge('event_theme/order_dream_taste.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('event_theme/order_dream_taste.html.php', $orderInfo);
			$normalSubject = "RSVP & Payment Confirmation";
			$delayedPymentSubject = "Payment Confirmation - Meal Prep Workshop";
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::FUNDRAISER)
		{
			$contentsText = CMail::mailMerge('event_theme/order_fundraiser.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('event_theme/order_fundraiser.html.php', $orderInfo);
			$normalSubject = "Payment Confirmation";
			$delayedPymentSubject = "Payment Confirmation - Fundraiser";
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::DELIVERED)
		{
			$contentsText = CMail::mailMerge('shipping/shipping_order_confirmation.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('shipping/shipping_order_confirmation.html.php', $orderInfo);

			if (!empty($order->orderAddress->is_gift) && $shouldSendGiftMessage)
			{
				CEmail::sendDeliveredGiftEmail($order);
			}
		}
		else
		{
			if ($delayedTransaction)
			{
				$contentsText = CMail::mailMerge('order_delayed.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_delayed.html.php', $orderInfo);
			}
			else
			{
				$contentsText = CMail::mailMerge('order.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order.html.php', $orderInfo);
			}
		}

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, ($delayedTransaction ? $delayedPymentSubject : $normalSubject), $contentsHtml, $contentsText, '', '', $user->id, ($delayedTransaction ? 'order_delayed' : 'order'));

		// Send the store an email if there are special instructions...
		CEmail::alertStoreInstructions($orderInfo);

		CEmail::alertStoreShiftSetGoOrdered($user, $order);
	}

	static public function sendConfirmationRetryEmail($user, $order, $delayedTransaction = false)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order_details';
		$orderInfo['customer_primary_email'] = $user->primary_email;
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			if ($delayedTransaction)
			{
				$contentsText = CMail::mailMerge('order_delayed_retry.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_delayed_retry.html.php', $orderInfo);
			}
			else
			{
				$contentsText = CMail::mailMerge('order_retry.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_retry.html.php', $orderInfo);
			}
		}
		else
		{
			if ($delayedTransaction)
			{
				$contentsText = CMail::mailMerge('order_delayed_retry.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_delayed_retry.html.php', $orderInfo);
			}
			else
			{
				$contentsText = CMail::mailMerge('order_retry.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_retry.html.php', $orderInfo);
			}
		}

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, ($delayedTransaction ? 'Payment Confirmation' : 'Order Confirmation'), $contentsHtml, $contentsText, '', '', $user->id, ($delayedTransaction ? 'order_delayed' : 'order'));
	}

	static public function sendEditedOrderConfirmationEmail($user, $order)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order_details';
		$orderInfo['customer_primary_email'] = $user->primary_email;
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		if ($user->dream_rewards_version > 2 && ($user->dream_reward_status == 1 || $user->dream_reward_status == 3) && $order->dream_rewards_level > 0)
		{
			// in PP but order is DR=
			$DRState = array(
				'status' => 'Enrolled in PLATEPOINTS',
				'level' => 'N/A',
				'this_order_level' => $order->dream_rewards_level
			);
			$orderInfo['DRState'] = $DRState;
		}

		$subject = "Confirmation of your order changes";

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && ($orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY || $orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY_PRIVATE))
			{
				$contentsText = CMail::mailMerge('edited_order_special_event_delivery.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('edited_order_special_event_delivery.html.php', $orderInfo);
			}
			else if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && $orderInfo['sessionInfo']['session_type_subtype'] == CSession::REMOTE_PICKUP)
			{
				$contentsText = CMail::mailMerge('edited_order_special_event_remote_pickup.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('edited_order_special_event_remote_pickup.html.php', $orderInfo);
			}
			else if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && $orderInfo['sessionInfo']['session_type_subtype'] == CSession::WALK_IN)
			{
				$contentsText = CMail::mailMerge('order_special_event_walk_in.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_special_event_walk_in.html.php', $orderInfo);
			}
			else
			{
				$contentsText = CMail::mailMerge('edited_order_special_event.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('edited_order_special_event.html.php', $orderInfo);
			}
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::DREAM_TASTE)
		{
			$contentsText = CMail::mailMerge('event_theme/edited_order_dream_taste.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('event_theme/edited_order_dream_taste.html.php', $orderInfo);
			$subject = "Confirmation of your order changes";
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::TODD)
		{
			$contentsText = CMail::mailMerge('edited_order_todd.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('edited_order_todd.html.php', $orderInfo);
			$subject = "Confirmation of your order changes";
		}
		else
		{
			$contentsText = CMail::mailMerge('edited_order.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('edited_order.html.php', $orderInfo);
		}

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, $subject, $contentsHtml, $contentsText, '', '', $user->id, 'order');

		// Send the store an email if there are special instructions...
		CEmail::alertStoreInstructions($orderInfo);
	}

	static public function sendEditedOrderConfirmationRetryEmail($user, $order)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order_details';
		$orderInfo['customer_primary_email'] = $user->primary_email;
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		$subject = "Order Confirmation | Order Details Updated";

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			$contentsText = CMail::mailMerge('edited_order_retry.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('edited_order_retry.html.php', $orderInfo);
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::TODD)
		{
			$contentsText = CMail::mailMerge('edited_order_retry.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('edited_order_retry.html.php', $orderInfo);
			$subject = "Party Confirmation | Order Details Updated - Taste of Dream Dinners";
		}
		else
		{
			$contentsText = CMail::mailMerge('edited_order_retry.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('edited_order_retry.html.php', $orderInfo);
		}

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, $subject, $contentsHtml, $contentsText, '', '', $user->id, 'order');
	}

	static public function sendDelayedPaymentEmail($user, $order)
	{
		return self::sendConfirmationEmail($user, $order, false, true);
	}

	static public function sendCancelEmail($user, $order)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order_details';
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		$subject = "Order Canceled";

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && ($orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY || $orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY_PRIVATE))
			{
				$contentsText = CMail::mailMerge('order_cancelled_special_event_delivery.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_cancelled_special_event_delivery.html.php', $orderInfo);
			}
			else if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && $orderInfo['sessionInfo']['session_type_subtype'] == CSession::REMOTE_PICKUP)
			{
				$contentsText = CMail::mailMerge('order_cancelled_special_event_remote_pickup.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_cancelled_special_event_remote_pickup.html.php', $orderInfo);
			}
			else
			{
				$contentsText = CMail::mailMerge('order_cancelled_special_event.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('order_cancelled_special_event.html.php', $orderInfo);
			}
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::DREAM_TASTE)
		{
			$contentsText = CMail::mailMerge('event_theme/order_cancelled_dream_taste.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('event_theme/order_cancelled_dream_taste.html.php', $orderInfo);
			$subject = "Event Canceled - Meal Prep Workshop";
		}
		else if ($orderInfo['sessionInfo']['session_type'] == CSession::TODD)
		{
			$contentsText = CMail::mailMerge('order_cancelled_todd.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('order_cancelled_todd.html.php', $orderInfo);
			$subject = "Party Canceled - Taste of Dream Dinners";
		}
		else
		{
			$contentsText = CMail::mailMerge('order_cancelled.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('order_cancelled.html.php', $orderInfo);
		}

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, $subject, $contentsHtml, $contentsText, '', '', $user->id, 'order_cancelled');
	}

	static public function sendCancelRetryEmail($user, $order)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['details_page'] = 'order_details';
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			$contentsText = CMail::mailMerge('order_cancelled_retry.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('order_cancelled_retry.html.php', $orderInfo);
		}
		else
		{
			$contentsText = CMail::mailMerge('order_cancelled_retry.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('order_cancelled_retry.html.php', $orderInfo);
		}

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, 'Order Canceled', $contentsHtml, $contentsText, '', '', $user->id, 'order_cancelled');
	}

	static public function sendRescheduleEmail($user, $order, $origSessionTime)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['origSessionInfo'] = array('session_start' => $origSessionTime);
		$orderInfo['details_page'] = 'order_details';
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		if ($orderInfo['sessionInfo']['session_type'] == CSession::SPECIAL_EVENT)
		{
			if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && ($orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY || $orderInfo['sessionInfo']['session_type_subtype'] == CSession::DELIVERY_PRIVATE))
			{
				$contentsText = CMail::mailMerge('session_rescheduled_special_event_delivery.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('session_rescheduled_special_event_delivery.html.php', $orderInfo);
			}
			else if (!empty($orderInfo['sessionInfo']['session_type_subtype']) && $orderInfo['sessionInfo']['session_type_subtype'] == CSession::REMOTE_PICKUP)
			{
				$contentsText = CMail::mailMerge('session_rescheduled_special_event_remote_pickup.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('session_rescheduled_special_event_remote_pickup.html.php', $orderInfo);
			}
			else
			{
				$contentsText = CMail::mailMerge('session_rescheduled_special_event.txt.php', $orderInfo);
				$contentsHtml = CMail::mailMerge('session_rescheduled_special_event.html.php', $orderInfo);
			}
		}
		else
		{
			$contentsText = CMail::mailMerge('session_rescheduled.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('session_rescheduled.html.php', $orderInfo);
		}

		$fromEmail = empty($orderInfo['storeInfo']['email_address']) ? null : $orderInfo['storeInfo']['email_address'];

		$Mail->send(null, $fromEmail, $user->firstname . ' ' . $user->lastname, $user->primary_email, 'Order Rescheduled', $contentsHtml, $contentsText, '', '', $user->id, 'session_rescheduled');
	}

	static public function sendPaymentDeclinedEmail($user, $order, $explanation, $doEmailCustomer)
	{
		require_once('CMail.inc');
		$Mail = new CMail();

		$orderInfo = COrders::buildOrderDetailArrays($user, $order);
		$orderInfo['declinedPaymentReason'] = $explanation;
		$orderInfo['sessionInfo'] = array_merge($orderInfo['sessionInfo'], $orderInfo['storeInfo']);//hack
		$orderInfo['plate_points'] = $user->getPlatePointsSummary($order);
		$orderInfo['membership'] = $user->getMembershipStatus($order->id);

		if ($doEmailCustomer)
		{
			$orderInfo['details_page'] = 'order_details';
			$contentsText = CMail::mailMerge('order_delayed_declined.txt.php', $orderInfo);
			$contentsHtml = CMail::mailMerge('order_delayed_declined.html.php', $orderInfo);

			$Mail->send(null, null, $user->firstname . ' ' . $user->lastname, $user->primary_email, 'Payment Declined', $contentsHtml, $contentsText, '', '', $user->id, 'order_delayed_declined');
		}

		$orderInfo['details_page'] = 'admin_order_details';
		// email the fadmin
		$contentsText = CMail::mailMerge('admin_order_delayed_declined.txt.php', $orderInfo);
		$contentsHtml = CMail::mailMerge('admin_order_delayed_declined.html.php', $orderInfo);

		$Mail->send(null, null, $orderInfo['sessionInfo']['store_name'], $orderInfo['sessionInfo']['email_address'], 'Delayed Payment Failure', $contentsHtml, $contentsText, '', '', $user->id, 'admin_order_delayed_declined');
		// TODO: revert to real email addresses

	}

	public function getStore()
	{
		if (!$this->store_id)
		{
			return null;
		}

		if (!is_object($this->store) || empty($this->store) || $this->store->id != $this->store_id)
		{
			$this->store = DAO_CFactory::create('store');
			$this->store->id = $this->store_id;
			$this->store->find(true);
		}

		return $this->store;
	}

	public function getUser()
	{
		if (!$this->user_id)
		{
			return null;
		}

		if (!is_object($this->user) || empty($this->user) || $this->user->id != $this->user_id)
		{
			$this->user = DAO_CFactory::create('user');
			$this->user->id = $this->user_id;
			$this->user->find(true);
		}

		return $this->user;
	}

	public function getPromo()
	{
		if (!$this->promo_code_id)
		{
			return null;
		}

		if (!$this->promo)
		{
			$promo = DAO_CFactory::create('promo_code');
			$promo->id = $this->promo_code_id;
			$promo->find(true);
			$this->promo = $promo;
		}

		return $this->promo;
	}

	public function getCoupon()
	{
		if (empty($this->coupon_code_id))
		{
			return null;
		}

		if (!$this->coupon)
		{
			$coupon = DAO_CFactory::create('coupon_code');
			$coupon->id = $this->coupon_code_id;
			$coupon->find(true);

			// if this is a dream taste, the hostess discount is equal to the cost of the dream taste bundle
			if (isset($this->session) && $coupon->coupon_code == 'HOSTESS' && $this->session->session_type == CSession::DREAM_TASTE)
			{
				$coupon->discount_var = $this->bundle->price;
			}

			$this->coupon = $coupon;
		}

		return $this->coupon;
	}

	/**
	 * Add payments including pending delayed payments and return the total payment amount
	 */
	public function getPaymentsPending($orderIsCancelled = false)
	{

		if (!$this->id)
		{
			return null;
		}

		//get payments
		$payment = DAO_CFactory::create('payment');
		$payment->order_id = $this->id;
		$payment->find();

		$totalPayment = 0;
		while ($payment->fetch())
		{
			switch ($payment->payment_type)
			{
				case CPayment::CHECK:
				case CPayment::CASH:
				case CPayment::GIFT_CERT:
				case CPayment::GIFT_CARD:
				case CPayment::OTHER:
				case CPayment::CREDIT:
				case CPayment::STORE_CREDIT;
					$totalPayment += $payment->total_amount;
					break;

				case CPayment::CC:
					if ((!$payment->is_delayed_payment) || ($payment->is_delayed_payment && (($payment->delayed_payment_status == CPayment::SUCCESS) || ($payment->delayed_payment_status == CPayment::PENDING))))
					{
						if ($orderIsCancelled && $payment->is_delayed_payment && $payment->delayed_payment_status == CPayment::PENDING)
						{
							// skip if pending delayed payments if order is cancelled
							continue 2;
						}
						else
						{
							$totalPayment += $payment->total_amount;
						}
					}
					break;

				case CPayment::REFUND:
				case CPayment::REFUND_STORE_CREDIT:  // LMH... gift cards are always redeemed with STORE CREDIT REFUND
				case CPayment::REFUND_CASH:
				case CPayment::REFUND_GIFT_CARD:
					$totalPayment -= $payment->total_amount;
					break;
			}
		}

		if ($totalPayment < 0.01)
		{
			$totalPayment = 0;
		}

		return $totalPayment;
	}

	private function findOrderItems()
	{

		$order_item = DAO_CFactory::create('order_item');
		$order_item->order_id = $this->id;
		$item = DAO_CFactory::create('menu_item');
		$order_item->joinAdd($item);

		$order_item->find();

		return $order_item;
	}

	public function findSession($findSaved = false, $findCancelled = false)
	{

		if (!empty($this->session))
		{
			return $this->session;
		}

		$session = false;

		if ($this->id)
		{
			if (!$this->session)
			{
				$session = DAO_CFactory::create('session');
				$booking = DAO_CFactory::create('booking');
				$session->selectAdd();
				$session->selectAdd("session.*");
				$booking->order_id = $this->id;
				$session->joinAdd($booking);

				if ($findCancelled)
				{
					$session->whereAdd("booking.status = 'CANCELLED'");
				}
				else if ($findSaved)
				{
					$session->whereAdd("booking.status = 'ACTIVE' or booking.status = 'SAVED'");
				}
				else
				{
					$session->whereAdd("booking.status = 'ACTIVE'");
				}

				if ($session->find(true))
				{
					$this->session = $session;

					$this->setMenuId($session->menu_id);

					return $session;
				}
				else
				{
					return null;
				}
			}
		}
		else
		{
			return null;
		}

		return $session;
	}

	public function findMarkup()
	{

		if ($this->mark_up)
		{
			return $this->mark_up;
		}

		if (!empty($this->mark_up_multi_id))
		{
			$this->mark_up = DAO_CFactory::create('mark_up_multi');
			$this->mark_up->id = $this->mark_up_multi_id;

			if ($this->mark_up->find_includeDeleted(true))
			{
				return $this->mark_up;
			}

			$this->mark_up = null;

			return null;
		}

		return null;
	}

	// order_helper.php
	static public function getVolumeDiscountAmount($menu_id, $markup)
	{
		if ($menu_id <= 73) // september and earlier use the the discount tied tied to menu
		{
			$volume_discount = DAO_CFactory::create('volume_discount_type');
			$volume_discount->menu_id = $menu_id;

			// use first one found - should only be one
			if ($volume_discount->find(true))
			{
				return $volume_discount->discount_value;
			}
		}
		else // the volume reward is tied to markup
			// if there is no markup for the store use a default value of $20
		{
			$discount_amount = false;

			if ($markup)
			{
				$markup_link = DAO_CFactory::create('mark_up_multi_to_volume_discount');
				$markup_link->mark_up_multi_id = $markup->id;

				if ($markup_link->find(true))
				{
					$volume_discount = DAO_CFactory::create('volume_discount_type');
					$volume_discount->id = $markup_link->volume_discount_id;

					if ($volume_discount->find(true))
					{
						$discount_amount = $volume_discount->discount_value;
					}
				}
			}

			// we are looking for false here - a 0 value is legal and must be enforced
			if ($discount_amount === false)
			{
				return 30;
			}

			return $discount_amount;
		}
	}

	static public function getCategoryImageLink($categoryName)
	{
		if (empty($categoryName))
		{
			return "";
		}

		$imageName = "menu_category_" . strtolower(str_replace(" ", "_", $categoryName)) . ".gif";

		return '<img src="' . ADMIN_IMAGES_PATH . '/menu/' . $imageName . '" />';
	}

	static public function getCategoryDescriptions()
	{
		$descriptions = DAO_CFactory::create('menu_item_category');
		$descriptions->selectAdd();
		$descriptions->selectAdd("category_type, category_description");
		$descriptions->find();

		$retVal = array();
		while ($descriptions->fetch())
		{
			$retVal[$descriptions->category_type] = $descriptions->category_description;
		}

		return $retVal;
	}

	static public function getCategoryMap()
	{
		$descriptions = DAO_CFactory::create('menu_item_category');
		$descriptions->selectAdd();
		$descriptions->selectAdd("id, category_type");
		$descriptions->find();

		$retVal = array();
		while ($descriptions->fetch())
		{
			$retVal[$descriptions->id] = $descriptions->category_type;
		}

		return $retVal;
	}

	static public function getBasisAdjustment($menuInfo)
	{
		$retVal = 0;

		foreach ($menuInfo as $subArray)
		{
			if (is_array($subArray))
			{
				foreach ($subArray as $k => $v)
				{
					if (isset($v['is_kids_choice']) && $v['is_kids_choice'] == 1)
					{
						$retVal += $v['price'];
					}
					if (isset($v['is_menu_addon']) && $v['is_menu_addon'] == 1)
					{
						$retVal += $v['price'];
					}
					if (isset($v['is_chef_touched']) && $v['is_chef_touched'] == 1)
					{
						$retVal += $v['price'];
					}
				}
			}
		}

		if ($retVal == 0)
		{
			return null;
		}

		return $retVal;
	}

	static public function averageCostPerServing($orderInfo, $servingsCount = false, $basisAdjustment = null, $servingCountAdjustment = 0)
	{
		$basis = $orderInfo['grand_total'] - $orderInfo['subtotal_all_taxes'] - $orderInfo['misc_food_subtotal'] - $orderInfo['misc_nonfood_subtotal'] - $orderInfo['subtotal_products'];
		if (!empty($orderInfo['pcal_sidedish_total']))
		{
			$basis -= $orderInfo['pcal_sidedish_total'];
		}

		if (!empty($basisAdjustment))
		{
			$basis -= $basisAdjustment;
		}

		$promoAdjustment = 0;
		if (!empty($orderInfo['promo_code_id']))
		{
			$promo = DAO_CFactory::create('promo_code');
			$promo->query("SELECT menu_item.pricing_type FROM promo_code JOIN menu_item ON promo_code.promo_menu_item_id = menu_item.id " . " WHERE promo_code.id = " . $orderInfo['promo_code_id']);
			if ($promo->fetch())
			{
				if ($promo->pricing_type == CMenuItem::HALF)
				{
					$promoAdjustment = 3;
				}
				else
				{
					$promoAdjustment = 6;
				}
			}
		}

		if ($servingsCount)
		{
			$servings = $servingsCount - $promoAdjustment;
		}
		else
		{
			$servings = $orderInfo['servings_total_count'] - $promoAdjustment - $servingCountAdjustment;
		}

		$retVal = 0;

		if ($servings > 0)
		{
			$retVal = COrders::std_round($basis / $servings);
		}

		return $retVal;
	}

	static public function countServings($menuInfo)
	{
		if (!$menuInfo || empty($menuInfo))
		{
			return 0;
		}

		$servingCount = 0;

		foreach ($menuInfo as $categoryName => $subArray)
		{
			if (is_array($subArray))
			{
				foreach ($subArray as $thisItem)
				{
					if (isset($thisItem['servings_per_item']))
					{
						$servingCount += ($thisItem['qty'] * $thisItem['servings_per_item']);
					}
					else
					{
						if ($thisItem['pricing_type'] == CMenuItem::HALF)
						{
							$servingCount += ($thisItem['qty'] * 3);
						}
						else
						{
							$servingCount += ($thisItem['qty'] * 6);
						}
					}
				}
			}
		}

		return $servingCount;
	}

	static public function getItemDiscountedPrice($price, $numServings, $size, $markup)
	{
		$discountedPrice = $price;

		if (!isset($markup))
		{
			$markup = 1.0;
		}

		if ($numServings >= 72)
		{
			$discountedPrice = $price - (1.29 * $markup * $size);
		}
		else if ($numServings >= 66)
		{
			$discountedPrice = $price - (1.08 * $markup * $size);
		}
		else if ($numServings >= 60)
		{
			$discountedPrice = $price - (0.86 * $markup * $size);
		}
		else if ($numServings >= 54)
		{
			$discountedPrice = $price - (0.65 * $markup * $size);
		}
		else if ($numServings >= 48)
		{
			$discountedPrice = $price - (0.44 * $markup * $size);
		}
		else if ($numServings >= 42)
		{
			$discountedPrice = $price - (0.23 * $markup * $size);
		}

		return $discountedPrice;
	}

	static public function getCustomerActionStringFrom($FullyQualifiedOrderType)
	{
		switch ($FullyQualifiedOrderType)
		{
			case 'STANDARD_WALK_IN':
				return 'Walk-In';
			case 'STARTER_PACK':
			case 'DREAM_TASTE_STANDARD':
			case 'DREAM_TASTE_OPEN_HOUSE':
			case 'DREAM_TASTE_FRIENDS_NIGHT_OUT':
			case 'FUNDRAISER':
			case 'STANDARD':
				return 'In-Store Assembly';
			case 'STARTER_PACK_MFY':
			case 'DREAM_TASTE_STANDARD_CURBSIDE':
			case 'DREAM_TASTE_OPEN_HOUSE_CURBSIDE':
			case 'DREAM_TASTE_OPEN_HOUSE_HOLIDAY':
			case 'FUNDRAISER_CURBSIDE':
			case 'STANDARD_MFY':
				return 'Pick Up';
			case 'STANDARD_DELIVERY':
			case 'STARTER_PACK_DELIVERY':
			case 'STARTER_PACK_DELIVERY_PRIVATE':
			case 'STANDARD_DELIVERY_PRIVATE':
				return 'Home Delivery';
			case 'STANDARD_REMOTE_PICKUP_PRIVATE':
			case 'STANDARD_REMOTE_PICKUP':
				return 'Community Pick Up';
			case 'QUICK_SIX':
			case 'TODD':
				return '';
		}

		return '';
	}

	static public function getFullyQualifiedOrderTypeFromSession($sessionObj)
	{
		$fadminAcronym = $sessionObj->fetchFadminAcronym();
		$FullyQualifiedOrderType = self::getFullyQualifiedOrderTypeFrom(null, $sessionObj->session_type, $sessionObj->session_type_subtype, false, $fadminAcronym);

		return self::getCustomerActionStringFrom($FullyQualifiedOrderType);
	}

	static public function getFullyQualifiedOrderTypeFrom($OrderDetailsArray, $session_type = false, $session_subtype = false, $booking_type = false, $Fadmin_Acronym = false)
	{
		if ($OrderDetailsArray)
		{
			$session_type = $OrderDetailsArray['sessionInfo']['session_type'];
			$session_subtype = $OrderDetailsArray['sessionInfo']['session_type_subtype'];
			$booking_type = $OrderDetailsArray['bookingInfo']['booking_type'];
			$Fadmin_Acronym = $OrderDetailsArray['sessionInfo']['dream_taste_theme_fadmin_acronym'];
		}

		if ($session_type == CSession::STANDARD)
		{
			if ($booking_type == 'INTRO')
			{
				return 'STARTER_PACK';
			}
			else
			{
				return 'STANDARD';
			}
		}
		else if ($session_type == CSession::SPECIAL_EVENT)
		{
			if ($session_subtype == 'DELIVERY' || $session_subtype == 'DELIVERY_PRIVATE')
			{
				if ($booking_type == 'INTRO')
				{
					return 'STARTER_PACK_DELIVERY';
				}
				else
				{
					return 'STANDARD_DELIVERY';
				}
			}
			else if ($session_subtype == CSession::WALK_IN)
			{
				return 'STANDARD_WALK_IN';
			}
			else if ($session_subtype == 'DELIVERY_PRIVATE')
			{
				if ($booking_type == 'INTRO')
				{
					return 'STARTER_PACK_DELIVERY_PRIVATE';
				}
				else
				{
					return 'STANDARD_DELIVERY_PRIVATE';
				}
			}
			else if ($session_subtype == 'REMOTE_PICKUP')
			{
				return 'STANDARD_REMOTE_PICKUP';
			}
			else if ($session_subtype == 'REMOTE_PICKUP_PRIVATE')
			{
				return 'STANDARD_REMOTE_PICKUP_PRIVATE';
			}
			else
			{
				if ($booking_type == 'INTRO')
				{
					return 'STARTER_PACK_MFY';
				}
				else
				{
					return 'STANDARD_MFY';
				}
			}
		}
		else if ($session_type == CSession::DREAM_TASTE)
		{
			// TODO
			switch ($Fadmin_Acronym)
			{
				case 'DT':
				case 'MPW':
				case 'DDT': // DONATED
				case 'MPWD': // DONATED
					return 'DREAM_TASTE_STANDARD';
				case 'OH':
				case 'HPE': // HOLIDAY PICKUP
				case 'OHH': // HOLIDAY PICKUP
					return 'DREAM_TASTE_OPEN_HOUSE';
				case 'FNO':
					return 'DREAM_TASTE_FRIENDS_NIGHT_OUT';
				case 'OHC':
					return 'DREAM_TASTE_OPEN_HOUSE_CURBSIDE';
				case 'MPWC':
					return 'DREAM_TASTE_STANDARD_CURBSIDE';
				case 'FNOC':
					return 'FRIENDS_NIGHT_OUT_CURBSIDE';
				default:
					return 'DREAM_TASTE_STANDARD';
			}
		}
		else if ($session_type == CSession::DELIVERED)
		{
			return 'DELIVERED';
		}
		else if ($session_type == CSession::FUNDRAISER)
		{
			//TODO
			if ($Fadmin_Acronym == 'FC')
			{
				return 'FUNDRAISER_CURBSIDE';
			}

			return 'FUNDRAISER';
		}
		else if ($session_type == CSession::TODD)
		{
			return 'TODD';
		}
	}

	/**
	 * returns    array('orderInfo'=>$orderArray, 'menuInfo'=>$menuInfo, 'paymentInfo'=>$PaymentInfo,'sessionInfo'=>$sessionData,'customer_name'=>$customerName, 'bookingStatus'=>$booking->status, 'storeInfo'=>$storeInfo);
	 */
	static public function buildOrderDetailArrays($User, $Order, $viewing_user_type = null, $flatItemsList = false, $getBundleList = false, $allowDeletedSession = false, $isDelivered = false, $orderBy = 'FeaturedFirst')
	{
		$booking = DAO_CFactory::create('booking');
		$session = DAO_CFactory::create('session');

		$booking->order_id = $Order->id;
		$booking->whereAdd();

		// get the most recent booking row for this order
		$booking->orderBy("booking.timestamp_created desc");

		if (!$booking->find(true))
		{
			throw new Exception('Booking not found in buildOrderDetailArrays(); OrderID = ' . $Order->id);
		}

		$session->id = $booking->session_id;

		if ($allowDeletedSession)
		{
			$session->is_deleted = null;
		}

		if (!$session->find(true))
		{
			throw new Exception('Session not found in buildOrderDetailArrays()');
		}

		$menu_id = $session->menu_id;

		$customerName = $User->getName();

		$user_type = isset($viewing_user_type) ? $viewing_user_type : $User->user_type;

		// ---------------------------------- get payment info.. might be 1 to n records...

		$cancelled = ($booking->status == CBooking::CANCELLED);

		$PaymentInfo = self::buildPaymentInfoArray($booking->order_id, $user_type, $session->session_start, $cancelled, $isDelivered);

		// --------------------------------- get store name
		$Store = DAO_CFactory::create('store');
		$Store->id = $session->store_id;
		$Store->find(true);
		$Order->store = clone $Store;
		$storeInfo = $Store->toArray();
		$storeInfo['map'] = $Store->generateMapLink();
		$storeInfo['linear_address'] = $Store->generateLinearAddress();
		$storeInfo['image_name'] = $Store->getStoreImageName();
		$storeInfo['PHPTimeZone'] = CTimezones::getPHPTimeZoneFromID($Store->timezone_id);

		$promo = null;
		//look for promo code
		if ($Order->family_savings_discount_version == 2 && !empty($Order->promo_code_id))
		{
			$promo = $Order->getPromo();
		}

		$coupon = null;
		$freemeal = null;
		$hasServiceFeeCoupon = false;
		//look for promo code
		if (!empty($Order->coupon_code_id))
		{
			$coupon = $Order->getCoupon();
			if ($coupon->discount_method == CCouponCode::FREE_MEAL && $coupon->menu_item_id)
			{
				$freemeal = $coupon;
			}

			$UCCode = strtoupper($coupon->coupon_code);

			if ($coupon && !empty($coupon->limit_to_mfy_fee))
			{
				$hasServiceFeeCoupon = true;
			}
		}

		$canEditDeliveredOrder = false;
		if ($session->session_type == CSession::DELIVERED)
		{
			$menuInfo = COrdersDelivered::buildOrderItemsArray($Order, $promo, $freemeal, $flatItemsList);
			$canEditDeliveredOrder = COrdersDelivered::canEditDeliveredOrder($Order);
		}
		else
		{
			$menuInfo = self::buildOrderItemsArray($Order, $promo, $freemeal, $flatItemsList, $orderBy);
		}

		$menuInfo['menu_id'] = $menu_id;
		$menuInfo['markup_discount_scalar'] = 1.0;
		if ($Order->family_savings_discount_version != 2)
		{
			if (isset($Store) && $Store)
			{
				if (!isset($markup))
				{
					$markup = $Store->getMarkUpObj($menu_id);
				}

				if (isset($markup) && $markup->markup_type == CMarkUp::PERCENTAGE)
				{
					$menuInfo['markup_discount_scalar'] = ($markup->markup_value / 100) + 1.0;
				}
			}
		}

		$orderArray = $Order->toArray();

		$orderArray['orderAddress'] = $Order->orderAddress()->toArray();

		if ($isDelivered)
		{
			$orderArray['orderShipping'] = $Order->orderShipping()->toArray();
		}

		$foodPortionOfPPCredit = 0;
		$feePortionOfPPCredit = 0;

		if ($orderArray['points_discount_total'] > 0)
		{
			$Order->getPointCredits($foodPortionOfPPCredit, $feePortionOfPPCredit, true, false, $hasServiceFeeCoupon);
		}

		$orderArray['points_discount_total_food'] = $foodPortionOfPPCredit;
		$orderArray['points_discount_total_fee'] = $feePortionOfPPCredit;

		$coupon = $Order->getCoupon();
		if ($coupon)
		{
			$orderArray['coupon_title'] = $coupon->coupon_code_short_title;
		}

		if ($Order->family_savings_discount_version != 2 && $Order->isFamilySavingsOrder($menuInfo))
		{
			// Calculate and add average per serving cost
			$numberServings = self::countServings($menuInfo);
			$adjustedSubTotal = $Order->subtotal_menu_items + $Order->subtotal_home_store_markup - $Order->family_savings_discount - $Order->subtotal_menu_item_mark_down + $Order->subtotal_ltd_menu_item_value;
			$orderArray['average_per_serving_cost'] = $numberServings != 0 ? $adjustedSubTotal / $numberServings : 0;
			$orderArray['number_servings'] = $numberServings;
		}

		$sessionData = CSession::getSessionDetail($session->id, false);
		$sessionData['session_start'] = $session->session_start;
		$sessionData['duration_minutes'] = $session->duration_minutes;
		$sessionData['session_end'] = date("Y-m-d H:i:s", strtotime($session->session_start) + $session->duration_minutes * 60);
		$sessionData['id'] = $booking->session_id;
		$sessionData['session_id'] = $booking->session_id;
		$sessionData['session_type'] = $session->session_type;
		$sessionData['session_type_subtype'] = $session->session_type_subtype;
		$sessionData['session_type_text'] = str_replace('_', ' ', $session->session_type);
		$sessionData['session_is_deleted'] = !empty($session->is_deleted);

		$timeNow = CTimezones::getAdjustedServerTime($Store->timezone_id);
		$sessionData['is_past'] = false;
		if ($timeNow > strtotime($session->session_start))
		{
			$sessionData['is_past'] = true;
		}

		if ($session->session_type == CSession::SPECIAL_EVENT)
		{
			if ($session->isDelivery())
			{
				$sessionData['session_type_text'] = 'DELIVERY';
			}
			else
			{
				$sessionData['session_type_text'] = 'MADE FOR YOU';
			}
		}

		if (isset($session->session_discount_id) && is_numeric($session->session_discount_id) && $session->session_discount_id > 0)
		{
			$sessionData['session_type_text'] .= " (Discounted)";
		}

		$sessionData['is_intro'] = $booking->booking_type == 'INTRO' ? true : false;
		$sessionData['remaining_slots'] = $session->getRemainingSlots();
		$sessionData['remaining_intro_slots'] = $session->getRemainingIntroSlots();

		$orderArray['is_menu_sampler'] = false; //$menuInfo['is_menu_sampler'];

		$BundleItems = array();
		$relocatedBundleItems = false;

		if ($sessionData['session_type'] == CSession::DELIVERED)
		{
			$orderArray['boxes'] = $menuInfo;
		}

		$BundleInfo = array();
		if (!empty($Order->bundle_id) && $Order->bundle_id > 0)
		{
			$Bundle = DAO_CFactory::create('bundle');
			$Bundle->id = $Order->bundle_id;
			$Bundle->find(true);

			$relocatedBundleItems = array();

			// Bundle Price Intercept Point

			if ($Store->is_corporate_owned && $menu_id > 176 && $menu_id <= 184)
			{
				if ($Bundle->bundle_type == CBundle::DREAM_TASTE)
				{
					$Bundle->price = 34.99;
				}
				else if ($Bundle->bundle_type == CBundle::TV_OFFER)
				{
					$Bundle->price = 84.95;
				}
			}

			$BundleInfo = $Bundle->toArray();

			$relocatedBundleItems = array();

			$subItemization = "<br /><div style=\"margin-left:10px;\"><ul style=\"padding:5px;\">";

			foreach ($menuInfo as $categoryName => &$subArray)
			{
				if (is_array($subArray))
				{
					foreach ($subArray as $id => &$item)
					{
						if ($Bundle->bundle_type == 'TV_OFFER')
						{

							if (is_numeric($id) && $item['qty'] && isset($item['bundle_id']) && $item['bundle_id'] > 0)
							{
								$item['qty'] = $item['qty'] - 1;
								$BundleItems[$id] = $menuInfo[$categoryName][$id];

								if ($item['qty'] == 0)
								{
									$relocatedBundleItems[$id] = $menuInfo[$categoryName][$id];
									$relocatedBundleItems[$id]['qty'] = 1;
									unset($menuInfo[$categoryName][$id]);
								}

								$BundleItems[$id]['qty'] = 1;
								$subItemization .= '<li>' . (($item['servings_per_item'] == '3') ? 'Medium' : 'Large') . ': ' . $item['display_title'] . '</li>';
							}
						}
						else
						{

							if (is_numeric($id) && $item['qty'] && isset($item['bundle_id']) && $item['bundle_id'] > 0)
							{
								$BundleItems[$id] = $menuInfo[$categoryName][$id];
								$relocatedBundleItems[$id] = $menuInfo[$categoryName][$id];
								unset($menuInfo[$categoryName][$id]);

								$subItemization .= '<li>' . $item['qty'] . ' ' . (($item['servings_per_item'] == '3') ? 'Medium' : 'Large') . ': ' . $item['display_title'] . '</li>';
							}
						}
					}
				}
			}
			$subItemization .= "</ul></div>";

			if ($flatItemsList)
			{
				$menuInfo['itemList'][$Order->bundle_id] = array(
					'qty' => 1,
					'price' => $Bundle->price,
					'isBundle' => true,
					'is_chef_touched' => false,
					'pricing_type' => 'HALF',
					'servings_per_item' => $Bundle->number_servings_required,
					'display_title' => $Bundle->bundle_name . $subItemization
				);

				$menuInfo['bundle_items'] = $BundleItems;
			}
			else
			{
				$menuInfo[$Bundle->bundle_name][$Order->bundle_id] = array(
					'qty' => 1,
					'price' => $Bundle->price,
					'pricing_type' => 'HALF',
					'servings_per_item' => $Bundle->number_servings_required,
					'display_title' => $Bundle->bundle_name . $subItemization
				);
			}
		}

		// Calculate Cost Per Serving
		if ($flatItemsList)
		{
			$totalQualifyingServings = 0;
			$totalEntreeCost = 0;
			$totalCTSCost = 0;

			foreach ($menuInfo['itemList'] as $id => $itemData)
			{
				if (!empty($itemData['is_chef_touched']))
				{
					$totalCTSCost += ($itemData['price'] * $itemData['qty']);
				}
				else
				{
					// if the order is delivered and it's a side, use the servings_per_container_display to calculate total serving average
					if ($isDelivered && $itemData['servings_per_item'] == 1 && !empty($itemData['servings_per_container_display']))
					{
						$totalQualifyingServings += ($itemData['servings_per_container_display'] * $itemData['qty']);
					}
					else
					{
						$totalQualifyingServings += ($itemData['servings_per_item'] * $itemData['qty']);
					}

					$totalEntreeCost += ($itemData['price'] * $itemData['qty']);
				}
			}

			if ($totalCTSCost > 0 && !empty($Order->subtotal_food_sales_taxes))
			{
				$pretax = $Order->grand_total - $Order->subtotal_food_sales_taxes;
				$CTSPotionOfPretax = $totalCTSCost / $pretax;
				$CTSPotionOfTax = $Order->subtotal_food_sales_taxes * $CTSPotionOfPretax;

				$totalCTSCost += $CTSPotionOfTax;
			}

			$totalCost = $Order->grand_total - $totalCTSCost;

			if ($totalQualifyingServings > 0)
			{
				$menuInfo['cost_per_serving'] = CTemplate::moneyFormat($totalCost / $totalQualifyingServings);
				$menuInfo['servings_total_count_display'] = $totalQualifyingServings;
			}
			else
			{
				$menuInfo['cost_per_serving'] = 0;
				$menuInfo['servings_total_count_display'] = 0;
			}
		}

		$retVal = array(
			'orderInfo' => $orderArray,
			'menuInfo' => $menuInfo,
			'bookingInfo' => $booking->toArray(),
			'paymentInfo' => $PaymentInfo,
			'sessionInfo' => $sessionData,
			'customer_name' => $customerName,
			'bookingStatus' => $booking->status,
			'storeInfo' => $storeInfo,
			'bundleInfo' => $BundleInfo,
			'canEditDeliveredOrder' => $canEditDeliveredOrder
		);

		if ($getBundleList)
		{
			$retVal['RelocatedItems'] = $relocatedBundleItems;
		}

		return $retVal;
	}

	public static function buildPaymentInfoArray($order_id, $user_type, $sessionTime = false, $orderIsCancelled = false, $orderIsDelivered = false)
	{
		// ---------------------------------- get payment info.. might be 1 to n records...

		//TODO: this code is weird, seems like all those title strings should be in the template.
		//The title is not meant neces.. for display title, was just trying to make this a bit easier to undestand when
		//on the page view side...
		//A customer shouldn't be able to see things like Reference IDs or ADMIN comments
		//USER type == CUSTOMER then don't show those details... everyone else okay.. SITE ADMIN, FRANCHISE OWNER, FRANCHISE EMPLOYEE?
		$PaymentTopArr = array();
		$PaymentTopArr['canAutoAdjust'] = false;
		$PaymentTopArr['paymentsTotal'] = 0.0;
		$PaymentTopArr['payment_count'] = 0;
		$Payment = DAO_CFactory::create('payment');
		$Payment->order_id = $order_id;
		$Payment->find();
		$counter = 0;
		while ($Payment->fetch())
		{
			$counter++;
			$PaymentArr = array();

			$PaymentTopArr['payment_count']++;
			$PaymentArr['id'] = $Payment->id;
			$PaymentArr['payment_type'] = $Payment->payment_type;

			if ($Payment->is_delayed_payment)
			{
				$PaymentArr['is_delayed_payment'] = $Payment->is_delayed_payment;
				$PaymentArr['delayed_payment_status'] = $Payment->delayed_payment_status;
			}

			$PaymentArr['paymentDate'] = array(
				'title' => 'Payment Date',
				'other' => CTemplate::dateTimeFormat($Payment->timestamp_created, NORMAL, $Payment->store_id, false)
			);

			if (!$Payment->is_delayed_payment || ($Payment->is_delayed_payment && $Payment->delayed_payment_status != CPayment::FAIL && $Payment->delayed_payment_status != CPayment::CANCELLED))
			{
				if ($PaymentArr['payment_type'] == 'REFUND' || $PaymentArr['payment_type'] == 'REFUND_CASH' || $PaymentArr['payment_type'] == 'REFUND_STORE_CREDIT' || $PaymentArr['payment_type'] == 'REFUND_GIFT_CARD')
				{
					$PaymentTopArr['paymentsTotal'] -= $Payment->total_amount;
				}
				else
				{
					$PaymentTopArr['paymentsTotal'] += $Payment->total_amount;
				}
			}

			if ($PaymentArr['payment_type'] == 'CC')
			{

				if (isset($Payment->payment_transaction_number) && $Payment->payment_transaction_number)
				{
					$PaymentTopArr['canAutoAdjust'] = true;
				}

				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Credit Card Payment'
				);
				if ($Payment->is_delayed_payment)
				{
					$PaymentArr['payment_info'] = array(
						'title' => 'Payment Type',
						'other' => 'Delayed CC Payment'
					);
				}
				if (isset($Payment->credit_card_type))
				{
					$PaymentArr['credit_card_type'] = array(
						'title' => 'Credit Card Type',
						'other' => $Payment->credit_card_type
					);
				}
				if (isset($Payment->payment_number))
				{
					$PaymentArr['payment_number'] = array(
						'title' => 'Credit Card Number',
						'other' => $Payment->payment_number
					);
				}
				if (isset($Payment->payment_transaction_number))
				{
					$PaymentArr['payment_reference'] = array(
						'title' => 'Credit Card Ref',
						'other' => $Payment->payment_transaction_number
					);
				}
			}
			else if ($PaymentArr['payment_type'] == 'OTHER')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'In Store Purchase/Other'
				);
			}
			else if ($PaymentArr['payment_type'] == 'GIFT_CERT')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Gift Certificate'
				);
				$PaymentArr['gift_certificate_type'] = array(
					'title' => 'Gift Certificate Type',
					'other' => $Payment->gift_cert_type
				);

				if ($user_type != CUser::CUSTOMER && isset ($Payment->gift_certificate_number))
				{
					$PaymentArr['gift_cert_id'] = array(
						'title' => 'Gift Certificate Number',
						'other' => $Payment->gift_certificate_number
					);
					if ($Payment->payment_number)
					{
						$PaymentArr['payment_number'] = array(
							'title' => 'Payment Number',
							'other' => $Payment->payment_number
						);
					}
					else
					{
						$PaymentArr['payment_number'] = array(
							'title' => 'Payment Number',
							'other' => '--'
						);
					}
				}
			}
			else if ($PaymentArr['payment_type'] == 'CASH')
			{
				if ($Payment->is_migrated == true)
				{
					$PaymentArr['payment_info'] = array(
						'title' => 'Payment Type',
						'other' => 'Cash/Check'
					);
				}
				else
				{
					$PaymentArr['payment_info'] = array(
						'title' => 'Payment Type',
						'other' => 'Cash'
					);
					if ($Payment->payment_number)
					{
						$PaymentArr['payment_number'] = array(
							'title' => 'Payment Number',
							'other' => $Payment->payment_number
						);
					}
					else
					{
						$PaymentArr['payment_number'] = array(
							'title' => 'Payment Number',
							'other' => '--'
						);
					}
				}
			}
			else if ($PaymentArr['payment_type'] == 'CHECK')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Check'
				);
				$PaymentArr['payment_number'] = array(
					'title' => 'Check Number',
					'other' => $Payment->payment_number
				);
			}
			else if ($PaymentArr['payment_type'] == 'CREDIT')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'No Charge'
				);
			}
			else if ($PaymentArr['payment_type'] == 'REFUND')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Refund'
				);
				if (isset($Payment->credit_card_type))
				{
					$PaymentArr['credit_card_type'] = array(
						'title' => 'Credit Card Type',
						'other' => $Payment->credit_card_type
					);
				}
				if (isset($Payment->payment_number))
				{
					$PaymentArr['payment_number'] = array(
						'title' => 'Credit Card Number',
						'other' => $Payment->payment_number
					);
				}
				if (isset($Payment->referent_id))
				{
					$PaymentArr['original_payment'] = array(
						'title' => 'Original Payment ID',
						'other' => $Payment->referent_id
					);
				}
			}
			else if ($PaymentArr['payment_type'] == 'REFUND_CASH')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Refund Cash'
				);
				if ($Payment->payment_number)
				{
					$PaymentArr['payment_number'] = array(
						'title' => 'Payment Number',
						'other' => $Payment->payment_number
					);
				}
				else
				{
					$PaymentArr['payment_number'] = array(
						'title' => 'Payment Number',
						'other' => '--'
					);
				}
			}
			else if ($PaymentArr['payment_type'] == 'STORE_CREDIT' || $PaymentArr['payment_type'] == 'GIFT_CARD')
			{
				if (isset($Payment->payment_number))
				{
					$PaymentArr['payment_info'] = array(
						'title' => 'Payment Type',
						'other' => 'Dream Dinners Gift Card'
					);
					$number = $Payment->payment_number;
					if (strlen($number) == 4)
					{
						$number = str_pad($number, 16, "X", STR_PAD_LEFT);
					}
					$PaymentArr['payment_number'] = array(
						'title' => 'Gift Card Number',
						'other' => $number
					);
				}
				else
				{
					$PaymentArr['payment_info'] = array(
						'title' => 'Payment Type',
						'other' => 'Store Credit'
					);
				}
			}
			else if ($PaymentArr['payment_type'] == 'REFUND_STORE_CREDIT')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Store Credit Refund'
				);
				if (isset($Payment->payment_number))
				{
					$number = $Payment->payment_number;
					if (strlen($number) == 4)
					{
						$number = str_pad($number, 16, "X", STR_PAD_LEFT);
					}
					$PaymentArr['payment_number'] = array(
						'title' => 'Gift Card Number',
						'other' => $number
					);
				}
			}
			else if ($PaymentArr['payment_type'] == 'PAY_AT_SESSION')
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Guest will Pay at Session'
				);
			}
			else if ($PaymentArr['payment_type'] == 'REFUND_GIFT_CARD')
			{
				if ($orderIsDelivered)
				{
					$PaymentArr['payment_info'] = array(
						'title' => 'Payment Type',
						'other' => 'Gift Card Refund (loaded back on Gift Card)'
					);
				}
				else
				{
					$PaymentArr['payment_info'] = array(
						'title' => 'Payment Type',
						'other' => 'Gift Card Refund (refunded as Store Credit)'
					);
				}

				if (isset($Payment->payment_number))
				{
					$number = $Payment->payment_number;
					if (strlen($number) == 4)
					{
						$number = str_pad($number, 16, "X", STR_PAD_LEFT);
					}
					$PaymentArr['payment_number'] = array(
						'title' => 'Gift Card Number',
						'other' => $number
					);
				}
			}
			else
			{
				$PaymentArr['payment_info'] = array(
					'title' => 'Payment Type',
					'other' => 'Other Payment Type'
				);
			}

			if ($Payment->is_delayed_payment)
			{
				if ($user_type != CUser::CUSTOMER)
				{
					//$PaymentArr['delayed_info'] = array('title' => 'Delayed Payment', 'other' => 'TRUE');
					$PaymentArr['delayed_tran_num'] = array(
						'title' => 'Delayed Transaction Number',
						'other' => $Payment->delayed_payment_transaction_number
					);
				}
				if ($Payment->delayed_payment_transaction_date)
				{
					$PaymentArr['delayed_date'] = array(
						'title' => 'Delayed Payment Date',
						'other' => CTemplate::dateTimeFormat($Payment->delayed_payment_transaction_date)
					);
				}
				else
				{
					$dateStr = 'Scheduled';
					$sessionStartTS = strtotime($sessionTime);
					if (!empty($sessionTime))
					{
						$dateStr = date("n-j-Y", mktime(0, 0, 0, date("n", $sessionStartTS), date("j", $sessionStartTS) - 5, date("Y", $sessionStartTS)));
					}

					if ($orderIsCancelled)
					{
						$PaymentArr['delayed_date'] = array(
							'title' => 'Delayed Payment Date',
							'other' => 'NA'
						);
					}
					else
					{
						$PaymentArr['delayed_date'] = array(
							'title' => 'Delayed Payment Date',
							'other' => 'Scheduled for ' . $dateStr
						);
					}

					$PaymentArr['delayed_tran_num'] = array(
						'title' => 'Delayed Transaction Number',
						'other' => 'TBD'
					);
				}

				if ($Payment->delayed_payment_status == CPayment::PENDING)
				{
					if ($orderIsCancelled)
					{
						$PaymentArr['delayed_status'] = array(
							'title' => 'Delayed Payment Status',
							'other' => 'Status: Payment Canceled'
						);
					}
					else
					{
						$PaymentArr['delayed_status'] = array(
							'title' => 'Delayed Payment Status',
							'other' => 'Status: Payment Scheduled'
						);
					}
				}
				else if ($Payment->delayed_payment_status == CPayment::SUCCESS)
				{
					$PaymentArr['delayed_status'] = array(
						'title' => 'Delayed Payment Status',
						'other' => 'Status: Payment Processed'
					);
				}
				else
				{
					$PaymentArr['delayed_status'] = array(
						'title' => 'Delayed Payment Status',
						'other' => 'Status: ' . $Payment->delayed_payment_status
					);

					if ($Payment->delayed_payment_status == CPayment::CANCELLED)
					{
						$PaymentArr['last_modified_date'] = $Payment->timestamp_updated;
					}
				}

				// everyone else can see this info.. store owner, employee and fadmin.. but not a customer
				if (($user_type != CUser::CUSTOMER) && $Payment->payment_transaction_number)
				{
					$PaymentArr['payment_transaction_number'] = array(
						'title' => 'Transaction ID',
						'other' => $Payment->payment_transaction_number
					);
				}
			}
			else
			{

				// everyone else can see this info.. store owner, employee and fadmin.. but not a customer
				if (($user_type != CUser::CUSTOMER) && $Payment->payment_transaction_number)
				{
					if ($PaymentArr['payment_type'] == 'GIFT_CARD')
					{
						$PaymentArr['payment_transaction_number'] = array(
							'title' => 'Transaction ID',
							'other' => $Payment->payment_transaction_number
						);
					}
					else
					{
						$PaymentArr['payment_transaction_number'] = array(
							'title' => 'Transaction ID',
							'other' => $Payment->payment_transaction_number
						);
					}
				}
			}

			$PaymentArr['total'] = array(
				'title' => 'Payment Total',
				'other' => $Payment->total_amount
			);

			if ($Payment->payment_note)
			{
				$PaymentArr['payment_note'] = array(
					'title' => 'Payment Notes',
					'other' => $Payment->payment_note
				);
			}
			if (($user_type != CUser::CUSTOMER) && $Payment->admin_note)
			{
				$PaymentArr['admin_note'] = array(
					'title' => 'Administrative Notes',
					'other' => $Payment->admin_note
				);
			}

			if ($Payment->is_deposit)
			{
				$PaymentArr['deposit'] = array(
					'title' => 'Non-refundable Deposit',
					'other' => 'yes'
				);
			}
			$PaymentArr['payment_type'] = $Payment->payment_type;

			$PaymentTopArr[$counter] = $PaymentArr;
		}

		return $PaymentTopArr;
	}

	public static function buildStoreInfoArray($storeObj)
	{

		$storeInfo = $storeObj->toArray();
		$storeInfo['image_name'] = $storeObj->getStoreImageName();
		$storeInfo['map_link'] = $storeObj->generateMapLink();
		$storeInfo['linear_address'] = $storeObj->generateLinearAddress();

		return $storeInfo;
	}

	/**
	 * Tries to find a store object from the $_REQUEST unless an id is passed in
	 */
	public static function buildStore($id = false)
	{

		$daoStore = null;
		$tpl = CApp::instance()->template();
		if ($id || isset($_REQUEST['store']))
		{
			if ($id || ($_REQUEST['store'] && is_numeric($_REQUEST['store'])))
			{
				$daoStore = DAO_CFactory::create('store');
				$daoStore->id = ($id ? $id : $_REQUEST['store']);
				$num = $daoStore->find(true);
				if ($num !== 1)
				{
					$daoStore = null;
					$tpl->setErrorMsg('The selected store could not be found');
				}
			}
		}
		else if (CBrowserSession::getCurrentFadminStore())
		{
			$daoStore = DAO_CFactory::create('store');
			$daoStore->id = CBrowserSession::getCurrentFadminStore();
			$num = $daoStore->find(true);
			if ($num !== 1)
			{
				$daoStore = null;
				$tpl->setErrorMsg('The selected store could not be found');
			}
		}

		return $daoStore;
	}

	static function buildOrdersSessionInfoArray($sessionId)
	{
		//get session details
		if ($sessionId)
		{
			$Session = DAO_CFactory::create('session');
			$Session->id = $sessionId;
			$Store = DAO_CFactory::create('store');
			$Session->joinAdd($Store);
			$Session->find(true);

			return $Session->toArray();
		}
	}

	static function buildOrdersSessionInfoArrayV2($sessionId)
	{
		//get session details
		if ($sessionId)
		{
			$Session = DAO_CFactory::create('session');
			$Session->query("select s.id as session_id, s.session_start, s.duration_minutes, s.session_password, s.session_details, s.menu_id, " . " st.id as store_id, st.store_name, st.address_line1, st.address_line2, st.city, st.state_id, " . " st.postal_code, st.email_address, st.telephone_day, st.telephone_evening from session s " . " join store st on st.id = s.store_id where s.id = $sessionId and s.is_deleted = 0 and session_publish_state = 'PUBLISHED'");

			$Session->fetch();

			return $Session->toArray();
		}
	}

	public static function buildOrderItemsArray($Order, $promo = null, $freeMeal = null, $flatList = false, $orderBy = 'FeaturedFirst')
	{
		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->id = $Order->menu_id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'menu_to_menu_item_store_id' => $Order->store->id,
			'join_order_item_order_id' => array($Order->id),
			'join_order_item_order' => 'INNER',
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$is_menu_sampler = false;

		$parentItems = array();

		$thisOrderAllowsCustomization = false;
		$thisOrderAllowsPreAssembledCustomization = false;

		if (!empty($Order))
		{
			$orderCustomization = OrdersCustomization::getInstance($Order);
			$storeCustomization = $orderCustomization->storeCustomizationSettingsToObj();
			$thisOrderAllowsCustomization = $storeCustomization->allowsMealCustomization();
			$thisOrderAllowsPreAssembledCustomization = $storeCustomization->allowsPreAssembledCustomization();
		}

		$menuInfo = array(
			CMenuItem::CORE => null,
			CMenuItem::EXTENDED => null,
			CMenuItem::SIDE => null
		);

		while ($DAO_menu_item->fetch())
		{
			if (empty($DAO_menu_item->DAO_order_item->parent_menu_item_id) && !empty($DAO_menu_item->is_bundle))
			{
				$menu_item_id = $DAO_menu_item->id;

				if (!isset($parentItems[$menu_item_id]))
				{
					$parentItems[$menu_item_id] = array(
						'display_title' => $DAO_menu_item->menu_item_name,
						'display_title_pre_assembled' => $DAO_menu_item->menu_item_name,
						'sub_items' => array(
							'has_pre_assembled' => false,
							'menu_items' => array()
						)
					);
				}
				else
				{
					//Make sure to override with actual parent name if it isn't the first item encountered
					$parentItems[$menu_item_id]['display_title'] = $DAO_menu_item->menu_item_name . $parentItems[$menu_item_id]['display_title'];
					$parentItems[$menu_item_id]['display_title_pre_assembled'] = $DAO_menu_item->menu_item_name . $parentItems[$menu_item_id]['display_title_pre_assembled'];
				}
			}

			if (!empty($DAO_menu_item->DAO_order_item->parent_menu_item_id))
			{
				$parentItems[$DAO_menu_item->DAO_order_item->parent_menu_item_id]['display_title'] .= "<br /><span style='font-size:smaller;'>&nbsp;&nbsp;&nbsp;" . $DAO_menu_item->DAO_order_item->bundle_item_count . " " . $DAO_menu_item->menu_item_name . "</span>";

				if (!empty($DAO_menu_item->is_preassembled))
				{
					$parentItems[$DAO_menu_item->DAO_order_item->parent_menu_item_id]['display_title_pre_assembled'] .= "<br /><span style='font-size:smaller;'>&nbsp;&nbsp;&nbsp;" . $DAO_menu_item->DAO_order_item->bundle_item_count . " " . $DAO_menu_item->menu_item_name . "</span>";

					$parentItems[$DAO_menu_item->DAO_order_item->parent_menu_item_id]['display_title'] .= "<span style='font-size:x-small;color:black;font-weight: lighter;text-transform:capitalize;'><br>&nbsp;&nbsp;&nbsp; - Pre-Assembled</span>";

					if ($thisOrderAllowsCustomization && !$thisOrderAllowsPreAssembledCustomization)
					{
						$parentItems[$DAO_menu_item->DAO_order_item->parent_menu_item_id]['display_title'] .= "<span style='font-size:x-small;color:darkred;font-weight: lighter;text-transform:capitalize;'><br>&nbsp;&nbsp;&nbsp; - Not Customizable</span>";
					}
				}

				$parentItems[$DAO_menu_item->DAO_order_item->parent_menu_item_id]['sub_items']['menu_items'][$DAO_menu_item->id] = $DAO_menu_item->cloneObj();

				if (empty($parentItems[$DAO_menu_item->DAO_order_item->parent_menu_item_id]['sub_items']['has_pre_assembled']) && !empty($DAO_menu_item->is_preassembled))
				{
					$parentItems[$DAO_menu_item->DAO_order_item->parent_menu_item_id]['sub_items']['has_pre_assembled'] = true;
				}

				if ($DAO_menu_item->DAO_order_item->bundle_item_count == $DAO_menu_item->DAO_order_item->item_count)
				{
					continue;
				}

				$DAO_menu_item->DAO_order_item->sub_total -= ($DAO_menu_item->DAO_order_item->bundle_item_count * ($DAO_menu_item->DAO_order_item->sub_total / $DAO_menu_item->DAO_order_item->item_count));
				$DAO_menu_item->DAO_order_item->item_count -= $DAO_menu_item->DAO_order_item->bundle_item_count;
			}

			//use price with mark up

			// Some migrated data has 0 for an item count, look for that condition and try to display something meaningful with a divide by 0 error
			if (isset($DAO_menu_item->DAO_order_item->item_count) && $DAO_menu_item->DAO_order_item->item_count != 0)
			{
				$tempItemAmount = $DAO_menu_item->DAO_order_item->sub_total / $DAO_menu_item->DAO_order_item->item_count;
				if ($promo && $promo->promo_menu_item_id == $DAO_menu_item->id)
				{
					$DAO_menu_item->DAO_order_item->item_count--;
					$menuInfo['promo_item'] = array(
						'qty' => 1,
						'display_title' => $DAO_menu_item->menu_item_name,
						'is_menu_addon' => $DAO_menu_item->is_menu_addon,
						'is_chef_touched' => $DAO_menu_item->is_chef_touched,
						'pricing_type' => $DAO_menu_item->pricing_type,
						'pricing_type_info' => $DAO_menu_item->pricing_type_info,
						'price' => $tempItemAmount,
						'is_kids_choice' => $DAO_menu_item->is_kids_choice,
						'is_bundle' => $DAO_menu_item->is_bundle,
						'is_side_dish' => $DAO_menu_item->is_side_dish,
						'menu_program_id' => $DAO_menu_item->menu_program_id,
						'servings_per_item' => $DAO_menu_item->servings_per_item,
						'recipe_id' => $DAO_menu_item->recipe_id,
						'station_number' => $DAO_menu_item->station_number
					);

					$DAO_menu_item->DAO_order_item->sub_total -= $tempItemAmount;
				}

				if ($freeMeal && $freeMeal->menu_item_id == $DAO_menu_item->id)
				{
					$DAO_menu_item->DAO_order_item->item_count--;
					$menuInfo['free_meal_item'] = array(
						'qty' => 1,
						'display_title' => $DAO_menu_item->menu_item_name,
						'is_menu_addon' => $DAO_menu_item->is_menu_addon,
						'is_chef_touched' => $DAO_menu_item->is_chef_touched,
						'pricing_type' => $DAO_menu_item->pricing_type,
						'pricing_type_info' => $DAO_menu_item->pricing_type_info,
						'price' => $tempItemAmount,
						'is_kids_choice' => $DAO_menu_item->is_kids_choice,
						'is_bundle' => $DAO_menu_item->is_bundle,
						'is_side_dish' => $DAO_menu_item->is_side_dish,
						'menu_program_id' => $DAO_menu_item->menu_program_id,
						'servings_per_item' => $DAO_menu_item->servings_per_item,
						'recipe_id' => $DAO_menu_item->recipe_id,
						'station_number' => $DAO_menu_item->station_number
					);

					$DAO_menu_item->DAO_order_item->sub_total -= $tempItemAmount;
				}

				if ($DAO_menu_item->DAO_order_item->item_count)
				{

					$tempArr = array(
						'qty' => $DAO_menu_item->DAO_order_item->item_count,
						'display_title' => $DAO_menu_item->menu_item_name,
						'pricing_type' => $DAO_menu_item->pricing_type,
						'pricing_type_info' => $DAO_menu_item->pricing_type_info,
						'price' => ($DAO_menu_item->DAO_order_item->sub_total / $DAO_menu_item->DAO_order_item->item_count),
						'is_preassembled' => $DAO_menu_item->is_preassembled,
						'is_menu_addon' => $DAO_menu_item->is_menu_addon,
						'is_chef_touched' => $DAO_menu_item->is_chef_touched,
						'is_kids_choice' => $DAO_menu_item->is_kids_choice,
						'is_bundle' => $DAO_menu_item->is_bundle,
						'menu_program_id' => $DAO_menu_item->menu_program_id,
						'is_side_dish' => $DAO_menu_item->is_side_dish,
						'servings_per_item' => $DAO_menu_item->servings_per_item,
						'bundle_id' => $DAO_menu_item->DAO_order_item->bundle_id,
						'station_number' => $DAO_menu_item->station_number,
						'subtotal' => $DAO_menu_item->DAO_order_item->sub_total,
						'recipe_id' => $DAO_menu_item->recipe_id,
					);

					if ($flatList)
					{
						$menuInfo['itemList'][$DAO_menu_item->id] = $tempArr;
						if ($DAO_menu_item->DAO_order_item->discounted_subtotal && $DAO_menu_item->DAO_order_item->discounted_subtotal != 0)
						{
							$menuInfo['itemList'][$DAO_menu_item->id]['discounted_price'] = $DAO_menu_item->DAO_order_item->discounted_subtotal / ($DAO_menu_item->DAO_order_item->item_count + $DAO_menu_item->DAO_order_item->bundle_item_count);
							$menuInfo['itemList'][$DAO_menu_item->id]['subtotal'] = $DAO_menu_item->DAO_order_item->discounted_subtotal;
						}
					}
					else
					{
						$menuInfo[$DAO_menu_item->category_group][$DAO_menu_item->id] = $tempArr;
						if ($DAO_menu_item->DAO_order_item->discounted_subtotal && $DAO_menu_item->DAO_order_item->discounted_subtotal != 0)
						{
							$menuInfo[$DAO_menu_item->category_group][$DAO_menu_item->id]['discounted_price'] = $DAO_menu_item->DAO_order_item->discounted_subtotal / ($DAO_menu_item->DAO_order_item->item_count + $DAO_menu_item->DAO_order_item->bundle_item_count);
							$menuInfo[$DAO_menu_item->category_group][$DAO_menu_item->id]['subtotal'] = $DAO_menu_item->DAO_order_item->discounted_subtotal;
						}
					}
				}
			}
			else
			{
				// Handle 0 item count

				$tempArr = array(
					'qty' => $DAO_menu_item->DAO_order_item->item_count,
					'display_title' => $DAO_menu_item->menu_item_name,
					'is_menu_addon' => $DAO_menu_item->is_menu_addon,
					'is_chef_touched' => $DAO_menu_item->is_chef_touched,
					'pricing_type' => $DAO_menu_item->pricing_type,
					'pricing_type_info' => $DAO_menu_item->pricing_type_info,
					'price' => $DAO_menu_item->DAO_order_item->sub_total,
					'menu_program_id' => $DAO_menu_item->menu_program_id,
					'is_kids_choice' => $DAO_menu_item->is_kids_choice,
					'is_bundle' => $DAO_menu_item->is_bundle,
					'is_side_dish' => $DAO_menu_item->is_side_dish,
					'servings_per_item' => $DAO_menu_item->servings_per_item,
					'station_number' => $DAO_menu_item->station_number,
					'subtotal' => $DAO_menu_item->DAO_order_item->sub_total,
					'recipe_id' => $DAO_menu_item->recipe_id,
				);

				if ($flatList)
				{
					$menuInfo['itemList'][$DAO_menu_item->id] = $tempArr;
					if ($DAO_menu_item->DAO_order_item->discounted_subtotal && $DAO_menu_item->DAO_order_item->discounted_subtotal != 0)
					{
						$menuInfo['itemList'][$DAO_menu_item->id]['discounted_price'] = $DAO_menu_item->DAO_order_item->discounted_subtotal / $DAO_menu_item->DAO_order_item->item_count;
					}
				}
				else
				{
					$menuInfo[$DAO_menu_item->category_group][$DAO_menu_item->id] = $tempArr;
					if ($DAO_menu_item->DAO_order_item->discounted_subtotal && $DAO_menu_item->DAO_order_item->discounted_subtotal != 0)
					{
						$menuInfo[$DAO_menu_item->category_group][$DAO_menu_item->id]['discounted_price'] = $DAO_menu_item->DAO_order_item->discounted_subtotal / $DAO_menu_item->DAO_order_item->item_count;
					}
				}
			}

			if ($DAO_menu_item->pricing_type == CMenuItem::INTRO && $DAO_menu_item->servings_per_item == 3)
			{
				$is_menu_sampler = true;
			}
		}

		if (!empty($parentItems))
		{

			foreach ($menuInfo as $categoryName => $subArray)
			{
				if (is_array($subArray))
				{
					foreach ($subArray as $id => $thisItem)
					{
						if (array_key_exists($id, $parentItems))
						{
							$menuInfo[$categoryName][$id]['display_title'] = $parentItems[$id]['display_title'];
							$menuInfo[$categoryName][$id]['display_title_pre_assembled'] = $parentItems[$id]['display_title_pre_assembled'];
							$menuInfo[$categoryName][$id]['sub_items'] = $parentItems[$id]['sub_items'];
						}
					}
				}
			}
		}

		$menuInfo['is_menu_sampler'] = $is_menu_sampler;

		// Products
		$product_order_item = DAO_CFactory::create('menu_item');
		$select = "select order_item.id, order_item.product_id, order_item.item_count, order_item.discounted_subtotal, " . " order_item.sub_total, order_item.item_count, product.product_title from order_item ";
		$joins = "INNER JOIN product ON order_item.product_id = product.id ";
		$where = "where order_item.order_id = " . $Order->id . " AND order_item.is_deleted = 0 AND product.is_deleted = 0 ";

		$product_order_item->query($select . $joins . $where);

		while ($product_order_item->fetch())
		{
			//use price with mark up

			// Some migrated data has 0 for an item count, look for that condition and try to display something meaningful with a divide by 0 error
			if (isset($product_order_item->item_count) && $product_order_item->item_count != 0)
			{

				if ($product_order_item->product_id == COrders::GIFT_CARD_PRODUCT_ID)
				{
					continue;
				}

				if ($product_order_item->item_count && $product_order_item->product_id == COrders::TODD_FREE_ATTENDANCE_PRODUCT_ID)
				{
					$menuInfo['Taste of Dream Dinners'][$product_order_item->product_id] = array(
						'qty' => $product_order_item->item_count,
						'display_title' => $product_order_item->product_title,
						'price' => ($product_order_item->sub_total / $product_order_item->item_count),
						'is_preassembled' => 0,
						'is_menu_addon' => 0,
						'is_chef_touched' => 0,
						'is_kids_choice' => 0,
						'is_bundle' => 0,
						'menu_program_id' => 1,
						'is_side_dish' => 0,
						'servings_per_item' => 0,
						'station_number' => $DAO_menu_item->station_number
					);
				}
				else
				{
					$menuInfo['Enrollment'][$product_order_item->product_id] = array(
						'qty' => $product_order_item->item_count,
						'display_title' => $product_order_item->product_title,
						'price' => ($product_order_item->sub_total / $product_order_item->item_count),
						'is_preassembled' => 0,
						'is_menu_addon' => 0,
						'is_chef_touched' => 0,
						'is_kids_choice' => 0,
						'is_bundle' => 0,
						'menu_program_id' => 2,
						'is_side_dish' => 0,
						'servings_per_item' => 0,
						'station_number' => $DAO_menu_item->station_number
					);
				}
			}
		}

		return $menuInfo;
	}

	public static function evaluateDisplayFragment($fragment, $title)
	{
		$fragment = str_replace("<IMAGES_PATH>", ADMIN_IMAGES_PATH, $fragment);
		$fragment = str_replace("<MENU_TITLE>", $title, $fragment);

		return $fragment;
	}

	/**
	 * @param $menu_select can be a menu id or 'next' for next month's menu
	 *
	 * @return array(    $menuOptions[id]['menu_name']
	 *                    $menuOptions[id]['startdate']
	 *                    $menuOptions[id]['enddate'],
	 *                    $menuItemInfo
	 */
	public static function buildMenuArrays($storeObj, $menu_select = null, $isDirect = false, $introOrder = false)
	{
		return self::buildMenuPlanArrays($storeObj, $menu_select, $isDirect, true, $introOrder);
	}

	/* @param $menu_select can be a menu id or 'next' for next month's menu
	 *
	 * @return array(    $menuOptions[id]['menu_name']
	 *                    $menuOptions[id]['startdate']
	 *                    $menuOptions[id]['enddate'],
	 *                    $menuItemInfo
	 */
	public static function buildIntroMenuPlanArrays($storeObj, $menu_select = null)
	{
		//create menu array[ id ] = (startdate, enddate)
		$menuInfo = array();

		//fetch the menu for the month
		$daoMenu = DAO_CFactory::create('menu');
		if (is_numeric($menu_select))
		{
			$daoMenu->id = $menu_select;
			$cnt = $daoMenu->find(true); //find(true) also calls fetch() on the first row
			//		} else if ( $menuInfo ) {
			//			$daoMenu->id = reset(array_keys($menuInfo)); //get the first id, should be ordered by date
			//			$cnt = $daoMenu->find(true); //find(true) also calls fetch() on the first row
		}
		else
		{
			$cnt = $daoMenu->findCurrent($menu_select === 'next');
			$daoMenu->fetch();
		}

		if ($cnt == 0)
		{
			throw new exception('error: menu not found');
		}
		if ($cnt > 1)
		{
			CLog::Record('E_ERROR: more than one menu result for this month');
		}

		// get entrees
		$daoMenuItem = $daoMenu->getMenuItemDAO('FeaturedFirst', $storeObj->id); //returns the associated menu item query
		$menuItemInfo = array();
		$markup = null;
		$full_prices = array();

		$servings = 6;
		if ($daoMenu->id > 81)
		{
			$servings = 3;
		}

		$sampler_item_price = null;
		// CES 09-23-08: price now comes from store setting in the markupObj
		if (isset($storeObj) && $storeObj)
		{
			if (!isset($markup))
			{
				$markup = $storeObj->getMarkUpMultiObj($daoMenu->id);
			}

			if (isset($markup) && !empty($markup->sampler_item_price))
			{
				$sampler_item_price = $markup->sampler_item_price;
			}
		}

		if (is_null($sampler_item_price))
		{
			$sampler_item_price = 12.50;
		}

		while ($daoMenuItem->fetch())
		{

			if ($daoMenuItem->pricing_type == CMenuItem::INTRO && $daoMenuItem->category != 'Fast Lane' && $daoMenuItem->category != 'Sides' && $daoMenuItem->servings_per_item == $servings)
			{
				$i = $daoMenuItem->id;
				$menuItemInfo[$daoMenuItem->category][$i] = array();
				$menuItemInfo[$daoMenuItem->category][$i]['id'] = $i;
				$menuItemInfo[$daoMenuItem->category][$i]['display_title'] = $daoMenuItem->menu_item_name;
				$menuItemInfo[$daoMenuItem->category][$i]['menu_item_name'] = $daoMenuItem->menu_item_name;
				$menuItemInfo[$daoMenuItem->category][$i]['display_description'] = stripslashes($daoMenuItem->menu_item_description);
				$menuItemInfo[$daoMenuItem->category][$i]['menu_item_description'] = $daoMenuItem->menu_item_description;
				$menuItemInfo[$daoMenuItem->category][$i]['menu_item_name'] = $daoMenuItem->menu_item_name;
				$menuItemInfo[$daoMenuItem->category][$i]['menu_item_description'] = $daoMenuItem->menu_item_description;
				$menuItemInfo[$daoMenuItem->category][$i]['is_featured'] = $daoMenuItem->featuredItem;
				$menuItemInfo[$daoMenuItem->category][$i]['base_price'] = $sampler_item_price; //$daoMenuItem->price;
				$menuItemInfo[$daoMenuItem->category][$i]['pricing_type'] = $daoMenuItem->pricing_type;
				$menuItemInfo[$daoMenuItem->category][$i]['pricing_type_info'] = $daoMenuItem->pricing_type_info;
				$menuItemInfo[$daoMenuItem->category][$i]['entree_id'] = $daoMenuItem->entree_id;
				$menuItemInfo[$daoMenuItem->category][$i]['servings_per_item'] = $daoMenuItem->servings_per_item;
				$menuItemInfo[$daoMenuItem->category][$i]['is_preassembled'] = $daoMenuItem->is_preassembled;
				$menuItemInfo[$daoMenuItem->category][$i]['is_side_dish'] = $daoMenuItem->is_side_dish;
				$menuItemInfo[$daoMenuItem->category][$i]['is_kids_choice'] = $daoMenuItem->is_kids_choice;
				$menuItemInfo[$daoMenuItem->category][$i]['is_menu_addon'] = $daoMenuItem->is_menu_addon;
				$menuItemInfo[$daoMenuItem->category][$i]['is_chef_touched'] = $daoMenuItem->is_chef_touched;
				$menuItemInfo[$daoMenuItem->category][$i]['is_bundle'] = $daoMenuItem->is_bundle;
				$menuItemInfo[$daoMenuItem->category][$i]['excluded'] = isset($daoMenuItem->excluded) ? true : false;
				$menuItemInfo[$daoMenuItem->category][$i]['initial_inventory'] = isset($daoMenuItem->initial_inventory) ? $daoMenuItem->initial_inventory : 9999;
				$menuItemInfo[$daoMenuItem->category][$i]['override_inventory'] = isset($daoMenuItem->override_inventory) ? $daoMenuItem->override_inventory : 9999;
				$menuItemInfo[$daoMenuItem->category][$i]['number_sold'] = isset($daoMenuItem->number_sold) ? $daoMenuItem->number_sold : 0;
			}
		}

		$menuItemInfo['menu_id'] = $daoMenu->id;
		$menuItemInfo['menu_name'] = $daoMenu->menu_name;
		$menuItemInfo['menu_month'] = strftime('%B', strtotime($daoMenu->menu_start));
		$menuItemInfo['volume_discount_amount'] = self::getVolumeDiscountAmount($daoMenu->id, $markup);

		return $menuItemInfo;
	}

	/*
	* This functions removes all duplicate menu_items from $auxMenuInfo that occur in $menuItemInfo.
	* A pair of menu items are duplicated if they share the same "cross_program_grouping_id"
	*/
	public static function filterDuplicates(&$auxMenuInfo, $menuItemInfo)
	{
		$flattenedArray = array();
		foreach ($auxMenuInfo as $k => $subarray)
		{
			if (is_array($subarray))
			{
				foreach ($subarray as $id => $data)
				{
					if (isset($data['cross_program_grouping_id']) && $data['cross_program_grouping_id'] !== 0 && $data['cross_program_grouping_id'] !== "0")
					{
						$flattenedArray[$data['cross_program_grouping_id']] = array(
							$id,
							$k
						);
					}
				}
			}
		}

		foreach ($menuItemInfo as $k => $subarray)
		{
			if (is_array($subarray))
			{
				foreach ($subarray as $id => $data)
				{

					if (array_key_exists($data['cross_program_grouping_id'], $flattenedArray))
					{

						unset($auxMenuInfo[$flattenedArray[$data['cross_program_grouping_id']][1]][$flattenedArray[$data['cross_program_grouping_id']][0]]);
					}
				}
			}
		}
	}

	/**
	 * @param $menu_select can be a menu id or 'next' for next month's menu
	 *
	 * @return array(    $menuOptions[id]['menu_name']
	 *                    $menuOptions[id]['startdate']
	 *                    $menuOptions[id]['enddate'],
	 *                    $menuItemInfo
	 */
	public static function buildNewPricingMenuPlanArrays($storeObj, $menu_select = null, $order_by = 'FeaturedFirst', $excludeMenuAddons = true, $excludeChefTouchedSelections = true, $reorderCategories = false, $excludeStoreSpecialsIfMenuIsGlobal = true, $alsoReturnFlatList = false, $returnWeeklyProjections = false)
	{
		//create menu array[ id ] = (startdate, enddate)
		$menuInfo = array();

		// this should be a string
		if (is_bool($order_by))
		{
			$order_by = 'FeaturedFirst';
		}

		//fetch the menu for the month
		$daoMenu = DAO_CFactory::create('menu');
		if (is_numeric($menu_select))
		{
			$daoMenu->id = $menu_select;
			$cnt = $daoMenu->find(true); //find(true) also calls fetch() on the first row
			//		} else if ( $menuInfo ) {
			//			$daoMenu->id = reset(array_keys($menuInfo)); //get the first id, should be ordered by date
			//			$cnt = $daoMenu->find(true); //find(true) also calls fetch() on the first row
		}
		else
		{
			$cnt = $daoMenu->findCurrent($menu_select === 'next');
			$daoMenu->fetch();
		}

		if ($cnt == 0)
		{
			throw new exception('error: menu not found');
		}
		if ($cnt > 1)
		{
			CLog::Record('E_ERROR: more than one menu result for this month');
		}

		// get entrees
		$daoMenuItem = $daoMenu->getMenuItemDAO($order_by, $storeObj->id, false, false, $excludeMenuAddons, $excludeChefTouchedSelections, $excludeStoreSpecialsIfMenuIsGlobal, false, false, false, $returnWeeklyProjections); //returns the associated menu item query
		$menuItemInfo = array();
		$markup = null;

		$markup = $storeObj->getMarkUpMultiObj($daoMenu->id);

		$flatList = array();

		while ($daoMenuItem->fetch())
		{
			$daoMenuItem->original_category = $daoMenuItem->category;

			if ($daoMenuItem->menu_item_category_id == 1 || ($daoMenuItem->menu_item_category_id == 4 && !$daoMenuItem->is_store_special))
			{
				$daoMenuItem->category = "Specials";
			}

			if ($daoMenuItem->menu_item_category_id == 4 && $daoMenuItem->is_store_special)
			{
				$daoMenuItem->category = "Extended Fast Lane";
			}

			$i = $daoMenuItem->id;
			$menuItemInfo[$daoMenuItem->category][$i] = $daoMenuItem->toArray();
			$menuItemInfo[$daoMenuItem->category][$i]['display_title'] = $daoMenuItem->menu_item_name;
			$menuItemInfo[$daoMenuItem->category][$i]['display_description'] = stripslashes($daoMenuItem->menu_item_description);
			$menuItemInfo[$daoMenuItem->category][$i]['is_featured'] = $daoMenuItem->featuredItem;
			$menuItemInfo[$daoMenuItem->category][$i]['base_price'] = $daoMenuItem->price;
			$menuItemInfo[$daoMenuItem->category][$i]['excluded'] = isset($daoMenuItem->excluded) ? true : false;
			$menuItemInfo[$daoMenuItem->category][$i]['initial_inventory'] = isset($daoMenuItem->initial_inventory) ? $daoMenuItem->initial_inventory : 9999;
			$menuItemInfo[$daoMenuItem->category][$i]['override_inventory'] = isset($daoMenuItem->override_inventory) ? $daoMenuItem->override_inventory : 9999;
			$menuItemInfo[$daoMenuItem->category][$i]['number_sold'] = isset($daoMenuItem->number_sold) ? $daoMenuItem->number_sold : 0;
			$menuItemInfo[$daoMenuItem->category][$i]['recipe_id'] = isset($daoMenuItem->recipe_id) ? $daoMenuItem->recipe_id : 0;
			$menuItemInfo[$daoMenuItem->category][$i]['station_number'] = isset($daoMenuItem->station_number) ? $daoMenuItem->station_number : 0;
			$menuItemInfo[$daoMenuItem->category][$i]['show_on_pick_sheet'] = !empty($daoMenuItem->show_on_pick_sheet) ? 1 : 0;
			$menuItemInfo[$daoMenuItem->category][$i]['markdown_id'] = !empty($daoMenuItem->markdown_id) ? $daoMenuItem->markdown_id : false;
			$menuItemInfo[$daoMenuItem->category][$i]['markdown_value'] = !empty($daoMenuItem->markdown_value) ? $daoMenuItem->markdown_value : 0;
			$menuItemInfo[$daoMenuItem->category][$i]['is_visible'] = !isset($daoMenuItem->is_visible) || (($daoMenuItem->is_visible ? true : false));
			$menuItemInfo[$daoMenuItem->category][$i]['icons'] = $daoMenuItem->icons;
			$menuItemInfo[$daoMenuItem->category][$i]['pricing_type_info'] = $daoMenuItem->pricing_type_info;
			$menuItemInfo[$daoMenuItem->category][$i]['pricing_tiers'] = $daoMenuItem->pricing_tiers;

			if ($returnWeeklyProjections)
			{
				$menuItemInfo[$daoMenuItem->category][$i]['week1_projection'] = $daoMenuItem->week1_projection;
				$menuItemInfo[$daoMenuItem->category][$i]['week2_projection'] = $daoMenuItem->week2_projection;
				$menuItemInfo[$daoMenuItem->category][$i]['week3_projection'] = $daoMenuItem->week3_projection;
				$menuItemInfo[$daoMenuItem->category][$i]['week4_projection'] = $daoMenuItem->week4_projection;
				$menuItemInfo[$daoMenuItem->category][$i]['week5_projection'] = $daoMenuItem->week5_projection;
			}

			if (isset($daoMenuItem->override_price))
			{
				$menuItemInfo[$daoMenuItem->category][$i]['price'] = $daoMenuItem->override_price;
			}
			else
			{
				if (isset($storeObj) && $storeObj)
				{
					if (!isset($markup))
					{
						$markup = $storeObj->getMarkUpMultiObj($daoMenu->id);
					}
					$menuItemInfo[$daoMenuItem->category][$i]['price'] = CTemplate::moneyFormat(COrders::getItemMarkupMultiSubtotal($markup, $daoMenuItem, 1));
				}
			}

			if ($menuItemInfo[$daoMenuItem->category][$i]['markdown_id'])
			{
				$percentage = $menuItemInfo[$daoMenuItem->category][$i]['markdown_value'] / 100;

				$menuItemInfo[$daoMenuItem->category][$i]['price'] -= COrders::std_round(($menuItemInfo[$daoMenuItem->category][$i]['price'] * $percentage));
			}

			if (!empty($storeObj->supports_ltd_roundup))
			{
				if ($menuItemInfo[$daoMenuItem->category][$i]['ltd_menu_item_value'])
				{
					$menuItemInfo[$daoMenuItem->category][$i]['price'] += COrders::std_round($menuItemInfo[$daoMenuItem->category][$i]['ltd_menu_item_value']);
				}
			}

			if ($alsoReturnFlatList)
			{
				$flatList[$i] = array(
					'recipe_id' => isset($daoMenuItem->recipe_id) ? $daoMenuItem->recipe_id : 0,
					'override_inventory' => isset($daoMenuItem->override_inventory) ? $daoMenuItem->override_inventory : 9999,
					'number_sold' => isset($daoMenuItem->number_sold) ? $daoMenuItem->number_sold : 0,
					'is_visible' => $menuItemInfo[$daoMenuItem->category][$i]['is_visible']
				);
			}
		}
		$daoMenuItem->free();

		$menuItemInfo['menu_id'] = $daoMenu->id;
		$menuItemInfo['menu_name'] = $daoMenu->menu_name;
		$menuItemInfo['menu_name_abbr'] = $daoMenu->menu_name_abbr;
		$menuItemInfo['menu_anchor_date'] = $daoMenu->menu_start;
		$menuItemInfo['menu_month'] = strftime('%B', strtotime($daoMenu->menu_start));
		$menuItemInfo['volume_discount_amount'] = self::getVolumeDiscountAmount($daoMenu->id, $markup);

		if ($alsoReturnFlatList)
		{
			return array(
				$flatList,
				$menuItemInfo
			);
		}

		return $menuItemInfo;
	}

	/**
	 * @param $menu_select can be a menu id or 'next' for next month's menu
	 *                     Order Editing uses the Store Markup associated with the order which is passed in
	 *
	 * @return array(    $menuOptions[id]['menu_name']
	 *                    $menuOptions[id]['startdate']
	 *                    $menuOptions[id]['enddate'],
	 *                    $menuItemInfo
	 */
	function buildOrderEditMenuPlanArrays($menu_select = null, $markup = null, $isNewPricePlan = false, $storeObj = false, $orderBy = 'FeaturedFirst')
	{
		//create menu array[ id ] = (startdate, enddate)
		$menuInfo = array();

		//fetch the menu for the month
		$daoMenu = DAO_CFactory::create('menu');
		if (is_numeric($menu_select))
		{
			$daoMenu->id = $menu_select;
			$cnt = $daoMenu->find(true); //find(true) also calls fetch() on the first row
			//		} else if ( $menuInfo ) {
			//			$daoMenu->id = reset(array_keys($menuInfo)); //get the first id, should be ordered by date
			//			$cnt = $daoMenu->find(true); //find(true) also calls fetch() on the first row
		}
		else
		{
			$cnt = $daoMenu->findCurrent($menu_select === 'next');
			$daoMenu->fetch();
		}

		if ($cnt == 0)
		{
			throw new exception('error: menu not found');
		}
		if ($cnt > 1)
		{
			CLog::Record('E_ERROR: more than one menu result for this month');
		}

		/*
        function getMenuItemDAO($order_by = 'FeaturedFirst',
		$storeID = false,
		$fullEntreesOnly = false,
		$groupByEntreeID = false,
		$excludeAddons = true,
		$excludeChefTouchedSelections = true,
		$excludeStoreSpecialsIfMenuIsGlobal = true,
		 $joinUsersFavoritesFlag = false,
		$inItemList = false,
		$excludeCoreMenuItems = false)

		*/

		// get entrees
		$DAO_menu = DAO_CFactory::create('menu', true);
		$DAO_menu->id = $daoMenu->id;
		$DAO_menu_item = $DAO_menu->findMenuItemDAO(array(
			'join_order_item_order_id' => array($this->id),
			'join_order_item_order' => 'LEFT',
			'menu_to_menu_item_store_id' => $storeObj->id,
			'exclude_menu_item_category_core' => false,
			'exclude_menu_item_category_efl' => false,
			'exclude_menu_item_category_sides_sweets' => true
		));

		$menuItemInfo = array();

		while ($DAO_menu_item->fetch())
		{
			$DAO_menu_item->original_category = $DAO_menu_item->category;

			if ($DAO_menu_item->menu_item_category_id == 1 || ($DAO_menu_item->menu_item_category_id == 4 && !$DAO_menu_item->is_store_special))
			{
				$DAO_menu_item->category = "Specials";
			}

			if ($DAO_menu_item->menu_item_category_id == 4 && $DAO_menu_item->is_store_special)
			{
				$DAO_menu_item->category = "Extended Fast Lane";
			}

			$i = $DAO_menu_item->id;
			$menuItemInfo[$DAO_menu_item->category][$i] = array();
			$menuItemInfo[$DAO_menu_item->category][$i]['id'] = $i;
			$menuItemInfo[$DAO_menu_item->category][$i]['menu_item_name'] = $DAO_menu_item->menu_item_name;
			$menuItemInfo[$DAO_menu_item->category][$i]['display_title'] = $DAO_menu_item->menu_item_name;
			$menuItemInfo[$DAO_menu_item->category][$i]['menu_item_description'] = $DAO_menu_item->menu_item_description;
			$menuItemInfo[$DAO_menu_item->category][$i]['display_description'] = stripslashes($DAO_menu_item->menu_item_description);
			$menuItemInfo[$DAO_menu_item->category][$i]['is_featured'] = $DAO_menu_item->featuredItem;
			$menuItemInfo[$DAO_menu_item->category][$i]['base_price'] = $DAO_menu_item->price;
			$menuItemInfo[$DAO_menu_item->category][$i]['pricing_type'] = $DAO_menu_item->pricing_type;
			$menuItemInfo[$DAO_menu_item->category][$i]['pricing_type_info'] = $DAO_menu_item->pricing_type_info;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_side_dish'] = $DAO_menu_item->is_side_dish;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_kids_choice'] = $DAO_menu_item->is_kids_choice;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_menu_addon'] = $DAO_menu_item->is_menu_addon;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_chef_touched'] = $DAO_menu_item->is_chef_touched;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_bundle'] = $DAO_menu_item->is_bundle;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_preassembled'] = $DAO_menu_item->is_preassembled;
			$menuItemInfo[$DAO_menu_item->category][$i]['servings_per_item'] = $DAO_menu_item->servings_per_item;
			$menuItemInfo[$DAO_menu_item->category][$i]['item_count_per_item'] = $DAO_menu_item->item_count_per_item;
			$menuItemInfo[$DAO_menu_item->category][$i]['entree_id'] = $DAO_menu_item->entree_id;
			$menuItemInfo[$DAO_menu_item->category][$i]['cross_program_grouping_id'] = $DAO_menu_item->cross_program_grouping_id;
			$menuItemInfo[$DAO_menu_item->category][$i]['menu_program_id'] = $DAO_menu_item->menu_program_id;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_optional'] = $DAO_menu_item->is_optional;
			$menuItemInfo[$DAO_menu_item->category][$i]['menu_item_category_id'] = $DAO_menu_item->menu_item_category_id;
			$menuItemInfo[$DAO_menu_item->category][$i]['initial_inventory'] = isset($DAO_menu_item->initial_inventory) ? $DAO_menu_item->initial_inventory : 9999;
			$menuItemInfo[$DAO_menu_item->category][$i]['override_inventory'] = isset($DAO_menu_item->override_inventory) ? $DAO_menu_item->override_inventory : 9999;
			$menuItemInfo[$DAO_menu_item->category][$i]['number_sold'] = isset($DAO_menu_item->number_sold) ? $DAO_menu_item->number_sold : 0;
			$menuItemInfo[$DAO_menu_item->category][$i]['markdown_id'] = !empty($DAO_menu_item->markdown_id) ? $DAO_menu_item->markdown_id : false;
			$menuItemInfo[$DAO_menu_item->category][$i]['markdown_value'] = !empty($DAO_menu_item->markdown_value) ? $DAO_menu_item->markdown_value : 0;
			$menuItemInfo[$DAO_menu_item->category][$i]['ltd_menu_item_value'] = !empty($DAO_menu_item->ltd_menu_item_value) ? $DAO_menu_item->ltd_menu_item_value : 0;
			$menuItemInfo[$DAO_menu_item->category][$i]['menu_order_value'] = $DAO_menu_item->menu_order_value;
			$menuItemInfo[$DAO_menu_item->category][$i]['item_is_customizable'] = $DAO_menu_item->allowsMealCustomization($storeObj);
			$menuItemInfo[$DAO_menu_item->category][$i]['store_price'] = $DAO_menu_item->getStorePrice($markup);

			// is_visible will only be set if we are obtaining a store specific menu
			// if not set it should default to true
			if (isset($DAO_menu_item->is_visible))
			{
				$menuItemInfo[$DAO_menu_item->category][$i]['is_visible'] = $DAO_menu_item->is_visible ? true : false;
			}
			else
			{
				$menuItemInfo[$DAO_menu_item->category][$i]['is_visible'] = true;
			}

			$menuItemInfo[$DAO_menu_item->category][$i]['is_price_controllable'] = $DAO_menu_item->is_price_controllable;
			$menuItemInfo[$DAO_menu_item->category][$i]['is_visibility_controllable'] = $DAO_menu_item->is_visibility_controllable;
			$menuItemInfo[$DAO_menu_item->category][$i]['override_price'] = isset($DAO_menu_item->override_price) ? $DAO_menu_item->override_price : null;

			if (isset($DAO_menu_item->override_price))
			{
				$menuItemInfo[$DAO_menu_item->category][$i]['price'] = $DAO_menu_item->override_price;
			}
			else
			{
				if ($isNewPricePlan)
				{
					$menuItemInfo[$DAO_menu_item->category][$i]['price'] = CTemplate::moneyFormat(COrders::getItemMarkupMultiSubtotal($markup, $DAO_menu_item, 1));
				}
				else
				{
					$menuItemInfo[$DAO_menu_item->category][$i]['price'] = CTemplate::moneyFormat(COrders::getItemMarkupSubtotal($markup, $DAO_menu_item, 1));
				}
			}

			if ($menuItemInfo[$DAO_menu_item->category][$i]['markdown_id'])
			{
				$percentage = $menuItemInfo[$DAO_menu_item->category][$i]['markdown_value'] / 100;

				$menuItemInfo[$DAO_menu_item->category][$i]['price'] -= COrders::std_round(($menuItemInfo[$DAO_menu_item->category][$i]['price'] * $percentage));
			}

			if (!empty($storeObj->supports_ltd_roundup))
			{
				if ($menuItemInfo[$DAO_menu_item->category][$i]['ltd_menu_item_value'])
				{
					$menuItemInfo[$DAO_menu_item->category][$i]['price'] += COrders::std_round($menuItemInfo[$DAO_menu_item->category][$i]['ltd_menu_item_value']);
				}
			}
		}

		$menuItemInfo['markup_discount_scalar'] = 1.0;
		if (!$isNewPricePlan)
		{

			if (isset($markup) && $markup->markup_type == CMarkUp::PERCENTAGE)
			{
				$menuItemInfo['markup_discount_scalar'] = ($markup->markup_value / 100) + 1.0;
			}
		}

		$menuItemInfo['menu_id'] = $daoMenu->id;
		$menuItemInfo['menu_name'] = $daoMenu->menu_name;
		$menuItmInfo['menu_month'] = strftime('%B', strtotime($daoMenu->menu_start));

		return $menuItemInfo;
	}

	function buildCTSArray($storeObj, $menu_id, $overrideMarkup = null)
	{
		$daoMenu = DAO_CFactory::create('menu');
		$daoMenu->id = $menu_id;

		$daoMenuItem = $daoMenu->findMenuItemDAO(array(
			'join_order_item_order_id' => array($this->id),
			'join_order_item_order' => 'LEFT',
			'menu_to_menu_item_store_id' => $storeObj->id,
			'exclude_menu_item_category_core' => true,
			'exclude_menu_item_category_efl' => true,
			'exclude_menu_item_category_sides_sweets' => false
		));

		$menuItemInfo = array();

		while ($daoMenuItem->fetch())
		{
			$menuItemInfo[$daoMenuItem->category][$daoMenuItem->id] = $daoMenuItem->buildMenuItemArray($storeObj, $overrideMarkup);
		}

		if (!empty($menuItemInfo['Chef Touched Selections']))
		{
			return $menuItemInfo['Chef Touched Selections'];
		}

		return array();
	}

	/**
	 * Returns all of the session data for this month plus surrounding days, regardless of the menu.
	 *
	 * return calendarInfo array
	 */
	static function buildCompactCustomerCalendarArray($storeObj, $rangeStart, $rangeEnd, $menu_id, $timestamp, $selectedSessionId = false, $programObj = false, $doCheckForOpenSessions = false, $standardSessionsOnly = false, $introRequest = false)
	{

		$sessionInfo = array();
		$menuInfo = array();

		//find future sessions for this store
		$daoSession = DAO_CFactory::create('session');

		if ($doCheckForOpenSessions)
		{
			self::$foundAtLeastOneOpenSession = false;
		}
		else
		{
			self::$foundAtLeastOneOpenSession = true;
		}

		if ($introRequest)
		{
			$rslt = $daoSession->findIntroCalendarRange($storeObj->id, $rangeStart, $rangeEnd, $menu_id);
		}
		else
		{
			$rslt = $daoSession->findCalendarRangeForMenu($storeObj->id, $rangeStart, $rangeEnd, $menu_id);
		}

		$currentMonth = date('m', $timestamp);
		$currentMonthNum = date('n', $timestamp);

		$currentMonthDate = date('Y-m-01', $timestamp);
		$tempmenu = DAO_CFactory::create('menu');
		$tempmenu->menu_start = $currentMonthDate;
		$tempmenu->find(true);
		$startDateTS = null;
		$endDateTS = null;
		$tempmenu->getValidMenuRange($startDateTS, $endDateTS);
		//self::$endofPreviousMenuCached = date('n/d/Y', $startDateTS);
		//self::$endofCurrentMenuCached = date('n/d/Y', $endDateTS);
		self::$startofCurrentMenu = date('n/d/Y', $startDateTS);
		self::$endofCurrentMenu = date('n/d/Y', $endDateTS);

		self::$currentMonthSuffix = '_' . strtolower(date('M', $timestamp));
		//create session array[ id ] = (menu, session_start, openSlots)
		while ($daoSession->fetch())
		{
			$menu_id = $daoSession->menu_id;
			$session_start = $daoSession->session_start;

			$isOpenSession = $daoSession->isOpen($storeObj);
			$sessiondate = CCalendar::dateConvert($session_start);
			if (!array_key_exists($sessiondate, $sessionInfo))
			{
				$sessionInfo[$sessiondate] = array();
			}

			$isCurrentMonth = ($currentMonth == date('m', strtotime($daoSession->menu_start)));

			$warnAboutProgramExit = false;
			if ($programObj && !$programObj->isValidDate($session_start))
			{
				$warnAboutProgramExit = true;
			}

			$suppress = false;
			global $isDinnersForLife;

			if ($isDinnersForLife && $daoSession->session_type == CSession::TODD)
			{
				$suppress = true;
			}

			if ($standardSessionsOnly && ($daoSession->session_type == CSession::TODD || $daoSession->session_type == CSession::DREAM_TASTE || $daoSession->session_type == CSession::FUNDRAISER || $daoSession->session_type == CSession::SPECIAL_EVENT || !empty($daoSession->session_password)))
			{
				$suppress = true;
			}

			if ($daoSession->session_publish_state != CSession::PUBLISHED)
			{
				$suppress = true;
			}

			if (!$suppress)
			{
				$sessionInfo[$sessiondate][$daoSession->id] = array(
					'menu_id' => $menu_id,
					'time' => date("g:i a", strtotime($session_start)),
					'remaining_slots' => $daoSession->getRemainingSlots(),
					'isQ6' => false,
					'is3Plan' => true,
					'isSpecialEvent' => ($daoSession->session_type == CSession::SPECIAL_EVENT ? true : false),
					'isTODD' => ($daoSession->session_type == CSession::TODD ? true : false),
					'dreamTaste' => ($daoSession->session_type == CSession::DREAM_TASTE ? true : false),
					'fundraiserEvent' => ($daoSession->session_type == CSession::FUNDRAISER ? true : false),
					'isPrivate' => ($daoSession->session_password ? true : false),
					'details' => $storeObj->publish_session_details ? $daoSession->session_details : "",
					'supportsIntro' => $storeObj->storeSupportsIntroOrders($daoSession->menu_id),
					'capacity' => $daoSession->available_slots,
					'isOpen' => $isOpenSession,
					'isCurrentMonth' => ($currentMonth == date('m', strtotime($daoSession->menu_start)) ? true : false),
					'isSelected' => ($selectedSessionId && ($selectedSessionId == $daoSession->id)) ? true : false,
					'warnAboutProgramExit' => $warnAboutProgramExit,
					'store_id' => $daoSession->store_id,
					'monthNum' => $currentMonthNum
				);
			}

			if ($isCurrentMonth)
			{
				//self::$endofCurrentMenu = $sessiondate;
				self::$currentMenu = $menu_id; //save menu_id of most common menu on this calendar
			}

			//if (( !$isCurrentMonth) && (!self::$endofCurrentMenu) )
			//	self::$endofPreviousMenu = $sessiondate;

			if ($isOpenSession)
			{
				self::$foundAtLeastOneOpenSession = true;
			}
		}

		self::$sessionInfo = $sessionInfo;

		$calMonth = date("m", $timestamp);
		$calYear = date("Y", $timestamp);

		///add calendar data
		$Cal = new CCalendar();

		$calendarInfo = $Cal->generateCompactDayArray($calMonth, $calYear, 'PopulateCompactCalendarDay', false, $rangeStart, $rangeEnd);

		//self::postProcessCalendarArray($calendarInfo);

		$lastWeekIsEmpty = true;
		$weekCount = count($calendarInfo);
		if ($weekCount == 6)
		{
			foreach ($calendarInfo[$weekCount - 1] as $thisDay)
			{
				if (!empty($thisDay['items']))
				{
					$lastWeekIsEmpty = false;
					break;
				}
			}

			if ($lastWeekIsEmpty)
			{
				unset($calendarInfo[5]);
			}
		}

		$monthTimeStamp = mktime(0, 0, 0, $calMonth, 1, $calYear);

		return array(
			'days' => $calendarInfo,
			'all_sessions_full' => !self::$foundAtLeastOneOpenSession,
			'month_year' => date("F Y", $monthTimeStamp),
			'month' => date("F", $monthTimeStamp),
			'year' => date("Y", $monthTimeStamp),
			"menu_id" => $tempmenu->id
		);
	}

	static private function getPreviousMonthSuffix()
	{

		switch (COrders::$currentMonthSuffix)
		{
			case "_jan":
				return "_dec";
			case "_feb":
				return "_jan";
			case "_mar":
				return "_feb";
			case "_apr":
				return "_mar";
			case "_may":
				return "_apr";
			case "_jun":
				return "_may";
			case "_jul":
				return "_jun";
			case "_aug":
				return "_jul";
			case "_sep":
				return "_aug";
			case "_oct":
				return "_sep";
			case "_nov":
				return "_oct";
			case "_dec":
				return "_nov";
		}

		return "";
	}

	static private function getNextMonthSuffix()
	{

		switch (COrders::$currentMonthSuffix)
		{
			case "_jan":
				return "_feb";
			case "_feb":
				return "_mar";
			case "_mar":
				return "_apr";
			case "_apr":
				return "_may";
			case "_may":
				return "_jun";
			case "_jun":
				return "_jul";
			case "_jul":
				return "_aug";
			case "_aug":
				return "_sep";
			case "_sep":
				return "_oct";
			case "_oct":
				return "_nov";
			case "_nov":
				return "_dec";
			case "_dec":
				return "_jan";
		}

		return "";
	}

	static function postProcessCalendarArray(&$dayArray)
	{
		$beginTime = strtotime(COrders::$startofCurrentMenu);
		$endTime = strtotime(COrders::$endofCurrentMenu);

		$prevEndTime = $beginTime - 86400;
		$nextBeginTime = $endTime + 86400;

		$beginTimeStr = date("n", $beginTime) . "/" . date("j", $beginTime) . "/" . date("Y", $beginTime);
		$endTimeStr = date("n", $endTime) . "/" . date("j", $endTime) . "/" . date("Y", $endTime);
		$prevEndTimeStr = date("n", $prevEndTime) . "/" . date("j", $prevEndTime) . "/" . date("Y", $prevEndTime);
		$nextBeginTimeStr = date("n", $nextBeginTime) . "/" . date("j", $nextBeginTime) . "/" . date("Y", $nextBeginTime);

		foreach ($dayArray as &$week)
		{
			foreach ($week as &$day)
			{
				if ($day['date'] == $beginTimeStr)
				{
					$day['styleOverride'] = ' background="' . ADMIN_IMAGES_PATH . '/calendar/cal_menu_start' . COrders::$currentMonthSuffix . '.gif" ';
				}

				if ($day['date'] == $endTimeStr)
				{
					$day['styleOverride'] = ' background="' . ADMIN_IMAGES_PATH . '/calendar/cal_menu_end' . COrders::$currentMonthSuffix . '.gif" ';
				}

				if ($day['date'] == $prevEndTimeStr)
				{
					$day['styleOverride'] = ' background="' . ADMIN_IMAGES_PATH . '/calendar/cal_menu_end' . COrders::getPreviousMonthSuffix() . '.gif" ';
				}

				if ($day['date'] == $nextBeginTimeStr)
				{
					$day['styleOverride'] = ' background="' . ADMIN_IMAGES_PATH . '/calendar/cal_menu_start' . COrders::getNextMonthSuffix() . '.gif" ';
				}
			}
		}
	}

	/**
	 * Returns all of the session data for this month plus surrounding days, regardless of the menu.
	 *
	 * return calendarInfo array
	 */
	static function buildDirectOrderCalendarArray($storeObj, $timestamp = false, $selectedSessionId = false, $cutOffTS = false)
	{

		$sessionInfo = array();
		$menuInfo = array();

		list($rangeStart, $rangeEnd) = CCalendar::calculateMonthRange($timestamp);

		$calMonth = date("m", $timestamp);
		$calYear = date("Y", $timestamp);

		//	if ($calMonth == 1 && $calYear = 2013)
		//		$rangeStart = '2012-12-25 00:00:00';

		//find future sessions for this store
		$daoSession = DAO_CFactory::create('session');

		$rslt = $daoSession->findDirectOrderCalendarRange($storeObj->id, $rangeStart, $rangeEnd);

		$currentMonth = date('m', $timestamp);
		self::$currentMonthSuffix = '_' . strtolower(date('M', $timestamp));

		$currentMonthDate = date('Y-m-01', $timestamp);
		$tempmenu = DAO_CFactory::create('menu');
		$tempmenu->menu_start = $currentMonthDate;
		$tempmenu->find(true);
		$startDateTS = null;
		$endDateTS = null;
		$tempmenu->getValidMenuRange($startDateTS, $endDateTS);
		//self::$endofPreviousMenuCached = date('n/d/Y', $startDateTS);
		//self::$endofCurrentMenuCached = date('n/d/Y', $endDateTS);
		self::$startofCurrentMenu = date('n/d/Y', $startDateTS);
		self::$endofCurrentMenu = date('n/d/Y', $endDateTS);

		$sessionArray = array();

		//create session array[ id ] = (menu, session_start, openSlots)
		while ($daoSession->fetch())
		{

			$menu_id = $daoSession->menu_id;
			$session_start = $daoSession->session_start;

			$session_startTS = strtotime($session_start);
			if ($cutOffTS && $session_startTS < $cutOffTS)
			{
				continue;
			}

			$isOpenSession = $daoSession->isOpen($storeObj);
			$isOpenForCustomization = $daoSession->isOpenForCustomization($storeObj);
			$allowedCustomization = $daoSession->allowedCustomization($storeObj);
			$sessiondate = CCalendar::dateConvert($session_start);
			if (!array_key_exists($sessiondate, $sessionInfo))
			{
				$sessionInfo[$sessiondate] = array();
			}

			$isCurrentMonth = ($currentMonth == date('m', strtotime($daoSession->menu_start)));

			$isTODDExpired = false;

			if ($daoSession->session_type == CSession::TODD && strtotime($sessiondate) + 270000 < time())
			{
				$isTODDExpired = true;
			}

			$sessionInfo[$sessiondate][$daoSession->id] = array(
				'DAO_session' => clone $daoSession,
				'menu_id' => $menu_id,
				'time' => date("g:i a", strtotime($session_start)),
				'slots' => $daoSession->getRemainingSlots(),
				'intro_slots' => $daoSession->remaining_intro_slots,
				'num_rsvps' => $daoSession->num_rsvps,
				'isQ6' => false,
				'isSpecialEvent' => ($daoSession->session_type == CSession::SPECIAL_EVENT ? true : false),
				'is_walk_in' => ($daoSession->session_type_subtype == CSession::WALK_IN ? true : false),
				'is_delivery' => ($daoSession->isDelivery() ? true : false),
				'is_remote_pickup' => ($daoSession->isRemotePickup() ? true : false),
				'isTODD' => ($daoSession->session_type == CSession::TODD ? true : false),
				'isTODDExpired' => $isTODDExpired,
				'dreamTaste' => ($daoSession->session_type == CSession::DREAM_TASTE ? true : false),
				'fundraiserEvent' => ($daoSession->session_type == CSession::FUNDRAISER ? true : false),
				'is3Plan' => true,
				'isPrivate' => ($daoSession->session_password ? true : false),
				'details' => $storeObj->publish_session_details ? $daoSession->session_details : "",
				'capacity' => $daoSession->available_slots,
				'supportsIntro' => $storeObj->storeSupportsIntroOrders($daoSession->menu_id),
				'intro_capacity' => $daoSession->introductory_slots,
				'is_discounted' => !empty($daoSession->session_discount_id) ? true : false,
				'isOpen' => $isOpenSession,
				'isOpenForCustomization' => $isOpenForCustomization,
				'allowedCustomization' => $allowedCustomization,
				'publish_state' => $daoSession->session_publish_state,
				'isCurrentMonth' => ($currentMonth == date('m', strtotime($daoSession->menu_start)) ? true : false),
				'isSelected' => ($selectedSessionId && ($selectedSessionId == $daoSession->id)) ? true : false
			);

			$sessionArray[$daoSession->id] = $daoSession->id;

			if ($isCurrentMonth)
			{
				//self::$endofCurrentMenu = $sessiondate;
				self::$currentMenu = $menu_id; //save menu_id of most common menu on this calendar
			}

			//if (( !$isCurrentMonth) && (!self::$endofCurrentMenu) )
			//	self::$endofPreviousMenu = $sessiondate;

		}

		$sessionArray = CSession::getSessionDetailArray(implode(',', $sessionArray), false);

		foreach ($sessionInfo as $date => $day)
		{
			foreach ($day as $session_id => $session)
			{
				$sessionInfo[$date][$session_id] = array_merge($sessionInfo[$date][$session_id], $sessionArray[$session_id]);
			}
		}

		self::$sessionInfo = $sessionInfo;

		///add calendar data
		$Cal = new CCalendar();

		$calendarInfo = $Cal->generateDayArray($calMonth, $calYear, 'PopulateCalendarItemDirectOrder', null, null, false, $rangeStart, $rangeEnd);

		self::postProcessCalendarArray($calendarInfo);

		return $calendarInfo;
	}

	/**
	 * @return array($success, $msg);
	 */
	static function validateCC($ccNumber, $ccType, $ccMonth, $ccYear, $CSC = null)
	{
		$msg = '';
		//validate cc# && send on its way
		if ($ccNumber)
		{
			$validateCode = CPayment::validateCC($ccType, $ccNumber, $ccMonth, $ccYear, $CSC);
			if ($validateCode === 'invalidtype')
			{
				$msg = 'That credit card number or type is invalid.';
			}
			else if ($validateCode === 'invalidmonth')
			{
				$msg = 'That month of expiration is invalid.';
			}
			else if ($validateCode === 'invalidyear')
			{
				$msg = 'That year of expiration is invalid.';
			}
			else if ($validateCode === 'expired')
			{
				$msg = 'That credit card is expired.';
			}
			else if ($validateCode === 'invalidnumber')
			{
				$msg = 'That credit card number is invalid.';
			}
			else if ($validateCode === 'invalidCSC')
			{
				$msg = 'That pin number is incorrect.';
			}
			else if ($validateCode === 0)
			{
				return array(
					true,
					null
				);
			}
		}

		return array(
			false,
			$msg
		);
	}

	static function validateCart($cartObj)
	{
		$entreesfound = false;

		$Order = $cartObj->getOrder();
		if (!$Order)
		{
			return 'No Order Object found in cart';
		}

		$session = $Order->findSession();
		if (!$session)
		{
			return 'No Session Object found in order in cart';
		}

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $session->store_id;
		$storeObj->find(true);

		if (!$session->isStandardSessionValid($storeObj))
		{
			return 'Session is closed or full';
		}

		$hasStoreMenu = CMenu::storeSpecificMenuExists($session->menu_id, $session->store_id);

		if ($Order->getItems())
		{

			foreach ($Order->getItems() as $itemStuff)
			{

				$entreesfound = true;

				$menu_to_menu_item = DAO_CFactory::create('menu_to_menu_item');
				$menu_to_menu_item->menu_id = $session->menu_id;
				$menu_to_menu_item->menu_item_id = $itemStuff[1]->id;
				$menu_to_menu_item->store_id = $hasStoreMenu ? $session->store_id : 'null';
				$menu_to_menu_item->selectAdd();
				$menu_to_menu_item->selectAdd('id');
				if (!$menu_to_menu_item->find())
				{
					return 'Entrees in cart do not match menu of session';
				}
			}
		}
		else if ($Order->getProducts())
		{
			foreach ($Order->getProducts() as $itemStuff)
			{

				$entreesfound = true;
				break;
			}
		}
		else
		{
			return 'No Items found in order in cart';
		}

		if (!$entreesfound)
		{
			return 'No Items found in order in cart';
		}

		return 'noError';
	}

	static function buildPaymentForm($Form, $User = null, $Session = null, $Store = null, $DR_Ordering = false)
	{

		require_once('DAO/BusinessObject/CPayment.php');

		//set defaults
		if (!$User)
		{
			$User = CUser::getCurrentUser();
		}

		if (!isset($_POST['address']))
		{
			$Addr = $User->getPrimaryAddress();

			if ($Addr)
			{
				$Form->DefaultValues['billing_address'] = $Addr->address_line1;
				$Form->DefaultValues['billing_postal_code'] = $Addr->postal_code;
			}
		}

		$Form->DefaultValues['is_delayed_payment'] = '0';

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "ccNameOnCard",
			CForm::autocomplete => false,
			CForm::required => true,
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "ccNumber",
			CForm::autocomplete => false,
			CForm::required => true,
			CForm::number => true,
			CForm::size => 16,
			CForm::length => 16
		));

		$cardOptions = array('null' => 'Card Type');
		if ($Store)
		{
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
		}
		else
		{
			$cardOptions [CPayment::VISA] = 'Visa';
			$cardOptions [CPayment::MASTERCARD] = 'MasterCard';
			$cardOptions [CPayment::DISCOVERCARD] = 'Discover';
			$cardOptions [CPayment::AMERICANEXPRESS] = 'American Express';
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccType",
			CForm::required => true,
			CForm::options => $cardOptions
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "ccMonth",
			CForm::required => true,
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
			CForm::name => "ccYear",
			CForm::required => true,
			CForm::options => array(
				'null' => 'Year',
				'11' => '11',
				'12' => '12',
				'13' => '13',
				'14' => '14',
				'15' => '15',
				'16' => '16',
				'17' => '17',
				'18' => '18',
				'19' => '19',
				'20' => '20',
				'21' => '21',
				'22' => '22',
				'23' => '23',
				'24' => '24'
			)
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::name => "ccSecurityCode",
			CForm::autocomplete => false,
			CForm::required => true,
			CForm::min => 100,
			CForm::max => 9999
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_address",
			CForm::required => true,
			CForm::size => 30,
			CForm::length => 50
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => "billing_postal_code",
			CForm::required => true,
			CForm::size => 16,
			CForm::length => 16
		));

		// Gift Cards widgets
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_number',
			CForm::autocomplete => false,
			CForm::length => 16,
			CForm::maxlength => 16,
			CForm::required => true
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_amount',
			CForm::length => 6,
			CForm::maxlength => 6,
			CForm::money => true,
			CForm::required => true
		));
		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::name => 'gift_card_security_code',
			CForm::autocomplete => false,
			CForm::length => 3,
			CForm::maxlength => 3,
			CForm::required => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => "gift_card_ccMonth",
			CForm::required => true,
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
			CForm::required => true,
			CForm::options => array(
				'null' => 'Year',
				'11' => '11',
				'12' => '12',
				'13' => '13',
				'14' => '14',
				'15' => '15',
				'16' => '16',
				'17' => '17',
				'18' => '18',
				'19' => '19',
				'20' => '20',
				'21' => '21',
				'22' => '22',
				'23' => '23',
				'24' => '24'
			)
		));

		//check if session is more than five days away
		if ($Session)
		{

			$sessionTS = strtotime($Session->session_start) - 518400; // allow delayed payment 6 days prior

			if ((CApp::$isStoreView || $DR_Ordering) && (strtotime("now") < $sessionTS))
			{
				$Form->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => "is_delayed_payment",
					CForm::required => false,
					CForm::value => '0'
				));
				$Form->AddElement(array(
					CForm::type => CForm::RadioButton,
					CForm::name => "is_delayed_payment",
					CForm::required => false,
					CForm::value => '1'
				));
			}
		}
	}

}

function sort_by_price($a, $b)
{

	if (COrders::isPriceEqualTo($a[1]->store_price, $b[1]->store_price))
	{
		return 0;
	}

	return (COrders::isPriceLessThan($a[1]->store_price, $b[1]->store_price)) ? 1 : -1;
}

function items_sorter($a, $b)
{
	$category_order = array(
		1 => 1,
		2 => 2,
		3 => 3,
		4 => 4,
		5 => 6,
		6 => 7,
		7 => 8,
		8 => 5,
		9 => 11,
		10 => 10,
		11 => 9
	);

	if ($category_order[$a[1]->menu_item_category_id] == $category_order[$b[1]->menu_item_category_id])
	{

		if ($a[1]->original_order == $b[1]->original_order)
		{
			return 0;
		}

		return ($a[1]->original_order < $b[1]->original_order) ? -1 : 1;
	}

	return ($category_order[$a[1]->menu_item_category_id] < $category_order[$b[1]->menu_item_category_id]) ? -1 : 1;
}

function history_sort($a, $b)
{
	$a_time = strtotime($a['time']);
	$b_time = strtotime($b['time']);

	if ($a_time == $b_time)
	{
		return 0;
	}

	if ($a_time < $b_time)
	{
		return -1;
	}

	return 1;
}

function history_sort_backwards($a, $b)
{
	$a_time = strtotime($a['time']);
	$b_time = strtotime($b['time']);

	if ($a_time == $b_time)
	{
		return 0;
	}

	if ($a_time < $b_time)
	{
		return 1;
	}

	return -1;
}

function PopulateCalendarItemDirectOrder($date)
{
	return PopulateCalendarItem($date, true);
}

/**
 * returns an HTML string for the calendar
 */
function PopulateCalendarItem($date, $isDirect = false)
{
	$retVal = array();

	$styleOverride = null;

	if (array_key_exists($date, COrders::$sessionInfo))
	{
		$dateArray = COrders::$sessionInfo;
		foreach ($dateArray[$date] as $id => $dayItem)
		{
			if ($dayItem['dreamTaste'] || $dayItem['fundraiserEvent'])
			{
				$spotsText = $dayItem['slots'] . " of " . $dayItem['capacity'] . " spots available";
				if ($dayItem['num_rsvps'] > 0)
				{
					$numOrders = $dayItem['capacity'] - ($dayItem['slots'] + $dayItem['num_rsvps']);
					$spotsText .= "(" . $numOrders . " Orders, " . $dayItem['num_rsvps'] . " RSVPs)";
				}
			}
			else
			{
				$spotsText = $dayItem['slots'] . " of " . $dayItem['capacity'] . " spots available";

				if ($dayItem["supportsIntro"])
				{
					$spotsText .= "; " . $dayItem['intro_slots'] . " of " . $dayItem['intro_capacity'] . " starter pack spots available";
				}
			}

			if ($dayItem['isTODD'] && $dayItem['isTODDExpired'])
			{
				$sessionAction = 'javascript:dd_message({title:\'Alert\', message:\'The Taste of Dream Dinners Session is more than 3 days old and cannot be booked\'});';
			}
			else
			{
				$sessionAction = 'javascript:onSessionClick(' . $id . ', \'' . CTemplate::dateTimeFormat($date . ' ' . $dayItem['time']) . '\', ' . ($dayItem['DAO_session']->isDiscounted() ? '1' : '0') . ')';
			}

			$retVal[] = $dayItem['DAO_session']->sessionTypeIcon(true) . '
				<a class="' . (($dayItem['isSelected']) ? 'bg-warning border border-green' : '') . '" href="' . $sessionAction . '"><span ' . ((!$dayItem['DAO_session']->isPublished()) ? 'class="text-decoration-line-through"' : '') . ' data-toggle="tooltip" title="' . ((!$dayItem['DAO_session']->isWalkIn()) ? $dayItem['DAO_session']->sessionStartDateTime()->format('g:i A') . ' - ' . $dayItem['DAO_session']->sessionEndDateTime()->format('g:i A') : 'All day') . '">' . ((!$dayItem['DAO_session']->isWalkIn()) ? $dayItem['DAO_session']->sessionStartDateTime()->format('g:i A') : 'Walk-In') . '</span></a>
				' . ((!$dayItem['DAO_session']->isWalkIn()) ? '<span data-toggle="tooltip" data-html="true" title="' . $spotsText . '">(' . $dayItem['slots'] . ')</span>' : '') . '
				' . $dayItem['DAO_session']->openForCustomizationIcon() . '
				' . $dayItem['DAO_session']->discountedIcon();
		}
	}

	return array(
		$retVal,
		$styleOverride
	);
}

/**
 * returns an HTML string for the calendar
 */
function PopulateCompactCalendarDay($date, $isDirect = false)
{
	$retVal = array();
	$styleOverride = null;

	if (array_key_exists($date, COrders::$sessionInfo))
	{
		$dateArray = COrders::$sessionInfo;
		$numSessions = 0;
		$numOpenSessions = 0;
		$totalSlots = 0;
		$usedSlots = 0;
		$typeArray = array();
		$numJoinableSessions = 0;
		$selectedSession = 0;

		foreach ($dateArray[$date] as $id => $dayItem)
		{
			$numSessions++;
			$totalSlots += $dayItem['capacity'];
			$usedSlots += ($dayItem['capacity'] - $dayItem['remaining_slots']);

			if ($dayItem['remaining_slots'] > 0 && $dayItem['isOpen'])
			{
				$numOpenSessions++;
			}

			if ($dayItem['isTODD'])
			{
				$typeArray['t'] = 't';
			}
			else if ($dayItem['isSpecialEvent'])
			{
				$typeArray['m'] = 'm';
			}
			else if ($dayItem['isPrivate'])
			{
				$typeArray['p'] = 'p';
			}
			else
			{
				$typeArray['s'] = 's';
			}

			if ($dayItem['isSelected'])
			{
				$selectedSession = $id;
			}
		}

		$retVal['numSessions'] = $numSessions;
		$retVal['numOpenSessions'] = $numOpenSessions;
		$retVal['totalSlots'] = $totalSlots;
		$retVal['usedSlots'] = $usedSlots;
		$retVal['session_types'] = $typeArray;

		$typeStr = implode("-", $typeArray);
		$dateStr = date("Ymd", strtotime($date));

		$retVal['li_id'] = "sid-" . $dayItem['store_id'] . "_mid-" . $dayItem['menu_id'] . "_m-" . $dayItem['monthNum'] . "_d-" . $dateStr . "_t-" . $typeStr;
		$retVal['date_id'] = $dateStr;
		$retVal['session_in_cart'] = $selectedSession;
	}

	return $retVal;
}

?>