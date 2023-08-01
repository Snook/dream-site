<?php
  /**
  * The nice thing about this protocol is that if you *don't* get a
  * $response, you can simply re-submit the transaction *using the same
  * REQUEST_ID* until you *do* get a response -- every time PayPal gets
  * a transaction with the same REQUEST_ID, it will not process a new
  * transactions, but simply return the same results, with a DUPLICATE=1
  * parameter appended.
  */

  /**
  * API rebuild by Radu Manole,
  * radu@u-zine.com, March 2007
  */


require_once("Config.inc");


  class payflow {

    var $submiturl;
    var $vendor;
    var $user;
    var $partner;
    var $password;
    var $errors = '';
    var $currencies_allowed = array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');

    function __construct($vendor, $user, $partner, $password) {

      $this->vendor = $vendor;
      $this->user = $user;
      $this->partner = $partner;
      $this->password = $password;

      if (strlen($this->vendor) == 0) {
        $this->set_errors('Vendor not found');
        return false;
      }
      if (strlen($this->user) == 0) {
        $this->set_errors('User not found');
        return false;
      }
      if (strlen($this->partner) == 0) {
        $this->set_errors('Partner not found');
        return false;
      }
      if (strlen($this->password) == 0) {
        $this->set_errors('Password not found');
        return false;
      }

		if ( defined('PFP_TEST_MODE') && PFP_TEST_MODE )
		{
		 	$this->submiturl = 'https://pilot-payflowpro.paypal.com';
		} else
		{
		    $this->submiturl = 'https://payflowpro.paypal.com';
		}

      // check for CURL
      if (!function_exists('curl_init')) {
        $this->set_errors('Curl function not found.');
        return false;
      }
    }


    private function setCURLOptions($ch, $plist, $request_id = false)
    {

      $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla


      $headers = $this->get_curl_headers();

      if ($request_id)
     	$headers[] = "X-VPS-Request-ID: " . $request_id;

      curl_setopt($ch, CURLOPT_URL, $this->submiturl);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		if ( defined('PFP_TEST_MODE') && PFP_TEST_MODE )
		{
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		}

      curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
      curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
      curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
      curl_setopt($ch, CURLOPT_POSTFIELDS, $plist); //adding POST data
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
      curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done
      curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST

    }


    function runTransaction($plist, $request_id = false)
    {
      $ch = curl_init();
      $this->setCURLOptions($ch, $plist, $request_id);

      for ($x = 0; $x < 4; $x++)
      {

     	 	$result = curl_exec($ch);
      		$headers = curl_getinfo($ch);
      		if ($headers['http_code'] != 200)
      		{
         	   sleep(5);  // Let's wait 5 seconds to see if its a temporary network issue.
        	}
       		else if ($headers['http_code'] == 200)
       		{
            	// we got a good response, drop out of loop.
            	break;
        	}
      }

   	  if ($headers['http_code'] != 200)
   	  {
        	curl_close($ch);

            CLog::RecordIntense("Error connecting to PayPal: " . print_r($headers, true), "ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com");

        	return array('RESULT' => -1, 'RESPMSG' => 'Unable to connect to PayPal server.');
   	   }

   	   // otherwise connection was successful - use returned result array
       curl_close($ch);
       $pfpro = $this->get_curl_result($result); //result arrray
       return $pfpro;
    }


    function getSecureToken($tokenID, $store_id, $name, $street, $zip, $amount = false)
    {
    	$currency = 'USD';

    	if (!$amount)
    	{
    	   $amount= "50.00";
    	}

    	if (defined('TEST_CALLBACK_URL'))
    	{
    	    $returnURL = TEST_CALLBACK_URL;
    	}
    	else
    	{
    	   $returnURL = HTTPS_BASE . "ddproc.php?processor=admin_payflow_callback";
    	}

    	// body
    	$plist = 'USER=' . $this->user . '&';
    	$plist .= 'VENDOR=' . $this->vendor . '&';
    	$plist .= 'PARTNER=' . $this->partner . '&';
    	$plist .= 'PWD=' . $this->password . '&';
    	$plist .= 'CURRENCY=' . $currency . '&';
    	$plist .= 'SECURETOKENID=' . $tokenID . '&';
    	$plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
    	$plist .= 'TRXTYPE=' . 'S' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
    	$plist .= 'AMT=' . $amount . '&';
    	$plist .= 'NAME=' . $name . '&';
    	$plist .= 'STREET=' . $street . '&';
    	$plist .= 'ZIP=' . $zip . '&';
    	$plist .= 'SILENTTRAN=TRUE&';
    	$plist .= 'CREATESECURETOKEN=Y&';
    	$plist .= 'RETURNURL=' . $returnURL;



    	$tempstr = $tokenID . date('YmdGis') . "1";
    	$request_id = md5($tempstr);

    	return $this->runTransaction($plist, $request_id);

    }


    // sale
    function sale_transaction($card_number, $card_expire, $amount, $currency = 'USD', $data_array = array()) {

      if ($this->validate_card_number($card_number) == false) {
        $this->set_errors('Card Number not valid');
        return false;
      }
      if ($this->validate_card_expire($card_expire) == false) {
        $this->set_errors('Card Expiration Date not valid');
        return false;
      }
      if (!is_numeric($amount) || $amount <= 0) {
        $this->set_errors('Amount is not valid');
        return false;
      }
      if (!in_array($currency, $this->currencies_allowed)) {
        $this->set_errors('Currency not allowed');
        return false;
      }

      // body
      $plist = 'USER=' . $this->user . '&';
      $plist .= 'VENDOR=' . $this->vendor . '&';
      $plist .= 'PARTNER=' . $this->partner . '&';
      $plist .= 'PWD=' . $this->password . '&';
      $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
      $plist .= 'TRXTYPE=' . 'S' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
      $plist .= 'ACCT=' . $card_number . '&';
      $plist .= 'EXPDATE=' . $card_expire . '&';
      $plist .= 'AMT=' . $amount . '&';
      // extra data
      $plist .= 'CURRENCY=' . $currency . '&';
      $plist .= 'COMMENT1=' . $data_array['comment1'] . '&';
      $plist .= 'NAME=' . $data_array['name'] . '&';
      $plist .= 'STREET=' . $data_array['street'] . '&';
      $plist .= 'ZIP=' . $data_array['zip'] .  '&';
      $plist .= 'COUNTRY=US';//. $data_array['country'] . '&';
      if (isset($data_array['cvv'])) {
        $plist .= 'CVV2=' . $data_array['cvv'] . '&';
      }
      $plist .= 'CLIENTIP=' . $data_array['clientip'] . '&';
      // verbosity
      $plist .= 'VERBOSITY=MEDIUM';

      $tempstr = $card_number . $amount . date('YmdGis') . "1";
      $request_id = md5($tempstr);

      return $this->runTransaction($plist, $request_id);

    }

        // reference sale
    function reference_sale_transaction($origid, $amount, $currency = 'USD', $data_array = array()) {

      if (strlen($origid) < 3) {
        $this->set_errors('OrigID not valid');
        return false;
      }

      if (!is_numeric($amount) || $amount <= 0) {
        $this->set_errors('Amount is not valid');
        return false;
      }

      if (!in_array($currency, $this->currencies_allowed)) {
        $this->set_errors('Currency not allowed');
        return false;
      }

      // body
      $plist = 'USER=' . $this->user . '&';
      $plist .= 'VENDOR=' . $this->vendor . '&';
      $plist .= 'PARTNER=' . $this->partner . '&';
      $plist .= 'PWD=' . $this->password . '&';
      $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
      $plist .= 'TRXTYPE=' . 'S' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
      $plist .= "ORIGID=" . $origid . "&"; // ORIGID to the PNREF value returned from the original transaction
      $plist .= 'AMT=' . $amount . '&';
      $plist .= 'CURRENCY=' . $currency . '&';

      $plist .= 'COMMENT1=' . $data_array['comment1'] . '&';
      $plist .= 'CLIENTIP=' . $data_array['clientip'] . '&';
      // verbosity
      $plist .= 'VERBOSITY=MEDIUM';

      $tempstr = $origid . $amount . date('YmdGis') . "1";
      $request_id = md5($tempstr);

      return $this->runTransaction($plist, $request_id);

    }


    // Authorization
    function authorization($card_number, $card_expire, $amount, $currency = 'USD', $data_array = array()) {

      if ($this->validate_card_number($card_number) == false) {
        $this->set_errors('Card Number not valid');
        return false;
      }
      if ($this->validate_card_expire($card_expire) == false) {
        $this->set_errors('Card Expiration Date not valid');
        return false;
      }
      if (!is_numeric($amount) || $amount <= 0) {
        $this->set_errors('Amount is not valid');
        return false;
      }
      if (!in_array($currency, $this->currencies_allowed)) {
        $this->set_errors('Currency not allowed');
        return false;
      }

      // body
      $plist = 'USER=' . $this->user . '&';
      $plist .= 'VENDOR=' . $this->vendor . '&';
      $plist .= 'PARTNER=' . $this->partner . '&';
      $plist .= 'PWD=' . $this->password . '&';
      $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
      $plist .= 'TRXTYPE=' . 'A' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
      $plist .= 'ACCT=' . $card_number . '&';
      $plist .= 'EXPDATE=' . $card_expire . '&';
      $plist .= 'AMT=' . $amount . '&';
      // extra data
      $plist .= 'CURRENCY=' . $currency . '&';
      $plist .= 'COMMENT1=' . $data_array['comment1'] . '&';
      $plist .= 'NAME=' . $data_array['name'] . '&';
      $plist .= 'CLIENTIP=' . $data_array['clientip'] . '&';
      // verbosity
      $plist .= 'VERBOSITY=MEDIUM';

      $tempstr = $amount . date('YmdGis') . "2";
      $request_id = md5($tempstr);

      return $this->runTransaction($plist, $request_id);
    }

    // Delayed Capture
    function delayed_capture($origid, $data_array = array()) {

      if (strlen($origid) < 3) {
        $this->set_errors('OrigID not valid');
        return false;
      }

      // body
      $plist = 'USER=' . $this->user . '&';
      $plist .= 'VENDOR=' . $this->vendor . '&';
      $plist .= 'PARTNER=' . $this->partner . '&';
      $plist .= 'PWD=' . $this->password . '&';
      $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
      $plist .= 'TRXTYPE=' . 'D' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
      $plist .= "ORIGID=" . $origid . "&"; // ORIGID to the PNREF value returned from the original transaction
      $plist .= 'COMMENT1=' . $data_array['comment1'] . '&';
      $plist .= 'CLIENTIP=' . $data_array['clientip'] . '&';
      $plist .= 'VERBOSITY=MEDIUM';

      $tempstr = $origid . date('YmdGis') . "2";
      $request_id = md5($tempstr);

      return $this->runTransaction($plist, $request_id);
     }


    // Credit Transaction
    function credit_transaction($origid, $amount, $data_array) {

      if (strlen($origid) < 3) {
        $this->set_errors('OrigID not valid');
        return false;
      }

      // body
      $plist = 'USER=' . $this->user . '&';
      $plist .= 'VENDOR=' . $this->vendor . '&';
      $plist .= 'PARTNER=' . $this->partner . '&';
      $plist .= 'PWD=' . $this->password . '&';
      $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
      $plist .= 'TRXTYPE=' . 'C' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
      $plist .= "ORIGID=" . $origid . "&"; // ORIGID to the PNREF value returned from the original transaction
      $plist .= 'AMT=' . $amount . '&';  // amount
      $plist .= 'COMMENT1=' . $data_array['comment1'] . '&';
      $plist .= 'CLIENTIP=' . $data_array['clientip'] . '&';

      $plist .= 'VERBOSITY=MEDIUM';

      $tempstr = $origid . $amount . date('YmdGis') . "2";
      $request_id = md5($tempstr);

      return $this->runTransaction($plist, $request_id);
    }

    // Void Transaction
    function void_transaction($origid, $data_array) {

      if (strlen($origid) < 3) {
        $this->set_errors('OrigID not valid');
        return false;
      }

      // body
      $plist = 'USER=' . $this->user . '&';
      $plist .= 'VENDOR=' . $this->vendor . '&';
      $plist .= 'PARTNER=' . $this->partner . '&';
      $plist .= 'PWD=' . $this->password . '&';
      $plist .= 'TENDER=' . 'C' . '&'; // C = credit card, P = PayPal
      $plist .= 'TRXTYPE=' . 'V' . '&'; //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
      $plist .= "ORIGID=" . $origid . "&"; // ORIGID to the PNREF value returned from the original transaction
      $plist .= 'COMMENT1=' . $data_array['comment1'] . '&';
      $plist .= 'CLIENTIP=' . $data_array['clientip'] . '&';
      $plist .= 'VERBOSITY=MEDIUM';

      $tempstr = $origid . date('YmdGis') . "2";
      $request_id = md5($tempstr);

      return $this->runTransaction($plist, $request_id);
     }

    // Curl custom headers; adjust appropriately for your setup:
    function get_curl_headers() {
      $headers = array();

      $headers[] = "Content-Type: text/namevalue"; //or maybe text/xml
      $headers[] = "X-VPS-Timeout: 30";
      $headers[] = "X-VPS-VIT-OS-Name: Linux";  // Name of your OS
      $headers[] = "X-VPS-VIT-OS-Version: RHEL 4";  // OS Version
      $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  // What you are using
      $headers[] = "X-VPS-VIT-Client-Version: 0.01";  // For your info
      $headers[] = "X-VPS-VIT-Client-Architecture: x86";  // For your info
      $headers[] = "X-VPS-VIT-Integration-Product: MyApplication";  // For your info, would populate with application name
      $headers[] = "X-VPS-VIT-Integration-Version: 0.01"; // Application version

      return $headers;
    }

    // parse result and return an array
    function get_curl_result($result) {
      if (empty($result)) return false;

      $pfpro = array();
      $result = strstr($result, 'RESULT');
      $valArray = explode('&', $result);
      foreach($valArray as $val) {
        $valArray2 = explode('=', $val);
        $pfpro[$valArray2[0]] = $valArray2[1];
      }
      return $pfpro;
    }

    function validate_card_expire($mmyy) {
      if (!is_numeric($mmyy) || strlen($mmyy) != 4) {
        return false;
      }
      $mm = substr($mmyy, 0, 2);
      $yy = substr($mmyy, 2, 2);
      if ($mm < 1 || $mm > 12) {
        return false;
      }
      $year = date('Y');
      $yy = substr($year, 0, 2) . $yy; // eg 2007
      if (is_numeric($yy) && $yy >= $year && $yy <= ($year + 10)) {
      } else {
        return false;
      }
      if ($yy == $year && $mm < date('n')) {
        return false;
      }
      return true;
    }

    // luhn algorithm
    function validate_card_number($card_number) {
      $card_number = preg_replace('/[^0-9]/', '', $card_number);
      if ($card_number < 9) return false;
      $card_number = strrev($card_number);
      $total = 0;
      for ($i = 0; $i < strlen($card_number); $i++) {
        $current_number = substr($card_number, $i, 1);
        if ($i % 2 == 1) {
          $current_number *= 2;
        }
        if ($current_number > 9) {
          $first_number = $current_number % 10;
          $second_number = ($current_number - $first_number) / 10;
          $current_number = $first_number + $second_number;
        }
        $total += $current_number;
      }
      return ($total % 10 == 0);
    }

    function get_errors() {
      if ($this->errors != '') {
        return $this->errors;
      }
      return false;
    }

    function set_errors($string) {
      $this->errors = $string;
    }

    function get_version() {
      return '4.03';
    }
  }
?>