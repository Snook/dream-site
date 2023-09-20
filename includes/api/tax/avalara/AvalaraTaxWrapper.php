<?php
require_once 'includes/api/TransientDataStore.php';
require_once 'includes/api/CacheableRequestWrapper.php';

/**
 * Map Order Data to get Tax Info from Avalara, Expose properties,
 * render as json.
 *
 */
class AvalaraTaxWrapper extends CacheableRequestWrapper
{

	const AVALARA_CREATE_TYPE_TRANSIENT = 'SalesOrder';//get sales tax no perm. record, e.g. in checkout
	const AVALARA_CREATE_TYPE_PERMANENT = 'SalesInvoice';//create order record, e.g. order confirmed

	private $hydratedOrderObj = null;

	private $avalaraTransaction = null;
	private $transactionType = AvalaraTaxWrapper::AVALARA_CREATE_TYPE_TRANSIENT;

	const CACHE_EXPIRE_SECONDS = 86400; //one day

	private $taxRateResponseJsonData = null;
	private $taxResponseArray = null;

	/**
	 * AvalaraTaxWrapper constructor.
	 *
	 * @param $COrdersDelivered
	 */
	public function __construct($COrdersDelivered)
	{

		$this->hydratedOrderObj = DAO_CFactory::create('orders');

		if (!is_null($COrdersDelivered->id) && !$COrdersDelivered->isInEditOrder())
		{
			$this->hydratedOrderObj->id = $COrdersDelivered->id;
			$this->hydratedOrderObj->find(true);
			$this->hydratedOrderObj->reconstruct();
			$this->hydratedOrderObj->orderAddress();
		}
		else
		{
			$this->hydratedOrderObj = $COrdersDelivered;
		}

		$this->populateRateRequestData();
	}

	public function getTaxAddress()
	{
		return $this->hydratedOrderObj->orderAddress;
	}

	public function getOrderTotal()
	{
		return $this->hydratedOrderObj->grand_total;
	}

	/**
	 * @param string $typeConstant [AVALARA_CREATE_TYPE_TRANSIENT, AVALARA_CREATE_TYPE_PERMANENT]
	 */
	public function setTransactionType($typeConstant = self::AVALARA_CREATE_TYPE_TRANSIENT)
	{
		$this->transactionType = $typeConstant;
	}

	private function populateRateRequestData()
	{
		$this->avalaraTransaction = new stdClass();
		$this->avalaraTransaction->type = $this->transactionType;
		$this->avalaraTransaction->date = date('Y-m-d\TH:i:s');
		//$this->avalaraTransaction->companyCode = 'DEFAULT';

		$this->avalaraTransaction->currencyCode = 'USD';
		if (is_null($this->hydratedOrderObj->user_id))
		{
			$this->avalaraTransaction->customerCode = 'unavailable';
		}
		else
		{
			$this->avalaraTransaction->customerCode = $this->hydratedOrderObj->user_id;
		}

		$this->avalaraTransaction->addresses = array(
			"singleLocation" => array(
				"line1" => $this->hydratedOrderObj->orderAddress->address_line1,
				"line2" => $this->hydratedOrderObj->orderAddress->address_line2,
				"city" => $this->hydratedOrderObj->orderAddress->city,
				"region" => $this->hydratedOrderObj->orderAddress->state_id,
				"country" => "US",
				"postalCode" => $this->hydratedOrderObj->orderAddress->postal_code
			)
		);

		$foodcost = $this->hydratedOrderObj->subtotal_all_items - $this->hydratedOrderObj->subtotal_delivery_fee;

		$this->avalaraTransaction->lines = array();
		$this->avalaraTransaction->lines[] = array(
			"number" => 1,
			"quantity" => 1,
			"amount" => $foodcost,
			"taxCode" => "PF050002",
			//Per Lori 10/28-21 - Food and Food Ingredients / Food for home consumption or basic groceries
			//"taxCode" => "PF160058",//Prepared food / meals or combination plates / seller prepared / sold cold / packaged
			//"taxCode" => "PF160022",//Prepared food / entrees / seller prepared / requires heating / packaged
			//"taxCode" => "P0000000"  - Tangible personal property is generally deemed to be items, other than real property
			"description" => "Food Cost Order ID # {$this->hydratedOrderObj->id}"
		);
		$this->avalaraTransaction->lines[] = array(
			"number" => 2,
			"quantity" => 1,
			"amount" => $this->hydratedOrderObj->subtotal_delivery_fee,
			"taxCode" => "FR000000",
			"description" => "Delivery Fee Order ID # {$this->hydratedOrderObj->id}"
		);

		$this->avalaraTransaction->debugLevel = "Normal";
	}

	private function populateRateResponseData()
	{

		$this->taxResponseArray = $this->jsonDecoder($this->taxRateResponseJsonData, true);
	}

	/**
	 * @return false|string The json required to request a rate
	 * from Avalara service based on the order, session data passed in.
	 */
	public function getRateRequestJson()
	{
		return json_encode($this->avalaraTransaction);
	}

	/**
	 * @param AvalaraTaxRateWrapper populated with the rate information returned
	 * from the Avalara Tax service
	 */
	public function populateRateFromJson($rateJson)
	{

		$this->taxRateResponseJsonData = $rateJson;
		$this->populateRateResponseData();
	}

	/**
	 * @return mixed|null Returns the food tax if returned from Avalara or
	 *                    else null if it could not be calculated
	 */
	public function getFoodTax()
	{
		if (is_array($this->taxResponseArray["lines"]))
		{
			return $this->taxResponseArray["lines"][0]["tax"];
		}

		return null;
	}

	/**
	 * @return mixed|null Returns the delivery tax if returned from Avalara or
	 *                    else null if it could not be calculated
	 */
	public function getDeliveryFeeTax()
	{
		if (is_array($this->taxResponseArray["lines"]) && count($this->taxResponseArray["lines"]) > 1)
		{
			return $this->taxResponseArray["lines"][1]["tax"];
		}

		return null;
	}

	/**
	 *
	 * @return array of all taxes at the item level
	 */
	public function getTaxes()
	{
		return $this->taxResponseArray;
	}

	protected function createCacheHashKey()
	{
		$foodCost = $this->hydratedOrderObj->subtotal_all_items - $this->hydratedOrderObj->subtotal_delivery_fee;
		$ref = $this->hydratedOrderObj->orderAddress->postal_code . '|' . $this->hydratedOrderObj->orderAddress->address_line1 . '|' . $this->hydratedOrderObj->orderAddress->city . '|' . $this->hydratedOrderObj->orderAddress->state_id . '|' . $foodCost;

		$hashedDataRef = hash('md5', $ref, false);

		return $hashedDataRef;
	}

	protected function setFromCache($data)
	{
		$this->populateRateFromJson($data);
	}

	protected function getDataToCache()
	{
		return $this->taxRateResponseJsonData;
	}

	protected function getExpirationTime()
	{
		return self::CACHE_EXPIRE_SECONDS;
	}

	protected function getDatabaseLookupKey()
	{
		return TransientDataStore::TAX_RATE_CACHE;
	}

}