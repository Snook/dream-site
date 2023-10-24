<?php
require_once("Config.inc");

require_once 'DAO/BusinessObject/COrders.php';
require_once 'DAO/BusinessObject/CBox.php';
require_once 'DAO/BusinessObject/CBoxInstance.php';
require_once 'DAO/BusinessObject/CBundle.php';


class CEditOrderPaymentManager
{
	/**
	 * Process PaymentType for an edited order
	 *
	 * This handles Refunds and Charges to Credit Card and Gift Cards
	 *
	 * @param $newOrder
	 * @param $Cart
	 * @param $paymentInfo
	 * @param $paymentType
	 * @param $originalOrder
	 * @param $originalGrandTotal
	 * @param $User
	 * @param $Store
	 *
	 * @throws Exception
	 */
	public static function processPayment($newOrder, $Cart, $paymentInfo, $paymentType, $originalOrder, $originalGrandTotal, $User, $Store)
	{

		$paymentResult = array('result' => 'edit_success');
		$newPayment = DAO_CFactory::create('payment');
		$newPayment->user_id = $originalOrder->user_id;
		$newPayment->store_id = $originalOrder->store_id;
		$newPayment->order_id = $originalOrder->id;
		$newPayment->is_delayed_payment = 0;
		$newPayment->is_escrip_customer = 0;
		$newPayment->total_amount = $newOrder->grand_total - $originalGrandTotal;


		switch (strtoupper($paymentType)) {
			case CPayment::REFERENCE:
				self::processReferences($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store);
				break;
			case 'GC_REFUND':
			case 'GC_REFUND_GIFT_CARD':
				self::processGiftCardRefund($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store);
				break;
			case CPayment::GIFT_CARD:
				self::processNewGiftCard($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store);
				break;
			case 'REFERENCE_GIFT_CARD':
				self::processReferenceWithGiftCard($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store);
				break;
			case 'CC_REFUND':
				self::processCreditCardRefund($newOrder,$Cart, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store);
				break;
			case 'CC_GC_REFUND':
			case 'CC_REFUND_GIFT_CARD':
			case 'CC_GC_REFUND_GIFT_CARD':
				self::processCreditCardWithGiftCardRefund($newOrder, $Cart, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store);
				break;
			case 'CC_GIFT_CARD':
				self::processNewCreditCardWithGiftCard($newOrder,$paymentInfo, $newPayment,$originalOrder,$originalGrandTotal,$User,$Store);
				break;
			case CPayment::CC:
				self::processNewCreditCard($newOrder,$paymentInfo, $newPayment,$originalOrder,$originalGrandTotal,$User,$Store);
				break;
			default:
				CApp::instance()->template()->setErrorMsg('Cannot process reference transaction.');
				throw new Exception("Cannot process reference transaction - transaction type not found.");
		}

	}

	private static function sortByAmount(&$paymentsInCart){
		if (!function_exists('cmpTotals'))
		{
			function cmpTotals($a, $b)
			{
				if ($a['amount'] == $b['amount'])
				{
					return 0;
				}

				return ($a['amount'] < $b['amount']) ? 1 : -1;
			}
		}
		usort($paymentsInCart, "cmpTotals");
	}

