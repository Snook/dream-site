<?php
require_once("C:\\Development\\Sites\\DeliveredPhase2\\includes\\Config.inc");
require_once 'includes/CEditOrderPaymentManager.inc';
require_once 'includes/CApp.inc';
require_once 'DAO/BusinessObject/COrdersDelivered.php';
error_reporting(E_ERROR);
ini_set('display_errors', 'on');
ini_set('error_log', 'C:\xampp\php\logs\error.log');
set_error_handler("myErrorHandler");
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (strpos($errstr, 'Cannot modify header information') !== false)
	{
	}
	else if (strpos($errstr, 'REMOTE_ADDR') !== false)
	{
	}
	else if (strpos($errstr, 'SERVER_ADDR') !== false)
	{
	}
	else{
		deffered_msg_collector::addMessage('---' . $errstr . ' in ' . $errfile . ' on ' . $errline . PHP_EOL);
	}
}

define('TEST_COMMAND_LINE_USING_CART', true);

$collectErrMessage = [];

class deffered_msg_collector
{
	private static $msgCollection = [];

	public static function addMessage($msg)
	{
		self::$msgCollection[] = $msg;
	}

	public static function allMessages()
	{
		return self::$msgCollection;
	}
}

class CEditOrderPaymentManagerTest
{
	private $orderId = null;
	private $Store = null;
	private $User = null;
	private $editedOrder = null;
	private $originalOrder = null;

	private $originalPaymentCount = 0;

	private $paymentTestCount = 0;

	private $paymentTotal = 0;

	private $giftCardNumber = '9999888879556126';

	function __construct($orderId = null)
	{
		$this->setup($orderId);
	}

	private function getRandomDeliveredOrderIdWithOneCcPayment()
	{
		$limitDate = date('Y-m-d 00:00:00', strtotime(' -30 day'));
		$sql = "select order_id from payment p where p.order_id in (select id from orders " . "where store_id IN (310,311,312) and timestamp_created > '" . $limitDate . "' order by id desc) " . "group by p.order_id having count(p.order_id) = 1 limit 1;";
		$QBE = DAO_CFactory::create("payment");
		$QBE->query($sql);
		$QBE->fetch();

		return $QBE->order_id;
	}

	private function collectPreTestData(){
		//Check total number of records created -- assuming only one original payment method
		$sql = "select * from dreamsite.payment where order_id = {$this->orderId}";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();

		$this->originalPaymentCount = $Payment->N;
	}

	private function setup($orderId = null)
	{
		//just need a new delivered order with CC payment
		if (is_null($orderId))
		{
			$this->orderId = self::getRandomDeliveredOrderIdWithOneCcPayment();
		}
		else
		{
			$this->orderId = $orderId;
		}

		$this->collectPreTestData();

		echo 'Testing with Delivered order id ' . $this->orderId . PHP_EOL;
		$this->originalOrder = new COrdersDelivered();
		$this->originalOrder->id = $this->orderId;
		$this->originalOrder->find(true);

		$this->Store = $this->originalOrder->getStore();
		$this->User = $this->originalOrder->getUser();

		$this->editedOrder = clone($this->originalOrder);

		$this->sumOfPayments =  $this->sumOfExistingPayments();

		list($success, $message, $DDTransactionRowID) = CGiftCard::loadDebitGiftCardWithRetry($this->giftCardNumber, 200, null, null, null, null, 'M');


	}

