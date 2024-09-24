<?php

class CAppUtil
{
	/**
	 * If a common HTML entity is present, convert to Windows Latin charset. Used by FPDF-based labels.
	 *
	 * @param string $inStr The string to convert
	 *
	 * @return string The converted string
	 */
	static function toPDFCharConversions(string $inStr): string
	{
		$chars = array(
			"'",
			"\xE9",
			"\"",
			"\"",
			"&",
			"\x99"
		);
		$entities = array(
			"&rsquo;",
			"&eacute;",
			"&ldquo;",
			"&rdquo;",
			"&amp;",
			"&trade;"
		);

		return str_replace($entities, $chars, $inStr);
	}

	/**
	 * Attempt to decode a JSON string. If the string is not valid JSON,
	 * return the string unchanged.
	 *
	 * @param string  $string      The string to decode
	 * @param boolean $assoc_array If true, return the data as an associative array
	 *
	 * @return mixed The decoded JSON string, or the original string if not valid JSON
	 */
	static function return_string_or_json(string $string, bool $assoc_array = false): mixed
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

	/**
	 * Output a JSON encoded associative array as a message to the processor
	 * and exit the script unless $no_exit is set to true.
	 *
	 * @param array    $messageArray        associative array to be JSON encoded
	 * @param bool     $no_exit             if set to true, the script won't exit
	 * @param bool|int $json_encode_options options for json_encode()
	 */
	static function processorMessageEcho(array $messageArray, bool $no_exit = false, bool|int $json_encode_options = false): void
	{
		echo json_encode($messageArray, $json_encode_options);

		if (empty($no_exit))
		{
			exit;
		}
	}

	/**
	 * Generate a unique string by combining a timestamp with a random string of characters from a provided pool.
	 *
	 * @param int $length the length of the random string to generate, defaults to 20
	 *
	 * @return string the generated unique string
	 */
	static function generateUniqueString(int $length = 20): string
	{
		$pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

		mt_srand((double)microtime() * 1000000);

		$unique_id = "";

		for ($index = 0; $index < $length; $index++)
		{
			$unique_id .= substr($pool, (mt_rand() % (strlen($pool))), 1);
		}// end for

		return (dechex(time()) . $unique_id);
	}

	/**
	 * Truncates a string to a certain length, appending an ellipsis if it exceeds that length.
	 *
	 * @param string $string The string to truncate.
	 * @param int    $limit  The maximum length of the string, defaults to 30.
	 *
	 * @return string The truncated string.
	 */
	static function truncate(string $string, int $limit = 30): string
	{
		if (strlen($string) > $limit)
		{
			return substr($string, 0, $limit - 3) . '...';
		}

		return $string;
	}

	/**
	 * Convert a hexadecimal string into a regular string.
	 *
	 * @param string $hex a hexadecimal string
	 *
	 * @return string the converted string
	 */
	static function hexToStr(string $hex): string
	{
		$string = '';
		for ($i = 0; $i < strlen($hex) - 1; $i += 2)
		{
			$string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
		}

		return $string;
	}

	/**
	 * Converts a string into a hexadecimal string where each character of the string is converted to 2 characters in the new string.
	 * The high-order 4 bits of each character in the original string are converted to the first character in the new string and the low-order
	 * 4 bits are converted to the second character in the new string.
	 *
	 * @param string $inStr The string to convert
	 *
	 * @return string The converted string
	 */
	static function hexDigit(string $inStr): string
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

	/**
	 * Ensures that all strings in an array or string are encoded as UTF-8.
	 *
	 * If the input is an array, this function iterates over it recursively and
	 * applies the same logic to each element.  If the element is a string, it
	 * is converted to UTF-8 using utf8_encode.  If the element is not a string,
	 * it is left unchanged.
	 *
	 * @param array|string $d The array or string to convert.
	 *
	 * @return array|string The converted array or string.
	 */
	static function utf8ize(array|string $d): array|string
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

	/**
	 * Validate a Google reCaptcha response.
	 *
	 * @param string $response The user's response to the reCaptcha challenge.
	 *
	 * @return boolean True if the response is valid, false if not.
	 */
	static function validateGoogleCaptchaResponse(string $response): bool
	{
		/*
		{
			  "success": true|false,
			  "challenge_ts": timestamp,  // timestamp of the challenge load (ISO format yyyy-MM-dd'T'HH:mm:ssZZ)
			  "hostname": string,         // the hostname of the site where the reCAPTCHA was solved
			  "error-codes": [...]        // optional
		}
		*/
		$content = "secret=" . CAPTCHA_SECRET . "&response=" . $response;

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

		if ($status != 200)
		{
			CLog::RecordNew(CLog::ERROR, "validateGoogleCaptchaResponse Error: call to URL \r\n https://www.google.com/recaptcha/api/siteverify failed with status \r\n $status, response \r\n $response, curl_error \r\n" . curl_error($curl) . ", curl_errno \r\n" . curl_errno($curl), "", "", true);

			return false;
		}

		$result = json_decode($json_response, true);

		curl_close($curl);

		if (!$result['success'])
		{
			CLog::RecordNew(CLog::NOTICE, "Captcha Failure");
		}

		return $result['success'];
	}

	/**
	 * Finds the value of an element in an XML string.
	 *
	 * @param string $element_name The name of the XML element to search for.
	 * @param string $xml          The XML string to search in.
	 * @param bool   $content_only Whether to return only the content of the element
	 *                             (i.e., exclude the enclosing tags), or the full pattern match (i.e.,
	 *                             include the enclosing tags).  Defaults to true.
	 *
	 * @return string|false The value of the element if found, otherwise false.
	 */
	static function get_value_in_xml(string $element_name, string $xml, bool $content_only = true): bool|string
	{
		if (!$xml)
		{
			return false;
		}
		$found = preg_match('#<' . $element_name . '(?:\s+[^>]+)?>(.*?)' . '</' . $element_name . '>#s', $xml, $matches);
		if ($found)
		{
			if ($content_only)
			{
				return $matches[1];  //ignore the enclosing tags
			}
			else
			{
				return $matches[0];  //return the full pattern match
			}
		}

		// No match found: return false.
		return false;
	}

	/**
	 * Returns a valid 10-digit string or false.
	 *
	 * @param string $phoneNumber
	 *
	 * @return string|false
	 */
	static function normalizePhoneNumber(string $phoneNumber): bool|string
	{
		$allNumbers = preg_replace('/\D/', '', $phoneNumber);

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

	/**
	 * @param string $trackingNumber
	 *
	 * @return string
	 */
	public static function fedexTrackingUrl(string $trackingNumber): string
	{
		return "https://www.fedex.com/fedextrack/?tracknumbers=$trackingNumber&cntry_code=us";
	}

	/**
	 * Returns the URL for a given UPS tracking number.
	 *
	 * @param string $trackingNumber
	 *
	 * @return string
	 */
	public static function upsTrackingUrl(string $trackingNumber): string
	{
		return "https://wwwapps.ups.com/tracking/tracking.cgi?tracknum=$trackingNumber";
	}

}