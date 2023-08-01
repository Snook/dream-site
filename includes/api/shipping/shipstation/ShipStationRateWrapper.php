<?php
require_once 'includes/api/TransientDataStore.php';
require_once 'includes/api/CacheableRequestWrapper.php';

/**
 * Wrapper Translate from DD Order to
 * ShipStation Order, Expose properties,
 * render as json.
 *
 */
class ShipStationRateWrapper extends CacheableRequestWrapper
{

	const SS_CARRIER_FEDEX = "fedex";
	const SS_CARRIER_UPS = "ups";

	const SS_FEDEX_OVERNIGHT_FIRST = "fedex_first_overnight";
	const SS_FEDEX_PRIORITY_OVERNIGHT = "fedex_priority_overnight";
	const SS_FEDEX_STANDARD_OVERNIGHT = "fedex_standard_overnight";
	const SS_FEDEX_TWO_DAY_AM = "fedex_2day_am";
	const SS_FEDEX_TWO_DAY = "fedex_2day";
	const SS_FEDEX_EXPRESS_SAVER = "fedex_express_save";
	const SS_FEDEX_GROUND = "fedex_ground";

	const CACHE_EXPIRE_SECONDS = 86400; //one day

	private $hydratedOrderObj = null;
	private $ddStore;

	private $rateRequestData = null;

	private $rateResponseJsonData = null;
	private $rateResponseArray = null;

	private $selectedCarrierCode = null;

	/**
	 * ShipStationOrderWrapper constructor.
	 *
	 * @param $COrdersDelivered
	 */
	public function __construct($COrdersDelivered, $carrierCode = "fedex")
	{

		$this->selectedCarrierCode = $carrierCode;

		$this->hydratedOrderObj = DAO_CFactory::create('orders');
		if( !is_null($COrdersDelivered->id))
		{
			$this->hydratedOrderObj->id = $COrdersDelivered->id;
			$this->hydratedOrderObj->find(true);
			$this->hydratedOrderObj->reconstruct();
			$this->hydratedOrderObj->orderAddress();

			$this->ddStore = $this->hydratedOrderObj->getStore();

			$this->populateRateRequestData();
		}
	}

	private function populateRateRequestData()
	{
		$this->rateRequestData = new stdClass();

		$this->rateRequestData->carrierCode = $this->selectedCarrierCode;
		$this->rateRequestData->fromPostalCode = $this->ddStore->postal_code;
		$this->rateRequestData->toCountry = $this->hydratedOrderObj->orderAddress->country_id;
		$this->rateRequestData->toPostalCode = $this->hydratedOrderObj->orderAddress->postal_code;
		//TODO: evanl - where do we get dimensions?
		$this->rateRequestData->weight = array(
			"value" => 3,
			"units" => "ounces"
		);
	}

	private function populateRateResponseData()
	{
		$this->rateResponseArray = $this->jsonDecoder($this->rateResponseJsonData);
	}

	/**
	 * @return false|string The json required to request a rate
	 * from ShipStations service based on the order, session data passed in.
	 */
	public function getRateRequestJson()
	{
		return json_encode($this->rateRequestData);
	}

	/**
	 * @param ShipStationRateWrapper populated with the rate information returned
	 * from the ShipStations service
	 */
	public function populateRateFromJson($rateJson)
	{

		$this->rateResponseJsonData = $rateJson;
		$this->populateRateResponseData();
	}

	/**
	 * @return $array of all the delivery fees
	 */
	public function getDeliveryFees()
	{
		return $this->rateResponseArray;
	}

	/**
	 * This is not making additional calls to service, just returning the stored value
	 *
	 * @param $code Provider's delivery service code, constant on this class
	 *
	 * @return $dollar amount, or "Unavailable" if there was an error
	 */
	public function getDeliveryFeeByServiceCode($code)
	{

		if (is_array($this->rateResponseArray))
		{
			foreach ($this->rateResponseArray as $rateData)
			{
				if ($rateData->serviceCode == $code)
				{
					return $rateData->shipmentCost + $rateData->otherCost;
				}
			}
		}

		return "Unavailable";
	}

	protected function createCacheHashKey()
	{

		$ref = $this->rateRequestData->carrierCode . '|' . $this->rateRequestData->fromPostalCode . '|' . $this->hydratedOrderObj->orderAddress->postal_code;

		$hashedDataRef = hash('md5', $ref, false);

		return $hashedDataRef;
	}

	protected function getExpirationTime()
	{
		return self::CACHE_EXPIRE_SECONDS;
	}

	protected function setFromCache($data)
	{
		$this->populateRateFromJson($data);
	}

	protected function getDataToCache()
	{
		return $this->rateResponseArray;
	}

	protected function getDatabaseLookupKey()
	{
		return TransientDataStore::SHIPPING_RATE_CACHE;
	}

}