<?php // page_order_tahnkyou.php
 require_once("includes/CPageAdminOnly.inc");

require_once("page/customer/order_details.php");
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');

class page_admin_gc_only_order_thankyou extends CPageAdminOnly {

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


	function run() {

		$tpl = CApp::instance()->template();

		$cardPurchase = array();

		if (!isset($_REQUEST['gcOrders']))
		{
			throw new Exception('did not receive GD array in gc_only_order__thankyou');
		}

		$IDs = explode("|", CGPC::do_clean($_REQUEST['gcOrders'],TYPE_STR));
        CGiftCard::addOrderDrivenGiftCardDetailsToTemplate($tpl, NULL, false, $IDs, false, false);

		//logout user after order in storeview
		if ( CApp::$isStoreView ) {
			$session = CBrowserSession::instance()->ExpireSession();
		}


	}

}


?>