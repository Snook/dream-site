<?php

require_once 'DAO/Product_payment.php';
require_once 'DAO/BusinessObject/CUser.php';


class CProductPayment extends DAO_Product_payment
{

	const CHECK = 'CHECK';
	const CASH = 'CASH';
	const GIFT_CERT = 'GIFT_CERT';
	const GIFT_CARD = 'GIFT_CARD';
	const CC = 'CC';
	const REFERENCE = 'REFERENCE';
	const OTHER = 'OTHER';
	const CREDIT = 'CREDIT';
	const REFUND = 'REFUND';
	const REFUND_CASH = 'REFUND_CASH';
	const STORE_CREDIT = 'STORE_CREDIT';
	const REFUND_STORE_CREDIT = 'REFUND_STORE_CREDIT';
	const REFUND_GIFT_CARD = 'REFUND_GIFT_CARD';

	const VISA = 'Visa';
	const MASTERCARD = 'Mastercard';
	const DISCOVERCARD = 'Discover';
	const AMERICANEXPRESS = 'American Express';

	const PENDING = 'PENDING';
	const SUCCESS = 'SUCCESS';
	const FAIL = 'FAIL';
	const CANCELLED = 'CANCELLED';

	//transient properties, not stored in db
	private $ccNumber = null;
	private $ccMonth = null;
	private $ccYear = null;
	private $ccName = null;
	private $ccAddress = null;
	private $ccCity = null;
	private $ccState = null;
	private $ccZip = null;
	private $ccSecurityCode = null;

	public $verisignExtendedRslt = null;
	public $referent_id = null;

	function __construct()
	{
		parent::__construct();
	}

	function parseValidatePrepare($paymentDataArr, $user_id, $store_id, $amount)
	{
		$this->referent_id = false;

		if ($amount == 0)
		{
			$this->payment_type = CProductPayment::CREDIT;
			$this->total_amount = 0;

			if (!empty($store_id) && is_numeric($store_id))
			{
				$this->store_id = $store_id;
			}
			else
			{
				return array(
					'success' => false,
					'human_readable_error' => "Invalid store id."
				);
			}

			if (!empty($user_id) && is_numeric($user_id))
			{
				$this->user_id = $user_id;
			}
			else
			{
				return array(
					'success' => false,
					'human_readable_error' => "Invalid user id"
				);
			}

			return array('success' => true, 'human_readable_error' => "Success");

		}
		else
		{
			// payment type
			if (isset($paymentDataArr['payment_type']))
			{
				switch ($paymentDataArr['payment_type'])
				{
					case 'newcc':
						$this->payment_type = CProductPayment::CC;
						break;
					case 'check':
						$this->payment_type = CProductPayment::CHECK;
						break;
					case 'cash':
						$this->payment_type = CProductPayment::CASH;
						break;
					default:
						if (strpos($paymentDataArr['payment_type'], 'REF_') === 0)
						{
							// reference
							$this->payment_type = CProductPayment::CC;
							$this->referent_id = substr($paymentDataArr['payment_type'], 4);
							break;
						}

						return array(
							'success' => false,
							'human_readable_error' => "Unexpected Error"
						);
				}
			}
			else
			{
				return array(
					'success' => false,
					'human_readable_error' => "Unexpected Error"
				);
			}

			if (!empty($store_id) && is_numeric($store_id))
			{
				$this->store_id = $store_id;
			}
			else
			{
				return array(
					'success' => false,
					'human_readable_error' => "Invalid store id."
				);
			}

			if (!empty($user_id) && is_numeric($user_id))
			{
				$this->user_id = $user_id;
			}
			else
			{
				return array(
					'success' => false,
					'human_readable_error' => "Invalid user id"
				);
			}

			if (is_numeric($amount) && $amount > 0)
			{
				$this->total_amount = $amount;
			}
			else
			{
				return array(
					'success' => false,
					'human_readable_error' => "Invalid amount."
				);
			}


			if ($this->payment_type == self::CC)
			{
				if ($this->referent_id)
				{
					return array('success' => true, 'human_readable_error' => "Success");
				}
				else
				{
					$ccNumber = str_replace(" ", "", $paymentDataArr['ccNumber']);

					$validationResult = CProductPayment::validateCC($paymentDataArr['ccType'], $ccNumber, $paymentDataArr['ccMonth'], $paymentDataArr['ccYear'], $paymentDataArr['ccSecurityCode']);

					if ($validationResult === 0)
					{
						$this->setCCInfo($ccNumber, $paymentDataArr['ccMonth'], $paymentDataArr['ccYear'], $paymentDataArr['ccNameOnCard'],$paymentDataArr['billing_address'], null, null, $paymentDataArr['billing_postal_code'],
							$paymentDataArr['ccType'], (empty($paymentDataArr['ccSecurityCode']) ? false : $paymentDataArr['ccSecurityCode']));

						return array('success' => true, 'human_readable_error' => "Success");
					}
					else
					{
						return array('success' => false, 'human_readable_error' => "Problem with Cardholder data: " . $this->getHumanReadableValidationResultText($validationResult));
					}
				}
			}
			else if ($this->payment_type == self::CHECK)
			{
					// check number
				return array('success' => true, 'human_readable_error' => "Success");

			}
			else if ($this->payment_type == self::CASH)
			{
				// nothing further needed
				return array('success' => true, 'human_readable_error' => "Success");
			}
			else
			{
				throw new Exception("Impossible payment type specified.");
			}

		}

	}


