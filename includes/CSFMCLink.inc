<?php


require_once("includes/DAO/CFactory.php");
require_once("includes/CLog.inc");



class CSFMCLink
{
    var $token = false;
    var $instanceURL = "https://wa98290.my.";

    const DD_SMS_SHORT_CODE = '73328';

    var $ReadWriteServers = array('LIVE', 'RESPONSIVE', 'CARL');


    private function isReadOnly()
    {
        if (defined('DD_SERVER_NAME') && !in_array(DD_SERVER_NAME, $this->ReadWriteServers))
        {
            return true;
        }

        return false;

        // so if DD_SERVER_NAME not defined we are still readwrite - in case the define is blown away the live site still works
        // but if it is defined an not one of the designated servers we are read only
    }

    function attemptLogin()
    {
        $url = "https://mc01mx89tgtvsgs7trq9ghzl-v9q.auth.marketingcloudapis.com/v2/token";

        $json_request = json_encode(array(
            "grant_type" => "client_credentials",
            "client_id" => SFMC_CLIENT_ID,
            "client_secret" => SFMC_CLIENT_SECRET,
        ));

        $curl = curl_init($url);

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status != 200) {
            //throw new Exception("attemptLogin Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl));
            return array('error_occurred' => true, 'description' => "attemptLogin Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));
        }


        $fields = json_decode($response, true);
        $sessionID = $fields['access_token'];

        return $sessionID;
    }


    function getSMSSubscriptionStatus($phoneNumber)
    {
        if (!$this->token)
        {
            $this->token = $this->attemptLogin();
        }


        $url = "https://mc01mx89tgtvsgs7trq9ghzl-v9q.rest.marketingcloudapis.com/sms/v1/contacts/subscriptions";

        $json_request = json_encode(array("mobileNumber" => array($phoneNumber)));

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$this->token}", "Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status != 200 && $status != 201 && $status != 202) {
          //  throw new Exception("getJobID Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl));
            return array('error_occurred' => true, 'description' => "getSMSSubscriptionStatus Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));

        }

        $data = json_decode($response, true);
        return $data;

/*
         * data example
* Array
(
    [count] => 1
    [createDate] => 2020-03-02T22:42:25.4025850Z
    [completeDate] => 2020-03-02T22:42:25.4807156Z
    [contacts] => Array
        (
            [0] => Array
                (
                    [mobileNumber] => 14254205526
                    [shortCode] => 73328
                    [keyword] => DD_TEST_2_STEP
                    [optInDate] => 2020-02-28T16:24:12.9370000
                    [status] => active
                )

        )

)

         */
    }

    function optinToKeyword($phoneNumber, $keyword)
    {
        if ($this->isReadOnly())
        {
            // might need to return simulated data for effective testing
            return array('error_occurred' => true, 'description' => "This is a read only server");
        }


        if (!$this->token)
        {
            $this->token = $this->attemptLogin();
        }

        $url = "https://mc01mx89tgtvsgs7trq9ghzl-v9q.rest.marketingcloudapis.com/sms/v1/queueMO";

        $json_request = json_encode(array("mobileNumbers" => array($phoneNumber), "shortCode" => self::DD_SMS_SHORT_CODE, "messageText" => $keyword));


        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$this->token}", "Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status != 200 && $status != 201 && $status != 202) {
       //     throw new Exception("getJobID Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl));
            return array('error_occurred' => true, 'description' => "getJobID Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));
        }


        $data = json_decode($response, true);

        return $data;
    }

    function optoutOfAllKeywords($phoneNumber)
    {
        if ($this->isReadOnly())
        {
            // might need to return simulated data for effective testing
            return array('error_occurred' => true, 'description' => "This is a read only server");
        }

        if (!$this->token)
        {
            $this->token = $this->attemptLogin();
        }

        $url = "https://mc01mx89tgtvsgs7trq9ghzl-v9q.rest.marketingcloudapis.com/sms/v1/queueMO";

        $json_request = json_encode(array("mobileNumbers" => array($phoneNumber), "shortCode" => self::DD_SMS_SHORT_CODE, "messageText" => 'STOP'));


        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$this->token}", "Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status != 200 && $status != 201 && $status != 202) {
          //  throw new Exception("getJobID Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl));
            return array('error_occurred' => true, 'description' => "getJobID Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));

        }

        $data = json_decode($response, true);
        return $data;
    }



    function addKeyword($keyword)
    {
        if ($this->isReadOnly())
        {
            // might need to return simulated data for effective testing
            return array('error_occurred' => true, 'description' => "This is a read only server");
        }

        if (!$this->token)
        {
            $this->token = $this->attemptLogin();
        }

        $url = "https://mc01mx89tgtvsgs7trq9ghzl-v9q.rest.marketingcloudapis.com/sms/v1/keyword";

        $json_request = json_encode(array("shortCode" => self::DD_SMS_SHORT_CODE, "CountryCode" => 'US', "Keyword" => $keyword));


        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$this->token}", "Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status != 200 && $status != 201 && $status != 202) {
            throw new Exception("getJobID Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl));
        }

        curl_close($curl);

        $data = json_decode($response);

        print_r($data);

        return $data;

    }

// DD_SESSION_TEST
// YmtfR2dISGpqRWlMNGV0TVYwRFNTdzo4Njow

    function retrieveSMSPreferences($phoneNumber)
    {
        if ($this->isReadOnly())
        {
            // might need to return simulated data for effective testing
            return array('error_occurred' => true, 'description' => "This is a read only server");
        }

        $phoneNumber = CAppUtil::normalizePhoneNumber($phoneNumber);

        if ($phoneNumber)
        {
            $this->token = $this->attemptLogin();
            $data = $this->getSMSSubscriptionStatus($phoneNumber);
            return $data;
        }

        return false;
    }

}
//$test = new CSFMCLink();
//print_r($test->getSMSSubscriptionStatus('14254205526'));


?>