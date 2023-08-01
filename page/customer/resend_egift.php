<?php // page_order_tahnkyou.php

require_once("page/customer/order_details.php");
require_once('includes/DAO/BusinessObject/COrders.php');
require_once('includes/DAO/BusinessObject/CCouponCodeProgram.php');
require_once('includes/DAO/BusinessObject/CGiftCard.php');

class page_resend_egift extends CPage {

	function runPublic() {
            $this->run();
	}

	function run() {

		$tpl = CApp::instance()->template();

		if (isset($_REQUEST['oid']))
		{
		    $thisOrder = DAO_CFactory::create('gift_card_order');
			$thisOrder->order_confirm_id = $_REQUEST['oid'];
			if ($thisOrder->find(true))
			{

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
						$nowish = date("Y-m-d H:i:s");
						$thisOrder->query("update gift_card_order set last_resend_time = '$nowish' where order_confirm_id = '{$_REQUEST['oid']}'" );
						$tpl->assign('confirm', true);
						$tpl->assign('successMsg', "The eGift card email has been resent.");
					}
					else
					{
						$tpl->assign('error', true);
						$tpl->assign('errorMsg', "An error occurred. The eGift card email was not sent.");
					}
				}


			}
			else
			{
				$tpl->assign('error', true);
				$tpl->assign('errorMsg', "An error occurred. The eGift card email was not sent.");
			}

		}
		else
		{
			$tpl->assign('error', true);
			$tpl->assign('errorMsg', "An error occurred. The eGift card email was not sent.");
		}

	}



}


?>