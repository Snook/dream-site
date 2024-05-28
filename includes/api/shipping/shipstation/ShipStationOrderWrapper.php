<?php

require_once 'includes/CLog.inc';
require_once 'includes/api/shipping/shipstation/ShipStationOrderResponseWrapper.php';

/**
 * Wrapper Translate from DD Order to
 * ShipStation Order, Expose properties,
 * render as json.
 *
 */
class ShipStationOrderWrapper
{
	const SS_ORDER_STATUS_PENDING_PAYMENT = "awaiting_payment";
	const SS_ORDER_STATUS_PENDING_SHIPMENT = "awaiting_shipment";
	const SS_ORDER_STATUS_SHIPPED = "shipped";
	const SS_ORDER_STATUS_ON_HOLD = "on_hold";
	const SS_ORDER_STATUS_PENDING_CANCELLED = "cancelled";

	private $hydratedOrderObj = null;

	private $ssOrder = null;
	private $orderDAO = null;

	private $storesLargeDeliveryFee = 0;
	private $storesMediumDeliveryFee = 0;

	private $shipWeightRollup = 0;

	//from API ShipStationManager->listAccountTags()[{"tagId":46183,"name":"ATTENTION - Unassigned Facility","color":"#00FF00"},{"tagId":46419,"name":"Mobile AL","color":"#CC99FF"},{"tagId":43541,"name":"Rochester Hills MI","color":"#FF0000"},{"tagId":43540,"name":"Salt Lake City UT","color":"#0000FF"}]
	//**NOT currently used, was used for marking-up orders in the old instance of Shipstation when all stores shared a single instance
	private $ds_tags = array(
		'310' => array(
			'tagId' => 46419,
			'ssName' => 'Mobile AL',
			'warehouseId' => 97052
		),
		'312' => array(
			'tagId' => 43541,
			'ssName' => 'Rochester Hills MI',
			'warehouseId' => 63108
		),
		'311' => array(
			'tagId' => 43540,
			'ssName' => 'Salt Lake City UT',
			'warehouseId' => 90212
		)
	);

	/**
	 * ShipStationOrderWrapper constructor.
	 *
	 * @param $COrdersDelivered
	 */
	public function __construct($COrdersDelivered)
	{
		$this->hydratedOrderObj = $COrdersDelivered;
		$this->hydratedOrderObj->orderShipping();
		$this->hydratedOrderObj->orderAddress();

		$this->orderDAO = DAO_CFactory::create('orders', true);
		$this->orderDAO->id = $COrdersDelivered->id;
		$this->orderDAO->joinAddWhereAsOn(DAO_CFactory::create('store', true));
		$this->orderDAO->joinAddWhereAsOn(DAO_CFactory::create('booking', true));
		$this->orderDAO->find(true);
		$this->orderDAO->reconstruct();

		$this->mapOrder();
	}

	/**
	 * @param $shipStationJson
	 *
	 * @return $array ShipStationOrderWrappers
	 */
	public static function instanceFromShipstationResponse($shipStationJson)
	{
		$ordersCollection = json_decode($shipStationJson);

		$result = array();
		foreach ($ordersCollection->orders as $order)
		{
			$result[] = new ShipStationOrderResponseWrapper($order);
		}

		return $result;
	}

