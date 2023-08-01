<?php

class CAppUtil
{
	/*
	*  If a common html entity is present, convert to Windows Latin charset. Used by FPDF based labels
	*/
	static function toPDFCharConversions($inStr)
	{
		$chars = array(
			"'",
			"\xE9",
			"\"",
			"\"",
			"&",
			"\x99"
		);
		$ents = array(
			"&rsquo;",
			"&eacute;",
			"&ldquo;",
			"&rdquo;",
			"&amp;",
			"&trade;"
		);

		return str_replace($ents, $chars, $inStr);
	}

	static function return_string_or_json($string, $assoc_array = false)
	{
		// decode the JSON data
		$result = json_decode($string, $assoc_array);

		// switch and check possible JSON errors
		switch (json_last_error())
		{
			case JSON_ERROR_NONE:
				break;
			default:
				$result = $string;
				break;
		}

		// everything is OK
		return $result;
	}

	static function processorMessageEcho($messageArray, $noexit = false, $json_encode_options = false)
	{
		echo json_encode($messageArray, $json_encode_options);

		if (empty($noexit))
		{
			exit;
		}
	}

	static function generateUniqueString($length = 20)
	{
		$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

		// set pool of possible char
		if ($pool == "")
		{
			$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$pool .= "abcdefghijklmnopqrstuvwxyz";
			$pool .= "0123456789";
		}// end if

		mt_srand((double)microtime() * 1000000);

		$unique_id = "";

		for ($index = 0; $index < $length; $index++)
		{
			$unique_id .= substr($pool, (mt_rand() % (strlen($pool))), 1);
		}// end for

		return (dechex(time()) . $unique_id);
	}

	static function truncate($string, $limit = 30)
	{
		if (strlen($string) > $limit)
		{
			return substr($string, 0, $limit - 3) . '...';
		}

		return $string;
	}

	static function truncateTextAddElipsis(&$text, $cutOffIndex)
	{
		$retVal = wordwrap($text, $cutOffIndex);
		$retVal = explode("\n", $retVal);

		if (isset($retVal[1]) && strlen($retVal[1]) > 1)
		{
			$retVal = $retVal[0] . '...';
		}
		else
		{
			$retVal = $retVal[0];
		}

		return $retVal;
	}

	//utility function to convert key for use by PHP's mycrypt library
	static function hexToStr($hex)
	{
		$string = '';
		for ($i = 0; $i < strlen($hex) - 1; $i += 2)
		{
			$string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
		}

		return $string;
	}

	static function hexDigit($inStr)
	{
		$HEX = array(
			'0',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'a',
			'b',
			'c',
			'd',
			'e',
			'f'
		);

		$outStr = "";

		for ($i = 0, $j = strlen($inStr); $i < $j; $i++)
		{
			$low = ord($inStr[$i]) & 0xF;
			$high = (ord($inStr[$i]) & 0xF0) >> 4;
			$outStr .= $HEX[$high];
			$outStr .= $HEX[$low];
		}

		return $outStr;
	}

	static function utf8ize($d)
	{
		if (is_array($d))
		{
			foreach ($d as $k => $v)
			{
				$d[$k] = self::utf8ize($v);
			}
		}
		else if (is_string($d))
		{
			return utf8_encode($d);
		}

		return $d;
	}

	static function validateGoogleCaptchaResponse($response)
    {
        /*
        {
              "success": true|false,
              "challenge_ts": timestamp,  // timestamp of the challenge load (ISO format yyyy-MM-dd'T'HH:mm:ssZZ)
              "hostname": string,         // the hostname of the site where the reCAPTCHA was solved
              "error-codes": [...]        // optional
        }
        */
        $content ="secret=" . CAPTCHA_SECRET .  "&response=" . $response;


        $curl = curl_init("https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      //  curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json; charset=UTF-8", "Accept: json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ( $status != 200)
        {
            CLog::RecordNew(CLog::ERROR, "validateGoogleCaptchaResponse Error: call to URL \r\n https://www.google.com/recaptcha/api/siteverify failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl), "", "", true);
            return false;
        }

        $result = json_decode($json_response, true);

        curl_close($curl);

        if (!$result['success'])
        {
            CLog::RecordNew(CLog::NOTICE, "Captcha Failure", "", "", false);
        }

        return $result['success'];
    }

    static function get_value_in_xml($element_name, $xml, $content_only = true) {
        if ($xml == false) {
            return false;
        }
        $found = preg_match('#<'.$element_name.'(?:\s+[^>]+)?>(.*?)'.
            '</'.$element_name.'>#s', $xml, $matches);
        if ($found != false) {
            if ($content_only) {
                return $matches[1];  //ignore the enclosing tags
            } else {
                return $matches[0];  //return the full pattern match
            }
        }
        // No match found: return false.
        return false;
    }


    // returns a valid 10 digit string or false
    static function normalizePhoneNumber($phoneNumber)
    {

        $allNumbers =  preg_replace('/\D/', '', $phoneNumber);

        if (strlen($allNumbers) == 10)
        {
            if ($allNumbers[0] == "1")
            {
                // must be a partial number
                // https://en.wikipedia.org/wiki/Category:Area_codes_in_the_United_States
                return false;
            }

            return "1" . $allNumbers;
        }
        else if (strlen($allNumbers) == 11)
        {
            if ($allNumbers[0] == "1")
            {
                return $allNumbers;
            }

            return false;
        }

        return false;


    }

    public static function fedexTrackingUrl($trackingNumber)
	{
		return "https://www.fedex.com/fedextrack/?tracknumbers={$trackingNumber}&cntry_code=us";
	}

}

?>