<?php
require_once('includes/CCart2.inc');
require_once("includes/CPageProcessor.inc");
require_once("CTemplate.inc");
require_once("page/admin/order_mgr.php");
require_once('payment/PayPalProcess.php');
require_once('includes/class.inputfilter_clean.php');
require_once('includes/DAO/BusinessObject/CDreamTasteEvent.php');
require_once('includes/DAO/BusinessObject/CBundle.php');
require_once('includes/DAO/BusinessObject/CFundraiser.php');
require_once('includes/DAO/BusinessObject/CEmail.php');
require_once("includes/CDashboardReport.inc");

class processor_admin_order_mgr_processor extends CPageProcessor
{
	private $targetSession = false;
	private $org_session_id = false;
	private $store_id = false;
	private $user_id = false;
	private $order_id = false;
	private $booking_id = false;

	const dd_general_exception_code = 20000;
	const dd_taste_session_exception_code = 20001;
	const dd_std_session_exception_code = 20002;
	const dd_dinner_dollars_exception_code = 20003;
	public $orderState;

	function __construct()
	{
		$this->inputTypeMap['ltdOrderedMealsArray'] = TYPE_NOCLEAN;
		$this->inputTypeMap['order_notes'] = TYPE_NOCLEAN;
	}

	public function prepareForDirectCall($targetSession, $store_id, $user_id, $order_id = false)
	{
		$this->targetSession = $targetSession;
		$this->store_id = $store_id;
		$this->user_id = $user_id;
		$this->order_id = $order_id;
	}

	public function getOrderID()
	{
		return $this->order_id;
	}

	function runSiteAdmin()
	{
		$this->runFranchiseOwner();
	}