	/**
	 * @throws Exception
	 */
	private static function processCreditCardWithGiftCardRefund($newOrder, $Cart, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store){


		//should come in as negative since refund
		CLog::Assert($newPayment->total_amount < 0, "Refunds should come in as negative amounts");
		$totalAmountToRefund = intval(($newPayment->total_amount * -1) *100);

		$origPayments['canAutoAdjust'] = $paymentInfo['canAutoAdjust'];
		$origPayments['paymentsFromCart'] = $paymentInfo['paymentsFromCart'];

		$paymentsInCart = $Cart->getAllPayments();

		$selectedPayments = $origPayments['paymentsFromCart'];
		self::sortByAmount($selectedPayments);

		$paymentTypesSelectedToRefund = count($selectedPayments);
		$paymentTypesWithAppliedFunds  = 0;
		//divide payments across multiple methods
		foreach ($selectedPayments as &$payment){
			$availablePaymentAmount = self::determineAvailablePaymentAmount($payment,$paymentsInCart);
			$availablePaymentAmount = $availablePaymentAmount * 100;
			$paymentTypesWithAppliedFunds++;
			if( $totalAmountToRefund <= $availablePaymentAmount ){

				$payment['paymentData']['amount_to_refund'] = $totalAmountToRefund/100 * -1;

				$totalAmountToRefund  = $totalAmountToRefund - $availablePaymentAmount;
				break;
			}else{
				$totalAmountToRefund  = $totalAmountToRefund - $availablePaymentAmount;
				$payment['paymentData']['amount_to_refund'] = $availablePaymentAmount/100 * -1;
			}
		}
		$origPayments['paymentsFromCart'] = $selectedPayments;
		$processedReference = [];
		if($totalAmountToRefund > 0)
		{
			CApp::instance()->template()->setErrorMsg('Cannot process refund. There was an issue with the refund methods selected.');
			throw new Exception("Cannot process selected refund. Payment types selected do not have enough funds.");
		}
/*		elseif($paymentTypesSelectedToRefund > $paymentTypesWithAppliedFunds)
		{
			CApp::instance()->template()->setErrorMsg('Cannot process the refund. There was an issue with the refund methods selected.');
			throw new Exception("Cannot process the refund. The payments types selected to refund do not match the available types to refund.");
		}*/
		else{
			self::processReferences($newOrder, $origPayments, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store, $processedReference);

			try
			{
				self::processGiftCardRefund($newOrder, $origPayments, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store);
			}
			catch (Exception $eWithGCProcessing)
			{
				foreach ($processedReference['success'] as $referencePayment)
				{
					$referencePayment->total_amount = $referencePayment->total_amount * -1;
					self::revertReferencePayment($referencePayment, $originalOrder, $User, $Store);
				}
				throw $eWithGCProcessing;
			}
		}

	}

	//Determines if an existing payment method has balance that can be credited
	private static function determineAvailablePaymentAmount($currentPayment, $allPayments){
		$availableToRefund = $currentPayment['amount'];
		foreach ($allPayments as $payment){
			$type = strtoupper($payment['paymentData']['payment_type']);
			if($type == CPayment::REFUND){
				$refundRef = $payment['paymentData']['previous_payment_id'];
				$currentId = $currentPayment['paymentData']['payment_id'];
				if($refundRef == $currentId){
					$availableToRefund = (intval($availableToRefund * 100) - intval($payment['amount'] * 100))/100;
				}
			}
			//Gift Cards can have any amount refunde....unless business rules change...
			if($type == CPayment::REFUND_GIFT_CARD){
				$refundRef = $payment['paymentData']['previous_payment_id'];
				$currentId = $currentPayment['paymentData']['payment_id'];
				if($refundRef == $currentId){
					$availableToRefund = (intval($availableToRefund * 100) - intval($payment['amount'] * 100))/100;
				}
			}

		}
		return $availableToRefund;
	}

	/**
	 * @throws Exception
	 */
	private static function processCreditCardRefund($newOrder, $Cart, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store){

		CLog::Assert($newPayment->total_amount < 0, "Refunds should come in as negative amounts");
		$totalAmountToRefund = intval(($newPayment->total_amount * -1) *100);

		$totalGCpayment = 0;
		if (array_key_exists('new_gc_payment', $paymentInfo))
		{
			foreach ($paymentInfo['new_gc_payment'] as $gcPayment)
			{
				if (!is_null($gcPayment['gc_number']))
				{
					$totalGCpayment += $gcPayment['gc_amount'];
				}
			}
		}
		$totalAmountToRefund = $totalAmountToRefund - ( $totalGCpayment * 100);

		$origPayments['canAutoAdjust'] = $paymentInfo['canAutoAdjust'];
		$origPayments['paymentsFromCart'] = $paymentInfo['paymentsFromCart'];


		$paymentsInCart = $Cart->getAllPayments();

		self::sortByAmount($paymentsInCart);

		//divide payments across multiple methods
		foreach ($origPayments['paymentsFromCart'] as &$payment){
			//$paymentAmount = intval($payment['amount'] *100);
			$availablePaymentAmount = self::determineAvailablePaymentAmount($payment,$paymentsInCart);
			$availablePaymentAmount = $availablePaymentAmount * 100;
			if( $totalAmountToRefund <= $availablePaymentAmount ){
				$payment['paymentData']['amount_to_refund'] = $totalAmountToRefund/100 * -1;
				break;
			}else{
				$totalAmountToRefund  = $totalAmountToRefund - $availablePaymentAmount;
				$payment['paymentData']['amount_to_refund'] = $availablePaymentAmount/100 * -1;
			}

		}

		self::processReferences($newOrder, $origPayments, $newPayment, $originalOrder, $originalGrandTotal, $User,$Store);
	}

