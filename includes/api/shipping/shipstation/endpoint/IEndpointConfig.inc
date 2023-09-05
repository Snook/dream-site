<?php
interface IEndpointConfig
{

	/**
	 * @return string
	 */
	public function getEndpoint();

	/**
	 * @param string $endpoint
	 */
	public function setEndpoint($endpoint);

	/**
	 * @return string
	 */
	public function getApiKey();
	/**
	 * @param string $apiKey
	 */
	public function setApiKey($apiKey);

	/**
	 * @return string
	 */
	public function getApiSecret();

	/**
	 * @param string $apiSecret
	 */
	public function setApiSecret($apiSecret);
	/**
	 * @return string
	 */
	public function getAuthorization();

	/**
	 * @param string $authorization
	 */
	public function setAuthorization($authorization);

}

?>