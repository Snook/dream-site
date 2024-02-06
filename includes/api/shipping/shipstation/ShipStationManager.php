<?php
require_once 'includes/api/ApiManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationEndpointFactory.php';
require_once 'includes/DAO/BusinessObject/COrdersShipping.php';
require_once 'includes/DAO/BusinessObject/CEmail.php';

/**
 * Wrapper for the ShipStation API
 *
 */
class ShipStationManager extends ApiManager
{

	// Hold the class instance.
	/**
	 * @var null
	 */
	private static $instance = null;

	// API connection information
	private $endpoint;
	private $apiKey;
	private $apiSecret;
	private $authorization;

	// methods available to handle
	private $methodsPaths;

	//store config
	private $storeObj;

	protected function __construct($storeObj)
	{
		parent::__construct("ShipStationApi");

		$this->storeObj = $storeObj;

		$config = ShipStationEndpointFactory::getEndpoint($storeObj);

		$this->endpoint = $config->getEndpoint();
		$this->apiKey = $config->getApiKey();
		$this->apiSecret = $config->getApiSecret();
		$this->authorization = $config->getAuthorization();

		$this->methodsPaths = array(
			'getOrders' => 'orders',
			'getOrder' => 'orders/{id}',
			'addOrder' => 'orders/createorder',
			'listTags' => 'accounts/listtags',
			'listOrders' => ' /orders',
			'addTagToOrder' => 'orders/addtag',
			'deleteOrder' => 'orders/{id}',
			'getShipments' => 'shipments',
			'getRates' => 'shipments/getrates',
			'createLabel' => 'shipments/createLabel',
			'getWarehouses' => 'warehouses',
			'getStores' => 'stores',
			'getCarriers' => 'carriers',
			'getCarrier' => 'carriers/getcarrier',
			'getPackages' => 'carriers/listpackages',
			'getServices' => 'carriers/listservices',
			'getWebhooks' => 'webhooks',
			'subWebhook' => 'webhooks/subscribe',
			'unsubWebhook' => 'webhooks/{id} '
		);
	}

	/**
	 * ----------------------------------------------------
	 *  getInstance()
	 * ----------------------------------------------------
	 *
	 * Get the ShipStation Manager instance for the specified store.
	 *
	 * @param CStore with id populated
	 *
	 * @return  ShipStationManager $shipStationManager
	 */
	public static function getInstance($storeObj)
	{
		if (self::$instance == null || $storeObj->id != self::$instance->getCurrentStoreForConfig()->id)
		{
			self::$instance = new ShipStationManager($storeObj);
		}

		return self::$instance;
	}

	/**
	 * ----------------------------------------------------
	 *  getInstanceForOrder()
	 * ----------------------------------------------------
	 *
	 * Get the ShipStation Manager instance for the specified order's store.
	 *
	 * @param COrdersDelivered with the store id populated
	 *
	 * @return  ShipStationManager $shipStationManager
	 */
	public static function getInstanceForOrder($orderObj)
	{
		$store = new CStore();
		$store->id = $orderObj->store_id;

		return self::getInstance($store);
	}

	/**
	 * ----------------------------------------------------
	 *  getInstanceForOrder()
	 * ----------------------------------------------------
	 *
	 * Get the ShipStation Manager instance for the specified order's store.
	 *
	 * @param string shipstation store id
	 *
	 * @return  ShipStationManager $shipStationManager
	 */
	public static function getInstanceFromShipStationStore($shipstationStoreId)
	{
		foreach (ShipStationOrderWrapper::$ssStoreIds as $ddStoreId => $ssStoreId)
		{
			if ($ssStoreId == $shipstationStoreId)
			{
				$store = new CStore();
				$store->id = $ddStoreId;

				return self::getInstance($store);
			}
		}

		throw new Exception('ShipStationManager::getInstanceFromShipStationStore() -> Could not find a shipstation manager instance that matches the incoming ShipSations store ID of ' . $shipstationStoreId);
	}

	public function getCurrentStoreForConfig()
	{
		return $this->storeObj;
	}

	/**
	 * ----------------------------------------------------
	 *  getOrders($filters)
	 * ----------------------------------------------------
	 *
	 * Get a list of all orders on ShipStation matching the filter.
	 *
	 * @param Array $filters
	 *
	 * @return   $array ShipStationOrderWrappers
	 */
	public function getOrders($filters)
	{

		$this->enforceApiRateLimit();

		$this->cleanNulls($filters);

		$filter = http_build_query($filters);
		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getOrders'] . '?' . $filter);

		$jsonResults = $this->processReply($response);

		return ShipStationOrderWrapper::instanceFromShipstationResponse($jsonResults);
	}

