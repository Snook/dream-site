<?php
require_once 'DAO/Gift_card_transaction.php';
require_once 'DAO/BusinessObject/CSession.php';
require_once 'DAO/BusinessObject/COrders.php';
require_once 'CCrypto.inc';
require_once 'ValidationRules.inc';
require_once('CMail.inc');

if (defined('DEBIT_GIFT_CARD_TESTMODE') && DEBIT_GIFT_CARD_TESTMODE === true)
{
	define('DEBIT_GIFT_CARD_MERCHANT_NUMBER', '111111111111');
	define('DEBIT_GIFT_CARD_TERMINAL_ID', '150');
	define('OBTAIN_ACCOUNT_NUMBER_DEFAULT', 'NewAccountRQ');
}
else
{
	define('DEBIT_GIFT_CARD_MERCHANT_NUMBER', '711389000888');
	define('DEBIT_GIFT_CARD_TERMINAL_ID', '002');
	define('OBTAIN_ACCOUNT_NUMBER_DEFAULT', 'NewAccountRQ');
}

class CGiftCard extends DAO_Gift_card_transaction
{

	const DEBIT_GIFT_CARD_DOMAIN = 'www.smart-transactions.com';
	const DEBIT_GIFT_CARD_URL = "https://www.smart-transactions.com/gateway.php";

	function __construct()
	{
		parent::__construct();
	}

	static function getActiveGCDesignArray($support_physical = true, $support_virtual = true)
	{
		$DesignTypes = DAO_CFactory::create('gift_card_design');
		$DesignTypes->is_active = 1;
		$DesignTypes->orderBy('layout_order');
		$DesignTypes->find();

		$retval = array(
			'info' => array(
				'num_physical' => 0,
				'num_virtual' => 0
			),
			'designs' => array()
		);

		while ($DesignTypes->fetch())
		{
			if (!empty($support_physical) && !empty($DesignTypes->supports_physical))
			{
				$retval['info']['num_physical'] += 1;
			}

			if (!empty($support_virtual) && !empty($DesignTypes->supports_virtual))
			{
				$retval['info']['num_virtual'] += 1;
			}

			$retval['designs'][$DesignTypes->id] = array(
				'title' => $DesignTypes->title,
				'description' => $DesignTypes->description,
				'image_path' => $DesignTypes->image_path,
				'image_path_virtual' => $DesignTypes->image_path_egift,
				'image_path_egift' => $DesignTypes->image_path_egift,
				'supports_physical' => ((empty($support_physical)) ? '0' : $DesignTypes->supports_physical),
				'supports_virtual' => ((empty($support_virtual)) ? '0' : $DesignTypes->supports_virtual)
			);
		}

		return $retval;
	}

