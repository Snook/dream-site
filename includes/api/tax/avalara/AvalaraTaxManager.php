<?php
require_once 'includes/api/ApiManager.php';
require_once 'includes/api/tax/avalara/MockAvalaraTaxManager.php';

class AvalaraTaxManager extends ApiManager
{
	// Hold the class instance for the singleton
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

	protected function __construct()
	{
		parent::__construct("AvalaraTaxApi");
		if (defined('AVALARA_API_ENDPOINT') && defined('AVALARA_API_ACCOUNT_ID') && defined('AVALARA_API_LICENSE_KEY'))
		{

			$this->endpoint = AVALARA_API_ENDPOINT;
			$this->apiKey = AVALARA_API_ACCOUNT_ID;
			$this->apiSecret = AVALARA_API_LICENSE_KEY;

			$this->authorization = 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret);
		}
		else
		{
			//log ERROR - missing config
			CLog::RecordNew(CLog::ERROR, 'Avalara API is not configured correctly');
		}

		$this->methodsPaths = array(
			'create' => 'transactions/create',
			'resolveAddress' => 'addresses/resolve',
			'ping' => 'utilities/ping'
		);
	}

	/**
	 * ----------------------------------------------------
	 *  getInstance()
	 * ----------------------------------------------------
	 *
	 * Get the Avalara Tax API Manager instance.
	 *
	 *
	 * @return  AvalaraTaxManager $avalaraTaxManager
	 */
	public static function getInstance()
	{
		$isMock = false;
		if(defined('AVALARA_API_ENDPOINT_USE_MOCK'))
		{
			$isMock = AVALARA_API_ENDPOINT_USE_MOCK;
		}
		if (self::$instance == null)
		{
			if($isMock)
			{
				self::$instance = new MockAvalaraTaxManager();
			}
			else
			{
				self::$instance = new AvalaraTaxManager();
			}

		}

		return self::$instance;
	}

	//public function fetch_tax($price, $address1, $address2, $city, $state, $zipcode, $country, $taxType){
	public function getTaxRates($avalaraTaxRateWrapper)
	{

		if ($avalaraTaxRateWrapper->isCached())
		{
			return $avalaraTaxRateWrapper->restoreFromCache();
		}

		$this->enforceApiRateLimit();

		$this->cleanNulls($avalaraTaxRateWrapper);

		$payload = $avalaraTaxRateWrapper->getRateRequestJson();

		// Debug information
		// CLog::RecordNew(CLog::DEBUG, "INFO ONLY: Call to Avalara with payload: ".$payload, "", "", true);

		$response = $this->sendPostRequest($this->endpoint . $this->methodsPaths['create'], $payload);

		$jsonRateResults = $this->processReply($response);

		if ($jsonRateResults != false)
		{
			$avalaraTaxRateWrapper->populateRateFromJson($jsonRateResults);
			$avalaraTaxRateWrapper->cacheData();
			return $avalaraTaxRateWrapper;
		}

		//there was an error
		return false;
	}

	public function resolveAddress($filters)
	{
		$this->enforceApiRateLimit();

		$this->cleanNulls($filters);

		$filters = http_build_query($filters, '', '&');

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['resolveAddress'] . '?' . $filters );

		return $this->processReply($response);
	}

	public function pingService()
	{
		$this->enforceApiRateLimit();

		$response = $this->sendGetRequest($this->endpoint . $this->methodsPaths['ping']);

		return $this->processReply($response);
	}

	//    function enforceApiRateLimit(){
	//        //API specific Impl
	//    }

	function getAuthorization()
	{
		return $this->authorization;
	}
}