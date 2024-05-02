<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */

require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CCouponCode.php");
require_once("CTemplate.inc");

 class processor_giftCardBalance extends CPageProcessor {

 	function runPublic()
	{
		if(isset($_REQUEST['output']) && $_REQUEST['output'] == 'json')
		{
			echo json_encode(array('success' => false, 'message' => 'Not logged in'));
		}
		else
		{
			echo 'Not logged in';
		}
	}

	function runSiteAdmin()
	{
	 	$this->runGiftCardBalance();
	}

    function runFranchiseStaff()
    {
		$this->runGiftCardBalance();
	}

	function runCustomer()
	{
		$this->runGiftCardBalance();
	}

	function runFranchiseManager()
	{
		$this->runGiftCardBalance();
	}

	function runOpsLead()
	{
		$this->runGiftCardBalance();
	}

	function runHomeOfficeManager()
	{
		$this->runGiftCardBalance();
	}

	 function runFranchiseOwner()
	 {
		 $this->runGiftCardBalance();
	 }

	function runGiftCardBalance()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$cardNumber = trim($_REQUEST['card_number']);


		if (isset($_REQUEST['redeemAmount']) && is_numeric($_REQUEST['redeemAmount']))
		{
			if ( isset($cardNumber) && is_numeric($cardNumber))
			{
				$cardBalance = CGiftCard::checkBalanceDebitGiftCard($cardNumber);

				if ($cardBalance == "Invalid Card")
				{
					if(isset($_REQUEST['output']) && $_REQUEST['output'] == 'json')
					{
						echo json_encode(array('processor_success' => false, 'processor_message' => 'Invalid card'));
					}
					else
					{
						echo 'error';
					}
				}
				else
				{
					if(isset($_REQUEST['output']) && $_REQUEST['output'] == 'json')
					{
						echo json_encode(array(
							'processor_success' => true,
							'card_number' => $cardNumber,
							'card_balance' => $cardBalance,
							'redeem_amount' => $_REQUEST['redeemAmount'],
						));
					}
					else
					{
						echo $cardNumber . "|" . $cardBalance . "|" . $_REQUEST['redeemAmount'];
					}
				}
			}
			else
			{
				if(isset($_REQUEST['output']) && $_REQUEST['output'] == 'json')
				{
					echo json_encode(array('processor_success' => false, 'processor_message' => 'Invalid card number'));
				}
				else
				{
					echo 'error';
				}
			}
		}
		else
		{
			if ( isset($cardNumber) && is_numeric($cardNumber))
			{
				$cardBalance = CGiftCard::checkBalanceDebitGiftCard($cardNumber);

				if(isset($_REQUEST['output']) && $_REQUEST['output'] == 'json')
				{
					echo json_encode(array(
						'processor_success' => true,
						'card_number' => $_REQUEST['card_number'],
						'card_balance' => $cardBalance,
					));
				}
				else
				{
					echo '&nbsp;&nbsp; Balance: ' .$cardBalance;
				}
			}
			else
			{
				if(isset($_REQUEST['output']) && $_REQUEST['output'] == 'json')
				{
					echo json_encode(array('processor_success' => false, 'processor_message' => 'Invalid card number'));
				}
				else
				{
					echo '&nbsp;&nbsp; Balance: Invalid Card Number';
				}
			}
		}
	 }
 };
?>