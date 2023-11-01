<?php
require_once("includes/CPageProcessor.inc");
require_once("includes/DAO/BusinessObject/CBooking.php");
require_once 'includes/api/shipping/shipstation/ShipStationShipmentWrapper.php';
require_once("CTemplate.inc");

class processor_admin_shipstation_manager extends CPageProcessor
{
	function runSiteAdmin()
	{
		$this->shipstationAction();
	}

	function runFranchiseStaff()
	{
		$this->shipstationAction();
	}

	function runFranchiseOwner()
	{
		$this->shipstationAction();
	}

	function runFranchiseLead()
	{
		$this->shipstationAction();
	}

	function runEventCoordinator()
	{
		$this->shipstationAction();
	}

	function runOpsLead()
	{
		$this->shipstationAction();
	}

	function runOpsSupport()
	{
		$this->shipstationAction();
	}

	function runFranchiseManager()
	{
		$this->shipstationAction();
	}

	function runHomeOfficeManager()
	{
		$this->shipstationAction();
	}

	function shipstationAction()
	{
		header('Pragma: no-cache');
		header("Cache-Control: no-store,no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Mon, 26 Jul 2005 05:00:00 GMT"); // Date in the past

		if (empty($_REQUEST['op']))
		{
			CAppUtil::processorMessageEcho(array(
				'processor_success' => false,
				'processor_message' => 'No operation specified.',
				'dd_toasts' => array(
					array('message' => 'No operation specified.')
				)
			));
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'resend-order')
		{
			if(!empty($_REQUEST['order']) && is_numeric($_REQUEST['order'])){
				//Send Updates to SS
				$order = new COrdersDelivered();
				$order->id = $_REQUEST['order'];
				$order->find(true);
				$order->orderShipping();
				$order->orderAddress();
				$result = ShipStationManager::getInstanceForOrder($order)->addUpdateOrder(new ShipStationOrderWrapper($order));
				if ($result == false)
				{
					$errors = ShipStationManager::getInstanceForOrder($order)->getLastError();
					CAppUtil::processorMessageEcho(array(
						'processor_success' => false,
						'processor_message' => 'Unable to resend order data to ShipStation.',
						'dd_toasts' => array(
							array('message' => $errors->message),
							$errors
						)
					));
				}
				else
				{
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Resent order data to ShipStation.',
						'dd_toasts' => array(
							array('message' => 'Resent order data to ShipStation.')
						)
					));
				}
			}else{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Invalid order number.',
					'dd_toasts' => array(
						array('message' => 'Invalid order number.')
					)
				));
			}
		}

		if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'fetch-tracking-number')
		{

			if(!empty($_REQUEST['order']) && is_numeric($_REQUEST['order'])){
				$order = new COrdersDelivered();
				$order->id = $_REQUEST['order'];
				$order->find(true);

				$shipmentWrapper = ShipStationManager::getInstanceForOrder($order)->getShipments(new ShipStationShipmentWrapper($order), false);
				$result = $shipmentWrapper->storeShippingData();
				if($result->isFailure()){
					$result->echoFailureMessages();
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Error fetching Tracking Information from ShipStation.',
						'dd_toasts' => $result->getFailureMessages()
					));
				}else{
					$message = 'Updated tracking number for this order is '.$shipmentWrapper->getLatestTrackingNumber().'.';
					$returned = $shipmentWrapper->getLatestTrackingNumber();
					if(empty($returned)){
						$message = 'The Tracking Number is not yet available.';
					}
					CAppUtil::processorMessageEcho(array(
						'processor_success' => true,
						'processor_message' => 'Fetched Tracking Number from ShipStation',
						'processor_tracking_number' => $shipmentWrapper->getLatestTrackingNumber(),
						'dd_toasts' => array(
							array('message' => $message)
						)
					));
				}
			}else{
				CAppUtil::processorMessageEcho(array(
					'processor_success' => true,
					'processor_message' => 'Invalid order number.',
					'dd_toasts' => array(
						array('message' => 'Invalid order number.')
					)
				));
			}
		}
	}
}

?>