<?php

require_once("includes/CPageProcessor.inc");

class calculatetax_processor extends CPageProcessor {


	private $token  = false;

	function runPublic()
	{
		$this->runProcessor();
	}

	function runCustomer()
	{
        $this->runProcessor();
    }
    
    function runProcessor() {

        if(isset($_POST['op'])) {
            switch( $_POST['op']) {
                case 'getTaxRates':
                    $taxamount = $this->getTaxRates();
                    CAppUtil::processorMessageEcho(array(
                        'processor_success' => true,
                        'processor_message' => 'Success!',
                        'tax_amount' => $taxamount
                    ));
                    break;
            }
        }

    }

    public function getTaxRates($OrderObj) {

    // Destination Address
    //print_r($OrderObj);
    $data = array();
    $data = array_merge($data, array("orderid" => $OrderObj->orderAddress->order_id));
    $data = array_merge($data, array("firstname" => $OrderObj->orderAddress->firstname));
    $data = array_merge($data, array("lastname" => $OrderObj->orderAddress->lastname));
    $data = array_merge($data, array("phone_1" => $OrderObj->orderAddress->telephone_1));
    $data = array_merge($data, array("address_1" => $OrderObj->orderAddress->address_line1));
    $data = array_merge($data, array("address_2" => $OrderObj->orderAddress->address_line2));
    $data = array_merge($data, array("city" => $OrderObj->orderAddress->city));
    $data = array_merge($data, array("county" => $OrderObj->orderAddress->county));
    $data = array_merge($data, array("state" => $OrderObj->orderAddress->state_id));
    $data = array_merge($data, array("postal_code" => $OrderObj->orderAddress->postal_code));
    $data = array_merge($data, array("country_id" => $OrderObj->orderAddress->country_id));
    $data = array_merge($data, array("date_ordered" => $OrderObj->orderAddress->date_ordered));
    if ( is_null($OrderObj->user_id) ) {
      $data = array_merge($data, array("user_id" => 'unavailable'));
    } else {
      $data = array_merge($data, array("user_id" => $OrderObj->user_id));
    }

    // Total Food Cost (possibly as list of items)
    $foodcost = $OrderObj->subtotal_all_items - $OrderObj->subtotal_delivery_fee;
    $data = array_merge($data, array("food_cost" => $foodcost));

    // Delivery Fee
    $data = array_merge($data, array("delivery_fee" => $OrderObj->subtotal_delivery_fee));
/*
		1) Check cache
		2) If cached return cache value
		3) Otherwise call submitTransaction
*/
    	return $this->submitTransaction($data);

    }

    public function submitTransaction($data)
	  {



        $payload = '{
            "lines": [
              {
                "number": "1",
                "quantity": 1,
                "amount": "'.$data["food_cost"].'",
                "taxCode": "PF160059",
                "itemCode": "'.$data["orderid"].'",
                "description": "Food Cost Order ID #'.$data["orderid"].'"
              },
              {
                "number": "2",
                "quantity": 1,
                "amount": "'.$data["delivery_fee"].'",
                "taxCode": "FR000000",
                "itemCode": "'.$data["orderid"].'",
                "description": "Delivery Fee Order ID #'.$data["orderid"].'"
              }
            ],
            "type": "SalesInvoice",
            "companyCode": "DEFAULT",
            "date": "'.$data["date_ordered"].'",
            "customerCode": "'.$data["user_id"].'",
            "purchaseOrderNo": '.$data["orderid"].',
            "addresses": {
              "singleLocation": {
                "line1": "'.$data["address_1"].'",
                "line2": "'.$data["address_2"].'",
                "city": "'.$data["city"].'",
                "region": "'.$data["state"].'",
                "country": "'.$data["country_id"].'",
                "postalCode": '.$data["postal_code"].'
              }
            },
            "commit": true,
            "currencyCode": "USD",
            "description": "Order ID: '.$data["orderid"].'"
          }';
          

        //echo "Payload: " . $payload . "<br>";
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
            $result =  array('error_occurred' => true, 'description' => "attemptLogin Error: call to URL \r\n $url failed with status \r\n $status, response \r\n $response, curl_error", "curl_error" => curl_error($curl) , "error_number" => curl_errno($curl));
            CLog::RecordNew(CLog::ERROR, "Login to Avalara AvaTax failed: " . print_r($result, true), "","", true);
            return false;
        }

        $fields = json_decode($response, true);

        $taxes = '{"food_tax": '.$fields["lines"][0]["tax"].', "delivery_fee_tax": '.$fields["lines"][1]["tax"].' }';

        return $taxes;
        

	  }


}