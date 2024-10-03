<?php // page_admin_create_store.php

/**
 * @author Todd Wallar
 */

 require_once("includes/CPageAdminOnly.inc");
 require_once('includes/DAO/BusinessObject/CGiftCard.php');
 require_once('includes/payment/PayPalProcess.php');
 require_once('includes/DAO/BusinessObject/COrders.php');
 require_once('includes/DAO/BusinessObject/CPayment.php');
 require_once('includes/class.inputfilter_clean.php');

 class page_admin_gift_card_order extends CPageAdminOnly {

	private $needStoreSelector = false;

 	function runSiteAdmin()
 	{
 			$this->needStoreSelector= true;
            $this->run();
	}
	function runHomeOfficeStaff()
	{
 			$this->needStoreSelector= true;
            $this->run();
	}
	function runHomeOfficeManager()
	{
 			$this->needStoreSelector= true;
            $this->run();
	}
	function runFranchiseManager()
	{
            $this->run();
	}
	function runFranchiseOwner()
	{
            $this->run();
	}
	function runFranchiseStaff()
	{
            $this->run();
	}
	function runFranchiseLead()
	{
		$this->run();
	}
	function runEventCoordinator()
	{
	    $this->run();
	}
	function runOpsLead()
	{
	    $this->run();
	}

	static function sendLoadConfirmationEmail($valArray)
	{
		try {
			$Mail = new CMail();

			$data = array('credit_amount' => CTemplate::moneyFormat($valArray['amount']),
				 'gc_number' => str_repeat('X', (strlen($valArray['gift_card_number']) - 4)) . substr($valArray['gift_card_number'], -4),
				 'date_of_purchase' => CTemplate::dateTimeFormat(date("Y-m-d H:i:s")),
				 'charged_amount' => CTemplate::moneyFormat($valArray['amount']),
				 'credit_card_number' => str_repeat('X', (strlen($valArray['credit_card_number']) - 4)) . substr($valArray['credit_card_number'], -4),
				 'billing_name' => $valArray['billing_name'],
				 'billing_address' => $valArray['billing_address'],
				 'billing_zip' => $valArray['billing_zip'],
				 'billing_email' => $valArray['primary_email'],
				 'credit_card_type' => $valArray['credit_card_type']);

				$contentsText = CMail::mailMerge('order_gift_card_load.txt.php', $data, false);
				$contentsHtml = CMail::mailMerge('order_gift_card_load.html.php', $data, false);

				$Mail->send(null, null,
						null, $valArray['primary_email'],
						'Gift Card Load Confirmation', $contentsHtml,
						$contentsText, '','', null, 'order_gift_card - load');

		} catch (exception $e) {
			CLog::RecordException($e);
		}

	}

	 /**
	  * @throws exception
	  */
	 function run()
    {
		if (CApp::wind_down_Live())
		{
			Capp::bounce('/backoffice/gift-card-management');
		}

    	header('Pragma: no-cache');
    	header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
    	header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

    	$tpl = CApp::instance()->template();

		$Form = new CForm('om_save');
		$Form->Repost = true;
		$Form->Bootstrap = true;

        $xssFilter = new InputFilter();
        $_POST = $xssFilter->process($_POST);


        $card_designs = CGiftCard::getActiveGCDesignArray();
        $tpl->assign('card_designs', $card_designs['designs']);

        $tpl->assign('edit', false);

        $tpl->assign('selectedDesignID', "");
        $tpl->assign('selectedMediaType', "");

        $store_id = false;
		if ($this->needStoreSelector)
		{

			$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();

			$Form->addElement(array(CForm::type => CForm::AdminStoreDropDown,
									CForm::name => 'store',
									CForm::onChangeSubmit => false,
									CForm::allowAllOption => false,
									CForm::showInactiveStores => true) );

			$store_id = $Form->value('store');

		}
		else
		{

			$store_id =  CBrowserSession::getCurrentFadminStore();
			$Form->DefaultValues['store'] = $store_id;

			$Form->addElement(array(CForm::type => CForm::Hidden,
			    CForm::name => 'store'));

		}



		if (!$store_id)
        {
            $store_id = 244;
        }

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $store_id;
		if (!$storeObj->find(true))
		{
            throw new Exception('Store not found in GCO');
        }

          $Form->AddElement(array(CForm::type=> CForm::CheckBox,
                                    CForm::name => 'sameInfo',
                                    CForm::dd_required => false,
                                    CForm::onClick => 'updateInfo'));


         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 64,
         CForm::size => 20,
         CForm::optional_email => true,
         CForm::name => 'primary_email'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 64,
         CForm::size => 20,
         CForm::optional_email => true,
         CForm::name => 'confirm_email_address'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 50,
         CForm::size => 20,
         CForm::dd_required => true,
         CForm::name => 'billing_address'));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 50,
			CForm::size => 20,
			CForm::dd_required => true,
			CForm::name => 'billing_city'
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::size => 20,
			CForm::dd_required => true,
			CForm::name => 'billing_state_id'
		));


		$Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 16,
         CForm::size =>10,
         CForm::dd_required => true,
         CForm::name => 'billing_zip'));

        $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 5,
         CForm::size => 6,
         CForm::money => true,
         CForm::dd_required => true,
         CForm::name => 'amount'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 40,
         CForm::size => 20,
         CForm::dd_required => true,
         CForm::name => 'to_name'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 40,
         CForm::size => 20,
         CForm::dd_required => true,
         CForm::name => 'from_name'));

        $Form->AddElement(array (CForm::type => CForm::TextArea,
         CForm::rows => 4,
         CForm::cols => 36,
         CForm::dd_required => false,
         CForm::name => 'message'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 30,
         CForm::size => 20,
         CForm::dd_required => true,
         CForm::name => 'billing_name'));

          $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 16,
         CForm::size => 16,
         CForm::number => true,
         CForm::autocomplete => false,
         CForm::dd_required => true,
         CForm::name => 'credit_card_number'));


          $initialYear = date('y');
          $yearOptions = array('null'=>'year', $initialYear => $initialYear);
          for ($i = 0; $i < 12; $i++) $yearOptions[++$initialYear] = $initialYear;


        $Form->AddElement(array(CForm::type=> CForm::DropDown,CForm::name => 'credit_card_exp_year',CForm::options => $yearOptions, CForm::dd_required => true));
		$Form->AddElement(array(CForm::type=> CForm::DropDown,CForm::name => 'credit_card_exp_month',CForm::options => array('null'=>'Month','01'=>'01', '02'=>'02','03'=>'03','04'=>'04', '05'=>'05','06'=>'06','07'=>'07', '08'=>'08','09'=>'09','10'=>'10', '11'=>'11','12'=>'12'), CForm::dd_required => true));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 4,
         CForm::size => 1,
         CForm::dd_required => true,
         CForm::name => 'credit_card_cvv'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 40,
         CForm::size => 20,
         CForm::dd_required => true,
         CForm::name => 'shipping_address_1'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 40,
         CForm::size => 20,
         CForm::name => 'shipping_address_2'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 20,
         CForm::size => 10,
         CForm::dd_required => true,
         CForm::name => 'shipping_city'));


        $Form->AddElement(array(CForm::type=> CForm::StatesProvinceDropDown,
							    CForm::name => 'shipping_state',
							    CForm::dd_required => true));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 5,
         CForm::size => 3,
         CForm::dd_required => true,
         CForm::name => 'shipping_zip'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 20,
         CForm::size => 10,
         CForm::dd_required => true,
         CForm::name => 'shipping_first_name'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 20,
         CForm::size => 10,
         CForm::dd_required => true,
         CForm::name => 'shipping_last_name'));

        $cardOptions = array('null'=>'Card Type');
		$cardOptions [CPayment::VISA]= 'Visa';
		$cardOptions [CPayment::MASTERCARD]= 'MasterCard';
		$cardOptions [CPayment::DISCOVERCARD]= 'Discover';
		$cardOptions [CPayment::AMERICANEXPRESS]= 'American Express';

		$Form->AddElement(array(CForm::type=> CForm::DropDown,
								CForm::name => 'credit_card_type',
								CForm::options => $cardOptions,
								CForm::dd_required => true));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 64,
         CForm::size => 20,
         CForm::email => true,
         CForm::dd_required => true,
         CForm::name => 'recipient_email'));

         $Form->AddElement(array (CForm::type => CForm::Text,
         CForm::maxlength => 64,
         CForm::size => 20,
         CForm::email => true,
         CForm::dd_required => true,
         CForm::name => 'confirm_recipient_email'));


         if (isset($_POST["submit_gc_order"]))
         {

         	$hadError = false;

         	//get the card number they entered and the amount desired
         	$currentAmount = $_REQUEST['amount'];

         	// validate amount
         	if ($currentAmount < 25 or $currentAmount > 500)
         	{
         		$tpl->setErrorMsg('Invalid Amount: Please enter an amount greater than $25 and no greater than $500.');
         		$hadError = true;
         	}

         	if ($_POST['confirm_email_address'] != $_POST['primary_email'])
         	{
         		$tpl->setErrorMsg('Email addresses do not match. Please enter your email address again.');
         		$hadError = true;
         	}

         	// validate credit card payment
         	list($success, $msg) = COrders::validateCC($_POST['credit_card_number'], $_POST['credit_card_type'], $_POST['credit_card_exp_month'], $_POST['credit_card_exp_year']);

         	if ( !$success )
         	{
         		if ($msg )
         		{
         			$tpl->setErrorMsg($msg);
         			$hadError = true;
         		}
         		else
         		{
         			$tpl->setErrorMsg('A credit card processing error has occurred.');
         			$hadError = true;
         		}

         	}


         	$isVirtualCard = false;
         	if (isset($_POST['media_type']) and $_POST['media_type'] == 'virt')
         	{
         		$isVirtualCard = true;
         	}

             if(is_numeric($designID = $_POST['design_id']))
			 {
				 $designID = $_POST['design_id'];
			 }

         	if (!$designID or $designID < 2 or $designID > 9)
         	{
         		throw new Exception ("Design ID was not set");
         	}

         	if ($isVirtualCard)
         	{

         		if ($_POST['confirm_recipient_email'] != $_POST['recipient_email'])
         		{
         			$tpl->setErrorMsg('Email addresses do not match. Please enter your email address again.');
         			$hadError = true;
         		}
         	}

         	$isUS = true;
         	if (!$isVirtualCard)
         	{
         		if (CStatesAndProvinces::isProvince($_POST['shipping_state']))
         		{
         			$isUS = false;
         		}
         	}

         	if (!$isVirtualCard && ((strlen($_POST['shipping_zip']) != 5 && $isUS) || !is_numeric($_POST['shipping_zip'])))
         	{
         		$tpl->setErrorMsg('The ZIP code is not valid. Please provide a 5 digit ZIP code.');
         		$hadError = true;
         	}

         	if (!$isVirtualCard && strlen($_POST['shipping_zip']) != 6 && !$isUS)
         	{
         		$tpl->setErrorMsg('The ZIP code is not valid. Please provide a 6 digit ZIP code.');
         		$hadError = true;
         	}

			 if(is_numeric($_POST['amount']))
			 {
				 $charge_amount = $_POST['amount'];
			 }
			 else
			 {
				 $tpl->setErrorMsg('The Amount is not valid. Please provide an amount > $25.');
				 $hadError = true;
			 }

			 if ($_POST['media_type'] == 'phys')
			 {
				 $charge_amount += COrders::GIFT_CARD_SHIPPING;
			 }


			 if(!is_numeric($_POST['credit_card_cvv']))
			 {
				 $tpl->setErrorMsg('The CVV code is not valid.');
				 $hadError = true;
			 }

			 if(!is_numeric($_POST['billing_zip']))
			 {
				 $tpl->setErrorMsg('The Zip code is not valid.');
				 $hadError = true;
			 }

			 if (!$hadError)
         	{
         		//Process the credit card
         		$process = new PayPalProcess();

         		if (CGiftCard::isSmartTransactionsAliveAndWell())
         		{
					$ccResponse = $process->processGiftCardOrder(
								CGPC::do_clean($_POST['primary_email'],TYPE_STR),
								CGPC::do_clean($_POST['billing_name'],TYPE_STR),
								$charge_amount,
								$_POST['credit_card_number'],
								$_POST['credit_card_exp_month'],
								$_POST['credit_card_exp_year'],
								$_POST['credit_card_cvv'],
								$_POST['billing_zip'],
								CGPC::do_clean($_POST['billing_address'], TYPE_STR),'S',$_POST['billing_city'],$_POST['billing_state_id'] );


         			//if the credit card transaction was a success then load card
         			switch ($ccResponse[0])
         			{
         				case 'transactionDecline':
         					$tpl->assign('response','Sorry your credit card was declined.');
         					$tpl->setErrorMsg('The credit card transaction failed.');
         					$tpl->assign('step',1);
         					break;
         				case 'success':
         					$confirmNumber = COrders::generateConfirmationNum();
                            $hadGiftCardSystemError = false;

         					$cc_process_result = $process->getResult();
         					$refNum = $cc_process_result['PNREF'];
         					$obfNum = str_repeat('X', (strlen($_POST['credit_card_number']) - 4)) . substr($_POST['credit_card_number'], -4);
         					$orderObj = null;
         					if ($isVirtualCard)
         					{
                                 if(is_numeric($_POST['amount']))
         						$id = CGiftCard::addUnprocessedVirtualCardOrder(
         									$designID,
         									$_POST['amount'],
         									$_POST['to_name'],
         									$_POST['from_name'],
         									$_POST['message'],
         									CGPC::do_clean($_POST['recipient_email'], TYPE_STR),
         									'FADMIN');

                                list($newAccountNumber, $thisTransID, $AuthCode) = CGiftCard::obtainAccountNumberAndLoadWithRetry($charge_amount, 'M', CGPC::do_clean($_POST['to_name'], TYPE_STR), CGPC::do_clean($_POST['recipient_email'], TYPE_STR));

         						if ($newAccountNumber)
         						{
         							$orderObj = CGiftCard::completeNewAccountTransaction($id, CGPC::do_clean($_POST['primary_email'], TYPE_STR), $obfNum, $refNum, CGPC::do_clean($_POST['credit_card_type'], TYPE_STR),
         											$newAccountNumber, $_POST['billing_name'], CGPC::do_clean($_POST['billing_address'], TYPE_STR), $_POST['billing_zip'],  $confirmNumber, 'FADMIN', false,  false);

         							if (!$orderObj)
         							{
                                        $hadGiftCardSystemError = true;
         								// update failed - should be rare - email and event trace generated in completeNewAccountTransaction
         								$tpl->setErrorMsg("An error occurred when recording your Gift card order. Please try again later or contact support.");
                                        $process = new PayPalProcess();
                                        $process->voidGiftCardPayment(CGPC::do_clean($_POST['primary_email'], TYPE_STR), $refNum);
                                        $voidResult = CGiftCard::voidTransaction($thisTransID, $AuthCode);
                                        if ($voidResult != 'success')
                                        {
                                            CLog::RecordIntense("Failure in attempting to void CreditCard payment after Gift Card System failure.", 'ryan.snook@dreamdinners.com');
                                        }

                                        $reverter = new DAO();
                                        $reverter->query("update gift_card_order set paid = 0, processed = 0, gift_card_account_number = null where id = " . $TransData["GCO"]->id);

                                    }
         							else
                                    {
                                        CGiftCard::sendVirtualGiftCard($orderObj, $newAccountNumber);
                                    }
         						}
         						else
         						{
         							// error retreiving new account
         							// update failed - should be rare - email and event trace generated in completeNewAccountTransaction
                                    $hadGiftCardSystemError = true;

                                    $process = new PayPalProcess();
                                    $voidResult = $process->voidGiftCardPayment(CGPC::do_clean($_POST['primary_email']), $refNum);
                                    if ($voidResult != 'success')
                                    {
                                        CLog::RecordIntense("Failure in attempting to void CreditCard payment after Gift Card System failure.", 'ryan.snook@dreamdinners.com');
                                    }
         							$tpl->setErrorMsg("An error occurred when recording your Gift card order. Please try again later or contact support.");
         						}
         				}
         				else
         				{
                             if(is_numeric($_POST['shipping_zip']))
         					$id = CGiftCard::addUnprocessedPhysicalCardOrder(
         									$designID,
         									CGPC::do_clean($_POST['amount'], TYPE_STR),
         									CGPC::do_clean($_POST['to_name'], TYPE_STR),
         									CGPC::do_clean($_POST['from_name'], TYPE_STR),
         									CGPC::do_clean($_POST['message'], TYPE_STR),
         									CGPC::do_clean($_POST['shipping_first_name'], TYPE_STR),
         									CGPC::do_clean($_POST['shipping_last_name'], TYPE_STR),
         									CGPC::do_clean($_POST['shipping_address_1'], TYPE_STR),
         									CGPC::do_clean($_POST['shipping_address_2'], TYPE_STR),
         									CGPC::do_clean($_POST['shipping_state'],TYPE_STR),
         									$_POST['shipping_zip'],
         									CGPC::do_clean($_POST['shipping_city'],TYPE_STR),
         									'FADMIN');

         					$orderObj = CGiftCard::completePhysicalCardPurchaseTransaction($id,
         										CGPC::do_clean($_POST['shipping_zip'], TYPE_STR), $obfNum, $refNum, CGPC::do_clean($_POST['credit_card_type'],TYPE_STR),
         										CGPC::do_clean($_POST['billing_name'], TYPE_STR),CGPC::do_clean($_POST['billing_address'],TYPE_STR), $_POST['billing_zip'], $confirmNumber, 'FADMIN');

         					if (!$orderObj)
         					{
         						// update failed - should be rare - email and event trace generated in addUnprocessedPhysicalCardOrder
         						$tpl->setErrorMsg("An error occurred when recording your Gift card order. Please try again later or contact support.");
                                $process = new PayPalProcess();
                                $voidResult = $process->voidGiftCardPayment(CGPC::do_clean($_POST['primary_email'], TYPE_STR), $refNum);
                                $hadGiftCardSystemError = true;
                            }

         				}

         				if (!$hadGiftCardSystemError)
                        {
                            $_POST['purchase_date'] = $orderObj->purchase_date;

                            $tempArray = array();
                            $tempArray[] = $orderObj;
                            CGiftCard::sendGCOrderReceiptEmail($tempArray, CGPC::do_clean($_POST, TYPE_ARRAY), $charge_amount);
                            CApp::bounce('/backoffice/gc-only-order-thankyou?gcOrders=' . $orderObj->id);
                        }
         				break;
         				default:
         					// could be config or comm error
         					$tpl->assign('response','Sorry the credit card transaction failed. Please try again.');
         					$tpl->setErrorMsg('The credit card transaction failed.');
         			}
         		}
         		else
         		{
         			// smartTransaction Site is down
         			$tpl->setErrorMsg('We apologize but it appears that the Gift Card system is unresponsive. Please try the order again later or contact Dream Dinners customer support at (360) 804-2020.');
         			$tpl->assign('hadError', true);

         		}


         	}

         }


         $Form->setCSRFToken();


        $tpl->assign('form_account', $Form->Render());
    }

}

?>