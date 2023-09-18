<?php
require_once('DAO/BusinessObject/COrders.php');
require_once('DAO/BusinessObject/CPayment.php');
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CUser.php');
require_once('includes/CCart2.inc');
require_once('DAO/BusinessObject/CSession.php');
require_once('includes/payment/PayPalProcess.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');
require_once('includes/class.inputfilter_clean.php');

class page_gift_card_order extends CPage
{

	function runPublic()
	{
		CApp::forceSecureConnection();
		$this->run();
	}

	function run()
	{
		$tpl = CApp::instance()->template();
		$Form = new CForm();
		$Form->Repost = true;

		$isEditMode = false;
		//$tpl->assign('edit', false);

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		if (isset($_POST['message']))
		{
			// Note: calling these functions this way actually adds to the POST array.  The should not be added ...
			// in the case where the page ois called with an gc_edit_id setting these to null overrides the default values set below
			$_POST['message'] = CGPC::do_clean((!empty($_POST['message']) ? $_POST['message'] : false), TYPE_STR);
			$_POST['amount'] = CGPC::do_clean((!empty($_POST['amount']) ? $_POST['amount'] : false), TYPE_NUM);
			$_POST['quantity'] = CGPC::do_clean((!empty($_POST['quantity']) ? $_POST['quantity'] : false), TYPE_UINT);
			$_POST['shipping_address_1'] = CGPC::do_clean((!empty($_POST['shipping_address_1']) ? $_POST['shipping_address_1'] : false), TYPE_STR);
			$_POST['shipping_address_2'] = CGPC::do_clean((!empty($_POST['shipping_address_2']) ? $_POST['shipping_address_2'] : false), TYPE_STR);
			$_POST['from_name'] = CGPC::do_clean((!empty($_POST['from_name']) ? $_POST['from_name'] : false), TYPE_STR);
			$_POST['to_name'] = CGPC::do_clean((!empty($_POST['to_name']) ? $_POST['to_name'] : false), TYPE_STR);
			$_POST['shipping_first_name'] = CGPC::do_clean((!empty($_POST['shipping_first_name']) ? $_POST['shipping_first_name'] : false), TYPE_STR);
			$_POST['shipping_last_name'] = CGPC::do_clean((!empty($_POST['shipping_last_name']) ? $_POST['shipping_last_name'] : false), TYPE_STR);
			$_POST['shipping_city'] = CGPC::do_clean((!empty($_POST['shipping_city']) ? $_POST['shipping_city'] : false), TYPE_STR);
			$_POST['recipient_email'] = CGPC::do_clean((!empty($_POST['recipient_email']) ? $_POST['recipient_email'] : false), TYPE_EMAIL);
			$_POST['shipping_zip'] = CGPC::do_clean((!empty($_POST['shipping_zip']) ? $_POST['shipping_zip'] : false), TYPE_POSTAL_CODE);
			$_POST['confirm_recipient_email'] = CGPC::do_clean((!empty($_POST['confirm_recipient_email']) ? $_POST['confirm_recipient_email'] : false), TYPE_EMAIL);
		}

		$OrderToEdit = null;
		$selectedDesign = false;
		$selectedMedia = false;
		if (isset($_POST['gc_edit_id']))
		{
			$OrderToEdit = DAO_CFactory::create('gift_card_order');
			$OrderToEdit->id = $_POST['gc_edit_id'];
			$OrderToEdit->paid = 0;
			$OrderToEdit->processed = 0;

			if ($OrderToEdit->find(true))
			{
				$isEditMode = true;

				$Form->DefaultValues['recipient_email'] = $OrderToEdit->recipient_email_address;
				$Form->DefaultValues['confirm_recipient_email'] = $OrderToEdit->recipient_email_address;
				$Form->DefaultValues['amount'] = $OrderToEdit->initial_amount;
				$Form->DefaultValues['to_name'] = $OrderToEdit->to_name;
				$Form->DefaultValues['from_name'] = $OrderToEdit->from_name;
				$Form->DefaultValues['message'] = strip_tags($OrderToEdit->message_text);
				$Form->DefaultValues['shipping_first_name'] = $OrderToEdit->first_name;
				$Form->DefaultValues['shipping_last_name'] = $OrderToEdit->last_name;
				$Form->DefaultValues['shipping_address_1'] = $OrderToEdit->shipping_address_1;
				$Form->DefaultValues['shipping_address_2'] = $OrderToEdit->shipping_address_2;
				$Form->DefaultValues['shipping_city'] = $OrderToEdit->shipping_city;
				$Form->DefaultValues['shipping_state'] = $OrderToEdit->shipping_state;
				$Form->DefaultValues['shipping_zip'] = $OrderToEdit->shipping_zip;

				$selectedDesign = $OrderToEdit->design_type_id;

				if ($OrderToEdit->media_type == 'VIRTUAL')
				{
					$selectedMedia = 'virt';
				}
				else
				{
					$selectedMedia = 'phys';
				}
			}
		}


		if (isset($_POST['gc_delete_id']))
		{
			$CartObj = CCart2::instance(true);
			$CartObj->removeGiftCardOrder($_POST['gc_delete_id']);
			$gc_orders = $CartObj->getGiftCardOrders();

			$tpl->setStatusMsg("The Gift Card was removed from the cart.");

			if (!empty($gc_orders))
			{
				CApp::bounce('/gift-card-cart');
			}
		}


		$tpl->assign('edit', $isEditMode);
		$tpl->assign('selectedDesignID', $selectedDesign);
		$tpl->assign('selectedMediaType', $selectedMedia);

		$card_designs = CGiftCard::getActiveGCDesignArray(true, true);

		$tpl->assign('card_designs', $card_designs);
		$tpl->assign('card_designjs', json_encode($card_designs['designs']));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 64,
			CForm::placeholder => "*Email Address",
			CForm::required_msg => "Please enter the recipient's email address.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::email => true,
			CForm::name => 'recipient_email'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 64,
			CForm::email => true,
			CForm::placeholder => "*Confirm Email Address",
			CForm::required_msg => "Please confirm the email address.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::name => 'confirm_recipient_email'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Number,
			CForm::maxlength => 5,
			CForm::placeholder => "*Gift Card Dollar Amount",
			CForm::required_msg => "Please enter a dollar amount.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::attribute => array(
				'min' => '25',
				'max' => '500',
				'step' => '1'
			),
			CForm::money => true,
			CForm::name => 'amount'
		));

		$Form->DefaultValues['quantity'] = 1;

		$quantityOptions = range(0, 50);
		unset($quantityOptions[0]);

		$Form->AddElement(array(
			CForm::type => CForm::DropDown,
			CForm::maxlength => 225,
			CForm::placeholder => "*Quantity (default is one)",
			CForm::required_msg => "Please enter a quantity.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::options => $quantityOptions,
			//	CForm::number => true,
			CForm::onKeyUp => 'quantityClick',
			CForm::name => 'quantity'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 40,
			CForm::required_msg => "Please enter a TO name.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::name => 'to_name'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 40,
			CForm::required_msg => "Please enter a FROM name.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::name => 'from_name'
		));

		$Form->AddElement(array(
			CForm::type => CForm::TextArea,
			CForm::rows => 4,
			CForm::cols => 36,
			CForm::placeholder => "Personalized Message",
			CForm::css_class => "form-control",
			CForm::name => 'message'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 40,
			CForm::size => 20,
			CForm::placeholder => "*Shipping Address 1",
			CForm::required_msg => "Please enter a shipping street address.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::name => 'shipping_address_1'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 40,
			CForm::size => 20,
			CForm::placeholder => "Shipping Address 2",
			CForm::css_class => "form-control",
			CForm::name => 'shipping_address_2'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 20,
			CForm::size => 10,
			CForm::placeholder => "*Shipping City",
			CForm::required_msg => "Please enter a shipping city.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::name => 'shipping_city'
		));

		$Form->AddElement(array(
			CForm::type => CForm::StatesProvinceDropDown,
			CForm::name => 'shipping_state',
			CForm::add_provinces => true,
			CForm::placeholder => "*Shipping State",
			CForm::required_msg => "Please enter a shipping state.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::onChange => 'javascript:handleStateSelection(this);'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 5,
			CForm::size => 3,
			CForm::number => true,
			CForm::placeholder => "*Shipping Zip Code",
			CForm::required_msg => "Please enter a shipping zip code.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::name => 'shipping_zip'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 20,
			CForm::size => 10,
			CForm::placeholder => "*Shipping First Name",
			CForm::required_msg => "Please enter a shipping first name.",
			CForm::required => true,
			CForm::css_class => "form-control",
			CForm::name => 'shipping_first_name'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Text,
			CForm::maxlength => 20,
			CForm::size => 10,
			CForm::css_class => "form-control",
			CForm::placeholder => "*Shipping Last Name",
			CForm::required_msg => "Please enter a shipping last name.",
			CForm::required => true,
			CForm::name => 'shipping_last_name'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'add_submit',
			CForm::value => 'Add to Cart',
			CForm::css_class => 'btn btn-primary collapse'
		));

		$Form->AddElement(array(
			CForm::type => CForm::Submit,
			CForm::name => 'edit_submit',
			CForm::value => 'Save Changes',
			CForm::css_class => 'btn btn-primary'
		));

		$hadError = false;

		if (isset($_POST['add_submit']) || isset($_POST['edit_submit']))
		{

			$isVirtualCard = false;
			if (isset($_POST['media_type']) && $_POST['media_type'] == 'virt')
			{
				$isVirtualCard = true;
			}

			// someone is trying to order a card when there aren't any available
			if ($isVirtualCard && empty($card_designs['info']['num_virtual']))
			{
				$tpl->setErrorMsg('Sorry, there are no virtual cards available at this time.');
				CApp::bounce('/gift-card-order');
			}
			else if (empty($card_designs['info']['num_physical']))
			{
				$tpl->setErrorMsg('Sorry, there are no physical cards available at this time.');
				CApp::bounce('/gift-card-order');
			}

			$currentAmount = sprintf("%01.2f", $_POST['amount']);

			// validate amount
			if (!is_numeric($currentAmount) || $currentAmount < 25 || $currentAmount > 500)
			{
				$tpl->setErrorMsg('Invalid Amount: Please enter an amount greater than $25 and no greater than $500.');
				$hadError = true;
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

			if (empty($_POST['quantity']))
			{
				$quantity = 1;
			}
			else
			{
				$quantity = $_POST['quantity'];
			}

			if (!isset($_POST['edit_submit']) && ($quantity < 1 || $quantity > 50))
			{
				$tpl->setErrorMsg("Invalid quantity: Please enter a quantity that is greater than 0 and no more than 50.");
				$hadError = true;
			}

			if (!$hadError)
			{

				$Cart = CCart2::instance();

				$designID = $_POST['design_id'];

				if (!$designID || $designID < 2 || $designID > 9)
				{
					throw new Exception ("Flipping design ID was not set");
				}

				if (isset($_POST['add_submit']))
				{
					if ($isVirtualCard)
					{

						for ($x = 0; $x < $quantity; $x++)
						{
							$id = CGiftCard::addUnprocessedVirtualCardOrder($designID, $_POST['amount'], $_POST['to_name'], $_POST['from_name'], $_POST['message'], $_POST['recipient_email'], 'CUST_CART');

							if ($id)
							{
								$Cart->addGiftCardOrder($id);
							}
						}
					}
					else
					{

						for ($x = 0; $x < $quantity; $x++)
						{
							$id = CGiftCard::addUnprocessedPhysicalCardOrder($designID, $_POST['amount'], $_POST['to_name'], $_POST['from_name'], $_POST['message'], $_POST['shipping_first_name'], $_POST['shipping_last_name'], $_POST['shipping_address_1'], $_POST['shipping_address_2'], $_POST['shipping_state'], $_POST['shipping_zip'], $_POST['shipping_city'], 'CUST_CART');

							if ($id)
							{
								$Cart->addGiftCardOrder($id);
							}
						}
					}

					CApp::bounce('/gift-card-cart');
				}
				else
				{
					// editing
					$result = false;
					if ($isVirtualCard)
					{

						$result = CGiftCard::editUnprocessedVirtualCardOrder($OrderToEdit->id, $designID, $_POST['amount'], $_POST['to_name'], $_POST['from_name'], $_POST['message'], $_POST['recipient_email'], 'CUST_CART');
					}
					else
					{
						$result = CGiftCard::editUnprocessedPhysicalCardOrder($OrderToEdit->id, $designID, $_POST['amount'], $_POST['to_name'], $_POST['from_name'], $_POST['message'], $_POST['shipping_first_name'], $_POST['shipping_last_name'], $_POST['shipping_address_1'], $_POST['shipping_address_2'], $_POST['shipping_state'], $_POST['shipping_zip'], $_POST['shipping_city'], 'CUST_CART');
					}

					if ($result !== false)
					{
						CApp::bounce('/gift-card-cart');
					}
					else
					{
						$tpl->setErrorMsg("An error occurred when updating your Gift Card order.");
					}
				}
			}
		}

		$tpl->assign('hadError', $hadError);

		if ($hadError)
		{
			if ($_POST['media_type'] !== "virt" && $_POST['media_type'] != "phys" && $_POST['media_type'] != "none")
			{
				$_POST['media_type'] = 'none';
			}

			if (!is_numeric($_POST['design_id']))
			{
				$_POST['design_id'] = 'none';
			}

			$tpl->assign('selectedMediaType', $_POST['media_type']);
			$tpl->assign('selectedDesignID', $_POST['design_id']);
		}

		$tpl->assign('form', $Form->Render());
		$tpl->assign('cart_info', CUser::getCartIfExists());
	}

}

?>