	private function mapOrder()
	{
		$this->ssOrder = new stdClass();

		$testingPrefix = '';
		if (defined('SHIPSTATION_TEST_ORDER_PREFIX'))
		{
			$testingPrefix = SHIPSTATION_TEST_ORDER_PREFIX;
		}

		$this->ssOrder->orderNumber = $testingPrefix . $this->hydratedOrderObj->id;//string, required, max-length: 50
		$this->ssOrder->orderKey = $testingPrefix . $this->orderDAO->id;//string, optional **used to determine add and update must be unique**
		$this->ssOrder->orderDate = $this->orderDAO->timestamp_created;//string, required
		$this->ssOrder->orderStatus = self::SS_ORDER_STATUS_PENDING_SHIPMENT;//string, required [awaiting_payment, awaiting_shipment, shipped, on_hold, cancelled]
		if (!is_null($this->orderDAO) && !is_null($this->orderDAO->DAO_booking))
		{
			if ($this->orderDAO->DAO_booking->status == CBooking::CANCELLED)
			{
				$this->ssOrder->orderStatus = self::SS_ORDER_STATUS_PENDING_CANCELLED;
			}
		}

		$userObj = $this->hydratedOrderObj->getUser();
		$this->ssOrder->customerEmail = $userObj->primary_email;//string, optional

		$storeObj = $this->hydratedOrderObj->getStore();

		$this->storesLargeDeliveryFee = $storeObj->large_ship_cost;
		$this->storesMediumDeliveryFee = $storeObj->medium_ship_cost;

		$this->ssOrder->shipByDate = $this->hydratedOrderObj->orderShipping->ship_date;

		$this->ssOrder->paymentDate = $this->orderDAO->timestamp_created;
		$this->ssOrder->amountPaid = $this->orderDAO->grand_total;//number, optional
		$this->ssOrder->taxAmount = $this->orderDAO->subtotal_all_taxes;//number, optional
		$this->ssOrder->shippingAmount = $this->orderDAO->orderShipping->ship_cost;//number, optional

		$this->ssOrder->internalNotes = "Confirmation: " . $this->hydratedOrderObj->order_confirmation . ". " . $this->hydratedOrderObj->order_admin_notes;//string, optional

		$this->ssOrder->requestedShippingService = $storeObj->store_name;

		$this->ssOrder->items = $this->fetchBoxOrderItemInformation($this->orderDAO->id, $this->hydratedOrderObj->orderShipping->distribution_center);

		$this->ssOrder->gift = ($this->hydratedOrderObj->orderAddress->is_gift == '1') ? 'true' : 'false';

		$billing = $userObj->getPrimaryBillingAddress();
		$this->ssOrder->billTo = array(
			"name" => $this->hydratedOrderObj->orderAddress->firstname . ' ' . $this->hydratedOrderObj->orderAddress->lastname,
			"street1" => $billing->address_line1,
			"street2" => $billing->address_line2,
			"city" => $billing->city,
			"state" => $billing->state_id,
			"postalCode" => $billing->postal_code,
			"country" => $billing->country_id,
			"residential" => true
		);

		$this->ssOrder->shipTo = array(
			"name" => $this->hydratedOrderObj->orderAddress->firstname . ' ' . $this->hydratedOrderObj->orderAddress->lastname,
			"street1" => $this->hydratedOrderObj->orderAddress->address_line1,
			"street2" => $this->hydratedOrderObj->orderAddress->address_line2,
			"city" => $this->hydratedOrderObj->orderAddress->city,
			"state" => $this->hydratedOrderObj->orderAddress->state_id,
			"postalCode" => $this->hydratedOrderObj->orderAddress->postal_code,
			"country" => $this->hydratedOrderObj->orderAddress->country_id,
			"phone" => $this->hydratedOrderObj->orderAddress->telephone_1,
			"residential" => true
		);

		$couponCodeId = $this->orderDAO->coupon_code_id;
		$couponCode = '';
		if (!empty($couponCodeId))
		{
			$CouponObj = DAO_CFactory::create('coupon_code');
			$CouponObj->id = $couponCodeId;
			$CouponObj->find(true);
			$couponCode = $CouponObj->coupon_code;
		}

		$this->ssOrder->advancedOptions = array(
			//"warehouseId"=>$this->translateStoreIdToWarehouseId($storeObj->id),
			"storeId" => $this->translateDDStoreIdToSSStoreId($storeObj->id),
			//125909,//Manual Orders Store in SS
			"customField1" => $couponCode,
			"customField2" => $this->hydratedOrderObj->orderShipping->requested_delivery_date,
			"customField3" => $storeObj->store_name,
			"source" => "Main Website"
		);

		//Was used when all stores used same shipstation instance
		//$this->ssOrder->tagIds = $this->translateStoreIdToTagId($storeObj->id);
	}

