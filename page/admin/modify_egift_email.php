<?php // page_order_tahnkyou.php
 require_once("includes/CPageAdminOnly.inc");

require_once("page/customer/order_details.php");
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');

class page_admin_modify_egift_email extends CPageAdminOnly {


 	function runSiteAdmin() {
            $this->run();
	}


	function runHomeOfficeManager() {
	         $this->run();
	}


	function run() {

		$tpl = CApp::instance()->template();

		$cardPurchase = array();

		if (!isset($_REQUEST['gcOrder']))
		{
			throw new Exception('did not receive ID in gift_card_details');
		}

		$OrderRetreiver = DAO_CFactory::create('gift_card_order');
		$OrderRetreiver->id = CGPC::do_clean($_REQUEST['gcOrder'],TYPE_INT);
		$OrderRetreiver->find(true);

		$Form = new CForm();

		$Form->Repost = FALSE;

		$Form->DefaultValues['recipient_email_address'] = $OrderRetreiver->recipient_email_address;

		$Form->AddElement(array (CForm::type => CForm::Text,
		 CForm::size => 50,
		 CForm::maxlength => 80,
		 CForm::name => 'recipient_email_address'));

		$Form->AddElement(array (CForm::type => CForm::Submit,
		//	CForm::verify => true,
	 		CForm::name => 'change_email', CForm::value => 'Change Email Address'));

	 		$tpl->assign('form', $Form->Render());


	 	if (isset($_POST['change_email']))
	 	{
	 		$oldVersion = clone($OrderRetreiver);
	 		$newEmail = $Form->value('recipient_email_address');

	 		if (ValidationRules::validateEmail($newEmail, true))
	 		{

		 		$oldEmail = $OrderRetreiver->recipient_email_address;
		 		CLog::Record("eGift Record: Recipient Email changed from " . $oldEmail . " to " . $newEmail);
		 		$OrderRetreiver->recipient_email_address = $newEmail;
		 		$OrderRetreiver->update($oldVersion);

		 		$tpl->assign('confirm', true);
		 		$tpl->assign('successMsg', "Recipient Email successfully changed from " . $oldEmail . " to " . $newEmail);
	 		}
	 		else
	 		{
		 		$tpl->assign('error', true);
		 		$tpl->assign('errorMsg', "Recipient Email address is invalid");
	 		}

	 	}

	}



}