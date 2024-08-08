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

class page_admin_gift_card_load extends CPageAdminOnly
{

	private $needStoreSelector = false;

	function runSiteAdmin()
	{
		$this->needStoreSelector = true;
		$this->run();
	}

	function runHomeOfficeStaff()
	{
		$this->needStoreSelector = true;
		$this->run();
	}

	function runHomeOfficeManager()
	{
		$this->needStoreSelector = true;
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
		try
		{
			$Mail = new CMail();

			$data = array(
				'credit_amount' => CTemplate::moneyFormat($valArray['amount']),
				'gift_card_number' => str_repeat('X', (strlen($valArray['gift_card_number']) - 4)) . substr($valArray['gift_card_number'], -4),
				'date_of_purchase' => CTemplate::dateTimeFormat(date("Y-m-d H:i:s")),
				'charged_amount' => CTemplate::moneyFormat($valArray['amount']),
				'credit_card_number' => str_repeat('X', (strlen($valArray['credit_card_number']) - 4)) . substr($valArray['credit_card_number'], -4),
				'billing_name' => $valArray['billing_name'],
				'billing_address' => $valArray['billing_address'],
				'billing_zip' => $valArray['billing_zip'],
				'billing_email' => $valArray['primary_email'],
				'credit_card_type' => $valArray['credit_card_type']
			);

			$contentsText = CMail::mailMerge('order_gift_card_load.txt.php', $data, false);
			$contentsHtml = CMail::mailMerge('order_gift_card_load.html.php', $data, false);

			$Mail->send(null, null, null, $valArray['primary_email'], 'Gift Card Load Confirmation', $contentsHtml, $contentsText, '', '', null, 'order_gift_card - load');
		}
		catch (exception $e)
		{
			CLog::RecordException($e);
		}
	}

	function run()
	{

		

		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$tpl = CApp::instance()->template();
		$tpl->assign('process', 'load');

		$Form = new CForm('fadmin_gift_card_load');
		$Form->Repost = true;
		$Form->Bootstrap = true;

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);
		$_REQUEST = $xssFilter->process($_REQUEST);

		$store_id = false;
		if ($this->needStoreSelector)
		{

			$Form->DefaultValues['store'] = CBrowserSession::getCurrentStore();

			$Form->addElement(array(
				CForm::type => CForm::AdminStoreDropDown,
				CForm::name => 'store',
				CForm::onChangeSubmit => true,
				CForm::allowAllOption => false,
				CForm::showInactiveStores => true
			));

			$store_id = $Form->value('store');
		}
		else
		{
			$store_id = CBrowserSession::getCurrentFadminStore();
		}

		if (!$store_id)
		{
			throw new Exception('no store id found');
		}

		$storeObj = DAO_CFactory::create('store');
		$storeObj->id = $store_id;
		if (!$storeObj->find(true))
		{
			throw new Exception('Store not found in Menu Editor');
		}

