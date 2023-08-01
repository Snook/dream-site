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

class page_admin_gift_card_balance extends CPageAdminOnly
{


 	function runSiteAdmin()
 	{
            $this->run();
	}
	function runHomeOfficeStaff()
	{
            $this->run();
	}
	function runHomeOfficeManager()
	{
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

	//displays the balance inquiry form
	function run()
	{
		CApp::forceSecureConnection();

		$tpl = CApp::instance()->template();

		$Form = new CForm('get_gc_balance');
		$Form->Repost = true;

		if (isset($_POST['balance_submit']))
		{
			if ($Form->validate_CSRF_token())
			{
				$currentCard = CGPC::do_clean($_REQUEST['gift_card_number'],TYPE_INT);
				$response = "$ " . CGiftCard::checkBalanceDebitGiftCard($currentCard);
				$tpl->assign('response', $response);
				$tpl->assign('card_number', str_repeat('X', (strlen($currentCard) - 4)) . substr($currentCard, -4));
			}
			else
			{
				$tpl->setErrorMsg(CSRF_Fail_Msg);
			}
		}
		else
		{
			$Form->setCSRFToken();
		}

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 20,
			CForm::size => 15,
			CForm::dd_required => true,
			CForm::autocomplete => false,
			CForm::name => 'gift_card_number'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::css_class => 'button',
			CForm::name => 'balance_submit',
			CForm::value => 'Check Balance'
		));

		if (isset($_GET['print']) && $_GET['print'] == "true")
		{
			$tpl->assign('response', CGPC::do_clean($_GET['r'],TYPE_STR));
			$tpl->assign('card_number', CGPC::do_clean($_GET['cn'],TYPE_INT));

			$tpl->assign('print_view', true);
		}

		$tpl->assign('form_account', $Form->Render());
	}
}

?>