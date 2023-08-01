<?php
require_once 'includes/api/TransientDataStore.php';
require_once 'includes/api/CacheableRequestWrapper.php';
require_once 'includes/DAO/BusinessObject/COrdersShipping.php';
require_once 'includes/CResultToken.inc';

/**
 * Wrapper ShipStation Shipment Response
 *
 */
class ShipStationShipmentWrapper extends CacheableRequestWrapper
{


	const CACHE_EXPIRE_SECONDS = 300; //5 minutes to prevent spamming service

	private $orderId = null;

	private $requestData = null;

	private $responseJsonData = null;
	private $responseArray = null;


	/**
	 * ShipStationShipmentWrapper constructor.
	 *
	 * @param $COrdersDelivered with the ID populated
	 */
	public function __construct($COrdersDelivered)
	{
		if( !is_null($COrdersDelivered->id))
		{
			$this->orderId = $COrdersDelivered->id;

			$this->populateRequestData();
		}else{
			//error
		}
	}

	/**
	 * @return OrderId
	 */
	public function getOrderNumber(){
		return $this->orderId;
	}

	private function populateRequestData()
	{
		$this->requestData = new stdClass();

		$this->requestData->orderNumber = $this->orderId;
	}

	private function populateResponseData()
	{
		$this->responseArray = $this->jsonDecoder($this->responseJsonData);
	}

	/**
	 * @return false|string The json required to request a 
	 * from ShipStations service based on the order, session data passed in.
	 */
	public function getRequestJson()
	{
		return json_encode($this->requestData);
	}

	/**
	 * @param ShipStationShipmentWrapper populated with the shipment information returned
	 * from the ShipStations service
	 */
	public function populateFromJson($Json)
	{

		$this->responseJsonData = $Json;
		$this->populateResponseData();
	}


	protected function createCacheHashKey()
	{

		$ref = $this->requestData->carrierCode . '|' . $this->requestData->fromPostalCode . '|' .$this->getOrderNumber();

		$hashedDataRef = hash('md5', $ref, false);

		return $hashedDataRef;
	}

	protected function getExpirationTime()
	{
		return self::CACHE_EXPIRE_SECONDS;
	}

	protected function setFromCache($data)
	{
		$this->populateFromJson($data);
	}

	protected function getDataToCache()
	{
		return $this->responseArray;
	}

	protected function getDatabaseLookupKey()
	{
		return TransientDataStore::SHIPPING_SHIPMENT_CACHE;
	}


	/////public data access and persistence methods

	/**
	 *
	 * Currently the normal use case is for there to only be one per order
	 * since we do not split ship
	 *
	 * @return String the latest tracking number if there are multiple
	 */
	public function getLatestTrackingNumber(){

		if(count($this->responseArray->shipments) == 1){
			return $this->responseArray->shipments[0]->trackingNumber;
		}

		$currentDate = null;
		$latestTrackingNumber = null;
		foreach ($this->responseArray->shipments as $shipment)
		{
			$newDate = strtotime($shipment->createDate);
			if($currentDate == null || $newDate > $currentDate){
				$currentDate = strtotime($newDate);
				$latestTrackingNumber = $shipment->trackingNumber;
			}
		}

		return $latestTrackingNumber;
	}

	/**
	 * Currently the normal use case is for there to only be one per order
	 * since we do not split ship
	 *
	 * @return array of tracking number strings
	 */
	public function getTrackingNumbers(){
		$result = array();

		foreach ($this->responseArray->shipments as $shipment)
		{
			$result[] = $shipment->trackingNumber;
		}

		return $result;

	}

	/**
	 * Store tracking data to the Orders_shipping table
	 * @return CResultToken true if data was updated
	 */
	public function storeShippingData(){

		$result = new CResultToken();

		$ordersShippingQbe = DAO_CFactory::create("orders_shipping");
		$ordersShippingQbe->order_id = $this->getOrderNumber();
		$ordersShippingQbe->find(true);

		if($ordersShippingQbe->N != 1){
			$result->addFailureMessage('Invalid number of orders_shipping records for order id: '.$this->getOrderNumber(), true);
		}

		if($ordersShippingQbe->tracking_number != $this->getLatestTrackingNumber()){
			$copy = clone($ordersShippingQbe);
			$ordersShippingQbe->tracking_number = $this->getLatestTrackingNumber();
			$ordersShippingQbe->tracking_number_received = date("Y-m-d H:i:s");
			$ordersShippingQbe->status = COrdersShipping::STATUS_SHIPPED;

			if( $ordersShippingQbe->update($copy) ){
				$result->addSuccessMessage('Updated orders_shipping records for order id: '.$this->getOrderNumber());
			}else{
				$result->addFailureMessage('Unable to update orders_shipping records for order id: '.$this->getOrderNumber(), true);
			}
		}else{
			$result->addSuccessMessage('Tracking number ('.$ordersShippingQbe->tracking_number.') already up to date for: '.$this->getOrderNumber());
		}

		return $result;
	}

}