	private static function processNewGiftCard($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store){

		foreach ($paymentInfo['paymentsFromCart'] as $id => $gcPayment)
		{
			$newCardNumber = null;
			if (array_key_exists('card_number', $gcPayment['tempData'])) {
				$newCardNumber = $gcPayment['tempData']['card_number'];
			}
			if(!is_null($newCardNumber)){//new
				$newPayment->total_amount =  $gcPayment['amount'];
				$newPayment->payment_number = $newCardNumber;
				if(!is_null($gcPayment['amount']) && $gcPayment['amount'] != 0){
					self::processDebitGiftCard( $newPayment, $originalOrder);
				}
			}

		}
	}

	private static function processNewCreditCardWithGiftCard($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store){
		$totalGCpayment = 0;
		foreach ($paymentInfo['new_gc_payment'] as $gcPayment)
		{
			$newPayment->total_amount =  $gcPayment['gc_amount'];
			$newPayment->payment_number = $gcPayment['gc_number'];
			if(!is_null($gcPayment['gc_number'] )){
				self::processDebitGiftCard( $newPayment, $originalOrder);
				$totalGCpayment += $gcPayment['gc_amount'];
			}
		}

		$newPayment = DAO_CFactory::create('payment');
		$newPayment->user_id = $originalOrder->user_id;
		$newPayment->store_id = $originalOrder->store_id;
		$newPayment->order_id = $originalOrder->id;
		$newPayment->is_delayed_payment = 0;
		$newPayment->is_escrip_customer = 0;

		$origPayments['canAutoAdjust'] = $paymentInfo['canAutoAdjust'];
		$origPayments['paymentsFromCart'] = $paymentInfo['paymentsFromCart'];
		$newPayment->total_amount = $newOrder->grand_total - $originalGrandTotal - $totalGCpayment;

		self::processNewCreditCard($newOrder, $paymentInfo, $newPayment,$originalOrder,$originalGrandTotal,$User,$Store);
	}

	/**
	 * @throws Exception
	 */
	private static function processReferenceWithGiftCard($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store){

		$processedPayments = [];
		if (!$paymentInfo['canAutoAdjust'])
		{
			CApp::instance()->template()->setErrorMsg('Cannot process reference transaction - transaction not found.');
			throw new Exception("Cannot process reference transaction - transaction not found.");
		}

		$totalGCpayment = 0;
		foreach ($paymentInfo['new_gc_payment'] as $gcPayment)
		{
			if(!is_null($gcPayment['gc_number'] ))
			{
				$totalGCpayment += $gcPayment['gc_amount'];
			}
		}

		$newPayment = DAO_CFactory::create('payment');
		$newPayment->user_id = $originalOrder->user_id;
		$newPayment->store_id = $originalOrder->store_id;
		$newPayment->order_id = $originalOrder->id;
		$newPayment->is_delayed_payment = 0;
		$newPayment->is_escrip_customer = 0;

		$origPayments['canAutoAdjust'] = $paymentInfo['canAutoAdjust'];
		$origPayments['paymentsFromCart'] = $paymentInfo['paymentsFromCart'];
		$newPayment->total_amount = $newOrder->grand_total - $originalGrandTotal - $totalGCpayment;

		self::processReferences($newOrder, $origPayments, $newPayment, $originalOrder,$originalGrandTotal,$User,$Store);


		foreach ($paymentInfo['new_gc_payment'] as $gcPayment)
		{
			$newPayment->total_amount =  $gcPayment['gc_amount'];
			$newPayment->payment_number = $gcPayment['gc_number'];
			if(!is_null($gcPayment['gc_number'] )){
				try{
					self::processDebitGiftCard( $newPayment, $originalOrder);
				} catch (Exception $e) {
					self::processReferencesRollback($newOrder, $origPayments, $newPayment, $originalOrder,$originalGrandTotal,$User,$Store);

					throw $e;
				}
			}
		}
	}

