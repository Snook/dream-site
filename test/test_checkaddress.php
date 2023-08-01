<?php

$mysqli = new mysqli('127.0.0.1', 'root', '', 'dreamsite');


function ckaddress($addr) {
    $url = "https://sandbox-rest.avatax.com/api/v2/addresses/resolve";
    $authorization = "Authorization: Basic amltLnRzZW5nQGRyZWFtZGlubmVycy5jb206VmlydHVlMTEk";

    $payload = '{"line1":"' . $addr['address_line1'] . '",';
    $payload .= '"line2":"' . $addr['address_line2'] . '",';
    $payload .= '"city":"' . $addr['city'] . '",';
    $payload .= '"region":"' . $addr['state_id'] . '",';
    $payload .= '"country":"US",';
    $payload .= '"postalCode":"' . $addr['postal_code'] . '"}';
    echo $payload . " :: ";

    $curl = curl_init($url);

    //$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
    curl_setopt($curl, CURLOPT_HEADER, false);
    //curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json; charset=UTF-8", $authorization));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($curl);
    if ($response === false)
    {
        $err = curl_error($curl);
    }


    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($status != 200 && $status != 201) {
        // $result =  array('error_occurred' => true, 'description' => "attemptLogin Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));
        // CLog::RecordNew(CLog::ERROR, "Login to Avalara AvaTax failed: " . print_r($result, true), "","", true);
        // return false;
    };

    $fields = json_decode($response, true);

    //print_r($fields);

    //$sessionID = $fields['access_token'];
    if ($fields['validatedAddresses'][0]['addressType'] === "UnknownAddressType") {
        echo "Invalid Adddress\r\n";
    } else {
        echo "Valid Address\r\n";
    }
}

$sql = "SELECT * FROM address WHERE id>902430 and id<902440";
if (!$result = $mysqli->query($sql)) {
    // Oh no! The query failed. 
    echo "Sorry, the website is experiencing problems.";

    // Again, do not do this on a public site, but we'll show you how
    // to get the error information
    echo "Error: Our query failed to execute and here is why: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    echo "Error: " . $mysqli->error . "\n";
    exit;
} else {

    while ($address = $result->fetch_assoc()) {
        //echo "ID: " . $address['id'] . "\r\n";
        //echo "Address 1: " . $address['address_line1'] . "\r\n";
        //echo "Address 2: " . $address['address_line2'] . "\r\n";
        //echo "City: " . $address['city'] . "\r\n";
        //echo "State: " . $address['state_id'] . "\r\n";
        //echo "Zip Code: " . $address['postal_code'] . "\r\n";

        $arr = array(
            "address_line1" => $address['address_line1'],
            "address_line2" => $address['address_line2'],
            "city" => $address['city'],
            "state_id" => $address['state_id'],
            "postal_code" => $address['postal_code']
        );

        ckaddress($arr);
        echo "\r\n";

    };

};



?>