<?php

require_once 'DAO/User_history.php';

class CUserHistory extends DAO_User_history {


	static $eventDescMap = array(
		100 => 'successful_login',
		1 => 'unsuccessful_login',
		2 => 'account_created',
		3 => 'account_edited',
		4 => 'dfl_account_created',
		200 => 'dfl_enrollment',
		201 => 'dfl_enrollment_cancelled',
		202 => 'dfl_enrollment_expired',
		203 => 'dfl_enrollment_activated',
		204 => 'dfl_enrollment_renewed',
		300 => 'referral_reward_denied_due_to_PU_discount',
		400 => 'store_credit_expired',
		500 => 'changed_delayed_payment_transaction_number',
		501 => 'changed_delayed_payment_status',
		502 => 'edited_check_payment_details',
		600 => 'admin_email_guest',
		601 => 'admin_email_guest_with_attachment',
		700 => 'agreed_to_fadmin_nda',
		800 => 'social_facebook_create_post',
		801 => 'social_facebook_create_event',
		802 => 'social_facebook_connected',
		900 => 'opted_out_of_PlatePoints',
	    1000 => 'order_was_completed'
	);


	static function recordUserEvent($user_id, $store_id, $order_id, $event_id, $booking_id = 'null', $session_id = 'null', $description = 'null')
	{
		$remote_address = 'localhost';
		if (isset($_SERVER['REMOTE_ADDR']))
			$remote_address = $_SERVER['REMOTE_ADDR'];


		$historyDAO = DAO_CFactory::create('user_history');
		$historyDAO->user_id = $user_id;
		$historyDAO->store_id = $store_id;
		$historyDAO->event_id = $event_id;
		$historyDAO->order_id = $order_id;
		$historyDAO->booking_id = $booking_id;
		$historyDAO->session_id = $session_id;
		$historyDAO->description = $description;
		$historyDAO->ip_address = $remote_address;

		$historyDAO->insert();
	}


}
?>