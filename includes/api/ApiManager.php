<?php
require_once 'includes/CAppUtil.inc';
require_once 'includes/CLog.inc';

/**
 * Class ApiManager base class for API calls made using
 * CURL
 */
abstract class ApiManager
{

	private $apiName = "";

	// methods available to handle
	private $lastError;

	// Throttling
	private $remainingRequests = 40;
	private $resetTime = 0;
	private $lastRequestTime = null;

	protected function __construct($name)
	{
		$this->apiName = $name;
	}

	/**
	 * Returns the authorization token for the curl request
	 *
	 *
	 * @return mixed
	 */
	abstract function getAuthorization();

	protected function sendGetRequest($url)
	{
		$curlResponse = new stdClass();

		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_TIMEOUT, 45);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				"Content-type: application/json; charset=UTF-8",
				"Authorization: " . $this->getAuthorization()
			));

		curl_setopt($curl, CURLOPT_POST, false);

		$response = false;
		for ($x = 0; $x < 4; $x++)
		{
			$response = curl_exec($curl);
			$curlResponse->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($curlResponse->status != 200)
			{
				sleep(5);  // Let's wait 5 seconds to see if its a temporary network issue.
			}
			else if ($curlResponse->status == 200)
			{
				// we got a good response, drop out of loop.
				break;
			}
		}

		if ($response === false)
		{
			$err = curl_error($curl);
			CLog::RecordNew(CLog::ERROR, "Error occurred in {$this->apiName}.sendGetRequest(); Error: " . $err . " :: url = " . $url, "", "", true);
		}
		else
		{
			$curlResponse->last_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$curlResponse->responseHeaderStr = substr($response, 0, $header_size);
			$curlResponse->responseHeader = $this->parseHeadersFromString($curlResponse->responseHeaderStr);
			$curlResponse->responseBody = substr($response, $header_size);
			$curlResponse->resp = $response;
		}

		curl_close($curl);

		return $curlResponse;
	}


	protected function sendPostRequest($url, $payload = null, $retryCount = 4)
	{
		if(!is_numeric($retryCount) || $retryCount < 0){
			$retryCount = 4;
		}
		$curlResponse = new stdClass();

		$curl = curl_init($url);

		$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
		curl_setopt($curl, CURLOPT_TIMEOUT, 45);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$auth = $this->getAuthorization();

		if(is_null($auth)){
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				"Content-type: application/json; charset=UTF-8",
				"Accept: application/json"
			));
		}else{
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				"Content-type: application/json; charset=UTF-8",
				"Authorization: " . $auth
			));
		}


		curl_setopt($curl, CURLOPT_POST, true);

		if (!is_null($payload))
		{
			curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		}

		$response = false;
		for ($x = 0; $x < $retryCount; $x++)
		{
			$response = curl_exec($curl);
			$curlResponse->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($curlResponse->status != 200 && $curlResponse->status != 201 && $curlResponse->status != 400 && $curlResponse->status != 401)
			{
				sleep(5);  // Let's wait 5 seconds to see if its a temporary network issue.
			}
			else if ($curlResponse->status == 400 )
			{
				//Bad Request - Dont retry
				//TODO: evanl move to api manager
				if($this->apiName != 'SalesForceMarketingApi'){
					$err = curl_error($curl);
					CLog::RecordNew(CLog::ERROR, "Error occurred in {$this->apiName}.sendPostRequest(); Error: " . $err . " :: bad request issue", "", "", true);
				}
				break;
			}
			else if ($curlResponse->status == 401 )
			{
				//Authorization issue - Dont retry
				$err = curl_error($curl);
				CLog::RecordNew(CLog::ERROR, "Error occurred in {$this->apiName}.sendPostRequest(); Error: " . $err . " :: authorization issue", "", "", true);
				break;
			}
			else if ($curlResponse->status == 200 || $curlResponse->status == 201)
			{
				// we got a good response, drop out of loop.
				break;
			}
		}

		if ($response === false)
		{
			$err = curl_error($curl);
			CLog::RecordNew(CLog::ERROR, "Error occurred in {$this->apiName}.sendPostRequest(); Error: " . $err . " :: payload = " . $payload, "", "", true);
		}
		else
		{
			$curlResponse->last_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$curlResponse->responseHeaderStr = substr($response, 0, $header_size);
			$curlResponse->responseHeader = $this->parseHeadersFromString($curlResponse->responseHeaderStr);
			$curlResponse->responseBody = substr($response, $header_size);
			$curlResponse->resp = $response;
		}

		curl_close($curl);

		return $curlResponse;
	}

	protected function sendDeleteRequest($url)
	{
		$curlResponse = new stdClass();

		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_TIMEOUT, 45);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Authorization: " . $this->getAuthorization()
		));

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

		$response = false;
		for ($x = 0; $x < 4; $x++)
		{
			$response = curl_exec($curl);
			$curlResponse->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($curlResponse->status != 200)
			{
				sleep(5);  // Let's wait 5 seconds to see if its a temporary network issue.
			}
			else if ($curlResponse->status == 200)
			{
				// we got a good response, drop out of loop.
				break;
			}
		}

		if ($response === false)
		{
			$err = curl_error($curl);
			CLog::RecordNew(CLog::ERROR, "Error occurred in {$this->apiName}.sendGetRequest(); Error: " . $err . " :: url = " . $url, "", "", true);
		}
		else
		{
			$curlResponse->last_url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$curlResponse->responseHeaderStr = substr($response, 0, $header_size);
			$curlResponse->responseHeader = $this->parseHeadersFromString($curlResponse->responseHeaderStr);
			$curlResponse->responseBody = substr($response, $header_size);
			$curlResponse->resp = $response;
		}

		curl_close($curl);

		return $curlResponse;
	}

	/**
	 * ----------------------------------------------------
	 *  setLastError()
	 * ----------------------------------------------------
	 *
	 * Sets the error object for last failed request.
	 *
	 * @return void
	 */

	protected function setLastError($response)
	{

		$error = new stdClass();

		$error->code = $response->status;
		$error->headers = $response->responseHeader;
		$error->message = $response->responseBody;

		//TODO: evanl move this into the api manager - silence alert notifictions for warnings from Salesforce
		if($this->apiName == 'SalesForceMarketingApi'){
			CLog::RecordNew(CLog::ERROR, "Error occurred in {$this->apiName}; Error: " . $error->message, "", "", false);
		}else{
			CLog::RecordNew(CLog::ERROR, "Error occurred in {$this->apiName}; Error: " . $error->message, "", "", true);
		}

		$this->lastError = $error;
	}

	/**
	 * ----------------------------------------------------
	 *  getLastError()
	 * ----------------------------------------------------
	 *
	 * Returns the last response from server, reported as error.
	 *
	 * @return stdClass $error
	 */

	public function getLastError()
	{
		return $this->lastError;
	}

	/**
	 * ----------------------------------------------------
	 *  processReply($response)
	 * ----------------------------------------------------
	 *
	 * Process reply from server, intended to add further validation/handling.
	 *
	 * @return stdClass $object
	 */

	protected function processReply($response)
	{
		// API cap handling + error handling //
		if (is_object($response))
		{
			if (array_key_exists('X-Rate-Limit-Remaining', $response->responseHeader))
			{
				$this->remainingRequests = $response->responseHeader['X-Rate-Limit-Remaining'];
				$this->resetTime = $response->responseHeader['X-Rate-Limit-Reset'];
			}else{
				$this->remainingRequests = 10;
				$this->resetTime = null;
			}
			$this->lastRequestTime = time();
		}
		else
		{
			// Something went really wrong...
			CLog::RecordNew(CLog::ERROR, "Unable to process {$this->apiName} Response ", "", "", true);

			return false;
		}

		if ($response->status == 200 || $response->status == 201)
		{
			CLog::RecordNew(CLog::DEBUG, "Response Data from {$this->apiName} = " . $response->responseBody, "", "", false);

			return $response->responseBody;
		}
		else
		{
			$this->setLastError($response);

			return false;
		}
	}

	/**
	 * ----------------------------------------------------
	 *  enforceApiRateLimit()
	 * ----------------------------------------------------
	 *
	 * Enforces ShipStation API
	 *
	 * @return stdClass $object
	 */

	protected function enforceApiRateLimit()
	{
		if ($this->remainingRequests > 0)
		{
			return;
		}
		else
		{
			if (!empty($this->lastRequestTime))
			{

				$elapsedTime = (time() - $this->lastRequestTime);

				if ($elapsedTime > $this->resetTime)
				{
					return;
				}
				else
				{
					$waitingTime = ($this->resetTime - $elapsedTime);
					sleep($waitingTime);
				}
			}
			else
			{
				return; // We never should get here...
			}
		}
	}

	private function parseHeadersFromString($strHeaders)
	{
		$headerStings = explode("\r\n", $strHeaders);
		$headers = array();
		foreach ($headerStings as $headerPairStr)
		{
			if (strpos($headerPairStr, ':') === false)
			{
				//skip
			}
			else
			{
				$pair = explode(":", $headerPairStr);
				$headers[$pair[0]] = $pair[1];
			}
		}

		return $headers;
	}

	protected function cleanNulls(&$objToClean)
	{
		foreach ($objToClean as $property => $value)
		{
			if (is_array($value))
			{
				$this->cleanNulls($value);
			}
			else if (empty($value))
			{
				unset($objToClean->{"$property"});
			}
		}
	}

}