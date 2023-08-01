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


class processor_delivered_cancel_order extends CPageProcessor
{

	private $order_id = false;


	public function getOrderID()
	{
		return $this->order_id;
	}

	function runPublic()
	{
		echo json_encode(array('success' => false, 'message' => 'Not logged in'));
	}
	function runCustomer()
	{
		$this->processData();
	}
	function runFranchiseStaff()
	{
		$this->processData();
	}
	function runFranchiseManager()
	{
		$this->processData();
	}
	function runOpsLead()
	{
		$this->processData();
	}
	function runFranchiseOwner()
	{
		$this->processData();
	}
	function runHomeOfficeStaff()
	{
		$this->processData();
	}
	function runHomeOfficeManager()
	{
		$this->processData();
	}
	function runSiteAdmin()
	{
		$this->processData();
	}


	function processData()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past


		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'cancel_preflight')
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
	}


	function do_cancel()
	{

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

        $declined_MFY = true;

        $declined_Reschedule = true;



        $result = $OrderObj->cancel($creditArray, $cancelReason, $declined_MFY, $declined_Reschedule);

		$result = true;
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
			$html = $tpl->fetch('customer/subtemplate/cancel_success.tpl.php');

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

		$html = $tpl->fetch('customer/subtemplate/cancel_preflight.tpl.php');

		echo json_encode(array(
			'processor_success' => true,
			'processor_message' => 'The cancel_preflight was successful.',
			'data' => $html,
			'orderInfo' => $OrderObj
		));
		exit;
	}
}
?>