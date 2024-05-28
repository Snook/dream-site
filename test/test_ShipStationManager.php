<?php
require_once(dirname(__FILE__) . "/../includes/Config.inc");
require_once 'includes/api/shipping/shipstation/ShipStationManager.php';
require_once 'includes/api/shipping/shipstation/ShipStationOrderBatchWrapper.php';
require_once 'includes/api/shipping/shipstation/ShipStationShipmentWrapper.php';

class test_ShipStationManager
{
	private static $instance = null;

	private $storeInfo = array(
		'313' => array('ssName' => 'Cummings'),
		'314' => array('ssName' => 'Camden'),
		'315' => array('ssName' => 'Overland'),
		'316' => array('ssName' => 'Pasadena'),
		'317' => array('ssName' => 'Midlothian'),
		'318' => array('ssName' => 'Plainville'),
		'319' => array('ssName' => 'La Mesa'),
		'320' => array('ssName' => 'Belmont'),
		'321' => array('ssName' => 'Austin'),
		'322' => array('ssName' => 'Lancaster'),
		'323' => array('ssName' => 'Phoenix'),
		'324' => array('ssName' => 'Castle Rock'),
		'325' => array('ssName' => 'Kennewick')
	);

	protected function __construct()
	{

	}

	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new test_ShipStationManager();
		}

		return self::$instance;
	}

	public function echoAllShipstationStoreId()
	{
		echo '-----All Shipstation Store Id------';
		echo PHP_EOL;
		foreach ($this->storeInfo as $id => $details)
		{
			sleep(5);//Dont hammer API
			$storeObj = new CStore();
			$storeObj->id = $id;
			echo $id;
			echo PHP_EOL;
			$result = ShipStationManager::getInstance($storeObj)->listStores();
			if ($result == false)
			{
				echo print_r(ShipStationManager::getInstance($storeObj)->getLastError(), true);
			}
			else
			{
				echo $result;
			}
			echo PHP_EOL;
		}
		echo PHP_EOL;
		echo PHP_EOL;
	}

	public function echoAllShipstationCarriers()
	{
		echo '-----All Shipstation Carriers------';
		echo PHP_EOL;
		foreach ($this->storeInfo as $id => $details)
		{
			sleep(5);//Dont hammer API
			$storeObj = new CStore();
			$storeObj->id = $id;
			echo $id;
			echo PHP_EOL;
			$result = ShipStationManager::getInstance($storeObj)->getCarriers();
			if ($result == false)
			{
				echo print_r(ShipStationManager::getInstance($storeObj)->getLastError(), true);
			}
			else
			{
				echo $result;
			}
			echo PHP_EOL;
			echo PHP_EOL;
		}
	}

	public function echoAllAccountTags()
	{
		echo '-----All Shipstation Account Tags------';
		echo PHP_EOL;
		foreach ($this->storeInfo as $id => $details)
		{
			sleep(5);//Dont hammer API
			$storeObj = new CStore();
			$storeObj->id = $id;
			echo $id;
			echo PHP_EOL;
			$result = ShipStationManager::getInstance($storeObj)->listAccountTags();
			if ($result == false)
			{
				echo print_r(ShipStationManager::getInstance($storeObj)->getLastError(), true);
			}
			else
			{
				echo $result;
			}
			echo PHP_EOL;
			echo PHP_EOL;
		}
	}

	public function doUpsertShipstationOrder($orderId)
	{
		echo '-----Insert or Update Shipstation Order------';
		echo PHP_EOL;

		$order = new COrdersDelivered();
		$order->id = $orderId;
		$order->find(true);
		$order->orderShipping();
		$order->orderAddress();
		echo $orderId;
		echo PHP_EOL;

		$orderMap = new ShipStationOrderWrapper($order);

		sleep(5);//Dont hammer API
		$result = ShipStationManager::getInstanceForOrder($order)->addUpdateOrder($orderMap);
		if ($result == false)
		{
			echo print_r(ShipStationManager::getInstanceForOrder($order)->getLastError(), true);
		}
		else
		{
			echo $result;
		}

		echo PHP_EOL;
		echo PHP_EOL;
	}

	public function echoShipstationOrder($orderId)
	{
		echo '-----Fetch Shipstation Order------';
		echo PHP_EOL;
		$order = new COrdersDelivered();
		$order->id = $orderId;
		$order->find(true);
		echo $orderId;
		echo PHP_EOL;
		sleep(5);//Dont hammer API
		$result = ShipStationManager::getInstanceForOrder($order)->getOrders(array('orderNumber' => 'TEST_IGNORE_' . $orderId));
		if ($result == false)
		{
			echo print_r(ShipStationManager::getInstanceForOrder($order)->getLastError(), true);
		}
		else
		{
			echo print_r($result, true);
		}

		echo PHP_EOL;
		echo PHP_EOL;
	}

	public function echoTrackingNumber($orderId)
	{

		echo '-----Fetch Tracking Number for Order------';
		echo PHP_EOL;
		$order = new COrdersDelivered();
		$order->id = $orderId;
		$order->find(true);
		echo $orderId;
		echo PHP_EOL;
		sleep(5);//Dont hammer API
		$shipmentWrapper = ShipStationManager::getInstanceForOrder($order)->getShipments(array('orderNumber' => $orderId));
		echo $shipmentWrapper->getLatestTrackingNumber();

		echo PHP_EOL;
		echo PHP_EOL;
	}

	public function storeTrackingNumber($orderId)
	{
		echo '-----Persist Tracking Number for Order------';
		echo PHP_EOL;
		$order = new COrdersDelivered();
		$order->id = $orderId;
		$order->find(true);
		echo $orderId;
		echo PHP_EOL;
		sleep(5);//Dont hammer API
		$shipmentWrapper = ShipStationManager::getInstanceForOrder($order)->getShipments(array('orderNumber' => $orderId));
		$result = $shipmentWrapper->storeShippingData();
		if ($result->isFailure())
		{
			$result->echoFailureMessages();
		}
		else
		{
			$result->echoAllMessages();
		}

		echo PHP_EOL;
		echo PHP_EOL;
	}
}

$test = test_ShipStationManager::getInstance();
$test->echoAllShipstationStoreId();
//$test->echoAllShipstationCarriers();
//$test->doUpsertShipstationOrder(3855722);
//$test->echoShipstationOrder(3855722);
//$test->echoShipstationOrder(3855723);
//$test->echoShipstationOrder(3855725);

//$test->echoTrackingNumber(3641674);
//$test->storeTrackingNumber(3641674);

?>