	/**
	 * @throws Exception
	 */
	private static function processCreditCardWithGiftCard($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store){

		if (!$paymentInfo['canAutoAdjust'])
		{
			CApp::instance()->template()->setErrorMsg('Cannot process reference transaction - transaction not found.');
			throw new Exception("Cannot process reference transaction - transaction not found.");
		}

		$totalGCpayment = 0;
		foreach ($paymentInfo['new_gc_payment'] as $gcPayment)
		{
			$newPayment->total_amount =  $gcPayment['gc_amount'];
			$newPayment->payment_number = $gcPayment['gc_number'];
			if(!is_null($gcPayment['gc_number'] ))
			{
				self::processDebitGiftCard($newPayment, $originalOrder);
			}
			$totalGCpayment += $gcPayment['gc_amount'];
		}

		$newPayment = DAO_CFactory::create('payment');
		$newPayment->user_id = $originalOrder->user_id;
		$newPayment->store_id = $originalOrder->store_id;
		$newPayment->order_id = $originalOrder->id;
		$newPayment->is_delayed_payment = 0;
		$newPayment->is_escrip_customer = 0;

		$origPayments = $paymentInfo['paymentsFromCart'];
		$newPayment->total_amount = $newOrder->grand_total - $originalGrandTotal - $totalGCpayment;


		self::processNewCreditCard($newOrder, $paymentInfo, $newPayment,$originalOrder, $originalGrandTotal, $User,$Store);

	}

	/**
	 * @throws Exception
	 */
	private static function processDebitGiftCard($newPayment, $originalOrder)
	{

		if (!ValidationRules::isValidPositiveDecimal($newPayment->total_amount))
		{
			CLog::RecordNew(CLog::ERROR, 'Debit Gift Card Transaction failed. The amount was invalid.', '', '', true);
			CApp::instance()->template()->setErrorMsg('Debit Gift Card Transaction failed. The amount was invalid.');
			throw new Exception('Debit Gift Card Transaction failed. The amount was invalid.');
		}

		$newPayment->payment_type = CPayment::GIFT_CARD;

		$GCresult = CGiftCard::unloadDebitGiftCardWithRetry($newPayment->payment_number, $newPayment->total_amount, false, $originalOrder->store_id, $originalOrder->id);

		if ($GCresult)
		{
			$newPayment->payment_transaction_number = $GCresult;
			$newPayment->payment_number = str_repeat('X', (strlen($newPayment->payment_number) - 4)) . substr($newPayment->payment_number, -4);
			$newPayment->insert();
		}
		else
		{
			CLog::RecordNew(CLog::ERROR, 'Debit Gift Card Transaction failed. The number was invalid or there was insufficient funds.', '', '', true);
			CApp::instance()->template()->setErrorMsg('Debit Gift Card Transaction failed. The number was invalid or there was insufficient funds.');
			throw new Exception('Debit Gift Card Transaction failed. The number was invalid or there was insufficient funds.');
		}
	}