	/**
	 * ----------------------------------------------------
	 *  getAllOrders($filters)
	 * ----------------------------------------------------
	 *
	 * Get a list of all orders on ShipStation matching the filter of all the pages.
	 *
	 * @param Array $filters
	 *
	 * @return   Array $allOrders
	 */
	public function getAllOrders($filters)
	{

		$allOrders = array();
		$searchResult = $this->getOrders($filters);
		if (!empty($searchResult))
		{
			$orders = $searchResult->orders;
			foreach ($orders as $or)
			{
				array_push($allOrders, $or);
			}

			$currentPage = $searchResult->page;
			$totalPages = $searchResult->pages;

			if ($currentPage < $totalPages)
			{
				for ($i = 2; $i <= $totalPages; $i++)
				{
					$filters['page'] = $i;
					$searchResult = $this->getOrders($filters);
					$orders = $searchResult->orders;
					foreach ($orders as $or)
					{
						array_push($allOrders, $or);
					}
				}
			}
		}

		return $allOrders;
	}

	/**
	 * ----------------------------------------------------
	 *  getOrder($orderId)
	 * ----------------------------------------------------
	 *
	 * Get an specific order on ShipStation by its ID.
	 *
	 * @param int $orderId
	 *
	 * @return   stdClass    $order
	 */

	public function getOrder($orderId)
	{
		$this->enforceApiRateLimit();

		$methodPath = str_replace('{id}', $orderId, $this->methodsPaths['getOrder']);

		$response = $this->sendGetRequest($this->endpoint . $methodPath);

		return $this->processReply($response);
	}

	/**
	 * --------------------------------------------------
	 * addTagToOrder($orderId, $tagId)
	 * --------------------------------------------------
	 *
	 * Add a Tag to a Shipstation Order.
	 *
	 * @param int $orderId
	 * @param int $tagId
	 *
	 * @return   stdClass    $order
	 */

	public function addTagToOrder($orderId, $tagId)
	{
		$this->enforceApiRateLimit();
		$url = $this->endpoint . $this->methodsPaths['addTagToOrder'];

		//TODO: get live values
		$data = json_encode(array(
			"orderId" => $orderId,
			"tagId" => $tagId
		));

		$response = $this->sendPostRequest($url, json_encode($data));

		return $this->processReply($response);
	}

	public function listAccountTags()
	{
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['listTags']);