		$tpl->assign('store_id', $store_id);

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 16,
			CForm::size => 20,
			CForm::dd_required => true,
			CForm::number => true,
			CForm::autocomplete => false,
			CForm::onKeyPress => 'return keyHandler(event); ',
			CForm::name => 'gift_card_number'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 64,
			CForm::size => 20,
			CForm::optional_email => true,
			CForm::name => 'primary_email'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 64,
			CForm::size => 20,
			CForm::optional_email => true,
			CForm::name => 'confirm_email_address'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 20,
			CForm::size => 20,
			CForm::dd_required => true,
			CForm::name => 'billing_address'
		));

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

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 16,
			CForm::size => 10,
			CForm::dd_required => true,
			CForm::name => 'billing_zip'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 6,
			CForm::size => 6,
			CForm::dd_required => true,
			CForm::money => true,
			CForm::name => 'amount'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 30,
			CForm::size => 20,
			CForm::dd_required => true,
			CForm::name => 'billing_name'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 16,
			CForm::size => 16,
			CForm::dd_required => true,
			CForm::number => true,
			CForm::autocomplete => false,
			CForm::name => 'credit_card_number'
		));

		$initialYear = date('y');
		$yearOptions = array(
			'null' => 'Year',
			$initialYear => $initialYear
		);
		for ($i = 0; $i < 12; $i++)
		{
			$yearOptions[++$initialYear] = $initialYear;
		}

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'credit_card_exp_year',
			CForm::options => $yearOptions,
			CForm::dd_required => true
		));
		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'credit_card_exp_month',
			CForm::options => array(
				'null' => 'Month',
				'01' => '01',
				'02' => '02',
				'03' => '03',
				'04' => '04',
				'05' => '05',
				'06' => '06',
				'07' => '07',
				'08' => '08',
				'09' => '09',
				'10' => '10',
				'11' => '11',
				'12' => '12'
			),
			CForm::dd_required => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 4,
			CForm::size => 1,
			CForm::number => true,
			CForm::dd_required => true,
			CForm::name => 'credit_card_cvv'
		));

		$cardOptions = array('null' => 'Card Type');
		$cardOptions [CPayment::VISA] = 'Visa';
		$cardOptions [CPayment::MASTERCARD] = 'MasterCard';
		$cardOptions [CPayment::DISCOVERCARD] = 'Discover';
		$cardOptions [CPayment::AMERICANEXPRESS] = 'American Express';

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::name => 'credit_card_type',
			CForm::options => $cardOptions,
			CForm::dd_required => true
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'load_submit',
			CForm::value => 'Load Card'
		));

		IF (isset($_POST['load_submit']))
		{

			if (!$Form->validate_CSRF_token())
			{
				$tpl->setErrorMsg("The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support.
				This message can also be caused by a double submission of the same page.<br /><br /><i>If the problem persists try reloading the page.");
				$Form->setCSRFToken();
			}
			else
			{

				$hadError = false;

				if (is_numeric($_REQUEST['gift_card_number']))
				{
					$currentCard = $_REQUEST['gift_card_number'];
				}
				else
				{
					$currentCard = null;
					$tpl->setErrorMsg('The Gift Card number is invalid, non-numeric numbers were entered.');
					$hadError = true;
				}

                if(is_numeric($_REQUEST['amount']))
				{
					$currentAmount = $_REQUEST['amount'];
				}
                else
				{
					$currentAmount = 0;
				}

				// validate amount
				if ($currentAmount < 25 or $currentAmount > 500)
				{
					$tpl->setErrorMsg('Invalid Amount: Please enter an amount greater than or equal to $25 and no greater than $500.');
					$hadError = true;
				}

				if ($_POST['confirm_email_address'] != $_POST['primary_email'])
				{
					$tpl->setErrorMsg('Email addresses do not match. Please enter your email address again.');
					$hadError = true;
				}

				if (!empty($_POST['primary_email']) && !ValidationRules::validateEmail($_POST['primary_email']))
				{
					$tpl->setErrorMsg('The email address entered is not valid. Please enter your email address again.');
					$hadError = true;
				}

				// validate credit card payment
				list($success, $msg) = COrders::validateCC($_POST['credit_card_number'], $_POST['credit_card_type'], $_POST['credit_card_exp_month'], $_POST['credit_card_exp_year']);
				if (!$success)
				{
					if ($msg)
					{
						$tpl->setErrorMsg($msg);
					}
					else
					{
						$tpl->setErrorMsg('A credit card processing error has occurred.');
					}

					$hadError = true;
				}
				//	9999888877953940
				// float a trial transaction by testing the card balance
				$result = CGiftCard::checkBalanceDebitGiftCard($currentCard);
				if ($result == 'Invalid Card')
				{
					$tpl->setErrorMsg('The Gift Card number is invalid.');
					$hadError = true;
				}

				if (!$hadError)
				{
					//Process the credit card
					$process = new PayPalProcess();

					$ccResponse = $process->processGiftCardOrder($_POST['primary_email'], $_POST['billing_name'], $_POST['amount'], $_POST['credit_card_number'], $_POST['credit_card_exp_month'], $_POST['credit_card_exp_year'], $_POST['credit_card_cvv'], $_POST['billing_zip'], $_POST['billing_address'],'S',$_POST['billing_city'],$_POST['billing_state_id']);

					//if the credit card transaction was a success then load card
					switch ($ccResponse[0])
					{
						case 'transactionDecline':
							$tpl->assign('response', 'Sorry your credit card was declined.');
							$tpl->setErrorMsg('Sorry your credit card was declined.');
							$Form->setCSRFToken();
							break;
						case 'success':

							$tpl->assign('paymentDate', CTemplate::dateTimeFormat(date('Y-m-d h:m:s')));

                            if(is_numeric($_POST['billing_zip']))
							{
								$addInfo = json_encode(array(
									"billing_name" => CGPC::do_clean($_POST['billing_name'], TYPE_STR),
									"billing_address" => CGPC::do_clean($_POST['billing_address'], TYPE_STR),
									"billing_zip" => $_POST['billing_zip']
								));
							}

                            // Credit card has ben validated above
                            list($success, $message, $DDTransactionRowID) = CGiftCard::loadDebitGiftCardWithRetry($currentCard, $currentAmount, $ccResponse[1]['PNREF'], $_POST['credit_card_number'], CGPC::do_clean($_POST['credit_card_type'], TYPE_STR), CGPC::do_clean($_POST['primary_email'], TYPE_STR), 'M', $store_id, $addInfo);

                            $tpl->assign('response',$message);
                            if (!$success)
                            {
                                // TODO: better message and better error handling required
                                $tpl->setErrorMsg('The Gift Card load failed. Please try again later or contact support.');
                                $process = new PayPalProcess();
                                $voidResult = $process->voidGiftCardPayment(CGPC::do_clean($_POST['primary_email'],TYPE_STR), $ccResponse[1]['PNREF']);
                                if ($voidResult != 'success')
                                {
                                    CLog::RecordIntense("Failure in attempting to void CreditCard payment after Gift Card System failure.", 'ryan.snook@dreamdinners.com');
                                }
                            }
                            else
							{
								$tpl->assign('gc_purchase_date', date("m/d/Y"));
								if (!empty($_POST['primary_email']))
								{
									self::sendLoadConfirmationEmail($_POST);
								}

								CApp::bounce("/backoffice/gift-card-load-confirm?dd_trans_id=" . $DDTransactionRowID);
							}
							break;
						default:
							// could be config or comm error
							$tpl->assign('response', 'Sorry the credit card transaction failed. Please try again.');
							$tpl->setErrorMsg('The credit card transaction failed.');
							$Form->setCSRFToken();
					}
				}
				else
				{
					$Form->setCSRFToken();
				}
			}
		}
		else
		{
			$Form->setCSRFToken();
		}

		$tpl->assign('form_account', $Form->Render());
	}

}
?>