	private static function processNewCreditCard($newOrder, $paymentInfo, $newPayment,$originalOrder,$originalGrandTotal,$User,$Store)
	{


		$new_cc_data = $paymentInfo['new_cc_payment'];

		list($success, $msg) = COrders::validateCC($new_cc_data['ccNumber'], $new_cc_data['ccType'], $new_cc_data['ccMonth'], $new_cc_data['ccYear']);
		if (!$success)
		{
			CApp::instance()->template()->setErrorMsg($msg);
			throw new Exception("Credit Card did not validate.");
		}

		$newPayment->payment_type = CPayment::CC;


		$addr = $new_cc_data['billing_address'];
		$zip = $new_cc_data['billing_postal_code'];
		if (empty($zip)){
			$addrObj = DAO_CFactory::create('address');
			$addrObj->user_id = $originalOrder->user_id;
			if ($addrObj->find(true))
			{
				$addr = $addrObj->address_line1;
				$zip = $addrObj->postal_code;
			}
		}


		$newPayment->setCCInfo($new_cc_data['ccNumber'], $new_cc_data['ccMonth'], $new_cc_data['ccYear'], $new_cc_data['ccNameOnCard'], $addr, null, null, $zip, $new_cc_data['ccType'], (empty($new_cc_data['ccSecurityCode']) ? false : $new_cc_data['ccSecurityCode']));


		if (isset($new_cc_data['save_card_as_referene']))
		{
			$newPayment->save_card_on_completion = true;
		}


		$rslt = $newPayment->processPayment($User, $originalOrder, $Store);

		if ($rslt['result'] != 'success')
		{
			//verisign failed, rollback order and booking

			if ($rslt['result'] == 'transactionDecline')
			{
				CLog::RecordNew(CLog::CCDECLINE, $newPayment->verisignExtendedRslt);
				throw new Exception("Credit Card transaction was declined.");
			}
			else
			{
				CLog::RecordNew(CLog::ERROR, $newPayment->verisignExtendedRslt, '', '', true);
				CApp::instance()->template()->setErrorMsg('Cannot process credit card transaction.');
				throw new Exception("Cannot process credit card transaction.");
			}
		}
		else
		{
			$newPayment->insert();
		}
	}

	private static function processGiftCardRefund($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store)
	{
		foreach ($paymentInfo['paymentsFromCart'] as $id => $origPayment){

			$total_amount = $newPayment->total_amount;
			if(strtoupper($origPayment['payment_type']) != CPayment::GIFT_CARD ){
				continue;
			}
			if(array_key_exists('amount_to_refund', $origPayment['paymentData'])){
				$total_amount = $origPayment['paymentData']['amount_to_refund'];
			}

			if(array_key_exists('amount_to_charge', $origPayment['paymentData'])){
				$total_amount = $origPayment['paymentData']['amount_to_charge'];
			}

			$total_amount *= -1;

			if($total_amount > 0 )
			{
				{
					$GC_trans = new DAO();
				}
				$query = "select id, billing_email, cc_number, cc_type, cc_ref_number from gift_card_transaction where order_id = {$originalOrder->id} and transaction_id = '{$origPayment['paymentData']['payment_reference']}'";

				$GC_trans->query($query);
				$GC_trans->fetch();

				$acct_retreive = DAO_CFactory::create('gift_card_transaction');
				$acct_retreive->query("SET @GCAN = ''");
				$acct_retreive->query("Call get_gcan_trans({$GC_trans->id}, @GCAN)");
				$acct_retreive->query("SELECT @GCAN");
				$acct_retreive->fetch();
				$account = base64_decode(CCrypto::decode($acct_retreive->_GCAN));

				list($success, $message, $DDTransactionRowID) = CGiftCard::loadDebitGiftCardWithRetry($account, $total_amount, $GC_trans->cc_ref_number, $GC_trans->cc_number, $GC_trans->cc_type, $GC_trans->billing_email, 'M');

				if ($success)
				{
					// also add a payment that reflects this refund
					$refundLine = DAO_CFactory::create('payment');

					$refundLine->user_id = $User->id;
					$refundLine->store_id = $Store->id;
					$refundLine->order_id = $originalOrder->id;
					$refundLine->is_delayed_payment = 0;
					$refundLine->is_escrip_customer = 0;
					$refundLine->total_amount = $total_amount;
					$refundLine->payment_number = $origPayment['paymentData']['cc_number'];
					$refundLine->payment_type = CPayment::REFUND_GIFT_CARD;
					$refundLine->referent_id = $origPayment['paymentData']['payment_id'];
					$refundLine->insert();
				}
				else
				{
					CLog::RecordNew(CLog::ERROR, "Error reloading Gift Card while refunding Shipping Order: {$originalOrder->id} | Card # $account | Trans # {$origPayment['paymentData']['cc_number']} ", false, false, true);
					CApp::instance()->template()->setErrorMsg('Reloading Gift Card failed.' );
					throw new Exception('Reloading Gift Card Transaction failed.'. $message);
				}
			}
		}
	}

