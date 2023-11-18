<?php
// payment.php - processing payments

require('paypal/payflow_curl.php');
require_once('CCrypto.inc');
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/Corporate_office_data.php');

class PayPalProcess
{

	private $_result = null;
	private $_request = null;
	private $_users_explanation = null;
	private $_merchant_account_id = 0;
	private $_is_delivered_payment = false;

	function __construct()
	{
	}

	function setIsDeliveredPayment()
	{
		$this->_is_delivered_payment = true;
	}

	/**
	 * Returns the response from verisign after call processPayment
	 */
	function getResult()
	{
		return $this->_result;
	}

	/**
	 * Returns a printable (no pwd,cc#) version of the transaction request for debugging/logging
	 */
	function getRequest()
	{
		return $this->_request;
	}

	/**
	 * Returns the short plain english explanation of the result code
	 */
	function getResponseMessage()
	{
		return $this->_result['RESPMSG'];
	}

	function getUsersExplanation()
	{
		if (is_null($this->_users_explanation))
		{
			$this->_users_explanation = '';
		}

		return $this->_users_explanation;
	}

	function getMerchantAccountId()
	{
		return $this->_merchant_account_id;
	}

	function getSystemName()
	{
		return 'PAYFLOW';
	}

	static function getFullErrorDescription($result, $store_id = false)
	{
		$retVal = array(
			"errorCode" => $result['RESULT'],
			"errorAbbrev" => "",
			"errorDesc" => ""
		);

		switch ($result['RESULT'])
		{

			//merchant/system related
			case 1: //user authentication failed
			case 26:
				@CLog::NotifyFadmin($store_id, '!!Dreamdinners Server Alert!!', 'The Dreamdinners website ' . 'tried to process a credit card for your store, but the merchant account login has failed. ' . 'This may happen if you have recently changed your merchant account information, please notify ' . 'DreamDinners support staff.');
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "The Dreamdinners website tried to process a credit card for your store, but the merchant account login has failed.
	  					This may happen if you have recently changed your merchant account information, please notify DreamDinners support staff.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 2: //invalid tender
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Invalid Tender.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 3: //invalid tx type
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Invalid Transaction Type.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 4: //invalid merchant info
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Invalid Merchant Info.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 7: //field format error
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Field Format Error.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 8: //not a transaction server
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Not a Transaction Server.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 9: //too many parameters
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Too Many Parameters.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 10: //too many items
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Too Many Items.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 105: //credit error
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Credit Error.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 106: //hosts not available
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "A system configuration occurred: Host not Available.  Dream Dinners support staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, true);
				break;
			//customer related
			case 12: //declined
				$retVal['errorAbbrev'] = "transactionDecline";
				$retVal['errorDesc'] = "Payment Declined. Check the credit card number, expiration date, and transaction information to make sure they were entered correctly. If this does not resolve the problem, have the customer call their card issuing bank to resolve.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			case 13: //referral
				$retVal['errorAbbrev'] = "transactionDecline";
				$retVal['errorDesc'] = "Payment Declined. Referral. Transaction cannot be approved electronically but can be approved with a verbal authorization. Contact your merchant bank to obtain an authorization and submit a manual Voice Authorization transaction.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			case 50: // insufficient funds
				$retVal['errorAbbrev'] = "transactionDecline";
				$retVal['errorDesc'] = "Payment Declined. Insufficient funds available in account.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			case 24: // invalid expiration date
				$retVal['errorAbbrev'] = "transactionDecline";
				$retVal['errorDesc'] = "Payment Declined. Invalid expiration date. Check and re-submit.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			case 114: // csc mismatch
				$retVal['errorAbbrev'] = "transactionDecline";
				$retVal['errorDesc'] = "Payment Declined. Card Security Code (CSC) Mismatch. An authorization may still exist on the cardholder's account.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			case 19: //original transaction id not found
				$retVal['errorAbbrev'] = "transactionDecline";
				$retVal['errorDesc'] = "Payment Declined. The original payment this transaction references cannot be found. This could be because the original payment was placed using a different merchant if your store has recently switched accounts.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			case 23: // merchant account not configured for reference transactions OR problem with CC number
				$retVal['errorAbbrev'] = "transactionDecline";
				$retVal['errorDesc'] = "Payment Declined. Invalid account number. Check your card number and resubmit.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			case 112: //failed AVS
				$retVal['errorAbbrev'] = "Failed Address Verification System Check";
				$retVal['errorDesc'] = "The address and/or Zip code does not match account settings. Please review and correct and resubmit the payment.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction system error: Failed AVS Check.' . " Code: " . $result['RESULT'], false, false, true);
				break;
			case 170:
				$retVal['errorAbbrev'] = "CardingError";
				$retVal['errorDesc'] = "Payment Declined. We are experiencing a technical issue. Please try again later. Dream Dinners staff have been notified.";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . " Code: " . $result['RESULT'], false, false, false);
				break;
			default:
				$retVal['errorAbbrev'] = "configurationError";
				$retVal['errorDesc'] = "Unexpected Error. Code: {$result['RESULT']} Message: {$result['RESPMSG']}";
				CLog::RecordNew(CLog::ERROR, 'PayPal transaction error: ' . $result['RESPMSG'] . "Code: " . $result['RESULT'], false, false, true);
				break;
		}