	private function fetchBoxOrderItemInformation($orderId, $location)
	{

		$OrderItem = DAO_CFactory::create('order_item');

		$OrderItem->query("	select oi.id as order_item_id, bi.id as box_instance_id, b.title as title, 
	       bu.bundle_name as bundle_name ,mi.menu_item_name as menu_item_name,oi.sub_total as sub_total, 
	       oi.item_count as item_qty, bu.price as bundle_price, bu.number_servings_required
			from box b,box_instance bi, order_item oi, menu_item mi, bundle bu
			where b.id = bi.box_id
			and bu.id = bi.bundle_id
			and oi.box_instance_id = bi.id
			and mi.id = oi.menu_item_id
			and oi.order_id = {$orderId}
			and oi.is_deleted = 0
			and bi.is_deleted = 0");

		//collection of boxes, each with a collection of items
		$boxes = array();

		//medium
		$mediumWeight = array(
			"value" => 0,
			"units" => "pounds"
		);
		//large
		$largeWeight = array(
			"value" => 0,
			"units" => "pounds"
		);

		$dimensions = array(
			"units" => "inches",
			"length" => 0,
			"width" => 0,
			"height" => 0
		);

		while ($OrderItem->fetch())
		{
			if (array_key_exists($OrderItem->box_instance_id, $boxes))
			{
				//$items = $boxes[$OrderItem->box_instance_id]["options"];

				$boxes[$OrderItem->box_instance_id]["options"][] = array(
					"name" => $OrderItem->menu_item_name,
					"value" => "Qty: " . $OrderItem->item_qty
				);
			}
			else
			{
				//add new
				$weight = $largeWeight;
				$shipAmount = $this->storesLargeDeliveryFee;

				if ($OrderItem->number_servings_required != 24)
				{
					$weight = $mediumWeight;
					$shipAmount = $this->storesMediumDeliveryFee;
				}
				$this->shipWeightRollup += $weight['value'];
				$boxes[$OrderItem->box_instance_id] = array(
					"lineItemKey" => $OrderItem->box_instance_id,
					"options" => array(
						array(
							"name" => $OrderItem->menu_item_name,
							"value" => "Qty: " . $OrderItem->item_qty
						)
					),
					"name" => $OrderItem->bundle_name,
					"quantity" => 1,
					"unitPrice" => $OrderItem->bundle_price,
					"dimensions" => $dimensions,
					"weight" => $weight,
					"warehouseLocation" => $location,
					"taxAmount" => 2.5,
					"shippingAmount" => $shipAmount
				);
			}
		}
		$result = array();

		foreach ($boxes as $key => $subArray)
		{
			$result[] = $subArray;
		}

		$this->ssOrder->weight = array(
			"value" => $this->shipWeightRollup,
			"units" => "pounds"
		);

		return $result;
	}

	private function translateDDStoreIdToSSStoreId($storeId)
	{
		if (!empty($storeId))
		{
			$DAO_store_to_api = DAO_CFactory::create('store_to_api', true);
			$DAO_store_to_api->store_id = $storeId;
			$DAO_store_to_api->api = 'SHIPSTATION';

			if ($DAO_store_to_api->find(true))
			{
				return $DAO_store_to_api->api_storeId;
			}
		}

		CLog::RecordNew(CLog::ERROR, "Error occurred in ShipStationOrderWrapper.translateDDStoreIdToSSStoreId for storeID = {$storeId};", "", "", true);

		return null;
	}

	private function translateStoreIdToWarehouseId($storeId)
	{
		if (defined('SHIPSTATION_TEST_ORDER_WHAREHOUSEID'))
		{
			return SHIPSTATION_TEST_ORDER_WHAREHOUSEID;
		}
		else if (!empty($storeId))
		{
			return $this->ds_tags[$storeId]['warehouseId'];
		}

		CLog::RecordNew(CLog::ERROR, "Error occurred in ShipStationOrderWrapper.translateStoreIdToWarehouseId for storeID = {$storeId};", "", "", true);

		return null;
	}

	private function translateStoreIdToTagId($storeId)
	{

		if (defined('SHIPSTATION_TEST_ORDER_TAG'))
		{
			return array(SHIPSTATION_TEST_ORDER_TAG);
		}
		else if (!empty($storeId))
		{
			return array($this->ds_tags[$storeId]['tagId']);
		}

		CLog::RecordNew(CLog::ERROR, "Error occurred in ShipStationOrderWrapper.translateStoreIdToTagId for storeID = {$storeId};", "", "", true);

		return null;
	}

	public function toJsonString()
	{
		return json_encode($this->ssOrder);
	}

	public function getShipstationOrder()
	{
		return $this->ssOrder;
	}
}