	private static function processReferences($newOrder, $paymentInfo, $newPayment, $originalOrder, $originalGrandTotal, $User, $Store, &$processedPayments = [])
	{
		$counter = 0;


		if (!$paymentInfo['canAutoAdjust'])
		{
			CApp::instance()->template()->setErrorMsg('Cannot process reference transaction - transaction not found.');
			throw new Exception("Cannot process reference transaction - transaction not found.");
		}
		$origPayments = $paymentInfo['paymentsFromCart'];

		if(empty($newPayment->total_amount)){
			$newPayment->total_amount = $newOrder->grand_total - $originalGrandTotal;
		}


		foreach ($origPayments as $id => $origPayment)
		{
			if(strtoupper($origPayment['payment_type']) == CPayment::GIFT_CARD){
				//ignore gc - they should be processed by another handler
				continue;
			}
			$counter++;

			if ($counter > 1)
			{
				// first new payment was provided to us, but for others we must create and init them
				$newPayment = DAO_CFactory::create('payment');
				$newPayment->user_id = $originalOrder->user_id;
				$newPayment->store_id = $originalOrder->store_id;
				$newPayment->order_id = $originalOrder->id;
				$newPayment->is_delayed_payment = 0;
				$newPayment->is_escrip_customer = 0;
			}

			$newPayment->credit_card_type = $origPayment['paymentData']['card_type'];
			$newPayment->payment_number = $origPayment['paymentData']['cc_number'];
			$newPayment->payment_type = CPayment::CC;
			$newPayment->referent_id = $origPayment['paymentData']['payment_id'];

			if(array_key_exists('amount_to_refund', $origPayment['paymentData'])){
				$newPayment->total_amount = $origPayment['paymentData']['amount_to_refund'];
			}

			if(array_key_exists('amount_to_charge', $origPayment['paymentData'])){
				$newPayment->total_amount = $origPayment['paymentData']['amount_to_charge'];
			}

			$rslt = $newPayment->processByReference($User, $originalOrder, $Store, $origPayment['paymentData']['payment_reference']); // will credit or debit

			if ($rslt['result'] != 'success')
			{
				//verisign failed, rollback order and booking
				$processedPayments['failed'][] = $newPayment;
				if ($rslt['result'] == 'transactionDecline')
				{
					CLog::RecordNew(CLog::CCDECLINE, $newPayment->verisignExtendedRslt);
					CApp::instance()->template()->setErrorMsg($rslt['userText']);
					throw new Exception("Reference transaction was declined.");
				}
				else if ($rslt['result'] == 'overCreditingOriginalTransactionError')
				{
					CLog::RecordNew(CLog::ERROR, $newPayment->verisignExtendedRslt, '', '', true);
					CApp::instance()->template()->setErrorMsg('Cannot process reference transaction. PayPal returned error number 117 which usually means that the credit limit of the referenced (original) transaction has been exceeded. (You cannot credit more than the original amount of the sales transaction referenced minus any subsequent credits.) ');
					throw new Exception("Cannot process reference transaction.");
				}
				else
				{
					CLog::RecordNew(CLog::ERROR, $newPayment->verisignExtendedRslt, '', '', true);
					is_null(CApp::instance())?'':CApp::instance()->template()->setErrorMsg('Cannot process reference transaction.');
					throw new Exception("Cannot process reference transaction.");
				}
			}else{
				$processedPayments['success'][] = $newPayment;
			}
		}
	}

	private static function revertReferencePayment($payemntToProcess, $originalOrder, $User, $Store, &$processedPayments = [])
	{



			$rslt = $payemntToProcess->processByReference($User, $originalOrder, $Store, $payemntToProcess->payment_transaction_number); // will credit or debit

			if ($rslt['result'] != 'success')
			{
				//verisign failed, rollback order and booking
				$processedPayments['failed'][] = $payemntToProcess;
				if ($rslt['result'] == 'transactionDecline')
				{
					CLog::RecordNew(CLog::CCDECLINE, $payemntToProcess->verisignExtendedRslt);
					CApp::instance()->template()->setErrorMsg($rslt['userText']);
					throw new Exception("Reference transaction was declined.");
				}
				else if ($rslt['result'] == 'overCreditingOriginalTransactionError')
				{
					CLog::RecordNew(CLog::ERROR, $payemntToProcess->verisignExtendedRslt, '', '', true);
					CApp::instance()->template()->setErrorMsg('Cannot process reference transaction. PayPal returned error number 117 which usually means that the credit limit of the referenced (original) transaction has been exceeded. (You cannot credit more than the original amount of the sales transaction referenced minus any subsequent credits.) ');
					throw new Exception("Cannot process reference transaction.");
				}
				else
				{
					CLog::RecordNew(CLog::ERROR, $payemntToProcess->verisignExtendedRslt, '', '', true);
					is_null(CApp::instance())?'':CApp::instance()->template()->setErrorMsg('Cannot process reference transaction.');
					throw new Exception("Cannot process reference transaction.");
				}
			}else{
				$processedPayments['success'][] = $payemntToProcess;
			}
	}

