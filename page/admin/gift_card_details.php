<?php // page_order_tahnkyou.php
 require_once("includes/CPageAdminOnly.inc");

require_once("page/customer/order_details.php");
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');

class page_admin_gift_card_details extends CPageAdminOnly {

	var $canModify = false;

 	function runSiteAdmin()
 	{
 			$this->canModify = true;
            $this->run();
	}
	function runHomeOfficeStaff()
	{
            $this->run();
	}
	function runHomeOfficeManager()
	{
		 	$this->canModify = true;
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

		if (!isset($_REQUEST['gcOrder']))
		{
			throw new Exception('did not receive ID in gift_card_details');
		}

		$OrderRetreiver = DAO_CFactory::create('gift_card_order');
        $gcOrder = CGPC::do_clean($_REQUEST['gcOrder'],TYPE_STR);
		$OrderRetreiver->query("select GROUP_CONCAT(id) as IDs, user_id from gift_card_order where cc_ref_number = '{$gcOrder}' group by cc_ref_number" );
		$OrderRetreiver->fetch();

		$IDs = explode("," ,$OrderRetreiver->IDs);


		CGiftCard::addOrderDrivenGiftCardDetailsToTemplate($tpl, $OrderRetreiver->user_id, false, $IDs, $this->canModify, false);


		$tpl->assign('can_resend', true);

		if (isset($_POST['action']) && $_POST['action'] == 'send_receipt')
		{
		    $thisOrder = DAO_CFactory::create('gift_card_order');

	       	$thisOrder->query("select * from gift_card_order where id in (" . implode(',', $IDs) . ") and is_deleted = 0");

	       	$ordersArray = array();
	       	$order_total = 0;
	       	while($thisOrder->fetch())
	       	{
	       		$order_total += ($thisOrder->initial_amount + $thisOrder->s_and_h_amount);
	       		$ordersArray[] = clone($thisOrder);
	       	}


			if (CGiftCard::resend_receipt($ordersArray, $order_total))
			{
				$tpl->assign('confirm', true);
				$tpl->assign('successMsg', "The receipt email has been resent.");
			}
			else
			{
				$tpl->assign('error', true);
				$tpl->assign('errorMsg', "An error occurred. The receipt email was not sent.");
			}
		}

		if (isset($_POST['action']) && $_POST['action'] == 'send_egift_card' && isset($_POST['order_id']))
		{
		    $thisOrder = DAO_CFactory::create('gift_card_order');
			$thisOrder->id = CGPC::do_clean($_POST['order_id'],TYPE_INT);
			$thisOrder->find(true);

			// check to see if this the number was sent recently

			if (isset($thisOrder->last_resend_time) && (time() - strtotime($thisOrder->last_resend_time) < 1800))
			{
				$tpl->assign('error', true);
				$tpl->assign('errorMsg', "The eGift card can only be resent once every 30 minutes. Please wait and try again.");

			}
			else
			{
				if (CGiftCard::resend_eGift_card($thisOrder))
				{
                    $order_id = CGPC::do_clean($_POST['order_id'],TYPE_INT);
						$nowish = date("Y-m-d H:i:s");
						$thisOrder->query("update gift_card_order set last_resend_time = '$nowish' where id = '{$order_id}'" );
						$tpl->assign('confirm', true);
						$tpl->assign('successMsg', "The egift card has been resent.");
				}
				else
				{
						$tpl->assign('error', true);
						$tpl->assign('errorMsg', "An error occurred. The egift card was not sent.");
				}
			}
		}

		if (isset($_POST['action']) && $_POST['action'] == 'modify_email' && isset($_POST['order_id']) && $this->canModify)
		{
			CApp::bounce('/backoffice/modify_egift_email?gcOrder=' .CGPC::do_clean($_POST['order_id'],TYPE_INT));
		}

	}



}