	function getHumanReadableValidationResultText($inStr)
	{
		switch ($inStr)
		{
			case 'invalidtype':
				return "The Card type is invalid. The number may be incorrect or the wrong card type may be selected.";
			case 'invalidmonth':
				return "The Expiration date month is invalid.";
			case 'invalidyear':
				return "The Expiration date year is invalid.";
			case 'expired':
				return "The card is expired of the expiration date is incorrect,";
			case 'invalidnumber':
				return "Card number is invalid.";
			default:
				return $inStr;
		}

	}

	function process($productOrder)
	{

		if ($this->payment_type == self::CC)
		{
			$UserObj = DAO_CFactory::create('user');
			$UserObj->id = $this->user_id;
			$UserObj->find(true);

			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->id = $this->store_id;
			$StoreObj->find(true);

			if ($this->referent_id)
			{
				$rslt = $this->processByReference($UserObj, $productOrder, $StoreObj, $this->referent_id, true);
				return $rslt;
			}
			else
			{
				$rslt = $this->processPayment($UserObj, $StoreObj, $productOrder);
				return $rslt;
			}
		}
		else if ($this->payment_type == self::CHECK)
		{
			$this->product_orders_id = $productOrder->id;
			$this->payment_number = $_POST['payment_number'];
			$this->insert();
			return array('success' => true, 'human_readable_error' => 'Success');
		}
		else if ($this->payment_type == self::CASH)
		{
			$this->product_orders_id = $productOrder->id;
			$this->insert();
			return array('success' => true, 'human_readable_error' => 'Success');
		}
		else if ($this->payment_type == self::CREDIT)
		{
			$this->product_orders_id = $productOrder->id;
			$this->insert();
			return array('success' => true, 'human_readable_error' => 'Success');
		}
		else
		{
			throw new Exception("Impossible payment type specified.");
		}
	}

	public function setCCInfo($paymentNumber, $paymentMonth, $paymentYear, $name, $address, $city, $state, $zip, $ccType = false, $securityCode = false)
	{
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

		$this->card_number = @str_repeat('X', (strlen($paymentNumber) - 4)) . substr($paymentNumber, -4);
		$this->credit_card_type = $ccType;
	}

	/**
	 * Processes the initial credit card payment
	 * @return $rslt
	 * @throws Exception
	 */
	public function processPayment($CustomerObj, $StoreObj, $OrderObj)
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

			$trxtype = 'S'; //sale

			$rslt = $process->processPayment($CustomerObj, $OrderObj->id, $StoreObj->franchise_id, $StoreObj->id, $this->ccName, $this->total_amount, $this->ccNumber, $this->ccMonth, $this->ccYear, $this->ccSecurityCode,        // TAG: DAVIDB (used to be false)
				$this->ccZip, $this->ccAddress, $trxtype, false, "Meal Prep Plus payment");

			if ($rslt == 'success')
			{
				//insert payment record
				$this->product_orders_id = $OrderObj->id;
				$this->payment_transaction_id = $process->getPNRef();
				$this->merchant_account_id = $process->getMerchantAccountId();
				$this->payment_system = $process->getSystemName();
				$this->insert();

				$retval['success'] = true;
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

			$retval['success'] = false;
			$retval['human_readable_error'] = $process->getUsersExplanation();

			return $retval;
		}
		else
		{
			$retval['result'] = 'success';
			$retval['success'] = false;
			$retval['human_readable_error'] = 'Invalid Payment type.';

			return $retval;
		}
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