	///-------------------------------------- Payment Method Reconciliation
	///


	private static function determineTotalGiftCardPaymentAmount($allPayments){
		$total = 0;
		foreach ($allPayments as $payment){
			$type = strtoupper($payment['paymentData']['payment_type']);
			if($type == CPayment::GIFT_CARD){
				$total = $total + $payment['amount'];
			}
			if($type == CPayment::REFUND_GIFT_CARD){
				$total = $total - $payment['amount'];
			}

		}
		return $total;
	}

	//Consolidate information about the Payment Methods in the cart
	private static function handleSinglePaymentMethod($paymentsInCart, $totalCost, $isRefund = false, &$isOnlyGiftCard = false){
		$uniquePaymentNumbers = [];
		$totalCost = intval($totalCost * 100 * -1);
		$totalRefund = $totalCost;

		$payment = reset($paymentsInCart);
		$type = strtoupper($payment['payment_type']);
		$refPaymentTypeDataArray = [];
		$paymentAmount = intval($payment['paymentData']['total'] * 100);

		if ($type == CPayment::GIFT_CARD || $type == 'GC'){
			$isOnlyGiftCard = true;
			if($isRefund)
			{
				if ($paymentAmount >= $totalRefund)
				{

					$date = new DateTime('now', new DateTimeZone('America/New_York'));
					$date->setTimestamp($payment['paymentData']['date']);
					$payment['paymentData']['date_string'] = $date->format('Y-m-d');;

					$refPaymentTypeDataArray[] = $payment['paymentData'];
				}
			}
			//Single Gift Card charge is handled on checkout page
		}

		if ($type == CPayment::CREDIT_CARD  || $type == CPayment::CC )
		{
			if($isRefund)
			{
				if ($paymentAmount >= $totalRefund)
				{

					$date = new DateTime('now', new DateTimeZone('America/New_York'));
					$date->setTimestamp($payment['paymentData']['date']);
					$payment['paymentData']['date_string'] = $date->format('Y-m-d');;

					$refPaymentTypeDataArray[] = $payment['paymentData'];
				}
			}else{
				$cardKey = $payment['paymentData']['card_type'] . '-' . $payment['paymentData']['cc_number'];
				//try to limit if duplicates
				if( !in_array($cardKey, $uniquePaymentNumbers)){
					$refPaymentTypeDataArray[] = $payment['paymentData'];
					$uniquePaymentNumbers[] = $cardKey;
				}
			}
		}

		return $refPaymentTypeDataArray;
	}

