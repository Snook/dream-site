<?php
require_once 'includes/CAppUtil.inc';
require_once 'includes/CLog.inc';
require_once 'IEndpointConfig.inc';
class EndpointConfig implements IEndpointConfig
{
	private $endpoint = '';
	private $apiKey  = '';
	private $apiSecret= '';
	private $authorization= '';

	function __construct($DAO_store)
	{
		$DAO_store_to_api = DAO_CFactory::create('store_to_api', true);
		$DAO_store_to_api->store_id = $DAO_store->id;
		$DAO_store_to_api->api = 'SHIPSTATION';

		if($DAO_store_to_api->find(true))
		{
			$this->setEndpoint($DAO_store_to_api->endpoint);
			$this->setApiKey($DAO_store_to_api->key);
			$this->setApiSecret($DAO_store_to_api->secret) ;
			$this->setAuthorization('Basic ' . base64_encode($this->getApiKey() . ':' . $this->getApiSecret()));
		}
		else
		{
			//log ERROR - missing config
			CLog::RecordNew(CLog::ERROR, 'ShipStation API is not configured correctly for store ID ' .$DAO_store->id,"EndpointConfig.inc","29",true);
		}
	}

	/**
	 * @return string
	 */
	public function getEndpoint()
	{
		return $this->endpoint;
	}

	/**
	 * @param string $endpoint
	 */
	public function setEndpoint($endpoint)
	{
		$this->endpoint = $endpoint;
	}

	/**
	 * @return string
	 */
	public function getApiKey()
	{
		return $this->apiKey;
	}

	/**
	 * @param string $apiKey
	 */
	public function setApiKey($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	/**
	 * @return string
	 */
	public function getApiSecret()
	{
		return $this->apiSecret;
	}

	/**
	 * @param string $apiSecret
	 */
	public function setApiSecret($apiSecret)
	{
		$this->apiSecret = $apiSecret;
	}

	/**
	 * @return string
	 */
	public function getAuthorization()
	{
		return $this->authorization;
	}

	/**
	 * @param string $authorization
	 */
	public function setAuthorization($authorization)
	{
		$this->authorization = $authorization;
	}

}

?>