		return $this->processReply($response);
	}

	public function listStores()
	{
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getStores']);

		return $this->processReply($response);
	}

	public function listWarehouses()
	{
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getWarehouses']);

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  addOrder($order)
	 * ----------------------------------------------------
	 *
	 * Add a new order or update and existing to ShipStation.
	 *
	 * Order is matched by the orderKey which is the order ID PK
	 * in the dreamsite DB. OrderKey must be unique
	 *
	 * @return string $orderJson returned from Shipstation OR false if
	 * there was a failure
	 */

	public function addUpdateOrder($orderWrapper)
	{
		if (defined('SHIPSTATION_DONT_SEND'))
		{
			return 'Order not sent to ShipStation based on SHIPSTATION_DONT_SEND set in config, remove from config to send.';
		}
		$this->enforceApiRateLimit();

		$order = $orderWrapper->getShipstationOrder();

		//filter unhandled data
		$this->cleanNulls($order);

		$response = $this->sendPostRequest($this->endpoint . $this->methodsPaths['addOrder'], json_encode($order));

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  deleteOrder($orderId)
	 * ----------------------------------------------------
	 *
	 * Delete an order on ShipStation by its ID.
	 *
	 * @param int $orderId
	 *
	 * @return   void
	 */

	public function deleteOrder($orderId)
	{

		// Enforce API requests cap //

		$this->enforceApiRateLimit();

		$methodPath = str_replace('{id}', $orderId, $this->methodsPaths['deleteOrder']);
		$response = $this->sendPostRequest($this->endpoint . $methodPath);

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  getShipment($shipmentWrapper)
	 * ----------------------------------------------------
	 *
	 * Get all shipments on ShipStation for and order.
	 *
	 * @param Array $filters
	 *
	 * @return ShipStationShipmentWrapper
	 */

	public function getShipments($shipmentWrapper, $useCache = true)
	{

		if ($useCache && $shipmentWrapper->isCached())
		{
			return $shipmentWrapper->restoreFromCache();
		}
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getShipments'] . '?orderNumber=' . $shipmentWrapper->getOrderNumber());

		$jsonRateResults = $this->processReply($response);

		if ($jsonRateResults != false)
		{
			$shipmentWrapper->populateFromJson($jsonRateResults);
			$shipmentWrapper->cacheData();
		}

		return $shipmentWrapper;
	}

	/**
	 * ----------------------------------------------------
	 *  getRates($filters)
	 * ----------------------------------------------------
	 *
	 * Retrieves shipping rates for the specified shipping details.
	 *
	 * @param Array $data collection of shipping details
	 *
	 *
	 * @return   $rateWrapper with the returned rates populated
	 */

	public function getRates($rateWrapper)
	{

		if ($rateWrapper->isCached())
		{
			return $rateWrapper->restoreFromCache();
		}

		$this->enforceApiRateLimit();

		$this->cleanNulls($rateWrapper);

		$response = $this->sendPostRequest($this->endpoint . $this->methodsPaths['getRates'], $rateWrapper->getRateRequestJson());

		$jsonRateResults = $this->processReply($response);

		if ($jsonRateResults != false)
		{
			$rateWrapper->populateRateFromJson($jsonRateResults);
			$rateWrapper->cacheData();
		}

		return $rateWrapper;
	}

	/**
	 * ----------------------------------------------------
	 *  createLabel($filters)
	 * ----------------------------------------------------
	 *
	 * Creates a shipping label. The labelData field returned in the response is a base64 encoded PDF value.
	 * Simply decode and save the output as a PDF file to retrieve a printable label.
	 *
	 * @param Array $filters
	 *
	 * @return   Array $label
	 */

	public function createLabel($filters)
	{

		$this->enforceApiRateLimit();

		$this->cleanNulls($filters);

		$response = $this->sendPostRequest($this->endpoint . $this->methodsPaths['createLabel'], json_encode($filters));

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  getCarriers()
	 * ----------------------------------------------------
	 *
	 * Get list of carriers available.
	 *
	 * @return Array $carriers
	 */

	public function getCarriers()
	{

		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getCarriers']);

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  getCarrier($carrierCode)
	 * ----------------------------------------------------
	 *
	 * Get attributes of Carrier matching provided carrierCode
	 *
	 * @param String $carrierCode
	 *
	 * @return   Object $carrier
	 */

	public function getCarrier($carrierCode)
	{

		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getCarrier'] . '?carrierCode=' . $carrierCode);

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  getPackages($carrierCode)
	 * ----------------------------------------------------
	 *
	 * Get a list of all Packages offered by the supplied carrierCode
	 *
	 * @param String $carrierCode
	 *
	 * @return   Array $packages
	 */

	public function getPackages($carrierCode)
	{

		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getPackages'] . '?carrierCode=' . $carrierCode);

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  getServices($carrierCode)
	 * ----------------------------------------------------
	 *
	 * Get a list of all Services offered by the supplied carrierCode
	 *
	 * @param String $carrierCode
	 *
	 * @return   Array $services
	 */

	public function getServices($carrierCode)
	{
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getServices'] . '?carrierCode=' . $carrierCode);

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  getWebhooks()
	 * ----------------------------------------------------
	 *
	 * Get a list of all available webhooks
	 *
	 *
	 * @return   Array $webhooks
	 */

	public function listWebhooks()
	{
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['getWebhooks']);

		return $this->processReply($response);
	}

	/**
	 * @param $targetUrl   required - callback url
	 * @param $event       required - ORDER_NOTIFY, ITEM_ORDER_NOTIFY, SHIP_NOTIFY, ITEM_SHIP_NOTIFY
	 * @param $store_id    optional, limits to events for store id
	 * @param $displayName optional - display name for hook in Shipstation
	 *
	 * @return false|stdClass
	 */
	public function subscribeWebhook($targetUrl, $event, $store_id, $displayName)
	{
		$this->enforceApiRateLimit();

		$data = new stdClass();
		$data->target_url = $targetUrl;//required
		$data->event = $event;//required
		$data->store_id = $store_id;//opt
		$data->friendly_name = $displayName;//opt

		$this->cleanNulls($data);

		$response = $this->sendPostRequest($this->endpoint . $this->methodsPaths['subWebhook'], json_encode($data));

		return $this->processReply($response);
	}

	/**
	 * ----------------------------------------------------
	 *  unsubscribeWebhook()
	 * ----------------------------------------------------
	 *
	 * unSubscribe from existing webhooks
	 *
	 *
	 * @param webhookId from shipstation
	 */
	public function unsubscribeWebhook($webhookId)
	{
		$this->enforceApiRateLimit();

		$methodPath = str_replace('{id}', $webhookId, $this->methodsPaths['unsubWebhook']);
		$response = $this->sendDeleteRequest($this->endpoint . $methodPath);

		return $this->processReply($response);
	}

	/**
	 * Use cached order data url to fetch order data from
	 * shipstation and then update order_shipping info...e.g. tracking number,
	 * actual ship cost,...
	 *
	 *
	 * @return boolean true if all updated
	 */
	public static function loadOrderShippingInfo()
	{
		$recordToProcess = TransientDataStore::retrieveData(TransientDataStore::SHIPPING_SHIP_NOTIFICATION_NEW);

		if ($recordToProcess['successful'])
		{
			$data = json_decode($recordToProcess['data']);
			$recordId = $recordToProcess['id'];
			$url = $data->resource_url;
			$url_components = parse_url($url);
			$qs = $url_components['query'];
			parse_str($qs, $queryParams);

			$shipStationMgrInstance = null;
			try
			{
				$shipStationMgrInstance = ShipStationManager::getInstanceFromShipStationStore($queryParams['storeID']);
			}
			catch (Exception $e)
			{
				CLog::RecordNew(CLog::ERROR, $e->getMessage(), "", "", true);
				TransientDataStore::updateDataClass($recordId, TransientDataStore::SHIPPING_SHIP_NOTIFICATION_FAILED);

				return;
			}

			$batchWrapper = $shipStationMgrInstance->loadOrderShippingInfoFromBatch(new ShipStationOrderBatchWrapper($url));

			$setShippingOnAll = true;
			foreach ($batchWrapper->getShipments() as $ssorder)
			{
				$DAO_orders = DAO_CFactory::create('orders', true);
				$DAO_orders->id = $ssorder->orderKey;

				if ($DAO_orders->find_DAO_orders(true))
				{
					if (!empty($ssorder->trackingNumber))
					{
						$copy_DAO_orders_shipping = clone($DAO_orders->DAO_orders_shipping);
						$DAO_orders->DAO_orders_shipping->status = COrdersShipping::STATUS_SHIPPED;
						$DAO_orders->DAO_orders_shipping->tracking_number = $ssorder->trackingNumber;
						$DAO_orders->DAO_orders_shipping->tracking_number_received = date('Y-m-d H:i:s');
						$DAO_orders->DAO_orders_shipping->shipping_cost = $ssorder->shipmentCost;
						$DAO_orders->DAO_orders_shipping->shipping_tax = 0.00;//$ssorder->shipping_tax;
						$rslt = $DAO_orders->DAO_orders_shipping->update($copy_DAO_orders_shipping);
						if (!$rslt)
						{
							$setShippingOnAll = false;
						}
						else
						{
							//Send Tracking Email to Guest
							CEmail::sendDeliveredShipmentTrackingEmail($DAO_orders);
						}
					}
					else
					{
						$setShippingOnAll = false;
					}
				}
			}

			if ($setShippingOnAll)
			{
				TransientDataStore::updateDataClass($recordId, TransientDataStore::SHIPPING_SHIP_NOTIFICATION_DONE);
			}

			return $setShippingOnAll;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Used to gather information based on the webhook callback
	 *
	 * @param $batchUrl url returned from the webhook
	 *
	 * @return false|stdClass Json containing the information return from the service
	 */
	public function loadOrderShippingInfoFromUrl($url)
	{

		return $this->loadOrderShippingInfoFromBatch(new ShipStationOrderBatchWrapper($url));
	}

	/**
	 * Used to gather information based on the webhook callback
	 *
	 * @param $batchWrapper containing url returned from the webhook
	 *
	 * @return false|stdClass Json containing the information return from the service
	 */
	public function loadOrderShippingInfoFromBatch($batchWrapper)
	{


		if ($batchWrapper->isCached())
		{
			return $batchWrapper->restoreFromCache();
		}

		$this->enforceApiRateLimit();

		$this->cleanNulls($batchWrapper);

		$response = $this->sendGetRequest($batchWrapper->getCallbackUrl());

		$jsonResults = $this->processReply($response);

		if ($jsonResults != false)
		{
			$batchWrapper->populateFromJson($jsonResults);
			$batchWrapper->cacheData();
		}

		return $batchWrapper;
	}

	function getAuthorization()
	{
		return $this->authorization;
	}
}

?>