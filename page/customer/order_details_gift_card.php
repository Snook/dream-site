<?php
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');

class page_order_details_gift_card extends CPage {

	function runPublic() {
		self::runPage();
	}

	function runCustomer() {
		self::runPage();
	}

	static function runPage()
	{
		$tpl = CApp::instance()->template();
		$cardPurchase = array();

		if (!isset($_REQUEST['orders']))
		{
			throw new Exception('did not receive GD array in order_details_gift_card');
		}

		$IDs = explode(",", $_REQUEST['orders']);

		CGiftCard::addOrderDrivenGiftCardDetailsToTemplate($tpl, CUser::getCurrentUser()->id, false, $IDs);

		$gcOrders = array();
		foreach($tpl->gift_card_purchase_array as $number => $gc_purchase)
		{
			$gcOrders[$gc_purchase['order_confirm_id']] = $gc_purchase['order_confirm_id'];
		}

		$tpl->assign('orders', implode(',', $gcOrders));

		if (empty($gcOrders))
		{
			CApp::bounce();
		}
	}
}
?>