		return $retVal;
	}

	/**
	 * Returns the VeriSign Reference ID
	 *
	 * PNREF Value
	 * The PNREF is a unique transaction identification number issued by VeriSign that
	 * identifies the transaction for billing, reporting, and transaction data purposes. The
	 * PNREF value appears in the Transaction ID column in VeriSign Manager reports.
	 * The PNREF value is used as the ORIGID value (original transaction ID) in
	 * delayed capture transactions (TRXTYPE=D), credits (TRXTYPE=C),
	 * inquiries (TRXTYPE=I), and voids (TRXTYPE=V).
	 * The PNREF value is used as the ORIGID value (original transaction ID) value
	 * in reference transactions for authorization (TRXTYPE=A) and Sale
	 * (TRXTYPE=S).
	 * Note The PNREF is also referred to as the Transaction ID in Payflow Link
	 * documentation.
	 */
	function getPNRef()
	{
		return $this->_result['PNREF'];
	}

	static function scrubUserData($inArr)
	{

		$retVal = $inArr;

		if (isset($retVal))
		{
			unset($retVal['ADDRESS']);
			unset($retVal['BILLTOSTREET']);
			unset($retVal['BILLTOZIP']);
			unset($retVal['EXPDATE']);
			unset($retVal['ZIP']);

			$DD_data = self::decodeCommentPayload($retVal['USER1']);

			unset($DD_data['billing_data']['bzip']);
			unset($DD_data['billing_data']['badd']);

			$retVal['USER1'] = self::encodeCommentPayload($DD_data);
		}

		return $retVal;
	}

	static function encodeCommentPayload(&$inArr)
	{
		foreach ($inArr as $k => &$v)
		{
			if (is_array($v))
			{
				$strVer = "";
				$arrCount = count($v);
				$count = 0;
				foreach ($v as $k2 => $v2)
				{
					$strVer .= $k2 . "~" . $v2;

					if ($arrCount != ++$count)
					{
						$strVer .= "|";
					}
				}

				$v = $strVer;
			}
		}

		$strVer = "";
		$arrCount = count($inArr);
		$count = 0;

		foreach ($inArr as $k3 => $v3)
		{
			$strVer .= $k3 . ":" . $v3;

			if ($arrCount != ++$count)
			{
				$strVer .= "+";
			}
		}

		return $strVer;
	}

	static function decodeCommentPayload($payload)
	{
		$retVal = array();

		$commentsArr = explode("+", $payload);
		foreach ($commentsArr as $datum)
		{
			$tempArr = explode(":", $datum);

			if (strpos($tempArr[1], "|") !== false)
			{
				$subArray = array();
				$commentsArr2 = explode("|", $tempArr[1]);
				foreach ($commentsArr2 as $pair)
				{
					$tempArr2 = explode("~", $pair);
					$subArray[$tempArr2[0]] = $tempArr2[1];
				}

				$retVal[$tempArr[0]] = $subArray;
			}
			else
			{
				$retVal[$tempArr[0]] = $tempArr[1];
			}
		}

		return $retVal;
	}

	static function removeOffendingCharacters(&$transaction)
	{

		$offensiveCharacters = array(
			"`",
			"'",
			"\""
		);

		if (isset($transaction['COMMENT1']))
		{
			$transaction['COMMENT1'] = str_replace($offensiveCharacters, "", $transaction['COMMENT1']);
		}

		if (isset($transaction['NAME']))
		{
			$transaction['NAME'] = str_replace($offensiveCharacters, "", $transaction['NAME']);
		}

		if (isset($transaction['STREET']))
		{
			$transaction['STREET'] = str_replace($offensiveCharacters, "", $transaction['STREET']);
		}

		if (isset($transaction['ZIP']))
		{
			$transaction['ZIP'] = str_replace($offensiveCharacters, "", $transaction['ZIP']);
		}
	}

	static function getSecureToken($store_id, $name, $street, $zip, $amount = false)
	{

		$tokenId = COrders::generateConfirmationNum();

		$transaction = array();

		$name = str_replace("&", "and", $name);
		$street = str_replace("&", "and", $street);

		$user = false;

		if (defined('DD_SERVER_NAME') && DD_SERVER_NAME == 'PROD6VM')
		{
			$user = 'dreamweb';
		}

		if (!$store_id)
		{

			$corpAcct = DAO_CFactory::create("corporate_office_data");
			$corpAcct->id = 1;
			if (!$corpAcct->find(true))
			{
				return 'noMerchantAccountFound';
			}

			if ($user)
			{
				$transaction['USER'] = $user;
			}
			else
			{
				$transaction['USER'] = trim($corpAcct->ca_name);
			}

			$transaction['PWD'] = trim(CCrypto::decode($corpAcct->ca_password));
			$transaction['PARTNER'] = $corpAcct->partner_id;
			$transaction['VENDOR'] = trim($corpAcct->ca_name);
		}
		else
		{
			if (defined('USE_CORPORATE_TEST_ACCOUNT') && USE_CORPORATE_TEST_ACCOUNT !== false)
			{
				$store_id = USE_CORPORATE_TEST_ACCOUNT;
			}

			//find the franchise merchant account number
			$MerchantAccount = DAO_CFactory::create("merchant_accounts");
			$MerchantAccount->store_id = $store_id;

			if (!$MerchantAccount->find(true))
			{
				return 'noMerchantAccountFound';
			}

			if ($user)
			{
				$transaction['USER'] = $user;
			}
			else
			{
				$transaction['USER'] = trim($MerchantAccount->ma_username);
			}

			$transaction['PWD'] = trim(CCrypto::decode($MerchantAccount->ma_password));
			$transaction['PARTNER'] = $MerchantAccount->partner_id;
			$transaction['VENDOR'] = trim($MerchantAccount->ma_username);
		}

		self::removeOffendingCharacters($transaction);

		$payflow = new payflow($transaction['VENDOR'], $transaction['USER'], $transaction['PARTNER'], $transaction['PWD']);

		if ($payflow->get_errors())
		{
			throw new Exception("PayPal Config Error: " . $payflow->get_errors());
		}

		$result = $payflow->getSecureToken($tokenId, $store_id, $name, $street, $zip, $amount);

		if ($result === false)
		{
			// this should be rare in production but if parameters are incorrect or something really strange is going the paypal object
			// may return false. It will have set an internal string representing the error.

			unset($transaction['PWD']);
			$theError = $payflow->get_errors();
			CLog::RecordNew(CLog::ERROR, 'Unexpected Error in internal PayPal processor: ' . $theError, '', implode(',', $transaction), true);

			return 'configurationError';
		}

		if ($result['RESULT'] === '0')
		{
			return array(
				'tokenID' => $result['SECURETOKENID'],
				'token' => $result['SECURETOKEN']
			);
		}
		else
		{
			unset($transaction['PWD']);
			CLog::RecordNew(CLog::ERROR, 'Unexpected Error in PayPal processor: ' . $result['RESULT'], '', implode(',', $transaction), false);
		}

		return 'failure';
	}

	/**
	 * @param trxtype 'A' = authorization only, 'S' = sale, 'C' = credit, 'D' = delayed payment, 'V' = void
	 * $param origid : only use with Credits, Authorizations, or a delayed payment
	 *
	 * @return 'success', 'communicationError', 'transactionDecline', 'noConnection', 'configurationError', 'noMerchantAccountFound', overCreditingOriginalTransactionError
	 */
	function processPayment($customerObj, $order_id, $franchise_id, $store_id, $name, $amount, $ccNumber = false, $ccMonth = false, $ccYear = false, $ccCVV2 = false, $postalcode = false, $streetAddress = false, $trxtype = 'S', $origid = false, $additionalComment = false)
	{

		if ($amount == 0)
		{
			return 'invalidAmount';
		}

		$amount = sprintf("%01.2f", $amount);

		//find the franchise merchant account number
		$MerchantAccount = DAO_CFactory::create("merchant_accounts");

		$MerchantAccount->store_id = $store_id;
		if (defined('USE_CORPORATE_TEST_ACCOUNT') && USE_CORPORATE_TEST_ACCOUNT !== false)
		{
			$MerchantAccount->store_id = USE_CORPORATE_TEST_ACCOUNT;
		}

		if (!$MerchantAccount->find(true))
		{
			return 'noMerchantAccountFound';
		}

		$this->_merchant_account_id = $MerchantAccount->id;

		$transaction = array();
		$user = false;

		if (!empty($MerchantAccount->ma_login_account))
		{
			$user = $MerchantAccount->ma_login_account;
		}

		if (defined('DD_SERVER_NAME') && (DD_SERVER_NAME == 'PROD6VM' || DD_SERVER_NAME == 'MR_TEST'))
		{
			$user = 'dreamweb';
		}

		if ($user)
		{
			$transaction['USER'] = $user;
		}
		else
		{
			$transaction['USER'] = trim($MerchantAccount->ma_username);
		}

		$transaction['PWD'] = trim(CCrypto::decode($MerchantAccount->ma_password));
		$transaction['PARTNER'] = $MerchantAccount->partner_id;
		$transaction['VENDOR'] = trim($MerchantAccount->ma_username);
		$transaction['TENDER'] = 'C';
		$transaction['TRXTYPE'] = $trxtype;

		if ($origid) //changed to allow for followup sales
		{
			$transaction['ORIGID'] = $origid;
		}

		if ($trxtype == 'A' || $trxtype == 'S' || $trxtype == 'C')
		{
			$transaction['AMT'] = $amount;

			if (!$origid)
			{
				$transaction['ACCT'] = $ccNumber;
				$transaction['EXPDATE'] = $ccMonth . $ccYear;
				//if ( $ccCVV2 !== false )		//TAG: DAVIDB
				if (!empty($ccCVV2))
				{
					$transaction['CVV2'] = $ccCVV2;
				}
				$transaction['NAME'] = $name;
				if ($streetAddress !== false)
				{
					$transaction['STREET'] = $streetAddress;
				}
				if ($postalcode !== false)
				{
					$transaction['ZIP'] = $postalcode;
				}
			}
		}

		if ($this->_is_delivered_payment)
		{
			$transaction['COMMENT1'] = $store_id . '|user id:' . $customerObj->id . '+StoreID:' . $store_id . '+OrderID:' . ($order_id ? $order_id : 'null') . '+Email:' . $customerObj->primary_email;
		}
		else
		{
			$transaction['COMMENT1'] = 'user id:' . $customerObj->id . '+StoreID:' . $store_id . '+OrderID:' . ($order_id ? $order_id : 'null') . '+Email:' . $customerObj->primary_email;
		}

		if ($additionalComment)
		{
			$transaction['COMMENT1'] .= "+" . $additionalComment;
		}

		self::removeOffendingCharacters($transaction);

		$payflow = new payflow($transaction['VENDOR'], $transaction['USER'], $transaction['PARTNER'], $transaction['PWD']);

		if ($payflow->get_errors())
		{
			throw new Exception("PayPal Config Error: " . $payflow->get_errors());
		}

		if ($trxtype == 'S')
		{
			/**
			 * Sale Transactions
			 */

			if ($origid)
			{

				$clientIP = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : "localhost");

				// extra params
				$data_array = array(
					'comment1' => $transaction['COMMENT1'],
					'clientip' => $clientIP
				);

				$result = $payflow->reference_sale_transaction($transaction['ORIGID'], $transaction['AMT'], 'USD', $data_array);
			}
			else
			{

				$clientIP = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : "localhost");

				// extra params
				$data_array = array(
					'comment1' => $transaction['COMMENT1'],
					'name' => $transaction['NAME'],
					'street' => $transaction['STREET'],
					'zip' => $transaction['ZIP'],
					'country' => 'US',
					// iso codes
					'cvv' => $transaction['CVV2'],
					// for cvv validation response
					'clientip' => $clientIP
				);

				$result = $payflow->sale_transaction($transaction['ACCT'], $transaction['EXPDATE'], $transaction['AMT'], 'USD', $data_array);
			}
		}
		else if ($trxtype == 'C')
		{
			/**
			 * Credit Transactions
			 */

			// extra params
			$data_array = array(
				'comment1' => $transaction['COMMENT1'],
				'clientip' => $_SERVER['SERVER_ADDR']
			);

			$result = $payflow->credit_transaction($transaction['ORIGID'], $transaction['AMT'], $data_array);
		}
		else if ($trxtype == 'A')
		{
			/**
			 * Authorization Transactions
			 */
			throw new Exception('Authorization Transactions are not currently supported by higher level code.');

			// extra params
			$data_array = array(
				'comment1' => $transaction['COMMENT1'],
				'name' => $transaction['NAME'],
				'clientip' => $_SERVER['SERVER_ADDR']
			);

			$result = $payflow->authorization($transaction['ACCT'], $transaction['EXPDATE'], $transaction['AMT'], 'USD', $data_array);
		}
		else if ($trxtype == 'D')
		{
			throw new Exception('Delayed Capture Transactions are not currently supported by higher level code.');
			/**
			 * Delayed Capture Transactions
			 */
			// extra params
			$data_array = array(
				'comment1' => $transaction['COMMENT1'],
				'clientip' => $_SERVER['SERVER_ADDR']
			);

			$result = $payflow->delayed_capture($transaction['ORIGID'], $data_array);
		}
		else if ($trxtype == 'V')
		{

			throw new Exception('Voidng a Transaction is not yet supported by higher level code.');

			/**
			 * Void Transactions
			 */ // extra params
			// extra params
			$data_array = array(
				'comment1' => $transaction['COMMENT1'],
				'clientip' => $_SERVER['SERVER_ADDR']
			);

			$result = $payflow->void_transaction($transaction['ORIGID'], $data_array);
		}
		else
		{
			throw new Exception('Unknown transaction type: ' . $trxtype);
		}

		if ($result === false)
		{
			// this should be rare in production but if parameters are incorrect or something really strange is going the paypal object
			// may return false. It will have set an internal string representing the error.
			unset($transaction['ACCT']);
			unset($transaction['EXPDATE']);
			unset($transaction['CVV2']);
			unset($transaction['STREET']);
			unset($transaction['ZIP']);
			unset($transaction['PWD']);

			$theError = $payflow->get_errors();
			CLog::RecordNew(CLog::ERROR, 'Unexpected Error in internal PayPal processor: ' . $theError, '', implode(',', $transaction), true);

			return 'configurationError';
		}

		$this->_result = $result;

		if ($ccNumber)
		{
			$transaction['ACCT'] = str_repeat('X', (strlen($transaction['ACCT']) - 4)) . substr($transaction['ACCT'], -4);
		}
		$transaction['PWD'] = 'XXXXXX';
		$this->_request = $transaction;

		if (($result['RESULT'] != 0))
		{


			unset($transaction['EXPDATE']);
			unset($transaction['CVV2']);
			unset($transaction['STREET']);
			unset($transaction['ZIP']);

			if ($result['RESULT'] < 0)
			{
				//log bad transactions
				CLog::RecordNew(CLog::ERROR, 'Verisign communication error: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

				return 'communicationError';
			}
			else
			{
				//log bad transactions
				switch ($result['RESULT'])
				{

					//merchant/system related
					case 1: //user authentication failed
					case 26:

						if (!defined(PFP_TEST_MODE) || !PFP_TEST_MODE)
						{
							@CLog::NotifyFadmin($store_id, '!!Dreamdinners Server Alert!!', 'The Dreamdinners website ' . 'tried to process a credit card for your store, but the merchant account login has failed. ' . 'This may happen if you have recently changed your merchant account information, please notify ' . 'DreamDinners support staff.');
						}
						else
						{
							CLog::RecordNew(CLog::DEBUG, 'merchant account user authentication error or IP restriction', "", "", true);
						}

					case 2: //invalid tender
					case 3: //invalid tx type
					case 4: //invalid merchant info
					case 7: //field format error
					case 8: //not a transaction server
					case 9: //too many parameters
					case 10: //too many items
					case 105: //credit error
					case 106: //hosts not available
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction system error: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

						return 'configurationError';

					//customer related
					case 12: //declined
						$this->_users_explanation = "Declined. Check the credit card number, expiration date, and transaction information to make sure they were entered correctly.";

						return 'transactionDecline';
					case 13: //referral
						$this->_users_explanation = "Referral. Transaction cannot be approved electronically but can be approved with a verbal authorization. Contact your merchant bank to obtain an authorization and submit a manual Voice Authorization transaction.";

						return 'transactionDecline';
					case 50: // insufficient funds
						$this->_users_explanation = "Insufficient funds available in account.";

						return 'transactionDecline';
					case 24:
						$this->_users_explanation = "Invalid expiration date. Check and re-submit.";

						return 'transactionDecline';
					case 114:
						$this->_users_explanation = "Card Security Code (CSC) Mismatch. An authorization may still exist on the cardholder's account.";

						return 'transactionDecline';
					case 19: //original transaction id not found
						$this->_users_explanation = "Error 19: Transaction not found. This is usually due to the original transcation residing in an different merchant account.";
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));

						return 'configurationError';
					case 23: // merchant account not configured for reference transactions OR problem with CC number
						// If this was a reference transaction then log and notify BackOffice
						if ($origid)
						{

							if (!defined(PFP_TEST_MODE) || !PFP_TEST_MODE)
							{

								@CLog::NotifyFadmin($store_id, '!!Dreamdinners Server Alert!!', 'The Dreamdinners website ' . 'tried to process a reference transaction for your store, but the transaction failed with error number 23. ' . 'This ususally means the merchant account is not configured to accept reference transactions. Please correct this or notify ' . 'DreamDinners support staff.');
							}

							CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

							return 'configurationError';
						}

						$this->_users_explanation = "Invalid account number. Check credit card number and re-submit.";

						return 'transactionDecline';

					case 117:
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));
						$this->_users_explanation = "Error 117: The amount to be credited to the customer is greater than the amount of the original sale minus credits already applied.";

						return 'overCreditingOriginalTransactionError';

					case 170:
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));
						$this->_users_explanation = "Error 170: We are experiencing difficulty with the payment gateway. Please try again later.";

						return 'transactionDecline';

					default:
						CLog::RecordNew(CLog::ERROR, 'Unexpected Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

						return 'configurationError';
				}
			}
		}

		return 'success';
	}

	function processGiftCardOrder($email, $name, $amount, $ccNumber = false, $ccMonth = false, $ccYear = false, $ccCVV2 = false, $postalcode = false, $streetAddress = false, $trxtype = 'S', $city = false, $state_id = false)
	{
		if ($amount == 0)
		{
			return 'invalidAmount';
		}

		$amount = sprintf("%01.2f", $amount);
		CLog::Record("GC_DEBUG: Payment attempt of $amount");
		//find the franchise merchant account number
		$corpAcct = DAO_CFactory::create("corporate_office_data");
		$corpAcct->id = 1;
		if (!$corpAcct->find(true))
		{
			return 'noMerchantAccountFound';
		}

		$transaction = array();

		$user = false;

		if (defined('DD_SERVER_NAME') && (DD_SERVER_NAME == 'PROD6VM' || DD_SERVER_NAME == 'MR_TEST'))
		{
			$user = 'dreamweb';
		}

		if ($user)
		{
			$transaction['USER'] = $user;
		}
		else
		{
			$transaction['USER'] = trim($corpAcct->ca_name);
		}

		$transaction['PWD'] = trim(CCrypto::decode($corpAcct->ca_password));
		$transaction['PARTNER'] = $corpAcct->partner_id;
		$transaction['VENDOR'] = trim($corpAcct->ca_name);
		$transaction['TENDER'] = 'C';
		$transaction['TRXTYPE'] = $trxtype;

		if ($trxtype == 'A' || $trxtype == 'S' || $trxtype == 'C')
		{
			$transaction['AMT'] = $amount;

			$transaction['ACCT'] = $ccNumber;
			$transaction['EXPDATE'] = $ccMonth . $ccYear;
			if (!empty($ccCVV2))
			{
				$transaction['CVV2'] = $ccCVV2;
			}
			$transaction['NAME'] = $name;
			if ($streetAddress !== false)
			{
				$transaction['STREET'] = $streetAddress;
			}
			if ($postalcode !== false)
			{
				$transaction['ZIP'] = $postalcode;
			}
			if ($postalcode !== false)
			{
				$transaction['CITY'] = $city;
			}
			if ($postalcode !== false)
			{
				$transaction['STATE'] = $state_id;
			}
		}

		$transaction['COMMENT1'] = 'Email:' . $email;

		self::removeOffendingCharacters($transaction);

		$payflow = new payflow($transaction['VENDOR'], $transaction['USER'], $transaction['PARTNER'], $transaction['PWD']);

		if ($payflow->get_errors())
		{
			throw new Exception("PayPal Config Error: " . $payflow->get_errors());
		}

		if ($trxtype == 'S')
		{

			$data_array = array(
				'comment1' => $transaction['COMMENT1'],
				'name' => $transaction['NAME'],
				'street' => $transaction['STREET'],
				'city' => $transaction['CITY'],
				'state' => $transaction['STATE'],
				'zip' => $transaction['ZIP'],
				'country' => 'US',
				// iso codes
				'cvv' => $transaction['CVV2'],
				// for cvv validation response
				'clientip' => $_SERVER['SERVER_ADDR']
			);

			$result = $payflow->sale_transaction($transaction['ACCT'], $transaction['EXPDATE'], $transaction['AMT'], 'USD', $data_array);
		}

		else
		{
			throw new Exception('Incorrect transaction type: ' . $trxtype);
		}

		if ($result === false)
		{
			// this should be rare in production but if parameters are incorrect or something really strange is going the paypal object
			// may return false. It will have set an internal string representing the error.
			$theError = $payflow->get_errors();
			unset($transaction['ACCT']);
			unset($transaction['EXPDATE']);
			unset($transaction['CVV2']);
			unset($transaction['STREET']);
			unset($transaction['ZIP']);
			unset($transaction['PWD']);

			CLog::RecordNew(CLog::ERROR, 'Unexpected Error in internal PayPal processor: ' . $theError, '', implode(',', $transaction), true);

			return 'configurationError';
		}

		$this->_result = $result;

		if ($ccNumber)
		{
			$transaction['ACCT'] = str_repeat('X', (strlen($transaction['ACCT']) - 4)) . substr($transaction['ACCT'], -4);
		}
		$transaction['PWD'] = 'XXXXXX';
		$this->_request = $transaction;

		if (($result['RESULT'] != 0))
		{

			unset($transaction['EXPDATE']);
			unset($transaction['CVV2']);
			unset($transaction['STREET']);
			unset($transaction['ZIP']);

			if ($result['RESULT'] < 0)
			{
				//log bad transactions
				CLog::RecordNew(CLog::ERROR, 'Verisign communication error: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

				return 'communicationError';
			}
			else
			{
				//log bad transactions
				switch ($result['RESULT'])
				{

					//merchant/system related
					case 1: //user authentication failed
					case 26:
					case 2: //invalid tender
					case 3: //invalid tx type
					case 4: //invalid merchant info
					case 7: //field format error
					case 8: //not a transaction server
					case 9: //too many parameters
					case 10: //too many items
					case 105: //credit error
					case 106: //hosts not available
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction system error: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

						return 'configurationError';

					//customer related
					case 12: //declined
						$this->_users_explanation = "Declined. Check the credit card number, expiration date, and transaction information to make sure they were entered correctly. If this does not resolve the problem, have the customer call their card issuing bank to resolve.";

						return 'transactionDecline';
					case 13: //referral
						$this->_users_explanation = "Referral. Transaction cannot be approved electronically but can be approved with a verbal authorization. Contact your merchant bank to obtain an authorization and submit a manual Voice Authorization transaction.";

						return 'transactionDecline';
					case 50: // insufficient funds
						$this->_users_explanation = "Insufficient funds available in account.";

						return 'transactionDecline';
					case 24:
						$this->_users_explanation = "Invalid expiration date. Check and re-submit.";

						return 'transactionDecline';
					case 114:
						$this->_users_explanation = "Card Security Code (CSC) Mismatch. An authorization may still exist on the cardholder's account.";

						return 'transactionDecline';
					case 19: //original transaction id not found
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));

						return 'configurationError';
					case 23: // merchant account not configured for reference transactions OR problem with CC number
						// If this was a reference transaction then log and notify BackOffice

						$this->_users_explanation = "Invalid account number. Check credit card number and re-submit.";

						return 'transactionDecline';

					case 117:
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));
						$this->_users_explanation = "Error 117: The amount to be credited to the customer is greater than the amount of the original sale minus credits already applied.";

						return 'overCreditingOriginalTransactionError';
						//TODO:evanl - new
					case 126: //REVIEW FOR FRAUD
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));
						$this->_users_explanation = "Error 126: Under review by Fraud Services.";

						return 'fraudReviewTransactionError';
					default:
						CLog::RecordNew(CLog::ERROR, 'Unexpected Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

						return 'configurationError';
				}
			}
		}

		$responseArray = array();
		$responseArray[1] = $result;
		$responseArray[0] = 'success';

		return $responseArray;
	}

	function voidGiftCardPayment($email, $original_transaction_id)
	{


		//find the franchise merchant account number
		$corpAcct = DAO_CFactory::create("corporate_office_data");
		$corpAcct->id = 1;
		if (!$corpAcct->find(true))
		{
			return 'noMerchantAccountFound';
		}

		$transaction = array();

		$user = false;

		if (defined('DD_SERVER_NAME') && (DD_SERVER_NAME == 'PROD6VM' || DD_SERVER_NAME == 'MR_TEST'))
		{
			$user = 'dreamweb';
		}

		if ($user)
		{
			$transaction['USER'] = $user;
		}
		else
		{
			$transaction['USER'] = trim($corpAcct->ca_name);
		}

		$transaction['PWD'] = trim(CCrypto::decode($corpAcct->ca_password));
		$transaction['PARTNER'] = $corpAcct->partner_id;
		$transaction['VENDOR'] = trim($corpAcct->ca_name);
		$transaction['TENDER'] = 'C';
		$transaction['COMMENT1'] = 'Email:' . $email;
		$transaction['ORIGID'] = $original_transaction_id;

		self::removeOffendingCharacters($transaction);

		$payflow = new payflow($transaction['VENDOR'], $transaction['USER'], $transaction['PARTNER'], $transaction['PWD']);

		if ($payflow->get_errors())
		{
			throw new Exception("PayPal Config Error: " . $payflow->get_errors());
		}

		$data_array = array(
			'comment1' => $transaction['COMMENT1'],
			'clientip' => $_SERVER['SERVER_ADDR']
		);

		$result = $payflow->void_transaction($transaction['ORIGID'], $data_array);

		if ($result === false)
		{
			// this should be rare in production but if parameters are incorrect or something really strange is going the paypal object
			// may return false. It will have set an internal string representing the error.
			$theError = $payflow->get_errors();

			unset($transaction['PWD']);

			CLog::RecordNew(CLog::ERROR, 'Unexpected Error in internal PayPal processor: ' . $theError, '', implode(',', $transaction), true);

			return 'configurationError';
		}

		$this->_result = $result;

		$transaction['PWD'] = 'XXXXXX';
		$this->_request = $transaction;

		if (($result['RESULT'] != 0))
		{

			unset($transaction['EXPDATE']);
			unset($transaction['CVV2']);
			unset($transaction['STREET']);
			unset($transaction['ZIP']);

			if ($result['RESULT'] < 0)
			{
				//log bad transactions
				CLog::RecordNew(CLog::ERROR, 'Verisign communication error: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

				return 'communicationError';
			}
			else
			{
				//log bad transactions
				switch ($result['RESULT'])
				{

					//merchant/system related
					case 1: //user authentication failed
					case 26:
					case 2: //invalid tender
					case 3: //invalid tx type
					case 4: //invalid merchant info
					case 7: //field format error
					case 8: //not a transaction server
					case 9: //too many parameters
					case 10: //too many items
					case 105: //credit error
					case 106: //hosts not available
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction system error: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

						return 'configurationError';

					//customer related
					case 12: //declined
						$this->_users_explanation = "Declined. Check the credit card number, expiration date, and transaction information to make sure they were entered correctly. If this does not resolve the problem, have the customer call their card issuing bank to resolve.";

						return 'transactionDecline';
					case 13: //referral
						$this->_users_explanation = "Referral. Transaction cannot be approved electronically but can be approved with a verbal authorization. Contact your merchant bank to obtain an authorization and submit a manual Voice Authorization transaction.";

						return 'transactionDecline';
					case 50: // insufficient funds
						$this->_users_explanation = "Insufficient funds available in account.";

						return 'transactionDecline';
					case 24:
						$this->_users_explanation = "Invalid expiration date. Check and re-submit.";

						return 'transactionDecline';
					case 114:
						$this->_users_explanation = "Card Security Code (CSC) Mismatch. An authorization may still exist on the cardholder's account.";

						return 'transactionDecline';
					case 19: //original transaction id not found
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));

						return 'configurationError';
					case 23: // merchant account not configured for reference transactions OR problem with CC number
						// If this was a reference transaction then log and notify BackOffice

						$this->_users_explanation = "Invalid account number. Check credit card number and re-submit.";

						return 'transactionDecline';

					case 117:
						CLog::RecordNew(CLog::ERROR, 'Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction));
						$this->_users_explanation = "Error 117: The amount to be credited to the customer is greater than the amount of the original sale minus credits already applied.";

						return 'overCreditingOriginalTransactionError';
					default:
						CLog::RecordNew(CLog::ERROR, 'Unexpected Verisign transaction response: ' . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

						return 'configurationError';
				}
			}
		}

		return 'success';
	}



	// used to confirmn proper operation of the PayFLow Gateway
	// returns noMerchantAccountFound, configurationError, communicationError, transactionError, configurationErrorVoid, communicationErrorVoid, transactionErrorVoid  and success
	// Can also throw exceptions if misconfigured but this is very unlikely

	function payFlowTest()
	{
		//find the franchise merchant account number
		$corpAcct = DAO_CFactory::create("corporate_office_data");
		$corpAcct->id = 1;
		if (!$corpAcct->find(true))
		{
			return 'noMerchantAccountFound';
		}

		$trxtype = 'S';

		$transaction = array();

		$transaction['USER'] = trim($corpAcct->ca_name);
		$transaction['PWD'] = trim(CCrypto::decode($corpAcct->ca_password));
		$transaction['PARTNER'] = $corpAcct->partner_id;
		$transaction['VENDOR'] = $transaction['USER'];
		$transaction['TENDER'] = 'C';
		$transaction['TRXTYPE'] = $trxtype;
		$transaction['AMT'] = '1.00';
		$transaction['ACCT'] = '4856200202017987';
		$transaction['EXPDATE'] = '0718';
		$transaction['CVV2'] = '707';
		$transaction['NAME'] = "Cristen Ellis";
		$transaction['STREET'] = "PO Box 889";
		$transaction['ZIP'] = '98291';
		$transaction['COMMENT1'] = 'TEST TRANSACTION: $1.00 to be followed by a VOID transaction';

		self::removeOffendingCharacters($transaction);

		$payflow = new payflow($transaction['VENDOR'], $transaction['USER'], $transaction['PARTNER'], $transaction['PWD']);

		if ($payflow->get_errors())
		{
			throw new Exception("PayPal Config Error: " . $payflow->get_errors());
		}

		$data_array = array(
			'comment1' => $transaction['COMMENT1'],
			'name' => $transaction['NAME'],
			'street' => $transaction['STREET'],
			'zip' => $transaction['ZIP'],
			'country' => 'US',
			// iso codes
			'cvv' => $transaction['CVV2'],
			// for cvv validation response
			'clientip' => '162.218.138.138'
		);

		$result = $payflow->sale_transaction($transaction['ACCT'], $transaction['EXPDATE'], $transaction['AMT'], 'USD', $data_array);

		if ($result === false)
		{
			// this should be rare in production but if parameters are incorrect or something really strange is going the paypal object
			// may return false. It will have set an internal string representing the error.
			$theError = $payflow->get_errors();
			unset($transaction['ACCT']);
			unset($transaction['EXPDATE']);
			unset($transaction['CVV2']);
			unset($transaction['STREET']);
			unset($transaction['ZIP']);
			unset($transaction['PWD']);

			CLog::RecordNew(CLog::ERROR, 'Unexpected Error in internal PayPal processor attempting test transaction: ' . $theError, '', implode(',', $transaction), true);

			return 'configurationError';
		}

		$this->_result = $result;

		$transaction['ACCT'] = str_repeat('X', (strlen($transaction['ACCT']) - 4)) . substr($transaction['ACCT'], -4);
		$transaction['PWD'] = 'XXXXXX';
		$this->_request = $transaction;

		if (($result['RESULT'] != 0))
		{
			if ($result['RESULT'] < 0)
			{
				unset($transaction['ACCT']);
				unset($transaction['EXPDATE']);
				unset($transaction['CVV2']);
				unset($transaction['STREET']);
				unset($transaction['ZIP']);
				unset($transaction['PWD']);

				CLog::RecordNew(CLog::ERROR, 'Verisign communication during eCommerce Test error: ' . $result['RESULT'] . "-" . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

				return 'communicationError';
				// a communication error - time to panic - raise an error
			}
			else
			{
				unset($transaction['ACCT']);
				unset($transaction['EXPDATE']);
				unset($transaction['CVV2']);
				unset($transaction['STREET']);
				unset($transaction['ZIP']);
				unset($transaction['PWD']);

				CLog::RecordNew(CLog::ERROR, 'Verisign transaction error during eCommerce Test error: ' . $result['RESULT'] . "-" . $result['RESPMSG'], $result['RESULT'], implode(',', $transaction), true);

				return 'transactionError';
				// a transaction error -probably just an issue with the test card account. Notify dev but do not panic.
			}
		}
		else
		{

			sleep(5);
			// success so void transaction
			$void_transaction = array();

			$void_transaction['USER'] = trim($corpAcct->ca_name);
			$void_transaction['PWD'] = trim(CCrypto::decode($corpAcct->ca_password));
			$void_transaction['PARTNER'] = $corpAcct->partner_id;
			$void_transaction['VENDOR'] = $void_transaction['USER'];
			$void_transaction['TENDER'] = 'C';

			$payflowVoid = new payflow($void_transaction['VENDOR'], $void_transaction['USER'], $void_transaction['PARTNER'], $void_transaction['PWD']);

			if ($payflowVoid->get_errors())
			{
				throw new Exception("PayPal Config Error: " . $payflowVoid->get_errors());
			}

			$data_array = array(
				'comment1' => "Voiding previous test sale: " . $result['PNREF'],
				'clientip' => '162.218.138.138'
			);

			$void_result = $payflowVoid->void_transaction($result['PNREF'], $data_array);

			$this->_result = $void_result;

			if ($void_result === false)
			{
				// this should be rare in production but if parameters are incorrect or something really strange is going the paypal object
				// may return false. It will have set an internal string representing the error.
				$theError = $payflow->get_errors();
				unset($transaction['PWD']);
				CLog::RecordNew(CLog::ERROR, 'Unexpected Error in internal PayPal processor when voiding test transaction: ' . $theError, '', implode(',', $void_transaction), true);

				return 'configurationErrorVoid';
			}

			if (($void_result['RESULT'] != 0))
			{
				if ($void_result['RESULT'] < 0)
				{
					unset($transaction['PWD']);
					CLog::RecordNew(CLog::ERROR, 'Verisign communication during eCommerce void Test error: ' . $void_result['RESULT'] . "-" . $void_result['RESPMSG'], $void_result['RESULT'], implode(',', $void_transaction), true);

					return 'communicationErrorVoid';
					// a communication error - time to panic - raise an error
				}
				else
				{
					unset($transaction['PWD']);
					CLog::RecordNew(CLog::ERROR, 'Verisign transaction error during eCommerce void Test error: ' . $void_result['RESULT'] . "-" . $void_result['RESPMSG'], $void_result['RESULT'], implode(',', $void_transaction), true);

					return 'transactionErrorVoid';
					// a transaction error -probably just an issue with the test card account. Notify dev but do not panic.
				}
			}
			else
			{
				return 'success';
			}
		}
	}
}

?>