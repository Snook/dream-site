<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once('payment/PayPalProcess.php');
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CCouponCode.php');
require_once('includes/DAO/BusinessObject/CStore.php');
require_once('includes/DAO/BusinessObject/CFundraiser.php');
require_once('includes/DAO/BusinessObject/CMembershipHistory.php');
require_once('includes/DAO/BusinessObject/CProductPayment.php');
require_once('page/admin/fundraiser.php');

class processor_admin_user_membership extends CPageProcessor
{
	function runManufacturerStaff()
	{
		$this->runUserMembership();
	}

	function runFranchiseStaff()
	{
		$this->runUserMembership();
	}

	function runFranchiseLead()
	{
		$this->runUserMembership();
	}

	function runEventCoordinator()
	{
		$this->runUserMembership();
	}

	function runOpsLead()
	{
		$this->runUserMembership();
	}

	function runFranchiseManager()
	{
		$this->runUserMembership();
	}

	function runHomeOfficeStaff()
	{
		$this->runUserMembership();
	}

	function runHomeOfficeManager()
	{
		$this->runUserMembership();
	}

	function runSiteAdmin()
	{
		$this->runUserMembership();
	}

	function getProductOrderIDForMenuID($user_id, $menu_id, &$store_id)
	{
		$prodObj = new DAO();
		$prodObj->query("SELECT
					po.id as product_id,
       				po.store_id
					FROM product_orders po
					join product_orders_items poi on poi.product_orders_id = po.id and poi.product_membership_status = 'MEMBERSHIP_STATUS_CURRENT'
						join product_membership pm on poi.product_id = pm.product_id
					join store st on st.id = po.store_id
					where user_id = $user_id and po.is_deleted = 0  and $menu_id >= poi.product_membership_initial_menu  and $menu_id <= poi.product_membership_initial_menu + pm.term_months - 1
					order by poi.product_membership_initial_menu limit 1");

		if ($prodObj->fetch())
		{
			$store_id = $prodObj->store_id;
			return $prodObj->product_id;
		}

		$store_id = false;
		return false;
	}

	function runUserMembership()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'skip')
		{
			$User = DAO_CFactory::create('user');
			$User->id = $_POST['user_id'];

			if ($User->find(true))
			{
				$store_id = false;
				$product_orders_id = $this->getProductOrderIDForMenuID($User->id, $_POST['menu_id'], $store_id);

				$productOrderItem = DAO_CFactory::create('product_orders_items');
				$productOrderItem->product_orders_id = $product_orders_id;
				$productOrderItem->find(true);

				$orgOrderItem = clone $productOrderItem;

				$jsonArr = json_decode($productOrderItem->product_membership_hard_skip_menu);
				if (!is_array($jsonArr))
				{
					$jsonArr = array();
				}

				if (in_array($_POST['menu_id'], $jsonArr))
				{
					echo CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Menu already skipped'
					));
				}

				$jsonArr[] = $_POST['menu_id'];

				$productOrderItem->product_membership_hard_skip_menu = json_encode($jsonArr);
				$productOrderItem->update($orgOrderItem);

				$meta_arr = array(
					'store_id' => $store_id,
					'menu_id' => $_POST['menu_id']
				);
				CMembershipHistory::recordEvent($User->id, $productOrderItem->id, CMembershipHistory::HARD_SKIP, $meta_arr);