	static function checkBalanceDebitGiftCard($gift_card_number)
	{

		if (!is_numeric($gift_card_number))
		{
			CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Non numeric entry in checkBalanceDebitGiftCard");

			return "Invalid Card";
		}

		$array = CGiftCard::getStoreDetails();
		$post_string = 'Auth_Request=' . chr(2) . '<Request>
				<Merchant_Number>' . $array['merchantNumber'] . '</Merchant_Number>
				<Terminal_ID>' . $array['terminalNumber'] . '</Terminal_ID>
				<Action_Code>05</Action_Code>
				<Trans_Type>N</Trans_Type>
				<POS_Entry_Mode>M</POS_Entry_Mode>
				<Card_Number>' . $gift_card_number . '</Card_Number>
			</Request>' . chr(3);

		//CLog::RecordNew(CLog::DEBUG, $post_string, "", "", false);
		//For debugging
		//echo "\r\n<br /><textarea rows=50 cols=90>".htmlentities($post_string)."</textarea>";

		//***Use Curl to send API request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CGiftCard::DEBIT_GIFT_CARD_URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//*** Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);

		$outarr = null;
		$retarr = array();

		//Parse XML reponse
		preg_match_all("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
		//switch on response code for accepted or declined
		switch ($retarr['Response_Code'])
		{
			case '01': //Declined
				//echo "Declined<br />".$retarr['Response_Text']."<br />";
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Declined response code in checkBalanceDebitGiftCard: {$retarr['Response_Code']} | {$retarr['Response_Text']}");

				return "Invalid Card";
			case '00': //Accepted
				// echo "Your Gift Card Balance is: ".$retarr['Amount_Balance']."<br />";
				return $retarr['Amount_Balance'];
			default:
			{
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Unexpected response code in checkBalanceDebitGiftCard: {$retarr['Response_Code']} | {$retarr['Response_Text']}");

				return "Invalid Card";
			}
		}
	}

	static function voidTransaction($transactionID, $Auth_code)
	{
		$post_string = 'Auth_Request=' . chr(2) . '<Request>
				<Action_Code>11</Action_Code>
				<Auth_Reference>' . $Auth_code . '</Auth_Reference>
				<Merchant_Number>' . DEBIT_GIFT_CARD_MERCHANT_NUMBER . '</Merchant_Number>
				<Terminal_ID>' . DEBIT_GIFT_CARD_TERMINAL_ID . '</Terminal_ID>
				<Trans_Type>N</Trans_Type>
				<POS_Entry_Mode>M</POS_Entry_Mode>
				<Transaction_ID>' . $transactionID . '</Transaction_ID>
			</Request>' . chr(3);

		//For debugging
		//echo "\r\n<br /><textarea rows=50 cols=90>".htmlentities($post_string)."</textarea>";

		//***Use Curl to send API request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CGiftCard::DEBIT_GIFT_CARD_URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//*** Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);

		if (!$result)
		{
			CLog::RecordIntense("CURL error - in voidTransaction", 'ryan.snook@dreamdinners.com');

			return false;
		}

		$outarr = null;
		$retarr = array();

		//Parse XML reponse
		preg_match_all("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
		//switch on response code for accepted or declined
		switch ($retarr['Response_Code'])
		{
			case '00':
				return true;
			default:
				CLog::RecordIntense("Error occurred when voiding transaction: " . print_r($retarr, true), 'ryan.snook@dreamdinners.com');

				return false;
		}
	}

	static function isSmartTransactionsAliveAndWell()
	{
		$post_string = 'Auth_Request=' . chr(2) . '<Request>
				<Merchant_Number>' . DEBIT_GIFT_CARD_MERCHANT_NUMBER . '</Merchant_Number>
				<Terminal_ID>' . DEBIT_GIFT_CARD_TERMINAL_ID . '</Terminal_ID>
				<Action_Code>05</Action_Code>
				<Trans_Type>N</Trans_Type>
				<POS_Entry_Mode>M</POS_Entry_Mode>
				<Card_Number>1111111111111111</Card_Number>
			</Request>' . chr(3);

		//For debugging
		//echo "\r\n<br /><textarea rows=50 cols=90>".htmlentities($post_string)."</textarea>";

		//***Use Curl to send API request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CGiftCard::DEBIT_GIFT_CARD_URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//*** Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);

		if (!$result)
		{
			CLog::RecordIntense("CURL error - in isSmartTransactionsAliveAndWell", 'ryan.snook@dreamdinners.com');

			return false;
		}

		$outarr = null;
		$retarr = array();

		//Parse XML reponse
		preg_match_all("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
		//switch on response code for accepted or declined
		switch ($retarr['Response_Code'])
		{
			case '01': //Declined
				return true;
			case '00': //Accepted
				if (defined('DEBIT_GIFT_CARD_TESTMODE') && DEBIT_GIFT_CARD_TESTMODE === true)
				{
					return true;
				}
				CLog::RecordIntense("bogus card number accepted as real - very strange happenings indeed in isSmartTransactionsAliveAndWell: " . print_r($retarr, true), 'ryan.snook@dreamdinners.com');

				return false;
			default:
				CLog::RecordIntense("bogus card number returns unexpected response code in isSmartTransactionsAliveAndWell: " . print_r($retarr, true), 'ryan.snook@dreamdinners.com');

				return false;
		}
	}

	static function completeNewAccountTransaction($GC_Order_id, $purchaser_email, $CC_Num, $transaction_id, $card_type, $accountNumber, $billing_name, $billing_address, $billing_zip, $confirmation_number, $UI = 'UNKNOWN', $order_id = false, $user_id = false)
	{
		$gcOrderObj = DAO_CFactory::create('gift_card_order');
		$gcOrderObj->id = $GC_Order_id;
		if (!$gcOrderObj->find(true))
		{
			// gift_card_orders update failed - should never happen
			CLog::RecordIntense("Cannot find order in completeNewAccountTransaction - Order ID = $GC_Order_id - Acct number: $accountNumber", 'ryan.snook@dreamdinners.com');

			$args = func_get_args();
			CLog::RecordNew(CLog::ERROR, "GC_ERROR: cannot find Order ID = $GC_Order_id Order Data: " . print_r($args, true));

			return null;
		}

		$gcOldObj = clone($gcOrderObj);

		$gcOrderObj->purchase_date = date("Y-m-d H:i:s");
		$gcOrderObj->transaction_ui = $UI;
		$gcOrderObj->email = $purchaser_email;
		$gcOrderObj->payment_card_number = $CC_Num;
		$gcOrderObj->payment_card_type = $card_type;
		$gcOrderObj->cc_ref_number = $transaction_id;
		$gcOrderObj->billing_name = $billing_name;
		$gcOrderObj->billing_address = $billing_address;
		$gcOrderObj->billing_zip = $billing_zip;
		$gcOrderObj->ip_address = $_SERVER['REMOTE_ADDR'];
		$gcOrderObj->order_confirm_id = $confirmation_number;

		$gcOrderObj->paid = 1;
		$gcOrderObj->processed = 1;

		if ($order_id)
		{
			$gcOrderObj->order_id = $order_id;
		}

		if ($user_id)
		{
			$gcOrderObj->user_id = $user_id;
		}

		$result = $gcOrderObj->update($gcOldObj);

		if ($result === false)
		{
			// gift_card_orders update failed - should never happen
			$args = func_get_args();
			CLog::RecordNew(CLog::ERROR, "GC_ERROR: Update failure - ID = $GC_Order_id Order Data: " . print_r($args, true));
			CLog::RecordIntense("Insert failed in completeNewAccountTransaction - Order ID = $GC_Order_id - Acct number: $accountNumber", 'ryan.snook@dreamdinners.com');

			return null;
		}
		else
		{
			$message = CCrypto::encode(base64_encode($accountNumber));
			$gcOrderObj->query("Call store_gcan('$message', {$gcOrderObj->id})");
			//self::sendVirtualGiftCard($gcOrderObj, $accountNumber);
			// sendVirtualGiftCard is now called for all cards by client code after all processing is complete
		}

		return $gcOrderObj;
	}

	static function completePhysicalCardPurchaseTransaction($GC_Order_id, $purchaser_email, $CC_Num, $transaction_id, $card_type, $billing_name, $billing_address, $billing_zip, $confirmation_number, $UI = 'UNKNOWN', $order_id = false, $user_id = false)
	{
		$gcOrderObj = DAO_CFactory::create('gift_card_order');
		$gcOrderObj->id = $GC_Order_id;
		if (!$gcOrderObj->find(true))
		{
			CLog::RecordIntense("Cannot find order in completePhysicalCardPurchaseTransaction - Order ID = $GC_Order_id", 'ryan.snook@dreamdinners.com');

			$args = func_get_args();
			CLog::RecordNew(CLog::ERROR, "GC_ERROR: cannot find Order ID = $GC_Order_id Order Data: " . print_r($args, true));

			return null;
		}

		$gcOldObj = clone($gcOrderObj);

		$gcOrderObj->purchase_date = date("Y-m-d H:i:s");
		$gcOrderObj->transaction_ui = $UI;
		$gcOrderObj->email = $purchaser_email;
		$gcOrderObj->payment_card_number = $CC_Num;
		$gcOrderObj->payment_card_type = $card_type;
		$gcOrderObj->cc_ref_number = $transaction_id;
		$gcOrderObj->billing_name = $billing_name;
		$gcOrderObj->billing_address = $billing_address;
		$gcOrderObj->billing_zip = $billing_zip;
		$gcOrderObj->ip_address = $_SERVER['REMOTE_ADDR'];
		$gcOrderObj->order_confirm_id = $confirmation_number;

		$gcOrderObj->paid = 1;
		$gcOrderObj->processed = 0;

		if ($order_id)
		{
			$gcOrderObj->order_id = $order_id;
		}

		if ($user_id)
		{
			$gcOrderObj->user_id = $user_id;
		}

		$result = $gcOrderObj->update($gcOldObj);

		if ($result === false)
		{
			// gift_card_orders update failed - should never happen
			$args = func_get_args();
			CLog::RecordNew(CLog::ERROR, "GC_ERROR: Update failure - ID = $GC_Order_id Order Data: " . print_r($args, true));
			CLog::RecordIntense("Insert failed in completePhysicalCardPurchaseTransaction - Order ID = $GC_Order_id", 'ryan.snook@dreamdinners.com');

			return null;
		}

		return $gcOrderObj;
	}

	static function obtainAccountNumberAndLoadWithRetry($trans_amount, $POS_type = 'M', $toName = "none", $toEMailAddress = "none")
	{

		$attemptCount = 0;
		$transId = COrders::generateConfirmationNum();

		$toEMailAddress = substr($toEMailAddress, 0, 50);

		$toNameArr = explode(" ", $toName);
		if (count($toNameArr) > 1)
		{
			$firstName = $toNameArr[0];
		}
		else
		{
			$firstName = $toName;
		}

		$firstName = substr($firstName, 0, 30);
		$firstName = str_replace(array(
			"#",
			"'",
			"\"",
			";",
			"&"
		), "", $firstName);

		$bail = false;
		if ($bail)
		{
			return array(
				false,
				$transId,
				false
			);
		}

		while ($attemptCount++ < 6)
		{
			list($result, $authCode) = self::obtainAccountNumberAndLoad($trans_amount, $transId, $POS_type, $firstName, $toEMailAddress);
			if ($result)
			{
				return array(
					$result,
					$transId,
					$authCode
				);
			}

			sleep(5);
		}

		return array(
			false,
			$transId,
			false
		);
	}

	static function obtainAccountNumberAndLoad($trans_amount, $transId, $POS_type = 'M', $firstName = 'none', $toEMailAddress = "none")
	{


		$post_string = 'Auth_Request=' . chr(2) . '<Request>
				<Merchant_Number>' . DEBIT_GIFT_CARD_MERCHANT_NUMBER . '</Merchant_Number>
				<Terminal_ID>' . DEBIT_GIFT_CARD_TERMINAL_ID . '</Terminal_ID>
				<Action_Code>06</Action_Code>
				<Trans_Type>N</Trans_Type>
				<Transaction_Amount>' . $trans_amount . '</Transaction_Amount>
				<POS_Entry_Mode>' . $POS_type . '</POS_Entry_Mode>
				<Card_Number>' . OBTAIN_ACCOUNT_NUMBER_DEFAULT . '</Card_Number>
				<Email_Addr>' . $toEMailAddress . '</Email_Addr>
				<First_Name>' . $firstName . '</First_Name>
				<Last_Name>' . $firstName . '</Last_Name>
				<Transaction_ID>' . $transId . '</Transaction_ID>
			</Request>' . chr(3);

		//For debugging
		//echo "\r\n<br /><textarea rows=50 cols=90>".htmlentities($post_string)."</textarea>";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CGiftCard::DEBIT_GIFT_CARD_URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		## Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);

		if (!$result)
		{
			CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: curl_exec failed in obtainAccountNumberAndLoad");

			return array(
				false,
				false
			);
		}

		$outarr = null;
		$retarr = array();

		//Parse XML reponse
		preg_match_all("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
		//switch on response code for accepted or declined
		switch ($retarr['Response_Code'])
		{
			case '01': //Declined
				// echo "Declined<br />".$retarr['Response_Text']."<br />";
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Decline in obtainAccountNumberAndLoad: resp text = {$retarr['Response_Text']}");

				return array(
					false,
					false
				);
			case '00': //Accepted
				return array(
					$retarr['Card_Number'],
					$retarr['Auth_Reference']
				);
			default:
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Unexpected responce code in obtainAccountNumberAndLoad: {$retarr['Response_Code']} | {$retarr['Response_Text']}");

				return array(
					false,
					false
				);
		}
	}

	static function unloadDebitGiftCardWithRetry($gift_card_number, $trans_amount, $ccRef, $storeId = false, $orderId = '0', $POS_type = 'M')
	{
		$attemptCount = 0;
		$transId = COrders::generateConfirmationNum();

		while ($attemptCount++ < 6)
		{
			$result = self::unloadDebitGiftCard($gift_card_number, $transId, $trans_amount, $ccRef, $storeId, $orderId, $POS_type);
			if ($result)
			{
				return $result;
			}

			sleep(5);
		}

		return false;
	}

	static function unloadDebitGiftCard($gift_card_number, $transId, $trans_amount, $ccRef, $storeId = false, $orderId = '0', $POS_type = 'M')
	{
		$array = CGiftCard::getStoreDetails($storeId);

		$post_string = 'Auth_Request=' . chr(2) . '<Request>
				<Merchant_Number>' . $array['merchantNumber'] . '</Merchant_Number>
				<Terminal_ID>' . $array['terminalNumber'] . '</Terminal_ID>
				<Action_Code>01</Action_Code>
				<Trans_Type>N</Trans_Type>
				<Transaction_Amount>' . $trans_amount . '</Transaction_Amount>
				<POS_Entry_Mode>' . $POS_type . '</POS_Entry_Mode>
				<Card_Number>' . $gift_card_number . '</Card_Number>
				<Transaction_ID>' . $transId . '</Transaction_ID>
			</Request>' . chr(3);

		//For debugging
		//echo "\r\n<br /><textarea rows=50 cols=90>".htmlentities($post_string)."</textarea>";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CGiftCard::DEBIT_GIFT_CARD_URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		## Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);

		$outarr = null;
		$retarr = array();

		//Parse XML reponse
		preg_match_all("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
		//switch on response code for accepted or declined
		switch ($retarr['Response_Code'])
		{
			case '01': //Declined
				// echo "Declined<br />".$retarr['Response_Text']."<br />";
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Decline in unloadDebitGiftCard: resp text = {$retarr['Response_Text']}");
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Decline in unloadDebitGiftCard: request xml = {$post_string}, store_id={$storeId}");

				return false;
			case '00': //Accepted
				{
					// echo "Your Gift Card was Debited ".$trans_amount."<br />
					// Your Gift Card Has a Balance of ".$retarr['Amount_Balance']."<br />";
					//create DAO for gift_card_transaction
					$gcTrans = DAO_CFactory::create('gift_card_transaction');
					$gcTrans->transaction_type = '1';
					$gcTrans->transaction_response = $retarr['Response_Code'];
					$gcTrans->transaction_date = date("Y-m-d H:i:s");
					$gcTrans->transaction_amount = $trans_amount;
					$gcTrans->pos_type = $POS_type;
					$gcTrans->auth_ref_number = $retarr['Auth_Reference'];
					$gcTrans->gift_card_number = str_repeat('X', (strlen($gift_card_number) - 4)) . substr($gift_card_number, -4);
					$gcTrans->cc_ref_number = $ccRef;
					if (!$storeId || empty($storeId))
					{
						$storeId = 'null';
					}

					$gcTrans->store_id = $storeId;
					$gcTrans->order_id = $orderId;
					$gcTrans->transaction_id = $transId;

					$rslt_trans = $gcTrans->insert();

					$message = CCrypto::encode(base64_encode($gift_card_number));
					$gcTrans->query("Call store_gcan_trans('$message', {$gcTrans->id})");

					CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Success in unloadDebitGiftCard: resp text = {$retarr['Response_Text']}");

					return $transId;
				}
				break;
			default:
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Unexpected responce code in unloadDebitGiftCard: {$retarr['Response_Code']} | {$retarr['Response_Text']}");

				return false;
		}
	}

	static function unloadDebitGiftCardVerbose($gift_card_number, $trans_amount, $ccRef, $storeId = false, $orderId = '0', $POS_type = 'M')
	{
		$array = CGiftCard::getStoreDetails($storeId);

		$transId = COrders::generateConfirmationNum();
		$post_string = 'Auth_Request=' . chr(2) . '<Request>
				<Merchant_Number>' . $array['merchantNumber'] . '</Merchant_Number>
				<Terminal_ID>' . $array['terminalNumber'] . '</Terminal_ID>
				<Action_Code>01</Action_Code>
				<Trans_Type>N</Trans_Type>
				<Transaction_Amount>' . $trans_amount . '</Transaction_Amount>
				<POS_Entry_Mode>' . $POS_type . '</POS_Entry_Mode>
				<Card_Number>' . $gift_card_number . '</Card_Number>
				<Transaction_ID>' . $transId . '</Transaction_ID>
			</Request>' . chr(3);

		//For debugging
		//echo "\r\n<br /><textarea rows=50 cols=90>".htmlentities($post_string)."</textarea>";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CGiftCard::DEBIT_GIFT_CARD_URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		## Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);

		$outarr = null;
		$retarr = array();

		//Parse XML reponse
		preg_match_all("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
		//switch on response code for accepted or declined
		switch ($retarr['Response_Code'])
		{
			case '01': //Declined
				// echo "Declined<br />".$retarr['Response_Text']."<br />";
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Decline in unloadDebitGiftCardVerbose: resp text = {$retarr['Response_Text']}");

				return "Declined|null";
			case '00': //Accepted
				{
					// echo "Your Gift Card was Debited ".$trans_amount."<br />
					// Your Gift Card Has a Balance of ".$retarr['Amount_Balance']."<br />";
					//create DAO for gift_card_transaction
					$gcTrans = DAO_CFactory::create('gift_card_transaction');
					$gcTrans->transaction_type = '1';
					$gcTrans->transaction_response = $retarr['Response_Code'];
					$gcTrans->transaction_date = date("Y-m-d H:i:s");
					$gcTrans->transaction_amount = $trans_amount;
					$gcTrans->pos_type = $POS_type;
					$gcTrans->auth_ref_number = $retarr['Auth_Reference'];
					$gcTrans->gift_card_number = str_repeat('X', (strlen($gift_card_number) - 4)) . substr($gift_card_number, -4);
					$gcTrans->cc_ref_number = $ccRef;
					if (!$storeId || empty($storeId))
					{
						$storeId = 'null';
					}

					$gcTrans->store_id = $storeId;
					$gcTrans->order_id = $orderId;
					$gcTrans->transaction_id = $transId;

					$rslt_trans = $gcTrans->insert();

					$message = CCrypto::encode(base64_encode($gift_card_number));
					$gcTrans->query("Call store_gcan_trans('$message', {$gcTrans->id})");

					return 'Success|' . $gcTrans->id;
				}
				break;
			default:
				CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Unexpected responce code in unloadDebitGiftCardVerbose: {$retarr['Response_Code']} | {$retarr['Response_Text']}");

				return "Failure|" . $retarr['Response_Code'] . "|" . $retarr['Response_Text'];
		}
	}

	static function loadDebitGiftCardWithRetry($gift_card_number, $trans_amount, $ccRef, $ccNum = false, $ccType = false, $billing_email = 'null', $POS_type = 'M', $store_id = false, $additional_info = false)
	{
		$attemptCount = 0;
		$transId = COrders::generateConfirmationNum();

		while ($attemptCount++ < 6)
		{
			list($result, $description, $GCTRowID) = self::loadDebitGiftCard($gift_card_number, $transId, $trans_amount, $ccRef, $ccNum, $ccType, $billing_email, $POS_type, $store_id, $additional_info);
			if ($result)
			{
				return array(
					$result,
					$description,
					$GCTRowID
				);
			}

			sleep(5);
		}

		return array(
			false,
			$description,
			$GCTRowID
		);
	}

	// $additional_info is an Json encoded array that is stuck in a text field. It's needed to pass billing info to the confirmation page
	static function loadDebitGiftCard($gift_card_number, $transId, $trans_amount, $ccRef, $ccNum = false, $ccType = false, $billing_email = 'null', $POS_type = 'M', $store_id = false, $additional_info = false)

	{
		$array = CGiftCard::getStoreDetails($store_id);

		$post_string = 'Auth_Request=' . chr(2) . '<Request>
				<Merchant_Number>' . $array['merchantNumber'] . '</Merchant_Number>
				<Terminal_ID>' . $array['terminalNumber'] . '</Terminal_ID>
				<Action_Code>02</Action_Code>
				<Trans_Type>N</Trans_Type>
				<Transaction_Amount>' . $trans_amount . '</Transaction_Amount>
				<POS_Entry_Mode>' . $POS_type . '</POS_Entry_Mode>
				<Card_Number>' . $gift_card_number . '</Card_Number>
				<Transaction_ID>' . $transId . '</Transaction_ID>
			</Request>' . chr(3);

		//For debugging
		//echo "\r\n<br /><textarea rows=50 cols=90>".htmlentities($post_string)."</textarea>";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CGiftCard::DEBIT_GIFT_CARD_URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		## Below two option will enable the HTTPS option.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);

		$outarr = null;
		$retarr = array();

		//Parse XML reponse
		preg_match_all("/<(.*?)>(.*?)\</", $result, $outarr, PREG_SET_ORDER);
		$n = 0;
		while (isset($outarr[$n]))
		{
			$retarr[$outarr[$n][1]] = strip_tags($outarr[$n][0]);
			$n++;
		}
		//switch on response code for accepted or declined
		switch ($retarr['Response_Code'])
		{
			case '01': //Declined
				$response = "Declined<br />" . $retarr['Response_Text'] . "<br />";

				return array(
					false,
					$response
				);
			case '00': //Accepted
			{
				$response = "Your Gift Card was Loaded with " . $trans_amount . "<br />
						Your Gift Card Has a Balance of " . $retarr['Amount_Balance'] . "<br />";
				//create DAO for gift_card_transaction
				$gcTrans = DAO_CFactory::create('gift_card_transaction');

				$gcTrans->transaction_type = '2';
				$gcTrans->transaction_response = $retarr['Response_Code'];
				$gcTrans->transaction_date = date("Y-m-d H:i:s");
				$gcTrans->transaction_amount = $trans_amount;
				$gcTrans->pos_type = $POS_type;
				$gcTrans->auth_ref_number = $retarr['Auth_Reference'];
				$gcTrans->gift_card_number = str_repeat('X', (strlen($gift_card_number) - 4)) . substr($gift_card_number, -4);

				if (is_null($ccNum))
				{
					$gcTrans->cc_number = '';
				}
				else
				{
					$gcTrans->cc_number = str_repeat('X', (strlen($ccNum) - 4)) . substr($ccNum, -4);
				}
				$gcTrans->cc_type = $ccType;
				$gcTrans->billing_email = $billing_email;

				$gcTrans->cc_ref_number = $ccRef;
				$gcTrans->transaction_id = $transId;

				if (!empty($additional_info))
				{
					$gcTrans->additional_info = $additional_info;
				}

				if (!$store_id || empty($store_id))
				{
					$store_id = 'null';
				}
				$gcTrans->store_id = $store_id;

				$rslt = $gcTrans->insert();

				$message = CCrypto::encode(base64_encode($gift_card_number));
				$gcTrans->query("Call store_gcan_trans('$message', {$gcTrans->id})");

				return array(
					true,
					$response,
					$gcTrans->id
				);
			}
			default:
			{
				if (empty($retarr['Response_Code']))
				{
					CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Empty response code in loadDebitGiftCard: {$retarr['Response_Code']} | {$retarr['Response_Text']}");

					return array(
						false,
						"A communication error occurred.",
						false
					);
				}
				else
				{
					CLog::RecordNew(CLog::DEBUG, "GC_DEBUG: Unexpected responce code in loadDebitGiftCard: {$retarr['Response_Code']} | {$retarr['Response_Text']}");

					return array(
						false,
						"Unexpected responce code: {$retarr['Response_Code']} | {$retarr['Response_Text']}",
						false
					);
				}
			}
		}
	}

	/*
	* Adds the initial data for an order and returns the row id
	* which is then stored in the cart
	*/
	static function addUnprocessedVirtualCardOrder($design_type, $trans_amount, $to_name, $from_name, $message, $recipient_email, $transaction_UI = 'UNKNOWN')
	{

		$gcOrder = DAO_CFactory::create('gift_card_order');

		$gcOrder->initial_amount = $trans_amount;
		$gcOrder->media_type = 'VIRTUAL';
		$gcOrder->design_type_id = $design_type;
		$gcOrder->to_name = $to_name;
		$gcOrder->from_name = $from_name;
		$gcOrder->message_text = $message;
		$gcOrder->s_and_h_amount = 0;
		$gcOrder->transaction_ui = $transaction_UI;
		$gcOrder->recipient_email_address = $recipient_email;
		$gcOrder->processed = 0;
		$gcOrder->paid = 0;

		$rslt_order = $gcOrder->insert();

		return $rslt_order;
	}

	static function editUnprocessedVirtualCardOrder($order_id, $design_type, $trans_amount, $to_name, $from_name, $message, $recipient_email, $transaction_UI = 'UNKNOWN')
	{

		$gcOrder = DAO_CFactory::create('gift_card_order');
		$gcOrder->id = $order_id;
		if ($gcOrder->find(true))
		{
			$orgOrder = clone($gcOrder);
			$gcOrder->initial_amount = $trans_amount;
			$gcOrder->media_type = 'VIRTUAL';
			$gcOrder->design_type_id = $design_type;
			$gcOrder->to_name = $to_name;
			$gcOrder->from_name = $from_name;
			$gcOrder->message_text = $message;
			$gcOrder->s_and_h_amount = 0; // TODO: ???
			$gcOrder->transaction_ui = $transaction_UI;
			$gcOrder->recipient_email_address = $recipient_email;
			$gcOrder->processed = 0;
			$gcOrder->paid = 0;

			return $gcOrder->update($orgOrder);
		}

		return false;
	}

	/*
	* Adds the initial data for an order and returns the row id
	* which is then stored in the cart
	*/
	static function addUnprocessedPhysicalCardOrder($design_type, $trans_amount, $to_name, $from_name, $message, $shipping_first_name, $shipping_last_name, $shipping_address_1, $shipping_address_2, $shipping_state, $shipping_zip, $shipping_city, $transaction_UI = 'UNKNOWN', $store_id = false)
	{

		$gcOrder = DAO_CFactory::create('gift_card_order');

		$gcOrder->first_name = $shipping_first_name;
		$gcOrder->last_name = $shipping_last_name;
		$gcOrder->shipping_address_1 = $shipping_address_1;
		$gcOrder->shipping_address_2 = $shipping_address_2;
		$gcOrder->shipping_state = $shipping_state;
		$gcOrder->shipping_zip = $shipping_zip;
		$gcOrder->initial_amount = $trans_amount;
		$gcOrder->s_and_h_amount = COrders::GIFT_CARD_SHIPPING;
		$gcOrder->transaction_ui = $transaction_UI;
		$gcOrder->media_type = 'PHYSICAL';
		$gcOrder->design_type_id = $design_type;
		$gcOrder->to_name = $to_name;
		$gcOrder->from_name = $from_name;
		$gcOrder->message_text = $message;
		$gcOrder->shipping_city = $shipping_city;
		$gcOrder->processed = 0;
		$gcOrder->paid = 0;
		if ($store_id)
		{
			$gcOrder->store_id = $store_id;
		}

		$rslt_order = $gcOrder->insert();

		if ($rslt_order)
		{
			return $gcOrder->id;
		}

		return false;
	}

	static function editUnprocessedPhysicalCardOrder($order_id, $design_type, $trans_amount, $to_name, $from_name, $message, $shipping_first_name, $shipping_last_name, $shipping_address_1, $shipping_address_2, $shipping_state, $shipping_zip, $shipping_city, $transaction_UI = 'UNKNOWN', $store_id = false)
	{

		$gcOrder = DAO_CFactory::create('gift_card_order');
		$gcOrder->id = $order_id;
		if ($gcOrder->find(true))
		{
			$orgOrder = clone($gcOrder);
			$gcOrder->first_name = $shipping_first_name;
			$gcOrder->last_name = $shipping_last_name;
			$gcOrder->shipping_address_1 = $shipping_address_1;
			$gcOrder->shipping_address_2 = $shipping_address_2;
			$gcOrder->shipping_state = $shipping_state;
			$gcOrder->shipping_zip = $shipping_zip;
			$gcOrder->initial_amount = $trans_amount;
			$gcOrder->s_and_h_amount = COrders::GIFT_CARD_SHIPPING;
			$gcOrder->transaction_ui = $transaction_UI;
			$gcOrder->media_type = 'PHYSICAL';
			$gcOrder->design_type_id = $design_type;
			$gcOrder->to_name = $to_name;
			$gcOrder->from_name = $from_name;
			$gcOrder->message_text = $message;
			$gcOrder->shipping_city = $shipping_city;
			$gcOrder->processed = 0;
			$gcOrder->paid = 0;
			if ($store_id)
			{
				$gcOrder->store_id = $store_id;
			}

			return $gcOrder->update($orgOrder);
		}

		return false;
	}

	static function purchaseDebitGiftCard($trans_amount, $SH_amount, $ccRef, $card_number, $card_type, $first_name, $last_name, $address_1, $address_2, $state, $zip, $city, $email, $paid = 0, $user_id = 'null', $transaction_UI = 'UNKNOWN', $store_id = false)
	{

		$gcOrder = DAO_CFactory::create('gift_card_order');

		$gcOrder->first_name = $first_name;
		$gcOrder->last_name = $last_name;
		$gcOrder->shipping_address_1 = $address_1;
		$gcOrder->shipping_address_2 = $address_2;
		$gcOrder->shipping_state = $state;
		$gcOrder->shipping_zip = $zip;
		$gcOrder->purchase_date = date("Y-m-d H:i:s");
		$gcOrder->initial_amount = $trans_amount;
		$gcOrder->s_and_h_amount = $SH_amount;
		$gcOrder->transaction_ui = $transaction_UI;
		$gcOrder->email = $email;
		$gcOrder->payment_card_type = $card_type;
		$gcOrder->payment_card_number = $card_number;
		$gcOrder->cc_ref_number = $ccRef;
		$gcOrder->shipping_city = $city;
		$gcOrder->processed = '0';
		$gcOrder->paid = $paid;
		if ($store_id)
		{
			$gcOrder->store_id = $store_id;
		}

		$gcOrder->user_id = $user_id;

		$rslt_order = $gcOrder->insert();

		if ($rslt_order)
		{
			return "Your Gift Card Has Been Ordered for " . $trans_amount . "<br />";
		}

		CLog::RecordNew(CLog::DEBUG, "GC_DEBUG " . $rslt_order->_lastError->userinfo);

		return 'insert_failed';
	}

	static function addOrderDrivenGiftCardDetailsToTemplate(&$template, $user_id = null, $order_id = false, $GCList = false, $canModify = false, $front_end_ordering = true)
	{
		$giftcardArray = array();
		$giftcardTotal = 0.00;
		$gcList = DAO_CFactory::create('gift_card_order');

		$confirm_id_list = "";

		$counter = 0;

		if (!empty($GCList))
		{
			foreach ($GCList as $thisCID)
			{
				$confirm_id_list .= "'" . $thisCID;
				$counter++;

				if ($counter == count($GCList))
				{
					$confirm_id_list .= "'";
				}
				else
				{
					$confirm_id_list .= "',";
				}
			}
		}

		if ($front_end_ordering)
		{

			if ($order_id)
			{
				$gcList->paid = '1';
				$gcList->user_id = $user_id;
				$gcList->order_id = $order_id;
				$gcList->find();
			}
			else if ($GCList && !empty($user_id))
			{
				$gcList->query("SELECT gift_card_design.image_path_egift, gift_card_order.* FROM gift_card_order
									LEFT JOIN gift_card_design on gift_card_design.id = gift_card_order.design_type_id
									WHERE order_confirm_id IN (" . $confirm_id_list . ") AND user_id = $user_id AND gift_card_order.is_deleted = 0");
			}
			if ($GCList && empty($user_id))
			{
				$viewing_cutoff_date = CTemplate::unix_to_mysql_timestamp(strtotime("-2 days"));

				$gcList->query("SELECT gift_card_design.image_path_egift, gift_card_order.* FROM gift_card_order
									LEFT JOIN gift_card_design on gift_card_design.id = gift_card_order.design_type_id
									 WHERE order_confirm_id IN (" . $confirm_id_list . ") AND ISNULL(user_id) 
									 AND gift_card_order.is_deleted = 0 AND gift_card_order.purchase_date > '" . $viewing_cutoff_date . "'");
			}
			else
			{
				// ??? throw new Exception("Unable to find Gift Card Orders.");
			}
		}
		else
		{
			if ($order_id)
			{
				$gcList->paid = '1';
				$gcList->user_id = $user_id;
				$gcList->order_id = $order_id;
				$gcList->find();
			}
			else if ($GCList && !empty($user_id))
			{
				$gcList->query("SELECT gift_card_design.image_path_egift, gift_card_order.* FROM gift_card_order
								LEFT JOIN gift_card_design on gift_card_design.id = gift_card_order.design_type_id
								WHERE gift_card_order.id IN (" . $confirm_id_list . ") AND gift_card_order.user_id = '" . $user_id . "' AND gift_card_order.is_deleted = 0");
			}
			if ($GCList && empty($user_id))
			{
				$gcList->query("SELECT  gift_card_design.image_path_egift, gift_card_order.* FROM gift_card_order 
					LEFT JOIN gift_card_design on gift_card_design.id = gift_card_order.design_type_id
					WHERE gift_card_order.id IN (" . $confirm_id_list . ") AND ISNULL(gift_card_order.user_id) AND gift_card_order.is_deleted = 0");
			}
			else
			{
				// ??? throw new Exception("Unable to find Gift Card Orders.");
			}
		}

		$gcPaymentCardType = false;
		$gcPaymentCardNumber = false;
		$gcPaymentDate = false;

		$giftcardArray = array();
		while ($gcList->fetch())
		{
			$DescString = "From: {$gcList->from_name}<br />";
			$DescString .= "Message: {$gcList->message_text}<br />";

			if ($gcList->media_type == 'PHYSICAL')
			{
				$DescString .= "Ship To: {$gcList->first_name} {$gcList->last_name}<br />";

				$DescString .= "Shipping Address: {$gcList->shipping_address_1}";
				if (!empty($gcList->shipping_address_2))
				{
					$DescString .= ", {$gcList->shipping_address_2}";
				}

				$DescString .= " {$gcList->shipping_city}, {$gcList->shipping_state} {$gcList->shipping_zip}<br />";
			}

			if ($gcList->media_type == 'VIRTUAL')
			{
				$DescString .= "Recipient Address: {$gcList->recipient_email_address}";
				if ($canModify)
				{
					$DescString .= '&nbsp;<font color="red">[</font> <a href="javascript:modify_eGiftCard(' . $gcList->id . ')">Modify </a><font color="red">]</font>';
				}
			}

			//				$imagePath = (($GCOrderObj->media_type == 'PHYSICAL') ? $thisDesign['image_path'] : $thisDesign['image_path_virtual']);
			//				$imagePath = IMAGES_PATH . "/gift_cards/" . $imagePath;

			$giftcardArray[] = array(
				'id' => $gcList->id,
				'order_confirm_id' => $gcList->order_confirm_id,
				'name' => ($gcList->media_type == 'PHYSICAL' ? $gcList->first_name . ' ' . $gcList->last_name : $gcList->recipient_email_address),
				'gc_media_type' => ($gcList->media_type == 'VIRTUAL' ? 'Virtual eGift Card' : 'Traditional Gift Card'),
				'to_name' => $gcList->to_name,
				'desc' => $DescString,
				'design_type_id' => $gcList->design_type_id,
				'image_name' => ($gcList->media_type == 'VIRTUAL' ? $gcList->image_path_egift : 'gc_design_' . ($gcList->design_type_id - 1) . ".jpg"),
				'media_type' => $gcList->media_type,
				'gc_amount' => ($gcList->media_type == 'PHYSICAL' ? $gcList->initial_amount + COrders::GIFT_CARD_SHIPPING : $gcList->initial_amount)
			);

			$giftcardTotal += $gcList->initial_amount;
			if ($gcList->media_type == 'PHYSICAL')
			{
				$giftcardTotal += COrders::GIFT_CARD_SHIPPING;
			}

			$gcPaymentCardType = $gcList->payment_card_type;
			$gcPaymentCardNumber = $gcList->payment_card_number;
			$gcPaymentDate = $gcList->purchase_date;
		}

		$template->assign("billing_name", $gcList->billing_name);
		$template->assign("billing_zip", $gcList->billing_zip);
		$template->assign("billing_address", $gcList->billing_address);
		$template->assign("billing_email", $gcList->email);

		$template->assign("gift_card_purchase_array", $giftcardArray);
		$template->assign("gift_card_total", $giftcardTotal);
		$template->assign("gcPaymentCardType", $gcPaymentCardType);
		$template->assign("gcPaymentCardNumber", $gcPaymentCardNumber);
		$template->assign("gcPaymentDate", $gcPaymentDate);
	}

	static function sendConfirmationEmail($gcOrder, $totalPurchaseAmount, $giftcardArray, $toEmail)
	{

		try
		{
			$Mail = new CMail();

			$data = array(
				'gift_card_purchase_array' => $giftcardArray,
				'gift_card_total' => $totalPurchaseAmount,
				'gcPaymentCardType' => $gcOrder->payment_card_type,
				'gcPaymentCardNumber' => $gcOrder->payment_card_number,
				'gcPaymentDate' => $gcOrder->timestamp_updated
			);

			$contentsHtml = CMail::mailMerge('order_gift_card_cart.html.php', $data, false);

			$Mail->send(null, null, null, $toEmail, 'Dream Dinners Gift Card Receipt', $contentsHtml, null, '', '', null, 'order_gift_card - cart');
		}
		catch (exception $e)
		{
			CLog::RecordException($e);
		}
	}

	static function resend_receipt($this_order, $order_total)
	{
		$paymentData = array(
			'credit_card_number' => $this_order[0]->payment_card_number,
			'billing_name' => $this_order[0]->billing_name,
			'billing_address' => $this_order[0]->billing_address,
			'billing_zip' => $this_order[0]->billing_zip,
			'primary_email' => $this_order[0]->email,
			'payment_card_type' => $this_order[0]->payment_card_type,
			'purchase_date' => $this_order[0]->purchase_date
		);

		self::sendGCOrderReceiptEmail($this_order, $paymentData, $order_total);

		return true;
	}

	static function resend_eGift_card($this_order)
	{
		$acct_retrieve = DAO_CFactory::create('gift_card_order');
		$acct_retrieve->query("SET @GCAN = ''");
		$acct_retrieve->query("Call get_gcan({$this_order->id}, @GCAN)");
		$acct_retrieve->query("SELECT @GCAN");
		$acct_retrieve->fetch();
		$account = base64_decode(CCrypto::decode($acct_retrieve->_GCAN));

		self::sendVirtualGiftCard($this_order, $account);

		return true;
	}

	static function sendGCOrderReceiptEmail($giftCardObjArray, $paymentData, $amountCharged)
	{
		try
		{
			$Mail = new CMail();

			$designs = self::getActiveGCDesignArray();

			$data = array(
				'date_of_purchase' => CTemplate::dateTimeFormat($paymentData['purchase_date']),
				'charged_amount' => CTemplate::moneyFormat($amountCharged),
				'credit_card_number' => str_repeat('X', (strlen($paymentData['credit_card_number']) - 4)) . substr($paymentData['credit_card_number'], -4),
				'billing_name' => $paymentData['billing_name'],
				'billing_address' => $paymentData['billing_address'],
				'billing_zip' => $paymentData['billing_zip'],
				'billing_email' => $paymentData['primary_email'],
				'payment_card_type' => (isset($paymentData['payment_card_type']) ? $paymentData['payment_card_type'] : $paymentData['credit_card_type']),
				'cards_purchased' => array()
			);

			foreach ($giftCardObjArray as $thisOrder)
			{
				$image_html = "";
				if ($thisOrder->media_type == 'PHYSICAL')
				{
					$image_html = '<img src="' . EMAIL_IMAGES_PATH . "/gift_cards/" . $designs['designs'][$thisOrder->design_type_id]['image_path'] . '" alt="" />';
				}
				else
				{
					$image_html = '<img src="' . EMAIL_IMAGES_PATH . "/gift_cards/" . $designs['designs'][$thisOrder->design_type_id]['image_path_virtual'] . '" alt="" />';
				}

				$data['cards_purchased'][$thisOrder->id] = array(
					'card_image' => $image_html,
					'design_title' => $designs['designs'][$thisOrder->design_type_id]['title'],
					'media_type' => $thisOrder->media_type,
					'card_amount' => $thisOrder->initial_amount,
					'from_name' => $thisOrder->from_name,
					'to_name' => $thisOrder->to_name,
					'message_text' => $thisOrder->message_text,
					'recipient_email' => $thisOrder->recipient_email_address,
					'ship_to_name' => $thisOrder->first_name . " " . $thisOrder->last_name,
					'shipping_address_1' => $thisOrder->shipping_address_1,
					'shipping_address_2' => $thisOrder->shipping_address_2,
					'shipping_city' => $thisOrder->shipping_city,
					'shipping_state' => $thisOrder->shipping_state,
					'shipping_zip' => $thisOrder->shipping_zip,
					'confirm_id' => $thisOrder->order_confirm_id
				);
			}

			$contentsText = CMail::mailMerge('order_gift_card.txt.php', $data, false);
			$contentsHtml = CMail::mailMerge('order_gift_card.html.php', $data, false);

			$Mail->send(null, null, null, $paymentData['primary_email'], 'Gift Card Receipt from Dream Dinners', $contentsHtml, $contentsText, '', '', null, 'order_gift_card - order');
		}
		catch (exception $e)
		{
			CLog::RecordException($e);
		}
	}

	static function sendVirtualGiftCard($gcOrderObj, $account_number)
	{

		try
		{
			$Mail = new CMail();

			$templateName = null;
			$textTemplateName = null;

			switch ($gcOrderObj->design_type_id)
			{
				case 2:
					$templateName = "virtual_gift_card/virtual_gift_card_logo.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_logo.txt.php";
					break;
				case 3:
					$templateName = "virtual_gift_card/virtual_gift_card_celebrate.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_celebrate.txt.php";
					break;

				//replaced by next 2 but left for resending old purchases
				case 4:
					$templateName = "virtual_gift_card/virtual_gift_card_fun.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_fun.txt.php";
					break;

				case 5:
					$templateName = "virtual_gift_card/virtual_gift_card_life_easier.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_life_easier.txt.php";
					break;

				//may need new templates
				case 6:
					$templateName = "virtual_gift_card/virtual_gift_card_fun.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_fun.txt.php";
					break;

				case 7:
					$templateName = "virtual_gift_card/virtual_gift_card_life_easier.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_life_easier.txt.php";
					break;

				case 8:
					$templateName = "virtual_gift_card/virtual_gift_card_occasion2.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_occasion2.txt.php";
					break;

				case 9:
					$templateName = "virtual_gift_card/virtual_gift_card_occasion3.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_occasion3.txt.php";
					break;

				default:
					$templateName = "virtual_gift_card/virtual_gift_card_logo.html.php";
					$textTemplateName = "virtual_gift_card/virtual_gift_card_logo.txt.php";
			}

			//$subject = "an eGift Card from " . $gcOrderObj->from_name;
			$subject = "You've received a Dream Dinners gift card";

			$data = array(
				'to_name' => $gcOrderObj->to_name,
				'from_name' => $gcOrderObj->from_name,
				'message_text' => $gcOrderObj->message_text,
				'account_number' => $account_number,
				'card_amount' => $gcOrderObj->initial_amount
			);

			$contentsText = CMail::mailMerge($textTemplateName, $data, false);
			$contentsHtml = CMail::mailMerge($templateName, $data, false);

			$Mail->send(null, null, null, $gcOrderObj->recipient_email_address, $subject, $contentsHtml, $contentsText, '', '', null, 'egift_card');
		}
		catch (exception $e)
		{
			CLog::RecordException($e);
		}
	}

	static function getStoreDetails($storeId = false)
	{

		$retVal = array();

		if (defined('DEBIT_GIFT_CARD_TESTMODE') && DEBIT_GIFT_CARD_TESTMODE === true)
		{
			$retVal['merchantNumber'] = DEBIT_GIFT_CARD_MERCHANT_NUMBER;
			$retVal['terminalNumber'] = DEBIT_GIFT_CARD_TERMINAL_ID;

			return $retVal;
		}

		$gcOrder = DAO_CFactory::create('store');

		if (!$storeId || empty($storeId))
		{
			if (CBrowserSession::getCurrentStore())
			{
				$storeId = CBrowserSession::getCurrentStore();
			}
			else
			{
				$storeId = '244';
			}
		}

		$gcOrder->id = $storeId;

		$gcOrder->selectAdd();
		$gcOrder->selectAdd("merchant_id");
		$gcOrder->selectAdd("terminal_id");

		$gcOrder->find();

		while ($gcOrder->fetch())
		{
			if ($gcOrder->merchant_id)
			{
				$retVal['merchantNumber'] = $gcOrder->merchant_id;
			}
			else
			{
				$retVal['merchantNumber'] = DEBIT_GIFT_CARD_MERCHANT_NUMBER;
			}
			if ($gcOrder->terminal_id)
			{
				$retVal['terminalNumber'] = $gcOrder->terminal_id;
			}
			else
			{
				$retVal['terminalNumber'] = DEBIT_GIFT_CARD_TERMINAL_ID;
			}
		}

		return $retVal;
	}

	static function sendGiftCardOrderReport()
	{
		$filename = REPORT_OUTPUT_BASE . '/gift_card_order_report/dream_dinners_orders_' . date("m-d-y") . '.csv';

		$list = array(
			array('Amount', 'First Name', 'Last Name', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'Merchant_ID', 'Design Name', 'To Name', 'From Name', 'Message', 'ORDER_ID')
		);

		$processed_ids = array();

		$DAO_Gift_card_order = DAO_CFactory::create('gift_card_order', true);
		$DAO_Gift_card_order->media_type = 'PHYSICAL';
		$DAO_Gift_card_order->processed = 0;
		$DAO_Gift_card_order->paid = 1;
		$DAO_Gift_card_order->joinAddWhereAsOn(DAO_CFactory::create('gift_card_design', true));
		$DAO_Gift_card_order->joinAddWhereAsOn(DAO_CFactory::create('store', true), 'LEFT');
		$DAO_Gift_card_order->find();

		while ($DAO_Gift_card_order->fetch())
		{
			$list[] = array (
				$DAO_Gift_card_order->initial_amount,
				$DAO_Gift_card_order->first_name,
				$DAO_Gift_card_order->last_name,
				$DAO_Gift_card_order->shipping_address_1,
				$DAO_Gift_card_order->shipping_address_2,
				$DAO_Gift_card_order->shipping_city,
				$DAO_Gift_card_order->shipping_state,
				$DAO_Gift_card_order->shipping_zip,
				(!empty($DAO_Gift_card_order->DAO_store->merchant_id)) ? $DAO_Gift_card_order->DAO_store->merchant_id : '711389000121',
				$DAO_Gift_card_order->DAO_gift_card_design->title,
				$DAO_Gift_card_order->to_name,
				$DAO_Gift_card_order->from_name,
				$DAO_Gift_card_order->message_text,
				$DAO_Gift_card_order->id
			);

			array_push($processed_ids, $DAO_Gift_card_order->id);
		}

		if(!empty($processed_ids))
		{
			$fp = fopen($filename, 'w');

			foreach ($list as $line)
			{
				fputcsv($fp, $line);
			}

			fclose($fp);
		}

		$subject = "Dream Dinners Card Orders Report for " . date("m-d-y");
		$Mail = new CMail();
		$Mail->to_email = "sarah@smarttransactions.com, geekifyinc@gmail.com";
		$Mail->cc_email = "ryan.snook@dreamdinners.com";
		$Mail->subject = $subject;

		if (!empty($processed_ids))
		{
			try
			{
				$email_data = array(
					'gift_card_order' => $list
				);

				$Mail->body_html = CMail::mailMerge('report/gift_card_order/physical_gift_card_order_report.html.php', $email_data);
				$Mail->body_text = CMail::mailMerge('report/gift_card_order/physical_gift_card_order_report.txt.php', $email_data);
				$Mail->attachment['name'] = 'dream_dinners_orders_' . date("m-d-y") . '.csv';
				$Mail->attachmentbase64 = base64_encode(file_get_contents($filename));
				$Mail->sendEmail();

				//update gift_card_order to flag these orders as processed
				foreach ($processed_ids as $id)
				{
					$update_DAO_gift_card_order = DAO_CFactory::create('gift_card_order', true);
					$update_DAO_gift_card_order->id = $id;
					$update_DAO_gift_card_order->find(true);
					$org_update_DAO_gift_card_order = clone($update_DAO_gift_card_order);
					$update_DAO_gift_card_order->processed = '1';
					$update_DAO_gift_card_order->update($org_update_DAO_gift_card_order);
				}
			}
			catch (exception $e)
			{
				CLog::RecordException($e);
			}
		}
		else
		{
			$Mail->body_text = "No orders today.";
			$Mail->sendEmail();
		}
	}
}

?>