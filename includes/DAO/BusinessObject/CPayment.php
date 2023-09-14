<?php

require_once 'DAO/Payment.php';
require_once 'DAO/BusinessObject/COrdersDigest.php';
require_once 'DAO/BusinessObject/CUser.php';

/* ------------------------------------------------------------------------------------------------
 *	Class: CPayment
 *
 *	Data:
 *
 *	Methods:
 *		Create()
 *
 *  	Properties:
 *
 *
 *	Description:
 *
 *
 *	Requires:
 *
 * -------------------------------------------------------------------------------------------------- */

class CPayment extends DAO_Payment
{

	const CHECK = 'CHECK';
	const CASH = 'CASH';
	const GIFT_CERT = 'GIFT_CERT';
	const GIFT_CARD = 'GIFT_CARD';
	const CREDIT_CARD = 'CREDIT_CARD';
	const CC = 'CC';
	const REFERENCE = 'REFERENCE';
	const OTHER = 'OTHER';
	const CREDIT = 'CREDIT';
	const REFUND = 'REFUND';
	const REFUND_CASH = 'REFUND_CASH';
	const STORE_CREDIT = 'STORE_CREDIT';
	const REFUND_STORE_CREDIT = 'REFUND_STORE_CREDIT';
	const REFUND_GIFT_CARD = 'REFUND_GIFT_CARD';

	const PAY_AT_SESSION = 'PAY_AT_SESSION';

	const VISA = 'Visa';
	const MASTERCARD = 'Mastercard';
	const DISCOVERCARD = 'Discover';
	const AMERICANEXPRESS = 'American Express';

	const PENDING = 'PENDING';
	const SUCCESS = 'SUCCESS';
	const FAIL = 'FAIL';
	const CANCELLED = 'CANCELLED';

	const GC_TYPE_SCRIP = 'SCRIP';
	const GS_TYPE_STANDARD = 'STANDARD';
	const GC_TYPE_DONATED = 'DONATED';
	const GC_TYPE_VOUCHER = 'VOUCHER';

	//transient properties, not stored in db
	private $ccNumber = null;
	private $ccMonth = null;
	private $ccYear = null;
	private $ccName = null;
	private $ccAddress = null;
	private $ccCity = null;
	private $ccState = null;
	private $ccZip = null;
	private $ccSecurityCode = null;        // TAG: DAVIDB  (new line)
	public $store_credit_DAO = null;
	public $save_card_on_completion = false;

	/**
	 * Added for the new delayed payment process 1/17/06 ToddW
	 * This is the second (final) payment record. Spawned from the first initial
	 * deposit payment. processPayment() will build this object from the first payment.
	 * It will need to be inserted as a pending payment, to later be processed
	 * by the cron task.
	 */
	public $PendingPayment = null;

	public $verisignExtendedRslt = null;

	function __construct()
	{
		parent::__construct();
	}

	static function getCardTypeString($code)
	{
		switch ($code)
		{
			case 0:
				return 'Visa';
			case 1:
				return 'Mastercard';
			case 2:
				return 'Discover';
			case 3:
				return 'American Express';
			default:
				CLog::Assert(false, "Unknown Card Type");
		}
	}

	static function getMerchantAccountID($store_id)
	{
		//find the franchise merchant account number
		$MerchantAccount = DAO_CFactory::create("merchant_accounts");

		$MerchantAccount->store_id = $store_id;
		if (defined('USE_CORPORATE_TEST_ACCOUNT') && USE_CORPORATE_TEST_ACCOUNT)
		{
			$MerchantAccount->store_id = 244;
		}

		if (!$MerchantAccount->find(true))
		{
			return 'noMerchantAccountFound';
		}

		return $MerchantAccount->id;
	}

	static function getPaymentDescription($payment_type)
	{
		switch ($payment_type)
		{
			case CPayment::CHECK:
				return "Check";
			case CPayment::CASH:
				return "Cash";
			case  CPayment::GIFT_CERT:
				return "Gift Certificate";
			case  CPayment::CC:
				return "Credit Card";
			case  CPayment::CREDIT:
				return "Credit";
			case  CPayment::REFUND:
				return "Refund";
			case  CPayment::REFUND_CASH:
				return "Refund Cash";
			case  CPayment::STORE_CREDIT:
				return "Dream Dinners Gift Card Credit";
			case  CPayment::REFUND_STORE_CREDIT:
				return "Dream Dinners Gift Card Store Credit Refund";
			case CPayment::GIFT_CARD:
				return "New Debit Dream Dinners Gift Card";
			case  CPayment::OTHER:
				return "Other";
			default:
				return "Other";
		}
	}

	static function translatePaymentTypeStr($string)
	{
		switch ($string)
		{
			case "Credit Card Payment":
				return "Credit Card";
			case "Gift Certificate":
				return "Gift Cert";
			case "Dream Dinners Gift Card":
				return "Gift Card";
			case "Dream Dinners Gift Card Refund":
				return "Gift Card Refund";
			default:
				return $string;
		}
	}

