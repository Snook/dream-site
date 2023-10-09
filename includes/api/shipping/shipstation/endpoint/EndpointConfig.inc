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

	function __construct($storeObj)
	{

		if(defined('SHIPSTATION_API_ENDPOINT_'.$storeObj->id) && defined('SHIPSTATION_API_KEY_'.$storeObj->id) && defined('SHIPSTATION_API_SECRET_'.$storeObj->id))
		{
			$this->setEndpoint(constant('SHIPSTATION_API_ENDPOINT_'.$storeObj->id));
			$this->setApiKey(constant('SHIPSTATION_API_KEY_'.$storeObj->id));
			$this->setApiSecret(constant('SHIPSTATION_API_SECRET_'.$storeObj->id)) ;
			$this->setAuthorization('Basic ' . base64_encode($this->getApiKey() . ':' . $this->getApiSecret()));
		}
		else
		{
			//log ERROR - missing config
			CLog::RecordNew(CLog::ERROR, 'ShipStation API is not configured correctly for store ID ' .$storeObj->id,"EndpointConfig.inc","29",true);
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