				echo CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Success'
				));
			}
			else
			{
				echo CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Guest not found.'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'unskip')
		{
			$User = DAO_CFactory::create('user');
			$User->id = $_POST['user_id'];

			if ($User->find(true))
			{
				$store_id = false;
				$product_orders_id = $this->getProductOrderIDForMenuID($User->id, $_POST['menu_id'],$store_id);

				$productOrderItem = DAO_CFactory::create('product_orders_items');
				$productOrderItem->product_orders_id = $product_orders_id;
				$productOrderItem->find(true);

				$orgOrderItem = clone $productOrderItem;

				$jsonArr = json_decode($productOrderItem->product_membership_hard_skip_menu);
				$newJsonArr = array();
				foreach ($jsonArr as $thisMenuID)
				{
					if ($thisMenuID != $_POST['menu_id'])
					{
						$newJsonArr[] = $thisMenuID;
					}
				}

				$productOrderItem->product_membership_hard_skip_menu = json_encode($newJsonArr);
				$productOrderItem->update($orgOrderItem);

				//CMembershipHistory::deleteHardSkipEvent($User->id, $_POST['menu_id']);
				// New Plan:   Just record a SKIP_REVOKED and only send the last event to occur that day
				$meta_arr = array(
					'store_id' => $store_id,
					'menu_id' => $_POST['menu_id']
				);
				CMembershipHistory::recordEvent($User->id, $productOrderItem->id, CMembershipHistory::SKIP_REVOKED, $meta_arr);


				echo CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Success'
				));
			}
			else
			{
				echo CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Guest not found.'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'reinstate')
		{
			$User = DAO_CFactory::create('user');
			$User->id = $_POST['user_id'];
			$membershipID = $_POST['membership_id'];

			if ($User->find(true))
			{
				$membershipData = $User->getMembershipStatus(false, false, $membershipID, false, false);

				if ($membershipData['status'] != CUser::MEMBERSHIP_STATUS_TERMINATED)
				{
					echo CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'The guest is not eligible for reinstatement.'
					));
				}

				if (empty($membershipData['ejection_menu_id']))
				{
					echo CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'The guest is not eligible for reinstatement.'
					));
				}

				if (count($membershipData['hard_skip_menus']) >= $membershipData['number_skips_allowed'])
				{
					echo CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'The guest is not eligible for reinstatement.'
					));
				}

				// Add the skip and update status
				$productOrderItem = DAO_CFactory::create('product_orders_items');
				$productOrderItem->id = $membershipID;
				$productOrderItem->find(true);

				$orgOrderItem = clone $productOrderItem;

				$jsonArr = json_decode($productOrderItem->product_membership_hard_skip_menu);
				if (!is_array($jsonArr))
				{
					$jsonArr = array();
				}

				$jsonArr[] = $_POST['menu_id'];

				$productOrderItem->product_membership_hard_skip_menu = json_encode($jsonArr);
				$productOrderItem->product_membership_status = CUser::MEMBERSHIP_STATUS_CURRENT;
				$productOrderItem->update($orgOrderItem);

				echo CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Success'
				));
			}
			else
			{
				echo CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Guest not found.'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'check_coupon')
		{
			$Coupon = DAO_CFactory::create('coupon_code');
			$Coupon->coupon_code = $_POST['coupon_code'];

			if ($Coupon->find(true))
			{
				$Order = DAO_CFactory::create('product_orders');
				$Order->user_id = $_POST['user_id'];
				$Order->store_id = $_POST['store_id'];

				$result = $Coupon->isValidForProduct($Order);

				if (!empty($result))
				{
					$errorString = "The coupon is not valid.<br />";
					foreach ($result as $thisError)
					{
						$errorString .= CCouponCode::getCouponErrorUserText($thisError) . "<br />";
					}

					echo CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => $errorString,
						'discount_var' => $Coupon->discount_var,
						'discount_method' => $Coupon->discount_method
					));
				}
				else
				{
					echo CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Success',
						'discount_var' => $Coupon->discount_var,
						'discount_method' => $Coupon->discount_method
					));
				}
			}
			else
			{
				echo CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Coupon not found.'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'cancel')
		{

			$User = DAO_CFactory::create('user');
			$User->id = $_POST['user_id'];

			if (!empty($_POST['membership_id']) && is_numeric($_POST['membership_id']))
			{
				if ($User->find(true))
				{
					$membershipData = $User->getMembershipStatus(false, false, $_POST['membership_id']);

					if ($membershipData['status'] == CUser::MEMBERSHIP_STATUS_CURRENT)
					{

						$membership_item = DAO_CFactory::create('product_orders_items');
						$membership_item->id = $membershipData['membership_id'];
						$membership_item->find(true);

						$order = DAO_CFactory::create('product_orders');
						$order->id = $membership_item->product_orders_id;
						$order->find(true);

						//refund cc
						$rslt = CProductPayment::refundForOrder($order);

						if ($rslt['result'] == 'success')
						{


							// update state
							$membership_item->query("update product_orders_items set product_membership_status = 'MEMBERSHIP_STATUS_REFUNDED' where product_orders_id = {$membership_item->product_orders_id} and is_deleted = 0");

							// record in history
							$meta_arr = array(
								'order_id' => $order->id,
								'membership_id' => $membership_item->id
							);
							CMembershipHistory::recordEvent($User->id, $membership_item->id, CMembershipHistory::MEMBERSHIP_CANCELLED, $meta_arr);
							//update future orders and remove discount - hold it ...there can be no future orders

							$conversionResult = $this->convertMembershipOrdersToNormal($membership_item->product_membership_initial_menu, $User->id, $membership_item->id);

							$currentMenu = CMenu::getMenuInfo(CMenu::getCurrentMenuId());
							$revenueEvent = DAO_CFactory::create('revenue_event');
							$revenueEvent->event_type = 'MEMBERSHIP_REFUND';
							$revenueEvent->event_time = date("Y-m-d H:i:s");
							$revenueEvent->store_id = $order->store_id;
							$revenueEvent->menu_id = $currentMenu['id'];
							$revenueEvent->amount = $order->grand_total - $order->subtotal_all_taxes;
							$revenueEvent->session_amount = $order->grand_total - $order->subtotal_all_taxes;
							$revenueEvent->session_id = 'null';
							$revenueEvent->final_session_id = 'null';
							$revenueEvent->order_id = 'null';
							$revenueEvent->membership_id = $membership_item->id;
							$revenueEvent->positive_affected_month = $currentMenu['menu_start'];
							$revenueEvent->negative_affected_month = 'null';
							$revenueEvent->insert();

							CAppUtil::processorMessageEcho(array(
								'processor_success' => true,
								'processor_message' => 'Success.',
								'conversion_result' => $conversionResult
							));
						}
						else
						{
							// refund failure
							CAppUtil::processorMessageEcho(array(
								'processor_success' => false,
								'processor_message' => 'Refund failed.'
							));
						}
					}
					else
					{
						// membership not found or in wrong state
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'Membership not valid.'
						));
					}
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'User not found.'
					));
				}
			}
			else
			{
				// invalid membership id
				// membership not found or in wrong state
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Membership ID not valid.'
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'terminate')
		{

			$User = DAO_CFactory::create('user');
			$User->id = $_POST['user_id'];

			if (!empty($_POST['membership_id']) && is_numeric($_POST['membership_id']))
			{
				if ($User->find(true))
				{
					$membershipData = $User->getMembershipStatus(false, false, $_POST['membership_id']);

					if ($membershipData['status'] == CUser::MEMBERSHIP_STATUS_CURRENT)
					{

						$membership_item = DAO_CFactory::create('product_orders_items');
						$membership_item->id = $membershipData['membership_id'];
						$membership_item->find(true);

						$order = DAO_CFactory::create('product_orders');
						$order->id = $membership_item->product_orders_id;
						$order->find(true);


						// update state
						$membership_item->query("update product_orders_items set product_membership_status = 'MEMBERSHIP_STATUS_TERMINATED' where product_orders_id = {$membership_item->product_orders_id} and is_deleted = 0");

						// record in history
						$meta_arr = array(
							'order_id' => $order->id,
							'membership_id' => $membership_item->id
						);
						CMembershipHistory::recordEvent($User->id, $membership_item->id, CMembershipHistory::MEMBERSHIP_TERMINATED, $meta_arr);

						$conversionResult = $this->convertMembershipOrdersToNormal($membership_item->product_membership_initial_menu, $User->id, $membership_item->id, true);


						CAppUtil::processorMessageEcho(array(
							'processor_success' => true,
							'processor_message' => 'Success.',
							'conversion_result' => $conversionResult
						));
					}
					else
					{
						// membership not found or in wrong state
						CAppUtil::processorMessageEcho(array(
							'processor_success' => false,
							'processor_message' => 'Membership not valid.'
						));
					}
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'User not found.'
					));
				}
			}
			else
			{
				// invalid membership id
				// membership not found or in wrong state
				CAppUtil::processorMessageEcho(array(
					'processor_success' => false,
					'processor_message' => 'Membership ID not valid.'
				));
			}
		}

		CAppUtil::processorMessageEcho(array(
			'processor_success' => false,
			'processor_message' => 'Invalid action.'
		));
	}

	function convertMembershipOrdersToNormal($menu_id, $user_id, $membership_id, $futureOnly = false)
	{
		$order_retriever = new DAO();

		$futureOnlyClause = "";
		if ($futureOnly)
		{
			$today = date("Y-m-d");
			$todayArr = explode("-", $today);
			$tomorrowTS = mktime(0,0,0,$todayArr[1],$todayArr[2] + 1, $todayArr[0]);
			$futureOnlyClause = " and session_start > '" . date("Y-m-d 00:00:00", $tomorrowTS) . "' ";
		}

		$order_retriever->query("select o.id from booking b
				join session s on s.id = b.session_id and s.menu_id >= $menu_id $futureOnlyClause
				join orders o on o.id = b.order_id and o.type_of_order = 'STANDARD' and (o.membership_discount > 0 or o.membership_id > 0)
				where b.user_id = $user_id and b.status = 'ACTIVE' and b.is_deleted = 0");

		if ($order_retriever->N > 0)
		{
			require_once('processor/admin/order_mgr_processor.php');
			$proc = new processor_admin_order_mgr_processor();
		}

		$results = array();

		while ($order_retriever->fetch())
		{

			$proc->prepareForDirectCall(false, false, $user_id, $order_retriever->id);
			$result = $proc->ConvertOrderFromMembership(true);

			if ($result['processor_success'])
			{
				$results[$order_retriever->id] = 'success';
			}
			else
			{
				$results[$order_retriever->id] = $result['processor_message'];
			}
		}

		return $results;
	}

}

?>