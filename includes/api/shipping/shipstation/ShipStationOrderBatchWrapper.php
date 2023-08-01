<?php
require_once 'includes/api/TransientDataStore.php';
require_once 'includes/api/CacheableRequestWrapper.php';

/**
 * Wrap the batch of order data returned from webhook call
 * to Shipstation
 *
 */
class ShipStationOrderBatchWrapper extends CacheableRequestWrapper
{

	const CACHE_EXPIRE_SECONDS = 864000; //10 days
	private $responseObj = null;

	private $responseJsonData;

	private $callbackUrl = '';

	/**
	 * ShipStationOrderBatchWrapper constructor.
	 *

	 */
	public function __construct($callbackUrl)
	{
		$this->callbackUrl = $callbackUrl;
	}


	private function populateResponseData()
	{
		$this->responseObj = $this->jsonDecoder($this->responseJsonData);
	}

	public function getCallbackUrl(){
		return $this->callbackUrl;
	}

	/**
	 * @param ShipStationRateWrapper populated with the rate information returned
	 * from the ShipStations service
	 */
	public function populateFromJson($json)
	{

		$this->responseJsonData = $json;
		$this->populateResponseData();
	}

	public function getOrders(){
		return $this->responseObj->orders;
	}

	public function getShipments(){
		return $this->responseObj->shipments;
	}


	protected function createCacheHashKey()
	{

		$hashedDataRef = hash('md5', $this->callbackUrl, false);

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
		return $this->responseJsonData;
	}

	protected function getDatabaseLookupKey()
	{
		return TransientDataStore::SHIPPING_TRACKING_CACHE;
	}

}