	/*
	 *   A revenue event in the context of payments are those payments that lower
	 *   a store's royaltly obligation. That is store credits that were referral rewards
	 *   or DONATED or VOUCHER gift certificates
	 *
	 */
	function recordRevenueEvent($sessionObjOrID, $isRefund = false)
	{
		try
		{

			if (is_object($sessionObjOrID) && get_class($sessionObjOrID) == 'CSession')
			{
				$sessionObj = $sessionObjOrID;
			}
			else
			{
				$sessionObj = DAO_CFactory::create('user');
				$sessionObj->id = $sessionObjOrID;
				if (!$sessionObj->find(true))
				{
					throw new Exception('session not found in CPayment::recordRevenueEvent: ' . $sessionObjOrID);
				}
			}

			$menu_id = $sessionObj->menu_id;

			// capture any revenue events but
			// don't allow a failure here to interrupt the payment process
			$revenue_event = null;

			if ($this->payment_type == CPayment::GIFT_CERT)
			{
				if ($this->gift_cert_type == 'SCRIP')
				{
					$revenue_event = DAO_CFactory::create('revenue_event');
					$revenue_event->event_type = 'GIFT_CERT_ADJUST';
					$revenue_event->amount = ($this->total_amount * .12) * -1;
					$revenue_event->session_amount = $revenue_event->amount;
				}
				else if ($this->gift_cert_type == 'DONATED' || $this->gift_cert_type == 'VOUCHER')
				{
					$revenue_event = DAO_CFactory::create('revenue_event');
					$revenue_event->event_type = 'GIFT_CERT_ADJUST';
					$revenue_event->amount = $this->total_amount * -1;
					$revenue_event->session_amount = $revenue_event->amount;
				}
			}
			else if ($this->payment_type == CPayment::STORE_CREDIT && isset($this->store_credit_DAO) && ($this->store_credit_DAO->credit_type == 2 || $this->store_credit_DAO->credit_type == 3))
			{
				$revenue_event = DAO_CFactory::create('revenue_event');
				$revenue_event->event_type = 'STORE_CREDIT_ADJUST';
				$revenue_event->amount = $this->total_amount * -1;
				$revenue_event->session_amount = $revenue_event->amount;
			}

			if ($revenue_event)
			{

				if (empty($this->timestamp_created))
				{
					$revenue_event->event_time = date("Y-m-d H:i:s");
				}
				else
				{
					$revenue_event->event_time = $this->timestamp_created;
				}

				$revenue_event->store_id = $this->store_id;
				$revenue_event->menu_id = $menu_id;
				$revenue_event->session_id = $sessionObj->id;
				$revenue_event->final_session_id = $sessionObj->id;
				$revenue_event->order_id = $this->order_id;
				$revenue_event->positive_affected_month = date("Y-m-01", strtotime($sessionObj->session_start));
				$revenue_event->negative_affected_month = null;
				$revenue_event->insert();
			}
		}
		catch (Exception $e)
		{
			//eat it
		}
	}

	/**
	 * Overridden to do some extra validation
	 */
	function insert($ignoreAffectedRows = false)
	{

		if ((!isset($this->is_delayed_payment)) || (!$this->is_delayed_payment))
		{
			$this->delayed_payment_status = null;
		}

		$rtn = parent::insert($ignoreAffectedRows);

		return $rtn;
	}

	public static function removePaymentStoredForReference($UCF_ID, $user_id)
	{
		$retVal = array();

		$UCF = DAO_CFactory::create('user_card_reference');
		$UCF->id = $UCF_ID;
		$UCF->user_id = $user_id; // user id is for safety in case the wrong_id is passed
		$UCF->find(true);

		if ($UCF->N == 0)
		{
			return false;
		}

		$card_type = $UCF->credit_card_type;
		$card_number = $UCF->card_number;

		$UCF->delete();

		// check to if the same card exists at a different store for this user
		$testUCF = DAO_CFactory::create('user_card_reference');
		$testUCF->query("select * from user_card_reference where user_id = $user_id and card_number = '$card_number' and credit_card_type = '$card_type' and is_deleted = 0");

		while ($testUCF->fetch())
		{
			$testUCF->delete();
		}

		return true;
	}

	//Stale is a reference that is older than a year
	public static function getPaymentsStoredForReference($user_id, $store_id, $exclude_stale = false)
	{
		$retVal = array();

		$UCF = DAO_CFactory::create('user_card_reference');

		$storeClause = "";
		if ($store_id != 'all')
		{
			$storeClause = " and ucf.store_id = $store_id ";
		}

		$query = "select ucf.*, st.store_name, p.timestamp_created AS org_trans_time 
					FROM user_card_reference AS ucf
					JOIN store AS st on st.id = ucf.store_id " . $storeClause . "
    				JOIN merchant_accounts AS ma ON st.id = ma.store_id AND ucf.merchant_account_id = ma.id AND ma.is_deleted = 0
					JOIN payment AS p on p.payment_transaction_number = ucf.card_transaction_number and p.user_id = ucf.user_id
					WHERE ucf.user_id = $user_id and ucf.is_deleted = 0";

		if ($exclude_stale)
		{
			$query .= " and ucf.last_process_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR) ";
		}

		$query .= " order by ucf.last_process_date desc";

		$UCF->query($query);

		if ($UCF->N == 0)
		{
			return false;
		}

		$cardNumbers = array();
		$card_count = 0;

		while ($UCF->fetch())
		{
			if ($store_id == 'all' && in_array($UCF->card_number . $UCF->credit_card_type, $cardNumbers))
			{
				continue;
			}

			$stale = false;
			if (strtotime($UCF->org_trans_time) < time() - (365 * 86400))
			{
				$stale = true;
			}

			$retVal[$UCF->id] = array(
				'ucf_id' => $UCF->id,
				'payment_id' => $UCF->card_transaction_number,
				'cc_number' => $UCF->card_number,
				'card_type' => $UCF->credit_card_type,
				'maid' => $UCF->merchant_account_id,
				'date' => strtotime($UCF->last_process_date),
				'last_result' => $UCF->last_process_result,
				'store_id' => $UCF->store_id,
				'card_count' => ++$card_count,
				'stale' => $stale
			);

			$cardNumbers[] = $UCF->card_number . $UCF->credit_card_type;
		}

		return $retVal;
	}