	static function refundForOrder($order)
	{

		$paymentObj = DAO_CFactory::create('product_payment');
		$paymentObj->product_orders_id = $order->id;
		$paymentObj->find();
		$hadError = false;

		while($paymentObj->fetch())
		{
			if ($paymentObj->payment_type == CProductPayment::CC)
			{
				$rslt  = $paymentObj->processCredit();

				if ($rslt['result'] != 'success')
				{
					return $rslt;
				}
			}
		}

		if (empty($rslt))
		{
			$rslt['result'] = 'success';
			$rslt['userText'] = 'Success';

		}


		return $rslt;

	}

	static function getMerchantAccountID($store_id)
	{
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


	/**
	 * Overridden to do some extra validation
	 */
	function insert($ignoreAffectedRows = false)
	{

		$rtn = parent::insert($ignoreAffectedRows = false);

		return $rtn;
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

		$paymentObj->query("select p.payment_transaction_number, p.credit_card_type, p.payment_number, p.do_not_reference, p.timestamp_created, p.created_by, p.user_id, u.user_type
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
					'date' => $thisDate
				);
			}
			else if ($thisDate > $retVal[$cc_number]['date'])
			{
				$retVal[$cc_number] = array(
					'payment_id' => $paymentObj->payment_transaction_number,
					'cc_number' => $cc_number,
					'card_type' => $paymentObj->credit_card_type,
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

	/**
	 * Processes the a credit or debit using the passed in reference number.
	 * Inserts "$this" if successful
	 * @return $rslt
	 * @throws Exception
	 */
	public function processByReference($CustomerObj, $OrderObj, $StoreObj, $refNum)
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

			$rslt = $process->processPayment($CustomerObj, $OrderObj->id, $StoreObj->franchise_id, $StoreObj->id, false, $this->total_amount, false, false, false, false, false, false, $trxtype, $refNum);

			if ($rslt == 'success')
			{
				//insert payment record
				$this->payment_transaction_id = $process->getPNRef();
				$this->product_orders_id = $OrderObj->id;
				$this->payment_transaction_id = $process->getPNRef();
				$this->merchant_account_id = $process->getMerchantAccountId();
				$this->payment_system = $process->getSystemName();
				$this->insert();

				$retval['result'] = 'success';
				$retval['success'] = true;
				$retval['human_readable_error'] = 'Success.';
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

			$retval['success'] = false;
			$retval['result'] = $rslt;
			$retval['userText'] = $process->getUsersExplanation();
			$retval['human_readable_error'] = $process->getUsersExplanation();

			return $retval;
		}
		else
		{
			$retval['result'] = 'incorrect_payment_type';
			$retval['success'] = false;
			return $retval;
		}
	}

	/**
	 * Processes a new credit card credit - the amount to credit is passed in.
	 * The CC# is required since this method will not reference a previous transaction
	 * @return $rslt
	 * @throws Exception
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

	/**
	 * Processes a credit card credit by referencing the original cc payment and credit the entire amount
	 * @return $rslt
	 * @throws Exception
	 */
	public function processCredit($amount = false)
	{

		$retval = array(
			'result' => 'none',
			'userText' => ''
		);

		if ($this->payment_type == CPayment::CC && $this->payment_transaction_id)
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

			$origid = $this->payment_transaction_id;
			$amountToCredit = $this->total_amount;
			if ($amount)
			{
				$amountToCredit = ($amount <= $this->total_amount && $amount > 0) ? $amount : $this->total_amount;
			}

			$rslt = $process->processPayment($CustomerObj, $this->product_orders_id, $StoreObj->franchise_id, $StoreObj->id, null, $amountToCredit, null, null, null, null, null, null, $trxtype, $origid);

			if ($rslt == 'success')
			{

				$CreditObj = clone($this);
				$CreditObj->payment_type = CPayment::REFUND;
				$CreditObj->payment_transaction_id = $process->getPNRef();
				$CreditObj->total_amount = $amountToCredit;
				$CreditObj->referent_id = $this->id;
				$CreditObj->merchant_account_id = $process->getMerchantAccountId();
				$CreditObj->payment_system = $process->getSystemName();

				$CreditObj->insert();

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
			$retval['userText'] = 'The Payment type was invalid or the original transaction number was invalid.';
			$retval['result'] = 'invalid_payment_type_or_transaction_number';

			return $retval;
		}
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