<?php
require_once 'includes/api/ApiManager.php';
require_once 'includes/DAO/BusinessObject/COrdersShipping.php';

/**
 * Wrapper for the SaleForce Marketing API
 *
 */
class SalesForceMarketingManager extends ApiManager
{

	const JOURNEY_EVENT_DEFINITION_KEY = 'APIEvent-2714fbe2-152d-9709-2638-ffd8e7c85456';


	// Hold the class instance.
	/**
	 * @var null
	 */
	private static $instance = null;

	// API connection information
	private $endpoint;//string - url
	private $apiKey;//string
	private $apiSecret;//string
	private $authorization; //string
	//@var mixed
	private $bearerToken;

	// methods available to handle - assoc array
	private $methodsPaths;



	protected function __construct()
	{
		parent::__construct("SalesForceMarketingApi");

		$this->methodsPaths = array(
			'getToken' => '/v2/token',
			'invokeEvent' => '/interaction/v1/events',
			'fetchContactKey' => '/contacts/v1/addresses/email/search'
		);

		if (defined('SFMC_AUTH_API_ENDPOINT') && defined('SFMC_REST_API_ENDPOINT') && defined('SFMC_CLIENT_ID') && defined('SFMC_CLIENT_SECRET'))
		{
			$this->endpoint = SFMC_REST_API_ENDPOINT;
			$this->apiKey = SFMC_CLIENT_ID;
			$this->apiSecret = SFMC_CLIENT_SECRET;


			$this->bearerToken = self::loadBearerToken();

			$this->authorization = 'Bearer ' . $this->bearerToken;
		}
		else
		{
			//log ERROR - missing config
			CLog::RecordNew(CLog::ERROR, 'SalesForce Marketing API is not configured correctly');
		}


	}

	/**
	 * ----------------------------------------------------
	 *  getInstance()
	 * ----------------------------------------------------
	 *
	 * Get the SalesForce Marketing Manager instance.
	 *
	 *
	 * @return  SalesForceMarketingManager $salesForceMarketingManagerManager
	 */
	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new SalesForceMarketingManager();
		}

		return self::$instance;
	}

	private function loadBearerToken(){

		$this->enforceApiRateLimit();
		$url = SFMC_AUTH_API_ENDPOINT . $this->methodsPaths['getToken'];

		$data = array(
			"grant_type" => "client_credentials",
			"client_id" => $this->apiKey,
			"client_secret" => $this->apiSecret,
		);

		$response = $this->sendPostRequest($url, json_encode($data));

		$data = $this->processReply($response);
		$data = json_decode($data,true);

		return $data['access_token'];
	}

	/**
	 * @return mixed|string
	 */
	function getAuthorization()
	{
		return $this->authorization;
	}

	/**
	 * @param $contactKey - the  contact identifier in salesforce, this should be fetched from salesforce
	 * @param $email - email address of contact to provide to the Journey
	 * @param $firstName - first name of contact to provide to the Journey
	 * @param $cartKey - the cart key value from the cart table in DreamSite database. Used to create the
	 *                 restore cart url.
	 *
	 * @return CResultToken payload is data from the SalseForce API call response
	 */
	public function invokeAbandonedCartJourney($contactKey,  $email, $firstName, $cartKey){
		CLog::RecordNew(CLog::DEBUG, 'Invoking Journey for contact key for '.$contactKey);
		$result = new CResultToken();
		$data = new stdClass();

		//succeed on live site not matter the config
		$restoreCartUrl = 'https://www.dreamdinners.com/checkout?restore_cart=' . $cartKey;
		if (defined('HTTPS_SERVER')){
			$restoreCartUrl = HTTPS_SERVER . '/checkout?restore_cart=' . $cartKey;
		}

		$data->ContactKey = $contactKey;
		$data->EventDefinitionKey = self::JOURNEY_EVENT_DEFINITION_KEY;
		$data->Data = array("ContactID" => $contactKey, "email_address" => $email,  "first_name"=> $firstName,"cart_url" => $restoreCartUrl);

		$this->enforceApiRateLimit();
		$url = $this->endpoint . $this->methodsPaths['invokeEvent'];

		$response = $this->sendPostRequest($url, json_encode($data));

		$data = $this->processReply($response);
		$data = json_decode($data,true);
		CLog::RecordNew(CLog::DEBUG, 'Journey started for contact key for '.print_r($data,true));
		$result->setPayload($data);
		return $result;
	}

	/**
	 * @param $email
	 *
	 * @return CResultToken
	 */
	public function fetchContactKeyByEmail($email){
		CLog::RecordNew(CLog::DEBUG, 'Getting SalesForce contact key for '.$email);

		$result = new CResultToken();

		$data = new stdClass();

		$data->ChannelAddressList = array($email);
		$data->MaximumCount = 1;

		$this->enforceApiRateLimit();
		$url = $this->endpoint . $this->methodsPaths['fetchContactKey'];

		$response = $this->sendPostRequest($url, json_encode($data));

		$data = $this->processReply($response);

		$data = json_decode($data,true);
		$contactKey = null;
		if(array_key_exists('channelAddressResponseEntities',$data) && count($data['channelAddressResponseEntities']) >0){
			$entities = $data['channelAddressResponseEntities'][0];
			if(array_key_exists('contactKeyDetails',$entities) && count($entities['contactKeyDetails']) >0){
				$contactDetail = $entities['contactKeyDetails'][0];
				$contactKey = $contactDetail['contactKey'];
			}
		}
		CLog::RecordNew(CLog::DEBUG, 'SalesForce contact key '.$contactKey.' for '.$email);
		$result->setPayload($contactKey);
		return $result;
	}
}

?>