	/**
	 * Finds a credit card payment that is less than a year old made by the given user
	 * If it finds one returns an array of information regarding the payment otherwise returns false
	 */
	public static function getPaymentEligibleForReference($user_id, $store_id)
	{

		$retVal = false;

		$exclusionList = array();

		$accountChangeClause = "";
		if ($store_id == 121)
		{
			$accountChangeClause = " and p.timestamp_created > '2018-05-01 14:02:30'";
		}
		else if ($store_id == 286)
		{
			$accountChangeClause = " and p.timestamp_created > '2014-10-02 17:54:16'";
		}
		else if ($store_id == 239)
		{
			$accountChangeClause = " and p.timestamp_created > '2014-12-01 19:26:12'";
		}
		else if ($store_id == 91)
		{
			$accountChangeClause = " and p.timestamp_created > '2014-12-03 17:29:58'";
		}
		else if ($store_id == 224)
		{
			$accountChangeClause = " and p.timestamp_created > '2015-03-05 20:06:00'";
		}
		else if ($store_id == 165)
		{
			$accountChangeClause = " and p.timestamp_created > '2015-04-06 15:28:01'";
		}
		else if ($store_id == 209)
		{
			$accountChangeClause = " and p.timestamp_created > '2015-06-03 12:53:00'";
		}
		else if ($store_id == 181)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-05-01 17:32:00'";
		}
		else if ($store_id == 66)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-08-01 14:10:00'";
		}
		else if ($store_id == 28)
		{
			$accountChangeClause = " and p.timestamp_created > '2015-11-30 13:46:27'";
		}
		else if ($store_id == 87)
		{
			$accountChangeClause = " and p.timestamp_created > '2015-12-02 13:16:35'";
		}
		else if ($store_id == 57)
		{
			$accountChangeClause = " and p.timestamp_created > '2016-05-03 19:44:00'";
		}
		else if ($store_id == 261)
		{
			$accountChangeClause = " and p.timestamp_created > '2016-12-30 15:09:37'";
		}
		else if ($store_id == 30)
		{
			$accountChangeClause = " and p.timestamp_created > '2017-09-07 13:34:00'";
		}
		else if ($store_id == 274)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-05-01 17:53:00'";
		}
		else if ($store_id == 67)
		{
			$accountChangeClause = " and p.timestamp_created > '2018-07-02 12:11:48'";
		}
		else if ($store_id == 61)
		{
			$accountChangeClause = " and p.timestamp_created > '2018-07-17 20:50:43'";
		}
		else if ($store_id == 122)
		{
			$accountChangeClause = " and p.timestamp_created > '2018-09-05 10:00:00'";
		}
		else if ($store_id == 158)
		{
			$accountChangeClause = " and p.timestamp_created > '2018-10-10 20:51:00'";
		}
		else if ($store_id == 133)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-01-02 14:50:50'";
		}
		else if ($store_id == 63)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-03-01 14:15:00'";
		}
		else if ($store_id == 54)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-04-01 15:50:00'";
		}
		else if ($store_id == 85)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-05-03 13:36:00'";
		}
		else if ($store_id == 80)
		{
			$accountChangeClause = " and p.timestamp_created > '2019-12-24 12:53:07'";
		}
		else if ($store_id == 102)
		{
			$accountChangeClause = " and p.timestamp_created > '2020-01-04 12:53:14'";
		}
		else if ($store_id == 180)
		{
			$accountChangeClause = " and p.timestamp_created > '2020-10-09 13:49:26'";
		}
		else if ($store_id == 96)
		{
			$accountChangeClause = " and p.timestamp_created > '2020-11-02 12:55:28'";
		}
		else if ($store_id == 99)
		{
			$accountChangeClause = " and p.timestamp_created > '2020-11-03 14:20:00'";
		}
		else if ($store_id == 166)
		{
			$accountChangeClause = " and p.timestamp_created > '2021-02-18 10:52:00'";
		}

		$paymentObj = DAO_CFactory::create('payment');

		/*
					$paymentObj->query("select payment_transaction_number, credit_card_type, payment_number, do_not_reference, timestamp_created
										from payment where store_id = $store_id and user_id = $user_id and payment_type = 'CC'
										and DATEDIFF( now(), timestamp_created ) < 365 and is_deleted = 0
										and is_delayed_payment = 0 and payment_number is not null and user_id <> created_by
							$accountChangeClause
							order by id desc");
		*/
		// CES 9/18/2015 - added clause allowing employees to use cards then enter themselves

		$paymentObj->query("select p.payment_transaction_number, p.merchant_account_id, p.credit_card_type, p.payment_number, p.do_not_reference, p.timestamp_created, p.created_by, p.user_id, u.user_type
			from payment p
			join user u on u.id = $user_id
			where p.store_id = $store_id and p.user_id = $user_id
			and p.payment_type = 'CC'
			    and p.is_deleted = 0
			    and p.is_delayed_payment = 0 and p.payment_number is not null 
				and DATEDIFF( now(), p.timestamp_created ) < 365
					$accountChangeClause
			    order by p.id desc");

		$hasStoreInput = false;

		while ($paymentObj->fetch())
		{


			if ($paymentObj->user_id != $paymentObj->created_by || $paymentObj->user_type <> CUser::CUSTOMER)
			{
				$hasStoreInput = true;
			}

			$cc_number = substr($paymentObj->payment_number, strlen($paymentObj->payment_number) - 4);
			$thisDate = strtotime($paymentObj->timestamp_created);

			if ($store_id == 200)
			{
				if ($thisDate < strtotime('2011-10-14 02:07:17'))
				{
					continue;
				}
			}

			if ($store_id == 229)
			{
				if ($thisDate < strtotime('2012-06-20 14:25:11'))
				{
					continue;
				}
			}

			if ($paymentObj->do_not_reference && !isset($exclusionList[$cc_number]))
			{
				$exclusionList[$cc_number] = $cc_number;
			}

			if (!isset($retVal[$cc_number]))
			{
				$retVal[$cc_number] = array(
					'payment_id' => $paymentObj->payment_transaction_number,
					'cc_number' => $cc_number,
					'card_type' => $paymentObj->credit_card_type,
					'maid' => $paymentObj->merchant_account_id,
					'date' => $thisDate
				);
			}
			else if ($thisDate > $retVal[$cc_number]['date'])
			{
				$retVal[$cc_number] = array(
					'payment_id' => $paymentObj->payment_transaction_number,
					'cc_number' => $cc_number,
					'card_type' => $paymentObj->credit_card_type,
					'maid' => $paymentObj->merchant_account_id,
					'date' => $thisDate
				);
			}
		}

		if (!empty($retVal))
		{
			foreach ($retVal as $cc_num => $data)
			{
				if (array_key_exists($cc_num, $exclusionList))
				{
					unset($retVal[$cc_num]);
				}
			}
		}

		if (!$hasStoreInput)
		{
			return array();
		}

		return $retVal;
	}

	public function setCCInfo($paymentNumber, $paymentMonth, $paymentYear, $name, $address, $city, $state, $zip, $ccType = false, $securityCode = false, $saveCard = false)
	{        // TAG: DAVIDB (securityCode)

		$this->payment_type = CPayment::CC;
		$this->ccNumber = $paymentNumber;
		$this->ccMonth = $paymentMonth;
		$this->ccYear = $paymentYear;
		$this->ccName = $name;
		$this->ccAddress = $address;
		$this->ccCity = $city;
		$this->ccState = $state;
		$this->ccZip = $zip;
		$this->ccSecurityCode = $securityCode;        // TAG: DAVIDB (new line)
		if (!empty($paymentNumber))
		{
			$this->payment_number = @str_repeat('X', (strlen($paymentNumber) - 4)) . substr($paymentNumber, -4);
		}
		$this->credit_card_type = $ccType;
		$this->save_card_on_completion = $saveCard;
	}

	public function updateUserCardReference($result, $refNum)
	{

		if ($result == 'success')
		{
			$UCRUpdater = new DAO();
			$UCRUpdater->query("update user_card_reference set card_transaction_number = '{$this->payment_transaction_number}',
    		last_process_date = now(), last_process_result = 'success' where user_id = {$this->user_id} and is_deleted = 0 and card_transaction_number = '$refNum'");
		}
		else
		{

			// Need to update the user_card_reference table, the transaction ID and last used date
			$UCRUpdater = new DAO();
			$result_text = addslashes($this->verisignExtendedRslt) . "\n" . $result['userText'];
			$UCRUpdater->query("update user_card_reference set last_process_date = now(), last_process_result = '$result_text' where user_id = {$this->user_id} and is_deleted = 0 and card_transaction_number = '$refNum'");
		}
	}

	/**
	 * Processes the a credit or debit using the passed in reference number.
	 * Inserts "$this" if successful
	 * @return $rslt
	 */
	public function processByReference($CustomerObj, $OrderObj, $StoreObj, $refNum, $doInsertPayment = true)
	{
		if ($this->payment_type == CPayment::CC)
		{


			$retval = array(
				'result' => 'none',
				'userText' => ''
			);

			$process = null;

			require_once 'includes/payment/PayPalProcess.php';
			$process = new PayPalProcess();

			if ($this->total_amount > 0)
			{
				$trxtype = 'S';
			} //sale
			else
			{
				$trxtype = 'C'; //credit
				$this->total_amount = $this->total_amount * -1;
				$this->payment_type = CPayment::REFUND;
			}

			$initialAmount = $this->total_amount;
			if ($this->is_delayed_payment)
			{

				if ($this->is_delayed_payment == 5)
				{
					$initialAmount = 20;
				}
				else if ($this->is_delayed_payment == 4)
				{
					$initialAmount = $StoreObj->default_delayed_payment_deposit;
				}
				else if ($this->is_delayed_payment == 3)
				{
					$initialAmount = 10;
				}
				else if ($this->is_delayed_payment == 2)
				{
					$initialAmount = 1;
				}
				else
				{
					$initialAmount = self::calculateDeposit($this->total_amount);
				}

				$secondAmount = $this->total_amount - $initialAmount;
				$this->is_deposit = true;
			}

			$this->total_amount = $initialAmount;

			$rslt = $process->processPayment($CustomerObj, $OrderObj->id, $StoreObj->franchise_id, $StoreObj->id, false, $this->total_amount, false, false, false, false, false, false, $trxtype, $refNum);

			if ($rslt == 'success')
			{
				//insert payment record
				$this->payment_transaction_number = $process->getPNRef();
				$this->merchant_account_id = $process->getMerchantAccountId();
				$this->payment_system = $process->getSystemName();

				if ($doInsertPayment)
				{
					$this->insert();
				}

				if ($this->is_delayed_payment)
				{
					$this->is_delayed_payment = 0;

					$PendingPayment = clone($this);
					$PendingPayment->is_delayed_payment = 1;
					$PendingPayment->total_amount = $secondAmount;
					$PendingPayment->delayed_payment_status = CPayment::PENDING;
					$PendingPayment->is_deposit = false;
					$this->PendingPayment = $PendingPayment;
				}

				// Need to update the user_card_reference table, the transaction ID and last used date
				// Note: Failue cannot be recorded here since the Rollback will undo the update.  Capture the failure in the client code.
				$this->updateUserCardReference($rslt, $refNum);

				$retval['result'] = 'success';

				return $retval;
			}

			if ($rslt == 'transactionDecline')
			{
				CLog::Record(print_r($process->getResult(), true));
				$this->verisignExtendedRslt = 'Transaction Decline ' . $rslt . ' request: ' . implode(',', $process->getRequest()) . ' result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
			}
			else
			{
				$this->verisignExtendedRslt = 'Transaction error: ' . $rslt . 'result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
			}

			$retval['result'] = $rslt;
			$retval['userText'] = $process->getUsersExplanation();

			return $retval;
		}
		else
		{
			$retval['result'] = 'incorrect_payment_type';

			return $retval;
		}
	}

	/**
	 * Processes the card number, CVV2 and amount
	 * and if successful stores the amount as store credit for the given user
	 * @return 'illegal_amount', 'success', 'communicationError', 'transactionDecline',
	 *  'noConnection', 'configurationError', 'noMerchantAccountFound', 'storeCreditCreateFailed'
	 * and the new store credit id if successful
	 */
	public static function processPaymentToStoreCredit($cc_number, $cvv2_number, $amount, $ccMonth, $ccYear, $CustomerObj, $StoreObj)
	{


		$newStoreCreditID = null;

		if ($amount <= 0)
		{
			return array(
				'result' => 'illegal_amount',
				'newStoreCreditID' => null,
				'userText' => "The amount submitted was illegal"
			);
		}

		$process = null;

		require_once 'includes/payment/PayPalProcess.php';
		$process = new PayPalProcess();

		$rslt = $process->processPayment($CustomerObj, false, $StoreObj->franchise_id, $StoreObj->id, false, $amount, $cc_number, $ccMonth, $ccYear, $cvv2_number, false, false, 'S');

		if ($rslt == 'success')
		{

			try
			{
				$storeCredit = DAO_CFactory::create('store_credit');
				$storeCredit->amount = $amount;
				$storeCredit->store_id = $StoreObj->id;
				$storeCredit->user_id = $CustomerObj->id;
				$storeCredit->credit_card_number = substr($cc_number, strlen($cc_number) - 4);
				$storeCredit->payment_transaction_number = $process->getPNRef();

				$rslt = $storeCredit->insert();
				if (!$rslt)
				{
					CLog::RecordNew(CLog::ERROR, "Successful Gift Card Redemption but insert of store credit failed for customer: " . $CustomerObj->id, "", "", true);

					return array(
						'result' => 'storeCreditCreateFailed',
						'newStoreCreditID' => null,
						'userText' => ""
					);
				}

				$newStoreCreditID = $storeCredit->id;
			}
			catch (Exception $e)
			{
				CLog::RecordNew(CLog::ERROR, "Successful Gift Card Redemption but insert of store credit failed for customer: " . $CustomerObj->id, "", "", true);

				return array(
					'result' => 'storeCreditCreateFailed',
					'newStoreCreditID' => null,
					'userText' => ""
				);
			}

			$retval = array(
				'result' => 'success',
				'newStoreCreditID' => $newStoreCreditID,
				'userText' => ""
			);

			return $retval;
		}

		return array(
			'result' => $rslt,
			'newStoreCreditID' => null,
			'userText' => $process->getUsersExplanation()
		);
	}

	/**
	 * Used when refunding a cancelled order that contains a Debit Gift Card Payment
	 */
	public function convertDebitGiftCardPaymentToStoreCredit($order_id)
	{
		if ($this->payment_type !== self::GIFT_CARD)
		{
			return 'Invalid payment type';
		}

		if ($this->total_amount <= 0)
		{
			return "The amount submitted was illegal";
		}

		try
		{
			$storeCredit = DAO_CFactory::create('store_credit');
			$storeCredit->amount = $this->total_amount;
			$storeCredit->store_id = $this->store_id;
			$storeCredit->user_id = $this->user_id;
			$storeCredit->credit_card_number = substr($this->payment_number, strlen($this->payment_number) - 4);
			$storeCredit->payment_transaction_number = $this->payment_transaction_number;
			$storeCredit->credit_type = 1;

			$rslt = $storeCredit->insert();
			if (!$rslt)
			{
				return 'Operation failed: There was a problem inserting the new store credit';
			}

			// also add a payment that reflects this refund
			$refundLine = DAO_CFactory::create('payment');

			$refundLine->user_id = $this->user_id;
			$refundLine->store_id = $this->store_id;
			$refundLine->order_id = $order_id;
			$refundLine->is_delayed_payment = 0;
			$refundLine->is_escrip_customer = 0;
			$refundLine->total_amount = $this->total_amount;
			$refundLine->payment_number = substr($this->payment_number, strlen($this->payment_number) - 4);
			$refundLine->payment_type = CPayment::REFUND_GIFT_CARD;
			$refundLine->insert();
		}
		catch (Exception $e)
		{
			return 'Operation failed: There was a problem inserting the new store credit';
		}

		return 'The Gift Card Payment was converted to store credit.';
	}

	/**
	 * Processes the initial credit card payment
	 * @return $rslt
	 */
	public function processPayment($CustomerObj, $OrderObj, $StoreObj)
	{

		$retval = array(
			'result' => 'none',
			'userText' => ''
		);

		if ($this->payment_type == CPayment::CC)
		{
			$process = null;

			require_once 'includes/payment/PayPalProcess.php';
			$process = new PayPalProcess();

			if (!empty($StoreObj->store_type) && $StoreObj->store_type == CStore::DISTRIBUTION_CENTER)
			{
				$process->setIsDeliveredPayment();
			}

			$trxtype = 'S'; //sale
			//				if ( $this->is_delayed_payment )
			//					$trxtype = 'A'; //authorize only
			//
			$initialAmount = $this->total_amount;
			if ($this->is_delayed_payment)
			{


				if ($this->is_delayed_payment == 5)
				{
					$initialAmount = 20;
				}
				else if ($this->is_delayed_payment == 4)
				{
					$initialAmount = $StoreObj->default_delayed_payment_deposit;
				}
				else if ($this->is_delayed_payment == 3)
				{
					$initialAmount = 10;
				}
				else if ($this->is_delayed_payment == 2)
				{
					$initialAmount = 1;
				}
				else
				{
					$initialAmount = self::calculateDeposit($this->total_amount);
				}

				$secondAmount = $this->total_amount - $initialAmount;
				$this->is_deposit = true;
			}

			$this->total_amount = $initialAmount;

			$rslt = $process->processPayment($CustomerObj, $OrderObj->id, $StoreObj->franchise_id, $StoreObj->id, $this->ccName, $this->total_amount, $this->ccNumber, $this->ccMonth, $this->ccYear, $this->ccSecurityCode,        // TAG: DAVIDB (used to be false)
				$this->ccZip, $this->ccAddress, $trxtype);

			if ($rslt == 'success')
			{
				//insert payment record
				$this->order_id = $OrderObj->id;
				$this->payment_transaction_number = $process->getPNRef();
				$this->merchant_account_id = $process->getMerchantAccountId();
				$this->payment_system = $process->getSystemName();

				if ($this->is_delayed_payment)
				{
					$this->is_delayed_payment = 0;

					$PendingPayment = clone($this);
					$PendingPayment->is_delayed_payment = 1;
					$PendingPayment->total_amount = $secondAmount;
					$PendingPayment->delayed_payment_status = CPayment::PENDING;
					$PendingPayment->is_deposit = false;
					$this->PendingPayment = $PendingPayment;
				}

				if ($this->save_card_on_completion)
				{
					$UCR = DAO_CFactory::create('user_card_reference');
					$UCR->card_number = substr($this->ccNumber, strlen($this->ccNumber) - 4);
					$UCR->credit_card_type = $this->credit_card_type;
					$UCR->store_id = $this->store_id;
					$UCR->user_id = $CustomerObj->id;
					$UCR->merchant_account_id = $process->getMerchantAccountId();

					if ($UCR->find(true))
					{
						$UCRClone = clone($UCR);
						$UCR->card_transaction_number = $process->getPNRef();
						$UCR->last_process_date = date("Y-m-d H:i:s");
						$UCR->last_process_result = 0;
						$UCR->update($UCRClone);
					}
					else
					{
						$UCR->card_transaction_number = $process->getPNRef();
						$UCR->last_process_date = date("Y-m-d H:i:s");
						$UCR->last_process_result = 0;
						$UCR->insert();
					}
				}

				$retval['result'] = 'success';

				return $retval;
			}

			if ($rslt == 'transactionDecline')
			{
				CLog::Record(print_r($process->getResult(), true));
				$this->verisignExtendedRslt = 'Transaction Decline ' . $rslt . ' request: ' . implode(',', $process->getRequest()) . ' result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
			}
			else
			{
				$this->verisignExtendedRslt = 'Transaction error: ' . $rslt . 'result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
			}

			$retval['result'] = $rslt;
			$retval['userText'] = $process->getUsersExplanation();

			return $retval;
		}
		else
		{
			$retval['result'] = 'success';

			return $retval;
		}
	}

	/**
	 * Processes a new credit card credit - the amount to credit is passed in.
	 * The CC# is required since this method will not reference a previous transaction
	 * @return $rslt
	 */
	public function processNewCredit($CustomerObj, $OrderObj, $StoreObj)
	{

		$retval = array(
			'result' => 'none',
			'userText' => ''
		);

		if ($this->payment_type == CPayment::CC)
		{

			$process = null;

			require_once 'includes/payment/PayPalProcess.php';
			$process = new PayPalProcess();

			$trxtype = 'C'; //sale

			if ($this->total_amount <= 0)
			{
				throw new Exception("processNewCredit requires total_amount to be a positive number.");
			}

			$rslt = $process->processPayment($CustomerObj, $OrderObj->id, $StoreObj->franchise_id, $StoreObj->id, $this->ccName, $this->total_amount, $this->ccNumber, $this->ccMonth, $this->ccYear, false, $this->ccZip, $this->ccAddress, $trxtype);

			if ($rslt == 'success')
			{
				//insert payment record
				$this->order_id = $OrderObj->id;
				$this->payment_transaction_number = $process->getPNRef();
				$this->payment_type = CPayment::REFUND;
				$this->payment_note = null;
				$this->admin_note = null;
				$this->is_deposit = false;
				$this->is_delayed_payment = false;
				$this->delayed_payment_transaction_date = null;
				$this->delayed_payment_transaction_number = null;
				$this->merchant_account_id = $process->getMerchantAccountId();
				$this->payment_system = $process->getSystemName();

				$this->insert();

				$retval['result'] = 'success';

				return $retval;
			}

			if ($rslt == 'transactionDecline')
			{
				$this->verisignExtendedRslt = 'Transaction Decline ' . $rslt . ' request: ' . implode(',', $process->getRequest()) . ' result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
			}
			else
			{
				$this->verisignExtendedRslt = 'Transaction error: ' . $rslt . 'result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
			}

			$retval['result'] = $rslt;
			$retval['userText'] = $process->getUsersExplanation();

			return $retval;
		}
		else
		{
			$retval['result'] = 'success';

			return $retval;
		}
	}

	public function processStoreCreditCancellation($sessionObj)
	{


		$success = false;
		$storeCreditDAO = DAO_CFactory::create('store_credit');
		$storeCreditDAO->credit_card_number = $this->payment_number;
		$storeCreditDAO->payment_transaction_number = $this->payment_transaction_number;
		$storeCreditDAO->amount = $this->total_amount;
		$storeCreditDAO->user_id = $this->user_id;
		$storeCreditDAO->store_id = $this->store_id;

		$originalCredit = DAO_CFactory::create('store_credit');
		$originalCredit->id = $this->store_credit_id;

		$storeCreditType = 1;
		if ($originalCredit->find(true))
		{
			$storeCreditType = $originalCredit->credit_type;
		}

		$storeCreditDAO->credit_type = $storeCreditType;

		$rslt = $storeCreditDAO->insert();
		if (!$rslt)
		{
			throw new Exception ('Store Credit Insert Failed');
		}

		$CreditObj = clone($this);
		$CreditObj->payment_type = CPayment::REFUND_STORE_CREDIT;

		// SHOULD WE LEAVE THE TRANSACTION NUMBER NULL?  DO WE CARE AT THIS POINT
		$CreditObj->payment_transaction_number = $this->payment_transaction_number;

		$CreditObj->payment_note = null;
		$CreditObj->admin_note = null;
		$CreditObj->is_deposit = false;
		$CreditObj->is_delayed_payment = false;
		$CreditObj->delayed_payment_transaction_date = null;
		$CreditObj->delayed_payment_transaction_number = null;
		$rslt = $CreditObj->insert();
		if ($rslt > 0)
		{
			$success = 'success';
		}

		return $success;
	}

	/**
	 * Processes a credit card credit by referencing the original cc payment and credit the entire amount
	 * @return $rslt
	 */
	public function processCredit($amount = false)
	{

		$retval = array(
			'result' => 'none',
			'userText' => ''
		);

		if ($this->payment_type == CPayment::CC && $this->payment_transaction_number)
		{
			if (!$this->is_delayed_payment || ($this->delayed_payment_transaction_date && $this->delayed_payment_transaction_number))
			{

				//get customer, store
				$CustomerObj = DAO_CFactory::create('user');
				$CustomerObj->id = $this->user_id;
				if ($CustomerObj->find(true) !== 1)
				{
					throw new Exception ('user id not set for processCredit');
				}

				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->selectAdd();
				$StoreObj->selectAdd('id, franchise_id');
				$StoreObj->id = $this->store_id;
				if ($StoreObj->find(true) !== 1)
				{
					throw new Exception ('store id not set for processCredit');
				}

				$process = null;

				require_once 'includes/payment/PayPalProcess.php';
				$process = new PayPalProcess();

				$trxtype = 'C'; // credit

				if ($this->is_delayed_payment)
				{
					$origid = $this->delayed_payment_transaction_number;
				}
				else
				{
					$origid = $this->payment_transaction_number;
				}

				$amountToCredit = $this->total_amount;
				if ($amount)
				{
					$amountToCredit = ($amount <= $this->total_amount && $amount > 0) ? $amount : $this->total_amount;
				}

				$rslt = $process->processPayment($CustomerObj, $this->order_id, $StoreObj->franchise_id, $StoreObj->id, null, $amountToCredit, null, null, null, null, null, null, $trxtype, $origid);

				if ($rslt == 'success')
				{

					$CreditObj = clone($this);
					$CreditObj->payment_type = CPayment::REFUND;
					$CreditObj->payment_transaction_number = $process->getPNRef();
					$CreditObj->total_amount = $amountToCredit;
					$CreditObj->payment_note = null;
					$CreditObj->admin_note = null;
					$CreditObj->is_deposit = false;
					$CreditObj->referent_id = $this->id;
					$CreditObj->is_delayed_payment = false;
					$CreditObj->delayed_payment_transaction_date = null;
					$CreditObj->delayed_payment_transaction_number = null;
					$CreditObj->merchant_account_id = $process->getMerchantAccountId();
					$CreditObj->payment_system = $process->getSystemName();

					$CreditObj->insert();
					// TODO: Do we send an email for a credit?
					/*

					try {


						$OrderObj = DAO_CFactory::create('orders');
						$OrderObj->id = $this->order_id;
						$OrderObj->find(true);

						//send confirmation
						COrders::sendConfirmationEmail($CustomerObj, $OrderObj, true);
					} catch (exception $e) {
						CLog::RecordException($e);
					}
					*/
					$retval['userText'] = 'Success';
					$retval['result'] = 'success';

					return $retval;
				}

				if ($rslt == 'transactionDecline')
				{
					$this->verisignExtendedRslt = 'Transaction Decline request: ' . implode(',', $process->getRequest()) . ' result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
				}
				else
				{
					$this->verisignExtendedRslt = 'Transaction error: result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
				}

				$retval['result'] = $rslt;
				$retval['userText'] = $process->getUsersExplanation();

				return $retval;
			}
			else
			{
				$retval['userText'] = 'A pending Delayed Payment does not require a credit. (It can just be canceled)';
				$retval['result'] = 'pending_delayed_payment_no_credit_required';

				return $retval;
			}
		}
		else
		{
			$retval['userText'] = 'The Payment type was invalide or the original transaction number was invalid.';
			$retval['result'] = 'invalid_payment_type_or_transaction_number';

			return $retval;
		}
	}

	/**
	 * New method of delaying payment. Use the ref num returned by the original deposit on the order to
	 * remunerate the remainder
	 * returns 'success', 'already payed or invalid reference id', 'transactionDecline'
	 */
	function processDelayedPayment($transactionDetails = false)
	{
		if ($this->payment_type == CPayment::CC && $this->is_delayed_payment && $this->delayed_payment_status == CPayment::PENDING)
		{
			if ($this->payment_transaction_number && (!$this->delayed_payment_transaction_number) && (!$this->delayed_payment_transaction_date))
			{

				//get customer, store
				$CustomerObj = DAO_CFactory::create('user');
				$CustomerObj->id = $this->user_id;
				if ($CustomerObj->find(true) !== 1)
				{
					throw new Exception ('user id not set for delayed payment');
				}

				$StoreObj = DAO_CFactory::create('store');
				$StoreObj->selectAdd();
				$StoreObj->selectAdd('id, franchise_id');
				$StoreObj->id = $this->store_id;
				if ($StoreObj->find(true) !== 1)
				{
					throw new Exception ('store id not set for delayed payment');
				}

				$process = null;

				require_once 'includes/payment/PayPalProcess.php';
				$process = new PayPalProcess();

				$trxtype = 'S'; //sale

				$origid = $this->payment_transaction_number;

				$rslt = $process->processPayment($CustomerObj, $this->order_id, $StoreObj->franchise_id, $StoreObj->id, null, $this->total_amount, null, null, null, null, null, null, $trxtype, $origid);

				if ($rslt == 'success')
				{
					$this->delayed_payment_transaction_number = $process->getPNRef();
					$this->delayed_payment_transaction_date = DAO::now();
					$this->delayed_payment_status = CPayment::SUCCESS;
					$this->merchant_account_id = $process->getMerchantAccountId();
					$this->payment_system = $process->getSystemName();

					$this->update();

					try
					{
						$OrderObj = DAO_CFactory::create('orders');
						$OrderObj->id = $this->order_id;
						$OrderObj->find(true);

						COrdersDigest::handleDelayedPaymentSuccess($this->order_id, $OrderObj->grand_total);

						//COrders::sendDelayedPaymentEmail($CustomerObj, $OrderObj);
					}
					catch (exception $e)
					{
						CLog::RecordException($e);
					}

					if ($transactionDetails)
					{
						$transactionDetails = array();
						$transactionDetails['success'] = true;
						$transactionDetails['payment_id'] = $this->id;
						$transactionDetails['transaction_id'] = $this->delayed_payment_transaction_number;
						$transactionDetails['transaction_date'] = $this->delayed_payment_transaction_date;
						$transactionDetails['summary_date'] = date("m.d.Y", strtotime($this->delayed_payment_transaction_date));

						return $transactionDetails;
					}
					else
					{
						return $rslt;
					}
				}

				if ($rslt == 'transactionDecline')
				{
					// all is well with the system but the payment was refused.
					// send an email to both the fadmin and customer

					$OrderObj = DAO_CFactory::create('orders');
					$OrderObj->id = $this->order_id;
					$OrderObj->find(true);

					$this->delayed_payment_transaction_date = DAO::now();
					$this->delayed_payment_status = CPayment::FAIL;
					$this->update();

					$this->verisignExtendedRslt = 'Transaction Decline request: ' . implode(',', $process->getRequest()) . ' result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());

					$shortExplanation = $process->getResponseMessage() . " -- " . $process->getUsersExplanation();

					CLog::RecordNew(CLog::ERROR, 'Delayed Payment Failure: ' . $shortExplanation, $process->getResult(), "", false);

					//send explanation
					COrders::sendPaymentDeclinedEmail($CustomerObj, $OrderObj, $shortExplanation, true);
				}
				else
				{
					// the payment failed due to a system issue, email the fadmin only

					$OrderObj = DAO_CFactory::create('orders');
					$OrderObj->id = $this->order_id;
					$OrderObj->find(true);

					$this->delayed_payment_transaction_date = DAO::now();
					$this->delayed_payment_status = CPayment::FAIL;
					$this->update();

					$this->verisignExtendedRslt = 'Transaction error: result: ' . (is_array($process->getResult()) ? implode(',', $process->getResult()) : $process->getResult());
					$shortExplanation = $process->getResponseMessage();
					//send explanation
					COrders::sendPaymentDeclinedEmail($CustomerObj, $OrderObj, $shortExplanation, false);
				}

				return $rslt;
			}
			else
			{
				return 'already payed or invalid origination id';
			}
		}
		else
		{
			return 'success';
		}
	}

	/**
	 * $preprocess == true does not check all required fk, it only verifies the payment (cc#) prior to actually placing the order
	 */
	function validate($preprocess = false)
	{
		if (!$preprocess)
		{
			parent::validate();
		}

		switch ($this->payment_type)
		{
			case self::GIFT_CERT:
				if (empty($this->gift_certificate_number))
				{
					throw new Exception('gift certificate number required');
				}
				break;

			case self::CC:
				if ($this->ccNumber)
				{
					$rslt = self::validateCC($this->credit_card_type, $this->ccNumber, $this->ccMonth, $this->ccYear);
					if ($rslt !== 0)
					{
						throw new Exception($rslt);
					}
				}
				break;
		}

		return;
	}

	/**
	 * Validate a credit card number, taken from the Zen Cart code
	 *
	 * @return an error code string or 0
	 * 'invalidtype', 'invalidmonth', 'invalidyear', 'expired', 'invalidnumber'
	 */
	static function validateCC($cc_type, $cc_number, $expiry_m, $expiry_y, $CSC = null)
	{

		$cc_number = preg_replace('/[^0-9]/', '', $cc_number);

		if (preg_match('/^4[0-9]{12}([0-9]{3})?$/', $cc_number) && ($cc_type == self::VISA))
		{
		}
		else if (preg_match('/^5[1-5][0-9]{14}$/', $cc_number) && ($cc_type == self::MASTERCARD))
		{
		}
		else if (preg_match('/^3[47][0-9]{13}$/', $cc_number) && ($cc_type == self::AMERICANEXPRESS))
		{
		}
		else if (preg_match('/^6011[0-9]{12}$/', $cc_number) && ($cc_type == self::DISCOVERCARD))
		{
			//      } elseif (preg_match('/^3(0[0-5]|[68][0-9])[0-9]{11}$/', $cc_number) && CC_ENABLED_DINERS_CLUB=='1') {
			//        $this->cc_type = 'Diners Club';
			//      } elseif (preg_match('/^(3[0-9]{4}|2131|1800)[0-9]{11}$/', $cc_number) && CC_ENABLED_JCB=='1') {
			//        $this->cc_type = 'JCB';
			//      } elseif (preg_match('/^5610[0-9]{12}$/', $cc_number) && CC_ENABLED_AUSTRALIAN_BANKCARD=='1') {
			//        $this->cc_type = 'Australian BankCard';
		}
		else
		{
			return 'invalidtype';
		}

		if (is_numeric($expiry_m) && ($expiry_m > 0) && ($expiry_m < 13))
		{
		}
		else
		{
			return 'invalidmonth';
		}

		$current_year = date('Y');
		$expiry_y = substr($current_year, 0, 2) . $expiry_y;
		if (is_numeric($expiry_y) && ($expiry_y >= $current_year) && ($expiry_y <= ($current_year + 10)))
		{
		}
		else
		{
			return 'invalidyear';
		}

		if ($expiry_y == $current_year)
		{
			if ($expiry_m < date('n'))
			{
				return 'expired';
			}
		}

		if ($CSC != null)
		{
			if (!is_numeric($CSC))
			{
				return 'invalidCSC';
			}

			if ($cc_type == self::AMERICANEXPRESS)
			{
				if (strlen($CSC) != 4)
				{
					return 'invalidCSC';
				}
			}
			else
			{
				if (strlen($CSC) != 3)
				{
					return 'invalidCSC';
				}
			}
		}

		$cardNumber = strrev($cc_number);
		$numSum = 0;

		for ($i = 0; $i < strlen($cardNumber); $i++)
		{
			$currentNum = substr($cardNumber, $i, 1);

			// Double every second digit
			if ($i % 2 == 1)
			{
				$currentNum *= 2;
			}

			// Add digits of 2-digit numbers together
			if ($currentNum > 9)
			{
				$firstNum = $currentNum % 10;
				$secondNum = ($currentNum - $firstNum) / 10;
				$currentNum = $firstNum + $secondNum;
			}

			$numSum += $currentNum;
		}

		// If the total has no remainder it's OK
		if ($numSum % 10 == 0)
		{
			return 0;
		}
		else
		{
			return 'invalidnumber';
		}
	}

	static function calculateDeposit($total)
	{
		return COrders::std_round($total * 25 / 100);
	}

	function toCondensedArray()
	{

		$dataArr = array();

		if (isset($this->user_id))
		{
			$dataArr['user_id'] = $this->user_id;
		}
		if (isset($this->store_id))
		{
			$dataArr['store_id'] = $this->store_id;
		}
		if (isset($this->payment_type))
		{
			$dataArr['payment_type'] = $this->payment_type;
		}
		if (isset($this->gift_certificate_number))
		{
			$dataArr['gift_certificate_number'] = $this->gift_certificate_number;
		}
		if (isset($this->gift_certificate_type))
		{
			$dataArr['gift_certificate_type'] = $this->gift_certificate_type;
		}
		if (isset($this->credit_card_type))
		{
			$dataArr['credit_card_type'] = $this->credit_card_type;
		}
		if (isset($this->payment_note))
		{
			$dataArr['payment_note'] = $this->payment_note;
		}
		if (isset($this->admin_note))
		{
			$dataArr['admin_note'] = $this->admin_note;
		}
		if (isset($this->total_amount))
		{
			$dataArr['total_amount'] = $this->total_amount;
		}
		if (isset($this->is_delayed_payment))
		{
			$dataArr['is_delayed_payment'] = $this->is_delayed_payment;
		}
		if (isset($this->is_deposit))
		{
			$dataArr['is_deposit'] = $this->is_deposit;
		}

		if (isset($this->payment_type) && $this->payment_type == "CC")
		{
			$tempData = array();

			if (isset($this->ccNumber))
			{
				$tempData['ccNumber'] = $this->ccNumber;
			}
			if (isset($this->ccMonth))
			{
				$tempData['ccMonth'] = $this->ccMonth;
			}
			if (isset($this->ccYear))
			{
				$tempData['ccYear'] = $this->ccYear;
			}
			if (isset($this->ccName))
			{
				$tempData['ccName'] = $this->ccName;
			}
			if (isset($this->ccAddress))
			{
				$tempData['ccAddress'] = $this->ccAddress;
			}
			if (isset($this->ccCity))
			{
				$tempData['ccCity'] = $this->ccCity;
			}
			if (isset($this->ccState))
			{
				$tempData['ccState'] = $this->ccState;
			}
			if (isset($this->ccZip))
			{
				$tempData['ccZip'] = $this->ccZip;
			}
			if (isset($this->ccSecurityCode))
			{
				$tempData['ccSecurityCode'] = $this->ccSecurityCode;
			}

			if (empty($tempData))
			{
				$tempData = false;
			}
		}
		else
		{
			if (isset($this->payment_number))
			{
				$dataArr['payment_number'] = $this->payment_number;
			}
		}

		return array(
			$dataArr,
			$tempData
		);
	}

}

?>