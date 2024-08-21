<?php

class ValidationRules
{
	public static $_dict = null; //pwd dictionary

	/**
	 * Validates an email address, copied from HTML_QuickForm_Rule_Email
	 *
	 * @param string  $email       Email address
	 * @param boolean $checkDomain True if dns check should be performed
	 *
	 * @access    public
	 * @return    boolean   true if email is valid
	 */
	static function validateEmail(string $email, bool $checkDomain = false): bool
	{
		$email = trim($email);

		if (filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			if ($checkDomain && function_exists('checkdnsrr'))
			{
				$tokens = explode('@', $email);
				if (checkdnsrr($tokens[1], 'MX') || checkdnsrr($tokens[1], 'A'))
				{
					return true;
				}

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Returns false if the password is based on a dictionary word, forward or reversed
	 */
	static function isCrackproof($password)
	{

		if (defined('PWD_DICTIONARY') && function_exists("crack_opendict"))
		{
			if (!self::$_dict)
			{
				self::$_dict = crack_opendict(PWD_DICTIONARY);
			}
			$check = crack_check(self::$_dict, $password);

			// Retrieve messages
			$diag = crack_getlastmessage();

			if (strstr($diag, 'dictionary word') !== false)
			{
				return false;
			}

			return true;
		}

		return true;
	}

	static function isValidPositiveDecimal($inNumber)
	{

		$retVal = true;
		if (!is_numeric($inNumber))
		{
			$retVal = false;
		}

		if ($inNumber <= 0)
		{
			$retVal = false;
		}

		return $retVal;
	}

	static function telephoneCheck($telephoneStr)
	{

		$teleArr = explode("-", $telephoneStr);

		$isValid = true;
		if (count($teleArr) != 3)
		{
			$isValid = false;
		}

		if ($isValid)
		{
			if (strlen($teleArr[0]) != 3)
			{
				$isValid = false;
			}
			else if (strlen($teleArr[1]) != 3)
			{
				$isValid = false;
			}
			else if (strlen($teleArr[2]) != 4)
			{
				$isValid = false;
			}
		}

		if ($isValid && !is_numeric($teleArr[0]))
		{
			$isValid = false;
		}
		else if ($isValid && !is_numeric($teleArr[1]))
		{
			$isValid = false;
		}
		else if ($isValid && !is_numeric($teleArr[2]))
		{
			$isValid = false;
		}

		return $isValid;
	}
}

?>