	function runCustomer()
	{

		if (!empty($_REQUEST['op']) && ($_REQUEST['op'] == 'get_token' || $_REQUEST['op'] == 'get_token_and_save_order' || $_REQUEST['op'] == 'delete_temp_order'))
		{
			$this->runFranchiseOwner();
		}
		else
		{

			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'You do not have access privileges to run this process. Your session may have expired and you may need to log in again.'
			));
			exit;
		}
	}

	function runPublic()
	{
		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_token')
		{
			$this->runFranchiseOwner();
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'You do not have access privileges to run this process. Your session may have expired and you may need to log in again.'
			));
			exit;
		}
	}

	function runOpsSupport()
	{
		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_history')
		{
			$this->runFranchiseOwner();
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'You do not have access privileges to run this process. Your session may have expired and you may need to log in again.'
			));
			exit;
		}
	}

	function runFranchiseStaff()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseLead()
	{
		$this->runFranchiseOwner();
	}

	function runEventCoordinator()
	{
		$this->runFranchiseOwner();
	}

	function runOpsLead()
	{
		$this->runFranchiseOwner();
	}

	function runHomeOfficeManager()
	{
		$this->runFranchiseOwner();
	}

	function runFranchiseOwner()
	{
		$this->runOrderMgrProcessor();
	}

	function runOrderMgrProcessor()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		//allow tages in the changeListStr
		$tempStr = "";
		if (!empty($_POST['changeListStr']))
		{
			$tempStr = $_POST['changeListStr'];
		}
		$xssFilter = new InputFilter();
		$_POST = $xssFilter->process($_POST);
		$_POST['changeListStr'] = $tempStr;

		if (isset($_REQUEST['store_id']) && is_numeric($_REQUEST['store_id']))
		{
			$this->store_id = $_REQUEST['store_id'];

			// Is this order editable by the current user?
			// TODO: isolate this to only the order manager
			if (false && !CStore::userHasAccessToStore($this->store_id))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'You do not have access privileges to this store.'
				));
				exit;
			}
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The store id is invalid.'
			));
			exit;
		}

		if (!empty($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']))
		{
			$this->user_id = $_REQUEST['user_id'];
		}
		else if ($_REQUEST['op'] != 'get_token')
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The user id is invalid.'
			));
			exit;
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'set_session_and_save')
		{
			if (!empty($_REQUEST['session_id']) && is_numeric($_REQUEST['session_id']))
			{
				$this->targetSession = $_REQUEST['session_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The session id is invalid.'
				));
				exit;
			}

			$this->setSessionAndSave();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'reschedule')
		{
			if (!empty($_REQUEST['session_id']) && is_numeric($_REQUEST['session_id']))
			{
				$this->targetSession = $_REQUEST['session_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The target session id is invalid.'
				));
				exit;
			}

			if (!empty($_REQUEST['org_session_id']) && is_numeric($_REQUEST['org_session_id']))
			{
				$this->org_session_id = $_REQUEST['org_session_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The original session id is invalid.'
				));
				exit;
			}

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->reschedule();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'update_items')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->updateItems();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'update_active_order')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->updateActiveOrder();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'convert_order_to_membership')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			if (!empty($_REQUEST['membership_id']) && is_numeric($_REQUEST['membership_id']))
			{
				$membership_id = $_REQUEST['membership_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The membership id is invalid.'
				));
				exit;
			}

			$this->ConvertOrderToMembership($membership_id);
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'convert_order_from_membership')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->ConvertOrderFromMembership();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_token')
		{
			$this->getToken();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_token_and_save_order')
		{
			if (!empty($_REQUEST['session_id']) && is_numeric($_REQUEST['session_id']))
			{
				$this->targetSession = $_REQUEST['session_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The target session id is invalid.'
				));
				exit;
			}

			$this->getToken(true); // true meaning "save an order"

		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'update_discounts')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->updateDiscounts();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'save_payment')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->savePaymentAndBookOrder();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'add_payment')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->addPayment();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'delete_saved_order')
		{
			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->deleteSavedOrder();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'get_history')
		{
			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->getOrderHistory();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'cancel_preflight')
		{
			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->cancel_preflight();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'cancel')
		{
			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->do_cancel();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'retrieve_related_orders')
		{
			if (!empty($_REQUEST['session_id']) && is_numeric($_REQUEST['session_id']))
			{
				$session_id = $_REQUEST['session_id'];
			}
			else
			{
				$session_id = false;
			}

			$this->retrieveRelatedOrders($session_id);
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'update_status_flag')
		{
			if (CUser::getCurrentUser()->user_type != 'SITE_ADMIN')
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'No permission.'
				));
				exit;
			}

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->updateOrderStatusFlag();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'update_special_instructions')
		{
			if ($_POST['do'] == 'get')
			{
				$orderNote = DAO_CFactory::create('orders');
				$orderNote->query("SELECT order_user_notes FROM orders WHERE id = '" . $_POST['order_id'] . "' AND is_deleted = '0'");
				$orderNote->fetch();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Order special instructions retrieved.',
					'special_instruction_note' => $orderNote->order_user_notes
				));
				exit;
			}
			else if ($_POST['do'] == 'save')
			{
				//require_once('includes/class.inputfilter_clean.php');
				//$xssFilter = new InputFilter();
				//$set_note = $xssFilter->process($_POST['note']);
				//ALREADY CLEANED

				$note = str_replace(array(
					"\r",
					"\r\n",
					"\n"
				), ' ', strip_tags($_POST['note']));

				$note = stripcslashes($note); // using Object oriented approach so cleaning will occur in DAO code

				$orderNote = DAO_CFactory::create('orders');
				$orderNote->id = $_POST['order_id'];
				$orderNote->fetch();

				$orderNote->order_user_notes = $note;
				$orderNote->update();

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Order special instructions updated.',
					'special_instruction_note' => $orderNote->order_user_notes
				));
				exit;
			}
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'delete_temp_order')
		{

			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The order id is invalid.'
				));
				exit;
			}

			$this->deleteSavedOrder();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'error_recovery')
		{
			// a timeout or other network related error during an ajax call occurred leaving the client without a CRSF token
			// this function will return a new token but only if the Referrer is a valid Dream Dinners site. The client must also have a valid session id as usual.
			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}

			$this->error_recovery();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'save_delivery_address')
		{
			// a timeout or other network related error during an ajax call occurred leaving the client without a CRSF token
			// this function will return a new token but only if the Referrer is a valid Dream Dinners site. The client must also have a valid session id as usual.
			if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
			{
				$this->order_id = $_REQUEST['order_id'];
			}

			if (!empty($_REQUEST['shipping_data']))
			{
				// TODO: clean array of shipping data

				$this->saveDeliveryAddress($_REQUEST['shipping_data']);
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address. Data not provided.',
				));
				exit;
			}
		}
	}

	function saveDeliveryAddress($shippingData)
	{
		$thisOrder = DAO_CFactory::create('orders');
		$thisOrder->id = $this->order_id;
		if ($thisOrder->find(true))
		{

			$thisOrder->orderAddress();

			$shipping_phone_number = $shippingData['shipping_phone_number'];
			$shipping_phone_number_new = $shippingData['shipping_phone_number_new'];
			$shipping_address_note = $shippingData['shipping_address_note'];
			$shipping_email_address = (!empty($shippingData['shipping_email_address']) ? $shippingData['shipping_email_address'] : false);

			if (empty($shipping_email_address) || !ValidationRules::validateEmail($shipping_email_address))
			{
				$shipping_email_address = 'null';
			}

			/*
			 * This is just risk for Home Delivery since this may not be tested.  This IS implemented in
			 *  order_manager_processor_delivered

			if (!empty($shipping_email_address) && !ValidationRules::validateEmail($shipping_email_address))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address. Invalid Email Address.',
				));
				exit;
			}
			*/

			$thisOrder->orderAddress->firstname = $shippingData['shipping_firstname'];
			$thisOrder->orderAddress->lastname = $shippingData['shipping_lastname'];
			$thisOrder->orderAddress->address_line1 = $shippingData['shipping_address_line1'];
			$thisOrder->orderAddress->address_line2 = $shippingData['shipping_address_line2'];
			$thisOrder->orderAddress->city = $shippingData['shipping_city'];
			$thisOrder->orderAddress->state_id = $shippingData['shipping_state_id'];
			$thisOrder->orderAddress->postal_code = $shippingData['shipping_postal_code'];
			$thisOrder->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
			$thisOrder->orderAddress->address_note = trim(strip_tags($shipping_address_note));
			$thisOrder->orderAddress->email_address = $shipping_email_address;
			$thisOrder->orderAddress->is_gift = (!empty($shippingData['shipping_is_gift']) ? 1 : 0);

			$updater = new DAO();
			if (empty($thisOrder->orderAddress->is_gift))
			{
				// update meal rating user.  If not a gift the user is the same as the ordering user
				// otherwise set as null. What if it's already been added by other means?
				$updater->query("update orders set my_meals_rating_user_id = user_id where id = " . $thisOrder->id);
			}
			else
			{
				$updater->query("update orders set my_meals_rating_user_id = null where id = " . $thisOrder->id);
			}

			$thisOrder->orderAddressDeliveryProcessUpdate();

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'The delivery address was saved..'
			));
			exit;
		}

		echo json_encode(array(
			'processor_success' => false,
			'processor_message' => 'A problem occurred while saving the delivery address. Order not found.',
		));
		exit;
	}

	function error_recovery()
	{
		// Delete booking, order, order_items if they exist

		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], HTTPS_SERVER) !== false)
		{

			if (!empty($this->order_id))
			{
				$bookingObj = DAO_CFactory::create('booking');
				$bookingObj->query("update booking set is_deleted = 1 where order_id = {$this->order_id} and status = 'SAVED'");

				$orderObj = DAO_CFactory::create('booking');
				$orderObj->query("update orders set is_deleted = 1 where id = {$this->order_id}");

				$orderObj = DAO_CFactory::create('booking');
				$orderObj->query("update order_item set is_deleted = 1 where order_id = {$this->order_id}");
			}

			// provide a new CSRF token

			$csrftoken = CSRF::getNewToken('customer_checkout');

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'The recovery is complete.',
				'token' => $csrftoken
			));
			exit;
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The recovery is complete.',
			));
			exit;
		}
	}

	function updateOrderStatusFlag($flag = false, $status = false, $echoResponse = true)
	{
		if (isset($_POST['flag']))
		{
			$switchFlag = $_POST['flag'];
		}

		$newStatus = 0;

		if (isset($_POST['new_state']))
		{
			if ($_POST['new_state'] == "on")
			{
				$newStatus = 1;
			}
		}

		// use passed in values over post values
		if ($flag !== false)
		{
			$switchFlag = $flag;
		}

		if ($status !== false)
		{
			$newStatus = $status;
		}

		switch ($switchFlag)
		{
			case "in-store_status":
				{
					// In Store Flag Intercept Point
					// brute force setting of flag
					$DigestWasUpdated = COrdersDigest::updateInStoreStatus($this->order_id, $newStatus);

					if ($newStatus)
					{

						// Check if order was confirmed
						$pointsObj = DAO_CFactory::create('points_user_history');
						$pointsObj->query("select * from points_user_history where event_type = 'ORDER_CONFIRMED' and is_deleted = 0 and order_id = {$this->order_id}");

						if ($pointsObj->N)
						{
							// it was so now what?
							$pointsObj->fetch();

							$OrderObj = DAO_CFactory::create('orders');
							$OrderObj->id = $this->order_id;
							$OrderObj->find(true);

							// need to get multiplier ... at the time of the order? For now, yes
							if ($OrderObj->is_in_plate_points_program)
							{

								$PointsBasis = $OrderObj->grand_total - $OrderObj->subtotal_all_taxes;
								$isInStore = $newStatus;

								if ($isInStore)
								{
									list($levelDetail, $nextLevelDetail) = CPointsUserHistory::getLevelDetailsByPoints($pointsObj->total_points - $pointsObj->points_allocated);
									$multiplier = $levelDetail['rewards']['in_store_multiplier'];
									$PointsBasis = ($PointsBasis * $multiplier);
								}

								$PointsBasis = floor($PointsBasis);
								$diff = $PointsBasis - $pointsObj->points_allocated;

								if ($diff > 0)
								{
									$orgPoints = clone($pointsObj);
									$pointsObj->points_allocated = $PointsBasis;
									$pointsObj->total_points += $diff;
									$pointsObj->update($orgPoints);

									// update total points from this point on
									$totalsUpdate = DAO_CFactory::create('points_user_history');
									$totalsUpdate->query("select id, total_points from points_user_history where user_id = {$OrderObj->user_id} and id > {$pointsObj->id} and is_deleted = 0 order by id asc");
									while ($totalsUpdate->fetch())
									{
										$updater = DAO_CFactory::create('points_user_history');
										$newPoints = $totalsUpdate->total_points + $diff;
										$updater->query("update points_user_history set total_points = $newPoints where id = {$totalsUpdate->id} ");
									}
								}
							}
						}

						if ($DigestWasUpdated)
						{
							$OrderObj = DAO_CFactory::create('orders');
							$OrderObj->query("update orders set in_store_order = $newStatus, is_multiplier_eligible = $newStatus  where id = {$this->order_id}");

							COrdersDigest::updateLastActivityDate($this->store_id, date("Y-m-d H:i:s"));
						}
						else
						{
							if ($echoResponse)
							{
								echo json_encode(array(
									'processor_success' => false,
									'processor_message' => 'No reasonable trigger order found.'
								));
								exit;
							}
						}

						if ($echoResponse)
						{
							echo json_encode(array(
								'processor_success' => true,
								'processor_message' => 'Status was updated.'
							));
							exit;
						}
					}
					else
					{
						$OrderObj = DAO_CFactory::create('orders');
						$OrderObj->query("update orders set in_store_order = $newStatus, is_multiplier_eligible = $newStatus where id = {$this->order_id}");

						COrdersDigest::updateLastActivityDate($this->store_id, date("Y-m-d H:i:s"));

						if ($echoResponse)
						{
							echo json_encode(array(
								'processor_success' => true,
								'processor_message' => 'Status was updated.'
							));
							exit;
						}
					}
				}
				break;
			case "plate_points_status":
				{
					$newStatus = 0;
					if ($_POST['new_state'] == "on")
					{
						$newStatus = 1;
					}

					$OrderObj = DAO_CFactory::create('orders');
					$OrderObj->query("update orders set is_in_plate_points_program = $newStatus where id = {$this->order_id}");

					if ($echoResponse)
					{
						echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Status was updated.'
						));
						exit;
					}
				}
				break;
			default:
				if ($echoResponse)
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Invalid flag.'
					));
					exit;
				}
				break;
		}
	}

	function retrieveRelatedOrders($session_id)
	{
		$tpl = new CTemplate();

		if (!empty($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']))
		{
			$order_id = $_REQUEST['order_id'];
		}
		else
		{
			$order_id = false;
		}

		$anyOrder = false;

		$bookings = false;
		if ($session_id)
		{
			$sessionObj = DAO_CFactory::create('session');
			$sessionObj->id = $session_id;
			$sessionObj->find(true);

			$bookings = $sessionObj->getBookingsForSession();
			reset($bookings);
			$anyOrder = current($bookings);
		}

		$tpl->assign('current_order', $order_id);

		if ($anyOrder)
		{
			$tpl->assign('session_start', $anyOrder['session_start']);
		}

		$recentTimeLimit = time() - (86400 * 120);
		$mySQLDateTimeLimit = date("Y-m-d H:i:s", $recentTimeLimit);

		$query = "SELECT
				booking.status,
				booking.order_id,
				orders.user_id,
				orders.order_user_notes,
				orders.order_admin_notes,
				orders.order_confirmation,
				orders.grand_total,
				orders.type_of_order,
				orders.servings_total_count,
				orders.timestamp_created,
				orders.store_id,
				session.session_start,
				session.menu_id AS idmenu,
				store.store_name,
				store.timezone_id,
                user.firstname,
                user.lastname
				FROM orders
				JOIN booking ON booking.order_id = orders.id and (booking.status = 'ACTIVE' or booking.status = 'SAVED')
				JOIN store ON store.id = orders.store_id
				JOIN session ON booking.session_id=session.id
				JOIN user ON user.id=orders.user_id
            	WHERE orders.user_id = '" . $this->user_id . "'
				AND orders.is_deleted = 0
                AND orders.timestamp_created > '$mySQLDateTimeLimit'
				ORDER BY orders.timestamp_created DESC";

		$recentOrders = DAO_CFactory::create('orders');
		$recentOrders->query($query);

		$recentOrdersArr = array();

		$foundUserOrders = false;
		while ($recentOrders->fetch())
		{

			$now = CTimezones::getAdjustedServerTimeWithTimeZoneID($recentOrders->timezone_id);

			$canView = true;
			$canEdit = true;
			$sessionTS = strtotime($recentOrders->session_start);

			if ($now > $sessionTS && $recentOrders->status == 'ACTIVE')
			{
				$recentOrders->status = "COMPLETE";
			}

			$recentOrdersArr[$recentOrders->order_id] = $recentOrders->toArray();
			$recentOrdersArr[$recentOrders->order_id]['balance_due'] = COrdersDigest::calculateAndAddBalanceDue($recentOrders->order_id, $recentOrders->grand_total);
			$foundUserOrders = true;

			if ($recentOrders->status != CBooking::ACTIVE && $recentOrders->status != CBooking::SAVED)
			{
				$canEdit = false;
			}

			if ($recentOrders->status == CBooking::SAVED)
			{
				$canView = false;
			}

			if ($canEdit)
			{
				// check for orders in previous month if current day is greater than 6
				$day = date("j", $now);
				$month = date("n", $now);
				$year = date("Y", $now);

				if ($day > 6)
				{
					$cutOff = mktime(0, 0, 0, $month, 1, $year);
					if ($sessionTS < $cutOff)
					{
						$canEdit = false;
					}
				}
				else
				{
					$cutOff = mktime(0, 0, 0, $month - 1, 1, $year);
					if ($sessionTS < $cutOff)
					{
						$canEdit = false;
					}
				}
			}

			if ($recentOrders->status == CBooking::ACTIVE)
			{
				$recentOrders->status = 'COMPLETED';
			}

			$recentOrdersArr [$recentOrders->order_id]['canEdit'] = $canEdit;
			$recentOrdersArr [$recentOrders->order_id]['canView'] = $canView;
		}

		$CustomerName = "";
		if ($foundUserOrders)
		{
			$CustomerName = $recentOrders->firstname . " " . $recentOrders->lastname;
		}
		else
		{
			$userObj = DAO_CFactory::create('user');
			$userObj->query("select CONCAT(firstname, ' ', lastname) as customer from user where id = {$this->user_id}");
			$userObj->fetch();
			$CustomerName = $userObj->customer;
		}

		$tpl->assign('session_orders', $bookings);
		$tpl->assign('user_orders', $recentOrdersArr);
		$tpl->assign('customerName', $CustomerName);

		$data = $tpl->fetch('admin/order_mgr_related_orders.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'Related Orders retrieval Succes.',
			'data' => $data
		));
	}

	function do_cancel()
	{
		if (!empty($_POST['order_notes']))
		{
			$xssFilter = new InputFilter();
			$set_note = $xssFilter->process($_POST['order_notes']);

			$note = str_replace(array(
				"\r",
				"\r\n",
				"\n"
			), ' ', strip_tags($set_note));

			$orderNote = DAO_CFactory::create('orders');
			$orderNote->id = $this->order_id;
			$orderNote->fetch();

			$orderNote->order_admin_notes = $note;
			$orderNote->update();
		}

		$OrderObj = DAO_CFactory::create('orders');
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);
		$OrderObj->reconstruct();
		$Session = $OrderObj->findSession(true);
		$menu_id = (!empty($Session->menu_id) ? $Session->menu_id : false);
		$OrderObj->refreshForEditing($menu_id);
		$OrderObj->recalculate(true, false);

		$paymentArr = $_POST['paymentList'];

		$creditArray = array();
		$cancelArray = array();
		if (!empty($paymentArr))
		{
			foreach ($paymentArr as $k => $v)
			{
				if (strpos($k, "Amt") === 0 && !empty($v))
				{
					$amtToCredit = $v;
					$paymentId = substr($k, 3);
					$checkBoxName = "id" . $paymentId;

					// is credit checbox checked for this payment
					if (isset($paymentArr[$checkBoxName]))
					{
						$creditArray[$paymentId] = sprintf("%01.2f", $amtToCredit);
					}
				}

				if (strpos($k, "Dgc") === 0 && !empty($v))
				{
					$amtToCredit = $v;
					$paymentId = substr($k, 3);
					$creditArray[$paymentId] = sprintf("%01.2f", $amtToCredit);
				}

				if (strpos($k, "Pnd") === 0 && !empty($v))
				{
					$amtToCredit = $v;
					$paymentId = substr($k, 3);
					$cancelArray[$paymentId] = sprintf("%01.2f", $amtToCredit);
				}
			}
		}

		$cancelReason = false;
		if (isset($_POST['reason']))
		{
			$cancelReason = $_POST['reason'];
		}

		$declined_MFY = false;
		if (isset($_POST['declined_MFY']) && $_POST['declined_MFY'] == 'true')
		{
			$declined_MFY = true;
		}

		$declined_Reschedule = false;
		if (isset($_POST['declined_Reschedule']) && $_POST['declined_Reschedule'] == 'true')
		{
			$declined_Reschedule = true;
		}

		$suppressEmail = false;
		if (isset($_POST['suppress_cancel_email']) && $_POST['suppress_cancel_email'] == 'true')
		{
			$suppressEmail = true;
		}

		$result = $OrderObj->cancel($creditArray, $cancelReason, $declined_MFY, $declined_Reschedule, $suppressEmail, $cancelArray);

		if (!$result)
		{
			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'A problem occurred when cancelling this order. Please contact Dream Dinners support.'
			));
			exit;
		}
		else
		{

			$tpl = new CTemplate();
			$tpl->assign('cancel_result_array', $result);
			$html = $tpl->fetch('admin/subtemplate/cancel_success.tpl.php');

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'The cancellation was successful.',
				'data' => $html
			));
			exit;
		}
	}

	function cancel_preflight()
	{
		$OrderObj = DAO_CFactory::create('orders');
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);
		$OrderObj->reconstruct();
		$sessionObj = $OrderObj->findSession(true);
		$menu_id = (!empty($sessionObj->menu_id) ? $sessionObj->menu_id : false);

		$OrderObj->refreshForEditing($menu_id);
		$OrderObj->recalculate(true, false);

		// get user and store names
		$UserObj = DAO_CFactory::create('user');
		$UserObj->query("select CONCAT(u.firstname, ' ', u.lastname) as customer_name, s.store_name, u.dream_rewards_version, u.dream_reward_status from user u
				join store s on s.id = {$sessionObj->store_id}
				where u.id = {$OrderObj->user_id}");
		$UserObj->fetch();

		list ($paymentArray, $refundableAmount) = $OrderObj->cancel_preflight();

		$tpl = new CTemplate();
		$tpl->assign('user', $UserObj);
		$tpl->assign('session', $sessionObj);
		$tpl->assign('order', $OrderObj);

		$inPP = ($UserObj->dream_rewards_version == 3 && ($UserObj->dream_reward_status == 1 || $UserObj->dream_reward_status == 3));
		$tpl->assign('is_in_plate_points', $inPP);

		if (isset($paymentArray[0]))
		{
			$tpl->assign('cancelErrorMsg', $paymentArray[0]);
			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => $paymentArray[0]
			));
			exit;
		}
		else
		{
			$tpl->assign('cancelError', false);
			$tpl->assign('paymentArray', $paymentArray);
			$tpl->assign('refundableAmount', $refundableAmount);
		}

		$html = $tpl->fetch('admin/subtemplate/cancel_preflight.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The cancel_preflight was successful.',
			'data' => $html,
			'orderInfo' => $OrderObj
		));
		exit;
	}

	function getOrderHistory()
	{
		list($historyArray, $bookingObj) = COrders::getOrderHistory($this->order_id);

		$tpl = new CTemplate();
		$tpl->assign('orders_history', $historyArray);
		$tpl->assign('storeInfo', array('id' => $this->store_id));
		$html = $tpl->fetch('admin/subtemplate/individual_order_history.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The history is available.',
			'booking_id' => $bookingObj->id,
			'order_id' => $bookingObj->order_id,
			'html' => $html
		));
		exit;
	}

	function deleteSavedOrder()
	{
		// Delete booking, order, order_items

		$bookingTestObj = DAO_CFactory::create('booking');
		$bookingTestObj->order_id = $this->order_id;
		$bookingTestObj->status = CBooking::ACTIVE;
		$bookingTestObj->find();

		if ($bookingTestObj->N > 0)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The order has been activated and cannot be deleted. This may be due to network congestion or activity from another BackOffice user. The page will now refresh to show the current state of the order.',
				'doRefresh' => true
			));
			exit;
		}

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->order_id = $this->order_id;
		$bookingObj->status = CBooking::SAVED;

		if ($bookingObj->find(true))
		{
			$bookingObj->delete();
		}

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The saved order was deleted.',
			'user_id' => $this->user_id
		));
		exit;
	}

	function addPayment()
	{
		CLog::RecordDebugTrace('order_mgr_processor::addPayment called for order: ' . $this->order_id, "TR_TRACING");

		if (empty($_POST['dd_csrf_token']) || (!CSRF::validate($_POST['dd_csrf_token'], 'om_payment') && !CSRF::validate($_POST['dd_csrf_token'], 'om_payment_2')))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => CSRF_Fail_Msg
			));
			exit;
		}

		$amount = isset($_REQUEST['payment_data']['amount']) ? $_REQUEST['payment_data']['amount'] : 0;
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : false;

		// validate the amount field first
		if (!is_numeric($amount))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The amount submitted is not a number'
			));
			exit;
		}

		if ($amount < 0)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The amount submitted is less than zero'
			));
			exit;
		}

		if ($_REQUEST['payment_data']['amount'] > 3000)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The amount submitted is greater than $3000. Please contact the home office if this is a legitimate amount.'
			));
			exit;
		}

		$paymentObj = DAO_CFactory::create('payment');
		$paymentObj->store_id = $this->store_id;
		$paymentObj->user_id = $this->user_id;
		$paymentObj->order_id = $this->order_id;

		// Type
		if (!empty($_REQUEST['payment_data']['type']))
		{
			$paymentObj->payment_type = $_REQUEST['payment_data']['type'];
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The payment type is invalid.'
			));
			exit;
		}

		if ($paymentObj->payment_type == 'CC')
		{

			$paymentObj->setCCInfo($_REQUEST['payment_data']['number'], $_REQUEST['payment_data']['ccMonth'], $_REQUEST['payment_data']['ccYear'], $_REQUEST['payment_data']['ccNameOnCard'], $_REQUEST['payment_data']['billing_address'], null, null, $_REQUEST['payment_data']['billing_postal_code'], $_REQUEST['payment_data']['ccType'], (empty($_REQUEST['payment_data']['cc_security_code']) ? false : $_REQUEST['payment_data']['cc_security_code']));
			if (!empty($_REQUEST['payment_data']['do_not_ref']))
			{
				$paymentObj->do_not_reference = 1;
			}

			$paymentObj->is_delayed_payment = 0;

			if ($_REQUEST['payment_data']['is_store_specific_flat_rate_deposit_delayed_payment'] == '1')
			{
				$paymentObj->is_delayed_payment = 4;
				$paymentObj->delayed_payment_status = CPayment::PENDING;
			}
			if ($_REQUEST['payment_data']['is_store_specific_flat_rate_deposit_delayed_payment'] == '2')
			{
				$paymentObj->is_delayed_payment = 5;
				$paymentObj->delayed_payment_status = CPayment::PENDING;
			}

			$paymentObj->is_deposit = 0;
		}
		else
		{
			$paymentObj->is_delayed_payment = 0;
			$paymentObj->is_deposit = 0;
		}

		if ($paymentObj->payment_type == 'GIFT_CERT')
		{

			if (!empty($_REQUEST['payment_data']['cert_type']))
			{
				$paymentObj->gift_cert_type = $_REQUEST['payment_data']['cert_type'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The gift certificate type is invalid.'
				));
				exit;
			}

			$paymentObj->gift_certificate_number = (!empty($_REQUEST['payment_data']['number']) ? $_REQUEST['payment_data']['number'] : "null");
		}
		else if ($paymentObj->payment_type != 'CC')
		{
			$paymentObj->payment_number = (!empty($_REQUEST['payment_data']['number']) ? $_REQUEST['payment_data']['number'] : "null");
		}

		$paymentObj->payment_note = (!empty($_REQUEST['payment_data']['note']) ? $_REQUEST['payment_data']['note'] : "null");

		$paymentObj->total_amount = $amount;

		if ($paymentObj->payment_type == 'GIFT_CARD')
		{

			if (!is_numeric($paymentObj->payment_number))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The Gift Card number is not a number.'
				));
				exit;
			}
		}

		// Note: Currently reference transaction are handled by by this processor when an order is booked (savePaymentAndBookOrder())
		// When a reference transaction is submitted for a booked order it is handled by the page_admin_order_mgr

		if ($paymentObj->payment_type == 'CC')
		{

			$UserObj = DAO_CFactory::create('user');
			$UserObj->id = $paymentObj->user_id;
			$UserObj->find(true);

			$OrderObj = DAO_CFactory::create('orders');
			$OrderObj->id = $this->order_id;

			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->id = $this->store_id;
			$StoreObj->find(true);

			$rslt = $paymentObj->processPayment($UserObj, $OrderObj, $StoreObj);

			if ($rslt['result'] != 'success')
			{
				//verisign failed, rollback order and booking

				if ($rslt['result'] == 'transactionDecline')
				{
					CLog::RecordNew(CLog::CCDECLINE, $paymentObj->verisignExtendedRslt);
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'The credit card was declined.'
					));
					exit;
				}
				else
				{
					CLog::RecordNew(CLog::ERROR, $paymentObj->verisignExtendedRslt, '', '', true);
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'The was an error when processing the credit card.'
					));
					exit;
				}
			}
		}
		else if (strpos($paymentObj->payment_type, "REF_") === 0)
		{
			// reference transaction
			$ref_number = substr($paymentObj->payment_type, 4);

			$paymentObj->payment_type = CPayment::CC;

			if ($paymentObj->total_amount <= 0)
			{

				CLog::RecordNew(CLog::ERROR, "Reference Transaction submitted with invalid amount (zero or less).", '', '', true);
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Reference Transaction submitted with invalid amount (zero or less).'
				));
				exit;
			}

			$origPayment = DAO_CFactory::create('payment');
			$origPayment->payment_transaction_number = $ref_number;
			if (!$origPayment->find(true))
			{
				CLog::RecordNew(CLog::ERROR, "Unable to find original payment during processing of reference.", '', '', true);
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Unable to find original payment during processing of reference.'
				));
				exit;
			}

			$UserObj = DAO_CFactory::create('user');
			$UserObj->id = $paymentObj->user_id;
			$UserObj->find(true);

			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->id = $this->store_id;
			$StoreObj->find(true);

			$OrderObj = DAO_CFactory::create('orders');
			$OrderObj->id = $this->order_id;

			$paymentObj->referent_id = $origPayment->id;
			$paymentObj->payment_number = $origPayment->payment_number;
			$paymentObj->credit_card_type = $origPayment->credit_card_type;

			$rslt = $paymentObj->processByReference($UserObj, $OrderObj, $StoreObj, $ref_number, false);

			if ($rslt['result'] != 'success')
			{
				if ($rslt['result'] == 'transactionDecline')
				{
					CLog::RecordNew(CLog::CCDECLINE, $paymentObj->verisignExtendedRslt);
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'The credit card reference transaction was declined.'
					));
					exit;
				}
				else
				{
					CLog::RecordNew(CLog::CCDECLINE, $paymentObj->verisignExtendedRslt);
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'Could not process the credit card reference transaction.'
					));
					exit;
				}
			}
		}

		$paymentObj->insert();
		$paymentObj->recordRevenueEvent($session_id);
		//update digest with nerw balance due
		$tempOrderObj = DAO_CFactory::create('orders');
		$tempOrderObj->query("select grand_total from orders where id = {$this->order_id}");
		$tempOrderObj->fetch();
		$newBalanceDue = COrdersDigest::calculateAndAddBalanceDue($this->order_id, $tempOrderObj->grand_total);

		$dasOrderDigest = DAO_CFactory::create('orders_digest');
		$dasOrderDigest->query("update orders_digest set balance_due = '$newBalanceDue' where order_id = {$this->order_id}");

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The payment was successfully added.',
			'order_id' => $this->order_id
		));
		exit;
	}

	/*
	 *
	 *
	 */
	function savePaymentAndBookOrder()
	{

		CLog::RecordDebugTrace('order_mgr_processor::savePaymentAndBookOrder called for order: ' . $this->order_id, "TR_TRACING");

		if (empty($_POST['dd_csrf_token']) || !CSRF::validate($_POST['dd_csrf_token'], 'om_payment'))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => CSRF_Fail_Msg
			));
			exit;
		}

		$amount = isset($_REQUEST['payment_data']['amount']) ? $_REQUEST['payment_data']['amount'] : 0;

		// validate the amount field first
		if (!is_numeric($amount))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The amount submitted is not a number'
			));
			exit;
		}

		if ($amount < 0)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The amount submitted is less than zero'
			));
			exit;
		}

		if ($_REQUEST['payment_data']['amount'] > 3000)
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The amount submitted is greater than $3000. Please contact the home office if this is a legitimate amount.'
			));
			exit;
		}

		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;
		if (!$userObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The user account could not be found.'
			));
			exit;
		}

		$OrderObj = DAO_CFactory::create('orders');
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);

		$orgOrderObj = clone($OrderObj);

		$Session = $OrderObj->findSession(true);
		$OrderObj->reconstruct();

		if ($userObj->dream_rewards_version == 3 && ($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3))
		{
			$OrderObj->is_in_plate_points_program = 1;
		}

		if (!empty($OrderObj->bundle_id) && $OrderObj->type_of_order == 'INTRO')
		{
			$activeBundle = CBundle::getActiveBundleForMenu($Session->menu_id, $Session->store_id);

			$items = $OrderObj->getItems();
			$selectedBIs = array();
			foreach ($items as $id => $data)
			{
				$itemObj = $data[1];
				if (!empty($itemObj->bundle_id) && $itemObj->bundle_id == $activeBundle->id)
				{
					$selectedBIs[] = $id;
				}
			}

			$OrderObj->addBundle($activeBundle, $selectedBIs, true);
		}

		if ($Session->session_type == CSession::DREAM_TASTE)
		{

			$dreamEventProperties = CDreamTasteEvent::sessionProperties($Session->id);

			$theBundle = DAO_CFactory::create('bundle');
			$theBundle->id = $dreamEventProperties->bundle_id;
			if (!$theBundle->find(true))
			{
				throw new Exception('Bundle not found for the selected session.');
			}

			// Bundle Price Intercept Point

			$isCorporateStore = false;

			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select is_corporate_owned from store where id = {$Session->store_id}");
			$storeObj->fetch();

			if ($storeObj->is_corporate_owned)
			{
				$isCorporateStore = true;
			}

			if ($isCorporateStore && $Session->menu_id <= 184)
			{
				if ($theBundle->bundle_type == CBundle::DREAM_TASTE)
				{
					$theBundle->price = 34.99;
				}
			}

			$selectedBIs = array();
			foreach ($OrderObj->getItems() as $k => $v)
			{
				$selectedBIs[$k] = $v[0];
			}

			$OrderObj->addTasteBundle($theBundle, $selectedBIs, true);
		}

		if ($Session->session_type == CSession::FUNDRAISER)
		{

			$fundraiserEventProperties = CFundraiser::fundraiserEventSessionProperties($Session);

			$theBundle = DAO_CFactory::create('bundle');
			$theBundle->id = $fundraiserEventProperties->bundle_id;
			if (!$theBundle->find(true))
			{
				throw new Exception('Bundle not found for the selected session.');
			}

			$selectedBIs = array();
			foreach ($OrderObj->getItems() as $k => $v)
			{
				$selectedBIs[$k] = $v[0];
			}

			$OrderObj->addTasteBundle($theBundle, $selectedBIs, true);
		}

		$OrderObj->refreshForEditing($Session->menu_id);

		if ($Session->isDelivery())
		{
			$OrderObj->orderAddress();
		}

		// Recheck allowed PLATEPOINTS and cap if necessary
		if (!empty($_REQUEST['max_plate_points_deduction']) && is_numeric($_REQUEST['max_plate_points_deduction']))
		{
			$maxDeduction = $_REQUEST['max_plate_points_deduction'];
		}
		else
		{
			$maxDeduction = 0;
		}

		if ($OrderObj->points_discount_total > $maxDeduction)
		{
			$OrderObj->points_discount_total = $maxDeduction;
		}

		$userIsOnHold = ($OrderObj->points_discount_total > 0 && $OrderObj->membership_discount > 0);

		$OrderObj->recalculate(true, false, false, $userIsOnHold);

		$OrderObj->setOrderInStoreStatus();
		$OrderObj->setOrderMultiplierEligibility();

		$OrderObj->setStoreCustomizationOptions();

		$OrderObj->timestamp_created = date('Y-m-d H:i:s');
		$OrderObj->update($orgOrderObj, true);

		$use_store_credits = false;
		if (!empty($_REQUEST['use_store_credits']))
		{
			$use_store_credits = true;
		}

		if ($use_store_credits)
		{
			$store_credit_amount = isset($_REQUEST['store_credits_amount']) ? $_REQUEST['store_credits_amount'] : 0;

			// ---------------------------------------------Build Store Credits array and checkbox
			$Store_Credit = DAO_CFactory::create('store_credit');
			$Store_Credit->user_id = $OrderObj->user_id;
			$Store_Credit->store_id = $OrderObj->store_id;
			$Store_Credit->is_redeemed = 0;
			$Store_Credit->is_deleted = 0;
			$Store_Credit->is_expired = 0;

			$Store_Credit->find();

			$Store_Credit_Array = array();
			$Store_Credits_Total = 0;

			while ($Store_Credit->fetch())
			{

				$Store_Credit_Array[$Store_Credit->id] = array(
					'Source' => $Store_Credit->credit_card_number,
					'Amount' => $Store_Credit->amount,
					'Date_Redeemed' => $Store_Credit->timestamp_created,
					'DAO_Obj' => clone($Store_Credit)
				);

				$Store_Credits_Total += $Store_Credit->amount;
			}

			$this->processStoreCredits($store_credit_amount, $Store_Credit_Array, $OrderObj);
		}

		$payments = array();

		if (!empty($_REQUEST['add_payment_data']))
		{
			$payment2Obj = DAO_CFactory::create('payment');
			$payment2Obj->store_id = $this->store_id;
			$payment2Obj->user_id = $this->user_id;
			$payment2Obj->order_id = $this->order_id;

			// Type
			if (!empty($_REQUEST['payment_data']['type']))
			{
				$payment2Obj->payment_type = $_REQUEST['add_payment_data']['type'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'Invalid payment type.'
				));
				exit;
			}

			if ($payment2Obj->payment_type == CPayment::GIFT_CERT)
			{
				$payment2Obj->gift_certificate_number = $_REQUEST['add_payment_data']['number'];
				$payment2Obj->gift_cert_type = $_REQUEST['add_payment_data']['cert_type'];
			}
			else
			{
				$payment2Obj->payment_number = (!empty($_REQUEST['add_payment_data']['number']) ? $_REQUEST['add_payment_data']['number'] : "null");
			}

			$payment2Obj->is_delayed_payment = 0;
			$payment2Obj->is_deposit = 0;

			$payment2Obj->total_amount = $_REQUEST['add_payment_data']['amount'];

			if ($payment2Obj->payment_type == CPayment::CC || strpos($payment2Obj->payment_type, "REF_") === 0)
			{
				if (isset($_REQUEST['delay_remainder']) && $_REQUEST['delay_remainder'] > 0)
				{
					$payment2Obj->is_delayed_payment = $_REQUEST['delay_remainder'];
				}
			}

			$payments[] = $payment2Obj;
		}

		$paymentObj = DAO_CFactory::create('payment');
		$paymentObj->store_id = $this->store_id;
		$paymentObj->user_id = $this->user_id;
		$paymentObj->order_id = $this->order_id;

		// Type
		if (!empty($_REQUEST['payment_data']['type']))
		{
			$paymentObj->payment_type = $_REQUEST['payment_data']['type'];
		}
		else
		{
			$paymentObj->payment_type = CPayment::CASH;
		}

		if ($paymentObj->payment_type == 'CC')
		{

			$paymentObj->setCCInfo($_REQUEST['payment_data']['number'], $_REQUEST['payment_data']['ccMonth'], $_REQUEST['payment_data']['ccYear'], $_REQUEST['payment_data']['ccNameOnCard'], $_REQUEST['payment_data']['billing_address'], null, null, $_REQUEST['payment_data']['billing_postal_code'], $_REQUEST['payment_data']['ccType'], (empty($_REQUEST['payment_data']['cc_security_code']) ? false : $_REQUEST['payment_data']['cc_security_code']));
			if (!empty($_REQUEST['payment_data']['do_not_ref']))
			{
				$paymentObj->do_not_reference = 1;
			}

			$paymentObj->is_delayed_payment = 0;

			if ($_REQUEST['payment_data']['is_store_specific_flat_rate_deposit_delayed_payment'] == '1')
			{
				$paymentObj->is_delayed_payment = 4;
				$paymentObj->delayed_payment_status = CPayment::PENDING;
			}
			if ($_REQUEST['payment_data']['is_store_specific_flat_rate_deposit_delayed_payment'] == '2')
			{
				$paymentObj->is_delayed_payment = 5;
				$paymentObj->delayed_payment_status = CPayment::PENDING;
			}

			$paymentObj->is_deposit = 0;

			if (isset($_REQUEST['payment_data']['save_card']))
			{
				$paymentObj->save_card_on_completion = true;
			}

			$addr = $_REQUEST['payment_data']['billing_address'];
			$city = $_REQUEST['payment_data']['billing_city'];
			$state_id = $_REQUEST['payment_data']['billing_state_id'];
			$zip = $_REQUEST['payment_data']['billing_postal_code'];

			//check if user has billing address, if not then create one from address info entered
			if (!empty($addr) && !empty($state_id) && !empty($city) && !empty($zip))
			{
				$billingAddr = $userObj->getPrimaryBillingAddress();
				if (is_null($billingAddr->id))
				{
					//add billing address
					$addressData = array();

					$addressData['shipping_firstname'] = $userObj->firstname;
					$addressData['shipping_lastname'] = $userObj->lastname;
					$addressData['shipping_address_line1'] = $addr;
					$addressData['shipping_city'] = $city;
					$addressData['shipping_state_id'] = $state_id;
					$addressData['shipping_postal_code'] = $zip;
					CAddress::addToAddressBook($addressData, $userObj->id, CAddress::BILLING, true);
				}
			}
		}
		else
		{
			$paymentObj->is_delayed_payment = 0;
			$paymentObj->is_deposit = 0;
		}

		if ($paymentObj->payment_type == 'GIFT_CERT')
		{

			if (!empty($_REQUEST['payment_data']['cert_type']))
			{
				$paymentObj->gift_cert_type = $_REQUEST['payment_data']['cert_type'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The gift certificate type is invalid.'
				));
				exit;
			}

			$paymentObj->gift_certificate_number = (!empty($_REQUEST['payment_data']['number']) ? $_REQUEST['payment_data']['number'] : "null");
		}
		else if ($paymentObj->payment_type != 'CC')
		{
			$paymentObj->payment_number = (!empty($_REQUEST['payment_data']['number']) ? $_REQUEST['payment_data']['number'] : "null");
		}

		$paymentObj->payment_note = (!empty($_REQUEST['payment_data']['note']) ? $_REQUEST['payment_data']['note'] : "null");

		$paymentObj->total_amount = $amount;

		if ($paymentObj->payment_type == 'GIFT_CARD')
		{

			if (!is_numeric($paymentObj->payment_number))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The Gift Card number is not a number.'
				));
				exit;
			}
		}

		if ($paymentObj->payment_type == CPayment::CC || strpos($paymentObj->payment_type, "REF_") === 0)
		{
			if (isset($_REQUEST['delay_remainder']) && $_REQUEST['delay_remainder'] > 0)
			{
				$paymentObj->is_delayed_payment = $_REQUEST['delay_remainder'];
			}
		}

		$payments[] = $paymentObj;

		// Last second check for inventory
		if (!$OrderObj->verifyAdequateInventory())
		{
			$itemsOversold = $OrderObj->getInvExceptionItemsString();

			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => "One or more items has become unavailable since you loaded this saved order. Please review and adjust the order and try again. Items that require adjustment are:<br />" . $itemsOversold
			));
			exit;
		}

		try
		{

			$OrderObj->allowOverrideOfServiceFee = true;
			$OrderObj->allowOverrideOfDeliveryFee = true;

			$rslt = $OrderObj->processNewOrderDirectOrder($payments, empty($storeCredits) ? false : $storeCredits, empty($giftCardArray) ? false : $giftCardArray, true);

			switch ($rslt['result'])
			{
				case 'success':

					$OrderObj->query("update orders set order_confirmation = '{$OrderObj->order_confirmation}' where id = {$OrderObj->id}");

					$OrderObj->removeInitialInventory($OrderObj->findSession()->menu_id);

					$userObj->setHomeStore($OrderObj->store_id);
					// Increment DreamRewards Status if Appropriate

					CCustomerReferral::updateAsOrderedIfEligible($userObj, $OrderObj);

					if ($OrderObj->isBundleOrder() && !$OrderObj->isDreamTaste() && !$OrderObj->isFundraiser())
					{
						CEmail::sendBundleConfirmationEmail($userObj, $OrderObj);
					}
					else
					{
						COrders::sendConfirmationEmail($userObj, $OrderObj);
					}

					$token = false;
					if (!empty($_POST['payment2Type']))
					{
						$token = CSRF::getNewToken('om_payment_2');
					}

					//all good
					echo json_encode(array(
						'processor_success' => true,
						'processor_message' => 'The payment was saved and the order booked.',
						'order_id' => $OrderObj->id,
						'warnOfOutstandingSavedOrdersOnFullSession' => $rslt['warnOfOutstandingSavedOrdersOnFullSession'],
						'payment2token' => $token
					));
					exit;
				case 'transactionDecline':
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'The Credit Card was declined:<br />' . $rslt['userText']
					));
					exit;
				case 'invalidCC':
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'The credit card number you entered could not be verified. Please double check your payment information'
					));
					exit;
				case 'invalidPayment':
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'The credit card number you entered could not be verified. Please double check your payment information'
					));
					exit;

				case 'failed':
				default:
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'An error has occurred in the payment process, please try again later'
					));
					exit;
			}
		}
		catch (exception $e)
		{
			if (DEBUG)
			{
				CLog::RecordException($e);
			}
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'An error has occurred in the payment process, please try again later'
			));
			exit;
		}
	}

	function mightHaveDoubleSubmitted()
	{

		$bookingTest = DAO_CFactory::create('booking');

		// find any booking created by this user in the last 30 minutes
		$bookingTest->query("select session_id, order_id from booking where user_id = {$this->user_id} and status = 'ACTIVE' and TIMESTAMPDIFF(SECOND, timestamp_created, CURRENT_TIMESTAMP()) < 1800 and is_deleted = 0");

		if ($bookingTest->N)
		{
			return false;
			//no recent bookings so proceed normally
		}
		else
		{
			$bookingTest->fetch();

			// check for same session_id
			if ($this->targetSession == $bookingTest->session_id)
			{
				//sessions match so check grand_total
				$OrderTest = DAO_CFactory::create('orders');

				$OrderTest->query("select grand_total from orders where id = {$this->order_id} and is_deleted = 0");

				if ($OrderTest->N > 0)
				{
					$OrderTest->fetch();

					if ($OrderTest->grand_total == $_POST['grand_total'])
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}

	function getToken($saveOrder = false)
	{

		CLog::RecordDebugTrace('order_mgr_processor::getToken called', "TR_TRACING");

		if (empty($_POST['dd_csrf_token']) || (!CSRF::validate($_POST['dd_csrf_token'], 'om_get_token') && !CSRF::validate($_POST['dd_csrf_token'], 'om_payment') && !CSRF::validate($_POST['dd_csrf_token'], 'om_payment_2') && !CSRF::validate($_POST['dd_csrf_token'], 'order_mgr') && !CSRF::validate($_POST['dd_csrf_token'], 'customer_checkout') && !CSRF::validate($_POST['dd_csrf_token'], 'customer_gift_card_checkout')))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => CSRF_Fail_Msg
			));
			exit;
		}

		$csrftoken = false;
		if (!empty($_REQUEST['chain_token']) && $_REQUEST['chain_token'])
		{
			$csrftoken = CSRF::getNewToken('om_get_token');
		}

		// validate the amount field first
		if (empty($_REQUEST["billing_name"]))
		{
			echo json_encode(array(
				'processor_success' => false,
				'getTokenToken' => $csrftoken,
				'processor_message' => 'The billing name was not supplied'
			));
			exit;
		}

		// validate the amount field first
		if (empty($_REQUEST["billing_address"]))
		{
			echo json_encode(array(
				'processor_success' => false,
				'getTokenToken' => $csrftoken,
				'processor_message' => 'The billing address was not supplied'
			));
			exit;
		}

		// validate the amount field first
		if (empty($_REQUEST["billing_zip"]))
		{
			echo json_encode(array(
				'processor_success' => false,
				'getTokenToken' => $csrftoken,
				'processor_message' => 'The billing zip code was not supplied'
			));
			exit;
		}

		if (!empty($_REQUEST["use_corp"]))
		{
			$this->store_id = false;
		}

		if (!empty($_REQUEST["check_smart_transactions"]))
		{
			if (!CGiftCard::isSmartTransactionsAliveAndWell())
			{
				echo json_encode(array(
					'processor_success' => false,
					'getTokenToken' => $csrftoken,
					'processor_message' => 'The Gift Card system is unresponsive. Please try again in a few minutes or contact Dream Dinners support.'
				));
				exit;
			}
		}

		$amount = false;
		if (!empty($_REQUEST['amount']) && is_numeric($_REQUEST['amount']))
		{
			$amount = $_REQUEST['amount'];
		}

		$result = PayPalProcess::getSecureToken($this->store_id, $_REQUEST["billing_name"], $_REQUEST["billing_address"], $_REQUEST["billing_zip"], $amount);

		if (!is_array($result))
		{
			echo json_encode(array(
				'processor_success' => false,
				'getTokenToken' => $csrftoken,
				'processor_message' => 'There was a problem initiating the connection with the payment gateway.',
				'token' => $result
			));
			exit;
		}

		if ($saveOrder)
		{
			try
			{

				// validate the referral source and report error if necessary
				// if no error is found the demographics data will be included in the payflow payload and returned for processing upon order complete
				// alternatively we could store the data somewhere temporarily on the server for processing on transparent redirect success
				if ($_POST['pp_demo_info'])
				{
					if (isset($_POST['pp_demo_info']['referral_source']) && $_POST['pp_demo_info']['referral_source'] == 'CUSTOMER_REFERRAL')
					{
						$incomingEmail = null;

						if (!empty($_POST['pp_demo_info']['customer_referral_email']))
						{
							$incomingEmail = $_POST['pp_demo_info']['customer_referral_email'];
						}

						if (empty($incomingEmail))
						{
							echo json_encode(array(
								'processor_success' => false,
								'getTokenToken' => $csrftoken,
								'processor_message' => 'The email for the guest the referred you is invalid.',
								'token' => $result
							));
							exit;
						}
						else
						{
							$TempUser = DAO_CFactory::create('user');
							$TempUser->primary_email = $incomingEmail;
							if (!$TempUser->find(true))
							{
								echo json_encode(array(
									'processor_success' => false,
									'getTokenToken' => $csrftoken,
									'processor_message' => 'The email for the guest the referred you could not be found.',
									'token' => $result
								));
								exit;
							}
						}
					}
				}

				$warnOfDoubleSubmission = $this->mightHaveDoubleSubmitted();

				$this->doSetSessionAndSave(true, true); // true meaning enforce slot availiblity, true meaning the csrf token had been validated

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'The token was retrieved.',
					'getTokenToken' => $csrftoken,
					'token' => $result,
					'order_id' => $this->order_id,
					'warnOfDoubleSubmission' => $warnOfDoubleSubmission,
					'full_session_warning_required' => false
				));
				exit;
			}
			catch (Exception $e)
			{

				if ($e->getCode() == self::dd_general_exception_code)
				{

					// unexpected problem occurred so fail bith a new CSRF code
					echo json_encode(array(
						'processor_success' => false,
						'getTokenToken' => $csrftoken,
						'processor_message' => $e->getMessage()
					));
					exit;
				}
				else if ($e->getCode() == self::dd_taste_session_exception_code)
				{
					// unexpected problem occurred so fail bith a new CSRF code
					echo json_encode(array(
						'processor_success' => false,
						'getTokenToken' => $csrftoken,
						'bounce_to_menu' => true,
						'processor_message' => $e->getMessage()
					));
					exit;
				}
				else if ($e->getCode() == self::dd_std_session_exception_code)
				{
					// unexpected problem occurred so fail bith a new CSRF code
					echo json_encode(array(
						'processor_success' => false,
						'getTokenToken' => $csrftoken,
						'bounce_to_std_menu' => true,
						'processor_message' => $e->getMessage()
					));
					exit;
				}
				else if ($e->getCode() == self::dd_dinner_dollars_exception_code)
				{
					// changes to cart force us to need reload the checkout page
					echo json_encode(array(
						'processor_success' => false,
						'getTokenToken' => $csrftoken,
						'reset_dinner_dollars' => true,
						'processor_message' => $e->getMessage()
					));
					exit;
				}
				else
				{
					throw $e;
				}
			}
		}

		echo json_encode(array(
			'processor_success' => true,
			'getTokenToken' => $csrftoken,
			'processor_message' => 'The token was retrieved.',
			'token' => $result
		));
		exit;
	}

	/*
	 * Called by Ajax from the order manager when processing an edit that requires a CC payment. After this function executes the payment is
	 * sent to PayPal via Transparent Redirect.  The return call to payflow_callback_sp add the payment to our system. The edit is recorded in edited_order and
	 * all non-payment changes are recorded.
	 * */

	function updateActiveOrder()
	{
		CLog::RecordDebugTrace('order_mgr_processor::updateActiveOrder called for order: ' . $this->order_id, "TR_TRACING");

		if (empty($_POST['dd_csrf_token']) || (!CSRF::validate($_POST['dd_csrf_token'], 'order_mgr') && !CSRF::validate($_POST['dd_csrf_token'], 'om_get_token')))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => CSRF_Fail_Msg
			));
			exit;
		}

		$OrderObj = DAO_CFactory::create('orders');
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);

		$orgOrder = clone($OrderObj);

		$originalGrandTotalMinusTaxes = $OrderObj->grand_total - $OrderObj->subtotal_all_taxes;

		$order_record = DAO_CFactory::create('edited_orders');
		$order_record->setFrom($OrderObj->toArray());
		$order_record->original_order_id = $this->order_id;

		$order_record->order_revisions = $_POST['changeList'];
		$order_record->order_revision_notes = $_POST['changeListStr'];

		$order_record->insert();

		// $OrderObj->reconstruct(); ???

		if (!empty($_POST['misc_food_subtotal']) && is_numeric($_POST['misc_food_subtotal']) && $_POST['misc_food_subtotal'] >= 0)
		{
			$OrderObj->misc_food_subtotal = $_POST['misc_food_subtotal'];
		}

		if (!empty($_POST['misc_nonfood_subtotal']) && is_numeric($_POST['misc_nonfood_subtotal']) && $_POST['misc_nonfood_subtotal'] >= 0)
		{
			$OrderObj->misc_nonfood_subtotal = $_POST['misc_nonfood_subtotal'];
		}

		if (!empty($_POST['subtotal_service_fee']) && is_numeric($_POST['subtotal_service_fee']) && $_POST['subtotal_service_fee'] >= 0)
		{
			$OrderObj->subtotal_service_fee = $_POST['subtotal_service_fee'];
		}

		if (!empty($_POST['subtotal_delivery_fee']) && is_numeric($_POST['subtotal_delivery_fee']) && $_POST['subtotal_delivery_fee'] >= 0)
		{
			$OrderObj->subtotal_delivery_fee = $_POST['subtotal_delivery_fee'];
		}

		if (!empty($_POST['misc_food_subtotal_desc']))
		{
			$OrderObj->misc_food_subtotal_desc = $_POST['misc_food_subtotal_desc'];
		}
		else
		{
			$OrderObj->misc_food_subtotal_desc = 'null';
		}

		if (!empty($_POST['misc_nonfood_subtotal_desc']))
		{
			$OrderObj->misc_nonfood_subtotal_desc = $_POST['misc_nonfood_subtotal_desc'];
		}
		else
		{
			$OrderObj->misc_nonfood_subtotal_desc = 'null';
		}

		if (!empty($_POST['service_fee_description']))
		{
			$OrderObj->service_fee_description = $_POST['service_fee_description'];
		}
		else
		{
			$OrderObj->service_fee_description = 'null';
		}

		$Session = $OrderObj->findSession(true);

		$OrderItems = DAO_CFactory::create('order_item');
		$adminUser = CUser::getCurrentUser()->id;
		$OrderItems->query("update order_item set is_deleted = 1, edit_sequence_id = {$order_record->id}, updated_by = $adminUser where order_id = {$this->order_id} and is_deleted = 0");

		$OrderObj->refreshForEditing($Session->menu_id);

		$isIntro = false;
		$introItems = array();
		if (!empty($_POST['is_intro']) && $_POST['is_intro'] == "true")
		{
			$isIntro = true;
			if (!empty($_POST['intro_items']))
			{
				$introItems = $_POST['intro_items'];
			}
		}

		$items = array();
		if (!empty($_POST['items']))
		{
			$items = $_POST['items'];
		}

		$subItems = array();
		if (!empty($_POST['sub_items']))
		{
			$subItems = $_POST['sub_items'];
		}

		$ltdOrderedMealsArray = false;
		if (isset($_POST['ltdOrderedMealsArray']))
		{
			$ltdOrderedMealsArray = json_decode($_POST['ltdOrderedMealsArray']);
		}

		$this->addMenuItemsToOrder($OrderObj, null, $items, $isIntro, $introItems, $subItems, $ltdOrderedMealsArray);

		$OrderObj->insertEditedItems(false, false);
		$OrderObj->recalculate(true, false);

		if (!empty($_POST['direct_order_discount']) && is_numeric($_POST['direct_order_discount']) && $_POST['direct_order_discount'] >= 0)
		{
			$OrderObj->addDiscount($_POST['direct_order_discount']);
		}

		if (!empty($_POST['dream_rewards_level']) && is_numeric($_POST['dream_rewards_level']) && $_POST['dream_rewards_level'] > 0)
		{
			$OrderObj->dream_rewards_level = $_POST['dream_rewards_level'];
		}

		if (!empty($_POST['plate_points_discount']) && is_numeric($_POST['plate_points_discount']) && $_POST['plate_points_discount'] >= 0)
		{

			$pp_credit_adjust_summary = CPointsCredits::AdjustPointsForOrderEdit($orgOrder, $_POST['plate_points_discount']);

			$OrderObj->points_discount_total = $_POST['plate_points_discount'];
		}

		if (!empty($_POST['PUDSetting']))
		{
			if ($_POST['PUDSetting'] == "noUP")
			{
				$OrderObj->clearPreferred();
				$OrderObj->user_preferred_id = 'null';
			}
			else if ($_POST['PUDSetting'] == "activeUP")
			{
				$UPobj = DAO_CFactory::create('user_preferred');
				$UPobj->user_id = $OrderObj->user_id;
				if ($UPobj->findActive($OrderObj->store_id))
				{
					$UPobj->fetch();
					$OrderObj->addPreferred($UPobj);
				}
			}
		}

		$suppressSessionDiscount = false;
		if (!empty($_POST['SessDiscSetting']))
		{
			if ($_POST['SessDiscSetting'] == "noSD")
			{
				$OrderObj->findSession()->session_discount_id = 0;
				$OrderObj->session_discount_id = 'null';
				$OrderObj->session_discount_total = 0;
				$suppressSessionDiscount = true;
			}
			else if ($_POST['SessDiscSetting'] == "activeSD")
			{
				// nothing to do. recalculate will find the discount
			}
			else if ($_POST['SessDiscSetting'] == "originalSD")
			{
				// nothing to do. recalculate will find the discount ???
				// TODO: there's really no chance it changed so use the activeSD
			}
		}
		else
		{
			$suppressSessionDiscount = true;
		}

		$OrderObj->refreshForEditing($Session->menu_id);
		$OrderObj->recalculate(true, $suppressSessionDiscount);
		$OrderObj->update($orgOrder);

		COrdersDigest::recordEditedOrder($OrderObj, $originalGrandTotalMinusTaxes);

		$token = CSRF::getNewToken('om_get_token');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The order was successfully updated.',
			'getTokenToken' => $token
		));
		exit;
	}

	function updateDiscounts()
	{

		CLog::RecordDebugTrace('order_mgr_processor::updateDiscounts called for order: ' . $this->order_id);

		$OrderObj = DAO_CFactory::create('orders');
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);

		$orgOrder = clone($OrderObj);
		$OrderObj->reconstruct();

		$Session = $OrderObj->findSession(true);

		if (!empty($OrderObj->bundle_id) && $OrderObj->type_of_order == 'INTRO')
		{
			$activeBundle = CBundle::getActiveBundleForMenu($Session->menu_id, $OrderObj->store_id);

			$items = $OrderObj->getItems();
			$selectedBIs = array();
			foreach ($items as $id => $data)
			{
				$itemObj = $data[1];
				if (!empty($itemObj->bundle_id) && $itemObj->bundle_id == $activeBundle->id)
				{
					$selectedBIs[] = $id;
				}
			}

			$OrderObj->addBundle($activeBundle, $selectedBIs, true);
		}

		if ($Session->session_type == CSession::DREAM_TASTE || $Session->session_type == CSession::FUNDRAISER)
		{

			$dreamEventProperties = CDreamTasteEvent::sessionProperties($Session->id);

			$theBundle = DAO_CFactory::create('bundle');
			$theBundle->id = $dreamEventProperties->bundle_id;
			if (!$theBundle->find(true))
			{
				throw new Exception('Bundle not found for the selected session.');
			}

			$selectedBIs = array();
			foreach ($OrderObj->getItems() as $k => $v)
			{
				$selectedBIs[$k] = $v[0];
			}

			// Bundle Price Intercept Point
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select is_corporate_owned from store where id = {$Session->store_id}");
			$storeObj->fetch();

			if ($Session->menu_id <= 184 && $Session->session_type == CSession::DREAM_TASTE && $storeObj->is_corporate_owned)
			{
				$theBundle->price = 34.99;
			}

			$OrderObj->addTasteBundle($theBundle, $selectedBIs, true);
		}

		if (!empty($_POST['direct_order_discount']) && is_numeric($_POST['direct_order_discount']) && $_POST['direct_order_discount'] >= 0)
		{
			$OrderObj->addDiscount($_POST['direct_order_discount']);
		}

		if (!empty($_POST['dream_rewards_level']) && is_numeric($_POST['dream_rewards_level']) && $_POST['dream_rewards_level'] > 0)
		{
			$OrderObj->dream_rewards_level = $_POST['dream_rewards_level'];
		}

		if (isset($_POST['plate_points_discount']) && is_numeric($_POST['plate_points_discount']))
		{
			$OrderObj->points_discount_total = $_POST['plate_points_discount'];
		}

		if (!empty($_POST['PUDSetting']))
		{
			if ($_POST['PUDSetting'] == "noUP")
			{
				$OrderObj->clearPreferred();
				$OrderObj->user_preferred_id = 'null';
			}
			else if ($_POST['PUDSetting'] == "activeUP")
			{
				$UPobj = DAO_CFactory::create('user_preferred');
				$UPobj->user_id = $OrderObj->user_id;
				if ($UPobj->findActive($OrderObj->store_id))
				{
					$UPobj->fetch();
					$OrderObj->addPreferred($UPobj);
				}
			}
		}

		if (!empty($_POST['coupon_id']) && is_numeric($_POST['coupon_id']))
		{
			$codeDAO = DAO_CFactory::create('coupon_code');
			$codeDAO->id = $_POST['coupon_id'];
			$codeDAO->find(true);

			if ($codeDAO->discount_method == CCouponCode::FREE_MENU_ITEM)
			{
				if (!empty($_POST['coupon_free_menu_item']) && is_numeric($_POST['coupon_free_menu_item']))
				{
					$OrderObj->coupon_free_menu_item = $_POST['coupon_free_menu_item'];

					$OrderObj->addCoupon($codeDAO);
				}
				else
				{
					// error
				}
			}
			else
			{
				$OrderObj->addCoupon($codeDAO);
			}
		}
		else
		{
			$OrderObj->coupon_code_id = 'null';
			$OrderObj->coupon_code_discount_total = 0;
			$OrderObj->coupon_free_menu_item = 'null';
		}

		$OrderObj->findSession(true);

		$suppressSessionDiscount = false;
		if (!empty($_POST['SessDiscSetting']))
		{
			if ($_POST['SessDiscSetting'] == "noSD")
			{
				$OrderObj->findSession()->session_discount_id = 0;
				$OrderObj->session_discount_id = 'null';
				$OrderObj->session_discount_total = 0;
				$suppressSessionDiscount = true;
			}
			else if ($_POST['SessDiscSetting'] == "activeSD")
			{
				// nothing to do. recalculate will find the discount
			}
		}
		else
		{
			$suppressSessionDiscount = true;
		}

		$OrderObj->allowOverrideOfServiceFee = true;
		$OrderObj->allowOverrideOfDeliveryFee = true;

		// fundraiser
		if (!empty($_POST['fundraiser_id']) && !empty($_POST['fundraiser_value']))
		{
			$OrderObj->fundraiser_id = $_POST['fundraiser_id'];
			$OrderObj->fundraiser_value = number_format($_POST['fundraiser_value'], 2);
		}
		else if ($Session->session_type == CSession::FUNDRAISER && !empty($_POST['fundraiser_value']))
		{
			$OrderObj->fundraiser_value = number_format($_POST['fundraiser_value'], 2);
		}
		else
		{
			$OrderObj->fundraiser_id = 'null';
			$OrderObj->fundraiser_value = 'null';
		}

		// ltd roundup
		if (!empty($_POST['add_ltd_round_up']) && $_POST['add_ltd_round_up'] == 'true' && is_numeric($_POST['ltd_round_up_select']))
		{
			$OrderObj->ltd_round_up_value = $_POST['ltd_round_up_select'];
		}
		else
		{
			$OrderObj->ltd_round_up_value = 'null';
		}

		if (!empty($_POST['storeSupportsBagFees']) && $_POST['storeSupportsBagFees'] != 'false')
		{

			if (!empty($_POST['opted_to_bring_bags']) && $_POST['opted_to_bring_bags'] != 'false')
			{
				$OrderObj->subtotal_bag_fee = 0.00;
				$OrderObj->total_bag_count = 0;
				$OrderObj->opted_to_bring_bags = 1;
			}
			else
			{
				$OrderObj->opted_to_bring_bags = 0;

				if (!empty($_POST['bagFeeTotal']))
				{
					$OrderObj->subtotal_bag_fee = $_POST['bagFeeTotal'];
				}
				else
				{
					$OrderObj->subtotal_bag_fee = 0.00;
				}

				if (!empty($_POST['bagFeeCount']))
				{
					$OrderObj->total_bag_count = $_POST['bagFeeCount'];
				}
				else
				{
					$OrderObj->total_bag_count = 0;
				}
			}
		}
		else
		{
			$OrderObj->subtotal_bag_fee = 0.00;
			$OrderObj->total_bag_count = 0;
			$OrderObj->opted_to_bring_bags = 'null';
		}

		$OrderObj->refreshForEditing($OrderObj->findSession()->menu_id);
		$OrderObj->setAllowRecalculateMealCustomizationFeeClosedSession(true);
		$OrderObj->recalculate(true, $suppressSessionDiscount);
		$OrderObj->update($orgOrder);

		$token = CSRF::getNewToken('om_payment');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The discounts were successfully updated.',
			'paymentToken' => $token
		));
		exit;
	}

	function updateItems()
	{
		CLog::RecordDebugTrace('order_mgr_processor::updateItems called for order: ' . $this->order_id);

		$OrderObj = DAO_CFactory::create('orders');
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);

		$orgOrder = $OrderObj->cloneObj();

		$orgPrices = array();

		// original prices to be used if menu > 78
		if ($this->orderState != 'NEW')
		{
			$orgPrices = $orgOrder->getOriginalPrices();
		}

		if (isset($_POST['misc_food_subtotal']) && is_numeric($_POST['misc_food_subtotal']) && $_POST['misc_food_subtotal'] >= 0)
		{
			$OrderObj->misc_food_subtotal = $_POST['misc_food_subtotal'];
		}

		if (isset($_POST['misc_nonfood_subtotal']) && is_numeric($_POST['misc_nonfood_subtotal']) && $_POST['misc_nonfood_subtotal'] >= 0)
		{
			$OrderObj->misc_nonfood_subtotal = $_POST['misc_nonfood_subtotal'];
		}

		if (isset($_POST['subtotal_service_fee']) && is_numeric($_POST['subtotal_service_fee']) && $_POST['subtotal_service_fee'] >= 0)
		{
			$OrderObj->subtotal_service_fee = $_POST['subtotal_service_fee'];
		}

		if (isset($_POST['subtotal_delivery_fee']) && is_numeric($_POST['subtotal_delivery_fee']) && $_POST['subtotal_delivery_fee'] >= 0)
		{
			$OrderObj->subtotal_delivery_fee = $_POST['subtotal_delivery_fee'];
		}

		if (!empty($_POST['misc_food_subtotal_desc']))
		{
			$OrderObj->misc_food_subtotal_desc = $_POST['misc_food_subtotal_desc'];
		}
		else
		{
			$OrderObj->misc_food_subtotal_desc = 'null';
		}

		if (!empty($_POST['misc_nonfood_subtotal_desc']))
		{
			$OrderObj->misc_nonfood_subtotal_desc = $_POST['misc_nonfood_subtotal_desc'];
		}
		else
		{
			$OrderObj->misc_nonfood_subtotal_desc = 'null';
		}

		if (!empty($_POST['service_fee_description']))
		{
			$OrderObj->service_fee_description = $_POST['service_fee_description'];
		}
		else
		{
			$OrderObj->service_fee_description = 'null';
		}

		if (!empty($_POST['storeSupportsBagFees']) && $_POST['storeSupportsBagFees'] != 'false')
		{

			if (!empty($_POST['opted_to_bring_bags']) && $_POST['opted_to_bring_bags'] != 'false')
			{
				$OrderObj->subtotal_bag_fee = 0.00;
				$OrderObj->total_bag_count = 0;
				$OrderObj->opted_to_bring_bags = 1;
			}
			else
			{

				if (!empty($_POST['bagFeeTotal']))
				{
					$OrderObj->subtotal_bag_fee = $_POST['bagFeeTotal'];
				}
				else
				{
					$OrderObj->subtotal_bag_fee = 0.00;
				}

				if (!empty($_POST['bagFeeCount']))
				{
					$OrderObj->total_bag_count = $_POST['bagFeeCount'];
				}
				else
				{
					$OrderObj->total_bag_count = 0;
				}
			}
		}
		else
		{
			$OrderObj->subtotal_bag_fee = 0.00;
			$OrderObj->total_bag_count = 0;
			$OrderObj->opted_to_bring_bags = 'null';
		}

		if (!empty($_POST['opted_to_customize']) && $_POST['opted_to_customize'] == 'false')
		{
			$OrderObj->clearMealCustomizations();
		}
		else if (!empty($_POST['opted_to_customize']) && $_POST['opted_to_customize'] == 'true')
		{
			$OrderObj->setAllowRecalculateMealCustomizationFeeClosedSession(true);
			$OrderObj->opted_to_customize_recipes = 1;
			$OrderObj->total_customized_meal_count = 0;

			if ($_POST['manual_customization_fee'] == 'true')
			{
				$OrderObj->setShouldRecalculateMealCustomizationFee(false);
			}
			if (!empty($_POST['meal_customization_fee']))
			{
				$OrderObj->subtotal_meal_customization_fee = $_POST['meal_customization_fee'];
			}
			else
			{
				$OrderObj->subtotal_meal_customization_fee = 0.00;
			}
		}

		$ltdOrderedMealsArray = false;

		if (isset($_POST['ltdOrderedMealsArray']))
		{
			$ltdOrderedMealsArray = json_decode($_POST['ltdOrderedMealsArray']);
		}

		$OrderItems = DAO_CFactory::create('order_item');
		$adminUser = CUser::getCurrentUser()->id;
		$OrderItems->query("update order_item set is_deleted = 1, edit_sequence_id = 2, updated_by = $adminUser where order_id = {$this->order_id} and is_deleted = 0");

		$SessionObj = $OrderObj->findSession(true);
		$OrderObj->refreshForEditing($SessionObj->menu_id);

		$isIntro = false;
		$introItems = array();

		$BookingUpdate = DAO_CFactory::create('booking');

		if (!empty($_POST['is_intro']) && $_POST['is_intro'] == "true")
		{
			$isIntro = true;
			if (!empty($_POST['intro_items']))
			{
				$introItems = $_POST['intro_items'];
			}

			$OrderObj->type_of_order = COrders::INTRO;

			$BookingUpdate->query("update booking set booking_type = 'INTRO' where order_id = {$this->order_id} and status = 'SAVED'");
		}
		else
		{
			$BookingUpdate->query("update booking set booking_type = 'STANDARD' where order_id = {$this->order_id} and status = 'SAVED'");

			if ($OrderObj->findSession()->session_type == CSession::DREAM_TASTE)
			{
				$OrderObj->type_of_order = COrders::DREAM_TASTE;
			}
			else if ($OrderObj->findSession()->session_type == CSession::FUNDRAISER)
			{

				$OrderObj->type_of_order = COrders::FUNDRAISER;
			}
			else
			{
				$OrderObj->type_of_order = COrders::STANDARD;
				$OrderObj->bundle_id = 0;
			}
		}

		$items = array();
		if (!empty($_POST['items']))
		{
			$items = $_POST['items'];
		}

		$subItems = array();
		if (!empty($_POST['sub_items']))
		{
			$subItems = $_POST['sub_items'];
		}

		$this->addMenuItemsToOrder($OrderObj, $orgPrices, $items, $isIntro, $introItems, $subItems, $ltdOrderedMealsArray);

		$OrderObj->insertEditedItems(false, false);
		$OrderObj->recalculate(true, false);

		if (empty($OrderObj->bundle_id))
		{
			$OrderObj->bundle_id = 'null';
		}

		$OrderObj->updateMinimumQualification();

		$OrderObj->update($orgOrder);

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The items were successfully added.'
		));
		exit;
	}

	function doSetSessionAndSave($enforceSlotAvailability = false, $csrfAlreadyValidated = false)
	{
		CLog::RecordDebugTrace('order_mgr_processor::doSetSessionAndSave called for user: ' . $this->user_id, "TR_TRACING");

		if (!$csrfAlreadyValidated && (empty($_POST['dd_csrf_token']) || !CSRF::validate($_POST['dd_csrf_token'], 'order_mgr')))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => CSRF_Fail_Msg
			));
			exit;
		}

		$bookingType = 'STANDARD';
		if (isset($_REQUEST['is_intro_offer']) && $_REQUEST['is_intro_offer'] == 'true')
		{
			$bookingType = 'INTRO';
		}

		$sessionObj = DAO_CFactory::create('session');
		$sessionObj->id = $this->targetSession;
		if (!$sessionObj->find(true))
		{
			throw new Exception('The session cannot be found.', self::dd_general_exception_code);
		}

		if (!empty($_REQUEST['cart_id']))
		{
			CLog::RecordIntense("DoSetSessionAndSave called with a cart id", "ryan.snook@dreamdinners.com");

			$Cart = CCart2::instance(true, $_REQUEST['cart_id']);
			$Order = $Cart->getOrder();

			$StoreObj = DAO_CFactory::create('store');
			$StoreObj->query("select supports_free_assembly_promotion from store where id = {$this->store_id}");
			$StoreObj->fetch();

			$discountableAmount = $Order->getPointsDiscountableAmount();

			if ($sessionObj->session_type == CSession::SPECIAL_EVENT && !$Order->isFreePromotionInEffectForSession($StoreObj, $sessionObj))
			{
				$discountableAmount += 20;
			}

			$order_Credits = (int)($Order->points_discount_total * 100);
			$discountableAmount = (int)($discountableAmount * 100);

			$delta = $order_Credits - $discountableAmount;

			if ($delta > 1)
			{
				CLog::RecordIntense("PlatePoints Discount is greater than discountable amount during customer order submission", "ryan.snook@dreamdinners.com");

				$Cart->updateDinnerDollarsDirect($discountableAmount);
				throw new Exception('It appears your cart has been updated since this checkout page was loaded. We need to reload the page now to reflect those changes.', self::dd_dinner_dollars_exception_code);
			}

			$ltd_amount = false;
			if (isset($_REQUEST['ltd_amount']) && is_numeric($_REQUEST['ltd_amount']))
			{
				$ltd_amount = $_REQUEST['ltd_amount'];
			}

			if ($ltd_amount)
			{
				$Cart->updateLTDAmountDirect($ltd_amount);
			}
		}

		if (!empty($_REQUEST['cart_id']) && ($sessionObj->session_type == CSession::DREAM_TASTE || $sessionObj->session_type == CSession::FUNDRAISER))
		{
			$Cart = CCart2::instance(true, $_REQUEST['cart_id']);
			$Order = $Cart->getOrder();
			if (empty($Order->bundle_id))
			{
				throw new Exception('The session you selected is a Taste Session but the Taste items have been removed from the cart. If you wish to place a Taste order please RSVP again.', self::dd_taste_session_exception_code);
			}
		}

		if (!empty($_REQUEST['cart_id']) && ($bookingType != 'INTRO' && $sessionObj->session_type != CSession::DREAM_TASTE && $sessionObj->session_type != CSession::FUNDRAISER))
		{
			$Cart = CCart2::instance(true, $_REQUEST['cart_id']);
			$Order = $Cart->getOrder();
			if (!empty($Order->bundle_id) && is_numeric($Order->bundle_id))
			{
				throw new Exception('The session you selected is a Standard Session but you have added Meal Prep Workshop items to the cart. If you wish to place a standard order please try again.', self::dd_std_session_exception_code);
			}
		}

		if ($sessionObj->store_id != $this->store_id)
		{
			throw new Exception('The session does not belong to this store.', self::dd_general_exception_code);
		}

		$userObj = DAO_CFactory::create('user');
		$userObj->id = $this->user_id;
		if (!$userObj->find(true))
		{
			throw new Exception('The user account could not be found.', self::dd_general_exception_code);
		}

		if ($sessionObj->session_type == CSession::DREAM_TASTE)
		{
			$dreamTasteProperties = CDreamTasteEvent::sessionProperties($sessionObj->id);

			if (!$userObj->isEligibleForDreamTaste() && $userObj->id != $dreamTasteProperties->session_host)
			{
				if (CUser::getCurrentUser()->user_type == CUser::SITE_ADMIN)
				{
					//$tpl->setErrorMsg('This account has previous orders. However the order is permitted because of your SITE_ADMIN privileges.');
				}
				else
				{
					if (defined('ALLOW_TV_OFFER_IF_PREVIOUS') && ALLOW_TV_OFFER_IF_PREVIOUS)
					{
						//$tpl->setErrorMsg('This account has previous orders. This order is permitted on this test server but would be prohibited in production.');
					}
					else
					{
						throw new Exception('Guests must be new or reacquired in order to attend a Meal Prep Workshop session.', self::dd_general_exception_code);
					}
				}
			}
		}

		$orderObj = DAO_CFactory::create('orders');
		$orderObj->user_id = $this->user_id;
		$orderObj->my_meals_rating_user_id = $this->user_id;
		$orderObj->store_id = $this->store_id;
		$orderObj->grand_total = 0;
		$orderObj->subtotal_all_items = 0;
		$orderObj->subtotal_all_taxes = 0;
		$orderObj->menu_items_total_count = 0;
		$orderObj->is_sampler = 0;
		$orderObj->pcal_preassembled_total_count = 0;
		$orderObj->pcal_sidedish_total_count = 0;
		$orderObj->pcal_preassembled_total = 0;
		$orderObj->pcal_sidedish_total = 0;
		$orderObj->menu_program_id = 0;
		$orderObj->is_deleted = 0;
		$orderObj->product_items_total_count = 0;
		$orderObj->order_type = 'DIRECT';
		if ($userObj->dream_rewards_version == 3 && ($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3))
		{
			$orderObj->is_in_plate_points_program = 1;
		}

		$orderCustomizationWrapper = OrdersCustomization::getInstance($orderObj);
		$mealCustomizationPrefObj = $orderCustomizationWrapper->mealCustomizationToObj($userObj);
		$orderObj = $orderCustomizationWrapper->updateMealCustomization($mealCustomizationPrefObj,false);

		$fundraiserSessionPropObj = CFundraiser::fundraiserEventSessionProperties($sessionObj);
		if (!empty($fundraiserSessionPropObj) && !empty($fundraiserSessionPropObj->fundraiser_id) && !empty($fundraiserSessionPropObj->fundraiser_value))
		{
			$orderObj->fundraiser_id = $fundraiserSessionPropObj->fundraiser_id;
			$orderObj->fundraiser_value = $fundraiserSessionPropObj->fundraiser_value;
		}

		if (!empty($_REQUEST['special_inst']))
		{
			// note:: XSS and injection filtering has already occurred here and in javascript
			$orderObj->order_user_notes = $_REQUEST['special_inst'];
		}

		$activePreferred = DAO_CFactory::create('user_preferred');
		$activePreferred->user_id = $this->user_id;
		if ($activePreferred->findActive($this->store_id))
		{
			$activePreferred->fetch();
			$orderObj->user_preferred_id = $activePreferred->id;
		}

		if (isset($sessionObj->session_discount_id) && $sessionObj->session_discount_id)
		{
			$activeSessionDiscount = DAO_CFactory::create('session_discount');
			$activeSessionDiscount->id = $sessionObj->session_discount_id;
			if ($activeSessionDiscount->find(true))
			{
				$orderObj->session_discount_id = $activeSessionDiscount->id;
			}
		}

		$orderObj->addSession($sessionObj);

		// new order so we need to add the markup and sales tax onject ids
		$Store = $orderObj->getStore();

		$taxObj = $Store->getCurrentSalesTaxObj();

		if ($taxObj)
		{
			$orderObj->sales_tax_id = $taxObj->id;
		}

		$Markup = $Store->getMarkUpMultiObj($sessionObj->menu_id);
		$orderObj->mark_up_multi_id = $Markup->id;

		$orderObj->addMarkup($Markup);
		$orderObj->applyServiceFee();
		$orderObj->applyDeliveryFee();

		$fullSessionWarningNeeded = false;

		$activeRSVPs = $sessionObj->get_RSVP_count($this->user_id);

		if ($enforceSlotAvailability)
		{
			if ($bookingType == CBooking::INTRO && $sessionObj->getRemainingIntroSlots() <= 0)
			{
				throw new Exception('The session has no open slots for an introductory order. Please return to the Sessions and Menu Page and select a new session.', self::dd_general_exception_code);
			}
			else if ($sessionObj->getRemainingSlots() - $activeRSVPs <= 0)
			{

				throw new Exception('The session has no open slots for this order. Please return to the Sessions and Menu Page and select a new session.', self::dd_general_exception_code);
			}
		}
		else
		{
			// warn if saving to a full session.

			if ($bookingType == CBooking::INTRO && $sessionObj->getRemainingIntroSlots <= 0)
			{
				$fullSessionWarningNeeded = true;
			}
			else if ($sessionObj->getRemainingSlots() - $activeRSVPs <= 0)
			{
				$fullSessionWarningNeeded = true;
			}
		}

		$orderObj->insert(true);

		$bookingObj = DAO_CFactory::create('booking');
		$bookingObj->user_id = $this->user_id;
		$bookingObj->session_id = $this->targetSession;
		$bookingObj->order_id = $orderObj->id;
		$bookingObj->booking_type = $bookingType;
		$bookingObj->status = CBooking::SAVED;

		$bookingObj->insert();

		$this->order_id = $orderObj->id;

		return $fullSessionWarningNeeded;
	}

	function setSessionAndSave()
	{
		CLog::RecordDebugTrace('order_mgr_processor::setSessionAndSave called for user: ' . $this->user_id, "TR_TRACING");

		try
		{

			$fullSessionWarningNeeded = $this->doSetSessionAndSave();

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'Order has been saved.',
				'order_id' => $this->order_id,
				'full_session_warning_required' => $fullSessionWarningNeeded
			));
		}
		catch (Exception $e)
		{

			if ($e->getCode() == self::dd_general_exception_code)
			{

				$csrftoken = CSRF::getNewToken('order_mgr');

				// unexpected problem occurred so fail bith a new CSRF code
				echo json_encode(array(
					'processor_success' => false,
					'getTokenToken' => $csrftoken,
					'processor_message' => $e->getMessage()
				));
				exit;
			}
			else if ($e->getCode() == self::dd_taste_session_exception_code || $e->getCode() == self::dd_std_session_exception_code)
			{
				$csrftoken = CSRF::getNewToken('order_mgr');

				// unexpected problem occurred so fail bith a new CSRF code
				echo json_encode(array(
					'processor_success' => false,
					'getTokenToken' => $csrftoken,
					'bounce_to_menu' => true,
					'processor_message' => $e->getMessage()
				));
				exit;
			}
			else
			{
				throw $e;
			}
		}
	}

	function fullyRestoreBundleObject($order, $session)
	{
		if (!empty($order->bundle_id) && $order->type_of_order == 'INTRO')
		{
			$activeBundle = CBundle::getActiveBundleForMenu($session->menu_id, $order->store_id);

			$items = $order->getItems();
			$selectedBIs = array();
			foreach ($items as $id => $data)
			{
				$itemObj = $data[1];
				if ($itemObj->bundle_id == $activeBundle->id)
				{
					$selectedBIs[] = $id;
				}
			}

			$order->addBundle($activeBundle, $selectedBIs, true);
		}
	}

	function reschedule()
	{

		$OrderState = "UNKNOWN";
		if (!empty($_REQUEST['order_state']))
		{
			$OrderState = $_REQUEST['order_state'];
		}

		$TransitionType = "UNKNOWN";
		if (!empty($_REQUEST['transition_type']))
		{
			$TransitionType = $_REQUEST['transition_type'];
		}

		$SuppressEmail = false;
		if (!empty($_REQUEST['suppressEmail']) && $_REQUEST['suppressEmail'] == 'true')
		{
			$SuppressEmail = true;
		}

		$TargetSession = DAO_CFactory::create('session');
		$TargetSession->id = $this->targetSession;
		if (!$TargetSession->find(true))
		{
			throw new Exception("Request for target session failed");
		}

		$OrigSession = DAO_CFactory::create('session');
		$OrigSession->id = $this->org_session_id;
		if (!$OrigSession->find(true))
		{
			throw new Exception("Request for original session failed");
		}

		$thisOrder = DAO_CFactory::create('orders');
		$thisOrder->id = $_POST["order_id"];

		if (!$thisOrder->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'Target session not found.'
			));
			exit;
		}

		$thisOrder->addSession($TargetSession);

		if (!empty($_REQUEST['saved_booking_id']) && is_numeric($_REQUEST['saved_booking_id']))
		{
			$bookingObj = DAO_CFactory::create('booking');
			$bookingObj->id = $_REQUEST['saved_booking_id'];
			if (!$bookingObj->find(true))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The reschedule attempt failed - saved booking not found'
				));
				exit;
			}

			$result = $thisOrder->rescheduleSavedOrder($this->targetSession, $bookingObj);
		}
		else
		{
			$result = $thisOrder->reschedule($this->org_session_id, true, true);// true meaning this is a fadmin, relax rules regarding date and overbooking
		}

		$serviceFeeUpdate = -1;
		$serviceFeeDescUpdate = "";

		if ($result === 'success')
		{
			$canDelayPayment = false;
			$sessionTS = strtotime($TargetSession->session_start) - 518400; // allow delayed payment 6 days prior
			if (strtotime("now") < $sessionTS)
			{
				$canDelayPayment = true;
			}

			list ($activeSD, $originalSD) = page_admin_order_mgr::buildSessionDiscountArray(null, $TargetSession);

			// TODO: goes in specific handler
			//if ($TargetSession->session_type_subtype != CSession::DELIVERY && $thisOrder->subtotal_delivery_fee > 0)
			//{
			//	$thisOrder->query("update orders set subtotal_service_fee = 0 where order_id = {$thisOrder->id}");
			//	$thisOrder->subtotal_delivery_fee = 0;
			//	}

			$netChangeToRevenue = 0;

			$clientOperationsArray = array();
			/* instructions for Javascript client
			adj_service_fee => new amount,
			adj_delivery_fee => new_amount,
			require_delivery_address,
			update_svc_fee_desc
			delete_delivery_address */

			switch ($TransitionType)
			{
				case 'WI_to_Assembled':
				case 'MFY_to_Assembled':
				case 'CPU_to_Assembled':
					{
						// moving from Pick Up to Assembled

						$orgOrder = clone($thisOrder);
						$thisOrder->reconstruct(true);
						$thisOrder->findSession(true);

						$originalFees = $thisOrder->subtotal_service_fee + $thisOrder->subtotal_delivery_fee;

						$thisOrder->subtotal_delivery_fee = 0;
						$thisOrder->subtotal_service_fee = 0;

						$this->fullyRestoreBundleObject($thisOrder, $OrigSession);
						$thisOrder->refreshForEditing($OrigSession->menu_id);
						$thisOrder->recalculate(false, false, 'to_non_mfy');

						$netChangeToRevenue -= $originalFees;

						$thisOrder->update($orgOrder);

						$clientOperationsArray['adj_service_fee'] = 0;
						$clientOperationsArray['update_svc_fee_desc'] = "";
						//$clientOperationsArray['adj_delivery_fee'] = 0;
					}
					break;
				case 'Assembly_to_WI':
				case 'Assembly_to_MFY':
				case 'Assembly_to_CPU':
					{
						// moving from Assembled to Pick Up

						$orgOrder = clone($thisOrder);
						$thisOrder->reconstruct(true);
						$thisOrder->findSession(true);
						$this->fullyRestoreBundleObject($thisOrder, $OrigSession);
						$thisOrder->refreshForEditing($OrigSession->menu_id);
						$thisOrder->recalculate(false, false, 'to_mfy');

						$thisOrder->update($orgOrder);

						$serviceFeeDescUpdate = $thisOrder->service_fee_description;
						$serviceFeeUpdate = $thisOrder->subtotal_service_fee;
						$deliveryFeeUpdate = $thisOrder->subtotal_delivery_fee;

						$netChangeToRevenue = $serviceFeeUpdate + $deliveryFeeUpdate;
						$clientOperationsArray['adj_service_fee'] = $thisOrder->subtotal_service_fee;
						//$clientOperationsArray['adj_delivery_fee'] = $thisOrder->subtotal_delivery_fee;

					}
					break;
				case 'WI_to_HD':
				case 'MFY_to_HD':
				case 'CPU_to_HD':
					{
						// moving from Pick Up to Home Delivery

						$originalFee = $thisOrder->subtotal_service_fee;
						$originalDeliveryFee = $thisOrder->subtotal_delivery_fee;

						// TODO: will the fees be adjusted automatically

						$orgOrder = clone($thisOrder);
						$thisOrder->reconstruct(true);
						$thisOrder->findSession(true);
						$this->fullyRestoreBundleObject($thisOrder, $OrigSession);
						$thisOrder->refreshForEditing($OrigSession->menu_id);
						$thisOrder->recalculate(false, false, 'to_mfy');

						$thisOrder->update($orgOrder);

						$serviceFeeDescUpdate = $thisOrder->service_fee_description;
						$serviceFeeUpdate = $thisOrder->subtotal_service_fee;
						$DeliveryFeeUpdate = $thisOrder->subtotal_delivery_fee;

						$netChangeToRevenue = ($serviceFeeUpdate - $originalFee) + ($DeliveryFeeUpdate - $originalDeliveryFee);
						$clientOperationsArray['adj_service_fee'] = $thisOrder->subtotal_service_fee;
						$clientOperationsArray['adj_delivery_fee'] = $thisOrder->subtotal_delivery_fee;
						$clientOperationsArray['require_delivery_address'] = true;
						$clientOperationsArray['update_svc_fee_desc'] = $serviceFeeDescUpdate;
					}
					break;
				case 'HD_to_Assembled':
					{
						// moving from Home Delivery to Assembled

						$originalFee = $thisOrder->subtotal_service_fee;
						$originalDeliveryFee = $thisOrder->subtotal_delivery_fee;
						$thisOrder->subtotal_delivery_fee = 0;
						$thisOrder->subtotal_service_fee = 0;
						$thisOrder->service_fee_description = "";

						// TODO: will the fees be adjusted automatically

						$orgOrder = clone($thisOrder);
						$thisOrder->reconstruct(true);
						$thisOrder->findSession(true);
						$this->fullyRestoreBundleObject($thisOrder, $OrigSession);
						$thisOrder->refreshForEditing($OrigSession->menu_id);
						$thisOrder->recalculate(false, false, 'to_mfy');

						$thisOrder->update($orgOrder);

						$serviceFeeDescUpdate = $thisOrder->service_fee_description;
						$serviceFeeUpdate = $thisOrder->subtotal_service_fee;
						$DeliveryFeeUpdate = $thisOrder->subtotal_delivery_fee;

						$netChangeToRevenue = ($serviceFeeUpdate - $originalFee) + ($DeliveryFeeUpdate - $originalDeliveryFee);
						$clientOperationsArray['adj_service_fee'] = $thisOrder->subtotal_service_fee;
						$clientOperationsArray['adj_delivery_fee'] = $thisOrder->subtotal_delivery_fee;
						$clientOperationsArray['delete_delivery_address'] = true;
						$clientOperationsArray['update_svc_fee_desc'] = $serviceFeeDescUpdate;

						$orderAddress = DAO_CFactory::create('orders_address');
						$orderAddress->order_id = $thisOrder->id;
						$orderAddress->find(true);
						$orderAddress->delete();
					}
					break;
				case 'HD_to_WI':
				case 'HD_to_MFY':
				case 'HD_to_CPU':
					{
						// moving from Home Delivery to Pick Up

						$originalFee = $thisOrder->subtotal_service_fee;
						$originalDeliveryFee = $thisOrder->subtotal_delivery_fee;

						$orgOrder = clone($thisOrder);
						$thisOrder->reconstruct(true);
						$thisOrder->findSession(true);
						$this->fullyRestoreBundleObject($thisOrder, $OrigSession);
						$thisOrder->refreshForEditing($OrigSession->menu_id);
						$thisOrder->recalculate(false, false, 'to_mfy');

						$thisOrder->update($orgOrder);

						$serviceFeeDescUpdate = $thisOrder->service_fee_description;
						$serviceFeeUpdate = $thisOrder->subtotal_service_fee;
						$DeliveryFeeUpdate = $thisOrder->subtotal_delivery_fee;

						$netChangeToRevenue = ($serviceFeeUpdate - $originalFee) + ($DeliveryFeeUpdate - $originalDeliveryFee);
						$clientOperationsArray['adj_service_fee'] = $thisOrder->subtotal_service_fee;
						$clientOperationsArray['adj_delivery_fee'] = $thisOrder->subtotal_delivery_fee;
						$clientOperationsArray['delete_delivery_address'] = true;
						$clientOperationsArray['update_svc_fee_desc'] = $serviceFeeDescUpdate;

						$orderAddress = DAO_CFactory::create('orders_address');
						$orderAddress->order_id = $thisOrder->id;
						$orderAddress->find(true);
						$orderAddress->delete();
					}
					break;
				case 'Assembly_to_HD':
					{
						// moving from Assembly to Home Delivery

						$originalFee = 0;
						$originalDeliveryFee = 0;

						// TODO: will the fees be adjusted automatically

						$orgOrder = clone($thisOrder);
						$thisOrder->reconstruct(true);
						$thisOrder->findSession(true);
						$this->fullyRestoreBundleObject($thisOrder, $OrigSession);
						$thisOrder->refreshForEditing($OrigSession->menu_id);
						$thisOrder->recalculate(false, false, 'to_mfy');

						$thisOrder->update($orgOrder);

						$serviceFeeDescUpdate = $thisOrder->service_fee_description;
						$serviceFeeUpdate = $thisOrder->subtotal_service_fee;
						$DeliveryFeeUpdate = $thisOrder->subtotal_delivery_fee;

						$netChangeToRevenue = ($serviceFeeUpdate - $originalFee) + ($DeliveryFeeUpdate - $originalDeliveryFee);
						$clientOperationsArray['adj_service_fee'] = $thisOrder->subtotal_service_fee;
						$clientOperationsArray['adj_delivery_fee'] = $thisOrder->subtotal_delivery_fee;
						$clientOperationsArray['require_delivery_address'] = true;
						$clientOperationsArray['update_svc_fee_desc'] = $serviceFeeDescUpdate;
					}
					break;
			}

			if ($netChangeToRevenue != 0 && $OrderState != 'SAVED')
			{
				$revenueEvent = DAO_CFactory::create('revenue_event');
				$revenueEvent->event_type = 'EDITED';
				$revenueEvent->event_time = date("Y-m-d H:i:s");
				$revenueEvent->store_id = $thisOrder->store_id;
				$revenueEvent->menu_id = $thisOrder->findSession()->menu_id;
				$revenueEvent->amount = $netChangeToRevenue;
				$revenueEvent->session_amount = $netChangeToRevenue;
				$revenueEvent->session_id = $thisOrder->findSession()->id;
				$revenueEvent->final_session_id = $thisOrder->findSession()->id;
				$revenueEvent->order_id = $thisOrder->id;
				$revenueEvent->positive_affected_month = date("Y-m-01", strtotime($thisOrder->findSession()->session_start));
				$revenueEvent->negative_affected_month = 'null'; // note: negative month is used for reschedules only
				$revenueEvent->insert();

				// Note: If the store does not follow up and finalize the order the agr stored in orders_digest does not get updated. So do it here
				$newAGR = COrdersDigest::calculateAGRTotal($thisOrder->id, $thisOrder->grand_total, $thisOrder->subtotal_all_taxes, $thisOrder->fundraiser_value, $thisOrder->subtotal_ltd_menu_item_value, $thisOrder->subtotal_bag_fee);
				$newBalanceDue = COrdersDigest::calculateAndAddBalanceDue($thisOrder->id, $thisOrder->grand_total);
				$digestObj = DAO_CFactory::create('orders_digest');
				$digestObj->query("update orders_digest set agr_total = $newAGR, balance_due = $newBalanceDue where order_id = {$thisOrder->id}");
			}

			$additionalSessionProps = $TargetSession->getSessionTypeProperties();

			$sessionInfo = array(
				'session_type' => $TargetSession->session_type,
				'session_type_title' => $additionalSessionProps[0],
				'remaining_slots' => $TargetSession->getRemainingSlots(),
				'remaining_intro_slots' => $TargetSession->getRemainingIntroSlots()
			);

			if (isset($TargetSession->session_discount_id) && is_numeric($TargetSession->session_discount_id) && $TargetSession->session_discount_id > 0)
			{
				$sessionInfo['session_type_title'] .= " (Discounted)";
			}

			$User = DAO_CFactory::create('user');
			$User->id = $thisOrder->user_id;
			$User->find(true);

			if ($OrderState != 'SAVED' && !$SuppressEmail)
			{
				COrders::sendRescheduleEmail($User, $thisOrder, $OrigSession->session_start);
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'The order was successfully rescheduled.',
				'new_session_id' => $TargetSession->id,
				'new_session_time' => CTemplate::dateTimeFormat($TargetSession->session_start),
				'canDelayPayment' => $canDelayPayment,
				'session_discount' => $activeSD,
				'session_info' => $sessionInfo,
				'client_ops' => $clientOperationsArray
			));
			exit;
		}
		else if ($result === 'closed')
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The reschedule attempt failed because the target session is expired.'
			));
			exit;
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The reschedule attempt failed for an unknown reason'
			));
			exit;
		}
	}

	/**
	 * @return ($order, $success, $msg)
	 */
	function addMenuItemsToOrder($OrderObj, $orgPrices, $items, $isIntroOrder, $introItems, $subItemsArr, $ltdOrderedMealsArray)
	{

		//get menu from session
		$menu_id = $OrderObj->findSession()->menu_id;

		$addFinishingTouchOnly = false;

		// The order->items currently has the original mark down id and value.  Record these here and then re-apply them after rebuilding the list
		$oldItems = $OrderObj->getItems();

		$oldMarkdowns = array();

		if ($oldItems)
		{
			foreach ($oldItems as $id => $item)
			{
				if (isset($item[1]->markdown_id) && is_numeric($item[1]->markdown_id) && $item[1]->markdown_id > 0)
				{

					// We also need the original price - the passed in org price reflects the markdown so further calculations are off unless we restore marked up price
					$markedUpPrice = CTemplate::moneyFormat($orgPrices[$id] / ((100 - $item[1]->markdown_value) / 100));

					$oldMarkdowns[$id] = array(
						'markdown_id' => $item[1]->markdown_id,
						'markdown_value' => $item[1]->markdown_value,
						'marked_up_price' => $markedUpPrice
					);
				}
			}
		}

		//clear existing menu items
		$OrderObj->clearItemsUnsafe();

		if ($isIntroOrder)
		{
			$activeBundle = CBundle::getActiveBundleForMenu($menu_id, $OrderObj->store_id);

			$selectedBIs = array();
			foreach ($introItems as $k => $v)
			{
				if ($v == "on")
				{
					$selectedBIs[] = $k;
				}
			}

			if (empty($selectedBIs))
			{
				$OrderObj->bundle_id = $activeBundle->id;
			}
			else
			{

				$OrderObj->addBundle($activeBundle, $selectedBIs);
			}
		}
		else
		{
			$OrderObj->addBundle(null);
		}

		$Session = $OrderObj->findSession();
		if ($Session->session_type == CSession::DREAM_TASTE)
		{
			$dreamEventProperties = CDreamTasteEvent::sessionProperties($Session->id);

			$theBundle = DAO_CFactory::create('bundle');
			$theBundle->id = $dreamEventProperties->bundle_id;
			if (!$theBundle->find(true))
			{
				throw new Exception('Bundle not found for the selected session.');
			}

			$selectedBIs = array();
			foreach ($items as $k => $v)
			{
				$selectedBIs[$k] = $v;
			}

			// Bundle Price Intercept Point
			$storeObj = DAO_CFactory::create('store');
			$storeObj->query("select is_corporate_owned from store where id = {$Session->store_id}");
			$storeObj->fetch();

			if ($Session->menu_id <= 184 && $Session->session_type == CSession::DREAM_TASTE && $storeObj->is_corporate_owned)
			{
				$theBundle->price = 34.99;
			}

			$OrderObj->addTasteBundle($theBundle, $selectedBIs);

			$addFinishingTouchOnly = true;
		}

		if ($Session->session_type == CSession::FUNDRAISER)
		{
			$fundraiserEventProperties = CFundraiser::fundraiserEventSessionProperties($Session);

			$theBundle = DAO_CFactory::create('bundle');
			$theBundle->id = $fundraiserEventProperties->bundle_id;
			if (!$theBundle->find(true))
			{
				throw new Exception('Bundle not found for the selected session.');
			}

			$selectedBIs = array();
			foreach ($items as $k => $v)
			{
				$selectedBIs[$k] = $v;
			}

			$OrderObj->addTasteBundle($theBundle, $selectedBIs);

			$addFinishingTouchOnly = true;
		}

		$getStoreMenu = CMenu::storeSpecificMenuExists($menu_id, $OrderObj->store_id);

		//look for coupon code
		if (isset($_POST['coupon_id']) && !empty($_POST['coupon_id']))
		{
			$Coupon = DAO_CFactory::create('coupon_code');
			$Coupon->id = $_POST['coupon_id'];

			if ($Coupon->find(true))
			{
				// add the promo item, mark it as as promo by passing true in addMenuItem

				if ($Coupon->discount_method == CCouponCode::FREE_MEAL && !empty($Coupon->menu_item_id))
				{
					$menuItemInfo = DAO_CFactory::create('menu_item');
					if ($getStoreMenu)
					{
						$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id = " . $OrderObj->store_id . " AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id = " . $Coupon->menu_item_id . " AND mi.is_deleted = 0";
					}
					else
					{
						$query = "SELECT mmi.override_price AS override_price, mi.* FROM menu_item  mi " . "LEFT JOIN menu_to_menu_item mmi ON mi.id = mmi.menu_item_id AND mmi.store_id IS NULL AND mmi.menu_id = " . $menu_id . " AND mmi.is_deleted = 0 " . "WHERE mi.id = " . $Coupon->menu_item_id . " AND mi.is_deleted = 0";
					}
					$menuItemInfo->query($query);
					if ($menuItemInfo->fetch())
					{
						$OrderObj->addMenuItem(clone($menuItemInfo), 1, false, true);
					}
				}
			}
		}

		$menu_item_ids = implode(",", array_keys($items));

		if ($menu_item_ids)
		{
			$DAO_menu = DAO_CFactory::create('menu', true);
			$DAO_menu->id = $menu_id;
			$menuItemInfo = $DAO_menu->findMenuItemDAO(array(
				'join_order_item_order_id' => array($OrderObj->id),
				'menu_to_menu_item_store_id' => (!empty($OrderObj->store_id)) ? $OrderObj->store_id : 'NULL',
				'exclude_menu_item_category_core' => false,
				'exclude_menu_item_category_efl' => false,
				'exclude_menu_item_category_sides_sweets' => false,
				'menu_item_id_list' => $menu_item_ids
			));

			while ($menuItemInfo->fetch())
			{
				if ($addFinishingTouchOnly && !$menuItemInfo->isMenuItem_SidesSweets())
				{
					continue;
				}

				$qty = $items[$menuItemInfo->id];

				if ($menuItemInfo->is_bundle && $qty > 0)
				{
					$subItems = CBundle::getBundleMenuInfoForMenuItem($menuItemInfo->id, $menu_id, $OrderObj->store_id);
					$subItemKeys = array();
					foreach ($subItemsArr as $itemKey => $qtySubItems)
					{
						if ($qtySubItems > 0 && array_key_exists($itemKey, $subItems['bundle']))
						{
							$subItemKeys[] = $itemKey;
						}
					}

					$subItemInfo = $DAO_menu->findMenuItemDAO(array(
						'join_order_item_order_id' => array($OrderObj->id),
						'menu_to_menu_item_store_id' => (!empty($OrderObj->store_id)) ? $OrderObj->store_id : 'NULL',
						'exclude_menu_item_category_core' => false,
						'exclude_menu_item_category_efl' => false,
						'exclude_menu_item_category_sides_sweets' => false,
						'menu_item_id_list' => implode(",", $subItemKeys)
					));

					while ($subItemInfo->fetch())
					{
						$subqty = $subItemsArr[$subItemInfo->id];
						if ($subqty)
						{
							$subItemInfo->parentItemId = $menuItemInfo->id;
							$subItemInfo->bundleItemCount = $subqty;
							$OrderObj->addMenuItem(clone($subItemInfo), $subqty);
						}
					}
				}

				if ($qty)
				{

					if (isset($oldMarkdowns[$menuItemInfo->id]))
					{
						$menuItemInfo->override_price = $oldMarkdowns[$menuItemInfo->id]['marked_up_price'];
						$menuItemInfo->markdown_id = $oldMarkdowns[$menuItemInfo->id]['markdown_id'];
						$menuItemInfo->markdown_value = $oldMarkdowns[$menuItemInfo->id]['markdown_value'];
					}

					if (in_array($menuItemInfo->id, $ltdOrderedMealsArray))
					{
						$menuItemInfo->order_item_ltd_menu_item = true;
						//if the item was ordered originally then forcing to org pricing adds the donation
						// If it's a new MotM item then we need to update the price here

						$menuItemInfo->override_price = COrders::getStorePrice($OrderObj->findMarkUp(), $menuItemInfo, 1);
						if(!empty($orgPrices) && !is_null($orgPrices[$menuItemInfo->id])){
							$menuItemInfo->override_price = $orgPrices[$menuItemInfo->id];
						}
					}
					else
					{
						$menuItemInfo->order_item_ltd_menu_item = false;
					}

					$OrderObj->addMenuItem(clone($menuItemInfo), $qty);
				}
			}
		}

		return $OrderObj;
	}

	function processStoreCredits($amountToUse, $Store_Credit_Array, $OrderObj)
	{
		$sc_payment_array = array();
		$amount_paid = 0;

		if ($amountToUse > 0 && $amountToUse < 500)// sanity check
		{
			foreach ($Store_Credit_Array as $id => $vals)
			{
				$amount_paid += $vals['Amount'];

				$newPayment = DAO_CFactory::create('payment');
				$newPayment->user_id = $OrderObj->user_id;
				$newPayment->store_id = $OrderObj->store_id;
				$newPayment->order_id = $OrderObj->id;
				$newPayment->is_delayed_payment = 0;
				$newPayment->payment_type = CPayment::STORE_CREDIT;
				$newPayment->store_credit_id = $id;
				$newPayment->payment_number = $vals['Source'];
				$newPayment->payment_note = 'null';
				$newPayment->is_escrip_customer = 0;
				$newPayment->store_credit_DAO = $vals['DAO_Obj'];

				if ($amount_paid >= $amountToUse)
				{
					$newPayment->total_amount = CTemplate::moneyFormat($amountToUse - ($amount_paid - $vals['Amount']));
					array_push($sc_payment_array, $newPayment);
					break;
				}
				else
				{
					$newPayment->total_amount = CTemplate::moneyFormat($vals['Amount']);
					array_push($sc_payment_array, $newPayment);
				}
			}

			if (!empty($sc_payment_array))
			{
				$OrderObj->processStoreCreditPayments($sc_payment_array);
			}
		}
	}

	function ConvertOrderToMembership($inMembershipID = false, $runAsInternal = false)
	{
		if (!empty($_REQUEST['membership_id']) && is_numeric($_REQUEST['membership_id']))
		{
			$membershipID = $_REQUEST['membership_id'];
		}
		else if ($inMembershipID)
		{
			$membershipID = $inMembershipID;
		}
		else
		{
			if (!$runAsInternal)
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'invalid_membership_id'
				));
				exit;
			}

			return array(
				'processor_success' => false,
				'processor_message' => 'invalid_membership_id'
			);
		}

		$orderObj = DAO_CFactory::create('orders');
		$orderObj->id = $this->order_id;

		if ($orderObj->find(true))
		{

			if (!empty($orderObj->membership_orders_id))
			{
				if (!$runAsInternal)
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'already_in_membership'
					));
					exit;
				}

				return array(
					'processor_success' => false,
					'processor_message' => 'already_in_membership'
				);
			}

			// ensure other discounts are deleted

			$orderObj->session_discount_total = 0;
			$orderObj->session_discount_id = 'null';

			if (!empty($orderObj->coupon_code_id))
			{
				$coupon = new DAO();
				$coupon->query("select coupon_code from coupon_code where id = " . $orderObj->coupon_code_id);
				$coupon->fetch();

				if (!empty($coupon->limit_to_mfy_fee))
				{
					$orderObj->coupon_code_discount_total = 0;
					$orderObj->coupon_code_id = 'null';
				}

				if (!empty($coupon->limit_to_delivery_fee))
				{
					$orderObj->coupon_code_discount_total = 0;
					$orderObj->coupon_code_id = 'null';
				}
			}

			$orderObj->user_preferred_discount_total = 0;
			$orderObj->user_preferred_id = 'null';
			$orderObj->membership_id = $membershipID;
			$orderObj->reconstruct();
			$orderObj->refreshForEditing();
			$orderObj->recalculate(true, true);
			$orderObj->update();
			// succeed
			if (!$runAsInternal)
			{
				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'success'
				));
				exit;
			}

			return array(
				'processor_success' => true,
				'processor_message' => 'success'
			);
		}
		else
		{
			if (!$runAsInternal)
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'order_not_found'
				));
				exit;
			}

			return array(
				'processor_success' => false,
				'processor_message' => 'order_not_found'
			);
		}
	}

	function ConvertOrderFromMembership($runAsInternal = false)
	{

		$orderObj = DAO_CFactory::create('orders');
		$orderObj->id = $this->order_id;

		if ($orderObj->find(true))
		{

			if (empty($orderObj->membership_id))
			{
				if (!$runAsInternal)
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'order_not_in_membership.'
					));
					exit;
				}

				return array(
					'processor_success' => false,
					'processor_message' => 'order_not_in_membership.'
				);
			}

			// ensure other discounts are deleted

			$orderObj->reconstruct();
			$orderObj->refreshForEditing();

			$orderObj->membership_id = 0;
			$orderObj->membership_discount = 0.00;

			$orderObj->recalculate(true, true);
			$orderObj->update();

			// succeed
			if (!$runAsInternal)
			{
				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'success'
				));
				exit;
			}

			return array(
				'processor_success' => true,
				'processor_message' => 'success'
			);
		}
		else
		{
			if (!$runAsInternal)
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'order_not_found'
				));
				exit;
			}

			return array(
				'processor_success' => false,
				'processor_message' => 'order_not_found'
			);
		}
	}

}

?>