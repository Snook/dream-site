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
require_once('includes/DAO/BusinessObject/CBoxInstance.php');
require_once('includes/DAO/BusinessObject/CBox.php');

class processor_admin_order_mgr_processor_delivered extends CPageProcessor
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

	function __construct()
	{
		$this->inputTypeMap['ltdOrderedMealsArray'] = TYPE_NOCLEAN;
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

			$this->setSessionAndSave();
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'add_box')
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

			if (empty($_REQUEST['box_id']) || !is_numeric($_REQUEST['box_id']))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The box id is invalid.'
				));
				exit;
			}

			if (!empty($_REQUEST['bundle_id']) && is_numeric($_REQUEST['bundle_id']))
			{
				list ($add_result, $bundleInfo) = $this->addBoxToOrder($_REQUEST['bundle_id']);
				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => "Successfully cleared box for addition.",
					'html' => $add_result,
					'bundle_info' => $bundleInfo
				));
				exit;
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The bundle id is invalid.'
				));
				exit;
			}
		}
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'delete_box')
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

			if (empty($_REQUEST['box_inst_id']) || !is_numeric($_REQUEST['box_inst_id']))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'The box instance id is invalid.'
				));
				exit;
			}

			$this->deleteBoxFromOrder($_REQUEST['box_inst_id']);
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
				if (!empty($_POST['order_state']))
				{
					$this->setDeliveryAddressAndSaveOrder($_REQUEST['shipping_data']);
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'A problem occurred while saving the delivery address. Unknown Order State.',
					));
					exit;
				}
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
		else if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'retrieve_address_from_address_book')
		{
			if (empty($_REQUEST['address_id']) || !is_numeric($_REQUEST['address_id']))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while retrieving the address. Invalid address ID.',
				));
				exit;
			}

			$this->retrieveAddress($_REQUEST['address_id']);
		}
	}

	function retrieveAddress($address_id)
	{
		$addressObj = DAO_CFactory::create('address');
		$addressObj->id = $address_id;
		if ($addressObj->find(true))
		{
			echo json_encode(array(
				'processor_success' => true,
				'address' => DAO::getCompressedArrayFromDAO($addressObj),
				'processor_message' => 'Address Successfully retrieved.',
			));
			exit;
		}

		echo json_encode(array(
			'processor_success' => false,
			'processor_message' => 'A problem occurred while retrieving the address. Address not found.',
		));
		exit;
	}

	function deleteBoxFromOrder($box_inst_id)
	{
		$box_instance = DAO_CFactory::create('box_instance');
		$box_instance->id = $box_inst_id;
		$box_instance->order_id = $this->order_id;



		if ($box_instance->find())
		{

			$bundle = DAO_CFactory::create('bundle');
			$bundle->id = $box_instance->bundle_id;
			$bundle->find(true);

			$box_instance->delete();

			$orderItems = DAO_CFactory::create('order_item');
			$orderItems->order_id = $this->order_id;
			$orderItems->box_id = $box_inst_id;
			if ($orderItems->find(true))
			{
				$orderItems->delete();
			}

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'The box instance was deleted from the order.',
				'bundle_info' => DAO::getCompressedArrayFromDAO($bundle)
			));
			exit;
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'A problem occurred while deleting the box. Invalid Box Instance ID.',
			));
			exit;
		}
	}

	/*
	 *  Return an HTML fragment for the box editor
	 *  or False if a problem occurs
	 */
	function addBoxToOrder($bundle_id)
	{
		// TODO: check inventory

		$bundle = DAO_CFactory::create('bundle');
		$bundle->id = $bundle_id;
		$bundle->find(true);

		$items = CBundle::getDeliveredBundleByID($bundle_id, true);

		// Note:  for the order manager we can start with our instance as marked as complete since we can validate everything at once
		$boxInstanceID = CBoxInstance::getNewEmptyBoxForBundle($bundle_id, $_REQUEST['box_id'], $this->order_id, false, true, true);

		$tpl = new CTemplate();
		$tpl->assign('bundle_data', $bundle);
		$tpl->assign('bundle_items', $items['bundle']);
		$tpl->assign('box_inst_id', $boxInstanceID);
		$tpl->assign('box_id', $_REQUEST['box_id']);
		$tpl->assign('box_type', $_REQUEST['box_type']);
		$tpl->assign('box_label', $_REQUEST['box_label']);
		$tpl->assign('is_saved', false);

		// Note: We can't add inventory levels here unless we passed in what has been added to the order byt not finalized.
		// Instead, trigger javascript to add inventory based on levels at page load time
		// If the system becomes heavily loaded we may want to update the javascript inventory array with this process.

		$html = $tpl->fetch('admin/subtemplate/order_mgr/box_instance_for_editor.tpl.php');

		return array(
			$html,
			DAO::getCompressedArrayFromDAO($bundle)
		);
	}

	function saveDeliveryAddress($shippingData, $thisOrder = false, $returnToClient = true)
	{
		if (!$thisOrder)
		{
			$thisOrder = DAO_CFactory::create('orders');
			$thisOrder->id = $this->order_id;
			if (!$thisOrder->find(true) && $returnToClient)
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address. Order not found.',
				));
				exit;
			}
		}

		if ($thisOrder)
		{

			$shipping_phone_number = $shippingData['shipping_phone_number'];
			$shipping_phone_number_new = $shippingData['shipping_phone_number_new'];
			$shipping_email_address = $shippingData['shipping_email_address'];

			if ($this->isAddressNew($shippingData))
			{
				// update address book with new row
				$newABAddress = DAO_CFactory::create('address');
				$newABAddress->user_id = $this->user_id;
				$newABAddress->location_type = "ADDRESS_BOOK";
				$newABAddress->firstname = $shippingData['shipping_firstname'];
				$newABAddress->lastname = $shippingData['shipping_lastname'];
				$newABAddress->address_line1 = $shippingData['shipping_address_line1'];
				$newABAddress->address_line2 = $shippingData['shipping_address_line2'];
				$newABAddress->city = $shippingData['shipping_city'];
				$newABAddress->state_id = $shippingData['shipping_state_id'];
				$newABAddress->postal_code = $shippingData['shipping_postal_code'];
				$newABAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
				$newABAddress->email_address = $shipping_email_address;
				$newABAddress->insert();
			}

			$thisOrder->orderAddress();

			if (!empty($shipping_email_address) && !ValidationRules::validateEmail($shipping_email_address))
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address. Invalid Email Address.',
				));
				exit;
			}

			$thisOrder->orderAddress->firstname = $shippingData['shipping_firstname'];
			$thisOrder->orderAddress->lastname = $shippingData['shipping_lastname'];
			$thisOrder->orderAddress->address_line1 = $shippingData['shipping_address_line1'];
			$thisOrder->orderAddress->address_line2 = $shippingData['shipping_address_line2'];
			$thisOrder->orderAddress->city = $shippingData['shipping_city'];
			$thisOrder->orderAddress->state_id = $shippingData['shipping_state_id'];
			$thisOrder->orderAddress->postal_code = $shippingData['shipping_postal_code'];
			$thisOrder->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
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

			// TODO: This creates a new row -which in the Order manager can result in a lot of unneeded rows
			$thisOrder->orderAddressDeliveryProcessUpdate();

			if ($returnToClient)
			{
				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'The delivery address was saved..'
				));
				exit;
			}

			return true;
		}

		return false;
	}

	function getNextShipDeliveryDateSession($storeObj, $service_days)
	{
		$orgServiceDays = $service_days;
		$deliveryDateFilter = $service_days - 1;
		$today = new DateTime();
		$service_days++;
		$today->modify("+$service_days days");
		$earliestDate = $today->format("Y-m-d 00:00:00");

		$sessionFinder = new DAO();
		//$sessionFinder->query("select id from session where session_start > '$earliestDate' and store_id = {$storeObj->id} and is_deleted = 0 and delivered_supports_delivery > $deliveryDateFilter  order by session_start asc limit 1");
		$sessionFinder->query("select iq.id from (
							select id, session_start, store_id from session where session_start > '$earliestDate' and store_id =  {$storeObj->id} and is_deleted = 0 and delivered_supports_delivery > $deliveryDateFilter  order by session_start asc limit 20) as iq
							join session s2 on s2.session_start = DATE_SUB(iq.session_start, INTERVAL $orgServiceDays DAY) and s2.store_id = iq.store_id and s2.is_deleted = 0 and s2.delivered_supports_shipping > 0
							order by iq.session_start limit 1");
		if ($sessionFinder->fetch())
		{
			return $sessionFinder->id;
		}

		return false;
	}

	function isAddressNew($addressData)
	{
		$addressArray = array();
		$currentAddresses = DAO_CFactory::create('address');
		$currentAddresses->query("select * from address where user_id = {$this->user_id} and location_type='ADDRESS_BOOK' and is_deleted = 0");
		while ($currentAddresses->fetch())
		{
			$addressArray[] = DAO::getCompressedArrayFromDAO($currentAddresses);
		}

		$hasMatch = false;
		foreach ($addressArray as $thisABAddress)
		{
			if ($addressData['shipping_postal_code'] != $thisABAddress['postal_code'])
			{
				continue;
			}
			if ($addressData['shipping_firstname'] != $thisABAddress['firstname'])
			{
				continue;
			}
			if ($addressData['shipping_lastname'] != $thisABAddress['lastname'])
			{
				continue;
			}
			if ($addressData['shipping_address_line1'] != $thisABAddress['address_line1'])
			{
				continue;
			}
			if ($addressData['shipping_address_line2'] != $thisABAddress['address_line2'])
			{
				continue;
			}
			if ($addressData['shipping_city'] != $thisABAddress['city'])
			{
				continue;
			}
			if ($addressData['shipping_state_id'] != $thisABAddress['state_id'])
			{
				continue;
			}
			if ($addressData['shipping_email_address'] != $thisABAddress['email_address'])
			{
				continue;
			}
			$hasMatch = true;
		}

		return !$hasMatch;
	}

	function setDeliveryAddressAndSaveOrder($shippingData)
	{
		$orderState = false;
		if (!empty($_POST['order_state']) && in_array($_POST['order_state'], array(
				"NEW",
				"SAVED",
				"ACTIVE"
			)))
		{
			$orderState = $_POST['order_state'];
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'A problem occurred while saving the delivery address. The Order State is invalid.',
			));
			exit;
		}

		if ($orderState == "NEW")
		{
			$dist_ctr_id = false;
			$ckzip = DAO_CFactory::create('zipcodes');
			$ckzip->zip = $shippingData['shipping_postal_code'];

			if ($ckzip->find(true))
			{
				if (!empty($ckzip->distribution_center))
				{
					$dist_ctr_id = $ckzip->distribution_center;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'We do not ship to the zip code provided.',
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address. The zip code is invalid',
				));
				exit;
			}

			$orderObj = new COrdersDelivered();
			$orderObj->user_id = $this->user_id;
			$orderObj->store_id = $dist_ctr_id;
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
			$orderObj->is_in_plate_points_program = 0;

			// new order so we need to add the markup and sales tax onject ids
			$Store = $orderObj->getStore();

			$session_id = $this->getNextShipDeliveryDateSession($Store, $ckzip->service_days);

			$taxObj = $Store->getCurrentSalesTaxObj();
			if ($taxObj)
			{
				$orderObj->sales_tax_id = $taxObj->id;
			}

			//$orderObj->applyDeliveryFee();

			$orderObj->insert(true);

			$bookingObj = DAO_CFactory::create('booking');
			$bookingObj->user_id = $this->user_id;
			$bookingObj->session_id = $session_id;
			$bookingObj->order_id = $orderObj->id;
			$bookingObj->booking_type = CBooking::STANDARD;
			$bookingObj->status = CBooking::SAVED;

			$bookingObj->insert();

			if ($this->saveDeliveryAddress($shippingData, $orderObj, false))
			{
				echo json_encode(array(
					'processor_success' => true,
					'order_id' => $orderObj->id,
					'processor_message' => 'The delivery address was saved.'
				));
				exit;
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address.',
				));
				exit;
			}
		}
		else if ($orderState == "SAVED")
		{
			$orderID = false;
			if (!empty($_POST['order_id']) || !is_numeric($_POST['order_id']))
			{
				$orderID = $_POST['order_id'];
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address. The Order ID is invalid.'
				));
				exit;
			}

			$dist_ctr_id = false;
			$ckzip = DAO_CFactory::create('zipcodes');
			$ckzip->zip = $shippingData['shipping_postal_code'];

			if ($ckzip->find(true))
			{
				if (!empty($ckzip->distribution_center))
				{
					$dist_ctr_id = $ckzip->distribution_center;
				}
				else
				{
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => 'We do not ship to the zip code provided.',
					));
					exit;
				}
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address. The zip code is invalid',
				));
				exit;
			}

			$orderObj = new COrdersDelivered();
			$orderObj->id = $orderID;
			$orderObj->find(true);

			$Store = $orderObj->getStore();
			$session_id = $this->getNextShipDeliveryDateSession($Store, $ckzip->service_days);

			$taxObj = $Store->getCurrentSalesTaxObj();
			if ($taxObj)
			{
				$orderObj->sales_tax_id = $taxObj->id;
			}

			//$orderObj->applyDeliveryFee();

			$bookingObj = DAO_CFactory::create('booking');
			$bookingObj->status = "SAVED";
			$bookingObj->order_id = $orderID;
			$bookingObj->user_id = $this->user_id;
			$bookingObj->find(true);
			$bookingObj->session_id = $session_id;
			$oldBooking = clone($bookingObj);
			$bookingObj->update($oldBooking);

			if ($this->saveDeliveryAddress($shippingData, $orderObj, false))
			{
				echo json_encode(array(
					'processor_success' => true,
					'order_id' => $orderObj->id,
					'processor_message' => 'The delivery address was saved.'
				));
				exit;
			}
			else
			{
				echo json_encode(array(
					'processor_success' => false,
					'processor_message' => 'A problem occurred while saving the delivery address.',
				));
				exit;
			}
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The Delivery Address of Active orders cannot be changed.  Please cancel this order and place a new one.',
			));
			exit;
		}
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

		$OrderObj = new COrdersDelivered();
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);
		$OrderObj->reconstruct();
		$Session = $OrderObj->findSession(true);
		$menu_id = (!empty($Session->menu_id) ? $Session->menu_id : false);
		$OrderObj->refreshForEditing($menu_id);
		$OrderObj->recalculate(true, false);

		$paymentArr = $_POST['paymentList'];

		$creditArray = array();
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

		$result = $OrderObj->cancel($creditArray, $cancelReason, $declined_MFY, $declined_Reschedule);

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
		$OrderObj = DAO_CFactory::create('orders', true);
		$OrderObj->id = $this->order_id;
		$OrderObj->joinAddWhereAsOn(DAO_CFactory::create('store', true));
		$OrderObj->find(true);
		$OrderObj->reconstruct();
		$sessionObj = $OrderObj->findSession(true);
		$menu_id = (!empty($sessionObj->menu_id) ? $sessionObj->menu_id : false);

		$OrderObj->refreshForEditing($menu_id);
		$OrderObj->recalculate(true, false);

		// get user and store names
		$UserObj = DAO_CFactory::create('user', true);
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

		CLog::RecordDebugTrace('order_mgr_processor_delivered::savePaymentAndBookOrder called for order: ' . $this->order_id, "TR_TRACING");

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

		$OrderObj = new COrdersDelivered();
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);

		$orgOrderObj = clone($OrderObj);

		$Session = $OrderObj->findSession(true);
		$OrderObj->reconstruct();
		$OrderObj->refreshForEditing();

		if ($Session->menu_id <= 278)
		{
			if ($userObj->dream_rewards_version == 3 && ($userObj->dream_reward_status == 1 || $userObj->dream_reward_status == 3))
			{
				$OrderObj->is_in_plate_points_program = 1;
			}
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

		$OrderObj->orderAddress();

		$OrderObj->orderShipping();

		$OrderObj->recalculate(true, false, false);

		$OrderObj->timestamp_created = date('Y-m-d H:i:s');
		$OrderObj->update($orgOrderObj, true);

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

			$payment2Obj->payment_number = (!empty($_REQUEST['add_payment_data']['number']) ? $_REQUEST['add_payment_data']['number'] : "null");
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
			$paymentObj->is_delayed_payment = 0;
			$paymentObj->is_deposit = 0;

			if (isset($_REQUEST['payment_data']['save_card']))
			{
				$paymentObj->save_card_on_completion = true;
			}
		}
		else
		{
			$paymentObj->is_delayed_payment = 0;
			$paymentObj->is_deposit = 0;
		}

		if ($paymentObj->payment_type != 'CC')
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

		// TODO:
		/*
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
*/

		try
		{

			$OrderObj->allowOverrideOfServiceFee = true;
			$OrderObj->allowOverrideOfDeliveryFee = true;

			$rslt = $OrderObj->processNewOrderDirectOrder($payments, false, false, true);

			switch ($rslt['result'])
			{
				case 'success':

					$OrderObj->query("update orders set order_confirmation = '{$OrderObj->order_confirmation}' where id = {$OrderObj->id}");
					$OrderObj->removeInitialInventory();

					// TODO: set default Dist ctr?
					//$userObj->setHomeStore($OrderObj->store_id);
					// Increment DreamRewards Status if Appropriate

					CCustomerReferral::updateAsOrderedIfEligible($userObj, $OrderObj);

					// TODO: need override function
					COrdersDelivered::sendConfirmationEmail($userObj, $OrderObj);

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
						'processor_message' => '(#e1) The Credit Card was declined:<br />' . $rslt['userText']
					));
					exit;
				case 'invalidCC':
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => '(#e2) The credit card number you entered could not be verified. Please double check your payment information'
					));
					exit;
				case 'invalidPayment':
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => '(#e3) The credit card number you entered could not be verified. Please double check your payment information'
					));
					exit;

				case 'failed':
				default:
					echo json_encode(array(
						'processor_success' => false,
						'processor_message' => '(#e4) An error has occurred in the payment process, please try again later'
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
				'processor_message' => '(#e5) An error has occurred in the payment process, please try again later'
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
		$OrderItems->query("update order_item set is_deleted = 1, edit_sequence_id = $order_record->id, updated_by = $adminUser where order_id = {$this->order_id} and is_deleted = 0");

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

		$OrderObj = new COrdersDelivered();
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);

		$orgOrder = clone($OrderObj);
		$OrderObj->reconstruct();

		$Session = $OrderObj->findSession(true);

		if (!empty($_POST['direct_order_discount']) && is_numeric($_POST['direct_order_discount']) && $_POST['direct_order_discount'] >= 0)
		{
			$OrderObj->addDiscount($_POST['direct_order_discount']);
		}

		if (isset($_POST['plate_points_discount']) && is_numeric($_POST['plate_points_discount']))
		{
			$OrderObj->points_discount_total = $_POST['plate_points_discount'];
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
		$OrderObj->allowOverrideOfServiceFee = true;
		$OrderObj->allowOverrideOfDeliveryFee = true;

		$OrderObj->refreshForEditing($OrderObj->findSession()->menu_id);
		$OrderObj->recalculate(true);
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

		$OrderObj = new COrdersDelivered();
		$OrderObj->id = $this->order_id;
		$OrderObj->find(true);

		$orgOrder = clone($OrderObj);

		if (isset($_POST['subtotal_delivery_fee']) && is_numeric($_POST['subtotal_delivery_fee']) && $_POST['subtotal_delivery_fee'] >= 0)
		{
			$OrderObj->subtotal_delivery_fee = $_POST['subtotal_delivery_fee'];
		}

		$OrderItems = DAO_CFactory::create('order_item');
		$adminUser = CUser::getCurrentUser()->id;
		$OrderItems->query("update order_item set is_deleted = 1, edit_sequence_id = 2, updated_by = $adminUser where order_id = {$this->order_id} and is_deleted = 0");

		$SessionObj = $OrderObj->findSession(true);
		$OrderObj->refreshForEditing($SessionObj->menu_id);

		$BookingUpdate = DAO_CFactory::create('booking');
		$BookingUpdate->query("update booking set booking_type = 'STANDARD' where order_id = {$this->order_id} and status = 'SAVED'");

		$items = array();
		if (!empty($_POST['items']))
		{
			$items = $_POST['items'];
		}

		$this->addMenuItemsToOrder($OrderObj, $items);

		$OrderObj->insertEditedItems(false, false);
		$OrderObj->recalculate(true, false);
		$OrderObj->bundle_id = 'null';

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

		$sessionObj = DAO_CFactory::create('session');
		$sessionObj->id = $this->targetSession;
		if (!$sessionObj->find(true))
		{
			throw new Exception('The session cannot be found.', self::dd_general_exception_code);
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

		$booking = DAO_CFactory::create('booking');
		$booking->user_id = $this->user_id;
		$booking->order_id = $this->order_id;
		if (!$booking->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => "A problem occurred setting the session. Booking not found"
			));
			exit;
		}

		$oldBooking = clone($booking);
		$booking->session_id = $this->targetSession;
		$booking->update($oldBooking);

		return false;
	}

	function setSessionAndSave()
	{
		CLog::RecordDebugTrace('order_mgr_processor_delivered::setSessionAndSave called for user: ' . $this->user_id, "TR_TRACING");

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

	// TODO: convert to restore boxes
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

		$thisOrder = new COrdersDelivered();
		$thisOrder->id = $_POST["order_id"];

		if (!$thisOrder->find(true))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'Target session not found.'
			));
			exit;
		}

		$shippingInfo = $thisOrder->orderShipping();

		if (!$thisOrder->can_reschedule($shippingInfo, $TargetSession->id, 10))
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'The delivery date is not valid.'
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

			$result = $thisOrder->rescheduleSavedOrder($this->targetSession, $bookingObj, $thisOrder);
		}
		else
		{
			$result = $thisOrder->reschedule($this->org_session_id, true, true);// true meaning this is a fadmin, relax rules regarding date and overbooking
		}

		$serviceFeeUpdate = -1;
		$serviceFeeDescUpdate = "";

		if ($result === 'success')
		{


			$User = DAO_CFactory::create('user');
			$User->id = $thisOrder->user_id;
			$User->find(true);

			if ($OrderState != 'SAVED')
			{
				COrdersDelivered::sendRescheduleEmail($User, $thisOrder, $OrigSession->session_start);
			}

			$deliveryDate = new DateTime($TargetSession->session_start);
			$deliveryDate->modify("-{$shippingInfo->service_days} days");
			$shipDate = $deliveryDate->format("Y-m-d 00:00:00");

			echo json_encode(array(
				'processor_success' => true,
				'processor_message' => 'The order was successfully rescheduled.',
				'new_session_id' => $TargetSession->id,
				'new_session_time' => CTemplate::dateTimeFormat($TargetSession->session_start, FULL_MONTH_DAY_YEAR),
				'new_ship_time' => CTemplate::dateTimeFormat($shipDate, FULL_MONTH_DAY_YEAR)

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
	// Convert to support boxes
	function addMenuItemsToOrder($OrderObj, $items)
	{
		//get menu from session
		$menu_id = $OrderObj->findSession()->menu_id;

		//clear existing menu items
		$OrderObj->clearBoxesUnsafe();

		$totalItemQty = 0;
		//add menu items
		foreach ($items as $boxInstanceID => $boxData)
		{
			$boxInstanceObj = DAO_CFactory::create('box_instance');
			$boxInstanceObj->query("SELECT
					bi.*,
					b.title,
					b.description, 
					b.css_icon,
					b.box_type
					FROM box_instance AS bi
					JOIN box AS b ON b.id = bi.box_id
					WHERE bi.id = '" . $boxInstanceID . "'");
			$boxInstanceObj->fetch();

			foreach ($boxData as $id => $qty)
			{
				if (is_numeric($qty) && $qty > 0 && is_numeric($id))
				{
					$MenuItem = DAO_CFactory::create('menu_item');
					$MenuItem->query("SELECT
										mi.*
										FROM
										menu_item AS mi
										WHERE mi.id = $id
										AND mi.is_deleted = 0");

					if (!$MenuItem->fetch())
					{
						throw new Exception("Menu item not found: " . $id);
					}
					else
					{
						$totalItemQty += $qty;
						$OrderObj->addItemToBox($boxInstanceObj, $MenuItem, $qty);
					}
				}
			}
		}

		return $OrderObj;
	}

}

?>