	public function test_creditcard_new_payment($ccInfoArray)
	{
		$this->paymentTestCount++;
		$Cart = CCart2::instanceFromOrder($this->orderId);

		//get existing payment array
		$paymentArr = COrders::buildPaymentInfoArray($this->originalOrder->id, CUser::getCurrentUser()->user_type, false, false, true);

		////new CC payment
		$paymentType = CPayment::CC;
		$paymentArr['new_cc_payment'] = $ccInfoArray;

		$newPaymentAmount = 10;

		try
		{
			CEditOrderPaymentManager::processPayment($this->editedOrder, $Cart, $paymentArr, $paymentType, $this->originalOrder, $newPaymentAmount, $this->User, $this->Store);
			echo 'Placing CC Edit Order Worked';
			echo PHP_EOL;
		}
		catch (Exception $e)
		{
			echo 'Placing CC Edit Order Failed: ' . $e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}
	//mimic selecting one card
	public function test_creditcard_reference_payment()
	{
		$this->paymentTestCount++;
		//reload cart
		$Cart =  CCart2::instanceFromOrder($this->orderId);

		//reference previous CC payment for new payment
		$Payment = DAO_CFactory::create('payment');
		$Payment->order_id = $this->orderId;
		$Payment->payment_type = CPayment::CC;
		$creditCardPaymentId = null;

		$sql = "select id from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::CC."' order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$creditCardPaymentId = $Payment->id;


		$paymentsFromCart = $Cart->getCreditCardPayment(array($creditCardPaymentId));
		$paymentType = CPayment::REFERENCE;
		$paymentArr['paymentsFromCart'] = $paymentsFromCart;
		$paymentArr['canAutoAdjust'] = true;

		$newPaymentAmount =  10;

		try{
			CEditOrderPaymentManager::processPayment($this->editedOrder , $Cart, $paymentArr, $paymentType, $this->originalOrder, $newPaymentAmount, $this->User, $this->Store);
			echo 'Placing CC Reference Edit Order Worked';
			echo PHP_EOL;
		}catch(Exception $e){
			echo 'Placing CC Reference Edit Order Failed: '.$e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}

	public function test_creditcard_reference_giftcard_payment( $gcamount = '10')
	{
		$this->paymentTestCount++;
		//reload cart
		$Cart =  CCart2::instanceFromOrder($this->orderId);

		$GiftCardPaymentArray[] = array(
			'gc_amount' => $gcamount,
			'gc_number' => $this->giftCardNumber
		);


		//reference previous CC payment for new payment
		$Payment = DAO_CFactory::create('payment');
		$Payment->order_id = $this->orderId;
		$Payment->payment_type = CPayment::CC;
		$creditCardPaymentId = null;

		$sql = "select id from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::CC."' order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$creditCardPaymentId = $Payment->id;


		$paymentsFromCart = $Cart->getCreditCardPayment(array($creditCardPaymentId));
		$paymentType = 'REFERENCE_GIFT_CARD';
		$paymentArr['paymentsFromCart'] = $paymentsFromCart;
		$paymentArr['canAutoAdjust'] = true;
		$paymentArr['new_gc_payment'] = $GiftCardPaymentArray;

		$newPaymentAmount =  10;

		try{
			CEditOrderPaymentManager::processPayment($this->editedOrder , $Cart, $paymentArr, $paymentType, $this->originalOrder, $newPaymentAmount, $this->User, $this->Store);
			echo 'Placing CC Reference Edit Order Worked';
			echo PHP_EOL;
		}catch(Exception $e){
			echo 'Placing CC Reference Edit Order Failed: '.$e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}




	public function test_giftcard_new_payment( $amount = '10'){
		$this->paymentTestCount++;
		//reload cart
		$Cart =  CCart2::instanceFromOrder($this->orderId);

		//new gift card payment -- assumes card has funds
		$Arr = array(
			'payment_id' => 0,
			'payment_type' => 'gift_card',
			'total_amount' => $amount
		);
		$tempDataArray = array('card_number' => $this->giftCardNumber);
		$Cart->addPayment($Arr, $tempDataArray, false);

		$paymentType = CPayment::GIFT_CARD;
		$paymentsFromCart = $Cart->getNewGiftCardPayments();
		$paymentArr['paymentsFromCart'] = $paymentsFromCart;
		try{
			CEditOrderPaymentManager::processPayment($this->editedOrder , $Cart, $paymentArr, $paymentType, $this->originalOrder, '70.00', $this->User, $this->Store);
			echo 'Placing new Gift Card Edit Order Worked';
			echo PHP_EOL;
		}catch(Exception $e){
			echo 'Placing new Gift Card Reference Edit Order Failed: '.$e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}

	public function test_creditcard_refund(){
		$this->paymentTestCount++;
		//reload cart
		$Cart =  CCart2::instanceFromOrder($this->orderId);

		//CC refund
		$sql = "select id from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::CC."' order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$creditCardPaymentId = $Payment->id;

		$paymentType = 'CC_REFUND';
		$paymentsFromCart =  $Cart->getCreditCardPayment(array($creditCardPaymentId));
		$paymentArr['paymentsFromCart'] = $paymentsFromCart;
		$paymentArr['canAutoAdjust'] = true;
		$newPaymentBasis = $this->originalOrder->grand_total + 3;
		$paymentArr['canAutoAdjust'] = true;
		try{
			CEditOrderPaymentManager::processPayment($this->editedOrder , $Cart, $paymentArr, $paymentType, $this->originalOrder, $newPaymentBasis, $this->User, $this->Store);
			echo 'Refund Credit Card Worked';
			echo PHP_EOL;
		}catch(Exception $e){
			echo 'Refund Credit Card Failed: '.$e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}

	public function test_giftcard_refund(){
		$this->paymentTestCount++;
		//reload cart
		$Cart =  CCart2::instanceFromOrder($this->orderId);

		//GC refund
		$sql = "select id from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::GIFT_CARD."' order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$paymentId = $Payment->id;

		$paymentType = 'GC_REFUND';
		$paymentsFromCart =  $Cart->getGiftCardPayment(array($paymentId));
		$paymentArr['paymentsFromCart'] = $paymentsFromCart;
		$paymentArr['canAutoAdjust'] = true;
		$newPaymentBasis = $this->originalOrder->grand_total + 3;
		try{
			CEditOrderPaymentManager::processPayment($this->editedOrder , $Cart, $paymentArr, $paymentType, $this->originalOrder, $newPaymentBasis, $this->User, $this->Store);
			echo 'Refund Gift Card Worked';
			echo PHP_EOL;
		}catch(Exception $e){
			echo 'Refund Gift Card Failed: '.$e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}

	public function test_creditcard_creidtcard_split_refund(){
		$this->test_creditcard_new_payment($this->ccPaymentDataVisa());
		$this->test_creditcard_new_payment($this->ccPaymentDataMastercard());
		$this->paymentTestCount++;
		//reload cart
		$Cart =  CCart2::instanceFromOrder($this->orderId);

		$sql = "select id from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::CC."' order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$ccpaymentIdOne = $Payment->id;

		$sql = "select id, total_amount from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::CC."' and id <> {$Payment->id} order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$ccpaymentIdTwo = $Payment->id;
		$origCCpaymentAmount = $Payment->total_amount;

		$paymentType = 'CC_REFUND';
		$paymentsFromCart = $Cart->getCreditCardPayment(array($ccpaymentIdOne,$ccpaymentIdTwo));
		$paymentArr['paymentsFromCart'] = $paymentsFromCart;
		$paymentArr['canAutoAdjust'] = true;


		$newPaymentBasis = 0;
		$ogt = $this->editedOrder->grand_total;
		$this->editedOrder->grand_total = ( $origCCpaymentAmount + 10 ) * -1;
		try{
			CEditOrderPaymentManager::processPayment($this->editedOrder , $Cart, $paymentArr, $paymentType, $this->originalOrder, $newPaymentBasis, $this->User, $this->Store);
			echo 'Refund Credit Card - Gift Card Worked';
			echo PHP_EOL;
			$this->editedOrder->grand_total = $ogt;
		}catch(Exception $e){
			echo 'Refund Credit Card - Gift Card Failed: '.$e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}
	public function test_creditcard_giftcard_split_refund(){
		$this->test_creditcard_new_payment($this->ccPaymentDataVisa());
		$this->test_giftcard_new_payment();
		$this->paymentTestCount++;
		//reload cart
		$Cart =  CCart2::instanceFromOrder($this->orderId);

		$sql = "select id from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::GIFT_CARD."' order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$gcpaymentId = $Payment->id;

		$sql = "select id, total_amount from dreamsite.payment where order_id = {$this->orderId} and payment_type = '".CPayment::CC."' order by timestamp_created desc limit 1";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		$Payment->fetch();
		$ccpaymentId = $Payment->id;
		$origCCpaymentAmount = $Payment->total_amount;

		$paymentType = 'CC_GC_REFUND';
		$paymentsFromCart = $Cart->getGiftCardPayment(array($gcpaymentId));
		$paymentsFromCart =  array_merge ($paymentsFromCart, $Cart->getCreditCardPayment(array($ccpaymentId)));
		$paymentArr['paymentsFromCart'] = $paymentsFromCart;
		$paymentArr['canAutoAdjust'] = true;


		$newPaymentBasis = 0;
		$ogt = $this->editedOrder->grand_total;
		$this->editedOrder->grand_total = ( $origCCpaymentAmount + 10 ) * -1;
		try{
			CEditOrderPaymentManager::processPayment($this->editedOrder , $Cart, $paymentArr, $paymentType, $this->originalOrder, $newPaymentBasis, $this->User, $this->Store);
			echo 'Refund Credit Card - Gift Card Worked';
			echo PHP_EOL;
			$this->editedOrder->grand_total = $ogt;
		}catch(Exception $e){
			echo 'Refund Credit Card - Gift Card Failed: '.$e->getMessage();
			echo PHP_EOL;
			exit;
		}
	}


	public function printSummary($showPaymentRecord = true, $showErrorMessages = true)
	{

		//Check total number of records created -- assuming only one original payment method
		$sql = "select * from dreamsite.payment where order_id = {$this->orderId}";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);


		echo PHP_EOL.PHP_EOL.PHP_EOL;
		echo 'Total Original Payment Records is == ' . $this->originalPaymentCount .PHP_EOL;
		echo 'Total New Payment Records is == ' . $Payment->N  .PHP_EOL;
		echo 'Payment Records create is == ' . ($Payment->N - $this->originalPaymentCount ).PHP_EOL;
		echo 'Total Payment tests == ' . $this->paymentTestCount .PHP_EOL;;

		if($showPaymentRecord){
			echo PHP_EOL.PHP_EOL.PHP_EOL;
			echo 'Payment Records ' .PHP_EOL;;
			while ($Payment->fetch())
			{
				echo 'Order Id: ' . $Payment->order_id . ', Type: ' . $Payment->payment_type . ', Number:' . $Payment->payment_number . ', Ref:' . $Payment->payment_transaction_number . ', Amount:' . $Payment->total_amount . ', Created:' . $Payment->timestamp_created .PHP_EOL;
			}
		}

		if($showErrorMessages)
		{
			echo PHP_EOL . PHP_EOL . PHP_EOL;
			echo 'Collected Errors and Warnings:' . PHP_EOL;
			foreach (deffered_msg_collector::allMessages() as $message)
			{
				echo $message;
			}
		}
	}

	public function ccPaymentDataVisa()
	{
		return array(
			'ccType' => 'Visa',
			'ccNumber' => '4111111111111111',
			'ccMonth' => '04',
			'ccYear' => '24',
			'ccNameOnCard' => 'Test NewCC',
			'billing_address' => '233 Needham ST SE',
			'city' => '',
			'state_id' => '',
			'billing_postal_code' => '98028',
			'ccSecurityCode' => '123',
			'do_delayed_payment' => false
		);
	}

	public function ccPaymentDataMastercard()
	{
		return array(
			'ccType' => 'Mastercard',
			'ccNumber' => '5555555555554444',
			'ccMonth' => '04',
			'ccYear' => '24',
			'ccNameOnCard' => 'Test NewCC',
			'billing_address' => '233 Needham ST SE',
			'city' => '',
			'state_id' => '',
			'billing_postal_code' => '98028',
			'ccSecurityCode' => '123',
			'do_delayed_payment' => false
		);
	}

	private function sumOfExistingPayments(){
		$sumOfPayments = 0;
		//Check total number of records created -- assuming only one original payment method
		$sql = "select * from dreamsite.payment where order_id = {$this->orderId}";
		$Payment = DAO_CFactory::create("payment");
		$Payment->query($sql);
		while ($Payment->fetch())
		{
			if($Payment->payment_type == CPayment::REFERENCE || $Payment->payment_type == CPayment::CC || $Payment->payment_type == CPayment::CREDIT_CARD || $Payment->payment_type == CPayment::GIFT_CARD){
				$sumOfPayments += $Payment->total_amount;
			}
			if($Payment->payment_type == CPayment::REFUND_GIFT_CARD || $Payment->payment_type == CPayment::REFUND){
				$sumOfPayments -= $Payment->total_amount;
			}

		}
		return $sumOfPayments;
	}
}

//$test = new CEditOrderPaymentManagerTest(3654043);
$test = new CEditOrderPaymentManagerTest();
$test->test_creditcard_new_payment($test->ccPaymentDataMastercard());
$test->test_creditcard_reference_payment();
$test->test_giftcard_new_payment();
$test->test_creditcard_refund();
$test->test_giftcard_refund();
$test->test_creditcard_giftcard_split_refund();
$test->test_creditcard_creidtcard_split_refund();
$test->test_creditcard_reference_giftcard_payment();


$test->printSummary(true,true);