	//work through multiple previous payments methods: if refund determine how much can be assigned to each..
	//if payment, try to consolidate so duplicates are not shown
	private static function handleMultiplePaymentMethods($paymentsInCart, $totalCost, $isRefund = false, &$isOnlyGiftCard = false){
		$refPaymentTypeDataArray = [];
		$uniquePaymentNumbers = [];

		$totalCost = intval($totalCost * 100 * -1);

		$totalRefund = $totalCost;

		$totalPaymentCount = 1;
		$hasCreditCard = false;

		self::sortByAmount($paymentsInCart);

		$sumOfAvailablePayments = 0;
		$totalPaymentAdded = 0;
		foreach ($paymentsInCart as $id => $cartPayment)
		{
			$cartPayment['paymentData']['cart_pay_id'] = $id;
			$cartPayment['paymentData']['has_multiple_refund'] = false;
			$type = strtoupper($cartPayment['payment_type']);

			if ($type == CPayment::CREDIT_CARD  || $type == CPayment::CC  || $type == CPayment::GIFT_CARD  || $type == 'GC')
			{
				$hasCreditCard = true;
				if($isRefund){
					$paymentAmount = intval($cartPayment['paymentData']['total'] * 100);
					$availablePaymentAmount = CEditOrderPaymentManager::determineAvailablePaymentAmount($cartPayment,$paymentsInCart);
					$availablePaymentAmount = intval($availablePaymentAmount * 100);
					if($availablePaymentAmount >= $totalRefund){
						//single payment large enough to cover total refund
						$date = new DateTime('now',new DateTimeZone('America/New_York'));
						$date->setTimestamp($cartPayment['paymentData']['date']);
						$cartPayment['paymentData']['date_string'] = $date->format('Y-m-d'); ;

						if( $sumOfAvailablePayments > 0){
							$cartPayment['paymentData']['has_multiple_refund'] = true;
							$remainder = $totalRefund - $sumOfAvailablePayments;
							$cartPayment['paymentData']['refund_portion'] = $remainder / 100;
						}else{
							$cartPayment['paymentData']['refund_portion'] = $paymentAmount / 100;
						}

						$cartPayment['paymentData']['card_count'] = $totalPaymentCount++;
						$refPaymentTypeDataArray[] = $cartPayment['paymentData'];
						break;
					}else
					{
						//require multiple
						$cartPayment['paymentData']['has_multiple_refund'] = true;

						if ($availablePaymentAmount > 0)
						{
							$sumOfAvailablePayments +=  $availablePaymentAmount;
							if ($availablePaymentAmount <= $totalRefund && $sumOfAvailablePayments < $totalRefund)
							{
								$totalPaymentAdded += $availablePaymentAmount;
								$cartPayment['paymentData']['card_count'] = $totalPaymentCount++;
								$cartPayment['paymentData']['refund_portion'] = $availablePaymentAmount / 100;
								$refPaymentTypeDataArray[] = $cartPayment['paymentData'];
							}
							else
							{
								$remainder =  $totalRefund - $totalPaymentAdded;
								if ($remainder > 0)
								{
									$cartPayment['paymentData']['card_count'] = $totalPaymentCount++;
									$cartPayment['paymentData']['refund_portion'] = $remainder / 100;
									$refPaymentTypeDataArray[] = $cartPayment['paymentData'];
								}
								break;
							}
						}
					}
				}else
				{
					//limit new payments to CC on the payment page
					if ($type == CPayment::CREDIT_CARD || $type == CPayment::CC){
						{
							$cardKey = $cartPayment['paymentData']['card_type'] . '-' . $cartPayment['paymentData']['cc_number'];
						}
						//try to limit if duplicates
						if (!in_array($cardKey, $uniquePaymentNumbers))
						{
							$cartPayment['paymentData']['card_count'] = $totalPaymentCount++;
							$refPaymentTypeDataArray[] = $cartPayment['paymentData'];
							$uniquePaymentNumbers[] = $cardKey;
						}
					}
				}
			}
		}
		if(!$hasCreditCard){
			$isOnlyGiftCard = true;
		}
		return $refPaymentTypeDataArray;
	}

	/**
	 * Given all payments in the cart, determine which can be offered as payment
	 * methods on an Edit Order (for payment or refund). It takes into acount
	 * previous refunds when calculating amount available on subsequent refunds
	 *
	 * @param       $paymentsInCart
	 * @param       $totalCost
	 * @param false $isRefund
	 * @param false $isOnlyGiftCard
	 *
	 * @return array the payments methods the can be offered to debit or credit
	 */
	public static function reconcileAvailablePaymentMethods($paymentsInCart, $totalCost, $isRefund = false, &$isOnlyGiftCard = false ){

		if( count($paymentsInCart) == 1 ){
			return self::handleSinglePaymentMethod($paymentsInCart,$totalCost, $isRefund, $isOnlyGiftCard );
		}

		return self::handleMultiplePaymentMethods($paymentsInCart,$totalCost, $isRefund, $isOnlyGiftCard );
	}
}

?>