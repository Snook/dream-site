<?php



require_once("includes/DAO/CFactory.php");
require_once("includes/CLog.inc");
require_once("includes/CAppUtil.inc");

class CSalesforceLink
{
    var $token = false;
    var $instanceURL = "https://wa98290.my.";

    var $ReadWriteServers = array('LIVE', 'RESPONSIVE', 'CARL', 'SANDBOX');


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

    private function attemptLogin()
    {

        $url = "https://login.salesforce.com/services/Soap/u/42.0";

        $soap_request = '<?xml version="1.0" encoding="utf-8" ?>
                        <env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
                          <env:Body>
                            <n1:login xmlns:n1="urn:partner.soap.sforce.com">
                              <n1:username>' . SALESFORCE_INTEGRATION_USER . '</n1:username>
                              <n1:password>'. SALESFORCE_INTEGRATION_PASSWORD . '</n1:password>
                            </n1:login>
                          </env:Body>
                        </env:Envelope>';

        $curl = curl_init($url);

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: text/xml; charset=UTF-8", "SOAPAction: login"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $soap_request);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status != 200) {
            $result =  array('error_occurred' => true, 'description' => "attemptLogin Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));
            CLog::RecordNew(CLog::ERROR, "Login to Salesforce failed: " . print_r($result, true), "","", true);
            curl_close($curl);
            return false;
        }

        $sessionID = CAppUtil::get_value_in_xml('sessionId', $response);
        curl_close($curl);

        return $sessionID;
    }

    private function fetchPrefsFromSalesforce($userID)
    {

        if (!$this->token)
        {
            $this->token = $this->attemptLogin();
        }

        if (!is_numeric($userID) || $userID <= 0) {
            CLog::RecordNew(CLog::ERROR, "Invalid user id sent to fetchPrefsFromSalesforce:" . $userID, "", "", true);
            return false;
        }

        $url = $this->instanceURL . "salesforce.com/services/data/v47.0/sobjects/Account/DD_USER_ID__c/$userID?fields=primary_email__c,HasOptOut_OffersPromotions__c,HasOptOut_Platepoints__c,HasOptOut_SessionReminders__c,HasOptOut_Surveys__c";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$this->token}", "Content-type: application/json"));
        // curl_setopt($curl, CURLOPT_POST, true);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $soap_request);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status != 200 && $status != 201) {
            $errorArr = array('error_occurred' => true, 'description' => "fetchPrefsFromSalesforce Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl), "status" => $status);
            curl_close($curl);
            return $errorArr;
        }
        curl_close($curl);
        $data = json_decode($response, true);

        return $data;

    }

    public function setPrefArrayInSalesforce($userID, $prefArray)
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

        if (!is_numeric($userID) || $userID <= 0) {
            CLog::RecordNew(CLog::ERROR, "Invalid user id sent to setPrefArrayInSalesforce:" . $userID, "", "", true);
            return false;
        }

        $url = $this->instanceURL . "salesforce.com/services/data/v47.0/sobjects/Account/DD_USER_ID__c/$userID";

        $json = json_encode($prefArray);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$this->token}", "Content-type: application/json"));
        //   curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status != 200 && $status != 201) {
            $errorArr = array('error_occurred' => true, 'description' => "setPrefArrayInSalesforce Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));
            CLog::Record("CURL_ERROR: " . print_r($errorArr,true));
            curl_close($curl);
            return $errorArr;
        }

        curl_close($curl);
        $data = json_decode($response, true);
        return $data;

    }

    public function setPrefInSalesforce($userID, $prefName, $prefVal)
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

        if (!is_numeric($userID) || $userID <= 0) {
            CLog::RecordNew(CLog::ERROR, "Invalid user id sent to setPrefInSalesforce:" . $userID, "", "", true);
            return false;
        }

        $url = $this->instanceURL . "salesforce.com/services/data/v47.0/sobjects/Account/DD_USER_ID__c/$userID";

        $json = json_encode(array($prefName => $prefVal));

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$this->token}", "Content-type: application/json"));
     //   curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status != 200 && $status != 201) {
            $errorArr =  array('error_occurred' => true, 'description' => "setPrefInSalesforce Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));
            CLog::Record("CURL_ERROR: " . print_r($errorArr,true));
            curl_close($curl);
            return $errorArr;

        }
        curl_close($curl);
        $data = json_decode($response, true);

        return $data;
    }

    public function retrieveEmailPreferences($user_id)
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

        $data = $this->fetchPrefsFromSalesforce($user_id);

        return $data;
    }

}

//$testObj = new CSalesforceLink();
//$testObj->setPrefInSalesforce(400252, 'HasOptOut_SessionReminders__c', true);
//$testObj->retrieveEmailPreferences(1111111);
?>
