<?php
require_once 'DAO/Store_history.php';

class CStoreHistory extends DAO_Store_history
{

	static $eventDescMap = array(
		200 => 'pending_delayed_payment_adjusted',
		300 => 'store_opted_in_to_plate_points',
		301 => 'retention_program_opt_in',
		302 => 'retention_program_opt_out',
		400 => 'store_franchise_change'
	);

	static function recordStoreEvent($user_id, $store_id, $order_id, $event_id, $payment_id = 'null', $booking_id = 'null', $session_id = 'null', $description = 'null')
	{
		$remote_address = 'localhost';
		if (isset($_SERVER['REMOTE_ADDR']))
		{
			$remote_address = $_SERVER['REMOTE_ADDR'];
		}

		$historyDAO = DAO_CFactory::create('store_history');
		$historyDAO->user_id = $user_id;
		$historyDAO->store_id = $store_id;
		$historyDAO->event_id = $event_id;
		$historyDAO->order_id = $order_id;
		$historyDAO->payment_id = $payment_id;
		$historyDAO->booking_id = $booking_id;
		$historyDAO->session_id = $session_id;
		$historyDAO->description = $description;
		$historyDAO->ip_address = $remote_address;

		$historyDAO->insert();
	}
}

?>