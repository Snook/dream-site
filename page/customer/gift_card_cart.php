<?php
require_once('includes/CCart2.inc');
require_once('DAO/BusinessObject/COrders.php');
require_once('DAO/BusinessObject/CPayment.php');
require_once('DAO/BusinessObject/CStore.php');
require_once('DAO/BusinessObject/CUser.php');
require_once('DAO/BusinessObject/CSession.php');

class page_gift_card_cart extends CPage
{
	function runPublic()
	{
		$this->runCustomer();
	}

	function runCustomer()
	{
		$tpl = CApp::instance()->template();

		$tpl->assign('cart_info', CUser::getCartIfExists(true));
	}
}