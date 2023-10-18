<?php
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');

class page_order_details_gift_card extends CPage
{

	function runPublic()
	{
		self::runPage();
	}

	static function runPage()
	{
		$tpl = CApp::instance()->template();

		if (!isset($_GET['orders']) || !preg_match('/^\d+(?:,\d+)*$/', $_GET['orders']))
		{
			CApp::bounce('/gift');
		}

		$IDs = explode(",", $_GET['orders']);

		CGiftCard::addOrderDrivenGiftCardDetailsToTemplate($tpl, CUser::getCurrentUser()->id, false, $IDs);

		$gcOrders = array();
		foreach ($tpl->gift_card_purchase_array as $number => $gc_purchase)
		{
			$gcOrders[$gc_purchase['order_confirm_id']] = $gc_purchase['order_confirm_id'];
		}

		$tpl->assign('orders', implode(',', $gcOrders));

		if (empty($gcOrders))
		{
			CApp::bounce('/gift');
		}
	}

	function runCustomer()
	{
		self::runPage();
	}
}

?>